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
use app\common\model\Comment;

use addons\pay\controller\AlipayFactory;
use addons\pay\controller\WeixinFactory;

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
		// $comments = Comment::field('article_id,count(*) as count')
        //     ->hasWhere('article',['status' =>1])
        //     ->group('article_id')
        //     ->order('count','desc')
        //     ->limit(5)
        //     ->fetchSql(true)
        //     ->select();
        //     halt($comments);

		// $res = get_addons_info('callme1');

		// $htmlString = "<p>这是一个<a href='http://example.com'>链接</a>和其他文本。</p>";
		// $cleanString = preg_replace("/<a\b[^>]*>(.*?)<\/a>/is", "", $htmlString);
		// //$cleanString = preg_replace("(<a [^>]*>|</a>)","",$htmlString);
		// echo $cleanString;

		// $ip = file_get_contents('https://myip.ipip.net');
		// echo "My public IP address is: " . $ip;
		// $alipay = AlipayFactory::createPayMethod();
		// $weixin = WeixinFactory::createPayMethod();
		// $a = $alipay->index();
		// $b= $weixin->index();
		// var_dump($a,$b);

		$types = input('type');
		//置顶文章
		$artTop = Article::getArtTop(5);
        //首页文章列表,显示10个
        $artList = Article::getArtList(10);

		$vs = [
			'artTop'	=>	$artTop,
			'artList'	=>	$artList,
			'type'		=>	$types,
			'jspage'	=>	'',
		];
		View::assign($vs);

		return View::fetch();
    }

    public function jump()
    {
        $username = Request::param('username');
        $u = Db::name('user')->whereOr('nickname', $username)->whereOr('name', $username)->find();
        return redirect((string) url('user/home',['id'=>$u['id']]));

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
