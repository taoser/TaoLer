<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\Color;
use Intervention\Gif\Blocks\ColorTable;
use Intervention\Gif\Exceptions\EncoderException;

class ColorTableEncoder extends AbstractEncoder
{
    /**
     * Create new instance.
     */
    public function __construct(ColorTable $entity)
    {
        parent::__construct($entity);
    }

    /**
     * Encode current entity.
     *
     * @throws EncoderException
     */
    public function encode(): string
    {
        return implode('', array_map(
            fn(Color $color): string => $color->encode(),
            $this->entity->colors(),
        ));
    }
}
