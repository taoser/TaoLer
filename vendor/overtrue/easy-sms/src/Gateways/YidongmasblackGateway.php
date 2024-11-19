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
 * Class YidongmasblackGateway.
<<<<<<< HEAD
 * 移动MAS黑名单模式（免创建模板）
=======
 * 移动MAS黑名单模式（免创建模板）.
 *
>>>>>>> 3.0
 * @author houang <mail@houang.cn>
 *
 * @see https://mas.10086.cn
 */
class YidongmasblackGateway extends Gateway
{
    use HasHttpRequest;

<<<<<<< HEAD
    const ENDPOINT_URL = 'http://112.35.1.155:1992/sms/norsubmit';

    const ENDPOINT_METHOD = 'send';

    /**
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     * @param Config               $config
     *
=======
    public const ENDPOINT_URL = 'http://112.35.1.155:1992/sms/norsubmit';

    public const ENDPOINT_METHOD = 'send';

    /**
>>>>>>> 3.0
     * @return \Psr\Http\Message\ResponseInterface|array|string
     *
     * @throws GatewayErrorException
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
<<<<<<< HEAD
        $params["ecName"] = $config->get('ecName');
        $params["apId"] = $config->get('apId');
        $params["sign"] = $config->get('sign');
        $params["addSerial"] = $config->get('addSerial');
        $params["mobiles"] = $to->getNumber();
        $params["content"] = $message->getContent();
=======
        $params['ecName'] = $config->get('ecName');
        $params['apId'] = $config->get('apId');
        $params['sign'] = $config->get('sign');
        $params['addSerial'] = $config->get('addSerial');
        $params['mobiles'] = $to->getNumber();
        $params['content'] = $message->getContent();
>>>>>>> 3.0
        $result = $this->postJson(self::ENDPOINT_URL, $this->generateContent($params));

        if ('true' != $result['success']) {
            throw new GatewayErrorException($result['success'], $result['rspcod'], $result);
        }

        return $result;
    }

    /**
     * Generate Content.
     *
     * @param array $params
     *
     * @return string
     */
    protected function generateContent($params)
    {
        $secretKey = $this->config->get('secretKey');
<<<<<<< HEAD
        $params['mac'] = md5($params["ecName"].$params["apId"].$secretKey.$params["mobiles"].$params["content"].$params["sign"].$params["addSerial"]);
        
=======
        $params['mac'] = md5($params['ecName'].$params['apId'].$secretKey.$params['mobiles'].$params['content'].$params['sign'].$params['addSerial']);

>>>>>>> 3.0
        return base64_encode(json_encode($params));
    }
}
