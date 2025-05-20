<?php
/*
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\content\Cate.php
 * @Description: Cate 分类菜单
 * @LastEditTime: 2023-03-14 15:40:53
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\content;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\facade\Category;
use taoler\com\Files;
use think\Response\Json;
use Exception;

class Cate extends AdminBaseController
{
    /**
     * 浏览
     * @return string
     */
	public function index()
	{
        return View::fetch();
	}

	//帖子分类
	public function list()
	{
        return Category::getList();
	}

    //添加和编辑帖子分类 废弃
    public function addEdit()
    {
        $addOrEdit = !is_null(input('id'));//true是编辑false新增
        $msg = $addOrEdit ? lang('edit') : lang('add');
        if(Request::isAjax()) {
            $data = Request::param(['id/d','pid/d','catename','ename','type','icon','image','detpl','desc','sort', 'url']);

            if(isset($data['id']) && $data['pid'] == $data['id']) return json(['code'=>-1,'msg'=> $msg.'不能作为自己的子类']);
            try{
                Category::cache('catename')->save($data);
            } catch(Exception $e) {
                return json(['code' => 1, 'msg' => $msg.'失败'.$e->getMessage()]);
            }
            return json(['code'=>0,'msg'=> $msg.'成功']);
        }
        //详情模板
        $template = $this->getIndexTpl();
        // 如果是新增，pid=0, tpl默认第一个子模块，如果是编辑，查询出cate
        $cate = $addOrEdit ? Category::getCateInfoById((int) input('id')) : '';
        $view = $addOrEdit ? 'edit' : 'add';

        View::assign([
            'template'  => $template,
            'cate'      => $cate
        ]);
        return View::fetch($view);
    }
	
	//删除栏目及栏目内容
	public function delete()
	{
        $id = $this->app->request->param('id/d');

        return Category::delete($id);
	}

	// 动态审核
	public function check()
	{
        $param = Request::only(['id','name','value']);
        $data = ['id' => $param['id'], $param['name'] => $param['value']];
        return Category::check($data);
	}

    /**
     * index/view/article下模板文件
     * @return array
     */
    protected function getIndexTpl() :array
    {
        $sys = $this->getSystem();
        return Files::getDirName('../view/'.$sys['template'].'/article/');
    }

    /**
     * 有顶级菜单的分类数
     *
     * @return Json
     */
    public function getCateTree(): Json
    {
        $list = Category::field('id,pid,catename,sort')->select()->toArray();

        $data =  getTree($list);
        // 排序
        $cmf_arr = array_column($data, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $data);

        $count = count($data);

        return json([
            'code' => 0,
            'msg' => 'ok',
            'count' => $count,
            'data'  => [[
                'id' => 0,
                'pid'   => 0,
                'catename' => '顶级',
                'children'  => $data
            ]]
        ]);
    }

    /**
     * 文章可选分类菜单
     *
     * @return Json
     */
    public function getArticleCateTree(): Json
    {
        $list = Category::field('id,pid,catename,sort')->select()->toArray();

        $data =  getTree($list);
        // 排序
        $cmf_arr = array_column($data, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $data);

        $count = count($data);

        return json([
            'code' => 0,
            'msg' => 'ok',
            'count' => $count,
            'data'  => $data
        ]);
    }

    /**
     * 单页分类菜单
     *
     * @return Json
     */
    public function getSingleCateTree(): Json
    {
        $list = Category::field('id,pid,catename,sort')
        ->where('type', 2)
        ->select();

        $data =  getTree($list);
        // 排序
        $cmf_arr = array_column($data, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $data);

        $count = count($data);

        return json([
            'code' => 0,
            'msg' => 'ok',
            'count' => $count,
            'data'  => $data
        ]);
    }

    /**
     * 单页分类菜单
     *
     * @return Json
     */
    public function getSingleArticleCateTree(): Json
    {
        $pageCate = Db::name('page')->field('cate_id')->group('cate_id')->select()->column('cate_id');
   
        $list = Category::field('id,pid,catename,sort')
        ->where('type', 2)
        ->whereNotIn('id', $pageCate)
        ->select();

        $data =  getTree($list);
        // 排序
        $cmf_arr = array_column($data, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $data);

        $count = count($data);

        return json([
            'code' => 0,
            'msg' => 'ok',
            'count' => $count,
            'data'  => $data
        ]);
    }


}
