<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\validate\User as userValidate;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Cache;
use think\facade\View;
use app\common\model\Article;
use app\common\model\Collection;
use app\common\model\User as userModel;
use think\facade\Config;

class User extends BaseController
{	
	protected $middleware = [ 
    	'logincheck' => ['except' 	=> ['home'] ],
    ];
		
	//用户中心
	public function index()
	{
		//$this->isLogin();
		$user['user_id'] = session::get('user_id');
		$username = session::get('user_name');
		
        return view();
    }
	//文章管理
	public function post()
	{
		//发表的帖子
		$user['user_id'] = session::get('user_id');
		$username = session::get('user_name');
		
		$article = Article::withCount('comments')->where('user_id',$user['user_id'])->order('update_time','desc')->paginate([
		'list_rows'=>10,
		'page',
		'path' => 'post',
		'fragment' => 'index',
		'var_page' => 'page',
		]);
		$page = $article->render();
        View::assign(['article'=>$article,'page'=>$page]);
		
		
		//收藏的帖子
		$collect = Collection::with('article')->where('user_id',$user['user_id'])->order('create_time','desc')->paginate(10);

		$count =$collect->total();
		View::assign(['collect'=>$collect,'count'=>$count]);
		
        return View::fetch();
    }
	
	//取消文章收藏
	public function colltDel()
	{
		$collt = Collection::where('article_id',input('id'))->where('user_id',session::get('user_id'))->find();
		$result = $collt->delete();
		if($result){
			$this->success('取消成功');
		} else {
			$this->error('取消失败了');
		}
	}

	//用户设置-我的资料
	public function set()
	{
		if(Request::isAjax()){
			$data = Request::only(['user_id','email','nickname','sex','city','sign']);
			$validate = new \app\common\validate\User();
			$result = $validate->scene('Set')->check($data);
			if(!$result){
				$this->error($validate->getError());
			} else {
                $user = new \app\common\model\User();
                $result = $user->setNew($data);
				if($result==1){
				    return ['code'=>0,'msg'=>'资料更新成功','url'=>'/index/user/set'];
				} else {
					$this->error($result);
				}
			}
		}
		return View::fetch();
    }

	//更换头像
	public function uploadHeadImg()
    {
        $file = request()->file('file');
		try {
			validate(['image'=>'filesize:2048|fileExt:jpg,png,gif|image:200,200,jpg'])
            ->check(array($file));
			$savename = \think\facade\Filesystem::disk('public')->putFile('head_pic',$file);
		} catch (think\exception\ValidateException $e) {
			echo $e->getMessage();
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
        return view();
    }
	
	//个人页
    public function home()
    {
		$userID = input('id');
		Session::get('user_id');
		//用户
		$u = Db::name('user')->field('name,nickname,city,sex,sign,user_img,point,create_time')->find($userID ?: Session::get('user_id'));
		//用户发贴
		$arts = Db::name('user')->alias('u')->join('article a','u.id = a.user_id')->field('u.id,a.id,a.title,a.pv,a.is_hot,a.create_time,a.delete_time')->where('a.delete_time',0)->where('a.user_id',input('id') ?: Session::get('user_id'))->select();
		//用户回答
        $reys = Db::name('comment')->alias('c')->join('article a','c.article_id = a.id')->field('a.id,a.title,c.content,c.create_time,c.delete_time')->where('a.delete_time',0)->where('c.user_id',input('id') ?: Session::get('user_id'))->select();
		
		View::assign('u',$u);
		View::assign('arts',$arts);
		View::assign('reys',$reys);
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
		$user = new \app\common\model\User();
		$result = $user->setpass($data);
			if($result == 1) {
				Session::clear();
				return $this->success('密码修改成功 请登录', '/index/user/login');
			} else {
				return $this->error($result);
			}
		}
	}
	
	//退出账户
	public function logout()
	{
		Session::clear();
		return json(array('code' => 200, 'msg' => '退出成功'));
	}

}