<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\ApplicationExtension;
use Intervention\Gif\Blocks\DataSubBlock;
use Intervention\Gif\Exceptions\EncoderException;

class ApplicationExtensionEncoder extends AbstractEncoder
{
    /**
     * Create new decoder instance.
     */
    public function __construct(ApplicationExtension $entity)
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
        return implode('', [
            ApplicationExtension::MARKER,
            ApplicationExtension::LABEL,
            pack('C', $this->entity->blockSize()),
            $this->entity->application(),
            implode('', array_map(fn(DataSubBlock $block): string => $block->encode(), $this->entity->blocks())),
            ApplicationExtension::TERMINATOR,
        ]);
    }
}
