<?php

/**
 * Merge per-worker coverage dumps into a single report.
 *
 * The Feature tier runs each assertion inside a proc_open child process
 * (tests/Support/run-in-worker.php). pcov instruments the *child*, so each
 * child writes a SebastianBergmann\CodeCoverage\CodeCoverage object to
 * COVERAGE_DIR/cov-<uniq>.cov via the PHP report. The Unit tier runs in-process
 * and is captured separately by PHPUnit as a single --coverage-php file.
 *
 * This script merges every *.cov file in the coverage dir (plus an optional
 * extra in-process .cov) into one CodeCoverage, then emits a Clover report and a
 * text summary so the real src/Client.php number shows up.
 *
 * Usage:
 *   php bin/merge-coverage.php [coverage-dir] [clover-out] [--min=<pct>]
 *
 * Environment (override the positional args):
 *   COVERAGE_DIR   directory of *.cov dumps        (default: build/coverage)
 *   COVERAGE_UNIT  extra in-process .cov to merge  (default: <dir>/unit.cov if present)
 *   COVERAGE_XML   clover output path              (default: coverage.xml)
 *   COVERAGE_HTML  optional HTML report directory  (default: none)
 *   COVERAGE_TEXT  optional text report file       (default: stdout only)
 *   COVERAGE_MIN   minimum total line coverage %   (default: none; --min wins)
 *
 * Coverage floor (Step 0.4): when a minimum is supplied via the `--min=<pct>`
 * flag (takes precedence) or the COVERAGE_MIN env, the script computes the
 * merged total line coverage % and exits NON-ZERO (code 3) if it is below the
 * floor, printing both the achieved % and the floor. This is the canonical gate
 * for both local `make coverage` and CI — it runs on the MERGED number, which is
 * the only meaningful figure given the subprocess coverage model (a plain
 * `phpunit --min` can't see Client.php; see Step 0.1).
 *
 * Exits non-zero (loudly) when no *.cov files are found (code 2) or coverage is
 * below the floor (code 3).
 */

declare(strict_types=1);

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "autoload not found at {$autoload}\n");
    exit(1);
}
require $autoload;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;
use SebastianBergmann\CodeCoverage\Report\Text as TextReport;
use SebastianBergmann\CodeCoverage\Report\Thresholds;

$root = dirname(__DIR__);

// Separate option flags (--min=<pct>) from positional args so the positional
// [coverage-dir] [clover-out] order is preserved regardless of flag placement.
$minOpt = null;
$positional = [];
foreach (array_slice($argv, 1) as $arg) {
    if (strncmp($arg, '--min=', 6) === 0) {
        $minOpt = substr($arg, 6);
    } else {
        $positional[] = $arg;
    }
}

$envDir = getenv('COVERAGE_DIR');
$coverageDir = $positional[0] ?? (is_string($envDir) && $envDir !== '' ? $envDir : $root . '/build/coverage');

$envXml = getenv('COVERAGE_XML');
$cloverOut = $positional[1] ?? (is_string($envXml) && $envXml !== '' ? $envXml : $root . '/coverage.xml');

// Coverage floor: --min=<pct> takes precedence over the COVERAGE_MIN env.
$envMin = getenv('COVERAGE_MIN');
$minRaw = $minOpt ?? (is_string($envMin) && $envMin !== '' ? $envMin : null);
$minPct = ($minRaw !== null && is_numeric($minRaw)) ? (float) $minRaw : null;
if ($minRaw !== null && $minPct === null) {
    fwrite(STDERR, "invalid --min/COVERAGE_MIN value: {$minRaw}\n");
    exit(1);
}

$envUnit = getenv('COVERAGE_UNIT');
$unitCov = is_string($envUnit) && $envUnit !== '' ? $envUnit : null;

$htmlDir = getenv('COVERAGE_HTML');
$htmlDir = is_string($htmlDir) && $htmlDir !== '' ? $htmlDir : null;

$textOut = getenv('COVERAGE_TEXT');
$textOut = is_string($textOut) && $textOut !== '' ? $textOut : null;

if (!is_dir($coverageDir)) {
    fwrite(STDERR, "coverage dir not found: {$coverageDir}\n");
    exit(1);
}

// Build the merge target with the same src/ filter the children used.
require_once $root . '/tests/Support/coverage-filter.php';
$filter = new Filter();
$filter->includeFiles(workerman_redis_src_files());
$driver = (new Selector())->forLineCoverage($filter);
$merged = new CodeCoverage($driver, $filter);

$files = glob(rtrim($coverageDir, '/') . '/*.cov') ?: [];

// Default the unit dump to <dir>/unit.cov if it exists and was not given.
if ($unitCov === null) {
    $maybeUnit = rtrim($coverageDir, '/') . '/unit.cov';
    if (is_file($maybeUnit)) {
        $unitCov = $maybeUnit;
    }
}

$mergedCount = 0;
foreach ($files as $file) {
    try {
        /** @var CodeCoverage $partial */
        $partial = include $file;
        if ($partial instanceof CodeCoverage) {
            $merged->merge($partial);
            $mergedCount++;
        } else {
            fwrite(STDERR, "skipping {$file}: not a CodeCoverage object\n");
        }
    } catch (\Throwable $e) {
        fwrite(STDERR, "failed to merge {$file}: {$e->getMessage()}\n");
    }
}

// Merge the in-process unit .cov if it was passed explicitly and not already in
// the glob above (it lives in the same dir, so it usually already is).
if ($unitCov !== null && is_file($unitCov) && !in_array($unitCov, $files, true)) {
    try {
        /** @var CodeCoverage $partial */
        $partial = include $unitCov;
        if ($partial instanceof CodeCoverage) {
            $merged->merge($partial);
            $mergedCount++;
        }
    } catch (\Throwable $e) {
        fwrite(STDERR, "failed to merge unit cov {$unitCov}: {$e->getMessage()}\n");
    }
}

if ($mergedCount === 0) {
    fwrite(STDERR, "no .cov files merged from {$coverageDir} — coverage pipeline is broken\n");
    exit(2);
}

// Clover report (consumed by CI / the future --min floor in Step 0.4).
(new Clover())->process($merged, $cloverOut);

// Optional HTML report.
if ($htmlDir !== null) {
    (new HtmlReport())->process($merged, $htmlDir);
}

// Text summary — printed to stdout (and optionally a file).
$text = (new TextReport(Thresholds::default(), false, false))->process($merged);
echo $text;
if ($textOut !== null) {
    file_put_contents($textOut, $text);
}

fwrite(STDERR, "merged {$mergedCount} coverage file(s) -> {$cloverOut}\n");

// Coverage floor gate (Step 0.4). Compute merged total line coverage from the
// report's own counters so the gated number matches the clover/text summary.
if ($minPct !== null) {
    $report = $merged->getReport();
    $executable = $report->numberOfExecutableLines();
    $executed = $report->numberOfExecutedLines();
    $achieved = $executable > 0 ? ($executed / $executable) * 100.0 : 0.0;

    $achievedStr = number_format($achieved, 2);
    $floorStr = number_format($minPct, 2);

    if ($achieved + 1e-9 < $minPct) {
        fwrite(
            STDERR,
            "COVERAGE FLOOR FAILED: total line coverage {$achievedStr}% "
            . "({$executed}/{$executable}) is below the floor of {$floorStr}%\n"
        );
        exit(3);
    }

    fwrite(
        STDERR,
        "coverage floor OK: total line coverage {$achievedStr}% "
        . "({$executed}/{$executable}) >= floor {$floorStr}%\n"
    );
}

exit(0);
