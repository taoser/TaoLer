<?php
declare (strict_types = 1);

namespace app\index\model;


use Exception;
use app\common\model\BaseModel;
use think\model\concern\SoftDelete;
use app\observer\ArticleObserver;
use app\common\lib\IdEncode;
use Symfony\Component\VarExporter\Internal\Exporter;
use think\facade\Route;

class Article extends BaseModel
{
	//软删除
	use SoftDelete;
    protected function getOptions(): array 
    {
        return [
            'autoWriteTimestamp'    => true,
            'deleteTime'            => 'delete_time',
            'defaultSoftDelete'     => 0,
            'eventObserver'         => ArticleObserver::class,
            'jsonAssoc'             => true,
        ];
    }

    // 延迟写入pv
    protected $lazyFields = ['pv'];

    //文章关联栏目表
    public function cate()
    {
        return $this->belongsTo(Category::class);
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

        if(empty(config('taoler.url_rewrite.article_as'))) {
            $ename = Category::where('id', $data['cate_id'])->cache(true)->value('ename');
            return (string) Route::buildUrl('article_detail', ['id' => $data['id'],'ename' => $ename])->domain(true);
        }

        return (string) Route::buildUrl('article_detail',['id' => $data['id']])->domain(true);
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

    /**
     * APP应用转换,在后台admin应用转换为在其它app应用的路径
     * /admin/user/info转换为 /index/user/info
     * $appName 要转换为哪个应用
     * $url 路由地址
     * return string
     */
    public function getAurlAttr($value, $data)
    {
        $asName = config('taoler.url_rewrite.article_as'); //详情页URL别称

        $data['id'] = IdEncode::encode($data['id']);
        
        if(empty($asName)) {
            $ename = Category::where('id', $data['cate_id'])->cache(true)->value('ename');
            $url = (string) Route::buildUrl("{$ename}/{$data['id']}");
        } else if(!empty($asName)) {
            $url = (string) Route::buildUrl($asName."{$data['id']}");
        } else {
            $url = (string) Route::buildUrl("{$data['id']}");
        }

        $appName = 'index';

        // 判断应用是否绑定域名
        $app_bind = array_search($appName, config('app.domain_bind'));
        // 判断应用是否域名映射
        $app_map = array_search($appName, config('app.app_map'));

        // 判断admin应用是否绑定域名
        $bind_admin = array_search('admin',config('app.domain_bind'));
        // 判断admin应用是否域名映射
        $map_admin = array_search('admin',config('app.app_map'));

        //1.admin绑定了域名
        if($bind_admin) {
            // 1.应用绑定了域名
            if($app_bind) {
                return $url;
            }
            // 2.应用进行了映射
            if($app_map){
                return $appName . $url;
            }
            // 3.应用未绑定域名也未进行映射
            return $appName . $url;
        }

        //2.admin进行了映射
        if($map_admin) {
            // 1.应用绑定了域名
            if($app_bind) {
                return str_replace($map_admin, '', $url);;
            }
            // 2.应用进行了映射
            if($app_map){
                return str_replace($map_admin, $app_map, $url);
            }
            // 3.应用未绑定域名也未进行映射
            return str_replace($map_admin, $appName, $url);
        }
        //3.admin未绑定域名也未映射
        // 1.应用绑定了域名
        if($app_bind) {
            return $url;
        }
        // 2.应用进行了映射
        if($app_map){
            return str_replace('admin', $app_map, $url);
        }
        return str_replace('admin', $appName, $url);
        
    }

}