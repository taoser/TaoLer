<?php

namespace app\index\controller;

use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Session;
use think\facade\Config;
use app\facade\Category;
use app\facade\Comment;
use app\index\model\UserZan;
use app\index\model\PushJscode;
use app\common\lib\Msgres;
use app\common\lib\IdEncode;
use think\Response\Json;
use Exception;

class Article extends IndexBaseController
{
	protected $middleware = [ 
    	'logincheck' => ['except' 	=> ['cate','detail','download'] ],
    ];

    protected $model;

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\facade\Article();
    }

    //文章分类
    public function cate()
	{
		//动态参数
		$ename = $this->request->param('ename');
		$type = $this->request->param('type', 'all');
		$page = $this->request->param('page/d', 1);

		// 分类信息
		$cateInfo = Category::getCateInfoByEname($ename);

		//分页url
		$url = (string) url('cate_page',['ename'=>$ename, 'type'=>$type, 'page'=>$page]);
		$path = substr($url,0,strrpos($url,"/")); //返回最后/前面的字符串


		$assignArr = [
			'cateinfo'	=> $cateInfo,
			'cate'	=> $cateInfo,
			'path'		=> $path
		];

		View::assign($assignArr);

		$cateView = is_null($cateInfo) ? 'article/cate' : 'article/' . $cateInfo->detpl . '/cate';
		return View::fetch($cateView);
    }

	//文章详情页
    public function detail()
    {
		$id = $this->request->param('id');
		// dump($this->request);
		$commentPage = $this->request->param('page',1);
		$id = IdEncode::decode($id);

		// 1.内容
        $detail = $this->model::getDetail($id);
		
		// 2.pv
		$detail->setInc('pv', 1, 60);
		$pv = Db::table($this->getTableName($id))->where('id', $id)->value('pv');
        $detail->pv = $pv;

		// 3.设置内容的tag内链
		// $artDetail->content = $this->setArtTagLink($artDetail->content);		
		
		//最新评论时间
		$lrDate_time = Db::name('comment')->where('article_id', $id)->cache(true)->max('update_time',false) ?? time();
		// halt($lrDate_time);
		View::assign([
			'article'		=> $detail,
			'pv'			=> $pv,
			'page'			=> $commentPage,
			'cid' 			=> $id,
			'lrDate_time' 	=> $lrDate_time,
            'passJieMi'   	=> session('art_pass_'.$id)
		]);

		$html = View::fetch('article/'.$detail['cate']['detpl'].'/detail');
		
		$this->buildHtml($html);
		
		return $html;
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
            $data = Request::only(['cate_id/d', 'title', 'title_color','read_type','art_pass', 'content', 'keywords', 'description', 'captcha']);
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

			// 把中文，转换为英文,并去空格->转为数组->去掉空数组->再转化为带,号的字符串
			$data['keywords'] = implode(',',array_filter(explode(',',trim(str_replace('，',',',$data['keywords'])))));
            $data['description'] = strip_tags($this->filterEmoji($data['description']));
			// 处理图片内容
			$data['content'] = $this->downUrlPicsReaplace($data['content']);
			// 获取内容图片音视频标识
			// $iva= $this->hasIva($data['content']);
			// $data = array_merge($data, $iva);

			$images = get_all_img($data['content']);
			$video = get_one_video($data['content']);

			$media = [];

			if(!empty($images)) {
				$media['image'] = $images;
				$data['has_img'] = '1';
			}
			if(!empty($video)) {
				$media['video'] = $video;
				$data['has_video'] = '1';
			}

			$data['media'] = $media;

            // 获取分类ename,appname
            $cateName = Db::name('cate')->field('ename, appname')->find($data['cate_id']);

			// vip每天可免费发帖数
			$user = Db::name('user')->field('id,vip,point,auth')->find($this->uid);

			$postRule = Db::name('user_viprule')
				->field('postnum,postpoint')
				->where('vip', $user['vip'])
				->find();

			// 检测可发帖子剩余量
			$postLog = Db::name('user_article_log')
				->field('id,user_postnum')
				->where('user_id', $this->uid)
				->whereDay('create_time')
				->find();

			if(is_null($postLog)) {
				//没有记录创建
				Db::name('user_article_log')
				->save([
					'user_id' => $this->uid,
					'create_time' => time()
				]);

				$postLog = Db::name('user_article_log')
				->field('id,user_postnum')
				->where('user_id', $this->uid)
				->whereDay('create_time')
				->find();
			}

			// 超级管理员排外
			if($user['auth'] === '0') {
				// 可用免费额
				$cannum =  $postRule['postnum'] - $postLog['user_postnum']; 
				if($cannum <= 0) {
					//额度已用完需要扣积分
					$canpoint = 1 * $postRule['postpoint'];
					$point = $user['point'] - $canpoint;

					if($point < 0) { // 1.积分不足
						return json(['code' => -1, 'msg' => "免额已使用,本次需{$canpoint}积分,请充值！"]);
					}

					// 2.扣除积分
					Db::name('user')
					->where('id', $this->uid)
					->update(['point' => $point]);
				}
			}

			// 超级管理员无需审核
			$data['status'] = $user['auth'] ? 1 : Config::get('taoler.config.posts_check');
			$msg = $data['status'] ? '发布成功' : '发布成功，请等待审核';

			try{
				$result = $this->model::add($data);
			} catch(Exception $e) {
				return json(['code' => -1, 'msg' => $e->getMessage()]);
			}
            
			// 记录每天发帖量
			Db::name('user_article_log')
				->where('id', $postLog['id'])
				->inc('user_postnum')
				->update();

			// 获取到的最新ID
			$aid = $result['id'];

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
			$url = $data['status'] ? $link : (string)url('index/');

			hook('SeoBaiduPush', ['link'=>$link]); // 推送给百度收录接口
			hook('callme_add', ['article_id' => (int) $aid]); // 添加文章的联系方式

			$this->removeIndexHtml();
			return Msgres::success($msg, $url);
            
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
    public function edit()
    {
		$id = $this->request->param('id/d');
		// $id = IdEncode::decode($id);
// halt($id);
		$article = $this->model::setSuffix($this->byIdGetSuffix($id))->find($id);
// halt($article);
		$this->removeDetailHtml($article);
		
		if(Request::isAjax()){
			$data = Request::only(['id/d','cate_id/d','title','title_color','read_type','art_pass','content','keywords','description','captcha']);
			
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
			if(!$res){
                return Msgres::error($validate->getError());
			}

			//获取内容图片音视频标识
			$iva= $this->hasIva($data['content']);
			$data = array_merge($data,$iva);

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

			$id = IdEncode::encode($id);
			
			$link = $this->getRouteUrl($id, $article->cate->ename);

			hook('SeoBaiduPush', ['link'=>$link]); // 推送给百度收录接口
			return Msgres::success('edit_success',$link);
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
	 * 单条或者多条删除
	 *
	 * @return void
	 */
    public function delete(): Json
	{
		$id = $this->request->param('id');

		try {
			$arr = explode(",", $id);
			$ids = array_map('intval', $arr);
			$this->model::remove($ids);
				
		} catch (\Exception $e) {
			return json(['code'=>-1,'msg' => $e->getMessage()]);
		}

		return json(['code' => 0, 'msg' => '删除成功']);
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
		$article = $this->model::field('id,is_top,is_hot,is_reply')->find($data['id']);
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
		$result = $this->model::update($data);
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
        $cateList = Category::field('id,pid,catename,sort')->where(['status' => 1])->select()->toArray();
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
        $article = $this->model::find($param['id']);
        if($article['art_pass'] == $param['art_pass']) {
            session('art_pass_'.$param['id'], $param['art_pass']);
            return json(['code' => 0, 'msg' => '解密成功']);
        }
        return json(['code' => -1, 'msg' => '解密失败']);
    }
	
}