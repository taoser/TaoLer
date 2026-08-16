<?php
namespace app\install\controller;

// SSE 头部设置
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
// ob_implicit_flush(true);
// ob_end_flush();

use think\facade\View;
use think\Request;
use think\facade\Session;
use think\facade\Config;

class Index
{
    protected $dbConfig = [];

	// 安装首页
    public function index()
	{
        return View::fetch('step');
    }

	// 安装
	public function start()
    {
        // 禁用执行时间限制，避免大文件超时
        set_time_limit(0);
        // 2. 设置SSE响应头
        // ob_end_clean();
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // 关闭Nginx缓冲

        $data = Request::param(['admin_email','admin_user','admin_pass','admin_pass2','webname','webtitle','DB_HOST','DB_USER','DB_PASS','DB_PORT','DB_NAME','DB_PREFIX','DB_TYPE' ]);
            
        $installer = new DbInstaller($data);

        if(empty($data['DB_NAME']) || empty($data['DB_USER']) || empty($data['DB_PASS'])){
            $installer->sendMsg('error', "数据库名、用户名、密码不能为空");
            $installer->close();
            return;
        }
            
        if (!preg_match("/^[a-zA-Z]{1}([0-9a-zA-Z]|[._]){4,19}$/", $data['admin_user'])) {
            $installer->sendMsg('error', "管理用户名：至少包含5个字符，需以字母开头");
            $installer->close();
            return;
        }
        if (!preg_match("/^[\@A-Za-z0-9\!\#\$\%\^\&\*\.\~]{6,22}$/", $data['admin_pass'])) {
            $installer->sendMsg('error', "登录密码至少包含6个字符。可使用字母，数字和符号");
            $installer->close();
            return;
        }
        if ($data['admin_pass'] !== $data['admin_pass2']) {
            $installer->sendMsg('error', "两次输入的密码不一致");
            $installer->close();
            return;
        }
            
        // 创建数据库
        if ($data['DB_TYPE'] == 'mysql') {
            // 执行sql文件安装
            $installer->run();
			
		$dbStr = <<<EOV
<?php
return [
	// 默认使用的数据库连接配置
    'default'         => env('DB_DRIVER', 'mysql'),
    // 自定义时间查询规则
    'time_query_rule' => [],
    // 自动写入时间戳字段
    // true为自动识别类型 false关闭
    // 字符串则明确指定时间字段类型 支持 int timestamp datetime date
    'auto_timestamp'  => true,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 时间字段配置 配置格式：create_time,update_time
    'datetime_field'  => '',
    // 数据库连接配置信息
    'connections'     => [
        'mysql' => [
            // 数据库类型
            'type'              => env('DB_TYPE', 'mysql'),
            // 服务器地址
            'hostname'          => env('DB_HOST', '{$data['DB_HOST']}'),
            // 数据库名
            'database'          => env('DB_NAME', '{$data['DB_NAME']}'),
            // 用户名
            'username'          => env('DB_USER', '{$data['DB_USER']}'),
            // 密码
            'password'          => env('DB_PASS', '{$data['DB_PASS']}'),
            // 端口
            'hostport'          => env('DB_PORT', '{$data['DB_PORT']}'),
            // 数据库连接参数
            'params'            => [],
            // 数据库编码默认采用utf8
            'charset'           => env('DB_CHARSET', 'utf8mb4'),
            // 数据库表前缀
            'prefix'            => env('DB_PREFIX', '{$data['DB_PREFIX']}'),
            // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'deploy'            => 0,
            // 数据库读写是否分离 主从式有效
            'rw_separate'       => false,
            // 读写分离后 主服务器数量
            'master_num'        => 1,
            // 指定从服务器序号
            'slave_no'          => '',
            // 是否严格检查字段是否存在
            'fields_strict'     => true,
            // 是否需要断线重连
            'break_reconnect'   => false,
            // 监听SQL
            'trigger_sql'       => env('APP_DEBUG', false),
            // 开启字段缓存
            'fields_cache'      => true,
            // 字段缓存路径
            'schema_cache_path' => app()->getRuntimePath() . 'schema' . DIRECTORY_SEPARATOR,
        ],
        // 更多的数据库配置信息
    ],
];
EOV;
            // 创建数据库链接配置文件
            $database = config_path() . 'database.php';
            if (file_exists($database) && is_writable($database)) {
                $fp = fopen($database,"w");
                $resf = fwrite($fp, $dbStr);
                fclose($fp);
                if(!$resf) {
                    $installer->sendMsg('error', "数据库配置文件创建失败！");
                    return;
                }
            } else {
                $installer->sendMsg('error', "config/database.php 无写入权限");
                return;
            }

            $installer->sendMsg('success', "config/database.php 数据写入成功");
        }

        $env = <<<ENV
APP_DEBUG = false

DB_TYPE = mysql
DB_HOST = {$data['DB_HOST']}
DB_NAME = {$data['DB_NAME']}
DB_USER = {$data['DB_USER']}
DB_PASS = {$data['DB_PASS']}
DB_PORT = {$data['DB_PORT']}
DB_CHARSET = utf8mb4

DEFAULT_LANG = zh-cn
ENV;
        file_put_contents(root_path() . '.env', $env);

        $installer->sendMsg('success', "config/.env 数据写入成功");

        // 创建随机后台模块路径
        $taolerFile = config_path() . 'taoler.php';
        if (file_exists($taolerFile) && is_writable($taolerFile)) {
            try {
                $moduleName = Config::get('taoler.admin_module_name');
                $taolerStr = file_get_contents($taolerFile);

                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                // 随机生成8位字符串
                $modStr = '/' . substr(str_shuffle($chars), 0, 8);

                $taolerStr = str_replace($moduleName, $modStr, $taolerStr);

                Config::set(['admin_module_name' => $modStr], 'taoler');

                $resTaoler = file_put_contents($taolerFile, $taolerStr);

                if(!$resTaoler) {
                    $installer->sendMsg('error', "数据库配置文件创建失败！");
                    return;
                }

            } catch (\Exception $e) {
                $installer->sendMsg('error', $e->getMessage());
                return;
            }
            
        } else {
            $installer->sendMsg('error', "config/taoler.php 无写入权限");
            return;
        }

        //安装上锁
        file_put_contents('./install.lock', date("Y-m-d H:i:s"));

        Session::clear();

        $installer->sendMsg('ok', "安装成功", ['url' => $modStr.'/index']);
        
        return;        
	}

}