<?php

declare(strict_types=1);

namespace Intervention\Image\Colors\Hsl\Channels;

use Intervention\Image\Colors\AbstractColorChannel;

class Luminance extends AbstractColorChannel
{
    /**
     * {@inheritdoc}
     *
     * @see ColorChannelInterface::min()
     */
    public function min(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @see ColorChannelInterface::max()
     */
    public function max(): int
    {
        return 100;
    }
}
