<?php

namespace phpspirit\databackup\mysql;

use PDO;
use Exception;
use phpspirit\databackup\IRecovery;


class Recovery implements IRecovery
{


    /**
     * SQL文件所在的目录
     * @var string 
     */
    private $_sqlfiledir = '';

    /**
     * SQL文件数组
     * @var array 
     */
    private $_sqlfilesarr = [];

    /**
     * 当前恢复文件数组的索引
     * @var int 
     */
    private $_nowfileidx = 0;

    /**
     * 下一个恢复的文件
     * @var int 
     */
    private $_nextfileidx = 0;


    public function __construct($pdo)
    {
        $this->_pdo = $pdo;
    }

    public function setSqlfiledir($dir)
    {
        $this->_sqlfiledir = $dir;
        return $this;
    }

    public function getfiles()
    {
        if (!$this->_sqlfilesarr) {
            $dir = $this->_sqlfiledir;
            $iterator = new \DirectoryIterator($dir);
            $filesarr = [];
            foreach ($iterator as $it) {
                if (!$it->isDot()) {
                    $filenameinfo = explode('#', $it->getFilename());
                    $fileext = explode('.', $filenameinfo[1]);
                    $filesarr[$filenameinfo[0]][$fileext[0]] = $it->getFilename();
                }
            }
            ksort($filesarr);
            foreach ($filesarr as $k => $f) {
                ksort($f);
                $filesarr[$k] = $f;
            }
            foreach ($filesarr as $f) {
                foreach ($f as $_f) {
                    $this->_sqlfilesarr[] = $_f;
                }
            }
        }
        return $this->_sqlfilesarr;
    }

    public function recovery()
    {
        try {
            $filesarr = $this->getfiles();
            $totalpercentage = 100;
            $this->_nowfileidx = $this->_nextfileidx;
            if (isset($filesarr[$this->_nowfileidx])) {
                $this->_importsqlfile($this->_sqlfiledir . DIRECTORY_SEPARATOR . $filesarr[$this->_nowfileidx]);
                $totalpercentage = $this->_nowfileidx / count($this->_sqlfilesarr) * 100;
                $this->_nextfileidx = $this->_nowfileidx + 1;
            }
            return [
                'nowfileidex' => $this->_nowfileidx, //当前正在恢复的文件
                'nextfileidx' => $this->_nextfileidx,
                'totalpercentage' => (int) $totalpercentage, //总百分比
            ];
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function ajaxrecovery($preresult = [])
    {
        if($preresult)
        {
            $this->_nowfileidx = $preresult['nowfileidex'];
            $this->_nextfileidx = $preresult['nextfileidx'];
        }
        $result = $this->recovery();
        return $result;
    }

    private function _importsqlfile($sqlfile)
    {
        if (is_file($sqlfile)) {
            try {
                $content = file_get_contents($sqlfile);
                $arr = explode(';' . PHP_EOL, $content);
                foreach ($arr as $a) {
                    if (trim($a) != '') {
                        $this->_pdo->exec($a);
                    }
                }
            } catch (Exception $ex) {
                return false;
            }
        }
        return true;
    }
}
