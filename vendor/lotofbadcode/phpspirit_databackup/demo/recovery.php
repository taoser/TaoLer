<?php

set_time_limit(0);
error_reporting(-1);
ini_set('display_errors', 1);
include dirname(__FILE__) . '/../src/RecoveryFactory.php';
include dirname(__FILE__) . '/../src/IRecovery.php';
include dirname(__FILE__) . '/../src/mysql/Recovery.php';

use phpspirit\databackup\RecoveryFactory;
$recovery = RecoveryFactory::instance('mysql', '127.0.0.1:3306', 'test', 'root', 'root');
$recovery->setSqlfiledir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'backup'.DIRECTORY_SEPARATOR.'20191205010418');


do
{
    $result = $recovery->recovery();
    echo str_repeat(' ', 1000); //这里会把浏览器缓存装满
    ob_flush(); //把php缓存写入apahce缓存
    flush(); //把apahce缓存写入浏览器缓存
    if ($result['totalpercentage'] > 0)
    {
        echo '完成' . $result['totalpercentage'] . '%<br />';
    }
} while ($result['totalpercentage'] < 100);
