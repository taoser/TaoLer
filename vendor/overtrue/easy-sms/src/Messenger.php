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

use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

/**
 * Class Messenger.
 */
class Messenger
{
<<<<<<< HEAD
    const STATUS_SUCCESS = 'success';

    const STATUS_FAILURE = 'failure';

    /**
     * @var \Overtrue\EasySms\EasySms
=======
    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILURE = 'failure';

    /**
     * @var EasySms
>>>>>>> 3.0
     */
    protected $easySms;

    /**
     * Messenger constructor.
<<<<<<< HEAD
     *
     * @param \Overtrue\EasySms\EasySms $easySms
=======
>>>>>>> 3.0
     */
    public function __construct(EasySms $easySms)
    {
        $this->easySms = $easySms;
    }

    /**
     * Send a message.
     *
<<<<<<< HEAD
     * @param \Overtrue\EasySms\Contracts\PhoneNumberInterface $to
     * @param \Overtrue\EasySms\Contracts\MessageInterface     $message
     * @param array                                            $gateways
     *
     * @return array
     *
     * @throws \Overtrue\EasySms\Exceptions\NoGatewayAvailableException
=======
     * @return array
     *
     * @throws NoGatewayAvailableException
>>>>>>> 3.0
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, array $gateways = [])
    {
        $results = [];
        $isSuccessful = false;

        foreach ($gateways as $gateway => $config) {
            try {
                $results[$gateway] = [
                    'gateway' => $gateway,
                    'status' => self::STATUS_SUCCESS,
<<<<<<< HEAD
                    'template'  => $message->getTemplate($this->easySms->gateway($gateway)),
=======
                    'template' => $message->getTemplate($this->easySms->gateway($gateway)),
>>>>>>> 3.0
                    'result' => $this->easySms->gateway($gateway)->send($to, $message, $config),
                ];
                $isSuccessful = true;

                break;
            } catch (\Exception $e) {
                $results[$gateway] = [
                    'gateway' => $gateway,
                    'status' => self::STATUS_FAILURE,
<<<<<<< HEAD
                    'template'  => $message->getTemplate($this->easySms->gateway($gateway)),
=======
                    'template' => $message->getTemplate($this->easySms->gateway($gateway)),
>>>>>>> 3.0
                    'exception' => $e,
                ];
            } catch (\Throwable $e) {
                $results[$gateway] = [
                    'gateway' => $gateway,
                    'status' => self::STATUS_FAILURE,
<<<<<<< HEAD
                    'template'  => $message->getTemplate($this->easySms->gateway($gateway)),
=======
                    'template' => $message->getTemplate($this->easySms->gateway($gateway)),
>>>>>>> 3.0
                    'exception' => $e,
                ];
            }
        }

        if (!$isSuccessful) {
            throw new NoGatewayAvailableException($results);
        }

        return $results;
    }
}
