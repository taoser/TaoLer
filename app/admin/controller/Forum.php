<?php

namespace app\admin\controller;

use app\common\controller\AdminController;
use app\common\model\Cate;
use app\common\model\Comment;
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
			->field('a.id as aid,ename,name,user_img,title,content,a.update_time as update_time,is_top,a.is_hot as is_hot,is_reply,a.status as status')
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
					$url = $this->getRouteUrl($v['aid'],$v['ename']);
				$res['data'][]= ['id'=>$v['aid'],'poster'=>$v['name'],'avatar'=>$v['user_img'],'title'=>htmlspecialchars($v['title']),'url'=>$url,'content'=>htmlspecialchars($v['content']),'posttime'=>date("Y-m-d",$v['update_time']),'top'=>$v['is_top'],'hot'=>$v['is_hot'],'reply'=>$v['is_reply'],'check'=>$v['status']];
				}
			} else {
				$res = ['code'=>-1,'msg'=>'没有查询结果！'];
			}
			return json($res);
		}
		return View::fetch();
	}
	
	//编辑帖子
	public function listform()
	{
		if(Request::isAjax()){
			$data = Request::param();
			$form = Db::name('article')->find($data['id']);
			//halt($form);
		}
		return View::fetch();
	}
	
	//删除帖子
	public function listdel($id)
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
	
	//帖子分类
	public function tags()
	{
        $cate = new Cate();
		if(Request::isAjax()){
            return $cate->getList();
		}
		//详情模板
		$sys = $this->getSystem();
		$template = Files::getDirName('../view/'.$sys['template'].'/index/article/');
		View::assign(['template'=>$template]);
		return View::fetch();
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
	public function tagsform()
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
	
	//删除帖子分类
	public function tagsdelete()
	{
		if(Request::isAjax()){
		$id = Request::param('id');

		$cate = new Cate;
		$result = $cate->del($id);
		
		if($result == 1){
			Cache::tag('catename')->clear();
			return json(['code'=>0,'msg'=>'删除分类成功']);
		}else{
			return json(['code'=>-1,'msg'=>$result]);
		}
		}
	}
	
	//帖子评论
	public function replys()
	{
		if(Request::isAjax()) {
			$data = Request::only(['name','content','status']);
			$map = array_filter($data);
			$where = array();
			if(!empty($map['content'])){
				$where[] = ['a.content','like','%'.$map['content'].'%'];
				unset($map['content']);
			}
			if(isset($data['status']) && $data['status'] !== '' ){
				$where[] = ['a.status','=',(int)$data['status']];
				unset($map['status']);
			}

/*			
			$replys = Comment::field('id,article_id,user_id,content,create_time')->with([
				'user' => function($query){
				$query->field('id,name,user_img');
			},
			'article' => function($query){
				$query->field('id,title');
			}
			])->paginate(15);
*/			
			$replys = Db::name('comment')
				->alias('a')
				->join('user u','a.user_id = u.id')
				->join('article c','a.article_id = c.id')
				->join('cate ca','c.cate_id = ca.id')
				->field('a.id as aid,name,ename,title,user_img,a.content as content,a.create_time as create_time,a.status as astatus,c.id as cid')
				->where('a.delete_time',0)
				->where($map)
				->where($where)
				->order('a.create_time', 'desc')
				->paginate(15);
			
			$count = $replys->total();
			$res = [];
			if ($count) {
				$res = ['code'=>0,'msg'=>'','count'=>$count];
				foreach($replys as $k => $v){
					$url = $this->getRouteUrl($v['cid'],$v['ename']);
					//$res['data'][] = ['id'=>$v['id'],'replyer'=>$v->user->name,'cardid'=>$v->article->title,'avatar'=>$v->user->user_img,'content'=>$v['content'],'replytime'=>$v['create_time']];
					$res['data'][] = ['id'=>$v['aid'],'replyer'=>$v['name'],'title'=>htmlspecialchars($v['title']),'avatar'=>$v['user_img'],'content'=>htmlspecialchars($v['content']),'replytime'=>date("Y-m-d",$v['create_time']),'check'=>$v['astatus'],'url'=>$url];
				}
			} else {
				$res = ['code'=>-1,'msg'=>'没有查询结果！'];
			}
			return json($res);
		}
		
		return View::fetch();
	}
	
	//评论编辑
	public function replysform()
	{
		return View::fetch();
	}
	//评论删除
	public function redel($id)
	{
		if(Request::isAjax()){
			$comm =Comment::find($id);
			$result = $comm->delete();
			
				if($result){
					return json(['code'=>0,'msg'=>'删除成功']);
				}else{
					return json(['code'=>-1,'msg'=>'删除失败']);
				}
			}
	}
	//评论审核
	public function recheck()
	{
		$data = Request::param();

		//获取状态
		$res = Db::name('comment')->where('id',$data['id'])->save(['status' => $data['status']]);
		if($res){
			if($data['status'] == 1){
				return json(['code'=>0,'msg'=>'评论审核通过','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'评论被禁止','icon'=>5]);
			}
			
		}else {
			return json(['code'=>-1,'msg'=>'审核出错']);
		}
	}
	
	//帖子分类开启热点
	//评论审核
	public function tagshot()
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

			// 获取分类ename
			$cate_ename = Db::name('cate')->where('id',$data['cate_id'])->value('ename');
		
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

				$link = $this->getRouteUrl((int)$aid, $cate_ename);
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
					$link = $this->getRouteUrl((int) $id, $article->cate->ename);
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
	 * 上传接口
	 *
	 * @return void
	 */
	public function uploads()
    {
        $type = Request::param('type');
        return $this->uploadFiles($type);
    }

	public function getCateTree()
	{
		//
		$cate = Db::name('cate')->order(['id' => 'ASC','sort' => 'ASC'])->where(['delete_time'=>0])->select()->toArray();
		
		$cateTree = array2tree($cate);

		$count = count($cateTree);
			$tree = [];			
			if($cateTree){
				$tree = ['code'=>0,'msg'=>'','count'=>$count];
				
				$res = [];	//auth_rule储存数据表中的表结构
				foreach($cateTree as $k => $v){
					//第一层子权限
					$children = [];
					if(isset($v['children'])){
						
						foreach($v['children'] as $m => $j){
							//第二层子权限
							$chichi = [];
							if(isset($j['children'])){
								//第三层子权限
								foreach($j as $s){
									if(isset($s['children'])){
										$chichi[] = ['id'=>$s['id'],'catename'=>$s['catename'],'pid'=>$s['pid']];	//子数据的子数据
									}
								}
							}
							
						//if($j['level']  < 3){}
						$children[] = ['id'=>$j['id'],'catename'=>$j['catename'],'pid'=>$j['pid'],'children'=>$chichi];		//子数据
						}
					}
					$data[] = ['id'=>$v['id'],'catename'=>$v['catename'],'pid'=>$v['pid'],'children'=>$children];
				}
				
				//构造一个顶级菜单pid=0的数组。把权限放入顶级菜单下子权限中
				$tree['data'][] = ['id'=>0,'catename'=>'顶级','pid'=>0,'children'=>$data];
			}
		return json($tree);
	}



}
