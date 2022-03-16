<?php
namespace app\index\Controller;

use app\common\controller\BaseController;
use app\common\lib\Msgres;
use app\common\validate\User as userValidate;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Cache;
use think\facade\View;
use think\facade\Config;
use app\common\model\User;

class Login extends BaseController
{
	//已登陆中间件检测
	protected $middleware = [
	    'logedcheck' => ['except' 	=> ['index'] ]
    ];
	
	//给模板中JScace文件赋值
    protected function initialize()
    {
		parent::initialize();
		View::assign(['jspage'=>'']);
	}

    //用户登陆
	public function index()
	{
        //已登陆跳出
        if(Session::has('user_id')){
            return redirect((string) url('user/index'));
        }
		//获取登录前访问页面refer
		$refer = Request::server('HTTP_REFERER');
		//$domain = Request::domain();
		//截取域名后面的字符
		//$url = substr($refer,strlen($domain));
        Cookie::set('url',$refer);
        if(Request::isAjax()) {
			
            //登陆前数据校验
			$data = Request::param();
			if(Config::get('taoler.config.login_captcha') == 1)
			{				
				//先校验验证码
				if(!captcha_check($data['captcha'])){
				 // 验证失败
				 return json(['code'=>-1,'msg'=> '验证码失败']);
				};
			}
			
			//邮箱正则表达式
			$pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
			//判断输入的是邮箱还是用户名
		   if (preg_match($pattern, $data['name'])){
               //输入邮箱email登陆验证
               $data['email'] = $data['name'];
			   unset($data['name']);
			   try{
                    validate(userValidate::class)
                        ->scene('loginEmail')
                        ->check($data);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return json(['code'=>-1,'msg'=>$e->getError()]);
                }
                $data['name'] = $data['email'];
				unset($data['email']);
		   } else {
			   //用户名name登陆验证
			   try{
                    validate(userValidate::class)
                        ->scene('loginName')
                        ->check($data);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
					return json(['code'=>-1,'msg'=>$e->getError()]);
                }  
		   }			
			//登陆请求
			$user = new User();
			$res = $user->login($data);
            if ($res == 1) {	//登陆成功
                return Msgres::success('login_success',Cookie::get('url'));
            } else {
				return Msgres::error($res);
            }
        }
		
        return View::fetch('login');
	}

    //注册
    public function reg()
    {
        if(Request::isAjax()){
			$data = Request::only(['name','email','password','repassword','captcha']);
		
			//校验场景中reg的方法数据
			try{
				validate(userValidate::class)
					->scene('Reg')
					->check($data);
			} catch (ValidateException $e) {
				return json(['code'=>-1,'msg'=>$e->getError()]);
			}

			$user = new User();
			$result = $user->reg($data);
		
           if ($result['code'] == 1) {
			   $res = ['code'=>0,'msg'=>$result['msg'],'url'=>(string) url('login/index')];
			   if(Config::get('taoler.config.email_notice')) mailto($this->showUser(1)['email'],'注册新用户通知','Hi亲爱的管理员:</br>新用户 <b>'.$data['name'].'</b> 刚刚注册了新的账号，请尽快处理。');
           }else {
			   $res = ['code'=>-1,'msg'=>$result];
           }
		   return json($res);
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
		$user = Db::name('user')->where('email',$data['email'])->find();
            if($user) {
                $code = mt_rand('1111','9999');
                Cache::set('code',$code,600);
                Cache::set('userid',$user['id'],600);

                $result = mailto($data['email'],'重置密码','Hi亲爱的'.$user['name'].':</br>您正在维护您的信息，请在10分钟内验证，您的验证码为:'.$code);
                if($result){
                    Cache::set('repass','postcode',60);	//设置repass标志为1存入Cache
					$res = ['code'=>0,'msg'=>'验证码已发送成功，请去邮箱查看！','url'=>(string) url('login/postcode')]; 
                } else {
                    $res = ['code'=>-1,'msg'=>'验证码发送失败!'];
                }	
            }else{
                $res = ['code' =>-1,'msg'=>'邮箱错误或不存在'];
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
		$code = Request::only(['code']);
		try{
            validate(userValidate::class)
                        ->scene('Code')
                        ->check($code);
        } catch (ValidateException $e) {
            return json(['code'=>-1,'msg'=>$e->getError()]);
        }

		    if(Cache::get('code')==$code['code']) { //无任何输入情况下需排除code为0和Cache为0的情况
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
			$user = new User();
			$res = $user->respass($data);
				if ($res == 1) {
						return json(['code'=>0,'msg'=>'修改成功','url'=>(string) url('login/index')]);
				} else {
						return json(['code'=>-1,'msg'=>'$res']);		
				}
        }
		return View::fetch('forget');
	}

}