<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractExtension;

class ApplicationExtension extends AbstractExtension
{
    public const LABEL = "\xFF";

    /**
     * Application Identifier & Auth Code
     *
     * @var string
     */
    protected string $application = '';

    /**
     * Data Sub Blocks
     *
     * @var array<DataSubBlock>
     */
    protected array $blocks = [];

    /**
     * Get size of block
     *
     * @return int
     */
    public function getBlockSize(): int
    {
        return strlen($this->application);
    }

    /**
     * Set application name
     *
     * @param string $value
     * @return ApplicationExtension
     */
    public function setApplication(string $value): self
    {
        $this->application = $value;

        return $this;
    }

    /**
     * Get application name
     *
     * @return string
     */
    public function getApplication(): string
    {
        return $this->application;
    }

    /**
     * Add block to application extension
     *
     * @param DataSubBlock $block
     * @return ApplicationExtension
     */
    public function addBlock(DataSubBlock $block): self
    {
        $this->blocks[] = $block;

        return $this;
    }

    /**
     *  Set data sub blocks of instance
     *
     * @param array<DataSubBlock> $blocks
     * @return ApplicationExtension
     */
    public function setBlocks(array $blocks): self
    {
        $this->blocks = $blocks;

        return $this;
    }

    /**
     * Get blocks of ApplicationExtension
     *
     * @return array<DataSubBlock>
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }
}
