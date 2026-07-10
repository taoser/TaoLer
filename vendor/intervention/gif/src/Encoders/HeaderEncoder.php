<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\Header;

class HeaderEncoder extends AbstractEncoder
{
    /**
     * Create new instance.
     */
    public function __construct(Header $entity)
    {
        parent::__construct($entity);
    }

    /**
     * Encode current entity.
     */
    public function encode(): string
    {
        return Header::SIGNATURE . $this->entity->version();
    }
}
