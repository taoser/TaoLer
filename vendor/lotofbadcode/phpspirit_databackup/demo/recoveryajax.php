<?php

include dirname(__FILE__) . '/../vendor/autoload.php';

include dirname(__FILE__) . '/../src/RecoveryFactory.php';
include dirname(__FILE__) . '/../src/IRecovery.php';
include dirname(__FILE__) . '/../src/mysql/Recovery.php';
use phpspirit\databackup\RecoveryFactory;
$recovery = RecoveryFactory::instance('mysql', '127.0.0.1:3306', 'test', 'root', 'root');
$result = $recovery->setSqlfiledir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'backup'.DIRECTORY_SEPARATOR.'20191205010418')
        ->ajaxrecovery($_POST);

echo json_encode($result);
