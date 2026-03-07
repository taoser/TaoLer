<?php

namespace app\admin\controller\service;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Session;
use think\facade\Db;
use app\index\model\Message as MessageModel;
use taoler\com\Message;


class Notice extends AdminController
{
	//显示消息
	public function index()
	{
		return View::fetch();
	}

	public function list() {
		$page = Request::param('page/d', 1);
		$limit = Request::param('limit/d', 15);
		$count = MessageModel::count();
		$notices = MessageModel::page($page, $limit)->order('id', 'desc')->select();

		if($count){
			foreach($notices as &$v){
				$v['type'] == 0 ? '系统消息': '个人消息';
			}
			unset($v);

			return json(['code' => 0, 'msg' => 'ok', 'data' => $notices, 'count' => $count]);
		}

		return json(['code' => -1,'msg'=>'还没有发布任何通知']);
	}
	
	//添加消息
	public function add()
	{
		$sendId = Session::get('admin_id');

		$data = Request::only(['type','title','receve_id','content']);

		if($data['type'] == 1){
			$receveId = $data['receve_id']; //个人通知
		} else {
			$receveId = 0; //系统通知
		}
		unset($data['receve_id']); //收信人移除
		$data['user_id'] = $sendId; //发信人入信息库
		//写入信息库
		$result = Message::sendMsg($sendId, $receveId, $data);

		if($result){
			return json(['code' => 0,'msg' => '发布成功']);
		}

		return json(['code'=>-1,'msg'=>'发布失败']);
	}
	
	// 编辑
	public function edit()
	{
		$id = input('id');
		if(Request::isAjax()){
			$data = Request::only(['id','title','type','content']);
			$result = MessageModel::update($data);
			if($result){
				$res = ['code'=>0,'msg'=>'编辑成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'编辑失败'];
			}
			return json($res);
		}

		$msg = Db::name('message')->find($id);
		View::assign(['msg'=>$msg]);

		return View::fetch();
	}
	
	//删除消息
	public function delete($id)
	{
		if(Request::isAjax()){
			$msg = MessageModel::with('messageto')->find($id);
			$result = $msg->together(['messageto'])->delete();
			
			if($result){
				return json(['code'=>0,'msg'=>'删除成功']);
			}

			return json(['code'=>-1,'msg'=>'删除失败']);
		}
	}
	
	
	
}