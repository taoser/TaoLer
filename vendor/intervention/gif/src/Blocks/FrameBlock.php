<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;

class FrameBlock extends AbstractEntity
{
    /**
     * @var null|GraphicControlExtension $graphicControlExtension
     */
    protected ?GraphicControlExtension $graphicControlExtension = null;

    /**
     * @var null|ColorTable $colorTable
     */
    protected ?ColorTable $colorTable = null;

    /**
     * @var null|PlainTextExtension $plainTextExtension
     */
    protected ?PlainTextExtension $plainTextExtension = null;

    /**
     * @var array<ApplicationExtension> $applicationExtensions
     */
    protected array $applicationExtensions = [];

    /**
     * @var array<CommentExtension> $commentExtensions
     */
    protected array $commentExtensions = [];

    public function __construct(
        protected ImageDescriptor $imageDescriptor = new ImageDescriptor(),
        protected ImageData $imageData = new ImageData()
    ) {
    }

    public function addEntity(AbstractEntity $entity): self
    {
        switch (true) {
            case $entity instanceof TableBasedImage:
                $this->setTableBasedImage($entity);
                break;

            case $entity instanceof GraphicControlExtension:
                $this->setGraphicControlExtension($entity);
                break;

            case $entity instanceof ImageDescriptor:
                $this->setImageDescriptor($entity);
                break;

            case $entity instanceof ColorTable:
                $this->setColorTable($entity);
                break;

            case $entity instanceof ImageData:
                $this->setImageData($entity);
                break;

            case $entity instanceof PlainTextExtension:
                $this->setPlainTextExtension($entity);
                break;

            case $entity instanceof NetscapeApplicationExtension:
                $this->addApplicationExtension($entity);
                break;

            case $entity instanceof ApplicationExtension:
                $this->addApplicationExtension($entity);
                break;

            case $entity instanceof CommentExtension:
                $this->addCommentExtension($entity);
                break;
        }

        return $this;
    }

    /**
     * Return application extensions of current frame block
     *
     * @return array<ApplicationExtension>
     */
    public function getApplicationExtensions(): array
    {
        return $this->applicationExtensions;
    }

    /**
     * Return comment extensions of current frame block
     *
     * @return array<CommentExtension>
     */
    public function getCommentExtensions(): array
    {
        return $this->commentExtensions;
    }

    /**
     * Set the graphic control extension
     *
     * @param GraphicControlExtension $extension
     * @return self
     */
    public function setGraphicControlExtension(GraphicControlExtension $extension): self
    {
        $this->graphicControlExtension = $extension;

        return $this;
    }

    /**
     * Get the graphic control extension of the current frame block
     *
     * @return null|GraphicControlExtension
     */
    public function getGraphicControlExtension(): ?GraphicControlExtension
    {
        return $this->graphicControlExtension;
    }

    /**
     * Set the image descriptor
     *
     * @param ImageDescriptor $descriptor
     * @return self
     */
    public function setImageDescriptor(ImageDescriptor $descriptor): self
    {
        $this->imageDescriptor = $descriptor;
        return $this;
    }

    /**
     * Get the image descriptor of the frame block
     *
     * @return ImageDescriptor
     */
    public function getImageDescriptor(): ImageDescriptor
    {
        return $this->imageDescriptor;
    }

    /**
     * Set the color table of the current frame block
     *
     * @param ColorTable $table
     * @return FrameBlock
     */
    public function setColorTable(ColorTable $table): self
    {
        $this->colorTable = $table;

        return $this;
    }

    /**
     * Get color table
     *
     * @return null|ColorTable
     */
    public function getColorTable(): ?ColorTable
    {
        return $this->colorTable;
    }

    /**
     * Determine if frame block has color table
     *
     * @return bool
     */
    public function hasColorTable(): bool
    {
        return !is_null($this->colorTable);
    }

    /**
     * Set image data of frame block
     *
     * @param ImageData $data
     * @return self
     */
    public function setImageData(ImageData $data): self
    {
        $this->imageData = $data;

        return $this;
    }

    /**
     * Get image data of current frame block
     *
     * @return ImageData
     */
    public function getImageData(): ImageData
    {
        return $this->imageData;
    }

    /**
     * Set plain text extension
     *
     * @param PlainTextExtension $extension
     * @return self
     */
    public function setPlainTextExtension(PlainTextExtension $extension): self
    {
        $this->plainTextExtension = $extension;

        return $this;
    }

    /**
     * Get plain text extension
     *
     * @return null|PlainTextExtension
     */
    public function getPlainTextExtension(): ?PlainTextExtension
    {
        return $this->plainTextExtension;
    }

    /**
     * Add given application extension to the current frame block
     *
     * @param ApplicationExtension $extension
     * @return self
     */
    public function addApplicationExtension(ApplicationExtension $extension): self
    {
        $this->applicationExtensions[] = $extension;

        return $this;
    }

    /**
     * Add given comment extension to the current frame block
     *
     * @param CommentExtension $extension
     * @return self
     */
    public function addCommentExtension(CommentExtension $extension): self
    {
        $this->commentExtensions[] = $extension;

        return $this;
    }

    /**
     * Return netscape extension of the frame block if available
     *
     * @return null|NetscapeApplicationExtension
     */
    public function getNetscapeExtension(): ?NetscapeApplicationExtension
    {
        $extensions = array_filter($this->applicationExtensions, function ($extension) {
            return $extension instanceof NetscapeApplicationExtension;
        });

        return count($extensions) ? reset($extensions) : null;
    }

    /**
     * Set the table based image of the current frame block
     *
     * @param TableBasedImage $tableBasedImage
     * @return self
     */
    public function setTableBasedImage(TableBasedImage $tableBasedImage): self
    {
        $this->setImageDescriptor($tableBasedImage->getImageDescriptor());

        if ($colorTable = $tableBasedImage->getColorTable()) {
            $this->setColorTable($colorTable);
        }

        $this->setImageData($tableBasedImage->getImageData());

        return $this;
    }
}
