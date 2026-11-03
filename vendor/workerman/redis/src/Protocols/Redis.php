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
namespace Workerman\Redis\Protocols;

use Workerman\Connection\ConnectionInterface;
use Workerman\Redis\Exception;

/**
 * Redis Protocol.
 */
class Redis
{
    /**
     * Return the byte length of the next complete RESP frame in $buffer, or 0 if incomplete.
     *
     * @param string              $buffer
     * @param ConnectionInterface $connection
     * @return int
     */
    public static function input($buffer, ConnectionInterface $connection) {
        $len = self::measure($buffer, 0);
        if ($len <= 0) {
            return 0;
        }
        return $len;
    }

    /**
     * Maximum RESP array nesting accepted by the decoder; guards against stack
     * exhaustion from a malicious or buggy server.
     */
    const MAX_DEPTH = 64;

    /**
     * Return the byte length of the RESP value at $offset, or 0 if the buffer is incomplete.
     *
     * Recurses into nested arrays so multi-bulk replies like SCAN's `[cursor, [keys]]` are
     * sized correctly. The return value is a length relative to $offset, not an absolute
     * end position — callers compute `end = $offset + return`.
     *
     * @param string $buffer
     * @param int    $offset
     * @param int    $depth  Current recursion depth; bounded by MAX_DEPTH.
     * @return int           0 if incomplete; otherwise bytes consumed from $offset.
     */
    protected static function measure($buffer, $offset, $depth = 0)
    {
        if ($depth > self::MAX_DEPTH) {
            // Bail out with the buffer-length sentinel so input() treats the
            // frame as consumed and the decoder produces a protocol error.
            return \strlen($buffer) - $offset;
        }
        if (!isset($buffer[$offset])) {
            return 0;
        }
        $type = $buffer[$offset];
        $eol = \strpos($buffer, "\r\n", $offset);
        if (false === $eol) {
            return 0;
        }
        switch ($type) {
            case ':':
            case '+':
            case '-':
                return $eol + 2 - $offset;
            case '$':
                if (\substr($buffer, $offset, 5) === "$-1\r\n") {
                    return 5;
                }
                $length = (int)\substr($buffer, $offset + 1, $eol - $offset - 1);
                if ($length < 0) {
                    return 0;
                }
                // The header already fixes the frame size. Report it before the
                // body arrives so the connection can reject an oversized bulk
                // instead of buffering the payload first.
                return $eol + 2 + $length + 2 - $offset;
            case '*':
                if (\substr($buffer, $offset, 5) === "*-1\r\n") {
                    return 5;
                }
                $count = (int)\substr($buffer, $offset + 1, $eol - $offset - 1);
                if ($count < 0) {
                    return 0;
                }
                $cursor = $eol + 2;
                while ($count-- > 0) {
                    $childLen = self::measure($buffer, $cursor, $depth + 1);
                    if ($childLen === 0) {
                        return 0;
                    }
                    $cursor += $childLen;
                }
                return $cursor - $offset;
            default:
                return \strlen($buffer) - $offset;
        }
    }


    /**
     * Encode.
     *
     * @param array $data
     * @return string
     */
    public static function encode(array $data)
    {
        $cmd = '';
        $count = \count($data);
        foreach ($data as $item)
        {
            if (\is_array($item)) {
                $count += \count($item) - 1;
                foreach ($item as $str)
                {
                    $cmd .= '$' . \strlen($str) . "\r\n$str\r\n";
                }
                continue;
            }
            $cmd .= '$' . \strlen($item) . "\r\n$item\r\n";
        }
        return "*$count\r\n$cmd";
    }

    /**
     * Decode one RESP reply from $buffer into a `[type, value]` tuple.
     *
     * Supports arbitrary array nesting up to MAX_DEPTH. Returns `['!', <message>]`
     * on protocol error.
     *
     * @param string $buffer
     * @return array
     */
    public static function decode($buffer)
    {
        $offset = 0;
        $result = self::decodeOne($buffer, $offset);
        if ($result === null) {
            return ['!', "protocol error, got '" . ($buffer[0] ?? '') . "' as reply type byte. buffer:" . bin2hex($buffer)];
        }
        return $result;
    }

    /**
     * Decode one RESP value at $offset and advance $offset past the consumed frame.
     *
     * Recurses into arrays so nested replies like SCAN's `[bulk_string, [bulk_string, ...]]`
     * preserve their structure. Returns `null` on incomplete or unrecognised input.
     *
     * @param string $buffer
     * @param int    $offset  Cursor — updated by reference to point past the parsed value.
     * @param int    $depth   Current recursion depth; bounded by MAX_DEPTH.
     * @return array|null     `[type, value]` tuple, or null on protocol error.
     */
    protected static function decodeOne($buffer, &$offset, $depth = 0)
    {
        if ($depth > self::MAX_DEPTH) {
            return ['!', 'protocol error: max array depth exceeded'];
        }
        if (!isset($buffer[$offset])) {
            return null;
        }
        $type = $buffer[$offset];
        $eol = \strpos($buffer, "\r\n", $offset);
        if ($eol === false) {
            return null;
        }
        switch ($type) {
            case ':':
                $value = (int)\substr($buffer, $offset + 1, $eol - $offset - 1);
                $offset = $eol + 2;
                return [$type, $value];
            case '+':
            case '-':
                $value = \substr($buffer, $offset + 1, $eol - $offset - 1);
                $offset = $eol + 2;
                return [$type, $value];
            case '$':
                if (\substr($buffer, $offset, 5) === "$-1\r\n") {
                    $offset += 5;
                    return [$type, null];
                }
                $length = (int)\substr($buffer, $offset + 1, $eol - $offset - 1);
                $value = \substr($buffer, $eol + 2, $length);
                $offset = $eol + 2 + $length + 2;
                return [$type, $value];
            case '*':
                if (\substr($buffer, $offset, 5) === "*-1\r\n") {
                    $offset += 5;
                    return [$type, null];
                }
                $count = (int)\substr($buffer, $offset + 1, $eol - $offset - 1);
                $offset = $eol + 2;
                $value = [];
                while ($count-- > 0) {
                    $child = self::decodeOne($buffer, $offset, $depth + 1);
                    if ($child === null) {
                        return null;
                    }
                    if ($child[0] === '!') {
                        // Propagate the depth-exceeded protocol error upward.
                        return $child;
                    }
                    $value[] = $child[1];
                }
                return [$type, $value];
            default:
                return null;
        }
    }
}
