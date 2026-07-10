<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\AbstractEntity;
use Intervention\Gif\Blocks\DataSubBlock;
use Intervention\Gif\Blocks\ImageData;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\InvalidArgumentException;

class ImageDataDecoder extends AbstractDecoder
{
    /**
     * Decode current source.
     *
     * @throws DecoderException
     */
    public function decode(): ImageData
    {
        $data = new ImageData();

        // LZW min. code size
        $char = $this->nextByteOrFail();
        $unpacked = unpack('C', $char);
        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to decode lzw min. code size of image data');
        }

        $data->setLzwMinCodeSize(intval($unpacked[1]));

        do {
            // decode sub blocks
            $char = $this->nextByteOrFail();
            $unpacked = unpack('C', $char);
            if ($unpacked === false || !array_key_exists(1, $unpacked)) {
                throw new DecoderException('Failed to decode image data sub block of image data');
            }

            $size = intval($unpacked[1]);

            if ($size > 0) {
                try {
                    $data->addBlock(new DataSubBlock($this->nextBytesOrFail($size)));
                } catch (InvalidArgumentException $e) {
                    throw new DecoderException(
                        'Failed to decode image data sub block of image data',
                        previous: $e
                    );
                }
            }
        } while ($char !== AbstractEntity::TERMINATOR);

        return $data;
    }
}
