<?php
declare (strict_types = 1);

namespace app\listener;
use taoler\com\Message as MessageIns;
//use app\facade\Message;

class Message
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        //执行登陆用户消息数据写入
		MessageIns::insertMsg(session('user_id'));

    }
}
