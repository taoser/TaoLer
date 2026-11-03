<?php
/**
 * Subprocess runner used by integration tests.
 *
 * Each integration test invokes this script via proc_open with a small PHP
 * snippet on stdin. The snippet has access to:
 *   - $redis  (Workerman\Redis\Client connected to REDIS_URL env)
 *   - $emit(mixed $value)  callable that writes the value as a JSON line to
 *                          file descriptor 3 (the parent-readable pipe) and
 *                          stops the event loop
 *   - $fail(string $msg)   callable that writes FAIL to fd 3 and stops
 *
 * Why a separate file descriptor (fd 3) instead of stdout? Workerman prints
 * its own start-up banner on stdout when runAll() boots ("start in DEBUG
 * mode", etc.). Mixing that into the result protocol is fragile. The parent
 * proc_open passes an extra pipe at index 3 which the runner reuses as a
 * clean channel for the test result.
 *
 * Per-invocation, the runner also forces unique Worker::$pidFile and
 * Worker::$logFile paths under sys_get_temp_dir() — otherwise repeat runs
 * collide on the cached pid file and exit with "already running".
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
Worker::$pidFile = "{$tmpDir}/wm-redis-test-{$uniq}.pid";
Worker::$logFile = "{$tmpDir}/wm-redis-test-{$uniq}.log";
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

$emitted = false;

// ---------------------------------------------------------------------------
// Optional code coverage. When COVERAGE_DIR is set we instrument the project
// src/ directory *inside this child process* (pcov runs here, not in the parent
// PHPUnit process) and dump a .cov report right before the process exits. The
// parent then merges every cov-<uniq>.cov file.
//
// IMPORTANT: coverage must be STARTED inside onWorkerStart, i.e. inside the
// forked Workerman worker process that actually eval()s the snippet — not here
// at the top level (master). Workerman forks before running onWorkerStart, and
// pcov counters live per-process, so a session started in the master records
// nothing for code that executes in the forked child.
//
// A coverage failure must never disturb the fd-3 result protocol, so every step
// is wrapped in try/catch.
// ---------------------------------------------------------------------------
$coverageDir = getenv('COVERAGE_DIR');
$coverageEnabled = is_string($coverageDir) && $coverageDir !== '';
$coverage = null;

// Start coverage in the current (worker) process. Call once at the top of
// onWorkerStart. Never throws.
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
        $coverage->start('worker-' . $uniq);
    } catch (\Throwable $e) {
        // Coverage is best-effort; carry on without it.
        $coverage = null;
        fwrite(STDERR, "coverage start failed: {$e->getMessage()}\n");
    }
};

// Stop coverage (if active) and write a unique .cov file. Safe to call more than
// once; only the first call does real work. Never throws.
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
        $target = rtrim((string) $coverageDir, '/') . "/cov-{$uniq}.cov";
        (new \SebastianBergmann\CodeCoverage\Report\PHP())->process($cov, $target);
    } catch (\Throwable $e) {
        fwrite(STDERR, "coverage dump failed: {$e->getMessage()}\n");
    }
};

$worker = new Worker();
$worker->count = 1;
$worker->onWorkerStart = function () use ($snippet, $redisUrl, $timeoutSeconds, $resultFd, &$emitted, $startCoverage, $dumpCoverage, $cleanupTempFiles) {
    // Start coverage in THIS (forked) worker process, before any client/src
    // code runs, so pcov records the lines the snippet executes.
    $startCoverage();

    $redis = new Client($redisUrl);

    $emit = function ($value) use (&$emitted, $resultFd, $dumpCoverage, $cleanupTempFiles) {
        if ($emitted) {
            return;
        }
        $emitted = true;
        fwrite($resultFd, 'OK ' . json_encode($value, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) . "\n");
        fflush($resultFd);
        // Dump coverage before tearing down so the .cov is flushed to disk.
        $dumpCoverage();
        // Remove the per-process pid/log files on the normal exit path too.
        $cleanupTempFiles();
        // Signal the master AND exit — stopAll() alone leaves the master
        // monitoring a child it will immediately try to respawn, leading
        // to a tight emit loop. SIGTERM to the parent (master) followed
        // by exit() in the child cleanly tears down both.
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
        $fail('exception: ' . $e->getMessage());
    }
};

Worker::runAll();

// Clean up tempfiles. (Workerman may exit before reaching here, but if it
// returns normally we tidy up.)
@unlink(Worker::$pidFile);
@unlink(Worker::$logFile);
