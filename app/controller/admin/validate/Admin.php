<?php
namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'username|用户名' => 'require|min:2|max:18|unique:admin',
        'password|密码' => 'require|min:6|max:20',
        'repassword|确认密码'=>'require|confirm:password',
        'nickname|昵称' => 'require|min:2|max:20',
        'email|邮箱' => 'require|email|unique:admin',
        'captcha|验证码' => 'require|captcha',
		'city|城市' => 'min:2',
		'sign|签名' => 'min:10',
		'sex|性别' => 'require',
		'nowpass|密码' => 'require|min:6|max:20',
    ];

    //登陆验证场景
    public function sceneLogin()
    {
        return $this->only(['username','password','captcha'])
			->remove('username', 'unique');
    }

        //注册验证场景
    public function sceneReg()
    {
        return $this->only(['username','password','repassword','email','captcha']);
            //->append('email','unique:user');
           // ->remove('password', 'confirm');
    }
	
	//密码找回
    public function sceneForget()
    {
        return $this->only(['email','captcha']);
    }
	
	//密码重设
    public function sceneRepass()
    {
        return $this->only(['password','repassword','captcha']);
    }
	
	//用户资料
	public function sceneSet()
	{
		return $this->only(['email','nickname','ctity','sex','sign'])
					->remove('email','unique');
	}
	
	//设置新密码
	public function sceneSetpass()
	{
		return $this->only(['nowpass','password','repassword']);
	}
}