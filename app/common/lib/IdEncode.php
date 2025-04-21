<?php
namespace app\common\lib;

use Sqids\Sqids;

class IdEncode
{
    private static $instance;

    private function __construct() {}

    // 单例
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Sqids(config('taoler.id_alphabet'), config('taoler.id_minlength'));
        }
        return self::$instance;
    }

    // ID加密成字符串
    public static function encode(int $id): int|string
    {
        if(config('taoler.config.id_status') === 1) {
            // 若加密开启，返回加密后的string
            return self::getInstance()->encode([$id]);
        }
        // 未加密直接返回数值
        return $id;
    }

    // ID解密
    public static function decode(string|int $idStr): int
    {
        // 若加密开启  && is_string($idStr)
        if(config('taoler.config.id_status') === 1) {
            return self::getInstance()->decode($idStr)[0];
        }

        return (int)$idStr;
    }
}