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
 * Class YunpianGateway.
 *
 * @see https://www.yunpian.com/doc/zh_CN/intl/single_send.html
 */
class YunpianGateway extends Gateway
{
    use HasHttpRequest;

<<<<<<< HEAD
    const ENDPOINT_TEMPLATE = 'https://%s.yunpian.com/%s/%s/%s.%s';

    const ENDPOINT_VERSION = 'v2';

    const ENDPOINT_FORMAT = 'json';

    /**
     * @param \Overtrue\EasySms\Contracts\PhoneNumberInterface $to
     * @param \Overtrue\EasySms\Contracts\MessageInterface     $message
     * @param \Overtrue\EasySms\Support\Config                 $config
     *
     * @return array
     *
     * @throws \Overtrue\EasySms\Exceptions\GatewayErrorException ;
=======
    public const ENDPOINT_TEMPLATE = 'https://%s.yunpian.com/%s/%s/%s.%s';

    public const ENDPOINT_VERSION = 'v2';

    public const ENDPOINT_FORMAT = 'json';

    /**
     * @return array
     *
     * @throws GatewayErrorException ;
>>>>>>> 3.0
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $template = $message->getTemplate($this);
        $function = 'single_send';
        $option = [
            'form_params' => [
                'apikey' => $config->get('api_key'),
<<<<<<< HEAD
                'mobile' => $to->getUniversalNumber()
=======
                'mobile' => $to->getUniversalNumber(),
>>>>>>> 3.0
            ],
            'exceptions' => false,
        ];

        if (!is_null($template)) {
            $function = 'tpl_single_send';
            $data = [];

            $templateData = $message->getData($this);
            $templateData = isset($templateData) ? $templateData : [];
            foreach ($templateData as $key => $value) {
<<<<<<< HEAD
                $data[] = urlencode('#'.$key.'#') . '=' . urlencode($value);
=======
                $data[] = urlencode('#'.$key.'#').'='.urlencode($value);
>>>>>>> 3.0
            }

            $option['form_params'] = array_merge($option['form_params'], [
                'tpl_id' => $template,
<<<<<<< HEAD
                'tpl_value' => implode('&', $data)
=======
                'tpl_value' => implode('&', $data),
>>>>>>> 3.0
            ]);
        } else {
            $content = $message->getContent($this);
            $signature = $config->get('signature', '');
            $option['form_params'] = array_merge($option['form_params'], [
<<<<<<< HEAD
                'text' => 0 === \stripos($content, '【') ? $content : $signature.$content
=======
                'text' => 0 === \stripos($content, '【') ? $content : $signature.$content,
>>>>>>> 3.0
            ]);
        }

        $endpoint = $this->buildEndpoint('sms', 'sms', $function);
        $result = $this->request('post', $endpoint, $option);

        if ($result['code']) {
            throw new GatewayErrorException($result['msg'], $result['code'], $result);
        }

        return $result;
    }

    /**
     * Build endpoint url.
     *
     * @param string $type
     * @param string $resource
     * @param string $function
     *
     * @return string
     */
    protected function buildEndpoint($type, $resource, $function)
    {
        return sprintf(self::ENDPOINT_TEMPLATE, $type, self::ENDPOINT_VERSION, $resource, $function, self::ENDPOINT_FORMAT);
    }
}
