<?php
namespace app\entity;

use Exception;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Config;
use think\facade\Lang;
use app\event\UserLogin;
use app\common\helper\FileHelper;
use app\common\helper\JwtAuth;
use app\validate\User as UserValidate;
use think\exception\ValidateException;

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
	
	//登陆校验
    public function login($data)
    {
        //登陆请求
		try{
			// 英文和中文用户名邮箱正则表达式
			$patternEmail = "/^[A-Za-z0-9\x{4e00}-\x{9fa5}]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/u";
            // 手机号正则表达式
			$patternTel = "/^1[3-9]\d{9}$/";

			if(preg_match($patternTel, $data['name'])) {
				//手机验证登录
				validate(UserValidate::class)->scene('loginPhone')
				->check(['phone' => $data['name'],'password'=>$data['password']]);
				$user = $this->where('phone', $data['name'])->findOrEmpty();
			} elseif (preg_match($patternEmail, $data['name'])){
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
            throw new ValidateException($e->getError());
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}

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
        $salt = substr(md5($user->getData('create_time')),-6);
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

        $expireTime = JwtAuth::getExpireTime();

        return ['token' => $token, 'expire_time' => $expireTime];
        
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
		$images = FileHelper::getDirFilePaths('static/res/images/avatar');
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