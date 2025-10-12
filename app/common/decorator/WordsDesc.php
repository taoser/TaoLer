<?php

namespace app\common\decorator;

class WordsDesc extends ArticleProcessorDecorator {
    public function process($data) {
        $data = parent::process($data);
        
        // 把中文，转换为英文,并去空格->转为数组->去掉空数组->再转化为带,号的字符串
        $data['keywords'] = implode(',',array_filter(explode(',',trim(str_replace('，',',',$data['keywords'])))));
        $data['description'] = strip_tags($this->filterEmoji($data['description']));

        return $data;
    }

    /**
     * 过滤字符串中表情
     * @param $str string 字符串内容
     * @return string
     */
    private function filterEmoji(string $str): string
    {
        $str = preg_replace_callback('/./u', function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $str);
        return $str;
    }
}