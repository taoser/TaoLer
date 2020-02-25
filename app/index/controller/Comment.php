<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\Session;
use app\common\model\Comment as CommentModel;
use app\common\model\Article;
use app\common\model\UserZan;
use think\facade\Request;
use think\Db;

class Comment extends BaseController
{
	//采纳评论
    public function jiedaCai(){
		
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
        }
	 return json($res);
    }

    //删除评论
    public function jiedaDelete(){
		
		$id = input('id');
        //$arid = intval($id);

        $comms = CommentModel::find($id);
		$result = $comms->delete();
        //$result =  Db::name('collection')->where('article_id',$arid)->delete();
        if($result){
            //$res=['type' => 'add','type' => 'remove', 'msg' => '收藏成功'];
            $res = [
                'status' => 0,
            ]; 
        }
	 return json($res);
    }
	
	
	//编辑评论
	public function getDa()
	{
		$this->isLogin();
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
		$this->isLogin();
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
	
	//点赞评论
	public function jiedaZan()
	{
		$this->isLogin();
		$data['comment_id'] = input('post.id');
		$data['user_id'] = session('user_id');
		//查询是否已存在点赞
		$zan = UserZan::where(['comment_id'=>input('post.id'),'user_id'=>session('user_id')])->find();
		Session::set('ok',$zan['comment_id']);
		if(!$zan ){ //如果没有点过赞执行点赞操作
			$coms = CommentModel::find(input('post.id'));
			if($coms['user_id'] == session('user_id')){
				return $res=['msg' => '不能给自己点赞哦'];
			} else {
				$res = UserZan::create($data);
				if($res){
					//评论点赞数加1
					$coms->save(['zan' => $coms['zan']+1]);
					return $res=['status' => 0, 'msg' => '点赞成功'];
				}else {
					$this->error('点赞失败');
				}
			}
			
		} else {
			return $res=['status'=>1,'msg' => '你已赞过了'];
		}
	}	
}