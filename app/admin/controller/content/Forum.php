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

use app\common\controller\AdminController;
use app\common\model\Article;
use think\App;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use taoler\com\Files;
use app\common\lib\Msgres;
use think\response\Json;


class Forum extends AdminController
{
    protected $model;

    public function __construct(App $app)
    {
        parent::__construct($app);
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
        $data = Request::only(['id','name','title','sec']);
        $where = [];
        if (!empty($data['sec'])) {
            switch ($data['sec']) {
                case '1':
                    $where[] = ['status', '=', 1];
                    break;
                case '2':
                    $where[] = ['is_top', '=', 1];
                    break;
                case '3':
                    $where[] = ['is_hot', '=', 1];
                    break;
                case '4':
                    $where[] = ['is_reply', '=', 1];
                    break;
                case '5':
                    $where[] = ['status', '=', -1];
                    break;
                case '6':
                    $where[] = ['status', '=', 0];
                    break;
            }
        }
        unset($data['sec']);

        if(!empty($data['id'])){
            $where[] = ['id', '=', $data['id']];
        }

        if(!empty($data['name'])){
            $userId = Db::name('user')->where('name',$data['name'])->value('id');
            $where[] = ['user_id', '=', $userId];
        }

        if(!empty($data['title'])){
            $where[] = ['title', 'like', $data['title'].'%'];
        }

        $list = $this->model->getList($where, input('limit'), input('page'));
        $res = [];
        if($list['total']){
            foreach($list['data'] as $v) {
                $res['data'][] = [
                    'id'        => $v['id'],
                    'poster'    => $v['user']['name'],
                    'avatar'    => $v['user']['user_img'],
                    'title'     => htmlspecialchars($v['title']),
                    'url'       => $this->getArticleUrl($v['id'], 'index', $v['cate']['ename']),
                    'content'   => strip_tags($v['content']),
                    'posttime'  => $v['update_time'],
                    'top'       => $v['is_top'],
                    'hot'       => $v['is_hot'],
                    'reply'     => $v['is_reply'],
                    'check'     => $v['status']
                ];
            }
            return json(['code' =>0, 'msg' => 'ok', 'count' => $list['total'], 'data' => $res['data']]);
        }
        return json(['code' =>-1, 'msg' => 'no data']);
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

            // 获取分类ename,appname
            $cateName = $this->model->field('ename,appname')->find($data['cate_id']);

            $result = $this->model->add($data);
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

                $link = $this->getArticleUrl((int)$aid, 'index', $cateName['ename']);

                hook('SeoBaiduPush', ['link'=>$link]); // 推送给百度收录接口

                $url = $result['data']['status'] ? $link : (string)url('index/');
                $res = Msgres::success($result['msg'], $url);
            } else {
                $res = Msgres::error('add_error');
            }
            return $res;
        }
        //1.查询分类表获取所有分类
        $cateList = Db::name('cate')->where(['status'=>1,'delete_time'=>0])->order('sort','asc')->cache('catename',3600)->select();

        //2.将catelist变量赋给模板 公共模板nav.html
        View::assign('cateList',$cateList);

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
    public function edit($id)
    {
        $article = Article::find($id);
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

            $result = $article->edit($data);
            if($result == 1) {
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
                $link = $this->getRouteUrl((int) $id, $article->cate->ename, $article->cate->appname);
                hook('SeoBaiduPush', ['link'=>$link]); // 推送给百度收录接口
                return Msgres::success('edit_success',$link);
            }
            return Msgres::error($result);
        }

        View::assign(['article'=>$article]);
        //1.查询分类表获取所有分类
        $cateList = Db::name('cate')->where(['status'=>1,'delete_time'=>0])->order('sort','asc')->cache('catename',3600)->select();

        //2.将catelist变量赋给模板 公共模板nav.html
        View::assign('cateList',$cateList);

        return View::fetch();
    }


    //删除帖子
	public function delete($id)
	{
		if(Request::isAjax()){
			$arr = explode(",",$id);
			foreach($arr as $v){
				$article = Article::find($v);
				$result = $article->together(['comments'])->delete();
			}
			
			if($result){
				return json(['code'=>0,'msg'=>'删除成功']);
			}else{
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
		$param = Request::only(['id','name','value']);
		$data = ['id'=>$param['id'],$param['name']=>$param['value']];
		//获取状态
		$res = Db::name('article')->save($data);
		Cache::delete('article_'.$data['id']);
		if($res){
			return json(['code'=>0,'msg'=>'设置成功','icon'=>6]);
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
	 * 调用百度关键词
	 *
	 * @return json
	 */
    public function getKeywords()
    {
        $data = Request::only(['flag','keywords','content']);
        $keywords = $this->setKeywords($data);
        return json(['code'=>0, 'msg' => 'ok', 'data'=> $keywords]);
    }

    /**
     * 标题调用百度关键词词条
     * @return Json
     */
	public function getWordList()
	{
		$title = input('title');
		return $this->getBdiduSearchWordList($title);
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
	 * 获取描述，过滤html
	 *
	 * @return void
	 */
	public function getDescription()
	{
		$data = Request::only(['content']);
		$description = getArtContent($data['content']);
		return json(['code'=>0,'data'=>$description]);
	}


    /**
     * 分类
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
	public function getCateTree()
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

    //array_filter过滤函数
    protected function  filtr($arr){
        if($arr === '' || $arr === null){
            return false;
        }
        return true;
    }

}
