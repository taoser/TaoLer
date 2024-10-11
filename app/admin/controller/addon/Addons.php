<?php
/*
 * @Program: TaoLer 2023/3/15
 * @FilePath: app\admin\controller\addon\Addons.php
 * @Description: Addons
 * @LastEditTime: 2023-03-15 22:40:04
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\addon;

use app\common\controller\AdminController;
use app\common\lib\SqlFile;
use app\common\lib\Zip;
use think\Exception;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use app\admin\model\AuthRule;
use app\admin\model\Addons as AddonsModel;
use think\response\Json;
use taoler\com\Files;
use app\common\lib\facade\HttpHelper;
use app\common\lib\FileHelper;


class Addons extends AdminController
{
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
     * @param $data
     * @return Json
     */
    public function list()
    {
        $param = Request::param();
        $data = ['page' => $param['page'] ?? 1, 'limit' => $param['limit'] ?? 10, 'type' => $param['type'] ?? 'all'];
        $res = [];
        //本地插件列表
        $localAddons = Files::getDirName('../addons/');

        // 排除公共中间件目录
        $key = array_search('middleware',$localAddons,true);
        if($key !== false) {
            unset($localAddons[$key]);
        }

        if($data['type'] == 'installed') {
            $count = count($localAddons); // 安装总数
            // 已安装
            if ($count) {
                $res = ['code' => 0, 'msg' => 'ok', 'count' => $count];
                // 数组分组
                $arr = array_chunk($localAddons, $data['limit']);
                // 选中的页码数组
                $arrAddon = $arr[$data['page'] - 1];
                // $data数据
                foreach ($arrAddon as $k => $v) {
                    $info_file = '../addons/' . $v . '/info.ini';
                    $info = parse_ini_file($info_file);
                    $info['show'] = $info['status'] ? '启用' : '禁用';
                    $info['install'] = $info['status'] ? '是' : '否';
                    $res['data'][] = $info;
                }
                return json($res);
            }
            return json(['code' => -1, 'msg' => '没有安装任何插件']);
        }

        // 在线插件
        $response = HttpHelper::withHost()->get('/v1/addonlist', $data);
        $addons = $response->toJson();

        if($response->ok()) {
            $res = ['code' => 0, 'msg' => 'ok', 'count' => $addons->count];
            // $data数据 与本地文件对比
            foreach($addons->data as $v){
                if(in_array($v->name, $localAddons)) {
                    $info = get_addons_info($v->name);
                    //已安装
                    $v->isInstall = 1;
                    //判断是否有新版本
                    if($v->version > $info['version']) $v->have_newversion = 1;
                    $v->price =  $v->price ? $v->price : '免费';
                }
                $res['data'][] = $v;
            };
            return json($res);
        }
        return json(['code' => -1, 'msg' => '未获取到服务器信息']);
    }

    // 插件文件升级检查
    protected function addonsFileCheckInstall($name, $url) {

        // 1.判断远程文件是否可用存在
        $header = get_headers($url, true);
        if(!isset($header[0]) && (strpos($header[0], '200') || strpos($header[0], '304'))) {
            throw new Exception('获取远程文件失败');
        }
        
        //拼接路径
        $addons_dir = FileHelper::getDirPath(root_path() . 'runtime' . DS . 'addons');
        if(!is_dir($addons_dir)) Files::mkdirs($addons_dir);

        // 2.把远程文件放入本地
        $package_file = $addons_dir . $name . '.zip';  //升级的压缩包文件路径
        $cpfile = copy($url, $package_file);
        if(!$cpfile) {
            throw new Exception('下载升级文件失败');
        }

        $uzip = new Zip();
        $zipDir = strstr($package_file, '.zip',true);   //返回文件名后缀前的字符串
        $zipPath = FileHelper::getDirPath($zipDir);  //转换为带/的路径 压缩文件解压到的路径
        $unzip_res = $uzip->unzip($package_file, $zipPath, true);
        if(!$unzip_res) {
            throw new Exception('解压失败');
        }

        unlink($package_file);

        //升级前的写入文件权限检查
        $allUpdateFiles = Files::getAllFile($zipPath);

        if (empty($allUpdateFiles)) {
            throw new Exception('无可更新文件!');
        }
    
        $checkString    = '';
        foreach ($allUpdateFiles as $updateFile) {
            $coverFile  = ltrim(str_replace($zipPath, '', $updateFile), DIRECTORY_SEPARATOR);
            $dirPath    = dirname('../'.$coverFile);
            if (file_exists('../'.$coverFile)) {
                if (!is_writable('../'.$coverFile)) $checkString .= $coverFile . '&nbsp;[<span class="text-red">' . '无写入权限' . '</span>]<br>';
            } else {
                if (!is_dir($dirPath)) @mkdir($dirPath, 0777, true);
                if (!is_writable($dirPath)) $checkString .= $dirPath . '&nbsp;[<span class="text-red">' . '无写入权限' . '</span>]<br>';
            }
        }
        if (!empty($checkString)) {
            throw new Exception('$checkString');
        }

        // 拷贝文件
        FileHelper::copyDir(root_path() . 'runtime' . DS . 'addons' . DS . $name . DS, root_path());

        return true;
    }

    /**
     * 安装，
     * @param array $data
     * @return Json
     */
    public function install(array $data = [])
    {
        $data = Request::only(['name','version','uid','token']);
        $data['type'] = 'install';
        
        // 接口
        $response = HttpHelper::withHost()->post('/v1/getaddons', $data)->toJson();
        if($response->code < 0) return json($response);

        try{
            // 文件
            $this->addonsFileCheckInstall($data['name'], $response->addons_src);

            // 执行数据库
            $sqlInstallFile = root_path(). 'addons' . DS . $data['name'] . DS . 'install.sql';
            if(file_exists($sqlInstallFile)) {
                SqlFile::dbExecute($sqlInstallFile);
            }

            //安装菜单
            //$menu = get_addons_menu($data['name']);
            $menu = [];
            $menuFile = app()->getRootPath() . 'addons' . DS . $data['name'] . DS . 'menu.php';
            if(file_exists($menuFile)){
                $menu = include $menuFile;
                if(isset($menu['is_nav']) && $menu['is_nav'] < 8){
                    $pid = $menu['is_nav'];
                } else {
                    //$pid = AuthRule::where('name','addons')->value('id');
                    return json(['code'=>-1,'msg'=> 'is_nav菜单项目设置错误']);
                }
                // 父ID状态为0时打开
                $pidStatus = AuthRule::where('id', $pid)->value('status');
                if($pidStatus < 1) {
                    AuthRule::update(['status' => 1, 'id' => $pid]);
                }
                // 安装菜单
                $menu_arr[] = $menu['menu'];
                $this->addAddonMenu($menu_arr, (int)$pid,1);
            }

            //执行插件安装
            $class = get_addons_instance($data['name']);
            $class->install();

            set_addons_info($data['name'],['status' => 1,'install' => 1]);

        } catch (\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }

        Files::delDirAndFile('../runtime/addons/'.$data['name'] . DS);
        return json(['code' => 0, 'msg' => '插件安装成功！']);

    }

        /**
     * 升级插件
     * @return Json
     * @throws \Exception
     */
    public function upgrade()
    {
        $data = Request::only(['name','uid','token']);
        $plug = get_addons_info($data['name']);
        $data['version'] = $plug['version'];
        $data['type'] = 'upgrade';

        // 接口
        $response = HttpHelper::withHost()->post('/v1/getaddons',$data)->toJson();
        if($response->code < 0) return json($response);

        try {

            // 获取配置信息
            $config = get_addons_config($data['name']);
            $info = get_addons_info($data['name']);
            // 卸载插件
            $class = get_addons_instance($data['name']);
            $class->uninstall();
            //$this->uninstall($data['name']);

            // 卸载菜单
            // $menu = get_addons_menu($data['name']);
            // if(!empty($menu)){
            //     $menu_arr[] = $menu['menu'];
            //     $this->delAddonMenu($menu_arr);
            // }

            // 升级安装
            $this->addonsFileCheckInstall($data['name'], $response->addons_src);

            // 升级sql
            $sqlUpdateFile = root_path()."addons/{$data['name']}/data/update_{$response->version}.sql";
            if(file_exists($sqlUpdateFile)) {
                SqlFile::dbExecute($sqlUpdateFile);
            }
            // 恢复配置
            if(!empty($config)) {
                set_addons_config($data['name'], $config);
            }
            // 回复info
            $info['version'] = number_format($response->version, 1, '.', '');; // 写入版本号
            set_addons_info($data['name'], $info);

            return json(['code' => 0, 'msg' => "{$response->version}版本升级成功！"]);
        } catch (\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 卸载插件
     * @param string $name
     * @return Json
     * @throws \Exception
     */
    public function uninstall(string $name = '')
    {
        $name = input('name') ?? $name;
        // 执行插件卸载
        $class = get_addons_instance($name);
        $class->uninstall();
        // 卸载菜单
        $menu = get_addons_menu($name);
        if(!empty($menu)){
            $menu_arr[] = $menu['menu'];
            $this->delAddonMenu($menu_arr);
        }

        try {
            //卸载插件数据库
            $sqlUninstallFile = root_path().'addons/'.$name.'/uninstall.sql';
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

            if(file_exists($addonsDir)) Files::delDir($addonsDir);
            if(file_exists($admin_controller)) Files::delDir($admin_controller);
            if(file_exists($admin_model)) Files::delDir($admin_model);
            if(file_exists($admin_view)) Files::delDir($admin_view);
            if(file_exists($admin_validate)) Files::delDir($admin_validate);
            if(file_exists($addon_public)) Files::delDir($addon_public);

        } catch (\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }

        return json(['code' => 0, 'msg' => '插件卸载成功']);
    }

    /**
     * 启用禁用插件
     * @return Json
     * @throws Exception
     */
    public function check(){
        $name = input('name');
        $info = get_addons_info($name);
        try{
            $arr = ['status' => $info['status'] ? 0 :1];
            set_addons_info($name,$arr);
            $class = get_addons_instance($name);
            if($arr['status']) {
                $res = ['code'=>0,'msg'=>'启用成功'];
            } else {
                $res = ['code'=>0,'msg'=>'已被禁用'];
            }
            $info['status']==1 ?$class->enabled():$class->disabled();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        return json($res);
    }

    /**
     * 配置插件
     * @param $name
     * @return string|Json
     * @throws \Exception
     */
    public function config()
    {
        $name = input('name');
        $config = get_addons_config($name);

        if(empty($config)) return json(['code'=>-1,'msg'=>'无配置项！无需操作']);
        if(Request::isAjax()){
            $params = Request::param('params/a',[],'trim');

            if ($params) {
                foreach ($config as $k => &$v) {
                    if (isset($params[$k])) {
                        if ($v['type'] == 'array') {
                            $arr = [];
                            $params[$k] = is_array($params[$k]) ? $params[$k] :[];
                            foreach ($params[$k]['key'] as $kk=>$vv){
                                $arr[$vv] =  $params[$k]['value'][$kk];
                            }
                            $params[$k] = $arr;
                            $value = $params[$k];
                            $v['content'] = $value;
                            $v['value'] = $value;
                        } elseif ($v['type'] == 'select'){
                            $value = [(int)$params[$k]];
                            $v['value'] = $value;
                        } else {
                            $value =  $params[$k];
                        }
                        $v['value'] = $value;
                    }
                }
                unset($v);
                set_addons_config($name,$config);

            }
            return json(['code'=>0,'msg'=>'配置成功！']);
        }

        //模板引擎初始化
        $view = ['formData'=>$config,'title'=>'title'];
        View::assign($view);
        $configFile = root_path() . 'addons' . DS . $name . DS . 'config.html';
        $viewFile = is_file($configFile) ? $configFile : '';

        return View::fetch($viewFile);

    }

    /**
     * 添加菜单
     * @param array $menu
     * @param int $pid
     * @param int $type
     * @return void
     * @throws \Exception
     */
    public function addAddonMenu(array $menu,int $pid = 0, int $type = 1)
    {
        foreach ($menu as $v){
            $hasChild = isset($v['sublist']) && $v['sublist'] ? true : false;
            try {
                $v['pid'] = $pid;
                $v['name'] = trim($v['name'],'/');
                $v['type'] = $type;
                $menu = AuthRule::withTrashed()->where('name',$v['name'])->find();
                if($menu){
                    $menu->restore();
                } else {
                    $menu = AuthRule::create($v);
                }

                if ($hasChild) {
                    $this->addAddonMenu($v['sublist'], $menu->id,$type);
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }

    }

    /**
     * 循环删除菜单
     * @param array $menu
     * @param string $module
     * @return void
     * @throws \Exception
     */
    public function delAddonMenu(array $menu,string $module = 'addon')
    {
        foreach ($menu as $k=>$v){
            $hasChild = isset($v['sublist']) && $v['sublist'] ? true : false;
            try {
                $v['name'] = trim($v['name'],'/');
                $menu_rule = AuthRule::withTrashed()->where('name',$v['name'])->find();
                if(!is_null($menu_rule)){
                    $menu_rule->delete(true);
                    if ($hasChild) {
                        $this->delAddonMenu($v['sublist']);
                    }
                }

            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }

    }

    /**
     * 用户登录
     * @return mixed|Json
     */
    public function userLogin()
    {
        $response = HttpHelper::withHost()->post('/v1/user/login', Request::param())->toJson();
        return json($response);
    }

    /**
     * 订单
     * @return string|Json
     */
    public function pay()
    {
        $data = Request::only(['id','name','version','uid','price']);
//        $url = $this->getSystem()['api_url'].'/v1/createOrder';
//        $order = Api::urlPost($url,$data);
        $response = HttpHelper::withHost()->post('/v1/createOrder', $data);
        if ($response->ok()) {
//            $orderData = json_decode(json_encode($response->toJson()->data),TRUE);
            View::assign('orderData',$response->toArray()['data']);
            return View::fetch();
        } else {
            return json($response->toJson());
        }
    }

    /**
     * 支付查询
     * @return Json
     */
    public function isPay()
    {
        $param = Request::only(['name','userinfo']);
        $data = [
            'name'=>$param['name'],
            'uid'=> $param['userinfo']['uid'],
        ];
        $response = HttpHelper::withHost()->post('/v1/ispay', $data)->toJson();
        return json($response);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function add()
    {
        //添加版本
        if(Request::isAjax()){
            $data = Request::param();
            $result = AddonsModel::create($data);
            if($result){
                $res = ['code'=>0,'msg'=>'添加成功'];
            }else{
                $res = ['code'=>-1,'msg'=>'添加失败'];
            }
            return json($res);
        }
        return View::fetch();
    }

    /**
     * 上传插件文件zip
     * @return Json
     */
    public function uploadZip()
    {
        $id = Request::param();
        $file = request()->file('file');
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
    public function uploads()
    {
        $type = Request::param('type');
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


}
