<?php

declare(strict_types=1);

namespace Intervention\Gif\Traits;

use Intervention\Gif\Exceptions\StreamException;

trait CanHandleFiles
{
     /**
     * Determines if input is file path.
     */
    private static function isFilePath(mixed $input): bool
    {
        return is_string($input) && !self::hasNullBytes($input) && @is_file($input) === true;
    }

    /**
     * Determine if given string contains null bytes.
     */
    private static function hasNullBytes(string $string): bool
    {
        return str_contains($string, chr(0));
    }

    /**
     * Create stream resource from given gif image data.
     *
     * @throws StreamException
     */
    private static function streamFromData(string $data): mixed
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new StreamException('Failed to create tempory stream resource');
        }

        $result = fwrite($stream, $data);
        if ($result === false) {
            fclose($stream);
            throw new StreamException('Failed to write tempory stream resource');
        }

        $result = rewind($stream);
        if ($result === false) {
            fclose($stream);
            throw new StreamException('Failed to rewind tempory stream resource');
        }

        return $stream;
    }

    /**
     * Create stream resource from given file path.
     *
     * @throws StreamException
     */
    private static function streamFromFilePath(string $path): mixed
    {
        $stream = fopen($path, 'rb');

        if ($stream === false) {
            throw new StreamException('Failed to create stream resource from path');
        }

        return $stream;
    }
}
