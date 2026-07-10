<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\ApplicationExtension;
use Intervention\Gif\Blocks\CommentExtension;
use Intervention\Gif\Blocks\FrameBlock;
use Intervention\Gif\Exceptions\EncoderException;

class FrameBlockEncoder extends AbstractEncoder
{
    /**
     * Create new decoder instance.
     */
    public function __construct(FrameBlock $entity)
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
        $graphicControlExtension = $this->entity->graphicControlExtension();
        $colorTable = $this->entity->colorTable();
        $plainTextExtension = $this->entity->plainTextExtension();

        return implode('', [
            implode('', array_map(
                fn(ApplicationExtension $extension): string => $extension->encode(),
                $this->entity->applicationExtensions(),
            )),
            implode('', array_map(
                fn(CommentExtension $extension): string => $extension->encode(),
                $this->entity->commentExtensions(),
            )),
            $plainTextExtension ? $plainTextExtension->encode() : '',
            $graphicControlExtension ? $graphicControlExtension->encode() : '',
            $this->entity->imageDescriptor()->encode(),
            $colorTable ? $colorTable->encode() : '',
            $this->entity->imageData()->encode(),
        ]);
    }
}
