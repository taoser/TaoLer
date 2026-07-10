<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;

class ImageData extends AbstractEntity
{
    /**
     * LZW min. code size.
     */
    protected int $lzwMinCodeSize;

    /**
     * Sub blocks.
     *
     * @var array<DataSubBlock>
     */
    protected array $blocks = [];

    /**
     * Get LZW min. code size.
     */
    public function lzwMinCodeSize(): int
    {
        return $this->lzwMinCodeSize;
    }

    /**
     * Set lzw min. code size.
     */
    public function setLzwMinCodeSize(int $size): self
    {
        $this->lzwMinCodeSize = $size;

        return $this;
    }

    /**
     * Get current data sub blocks.
     *
     * @return array<DataSubBlock>
     */
    public function blocks(): array
    {
        return $this->blocks;
    }

    /**
     * Addd sub block.
     */
    public function addBlock(DataSubBlock $block): self
    {
        $this->blocks[] = $block;

        return $this;
    }

    /**
     * Determine if data sub blocks are present.
     */
    public function hasBlocks(): bool
    {
        return count($this->blocks) >= 1;
    }
}
