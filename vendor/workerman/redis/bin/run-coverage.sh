#!/bin/sh
# Orchestrate merged code coverage for workerman/redis.
#
# 1. clear + create build/coverage
# 2. Unit tier in-process  -> build/coverage/unit.cov  (pcov in PHPUnit process)
# 3. Feature tier          -> build/coverage/cov-*.cov (pcov in each worker child,
#                             dumped by tests/Support/run-in-worker.php)
# 4. merge everything      -> coverage.xml + text summary
#
# POSIX sh only. Run from the project root via `composer test:coverage`.

set -eu

ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
COVDIR="$ROOT/build/coverage"

# Coverage floor (Step 0.4). The merge script fails non-zero if the MERGED total
# line coverage drops below this. Ratcheted up as each group landed more tests:
# Group 0 68.62% -> Group 8 82.82% -> Group 9 (close-out) 92.99% total / Client.php
# 92.32%. Floor set to 90 — a few points below the achieved 92.99% to absorb the
# minor subprocess-dump nondeterminism (the merged total can vary by a line or two
# between runs). The residual ~7% is genuinely impractical fault-injection paths
# (socket failures, auto-reconnect timing, coroutine error arms) documented in
# docs/TEST_COVERAGE_PLAN.md "Coverage close-out". Override with COVERAGE_MIN=<pct>.
COVERAGE_MIN="${COVERAGE_MIN:-90}"

# 0. purge any residual per-process pid/log files from prior runs. The worker
#    runners self-clean (register_shutdown_function + in-handler unlink), so this
#    is just a containment sweep in case a child was hard-killed before exit().
rm -rf "${TMPDIR:-/tmp}/wm-redis-tests"

# 1. fresh coverage dir
rm -rf "$COVDIR"
mkdir -p "$COVDIR"

# 2. Unit tier in-process (captured by PHPUnit as a single .cov).
php -d pcov.enabled=1 "$ROOT/vendor/bin/phpunit" \
    --testsuite Unit \
    --coverage-php="$COVDIR/unit.cov"

# 3. Feature tier — children dump their own .cov into COVERAGE_DIR.
COVERAGE_DIR="$COVDIR" php -d pcov.enabled=1 "$ROOT/vendor/bin/phpunit" \
    --testsuite Feature

# 4. Merge + report + enforce the floor (the canonical coverage gate).
COVERAGE_DIR="$COVDIR" COVERAGE_XML="$ROOT/coverage.xml" \
    php "$ROOT/bin/merge-coverage.php" --min="$COVERAGE_MIN"
