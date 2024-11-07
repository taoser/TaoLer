<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\Exceptions\FormatException;

class NetscapeApplicationExtension extends ApplicationExtension
{
    public const IDENTIFIER = "NETSCAPE";
    public const AUTH_CODE = "2.0";
    public const SUB_BLOCK_PREFIX = "\x01";

    /**
     * Create new instance
     *
     * @throws FormatException
     * @return void
     */
    public function __construct()
    {
        $this->setApplication(self::IDENTIFIER . self::AUTH_CODE);
        $this->setBlocks([new DataSubBlock(self::SUB_BLOCK_PREFIX . "\x00\x00")]);
    }

    /**
     * Get number of loops
     *
     * @return int
     */
    public function getLoops(): int
    {
        return unpack('v*', substr($this->getBlocks()[0]->getValue(), 1))[1];
    }

    /**
     * Set number of loops
     *
     * @param int $loops
     * @throws FormatException
     * @return self
     */
    public function setLoops(int $loops): self
    {
        $this->setBlocks([
            new DataSubBlock(self::SUB_BLOCK_PREFIX . pack('v*', $loops))
        ]);

        return $this;
    }
}
