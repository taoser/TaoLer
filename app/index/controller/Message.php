<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-27 21:29:43
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \github\TaoLer\app\index\controller\Message.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\Session;
use think\facade\Request;
use think\facade\Db;
use app\common\model\Message as MessageModel;
use app\common\model\MessageTo;
use taoler\com\Message as MessageApi;

class Message extends BaseController
{
	//消息数目
	public function nums()
	{
		$messgeto = new MessageTo();
		$num = $messgeto->getMsgNum($this->uid);
		if(!is_null($num)){
			$res = ['status' =>0,'count' => $num, 'msg' => 'ok'];
		} else {
			$res = ['status' =>-1,'count' => 0, 'msg' => 'message error'];
		}
        return json($res);
	}
	
	//消息查询
	public function find()
	{
		$msg = MessageApi::receveMsg($this->uid);
		$count = $msg->count();
		$res = [];
		if($count){
			$res = ['status'=>0,'msg'=>'','count'=>$count];
			foreach ($msg as $k => $v){
			$data = ['id'=>$v['id'],'name'=>$v['name'],'title'=>$v['title'],'content'=>$v['content'],'time'=>date("Y-m-d H:i",$v['create_time']),'link'=>$v['link'],'read'=>$v['is_read'] ? '已读':'未读','type'=>$v['message_type']];
			$res['rows'][] = $data;
			}
		} else {
			$res = ['status'=>-1,'msg'=>'message find error','rows'=>''];;
		} 
		//var_dump($res);
		return json($res);
	}
	
	//读消息
	public function read()
	{
		$id =input('id');
		if($id){
			$msg = MessageTo::field('id,message_id')->with(['messages' => function($query){
				$query->where('delete_time',0)->field('id,content');
            }])->where('id',$id)->find();
			//改变读状态
			if($msg->is_read == 0){
				$result = $msg->update(['id'=>$id,'is_read'=>1]);
				if($result){
					$res=['status' =>0,'content'=>$msg['messages']['content']];
					return json($res);
				}
			} 
		} else {
			return json(['status' =>0, 'url'=>(string) url('User/message')]);
		}  
	}
	
	//消息删除
	public function remove()
	{
		$uid = $this->uid;
		
		$id = Request::only(['id']);
		if($id['id'] == 'true'){
			//删除所有此用户消息
			$msg = Db::name('message_to')->where(['receve_id'=>$uid,'delete_time'=>0])->useSoftDelete('delete_time',time())->delete();
		} else {
			//删除单条消息
			$msg = Db::name('message_to')->where('id',$id['id'])->useSoftDelete('delete_time',time())->delete();
		}
		
        if($msg){
			$res = ['status'=>0];
		}
      return $res;
           
	}
	
}