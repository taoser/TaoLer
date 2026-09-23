<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-23 22:46:40
 * @LastEditors: TaoLer
 * @Description: 文章管理
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\admin\controller\content\Article.php
 * @Copyright: Copyright (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\content;


use Exception;
use think\Request;
use think\Response;
use think\facade\Lang;
use app\facade\Article as ArticleEntity;
use app\facade\Category;
use think\facade\View;
use think\facade\Db;
use think\facade\Cache;
use think\response\Json;
use app\exception\BusinessException;
use app\admin\controller\AdminBaseController;

class Article extends AdminBaseController
{
    /**
     * 文章模型
     * @var ArticleEntity $entity
     */
    protected $entity;

    public function initialize()
    {
        parent::initialize();
        
        $this->entity = new ArticleEntity();
    }

	public function index()
	{
		return View::fetch();
	}

    public function list(Request $request)
    {
        $data = $request->get(['title','sec','category_id/d']);
        $page = $request->get('page/d', 1);
        $limit = $request->get('limit/d', 10);
        
        $list = $this->entity::getFilterList($data, $page, $limit);
        
        if($list['total']) {
            return json([
                'code'  => 0,
                'msg'   => 'ok',
                'data'  => $list['data'],
                'count' => $list['total']
            ]);
        }
        
        return json(['code' => -1, 'msg' => 'no data']);
    }

    /**
     * 添加帖子文章
     * @return Response
     */
    public function add(Request $request): Response
    {
        $data = $request->param(['category_id', 'title', 'tiny_content', 'content', 'keywords', 'description', 'tagid']);
        $data['user_id'] = 1; // 管理员ID
        $data['status'] = 1; // 不用审核
        
        $this->entity::addData($data);
        
        return json(['code' => 0, 'msg' => 'ok']);
            
    }


    public function getArticleInfo(Request $request)
	{
		$id = $request->get('id/d');

		$article = $this->entity::getInfo($id);

		return json(['code' => 0, 'msg' => Lang::get('get success'), 'data' => $article]);
	}

    /**
     * 编辑文章数据
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request): Response
    {
        $data = $request->post(['id/d','category_id','title','content','keywords','description','tagid']);

		$this->entity::editData($data);

        return json(['code' => 0, 'msg' => 'ok']);
        
    }


    // 删除帖子 多选和单独
	public function delete(Request $request)
	{
		$id = $request->get('id');
        
        $arr = explode(",",$id);
        foreach($arr as $v){
            $article = $this->entity::find($v);
            $article->together(['comments'])->delete();
        }

        return json(['code'=>0,'msg'=>'删除成功']);
	}

    /**
	 * 置顶、推荐、加精
	 *
	 * @return Json
	 */
	public function setFlag(Request $request)
	{
		$param = $request->post(['id/d', 'type', 'value/d']);

        $flag = new \app\entity\ArticleFlag();
        $flag->setFlag($param['id'], $param['type'], $param['value']);

        return json(['code' => 0, 'msg' => '设置成功', 'icon'=>6]);

	}

	/**
	 * 评论开关，审核等状态管理
	 *
	 * @return Json
	 */
	public function check(Request $request)
	{
		$param = $request->post(['id/d', 'name', 'value/d']);

        try{
            //获取状态
            Db::table($this->getTableName($param['id']))
            ->where('id', $param['id'])
            ->update([
                $param['name'] => $param['value']
            ]);

            Cache::delete('article_'.$param['id']);

			return json(['code' => 0, 'msg' => '设置成功', 'icon'=>6]);
        } catch(Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage(), 'icon'=>6]);
        }
	}

    /**
	 * 多选批量审核
	 *
	 * @return Json
	 */
	public function checkSelect(Request $request)
	{
        $param = $request->post('data');
        $data = [];
        foreach($param as $v) {
            $data[] = ['id' => (int)$v['id'], 'status' => $v['check'] == '1' ? '-1' : '1'];
        }

		//获取状态
		$res = $this->entity::saveAll($data);
	
		if($res){
			return json(['code'=>0,'msg'=>'审核成功','icon'=>6]);
		}else {
			return json(['code'=>-1,'msg'=>'失败啦','icon'=>6]);
		}
	}

    /**
     * 上传接口
     *
     * @return void
     */
    public function uploads(Request $request)
    {
        $type = $request->post('type');
        return $this->uploadFiles($type);
    }

    /**
     * 分类树
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
	public function getCategoryTree()
	{
		//
		$category = Db::name('category')
        ->field('id,pid,name,ename,sort')
        ->whereNull('delete_time')
        ->order(['id' => 'ASC','sort' => 'ASC'])
        ->select()
        ->toArray();

        $data = build_tree($category);
		$count = count($category);
        $tree = [];
        if($count){
            $tree = ['code'=>0,'msg'=>'','count'=>$count];

            //构造一个顶级菜单pid=0的数组。把权限放入顶级菜单下子权限中
            $tree['data'][] = ['id'=>0,'name'=>'顶级','pid'=>0,'children'=>$data];
        }
		return json($tree);
	}

    /**
     * 分类
     * @return \think\response\Json
     */
    public function getCategoryList()
    {
        $categoryList = Category::field('id,pid,name,sort')
        ->where('status',1)
        ->order('sort','asc')
        ->select()
        ->toArray();

        $list =  build_tree($categoryList);
        $count = count($list);

        $tree = [];
        if($count){
            $tree = ['code'=>0, 'msg'=>'ok','count'=>$count];
            $tree['data'] = $list;
        }

        return json($tree);
    }

    /**
	 * 设置多媒体数据
	 *
	 * @param string $content
	 * @return array
	 */
	protected function setMediaData(string $content): array {
		$data = [];

		$data['media'] = [
			'images' => [],
			'videos' => [],
			'audios' => []
		];

		$images = get_all_img($content);
		$video = get_one_video($content);

		if(!empty($images)) {
			$data['media']['images'] = $images;
			$data['has_image'] = count($images);
            $data['thumb'] = $images[0];
		}
		
		if(!empty($video)) {
			$data['media']['videos'] = $video;
			$data['has_video'] = count($video);
		}

		return $data;
	}

}
