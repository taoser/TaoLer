<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-08-16 12:09:28
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \TaoLer\extend\taoler\com\Message.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */

namespace taoler\com;

use think\facade\Db;
use app\common\model\Message as MessageModel;
use app\common\model\MessageTo;

class Message
{
    /**
     * 发送消息
     * @param $sendId
     * @param $receveId
     * @param $data
     * @return bool
     */
	public static function sendMsg($sendId,$receveId,$data)
	{
		//写入消息库
		$msg = MessageModel::create($data); //写入消息库
		$msgId = $msg->id;
		
		//类型1为用户，写入用户收件箱
		if($data['type'] == 1 || $data['type'] == 2 ){
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

    /**
     * 接收消息
     * @param $uid  接收消息用户ID
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
	public static function receveMsg($uid)
	{
		return Db::name('message_to')
		->alias('t')
		->join('message m','t.message_id = m.id' )
		->join('user u','t.send_id = u.id')
		->field('t.id as id,name,title,content,link,receve_id,t.create_time as create_time,message_type,is_read')
		->where('t.receve_id',$uid)
		->where(['t.delete_time'=>0])
		->order(['t.is_read'=>'asc','t.create_time'=>'desc'])
		->select()->toArray(); 
    }

    /**
     * 登录用户把系统消息写入数据表
     * @param int $uid
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
	public static function insertMsg(int $uid)
	{
		//得到所有系统消息
		$sysmsg = MessageModel::where(['type'=>0])->select();
		foreach($sysmsg as $smg){
			//检验通知ID是否被写入个人收件箱
			$msgId = Db::name('message_to')->where(['message_id'=>$smg['id'],'delete_time'=>0])->find();
			if(!$msgId){
				$result = MessageTo::create(['send_id'=>$smg['user_id'],'receve_id'=>$uid,'message_id'=>$smg['id'],'message_type'=>$smg['type']]);
			}
		}
	}

}