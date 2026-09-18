<?php
namespace app;

use Throwable;
use think\Request;
use think\Response;
use think\facade\Log;

use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use app\exception\BusinessException;

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
        BusinessException::class,  // 业务异常不写日志
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
        // 控制器不存在、路由不存在异常 不写入日志
        // if ($exception instanceof ControllerNotFoundException) {
        //     return;
        // }

        Log::error($exception->getMessage().':'.$exception->getFile().':'.$exception->getLine());
        // 使用内置的方式记录异常日志
        // parent::report($exception);
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
    protected function isApiRequest(Request $request): bool
    {
        // 方式1：通过请求头 Accept 判断（推荐）
         // 判断是否为API请求（关键：区分接口/网页）
        return $request->header('Accept')
            && str_contains($request->header('Accept'), 'application/json')
            || str_starts_with($request->pathinfo(), 'api/')
            || $request->isPost()
            || $request->isPut()
            || $request->isDelete()
            || $request->isOptions()
            || $request->isAjax();

 

        // 方式2：通过路由名称前缀，例如定义 api 路由组时设置别名
        // $rule = $request->rule();
        // if ($rule && str_starts_with($rule->getName(), 'api.')) {
        //     return true;
        // }

        // 方式3：通过域名/子目录判断，如 api.example.com 或 /api/*
        // if (str_contains($request->host(), 'api.') || str_starts_with($request->pathinfo(), 'api/')) {
        //     return true;
        // }

    
    }

    /**
     * 构造 JSON 格式的错误响应
     */
    protected function renderApiResponse(Throwable $e): Response
    {
        if ($e instanceof BusinessException) {
            return json([
                'code'  => $e->getCode() ?: -1,
                'msg'   => $e->getMessage(),
                'data'  => $e->getData(),
            ], 200);
        }

        // 业务失败返回1或者-1，其他错误返回500
        $code = $e->getCode() ?: 1;
        $httpStatus = ($code >= 100 && $code < 600) ? $code : 500;
        $message = $e->getMessage();

        if (!env('app_debug', false)) {
            $message = '服务器内部错误';
        }

        return json([
            'code'  => $code,
            'msg'   => $message,
            'data'  => [],
        ], $httpStatus);
    }

}
