<?php

/*
 * This file is part of the overtrue/easy-sms.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\EasySms\Strategies;

use Overtrue\EasySms\Contracts\StrategyInterface;

/**
 * Class RandomStrategy.
 */
class RandomStrategy implements StrategyInterface
{
    /**
<<<<<<< HEAD
     * @param array $gateways
     *
=======
>>>>>>> 3.0
     * @return array
     */
    public function apply(array $gateways)
    {
        uasort($gateways, function () {
            return mt_rand() - mt_rand();
        });

        return array_keys($gateways);
    }
}
