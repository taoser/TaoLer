<?php

namespace app\common\facade;

use think\Facade;
use think\response\Json;
use app\common\helper\Httper;

/**
 * Class HttpHelper
 * @package core\facade
 * @method static Httper withHost(?string $url = 'https://www.aieok.com/api') 设置主机名
 * @method static Httper withHeaders(array $headers = []) 设置请求头
 * @method static Httper withQuery(array $queryParams) 设置查询参数
 * @method static Httper withTimeout(int $seconds) 设置超时时间
 * @method static Httper withConnectTimeout(int $seconds) 设置连接超时时间
 * @method static Httper withRetry(int $maxRetries = 2, int $retryDelay = 1000) 设置重试配置
 * @method static Httper asJson() 使用JSON格式请求
 * @method static Httper asFormParams() 使用表单格式请求
 * @method static Httper asMultipart() 使用multipart格式请求
 * @method static ?Httper get(string $url, array $queryParams = []) 发送GET请求
 * @method static ?Httper post(string $url, array $data = []) 发送POST请求
 * @method static ?Httper put(string $url, array $data = []) 发送PUT请求
 * @method static ?Httper delete(string $url, array $data = []) 发送DELETE请求
 * @method static ?Httper rawResponse() 获取原始响应
 * @method static ?\stdClass toJson() 获取JSON响应
 * @method static ?array toArray() 获取数组响应
 */
class HttpHelper extends Facade
{

    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'app\common\helper\Httper';
    }

}