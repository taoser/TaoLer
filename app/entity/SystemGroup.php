<?php
namespace app\entity;

use think\facade\Cache;
use think\exception\ValidateException;

class SystemGroup extends BaseEntity
{
    public const CACHE_KEY = 'system_group';

    /**
     * 添加配置组
     * @param array $data 配置组数据
     * @return int 配置组ID
     */
    public function add(array $data): int
    {
        $this->group_name = $data['group_name'];
        $this->group_title = $data['group_title'];

        $this->save();

        return $this->id;
    }

    public function getGroupList(): array
    {
        return $this->field('id,group_name,group_title')->select()->toArray();
    }

    /**
     * 获取分组后的配置列表，用于渲染配置表单页面
     * @return array
     */
    public function getGroupFormList(): array
    {
        $groupList = $this->with(['config' => function($query){
            $query->order(['sort' => 'asc']);
        }])
        ->field('id,group_name,group_title')
        ->order(['sort' => 'asc'])
        ->select()
        ->toArray();

        return $groupList;
    }

}
