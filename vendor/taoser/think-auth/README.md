# thinkphp-auth

ThinkPHP8权限Auth扩展

## 安装

~~~php
//thinkphp8.0
composer require taoser/think-auth
~~~

## 配置
~~~php
// 安装之后会在config目录里生成auth.php配置文件,无需数据表前缀
return[
        'auth_on'            => true,					// 认证开关
        'auth_type'          => 1,						// 认证方式，1为实时认证；2为登录认证。
        'auth_group'         => 'auth_group',			// 用户组数据表名
        'auth_group_access'  => 'auth_group_access',	// 用户-用户组关系表
        'auth_rule'          => 'auth_rule',			// 权限规则表
        'auth_user'          => 'admin'					// 用户信息表
];
~~~

## 导入数据表

~~~php
/*
-- ----------------------------
-- tp_admin，用户表，
-- id:主键，is_admin：是否是管理员
-- ----------------------------
 DROP TABLE IF EXISTS `tp_admin`;
CREATE TABLE `tp_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '管理员用户名',
  `fullname` varchar(50) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `password_reset_token` varchar(255) NOT NULL DEFAULT '',
  `access_token` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '邮箱',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '管理员密码',
  `login_times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登陆次数',
  `login_ip` varchar(20) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登陆时间',
  `last_login_ip` varchar(255) NOT NULL DEFAULT '' COMMENT '上次登陆ip',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次登陆时间',
  `user_agent` varchar(500) NOT NULL DEFAULT '' COMMENT 'user_agent',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1可用0禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
-- ----------------------------
-- tp_auth_rule，规则表，
-- id:主键，name：规则唯一标识, title：规则中文名称 status 状态：为1正常，为0禁用，condition：规则表达式，为空表示存在就验证，不为空表示按照条件验证
-- ----------------------------
 DROP TABLE IF EXISTS `tp_auth_rule`;
CREATE TABLE `tp_auth_rule` (
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `name` char(80) NOT NULL DEFAULT '',
    `title` char(20) NOT NULL DEFAULT '',
    `type` tinyint(1) NOT NULL DEFAULT '1',
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `condition` char(100) NOT NULL DEFAULT '',  # 规则附件条件,满足附加条件的规则,才认为是有效的规则
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
-- ----------------------------
-- tp_auth_group 用户组表，
-- id：主键， title:用户组中文名称， rules：用户组拥有的规则id， 多个规则","隔开，status 状态：为1正常，为0禁用
-- ----------------------------
 DROP TABLE IF EXISTS `tp_auth_group`;
CREATE TABLE `tp_auth_group` (
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `title` char(100) NOT NULL DEFAULT '',
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `rules` char(80) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
-- ----------------------------
-- tp_auth_group_access 用户组明细表
-- uid:用户id，group_id：用户组id
-- ----------------------------
DROP TABLE IF EXISTS `tp_auth_group_access`;
CREATE TABLE `tp_auth_group_access` (
    `uid` mediumint(8) unsigned NOT NULL,
    `group_id` mediumint(8) unsigned NOT NULL,
    UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
    KEY `uid` (`uid`),
    KEY `group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
 */
~~~

~~~php

/**
 * 权限认证类
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth = new \taoser\think\Auth();  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
 *      $auth = new \taoser\think\Auth();  $auth->check('规则1,规则2','用户id','and')
 *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
 * 3，一个用户可以属于多个用户组(tp_auth_group_access表 定义了用户所属用户组)。我们需要设置每个用户组拥有哪些规则(tp_auth_group 定义了用户组权限)
 *
 * 4，支持规则表达式。
 *      在tp_auth_rule 表中定义一条规则时，如果type为1， condition字段就可以定义规则表达式。 如定义{score}>5  and {score}<100  表示用户的分数在5-100之间时这条规则才会通过。
 */
 
~~~


## 原理
Auth权限认证是按规则进行认证。
在数据库中我们有 

- 规则表（think_auth_rule） 
- 用户组表(think_auth_group) 
- 用户组明显表（think_auth_group_access）

我们在规则表中定义权限规则， 在用户组表中定义每个用户组有哪些权限规则，在用户组明显表中定义用户所属的用户组。 

//命名空间为
namespace taoser\think;

//直接使用
$auth = new \taoser\think\Auth();

// 引入类库
use think\auth\Auth;
$auth = new Auth();

// 检测权限
if($auth->check('show_button',1)){// 第一个参数是规则名称,第二个参数是用户UID
	//有显示操作按钮的权限
}else{
	//没有显示操作按钮的权限
}

##	实例
~~~php
 //以下实例以thinkphp5.1为例说明
 Auth类也可以对节点进行认证，我们只要将规则名称，定义为节点名称就行了。 
可以在公共控制器Base中定义_initialize方法
~~~

~~~php
<?php
//简单实例
namespace app\admin\controller;
use app\BaseController;
use think\auth\Auth;

class Base extends BaseController
{
    public function initialize()
    {
        if (!session('admin_id')) {
           $this->redirect('login/index');
        }
        $auth = new Auth();
        $controller = strtolower(request()->controller());
        $action = strtolower(request()->action());
        $url = $controller . "/" . $action;
        if (!$auth->check($url, session('admin_id'))) {
           $this->error('抱歉，您没有操作权限');
        }
    }
}
~~~

~~~php
<?php
//高级实例  根据用户积分判断权限

//Auth类还可以按用户属性进行判断权限， 比如 按照用户积分进行判断，假设我们的用户表 (tp_admin) 有字段 score 记录了用户积分。我在规则表添加规则时，定义规则表的condition 字段，condition字段是规则条件，默认为空 表示没有附加条件，用户组中只有规则 就通过认证。如果定义了 condition字段，用户组中有规则不一定能通过认证，程序还会判断是否满足附加条件。 比如我们添加几条规则：

//name字段：grade1 condition字段：{score}<100 
//name字段：grade2 condition字段：{score}>100 and {score}<200
//name字段：grade3 condition字段：{score}>200 and {score}<300

//这里 {score} 表示 think_members 表 中字段 score 的值。

//那么这时候

$auth = new \taoser\think\Auth();
$auth->check('grade1', 1); //是判断用户积分是不是0-100
$auth->check('grade2', 1); //判断用户积分是不是在100-200
$auth->check('grade3', 1); //判断用户积分是不是在200-300

~~~


~~~php
<?php
//高级实例  右侧菜单根据权限隐藏实例
namespace app\admin\controller;

use think\Controller;
use think\Db;

class Base extends Controller
{
    public function initialize()
    {
        if (!session('admin_id')) {
            $this->redirect('login/index');
        }
        $auth = new \taoser\think\Auth();
        $controller = strtolower(request()->controller());
        $action = strtolower(request()->action());
        $url = $controller . "/" . $action;
        $data = Db::name('auth_rule')->order('sort','asc')->select();
        $data = list_to_tree($data);
        //排除不需要验证的规则
        $no_check_default = ['index/index'];
        $no_check_status_list = Db::name('auth_rule')->where('status', 0)->column('name');
        $no_check_rules_list = explode(',', strtolower(implode(',', array_merge($no_check_default, (array)$no_check_status_list))));
        $no_check_user_list = Db::name('admin')->where('is_admin', 1)->column('id');
        if (!in_array(session('admin_id'), $no_check_user_list)) {
            if (!in_array($url, $no_check_rules_list)) {
                if (!$auth->check($url, session('admin_id'))) {
                    $this->error('抱歉，您没有操作权限');
                }
            }
            foreach ($data as $k => $v) {
                if (!$auth->check($v['name'], session('admin_id'))) {
                    unset($data[$k]);
                } else {
                    if (isset($v['_child'])) {
                        foreach ($v['_child'] as $key => $value) {
                            if (!$auth->check($value['name'], session('admin_id'))) {
                                unset($data[$k]['_child'][$key]);
                            }
                        }
                    }
                }
            }
        }
        //unset($data[0]['_child'][0]);
        //var_dump($data);
        //mysql不区分字段内容大小写
        $active_id = Db::name('auth_rule')->where('name', '=', $url)->field('id,pid,top_pid')->find();
        $this->assign([
            'active_id' => implode(',', (array)$active_id),
            'menu_nav' => $data,
            'crumb_list' => get_crumb_list($url)
        ]);
    }
}
~~~