<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-08-14 07:33:14
 * @LastEditors: TaoLer
 * @Description: 分类模型
 * @FilePath: \github\TaoLer\app\common\model\Cate.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
namespace app\common\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Lang;
use think\Model;
use think\model\concern\SoftDelete;

class Cate extends Model
{
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;

	//关联文章
	public function article()
    {
        return $this->hasMany(Article::class);
    }
	
	// 查询类别信息
	public function getCateInfo(string $ename)
	{
		//
		return $this->field('ename,catename,detpl,desc')->where('ename',$ename)->cache('cate_'.$ename,600)->find();
	}

    // 查询子分类
    public function getSubCate(string $ename)
    {
        return $this->field('ename,catename')->where('pid', $this::where('ename', $ename)->value('id'))->select();
    }

	// 删除类别
	public function del($id)
	{
		$cates = $this->field('id,pid')->with('article')->find($id);
		$sonCate = $this->field('id,pid')->where('pid',$cates['id'])->find();
		if(empty($sonCate)) {
			$res = $cates->together(['article'])->delete();
			if($res){
				return 1;
			}else{
				return '删除分类失败';
			}
		} else {
			return '存在子分类，无法删除';
		}
	}

    // 分类表
    public function getList()
    {
        $data = $this->field('id,pid,sort,catename,ename,detpl,icon,appname,is_hot,desc')->where(['status'=>1])->select()->toArray();

        if(count($data)) {
            // 排序
            $cmf_arr = array_column($data, 'sort');
            array_multisort($cmf_arr, SORT_ASC, $data);
            return json(['code'=>0,'msg'=>'ok', 'count' => count($data),'data'=>$data]);
        } else {
            return json(['code'=>-1,'msg'=>'no data','data'=>'']);
        }
    }

    // 如果菜单下无内容，URl不能点击
    public function menu()
    {
        $appname = app('http')->getName();
        try {
            $cateList = $this->where(['status' => 1, 'appname' => $appname])
                ->cache('catename' . $appname, 3600)
                ->append(['url'])
                ->select()
                ->toArray();
            return $cateList;
        } catch (DbException $e) {
            return $e->getMessage();
        }

    }

    // 获取url
    public function getUrlAttr($value,$data)
    {
        // 栏目下存在帖子，则返回正常url,否则为死链
        $articleArr = Article::where('cate_id',$data['id'])->column('id');
        if(empty($articleArr)) {
            return 'javascript:void(0);';
        }
        return (string) url('cate',['ename' => $data['ename']]);;
    }
	
	
}