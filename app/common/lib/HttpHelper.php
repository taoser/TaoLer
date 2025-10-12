<?php

namespace app\common\lib;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use RuntimeException;

/***
 * // 使用方法
    $http = new HttpHelper();

    // 设置主机和请求头
    $http->withHost('https://api.example.com')
        ->withHeaders(['Authorization' => 'Bearer token']);

    // 发送PUT请求
    $result = $http->asJson()
        ->put('/users/123', ['name' => 'New Name'])
        ->toArray();

    if (!$http->ok()) {
        // 获取详细错误信息
        echo "请求失败: " . $http->getLastError();
        // 获取HTTP状态码
        echo "状态码: " . $http->status();
    }

    // 发送DELETE请求
    $http->delete('/posts/456', ['confirm' => true]);

    if ($http->ok()) {
        echo "删除成功！";
    } else {
        echo "删除失败: " . $http->getLastError();
    }

    ****/

class HttpHelper
{
    protected ?Response $response = null;
    private Client $client;
    private string $host = '';
    private string $url = '';
    private array $options = [];
    private string $bodyFormat;
    private ?string $lastErrorMessage = null; // 添加属性存储错误信息

    public function __construct()
    {
        $certPath = public_path() . 'cacert.pem';
        $verify = file_exists($certPath) ? $certPath : false;
        
        $this->client = new Client([
            'verify' => $verify,
            'timeout' => 10,
            'http_errors' => false
        ]);

        $this->bodyFormat = 'form_params';
    }

    /**
     * 构建完整URL
     */
    private function buildUrl(string $url): string
    {
        // 绝对URL直接返回
        if (str_contains($url, 'http')) {
            return $url;
        }
        
        // 相对路径与基础URL拼接
        return rtrim($this->host, '/') . '/' . ltrim($url, '/');
    }

    /**
     * 重置请求选项（保留headers）
     */
    private function resetRequestOptions(): void
    {
        $keepKeys = ['headers'];
        $preserved = array_intersect_key($this->options, array_flip($keepKeys));
        
        $this->options = $preserved;
        $this->url = '';
    }

    /**
     * 设置基础URL
     */
    public function withHost(string $url = 'https://www.aieok.com/api'): self
    {
        $this->host = rtrim($url, '/');
        return $this;
    }

    /**
     * 添加请求头
     */
    public function withHeaders(array $headers = []): self
    {
        $this->options['headers'] = array_merge(
            $this->options['headers'] ?? [],
            $headers
        );
        return $this;
    }

    /**
     * 添加查询参数
     */
    public function withQuery(array $queryParams): self
    {
        $this->options['query'] = $queryParams;
        return $this;
    }

    /**
     * GET请求
     */
    public function get(string $url, array $queryParams = []): ?self
    {
        $this->resetRequestOptions();
        $this->url = $this->buildUrl($url);
        
        try {

            // 合并已有的查询参数和新传入的查询参数
            $existingQuery = $this->options['query'] ?? [];
            $mergedQuery = array_merge($existingQuery, $queryParams);
            
            if (!empty($mergedQuery)) {
                $this->options['query'] = $mergedQuery;
            }

            // if (!empty($queryParams)) {
            //     $this->options['query'] = $queryParams;
            // }
            
            $this->response = $this->client->get($this->url, $this->options);
            return $this;
            
        } catch (RequestException $e) {
            $this->handleException($e);
            return null;
        } finally {
            $this->bodyFormat = 'form_params'; // 重置为默认格式
        }
    }

    /**
     * POST请求
     */
    public function post(string $url, array $data = []): ?self
    {
        $this->resetRequestOptions();
        $this->url = $this->buildUrl($url);

        try {
            $this->options[$this->bodyFormat] = $data;

            $this->response = $this->client->post($this->url, $this->options);
            return $this;
            
        } catch (RequestException $e) {
            $this->handleException($e);
            return null;
        } finally {
            $this->bodyFormat = 'form_params'; // 重置为默认格式
        }
    }

    /**
     * PUT请求
     */
    public function put(string $url, array $data = []): ?self
    {
        $this->resetRequestOptions();
        $this->url = $this->buildUrl($url);
        
        try {
            $this->options[$this->bodyFormat] = $data;
            $this->response = $this->client->put($this->url, $this->options);
            return $this;
            
        } catch (RequestException $e) {
            $this->handleException($e);
            return null;
        } finally {
            $this->bodyFormat = 'form_params'; // 重置为默认格式
        }
    }

    /**
     * DELETE请求
     */
    public function delete(string $url, array $data = []): ?self
    {
        $this->resetRequestOptions();
        $this->url = $this->buildUrl($url);
        
        try {
            // DELETE请求可以同时支持查询参数和请求体
            if (!empty($data)) {
                if ($this->bodyFormat === 'form_params') {
                    $this->options['query'] = $data;
                } else {
                    $this->options[$this->bodyFormat] = $data;
                }
            }
            
            $this->response = $this->client->delete($this->url, $this->options);
            return $this;
            
        } catch (RequestException $e) {
            $this->handleException($e);
            return null;
        } finally {
            $this->bodyFormat = 'form_params'; // 重置为默认格式
        }
    }

    /**
     * 使用JSON发起请求
     */
    public function asJson(): self
    {
        $this->bodyFormat = 'json';
        return $this->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);
    }

    public function asFormParams(): self
    {
        $this->bodyFormat = 'form_params';
        return $this->withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);
    }

    public function asMultipart(): self
    {
        $this->bodyFormat = 'multipart';
        return $this;
    }

    /**
     * 返回JSON数据
     * @return mixed
     */
    // public function toJson()
    // {
    //     $body = $this->response->getBody()->getContents();
        
    //     return json_decode($body);
    // }

    /**
     * 获取JSON响应
     */
    public function toJson(): ?\stdClass
    {
        if (!$this->response) {
            return null;
        }
        
        $body = $this->response->getBody()->getContents();
        $result = json_decode($body);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('JSON解析失败: ' . json_last_error_msg());
        }
        
        return $result;
    }

    /**
     * 获取数组响应
     */
    public function toArray(): ?array
    {
        if (!$this->response) {
            return null;
        }
        
        $body = $this->response->getBody()->getContents();
        $result = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('JSON解析失败: ' . json_last_error_msg());
        }
        
        return $result;
    }

    /**
     * 判断请求是否成功（2xx状态码）
     */
    public function ok(): bool
    {
        return $this->response && 
               $this->response->getStatusCode() >= 200 && 
               $this->response->getStatusCode() < 300;
    }

    /**
     * 获取HTTP状态码
     */
    public function status(): ?int
    {
        return $this->response ? $this->response->getStatusCode() : null;
    }

    /**
     * 获取原始响应
     */
    public function rawResponse(): ?Response
    {
        return $this->response;
    }
    
    /**
     * 获取最后错误信息
     */
    public function getLastError(): ?string
    {
        return $this->lastErrorMessage;
    }

    /**
     * 异常处理
     */
    private function handleException(RequestException $e): void
    {
        if ($e->hasResponse()) {
            $this->response = $e->getResponse();
            $statusCode = $this->response->getStatusCode();
            $body = $this->response->getBody()->getContents();
            
            $this->lastErrorMessage = "请求出错，状态码: {$statusCode}，响应内容: {$body}";
            error_log($this->lastErrorMessage);
        } else {
            $this->lastErrorMessage = "请求出错: " . $e->getMessage();
            error_log($this->lastErrorMessage);
        }
    }
}