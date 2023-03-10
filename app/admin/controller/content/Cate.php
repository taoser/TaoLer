<?php

namespace app\admin\controller\content;

use app\common\controller\AdminController;
use app\common\model\Cate as CateModel;
use app\common\model\Article;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use taoler\com\Files;
use app\common\lib\Msgres;

class Cate extends AdminController
{
    /**
     * 浏览
     * @return string
     */
	public function index()
	{
        //详情模板
        $sys = $this->getSystem();
        $template = Files::getDirName('../view/'.$sys['template'].'/index/article/');
        View::assign(['template'=>$template]);
        return View::fetch();
	}

	//帖子分类
	public function list()
	{
        $cate = new CateModel();
		if(Request::isAjax()){
            return $cate->getList();
		}

	}

    // 应用下article/view模板
    public function getAppNameView()
    {
        $appName = input('appname') ?: 'index';
        $sys = $this->getSystem();
        if(is_dir(root_path() . 'app' . DS . $appName . DS . 'view' . DS)){
            $viewPath = root_path() . 'app' . DS . $appName . DS . 'view' . DS . 'article' . DS;
        } elseif(is_dir(root_path() . 'view' . DS . $sys['template'] . DS)) {
            $viewPath = root_path() . 'view' . DS . $sys['template'] . DS . 'index' . DS . 'article' . DS;
        } else {
            $viewPath = '';
        }
        $template = Files::getDirName($viewPath);
        return json(['data' => $template]);
    }
	
	//添加和编辑帖子分类
	public function addEdit()
	{
		$addOrEdit = !is_null(input('id'));//true是编辑false新增
		$msg = $addOrEdit ? lang('edit') : lang('add');
		if(Request::isAjax()) {
			$data = Request::param();
			if(isset($data['id']) && $data['pid'] == $data['id']) return json(['code'=>-1,'msg'=> $msg.'不能作为自己的子类']);
			$list = Db::name('cate')->cache('catename')->save($data);
		
			if($list){
				return json(['code'=>0,'msg'=> $msg.'分类成功']);
			}else{
				return json(['code'=>-1,'msg'=> $msg.'分类失败']);
			}
		}
		//详情模板
		$sys = $this->getSystem();
		$template = Files::getDirName('../view/'.$sys['template'].'/index/article/');
		// 如果是新增，pid=0,detpl默认第一个子模块，如果是编辑，查询出cate
		$cate = $addOrEdit ? Db::name('cate')->field('detpl,pid,appname')->where(['delete_time' =>0])->find((int) input('id')) : ['pid'=>0,'detpl'=>$template[0],'appname'=>'index'];
        // app下前台带模板的应用
        $appArr = [];
        if(is_dir(root_path() . 'app' . DS . 'home')) {
            $appArr = ['index','home'];
        } else {
            $appArr = ['index'];
        }
		View::assign(['template'=>$template,'cate'=>$cate, 'appname' => $appArr]);
		return View::fetch();
	}

    //添加分类
    public function add()
    {
        $addOrEdit = !is_null(input('id'));//true是编辑false新增

        if(Request::isAjax()) {
            $data = Request::param();
            if(isset($data['id']) && $data['pid'] == $data['id']) return json(['code'=>-1,'msg'=> '不能作为自己的子类']);
            $list = Db::name('cate')->cache('catename')->save($data);

            if($list){
                return json(['code'=>0,'msg'=> '添加成功']);
            }else{
                return json(['code'=>-1,'msg'=> '添加失败']);
            }
        }
        //详情模板
        $sys = $this->getSystem();
        $template = Files::getDirName('../view/'.$sys['template'].'/index/article/');
        // 如果是新增，pid=0,detpl默认第一个子模块，如果是编辑，查询出cate
        $cate = ['pid'=>0,'detpl'=>$template[0],'appname'=>'index'];
        // app下前台带模板的应用
        $appArr = [];
        if(is_dir(root_path() . 'app' . DS . 'home')) {
            $appArr = ['index','home'];
        } else {
            $appArr = ['index'];
        }
        View::assign(['template'=>$template,'cate'=>$cate, 'appname' => $appArr]);
        return View::fetch();
    }

    //编辑分类
    public function edit()
    {
        if(Request::isAjax()) {
            $data = Request::param();

            if(isset($data['id']) && $data['pid'] == $data['id']) return json(['code'=>-1,'msg'=> '不能作为自己的子类']);
            $list = Db::name('cate')->where('id', input('id'))->save($data);

            if($list){
                return json(['code'=>0,'msg'=> '编辑成功']);
            }else{
                return json(['code'=>-1,'msg'=> '编辑失败']);
            }
        }
        //详情模板
        $sys = $this->getSystem();
        $template = Files::getDirName('../view/'.$sys['template'].'/index/article/');
        // 如果是新增，pid=0,detpl默认第一个子模块，如果是编辑，查询出cate
        $cate = Db::name('cate')->field('id,catename,ename,detpl,pid,appname,icon,sort,desc')->where(['delete_time' =>0])->find((int) input('id'));
        // app下前台带模板的应用
        $appArr = [];
        if(is_dir(root_path() . 'app' . DS . 'home')) {
            $appArr = ['index','home'];
        } else {
            $appArr = ['index'];
        }
        View::assign(['template'=>$template,'cate'=>$cate, 'appname' => $appArr]);
        return View::fetch();
    }
	
	//删除帖子分类
	public function delete()
	{
		if(Request::isAjax()){
		    $id = Request::param('id');
            $cate = new CateModel;
            $result = $cate->del($id);
            if($result == 1){
                return json(['code'=>0,'msg'=>'删除分类成功']);
            }else{
                return json(['code'=>-1,'msg'=>$result]);
            }
		}
	}
	

	//帖子分类开启热点
	//评论审核
	public function hot()
	{
		$data = Request::only(['id','is_hot']);
		$cate = Db::name('cate')->save($data);
		if($cate){
			if($data['is_hot'] == 1){
				return json(['code'=>0,'msg'=>'设置热点成功','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'取消热点显示','icon'=>5]);
			}
		}else{
			$res = ['code'=>-1,'msg'=>'热点设置失败'];
		} 
		return json($res);
	}
	
	//array_filter过滤函数
	public function  filtr($arr){
			if($arr === '' || $arr === null){
				return false;
			}
        return true;
	}


}
