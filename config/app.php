<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],
    // 异常页面的模板文件
    'exception_tmpl' => env('app_debug') ? app()->getThinkPath() . 'tpl/think_exception.tpl' : public_path() . '404.html',
    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,
    // 定义HTTP异常模板文件地址
    'http_exception_template'    =>  [
        // 定义403错误的模板文件地址
        403 =>  public_path() . '403.html',
        // 还可以定义其它的HTTP status
        404 =>  public_path() . '404.html',
        // 服务器内部错误
        500 =>  public_path() . '500.html',
    ],
];
