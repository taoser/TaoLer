<?php

namespace app\common\observer;

interface Observer
{
    public function update($data = null);
}