<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\LogicalScreenDescriptor;

class LogicalScreenDescriptorEncoder extends AbstractEncoder
{
    /**
     * Create new instance.
     */
    public function __construct(LogicalScreenDescriptor $entity)
    {
        parent::__construct($entity);
    }

    /**
     * Encode current entity.
     */
    public function encode(): string
    {
        return implode('', [
            $this->encodeWidth(),
            $this->encodeHeight(),
            $this->encodePackedField(),
            $this->encodeBackgroundColorIndex(),
            $this->encodePixelAspectRatio(),
        ]);
    }

    /**
     * Encode width of current instance.
     */
    private function encodeWidth(): string
    {
        return pack('v*', $this->entity->width());
    }

    /**
     * Encode height of current instance.
     */
    private function encodeHeight(): string
    {
        return pack('v*', $this->entity->height());
    }

    /**
     * Encode background color index of global color table.
     */
    private function encodeBackgroundColorIndex(): string
    {
        return pack('C', $this->entity->backgroundColorIndex());
    }

    /**
     * Encode pixel aspect ratio.
     */
    private function encodePixelAspectRatio(): string
    {
        return pack('C', $this->entity->pixelAspectRatio());
    }

    /**
     * Return color resolution for encoding.
     */
    private function encodeColorResolution(): string
    {
        return str_pad(decbin($this->entity->bitsPerPixel() - 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Encode size of global color table.
     */
    private function encodeGlobalColorTableSize(): string
    {
        return str_pad(decbin($this->entity->globalColorTableSize()), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Encode packed field of current instance.
     */
    private function encodePackedField(): string
    {
        return pack('C', bindec(implode('', [
            (int) $this->entity->globalColorTableExistance(),
            $this->encodeColorResolution(),
            (int) $this->entity->globalColorTableSorted(),
            $this->encodeGlobalColorTableSize(),
        ])));
    }
}
