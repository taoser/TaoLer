<?php

namespace app\common\model;

use think\Model;
use think\facade\Db;

class BaseModel extends Model
{
    /**
     * 获取所有分表的后缀数组
     * ['_1','_2','_3']
     * @return array
     */
    public static function getSubTablesSuffix(?string $tableName = null): array
    {
        $suffixArr = [];
        $tableArr = self::getSubTables($tableName);
        if(count($tableArr)) {
            $suffixArr = array_map(function ($element) {
                $lastUnderscorePos = strrpos($element, '_');
                if ($lastUnderscorePos!== false) {
                    return substr($element, $lastUnderscorePos);
                }
                return $element;
            }, $tableArr);
        }

        return array_reverse($suffixArr);
    }

    /**
     * 获取子表数组
     * ['tao_article_1','tao_article_2','tao_article_3']
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
     * 增加数据时，判定是否需要新建数据库 自动获取分表后缀 自动判定maxID
     * 不传参数时，自动判定为当前表
     * '_1','_2'
     * @param string|null $id
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

        // 表后缀数字（层级）
        $num = (int) floor($maxId / $single_table_num);
            
       
        if($num > 0) {
            // 数据表后缀
            $suffix = "_{$num}";
        } else {
            // 主表无后缀
            $suffix = '';
        }

        return $suffix;
    }

    /**
     * 查、改、删时需要传入id,获取所在表的后缀
     *
     * @param integer $id
     * @return string
     */
    public static function byIdGetSuffix(int $id): string
    {
        // 数据表后缀为空时，id在主表中
        $suffix = '';
        $num = (int) floor(($id - 1) / config('taoler.single_table_num'));
        // num > 0, id在对应子表中
        if($num > 0) {
            //数据表后缀
            $suffix = "_{$num}";
        }

        return $suffix;
    }

    public static function getSuffixMap(array $where = [], ?string $tableName = null)
    {
        $arr = explode('_',self::getTable());
        // dump(end($arr));
        // $MODEL_NAME = !is_null($tableName) ? ucfirst($tableName)::class : static::class;
        if(is_null($tableName)) {
            $MODEL_NAME =static::class;
        } else {
            $reflectionClass = new \ReflectionClass($tableName);
            $instance = $reflectionClass->newInstanceArgs();
            $MODEL_NAME = $instance;
        }
        // 单个分表统计数 倒叙
        $counts = [];
        // 数据总和
        $totals = 0;

        // 得到所有的分表后缀 倒叙排列
        $suffixArr = self::getSubTablesSuffix($MODEL_NAME);
        // 主表没有后缀，添加到分表数组中
        $suffixArr[] = '';

        // 表综合
        $suffixCount = count($suffixArr);

        if($suffixCount) {
            foreach($suffixArr as $sfx) {
                $total = $MODEL_NAME::suffix($sfx)->where($where)->count();
                $counts[] = $total;
                $totals += $total;
            }
        }

        return [
            'counts'    => $counts,
            'totals'    => $totals,
            'suffixArr' => $suffixArr,
            'suffixCount' => $suffixCount
        ];
        
    }
}