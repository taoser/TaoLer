ThinkPHP Workerman 扩展
===============

交流群：981069000 [![点击加群](https://pub.idqqimg.com/wpa/images/group.png "点击加群")](https://qm.qq.com/q/A8YNpzrzC8)

## 安装
```
composer require topthink/think-worker
```

## 说明
> 由于windows下无法在一个文件里启动多个worker，所以本扩展不支持windows平台

## 使用方法

### HttpServer

在命令行启动服务端
~~~
php think worker
~~~

然后就可以通过浏览器直接访问当前应用

~~~
http://localhost:8080
~~~

如果需要使用守护进程方式运行，建议使用supervisor来管理进程

## 访问静态文件
> 建议使用nginx来支持静态文件访问，也可使用路由输出文件内容，下面是示例，可参照修改
1. 添加静态文件路由：

```php
Route::get('static/:path', function (string $path) {
    $filename = public_path() . $path;
    return new \think\worker\response\File($filename);
})->pattern(['path' => '.*\.\w+$']);
```

2. 访问路由 `http://localhost/static/文件路径`

## 队列支持

使用方法见 [think-queue](https://github.com/top-think/think-queue)

以下配置代替think-queue里的最后一步:`监听任务并执行`,无需另外起进程执行队列

```php
return [
    // ...
    'queue'      => [
        'enable'  => true,
        //键名是队列名称
        'workers' => [
            //下面参数是不设置时的默认配置
            'default'            => [
                'delay'      => 0,
                'sleep'      => 3,
                'tries'      => 0,
                'timeout'    => 60,
                'worker_num' => 1,
            ],
            //使用@符号后面可指定队列使用驱动
            'default@connection' => [
                //此处可不设置任何参数，使用上面的默认配置
            ],
        ],
    ],
    // ...
];

```

### websocket

> 使用路由调度的方式，可以让不同路径的websocket服务响应不同的事件

#### 配置

```
swoole.websocket = true 时开启
```

#### 路由定义
```php
Route::get('path1','controller/action1');
Route::get('path2','controller/action2');
```

#### 控制器

```php
use \think\swoole\Websocket;
use \think\swoole\websocket\Event;
use \Swoole\WebSocket\Frame;

class Controller {

    public function action1(){
    
        return \think\swoole\helper\websocket()
            ->onOpen(...)
            ->onMessage(function(Websocket $websocket, Frame $frame){ 
                ...
            })
            ->onClose(...);
    }
    
    public function action2(){
    
        return \think\swoole\helper\websocket()
            ->onOpen(...)
            ->onMessage(function(Websocket $websocket, Frame $frame){
               ...
            })
            ->onClose(...);
    }
}
```


## 自定义worker
监听`worker.init`事件 注入`Manager`对象，调用addWorker方法添加
~~~php
use think\worker\Manager;
use \think\worker\Worker;

//...

public function handle(Manager $manager){
   $worker = $manager->addWorker(function(Worker $worker){
        //..其他回调或处理
        //动态添加监听可参考 https://www.workerman.net/doc/workerman/worker/listen.html
    });
}

//...
~~~
