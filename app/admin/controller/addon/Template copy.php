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

        $frameworkVersion = config('taoler.version');
        $infoArr = $this->getViewInfos();
        $localInfoNum = count($infoArr);

        $list = Db::name('template_version')
        ->alias('v')
        ->join('template t','v.template_id = t.id')
        ->field('template_id,name,max(v.version) as version, max(v.create_time) as time,thum_img,description,max(ver_desc) as ver_desc')
        ->group('template_id')
        ->limit($limit - $localInfoNum)
        ->paginate([
            'list_rows'=> $limit,
            'page' => $page,
        ]);

halt($list->items());
        $datas = [];

        foreach($list as $v) {

            $data = [];
            // 是否可升级标志
            $data['update'] = false;
            // 启用标志
            $data['enable'] = false;
            // 是否已下载本地
            $data['local'] = false;

            if(array_key_exists($v['name'], $infoArr)) {
                $data['local'] = true;
                if (version_compare($v['version'], $infoArr[$v['name']]['version'], '>')) {
                    $data['update'] = true;
                }
            }

            if($v['name'] === $nowTplName) {
                $data['enable'] = true;
            }

            $datas[] = array_merge($v, $data); 
        }

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

        $tpl = Db::name('template_version')
        ->alias('v')
        ->join('template t', 'v.template_id = t.id')
        ->field('v.id,name,template_id,framework,version,url')
        ->where('t.name', $name)
        ->where('v.status', 1)
        ->order('v.id', 'desc')
        ->find();

        if(version_compare($frameworkVersion, $tpl['framework'], '<')) {
            return json(['code' => 0, 'msg' => '请先升级框架到'.$tpl['framework']]);
        }

        $viewPath = str_replace('\\','/',root_path().'runtime/view/');
        
        if(!file_exists($viewPath)) {
            mkdir($viewPath,0777,true);
        }

        try {

            $tplZip = $viewPath."$name.zip";
            // 下载文件
            FileHelper::downloadFile(Request::domain().$tpl['url'], $tplZip);
            // 解压zip到runtime目录
            FileHelper::unZip($tplZip, $viewPath, true);

            // 保留目录，避险
            $reserve = [
                str_replace('\\','/', root_path()."runtime/view/view/$name"),
                str_replace('\\','/', root_path()."runtime/view/public/static/tpl/$name"),
            ];
    
            FileHelper::copyFolder($viewPath, root_path(), $reserve);
            FileHelper::deleteFolder($viewPath.'view', root_path(), $reserve, true);
            
        } catch(Exception $e) {
            throw new Exception('解压缩失败'.$e->getMessage());
        }

        return json(['code'  => 1,'msg'   => 'ok']);
    }

    // 启用
    public function enable() {
        $name = Request::param('name');

        try{
            Db::name('system')->cache('system')->where('id', 1)->update([
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

        $id = Db::name('template')->where('name', $name)->value('id');
        
        if(is_null($id)) {
            return json(['code' => 0, 'msg' => '此模板暂时不可用']);
        }

        $tpl = Db::name('template_version')->where('template_id', $id)->where('version', '>', $info['version'])->find();

        if(version_compare($this->frameworkVersion, $tpl['framework'], '<')) {
            return json(['code' => 0, 'msg' => '请先升级框架到'.$tpl['framework']]);
        }

        $viewPath = str_replace('\\','/',root_path().'runtime/view/');
        
        if(!file_exists($viewPath)) {
            mkdir($viewPath,0777,true);
        }

        try {
            $tplZip = $viewPath."$name.zip";
            // 下载文件
            FileHelper::downloadFile(Request::domain().$tpl['url'], $tplZip);
            // 解压zip到runtime目录
            FileHelper::unZip($tplZip, $viewPath, true);

            // 保留目录
            $reserve = [
                str_replace('\\','/', root_path()."runtime/view/view/$name"),
                str_replace('\\','/', root_path()."runtime/view/public/static/tpl/$name"),
            ];
    
            FileHelper::copyFolder($viewPath, root_path(), $reserve);
            FileHelper::deleteFolder($viewPath.'view', root_path(), $reserve, true);
            
            
        } catch(Exception $e) {
            throw new Exception('更新失败'.$e->getMessage());
        }


       

        return json(['code'  => 1,'msg'   => 'ok']);
    }

}