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
use app\common\model\Category;
use Sqids\Sqids;

//use addons\pay\controller\AlipayFactory;
//use addons\pay\controller\WeixinFactory;

// use app\common\lib\Near;
use think\facade\Cache;
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
		// $a = $alipay->index();
		// $b= $weixin->index();
		// var_dump($a,$b);

// 		$a = $this->getSfx();
// halt($a);

// $sqids = new Sqids('', 8);
// $ID1 = $sqids->encode([123]);
// $ID = $sqids->decode($ID1);
// halt($ID);

// $a = new Article();
// $b = Article::getIndexs();
// halt($b);

// $c = Category::getArticlesByCategoryEname('posts');
// halt($c);



		$html = View::fetch();

		$this->buildHtml($html);

		return $html;
    }

	/**
     * 获取分表后缀 
     *
     * @param integer|null $id
     * @return void
     */
    protected function getSfx(?int $id = null)
    {
        $suffix = '';

		// 增
        if($id === null) {
            // 表前缀
            $prefix = config('database.connections.mysql.prefix') . 'article';
            $sql = "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME REGEXP '{$prefix}_[0-9]+';";
		    $tables = Db::query($sql);
			// 是否有子数据表
            if(count($tables)){
                $arr = array_column($tables,'TABLE_NAME');
				$lastTableName = end($arr);
				// 数据库最大id
                $maxId = (int) Db::table($lastTableName)->max('id');
				if($maxId === 0) {
					// 数据库为空
					$nameArr = explode("_", $lastTableName);
					// 当前空表序号
					$num =  (int) end($nameArr);
					// 空表前最大ID
					$maxId = 100 * $num;
				}
            } else {
				// 仅有一张article表
                $maxId = (int) DB::name('article')->max('id');
            }

			// 表后缀数字（层级）
            $num = (int) floor($maxId / 100);
            
            // $autoIncrement = (int) ($maxId + 1);
			// halt($autoIncrement);
            
        } else {
			// 查、改、删
            $num = (int) floor(($id - 1) / 100);
        }

        if($num > 0) {
            // 数据表后缀
            $suffix = "_{$num}";
        }

        return $suffix;
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
