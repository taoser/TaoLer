<?php

declare(strict_types=1);

namespace Endroid\QrCode\Label\Font;

final readonly class OpenSans implements FontInterface
{
    public function __construct(
<<<<<<< HEAD
        private int $size = 16
=======
        private int $size = 16,
>>>>>>> 3.0
    ) {
    }

    public function getPath(): string
    {
        return __DIR__.'/../../../assets/open_sans.ttf';
    }

    public function getSize(): int
    {
        return $this->size;
    }
}
