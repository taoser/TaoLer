<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\LogicalScreenDescriptor;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\InvalidArgumentException;

class LogicalScreenDescriptorDecoder extends AbstractPackedBitDecoder
{
    /**
     * Decode given string to current instance.
     *
     * @throws DecoderException
     */
    public function decode(): LogicalScreenDescriptor
    {
        $logicalScreenDescriptor = new LogicalScreenDescriptor();

        // bytes 1-4
        try {
            $logicalScreenDescriptor->setSize(
                $this->decodeWidth($this->nextBytesOrFail(2)),
                $this->decodeHeight($this->nextBytesOrFail(2))
            );
        } catch (InvalidArgumentException $e) {
            throw new DecoderException('Failed to decode image size of logical screen descriptor', previous: $e);
        }

        // byte 5
        $packedField = $this->nextByteOrFail();

        $logicalScreenDescriptor->setGlobalColorTableExistance(
            $this->decodeGlobalColorTableExistance($packedField)
        );

        $logicalScreenDescriptor->setBitsPerPixel(
            $this->decodeBitsPerPixel($packedField)
        );

        $logicalScreenDescriptor->setGlobalColorTableSorted(
            $this->decodeGlobalColorTableSorted($packedField)
        );

        $logicalScreenDescriptor->setGlobalColorTableSize(
            $this->decodeGlobalColorTableSize($packedField)
        );

        // byte 6
        $logicalScreenDescriptor->setBackgroundColorIndex(
            $this->decodeBackgroundColorIndex($this->nextByteOrFail())
        );

        // byte 7
        $logicalScreenDescriptor->setPixelAspectRatio(
            $this->decodePixelAspectRatio($this->nextByteOrFail())
        );

        return $logicalScreenDescriptor;
    }

    /**
     * Decode width.
     *
     * @throws DecoderException
     */
    private function decodeWidth(string $source): int
    {
        $unpacked = unpack('v*', $source);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to decode width in logical screen descriptor');
        }

        return $unpacked[1];
    }

    /**
     * Decode height.
     *
     * @throws DecoderException
     */
    private function decodeHeight(string $source): int
    {
        $unpacked = unpack('v*', $source);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to decode height in logical screen descriptor');
        }

        return $unpacked[1];
    }

    /**
     * Decode existance of global color table.
     *
     * @throws DecoderException
     */
    private function decodeGlobalColorTableExistance(string $byte): bool
    {
        return $this->hasPackedBit($byte, 0);
    }

    /**
     * Decode color resolution in bits per pixel.
     *
     * @throws DecoderException
     */
    private function decodeBitsPerPixel(string $byte): int
    {
        return intval(bindec($this->packedBits($byte, 1, 3))) + 1;
    }

    /**
     * Decode global color table sorted status.
     *
     * @throws DecoderException
     */
    private function decodeGlobalColorTableSorted(string $byte): bool
    {
        return $this->hasPackedBit($byte, 4);
    }

    /**
     * Decode size of global color table.
     *
     * @throws DecoderException
     */
    private function decodeGlobalColorTableSize(string $byte): int
    {
        return intval(bindec($this->packedBits($byte, 5, 3)));
    }

    /**
     * Decode background color index.
     *
     * @throws DecoderException
     */
    private function decodeBackgroundColorIndex(string $source): int
    {
        $unpacked = unpack('C', $source);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to decode background color index in logical screen descriptor');
        }

        return $unpacked[1];
    }

    /**
     * Decode pixel aspect ratio.
     *
     * @throws DecoderException
     */
    private function decodePixelAspectRatio(string $source): int
    {
        $unpacked = unpack('C', $source);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to decode pixel aspect ratio in logical screen descriptor');
        }

        return $unpacked[1];
    }
}
