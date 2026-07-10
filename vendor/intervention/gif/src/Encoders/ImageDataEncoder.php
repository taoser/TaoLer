<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\AbstractEntity;
use Intervention\Gif\Blocks\DataSubBlock;
use Intervention\Gif\Blocks\ImageData;
use Intervention\Gif\Exceptions\EncoderException;
use Intervention\Gif\Exceptions\StateException;

class ImageDataEncoder extends AbstractEncoder
{
    /**
     * Create new instance.
     */
    public function __construct(ImageData $entity)
    {
        parent::__construct($entity);
    }

    /**
     * Encode current entity.
     *
     * @throws EncoderException
     * @throws StateException
     */
    public function encode(): string
    {
        if (!$this->entity->hasBlocks()) {
            throw new StateException('No data blocks in image data');
        }

        return implode('', [
            pack('C', $this->entity->lzwMinCodeSize()),
            implode('', array_map(
                fn(DataSubBlock $block): string => $block->encode(),
                $this->entity->blocks(),
            )),
            AbstractEntity::TERMINATOR,
        ]);
    }
}
