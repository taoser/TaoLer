<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\validate\User as userValidate;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\Cache;
use think\facade\Cookie;
use think\facade\View;
use app\common\model\Article;
use app\common\model\Collection;
use app\common\model\User as userModel;
use think\facade\Config;
use taoler\com\Message;

class User extends BaseController
{	
	protected $middleware = [ 
    	'logincheck' => ['except' 	=> ['home'] ],
    ];
		
	//用户中心
	public function index()
	{
        return view();
    }
	
	
	//发帖list
	public function artList()
	{
		$article = Article::withCount('comments')->where('user_id',$this->uid)->order('update_time','desc')->paginate(10);
		//var_dump($article);
		$count = $article->total();
		$res = [];
		if($count){
			$res['code'] = 0;
			$res['count'] = $count;
			foreach($article as $v){
				$res['data'][] = ['id'=>$v['id'],
				'title'	=> $v['title'],
				'url'	=> (string) url('article/detail',['id'=>$v['id']]),
				'status'	=> $v['status'] ? '正常':'待审核',
				'ctime'		=> $v['create_time'],
				'datas'		=> $v['pv'].'阅/'.$v['comments_count'].'答'
				];
			} 
			
		} else {
			return json(['code'=>-1,'msg'=>'无数据']);
		}
		return json($res);	
	}
	
	//收藏list
	public function collList(){
		//收藏的帖子
		$collect = Collection::with(['article'=>function($query){
			$query->withCount('comments')->where('status',1);
		}])->where('user_id',$this->uid)->order('create_time','desc')->paginate(10);
		$count =$collect->total();
		$res = [];
		if($count){
			$res['code'] = 0;
			$res['count'] = $count ;
			foreach($collect as $v){
				
				$res['data'][] = [
					'id' 	=>$v['id'],
					'title'	=> $v->article->title,
					'url'	=> (string) url('article/detail',['id'=>$v['article_id']]),
					'auther' => $v->article->user->name,
					'ctime'=>	$v['create_time'],
					'comment' =>$v->article->comments_count,
				]; 
			}
			
		} else {
			return json(['code'=>-1,'msg'=>'无数据']);
		}
		return json($res);
	}
	
	
	
	//文章管理
	public function post()
	{
        return View::fetch();
    }
	
	//取消文章收藏
	public function colltDel()
	{
		if(Request::isAjax()){
			$collt = Collection::where('user_id',$this->uid)->find(input('id'));
			$result = $collt->delete();
			if($result){
				$res = ['code'=>0,'msg'=>'取消成功'];
			} else {
				$res = ['code'=>0,'msg'=>'取消失败'];
			}
			return json($res);
		}
	}

	//用户设置-我的资料
	public function set()
	{
		if(Request::isAjax()){
			$data = Request::only(['user_id','email','nickname','sex','city','area_id','sign']);
			$validate = new \app\common\validate\User;
			$result = $validate->scene('Set')->check($data);
			if(!$result){
				$this->error($validate->getError());
			} else {
                $user = new \app\common\model\User;
                $result = $user->setNew($data);
				if($result==1){
					Cache::tag('user')->clear();
				    return ['code'=>0,'msg'=>'资料更新成功'];
				} else {
					$this->error($result);
				}
			}
		}
		$area = Db::name('user_area')->select();
		View::assign(['area'=>$area]);
		return View::fetch();
    }

	//更换头像
	public function uploadHeadImg()
    {
        $file = request()->file('file');
		try {
			validate(['file'=>'fileSize:204800|fileExt:jpg,png,gif'])
            ->check(['file'=>$file]);
			$savename = \think\facade\Filesystem::disk('public')->putFile('head_pic',$file);
		} catch (think\exception\ValidateException $e) {
			return json(['status'=>-1,'msg'=>$e->getMessage()]);
		}
		$upload = Config::get('filesystem.disks.public.url');
        if($savename){
            //$name = $file->hashName();
            $name_path =str_replace('\\',"/",$upload.'/'.$savename);	
			//$image = \think\Image::open("uploads/$name_path");
			//$image->thumb(168, 168)->save("uploads/$name_path");

            //查出当前用户并把得到的用户头像更新
            $userId = Session::get('user_id');
            $result = Db::name('user')
                ->where('id',$userId)
                ->update(['user_img'=>$name_path]);
			Cache::tag(['user','tagArtDetail','tagArt'])->clear();
            if($result) {
				$res = ['status'=>0,'msg'=>'头像更新成功'];
            } else {
                $res = ['status'=>1,'msg'=>'头像更新失败'];
            }
        }else{
            $res = ['status'=>1,'msg'=>'上传错误'];
        }
	return json($res);
	}


    public function message()
    {
		$uid = Session::get('user_id');
		$msg = Message::receveMsg($uid);
		
		View::assign('msg',$msg);
        return View::fetch();
    }
	
	//个人页
    public function home($id)
    {
		//用户
		$u = Cache::get('user'.$id);
		if(!$u){
			$u = Db::name('user')->field('name,nickname,city,sex,sign,user_img,point,vip,create_time')->cache(3600)->find($id);
		}
		
		
		//用户发贴
		$arts = Db::name('user')->alias('u')->join('article a','u.id = a.user_id')->field('u.id,a.id,a.title,a.pv,a.is_hot,a.create_time,a.delete_time')->where('a.delete_time',0)->where('a.user_id',$id)->order(['a.create_time'=>'desc'])->cache(3600)->select();
		//用户回答
        $reys = Db::name('comment')->alias('c')->join('article a','c.article_id = a.id')->field('a.id,a.title,c.content,c.create_time,c.delete_time')->where(['a.delete_time'=>0,'c.delete_time'=>0])->where('c.user_id',$id)->order(['c.create_time'=>'desc'])->cache(3600)->select();
		
		View::assign(['u'=>$u,'arts'=>$arts,'reys'=>$reys,'jspage'=>'']);
        return View::fetch();
    }
	
	
    public function layout()
    {
        return View::fetch();
    }

	
	//邮箱激活
	public function activate()
	{
		$this->isLogin();
		$user['user_id'] = session::get('user_id');
		$user = UserModel::find($user['user_id']);
		$this->assign('user',$user);
		return view();
	}
	
	//修改密码
	public function setpass()
	{
		if(Request::isAjax()){
			$data = Request::param();
			$validate = new \app\common\validate\User();
			$res = $validate->scene('setPass')->check($data);
			if(!$res){
				return $this->error($validate->getError());
			}
		$user = new \app\common\model\User;
		$result = $user->setpass($data);
			if($result == 1) {
				Session::clear();
				return $this->success('密码修改成功 请登录', '/login');
			} else {
				return $this->error($result);
			}
		}
	}
	
	//退出账户
	public function logout()
	{
		Session::clear();
		Cookie::delete('auth');
		//Cookie::delete('user_name');
		//Cookie::delete('user_id');
		if(Session::has('user_id')){
			return json(['code' => -1, 'msg' => '退出失败']);
		} else {
			return json(['code' => 200, 'msg' => '退出成功', 'url' => '/']);	
		}
	}

}