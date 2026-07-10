<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\DataSubBlock;

class DataSubBlockEncoder extends AbstractEncoder
{
    /**
     * Create new instance.
     */
    public function __construct(DataSubBlock $entity)
    {
        parent::__construct($entity);
    }

    /**
     * Encode current entity.
     */
    public function encode(): string
    {
        return pack('C', $this->entity->size()) . $this->entity->value();
    }
}
