<?php

declare(strict_types=1);

namespace Intervention\Gif;

use Intervention\Gif\Blocks\FrameBlock;
use Intervention\Gif\Blocks\GraphicControlExtension;
use Intervention\Gif\Blocks\ImageDescriptor;
use Intervention\Gif\Blocks\NetscapeApplicationExtension;
use Intervention\Gif\Blocks\TableBasedImage;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\EncoderException;
use Intervention\Gif\Exceptions\StreamException;
use Intervention\Gif\Exceptions\InvalidArgumentException;
use Intervention\Gif\Exceptions\StateException;
use Intervention\Gif\Traits\CanHandleFiles;

class Builder
{
    use CanHandleFiles;

    /**
     * Create new instance.
     */
    public function __construct(protected GifDataStream $gif = new GifDataStream())
    {
        //
    }

    /**
     * Create new canvas.
     *
     * @throws InvalidArgumentException
     */
    public static function canvas(int $width, int $height): self
    {
        return (new self())->setSize($width, $height);
    }

    /**
     * Get GifDataStream object we're currently building.
     */
    public function gifDataStream(): GifDataStream
    {
        return $this->gif;
    }

    /**
     * Set canvas size of gif.
     *
     * @throws InvalidArgumentException
     */
    public function setSize(int $width, int $height): self
    {
        $this->gif->logicalScreenDescriptor()->setSize($width, $height);

        return $this;
    }

    /**
     * Set loop count.
     *
     * @throws StateException
     * @throws InvalidArgumentException
     */
    public function setLoops(int $loops): self
    {
        if ($loops < 0) {
            throw new InvalidArgumentException('The loop count must be equal to or greater than 0');
        }

        if ($this->gif->frames() === []) {
            throw new StateException('Add at least one frame before setting the loop count');
        }

        // with one single loop the netscape extension must be removed otherwise the
        // gif is looped twice because the first repetition always takes place
        if ($loops === 1) {
            $this->gif->firstFrame()?->clearApplicationExtensions();
            return $this;
        }

        // make sure a netscape extension is present to store the loop count
        if ($this->gif->firstFrame()?->netscapeExtension() === null) {
            $this->gif->firstFrame()?->addApplicationExtension(
                new NetscapeApplicationExtension()
            );
        }

        // the loop count is reduced by one because what is referred to here as
        // the “loop count” actually means repetitions in GIF format, and thus
        // the first repetition always takes place. A loop count of 0 howerver
        // means infinite repetitions and remains unaltered.
        $loops = $loops === 0 ? $loops : $loops - 1;

        // add loop count to netscape extension on first frame
        $this->gif->firstFrame()?->netscapeExtension()?->setLoops($loops);

        return $this;
    }

    /**
     * Create new animation frame from given source which can be path to a file or GIF image data.
     *
     * @throws DecoderException
     * @throws StreamException
     * @throws InvalidArgumentException
     */
    public function addFrame(
        mixed $source,
        float $delay = 0,
        int $left = 0,
        int $top = 0,
        bool $interlaced = false
    ): self {
        $frame = new FrameBlock();
        $source = Decoder::decode($source);

        // store delay
        $frame->setGraphicControlExtension(
            $this->buildGraphicControlExtension(
                $source,
                intval($delay * 100)
            )
        );

        // store image
        $frame->setTableBasedImage(
            $this->buildTableBasedImage($source, $left, $top, $interlaced)
        );

        // add frame
        $this->gif->addFrame($frame);

        return $this;
    }

    /**
     * Build new graphic control extension with given delay & disposal method
     */
    protected function buildGraphicControlExtension(
        GifDataStream $source,
        int $delay,
        DisposalMethod $disposalMethod = DisposalMethod::BACKGROUND
    ): GraphicControlExtension {
        // create extension
        $extension = new GraphicControlExtension($delay, $disposalMethod);

        // set transparency index
        $control = $source->firstFrame()?->graphicControlExtension();
        if ($control !== null && $control->transparentColorExistance()) {
            $extension->setTransparentColorExistance();
            $extension->setTransparentColorIndex(
                $control->transparentColorIndex()
            );
        }

        return $extension;
    }

    /**
     * Build table based image object from given source.
     *
     * @throws DecoderException
     */
    protected function buildTableBasedImage(
        GifDataStream $source,
        int $left,
        int $top,
        bool $interlaced
    ): TableBasedImage {
        $block = new TableBasedImage();
        $block->setImageDescriptor(new ImageDescriptor());

        // set global color table from source as local color table
        $block->imageDescriptor()->setLocalColorTableExistance();
        $globalColorTable = $source->globalColorTable();

        if ($globalColorTable === null) {
            throw new DecoderException(
                'Failed to build table based image. Unable to find global color table in gif data stream',
            );
        }

        $block->setColorTable($globalColorTable);

        $block->imageDescriptor()->setLocalColorTableSorted(
            $source->logicalScreenDescriptor()->globalColorTableSorted()
        );

        try {
            $block->imageDescriptor()->setLocalColorTableSize(
                $source->logicalScreenDescriptor()->globalColorTableSize()
            );

            $block->imageDescriptor()->setSize(
                $source->logicalScreenDescriptor()->width(),
                $source->logicalScreenDescriptor()->height()
            );
        } catch (InvalidArgumentException $e) {
            throw new DecoderException(
                'Failed to decode image source',
                previous: $e
            );
        }

        // set position
        $block->imageDescriptor()->setPosition($left, $top);

        // set interlaced flag
        $block->imageDescriptor()->setInterlaced($interlaced);

        // add image data from source
        $block->setImageData(
            $source->firstFrame()?->imageData() ?: throw new DecoderException(
                'Failed to build table based image. Unable to find image data',
            )
        );

        return $block;
    }

    /**
     * Encode the current build.
     *
     * @throws EncoderException
     */
    public function encode(): string
    {
        return $this->gif->encode();
    }
}
