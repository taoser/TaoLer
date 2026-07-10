<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\Header;
use Intervention\Gif\Exceptions\DecoderException;

class HeaderDecoder extends AbstractDecoder
{
    /**
     * Decode current source.
     *
     * @throws DecoderException
     */
    public function decode(): Header
    {
        $header = new Header();
        $header->setVersion($this->decodeVersion());

        return $header;
    }

    /**
     * Decode version string.
     *
     * @throws DecoderException
     */
    private function decodeVersion(): string
    {
        $parsed = (bool) preg_match("/^GIF(?P<version>[0-9]{2}[a-z])$/", $this->nextBytesOrFail(6), $matches);

        if ($parsed === false) {
            throw new DecoderException('Failed to parse GIF file header');
        }

        return $matches['version'];
    }
}
