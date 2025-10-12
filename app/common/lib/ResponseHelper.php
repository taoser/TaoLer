<?php

namespace app\common\lib;

/**
 * HTTP 响应助手类 - PHP 8 优化版本
 */
class ResponseHelper
{
    // HTTP 状态码常量
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * 返回成功响应
     * 
     * @param mixed $data 返回的数据
     * @param string $message 提示信息
     * @param int $httpCode HTTP 状态码
     * @param int|null $count 数据总数（用于分页等场景）
     * @return string JSON 响应字符串
     */
    public static function success(mixed $data = [], string $message = 'ok', int $httpCode = self::HTTP_OK, ?int $count = null): string {
        $response = [
            'code' => 0,
            'message' => $message,
            'data' => $data
        ];
        
        if ($count !== null) {
            $response['count'] = $count;
        } elseif (is_countable($data)) {
            $response['count'] = count($data);
        }

        return self::response($response, $httpCode);
    }

    /**
     * 返回失败响应
     * 
     * @param string $message 错误信息
     * @param int $errorCode 业务错误码
     * @param int $httpCode HTTP 状态码
     * @param mixed $data 附加数据
     * @return string JSON 响应字符串
     */
    public static function error(string $message = 'error', int $errorCode = 1, int $httpCode = self::HTTP_BAD_REQUEST, mixed $data = []): string {
        return self::response([
            'code' => $errorCode,
            'message' => $message,
            'data' => $data
        ], $httpCode);
    }

    /**
     * 处理异常并返回统一错误响应
     * 
     * @param \Throwable $e 可抛出对象
     * @param bool $debug 是否显示详细错误信息（调试模式）
     * @return string JSON 响应字符串
     */
    public static function handleException(\Throwable $e, bool $debug = false): string {
        $httpCode = self::getHttpCodeFromThrowable($e);
        $errorCode = $e->getCode() ?: 1;
        $message = $e->getMessage() ?: '服务器内部错误';
        
        $data = [];
        if ($debug) {
            $data = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }

        return self::error($message, $errorCode, $httpCode, $data);
    }

    /**
     * 根据 Throwable 获取 HTTP 状态码
     * 
     * @param \Throwable $e 可抛出对象
     * @return int HTTP 状态码
     */
    private static function getHttpCodeFromThrowable(\Throwable $e): int {
        // 利用 PHP 8 的混合类型和新异常处理特性
        return match (true) {
            $e instanceof \InvalidArgumentException => self::HTTP_BAD_REQUEST,
            $e instanceof \UnauthorizedException => self::HTTP_UNAUTHORIZED,
            $e instanceof \ForbiddenException => self::HTTP_FORBIDDEN,
            $e instanceof \NotFoundException => self::HTTP_NOT_FOUND,
            $e instanceof \PDOException => self::HTTP_INTERNAL_SERVER_ERROR,
            default => self::HTTP_INTERNAL_SERVER_ERROR,
        };
    }

    /**
     * 生成 JSON 响应但不终止脚本
     * 
     * @param array $data 响应数据
     * @param int $httpCode HTTP 状态码
     * @return string JSON 响应字符串
     */
    private static function response(array $data, int $httpCode): string {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code($httpCode);
        }
        
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}