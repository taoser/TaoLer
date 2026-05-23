<?php
declare(strict_types=1);

namespace app\common\lib;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Exception;

class JwtAuth
{    
    // 访问密钥    
    private static string $key;
    // 签发者 
    private static string $iss;
    // 接收者      
    private static string $aud;
    // 加密算法   
    private static string $alg = 'HS256';
    // 令牌过期时间（秒）
    private static int $expireTime = 86400 * 30;
    // 刷新令牌过期时间（秒）
    private static int $refreshExpireTime = 86400 * 60;

    /**
     * 初始化配置
     */
    public static function init()
    {
        // 从配置文件获取JWT配置
        $jwtConfig = config('jwt');
        
        // 获取配置值，如果配置不存在则使用默认值
        self::$key = $jwtConfig['key'] ?? 'adsfhgkjl1324675809abcdefghijklmnopqrstuvwxyz';
        self::$iss = $jwtConfig['iss'] ?? 'www.aieok.com';
        self::$aud = $jwtConfig['aud'] ?? 'www.aieok.com';
        self::$alg = $jwtConfig['alg'] ?? 'HS256';
        
        // 验证密钥长度
        if (strlen(self::$key) < 32) {
            throw new Exception('JWT secret key must be at least 32 characters long');
        }
    }

    /**
     * 对数据进行编码生成令牌
     * @param array $data 要编码的数据
     * @return string 生成的JWT令牌
     */    
    public static function encode(array $data)
    {
        // 确保初始化
        if (!self::$key) {
            self::init();
        }
        
        $time = time();      
        $payload = [
            "iss"  => self::$iss,
            "aud"  => self::$aud,         
            "iat"  => $time,      
            "nbf"  => $time,        
            "exp"  => $time + self::$expireTime,          
            "jti"  => uniqid(), // JWT ID，用于防止重放攻击
            "data" => $data,  
        ];
        
        try {
            $token = JWT::encode($payload, self::$key, self::$alg);
            return $token;
        } catch (Exception $e) {
            throw new Exception('Failed to generate token: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 对token进行解码验证
     * @param string $token JWT令牌
     * @return object 解码后的数据
     * @throws Exception 验证失败时抛出异常
     */
    public static function verify(string $token)
    {
        // 确保初始化
        if (!self::$key) {
            self::init();
        }
        
        try {
            // 对token进行解码
            $decoded = JWT::decode($token, new Key(self::$key, self::$alg));
            
            // 检测token附加数据中是否存在用户id
            if (empty($decoded->data->uid)) {
                throw new Exception('The token does not contain user information');
            }

            return $decoded->data;
            
        } catch (ExpiredException $e) {
            throw new Exception('Token has expired', 401);
        } catch (BeforeValidException $e) {
            throw new Exception('Token is not yet valid', 401);
        } catch (SignatureInvalidException $e) {
            throw new Exception('Invalid token signature', 401);
        } catch (Exception $e) {
            throw new Exception('Invalid token: ' . $e->getMessage(), 401);
        }
    }

    public static function decode(string $token)
    {
        try {
            // 对token进行解码
            $decoded = JWT::decode($token, new Key(self::$key, self::$alg));
            
            // 检测token附加数据中是否存在用户id
            if (empty($decoded->data->uid)) {
                throw new Exception('The token does not contain user information');
            }

            return $decoded->data;
            
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 401);
        }
    }

    /**
     * 从请求头获取令牌
     * @param array $header 请求头数组
     * @return string 提取的令牌
     * @throws Exception 当请求头中没有有效的Authorization时抛出
     */
    public static function getHeaderToken(array $header)
    {
        if (!isset($header['authorization'])) {
            throw new Exception('Authorization header is required', 401);
        }
        
        $authHeader = $header['authorization'];
        if (!str_starts_with($authHeader, 'Bearer ')) {
            throw new Exception('Invalid authorization header format', 401);
        }
        
        return str_replace('Bearer ', '', $authHeader);
    }

    /**
     * 生成刷新令牌
     * @param array $data 要编码的数据
     * @return string 生成的刷新令牌
     */
    public static function generateRefreshToken(array $data)
    {
        // 确保初始化
        if (!self::$key) {
            self::init();
        }
        
        $time = time();      
        $payload = [
            "iss"  => self::$iss,
            "aud"  => self::$aud,         
            "iat"  => $time,      
            "nbf"  => $time,        
            "exp"  => $time + self::$refreshExpireTime,          
            "jti"  => uniqid(),
            "type" => "refresh", // 标记为刷新令牌
            "data" => $data,  
        ];
        
        try {
            $token = JWT::encode($payload, self::$key, self::$alg);
            return $token;
        } catch (Exception $e) {
            throw new Exception('Failed to generate refresh token: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 使用刷新令牌获取新的访问令牌
     * @param string $refreshToken 刷新令牌
     * @return string 新的访问令牌
     * @throws Exception 刷新失败时抛出异常
     */
    public static function refreshToken(string $refreshToken)
    {
        // 确保初始化
        if (!self::$key) {
            self::init();
        }
        
        try {
            // 解码刷新令牌
            $decoded = JWT::decode($refreshToken, new Key(self::$key, self::$alg));
            
            // 验证是否为刷新令牌
            if (empty($decoded->type) || $decoded->type !== 'refresh') {
                throw new Exception('Invalid refresh token');
            }
            
            // 检测token附加数据中是否存在用户id
            if (empty($decoded->data->uid)) {
                throw new Exception('The refresh token does not contain user information');
            }
            
            // 生成新的访问令牌
            return self::encode((array) $decoded->data);
            
        } catch (Exception $e) {
            throw new Exception('Failed to refresh token: ' . $e->getMessage(), 401);
        }
    }

    /**
     * 设置令牌过期时间
     * @param int $seconds 过期时间（秒）
     */
    public static function setExpireTime(int $seconds)
    {
        self::$expireTime = $seconds;
    }

    /**
     * 设置刷新令牌过期时间
     * @param int $seconds 过期时间（秒）
     */
    public static function setRefreshExpireTime(int $seconds)
    {
        self::$refreshExpireTime = $seconds;
    }

    /**
     * 设置加密算法
     * @param string $alg 算法名称
     */
    public static function setAlgorithm(string $alg)
    {
        self::$alg = $alg;
    }
}