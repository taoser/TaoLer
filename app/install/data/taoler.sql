/*
 Navicat Premium Data Transfer

 Target Server Type    : MySQL
 Target Server Version : 80020 (8.0.20)
 File Encoding         : 65001

 Date: 8/06/2023 19:57:43
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
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '软删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_admin
-- ----------------------------
INSERT INTO `tao_admin` VALUES (1, 'admin', '管理员', '95d6f8d0d0c3b45e5dbe4057da1b149e', 'taoler@qq.com', '13812345678', 1, 'static/admin/images/avatar.jpg', 1, 1, '2021 TaoLer！', '127.0.0.1', 1678677599, 1579053025, 1578986600, 0);
INSERT INTO `tao_admin` VALUES (2, 'test', '测试账号', '95d6f8d0d0c3b45e5dbe4057da1b149e', 'test1@qq.com', '13012345678', 0, 'static/admin/images/avatar.jpg', 1, 0, '', '127.0.0.1', 1678428810, 1579053025, 1678418924, 0);

-- ----------------------------
-- Table structure for tao_area
-- ----------------------------
CREATE TABLE `tao_area`  (
  `id` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` smallint UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `parent_id`(`parent_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4069 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_area
-- ----------------------------
INSERT INTO `tao_area` VALUES (1, 0, '北京');
INSERT INTO `tao_area` VALUES (2, 0, '天津');
INSERT INTO `tao_area` VALUES (3, 0, '河北省');
INSERT INTO `tao_area` VALUES (4, 0, '山西省');
INSERT INTO `tao_area` VALUES (5, 0, '内蒙古自治区');
INSERT INTO `tao_area` VALUES (6, 0, '辽宁省');
INSERT INTO `tao_area` VALUES (7, 0, '吉林省');
INSERT INTO `tao_area` VALUES (8, 0, '黑龙江省');
INSERT INTO `tao_area` VALUES (9, 0, '上海');
INSERT INTO `tao_area` VALUES (10, 0, '江苏省');
INSERT INTO `tao_area` VALUES (11, 0, '浙江省');
INSERT INTO `tao_area` VALUES (12, 0, '安徽省');
INSERT INTO `tao_area` VALUES (13, 0, '福建省');
INSERT INTO `tao_area` VALUES (14, 0, '江西省');
INSERT INTO `tao_area` VALUES (15, 0, '山东省');
INSERT INTO `tao_area` VALUES (16, 0, '河南省');
INSERT INTO `tao_area` VALUES (17, 0, '湖北省');
INSERT INTO `tao_area` VALUES (18, 0, '湖南省');
INSERT INTO `tao_area` VALUES (19, 0, '广东省');
INSERT INTO `tao_area` VALUES (20, 0, '广西壮族自治区');
INSERT INTO `tao_area` VALUES (21, 0, '海南省');
INSERT INTO `tao_area` VALUES (22, 0, '重庆');
INSERT INTO `tao_area` VALUES (23, 0, '四川省');
INSERT INTO `tao_area` VALUES (24, 0, '贵州省');
INSERT INTO `tao_area` VALUES (25, 0, '云南省');
INSERT INTO `tao_area` VALUES (26, 0, '西藏自治区');
INSERT INTO `tao_area` VALUES (27, 0, '陕西省');
INSERT INTO `tao_area` VALUES (28, 0, '甘肃省');
INSERT INTO `tao_area` VALUES (29, 0, '青海省');
INSERT INTO `tao_area` VALUES (30, 0, '宁夏回族自治区');
INSERT INTO `tao_area` VALUES (31, 0, '新疆维吾尔自治区');
INSERT INTO `tao_area` VALUES (32, 0, '台湾');
INSERT INTO `tao_area` VALUES (33, 0, '香港特别行政区');
INSERT INTO `tao_area` VALUES (34, 0, '澳门特别行政区');
INSERT INTO `tao_area` VALUES (35, 0, '海外');
INSERT INTO `tao_area` VALUES (36, 1, '北京市');
INSERT INTO `tao_area` VALUES (37, 2, '天津市');
INSERT INTO `tao_area` VALUES (38, 3, '石家庄市');
INSERT INTO `tao_area` VALUES (39, 3, '唐山市');
INSERT INTO `tao_area` VALUES (40, 3, '秦皇岛市');
INSERT INTO `tao_area` VALUES (41, 3, '邯郸市');
INSERT INTO `tao_area` VALUES (42, 3, '邢台市');
INSERT INTO `tao_area` VALUES (43, 3, '保定市');
INSERT INTO `tao_area` VALUES (44, 3, '张家口市');
INSERT INTO `tao_area` VALUES (45, 3, '承德市');
INSERT INTO `tao_area` VALUES (46, 3, '沧州市');
INSERT INTO `tao_area` VALUES (47, 3, '廊坊市');
INSERT INTO `tao_area` VALUES (48, 3, '衡水市');
INSERT INTO `tao_area` VALUES (49, 4, '太原市');
INSERT INTO `tao_area` VALUES (50, 4, '大同市');
INSERT INTO `tao_area` VALUES (51, 4, '阳泉市');
INSERT INTO `tao_area` VALUES (52, 4, '长治市');
INSERT INTO `tao_area` VALUES (53, 4, '晋城市');
INSERT INTO `tao_area` VALUES (54, 4, '朔州市');
INSERT INTO `tao_area` VALUES (55, 4, '晋中市');
INSERT INTO `tao_area` VALUES (56, 4, '运城市');
INSERT INTO `tao_area` VALUES (57, 4, '忻州市');
INSERT INTO `tao_area` VALUES (58, 4, '临汾市');
INSERT INTO `tao_area` VALUES (59, 4, '吕梁市');
INSERT INTO `tao_area` VALUES (60, 5, '呼和浩特市');
INSERT INTO `tao_area` VALUES (61, 5, '包头市');
INSERT INTO `tao_area` VALUES (62, 5, '乌海市');
INSERT INTO `tao_area` VALUES (63, 5, '赤峰市');
INSERT INTO `tao_area` VALUES (64, 5, '通辽市');
INSERT INTO `tao_area` VALUES (65, 5, '鄂尔多斯市');
INSERT INTO `tao_area` VALUES (66, 5, '呼伦贝尔市');
INSERT INTO `tao_area` VALUES (67, 5, '巴彦淖尔市');
INSERT INTO `tao_area` VALUES (68, 5, '乌兰察布市');
INSERT INTO `tao_area` VALUES (69, 5, '兴安盟');
INSERT INTO `tao_area` VALUES (70, 5, '锡林郭勒盟');
INSERT INTO `tao_area` VALUES (71, 5, '阿拉善盟');
INSERT INTO `tao_area` VALUES (72, 6, '沈阳市');
INSERT INTO `tao_area` VALUES (73, 6, '大连市');
INSERT INTO `tao_area` VALUES (74, 6, '鞍山市');
INSERT INTO `tao_area` VALUES (75, 6, '抚顺市');
INSERT INTO `tao_area` VALUES (76, 6, '本溪市');
INSERT INTO `tao_area` VALUES (77, 6, '丹东市');
INSERT INTO `tao_area` VALUES (78, 6, '锦州市');
INSERT INTO `tao_area` VALUES (79, 6, '营口市');
INSERT INTO `tao_area` VALUES (80, 6, '阜新市');
INSERT INTO `tao_area` VALUES (81, 6, '辽阳市');
INSERT INTO `tao_area` VALUES (82, 6, '盘锦市');
INSERT INTO `tao_area` VALUES (83, 6, '铁岭市');
INSERT INTO `tao_area` VALUES (84, 6, '朝阳市');
INSERT INTO `tao_area` VALUES (85, 6, '葫芦岛市');
INSERT INTO `tao_area` VALUES (86, 7, '长春市');
INSERT INTO `tao_area` VALUES (87, 7, '吉林市');
INSERT INTO `tao_area` VALUES (88, 7, '四平市');
INSERT INTO `tao_area` VALUES (89, 7, '辽源市');
INSERT INTO `tao_area` VALUES (90, 7, '通化市');
INSERT INTO `tao_area` VALUES (91, 7, '白山市');
INSERT INTO `tao_area` VALUES (92, 7, '松原市');
INSERT INTO `tao_area` VALUES (93, 7, '白城市');
INSERT INTO `tao_area` VALUES (94, 7, '延边朝鲜族自治州');
INSERT INTO `tao_area` VALUES (95, 8, '哈尔滨市');
INSERT INTO `tao_area` VALUES (96, 8, '齐齐哈尔市');
INSERT INTO `tao_area` VALUES (97, 8, '鸡西市');
INSERT INTO `tao_area` VALUES (98, 8, '鹤岗市');
INSERT INTO `tao_area` VALUES (99, 8, '双鸭山市');
INSERT INTO `tao_area` VALUES (100, 8, '大庆市');
INSERT INTO `tao_area` VALUES (101, 8, '伊春市');
INSERT INTO `tao_area` VALUES (102, 8, '佳木斯市');
INSERT INTO `tao_area` VALUES (103, 8, '七台河市');
INSERT INTO `tao_area` VALUES (104, 8, '牡丹江市');
INSERT INTO `tao_area` VALUES (105, 8, '黑河市');
INSERT INTO `tao_area` VALUES (106, 8, '绥化市');
INSERT INTO `tao_area` VALUES (107, 8, '大兴安岭地区');
INSERT INTO `tao_area` VALUES (108, 9, '上海市');
INSERT INTO `tao_area` VALUES (109, 10, '南京市');
INSERT INTO `tao_area` VALUES (110, 10, '无锡市');
INSERT INTO `tao_area` VALUES (111, 10, '徐州市');
INSERT INTO `tao_area` VALUES (112, 10, '常州市');
INSERT INTO `tao_area` VALUES (113, 10, '苏州市');
INSERT INTO `tao_area` VALUES (114, 10, '南通市');
INSERT INTO `tao_area` VALUES (115, 10, '连云港市');
INSERT INTO `tao_area` VALUES (116, 10, '淮安市');
INSERT INTO `tao_area` VALUES (117, 10, '盐城市');
INSERT INTO `tao_area` VALUES (118, 10, '扬州市');
INSERT INTO `tao_area` VALUES (119, 10, '镇江市');
INSERT INTO `tao_area` VALUES (120, 10, '泰州市');
INSERT INTO `tao_area` VALUES (121, 10, '宿迁市');
INSERT INTO `tao_area` VALUES (122, 11, '杭州市');
INSERT INTO `tao_area` VALUES (123, 11, '宁波市');
INSERT INTO `tao_area` VALUES (124, 11, '温州市');
INSERT INTO `tao_area` VALUES (125, 11, '嘉兴市');
INSERT INTO `tao_area` VALUES (126, 11, '湖州市');
INSERT INTO `tao_area` VALUES (127, 11, '绍兴市');
INSERT INTO `tao_area` VALUES (128, 11, '金华市');
INSERT INTO `tao_area` VALUES (129, 11, '衢州市');
INSERT INTO `tao_area` VALUES (130, 11, '舟山市');
INSERT INTO `tao_area` VALUES (131, 11, '台州市');
INSERT INTO `tao_area` VALUES (132, 11, '丽水市');
INSERT INTO `tao_area` VALUES (133, 12, '合肥市');
INSERT INTO `tao_area` VALUES (134, 12, '芜湖市');
INSERT INTO `tao_area` VALUES (135, 12, '蚌埠市');
INSERT INTO `tao_area` VALUES (136, 12, '淮南市');
INSERT INTO `tao_area` VALUES (137, 12, '马鞍山市');
INSERT INTO `tao_area` VALUES (138, 12, '淮北市');
INSERT INTO `tao_area` VALUES (139, 12, '铜陵市');
INSERT INTO `tao_area` VALUES (140, 12, '安庆市');
INSERT INTO `tao_area` VALUES (141, 12, '黄山市');
INSERT INTO `tao_area` VALUES (142, 12, '滁州市');
INSERT INTO `tao_area` VALUES (143, 12, '阜阳市');
INSERT INTO `tao_area` VALUES (144, 12, '宿州市');
INSERT INTO `tao_area` VALUES (145, 12, '六安市');
INSERT INTO `tao_area` VALUES (146, 12, '亳州市');
INSERT INTO `tao_area` VALUES (147, 12, '池州市');
INSERT INTO `tao_area` VALUES (148, 12, '宣城市');
INSERT INTO `tao_area` VALUES (149, 13, '福州市');
INSERT INTO `tao_area` VALUES (150, 13, '厦门市');
INSERT INTO `tao_area` VALUES (151, 13, '莆田市');
INSERT INTO `tao_area` VALUES (152, 13, '三明市');
INSERT INTO `tao_area` VALUES (153, 13, '泉州市');
INSERT INTO `tao_area` VALUES (154, 13, '漳州市');
INSERT INTO `tao_area` VALUES (155, 13, '南平市');
INSERT INTO `tao_area` VALUES (156, 13, '龙岩市');
INSERT INTO `tao_area` VALUES (157, 13, '宁德市');
INSERT INTO `tao_area` VALUES (158, 14, '南昌市');
INSERT INTO `tao_area` VALUES (159, 14, '景德镇市');
INSERT INTO `tao_area` VALUES (160, 14, '萍乡市');
INSERT INTO `tao_area` VALUES (161, 14, '九江市');
INSERT INTO `tao_area` VALUES (162, 14, '新余市');
INSERT INTO `tao_area` VALUES (163, 14, '鹰潭市');
INSERT INTO `tao_area` VALUES (164, 14, '赣州市');
INSERT INTO `tao_area` VALUES (165, 14, '吉安市');
INSERT INTO `tao_area` VALUES (166, 14, '宜春市');
INSERT INTO `tao_area` VALUES (167, 14, '抚州市');
INSERT INTO `tao_area` VALUES (168, 14, '上饶市');
INSERT INTO `tao_area` VALUES (169, 15, '济南市');
INSERT INTO `tao_area` VALUES (170, 15, '青岛市');
INSERT INTO `tao_area` VALUES (171, 15, '淄博市');
INSERT INTO `tao_area` VALUES (172, 15, '枣庄市');
INSERT INTO `tao_area` VALUES (173, 15, '东营市');
INSERT INTO `tao_area` VALUES (174, 15, '烟台市');
INSERT INTO `tao_area` VALUES (175, 15, '潍坊市');
INSERT INTO `tao_area` VALUES (176, 15, '济宁市');
INSERT INTO `tao_area` VALUES (177, 15, '泰安市');
INSERT INTO `tao_area` VALUES (178, 15, '威海市');
INSERT INTO `tao_area` VALUES (179, 15, '日照市');
INSERT INTO `tao_area` VALUES (180, 15, '莱芜市');
INSERT INTO `tao_area` VALUES (181, 15, '临沂市');
INSERT INTO `tao_area` VALUES (182, 15, '德州市');
INSERT INTO `tao_area` VALUES (183, 15, '聊城市');
INSERT INTO `tao_area` VALUES (184, 15, '滨州市');
INSERT INTO `tao_area` VALUES (185, 15, '菏泽市');
INSERT INTO `tao_area` VALUES (186, 16, '郑州市');
INSERT INTO `tao_area` VALUES (187, 16, '开封市');
INSERT INTO `tao_area` VALUES (188, 16, '洛阳市');
INSERT INTO `tao_area` VALUES (189, 16, '平顶山市');
INSERT INTO `tao_area` VALUES (190, 16, '安阳市');
INSERT INTO `tao_area` VALUES (191, 16, '鹤壁市');
INSERT INTO `tao_area` VALUES (192, 16, '新乡市');
INSERT INTO `tao_area` VALUES (193, 16, '焦作市');
INSERT INTO `tao_area` VALUES (194, 16, '濮阳市');
INSERT INTO `tao_area` VALUES (195, 16, '许昌市');
INSERT INTO `tao_area` VALUES (196, 16, '漯河市');
INSERT INTO `tao_area` VALUES (197, 16, '三门峡市');
INSERT INTO `tao_area` VALUES (198, 16, '南阳市');
INSERT INTO `tao_area` VALUES (199, 16, '商丘市');
INSERT INTO `tao_area` VALUES (200, 16, '信阳市');
INSERT INTO `tao_area` VALUES (201, 16, '周口市');
INSERT INTO `tao_area` VALUES (202, 16, '驻马店市');
INSERT INTO `tao_area` VALUES (203, 17, '武汉市');
INSERT INTO `tao_area` VALUES (204, 17, '黄石市');
INSERT INTO `tao_area` VALUES (205, 17, '十堰市');
INSERT INTO `tao_area` VALUES (206, 17, '宜昌市');
INSERT INTO `tao_area` VALUES (207, 17, '襄阳市');
INSERT INTO `tao_area` VALUES (208, 17, '鄂州市');
INSERT INTO `tao_area` VALUES (209, 17, '荆门市');
INSERT INTO `tao_area` VALUES (210, 17, '孝感市');
INSERT INTO `tao_area` VALUES (211, 17, '荆州市');
INSERT INTO `tao_area` VALUES (212, 17, '黄冈市');
INSERT INTO `tao_area` VALUES (213, 17, '咸宁市');
INSERT INTO `tao_area` VALUES (214, 17, '随州市');
INSERT INTO `tao_area` VALUES (215, 17, '恩施土家族苗族自治州');
INSERT INTO `tao_area` VALUES (216, 18, '长沙市');
INSERT INTO `tao_area` VALUES (217, 18, '株洲市');
INSERT INTO `tao_area` VALUES (218, 18, '湘潭市');
INSERT INTO `tao_area` VALUES (219, 18, '衡阳市');
INSERT INTO `tao_area` VALUES (220, 18, '邵阳市');
INSERT INTO `tao_area` VALUES (221, 18, '岳阳市');
INSERT INTO `tao_area` VALUES (222, 18, '常德市');
INSERT INTO `tao_area` VALUES (223, 18, '张家界市');
INSERT INTO `tao_area` VALUES (224, 18, '益阳市');
INSERT INTO `tao_area` VALUES (225, 18, '郴州市');
INSERT INTO `tao_area` VALUES (226, 18, '永州市');
INSERT INTO `tao_area` VALUES (227, 18, '怀化市');
INSERT INTO `tao_area` VALUES (228, 18, '娄底市');
INSERT INTO `tao_area` VALUES (229, 18, '湘西土家族苗族自治州');
INSERT INTO `tao_area` VALUES (230, 19, '广州市');
INSERT INTO `tao_area` VALUES (231, 19, '韶关市');
INSERT INTO `tao_area` VALUES (232, 19, '深圳市');
INSERT INTO `tao_area` VALUES (233, 19, '珠海市');
INSERT INTO `tao_area` VALUES (234, 19, '汕头市');
INSERT INTO `tao_area` VALUES (235, 19, '佛山市');
INSERT INTO `tao_area` VALUES (236, 19, '江门市');
INSERT INTO `tao_area` VALUES (237, 19, '湛江市');
INSERT INTO `tao_area` VALUES (238, 19, '茂名市');
INSERT INTO `tao_area` VALUES (239, 19, '肇庆市');
INSERT INTO `tao_area` VALUES (240, 19, '惠州市');
INSERT INTO `tao_area` VALUES (241, 19, '梅州市');
INSERT INTO `tao_area` VALUES (242, 19, '汕尾市');
INSERT INTO `tao_area` VALUES (243, 19, '河源市');
INSERT INTO `tao_area` VALUES (244, 19, '阳江市');
INSERT INTO `tao_area` VALUES (245, 19, '清远市');
INSERT INTO `tao_area` VALUES (246, 19, '东莞市');
INSERT INTO `tao_area` VALUES (247, 19, '中山市');
INSERT INTO `tao_area` VALUES (248, 19, '东沙群岛');
INSERT INTO `tao_area` VALUES (249, 19, '潮州市');
INSERT INTO `tao_area` VALUES (250, 19, '揭阳市');
INSERT INTO `tao_area` VALUES (251, 19, '云浮市');
INSERT INTO `tao_area` VALUES (252, 20, '南宁市');
INSERT INTO `tao_area` VALUES (253, 20, '柳州市');
INSERT INTO `tao_area` VALUES (254, 20, '桂林市');
INSERT INTO `tao_area` VALUES (255, 20, '梧州市');
INSERT INTO `tao_area` VALUES (256, 20, '北海市');
INSERT INTO `tao_area` VALUES (257, 20, '防城港市');
INSERT INTO `tao_area` VALUES (258, 20, '钦州市');
INSERT INTO `tao_area` VALUES (259, 20, '贵港市');
INSERT INTO `tao_area` VALUES (260, 20, '玉林市');
INSERT INTO `tao_area` VALUES (261, 20, '百色市');
INSERT INTO `tao_area` VALUES (262, 20, '贺州市');
INSERT INTO `tao_area` VALUES (263, 20, '河池市');
INSERT INTO `tao_area` VALUES (264, 20, '来宾市');
INSERT INTO `tao_area` VALUES (265, 20, '崇左市');
INSERT INTO `tao_area` VALUES (266, 21, '海口市');
INSERT INTO `tao_area` VALUES (267, 21, '三亚市');
INSERT INTO `tao_area` VALUES (268, 21, '三沙市');
INSERT INTO `tao_area` VALUES (269, 22, '重庆市');
INSERT INTO `tao_area` VALUES (270, 23, '成都市');
INSERT INTO `tao_area` VALUES (271, 23, '自贡市');
INSERT INTO `tao_area` VALUES (272, 23, '攀枝花市');
INSERT INTO `tao_area` VALUES (273, 23, '泸州市');
INSERT INTO `tao_area` VALUES (274, 23, '德阳市');
INSERT INTO `tao_area` VALUES (275, 23, '绵阳市');
INSERT INTO `tao_area` VALUES (276, 23, '广元市');
INSERT INTO `tao_area` VALUES (277, 23, '遂宁市');
INSERT INTO `tao_area` VALUES (278, 23, '内江市');
INSERT INTO `tao_area` VALUES (279, 23, '乐山市');
INSERT INTO `tao_area` VALUES (280, 23, '南充市');
INSERT INTO `tao_area` VALUES (281, 23, '眉山市');
INSERT INTO `tao_area` VALUES (282, 23, '宜宾市');
INSERT INTO `tao_area` VALUES (283, 23, '广安市');
INSERT INTO `tao_area` VALUES (284, 23, '达州市');
INSERT INTO `tao_area` VALUES (285, 23, '雅安市');
INSERT INTO `tao_area` VALUES (286, 23, '巴中市');
INSERT INTO `tao_area` VALUES (287, 23, '资阳市');
INSERT INTO `tao_area` VALUES (288, 23, '阿坝藏族羌族自治州');
INSERT INTO `tao_area` VALUES (289, 23, '甘孜藏族自治州');
INSERT INTO `tao_area` VALUES (290, 23, '凉山彝族自治州');
INSERT INTO `tao_area` VALUES (291, 24, '贵阳市');
INSERT INTO `tao_area` VALUES (292, 24, '六盘水市');
INSERT INTO `tao_area` VALUES (293, 24, '遵义市');
INSERT INTO `tao_area` VALUES (294, 24, '安顺市');
INSERT INTO `tao_area` VALUES (295, 24, '铜仁市');
INSERT INTO `tao_area` VALUES (296, 24, '黔西南布依族苗族自治州');
INSERT INTO `tao_area` VALUES (297, 24, '毕节市');
INSERT INTO `tao_area` VALUES (298, 24, '黔东南苗族侗族自治州');
INSERT INTO `tao_area` VALUES (299, 24, '黔南布依族苗族自治州');
INSERT INTO `tao_area` VALUES (300, 25, '昆明市');
INSERT INTO `tao_area` VALUES (301, 25, '曲靖市');
INSERT INTO `tao_area` VALUES (302, 25, '玉溪市');
INSERT INTO `tao_area` VALUES (303, 25, '保山市');
INSERT INTO `tao_area` VALUES (304, 25, '昭通市');
INSERT INTO `tao_area` VALUES (305, 25, '丽江市');
INSERT INTO `tao_area` VALUES (306, 25, '普洱市');
INSERT INTO `tao_area` VALUES (307, 25, '临沧市');
INSERT INTO `tao_area` VALUES (308, 25, '楚雄彝族自治州');
INSERT INTO `tao_area` VALUES (309, 25, '红河哈尼族彝族自治州');
INSERT INTO `tao_area` VALUES (310, 25, '文山壮族苗族自治州');
INSERT INTO `tao_area` VALUES (311, 25, '西双版纳傣族自治州');
INSERT INTO `tao_area` VALUES (312, 25, '大理白族自治州');
INSERT INTO `tao_area` VALUES (313, 25, '德宏傣族景颇族自治州');
INSERT INTO `tao_area` VALUES (314, 25, '怒江傈僳族自治州');
INSERT INTO `tao_area` VALUES (315, 25, '迪庆藏族自治州');
INSERT INTO `tao_area` VALUES (316, 26, '拉萨市');
INSERT INTO `tao_area` VALUES (317, 26, '昌都市');
INSERT INTO `tao_area` VALUES (318, 26, '山南地区');
INSERT INTO `tao_area` VALUES (319, 26, '日喀则市');
INSERT INTO `tao_area` VALUES (320, 26, '那曲地区');
INSERT INTO `tao_area` VALUES (321, 26, '阿里地区');
INSERT INTO `tao_area` VALUES (322, 26, '林芝市');
INSERT INTO `tao_area` VALUES (323, 27, '西安市');
INSERT INTO `tao_area` VALUES (324, 27, '铜川市');
INSERT INTO `tao_area` VALUES (325, 27, '宝鸡市');
INSERT INTO `tao_area` VALUES (326, 27, '咸阳市');
INSERT INTO `tao_area` VALUES (327, 27, '渭南市');
INSERT INTO `tao_area` VALUES (328, 27, '延安市');
INSERT INTO `tao_area` VALUES (329, 27, '汉中市');
INSERT INTO `tao_area` VALUES (330, 27, '榆林市');
INSERT INTO `tao_area` VALUES (331, 27, '安康市');
INSERT INTO `tao_area` VALUES (332, 27, '商洛市');
INSERT INTO `tao_area` VALUES (333, 28, '兰州市');
INSERT INTO `tao_area` VALUES (334, 28, '嘉峪关市');
INSERT INTO `tao_area` VALUES (335, 28, '金昌市');
INSERT INTO `tao_area` VALUES (336, 28, '白银市');
INSERT INTO `tao_area` VALUES (337, 28, '天水市');
INSERT INTO `tao_area` VALUES (338, 28, '武威市');
INSERT INTO `tao_area` VALUES (339, 28, '张掖市');
INSERT INTO `tao_area` VALUES (340, 28, '平凉市');
INSERT INTO `tao_area` VALUES (341, 28, '酒泉市');
INSERT INTO `tao_area` VALUES (342, 28, '庆阳市');
INSERT INTO `tao_area` VALUES (343, 28, '定西市');
INSERT INTO `tao_area` VALUES (344, 28, '陇南市');
INSERT INTO `tao_area` VALUES (345, 28, '临夏回族自治州');
INSERT INTO `tao_area` VALUES (346, 28, '甘南藏族自治州');
INSERT INTO `tao_area` VALUES (347, 29, '西宁市');
INSERT INTO `tao_area` VALUES (348, 29, '海东市');
INSERT INTO `tao_area` VALUES (349, 29, '海北藏族自治州');
INSERT INTO `tao_area` VALUES (350, 29, '黄南藏族自治州');
INSERT INTO `tao_area` VALUES (351, 29, '海南藏族自治州');
INSERT INTO `tao_area` VALUES (352, 29, '果洛藏族自治州');
INSERT INTO `tao_area` VALUES (353, 29, '玉树藏族自治州');
INSERT INTO `tao_area` VALUES (354, 29, '海西蒙古族藏族自治州');
INSERT INTO `tao_area` VALUES (355, 30, '银川市');
INSERT INTO `tao_area` VALUES (356, 30, '石嘴山市');
INSERT INTO `tao_area` VALUES (357, 30, '吴忠市');
INSERT INTO `tao_area` VALUES (358, 30, '固原市');
INSERT INTO `tao_area` VALUES (359, 30, '中卫市');
INSERT INTO `tao_area` VALUES (360, 31, '乌鲁木齐市');
INSERT INTO `tao_area` VALUES (361, 31, '克拉玛依市');
INSERT INTO `tao_area` VALUES (362, 31, '吐鲁番市');
INSERT INTO `tao_area` VALUES (363, 31, '哈密地区');
INSERT INTO `tao_area` VALUES (364, 31, '昌吉回族自治州');
INSERT INTO `tao_area` VALUES (365, 31, '博尔塔拉蒙古自治州');
INSERT INTO `tao_area` VALUES (366, 31, '巴音郭楞蒙古自治州');
INSERT INTO `tao_area` VALUES (367, 31, '阿克苏地区');
INSERT INTO `tao_area` VALUES (368, 31, '克孜勒苏柯尔克孜自治州');
INSERT INTO `tao_area` VALUES (369, 31, '喀什地区');
INSERT INTO `tao_area` VALUES (370, 31, '和田地区');
INSERT INTO `tao_area` VALUES (371, 31, '伊犁哈萨克自治州');
INSERT INTO `tao_area` VALUES (372, 31, '塔城地区');
INSERT INTO `tao_area` VALUES (373, 31, '阿勒泰地区');
INSERT INTO `tao_area` VALUES (374, 32, '台北市');
INSERT INTO `tao_area` VALUES (375, 32, '高雄市');
INSERT INTO `tao_area` VALUES (376, 32, '台南市');
INSERT INTO `tao_area` VALUES (377, 32, '台中市');
INSERT INTO `tao_area` VALUES (378, 32, '金门县');
INSERT INTO `tao_area` VALUES (379, 32, '南投县');
INSERT INTO `tao_area` VALUES (380, 32, '基隆市');
INSERT INTO `tao_area` VALUES (381, 32, '新竹市');
INSERT INTO `tao_area` VALUES (382, 32, '嘉义市');
INSERT INTO `tao_area` VALUES (383, 32, '新北市');
INSERT INTO `tao_area` VALUES (384, 32, '宜兰县');
INSERT INTO `tao_area` VALUES (385, 32, '新竹县');
INSERT INTO `tao_area` VALUES (386, 32, '桃园县');
INSERT INTO `tao_area` VALUES (387, 32, '苗栗县');
INSERT INTO `tao_area` VALUES (388, 32, '彰化县');
INSERT INTO `tao_area` VALUES (389, 32, '嘉义县');
INSERT INTO `tao_area` VALUES (390, 32, '云林县');
INSERT INTO `tao_area` VALUES (391, 32, '屏东县');
INSERT INTO `tao_area` VALUES (392, 32, '台东县');
INSERT INTO `tao_area` VALUES (393, 32, '花莲县');
INSERT INTO `tao_area` VALUES (394, 32, '澎湖县');
INSERT INTO `tao_area` VALUES (395, 32, '连江县');
INSERT INTO `tao_area` VALUES (396, 33, '香港岛');
INSERT INTO `tao_area` VALUES (397, 33, '九龙');
INSERT INTO `tao_area` VALUES (398, 33, '新界');
INSERT INTO `tao_area` VALUES (399, 34, '澳门半岛');
INSERT INTO `tao_area` VALUES (400, 34, '离岛');
INSERT INTO `tao_area` VALUES (401, 35, '海外');
INSERT INTO `tao_area` VALUES (402, 36, '东城区');
INSERT INTO `tao_area` VALUES (403, 36, '西城区');
INSERT INTO `tao_area` VALUES (404, 36, '崇文区');
INSERT INTO `tao_area` VALUES (405, 36, '宣武区');
INSERT INTO `tao_area` VALUES (406, 36, '朝阳区');
INSERT INTO `tao_area` VALUES (407, 36, '丰台区');
INSERT INTO `tao_area` VALUES (408, 36, '石景山区');
INSERT INTO `tao_area` VALUES (409, 36, '海淀区');
INSERT INTO `tao_area` VALUES (410, 36, '门头沟区');
INSERT INTO `tao_area` VALUES (411, 36, '房山区');
INSERT INTO `tao_area` VALUES (412, 36, '通州区');
INSERT INTO `tao_area` VALUES (413, 36, '顺义区');
INSERT INTO `tao_area` VALUES (414, 36, '昌平区');
INSERT INTO `tao_area` VALUES (415, 36, '大兴区');
INSERT INTO `tao_area` VALUES (416, 36, '怀柔区');
INSERT INTO `tao_area` VALUES (417, 36, '平谷区');
INSERT INTO `tao_area` VALUES (418, 36, '密云县');
INSERT INTO `tao_area` VALUES (419, 36, '延庆县');
INSERT INTO `tao_area` VALUES (420, 36, '其它区');
INSERT INTO `tao_area` VALUES (421, 37, '和平区');
INSERT INTO `tao_area` VALUES (422, 37, '河东区');
INSERT INTO `tao_area` VALUES (423, 37, '河西区');
INSERT INTO `tao_area` VALUES (424, 37, '南开区');
INSERT INTO `tao_area` VALUES (425, 37, '河北区');
INSERT INTO `tao_area` VALUES (426, 37, '红桥区');
INSERT INTO `tao_area` VALUES (427, 37, '塘沽区');
INSERT INTO `tao_area` VALUES (428, 37, '汉沽区');
INSERT INTO `tao_area` VALUES (429, 37, '大港区');
INSERT INTO `tao_area` VALUES (430, 37, '东丽区');
INSERT INTO `tao_area` VALUES (431, 37, '西青区');
INSERT INTO `tao_area` VALUES (432, 37, '津南区');
INSERT INTO `tao_area` VALUES (433, 37, '北辰区');
INSERT INTO `tao_area` VALUES (434, 37, '武清区');
INSERT INTO `tao_area` VALUES (435, 37, '宝坻区');
INSERT INTO `tao_area` VALUES (436, 37, '滨海新区');
INSERT INTO `tao_area` VALUES (437, 37, '宁河县');
INSERT INTO `tao_area` VALUES (438, 37, '静海县');
INSERT INTO `tao_area` VALUES (439, 37, '蓟县');
INSERT INTO `tao_area` VALUES (440, 37, '其它区');
INSERT INTO `tao_area` VALUES (441, 38, '长安区');
INSERT INTO `tao_area` VALUES (442, 38, '桥东区');
INSERT INTO `tao_area` VALUES (443, 38, '桥西区');
INSERT INTO `tao_area` VALUES (444, 38, '新华区');
INSERT INTO `tao_area` VALUES (445, 38, '井陉矿区');
INSERT INTO `tao_area` VALUES (446, 38, '裕华区');
INSERT INTO `tao_area` VALUES (447, 38, '井陉县');
INSERT INTO `tao_area` VALUES (448, 38, '正定县');
INSERT INTO `tao_area` VALUES (449, 38, '栾城区');
INSERT INTO `tao_area` VALUES (450, 38, '行唐县');
INSERT INTO `tao_area` VALUES (451, 38, '灵寿县');
INSERT INTO `tao_area` VALUES (452, 38, '高邑县');
INSERT INTO `tao_area` VALUES (453, 38, '深泽县');
INSERT INTO `tao_area` VALUES (454, 38, '赞皇县');
INSERT INTO `tao_area` VALUES (455, 38, '无极县');
INSERT INTO `tao_area` VALUES (456, 38, '平山县');
INSERT INTO `tao_area` VALUES (457, 38, '元氏县');
INSERT INTO `tao_area` VALUES (458, 38, '赵县');
INSERT INTO `tao_area` VALUES (459, 38, '辛集市');
INSERT INTO `tao_area` VALUES (460, 38, '藁城区');
INSERT INTO `tao_area` VALUES (461, 38, '晋州市');
INSERT INTO `tao_area` VALUES (462, 38, '新乐市');
INSERT INTO `tao_area` VALUES (463, 38, '鹿泉区');
INSERT INTO `tao_area` VALUES (464, 38, '其它区');
INSERT INTO `tao_area` VALUES (465, 39, '路南区');
INSERT INTO `tao_area` VALUES (466, 39, '路北区');
INSERT INTO `tao_area` VALUES (467, 39, '古冶区');
INSERT INTO `tao_area` VALUES (468, 39, '开平区');
INSERT INTO `tao_area` VALUES (469, 39, '丰南区');
INSERT INTO `tao_area` VALUES (470, 39, '丰润区');
INSERT INTO `tao_area` VALUES (471, 39, '滦县');
INSERT INTO `tao_area` VALUES (472, 39, '滦南县');
INSERT INTO `tao_area` VALUES (473, 39, '乐亭县');
INSERT INTO `tao_area` VALUES (474, 39, '迁西县');
INSERT INTO `tao_area` VALUES (475, 39, '玉田县');
INSERT INTO `tao_area` VALUES (476, 39, '曹妃甸区');
INSERT INTO `tao_area` VALUES (477, 39, '遵化市');
INSERT INTO `tao_area` VALUES (478, 39, '迁安市');
INSERT INTO `tao_area` VALUES (479, 39, '其它区');
INSERT INTO `tao_area` VALUES (480, 40, '海港区');
INSERT INTO `tao_area` VALUES (481, 40, '山海关区');
INSERT INTO `tao_area` VALUES (482, 40, '北戴河区');
INSERT INTO `tao_area` VALUES (483, 40, '青龙满族自治县');
INSERT INTO `tao_area` VALUES (484, 40, '昌黎县');
INSERT INTO `tao_area` VALUES (485, 40, '抚宁县');
INSERT INTO `tao_area` VALUES (486, 40, '卢龙县');
INSERT INTO `tao_area` VALUES (487, 40, '其它区');
INSERT INTO `tao_area` VALUES (488, 40, '经济技术开发区');
INSERT INTO `tao_area` VALUES (489, 41, '邯山区');
INSERT INTO `tao_area` VALUES (490, 41, '丛台区');
INSERT INTO `tao_area` VALUES (491, 41, '复兴区');
INSERT INTO `tao_area` VALUES (492, 41, '峰峰矿区');
INSERT INTO `tao_area` VALUES (493, 41, '邯郸县');
INSERT INTO `tao_area` VALUES (494, 41, '临漳县');
INSERT INTO `tao_area` VALUES (495, 41, '成安县');
INSERT INTO `tao_area` VALUES (496, 41, '大名县');
INSERT INTO `tao_area` VALUES (497, 41, '涉县');
INSERT INTO `tao_area` VALUES (498, 41, '磁县');
INSERT INTO `tao_area` VALUES (499, 41, '肥乡县');
INSERT INTO `tao_area` VALUES (500, 41, '永年县');
INSERT INTO `tao_area` VALUES (501, 41, '邱县');
INSERT INTO `tao_area` VALUES (502, 41, '鸡泽县');
INSERT INTO `tao_area` VALUES (503, 41, '广平县');
INSERT INTO `tao_area` VALUES (504, 41, '馆陶县');
INSERT INTO `tao_area` VALUES (505, 41, '魏县');
INSERT INTO `tao_area` VALUES (506, 41, '曲周县');
INSERT INTO `tao_area` VALUES (507, 41, '武安市');
INSERT INTO `tao_area` VALUES (508, 41, '其它区');
INSERT INTO `tao_area` VALUES (509, 42, '桥东区');
INSERT INTO `tao_area` VALUES (510, 42, '桥西区');
INSERT INTO `tao_area` VALUES (511, 42, '邢台县');
INSERT INTO `tao_area` VALUES (512, 42, '临城县');
INSERT INTO `tao_area` VALUES (513, 42, '内丘县');
INSERT INTO `tao_area` VALUES (514, 42, '柏乡县');
INSERT INTO `tao_area` VALUES (515, 42, '隆尧县');
INSERT INTO `tao_area` VALUES (516, 42, '任县');
INSERT INTO `tao_area` VALUES (517, 42, '南和县');
INSERT INTO `tao_area` VALUES (518, 42, '宁晋县');
INSERT INTO `tao_area` VALUES (519, 42, '巨鹿县');
INSERT INTO `tao_area` VALUES (520, 42, '新河县');
INSERT INTO `tao_area` VALUES (521, 42, '广宗县');
INSERT INTO `tao_area` VALUES (522, 42, '平乡县');
INSERT INTO `tao_area` VALUES (523, 42, '威县');
INSERT INTO `tao_area` VALUES (524, 42, '清河县');
INSERT INTO `tao_area` VALUES (525, 42, '临西县');
INSERT INTO `tao_area` VALUES (526, 42, '南宫市');
INSERT INTO `tao_area` VALUES (527, 42, '沙河市');
INSERT INTO `tao_area` VALUES (528, 42, '其它区');
INSERT INTO `tao_area` VALUES (529, 43, '新市区');
INSERT INTO `tao_area` VALUES (530, 43, '北市区');
INSERT INTO `tao_area` VALUES (531, 43, '南市区');
INSERT INTO `tao_area` VALUES (532, 43, '满城县');
INSERT INTO `tao_area` VALUES (533, 43, '清苑县');
INSERT INTO `tao_area` VALUES (534, 43, '涞水县');
INSERT INTO `tao_area` VALUES (535, 43, '阜平县');
INSERT INTO `tao_area` VALUES (536, 43, '徐水县');
INSERT INTO `tao_area` VALUES (537, 43, '定兴县');
INSERT INTO `tao_area` VALUES (538, 43, '唐县');
INSERT INTO `tao_area` VALUES (539, 43, '高阳县');
INSERT INTO `tao_area` VALUES (540, 43, '容城县');
INSERT INTO `tao_area` VALUES (541, 43, '涞源县');
INSERT INTO `tao_area` VALUES (542, 43, '望都县');
INSERT INTO `tao_area` VALUES (543, 43, '安新县');
INSERT INTO `tao_area` VALUES (544, 43, '易县');
INSERT INTO `tao_area` VALUES (545, 43, '曲阳县');
INSERT INTO `tao_area` VALUES (546, 43, '蠡县');
INSERT INTO `tao_area` VALUES (547, 43, '顺平县');
INSERT INTO `tao_area` VALUES (548, 43, '博野县');
INSERT INTO `tao_area` VALUES (549, 43, '雄县');
INSERT INTO `tao_area` VALUES (550, 43, '涿州市');
INSERT INTO `tao_area` VALUES (551, 43, '定州市');
INSERT INTO `tao_area` VALUES (552, 43, '安国市');
INSERT INTO `tao_area` VALUES (553, 43, '高碑店市');
INSERT INTO `tao_area` VALUES (554, 43, '高开区');
INSERT INTO `tao_area` VALUES (555, 43, '其它区');
INSERT INTO `tao_area` VALUES (556, 44, '桥东区');
INSERT INTO `tao_area` VALUES (557, 44, '桥西区');
INSERT INTO `tao_area` VALUES (558, 44, '宣化区');
INSERT INTO `tao_area` VALUES (559, 44, '下花园区');
INSERT INTO `tao_area` VALUES (560, 44, '宣化县');
INSERT INTO `tao_area` VALUES (561, 44, '张北县');
INSERT INTO `tao_area` VALUES (562, 44, '康保县');
INSERT INTO `tao_area` VALUES (563, 44, '沽源县');
INSERT INTO `tao_area` VALUES (564, 44, '尚义县');
INSERT INTO `tao_area` VALUES (565, 44, '蔚县');
INSERT INTO `tao_area` VALUES (566, 44, '阳原县');
INSERT INTO `tao_area` VALUES (567, 44, '怀安县');
INSERT INTO `tao_area` VALUES (568, 44, '万全县');
INSERT INTO `tao_area` VALUES (569, 44, '怀来县');
INSERT INTO `tao_area` VALUES (570, 44, '涿鹿县');
INSERT INTO `tao_area` VALUES (571, 44, '赤城县');
INSERT INTO `tao_area` VALUES (572, 44, '崇礼县');
INSERT INTO `tao_area` VALUES (573, 44, '其它区');
INSERT INTO `tao_area` VALUES (574, 45, '双桥区');
INSERT INTO `tao_area` VALUES (575, 45, '双滦区');
INSERT INTO `tao_area` VALUES (576, 45, '鹰手营子矿区');
INSERT INTO `tao_area` VALUES (577, 45, '承德县');
INSERT INTO `tao_area` VALUES (578, 45, '兴隆县');
INSERT INTO `tao_area` VALUES (579, 45, '平泉县');
INSERT INTO `tao_area` VALUES (580, 45, '滦平县');
INSERT INTO `tao_area` VALUES (581, 45, '隆化县');
INSERT INTO `tao_area` VALUES (582, 45, '丰宁满族自治县');
INSERT INTO `tao_area` VALUES (583, 45, '宽城满族自治县');
INSERT INTO `tao_area` VALUES (584, 45, '围场满族蒙古族自治县');
INSERT INTO `tao_area` VALUES (585, 45, '其它区');
INSERT INTO `tao_area` VALUES (586, 46, '新华区');
INSERT INTO `tao_area` VALUES (587, 46, '运河区');
INSERT INTO `tao_area` VALUES (588, 46, '沧县');
INSERT INTO `tao_area` VALUES (589, 46, '青县');
INSERT INTO `tao_area` VALUES (590, 46, '东光县');
INSERT INTO `tao_area` VALUES (591, 46, '海兴县');
INSERT INTO `tao_area` VALUES (592, 46, '盐山县');
INSERT INTO `tao_area` VALUES (593, 46, '肃宁县');
INSERT INTO `tao_area` VALUES (594, 46, '南皮县');
INSERT INTO `tao_area` VALUES (595, 46, '吴桥县');
INSERT INTO `tao_area` VALUES (596, 46, '献县');
INSERT INTO `tao_area` VALUES (597, 46, '孟村回族自治县');
INSERT INTO `tao_area` VALUES (598, 46, '泊头市');
INSERT INTO `tao_area` VALUES (599, 46, '任丘市');
INSERT INTO `tao_area` VALUES (600, 46, '黄骅市');
INSERT INTO `tao_area` VALUES (601, 46, '河间市');
INSERT INTO `tao_area` VALUES (602, 46, '其它区');
INSERT INTO `tao_area` VALUES (603, 47, '安次区');
INSERT INTO `tao_area` VALUES (604, 47, '广阳区');
INSERT INTO `tao_area` VALUES (605, 47, '固安县');
INSERT INTO `tao_area` VALUES (606, 47, '永清县');
INSERT INTO `tao_area` VALUES (607, 47, '香河县');
INSERT INTO `tao_area` VALUES (608, 47, '大城县');
INSERT INTO `tao_area` VALUES (609, 47, '文安县');
INSERT INTO `tao_area` VALUES (610, 47, '大厂回族自治县');
INSERT INTO `tao_area` VALUES (611, 47, '开发区');
INSERT INTO `tao_area` VALUES (612, 47, '燕郊经济技术开发区');
INSERT INTO `tao_area` VALUES (613, 47, '霸州市');
INSERT INTO `tao_area` VALUES (614, 47, '三河市');
INSERT INTO `tao_area` VALUES (615, 47, '其它区');
INSERT INTO `tao_area` VALUES (616, 48, '桃城区');
INSERT INTO `tao_area` VALUES (617, 48, '枣强县');
INSERT INTO `tao_area` VALUES (618, 48, '武邑县');
INSERT INTO `tao_area` VALUES (619, 48, '武强县');
INSERT INTO `tao_area` VALUES (620, 48, '饶阳县');
INSERT INTO `tao_area` VALUES (621, 48, '安平县');
INSERT INTO `tao_area` VALUES (622, 48, '故城县');
INSERT INTO `tao_area` VALUES (623, 48, '景县');
INSERT INTO `tao_area` VALUES (624, 48, '阜城县');
INSERT INTO `tao_area` VALUES (625, 48, '冀州市');
INSERT INTO `tao_area` VALUES (626, 48, '深州市');
INSERT INTO `tao_area` VALUES (627, 48, '其它区');
INSERT INTO `tao_area` VALUES (628, 49, '小店区');
INSERT INTO `tao_area` VALUES (629, 49, '迎泽区');
INSERT INTO `tao_area` VALUES (630, 49, '杏花岭区');
INSERT INTO `tao_area` VALUES (631, 49, '尖草坪区');
INSERT INTO `tao_area` VALUES (632, 49, '万柏林区');
INSERT INTO `tao_area` VALUES (633, 49, '晋源区');
INSERT INTO `tao_area` VALUES (634, 49, '清徐县');
INSERT INTO `tao_area` VALUES (635, 49, '阳曲县');
INSERT INTO `tao_area` VALUES (636, 49, '娄烦县');
INSERT INTO `tao_area` VALUES (637, 49, '古交市');
INSERT INTO `tao_area` VALUES (638, 49, '其它区');
INSERT INTO `tao_area` VALUES (639, 50, '城区');
INSERT INTO `tao_area` VALUES (640, 50, '矿区');
INSERT INTO `tao_area` VALUES (641, 50, '南郊区');
INSERT INTO `tao_area` VALUES (642, 50, '新荣区');
INSERT INTO `tao_area` VALUES (643, 50, '阳高县');
INSERT INTO `tao_area` VALUES (644, 50, '天镇县');
INSERT INTO `tao_area` VALUES (645, 50, '广灵县');
INSERT INTO `tao_area` VALUES (646, 50, '灵丘县');
INSERT INTO `tao_area` VALUES (647, 50, '浑源县');
INSERT INTO `tao_area` VALUES (648, 50, '左云县');
INSERT INTO `tao_area` VALUES (649, 50, '大同县');
INSERT INTO `tao_area` VALUES (650, 50, '其它区');
INSERT INTO `tao_area` VALUES (651, 51, '城区');
INSERT INTO `tao_area` VALUES (652, 51, '矿区');
INSERT INTO `tao_area` VALUES (653, 51, '郊区');
INSERT INTO `tao_area` VALUES (654, 51, '平定县');
INSERT INTO `tao_area` VALUES (655, 51, '盂县');
INSERT INTO `tao_area` VALUES (656, 51, '其它区');
INSERT INTO `tao_area` VALUES (657, 52, '长治县');
INSERT INTO `tao_area` VALUES (658, 52, '襄垣县');
INSERT INTO `tao_area` VALUES (659, 52, '屯留县');
INSERT INTO `tao_area` VALUES (660, 52, '平顺县');
INSERT INTO `tao_area` VALUES (661, 52, '黎城县');
INSERT INTO `tao_area` VALUES (662, 52, '壶关县');
INSERT INTO `tao_area` VALUES (663, 52, '长子县');
INSERT INTO `tao_area` VALUES (664, 52, '武乡县');
INSERT INTO `tao_area` VALUES (665, 52, '沁县');
INSERT INTO `tao_area` VALUES (666, 52, '沁源县');
INSERT INTO `tao_area` VALUES (667, 52, '潞城市');
INSERT INTO `tao_area` VALUES (668, 52, '城区');
INSERT INTO `tao_area` VALUES (669, 52, '郊区');
INSERT INTO `tao_area` VALUES (670, 52, '高新区');
INSERT INTO `tao_area` VALUES (671, 52, '其它区');
INSERT INTO `tao_area` VALUES (672, 53, '城区');
INSERT INTO `tao_area` VALUES (673, 53, '沁水县');
INSERT INTO `tao_area` VALUES (674, 53, '阳城县');
INSERT INTO `tao_area` VALUES (675, 53, '陵川县');
INSERT INTO `tao_area` VALUES (676, 53, '泽州县');
INSERT INTO `tao_area` VALUES (677, 53, '高平市');
INSERT INTO `tao_area` VALUES (678, 53, '其它区');
INSERT INTO `tao_area` VALUES (679, 54, '朔城区');
INSERT INTO `tao_area` VALUES (680, 54, '平鲁区');
INSERT INTO `tao_area` VALUES (681, 54, '山阴县');
INSERT INTO `tao_area` VALUES (682, 54, '应县');
INSERT INTO `tao_area` VALUES (683, 54, '右玉县');
INSERT INTO `tao_area` VALUES (684, 54, '怀仁县');
INSERT INTO `tao_area` VALUES (685, 54, '其它区');
INSERT INTO `tao_area` VALUES (686, 55, '榆次区');
INSERT INTO `tao_area` VALUES (687, 55, '榆社县');
INSERT INTO `tao_area` VALUES (688, 55, '左权县');
INSERT INTO `tao_area` VALUES (689, 55, '和顺县');
INSERT INTO `tao_area` VALUES (690, 55, '昔阳县');
INSERT INTO `tao_area` VALUES (691, 55, '寿阳县');
INSERT INTO `tao_area` VALUES (692, 55, '太谷县');
INSERT INTO `tao_area` VALUES (693, 55, '祁县');
INSERT INTO `tao_area` VALUES (694, 55, '平遥县');
INSERT INTO `tao_area` VALUES (695, 55, '灵石县');
INSERT INTO `tao_area` VALUES (696, 55, '介休市');
INSERT INTO `tao_area` VALUES (697, 55, '其它区');
INSERT INTO `tao_area` VALUES (698, 56, '盐湖区');
INSERT INTO `tao_area` VALUES (699, 56, '临猗县');
INSERT INTO `tao_area` VALUES (700, 56, '万荣县');
INSERT INTO `tao_area` VALUES (701, 56, '闻喜县');
INSERT INTO `tao_area` VALUES (702, 56, '稷山县');
INSERT INTO `tao_area` VALUES (703, 56, '新绛县');
INSERT INTO `tao_area` VALUES (704, 56, '绛县');
INSERT INTO `tao_area` VALUES (705, 56, '垣曲县');
INSERT INTO `tao_area` VALUES (706, 56, '夏县');
INSERT INTO `tao_area` VALUES (707, 56, '平陆县');
INSERT INTO `tao_area` VALUES (708, 56, '芮城县');
INSERT INTO `tao_area` VALUES (709, 56, '永济市');
INSERT INTO `tao_area` VALUES (710, 56, '河津市');
INSERT INTO `tao_area` VALUES (711, 56, '其它区');
INSERT INTO `tao_area` VALUES (712, 57, '忻府区');
INSERT INTO `tao_area` VALUES (713, 57, '定襄县');
INSERT INTO `tao_area` VALUES (714, 57, '五台县');
INSERT INTO `tao_area` VALUES (715, 57, '代县');
INSERT INTO `tao_area` VALUES (716, 57, '繁峙县');
INSERT INTO `tao_area` VALUES (717, 57, '宁武县');
INSERT INTO `tao_area` VALUES (718, 57, '静乐县');
INSERT INTO `tao_area` VALUES (719, 57, '神池县');
INSERT INTO `tao_area` VALUES (720, 57, '五寨县');
INSERT INTO `tao_area` VALUES (721, 57, '岢岚县');
INSERT INTO `tao_area` VALUES (722, 57, '河曲县');
INSERT INTO `tao_area` VALUES (723, 57, '保德县');
INSERT INTO `tao_area` VALUES (724, 57, '偏关县');
INSERT INTO `tao_area` VALUES (725, 57, '原平市');
INSERT INTO `tao_area` VALUES (726, 57, '其它区');
INSERT INTO `tao_area` VALUES (727, 58, '尧都区');
INSERT INTO `tao_area` VALUES (728, 58, '曲沃县');
INSERT INTO `tao_area` VALUES (729, 58, '翼城县');
INSERT INTO `tao_area` VALUES (730, 58, '襄汾县');
INSERT INTO `tao_area` VALUES (731, 58, '洪洞县');
INSERT INTO `tao_area` VALUES (732, 58, '古县');
INSERT INTO `tao_area` VALUES (733, 58, '安泽县');
INSERT INTO `tao_area` VALUES (734, 58, '浮山县');
INSERT INTO `tao_area` VALUES (735, 58, '吉县');
INSERT INTO `tao_area` VALUES (736, 58, '乡宁县');
INSERT INTO `tao_area` VALUES (737, 58, '大宁县');
INSERT INTO `tao_area` VALUES (738, 58, '隰县');
INSERT INTO `tao_area` VALUES (739, 58, '永和县');
INSERT INTO `tao_area` VALUES (740, 58, '蒲县');
INSERT INTO `tao_area` VALUES (741, 58, '汾西县');
INSERT INTO `tao_area` VALUES (742, 58, '侯马市');
INSERT INTO `tao_area` VALUES (743, 58, '霍州市');
INSERT INTO `tao_area` VALUES (744, 58, '其它区');
INSERT INTO `tao_area` VALUES (745, 59, '离石区');
INSERT INTO `tao_area` VALUES (746, 59, '文水县');
INSERT INTO `tao_area` VALUES (747, 59, '交城县');
INSERT INTO `tao_area` VALUES (748, 59, '兴县');
INSERT INTO `tao_area` VALUES (749, 59, '临县');
INSERT INTO `tao_area` VALUES (750, 59, '柳林县');
INSERT INTO `tao_area` VALUES (751, 59, '石楼县');
INSERT INTO `tao_area` VALUES (752, 59, '岚县');
INSERT INTO `tao_area` VALUES (753, 59, '方山县');
INSERT INTO `tao_area` VALUES (754, 59, '中阳县');
INSERT INTO `tao_area` VALUES (755, 59, '交口县');
INSERT INTO `tao_area` VALUES (756, 59, '孝义市');
INSERT INTO `tao_area` VALUES (757, 59, '汾阳市');
INSERT INTO `tao_area` VALUES (758, 59, '其它区');
INSERT INTO `tao_area` VALUES (759, 60, '新城区');
INSERT INTO `tao_area` VALUES (760, 60, '回民区');
INSERT INTO `tao_area` VALUES (761, 60, '玉泉区');
INSERT INTO `tao_area` VALUES (762, 60, '赛罕区');
INSERT INTO `tao_area` VALUES (763, 60, '土默特左旗');
INSERT INTO `tao_area` VALUES (764, 60, '托克托县');
INSERT INTO `tao_area` VALUES (765, 60, '和林格尔县');
INSERT INTO `tao_area` VALUES (766, 60, '清水河县');
INSERT INTO `tao_area` VALUES (767, 60, '武川县');
INSERT INTO `tao_area` VALUES (768, 60, '其它区');
INSERT INTO `tao_area` VALUES (769, 61, '东河区');
INSERT INTO `tao_area` VALUES (770, 61, '昆都仑区');
INSERT INTO `tao_area` VALUES (771, 61, '青山区');
INSERT INTO `tao_area` VALUES (772, 61, '石拐区');
INSERT INTO `tao_area` VALUES (773, 61, '白云鄂博矿区');
INSERT INTO `tao_area` VALUES (774, 61, '九原区');
INSERT INTO `tao_area` VALUES (775, 61, '土默特右旗');
INSERT INTO `tao_area` VALUES (776, 61, '固阳县');
INSERT INTO `tao_area` VALUES (777, 61, '达尔罕茂明安联合旗');
INSERT INTO `tao_area` VALUES (778, 61, '其它区');
INSERT INTO `tao_area` VALUES (779, 62, '海勃湾区');
INSERT INTO `tao_area` VALUES (780, 62, '海南区');
INSERT INTO `tao_area` VALUES (781, 62, '乌达区');
INSERT INTO `tao_area` VALUES (782, 62, '其它区');
INSERT INTO `tao_area` VALUES (783, 63, '红山区');
INSERT INTO `tao_area` VALUES (784, 63, '元宝山区');
INSERT INTO `tao_area` VALUES (785, 63, '松山区');
INSERT INTO `tao_area` VALUES (786, 63, '阿鲁科尔沁旗');
INSERT INTO `tao_area` VALUES (787, 63, '巴林左旗');
INSERT INTO `tao_area` VALUES (788, 63, '巴林右旗');
INSERT INTO `tao_area` VALUES (789, 63, '林西县');
INSERT INTO `tao_area` VALUES (790, 63, '克什克腾旗');
INSERT INTO `tao_area` VALUES (791, 63, '翁牛特旗');
INSERT INTO `tao_area` VALUES (792, 63, '喀喇沁旗');
INSERT INTO `tao_area` VALUES (793, 63, '宁城县');
INSERT INTO `tao_area` VALUES (794, 63, '敖汉旗');
INSERT INTO `tao_area` VALUES (795, 63, '其它区');
INSERT INTO `tao_area` VALUES (796, 64, '科尔沁区');
INSERT INTO `tao_area` VALUES (797, 64, '科尔沁左翼中旗');
INSERT INTO `tao_area` VALUES (798, 64, '科尔沁左翼后旗');
INSERT INTO `tao_area` VALUES (799, 64, '开鲁县');
INSERT INTO `tao_area` VALUES (800, 64, '库伦旗');
INSERT INTO `tao_area` VALUES (801, 64, '奈曼旗');
INSERT INTO `tao_area` VALUES (802, 64, '扎鲁特旗');
INSERT INTO `tao_area` VALUES (803, 64, '霍林郭勒市');
INSERT INTO `tao_area` VALUES (804, 64, '其它区');
INSERT INTO `tao_area` VALUES (805, 65, '东胜区');
INSERT INTO `tao_area` VALUES (806, 65, '达拉特旗');
INSERT INTO `tao_area` VALUES (807, 65, '准格尔旗');
INSERT INTO `tao_area` VALUES (808, 65, '鄂托克前旗');
INSERT INTO `tao_area` VALUES (809, 65, '鄂托克旗');
INSERT INTO `tao_area` VALUES (810, 65, '杭锦旗');
INSERT INTO `tao_area` VALUES (811, 65, '乌审旗');
INSERT INTO `tao_area` VALUES (812, 65, '伊金霍洛旗');
INSERT INTO `tao_area` VALUES (813, 65, '其它区');
INSERT INTO `tao_area` VALUES (814, 66, '海拉尔区');
INSERT INTO `tao_area` VALUES (815, 66, '扎赉诺尔区');
INSERT INTO `tao_area` VALUES (816, 66, '阿荣旗');
INSERT INTO `tao_area` VALUES (817, 66, '莫力达瓦达斡尔族自治旗');
INSERT INTO `tao_area` VALUES (818, 66, '鄂伦春自治旗');
INSERT INTO `tao_area` VALUES (819, 66, '鄂温克族自治旗');
INSERT INTO `tao_area` VALUES (820, 66, '陈巴尔虎旗');
INSERT INTO `tao_area` VALUES (821, 66, '新巴尔虎左旗');
INSERT INTO `tao_area` VALUES (822, 66, '新巴尔虎右旗');
INSERT INTO `tao_area` VALUES (823, 66, '满洲里市');
INSERT INTO `tao_area` VALUES (824, 66, '牙克石市');
INSERT INTO `tao_area` VALUES (825, 66, '扎兰屯市');
INSERT INTO `tao_area` VALUES (826, 66, '额尔古纳市');
INSERT INTO `tao_area` VALUES (827, 66, '根河市');
INSERT INTO `tao_area` VALUES (828, 66, '其它区');
INSERT INTO `tao_area` VALUES (829, 67, '临河区');
INSERT INTO `tao_area` VALUES (830, 67, '五原县');
INSERT INTO `tao_area` VALUES (831, 67, '磴口县');
INSERT INTO `tao_area` VALUES (832, 67, '乌拉特前旗');
INSERT INTO `tao_area` VALUES (833, 67, '乌拉特中旗');
INSERT INTO `tao_area` VALUES (834, 67, '乌拉特后旗');
INSERT INTO `tao_area` VALUES (835, 67, '杭锦后旗');
INSERT INTO `tao_area` VALUES (836, 67, '其它区');
INSERT INTO `tao_area` VALUES (837, 68, '集宁区');
INSERT INTO `tao_area` VALUES (838, 68, '卓资县');
INSERT INTO `tao_area` VALUES (839, 68, '化德县');
INSERT INTO `tao_area` VALUES (840, 68, '商都县');
INSERT INTO `tao_area` VALUES (841, 68, '兴和县');
INSERT INTO `tao_area` VALUES (842, 68, '凉城县');
INSERT INTO `tao_area` VALUES (843, 68, '察哈尔右翼前旗');
INSERT INTO `tao_area` VALUES (844, 68, '察哈尔右翼中旗');
INSERT INTO `tao_area` VALUES (845, 68, '察哈尔右翼后旗');
INSERT INTO `tao_area` VALUES (846, 68, '四子王旗');
INSERT INTO `tao_area` VALUES (847, 68, '丰镇市');
INSERT INTO `tao_area` VALUES (848, 68, '其它区');
INSERT INTO `tao_area` VALUES (849, 69, '乌兰浩特市');
INSERT INTO `tao_area` VALUES (850, 69, '阿尔山市');
INSERT INTO `tao_area` VALUES (851, 69, '科尔沁右翼前旗');
INSERT INTO `tao_area` VALUES (852, 69, '科尔沁右翼中旗');
INSERT INTO `tao_area` VALUES (853, 69, '扎赉特旗');
INSERT INTO `tao_area` VALUES (854, 69, '突泉县');
INSERT INTO `tao_area` VALUES (855, 69, '其它区');
INSERT INTO `tao_area` VALUES (856, 70, '二连浩特市');
INSERT INTO `tao_area` VALUES (857, 70, '锡林浩特市');
INSERT INTO `tao_area` VALUES (858, 70, '阿巴嘎旗');
INSERT INTO `tao_area` VALUES (859, 70, '苏尼特左旗');
INSERT INTO `tao_area` VALUES (860, 70, '苏尼特右旗');
INSERT INTO `tao_area` VALUES (861, 70, '东乌珠穆沁旗');
INSERT INTO `tao_area` VALUES (862, 70, '西乌珠穆沁旗');
INSERT INTO `tao_area` VALUES (863, 70, '太仆寺旗');
INSERT INTO `tao_area` VALUES (864, 70, '镶黄旗');
INSERT INTO `tao_area` VALUES (865, 70, '正镶白旗');
INSERT INTO `tao_area` VALUES (866, 70, '正蓝旗');
INSERT INTO `tao_area` VALUES (867, 70, '多伦县');
INSERT INTO `tao_area` VALUES (868, 70, '其它区');
INSERT INTO `tao_area` VALUES (869, 71, '阿拉善左旗');
INSERT INTO `tao_area` VALUES (870, 71, '阿拉善右旗');
INSERT INTO `tao_area` VALUES (871, 71, '额济纳旗');
INSERT INTO `tao_area` VALUES (872, 71, '其它区');
INSERT INTO `tao_area` VALUES (873, 72, '和平区');
INSERT INTO `tao_area` VALUES (874, 72, '沈河区');
INSERT INTO `tao_area` VALUES (875, 72, '大东区');
INSERT INTO `tao_area` VALUES (876, 72, '皇姑区');
INSERT INTO `tao_area` VALUES (877, 72, '铁西区');
INSERT INTO `tao_area` VALUES (878, 72, '苏家屯区');
INSERT INTO `tao_area` VALUES (879, 72, '浑南区');
INSERT INTO `tao_area` VALUES (880, 72, '新城子区');
INSERT INTO `tao_area` VALUES (881, 72, '于洪区');
INSERT INTO `tao_area` VALUES (882, 72, '辽中县');
INSERT INTO `tao_area` VALUES (883, 72, '康平县');
INSERT INTO `tao_area` VALUES (884, 72, '法库县');
INSERT INTO `tao_area` VALUES (885, 72, '新民市');
INSERT INTO `tao_area` VALUES (886, 72, '浑南新区');
INSERT INTO `tao_area` VALUES (887, 72, '张士开发区');
INSERT INTO `tao_area` VALUES (888, 72, '沈北新区');
INSERT INTO `tao_area` VALUES (889, 72, '其它区');
INSERT INTO `tao_area` VALUES (890, 73, '中山区');
INSERT INTO `tao_area` VALUES (891, 73, '西岗区');
INSERT INTO `tao_area` VALUES (892, 73, '沙河口区');
INSERT INTO `tao_area` VALUES (893, 73, '甘井子区');
INSERT INTO `tao_area` VALUES (894, 73, '旅顺口区');
INSERT INTO `tao_area` VALUES (895, 73, '金州区');
INSERT INTO `tao_area` VALUES (896, 73, '长海县');
INSERT INTO `tao_area` VALUES (897, 73, '开发区');
INSERT INTO `tao_area` VALUES (898, 73, '瓦房店市');
INSERT INTO `tao_area` VALUES (899, 73, '普兰店市');
INSERT INTO `tao_area` VALUES (900, 73, '庄河市');
INSERT INTO `tao_area` VALUES (901, 73, '岭前区');
INSERT INTO `tao_area` VALUES (902, 73, '其它区');
INSERT INTO `tao_area` VALUES (903, 74, '铁东区');
INSERT INTO `tao_area` VALUES (904, 74, '铁西区');
INSERT INTO `tao_area` VALUES (905, 74, '立山区');
INSERT INTO `tao_area` VALUES (906, 74, '千山区');
INSERT INTO `tao_area` VALUES (907, 74, '台安县');
INSERT INTO `tao_area` VALUES (908, 74, '岫岩满族自治县');
INSERT INTO `tao_area` VALUES (909, 74, '高新区');
INSERT INTO `tao_area` VALUES (910, 74, '海城市');
INSERT INTO `tao_area` VALUES (911, 74, '其它区');
INSERT INTO `tao_area` VALUES (912, 75, '新抚区');
INSERT INTO `tao_area` VALUES (913, 75, '东洲区');
INSERT INTO `tao_area` VALUES (914, 75, '望花区');
INSERT INTO `tao_area` VALUES (915, 75, '顺城区');
INSERT INTO `tao_area` VALUES (916, 75, '抚顺县');
INSERT INTO `tao_area` VALUES (917, 75, '新宾满族自治县');
INSERT INTO `tao_area` VALUES (918, 75, '清原满族自治县');
INSERT INTO `tao_area` VALUES (919, 75, '其它区');
INSERT INTO `tao_area` VALUES (920, 76, '平山区');
INSERT INTO `tao_area` VALUES (921, 76, '溪湖区');
INSERT INTO `tao_area` VALUES (922, 76, '明山区');
INSERT INTO `tao_area` VALUES (923, 76, '南芬区');
INSERT INTO `tao_area` VALUES (924, 76, '本溪满族自治县');
INSERT INTO `tao_area` VALUES (925, 76, '桓仁满族自治县');
INSERT INTO `tao_area` VALUES (926, 76, '其它区');
INSERT INTO `tao_area` VALUES (927, 77, '元宝区');
INSERT INTO `tao_area` VALUES (928, 77, '振兴区');
INSERT INTO `tao_area` VALUES (929, 77, '振安区');
INSERT INTO `tao_area` VALUES (930, 77, '宽甸满族自治县');
INSERT INTO `tao_area` VALUES (931, 77, '东港市');
INSERT INTO `tao_area` VALUES (932, 77, '凤城市');
INSERT INTO `tao_area` VALUES (933, 77, '其它区');
INSERT INTO `tao_area` VALUES (934, 78, '古塔区');
INSERT INTO `tao_area` VALUES (935, 78, '凌河区');
INSERT INTO `tao_area` VALUES (936, 78, '太和区');
INSERT INTO `tao_area` VALUES (937, 78, '黑山县');
INSERT INTO `tao_area` VALUES (938, 78, '义县');
INSERT INTO `tao_area` VALUES (939, 78, '凌海市');
INSERT INTO `tao_area` VALUES (940, 78, '北镇市');
INSERT INTO `tao_area` VALUES (941, 78, '其它区');
INSERT INTO `tao_area` VALUES (942, 79, '站前区');
INSERT INTO `tao_area` VALUES (943, 79, '西市区');
INSERT INTO `tao_area` VALUES (944, 79, '鲅鱼圈区');
INSERT INTO `tao_area` VALUES (945, 79, '老边区');
INSERT INTO `tao_area` VALUES (946, 79, '盖州市');
INSERT INTO `tao_area` VALUES (947, 79, '大石桥市');
INSERT INTO `tao_area` VALUES (948, 79, '其它区');
INSERT INTO `tao_area` VALUES (949, 80, '海州区');
INSERT INTO `tao_area` VALUES (950, 80, '新邱区');
INSERT INTO `tao_area` VALUES (951, 80, '太平区');
INSERT INTO `tao_area` VALUES (952, 80, '清河门区');
INSERT INTO `tao_area` VALUES (953, 80, '细河区');
INSERT INTO `tao_area` VALUES (954, 80, '阜新蒙古族自治县');
INSERT INTO `tao_area` VALUES (955, 80, '彰武县');
INSERT INTO `tao_area` VALUES (956, 80, '其它区');
INSERT INTO `tao_area` VALUES (957, 81, '白塔区');
INSERT INTO `tao_area` VALUES (958, 81, '文圣区');
INSERT INTO `tao_area` VALUES (959, 81, '宏伟区');
INSERT INTO `tao_area` VALUES (960, 81, '弓长岭区');
INSERT INTO `tao_area` VALUES (961, 81, '太子河区');
INSERT INTO `tao_area` VALUES (962, 81, '辽阳县');
INSERT INTO `tao_area` VALUES (963, 81, '灯塔市');
INSERT INTO `tao_area` VALUES (964, 81, '其它区');
INSERT INTO `tao_area` VALUES (965, 82, '双台子区');
INSERT INTO `tao_area` VALUES (966, 82, '兴隆台区');
INSERT INTO `tao_area` VALUES (967, 82, '大洼县');
INSERT INTO `tao_area` VALUES (968, 82, '盘山县');
INSERT INTO `tao_area` VALUES (969, 82, '其它区');
INSERT INTO `tao_area` VALUES (970, 83, '银州区');
INSERT INTO `tao_area` VALUES (971, 83, '清河区');
INSERT INTO `tao_area` VALUES (972, 83, '铁岭县');
INSERT INTO `tao_area` VALUES (973, 83, '西丰县');
INSERT INTO `tao_area` VALUES (974, 83, '昌图县');
INSERT INTO `tao_area` VALUES (975, 83, '调兵山市');
INSERT INTO `tao_area` VALUES (976, 83, '开原市');
INSERT INTO `tao_area` VALUES (977, 83, '其它区');
INSERT INTO `tao_area` VALUES (978, 84, '双塔区');
INSERT INTO `tao_area` VALUES (979, 84, '龙城区');
INSERT INTO `tao_area` VALUES (980, 84, '朝阳县');
INSERT INTO `tao_area` VALUES (981, 84, '建平县');
INSERT INTO `tao_area` VALUES (982, 84, '喀喇沁左翼蒙古族自治县');
INSERT INTO `tao_area` VALUES (983, 84, '北票市');
INSERT INTO `tao_area` VALUES (984, 84, '凌源市');
INSERT INTO `tao_area` VALUES (985, 84, '其它区');
INSERT INTO `tao_area` VALUES (986, 85, '连山区');
INSERT INTO `tao_area` VALUES (987, 85, '龙港区');
INSERT INTO `tao_area` VALUES (988, 85, '南票区');
INSERT INTO `tao_area` VALUES (989, 85, '绥中县');
INSERT INTO `tao_area` VALUES (990, 85, '建昌县');
INSERT INTO `tao_area` VALUES (991, 85, '兴城市');
INSERT INTO `tao_area` VALUES (992, 85, '其它区');
INSERT INTO `tao_area` VALUES (993, 86, '南关区');
INSERT INTO `tao_area` VALUES (994, 86, '宽城区');
INSERT INTO `tao_area` VALUES (995, 86, '朝阳区');
INSERT INTO `tao_area` VALUES (996, 86, '二道区');
INSERT INTO `tao_area` VALUES (997, 86, '绿园区');
INSERT INTO `tao_area` VALUES (998, 86, '双阳区');
INSERT INTO `tao_area` VALUES (999, 86, '农安县');
INSERT INTO `tao_area` VALUES (1000, 86, '九台区');
INSERT INTO `tao_area` VALUES (1001, 86, '榆树市');
INSERT INTO `tao_area` VALUES (1002, 86, '德惠市');
INSERT INTO `tao_area` VALUES (1003, 86, '高新技术产业开发区');
INSERT INTO `tao_area` VALUES (1004, 86, '汽车产业开发区');
INSERT INTO `tao_area` VALUES (1005, 86, '经济技术开发区');
INSERT INTO `tao_area` VALUES (1006, 86, '净月旅游开发区');
INSERT INTO `tao_area` VALUES (1007, 86, '其它区');
INSERT INTO `tao_area` VALUES (1008, 87, '昌邑区');
INSERT INTO `tao_area` VALUES (1009, 87, '龙潭区');
INSERT INTO `tao_area` VALUES (1010, 87, '船营区');
INSERT INTO `tao_area` VALUES (1011, 87, '丰满区');
INSERT INTO `tao_area` VALUES (1012, 87, '永吉县');
INSERT INTO `tao_area` VALUES (1013, 87, '蛟河市');
INSERT INTO `tao_area` VALUES (1014, 87, '桦甸市');
INSERT INTO `tao_area` VALUES (1015, 87, '舒兰市');
INSERT INTO `tao_area` VALUES (1016, 87, '磐石市');
INSERT INTO `tao_area` VALUES (1017, 87, '其它区');
INSERT INTO `tao_area` VALUES (1018, 88, '铁西区');
INSERT INTO `tao_area` VALUES (1019, 88, '铁东区');
INSERT INTO `tao_area` VALUES (1020, 88, '梨树县');
INSERT INTO `tao_area` VALUES (1021, 88, '伊通满族自治县');
INSERT INTO `tao_area` VALUES (1022, 88, '公主岭市');
INSERT INTO `tao_area` VALUES (1023, 88, '双辽市');
INSERT INTO `tao_area` VALUES (1024, 88, '其它区');
INSERT INTO `tao_area` VALUES (1025, 89, '龙山区');
INSERT INTO `tao_area` VALUES (1026, 89, '西安区');
INSERT INTO `tao_area` VALUES (1027, 89, '东丰县');
INSERT INTO `tao_area` VALUES (1028, 89, '东辽县');
INSERT INTO `tao_area` VALUES (1029, 89, '其它区');
INSERT INTO `tao_area` VALUES (1030, 90, '东昌区');
INSERT INTO `tao_area` VALUES (1031, 90, '二道江区');
INSERT INTO `tao_area` VALUES (1032, 90, '通化县');
INSERT INTO `tao_area` VALUES (1033, 90, '辉南县');
INSERT INTO `tao_area` VALUES (1034, 90, '柳河县');
INSERT INTO `tao_area` VALUES (1035, 90, '梅河口市');
INSERT INTO `tao_area` VALUES (1036, 90, '集安市');
INSERT INTO `tao_area` VALUES (1037, 90, '其它区');
INSERT INTO `tao_area` VALUES (1038, 91, '浑江区');
INSERT INTO `tao_area` VALUES (1039, 91, '抚松县');
INSERT INTO `tao_area` VALUES (1040, 91, '靖宇县');
INSERT INTO `tao_area` VALUES (1041, 91, '长白朝鲜族自治县');
INSERT INTO `tao_area` VALUES (1042, 91, '江源区');
INSERT INTO `tao_area` VALUES (1043, 91, '临江市');
INSERT INTO `tao_area` VALUES (1044, 91, '其它区');
INSERT INTO `tao_area` VALUES (1045, 92, '宁江区');
INSERT INTO `tao_area` VALUES (1046, 92, '前郭尔罗斯蒙古族自治县');
INSERT INTO `tao_area` VALUES (1047, 92, '长岭县');
INSERT INTO `tao_area` VALUES (1048, 92, '乾安县');
INSERT INTO `tao_area` VALUES (1049, 92, '扶余市');
INSERT INTO `tao_area` VALUES (1050, 92, '其它区');
INSERT INTO `tao_area` VALUES (1051, 93, '洮北区');
INSERT INTO `tao_area` VALUES (1052, 93, '镇赉县');
INSERT INTO `tao_area` VALUES (1053, 93, '通榆县');
INSERT INTO `tao_area` VALUES (1054, 93, '洮南市');
INSERT INTO `tao_area` VALUES (1055, 93, '大安市');
INSERT INTO `tao_area` VALUES (1056, 93, '其它区');
INSERT INTO `tao_area` VALUES (1057, 94, '延吉市');
INSERT INTO `tao_area` VALUES (1058, 94, '图们市');
INSERT INTO `tao_area` VALUES (1059, 94, '敦化市');
INSERT INTO `tao_area` VALUES (1060, 94, '珲春市');
INSERT INTO `tao_area` VALUES (1061, 94, '龙井市');
INSERT INTO `tao_area` VALUES (1062, 94, '和龙市');
INSERT INTO `tao_area` VALUES (1063, 94, '汪清县');
INSERT INTO `tao_area` VALUES (1064, 94, '安图县');
INSERT INTO `tao_area` VALUES (1065, 94, '其它区');
INSERT INTO `tao_area` VALUES (1066, 95, '道里区');
INSERT INTO `tao_area` VALUES (1067, 95, '南岗区');
INSERT INTO `tao_area` VALUES (1068, 95, '道外区');
INSERT INTO `tao_area` VALUES (1069, 95, '香坊区');
INSERT INTO `tao_area` VALUES (1070, 95, '动力区');
INSERT INTO `tao_area` VALUES (1071, 95, '平房区');
INSERT INTO `tao_area` VALUES (1072, 95, '松北区');
INSERT INTO `tao_area` VALUES (1073, 95, '呼兰区');
INSERT INTO `tao_area` VALUES (1074, 95, '依兰县');
INSERT INTO `tao_area` VALUES (1075, 95, '方正县');
INSERT INTO `tao_area` VALUES (1076, 95, '宾县');
INSERT INTO `tao_area` VALUES (1077, 95, '巴彦县');
INSERT INTO `tao_area` VALUES (1078, 95, '木兰县');
INSERT INTO `tao_area` VALUES (1079, 95, '通河县');
INSERT INTO `tao_area` VALUES (1080, 95, '延寿县');
INSERT INTO `tao_area` VALUES (1081, 95, '阿城区');
INSERT INTO `tao_area` VALUES (1082, 95, '双城区');
INSERT INTO `tao_area` VALUES (1083, 95, '尚志市');
INSERT INTO `tao_area` VALUES (1084, 95, '五常市');
INSERT INTO `tao_area` VALUES (1085, 95, '阿城市');
INSERT INTO `tao_area` VALUES (1086, 95, '其它区');
INSERT INTO `tao_area` VALUES (1087, 96, '龙沙区');
INSERT INTO `tao_area` VALUES (1088, 96, '建华区');
INSERT INTO `tao_area` VALUES (1089, 96, '铁锋区');
INSERT INTO `tao_area` VALUES (1090, 96, '昂昂溪区');
INSERT INTO `tao_area` VALUES (1091, 96, '富拉尔基区');
INSERT INTO `tao_area` VALUES (1092, 96, '碾子山区');
INSERT INTO `tao_area` VALUES (1093, 96, '梅里斯达斡尔族区');
INSERT INTO `tao_area` VALUES (1094, 96, '龙江县');
INSERT INTO `tao_area` VALUES (1095, 96, '依安县');
INSERT INTO `tao_area` VALUES (1096, 96, '泰来县');
INSERT INTO `tao_area` VALUES (1097, 96, '甘南县');
INSERT INTO `tao_area` VALUES (1098, 96, '富裕县');
INSERT INTO `tao_area` VALUES (1099, 96, '克山县');
INSERT INTO `tao_area` VALUES (1100, 96, '克东县');
INSERT INTO `tao_area` VALUES (1101, 96, '拜泉县');
INSERT INTO `tao_area` VALUES (1102, 96, '讷河市');
INSERT INTO `tao_area` VALUES (1103, 96, '其它区');
INSERT INTO `tao_area` VALUES (1104, 97, '鸡冠区');
INSERT INTO `tao_area` VALUES (1105, 97, '恒山区');
INSERT INTO `tao_area` VALUES (1106, 97, '滴道区');
INSERT INTO `tao_area` VALUES (1107, 97, '梨树区');
INSERT INTO `tao_area` VALUES (1108, 97, '城子河区');
INSERT INTO `tao_area` VALUES (1109, 97, '麻山区');
INSERT INTO `tao_area` VALUES (1110, 97, '鸡东县');
INSERT INTO `tao_area` VALUES (1111, 97, '虎林市');
INSERT INTO `tao_area` VALUES (1112, 97, '密山市');
INSERT INTO `tao_area` VALUES (1113, 97, '其它区');
INSERT INTO `tao_area` VALUES (1114, 98, '向阳区');
INSERT INTO `tao_area` VALUES (1115, 98, '工农区');
INSERT INTO `tao_area` VALUES (1116, 98, '南山区');
INSERT INTO `tao_area` VALUES (1117, 98, '兴安区');
INSERT INTO `tao_area` VALUES (1118, 98, '东山区');
INSERT INTO `tao_area` VALUES (1119, 98, '兴山区');
INSERT INTO `tao_area` VALUES (1120, 98, '萝北县');
INSERT INTO `tao_area` VALUES (1121, 98, '绥滨县');
INSERT INTO `tao_area` VALUES (1122, 98, '其它区');
INSERT INTO `tao_area` VALUES (1123, 99, '尖山区');
INSERT INTO `tao_area` VALUES (1124, 99, '岭东区');
INSERT INTO `tao_area` VALUES (1125, 99, '四方台区');
INSERT INTO `tao_area` VALUES (1126, 99, '宝山区');
INSERT INTO `tao_area` VALUES (1127, 99, '集贤县');
INSERT INTO `tao_area` VALUES (1128, 99, '友谊县');
INSERT INTO `tao_area` VALUES (1129, 99, '宝清县');
INSERT INTO `tao_area` VALUES (1130, 99, '饶河县');
INSERT INTO `tao_area` VALUES (1131, 99, '其它区');
INSERT INTO `tao_area` VALUES (1132, 100, '萨尔图区');
INSERT INTO `tao_area` VALUES (1133, 100, '龙凤区');
INSERT INTO `tao_area` VALUES (1134, 100, '让胡路区');
INSERT INTO `tao_area` VALUES (1135, 100, '红岗区');
INSERT INTO `tao_area` VALUES (1136, 100, '大同区');
INSERT INTO `tao_area` VALUES (1137, 100, '肇州县');
INSERT INTO `tao_area` VALUES (1138, 100, '肇源县');
INSERT INTO `tao_area` VALUES (1139, 100, '林甸县');
INSERT INTO `tao_area` VALUES (1140, 100, '杜尔伯特蒙古族自治县');
INSERT INTO `tao_area` VALUES (1141, 100, '其它区');
INSERT INTO `tao_area` VALUES (1142, 101, '伊春区');
INSERT INTO `tao_area` VALUES (1143, 101, '南岔区');
INSERT INTO `tao_area` VALUES (1144, 101, '友好区');
INSERT INTO `tao_area` VALUES (1145, 101, '西林区');
INSERT INTO `tao_area` VALUES (1146, 101, '翠峦区');
INSERT INTO `tao_area` VALUES (1147, 101, '新青区');
INSERT INTO `tao_area` VALUES (1148, 101, '美溪区');
INSERT INTO `tao_area` VALUES (1149, 101, '金山屯区');
INSERT INTO `tao_area` VALUES (1150, 101, '五营区');
INSERT INTO `tao_area` VALUES (1151, 101, '乌马河区');
INSERT INTO `tao_area` VALUES (1152, 101, '汤旺河区');
INSERT INTO `tao_area` VALUES (1153, 101, '带岭区');
INSERT INTO `tao_area` VALUES (1154, 101, '乌伊岭区');
INSERT INTO `tao_area` VALUES (1155, 101, '红星区');
INSERT INTO `tao_area` VALUES (1156, 101, '上甘岭区');
INSERT INTO `tao_area` VALUES (1157, 101, '嘉荫县');
INSERT INTO `tao_area` VALUES (1158, 101, '铁力市');
INSERT INTO `tao_area` VALUES (1159, 101, '其它区');
INSERT INTO `tao_area` VALUES (1160, 102, '永红区');
INSERT INTO `tao_area` VALUES (1161, 102, '向阳区');
INSERT INTO `tao_area` VALUES (1162, 102, '前进区');
INSERT INTO `tao_area` VALUES (1163, 102, '东风区');
INSERT INTO `tao_area` VALUES (1164, 102, '郊区');
INSERT INTO `tao_area` VALUES (1165, 102, '桦南县');
INSERT INTO `tao_area` VALUES (1166, 102, '桦川县');
INSERT INTO `tao_area` VALUES (1167, 102, '汤原县');
INSERT INTO `tao_area` VALUES (1168, 102, '抚远县');
INSERT INTO `tao_area` VALUES (1169, 102, '同江市');
INSERT INTO `tao_area` VALUES (1170, 102, '富锦市');
INSERT INTO `tao_area` VALUES (1171, 102, '其它区');
INSERT INTO `tao_area` VALUES (1172, 103, '新兴区');
INSERT INTO `tao_area` VALUES (1173, 103, '桃山区');
INSERT INTO `tao_area` VALUES (1174, 103, '茄子河区');
INSERT INTO `tao_area` VALUES (1175, 103, '勃利县');
INSERT INTO `tao_area` VALUES (1176, 103, '其它区');
INSERT INTO `tao_area` VALUES (1177, 104, '东安区');
INSERT INTO `tao_area` VALUES (1178, 104, '阳明区');
INSERT INTO `tao_area` VALUES (1179, 104, '爱民区');
INSERT INTO `tao_area` VALUES (1180, 104, '西安区');
INSERT INTO `tao_area` VALUES (1181, 104, '东宁县');
INSERT INTO `tao_area` VALUES (1182, 104, '林口县');
INSERT INTO `tao_area` VALUES (1183, 104, '绥芬河市');
INSERT INTO `tao_area` VALUES (1184, 104, '海林市');
INSERT INTO `tao_area` VALUES (1185, 104, '宁安市');
INSERT INTO `tao_area` VALUES (1186, 104, '穆棱市');
INSERT INTO `tao_area` VALUES (1187, 104, '其它区');
INSERT INTO `tao_area` VALUES (1188, 105, '爱辉区');
INSERT INTO `tao_area` VALUES (1189, 105, '嫩江县');
INSERT INTO `tao_area` VALUES (1190, 105, '逊克县');
INSERT INTO `tao_area` VALUES (1191, 105, '孙吴县');
INSERT INTO `tao_area` VALUES (1192, 105, '北安市');
INSERT INTO `tao_area` VALUES (1193, 105, '五大连池市');
INSERT INTO `tao_area` VALUES (1194, 105, '其它区');
INSERT INTO `tao_area` VALUES (1195, 106, '北林区');
INSERT INTO `tao_area` VALUES (1196, 106, '望奎县');
INSERT INTO `tao_area` VALUES (1197, 106, '兰西县');
INSERT INTO `tao_area` VALUES (1198, 106, '青冈县');
INSERT INTO `tao_area` VALUES (1199, 106, '庆安县');
INSERT INTO `tao_area` VALUES (1200, 106, '明水县');
INSERT INTO `tao_area` VALUES (1201, 106, '绥棱县');
INSERT INTO `tao_area` VALUES (1202, 106, '安达市');
INSERT INTO `tao_area` VALUES (1203, 106, '肇东市');
INSERT INTO `tao_area` VALUES (1204, 106, '海伦市');
INSERT INTO `tao_area` VALUES (1205, 106, '其它区');
INSERT INTO `tao_area` VALUES (1206, 107, '松岭区');
INSERT INTO `tao_area` VALUES (1207, 107, '新林区');
INSERT INTO `tao_area` VALUES (1208, 107, '呼中区');
INSERT INTO `tao_area` VALUES (1209, 107, '呼玛县');
INSERT INTO `tao_area` VALUES (1210, 107, '塔河县');
INSERT INTO `tao_area` VALUES (1211, 107, '漠河县');
INSERT INTO `tao_area` VALUES (1212, 107, '加格达奇区');
INSERT INTO `tao_area` VALUES (1213, 107, '其它区');
INSERT INTO `tao_area` VALUES (1214, 108, '黄浦区');
INSERT INTO `tao_area` VALUES (1215, 108, '卢湾区');
INSERT INTO `tao_area` VALUES (1216, 108, '徐汇区');
INSERT INTO `tao_area` VALUES (1217, 108, '长宁区');
INSERT INTO `tao_area` VALUES (1218, 108, '静安区');
INSERT INTO `tao_area` VALUES (1219, 108, '普陀区');
INSERT INTO `tao_area` VALUES (1220, 108, '闸北区');
INSERT INTO `tao_area` VALUES (1221, 108, '虹口区');
INSERT INTO `tao_area` VALUES (1222, 108, '杨浦区');
INSERT INTO `tao_area` VALUES (1223, 108, '闵行区');
INSERT INTO `tao_area` VALUES (1224, 108, '宝山区');
INSERT INTO `tao_area` VALUES (1225, 108, '嘉定区');
INSERT INTO `tao_area` VALUES (1226, 108, '浦东新区');
INSERT INTO `tao_area` VALUES (1227, 108, '金山区');
INSERT INTO `tao_area` VALUES (1228, 108, '松江区');
INSERT INTO `tao_area` VALUES (1229, 108, '青浦区');
INSERT INTO `tao_area` VALUES (1230, 108, '南汇区');
INSERT INTO `tao_area` VALUES (1231, 108, '奉贤区');
INSERT INTO `tao_area` VALUES (1232, 108, '川沙区');
INSERT INTO `tao_area` VALUES (1233, 108, '崇明县');
INSERT INTO `tao_area` VALUES (1234, 108, '其它区');
INSERT INTO `tao_area` VALUES (1235, 109, '玄武区');
INSERT INTO `tao_area` VALUES (1236, 109, '白下区');
INSERT INTO `tao_area` VALUES (1237, 109, '秦淮区');
INSERT INTO `tao_area` VALUES (1238, 109, '建邺区');
INSERT INTO `tao_area` VALUES (1239, 109, '鼓楼区');
INSERT INTO `tao_area` VALUES (1240, 109, '下关区');
INSERT INTO `tao_area` VALUES (1241, 109, '浦口区');
INSERT INTO `tao_area` VALUES (1242, 109, '栖霞区');
INSERT INTO `tao_area` VALUES (1243, 109, '雨花台区');
INSERT INTO `tao_area` VALUES (1244, 109, '江宁区');
INSERT INTO `tao_area` VALUES (1245, 109, '六合区');
INSERT INTO `tao_area` VALUES (1246, 109, '溧水区');
INSERT INTO `tao_area` VALUES (1247, 109, '高淳区');
INSERT INTO `tao_area` VALUES (1248, 109, '其它区');
INSERT INTO `tao_area` VALUES (1249, 110, '崇安区');
INSERT INTO `tao_area` VALUES (1250, 110, '南长区');
INSERT INTO `tao_area` VALUES (1251, 110, '北塘区');
INSERT INTO `tao_area` VALUES (1252, 110, '锡山区');
INSERT INTO `tao_area` VALUES (1253, 110, '惠山区');
INSERT INTO `tao_area` VALUES (1254, 110, '滨湖区');
INSERT INTO `tao_area` VALUES (1255, 110, '江阴市');
INSERT INTO `tao_area` VALUES (1256, 110, '宜兴市');
INSERT INTO `tao_area` VALUES (1257, 110, '新区');
INSERT INTO `tao_area` VALUES (1258, 110, '其它区');
INSERT INTO `tao_area` VALUES (1259, 111, '鼓楼区');
INSERT INTO `tao_area` VALUES (1260, 111, '云龙区');
INSERT INTO `tao_area` VALUES (1261, 111, '九里区');
INSERT INTO `tao_area` VALUES (1262, 111, '贾汪区');
INSERT INTO `tao_area` VALUES (1263, 111, '泉山区');
INSERT INTO `tao_area` VALUES (1264, 111, '丰县');
INSERT INTO `tao_area` VALUES (1265, 111, '沛县');
INSERT INTO `tao_area` VALUES (1266, 111, '铜山区');
INSERT INTO `tao_area` VALUES (1267, 111, '睢宁县');
INSERT INTO `tao_area` VALUES (1268, 111, '新沂市');
INSERT INTO `tao_area` VALUES (1269, 111, '邳州市');
INSERT INTO `tao_area` VALUES (1270, 111, '其它区');
INSERT INTO `tao_area` VALUES (1271, 112, '天宁区');
INSERT INTO `tao_area` VALUES (1272, 112, '钟楼区');
INSERT INTO `tao_area` VALUES (1273, 112, '戚墅堰区');
INSERT INTO `tao_area` VALUES (1274, 112, '新北区');
INSERT INTO `tao_area` VALUES (1275, 112, '武进区');
INSERT INTO `tao_area` VALUES (1276, 112, '溧阳市');
INSERT INTO `tao_area` VALUES (1277, 112, '金坛市');
INSERT INTO `tao_area` VALUES (1278, 112, '其它区');
INSERT INTO `tao_area` VALUES (1279, 113, '沧浪区');
INSERT INTO `tao_area` VALUES (1280, 113, '平江区');
INSERT INTO `tao_area` VALUES (1281, 113, '金阊区');
INSERT INTO `tao_area` VALUES (1282, 113, '虎丘区');
INSERT INTO `tao_area` VALUES (1283, 113, '吴中区');
INSERT INTO `tao_area` VALUES (1284, 113, '相城区');
INSERT INTO `tao_area` VALUES (1285, 113, '姑苏区');
INSERT INTO `tao_area` VALUES (1286, 113, '常熟市');
INSERT INTO `tao_area` VALUES (1287, 113, '张家港市');
INSERT INTO `tao_area` VALUES (1288, 113, '昆山市');
INSERT INTO `tao_area` VALUES (1289, 113, '吴江区');
INSERT INTO `tao_area` VALUES (1290, 113, '太仓市');
INSERT INTO `tao_area` VALUES (1291, 113, '新区');
INSERT INTO `tao_area` VALUES (1292, 113, '园区');
INSERT INTO `tao_area` VALUES (1293, 113, '其它区');
INSERT INTO `tao_area` VALUES (1294, 114, '崇川区');
INSERT INTO `tao_area` VALUES (1295, 114, '港闸区');
INSERT INTO `tao_area` VALUES (1296, 114, '通州区');
INSERT INTO `tao_area` VALUES (1297, 114, '海安县');
INSERT INTO `tao_area` VALUES (1298, 114, '如东县');
INSERT INTO `tao_area` VALUES (1299, 114, '启东市');
INSERT INTO `tao_area` VALUES (1300, 114, '如皋市');
INSERT INTO `tao_area` VALUES (1301, 114, '通州市');
INSERT INTO `tao_area` VALUES (1302, 114, '海门市');
INSERT INTO `tao_area` VALUES (1303, 114, '开发区');
INSERT INTO `tao_area` VALUES (1304, 114, '其它区');
INSERT INTO `tao_area` VALUES (1305, 115, '连云区');
INSERT INTO `tao_area` VALUES (1306, 115, '新浦区');
INSERT INTO `tao_area` VALUES (1307, 115, '海州区');
INSERT INTO `tao_area` VALUES (1308, 115, '赣榆区');
INSERT INTO `tao_area` VALUES (1309, 115, '东海县');
INSERT INTO `tao_area` VALUES (1310, 115, '灌云县');
INSERT INTO `tao_area` VALUES (1311, 115, '灌南县');
INSERT INTO `tao_area` VALUES (1312, 115, '其它区');
INSERT INTO `tao_area` VALUES (1313, 116, '清河区');
INSERT INTO `tao_area` VALUES (1314, 116, '淮安区');
INSERT INTO `tao_area` VALUES (1315, 116, '淮阴区');
INSERT INTO `tao_area` VALUES (1316, 116, '清浦区');
INSERT INTO `tao_area` VALUES (1317, 116, '涟水县');
INSERT INTO `tao_area` VALUES (1318, 116, '洪泽县');
INSERT INTO `tao_area` VALUES (1319, 116, '盱眙县');
INSERT INTO `tao_area` VALUES (1320, 116, '金湖县');
INSERT INTO `tao_area` VALUES (1321, 116, '其它区');
INSERT INTO `tao_area` VALUES (1322, 117, '亭湖区');
INSERT INTO `tao_area` VALUES (1323, 117, '盐都区');
INSERT INTO `tao_area` VALUES (1324, 117, '响水县');
INSERT INTO `tao_area` VALUES (1325, 117, '滨海县');
INSERT INTO `tao_area` VALUES (1326, 117, '阜宁县');
INSERT INTO `tao_area` VALUES (1327, 117, '射阳县');
INSERT INTO `tao_area` VALUES (1328, 117, '建湖县');
INSERT INTO `tao_area` VALUES (1329, 117, '东台市');
INSERT INTO `tao_area` VALUES (1330, 117, '大丰市');
INSERT INTO `tao_area` VALUES (1331, 117, '其它区');
INSERT INTO `tao_area` VALUES (1332, 118, '广陵区');
INSERT INTO `tao_area` VALUES (1333, 118, '邗江区');
INSERT INTO `tao_area` VALUES (1334, 118, '维扬区');
INSERT INTO `tao_area` VALUES (1335, 118, '宝应县');
INSERT INTO `tao_area` VALUES (1336, 118, '仪征市');
INSERT INTO `tao_area` VALUES (1337, 118, '高邮市');
INSERT INTO `tao_area` VALUES (1338, 118, '江都区');
INSERT INTO `tao_area` VALUES (1339, 118, '经济开发区');
INSERT INTO `tao_area` VALUES (1340, 118, '其它区');
INSERT INTO `tao_area` VALUES (1341, 119, '京口区');
INSERT INTO `tao_area` VALUES (1342, 119, '润州区');
INSERT INTO `tao_area` VALUES (1343, 119, '丹徒区');
INSERT INTO `tao_area` VALUES (1344, 119, '丹阳市');
INSERT INTO `tao_area` VALUES (1345, 119, '扬中市');
INSERT INTO `tao_area` VALUES (1346, 119, '句容市');
INSERT INTO `tao_area` VALUES (1347, 119, '其它区');
INSERT INTO `tao_area` VALUES (1348, 120, '海陵区');
INSERT INTO `tao_area` VALUES (1349, 120, '高港区');
INSERT INTO `tao_area` VALUES (1350, 120, '兴化市');
INSERT INTO `tao_area` VALUES (1351, 120, '靖江市');
INSERT INTO `tao_area` VALUES (1352, 120, '泰兴市');
INSERT INTO `tao_area` VALUES (1353, 120, '姜堰区');
INSERT INTO `tao_area` VALUES (1354, 120, '其它区');
INSERT INTO `tao_area` VALUES (1355, 121, '宿城区');
INSERT INTO `tao_area` VALUES (1356, 121, '宿豫区');
INSERT INTO `tao_area` VALUES (1357, 121, '沭阳县');
INSERT INTO `tao_area` VALUES (1358, 121, '泗阳县');
INSERT INTO `tao_area` VALUES (1359, 121, '泗洪县');
INSERT INTO `tao_area` VALUES (1360, 121, '其它区');
INSERT INTO `tao_area` VALUES (1361, 122, '上城区');
INSERT INTO `tao_area` VALUES (1362, 122, '下城区');
INSERT INTO `tao_area` VALUES (1363, 122, '江干区');
INSERT INTO `tao_area` VALUES (1364, 122, '拱墅区');
INSERT INTO `tao_area` VALUES (1365, 122, '西湖区');
INSERT INTO `tao_area` VALUES (1366, 122, '滨江区');
INSERT INTO `tao_area` VALUES (1367, 122, '萧山区');
INSERT INTO `tao_area` VALUES (1368, 122, '余杭区');
INSERT INTO `tao_area` VALUES (1369, 122, '桐庐县');
INSERT INTO `tao_area` VALUES (1370, 122, '淳安县');
INSERT INTO `tao_area` VALUES (1371, 122, '建德市');
INSERT INTO `tao_area` VALUES (1372, 122, '富阳区');
INSERT INTO `tao_area` VALUES (1373, 122, '临安市');
INSERT INTO `tao_area` VALUES (1374, 122, '其它区');
INSERT INTO `tao_area` VALUES (1375, 123, '海曙区');
INSERT INTO `tao_area` VALUES (1376, 123, '江东区');
INSERT INTO `tao_area` VALUES (1377, 123, '江北区');
INSERT INTO `tao_area` VALUES (1378, 123, '北仑区');
INSERT INTO `tao_area` VALUES (1379, 123, '镇海区');
INSERT INTO `tao_area` VALUES (1380, 123, '鄞州区');
INSERT INTO `tao_area` VALUES (1381, 123, '象山县');
INSERT INTO `tao_area` VALUES (1382, 123, '宁海县');
INSERT INTO `tao_area` VALUES (1383, 123, '余姚市');
INSERT INTO `tao_area` VALUES (1384, 123, '慈溪市');
INSERT INTO `tao_area` VALUES (1385, 123, '奉化市');
INSERT INTO `tao_area` VALUES (1386, 123, '其它区');
INSERT INTO `tao_area` VALUES (1387, 124, '鹿城区');
INSERT INTO `tao_area` VALUES (1388, 124, '龙湾区');
INSERT INTO `tao_area` VALUES (1389, 124, '瓯海区');
INSERT INTO `tao_area` VALUES (1390, 124, '洞头县');
INSERT INTO `tao_area` VALUES (1391, 124, '永嘉县');
INSERT INTO `tao_area` VALUES (1392, 124, '平阳县');
INSERT INTO `tao_area` VALUES (1393, 124, '苍南县');
INSERT INTO `tao_area` VALUES (1394, 124, '文成县');
INSERT INTO `tao_area` VALUES (1395, 124, '泰顺县');
INSERT INTO `tao_area` VALUES (1396, 124, '瑞安市');
INSERT INTO `tao_area` VALUES (1397, 124, '乐清市');
INSERT INTO `tao_area` VALUES (1398, 124, '其它区');
INSERT INTO `tao_area` VALUES (1399, 125, '南湖区');
INSERT INTO `tao_area` VALUES (1400, 125, '秀洲区');
INSERT INTO `tao_area` VALUES (1401, 125, '嘉善县');
INSERT INTO `tao_area` VALUES (1402, 125, '海盐县');
INSERT INTO `tao_area` VALUES (1403, 125, '海宁市');
INSERT INTO `tao_area` VALUES (1404, 125, '平湖市');
INSERT INTO `tao_area` VALUES (1405, 125, '桐乡市');
INSERT INTO `tao_area` VALUES (1406, 125, '其它区');
INSERT INTO `tao_area` VALUES (1407, 126, '吴兴区');
INSERT INTO `tao_area` VALUES (1408, 126, '南浔区');
INSERT INTO `tao_area` VALUES (1409, 126, '德清县');
INSERT INTO `tao_area` VALUES (1410, 126, '长兴县');
INSERT INTO `tao_area` VALUES (1411, 126, '安吉县');
INSERT INTO `tao_area` VALUES (1412, 126, '其它区');
INSERT INTO `tao_area` VALUES (1413, 127, '越城区');
INSERT INTO `tao_area` VALUES (1414, 127, '柯桥区');
INSERT INTO `tao_area` VALUES (1415, 127, '新昌县');
INSERT INTO `tao_area` VALUES (1416, 127, '诸暨市');
INSERT INTO `tao_area` VALUES (1417, 127, '上虞区');
INSERT INTO `tao_area` VALUES (1418, 127, '嵊州市');
INSERT INTO `tao_area` VALUES (1419, 127, '其它区');
INSERT INTO `tao_area` VALUES (1420, 128, '婺城区');
INSERT INTO `tao_area` VALUES (1421, 128, '金东区');
INSERT INTO `tao_area` VALUES (1422, 128, '武义县');
INSERT INTO `tao_area` VALUES (1423, 128, '浦江县');
INSERT INTO `tao_area` VALUES (1424, 128, '磐安县');
INSERT INTO `tao_area` VALUES (1425, 128, '兰溪市');
INSERT INTO `tao_area` VALUES (1426, 128, '义乌市');
INSERT INTO `tao_area` VALUES (1427, 128, '东阳市');
INSERT INTO `tao_area` VALUES (1428, 128, '永康市');
INSERT INTO `tao_area` VALUES (1429, 128, '其它区');
INSERT INTO `tao_area` VALUES (1430, 129, '柯城区');
INSERT INTO `tao_area` VALUES (1431, 129, '衢江区');
INSERT INTO `tao_area` VALUES (1432, 129, '常山县');
INSERT INTO `tao_area` VALUES (1433, 129, '开化县');
INSERT INTO `tao_area` VALUES (1434, 129, '龙游县');
INSERT INTO `tao_area` VALUES (1435, 129, '江山市');
INSERT INTO `tao_area` VALUES (1436, 129, '其它区');
INSERT INTO `tao_area` VALUES (1437, 130, '定海区');
INSERT INTO `tao_area` VALUES (1438, 130, '普陀区');
INSERT INTO `tao_area` VALUES (1439, 130, '岱山县');
INSERT INTO `tao_area` VALUES (1440, 130, '嵊泗县');
INSERT INTO `tao_area` VALUES (1441, 130, '其它区');
INSERT INTO `tao_area` VALUES (1442, 131, '椒江区');
INSERT INTO `tao_area` VALUES (1443, 131, '黄岩区');
INSERT INTO `tao_area` VALUES (1444, 131, '路桥区');
INSERT INTO `tao_area` VALUES (1445, 131, '玉环县');
INSERT INTO `tao_area` VALUES (1446, 131, '三门县');
INSERT INTO `tao_area` VALUES (1447, 131, '天台县');
INSERT INTO `tao_area` VALUES (1448, 131, '仙居县');
INSERT INTO `tao_area` VALUES (1449, 131, '温岭市');
INSERT INTO `tao_area` VALUES (1450, 131, '临海市');
INSERT INTO `tao_area` VALUES (1451, 131, '其它区');
INSERT INTO `tao_area` VALUES (1452, 132, '莲都区');
INSERT INTO `tao_area` VALUES (1453, 132, '青田县');
INSERT INTO `tao_area` VALUES (1454, 132, '缙云县');
INSERT INTO `tao_area` VALUES (1455, 132, '遂昌县');
INSERT INTO `tao_area` VALUES (1456, 132, '松阳县');
INSERT INTO `tao_area` VALUES (1457, 132, '云和县');
INSERT INTO `tao_area` VALUES (1458, 132, '庆元县');
INSERT INTO `tao_area` VALUES (1459, 132, '景宁畲族自治县');
INSERT INTO `tao_area` VALUES (1460, 132, '龙泉市');
INSERT INTO `tao_area` VALUES (1461, 132, '其它区');
INSERT INTO `tao_area` VALUES (1462, 133, '瑶海区');
INSERT INTO `tao_area` VALUES (1463, 133, '庐阳区');
INSERT INTO `tao_area` VALUES (1464, 133, '蜀山区');
INSERT INTO `tao_area` VALUES (1465, 133, '包河区');
INSERT INTO `tao_area` VALUES (1466, 133, '长丰县');
INSERT INTO `tao_area` VALUES (1467, 133, '肥东县');
INSERT INTO `tao_area` VALUES (1468, 133, '肥西县');
INSERT INTO `tao_area` VALUES (1469, 133, '高新区');
INSERT INTO `tao_area` VALUES (1470, 133, '中区');
INSERT INTO `tao_area` VALUES (1471, 133, '其它区');
INSERT INTO `tao_area` VALUES (1472, 134, '镜湖区');
INSERT INTO `tao_area` VALUES (1473, 134, '弋江区');
INSERT INTO `tao_area` VALUES (1474, 134, '鸠江区');
INSERT INTO `tao_area` VALUES (1475, 134, '三山区');
INSERT INTO `tao_area` VALUES (1476, 134, '芜湖县');
INSERT INTO `tao_area` VALUES (1477, 134, '繁昌县');
INSERT INTO `tao_area` VALUES (1478, 134, '南陵县');
INSERT INTO `tao_area` VALUES (1479, 134, '其它区');
INSERT INTO `tao_area` VALUES (1480, 135, '龙子湖区');
INSERT INTO `tao_area` VALUES (1481, 135, '蚌山区');
INSERT INTO `tao_area` VALUES (1482, 135, '禹会区');
INSERT INTO `tao_area` VALUES (1483, 135, '淮上区');
INSERT INTO `tao_area` VALUES (1484, 135, '怀远县');
INSERT INTO `tao_area` VALUES (1485, 135, '五河县');
INSERT INTO `tao_area` VALUES (1486, 135, '固镇县');
INSERT INTO `tao_area` VALUES (1487, 135, '其它区');
INSERT INTO `tao_area` VALUES (1488, 136, '大通区');
INSERT INTO `tao_area` VALUES (1489, 136, '田家庵区');
INSERT INTO `tao_area` VALUES (1490, 136, '谢家集区');
INSERT INTO `tao_area` VALUES (1491, 136, '八公山区');
INSERT INTO `tao_area` VALUES (1492, 136, '潘集区');
INSERT INTO `tao_area` VALUES (1493, 136, '凤台县');
INSERT INTO `tao_area` VALUES (1494, 136, '其它区');
INSERT INTO `tao_area` VALUES (1495, 137, '金家庄区');
INSERT INTO `tao_area` VALUES (1496, 137, '花山区');
INSERT INTO `tao_area` VALUES (1497, 137, '雨山区');
INSERT INTO `tao_area` VALUES (1498, 137, '博望区');
INSERT INTO `tao_area` VALUES (1499, 137, '当涂县');
INSERT INTO `tao_area` VALUES (1500, 137, '其它区');
INSERT INTO `tao_area` VALUES (1501, 138, '杜集区');
INSERT INTO `tao_area` VALUES (1502, 138, '相山区');
INSERT INTO `tao_area` VALUES (1503, 138, '烈山区');
INSERT INTO `tao_area` VALUES (1504, 138, '濉溪县');
INSERT INTO `tao_area` VALUES (1505, 138, '其它区');
INSERT INTO `tao_area` VALUES (1506, 139, '铜官山区');
INSERT INTO `tao_area` VALUES (1507, 139, '狮子山区');
INSERT INTO `tao_area` VALUES (1508, 139, '郊区');
INSERT INTO `tao_area` VALUES (1509, 139, '铜陵县');
INSERT INTO `tao_area` VALUES (1510, 139, '其它区');
INSERT INTO `tao_area` VALUES (1511, 140, '迎江区');
INSERT INTO `tao_area` VALUES (1512, 140, '大观区');
INSERT INTO `tao_area` VALUES (1513, 140, '宜秀区');
INSERT INTO `tao_area` VALUES (1514, 140, '怀宁县');
INSERT INTO `tao_area` VALUES (1515, 140, '枞阳县');
INSERT INTO `tao_area` VALUES (1516, 140, '潜山县');
INSERT INTO `tao_area` VALUES (1517, 140, '太湖县');
INSERT INTO `tao_area` VALUES (1518, 140, '宿松县');
INSERT INTO `tao_area` VALUES (1519, 140, '望江县');
INSERT INTO `tao_area` VALUES (1520, 140, '岳西县');
INSERT INTO `tao_area` VALUES (1521, 140, '桐城市');
INSERT INTO `tao_area` VALUES (1522, 140, '其它区');
INSERT INTO `tao_area` VALUES (1523, 141, '屯溪区');
INSERT INTO `tao_area` VALUES (1524, 141, '黄山区');
INSERT INTO `tao_area` VALUES (1525, 141, '徽州区');
INSERT INTO `tao_area` VALUES (1526, 141, '歙县');
INSERT INTO `tao_area` VALUES (1527, 141, '休宁县');
INSERT INTO `tao_area` VALUES (1528, 141, '黟县');
INSERT INTO `tao_area` VALUES (1529, 141, '祁门县');
INSERT INTO `tao_area` VALUES (1530, 141, '其它区');
INSERT INTO `tao_area` VALUES (1531, 142, '琅琊区');
INSERT INTO `tao_area` VALUES (1532, 142, '南谯区');
INSERT INTO `tao_area` VALUES (1533, 142, '来安县');
INSERT INTO `tao_area` VALUES (1534, 142, '全椒县');
INSERT INTO `tao_area` VALUES (1535, 142, '定远县');
INSERT INTO `tao_area` VALUES (1536, 142, '凤阳县');
INSERT INTO `tao_area` VALUES (1537, 142, '天长市');
INSERT INTO `tao_area` VALUES (1538, 142, '明光市');
INSERT INTO `tao_area` VALUES (1539, 142, '其它区');
INSERT INTO `tao_area` VALUES (1540, 143, '颍州区');
INSERT INTO `tao_area` VALUES (1541, 143, '颍东区');
INSERT INTO `tao_area` VALUES (1542, 143, '颍泉区');
INSERT INTO `tao_area` VALUES (1543, 143, '临泉县');
INSERT INTO `tao_area` VALUES (1544, 143, '太和县');
INSERT INTO `tao_area` VALUES (1545, 143, '阜南县');
INSERT INTO `tao_area` VALUES (1546, 143, '颍上县');
INSERT INTO `tao_area` VALUES (1547, 143, '界首市');
INSERT INTO `tao_area` VALUES (1548, 143, '其它区');
INSERT INTO `tao_area` VALUES (1549, 144, '埇桥区');
INSERT INTO `tao_area` VALUES (1550, 144, '砀山县');
INSERT INTO `tao_area` VALUES (1551, 144, '萧县');
INSERT INTO `tao_area` VALUES (1552, 144, '灵璧县');
INSERT INTO `tao_area` VALUES (1553, 144, '泗县');
INSERT INTO `tao_area` VALUES (1554, 144, '其它区');
INSERT INTO `tao_area` VALUES (1555, 133, '巢湖市');
INSERT INTO `tao_area` VALUES (1556, 133, '居巢区');
INSERT INTO `tao_area` VALUES (1557, 133, '庐江县');
INSERT INTO `tao_area` VALUES (1558, 134, '无为县');
INSERT INTO `tao_area` VALUES (1559, 137, '含山县');
INSERT INTO `tao_area` VALUES (1560, 137, '和县');
INSERT INTO `tao_area` VALUES (1561, 145, '金安区');
INSERT INTO `tao_area` VALUES (1562, 145, '裕安区');
INSERT INTO `tao_area` VALUES (1563, 145, '寿县');
INSERT INTO `tao_area` VALUES (1564, 145, '霍邱县');
INSERT INTO `tao_area` VALUES (1565, 145, '舒城县');
INSERT INTO `tao_area` VALUES (1566, 145, '金寨县');
INSERT INTO `tao_area` VALUES (1567, 145, '霍山县');
INSERT INTO `tao_area` VALUES (1568, 145, '其它区');
INSERT INTO `tao_area` VALUES (1569, 146, '谯城区');
INSERT INTO `tao_area` VALUES (1570, 146, '涡阳县');
INSERT INTO `tao_area` VALUES (1571, 146, '蒙城县');
INSERT INTO `tao_area` VALUES (1572, 146, '利辛县');
INSERT INTO `tao_area` VALUES (1573, 146, '其它区');
INSERT INTO `tao_area` VALUES (1574, 147, '贵池区');
INSERT INTO `tao_area` VALUES (1575, 147, '东至县');
INSERT INTO `tao_area` VALUES (1576, 147, '石台县');
INSERT INTO `tao_area` VALUES (1577, 147, '青阳县');
INSERT INTO `tao_area` VALUES (1578, 147, '其它区');
INSERT INTO `tao_area` VALUES (1579, 148, '宣州区');
INSERT INTO `tao_area` VALUES (1580, 148, '郎溪县');
INSERT INTO `tao_area` VALUES (1581, 148, '广德县');
INSERT INTO `tao_area` VALUES (1582, 148, '泾县');
INSERT INTO `tao_area` VALUES (1583, 148, '绩溪县');
INSERT INTO `tao_area` VALUES (1584, 148, '旌德县');
INSERT INTO `tao_area` VALUES (1585, 148, '宁国市');
INSERT INTO `tao_area` VALUES (1586, 148, '其它区');
INSERT INTO `tao_area` VALUES (1587, 149, '鼓楼区');
INSERT INTO `tao_area` VALUES (1588, 149, '台江区');
INSERT INTO `tao_area` VALUES (1589, 149, '仓山区');
INSERT INTO `tao_area` VALUES (1590, 149, '马尾区');
INSERT INTO `tao_area` VALUES (1591, 149, '晋安区');
INSERT INTO `tao_area` VALUES (1592, 149, '闽侯县');
INSERT INTO `tao_area` VALUES (1593, 149, '连江县');
INSERT INTO `tao_area` VALUES (1594, 149, '罗源县');
INSERT INTO `tao_area` VALUES (1595, 149, '闽清县');
INSERT INTO `tao_area` VALUES (1596, 149, '永泰县');
INSERT INTO `tao_area` VALUES (1597, 149, '平潭县');
INSERT INTO `tao_area` VALUES (1598, 149, '福清市');
INSERT INTO `tao_area` VALUES (1599, 149, '长乐市');
INSERT INTO `tao_area` VALUES (1600, 149, '其它区');
INSERT INTO `tao_area` VALUES (1601, 150, '思明区');
INSERT INTO `tao_area` VALUES (1602, 150, '海沧区');
INSERT INTO `tao_area` VALUES (1603, 150, '湖里区');
INSERT INTO `tao_area` VALUES (1604, 150, '集美区');
INSERT INTO `tao_area` VALUES (1605, 150, '同安区');
INSERT INTO `tao_area` VALUES (1606, 150, '翔安区');
INSERT INTO `tao_area` VALUES (1607, 150, '其它区');
INSERT INTO `tao_area` VALUES (1608, 151, '城厢区');
INSERT INTO `tao_area` VALUES (1609, 151, '涵江区');
INSERT INTO `tao_area` VALUES (1610, 151, '荔城区');
INSERT INTO `tao_area` VALUES (1611, 151, '秀屿区');
INSERT INTO `tao_area` VALUES (1612, 151, '仙游县');
INSERT INTO `tao_area` VALUES (1613, 151, '其它区');
INSERT INTO `tao_area` VALUES (1614, 152, '梅列区');
INSERT INTO `tao_area` VALUES (1615, 152, '三元区');
INSERT INTO `tao_area` VALUES (1616, 152, '明溪县');
INSERT INTO `tao_area` VALUES (1617, 152, '清流县');
INSERT INTO `tao_area` VALUES (1618, 152, '宁化县');
INSERT INTO `tao_area` VALUES (1619, 152, '大田县');
INSERT INTO `tao_area` VALUES (1620, 152, '尤溪县');
INSERT INTO `tao_area` VALUES (1621, 152, '沙县');
INSERT INTO `tao_area` VALUES (1622, 152, '将乐县');
INSERT INTO `tao_area` VALUES (1623, 152, '泰宁县');
INSERT INTO `tao_area` VALUES (1624, 152, '建宁县');
INSERT INTO `tao_area` VALUES (1625, 152, '永安市');
INSERT INTO `tao_area` VALUES (1626, 152, '其它区');
INSERT INTO `tao_area` VALUES (1627, 153, '鲤城区');
INSERT INTO `tao_area` VALUES (1628, 153, '丰泽区');
INSERT INTO `tao_area` VALUES (1629, 153, '洛江区');
INSERT INTO `tao_area` VALUES (1630, 153, '泉港区');
INSERT INTO `tao_area` VALUES (1631, 153, '惠安县');
INSERT INTO `tao_area` VALUES (1632, 153, '安溪县');
INSERT INTO `tao_area` VALUES (1633, 153, '永春县');
INSERT INTO `tao_area` VALUES (1634, 153, '德化县');
INSERT INTO `tao_area` VALUES (1635, 153, '金门县');
INSERT INTO `tao_area` VALUES (1636, 153, '石狮市');
INSERT INTO `tao_area` VALUES (1637, 153, '晋江市');
INSERT INTO `tao_area` VALUES (1638, 153, '南安市');
INSERT INTO `tao_area` VALUES (1639, 153, '其它区');
INSERT INTO `tao_area` VALUES (1640, 154, '芗城区');
INSERT INTO `tao_area` VALUES (1641, 154, '龙文区');
INSERT INTO `tao_area` VALUES (1642, 154, '云霄县');
INSERT INTO `tao_area` VALUES (1643, 154, '漳浦县');
INSERT INTO `tao_area` VALUES (1644, 154, '诏安县');
INSERT INTO `tao_area` VALUES (1645, 154, '长泰县');
INSERT INTO `tao_area` VALUES (1646, 154, '东山县');
INSERT INTO `tao_area` VALUES (1647, 154, '南靖县');
INSERT INTO `tao_area` VALUES (1648, 154, '平和县');
INSERT INTO `tao_area` VALUES (1649, 154, '华安县');
INSERT INTO `tao_area` VALUES (1650, 154, '龙海市');
INSERT INTO `tao_area` VALUES (1651, 154, '其它区');
INSERT INTO `tao_area` VALUES (1652, 155, '延平区');
INSERT INTO `tao_area` VALUES (1653, 155, '顺昌县');
INSERT INTO `tao_area` VALUES (1654, 155, '浦城县');
INSERT INTO `tao_area` VALUES (1655, 155, '光泽县');
INSERT INTO `tao_area` VALUES (1656, 155, '松溪县');
INSERT INTO `tao_area` VALUES (1657, 155, '政和县');
INSERT INTO `tao_area` VALUES (1658, 155, '邵武市');
INSERT INTO `tao_area` VALUES (1659, 155, '武夷山市');
INSERT INTO `tao_area` VALUES (1660, 155, '建瓯市');
INSERT INTO `tao_area` VALUES (1661, 155, '建阳区');
INSERT INTO `tao_area` VALUES (1662, 155, '其它区');
INSERT INTO `tao_area` VALUES (1663, 156, '新罗区');
INSERT INTO `tao_area` VALUES (1664, 156, '长汀县');
INSERT INTO `tao_area` VALUES (1665, 156, '永定区');
INSERT INTO `tao_area` VALUES (1666, 156, '上杭县');
INSERT INTO `tao_area` VALUES (1667, 156, '武平县');
INSERT INTO `tao_area` VALUES (1668, 156, '连城县');
INSERT INTO `tao_area` VALUES (1669, 156, '漳平市');
INSERT INTO `tao_area` VALUES (1670, 156, '其它区');
INSERT INTO `tao_area` VALUES (1671, 157, '蕉城区');
INSERT INTO `tao_area` VALUES (1672, 157, '霞浦县');
INSERT INTO `tao_area` VALUES (1673, 157, '古田县');
INSERT INTO `tao_area` VALUES (1674, 157, '屏南县');
INSERT INTO `tao_area` VALUES (1675, 157, '寿宁县');
INSERT INTO `tao_area` VALUES (1676, 157, '周宁县');
INSERT INTO `tao_area` VALUES (1677, 157, '柘荣县');
INSERT INTO `tao_area` VALUES (1678, 157, '福安市');
INSERT INTO `tao_area` VALUES (1679, 157, '福鼎市');
INSERT INTO `tao_area` VALUES (1680, 157, '其它区');
INSERT INTO `tao_area` VALUES (1681, 158, '东湖区');
INSERT INTO `tao_area` VALUES (1682, 158, '西湖区');
INSERT INTO `tao_area` VALUES (1683, 158, '青云谱区');
INSERT INTO `tao_area` VALUES (1684, 158, '湾里区');
INSERT INTO `tao_area` VALUES (1685, 158, '青山湖区');
INSERT INTO `tao_area` VALUES (1686, 158, '南昌县');
INSERT INTO `tao_area` VALUES (1687, 158, '新建县');
INSERT INTO `tao_area` VALUES (1688, 158, '安义县');
INSERT INTO `tao_area` VALUES (1689, 158, '进贤县');
INSERT INTO `tao_area` VALUES (1690, 158, '红谷滩新区');
INSERT INTO `tao_area` VALUES (1691, 158, '经济技术开发区');
INSERT INTO `tao_area` VALUES (1692, 158, '昌北区');
INSERT INTO `tao_area` VALUES (1693, 158, '其它区');
INSERT INTO `tao_area` VALUES (1694, 159, '昌江区');
INSERT INTO `tao_area` VALUES (1695, 159, '珠山区');
INSERT INTO `tao_area` VALUES (1696, 159, '浮梁县');
INSERT INTO `tao_area` VALUES (1697, 159, '乐平市');
INSERT INTO `tao_area` VALUES (1698, 159, '其它区');
INSERT INTO `tao_area` VALUES (1699, 160, '安源区');
INSERT INTO `tao_area` VALUES (1700, 160, '湘东区');
INSERT INTO `tao_area` VALUES (1701, 160, '莲花县');
INSERT INTO `tao_area` VALUES (1702, 160, '上栗县');
INSERT INTO `tao_area` VALUES (1703, 160, '芦溪县');
INSERT INTO `tao_area` VALUES (1704, 160, '其它区');
INSERT INTO `tao_area` VALUES (1705, 161, '庐山区');
INSERT INTO `tao_area` VALUES (1706, 161, '浔阳区');
INSERT INTO `tao_area` VALUES (1707, 161, '九江县');
INSERT INTO `tao_area` VALUES (1708, 161, '武宁县');
INSERT INTO `tao_area` VALUES (1709, 161, '修水县');
INSERT INTO `tao_area` VALUES (1710, 161, '永修县');
INSERT INTO `tao_area` VALUES (1711, 161, '德安县');
INSERT INTO `tao_area` VALUES (1712, 161, '星子县');
INSERT INTO `tao_area` VALUES (1713, 161, '都昌县');
INSERT INTO `tao_area` VALUES (1714, 161, '湖口县');
INSERT INTO `tao_area` VALUES (1715, 161, '彭泽县');
INSERT INTO `tao_area` VALUES (1716, 161, '瑞昌市');
INSERT INTO `tao_area` VALUES (1717, 161, '其它区');
INSERT INTO `tao_area` VALUES (1718, 161, '共青城市');
INSERT INTO `tao_area` VALUES (1719, 162, '渝水区');
INSERT INTO `tao_area` VALUES (1720, 162, '分宜县');
INSERT INTO `tao_area` VALUES (1721, 162, '其它区');
INSERT INTO `tao_area` VALUES (1722, 163, '月湖区');
INSERT INTO `tao_area` VALUES (1723, 163, '余江县');
INSERT INTO `tao_area` VALUES (1724, 163, '贵溪市');
INSERT INTO `tao_area` VALUES (1725, 163, '其它区');
INSERT INTO `tao_area` VALUES (1726, 164, '章贡区');
INSERT INTO `tao_area` VALUES (1727, 164, '赣县');
INSERT INTO `tao_area` VALUES (1728, 164, '信丰县');
INSERT INTO `tao_area` VALUES (1729, 164, '大余县');
INSERT INTO `tao_area` VALUES (1730, 164, '上犹县');
INSERT INTO `tao_area` VALUES (1731, 164, '崇义县');
INSERT INTO `tao_area` VALUES (1732, 164, '安远县');
INSERT INTO `tao_area` VALUES (1733, 164, '龙南县');
INSERT INTO `tao_area` VALUES (1734, 164, '定南县');
INSERT INTO `tao_area` VALUES (1735, 164, '全南县');
INSERT INTO `tao_area` VALUES (1736, 164, '宁都县');
INSERT INTO `tao_area` VALUES (1737, 164, '于都县');
INSERT INTO `tao_area` VALUES (1738, 164, '兴国县');
INSERT INTO `tao_area` VALUES (1739, 164, '会昌县');
INSERT INTO `tao_area` VALUES (1740, 164, '寻乌县');
INSERT INTO `tao_area` VALUES (1741, 164, '石城县');
INSERT INTO `tao_area` VALUES (1742, 164, '黄金区');
INSERT INTO `tao_area` VALUES (1743, 164, '瑞金市');
INSERT INTO `tao_area` VALUES (1744, 164, '南康区');
INSERT INTO `tao_area` VALUES (1745, 164, '其它区');
INSERT INTO `tao_area` VALUES (1746, 165, '吉州区');
INSERT INTO `tao_area` VALUES (1747, 165, '青原区');
INSERT INTO `tao_area` VALUES (1748, 165, '吉安县');
INSERT INTO `tao_area` VALUES (1749, 165, '吉水县');
INSERT INTO `tao_area` VALUES (1750, 165, '峡江县');
INSERT INTO `tao_area` VALUES (1751, 165, '新干县');
INSERT INTO `tao_area` VALUES (1752, 165, '永丰县');
INSERT INTO `tao_area` VALUES (1753, 165, '泰和县');
INSERT INTO `tao_area` VALUES (1754, 165, '遂川县');
INSERT INTO `tao_area` VALUES (1755, 165, '万安县');
INSERT INTO `tao_area` VALUES (1756, 165, '安福县');
INSERT INTO `tao_area` VALUES (1757, 165, '永新县');
INSERT INTO `tao_area` VALUES (1758, 165, '井冈山市');
INSERT INTO `tao_area` VALUES (1759, 165, '其它区');
INSERT INTO `tao_area` VALUES (1760, 166, '袁州区');
INSERT INTO `tao_area` VALUES (1761, 166, '奉新县');
INSERT INTO `tao_area` VALUES (1762, 166, '万载县');
INSERT INTO `tao_area` VALUES (1763, 166, '上高县');
INSERT INTO `tao_area` VALUES (1764, 166, '宜丰县');
INSERT INTO `tao_area` VALUES (1765, 166, '靖安县');
INSERT INTO `tao_area` VALUES (1766, 166, '铜鼓县');
INSERT INTO `tao_area` VALUES (1767, 166, '丰城市');
INSERT INTO `tao_area` VALUES (1768, 166, '樟树市');
INSERT INTO `tao_area` VALUES (1769, 166, '高安市');
INSERT INTO `tao_area` VALUES (1770, 166, '其它区');
INSERT INTO `tao_area` VALUES (1771, 167, '临川区');
INSERT INTO `tao_area` VALUES (1772, 167, '南城县');
INSERT INTO `tao_area` VALUES (1773, 167, '黎川县');
INSERT INTO `tao_area` VALUES (1774, 167, '南丰县');
INSERT INTO `tao_area` VALUES (1775, 167, '崇仁县');
INSERT INTO `tao_area` VALUES (1776, 167, '乐安县');
INSERT INTO `tao_area` VALUES (1777, 167, '宜黄县');
INSERT INTO `tao_area` VALUES (1778, 167, '金溪县');
INSERT INTO `tao_area` VALUES (1779, 167, '资溪县');
INSERT INTO `tao_area` VALUES (1780, 167, '东乡县');
INSERT INTO `tao_area` VALUES (1781, 167, '广昌县');
INSERT INTO `tao_area` VALUES (1782, 167, '其它区');
INSERT INTO `tao_area` VALUES (1783, 168, '信州区');
INSERT INTO `tao_area` VALUES (1784, 168, '上饶县');
INSERT INTO `tao_area` VALUES (1785, 168, '广丰区');
INSERT INTO `tao_area` VALUES (1786, 168, '玉山县');
INSERT INTO `tao_area` VALUES (1787, 168, '铅山县');
INSERT INTO `tao_area` VALUES (1788, 168, '横峰县');
INSERT INTO `tao_area` VALUES (1789, 168, '弋阳县');
INSERT INTO `tao_area` VALUES (1790, 168, '余干县');
INSERT INTO `tao_area` VALUES (1791, 168, '鄱阳县');
INSERT INTO `tao_area` VALUES (1792, 168, '万年县');
INSERT INTO `tao_area` VALUES (1793, 168, '婺源县');
INSERT INTO `tao_area` VALUES (1794, 168, '德兴市');
INSERT INTO `tao_area` VALUES (1795, 168, '其它区');
INSERT INTO `tao_area` VALUES (1796, 169, '历下区');
INSERT INTO `tao_area` VALUES (1797, 169, '市中区');
INSERT INTO `tao_area` VALUES (1798, 169, '槐荫区');
INSERT INTO `tao_area` VALUES (1799, 169, '天桥区');
INSERT INTO `tao_area` VALUES (1800, 169, '历城区');
INSERT INTO `tao_area` VALUES (1801, 169, '长清区');
INSERT INTO `tao_area` VALUES (1802, 169, '平阴县');
INSERT INTO `tao_area` VALUES (1803, 169, '济阳县');
INSERT INTO `tao_area` VALUES (1804, 169, '商河县');
INSERT INTO `tao_area` VALUES (1805, 169, '章丘市');
INSERT INTO `tao_area` VALUES (1806, 169, '其它区');
INSERT INTO `tao_area` VALUES (1807, 170, '市南区');
INSERT INTO `tao_area` VALUES (1808, 170, '市北区');
INSERT INTO `tao_area` VALUES (1809, 170, '四方区');
INSERT INTO `tao_area` VALUES (1810, 170, '黄岛区');
INSERT INTO `tao_area` VALUES (1811, 170, '崂山区');
INSERT INTO `tao_area` VALUES (1812, 170, '李沧区');
INSERT INTO `tao_area` VALUES (1813, 170, '城阳区');
INSERT INTO `tao_area` VALUES (1814, 170, '开发区');
INSERT INTO `tao_area` VALUES (1815, 170, '胶州市');
INSERT INTO `tao_area` VALUES (1816, 170, '即墨市');
INSERT INTO `tao_area` VALUES (1817, 170, '平度市');
INSERT INTO `tao_area` VALUES (1818, 170, '胶南市');
INSERT INTO `tao_area` VALUES (1819, 170, '莱西市');
INSERT INTO `tao_area` VALUES (1820, 170, '其它区');
INSERT INTO `tao_area` VALUES (1821, 171, '淄川区');
INSERT INTO `tao_area` VALUES (1822, 171, '张店区');
INSERT INTO `tao_area` VALUES (1823, 171, '博山区');
INSERT INTO `tao_area` VALUES (1824, 171, '临淄区');
INSERT INTO `tao_area` VALUES (1825, 171, '周村区');
INSERT INTO `tao_area` VALUES (1826, 171, '桓台县');
INSERT INTO `tao_area` VALUES (1827, 171, '高青县');
INSERT INTO `tao_area` VALUES (1828, 171, '沂源县');
INSERT INTO `tao_area` VALUES (1829, 171, '其它区');
INSERT INTO `tao_area` VALUES (1830, 172, '市中区');
INSERT INTO `tao_area` VALUES (1831, 172, '薛城区');
INSERT INTO `tao_area` VALUES (1832, 172, '峄城区');
INSERT INTO `tao_area` VALUES (1833, 172, '台儿庄区');
INSERT INTO `tao_area` VALUES (1834, 172, '山亭区');
INSERT INTO `tao_area` VALUES (1835, 172, '滕州市');
INSERT INTO `tao_area` VALUES (1836, 172, '其它区');
INSERT INTO `tao_area` VALUES (1837, 173, '东营区');
INSERT INTO `tao_area` VALUES (1838, 173, '河口区');
INSERT INTO `tao_area` VALUES (1839, 173, '垦利县');
INSERT INTO `tao_area` VALUES (1840, 173, '利津县');
INSERT INTO `tao_area` VALUES (1841, 173, '广饶县');
INSERT INTO `tao_area` VALUES (1842, 173, '西城区');
INSERT INTO `tao_area` VALUES (1843, 173, '东城区');
INSERT INTO `tao_area` VALUES (1844, 173, '其它区');
INSERT INTO `tao_area` VALUES (1845, 174, '芝罘区');
INSERT INTO `tao_area` VALUES (1846, 174, '福山区');
INSERT INTO `tao_area` VALUES (1847, 174, '牟平区');
INSERT INTO `tao_area` VALUES (1848, 174, '莱山区');
INSERT INTO `tao_area` VALUES (1849, 174, '长岛县');
INSERT INTO `tao_area` VALUES (1850, 174, '龙口市');
INSERT INTO `tao_area` VALUES (1851, 174, '莱阳市');
INSERT INTO `tao_area` VALUES (1852, 174, '莱州市');
INSERT INTO `tao_area` VALUES (1853, 174, '蓬莱市');
INSERT INTO `tao_area` VALUES (1854, 174, '招远市');
INSERT INTO `tao_area` VALUES (1855, 174, '栖霞市');
INSERT INTO `tao_area` VALUES (1856, 174, '海阳市');
INSERT INTO `tao_area` VALUES (1857, 174, '其它区');
INSERT INTO `tao_area` VALUES (1858, 175, '潍城区');
INSERT INTO `tao_area` VALUES (1859, 175, '寒亭区');
INSERT INTO `tao_area` VALUES (1860, 175, '坊子区');
INSERT INTO `tao_area` VALUES (1861, 175, '奎文区');
INSERT INTO `tao_area` VALUES (1862, 175, '临朐县');
INSERT INTO `tao_area` VALUES (1863, 175, '昌乐县');
INSERT INTO `tao_area` VALUES (1864, 175, '开发区');
INSERT INTO `tao_area` VALUES (1865, 175, '青州市');
INSERT INTO `tao_area` VALUES (1866, 175, '诸城市');
INSERT INTO `tao_area` VALUES (1867, 175, '寿光市');
INSERT INTO `tao_area` VALUES (1868, 175, '安丘市');
INSERT INTO `tao_area` VALUES (1869, 175, '高密市');
INSERT INTO `tao_area` VALUES (1870, 175, '昌邑市');
INSERT INTO `tao_area` VALUES (1871, 175, '其它区');
INSERT INTO `tao_area` VALUES (1872, 176, '市中区');
INSERT INTO `tao_area` VALUES (1873, 176, '任城区');
INSERT INTO `tao_area` VALUES (1874, 176, '微山县');
INSERT INTO `tao_area` VALUES (1875, 176, '鱼台县');
INSERT INTO `tao_area` VALUES (1876, 176, '金乡县');
INSERT INTO `tao_area` VALUES (1877, 176, '嘉祥县');
INSERT INTO `tao_area` VALUES (1878, 176, '汶上县');
INSERT INTO `tao_area` VALUES (1879, 176, '泗水县');
INSERT INTO `tao_area` VALUES (1880, 176, '梁山县');
INSERT INTO `tao_area` VALUES (1881, 176, '曲阜市');
INSERT INTO `tao_area` VALUES (1882, 176, '兖州区');
INSERT INTO `tao_area` VALUES (1883, 176, '邹城市');
INSERT INTO `tao_area` VALUES (1884, 176, '其它区');
INSERT INTO `tao_area` VALUES (1885, 177, '泰山区');
INSERT INTO `tao_area` VALUES (1886, 177, '岱岳区');
INSERT INTO `tao_area` VALUES (1887, 177, '宁阳县');
INSERT INTO `tao_area` VALUES (1888, 177, '东平县');
INSERT INTO `tao_area` VALUES (1889, 177, '新泰市');
INSERT INTO `tao_area` VALUES (1890, 177, '肥城市');
INSERT INTO `tao_area` VALUES (1891, 177, '其它区');
INSERT INTO `tao_area` VALUES (1892, 178, '环翠区');
INSERT INTO `tao_area` VALUES (1893, 178, '文登区');
INSERT INTO `tao_area` VALUES (1894, 178, '荣成市');
INSERT INTO `tao_area` VALUES (1895, 178, '乳山市');
INSERT INTO `tao_area` VALUES (1896, 178, '其它区');
INSERT INTO `tao_area` VALUES (1897, 179, '东港区');
INSERT INTO `tao_area` VALUES (1898, 179, '岚山区');
INSERT INTO `tao_area` VALUES (1899, 179, '五莲县');
INSERT INTO `tao_area` VALUES (1900, 179, '莒县');
INSERT INTO `tao_area` VALUES (1901, 179, '其它区');
INSERT INTO `tao_area` VALUES (1902, 180, '莱城区');
INSERT INTO `tao_area` VALUES (1903, 180, '钢城区');
INSERT INTO `tao_area` VALUES (1904, 180, '其它区');
INSERT INTO `tao_area` VALUES (1905, 181, '兰山区');
INSERT INTO `tao_area` VALUES (1906, 181, '罗庄区');
INSERT INTO `tao_area` VALUES (1907, 181, '河东区');
INSERT INTO `tao_area` VALUES (1908, 181, '沂南县');
INSERT INTO `tao_area` VALUES (1909, 181, '郯城县');
INSERT INTO `tao_area` VALUES (1910, 181, '沂水县');
INSERT INTO `tao_area` VALUES (1911, 181, '兰陵县');
INSERT INTO `tao_area` VALUES (1912, 181, '费县');
INSERT INTO `tao_area` VALUES (1913, 181, '平邑县');
INSERT INTO `tao_area` VALUES (1914, 181, '莒南县');
INSERT INTO `tao_area` VALUES (1915, 181, '蒙阴县');
INSERT INTO `tao_area` VALUES (1916, 181, '临沭县');
INSERT INTO `tao_area` VALUES (1917, 181, '其它区');
INSERT INTO `tao_area` VALUES (1918, 182, '德城区');
INSERT INTO `tao_area` VALUES (1919, 182, '陵城区');
INSERT INTO `tao_area` VALUES (1920, 182, '宁津县');
INSERT INTO `tao_area` VALUES (1921, 182, '庆云县');
INSERT INTO `tao_area` VALUES (1922, 182, '临邑县');
INSERT INTO `tao_area` VALUES (1923, 182, '齐河县');
INSERT INTO `tao_area` VALUES (1924, 182, '平原县');
INSERT INTO `tao_area` VALUES (1925, 182, '夏津县');
INSERT INTO `tao_area` VALUES (1926, 182, '武城县');
INSERT INTO `tao_area` VALUES (1927, 182, '开发区');
INSERT INTO `tao_area` VALUES (1928, 182, '乐陵市');
INSERT INTO `tao_area` VALUES (1929, 182, '禹城市');
INSERT INTO `tao_area` VALUES (1930, 182, '其它区');
INSERT INTO `tao_area` VALUES (1931, 183, '东昌府区');
INSERT INTO `tao_area` VALUES (1932, 183, '阳谷县');
INSERT INTO `tao_area` VALUES (1933, 183, '莘县');
INSERT INTO `tao_area` VALUES (1934, 183, '茌平县');
INSERT INTO `tao_area` VALUES (1935, 183, '东阿县');
INSERT INTO `tao_area` VALUES (1936, 183, '冠县');
INSERT INTO `tao_area` VALUES (1937, 183, '高唐县');
INSERT INTO `tao_area` VALUES (1938, 183, '临清市');
INSERT INTO `tao_area` VALUES (1939, 183, '其它区');
INSERT INTO `tao_area` VALUES (1940, 184, '滨城区');
INSERT INTO `tao_area` VALUES (1941, 184, '惠民县');
INSERT INTO `tao_area` VALUES (1942, 184, '阳信县');
INSERT INTO `tao_area` VALUES (1943, 184, '无棣县');
INSERT INTO `tao_area` VALUES (1944, 184, '沾化区');
INSERT INTO `tao_area` VALUES (1945, 184, '博兴县');
INSERT INTO `tao_area` VALUES (1946, 184, '邹平县');
INSERT INTO `tao_area` VALUES (1947, 184, '其它区');
INSERT INTO `tao_area` VALUES (1948, 185, '牡丹区');
INSERT INTO `tao_area` VALUES (1949, 185, '曹县');
INSERT INTO `tao_area` VALUES (1950, 185, '单县');
INSERT INTO `tao_area` VALUES (1951, 185, '成武县');
INSERT INTO `tao_area` VALUES (1952, 185, '巨野县');
INSERT INTO `tao_area` VALUES (1953, 185, '郓城县');
INSERT INTO `tao_area` VALUES (1954, 185, '鄄城县');
INSERT INTO `tao_area` VALUES (1955, 185, '定陶县');
INSERT INTO `tao_area` VALUES (1956, 185, '东明县');
INSERT INTO `tao_area` VALUES (1957, 185, '其它区');
INSERT INTO `tao_area` VALUES (1958, 186, '中原区');
INSERT INTO `tao_area` VALUES (1959, 186, '二七区');
INSERT INTO `tao_area` VALUES (1960, 186, '管城回族区');
INSERT INTO `tao_area` VALUES (1961, 186, '金水区');
INSERT INTO `tao_area` VALUES (1962, 186, '上街区');
INSERT INTO `tao_area` VALUES (1963, 186, '惠济区');
INSERT INTO `tao_area` VALUES (1964, 186, '中牟县');
INSERT INTO `tao_area` VALUES (1965, 186, '巩义市');
INSERT INTO `tao_area` VALUES (1966, 186, '荥阳市');
INSERT INTO `tao_area` VALUES (1967, 186, '新密市');
INSERT INTO `tao_area` VALUES (1968, 186, '新郑市');
INSERT INTO `tao_area` VALUES (1969, 186, '登封市');
INSERT INTO `tao_area` VALUES (1970, 186, '郑东新区');
INSERT INTO `tao_area` VALUES (1971, 186, '高新区');
INSERT INTO `tao_area` VALUES (1972, 186, '其它区');
INSERT INTO `tao_area` VALUES (1973, 187, '龙亭区');
INSERT INTO `tao_area` VALUES (1974, 187, '顺河回族区');
INSERT INTO `tao_area` VALUES (1975, 187, '鼓楼区');
INSERT INTO `tao_area` VALUES (1976, 187, '禹王台区');
INSERT INTO `tao_area` VALUES (1977, 187, '金明区');
INSERT INTO `tao_area` VALUES (1978, 187, '杞县');
INSERT INTO `tao_area` VALUES (1979, 187, '通许县');
INSERT INTO `tao_area` VALUES (1980, 187, '尉氏县');
INSERT INTO `tao_area` VALUES (1981, 187, '祥符区');
INSERT INTO `tao_area` VALUES (1982, 187, '兰考县');
INSERT INTO `tao_area` VALUES (1983, 187, '其它区');
INSERT INTO `tao_area` VALUES (1984, 188, '老城区');
INSERT INTO `tao_area` VALUES (1985, 188, '西工区');
INSERT INTO `tao_area` VALUES (1986, 188, '瀍河回族区');
INSERT INTO `tao_area` VALUES (1987, 188, '涧西区');
INSERT INTO `tao_area` VALUES (1988, 188, '吉利区');
INSERT INTO `tao_area` VALUES (1989, 188, '洛龙区');
INSERT INTO `tao_area` VALUES (1990, 188, '孟津县');
INSERT INTO `tao_area` VALUES (1991, 188, '新安县');
INSERT INTO `tao_area` VALUES (1992, 188, '栾川县');
INSERT INTO `tao_area` VALUES (1993, 188, '嵩县');
INSERT INTO `tao_area` VALUES (1994, 188, '汝阳县');
INSERT INTO `tao_area` VALUES (1995, 188, '宜阳县');
INSERT INTO `tao_area` VALUES (1996, 188, '洛宁县');
INSERT INTO `tao_area` VALUES (1997, 188, '伊川县');
INSERT INTO `tao_area` VALUES (1998, 188, '偃师市');
INSERT INTO `tao_area` VALUES (1999, 189, '新华区');
INSERT INTO `tao_area` VALUES (2000, 189, '卫东区');
INSERT INTO `tao_area` VALUES (2001, 189, '石龙区');
INSERT INTO `tao_area` VALUES (2002, 189, '湛河区');
INSERT INTO `tao_area` VALUES (2003, 189, '宝丰县');
INSERT INTO `tao_area` VALUES (2004, 189, '叶县');
INSERT INTO `tao_area` VALUES (2005, 189, '鲁山县');
INSERT INTO `tao_area` VALUES (2006, 189, '郏县');
INSERT INTO `tao_area` VALUES (2007, 189, '舞钢市');
INSERT INTO `tao_area` VALUES (2008, 189, '汝州市');
INSERT INTO `tao_area` VALUES (2009, 189, '其它区');
INSERT INTO `tao_area` VALUES (2010, 190, '文峰区');
INSERT INTO `tao_area` VALUES (2011, 190, '北关区');
INSERT INTO `tao_area` VALUES (2012, 190, '殷都区');
INSERT INTO `tao_area` VALUES (2013, 190, '龙安区');
INSERT INTO `tao_area` VALUES (2014, 190, '安阳县');
INSERT INTO `tao_area` VALUES (2015, 190, '汤阴县');
INSERT INTO `tao_area` VALUES (2016, 190, '滑县');
INSERT INTO `tao_area` VALUES (2017, 190, '内黄县');
INSERT INTO `tao_area` VALUES (2018, 190, '林州市');
INSERT INTO `tao_area` VALUES (2019, 190, '其它区');
INSERT INTO `tao_area` VALUES (2020, 191, '鹤山区');
INSERT INTO `tao_area` VALUES (2021, 191, '山城区');
INSERT INTO `tao_area` VALUES (2022, 191, '淇滨区');
INSERT INTO `tao_area` VALUES (2023, 191, '浚县');
INSERT INTO `tao_area` VALUES (2024, 191, '淇县');
INSERT INTO `tao_area` VALUES (2025, 191, '其它区');
INSERT INTO `tao_area` VALUES (2026, 192, '红旗区');
INSERT INTO `tao_area` VALUES (2027, 192, '卫滨区');
INSERT INTO `tao_area` VALUES (2028, 192, '凤泉区');
INSERT INTO `tao_area` VALUES (2029, 192, '牧野区');
INSERT INTO `tao_area` VALUES (2030, 192, '新乡县');
INSERT INTO `tao_area` VALUES (2031, 192, '获嘉县');
INSERT INTO `tao_area` VALUES (2032, 192, '原阳县');
INSERT INTO `tao_area` VALUES (2033, 192, '延津县');
INSERT INTO `tao_area` VALUES (2034, 192, '封丘县');
INSERT INTO `tao_area` VALUES (2035, 192, '长垣县');
INSERT INTO `tao_area` VALUES (2036, 192, '卫辉市');
INSERT INTO `tao_area` VALUES (2037, 192, '辉县市');
INSERT INTO `tao_area` VALUES (2038, 192, '其它区');
INSERT INTO `tao_area` VALUES (2039, 193, '解放区');
INSERT INTO `tao_area` VALUES (2040, 193, '中站区');
INSERT INTO `tao_area` VALUES (2041, 193, '马村区');
INSERT INTO `tao_area` VALUES (2042, 193, '山阳区');
INSERT INTO `tao_area` VALUES (2043, 193, '修武县');
INSERT INTO `tao_area` VALUES (2044, 193, '博爱县');
INSERT INTO `tao_area` VALUES (2045, 193, '武陟县');
INSERT INTO `tao_area` VALUES (2046, 193, '温县');
INSERT INTO `tao_area` VALUES (2047, 193, '沁阳市');
INSERT INTO `tao_area` VALUES (2048, 193, '孟州市');
INSERT INTO `tao_area` VALUES (2049, 193, '其它区');
INSERT INTO `tao_area` VALUES (2050, 194, '华龙区');
INSERT INTO `tao_area` VALUES (2051, 194, '清丰县');
INSERT INTO `tao_area` VALUES (2052, 194, '南乐县');
INSERT INTO `tao_area` VALUES (2053, 194, '范县');
INSERT INTO `tao_area` VALUES (2054, 194, '台前县');
INSERT INTO `tao_area` VALUES (2055, 194, '濮阳县');
INSERT INTO `tao_area` VALUES (2056, 194, '其它区');
INSERT INTO `tao_area` VALUES (2057, 195, '魏都区');
INSERT INTO `tao_area` VALUES (2058, 195, '许昌县');
INSERT INTO `tao_area` VALUES (2059, 195, '鄢陵县');
INSERT INTO `tao_area` VALUES (2060, 195, '襄城县');
INSERT INTO `tao_area` VALUES (2061, 195, '禹州市');
INSERT INTO `tao_area` VALUES (2062, 195, '长葛市');
INSERT INTO `tao_area` VALUES (2063, 195, '其它区');
INSERT INTO `tao_area` VALUES (2064, 196, '源汇区');
INSERT INTO `tao_area` VALUES (2065, 196, '郾城区');
INSERT INTO `tao_area` VALUES (2066, 196, '召陵区');
INSERT INTO `tao_area` VALUES (2067, 196, '舞阳县');
INSERT INTO `tao_area` VALUES (2068, 196, '临颍县');
INSERT INTO `tao_area` VALUES (2069, 196, '其它区');
INSERT INTO `tao_area` VALUES (2070, 197, '湖滨区');
INSERT INTO `tao_area` VALUES (2071, 197, '渑池县');
INSERT INTO `tao_area` VALUES (2072, 197, '陕州区');
INSERT INTO `tao_area` VALUES (2073, 197, '卢氏县');
INSERT INTO `tao_area` VALUES (2074, 197, '义马市');
INSERT INTO `tao_area` VALUES (2075, 197, '灵宝市');
INSERT INTO `tao_area` VALUES (2076, 197, '其它区');
INSERT INTO `tao_area` VALUES (2077, 198, '宛城区');
INSERT INTO `tao_area` VALUES (2078, 198, '卧龙区');
INSERT INTO `tao_area` VALUES (2079, 198, '南召县');
INSERT INTO `tao_area` VALUES (2080, 198, '方城县');
INSERT INTO `tao_area` VALUES (2081, 198, '西峡县');
INSERT INTO `tao_area` VALUES (2082, 198, '镇平县');
INSERT INTO `tao_area` VALUES (2083, 198, '内乡县');
INSERT INTO `tao_area` VALUES (2084, 198, '淅川县');
INSERT INTO `tao_area` VALUES (2085, 198, '社旗县');
INSERT INTO `tao_area` VALUES (2086, 198, '唐河县');
INSERT INTO `tao_area` VALUES (2087, 198, '新野县');
INSERT INTO `tao_area` VALUES (2088, 198, '桐柏县');
INSERT INTO `tao_area` VALUES (2089, 198, '邓州市');
INSERT INTO `tao_area` VALUES (2090, 198, '其它区');
INSERT INTO `tao_area` VALUES (2091, 199, '梁园区');
INSERT INTO `tao_area` VALUES (2092, 199, '睢阳区');
INSERT INTO `tao_area` VALUES (2093, 199, '民权县');
INSERT INTO `tao_area` VALUES (2094, 199, '睢县');
INSERT INTO `tao_area` VALUES (2095, 199, '宁陵县');
INSERT INTO `tao_area` VALUES (2096, 199, '柘城县');
INSERT INTO `tao_area` VALUES (2097, 199, '虞城县');
INSERT INTO `tao_area` VALUES (2098, 199, '夏邑县');
INSERT INTO `tao_area` VALUES (2099, 199, '永城市');
INSERT INTO `tao_area` VALUES (2100, 199, '其它区');
INSERT INTO `tao_area` VALUES (2101, 200, '浉河区');
INSERT INTO `tao_area` VALUES (2102, 200, '平桥区');
INSERT INTO `tao_area` VALUES (2103, 200, '罗山县');
INSERT INTO `tao_area` VALUES (2104, 200, '光山县');
INSERT INTO `tao_area` VALUES (2105, 200, '新县');
INSERT INTO `tao_area` VALUES (2106, 200, '商城县');
INSERT INTO `tao_area` VALUES (2107, 200, '固始县');
INSERT INTO `tao_area` VALUES (2108, 200, '潢川县');
INSERT INTO `tao_area` VALUES (2109, 200, '淮滨县');
INSERT INTO `tao_area` VALUES (2110, 200, '息县');
INSERT INTO `tao_area` VALUES (2111, 200, '其它区');
INSERT INTO `tao_area` VALUES (2112, 201, '川汇区');
INSERT INTO `tao_area` VALUES (2113, 201, '扶沟县');
INSERT INTO `tao_area` VALUES (2114, 201, '西华县');

-- ----------------------------
-- Table structure for tao_article
-- ----------------------------
DROP TABLE IF EXISTS `tao_article`;
CREATE TABLE `tao_article`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `cate_id` int UNSIGNED NOT NULL COMMENT '分类id',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户id',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `thum_img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '缩略图',
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '关键词',
  `description` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'seo描述',
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT 'ip地址',
  `type` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型1文章2视频3音频',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态1显示0待审-1禁止',
  `has_image` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '图片张数',
  `has_video` tinyint NOT NULL DEFAULT 0 COMMENT '1有视频0无',
  `has_audio` tinyint NOT NULL DEFAULT 0 COMMENT '1有音频0无',
  `is_comment` tinyint UNSIGNED NOT NULL COMMENT '可评论1是0否',
  `pv` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览量',
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
  `comments_num` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论数',
  `media` json NOT NULL COMMENT '媒体image,video,audio',
  `flags` json NOT NULL COMMENT '标记is_top置顶is_good推荐is_wait完结',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE COMMENT '文章的用户索引',
  INDEX `cate_id`(`cate_id` ASC) USING BTREE COMMENT '文章分类索引',
  INDEX `idx_article_create_time`(`create_time` DESC) USING BTREE COMMENT '创建时间',
  INDEX `idx_article_cid_status_dtime`(`cate_id` ASC, `status` ASC, `delete_time` ASC) USING BTREE COMMENT '分类状态时间索引'
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文章主表' ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `tao_article_flag`;
CREATE TABLE `tao_article_flag`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '属性id',
  `type` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '1置顶is_top,2精华is_good,3待解is_wait,4热评hot_comment,5hot_pv阅读排行',
  `article_id` int UNSIGNED NOT NULL COMMENT '文章id',
  `create_time` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '时间',
  PRIMARY KEY (`id` DESC) USING BTREE,
  INDEX `idx_type_article_id`(`type` ASC, `article_id` ASC) USING BTREE COMMENT '类型文章id'
) ENGINE = InnoDB AUTO_INCREMENT = 50 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
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
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户组id',
  `uid` int UNSIGNED NOT NULL,
  `group_id` int UNSIGNED NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '用户权限组状态0禁止1正常',
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
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
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE,
  INDEX `pid`(`pid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 200 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '权限表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_auth_rule
-- ----------------------------
INSERT INTO `tao_auth_rule` VALUES (1, 'System', 'System', 1, 1, 0, 0, 'layui-icon-template', 0, 1, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (2, 'Account', 'Account', 1, 1, 0, 0, 'layui-icon-user', 0, 1, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (3, 'Addon', 'Addon', 1, 1, 0, 0, 'layui-icon-component', 0, 3, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (4, 'Content', 'Content', 1, 1, 0, 0, 'layui-icon-template-1', 0, 4, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (5, 'Set', 'Set', 1, -1, 0, 0, 'layui-icon-set', 0, 5, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (6, 'Server', 'Server', 1, -1, 0, 0, 'layui-icon-senior', 0, 6, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (7, 'Apps', 'Apps', 1, -1, 0, 0, 'layui-icon-app', 0, 7, '', 0, 0, 0);
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
INSERT INTO `tao_auth_rule` VALUES (68, 'user.vip/index', '会员等级', 1, 1, 2, 1, '', 1, 2, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (69, 'user.vip/list', 'vip列表', 1, 1, 68, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (70, 'user.vip/add', '添加vip', 1, 1, 68, 2, '', 2, 51, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (71, 'user.vip/edit', '编辑vip', 1, 1, 68, 2, '', 2, 52, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (72, 'user.vip/delete', '删除vip', 1, 1, 68, 2, '', 2, 53, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (73, 'addon.addons/index', '插件市场', 1, 1, 3, 1, '', 1, 1, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (74, 'addon.addons/list', '插件列表', 1, 1, 73, 2, '', 2, 50, '', 0, 0, 0);
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
INSERT INTO `tao_auth_rule` VALUES (124, 'content.tag/index', '标签管理', 1, 1, 4, 1, '', 1, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (125, 'content.tag/list', '标签列表', 1, 1, 124, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (126, 'content.tag/add', '添加标签', 1, 1, 124, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (127, 'content.tag/edit', '编辑标签', 1, 1, 124, 2, '', 2, 50, '', 0, 0, 0);
INSERT INTO `tao_auth_rule` VALUES (128, 'content.tag/delete', '删除标签', 1, 1, 124, 2, '', 2, 50, '', 0, 0, 0);

-- ----------------------------
-- Table structure for tao_cate
-- ----------------------------
DROP TABLE IF EXISTS `tao_cate`;
CREATE TABLE `tao_cate`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
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
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updata_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
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
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `article_id` int NOT NULL COMMENT '文章id',
  `user_id` int NOT NULL COMMENT '用户id',
  `auther` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '作者',
  `collect_title` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '收藏帖子标题',
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章收藏表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tao_comment
-- ----------------------------
DROP TABLE IF EXISTS `tao_comment`;

CREATE TABLE `tao_comment`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '评论id',
    `pid` int NOT NULL DEFAULT 0 COMMENT '父id',
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '评论',
    `article_id` int NOT NULL COMMENT '文章id',
    `user_id` int NOT NULL COMMENT '评论用户',
    `to_user_id` int NULL DEFAULT NULL COMMENT '给用户留言',
    `zan` tinyint NOT NULL DEFAULT 0 COMMENT '赞',
    `cai` enum('1','0') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '0求解1采纳',
    `status` enum('0','-1','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '1通过0待审-1禁止',
    `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '评论类型1帖子2其它',
    `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
    `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
    `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aiticle_id`(`article_id` ASC) USING BTREE COMMENT '文章评论索引',
  INDEX `user_id`(`user_id` ASC) USING BTREE COMMENT '评论用户索引'
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '评论表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tao_cunsult
-- ----------------------------
DROP TABLE IF EXISTS `tao_cunsult`;
CREATE TABLE `tao_cunsult`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` tinyint NOT NULL COMMENT '问题类型1问题资讯2提交BUG',
  `title` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '问题',
  `content` tinytext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
  `poster` tinyint NOT NULL COMMENT '发送人ID',
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '反馈表' ROW_FORMAT = Dynamic;

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
  `update_time` int UNSIGNED NOT NULL COMMENT '更新时间',
  `delete_time` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '友情链接' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_friend_link
-- ----------------------------
INSERT INTO `tao_friend_link` VALUES (1, 'taoler', 'https://www.aieok.com', '', 0, 0, 0);

-- ----------------------------
-- Table structure for tao_mail_server
-- ----------------------------
DROP TABLE IF EXISTS `tao_mail_server`;
CREATE TABLE `tao_mail_server`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `mail` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '邮箱设置',
  `host` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '邮箱服务地址',
  `port` smallint NOT NULL COMMENT '邮箱端口',
  `nickname` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '邮箱密码',
  `active` tinyint(1) NOT NULL DEFAULT 0 COMMENT '邮箱服务1激活0未激活',
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
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
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '消息表' ROW_FORMAT = Dynamic;

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
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_mesto_receveid`(`receve_id`) USING BTREE COMMENT '收件人ID'
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '消息详细表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tao_slider
-- ----------------------------
DROP TABLE IF EXISTS `tao_slider`;
CREATE TABLE `tao_slider`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `slid_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '幻灯名',
  `slid_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '类型',
  `slid_img` varchar(70) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '幻灯图片地址',
  `slid_href` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '链接',
  `slid_color` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '广告块颜色',
  `slid_start` int NOT NULL DEFAULT 0 COMMENT '开始时间',
  `slid_over` int NOT NULL DEFAULT 0 COMMENT '结束时间',
  `slid_status` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '1投放0仓库',
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

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
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '系统配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_system
-- ----------------------------
INSERT INTO `tao_system` VALUES (1, 'TaoLer', 'TaoLer社区-www.aieok.com', '', 'taoler', '/storage/logo/logo.png', '/storage/logo/logo-m.png', 10, 2048, 'image:png|gif|jpg|jpeg,application:zip|rar|docx,video:mp4,audio:mp3|m4a|mp4|mpeg,zip:zip,application/msword:docx', '<a href=\"https://www.aieok.com\" target=\"_blank\">TaoLer</a>', 'TaoLer,轻社区系统,bbs,论坛,Thinkphp6,layui,fly模板,', '这是一个Taoler轻社区论坛系统.', '网站声明：<br>\n1.本站使用TaoLerCMS驱动，简单好用，深度SEO。<br>\n2.内容为用户提供如有侵权，请联系本站管理员删除。<br>\n3.原创内容转载请注明出处，否则一切后果自行承担。<br>', '1', '1', '1', '0.0.0.0-1', '', '管理员|admin|审核员|超级|垃圾', '1.6.3', '', 0, 'https://www.aieok.com/api', 'https://www.aieok.com/api/v1/cy', 'https://www.aieok.com/api/v1/upload/check', 'https://www.aieok.com/api/v1/upload/api', 1641004619, 1678515271);

-- ----------------------------
-- Table structure for tao_tag
-- ----------------------------
DROP TABLE IF EXISTS `tao_tag`;
CREATE TABLE `tao_tag`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'tag自增id',
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称',
  `ename` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '英文名',
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '关键词',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '摘要',
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标签',
  `create_time` int UNSIGNED NOT NULL COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `ename`(`ename` ASC) USING BTREE COMMENT 'ename查询tag索引'
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章tag表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tao_taglist
-- ----------------------------
DROP TABLE IF EXISTS `tao_taglist`;
CREATE TABLE `tao_taglist`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标签列表id',
  `tag_id` int NOT NULL COMMENT '标签id',
  `article_id` int NOT NULL COMMENT '文章id',
  `create_time` int UNSIGNED NOT NULL COMMENT '创建时间',
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
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
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
  `note` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `last_login_ip` varchar(70) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '最后登陆ip',
  `last_login_time` int NOT NULL DEFAULT 0 COMMENT '最后登陆时间',
  `login_error_num` tinyint(1) NOT NULL DEFAULT 0 COMMENT '登陆错误次数',
  `login_error_time` int NOT NULL DEFAULT 0 COMMENT '登陆错误时间',
  `login_error_lock` tinyint(1) NOT NULL DEFAULT 0 COMMENT '登陆锁定0正常1锁定',
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '修改时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE COMMENT '用户名查询用户索引',
  INDEX `email`(`email`) USING BTREE COMMENT 'email查询用户索引'
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_user
-- ----------------------------
INSERT INTO `tao_user` VALUES (1, 'admin', '95d6f8d0d0c3b45e5dbe4057da1b149e', '2147483647', 'admin@qq.com', 0, '管理员', 'earth', '1', '这是一个社区系统', '/static/res/images/avatar/00.jpg', '1', 0, 1, '1', 0, '备注信息', '127.0.0.1', 1677900186, 0, 0, 0, 1579053025, 1677900186, 0);

-- ----------------------------
-- Table structure for tao_user_area
-- ----------------------------
DROP TABLE IF EXISTS `tao_user_area`;
CREATE TABLE `tao_user_area`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `area` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '所属区域',
  `asing` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '区域简称',
  `create_time` int UNSIGNED NOT NULL DEFAULT 0,
  `update_time` int UNSIGNED NOT NULL DEFAULT 0,
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0,
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
-- Table structure for tao_user_article_log
-- ----------------------------
DROP TABLE IF EXISTS `tao_user_article_log`;
CREATE TABLE `tao_user_article_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int NOT NULL COMMENT '用户ID',
  `user_postnum` int NOT NULL DEFAULT 0 COMMENT '用户发帖数量',
  `user_refreshnum` int NOT NULL DEFAULT 0 COMMENT '用户刷新数量',
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '记录时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`user_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户发文刷新日志记录' ROW_FORMAT = Dynamic;

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
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户签到表' ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for tao_user_signrule
-- ----------------------------
DROP TABLE IF EXISTS `tao_user_signrule`;
CREATE TABLE `tao_user_signrule`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `days` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '连续天数',
  `score` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分',
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '升级时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
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
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户等级ID',
  `vip` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'vip等级',
  `score` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '积分区间',
  `nick` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '认证昵称',
  `rules` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '权限',
  `postnum` int NOT NULL DEFAULT 10 COMMENT '日发帖数量',
  `postpoint` int NOT NULL DEFAULT 0 COMMENT '发文扣积分',
  `refreshnum` int NOT NULL DEFAULT 10 COMMENT '日刷贴数量',
  `refreshpoint` int NOT NULL DEFAULT 0 COMMENT '刷帖扣积分',
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '升级时间',
  `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tao_user_viprule
-- ----------------------------
INSERT INTO `tao_user_viprule` VALUES (1, 0, '0-99', '游民', '0', 2, 2, 10, 1, 1585476523, 1698763623, 0);
INSERT INTO `tao_user_viprule` VALUES (2, 1, '100-299', '富农', '1', 50, 0, 10, 0, 1585476551, 1698740135, 0);
INSERT INTO `tao_user_viprule` VALUES (3, 2, '300-500', '地主', '0', 100, 0, 0, 0, 1585545450, 1698733320, 0);
INSERT INTO `tao_user_viprule` VALUES (4, 3, '501-699', '土豪', '0', 10, 0, 100, 0, 1585545542, 1698746583, 0);
INSERT INTO `tao_user_viprule` VALUES (5, 4, '700-899', '霸主', '0', 10, 0, 0, 0, 1677824242, 1677824242, 0);
INSERT INTO `tao_user_viprule` VALUES (6, 5, '900-1000', '王爷', '0', 10, 0, 0, 0, 1677824859, 1677824859, 0);

-- ----------------------------
-- Table structure for tao_user_zan
-- ----------------------------
DROP TABLE IF EXISTS `tao_user_zan`;
CREATE TABLE `tao_user_zan`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '点赞主键id',
  `article_id` int NULL DEFAULT NULL COMMENT '文章id',
  `comment_id` int NULL DEFAULT NULL COMMENT '评论id',
  `user_id` int NOT NULL COMMENT '用户id',
  `type` tinyint NOT NULL DEFAULT 2 COMMENT '1文章点赞2评论点赞',
  `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

SET FOREIGN_KEY_CHECKS = 1;
