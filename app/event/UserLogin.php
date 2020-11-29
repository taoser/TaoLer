<?php
declare (strict_types = 1);

namespace app\event;
//use app\common\model\User;

class UserLogin
{
    public $name;
    public $ip;
    public function __construct($name,$ip)
    {
        $this->name = $name;
        $this->ip = $ip;
    }
}
