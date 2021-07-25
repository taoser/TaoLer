<?php
namespace app\common\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'name|用户名' => 'require|min:2|max:18|chsDash|unique:user',
		'email|邮箱' => 'require|email|unique:user',
        'password|密码' => 'require|min:6|max:20',
        'repassword|确认密码'=>'require|confirm:password',
        'nickname|昵称' => 'require|min:2|max:20',      
        'captcha|验证码' => 'require|captcha',
		'city|城市' => 'min:2|max:25',
		'sign|签名' => 'min:10|max:100',
		'sex|性别' => 'require',
		'nowpass|新密码' => 'require|min:6|max:20',
		'code|校验码' => 'require|length:4',
    ];
	
	//邮件邮件码验证
	 public function sceneCode()
    {
        return $this->only(['code']);
    }

    //name登陆验证场景
    public function sceneLoginName()
    {
        return $this->only(['name','password','captcha'])
			->remove('name', 'unique');
    }
	
	//emai登陆验证场景
    public function sceneLoginEmail()
    {
        return $this->only(['email','password','captcha'])
			->remove('email', 'unique');
    }

        //注册验证场景
    public function sceneReg()
    {
        return $this->only(['name','email','password','repassword','captcha']);
            
    }
	
	//密码找回
    public function sceneForget()
    {
        return $this->only(['email','captcha'])
				->remove('email', 'unique');
    }
	
	//密码重设
    public function sceneRepass()
    {
        return $this->only(['password','repassword','captcha']);
    }
	
	//密码重置
    public function sceneRespass()
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