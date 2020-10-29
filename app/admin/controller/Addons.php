<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;
use think\exception\ValidateException;
use app\admin\model\Addons as AddonsModel;
use taoler\com\Files;

class Addons extends AdminController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        if(Request::isAjax()){
			
			var_dump(Files::getDirName('../addons/'));

		}
		return View::fetch();
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
     * 编辑版本
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
		$addons = AddonsModel::find($id);

		if(Request::isAjax()){
			$data = Request::only(['id','addons_name','addons_version','addons_auther','addons_resume','addons_price','addons_src']);
			$result = $addons->where('id',$id)->save($data);
			if($result){
				$res = ['code'=>0,'msg'=>'编辑成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'编辑失败'];
			}
			return json($res);
		}
		View::assign('addons',$addons);
		return View::fetch();
    }

    /**
     * 上传版本的zip资源
     *
     * @param
     * @param  int  $id
     * @return \think\Response
     */
    public function uploadZip()
    {
		$id = Request::param();
        $file = request()->file('file');
		try {
			validate(['file'=>'filesize:2048|fileExt:zip,rar,7z'])
            ->check(array($file));
			$savename = \think\facade\Filesystem::disk('public')->putFile('addons',$file);
		} catch (think\exception\ValidateException $e) {
			echo $e->getMessage();
		}
		$upload = Config::get('filesystem.disks.public.url');
		
		if($savename){
            $name_path =str_replace('\\',"/",$upload.'/'.$savename);
				$res = ['code'=>0,'msg'=>'插件上传成功','src'=>$name_path];
			} else {
				$res = ['code'=>-1,'msg'=>'上传错误'];
			}
		return json($res);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
		$version = AddonsModel::find($id);
		$res = $version->delete();
		if($res){
			return json(['code'=>0,'msg'=>'删除成功']);
		} else {
			return json(['code'=>-1,'msg'=>'删除失败']);
		}
    }
}
