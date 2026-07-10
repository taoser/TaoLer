<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;
use Intervention\Gif\Exceptions\InvalidArgumentException;

class ImageDescriptor extends AbstractEntity
{
    public const SEPARATOR = "\x2C";

    /**
     * Width of frame.
     */
    protected int $width = 0;

    /**
     * Height of frame.
     */
    protected int $height = 0;

    /**
     * Left position of frame.
     */
    protected int $left = 0;

    /**
     * Top position of frame.
     */
    protected int $top = 0;

    /**
     * Determine if frame is interlaced.
     */
    protected bool $interlaced = false;

    /**
     * Local color table flag.
     */
    protected bool $localColorTableExistance = false;

    /**
     * Sort flag of local color table.
     */
    protected bool $localColorTableSorted = false;

    /**
     * Size of local color table.
     */
    protected int $localColorTableSize = 0;

    /**
     * Get current width.
     */
    public function width(): int
    {
        return $this->width;
    }

    /**
     * Get current width.
     */
    public function height(): int
    {
        return $this->height;
    }

    /**
     * Get current Top.
     */
    public function top(): int
    {
        return $this->top;
    }

    /**
     * Get current Left.
     */
    public function left(): int
    {
        return $this->left;
    }

    /**
     * Set size of current instance.
     *
     * @throws InvalidArgumentException
     */
    public function setSize(int $width, int $height): self
    {
        if ($width <= 0) {
            throw new InvalidArgumentException('Width in ' . $this::class . ' must be larger than 0');
        }

        if ($height <= 0) {
            throw new InvalidArgumentException('Height in ' . $this::class . ' must be larger than 0');
        }

        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Set position of current instance.
     */
    public function setPosition(int $left, int $top): self
    {
        $this->left = $left;
        $this->top = $top;

        return $this;
    }

    /**
     * Determine if frame is interlaced.
     */
    public function isInterlaced(): bool
    {
        return $this->interlaced;
    }

    /**
     * Set or unset interlaced value.
     */
    public function setInterlaced(bool $value = true): self
    {
        $this->interlaced = $value;

        return $this;
    }

    /**
     * Determine if local color table is present.
     */
    public function localColorTableExistance(): bool
    {
        return $this->localColorTableExistance;
    }

    /**
     * Alias for localColorTableExistance.
     */
    public function hasLocalColorTable(): bool
    {
        return $this->localColorTableExistance();
    }

    /**
     * Set local color table flag.
     */
    public function setLocalColorTableExistance(bool $existance = true): self
    {
        $this->localColorTableExistance = $existance;

        return $this;
    }

    /**
     * Get local color table sorted flag.
     */
    public function localColorTableSorted(): bool
    {
        return $this->localColorTableSorted;
    }

    /**
     * Set local color table sorted flag.
     */
    public function setLocalColorTableSorted(bool $sorted = true): self
    {
        $this->localColorTableSorted = $sorted;

        return $this;
    }

    /**
     * Get size of local color table.
     */
    public function localColorTableSize(): int
    {
        return $this->localColorTableSize;
    }

    /**
     * Get byte size of global color table.
     */
    public function localColorTableByteSize(): int
    {
        return 3 * pow(2, $this->localColorTableSize() + 1);
    }

    /**
     * Set size of local color table.
     */
    public function setLocalColorTableSize(int $size): self
    {
        $this->localColorTableSize = $size;

        return $this;
    }
}
