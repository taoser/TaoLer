<?php

namespace think\worker\contract\websocket;

use think\Request;
use think\worker\websocket\Frame;

interface HandlerInterface
{
    /**
     * "onOpen" listener.
     *
     * @param Request $request
     */
    public function onOpen(Request $request);

    /**
     * "onMessage" listener.
     *
     * @param Frame $frame
     */
    public function onMessage(Frame $frame);

    /**
     * "onClose" listener.
     */
    public function onClose();

    public function encodeMessage($message);

}
