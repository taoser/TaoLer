<?php

namespace app\common\facade;

use think\Facade;
use think\response\Json;

/**
 * @method string create(string $data, string $labText = '', string $logoPath = '') 生成二维码
 */
class QrHelper extends Facade
{

    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'app\common\helper\QrCoder';
    }

}