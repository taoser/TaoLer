<?php

namespace  phpspirit\databackup;

interface IRecovery
{
    /**
     * 待恢复SQL文件目录
     *
     * @param [type] $dir
     * @return void
     */
    public function setSqlfiledir($dir);

    /**
     * 
     * 恢复
     * @return void
     */
    public function recovery();

    /**
     * AJAX恢复
     *
     * @return void
     */
    public function ajaxrecovery();
}
