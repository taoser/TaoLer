<?php

namespace app\common\lib\facade;

use think\Facade;
use think\response\Json;

/**
 * @method static \app\common\lib\HttpHelper withHost(string $url = 'http://api.aieok.com') 携带指定接口
 * @method static \app\common\lib\HttpHelper withHeaders(array $data = []) 携带请求头
 * @method static \app\common\lib\HttpHelper get(string $url, array $data = []) GET请求
 * @method static \app\common\lib\HttpHelper post(string $url, array $data = []) POST请求
 * @method static array toArray() 返回ARRAY数据
 * @method static bool ok() 服务器200
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
        return 'app\common\lib\HttpHelper';
    }

}