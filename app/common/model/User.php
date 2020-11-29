<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Db;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Log;
use taoler\com\Api;

class User extends Model
{
    protected $pk = 'id'; //主键
    protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'false';
    protected $updateTime = 'update_time';
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
	
	//登陆校验
    public function login($data)
    {	
        //查询使用邮箱或者用户名登陆
        $user = $this::whereOr('email',$data['name'])->whereOr('name',$data['name'])->find();

		//对输入的密码字段进行MD5加密，再进行数据库的查询
		$salt = substr(md5($user['create_time']),-6);
		$pwd = substr_replace(md5($data['password']),$salt,0,6);
		$data['password'] = md5($pwd);

        if($user['password'] == $data['password']){
			//将用户数据写入Session
			Session::set('user_id',$user['id']);
			Session::set('user_name',$user['name']);
			if(isset($data['remember'])){
				$salt = 'taoler';
				//加密auth存入cookie
				$auth = md5($user['name'].$salt).":".$user['id'];
				Cookie::set('auth',$auth,604800);
				//Cookie::set('user_id', $user['id'], 604800);
				//Cookie::set('user_name', $user['name'], 604800);
			}
            
            //查询结果1表示有用户，用户名密码正确
            return 1;
        }else{
            return '用户名或密码错误';
        }
    }

    //更新数据
    public function updata($data)
    {
        //dump($data);
    }
	
    //注册校验
    public function reg($data)
    {	
			//随机存入默认头像
			$code = mt_rand('1','11');
			$data['user_img'] = "/static/res/images/avatar/$code.jpg";	
			$data['create_time'] = time();
			$salt = substr(md5($data['create_time']),-6);
			$data['password'] = substr_replace(md5($data['password']),$salt,0,6);
            $result = $this->save($data);
           if ($result) {
               return 1;
           } else{
               return '注册失败';
           }
    }
	
	//重置密码
    public function respass($data)
    {	//halt($data);
		$user = $this->where('id',$data['uid'])->find();
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
        $userId = $data['user_id'];
        $user = User::where('id',$userId)->find();
        $result = $user->allowField(['email','nickname','sex','city','area_id','sign'])->save($data);
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
	
	
}