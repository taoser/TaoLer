<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\DataSubBlock;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\InvalidArgumentException;

class DataSubBlockDecoder extends AbstractDecoder
{
    /**
     * Decode current source.
     *
     * @throws DecoderException
     */
    public function decode(): DataSubBlock
    {
        $char = $this->nextByteOrFail();
        $unpacked = unpack('C', $char);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to decode data sub block');
        }

        $size = (int) $unpacked[1];

        try {
            return new DataSubBlock($this->nextBytesOrFail($size));
        } catch (InvalidArgumentException $e) {
            throw new DecoderException(
                'Failed to decode image data sub block of image data',
                previous: $e
            );
        }
    }
}
