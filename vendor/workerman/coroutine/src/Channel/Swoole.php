<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace Workerman\Coroutine\Channel;

use Closure;
use RuntimeException;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

/**
 * Class Swoole
 */
class Swoole implements ChannelInterface
{

    /**
     * @var Channel
     */
    protected Channel $channel;

    /**
     * Whether PHP has entered the shutdown phase, null until the shutdown handler is registered.
     *
     * Once shutdown functions have run, objects are destructed in arbitrary order, so the Swoole channel
     * may already be destroyed, and calling a destroyed channel is an uncatchable fatal error.
     *
     * @var bool|null
     */
    private static ?bool $shuttingDown = null;

    /**
     * Constructor.
     *
     * @param int $capacity
     */
    public function __construct(protected int $capacity = 1)
    {
        $this->channel = new Channel($capacity);
        if (self::$shuttingDown === null) {
            self::$shuttingDown = false;
            register_shutdown_function(static fn () => self::$shuttingDown = true);
        }
    }

    /**
     * @inheritDoc
     */
    public function push(mixed $data, float $timeout = -1): bool
    {
        if (Coroutine::getCid() > 0) {
            // Swoole treats a zero timeout as waiting forever.
            if ($timeout == 0 && $this->channel->isFull()) {
                return false;
            }
            return $this->channel->push($data, $timeout);
        }
        if (self::$shuttingDown) {
            return false;
        }
        if ($this->channel->isFull()) {
            if ($timeout == 0) {
                return false;
            }
            throw new RuntimeException('Blocking push on a full channel must be called within a coroutine.');
        }
        return $this->runInCoroutine(fn () => $this->channel->push($data));
    }

    /**
     * @inheritDoc
     */
    public function pop(float $timeout = -1): mixed
    {
        if (Coroutine::getCid() > 0) {
            // Swoole treats a zero timeout as waiting forever.
            if ($timeout == 0 && $this->channel->isEmpty()) {
                return false;
            }
            return $this->channel->pop($timeout);
        }
        if (self::$shuttingDown) {
            return false;
        }
        if ($this->channel->isEmpty()) {
            if ($timeout == 0) {
                return false;
            }
            throw new RuntimeException('Blocking pop on an empty channel must be called within a coroutine.');
        }
        return $this->runInCoroutine(fn () => $this->channel->pop());
    }

    /**
     * Run a non-blocking channel operation outside a coroutine.
     *
     * Swoole channel APIs raise an uncatchable fatal error outside a coroutine, so the operation
     * runs in a short-lived coroutine, which completes synchronously because it never yields.
     *
     * @param Closure $operation
     * @return mixed
     */
    private function runInCoroutine(Closure $operation): mixed
    {
        $result = false;
        Coroutine::create(function () use ($operation, &$result) {
            $result = $operation();
        });
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function length(): int
    {
        return $this->channel->length();
    }

    /**
     * @inheritDoc
     */
    public function getCapacity(): int
    {
        return $this->channel->capacity;
    }

    /**
     * @inheritDoc
     */
    public function hasConsumers(): bool
    {
        return $this->channel->stats()['consumer_num'] > 0;
    }

    /**
     * @inheritDoc
     */
    public function hasProducers(): bool
    {
        return $this->channel->stats()['producer_num'] > 0;
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->channel->close();
    }

}
