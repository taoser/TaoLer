<?php
namespace app\index\Controller;

use app\common\validate\User as userValidate;
use think\exception\ValidateException;
use think\facade\Request;
use think\facade\Session;
use think\facade\Cache;
use think\facade\View;
use think\facade\Config;
use app\common\model\User;
use Exception;
use Symfony\Component\VarExporter\Internal\Exporter;

class Login extends IndexBaseController
{
	protected $users = null;
	//已登陆中间件检测
	protected $middleware = [
	    'logedcheck' => ['except' 	=> ['index','status'] ]
    ];

	public function __construct(\think\App $app)
	{
		parent::__construct($app);
		$this->users = new User();
	}

    //用户登陆
	public function index()
	{
        //已登陆跳出
        if(Session::has('user_id')){
            return redirect((string) url('user/index'));
        }
		//获取登录前访问页面refer
        $refer = str_replace(Request::domain(), '', Request::server('HTTP_REFERER'));

        if(Request::isAjax()) {
			// 检验登录是否开放
			if(config('taoler.config.is_login') == 0 ) return json(['code'=>-1,'msg'=>'抱歉，网站维护中，暂时不能登录哦！']);
            //登陆前数据校验
			$data = Request::only(['name','email','phone','password','captcha','remember']);
			if(Config::get('taoler.config.login_captcha') == 1) {				
				//先校验验证码
				if(!captcha_check($data['captcha'])){
				 // 验证失败
				 return json(['code'=>-1,'msg'=> '验证码失败']);
				};
			}
						
			//登陆请求
			try{
				//邮箱正则表达式
				// $patternEmail = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
				// 包含英文和中文用户名邮箱
				$patternEmail = "/^[A-Za-z0-9\x{4e00}-\x{9fa5}]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/u";
				$patternTel = "/^1[3-9]\d{9}$/";

				if(preg_match($patternTel, $data['name'])) {
					//手机验证登录
					$data['phone'] = $data['name'];
					unset($data['name']);
					validate(userValidate::class)
						->scene('loginPhone')
						->check($data);
					
					$data['name'] = $data['phone'];
					unset($data['phone']);

				} elseif (preg_match($patternEmail, $data['name'])){
					//输入邮箱email登陆验证
					$data['email'] = $data['name'];
					unset($data['name']);
					
					validate(userValidate::class)
						->scene('loginEmail')
						->check($data);
					
					$data['name'] = $data['email'];
					unset($data['email']);
				} else {
					//用户名name登陆验证
					validate(userValidate::class)
						->scene('loginName')
						->check($data);
						  
				}

				$res = $this->users->login($data);
				
			} catch (ValidateException $e) {
				return json(['code'=>-1,'msg'=>$e->getError()]);
			} catch(Exception $e) {
				return json(['code' => -1, 'msg' => $e->getMessage()]);
			}
			return json(['code' => 0, 'msg' => '登录成功', 'data' => ['token' => $res['token'], 'url' => $refer]]);
        }

        return View::fetch('login');
	}

    //注册
    public function reg()
    {
        if(Request::isAjax()){
			// 检验注册是否开放
			if(config('taoler.config.is_regist') == 0 ) return json(['code'=>-1,'msg'=>'抱歉，注册暂时未开放']);

			$data = Request::only(['name','email','email_code','password','repassword','captcha']);

			// 验证码
			if(Config::get('taoler.config.regist_type') == 1) {				
				//先校验验证码
				if(!captcha_check($data['captcha'])){
					// 验证失败
					return json(['code'=>-1,'msg'=> '验证码失败']);
				};
			}

			// 邮箱
			if(Config::get('taoler.config.regist_type') == 2) {
				$emailCode = Cache::get($data['email']);
				if($emailCode) {
					if($data['email_code'] != $emailCode) {
						// 验证失败
				 		return json(['code' => -1,'msg' => '验证码不正确']);
					}
				} else {
					return json(['code' => -1,'msg' => '验证码过期，请重试']);
				}
			}
		
			//校验场景中reg的方法数据
			try{
				validate(userValidate::class)
					->scene('Reg')
					->check($data);
			} catch (ValidateException $e) {
				return json(['code'=>-1,'msg'=>$e->getError()]);
			}

			try{
				$this->users->reg($data);
				return json([
					'code' => 0,
					'msg'=> '注册成功',
					'url'=>(string) url('login/index')
				]);
			   if(Config::get('taoler.config.email_notice')) hook('mailtohook',[$this->showUser(1)['email'],'注册新用户通知','Hi亲爱的管理员:</br>新用户 <b>'.$data['name'].'</b> 刚刚注册了新的账号，请尽快处理。']);
			} catch(\Exception $e){
				return json(['code'=>-1,'msg'=>'注册失败！']);
			}
        }

        return View::fetch();
    }
	
	//找回密码
	public function forget()
	{
		if(Request::isAjax()){
			$data = Request::param();
			
			try{
				validate(userValidate::class)
					->scene('Forget')
					->check($data);
			} catch (ValidateException $e) {
				return json(['code'=>-1,'msg'=>$e->getError()]);
			}
			//查询用户
			$user = $this->users::field('id,name')->where('email',$data['email'])->find();
			if(is_null($user)) {
				return json(['code' =>-1,'msg'=>'邮箱错误或不存在']);
			}

			$code = mt_rand(1111, 9999);
			Cache::set('code', $code, 600);
			Cache::set('userid', $user['id'], 600);

			$result = hook('mailtohook',[
				$data['email'],
				'重置密码',
				"Hi亲爱的{$user['name']}:</br>您正在维护您的信息，请在10分钟内验证，您的验证码为:{$code}"
			]);

			if($result){
				Cache::set('repass','postcode',60);	//设置repass标志为1存入Cache
				$res = ['code'=>0,'msg'=>'验证码已发送成功，请去邮箱查看！','url'=>(string) url('login/postcode')]; 
			} else {
				$res = ['code'=>-1,'msg'=>'验证码发送失败!'];
			}
			return json($res);
		}
		return View::fetch();
	}
	
	//接收验证码
	public function postcode()
	{
        if(Cache::get('repass') !== 'postcode'){
			return redirect((string) url('login/forget'));
        }

        if(Request::isAjax()){
			$code = input('code');
			try{
				validate(userValidate::class)
					->scene('Code')
					->check($code);
			} catch (ValidateException $e) {
				return json(['code'=>-1,'msg'=>$e->getError()]);
			}

		    if(Cache::get('code') == $code) { //无任何输入情况下需排除code为0和Cache为0的情况
                //Cache::delete('repass');
                Cache::set('repass','resetpass',60);
				$res = ['code'=>0,'msg'=>'验证成功','url'=>(string) url('login/respass')];
		    } else {
			    $res = ['code'=>-1,'msg'=>'验证码错误或已过期！'];
		    }
			return json($res);
        }
		
		return View::fetch('forget');
	}
	
	//忘记密码找回重置
	public function respass()
	{
        if(Cache::get('repass') !== 'resetpass'){
            return redirect((string) url('login/forget'));
        }
        if(Request::isAjax()){
            $data = Request::param();
			try{
				validate(userValidate::class)
							->scene('Repass')
							->check($data);
			} catch (ValidateException $e) {
				return json(['code'=>-1,'msg'=>$e->getError()]);
			}	
			
			$data['uid'] = Cache::get('userid');
			
			$res = $this->users->respass($data);
				if ($res == 1) {
					return json(['code'=> 0, 'msg'=> '修改成功', 'url'=>(string) url('login/index')]);
				} else {
					return json(['code'=> -1, 'msg'=> '$res']);		
				}
        }
		return View::fetch('forget');
	}

	// 邮箱注册验证
	public function sentMailCode()
	{
		if(Request::isAjax()) {
			// 用户邮箱
			$email = input('email');
			//dump($email);
			if(empty($email)) return json(['code'=>-1,'msg'=>'邮箱不能为空']);

			$code = mt_rand('1111','9999');
			Cache::set($email, $code, 600);

			$result = hook('mailtohook',[
				$email,
				'注册邮箱验证码',
				'Hi亲爱的新用户:</br>您正在注册我们站点的新账户，请在10分钟内验证，您的验证码为:'.$code
			]);

			if($result == 1) {
				$res = ['code' => 0, 'msg' => '验证码已发送成功，请去邮箱查看！']; 
			} else {
				$res = ['code' => -1, 'msg' => $result];
			}
			return json($res);
		}
		
	}

	public function status() {
		$user = $this->user;
		if(empty($user)) {
			return json(['code' => 0]);
		}

		$data = [
			'name' => $user['name'],
			'avatar' => $user['user_img'],
			'user_home' => (string) url('user_home', ['id' => $user['id']])
		];
		return json(['code' => 1, 'msg' => 'ok', 'data' => $data]);
	}

}