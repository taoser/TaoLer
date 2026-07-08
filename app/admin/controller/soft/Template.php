<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\soft\Template.php
 * @Description: Template
 * @LastEditTime: 2026-03-14 16:52:56
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\soft;

use app\admin\controller\AdminBaseController;
use Exception;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use taoler\com\Files;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use ZipArchive;
use app\common\lib\FileHelper;
use app\common\facade\HttpHelper;

class Template extends AdminBaseController
{
    protected string $frameworkVersion;
    protected array $info;
    protected $http;

    public function initialize()
    {
        parent::initialize();

        $this->frameworkVersion = config('taoler.version');

        $this->http = new HttpHelper();
    }

    // 本地模板放最前面
    public function index()
    {
        return View::fetch();
    }

    public function list()
    {
        $page = $this->request->param('page/d', 1);
        $limit = $this->request->param('limit/d', 8);
        $type = $this->request->param('type/s', 'all');
        $appName = $this->request->param('app_name/s', '');

        // 当前模板
        $currentTplName = $this->getSystem()['template'];
        // 已安装模板
        $localTpl = $this->getAllTplInfo();

        // 已安装模板名称数组
        $tplNameArr = array_keys($localTpl);

        $result = HttpHelper::withHost()->post('/v2/template/list', [
            'type'          => $type,
            'app_name'      => $appName,
            'installed_name'=> $tplNameArr,
            'page'          => $page,
            'limit'         => $limit
        ]);

        if(!HttpHelper::ok()) {
            return json(['code' => -1, 'msg' => $result->getLastError()]);
        }

        $datas = [];

        try {
            $response = $result->toJson();

            // 已安装模板
            if($type === 'installed') {
                // 搜索本地模板
                if(!empty($appName)) {
                    if(array_key_exists($appName, $localTpl)) {
                        $local = $localTpl[$appName];
                        $localTpl = []; // 清空数组
                        $localTpl[] = $local;
                    }
                }
                // 已安装模板数量
                $localCount = count($localTpl);

                if($localCount === 0) {
                    return json(['code' => -1, 'msg' => 'success', 'count' => 0, 'data' => []]);
                }

                foreach($localTpl as $local) {
                    $data = [];
                    $data['update']     = false;
                    $data['installed']  = true;
                    $data['enable']     = $local['name'] === $currentTplName;
                    // 在线和本地版本对比
                    if($response->code === 0) {
                        foreach($response->data as $online) {
                            if($online->name !== $local['name']) {
                                continue;
                            }
                            if (version_compare($online->version, $local['version'], '>')) {
                                $data['update'] = true;
                            }
                        }
                    }
                    $datas[] = array_merge((array)$local, $data); 
                }

                return json(['code' => 0, 'msg' => 'success', 'count' => $localCount, 'data' => $datas]);

            }

            if($response->code !== 0) {
                return json($response);
            }

            // 所有在线模板
            if($response->count === 0) {
                return json(['code' => -1, 'msg' => 'no data', 'count' => 0, 'data' => []]);
            }
            
            foreach($response->data as $v) {

                $data = [];
                // 是否可升级标志
                $data['update'] = false;
                // 启用标志
                $data['enable'] = $v->name === $currentTplName;
                // 是否已下载本地
                $data['installed'] = false;

                $v->price = $v->price > 0 ? $v->price : '免费';

                if(array_key_exists($v->name, $localTpl)) {
                    $data['installed'] = true;
                    if (version_compare($v->version, $localTpl[$v->name]['version'], '>')) {
                        $data['update'] = true;
                    }
                }

                $datas[] = array_merge((array)$v, $data); 
            }
            

            // // 本地模板合同
            // $listNameArr = array_column($datas, 'name');

            // foreach($localTpl as $k => $v) {
            //     if(!in_array($k, $listNameArr)) {

            //         $infoPath = str_replace('\\','/', root_path().'view/'.$k.'/info.ini');
            //         if(file_exists($infoPath)) {
            //             // 单文件配置
            //             $ini = $this->getTplInfo($infoPath);

            //             // 本地放数组最前面
            //             array_unshift($datas, $ini);
            //         }
            //     }
            // }

            return json(['code' => 0, 'msg' => 'success', 'data' => $datas]);

        } catch (\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }


    }

    // 安装，只能安装最新版
    public function install() {

        $params = Request::param(['name', 'token', 'version']);
        $params['type'] = 'install';
        $params['framework'] = config('taoler.version');
        $name = $params['name'];
    
        $result = HttpHelper::withHost()->post('/v2/template/get', $params);

        if(!$result->ok()) {
            return json(['code' => -1, 'msg' => $result->getLastMessage()]);
        }

        try {
            $response = $result->toJson();
            if($response->code !== 0) {
                return json($response);
            }

            $viewPath = str_replace('\\','/',root_path()."runtime/view/$name/");
            $tplZip = $viewPath."$name.zip";
            // 下载文件
            FileHelper::downloadFile($response->data->url, $tplZip);
            // 解压zip到runtime目录
            FileHelper::unZip($tplZip, $viewPath, true);

            // 只能复制限定路径的目录，避险
            $reserve = "view/$name";
            // 复制
            FileHelper::copyFolder($viewPath, root_path(), $reserve);
            // 删除
            FileHelper::deleteFolder($viewPath);

            return json(['code'  => 0, 'msg'   => 'ok']);
            
        } catch(Exception $e) {
            // throw new Exception('解压缩失败'.$e->getMessage());
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
        
    }

    // 升级
    public function upgrade()
    {
        $params = Request::param(['name', 'version', 'token']);
        $params['type'] = 'upgrade';
        $params['framework'] = config('taoler.version');
    
        $name = $params['name'];
    
        // 单文件配置
        $info = $this->getViewInfo($name);

        $result = HttpHelper::withHost()->post('/v2/template/update', $params);
        if(!$result->ok()) {
            return json(['code' => -1, 'msg' => $result->getLastMessage()]);
        }
        
        try {

            $response = $result->toJson();
            if($response->code < 0 ) {
                return $response;
            } 

            $viewPath = str_replace('\\','/',root_path()."runtime/view/$name/");
        
            $tplZip = $viewPath."$name.zip";
            // 下载文件
            FileHelper::downloadFile($response->data->url, $tplZip);
            // 解压zip到runtime目录
            FileHelper::unZip($tplZip, $viewPath, true);

            FileHelper::copyFolder($viewPath, root_path(), "view/$name");
            FileHelper::deleteFolder($viewPath);
            
            return json(['code'  => 0,'msg'   => 'ok']);

        } catch(Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }

    }

    // 启用
    public function enable() {
        $name = Request::param('name');

        try{
            Db::name('system')
            ->cache(true)
            ->where('id', 1)
            ->update(['template' => $name]);

            Db::name('cate')->where('status', 1)->update(['tpl' => 'default']);

            return json(['code'  => 0,'msg'   => 'ok']);
        } catch(Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }

    }

    // 删除
    public function uninstall() {
        $name = Request::param('name');

        $infoArr = $this->getViewInfos();
        if(count($infoArr) == 1) {
            return json(['code' => 0, 'msg' => '需要保留一个模板']);
        }

        $viewPath = str_replace("\\", "/", root_path().'/view/'.$name);
        $staticPath = str_replace("\\", "/", public_path().'static/tpl/'.$name);

        try{
            Files::delDir($viewPath);
            Files::delDir($staticPath);

            return json([
                'code'  => 1,
                'msg'   => 'ok'
            ]);
        } catch(Exception $e) {
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }
        
    }

    /**
     * 订单
     * @return string|Json
     */
    public function pay()
    {
        $data = Request::only(['name','token']);
        $response = HttpHelper::withHost()->post('/v2/template/pay', $data);
        if(!$response->ok()) {
            return json(['code'=>-1,'msg'=>$response->getLastError()]);
        }

        try{
            return json($response->toJson());
        } catch (Exception $e) {
            return json(['code'=>-1,'msg'=>$e->getMessage()]);
        }
    }

    /**
     * 支付查询
     * @return Json
     */
    public function isPay()
    {
        $param = Request::only(['out_order_no','token']);
        $response = HttpHelper::withHost()->post('/v2/template/ispay', $param);
            
        if(!HttpHelper::ok()) {
            return json(['code'=>-1,'msg'=>$response->getLastMessage()]);
        }
        try{
            return json($response->toJson());
        } catch (Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
    }

    // 获取所有模板配置文件
    protected function getAllTplInfo(): array
    {
        $viewPath = root_path() . 'view';
        $tpl = scandir($viewPath);
		
		$iniArr = [];
		foreach($tpl as $item) {
			if ($item !== '.' && $item !== '..') {
				$itemPath = $viewPath.'/'.$item;
				if(is_dir($itemPath)) {
					$infoPath = $itemPath . '/info.ini';
					if (file_exists($infoPath)) {
						$iniArr[$item] = parse_ini_file($infoPath);
					}
					
				}
			}
		}

        return $iniArr;
    }

    // 获取本地模板配置信息，可以传入路径或者模板名称
    protected function getTplInfo(string $name): array
    {
        if(file_exists($name)) {
            $infoPath = $name;
        } else {
            $infoPath = str_replace('\\','/', root_path().'view/'.$name.'/info.ini');
        }

        $info = [];
        $currentTplName = $this->getSystem()['template'];
        if(file_exists($infoPath)) {
            $ini = parse_ini_file($infoPath);
            $ini['update'] = false;
            $ini['installed'] = true;
            // 是否在使用中
            if($ini['name'] === $currentTplName) {
                $ini['enable'] = true;
            } else {
                $ini['enable'] = false;
            }
            unset($ini['web']);

            $info = array_merge($info, $ini);
        }
        
        return $info;
    }

}