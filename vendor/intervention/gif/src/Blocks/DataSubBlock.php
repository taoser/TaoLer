<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;
use Intervention\Gif\Exceptions\InvalidArgumentException;

class DataSubBlock extends AbstractEntity
{
    /**
     * Create new instance.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(protected string $value)
    {
        if ($this->size() > 255) {
            throw new InvalidArgumentException(
                'Data Sub-Block can not have a block size larger than 255 bytes'
            );
        }
    }

    /**
     * Return size of current block.
     */
    public function size(): int
    {
        return strlen($this->value);
    }

    /**
     * Return block value.
     */
    public function value(): string
    {
        return $this->value;
    }
}
