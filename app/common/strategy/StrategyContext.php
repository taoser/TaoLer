<?php

namespace app\common\strategy;

// 策略模式 权限检查上下文类
class StrategyContext {
    
    private $strategy;

    public function __construct(Strategy $strategy) {
        $this->strategy = $strategy;
    }

    public function setStrategy(ValidationStrategy $strategy) {
        $this->strategy = $strategy;
    }

    public function validate($user) {
        return $this->strategy->validate($user);
    }
}