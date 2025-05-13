<?php

namespace app\common\observer;

// 观察者管理器类
class ObserverManager {
    private $observers = [];

    // 添加观察者
    public function addObserver(Observer $observer) {
        $this->observers[] = $observer;
    }

    // 删除观察者
    public function detach(Observer $observer) {
        $this->observers = array_filter($this->observers, fn($o) => $o !== $observer);
    }

    // 通知观察者
    public function notify($data){
        foreach($this->observers as $observer) {
            $observer->update($data);
        }
    }
}