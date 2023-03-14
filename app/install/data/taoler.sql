/*
 Navicat Premium Data Transfer

 Target Server Type    : MySQL
 Target Server Version : 80020 (8.0.20)
 File Encoding         : 65001

 Date: 14/03/2023 19:57:43
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tao_admin
-- ----------------------------
DROP TABLE IF EXISTS `tao_admin`;
CREATE TABLE `tao_admin`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '管理员账户',
  `nickname` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '密码',
  `email` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `sex` tinyint(1) UNSIGNED ZEROFILL NOT NULL DEFAULT 0 COMMENT '0女1男',
  `avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'static/admin/images/avatar.jpg' COMMENT '头像',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1启用0待审-1禁用',
  `auth_group_id` smallint NOT NULL DEFAULT 0 COMMENT '1超级管理员0是普通管理员',
  `remarks` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `last_login_ip` varchar(70) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `last_login_time` int NOT NULL DEFAULT 0 COMMENT '最后登录时间',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '软删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_admin
-- ----------------------------
INSERT INTO `tao_admin` VALUES (1, 'admin', '管理员', '95d6f8d0d0c3b45e5dbe4057da1b149e', 'taoler@qq.com', '13812345678', 1, 'static/admin/images/avatar.jpg', 1, 1, '2021 TaoLer！', '127.0.0.1', 1678677599, 1579053025, 1578986600, 0);
INSERT INTO `tao_admin` VALUES (2, 'test', '测试账号', '95d6f8d0d0c3b45e5dbe4057da1b149e', 'test1@qq.com', '13012345678', 0, 'static/admin/images/avatar.jpg', 1, 0, '', '127.0.0.1', 1678428810, 1579053025, 1678418924, 0);

-- ----------------------------
-- Table structure for tao_article
-- ----------------------------
DROP TABLE IF EXISTS `tao_article`;
CREATE TABLE `tao_article`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
  `status` enum('0','-1','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '状态1显示0待审-1禁止',
  `cate_id` int NOT NULL COMMENT '分类id',
  `user_id` int NOT NULL COMMENT '用户id',
  `goods_detail_id` int NULL DEFAULT NULL COMMENT '商品ID',
  `is_top` enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '置顶1否0',
  `is_hot` enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '推荐1否0',
  `is_reply` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '0禁评1可评',
  `has_img` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '1有图0无图',
  `has_video` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '1有视频0无',
  `has_audio` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '1有音频0无',
  `pv` int NOT NULL DEFAULT 0 COMMENT '浏览量',
  `jie` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '0未结1已结',
  `upzip` varchar(70) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '文章附件',
  `downloads` int NOT NULL DEFAULT 0 COMMENT '下载量',
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '关键词',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'seo描述',
  `read_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '阅读权限0开放1回复可读2密码可读3私密',
  `art_pass` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '文章加密密码',
  `title_color` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '标题颜色',
  `title_font` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '标题字形',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE COMMENT '文章的用户索引',
  INDEX `cate_id`(`cate_id`) USING BTREE COMMENT '文章分类索引'
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tao_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `tao_auth_group`;
CREATE TABLE `tao_auth_group`  (
  `id` mediumint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '角色名称',
  `rules` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '拥有权限',
  `limits` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '权限范围',
  `descr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '权限描述',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '角色状态1可用0禁止',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户组权限表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_auth_group
-- ----------------------------
INSERT INTO `tao_auth_group` VALUES (1, '超级管理员', '5,15,21,22,62,63,23,17,27,28,64,16,24,26,25,4,20,32,33,34,14,29,30,31,1,65,6,35,36,37,38,7,39,40,41,42,8,43,44,45,66,9,47,48,49,50,46,67,2,10,51,11,18,52,54,55,19,56,57,58,59,60,53,3,12,13', '管理所有的管理员', '所有权限', 1, 0, 1578984825, 0);
INSERT INTO `tao_auth_group` VALUES (2, '管理员', '1,11,17,20,23,24,31,32,39,41,53,59,2,60,62,3,73,74,4,100,101,117,118', '所有列表的管理', '普通管理员', 1, 0, 1678372607, 0);
INSERT INTO `tao_auth_group` VALUES (3, '帖子管理', '5,15,21,22,62,63,23,17,27,28,64,16,24,26,25', '负责帖子的审核', '文章专员', 1, 0, 1578980219, 0);
INSERT INTO `tao_auth_group` VALUES (4, '网站维护', '2,10,51,11,18,52,54,55,19,56,57,58,59,60,53,3,12,13', '对数据进行统计', '网站维护', 1, 0, 1578980364, 0);

-- ----------------------------
-- Table structure for tao_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `tao_auth_group_access`;
CREATE TABLE `tao_auth_group_access`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '用户组id',
  `uid` int UNSIGNED NOT NULL,
  `group_id` int UNSIGNED NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '用户权限组状态0禁止1正常',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE,
  INDEX `group_id`(`group_id`) USING BTREE,
  INDEX `uid_group_id`(`uid`, `group_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户组明细表' ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of tao_auth_group_access
-- ----------------------------
INSERT INTO `tao_auth_group_access` VALUES (1, 1, 1, 1, 0, 0, 0);
INSERT INTO `tao_auth_group_access` VALUES (2, 2, 2, 1, 0, 0, 0);

-- ----------------------------
-- Table structure for tao_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `tao_auth_rule`;
CREATE TABLE `tao_auth_rule`  (
  `id` mediumint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '权限主键ID',
  `name` char(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '权限名称',
  `title` char(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '权限标题',
  `type` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1启用,0禁用',
  `pid` smallint NOT NULL DEFAULT 0 COMMENT '父级ID',
  `level` tinyint(1) NOT NULL DEFAULT 1 COMMENT '菜单层级',
  `icon` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '图标',
  `ismenu` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0目录,1菜单2按钮',
  `sort` int NOT NULL DEFAULT 50 COMMENT '排序',
  `condition` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE,
  INDEX `pid`(`pid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 124 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '权限表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_auth_rule
-- ----------------------------
INSERT INTO `tao_auth_rule` VALUES (1, 'System', 'System', 1, 1, 0, 0, 'layui-icon-user', 0, 1, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (2, 'Account', 'Account', 1, 1, 0, 0, 'layui-icon-set', 0, 1, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (3, 'Addon', 'Addon', 1, 1, 0, 0, 'layui-icon-component', 0, 3, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (4, 'Content', 'Content', 1, 1, 0, 0, 'layui-icon-app', 0, 4, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (5, 'Set', 'Set', 1, -1, 0, 0, 'layui-icon-read', 0, 5, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (6, 'Server', 'Server', 1, -1, 0, 0, '', 0, 6, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (7, 'Apps', 'Apps', 1, -1, 0, 0, '', 0, 7, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (8, 'ID8', 'ID8', 1, -1, 0, 0, '', 0, 8, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (9, 'ID9', 'ID9', 1, -1, 0, 0, '', 0, 9, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (10, 'apps/index', 'ID10', 1, -1, 0, 0, '', 0, 10, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (11, 'system.admin/index', 'Account management', 1, 1, 1, 1, '', 1, 1, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (12, 'system.admin/list', 'Account list', 1, 1, 11, 2, '', 2, 51, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (13, 'system.admin/add', 'Add account', 1, 1, 11, 2, '', 2, 52, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (14, 'system.admin/edit', 'Edit account', 1, 1, 11, 2, '', 2, 53, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (15, 'system.admin/delete', 'Delete account', 1, 1, 11, 2, '', 2, 54, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (16, 'system.admin/check', 'Check account', 1, 1, 11, 2, '', 2, 55, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (17, 'system.admin/info', 'Account info', 1, 1, 11, 2, '', 2, 56, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (18, 'system.admin/infoEdit', 'Edit account info', 1, 1, 11, 2, '', 2, 57, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (19, 'system.admin/infoSet', 'Set account info', 1, 1, 11, 2, '', 2, 58, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (20, 'system.admin/repass', 'View admin password', 1, 1, 11, 2, '', 2, 59, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (21, 'system.admin/repassSet', 'Reset admin password', 1, 1, 11, 2, '', 2, 60, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (22, 'system.admin/clearCache', 'Clear cache', 1, 1, 11, 2, '', 2, 61, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (23, 'system.admin/logout', 'Logout', 1, 1, 11, 2, '', 2, 62, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (24, 'system.AuthGroup/index', 'Role management', 1, 1, 1, 1, '', 1, 2, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (25, 'system.AuthGroup/list', 'Role list', 1, 1, 24, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (26, 'system.AuthGroup/add', 'Add role', 1, 1, 24, 2, '', 2, 51, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (27, 'system.AuthGroup/edit', 'Edit role', 1, 1, 24, 2, '', 2, 52, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (28, 'system.AuthGroup/check', 'Check role', 1, 1, 24, 2, '', 2, 53, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (29, 'system.AuthGroup/auth', 'Auth role', 1, 1, 24, 2, '', 2, 54, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (30, 'system.AuthGroup/delete', 'Delete role', 1, 1, 24, 2, '', 2, 55, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (31, 'system.AuthRule/index', 'Rule management', 1, 1, 1, 1, '', 1, 3, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (32, 'system.AuthRule/list', 'Rule list', 1, 1, 31, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (33, 'system.AuthRule/add', 'Add rule', 1, 1, 31, 2, '', 2, 51, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (34, 'system.AuthRule/edit', 'Edit rule', 1, 1, 31, 2, '', 2, 52, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (35, 'system.AuthRule/delete', 'Delete rule', 1, 1, 31, 2, '', 2, 53, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (36, 'system.AuthRule/check', 'Check rule', 1, 1, 31, 2, '', 2, 54, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (37, 'system.AuthRule/ruleTree', 'Rule tree', 1, 1, 31, 2, '', 2, 55, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (38, 'system.AuthRule/sort', 'Rule sort', 1, 1, 31, 2, '', 2, 56, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (39, 'system.upgrade/index', 'Upgrade', 1, 1, 1, 1, '', 1, 4, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (40, 'system.upgrade/key', 'Upgrade key', 1, 1, 39, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (41, 'system.upgrade/check', 'Upgrade check', 1, 1, 39, 2, '', 2, 51, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (42, 'system.upgrade/keyedit', 'Eidt key', 1, 1, 39, 2, '', 2, 52, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (43, 'system.upgrade/upload', 'Upgrade system', 1, 1, 39, 2, '', 2, 53, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (44, 'system.upgrade/uploadZip', 'Upload zip', 1, 1, 39, 2, '', 2, 54, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (45, 'system.upgrade/backFile', 'Back file', 1, 1, 39, 2, '', 2, 55, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (53, 'system.set/index', 'Web', 1, 1, 1, 1, '', 1, 5, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (54, 'system.set/website', 'Website', 1, 1, 53, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (55, 'system.set/config', 'Web config', 1, 1, 53, 2, '', 2, 51, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (56, 'system.set/setDomain', 'Set domain', 1, 1, 53, 2, '', 2, 52, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (57, 'system.set/bindMap', 'Bind map', 1, 1, 53, 2, '', 2, 53, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (58, 'system.set/setUrl', 'Set url', 1, 1, 53, 2, '', 2, 54, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (59, 'system.set/upload', 'web upload', 1, 1, 53, 2, '', 2, 55, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (60, 'user.user/index', 'User management', 1, 1, 2, 1, '', 1, 1, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (61, 'user.user/uploadImg', 'Upload avatar', 1, 1, 60, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (62, 'user.user/list', 'User list', 1, 1, 60, 2, '', 2, 51, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (63, 'user.user/add', 'Add user', 1, 1, 60, 2, '', 2, 52, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (64, 'user.user/edit', 'Edit user', 1, 1, 60, 2, '', 2, 53, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (65, 'user.user/delete', 'Delete user', 1, 1, 60, 2, '', 2, 54, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (66, 'user.user/check', 'Check user', 1, 1, 60, 2, '', 2, 55, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (67, 'user.user/auth', 'Superuser', 1, 1, 60, 2, '', 2, 56, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (68, 'user.vip/index', '用户vip', 1, 1, 2, 1, '', 1, 2, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (69, 'user.vip/list', 'vip列表', 1, 1, 68, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (70, 'user.vip/add', '添加vip', 1, 1, 68, 2, '', 2, 51, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (71, 'user.vip/edit', '编辑vip', 1, 1, 68, 2, '', 2, 52, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (72, 'user.vip/delete', '删除vip', 1, 1, 68, 2, '', 2, 53, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (73, 'addon.addons/index', '插件市场', 1, 1, 3, 1, '', 1, 1, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (74, 'addon.addons/getList', '插件列表', 1, 1, 73, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (75, 'addon.addons/install', '安装插件', 1, 1, 73, 2, '', 2, 51, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (76, 'addon.addons/uninstall', '卸载插件', 1, 1, 73, 2, '', 2, 52, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (77, 'addon.addons/upgrade', '升级插件', 1, 1, 73, 2, '', 2, 53, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (78, 'addon.addons/check', '插件开关', 1, 1, 73, 2, '', 2, 54, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (79, 'addon.addons/config', '配置插件', 1, 1, 73, 2, '', 2, 55, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (80, 'addon.addons/addAddonMenu', '添加插件菜单', 1, 1, 73, 2, '', 2, 56, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (81, 'addon.addons/delAddonMenu', '卸载插件菜单', 1, 1, 73, 2, '', 2, 57, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (82, 'addon.addons/userLogin', '用户登录', 1, 1, 73, 2, '', 2, 58, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (83, 'addon.addons/pay', '购买插件', 1, 1, 73, 2, '', 2, 59, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (84, 'addon.addons/isPay', '支付检测', 1, 1, 73, 2, '', 2, 60, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (88, 'addon.addons/add', '离线安装', 1, 1, 73, 2, '', 2, 61, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (89, 'addon.addons/uploadZip', '上传插件', 1, 1, 73, 2, '', 2, 62, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (90, 'addon.addons/uploads', '上传接口', 1, 1, 73, 2, '', 2, 63, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (91, 'addon.template/index', '模板市场', 1, 1, 3, 1, '', 1, 2, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (92, 'addon.template/install', '模板安装', 1, 1, 91, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (93, 'addon.template/uninstall', '模板卸载', 1, 1, 91, 2, 'layui-icon-cols', 2, 51, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (94, 'addon.template/upgrade', '模板升级', 1, 1, 91, 2, '', 2, 52, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (95, 'addon.template/list', '模板列表', 1, 1, 91, 2, '', 2, 53, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (96, 'addon.template/info', '模板信息', 1, 1, 91, 2, '', 2, 54, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (97, 'addon.template/pay', '模板支付', 1, 1, 91, 2, 'layui-icon-flag', 2, 55, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (98, 'addon.template/isPay', '支付检测', 1, 1, 91, 2, '', 2, 56, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (99, 'addon.template/check', '模板状态', 1, 1, 91, 2, '', 2, 57, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (100, 'content.forum/index', '帖子管理', 1, 1, 4, 1, 'layui-icon-component', 1, 1, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (101, 'content.forum/list', '帖子列表', 1, 1, 100, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (102, 'content.forum/add', '添加帖子', 1, 1, 100, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (103, 'content.forum/edit', '编辑帖子', 1, 1, 100, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (104, 'content.forum/delete', '删除帖子', 1, 1, 100, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (105, 'content.forum/check', '审核帖子', 1, 1, 100, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (106, 'content.forum/uploads', '上传接口', 1, 1, 100, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (107, 'content.forum/getKeywords', '帖子关键词', 1, 1, 100, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (108, 'content.forum/getWordList', '百度词条', 1, 1, 100, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (109, 'content.forum/hasIva', '图像标志', 1, 1, 100, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (110, 'content.forum/getDescription', '帖子描述', 1, 1, 100, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (111, 'content.forum/getCateTree', '分类列表', 1, 1, 100, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (112, 'content.comment/index', '评论管理', 1, 1, 4, 1, '', 1, 2, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (113, 'content.comment/list', '评论列表', 1, 1, 112, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (114, 'content.comment/delete', '删除评论', 1, 1, 112, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (115, 'content.comment/check', '审核评论', 1, 1, 112, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (116, 'content.comment/edit', '编辑评论', 1, 1, 112, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (117, 'content.cate/index', '分类管理', 1, 1, 4, 1, '', 1, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (118, 'content.cate/list', '分类列表', 1, 1, 117, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (119, 'content.cate/add', '添加分类', 1, 1, 117, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (120, 'content.cate/edit', '编辑分类', 1, 1, 117, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (121, 'content.cate/delete', '删除分类', 1, 1, 117, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (122, 'content.cate/hot', '热点分类', 1, 1, 117, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (123, 'content.cate/getAppNameView', '分类应用模板', 1, 1, 117, 2, '', 2, 50, '', 0, 0, 0);

-- ----------------------------
-- Table structure for tao_cate
-- ----------------------------
DROP TABLE IF EXISTS `tao_cate`;
CREATE TABLE `tao_cate`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `pid` smallint NOT NULL DEFAULT 0 COMMENT '上级id',
  `catename` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '导航名称',
  `ename` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分类别名',
  `detpl` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '详情模板',
  `icon` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图标',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '状态1启用0禁用',
  `appname` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'index' COMMENT '所属应用',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0帖子1文章',
  `desc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `is_hot` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否是热点',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updata_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `ename`(`ename`) USING BTREE COMMENT '英文名称索引'
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '分类表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tao_cate
-- ----------------------------
INSERT INTO `tao_cate` VALUES (1, 0, '提问', 'ask2', 'ask', 'layui-icon-help', 50, '1', 'index', 0, 'TaoLer社区提问专栏1', 0, 0, 0, 0);
INSERT INTO `tao_cate` VALUES (2, 0, '分享', 'share', 'posts', 'layui-icon-share', 2, '1', 'index', 1, '内容分析', 0, 0, 0, 0);
INSERT INTO `tao_cate` VALUES (3, 0, '讨论', 'talk', 'news', 'layui-icon-dialogue', 3, '1', 'index', 1, '讨论问题', 1, 0, 0, 0);

-- ----------------------------
-- Table structure for tao_collection
-- ----------------------------
DROP TABLE IF EXISTS `tao_collection`;
CREATE TABLE `tao_collection`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键',
  `article_id` int NOT NULL COMMENT '文章id',
  `user_id` int NOT NULL COMMENT '用户id',
  `auther` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '作者',
  `collect_title` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '收藏帖子标题',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章收藏表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_collection
-- ----------------------------

-- ----------------------------
-- Table structure for tao_comment
-- ----------------------------
DROP TABLE IF EXISTS `tao_comment`;
CREATE TABLE `tao_comment`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '评论id',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '评论',
  `article_id` int NOT NULL COMMENT '文章id',
  `user_id` int NOT NULL COMMENT '评论用户',
  `zan` tinyint NOT NULL DEFAULT 0 COMMENT '赞',
  `cai` enum('1','0') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '0求解1采纳',
  `status` enum('0','-1','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '1通过0待审-1禁止',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aiticle_id`(`article_id` ASC) USING BTREE COMMENT '文章评论索引',
  INDEX `user_id`(`user_id` ASC) USING BTREE COMMENT '评论用户索引'
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '评论表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_comment
-- ----------------------------
INSERT INTO `tao_comment` VALUES (1, 'https://www.aieok.com', 1, 1, 0, '0', '1', 1555127897, 1578977505, 0);
INSERT INTO `tao_comment` VALUES (2, 'face[嘻嘻] ddddd', 1, 1, 0, '0', '1', 1677900207, 1677975943, 1677975943);
INSERT INTO `tao_comment` VALUES (3, 'ddddfdfd', 1, 1, 0, '0', '1', 1677900215, 1677975943, 1677975943);

-- ----------------------------
-- Table structure for tao_cunsult
-- ----------------------------
DROP TABLE IF EXISTS `tao_cunsult`;
CREATE TABLE `tao_cunsult`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` tinyint NOT NULL COMMENT '问题类型1问题资讯2提交BUG',
  `title` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '问题',
  `content` tinytext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
  `poster` tinyint NOT NULL COMMENT '发送人ID',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '反馈表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_cunsult
-- ----------------------------

-- ----------------------------
-- Table structure for tao_friend_link
-- ----------------------------
DROP TABLE IF EXISTS `tao_friend_link`;
CREATE TABLE `tao_friend_link`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '友情链接id',
  `linkname` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '链接名称',
  `linksrc` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '链接地址',
  `linkimg` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '链接图片',
  `creat_time` int NOT NULL COMMENT '创建时间',
  `update_time` int NOT NULL COMMENT '更新时间',
  `delete_time` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '友情链接' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_friend_link
-- ----------------------------
INSERT INTO `tao_friend_link` VALUES (1, 'taobao', 'https://www.taobao.com', '', 0, 0, 0);
INSERT INTO `tao_friend_link` VALUES (2, 'baidu', 'https://www.baidu.com', '', 0, 0, 0);
INSERT INTO `tao_friend_link` VALUES (3, 'tensent', 'https://www.qq.com', '', 0, 0, 0);

-- ----------------------------
-- Table structure for tao_mail_server
-- ----------------------------
DROP TABLE IF EXISTS `tao_mail_server`;
CREATE TABLE `tao_mail_server`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `mail` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '邮箱设置',
  `host` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '邮箱服务地址',
  `port` smallint NOT NULL COMMENT '邮箱端口',
  `nickname` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '邮箱密码',
  `active` tinyint(1) NOT NULL DEFAULT 0 COMMENT '邮箱服务1激活0未激活',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '邮件服务器配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_mail_server
-- ----------------------------
INSERT INTO `tao_mail_server` VALUES (1, 'xxxx@aliyun.com', 'smtp.aliyun.com', 25, 'user', '123456', 0, 0);

-- ----------------------------
-- Table structure for tao_message
-- ----------------------------
DROP TABLE IF EXISTS `tao_message`;
CREATE TABLE `tao_message`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '消息ID',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '消息标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '消息内容',
  `user_id` int NOT NULL COMMENT '发送人ID',
  `link` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '链接',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '消息类型0系统消息1普通消息',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '消息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_message
-- ----------------------------
INSERT INTO `tao_message` VALUES (1, '测试后台帖子', '评论通知', 1, 'http://www.tp6.com/index/ask/1.html', 2, 1677900207, 1677900207, 0);
INSERT INTO `tao_message` VALUES (2, '测试后台帖子', '评论通知', 1, 'http://www.tp6.com/index/ask/1.html', 2, 1677900215, 1677900215, 0);

-- ----------------------------
-- Table structure for tao_message_to
-- ----------------------------
DROP TABLE IF EXISTS `tao_message_to`;
CREATE TABLE `tao_message_to`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '消息ID',
  `send_id` int NOT NULL COMMENT '发送人ID',
  `receve_id` int NOT NULL COMMENT '接收人ID',
  `message_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '消息标题',
  `message_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '消息类型0系统消息1普通消息',
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT '消息状态',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '消息详细表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_message_to
-- ----------------------------
INSERT INTO `tao_message_to` VALUES (1, 1, 1, '1', 2, 0, 1677900207, 1677900207, 0);
INSERT INTO `tao_message_to` VALUES (2, 1, 1, '2', 2, 0, 1677900215, 1677900215, 0);

-- ----------------------------
-- Table structure for tao_push_jscode
-- ----------------------------
DROP TABLE IF EXISTS `tao_push_jscode`;
CREATE TABLE `tao_push_jscode`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '平台名',
  `jscode` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'js代码',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1push2taglink',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '站长平台自动推送js代码' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_push_jscode
-- ----------------------------

-- ----------------------------
-- Table structure for tao_slider
-- ----------------------------
DROP TABLE IF EXISTS `tao_slider`;
CREATE TABLE `tao_slider`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `slid_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '幻灯名',
  `slid_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '类型',
  `slid_img` varchar(70) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '幻灯图片地址',
  `slid_href` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '链接',
  `slid_color` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '广告块颜色',
  `slid_start` int NOT NULL DEFAULT 0 COMMENT '开始时间',
  `slid_over` int NOT NULL DEFAULT 0 COMMENT '结束时间',
  `slid_status` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '1投放0仓库',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_slider
-- ----------------------------
INSERT INTO `tao_slider` VALUES (1, 'CODING', 1, '/storage/slider/F1.jpg', '#', '', 1574870400, 1575043200, '1', 0, 0, 0);
INSERT INTO `tao_slider` VALUES (2, '通用右栏底部广告', 2, '/storage/slider/20200101/851c0b88a72590293bcb45454bdce056.jpg', 'https://www.aieok.com', '', 1571155200, 1609344000, '1', 0, 0, 0);

-- ----------------------------
-- Table structure for tao_system
-- ----------------------------
DROP TABLE IF EXISTS `tao_system`;
CREATE TABLE `tao_system`  (
  `id` tinyint NOT NULL AUTO_INCREMENT COMMENT '主键',
  `webname` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '网站名称',
  `webtitle` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `domain` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `template` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '模板',
  `logo` varchar(70) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '网站logo',
  `m_logo` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '/static/res/images/logo-m.png' COMMENT '移动端logo',
  `cache` tinyint NOT NULL DEFAULT 0 COMMENT '缓存时间分钟',
  `upsize` int NOT NULL DEFAULT 0 COMMENT '上传文件大小KB',
  `uptype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '上传文件类型',
  `copyright` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '版权',
  `keywords` tinytext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '网站关键字',
  `descript` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '网站描述',
  `state` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '网站声明',
  `is_open` enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '是否开启站点1开启0关闭',
  `is_comment` enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '是否开启评论1开启0关闭',
  `is_reg` enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '是否开放注册1开启0禁止',
  `icp` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备案',
  `showlist` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '统计代码',
  `blackname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '注册黑名单',
  `sys_version_num` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '系统版本',
  `key` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'key',
  `clevel` tinyint(1) NOT NULL DEFAULT 0,
  `api_url` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'api',
  `base_url` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `upcheck_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '版本检测',
  `upgrade_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '升级地址',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '系统配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_system
-- ----------------------------
INSERT INTO `tao_system` VALUES (1, 'TaoLer', 'TaoLer社区-www.aieok.com', '', 'taoler', '/storage/logo/logo.png', '/static/res/images/logo-m.png', 10, 2048, 'image:png|gif|jpg|jpeg,application:zip|rar|docx,video:mp4,audio:mp3|m4a|mp4|mpeg,zip:zip,application/msword:docx', '<a href=\"https://www.aieok.com\" target=\"_blank\">TaoLer</a>', 'TaoLer,轻社区系统,bbs,论坛,Thinkphp6,layui,fly模板,', '这是一个Taoler轻社区论坛系统.', '网站声明：<br>\n1.本站使用TaoLerCMS驱动，简单好用，深度SEO。<br>\n2.内容为用户提供如有侵权，请联系本站管理员删除。<br>\n3.原创内容转载请注明出处，否则一切后果自行承担。<br>', '1', '1', '1', '0.0.0.0-1', '', '管理员|admin|审核员|超级|垃圾', '1.6.3', '', 0, 'http://api.aieok.com', 'http://api.aieok.com/v1/cy', 'http://api.aieok.com/v1/upload/check', 'http://api.aieok.com/v1/upload/api', 1641004619, 1678515271);

-- ----------------------------
-- Table structure for tao_tag
-- ----------------------------
DROP TABLE IF EXISTS `tao_tag`;
CREATE TABLE `tao_tag`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'tag自增id',
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称',
  `ename` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '英文名',
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '关键词',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '摘要',
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标签',
  `create_time` int NOT NULL COMMENT '创建时间',
  `update_time` int NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `ename`(`ename` ASC) USING BTREE COMMENT 'ename查询tag索引'
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章tag表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_tag
-- ----------------------------

-- ----------------------------
-- Table structure for tao_taglist
-- ----------------------------
DROP TABLE IF EXISTS `tao_taglist`;
CREATE TABLE `tao_taglist`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '标签列表id',
  `tag_id` int NOT NULL COMMENT '标签id',
  `article_id` int NOT NULL COMMENT '文章id',
  `create_time` int NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `tag_id`(`tag_id` ASC) USING BTREE COMMENT 'tagID索引',
  INDEX `article_id`(`article_id` ASC) USING BTREE COMMENT '文章ID查询索引'
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'tag详细列表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_taglist
-- ----------------------------
INSERT INTO `tao_taglist` VALUES (1, 0, 1, 1677847332);

-- ----------------------------
-- Table structure for tao_user
-- ----------------------------
DROP TABLE IF EXISTS `tao_user`;
CREATE TABLE `tao_user`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `name` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名',
  `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
  `phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机',
  `email` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `active` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1激活账户0未激活',
  `nickname` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `city` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '归属地',
  `sex` enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '性别0男1女',
  `sign` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '签名',
  `user_img` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '头像',
  `auth` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '管理员权限0普通1超级',
  `point` int NOT NULL DEFAULT 0 COMMENT '积分',
  `area_id` int NULL DEFAULT NULL COMMENT '用户所属区域ID',
  `status` enum('1','0','-1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '1启用0待审-1禁用',
  `vip` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'vip',
  `last_login_ip` varchar(70) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '最后登陆ip',
  `last_login_time` int NOT NULL DEFAULT 0 COMMENT '最后登陆时间',
  `login_error_num` tinyint(1) NOT NULL DEFAULT 0 COMMENT '登陆错误次数',
  `login_error_time` int NOT NULL DEFAULT 0 COMMENT '登陆错误时间',
  `login_error_lock` tinyint(1) NOT NULL DEFAULT 0 COMMENT '登陆锁定0正常1锁定',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '修改时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE COMMENT '用户名查询用户索引',
  INDEX `email`(`email`) USING BTREE COMMENT 'email查询用户索引'
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_user
-- ----------------------------
INSERT INTO `tao_user` VALUES (1, 'admin', '95d6f8d0d0c3b45e5dbe4057da1b149e', '2147483647', 'admin@qq.com', 0, '管理员', 'earth', '1', '这是一个社区系统', '/static/res/images/avatar/00.jpg', '1', 0, 1, '1', 0, '127.0.0.1', 1677900186, 0, 0, 0, 1579053025, 1677900186, 0);

-- ----------------------------
-- Table structure for tao_user_area
-- ----------------------------
DROP TABLE IF EXISTS `tao_user_area`;
CREATE TABLE `tao_user_area`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `area` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '所属区域',
  `asing` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '区域简称',
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  `delete_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_user_area
-- ----------------------------
INSERT INTO `tao_user_area` VALUES (1, '北京', '京', 0, 0, 0);
INSERT INTO `tao_user_area` VALUES (2, '上海', '沪', 0, 0, 0);
INSERT INTO `tao_user_area` VALUES (3, '广州', '广', 0, 0, 0);
INSERT INTO `tao_user_area` VALUES (4, '深圳', '深', 0, 0, 0);

-- ----------------------------
-- Table structure for tao_user_sign
-- ----------------------------
DROP TABLE IF EXISTS `tao_user_sign`;
CREATE TABLE `tao_user_sign`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int UNSIGNED NOT NULL COMMENT '用户id',
  `days` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '连续签到的天数',
  `is_share` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否分享过',
  `is_sign` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否签到过',
  `stime` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '签到的时间',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户签到表' ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of tao_user_sign
-- ----------------------------

-- ----------------------------
-- Table structure for tao_user_signrule
-- ----------------------------
DROP TABLE IF EXISTS `tao_user_signrule`;
CREATE TABLE `tao_user_signrule`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `days` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '连续天数',
  `score` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '升级时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户签到积分规则' ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of tao_user_signrule
-- ----------------------------
INSERT INTO `tao_user_signrule` VALUES (1, 1, 2, 0, 0, 0);
INSERT INTO `tao_user_signrule` VALUES (2, 3, 3, 0, 0, 0);
INSERT INTO `tao_user_signrule` VALUES (3, 5, 6, 0, 1677824168, 0);
INSERT INTO `tao_user_signrule` VALUES (4, 7, 10, 1677824262, 1677824262, 0);

-- ----------------------------
-- Table structure for tao_user_viprule
-- ----------------------------
DROP TABLE IF EXISTS `tao_user_viprule`;
CREATE TABLE `tao_user_viprule`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '用户等级ID',
  `score` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '积分区间',
  `vip` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'vip等级',
  `nick` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '认证昵称',
  `rules` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '权限',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '升级时间',
  `delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_user_viprule
-- ----------------------------
INSERT INTO `tao_user_viprule` VALUES (1, '0-99', 0, '游民', '0', 1585476523, 1585544577, 0);
INSERT INTO `tao_user_viprule` VALUES (2, '100-299', 1, '富农', '1', 1585476551, 1677823895, 0);
INSERT INTO `tao_user_viprule` VALUES (3, '300-500', 2, '地主', '0', 1585545450, 1585546241, 0);
INSERT INTO `tao_user_viprule` VALUES (4, '501-699', 3, '土豪', '0', 1585545542, 1585569657, 0);
INSERT INTO `tao_user_viprule` VALUES (5, '700-899', 4, '霸主', '0', 1677824242, 1677824242, 0);
INSERT INTO `tao_user_viprule` VALUES (6, '900-1000', 5, '王爷', '0', 1677824859, 1677824859, 0);

-- ----------------------------
-- Table structure for tao_user_zan
-- ----------------------------
DROP TABLE IF EXISTS `tao_user_zan`;
CREATE TABLE `tao_user_zan`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '点赞主键id',
  `article_id` int NULL DEFAULT NULL COMMENT '文章id',
  `comment_id` int NOT NULL COMMENT '评论id',
  `user_id` int NOT NULL COMMENT '用户id',
  `type` tinyint NOT NULL DEFAULT 2 COMMENT '1文章点赞2评论点赞',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '点赞时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of tao_user_zan
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
