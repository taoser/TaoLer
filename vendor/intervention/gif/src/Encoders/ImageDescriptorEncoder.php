<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\ImageDescriptor;

class ImageDescriptorEncoder extends AbstractEncoder
{
    /**
     * Create new instance.
     */
    public function __construct(ImageDescriptor $entity)
    {
        parent::__construct($entity);
    }

    /**
     * Encode current entity.
     */
    public function encode(): string
    {
        return implode('', [
            ImageDescriptor::SEPARATOR,
            $this->encodeLeft(),
            $this->encodeTop(),
            $this->encodeWidth(),
            $this->encodeHeight(),
            $this->encodePackedField(),
        ]);
    }

    /**
     * Encode left value.
     */
    private function encodeLeft(): string
    {
        return pack('v*', $this->entity->left());
    }

    /**
     * Encode top value.
     */
    private function encodeTop(): string
    {
        return pack('v*', $this->entity->top());
    }

    /**
     * Encode width value.
     */
    private function encodeWidth(): string
    {
        return pack('v*', $this->entity->width());
    }

    /**
     * Encode height value.
     */
    private function encodeHeight(): string
    {
        return pack('v*', $this->entity->height());
    }

    /**
     * Encode size of local color table.
     */
    private function encodeLocalColorTableSize(): string
    {
        return str_pad(decbin($this->entity->localColorTableSize()), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Encode reserved field.
     */
    private function encodeReservedField(): string
    {
        return str_pad('0', 2, '0', STR_PAD_LEFT);
    }

    /**
     * Encode packed field.
     */
    private function encodePackedField(): string
    {
        return pack('C', bindec(implode('', [
            (int) $this->entity->localColorTableExistance(),
            (int) $this->entity->isInterlaced(),
            (int) $this->entity->localColorTableSorted(),
            $this->encodeReservedField(),
            $this->encodeLocalColorTableSize(),
        ])));
    }
}
