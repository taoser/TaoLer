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

use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;

/**
<<<<<<< HEAD
 * Class SmsbaoGateway
 * @author iwindy <203962638@qq.com>
=======
 * Class SmsbaoGateway.
 *
 * @author iwindy <203962638@qq.com>
 *
>>>>>>> 3.0
 * @see http://www.smsbao.com/openapi/
 */
class SmsbaoGateway extends Gateway
{
    use HasHttpRequest;

<<<<<<< HEAD
    const ENDPOINT_URL = 'http://api.smsbao.com/%s';

    const SUCCESS_CODE = '0';
=======
    public const ENDPOINT_URL = 'http://api.smsbao.com/%s';

    public const SUCCESS_CODE = '0';
>>>>>>> 3.0

    protected $errorStatuses = [
        '0' => '短信发送成功',
        '-1' => '参数不全',
        '-2' => '服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！',
        '30' => '密码错误',
        '40' => '账号不存在',
        '41' => '余额不足',
        '42' => '帐户已过期',
        '43' => 'IP地址限制',
<<<<<<< HEAD
        '50' => '内容含有敏感词'
=======
        '50' => '内容含有敏感词',
>>>>>>> 3.0
    ];

    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $data = $message->getContent($this);

<<<<<<< HEAD
        if (is_null($to->getIDDCode()) || $to->getIDDCode() == '86') {
=======
        if (is_null($to->getIDDCode()) || '86' == $to->getIDDCode()) {
>>>>>>> 3.0
            $number = $to->getNumber();
            $action = 'sms';
        } else {
            $number = $to->getUniversalNumber();
            $action = 'wsms';
        }

        $params = [
            'u' => $config->get('user'),
            'p' => md5($config->get('password')),
            'm' => $number,
<<<<<<< HEAD
            'c' => $data
=======
            'c' => $data,
>>>>>>> 3.0
        ];

        $result = $this->get($this->buildEndpoint($action), $params);

<<<<<<< HEAD
        if ($result !== self::SUCCESS_CODE) {
=======
        if (self::SUCCESS_CODE !== $result) {
>>>>>>> 3.0
            throw new GatewayErrorException($this->errorStatuses[$result], $result);
        }

        return $result;
    }

    protected function buildEndpoint($type)
    {
        return sprintf(self::ENDPOINT_URL, $type);
    }
}
