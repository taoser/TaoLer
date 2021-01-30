/*
Navicat MySQL Data Transfer

Source Server         : lc
Source Server Version : 50730
Source Host           : localhost:3306
Source Database       : taotao

Target Server Type    : MYSQL
Target Server Version : 50730
File Encoding         : 65001

Date: 2021-01-30 17:41:02
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for tao_admin
-- ----------------------------
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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_admin
-- ----------------------------
INSERT INTO `tao_admin` VALUES ('1', 'admin', '管理员', '95d6f8d0d0c3b45e5dbe4057da1b149e', 'taoler@qq.com', '13812345678', '1', '1', '1', '2019.1.1 新年发布新版本！', '127.0.0.1', '1611997302', '1579053025', '1578986600', '0');
INSERT INTO `tao_admin` VALUES ('2', 'test', '', '3dbfa76bd34a2a0274f5d52f5529ccb3', 'test@qq.com', '13567891236', '0', '0', '2', '', '127.0.0.1', '1578643147', '1555892325', '1576554415', '0');

-- ----------------------------
-- Table structure for tao_article
-- ----------------------------
DROP TABLE IF EXISTS `tao_article`;
CREATE TABLE `tao_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
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
  `upzip` varchar(70) DEFAULT NULL COMMENT '文章附件',
  `downloads` int(5) NOT NULL DEFAULT '0' COMMENT '下载量',
  `tags` varchar(255) DEFAULT NULL COMMENT 'tag',
  `read_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '阅读权限0开放1回复可读2密码可读3私密',
  `art_pass` varchar(6) DEFAULT NULL COMMENT '文章加密密码',
  `title_color` varchar(10) DEFAULT NULL COMMENT '标题颜色',
  `title_font` varchar(20) DEFAULT NULL COMMENT '标题字形',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE COMMENT '文章的用户索引',
  KEY `cate_id` (`cate_id`) USING BTREE COMMENT '文章分类索引'
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_article
-- ----------------------------
INSERT INTO `tao_article` VALUES ('1', 'Fly Template 社区模版', '[quote]\r\n  你们认为layui官方Fly Template 社区模版怎么样？\r\n[/quote]\r\n你喜欢吗？\r\n很多人都说比较喜欢，我个人认为不错的，这个板子非常喜欢，我看到有一些人做了开发，可惜的是都没有很好的维护，有的漏洞比较多，不完善，很美好的一个板子，但没有长久 的更新，非常的可惜。\r\n如果用别人的不好用，那我就做一个出来吧。喜欢的人多关注，适当时候放出来大家一起用。\r\n关于详情页的内容解析\r\n该模板自带一个特定语法的编辑器，当你把内容存储到数据库后，在页面读取后浏览，会发现诸如“表情、代码、图片”等无法解析，这是因为需要对该内容进行一次转义，通常来说这是在服务端完成的，但鉴于简单化，你还可以直接在前端去解析，在模板的detail.html中，我们已经把相关的代码写好了，你只需打开注释即可（在代码的最下面）。当然，如果觉得编辑器无法满足你的需求，你也可以把该编辑器换成别的HTML编辑器或MarkDown编辑器。', '1', '1', '1', '0', '0', '1', '12', '0', null, '0', null, '0', null, null, null, '1546698225', '1577772362', '0');

-- ----------------------------
-- Table structure for tao_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `tao_auth_group`;
CREATE TABLE `tao_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '角色名称',
  `rules` varchar(500) NOT NULL DEFAULT '' COMMENT '拥有权限',
  `limits` varchar(255) NOT NULL DEFAULT '' COMMENT '权限范围',
  `descr` varchar(255) NOT NULL DEFAULT '' COMMENT '权限描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '角色状态1可用0禁止',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_auth_group
-- ----------------------------
INSERT INTO `tao_auth_group` VALUES ('1', '超级管理员', '5,15,21,22,62,63,23,17,27,28,64,16,24,26,25,4,20,32,33,34,14,29,30,31,1,65,6,35,36,37,38,7,39,40,41,42,8,43,44,45,66,9,47,48,49,50,46,67,2,10,51,11,18,52,54,55,19,56,57,58,59,60,53,3,12,13', '管理所有的管理员', '所有权限', '1', '0', '1578984825', '0');
INSERT INTO `tao_auth_group` VALUES ('2', '管理员', '5,15,21,22,62,63,23,17,27,28,64,16,24,26,25,1,65,6,35,36,37,38,67,3,12,13', '所有列表的管理', '普通管理员', '1', '0', '1578984832', '0');
INSERT INTO `tao_auth_group` VALUES ('3', '帖子管理', '5,15,21,22,62,63,23,17,27,28,64,16,24,26,25', '负责帖子的审核', '文章专员', '1', '0', '1578980219', '0');
INSERT INTO `tao_auth_group` VALUES ('4', '网站维护', '2,10,51,11,18,52,54,55,19,56,57,58,59,60,53,3,12,13', '对数据进行统计', '网站维护', '1', '0', '1578980364', '0');

-- ----------------------------
-- Table structure for tao_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `tao_auth_group_access`;
CREATE TABLE `tao_auth_group_access` (
  `id` int(2) NOT NULL AUTO_INCREMENT COMMENT '用户组id',
  `uid` int(11) unsigned NOT NULL,
  `group_id` int(8) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户权限组状态0禁止1正常',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`),
  KEY `uid_group_id` (`uid`,`group_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_auth_group_access
-- ----------------------------

-- ----------------------------
-- Table structure for tao_auth_group_copy
-- ----------------------------
DROP TABLE IF EXISTS `tao_auth_group_copy`;
CREATE TABLE `tao_auth_group_copy` (
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
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_auth_group_copy
-- ----------------------------
INSERT INTO `tao_auth_group_copy` VALUES ('1', '超级管理员', '5,15,21,22,62,63,23,17,27,28,64,16,24,26,25,4,20,32,33,34,14,29,30,31,1,65,6,35,36,37,38,7,39,40,41,42,8,43,44,45,66,9,47,48,49,50,46,67,2,10,51,11,18,52,54,55,19,56,57,58,59,60,53,3,12,13', '管理所有的管理员', '所有权限', '1', '0', '1578984825', '0');
INSERT INTO `tao_auth_group_copy` VALUES ('2', '管理员', '5,15,21,22,62,63,23,17,27,28,64,16,24,26,25,1,65,6,35,36,37,38,67,3,12,13', '所有列表的管理', '普通管理员', '1', '0', '1578984832', '0');
INSERT INTO `tao_auth_group_copy` VALUES ('3', '帖子管理', '5,15,21,22,62,63,23,17,27,28,64,16,24,26,25', '负责帖子的审核', '文章专员', '1', '0', '1578980219', '0');
INSERT INTO `tao_auth_group_copy` VALUES ('4', '网站维护', '5,15,21,22,62,63,23,17,27,28,64,16,24,26,25,90,2,10,51,11,18,52,54,55,19,56,57,58,59,60,53,3,12,13', '对数据进行统计', '网站维护', '1', '0', '1588065032', '0');

-- ----------------------------
-- Table structure for tao_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `tao_auth_rule`;
CREATE TABLE `tao_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '权限主键ID',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '权限名称',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '权限标题',
  `etitle` varchar(100) NOT NULL DEFAULT '' COMMENT '英文权限标题',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型',
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
) ENGINE=MyISAM AUTO_INCREMENT=97 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_auth_rule
-- ----------------------------
INSERT INTO `tao_auth_rule` VALUES ('1', 'admin', '管理', '', '1', '1', '0', '0', 'layui-icon-user', '1', '3', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('2', 'set', '设置', '', '1', '1', '0', '0', 'layui-icon-set', '1', '4', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('3', 'administrator', '账户', '', '1', '1', '0', '0', 'layui-icon-username', '1', '5', '', '0', '1578980034', '0');
INSERT INTO `tao_auth_rule` VALUES ('4', 'app', '应用', '', '1', '1', '0', '0', 'layui-icon-app', '1', '2', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('5', 'article', '内容', '', '1', '1', '0', '0', 'layui-icon-read', '1', '0', '', '0', '1578902321', '0');
INSERT INTO `tao_auth_rule` VALUES ('6', 'admin/User/list', '用户管理', '', '1', '1', '1', '1', '', '1', '1', '', '0', '1578901015', '0');
INSERT INTO `tao_auth_rule` VALUES ('7', 'admin/Admin/index', '管理员', '', '1', '1', '1', '1', '', '1', '6', '', '0', '1578901133', '0');
INSERT INTO `tao_auth_rule` VALUES ('8', 'admin/AuthGroup/list', '角色管理', '', '1', '1', '1', '1', '', '1', '11', '', '0', '1578901282', '0');
INSERT INTO `tao_auth_rule` VALUES ('9', 'admin/AuthRule/index', '权限菜单', '', '1', '1', '2', '1', '', '1', '16', '', '0', '1611998671', '0');
INSERT INTO `tao_auth_rule` VALUES ('10', 'admin/Set/index', '网站设置', '', '1', '1', '2', '1', '', '1', '1', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('11', 'admin/Set/server', '综合服务', '', '1', '1', '2', '1', '', '1', '3', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('12', 'admin/Admin/info', '基本资料', '', '1', '1', '3', '1', '', '1', '50', '', '0', '1578980034', '0');
INSERT INTO `tao_auth_rule` VALUES ('13', 'admin/Admin/repass', '修改密码', '', '1', '1', '3', '1', '', '1', '51', '', '0', '1578980034', '0');
INSERT INTO `tao_auth_rule` VALUES ('15', 'admin/Forum/list', '帖子管理', '', '1', '1', '5', '1', '', '1', '1', '', '0', '1578902605', '0');
INSERT INTO `tao_auth_rule` VALUES ('16', 'admin/Forum/tags', '分类管理', '', '1', '1', '5', '1', '', '1', '11', '', '0', '1578904950', '0');
INSERT INTO `tao_auth_rule` VALUES ('17', 'admin/Forum/replys', '评论管理', '', '1', '1', '5', '1', '', '1', '7', '', '0', '1578904590', '0');
INSERT INTO `tao_auth_rule` VALUES ('18', 'admin/Slider/index', '广告投放', '', '1', '1', '93', '1', '', '1', '4', '', '0', '1611999603', '0');
INSERT INTO `tao_auth_rule` VALUES ('19', 'admin/Upgrade/index', '系统升级', '', '1', '1', '2', '1', '', '1', '8', '', '0', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('21', 'admin/Forum/listform', '编辑帖子', '', '1', '1', '15', '2', '', '0', '2', '', '0', '1611997428', '0');
INSERT INTO `tao_auth_rule` VALUES ('22', 'admin/Forum/listdel', '删除帖子', '', '1', '1', '15', '2', '', '0', '3', '', '0', '1611997448', '0');
INSERT INTO `tao_auth_rule` VALUES ('23', 'admin/Forum/check', '审核帖子', '', '1', '1', '15', '2', '', '0', '6', '', '0', '1611997474', '0');
INSERT INTO `tao_auth_rule` VALUES ('24', 'admin/Forum/addtags', '添加分类', '', '1', '1', '16', '2', '', '0', '12', '', '0', '1611997513', '0');
INSERT INTO `tao_auth_rule` VALUES ('25', 'admin/Forum/tagsform', '编辑分类', '', '1', '1', '16', '2', '', '0', '14', '', '0', '1611997535', '0');
INSERT INTO `tao_auth_rule` VALUES ('26', 'admin/Forum/tagsdelete', '删除分类', '', '1', '1', '16', '2', '', '0', '13', '', '0', '1611997523', '0');
INSERT INTO `tao_auth_rule` VALUES ('27', 'admin/Forum/replysform', '编辑评论', '', '1', '1', '17', '2', '', '0', '8', '', '0', '1611997484', '0');
INSERT INTO `tao_auth_rule` VALUES ('28', 'admin/Forum/redel', '删除评论', '', '1', '1', '17', '2', '', '0', '9', '', '0', '1611997494', '0');
INSERT INTO `tao_auth_rule` VALUES ('35', 'admin/User/userForm', '添加用户', '', '1', '1', '6', '2', '', '0', '2', '', '0', '1611997673', '0');
INSERT INTO `tao_auth_rule` VALUES ('36', 'admin/User/userEdit', '编辑用户', '', '1', '1', '6', '2', '', '0', '3', '', '0', '1611997690', '0');
INSERT INTO `tao_auth_rule` VALUES ('37', 'admin/User/delete', '删除用户', '', '1', '1', '6', '2', '', '0', '4', '', '0', '1611997701', '0');
INSERT INTO `tao_auth_rule` VALUES ('38', 'admin/User/check', '审核用户', '', '1', '1', '6', '2', '', '0', '5', '', '0', '1611997713', '0');
INSERT INTO `tao_auth_rule` VALUES ('39', 'admin/Admin/add', '添加管理员', '', '1', '1', '7', '2', '', '0', '7', '', '0', '1611997732', '0');
INSERT INTO `tao_auth_rule` VALUES ('40', 'admin/Admin/edit', '编辑管理员', '', '1', '1', '7', '2', '', '0', '8', '', '0', '1611997747', '0');
INSERT INTO `tao_auth_rule` VALUES ('41', 'admin/Admin/delete', '删除管理员', '', '1', '1', '7', '2', '', '0', '9', '', '0', '1611997760', '0');
INSERT INTO `tao_auth_rule` VALUES ('42', 'admin/Admin/check', '审核管理员', '', '1', '1', '7', '2', '', '0', '10', '', '0', '1611997772', '0');
INSERT INTO `tao_auth_rule` VALUES ('43', 'admin/AuthGroup/roleAdd', '添加角色', '', '1', '1', '8', '2', '', '0', '12', '', '0', '1611997790', '0');
INSERT INTO `tao_auth_rule` VALUES ('44', 'admin/AuthGroup/roleEdit', '编辑角色', '', '1', '1', '8', '2', '', '0', '13', '', '0', '1611997805', '0');
INSERT INTO `tao_auth_rule` VALUES ('45', 'admin/AuthGroup/roledel', '删除角色', '', '1', '1', '8', '2', '', '0', '14', '', '0', '1611997820', '0');
INSERT INTO `tao_auth_rule` VALUES ('46', 'admin/AuthRule/add', '添加权限', '', '1', '1', '9', '2', '', '0', '21', '', '0', '1611997901', '0');
INSERT INTO `tao_auth_rule` VALUES ('47', 'admin/AuthRule/edit', '编辑权限', '', '1', '1', '9', '2', '', '0', '17', '', '0', '1611997849', '0');
INSERT INTO `tao_auth_rule` VALUES ('48', 'admin/AuthRule/delete', '删除权限', '', '1', '1', '9', '2', '', '0', '18', '', '0', '1611997869', '0');
INSERT INTO `tao_auth_rule` VALUES ('49', 'admin/AuthRule/check', '审核权限', '', '1', '1', '9', '2', '', '0', '19', '', '0', '1611997884', '0');
INSERT INTO `tao_auth_rule` VALUES ('50', 'admin/AuthRule/menushow', '菜单权限', '', '1', '1', '9', '2', '', '0', '20', '', '0', '1611997929', '0');
INSERT INTO `tao_auth_rule` VALUES ('51', 'admin/Set/upload', '上传logo', '', '1', '1', '10', '2', '', '0', '2', '', '0', '1611998097', '0');
INSERT INTO `tao_auth_rule` VALUES ('52', 'admin/Slider/add', '添加广告', '', '1', '1', '18', '2', '', '0', '5', '', '0', '1611998128', '0');
INSERT INTO `tao_auth_rule` VALUES ('53', 'admin/Slider/edit', '编辑广告', '', '1', '1', '18', '2', '', '0', '14', '', '0', '1611998263', '0');
INSERT INTO `tao_auth_rule` VALUES ('54', 'admin/Slider/delete', '删除广告', '', '1', '1', '18', '2', '', '0', '6', '', '0', '1611998141', '0');
INSERT INTO `tao_auth_rule` VALUES ('55', 'admin/Slider/uploadimg', '上传广告图片', '', '1', '1', '18', '2', '', '0', '7', '', '0', '1611998156', '0');
INSERT INTO `tao_auth_rule` VALUES ('56', 'admin/Upgrade/key', '设置key', '', '1', '1', '19', '2', '', '0', '9', '', '0', '1611998178', '0');
INSERT INTO `tao_auth_rule` VALUES ('57', 'admin/Upgrade/keyedit', '修改key', '', '1', '1', '19', '2', '', '0', '10', '', '0', '1611998192', '0');
INSERT INTO `tao_auth_rule` VALUES ('58', 'admin/Upgrade/check', '升级检测', '', '1', '1', '19', '2', '', '0', '11', '', '0', '1611998214', '0');
INSERT INTO `tao_auth_rule` VALUES ('59', 'admin/Upgrade/upload', '自动升级', '', '1', '1', '19', '2', '', '0', '12', '', '0', '1611998230', '0');
INSERT INTO `tao_auth_rule` VALUES ('60', 'admin/Upgrade/uploadzip', '上传升级包', '', '1', '1', '19', '2', '', '0', '13', '', '0', '1611998245', '0');
INSERT INTO `tao_auth_rule` VALUES ('62', 'admin/Forum/top', '置顶帖子', '', '1', '1', '15', '2', '', '0', '4', '', '0', '1611997455', '0');
INSERT INTO `tao_auth_rule` VALUES ('63', 'admin/Forum/hot', '加精帖子', '', '1', '1', '15', '2', '', '0', '5', '', '0', '1611997465', '0');
INSERT INTO `tao_auth_rule` VALUES ('64', 'admin/Froum/recheck', '审核评论', '', '1', '1', '17', '2', '', '0', '10', '', '0', '1611997503', '0');
INSERT INTO `tao_auth_rule` VALUES ('65', 'admin/User/uploadImg', '上传用户头像', '', '1', '1', '6', '2', '', '0', '0', '', '0', '1611997661', '0');
INSERT INTO `tao_auth_rule` VALUES ('66', 'admin/AuthGroup/check', '审核角色', '', '1', '1', '8', '2', '', '0', '15', '', '0', '1611997835', '0');
INSERT INTO `tao_auth_rule` VALUES ('67', 'admin/Sign/signRule', '签到规则', '', '1', '1', '11', '2', '', '0', '15', '', '1585547595', '1611998427', '0');
INSERT INTO `tao_auth_rule` VALUES ('68', 'admin/Sign/add', '添加签到', '', '1', '1', '11', '2', '', '0', '16', '', '1585547705', '1611998444', '0');
INSERT INTO `tao_auth_rule` VALUES ('69', 'admin/Sign/signEdit', '编辑签到', '', '1', '1', '11', '2', '', '0', '17', '', '1585547774', '1611998457', '0');
INSERT INTO `tao_auth_rule` VALUES ('70', 'admin/Sign/delete', '删除签到', '', '1', '1', '11', '2', '', '0', '18', '', '1585547817', '1611998470', '0');
INSERT INTO `tao_auth_rule` VALUES ('71', 'admin/Vip/vipRule', '用户等级', '', '1', '1', '11', '2', '', '0', '19', '', '1585547921', '1611998481', '0');
INSERT INTO `tao_auth_rule` VALUES ('72', 'admin/Vip/add', '添加vip等级', '', '1', '1', '11', '2', '', '0', '20', '', '1585547981', '1611998492', '0');
INSERT INTO `tao_auth_rule` VALUES ('73', 'admin/Vip/vipEdit', '编辑vip等级', '', '1', '1', '11', '2', '', '0', '21', '', '1585548029', '1611998556', '0');
INSERT INTO `tao_auth_rule` VALUES ('74', 'admin/Vip/delete', '删除vip等级', '', '1', '1', '11', '2', '', '0', '22', '', '1585548077', '1611998503', '0');
INSERT INTO `tao_auth_rule` VALUES ('75', 'admin/Set/email', '邮箱设置', '', '1', '1', '10', '2', '', '0', '23', '', '1585548143', '1611998372', '0');
INSERT INTO `tao_auth_rule` VALUES ('76', 'admin/Notice/index', '发布通知', '', '1', '1', '4', '1', '', '1', '10', '', '1585618141', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('77', 'admin/Notice/add', '添加通知', '', '1', '1', '76', '2', '', '0', '11', '', '1585663336', '1611997580', '0');
INSERT INTO `tao_auth_rule` VALUES ('78', 'admin/Notice/edit', '编辑通知', '', '1', '1', '76', '2', '', '0', '12', '', '1585663366', '1611997590', '0');
INSERT INTO `tao_auth_rule` VALUES ('79', 'admin/Notice/delete', '删除通知', '', '1', '1', '76', '2', '', '0', '13', '', '1585663412', '1611997601', '0');
INSERT INTO `tao_auth_rule` VALUES ('83', 'admin/AuthAccess/index', '管理员权限', '', '1', '1', '1', '1', '', '1', '22', '', '1585794015', '0', '0');
INSERT INTO `tao_auth_rule` VALUES ('84', 'admin/AuthAccess/add', '添加管理员权限', '', '1', '1', '83', '2', '', '0', '23', '', '1585806544', '1611998012', '0');
INSERT INTO `tao_auth_rule` VALUES ('85', 'admin/AuthAccess/edit', '编辑管理员权限', '', '1', '1', '83', '2', '', '0', '24', '', '1585806592', '1611998030', '0');
INSERT INTO `tao_auth_rule` VALUES ('86', 'admin/AuthAccess/delete', '删除管理员权限', '', '1', '1', '83', '2', '', '0', '25', '', '1585806620', '1611998046', '0');
INSERT INTO `tao_auth_rule` VALUES ('87', 'admin/AuthAccess/check', '审核管理员权限', '', '1', '1', '83', '2', '', '0', '26', '', '1585806653', '1611998060', '0');
INSERT INTO `tao_auth_rule` VALUES ('88', 'admin/Set/website', '网站信息保存', '', '1', '1', '10', '2', '', '0', '24', '', '1585819936', '1611998395', '0');
INSERT INTO `tao_auth_rule` VALUES ('89', 'admin/User/auth', '设置超级用户', '', '1', '1', '6', '2', '', '0', '22', '', '1578984801', '1611997990', '0');
INSERT INTO `tao_auth_rule` VALUES ('90', 'admin/Forum/tagshot', '开启热点', '', '1', '1', '16', '2', '', '0', '15', '', '1585841826', '1611997546', '0');
INSERT INTO `tao_auth_rule` VALUES ('91', 'admin/Admin/infoSet', '资料设置', '', '1', '1', '12', '2', '', '0', '62', '', '1586245669', '1611998517', '0');
INSERT INTO `tao_auth_rule` VALUES ('92', 'admin/Admin/repassSet', '密码设置', '', '1', '1', '13', '2', '', '0', '64', '', '1586245727', '1611998534', '0');
INSERT INTO `tao_auth_rule` VALUES ('93', 'servers', '服务', '', '1', '1', '0', '0', 'layui-icon-cols', '1', '2', '', '1611286515', '1611997619', '0');
INSERT INTO `tao_auth_rule` VALUES ('94', 'admin/Database/index', '数据备份', '', '1', '1', '93', '1', '', '1', '9', '', '1611897141', '1611902589', '0');
INSERT INTO `tao_auth_rule` VALUES ('95', 'admin/Database/backup', '进行备份', '', '1', '1', '94', '2', '', '0', '10', '', '1611897285', '1611902610', '0');
INSERT INTO `tao_auth_rule` VALUES ('96', 'admin/Database/delete', '备份删除', '', '1', '1', '94', '2', '', '0', '0', '', '1611902429', '0', '0');

-- ----------------------------
-- Table structure for tao_cate
-- ----------------------------
DROP TABLE IF EXISTS `tao_cate`;
CREATE TABLE `tao_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `catename` varchar(20) NOT NULL COMMENT '导航名称',
  `ename` varchar(20) NOT NULL DEFAULT '' COMMENT '分类别名',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '状态1启用0禁用',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0帖子1文章',
  `desc` varchar(255) NOT NULL,
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否是热点',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updata_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `ename` (`ename`) COMMENT '英文名称索引'
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_cate
-- ----------------------------
INSERT INTO `tao_cate` VALUES ('1', '提问', 'ask', '1', '1', '0', 'TaoLer社区提问专栏1', '0', '0', '0', '0');
INSERT INTO `tao_cate` VALUES ('2', '分享', 'share', '2', '1', '0', '', '0', '0', '0', '0');
INSERT INTO `tao_cate` VALUES ('3', '讨论', 'talk', '3', '1', '0', '', '1', '0', '0', '0');

-- ----------------------------
-- Table structure for tao_collection
-- ----------------------------
DROP TABLE IF EXISTS `tao_collection`;
CREATE TABLE `tao_collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `article_id` int(11) NOT NULL COMMENT '文章id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章收藏表';

-- ----------------------------
-- Records of tao_collection
-- ----------------------------

-- ----------------------------
-- Table structure for tao_comment
-- ----------------------------
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
  PRIMARY KEY (`id`),
  KEY `aiticle_id` (`article_id`) USING BTREE COMMENT '文章评论索引',
  KEY `user_id` (`user_id`) USING BTREE COMMENT '评论用户索引'
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_comment
-- ----------------------------
INSERT INTO `tao_comment` VALUES ('1', 'https://www.aieok.com', '1', '1', '0', '0', '1', '1555127897', '1578977505', '1578977505');

-- ----------------------------
-- Table structure for tao_friend_link
-- ----------------------------
DROP TABLE IF EXISTS `tao_friend_link`;
CREATE TABLE `tao_friend_link` (
  `id` int(2) unsigned NOT NULL AUTO_INCREMENT COMMENT '友情链接id',
  `linkname` varchar(10) NOT NULL COMMENT '链接名称',
  `linksrc` varchar(60) NOT NULL COMMENT '链接地址',
  `linkimg` varchar(60) NOT NULL COMMENT '链接图片',
  `creat_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `delete_time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_friend_link
-- ----------------------------
INSERT INTO `tao_friend_link` VALUES ('1', 'taobao', 'https://www.taobao.com', '', '0', '0', '0');
INSERT INTO `tao_friend_link` VALUES ('2', 'baidu', 'https://www.baidu.com', '', '0', '0', '0');
INSERT INTO `tao_friend_link` VALUES ('3', 'tensent', 'https://www.qq.com', '', '0', '0', '0');

-- ----------------------------
-- Table structure for tao_mail_server
-- ----------------------------
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

-- ----------------------------
-- Records of tao_mail_server
-- ----------------------------
INSERT INTO `tao_mail_server` VALUES ('1', 'xxxx@aliyun.com', 'smtp.aliyun.com', '25', 'user', '123456', '0');

-- ----------------------------
-- Table structure for tao_message
-- ----------------------------
DROP TABLE IF EXISTS `tao_message`;
CREATE TABLE `tao_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '消息ID',
  `title` varchar(255) NOT NULL COMMENT '消息标题',
  `content` text COMMENT '消息内容',
  `user_id` int(11) NOT NULL COMMENT '发送人ID',
  `link` varchar(255) DEFAULT NULL COMMENT '链接',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '消息类型0系统消息1普通消息',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_message
-- ----------------------------

-- ----------------------------
-- Table structure for tao_message_to
-- ----------------------------
DROP TABLE IF EXISTS `tao_message_to`;
CREATE TABLE `tao_message_to` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '消息ID',
  `send_id` int(11) NOT NULL COMMENT '发送人ID',
  `receve_id` int(11) NOT NULL COMMENT '接收人ID',
  `message_id` varchar(255) NOT NULL COMMENT '消息标题',
  `message_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '消息类型0系统消息1普通消息',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '消息状态',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_message_to
-- ----------------------------

-- ----------------------------
-- Table structure for tao_plugins
-- ----------------------------
DROP TABLE IF EXISTS `tao_plugins`;
CREATE TABLE `tao_plugins` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `plugins_name` varchar(20) NOT NULL COMMENT '插件名称',
  `plugins_version` varchar(20) NOT NULL COMMENT '插件版本',
  `plugins_auther` varchar(20) NOT NULL COMMENT '插件作者',
  `plugins_resume` varchar(255) NOT NULL DEFAULT '' COMMENT '简介',
  `plugins_price` int(5) NOT NULL COMMENT '差价售价',
  `plugins_src` varchar(70) NOT NULL DEFAULT '' COMMENT '插件获取路径',
  `plugins_status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '插件状态1开启0禁止',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`,`plugins_version`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_plugins
-- ----------------------------
INSERT INTO `tao_plugins` VALUES ('1', '1.1.1', '', '', '第1个版本', '0', '/storage/version/20191215/536c65fc4df42100016fa3d97b584d26.zip', '1', '1575862901', '0', '0');
INSERT INTO `tao_plugins` VALUES ('2', '1.1.2', '', '', '第2个版本', '0', '/storage/version/20191215/832e150dfbc0e88e04a408e475bce8bb.zip', '1', '1575862901', '0', '0');
INSERT INTO `tao_plugins` VALUES ('3', '1.1.3', '', '', '第3个版本', '0', '/storage/version/20191215/9ff1153045f1ad1e26c74aad148bdde3.zip', '1', '1575862901', '1575862901', '0');
INSERT INTO `tao_plugins` VALUES ('4', '1.1.4', '1.0.0', 'au', '第四个版本', '5', '/storage/version/20191209/1fae8a15fcd41181490a0c02e0218ef1.zip', '1', '1575864450', '1575864587', '0');
INSERT INTO `tao_plugins` VALUES ('5', 'hello', 'v0.0.1', 'taoler', '第一款欢迎插件', '10', '/storage/addons/20201016/2c5b581d90a92b6b154fa9bc0c73d494.zip', '1', '1602833500', '1602833500', '0');

-- ----------------------------
-- Table structure for tao_point_note
-- ----------------------------
DROP TABLE IF EXISTS `tao_point_note`;
CREATE TABLE `tao_point_note` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `controller` varchar(255) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `pointid` int(10) unsigned NOT NULL,
  `score` int(10) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_point_note
-- ----------------------------

-- ----------------------------
-- Table structure for tao_slider
-- ----------------------------
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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_slider
-- ----------------------------
INSERT INTO `tao_slider` VALUES ('1', 'CODING', '1', '/storage/slider/F1.jpg', '#', '', '1574870400', '1575043200', '1', '0', '0', '0');
INSERT INTO `tao_slider` VALUES ('3', '通用右栏底部广告', '2', '/storage/slider/20200101/851c0b88a72590293bcb45454bdce056.jpg', 'https://www.aieok.com', '', '1571155200', '1609344000', '1', '0', '0', '0');

-- ----------------------------
-- Table structure for tao_system
-- ----------------------------
DROP TABLE IF EXISTS `tao_system`;
CREATE TABLE `tao_system` (
  `id` tinyint(2) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `webname` varchar(20) NOT NULL COMMENT '网站名称',
  `webtitle` varchar(30) NOT NULL,
  `domain` varchar(50) NOT NULL,
  `template` varchar(30) NOT NULL DEFAULT '' COMMENT '模板',
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
  `api_url` varchar(80) NOT NULL COMMENT 'api',
  `base_url` varchar(50) NOT NULL DEFAULT '',
  `upcheck_url` varchar(255) NOT NULL COMMENT '版本检测',
  `upgrade_url` varchar(255) NOT NULL COMMENT '升级地址',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='系统配置表';

-- ----------------------------
-- Records of tao_system
-- ----------------------------
INSERT INTO `tao_system` VALUES ('1', 'TaoLer社区演示站', '轻论坛系统', 'http://www.xxx.com', 'taoler', '/storage/logo/logo.png', '10', '2048', 'png|gif|jpg|jpeg|zip|rarr', '<a href=\"http://www.aieok.com\" target=\"_blank\">aieok.com 版权所有</a>', 'TaoLer,轻社区系统,bbs,论坛,Thinkphp6,layui,fly模板,', '这是一个Taoler轻社区论坛系统', '1', '1', '1', '0.0.0.0', '管理员|admin|审核员|超级|垃圾', '1.6.3', '', 'http://api.aieok.com', 'http://api.aieok.com/v1/index/cy', 'http://api.aieok.com/v1/upload/check', 'http://api.aieok.com/v1/upload/api', '1581221008', '1577419197');

-- ----------------------------
-- Table structure for tao_time_line
-- ----------------------------
DROP TABLE IF EXISTS `tao_time_line`;
CREATE TABLE `tao_time_line` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '时间线Id',
  `timeline_title` varchar(255) NOT NULL COMMENT '时间线标题',
  `timeline_content` text NOT NULL COMMENT '时间线内容',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_time_line
-- ----------------------------
INSERT INTO `tao_time_line` VALUES ('8', '2019年5月1日', '<p>thinkphp5.1+layui2.4+fly3.0</p><p>TBLs0.1</p>', '1584870099', '1584870099', '0');
INSERT INTO `tao_time_line` VALUES ('9', '2019年5月5日', '<p>创建数据表结构,</p><p>建立前台框架,</p><p>构建用户端html；</p><p>登录，注册，密码找回。</p>', '1584871136', '1584871136', '0');
INSERT INTO `tao_time_line` VALUES ('10', '2019年5月10日', '<p>[新增]&nbsp;注册模块，进行动态密码双重加密</p><p>[新增]登录模块，加入validata验证</p><p>[新增]密码找回</p><p>[安装]phpmailer插件</p><p>[优化]验证码captcha</p>', '1584871314', '1584871314', '0');
INSERT INTO `tao_time_line` VALUES ('11', '2019年5月15日', '<p><span>[优化]</span><span>&nbsp;</span>注册模块，邮箱用户名多态登录</p><p>[优化]密码找回，更新验证码</p>', '1584871602', '1584871602', '0');
INSERT INTO `tao_time_line` VALUES ('12', '2019年5月25日', '<p>[新增]文章添加，编辑，删除</p><p>[新增]首页，分类，详情</p><p>[新增]回帖</p><p>[优化]多态登录</p><p>[优化]邮件首发验证码</p>', '1585208263', '1585208263', '0');
INSERT INTO `tao_time_line` VALUES ('13', '2019年6月1日', '<p>[新增]右栏各模块</p><p>[新增]签到</p><p>[优化]帖子发布</p><p>[更新]layui2.5</p>', '1585208481', '1585208481', '0');
INSERT INTO `tao_time_line` VALUES ('14', '2019年6月10日', '<p>[优化]前台分类导航选择</p><p>[优化]前台分类筛选选择</p><p>[优化]cate控制</p><p>[修复]未登录状态点文章发布跳转</p>', '1585208796', '1585208796', '0');
INSERT INTO `tao_time_line` VALUES ('15', '2019年6月20日', '<p>[新增]后台模版的更换</p><p>[新增]账户管理</p>', '1585209047', '1585209047', '0');
INSERT INTO `tao_time_line` VALUES ('16', '2019年7月5日', '<p>[新增]内容的管理模块</p><p>[新增]设置管理模块</p><p>[修复]前台用户页面bug</p>', '1585209329', '1585209329', '0');
INSERT INTO `tao_time_line` VALUES ('17', '2019年7月25日', '<p>[新增]应用模块，系统升级，版本发布，授权管理等</p><p><br></p>', '1585209434', '1585209434', '0');
INSERT INTO `tao_time_line` VALUES ('18', '2019年8月10日', '[优化]管理，用户，管理员，角色，权限', '1585209551', '1585209551', '0');
INSERT INTO `tao_time_line` VALUES ('19', '2019年9月10日', '<p>[更新]layui2.5.5</p><p>[修复]授权前后台</p><p>[修复]前台版本升级系统的检测</p><p><br></p>', '1585209783', '1585209783', '0');
INSERT INTO `tao_time_line` VALUES ('20', '2019年9月25日 ', '<p>[修复]权限控制</p><p>[修复]角色管理</p><p>[优化]网站设置Logo保存</p><p>[优化]前台注册在一些信息</p><p><br></p>', '1585210095', '1585210095', '0');
INSERT INTO `tao_time_line` VALUES ('21', '2019年10月5日', '[修复]一些已知bug', '1585210220', '1585210220', '0');
INSERT INTO `tao_time_line` VALUES ('22', '2019年10月25日', '<p>[更新] 升级Thinkphp6框架为正式版</p><p>[优化] 各查询条件相关优化</p>', '1585210342', '1585210342', '0');
INSERT INTO `tao_time_line` VALUES ('23', '2019年11月5日', '<p><span>[优化] 后台多模块功能的优化</span></p><p><span>[新增] 后台多个功能的增加</span></p>', '1585210449', '1585210449', '0');
INSERT INTO `tao_time_line` VALUES ('24', '2019年11月20日', '<p>[新增]广告管理</p><p>[优化]幻灯和广告合并</p><p>[修复] 账户密码，基本资料设置</p>', '1585210712', '1585210712', '0');
INSERT INTO `tao_time_line` VALUES ('25', '2019年12月5日', '<p>[新增] 安装引导程序</p><p>[优化] 数据库表</p><p>[优化] 后台数个页面的优化和新增</p>', '1585210905', '1585210905', '0');
INSERT INTO `tao_time_line` VALUES ('26', '2019年12月15日', '<p>[修复] 修复多数已知问题</p><p>[修复] 转移测试平台</p>', '1585211052', '1585211052', '0');
INSERT INTO `tao_time_line` VALUES ('27', '2019年12月30日', '<p><span>[修复] 安装引导文件的修复</span></p><p><span>[新增] 发布测试版本前的准备</span></p><p><span>[新增] 系统的介绍</span></p>', '1585211146', '1585211146', '0');
INSERT INTO `tao_time_line` VALUES ('28', '2020年1月1日 Beat 1.0', '[新增] 发布1.0测试版本，命名TaoLer。', '1585211259', '1585211259', '0');
INSERT INTO `tao_time_line` VALUES ('29', '2020年1月1-31日', '<p><span>[修复] 数个问题修复和完善</span></p>', '1585211340', '1585211340', '0');
INSERT INTO `tao_time_line` VALUES ('30', '2020年2月1-29日', '<p>[新增] 帖子tags</p><p>[优化] 帖子文章的SEO</p><p>[新增] auth模块发布至github</p><p>[更新] auth权限管理插件</p><p>[修复] 引导文件的数据库配置</p><p>[修复] 授权在某些服务器的获取</p><p>[修复] 文件上传</p>', '1585211685', '1585211685', '0');
INSERT INTO `tao_time_line` VALUES ('31', '2020年3月10', '<p><span>[优化] 帖子，分类各类缓存，数据库查询，性能优化等</span></p><p>[优化] 前台超级管理员权限的分配</p><p><span>[修复] 编辑帖子，发帖人改变的bug</span></p><p>[新增] 插件管理模块</p><p>[新增] 登录日志文件，浏览帖子日志</p>', '1585212061', '1585212061', '0');
INSERT INTO `tao_time_line` VALUES ('32', '2020年3月20', '<p>[新增] 版本更新日志线</p><p>[修复] 前台会员页面展示</p><p>[优化] 多个页面的缓存</p><p>[优化] 前台用户授权页面</p>', '1585212188', '1585212188', '0');
INSERT INTO `tao_time_line` VALUES ('33', '2020年3月26', '<p>[新增] 站内信消息模块</p><p><br></p>', '1585212283', '1585212283', '0');
INSERT INTO `tao_time_line` VALUES ('34', '2020年3月29', '<p><span>[新增] 用户后台签到模块</span></p><p>[新增] 积分等级后台设置</p><p>[优化] 消息通知功能</p>', '1585484428', '1585484428', '0');
INSERT INTO `tao_time_line` VALUES ('35', '2020年3月30', '<p>[新增] VIP模块</p><p>[优化] 优化签到积分规则</p><p>[修复] 权限规则</p>', '1585577995', '1585577995', '0');
INSERT INTO `tao_time_line` VALUES ('36', 'TaoLer V1.0.0正式版', '<p>[新增] 后台消息管理模块，发全站内信，单独推送用户消息</p><p>[优化] 前台用户系统消息和接收系统消息&nbsp;</p><p>[版本] 框架基本定型，推送1.0正式版，其它待优化事项逐步完成</p>', '1585669343', '1585669343', '0');
INSERT INTO `tao_time_line` VALUES ('37', '222', '222', '1585752242', '1585752256', '1585752256');
INSERT INTO `tao_time_line` VALUES ('38', '111', '1111', '1585754796', '1585754830', '1585754830');
INSERT INTO `tao_time_line` VALUES ('39', 'TaoLer V1.1.0', '<p>[修复] 后台权限控制更加仔细，权限，角色，用户组分配三级设置</p><p>[修复] 移除管理员的角色直接分配</p><p>[新增] 后台分类的热点的单选设置选项</p><p>[新增] 添加数据库auth_group_access id字段,可以更改用户的编辑操作</p><p>[新增] 权限控制菜单和按钮规则的添加</p><p>[优化] 站内信后台通知和前台之间的系统通知和用户通知的读取</p>', '1585898118', '1585898118', '0');
INSERT INTO `tao_time_line` VALUES ('40', 'qqq', 'qqqqqq', '1588050588', '1605504731', '1605504731');
INSERT INTO `tao_time_line` VALUES ('41', 'ddd', 'dddd', '1605507216', '1605507216', '0');

-- ----------------------------
-- Table structure for tao_upgrade_auth
-- ----------------------------
DROP TABLE IF EXISTS `tao_upgrade_auth`;
CREATE TABLE `tao_upgrade_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `user` varchar(20) NOT NULL COMMENT '用户',
  `domain` varchar(30) NOT NULL COMMENT '域名',
  `key` varchar(60) NOT NULL COMMENT '授权秘钥',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `auth_level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '授权等级0无授权1初级2中级3高级',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_upgrade_auth
-- ----------------------------
INSERT INTO `tao_upgrade_auth` VALUES ('1', 'admin', 'http://www.tp6.com', '9ee40f0c4f5c8f2f10b06ae2e1ddda96ac709c4a', '1', '0', '0', '0', '0', '0');
INSERT INTO `tao_upgrade_auth` VALUES ('2', 'tao', 'http://www.tp6.com', '123456', '1', '1', '1577721600', '0', '1577172802', '1577172802');
INSERT INTO `tao_upgrade_auth` VALUES ('3', 'admin', 'https://www.tp.com', 'e49183beee30d0b463fbf415d63cce3908d95599', '1', '0', '0', '1576835663', '1583765865', '1583765865');
INSERT INTO `tao_upgrade_auth` VALUES ('4', 'admin', 'https://www.tp6.com', '9ee40f0c4f5c8f2f10b06ae2e1ddda96ac709c4a', '1', '1', '0', '1576835915', '1576835915', '0');
INSERT INTO `tao_upgrade_auth` VALUES ('5', 'admin', 'http://www.baidu.com', 'fd27553322b3ed27ff7114b1c540901d36ac1ef8', '1', '0', '0', '1576836110', '1576836110', '0');
INSERT INTO `tao_upgrade_auth` VALUES ('6', 'admin', 'https://www.taobao.com', 'b5d63bc7ae0d86c4d4019e2aea8d828f06818cc7', '1', '1', '0', '1576836142', '1583765908', '1583765908');
INSERT INTO `tao_upgrade_auth` VALUES ('7', 'admin', 'http://www.taobao.com', '4931b81d82673528828d2fd64a0414e8925c9221', '1', '0', '0', '1576836211', '1583765821', '1583765821');
INSERT INTO `tao_upgrade_auth` VALUES ('8', 'admin', 'http://qianniu.com', '0106c5c4f8e0a1d97745353bee6201783d481ac2', '1', '1', '0', '1576836529', '1583765837', '1583765837');
INSERT INTO `tao_upgrade_auth` VALUES ('9', 'admin', 'http://baidu.com', '4ce7a1d87218353d09081a291f38ddb290b0630b', '1', '0', '0', '1576836712', '1583765886', '1583765886');
INSERT INTO `tao_upgrade_auth` VALUES ('10', 'zhao', 'http://tao.tp6.com', '374f2e2a63f1201924a07561095cd078acae238b', '1', '1', '0', '1576837200', '1576837200', '0');
INSERT INTO `tao_upgrade_auth` VALUES ('11', 'ZhiQ', 'http://www.hicheng.cn', '20b32d07aa9c2fb24afed0bf72f420144928fd22', '1', '0', '0', '1579012640', '1579012640', '0');
INSERT INTO `tao_upgrade_auth` VALUES ('12', 'ZhiQ', 'https://www.hicheng.cn', '9a2e0b92f54238760c48d6021adba6e20c9ede0a', '1', '0', '0', '1579012652', '1579012652', '0');
INSERT INTO `tao_upgrade_auth` VALUES ('13', 'lingkur', 'http://demo.biudj.com', '15eacf288cc4e8a1a9b69da3338607d0710b8cd1', '1', '0', '0', '1579455284', '1579455284', '0');
INSERT INTO `tao_upgrade_auth` VALUES ('14', '云飞', 'https://www.cqtl520.com', 'db88afaedaacc1cabc15013dd3ea5f412f6086c9', '1', '0', '0', '1579665257', '1579665257', '0');
INSERT INTO `tao_upgrade_auth` VALUES ('15', '123', 'http://http://www.aieok.com/', '13971a9975a4e4eb9827a01767a2796c68e03b69', '1', '0', '0', '1583659119', '1583659119', '0');
INSERT INTO `tao_upgrade_auth` VALUES ('16', '22553456', 'http://www.srsn.com', 'fb86db60328ada7c0180a3f32765777dc4250a7f', '1', '0', '0', '1584153862', '1584153862', '0');
INSERT INTO `tao_upgrade_auth` VALUES ('17', 'maqiang', 'https://www.wujiangpu.com', 'b4c22df2a87ecd0524b90cf8d56eeadeba58a8a1', '1', '0', '0', '1584278073', '1584278073', '0');
INSERT INTO `tao_upgrade_auth` VALUES ('18', 'admin', 'https://www.igoke.cn', '8e8a3c05508f853fb06e3d5accd927d949194971', '1', '0', '0', '1585382531', '1590487273', '1590487273');
INSERT INTO `tao_upgrade_auth` VALUES ('19', 'tt', 'http://www.tt.com', 'ff9f48057c831cb5db3af907b799b754f0499050', '1', '0', '2020', '1590486647', '1590487269', '1590487269');
INSERT INTO `tao_upgrade_auth` VALUES ('20', 'dfdf', 'http://wwerw.com', '1675905f7042fcdbca2c2de43ba79827ee62631f', '1', '0', '2020', '1590486715', '1590486715', '0');
INSERT INTO `tao_upgrade_auth` VALUES ('21', 'fdfdf', 'dfdfdsf', 'cb71fef668253fe6b2761f10500af9440acdcd8d', '1', '0', '2020', '1590486752', '1590487269', '1590487269');
INSERT INTO `tao_upgrade_auth` VALUES ('22', 'fdfdf', 'http://wwwe.com', '1db68d6d7d0cb9fd44ad51e22611680f3aa26a7d', '1', '0', '1590854400', '1590486950', '1590486950', '0');

-- ----------------------------
-- Table structure for tao_user
-- ----------------------------
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
  `user_img` varchar(70) NOT NULL DEFAULT '' COMMENT '头像',
  `auth` enum('1','0') NOT NULL DEFAULT '0' COMMENT '管理员权限0普通1超级',
  `point` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
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
  PRIMARY KEY (`id`),
  KEY `name` (`name`) USING BTREE COMMENT '用户名查询用户索引',
  KEY `email` (`email`) USING BTREE COMMENT 'email查询用户索引'
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_user
-- ----------------------------
INSERT INTO `tao_user` VALUES ('1', 'admin', '95d6f8d0d0c3b45e5dbe4057da1b149e', '2147483647', 'admin@qq.com', '管理员', '北京市', '1', '这是我的第一个TP5系统，2019北京。OK! OK!ok@', '/static/res/images/avatar/00.jpg', '1', '14', '1', '1', '0', '127.0.0.1', '0', '0', '0', '0', '1579053025', '1578469091', '0');

-- ----------------------------
-- Table structure for tao_user_area
-- ----------------------------
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

-- ----------------------------
-- Records of tao_user_area
-- ----------------------------
INSERT INTO `tao_user_area` VALUES ('1', '北京', '京', '0', '0', '0');
INSERT INTO `tao_user_area` VALUES ('2', '上海', '沪', '0', '0', '0');
INSERT INTO `tao_user_area` VALUES ('3', '广州', '广', '0', '0', '0');
INSERT INTO `tao_user_area` VALUES ('4', '深圳', '深', '0', '0', '0');

-- ----------------------------
-- Table structure for tao_user_sign
-- ----------------------------
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户签到表';

-- ----------------------------
-- Records of tao_user_sign
-- ----------------------------

-- ----------------------------
-- Table structure for tao_user_signrule
-- ----------------------------
DROP TABLE IF EXISTS `tao_user_signrule`;
CREATE TABLE `tao_user_signrule` (
  `id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `days` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '连续天数',
  `score` int(3) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '升级时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='用户签到积分规则';

-- ----------------------------
-- Records of tao_user_signrule
-- ----------------------------
INSERT INTO `tao_user_signrule` VALUES ('1', '1', '2', '0', '0', '0');
INSERT INTO `tao_user_signrule` VALUES ('2', '3', '3', '0', '0', '0');
INSERT INTO `tao_user_signrule` VALUES ('3', '5', '5', '0', '0', '0');

-- ----------------------------
-- Table structure for tao_user_viprule
-- ----------------------------
DROP TABLE IF EXISTS `tao_user_viprule`;
CREATE TABLE `tao_user_viprule` (
  `id` int(2) NOT NULL AUTO_INCREMENT COMMENT '用户等级ID',
  `score` varchar(255) NOT NULL DEFAULT '0' COMMENT '积分区间',
  `vip` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'vip等级',
  `nick` varchar(20) DEFAULT NULL COMMENT '认证昵称',
  `rules` varchar(255) DEFAULT NULL COMMENT '权限',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '升级时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_user_viprule
-- ----------------------------
INSERT INTO `tao_user_viprule` VALUES ('1', '0-99', '0', '游民', '', '1585476523', '1585544577', '0');
INSERT INTO `tao_user_viprule` VALUES ('2', '100-299', '1', '富农', '', '1585476551', '1585546376', '0');
INSERT INTO `tao_user_viprule` VALUES ('3', '300-500', '2', '地主', '', '1585545450', '1585546241', '0');
INSERT INTO `tao_user_viprule` VALUES ('4', '501-699', '3', '土豪', '', '1585545542', '1585569657', '0');

-- ----------------------------
-- Table structure for tao_user_zan
-- ----------------------------
DROP TABLE IF EXISTS `tao_user_zan`;
CREATE TABLE `tao_user_zan` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '点赞主键id',
  `comment_id` int(11) NOT NULL COMMENT '评论id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '点赞时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_user_zan
-- ----------------------------

-- ----------------------------
-- Table structure for tao_version
-- ----------------------------
DROP TABLE IF EXISTS `tao_version`;
CREATE TABLE `tao_version` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `version_name` varchar(20) NOT NULL COMMENT '版本名称',
  `version_src` varchar(70) NOT NULL DEFAULT '' COMMENT '版本文件路径',
  `version_resume` varchar(255) NOT NULL DEFAULT '' COMMENT '简介',
  `version_status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '1开启0禁止',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_version
-- ----------------------------
INSERT INTO `tao_version` VALUES ('1', '1.1.1', '/storage/version/20191215/536c65fc4df42100016fa3d97b584d26.zip', '第1个版本', '1', '1575862901', '0', '1575864587');
INSERT INTO `tao_version` VALUES ('2', '1.1.2', '/storage/version/20191215/832e150dfbc0e88e04a408e475bce8bb.zip', '第2个版本', '1', '1575862901', '0', '1575864587');
INSERT INTO `tao_version` VALUES ('3', '1.1.3', '/storage/version/20191215/9ff1153045f1ad1e26c74aad148bdde3.zip', '第3个版本', '1', '1575862901', '1575862901', '1575864587');
INSERT INTO `tao_version` VALUES ('4', '1.1.4', '/storage/version/20191209/1fae8a15fcd41181490a0c02e0218ef1.zip', '第四个版本', '1', '1575864450', '1575864587', '1575864587');

-- ----------------------------
-- Table structure for tao_webconfig
-- ----------------------------
DROP TABLE IF EXISTS `tao_webconfig`;
CREATE TABLE `tao_webconfig` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ename` varchar(30) NOT NULL COMMENT '英文名',
  `cname` varchar(50) NOT NULL COMMENT '中文名',
  `form_type` varchar(10) DEFAULT '' COMMENT '表单类型',
  `conf_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '配置类型1论坛2网店3企业站',
  `values` varchar(255) NOT NULL,
  `value` varchar(60) NOT NULL COMMENT '默认值',
  `sort` tinyint(2) NOT NULL DEFAULT '20',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tao_webconfig
-- ----------------------------
INSERT INTO `tao_webconfig` VALUES ('1', 'template', '', '', '0', '', 'taoler', '20', '0', '0', '0');
