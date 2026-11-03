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
 * @method static string|null getDel($key, $cb = null)
 * @method static string|null getEx($key, array $options = [], $cb = null)
 * @method static string getRange($key, $start, $end, $cb = null)
 * @method static string getSet($key, $value, $cb = null)
 * @method static int incrBy($key, $value, $cb = null)
 * @method static float incrByFloat($key, $value, $cb = null)
 * @method static array mGet(array $keys, $cb = null)
 * @method static bool setBit($key, $offset, $value, $cb = null)
 * @method static bool setEx($key, $ttl, $value, $cb = null)
 * @method static bool pSetEx($key, $ttl, $value, $cb = null)
 * @method static bool setNx($key, $value, $cb = null)
 * @method static string setRange($key, $offset, $value, $cb = null)
 * @method static string substr($key, $start, $end, $cb = null)
 * @method static int strLen($key, $cb = null)
 * @method static array sortRo($key, $options = [], $cb = null)
 * Keys methods
 * @method static int copy($src, $dst, array $options = [], $cb = null)
 * @method static int del(...$keys)
 * @method static int unlink(...$keys)
 * @method static false|string dump($key, $cb = null)
 * @method static int exists(...$keys)
 * @method static bool expire($key, $ttl, $cb = null)
 * @method static bool pexpire($key, $ttl, $cb = null)
 * @method static bool expireAt($key, $timestamp, $cb = null)
 * @method static bool pexpireAt($key, $timestamp, $cb = null)
 * @method static int expireTime($key, $cb = null)
 * @method static int pExpireTime($key, $cb = null)
 * @method static array keys($pattern, $cb = null)
 * @method static void migrate($host, $port, $keys, $dbIndex, $timeout, $copy = false, $replace = false, $cb = null)
 * @method static bool move($key, $dbIndex, $cb = null)
 * @method static string|int|bool object($information, $key, $cb = null)
 * @method static bool persist($key, $cb = null)
 * @method static string randomKey($cb = null)
 * @method static bool rename($srcKey, $dstKey, $cb = null)
 * @method static bool renameNx($srcKey, $dstKey, $cb = null)
 * @method static int touch(...$keys)
 * @method static string type($key, $cb = null)
 * @method static int ttl($key, $cb = null)
 * @method static int pttl($key, $cb = null)
 * @method static void restore($key, $ttl, $value, $cb = null)
 * @method static array|null scan($cursor, array $options = [], $cb = null)
 * @method static array|false|null scanAll(array $options = [], $cb = null)
 * Hashes methods
 * @method static false|int hSet($key, $hashKey, $value, $cb = null)
 * @method static bool hSetNx($key, $hashKey, $value, $cb = null)
 * @method static false|string hGet($key, $hashKey, $cb = null)
 * @method static false|int hLen($key, $cb = null)
 * @method static false|int hDel($key, ...$hashKeys)
 * @method static array hKeys($key, $cb = null)
 * @method static array hVals($key, $cb = null)
 * @method static bool hExists($key, $hashKey, $cb = null)
 * @method static int hIncrBy($key, $hashKey, $value, $cb = null)
 * @method static float hIncrByFloat($key, $hashKey, $value, $cb = null)
 * @method static int hStrLen($key, $hashKey, $cb = null)
 * @method static string|array hRandField($key, $count = null, $withValues = false, $cb = null)
 * @method static array|null hScan($key, $cursor, array $options = [], $cb = null)
 * @method static array|false|null hScanAll($key, array $options = [], $cb = null)
 * @method static array hExpire($key, $seconds, $fieldsOrOptions, ...$fields)
 * @method static array hPersist($key, $fieldsOrOptions, ...$fields)
 * @method static array hExpireAt($key, $timestamp, $fieldsOrOptions, ...$fields)
 * @method static array hTtl($key, $fieldsOrOptions, ...$fields)
 * @method static array hExpireTime($key, $fieldsOrOptions, ...$fields)
 * @method static array hPExpire($key, $milliseconds, $fieldsOrOptions, ...$fields)
 * @method static array hPExpireAt($key, $timestamp, $fieldsOrOptions, ...$fields)
 * @method static array hPTtl($key, $fieldsOrOptions, ...$fields)
 * @method static array hPExpireTime($key, $fieldsOrOptions, ...$fields)
 * Lists methods
 * @method static array blPop($keys, $timeout, $cb = null)
 * @method static array brPop($keys, $timeout, $cb = null)
 * @method static false|string bRPopLPush($srcKey, $dstKey, $timeout, $cb = null)
 * @method static false|string lIndex($key, $index, $cb = null)
 * @method static int lInsert($key, $position, $pivot, $value, $cb = null)
 * @method static false|string lPop($key, $cb = null)
 * @method static false|int lPush($key, ...$entries)
 * @method static false|int lPushx($key, $value, $cb = null)
 * @method static array lRange($key, $start, $end, $cb = null)
 * @method static false|int lRem($key, $value, $count, $cb = null)
 * @method static bool lSet($key, $index, $value, $cb = null)
 * @method static false|array lTrim($key, $start, $end, $cb = null)
 * @method static false|string rPop($key, $cb = null)
 * @method static false|string rPopLPush($srcKey, $dstKey, $cb = null)
 * @method static false|int rPush($key, ...$entries)
 * @method static false|int rPushX($key, $value, $cb = null)
 * @method static false|int lLen($key, $cb = null)
 * @method static false|string lMove($src, $dst, $srcWhere, $dstWhere, $cb = null)
 * @method static array|null lMPop($keys, $where, $count = 1, $cb = null)
 * @method static int|false lPos($key, $element, array $options = [], $cb = null)
 * @method static false|string blMove($src, $dst, $srcWhere, $dstWhere, $timeout, $cb = null)
 * @method static array|null blMPop($timeout, $keys, $where, $count = 1, $cb = null)
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
 * @method static int sRem($key, ...$members)
 * @method static array sUnion(...$keys)
 * @method static false|int sUnionStore($dst, ...$keys)
 * @method static array sMIsMember($key, ...$members)
 * @method static int sInterCard($numkeys, $keys, $limit = 0, $cb = null)
 * @method static array|null sScan($key, $cursor, array $options = [], $cb = null)
 * @method static array|false|null sScanAll($key, array $options = [], $cb = null)
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
 * @method static int zRem($key, ...$members)
 * @method static int zRemRangeByRank($key, $start, $end, $cb = null)
 * @method static int zRemRangeByScore($key, $start, $end, $cb = null)
 * @method static array zRevRange($key, $start, $end, $withScores = false, $cb = null)
 * @method static double zScore($key, $member, $cb = null)
 * @method static int zunionstore($keyOutput, $arrayZSetKeys, $arrayWeights = [], $aggregateFunction = '', $cb = null)
 * @method static array zRandMember($key, $count = null, $withScores = false, $cb = null)
 * @method static array zMScore($key, ...$members)
 * @method static array zDiff($numkeys, $keys, $withScores = false, $cb = null)
 * @method static int zDiffStore($dst, $numkeys, $keys, $cb = null)
 * @method static array zInter($numkeys, $keys, array $options = [], $cb = null)
 * @method static int zInterCard($numkeys, $keys, $limit = 0, $cb = null)
 * @method static array zUnion($numkeys, $keys, array $options = [], $cb = null)
 * @method static int zRangeStore($dst, $src, $min, $max, array $options = [], $cb = null)
 * @method static array|null zMPop($numkeys, $keys, $where, $count = 1, $cb = null)
 * @method static array|null bzMPop($timeout, $numkeys, $keys, $where, $count = 1, $cb = null)
 * @method static array zRevRangeByLex($key, $max, $min, $offset = 0, $count = 0, $cb = null)
 * @method static int zRemRangeByLex($key, $min, $max, $cb = null)
 * @method static int zLexCount($key, $min, $max, $cb = null)
 * @method static array|null zScan($key, $cursor, array $options = [], $cb = null)
 * @method static array|false|null zScanAll($key, array $options = [], $cb = null)
 * HyperLogLogs methods
 * @method static int pfAdd($key, $values, $cb = null)
 * @method static int pfCount($keys, $cb = null)
 * @method static bool pfMerge($dstKey, $srcKeys, $cb = null)
 * Bitmap methods
 * @method static int bitOp($operation, $destKey, ...$keys)
 * @method static int bitPos($key, $bit, $start = 0, $end = -1, $byte = false, $cb = null)
 * @method static array bitField($key, ...$ops)
 * @method static array bitFieldRo($key, ...$ops)
 * Geocoding methods
 * @method static int geoAdd($key, $longitude, $latitude, $member, ...$items)
 * @method static array geoHash($key, ...$members)
 * @method static array geoPos($key, ...$members)
 * @method static double geoDist($key, $members, $unit = '', $cb = null)
 * @method static int|array geoRadius($key, $longitude, $latitude, $radius, $unit, $options = [], $cb = null)
 * @method static array geoRadiusByMember($key, $member, $radius, $units, $options = [], $cb = null)
 * @method static array geoSearch($key, $from, $by, array $options = [], $cb = null)
 * @method static array geoRadiusRo($key, $longitude, $latitude, $radius, $unit, array $options = [], $cb = null)
 * @method static array geoRadiusByMemberRo($key, $member, $radius, $unit, array $options = [], $cb = null)
 * JSON module (RedisJSON-compatible — supported by Dragonfly)
 * @method static mixed json(...$args)
 * @method static bool jsonSet($key, $path, $value, $cb = null)
 * @method static string|array jsonGet($key, ...$pathsAndCb)
 * @method static int jsonDel($key, $path = '$', $cb = null)
 * @method static int jsonForget($key, $path = '$', $cb = null)
 * @method static array jsonMGet(array $keys, $path = '$', $cb = null)
 * @method static bool jsonMSet(array $tuples, $cb = null)
 * @method static bool jsonMerge($key, $path, $value, $cb = null)
 * @method static array jsonArrAppend($key, $path, ...$valuesAndCb)
 * @method static array jsonArrLen($key, $path = '$', $cb = null)
 * @method static array jsonObjKeys($key, $path = '$', $cb = null)
 * @method static array jsonObjLen($key, $path = '$', $cb = null)
 * @method static array jsonType($key, $path = '$', $cb = null)
 * @method static array jsonNumIncrBy($key, $path, $by, $cb = null)
 * @method static array jsonStrAppend($key, $path, $value, $cb = null)
 * @method static array jsonStrLen($key, $path = '$', $cb = null)
 * @method static array jsonToggle($key, $path, $cb = null)
 * Bloom Filter module (RedisBloom-compatible — supported by Dragonfly)
 * @method static mixed bf(...$args)
 * @method static bool bfReserve($key, $errorRate, $capacity, $cb = null)
 * @method static int bfAdd($key, $item, $cb = null)
 * @method static int bfExists($key, $item, $cb = null)
 * @method static array bfMAdd($key, ...$itemsAndCb)
 * @method static array bfMExists($key, ...$itemsAndCb)
 * Count-Min Sketch module (RedisBloom-compatible — supported by Dragonfly)
 * @method static mixed cms(...$args)
 * @method static bool cmsInitByDim($key, $width, $depth, $cb = null)
 * @method static bool cmsInitByProb($key, $error, $probability, $cb = null)
 * @method static array cmsIncrBy($key, ...$pairsAndCb)
 * @method static array cmsQuery($key, ...$itemsAndCb)
 * @method static bool cmsMerge($dest, $numKeys, array $sources, ?array $weights = null, $cb = null)
 * @method static array cmsInfo($key, $cb = null)
 * TopK module (RedisBloom-compatible — supported by Dragonfly)
 * @method static mixed topk(...$args)
 * @method static bool topkReserve($key, $topk, $width = 8, $depth = 7, $decay = 0.9, $cb = null)
 * @method static array topkAdd($key, ...$itemsAndCb)
 * @method static array topkIncrBy($key, ...$pairsAndCb)
 * @method static array topkQuery($key, ...$itemsAndCb)
 * @method static array topkCount($key, ...$itemsAndCb)
 * @method static array topkList($key, $cb = null)
 * @method static array topkInfo($key, $cb = null)
 * Streams methods
 * @method static int xAck($stream, $group, $arrMessages, $cb = null)
 * @method static string xAdd($key, $id, array $message, $maxLen = 0, $approximate = false, $cb = null)
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
 * @method static array xAutoClaim($key, $group, $consumer, $minIdleMs, $start, array $options = [], $cb = null)
 * @method static bool xSetId($key, $lastId, array $options = [], $cb = null)
 * Pub/sub methods
 * @method static mixed publish($channel, $message, $cb = null)
 * @method static int sPublish($channel, $message, $cb = null)
 * @method static mixed pubSub($keyword, $argument = null, $cb = null)
 * @method static void sSubscribe($channels, $cb)
 * @method static void unsubscribe(...$channelsAndCb)
 * @method static void pUnsubscribe(...$patternsAndCb)
 * @method static void sUnsubscribe(...$channelsAndCb)
 * Connection / server methods
 * @method static string|bool ping($cb = null)
 * @method static string|bool quit($cb = null)
 * @method static string|null info($section = null, $cb = null)
 * @method static int|bool dbSize($cb = null)
 * @method static array|bool time($cb = null)
 * @method static bool flushDb($async = false, $cb = null)
 * @method static bool flushAll($async = false, $cb = null)
 * @method static string echo($message, $cb = null)
 * @method static array hello($protover = null, $cb = null)
 * Server administration
 * @method static mixed config(...$args)
 * @method static mixed acl(...$args)
 * @method static mixed slowLog(...$args)
 * @method static mixed memory(...$args)
 * @method static mixed command(...$args)
 * @method static mixed cluster(...$args)
 * @method static int lastSave($cb = null)
 * @method static bool save($cb = null)
 * @method static bool bgSave($schedule = false, $cb = null)
 * @method static array role($cb = null)
 * @method static void monitor($cb)
 * @method static bool shutdown($mode = 'SAVE', $cb = null)
 * @method static bool replicaOf($host, $port, $cb = null)
 * @method static bool slaveOf($host, $port, $cb = null)
 * @method static mixed debug(...$args)
 * @method static mixed module(...$args)
 * @method static array moduleList($cb = null)
 * @method static int delEx(...$keys) — Dragonfly extension
 * @method static string digest($cb = null) — Dragonfly extension
 * RedisSearch (FT) module — supported by Dragonfly
 * @method static mixed ft(...$args)
 * @method static bool ftCreate($index, ...$args)
 * @method static array ftSearch($index, $query, ...$optionsAndCb)
 * @method static array ftAggregate($index, $query, ...$optionsAndCb)
 * @method static bool ftDropIndex($index, $deleteDocs = false, $cb = null)
 * @method static array ftInfo($index, $cb = null)
 * @method static array ftList($cb = null)
 * @method static bool ftAlter($index, ...$args)
 * @method static mixed ftConfig(...$args)
 * @method static array ftTagVals($index, $field, $cb = null)
 * @method static array ftSynDump($index, $cb = null)
 * @method static bool ftSynUpdate($index, $groupId, ...$termsAndCb)
 * @method static array ftProfile($index, ...$args)
 * Generic methods
 * @method static mixed rawCommand(...$commandAndArgs)
 * Transactions methods
 * @method static multi($cb = null)
 * @method static mixed exec($cb = null)
 * @method static mixed discard($cb = null)
 * @method static mixed watch($keys, $cb = null)
 * @method static mixed unwatch($keys, $cb = null)
 * Scripting methods
 * @method static mixed eval($script, $args = [], $numKeys = 0, $cb = null)
 * @method static mixed evalSha($sha, $args = [], $numKeys = 0, $cb = null)
 * @method static mixed evalRo($script, $args = [], $numKeys = 0, $cb = null)
 * @method static mixed evalShaRo($sha, $args = [], $numKeys = 0, $cb = null)
 * @method static mixed script($command, ...$scripts)
 * @method static mixed client(...$args)
 * @method static mixed _prefix($value, $cb = null)
 * @method static mixed _serialize($value, $cb = null)
 * @method static mixed _unserialize($value, $cb = null)
 */
#[\AllowDynamicProperties]
class Client
{
    /**
     * Commands that legitimately hold the connection open until the server
     * replies (or the command's own server-side timeout fires). When one of
     * these is the in-flight head of the queue the wait-timeout scan must not
     * treat the connection as hung — nor time out the commands queued behind
     * it, which are simply waiting on the block rather than stalled.
     *
     * @var string[]
     */
    const BLOCKING_COMMANDS = [
        'BLPOP', 'BRPOP', 'BRPOPLPUSH', 'BLMOVE', 'BLMPOP',
        'BZPOPMIN', 'BZPOPMAX', 'BZMPOP',
    ];

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
     * Set to true while a MONITOR stream is active. Like $_subscribe it locks
     * the connection — process() stops dispatching queued commands and
     * onMessage() keeps the MONITOR entry pinned at the head of the queue so
     * its callback receives every line — but MONITOR has no unsubscribe, so the
     * only way to stop the stream is to close() the client.
     *
     * @var bool
     */
    protected $_monitoring = false;

    /**
     * @var bool
     */
    protected $_firstConnect = true;

    /**
     * Set to true when QUIT has been sent. Suppresses the onClose
     * auto-reconnect so the connection genuinely closes.
     *
     * @var bool
     */
    protected $_quitting = false;

    /**
     * Callbacks registered by unsubscribe() / pUnsubscribe() / sUnsubscribe(),
     * fired with (true, $client) once the connection has fully left subscribe
     * mode (the server reports zero remaining subscriptions).
     *
     * @var array
     */
    protected $_unsubscribeCallbacks = [];

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
        // Periodic wait-timeout scan. Store the handle so close() can delete it
        // — left dangling it would fire forever and, by capturing $this, keep
        // the client object alive (a leak in workers that create clients
        // dynamically).
        $this->_waitTimeoutTimer = Timer::add(1, function () {
            if (empty($this->_queue)) {
                return;
            }
            if ($this->_subscribe || $this->_monitoring) {
                // A subscribe / monitor stream pins an entry at the head of the
                // queue indefinitely; skip the timeout scan so it isn't evicted.
                // Don't delete the timer — when the stream ends (unsubscribe, a
                // rejected monitor, or a reconnect) it must resume guarding
                // queued commands again.
                return;
            }
            reset($this->_queue);
            $current_queue = current($this->_queue);
            $current_command = $current_queue[0][0];
            if (\in_array($current_command, self::BLOCKING_COMMANDS, true) || self::holdsForBlock($current_queue[0])) {
                // A blocking command at the head legitimately holds the
                // connection until it returns (or hits its own server-side
                // timeout); everything queued behind it is waiting on that
                // block, not hung. Time out none of it — otherwise queued
                // commands would be failed with a spurious "Wait Timeout"
                // while they had never even been sent.
                return;
            }
            $time = time();
            $timeout = isset($this->_options['wait_timeout']) ? $this->_options['wait_timeout'] : 600;
            $has_timeout = false;
            foreach ($this->_queue as $key => $queue) {
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
            if ($has_timeout) {
                $this->closeConnection();
                $this->connect();
            }
        });
    }

    /**
     * XREAD / XREADGROUP block only when the queued command carries BLOCK.
     * The token always sits before STREAMS, so a stream id of "BLOCK" does not count.
     *
     * @param array $args
     * @return bool
     */
    protected static function holdsForBlock(array $args)
    {
        $name = $args[0] ?? '';
        if ($name !== 'XREAD' && $name !== 'XREADGROUP') {
            return false;
        }
        foreach ($args as $arg) {
            if (!\is_string($arg)) {
                continue;
            }
            if (\strcasecmp($arg, 'STREAMS') === 0) {
                return false;
            }
            if (\strcasecmp($arg, 'BLOCK') === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $command
     * @param array  $prefix
     * @param mixed  $streams
     * @param int    $count
     * @param mixed  $block
     * @return array
     */
    protected function streamReadArgs($command, array $prefix, $streams, $count, $block)
    {
        if (!\is_array($streams) || $streams === []) {
            throw new \InvalidArgumentException($command . ' requires at least one stream');
        }
        $args = \array_merge([$command], $prefix);
        if ($count) {
            $args[] = 'COUNT';
            $args[] = $count;
        }
        if ($block !== null) {
            $args[] = 'BLOCK';
            $args[] = $block;
        }
        $i = 0;
        $list = true;
        foreach ($streams as $key => $unused) {
            if ($key !== $i++) {
                $list = false;
                break;
            }
        }
        if ($list) {
            return \array_merge($args, $streams);
        }
        $args[] = 'STREAMS';
        $ids = [];
        foreach ($streams as $key => $id) {
            $args[] = $key;
            $ids[] = $id;
        }
        foreach ($ids as $id) {
            $args[] = $id;
        }
        return $args;
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
            $this->_monitoring = false;
            if ($this->_connectTimeoutTimer) {
                Timer::del($this->_connectTimeoutTimer);
            }
            if ($this->_reconnectTimer) {
                Timer::del($this->_reconnectTimer);
                $this->_reconnectTimer = null;
            }
            $this->closeConnection();
            if ($this->_quitting) {
                // Intentional QUIT — do not auto-reconnect.
                return;
            }
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
            if (!$this->_subscribe && !$this->_monitoring) {
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
            // format runs even when there is no user callback. select()/auth()
            // rely on that to record _db/_auth; coroutine mode then suspends
            // on a null callback instead of a dummy closure.
            if (!empty($queue[3])) {
                $result = \call_user_func($queue[3], $result);
            }
            if (!$cb) {
                $this->process();
                return;
            }
            try {
                \call_user_func($cb, $result, $this);
            } catch (\Throwable $exception) {
                // Catch \Throwable (not just \Exception) so a user callback
                // that raises an Error — TypeError, DivisionByZeroError, … —
                // can't escape before process() pumps the next command and
                // wedge the queue. The captured throwable is re-thrown below,
                // after the pump has run.
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
        if (!$this->_connection || $this->_waiting || empty($this->_queue) || $this->_subscribe || $this->_monitoring) {
            return;
        }
        \reset($this->_queue);
        $queue = \current($this->_queue);
        if ($queue[0][0] === 'SUBSCRIBE' || $queue[0][0] === 'PSUBSCRIBE' || $queue[0][0] === 'SSUBSCRIBE') {
            $this->_subscribe = true;
        }
        if ($queue[0][0] === 'MONITOR') {
            $this->_monitoring = true;
        }
        $this->_waiting = true;
        $this->_connection->send($queue[0]);
        $this->_error = '';
    }

    /**
     * Queue a command for transmission and return immediately (callback mode)
     * or suspend the current fiber until the reply arrives (Revolt mode).
     *
     * Every explicit command method should funnel through this helper so the
     * suspension / non-suspension branches stay identical and bug-fixes apply
     * uniformly. When Revolt's EventLoop class is loaded and no callback was
     * provided, a Suspension is created, registered as the callback, and the
     * current fiber is suspended; the suspension is resumed by onMessage()
     * via the queue's stored callback.
     *
     * @param array         $args   The wire-level command parts, e.g. ['SET','key','value'].
     * @param callable|null $cb     User callback signature: function($result, Client $client).
     * @param callable|null $format Optional reshaper applied to the raw result before $cb.
     * @return mixed                The reply when suspended; null in pure callback mode.
     */
    protected function queueCommand(array $args, $cb = null, $format = null)
    {
        $need_suspend = !$cb && $this->inCoroutineMode();
        if ($need_suspend && ($this->_subscribe || $this->_monitoring)) {
            // In coroutine mode an ordinary command would suspend the current
            // fiber until its reply arrives — but process() refuses to send
            // anything while the connection is subscribe/monitor-locked, so the
            // reply (and the resume) can never come while the lock holds. That
            // is a silent, unrecoverable fiber hang. Fail loudly instead. Use a
            // dedicated Client for ordinary commands, or pass an explicit
            // callback (callback-mode commands queue and drain after the stream
            // ends rather than suspending).
            throw new Exception(
                'Cannot issue a coroutine-mode Redis command while the connection '
                . 'is in subscribe/monitor mode: the fiber could never be resumed. '
                . 'Use a separate Client, or pass an explicit callback.'
            );
        }
        $suspend = null;
        if ($need_suspend) {
            [$cb, $suspend] = $this->suspenstion();
        }
        if ($format === null) {
            $this->_queue[] = [$args, time(), $cb];
        } else {
            $this->_queue[] = [$args, time(), $cb, $format];
        }
        $this->process();
        if ($suspend) {
            return $suspend();
        }
        return null;
    }

    /**
     * True when a callback-less command should wait for the reply.
     *
     * The Fiber loop loads Revolt and suspends the current PHP fiber.
     * The Swoole and Swow loops do not load Revolt; they suspend the
     * native coroutine that Workerman is already running inside.
     */
    protected function inCoroutineMode(): bool
    {
        $loop = \class_exists(\Workerman\Worker::class, false) ? \Workerman\Worker::$eventLoopClass : null;
        if ($loop === \Workerman\Events\Swoole::class || $loop === \Workerman\Events\Swow::class) {
            return \class_exists(\Workerman\Coroutine::class) && \Workerman\Coroutine::isCoroutine();
        }
        return \class_exists(EventLoop::class, false);
    }

    /**
     * Dispatch a subcommand for a multi-verb family (CONFIG, ACL, SLOWLOG,
     * MEMORY, COMMAND, CLUSTER, CLIENT) or a dotted module command (JSON.*,
     * BF.*, CMS.*, TOPK.*, FT.*).
     *
     * The convention is: caller passes `$prefix` ending in either a space
     * ('CLUSTER ') for subcommand verbs or a dot ('JSON.') for module
     * commands. The first element of $args is the verb (uppercased here);
     * the rest are command arguments. A trailing callable in $args is
     * popped and treated as the callback.
     *
     * For dot-prefixed families the verb is glued onto the prefix to form a
     * single Redis token ('JSON.SET'); for space-prefixed families the verb
     * becomes a separate wire arg ('CLUSTER', 'INFO').
     *
     * @param string $prefix  Either 'FAMILY ' (space) or 'PREFIX.' (dot).
     * @param array  $args    [verb, ...args, optional callable].
     * @return mixed
     */
    protected function dispatcher($prefix, array $args)
    {
        $cb = null;
        if (!empty($args) && \is_callable(\end($args))) {
            $cb = \array_pop($args);
        }
        $verb = \strtoupper((string)\array_shift($args));
        if ($prefix !== '' && $prefix[\strlen($prefix) - 1] === '.') {
            \array_unshift($args, $prefix . $verb);
        } else {
            $head = \rtrim($prefix);
            \array_unshift($args, $head, $verb);
        }
        return $this->queueCommand($args, $cb);
    }

    /**
     * Whether a streaming command (SUBSCRIBE / PSUBSCRIBE / SSUBSCRIBE /
     * MONITOR) is already active OR sitting in the queue waiting to be sent.
     *
     * The flag pair ($_subscribe / $_monitoring) only flips once process()
     * actually puts the stream on the wire. Checking it alone misses the most
     * common misuse — two subscribe() calls in a row before the first frame has
     * been sent — where both entries are still queued and the flags are still
     * false. So this also scans the queue for a pending stream verb.
     *
     * @return bool
     */
    protected function streamActiveOrPending()
    {
        if ($this->_subscribe || $this->_monitoring) {
            return true;
        }
        foreach ($this->_queue as $entry) {
            $verb = $entry[0][0] ?? '';
            if ($verb === 'SUBSCRIBE' || $verb === 'PSUBSCRIBE'
                || $verb === 'SSUBSCRIBE' || $verb === 'MONITOR') {
                return true;
            }
        }
        return false;
    }

    /**
     * Guard a subscribe-family entry point against the single-stream limit.
     *
     * This client pins ONE stream entry at the head of the queue and routes
     * every incoming message to that entry's callback. A second subscribe while
     * a stream is active or pending can't be honoured — process() is locked, so
     * the frame would never reach the wire, and even if it did its messages
     * would be delivered to the first callback, not this one. Rather than drop
     * it silently, fail loudly: subscribe to every channel in a single call, or
     * use a separate Client per stream.
     *
     * @param  string $method The calling method name, for the error message.
     * @return void
     */
    protected function assertNoActiveStream($method)
    {
        if ($this->streamActiveOrPending()) {
            throw new Exception(
                "$method: the connection already has an active or pending "
                . 'subscribe/monitor stream. This client supports one stream per '
                . 'connection — pass all channels/patterns in a single subscribe '
                . 'call, or use a separate Client for the additional stream.'
            );
        }
    }

    /**
     * subscribe
     *
     * @param $channels
     * @param $cb
     */
    public function subscribe($channels, $cb)
    {
        $this->assertNoActiveStream('subscribe');
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
                case 'unsubscribe':
                case 'punsubscribe':
                case 'sunsubscribe':
                    // Any unsubscribe-family ack clears the lock — see
                    // handleUnsubscribeAck() for why all three types are accepted.
                    $this->handleUnsubscribeAck($result);
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
        $this->assertNoActiveStream('pSubscribe');
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
                case 'unsubscribe':
                case 'punsubscribe':
                case 'sunsubscribe':
                    // Any unsubscribe-family ack clears the lock — see
                    // handleUnsubscribeAck() for why all three types are accepted.
                    $this->handleUnsubscribeAck($result);
                    return;
                default:
                    echo 'unknow response type for psubscribe. buffer:' . serialize($result) . "\n";
            }
        };
        $this->_queue[] = [['PSUBSCRIBE', $patterns], time(), $new_cb];
        $this->process();
    }

    /**
     * Sharded subscribe — listen for SPUBLISH messages on one or more shard
     * channels. Mirrors subscribe() but uses SSUBSCRIBE / smessage instead of
     * SUBSCRIBE / message. The SSUBSCRIBE command flips $this->_subscribe via
     * process(), so the connection enters subscribe-mode just like the regular
     * subscribe(). Call sUnsubscribe() to drop the shard subscription and hand
     * the connection back for ordinary commands (or close() the client).
     *
     * @param string|array $channels Single channel name or list of channel names.
     * @param callable     $cb       function(string $channel, string $message, Client $client): void
     */
    public function sSubscribe($channels, $cb)
    {
        $this->assertNoActiveStream('sSubscribe');
        $new_cb = function ($result) use ($cb) {
            if (!$result) {
                echo $this->error();
                return;
            }
            $response_type = $result[0];
            switch ($response_type) {
                case 'ssubscribe':
                    return;
                case 'smessage':
                    \call_user_func($cb, $result[1], $result[2], $this);
                    return;
                case 'unsubscribe':
                case 'punsubscribe':
                case 'sunsubscribe':
                    // Any unsubscribe-family ack clears the lock — see
                    // handleUnsubscribeAck() for why all three types are accepted.
                    $this->handleUnsubscribeAck($result);
                    return;
                default:
                    echo 'unknow response type for ssubscribe. buffer:' . serialize($result) . "\n";
            }
        };
        $this->_queue[] = [['SSUBSCRIBE', $channels], time(), $new_cb];
        $this->process();
    }

    /**
     * UNSUBSCRIBE — stop listening on zero or more channels.
     *
     * Pass no channels to drop every active subscription; pass specific
     * channel names to drop only those. An optional trailing callable fires
     * with (true, $client) once the connection has fully left subscribe mode
     * (zero remaining subscriptions). The callback signals "back to normal
     * command mode", not the teardown of a specific channel: on a partial
     * unsubscribe — dropping some of several channels — it is held until the
     * connection eventually leaves subscribe mode entirely. Callers that need
     * per-channel notification should track that in their subscribe callback.
     *
     * subscribe() locks the connection (process() refuses to send anything
     * while $this->_subscribe is true), so an unsubscribe routed through
     * __call() would sit in the queue forever. This method writes the frame
     * straight to the socket, bypassing that lock; handleUnsubscribeAck()
     * clears the lock when the server reports zero remaining subscriptions.
     *
     * @param  mixed ...$channelsAndCb channel names, optional trailing callable.
     * @return null
     */
    public function unsubscribe(...$channelsAndCb)
    {
        return $this->writeUnsubscribe('UNSUBSCRIBE', $channelsAndCb);
    }

    /**
     * PUNSUBSCRIBE — stop listening on zero or more patterns.
     *
     * Mirror of unsubscribe() for pSubscribe() pattern subscriptions. Pass no
     * patterns to drop them all; an optional trailing callable fires with
     * (true, $client) once the connection has fully left subscribe mode.
     *
     * @param  mixed ...$patternsAndCb pattern strings, optional trailing callable.
     * @return null
     */
    public function pUnsubscribe(...$patternsAndCb)
    {
        return $this->writeUnsubscribe('PUNSUBSCRIBE', $patternsAndCb);
    }

    /**
     * SUNSUBSCRIBE — stop listening on zero or more shard channels.
     *
     * Mirror of unsubscribe() for sSubscribe() shard subscriptions. Pass no
     * channels to drop them all; an optional trailing callable fires with
     * (true, $client) once the connection has fully left subscribe mode.
     *
     * @param  mixed ...$channelsAndCb shard channel names, optional trailing callable.
     * @return null
     */
    public function sUnsubscribe(...$channelsAndCb)
    {
        return $this->writeUnsubscribe('SUNSUBSCRIBE', $channelsAndCb);
    }

    /**
     * Write an UNSUBSCRIBE-family frame directly to the connection, bypassing
     * the queue/process() lock that subscribe() puts in place.
     *
     * A trailing callable in $args is popped and registered to fire once the
     * connection has fully unsubscribed (see handleUnsubscribeAck()). If the
     * client is not currently in subscribe mode there is nothing to tear down
     * — and a bare UNSUBSCRIBE would produce a reply onMessage() couldn't match
     * to any queued command — so we honour the callback contract and return
     * without touching the wire.
     *
     * @param  string $verb 'UNSUBSCRIBE' | 'PUNSUBSCRIBE' | 'SUNSUBSCRIBE'.
     * @param  array  $args channel/pattern names, optional trailing callable.
     * @return null
     */
    protected function writeUnsubscribe($verb, array $args)
    {
        $cb = null;
        if (!empty($args) && \is_callable(\end($args))) {
            $cb = \array_pop($args);
        }
        // A live connection is implied here: closeConnection() clears the
        // _subscribe flag and the socket together, so being in subscribe mode
        // guarantees _connection is set.
        if (!$this->_subscribe) {
            if ($cb !== null) {
                \call_user_func($cb, true, $this);
            }
            return null;
        }
        if ($cb !== null) {
            $this->_unsubscribeCallbacks[] = $cb;
        }
        $this->_connection->send(\array_merge([$verb], $args));
        return null;
    }

    /**
     * Handle an UNSUBSCRIBE / PUNSUBSCRIBE / SUNSUBSCRIBE acknowledgement frame.
     *
     * All three subscribe callbacks route every unsubscribe-family ack type
     * here, not just their matching one: a SUNSUBSCRIBE issued with no channel
     * argument is acked by Dragonfly as type 'unsubscribe' (not 'sunsubscribe'),
     * so keying off a single type would miss the unsubscribe-all teardown and
     * leave the connection locked forever.
     *
     * Redis sends one ack per channel, each carrying the running count of
     * remaining subscriptions ($result[2]). While that count is non-zero the
     * connection is still subscribed and we leave the lock in place. When it
     * reaches zero the connection has left subscribe mode, so we:
     *   - clear $this->_subscribe so process() can resume sending,
     *   - drop the now-stale SUBSCRIBE entry that has been pinned at the head
     *     of the queue (onMessage() never removes it while subscribed; without
     *     this the next process() would re-send SUBSCRIBE), and
     *   - fire any callbacks registered by the unsubscribe* methods.
     *
     * onMessage() calls process() immediately after this returns, which drains
     * whatever the caller queued while the connection was locked.
     *
     * @param  array $result [verb, channel|null, remaining_count].
     * @return void
     */
    protected function handleUnsubscribeAck($result)
    {
        // Default the count to 0 (trigger teardown) when the element is absent:
        // the safe failure mode on a malformed ack is to unlock rather than
        // stay locked forever.
        $remaining = isset($result[2]) ? (int)$result[2] : 0;
        if ($remaining > 0) {
            // Still subscribed to other channels — keep the lock and hold any
            // registered completion callbacks until the connection is fully
            // unsubscribed (see writeUnsubscribe()).
            return;
        }
        $this->_subscribe = false;
        \reset($this->_queue);
        $headKey = \key($this->_queue);
        if ($headKey !== null) {
            $verb = $this->_queue[$headKey][0][0] ?? '';
            if ($verb === 'SUBSCRIBE' || $verb === 'PSUBSCRIBE' || $verb === 'SSUBSCRIBE') {
                unset($this->_queue[$headKey]);
            }
        }
        if (!empty($this->_unsubscribeCallbacks)) {
            $callbacks = $this->_unsubscribeCallbacks;
            $this->_unsubscribeCallbacks = [];
            foreach ($callbacks as $callback) {
                \call_user_func($callback, true, $this);
            }
        }
    }

    /**
     * MONITOR — stream every command the server processes to $cb.
     *
     * Like subscribe(), MONITOR is long-lived and locks the connection: once it
     * is sent, process() stops dispatching queued commands and onMessage()
     * keeps this entry pinned at the head of the queue so its callback receives
     * every monitor line. It uses its own $_monitoring flag rather than
     * $_subscribe because there is no UNMONITOR — the only way to stop the
     * stream is to close() the client (which clears the flag).
     *
     * The server's initial reply is +OK, delivered here as boolean true by the
     * onMessage OK-normalisation; that handshake is swallowed. Every subsequent
     * reply is a raw monitor line passed verbatim to $cb, e.g.
     *   1700000000.123456 [0 127.0.0.1:6379] "set" "key" "value"
     *
     * DANGER: MONITOR streams ALL traffic the server handles and measurably
     * reduces its throughput — use it for debugging, never as a steady-state
     * listener, and keep a monitoring client off your hot-path connections.
     *
     * If the connection is already running a subscribe or monitor stream the
     * call is ignored (silently, like connect() when already connected) — you
     * cannot layer two streaming commands on one connection, and a queued
     * second MONITOR would otherwise fire when the first stream ends, re-locking
     * the connection with no way to recover.
     *
     * @param  callable $cb function(string $line, Client $client): void
     * @return void
     */
    public function monitor($cb)
    {
        // Silently ignore (documented contract) if a stream is already active —
        // or merely queued and not yet sent, which the bare flag check would
        // miss. See streamActiveOrPending().
        if ($this->streamActiveOrPending()) {
            return;
        }
        $new_cb = function ($result) use ($cb) {
            if ($result === true) {
                // The +OK handshake — monitoring has started. Swallow it.
                return;
            }
            if ($result === false) {
                // MONITOR was rejected (e.g. ACL). The server never entered
                // monitor mode, so release our lock and drop the pinned entry
                // so the client stays usable, then surface the failure. The
                // onMessage() that invoked this closure calls process() right
                // after it returns, which drains any commands the caller queued
                // (or had waiting) now that the lock is clear.
                $this->_monitoring = false;
                \reset($this->_queue);
                $headKey = \key($this->_queue);
                if ($headKey !== null && ($this->_queue[$headKey][0][0] ?? '') === 'MONITOR') {
                    unset($this->_queue[$headKey]);
                }
                \call_user_func($cb, false, $this);
                return;
            }
            \call_user_func($cb, $result, $this);
        };
        $this->_queue[] = [['MONITOR'], time(), $new_cb];
        $this->process();
    }

    /**
     * select
     *
     * @param int           $db
     * @param callable|null $cb
     * @return mixed
     */
    public function select($db, $cb = null)
    {
        $format = function ($result) use ($db) {
            // Only record the new DB once the server has confirmed the switch.
            // On a failed SELECT onMessage hands the formatter $result === false;
            // updating _db then would make the next reconnect re-issue a SELECT
            // to a DB the server never accepted.
            if ($result !== false) {
                $this->_db = $db;
            }
            return $result;
        };
        return $this->queueCommand(['SELECT', $db], $cb, $format);
    }

    /**
     * auth
     *
     * @param string|array  $auth
     * @param callable|null $cb
     * @return mixed
     */
    public function auth($auth, $cb = null)
    {
        $format = function ($result) use ($auth) {
            // Only remember the credential once the server has accepted it.
            // A failed AUTH delivers $result === false here; recording it would
            // make the next reconnect replay a credential the server rejected.
            if ($result !== false) {
                $this->_auth = $auth;
            }
            return $result;
        };
        $args = \is_array($auth) ? \array_merge(['AUTH'], $auth) : ['AUTH', $auth];
        return $this->queueCommand($args, $cb, $format);
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
        $args = \func_get_args();
        if ($cb !== null && !\is_callable($cb)) {
            $timeout = $cb;
            $cb = $args[3] ?? null;
            return $this->queueCommand(['SETEX', $key, $timeout, $value], $cb);
        }
        return $this->queueCommand(['SET', $key, $value], $cb);
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
        $args = \func_get_args();
        if ($cb !== null && !\is_callable($cb)) {
            $num = $cb;
            $cb = $args[2] ?? null;
            return $this->queueCommand(['INCRBY', $key, $num], $cb);
        }
        return $this->queueCommand(['INCR', $key], $cb);
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
        $args = \func_get_args();
        if ($cb !== null && !\is_callable($cb)) {
            $num = $cb;
            $cb = $args[2] ?? null;
            return $this->queueCommand(['DECRBY', $key, $num], $cb);
        }
        return $this->queueCommand(['DECR', $key], $cb);
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
            if (!\is_array($value)) {
                $args[] = $value;
                continue;
            }
            foreach ($value as $sub_value) {
                $args[] = $sub_value;
            }
        }
        \array_unshift($args, 'SORT', $key);
        return $this->queueCommand($args, $cb);
    }

    /**
     * SORT_RO — read-only variant of SORT.
     *
     * Same wire shape as sort() but the verb carries an underscore, which
     * __call()'s strtoupper() can't produce — it would send `SORTRO`. The
     * option-flattening loop mirrors sort() (each $op contributes its
     * literal name and either a scalar or a flat list of sub-values).
     *
     * Pass a callable as $options to shortcut into callback mode with the
     * default `[]` options — mirrors how flushDb() / hello() fold a
     * trailing-callback shortcut.
     *
     * SORT_RO is subject to the same numeric-vs-alpha gotcha as SORT: by
     * default the server tries to sort elements as numbers, so callers
     * with non-numeric values must include `['ALPHA' => '']` or similar
     * in $options. The empty-value convention matches sort() — flag-only
     * options are spelled as `'ALPHA' => ''` because the loop emits each
     * key followed by its value.
     *
     * @param  string         $key
     * @param  array|callable $options Flat associative array of sort options, or the callback.
     * @param  callable|null  $cb      function(array $reply, Client $client): void
     * @return mixed                   Coroutine mode: sorted list. Callback mode: null.
     */
    public function sortRo($key, $options = [], $cb = null)
    {
        if (\is_callable($options)) {
            $cb = $options;
            $options = [];
        }
        $args = ['SORT_RO', $key];
        foreach ($options as $op => $value) {
            $args[] = $op;
            if (\is_array($value)) {
                foreach ($value as $sub_value) {
                    $args[] = $sub_value;
                }
                continue;
            }
            $args[] = $value;
        }
        return $this->queueCommand($args, $cb);
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
        return $this->queueCommand($args, $cb);
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
            if (!\is_array($result)) {
                return $result;
            }
            return \array_combine($array, $result);
        };
        return $this->queueCommand(['HMGET', $key, $array], $cb, $format);
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
        return $this->queueCommand(['HGETALL', $key], $cb, $format);
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
        foreach ($array as $field => $value) {
            $args[] = $field;
            $args[] = $value;
        }
        return $this->queueCommand($args, $cb);
    }

    /**
     * XADD — append an entry to a stream.
     *
     * Explicit method because the RESP encoder flattens a nested array arg by
     * emitting its VALUES ONLY (foreach ($item as $str) in Protocols\Redis::
     * encode()). Passing an associative field=>value message through __call()
     * therefore drops the field names and the server rejects the command with
     * "wrong number of arguments". This method flattens the message itself —
     * each field and its value become separate wire tokens — so callers can
     * use the natural ['field' => 'value'] shape.
     *
     * $message must be a non-empty associative map of field => value. A bare
     * indexed list would be sent as field "0", value <first>, … which is not
     * what you want; keep field names explicit.
     *
     * Optional capping mirrors phpredis: pass $maxLen > 0 to add `MAXLEN n`,
     * and $approximate = true to make it `MAXLEN ~ n` (cheaper trimming). A
     * callable in the $maxLen or $approximate slot is taken as the callback,
     * so `$redis->xAdd($key, '*', $msg, $cb)` works without spelling out the
     * cap arguments — the same trailing-callback shortcut used by flushDb()
     * and bgSave().
     *
     * @param  string         $key
     * @param  string         $id          Entry ID, or '*' to let the server assign one.
     * @param  array          $message     Associative field => value map (non-empty).
     * @param  int|callable    $maxLen      Cap the stream length, or the callback.
     * @param  bool|callable   $approximate true for `MAXLEN ~`, or the callback.
     * @param  callable|null   $cb          function(string $id, Client $client): void
     * @return mixed                        Coroutine mode: the entry ID. Callback mode: null.
     */
    public function xAdd($key, $id, array $message, $maxLen = 0, $approximate = false, $cb = null)
    {
        if (\is_callable($maxLen)) {
            $cb = $maxLen;
            $maxLen = 0;
            $approximate = false;
        } elseif (\is_callable($approximate)) {
            $cb = $approximate;
            $approximate = false;
        }
        if (empty($message)) {
            throw new \InvalidArgumentException('xAdd requires a non-empty field => value message');
        }
        $args = ['XADD', $key];
        if ($maxLen > 0) {
            $args[] = 'MAXLEN';
            if ($approximate) {
                $args[] = '~';
            }
            $args[] = $maxLen;
        }
        $args[] = $id;
        foreach ($message as $field => $value) {
            $args[] = $field;
            $args[] = $value;
        }
        return $this->queueCommand($args, $cb);
    }

    /**
     * XREAD. $streams is stream => id. A plain list is sent as raw tokens so
     * XREAD COUNT n STREAMS key id still works.
     *
     * @param array          $streams
     * @param int|callable   $count
     * @param int|callable|null $block
     * @param callable|null  $cb
     * @return mixed
     */
    public function xRead($streams, $count = 0, $block = null, $cb = null)
    {
        if (\is_callable($count)) {
            $cb = $count;
            $count = 0;
            $block = null;
        } elseif (\is_callable($block)) {
            $cb = $block;
            $block = null;
        }
        return $this->queueCommand($this->streamReadArgs('XREAD', [], $streams, $count, $block), $cb);
    }

    /**
     * XREADGROUP. $streams is stream => id.
     *
     * @param string         $group
     * @param string         $consumer
     * @param array          $streams
     * @param int|callable   $count
     * @param int|callable|null $block
     * @param callable|null  $cb
     * @return mixed
     */
    public function xReadGroup($group, $consumer, $streams, $count = 0, $block = null, $cb = null)
    {
        if (\is_callable($count)) {
            $cb = $count;
            $count = 0;
            $block = null;
        } elseif (\is_callable($block)) {
            $cb = $block;
            $block = null;
        }
        return $this->queueCommand(
            $this->streamReadArgs('XREADGROUP', ['GROUP', $group, $consumer], $streams, $count, $block),
            $cb
        );
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
        if (\count($args) > 1 || \in_array($method, ['randomKey', 'multi', 'exec', 'discard'], true)) {
            if (\is_callable(\end($args))) {
                $cb = \array_pop($args);
            }
        }
        \array_unshift($args, \strtoupper($method));
        return $this->queueCommand($args, $cb);
    }

    /**
     * Escape hatch for sending any Redis command verbatim.
     *
     * Unlike __call(), rawCommand does NOT prepend the method name to the
     * wire payload. The args you pass ARE the wire payload — the first
     * non-callback arg is the command name and the rest are its arguments.
     * Use this for commands that don't yet have a dedicated wrapper:
     * newer Redis/Dragonfly verbs, custom modules, multi-word verbs you'd
     * rather not assemble through dispatcher(), etc.
     *
     * The last arg is treated as a callback if it is callable; the rest of
     * the args are queued literally via queueCommand(). At least one arg
     * (the command name) is required — calling rawCommand() with only a
     * callable, or with nothing at all, throws InvalidArgumentException
     * rather than sending an empty command to the server.
     *
     * Example:
     *     $redis->rawCommand('CONFIG', 'GET', 'maxmemory', function ($reply) {
     *         // $reply === ['maxmemory', '0']
     *     });
     *
     * @param  mixed ...$args  Wire command parts; optionally a trailing callable.
     * @return mixed           Coroutine mode: the reply. Callback mode: null.
     */
    public function rawCommand(...$args)
    {
        $cb = null;
        if (!empty($args) && \is_callable(\end($args))) {
            $cb = \array_pop($args);
        }
        if (empty($args)) {
            throw new \InvalidArgumentException('rawCommand requires at least the command name');
        }
        return $this->queueCommand($args, $cb);
    }

    /*
    |--------------------------------------------------------------------------
    | Underscore-bearing verbs (Bitmap / Geo / Scripting RO variants)
    |--------------------------------------------------------------------------
    |
    | __call() runs strtoupper() on the method name, which strips no characters
    | but also adds none — so 'bitFieldRo' becomes 'BITFIELDRO', not the
    | required 'BITFIELD_RO'. The server rejects the verb with "ERR unknown
    | command". These thin wrappers spell the underscore form directly on the
    | wire while keeping the camelCase method name advertised in the @method
    | declarations above.
    */

    /**
     * BITFIELD_RO — read-only variant of BITFIELD, operations limited to GET.
     *
     * Args after $key are forwarded verbatim. A trailing callable, if present,
     * is popped and treated as the callback — mirrors how info() / hello() /
     * flushDb() fold in a trailing-callback shortcut.
     *
     * @param  string        $key
     * @param  mixed         ...$args GET-op groups (e.g. 'GET', 'i5', 0) and an optional trailing callable.
     * @return array|null             See bitField() return semantics.
     */
    public function bitFieldRo($key, ...$args)
    {
        $cb = null;
        if (!empty($args) && \is_callable(\end($args))) {
            $cb = \array_pop($args);
        }
        return $this->queueCommand(\array_merge(['BITFIELD_RO', $key], $args), $cb);
    }

    /**
     * GEORADIUS_RO — read-only variant of GEORADIUS.
     *
     * $options is a flat array of additional wire tokens (WITHCOORD, WITHDIST,
     * COUNT n, ASC/DESC, etc.). A callable passed as $options is interpreted
     * as the callback for no-option calls.
     *
     * @param  string         $key
     * @param  float|string   $lng
     * @param  float|string   $lat
     * @param  float|int      $radius
     * @param  string         $unit    m | km | ft | mi
     * @param  array|callable $options Flat array of extra tokens, or the callback.
     * @param  callable|null  $cb      function($reply, Client $client): void
     * @return array|null              Coroutine mode: list of members (or richer rows with options). Callback mode: null.
     */
    public function geoRadiusRo($key, $lng, $lat, $radius, $unit, $options = [], $cb = null)
    {
        if (\is_callable($options)) {
            $cb = $options;
            $options = [];
        }
        return $this->queueCommand(\array_merge(['GEORADIUS_RO', $key, $lng, $lat, $radius, $unit], $options), $cb);
    }

    /**
     * GEORADIUSBYMEMBER_RO — read-only variant of GEORADIUSBYMEMBER.
     *
     * Same $options semantics as geoRadiusRo(): a flat array of extra wire
     * tokens, or a callable that is taken as the callback.
     *
     * @param  string         $key
     * @param  string         $member
     * @param  float|int      $radius
     * @param  string         $unit    m | km | ft | mi
     * @param  array|callable $options Flat array of extra tokens, or the callback.
     * @param  callable|null  $cb      function($reply, Client $client): void
     * @return array|null              Coroutine mode: list of members. Callback mode: null.
     */
    public function geoRadiusByMemberRo($key, $member, $radius, $unit, $options = [], $cb = null)
    {
        if (\is_callable($options)) {
            $cb = $options;
            $options = [];
        }
        return $this->queueCommand(\array_merge(['GEORADIUSBYMEMBER_RO', $key, $member, $radius, $unit], $options), $cb);
    }

    /**
     * EVAL_RO — execute a Lua script in read-only mode.
     *
     * Wire form: EVAL_RO script numkeys [arg ...]. $args is a flat array of
     * KEYS followed by ARGV (the first $numKeys elements are KEYS). A
     * callable passed in either positional slot is taken as the callback,
     * mirroring how info()/hello() fold trailing callables.
     *
     * @param  string                  $script
     * @param  array|callable          $args    Flat KEYS+ARGV array, or the callback.
     * @param  int|callable            $numKeys Number of KEYS prefixing $args, or the callback.
     * @param  callable|null           $cb      function($reply, Client $client): void
     * @return mixed
     */
    public function evalRo($script, $args = [], $numKeys = 0, $cb = null)
    {
        if (\is_callable($args)) {
            $cb = $args;
            $args = [];
            $numKeys = 0;
        }
        if (\is_callable($numKeys)) {
            $cb = $numKeys;
            $numKeys = \count($args);
        }
        $wire = ['EVAL_RO', $script, $numKeys];
        foreach ($args as $a) {
            $wire[] = $a;
        }
        return $this->queueCommand($wire, $cb);
    }

    /**
     * EVALSHA_RO — execute a cached Lua script (by SHA1) in read-only mode.
     *
     * Same signature semantics as evalRo(): $args is a flat KEYS+ARGV array,
     * and a callable in either positional slot is taken as the callback.
     *
     * @param  string                  $sha
     * @param  array|callable          $args    Flat KEYS+ARGV array, or the callback.
     * @param  int|callable            $numKeys Number of KEYS prefixing $args, or the callback.
     * @param  callable|null           $cb      function($reply, Client $client): void
     * @return mixed
     */
    public function evalShaRo($sha, $args = [], $numKeys = 0, $cb = null)
    {
        if (\is_callable($args)) {
            $cb = $args;
            $args = [];
            $numKeys = 0;
        }
        if (\is_callable($numKeys)) {
            $cb = $numKeys;
            $numKeys = \count($args);
        }
        $wire = ['EVALSHA_RO', $sha, $numKeys];
        foreach ($args as $a) {
            $wire[] = $a;
        }
        return $this->queueCommand($wire, $cb);
    }

    /**
     * @return array
     */
    protected function suspenstion()
    {
        $loop = \Workerman\Worker::$eventLoopClass;
        if ($loop === \Workerman\Events\Swoole::class || $loop === \Workerman\Events\Swow::class) {
            $box = new \stdClass();
            $coroutine = \Workerman\Coroutine::getCurrent();
            $cb = function ($result) use ($box, $coroutine) {
                $box->value = $result;
                $coroutine->resume();
            };
            return [$cb, static function () use ($box) {
                \Workerman\Coroutine::suspend();
                return $box->value;
            }];
        }
        $suspension = EventLoop::getSuspension();
        $cb = function ($result) use ($suspension) {
            $suspension->resume($result);
        };
        return [$cb, function () use ($suspension) {
            return $suspension->suspend();
        }];
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
        $this->_monitoring = false;
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
     * getHost — local accessor (phpredis-compat).
     *
     * Returns the host parsed out of the stored connection URL ($_address,
     * e.g. "redis://127.0.0.1:6379"). This is client state, NOT a server
     * round-trip — it never touches the connection or the command queue.
     *
     * @return string|null  The host, or null if $_address cannot be parsed.
     */
    public function getHost()
    {
        $host = \parse_url($this->_address, \PHP_URL_HOST);

        return $host === false ? null : $host;
    }

    /**
     * getPort — local accessor (phpredis-compat).
     *
     * Returns the port parsed out of the stored connection URL. Defaults to
     * 6379 when the URL carries no explicit port, matching the conventional
     * Redis port the address is built around. Purely local, no round-trip.
     *
     * @return int
     */
    public function getPort()
    {
        $port = \parse_url($this->_address, \PHP_URL_PORT);

        return $port === false || $port === null ? 6379 : (int)$port;
    }

    /**
     * getDbNum — local accessor (phpredis-compat).
     *
     * Returns the currently selected database index as tracked locally by
     * select()/connect(). No SELECT is sent; this reflects $_db only.
     *
     * @return int
     */
    public function getDbNum()
    {
        return (int)$this->_db;
    }

    /**
     * getAuth — local accessor (phpredis-compat).
     *
     * Returns the stored auth credential exactly as supplied to auth()/the
     * options: a string password, a [user, pass] array for ACL auth, or null
     * when no credential was set. Purely local, no AUTH round-trip.
     *
     * @return mixed
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * getTimeout — local accessor (phpredis-compat).
     *
     * Returns the configured connect timeout from the client options
     * ('connect_timeout'), or null if none was configured. Local only.
     *
     * @return mixed  The configured connect timeout, or null.
     */
    public function getTimeout()
    {
        return $this->_options['connect_timeout'] ?? null;
    }

    /**
     * getReadTimeout — local accessor (phpredis-compat).
     *
     * Returns the configured read/wait timeout from the client options
     * ('wait_timeout' — the key the wait-timeout scan reads), or null if not
     * configured. Local only, no round-trip.
     *
     * @return mixed  The configured wait timeout, or null.
     */
    public function getReadTimeout()
    {
        return $this->_options['wait_timeout'] ?? null;
    }

    /**
     * isConnected — local accessor (phpredis-compat).
     *
     * Reports whether this client currently holds an established connection.
     * $_connection is null until connect() runs and is reset to null on
     * teardown, so a non-null connection in the ESTABLISHED state means we
     * are connected.
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->_connection !== null
            && $this->_connection->getStatus(false) === 'ESTABLISHED';
    }

    /**
     * getLastError — local accessor (phpredis-compat).
     *
     * Returns the last stored error string, or null when there is none.
     * phpredis returns null (not an empty string) when no error has occurred,
     * so the internal '' sentinel is normalised to null here. Local only.
     *
     * @return string|null
     */
    public function getLastError()
    {
        return $this->_error === '' ? null : $this->_error;
    }

    /**
     * clearLastError — local accessor (phpredis-compat).
     *
     * Clears the stored last-error string and returns true, mirroring
     * phpredis. Local only, no round-trip.
     *
     * @return bool
     */
    public function clearLastError()
    {
        $this->_error = '';

        return true;
    }

    /**
     * getPersistentID — local accessor (phpredis-compat).
     *
     * This async client never opens persistent connections (every connection
     * is a fresh AsyncTcpConnection torn down on close()), so there is no
     * persistent-connection identifier to report. Always null. Local only.
     *
     * @return null
     */
    public function getPersistentID()
    {
        return null;
    }

    /**
     * getMultiple — phpredis MGET alias.
     *
     * Unlike the other accessors here, this IS a server command: phpredis
     * exposes getMultiple() as an alias for MGET, so it delegates to MGET via
     * the normal command queue (works in both callback and coroutine modes).
     * Returns the values for $keys in order, with null for any missing key.
     *
     * @param  array  $keys
     * @param  callable|null  $cb
     * @return mixed
     */
    public function getMultiple(array $keys, $cb = null)
    {
        return $this->queueCommand(\array_merge(['MGET'], $keys), $cb);
    }

    /**
     * close
     */
    public function close()
    {
        $this->closeConnection();
        // Tear down the periodic wait-timeout scan installed by the
        // constructor; without this the timer keeps firing and its closure
        // keeps $this alive, defeating the gc_collect_cycles() below.
        if ($this->_waitTimeoutTimer) {
            Timer::del($this->_waitTimeoutTimer);
            $this->_waitTimeoutTimer = null;
        }
        $this->_queue = [];
        gc_collect_cycles();
        if (function_exists('gc_mem_caches')) {
            gc_mem_caches();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | No-arg server / connection commands
    |--------------------------------------------------------------------------
    |
    | Explicit methods for commands whose only wire payload is the verb itself
    | (or the verb plus a single optional flag). __call() only pops a trailing
    | callable when count($args) > 1 OR the method is one of a small allowlist,
    | so calling these as $redis->ping($cb) would otherwise put the closure on
    | the wire as a command argument. Funnelling each through queueCommand()
    | bypasses __call() and gives PHPStan a real signature to lock onto.
    */

    /**
     * PING — server health check. Reply is the literal string 'PONG'.
     *
     * @param  callable|null $cb function($reply, Client $client): void
     * @return mixed             Coroutine mode: 'PONG'. Callback mode: null.
     */
    public function ping($cb = null)
    {
        return $this->queueCommand(['PING'], $cb);
    }

    /**
     * QUIT — ask the server to close the connection.
     *
     * Sets the internal $_quitting flag so the onClose handler suppresses
     * the usual 5-second reconnect timer. Once QUIT's +OK reply has been
     * delivered to the callback, the socket is closed by the server and
     * the client stays closed — call connect() again only if you need to
     * resume work on the same instance.
     *
     * @param  callable|null $cb function(string|bool $reply, Client $client): void
     * @return mixed             Coroutine mode: true on +OK. Callback mode: null.
     */
    public function quit($cb = null)
    {
        $this->_quitting = true;
        return $this->queueCommand(['QUIT'], $cb);
    }

    /**
     * INFO — server stats and metadata as a single bulk string.
     *
     * Optional $section narrows the report (e.g. 'server', 'memory',
     * 'clients'). If $section is callable it is treated as the callback
     * and no section filter is sent — this lets `$redis->info($cb)` work
     * naturally without the caller spelling out a null section first.
     *
     * @param  string|callable|null $section Section name, or callback if no filter.
     * @param  callable|null        $cb      function($reply, Client $client): void
     * @return mixed                         Coroutine mode: the INFO bulk string. Callback mode: null.
     */
    public function info($section = null, $cb = null)
    {
        if (\is_callable($section)) {
            $cb = $section;
            $section = null;
        }
        return $this->queueCommand($section === null ? ['INFO'] : ['INFO', $section], $cb);
    }

    /**
     * DBSIZE — number of keys in the currently selected DB.
     *
     * @param  callable|null $cb function($reply, Client $client): void
     * @return mixed             Coroutine mode: integer count. Callback mode: null.
     */
    public function dbSize($cb = null)
    {
        return $this->queueCommand(['DBSIZE'], $cb);
    }

    /**
     * TIME — server-side wall clock as a two-element array
     * [unix_seconds, microseconds]. Both elements are returned as numeric
     * strings (Redis bulk replies).
     *
     * @param  callable|null $cb function($reply, Client $client): void
     * @return mixed             Coroutine mode: [seconds, microseconds]. Callback mode: null.
     */
    public function time($cb = null)
    {
        return $this->queueCommand(['TIME'], $cb);
    }

    /**
     * FLUSHDB — remove every key from the currently selected DB.
     *
     * Pass $async = true to send `FLUSHDB ASYNC` for a non-blocking flush
     * (the server reclaims memory in a background thread). If $async is
     * callable it is treated as the callback and a synchronous flush is
     * sent — mirrors how info() folds in a trailing-callback shortcut.
     *
     * @param  bool|callable $async true for ASYNC, or the callback.
     * @param  callable|null $cb    function($reply, Client $client): void
     * @return mixed                Coroutine mode: true on OK. Callback mode: null.
     */
    public function flushDb($async = false, $cb = null)
    {
        if (\is_callable($async)) {
            $cb = $async;
            $async = false;
        }
        return $this->queueCommand($async ? ['FLUSHDB', 'ASYNC'] : ['FLUSHDB'], $cb);
    }

    /**
     * FLUSHALL — remove every key from every DB.
     *
     * Same $async semantics as flushDb(): pass true for `FLUSHALL ASYNC`,
     * or pass a callable directly to shortcut into callback mode with a
     * synchronous flush.
     *
     * @param  bool|callable $async true for ASYNC, or the callback.
     * @param  callable|null $cb    function($reply, Client $client): void
     * @return mixed                Coroutine mode: true on OK. Callback mode: null.
     */
    public function flushAll($async = false, $cb = null)
    {
        if (\is_callable($async)) {
            $cb = $async;
            $async = false;
        }
        return $this->queueCommand($async ? ['FLUSHALL', 'ASYNC'] : ['FLUSHALL'], $cb);
    }

    /**
     * RESP version negotiation / server handshake.
     *
     * Like info(), the first positional arg accepts the callback directly so
     * `$redis->hello($cb)` works without a $protover argument. Otherwise
     * `$redis->hello(2, $cb)` upgrades to RESP3 and `$redis->hello(2, ['AUTH',
     * 'user', 'pass', 'SETNAME', 'client-name'], $cb)` includes the full
     * sub-command grammar — pass the extra args as a flat array which the
     * RESP encoder flattens onto the wire.
     *
     * Without an explicit method, calls like hello($cb) (count($args) == 1)
     * fall into __call() which doesn't extract the trailing callable and
     * sends the closure as a HELLO argument — same no-arg-callback bug as
     * PING / INFO / DBSIZE etc.
     *
     * @param int|string|callable|null $protover RESP protocol version (2 or 3), or callable for the callback.
     * @param array|callable|null      $extra    Additional sub-args (AUTH/SETNAME), or callable for the callback.
     * @param callable|null            $cb       function(array $reply, Client $client): void
     * @return array|null
     */
    public function hello($protover = null, $extra = null, $cb = null)
    {
        if (\is_callable($protover)) {
            $cb = $protover;
            $protover = null;
            $extra = null;
        } elseif (\is_callable($extra)) {
            $cb = $extra;
            $extra = null;
        }
        $args = ['HELLO'];
        if ($protover !== null) {
            $args[] = $protover;
        }
        if (\is_array($extra)) {
            $args[] = $extra;
        }
        return $this->queueCommand($args, $cb);
    }

    /*
    |--------------------------------------------------------------------------
    | Server administration — multi-verb dispatchers
    |--------------------------------------------------------------------------
    |
    | CONFIG / ACL / SLOWLOG / MEMORY / COMMAND / CLUSTER all share the same
    | wire shape: a fixed family verb followed by a subcommand verb and that
    | subcommand's arguments. Each thin wrapper forwards to dispatcher() with
    | the space-suffixed family prefix; the dispatcher pops a trailing
    | callable, uppercases the next arg as the verb, and queues the result.
    |
    | Calling these with no verb (e.g. $redis->command($cb)) sends the bare
    | family command — useful for COMMAND (returns the full command table).
    | command() special-cases that path because dispatcher()'s array_shift
    | would otherwise produce an empty verb token on the wire.
    */

    /**
     * CONFIG — server configuration subcommand family.
     *
     * Wire form: `CONFIG <verb> [args...]`. Typical verbs are GET, SET,
     * RESETSTAT, REWRITE. A trailing callable is taken as the callback.
     *
     * @param  mixed ...$args [verb, ...args, optional callable]
     * @return mixed
     */
    public function config(...$args)
    {
        return $this->dispatcher('CONFIG ', $args);
    }

    /**
     * ACL — access control list subcommand family.
     *
     * Wire form: `ACL <verb> [args...]`. Typical verbs are WHOAMI, LIST,
     * GETUSER, SETUSER, CAT, USERS, LOG. A trailing callable is taken as
     * the callback.
     *
     * @param  mixed ...$args [verb, ...args, optional callable]
     * @return mixed
     */
    public function acl(...$args)
    {
        return $this->dispatcher('ACL ', $args);
    }

    /**
     * SLOWLOG — slow-command log subcommand family.
     *
     * Wire form: `SLOWLOG <verb> [args...]`. Typical verbs are GET, LEN,
     * RESET, HELP. A trailing callable is taken as the callback.
     *
     * @param  mixed ...$args [verb, ...args, optional callable]
     * @return mixed
     */
    public function slowLog(...$args)
    {
        return $this->dispatcher('SLOWLOG ', $args);
    }

    /**
     * MEMORY — memory introspection subcommand family.
     *
     * Wire form: `MEMORY <verb> [args...]`. Typical verbs are USAGE,
     * STATS, DOCTOR, MALLOC-STATS, PURGE. A trailing callable is taken
     * as the callback.
     *
     * @param  mixed ...$args [verb, ...args, optional callable]
     * @return mixed
     */
    public function memory(...$args)
    {
        return $this->dispatcher('MEMORY ', $args);
    }

    /**
     * COMMAND — command-table introspection family.
     *
     * Wire form: `COMMAND [<verb> [args...]]`. Calling with only a
     * callback (or with no args at all) sends the bare `COMMAND` form
     * which returns the full command table — dispatcher()'s verb-shift
     * would otherwise leave an empty token on the wire, so this method
     * special-cases the no-verb path.
     *
     * @param  mixed ...$args [optional verb, ...args, optional callable]
     * @return mixed
     */
    public function command(...$args)
    {
        $cb = null;
        if (!empty($args) && \is_callable(\end($args))) {
            $cb = \array_pop($args);
        }
        if (empty($args)) {
            return $this->queueCommand(['COMMAND'], $cb);
        }
        // Re-attach the callback (if any) so dispatcher() can pop it back off.
        if ($cb !== null) {
            $args[] = $cb;
        }
        return $this->dispatcher('COMMAND ', $args);
    }

    /**
     * CLUSTER — cluster bus / topology subcommand family.
     *
     * Wire form: `CLUSTER <verb> [args...]`. Typical verbs are INFO,
     * NODES, MYID, SLOTS, SHARDS, COUNT-FAILURE-REPORTS, RESET. A
     * trailing callable is taken as the callback.
     *
     * @param  mixed ...$args [verb, ...args, optional callable]
     * @return mixed
     */
    public function cluster(...$args)
    {
        return $this->dispatcher('CLUSTER ', $args);
    }

    /*
    |--------------------------------------------------------------------------
    | JSON module (RedisJSON-compatible — supported by Dragonfly)
    |--------------------------------------------------------------------------
    |
    | Dragonfly natively implements the RedisJSON command set with a `JSON.`
    | prefix. The dispatcher pattern matches the dotted module form: the
    | trailing dot on the prefix tells dispatcher() to glue the verb onto
    | the prefix as a single Redis token (e.g. `JSON.SET`), as opposed to
    | the space-separated subcommand form used by CONFIG/ACL/etc.
    |
    | The json(...$args) dispatcher accepts an arbitrary verb. The shortcut
    | wrappers (jsonSet, jsonGet, …) bake in the verb so callers get IDE
    | autocomplete and don't have to remember the magic verb string.
    |
    | JSON values are passed as JSON-encoded strings on the wire; the server
    | echoes them back the same way. The format-callback layer does not
    | decode them — callers should json_decode($reply, true) where they
    | need a PHP array.
    */

    /**
     * JSON.* — module dispatcher.
     *
     * Wire form: `JSON.<verb> [args...]`. The first positional arg is the
     * verb (uppercased here and glued to the `JSON.` prefix); a trailing
     * callable is taken as the callback.
     *
     * A trailing null is treated as "no callback" — this lets the shortcut
     * wrappers (jsonSet, jsonGet, …) forward their `$cb = null` default
     * uniformly without having to special-case the null path themselves.
     *
     * @param  mixed ...$args [verb, ...args, optional callable or trailing null]
     * @return mixed
     */
    public function json(...$args)
    {
        if (!empty($args) && \end($args) === null) {
            \array_pop($args);
        }
        return $this->dispatcher('JSON.', $args);
    }

    // ---- Setters -----------------------------------------------------------

    /**
     * JSON.SET — set the JSON value at $path in $key.
     *
     * @param string        $key
     * @param string        $path   JSONPath, typically `$` for the root.
     * @param string        $value  JSON-encoded string.
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonSet($key, $path, $value, $cb = null)
    {
        return $this->json('SET', $key, $path, $value, $cb);
    }

    /**
     * JSON.MSET — set multiple key/path/value triples atomically.
     *
     * @param array         $tuples [[key, path, value], ...]
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonMSet(array $tuples, $cb = null)
    {
        $args = ['MSET'];
        foreach ($tuples as $t) {
            $args[] = $t[0];
            $args[] = $t[1];
            $args[] = $t[2];
        }
        $args[] = $cb;
        return $this->json(...$args);
    }

    /**
     * JSON.MERGE — merge $value into the document at $path (RFC 7396).
     *
     * @param string        $key
     * @param string        $path
     * @param string        $value  JSON-encoded string.
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonMerge($key, $path, $value, $cb = null)
    {
        return $this->json('MERGE', $key, $path, $value, $cb);
    }

    // ---- Getters -----------------------------------------------------------

    /**
     * JSON.GET — fetch the JSON value at zero or more paths.
     *
     * Wire form: `JSON.GET key [path ...]`. With no paths the entire
     * document is returned. With one path the matching slice is returned
     * (wrapped in an array per JSONPath semantics). With multiple paths
     * the server returns a JSON object keyed by path, e.g.
     * `{"$.a":[1],"$.b":["hi"]}`.
     *
     * A trailing callable in $pathsAndCb is treated as the callback.
     *
     * @param  string $key
     * @param  mixed  ...$pathsAndCb [path ...] with optional trailing callable.
     * @return mixed
     */
    public function jsonGet($key, ...$pathsAndCb)
    {
        $cb = null;
        if (!empty($pathsAndCb) && \is_callable(\end($pathsAndCb))) {
            $cb = \array_pop($pathsAndCb);
        }
        $args = \array_merge(['GET', $key], $pathsAndCb, [$cb]);
        return $this->json(...$args);
    }

    /**
     * JSON.MGET — fetch the value at $path across multiple keys.
     *
     * @param array         $keys
     * @param string        $path
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonMGet(array $keys, $path = '$', $cb = null)
    {
        if (\is_callable($path)) {
            $cb = $path;
            $path = '$';
        }
        $args = \array_merge(['MGET'], $keys, [$path, $cb]);
        return $this->json(...$args);
    }

    /**
     * JSON.TYPE — JSON type of the value at $path.
     *
     * @param string        $key
     * @param string        $path
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonType($key, $path = '$', $cb = null)
    {
        if (\is_callable($path)) {
            $cb = $path;
            $path = '$';
        }
        return $this->json('TYPE', $key, $path, $cb);
    }

    /**
     * JSON.OBJKEYS — keys of the JSON object at $path.
     *
     * @param string        $key
     * @param string        $path
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonObjKeys($key, $path = '$', $cb = null)
    {
        if (\is_callable($path)) {
            $cb = $path;
            $path = '$';
        }
        return $this->json('OBJKEYS', $key, $path, $cb);
    }

    /**
     * JSON.OBJLEN — number of keys in the JSON object at $path.
     *
     * @param string        $key
     * @param string        $path
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonObjLen($key, $path = '$', $cb = null)
    {
        if (\is_callable($path)) {
            $cb = $path;
            $path = '$';
        }
        return $this->json('OBJLEN', $key, $path, $cb);
    }

    /**
     * JSON.ARRLEN — length of the JSON array at $path.
     *
     * @param string        $key
     * @param string        $path
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonArrLen($key, $path = '$', $cb = null)
    {
        if (\is_callable($path)) {
            $cb = $path;
            $path = '$';
        }
        return $this->json('ARRLEN', $key, $path, $cb);
    }

    /**
     * JSON.STRLEN — length of the JSON string at $path.
     *
     * @param string        $key
     * @param string        $path
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonStrLen($key, $path = '$', $cb = null)
    {
        if (\is_callable($path)) {
            $cb = $path;
            $path = '$';
        }
        return $this->json('STRLEN', $key, $path, $cb);
    }

    // ---- Modifiers ---------------------------------------------------------

    /**
     * JSON.DEL — remove the value at $path.
     *
     * @param string        $key
     * @param string        $path
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonDel($key, $path = '$', $cb = null)
    {
        if (\is_callable($path)) {
            $cb = $path;
            $path = '$';
        }
        return $this->json('DEL', $key, $path, $cb);
    }

    /**
     * JSON.FORGET — alias of JSON.DEL.
     *
     * @param string        $key
     * @param string        $path
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonForget($key, $path = '$', $cb = null)
    {
        if (\is_callable($path)) {
            $cb = $path;
            $path = '$';
        }
        return $this->json('FORGET', $key, $path, $cb);
    }

    /**
     * JSON.ARRAPPEND — append one or more JSON-encoded values to the array at $path.
     *
     * @param  string $key
     * @param  string $path
     * @param  mixed  ...$valuesAndCb JSON-encoded values, with optional trailing callable.
     * @return mixed
     */
    public function jsonArrAppend($key, $path, ...$valuesAndCb)
    {
        $cb = null;
        if (!empty($valuesAndCb) && \is_callable(\end($valuesAndCb))) {
            $cb = \array_pop($valuesAndCb);
        }
        $args = \array_merge(['ARRAPPEND', $key, $path], $valuesAndCb, [$cb]);
        return $this->json(...$args);
    }

    /**
     * JSON.NUMINCRBY — increment the number at $path by $by.
     *
     * @param string         $key
     * @param string         $path
     * @param int|float      $by
     * @param callable|null  $cb
     * @return mixed
     */
    public function jsonNumIncrBy($key, $path, $by, $cb = null)
    {
        return $this->json('NUMINCRBY', $key, $path, $by, $cb);
    }

    /**
     * JSON.STRAPPEND — append a JSON-encoded string to the string at $path.
     *
     * The value must be a JSON-encoded string literal (e.g. `'"!"'`), not
     * a bare PHP string — that's the RedisJSON convention.
     *
     * @param string        $key
     * @param string        $path
     * @param string        $value  JSON-encoded string literal.
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonStrAppend($key, $path, $value, $cb = null)
    {
        return $this->json('STRAPPEND', $key, $path, $value, $cb);
    }

    /**
     * JSON.TOGGLE — flip the boolean at $path.
     *
     * @param string        $key
     * @param string        $path
     * @param callable|null $cb
     * @return mixed
     */
    public function jsonToggle($key, $path, $cb = null)
    {
        return $this->json('TOGGLE', $key, $path, $cb);
    }

    /*
    |--------------------------------------------------------------------------
    | Bloom Filter module (RedisBloom-compatible — supported by Dragonfly)
    |--------------------------------------------------------------------------
    |
    | Dragonfly natively implements RedisBloom's probabilistic-data-structure
    | command set with the `BF.` prefix. Same dotted-module dispatch pattern
    | as JSON.*: the dispatcher glues the verb onto `BF.` to form a single
    | Redis token (e.g. `BF.RESERVE`).
    |
    | The bf(...$args) dispatcher accepts an arbitrary verb so callers can
    | reach less-common commands (BF.INFO, BF.INSERT, …) without waiting for
    | a typed shortcut. The shortcuts (bfReserve, bfAdd, …) bake in the verb
    | for IDE autocomplete and clearer error messages.
    */

    /**
     * BF.* — module dispatcher.
     *
     * Wire form: `BF.<verb> [args...]`. The first positional arg is the verb
     * (uppercased and glued to the `BF.` prefix); a trailing callable is
     * taken as the callback. A trailing null is treated as "no callback" so
     * the typed shortcuts can forward their `$cb = null` defaults uniformly.
     *
     * @param  mixed ...$args [verb, ...args, optional callable or trailing null]
     * @return mixed
     */
    public function bf(...$args)
    {
        if (!empty($args) && \end($args) === null) {
            \array_pop($args);
        }
        return $this->dispatcher('BF.', $args);
    }

    /**
     * BF.RESERVE — create a new Bloom filter with a target false-positive
     * rate and initial capacity. Returns +OK on success.
     *
     * @param  string        $key
     * @param  float         $errorRate   e.g. 0.01 for 1 %.
     * @param  int           $capacity    Expected number of items.
     * @param  callable|null $cb
     * @return mixed
     */
    public function bfReserve($key, $errorRate, $capacity, $cb = null)
    {
        return $this->bf('RESERVE', $key, $errorRate, $capacity, $cb);
    }

    /**
     * BF.ADD — add one item to the filter. Reply is 1 if the item was newly
     * added, 0 if it was already (probably) present.
     *
     * @param  string        $key
     * @param  string        $item
     * @param  callable|null $cb
     * @return mixed
     */
    public function bfAdd($key, $item, $cb = null)
    {
        return $this->bf('ADD', $key, $item, $cb);
    }

    /**
     * BF.EXISTS — test for membership. Reply is 1 if the item is probably
     * present, 0 if it is definitely absent.
     *
     * @param  string        $key
     * @param  string        $item
     * @param  callable|null $cb
     * @return mixed
     */
    public function bfExists($key, $item, $cb = null)
    {
        return $this->bf('EXISTS', $key, $item, $cb);
    }

    /**
     * BF.MADD — add multiple items at once. Reply is an array of 0/1 per
     * item, aligned with the input order.
     *
     * @param  string $key
     * @param  mixed  ...$itemsAndCb items..., optional trailing callable.
     * @return mixed
     */
    public function bfMAdd($key, ...$itemsAndCb)
    {
        $cb = null;
        if (!empty($itemsAndCb) && \is_callable(\end($itemsAndCb))) {
            $cb = \array_pop($itemsAndCb);
        }
        $args = \array_merge(['MADD', $key], $itemsAndCb, [$cb]);
        return $this->bf(...$args);
    }

    /**
     * BF.MEXISTS — test multiple items at once. Reply is an array of 0/1
     * per item, aligned with the input order.
     *
     * @param  string $key
     * @param  mixed  ...$itemsAndCb items..., optional trailing callable.
     * @return mixed
     */
    public function bfMExists($key, ...$itemsAndCb)
    {
        $cb = null;
        if (!empty($itemsAndCb) && \is_callable(\end($itemsAndCb))) {
            $cb = \array_pop($itemsAndCb);
        }
        $args = \array_merge(['MEXISTS', $key], $itemsAndCb, [$cb]);
        return $this->bf(...$args);
    }

    /*
    |--------------------------------------------------------------------------
    | Count-Min Sketch module (RedisBloom-compatible — supported by Dragonfly)
    |--------------------------------------------------------------------------
    |
    | CMS commands count item occurrences with sub-linear memory. Same dotted
    | dispatch pattern as BF.* / JSON.*; the cms(...$args) dispatcher accepts
    | arbitrary verbs, the shortcuts cover the typical surface.
    */

    /**
     * CMS.* — module dispatcher.
     *
     * Wire form: `CMS.<verb> [args...]`. The first positional arg is the
     * verb (uppercased and glued to the `CMS.` prefix); a trailing callable
     * is taken as the callback. A trailing null is treated as "no callback"
     * so the typed shortcuts can forward their `$cb = null` defaults
     * uniformly.
     *
     * @param  mixed ...$args [verb, ...args, optional callable or trailing null]
     * @return mixed
     */
    public function cms(...$args)
    {
        if (!empty($args) && \end($args) === null) {
            \array_pop($args);
        }
        return $this->dispatcher('CMS.', $args);
    }

    /**
     * CMS.INITBYDIM — create a sketch with explicit width / depth. Returns
     * +OK on success.
     *
     * @param  string        $key
     * @param  int           $width   Number of counters per row.
     * @param  int           $depth   Number of rows (independent hashes).
     * @param  callable|null $cb
     * @return mixed
     */
    public function cmsInitByDim($key, $width, $depth, $cb = null)
    {
        return $this->cms('INITBYDIM', $key, $width, $depth, $cb);
    }

    /**
     * CMS.INITBYPROB — create a sketch sized for a target error rate and
     * probability of being within that bound. Returns +OK on success.
     *
     * @param  string        $key
     * @param  float         $error        Tolerated overestimation (e.g. 0.001).
     * @param  float         $probability  Probability of staying within $error (e.g. 0.01).
     * @param  callable|null $cb
     * @return mixed
     */
    public function cmsInitByProb($key, $error, $probability, $cb = null)
    {
        return $this->cms('INITBYPROB', $key, $error, $probability, $cb);
    }

    /**
     * CMS.INCRBY — increment one or more items by their associated counts.
     *
     * Variadic shape: item1, count1, item2, count2, …, optional trailing
     * callable. Reply is an array of the new estimated counts, aligned with
     * the input order.
     *
     * @param  string $key
     * @param  mixed  ...$pairsAndCb item/count pairs, optional trailing callable.
     * @return mixed
     */
    public function cmsIncrBy($key, ...$pairsAndCb)
    {
        $cb = null;
        if (!empty($pairsAndCb) && \is_callable(\end($pairsAndCb))) {
            $cb = \array_pop($pairsAndCb);
        }
        $args = \array_merge(['INCRBY', $key], $pairsAndCb, [$cb]);
        return $this->cms(...$args);
    }

    /**
     * CMS.QUERY — get the estimated counts for one or more items. Reply is
     * an array of integers aligned with the input order.
     *
     * @param  string $key
     * @param  mixed  ...$itemsAndCb items..., optional trailing callable.
     * @return mixed
     */
    public function cmsQuery($key, ...$itemsAndCb)
    {
        $cb = null;
        if (!empty($itemsAndCb) && \is_callable(\end($itemsAndCb))) {
            $cb = \array_pop($itemsAndCb);
        }
        $args = \array_merge(['QUERY', $key], $itemsAndCb, [$cb]);
        return $this->cms(...$args);
    }

    /**
     * CMS.MERGE — merge $numKeys source sketches into $dest, optionally
     * scaling each by its corresponding weight. All sources and the dest
     * must share the same width / depth.
     *
     * @param  string        $dest
     * @param  int           $numKeys
     * @param  array         $sources List of source sketch keys.
     * @param  array|null    $weights Optional aligned weight list (same length as $sources).
     * @param  callable|null $cb
     * @return mixed
     */
    public function cmsMerge($dest, $numKeys, array $sources, ?array $weights = null, $cb = null)
    {
        if (\is_callable($weights)) {
            $cb = $weights;
            $weights = null;
        }
        $args = ['MERGE', $dest, $numKeys];
        foreach ($sources as $s) {
            $args[] = $s;
        }
        if ($weights !== null) {
            $args[] = 'WEIGHTS';
            foreach ($weights as $w) {
                $args[] = $w;
            }
        }
        $args[] = $cb;
        return $this->cms(...$args);
    }

    /**
     * CMS.INFO — return sketch metadata as a flat array of
     * [name, value, name, value, …]: width, depth, count.
     *
     * @param  string        $key
     * @param  callable|null $cb
     * @return mixed
     */
    public function cmsInfo($key, $cb = null)
    {
        return $this->cms('INFO', $key, $cb);
    }

    /*
    |--------------------------------------------------------------------------
    | TopK module (RedisBloom-compatible — supported by Dragonfly)
    |--------------------------------------------------------------------------
    |
    | TopK approximates the K most frequent items in a stream. Same dotted
    | dispatch pattern as BF.* / CMS.* / JSON.*.
    |
    | Quirks worth noting (verified against Dragonfly):
    |   - TOPK.ADD returns an array whose elements are either bulk strings
    |     (the displaced item, when the new item bumped someone out of the
    |     top-K) or nil/empty when no displacement happened. The Redis client
    |     surface returns these as a flat array; nil elements come through as
    |     null entries.
    |   - TOPK.QUERY returns 1 / 0 per item (in / out of the top-K).
    |   - TOPK.COUNT returns estimated counts per item.
    |   - TOPK.LIST returns the current top-K members as a bulk-string array.
    */

    /**
     * TOPK.* — module dispatcher.
     *
     * Wire form: `TOPK.<verb> [args...]`. The first positional arg is the
     * verb (uppercased and glued to the `TOPK.` prefix); a trailing callable
     * is taken as the callback. A trailing null is treated as "no callback"
     * so the typed shortcuts can forward their `$cb = null` defaults
     * uniformly.
     *
     * @param  mixed ...$args [verb, ...args, optional callable or trailing null]
     * @return mixed
     */
    public function topk(...$args)
    {
        if (!empty($args) && \end($args) === null) {
            \array_pop($args);
        }
        return $this->dispatcher('TOPK.', $args);
    }

    /**
     * TOPK.RESERVE — create a new TopK sketch. Width / depth / decay use the
     * RedisBloom defaults (8 / 7 / 0.9). Returns +OK on success.
     *
     * @param  string        $key
     * @param  int           $topk    K — number of tracked items.
     * @param  int           $width
     * @param  int           $depth
     * @param  float         $decay
     * @param  callable|null $cb
     * @return mixed
     */
    public function topkReserve($key, $topk, $width = 8, $depth = 7, $decay = 0.9, $cb = null)
    {
        return $this->topk('RESERVE', $key, $topk, $width, $depth, $decay, $cb);
    }

    /**
     * TOPK.ADD — add one or more items. Reply is an array aligned with the
     * input: each slot is either the displaced item (string) or null when no
     * eviction happened.
     *
     * @param  string $key
     * @param  mixed  ...$itemsAndCb items..., optional trailing callable.
     * @return mixed
     */
    public function topkAdd($key, ...$itemsAndCb)
    {
        $cb = null;
        if (!empty($itemsAndCb) && \is_callable(\end($itemsAndCb))) {
            $cb = \array_pop($itemsAndCb);
        }
        $args = \array_merge(['ADD', $key], $itemsAndCb, [$cb]);
        return $this->topk(...$args);
    }

    /**
     * TOPK.INCRBY — increment one or more items by their associated counts.
     *
     * Variadic shape: item1, count1, item2, count2, …, optional trailing
     * callable. Reply mirrors topkAdd(): array of displaced items / null.
     *
     * @param  string $key
     * @param  mixed  ...$pairsAndCb item/count pairs, optional trailing callable.
     * @return mixed
     */
    public function topkIncrBy($key, ...$pairsAndCb)
    {
        $cb = null;
        if (!empty($pairsAndCb) && \is_callable(\end($pairsAndCb))) {
            $cb = \array_pop($pairsAndCb);
        }
        $args = \array_merge(['INCRBY', $key], $pairsAndCb, [$cb]);
        return $this->topk(...$args);
    }

    /**
     * TOPK.QUERY — test for membership in the current top-K. Reply is an
     * array of 0/1 per item, aligned with the input order.
     *
     * @param  string $key
     * @param  mixed  ...$itemsAndCb items..., optional trailing callable.
     * @return mixed
     */
    public function topkQuery($key, ...$itemsAndCb)
    {
        $cb = null;
        if (!empty($itemsAndCb) && \is_callable(\end($itemsAndCb))) {
            $cb = \array_pop($itemsAndCb);
        }
        $args = \array_merge(['QUERY', $key], $itemsAndCb, [$cb]);
        return $this->topk(...$args);
    }

    /**
     * TOPK.COUNT — return estimated counts for the given items. Items not
     * in the top-K may be reported as 0. Reply is an array of integers
     * aligned with the input order.
     *
     * @param  string $key
     * @param  mixed  ...$itemsAndCb items..., optional trailing callable.
     * @return mixed
     */
    public function topkCount($key, ...$itemsAndCb)
    {
        $cb = null;
        if (!empty($itemsAndCb) && \is_callable(\end($itemsAndCb))) {
            $cb = \array_pop($itemsAndCb);
        }
        $args = \array_merge(['COUNT', $key], $itemsAndCb, [$cb]);
        return $this->topk(...$args);
    }

    /**
     * TOPK.LIST — return the current top-K members.
     *
     * @param  string        $key
     * @param  callable|null $cb
     * @return mixed
     */
    public function topkList($key, $cb = null)
    {
        return $this->topk('LIST', $key, $cb);
    }

    /**
     * TOPK.INFO — return sketch metadata as a flat array of
     * [name, value, name, value, …]: k, width, depth, decay.
     *
     * @param  string        $key
     * @param  callable|null $cb
     * @return mixed
     */
    public function topkInfo($key, $cb = null)
    {
        return $this->topk('INFO', $key, $cb);
    }

    /**
     * LASTSAVE — unix timestamp of the last successful RDB snapshot.
     *
     * @param  callable|null $cb function(int $reply, Client $client): void
     * @return mixed             Coroutine mode: unix seconds. Callback mode: null.
     */
    public function lastSave($cb = null)
    {
        return $this->queueCommand(['LASTSAVE'], $cb);
    }

    /**
     * SAVE — synchronously snapshot the dataset to disk.
     *
     * The server blocks while writing; on Dragonfly the snapshot path is
     * non-blocking but still takes wall-clock time on large datasets.
     *
     * @param  callable|null $cb function($reply, Client $client): void
     * @return mixed             Coroutine mode: true on +OK. Callback mode: null.
     */
    public function save($cb = null)
    {
        return $this->queueCommand(['SAVE'], $cb);
    }

    /**
     * ROLE — replication role of this server, as an array.
     *
     * Reply shape varies by role: master returns ['master', repl_offset,
     * [[ip, port, offset], ...]], slave returns ['slave', master_ip,
     * master_port, link_state, repl_offset].
     *
     * @param  callable|null $cb function(array $reply, Client $client): void
     * @return mixed             Coroutine mode: role tuple. Callback mode: null.
     */
    public function role($cb = null)
    {
        return $this->queueCommand(['ROLE'], $cb);
    }

    /**
     * SHUTDOWN — ask the server to terminate.
     *
     * Default mode is SAVE (perform an RDB snapshot first); pass 'NOSAVE'
     * to skip persistence. The server normally closes the socket and exits
     * before replying, so the callback may never fire — this is a normal
     * SHUTDOWN behaviour, not a client bug. The internal $_quitting flag
     * is set so the onClose handler does NOT auto-reconnect after the
     * server-side close.
     *
     * DANGER: this stops the Redis/Dragonfly process. Test suites must not
     * call shutdown() against a shared server.
     *
     * @param  string|callable $mode 'SAVE' (default) or 'NOSAVE', or the callback.
     * @param  callable|null   $cb   function($reply, Client $client): void
     * @return mixed                 Reply rarely arrives; usually null.
     */
    public function shutdown($mode = 'SAVE', $cb = null)
    {
        if (\is_callable($mode)) {
            $cb = $mode;
            $mode = 'SAVE';
        }
        // Suppress the auto-reconnect once the server hangs up.
        $this->_quitting = true;
        return $this->queueCommand(['SHUTDOWN', $mode], $cb);
    }

    /**
     * DIGEST — Dragonfly-specific hash digest of the dataset.
     *
     * The reply is a hex string covering the current DB state. Provided as
     * an explicit method (rather than relying on __call) because the
     * no-arg-plus-callback shape `$redis->digest($cb)` would otherwise
     * land in __call()'s count==1 path where the callable is sent on the
     * wire instead of being treated as the callback.
     *
     * Note: this is a Dragonfly extension. Stock Redis returns -ERR
     * unknown command and the callback receives `false`.
     *
     * @param  callable|null $cb function(string|false $reply, Client $client): void
     * @return mixed             Coroutine mode: the hex digest string. Callback mode: null.
     */
    public function digest($cb = null)
    {
        return $this->queueCommand(['DIGEST'], $cb);
    }

    /**
     * Incrementally iterate the keyspace one batch at a time.
     *
     * Reshapes Redis's `[cursor, [keys]]` tuple into `['cursor' => string, 'keys' => array]`.
     * The cursor is always a string; `'0'` signals iteration complete. Non-array replies
     * (e.g. error strings) are passed through unchanged.
     *
     * @param string|int    $cursor  Cursor value; start with '0'.
     * @param array         $options Recognised keys (case-insensitive): MATCH, COUNT, TYPE. Unknown keys are ignored.
     * @param callable|null $cb      function(array|mixed $reply, Client $client): void
     * @return array|null            Coroutine mode: the formatted reply. Callback mode: null.
     */
    public function scan($cursor, array $options = [], $cb = null)
    {
        $args = ['SCAN', (string)$cursor];
        foreach ($options as $key => $value) {
            $upper = \strtoupper((string)$key);
            if ($upper === 'MATCH' || $upper === 'COUNT' || $upper === 'TYPE') {
                $args[] = $upper;
                $args[] = $value;
            }
        }
        $format = function ($result) {
            if (!\is_array($result)) {
                return $result;
            }
            $cursor = isset($result[0]) ? (string)$result[0] : '0';
            $keys = (isset($result[1]) && \is_array($result[1])) ? $result[1] : [];
            return ['cursor' => $cursor, 'keys' => $keys];
        };
        return $this->queueCommand($args, $cb, $format);
    }

    /**
     * Drive SCAN to completion and return every matching key.
     *
     * Loops scan() from cursor '0' until Redis returns '0', accumulating keys across batches.
     * The 'limit' option (default 100000) caps the result so a growing keyspace can't loop
     * forever; iteration stops once the collected count reaches the limit. On a Redis-side
     * error iteration halts and the caller receives `false` (see error()).
     *
     * @param array         $options Same keys as scan() (MATCH, COUNT, TYPE) plus 'limit' (int).
     * @param callable|null $cb      function(array|false $keys, Client $client): void
     * @return array|false|null      Coroutine mode: aggregated keys array, or `false` on error. Callback mode: null.
     */
    public function scanAll(array $options = [], $cb = null)
    {
        $limit = 100000;
        $scanOptions = [];
        foreach ($options as $key => $value) {
            $upper = \strtoupper((string)$key);
            if ($upper === 'LIMIT') {
                $limit = (int)$value;
                continue;
            }
            if ($upper === 'MATCH' || $upper === 'COUNT' || $upper === 'TYPE') {
                $scanOptions[$upper] = $value;
            }
        }

        // Coroutine mode: synchronous loop, return aggregated keys.
        if (!$cb && $this->inCoroutineMode()) {
            $collected = [];
            $cursor = '0';
            do {
                $reply = $this->scan($cursor, $scanOptions);
                if (!\is_array($reply) || !isset($reply['cursor'])) {
                    // scan() failed; $this->_error already set by the
                    // queueCommand error path. Signal abort to the caller.
                    return false;
                }
                foreach ($reply['keys'] as $k) {
                    $collected[] = $k;
                    if (\count($collected) >= $limit) {
                        return $collected;
                    }
                }
                $cursor = $reply['cursor'];
            } while ($cursor !== '0');
            return $collected;
        }

        // Callback mode: chain scan() calls via nested callbacks.
        $collected = [];
        $self = $this;
        $step = null;
        $step = function ($reply) use (&$step, &$collected, $self, $scanOptions, $limit, $cb) {
            if (!\is_array($reply) || !isset($reply['cursor'])) {
                // scan() errored — last error is already in $self->_error.
                // Signal abort to the user by handing them `false`, matching
                // the rest of the client's error convention.
                if ($cb) {
                    \call_user_func($cb, false, $self);
                }
                return;
            }
            foreach ($reply['keys'] as $k) {
                $collected[] = $k;
                if (\count($collected) >= $limit) {
                    if ($cb) {
                        \call_user_func($cb, $collected, $self);
                    }
                    return;
                }
            }
            if ($reply['cursor'] === '0') {
                if ($cb) {
                    \call_user_func($cb, $collected, $self);
                }
                return;
            }
            $self->scan($reply['cursor'], $scanOptions, $step);
        };
        $this->scan('0', $scanOptions, $step);
        return null;
    }

    /**
     * Incrementally iterate one hash one batch of fields at a time.
     *
     * Reshapes Redis's `[cursor, [f1, v1, f2, v2, ...]]` flat reply into
     * `['cursor' => string, 'fields' => ['f1' => 'v1', ...]]`. The cursor is always a
     * string; `'0'` signals iteration complete. Non-array replies (e.g. error strings)
     * are passed through unchanged so callers can detect errors.
     *
     * @param string        $key     The hash key to iterate.
     * @param string|int    $cursor  Cursor value; start with '0'.
     * @param array         $options Recognised keys (case-insensitive): MATCH, COUNT. Unknown keys are ignored.
     * @param callable|null $cb      function(array|mixed $reply, Client $client): void
     * @return array|null            Coroutine mode: the formatted reply. Callback mode: null.
     */
    public function hScan($key, $cursor, array $options = [], $cb = null)
    {
        $args = ['HSCAN', $key, (string)$cursor];
        foreach ($options as $optKey => $value) {
            $upper = \strtoupper((string)$optKey);
            if ($upper === 'MATCH' || $upper === 'COUNT') {
                $args[] = $upper;
                $args[] = $value;
            }
        }
        $format = function ($result) {
            if (!\is_array($result)) {
                return $result;
            }
            $cursor = isset($result[0]) ? (string)$result[0] : '0';
            $fields = [];
            if (isset($result[1]) && \is_array($result[1])) {
                $current = '';
                foreach ($result[1] as $index => $item) {
                    if ($index % 2 === 0) {
                        $current = $item;
                        continue;
                    }
                    $fields[$current] = $item;
                }
            }
            return ['cursor' => $cursor, 'fields' => $fields];
        };
        return $this->queueCommand($args, $cb, $format);
    }

    /**
     * Drive HSCAN to completion and return every field=>value pair for one hash.
     *
     * Loops hScan() from cursor '0' until Redis returns '0', merging field=>value pairs
     * across batches into a single associative array. The 'limit' option (default 100000)
     * caps the result so a growing hash can't loop forever; iteration stops once the
     * collected count reaches the limit. On a Redis-side error iteration halts and the
     * caller receives `false` (see error()).
     *
     * @param string        $key     The hash key to iterate.
     * @param array         $options Same keys as hScan() (MATCH, COUNT) plus 'limit' (int).
     * @param callable|null $cb      function(array|false $fields, Client $client): void
     * @return array|false|null      Coroutine mode: aggregated field=>value array, or `false` on error. Callback mode: null.
     */
    public function hScanAll($key, array $options = [], $cb = null)
    {
        $limit = 100000;
        $scanOptions = [];
        foreach ($options as $optKey => $value) {
            $upper = \strtoupper((string)$optKey);
            if ($upper === 'LIMIT') {
                $limit = (int)$value;
                continue;
            }
            if ($upper === 'MATCH' || $upper === 'COUNT') {
                $scanOptions[$upper] = $value;
            }
        }

        // Coroutine mode: synchronous loop, return aggregated fields.
        if (!$cb && $this->inCoroutineMode()) {
            $collected = [];
            $cursor = '0';
            do {
                $reply = $this->hScan($key, $cursor, $scanOptions);
                if (!\is_array($reply) || !isset($reply['cursor'])) {
                    // hScan() failed; $this->_error already set by the
                    // queueCommand error path. Signal abort to the caller.
                    return false;
                }
                foreach ($reply['fields'] as $field => $value) {
                    $collected[$field] = $value;
                    if (\count($collected) >= $limit) {
                        return $collected;
                    }
                }
                $cursor = $reply['cursor'];
            } while ($cursor !== '0');
            return $collected;
        }

        // Callback mode: chain hScan() calls via nested callbacks.
        $collected = [];
        $self = $this;
        $step = null;
        $step = function ($reply) use (&$step, &$collected, $self, $key, $scanOptions, $limit, $cb) {
            if (!\is_array($reply) || !isset($reply['cursor'])) {
                // hScan() errored — last error is already in $self->_error.
                // Signal abort to the user by handing them `false`, matching
                // the rest of the client's error convention.
                if ($cb) {
                    \call_user_func($cb, false, $self);
                }
                return;
            }
            foreach ($reply['fields'] as $field => $value) {
                $collected[$field] = $value;
                if (\count($collected) >= $limit) {
                    if ($cb) {
                        \call_user_func($cb, $collected, $self);
                    }
                    return;
                }
            }
            if ($reply['cursor'] === '0') {
                if ($cb) {
                    \call_user_func($cb, $collected, $self);
                }
                return;
            }
            $self->hScan($key, $reply['cursor'], $scanOptions, $step);
        };
        $this->hScan($key, '0', $scanOptions, $step);
        return null;
    }

    /**
     * Incrementally iterate one set one batch of members at a time.
     *
     * Reshapes Redis's `[cursor, [m1, m2, ...]]` flat reply into
     * `['cursor' => string, 'members' => ['m1', 'm2', ...]]` — same shape as SCAN, not HSCAN.
     * The cursor is always a string; `'0'` signals iteration complete. Non-array replies
     * (e.g. error strings) are passed through unchanged so callers can detect errors.
     *
     * @param string        $key     The set key to iterate.
     * @param string|int    $cursor  Cursor value; start with '0'.
     * @param array         $options Recognised keys (case-insensitive): MATCH, COUNT. Unknown keys are ignored.
     * @param callable|null $cb      function(array|mixed $reply, Client $client): void
     * @return array|null            Coroutine mode: the formatted reply. Callback mode: null.
     */
    public function sScan($key, $cursor, array $options = [], $cb = null)
    {
        $args = ['SSCAN', $key, (string)$cursor];
        foreach ($options as $optKey => $value) {
            $upper = \strtoupper((string)$optKey);
            if ($upper === 'MATCH' || $upper === 'COUNT') {
                $args[] = $upper;
                $args[] = $value;
            }
        }
        $format = function ($result) {
            if (!\is_array($result)) {
                return $result;
            }
            $cursor = isset($result[0]) ? (string)$result[0] : '0';
            $members = (isset($result[1]) && \is_array($result[1])) ? $result[1] : [];
            return ['cursor' => $cursor, 'members' => $members];
        };
        return $this->queueCommand($args, $cb, $format);
    }

    /**
     * Drive SSCAN to completion and return every member of one set.
     *
     * Loops sScan() from cursor '0' until Redis returns '0', accumulating members across
     * batches. Set members are unique by definition, but SCAN-family commands can revisit
     * the same slot during a rehash, so the accumulator dedupes via a member-keyed map and
     * returns `array_values($map)`. The 'limit' option (default 100000) caps the result so
     * a growing set can't loop forever; iteration stops once the collected count reaches
     * the limit. On a Redis-side error iteration halts and the caller receives `false`
     * (see error()).
     *
     * @param string        $key     The set key to iterate.
     * @param array         $options Same keys as sScan() (MATCH, COUNT) plus 'limit' (int).
     * @param callable|null $cb      function(array|false $members, Client $client): void
     * @return array|false|null      Coroutine mode: aggregated members array, or `false` on error. Callback mode: null.
     */
    public function sScanAll($key, array $options = [], $cb = null)
    {
        $limit = 100000;
        $scanOptions = [];
        foreach ($options as $optKey => $value) {
            $upper = \strtoupper((string)$optKey);
            if ($upper === 'LIMIT') {
                $limit = (int)$value;
                continue;
            }
            if ($upper === 'MATCH' || $upper === 'COUNT') {
                $scanOptions[$upper] = $value;
            }
        }

        // Coroutine mode: synchronous loop, return aggregated members.
        if (!$cb && $this->inCoroutineMode()) {
            $collected = [];
            $cursor = '0';
            do {
                $reply = $this->sScan($key, $cursor, $scanOptions);
                if (!\is_array($reply) || !isset($reply['cursor'])) {
                    // sScan() failed; $this->_error already set by the
                    // queueCommand error path. Signal abort to the caller.
                    return false;
                }
                foreach ($reply['members'] as $member) {
                    // Dedupe — SCAN can revisit members during a rehash.
                    // Member used as map key; array_values() flattens at the end.
                    $collected[(string)$member] = $member;
                    if (\count($collected) >= $limit) {
                        return \array_values($collected);
                    }
                }
                $cursor = $reply['cursor'];
            } while ($cursor !== '0');
            return \array_values($collected);
        }

        // Callback mode: chain sScan() calls via nested callbacks.
        $collected = [];
        $self = $this;
        $step = null;
        $step = function ($reply) use (&$step, &$collected, $self, $key, $scanOptions, $limit, $cb) {
            if (!\is_array($reply) || !isset($reply['cursor'])) {
                // sScan() errored — last error is already in $self->_error.
                // Signal abort to the user by handing them `false`, matching
                // the rest of the client's error convention.
                if ($cb) {
                    \call_user_func($cb, false, $self);
                }
                return;
            }
            foreach ($reply['members'] as $member) {
                $collected[(string)$member] = $member;
                if (\count($collected) >= $limit) {
                    if ($cb) {
                        \call_user_func($cb, \array_values($collected), $self);
                    }
                    return;
                }
            }
            if ($reply['cursor'] === '0') {
                if ($cb) {
                    \call_user_func($cb, \array_values($collected), $self);
                }
                return;
            }
            $self->sScan($key, $reply['cursor'], $scanOptions, $step);
        };
        $this->sScan($key, '0', $scanOptions, $step);
        return null;
    }

    /**
     * Incrementally iterate one sorted set one batch of member=>score pairs at a time.
     *
     * Reshapes Redis's `[cursor, [m1, s1, m2, s2, ...]]` flat reply into
     * `['cursor' => string, 'members' => ['m1' => 's1', 'm2' => 's2', ...]]`. The cursor is
     * always a string; `'0'` signals iteration complete. Scores are kept as the raw bulk
     * strings Redis sent — casting to float would lose precision on values that don't have
     * an exact binary representation. Non-array replies (e.g. error strings) are passed
     * through unchanged so callers can detect errors.
     *
     * @param string        $key     The sorted set key to iterate.
     * @param string|int    $cursor  Cursor value; start with '0'.
     * @param array         $options Recognised keys (case-insensitive): MATCH, COUNT. Unknown keys are ignored.
     * @param callable|null $cb      function(array|mixed $reply, Client $client): void
     * @return array|null            Coroutine mode: the formatted reply. Callback mode: null.
     */
    public function zScan($key, $cursor, array $options = [], $cb = null)
    {
        $args = ['ZSCAN', $key, (string)$cursor];
        foreach ($options as $optKey => $value) {
            $upper = \strtoupper((string)$optKey);
            if ($upper === 'MATCH' || $upper === 'COUNT') {
                $args[] = $upper;
                $args[] = $value;
            }
        }
        $format = function ($result) {
            if (!\is_array($result)) {
                return $result;
            }
            $cursor = isset($result[0]) ? (string)$result[0] : '0';
            $members = [];
            if (isset($result[1]) && \is_array($result[1])) {
                $current = '';
                foreach ($result[1] as $index => $item) {
                    if ($index % 2 === 0) {
                        $current = $item;
                        continue;
                    }
                    // Score stays as the raw bulk string — casting to float
                    // would lose precision for non-exact-binary values.
                    $members[$current] = $item;
                }
            }
            return ['cursor' => $cursor, 'members' => $members];
        };
        return $this->queueCommand($args, $cb, $format);
    }

    /**
     * Drive ZSCAN to completion and return every member=>score pair for one sorted set.
     *
     * Loops zScan() from cursor '0' until Redis returns '0', merging member=>score pairs
     * across batches into a single associative array. Sorted set members are unique by
     * definition, so a member re-yielded during a rehash simply overwrites the previous
     * score (which is also the current score). The 'limit' option (default 100000) caps
     * the result so a growing sorted set can't loop forever; iteration stops once the
     * collected count reaches the limit. On a Redis-side error iteration halts and the
     * caller receives `false` (see error()).
     *
     * @param string        $key     The sorted set key to iterate.
     * @param array         $options Same keys as zScan() (MATCH, COUNT) plus 'limit' (int).
     * @param callable|null $cb      function(array|false $members, Client $client): void
     * @return array|false|null      Coroutine mode: aggregated member=>score array, or `false` on error. Callback mode: null.
     */
    public function zScanAll($key, array $options = [], $cb = null)
    {
        $limit = 100000;
        $scanOptions = [];
        foreach ($options as $optKey => $value) {
            $upper = \strtoupper((string)$optKey);
            if ($upper === 'LIMIT') {
                $limit = (int)$value;
                continue;
            }
            if ($upper === 'MATCH' || $upper === 'COUNT') {
                $scanOptions[$upper] = $value;
            }
        }

        // Coroutine mode: synchronous loop, return aggregated members.
        if (!$cb && $this->inCoroutineMode()) {
            $collected = [];
            $cursor = '0';
            do {
                $reply = $this->zScan($key, $cursor, $scanOptions);
                if (!\is_array($reply) || !isset($reply['cursor'])) {
                    // zScan() failed; $this->_error already set by the
                    // queueCommand error path. Signal abort to the caller.
                    return false;
                }
                foreach ($reply['members'] as $member => $score) {
                    $collected[$member] = $score;
                    if (\count($collected) >= $limit) {
                        return $collected;
                    }
                }
                $cursor = $reply['cursor'];
            } while ($cursor !== '0');
            return $collected;
        }

        // Callback mode: chain zScan() calls via nested callbacks.
        $collected = [];
        $self = $this;
        $step = null;
        $step = function ($reply) use (&$step, &$collected, $self, $key, $scanOptions, $limit, $cb) {
            if (!\is_array($reply) || !isset($reply['cursor'])) {
                // zScan() errored — last error is already in $self->_error.
                // Signal abort to the user by handing them `false`, matching
                // the rest of the client's error convention.
                if ($cb) {
                    \call_user_func($cb, false, $self);
                }
                return;
            }
            foreach ($reply['members'] as $member => $score) {
                $collected[$member] = $score;
                if (\count($collected) >= $limit) {
                    if ($cb) {
                        \call_user_func($cb, $collected, $self);
                    }
                    return;
                }
            }
            if ($reply['cursor'] === '0') {
                if ($cb) {
                    \call_user_func($cb, $collected, $self);
                }
                return;
            }
            $self->zScan($key, $reply['cursor'], $scanOptions, $step);
        };
        $this->zScan($key, '0', $scanOptions, $step);
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 9 — partial-support commands
    |--------------------------------------------------------------------------
    |
    | A small grab-bag of commands that needed dedicated wrappers either to
    | sidestep the no-arg-callback bug in __call() (BGSAVE), to spell an
    | underscore the strtoupper() pipeline can't produce (SORT_RO — already
    | above), or to drive a module / dotted command family (FT.*, MODULE).
    |
    | The HEXPIRE family (HEXPIRE / HPERSIST / HTTL / HEXPIREAT / HEXPIRETIME
    | / HPEXPIRE / HPTTL) does NOT get explicit methods: every member is a
    | multi-arg verb so __call()'s count > 1 branch correctly pops the
    | trailing callable, and the @method declarations above lock in IDE
    | autocomplete. Dragonfly currently only ships HEXPIRE and HTTL; the
    | rest reply -ERR unknown command and the test suite skips them.
    */

    /**
     * BGSAVE — request an asynchronous background snapshot.
     *
     * Without an explicit method, `$redis->bgSave($cb)` lands in __call()'s
     * count == 1 path where the closure is sent on the wire as a BGSAVE
     * argument rather than being treated as the callback — same shape bug
     * as PING / INFO / DBSIZE.
     *
     * Pass `$schedule = true` to send `BGSAVE SCHEDULE`, deferring the
     * snapshot until any in-progress AOF rewrite completes. A callable in
     * the first slot is folded in as the callback with a regular (non-
     * scheduled) BGSAVE, matching the flushDb()/info() shortcut style.
     *
     * @param  bool|callable $schedule true for SCHEDULE, or the callback.
     * @param  callable|null $cb       function($reply, Client $client): void
     * @return mixed                   Coroutine mode: true on +OK. Callback mode: null.
     */
    public function bgSave($schedule = false, $cb = null)
    {
        if (\is_callable($schedule)) {
            $cb = $schedule;
            $schedule = false;
        }
        return $this->queueCommand($schedule ? ['BGSAVE', 'SCHEDULE'] : ['BGSAVE'], $cb);
    }

    /**
     * MODULE — module-management subcommand family.
     *
     * Wire form: `MODULE <verb> [args...]`. Typical verbs are LIST, LOAD,
     * UNLOAD. On Dragonfly modules are statically linked: LIST reports
     * loaded modules (ReJSON, search, …) but LOAD / UNLOAD return errors.
     *
     * A trailing callable is taken as the callback by the dispatcher.
     * A trailing null is treated as "no callback", so moduleList() can
     * forward its `$cb = null` default without sending an extra argument.
     *
     * @param  mixed ...$args [verb, ...args, optional callable or trailing null]
     * @return mixed
     */
    public function module(...$args)
    {
        if (!empty($args) && \end($args) === null) {
            \array_pop($args);
        }
        return $this->dispatcher('MODULE ', $args);
    }

    /**
     * MODULE LIST — return the currently loaded modules.
     *
     * Reply shape on Dragonfly: a flat array of `[name, <module>, ver,
     * <int>, name, <module>, ver, <int>, ...]` — pairs of name/value
     * metadata per module. The format-callback layer doesn't reshape this;
     * callers can walk it as-is or use a helper.
     *
     * @param  callable|null $cb function(array $reply, Client $client): void
     * @return mixed             Coroutine mode: module list. Callback mode: null.
     */
    public function moduleList($cb = null)
    {
        return $this->module('LIST', $cb);
    }

    /*
    |--------------------------------------------------------------------------
    | RedisSearch (FT) module — supported by Dragonfly
    |--------------------------------------------------------------------------
    |
    | Dragonfly ships a search module that implements the RedisSearch
    | `FT.*` command surface (see MODULE LIST for the version). Same dotted
    | dispatch pattern as JSON.* / BF.* / CMS.* / TOPK.*: the dispatcher
    | glues the verb onto `FT.` to form a single Redis token, e.g.
    | `FT.CREATE`, `FT.SEARCH`, `FT.AGGREGATE`.
    |
    | The ft(...$args) dispatcher accepts arbitrary verbs (including the
    | underscore-prefixed `_LIST` administrative variant). The shortcut
    | wrappers cover the most-common surface for IDE autocomplete.
    */

    /**
     * FT.* — RedisSearch module dispatcher.
     *
     * Wire form: `FT.<verb> [args...]`. The first positional arg is the
     * verb (uppercased and glued onto the `FT.` prefix); a trailing
     * callable is taken as the callback. A trailing null is treated as
     * "no callback" so the typed shortcuts can forward their `$cb = null`
     * defaults uniformly.
     *
     * Note: FT._LIST has a leading underscore that strtoupper() preserves
     * intact, so ftList() can go through this dispatcher path.
     *
     * @param  mixed ...$args [verb, ...args, optional callable or trailing null]
     * @return mixed
     */
    public function ft(...$args)
    {
        if (!empty($args) && \end($args) === null) {
            \array_pop($args);
        }
        return $this->dispatcher('FT.', $args);
    }

    /**
     * FT.CREATE — define a new index over hash or JSON documents.
     *
     * Wire form is highly variadic — typical usage is
     * `FT.CREATE idx ON HASH PREFIX 1 doc: SCHEMA name TEXT score NUMERIC`.
     * All args after $index are forwarded verbatim; a trailing callable is
     * popped and treated as the callback.
     *
     * @param  string $index
     * @param  mixed  ...$args index-definition tokens, optional trailing callable.
     * @return mixed
     */
    public function ftCreate($index, ...$args)
    {
        $cb = null;
        if (!empty($args) && \is_callable(\end($args))) {
            $cb = \array_pop($args);
        }
        $wire = \array_merge(['CREATE', $index], $args, [$cb]);
        return $this->ft(...$wire);
    }

    /**
     * FT.SEARCH — query an index.
     *
     * Reply shape: `[total, doc1Key, [doc1Field, doc1Value, ...], doc2Key,
     * [doc2Field, doc2Value, ...], ...]` for HASH indexes. The flat shape
     * makes incremental decoding trivial but callers usually want to walk
     * the result in steps of 2 (key + flat-field-array).
     *
     * Optional tokens (LIMIT offset count, RETURN n field..., NOCONTENT,
     * SORTBY, etc.) flow through $optionsAndCb verbatim. A trailing
     * callable is popped and treated as the callback.
     *
     * @param  string $index
     * @param  string $query
     * @param  mixed  ...$optionsAndCb FT.SEARCH option tokens, optional trailing callable.
     * @return mixed
     */
    public function ftSearch($index, $query, ...$optionsAndCb)
    {
        $cb = null;
        if (!empty($optionsAndCb) && \is_callable(\end($optionsAndCb))) {
            $cb = \array_pop($optionsAndCb);
        }
        $wire = \array_merge(['SEARCH', $index, $query], $optionsAndCb, [$cb]);
        return $this->ft(...$wire);
    }

    /**
     * FT.AGGREGATE — run an aggregation pipeline over an index.
     *
     * Wire form: `FT.AGGREGATE idx query [GROUPBY ...] [REDUCE ...] [SORTBY
     * ...] [LIMIT ...]`. Reply shape is roughly `[count, [field, value,
     * ...], [field, value, ...], ...]`.
     *
     * @param  string $index
     * @param  string $query
     * @param  mixed  ...$optionsAndCb Pipeline tokens, optional trailing callable.
     * @return mixed
     */
    public function ftAggregate($index, $query, ...$optionsAndCb)
    {
        $cb = null;
        if (!empty($optionsAndCb) && \is_callable(\end($optionsAndCb))) {
            $cb = \array_pop($optionsAndCb);
        }
        $wire = \array_merge(['AGGREGATE', $index, $query], $optionsAndCb, [$cb]);
        return $this->ft(...$wire);
    }

    /**
     * FT.DROPINDEX — delete an index.
     *
     * Pass `$deleteDocs = true` to also remove the indexed documents (the
     * `DD` flag). A callable in the first slot folds in as the callback
     * with the documents preserved.
     *
     * @param  string        $index
     * @param  bool|callable $deleteDocs true to add `DD`, or the callback.
     * @param  callable|null $cb         function($reply, Client $client): void
     * @return mixed
     */
    public function ftDropIndex($index, $deleteDocs = false, $cb = null)
    {
        if (\is_callable($deleteDocs)) {
            $cb = $deleteDocs;
            $deleteDocs = false;
        }
        return $deleteDocs
            ? $this->ft('DROPINDEX', $index, 'DD', $cb)
            : $this->ft('DROPINDEX', $index, $cb);
    }

    /**
     * FT.INFO — return metadata about an index as a flat
     * `[key, value, key, value, ...]` array.
     *
     * @param  string        $index
     * @param  callable|null $cb
     * @return mixed
     */
    public function ftInfo($index, $cb = null)
    {
        return $this->ft('INFO', $index, $cb);
    }

    /**
     * FT._LIST — list the names of every defined index.
     *
     * The administrative verb has a leading underscore that survives
     * strtoupper() unchanged, so dispatcher() forwards it intact.
     *
     * @param  callable|null $cb function(array $reply, Client $client): void
     * @return mixed             Coroutine mode: list of index names. Callback mode: null.
     */
    public function ftList($cb = null)
    {
        return $this->ft('_LIST', $cb);
    }

    /**
     * FT.ALTER — add a field to an existing index.
     *
     * Wire form: `FT.ALTER idx SCHEMA ADD field type [options...]`. All
     * args after $index pass through verbatim; a trailing callable is
     * popped and treated as the callback.
     *
     * @param  string $index
     * @param  mixed  ...$args ALTER tokens, optional trailing callable.
     * @return mixed
     */
    public function ftAlter($index, ...$args)
    {
        $cb = null;
        if (!empty($args) && \is_callable(\end($args))) {
            $cb = \array_pop($args);
        }
        $wire = \array_merge(['ALTER', $index], $args, [$cb]);
        return $this->ft(...$wire);
    }

    /**
     * FT.CONFIG — module-wide configuration subcommand.
     *
     * Wire form: `FT.CONFIG <verb> <option> [value]`. Typical verbs are
     * GET, SET, HELP. Reply shape varies by Dragonfly version — older
     * builds return a flat `[option, value]`, newer ones a list of pairs.
     * Callers should inspect with var_dump() the first time.
     *
     * @param  mixed ...$args [verb, ...args, optional callable]
     * @return mixed
     */
    public function ftConfig(...$args)
    {
        $cb = null;
        if (!empty($args) && \is_callable(\end($args))) {
            $cb = \array_pop($args);
        }
        $wire = \array_merge(['CONFIG'], $args, [$cb]);
        return $this->ft(...$wire);
    }

    /**
     * FT.TAGVALS — return the distinct values of a TAG field in an index.
     *
     * @param  string        $index
     * @param  string        $field
     * @param  callable|null $cb
     * @return mixed
     */
    public function ftTagVals($index, $field, $cb = null)
    {
        return $this->ft('TAGVALS', $index, $field, $cb);
    }

    /**
     * FT.SYNDUMP — dump the synonym map of an index.
     *
     * @param  string        $index
     * @param  callable|null $cb
     * @return mixed
     */
    public function ftSynDump($index, $cb = null)
    {
        return $this->ft('SYNDUMP', $index, $cb);
    }

    /**
     * FT.SYNUPDATE — add or update a synonym group on an index.
     *
     * Wire form: `FT.SYNUPDATE idx groupId term1 [term2 ...]`. A trailing
     * callable is popped and treated as the callback.
     *
     * @param  string $index
     * @param  string $groupId
     * @param  mixed  ...$termsAndCb terms..., optional trailing callable.
     * @return mixed
     */
    public function ftSynUpdate($index, $groupId, ...$termsAndCb)
    {
        $cb = null;
        if (!empty($termsAndCb) && \is_callable(\end($termsAndCb))) {
            $cb = \array_pop($termsAndCb);
        }
        $wire = \array_merge(['SYNUPDATE', $index, $groupId], $termsAndCb, [$cb]);
        return $this->ft(...$wire);
    }

    /**
     * FT.PROFILE — execute a SEARCH or AGGREGATE with timing information.
     *
     * Wire form: `FT.PROFILE idx SEARCH|AGGREGATE [LIMITED] QUERY query
     * [args...]`. All args after $index pass through verbatim.
     *
     * @param  string $index
     * @param  mixed  ...$args profile spec tokens, optional trailing callable.
     * @return mixed
     */
    public function ftProfile($index, ...$args)
    {
        $cb = null;
        if (!empty($args) && \is_callable(\end($args))) {
            $cb = \array_pop($args);
        }
        $wire = \array_merge(['PROFILE', $index], $args, [$cb]);
        return $this->ft(...$wire);
    }

}
