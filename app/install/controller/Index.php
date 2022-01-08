<?php
namespace app\install\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;

class Index extends BaseController
{
	// 检测是否安装过
	protected function initialize(){
        if(file_exists('./install.lock')){
           echo "<script>alert('已经成功安装了TaoLer社区系统，安装系统已锁定。如需重新安装，请删除根目录下的install.lock文件')</script>";
		   die();
        }
    }

	//安装首页
    public function index()
	{
		Session::set('install',1);
        return View::fetch('agreement');
    }
	
	//test
    public function test()
	{
		if(Session::get('install') == 1){
			Session::set('install',2);
			return View::fetch('test');
		} else {
			return redirect('index.html');
		} 
    }
	
	//create
    public function create(){
		if(Session::get('install') == 2){
			Session::set('install',3);
			return View::fetch('create');
		} else {
			return redirect('test.html');
		} 
    }
	
	// 安装
	public function install(){
		
		//if(Session::get('install') != 3){
		//	return redirect('./create.html');
		//}

	if(Request::isAjax()){	
		$data = Request::param();
		//var_dump($data);
        if (!preg_match("/^[a-zA-Z]{1}([0-9a-zA-Z]|[._]){4,19}$/", $data['admin_user'])) {
			return json(['code'=>-1,'msg'=>"管理用户名：至少包含5个字符，需以字母开头"]);
        }
       
		if (!preg_match("/^[\@A-Za-z0-9\!\#\$\%\^\&\*\.\~]{6,22}$/", $data['admin_pass'])) {
			return json(['code'=>-1,'msg'=>'登录密码至少包含6个字符。可使用字母，数字和符号']);
		}
		if ($data['admin_pass'] != $data['admin_pass2']) {
			return json(['code'=>-1,'msg'=>'两次输入的密码不一致']);
			//die("<script>alert('两次输入的密码不一致');history.go(-1)</script>");
		}

		$email = $data['admin_email'];
		$user = $data['admin_user'];
		$create_time = time();
		$salt = substr(md5($create_time),-6);
		$pass = md5(substr_replace(md5($data['admin_pass']),$salt,0,6));
		$webname = $data['webname'];
		$webtitle = $data['webtitle'];
		$web = Request::host();
		//数据库配置
		$dbhost = $data['DB_HOST'];
		$dbuser = $data['DB_USER'];
		$dbpass = $data['DB_PWD'];
		$dbport = $data['DB_PORT'];
		$dbname = $data['DB_NAME'];
		$prefix	= $data['DB_PREFIX'];
       
		if ($data['DB_TYPE'] == 'mysql') {
			
			//创建数据库
			try { 
				$conn = new \PDO("mysql:host=$dbhost", $dbuser, $dbpass); 
			} 
			catch(\PDOException $e) 
			{ 
				return json(['code'=>-1,'msg'=>"数据库信息错误" . $e->getMessage()]); 
			}
			
			$sql = 'CREATE DATABASE IF NOT EXISTS '.$dbname.' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci'; 
			 
			// 使用 exec() ，没有结果返回 
			$conn->exec($sql); 
			//echo $dbname."数据库创建成功<br>"; 
			$conn = null;
			
			//写入数据表
			try { 
				$db = new \PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass); 
			} 
			catch(\PDOException $e) 
			{ 
				return json(['code'=>-1,'msg'=>"数据库连接失败" . $e->getMessage()]); 
			}
			//创建表
			$res = create_tables($db,$prefix);
			if(!$res){
				return json(['code'=>-1,'msg'=>"数据表创建失败"]); 
			}
			
			//写入初始配置	
			$table_admin = $data['DB_PREFIX'] . "admin";
			$table_user = $data['DB_PREFIX'] . "user";
			$table_system = $data['DB_PREFIX'] . "system";
			
			$sql_a = "UPDATE $table_admin SET username='{$user}',email='{$email}',password='{$pass}',status=1,auth_group_id=1,create_time='{$create_time}' WHERE id = 1";
			$sql_u = "UPDATE $table_user SET name='{$user}',email='{$email}',password='{$pass}',auth=1,status=1,create_time='{$create_time}' WHERE id = 1"; 
			$sql_s = "UPDATE $table_system SET webname='{$webname}',webtitle='{$webtitle}',domain='{$web}',create_time='{$create_time}' WHERE id = 1";
			
			$res_a = $db->exec($sql_a);
			//var_dump($db->errorInfo());
			if($res_a == 0){
				return json(['code'=>-1,'msg'=>"管理员账号写入失败"]); 
			}
			$res_u = $db->exec($sql_u);
			if($res_u == 0){
				return json(['code'=>-1,'msg'=>"前台管理员写入失败"]); 
			}
			$res_s = $db->exec($sql_s);
			if($res_s == 0){
				return json(['code'=>-1,'msg'=>"网站配置写入失败"]); 
			}
			$db = null;
			

		$db_str = <<<php
<?php
return [
	// 默认使用的数据库连接配置
    'default'         => env('database.driver', 'mysql'),
    // 自定义时间查询规则
    'time_query_rule' => [],
    // 自动写入时间戳字段
    // true为自动识别类型 false关闭
    // 字符串则明确指定时间字段类型 支持 int timestamp datetime date
    'auto_timestamp'  => true,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 数据库连接配置信息
    'connections'     => [
	'mysql' => [
		// 数据库类型
		'type'              => env('database.type', 'mysql'),
		// 服务器地址
		'hostname'          => env('database.hostname', '{$data['DB_HOST']}'),
		// 数据库名
		'database'          => env('database.database', '{$data['DB_NAME']}'),
		// 用户名
		'username'          => env('database.username', '{$data['DB_USER']}'),
		// 密码
		'password'          => env('database.password', '{$data['DB_PWD']}'),
		// 端口
		'hostport'          => env('database.hostport', '{$data['DB_PORT']}'),
		// 数据库连接参数
		'params'            => [],
		// 数据库编码默认采用utf8
		'charset'           => 'utf8',
		// 数据库表前缀
		'prefix'            => env('database.prefix', '{$data['DB_PREFIX']}'),
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
		'trigger_sql'       => env('app_debug', true),
		// 开启字段缓存
		'fields_cache'      => false,
		// 字段缓存路径
		//'schema_cache_path' => app()->getRuntimePath() . 'schema' . DIRECTORY_SEPARATOR,
		],
		// 更多的数据库配置信息
    ],
];
php;
        // 创建数据库链接配置文件
			$database = '../config/database.php';
			if (file_exists($database)) {
				if(is_writable($database)){
					$fp = fopen($database,"w");
					$resf = fwrite($fp, $db_str);
					fclose($fp);
					if(!$resf){
						$res = json(['code' => -1,'msg'=>'数据库配置文件创建失败！']);
					}
				} else {
					$res = json(['code' => -1,'msg'=>'config/database.php 无写入权限']);
				}
			}

        }

		//安装上锁
		file_put_contents('./install.lock', 'lock');
		Session::clear();

		$res = json(['code' => 0,'msg'=>'安装成功','url'=>(string) url('success/complete')]);
		} else {
			$res = json(['code' => -1,'msg'=>'请求失败']);
		} 
	return $res;
	} 
}