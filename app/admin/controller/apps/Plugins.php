<?php
/**
 * @Program: TaoLer 2023/3/13
 * @FilePath: app\admin\controller\apps\Plugins.php
 * @Description: Plugins
 * @LastEditTime: 2023-03-13 11:21:36
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\apps;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use app\api\model\Plugins as PluginsModel;


class Plugins extends AdminController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
		return View::fetch();
    }

    public function list()
    {
        if(Request::isAjax()){
            $data = Request::only(['app_name']);
            if(isset($data['app_name'])) {
                $data = ['app_id' => Db::name('app')->where('app_name', $data['app_name'])->value('id')];
            }
            $map = array_filter($data);
            $plugins = PluginsModel::where($map)->with('app')->order('create_time desc')->paginate([
                'list_rows' => input('limit'),
                'page' => input('page')
            ])->toArray();

            $res = [];
            if($plugins['total']){
                foreach($plugins['data'] as $k=>$v){
                    $res['data'][] = [
                        'id'    => $v['id'],
                        'plugins_name'      => $v['app']['app_name'],
                        'plugins_title'     => $v['app']['app_title'],
                        'plugins_version'   => $v['plugins_version'],
                        'plugins_author'    => $v['app']['app_author'],
                        'description'       => $v['description'],
                        'plugins_price'     => $v['plugins_price'],
                        'status'            => $v['status'],
                        'ctime'             => $v['create_time']];
                }
                return json(['code'=>0,'msg'=>'','count'=>$plugins['total'], 'data'=> $res['data']]);
            }
            return json(['code'=>-1,'msg'=>'还没有插件发布']);
        }
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
			$result = PluginsModel::create($data);
			if($result){
				$res = ['code'=>0,'msg'=>'添加成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'添加失败'];
			}
		return json($res);
		}
		$apps = Db::name('app')->field('id,app_name,app_type')->where(['app_status'=>1, 'app_type'=>2, 'delete_time'=>0])->select();
        
        View::assign('apps',$apps);
		return View::fetch();
    }


    /**
     * 编辑版本
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
		$plugins = PluginsModel::find($id);
		if(Request::isAjax()){
			$data = Request::only(['id','app_id','plugins_version','description','plugins_price','plugins_src']);
			$result = $plugins->where('id',$id)->save($data);
			if($result){
				$res = ['code'=>0,'msg'=>'编辑成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'编辑失败'];
			}
			return json($res);
		}
        $apps = Db::name('app')->field('id,app_name,app_type')->where(['app_status'=>1, 'app_type'=>2,'delete_time'=>0])->select();
        View::assign('apps',$apps);
		View::assign('plugins',$plugins);
		return View::fetch();
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
		$version = PluginsModel::find($id);
		$res = $version->delete();
		if($res){
			return json(['code'=>0,'msg'=>'删除成功']);
		} else {
			return json(['code'=>-1,'msg'=>'删除失败']);
		}
    }
	
	// 审核插件
	public function check()
	{
		$data = Request::only(['id','status']);

		//获取状态
		$res = Db::name('plugins')->where('id',$data['id'])->save(['status' => $data['status']]);
		if($res){
			if($data['status'] == 1){
				return json(['code'=>0,'msg'=>'审核通过','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'被禁止','icon'=>5]);
			}
			
		}else {
			return json(['code'=>-1,'msg'=>'审核出错']);
		}
	}

    /**
     * 上传版本的zip资源
     * @param
     * @param  int  $id
     * @return \think\Response
     */
    public function uploadZip()
    {
        //$id = Request::param();
        $uploads = new \app\common\lib\Uploads();
        $upRes = $uploads->put('file','SYS_plugins',204800, 'application');
        $plugJson = $upRes->getData();

        if($plugJson['status'] == 0){
            $res = ['code'=>0,'msg'=>'上传插件成功','src'=>$plugJson['url']];
        } else {
            $res = ['code'=>-1,'msg'=>'上传错误'];
        }
        return json($res);
    }

}
