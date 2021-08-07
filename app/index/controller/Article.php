<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Config;
use app\common\model\Comment;
use app\common\model\Article as ArticleModel;
use app\common\model\Slider;
use think\exception\ValidateException;
use taoler\com\Message;
use app\common\lib\Msgres;
use app\common\lib\Uploads;

class Article extends BaseController
{
	protected $middleware = [ 
    	'logincheck' => ['except' 	=> ['cate','detail','download'] ],
    ];
	
	//文章分类
    public function cate()
	{
		//非法请求参数，抛出异常
		if(count(Request::param()) >3){
			// 抛出 HTTP 异常
                throw new \think\exception\HttpException(404, '请求异常');
		}

		//获取分类ID
		$ename = Request::param('ename');
		$type = Request::param('type') ?? 'all';
		$tpl = Db::name('cate')->where('ename',$ename)->value('detpl');
		//分页伪静态
		$str = Request::baseUrl();	//不带参数在url
		$patterns = "/\d+/"; //数字正则
		preg_match($patterns,$str,$arr);	//正则查询页码出现在位置
		//检测route配置中是否设置了伪静态后缀
		$suffix = Config::get('route.url_html_suffix') ? '.'.Config::get('route.url_html_suffix') : '/';
		if(Config::get('route.url_html_suffix')){
			//伪静态有后缀
			if(isset($arr[0])){
				$page = $arr[0];
				$url = strstr($str,$arr[0],true);
			} else {
				$page = 1;
				$url = strstr($str,'.html',true).'/';
			}
		} else {
			//伪静态后缀false
			if(isset($arr[0])){
				$page = $arr[0];
				$url = strstr($str,$arr[0],true);
			} else {
				$page = 1;
				$url = $str.'/';
			}
		}
        //分类列表
        $article = new ArticleModel();
		$artList = $article->getCateList($ename,$type,$page,$url,$suffix);
		$count = $artList->total();

		//	热议文章
		$artHot = $article->getArtHot(10);

		//广告
        $ad = new Slider();
        //分类图片
        $ad_cateImg = $ad->getSliderList(3);
        //分类钻展赞助
        $ad_comm = $ad->getSliderList(6);
		
		View::assign(['type'=>$type,'artList'=>$artList,'artHot'=>$artHot,'ad_cateImg'=>$ad_cateImg,'ad_comm'=>$ad_comm,'jspage'=>'jie','count'=>$count,'page'=>$page,'url'=>$url]);
		return View::fetch('article/'.$tpl.'/cate');
    }

	//文章详情页
    public function detail($id)
    {
        $article = new ArticleModel();
        $artDetail = $article->getArtDetail($id);
		$arId = $artDetail->cate->id;
		$tpl = Db::name('cate')->where('id',$arId)->value('detpl');
		if(!$artDetail){
			// 抛出 HTTP 异常
			throw new \think\exception\HttpException(404, '异常消息');
		}
		$comments = $artDetail->comments()->where('status',1)->order(['cai'=>'asc','create_time'=>'asc'])->paginate(10);
        //$comment = new \app\common\model\Comment();
        //$comments = $comment->getComment($id);
		//dump($comments);
        $artDetail->inc('pv')->update();
		$pv = Db::name('article')->field('pv')->where('id',$id)->value('pv');
		$download = $artDetail->upzip ? download($artDetail->upzip,'file') : '';	
		
		//	热议文章
		$artHot = $article->getArtHot(10);
        //广告
        $ad = new Slider();
        //分类图片
        $ad_artImg = $ad->getSliderList(4);
        //分类钻展赞助
        $ad_comm = $ad->getSliderList(7);
		
		View::assign(['article'=>$artDetail,'pv'=>$pv,'comments'=>$comments,'artHot'=>$artHot,'ad_art'=>$ad_artImg,'ad_comm'=>$ad_comm,$download,'jspage'=>'jie']);
		return View::fetch('article/'.$tpl.'/detail');
    }
	
	//文章评论
	public function comment()
	{
		if (Request::isAjax()){
			//获取评论
			$data = Request::only(['content','article_id','user_id']);
			$sendId = $data['user_id'];
			if(empty($data['content'])){
				return json(['code'=>0, 'msg'=>'评论不能为空！']);
			}
			$data['status'] = Config::get('taoler.config.commnets_check');
			$msg = $data['status'] ? '留言成功' : '留言成功，请等待审核';
				
			//用户留言存入数据库
			if (Comment::create($data)) {
				//站内信
				$article = Db::name('article')->field('id,title,user_id')->where('id',$data['article_id'])->find();
				$title = $article['title'];
				$link = (string) url('article/detail',['id'=>$data['article_id']]);

				//评论中回复@user comment
				$preg = "/@([^@\s]*)\s/";
				preg_match($preg,$data['content'],$username);
				if(isset($username[1])){
					$receveId = Db::name('user')->whereOr('nickname', $username[1])->whereOr('name', $username[1])->value('id'); 
				} else {
					$receveId = $article['user_id'];
				}
				$data = ['title'=>$title,'content'=>'评论通知','link'=>$link,'user_id'=>$sendId,'type'=>2]; //type=2为评论留言
				Message::sendMsg($sendId,$receveId,$data);
				if(Config::get('taoler.config.email_notice')) mailto($this->showUser(1)['email'],'评论审核通知','Hi亲爱的管理员:</br>用户'.$this->showUser($this->uid)['name'].'刚刚对 <b>'.$title.'</b> 发表了评论，请尽快处理。');
				$res = ['code'=>0, 'msg'=>$msg];
			} else {
				$res = ['code'=>-1, 'msg'=>'留言失败'];
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
            $data = Request::only(['cate_id', 'title', 'title_color', 'user_id', 'content', 'upzip', 'tags', 'captcha']);
			$validate = new \app\common\validate\Article; //调用验证器
            $result = $validate->scene('Artadd')->check($data); //进行数据验证
            if (true !== $result) {
                return Msgres::error($validate->getError());
            }
			
			//获取内容图片音视频标识
			$iva= $this->hasIva($data['content']);
			$data = array_merge($data,$iva);
		
            $article = new ArticleModel();
            $result = $article->add($data);
            if ($result['code'] == 1) {
				if(Config::get('taoler.config.posts_check')){
					$aid = Db::name('article')->max('id');
					$link = (string)url('article/detail', ['id' => $aid]);
				}else{
					$link = (string)url('index/');
				}
                //清除文章tag缓存
                Cache::tag('tagArtDetail')->clear();
				if(Config::get('taoler.config.email_notice')) mailto($this->showUser(1)['email'],'发帖审核通知','Hi亲爱的管理员:</br>用户'.$this->showUser($this->uid)['name'].'刚刚发表了 <b>'.$data['title'].'</b> 新的帖子，请尽快处理。');
                $res = Msgres::success($result['msg'], $link);
            } else {
                $res = Msgres::error('add_error');
            }
            return $res;
        }
		View::assign(['jspage'=>'jie']);
        return View::fetch();
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
		$article = ArticleModel::find($id);
		//编辑
		if(Request::isAjax()){
			$data = Request::only(['id','cate_id','title','title_color','user_id','content','upzip','tags','captcha']);
			$validate = new \app\common\validate\Article(); //调用验证器
			$res = $validate->scene('Artadd')->check($data); //进行数据验证
			
			if(true !== $res){
                return Msgres::error($validate->getError());
			} else {
				//获取内容图片音视频标识
				$iva= $this->hasIva($data['content']);
				$data = array_merge($data,$iva);
				
				$result = $article->edit($data);
				if($result == 1) {
                    //删除原有缓存显示编辑后内容
                    Cache::delete('article_'.$id);
                    $link = (string) url('article/detail',['id'=> $id]);
					$editRes = Msgres::success('edit_success',$link);
				} else {
                    $editRes = Msgres::error($result);
				}
				return $editRes;
			}
		}
		//查询标签
		$tag = $article->tags;
		$attr = explode(',',$tag);
		$tags = [];
			foreach($attr as $key=>$v){
				if ($v !='') {
				$tags[] = $v;
				}
			}
			
        View::assign(['article'=>$article,'tags'=>$tags,'jspage'=>'jie']);
		return View::fetch();
    }
	
	//删除帖子
    public function delete()
	{
		$article = ArticleModel::find(input('id'));
		$result = $article->together(['comments'])->delete();
		if($result) {
		    $res = Msgres::success('delete_success');
		} else {
			$res = Msgres::error('delete_error');
		}
		return $res;
	}

	public function uploads()
    {
        $type = Request::param('type');
        $uploads = new Uploads();
        switch ($type){
            case 'image':
                $upRes = $uploads->put('file','article_pic',1024,'image');
                break;
            case 'zip':
                $upRes = $uploads->put('file','article_zip',1024,'application|image');
                break;
            case 'video':
                $upRes = $uploads->put('file','article_video',102400,'video|audio');
                break;
            case 'audio':
                $upRes = $uploads->put('file','article_audio',102400,'audio');
                break;
            default:
                $upRes = $uploads->put('file','article_file',1024,'image');
                break;
        }
        return $upRes;
    }

    //附件下载
    public function download($id)
    {
        $zipdir = Db::name('article')->where('id',$id)->value('upzip');
        $zip = substr($zipdir,1);
		Db::name('article')->cache(true)->where('id',$id)->inc('downloads')->update();
		//删除缓存显示下载后数据
        Cache::delete('article_'.$id);
        return download($zip,'my');
    }

    //添加tag
    public function tags()
    {
        $data = Request::only(['tags']);
        $att = explode(',',$data['tags']);
        $tags = [];
        foreach($att as $v){
            if ($v !='') {
                $tags[] = $v;
            }
        }
        return json(['code'=>0,'data'=>$tags]);
    }
	
	//文章置顶，状态
	public function jieset(){
		$data = Request::param();
		$article = ArticleModel::field('id,is_top,is_hot,is_reply')->find($data['id']);
		switch ($data['field']){
            case  'top':
                if($data['rank']==1){
                    $article->save(['is_top' => 1]);
                    $res = ['status'=>0,'msg'=>'置顶成功'];
                } else {
                    $article->save(['is_top' => 0]);
                    $res = ['status'=>0,'msg'=>'已取消置顶'];
                }
            break;
            case 'hot':
                if($data['rank']==1){
                    $article->save(['is_hot' => 1]);
                    $res = ['status'=>0,'msg'=>'已设精贴'];
                } else {
                    $article->save(['is_hot' => 0]);
                    $res = ['status'=>0,'msg'=>'精贴已取消'];
                }
            break;
            case 'reply':
                if($data['rank']==1){
                    $article->save(['is_reply' => 1]);
                    $res = ['status'=>0,'msg'=>'本帖禁评'];
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
	
	//改变标题颜色
	public function titleColor()
	{
		$data = Request::param();
		$result = ArticleModel::update($data);
		if($result){
			//清除文章缓存
			Cache::tag(['tagArt','tagArtDetail'])->clear();
			$res = ['code'=> 0, 'msg'=>'标题颜色设置成功'];
		}else{
			$res = ['code'=> -1, 'msg'=>'标题颜色设置失败'];
		}
		return json($res);
	}
	
	//内容中是否有图片视频音频插入
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
	
}