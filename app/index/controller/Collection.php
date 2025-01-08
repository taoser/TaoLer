<?php
namespace app\index\controller;

use think\facade\Session;
use app\index\model\Collection as CollectionModel;
use app\facade\Article;
use think\facade\Request;
use think\facade\Db;

class Collection extends IndexBaseController
{
	//文章收藏
	public function add(){
		//$data = Request::param();
		$data['article_id'] = intval(input('cid'));
		$data['user_id'] = $this->uid;
		$arts = Article::with(['user'])->field('id,title,user_id')->find($data['article_id']);
		$data['collect_title'] = $arts['title'];
		$data['auther'] = $arts->user->name;
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
        //$result = CollectionModel::where('cid',$arid)->select();
        $result =  Db::name('collection')->where(['article_id' => $aid,'user_id' => $this->uid])->delete();
        if($result){
			$res['status'] = 0;
            //$res=['type' => 'add','type' => 'remove', 'msg' => '收藏成功'];  
        }
	 return $res;
    }

    //收藏查询
	public function find(){
		$cid = input('cid');
		$aid = intval($cid);
        $collectData =  Db::name('collection')->where(['article_id' => $aid,'user_id' => $this->uid])->find();
		if(!is_null($collectData)){
			$res['status'] = 0;
			$res['data']['collection'] = $collectData['article_id'];
		} else {
			$res['status'] = 0;
			$res['data'] = '';
		} 
       return json($res);
	}

	
}