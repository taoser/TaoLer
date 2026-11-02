<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-11-02 14:42:18
 * @LastEditors: TaoLer
 * @Description: 菜单控制器
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\admin\controller\system\Menu.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\system;

use app\admin\controller\AdminBaseController;
use Override;
use think\Request;
use think\Response;
use think\facade\Db;
use taoser\think\Auth;
use think\facade\Lang;
use think\facade\Session;
use app\entity\AuthRule;

class Menu extends AdminBaseController
{
    protected  string $adminModuleName = '';

    #[Override]
    public function initialize()
    {
        parent::initialize();
        $this->adminModuleName = '/' . trim(system_config('admin_module', 'admin'), '/');
    }



    
    /**
     * 菜单无限极分类
     *
     * @param array $data 包含有pid的rule权限数组
     * @param integer $pId 父ID
     * @return array
     */
    public function getTrees(array $data, int $pId = 0): array
    {
        // 递归
        $tree = [];
        foreach ($data as $k => $v) {
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($v['pid'] == $pId) {
                $child = $this->getTrees($data, $v['id']);
                // 有子类
                if(!empty($child)) {
                    // foreach($child as $m => $n) {
                    //     $v['children'][$m] = $n;
                    //     //$v['children'][$m]['type'] = 1;
                    //     //$v['children'][$m]['openType'] = '_iframe';
                    // }
                    $v['type'] = $v['type'];
                    $v['children'] = $child;
                } else {
                    // 没有子菜单type=1
                    $v['type'] = 1;
                    $v['openType'] = '_iframe';
                }

                //把数组放到$tree中
                $tree[] = $v;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($data[$k]);
            }
        }

        return $tree;
    }

    /**
     * 动态菜单并排序
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenuNavbar()
    {
        // 用户菜单权限
        $auth     = new Auth();

        $pid = empty(input('id')) ? 0 : input('id');
        $data = Db::name('auth_rule')->field('id,title,icon,name,sort')->where(['pid'=>$pid,'status'=> 1, 'type'=>1, 'delete_time'=> 0])->select();
        $tree = [];
        foreach ($data as $k => $v) {
            $hasChild = $this->hasChildren($v['id']);
            if($hasChild) {
                $v['hasChildren'] = 1;
            } else {
                $v['hasChildren'] = 0;
            }
            if ($auth->check($v['name'], session('admin.id')) || session('admin.id') == 1) {
                $tree[] = ['id'=>$v['id'],'text'=>$v['title'],'icon'=>$v['icon'],'hasChildren'=>$v['hasChildren'],'href'=>(string) url($v['name']),'sort'=>$v['sort']];
            }

        }
        // 排序
        $cmf_arr = array_column($tree, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $tree);

        return json($tree);
    }

    /**
     * 是否有子菜单
     * @param $pid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function hasChildren(int | string $pid)
    {
        $count = Db::name('auth_rule')->where(['pid' => $pid, 'status' => 1, 'type' => 1, 'delete_time' => 0])->count();

        return $count ? true : false;

    }


}