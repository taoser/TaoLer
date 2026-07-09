<?php

namespace app\common\lib\v2;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * HTTP请求助手类
 *
 * 使用方法：
 * $http = new HttpHelper(['timeout' => 15]);
 *
 * $result = $http->withHost('https://api.example.com')
 *     ->withHeaders(['Authorization' => 'Bearer token'])
 *     ->asJson()
 *     ->get('/users', ['page' => 1])
 *     ->toArray();
 *
 * if (!$http->ok()) {
 *     echo "请求失败: " . $http->getLastError();
 * }
 */
class HttpHelper
{
    protected ?Response $response = null;
    private Client $client;
    private string $host = '';
    private string $url = '';
    private array $options = [];
    private string $bodyFormat;
    private ?string $lastErrorMessage = null;
    private ?float $requestStartTime = null;
    private ?float $requestDuration = null;
    private int $maxRetries = 2;
    private int $retryDelay = 1000;
    private ?LoggerInterface $logger = null;

    /**
     * 构造函数
     *
     * @param array $config 客户端配置
     * @param LoggerInterface|null $logger 日志记录器
     */
    public function __construct(array $config = [], ?LoggerInterface $logger = null)
    {
        $defaultConfig = [
            'timeout' => 10,
            'connect_timeout' => 5,
            'http_errors' => false,
            // 兼容TP框架，不存在则关闭SSL验证
            'verify' => file_exists(public_path('cacert.pem')) ? public_path('cacert.pem') : false,
        ];

        $this->client = new Client(array_merge($defaultConfig, $config));
        $this->bodyFormat = 'form_params';
        $this->logger = $logger;
    }

    /**
     * 构建完整URL
     */
    private function buildUrl(string $url): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }
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
        // 每次请求重置错误信息和响应
        $this->response = null;
        $this->lastErrorMessage = null;
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
        $this->options['query'] = array_merge(
            $this->options['query'] ?? [],
            $queryParams
        );
        return $this;
    }

    /**
     * 设置超时时间
     */
    public function withTimeout(int $seconds): self
    {
        $this->options['timeout'] = $seconds;
        return $this;
    }

    /**
     * 设置连接超时时间
     */
    public function withConnectTimeout(int $seconds): self
    {
        $this->options['connect_timeout'] = $seconds;
        return $this;
    }

    /**
     * 设置重试配置
     */
    public function withRetry(int $maxRetries = 2, int $retryDelay = 1000): self
    {
        $this->maxRetries = $maxRetries;
        $this->retryDelay = $retryDelay;
        return $this;
    }

    /**
     * 设置日志记录器
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * 发送请求（公共方法）
     */
    private function sendRequest(string $method, string $url, array $data = []): ?self
    {
        $this->resetRequestOptions();
        $this->url = $this->buildUrl($url);
        $this->requestStartTime = microtime(true);
        $attempt = 0;
        $lastException = null;

        do {
            try {
                if ($method === 'GET') {
                    // GET请求合并查询参数
                    $existingQuery = $this->options['query'] ?? [];
                    $this->options['query'] = array_merge($existingQuery, $data);
                } else {
                    // POST/PUT/DELETE 统一使用body格式
                    $this->options[$this->bodyFormat] = $data;
                }

                $this->response = $this->client->request($method, $this->url, $this->options);
                $this->requestDuration = microtime(true) - $this->requestStartTime;

                // 检查HTTP状态码
                $this->checkHttpError();
                return $this;

            } catch (RequestException $e) {
                $lastException = $e;
                $attempt++;

                // 满足重试条件则重试
                if ($attempt < $this->maxRetries && $this->shouldRetry($e)) {
                    usleep($this->retryDelay * 1000);
                    continue;
                }

                // 重试耗尽，处理异常
                $this->handleException($e);
                $this->requestDuration = microtime(true) - $this->requestStartTime;
                return null;
            }
        } while ($attempt < $this->maxRetries);

        // 兜底返回
        $this->lastErrorMessage = '请求重试次数耗尽';
        return null;
    }

    /**
     * 判断是否应该重试
     */
    private function shouldRetry(RequestException $e): bool
    {
        // 无响应（网络错误）重试
        if (!$e->hasResponse()) {
            return true;
        }
        // 5xx服务器错误重试
        return $e->getResponse()->getStatusCode() >= 500;
    }

    /**
     * 检查HTTP错误状态码
     */
    private function checkHttpError(): void
    {
        if (!$this->response) {
            return;
        }

        $statusCode = $this->response->getStatusCode();
        if ($statusCode >= 200 && $statusCode < 300) {
            // 成功清空错误
            $this->lastErrorMessage = null;
            return;
        }

        // 失败记录错误
        $body = $this->response->getBody()->getContents();
        $this->response->getBody()->rewind();
        $this->lastErrorMessage = sprintf(
            'HTTP请求失败 [%d]: %s',
            $statusCode,
            strlen($body) > 500 ? substr($body, 0, 500) . '...' : $body
        );
    }

    /**
     * GET请求
     */
    public function get(string $url, array $queryParams = []): ?self
    {
        return $this->sendRequest('GET', $url, $queryParams);
    }

    /**
     * POST请求
     */
    public function post(string $url, array $data = []): ?self
    {
        return $this->sendRequest('POST', $url, $data);
    }

    /**
     * PUT请求
     */
    public function put(string $url, array $data = []): ?self
    {
        return $this->sendRequest('PUT', $url, $data);
    }

    /**
     * DELETE请求
     */
    public function delete(string $url, array $data = []): ?self
    {
        return $this->sendRequest('DELETE', $url, $data);
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

    /**
     * 使用表单格式发起请求
     */
    public function asFormParams(): self
    {
        $this->bodyFormat = 'form_params';
        return $this->withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);
    }

    /**
     * 使用multipart格式发起请求
     */
    public function asMultipart(): self
    {
        $this->bodyFormat = 'multipart';
        return $this;
    }

    /**
     * 解析响应
     */
    private function parseResponse(bool $asArray = false)
    {
        if (!$this->response) {
            return $asArray ? [] : null;
        }

        $body = $this->response->getBody()->getContents();
        $this->response->getBody()->rewind();

        // 空响应兼容
        if (trim($body) === '') {
            return $asArray ? [] : new \stdClass();
        }

        $result = json_decode($body, $asArray);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $errMsg = 'JSON解析失败: ' . json_last_error_msg() . " 响应内容：" . substr($body, 0, 500);
            $this->lastErrorMessage = $errMsg;
            throw new RuntimeException($errMsg);
        }

        return $result;
    }

    /**
     * 获取JSON响应
     */
    public function toJson(): ?\stdClass
    {
        return $this->parseResponse(false);
    }

    /**
     * 获取数组响应
     */
    public function toArray(): ?array
    {
        return $this->parseResponse(true);
    }

    /**
     * 获取原始响应体
     */
    public function getBody(): ?string
    {
        if (!$this->response) {
            return null;
        }
        $body = $this->response->getBody()->getContents();
        $this->response->getBody()->rewind();
        return $body;
    }

    /**
     * 判断请求是否成功（2xx状态码）
     */
    public function ok(): bool
    {
        return $this->response
            && $this->response->getStatusCode() >= 200
            && $this->response->getStatusCode() < 300;
    }

    /**
     * 获取HTTP状态码
     */
    public function status(): ?int
    {
        return $this->response?->getStatusCode();
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
     * 获取请求耗时（秒）
     */
    public function getRequestDuration(): ?float
    {
        return $this->requestDuration;
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
            $this->response->getBody()->rewind();

            $this->lastErrorMessage = sprintf('HTTP请求失败 [%d]: %s', $statusCode, $body);
        } else {
            $this->lastErrorMessage = '网络请求失败: ' . $e->getMessage();
        }

        // 日志记录
        if ($this->logger) {
            $this->logger->error($this->lastErrorMessage, [
                'method' => $this->url,
                'status' => $this->status(),
                'duration' => $this->requestDuration,
            ]);
        } else {
            error_log($this->lastErrorMessage);
        }
    }
}