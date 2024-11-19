<?php

/*
 * This file is part of the overtrue/easy-sms.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\EasySms;

use Closure;
use Overtrue\EasySms\Contracts\GatewayInterface;
use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Contracts\StrategyInterface;
use Overtrue\EasySms\Exceptions\InvalidArgumentException;
use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\Strategies\OrderStrategy;
use Overtrue\EasySms\Support\Config;

/**
 * Class EasySms.
 */
class EasySms
{
    /**
<<<<<<< HEAD
     * @var \Overtrue\EasySms\Support\Config
=======
     * @var Config
>>>>>>> 3.0
     */
    protected $config;

    /**
     * @var string
     */
    protected $defaultGateway;

    /**
     * @var array
     */
    protected $customCreators = [];

    /**
     * @var array
     */
    protected $gateways = [];

    /**
<<<<<<< HEAD
     * @var \Overtrue\EasySms\Messenger
=======
     * @var Messenger
>>>>>>> 3.0
     */
    protected $messenger;

    /**
     * @var array
     */
    protected $strategies = [];

    /**
     * Constructor.
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
     * Send a message.
     *
<<<<<<< HEAD
     * @param string|array                                       $to
     * @param \Overtrue\EasySms\Contracts\MessageInterface|array $message
     * @param array                                              $gateways
     *
     * @return array
     *
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     * @throws \Overtrue\EasySms\Exceptions\NoGatewayAvailableException
=======
     * @param string|array           $to
     * @param MessageInterface|array $message
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws Exceptions\NoGatewayAvailableException
>>>>>>> 3.0
     */
    public function send($to, $message, array $gateways = [])
    {
        $to = $this->formatPhoneNumber($to);
        $message = $this->formatMessage($message);
        $gateways = empty($gateways) ? $message->getGateways() : $gateways;

        if (empty($gateways)) {
            $gateways = $this->config->get('default.gateways', []);
        }

        return $this->getMessenger()->send($to, $message, $this->formatGateways($gateways));
    }

    /**
     * Create a gateway.
     *
     * @param string|null $name
     *
<<<<<<< HEAD
     * @return \Overtrue\EasySms\Contracts\GatewayInterface
     *
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
=======
     * @return GatewayInterface
     *
     * @throws InvalidArgumentException
>>>>>>> 3.0
     */
    public function gateway($name)
    {
        if (!isset($this->gateways[$name])) {
            $this->gateways[$name] = $this->createGateway($name);
        }

        return $this->gateways[$name];
    }

    /**
     * Get a strategy instance.
     *
     * @param string|null $strategy
     *
<<<<<<< HEAD
     * @return \Overtrue\EasySms\Contracts\StrategyInterface
     *
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
=======
     * @return StrategyInterface
     *
     * @throws InvalidArgumentException
>>>>>>> 3.0
     */
    public function strategy($strategy = null)
    {
        if (\is_null($strategy)) {
            $strategy = $this->config->get('default.strategy', OrderStrategy::class);
        }

        if (!\class_exists($strategy)) {
            $strategy = __NAMESPACE__.'\Strategies\\'.\ucfirst($strategy);
        }

        if (!\class_exists($strategy)) {
            throw new InvalidArgumentException("Unsupported strategy \"{$strategy}\"");
        }

        if (empty($this->strategies[$strategy]) || !($this->strategies[$strategy] instanceof StrategyInterface)) {
            $this->strategies[$strategy] = new $strategy($this);
        }

        return $this->strategies[$strategy];
    }

    /**
     * Register a custom driver creator Closure.
     *
<<<<<<< HEAD
     * @param string   $name
     * @param \Closure $callback
     *
     * @return $this
     */
    public function extend($name, Closure $callback)
=======
     * @param string $name
     *
     * @return $this
     */
    public function extend($name, \Closure $callback)
>>>>>>> 3.0
    {
        $this->customCreators[$name] = $callback;

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
     * @return \Overtrue\EasySms\Messenger
=======
     * @return Messenger
>>>>>>> 3.0
     */
    public function getMessenger()
    {
        return $this->messenger ?: $this->messenger = new Messenger($this);
    }

    /**
     * Create a new driver instance.
     *
     * @param string $name
     *
<<<<<<< HEAD
     * @throws \InvalidArgumentException
     *
     * @return GatewayInterface
     *
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
=======
     * @return GatewayInterface
     *
     * @throws \InvalidArgumentException
     * @throws InvalidArgumentException
>>>>>>> 3.0
     */
    protected function createGateway($name)
    {
        $config = $this->config->get("gateways.{$name}", []);

        if (!isset($config['timeout'])) {
            $config['timeout'] = $this->config->get('timeout', Gateway::DEFAULT_TIMEOUT);
        }

        $config['options'] = $this->config->get('options', []);

        if (isset($this->customCreators[$name])) {
            $gateway = $this->callCustomCreator($name, $config);
        } else {
            $className = $this->formatGatewayClassName($name);
            $gateway = $this->makeGateway($className, $config);
        }

        if (!($gateway instanceof GatewayInterface)) {
            throw new InvalidArgumentException(\sprintf('Gateway "%s" must implement interface %s.', $name, GatewayInterface::class));
        }

        return $gateway;
    }

    /**
     * Make gateway instance.
     *
     * @param string $gateway
     * @param array  $config
     *
<<<<<<< HEAD
     * @return \Overtrue\EasySms\Contracts\GatewayInterface
     *
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
=======
     * @return GatewayInterface
     *
     * @throws InvalidArgumentException
>>>>>>> 3.0
     */
    protected function makeGateway($gateway, $config)
    {
        if (!\class_exists($gateway) || !\in_array(GatewayInterface::class, \class_implements($gateway))) {
            throw new InvalidArgumentException(\sprintf('Class "%s" is a invalid easy-sms gateway.', $gateway));
        }

        return new $gateway($config);
    }

    /**
     * Format gateway name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function formatGatewayClassName($name)
    {
        if (\class_exists($name) && \in_array(GatewayInterface::class, \class_implements($name))) {
            return $name;
        }

        $name = \ucfirst(\str_replace(['-', '_', ''], '', $name));

        return __NAMESPACE__."\\Gateways\\{$name}Gateway";
    }

    /**
     * Call a custom gateway creator.
     *
     * @param string $gateway
     * @param array  $config
<<<<<<< HEAD
     *
     * @return mixed
=======
>>>>>>> 3.0
     */
    protected function callCustomCreator($gateway, $config)
    {
        return \call_user_func($this->customCreators[$gateway], $config);
    }

    /**
<<<<<<< HEAD
     * @param string|\Overtrue\EasySms\Contracts\PhoneNumberInterface $number
     *
     * @return \Overtrue\EasySms\Contracts\PhoneNumberInterface|string
=======
     * @param string|PhoneNumberInterface $number
     *
     * @return PhoneNumberInterface|string
>>>>>>> 3.0
     */
    protected function formatPhoneNumber($number)
    {
        if ($number instanceof PhoneNumberInterface) {
            return $number;
        }

        return new PhoneNumber(\trim($number));
    }

    /**
<<<<<<< HEAD
     * @param array|string|\Overtrue\EasySms\Contracts\MessageInterface $message
     *
     * @return \Overtrue\EasySms\Contracts\MessageInterface
=======
     * @param array|string|MessageInterface $message
     *
     * @return MessageInterface
>>>>>>> 3.0
     */
    protected function formatMessage($message)
    {
        if (!($message instanceof MessageInterface)) {
            if (!\is_array($message)) {
                $message = [
                    'content' => $message,
                    'template' => $message,
                ];
            }

            $message = new Message($message);
        }

        return $message;
    }

    /**
<<<<<<< HEAD
     * @param array $gateways
     *
     * @return array
     *
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
=======
     * @return array
     *
     * @throws InvalidArgumentException
>>>>>>> 3.0
     */
    protected function formatGateways(array $gateways)
    {
        $formatted = [];

        foreach ($gateways as $gateway => $setting) {
            if (\is_int($gateway) && \is_string($setting)) {
                $gateway = $setting;
                $setting = [];
            }

            $formatted[$gateway] = $setting;
            $globalSettings = $this->config->get("gateways.{$gateway}", []);

            if (\is_string($gateway) && !empty($globalSettings) && \is_array($setting)) {
                $formatted[$gateway] = new Config(\array_merge($globalSettings, $setting));
            }
        }

        $result = [];

        foreach ($this->strategy()->apply($formatted) as $name) {
            $result[$name] = $formatted[$name];
        }

        return $result;
    }
}
