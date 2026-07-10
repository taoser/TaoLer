<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;
use Intervention\Gif\Exceptions\InvalidArgumentException;

class LogicalScreenDescriptor extends AbstractEntity
{
    /**
     * Width.
     */
    protected int $width;

    /**
     * Height.
     */
    protected int $height;

    /**
     * Global color table flag.
     */
    protected bool $globalColorTableExistance = false;

    /**
     * Sort flag of global color table.
     */
    protected bool $globalColorTableSorted = false;

    /**
     * Size of global color table.
     */
    protected int $globalColorTableSize = 0;

    /**
     * Background color index.
     */
    protected int $backgroundColorIndex = 0;

    /**
     * Color resolution.
     */
    protected int $bitsPerPixel = 8;

    /**
     * Pixel aspect ration.
     */
    protected int $pixelAspectRatio = 0;

    /**
     * Set size.
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
     * Get width of current instance.
     */
    public function width(): int
    {
        return $this->width;
    }

    /**
     * Get height of current instance.
     */
    public function height(): int
    {
        return $this->height;
    }

    /**
     * Determine if global color table is present.
     */
    public function globalColorTableExistance(): bool
    {
        return $this->globalColorTableExistance;
    }

    /**
     * Alias of globalColorTableExistance.
     */
    public function hasGlobalColorTable(): bool
    {
        return $this->globalColorTableExistance();
    }

    /**
     * Set global color table flag.
     */
    public function setGlobalColorTableExistance(bool $existance = true): self
    {
        $this->globalColorTableExistance = $existance;

        return $this;
    }

    /**
     * Get global color table sorted flag.
     */
    public function globalColorTableSorted(): bool
    {
        return $this->globalColorTableSorted;
    }

    /**
     * Set global color table sorted flag.
     */
    public function setGlobalColorTableSorted(bool $sorted = true): self
    {
        $this->globalColorTableSorted = $sorted;

        return $this;
    }

    /**
     * Get size of global color table.
     */
    public function globalColorTableSize(): int
    {
        return $this->globalColorTableSize;
    }

    /**
     * Get byte size of global color table.
     */
    public function globalColorTableByteSize(): int
    {
        return 3 * pow(2, $this->globalColorTableSize() + 1);
    }

    /**
     * Set size of global color table.
     */
    public function setGlobalColorTableSize(int $size): self
    {
        $this->globalColorTableSize = $size;

        return $this;
    }

    /**
     * Get background color index.
     */
    public function backgroundColorIndex(): int
    {
        return $this->backgroundColorIndex;
    }

    /**
     * Set background color index.
     */
    public function setBackgroundColorIndex(int $index): self
    {
        $this->backgroundColorIndex = $index;

        return $this;
    }

    /**
     * Get current pixel aspect ration.
     */
    public function pixelAspectRatio(): int
    {
        return $this->pixelAspectRatio;
    }

    /**
     * Set pixel aspect ratio.
     */
    public function setPixelAspectRatio(int $ratio): self
    {
        $this->pixelAspectRatio = $ratio;

        return $this;
    }

    /**
     * Get color resolution.
     */
    public function bitsPerPixel(): int
    {
        return $this->bitsPerPixel;
    }

    /**
     * Set color resolution.
     */
    public function setBitsPerPixel(int $value): self
    {
        $this->bitsPerPixel = $value;

        return $this;
    }
}
