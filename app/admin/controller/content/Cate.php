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
use taoler\com\Files;
use app\index\model\Category;



class Cate extends AdminBaseController
{
    protected $model;

    public function initialize()
    {
        parent::initialize();
        $this->model = new Category();
    }

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
        return $this->model->getList();
	}

    //添加和编辑帖子分类 废弃
    public function addEdit()
    {
        $addOrEdit = !is_null(input('id'));//true是编辑false新增
        $msg = $addOrEdit ? lang('edit') : lang('add');
        if(Request::isAjax()) {
            $data = Request::param();
            if(isset($data['id']) && $data['pid'] == $data['id']) return json(['code'=>-1,'msg'=> $msg.'不能作为自己的子类']);
            $list = Db::name('cate')->cache('catename')->save($data);
            if($list){
                return json(['code'=>0,'msg'=> $msg.'成功']);
            }
            return json(['code'=>-1,'msg'=> $msg.'失败']);
        }
        //详情模板
        $template = $this->getIndexTpl();
        // 如果是新增，pid=0,detpl默认第一个子模块，如果是编辑，查询出cate
        $cate = $addOrEdit ? $this->model->getCateInfoById((int) input('id')) : '';
        $view = $addOrEdit ? 'edit' : 'add';

        View::assign([
            'template'  => $template,
            'cate'      => $cate
        ]);
        return View::fetch($view);
    }
	
	//删除帖子分类
	public function delete()
	{
        $result = $this->model->del(input('id'));
        if($result == 1){
            return json(['code'=>0,'msg'=>'删除分类成功']);
        }
        return json(['code'=>-1,'msg' => $result]);
	}

	// 动态审核
	public function check()
	{
        $param = Request::only(['id','name','value']);
        $data = ['id'=>$param['id'],$param['name']=>$param['value']];
        //获取状态
        $res = Db::name('cate')->save($data);
		if($res){
			return json(['code'=>0,'msg'=>'设置成功','icon'=>6]);
		}
        return json(['code'=>-1,'msg'=>'设置失败']);
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
     * 分类树
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCateTree()
    {
        $cateList = Category::field('id,pid,catename,sort')
        ->where('status', 1)
        ->select()
        ->toArray();

        $data =  getTree($cateList);
        // 排序
        $cmf_arr = array_column($data, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $data);
        
        $count = count($data);

        $tree = [];
        if($count){

            $tree = ['code'=>0, 'msg'=>'ok', 'count' => $count];

            $tree['data'][] = ['id'=>0, 'catename'=>'顶级', 'pid' => 0, 'children'=>$data];
        }

        return json($tree);
    }


}
