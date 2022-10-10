<?php
namespace app\admin\controller\addonfactory;

use app\common\controller\AdminController;
use think\App;
use think\facade\Request;
use think\facade\View;
use taoler\com\Files;
use app\admin\model\addonfactory\AddonFactory as AddonFactoryModel;
use app\common\lib\Zip;

class Index extends AdminController
{

    protected $model;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new AddonFactoryModel();
    }

    public function index()
    {
        if(Request::isAjax()) {
            $param = Request::param(['page','limit']);
            return $this->model->getList($param['page'],$param['limit']);

        }
        return View::fetch();
    }



    public function add()
    {
        if(Request::isAjax()) {
            $param = Request::param();
            $addonInfo = [
                'name'          => $param['name'],
                'title'         => $param['title'],
                'description'   => $param['description'],
                'author'        => $param['author'],
                'version'       => $param['version']
            ];

            // 插件路径
            $addonsDir = root_path() . 'addons' . DS . $param['name'] . DS;
            // 插件管理后台路径
            $addonsAdminDir = app_path();
            // 插件静态资源路径
            $addon_public = public_path() . 'addons' . DS . $param['name'] . DS;

            // 插件信息
            $info = $addonsDir . 'info.ini';
            // 插件
            $plugin = $addonsDir . 'Plugin.php';
            // 插件路由
            $route = $addonsDir . 'route' . DS . 'route.php';
            // 插件菜单
            $menu = $addonsDir . 'menu.php';

            // 插件控制器
            $controller = $addonsDir . 'controller' . DS;
            // 插件模型
            $model = $addonsDir . 'model' . DS;
            // 插件视图
            $view = $addonsDir . 'view' . DS;

            // 插件管理后台控制器
            $admin_controller = $addonsAdminDir . 'controller' . DS . $param['name'] . DS;
            // 插件管理后台模型
            $admin_model = $addonsAdminDir . 'model' . DS . $param['name']  . DS;
            // 插件管理后台视图
            $admin_view = $addonsAdminDir . 'view' . DS . $param['name'] . DS;
            // 插件管理后台验证器
            $admin_validate = $addonsAdminDir . 'validate' . DS . $param['name'] . DS;

            // 插件js
            $addon_js = $addon_public . 'js' . DS;
            // css
            $addon_css = $addon_public . 'css' . DS;
            // img
            $addon_img = $addon_public . 'img' . DS;




            if(!is_dir($addonsDir)) mkdir($addonsDir, 0755,true);
            //插件信息
            if(!file_exists($info)) {
                $time = date('Y-m-d H:i:s');
                $addon_info = <<<INFO
<?php
name =  {$param['name']}
title = {$param['title']}
description = {$param['description']}
status = 0
author = {$param['author']}
version = {$param['version']}
url = 
install = 0
requires = 1
website =
thumb =
publish_time = {$time}

INFO;
                // 写插件信息文件
                file_put_contents($info, $addon_info);
            }



            // 写插件核心文件
            if(!file_exists($plugin)) {
                // 插件核心
                $addon_plugin = <<<EOF
<?php
namespace addons\\{$param['name']};

use taoser\Addons;

/**
 * 插件测试
 * @author byron sampson
 */
class Plugin extends Addons
{
    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

	//必须实现安装
     public function enabled(){
		 return true;
	 }
	 
    //必须卸载插件方法
    public function disabled(){
		return true;
	}

}
EOF;
                file_put_contents($plugin, $addon_plugin);
            }


            // 创建路由
            if(isset($param['route']) && !file_exists($route)) {
                mkdir($addonsDir . 'route' . DS, 0755,true);
                // 路由
                $addon_route = <<<ROU
<?php

use think\\facade\Route;

ROU;
                file_put_contents($route, $addon_route);
            }

            // 创建菜单
            if(isset($param['menu']) && !file_exists($menu)) {
                // 菜单数组结构
                $addon_menu = <<<MENU
<?php
return [
    // 是否为主菜单
    'is_nav' => 0,
    'menu' => [
        //权限地址
        "name"    => "",
        //权限名称
        "title"   => "",
        //layui图标
        "icon"    => "layui-icon-template-1",
        //是否为菜单
        "ismenu"  => 1,
        //排序
        'sort'   => '50',
        //子菜单
        "sublist" => [
        ],
    ]
];

MENU;
                file_put_contents($menu, $addon_menu);
            }

            try{

                // 创建插件前台控制器、模型、视图目录
                if(isset($param['controller']) && !file_exists($controller)) mkdir($controller, 0755,true);
                if(isset($param['model']) && !file_exists($model)) mkdir($model, 0755,true);
                if(isset($param['view']) && !file_exists($view)) mkdir($view, 0755,true);

                // 后台控制器,模型，视图目录
                if(isset($param['admin_controller']) && !file_exists($admin_controller)) mkdir($admin_controller,0755,true);
                if(isset($param['admin_model']) && !file_exists($admin_model)) mkdir($admin_model,0755,true);
                if(isset($param['admin_view']) && !file_exists($admin_view)) mkdir($admin_view,0755,true);
                if(isset($param['admin_validate']) && !file_exists($admin_validate)) mkdir($admin_validate,0755,true);

                // 插件静态资源
                if(isset($param['js']) && !file_exists($addon_js)) mkdir($addon_js,0755,true);
                if(isset($param['css']) && !file_exists($addon_css)) mkdir($addon_css,0755,true);
                if(isset($param['img']) && !file_exists($addon_img)) mkdir($addon_img,0755,true);
                //写入数据库
                AddonFactoryModel::create($addonInfo);

            } catch (\Exception $e){
                return json(['code'=>-1,'msg'=>$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '插件创建成功']);
//          set_addons_info($param['name'],$addonInfo);
        }

        return View::fetch();
    }

    /**
     * 删除创建的插件结构
     * @return \think\response\Json|void
     */
    public function delete()
    {
        if(Request::isAjax()) {
            $param = Request::param();
            // 插件addons下目录
            $addonsDir = root_path() . 'addons' . DS . $param['name'] . DS;
            // 插件管理后台目录
            $admin_controller = app_path() . 'controller' . DS . $param['name'] . DS;
            $admin_model = app_path() . 'model' . DS . $param['name']  . DS;
            $admin_view = app_path() . 'view' . DS . $param['name'] . DS;
            $admin_validate = app_path() . 'validate' . DS . $param['name'] . DS;
            // 插件静态资源目录
            $addon_public = public_path() . 'addons' . DS . $param['name'] . DS;

            try {
                if(file_exists($addonsDir)) Files::delDir($addonsDir);
                if(file_exists($admin_controller)) Files::delDir($admin_controller);
                if(file_exists($admin_model)) Files::delDir($admin_model);
                if(file_exists($admin_view)) Files::delDir($admin_view);
                if(file_exists($admin_validate)) Files::delDir($admin_validate);
                if(file_exists($addon_public)) Files::delDir($addon_public);
                $this->model->destroy($param['id']);
            } catch (\Exception $e) {
                return json(['code' => -1, 'msg' => $e->getMessage()]);
            }

            return json(['code' => 0, 'msg' => '插件删除成功']);

        }
    }

    /**
     * 打包插件到runtime/addons目录
     * @return \think\response\Json|void
     */
    public function addonZip()
    {
        if(Request::isAjax()) {
            $param = Request::param();
            $addonsDir = root_path() . 'addons' . DS . $param['name'] . DS;
            $admin_controller = app_path() . 'controller' . DS . $param['name'] . DS;
            $admin_model = app_path() . 'model' . DS . $param['name']  . DS;
            $admin_view = app_path() . 'view' . DS . $param['name'] . DS;
            $admin_validate = app_path() . 'validate' . DS . $param['name'] . DS;
            $addon_public = public_path() . 'addons' . DS . $param['name'] . DS;

            $zipFile = root_path() . 'runtime' . DS . 'addons' . DS . $param['name'] . '-' . $param['version'] . '.zip';

            try {
                Zip::dirZip($zipFile,[
                    $addonsDir,$admin_controller,$admin_model,$admin_view,$admin_validate,$addon_public
                ]);

            } catch (\Exception $e) {

                return json(['code' => -1, 'msg' => $e->getMessage()]);
            }

            return json(['code' => 0, 'msg' => '打包插件成功']);

        }
    }

}
