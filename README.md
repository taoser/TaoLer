
<!-- @import "[TOC]" {cmd="toc" depthFrom=1 depthTo=6 orderedList=false} -->
# TaoLer

> TaoLer，简单迅捷的内容管理系统，支持千万级别数据，插件化开发，适用于企业、个人建站需求，小程序、app开发。

## 📖 简介
- 可轻松搭建web站点，api接口，小程序、app后端数据管理等；一套系统多端应用。
- 系统使用简单，新手容易学习，看的懂，开发效率高。
- 企业站、问答、博客、论坛、新闻、商城，或作为后端管理皆可选择。
- PHP语言新特性，如：异步IO、协程、事件驱动等，使系统运行更高效、更稳定。
  
## ✨ 功能特性
- 采用TinkPHP8为基础框架，可使用传统FPM模式 或 Workerman5.2/Swoole常驻内存驱动模式
- 采用动态密码加密，相同密码在入库时具有唯一性，即使管理员也无法破解，用户信息安全牢固
- 自适应多合一模板，站点视图支持模版风格切换，支持分类单独绑定模版模块，一套模版多风格
- 成熟的热插拔插件化系统，功能无限拓展，让您专注于您的业务逻辑，数据交由系统维护
- 支持多用户角色，可自由分配权限，确保数据安全
- 完善的后台系统，管理便捷，动态菜单和权限角色分配系统。支持3级菜单和无限极分类
- 双升级系统，可支持自动和手动升级。可在线检测并升级系统，保持网站的更新和安全
- 代码开源，更新频繁，持续维护，避免断更焦虑，确保系统稳定运行
- 项目会长期迭代更新维护，优化更新
- 新版插件管理机制3.x
- 支持多语言

## 🖼️ 截图/演示（可选）
放效果图、截图、GIF演示
![alt taoler官网](https://www.aieok.com/storage/1/article_pic/20220802/3cf60f90f7d75b7ddb7efedd96b9e62c.png "TaoLerCMS首页")
![alt taoler官网](https://www.aieok.com/storage/1/article_pic/20220802/6ea6bb3e40d9a3bc7c9ec28f3e0d7b90.png "TaoLerCMS列表")

## 🚀 快速开始
### 环境要求
- 4.x版本构架：
	- 构架：Tinkphp8
	- 环境：php8.5 + mysql9.x
	- 驱动：可选 Workerman5.2 + Swoole常驻内存驱动模式
  
### 📚 使用文档
 * 官网：https://www.aieok.com
 * 文档：http://wiki.aieok.com

### 项目地址

- gitee	https://www.gitee.com/toogee/Taoler
- github https://www.github.com/taoser/TaoLer
  
```bash
composer create-project taoser/taoler
```

## 🛠️ 开发指南
### 目录结构
```bash
www  WEB部署目录（或者子目录）
├─addons			插件目录
|  ├─pay				支付插件
|  |  ├─controller			插件控制器目录
|  |  ├─lang				插件语言目录
|  |  ├─model				插件模型目录
|  |  ├─route				插件路由目录
|  |  ├─view				插件视图目录
|  |  |  ├─index			插件控制器视图目录
|  |  |  |  ├─index.html	插件控制器视图文件
|  |  |  |  └─gopay.html	插件支付视图文件
|  |  |  └─plugin			插件钩子视图目录
|  |  |     ├─pay.html		插件钩子视图文件
|  |  |     └─...			更多钩子视图文件
|  |  ├─data				插件数据目录
|  |  ├─config.php			插件配置文件
|  |  ├─info.ini			插件信息文件
|  |  └─Plugin.php			插件类入口文件
|  |
|  ├─sign				签到插件
|  └─...				更多插件
|
├─app           应用目录
|  ├─admin				admin管理模块
|  ├─api				api接口模块
|  ├─common      		common目录
|  ├─entity     		entity数据目录
|  ├─event      		event目录
|  ├─facade     		facade静态门面目录
|  ├─index      		index前端模块
|  ├─install      		引导安装模块
|  ├─lang      			多语言目录
|  ├─listener      		事件监听器目录
|  ├─middleware     	中间件目录
│  ├─controller			控制器目录
│  ├─model				模型目录
|  ├─service			服务目录
|  ├─observer			观察者目录
|  ├─subscribe			订阅目录
|  ├─validate			验证目录
│  ├─ ...             	更多类库目录
│  │
│  ├─common.php         公共函数文件
│  └─event.php          事件定义文件
│
├─config		配置目录
│  ├─addons.php         插件配置
│  ├─app.php            应用配置
│  ├─cache.php          缓存配置
│  ├─console.php        控制台配置
│  ├─cookie.php         Cookie配置
│  ├─database.php       数据库配置
│  ├─filesystem.php     文件磁盘配置
│  ├─lang.php           多语言配置
│  ├─log.php            日志配置
│  ├─middleware.php     中间件配置
│  ├─route.php          URL和路由配置
│  ├─session.php        Session配置
│  ├─trace.php          Trace配置
│  ├─taoler .php        系统配置
│  ├─throttle.php       限流配置
│  ├─view.php           视图配置
│  └─worker.php         Workererman配置
│
├─view            视图目录
├─route           路由定义目录
│  ├─route.php          路由定义文件
│  └─ ...   
│
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
│
├─extend                扩展类库目录
├─runtime               应用的运行时目录（可写，可定制）
├─vendor                Composer类库目录
├─.example.env          环境变量示例文件
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
```

### 安装教程

1.	首选确保满目使用环境要求，php > 8.5, mysql > 9
2.	https://github.com/taoser/TaoLer/archive/refs/heads/master.zip
	git下载：https://gitee.com/toogee/TaoLer 
	官网下载：https://www.aieok.com
	
### 引导安装

1. 绑定域名 
 
!> 先绑定域名，然后把域名指向解析到`public`目录下

2. 伪静态

	* nginx 
	> 在`Nginx`低版本中，是不支持`PATHINFO`的，但是可以通过在`Nginx.conf`中配置转发规则实现：遇到`404`错误一般是nginx的伪静态错误
	```bash
	location / {
	   if (!-e $request_filename) {
			rewrite  ^(.*)$  /index.php?s=/$1  last;   break;
		}
	}
	```

	* apache:
	> 在apache服务器，一般不用手动设置、`public`文件加已经设置了`.htaccess`文件	
	```bash
	<IfModule mod_rewrite.c>
	  Options +FollowSymlinks -Multiviews
	  RewriteEngine On

	  RewriteCond %{REQUEST_FILENAME} !-d
	  RewriteCond %{REQUEST_FILENAME} !-f
	  RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
	</IfModule>
	```
	!> 但在有的集成包的apache环境下`访问域名无法安装或者No input file specified`，如phpstudy,伪静态要设置为如下：
	```bash
	<IfModule mod_rewrite.c>
	  Options +FollowSymlinks -Multiviews
	  RewriteEngine On

	  RewriteCond %{REQUEST_FILENAME} !-d
	  RewriteCond %{REQUEST_FILENAME} !-f
	  RewriteRule ^(.*)$ index.php [L,E=PATH_INFO:$1]
	</IfModule>
	```
	
> 如果是宝塔集成环境，网站目录部署如下示例：
网站目录：D:/www/TaoLer
运行目录：/public

![alt install](https://www.aieok.com/storage/1/article_pic/20220802/f3ae219092a10548268693ec85d978ee.png "install")

	
3.	首次安装，访问域名http://www.youdomain.com，可自动跳转到/install/index进行引导安装，重新安装需删除public目录下install.lock。
4.	安装前需要先创建mysql数据库(准备：数据库连接地址，数据库用户名，密码，端口)
5.	如果手动导入数据库，管理员用户名和密码，默认admin/123456，前后台的管理员密码一致。前后端管理员账户是独立的，前端主要对文章内容的审查管理等操作。


![alt taoler官网](https://www.aieok.com/storage/1/article_pic/20220802/54c8364fffd9ca1d15856efd90b689bc.png "TaoLerAdmin")


#### 使用说明

1.	安装后本系统已配置默认演示数据，可以删除原数据或者进行数据的修改
2.	后台可设置分类cate,一定要设置英文别名
3.	首页有置顶模块，列表文章模块，右侧包含广告模块，回复展示模块

#### 文档

 参考官网分享文章
 
 aieok.com (http://wiki.aieok.com)

#### 参与贡献

1.  Fork 本仓库
2.  网站提交BUG
3.  提交代码
4.  新建 Pull Request

#### 版权信息

非商业可免费使用，没有功能限制，但不能更改版本信息，如需更改可购买授权。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2020-2026 by aieok.com (https://www.aieok.com)

All rights reserved。
