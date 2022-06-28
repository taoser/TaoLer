<?php

namespace phpspirit\databackup;

use phpspirit\databackup\mysql\Backup;
use PDO;

class BackupFactory
{
    private static  $instance = null;

    public static function instance($scheme, $server, $dbname, $username, $password, $code = 'utf8')
    {
        $args = md5(implode('_', func_get_args()));
        if (!isset(self::$instance[$args]) || self::$instance[$args] == null) {
            switch ($scheme) {
                case 'mysql':
                   
                    $pdo =  new PDO($scheme . ':host=' . $server . ';dbname=' . $dbname, $username, $password, [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES'" . $code . "';"]);
                    self::$instance[$args] = new Backup($pdo);
            }
        }
        return self::$instance[$args];
    }
}
