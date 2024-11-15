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

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\facade\Article;
use app\common\lib\Msgres;

// use addons\pay\controller\AlipayFactory;
use addons\pay\controller\WeixinFactory;

//use app\common\lib\Near;

class Index extends BaseController
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
		// dump($weixin);
		// $a = $alipay->index();
		// $b= $weixin->index();
		// var_dump($b);

		// $hook['hook_name'] = 'thinkphp';
		// $hook['hook_type'] = 1;
		// $hook['template'] = 'taoler';
		// $hook['sort'] = 111;
		// $hook['create_time'] = time();
		// $hook['param'] = [
		// 	'article_id'    => '$article.id',
		// 	'uid' => 1,
		// ];
		// Db::name('addon_hook')
		// 	->json(['param'])
		// 	->insert($hook);

		// $lawyers = Db::name('addon_lawyer')
		// //->json(['begood'])
		// ->where('begood','like', '%1%')
		// ->select()
		// ->toArray();

		// $count = count($lawyers);

		// if($count) {
		// 	$k = rand(0,$count - 1) ;
		// 	$lawyer = $lawyers[$k];
		// }

	
		$types = input('type');
		//置顶文章
		$artTop = Article::getArtTop(5);
        //首页文章列表,显示10个
        $artList = Article::getArtList(10);

		$vs = [
			'artTop'	=>	$artTop,
			'artList'	=>	$artList,
			'type'		=>	$types
		];
		View::assign($vs);

		return View::fetch();
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
		}else {
			return Msgres::error('illegal_request');
		}
	}

}
