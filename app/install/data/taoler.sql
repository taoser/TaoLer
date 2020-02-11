
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `tao_admin`;
CREATE TABLE `tao_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '管理员账户',
  `nickname` varchar(20) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `email` varchar(30) NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `sex` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0女1男',
  `status` enum('1','0') NOT NULL DEFAULT '0' COMMENT '1启用0禁用',
  `auth_group_id` smallint(1) NOT NULL DEFAULT '0' COMMENT '1超级管理员0是普通管理员',
  `remarks` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `last_login_ip` varchar(70) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '软删除',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

INSERT INTO `tao_admin` VALUES ('1', 'admin', '管理员', '95d6f8d0d0c3b45e5dbe4057da1b149e', 'taoler@qq.com', '13812345678', '1', '1', '1', '2019.1.1 新年发布新版本！', '127.0.0.1', '1578986287', '1579053025', '1578986600', '0');
INSERT INTO `tao_admin` VALUES ('2', 'test', '', '3dbfa76bd34a2a0274f5d52f5529ccb3', 'tao@qq.com', '13567891236', '0', '0', '2', '', '127.0.0.1', '1578643147', '1555892325', '1576554415', '0');


DROP TABLE IF EXISTS `tao_article`;
CREATE TABLE `tao_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文章ID',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '状态1显示0隐藏',
  `cate_id` int(11) NOT NULL COMMENT '分类id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `is_top` enum('0','1') NOT NULL DEFAULT '0' COMMENT '置顶1否0',
  `is_hot` enum('0','1') NOT NULL DEFAULT '0' COMMENT '推荐1否0',
  `is_reply` enum('1','0') NOT NULL DEFAULT '1' COMMENT '0禁评1可评',
  `pv` int(11) NOT NULL DEFAULT '0' COMMENT '浏览量',
  `jie` enum('1','0') NOT NULL DEFAULT '0' COMMENT '0未结1已结',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;


INSERT INTO `tao_article` VALUES ('1', 'Fly Template 社区模版', '[quote]\r\n  你们认为layui官方Fly Template 社区模版怎么样？\r\n[/quote]\r\nimg[https://cdn.layui.com/upload/2017_11/168_1512035128058_80242.jpg] \r\n你喜欢吗？\r\n很多人都说比较喜欢，我个人认为不错的，这个板子非常喜欢，我看到有一些人做了开发，可惜的是都没有很好的维护，有的漏洞比较多，不完善，很美好的一个板子，但没有长久 的更新，非常的可惜。\r\n如果用别人的不好用，那我就做一个出来吧。喜欢的人多关注，适当时候放出来大家一起用。\r\n关于详情页的内容解析\r\n该模板自带一个特定语法的编辑器，当你把内容存储到数据库后，在页面读取后浏览，会发现诸如“表情、代码、图片”等无法解析，这是因为需要对该内容进行一次转义，通常来说这是在服务端完成的，但鉴于简单化，你还可以直接在前端去解析，在模板的detail.html中，我们已经把相关的代码写好了，你只需打开注释即可（在代码的最下面）。当然，如果觉得编辑器无法满足你的需求，你也可以把该编辑器换成别的HTML编辑器或MarkDown编辑器。', '1', '1', '1', '0', '0', '1', '13', '0', '1546698225', '1577772362', '0');
INSERT INTO `tao_article` VALUES ('2', 'PHP是什么', '[quote]\r\n  PHP原始为Personal Home Page的缩写，已经正式更名为 \"PHP: Hypertext Preprocessor\"。\r\n[/quote]\r\n\r\n自20世纪90年代国内互联网开始发展到现在，互联网信息几乎覆盖了我们日常活动所有知识范畴，并逐渐成为我们生活、学习、工作中必不可少的一部分。据统计，从2003 年开始，我国的网页规模基本保持了翻番的增长速度，并且呈上升趋势。PHP 语言作为当今最热门的网站程序开发语言，它具有成本低、速度快、可移植性好、 内置丰富的函数库等优点，因此被越来越多的企业应用于网站开发中。但随着互联网的不断更新换代，PHP语言也出现了不少问题。 [1] \r\n根据动态网站要求，PHP语言作为一种语言程序，其专用性逐渐在应用过程中显现，其技术水平的优劣与否将直接影响网站的运行效率。其特点是具有公开的源代码， 在程序设计上与通用型语言，如C语言相似性较高，因此在操作过程中简单易懂，可操作性强。同时，PHP语言具有较高的数据传送处理水平和输出水平，可以广泛应用在Windows系统及各类Web服务器中。如果数据量较大，PHP语言还可以拓宽链接面，与各种数据库相连，缓解数据存储、检索及维护压力。随着技术的发展，PHP 语言搜索引擎还可以量体裁衣，实行个性化服务，如根据客户的喜好进行分类收集储存，极大提高了数据运行效率。', '1', '3', '1', '0', '0', '1', '33', '0', '1546748103', '1578542674', '0');

DROP TABLE IF EXISTS `tao_auth_group`;
CREATE TABLE `tao_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '角色名称',
  `rules` char(255) NOT NULL DEFAULT '',
  `limits` varchar(255) NOT NULL DEFAULT '' COMMENT '权限范围',
  `descr` varchar(255) NOT NULL DEFAULT '' COMMENT '权限描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '角色状态1可用0禁止',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

INSERT INTO `tao_auth_group` VALUES ('1', '超级管理员', '5,15,21,22,62,63,23,17,27,28,64,16,24,26,25,4,20,32,33,34,14,29,30,31,1,65,6,35,36,37,38,7,39,40,41,42,8,43,44,45,66,9,47,48,49,50,46,67,2,10,51,11,18,52,54,55,19,56,57,58,59,60,53,3,12,13', '管理所有的管理员', '所有权限', '1', '0', '1578984825', '0');
INSERT INTO `tao_auth_group` VALUES ('2', '管理员', '5,15,21,22,62,63,23,17,27,28,64,16,24,26,25,1,65,6,35,36,37,38,67,3,12,13', '所有列表的管理', '普通管理员', '1', '0', '1578984832', '0');
INSERT INTO `tao_auth_group` VALUES ('3', '帖子管理', '5,15,21,22,62,63,23,17,27,28,64,16,24,26,25', '负责帖子的审核', '文章专员', '1', '0', '1578980219', '0');
INSERT INTO `tao_auth_group` VALUES ('4', '网站维护', '2,10,51,11,18,52,54,55,19,56,57,58,59,60,53,3,12,13', '对数据进行统计', '网站维护', '1', '0', '1578980364', '0');


DROP TABLE IF EXISTS `tao_auth_group_access`;
CREATE TABLE `tao_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `tao_auth_group_access` VALUES ('1', '1');
INSERT INTO `tao_auth_group_access` VALUES ('2', '2');
INSERT INTO `tao_auth_group_access` VALUES ('3', '3');
INSERT INTO `tao_auth_group_access` VALUES ('4', '3');


DROP TABLE IF EXISTS `tao_auth_rule`;
CREATE TABLE `tao_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL DEFAULT '',
  `title` char(20) NOT NULL DEFAULT '',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `status` enum('1','0') NOT NULL DEFAULT '1' COMMENT '菜单1启用,0禁用',
  `pid` smallint(5) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `level` tinyint(1) NOT NULL DEFAULT '1' COMMENT '菜单层级',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `ishidden` enum('1','0') NOT NULL DEFAULT '1' COMMENT '0隐藏,1显示',
  `sort` tinyint(4) NOT NULL DEFAULT '50' COMMENT '排序',
  `condition` char(100) NOT NULL DEFAULT '',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;

INSERT INTO `tao_auth_rule` VALUES ('1', 'admin', '管理', '1', '1', '0', '0', 'layui-icon-user', '1', '3', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('2', 'set', '设置', '1', '1', '0', '0', 'layui-icon-set', '1', '4', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('3', 'administrator', '账户', '1', '1', '0', '0', 'layui-icon-username', '1', '5', '', '0', '1578980034', '0');
INSERT INTO `tao_auth_rule` VALUES ('5', 'article', '内容', '1', '1', '0', '0', 'layui-icon-read', '1', '0', '', '0', '1578902321', '0');
INSERT INTO `tao_auth_rule` VALUES ('6', 'admin/User/list', '用户管理', '1', '1', '1', '1', '', '1', '1', '', '0', '1578901015', '0');
INSERT INTO `tao_auth_rule` VALUES ('7', 'admin/Admin/index', '管理员', '1', '1', '1', '1', '', '1', '6', '', '0', '1578901133', '0');
INSERT INTO `tao_auth_rule` VALUES ('8', 'admin/AuthGroup/list', '角色管理', '1', '1', '1', '1', '', '1', '11', '', '0', '1578901282', '0');
INSERT INTO `tao_auth_rule` VALUES ('9', 'admin/AuthRule/index', '权限管理', '1', '1', '1', '1', '', '1', '16', '', '0', '1578981541', '0');
INSERT INTO `tao_auth_rule` VALUES ('10', 'admin/set/website', '网站设置', '1', '1', '2', '1', '', '1', '1', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('11', 'admin/set/email', '邮件服务', '1', '1', '2', '1', '', '1', '3', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('12', 'admin/Admin/info', '基本资料', '1', '1', '3', '1', '', '1', '50', '', '0', '1578980034', '0');
INSERT INTO `tao_auth_rule` VALUES ('13', 'admin/Admin/repass', '修改密码', '1', '1', '3', '1', '', '1', '51', '', '0', '1578980034', '0');
INSERT INTO `tao_auth_rule` VALUES ('15', 'admin/Forum/list', '帖子列表', '1', '1', '5', '1', '', '1', '1', '', '0', '1578902605', '0');
INSERT INTO `tao_auth_rule` VALUES ('16', 'admin/Forum/tags', '分类管理', '1', '1', '5', '1', '', '1', '11', '', '0', '1578904950', '0');
INSERT INTO `tao_auth_rule` VALUES ('17', 'admin/Forum/replys', '评论管理', '1', '1', '5', '1', '', '1', '7', '', '0', '1578904590', '0');
INSERT INTO `tao_auth_rule` VALUES ('18', 'admin/slider/index', '广告投放', '1', '1', '2', '1', '', '1', '4', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('19', 'admin/Upgrade/index', '系统升级', '1', '1', '2', '1', '', '1', '8', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('21', 'admin/Forum/listform', '编辑帖子', '1', '1', '5', '1', '', '0', '2', '', '0', '1578903229', '0');
INSERT INTO `tao_auth_rule` VALUES ('22', 'admin/Forum/listdel', '删除帖子', '1', '1', '5', '1', '', '0', '3', '', '0', '1578903919', '0');
INSERT INTO `tao_auth_rule` VALUES ('23', 'admin/Forum/check', '审核帖子', '1', '1', '5', '1', '', '0', '6', '', '0', '1578904476', '0');
INSERT INTO `tao_auth_rule` VALUES ('24', 'admin/Forum/addtags', '添加分类', '1', '1', '5', '1', '', '0', '12', '', '0', '1578904966', '0');
INSERT INTO `tao_auth_rule` VALUES ('25', 'admin/Forum/tagsform', '编辑分类', '1', '1', '5', '1', '', '0', '14', '', '0', '1578905046', '0');
INSERT INTO `tao_auth_rule` VALUES ('26', 'admin/Forum/tagsdelete', '删除分类', '1', '1', '5', '1', '', '0', '13', '', '0', '1578904996', '0');
INSERT INTO `tao_auth_rule` VALUES ('27', 'admin/Forum/replysform', '编辑评论', '1', '1', '5', '1', '', '0', '8', '', '0', '1578904627', '0');
INSERT INTO `tao_auth_rule` VALUES ('28', 'admin/Forum/redel', '删除评论', '1', '1', '5', '1', '', '0', '9', '', '0', '1578904856', '0');
INSERT INTO `tao_auth_rule` VALUES ('35', 'admin/User/userform', '添加用户', '1', '1', '1', '1', '', '0', '2', '', '0', '1578901074', '0');
INSERT INTO `tao_auth_rule` VALUES ('36', 'admin/User/useredit', '编辑用户', '1', '1', '1', '1', '', '0', '3', '', '0', '1578901089', '0');
INSERT INTO `tao_auth_rule` VALUES ('37', 'admin/User/delete', '删除用户', '1', '1', '1', '1', '', '0', '4', '', '0', '1578901099', '0');
INSERT INTO `tao_auth_rule` VALUES ('38', 'admin/User/check', '审核用户', '1', '1', '1', '1', '', '0', '5', '', '0', '1578905291', '0');
INSERT INTO `tao_auth_rule` VALUES ('39', 'admin/Admin/add', '添加管理员', '1', '1', '1', '1', '', '0', '7', '', '0', '1578901163', '0');
INSERT INTO `tao_auth_rule` VALUES ('40', 'admin/Admin/edit', '编辑管理员', '1', '1', '1', '1', '', '0', '8', '', '0', '1578901184', '0');
INSERT INTO `tao_auth_rule` VALUES ('41', 'admin/Admin/delete', '删除管理员', '1', '1', '1', '1', '', '0', '9', '', '0', '1578901198', '0');
INSERT INTO `tao_auth_rule` VALUES ('42', 'admin/Admin/check', '审核管理员', '1', '1', '1', '1', '', '0', '10', '', '0', '1578901216', '0');
INSERT INTO `tao_auth_rule` VALUES ('43', 'admin/AuthGroup/roleadd', '添加角色', '1', '1', '1', '1', '', '0', '12', '', '0', '1578981437', '0');
INSERT INTO `tao_auth_rule` VALUES ('44', 'admin/AuthGroup/roleedit', '编辑角色', '1', '1', '1', '1', '', '0', '13', '', '0', '1578901349', '0');
INSERT INTO `tao_auth_rule` VALUES ('45', 'admin/AuthGroup/roledel', '删除角色', '1', '1', '1', '1', '', '0', '14', '', '0', '1578971659', '0');
INSERT INTO `tao_auth_rule` VALUES ('46', 'admin/AuthRule/add', '添加权限', '1', '1', '1', '1', '', '0', '21', '', '0', '1578981581', '0');
INSERT INTO `tao_auth_rule` VALUES ('47', 'admin/AuthRule/edit', '编辑权限', '1', '1', '1', '1', '', '0', '17', '', '0', '1578901457', '0');
INSERT INTO `tao_auth_rule` VALUES ('48', 'admin/AuthRule/delete', '删除权限', '1', '1', '1', '1', '', '0', '18', '', '0', '1578901469', '0');
INSERT INTO `tao_auth_rule` VALUES ('49', 'admin/AuthRule/check', '审核权限', '1', '1', '1', '1', '', '0', '19', '', '0', '1578901484', '0');
INSERT INTO `tao_auth_rule` VALUES ('50', 'admin/AuthRule/menushow', '菜单权限', '1', '1', '1', '1', '', '0', '20', '', '0', '1578901495', '0');
INSERT INTO `tao_auth_rule` VALUES ('51', 'admin/set/upload', '上传logo', '1', '1', '2', '1', '', '0', '2', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('52', 'admin/slider/add', '添加广告', '1', '1', '2', '1', '', '0', '5', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('53', 'admin/slider/edit', '编辑广告', '1', '1', '2', '1', '', '0', '14', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('54', 'admin/slider/delete', '删除广告', '1', '1', '2', '1', '', '0', '6', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('55', 'admin/Slider/uploadimg', '上传广告图片', '1', '1', '2', '1', '', '0', '7', '', '0', '1578906577', '0');
INSERT INTO `tao_auth_rule` VALUES ('56', 'admin/upgrade/key', '设置key', '1', '1', '2', '1', '', '0', '9', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('57', 'admin/upgrade/keyedit', '修改key', '1', '1', '2', '1', '', '0', '10', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('58', 'admin/upgrade/check', '升级检测', '1', '1', '2', '1', '', '0', '11', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('59', 'admin/upgrade/upload', '自动升级', '1', '1', '2', '1', '', '0', '12', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('60', 'admin/upgrade/uploadzip', '上传升级包', '1', '1', '2', '1', '', '0', '13', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('62', 'admin/Forum/top', '置顶帖子', '1', '1', '5', '1', '', '0', '4', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('63', 'admin/Forum/hot', '加精帖子', '1', '1', '5', '1', '', '0', '5', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('64', 'admin/Froum/recheck', '审核评论', '1', '1', '5', '1', '', '0', '10', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('65', 'admin/User/uploadImg', '上传用户头像', '1', '1', '1', '1', '', '0', '0', '', '0', '1578981624', '0');
INSERT INTO `tao_auth_rule` VALUES ('66', 'admin/AuthGroup/check', '审核角色', '1', '1', '1', '1', '', '0', '15', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('67', 'admin/User/auth', '设置超级用户', '1', '1', '1', '1', '', '0', '22', '', '1578984801', '0', '0');

DROP TABLE IF EXISTS `tao_cate`;
CREATE TABLE `tao_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `catename` varchar(20) NOT NULL COMMENT '导航名称',
  `ename` varchar(20) NOT NULL DEFAULT '' COMMENT '分类别名',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '状态1启用0禁用',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0帖子1文章',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updata_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

INSERT INTO `tao_cate` VALUES ('1', '提问', 'ask', '1', '1', '0', '0', '0', '0');
INSERT INTO `tao_cate` VALUES ('2', '分享', 'share', '2', '1', '0', '0', '0', '0');
INSERT INTO `tao_cate` VALUES ('3', '讨论', 'talk', '3', '1', '0', '0', '0', '0');
INSERT INTO `tao_cate` VALUES ('4', '新闻', 'news', '5', '1', '0', '0', '0', '0');
INSERT INTO `tao_cate` VALUES ('5', '咸鱼', 'fish', '7', '1', '0', '0', '0', '0');
INSERT INTO `tao_cate` VALUES ('6', '求助', 'help', '6', '1', '0', '0', '0', '0');
INSERT INTO `tao_cate` VALUES ('7', '工作', 'work', '5', '1', '0', '0', '0', '1571231165');


DROP TABLE IF EXISTS `tao_collection`;
CREATE TABLE `tao_collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `article_id` int(11) NOT NULL COMMENT '文章id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=149 DEFAULT CHARSET=utf8 COMMENT='文章收藏表';

INSERT INTO `tao_collection` VALUES ('141', '11', '1', '1567586177', '0');


DROP TABLE IF EXISTS `tao_comment`;
CREATE TABLE `tao_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '评论id',
  `content` text NOT NULL COMMENT '评论',
  `article_id` int(11) NOT NULL COMMENT '文章id',
  `user_id` int(11) NOT NULL COMMENT '评论用户',
  `zan` tinyint(4) NOT NULL DEFAULT '0' COMMENT '赞',
  `cai` enum('1','0') NOT NULL DEFAULT '0' COMMENT '0求解1采纳',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '1通过0禁止',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=79 DEFAULT CHARSET=utf8;

INSERT INTO `tao_comment` VALUES ('1', 'https://www.aieok.com', '1', '1', '0', '0', '1', '1555127897', '1578977505', '1578977505');


DROP TABLE IF EXISTS `tao_mail_server`;
CREATE TABLE `tao_mail_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail` varchar(50) NOT NULL COMMENT '邮箱设置',
  `host` varchar(50) NOT NULL COMMENT '邮箱服务地址',
  `port` tinyint(2) NOT NULL COMMENT '邮箱端口',
  `nickname` varchar(20) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(16) NOT NULL COMMENT '邮箱密码',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `tao_mail_server` VALUES ('1', 'xxxx@aliyun.com', 'smtp.aliyun.com', '25', 'user', '123456', '0');


DROP TABLE IF EXISTS `tao_slider`;
CREATE TABLE `tao_slider` (
  `id` int(2) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `slid_name` varchar(30) NOT NULL COMMENT '幻灯名',
  `slid_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型',
  `slid_img` varchar(70) NOT NULL DEFAULT '' COMMENT '幻灯图片地址',
  `slid_href` varchar(70) NOT NULL DEFAULT '' COMMENT '链接',
  `slid_color` varchar(10) NOT NULL DEFAULT '' COMMENT '广告块颜色',
  `slid_start` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `slid_over` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `slid_status` enum('1','0') NOT NULL DEFAULT '1' COMMENT '1投放0仓库',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

INSERT INTO `tao_slider` VALUES ('1', 'CODING', '1', '/storage/slider/F1.jpg', '#', '', '1574870400', '1575043200', '1', '0', '0', '0');
INSERT INTO `tao_slider` VALUES ('2', '无人机', '1', '/storage/slider/F2.jpg', 'www.taobao.com', '', '-28800', '1606665600', '1', '0', '0', '0');
INSERT INTO `tao_slider` VALUES ('3', '通用右栏底部广告', '2', '/storage/slider/20200101/851c0b88a72590293bcb45454bdce056.jpg', 'http://www.aieok.com', '', '1571155200', '1609344000', '1', '0', '0', '0');
INSERT INTO `tao_slider` VALUES ('4', 'Layui前端经典框架', '3', '/storage/slider/20191210/d35a49fda5839d4b27f65076fb57b7f2.jpg', 'https://www.layui.com', '#1E9FFF', '1575907200', '1609344000', '1', '0', '0', '0');
INSERT INTO `tao_slider` VALUES ('5', 'Taoler专业社区系统就是强大', '4', '/storage/slider/20191210/e87f15527f32e690538671753010fd00.jpg', 'http://www.aieok.com', '', '-28800', '1609344000', '1', '0', '0', '0');
INSERT INTO `tao_slider` VALUES ('6', '更快更好的PHP快速开发框架', '5', '', 'http://www.thinkphp.cn', '', '1577894400', '1640880000', '1', '0', '0', '0');
INSERT INTO `tao_slider` VALUES ('7', 'Layui前端框架', '6', '', 'https://www.layui.com', '', '1577894400', '1612022400', '1', '0', '0', '0');
INSERT INTO `tao_slider` VALUES ('8', 'ThinkPHP框架', '6', '', 'http://www.thinkphp.cn/', '', '1577894400', '1612022400', '1', '0', '0', '0');
INSERT INTO `tao_slider` VALUES ('9', 'Taoler社区系统', '6', '', 'http://www.aieok.com', '', '1577894400', '2147483647', '1', '0', '0', '0');
INSERT INTO `tao_slider` VALUES ('10', 'Flay模板-layui社区', '3', '', 'https://fly.layui.com/', '#009688', '1577894400', '1612022400', '1', '0', '0', '0');


DROP TABLE IF EXISTS `tao_system`;
CREATE TABLE `tao_system` (
  `id` tinyint(2) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `webname` varchar(20) NOT NULL COMMENT '网站名称',
  `webtitle` varchar(30) NOT NULL,
  `domain` varchar(50) NOT NULL,
  `logo` varchar(70) NOT NULL DEFAULT '' COMMENT '网站logo',
  `cache` tinyint(5) NOT NULL DEFAULT '0' COMMENT '缓存时间分钟',
  `upsize` int(5) NOT NULL DEFAULT '0' COMMENT '上传文件大小KB',
  `uptype` varchar(50) NOT NULL DEFAULT '' COMMENT '上传文件类型',
  `copyright` varchar(80) NOT NULL DEFAULT '' COMMENT '版权',
  `keywords` tinytext NOT NULL COMMENT '网站关键字',
  `descript` tinytext NOT NULL COMMENT '网站描述',
  `is_open` enum('0','1') NOT NULL DEFAULT '1' COMMENT '是否开启站点1开启0关闭',
  `is_comment` enum('0','1') NOT NULL DEFAULT '1' COMMENT '是否开启评论1开启0关闭',
  `is_reg` enum('0','1') NOT NULL DEFAULT '1' COMMENT '是否开放注册1开启0禁止',
  `icp` varchar(50) NOT NULL DEFAULT '' COMMENT '备案',
  `blackname` varchar(255) NOT NULL COMMENT '注册黑名单',
  `sys_version_num` varchar(5) NOT NULL COMMENT '系统版本',
  `key` varchar(60) DEFAULT NULL COMMENT 'key',
  `base_url` varchar(50) NOT NULL DEFAULT '',
  `upcheck_url` varchar(255) NOT NULL COMMENT '版本检测',
  `upgrade_url` varchar(255) NOT NULL COMMENT '升级地址',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='系统配置表';

INSERT INTO `tao_system` VALUES ('1', 'TaoLer社区演示站', '轻论坛系统', 'http://www.xxx.com', '/storage/logo/logo.png', '10', '2048', 'png|gif|jpg|jpeg|zip|rarr', '<a href=\"http://www.aieok.com\" target=\"_blank\">aieok.com 版权所有</a>', 'TaoLer,轻社区系统,bbs,论坛,Thinkphp6,layui,fly模板,', '这是一个Taoler轻社区论坛系统', '1', '1', '1', '0.0.0.0', '管理员|admin|审核员|超级|垃圾', '1.0.0', '', 'http://www.aieok.com/api/index/cy', 'http://www.aieok.com/api/upload/check', 'http://www.aieok.com/api/upload/api', '0', '1577419197');


DROP TABLE IF EXISTS `tao_user`;
CREATE TABLE `tao_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `name` varchar(16) NOT NULL COMMENT '用户名',
  `password` varchar(32) NOT NULL COMMENT '密码',
  `phone` varchar(11) NOT NULL DEFAULT '' COMMENT '手机',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '邮箱',
  `nickname` varchar(16) NOT NULL DEFAULT '' COMMENT '昵称',
  `city` varchar(50) NOT NULL DEFAULT '' COMMENT '归属地',
  `sex` enum('0','1') NOT NULL DEFAULT '0' COMMENT '性别0男1女',
  `sign` varchar(255) NOT NULL DEFAULT '' COMMENT '签名',
  `user_img` varbinary(70) NOT NULL DEFAULT '' COMMENT '头像',
  `auth` enum('1','0') NOT NULL DEFAULT '0' COMMENT '管理员权限0普通1超级',
  `point` tinyint(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `area_id` int(11) DEFAULT NULL COMMENT '用户所属区域ID',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '1启用0禁用',
  `vip` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'vip',
  `last_login_ip` varchar(70) NOT NULL DEFAULT '0' COMMENT '最后登陆ip',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登陆时间',
  `login_error_num` tinyint(1) NOT NULL DEFAULT '0' COMMENT '登陆错误次数',
  `login_error_time` int(11) NOT NULL DEFAULT '0' COMMENT '登陆错误时间',
  `login_error_lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '登陆锁定0正常1锁定',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

INSERT INTO `tao_user` VALUES ('1', 'admin', '95d6f8d0d0c3b45e5dbe4057da1b149e', '2147483647', 'admin@qq.com', '管理员', '北京市', '1', '这是我的第一个TP5系统，2019北京。OK! OK!ok@', 0x2F73746F726167652F686561645F7069632F32303139313231372F39343036636334623866336538323731613238616339646239353339333766352E6A7067, '1', '14', '1', '1', '0', '127.0.0.1', '0', '0', '0', '0', '1579053025', '1578469091', '0');


DROP TABLE IF EXISTS `tao_user_area`;
CREATE TABLE `tao_user_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `area` varchar(10) NOT NULL COMMENT '所属区域',
  `asing` varchar(2) NOT NULL COMMENT '区域简称',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `delete_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

INSERT INTO `tao_user_area` VALUES ('1', '北京', '京', '0', '0', '0');
INSERT INTO `tao_user_area` VALUES ('2', '上海', '沪', '0', '0', '0');
INSERT INTO `tao_user_area` VALUES ('3', '广州', '广', '0', '0', '0');
INSERT INTO `tao_user_area` VALUES ('4', '深圳', '深', '0', '0', '0');


DROP TABLE IF EXISTS `tao_user_sign`;
CREATE TABLE `tao_user_sign` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL COMMENT '用户id',
  `days` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '连续签到的天数',
  `is_share` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否分享过',
  `is_sign` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否签到过',
  `stime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '签到的时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='用户签到表';

INSERT INTO `tao_user_sign` VALUES ('16', '2', '1', '0', '0', '1558750514', '0');



DROP TABLE IF EXISTS `tao_user_signrule`;
CREATE TABLE `tao_user_signrule` (
  `id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `days` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '连续天数',
  `score` int(3) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='用户签到积分规则';

INSERT INTO `tao_user_signrule` VALUES ('1', '1', '2');
INSERT INTO `tao_user_signrule` VALUES ('2', '3', '3');
INSERT INTO `tao_user_signrule` VALUES ('3', '5', '5');


DROP TABLE IF EXISTS `tao_user_zan`;
CREATE TABLE `tao_user_zan` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '点赞主键id',
  `comment_id` int(11) NOT NULL COMMENT '评论id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '点赞时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;

