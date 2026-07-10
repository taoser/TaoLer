<?php

declare(strict_types=1);

namespace Intervention\Gif\Traits;

use Intervention\Gif\Decoders\AbstractDecoder;
use Intervention\Gif\Exceptions\DecoderException;

trait CanDecode
{
    /**
     * Decode current instance.
     *
     * @throws DecoderException
     */
    public static function decode(mixed $source, ?int $length = null): mixed
    {
        return self::decoder($source, $length)->decode();
    }

    /**
     * Get decoder for current instance.
     *
     * @throws DecoderException
     */
    protected static function decoder(mixed $source, ?int $length = null): AbstractDecoder
    {
        $classname = sprintf('Intervention\Gif\Decoders\%sDecoder', self::shortClassname());

        if (!class_exists($classname)) {
            throw new DecoderException('Decoder for "' . static::class . '" not found');
        }

        $decoder = new $classname($source, $length);

        if (!($decoder instanceof AbstractDecoder)) {
            throw new DecoderException('Decoder for "' . static::class . '" not found');
        }

        return $decoder;
    }
}
