<?php
namespace app\common\helper;
class StrHelper
{
    /**
     * 随机字符串
     * @param int $length 长度
     * @return string
     */
    public static function randomString(int $length = 16): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

        /**
     * 通用字符串脱敏，中间替换为*
     * @param string $str 原始字符串
     * @param int $prefix 前面保留位数
     * @param int $suffix 后面保留位数
     * @return string
     */
    public static function maskString(string $str, int $prefix = 1, int $suffix = 1): string
    {
        $len = mb_strlen($str);
        // 长度不足直接原样返回
        if ($len <= $prefix + $suffix) {
            return $str;
        }
        $front = mb_substr($str, 0, $prefix);
        $back = mb_substr($str, $len - $suffix, $suffix);
        $star = str_repeat('*', $len - $prefix - $suffix);
        return $front . $star . $back;
    }

    /**
     * 手机号脱敏 138****1234
     * @param string $mobile
     * @return string
     */
    public static function maskMobile(string $mobile): string
    {
        if (!preg_match('/^1[3-9]\d{10}$/', $mobile)) {
            return $mobile;
        }
        return self::maskString($mobile, 3, 4);
    }

    /**
     * 身份证脱敏 430***********1234
     * @param string $idCard
     * @return string
     */
    public static function maskIdCard(string $idCard): string
    {
        $len = strlen($idCard);
        if ($len !== 15 && $len !== 18) {
            return $idCard;
        }
        return self::maskString($idCard, 4, 4);
    }

    /**
     * 用户名/昵称脱敏，只留首尾1位 张*三
     * @param string $name
     * @return string
     */
    public static function maskName(string $name): string
    {
        return self::maskString($name, 1, 1);
    }

    /**
     * 邮箱脱敏 x***@shturl.
     * @param string $email
     * @return string
     */
    public static function maskEmail(string $email): string
    {
        $atPos = strpos($email, '@');
        if ($atPos === false) {
            return $email;
        }
        $username = substr($email, 0, $atPos);
        $domain = substr($email, $atPos);
        $maskUser = self::maskString($username, 1, 0);
        return $maskUser . $domain;
    }
}