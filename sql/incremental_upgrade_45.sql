--
-- Struktura tabeli dla  `tidal_album`
--

CREATE TABLE IF NOT EXISTS `tidal_album` (
  `album_id` bigint unsigned NOT NULL DEFAULT '0',
  `artist` varchar(333) NOT NULL DEFAULT '',
  `artist_alphabetic` varchar(333) NOT NULL DEFAULT '',
  `artist_id` bigint unsigned NOT NULL DEFAULT '0',
  `album` varchar(333) NOT NULL DEFAULT '',
  `album_date` varchar(21) DEFAULT NULL,
  `genre_id` varchar(255) NOT NULL DEFAULT '',
  `discs` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `seconds` int(7) unsigned NOT NULL DEFAULT '0',
  `last_update_time` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `album_id` (`album_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------
--
-- Struktura tabeli dla  `tidal_track`
--

CREATE TABLE IF NOT EXISTS `tidal_track` (
  `track_id` bigint unsigned NOT NULL DEFAULT '0',
  `title` varchar(333) NOT NULL DEFAULT '',
  `artist` varchar(333) NOT NULL DEFAULT '',
  `artist_alphabetic` varchar(333) NOT NULL DEFAULT '',
  `genre_id` varchar(255) NOT NULL DEFAULT '',
  `disc` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `seconds` int(5) unsigned NOT NULL DEFAULT '0',
  `number` smallint(5) unsigned DEFAULT NULL,
  `album_id` bigint unsigned NOT NULL DEFAULT '0',
  `last_update_time` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `track_id` (`track_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------
--
-- Zmiany w tabeli `counter`
--

ALTER TABLE `counter` CHANGE `album_id` `album_id` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

-- --------------------------------------------------------

--
-- Database version
--

UPDATE `server` SET `value` = '' WHERE `name` = 'latest_version' LIMIT 1;
UPDATE `server` SET `value` = '0' WHERE `name` = 'latest_version_idle_time' LIMIT 1;
UPDATE `server` SET `value` = '45' WHERE `name` = 'database_version' LIMIT 1;