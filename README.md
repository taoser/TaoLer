# TaoLer

> TaoLer是一个简单迅捷的管理系统，支持插件化开发，适用于企业、个人或组织建站需求。

支持多模板、多单页自由切换，网站风格可定制性强，支持插件化开发，方便二开。可做为企业门户网站，生活服务，学习问答笔记，文章分享等。
默认内置一套完整的**问答+博客+论坛+新闻+轻社区化**的模板系统，还可持续增加模块，一个系统拥有多个版式的网站风格。

 * 官网：https://www.aieok.com
 * 文档：http://wiki.aieok.com
 * 演示后台：https://www.aieok.com/adminff

#### 项目地址

1.	gitee	https://www.gitee.com/toogee/Taoler
2.	github	https://www.github.com/taoser/TaoLer
3.  composer create-project taoser/taoler

![alt taoler官网](https://www.aieok.com/storage/1/article_pic/20220802/3cf60f90f7d75b7ddb7efedd96b9e62c.png "TaoLerCMS")

#### 介绍

1.	采用动态密码加密，相同密码在入库时具有唯一性，即使管理员也无法破解，用户信息安全牢固。
2.	系统采用最新版TinkPHP6框架开发，底层安全可靠，数据查询更快，运行效率更高，网站速度更快。
3.	自适应前端，桌面和移动端访问界面友好简洁，模块清晰。
4.	后续主要多合一单页模板开发，可自由切换单页显示，可以设置不同分类模块，不同模板单页，论坛模块，问答模板，企业站模板，产品模块等自由开发。
5.	完善的后台系统，管理便捷，动态菜单和权限角色分配系统。支持3级菜单和无限极分类。
6.	双升级系统，可支持自动和手动升级。可在线检测并升级系统，保持网站的更新和安全。
7.	代码开源，不设暗门操作，更安全。
8.	项目会长期维护，优化更新。
9.	预增加插件管理机制1.x（2.x版本正式上线）

![alt taoler官网](https://www.aieok.com/storage/1/article_pic/20220802/6ea6bb3e40d9a3bc7c9ec28f3e0d7b90.png "TaoLerCMS")

#### 构架组成
- 1.x版本构架：
	- 构架：Tinkphp6 + layui2.7
	- 环境：php7/8.1 + mysql5.7/8.0
	
#### 构架介绍
	thinkphp:
	快速、简单的面向对象的轻量级PHP开发框架，出色的性能和至简代码的，更注重易用性。代码维护方便。
	layui前端:
	极简、丰盈，简单高效，模块化UI框架，体积轻盈，组件丰盈。
	Fly模板:
	一款至简的社区模板。
	
![alt taoler官网](https://www.aieok.com/storage/1/article_pic/20220802/edd9bfc2ca1d1ec52a551b2233b5ab62.png "TaoLerCMS")
	
#### 重点
* 长期维护，对抗消亡
* 迭代及时，查漏补缺
* 多模板多模块，一站顶多站

#### 安装教程

1.	首选确保满目使用环境要求，php > 7.2, mysql > 5.7.3
2.	https://github.com/taoser/TaoLer/archive/refs/heads/master.zip
	git下载：https://gitee.com/toogee/TaoLer 
	官网下载：https://www.aieok.com
	
#### 引导安装

1. 绑定域名 
 
!> 先绑定域名，然后把域名指向解析到`public`目录下

2. 伪静态
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
	
	* nginx 
	> 在`Nginx`低版本中，是不支持`PATHINFO`的，但是可以通过在`Nginx.conf`中配置转发规则实现：遇到`404`错误一般是nginx的伪静态错误
	```bash
	location / {
	   if (!-e $request_filename) {
			rewrite  ^(.*)$  /index.php?s=/$1  last;   break;
		}
	}
	```
	
> 如果是宝塔集成环境，网站目录部署如下示例：
网站目录：D:/www/TaoLer
运行目录：/public

![alt install](https://www.aieok.com/storage/1/article_pic/20220802/f3ae219092a10548268693ec85d978ee.png "install")

	
3.	首次安装，访问域名http://www.youdomain.com可自动跳转到/install/index进行引导安装，重新安装需删除public目录下install.lock。
4.	安装前需要先创建mysql数据库(准备：数据库连接地址，数据库用户名，密码，端口)
5.	如果手动导入数据库，管理员用户名和密码，默认admin/123456，前后台的管理员密码一致。前后端管理员账户是独立的，前端主要对文章内容的审查管理等操作。


![alt taoler官网](https://www.aieok.com/storage/1/article_pic/20220802/54c8364fffd9ca1d15856efd90b689bc.png "TaoLerAdmin")

#### 前后台独立域名的绑定

1. 支持设置绑定域名

> 如果绑定`index`应用对应的域名，后台`admin`应用也必须独立绑定域名，否则原`xxx.com/admin`访问路径就无法再访问。

2. 支持应用设置映射

> 域名绑定的优先级高于应用映射，若绑定过域名，直接访问域名，如果没有绑定过域名，访问路径为xxx.com/youbind.
如后台的映射地址 www.xxx.com/youadmin



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

版权所有Copyright © 2020-2022 by aieok.com (https://www.aieok.com)

All rights reserved。
