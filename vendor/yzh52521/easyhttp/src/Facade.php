<?php

namespace yzh52521\EasyHttp;

class Facade
{
    protected $facade;

    public function __construct()
    {
        $this->facade = new $this->facade;
    }

    public function __call($name, $params)
    {
        if (method_exists($this->facade, 'removeOptions')) {
            call_user_func_array([$this->facade, 'removeOptions'], []);
        }
        return call_user_func_array([$this->facade, $name], $params);
    }

    public static function __callStatic($name, $params)
    {
        return call_user_func_array([new static(), $name], $params);
    }
}
