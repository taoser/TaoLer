<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2026-11-14 08:08:45
 * @LastEditors: TaoLer
 * @Description: 评论模型
 * @FilePath: \TaoLer\app\model\Comment.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */

namespace app\model;

use think\model\concern\SoftDelete;
use think\facade\Cache;

class Comment extends BaseModel
{	
	//软删除
	use SoftDelete;
    
    protected function getOptions(): array 
    {
        return [
            'autoWriteTimestamp'    => true,
            'deleteTime'            => 'delete_time',
            'defaultSoftDelete'     => null,
        ];
    }
	
    //评论关联文章
	public function article()
	{
		return $this->belongsTo(Article::class);
	}
	
    //评论关联用户
	public function user()
	{
		return $this->belongsTo(User::class);
	}

    // 获取url
    public function getUrlAttr($value,$data)
    {
        // dump($data);
        if(config('taoler.url_rewrite.article_as') == '<ename>/') {
            
            $article = Article::field('id,category_id')
            ->with(['category' => function($query){
                $query->withField('id,ename')->where(['status' => 1]);
            }])
            ->find($data['article_id']);

            return (string) url('detail', ['id' => $data['article_id'], 'ename' => $article->cate->ename]);
        } else {
            return (string) url('detail',['id' => $data['id']]);
        }
    }

    // 获取to_user_id
    public function getTouserAttr($value, $data)
    {
        if(isset($data['to_user_id'])) {
            return User::where('id', $data['to_user_id'])->value('name');
        }
        return '';
    }


    /**
     * 评论没有被删除正常显示
     * 评论被删除，但它下面有跟评时自身会显示为“评论已删除”，跟评会显示，无跟评且已删除则不显示
     * @param $value
     * @param $data
     * @return string
     */
    public function getContentAttr($value,$data)
    {
        if($data['delete_time'] == 0) {
            return $value;
        } else {
            if($this::getByPid($data['id'])) {
                return '<span style="text-decoration:line-through;">评论已删除</span>';
            } else {
                return '';
            }

        }
    }

}