EasyHttp 是一个轻量级、语义化、对IDE友好的HTTP客户端，支持常见的HTTP请求、异步请求和并发请求，让你可以快速地使用 HTTP 请求与其他 Web 应用进行通信。

> EasyHttp并不强制依赖于cURL，如果没有安装cURL，EasyHttp会自动选择使用PHP流处理，或者你也可以提供自己的发送HTTP请求的处理方式。

如果您觉得EasyHttp对您有用的话，别忘了给点个赞哦^_^ ！

github:[github.com/yzh52521/easyhttp](https://github.com/yzh52521/easyhttp "github.com/yzh52521/easyhttp")

gitee:[gitee.com/yzh52521/easyhttp](https://gitee.com/yzh52521/easyhttp "gitee.com/yzh52521/easyhttp")

本包是基于 [ gouguoyin/easyhttp ](https://gitee.com/gouguoyin/easyhttp "gitee.com/gouguoyin/easyhttp") 进行扩展开发，主要实现了以下扩展：

1. 增加 retry() 重试机制。
2. 增加 debug 日志调试功能。
3. 增加 withHost 指定服务端base_url
4. 增加 withBody 发送原始数据（Raw）请求
5. 增加 withMiddleware/withRequestMiddleware/withResponseMiddleware  Guzzle 中间件
6. 增加 connectTimeout 设置等待服务器响应超时
7. 增加 sink  响应的主体部分将要保存的位置
8. 增加 maxRedirects 请求的重定向行为最大次数


# 安装说明

#### 环境依赖

- PHP >= 7.2.5
- 如果使用PHP流处理，allow_url_fopen 必须在php.ini中启用。
- 如果使用cURL处理，cURL >= 7.19.4，并且编译了OpenSSL 与 zlib。

#### 一键安装

    composer require yzh52521/easyhttp

## 发起请求

#### 同步请求

###### 常规请求

```php
$response = Http::get('http://httpbin.org/get');

$response = Http::get('http://httpbin.org/get?name=yzh52521');

$response = Http::get('http://httpbin.org/get?name=yzh52521', ['age' => 18]);

$response = Http::post('http://httpbin.org/post');

$response = Http::post('http://httpbin.org/post', ['name' => 'yzh52521']);

$response = Http::patch(...);

$response = Http::put(...);

$response = Http::delete(...);

$response = Http::head(...);

$response = Http::options(...);

```

###### 指定服务端base_url的请求

```php
// 指定服务端base_url地址,最终请求地址为 https://serv.yzh52521.com/login
$response = Http::withHost('https://serv.yzh52521.com')->post('/login');

```
##### 发送原始数据（Raw）请求
```php
$response = Http::withBody(
    base64_encode($photo), 'image/jpeg'
)->post(...);
```
###### 发送 Content-Type 编码请求

```php
// application/x-www-form-urlencoded(默认)
$response = Http::asForm()->post(...);

// application/json
$response = Http::asJson()->post(...);
```

###### 发送 Multipart 表单请求

```php
$response = Http::asMultipart(
    'file_input_name', file_get_contents('photo1.jpg'), 'photo2.jpg'
)->post('http://test.com/attachments');

$response = Http::asMultipart(
    'file_input_name', fopen('photo1.jpg', 'r'), 'photo2.jpg'
)->post(...);

$response = Http::attach(
    'file_input_name', file_get_contents('photo1.jpg'), 'photo2.jpg'
)->post(...);

$response = Http::attach(
    'file_input_name', fopen('photo1.jpg', 'r'), 'photo2.jpg'
)->post(...);
```
> 表单enctype属性需要设置成 multipart/form-data

###### 携带请求头的请求

```php
$response = Http::withHeaders([
    'x-powered-by' => 'yzh52521'
])->post(...);
```

###### 携带重定向的请求

```php
// 默认
$response = Http::withRedirect(false)->post(...);

$response = Http::withRedirect([
    'max'             => 5,
    'strict'          => false,
    'referer'         => true,
    'protocols'       => ['http', 'https'],
    'track_redirects' => false
])->post(...);

$response = Http::maxRedirects(5)->post(...);
```



###### 携带认证的请求

```php
// Basic认证
$response = Http::withBasicAuth('username', 'password')->post(...);

// Digest认证(需要被HTTP服务器支持)
$response = Http::withDigestAuth('username', 'password')->post(...);
```

###### 携带 User-Agent 的请求
```php
$response = Http::withUA('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3100.0 Safari/537.36')->post(...);
```

###### 携带Token令牌的请求

```php
$response = Http::withToken('token')->post(...);
```

###### 携带认证文件的请求

```php
$response = Http::withCert('/path/server.pem', 'password')->post(...);
```

###### 携带SSL证书的请求

```php
// 默认
$response = Http::withVerify(false)->post(...);

$response = Http::withVerify('/path/to/cert.pem')->post(...);
```

###### 携带COOKIE的请求

```php
$response = Http::withCookies(array $cookies, string $domain)->post(...);
```

###### 携带协议版本的请求

```php
$response = Http::withVersion(1.1)->post(...);
```

###### 携带代理的请求

```php
$response = Http::withProxy('tcp://localhost:8125')->post(...);

$response = Http::withProxy([
    'http'  => 'tcp://localhost:8125', // Use this proxy with "http"
    'https' => 'tcp://localhost:9124', // Use this proxy with "https",
    'no'    => ['.com.cn', 'yzh52521.cn'] // Don't use a proxy with these
])->post(...);
```

###### 设置超时时间(单位秒)

```php
$response = Http::timeout(60)->post(...);
```

###### 设置等待服务器响应超时的最大值(单位秒)

```php
$response = Http::connectTimeout(60)->post(...);
```

###### 设置延迟时间(单位秒)

```php
$response = Http::delay(60)->post(...);
```

###### 设置并发次数

```php
$response = Http::concurrency(10)->promise(...);
```

###### 重发请求，设置retry方法。重试次数/两次重试之间的时间间隔（毫秒）：

```php
$response = Http::retry(3, 100)->post(...);
```
##### 响应的主体部分将要保存的位置
```php
$response = Http::sink('/path/to/file')->post(...);
```

#### Guzzle 中间件

```php
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

$response = Http::withMiddleware(
    Middleware::mapRequest(function (RequestInterface $request) {
        $request = $request->withHeader('X-Example', 'Value');
        return $request;
    })
)->get('http://example.com');

………………
$response = Http::withRequestMiddleware(
    function (RequestInterface $request) {
        $request = $request->withHeader('X-Example', 'Value');
        return $request;
    }
)->get('http://example.com');

………………

$response = Http::withResponseMiddleware(
    function (RequestInterface $response) {
        $response = $response->getHeader('X-Example');
        return $response;
    }
)->get('http://example.com');
```

#### 异步请求

```php
use yzh52521\EasyHttp\Response;
use yzh52521\EasyHttp\RequestException;

$promise = Http::getAsync('http://easyhttp.yzh52521.cn/api/sleep3.json', ['token' => TOKEN], function (Response $response) {
    echo '异步请求成功，响应内容：' . $response->body() . PHP_EOL;
}, function (RequestException $e) {
    echo '异步请求异常，错误码：' . $e->getCode() . '，错误信息：' . $e->getMessage() . PHP_EOL;
});

$promise->wait();
echo json_encode(['code' => 200, 'msg' => '请求成功'], JSON_UNESCAPED_UNICODE) . PHP_EOL;

//输出
{"code":200,"msg":"请求成功"}
异步请求成功，响应内容：{"code":200,"msg":"success","second":3}

$promise = Http::getAsync('http1://easyhttp.yzh52521.cn/api/sleep3.json', function (Response $response) {
    echo '异步请求成功，响应内容：' . $response->body() . PHP_EOL;
}, function (RequestException $e) {
    echo '异步请求异常，错误信息：' . $e->getMessage() . PHP_EOL;
});

$promise->wait();
echo json_encode(['code' => 200, 'msg' => '请求成功'], JSON_UNESCAPED_UNICODE) . PHP_EOL;

//输出
{"code":200,"msg":"请求成功"}
异步请求异常，错误信息：cURL error 1: Protocol "http1" not supported or disabled in libcurl (see https://curl.haxx.se/libcurl/c/libcurl-errors.html)

Http::postAsync(...);

Http::patchAsync(...);

Http::putAsync(...);

Http::deleteAsync(...);

Http::headAsync(...);

Http::optionsAsync(...);

使用 等待异步回调处理完成
Http::wait();
```

#### 异步并发请求

```php
use yzh52521\EasyHttp\Response;
use yzh52521\EasyHttp\RequestException;

$promises = [
    Http::getAsync('http://easyhttp.yzh52521.cn/api/sleep3.json'),
    Http::getAsync('http1://easyhttp.yzh52521.cn/api/sleep1.json', ['name' => 'yzh52521']),
    Http::postAsync('http://easyhttp.yzh52521.cn/api/sleep2.json', ['name' => 'yzh52521']),
];

$pool=Http::concurrency(10)->multiAsync($promises, function (Response $response, $index) {
    echo "发起第 $index 个异步请求，请求时长：" . $response->json()->second . '秒' . PHP_EOL;
}, function (RequestException $e, $index) {
    echo "发起第 $index 个请求失败，失败原因：" . $e->getMessage() . PHP_EOL;
});

$promise = $pool->promise();
$promise->wait();

//输出
发起第 1 个请求失败，失败原因：cURL error 1: Protocol "http1" not supported or disabled in libcurl (see https://curl.haxx.se/libcurl/c/libcurl-errors.html)
发起第 2 个异步请求，请求时长：2 秒
发起第 0 个异步请求，请求时长：3 秒
```
> 如果未调用concurrency()方法，并发次数默认为$promises的元素个数，$promises数组里必须是异步请求

## 使用响应

发起请求后会返回一个 yzh52521\EasyHttp\Response $response的实例，该实例提供了以下方法来检查请求的响应：

```php
$response->body() : string;
$response->json() : object;
$response->array() : array;
$response->status() : int;
$response->ok() : bool;
$response->successful() : bool;
$response->serverError() : bool;
$response->clientError() : bool;
$response->headers() : array;
$response->header($header) : string;
```

## 异常处理

请求在发生客户端或服务端错误时会抛出 yzh52521\EasyHttp\RequestException $e异常，该实例提供了以下方法来返回异常信息：

```php
$e->getCode() : int;
$e->getMessage() : string;
$e->getFile() : string;
$e->getLine() : int;
$e->getTrace() : array;
$e->getTraceAsString() : string;
```
## 调试日志

有时候难免要对 Http 的请求和响应包体进行记录以方便查找问题或做什么

```php
//传递一个日志类 thinkphp  \think\facade\Log  laravel  Illuminate\Support\Facades\Log
Http::debug(Log::class)->post(...);
```


## 更新日志
### 2023-08-31
* 新增 withBody 可以发送原始数据（Raw）请求
* 新增 withMiddleware/withRequestMiddleware/withResponseMiddleware  支持Guzzle中间件 
* 新增 connectTimeout 设置等待服务器响应超时 
* 新增 sink  响应的主体部分将要保存的位置 
* 新增 maxRedirects 请求的重定向行为最大次数
### 2022-05-11
* 新增removeBodyFormat() 用于withOptions 指定body时，清除原由的bodyFromat
### 2022-05-10
* 新增发送原生请求的方法client()
* 新增发送原生异步请求的方法clientASync()
### 2021-09-03
* 新增 debug() 调试日志
* 新增 retry() 重试机制
* 修复header重叠的bug
### 2020-03-30
* 修复部分情况下IDE不能智能提示的BUG
* get()、getAsync()方法支持带参数的url
* 新增withUA()方法
* 新增withStream()方法
* 新增asMultipart()方法，attach()的别名
* 新增multiAsync()异步并发请求方法

### 2020-03-20
* 新增异步请求getAsync()方法
* 新增异步请求postAsync()方法
* 新增异步请求patchAsync()方法
* 新增异步请求putAsync()方法
* 新增异步请求deleteAsync()方法
* 新增异步请求headAsync()方法
* 新增异步请求optionsAsync()方法

## Todo List
 - [x] 异步请求
 - [x] 并发请求
 - [x] 重试机制
 - [ ] 支持http2
 - [ ] 支持swoole
# easyhttp
