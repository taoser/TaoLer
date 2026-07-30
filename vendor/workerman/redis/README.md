# redis
Asynchronous redis client for PHP based on workerman.

# Install

```
composer require workerman/redis
```

# Usage
```php

require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Redis\Client;
use Workerman\Worker;

$worker = new Worker('http://0.0.0.0:6161');

$worker->onWorkerStart = function() {
    global $redis;
    $redis = new Client('redis://127.0.0.1:6379');
};

$worker->onMessage = function($connection, $data) {
    global $redis;
    $redis->set('key', 'hello world');    
    $redis->get('key', function ($result) use ($connection) {
        $connection->send($result);
    });  
};

Worker::runAll();
```

## Document

http://doc.workerman.net/components/workerman-redis.html
