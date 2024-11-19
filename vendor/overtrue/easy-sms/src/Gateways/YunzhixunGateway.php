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
 * Class YunzhixunGateway.
 *
 * @author her-cat <i@her-cat.com>
 *
 * @see http://docs.ucpaas.com/doku.php?id=%E7%9F%AD%E4%BF%A1:sendsms
 */
class YunzhixunGateway extends Gateway
{
    use HasHttpRequest;

<<<<<<< HEAD
    const SUCCESS_CODE = '000000';

    const FUNCTION_SEND_SMS = 'sendsms';

    const FUNCTION_BATCH_SEND_SMS = 'sendsms_batch';

    const ENDPOINT_TEMPLATE = 'https://open.ucpaas.com/ol/%s/%s';
=======
    public const SUCCESS_CODE = '000000';

    public const FUNCTION_SEND_SMS = 'sendsms';

    public const FUNCTION_BATCH_SEND_SMS = 'sendsms_batch';

    public const ENDPOINT_TEMPLATE = 'https://open.ucpaas.com/ol/%s/%s';
>>>>>>> 3.0

    /**
     * Send a short message.
     *
<<<<<<< HEAD
     * @param \Overtrue\EasySms\Contracts\PhoneNumberInterface $to
     * @param \Overtrue\EasySms\Contracts\MessageInterface     $message
     * @param \Overtrue\EasySms\Support\Config                 $config
     *
=======
>>>>>>> 3.0
     * @return array
     *
     * @throws GatewayErrorException
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $data = $message->getData($this);

        $function = isset($data['mobiles']) ? self::FUNCTION_BATCH_SEND_SMS : self::FUNCTION_SEND_SMS;

        $endpoint = $this->buildEndpoint('sms', $function);

        $params = $this->buildParams($to, $message, $config);

        return $this->execute($endpoint, $params);
    }

    /**
<<<<<<< HEAD
     * @param $resource
     * @param $function
     *
=======
>>>>>>> 3.0
     * @return string
     */
    protected function buildEndpoint($resource, $function)
    {
        return sprintf(self::ENDPOINT_TEMPLATE, $resource, $function);
    }

    /**
<<<<<<< HEAD
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     * @param Config               $config
     *
=======
>>>>>>> 3.0
     * @return array
     */
    protected function buildParams(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $data = $message->getData($this);

        return [
            'sid' => $config->get('sid'),
            'token' => $config->get('token'),
            'appid' => $config->get('app_id'),
            'templateid' => $message->getTemplate($this),
            'uid' => isset($data['uid']) ? $data['uid'] : '',
            'param' => isset($data['params']) ? $data['params'] : '',
            'mobile' => isset($data['mobiles']) ? $data['mobiles'] : $to->getNumber(),
        ];
    }

    /**
<<<<<<< HEAD
     * @param $endpoint
     * @param $params
     *
=======
>>>>>>> 3.0
     * @return array
     *
     * @throws GatewayErrorException
     */
    protected function execute($endpoint, $params)
    {
        try {
            $result = $this->postJson($endpoint, $params);

            if (!isset($result['code']) || self::SUCCESS_CODE !== $result['code']) {
                $code = isset($result['code']) ? $result['code'] : 0;
                $error = isset($result['msg']) ? $result['msg'] : json_encode($result, JSON_UNESCAPED_UNICODE);

                throw new GatewayErrorException($error, $code);
            }

            return $result;
        } catch (\Exception $e) {
            throw new GatewayErrorException($e->getMessage(), $e->getCode());
        }
    }
}
