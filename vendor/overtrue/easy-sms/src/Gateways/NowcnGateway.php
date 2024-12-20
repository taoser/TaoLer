<?php

namespace Overtrue\EasySms\Gateways;

use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;

class NowcnGateway extends Gateway
{
    use HasHttpRequest;

<<<<<<< HEAD
    const ENDPOINT_URL = 'http://ad1200.now.net.cn:2003/sms/sendSMS';

    const SUCCESS_CODE = 0;
=======
    public const ENDPOINT_URL = 'http://ad1200.now.net.cn:2003/sms/sendSMS';

    public const SUCCESS_CODE = 0;
>>>>>>> 3.0

    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        if (!$config->get('key')) {
<<<<<<< HEAD
            throw new GatewayErrorException("key not found", -2, []);
=======
            throw new GatewayErrorException('key not found', -2, []);
>>>>>>> 3.0
        }
        $params = [
            'mobile' => $to->getNumber(),
            'content' => $message->getContent($this),
            'userId' => $config->get('key'),
            'password' => $config->get('secret'),
            'apiType' => $config->get('api_type'),
        ];
        $result = $this->get(self::ENDPOINT_URL, $params);
        $result = is_string($result) ? json_decode($result, true) : $result;
        if (self::SUCCESS_CODE != $result['code']) {
            throw new GatewayErrorException($result['msg'], $result['code'], $result);
        }
<<<<<<< HEAD
=======

>>>>>>> 3.0
        return $result;
    }
}
