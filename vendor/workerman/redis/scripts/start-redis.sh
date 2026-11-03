#!/bin/sh
# Convenience helper: start/confirm a real Redis server for the test suite.
#
# This is NOT an installer. Redis is already installed on this machine.
# The script is idempotent: if something already answers PING on the port
# it reports "already running" and exits 0; otherwise it tries to launch a
# daemonised redis-server on that port.
#
# Module commands (ReJSON / RedisBloom / RediSearch / TimeSeries) must be
# configured separately (loadmodule directives / redis-stack-server); a
# bare `redis-server` started here will NOT have them.
#
# Env: REDIS_PORT (default 63790).
# Prints the connection URL on success.
set -eu

PORT="${REDIS_PORT:-63790}"
HOST=127.0.0.1
URL="redis://${HOST}:${PORT}"

if redis-cli -h "$HOST" -p "$PORT" ping 2>/dev/null | grep -q PONG; then
    echo "Redis already running on ${HOST}:${PORT}"
    echo "$URL"
    exit 0
fi

if ! command -v redis-server >/dev/null 2>&1; then
    echo "redis-server not found on PATH; install Redis (or redis-stack-server for modules)" >&2
    exit 1
fi

echo "Starting redis-server on port ${PORT} (daemonised)..."
echo "Note: modules (ReJSON/RedisBloom/RediSearch/TimeSeries) are NOT loaded by this bare launch; configure them separately."
redis-server --port "$PORT" --daemonize yes

# Give it a moment, then confirm.
i=0
while [ "$i" -lt 20 ]; do
    if redis-cli -h "$HOST" -p "$PORT" ping 2>/dev/null | grep -q PONG; then
        echo "Redis is up on ${HOST}:${PORT}"
        echo "$URL"
        exit 0
    fi
    i=$((i + 1))
    sleep 0.25
done

echo "redis-server did not answer PING on ${PORT} after startup" >&2
exit 1
