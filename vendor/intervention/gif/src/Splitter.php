<?php

declare(strict_types=1);

namespace Intervention\Gif;

use ArrayIterator;
use GdImage;
use Intervention\Gif\Exceptions\CoreException;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\EncoderException;
use Intervention\Gif\Exceptions\StreamException;
use Intervention\Gif\Exceptions\InvalidArgumentException;
use Intervention\Gif\Exceptions\SplitterException;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<GifDataStream|GdImage>
 */
class Splitter implements IteratorAggregate
{
    /**
     * Single frames resolved from main GifDataStream.
     *
     * @var array<GifDataStream|GdImage>
     */
    protected array $frames = [];

    /**
     * Delays of each frame resolved from main GifDataStream.
     *
     * @var array<int>
     */
    protected array $delays = [];

    /**
     * Loop count of main GifDataStream.
     */
    protected int $loops;

    /**
     * Create new instance.
     *
     * @throws SplitterException
     */
    public function __construct(protected GifDataStream $gif)
    {
        try {
            $this->loops = $gif->mainApplicationExtension()?->loops() ?: 0;
        } catch (DecoderException $e) {
            throw new SplitterException('Failed to create instance from ' . GifDataStream::class, previous: $e);
        }
    }

    /**
     * Create splitter instance from gif data stream object.
     *
     * @throws SplitterException
     */
    public static function create(GifDataStream $stream): self
    {
        return new self($stream);
    }

    /**
     * Create splitter instance from raw binary gif image data.
     *
     * @throws SplitterException
     * @throws InvalidArgumentException
     * @throws StreamException
     * @throws DecoderException
     */
    public static function decode(mixed $input): self
    {
        return new self(Decoder::decode($input));
    }

    /**
     * Iterate over the frames and pass each frame to a closure.
     */
    public function each(callable $callback): self
    {
        array_map($callback, $this->frames, $this->delays);

        return $this;
    }

    /**
     * Set stream of instance.
     */
    public function setStream(GifDataStream $stream): self
    {
        $this->gif = $stream;

        return $this;
    }

    /**
     * Build iterator.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->frames);
    }

    /**
     * Get frames.
     *
     * @return array<GifDataStream|GdImage>
     */
    public function frames(): array
    {
        return $this->frames;
    }

    /**
     * Get delays.
     *
     * @return array<int>
     */
    public function delays(): array
    {
        return $this->delays;
    }

    /**
     * Get loop count of currently handled gif data.
     */
    public function loops(): int
    {
        return $this->loops;
    }

    /**
     * Split current stream into array of seperate gif data stream objects for each frame.
     *
     * @throws SplitterException
     */
    public function split(): self
    {
        $this->frames = [];

        foreach ($this->gif->frames() as $frame) {
            // create separate stream for each frame
            try {
                $gif = Builder::canvas(
                    $this->gif->logicalScreenDescriptor()->width(),
                    $this->gif->logicalScreenDescriptor()->height(),
                )->gifDataStream();
            } catch (InvalidArgumentException $e) {
                throw new SplitterException('Failed to create separate stream resource for each frame', previous: $e);
            }

            // check if working stream has global color table
            $table = $this->gif->globalColorTable();
            if ($table !== null) {
                $gif->setGlobalColorTable($table);
                $gif->logicalScreenDescriptor()->setGlobalColorTableExistance(true);

                $gif->logicalScreenDescriptor()->setGlobalColorTableSorted(
                    $this->gif->logicalScreenDescriptor()->globalColorTableSorted(),
                );

                $gif->logicalScreenDescriptor()->setGlobalColorTableSize(
                    $this->gif->logicalScreenDescriptor()->globalColorTableSize(),
                );

                $gif->logicalScreenDescriptor()->setBackgroundColorIndex(
                    $this->gif->logicalScreenDescriptor()->backgroundColorIndex(),
                );

                $gif->logicalScreenDescriptor()->setPixelAspectRatio(
                    $this->gif->logicalScreenDescriptor()->pixelAspectRatio(),
                );

                $gif->logicalScreenDescriptor()->setBitsPerPixel(
                    $this->gif->logicalScreenDescriptor()->bitsPerPixel(),
                );
            }

            // copy original frame
            $gif->addFrame($frame);

            $this->frames[] = $gif;
            $this->delays[] = match (is_object($frame->graphicControlExtension())) {
                true => $frame->graphicControlExtension()->delay(),
                default => 0,
            };
        }

        return $this;
    }

    /**
     * Transform current frames to an array of transparency flattened GdImage objects for each frame.
     *
     * @throws SplitterException
     * @throws CoreException
     */
    public function flatten(): self
    {
        $frames = $this->unprocessedFramesOrFail();
        $gdImages = $this->extractFrames();

        // non-animated gif files don't need to be flattened
        // just replace frames with extracted
        if (count($frames) === 1) {
            $this->frames = $gdImages;

            return $this;
        }

        // get main image size
        $width = imagesx($gdImages[0]);
        $height = imagesy($gdImages[0]);

        foreach ($gdImages as $key => $gdImage) {
            // first frame has no previous frame to fall back to
            if ($key === 0) {
                imagealphablending($gdImage, true);
                $gdImages[$key] = $gdImage;
                continue;
            }

            // get meta data of frame
            $descriptor = $frames[$key]->firstFrame()?->imageDescriptor();
            $offsetX = $descriptor?->left() ?: 0;
            $offsetY = $descriptor?->top() ?: 0;
            $w = $descriptor?->width() ?: 0;
            $h = $descriptor?->height() ?: 0;

            try {
                // create normalized, transparency filled canvas
                $canvas = $this->createTransparentCanvas($width, $height, $gdImage, $key);
            } catch (InvalidArgumentException $e) {
                throw new SplitterException('Failed to flatten image', previous: $e);
            }

            // whether the previous frame remains visible as the base of this frame
            // depends on the previous frame's disposal method, not this frame's own
            $previousDisposalMethod = $this->disposalMethod($frames[$key - 1]);

            if (in_array($previousDisposalMethod, [DisposalMethod::NONE, DisposalMethod::PREVIOUS])) {
                // insert last as base
                imagecopy($canvas, $gdImages[$key - 1], 0, 0, 0, 0, $width, $height);
            }

            // insert gd image
            imagecopy($canvas, $gdImage, $offsetX, $offsetY, 0, 0, $w, $h);

            $gdImages[$key] = $canvas;
        }

        $this->frames = $gdImages;

        return $this;
    }

    /**
     * Create a truecolor canvas of given size, filled entirely with a transparent color
     * resolved from the given gd image.
     *
     * @throws InvalidArgumentException
     * @throws CoreException
     */
    private function createTransparentCanvas(int $width, int $height, GdImage $gdImage, int $key): GdImage
    {
        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Width and height must be greater than 0');
        }

        $canvas = imagecreatetruecolor($width, $height);

        if ($canvas === false) {
            throw new CoreException('Failed to create new image instance for animation frame #' . $key);
        }

        $transparent = imagecolortransparent($gdImage) !== -1
            ? imagecolortransparent($gdImage)
            : imagecolorallocatealpha($gdImage, 255, 0, 255, 127);

        if (!is_int($transparent)) {
            throw new CoreException('Failed to allocate transparent color in animation frame #' . $key);
        }

        imagefill($canvas, 0, 0, $transparent);
        imagecolortransparent($canvas, $transparent);
        imagealphablending($canvas, true);

        return $canvas;
    }

    /**
     * Return array of GdImage objects for each frame.
     *
     * @throws CoreException
     * @throws SplitterException
     * @return array<GdImage>
     */
    private function extractFrames(): array
    {
        $gdImages = [];

        foreach ($this->unprocessedFramesOrFail() as $frame) {
            try {
                $gdImage = imagecreatefromstring($frame->encode());
            } catch (EncoderException) {
                throw new CoreException('Failed to extract animation frame to GdImage object');
            }

            if ($gdImage === false) {
                throw new CoreException('Failed to extract animation frame to GdImage object');
            }

            imagepalettetotruecolor($gdImage);
            imagesavealpha($gdImage, true);

            $gdImages[] = $gdImage;
        }

        return $gdImages;
    }

    /**
     * Find and return disposal method of given gif data stream.
     *
     * @throws SplitterException
     */
    private function disposalMethod(GifDataStream $gif): DisposalMethod
    {
        $disposalMethod = $gif->firstFrame()?->graphicControlExtension()?->disposalMethod();

        return $disposalMethod ?: throw new SplitterException('Failed to find disposal method in gif data stream');
    }

    /**
     * Return array of unprocessed frames or throw exception if frames are already processed.
     *
     * @throws SplitterException
     * @return array<GifDataStream>
     */
    private function unprocessedFramesOrFail(): array
    {
        if (count($this->frames) === 0) {
            throw new SplitterException('No frames available. Run ' . $this::class . '::split() first');
        }

        // if any frame is instanceof GdImage, frame was already flattened
        $processed = count(array_filter(
            $this->frames,
            fn(GdImage|GifDataStream $frame): bool => $frame instanceof GdImage,
        )) > 0;

        if ($processed) {
            throw new SplitterException('Frames have already been flattened');
        }

        return array_filter(
            $this->frames,
            fn(GifDataStream|GdImage $frame): bool => $frame instanceof GifDataStream,
        );
    }
}
