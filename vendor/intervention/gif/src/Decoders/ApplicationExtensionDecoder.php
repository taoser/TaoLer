<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\ApplicationExtension;
use Intervention\Gif\Blocks\DataSubBlock;
use Intervention\Gif\Blocks\NetscapeApplicationExtension;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\InvalidArgumentException;

class ApplicationExtensionDecoder extends AbstractDecoder
{
    /**
     * Decode current source.
     *
     * @throws DecoderException
     */
    public function decode(): ApplicationExtension
    {
        $result = new ApplicationExtension();

        $this->nextByteOrFail(); // marker
        $this->nextByteOrFail(); // label
        $blocksize = $this->decodeBlockSize($this->nextByteOrFail());
        $application = $this->nextBytesOrFail($blocksize);

        if ($application === NetscapeApplicationExtension::IDENTIFIER . NetscapeApplicationExtension::AUTH_CODE) {
            $result = new NetscapeApplicationExtension();

            // skip length
            $this->nextByteOrFail();

            try {
                $result->setBlocks([
                    new DataSubBlock($this->nextBytesOrFail(3))
                ]);
            } catch (InvalidArgumentException $e) {
                throw new DecoderException(
                    'Failed to decode image data sub block of image data',
                    previous: $e
                );
            }

            // skip terminator
            $this->nextByteOrFail();

            return $result;
        }

        $result->setApplication($application);

        // decode data sub blocks
        $blocksize = $this->decodeBlockSize($this->nextByteOrFail());
        while ($blocksize > 0) {
            try {
                $result->addBlock(new DataSubBlock($this->nextBytesOrFail($blocksize)));
            } catch (InvalidArgumentException $e) {
                throw new DecoderException(
                    'Failed to decode image data sub block of image data',
                    previous: $e
                );
            }

            $blocksize = $this->decodeBlockSize($this->nextByteOrFail());
        }

        return $result;
    }

    /**
     * Decode block size of ApplicationExtension from given byte.
     *
     * @throws DecoderException
     */
    protected function decodeBlockSize(string $byte): int
    {
        $unpacked = @unpack('C', $byte);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to decode block size of application extension');
        }

        return intval($unpacked[1]);
    }
}
