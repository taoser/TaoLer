<?php
namespace app\common\lib;

class UploadFile
{
    protected static $_instance = null;
    protected $file;
    protected $path;
    protected $filesize;
    protected $fileExt;

    private function __construct($path)
    {
        $this->path = $path;
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    static function getInstance()
    {
        if(!(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

}