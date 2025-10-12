<?php

namespace app\common\strategy;

// 策略模式：校验策略接口
interface Strategy {
    public function check($data);
}