<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\H5;

use Yansongda\Pay\Rocket;

class PrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\PrepayPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'v3/pay/transactions/h5';
    }

    protected function getPartnerUri(Rocket $rocket): string
    {
        return 'v3/pay/partner/transactions/h5';
    }

    protected function getConfigKey(array $params): string
    {
        $key = ($params['_type'] ?? 'mp').'_app_id';
        if ('app_app_id' === $key) {
            $key = 'app_id';
        }

        return $key;
    }
}
