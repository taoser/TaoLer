<?php

declare(strict_types=1);

namespace Endroid\QrCode\Logo;

final readonly class Logo implements LogoInterface
{
    public function __construct(
        private string $path,
<<<<<<< HEAD
        private int|null $resizeToWidth = null,
        private int|null $resizeToHeight = null,
        private bool $punchoutBackground = false
    ) {
    }

    public static function create(string $path): self
    {
        return new self($path);
=======
        private ?int $resizeToWidth = null,
        private ?int $resizeToHeight = null,
        private bool $punchoutBackground = false,
    ) {
>>>>>>> 3.0
    }

    public function getPath(): string
    {
        return $this->path;
    }

<<<<<<< HEAD
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getResizeToWidth(): int|null
=======
    public function getResizeToWidth(): ?int
>>>>>>> 3.0
    {
        return $this->resizeToWidth;
    }

<<<<<<< HEAD
    public function setResizeToWidth(int|null $resizeToWidth): self
    {
        $this->resizeToWidth = $resizeToWidth;

        return $this;
    }

    public function getResizeToHeight(): int|null
=======
    public function getResizeToHeight(): ?int
>>>>>>> 3.0
    {
        return $this->resizeToHeight;
    }

<<<<<<< HEAD
    public function setResizeToHeight(int|null $resizeToHeight): self
    {
        $this->resizeToHeight = $resizeToHeight;

        return $this;
    }

=======
>>>>>>> 3.0
    public function getPunchoutBackground(): bool
    {
        return $this->punchoutBackground;
    }
}
