<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\TableBasedImage;

class TableBasedImageEncoder extends AbstractEncoder
{
    /**
     * Create new instance.
     */
    public function __construct(TableBasedImage $entity)
    {
        parent::__construct($entity);
    }

    /**
     * Encode current entity.
     */
    public function encode(): string
    {
        return implode('', [
            $this->entity->imageDescriptor()->encode(),
            $this->entity->colorTable() ? $this->entity->colorTable()->encode() : '',
            $this->entity->imageData()->encode(),
        ]);
    }
}
