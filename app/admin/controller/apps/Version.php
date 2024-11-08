<?php
/**
 * @Program: TaoLer 2023/3/12
 * @FilePath: app\admin\controller\apps\Version.php
 * @Description: Version
 * @LastEditTime: 2023-03-12 20:26:12
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\apps;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;

use app\api\model\Version as VersionModel;

class Version extends AdminController
{
    /**
     * @return string|\think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        return View::fetch();
    }

    public function list()
    {
        if(Request::isAjax()){
            $versions =  VersionModel::order('id', 'desc')->paginate([
                'list_rows'=> input('limit'),
                'page' => input('page'),
            ])->toArray();

            if($versions['total']){
                return json(['code'=>0,'msg'=>'','count'=>$versions['total'], 'data' => $versions['data']]);
            }
                $res = json(['code'=>-1,'msg'=>'no data']);

        }
    }

    /**
     * @return string|\think\response\Json
     */
    public function add()
    {
        //添加版本
        if(Request::isAjax()){
            $data = Request::only(['pname','version_name','version_resume','version_src']);
            $result = VersionModel::create($data);
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
     * @param $id
     * @return string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {
        if(Request::isAjax()){
            $data = Request::only(['id','pname','version_name','version_resume','version_src']);
            $version = VersionModel::find($data['id']);
            $ver_src = $version['version_src']; //原版本
            $result = $version->where('id',$id)->save($data); //更新现在版本
            if($result){
                //查出并删除原版本
                if(($data['version_src'] != $ver_src) && file_exists($ver_src)){
                    unlink('.'.$ver_src);
                }
                $res = ['code'=>0,'msg'=>'编辑成功'];
            }else{
                $res = ['code'=>-1,'msg'=>'编辑失败'];
            }
            return json($res);
        }
        $version = VersionModel::find($id);
        View::assign('version',$version);
        return View::fetch();
    }

    /**
     * @param $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete($id)
    {
        $version = VersionModel::find($id);
        $res = $version->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        } else {
            return json(['code'=>-1,'msg'=>'删除失败']);
        }
    }

    /**
     * @return \think\response\Json
     */
    public function uploadZip()
    {
        $uploads = new \app\common\lib\Uploads();
        $upRes = $uploads->put('file','SYS_app',20480, 'application','sha1');
        $verJson = $upRes->getData();

        if($verJson['status'] == 0){
            $res = ['code'=>0,'msg'=>'上传app成功','src'=>$verJson['url']];
        } else {
            $res = ['code'=>-1,'msg'=>'上传错误'];
        }
        return json($res);

//         $file = request()->file('file');
// 		try {
// 			validate(['file'=>'filesize:2048|fileExt:zip,rar,7z'])
//             ->check(array($file));
// 			$savename = \think\facade\Filesystem::disk('public')->putFile('version',$file);
// 		} catch (think\exception\ValidateException $e) {
// 			echo $e->getMessage();
// 		}
// 		$upload = Config::get('filesystem.disks.public.url');

// 		if($savename){
//             $name_path =str_replace('\\',"/",$upload.'/'.$savename);
// 				$res = ['code'=>0,'msg'=>'上传version成功','src'=>$name_path];
// 			} else {
// 				$res = ['code'=>-1,'msg'=>'上传错误'];
// 			}
// 		return json($res);
    }



    // 审核
    public function check()
    {
        $param = Request::only(['id','name','value']);
        $data = ['id'=>$param['id'],$param['name']=>$param['value']];

        //获取状态
        $res = Db::name('version')->save($data);
        if($res){
            return json(['code'=>0,'msg'=>'设置成功','icon'=>6]);
        }else {
            return json(['code'=>-1,'msg'=>'失败啦','icon'=>6]);
        }
    }
}
