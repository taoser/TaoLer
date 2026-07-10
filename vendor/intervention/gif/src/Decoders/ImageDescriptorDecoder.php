<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\ImageDescriptor;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\InvalidArgumentException;

class ImageDescriptorDecoder extends AbstractPackedBitDecoder
{
    /**
     * Decode given string to current instance.
     *
     * @throws DecoderException
     */
    public function decode(): ImageDescriptor
    {
        $descriptor = new ImageDescriptor();

        $this->nextByteOrFail(); // skip separator

        $descriptor->setPosition(
            $this->decodeMultiByte($this->nextBytesOrFail(2)),
            $this->decodeMultiByte($this->nextBytesOrFail(2))
        );

        try {
            $descriptor->setSize(
                $this->decodeMultiByte($this->nextBytesOrFail(2)),
                $this->decodeMultiByte($this->nextBytesOrFail(2))
            );
        } catch (InvalidArgumentException $e) {
            throw new DecoderException('Failed to decode image size of image descriptor', previous: $e);
        }

        $packedField = $this->nextByteOrFail();

        $descriptor->setLocalColorTableExistance(
            $this->decodeLocalColorTableExistance($packedField)
        );

        $descriptor->setLocalColorTableSorted(
            $this->decodeLocalColorTableSorted($packedField)
        );

        $descriptor->setLocalColorTableSize(
            $this->decodeLocalColorTableSize($packedField)
        );

        $descriptor->setInterlaced(
            $this->decodeInterlaced($packedField)
        );

        return $descriptor;
    }

    /**
     * Decode local color table existance.
     *
     * @throws DecoderException
     */
    private function decodeLocalColorTableExistance(string $byte): bool
    {
        return $this->hasPackedBit($byte, 0);
    }

    /**
     * Decode local color table sort method.
     *
     * @throws DecoderException
     */
    private function decodeLocalColorTableSorted(string $byte): bool
    {
        return $this->hasPackedBit($byte, 2);
    }

    /**
     * Decode local color table size.
     *
     * @throws DecoderException
     */
    private function decodeLocalColorTableSize(string $byte): int
    {
        return (int) bindec($this->packedBits($byte, 5, 3));
    }

    /**
     * Decode interlaced flag.
     *
     * @throws DecoderException
     */
    private function decodeInterlaced(string $byte): bool
    {
        return $this->hasPackedBit($byte, 1);
    }
}
