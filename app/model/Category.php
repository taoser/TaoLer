<?php

namespace app\model;

use Exception;
use think\db\exception\DbException;
use think\db\Query;
use think\facade\Db;
use think\facade\Cache;
use app\common\helper\IdEncode;
use think\facade\Route;

class Category extends BaseModel
{
    
    protected function getOptions(): array 
    {
        return [
            'name' => 'cate' // 表名
        ];
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'pid');
    }

    //关联文章
	public function article()
    {
        return $this->hasMany(Article::class);
    }

    //关联主题单页
    public function page()
    {
        return $this->hasOne(Page::class);
    }
	
    // 获取url
    public function getUrlAttr($value, $data)
    {
        if($data['type'] === 3) { // 自定义url
            return $data['url'];
        }
        return (string) url('cate', ['ename' => $data['ename']]);
    }

}