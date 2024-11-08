<?php
/**
 * @Program: TaoLer 2023/3/12
 * @FilePath: app\admin\controller\apps\KeyAuth.php
 * @Description: KeyAuth 授权管理
 * @LastEditTime: 2023-03-12 18:16:06
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\apps;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use app\api\model\UpgradeAuth;

class KeyAuth extends AdminController
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
     * key列表
     * @return \think\response\Json|void
     * @throws \think\db\exception\DbException
     */
	 public function list()
    {
		if(Request::isAjax()){
			$data = Request::only(['user','domain','key','status']);
			$map = array_filter($data);
			//状态为0待审核
			if(isset($data['status']) && $data['status'] == 0){
                $map['status'] = 0;
            }

			$keys = UpgradeAuth::where($map)->order('create_time', 'desc')->paginate([
                'list_rows' => input('limit'),
                'page' => input('page')
            ])->toArray();

			if($keys['total']){
                return json(['code'=>0,'msg'=>'','count'=>$keys['total'], 'data' => $keys['data']]);
			}
			return json(['code'=>-1,'msg'=>'没有数据']);
		} 

    }

    /**
     * 添加
     * @return string|\think\response\Json
     */
    public function add()
    {
		if(Request::isAjax()){
			$data = Request::param();
			$data['end_time'] =  strtotime($data['end_time']);
			$data['key'] = sha1(substr_replace($data['domain'],$data['user'],0,0));
			$result = UpgradeAuth::create($data);
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
     * 编辑
     * @return string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
		$keys = UpgradeAuth::find(input('id'));
		if(Request::isAjax()){
			$data = Request::param();
			$data['end_time'] =  strtotime($data['end_time']);
			$result = $keys->save($data);
			if($result){
				$res = ['code'=>0,'msg'=>'编辑成功'];
			} else {
				$res = ['code'=>-1,'msg'=>'编辑失败'];
			}
			return json($res);
		}
		$keys = $keys->getData();
		View::assign('keys',$keys);
		return View::fetch();
    }

    /**
     * 删除
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete()
    {
		$keys = UpgradeAuth::find(input('id'));
		$res = $keys->delete();
		if($res){
			return json(['code'=>0,'msg'=>'删除成功']);
		}
        return json(['code'=>-1,'msg'=>'删除失败']);
    }

    //审核
    public function check()
    {
        $data = Request::only(['id','status']);
        //获取状态
        $res = Db::name('upgrade_auth')->where('id',$data['id'])->save(['status' => $data['status']]);
        if($res){
            if($data['status'] == 1) return json(['code'=>0,'msg'=>'审核通过','icon'=>6]);
            return json(['code'=>0,'msg'=>'被禁止','icon'=>5]);
        }
        return json(['code'=>-1,'msg'=>'审核出错']);

    }



}
