<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Session;
use think\facade\Cache;
use app\common\model\Comment as CommentModel;
use app\common\model\Article;
use app\common\model\UserZan;


class Comment extends BaseController
{
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