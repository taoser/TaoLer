<?php

namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

class AuthRule extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
	
	public function searchIdAttr($query, $value, $data)
    {
        $query->where('id', $value );      
    }

    /**
     * 获取权限列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAuthRuleTree()
    {
        $authRules = $this->field('id,pid,title,name,icon,status,ismenu,sort,create_time')->select()->toArray();
        //数组排序
        $cmf_arr = array_column($authRules, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $authRules);

        if(count($authRules)) {
            return json(['code'=>0,'msg'=>'ok','data'=>$authRules]);
        } else {
            return json(['code'=>0,'msg'=>'no data','data'=>'']);
        }
    }

    public function saveRule($data)
    {
        $res = $this->save($data);
        if($res){
            return json(['code'=>0,'msg'=>'添加权限成功']);
        }else{
            return json(['code'=>-1,'msg'=>'添加权限失败']);
        }
    }

}
