--
-- Table structure for table `tidal_token`
--

CREATE TABLE `tidal_token` (
  `time` int(10) NOT NULL,
  `access_token` text COLLATE utf8_unicode_ci NOT NULL,
  `refresh_token` text COLLATE utf8_unicode_ci NOT NULL,
  `token_type` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `expires_in` int(10) NOT NULL,
  `expires_after` int(10) NOT NULL,
  `userId` int(11) NOT NULL,
  `countryCode` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `username` text COLLATE utf8_unicode_ci NOT NULL,
  `deviceCode` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 
-- Default Tidal token

INSERT INTO `tidal_token`(`time`, `access_token`, `refresh_token`, `token_type`, `expires_in`, `expires_after`, `userId`, `countryCode`, `username`, `deviceCode`) VALUES (0,'','','',0,0,0,'','','');

-- --------------------------------------------------------

--
-- Database version
--

UPDATE `server` SET `value` = '' WHERE `name` = 'latest_version' LIMIT 1;
UPDATE `server` SET `value` = '0' WHERE `name` = 'latest_version_idle_time' LIMIT 1;
UPDATE `server` SET `value` = '50' WHERE `name` = 'database_version' LIMIT 1;