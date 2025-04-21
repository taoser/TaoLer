<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-27 09:14:12
 * @LastEditors: TaoLer
 * @Description: 首页优化版
 * @FilePath: \github\TaoLer\app\index\controller\Index.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
namespace app\index\controller;

use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\common\lib\Msgres;
use app\facade\Category;
//use addons\pay\controller\AlipayFactory;
//use addons\pay\controller\WeixinFactory;
use app\common\lib\facade\HttpHelper;
use app\facade\Article;
use think\db\Query;
use app\index\entity\Article as ArticleEntity;

class Index extends IndexBaseController
{
    /**
     * 首页
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
		// $ip = file_get_contents('https://myip.ipip.net');
		// echo "My public IP address is: " . $ip;
    
		// $alipay = AlipayFactory::createPayMethod();
		// $weixin = WeixinFactory::createPayMethod();
		// $a = $alipay->index();
		// $b= $weixin->index();
		// var_dump($a,$b);
		// $ar = new ArticleEntity();
		// $a = $ar->getTops();


		// HttpHelper::asJson()->post('https://www.aieok.com/a',['a' =>'b']);

// $a = Article::getHotPvs();
// dump($a);

// $sql = Article::where('status', 1)->fetchSql(true)->select();
// halt($sql);

// halt($a);

// echo 'JIT Enabled: ' . (filter_var(ini_get('zend_enable_jit'), FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No');

		// 滚屏自动加载页码路由
		$page = Request::param('page/d', 1);
		$next = (string) url('index_page', ['page' => ++$page]);

		View::assign('next', $next);

		$html = View::fetch('index');

		$this->buildHtml($html);

		return $html;
    }

    public function jump()
    {
        $username = Request::param('username');
        $uid = Db::name('user')->whereOr('nickname', $username)->whereOr('name', $username)->value('id');
        return redirect((string) url('user/home',['id'=>$uid]));

    }
	
	public function language()
	{
		if(request()->isPost()){
			$language = new \app\common\controller\Language;
			$lang = $language->select(input('language'));
			if($lang){
				return Msgres::success();
			}
		} else {
			return Msgres::error('illegal_request');
		}
	}

}
