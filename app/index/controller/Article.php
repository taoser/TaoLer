<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use app\common\model\Cate;
use app\common\model\User;
use app\common\model\Comment;
use app\common\model\Collection;
use app\common\model\Article as ArticleModel;
use think\exception\ValidateException;
use think\facade\Config;

class Article extends BaseController
{
	protected $middleware = [ 
    	'logincheck' => ['except' 	=> ['cate','detail'] ],
    ];
	
	//文章分类
    public function cate(){

		$where =[];
		//获取分类ID
		$ename = Request::param('ename');
		$type = Request::param('type');
		$page = Request::param('page');
		
		$cateId = Db::name('cate')->where('ename',$ename)->value('id');
		if($cateId){
			$where = ['cate_id' => $cateId];
		}
		
		$artList = Cache::get('arts'.$ename.$type.$page);
		if(!$artList){
			switch ($type) {
				//查询文章,15个分1页
                case 'jie':
				$artList = ArticleModel::field('id,title,cate_id,user_id,create_time,is_top,is_hot')->with([
					'cate' => function($query){
						$query->where('delete_time',0)->field('id,catename');
					},
					'user' => function($query){
						$query->field('id,name,nickname,user_img,area_id');
					}
				])->withCount(['comments'])->where(['status'=>1,'jie'=>1])->where($where)->order(['is_top'=>'desc','create_time'=>'desc'])->paginate(15);
				break;
				
				case 'hot':
				$artList = ArticleModel::field('id,title,cate_id,user_id,create_time,is_top,is_hot')->with([
					'cate' => function($query){
						$query->where('delete_time',0)->field('id,catename');
					},
					'user' => function($query){
						$query->field('id,name,nickname,user_img,area_id');
					}
				])->withCount(['comments'])->where('status',1)->where($where)->where('is_hot',1)->order(['is_top'=>'desc','create_time'=>'desc'])->paginate(15);
				break;
				
				case 'top':
				$artList = ArticleModel::field('id,title,cate_id,user_id,create_time,is_top,is_hot')->with([
					'cate' => function($query){
						$query->where('delete_time',0)->field('id,catename');
					},
					'user' => function($query){
						$query->field('id,name,nickname,user_img,area_id');
					}
				])->withCount(['comments'])->where('status',1)->where($where)->where('is_top',1)->order(['is_top'=>'desc','create_time'=>'desc'])->paginate(15);
				break;
				
				default:
				$artList = ArticleModel::field('id,title,cate_id,user_id,create_time,is_top,is_hot')->with([
					'cate' => function($query){
						$query->where('delete_time',0)->field('id,catename');
					},
					'user' => function($query){
						$query->field('id,name,nickname,user_img,area_id');
					}
				])->withCount(['comments'])->where('status',1)->where($where)->order(['is_top'=>'desc','create_time'=>'desc'])->paginate(15);
	
				break;
			}
			Cache::set('arts'.$ename.$type.$page,$artList,600);
		}
		
		
		//	热议文章
		$artHot = ArticleModel::field('id,title')->withCount('comments')->where('status',1)->whereTime('create_time', 'year')->order('comments_count','desc')->limit(10)->select();
		//分类右栏广告
		$ad_cate = Db::name('slider')->where('slid_status',1)->where('delete_time',0)->where('slid_type',5)->whereTime('slid_over','>=',time())->select();
		//通用右栏
		$ad_comm = Db::name('slider')->where('slid_status',1)->where('delete_time',0)->where('slid_type',2)->whereTime('slid_over','>=',time())->select();
		
		View::assign(['type'=>$type,'artList'=>$artList,'artHot'=>$artHot,'ad_cate'=>$ad_cate,'ad_comm'=>$ad_comm]);
		return View::fetch();
    }

	//文章详情页
    public function detail($id)
    {
		$article = Cache::get('article_'.$id);
		if(!$article){
			//查询文章
		$article = ArticleModel::field('id,title,content,status,cate_id,user_id,is_top,is_hot,is_reply,pv,jie,tags,create_time')->where('status',1)->with([
            'cate' => function($query){
				$query->where('delete_time',0)->field('id,catename');
            },
			'user' => function($query){
				$query->field('id,name,nickname,user_img,area_id');
			}
        ])->find($id);
		Cache::set('article_'.$id,$article,3600);
		}
		$comments = $article->comments()->where('status',1)->paginate(10);
		$article->inc('pv')->update();
		$pv = Db::name('article')->field('pv')->where('id',$id)->value('pv');

/*		
		$nt = time();
		$ft = $article->comments;
		$ct[] = [];
		foreach($ft as $c){
			$t = $c->create_time;
			$ct[] = intval(($nt - strtotime($t))/86400);
		}
		dump($nt);
		dump($ct);
		$this->assign('ct',$ct);
		$article->append(['comment.ct'])->toArray();
		//halt($article);
*/		
		
		//	热议文章
		$artHot = ArticleModel::field('id,title')->withCount('comments')->where('status',1)->whereTime('create_time', 'year')->order('comments_count','desc')->limit(10)->select();
		//文章广告
		$ad_article = Db::name('slider')->where('slid_status',1)->where('delete_time',0)->where('slid_type',4)->whereTime('slid_over','>=',time())->select();
		//通用右栏
		$ad_comm = Db::name('slider')->where('slid_status',1)->where('delete_time',0)->where('slid_type',2)->whereTime('slid_over','>=',time())->select();

		View::assign(['article'=>$article,'pv'=>$pv,'comments'=>$comments,'artHot'=>$artHot,'ad_art'=>$ad_article,'ad_comm'=>$ad_comm]);
		return View::fetch();
    }
	
	//文章评论
	public function comment()
	{
		//if (Request::isAjax()){
		//获取评论
		$data = Request::only(['content','article_id','user_id']);
		
		//用户留言存入数据库
		if (Comment::create($data)) {
			$res = ['code'=>1, 'msg'=>'留言成功'];
		} else{
			$res = ['code'=>0, 'msg'=>'留言失败'];
		}
		return json($res);
	}
	
	

    //添加文章
    public function add()
	{
		if(Request::isAjax()){
			$data = Request::only(['cate_id','title','user_id','content','upzip','tags','captcha']);
			$validate = new \app\common\validate\Article; //调用验证器
			$result = $validate->scene('Artadd')->check($data); //进行数据验证		
			if(true !==$result){	
				return $this->error($validate->getError());
			} else {	
				$article = new \app\common\model\Article;
				$result = $article->add($data);
				if($result == 1) {
					$aid = Db::name('article')->max('id');
					return json(['code'=>1,'msg'=>'发布成功','url'=>'/'.app('http')->getName().'/jie/'.$aid.'.html']);
				} else {
					$this->error($result);
				}
			}
		}
		return View::fetch();
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

    //编辑文章
    public function edit($id)
    {
		$article = Db::name('article')->find($id);
		
		if(Request::isAjax()){
			$data = Request::only(['id','cate_id','title','user_id','content','upzip','tags','captcha']);
			$validate = new \app\common\validate\Article(); //调用验证器
			$res = $validate->scene('Artadd')->check($data); //进行数据验证
			
			if(true !==$res){
				return $this->error($validate->getError());
			} else {
				$article = new \app\common\model\Article;
				$result = $article->edit($data);
				if($result == 1) {
					return json(['code'=>1,'msg'=>'修改成功','url'=>'/'.app('http')->getName().'/jie/'.$id.'.html']);
				} else {
				$this->error($result);
				}
			}
		}
		
		$tag = Db::name('article')->where('id',$id)->value('tags');
		$attr = explode(',',$tag);
		$tags = [];
			foreach($attr as $key=>$v){
				if ($v !='') {
				$tags[] = $v;
				}
			}
			
        View::assign(['article'=>$article,'tags'=>$tags]);
		return View::fetch();
    }
	
	//删除文章
    public function delete()
	{
		$article = ArticleModel::find(input('id'));
		$result = $article->together(['comments'])->delete();
		if($result) {
			$res = ['code'=>1,'msg'=>'删除文章成功','url'=>'/index/user/post'];
		} else {
			$res = ['code'=>0,'msg'=>'删除文章失败'];	
		}
		return json($res);
	}
	
	//文本编辑器图片上传
	public function text_img_upload()
    {
        $file = request()->file('file');
		try {
			validate(['file'=>'fileSize:1024000|fileExt:jpg,png,gif'])
            ->check(['file'=>$file]);
			$savename = \think\facade\Filesystem::disk('public')->putFile('article_pic',$file);
		} catch (ValidateException $e) {
			return json(['status'=>-1,'msg'=>$e->getMessage()]);
		}
		$upload = Config::get('filesystem.disks.public.url');

		if($savename){
            //$name = $file->hashName();
            $name_path =str_replace('\\',"/",$upload.'/'.$savename);
			//halt($name_path);
			//$image = \think\Image::open("uploads/$name_path");
			//$image->thumb(168, 168)->save("uploads/$name_path");

            $res = ['status'=>0,'msg'=>'上传成功','url'=> $name_path];
        }else{
            $res = ['status'=>-1,'msg'=>'上传错误'];
        }
	return json($res);
    }

	
	//文章置顶，状态
	public function jieset(){
		$data = Request::param();
		$article = ArticleModel::field('id,is_top,is_hot')->find($data['id']);
		if($data['field'] === 'top') {
			if($data['rank']==1){
				$article->save(['is_top' => 1]);
				$res = ['status'=>0,'msg'=>'置顶成功'];
			} else {
				$article->save(['is_top' => 0]);
				$res = ['status'=>0,'msg'=>'已取消置顶'];
			}
		} else {
			if($data['rank']==1){
				$article->save(['is_hot' => 1]);
				$res = ['status'=>0,'msg'=>'已设精贴'];
			} else {
				$article->save(['is_hot' => 0]);
				$res = ['status'=>0,'msg'=>'精贴已取消'];
			}
		}
		return json($res);	
	}
	
}