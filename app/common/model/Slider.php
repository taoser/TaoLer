<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-05-01 17:10:55
 * @LastEditors: TaoLer
 * @Description: 链接投放优化设置
 * @FilePath: \TaoLer\app\common\model\Slider.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Cache;

class Slider extends Model
{
	//protected $pk = 'id'; //主键
    protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
	//开启自动设置
	protected $auto = [];

	//仅更新有效
	protected $update = ['update_time'];
	
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;

    /**
     * 链接投放获取
     * @param $type 链接类型
     * @return mixed|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSliderList($type)
    {
        $sliders = Cache::get('slider'.$type);
        if(!$sliders){
            $sliders = $this::where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>$type])->whereTime('slid_over','>=',time())->select()->toArray();
            Cache::tag('tagSlider')->set('slider'.$type,$sliders,3600);
        }
        return $sliders;
    }

	//添加
	public function add($data)
	{
		$result = $this::save($data);

		if($result) {
			Cache::tag('tagSlider')->clear();
			return 1;
		} else {
			return 'add_error';
		}
	}
	
	//文章编辑
	public function edit($data)
	{
		$slider = $this::find($data['id']);
		$result = $slider->save($data);
		if($result) {
			Cache::tag('tagSlider')->clear();
			return 1;
		} else {
			return 'edit_error';
		}
	}

	//获取器
    public function getSlidTypeAttr($value)
    {
        $slid_type = [1=>'首页幻灯',2=>'首页图片',3=>'分类图片',4=>'详情图片',5=>'首页赞助',6=>'分类赞助',7=>'详情赞助',8=>'温馨通道',9=>'友情链接',10=>'头部菜单',11=>'页脚链接'];
        return $slid_type[$value];
    }
	







}