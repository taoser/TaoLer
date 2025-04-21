<?php

namespace app\common\model;

use think\Model;
use think\facade\Db;

class BaseModel extends Model
{
    /**
     * article 查、改、删时需要传入id,获取所在表的后缀
     *
     * @param integer $id
     * @return string
     */
    public static function byIdGetSuffix(int $id): string
    {
        // 主数据表后缀为空，id存在主表中
        $suffix = '';
        $num = (int) floor(($id - 1) / config('taoler.single_table_num'));
        // num > 0, id在对应子表中
        if($num > 0) {
            //数据表后缀
            $suffix = "_{$num}";
        }

        return $suffix;
    }

    /**
     * 根据id数组，获取以表前缀为key, id为值的数组 ['' => [1,2,3], '_1' => [5,6,7,8,9],'_2' => [10,11]]
     *
     * @param array $idArr
     * @return array
     */
    public static function getSfxKeyIdValueArrByIdArr(array $idArr): array
    {
        $suffixIdArr = [];
        foreach($idArr as $id){
            $key = self::byIdGetSuffix($id);
            $suffixIdArr[$key][] = $id;
        }
        
        return $suffixIdArr;
    }

        /**
     * 获取子表报名数组['tao_article_1','tao_article_2','tao_article_3']
     * @param string|null $tableName
     * @return array
     */
    public static function getSubTables(?string $tableName = null): array
    {
        $tables = [];
        // 表前缀
        $prefix = !is_null($tableName) ? config('database.connections.mysql.prefix') . strtolower($tableName) : self::getTable();
        $sql = "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME REGEXP '{$prefix}_[0-9]+';";
        $sqlTables = Db::query($sql);
        // 是否有子数据表
        if(count($sqlTables)){
            $tables = array_column($sqlTables,'TABLE_NAME');
        }

        return $tables;
    }

    /**
     * 获取所有分表的后缀数组 ['_1','_2','_3']
     * @param string|null $tableName
     * @return array
     */
    public static function getSubTablesSuffix(?string $tableName = null): array
    {
        $tableSuffixArr = [];
        $tableArr = self::getSubTables($tableName);
        if(!empty($tableArr)) {
            $tableSuffixArr = array_map(function ($element) {
                $lastUnderscorePos = strrpos($element, '_');
                if ($lastUnderscorePos!== false) {
                    return substr($element, $lastUnderscorePos);
                }
                return $element;
            }, $tableArr);
        }

        return array_reverse($tableSuffixArr);
    }

    /**
     * 查询表及分表 统计 总计 后缀名 后缀统计
     *
     * @param array $where 查询条件 
     * @param [type] $class 类
     * @return void
     */
    public static function getSuffixMap(array $where = [], ?string $class = null)
    {
        $MODEL_CLASS_NAME = !is_null($class) ? $class : static::class;
        // 单个分表文章数统计 倒叙
        $countArr = [];
        // 数据总和
        $totals = 0;

        // 处理类名和表名
        if(is_null($class)) {
            $MODEL_CLASS_NAME = static::class;
            $tableArr = explode('_', static::getTable());
            $tableName = end($tableArr);
        } else {
            $MODEL_CLASS_NAME = $class;
            $parts = explode('\\', $class);
            $lastPart = end($parts);
            $tableName = strtolower($lastPart);
        }
        
        // 得到所有的分表后缀 倒叙排列
        $tableSuffixArr = self::getSubTablesSuffix($tableName);
        // 主表没有后缀，添加到分表数组中
        $tableSuffixArr[] = '';

        // 表综合
        $tableCount = count($tableSuffixArr);

        if($tableCount) {
            foreach($tableSuffixArr as $sfx) {
                $total = $MODEL_CLASS_NAME::suffix($sfx)->where($where)->count();
                $countArr[] = $total;
                $totals += $total;
            }
        }

        return [
            'countArr'          => $countArr, // 单个分表文章数统计 倒叙
            'totals'            => $totals, // 数据总和
            'tableSuffixArr'    => $tableSuffixArr, // 所有表后缀(包括主表)
            'tableCount'        => $tableCount // 表数量
        ];
        
    }

        /**
     * 增加数据时，判定是否需要新建数据库 自动获取分表后缀 自动判定maxID
     * 不传参数时，自动判定为当前表
     * '_1','_2'
     * @param string|null $tableName
     * @return string
     */
    public static function insertDataByTableNameGetSuffix(?string $tableName = null): string
    {
        $single_table_num = config('taoler.single_table_num');
        // 表前缀
        $prefix = !is_null($tableName) ? config('database.connections.mysql.prefix') . strtolower($tableName) : self::getTable();
        $sql = "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME REGEXP '{$prefix}_[0-9]+';";
        $tables = Db::query($sql);
        
        // 是否有子数据表
        if(count($tables)){
            $arr = array_column($tables,'TABLE_NAME');
            $lastTableName = end($arr);
            // 数据库最大id
            $maxId = (int) Db::table($lastTableName)->max('id');
            if($maxId === 0) {
                // 数据库为空
                $nameArr = explode("_", $lastTableName);
                // 当前空表序号
                $num =  (int) end($nameArr);
                // 空表前最大ID
                $maxId = $single_table_num * $num;
            }
        } else {
            // 仅有一张article表
            $maxId = (int) Db::table($prefix)->max('id');
        }

        // 主表无后缀
        $suffix = '';
        // 表后缀数字（层级）
        $num = (int) floor($maxId / $single_table_num);
            
       
        if($num > 0) {
            // 数据表后缀
            $suffix = "_{$num}";
        }

        return $suffix;
    }
}