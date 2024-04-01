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

use think\db\exception\DbException;
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

    // ID查询类别信息
    public function getCateInfoById(int $id)
    {
        return $this->field('id,catename,ename,detpl,pid,icon,sort,desc')->find($id);
    }

    // 查询子分类
    public function getSubCate(string $ename)
    {
        return $this->field('ename,catename')->where('pid', $this::where('ename', $ename)->value('id'))->select();
    }

    // 查询兄弟分类
    public function getBrotherCate(string $ename)
    {
        return $this->field('id,ename,catename')->where('pid', $this::where('ename', $ename)->value('pid'))->append(['url'])->order('sort asc')->select();
    }

    /**
     * 删除分类
     * @param $id
     * @return int|string
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
	public function del($id)
	{
		$cates = $this->field('id,pid')->with('article')->find($id);
		$sonCate = $this->field('id,pid')->where('pid',$cates['id'])->find();
		if(empty($sonCate)) {
			$res = $cates->together(['article'])->delete();
            return $res ? 1 : '删除失败';
		}
        return '存在子分类，无法删除';
	}

    // 分类表
    public function getList()
    {
        $data = $this->field('id,pid,sort,catename,ename,detpl,icon,status,is_hot,desc')->select()->toArray();
        if(count($data)) {
            // 排序
            $cmf_arr = array_column($data, 'sort');
            array_multisort($cmf_arr, SORT_ASC, $data);
            return json(['code'=>0,'msg'=>'ok', 'count' => count($data),'data'=>$data]);
        }
        return json(['code'=>-1,'msg'=>'no data','data'=>'']);
    }

    // 如果菜单下无内容，URl不能点击
    public function menu()
    {
        try {
            return $this->where(['status' => 1])
                ->cache(3600)
                ->append(['url'])
                ->select()
                ->toArray();
        } catch (DbException $e) {
            return $e->getMessage();
        }

    }

    // 分类导航菜单
    public function getNav()
    {
        try {
            $cateList = $this->where(['status' => 1])
                ->cache(3600)
                ->append(['url'])
                ->select()
                ->toArray();
            // 排序
            $cmf_arr = array_column($cateList, 'sort');
            array_multisort($cmf_arr, SORT_ASC, $cateList);
            return getTree($cateList);
        } catch (DbException $e) {
            return $e->getMessage();
        }
    }

    // 获取url
    public function getUrlAttr($value,$data)
    {
        // 栏目下存在帖子，则返回正常url,否则为死链
        $articleArr = Article::field('id')->where('cate_id', $data['id'])->find();
        if(empty($articleArr)) {
            return 'javascript:void(0);';
        }
        return (string) url('cate',['ename' => $data['ename']]);;
    }
	
	
}