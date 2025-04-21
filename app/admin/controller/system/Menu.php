<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\system\Menu.php
 * @Description: Menu
 * @LastEditTime: 2023-03-14 16:46:37
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\system;

use app\common\controller\AdminController;
use think\facade\Db;
use taoser\think\Auth;
use think\facade\Lang;
use think\facade\Session;

class Menu extends AdminController
{
    /**
     * pearadmin动态菜单栏
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getnav()
    {
        $auth   = new Auth();
        // 菜单数组
        $menu   = [];

        // 初始菜单
        $menu[] = [
            'id'    => 501,
            "title" => "控制后台",
            "icon"  => "layui-icon layui-icon-console",
            "type"  => 1,
            "openType"  =>"_iframe",
            "href"  => (string) url("index/console1"),
            'sort'  => 1,
            'pid'   => 1,
        ];

        // 多后台菜单
        $rule = Session::has('ruleTable') ? Session::get('ruleTable') : 'auth_rule';

        $auth_rule_list = Db::name($rule)
        ->field('id,pid,title,icon,name,sort,ismenu')
        ->where(['delete_time'=> 0, 'status'=> 1])
        ->select();
        
        foreach ($auth_rule_list as $v) {
            if ($auth->check($v['name'], $this->aid) || $this->aid == 1) {
                $menu[] = [
                    'id'    => $v['id'],
                    'title' => Lang::get($v['title']),
                    'icon'  => 'layui-icon ' . $v['icon'],
                    'href'  => (string) url($v['name']),
                    'pid'   => $v['pid'],
                    'sort'  => $v['sort'],
                    'ismenu' => $v['ismenu']
                ];
            }
        }

        $nav = $this->getTrees($menu);

        // 初始化控制台
        
        // $nav[] = [
        //     'id'    => 500,
        //     'title' => '主页',
        //     'icon'  => 'layui-icon layui-icon-console',
        //     'href'  => '',
        //     'sort'  => 1,
        //     'type'  => 0,
        //     'children' => [
        //         [
        //             'id'    => 501,
        //             "title" => "控制后台",
        //             "icon"  => "layui-icon layui-icon-console",
        //             "type"  => 1,
        //             "openType"  =>"_iframe",
        //             "href"  => (string) url("index/console1"),
        //             'sort'  => 1,
        //         ],[
        //             'id'    => 502,
        //             "title" => "数据分析",
        //             "icon"  => "layui-icon layui-icon-console",
        //             "type"  => 1,
        //             "openType"  => "_iframe",
        //             "href"  => (string) url("index/console2"),
        //             'sort'  => 2,
        //         ]
        //     ]
        // ];

        $nav[] = Session::has('ruleTable') ? [
            'id'    => 999,
            'title' => '用户后台',
            'icon'  => 'layui-icon layui-icon-console',
            'href'  => (string) url("apps/delete"),
            'sort'  => 999,
            'type'  => 1,
            "openType"  => "_blank",
        ] : [
            'id'    => 999,
            'title' => '管理后台',
            'icon'  => 'layui-icon layui-icon-console',
            'href'  => (string) url("apps/index"),
            'sort'  => 999,
            'type'  => 1,
            "openType"  => "_blank",
        ];

        $nav[] = [
            'id'    => 1000,
            'title' => '官网',
            'icon'  => 'layui-icon layui-icon-console',
            'href'  => 'https://www.aieok.com',
            'sort'  => 1000,
            'type'  => 1,
            "openType"  => "_blank",
        ];

        //SORT排序
        $cmf_arr = array_column($nav, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $nav);

        return json($nav);

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
                    $v['type'] = $v['ismenu'];
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

    // 后台菜单控制
    public function getMenuJsonData()
    {
        $menu = [
            "logo" => [
                "title"=> "TaoLer Admin",
                "image"=> "/static/admin/images/logo.png"
            ],
            "menu"=> [
                "data"=> (string) url('system.menu/getnav'),
                "method"=> "GET",
                "accordion"=> true,
                "collapse"=> false,
                "control"=> false,
                "select"=> "501",
                "async"=> true
            ],
            "tab"=> [
                "enable"=> true,
                "keepState"=> true,
                "session"=> true,
                "preload"=> false,
                "max"=> "30",
                "index"=> [
                    "id"=> "501",
                    "href"=> (string) url('index/console1'),
                    "title"=> "首页"
                ]
            ],
            "theme"=> [
                "defaultColor"=> "2",
                "defaultMenu"=> "dark-theme",
                "defaultHeader"=> "light-theme",
                "allowCustom"=> true,
                "banner"=> false
            ],
            "colors"=> [
                [
                    "id"=> "1",
                    "color"=> "#2d8cf0",
                    "second"=> "#ecf5ff"
                ],
                [
                    "id"=> "2",
                    "color"=> "#36b368",
                    "second"=> "#f0f9eb"
                ],
                [
                    "id"=> "3",
                    "color"=> "#f6ad55",
                    "second"=> "#fdf6ec"
                ],
                [
                    "id"=> "4",
                    "color"=> "#f56c6c",
                    "second"=> "#fef0f0"
                ],
                [
                    "id"=> "5",
                    "color"=> "#3963bc",
                    "second"=> "#ecf5ff"
                ]
            ],
            "other"=> [
                "keepLoad"=> "1200",
                "autoHead"=> false,
                "footer"=> false
            ],
            "header"=> [
                "message"=> "/static/admin/data/message.json"
            ]
        ];

        return json($menu);
    }


}