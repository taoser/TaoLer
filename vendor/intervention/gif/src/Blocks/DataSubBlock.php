<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;
use Intervention\Gif\Exceptions\FormatException;

class DataSubBlock extends AbstractEntity
{
    /**
     * Create new instance
     *
     * @param string $value
     * @throws FormatException
     * @return void
     */
    public function __construct(protected string $value)
    {
        if ($this->getSize() > 255) {
            throw new FormatException(
                'Data Sub-Block can not have a block size larger than 255 bytes.'
            );
        }
    }

    /**
     * Return size of current block
     *
     * @return int
     */
    public function getSize(): int
    {
        return strlen($this->value);
    }

    /**
     * Return block value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
