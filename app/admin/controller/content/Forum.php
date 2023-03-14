<?php
/**
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
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use taoler\com\Files;
use app\common\lib\Msgres;
use think\response\Json;


class Forum extends AdminController
{
    /**
     * 浏览
     * @return string
     */
	public function index()
	{
		return View::fetch();
	}

    //帖子列表
	public function list()
	{
		if(Request::isAjax()){	
		$data = Request::only(['id','name','title','sec']);
		$where =array();
		if (!empty($data['sec'])) {
			switch ($data['sec']) {
			case '1':
			$data['a.status'] = 1;
			break;
			case '2':
			$data['is_top'] = 1;
			break;
			case '3':
			$data['is_hot'] = 1;
			break;
			case '4':
			$data['is_reply'] = 0;
			break;
			case '5':
			$data['a.status'] = -1;
			break;
			case '6':
			$data['a.status'] = 0;
			break;
			}
		}
		unset($data['sec']);
		unset($data['status']);

		if(!empty($data['id'])){
			$data['a.id'] = $data['id'];
			unset($data['id']);
		}
		
		if(!empty($data['title'])){
			$where[] = ['title', 'like', '%'.$data['title'].'%'];
			unset($data['title']);
		}

		$map = array_filter($data,[$this,"filtr"]);

			$forumList = Db::name('article')
			->alias('a')
			->join('user u','a.user_id = u.id')
			->join('cate c','a.cate_id = c.id')
			->field('a.id as aid,ename,appname,name,user_img,title,content,a.update_time as update_time,is_top,a.is_hot as is_hot,is_reply,a.status as status')
			->where('a.delete_time',0)
			->where($map)
			->where($where)
			->order('a.create_time', 'desc')
			->paginate(15);
			$res = [];
			$count = $forumList->total();
			if($count){
				$res['code'] = 0;
				$res['msg'] = '';
				$res['count'] = $count;
				foreach($forumList as $k=>$v){
					$url = $this->getRouteUrl($v['aid'],$v['ename'],$v['appname']);
                    $res['data'][]= [
                        'id'=>$v['aid'],
                        'poster'=>$v['name'],
                        'avatar'=>$v['user_img'],
                        'title'=>htmlspecialchars($v['title']),
                        'url' => $url,
                        'content' => strip_tags($v['content']),
                        'posttime'=>date("Y-m-d",$v['update_time']),
                        'top'=>$v['is_top'],
                        'hot'=>$v['is_hot'],
                        'reply'=>$v['is_reply'],
                        'check'=>$v['status']
                    ];
				}
			} else {
				$res = ['code'=>-1,'msg'=>'没有查询结果！'];
			}
			return json($res);
		}
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
halt($data);
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
            $cateName = Db::name('cate')->field('ename,appname')->find($data['cate_id']);

            $article = new Article();
            $result = $article->add($data);
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

                $link = $this->getRouteUrl((int)$aid, $cateName['ename'],$cateName['appname']);
                // 推送给百度收录接口
                $this->baiduPushUrl($link);

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

            if(true !== $res){
                return Msgres::error($validate->getError());
            } else {
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
                    // 推送给百度收录接口
                    $this->baiduPushUrl($link);
                    $editRes = Msgres::success('edit_success',$link);
                } else {
                    $editRes = Msgres::error($result);
                }
                return $editRes;
            }
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
