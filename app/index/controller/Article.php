<?php

namespace app\index\controller;

use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Session;
use think\facade\Config;
use app\facade\Category;
use app\index\model\PushJscode;
use app\common\lib\Msgres;
use app\common\lib\IdEncode;
use think\Response\Json;
use app\index\validate\Article as ArticleValidate;
use Exception;
use think\exception\HttpException;
use think\facade\Event;

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
		global $page;
		//动态参数
		$ename = $this->request->param('ename');
		$type = $this->request->param('type', 'all');
		$page = $this->request->param('page/d', 1);

		// 分类信息
		$cateInfo = Category::getCateInfoByEname($ename);

		//当前页url
		$url = (string) url('cate_page', ['ename' => $ename, 'type' => $type, 'page' => $page]);
		
		$path = substr($url,0,strrpos($url,"/")); //返回最后/前面的字符串
		// 下一页url
		$next = $path . '/' . ++$page . '.html';

		$assignArr = [
			'cateinfo'	=> $cateInfo,
			'cate'	=> $cateInfo,
			'path'	=> $path,
			'page'	=> ++$page,
			'next'  => $next
		];

		View::assign($assignArr);

		$cateView = is_null($cateInfo) ? 'article/cate' : 'article/' . $cateInfo->detpl . '/cate';
		return View::fetch($cateView);
    }

	//文章详情页
    public function detail()
    {
		$ID = $this->request->param('id');

		$commentPage = $this->request->param('page',1);

		try{
			// 解密ID，得到int型
			$id = IdEncode::decode($ID);
			// 1.内容
			$detail = $this->model::getDetail($id);
	
			// 2.pv
			$detail->setInc('pv', 1); // 延迟更新
			$pv = Db::table($this->getTableName($id))->where('id', $id)->value('pv');
			$detail->pv = $pv;

		} catch(Exception $e) {
			throw new HttpException(404, $e->getMessage());
		}

		// 3.设置内容的tag内链
		// $artDetail->content = $this->setArtTagLink($artDetail->content);		
		
		//最新评论时间
		$lrDate_time = Db::name('comment')->where('article_id', $id)->cache(true)->max('update_time',false) ?? time();
	
		View::assign([
			'article'		=> $detail,
			'pv'			=> $pv,
			'page'			=> $commentPage,
			'cid' 			=> $id,
			'lrDate_time' 	=> $lrDate_time,
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

			$media = [
				'images' => [],
				'videos' => [],
				'audios' => []
			];

			$flags = [
				'is_top'    => '0',
				'is_good'  => '0',
				'is_wait'    => '0'
			];
			
			// 数据
            $data = Request::only(['cate_id/d', 'title','content', 'keywords', 'description', 'captcha']);
            
			$tagId = input('tagid');

			// 验证
			if(Config::get('taoler.config.post_captcha') == 1) {			
				if(!captcha_check($data['captcha'])){
					return json(['code'=>-1,'msg'=> '验证码失败']);
				};
			}

			$validate = new ArticleValidate();
            $result = $validate->scene('Artadd')->check($data);
            if (!$result) {
                return Msgres::error($validate->getError());
            }

			// 把中文，转换为英文,并去空格->转为数组->去掉空数组->再转化为带,号的字符串
			$data['keywords'] = implode(',',array_filter(explode(',',trim(str_replace('，',',',$data['keywords'])))));
            $data['description'] = strip_tags($this->filterEmoji($data['description']));
			// 处理图片内容
			$data['content'] = $this->downUrlPicsReaplace($data['content']);

			// 多媒体数据
			$medisArr = $this->setMediaData($data['content']);
			$data = array_merge($data, $medisArr);

			// ---------------------
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
				$data['user_id'] = $this->uid;
				$result = $this->model::add($data);


				event('article.articlePush', [
					'article_log_id'	=> $postLog['id'],
					'article_id'		=> $result['id'],
					'tag_id'			=> $tagId,
				]);
					
			} catch(Exception $e) {
				return json(['code' => -1, 'msg' => $e->getMessage()]);
			}
			
			// 获取分类ename,appname
			$cateName = Db::name('cate')->field('ename, appname')->find($data['cate_id']);
			$link = $this->getRouteUrl((int) $result['id'], $cateName['ename']);
			$url = $data['status'] ? $link : (string)url('index/');

			// 清除文章tag缓存
			Cache::tag('tagArtDetail')->clear();

			// 发提醒邮件
			// hook('mailtohook',[$this->adminEmail,'发帖审核通知','Hi亲爱的管理员:</br>用户'.$this->user['name'].'刚刚发表了 <b>'.$data['title'].'</b> 新的帖子，请尽快处理。']);
			// hook('SeoBaiduPush', ['link'=>$link]); // 推送给百度收录接口
			// hook('callme_add', ['article_id' => (int) $aid]); // 添加文章的联系方式

			// 清理首页静态文件
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
		$article = $this->model::suffix($this->byIdGetSuffix($id))->find($id);

		$this->removeDetailHtml($article);
		
		if(Request::isAjax()){
			$data = Request::only(['id/d','cate_id/d','title','content','keywords','description','captcha']);
			
			$tagId = input('tagid');	
			
			// 验证码
			if(Config::get('taoler.config.post_captcha') == 1)
			{				
				if(!captcha_check($data['captcha'])){
					return json(['code'=>-1,'msg'=> '验证码失败']);
				};
			}
			//调用验证器
			$validate = new ArticleValidate(); 
			$res = $validate->scene('Artadd')->check($data);
			if(!$res){
                return Msgres::error($validate->getError());
			}


			$data['content'] = $this->downUrlPicsReaplace($data['content']);
			// 把，转换为,并去空格->转为数组->去掉空数组->再转化为带,号的字符串
			$data['keywords'] = implode(',',array_filter(explode(',',trim(str_replace('，',',',$data['keywords'])))));
			$data['description'] = strip_tags($this->filterEmoji($data['description']));
			// 多媒体数据
			$medisArr = $this->setMediaData($data['content']);
			$data = array_merge($data, $medisArr);

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

			$id = IdEncode::encode($id);
			
			$link = $this->getRouteUrl($id, $article->cate->ename);

			// hook('SeoBaiduPush', ['link'=>$link]); // 推送给百度收录接口
			return Msgres::success('edit_success',$link);
		}
			
        View::assign(['article' => $article]);
		
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
	 * @return Json
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

	/**
	 * 文章置顶、加精、评论状态
	 *
	 * @return Json
	 */
	public function jieset(): Json
	{
		$param = Request::only(['id/d','field','rank/d']);
		
		$article = $this->model::suffix($this->byIdGetSuffix($param['id']))
		->field('id,is_top,is_hot,is_reply')
		->find($param['id']);
		
		switch ($param['field']){
            case  'top':
                if($param['rank']==1){
					$data = ['is_top' => 1];
                    $msg = '置顶成功';
                } else {
                    $data = ['is_top' => 0];
                    $msg = '置顶已取消';
                }
            break;
            case 'hot':
                if($param['rank']==1){
                    $data = ['is_hot' => 1];
                    $msg ='加精成功';
                } else {
                    $data = ['is_hot' => 0];
                    $msg ='加精已取消';
                }
            break;
            case 'reply':
                if($param['rank']==1){
                    $data = ['is_reply' => '1'];
                    $msg ='禁评成功';
                } else {
                    $data = ['is_reply' => '0'];
                    $msg ='禁评已取消';
                }
        }
		
		try{
			$article->edit($data);
			//删除本贴设置缓存显示编辑后内容
			Cache::delete('article_'.$param['id']);
			//清除文章tag缓存
			Cache::tag('tagArtDetail')->clear();
		} catch(Exception $e) {
			return json(['status' => 1, 'msg' => $e->getMessage()]);
		}
		
		return json(['status' => 0, 'msg' => $msg]);	
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
			$data['thum_img'] = $images[0];
		}
		
		if(!empty($video)) {
			$data['media']['videos'] = $video;
			$data['has_video'] = count($video);
		}

		return $data;
	}

}