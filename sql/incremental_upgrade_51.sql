--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `name` CHAR(255) NOT NULL,
  `index` TINYINT UNSIGNED NOT NULL DEFAULT '0',
  `value` TEXT NOT NULL DEFAULT '',
   PRIMARY KEY (`name`, `index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Database version
--

UPDATE `server` SET `value` = '' WHERE `name` = 'latest_version' LIMIT 1;
UPDATE `server` SET `value` = '0' WHERE `name` = 'latest_version_idle_time' LIMIT 1;
UPDATE `server` SET `value` = '51' WHERE `name` = 'database_version' LIMIT 1;