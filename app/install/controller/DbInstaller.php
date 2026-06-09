<?php
namespace app\install\controller;

// SSE 头部设置
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
// ob_implicit_flush(true);
// ob_end_flush();


use PDO;
use PDOException;
use Exception;
use think\facade\Request;

class DbInstaller
{
    private $pdo;
    private $dbhost;
    private $dbport;
    private $dbuser;
    private $dbpwd;
    private $dbname;
    private $prefix;
    private $dbtype;

    private $admin_email;
    private $admin_user;
    private $admin_pass;
    private $webname;
    private $webtitle;
    private $web;
    private $salt;
    private $pass;
    private $create_time;
    

    public function __construct(array $param)
    {
        $this->dbhost   = $param['DB_HOST'];
        $this->dbuser   = $param['DB_USER'];
        $this->dbpwd    = $param['DB_PWD'];
        $this->dbport   = $param['DB_PORT'];
        $this->dbname   = $param['DB_NAME'];
        $this->prefix	= $param['DB_PREFIX'];
        $this->dbtype	= $param['DB_TYPE'];

        $this->admin_email  = $param['admin_email'];
        $this->admin_user   = $param['admin_user'];
        $this->admin_pass   = $param['admin_pass'];
        $this->webname      = $param['webname'];
        $this->webtitle     = $param['webtitle'];

        $this->create_time = time();
        $this->salt = substr(md5($this->create_time),-6);
        $this->pass = md5(substr_replace(md5($this->admin_pass),$this->salt,0,6));
        
        $this->web = Request::host();
    }

    // 推送消息到前端
    public function sendMsg(string $status, string $msg, $extra = [])
    {
        $data = array_merge(['type' => $status, 'message' => $msg], $extra);
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        echo "data: {$data}\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }

    // 连接服务器
    private function connectServer()
    {
        try {
            $dsn = "mysql:host={$this->dbhost};port={$this->dbport};charset=utf8mb4";
            $this->sendMsg('progress', '数据库连接'.$dsn);
            $this->pdo = new PDO($dsn, $this->dbuser, $this->dbpwd,[
                PDO::ATTR_PERSISTENT => true
            ]);
            return true;
        } catch (Exception $e) {
            $this->sendMsg('error', '数据库连接失败：' . $e->getMessage());
            return false;
        }
    }

    // 创建数据库
    private function createDb()
    {
        try {
            $this->pdo->exec("CREATE DATABASE IF NOT EXISTS {$this->dbname} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            return true;
        } catch (Exception $e) {
            $this->sendMsg('error', '创建数据库失败：' . $e->getMessage());
            return false;
        }
    }

    // 切换库
    private function useDb()
    {
        try {
            $this->pdo->exec("USE {$this->dbname}");
            return true;
        } catch (Exception $e) {
            $this->sendMsg('error', '切换数据库失败：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 安全加载并解析SQL文件（支持注释 + 表前缀替换）
     * @param string $sqlPath SQL文件路径
     * @param string $oldPrefix 原表前缀（如：pre_）
     * @param string $newPrefix 新表前缀（如：shop_）
     * @return array 解析后的SQL语句数组
     */
    private function load_sql_file(string $sqlPath, string $oldPrefix, string $newPrefix): array
    {
        // 1. 读取文件内容
        $content = file_get_contents($sqlPath);
        if ($content === false) {
            throw new \RuntimeException("SQL文件读取失败");
        }

        // 2. 【关键】过滤所有SQL注释（兼容行注释+块注释）
        $content = preg_replace('/--(.+)/', '', $content); // 去掉 -- 行注释
        $content = preg_replace('/\/\*[\s\S]*?\*\//', '', $content); // 去掉 /* */ 块注释

        // 3. 安全替换表前缀（正则精准匹配 表名前的前缀，不误伤注释/内容）
        if (!empty($oldPrefix) && !empty($newPrefix) && $oldPrefix !== $newPrefix) {
            $pattern = '/`?' . preg_quote($oldPrefix, '/') . '(\w+)`?/';
            $content = preg_replace($pattern, $newPrefix . '$1', $content);
        }

        // 4. 拆分SQL语句，过滤空语句
        $sqlList = array_filter(explode(';', $content));
        $sqlList = array_map('trim', $sqlList);

        return array_filter($sqlList); // 过滤空值
    }

    // 导入SQL
    private function importSql(string $file)
    {
        if (!file_exists($file)) {
            $this->sendMsg('error', 'SQL文件不存在');
            return false;
        }

        $sqlArr = $this->load_sql_file($file, 'tao_', $this->prefix); // 加载并解析SQL文件，替换表前缀
        $total = count($sqlArr);
        
        $current = 0;
        
        $this->sendMsg('total', "SQL语句解析完成，总计 {$total} 条，开始执行SQL语句...<br><br>",['total' => $total]);

        // try {
        //     $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //     // 开启事务
        //     $this->pdo->beginTransaction();

            foreach ($sqlArr as $sql) {
                $current++;
                $shortSql = substr($sql, 0, 100); // 只显示前100字符，避免太长

                try {
                    $this->pdo->exec($sql);

                    // 成功：实时输出
                    if (stripos($sql, 'CREATE TABLE') !== false) {
                        preg_match('/CREATE TABLE\s*`?(\w+)`?/i', $sql, $m);
                        $table = $m[1] ?? 'unknown';
                        $this->sendMsg('progress', "✅ [{$current}/{$total}] 创建数据表 {$table} 成功<br>",['total' => $total, 'current' => $current, 'percent' => floor($current/$total*100)]);
                    } elseif (stripos($sql, 'INSERT') !== false) {
                        preg_match('/INTO\s*`?(\w+)`?/i', $sql, $m);
                        $table = $m[1] ?? 'unknown';
                        $this->sendMsg('progress', "✅ [{$current}/{$total}] 插入数据表 {$table} 数据成功<br>",['total' => $total, 'current' => $current, 'percent' => floor($current/$total*100)]);
                    } else {
                        $this->sendMsg('progress', "✅ [{$current}/{$total}] 执行成功：{$shortSql}...<br>",['total' => $total, 'current' => $current, 'percent' => floor($current/$total*100)]);
                    }
                } catch (\Exception $e) {
                    // 失败：实时输出
                    if (stripos($sql, 'CREATE TABLE') !== false) {
                        preg_match('/CREATE TABLE\s*`?(\w+)`?/i', $sql, $m);
                        $table = $m[1] ?? 'unknown';
                        $this->sendMsg('error', "❌ [{$current}/{$total}] 创建数据表 {$table} 失败：{$e->getMessage()}<br>", ['sql_preview' => $shortSql]);
                    } elseif (stripos($sql, 'INSERT') !== false) {
                        preg_match('/INTO\s*`?(\w+)`?/i', $sql, $m);
                        $table = $m[1] ?? 'unknown';
                        $this->sendMsg('error', "❌ [{$current}/{$total}] 插入数据表 {$table} 数据失败：{$e->getMessage()}<br>", ['sql_preview' => $shortSql]);
                    } else {
                        $this->sendMsg('error', "❌ [{$current}/{$total}] 执行失败：{$shortSql}... 错误：{$e->getMessage()}<br>", ['sql_preview' => $shortSql]);
                    }

                    // 回滚事务
                    $this->pdo->rollBack();
                    $this->sendMsg('error', "<br>❌ 事务已回滚，安装终止<br>");
                }
                
                usleep(10000); // 轻微延迟，看得清进度（可删除）
            }

            // 全部成功，提交事务
            // 在 commit 前检查事务状态
            // if ($this->pdo->inTransaction()) {
            //     $this->pdo->commit();
            // } else {
            //     $this->sendMsg('error', "❌ 事务状态异常<br>");
            // }

            $this->sendMsg('success', "<br>🎉 所有SQL执行完成，安装成功！<br>");

        // } catch (Exception $e) {
        //     // 系统异常，回滚事务
        //     $this->pdo->rollBack();

        //     $this->sendMsg('error', "<br>❌ 系统异常：{$e->getMessage()}，已回滚<br>");
        // }
    }

    public function updateSystemInfo()
    {
        //写入初始配置
        $table_admin = $this->prefix . "admin";
        $table_user = $this->prefix . "user";
        $table_system = $this->prefix . "system";

        $sql_a = "UPDATE $table_admin SET username='{$this->admin_user}',email='{$this->admin_email}',password='{$this->pass}',status=1,auth_group_id=1,create_time='{$this->create_time}' WHERE id = 1";
        $sql_u = "UPDATE $table_user SET name='{$this->admin_user}',email='{$this->admin_email}',password='{$this->pass}',auth=1,status=1,create_time='{$this->create_time}' WHERE id = 1";
        $sql_s = "UPDATE $table_system SET webname='{$this->webname}',webtitle='{$this->webtitle}',domain='{$this->web}',create_time='{$this->create_time}' WHERE id = 1";
        
        try{

            $this->pdo->exec($sql_a);
            $this->pdo->exec($sql_u);
            $this->pdo->exec($sql_s);
            
        } catch(Exception $e) {
            $this->sendMsg('error', "❌ 系统数据写入异常：{$e->getMessage()}");
            $this->close();
            return;
        }
        

    }

    // ====================== 关键：主动断开数据库连接 ======================
    public function close()
    {
        $this->pdo = null; // 销毁PDO = 断开MySQL连接
    }

    // 安装总入口
    public function run()
    {
        // 禁用执行时间限制，避免大文件超时
        set_time_limit(0);

        $this->sendMsg('info', '开始连接数据库服务器...');
        if (!$this->connectServer()) {
            $this->close(); // 失败也断开
            return;
        }

        $this->sendMsg('info', '创建数据库中...');
        if (!$this->createDb()) {
            $this->close();
            return;
        }

        $this->sendMsg('info', '切换数据库...');
        if (!$this->useDb()) {
            $this->close();
            return;
        }

        $this->sendMsg('progress', '开始导入数据表...');
        $sqlFile = app_path() . 'install/data/taoler.sql';
        if ($this->importSql($sqlFile)) {
            $this->sendMsg('success', '✅ 数据库安装全部完成！');
        }

        // 更新系统信息（管理员账号、网站信息等）
        $this->sendMsg('progress', '更新系统信息（管理员账号、网站信息等）...');
        $this->updateSystemInfo();

        // ====================== 最终断开连接 ======================
        $this->close();
    }
}
