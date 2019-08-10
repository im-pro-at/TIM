<?php
/*
  Database.php
  
  Autor: im-pro
*/

$Database_TableNames_Backup= array('user', 'eintrag', 'link', 'log', 'tag');
$Database_Tabels=
'
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NULL,
  `password` varchar(100)  NULL,
  `lastaktive` INT( 14 ) NOT NULL,
  `cookie` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=2 ;

CREATE TABLE `eintrag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(100) DEFAULT NULL,
  `title` text NOT NULL,
  `beschreibung` text DEFAULT NULL,
  `link1` text DEFAULT NULL,
  `link2` text DEFAULT NULL,
  `link3` text DEFAULT NULL,
  `count` INT( 14 ) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `typo` (`location`,`title`,`beschreibung`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `eintragid` int(10) unsigned NOT NULL,
  `tagid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `eintragid` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `changetype`  varchar(10) NOT NULL,
  `oldvalues` text,
  `newvalues` text,
  `datum`  INT( 14 ),
  PRIMARY KEY (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `beschreibung` text NOT NULL,
  `typo` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  FULLTEXT KEY `typo` (`typo`)
) ENGINE=INNODB  AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

';
$Database_Admin_Entry=
'
INSERT INTO `user` (`id`, `name`, `password`, `lastaktive`, `cookie`) VALUES
(0, "?", "", "", ""),
(1, "admin", "5a52fc6648e5e02c69420711b3b89395abf8a8240be6f8f5b5bee0b8ade31073", 1511117379, "")
';


?>