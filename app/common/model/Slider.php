<?php
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
     * 首页幻灯
     * @return mixed|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSliderList()
    {
        $sliders = Cache::get('slider');
        if(!$sliders){
            $sliders = $this::where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>1])->whereTime('slid_over','>=',time())->select();
            Cache::set('slider',$sliders,3600);
        }
        return $sliders;
    }

	//添加
	public function add($data)
	{
		$result = $this::save($data);

		if($result) {
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
			return 1;
		} else {
			return 'edit_error';
		}
	}
	







}