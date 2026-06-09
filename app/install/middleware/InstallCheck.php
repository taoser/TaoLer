<?php
namespace app\install\middleware;

use think\Request;

class InstallCheck
{
    public function handle(Request $request, \Closure $next)
    {
        if(file_exists('./install.lock')){
            $html = '
            <link rel="stylesheet" href="/static/component/pear/css/pear.css" />
            <style>
            .check{
                height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center; 
                align-items: center;
            }
            </style>
            <div class="check">
            <p class="layui-font-26 layui-font-orange">TaoLer系统已被锁定!</p>
            <p class="layui-font-26 layui-font-orange">如需重新安装，请删除public目录下的install.lock文件。</p>
            </div>';
                
            return response($html);
        }
        return $next($request);
    }
}