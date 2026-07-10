<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\AbstractEntity;
use Intervention\Gif\Exceptions\DecoderException;

abstract class AbstractDecoder
{
    /**
     * Decode current source.
     */
    abstract public function decode(): AbstractEntity;

    /**
     * Create new instance.
     */
    public function __construct(protected mixed $stream, protected ?int $length = null)
    {
        //
    }

    /**
     * Set source to decode.
     */
    public function setStream(mixed $stream): self
    {
        $this->stream = $stream;

        return $this;
    }

    /**
     * Read given number of bytes and move stream position.
     *
     * @throws DecoderException
     */
    protected function nextBytesOrFail(int $length): string
    {
        if ($length < 1) {
            throw new DecoderException('The length of the next byte chain must be at least one byte');
        }

        $bytes = fread($this->stream, $length);
        if ($bytes === false || strlen($bytes) !== $length) {
            throw new DecoderException('Unexpected end of file');
        }

        return $bytes;
    }

    /**
     * Read given number of bytes and move stream position back to previous position.
     *
     * @throws DecoderException
     */
    protected function viewNextBytesOrFail(int $length): string
    {
        $bytes = $this->nextBytesOrFail($length);
        $this->moveStreamPosition($length * -1);

        return $bytes;
    }

    /**
     * Read next byte and move stream position back to previous position.
     *
     * @throws DecoderException
     */
    protected function viewNextByteOrFail(): string
    {
        return $this->viewNextBytesOrFail(1);
    }

    /**
     * Read all remaining bytes from stream.
     *
     * @throws DecoderException
     */
    protected function remainingBytes(): string
    {
        $contents = stream_get_contents($this->stream);

        if ($contents === false) {
            throw new DecoderException('Failed to read remaining bytes from stream');
        }

        return $contents;
    }

    /**
     * Get next byte in stream and move stream position.
     *
     * @throws DecoderException
     */
    protected function nextByteOrFail(): string
    {
        return $this->nextBytesOrFail(1);
    }

    /**
     * Move stream position by given offset.
     *
     * @throws DecoderException
     */
    protected function moveStreamPosition(int $offset): self
    {
        $result = fseek($this->stream, $offset, SEEK_CUR);

        if ($result !== 0) {
            throw new DecoderException('Failed to move stream position by offset ' . $offset);
        }

        return $this;
    }

    /**
     * Decode multi byte value.
     *
     * @throws DecoderException
     */
    protected function decodeMultiByte(string $bytes): int
    {
        $unpacked = unpack('v*', $bytes);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to decode given bytes');
        }

        return $unpacked[1];
    }

    /**
     * Set length.
     */
    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length.
     */
    public function length(): ?int
    {
        return $this->length;
    }

    /**
     * Get current stream position.
     *
     * @throws DecoderException
     */
    public function position(): int
    {
        $position = ftell($this->stream);

        if ($position === false) {
            throw new DecoderException('Failed to read current position from stream');
        }

        return $position;
    }
}
