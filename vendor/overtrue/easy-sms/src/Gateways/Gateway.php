<?php

/*
 * This file is part of the overtrue/easy-sms.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\EasySms\Gateways;

use Overtrue\EasySms\Contracts\GatewayInterface;
use Overtrue\EasySms\Support\Config;

/**
 * Class Gateway.
 */
abstract class Gateway implements GatewayInterface
{
<<<<<<< HEAD
    const DEFAULT_TIMEOUT = 5.0;

    /**
     * @var \Overtrue\EasySms\Support\Config
=======
    public const DEFAULT_TIMEOUT = 5.0;

    /**
     * @var Config
>>>>>>> 3.0
     */
    protected $config;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var float
     */
    protected $timeout;

    /**
     * Gateway constructor.
<<<<<<< HEAD
     *
     * @param array $config
=======
>>>>>>> 3.0
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    /**
     * Return timeout.
     *
     * @return int|mixed
     */
    public function getTimeout()
    {
        return $this->timeout ?: $this->config->get('timeout', self::DEFAULT_TIMEOUT);
    }

    /**
     * Set timeout.
     *
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = floatval($timeout);

        return $this;
    }

    /**
<<<<<<< HEAD
     * @return \Overtrue\EasySms\Support\Config
=======
     * @return Config
>>>>>>> 3.0
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
<<<<<<< HEAD
     * @param \Overtrue\EasySms\Support\Config $config
     *
=======
>>>>>>> 3.0
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
<<<<<<< HEAD
     * @param $options
     *
=======
>>>>>>> 3.0
     * @return $this
     */
    public function setGuzzleOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getGuzzleOptions()
    {
        return $this->options ?: $this->config->get('options', []);
    }

<<<<<<< HEAD
    /**
     * {@inheritdoc}
     */
=======
>>>>>>> 3.0
    public function getName()
    {
        return \strtolower(str_replace([__NAMESPACE__.'\\', 'Gateway'], '', \get_class($this)));
    }
}
