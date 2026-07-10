<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\GraphicControlExtension;

class GraphicControlExtensionEncoder extends AbstractEncoder
{
    /**
     * Create new instance.
     */
    public function __construct(GraphicControlExtension $entity)
    {
        parent::__construct($entity);
    }

    /**
     * Encode current entity.
     */
    public function encode(): string
    {
        return implode('', [
            GraphicControlExtension::MARKER,
            GraphicControlExtension::LABEL,
            GraphicControlExtension::BLOCKSIZE,
            $this->encodePackedField(),
            $this->encodeDelay(),
            $this->encodeTransparentColorIndex(),
            GraphicControlExtension::TERMINATOR,
        ]);
    }

    /**
     * Encode delay time.
     */
    private function encodeDelay(): string
    {
        return pack('v*', $this->entity->delay());
    }

    /**
     * Encode transparent color index.
     */
    private function encodeTransparentColorIndex(): string
    {
        return pack('C', $this->entity->transparentColorIndex());
    }

    /**
     * Encode packed field.
     */
    private function encodePackedField(): string
    {
        return pack('C', bindec(implode('', [
            str_pad('0', 3, '0', STR_PAD_LEFT),
            str_pad(decbin($this->entity->disposalMethod()->value), 3, '0', STR_PAD_LEFT),
            (int) $this->entity->userInput(),
            (int) $this->entity->transparentColorExistance(),
        ])));
    }
}
