<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\trace;

use Closure;
use think\App;
use think\Config;
use think\event\LogRecord;
use think\event\LogWrite;
use think\Request;
use think\Response;
use think\response\Redirect;

/**
 * 页面Trace中间件
 */
class TraceDebug
{

    /**
     * Trace日志
     * @var array
     */
    protected array $log = [];

    /**
     * 配置参数
     * @var array
     */
    protected array $config = [];

    /** @var App */
    protected $app;

    public function __construct(App $app, Config $config)
    {
        $this->app    = $app;
        $this->config = $config->get('trace');
    }

    /**
     * 页面Trace调试
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return void
     */
    public function handle($request, Closure $next)
    {
        $debug = $this->app->isDebug();

        // 注册日志监听
        if ($debug) {
            $this->log = [];
            $this->app->event->listen(LogWrite::class, function ($event) {
                if (empty($this->config['channel']) || $this->config['channel'] == $event->channel) {
                    $this->parseLog($event->log);
                }
            });
        }

        $response = $next($request);

        // Trace调试注入
        if ($debug) {
            $data = $response->getContent();
            $this->traceDebug($response, $data);
            $response->content($data);
        }

        return $response;
    }

    /**
     * 解析日志信息
     * @access public
     * @param array<LogRecord> $log
     * @return void
     */
    protected function parseLog(array $log)
    {
        foreach ($log as $key => $record) {
            if (is_string($key) && is_array($record)) {
                $this->log = array_merge_recursive($this->log, $log);
                break;
            } else {
                $this->log[$record->type][] = $record->message;
            }
        }
    }

    public function traceDebug(Response $response, &$content)
    {
        $config = $this->config;
        $type   = $config['type'] ?? 'Html';

        unset($config['type']);

        $trace = App::factory($type, '\\think\\trace\\', $config);

        if ($response instanceof Redirect) {
            //TODO 记录
        } else {
            $log    = $this->app->log->getLog($config['channel'] ?? '');

            $this->parseLog($log);
            $output = $trace->output($this->app, $response, $this->log);
            if (is_string($output)) {
                // trace调试信息注入
                $pos = strripos($content, '</body>');
                if (false !== $pos) {
                    $content = substr($content, 0, $pos) . $output . substr($content, $pos);
                } else {
                    $content = $content . $output;
                }
            }
        }
    }
}
