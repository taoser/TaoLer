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
use yzh52521\EasyHttp\Http;


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
// echo 'JIT Enabled: ' . (filter_var(ini_get('zend_enable_jit'), FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No');
// phpinfo();
// dump(time());

        // $user = Db::name('user')
        //     ->alias('u')
        //     ->join('addon_lawyer b','b.user_id= u.id')
        //     ->field('u.id,b.name,user_img as avatar,chat_price,begood,licence_number,service_people')
        //     ->find(1);

        // $user = Db::name('user')
        // ->alias('u')
        // ->join('addon_lawyer l', 'u.id = l.user_id')
        // ->field('u.id,l.name,user_img as avatar,tel')
        // ->where('l.id', 2)
        // ->find();

        // halt($user);

//         $array1 = array('a' => 1, 'b', 'c');
// $array2 = array('d', 'e', 'f','a' => 2);
// $result1 = array_merge($array1, $array2);
// halt($result1);

// $o = new \app\api\controller\jida\Order;
// $list = $o->userlist();

// $city = '';
// $longitude = '113.16378';
// $latitude = '23.05282';
// $key = config('jida.map_api.amap');
// $address = Http::get("https://restapi.amap.com/v3/geocode/regeo?output=json&location={$longitude},{$latitude}&key={$key}&extensions=base")->json();
// if(isset($address->status) && $address->status === '1') {
//     if(empty($address->regeocode->addressComponent->city)) {
//         $city = $address->regeocode->addressComponent->province;
//     } else {
//         $city = $address->regeocode->addressComponent->city;
//     }
// }
// halt($city);
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
