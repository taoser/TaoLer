<?php

/*
|--------------------------------------------------------------------------
| Global test helpers
|--------------------------------------------------------------------------
|
| Free functions shared across the suite. These live in the global namespace
| and are autoloaded via composer's autoload-dev.files so they resolve
| unqualified inside test bodies (e.g. coroutineSupported(), runInWorker()).
|
| Free functions (not RedisTestCase methods) because the project forbids the
| `@var $this` workaround in test bodies on PHP 8.1 / phpstan 2.x.
|
*/

/**
 * The engine the current run targets, lower-cased.
 *
 * Set by the Makefile (`REDIS_BACKEND=dragonfly|redis`). Falls back to
 * 'unknown' when running phpunit directly without the env var.
 *
 * @return string e.g. 'dragonfly', 'redis', or 'unknown'
 */
function currentBackend(): string
{
    $backend = getenv('REDIS_BACKEND');

    return strtolower(($backend === false || $backend === '') ? 'unknown' : $backend);
}

/**
 * Skip the current test when it is running against $backend.
 *
 * Used to gate a single assertion that is a genuine engine divergence
 * (reply shape / format / semantics that differs between Redis and
 * Dragonfly and is NOT a bug in this client). The reason is surfaced in
 * the test output, prefixed with the backend, so skips are never silent.
 *
 * @param string $backend Engine to skip on ('redis' or 'dragonfly').
 * @param string $reason  Specific divergence being avoided.
 */
function skipOnBackend(string $backend, string $reason): void
{
    $backend = strtolower($backend);
    if (currentBackend() === $backend) {
        // Use the static Assert::markTestSkipped() rather than throwing a skip
        // exception class directly: the concrete class differs across PHPUnit
        // majors (SkippedWithMessageException on 10-12, SkippedTestError on 9,
        // which the PHP 7.x legs resolve), but the static helper exists in all
        // of them and throws the version-appropriate exception. A free function
        // (not $this->markTestSkipped()) keeps the skip callable unqualified
        // from any test body. The reason is surfaced, prefixed with the backend.
        \PHPUnit\Framework\Assert::markTestSkipped("[{$backend}] {$reason}");
    }
}

/**
 * Skip the current test unconditionally with a reason.
 *
 * For divergences gated on OBSERVED runtime behaviour rather than the backend
 * name (e.g. "skip if the server accepted AUTH with no password set") — the
 * caller decides when to invoke it. Same cross-version skip mechanism as
 * skipOnBackend(): the static Assert::markTestSkipped() throws the
 * PHPUnit-major-appropriate skip exception (9 vs 10-12).
 *
 * @param string $reason Why this case is being skipped; surfaced in output.
 */
function skipTest(string $reason): void
{
    \PHPUnit\Framework\Assert::markTestSkipped($reason);
}

/*
|--------------------------------------------------------------------------
| runInWorker helper
|--------------------------------------------------------------------------
|
| Executes $snippet inside a child PHP process with the Workerman runtime
| initialised. The snippet runs in a scope where $redis is a connected
| Workerman\Redis\Client, and $emit($value) / $fail($msg) report back
| to the parent via an out-of-band pipe (fd 3) so they don't collide
| with Workerman's startup banner on stdout.
|
| Exposed as a free function instead of a method on RedisTestCase so
| PHPStan doesn't have to reason about $this inside test bodies — on
| PHP 8.1 / phpstan 2.x the `@var $this` workaround produces "Variable
| $this in PHPDoc tag @var does not match assigned variable $result"
| errors. Free function side-steps that entirely.
|
| @param  string  $snippet  PHP code (no <?php tag) executed inside the
|                           subprocess after the worker boots. Has
|                           $redis, $emit, $fail in scope.
| @param  int     $timeout  Seconds before the child self-aborts.
| @return mixed             The value passed to $emit() — anything
|                           json_encode()-able.
*/

function runInWorker(string $snippet, int $timeout = 5)
{
    return runInWorkerScript(__DIR__ . '/Support/run-in-worker.php', $snippet, $timeout);
}

/**
 * Coroutine-mode sibling of runInWorker().
 *
 * Runs $snippet inside a child that boots Workerman on the Revolt-backed
 * `Workerman\Events\Fiber` driver (see Support/run-in-worker-coroutine.php).
 * Under that driver `Revolt\EventLoop` is loaded and onWorkerStart runs inside
 * a fiber, so commands the snippet issues WITHOUT a callback suspend the fiber
 * and RETURN their reply synchronously:
 *
 *     $redis->set('k', 'v');     // 'OK'
 *     $emit($redis->get('k'));   // emits 'v'
 *
 * Same fd-3 OK/FAIL protocol, coverage forwarding and env handling as
 * runInWorker(); a free function for the same no-`@var $this`-in-closures
 * reason documented on runInWorker().
 *
 * @param  string $snippet PHP code (no <?php) run after the worker boots, in a
 *                         fiber, with $redis/$emit/$fail in scope.
 * @param  int    $timeout Seconds before the child self-aborts.
 * @return mixed           The value passed to $emit().
 */
function runInCoroutineWorker(string $snippet, int $timeout = 5)
{
    return runInWorkerScript(__DIR__ . '/Support/run-in-worker-coroutine.php', $snippet, $timeout);
}

/**
 * Shared subprocess driver for runInWorker()/runInCoroutineWorker().
 *
 * Spawns $scriptPath via proc_open, feeds $snippet on stdin, forwards the
 * REDIS_URL/REDIS_BACKEND env and (when set) the COVERAGE_DIR + pcov ini so the
 * child can dump its own .cov, then reads the OK/FAIL result line off fd 3.
 *
 * @param  string $scriptPath Absolute path to the runner script.
 * @param  string $snippet    PHP code executed inside the subprocess.
 * @param  int    $timeout    Seconds before the child self-aborts.
 * @return mixed              The decoded value emitted by the child.
 */
function runInWorkerScript(string $scriptPath, string $snippet, int $timeout = 5)
{
    $runner = realpath($scriptPath);
    $env = [
        'REDIS_URL' => getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379',
        'REDIS_BACKEND' => getenv('REDIS_BACKEND') ?: '',
        'REDIS_TEST_TIMEOUT' => (string) $timeout,
        'PATH' => getenv('PATH'),
    ];

    // When COVERAGE_DIR is set (the merged-coverage pipeline, see
    // bin/run-coverage.sh) the child must collect its own coverage. pcov only
    // records when pcov.enabled=1, and that flag is PHP_INI_SYSTEM — it cannot
    // be turned on with ini_set() inside the child, so it has to be passed on
    // the command line here. We also forward COVERAGE_DIR so run-in-worker.php
    // knows where to dump its cov-<uniq>.cov file.
    $coverageDir = getenv('COVERAGE_DIR');
    $cmd = [PHP_BINARY];
    if (is_string($coverageDir) && $coverageDir !== '') {
        $env['COVERAGE_DIR'] = $coverageDir;
        $cmd[] = '-d';
        $cmd[] = 'pcov.enabled=1';
        $cmd[] = '-d';
        $cmd[] = 'pcov.directory=' . dirname(__DIR__) . '/src';
    }
    $cmd[] = $runner;
    $cmd[] = 'start';

    // proc_open() accepts an ARRAY command only on PHP 7.4+; on 7.2/7.3 the first
    // argument must be a string. Build a shell-escaped string so the subprocess
    // runner works on every supported PHP version.
    $cmdString = implode(' ', array_map('escapeshellarg', $cmd));

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
        3 => ['pipe', 'w'],
    ];
    $proc = proc_open($cmdString, $descriptors, $pipes, null, $env);
    if (!\is_resource($proc)) {
        throw new \RuntimeException('Could not spawn run-in-worker child');
    }
    fwrite($pipes[0], $snippet);
    fclose($pipes[0]);
    $resultLine = stream_get_contents($pipes[3]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    fclose($pipes[3]);
    $exitCode = proc_close($proc);

    $firstLine = strtok((string) $resultLine, "\n");
    if ($firstLine === false) {
        throw new \RuntimeException("Child produced no result. exit={$exitCode} stdout={$stdout} stderr={$stderr}");
    }
    if (str_starts_with($firstLine, 'FAIL ')) {
        throw new \RuntimeException('Worker child reported: ' . substr($firstLine, 5) . " stderr={$stderr}");
    }
    if (!str_starts_with($firstLine, 'OK ')) {
        throw new \RuntimeException("Unexpected child result: {$firstLine} stderr={$stderr}");
    }
    return json_decode(substr($firstLine, 3), true);
}

/**
 * Whether the coroutine (Revolt-backed) test legs can run.
 *
 * Called unqualified inside test bodies, so it must be a global function.
 *
 * @return bool True on PHP >= 8.1 with revolt/event-loop installed.
 */
function coroutineSupported(): bool
{
    return PHP_VERSION_ID >= 80100 && class_exists(\Revolt\EventLoop::class);
}

/*
|--------------------------------------------------------------------------
| PHP 7 polyfills
|--------------------------------------------------------------------------
|
| str_starts_with()/str_contains()/str_ends_with() are PHP 8.0+. The library
| supports PHP >= 7.2, so the test suite (which uses them) needs these on the
| 7.x CI legs. Guarded so they are no-ops on PHP 8+.
|
*/

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

if (!function_exists('array_key_first')) {
    /**
     * Polyfill for array_key_first() (PHP 7.3+) so the suite runs on PHP 7.2.
     * @param array<mixed> $arr
     * @return mixed first key, or null if empty
     */
    function array_key_first(array $arr)
    {
        foreach ($arr as $k => $_) {
            return $k;
        }
        return null;
    }
}
