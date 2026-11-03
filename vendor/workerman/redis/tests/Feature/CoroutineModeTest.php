<?php

/*
|--------------------------------------------------------------------------
| Revolt coroutine mode (Group 8 §8.1 / plan §3D dual-mode gap)
|--------------------------------------------------------------------------
|
| Every other Feature test exercises CALLBACK mode: it runs in
| tests/Support/run-in-worker.php where Revolt is never loaded, so
| Client::queueCommand()'s `class_exists(EventLoop::class, false)` check is
| false and commands are fire-and-callback.
|
| These tests exercise the second, previously-untested mode. They run in
| tests/Support/run-in-worker-coroutine.php, which boots Workerman on the
| Revolt-backed `Workerman\Events\Fiber` driver. Under that driver:
|   - `Revolt\EventLoop` is loaded (the Fiber driver `use`s it), so the
|     `class_exists(..., false)` coroutine check is TRUE; and
|   - onWorkerStart runs inside a real \Fiber, so a command issued WITHOUT a
|     callback suspends that fiber via EventLoop::getSuspension() and is
|     resumed by onMessage when the reply lands — i.e. the call RETURNS its
|     reply synchronously.
|
| This covers: queueCommand()'s `$need_suspend` branch + suspenstion() +
| the onMessage resume, the four *ScanAll coroutine loops, and the
| subscribe/monitor-lock guard.
|
| Coroutine mode is a CLIENT-side concern (suspend/resume the calling
| fiber), engine-independent, so these run identically on Dragonfly and
| Redis via the Makefile targets.
|
| Keys use a pest:g8:coro: prefix to avoid colliding with workers that
| share db0.
*/

final class CoroutineModeTest extends \Tests\RedisTestCase
{
    public function test_returns_set_get_incr_del_replies_synchronously_with_no_callback(): void
    {
        if (!coroutineSupported()) {
            $this->markTestSkipped('coroutine mode requires PHP 8.1+ and Revolt');
        }

        $result = runInCoroutineWorker(<<<'PHP'
            // No callbacks anywhere: each call suspends the fiber and returns its
            // reply directly. Straight-line, synchronous-looking code.
            $del0   = $redis->del('pest:g8:coro:1:k', 'pest:g8:coro:1:n');
            $set    = $redis->set('pest:g8:coro:1:k', 'hello');
            $get    = $redis->get('pest:g8:coro:1:k');
            $incr1  = $redis->incr('pest:g8:coro:1:n');
            $incr2  = $redis->incr('pest:g8:coro:1:n');
            $delK   = $redis->del('pest:g8:coro:1:k');
            $getAft = $redis->get('pest:g8:coro:1:k');
            $emit([
                'set'        => $set,
                'get'        => $get,
                'incr1'      => $incr1,
                'incr2'      => $incr2,
                'del'        => $delK,
                'get_after'  => $getAft,
            ]);
PHP
        );

        // SET's "+OK" status reply is normalised to boolean true by the client.
        $this->assertTrue($result['set']);
        $this->assertSame('hello', $result['get']);
        $this->assertSame(1, $result['incr1']);
        $this->assertSame(2, $result['incr2']);
        $this->assertSame(1, $result['del']);
        // Deleted key reads back as null (callback-less coroutine return).
        $this->assertNull($result['get_after']);
    }

    public function test_scanall_returns_the_full_key_array_synchronously_in_coroutine_mode(): void
    {
        if (!coroutineSupported()) {
            $this->markTestSkipped('coroutine mode requires PHP 8.1+ and Revolt');
        }

        $result = runInCoroutineWorker(<<<'PHP'
            // Seed three keys, then aggregate them through the coroutine-mode
            // scanAll loop (no callback -> synchronous cursor walk + return).
            $redis->del('pest:g8:coro:2:a', 'pest:g8:coro:2:b', 'pest:g8:coro:2:c');
            $redis->set('pest:g8:coro:2:a', '1');
            $redis->set('pest:g8:coro:2:b', '1');
            $redis->set('pest:g8:coro:2:c', '1');
            $keys = $redis->scanAll(['match' => 'pest:g8:coro:2:*', 'count' => 100]);
            sort($keys);
            $emit($keys);
PHP
        );

        $this->assertSame([
            'pest:g8:coro:2:a',
            'pest:g8:coro:2:b',
            'pest:g8:coro:2:c',
        ], $result);
    }

    public function test_hscanall_sscanall_zscanall_aggregate_synchronously_in_coroutine_mode(): void
    {
        if (!coroutineSupported()) {
            $this->markTestSkipped('coroutine mode requires PHP 8.1+ and Revolt');
        }

        $result = runInCoroutineWorker(<<<'PHP'
            $redis->del('pest:g8:coro:3:h', 'pest:g8:coro:3:s', 'pest:g8:coro:3:z');
            $redis->hSet('pest:g8:coro:3:h', 'f1', 'v1');
            $redis->hSet('pest:g8:coro:3:h', 'f2', 'v2');
            $redis->sAdd('pest:g8:coro:3:s', 'm1', 'm2');
            $redis->zAdd('pest:g8:coro:3:z', 1, 'z1', 2, 'z2');

            $h = $redis->hScanAll('pest:g8:coro:3:h', ['count' => 100]);
            ksort($h);
            $s = $redis->sScanAll('pest:g8:coro:3:s', ['count' => 100]);
            sort($s);
            $z = $redis->zScanAll('pest:g8:coro:3:z', ['count' => 100]);
            ksort($z);

            $emit(['h' => $h, 's' => $s, 'z' => $z]);
PHP
        );

        $this->assertSame(['f1' => 'v1', 'f2' => 'v2'], $result['h']);
        $this->assertSame(['m1', 'm2'], $result['s']);
        // zScanAll keeps score strings keyed by member.
        $this->assertSame(['z1' => '1', 'z2' => '2'], $result['z']);
    }

    public function test_throws_when_a_coroutine_mode_command_is_issued_while_subscribe_locked(): void
    {
        if (!coroutineSupported()) {
            $this->markTestSkipped('coroutine mode requires PHP 8.1+ and Revolt');
        }

        $result = runInCoroutineWorker(<<<'PHP'
            // Establish the connection first (a coroutine ping suspends until
            // connected) so SUBSCRIBE is actually sent and the _subscribe lock is
            // set synchronously by process(). Then a callback-less ordinary command
            // must throw rather than suspend into an unresumable fiber.
            $redis->ping();
            $redis->subscribe('pest:g8:coro:4:chan', function () {});
            try {
                $redis->get('pest:g8:coro:4:x');
                $emit(['threw' => false, 'message' => null]);
            } catch (\Throwable $e) {
                $emit(['threw' => true, 'class' => get_class($e), 'message' => $e->getMessage()]);
            }
PHP
        );

        $this->assertTrue($result['threw']);
        $this->assertSame(\Workerman\Redis\Exception::class, $result['class']);
        $this->assertStringContainsString('subscribe/monitor mode', $result['message']);
    }

    public function test_select_auth_and_quit_return_their_replies_in_coroutine_mode(): void
    {
        if (!coroutineSupported()) {
            $this->markTestSkipped('coroutine mode requires PHP 8.1+ and Revolt');
        }

        $result = runInCoroutineWorker(<<<'PHP'
            $auth = $redis->auth('not-the-password');
            $select = $redis->select(1);
            $ref = new \ReflectionClass($redis);
            $dbProp = $ref->getProperty('_db');
            $dbProp->setAccessible(true);
            $db = $dbProp->getValue($redis);
            $set = $redis->set('pest:g8:coro:db', 'one');
            $get = $redis->get('pest:g8:coro:db');
            $redis->del('pest:g8:coro:db');
            $back = $redis->select(0);
            $missing = $redis->get('pest:g8:coro:db');
            $quit = $redis->quit();
            $emit([
                'auth' => $auth,
                'select' => $select,
                'db' => $db,
                'set' => $set,
                'get' => $get,
                'back' => $back,
                'missing' => $missing,
                'quit' => $quit,
            ]);
PHP
        );

        // No password is configured in CI. Redis rejects AUTH and the reply is
        // false; Dragonfly accepts it and the reply is true. Either value shows
        // the coroutine path returned the reply instead of null.
        $this->assertIsBool($result['auth']);
        $this->assertTrue($result['select']);
        $this->assertSame(1, $result['db']);
        $this->assertTrue($result['set']);
        $this->assertSame('one', $result['get']);
        $this->assertTrue($result['back']);
        $this->assertNull($result['missing']);
        $this->assertTrue($result['quit']);
    }
}
