# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.6.36-log)
# Database: yii
# Generation Time: 2017-08-15 09:49:35 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table access_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `access_log`;

CREATE TABLE `access_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `user_ip` char(20) NOT NULL DEFAULT '',
  `access_url` varchar(255) NOT NULL DEFAULT '',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `access_log` WRITE;
/*!40000 ALTER TABLE `access_log` DISABLE KEYS */;

INSERT INTO `access_log` (`id`, `user_id`, `user_ip`, `access_url`, `create_time`)
VALUES
	(1,1,'127.0.0.1','/','2017-07-11 18:02:16'),
	(2,1,'127.0.0.1','/','2017-07-11 18:02:38'),
	(3,1,'127.0.0.1','/','2017-07-11 18:03:20'),
	(4,1,'127.0.0.1','/','2017-07-11 18:03:34'),
	(5,1,'127.0.0.1','/','2017-07-11 18:03:41'),
	(6,1,'127.0.0.1','/','2017-07-11 18:03:43'),
	(7,1,'127.0.0.1','/','2017-07-11 18:03:45'),
	(8,1,'127.0.0.1','/','2017-07-11 18:03:46'),
	(9,1,'127.0.0.1','/','2017-07-11 18:03:48'),
	(10,1,'127.0.0.1','/','2017-07-11 18:03:50'),
	(11,1,'127.0.0.1','/','2017-07-11 18:03:52'),
	(12,1,'127.0.0.1','/','2017-07-11 18:03:54'),
	(13,1,'127.0.0.1','/','2017-07-11 18:03:56'),
	(14,1,'127.0.0.1','/','2017-07-11 18:03:59'),
	(15,1,'127.0.0.1','/user-backend/index','2017-07-11 18:07:01'),
	(16,1,'127.0.0.1','/user-backend/index','2017-07-11 18:07:01'),
	(17,1,'127.0.0.1','/category/index','2017-07-11 18:07:02'),
	(18,1,'127.0.0.1','/user-backend/index','2017-07-11 18:07:04'),
	(19,1,'127.0.0.1','/category/index','2017-07-11 18:07:04'),
	(20,1,'127.0.0.1','/user-backend/index','2017-07-11 18:07:05'),
	(21,1,'127.0.0.1','/category/index','2017-07-11 18:07:06'),
	(22,1,'127.0.0.1','/user-backend/index','2017-07-11 18:07:06'),
	(23,1,'127.0.0.1','/category/index','2017-07-11 18:07:07'),
	(24,1,'127.0.0.1','/user-backend/index','2017-07-11 18:07:08'),
	(25,1,'127.0.0.1','/category/index','2017-07-11 18:07:09'),
	(26,1,'127.0.0.1','/user-backend/index','2017-07-11 18:07:10'),
	(27,1,'127.0.0.1','/category/index','2017-07-11 18:07:11'),
	(28,1,'127.0.0.1','/user-backend/index','2017-07-11 18:07:12'),
	(29,1,'127.0.0.1','/category/index','2017-07-11 18:07:12'),
	(30,1,'127.0.0.1','/user-backend/index','2017-07-11 18:07:13'),
	(31,1,'127.0.0.1','/category/index','2017-07-11 18:07:14'),
	(32,1,'127.0.0.1','/user-backend/index','2017-07-11 18:07:15'),
	(33,1,'127.0.0.1','/category/index','2017-07-11 18:07:15'),
	(34,1,'127.0.0.1','/category','2017-07-11 18:19:12');

/*!40000 ALTER TABLE `access_log` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table auth_assignment
# ------------------------------------------------------------

DROP TABLE IF EXISTS `auth_assignment`;

CREATE TABLE `auth_assignment` (
  `item_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`),
  CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `auth_assignment` WRITE;
/*!40000 ALTER TABLE `auth_assignment` DISABLE KEYS */;

INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`)
VALUES
	('普通后台管理员','2',1496211558),
	('管理员','1',1496200986);

/*!40000 ALTER TABLE `auth_assignment` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table auth_item
# ------------------------------------------------------------

DROP TABLE IF EXISTS `auth_item`;

CREATE TABLE `auth_item` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `type` smallint(6) NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `rule_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` blob,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `rule_name` (`rule_name`),
  KEY `idx-auth_item-type` (`type`),
  CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `auth_item` WRITE;
/*!40000 ALTER TABLE `auth_item` DISABLE KEYS */;

INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`)
VALUES
	('/*',2,NULL,NULL,NULL,1496201328,1496201328),
	('/category/*',2,NULL,NULL,NULL,1497259947,1497259947),
	('/category/create',2,NULL,NULL,NULL,1497259947,1497259947),
	('/category/delete',2,NULL,NULL,NULL,1497259947,1497259947),
	('/category/index',2,NULL,NULL,NULL,1497259947,1497259947),
	('/category/update',2,NULL,NULL,NULL,1497259947,1497259947),
	('/category/view',2,NULL,NULL,NULL,1497259947,1497259947),
	('/user-backend/*',2,NULL,NULL,NULL,1496200721,1496200721),
	('/user-backend/create',2,NULL,NULL,NULL,1496200721,1496200721),
	('/user-backend/delete',2,NULL,NULL,NULL,1496200721,1496200721),
	('/user-backend/index',2,NULL,NULL,NULL,1496200721,1496200721),
	('/user-backend/update',2,NULL,NULL,NULL,1496200721,1496200721),
	('/user-backend/view',2,NULL,NULL,NULL,1496200721,1496200721),
	('后台用户查看权限',2,'后台用户查看权限',NULL,NULL,1496211469,1496211469),
	('后台用户管理',2,'后台用户管理权限',NULL,NULL,1496200815,1496200815),
	('普通后台管理员',1,'只能查看后台用户',NULL,NULL,1496211536,1496211536),
	('管理员',1,'系统管理员，拥有系统一切权限',NULL,NULL,1496200924,1496200924),
	('超级管理员',2,'拥有所有权限',NULL,NULL,1496201351,1496201351);

/*!40000 ALTER TABLE `auth_item` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table auth_item_child
# ------------------------------------------------------------

DROP TABLE IF EXISTS `auth_item_child`;

CREATE TABLE `auth_item_child` (
  `parent` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `child` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`),
  CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `auth_item_child` WRITE;
/*!40000 ALTER TABLE `auth_item_child` DISABLE KEYS */;

INSERT INTO `auth_item_child` (`parent`, `child`)
VALUES
	('超级管理员','/*'),
	('后台用户管理','/user-backend/*'),
	('后台用户管理','/user-backend/create'),
	('后台用户管理','/user-backend/delete'),
	('后台用户查看权限','/user-backend/index'),
	('后台用户管理','/user-backend/index'),
	('后台用户管理','/user-backend/update'),
	('后台用户管理','/user-backend/view'),
	('普通后台管理员','后台用户查看权限'),
	('管理员','超级管理员');

/*!40000 ALTER TABLE `auth_item_child` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table auth_rule
# ------------------------------------------------------------

DROP TABLE IF EXISTS `auth_rule`;

CREATE TABLE `auth_rule` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `data` blob,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `category`;

CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `pid` int(11) NOT NULL DEFAULT '0',
  `path` varchar(100) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;

INSERT INTO `category` (`id`, `name`, `pid`, `path`, `status`)
VALUES
	(1,'衣服',0,'0,',1),
	(2,'男装',1,'0,1,',1),
	(3,'女装',1,'0,1,',1),
	(4,'手机',0,'0,',1),
	(5,'苹果手机',4,'0,4,',1),
	(6,'三星手机',4,'0,4,',1),
	(7,'安踏',2,'0,1,2,',1),
	(8,'纳微',3,'0,1,3,',0),
	(9,'iphone6',5,'0,4,5,',1),
	(10,'汽车',0,'0,',0),
	(11,'化妆品',0,'0,',0),
	(12,'宝马',10,'0,10,',1);

/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table goods
# ------------------------------------------------------------

DROP TABLE IF EXISTS `goods`;

CREATE TABLE `goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_name` varchar(255) NOT NULL DEFAULT '',
  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `goods_img` varchar(255) NOT NULL DEFAULT '',
  `create_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table menu
# ------------------------------------------------------------

DROP TABLE IF EXISTS `menu`;

CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `route` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `data` blob,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `menu` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;

INSERT INTO `menu` (`id`, `name`, `parent`, `route`, `order`, `data`)
VALUES
	(1,'用户管理',NULL,'/user-backend/index',1,X'7B2269636F6E223A2266612066612D75736572222C2276697369626C65223A747275657D'),
	(2,'分类列表',NULL,'/category/index',2,X'7B2269636F6E223A202266612066612D75736572222C202276697369626C65223A20747275657D');

/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table migration
# ------------------------------------------------------------

DROP TABLE IF EXISTS `migration`;

CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `migration` WRITE;
/*!40000 ALTER TABLE `migration` DISABLE KEYS */;

INSERT INTO `migration` (`version`, `apply_time`)
VALUES
	('m000000_000000_base',1496199865),
	('m140506_102106_rbac_init',1496199992),
	('m140602_111327_create_menu_table',1496202055),
	('m160312_050000_create_user',1496202055);

/*!40000 ALTER TABLE `migration` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `auth_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT '10',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table user_backend
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_backend`;

CREATE TABLE `user_backend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `auth_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `user_backend` WRITE;
/*!40000 ALTER TABLE `user_backend` DISABLE KEYS */;

INSERT INTO `user_backend` (`id`, `username`, `auth_key`, `password_hash`, `email`, `created_at`, `updated_at`)
VALUES
	(1,'qpao123','nGAMFf5rtmDHh6nSTtHSf6u4VEoaItTV','$2y$13$g.8.ZEMM75knkMO.IwdNr.ghxSYv5b9Ol/0dvSkeQZFCZPsgec.iS','111111@qq.com','2017-05-27 18:54:51','2017-05-27 18:54:51'),
	(2,'tom','HWUTQ97yzSadilOiZldi3PGnWXVn4Lqh','$2y$13$qsWL2q832j3I7QyR2G1Xa.u89fgUmhyKGkG5MT4W2YpS8ffWHoDIW','123456@qq.com','2017-05-31 11:56:13','2017-05-31 11:56:13');

/*!40000 ALTER TABLE `user_backend` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
