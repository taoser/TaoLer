<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\ColorTable;
use Intervention\Gif\Blocks\ImageData;
use Intervention\Gif\Blocks\ImageDescriptor;
use Intervention\Gif\Blocks\TableBasedImage;
use Intervention\Gif\Exceptions\DecoderException;

class TableBasedImageDecoder extends AbstractDecoder
{
    /**
     * Decode TableBasedImage.
     *
     * @throws DecoderException
     */
    public function decode(): TableBasedImage
    {
        $block = new TableBasedImage();

        $block->setImageDescriptor(ImageDescriptor::decode($this->stream));

        if ($block->imageDescriptor()->hasLocalColorTable()) {
            $block->setColorTable(
                ColorTable::decode(
                    $this->stream,
                    $block->imageDescriptor()->localColorTableByteSize()
                )
            );
        }

        $block->setImageData(
            ImageData::decode($this->stream)
        );

        return $block;
    }
}
