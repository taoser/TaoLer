<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\apps\App.php
 * @Description: App
 * @LastEditTime: 2023-03-14 10:34:03
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\apps;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use app\api\model\App as AppModel;


class App extends AdminController
{
    /**
     * 浏览
     * @return string
     */
    public function index()
    {
        return View::fetch();
    }

    /**
     * APP列表
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function list()
    {
        $app = AppModel::paginate([
            'list_rows'=> input('limit'),
            'page' => input('page'),
        ])->toArray();

        if($app['total']){
            return json(['code'=>0,'msg'=>'ok','count' => $app['total'], 'data' => $app['data']]);
        }
        return json(['code'=>-1,'msg'=>'No data']);
    }

    /**
     * 添加
     * @return string|\think\response\Json
     */
    public function add()
    {
        //添加版本
        if(Request::isAjax()){
            $data = Request::param();
            $result = AppModel::create($data);
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
        $app = AppModel::find($id);

        if(Request::isAjax()){
            $data = Request::only(['id','app_name','app_title','app_type','app_author','app_description','app_price','app_image']);
            $result = $app->where('id',$id)->save($data);
            if($result){
                $res = ['code'=>0,'msg'=>'编辑成功'];
            }else{
                $res = ['code'=>-1,'msg'=>'编辑失败'];
            }
            return json($res);
        }
        View::assign('app',$app);
        return View::fetch();
    }

    /**
     * 上传版本的zip资源
     *
     * @param
     * @param  int  $id
     * @return \think\Response
     */
    public function uploadImg()
    {
        //$id = Request::param();
        $uploads = new \app\common\lib\Uploads();
        $upRes = $uploads->put('file','SYS_app',204800, 'image');
        $app = $upRes->getData();

        if($app['status'] == 0){
            $res = ['code'=>0,'msg'=>'上传图片成功','image'=>$app['url']];
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
        $version = appModel::find($id);
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
        $data = Request::only(['id','app_status']);
        //获取状态
        $res = Db::name('app')->where('id',$data['id'])->save(['app_status' => $data['app_status']]);
        if($res){
            if($data['app_status'] == 1){
                return json(['code'=>0,'msg'=>'审核通过','icon'=>6]);
            } else {
                return json(['code'=>0,'msg'=>'被禁止','icon'=>5]);
            }

        }else {
            return json(['code'=>-1,'msg'=>'审核出错']);
        }
    }
}
