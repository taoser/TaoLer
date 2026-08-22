<?php

namespace app\common\facade;

use think\Facade;

/**
 * @method string decrypt(string $data) 解密
 * @method string encrypt(string $data) 加密
 */
class Decrypt extends Facade
{

    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'app\common\helper\DecryptUtil';
    }

}