<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\Color;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\InvalidArgumentException;

class ColorDecoder extends AbstractDecoder
{
    /**
     * Decode current source to Color.
     *
     * @throws DecoderException
     */
    public function decode(): Color
    {
        try {
            return new Color(
                $this->decodeColorValue($this->nextByteOrFail()),
                $this->decodeColorValue($this->nextByteOrFail()),
                $this->decodeColorValue($this->nextByteOrFail()),
            );
        } catch (InvalidArgumentException $e) {
            throw new DecoderException(
                'Failed to decode color channel values',
                previous: $e
            );
        }
    }

    /**
     * Decode color value from source.
     *
     * @throws DecoderException
     */
    protected function decodeColorValue(string $byte): int
    {
        $unpacked = unpack('C', $byte);
        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to decode color value');
        }

        return $unpacked[1];
    }
}
