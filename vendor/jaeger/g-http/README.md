# GHttp
基于GuzzleHttp的简单版Http客户端。 Simple Http client base on GuzzleHttp

## 安装

```
composer require jaeger/g-http
```

## 用法

#### 1. get / getJson
```php

use Jaeger\GHttp;

$rt = GHttp::get('https://www.baidu.com/s?wd=QueryList');

$rt = GHttp::get('https://www.baidu.com/s','wd=QueryList&wd2=teststr');

//or

$rt = GHttp::get('https://www.baidu.com/s',[
    'wd' => 'QueryList',
    'wd2' => 'teststr'
]);

//opt

$rt = GHttp::get('https://www.baidu.com/s',[
    'wd' => 'QueryList'
],[
    'headers' => [
        'referer' => 'https://baidu.com',
        'User-Agent' => 'Mozilla/5.0 (Windows NTChrome/58.0.3029.110 Safari/537.36',
        'Cookie' => 'cookie xxx'
    ]
]);

$rt = GHttp::getJson('https://xxxx.com/json');

```

#### 2.post / postRaw / postJson
```php
$rt = GHttp::post('https://www.posttestserver.com/post.php',[
    'name' => 'QueryList',
    'password' => 'ql'
]);

$rt = GHttp::post('https://www.posttestserver.com/post.php','name=QueryList&password=ql');


$rt = GHttp::postRaw('http://httpbin.org/post','raw data');
$rt = GHttp::postRaw('http://httpbin.org/post',['aa' => 11,'bb' => 22]);


$rt = GHttp::postJson('http://httpbin.org/post',['aa' => 11,'bb' => 22]);
$rt = GHttp::postJson('http://httpbin.org/post','aa=11&bb=22');

```
#### 3.download

```php
GHttp::download('http://sw.bos.baidu.com/setup.exe','./path/to/xx.exe');
```
### 4. concurrent requests
```php
use Jaeger\GHttp;

$urls = [
    'http://httpbin.org/get?name=php',
    'http://httpbin.org/get?name=go',
    'http://httpbin.org/get?name=c#',
    'http://httpbin.org/get?name=java'
];

GHttp::multiRequest($urls)->withHeaders([
    'X-Powered-By' => 'Jaeger'
])->withOptions([
    'timeout' => 10
])->concurrency(2)->success(function($response,$index){
    print_r((String)$response->getBody());
    print_r($index);
})->error(function($reason,$index){
    print_r($reason);
})->get();
```

```php
use Jaeger\GHttp;
use GuzzleHttp\Psr7\Request;

$requests = [
    new Request('POST','http://httpbin.org/post',[
        'Content-Type' => 'application/x-www-form-urlencoded',
        'User-Agent' => 'g-http'
    ],http_build_query([
        'name' => 'php'
    ])),
    new Request('POST','http://httpbin.org/post',[
        'Content-Type' => 'application/x-www-form-urlencoded',
        'User-Agent' => 'g-http'
    ],http_build_query([
        'name' => 'go'
    ])),
    new Request('POST','http://httpbin.org/post',[
        'Content-Type' => 'application/x-www-form-urlencoded',
        'User-Agent' => 'g-http'
    ],http_build_query([
        'name' => 'c#'
    ]))
];

GHttp::multiRequest($requests)->success(function($response,$index){
    print_r((String)$response->getBody());
    print_r($index);
})->post();
```
### 5. Request with cache

Base on Symfony-Cache: https://symfony.com/doc/current/components/cache.html

- Use filesystem cache
```php
use Jaeger\GHttp;

$rt = GHttp::get('http://httpbin.org/get',[
    'wd' => 'QueryList'
],[
    'cache' => __DIR__,
    'cache_ttl' => 120 //seconds
]);

```

- Use predis cache

Install predis:
```
composer require predis/predis
```

Usage:
```php
use Jaeger\GHttp;
use Symfony\Component\Cache\Adapter\RedisAdapter;

$cache = new RedisAdapter(

    // the object that stores a valid connection to your Redis system
    $redis = RedisAdapter::createConnection(
        'redis://localhost'
    ),

    // the string prefixed to the keys of the items stored in this cache
    $namespace = 'cache',

    // the default lifetime (in seconds) for cache items that do not define their
    // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
    // until RedisAdapter::clear() is invoked or the server(s) are purged)
    $defaultLifetime = 0
);

$rt = GHttp::get('http://httpbin.org/get',[
    'wd' => 'QueryList'
],[
    'cache' => $cache,
    'cache_ttl' => 120
]);

print_r($rt);
```