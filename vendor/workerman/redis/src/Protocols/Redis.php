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
     * Check the integrity of the package.
     *
     * @param string        $buffer
     * @param ConnectionInterface $connection
     * @return int
     */
    public static function input($buffer, ConnectionInterface $connection) {
        $type = $buffer[0];
        $pos = \strpos($buffer, "\r\n");
        if (false === $pos) {
            return 0;
        }
        switch ($type) {
            case ':':
            case '+':
            case '-':
                return $pos + 2;
            case '$':
                if(0 === strpos($buffer, '$-1')) {
                    return 5;
                }
                return $pos + 4 + (int)substr($buffer, 1, $pos);
            case '*':
                if(0 === strpos($buffer, '*-1')) {
                    return 5;
                }
                $count = (int)substr($buffer, 1, $pos - 1);
                while ($count --) {
                    if (strlen($buffer) < $pos + 2) {
                        return 0;
                    }
                    $next_pos = strpos($buffer, "\r\n", $pos + 2);
                    if (!$next_pos) {
                        return 0;
                    }
                    $sub_type = $buffer[$pos + 2];
                    switch ($sub_type) {
                        case ':':
                        case '+':
                        case '-':
                            $pos = $next_pos;
                            break;
                        case '$':
                            if($pos + 2 === strpos($buffer, '$-1', $pos)) {
                                $pos = $next_pos;
                                break;
                            }
                            $length = (int)substr($buffer, $pos + 3, $next_pos - $pos -3);
                            $pos = $next_pos + $length + 2;
                            if (strlen($buffer) < $pos) {
                                return 0;
                            }
                            break;
                        default:
                            return \strlen($buffer);
                    }
                }
                return $pos + 2;
            default:
                return \strlen($buffer);
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
     * Decode.
     *
     * @param string $buffer
     * @return string
     */
    public static function decode($buffer)
    {
        $type = $buffer[0];
        switch ($type) {
            case ':':
                return [$type ,(int) substr($buffer, 1)];
            case '+':
                return [$type, \substr($buffer, 1, strlen($buffer) - 3)];
            case '-':
                return [$type, \substr($buffer, 1, strlen($buffer) - 3)];
            case '$':
                if(0 === strpos($buffer, '$-1')) {
                    return [$type, null];
                }
                $pos = \strpos($buffer, "\r\n");
                return [$type, \substr($buffer, $pos + 2, (int)substr($buffer, 1, $pos))];
            case '*':
                if(0 === strpos($buffer, '*-1')) {
                    return [$type, null];
                }
                $pos = \strpos($buffer, "\r\n");
                $value = [];
                $count = (int)substr($buffer, 1, $pos - 1);
                while ($count --) {
                    if (strlen($buffer) < $pos + 2) {
                        return 0;
                    }
                    $next_pos = strpos($buffer, "\r\n", $pos + 2);
                    if (!$next_pos) {
                        return 0;
                    }
                    $sub_type = $buffer[$pos + 2];
                    switch ($sub_type) {
                        case ':':
                            $value[] = (int) substr($buffer, $pos + 3, $next_pos - $pos - 3);
                            $pos = $next_pos;
                            break;
                        case '+':
                            $value[] = substr($buffer, $pos + 3, $next_pos - $pos - 3);
                            $pos = $next_pos;
                            break;
                        case '-':
                            $value[] = substr($buffer, $pos + 3, $next_pos - $pos - 3);
                            $pos = $next_pos;
                            break;
                        case '$':
                            if($pos + 2 === strpos($buffer, '$-1', $pos)) {
                                $pos = $next_pos;
                                $value[] = null;
                                break;
                            }
                            $length = (int)substr($buffer, $pos + 3, $next_pos - $pos -3);
                            $value[] = substr($buffer, $next_pos + 2, $length);
                            $pos = $next_pos + $length + 2;
                            break;
                        default:
                            return ['!', "protocol error, got '$sub_type' as reply type byte. buffer:".bin2hex($buffer)." pos:$pos"];
                    }
                }
                return [$type, $value];
            default:
                return ['!', "protocol error, got '$type' as reply type byte. buffer:".bin2hex($buffer)];

        }
    }
}
