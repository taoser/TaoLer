<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\Db;
use taoser\think\Auth;

class Menu extends AdminController
{
    public function index(){
        return view();
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
        $data = Db::name('auth_rule')->field('id,title,icon,name,sort')->where(['pid'=>$pid,'status'=> 1, 'ismenu'=>1, 'delete_time'=> 0])->select();
        $tree = [];
        foreach ($data as $k => $v) {
            $hasChild = $this->hasChildren($v['id']);
            if($hasChild) {
                $v['hasChildren'] = 1;
            } else {
                $v['hasChildren'] = 0;
            }
            if ($auth->check($v['name'], session('admin_id')) || session('admin_id') == 1) {
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
    public function hasChildren($pid)
    {
        $data = Db::name('auth_rule')->field('pid')->where(['delete_time'=> 0,'status'=> 1,'ismenu'=>1,'pid'=>$pid])->select()->toArray();
        if(count($data)) {
            return true;
        } else {
            return false;
        }

    }
}