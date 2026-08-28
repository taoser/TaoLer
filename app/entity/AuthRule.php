<?php
namespace app\entity;

use Exception;
use think\Request;
use think\Response;
use think\facade\Lang;
use think\facade\Db;

class AuthRule extends BaseEntity
{
    /**
     * 添加权限
     * @param array $data 
     * @return bool
     */
    public function add(array $data): bool
    {
        $count = $this->where('name', $data['name'])->count();
		if($count) {
            throw new Exception('权限地址已存在！', -1);
        }

		//层级level
		$rule = $this->field('level')->find($data['pid']);

		if(!is_null($rule)){
			$data['level'] = $rule['level'] + 1;
		} else {
			$data['level'] = 0;
		}

		return $this->save($data);
    }

    /**
     * 编辑权限
     * @param array $data 
     * @return bool
     */
    public function edit(array $data): bool
    {
        //层级level
		$ruId = $this->find($data['pid']); //查询出上级ID
		if($ruId){
			$plevel = $ruId->level; //上级level等级
			$data['level'] = $plevel+1;	
		} else {
			$data['level'] = 0;
		}

        //查询出下级权限
		$sub = $this->where('pid', $data['id'])->select();
		if(!empty($sub)){
			$sub->update(['level' => $data['level'] + 1]);
		}
        $rule = $this->find($data['id']);
        unset($data['id']);
		return $rule->save($data);
    }

    /**
     * 删除权限
     * @param int $id 
     * @return bool
     */
    public function delete(int $id): bool
    {
        DB::startTrans();
        try {
            $rule = $this->find($id);
            if(is_null($rule)){
                throw new Exception('权限不存在！', -1);
            }
            // 删除当前权限
            $rule->delete();
            
            // 删除当前权限下的所有子权限
            $this->where('pid', $id)->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw new Exception($e->getMessage(), $e->getCode());
        }
        
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
        $authRules = $this->field('id,pid,title,name,icon,status,ismenu,sort,create_time')
        ->order('sort','asc')
        ->select()
        ->toArray();

        if(count($authRules)) {
            return json(['code'=>0,'msg'=>'ok','data'=>$authRules]);
        }
        return json(['code'=>0,'msg'=>'no data','data'=>'']);
    }

    /**
     * 获取权限菜单数组
     *
     * @return  Response
     */
    public function getAuthRuleArray() :Response
    {
        $authRules = $this->field('id,pid,title,name,icon,status,ismenu,sort,create_time')
        ->order('sort','asc')
        ->select()
        ->toArray();
        $ruls = [];
        foreach($authRules as $v) {
            $ruls[] = [
                'powerId'   => $v['id'],
                'powerName' => Lang::get($v['title']),
                'powerType' => $v['ismenu'],
                'powerCode' => '',
                "powerUrl"  => $v['name'],
                "openType"  => null,
                "parentId"  => $v['pid'],
                "icon"      => $v['icon'],
                "sort"      => $v['sort'],
                "enable"    => $v['status'],
                "checkArr"  => "0"

            ];
        }

        if(count($ruls)) {
            return json(['code' => 0, 'msg' => 'ok', 'count' => count($ruls), 'data'=>$ruls]);
        }

        return json(['code' => 0, 'msg' => 'no data','count' => null,'data'=>'']);
    }

}
