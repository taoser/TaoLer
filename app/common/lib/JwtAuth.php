<?php
declare(strict_types=1);

namespace app\common\lib;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuth
{    
    // 访问密钥    
    const KEY = 'adsfhgkjl1324675809';
    // 签发者 
    const ISS = 'www.aieok.com';
    // 接收者      
    const AUD = 'www.aieok.com';    
    // 加密算法   
    const ALG = 'HS256';

    /**  对数据进行编码  
     *   @param array $data
     */    
    
    public static function encode(array $data)
    {
        $time = time();      
        $payload = [
            "iss"  => self::ISS,
            "aud"  => self::AUD,         
            "iat"  => $time,      
            "nbf"  => $time,        
            'exp'  => $time + 86400 * 30,          
            'data' => $data,  
        ];
        $token = JWT::encode($payload, self::KEY, self::ALG);
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
            $decoded = JWT::decode($token, new Key(self::KEY, self::ALG));
            // 检测 token 附加数据中是否存在用户id
            if (!empty($decoded->data->uid)) {
                $data =  $decoded->data;
            } else {
                throw new \Exception('token 中没有用户信息');
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 201);
        }
        return $data; // 用户信息
    }

    public static function getHeaderToken(array $header)
    {
        return str_replace('Bearer ', '', $header['authorization']);
    }
}