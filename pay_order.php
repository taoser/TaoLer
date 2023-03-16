<?php
use Workerman\Worker;
use Workerman\Timer;
use think\App;
use addons\pay\controller\Index;
use app\common\lib\HttpHelper;
use yzh52521\EasyHttp\Request;
use yzh52521\EasyHttp\Http;


include __DIR__ . '/vendor/autoload.php';

// 普通的函数
function setOrderStatus()
{
//    HttpHelper::get('https://www.aieok.com/pay/setOrderStatus');
    Http::get('https://www.aieok.com/pay/setOrderStatus');

}



$task = new Worker();

$task->count = 1;
$task->onWorkerStart = function($task)
{
    // 10秒后发送一次邮件
//    $app = new App();
//    $pay = new Index($app);
//    Timer::add(3, array($pay, 'setOrderStatus'));

//    $pay = new Index(\think\App::getInstance());
//    Timer::add(3, array($pay, 'setOrderStatus'));

    Timer::add(3,'setOrderStatus');

};

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
