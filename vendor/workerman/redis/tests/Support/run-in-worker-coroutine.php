<?php
/**
 * Coroutine-mode subprocess runner used by Revolt coroutine-mode tests.
 *
 * This is the sibling of run-in-worker.php, but instead of booting Workerman
 * on its default (Select/Event) event loop it selects the Revolt-backed
 * `Workerman\Events\Fiber` driver. Two things follow from that choice and are
 * the whole point of this variant:
 *
 *   1. The Fiber driver `use`s `Revolt\EventLoop`, so the class is loaded into
 *      memory before any client command runs. `Client::queueCommand()` checks
 *      `class_exists(EventLoop::class, false)` (NO autoload) to decide whether
 *      to run in coroutine mode — under this driver that check is TRUE.
 *   2. With `eventLoopClass === Fiber`, Workerman runs `onWorkerStart` inside a
 *      real PHP \Fiber (see Worker::run() -> Coroutine::create()). That gives
 *      `EventLoop::getSuspension()` a fiber context to suspend, and `onMessage`
 *      (dispatched by the Fiber driver in its own fiber via safeCall) resumes
 *      it. So a command issued WITHOUT a callback suspends the snippet's fiber
 *      and RETURNS its reply synchronously — coroutine mode.
 *
 * The snippet therefore writes straight-line code with synchronous returns:
 *
 *     $redis->set('k', 'v');         // returns 'OK'
 *     $v = $redis->get('k');         // returns 'v'
 *     $emit($v);
 *
 * Result protocol (fd 3, OK/FAIL), per-invocation pid/log files, the timeout
 * timer, and the subprocess coverage dump all mirror run-in-worker.php so the
 * coroutine branches merge into the same coverage report.
 *
 * Wire format on fd 3:
 *   OK <json-value>\n
 *   FAIL <message>\n
 *
 * Timeout: REDIS_TEST_TIMEOUT env var (default 5 seconds).
 */

require __DIR__ . '/../../vendor/autoload.php';

use Workerman\Redis\Client;
use Workerman\Worker;
use Workerman\Timer;
use Workerman\Events\Fiber as FiberEvent;

$snippet = stream_get_contents(STDIN);
if ($snippet === false || $snippet === '') {
    fwrite(STDERR, "no snippet on stdin\n");
    exit(2);
}

$resultFd = fopen('php://fd/3', 'w');
if (!$resultFd) {
    // Fall back to stdout if fd 3 isn't available — parent must filter.
    $resultFd = STDOUT;
}

$redisUrl = getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379';
$timeoutSeconds = (int)(getenv('REDIS_TEST_TIMEOUT') ?: 5);

$uniq = bin2hex(random_bytes(6));
// Scope per-process pid/log files into a dedicated subdir so any residual leak
// is contained and trivially purgeable (bin/run-coverage.sh clears it at start).
$tmpDir = sys_get_temp_dir() . '/wm-redis-tests';
@mkdir($tmpDir, 0777, true);
Worker::$pidFile = "{$tmpDir}/wm-redis-coro-test-{$uniq}.pid";
Worker::$logFile = "{$tmpDir}/wm-redis-coro-test-{$uniq}.log";
Worker::$stdoutFile = '/dev/null';
Worker::$daemonize = false;

// The pid/log files leak otherwise: Worker::runAll() and the $emit/$fail
// handlers exit() (after SIGTERMing the master) before the bottom-of-file
// unlink lines are ever reached. register_shutdown_function runs on exit(), and
// each worker child exit(0)s itself in $emit/$fail, so its own shutdown handler
// fires and removes these files. (Belt-and-suspenders: $emit/$fail also unlink
// inline right before exit; the bottom-of-file unlinks remain as a fallback.)
$cleanupTempFiles = static function () {
    @unlink(Worker::$pidFile);
    @unlink(Worker::$logFile);
};
register_shutdown_function($cleanupTempFiles);

// Select the Revolt-backed Fiber driver. This is what loads Revolt\EventLoop
// (so the client takes its coroutine path) AND makes Workerman run
// onWorkerStart inside a fiber that can be suspended/resumed.
Worker::$eventLoopClass = FiberEvent::class;

$emitted = false;

// ---------------------------------------------------------------------------
// Optional code coverage. Identical mechanism to run-in-worker.php: instrument
// src/ inside THIS forked worker process and dump a unique .cov when
// COVERAGE_DIR is set, so the coroutine branches merge into the same report.
// Every coverage step is wrapped in try/catch so it can never disturb the
// fd-3 result protocol.
// ---------------------------------------------------------------------------
$coverageDir = getenv('COVERAGE_DIR');
$coverageEnabled = is_string($coverageDir) && $coverageDir !== '';
$coverage = null;

$startCoverage = function () use (&$coverage, $coverageEnabled, $uniq) {
    if (!$coverageEnabled) {
        return;
    }
    try {
        require_once __DIR__ . '/coverage-filter.php';
        $filter = new \SebastianBergmann\CodeCoverage\Filter();
        $filter->includeFiles(workerman_redis_src_files());
        $driver = (new \SebastianBergmann\CodeCoverage\Driver\Selector())->forLineCoverage($filter);
        $coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage($driver, $filter);
        $coverage->start('worker-coro-' . $uniq);
    } catch (\Throwable $e) {
        $coverage = null;
        fwrite(STDERR, "coverage start failed: {$e->getMessage()}\n");
    }
};

$dumpCoverage = function () use (&$coverage, $coverageDir, $uniq) {
    if ($coverage === null) {
        return;
    }
    $cov = $coverage;
    $coverage = null;
    try {
        $cov->stop();
        if (!is_dir((string) $coverageDir)) {
            @mkdir((string) $coverageDir, 0777, true);
        }
        $target = rtrim((string) $coverageDir, '/') . "/cov-coro-{$uniq}.cov";
        (new \SebastianBergmann\CodeCoverage\Report\PHP())->process($cov, $target);
    } catch (\Throwable $e) {
        fwrite(STDERR, "coverage dump failed: {$e->getMessage()}\n");
    }
};

$worker = new Worker();
$worker->count = 1;
// Belt-and-suspenders: also pin the driver on the worker instance. Worker::run()
// resolves `$worker->eventLoop ?: static::$eventLoopClass`, so this guarantees
// the Fiber driver even if static state is reset between fork and run.
$worker->eventLoop = FiberEvent::class;
$worker->onWorkerStart = function () use ($snippet, $redisUrl, $timeoutSeconds, $resultFd, &$emitted, $startCoverage, $dumpCoverage, $cleanupTempFiles) {
    // Runs inside a real \Fiber (Coroutine::create) because the event loop is
    // the Fiber driver — so coroutine-mode commands in the snippet can suspend.
    $startCoverage();

    $redis = new Client($redisUrl);

    $emit = function ($value) use (&$emitted, $resultFd, $dumpCoverage, $cleanupTempFiles) {
        if ($emitted) {
            return;
        }
        $emitted = true;
        fwrite($resultFd, 'OK ' . json_encode($value, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) . "\n");
        fflush($resultFd);
        $dumpCoverage();
        $cleanupTempFiles();
        $ppid = posix_getppid();
        if ($ppid > 1) {
            posix_kill($ppid, SIGTERM);
        }
        exit(0);
    };

    $fail = function ($msg) use (&$emitted, $resultFd, $dumpCoverage, $cleanupTempFiles) {
        if ($emitted) {
            return;
        }
        $emitted = true;
        fwrite($resultFd, 'FAIL ' . $msg . "\n");
        fflush($resultFd);
        $dumpCoverage();
        $cleanupTempFiles();
        $ppid = posix_getppid();
        if ($ppid > 1) {
            posix_kill($ppid, SIGTERM);
        }
        exit(0);
    };

    Timer::add($timeoutSeconds, function () use ($fail) {
        $fail('timeout');
    }, [], false);

    try {
        eval($snippet);
    } catch (\Throwable $e) {
        $fail('exception: ' . get_class($e) . ': ' . $e->getMessage());
    }
};

Worker::runAll();

@unlink(Worker::$pidFile);
@unlink(Worker::$logFile);
