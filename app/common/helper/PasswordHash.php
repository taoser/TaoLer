<?php

declare(strict_types=1);

namespace app\common\helper;

// 使用示例
// $pwd = 'Test@123456';
// $hash = PasswordHash::make($pwd);

// // 登录校验
// $result = PasswordHash::verify($pwd, $hash);
// if ($result['ok']) {
//     if ($result['update']) {
//         // $result['new_hash'] 更新到数据库
//     }
// }

class PasswordHash
{
    // 目标算法
    private const ALGORITHM = PASSWORD_ARGON2ID;
    private const ARGON_OPTS = [
        'memory_cost' => 65536,
        'time_cost'   => 3,
        'threads'     => 2
    ];

    /**
     * 生成密码哈希
     */
    public static function make(string $plainPassword): string
    {
        return password_hash($plainPassword, self::ALGORITHM, self::ARGON_OPTS);
    }

    /**
     * 校验密码，同时检测是否需要升级哈希
     * @return array{ok:bool,update:bool,new_hash:string|null}
     */
    public static function verify(string $input, string $dbHash): array
    {
        if (!password_verify($input, $dbHash)) {
            return ['ok' => false, 'update' => false, 'new_hash' => null];
        }

        // 检测：bcrypt旧哈希 / argon参数修改 都会触发重生成
        if (password_needs_rehash($dbHash, self::ALGORITHM, self::ARGON_OPTS)) {
            return [
                'ok' => true,
                'update' => true,
                'new_hash' => self::make($input)
            ];
        }
        return ['ok' => true, 'update' => false, 'new_hash' => null];
    }
}