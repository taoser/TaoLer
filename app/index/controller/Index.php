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
use app\index\model\Article;

//use addons\pay\controller\AlipayFactory;
//use addons\pay\controller\WeixinFactory;

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

// 		$a = $this->getSfx();
// halt($a);

// $s = Article::where('id', '>=', 10 + 1) // >= <= 条件可以使用索引
// ->where([
// 	['cate_id', '=', 1],
// 	['status', '=',1]
// ])
// ->order('id asc')
// ->fetchSql(true)
// ->value('id');
// dump($s);
// $d = Article::getRelationArticle(1);

// $c = Category::getArticlesByCategoryEname('posts');
// halt($c);

// $t = new \app\index\model\Tag();
// $h = $t->getHots();

// dump($h);

		$html = View::fetch();

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
