<?php
set_time_limit(0);
error_reporting(-1);
ini_set('display_errors', 1);
include dirname(__FILE__) . '/../src/BackupFactory.php';
include dirname(__FILE__) . '/../src/IBackup.php';
include dirname(__FILE__) . '/../src/mysql/backup.php';

use phpspirit\databackup\BackupFactory;

//自行判断文件夹
$backupdir = '';
if (isset($_POST['backdir']) && $_POST['backdir'] != '') {
    $backupdir = $_POST['backdir'];
} else {
    $backupdir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . date('Ymdhis');
}

if (!is_dir($backupdir)) {
    mkdir($backupdir, 0777, true);
}
$backup = BackupFactory::instance('mysql', '127.0.0.1:3306', 'test', 'root', 'root');
$backup->setbackdir($backupdir)
    ->settablelist(['md_menu', 'md_api_group'])
    ->setstructuretable(['md_api_group'])
    ->setvolsize(0.2);
do {
    $result = $backup->backup();
    echo str_repeat(' ', 1000); //这里会把浏览器缓存装满
    ob_flush(); //把php缓存写入apahce缓存
    flush(); //把apahce缓存写入浏览器缓存
    if ($result['totalpercentage'] > 0) {
        echo '完成' . $result['totalpercentage'] . '%<br />';
    }
} while ($result['totalpercentage'] < 100);
