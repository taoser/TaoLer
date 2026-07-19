<?php


namespace app\common\helper;

class Toolkit
{
   /**
     * @param array $arr
     * @param $key
     * @return array
     */
    public static function setArray2Index(array $arr, $key)
    {
        $newArr = [];
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $newArr[$v[$key]][] = $v;
            }
        }

        return $newArr;
    }

   /**
     * 笛卡尔积
     * @param $arr
     * @return array|mixed|null
     */
    static public function diker($arr)
    {
        $result = array_shift($arr);
        while ($curArr = array_shift($arr)) {
            $lastArr = $result;
            $result = array();
            foreach ($lastArr as $lastVal) {
                if (!is_array($lastVal)) {
                    $lastVal = array($lastVal);
                }
                foreach ($curArr as $curVal) {
                    if (!is_array($curVal)) {
                        $curVal = array($curVal);
                    }
                    $result[] =  array_merge_recursive($lastVal, $curVal);
                }
            }
        }
        return $result;
    }
}
