<?php
namespace app\index\Controller;

use Exception;
use think\Request;
use think\Response;
use think\exception\ValidateException;
use think\facade\Validate;
use think\facade\Session;
use think\facade\Cache;
use think\facade\View;
use think\facade\Config;
use app\facade\User;
use app\index\validate\User as UserValidate;
use app\index\controller\IndexBaseController;

class Login extends IndexBaseController
{
	protected $userModel;

	//已登陆中间件检测
	protected $middleware = [
	    'logedcheck' => ['except' 	=> ['index', 'login', 'status']]
    ];

	public function initialize()
	{
		parent::initialize();
		$this->userModel = new User();
	}

    //用户登陆
	public function index()
	{
        //已登陆跳出
        if(Session::has('user_id')){
            return redirect((string) url('user_index'));
        }
		
        return View::fetch('login');
	}

	public function login(Request $request)
	{
		$data = $request->post(['name','email','phone','password','captcha','remember']);
	
		$res = User::login($data);

		return json([
			'code' => 0,
			'msg' => '登录成功',
			'data' => [
				'token'			=> $res['token'],
				'expire_time'	=> $res['expire_time'],
				'url'			=> (string) url('user_index')
			]
		]);
	}

    //注册
    public function register(Request $request)
    {
        if (!$request->isPost()) {
			return View::fetch();
		}

		$data = $request->post(['name','email','password','repassword','email_code','captcha']);
		
		$this->userModel::add($data);

		if (Config::get('taoler.config.email_notice')) {
			// hook('mailtohook',[
			// 	$this->$adminEmail,
			// 	'新用户注册通知',
			// 	"Hi亲爱的管理员:</br>新用户 <b>{$data['name']}</b> 刚刚注册了新的账号，请尽快处理。"
			// ]);
		}

		return json([
			'code'	=> 0,
			'msg'	=> '注册成功',
			'url'	=> (string) url('login_index')
		]);
    }
	
	//找回密码
	public function forget(Request $request)
	{
		if($request->isAjax()){
			$data = $request->post();
			
			try{
				validate(UserValidate::class)
					->scene('Forget')
					->check($data);
			} catch (ValidateException $e) {
				return json(['code'=>-1,'msg'=>$e->getError()]);
			}
			//查询用户
			$user = $this->userModel::field('id,name')->where('email',$data['email'])->find();
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
	public function postcode(Request $request)
	{
        if(Cache::get('repass') !== 'postcode'){
			return redirect((string) url('login/forget'));
        }

        if($request->isAjax()){
			$code = $request->get('code');
			try{
				validate(UserValidate::class)
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
	public function respass(Request $request)
	{
        if(Cache::get('repass') !== 'resetpass'){
            return redirect((string) url('login/forget'));
        }
        if($request->isAjax()){
            $data = $request->post();
			try{
				validate(UserValidate::class)
							->scene('Repass')
							->check($data);
			} catch (ValidateException $e) {
				return json(['code'=>-1,'msg'=>$e->getError()]);
			}	
			
			$data['uid'] = Cache::get('userid');
			
			$res = $this->userModel::respass($data);
			if ($res == 1) {
				return json(['code'=> 0, 'msg'=> '修改成功', 'url'=>(string) url('login/index')]);
			}

			return json(['code'=> -1, 'msg'=> '$res']);		
        }

		return View::fetch('forget');
	}

	// 邮箱注册验证
	public function sentMailCode(Request $request)
	{
		if($request->isPost()) {
			// 用户邮箱
			$email = $request->get('email');
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
			'avatar' => $user['avatar'],
			'user_home' => (string) url('user_home', ['id' => $user['id']])
		];

		return json(['code' => 1, 'msg' => 'ok', 'data' => $data]);
	}

}