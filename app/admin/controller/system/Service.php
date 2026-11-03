<?php
namespace app\admin\controller\system;

use think\Request;
use think\Response;
use think\facade\Session;
use think\facade\Lang;
use app\entity\Admin;
use app\admin\controller\AdminBaseController;

class Service extends AdminBaseController
{
    protected  string $adminModuleName = '';


    public function initialize()
    {
        parent::initialize();

        $this->adminModuleName = '/' . trim(system_config('admin_module', 'admin'), '/');
    }

    /**
     * 服务配置接口
     * @return Response
     */
    public function siteConfig(): Response
    {
        $config = [
            "logo" => [
                "title"=> "TaoLer Admin",
                "image"=> "/static/admin/images/logo.png"
            ],
            "menu"=> [
                "data"=> (string) url('get_menu'),
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
                    "href"=> (string) url($this->adminModuleName . "/index/console1"),
                    "title"=> lang::get('home page')
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

        return json($config);
    }

    /**
     * 获取菜单接口
     * 
     * $menu[] = [
     *       'id'    => 500,
     *      'title' => '主页',
     *       'icon'  => 'layui-icon layui-icon-console',
     *       'href'  => '',
     *       'sort'  => 1,
     *       'type'  => 0, // 0目录1菜单2按钮
     *       'children' => [
     *           [
     *               'id'    => 501,
     *               "title" => "控制后台",
     *               "icon"  => "layui-icon layui-icon-console",
     *               "type"  => 1,
     *               "openType"  =>"_iframe",
     *               "href"  => (string) url("index/console1"),
     *               'sort'  => 1,
     *           ],[
     *               'id'    => 502,
     *               "title" => "数据分析",
     *               "icon"  => "layui-icon layui-icon-console",
     *               "type"  => 1,
     *               "openType"  => "_iframe",
     *               "href"  => (string) url("index/console2"),
     *               'sort'  => 2,
     *           ]
     *       ]
     *   ];
     * @return Response
     */
    public function getMenu(): Response
    {
        // 菜单数组
        $menu   = [];

        // 初始菜单
        $menu[] = [
            'id'    => 501,
            "title" => lang::get('control panel'),
            "icon"  => "layui-icon layui-icon-console",
            "type"  => 1,
            "openType"  =>"_iframe",
            "href"  => (string) url($this->adminModuleName . "/index/console1"),
            'sort'  => 1,
            'pid'   => 1,
        ];

        // 多后台菜单
        // $rule = Session::has('ruleTable') ? Session::get('ruleTable') : 'auth_rule';

        $rules = request()->admin->getMenu();
        $menu = array_merge($menu, $rules);

        $menu[] = Session::has('ruleTable') ? [
            'id'    => 999,
            'pid'   => 0,
            'title' => '用户后台',
            'icon'  => 'layui-icon layui-icon-console',
            'href'  => (string) url("{$this->adminModuleName}/apps/delete"),
            'sort'  => 999,
            'type'  => 1,
            "openType"  => "_blank",
        ] : [
            'id'    => 999,
            'pid'   => 0,
            'title' => '管理后台',
            'icon'  => 'layui-icon layui-icon-console',
            'href'  => (string) url($this->adminModuleName . "/apps/index"),
            'sort'  => 999,
            'type'  => 1,
            "openType"  => "_blank",
        ];

        $menu[] = [
            'id'    => 1000,
            'pid'   => 0,
            'title' => '官网',
            'icon'  => 'layui-icon layui-icon-console',
            'href'  => 'https://www.aieok.com',
            'sort'  => 1000,
            'type'  => 1,
            "openType"  => "_blank",
        ];

        $menu = build_tree($menu);
        
        return json($menu);
    }

}