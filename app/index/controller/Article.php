<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
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
		$cate = Db::name('cate')->where('ename',$ename)->find();
		$types = input('type');
		if($cate){
			$where = ['cate_id' => $cate['id']];
		}else{
			$where = true;
		}
		switch ($types) {
				//查询文章,15个分1页
                case 'all':
				$artList = ArticleModel::field('id,title,cate_id,user_id,create_time,is_top,is_hot')->with([
					'cate' => function($query){
						$query->where('delete_time',0)->field('id,catename');
					},
					'user' => function($query){
						$query->field('id,name,nickname,user_img,area_id');
					}
				])->withCount(['comments'])->where('status',1)->where($where)->order(['is_top'=>'desc','create_time'=>'desc'])->paginate(15);
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
				])->withCount(['comments'])->where('status',1)->where($where)->order(['is_top'=>'desc','create_time'=>'desc'])->withCache(30)->paginate(15);

				break;
		}
		
		//	热议文章
		$artHot = ArticleModel::field('id,title')->withCount('comments')->where('status',1)->whereTime('create_time', 'year')->order('comments_count','desc')->limit(10)->select();
		//分类右栏广告
		$ad_cate = Db::name('slider')->where('slid_status',1)->where('delete_time',0)->where('slid_type',5)->whereTime('slid_over','>=',time())->select();
		//通用右栏
		$ad_comm = Db::name('slider')->where('slid_status',1)->where('delete_time',0)->where('slid_type',2)->whereTime('slid_over','>=',time())->select();
		
		View::assign(['type'=>$types,'artList'=>$artList,'artHot'=>$artHot,'ad_cate'=>$ad_cate,'ad_comm'=>$ad_comm]);
		return View::fetch();
    }

	//文章详情页
    public function detail($id)
    {
		//获取文章ID
		//$id = Request::param('id');
		//查询文章
		$article = ArticleModel::field('id,title,content,status,cate_id,user_id,is_top,is_hot,is_reply,pv,jie,create_time')->where('status',1)->with([
            'cate' => function($query){
				$query->where('delete_time',0)->field('id,catename');
            },
			'user' => function($query){
				$query->field('id,name,nickname,user_img,area_id');
			}
        ])->find($id);
		$comments = $article->comments()->where('status',1)->select();
		$article->inc('pv')->update();

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
		
		View::assign(['article'=>$article,'comments'=>$comments,'artHot'=>$artHot,'ad_art'=>$ad_article,'ad_comm'=>$ad_comm]);
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
			$data = Request::post();
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
	
	/添加tag
	public function tags(){
		$data = Request::only(['tags']);
		$att = explode(',',$data['tags']);
		$tags = [];
			foreach($att as $v){
				if ($v !='') {
				$tags = $v;
				}
			}
		//var_dump($tags);
		 return json(['code'=>0,'data'=>$tags]);
	}

    //编辑文章
    public function edit()
    {
		$aid = input('id');
		$article = Db::name('article')->find($aid);
		
		if(Request::isAjax()){
			$data = Request::post();
			$validate = new \app\common\validate\Article(); //调用验证器
			$res = $validate->scene('Artadd')->check($data); //进行数据验证
			
			if(true !==$res){	
				//echo '<script>alert("'.$res.'");location.back()</script>';
				return $this->error($validate->getError());
			} else {	
				$article = new \app\common\model\Article();
				$result = $article->edit($data);
				if($result == 1) {
					return json(['code'=>1,'msg'=>'修改成功','url'=>'/'.app('http')->getName().'/jie/'.$aid.'.html']);
				} else {
				$this->error($result);
				}
			}
		}
        
        View::assign('article',$article);
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
	
	//添加文章和编辑页富文本编辑器图片上传
	public function lay_img_upload()
    {
        $file = Request()->file('file');
        if(empty($file)){
            $result["code"] = "1";
            $result["msg"] = "请选择图片";
            $result['data']["src"] = '';
        }else{
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move('uploads/' );
            if($info){
                $infos = $info->getInfo();
                // 源文件名
                $name = $infos['name'];
 
                $name_path =str_replace('\\',"/",$info->getSaveName());
                //成功上传后 获取上传信息
                $result["code"] = '0';
                $result["msg"] = "上传成功";
                $result['data']["src"] = "/uploads/".$name_path;
                $result['data']["title"] = $name;
            }else{
                // 上传失败获取错误信息
                $result["code"] = "2";
                $result["msg"] = "上传出错";
                $result['data']["src"] ='';
            }
        }
 
        return json_encode($result);
 
    }
	
	//文本编辑器图片上传
	public function text_img_upload()
    {
        $file = request()->file('file');
		try {
			validate(['file'=>'fileSize:2048|fileExt:jpg,png,gif'])
            ->check(array($file));
			$savename = \think\facade\Filesystem::disk('public')->putFile('article_pic',$file);
		} catch (ValidateException $e) {
			echo $e->getMessage();
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
            $res = ['status'=>1,'msg'=>'上传错误'];
        }
	return json($res);
    }

	
	//文章置顶，状态
	public function jieset(){
		$data = Request::param();
		$article = ArticleModel::find($data['id']);
		if($data['field'] === 'top') {
			if($data['rank']==1){
				$article->save(['is_top' => 1]);
				$res['status'] = 0;
				$res['msg'] ='置顶成功';
			} else {
				$article->save(['is_top' => 0]);
				$res['status'] = 0;
				$res['msg'] ='已取消置顶';
			}
		} else {
			if($data['rank']==1){
				$article->save(['is_hot' => 1]);
				$res['status'] = 0;
				$res['msg'] ='已设精贴';
			} else {
				$article->save(['is_hot' => 0]);
				$res['status'] = 0;
				$res['msg'] ='精贴已取消';
			}
		}
		return json($res);	
	}
	
}