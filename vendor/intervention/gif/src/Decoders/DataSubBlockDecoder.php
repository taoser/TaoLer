<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\DataSubBlock;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\FormatException;

class DataSubBlockDecoder extends AbstractDecoder
{
    /**
     * Decode current sourc
     *
     * @throws FormatException
     * @throws DecoderException
     * @return DataSubBlock
     */
    public function decode(): DataSubBlock
    {
        $char = $this->getNextByteOrFail();
        $size = (int) unpack('C', $char)[1];

        return new DataSubBlock($this->getNextBytesOrFail($size));
    }
}
