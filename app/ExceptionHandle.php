<?php
namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;
use think\facade\Log;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        Log::error($exception->getMessage().':'.$exception->getFile().':'.$exception->getLine());
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        // 判断是否是 API 请求（支持多种方式，按需选择）
        if ($this->isApiRequest($request)) {
            return $this->renderApiResponse($e);
        }

        // 其他错误交给系统处理
        return parent::render($request, $e);
    }

    /**
     * 判断是否为 API 请求
     */
    protected function isApiRequest($request): bool
    {
        // 方式1：通过请求头 Accept 判断（推荐）
         // 判断是否为API请求（关键：区分接口/网页）
        $isApi = $request->header('Accept') && str_contains($request->header('Accept'), 'application/json')
            || str_starts_with($request->pathinfo(), 'api/')
            || $request->isAjax();

        if ($isApi) {
            return true;
        }

        // 方式2：通过路由名称前缀，例如定义 api 路由组时设置别名
        // $rule = $request->rule();
        // if ($rule && str_starts_with($rule->getName(), 'api.')) {
        //     return true;
        // }

        // 方式3：通过域名/子目录判断，如 api.example.com 或 /api/*
        // if (str_contains($request->host(), 'api.') || str_starts_with($request->pathinfo(), 'api/')) {
        //     return true;
        // }

        return false;
    }

    /**
     * 构造 JSON 格式的错误响应
     */
    protected function renderApiResponse(Throwable $e): Response
    {
        $code = $e->getCode() ?: 500;
        $message = $e->getMessage();
        
        // 生产环境下可隐藏真实错误信息，只暴露统一提示
        if (!env('app_debug', false)) {
            $message = '服务器内部错误';
        }

        $data = [
            'code'      => $code,
            'message'   => $message,
            'data'      => [],
        ];

        return json($data, $code);
    }

}
