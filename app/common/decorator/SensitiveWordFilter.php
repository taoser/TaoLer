<?php

namespace app\common\decorator;

class SensitiveWordFilter extends ArticleProcessorDecorator {
    public function process($data) {
        $data = parent::process($data);
        $sensitiveWords = ['操你妈', '我靠','fuck you','傻逼','我靠'];
        foreach ($sensitiveWords as $word) {
            $data['content'] = str_replace($word, '***', $data['content']);
        }
        return $data;
    }
}