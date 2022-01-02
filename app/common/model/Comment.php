<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Cache;

class Comment extends Model
{
	protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
	
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
	public function article()
	{
		//评论关联文章
		return $this->belongsTo('Article','article_id','id');
	}
	
	public function user()
	{
		//评论关联用户
		return $this->belongsTo('User','user_id','id');
	}

	//获取评论
    public function getComment($id, $page)
    {
	    $comments = $this::with(['user'])->where(['article_id'=>$id,'status'=>1])->order(['cai'=>'asc','create_time'=>'asc'])->paginate(['list_rows'=>10, 'page'=>$page]);
	    return $comments;
    }

    //回帖榜
    public function reply($num)
    {
        $res = Cache::get('reply');
        if(!$res){
            $user = User::withCount('comments')->order(['comments_count'=>'desc','last_login_time'=>'desc'])->limit($num)->select();
            if($user)
            {
                $res['status'] = 0;
                $res['data'] = array();
                foreach ($user as $key=>$v) {

                    $u['uid'] = (string) url('user/home',['id'=>$v['id']]);
                    $u['count(*)'] = $v['comments_count'];
                    if($v['nickname'])
                    {
                        $u['user'] = ['username'=>$v['nickname'],'avatar'=>$v['user_img']];
                    } else {
                        $u['user'] = ['username'=>$v['name'],'avatar'=>$v['user_img']];
                    }
                    $res['data'][] = $u;
                }
            }
            Cache::set('reply',$res,3600);
        }

        return json($res);
    }
	
}