<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-13 18:11:15
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\model\AuthRule.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\model;

use think\model\concern\SoftDelete;

class AuthRule extends BaseModel
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

    /**
     * 关联语言
     */
    public function lang()
    {
        return $this->hasMany(AuthRuleLang::class);
    }

    /**
     * 获取图标
     */
    public function getIconAttr($value, $data)
    {
        return empty($value) ? '' : 'layui-icon ' . $value;
    }

    /**
     * 获取标题
     */
    public function getTitleAttr($value, $data)
    {
        // $langSet = app()->lang->getLangSet();
        // $langItem = $this->lang->first();
        // return $langItem['title'] ?? '';

        $langSet = app()->lang->getLangSet();
        $fallbackLang = 'en-us'; // 降级改为英文

        $current = $this->lang->where('lang', $langSet)->first();
        if ($current && !empty(trim($current['title']))) {
            return $current['title'];
        }
        $fallback = $this->lang->where('lang', $fallbackLang)->first();
        return $fallback['title'] ?? '';




    }
	
	public function searchIdAttr($query, $value, $data)
    {
        $query->where('id', $value );      
    }

}
