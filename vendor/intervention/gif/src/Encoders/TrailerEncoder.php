<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\Trailer;

class TrailerEncoder extends AbstractEncoder
{
    /**
     * Create new instance.
     */
    public function __construct(Trailer $entity)
    {
        parent::__construct($entity);
    }

    /**
     * Encode current entity.
     */
    public function encode(): string
    {
        return Trailer::MARKER;
    }
}
