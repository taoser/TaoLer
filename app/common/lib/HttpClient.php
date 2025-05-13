<?php

namespace app\common\lib;
/**
 * HTTP请求工具类，封装了cURL的常用操作（PHP 8优化版）
 */
class HttpClient {
    
    public function __construct(
        private array $headers = [],
        private int $timeout = 30,
        private int $connectTimeout = 10,
        private bool $verifySSL = true,
        private ?string $proxy = null
    ) {}

    /**
     * 设置请求头
     * @param array $headers 请求头数组
     * @return $this
     */
    public function setHeaders(array $headers): static {
        $this->headers = $headers;
        return $this;
    }

    /**
     * 设置超时时间
     * @param int $timeout 超时时间（秒）
     * @return $this
     */
    public function setTimeout(int $timeout): static {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * 设置连接超时时间
     * @param int $connectTimeout 连接超时时间（秒）
     * @return $this
     */
    public function setConnectTimeout(int $connectTimeout): static {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    /**
     * 设置是否验证SSL证书
     * @param bool $verifySSL 是否验证SSL证书
     * @return $this
     */
    public function setVerifySSL(bool $verifySSL): static {
        $this->verifySSL = $verifySSL;
        return $this;
    }

    /**
     * 设置代理服务器
     * @param string|null $proxy 代理服务器地址，格式：host:port
     * @return $this
     */
    public function setProxy(?string $proxy): static {
        $this->proxy = $proxy;
        return $this;
    }

    /**
     * 发送GET请求
     * @param string $url 请求URL
     * @param array $params 请求参数
     * @return array 返回结果，包含响应头和响应体
     */
    public function get(string $url, array $params = []): array {
        if (!empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }
        return $this->sendRequest($url, 'GET');
    }

    /**
     * 发送POST请求
     * @param string $url 请求URL
     * @param array|string $data 请求数据
     * @param array $headers 额外的请求头
     * @return array 返回结果，包含响应头和响应体
     */
    public function post(string $url, array|string $data = [], array $headers = []): array {
        return $this->sendRequest($url, 'POST', $data, $headers);
    }

    /**
     * 发送PUT请求
     * @param string $url 请求URL
     * @param array|string $data 请求数据
     * @return array 返回结果，包含响应头和响应体
     */
    public function put(string $url, array|string $data = []): array {
        return $this->sendRequest($url, 'PUT', $data);
    }

    /**
     * 发送DELETE请求
     * @param string $url 请求URL
     * @param array $params 请求参数
     * @return array 返回结果，包含响应头和响应体
     */
    public function delete(string $url, array $params = []): array {
        if (!empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }
        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * 下载远程文件到本地
     * @param string $url 远程文件URL
     * @param string $destination 本地保存路径
     * @param callable|null $progressCallback 进度回调函数
     * @return bool|string 成功时返回本地文件路径，失败时返回false
     */
    public function download(string $url, string $destination, ?callable $progressCallback = null): bool|string {
        $ch = curl_init($url);
        
        if ($ch === false) {
            return false;
        }
        
        $file = fopen($destination, 'wb');
        if ($file === false) {
            curl_close($ch);
            return false;
        }
        
        // 设置cURL选项
        $this->setCommonCurlOptions($ch);
        curl_setopt($ch, CURLOPT_FILE, $file);
        
        // 支持进度回调
        if (is_callable($progressCallback) && PHP_VERSION_ID >= 50500) {
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, $progressCallback);
        }
        
        // 执行下载
        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        fclose($file);
        
        if (!$success || $httpCode !== 200) {
            if (file_exists($destination)) {
                unlink($destination);
            }
            return false;
        }
        
        return $destination;
    }

    /**
     * 直接输出文件内容供浏览器下载
     * @param string $url 远程文件URL
     * @param string|null $filename 下载时的文件名，为空则尝试从响应头中获取
     */
    public function downloadToBrowser(string $url, ?string $filename = null): void {
        $response = $this->sendRequest($url, 'GET', [], ['Accept' => 'application/octet-stream']);
        
        if (!$response['success'] || $response['httpCode'] !== 200) {
            http_response_code(500);
            echo "下载失败: " . ($response['error'] ?? '未知错误');
            return;
        }
        
        // 尝试从响应头中获取文件名
        if (!$filename && isset($response['headers']['Content-Disposition'])) {
            preg_match('/filename="?([^"]+)"?/', $response['headers']['Content-Disposition'], $matches);
            $filename = $matches[1] ?? null;
        }
        
        // 如果没有找到文件名，生成一个随机文件名
        if (!$filename) {
            $filename = 'download_' . time() . '.dat';
        }
        
        // 设置响应头
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($response['body']));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // 输出文件内容
        echo $response['body'];
    }

    /**
     * 发送HTTP请求
     * @param string $url 请求URL
     * @param string $method 请求方法
     * @param array|string $data 请求数据
     * @param array $extraHeaders 额外的请求头
     * @return array 返回结果，包含响应头和响应体
     */
    private function sendRequest(string $url, string $method, array|string $data = [], array $extraHeaders = []): array {
        $ch = curl_init();
        
        if ($ch === false) {
            return [
                'success' => false,
                'error' => '初始化cURL失败',
                'httpCode' => 0,
                'headers' => [],
                'body' => ''
            ];
        }
        
        // 设置cURL选项
        $this->setCommonCurlOptions($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        // 设置请求体
        if (!empty($data) && $method !== 'GET') {
            if (is_array($data)) {
                // 检查是否有文件上传（PHP 8.1+ 推荐使用CURLFile）
                $hasFile = false;
                foreach ($data as $key => $value) {
                    if (is_string($value) && str_starts_with($value, '@') && file_exists(substr($value, 1))) {
                        $hasFile = true;
                        // 传统方式处理文件上传
                        break;
                    }
                }
                
                if ($hasFile) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                } else {
                    // 普通表单数据
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                }
            } else {
                // 原始数据（如JSON）
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        
        // 设置请求头
        $headers = array_merge($this->headers, $extraHeaders);
        if (!empty($headers)) {
            $curlHeaders = [];
            foreach ($headers as $key => $value) {
                $curlHeaders[] = "$key: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        }
        
        // 获取响应头
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        // 执行请求
        $response = curl_exec($ch);
        
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                'success' => false,
                'error' => $error,
                'httpCode' => 0,
                'headers' => [],
                'body' => ''
            ];
        }
        
        // 获取响应信息
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        curl_close($ch);
        
        // 分离响应头和响应体
        $headerText = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        // 解析响应头
        $headers = [];
        $headerLines = explode("\r\n", $headerText);
        foreach ($headerLines as $line) {
            if (preg_match('/^([^:]+):\s+(.*)$/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }
        
        return [
            'success' => true,
            'httpCode' => $httpCode,
            'headers' => $headers,
            'body' => $body
        ];
    }
    
    /**
     * 设置通用的cURL选项
     */
    private function setCommonCurlOptions($ch): void {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        
        // 设置代理
        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }
        
        // SSL验证设置
        if (!$this->verifySSL) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
    }
}