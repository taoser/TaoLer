<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\GraphicControlExtension;
use Intervention\Gif\DisposalMethod;
use Intervention\Gif\Exceptions\DecoderException;
use TypeError;
use ValueError;

class GraphicControlExtensionDecoder extends AbstractPackedBitDecoder
{
    /**
     * Decode given string to current instance.
     *
     * @throws DecoderException
     */
    public function decode(): GraphicControlExtension
    {
        $result = new GraphicControlExtension();

        // bytes 1-3
        $this->nextBytesOrFail(3); // skip marker, label & bytesize

        // byte #4
        $packedField = $this->nextByteOrFail();
        $result->setDisposalMethod($this->decodeDisposalMethod($packedField));
        $result->setUserInput($this->decodeUserInput($packedField));
        $result->setTransparentColorExistance($this->decodeTransparentColorExistance($packedField));

        // bytes 5-6
        $result->setDelay($this->decodeDelay($this->nextBytesOrFail(2)));

        // byte #7
        $result->setTransparentColorIndex($this->decodeTransparentColorIndex(
            $this->nextByteOrFail()
        ));

        // byte #8 (terminator)
        $this->nextByteOrFail();

        return $result;
    }

    /**
     * Decode disposal method
     *
     * @throws DecoderException
     */
    protected function decodeDisposalMethod(string $byte): DisposalMethod
    {
        try {
            return DisposalMethod::from(
                intval(bindec($this->packedBits($byte, 3, 3)))
            );
        } catch (TypeError | ValueError $e) {
            throw new DecoderException(
                'Failed to decode disposal method in graphic control extension',
                previous: $e,
            );
        }
    }

    /**
     * Decode user input flag.
     *
     * @throws DecoderException
     */
    private function decodeUserInput(string $byte): bool
    {
        return $this->hasPackedBit($byte, 6);
    }

    /**
     * Decode transparent color existance.
     *
     * @throws DecoderException
     */
    private function decodeTransparentColorExistance(string $byte): bool
    {
        return $this->hasPackedBit($byte, 7);
    }

    /**
     * Decode delay value.
     *
     * @throws DecoderException
     */
    private function decodeDelay(string $bytes): int
    {
        $unpacked = unpack('v*', $bytes);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to decode animation delay in graphic control extension');
        }

        return $unpacked[1];
    }

    /**
     * Decode transparent color index.
     *
     * @throws DecoderException
     */
    private function decodeTransparentColorIndex(string $byte): int
    {
        $unpacked = unpack('C', $byte);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to decode transparent color index in graphic control extension');
        }

        return $unpacked[1];
    }
}
