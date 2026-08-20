<?php
namespace app\entity;

use Exception;
use think\exception\ValidateException;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Config;
use think\facade\Lang;
use think\facade\Cache;
use app\event\UserLogin;
use app\common\helper\FileHelper;
use app\common\helper\JwtAuth;
use app\common\helper\PasswordHash;
use app\validate\User as UserValidate;



class User extends BaseEntity
{

    /**
     * 根据标识符查询用户
     *
     * @param string $identifier 用户标识符（手机、邮箱或昵称）
     * @return array|null
     */
    public function findUserByIdentifier($identifier)
    {
        // 判断输入的格式，进行相应查询
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $this->where('email', $identifier)->find();
        } elseif (preg_match('/^1[3-9]\d{9}$/', $identifier)) {
            return $this->where('phone', $identifier)->find();
        } else {
            return $this->where('nickname', $identifier)->find();
        }
    }
	
	/**
     * 登录校验
     *
     * @param array $data 登录数据（用户名/手机号/邮箱、密码、验证码（可选）（仅在登录配置中开启验证码时必填））
     * @return array 登录成功后的用户信息  ['token','expire_time']
     */
    public function login(array $data): array
    {
        // 检验登录是否开放
        if(Config::get('taoler.config.is_login') == 0 ) {
			throw new Exception("Sorry,Sorry, website maintenance, temporarily unable to log in!", -1);
        }

        // 校验验证码
        if(Config::get('taoler.config.login_captcha') == 1 && !captcha_check($data['captcha'])) {				
			throw new Exception("验证码失败", -1);
        }

        // 登陆请求
		try {
			// 英文和中文用户名邮箱正则表达式
			$patternEmail = "/^[A-Za-z0-9\x{4e00}-\x{9fa5}]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/u";
            // 手机号正则表达式
			$patternTel = "/^1[3-9]\d{9}$/";

			if(preg_match($patternTel, $data['name'])) {
				//手机验证登录
				validate(UserValidate::class)->scene('loginPhone')
				->check(['phone' => $data['name'],'password'=>$data['password']]);
				$user = $this->where('phone', $data['name'])->findOrEmpty();

			} elseif (preg_match($patternEmail, $data['name'])) {
				//输入邮箱email登陆验证				
				validate(UserValidate::class)->scene('loginEmail')
				->check(['email' => $data['name'],'password'=>$data['password']]);
				$user = $this->where('email', $data['name'])->findOrEmpty();

			} else {
				//用户名name登陆验证
				validate(UserValidate::class)->scene('loginName')
				->check(['name' => $data['name'],'password'=>$data['password']]);
				$user = $this->where('name', $data['name'])->findOrEmpty();
			}
		} catch (ValidateException $e) {
            throw new Exception($e->getError(), -1);
		} catch(Exception $e) {
			throw new Exception($e->getMessage(), -1);
		}

        if($user->isEmpty()) {
			throw new Exception(Lang::get('username or password error'), -1);
        }

        //被禁用和待审核
        if($user['status'] == -1) {
            throw new Exception(Lang::get('Account disabled'), -1);
        }

        if($user['status'] == 0) {
            throw new Exception(Lang::get('Pending approval'), -1);
        }

        //错误登陆连续3次且小于10分钟
        if((time() - $user->login_error_time < 60) && is_int($user->login_error_num/3)) {	
            throw new Exception(Lang::get('Please log in 10 minutes later'), -1);
        }

        $result = PasswordHash::verify($data['password'], $user['password']);
        
        if(!$result['ok']) {
             //密码错误登陆错误次数加1
             event(new UserLogin(['type'=>'logError','id'=>$user->id]));
      
             //连续3次错误
             if(is_int(($user->login_error_num+1)/3) && $user->login_error_num >0 ) {
                 throw new Exception(Lang::get('Login error 3, Please log in 10 minutes later'), -1);
             }

             throw new Exception(Lang::get('The user name or password is incorrect'), -1);
        }

        //将用户数据写入Session
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);

        //记住密码
        if(isset($data['remember'])) {
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

        // 过期时间
        $expireTime = JwtAuth::getExpireTime();

        return ['token' => $token, 'expire_time' => $expireTime];
        
    }

    /**
     * 添加用户
     * @param array $data ['name','email','phone','password','nickname','city','sex','auth','note']
     * @return bool
     */
    public function add(array $data): bool
    {
        // 检验注册是否开放
		if(config('taoler.config.is_regist') == 0 ) {
            throw new Exception('抱歉，注册暂时未开放', -1);
		}

        $registType = Config::get('taoler.config.regist_type');

		// 验证码
		if($registType == 1) {				
			//先校验验证码 // 验证失败
			if(!captcha_check($data['captcha'])){
                throw new Exception("验证码失败", -1);
			};
		}

        // 邮箱
		if($registType == 2) {
			$emailCode = Cache::get($data['email']);
			if(!$emailCode) {
                throw new Exception("验证码过期，请重试", -1);
			}

			if($data['email_code'] !== $emailCode) {
                throw new Exception("验证码不正确", -1);
			}
		}

        //校验场景中reg的方法数据
        try{
            validate(UserValidate::class)
                ->scene('Reg')
                ->check($data);
        } catch (ValidateException $e) {
            throw new Exception($e->getError(), -1);
        }

        $data['password'] = PasswordHash::make($data['password']);
        $data['status'] = Config::get('taoler.config.regist_check');
        $data['nickname'] = $data['name'];

        // public/static/avatar的所有图片
		$images = FileHelper::getDirFilePaths(public_path().'static/avatar');
		//随机图片
		$i = array_rand($images);
		$img = $images[$i];
        $data['user_img'] = '/'.str_replace('\\','/',$img);
        //随机存入默认头像
        // $code = mt_rand('1','11');
        // $data['user_img'] = "/static/res/images/avatar/$code.jpg";        
        
        return $this->save($data);

    }

    //更新数据
    public function updata($data)
    {
        //dump($data);
    }
	
	/**
     * 重置密码
     * @param array $data ['uid','password']
     * @return bool
    */
    public function reSetPassword(array $data): bool
    {
        $this->id = $data['uid'];
		$this->password = PasswordHash::make($data['password']);
        
		return $this->save();
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