<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\api\model\Plugins.php
 * @Description: Plugins
 * @LastEditTime: 2023-03-14 10:20:34
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;


class Plugins extends Model
{
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
	public function app()
	{
		
		return $this->belongsTo(App::class);
	}
	
	public function getAddons()
	{
	   // return $this::with('app')->field('max(id),description,max(plugins_version),plugins_price,status,create_time,app_id')->where(['status'=>1])->group('app_id')->append(['vers'])->order('id','desc')->select();
	   
	    $plugins = [];
	    $ids = $this::field('max(id)')->where(['status'=>1])->group('app_id')->order('id','desc')->select();
        // halt($ids);
	    foreach($ids as $v) {
	        $plugins[] = $this::with('app')->field('id,description,plugins_version,plugins_price,status,create_time,app_id')->where(['status'=>1,'id' => $v['max(id)']])->append(['vers'])->find();
	    }
	    
	    return $plugins;
	    
	    
	}
	
	public function getList($data)
	{
	   
	   // return $this::with('app')->field('max(id),description,max(plugins_version),plugins_price,status,create_time,app_id')->where(['status'=>1])->group('app_id')->append(['vers'])->order('id','desc')->select();
	   if($data['type'] == 'freeAddons') {
	       $where[] = ['plugins_price', '=', 0];
	   } elseif ($data['type'] == 'payAddons') {
	       $where[] = ['plugins_price', '>', 0];
	   } else {
	       $where = [];
	   }
	   
	    $plugins = [];
	    $ids = $this::field('max(id)')->where(['status'=>1])->where($where)->group('app_id')->order('id','desc')->paginate([
            'list_rows'=> $data['limit'],
            'page' => $data['page'],
        ])->toArray();
        // halt($ids);
        $plugins['total'] = $ids['total'];
	    foreach($ids['data'] as $v) {
	         $plugins['data'][] = $this::with('app')->field('id,description,plugins_version,plugins_price,status,create_time,app_id')->where(['status'=>1,'id' => $v['max(id)']])->append(['vers'])->find();
	    }
	   // halt($plugins);
	    return $plugins;
	    
	    
	}
	
	// 获取各版本
    public function getVersAttr($value,$data)
    {
        
        return $this::where('app_id',$data['app_id'])->order('id', 'desc')->column('plugins_version');
            
    }
	
	
}