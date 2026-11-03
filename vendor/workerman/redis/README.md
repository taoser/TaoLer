# redis

Asynchronous Redis client for PHP, built on Workerman.

[![Latest Stable Version](https://poser.pugx.org/workerman/redis/v/stable)](https://packagist.org/packages/workerman/redis)
[![Total Downloads](https://poser.pugx.org/workerman/redis/downloads)](https://packagist.org/packages/workerman/redis)
[![License](https://poser.pugx.org/workerman/redis/license)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/workerman/redis.svg)](https://php.net)
[![CI](https://github.com/workerman-php/redis/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/workerman-php/redis/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/workerman-php/redis/graph/badge.svg)](https://codecov.io/gh/workerman-php/redis)

Wire-compatible with both Redis and [Dragonfly](https://www.dragonflydb.io/). Supports two execution modes:

- **Callback mode** — works out of the box, no extra dependencies.
- **Coroutine mode** — if [`revolt/event-loop`](https://github.com/revoltphp/event-loop) is installed, methods can be called without a callback and the current fiber suspends until the result arrives.

## Requirements

- **Runtime: PHP ≥ 7.2** for callback mode — the library code itself carries no
  PHP 8-only syntax, and Composer resolves Workerman to v4 on PHP 7.
- **Coroutine mode requires PHP ≥ 8.1** with
  [`revolt/event-loop`](https://github.com/revoltphp/event-loop) installed (and,
  for Workerman v5's fiber loop, Workerman ≥ 5). It is gated at runtime via
  `class_exists()`, so on PHP 7 it simply stays off and you use callbacks.
- **Development/test tooling.** Dev dependencies are declared as version
  **ranges** (not pins), so each PHP version resolves a compatible toolchain and
  the suite runs from **PHP ≥ 7.2** up to 8.3:
  - PHP **7.2 / 7.3 / 7.4** → PHPUnit 9 + Workerman 4 (CI strips
    `phpstan/phpstan` and `revolt/event-loop` — which need newer PHP — before
    `composer update`, and uses `phpunit9.xml.dist`).
  - PHP **8.1 / 8.2 / 8.3** → PHPUnit 12 + Workerman 5 (uses `phpunit.xml.dist`).

  Running the suite does **not** affect applications that pull the package in as
  a dependency — these are dev-only requirements.

## Install

```
composer require workerman/redis
```

## Usage

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

## SCAN

Non-blocking alternative to `KEYS *`. `scan()` wraps a single `SCAN` call;
`scanAll()` drives the cursor loop and returns every matching key.

Both examples assume `$redis` is a connected `Client` (see the `Usage` block above).

```php
// One step — pass the cursor through yourself.
$redis->scan('0', ['MATCH' => 'user:*', 'COUNT' => 100], function ($reply) {
    // $reply === ['cursor' => '17', 'keys' => ['user:1', 'user:5', ...]]
});

// Iterator helper — collects every matching key.
$redis->scanAll(['MATCH' => 'session:*', 'COUNT' => 200], function ($keys) {
    foreach ($keys as $key) {
        // ...
    }
});
```

The `limit` option (default `100000`) caps the total keys collected by `scanAll()` so a growing keyspace can't loop forever.
On a Redis-side error the callback receives `false`.

## HSCAN

Non-blocking iterator over a single hash's fields. `hScan()` wraps a single `HSCAN` call;
`hScanAll()` drives the cursor loop and returns every field=>value pair as an associative array.

```php
// One step — pass the cursor through yourself.
$redis->hScan('user:42:meta', '0', ['MATCH' => 'pref_*', 'COUNT' => 100], function ($reply) {
    // $reply === ['cursor' => '17', 'fields' => ['pref_theme' => 'dark', ...]]
});

// Iterator helper — collects every field=>value pair for the hash.
$redis->hScanAll('user:42:meta', ['COUNT' => 200], function ($fields) {
    foreach ($fields as $field => $value) {
        // ...
    }
});
```

The `limit` option (default `100000`) caps the total fields collected by `hScanAll()`.
On a Redis-side error the callback receives `false`.

## SSCAN

Non-blocking iterator over a single set's members. `sScan()` wraps a single `SSCAN` call;
`sScanAll()` drives the cursor loop and returns every member as a flat array.

```php
// One step — pass the cursor through yourself.
$redis->sScan('user:42:tags', '0', ['MATCH' => 'topic_*', 'COUNT' => 100], function ($reply) {
    // $reply === ['cursor' => '17', 'members' => ['topic_php', 'topic_redis', ...]]
});

// Iterator helper — collects every member of the set.
$redis->sScanAll('user:42:tags', ['COUNT' => 200], function ($members) {
    foreach ($members as $member) {
        // ...
    }
});
```

The `limit` option (default `100000`) caps the total members collected by `sScanAll()`.
On a Redis-side error the callback receives `false`.

## ZSCAN

Non-blocking iterator over a single sorted set's member=>score map. `zScan()` wraps a single
`ZSCAN` call; `zScanAll()` drives the cursor loop and returns every member=>score pair as an
associative array. Scores stay as the raw bulk strings Redis sent — casting to float would
lose precision on values that don't have an exact binary representation.

```php
// One step — pass the cursor through yourself.
$redis->zScan('leaderboard:weekly', '0', ['MATCH' => 'user:*', 'COUNT' => 100], function ($reply) {
    // $reply === ['cursor' => '17', 'members' => ['user:42' => '1500', 'user:7' => '980', ...]]
});

// Iterator helper — collects every member=>score pair for the sorted set.
$redis->zScanAll('leaderboard:weekly', ['COUNT' => 200], function ($members) {
    foreach ($members as $member => $score) {
        // $score is the raw string Redis returned — keep it that way for precision.
    }
});
```

The `limit` option (default `100000`) caps the total members collected by `zScanAll()`.
On a Redis-side error the callback receives `false`.

## rawCommand

Escape hatch for sending any Redis or Dragonfly command verbatim — useful for verbs that don't yet have a dedicated wrapper (new server commands, custom modules, multi-word admin verbs, etc.). The args you pass are the wire payload: the first non-callback arg is the command name and the rest are its arguments. The optional trailing callable receives the reply.

```php
$redis->rawCommand('CONFIG', 'GET', 'maxmemory', function ($reply) {
    // $reply === ['maxmemory', '0']
});
```

Calling `rawCommand()` with no args (or only a callback) throws `InvalidArgumentException` rather than sending an empty command.

## Pub/Sub

`subscribe()` / `pSubscribe()` / `sSubscribe()` put the connection into
subscribe mode and stream messages to your callback. Once subscribed, the
connection is *locked* — Redis only allows (un)subscribe commands on it — so
the matching `unsubscribe()` / `pUnsubscribe()` / `sUnsubscribe()` methods are
how you hand it back for ordinary commands. They write the teardown frame
straight to the socket (bypassing the lock), and the connection resumes normal
work as soon as the server confirms zero remaining subscriptions.

```php
$redis->subscribe(['news', 'sport'], function ($channel, $message) {
    // fires for every published message
});

// Later — e.g. from a Timer — drop the subscription and resume normal use.
$redis->unsubscribe();                      // omit args to drop every channel
$redis->get('some:key', function ($value) {
    // runs now that the connection is no longer locked
});
```

`unsubscribe()` takes an optional list of channels (omit to drop them all) and
an optional trailing callback that fires with `(true, $client)` once the
connection has fully left subscribe mode. That callback means "back to normal
command mode" — on a partial unsubscribe (dropping some of several channels) it
is held until the last channel goes too, so track per-channel state in your
subscribe callback if you need it. `pUnsubscribe()` mirrors it for
`pSubscribe()` patterns, and `sUnsubscribe()` for `sSubscribe()` shard
channels. Calling them when not subscribed is a no-op that still invokes the
callback. To stop listening entirely you can also just `close()` the client.

> **One stream per connection.** A `Client` pins a single streaming entry and
> routes every incoming message to its callback, so a connection can host **one**
> active subscription at a time. Subscribe to every channel/pattern you need in a
> single call — `subscribe(['a', 'b', 'c'], $cb)`. A second `subscribe()` /
> `pSubscribe()` / `sSubscribe()` (or mixing the families) on a connection that
> already has an active or pending stream throws a `Workerman\Redis\Exception`
> rather than silently doing nothing; use a separate `Client` for an additional
> stream. The same one-stream rule applies in coroutine mode: issuing an ordinary
> (suspending) command while the connection is subscribe/monitor-locked throws,
> because its reply could never arrive to resume the fiber — run ordinary
> commands on a different `Client`.

## Monitor

`monitor()` streams every command the server processes to your callback — the
debugging counterpart to `redis-cli MONITOR`. Like `subscribe()` it locks the
connection (its own internal flag); there is no "unmonitor", so you stop it by
`close()`ing the client. The opening `+OK` handshake is swallowed; each later
call is one raw monitor line. Like the subscribe family it is one-stream-per-
connection — calling `monitor()` on a connection that already has an active or
pending stream is ignored.

```php
$debug = new Client('redis://127.0.0.1:6379');
$debug->monitor(function ($line) {
    // e.g. 1700000000.123456 [0 127.0.0.1:6379] "set" "key" "value"
    error_log($line);
});
```

> **Heads up:** MONITOR mirrors *all* traffic the server handles and measurably
> lowers its throughput. Use a dedicated client for it and never leave one
> running on a hot path.

## Server commands

Explicit wrappers for the no-arg health and admin commands: `ping()`, `info()`, `dbSize()`, `time()`, `flushDb()`, `flushAll()`. These bypass `__call()`'s trailing-callback handling (which only triggers when more than one argument is passed), so the closure goes through `queueCommand()` instead of being shipped to Redis as a bogus command arg.

```php
$redis->ping(function ($reply) {
    // $reply === 'PONG'
});

$redis->dbSize(function ($count) {
    // $count is an int — number of keys in the current DB
});
```

`info($section, $cb)` accepts an optional section filter (`'server'`, `'memory'`, `'clients'`, …). `flushDb($async, $cb)` and `flushAll($async, $cb)` take an optional first arg — pass `true` to send `FLUSHDB ASYNC` / `FLUSHALL ASYNC` for a non-blocking flush, or pass the callback directly for a synchronous one.

## JSON module

The `json()` dispatcher and `jsonSet()` / `jsonGet()` / `jsonDel()` / `jsonMerge()` / `jsonArrAppend()` / … shortcuts speak the RedisJSON `JSON.*` command family. Dragonfly implements this natively (no module install needed); on stock Redis the same wire form works against a server with RedisJSON loaded. Values cross the wire as JSON-encoded strings — the client does not auto-decode replies, so use `json_decode($reply, true)` where you need a PHP array.

```php
$redis->jsonSet('user:42', '$', '{"name":"alice","tags":["a","b"]}', function ($ok) use ($redis) {
    $redis->jsonGet('user:42', function ($reply) {
        $doc = json_decode($reply, true);
        // $doc === ['name' => 'alice', 'tags' => ['a', 'b']]
    });
});
```

## Coroutine mode

Every command accepts an optional **trailing callback** as shown above. If you
install [`revolt/event-loop`](https://github.com/revoltphp/event-loop), you can
instead **omit the callback** and the current fiber suspends until the reply
arrives, so the call reads synchronously and returns the value directly:

```php
// Callback mode (always available):
$redis->get('key', function ($value) { /* ... */ });

// Coroutine mode (with revolt/event-loop installed):
$value = $redis->get('key');   // suspends this fiber, returns the value
```

This applies to the whole command surface below — including the `scanAll()`
iterators, the module dispatchers, and the server-admin helpers.

## Command reference

The fork exposes a typed `@method` surface for every command
[Dragonfly](https://www.dragonflydb.io/) fully or partially supports, so IDEs
and PHPStan see them. Commands marked **new** were added or fixed in this fork
(see [`CHANGELOG.md`](CHANGELOG.md)); the rest carried over from upstream and now
just have declarations and tests. Commands not listed (e.g. `set`, `get`, `del`,
`hSet`, `zAdd`, `lPush`, the `x*` stream verbs) work exactly as before.

| Family | New / fixed in this fork |
|--------|--------------------------|
| **Strings** | `getDel`, `getEx`, `substr` |
| **Keys** | `copy`, `touch`, `expireTime`, `pExpireTime`, **`scan`/`scanAll`** |
| **Hashes** | `hRandField`, **`hScan`/`hScanAll`**, HEXPIRE family (`hExpire`, `hPersist`, `hExpireAt`, `hTtl`, `hExpireTime`, `hPExpire`, `hPExpireAt`, `hPTtl`, `hPExpireTime`) |
| **Lists** | `lMove`, `lMPop`, `lPos`, `blMove`, `blMPop` |
| **Sets** | `sMIsMember`, `sInterCard`, **`sScan`/`sScanAll`** |
| **Sorted sets** | `zRandMember`, `zMScore`, `zDiff`, `zDiffStore`, `zInter`, `zInterCard`, `zUnion`, `zRangeStore`, `zMPop`, `bzMPop`, `zRevRangeByLex`, `zRemRangeByLex`, `zLexCount`, **`zScan`/`zScanAll`** |
| **Streams** | `xAutoClaim`, `xSetId`, **`xAdd`** (explicit, flattens the field map) |
| **Bitmap** | `bitOp`, `bitPos`, `bitField`, **`bitFieldRo`** |
| **Geo** | `geoSearch`, **`geoRadiusRo`**, **`geoRadiusByMemberRo`** |
| **Scripting** | **`evalRo`**, **`evalShaRo`** |
| **Pub/Sub** | `sPublish`, **`sSubscribe`**, **`unsubscribe`/`pUnsubscribe`/`sUnsubscribe`** |
| **Connection/server** | **`ping`/`info`/`dbSize`/`time`/`flushDb`/`flushAll`/`quit`**, `echo`, `hello` |
| **Server admin** | **`config`/`acl`/`slowLog`/`memory`/`command`/`cluster`** dispatchers; **`lastSave`/`save`/`role`/`bgSave`/`shutdown`/`digest`/`monitor`**; `replicaOf`, `slaveOf`, `debug`, `delEx` |
| **Read-only bridges** | **`sortRo`**, **`rawCommand`** |
| **JSON module** | **`json()`** + 16 `json*` shortcuts |
| **Bloom Filter** | **`bf()`** + `bfReserve`, `bfAdd`, `bfExists`, `bfMAdd`, `bfMExists` |
| **Count-Min Sketch** | **`cms()`** + `cmsInitByDim`, `cmsInitByProb`, `cmsIncrBy`, `cmsQuery`, `cmsMerge`, `cmsInfo` |
| **TopK** | **`topk()`** + `topkReserve`, `topkAdd`, `topkIncrBy`, `topkQuery`, `topkCount`, `topkList`, `topkInfo` |
| **RediSearch (FT)** | **`ft()`** + `ftCreate`, `ftSearch`, `ftAggregate`, `ftDropIndex`, `ftInfo`, `ftList`, `ftAlter`, `ftConfig`, `ftTagVals`, `ftSynDump`, `ftSynUpdate`, `ftProfile` |
| **Modules** | **`module()`**, `moduleList` |

The SCAN family, `rawCommand`, Pub/Sub, `monitor()`, the no-arg server commands,
and the JSON module each have their own section above. The remaining families are
documented below.

## Streams — `xAdd()`

`xAdd()` takes the message as a natural `['field' => 'value']` map and flattens
it onto the wire itself, so field names survive (passing the map through the
generic dispatcher would emit values only and the server would reject it):

```php
$redis->xAdd('mystream', '*', ['sensor' => 'temp', 'value' => '21.5'], function ($id) {
    // $id === e.g. '1700000000000-0'
});

// Cap the stream length (MAXLEN [~] n) — the 4th/5th args, or pass the callback directly.
$redis->xAdd('mystream', '*', ['k' => 'v'], 1000, true, function ($id) { /* ~1000 cap */ });
```

`xAutoClaim()` and `xSetId()` route through `__call()` and take their usual
arguments plus an optional trailing callback.

## Modern list / set / sorted-set commands

These all take a trailing callback (or return a value in coroutine mode):

```php
// Lists
$redis->lMove('src', 'dst', 'LEFT', 'RIGHT', function ($moved) {});
$redis->lPos('mylist', 'needle', ['RANK' => 1, 'COUNT' => 2], function ($positions) {});

// Sets
$redis->sMIsMember('myset', 'a', 'b', 'c', function ($flags) {});       // [1, 0, 1]
$redis->sInterCard(2, ['s1', 's2'], 10, function ($card) {});           // bounded cardinality

// Sorted sets
$redis->zMScore('z', 'm1', 'm2', function ($scores) {});                // ['1', '2'] (strings)
$redis->zRangeStore('dst', 'src', 0, -1, [], function ($count) {});
$redis->zUnion(2, ['z1', 'z2'], ['WITHSCORES'], function ($rows) {});
```

## Bitmap, Geo & read-only scripting

The `*_RO` (read-only) verbs need explicit methods because uppercasing a
camelCase name drops the underscore. Each accepts the callable-as-last-arg
shortcut:

```php
$redis->bitFieldRo('bf', 'GET', 'i5', 0, function ($vals) {});
$redis->geoRadiusRo('geo', 13.4, 52.5, 200, 'km', ['WITHDIST'], function ($rows) {});
$redis->geoRadiusByMemberRo('geo', 'Berlin', 200, 'km', [], function ($rows) {});
$redis->evalRo('return ARGV[1]', ['hello'], 0, function ($r) {});
$redis->evalShaRo($sha, ['hello'], 0, function ($r) {});

// SORT_RO — same option grammar as sort():
$redis->sortRo('mylist', ['ALPHA' => true, 'LIMIT' => [0, 10]], function ($sorted) {});
```

`bitOp`, `bitPos`, `bitField`, and `geoSearch` route cleanly through `__call()`.

## Server administration

Multi-verb admin families are reached through dispatchers — the first argument is
the subcommand, the rest are its arguments, and an optional trailing callable is
the callback:

```php
$redis->config('GET', 'maxmemory', function ($pairs) {});      // CONFIG GET maxmemory
$redis->config('SET', 'maxmemory', '256mb', function ($ok) {});
$redis->acl('WHOAMI', function ($user) {});                    // ACL WHOAMI
$redis->slowLog('GET', 10, function ($entries) {});            // SLOWLOG GET 10
$redis->memory('USAGE', 'mykey', function ($bytes) {});        // MEMORY USAGE mykey
$redis->command('COUNT', function ($n) {});                    // COMMAND COUNT
$redis->cluster('INFO', function ($info) {});                  // CLUSTER INFO

// Lifecycle verbs (explicit — these fix the no-arg-callback bug):
$redis->lastSave(function ($ts) {});
$redis->save(function ($ok) {});
$redis->role(function ($role) {});
$redis->bgSave(false, function ($ok) {});      // pass true for BGSAVE SCHEDULE
// $redis->shutdown('SAVE');                    // closes the connection; no reconnect
```

`shutdown()` sets the same don't-reconnect flag `quit()` uses, so the client
won't silently re-open the socket the server just closed.

## Bloom Filter / Count-Min Sketch / TopK modules

RedisBloom-compatible probabilistic structures — native in Dragonfly. Each
family has a dispatcher (`bf()`, `cms()`, `topk()`) plus typed shortcuts:

```php
// Bloom Filter
$redis->bfReserve('seen', 0.01, 100000, function ($ok) {});
$redis->bfAdd('seen', 'user:42', function ($added) {});        // 1 first time, 0 after
$redis->bfMExists('seen', 'a', 'b', 'c', function ($flags) {}); // [1, 0, 0]

// Count-Min Sketch
$redis->cmsInitByProb('freq', 0.001, 0.01, function ($ok) {});
$redis->cmsIncrBy('freq', 'page:/', 1, function ($counts) {});
$redis->cmsQuery('freq', 'page:/', function ($estimates) {});

// TopK
$redis->topkReserve('top', 10, function ($ok) {});             // width/depth/decay default
$redis->topkAdd('top', 'apple', 'banana', function ($dropped) {});
$redis->topkList('top', function ($leaders) {});
```

> Replies follow Dragonfly's shapes: `BF.ADD`/`BF.EXISTS` return ints (1/0);
> `CMS.INFO`/`TOPK.INFO` come back as flat `[name, value, …]` arrays; `TOPK.COUNT`
> is approximate and may under-count by ~1.

## RediSearch (FT) module

Full-text/secondary-index search — preloaded in Dragonfly. The `ft()` dispatcher
prepends `FT.`; typed shortcuts cover the common verbs:

```php
$redis->ftCreate('idx', 'ON', 'HASH', 'PREFIX', 1, 'doc:', 'SCHEMA', 'title', 'TEXT', function ($ok) {
    $redis->ftSearch('idx', 'hello', function ($results) {
        // [total, 'doc:1', ['title', 'hello world'], ...]
    });
});

$redis->ftInfo('idx', function ($meta) {});
$redis->ftList(function ($indexes) {});           // FT._LIST
$redis->ftDropIndex('idx', true, function ($ok) {}); // true => DD (also delete docs)
```

Anything without a shortcut goes through the dispatcher directly, e.g.
`$redis->ft('AGGREGATE', 'idx', '*', 'GROUPBY', 1, '@title', $cb)`.

## Development

```
composer install
composer test          # PHPUnit (phpunit --colors=always)
composer analyze       # PHPStan (8.x only)
composer test:coverage # merged subprocess coverage (sh bin/run-coverage.sh; needs PCOV or Xdebug)
```

Integration tests connect to a real Redis/Dragonfly at `REDIS_URL` (default `redis://127.0.0.1:6379`). Tests skip cleanly when no server is reachable.

The suite is run against **both engines** via the `Makefile` — see *Testing &
continuous integration* below. Any change must stay green on **both** Dragonfly
and Redis; a case that can only pass on one engine must be skipped with a logged
reason via `skipOnBackend()` (never a silent skip) and listed under *Compatibility*.

> The test suite runs on **PHP ≥ 7.2** (PHPUnit 8.5 on 7.2, 9.6 on 7.3/7.4,
> all with Workerman 4; PHPUnit 12 + Workerman 5 on 8.1–8.5). PHPStan runs on
> the **8.x** legs only. Coroutine mode (and its tests) needs **PHP ≥ 8.1** with
> `revolt/event-loop`; the `CoroutineModeTest` cases self-skip below 8.1 via the
> `coroutineSupported()` guard. The library itself runs on **PHP ≥ 7.2** in
> callback mode.
>
> **`composer.lock` is not committed.** This is a library, and the CI legs need
> different dependency resolutions per PHP version, so the lockfile is
> `.gitignore`d and CI runs `composer update` (never `composer install` against a
> committed lock).

## Testing & continuous integration

The fork ships with a [PHPUnit](https://phpunit.de/) test suite — **430 tests
(145 Unit + 285 Feature)** — run against **both Dragonfly and Redis**. The Redis
leg is skip-free; the only skips are behaviour-gated server divergences on
Dragonfly (see *Compatibility* below). It is split into two tiers, so most of the
code can be exercised without a server and the rest is verified end-to-end
against a live engine:

- **Unit suite (`tests/Unit/`) — no server needed.** Pure, mock-style tests that
  run anywhere:
  - `ProtocolTest` round-trips the RESP encoder/decoder directly — nested
    arrays, null bulks/arrays, deep-nesting within `MAX_DEPTH`, the
    depth-overflow protocol-error path, truncated frames, and empty replies.
  - `MethodSurfaceTest` uses reflection to lock in the method surface for
    commands that can't be run live (`shutdown`, `monitor`, the unsubscribe
    family).
- **Feature suite (`tests/Feature/`) — live integration.** Because
  `Worker::runAll()` takes over the process, each integration assertion runs in
  its own short-lived Workerman subprocess via a `runInWorker($snippet)` helper:
  the snippet executes inside a real worker with `$redis`, `$emit()`, and
  `$fail()` in scope and returns its result over a dedicated pipe. Every command
  family — Strings, Keys, Hashes, Lists, Sets, Sorted Sets, the SCAN iterators,
  Streams, Pub/Sub, Bitmap, Geo, scripting, server admin, and the JSON / Bloom /
  CMS / TopK / RediSearch modules — has live round-trip coverage. The suite
  **skips cleanly** when no server is reachable, so `composer test` stays green
  on a bare checkout.
  - **Revolt coroutine mode.** A second worker variant
    (`tests/Support/run-in-worker-coroutine.php`) runs the same snippets under a
    Revolt event loop so the fiber-suspend (`await`) path through `Client.php` is
    exercised end-to-end alongside the default callback mode.

Static analysis runs alongside the tests: **PHPStan** (level 5, with a baseline
that freezes legacy typing issues so new code can't regress).

### Running against both backends

The suite runs against **both** Dragonfly and a real Redis, locally and in CI.
Locally the two engines listen on different ports and the `Makefile` selects one
per leg via the `REDIS_URL` + `REDIS_BACKEND` pair:

| Target | Engine | `REDIS_URL` |
|--------|--------|-------------|
| `make test-dragonfly` | Dragonfly | `redis://127.0.0.1:6379` |
| `make test-redis` | Redis | `redis://127.0.0.1:63790` |
| `make test-all` | both, sequentially (fails if either leg fails) | — |
| `make coverage` | Dragonfly leg only | `redis://127.0.0.1:6379` |

`scripts/start-dragonfly.sh` and `scripts/start-redis.sh` are idempotent
helpers that detect-and-confirm a running engine on each port (`make help` lists
every target). **Both engines must stay green.** A case that can only pass on one
engine is skipped — never silently — with `skipOnBackend('redis', 'reason')` /
`skipOnBackend('dragonfly', 'reason')` (free helpers in `tests/helpers.php`, keyed on
`REDIS_BACKEND`); every skip prints `[<backend>] <reason>` and is documented under
*Compatibility* below.

### Coverage

Feature tests run each assertion inside a `proc_open`ed Workerman worker child,
so pcov in the parent process never instrumented `src/Client.php` — it reported
a false **0.0%**. The worker now collects coverage *inside the child* (gated on a
`COVERAGE_DIR` env) and dumps a per-invocation `.cov`; `bin/merge-coverage.php`
merges every child `.cov` (plus the in-process Unit `unit.cov`) into
`coverage.xml` (Clover) and a text summary, and `bin/run-coverage.sh` orchestrates
the run. Run it with `make coverage` or `composer test:coverage` (both need PCOV
or Xdebug).

With the merge in place the real numbers are:

| File | Line coverage |
|------|---------------|
| `src/Client.php` | **92.32%** (877/950 lines; 91.06% of methods, 112/123) |
| `src/Protocols/Redis.php` | **100%** |
| `src/Exception.php` | **100%** |
| **Total** | **92.99%** (969/1042) |

A Revolt coroutine-mode worker variant (`run-in-worker-coroutine.php`) re-runs the
Feature snippets under a fiber event loop so the coroutine `await` path is merged
into the same numbers.

`bin/merge-coverage.php` enforces a **coverage floor** (`--min` / `COVERAGE_MIN`,
default set in `bin/run-coverage.sh`) and exits non-zero below it; the floor is
currently **90**. This is the canonical gate — CI fails below it.

The residual ~7% is documented as genuinely impractical to cover (socket fault
injection, `onClose` auto-reconnect timing, `onMessage` exception/reconnect,
echo-`Exception` diagnostic sinks, and the coroutine `*ScanAll` error arms) — see
*Coverage close-out (Group 9)* in [`docs/TEST_COVERAGE_PLAN.md`](docs/TEST_COVERAGE_PLAN.md).

### Compatibility

The suite is green on both engines except for documented, server-side divergences
that are skipped on the affected backend with a logged reason (`skipOnBackend`),
never silently. The **Redis leg is skip-free (0 skips)**; the only remaining
divergences are **3 Dragonfly behaviour-gated skips**:

| Skipped on | Tests | Reason |
|------------|-------|--------|
| Dragonfly | auth-with-no-password (2 cases) | `AUTH` against a server with no password configured returns `+OK` on Dragonfly instead of the `-ERR` Redis returns, so the error-path assertion is gated off there. |
| Dragonfly | `OBJECT` subcommand | `OBJECT` (e.g. `OBJECT ENCODING`/`REFCOUNT`) is reported as an unknown command on the Dragonfly build under test. |

The RediSearch **FT family runs in full on both engines** — the earlier
`FT.SEARCH` `SEARCH_INDEX_NOT_FOUND` divergence no longer reproduces on the
current Redis 8.8 + RediSearch build, so those tests were un-gated and now run on
Redis too.

### GitHub Actions

[`.github/workflows/ci.yml`](.github/workflows/ci.yml) runs on every push and
pull request to `master` (and on demand via `workflow_dispatch`):

- **Matrix across PHP 7.2, 7.3, 7.4, 8.1, 8.2, and 8.3 × backend
  `[dragonfly, redis]`** (12 legs, fail-fast disabled), so the full suite runs
  against **both engines** on every push and PR — not just mocks, and not just
  Dragonfly. The 7.x legs prove the advertised `>=7.2` floor: Composer resolves
  **PHPUnit 9 + Workerman 4** there (using `phpunit9.xml.dist`), after the
  install step strips `phpstan/phpstan` + `revolt/event-loop` so the platform
  check can pick the older line; the 8.x legs resolve **PHPUnit 12 + Workerman
  5** (`phpunit.xml.dist`). `composer.lock` is not committed — each leg runs
  `composer update`.
- **Each leg starts exactly one engine** on `127.0.0.1:6379` as a service
  container: the Dragonfly image
  (`docker.dragonflydb.io/dragonflydb/dragonfly`), or
  `redis/redis-stack-server:latest` on the Redis leg so the JSON / Bloom / CMS /
  TopK / FT modules are available there too.
- **PHPUnit** runs on every leg (**PHPStan** on the 8.x legs only); the
  coroutine-mode tests self-skip below PHP 8.1 via the `coroutineSupported()`
  guard and run only on the 8.1/8.2/8.3 legs. The single
  `php=8.3 && backend=dragonfly` leg additionally collects merged line coverage
  with **PCOV** (via `composer test:coverage`) and uploads a Clover report to
  **[Codecov](https://codecov.io/gh/workerman-php/redis)**. The coverage floor gate
  fails the run below the minimum (see *Coverage* above).
- Composer downloads are cached per PHP version to keep runs fast.

Current merged coverage is **92.99%** total (`Client.php` 92.32%,
`Protocols/Redis.php` 100%) — the subprocess-merge fix surfaced the real
end-to-end numbers that pcov in the parent process used to miss. The floor is held
at **90** and ratcheted upward as coverage grows. Coverage badges at the top of
this README reflect the latest `master` run.

## Documentation

http://doc.workerman.net/components/workerman-redis.html
