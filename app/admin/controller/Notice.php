<?php

namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Session;
use think\facade\Db;
use app\common\model\Message as MessageModel;
use taoler\com\Message;


class Notice extends AdminController
{
	//显示消息
	public function index()
	{
		if(Request::isAjax()){
			$notices = MessageModel::where(['type'=>0,'delete_time'=>0])->paginate(15);
			$count = $notices->total();
			$res = [];
			if($count){
				$res = ['code'=>0,'msg'=>'','count'=>$count];
				foreach($notices as $msg){
				 $res['data'][] = ['id'=>$msg['id'],'type'=>$msg['type'],'title'=>$msg['title'],'user_id'=>$msg['user_id'],'content'=>$msg['content'],'ctime'=>$msg['create_time']];
				}
			} else {
				$res = ['code'=>-1,'msg'=>'还没有发布任何通知'];
			}
			return json($res);
		}
		return View::fetch();
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
		$result = Message::sendMsg($sendId,$receveId,$data);
		if($result){
			//event('Message');
			$res = ['code'=>0,'msg'=>'发布成功'];
		} else {
			$res = ['code'=>-1,'msg'=>'发布失败'];
		}
		return json($res);
	}
	
	//编辑VIP积分规则
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
				}else{
					return json(['code'=>-1,'msg'=>'删除失败']);
				}
		}
	}
	
	
	
}