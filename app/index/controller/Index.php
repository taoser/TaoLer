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
use app\common\lib\ResponseHelper;

use app\facade\Category;
//use addons\pay\controller\AlipayFactory;
//use addons\pay\controller\WeixinFactory;
use app\common\lib\facade\HttpHelper;
use app\facade\Article;
use think\db\Query;
use app\index\entity\Article as ArticleEntity;
use Exception;
use think\facade\Cache;
use think\Response;


class Index extends IndexBaseController
{

	public function showImg($filename) {
		// 3. 构建图片的完整物理路径
        $filePath = root_path() . 'data/' . $filename; // 或者 Filesystem::disk('local')->path($filename)

        // 4. 检查文件是否存在
        if (!file_exists($filePath)) {
            return json(['code' => 404, 'msg' => 'Image not found.'], 404);
        }

		$filePath = '../data/storage/kefu.jpg';
		// 5. 读取文件内容并输出
        $fileContent = file_get_contents($filePath);
        $mimeType = mime_content_type($filePath); // 获取正确的MIME类型

		return Response::create($fileContent)->contentType($mimeType);
	}

    /**
     * 首页
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
		// halt(123);

		// $s = 'storage/1/d/jkjlkjlkkjl.jpg';
		// $a = pathinfo($s);
		// $name = basename($s, '.jpg');
		// halt($a,$name);

		// $alipay = AlipayFactory::createPayMethod();
		// $weixin = WeixinFactory::createPayMethod();
		// $a = $alipay->index();
		// $b= $weixin->index();
		// var_dump($a,$b);

// 		$address = HttpHelper::get('https://app.bilibili.com/x/resource/ip')->toJson();
// 		$city = empty($address->data->city) ? $address->data->province : $address->data->city;
// halt($city);

		// $pushData = [
		// 	'force_notification' => true,
		// 	'push_clientid' => ['ba29ce2e13b8100ba7003af54f4d01b8','9d22e99139293f92042bcd2790b8ce13'],
		// 	// 'push_clientid' => 'ba29ce2e13b8100ba7003af54f4d01b8',
		// 	// 'push_clientid' => '60a8658dd3704ed3fac0a8f499686d87',
		// 	// 'push_clientid' => 'f52c05442224dd49d3101366586a8018', //oppo1
		// 	// 'push_clientid' => 'df4786b2e5fef0bb1dddb12b333f6b97',
		// 	'title' => '新订单消息',
		// 	'content' => '您有一条新的订单，请注意查看并及时处理',
		// 	'payload' => [
		// 		'text' => '您有新的订单要服务'
		// 	],
		// 	'category' => [
		// 		// 'huawei' => 'IM',
		// 		// 'oppo'	 => 'IM',
		// 		'harmony' => 'EXPRESS',
		// 		'huawei' => 'EXPRESS',
		// 		'vivo'  => 'ORDER'
		// 	],
		// 	'badge' => '+1',
		// 	'request_id' => (string)time(),
		// 	'options' => [
		// 		'android' => [
		// 			'HW' => [
		// 				// '/message/android/category' => 'IM', // IM/ACCOUNT/EXPRESS
		// 				'/message/android/category' => 'EXPRESS',
		// 				'/message/android/target_user_type' => 1,
		// 				'/message/android/notification/icon' => '/raw/icon_16',
		// 				// '/message/android/notification/sound' => '/raw/neworder', // 自定义铃声 中国区无效
		// 				// '/message/android/notification/default_sound' => false,
		// 			],
		// 			'XM' => [
		// 				'/extra.channel_id' => 138443, // 138443 IM消息/138444 订单/138445 账户
		// 				'/extra.sound_uri'	=> 'https://www.lawjida.com/audio/neworder.mp3', // 小米后台申请的自定义 sound_url 地址
		// 			],
		// 			"OP" => [
		// 				"/category" => "IM", // IM ACCOUNT ORDER
		// 				// '/channel_id' => 'push_oplus_category_service', //OPush平台上所有通道分为“公信”(默认)、“私信”两类，默认下发公信消息。公信消息单日可推送数量有限制，私信消息不限(仅限单个用户)
		// 				"/notify_level" => 2, // 通知栏消息提醒等级取值定义。1：通知栏，2：通知栏+锁屏，16：通知栏+锁屏+横幅+震动+铃声。使用notify_level参数时，category参数必传。
		// 				"/private_msg_template_id" => '68839785d6fbd5012f05b090', // IM消息：68839785d6fbd5012f05b090，订单消息：6875dbfcd6fbd5012d3fa6c6，账户：6875dc3dd6fbd5012d3fa6c7
		// 				// "/private_title_parameters" => '新消息通知',
		// 				// "/private_content_parameters"  => '您有一条新的消息'
		// 			],
		// 			'HO' => [
		// 				'/android/notification/importance' => 'NORMAL', // 消息分类 NORMAL 时，表示消息为服务通讯类 LOW 时，表示消息为资讯营销类
		// 				// '/android/notification/badge/addNum' => 1,
		// 				// '/android/notification/badge/setNum' => 5, 
		// 				// '/android/notification/badge/badgeClass' => '应用入口Activity类全路径名称'
		// 			],
		// 			'VV' => [
		// 				// '/category' => '填写对应的ID', // 二级分类
		// 			]
		// 		],
		// 		'harmony' => [
		// 			'HW' => [
		// 				// '/message/android/category' => 'IM', 
		// 				'/message/android/category' => 'EXPRESS',
		// 				'/message/android/target_user_type' => 1,
		// 				'/message/android/notification/sound' => '/raw/neworder',
		// 			]
		// 		]
		// 	],
			
		// ];


		// // $res = HttpHelper::asJson()->post("https://env-00jxtp0fagk2.dev-hz.cloudbasefunction.cn/sm", $pushData)->toJson();

		// $res = HttpHelper::asJson()->post("https://env-00jxtp0fagk2.dev-hz.cloudbasefunction.cn/push/send", $pushData)->toJson();
		
		// halt($res);

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
		}
		
		return Msgres::error('illegal_request');
	}

}
