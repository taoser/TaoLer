<?php

namespace app\common\strategy;

// 权限检查上下文类
class ArticleValidation {
    
    private $strategy = [];

    // public function __construct(Strategy $strategy) {
    //     $this->strategy = $strategy;
    // }

    public function addValidation(ValidationStrategy $strategy) {
        $this->strategy[] = $strategy;
    }

    public function validate($user) {
        foreach($this->strategy as $v) {
            $v->validate($user);
        }
    }
}