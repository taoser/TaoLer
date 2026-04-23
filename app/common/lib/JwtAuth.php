<?php
declare(strict_types=1);

namespace app\common\lib;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtAuth
{    

    /**  对数据进行编码  
     *   @param array $data
     */    
    
    public static function encode(array $data)
    {
        $time = time();      
        $payload = [
            "iss"  => config('jwt.iss'),
            "aud"  => config('jwt.aud'),         
            "iat"  => $time,      
            "nbf"  => $time,        
            'exp'  => $time + 86400 * 30,          
            'data' => $data,  
        ];
        $token = JWT::encode($payload, config('jwt.key'), config('jwt.alg'));
        return $token;
    }
    
    /**
     *
     *  对 token 进行编码验证
     *  @param string  $token
     *  @param integer $user_id
     */
    public static function decode(string $token)
    {
        try {
            // 对 token 进行编码
            $decoded = JWT::decode($token, new Key(config('jwt.key'), config('jwt.alg')));
            // 检测 token 附加数据中是否存在用户id
            if (empty($decoded->data->uid)) {
                throw new Exception('The token does not contain user information');
            }

            return $decoded->data;
            
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 201);
        }
    }

    public static function getHeaderToken(array $header)
    {
        return str_replace('Bearer ', '', $header['authorization']);
    }
}