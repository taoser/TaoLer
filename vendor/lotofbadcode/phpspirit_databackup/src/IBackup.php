<?php

namespace  phpspirit\databackup;

interface IBackup
{ 
    /**
     * 设置分卷大小
     *
     * @param float|int $size
     * @return void
     */
    public function setvolsize($size);

    /**
     * 设置备份路径
     *
     * @param string $dir
     * @return void
     */
    public function setbackdir($dir);

    /**
     * 备份
     *
     * @return void
     */
    public function backup();

    /**
     * ajax备份
     *
     * @return void
     */
    public function ajaxbackup();
}
