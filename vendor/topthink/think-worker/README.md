ThinkPHP Workerman 扩展
===============

交流群：981069000 [![点击加群](https://pub.idqqimg.com/wpa/images/group.png "点击加群")](https://qm.qq.com/q/A8YNpzrzC8)

## 安装
```
composer require topthink/think-worker
```

## 说明

本扩展基于 Workerman 5，支持 Linux 与 Windows 平台。

## 使用方法

### 启动与控制

~~~bash
php think worker                # 前台启动（默认 action 为 start）
php think worker start -d       # 守护进程启动
php think worker stop           # 停止（-g 优雅停止，等待请求处理完成）
php think worker restart -d     # 重启
php think worker reload         # 平滑重启（重载业务代码，不断开连接；-g 优雅模式）
php think worker status         # 查看进程状态（-d 实时刷新）
php think worker connections    # 查看当前连接
~~~

守护进程、停止、平滑重启、状态查询依赖 Linux 信号机制，**仅 Linux 下可用**；Windows 下仅支持 `start`。

启动后通过浏览器直接访问当前应用：

~~~
http://localhost:8080
~~~

### 调试输出

控制器或路由中使用 `echo`、`var_dump` 等输出时，内容会**打印到命令行窗口**（不混入 HTTP 响应体），与 webman 行为一致。`dump()` 函数同样输出到命令行。

支持的特性：

- `ETag` / `Last-Modified` 协商缓存（304）
- `Range` 分段请求（206 / 416）
- MIME 类型按扩展名映射，未知类型回退 `finfo` 检测
- 路径穿越防护（`../`、`..%2F`、`\0` 等返回 404）
- 点文件（`.htaccess`、`.git` 等）拒绝访问
- 敏感扩展名黑名单（`php`、`sql`、`env`、`ini`、`log` 等）不作为静态资源返回

在 `config/worker.php` 中配置：

```php
'static' => [
    'enable'            => true,
    'public_path'       => root_path('public'),
    'forbid_extensions' => ['php', 'sql', 'sqlite', 'db', 'env', 'ini', 'log', 'bak', 'sh', 'bat', 'htaccess', 'config'],
    // public 下已存在的独立 php 脚本按 FPM 方式执行
    'public_scripts'    => true,
],
```

文件不存在或命中黑名单时交给应用路由处理。

### 文件上传

兼容 Workerman 的上传机制，无需额外处理。`think\Request` 已自动绑定为 `think\worker\Request`，`move()` 等方法在 worker 模式下可用，单文件与多文件上传均支持。

### 热更新

```php
'hot_update' => [
    'enable'  => env('APP_DEBUG', false),
    'name'    => ['*.php'],
    'include' => [app_path(), config_path(), root_path('route')],
    'exclude' => [],
],
```

开启后修改 `include` 目录内的 PHP 文件会自动重载（增删改均触发）。

### 队列支持

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
worker.websocket.enable = true 时开启
```

#### 路由定义
```php
Route::get('path1','controller/action1');
Route::get('path2','controller/action2');
```

#### 控制器

```php
use \think\worker\Websocket;
use \think\worker\websocket\Frame;

class Controller {

    public function action1(){

        return (new \think\worker\response\Websocket())
            ->onOpen(...)
            ->onMessage(function(Websocket $websocket, Frame $frame){
                ...
            })
            ->onClose(...);
    }

    public function action2(){

        return (new \think\worker\response\Websocket())
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

---

## 自定义 Worker 使用说明

### 核心原理

从源码可以看出，think-worker 在启动时会触发 `worker.init` 事件（见 [InteractsWithServer.php:96](file:///e:/github/TaoLer/vendor/topthink/think-worker/src/concerns/InteractsWithServer.php#L96)）：

```php
$this->triggerEvent('init');
```

而 `triggerEvent` 方法（见 [WithContainer.php:47](file:///e:/github/TaoLer/vendor/topthink/think-worker/src/concerns/WithContainer.php#L47)）实际触发的是 `worker.init` 事件：

```php
public function triggerEvent(string $event, $params = null): void
{
    $this->container->event->trigger("worker.{$event}", $params);
}
```

所以监听 `worker.init` 事件，就能拿到 `Manager` 实例，调用其 `addWorker` 方法添加自定义 worker。

### `addWorker` 方法签名

来自 [InteractsWithServer.php:24-42](file:///e:/github/TaoLer/vendor/topthink/think-worker/src/concerns/InteractsWithServer.php#L24)：

```php
public function addWorker(
    callable $func,       // Worker 启动后的回调
    $name = 'none',       // Worker 名称（用于 status 展示）
    int $count = 1,       // 进程数
    ?string $socket = null,  // 监听地址，如 "tcp://0.0.0.0:9501"
    array $socketContext = [] // socket 上下文选项
): Worker
```

回调函数内，框架已自动完成了应用初始化、conduit 连接、IPC 监听等工作，你只需编写业务逻辑。

---

### 示例 1：TCP Echo Server

在 `app/event.php` 或自定义事件订阅类中监听 `worker.init`：

```php
// app/event.php
use think\worker\Manager;
use think\worker\Worker;

return [
    'listen' => [
        'worker.init' => function (Manager $manager) {
            $manager->addWorker(function (Worker $worker) {
                $worker->onConnect = function ($connection) {
                    echo "New connection from {$connection->getRemoteIp()}\n";
                };
                $worker->onMessage = function ($connection, $data) {
                    $connection->send("Echo: {$data}");
                };
                $worker->onClose = function ($connection) {
                    echo "Connection closed\n";
                };
            }, 'echo-server', 1, 'tcp://0.0.0.0:9501');
        },
    ],
];
```

启动后，该 worker 会监听 `0.0.0.0:9501`，任何 TCP 连接发送的数据都会原样回显。

---

### 示例 2：UDP 数据采集 Worker

```php
use think\worker\Manager;
use think\worker\Worker;

// 在 app/event.php 中
'worker.init' => function (Manager $manager) {
    $manager->addWorker(function (Worker $worker) {
        $worker->onMessage = function ($connection, $data) {
            // 处理 UDP 数据包
            $payload = json_decode($data, true);
            if ($payload) {
                // 写入数据库或推送到队列
                echo "Received UDP: " . $data . "\n";
            }
        };
    }, 'udp-collector', 1, 'udp://0.0.0.0:9502');
},
```

---

### 示例 3：定时任务 Worker（无需监听端口）

`addWorker` 的 `$socket` 参数默认为 `null`，此时创建的是不监听任何端口的 worker 进程，适合跑定时任务：

```php
use think\worker\Manager;
use think\worker\Worker;
use Workerman\Timer;

'worker.init' => function (Manager $manager) {
    $manager->addWorker(function (Worker $worker) {
        // 每 60 秒执行一次
        Timer::add(60, function () {
            echo "Running scheduled task at " . date('Y-m-d H:i:s') . "\n";
            // 例如：清理过期 session、同步数据等
        });
    }, 'cron-worker', 1);
},
```

---

### 示例 4：使用事件订阅类（推荐）

对于复杂场景，建议用事件订阅类而非闭包，便于维护：

**1. 创建订阅类** `app\subscribe\WorkerSubscribe.php`：

```php
<?php
namespace app\subscribe;

use think\worker\Manager;
use think\worker\Worker;
use Workerman\Timer;

class WorkerSubscribe
{
    public function onWorkerInit(Manager $manager)
    {
        $this->registerTcpServer($manager);
        $this->registerCronWorker($manager);
    }

    protected function registerTcpServer(Manager $manager)
    {
        $manager->addWorker(function (Worker $worker) {
            $worker->onMessage = function ($connection, $data) {
                $result = $this->handleData($data);
                $connection->send(json_encode($result));
            };
        }, 'tcp-service', 2, 'tcp://0.0.0.0:9503');
    }

    protected function registerCronWorker(Manager $manager)
    {
        $manager->addWorker(function (Worker $worker) {
            Timer::add(30, function () {
                echo "Heartbeat check at " . date('H:i:s') . "\n";
            });
        }, 'cron-heartbeat', 1);
    }

    protected function handleData($data)
    {
        return ['status' => 'ok', 'input' => $data];
    }
}
```

**2. 在 `app/event.php` 中注册订阅**：

```php
return [
    'subscribe' => [
        \app\subscribe\WorkerSubscribe::class,
    ],
];
```

ThinkPHP 的事件系统会自动将 `onWorkerInit` 方法映射到 `worker.init` 事件（方法名 `on` + 事件名驼峰）。

---

### 关键注意事项

| 要点 | 说明 |
|---|---|
| **事件名称** | 监听的是 `worker.init`（ThinkPHP 事件名），框架内部触发的是 `worker.init` |
| **进程数** | `$count` 参数仅在 Linux 下生效（fork 多进程），Windows 下每类 worker 只有 1 个进程 |
| **动态监听** | 在回调内可调用 `$worker->listen()` 动态添加监听，参考 [Workerman 文档](https://www.workerman.net/doc/workerman/worker/listen.html) |
| **应用容器** | 回调执行时框架已完成应用初始化，可直接使用 `app()`、`Db`、`Cache` 等 ThinkPHP 功能 |
| **Windows 限制** | Windows 下自定义 worker 需要在 `workerTypes()` 中注册才能被独立进程拉起，否则建议仅在 Linux 下使用 |
| **热更新** | 开启热更新后，修改 PHP 文件会自动重载自定义 worker 的业务代码 |

### 启动验证

```bash
php think worker start
```

启动后执行 `php think worker status`（仅 Linux），可以看到自定义 worker 的名称和状态：

```
| echo-server   | 1 | [active] |
| cron-worker   | 1 | [active] |
```



The user is asking how to define multiple custom workers on Linux. This is simpler than Windows because on Linux, the `worker.init` event IS triggered. Let me explain based on the source code I've already read.

On Linux, the startup flow is:
1. `startLinux()` → `initialize()` → `triggerEvent('init')` → `Worker::runAll()`
2. The `worker.init` event is triggered, passing the `Manager` instance
3. You can listen to this event and call `$manager->addWorker()` multiple times

So on Linux, you simply listen to the `worker.init` event and call `addWorker` as many times as you need. Each call creates a separate Worker process.

Let me provide clear examples with multiple workers.
Linux 下自定义 worker 比 Windows 简单得多，因为 `worker.init` 事件**会正常触发**。只需监听该事件，多次调用 `addWorker` 即可。

## Linux 下自定义多个 Worker

### 启动流程回顾

Linux 启动路径（[InteractsWithServer.php:86-97](file:///e:/github/TaoLer/vendor/topthink/think-worker/src/concerns/InteractsWithServer.php#L86)）：

```php
protected function startLinux(string $command = 'start'): void
{
    Worker::$command = $command;

    $this->initialize();       // 准备 http/queue/conduit
    $this->prepareIpc();
    $this->triggerEvent('init');  // ← 触发 worker.init 事件，传入 Manager 实例

    if ($this->getConfig('hot_update.enable', false)) {
        $this->addHotUpdateWorker();
    }

    Worker::runAll();          // 所有 Worker 统一启动
}
```

关键：`triggerEvent('init')` 把 `Manager` 自身作为参数传出，监听者拿到后可多次调用 `addWorker`。

---

### 方式一：闭包方式（简单场景）

在 `app/event.php` 中：

```php
<?php
use think\worker\Manager;
use think\worker\Worker;
use Workerman\Timer;

return [
    'listen' => [
        'worker.init' => function (Manager $manager) {

            // Worker 1: TCP Echo 服务
            $manager->addWorker(function (Worker $worker) {
                $worker->onConnect = function ($connection) {
                    echo "[echo] Client connected: {$connection->getRemoteIp()}\n";
                };
                $worker->onMessage = function ($connection, $data) {
                    $connection->send("Echo: {$data}");
                };
                $worker->onClose = function ($connection) {
                    echo "[echo] Client disconnected\n";
                };
            }, 'echo-server', 2, 'tcp://0.0.0.0:9501');

            // Worker 2: UDP 数据采集
            $manager->addWorker(function (Worker $worker) {
                $worker->onMessage = function ($connection, $data) {
                    $payload = json_decode($data, true);
                    if ($payload) {
                        echo "[udp] Received: " . $data . "\n";
                    }
                };
            }, 'udp-collector', 1, 'udp://0.0.0.0:9502');

            // Worker 3: 定时任务（无端口监听）
            $manager->addWorker(function (Worker $worker) {
                Timer::add(60, function () {
                    echo "[cron] Running at " . date('Y-m-d H:i:s') . "\n";
                });
            }, 'cron-task', 1);

            // Worker 4: WebSocket 推送服务
            $manager->addWorker(function (Worker $worker) {
                $worker->onConnect = function ($connection) {
                    echo "[ws-push] New connection\n";
                };
                $worker->onMessage = function ($connection, $data) {
                    // 收到消息后广播给所有连接
                    foreach ($worker->connections as $conn) {
                        $conn->send($data);
                    }
                };
            }, 'ws-push', 1, 'websocket://0.0.0.0:9503');
        },
    ],
];
```

---

### 方式二：事件订阅类（推荐，结构清晰）

当自定义 worker 较多时，建议用订阅类组织代码。

**1. 创建订阅类** `app\subscribe\WorkerSubscribe.php`：

```php
<?php
namespace app\subscribe;

use think\worker\Manager;
use think\worker\Worker;
use Workerman\Timer;

class WorkerSubscribe
{
    public function onWorkerInit(Manager $manager)
    {
        $this->registerEchoServer($manager);
        $this->registerUdpCollector($manager);
        $this->registerCronTask($manager);
        $this->registerWsPush($manager);
    }

    protected function registerEchoServer(Manager $manager): void
    {
        $manager->addWorker(function (Worker $worker) {
            $worker->onConnect = function ($connection) {
                echo "[echo] Client connected: {$connection->getRemoteIp()}\n";
            };
            $worker->onMessage = function ($connection, $data) {
                $connection->send("Echo: {$data}");
            };
            $worker->onClose = function ($connection) {
                echo "[echo] Client disconnected\n";
            };
        }, 'echo-server', 2, 'tcp://0.0.0.0:9501');
    }

    protected function registerUdpCollector(Manager $manager): void
    {
        $manager->addWorker(function (Worker $worker) {
            $worker->onMessage = function ($connection, $data) {
                $payload = json_decode($data, true);
                if ($payload) {
                    echo "[udp] Received: " . $data . "\n";
                }
            };
        }, 'udp-collector', 1, 'udp://0.0.0.0:9502');
    }

    protected function registerCronTask(Manager $manager): void
    {
        $manager->addWorker(function (Worker $worker) {
            Timer::add(60, function () {
                echo "[cron] Running at " . date('Y-m-d H:i:s') . "\n";
            });
        }, 'cron-task', 1);
    }

    protected function registerWsPush(Manager $manager): void
    {
        $manager->addWorker(function (Worker $worker) {
            $worker->onConnect = function ($connection) {
                echo "[ws-push] New connection\n";
            };
            $worker->onMessage = function ($connection, $data) use ($worker) {
                foreach ($worker->connections as $conn) {
                    $conn->send($data);
                }
            };
        }, 'ws-push', 1, 'websocket://0.0.0.0:9503');
    }
}
```

**2. 注册订阅** `app/event.php`：

```php
<?php
return [
    'subscribe' => [
        \app\subscribe\WorkerSubscribe::class,
    ],
];
```

ThinkPHP 的事件系统会自动将 `onWorkerInit` 映射到 `worker.init` 事件。

---

### 方式三：配置驱动（最灵活，适合生产环境）

将自定义 worker 定义在配置文件中，通过一个统一的订阅类读取配置并批量注册。

**1. 在 `config/worker.php` 中添加自定义 worker 配置**：

```php
<?php
return [
    // ... 原有配置 ...

    'custom_workers' => [
        [
            'name'      => 'echo-server',
            'socket'    => 'tcp://0.0.0.0:9501',
            'count'     => 2,
            'callbacks' => \app\worker\callback\EchoServer::class,
        ],
        [
            'name'      => 'udp-collector',
            'socket'    => 'udp://0.0.0.0:9502',
            'count'     => 1,
            'callbacks' => \app\worker\callback\UdpCollector::class,
        ],
        [
            'name'      => 'cron-task',
            'socket'    => null,
            'count'     => 1,
            'callbacks' => \app\worker\callback\CronTask::class,
        ],
        [
            'name'      => 'ws-push',
            'socket'    => 'websocket://0.0.0.0:9503',
            'count'     => 1,
            'callbacks' => \app\worker\callback\WsPush::class,
        ],
    ],
];
```

**2. 创建回调类**，每个 worker 一个类，放在 `app\worker\callback\` 目录下：

```php
<?php
// app/worker/callback/EchoServer.php
namespace app\worker\callback;

use think\worker\Worker;

class EchoServer
{
    public function __invoke(Worker $worker): void
    {
        $worker->onConnect = function ($connection) {
            echo "[echo] Client connected: {$connection->getRemoteIp()}\n";
        };
        $worker->onMessage = function ($connection, $data) {
            $connection->send("Echo: {$data}");
        };
        $worker->onClose = function ($connection) {
            echo "[echo] Client disconnected\n";
        };
    }
}
```

```php
<?php
// app/worker/callback/CronTask.php
namespace app\worker\callback;

use think\worker\Worker;
use Workerman\Timer;

class CronTask
{
    public function __invoke(Worker $worker): void
    {
        Timer::add(60, function () {
            echo "[cron] Running at " . date('Y-m-d H:i:s') . "\n";
        });
    }
}
```

```php
<?php
// app/worker/callback/WsPush.php
namespace app\worker\callback;

use think\worker\Worker;

class WsPush
{
    public function __invoke(Worker $worker): void
    {
        $worker->onConnect = function ($connection) {
            echo "[ws-push] New connection\n";
        };
        $worker->onMessage = function ($connection, $data) use ($worker) {
            foreach ($worker->connections as $conn) {
                $conn->send($data);
            }
        };
    }
}
```

```php
<?php
// app/worker/callback/UdpCollector.php
namespace app\worker\callback;

use think\worker\Worker;

class UdpCollector
{
    public function __invoke(Worker $worker): void
    {
        $worker->onMessage = function ($connection, $data) {
            $payload = json_decode($data, true);
            if ($payload) {
                echo "[udp] Received: " . $data . "\n";
            }
        };
    }
}
```

**3. 创建统一注册的订阅类** `app\subscribe\WorkerSubscribe.php`：

```php
<?php
namespace app\subscribe;

use think\worker\Manager;
use think\worker\Worker;

class WorkerSubscribe
{
    public function onWorkerInit(Manager $manager): void
    {
        $workers = $manager->getConfig('custom_workers', []);

        foreach ($workers as $config) {
            $callback  = $config['callbacks'];
            $name      = $config['name'];
            $count     = $config['count'] ?? 1;
            $socket    = $config['socket'] ?? null;

            $manager->addWorker(function (Worker $worker) use ($callback) {
                if (is_callable($callback)) {
                    $callback($worker);
                } elseif (class_exists($callback)) {
                    (new $callback())($worker);
                }
            }, $name, $count, $socket);
        }
    }
}
```

**4. 注册订阅** `app/event.php`：

```php
<?php
return [
    'subscribe' => [
        \app\subscribe\WorkerSubscribe::class,
    ],
];
```

---

### 启动与验证

```bash
# 前台启动（可看到所有 worker 启动信息）
php think worker start

# 守护进程启动
php think worker start -d

# 查看进程状态
php think worker status
```

`status` 输出示例：

```
┌────────────────┬──────┬────────────┐
│ name           │ pid  │ status     │
├────────────────┼──────┼────────────┤
│ http server    │ 1001 │ [active]   │
│ http server    │ 1002 │ [active]   │
│ http server    │ 1003 │ [active]   │
│ http server    │ 1004 │ [active]   │
│ echo-server    │ 1005 │ [active]   │
│ echo-server    │ 1006 │ [active]   │
│ udp-collector  │ 1007 │ [active]   │
│ cron-task      │ 1008 │ [active]   │
│ ws-push        │ 1009 │ [active]   │
└────────────────┴──────┴────────────┘
```

---

### 三种方式对比

| | 方式一：闭包 | 方式二：订阅类 | 方式三：配置驱动 |
|---|---|---|---|
| 适合场景 | 1-2 个简单 worker | 多个 worker，逻辑较复杂 | 生产环境，需动态增减 worker |
| 代码组织 | 集中在 event.php | 按方法拆分 | 每个回调独立类 |
| 可维护性 | ⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| 配置外部化 | ❌ | ❌ | ✅ 配置文件控制 |
| 新增 worker | 改代码 | 加方法 | 加配置 + 加回调类 |

**推荐**：少量 worker 用方式二，生产环境用方式三。方式三的优势在于 worker 的端口、进程数等参数都在配置文件中，修改后重启即可生效，无需改动代码逻辑。

## 平台差异

| 能力 | Linux | Windows |
|---|---|---|
| 进程模型 | fork 多进程，`worker_num` 生效 | 每类 worker 一个独立进程 |
| 守护/停止/平滑重启/状态 | 支持 | 不支持（仅前台 start） |
| 进程间通信（conduit） | `unix://` 域套接字 | `tcp://`（默认 `127.0.0.1:9999`） |
| 热更新 | 支持 | 支持（重载标记机制） |
| 静态文件 / 文件上传 | 支持 | 支持 |

## 生产环境建议

- 使用 `php think worker start -d` 守护方式运行，或使用 supervisor/systemd 管理进程。
- 修改 `.env` 或配置文件后需重启 worker 才能生效（常驻进程不会自动重读）。
- 生产环境建议前置 nginx 做静态文件分发与 HTTPS 终结，worker 只处理动态请求。
