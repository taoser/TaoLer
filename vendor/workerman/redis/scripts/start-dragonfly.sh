#!/bin/sh
# Convenience helper: confirm a Dragonfly server is up for the test suite.
#
# This is NOT an installer. Dragonfly is installed via APT on this machine
# (see project memory: reference_dragonfly_install.md). The script is
# idempotent: if something already answers PING on the port it reports
# "already running" and exits 0; otherwise it prints guidance (Dragonfly
# is run as a service, not casually daemonised like redis-server).
#
# Env: DRAGONFLY_PORT (default 6379).
# Prints the connection URL on success.
set -eu

PORT="${DRAGONFLY_PORT:-6379}"
HOST=127.0.0.1
URL="redis://${HOST}:${PORT}"

if redis-cli -h "$HOST" -p "$PORT" ping 2>/dev/null | grep -q PONG; then
    echo "Dragonfly already running on ${HOST}:${PORT}"
    echo "$URL"
    exit 0
fi

echo "Nothing answered PING on ${HOST}:${PORT}." >&2
echo "Dragonfly is APT-installed here; start it via its service, e.g.:" >&2
echo "    sudo systemctl start dragonfly" >&2
echo "  or run the dragonfly binary directly:" >&2
echo "    dragonfly --port ${PORT} --bind ${HOST}" >&2
echo "(see project memory reference_dragonfly_install.md for the APT recipe)" >&2
exit 1
