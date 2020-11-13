<?php

namespace app\admin\model;

use think\Model;
use think\facade\Db;
use think\facade\Session;
use think\model\concern\SoftDelete;

class Admin extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
	
	//管理员关联角色
/*
    public function authGroup()
    {
        return $this->belongsTo('AuthGroup','auth_group_id','id');
    }
*/

    //远程一对多管理员关联角色
    public function adminGroup()
    {
        return $this->hasManyThrough('AuthGroup', 'AuthGroupAccess','uid','id','id','group_id');
    }
    //管理员关联角色分配表
    public function authGroupAccess()
    {
        return $this->hasMany('AuthGroupAccess','uid','id');
    }
	
	//登陆校验
    public function login($data)
    {	
        //查询用户
        $admin = Db::name('admin')->where('username',$data['username'])->where('delete_time',0)->find();
		
		if($admin['status'] !=1){
			return '用户被禁用或未审核,请联系管理员';
		}
		//对输入的密码字段进行MD5加密，再进行数据库的查询
		$salt = substr(md5($admin['create_time']),-6);
		$pwd = substr_replace(md5($data['password']),$salt,0,6);
		$data['password'] = md5($pwd);
        if($admin['password'] == $data['password']){
			
			//将用户数据写入Session
			Session::set('admin_id',$admin['id']);
			Session::set('admin_name',$admin['username']);

			Db::name('admin')->where('id',$admin['id'])->update(
                        [
                            'last_login_time' => time(),
                            'last_login_ip' => request()->ip(),
                        ]
                    );
					
            //用户名密码正确返回1
            return 1;
        }else{
            return '用户名或密码错误';
        }
    }
}
