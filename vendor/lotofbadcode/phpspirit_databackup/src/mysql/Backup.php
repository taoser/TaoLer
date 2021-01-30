<?php

namespace phpspirit\databackup\mysql;

use PDO;
use Exception;
use phpspirit\databackup\IBackup;

class Backup implements IBackup
{

    /**
     * 分卷大小的 默认2M
     * @var int
     */
    private $_volsize = 2;

    /**
     * 备份路径
     * @var string
     */
    private $_backdir = '';

    /**
     * 表集合
     * @var array 
     */
    private $_tablelist = [];

    /**
     * 当前备份表的索引 
     */
    private $_nowtableidx = 0;

    /**
     * 当前表已备份条数
     * @var int 
     */
    private $_nowtableexeccount = 0;

    /**
     * 当前表的总记录数
     * @var int 
     */
    private $_nowtabletotal = 0;

    /**
     * 当前表备份百分比
     */
    private $_nowtablepercentage = 0;

    /**
     * PDO对象
     * @var PDO 
     */
    private $_pdo;

    /**
     * 保存的文件名
     * @var string 
     */
    private $_filename = '';

    /**
     * insert Values 总条数
     * @var type 
     */
    private $_totallimit = 200;


    /**
     * 是否仅备份结构不备份数据
     */
    private $_onlystructure = false;
    /**
     * 
     * @param string $server 服务器
     * @param string $dbname 数据库
     * @param string $username 账户
     * @param string $password 密码
     * @param string $code 编码
     */

    /**
     * 仅备份数据结构 不备份数据的表
     */
    private $_structuretable = [];

    public function __construct($pdo)
    {
        $this->_pdo = $pdo;
    }

    public function setvolsize($size)
    {
        $this->_volsize = $size;
        return $this;
    }

    public function settablelist($tablelist = [])
    {
        $this->_tablelist = $tablelist;
        return $this;
    }

    public function gettablelist()
    {
        if (!$this->_tablelist) {
            $rs = $this->_pdo->query('show table status');
            $res = $rs->fetchAll(PDO::FETCH_ASSOC);
            foreach ($res as $r) {
                $this->_tablelist[] = $r['Name'];
            }
        }
        return $this->_tablelist;
    }

    /**
     * 设置备份目录
     */
    public function setbackdir($dir)
    {
        if ($this->_backdir) {
            return $this;
        }
        $this->_backdir = $dir;
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
        }
        return $this;
    }

    public function getbackdir()
    {
        return $this->_backdir;
    }

    /**
     * 设置文件名
     * @param string $filename
     * @return $this
     */
    public function setfilename($filename)
    {

        $this->_filename = $filename;
        if (!is_file($this->_backdir . '/' . $this->_filename)) {
            fopen($this->_backdir . '/' . $this->_filename, "x+");
        }
        // return $this;
    }

    /**
     * 获取文件名
     * @return string 
     */
    public function getfilename()
    {
        if (!$this->_filename) {
            $this->_filename = isset($this->_tablelist[$this->_nowtableidx]) ? $this->_tablelist[$this->_nowtableidx] . '#0.sql' : '';
        }
        if (!is_file($this->_backdir . '/' . $this->_filename)) {
            fopen($this->_backdir . '/' . $this->_filename, "x+");
        }
        return $this->_filename;
    }



    /**
     * 设置是否仅备份结构
     */
    public function setonlystructure($bool)
    {
        $this->_onlystructure = $bool;
        return $this;
    }

    public function getonlystructure()
    {
        return $this->_onlystructure;
    }


    /**
     * 设置仅备份表结构 不备份数据的表
     */
    public function setstructuretable($table = [])
    {
        $this->_structuretable = $table;
        return $this;
    }

    public function getstructuretable()
    {
        return $this->_structuretable;
    }
    /**
     * 备份
     * 
     * 正在备份的表
     * 正在备份的表的索引
     * 正在备份的表已备份的记录数
     * 正在备份的表总记录数
     * 当前备份表百分比
     * 总百分比
     */
    public function backup()
    {
        $totalpercentage = 100; //默认总百分比 0%
        $tablelist = $this->gettablelist(); //所有的表列表

        $nowtable = '';

        //上一次备份的表完成100% 将备份下一个表
        if (
            $this->_nowtablepercentage >= 100 &&
            isset($tablelist[$this->_nowtableidx + 1])
        ) {
            $this->_nowtableidx = $this->_nowtableidx + 1;
            $this->_nowtableexeccount = $this->_nowtabletotal = 0;
            $this->setfilename($tablelist[$this->_nowtableidx] . '#0.sql');
        }

        //备份表开始 默认第一个
        if (isset($tablelist[$this->_nowtableidx])) {

            $nowtable = $tablelist[$this->_nowtableidx]; //当前正在备份的表

            $sqlstr = '';
            if ($this->_nowtableexeccount == 0) { //将要执行表已备份的sql记录数
                //Drop 建表
                $sqlstr .= 'DROP TABLE IF EXISTS `' . $nowtable . '`;' . PHP_EOL;
                $rs = $this->_pdo->query('SHOW CREATE TABLE `' . $nowtable . '`');
                $res = $rs->fetchAll();
                $sqlstr .= $res[0][1] . ';' . PHP_EOL;
                file_put_contents($this->_backdir . DIRECTORY_SEPARATOR . $this->getfilename(), file_get_contents($this->_backdir . DIRECTORY_SEPARATOR . $this->getfilename()) . $sqlstr);

                if ($this->getonlystructure() === false  && !in_array($nowtable, $this->getstructuretable())) {
                    $this->gettabletotal($nowtable); //当前备份表总条数
                }
            }

            if ($this->_nowtableexeccount < $this->_nowtabletotal) {
               
                //建记录SQL语句 并设置已经备份的条数
                $this->_singleinsertrecord($nowtable, $this->_nowtableexeccount);
            }


            //计算单表百分比
            if ($this->_nowtabletotal != 0) {
                $this->_nowtablepercentage = $this->_nowtableexeccount / $this->_nowtabletotal * 100;
            } else {
                $this->_nowtablepercentage = 100;
            }

            if ($this->_nowtablepercentage == 100) {
                $totalpercentage = ($this->_nowtableidx + 1) / count($tablelist) * 100;
            } else {
                $totalpercentage = ($this->_nowtableidx) / count($tablelist) * 100;
            }
        }

        return [
            'nowtable' => $nowtable, //当前正在备份的表
            'nowtableidx' => $this->_nowtableidx, //当前正在备份表的索引
            'nowtableexeccount' => $this->_nowtableexeccount, //当前表已备份条数
            'nowtabletotal' => $this->_nowtabletotal, //当前表总条数
            'totalpercentage' => (int) $totalpercentage, //总百分比
            'tablepercentage' => (int) $this->_nowtablepercentage, //当前表百分比
            'backfilename' => $this->getfilename(),
        ];
    }

    public function ajaxbackup($preresult = [])
    {

        if ($this->getbackdir() == '' && !isset($preresult['backdir'])) {
            throw new Exception('请先设置备份目录');
        }

        if (isset($preresult['backdir'])) {
            $this->setbackdir($preresult['backdir']);
        }
        unset($preresult['backdir']);
        if ($preresult) {
            $this->_nowtableidx = $preresult['nowtableidx'];
            $this->_nowtableexeccount = $preresult['nowtableexeccount'];
            $this->_nowtabletotal = $preresult['nowtabletotal'];
            $this->_nowtablepercentage = (int) $preresult['tablepercentage'];
            $this->setfilename($preresult['backfilename']);
        }

        $result = $this->backup();
        $result['backdir'] = $this->getbackdir();
        return $result;
    }

    //获取表中总条数
    public function gettabletotal($table)
    {
        $value = $this->_pdo->query('select count(*) from ' . $table);
        $counts = $value->fetchAll(PDO::FETCH_NUM);
        return $this->_nowtabletotal = $counts[0][0];
    }

    private function _singleinsertrecord($tablename, $limit)
    {
        $sql = 'select * from `' . $tablename . '` limit ' . $limit . ',' . $this->_totallimit;
        $valuers = $this->_pdo->query($sql);
        $valueres = $valuers->fetchAll(PDO::FETCH_NUM);
        $insertsqlv = '';
        $insertsql = 'insert into `' . $tablename . '` VALUES ';
        foreach ($valueres as $v) {
            $insertsqlv .= ' ( ';

            foreach ($v as $_v) {
                $insertsqlv .=  $this->_pdo->quote($_v)  . ",";
            }
            $insertsqlv = rtrim($insertsqlv, ',');
            $insertsqlv .= ' ),';
        }
        $insertsql .= rtrim($insertsqlv, ',') . ' ;' . PHP_EOL;
        $this->_checkfilesize();
        file_put_contents($this->_backdir . '/' . $this->getfilename(), file_get_contents($this->_backdir . '/' . $this->getfilename()) . $insertsql);
        $this->_nowtableexeccount += $this->_totallimit;
        $this->_nowtableexeccount = $this->_nowtableexeccount >= $this->_nowtabletotal ? $this->_nowtabletotal : $this->_nowtableexeccount;
    }

    /**
     * 检查文件大小
     */
    private function _checkfilesize()
    {
        clearstatcache();
        $b = filesize($this->_backdir . '/' . $this->getfilename()) < $this->_volsize * 1024 * 1024 ? true : false;
        if ($b === false) {
            $filearr = explode('#', $this->getfilename());
            if (count($filearr) == 2) {
                $fileext = explode('.', $filearr[1]); //.sql
                $filename = $filearr[0] . '#' . ($fileext[0] + 1) . '.sql';
                $this->setfilename($filename);
            }
        }
    }
}
