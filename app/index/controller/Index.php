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

use Exception;
use think\Request;
use think\Response;
use think\facade\View;
use think\facade\Db;
use think\facade\Route;
use app\common\helper\Msgres;
use app\common\helper\ResponseHelper;
use think\facade\Session;

use app\facade\Category;
//use addons\pay\controller\AlipayFactory;
//use addons\pay\controller\WeixinFactory;
use app\common\facade\HttpHelper;
use app\facade\Article;
use think\db\Query;
use app\index\entity\Article as ArticleEntity;
use app\common\helper\JwtAuth;

use think\facade\Cache;

class Index extends IndexBaseController
{
    /**
     * 首页
     */
    public function index(Request $request)
    {
		// Session::set('user_id', 1);

		// $uid = Session::get('user_id_1');

		$uid = $request->session('user_id');

		$tagItems = Cache::getTagItems('tag');

		var_dump($uid, $tagItems);

		// var_dump(get_addons_config('ads',true));
	// var_dump(__DIR__);
	// echo 111;

	// dump(__DIR__.'/../../');
	// dump(realpath(__DIR__.'/../../'));

	// dump(root_path());

	// $P = pathinfo(__FILE__);

	// $namespace = str_replace(root_path(), '', $P['dirname']);
	// $namespace = str_replace('\\', '/', $namespace);
	// dump($namespace . DS . $P['filename'] . DS);
		
	// dump($P['dirname']);
	// dump($P);


	// hook('signhook', ['id'=>1]);

	// var_dump(get_addons_list());

	// dump(config('addons.hooks'));
	
	// dump(cache('hooks'));

	// dump(cache('addons_list'));

	// dump(cache('addons_config'));

	// dump(config('addons'));

	// 查看已注册的路由
	// dump(app('route')->getRuleList());

		// $subQuery = Article::suffix(1)
        //     ->with(['category' => function($query) {
        //         $query->where('status', 1);  // 只关联状态正常的用户
        //     }])
        //     ->has('category')  // 关键：只返回有关联user的记录
        //     ->field('id')
        //     // ->where($where)
        //     ->where('status', 1)
        //     ->order('id', 'desc')
        //     // ->limit(self::$offset, self::$newLimit) // 深分页用limit(offset, limit)更直观
        //     ->buildSql(); // 生成带括号的子查询SQL

            // halt($subQuery);
		// $subsql = Db::name('article')
		// ->where('status',1)
		// ->page(2)
		// ->limit(10)
		// ->field('id')
		// ->buildSql();

		// $a = Db::name('article')
		// ->alias('a')
		// ->join([$subsql=> 'b'], 'a.id = b.id')
		// ->field('a.id,a.title,a.content,a.create_time')
		// ->select();

		// $subQuery = ArticleEntity::where('category_id', 1)
		// ->where('status',1)
		// ->page(2)
		// ->limit(10)
		// ->field('id')
		// ->buildSql();

		// $b = ArticleEntity::alias('a')
		// ->join([$subQuery => 'b'], 'a.id = b.id')  // 子查询别名b，关联主表a
		// ->field('a.id,a.title,a.content,a.create_time')
		// ->select();  // 返回模型集合（可直接遍历，用法与Db查询结果一致）
		// // halt($subsql, $a);
		// halt($subQuery, $b);

		// $s = 'storage/1/d/jkjlkjlkkjl.jpg';
		// $a = pathinfo($s);
		// $name = basename($s, '.jpg');
		// halt($a,$name);

		// $alipay = AlipayFactory::createPayMethod();
		// $weixin = WeixinFactory::createPayMethod();
		// $a = $alipay->index();
		// $b= $weixin->index();
		// var_dump($a,$b);

		// 滚屏自动加载页码路由
		$page = $request->get('page/d', 1);
		$next = (string) url('index_page', ['page' => ++$page]);

		View::assign('next', $next);

		$html = View::fetch('index');

		$this->buildHtml($html);

		return $html;
    }

    public function jump(Request $request)
    {
        $username = $request->get('username');
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

	public function showImg(string $filename) {
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

	public function miss()
	{
		return response('404 Not Found!', 404);
	}


}
