<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Session;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Config;
use app\common\model\Comment as CommentModel;
use app\common\model\Article;
use app\common\model\UserZan;
use taoler\com\Message;


class Comment extends BaseController
{
	protected $middleware = ['logincheck'];

	//文章评论
	public function add()
	{
		// 检验发帖是否开放
		if(config('taoler.config.is_reply') == 0 ) return json(['code'=>-1,'msg'=>'抱歉，系统维护中，暂时禁止评论！']);

		if (Request::isAjax()){
			//获取评论
			$data = Request::only(['content','article_id','pid','to_user_id']);
            $data['user_id'] = $this->uid;
			$sendId = $data['user_id'];
			$table = $this->getTableName($data['article_id']);
			$art = Db::table($table)->field('id,status,is_reply,delete_time')->find($data['article_id']);

			if($art['delete_time'] != 0 || $art['status'] != 1 || $art['is_reply'] != 1){
				return json(['code'=>-1, 'msg'=>'评论不可用状态']);
			}
			if(empty($data['content'])){
				return json(['code'=>-1, 'msg'=>'评论不能为空！']);
			}
			$superAdmin = Db::name('user')->where('id',$sendId)->value('auth');
			$data['status'] = $superAdmin ? 1 : Config::get('taoler.config.commnets_check');
			$msg = $data['status'] ? '留言成功' : '留言成功，请等待审核';
				
			//用户留言存入数据库
			if (CommentModel::create($data)) {
				//站内信
				$article = Db::table($table)->field('id,title,user_id,cate_id')->where('id',$data['article_id'])->find();
                // 获取分类ename,appname
                $cateName = Db::name('cate')->field('ename,appname')->find($article['cate_id']);
				//$link = (string) url('article_detail',['id'=>$data['article_id']]);
                $link = $this->getRouteUrl($data['article_id'], $cateName['ename'], $cateName['appname']);

				//评论中回复@user comment
				$preg = "/@([^@\s]*)\s/";
				preg_match($preg,$data['content'],$username);
				if(isset($username[1])){
					$receveId = Db::name('user')->whereOr('nickname', $username[1])->whereOr('name', $username[1])->value('id'); 
				} else {
					$receveId = $article['user_id'];
				}
				$data = ['title' => $article['title'], 'content' => '评论通知', 'link' => $link, 'user_id' => $sendId, 'type' => 2]; //type=2为评论留言
				Message::sendMsg($sendId, $receveId, $data);
				if(Config::get('taoler.config.email_notice')) hook('mailtohook',[$this->adminEmail,'评论审核通知','Hi亲爱的管理员:</br>用户'.$this->user['name'].'刚刚对 <b>' . $article['title'] . '</b> 发表了评论，请尽快处理。']);
				$res = ['code'=>0, 'msg'=>$msg];
			} else {
				$res = ['code'=>-1, 'msg'=>'留言失败'];
			}
			return json($res);
		}
	}

	//采纳评论
    public function jiedaCai()
    {
		$id = input('id');
        $comms = CommentModel::find($id);
		$result = $comms->save(['cai' =>1]);
		$res = [];
        if($result){
				$art = Article::find($comms['article_id']);
				$jie = $art->save(['jie' => 1]);
				if($jie){
					$res['status'] = 0; 
				}
			// 清除文章tag缓存
			Cache::tag('tagArtDetail')->clear();
        }
	 return json($res);
    }

    //删除评论
    public function jiedaDelete()
    {
        if(!session('?user_id')) return json(['code'=>-1,'msg'=>'未登录']);
		$id = input('id');
        $comms = CommentModel::find($id);
		$result = $comms->delete();
        if($result){
            $res = ['status' => 0,'msg' => '删除成功'];
        } else {
            $res = ['status' => -1,'msg' => '删除失败'];
        }
	 return json($res);
    }

	//编辑评论
	public function getDa()
	{
	    //获取原评论
        if(!session('?user_id')) return json(['code'=>-1,'msg'=>'未登录']);
		$id = input('id');
		$comms = CommentModel::find($id);
		$res['rows'] = [];
		if($comms) {
			$res['status'] = 0;
			$res['rows']['content'] = $comms['content'];		
		}
		return json($res);
	}
	
	//更新评论
	public function updateDa()
	{
        if(!session('?user_id')) return json(['code'=>-1,'msg'=>'未登录']);
		$id = input('id');
		$content = input('content');
		$comms = CommentModel::find($id);
		$result = $comms->save(['content' => $content]);
		if($result) {
			$res['status'] = 0;
			$res['msg'] = '更新成功';
		} else {
			$res['msg'] = '更新失败';
		}
		return json($res);
	}

    //更新评论
    public function edit()
    {
        if(!session('?user_id')) return json(['code'=>-1,'msg'=>'未登录']);
        if(Request::isAjax()) {
            $param = Request::param();
//            halt($param);
            $result = CommentModel::update($param);
            if($result) {
                return json(['code' => 0, 'msg' => '编辑成功']);
            }
            return json(['code' => 0, 'msg' => '编辑失败']);
        }
        $comms = CommentModel::find(input('id'));
        View::assign(['comment' => $comms, 'jspage' => '']);
        return View::fetch();

    }
	
	//评论点赞
	public function jiedaZan()
	{
        if(!session('?user_id')) return json(['code'=>-1,'msg'=>'未登录']);
		$data['comment_id'] = input('post.id');
		$data['user_id'] = session('user_id');
		//查询是否已存在点赞
		$zan = UserZan::where(['comment_id'=>input('post.id'),'user_id'=>session('user_id')])->find();
		
		if(!$zan){ //如果没有点过赞执行点赞操作
			$coms = CommentModel::find(input('post.id'));
			if($coms['user_id'] == session('user_id')){
				$res = ['msg' => '不能给自己点赞哦'];
			} else {
				$result = UserZan::create($data);
				if($result){
					//评论点赞数加1
					$coms->save(['zan' => $coms['zan']+1]);
					$res = ['status' => 0, 'msg' => '点赞成功'];
				}else {
                    $res = ['status' => -1, 'msg' => '点赞失败'];
				}
			}
		} else {
			Session::set('ok',$zan['comment_id']);
			$res = ['status'=>-1,'msg' => '你已赞过了'];
		}
		return json($res);
	}
}