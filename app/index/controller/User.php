<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\validate\User as userValidate;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\Cache;
use think\facade\Cookie;
use think\facade\View;
use app\common\model\Article;
use app\common\model\Collection;
use app\common\model\User as userModel;
use taoler\com\Message;

class User extends BaseController
{	
	protected $middleware = [ 
    	'logincheck' => ['except' 	=> ['home'] ],
    ];
		
	//用户中心
	public function index()
	{
        return view();
    }
	
	
	// 我的发帖list
	public function myArticles()
	{
        $param = Request::only(['page','limit']);
		$myArticle = Article::field('id,cate_id,title,status,pv,create_time,update_time')
            ->withCount(['comments'])
            ->where(['user_id' => $this->uid])
            ->order('update_time','desc')
            ->paginate([
                'list_rows' => $param['limit'],
                'page' 		=> $param['page']
            ]);
		$count = $myArticle->total();
		$res = [];
		if($count){
			$res['code'] = 0;
			$res['count'] = $count;
			foreach($myArticle as $v){
				$res['data'][] = ['id'=>$v['id'],
					'title'	=> htmlspecialchars($v['title']),
					'url'	=> $this->getRouteUrl($v['id'], $v->cate->ename, $v->cate->appname),
					'status'	=> $this->artStatus($v['status']),
					'ctime'		=> $v['create_time'],
					'utime'		=> $v['update_time'],
					'pv'		=> $v['pv'],
					'datas'		=> $v['comments_count'].'答'
				];
			} 
			return json($res);
		}
			
		return json(['code'=>-1,'msg'=>'无数据']);			
	}

	// 文章状态
	private function artStatus($status)
	{
		switch ($status) {
			case 1:
				$res = '正常';
				break;
			case -1:
				$res = '禁止';
				break;
			default:
				$res = '待审';
				break;
		}
		return $res;
	}
	
	// 收藏list
	public function myCollect()
	{
		//收藏的帖子
		$collect = Collection::with(['article'=>function($query){
			$query->withTrashed();
		}])->where('user_id',$this->uid)->order('id','desc')->paginate(10);
		$count =$collect->total();

		if($count){
			$res = [];
			$res['code'] = 0;
			$res['count'] = $count ;
			foreach($collect as $v){
				$res['data'][] = [
					'id' 	=>$v['id'],
					'title'	=> htmlspecialchars($v['collect_title']),
					'url'	=> $this->getRouteUrl($v['article_id'], $v->article->cate->ename),
					'auther' => $v['auther'],
					'status' => is_null(Db::name('article')->field('id')->where('delete_time',0)->find($v['article_id'])) ? '已失效' : '正常',
					'ctime' =>	$v['create_time']
				]; 
			}
			return json($res);
		}
		return json(['code'=>-1,'msg'=>'无数据']);		
	}
	
	
	
	//文章管理
	public function post()
	{
        return View::fetch();
    }

	// 编辑pv
	public function edtiPv()
	{
		if(Request::isAjax()){
			$param = Request::param(['id','pv']);
			$res = Db::name('article')->save(['id' => $param['id'], 'pv' => $param['pv']]);
			if($res) {
				return json(['code' => 0, 'msg' => '修改成功！']);
			}
		}
		return json(['code' => -1, 'msg' => '修改失败！']);
	}

	// 刷新
	public function updateTime()
	{
		if(Request::isAjax()){
			$param = Request::param(['data']);
			if(count($param) == 0) return json(['code' => -1, 'msg' => '未选中任何数据！']);
			$idArr = [];
			foreach($param['data'] as $v) {
				$idArr[] = $v['id'];
			}
			$count = count($idArr);

			// vip每天可刷新数
			$user = Db::name('user')->field('id,vip,point,auth')->find($this->uid);

			$refreshRule = Db::name('user_viprule')->field('refreshnum,refreshpoint')->where('vip', $user['vip'])->find();	
			// 检测刷新帖子剩余量
			$refreshLog = Db::name('user_article_log')->field('id,user_refreshnum')->where(['user_id' => $this->uid])->whereDay('create_time')->find();
			if(is_null($refreshLog)) {// 新增
				Db::name('user_article_log')->save(['user_id' => $this->uid, 'create_time' => time()]);
				$refreshLog = Db::name('user_article_log')->field('id,user_refreshnum')->where(['user_id' => $this->uid])->whereDay('create_time')->find();
			}
			// 超级管理员排外
			if($user['auth'] === '0') {
				$cannum =  $refreshRule['refreshnum'] - $refreshLog['user_refreshnum']; // 可用免费数
				// 刷帖先扣积分
				if($cannum <= 0) { // a.免费额已用完 后面需要积分
					$canpoint = $count * $refreshRule['refreshpoint'];
					$point = $user['point'] - $canpoint;
					if($point < 0) { 
						// 1.积分不足
						return json(['code' => -1, 'msg' => "免额已使用,本次需{$canpoint}积分,请充值！"]);
					} else {
						// 2.扣除积分
						Db::name('user')->where('id', $this->uid)->update(['point' => $point]);
					}
				} else { // b.未超限 有剩余条数
					if($count > $cannum) { // 本次刷新数量大于剩余免费数量，需要支付积分
						$canpoint = ($count - $cannum) * $refreshRule['refreshpoint'];
						$point = $user['point'] - $canpoint;
						if($point < 0) { 
							// 1.积分不足
							return json(['code' => -1, 'msg' => "免额已使用,本次需{$canpoint}积分,额度不足请充值！"]);
						} else {
							// 2.扣除积分
							Db::name('user')->where('id', $this->uid)->update(['point' => $point]);
						}
					}
				}
			}

			// 刷新数据
			$res = Db::name('article')->where('id', 'in', $idArr)->update(['update_time' => time()]);
			if($res > 0) {
				// 记录刷帖日志
				Db::name('user_article_log')->where('id', $refreshLog['id'])->inc('user_refreshnum', $count)->update();
				
				return json(['code' => 0, 'msg' => '刷新成功！']);
			}
		}
		return json(['code' => -1, 'msg' => '刷新失败！']);
	}
	
	//取消文章收藏
	public function colltDel()
	{
		if(Request::isAjax()){
			$collt = Collection::where('user_id',$this->uid)->find(input('id'));
			$result = $collt->delete();
			if($result){
				$res = ['code'=>0,'msg'=>'取消成功'];
			} else {
				$res = ['code'=>0,'msg'=>'取消失败'];
			}
			return json($res);
		}
	}

	//用户设置-我的资料
	public function set()
	{
		if(Request::isAjax()){
			$data = Request::only(['email','phone','nickname','sex','city','area_id','sign']);
            $data['user_id'] = $this->uid;
			// 过滤
			$sign = strtolower($data['sign']);
			if(strstr($sign, 'script')) return json(['code'=>-1,'msg'=>'包含有非法字符串script脚本']);
			if(strstr($sign, 'alert')) return json(['code'=>-1,'msg'=>'包含有非法字符alert']);
			if(strstr($sign, 'img')) return json(['code'=>-1,'msg'=>'禁用img标签']);
			if(strstr($sign, 'body')) return json(['code'=>-1,'msg'=>'禁用img标签']);
			if(strstr($sign, 'video')) return json(['code'=>-1,'msg'=>'禁用video标签']);

			// 验证
			$validate = new userValidate;
			$result = $validate->scene('Set')->check($data);
			if(!$result){
				return json(['code'=>-1,'msg' =>$validate->getError()]);
			} else {
				//防止重复的email
				$resEmail = Db::name('user')->where('email',$data['email'])->where('id','<>',$this->uid)->find();
				if(!is_null($resEmail)){
					return ['code'=>-1,'msg'=>'email已存在,请更换！'];
				}
				//若更换email，需重新激活
				$mail = Db::name('user')->where('id',$this->uid)->value('email');
				if($data['email'] !== $mail){
					$data['active'] = 0;
				}
                $user = new userModel;
                $result = $user->setNew($data);
				if($result == 1){
					Cache::tag('user')->clear();
				    return json(['code'=>0,'msg'=>'资料更新成功']);
				} else {
                    return json(['code'=>-1,'msg' =>$result]);
				}
			}
		}
		$area = Db::name('user_area')->select();
		View::assign(['area'=>$area]);
		return View::fetch();
    }

	//更换头像
	public function uploadHeadImg()
    {
        $uploads = new \app\common\lib\Uploads();
        $upRes = $uploads->put('file','head_img',1024,'image','uniqid');
        $upHeadRes = $upRes->getData();
        if($upHeadRes['status'] == 0){
            $name_path = $upHeadRes['url'];
            //$name = $file->hashName();
			//$image = \think\Image::open("uploads/$name_path");
			//$image->thumb(168, 168)->save("uploads/$name_path");

            //查出当前用户头像删除原头像并更新
			$imgPath = Db::name('user')->where('id',$this->uid)->value('user_img');
			if(file_exists('.'.$imgPath)){
				$dirPath    = dirname('.'.$imgPath);
				if($dirPath !== './static/res/images/avatar'){ //防止删除默认头像
					unlink('.'.$imgPath);
				}
			}
            $result = Db::name('user')
                ->where('id',$this->uid)
                ->update(['user_img'=>$name_path]);
			Cache::tag(['user','tagArtDetail','tagArt'])->clear();
            if($result) {
				$res = ['code'=>0,'msg'=>'头像更新成功'];
            } else {
                $res = ['code'=>1,'msg'=>'头像更新失败'];
            }
        } else {
            $res = ['code'=>1,'msg'=>'上传错误'];
        }
		return json($res);
	}


    public function message()
    {
		$uid = Session::get('user_id');
		$msg = Message::receveMsg($uid);
		
		View::assign('msg',$msg);
        return View::fetch();
    }
	
	//个人页
    public function home($id)
    {
		//用户
		$u = Db::name('user')->field('name,nickname,city,sex,sign,user_img,point,vip,create_time')->find($id);
	
		$article = new Article();
		$arts = $article->getUserArtList((int) $id);

		//用户回答
		// $commont = new Comment();
		// $reys = $commont->getUserCommentList((int) $id);

        $reys = Db::name('comment')
		->alias('c')
		->join('article a', 'c.article_id = a.id')
		->join('cate t', 'a.cate_id = t.id')
		->field('a.id,a.title,t.ename,c.content,c.create_time,c.delete_time,c.status')
		->where(['a.delete_time' => 0, 'c.delete_time' => 0, 'c.status' => 1])
		->where('c.user_id', $id)
		->order(['c.id' => 'desc'])
		->limit(10)
		->cache(3600)->select();

		View::assign(['u'=>$u,'arts'=>$arts,'reys'=>$reys,'jspage'=>'']);
        return View::fetch();
    }
	
	
    public function layout()
    {
        return View::fetch();
    }

	//邮箱激活
	public function activate()
	{
		//管理员邮箱
		$adminEmail = Db::name('user')->where('id',1)->cache(true)->value('email');
		View::assign('adminEmail',$adminEmail);
		 return View::fetch();
	}
	
	//邮箱激活
	public function active()
	{

		if(Request::isPost()){
			$email = Request::param('email');
			$url = Request::domain().Request::root().'/active/index?url='.time().md5($email).$this->uid;
			$content = "Hi亲爱的{$this->showUser($this->uid)['name']}:</br>您正在进行邮箱激活，请在10分钟内完成激活。 <a href='{$url}' target='_blank' >请点击进行激活</a> </br>若无法跳转请复制链接激活：{$url}";
			$res = hook('mailtohook',[$email,'邮箱激活',$content]);
			if($res){
				return json(['status'=>0]);
			}else{
				return json(['status'=>-1,'发送邮件出错！']);
			}
		}
	}
	
	//修改密码
	public function setPass()
	{
		if(Request::isAjax()){
			$data = Request::param();
			$validate = new userValidate;
			$res = $validate->scene('setPass')->check($data);
			if(!$res){
                return json(['code'=>-1,'msg' =>$validate->getError()]);

			}
			$user = new userModel;
			$result = $user->setpass($data);
			if($result == 1) {
				Session::clear();
				Cookie::delete('auth');
				return json(['code' => 1, 'msg' => '密码修改成功', 'data' => ['url' => (string) url('login/index')]]);
			}

            return json(['code' => -1,'msg' =>$result]);
		}
	}
	
	//退出账户
	public function logout()
	{
		Session::delete('user_name');
		Session::delete('user_id');
		Cookie::delete('auth');

		if(Session::has('user_id')){
			return json(['code' => -1, 'msg' => '退出失败']);
		}
        return json(['code' => 200, 'msg' => '退出成功', 'url' => '/']);
	}

}