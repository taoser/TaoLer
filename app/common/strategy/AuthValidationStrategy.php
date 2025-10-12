<?php

namespace app\common\strategy;

use Exception;

// 策略模式：权限校验策略接口
class AuthValidationStrategy implements ValidationStrategy
{
    public function validate($data)
    {
        if(config('taoler.config.is_post') == 0 ) {
            throw new Exception('抱歉，系统维护中，暂时禁止发帖！', 500);
        }

        // 验证
        if(config('taoler.config.post_captcha') == 1) {			
            if(!captcha_check($data['captcha'])){
                throw new Exception('验证码失败', 500);
            };
        }
        
    }
}