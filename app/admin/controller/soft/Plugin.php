<?php
/*
 * @Program: TaoLer 2023/3/15
 * @FilePath: app\admin\controller\soft\Plugin.php
 * @Description: Plugin
 * @LastEditTime: 2026-06-30 22:40:04
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\soft;

use app\admin\controller\AdminBaseController;

use think\Exception;
use think\Request;
use RuntimeException;
use think\facade\View;
use think\facade\Config;
use app\admin\model\AuthRule;
use think\response\Json;
use app\common\facade\HttpHelper;
use app\common\helper\FileHelper;
use app\common\helper\SqlFile;
use app\common\helper\Zip;

class Plugin extends AdminBaseController
{
    protected $menu = [];

    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 浏览插件
     *
     */
    public function index()
    {
        return View::fetch();
    }

    /**
     * 插件动态列表
     * @param Request $request
     * @return Json
     */
    public function list(Request $request)
    {
        $page = $request->get('page/d', 1);
        $limit = $request->get('limit/d', 10);
        $type = $request->get('type/s', 'all');
        $appName = $request->get('app_name/s', '');
        
        // 本地插件列表
        $localPlguin = $this->getLocalPlugins();

        // 已安装插件列表
        if($type == 'installed') {

            $total = count($localPlguin); // 安装总数
            
            if ($total) {

                // 搜索本地已安装插件
                if(!empty($appName)) {
                    // 搜索插件名
                    $localPlguin = array_filter($localPlguin, function($v) use($appName){
                        return strpos($v, $appName) !== false;
                    });
                    $total = count($localPlguin); // 搜索插件总数
                    if($total == 0) {
                        return json(['code' => -1, 'msg' => 'no installed plugins']);
                    }
                }
                  
                // 数组分组
                $arr = array_chunk($localPlguin, $limit);
                // 选中的页码数组
                $arrAddon = $arr[$page - 1];
                // 数据
                $installedPlugins = [];
                foreach ($arrAddon as $k => $v) {
                    $info_file = root_path() . 'addons/' . $v . '/info.ini';
                    $info = parse_ini_file($info_file);
                    $info['install'] = $info['install'] ? '√' : '×';
                    $installedPlugins[] = $info;
                }
                return json(['code' => 0, 'msg' => 'ok', 'count' => $total, 'data' => $installedPlugins]);
            }

            return json(['code' => -1, 'msg' => 'no installed plugins']);
        }

        try{
            // 在线插件
            $response = HttpHelper::withHost()->post('/v2/plugin/list', [
                'type'      => $type,
                'page'      => $page,
                'limit'     => $limit,
                'app_name'  => $appName
            ]);
            
            if(!$response->ok()) {
                return json(['code' => -1, 'msg' => $response->getLastError()]);
            }

            $res = $response->toJson();
            if(!$res->code == 0) {
                return json(['code' => -1, 'msg' => $res->msg]);
            }
            // $data数据 与本地文件对比
            $data = [];
            foreach($res->data as $v){
                if(in_array($v->name, $localPlguin)) {
                    $info = get_addons_info($v->name);
                    // 存在本地的均为已安装
                    $v->isInstalled = 1;
                    //判断是否有新版本
                    if(version_compare($v->version, $info['version'], '>')) {
                        $v->has_new_version = 1;
                    }
                    $v->status = $info['status'] == 1 ? 1 : 0;

                    $v->price =  $v->price > 0 ? $v->price : '免费';
                } else {
                    $v->isInstalled = 0;
                    $v->has_new_version = 0;
                }

                $data[] = $v;
            };

            return json(['code' => 0, 'msg' => 'ok', 'count' => $res->count, 'data' => $data]);
            
        } catch(Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
        
    }

    /**
     * 安装插件
     * @param array $data
     * @return Json
     */
    public function install(?array $data = [])
    {
        if(empty($data)) {
            $data = $this->request->post(['name', 'token', 'version']);
        }
        $data['type'] = 'install';
        
        $result = HttpHelper::withHost()->post('/v2/plugin/get', $data);

        if(!$result->ok()) {
            return json(['code' => -1, 'msg' => $result->getLastMessage()]);
        }
        
        try {
            
            $response = $result->toJson();

            // -2未付款 -1安装失败
            if($response->code < 0) {
                return json($response);
            }

            // 文件
            $this->addonsFileCheckInstall($data['name'], $response->data->file_src);

            // 执行数据库
            $sqlInstallFile = root_path(). 'addons' . DS . $data['name'] . DS . 'install.sql';
            if(file_exists($sqlInstallFile)) {
                SqlFile::dbExecute($sqlInstallFile);
            }

            //执行插件安装
            $class = get_addons_instance($data['name']);
            $class->install();

            //安装菜单
            $menu = get_addons_menu($data['name']);
            if(!empty($menu)){
                if(!isset($menu['is_nav']) || $menu['is_nav'] > 8){
                    return json(['code'=>-1,'msg'=> 'is_nav菜单设置错误,无法完成安装！']);
                }
                //$pid = AuthRule::where('name','addons')->value('id');
                $pid = $menu['is_nav'];
                // 父ID状态为0时打开
                $pidStatus = AuthRule::where('id', $pid)->value('status');
                if($pidStatus < 1) {
                    AuthRule::update(['status' => 1, 'id' => $pid]);
                }
                unset($menu['is_nav']);
                $this->insertMenu($menu, (int)$pid, 1);
            }
            
            // 设置插件info
            set_addons_info($data['name'], ['status' => 1, 'install' => 1]);

            FileHelper::deleteDir(runtime_path() . 'addons' . DIRECTORY_SEPARATOR . $data['name'] . DIRECTORY_SEPARATOR);

            return json(['code' => 0, 'msg' => '插件安装成功！']);

        } catch (Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }

    }

        /**
     * 升级插件
     * @return Json
     * @throws Exception
     */
    public function upgrade(Request $request)
    {
        $data = $request->post(['name','token']);
        $info = get_addons_info($data['name']);
        $data['version'] = $info['version'];
        $data['type'] = 'upgrade';
        
        $result = HttpHelper::withHost()->post('/v2/plugin/get', $data);
        if(!$result->ok()) {
            return json(['code' => -1, 'msg' => $result->getLastError()]);
        }
        
        try {
            $response = $result->toJson();
            if($response->code < 0) {
                return json($response);
            }

            // 获取原配置信息
            $config = get_addons_config($data['name']);

            // 文件升级安装
            $this->addonsFileCheckInstall($data['name'], $response->data->file_src);
            // 先恢复原来的info版本
            set_addons_info($data['name'], ['version' => $info['version']]);

            // 卸载插件
            // $class = get_addons_instance($data['name']);
            // $class->uninstall();

            // 卸载菜单
            $menu = get_addons_menu($data['name']);
            $isMenuEmpty = empty($menu);
            if(!$isMenuEmpty){
                $pid = $menu['is_nav'];
                unset($menu['is_nav']);
                $this->removeMenu($menu);
            }

            // 升级sql
            $sqlUpdateFile = root_path() . "addons/{$data['name']}/data/update_{$response->data->version}.sql";
            if(file_exists($sqlUpdateFile)) {
                SqlFile::dbExecute($sqlUpdateFile);
            }

            $class = get_addons_instance($data['name']);
            $class->enabled();

            // 恢复配置
            if(!empty($config)) {
                set_addons_config($data['name'], $config);
            }

            // 更新现在的新版本info
            $info['version'] = number_format($response->data->version, 1); // 写入版本号
            set_addons_info($data['name'], $info);
    
            //恢复菜单
            if(!$isMenuEmpty){
                $this->insertMenu($menu, (int)$pid, 1);
            }

            return json(['code' => 0, 'msg' => "{$response->data->version}版本升级成功！"]);
            
        } catch (Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 卸载插件
     * @param string $name
     * @return Json
     * @throws Exception
     */
    public function uninstall(?string $name = null)
    {
        if($name === null) {
            $name = $this->request->post('name');
        }
        
        try {
            // 执行插件卸载
            $class = get_addons_instance($name);
            $class->uninstall();

            // 卸载菜单
            $menu = get_addons_menu($name);
            if(!empty($menu)){
                unset($menu['is_nav']);
                $this->removeMenu($menu);
            }

            //卸载插件数据库
            $sqlUninstallFile = root_path()."addons/{$name}/uninstall.sql";
            if(file_exists($sqlUninstallFile)) {
                SqlFile::dbExecute($sqlUninstallFile);
            }

            // 插件addons下目录
            $addonsDir = root_path() . 'addons' . DS . $name . DS;
            // 插件管理后台目录
            $admin_controller = app_path() . 'controller' . DS . $name . DS;
            $admin_model = app_path() . 'model' . DS . $name  . DS;
            $admin_view = app_path() . 'view' . DS . $name . DS;
            $admin_validate = app_path() . 'validate' . DS . $name . DS;
            // 插件静态资源目录
            $addon_public = public_path() . 'addons' . DS . $name . DS;

            if(file_exists($addonsDir)) FileHelper::deleteDir($addonsDir);
            if(file_exists($admin_controller)) FileHelper::deleteDir($admin_controller);
            if(file_exists($admin_model)) FileHelper::deleteDir($admin_model);
            if(file_exists($admin_view)) FileHelper::deleteDir($admin_view);
            if(file_exists($admin_validate)) FileHelper::deleteDir($admin_validate);
            if(file_exists($addon_public)) FileHelper::deleteDir($addon_public);

            return json(['code' => 0, 'msg' => '插件卸载成功']);

        } catch (Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
        
    }

    /**
     * 启用禁用插件
     * @return Json
     * @throws Exception
     */
    public function check(Request $request)
    {
        $name = $request->post('name');
        
            $info = get_addons_info($name);
            $arr = ['status' => $info['status'] ? 0 : 1];
    
            set_addons_info($name, $arr);

            if($arr['status']) {
                $res = ['code'=>0,'msg'=>'启用成功'];
            } else {
                $res = ['code'=>0,'msg'=>'已被禁用'];
            }

        return json($res);
    }

    /**
     * 配置插件
     * @param Request $request
     * @return string|Json
     * @throws Exception
     */
    public function config(Request $request)
    {
        $name = $request->post('name');
        try{
            $config = $this->getConfigArray($name);
        } catch (Exception $e) {
           return json(['code' => -1, 'msg' => $e->getMessage()]);
        }

        //模板引擎初始化
        View::assign(['formData' => $config, 'title' => 'title']);

        $configFile = root_path() . 'addons' . DS . $name . DS . 'config.html';
        $viewFile = is_file($configFile) ? $configFile : '';


        return View::fetch($viewFile);

    }

    /**
     * 配置插件提交
     * @return Json
     * @throws Exception | \think\Response
     */
    public function configSet(Request $request)
    {
        $name = $request->post('name');
        $params = $request->post('params/a',[],'trim');

        if (empty($params)) {
            return json(['code' => -1,'msg' => 'no params！']);
        }
        
        try {
            $config = $this->getConfigArray($name);

            foreach ($config as $k => &$v) {
                if (isset($params[$k])) {
                    if ($v['type'] == 'array') {
                        $arr = [];
                        $params[$k] = is_array($params[$k]) ? $params[$k] :[];
                        foreach ($params[$k]['key'] as $kk => $vv){
                            $arr[$vv] =  $params[$k]['value'][$kk];
                        }
                        $params[$k] = $arr;
                        $value = $params[$k];
                        $v['content'] = [];
                        $v['value'] = $value;
                    } elseif ($v['type'] == 'select'){
                        $value = [(int)$params[$k]];
                        $v['value'] = $value;
                        $v['content'] = $value;
                    } else {
                        $value =  $params[$k];
                    }

                    $v['value'] = $value;
                }
            }
            unset($v);
            set_addons_config($name, $config);

            return json(['code' => 0,'msg' => '配置成功！']);
        } catch (Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }

    }

    /**
     * 订单
     * @return string|Json
     */
    public function pay(Request $request)
    {
        $data = $request->post(['id/d','name','token']);
        $result = HttpHelper::withHost()->post('/v2/plugin/pay', $data);
        if(!HttpHelper::ok()) {
            return json(['code'=>-1,'msg'=>$result->getLastError()]);
        }

        try{
            $response = $result->toJson();

            return json($response);

        } catch (RuntimeException $e) {
            echo $e->getMessage();
            // 获取原始文本调试
            echo $result->getBody();
        } catch (Exception $e) {
            return json(['code'=> -1, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 支付查询
     * @return Json
     */
    public function isPay(Request $request)
    {
        $params = $request->post(['out_order_no','token']);

        $result = HttpHelper::withHost()->post('/v2/plugin/ispay', $params);
        
        if(!HttpHelper::ok()) {
            return json(['code' => -1, 'msg' => $result->getLastError()]);
        }

        try{
            $response = $result->toJson();
            
            return json($response);

        } catch (Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function add(Request $request)
    {
        //添加版本
        if($request->isPost()){
            return View::fetch();
        }
        
        $data = $request->post();
        $result = Plugin::create($data);
        if($result){
            return json(['code'=>0,'msg'=>'添加成功']);
        }
        return json(['code'=>-1,'msg'=>'添加失败']);
    }

    /**
     * 上传插件文件zip
     * @return Json
     */
    public function uploadZip(Request $request)
    {
        $id = $request->param();
        $file = $request->file('file');
        try {
            validate(['file'=>'filesize:2048|fileExt:zip,rar,7z'])
                ->check(array($file));
            $saveName = \think\facade\Filesystem::disk('public')->putFile('addons',$file);
        } catch (\think\exception\ValidateException $e) {
            return json(['code' => -1,'msg' => $e->getMessage()]);
        }
        $upload = Config::get('filesystem.disks.public.url');

        if($saveName){
            $name_path =str_replace('\\',"/",$upload.'/'.$saveName);
            $res = ['code'=>0,'msg'=>'插件上传成功','src'=>$name_path];
        } else {
            $res = ['code'=>-1,'msg'=>'上传错误'];
        }
        return json($res);
    }

    /**
     * 上传接口
     *
     * @return void
     */
    public function uploads(Request $request)
    {
        $type = $request->post('type');
        return $this->uploadFiles($type);
    }


    /**
     * 检测已安装插件是否有新的插件版本
     * @param string $addons_name
     * @param string $local_version
     * @return bool
     */
    public function checkHasNewVer(string $addons_name, string $local_version) :bool
    {
        // 在线插件
        $response = HttpHelper::withHost()->get('/v1/checkNewVersion', ['name' => $addons_name, 'version' => $local_version]);
        $addons = $response->toJson();
        if($addons->code === 0) return true;
        return false;
    }

    // 插件文件升级检查
    protected function addonsFileCheckInstall(string $name, string $url) {

        //拼接路径
        $addons_dir = str_replace('\\','/', root_path() . 'runtime' . DS . 'addons' . DS . $name . DS);
 
        // 2.把远程文件放入本地
        $package_file = $addons_dir . $name . '.zip';  //升级的压缩包文件路径

        // 下载文件
        FileHelper::downloadFile($url, $package_file);

        // 解压zip到runtime目录
        FileHelper::unZip($package_file, $addons_dir, true);

        // 只能复制目录包含的路径，避险
        $reserve = "addons/$name";
        // 复制
        FileHelper::copyFolder($addons_dir, root_path(), $reserve);
        // 删除
        FileHelper::deleteDir($addons_dir);

        return true;
    }

    protected function getConfigArray(string $name)
    {
        // !!!获取插件配置 只能引用文件解析，不能使用get_addons_config()，否则会加载视图文件
        $configFile =  root_path() . 'addons' . DS . $name . DS . 'config.php';
        if(!is_file($configFile)) {
            throw new Exception(lang('无配置,无需操作!'));
        }

        $config = include $configFile;

        if(empty($config)) {
            throw new Exception(lang('配置项为空！请正确配置'));
        }
        return $config;
    }

    /**
     * 获取本地插件列表
     * @return array
     */
    protected function getLocalPlugins() :array
    {
        // 本地插件列表
        $localPlguin = FileHelper::getSubDirNames(root_path() . 'addons' . DIRECTORY_SEPARATOR);

        // 若不存在info.ini，只有文件夹，表示没有安装成功
        foreach($localPlguin as $name) {
            $iniFile = str_replace('\\', '/', root_path() . "addons/$name/info.ini");
            if(!file_exists($iniFile)) {
                $key = array_search($name, $localPlguin, true);
                unset($localPlguin[$key]);
            }
        }

        return $localPlguin;
    }

    /**
     * 插入插件菜单
     *
     * @param array $menu 菜单数组
     * @param integer $pid 父ID
     * @param integer $type 菜单类型
     * @return void
     */
    protected function insertMenu(array $menu, int $pid = 0, int $type = 1)
    {
        try {

            foreach($menu as $v){
                $hasChild = isset($v['sublist']) && $v['sublist'] ? true : false;
                
                $v['pid'] = $pid;
                $v['name'] = trim($v['name'],'/');
                $v['type'] = $type;

                $menu_rule = AuthRule::withTrashed()->where('name', $v['name'])->findOrEmpty();
                if(!$menu_rule->isEmpty()){
                    $menu_rule->restore();
                } else {
                    $menu_rule = AuthRule::create($v);
                }

                if ($hasChild) {
                    $this->insertMenu($v['sublist'], $menu_rule->id, $type);
                }
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return true;
    }

    /**
     * 循环删除插件菜单
     * @param array $menu 菜单数组
     * @param string $module 插件模式 预留功能
     * @return void
     * @throws Exception
     */
    protected function removeMenu(array $menu, string $module = 'addon')
    {
        try {
            foreach ($menu as $k => $v){
                $hasChild = isset($v['sublist']) && $v['sublist'] ? true : false;
                $v['name'] = trim($v['name'], '/');

                $menu_rule = AuthRule::withTrashed()->where('name', $v['name'])->findOrEmpty();
                if(!$menu_rule->isEmpty()){
                    $menu_rule->delete(true);
                    if ($hasChild) {
                        $this->removeMenu($v['sublist']);
                    }
                }
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return true;
    }

}
