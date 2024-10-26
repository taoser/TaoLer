<?php

namespace app\index\controller;

use app\common\controller\BaseController;
use think\App;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Session;
use think\facade\Config;
use app\common\model\Cate;
use app\common\model\Comment;
use app\common\model\UserZan;
use app\common\model\PushJscode;
use app\common\lib\Msgres;

class Article extends BaseController
{
	protected $middleware = [ 
    	'logincheck' => ['except' 	=> ['cate','detail','download'] ],
    ];

    protected $model;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new \app\common\model\Article();
    }

    //文章分类
    public function cate()
	{
		$cate = new Cate();
		//动态参数
		$ename = Request::param('ename');
		$type = Request::param('type','all');
		$page = Request::param('page',1);

		// 分类信息
		$cateInfo = $cate->getCateInfo($ename);

		if(is_null($cateInfo)) {
			// 抛出 HTTP 异常
			throw new \think\exception\HttpException(404, '没有可访问的数据！');
		}
		
        //分类列表
		$artList = $this->model->getCateList($ename,$type,$page);

		//	热议文章
		$artHot = $this->model->getArtHot(10);

		//分页url
		$url = (string) url('cate_page',['ename'=>$ename,'type'=>$type,'page'=>$page]);
		$path = substr($url,0,strrpos($url,"/")); //返回最后/前面的字符串


		$assignArr = [
			'ename'		=> $ename,
			'cateinfo'	=> $cateInfo,
			'type'		=> $type,
			'artList'	=> $artList,
			'artHot'	=> $artHot,
			'path'		=> $path
		];

		View::assign($assignArr);

		$cateView = is_null($cateInfo) ? 'article/cate' : 'article/' . $cateInfo->detpl . '/cate';
		return View::fetch($cateView);
    }

	//文章详情页
    public function detail()
    {
		$id = input('id');
		$page = input('page',1);

		// 1.内容
        $artDetail = $this->model->getArtDetail($id);
        if(is_null($artDetail)) throw new \think\exception\HttpException(404, '无内容');

		// 2.pv
		$artDetail->inc('pv', 1)->save();
		$pv = Db::name('article')->where('id',$id)->value('pv');
        $artDetail->pv = $pv;

		// 3.设置内容的tag内链
		$artDetail->content = $this->setArtTagLink($artDetail->content);

		//	热议文章
		$artHot = $this->model->getArtHot(10);

		// 5.赞列表
		$userZanList = [];
		$userZan = UserZan::where(['article_id'=>$id,'type'=>1])->cache(true)->select();
		if(count($userZan)) {
			foreach($userZan as $v){
				$userZanList[] = ['userImg'=>$v->user->user_img,'name'=>$v->user->name];
			}
		}

        // 被赞
        $zanCount = Db::name('user_zan')->where('user_id', $artDetail['user_id'])->cache(true)->count();

		// 标签
		$tags = [];
		$relationArticle = []; //相关帖子
        $artTags = Db::name('taglist')->where('article_id', $id)->cache(true)->select();
		if(count($artTags)) {
			foreach($artTags as $v) {
				$tag = Db::name('tag')->find($v['tag_id']);
				if(!is_null($tag)) 
				$tags[] = ['name'=>$tag['name'],'url'=> (string) url('tag_list',['ename'=>$tag['ename']])];
			}
			//相关帖子
			$relationArticle =  $this->model->getRelationTags($artTags[0]['tag_id'],$id,5);
		}

		//上一篇下一篇
		$upDownArt =  $this->model->getPrevNextArticle($id,$artDetail['cate_id']);
		if(empty($upDownArt['previous'])) {
            $previous = '前面已经没有了！';
        } else {
            $previous = '<a href="' . $upDownArt['previous']['url'] . '" rel="prev">' . $upDownArt['previous']['title'] . '</a>';
        }
		if(empty($upDownArt['next'])) {
            $next = '已经是最新的内容了!';
        } else {
            $next = '<a href="' . $upDownArt['next']['url'] . '" rel="prev">' . $upDownArt['next']['title'] . '</a>';
        }

		//评论
		$comment = new Comment();
		$comments = $comment->getComment($id, $page);

		//最新评论时间
		$lrDate_time = Db::name('comment')->where('article_id', $id)->cache(true)->max('update_time',false) ?? time();
		//push
		$push_js = Db::name('push_jscode')->where(['delete_time'=>0,'type'=>1])->cache(true)->select();

		View::assign([
			'article'		=> $artDetail,
			'artHot'	=> $artHot,
			'pv'			=> $pv,
			'tags'			=> $tags,
			'relationArticle' => $relationArticle,
			'previous'		=> $previous,
			'next'			=> $next,
			'page'			=> $page,
			'comments'		=> $comments,
			'push_js'		=> $push_js,
			'cid' 			=> $id,
			'lrDate_time' 	=> $lrDate_time,
			'userZanList' 	=> $userZanList,
			'zanCount'    	=> $zanCount,
            'passJieMi'   	=> session('art_pass_'.$id)
		]);

		return View::fetch('article/'.$artDetail['cate']['detpl'].'/detail');
    }

    /**
     * 添加帖子文章
     * @return string|\think\Response|\think\response\Json|void
     */
    public function add()
    {
        if (Request::isAjax()) {
			// 检验发帖是否开放
			if(config('taoler.config.is_post') == 0 ) return json(['code'=>-1,'msg'=>'抱歉，系统维护中，暂时禁止发帖！']);

			// 数据
            $data = Request::only(['cate_id', 'title', 'title_color','read_type','art_pass', 'content', 'keywords', 'description', 'captcha']);
            $data['user_id'] = $this->uid;
			$tagId = input('tagid');

			// 验证码
			if(Config::get('taoler.config.post_captcha') == 1) {				
				if(!captcha_check($data['captcha'])){
				 return json(['code'=>-1,'msg'=> '验证码失败']);
				};
			}

			// 验证器
			$validate = new \app\common\validate\Article;
            $result = $validate->scene('Artadd')->check($data);
            if (true !== $result) {
                return Msgres::error($validate->getError());
            }
			
			// 获取内容图片音视频标识
			$iva= $this->hasIva($data['content']);
			$data = array_merge($data,$iva);

			// 处理图片内容
			$data['content'] = $this->downUrlPicsReaplace($data['content']);
			// 把中文，转换为英文,并去空格->转为数组->去掉空数组->再转化为带,号的字符串
			$data['keywords'] = implode(',',array_filter(explode(',',trim(str_replace('，',',',$data['keywords'])))));
            $data['description'] = strip_tags($this->filterEmoji($data['description']));

			
            // 获取分类ename,appname
            $cateName = Db::name('cate')->field('ename,appname')->find($data['cate_id']);

			// vip每天可免费发帖数
			$user = Db::name('user')->field('id,vip,point,auth')->find($this->uid);

			$postRule = Db::name('user_viprule')->field('postnum,postpoint')->where('vip', $user['vip'])->find();	
			// 检测可发帖子剩余量
			$postLog = Db::name('user_article_log')->field('id,user_postnum')->where(['user_id' => $this->uid])->whereDay('create_time')->find();
			if(is_null($postLog)) {
				//没有记录创建
				Db::name('user_article_log')->save(['user_id' => $this->uid, 'create_time' => time()]);
				$postLog = Db::name('user_article_log')->field('id,user_postnum')->where(['user_id' => $this->uid])->whereDay('create_time')->find();
			}

			// 超级管理员排外
			if($user['auth'] === '0') {
				$cannum =  $postRule['postnum'] - $postLog['user_postnum']; // 可用免费额
				if($cannum <= 0) {
					//额度已用完需要扣积分
					$canpoint = 1 * $postRule['postpoint'];
					$point = $user['point'] - $canpoint;
					if($point < 0) { // 1.积分不足
						return json(['code' => -1, 'msg' => "免额已使用,本次需{$canpoint}积分,请充值！"]);
					}
					// 2.扣除积分
					Db::name('user')->where('id', $this->uid)->update(['point' => $point]);
				}
			}
			
            $result = $this->model->add($data);
            if ($result['code'] == 1) {
				// 记录每天发帖量
				Db::name('user_article_log')->where('id', $postLog['id'])->inc('user_postnum')->update();
				// 获取到的最新ID
				$aid = $result['data']['id'];

				//写入taglist表
				if(!empty($tagId)) {
					$tagArr = [];
					$tagIdArr = explode(',', $tagId);
					foreach($tagIdArr as $tid) {
						$tagArr[] = [ 'article_id' => $aid, 'tag_id' => $tid, 'create_time'=>time()];
					}
					Db::name('taglist')->insertAll($tagArr);
				}	
				
				// 清除文章tag缓存
                Cache::tag('tagArtDetail')->clear();
				// 发提醒邮件
				hook('mailtohook',[$this->adminEmail,'发帖审核通知','Hi亲爱的管理员:</br>用户'.$this->user['name'].'刚刚发表了 <b>'.$data['title'].'</b> 新的帖子，请尽快处理。']);

                $link = $this->getRouteUrl((int) $aid, $cateName['ename']);
				$url = $result['data']['status'] ? $link : (string)url('index/');

                hook('SeoBaiduPush', ['link'=>$link]); // 推送给百度收录接口
				hook('callme_add', ['article_id' => (int) $aid]); // 添加文章的联系方式

				return Msgres::success($result['msg'], $url);
            }
            return Msgres::error('add_error');
        }

		
		// 子模块自定义自适应add.html模板
		$cate = Db::name('cate')->field('id,detpl')->where('ename', input('cate'))->find();
		// 子模块下有add.html模板
		if(!empty($cate)) {
			$cid = $cate['id'];
		} else {
			$cate['detpl'] = '';
			$cid = '';
		}
		// 模板路径
		$appName = $this->app->http->getName();
		$viewRoot = root_path() . config('view.view_dir_name') . DIRECTORY_SEPARATOR . $appName .  DIRECTORY_SEPARATOR;
		$view = 'article' . DIRECTORY_SEPARATOR . $cate['detpl'] . DIRECTORY_SEPARATOR . 'add.html';
		$vfile = $viewRoot . $view;

		//子模块下存在add模板则调用，否则调用article/add.html
		$addTpl = is_file($vfile) ? $vfile : 'add';

		View::assign(['cid'=>$cid]);
		return View::fetch($addTpl);
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
		$article = $this->model->find($id);
		
		if(Request::isAjax()){
			$data = Request::only(['id','cate_id','title','title_color','read_type','art_pass','content','upzip','keywords','description','captcha']);
			$tagId = input('tagid');	
			
			// 验证码
			if(Config::get('taoler.config.post_captcha') == 1)
			{				
				if(!captcha_check($data['captcha'])){
				 return json(['code'=>-1,'msg'=> '验证码失败']);
				};
			}
			//调用验证器
			$validate = new \app\common\validate\Article(); 
			$res = $validate->scene('Artadd')->check($data);
			
			if(true !== $res){
                return Msgres::error($validate->getError());
			} else {
				//获取内容图片音视频标识
				$iva= $this->hasIva($data['content']);
				$data = array_merge($data,$iva);

				$data['content'] = $this->downUrlPicsReaplace($data['content']);
				// 把，转换为,并去空格->转为数组->去掉空数组->再转化为带,号的字符串
				$data['keywords'] = implode(',',array_filter(explode(',',trim(str_replace('，',',',$data['keywords'])))));
                $data['description'] = strip_tags($this->filterEmoji($data['description']));

				$result = $article->edit($data);
				if($result == 1) {
					//处理标签
					if(!empty($tagId)) {
						$tagIdArr = explode(',',$tagId);
						$artTags = Db::name('taglist')->where('article_id',$id)->column('tag_id','id');
						foreach($artTags as $aid => $tid) {
							if(!in_array($tid, $tagIdArr)){
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
                    Session::delete('art_pass_'.$id);

                    $link = $this->getRouteUrl((int) $id, $article->cate->ename);

                    hook('SeoBaiduPush', ['link'=>$link]); // 推送给百度收录接口
					return Msgres::success('edit_success',$link);
				}
                return Msgres::error($result);
			}
		}
			
        View::assign(['article'=>$article]);
		// 编辑多模板支持
		$tpl = Db::name('cate')->where('id', $article['cate_id'])->value('detpl');
		$appName = $this->app->http->getName();
		$viewRoot = root_path() . config('view.view_dir_name') . DIRECTORY_SEPARATOR . $appName .  DIRECTORY_SEPARATOR;
		$view = 'article' . DIRECTORY_SEPARATOR . $tpl . DIRECTORY_SEPARATOR . 'edit.html';
		$vfile = $viewRoot . $view;
		$editTpl = is_file($vfile) ? $vfile : 'edit';

		return View::fetch($editTpl);
    }
	
	/**
	 * 删除
	 *
	 * @return void
	 */
    public function delete()
	{
		$id = input('id');
		
		if(Request::isAjax()){
            try {
                $arr = explode(",", $id);
                foreach($arr as $v){
                    $article = $this->model->find((int) $v);
                    $article->together(['comments'])->delete();
                }
                return json(['code'=>0,'msg'=>'删除成功']);
            } catch (\Exception $e) {
                return json(['code'=>-1,'msg'=>'删除失败']);
            }
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

	// 文章置顶、加精、评论状态
	public function jieset()
	{
		$data = Request::param();
		$article = $this->model->field('id,is_top,is_hot,is_reply')->find($data['id']);
		switch ($data['field']){
            case  'top':
                if($data['rank']==1){
                    $article->save(['is_top' => 1]);
                    $res = ['status'=>0,'msg'=>'置顶成功'];
                } else {
                    $article->save(['is_top' => 0]);
                    $res = ['status'=>0,'msg'=>'置顶已取消'];
                }
            break;
            case 'hot':
                if($data['rank']==1){
                    $article->save(['is_hot' => 1]);
                    $res = ['status'=>0,'msg'=>'加精成功'];
                } else {
                    $article->save(['is_hot' => 0]);
                    $res = ['status'=>0,'msg'=>'加精已取消'];
                }
            break;
            case 'reply':
                if($data['rank']==1){
                    $article->save(['is_reply' => 1]);
                    $res = ['status'=>0,'msg'=>'禁评成功'];
                } else {
                    $article->save(['is_reply' => 0]);
                    $res = ['status'=>0,'msg'=>'禁评已取消'];
                }
        }
        //删除本贴设置缓存显示编辑后内容
        Cache::delete('article_'.$data['id']);
		//清除文章tag缓存
		Cache::tag('tagArtDetail')->clear();
		return json($res);	
	}
	
	// 改变标题颜色
	public function titleColor()
	{
		$data = Request::param();
		$result = $this->model->update($data);
		if($result){
			//清除文章缓存
			Cache::tag(['tagArt','tagArtDetail'])->clear();
			$res = ['code'=> 0, 'msg'=>'标题颜色设置成功'];
		}else{
			$res = ['code'=> -1, 'msg'=>'标题颜色设置失败'];
		}
		return json($res);
	}
	
	/**
	 * 内容中是否有图片视频音频插入
	 *
	 * @param [type] $content
	 * @return boolean
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

	//设置文章内容tag
	protected function setArtTagLink($content)
	{
		// tag链接数组
		$taglink = new PushJscode();
		$tag = $taglink->getAllCodes(2);
		if(count($tag)) {
			foreach($tag as $key=>$value) {
				// 匹配所有
				//$content = str_replace("$key", 'a('.$value.')['.$key.']',$content);
				// 限定匹配数量 '/'.$key.'/'

				// 匹配不包含[和]的内容
				// $pats = '/(?<!\[)'.$key.'(?!\])/';
				// $pats = '/(?<!<a\s?(.*)?)'.$key.'(?!<\/a>)/';
				//$pats = '/'.$key.'(?!<\/a>)/';

				//1.不匹配 $key</a>已经存在链接的情况
				//2.或不匹配 alt="$key等等等" $key后面有"这种情况
				$pats = '/' . $value['name'] . '\s?(?!<\/a>|\s?\S*")/is';

				preg_match($pats,$content,$arr);

				// 开启和关闭编辑器使用不同的链接方式
				$rpes = hook('taonystatus') ? '<a href="' . $value['jscode'] . '" target="_blank" title="' . $value['name'] . '" style="font-weight: bold;color:#31BDEC">' . $value['name'] . '</a>' : 'a(' . $value['jscode'] . ')[' . $value['name'] . ']';

				$content = preg_replace($pats,$rpes,$content,2);
			}
		}
		
		return $content;
	}

	//点赞文章
	public function userZanArticle()
	{
		//
		if(Request::isPost()) {
			$data = Request::post();
			$data['user_id'] = $this->uid;
			$userZan = Db::name('user_zan')->where(['user_id'=>$this->uid,'article_id'=>$data['article_id']])->find();
			if($userZan){
				return json(['code'=> -1, 'msg'=>'您已赞过了哦']);
			}
			$res = Db::name('user_zan')->insert($data);
			if($res) {
				return json(['code'=> 0, 'msg'=>'点赞成功']);
			}
		}
		
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
        $cateList = Cate::field('id,pid,catename,sort')->where(['status' => 1])->select()->toArray();
        $list =  getTree($cateList);
        // 排序
        $cmf_arr = array_column($list, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $list);
        $count = count($list);
        $tree = [];
        if($count){
            $tree = ['code'=>0, 'msg'=>'ok','count'=>$count];
            $tree['data'] = $list;
        }

        return json($tree);
    }

    public function jiemi()
    {
        $param = Request::param();
        $article = $this->model->find($param['id']);
        if($article['art_pass'] == $param['art_pass']) {
            session('art_pass_'.$param['id'], $param['art_pass']);
            return json(['code' => 0, 'msg' => '解密成功']);
        }
        return json(['code' => -1, 'msg' => '解密失败']);
    }
	
}