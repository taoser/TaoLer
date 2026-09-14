<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-13 18:56:17
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\entity\AuthRule.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
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

		if(!is_null($rule)) {
			$data['level'] = $rule['level'] + 1;
		} else {
			$data['level'] = 0;
		}

        DB::startTrans();
        try {
            $this->pid = $data['pid'];
            $this->name = $data['name'];
            $this->type = $data['type'];
            if(!empty($data['icon'])){
                $this->icon = $data['icon'];
            }
            if(!empty($data['sort'])){
                $this->sort = $data['sort'];
            }

            $this->save();

            $currentLang = Lang::getLangSet();

            // 保存权限语言
            $ruleLang = new AuthRuleLang();
            $ruleLang->auth_rule_id = $this->id;
            $ruleLang->lang = $currentLang;
            $ruleLang->title = $data['title'];
            $ruleLang->save();

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw new Exception($e->getMessage(), $e->getCode());
        }
		
    }

    /**
     * 编辑权限
     * @param array $data 
     * @return bool
     */
    public function edit(array $data): bool
    {
        // 层级level
		$ruId = $this->find($data['pid']); //查询出上级ID
		if($ruId){
			$plevel = $ruId->level; //上级level等级
			$data['level'] = $plevel + 1;
		} else {
			$data['level'] = 0;
		}

        Db::startTrans();
        try {

            // 查询出下级权限
            $sub = $this->where('pid', $data['id'])->select();
            // 更新下级权限level
            if($sub->count()){
                $sub->update(['level' => $data['level'] + 1]);
            }

            $rule = $this->find($data['id']);

            $rule->pid = $data['pid'];
            $rule->name = $data['name'];
            $rule->type = $data['type'];
            if(!empty($data['icon'])){
                $rule->icon = $data['icon'];
            }
            if(!empty($data['sort'])){
                $rule->sort = $data['sort'];
            }
            $rule->save();

            // ========权限语言标题==============

            $lang = new AuthRuleLang();
            $currentLang = Lang::getLangSet();

            $ruleLang = $lang->where('auth_rule_id', $rule->id)->where('lang', $currentLang)->find();
            // 更新权限语言标题
            if(!is_null($ruleLang)){
                $ruleLang->title = $data['title'];
                $ruleLang->save();
            } else {
                // 新增权限语言
                $lang->auth_rule_id = $rule->id;
                $lang->lang = $currentLang;
                $lang->title = $data['title'];
                $lang->save();
            }

            // 提交事务
            Db::commit();
            return true;

        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), $e->getCode());
        }

    }

    /**
     * 删除权限
     * @param int $id 
     * @return bool
     */
    public function del(int $id): bool
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
            // $this->where('pid', $id)->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw new Exception($e->getMessage(), $e->getCode());
        }
        
    }

    public function getRuleTree()
    {
        $authRules = $this->with(['lang' => function($query) {
                $query->field('auth_rule_id,lang,title');
            }
        ])
		->field('id,pid,name,icon,status,type,sort,create_time')
		->order('sort','asc')
        ->append(['title'])
		->select()
		->toArray();

		if(empty($authRules)) {
			return [];
		}

		return build_tree($authRules);
    }

    /**
     * 获取权限菜单 给前端选择框使用
     * @return array
     */
    public function getRoleMenu(): array
    {
        $authRuleList = $this->with(['lang' => function($query) {
                $query->field('auth_rule_id,lang,title');
            }
        ])
        ->field('id,pid,sort,level')
        ->where(['status'=> 1])
        ->order('sort','asc')
        ->append(['title'])
        ->select()
        ->toArray();

        if(empty($authRuleList)){
            return [];
        }

        return build_tree($authRuleList);

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
        $authRules = $this->field('id,pid,title,name,icon,status,type,sort,create_time')
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
        $authRules = $this->field('id,pid,title,name,icon,status,type,sort,create_time')
        ->order('sort','asc')
        ->select()
        ->toArray();
        $ruls = [];
        foreach($authRules as $v) {
            $ruls[] = [
                'powerId'   => $v['id'],
                'powerName' => Lang::get($v['title']),
                'powerType' => $v['type'],
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
