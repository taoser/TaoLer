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

namespace Workerman\Coroutine\Barrier;

use Swow\Errno;
use Swow\Sync\WaitReference;
use Swow\SyncException;

class Swow implements BarrierInterface
{
    /**
     * @inheritDoc
     */
    public static function wait(object &$barrier, int $timeout = -1): void
    {
        try {
            WaitReference::wait($barrier, $timeout > 0 ? $timeout * 1000 : -1);
        } catch (SyncException $exception) {
            if ($exception->getCode() !== Errno::ETIMEDOUT) {
                throw $exception;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function create(): object
    {
        return new WaitReference();
    }

}
