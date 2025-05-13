<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\addon\Template.php
 * @Description: Template
 * @LastEditTime: 2023-03-14 16:52:56
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\addon;

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
use app\common\lib\facade\HttpHelper;

class Template extends AdminBaseController
{
    protected string $frameworkVersion;
    protected array $info;

    public function initialize()
    {
        parent::initialize();

        $this->frameworkVersion = config('taoler.version');

    }

    // 本地模板放最前面
    public function index()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 8);
        
        $nowTplName = $this->getSystem()['template'];
        $infoArr = $this->getViewInfos();
        $localInfoNum = count($infoArr);

        $limitNum = $limit - $localInfoNum;

        $datas = [];

        $list = HttpHelper::withHost()->post('/v1/template/index',[
            'page' => $page,
            'limit' => $limitNum
        ])->toJson();

        if($list->code > 0) {
            foreach($list->data as $v) {

                $data = [];
                // 是否可升级标志
                $data['update'] = false;
                // 启用标志
                $data['enable'] = false;
                // 是否已下载本地
                $data['local'] = false;
 
                if(array_key_exists($v->name, $infoArr)) {
                    $data['local'] = true;
                    if (version_compare($v->version, $infoArr[$v->name]['version'], '>')) {
                        $data['update'] = true;
                    }
                }
    
                if($v->name === $nowTplName) {
                    $data['enable'] = true;
                }
                // var_dump($v);

                $datas[] = array_merge((array)$v, $data); 
            }
        }

        // 本地模板
        $listNameArr = array_column($datas, 'name');

        foreach($infoArr as $k => $v) {
            if(!in_array($k, $listNameArr)) {

                $infoPath = str_replace('\\','/', root_path().'view/'.$k.'/info.ini');
                if(file_exists($infoPath)) {
                    // 单文件配置
                    $ini = $this->getViewInfo($infoPath);

                    // 本地放数组最前面
                    array_unshift($datas, $ini);
                }
            }
        }

        View::assign('template', $datas);

        return View::fetch();
    }

    // 获取所有模板配置文件
    public function getViewInfos(): array
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
    public function getViewInfo(string $name): array
    {
        if(file_exists($name)) {
            $infoPath = $name;
        } else {
            $infoPath = str_replace('\\','/', root_path().'view/'.$name.'/info.ini');
        }

        $info = [];
        $nowTplName = $this->getSystem()['template'];
        if(file_exists($infoPath)) {
            $ini = parse_ini_file($infoPath);
            $ini['update'] = false;
            $ini['local'] = true;
            // 是否在使用中
            if($ini['name'] === $nowTplName) {
                $ini['enable'] = true;
            } else {
                $ini['enable'] = false;
            }
            unset($ini['web']);

            $info = array_merge($info, $ini);
        }
        
        return $info;
    }

    // 安装，只能安装最新版
    public function install() {

        $name = Request::param('name');
        $frameworkVersion = config('taoler.version');

        $tpl = HttpHelper::withHost()->post('/v1/template/install', [
            'name' => $name,
            'frame_version' => $frameworkVersion
        ])->toJson();

        if($tpl->code < 0 ) {
            return $tpl;
        }       

        $viewPath = str_replace('\\','/',root_path()."runtime/view/$name/");

        try {

            $tplZip = $viewPath."$name.zip";
            // 下载文件
            FileHelper::downloadFile($tpl->data->url, $tplZip);
            // 解压zip到runtime目录
            FileHelper::unZip($tplZip, $viewPath, true);

            // 只能复制限定路径的目录，避险
            $reserve = "view/$name";
            // 复制
            FileHelper::copyFolder($viewPath, root_path(), $reserve);
            // 删除
            FileHelper::deleteFolder($viewPath);
            
        } catch(Exception $e) {
            // throw new Exception('解压缩失败'.$e->getMessage());
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }

        return json(['code'  => 1,'msg'   => 'ok']);
    }

    // 启用
    public function enable() {
        $name = Request::param('name');

        try{
            Db::name('system')->cache(true)->where('id', 1)->update([
                'template' => $name
            ]);

            Db::name('cate')->where('status', 1)->update(['detpl' => 'default']);

            return json(['code'  => 1,'msg'   => 'ok']);
        } catch(Exception $e) {
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }

    }

    // 删除
    public function delete() {
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

    // 更新
    public function update()
    {
        $name = Request::param('name');
    
        // 单文件配置
        $info = $this->getViewInfo($name);

        // 框架
        $frameworkVersion = config('taoler.version');

        $tpl = HttpHelper::withHost()->post('/v1/template/update', [
            'name'          => $name,
            'version'       => $info['version'],
            'frame_version' => $frameworkVersion
        ])->toJson();

        if($tpl->code < 0 ) {
            return $tpl;
        } 

        $viewPath = str_replace('\\','/',root_path()."runtime/view/$name/");
        
        try {
            $tplZip = $viewPath."$name.zip";
            // 下载文件
            FileHelper::downloadFile($tpl->data->url, $tplZip);
            // 解压zip到runtime目录
            FileHelper::unZip($tplZip, $viewPath, true);

            FileHelper::copyFolder($viewPath, root_path(), "view/$name");
            FileHelper::deleteFolder($viewPath);
            
            
        } catch(Exception $e) {
            // throw new Exception('更新失败'.$e->getMessage());
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }


       

        return json(['code'  => 1,'msg'   => 'ok']);
    }

}