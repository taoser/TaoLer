<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\InvalidArgumentException;
use Intervention\Gif\Exceptions\StateException;

class NetscapeApplicationExtension extends ApplicationExtension
{
    public const IDENTIFIER = "NETSCAPE";
    public const AUTH_CODE = "2.0";
    public const SUB_BLOCK_PREFIX = "\x01";

    /**
     * Create new instance.
     */
    public function __construct()
    {
        try {
            $this->setApplication(self::IDENTIFIER . self::AUTH_CODE);
            $this->setBlocks([new DataSubBlock(self::SUB_BLOCK_PREFIX . "\x00\x00")]);
        } catch (InvalidArgumentException) {
            // ignore exception because of hard coded input
        }
    }

    /**
     * Get number of loops.
     *
     * @throws DecoderException
     */
    public function loops(): int
    {
        try {
            $unpacked = unpack('v*', substr($this->firstBlock()->value(), 1));
        } catch (StateException $e) {
            throw new DecoderException(
                'Failed to decode loop count of netscape extension',
                previous: $e
            );
        }

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Failed to calculate loop count');
        }

        return $unpacked[1];
    }

    /**
     * Set number of loops.
     *
     * @throws InvalidArgumentException
     */
    public function setLoops(int $loops): self
    {
        $this->setBlocks([
            new DataSubBlock(self::SUB_BLOCK_PREFIX . pack('v*', $loops))
        ]);

        return $this;
    }
}
