<?php
declare (strict_types = 1);

namespace app\model;

use Exception;
use think\model\concern\SoftDelete;
use app\observer\ArticleObserver;
use app\common\helper\IdEncode;
use think\facade\Route;

class Content extends BaseModel
{
	//软删除
	use SoftDelete;
    
    protected function getOptions(): array 
    {
        return [
            'autoWriteTimestamp'    => true,
            'deleteTime'            => 'delete_time',
            'defaultSoftDelete'     => null,
            'eventObserver'         => ArticleObserver::class,
            'jsonAssoc'             => true,
            'lazyFields'            => 'pv' // 延迟写入pv
        ];
    }


    //文章关联栏目表
    public function cate()
    {
        return $this->belongsTo(Category::class); // 内连接：无匹配分类的文章会被过滤;
    }
	
	//文章关联评论
	public function comments()
	{
		return $this->hasMany(Comment::class);
	}
	
	//文章关联收藏
	public function collection()
	{
		return $this->hasMany(Collection::class);
	}

    //文章关联用户点赞
	public function userzan()
	{
		return $this->hasMany(UserZan::class);
	}
	
	//文章关联用户
	public function user()
	{
		return $this->belongsTo(User::class);
	}

    //文章关联Tag表
	public function taglist()
	{
		return $this->hasMany(Taglist::class);
	}



    // 两种模式 获取url
    public function getUrlAttr($value, $data)
    {
        $data['id'] = IdEncode::encode($data['id']);
        $ename = Category::where('id', $data['category_id'])->cache(true)->value('ename');
        return (string) Route::buildUrl('article_detail', ['id' => $data['id'],'ename' => $ename])->domain(true);
       
    }

    /**
     * 获取主图
     *
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function getMasterPicAttr($value, $data)
    {
        if($data['has_image'] > 0 && isset($data['media']['images'])) {
            return $data['media']['images'][0];
        }
        return '';
    }

}