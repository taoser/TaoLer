<?php

namespace app\admin\model;

use think\Model;
use think\facade\Db;
use think\facade\Session;
use app\oil\model\Station;
use think\facade\Cookie;
use think\facade\Config;
use think\model\concern\SoftDelete;

class Admin extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
	protected $createTime = 'false';
	
	//自动对password进行md5加密
    protected function setPasswordAttr($value){
        return md5($value);
    }
	
	//管理员关联角色
/*
    public function authGroup()
    {
        return $this->belongsTo('AuthGroup','auth_group_id','id');
    }
*/
	//管理员关联站点
	public function station()
    {
        return $this->belongsTo(Station::class);
    }

    //远程一对多管理员关联角色
    public function adminGroup()
    {
        return $this->hasManyThrough('AuthGroup', 'AuthGroupAccess','uid','id','id','group_id');
    }
    //管理员关联角色分配表
    public function authGroupAccess()
    {
        return $this->hasMany(AuthGroupAccess::class,'uid');
    }
	
	//登陆校验
    public function login($data)
    {
        //查询用户
        $admin = $this->where('username',$data['username'])->where('delete_time',0)->find();

		if(is_null($admin)){
			return json(['code'=>-1,'msg'=>'用户名或密码错误']);
		}
		if($admin['status'] !=1){
			return json(['code' => -1,'msg'=> '用户被禁用或未审核,请联系管理员']);
		}
		//对输入的密码字段进行MD5加密，再进行数据库的查询
		$salt = substr(md5($admin['create_time']),-6);
		$pwd = substr_replace(md5($data['password']),$salt,0,6);
		$data['password'] = md5($pwd);
		
        if($admin['password'] == $data['password']){
			
			//将用户数据写入Session
			Session::set('admin_id',$admin['id']);
			Session::set('admin_name',$admin['username']);
			
			if(isset($data['remember'])){
				$salt = Config::get('taoler.salt');
				//加密auth存入cookie
				$auth = md5($admin['username'].$salt).":".$admin['id'];
				Cookie::set('adminAuth',$auth,604800);
			}

			Db::name('admin')->where('id',$admin['id'])->update(
                        [
                            'last_login_time' => time(),
                            'last_login_ip' => request()->ip(),
                        ]
                );
					
            //用户名密码正确返回1
            $res = ['code'=>0,'msg'=>'登陆成功', 'url'=>(string) url('index/index')];
        } else {
			$res = ['code'=>-1,'msg'=>'用户名或密码错误','url'=>(string) url('admin/login')];
		}
		return json($res);
    }
	
	//修改密码
	public function setpass($data)
	{
		$admin = $this->find($data['admin_id']);
		$salt = substr(md5($admin['create_time']),-6);
		$oldPassword = $this->pass($salt,$data['oldPassword']);
		if($oldPassword != $admin['password']){
			return json(['code'=>-1,'msg'=>'当前密码错误']);
		}
		
		if($data['password'] != $data['repassword']){
			return json(['code'=>-1,'msg'=>'两次密码不一致']);
		}

		$data['password'] = substr_replace(md5($data['password']),$salt,0,6); 
		$admin->password = $data['password'];
		$result = $admin->save();
		
		if($result){
			$res = ['code'=>0,'msg'=>'修改密码成功'];
		} else {
			$res = ['code'=>-1,'msg'=>'修改密码失败'];
		}
		
		return json($res);
	}
	
	//加密规则 加密字符串，原始秘密
	protected function pass($salt, $pass)
	{
		$pwd = substr_replace(md5($pass),$salt,0,6);
		return md5($pwd);
	}
	
}
