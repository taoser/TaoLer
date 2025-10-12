<?php

namespace app\common\strategy;

use Exception;
use think\facade\Db;
use think\facade\Config;

// 策略模式：权限校验策略接口
class PostValidationStrategy implements ValidationStrategy
{
    public function validate($data)
    {

        $user = Db::name('user')->field('id,vip,point,auth')->find($data['user_id']);

			$postRule = Db::name('user_viprule')
				->field('postnum,postpoint')
				->where('vip', $user['vip'])
				->find();

			// 检测可发帖子剩余量
			$postLog = Db::name('user_article_log')
				->field('id,user_postnum')
				->where('user_id', $data['user_id'])
				->whereDay('create_time')
				->find();

			if(is_null($postLog)) {
				//没有记录创建
				Db::name('user_article_log')
				->save([
					'user_id' => $data['user_id'],
					'create_time' => time()
				]);

				$postLog = Db::name('user_article_log')
				->field('id,user_postnum')
				->where('user_id', $data['user_id'])
				->whereDay('create_time')
				->find();
			}

			// 超级管理员排外
			if($user['auth'] === '0') {
				// 可用免费额
				$cannum =  $postRule['postnum'] - $postLog['user_postnum']; 
				if($cannum <= 0) {
					//额度已用完需要扣积分
					$canpoint = 1 * $postRule['postpoint'];
					$point = $user['point'] - $canpoint;

					if($point < 0) { // 1.积分不足
						// return json(['code' => -1, 'msg' => "免额已使用,本次需{$canpoint}积分,请充值！"]);

                        throw new Exception("免额已使用,本次需{$canpoint}积分,请充值！");
					}

					// 2.扣除积分
					Db::name('user')
					->where('id', $data['user_id'])
					->update(['point' => $point]);
				}
			}

			// 超级管理员无需审核
			// $data['status'] = $user['auth'] ? 1 : Config::get('taoler.config.posts_check');
			// $msg = $data['status'] ? '发布成功' : '发布成功，请等待审核';
        
    }
}