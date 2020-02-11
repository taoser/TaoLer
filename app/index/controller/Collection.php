<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\Session;
use app\common\model\Collection as CollectionModel;
use app\common\model\Article;
use think\facade\Request;
use think\facade\Db;

class Collection extends BaseController
{
//	protected $type = [
//        'cid'    =>  'integer',
//    ];

	//文章收藏
	public function add(){
		//$data = Request::param();
		$data['article_id'] = intval(input('cid'));
		$data['user_id'] = session::get('user_id');
		$result = CollectionModel::create($data);
        if($result){
            $res['status'] = 0;
			//$res=['type' => 'add','type' => 'remove', 'msg' => '收藏成功'];
        }
        return $res;
	}

    //取消收藏
    public function remove(){
		
		$cid = input('cid');
        $aid = intval($cid);
		$user['user_id'] = session::get('user_id');
        //$result = CollectionModel::where('cid',$arid)->select();
        $result =  Db::name('collection')->where(['article_id' => $aid,'user_id' => $user['user_id']])->delete();
        if($result){
			$res['status'] = 0;
            //$res=['type' => 'add','type' => 'remove', 'msg' => '收藏成功'];  
        }
	 return $res;
    }

    //收藏查询
	public function find(){
		//$cid = Request::param();
		$cid = input('cid');
		$aid = intval($cid);
		$user['user_id'] = session::get('user_id');
		//halt($artid);
        $collectData =  Db::name('collection')->where(['article_id' => $aid,'user_id' => $user['user_id']])->find();
		if($collectData){
			$res['status'] = 0;
			$res['data']['collection'] = $collectData['article_id'];
		} else {
			$res['status'] = 0;
			$res['data'] = '';
		} 
       return json($res);
	}

	
}