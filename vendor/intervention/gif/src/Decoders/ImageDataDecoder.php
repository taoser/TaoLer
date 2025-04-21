<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\AbstractEntity;
use Intervention\Gif\Blocks\DataSubBlock;
use Intervention\Gif\Blocks\ImageData;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\FormatException;

class ImageDataDecoder extends AbstractDecoder
{
    /**
     * Decode current source
     *
     * @throws DecoderException
     * @throws FormatException
     * @return ImageData
     */
    public function decode(): ImageData
    {
        $data = new ImageData();

        // LZW min. code size
        $char = $this->getNextByteOrFail();
        $size = (int) unpack('C', $char)[1];
        $data->setLzwMinCodeSize($size);

        do {
            // decode sub blocks
            $char = $this->getNextByteOrFail();
            $size = (int) unpack('C', $char)[1];
            if ($size > 0) {
                $data->addBlock(new DataSubBlock($this->getNextBytesOrFail($size)));
            }
        } while ($char !== AbstractEntity::TERMINATOR);

        return $data;
    }
}
