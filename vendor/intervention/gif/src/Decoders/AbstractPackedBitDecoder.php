<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Exceptions\DecoderException;

abstract class AbstractPackedBitDecoder extends AbstractDecoder
{
    /**
     * Decode packed byte.
     *
     * @throws DecoderException
     */
    protected function decodePackedByte(string $byte): int
    {
        $unpacked = unpack('C', $byte);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to decode packed info block size');
        }

        return intval($unpacked[1]);
    }

    /**
     * Determine if packed bit is set.
     *
     * @throws DecoderException
     */
    protected function hasPackedBit(string $byte, int $num): bool
    {
        return (bool) $this->packedBits($byte)[$num];
    }

    /**
     * Get packed bits.
     *
     * @throws DecoderException
     */
    protected function packedBits(string $byte, int $start = 0, int $length = 8): string
    {
        $bits = str_pad(decbin($this->decodePackedByte($byte)), 8, '0', STR_PAD_LEFT);

        return substr($bits, $start, $length);
    }
}
