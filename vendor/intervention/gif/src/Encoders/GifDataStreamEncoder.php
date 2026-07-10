<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\CommentExtension;
use Intervention\Gif\Blocks\FrameBlock;
use Intervention\Gif\Exceptions\EncoderException;
use Intervention\Gif\GifDataStream;

class GifDataStreamEncoder extends AbstractEncoder
{
    /**
     * Create new instance.
     */
    public function __construct(GifDataStream $entity)
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
            $this->entity->header()->encode(),
            $this->entity->logicalScreenDescriptor()->encode(),
            $this->maybeEncodeGlobalColorTable(),
            $this->encodeFrames(),
            $this->encodeComments(),
            $this->entity->trailer()->encode(),
        ]);
    }

    /**
     * Decode global color table if present in gif data.
     */
    private function maybeEncodeGlobalColorTable(): string
    {
        if (!$this->entity->hasGlobalColorTable()) {
            return '';
        }

        return $this->entity->globalColorTable()->encode();
    }

    /**
     * Encode data blocks of source.
     *
     * @throws EncoderException
     */
    private function encodeFrames(): string
    {
        return implode('', array_map(
            fn(FrameBlock $frame): string => $frame->encode(),
            $this->entity->frames(),
        ));
    }

    /**
     * Encode comment extension blocks of source.
     *
     * @throws EncoderException
     */
    private function encodeComments(): string
    {
        return implode('', array_map(
            fn(CommentExtension $commentExtension): string => $commentExtension->encode(),
            $this->entity->comments()
        ));
    }
}
