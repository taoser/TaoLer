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
use app\common\lib\facade\HttpHelper;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\facade\Article;
use app\common\model\Slider;
use app\common\lib\Msgres;
use yzh52521\EasyHttp\Http;
use QL\QueryList;

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
		$types = input('type');

        $slider = new Slider();
		//幻灯
        $sliders = Request::isMobile() ? $slider->getSliderList(12) : $slider->getSliderList(1);
		//置顶文章
		$artTop = Article::getArtTop(5);
        //首页文章列表,显示20个
        $artList = Article::getArtList(22);
        //热议文章
        $artHot = Article::getArtHot(10);
		//首页广告
		$indexAd = $slider->getSliderList(13);
        //温馨通道
        $fast_links = $slider->getSliderList(8);
		//首页赞助
		$ad_index =  $slider->getSliderList(5);
		//首页右栏图片
		$ad_comm = $slider->getSliderList(2);

		//友情链接申请
		$adminEmail = Db::name('user')->where('id',1)->cache(true)->value('email');
		
		$vs = [
			'slider'	=>	$sliders,
			'artTop'	=>	$artTop,
			'artList'	=>	$artList,
			'artHot'	=>	$artHot,
			'ad_index_r'=>  $indexAd,
			'type'		=>	$types,
			'ad_index'	=>	$ad_index,
			'ad_comm'	=>	$ad_comm,
			'fastlinks' =>	$fast_links,
			'adminEmail' => $adminEmail,
			'jspage'	=>	'',
		];
		View::assign($vs);

		return View::fetch();
    }
	
	//回帖榜
	public function reply()
	{
        $comment = new \app\common\model\Comment();
        return $comment->reply(20);
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
