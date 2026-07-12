<?php

namespace app\common\facade;

use think\Facade;
use think\response\Json;

/**
 * Class HttpHelper
 * @package core\facade
 * @method static string withHost(string $host) 设置主机名
 * @method static array withHeaders(array $headers) 设置请求头
 * @method static array withBody(string $body) 设置请求体
 * @method static array withTimeout(int $timeout) 设置超时时间
 * @method static array withQuery(array $queryParams) 设置查询参数
 * @method static array withConnectTimeout(int $seconds) 设置连接超时时间
 * @method static array get(string $url, array $params = []) 发送GET请求
 * @method static array post(string $url, array $params = []) 发送POST请求
 * @method static array put(string $url, array $params = []) 发送PUT请求
 * @method static array delete(string $url, array $params = []) 发送DELETE请求
 * @method static array options(string $url, array $params = []) 发送OPTIONS请求
 * @method static array sendRequest(string $url, array $params = []) 发送请求
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