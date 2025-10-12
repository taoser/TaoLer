<?php

namespace app\common\lib;

class DecryptUtil {
    /**
     * 对密文进行解密
     *
     * @param string $text 密文
     * @param string $sessionKey 从session2Code接口获取到的sessionKey
     * @param string $iv 向量
     * @return string 解密后的明文
     * @throws Exception
     */
    public function decrypt(string $text, string $sessionKey, string $iv): string {
        // Base64解码
        $sessionKey = base64_decode($sessionKey);
        $iv = base64_decode($iv);
        $text = base64_decode($text);
        
        if ($sessionKey === false || $iv === false || $text === false) {
            throw new Exception("Base64解码失败");
        }
        
        // 初始化加密模块
        $decrypted = openssl_decrypt(
            $text,
            'AES-128-CBC',
            $sessionKey,
            OPENSSL_RAW_DATA | OPENSSL_NO_PADDING,
            $iv
        );
        
        if ($decrypted === false) {
            throw new Exception("AES解密失败: " . openssl_error_string());
        }
        
        // PKCS7解码
        $decrypted = $this->pkcs7Decode($decrypted);
        
        // 转换为UTF-8字符串
        return $decrypted;
    }
    
    /**
     * PKCS7解码
     *
     * @param string $data 需要解码的数据
     * @return string 解码后的数据
     */
    private function pkcs7Decode(string $data): string {
        $length = strlen($data);
        if ($length === 0) {
            return $data;
        }
        
        // 获取最后一个字节的值作为填充长度
        $pad = ord($data[$length - 1]);
        
        // 如果填充长度大于数据长度，说明没有填充
        if ($pad > $length) {
            return $data;
        }
        
        // 检查所有填充字节是否都等于填充长度
        for ($i = 0; $i < $pad; $i++) {
            if (ord($data[$length - $pad + $i]) !== $pad) {
                return $data;
            }
        }
        
        // 去除填充
        return substr($data, 0, $length - $pad);
    }
}
?>
