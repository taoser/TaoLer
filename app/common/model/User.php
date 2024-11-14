<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Config;
use think\facade\Lang;
use app\event\UserLogin;
use taoler\com\Files;
use app\common\lib\JwtAuth;
use Exception;

class User extends Model
{
    protected $pk = 'id'; //主键
    protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'false';
    protected $updateTime = 'update_time';
    protected $loggedUser;
    //protected $auto = ['password']; //定义自动处理的字段
    //自动对password进行md5加密
    protected function setPasswordAttr($value){
        return md5($value);
    }

    //软删除
    use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
    //只读字段,禁止更改
    //protected $readonly = ['email'];
	
	//用户关联评论
	public function comments()
	{
		return $this->hasMany('Comment','user_id','id');
	}
	
	//用户关联所属区域
    public function userArea()
    {
        return $this->belongsTo('UserArea','user_raea_id','id');
    }

    public function article()
    {
        return $this->hasMany(Article::class);
    }
	
	//登陆校验
    public function login($data)
    {	
        //查询使用邮箱或者用户名登陆
        $user = $this::whereOr('phone',$data['name'])->whereOr('email',$data['name'])->whereOr('name',$data['name'])->findOrEmpty();

        if($user->isEmpty()){
			throw new Exception(Lang::get('username or password error'));
        }
        //被禁用和待审核
        if($user['status'] == -1){
            throw new Exception(Lang::get('Account disabled'));
        }
        if($user['status'] == 0){
            throw new Exception(Lang::get('Pending approval'));
        }
        //错误登陆连续3次且小于10分钟
        if((time() - $user->login_error_time < 60) && is_int($user->login_error_num/3)){	
            throw new Exception(Lang::get('Please log in 10 minutes later'));
        }
        
        //对输入的密码字段进行MD5加密，再进行数据库的查询
        $salt = substr(md5($user['create_time']),-6);
        $pwd = substr_replace(md5($data['password']),$salt,0,6);
        $password = md5($pwd);
        
        if($user['password'] !== $password){
             //密码错误登陆错误次数加1
             event(new UserLogin(['type'=>'logError','id'=>$user->id]));
      
             //连续3次错误
             if(is_int(($user->login_error_num+1)/3) && $user->login_error_num >0 ){
                 throw new Exception(Lang::get('Login error 3, Please log in 10 minutes later'));
             }

             throw new Exception(Lang::get('The user name or password is incorrect'));
        }
        //将用户数据写入Session
        Session::set('user_id',$user['id']);
        Session::set('user_name',$user['name']);
        //记住密码
        if(isset($data['remember'])){
            $salt = Config::get('taoler.salt');
            //加密auth存入cookie
            $auth = md5($user['name'].$salt).":".$user['id'];
            Cookie::set('auth',$auth,604800);
        }

        event(new UserLogin(['type'=>'log','id'=>$user->id]));

        //查询结果1表示有用户，用户名密码正确
        $this->loggedUser = $user;

        $token = JwtAuth::encode([
            'uid'       => $user['id'],
            'username'  => $user['name'],
            'avatar'    => $user['user_img']
        ]);

        return ['token' => $token];
        
    }

    //更新数据
    public function updata($data)
    {
        //dump($data);
    }
	
    //注册校验
    public function reg($data)
    {
        // public/static/res/images/avatar的所有图片
		$images = Files::getAllFile('static/res/images/avatar');
		//随机图片
		$i = array_rand($images);
		$img = $images[$i];
        $data['user_img'] = '/'.str_replace('\\','/',$img);
        //随机存入默认头像
        // $code = mt_rand('1','11');
        // $data['user_img'] = "/static/res/images/avatar/$code.jpg";
        $data['create_time'] = time();
        $salt = substr(md5($data['create_time']),-6);
        $data['password'] = substr_replace(md5($data['password']),$salt,0,6);
        $data['status'] = Config::get('taoler.config.regist_check');
        $data['nickname'] = $data['name'];
        $msg = $data['status'] ? '注册成功请登录' : '注册成功，请等待审核';
        try{
            $this->save($data);
        } catch(\Exception $e){
            throw new Exception("保存失败");
        }
        
        return true;
    }
	
	//重置密码
    public function respass($data)
    {	//halt($data);
		$user = $this->where('id', $data['uid'])->find();
		$salt = substr(md5($user['create_time']),-6);
		$data['password'] = substr_replace(md5($data['password']),$salt,0,6);
		$result = $user->save($data);
           if ($result) {
               return 1;
			} else{
               return '更改失败';
			}
    }
	
    //更新设置
    public function setNew($data)
    {
        $user = User::where('id', session('user_id'))->find();
        $result = $user->allowField(['email','active','nickname','sex','city','area_id','sign'])->save($data);
        if($result){
            return 1;
        }else{
            return '修改失败';
        }
    }
	
	//用户修改密码
	public function setpass($data)
	{
		$user = $this->find($data['user_id']);
		$salt = substr(md5($user['create_time']),-6);
		$pwd = substr_replace(md5($data['nowpass']),$salt,0,6);
		$data['nowpass'] = md5($pwd);
		$result = $data['nowpass'] == $user['password'];
		if(!$result){
			return '当前密码不正确';
		}
		$data['password'] = substr_replace(md5($data['password']),$salt,0,6); 
		$user->password = $data['password'];
		$result = $user->save();
		if($result){
			return 1;
		}else{
			return '修改失败,请改换密码';
		}
	}

    // 登录用户
    public function getLoggedUser(){
        return $this->loggedUser;
    }
	
	
}