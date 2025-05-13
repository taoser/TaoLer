<?php

namespace app\common\strategy;

use Exception;

use app\index\validate\Article;
use think\exception\ValidateException;

// 策略模式：数据校验策略接口
class DataValidationStrategy implements ValidationStrategy
{
    public function validate($data) {
        try {
            $validate = new Article();
            $validate->scene('Artadd')->failException(true)->check($data);
        } catch (ValidateException $e) {
            throw new Exception($e->getKey().$validate->getError(), 500);
        }
        
    }
}