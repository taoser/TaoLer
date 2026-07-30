<?php

namespace think\worker;

class Ipc
{
    protected $workerId;

    public function __construct(protected Manager $manager, protected Conduit $conduit)
    {

    }

    public function listenMessage()
    {
        $this->subscribe();
        return $this->workerId;
    }

    public function sendMessage($workerId, $message)
    {
        if ($workerId === $this->workerId) {
            $this->manager->triggerEvent('message', $message);
        } else {
            $this->publish($workerId, $message);
        }
    }

    public function subscribe()
    {
        $this->workerId = $this->conduit->inc('ipc:worker');
        $this->conduit->subscribe("ipc:message:{$this->workerId}", function ($message) {
            $this->manager->triggerEvent('message', unserialize($message));
        });
    }

    public function publish($workerId, $message)
    {
        $this->conduit->publish("ipc:message:{$workerId}", serialize($message));
    }
}
