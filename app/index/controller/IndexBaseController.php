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

namespace app\index\controller;

use think\Request;
use think\facade\View;
use think\facade\Db;
use app\common\helper\IdEncode;

/**
 * 控制器基础类
 */
class IndexBaseController extends \app\BaseController
{

	/**
	 * 登录用户uid
	 *
	 * @var int|null
	 */
	protected ?int $uid	= null;

	/**
	 * 登录用户信息
	 *
	 * @var array|object
	 */
	protected array|object $user = [];

    /**
     * 初始化系统，导航，用户
     * @return void
     */
    protected function initialize()
    {
		parent::initialize();

		$this->uid = $this->request->uid;

		$this->user = $this->getUserInfo();

		// 模板全局赋值，index下所有模板直接可以使用 $user
		View::assign('user', $this->user);

	}

	/**
	 * 当前用户信息
	 *
	 * @return array|object
	 */
    protected function getUserInfo(): array|object
    {
		if($this->uid === null) {
			return [];
		}

		$user = Db::name('user')
			->alias('u')
			->join('user_viprule v', 'v.vip = u.vip')
			->field('u.id as id,v.id as vid,name,nickname,avatar,sex,area_id,auth,city,phone,email,active,sign,point,u.vip as vip,nick,u.create_time as create_time')
			->cache(true)
			->findOrEmpty($this->uid);

		return $user;
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

}
