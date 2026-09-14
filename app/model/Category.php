<?php

namespace app\model;

use think\model\concern\SoftDelete;
use think\facade\Lang;

class Category extends BaseModel
{
    //软删除
	use SoftDelete;

    protected function getOptions(): array 
    {
        return [
            'autoWriteTimestamp'    => true,
            'deleteTime'            => 'delete_time',
            'defaultSoftDelete'     => null,
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
        return $this->hasOne(Page::class, 'category_id', 'id');
    }
	
    // 获取url
    public function getUrlAttr($value, $data)
    {
        if($data['type'] === 3) { // 自定义url
            return $data['url'];
        }
        return (string) url('category', ['ename' => $data['ename']]);
    }

    /**
     * 根据语言获取分类名称
     * @param string $value 分类名称
     * @param array $data 分类数据
     * @return string 分类名称
     */
    public function getNameAttr($value, $data): string
    {
        $lang = Lang::getLangSet();

        if($lang === 'en-us') {
            return $data['ename'];
        }
        return $data['name'];
    }

}