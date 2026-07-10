<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractExtension;
use Intervention\Gif\Exceptions\StateException;

class ApplicationExtension extends AbstractExtension
{
    public const LABEL = "\xFF";

    /**
     * Application Identifier & Auth Code.
     */
    protected string $application = '';

    /**
     * Data Sub Blocks.
     *
     * @var array<DataSubBlock>
     */
    protected array $blocks = [];

    /**
     * Get size of block.
     */
    public function blockSize(): int
    {
        return strlen($this->application);
    }

    /**
     * Set application name.
     */
    public function setApplication(string $value): self
    {
        $this->application = $value;

        return $this;
    }

    /**
     * Get application name.
     */
    public function application(): string
    {
        return $this->application;
    }

    /**
     * Add block to application extension.
     */
    public function addBlock(DataSubBlock $block): self
    {
        $this->blocks[] = $block;

        return $this;
    }

    /**
     *  Set data sub blocks of instance.
     *
     * @param array<DataSubBlock> $blocks
     */
    public function setBlocks(array $blocks): self
    {
        $this->blocks = $blocks;

        return $this;
    }

    /**
     * Get blocks of ApplicationExtension.
     *
     * @return array<DataSubBlock>
     */
    public function blocks(): array
    {
        return $this->blocks;
    }

    /**
     * Get first block of ApplicationExtension.
     *
     * @throws StateException
     */
    public function firstBlock(): DataSubBlock
    {
        if (!array_key_exists(0, $this->blocks)) {
            throw new StateException('Failed to retrieve data sub block');
        }

        return $this->blocks[0];
    }
}
