<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Exceptions\DecoderException;

abstract class AbstractDecoder
{
    /**
     * Decode current source
     *
     * @return mixed
     */
    abstract public function decode(): mixed;

    /**
     * Create new instance
     *
     * @param resource $handle
     * @param null|int $length
     */
    public function __construct(protected $handle, protected ?int $length = null)
    {
    }

    /**
     * Set source to decode
     *
     * @param resource $handle
     */
    public function setHandle($handle): self
    {
        $this->handle = $handle;

        return $this;
    }

    /**
     * Read given number of bytes and move file pointer
     *
     * @param int $length
     * @throws DecoderException
     * @return string
     */
    protected function getNextBytesOrFail(int $length): string
    {
        $bytes = fread($this->handle, $length);

        if (strlen($bytes) !== $length) {
            throw new DecoderException('Unexpected end of file.');
        }

        return $bytes;
    }

    /**
     * Read given number of bytes and move pointer back to previous position
     *
     * @param int $length
     * @throws DecoderException
     * @return string
     */
    protected function viewNextBytesOrFail(int $length): string
    {
        $bytes = $this->getNextBytesOrFail($length);
        $this->movePointer($length * -1);

        return $bytes;
    }

    /**
     * Read next byte and move pointer back to previous position
     *
     * @throws DecoderException
     * @return string
     */
    protected function viewNextByteOrFail(): string
    {
        return $this->viewNextBytesOrFail(1);
    }

    /**
     * Read all remaining bytes from file handler
     *
     * @return string
     */
    protected function getRemainingBytes(): string
    {
        $all = '';
        do {
            $byte = fread($this->handle, 1);
            $all .= $byte;
        } while (!feof($this->handle));

        return $all;
    }

    /**
     * Get next byte in stream and move file pointer
     *
     * @throws DecoderException
     * @return string
     */
    protected function getNextByteOrFail(): string
    {
        return $this->getNextBytesOrFail(1);
    }

    /**
     * Move file pointer on handle by given offset
     *
     * @param int $offset
     * @return self
     */
    protected function movePointer(int $offset): self
    {
        fseek($this->handle, $offset, SEEK_CUR);

        return $this;
    }

    /**
     * Decode multi byte value
     *
     * @return int
     */
    protected function decodeMultiByte(string $bytes): int
    {
        return unpack('v*', $bytes)[1];
    }

    /**
     * Set length
     *
     * @param int $length
     */
    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length
     *
     * @return null|int
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * Get current handle position
     *
     * @return int
     */
    public function getPosition(): int
    {
        return ftell($this->handle);
    }
}
