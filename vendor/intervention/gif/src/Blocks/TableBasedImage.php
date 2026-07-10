<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;

class TableBasedImage extends AbstractEntity
{
    protected ImageDescriptor $imageDescriptor;
    protected ?ColorTable $colorTable = null;
    protected ImageData $imageData;

    /**
     * Get image descriptor.
     */
    public function imageDescriptor(): ImageDescriptor
    {
        return $this->imageDescriptor;
    }

    /**
     * Set image descriptor for current instance.
     */
    public function setImageDescriptor(ImageDescriptor $descriptor): self
    {
        $this->imageDescriptor = $descriptor;

        return $this;
    }

    /**
     * Get image data.
     */
    public function imageData(): ImageData
    {
        return $this->imageData;
    }

    /**
     * Set image data for current instance.
     */
    public function setImageData(ImageData $data): self
    {
        $this->imageData = $data;

        return $this;
    }

    /**
     * Get current color table or null of table based based image has none.
     */
    public function colorTable(): ?ColorTable
    {
        return $this->colorTable;
    }

    /**
     * Set color table.
     */
    public function setColorTable(ColorTable $table): self
    {
        $this->colorTable = $table;

        return $this;
    }
}
