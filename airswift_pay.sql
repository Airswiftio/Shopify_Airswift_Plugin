/*
 Navicat Premium Data Transfer

 Source Server         : 【我的】RDS
 Source Server Type    : MySQL
 Source Server Version : 80025
 Source Host           : rm-bp113cg2eqn5qojj2uo.mysql.rds.aliyuncs.com:3306
 Source Schema         : dd_airswift_pay

 Target Server Type    : MySQL
 Target Server Version : 80025
 File Encoding         : 65001

 Date: 13/10/2022 18:14:55
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for asp_appkey
-- ----------------------------
DROP TABLE IF EXISTS `asp_appkey`;
CREATE TABLE `asp_appkey` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0',
  `app_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `app_secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `sign_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `shopify_api_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `shopify_api_secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `shopify_access_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `shopify_domain` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `shopify_shop_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `create_time` int NOT NULL DEFAULT '0',
  `update_time` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_key` (`app_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of asp_appkey
-- ----------------------------
BEGIN;
INSERT INTO `asp_appkey` (`id`, `uid`, `app_key`, `app_secret`, `sign_key`, `shopify_api_key`, `shopify_api_secret`, `shopify_access_token`, `shopify_domain`, `shopify_shop_name`, `create_time`, `update_time`) VALUES (1, 3, '8e6bd0d6-4ada-4b97-8840-183422700f81', '164e6921-c135-4075-b4c4-a238d77fd8d6', 'ae92b98e-e78f-41a2-87e9-cd6a94dd2014', '77109f7008a13b621fa9ad5e684fe9d5', '86ef02ded7ca1712e0e50e4c5274367d', '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', 'lighting-geek.myshopify.com', 'lok.l@lighting-geek.com', 0, 0);
INSERT INTO `asp_appkey` (`id`, `uid`, `app_key`, `app_secret`, `sign_key`, `shopify_api_key`, `shopify_api_secret`, `shopify_access_token`, `shopify_domain`, `shopify_shop_name`, `create_time`, `update_time`) VALUES (5, 3, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 1665579070, 1665579070);
INSERT INTO `asp_appkey` (`id`, `uid`, `app_key`, `app_secret`, `sign_key`, `shopify_api_key`, `shopify_api_secret`, `shopify_access_token`, `shopify_domain`, `shopify_shop_name`, `create_time`, `update_time`) VALUES (6, 3, 'b', 'b', 'b', 'b', 'b', 'b', 'b', 'b', 1665579172, 1665579172);
INSERT INTO `asp_appkey` (`id`, `uid`, `app_key`, `app_secret`, `sign_key`, `shopify_api_key`, `shopify_api_secret`, `shopify_access_token`, `shopify_domain`, `shopify_shop_name`, `create_time`, `update_time`) VALUES (7, 3, 'c', 'b', 'b', 'b', 'b', 'b', 'b', 'b', 1665579562, 1665579562);
INSERT INTO `asp_appkey` (`id`, `uid`, `app_key`, `app_secret`, `sign_key`, `shopify_api_key`, `shopify_api_secret`, `shopify_access_token`, `shopify_domain`, `shopify_shop_name`, `create_time`, `update_time`) VALUES (8, 3, 'd', 'd', 'd', 'd', 'd', 'd', 'd', 'd', 1665579601, 1665579601);
INSERT INTO `asp_appkey` (`id`, `uid`, `app_key`, `app_secret`, `sign_key`, `shopify_api_key`, `shopify_api_secret`, `shopify_access_token`, `shopify_domain`, `shopify_shop_name`, `create_time`, `update_time`) VALUES (9, 3, 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 1665579622, 1665579622);
INSERT INTO `asp_appkey` (`id`, `uid`, `app_key`, `app_secret`, `sign_key`, `shopify_api_key`, `shopify_api_secret`, `shopify_access_token`, `shopify_domain`, `shopify_shop_name`, `create_time`, `update_time`) VALUES (11, 3, 'q', 'q', 'q', 'q', 'q', 'q', 'q', 'q', 1665582787, 1665582787);
INSERT INTO `asp_appkey` (`id`, `uid`, `app_key`, `app_secret`, `sign_key`, `shopify_api_key`, `shopify_api_secret`, `shopify_access_token`, `shopify_domain`, `shopify_shop_name`, `create_time`, `update_time`) VALUES (12, 3, '1', '1', '1', '1', '1', '1', '1', '1', 1665582820, 1665582820);
COMMIT;

-- ----------------------------
-- Table structure for asp_log
-- ----------------------------
DROP TABLE IF EXISTS `asp_log`;
CREATE TABLE `asp_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nr` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '内容',
  `tjsj` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='订单表';

-- ----------------------------
-- Records of asp_log
-- ----------------------------
BEGIN;
INSERT INTO `asp_log` (`id`, `nr`, `tjsj`) VALUES (119, '4680183316629-----cancelled-----Order is cancelled.', '2022-10-08 15:27:23');
INSERT INTO `asp_log` (`id`, `nr`, `tjsj`) VALUES (120, '[]', '2022-10-10 17:21:45');
INSERT INTO `asp_log` (`id`, `nr`, `tjsj`) VALUES (121, '[]', '2022-10-10 17:22:09');
COMMIT;

-- ----------------------------
-- Table structure for asp_user
-- ----------------------------
DROP TABLE IF EXISTS `asp_user`;
CREATE TABLE `asp_user` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `create_time` int NOT NULL DEFAULT '0',
  `update_time` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of asp_user
-- ----------------------------
BEGIN;
INSERT INTO `asp_user` (`id`, `username`, `password`, `create_time`, `update_time`) VALUES (3, 'admin123', '0192023a7bbd73250516f069df18b500', 0, 0);
INSERT INTO `asp_user` (`id`, `username`, `password`, `create_time`, `update_time`) VALUES (4, 'admin1235', '0192023a7bbd73250516f069df18b500', 1665456277, 1665456277);
INSERT INTO `asp_user` (`id`, `username`, `password`, `create_time`, `update_time`) VALUES (5, 'admin1234', '0192023a7bbd73250516f069df18b500', 1665456771, 1665456771);
INSERT INTO `asp_user` (`id`, `username`, `password`, `create_time`, `update_time`) VALUES (6, 'admin1239', '0192023a7bbd73250516f069df18b500', 1665627845, 1665627845);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
