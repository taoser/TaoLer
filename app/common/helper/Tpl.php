<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-10 20:15:35
 * @LastEditTime: 2026-09-10 21:07:41
 * @LastEditors: TaoLer
 * @Description: 模板助手类
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\common\helper\Tpl.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\common\helper;

class Tpl
{
    /**
     * 获取所有模板名称
     */
    public static function getAllTplNames(): array
    {
        $tplPath = root_path() . 'view' . DIRECTORY_SEPARATOR;
        if(!is_dir($tplPath)){
            return [];
        }
        
        return FileHelper::getSubDirNames($tplPath);
    }

    /**
     * 获取所有首页模板名称
     */
    public static function getIndexTplNames(): array
    {
        $tpl = system_config('current_tpl');
        if(empty($tpl)){
            return [];
        }

        $tplPath = root_path() . 'view' . DIRECTORY_SEPARATOR . $tpl . DIRECTORY_SEPARATOR . 'index' . DIRECTORY_SEPARATOR;

        return FileHelper::getDirFileBaseNames($tplPath);
    }

    /**
     * 获取当前模板分类所有模板名称
     */
    public static function getCurrentTplCategoryNames(): array
    {
        $tpl = system_config('current_tpl');
        if(empty($tpl)) {
            return [];
        }

        $tplPath = root_path() . 'view' . DIRECTORY_SEPARATOR . $tpl . DIRECTORY_SEPARATOR . 'category' . DIRECTORY_SEPARATOR;

        return FileHelper::getSubDirNames($tplPath);
    }
}
