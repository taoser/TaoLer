<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\Color;
use Intervention\Gif\Blocks\ColorTable;
use Intervention\Gif\Exceptions\DecoderException;

class ColorTableDecoder extends AbstractDecoder
{
    /**
     * Decode given string to ColorTable.
     *
     * @throws DecoderException
     */
    public function decode(): ColorTable
    {
        $table = new ColorTable();
        $length = $this->length() !== null ? $this->length() : 0;

        for ($i = 0; $i < ($length / 3); $i++) {
            $table->addColor(Color::decode($this->stream));
        }

        return $table;
    }
}
