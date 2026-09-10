<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-10 16:46:47
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\entity\SystemGroup.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\entity;

use think\facade\Cache;
use think\exception\ValidateException;
use app\common\helper\FileHelper;

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
            $query->where('is_hidden', 0)->order(['sort' => 'asc']);
        }])
        ->field('id,group_name,group_title')
        ->order(['sort' => 'asc'])
        ->select()
        ->toArray();

        // 处理tpl类型配置项的options
        foreach($groupList as &$group) {
            foreach($group['config'] as &$item) {
                // 模板列表
                if($item['type'] === 'tpl') {
                    $item['options'] = $this->getTplList();
                }
                // 首页模板列表
                if($item['type'] === 'indextpl') {
                    $item['options'] = $this->getIndextplList();
                }
            }
            unset($item);
        }
        unset($group);

        return $groupList;
    }

    protected function getTplList(): array
    {
        $tplPath = root_path() . 'view' . DIRECTORY_SEPARATOR;
        if(!is_dir($tplPath)){
            return [];
        }
        return FileHelper::getSubDirNames($tplPath);
    }

    protected function getIndextplList(): array
    {
        $tplName = system_config('tpl_name');
        $tplPath = root_path() . 'view' . DIRECTORY_SEPARATOR . $tplName . DIRECTORY_SEPARATOR . 'index' . DIRECTORY_SEPARATOR;
        if(!is_dir($tplPath)){
            return [];
        }
        return FileHelper::getDirFileBaseNames($tplPath);
    }

}
