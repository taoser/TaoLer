<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Merchant\Item;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * 上传商户商品文件.
 *
 * @see https://opendocs.alipay.com/mini/510d4a72_alipay.merchant.item.file.upload?scene=common&pathHash=c08922b1
 *
 * @date 2025-10-28
 */
class FileUploadPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Merchant][Item][FileUploadPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload(
            array_merge(
                [
                    'method' => 'alipay.merchant.item.file.upload',
                    'scene' => 'SYNC_ORDER',
                ],
                $rocket->getParams()
            )
        );

        Logger::info('[Alipay][Merchant][Item][FileUploadPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
