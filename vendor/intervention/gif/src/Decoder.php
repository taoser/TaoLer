<?php

declare(strict_types=1);

namespace Intervention\Gif;

use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\StreamException;
use Intervention\Gif\Exceptions\InvalidArgumentException;
use Intervention\Gif\Traits\CanHandleFiles;

class Decoder
{
    use CanHandleFiles;

    /**
     * Decode given input.
     *
     * @throws InvalidArgumentException
     * @throws StreamException
     * @throws DecoderException
     */
    public static function decode(mixed $input): GifDataStream
    {
        $stream = match (true) {
            self::isFilePath($input) => self::streamFromFilePath($input),
            is_string($input) => self::streamFromData($input),
            self::isStream($input) => $input,
            default => throw new InvalidArgumentException(
                'Decoder input must be either file path, stream resource or binary data'
            )
        };

        $result = rewind($stream);

        if ($result === false) {
            throw new StreamException('Failed to rewind stream');
        }

        return GifDataStream::decode($stream);
    }

    /**
     * Determine if input is stream resource.
     */
    private static function isStream(mixed $input): bool
    {
        return is_resource($input) && get_resource_type($input) === 'stream';
    }
}
