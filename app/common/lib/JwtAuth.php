<?php
declare(strict_types=1);

namespace app\common\lib;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Config;

class JwtAuth
{    
    // 访问密钥    
    protected static $key;
    //const KEY = 'adsfhgkjl1324675809';
    // 签发者 
    protected static $iss;
    //const ISS = 'www.aieok.com';
    // 接收者 
    protected static $aud;     
    //const AUD = 'www.aieok.com';    
    // 加密算法
    protected static $alg;
    //const ALG = 'HS256';

    // 初始化
    public static function init()
    {
        self::$key = Config::get('jwt.key');
        self::$iss = Config::get('jwt.iss');
        self::$aud = Config::get('jwt.aud');
        self::$alg = Config::get('jwt.alg');
    }

    /**  对数据进行编码  
     *   @param array $data
     */    
    
    public static function encode(array $data): string
    {
        self::init();

        $time = time();      
        $payload = [
            "iss"  => self::$iss,
            "aud"  => self::$aud,         
            "iat"  => $time,      
            "nbf"  => $time,        
            'exp'  => $time + 86400 * 30,
            'data' => $data,  
        ];

        $token = JWT::encode($payload, self::$key, self::$alg);
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
        self::init();

        try {
            // 对 token 进行编码
            $decoded = JWT::decode($token, new Key(self::$key, self::$alg));
            $data = $decoded->data;
        } catch(\Firebase\JWT\SignatureInvalidException $e) {  // 签名不正确
            throw new Exception($e->getMessage(), 604);
        } catch(\Firebase\JWT\BeforeValidException $e) {  // 签名在某个时间点之后才能用
            throw new Exception($e->getMessage(), 603);
        } catch(\Firebase\JWT\ExpiredException $e) {  // token过期
            throw new Exception($e->getMessage(), 602);
        } catch(Exception $e) {  // 其他错误
            throw new Exception($e->getMessage(), 601);
        }

        return $data; // 用户信息
    }

    public static function getHeaderToken(array $header)
    {
        return str_replace('Bearer ', '', $header['authorization']);
    }
}