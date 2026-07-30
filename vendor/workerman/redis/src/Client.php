<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Workerman\Redis;

use Revolt\EventLoop;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Redis\Protocols\Redis;
use Workerman\Timer;

/**
 * Class Client
 * @package Workerman\Redis
 *
 * Strings methods
 * @method static int append($key, $value, $cb = null)
 * @method static int bitCount($key, $cb = null)
 * @method static int decrBy($key, $value, $cb = null)
 * @method static string|bool get($key, $cb = null)
 * @method static int getBit($key, $offset, $cb = null)
 * @method static string getRange($key, $start, $end, $cb = null)
 * @method static string getSet($key, $value, $cb = null)
 * @method static int incrBy($key, $value, $cb = null)
 * @method static float incrByFloat($key, $value, $cb = null)
 * @method static array mGet(array $keys, $cb = null)
 * @method static array getMultiple(array $keys, $cb = null)
 * @method static bool setBit($key, $offset, $value, $cb = null)
 * @method static bool setEx($key, $ttl, $value, $cb = null)
 * @method static bool pSetEx($key, $ttl, $value, $cb = null)
 * @method static bool setNx($key, $value, $cb = null)
 * @method static string setRange($key, $offset, $value, $cb = null)
 * @method static int strLen($key, $cb = null)
 * Keys methods
 * @method static int del(...$keys, $cb = null)
 * @method static int unlink(...$keys, $cb = null)
 * @method static false|string dump($key, $cb = null)
 * @method static int exists(...$keys, $cb = null)
 * @method static bool expire($key, $ttl, $cb = null)
 * @method static bool pexpire($key, $ttl, $cb = null)
 * @method static bool expireAt($key, $timestamp, $cb = null)
 * @method static bool pexpireAt($key, $timestamp, $cb = null)
 * @method static array keys($pattern, $cb = null)
 * @method static void migrate($host, $port, $keys, $dbIndex, $timeout, $copy = false, $replace = false, $cb = null)
 * @method static bool move($key, $dbIndex, $cb = null)
 * @method static string|int|bool object($information, $key, $cb = null)
 * @method static bool persist($key, $cb = null)
 * @method static string randomKey($cb = null)
 * @method static bool rename($srcKey, $dstKey, $cb = null)
 * @method static bool renameNx($srcKey, $dstKey, $cb = null)
 * @method static string type($key, $cb = null)
 * @method static int ttl($key, $cb = null)
 * @method static int pttl($key, $cb = null)
 * @method static void restore($key, $ttl, $value, $cb = null)
 * Hashes methods
 * @method static false|int hSet($key, $hashKey, $value, $cb = null)
 * @method static bool hSetNx($key, $hashKey, $value, $cb = null)
 * @method static false|string hGet($key, $hashKey, $cb = null)
 * @method static false|int hLen($key, $cb = null)
 * @method static false|int hDel($key, ...$hashKeys, $cb = null)
 * @method static array hKeys($key, $cb = null)
 * @method static array hVals($key, $cb = null)
 * @method static bool hExists($key, $hashKey, $cb = null)
 * @method static int hIncrBy($key, $hashKey, $value, $cb = null)
 * @method static float hIncrByFloat($key, $hashKey, $value, $cb = null)
 * @method static int hStrLen($key, $hashKey, $cb = null)
 * Lists methods
 * @method static array blPop($keys, $timeout, $cb = null)
 * @method static array brPop($keys, $timeout, $cb = null)
 * @method static false|string bRPopLPush($srcKey, $dstKey, $timeout, $cb = null)
 * @method static false|string lIndex($key, $index, $cb = null)
 * @method static int lInsert($key, $position, $pivot, $value, $cb = null)
 * @method static false|string lPop($key, $cb = null)
 * @method static false|int lPush($key, ...$entries, $cb = null)
 * @method static false|int lPushx($key, $value, $cb = null)
 * @method static array lRange($key, $start, $end, $cb = null)
 * @method static false|int lRem($key, $value, $count, $cb = null)
 * @method static bool lSet($key, $index, $value, $cb = null)
 * @method static false|array lTrim($key, $start, $end, $cb = null)
 * @method static false|string rPop($key, $cb = null)
 * @method static false|string rPopLPush($srcKey, $dstKey, $cb = null)
 * @method static false|int rPush($key, ...$entries, $cb = null)
 * @method static false|int rPushX($key, $value, $cb = null)
 * @method static false|int lLen($key, $cb = null)
 * Sets methods
 * @method static int sAdd($key, $value, $cb = null)
 * @method static int sCard($key, $cb = null)
 * @method static array sDiff($keys, $cb = null)
 * @method static false|int sDiffStore($dst, $keys, $cb = null)
 * @method static false|array sInter($keys, $cb = null)
 * @method static false|int sInterStore($dst, $keys, $cb = null)
 * @method static bool sIsMember($key, $member, $cb = null)
 * @method static array sMembers($key, $cb = null)
 * @method static bool sMove($src, $dst, $member, $cb = null)
 * @method static false|string|array sPop($key, $count = 0, $cb = null)
 * @method static false|string|array sRandMember($key, $count = 0, $cb = null)
 * @method static int sRem($key, ...$members, $cb = null)
 * @method static array sUnion(...$keys, $cb = null)
 * @method static false|int sUnionStore($dst, ...$keys, $cb = null)
 * Sorted sets methods
 * @method static array bzPopMin($keys, $timeout, $cb = null)
 * @method static array bzPopMax($keys, $timeout, $cb = null)
 * @method static int zAdd($key, $score, $value, $cb = null)
 * @method static int zCard($key, $cb = null)
 * @method static int zCount($key, $start, $end, $cb = null)
 * @method static double zIncrBy($key, $value, $member, $cb = null)
 * @method static int zinterstore($keyOutput, $arrayZSetKeys, $arrayWeights = [], $aggregateFunction = '', $cb = null)
 * @method static array zPopMin($key, $count, $cb = null)
 * @method static array zPopMax($key, $count, $cb = null)
 * @method static array zRange($key, $start, $end, $withScores = false, $cb = null)
 * @method static array zRangeByScore($key, $start, $end, $options = [], $cb = null)
 * @method static array zRevRangeByScore($key, $start, $end, $options = [], $cb = null)
 * @method static array zRangeByLex($key, $min, $max, $offset = 0, $limit = 0, $cb = null)
 * @method static int zRank($key, $member, $cb = null)
 * @method static int zRevRank($key, $member, $cb = null)
 * @method static int zRem($key, ...$members, $cb = null)
 * @method static int zRemRangeByRank($key, $start, $end, $cb = null)
 * @method static int zRemRangeByScore($key, $start, $end, $cb = null)
 * @method static array zRevRange($key, $start, $end, $withScores = false, $cb = null)
 * @method static double zScore($key, $member, $cb = null)
 * @method static int zunionstore($keyOutput, $arrayZSetKeys, $arrayWeights = [], $aggregateFunction = '', $cb = null)
 * HyperLogLogs methods
 * @method static int pfAdd($key, $values, $cb = null)
 * @method static int pfCount($keys, $cb = null)
 * @method static bool pfMerge($dstKey, $srcKeys, $cb = null)
 * Geocoding methods
 * @method static int geoAdd($key, $longitude, $latitude, $member, ...$items, $cb = null)
 * @method static array geoHash($key, ...$members, $cb = null)
 * @method static array geoPos($key, ...$members, $cb = null)
 * @method static double geoDist($key, $members, $unit = '', $cb = null)
 * @method static int|array geoRadius($key, $longitude, $latitude, $radius, $unit, $options = [], $cb = null)
 * @method static array geoRadiusByMember($key, $member, $radius, $units, $options = [], $cb = null)
 * Streams methods
 * @method static int xAck($stream, $group, $arrMessages, $cb = null)
 * @method static string xAdd($strKey, $strId, $arrMessage, $iMaxLen = 0, $booApproximate = false, $cb = null)
 * @method static array xClaim($strKey, $strGroup, $strConsumer, $minIdleTime, $arrIds, $arrOptions = [], $cb = null)
 * @method static int xDel($strKey, $arrIds, $cb = null)
 * @method static mixed xGroup($command, $strKey, $strGroup, $strMsgId, $booMKStream = null, $cb = null)
 * @method static mixed xInfo($command, $strStream, $strGroup = null, $cb = null)
 * @method static int xLen($stream, $cb = null)
 * @method static array xPending($strStream, $strGroup, $strStart = 0, $strEnd = 0, $iCount = 0, $strConsumer = null, $cb = null)
 * @method static array xRange($strStream, $strStart, $strEnd, $iCount = 0, $cb = null)
 * @method static array xRead($arrStreams, $iCount = 0, $iBlock = null, $cb = null)
 * @method static array xReadGroup($strGroup, $strConsumer, $arrStreams, $iCount = 0, $iBlock = null, $cb = null)
 * @method static array xRevRange($strStream, $strEnd, $strStart, $iCount = 0, $cb = null)
 * @method static int xTrim($strStream, $iMaxLen, $booApproximate = null, $cb = null)
 * Pub/sub methods
 * @method static mixed publish($channel, $message, $cb = null)
 * @method static mixed pubSub($keyword, $argument = null, $cb = null)
 * Generic methods
 * @method static mixed rawCommand(...$commandAndArgs, $cb = null)
 * Transactions methods
 * @method static multi($cb = null)
 * @method static mixed exec($cb = null)
 * @method static mixed discard($cb = null)
 * @method static mixed watch($keys, $cb = null)
 * @method static mixed unwatch($keys, $cb = null)
 * Scripting methods
 * @method static mixed eval($script, $args = [], $numKeys = 0, $cb = null)
 * @method static mixed evalSha($sha, $args = [], $numKeys = 0, $cb = null)
 * @method static mixed script($command, ...$scripts, $cb = null)
 * @method static mixed client(...$args, $cb = null)
 * @method static null|string getLastError($cb = null)
 * @method static bool clearLastError($cb = null)
 * @method static mixed _prefix($value, $cb = null)
 * @method static mixed _serialize($value, $cb = null)
 * @method static mixed _unserialize($value, $cb = null)
 * Introspection methods
 * @method static bool isConnected($cb = null)
 * @method static mixed getHost($cb = null)
 * @method static mixed getPort($cb = null)
 * @method static false|int getDbNum($cb = null)
 * @method static false|double getTimeout($cb = null)
 * @method static mixed getReadTimeout($cb = null)
 * @method static mixed getPersistentID($cb = null)
 * @method static mixed getAuth($cb = null)
 */
#[\AllowDynamicProperties]
class Client
{
    /**
     * @var AsyncTcpConnection
     */
    protected $_connection = null;

    /**
     * @var array
     */
    protected $_options = [];

    /**
     * @var string
     */
    protected $_address = '';

    /**
     * @var array
     */
    protected $_queue = [];

    /**
     * @var int
     */
    protected $_db = 0;

    /**
     * @var string|array
     */
    protected $_auth = null;

    /**
     * @var bool
     */
    protected $_waiting = true;

    /**
     * @var Timer
     */
    protected $_connectTimeoutTimer = null;

    /**
     * @var Timer
     */
    protected $_reconnectTimer = null;

    /**
     * @var callable
     */
    protected $_connectionCallback = null;

    /**
     * @var Timer
     */
    protected $_waitTimeoutTimer = null;

    /**
     * @var string
     */
    protected $_error = '';

    /**
     * @var bool
     */
    protected $_subscribe = false;

    /**
     * @var bool
     */
    protected $_firstConnect = true;

    /**
     * Client constructor.
     * @param $address
     * @param array $options
     * @param null $callback
     */
    public function __construct($address, $options = [], $callback = null)
    {
        if (!\class_exists('Protocols\Redis')) {
            \class_alias('Workerman\Redis\Protocols\Redis', 'Protocols\Redis');
        }
        $this->_address = $address;
        $this->_options = $options;
        $this->_connectionCallback = $callback;
        $this->connect();
        $timer = Timer::add(1, function () use (&$timer) {
            if (empty($this->_queue)) {
                return;
            }
            if ($this->_subscribe) {
                Timer::del($timer);
                return;
            }
            reset($this->_queue);
            $current_queue = current($this->_queue);
            $current_command = $current_queue[0][0];
            $ignore_first_queue = in_array($current_command, ['BLPOP', 'BRPOP']);
            $time = time();
            $timeout = isset($this->_options['wait_timeout']) ? $this->_options['wait_timeout'] : 600;
            $has_timeout = false;
            $first_queue = true;
            foreach ($this->_queue as $key => $queue) {
                if ($first_queue && $ignore_first_queue) {
                    $first_queue = false;
                    continue;
                }
                if ($time - $queue[1] > $timeout) {
                    $has_timeout = true;
                    unset($this->_queue[$key]);
                    $msg = "Workerman Redis Wait Timeout ($timeout seconds)";
                    if ($queue[2]) {
                        $this->_error = $msg;
                        \call_user_func($queue[2], false, $this);
                    } else {
                        echo new Exception($msg);
                    }
                }
            }
            if ($has_timeout && !$ignore_first_queue) {
                $this->closeConnection();
                $this->connect();
            }
        });
    }

    /**
     * connect
     */
    public function connect()
    {
        if ($this->_connection) {
            return;
        }

        $timeout = isset($this->_options['connect_timeout']) ? $this->_options['connect_timeout'] : 5;
        $context = isset($this->_options['context']) ? $this->_options['context'] : [];
        $this->_connection = new AsyncTcpConnection($this->_address, $context);
        $this->_connection->protocol = Redis::class;
        if (stripos($this->_address, 'unix://') === 0) {
            $this->_connection->transport = 'unix';
        }
        if(!empty($this->_options['ssl'])){
            $this->_connection->transport = 'ssl';
        }

        $this->_connection->onConnect = function () {
            $this->_waiting = false;
            Timer::del($this->_connectTimeoutTimer);
            if ($this->_reconnectTimer) {
                Timer::del($this->_reconnectTimer);
                $this->_reconnectTimer = null;
            }
          
            if ($this->_db && ($this->_queue[0][0][0] ?? '') !== 'SELECT' && ($this->_queue[0][0][0] ?? '') !== 'AUTH' && ($this->_queue[1][0][0] ?? '') !== 'SELECT') {
                $this->_queue = \array_merge([[['SELECT', $this->_db], time(), null]], $this->_queue);
            }

            if ($this->_auth && ($this->_queue[0][0][0] ?? '') !== 'AUTH') {
                $this->_queue = \array_merge([[['AUTH', $this->_auth], time(), null]],  $this->_queue);
            }

            $this->_connection->onError = function ($connection, $code, $msg) {
                echo new \Exception("Workerman Redis Connection Error $code $msg");
            };
            $this->process();
            $this->_firstConnect && $this->_connectionCallback && \call_user_func($this->_connectionCallback, true, $this);
            $this->_firstConnect = false;
        };

        $time_start = microtime(true);
        $this->_connection->onError = function ($connection) use ($time_start) {
            $time = microtime(true) - $time_start;
            $msg = "Workerman Redis Connection Failed ($time seconds)";
            $this->_error = $msg;
            $exception = new \Exception($msg);
            if (!$this->_connectionCallback) {
                echo $exception;
                return;
            }
            $this->_firstConnect && \call_user_func($this->_connectionCallback, false, $this);
        };

        $this->_connection->onClose = function () use ($time_start) {
            $this->_subscribe = false;
            if ($this->_connectTimeoutTimer) {
                Timer::del($this->_connectTimeoutTimer);
            }
            if ($this->_reconnectTimer) {
                Timer::del($this->_reconnectTimer);
                $this->_reconnectTimer = null;
            }
            $this->closeConnection();
            if (microtime(true) - $time_start > 5) {
                $this->connect();
            } else {
                $this->_reconnectTimer = Timer::add(5, function () {
                    $this->connect();
                }, null, false);
            }
        };

        $this->_connection->onMessage = function ($connection, $data) {
            $this->_error = '';
            $this->_waiting = false;
            reset($this->_queue);
            $queue = current($this->_queue);
            $cb = $queue[2];
            $type = $data[0];
            if (!$this->_subscribe) {
                unset($this->_queue[key($this->_queue)]);
            }
            if (empty($this->_queue)) {
                $this->_queue = [];
            }
            $success = !($type === '-' || $type === '!');
            $exception = false;
            $result = false;
            if ($success) {
                $result = $data[1];
                if ($type === '+' && $result === 'OK') {
                    $result = true;
                }
            } else {
                $this->_error = $data[1];
            }
            if (!$cb) {
                $this->process();
                return;
            }
            // format.
            if (!empty($queue[3])) {
                $result = \call_user_func($queue[3], $result);
            }
            try {
                \call_user_func($cb, $result, $this);
            } catch (\Exception $exception) {
            }

            if ($type === '!') {
                $this->closeConnection();
                $this->connect();
            } else {
                $this->process();
            }
            if ($exception) {
                throw $exception;
            }
        };

        $this->_connectTimeoutTimer = Timer::add($timeout, function () use ($timeout) {
            $this->_connectTimeoutTimer = null;
            if ($this->_connection && $this->_connection->getStatus(false) === 'ESTABLISHED') {
                return;
            }
            $this->closeConnection();
            $this->_error = "Workerman Redis Connection to {$this->_address} timeout ({$timeout} seconds)";
            if ($this->_firstConnect && $this->_connectionCallback) {
                \call_user_func($this->_connectionCallback, false, $this);
            } else {
                echo $this->_error . "\n";
            }

        });
        $this->_connection->connect();
    }

    /**
     * process
     */
    public function process()
    {
        if (!$this->_connection || $this->_waiting || empty($this->_queue) || $this->_subscribe) {
            return;
        }
        \reset($this->_queue);
        $queue = \current($this->_queue);
        if ($queue[0][0] === 'SUBSCRIBE' || $queue[0][0] === 'PSUBSCRIBE') {
            $this->_subscribe = true;
        }
        $this->_waiting = true;
        $this->_connection->send($queue[0]);
        $this->_error = '';
    }

    /**
     * subscribe
     *
     * @param $channels
     * @param $cb
     */
    public function subscribe($channels, $cb)
    {
        $new_cb = function ($result) use ($cb) {
            if (!$result) {
                echo $this->error();
                return;
            }
            $response_type = $result[0];
            switch ($response_type) {
                case 'subscribe':
                    return;
                case 'message':
                    \call_user_func($cb, $result[1], $result[2], $this);
                    return;
                default:
                    echo 'unknow response type for subscribe. buffer:' . serialize($result) . "\n";
            }
        };
        $this->_queue[] = [['SUBSCRIBE', $channels], time(), $new_cb];
        $this->process();
    }

    /**
     * psubscribe
     *
     * @param $patterns
     * @param $cb
     */
    public function pSubscribe($patterns, $cb)
    {
        $new_cb = function ($result) use ($cb) {
            if (!$result) {
                echo $this->error();
                return;
            }
            $response_type = $result[0];
            switch ($response_type) {
                case 'psubscribe':
                    return;
                case 'pmessage':
                    \call_user_func($cb, $result[1], $result[2], $result[3], $this);
                    return;
                default:
                    echo 'unknow response type for psubscribe. buffer:' . serialize($result) . "\n";
            }
        };
        $this->_queue[] = [['PSUBSCRIBE', $patterns], time(), $new_cb];
        $this->process();
    }

    /**
     * select
     *
     * @param $db
     * @param null $cb
     * @return mixed
     */
    public function select($db, $cb = null)
    {
        $format = function ($result) use ($db) {
            $this->_db = $db;
            return $result;
        };
        $need_suspend = !$cb && class_exists(EventLoop::class, false);
        if ($need_suspend) {
            [$suspension, $cb] = $this->suspenstion();
        }
        $cb = $cb ?: function () {
        };
        $this->_queue[] = [['SELECT', $db], time(), $cb, $format];
        $this->process();
        if ($need_suspend) {
            return $suspension->suspend();
        }
        return null;
    }

    /**
     * auth
     *
     * @param string|array $auth
     * @param null $cb
     * @return mixed
     */
    public function auth($auth, $cb = null)
    {
        $format = function ($result) use ($auth) {
            $this->_auth = $auth;
            return $result;
        };
        $need_suspend = !$cb && class_exists(EventLoop::class, false);
        if ($need_suspend) {
            [$suspension, $cb] = $this->suspenstion();
        }
        $cb = $cb ?: function () {
        };
        $this->_queue[] = [['AUTH', $auth], time(), $cb, $format];
        $this->process();
        if ($need_suspend) {
            return $suspension->suspend();
        }
        return null;
    }

    /**
     * set
     *
     * @param $key
     * @param $value
     * @param null $cb
     * @return mixed
     */
    public function set($key, $value, $cb = null)
    {
        $args = func_get_args();
        if ($cb !== null && !\is_callable($cb)) {
            $timeout = $cb;
            $cb = null;
            if (\count($args) > 3) {
                $cb = $args[3];
            }
            $need_suspend = !$cb && class_exists(EventLoop::class, false);
            if ($need_suspend) {
                [$suspension, $cb] = $this->suspenstion();
            }
            $this->_queue[] = [['SETEX', $key, $timeout, $value], time(), $cb];
            $this->process();
            if ($need_suspend) {
                return $suspension->suspend();
            }
            return null;
        }
        $need_suspend = !$cb && class_exists(EventLoop::class, false);
        if ($need_suspend) {
            [$suspension, $cb] = $this->suspenstion();
        }
        $this->_queue[] = [['SET', $key, $value], time(), $cb];
        $this->process();
        if ($need_suspend) {
            return $suspension->suspend();
        }
        return null;
    }

    /**
     * incr
     *
     * @param $key
     * @param null $cb
     * @return mixed
     */
    public function incr($key, $cb = null)
    {
        $args = func_get_args();
        if ($cb !== null && !\is_callable($cb)) {
            $num = $cb;
            $cb = null;
            if (\count($args) > 2) {
                $cb = $args[2];
            }
            $need_suspend = !$cb && class_exists(EventLoop::class, false);
            if ($need_suspend) {
                [$suspension, $cb] = $this->suspenstion();
            }
            $this->_queue[] = [['INCRBY', $key, $num], time(), $cb];
            $this->process();
            if ($need_suspend) {
                return $suspension->suspend();
            }
            return null;
        }
        $need_suspend = !$cb && class_exists(EventLoop::class, false);
        if ($need_suspend) {
            [$suspension, $cb] = $this->suspenstion();
        }
        $this->_queue[] = [['INCR', $key], time(), $cb];
        $this->process();
        if ($need_suspend) {
            return $suspension->suspend();
        }
        return null;
    }


    /**
     * decr
     *
     * @param $key
     * @param null $cb
     * @return mixed
     */
    public function decr($key, $cb = null)
    {
        $args = func_get_args();
        if ($cb !== null && !\is_callable($cb)) {
            $num = $cb;
            $cb = null;
            if (\count($args) > 2) {
                $cb = $args[2];
            }
            $need_suspend = !$cb && class_exists(EventLoop::class, false);
            if ($need_suspend) {
                [$suspension, $cb] = $this->suspenstion();
            }
            $this->_queue[] = [['DECRBY', $key, $num], time(), $cb];
            $this->process();
            if ($need_suspend) {
                return $suspension->suspend();
            }
            return null;
        }
        $need_suspend = !$cb && class_exists(EventLoop::class, false);
        if ($need_suspend) {
            [$suspension, $cb] = $this->suspenstion();
        }
        $this->_queue[] = [['DECR', $key], time(), $cb];
        $this->process();
        if ($need_suspend) {
            return $suspension->suspend();
        }
        return null;
    }

    /**
     * sort
     *
     * @param $key
     * @param $options
     * @param null $cb
     * @return mixed
     */
    function sort($key, $options, $cb = null)
    {
        $args = [];
        if (isset($options['sort'])) {
            $args[] = $options['sort'];
            unset($options['sort']);
        }

        foreach ($options as $op => $value) {
            $args[] = $op;
            if (!is_array($value)) {
                $args[] = $value;
                continue;
            }
            foreach ($value as $sub_value) {
                $args[] = $sub_value;
            }
        }
        \array_unshift($args, 'SORT', $key);
        $need_suspend = !$cb && class_exists(EventLoop::class, false);
        if ($need_suspend) {
            [$suspension, $cb] = $this->suspenstion();
        }
        $this->_queue[] = [$args, time(), $cb];
        $this->process();
        if ($need_suspend) {
            return $suspension->suspend();
        }
        return null;
    }

    /**
     * mSet
     *
     * @param array $array
     * @param null $cb
     */
    public function mSet(array $array, $cb = null)
    {
        return $this->mapCb('MSET', $array, $cb);
    }

    /**
     * mSetNx
     *
     * @param array $array
     * @param null $cb
     */
    public function mSetNx(array $array, $cb = null)
    {
        return $this->mapCb('MSETNX', $array, $cb);
    }

    /**
     * mapCb
     *
     * @param $command
     * @param array $array
     * @param $cb
     * @return mixed
     */
    protected function mapCb($command, array $array, $cb)
    {
        $args = [$command];
        foreach ($array as $key => $value) {
            $args[] = $key;
            $args[] = $value;
        }
        $need_suspend = !$cb && class_exists(EventLoop::class, false);
        if ($need_suspend) {
            [$suspension, $cb] = $this->suspenstion();
        }
        $this->_queue[] = [$args, time(), $cb];
        $this->process();
        if ($need_suspend) {
            return $suspension->suspend();
        }
        return null;
    }

    /**
     * hMSet
     *
     * @param $key
     * @param array $array
     * @param null $cb
     * @return mixed
     */
    public function hMSet($key, array $array, $cb = null)
    {
        return $this->keyMapCb('HMSET', $key, $array, $cb);
    }

    /**
     * hMGet
     *
     * @param $key
     * @param array $array
     * @param null $cb
     * @return mixed
     */
    public function hMGet($key, array $array, $cb = null)
    {
        $format = function ($result) use ($array) {
            if (!is_array($result)) {
                return $result;
            }
            return \array_combine($array, $result);
        };
        $need_suspend = !$cb && class_exists(EventLoop::class, false);
        if ($need_suspend) {
            [$suspension, $cb] = $this->suspenstion();
        }
        $this->_queue[] = [['HMGET', $key, $array], time(), $cb, $format];
        $this->process();
        if ($need_suspend) {
            return $suspension->suspend();
        }
        return null;
    }

    /**
     * hGetAll
     *
     * @param $key
     * @param null $cb
     * @return mixed
     */
    public function hGetAll($key, $cb = null)
    {
        $format = function ($result) {
            if (!\is_array($result)) {
                return $result;
            }
            $return = [];
            $key = '';
            foreach ($result as $index => $item) {
                if ($index % 2 == 0) {
                    $key = $item;
                    continue;
                }
                $return[$key] = $item;
            }
            return $return;
        };
        $need_suspend = !$cb && class_exists(EventLoop::class, false);
        if ($need_suspend) {
            [$suspension, $cb] = $this->suspenstion();
        }
        $this->_queue[] = [['HGETALL', $key], time(), $cb, $format];
        $this->process();
        if ($need_suspend) {
            return $suspension->suspend();
        }
        return null;
    }

    /**
     * keyMapCb
     *
     * @param $command
     * @param $key
     * @param array $array
     * @param $cb
     * @return mixed
     */
    protected function keyMapCb($command, $key, array $array, $cb)
    {
        $args = [$command, $key];
        foreach ($array as $key => $value) {
            $args[] = $key;
            $args[] = $value;
        }
        $need_suspend = !$cb && class_exists(EventLoop::class, false);
        if ($need_suspend) {
            [$suspension, $cb] = $this->suspenstion();
        }
        $this->_queue[] = [$args, time(), $cb];
        $this->process();
        if ($need_suspend) {
            return $suspension->suspend();
        }
        return null;
    }

    /**
     * __call
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $cb = null;
        if (count($args) > 1 || in_array($method, ['randomKey', 'multi', 'exec', 'discard'])) {
            if (\is_callable(end($args))) {
                $cb = array_pop($args);
            }
        }

        \array_unshift($args, \strtoupper($method));
        $need_suspend = !$cb && class_exists(EventLoop::class, false);
        if ($need_suspend) {
            [$suspension, $cb] = $this->suspenstion();
        }
        $this->_queue[] = [$args, time(), $cb];
        $this->process();
        if ($need_suspend) {
            return $suspension->suspend();
        }
        return null;
    }

    /**
     * @return array
     */
    protected function suspenstion()
    {
        $suspension = EventLoop::getSuspension();
        $cb = function ($result) use ($suspension) {
            $suspension->resume($result);
        };
        return [$suspension, $cb];
    }

    /**
     * closeConnection
     */
    public function closeConnection()
    {
        if (!$this->_connection) {
            return;
        }
        $this->_subscribe = false;
        $this->_connection->onConnect = $this->_connection->onError = $this->_connection->onClose =
        $this->_connection->onMessage = null;
        $this->_connection->close();
        $this->_connection = null;
        if ($this->_connectTimeoutTimer) {
            Timer::del($this->_connectTimeoutTimer);
        }
        if ($this->_reconnectTimer) {
            Timer::del($this->_reconnectTimer);
        }
    }

    /**
     * error
     *
     * @return string
     */
    function error()
    {
        return $this->_error;
    }

    /**
     * close
     */
    public function close()
    {
        $this->closeConnection();
        $this->_queue = [];
        gc_collect_cycles();
        if (function_exists('gc_mem_caches')) {
            gc_mem_caches();
        }
    }

    /**
     * scan
     *
     * @throws Exception
     */
    public function scan()
    {
        throw new Exception('Not implemented');
    }

    /**
     * hScan
     *
     * @throws Exception
     */
    public function hScan()
    {
        throw new Exception('Not implemented');
    }

    /**
     * hScan
     *
     * @throws Exception
     */
    public function sScan()
    {
        throw new Exception('Not implemented');
    }

    /**
     * hScan
     *
     * @throws Exception
     */
    public function zScan()
    {
        throw new Exception('Not implemented');
    }

}
