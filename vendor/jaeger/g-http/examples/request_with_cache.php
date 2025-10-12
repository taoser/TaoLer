<?php
/**
 * Created by PhpStorm.
 * User: Jaeger <JaegerCode@gmail.com>
 * Date: 18/12/11
 * Time: 下午6:48
 */

require __DIR__.'/../vendor/autoload.php';
use Jaeger\GHttp;

use Symfony\Component\Cache\Adapter\RedisAdapter;

$rt = GHttp::get('http://httpbin.org/get',[
    'wd' => 'QueryList'
],[
    'cache' => __DIR__.DIRECTORY_SEPARATOR.'.cache',
    'cache_ttl' => 120
]);

print_r($rt);



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