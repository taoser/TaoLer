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
 * Class MiaoxinGateway.
 *
 * @see http://docs.51miaoxin.com/sms/http_send.html
 * @see http://docs.51miaoxin.com/sms/http_sign_fixed.html
 * @see http://docs.51miaoxin.com/sms/http_template_parmed.html
 */
class MiaoxinGateway extends Gateway
{
    use HasHttpRequest;

    public const ENDPOINT_HOST = 'http://www.51miaoxin.com';

    public const SEND_PATH = '/sms/send';

    public const FIXED_SIGNATURE_PATH = '/sms/sendFixedSignature';

    public const TEMPLATE_PATH = '/sms/sendTemplateParamd';

    public const SUCCESS_CODE = 0;

    /**
     * @return array
     *
     * @throws GatewayErrorException
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $template = $message->getTemplate($this);
        $signatureId = $config->get('signature_id');

        if ($template !== null && $template !== '') {
            $params = $this->buildTemplateParams($to, $message, $config);
            $path = self::TEMPLATE_PATH;
        } elseif ($signatureId !== null && $signatureId !== '') {
            $params = $this->buildContentParams($to, $message, $config, true);
            $path = self::FIXED_SIGNATURE_PATH;
        } else {
            $params = $this->buildContentParams($to, $message, $config);
            $path = self::SEND_PATH;
        }

        return $this->sendRequest($this->buildEndpoint($config, $path), $params);
    }

    /**
     * Build the common protocol signature params.
     *
     * @return array
     */
    protected function buildBaseParams(Config $config)
    {
        $account = $config->get('account');
        $secret = $config->get('secret');
        $ts = date('YmdHis');

        return [
            'account' => $account,
            'ts' => $ts,
            'token' => $this->generateToken($account, $secret, $ts),
        ];
    }

    /**
     * Generate the SHA1 protocol token.
     */
    protected function generateToken(string $account, string $secret, string $ts): string
    {
        return sha1("account={$account}&ts={$ts}&secret={$secret}");
    }

    /**
     * Build the endpoint URL with optional host override.
     */
    protected function buildEndpoint(Config $config, string $path): string
    {
        $host = rtrim($config->get('endpoint') ?: self::ENDPOINT_HOST, '/');

        return $host.$path;
    }

    /**
     * Build params for custom or fixed-signature content sending.
     *
     * @return array
     */
    protected function buildContentParams(PhoneNumberInterface $to, MessageInterface $message, Config $config, bool $fixed = false)
    {
        $params = $this->buildBaseParams($config);

        $params['mobiles'] = $to->getNumber();
        $params['content'] = $message->getContent($this);

        if ($fixed) {
            $params['signatureId'] = $config->get('signature_id');
        }

        return $this->appendOptional($params, $config, ['ref', 'ext']);
    }

    /**
     * Build params for template sending.
     *
     * @return array
     */
    protected function buildTemplateParams(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $params = $this->buildBaseParams($config);

        $params['templateId'] = $message->getTemplate($this);
        $params['mobiles'] = $to->getNumber();

        $index = 1;
        foreach (array_values($message->getData($this)) as $value) {
            if ($index > 8) {
                break;
            }
            $params['param'.$index] = $value;
            $index++;
        }

        return $this->appendOptional($params, $config, ['ref', 'ext', 'schedule']);
    }

    /**
     * Append optional params when they are present in config.
     *
     * @return array
     */
    protected function appendOptional(array $params, Config $config, array $keys)
    {
        foreach ($keys as $key) {
            $value = $config->get($key);
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * Post the request and validate the response codes.
     *
     * @return array
     *
     * @throws GatewayErrorException
     */
    protected function sendRequest(string $endpoint, array $params)
    {
        $result = $this->post($endpoint, $params);

        if ($result['code'] != self::SUCCESS_CODE) {
            throw new GatewayErrorException($result['msg'], $result['code'], $result);
        }

        foreach ($result['result'] ?? [] as $item) {
            if ($item['code'] != self::SUCCESS_CODE) {
                throw new GatewayErrorException($item['msg'], $item['code'], $result);
            }
        }

        return $result;
    }
}
