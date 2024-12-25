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

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Request;
use think\facade\View;
use think\facade\Db;
use app\common\lib\IdEncode;

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
	protected int|null $uid = null;

	/**
	 * 登录用户信息
	 *
	 * @var array|object
	 */
	protected array|object $user = [];

	protected bool $isLogin = false;

	protected string $adminEmail;

    /**
     * 初始化系统，导航，用户
     * @return void
     */
    protected function initialize()
    {
		$this->uid = session('?user_id') ? (int)session('user_id') : null;

		$this->user = $this->userInfo();

		$this->adminEmail = Db::name('user')->where('id',1)->cache('adminEmail',3600)->value('email');

		//系统配置
		$this->showSystem();

		//变量赋给模板
		View::assign('user', $this->user);

	}

	//显示当前登录用户
    protected function userInfo()
    {
		if($this->uid === null) return [];
		//1.查询用户
        try {
            $user = Db::name('user')
                ->alias('u')
                ->join('user_viprule v', 'v.vip = u.vip')
                ->field('u.id as id,v.id as vid,name,nickname,user_img,sex,area_id,auth,city,phone,email,active,sign,point,u.vip as vip,nick,u.create_time as create_time')
                ->cache(true)
                ->find($this->uid);

        } catch (DataNotFoundException $e) {
        } catch (ModelNotFoundException $e) {
        } catch (DbException $e) {
        }

        return $user;
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
				$baseUrl = $this->request->baseUrl();
// dump($url);
				// 过滤掉html后面的参数
				$url = preg_replace('/\.html.*/', '.html', $baseUrl);

				if(str_contains($url, '.html')) {
					$staticFilePath = str_replace("\\", '/', public_path(). 'static_html/' . ltrim($url, '/'));
				} else {
					// 首页路径
					$staticFilePath = str_replace("\\", '/', public_path(). 'static_html/' . ltrim($url, '/') . 'index.html');
				}
			}
// dump($staticFilePath);
			if(!file_exists($staticFilePath)) {
				// 检测模板目录
				$dir = dirname($staticFilePath);
				if (!is_dir($dir)) {
					mkdir($dir, 0755, true);
				}

				// 压缩
				$content = advanced_compress_html_js($content);
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
