<?php
/*
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\content\Forum.php
 * @Description: Forum
 * @LastEditTime: 2023-03-14 15:42:00
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\content;

use app\admin\controller\AdminBaseController;
use app\facade\Article;
use app\facade\Category;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use app\common\lib\Msgres;
use think\response\Json;
use Exception;


class Forum extends AdminBaseController
{
    protected $model;

    public function initialize()
    {
        parent::initialize();
        $this->model = new Article();
    }

    /**
     * 浏览
     * @return string
     */
	public function index()
	{
		return View::fetch();
	}

    public function list()
    {
        $data = Request::only(['id/d','name','title','sec','cate_id/d']);
        $page = Request::param('page/d', 1);
        $limit = Request::param('limit/d', 10);
        
        $list = Article::getFilterList($data, $page, $limit);
        
        if($list['total']) {
            return json(['code' => 0, 'msg' => 'ok', 'data' => $list['data'], 'count' => $list['total']]);
        }
        
        return json(['code' => -1, 'msg' => 'no data']);
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

        $list =  getTree($cateList);
        // 排序
        $cmf_arr = array_column($list, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $list);
        
        $count = count($list);
        $tree = [];
        if($count){
            $tree = ['code'=>0, 'msg'=>'ok','data' => $list, 'count' => $count];
        }

        return json($tree);
    }

    /**
     * 添加帖子文章
     * @return string|\think\Response|\think\response\Json|void
     */
    public function add()
    {
        if (Request::isAjax()) {

            $data = Request::only(['cate_id', 'title', 'title_color', 'tiny_content', 'content', 'upzip', 'keywords', 'description', 'captcha']);
            $tagId = input('tagid');
            $data['user_id'] = 1; //管理员ID
            // 调用验证器
            $validate = new \app\common\validate\Article;
            $result = $validate->scene('Artadd')->check($data);
            if (true !== $result) {
                return Msgres::error($validate->getError());
            }

            // 获取内容图片音视频标识
            $iva= $this->hasIva($data['content']);
            $data = array_merge($data,$iva);

            // 处理内容
            $data['content'] = $this->downUrlPicsReaplace($data['content']);
            // 把，转换为,并去空格->转为数组->去掉空数组->再转化为带,号的字符串
            $data['keywords'] = implode(',',array_filter(explode(',',trim(str_replace('，',',',$data['keywords'])))));
            $data['description'] = strip_tags($this->filterEmoji($data['description']));
            // 获取分类ename,appname
            $cateEname = Db::name('cate')->where('id',$data['cate_id'])->value('ename');

            $result =  $this->model::add($data);
            if ($result['code'] == 1) {
                // 获取到的最新ID
                $aid = $result['data']['id'];
                //写入taglist表
                $tagArr = [];
                if(isset($tagId)) {
                    $tagIdArr = explode(',',$tagId);
                    foreach($tagIdArr as $tid) {
                        $tagArr[] = ['article_id'=>$aid,'tag_id'=>$tid,'create_time'=>time()];
                    }
                }
                Db::name('taglist')->insertAll($tagArr);

                // 清除文章tag缓存
                Cache::tag('tagArtDetail')->clear();

                $link = $this->getArticleUrl((int)$aid, 'index', $cateEname);

                hook('SeoBaiduPush', ['link'=>$link]); // 推送给百度收录接口

                $url = $result['data']['status'] ? $link : (string)url('index/');
                $res = Msgres::success($result['msg'], $url);
            } else {
                $res = Msgres::error('add_error');
            }
            return $res;
        }

        return View::fetch('add');
    }

    /**
     * 编辑文章
     * @param $id
     * @return string|\think\Response|\think\response\Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $id = $this->request->param('id/d');
		// $id = IdEncode::decode($id);

		$article = $this->model::suffix($this->byIdGetSuffix($id))->find($id);
        if(is_null($article)) return json(['code' => -1, 'msg' => '不能编辑！']);
        
        if(Request::isAjax()){

            $data = Request::only(['id','cate_id','title','title_color','content','upzip','keywords','description','captcha']);
            $tagId = input('tagid');

            //调用验证器
            $validate = new \app\common\validate\Article();
            $res = $validate->scene('Artadd')->check($data);

            if(!$res) return Msgres::error($validate->getError());
            //获取内容图片音视频标识
            $iva= $this->hasIva($data['content']);
            $data = array_merge($data,$iva);

            // 处理内容
            $data['content'] = $this->downUrlPicsReaplace($data['content']);
            // 把，转换为,并去空格->转为数组->去掉空数组->再转化为带,号的字符串
            $data['keywords'] = implode(',',array_filter(explode(',',trim(str_replace('，',',',$data['keywords'])))));
            $data['description'] = strip_tags($this->filterEmoji($data['description']));

            try{
				$article->edit($data);
			} catch(Exception $e) {
				return json(['code' => -1, 'msg' => $e->getMessage()]);
			}

            //处理标签
            $artTags = Db::name('taglist')->where('article_id',$id)->column('tag_id','id');
            if(isset($tagId)) {
                $tagIdArr = explode(',',$tagId);
                foreach($artTags as $aid => $tid) {
                    if(!in_array($tid,$tagIdArr)){
                        //删除被取消的tag
                        Db::name('taglist')->delete($aid);
                    }
                }
                //查询保留的标签
                $artTags = Db::name('taglist')->where('article_id',$id)->column('tag_id');
                $tagArr = [];
                foreach($tagIdArr as $tid) {
                    if(!in_array($tid, $artTags)){
                        //新标签
                        $tagArr[] = ['article_id'=>$data['id'],'tag_id'=>$tid,'create_time'=>time()];
                    }
                }
                //更新新标签
                Db::name('taglist')->insertAll($tagArr);
            }
            //删除原有缓存显示编辑后内容
            Cache::delete('article_'.$id);
            $link = $this->getArticleUrl((int) $id, 'index', $article->cate->ename);
            // hook('SeoBaiduPush', ['link'=>$link]); // 推送给百度收录接口
            return Msgres::success('edit_success',$link);
            
        }

        View::assign(['article'=>$article]);
        return View::fetch();
    }


    //删除帖子 多选和单独
	public function delete($id)
	{
		if(Request::isAjax()){
            try {
                $arr = explode(",",$id);
                foreach($arr as $v){
                    $article = Article::find($v);
                    $article->together(['comments'])->delete();
                }
                return json(['code'=>0,'msg'=>'删除成功']);
            } catch (\Exception $e) {
                return json(['code'=>-1,'msg'=>'删除失败']);
            }
		}
	}

	/**
	 * 置顶、加精、评论开关，审核等状态管理
	 *
	 * @return Json
	 */
	public function check()
	{
		$param = Request::only(['id/d','name','value']);

		//获取状态
		$res = Db::table($this->getTableName($param['id']))->save([
            'id' => $param['id'],
            $param['name'] => $param['value']
        ]);

		if($res){
            Cache::delete('article_'.$param['id']);
			return json(['code'=>0,'msg'=>'设置成功','icon'=>6]);
		}else {
			return json(['code'=>-1,'msg'=>'失败啦','icon'=>6]);
		}
	}

    /**
	 * 多选批量审核
	 *
	 * @return Json
	 */
	public function checkSelect()
	{
        $param = Request::param('data');
        $data = [];
        foreach($param as $v) {
            $data[] = ['id' => (int)$v['id'], 'status' => $v['check'] == '1' ? '-1' : '1'];
        }

		//获取状态
		$res = $this->model->saveAll($data);
	
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
    public function uploads()
    {
        $type = Request::param('type');
        return $this->uploadFiles($type);
    }

	/**
	 * 内容中是否有图片视频音频插入
	 *
	 * @param [type] $content
	 * @return array
     *
	 */
	public function hasIva($content)
	{
		//判断是否插入图片
		$isHasImg = strpos($content,'img[');
		$data['has_img'] = is_int($isHasImg) ? 1 : 0;
		//判断是否插入视频
		$isHasVideo = strpos($content,'video(');
		$data['has_video'] = is_int($isHasVideo) ? 1 : 0;
		//判断是否插入音频
		$isHasAudio = strpos($content,'audio[');
		$data['has_audio'] = is_int($isHasAudio) ? 1 : 0;
		
		return $data;
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
		$cate = Db::name('cate')->field('id,pid,catename,ename,sort')->order(['id' => 'ASC','sort' => 'ASC'])->where(['delete_time'=>0])->select()->toArray();
        $data = getTree($cate);
		$count = count($cate);
        $tree = [];
        if($count){
            $tree = ['code'=>0,'msg'=>'','count'=>$count];

            //构造一个顶级菜单pid=0的数组。把权限放入顶级菜单下子权限中
            $tree['data'][] = ['id'=>0,'catename'=>'顶级','pid'=>0,'children'=>$data];
        }
		return json($tree);
	}

    /**
     * 分类
     * @return \think\response\Json
     */
    public function getCategoryList()
    {
        $cateList = Category::field('id,pid,catename,sort')->where(['status' => 1])->select()->toArray();
        // 排序
        $cmf_arr = array_column($cateList, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $cateList);

        $list =  getTree($cateList);
        $count = count($list);
        $tree = [];
        if($count){
            $tree = ['code'=>0, 'msg'=>'ok','count'=>$count];
            $tree['data'] = $list;
        }

        return json($tree);
    }

    //array_filter过滤函数
    protected function  filtr($arr){
        if($arr === '' || $arr === null){
            return false;
        }
        return true;
    }

}
