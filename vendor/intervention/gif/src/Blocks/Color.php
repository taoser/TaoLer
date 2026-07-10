<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;
use Intervention\Gif\Exceptions\InvalidArgumentException;

class Color extends AbstractEntity
{
    /**
     * Create new instance.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        protected int $r = 0,
        protected int $g = 0,
        protected int $b = 0
    ) {
        if ($r < 0 || $r > 255) {
            throw new InvalidArgumentException('Color channel red must be in range 0 to 255');
        }

        if ($g < 0 || $g > 255) {
            throw new InvalidArgumentException('Color channel green must be in range 0 to 255');
        }

        if ($b < 0 || $b > 255) {
            throw new InvalidArgumentException('Color channel blue must be in range 0 to 255');
        }
    }

    /**
     * Get red value.
     */
    public function red(): int
    {
        return $this->r;
    }

    /**
     * Set red value.
     */
    public function setRed(int $value): self
    {
        $this->r = $value;

        return $this;
    }

    /**
     * Get green value.
     */
    public function green(): int
    {
        return $this->g;
    }

    /**
     * Set green value.
     */
    public function setGreen(int $value): self
    {
        $this->g = $value;

        return $this;
    }

    /**
     * Get blue value.
     */
    public function blue(): int
    {
        return $this->b;
    }

    /**
     * Set blue value.
     */
    public function setBlue(int $value): self
    {
        $this->b = $value;

        return $this;
    }

    /**
     * Return hash value of current color.
     */
    public function hash(): string
    {
        return md5(strval($this->r . $this->g . $this->b));
    }
}
