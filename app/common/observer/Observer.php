<?php

namespace app\common\observer;

use app\entity\Article;

interface Observer
{
    public function update(array $data, Article $article);
}