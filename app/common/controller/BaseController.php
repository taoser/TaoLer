<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2024-09-02 15:47:05
 * @LastEditors: TaoLer
 * @Description: 前端基础控制器设置
 * @FilePath: \TaoLer\app\common\controller\BaseController.php
 * Copyright (c) 2020~2024 https://www.aieok.com All rights reserved.
 */
declare (strict_types = 1);

namespace app\common\controller;

use think\facade\Request;
use think\facade\View;
use think\facade\Db;
use app\common\lib\IdEncode;

/**
 * 控制器基础类
 */
class BaseController extends \app\BaseController
{

	/**
	 * 登录用户uid
	 *
	 * @var int|null
	 */
	protected $uid = null;

	/**
	 * 登录用户信息
	 *
	 * @var array|object
	 */
	protected $user = [];

	protected $isLogin = false;

	protected $adminEmail;

	protected $hooks = [];

    /**
	 * 初始化系统，导航，用户
	 */
    protected function initialize()
    {
		$this->uid = session('?user_id') ? (int)session('user_id') : null;


		$this->adminEmail = Db::name('user')->where('id',1)->cache(true)->value('email');

		// $this->hooks = $this->getHooks();

		//系统配置
		$this->showSystem();

	}
	
	//显示网站设置
    protected function showSystem()
    {
        //1.查询分类表获取所有分类
		$sysInfo = $this->getSystem();


		$assign = [
			'sysInfo'	=> $sysInfo,
			'host'		=> Request::domain() . '/'
		];
		
        View::assign($assign);
		return $sysInfo;
    }

	/**
     * 纯静态化html 到 /public/static_html/
     *
     * @param string $content
     * @param string $staticFilePath
     * @return void
     */
	protected function buildHtml(string $content, string $staticFilePath = ''): void
	{
		if(config('taoler.config.static_html')) {

			if($staticFilePath == '') {
				
				// $baseUrl = $this->request->baseUrl();
				$rootUrl = $this->request->rootUrl().'/';
				$pathinfo = $this->request->pathinfo();
				$baseUrl = $rootUrl.$pathinfo;

				// 过滤掉html后面的参数
				$url = preg_replace('/\.html.*/', '.html', $baseUrl);

				if(str_contains($url, '.html')) {
					$staticFilePath = str_replace("\\", '/', public_path(). 'static_html/' . ltrim($url, '/'));
				} else {
					// 首页路径
					$staticFilePath = str_replace("\\", '/', public_path(). 'static_html/' . ltrim($url, '/') . 'index.html');
				}
			}

			if(!file_exists($staticFilePath)) {
				// 检测模板目录
				$dir = dirname($staticFilePath);
				if (!is_dir($dir)) {
					mkdir($dir, 0755, true);
				}

				// 压缩
				// $content = advanced_compress_html_js($content);
				$content = compressHtmlJs($content);
				file_put_contents($staticFilePath, $content);
			}
		}
	}

    /**
     * 编辑时 删除原有的静态html
     *
     * @param object $article
     * @return void
     */
	protected function removeDetailHtml(object $article): void
	{
		if(config('taoler.config.static_html')) {

			$id = IdEncode::encode((int)$article['id']);

			if(config('taoler.url_rewrite.article_as') == '<ename>/') {
				$url = (string) url('article_detail',['id' => $id,'ename' => $article->cate->ename]);
			} else {
				$url = (string) url('article_detail',['id' => $id]);
			}
	
			$staticFilePath = str_replace("\\", '/', public_path(). 'static_html/' . ltrim($url, '/'));

			if(file_exists($staticFilePath) && is_file($staticFilePath)) {
				unlink($staticFilePath);
			}
		}
	}

	/**
	 * 编辑时 删除原有的静态html
	 *
	 * @return void
	 */
	protected function removeIndexHtml(): void
	{
		if(config('taoler.config.static_html')) {
			$staticFilePath = str_replace("\\", '/', public_path(). 'static_html/index.html');
			if(file_exists($staticFilePath) && is_file($staticFilePath)) {
				unlink($staticFilePath);
			}
		}
	}

	/**
	 * // 查、改、删 tabname
	 *
	 * @param integer $id
	 * @return string
	 */
	protected function getTableName(int $id) : string
	{
		
		$suffix = '';
		$num = (int) floor(($id - 1) / config('taoler.single_table_num'));
		if($num > 0) {
			// 数据表后缀
            $suffix = "_{$num}";
		}

		// 表名
		$tableName = config('database.connections.mysql.prefix') . 'article' . $suffix; 
		return $tableName;
	}

	/**
     * 查、改、删时需要传入id,获取所在表的后缀
     *
     * @param integer $id
     * @return string
     */
    protected function byIdGetSuffix(int $id): string
    {
        // 数据表后缀为空时，id在主表中
        $suffix = '';
        $num = (int) floor(($id - 1) / config('taoler.single_table_num'));
        // num > 0, id在对应子表中
        if($num > 0) {
            //数据表后缀
            $suffix = "_{$num}";
        }

        return $suffix;
    }

}
