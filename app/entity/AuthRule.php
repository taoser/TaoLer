<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-11-03 13:25:48
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\entity\AuthRule.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\entity;

use Exception;
use think\Response;
use think\facade\Lang;
use think\facade\Db;
use app\exception\BusinessException;

class AuthRule extends BaseEntity
{

    /**
     * 获取权限层级
     * @param int $pid 父ID
     * @return int
     */
    public function getLevel($pid): int
    {
        // 顶级父ID=0，固定level=0
        if($pid === 0) {
            return 0;
        }

        // 查询父菜单level
        $parent = $this->field('level')->find($pid);
        if(is_null($parent)){
            return 0;
        }
        // 当前层级 = 父层级 + 1
        return $parent->level + 1;
        
    }

    /**
     * 递归刷新某个节点下所有子节点的level
     * @param int $parentId 父节点ID
     * @return void
     */
    function refreshChildLevel(int $parentId): void
    {
        $children = $this->where('pid', $parentId)->select();
        foreach ($children as $item) {
            // 重新计算：父level +1
            $item->level = $this->getLevel($parentId);
            $item->save();
            // 递归刷新它的子节点
            $this->refreshChildLevel($item->id);
        }
    }

    /**
     * 保存权限表单数据
     * 新增/更新权限
     * 自动更新子节点的level
     * @param array $data 权限数据 [
     *      'id' => 权限ID,
     *      'pid' => 父ID,
     *      'title' => 权限名称,
     *      'name' => 权限地址,
     *      'icon' => 图标,
     *      'sort' => 排序,
     *      'type' => 权限类型,
     * ]
     * @return bool
     */

    public function saveData(array $data): bool
    {
        $data['level'] = $this->getLevel($data['pid']);
        // 多语言
        $lang = new AuthRuleLang();
        $currentLang = Lang::getLangSet();

        Db::startTrans();
        try {
            // id > 0 时，更新权限
            if($data['id'] > 0){

                if($data['id'] === $data['pid']) {
                    throw new BusinessException('不能选择自己作为父节点！', 401);
                }
                // 更新权限
                $rule = $this->find($data['id']);
                $rule->pid = $data['pid'];
                $rule->name = $data['name'];
                $rule->type = $data['type'];
                $rule->level = $data['level'];
                if(!empty($data['icon'])){
                    $rule->icon = $data['icon'];
                }
                if(!empty($data['sort'])){
                    $rule->sort = $data['sort'];
                }
                $isChange = $rule->isChange('pid');
                $rule->save();

                $ruleId = $rule->id;
                // ✅【关键】如果本次修改了pid（换父节点），所有后代子节点的level要同步刷新！
                if ($isChange) {
                    $this->refreshChildLevel($ruleId);
                }

            } else {
                // 新增权限
                $rule = $this->where('name', $data['name'])->find();
                if(!is_null($rule)) {
                    throw new BusinessException('权限地址已存在！', 401);
                }
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
                $ruleId = $this->id;
            }

            // ========权限语言标题==============
            $ruleLang = $lang->where('auth_rule_id', $ruleId)->where('lang', $currentLang)->find();
            // 更新权限语言标题
            if(!is_null($ruleLang)){
                $ruleLang->title = $data['title'];
                $ruleLang->save();
            } else {
                // 新增权限语言
                $lang->auth_rule_id = $ruleId;
                $lang->lang = $currentLang;
                $lang->title = $data['title'];
                $lang->save();
            }

            // 提交事务
            Db::commit();
            return true;

        } catch (Exception $e) {
            Db::rollback();
            throw new BusinessException($e->getMessage(), $e->getCode());
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
            throw new BusinessException($e->getMessage(), $e->getCode());
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
        ->field('id,pid,title,sort,level')
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


}
