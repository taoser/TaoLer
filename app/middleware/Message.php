<?php
declare (strict_types = 1);

namespace app\middleware;
use taoler\com\Message as MessageIns;

class Message
{
    /**
     * @param $request
     * @param \Closure $next
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function handle($request, \Closure $next)
    {
        //后置中间件
        $response = $next($request);

        if(session('?user_id')){
            MessageIns::insertMsg((int)session('user_id'));
        }
        return $response;
    }
}
