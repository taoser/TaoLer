<?php

namespace taoler\com;

use think\facade\Db;
use app\common\model\Message as MessageModel;
use app\common\model\MessageTo;

class Message
{	
	//send msg
	public static function sendMsg($sendId,$receveId,$data)
	{
		//写入消息库
		$msg = MessageModel::create($data); //写入消息库
		$msgId = $msg->id;
		
		//类型1为用户，写入用户收件箱
		if($data['type'] == 1){
			$result = MessageTo::create(['send_id'=>$sendId,'receve_id'=>$receveId,'message_id'=>$msgId,'message_type'=>$data['type']]);
			if(!$result){
				return false;
			}
		}
		if($msg){
			return true;
		} else {
			return false;
		}
    }
	
	//receve msg
	public static function receveMsg($uid)
	{
		 $msg = Db::name('message_to')
		->alias('t')
		->join('message m','t.message_id = m.id' )
		->join('user u','t.send_id = u.id')
		->field('t.id as id,name,title,content,link,receve_id,t.create_time as create_time,message_type,is_read')
		->where('t.receve_id',$uid)
		->where(['t.delete_time'=>0])
		->order(['t.is_read'=>'asc','t.create_time'=>'desc'])
		->select();
		return $msg;
    }
	
	//登录后插入系统消息
	public static function insertMsg($uid)
	{
		//得到所有系统消息
		$sysmsg = MessageModel::where(['type'=>0,'delete_time'=>0])->select();
		foreach($sysmsg as $smg){
			//检验通知是否被写入个人收件箱
			$msgId = Db::name('message_to')->where('message_id',$smg['id'])->find();
			if(!$msgId){
				$result = MessageTo::create(['send_id'=>$smg['user_id'],'receve_id'=>$uid,'message_id'=>$smg['id'],'message_type'=>$smg['type']]);
			}
		}
	}

}