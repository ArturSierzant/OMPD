-- phpMyAdmin SQL Dump
-- version 3.3.5
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Czas wygenerowania: 18 Lip 2014, 04:53
-- Wersja serwera: 5.1.36
-- Wersja PHP: 5.3.14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `ompd`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `album`
--

CREATE TABLE IF NOT EXISTS `album` (
  `artist` varchar(333) NOT NULL DEFAULT '',
  `artist_alphabetic` varchar(333) NOT NULL DEFAULT '',
  `album` varchar(333) NOT NULL DEFAULT '',
  `year` smallint(4) unsigned DEFAULT NULL,
  `month` tinyint(2) unsigned DEFAULT NULL,
  `genre_id` varchar(255) NOT NULL DEFAULT '',
  `album_add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `discs` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `image_id` varchar(30) NOT NULL DEFAULT '',
  `album_id` varchar(11) NOT NULL DEFAULT '',
  `updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `album_dr` tinyint(3) unsigned  DEFAULT NULL,
  KEY `artist` (`artist`(333)),
  KEY `artist_alphabetic` (`artist_alphabetic`(333)),
  KEY `album` (`album`(333)),
  KEY `year` (`year`,`month`),
  KEY `genre_id` (`genre_id`),
  KEY `album_add_time` (`album_add_time`),
  KEY `album_id` (`album_id`),
  KEY `updated` (`updated`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `bitmap`
--

CREATE TABLE IF NOT EXISTS `bitmap` (
  `image` mediumblob NOT NULL,
  `filesize` bigint(20) unsigned NOT NULL DEFAULT '0',
  `filemtime` int(10) unsigned NOT NULL DEFAULT '0',
  `flag` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `image_front_width` int(10) unsigned NOT NULL DEFAULT '0',
  `image_front_height` int(10) unsigned NOT NULL DEFAULT '0',
  `image_front` text NOT NULL DEFAULT '',
  `image_back` varchar(255) NOT NULL DEFAULT '',
  `image_id` varchar(30) NOT NULL DEFAULT '',
  `album_id` varchar(11) NOT NULL DEFAULT '',
  `updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  KEY `flag` (`flag`),
  KEY `cd_front` (`image_front`(255)),
  KEY `cd_back` (`image_back`),
  KEY `album_id` (`album_id`),
  KEY `updated` (`updated`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `cache`
--

CREATE TABLE IF NOT EXISTS `cache` (
  `id` varchar(20) NOT NULL DEFAULT '',
  `profile` int(11) NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `idle_time` int(10) unsigned NOT NULL DEFAULT '0',
  `filesize` bigint(20) unsigned NOT NULL DEFAULT '0',
  `filemtime` int(10) unsigned NOT NULL DEFAULT '0',
  `tag_hash` varchar(32) NOT NULL DEFAULT '',
  `zip_hash` varchar(32) NOT NULL DEFAULT '',
  `relative_file` varchar(255) NOT NULL DEFAULT '',
  `updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  KEY `id` (`id`),
  KEY `profile` (`profile`),
  KEY `idle_time` (`idle_time`),
  KEY `relative_file` (`relative_file`),
  KEY `updated` (`updated`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `counter`
--

CREATE TABLE IF NOT EXISTS `counter` (
  `sid` varchar(40) NOT NULL DEFAULT '',
  `album_id` varchar(11) NOT NULL DEFAULT '',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `flag` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `sid` (`sid`),
  KEY `album_id` (`album_id`),
  KEY `user_id` (`user_id`),
  KEY `time` (`time`),
  KEY `flag` (`flag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `country`
--

CREATE TABLE IF NOT EXISTS `country` (
  `iso` char(2) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `code` smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `favorite`
--

CREATE TABLE IF NOT EXISTS `favorite` (
  `name` varchar(255) NOT NULL DEFAULT '',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `stream` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `favorite_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`favorite_id`),
  KEY `comment` (`comment`),
  KEY `name` (`name`),
  KEY `stream` (`stream`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `favoriteitem`
--

CREATE TABLE IF NOT EXISTS `favoriteitem` (
  `track_id` varchar(20) NOT NULL DEFAULT '',
  `stream_url` varchar(255) NOT NULL DEFAULT '',
  `position` smallint(5) unsigned NOT NULL DEFAULT '0',
  `favorite_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `favorite_id` (`favorite_id`,`position`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `genre`
--

CREATE TABLE IF NOT EXISTS `genre` (
  `genre_id` varchar(10) NOT NULL DEFAULT '',
  `genre` varchar(255) NOT NULL DEFAULT '',
  `updated` tinyint(4) NOT NULL DEFAULT '0',
  KEY `genre` (`genre`),
  KEY `genre_id` (`genre_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `player`
--

CREATE TABLE IF NOT EXISTS `player` (
  `player_name` varchar(255) NOT NULL DEFAULT '',
  `player_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `player_host` varchar(255) NOT NULL DEFAULT '',
  `player_port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `player_pass` varchar(255) NOT NULL DEFAULT '',
  `media_share` varchar(255) NOT NULL DEFAULT '',
  `mute_volume` smallint(5) unsigned NOT NULL DEFAULT '0',
  `player_id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`player_id`),
  KEY `httpq_name` (`player_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `random`
--

CREATE TABLE IF NOT EXISTS `random` (
  `sid` varchar(40) NOT NULL DEFAULT '',
  `track_id` varchar(20) NOT NULL DEFAULT '',
  `position` smallint(5) unsigned NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `sid` (`sid`),
  KEY `track_id` (`track_id`),
  KEY `position` (`position`),
  KEY `create_time` (`create_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `server`
--

CREATE TABLE IF NOT EXISTS `server` (
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `logged_in` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `hit_counter` int(10) unsigned NOT NULL DEFAULT '0',
  `visit_counter` int(10) unsigned NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `pre_login_time` bigint(20) unsigned NOT NULL DEFAULT '0',
  `login_time` int(10) unsigned NOT NULL DEFAULT '0',
  `idle_time` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `sid` varchar(40) NOT NULL DEFAULT '',
  `sign` varchar(40) NOT NULL DEFAULT '',
  `seed` varchar(40) NOT NULL DEFAULT '',
  `skin` varchar(255) NOT NULL DEFAULT 'ompd_default',
  `random_blacklist` varchar(255) NOT NULL DEFAULT '',
  `thumbnail` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `thumbnail_size` tinyint(3) unsigned NOT NULL DEFAULT '100',
  `stream_id` int(10) NOT NULL DEFAULT '-1',
  `download_id` int(10) NOT NULL DEFAULT '-1',
  `player_id` int(10) NOT NULL DEFAULT '1',
  KEY `user_id` (`user_id`),
  KEY `idle_time` (`idle_time`),
  KEY `sid` (`sid`),
  KEY `ip` (`ip`,`pre_login_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `share_download`
--

CREATE TABLE IF NOT EXISTS `share_download` (
  `ip` varchar(255) NOT NULL DEFAULT '',
  `sid` varchar(40) NOT NULL DEFAULT '',
  `album_id` varchar(11) NOT NULL DEFAULT '',
  `download_id` tinyint(4) NOT NULL DEFAULT '0',
  `expire_time` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `album_id` (`album_id`),
  KEY `expire_time` (`expire_time`),
  KEY `sid` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `share_stream`
--

CREATE TABLE IF NOT EXISTS `share_stream` (
  `ip` varchar(255) NOT NULL DEFAULT '',
  `sid` varchar(255) NOT NULL DEFAULT '',
  `album_id` varchar(11) NOT NULL DEFAULT '',
  `stream_id` tinyint(4) NOT NULL DEFAULT '0',
  `expire_time` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `sid` (`sid`),
  KEY `album_id` (`album_id`),
  KEY `expire_time` (`expire_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `track`
--

CREATE TABLE IF NOT EXISTS `track` (
  `artist` varchar(333) NOT NULL DEFAULT '',
  `title` varchar(333) NOT NULL DEFAULT '',
  `featuring` varchar(255) NOT NULL DEFAULT '',
  `relative_file` text NOT NULL DEFAULT '',
  `mime_type` varchar(64) NOT NULL DEFAULT '',
  `filesize` bigint(20) unsigned NOT NULL DEFAULT '0',
  `filemtime` int(10) unsigned NOT NULL DEFAULT '0',
  `miliseconds` int(10) unsigned NOT NULL DEFAULT '0',
  `audio_bitrate` int(10) unsigned NOT NULL DEFAULT '0',
  `audio_bits_per_sample` int(10) unsigned NOT NULL DEFAULT '0',
  `audio_sample_rate` int(10) unsigned NOT NULL DEFAULT '0',
  `audio_channels` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `audio_lossless` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `audio_compression_ratio` double unsigned NOT NULL DEFAULT '0',
  `audio_dataformat` varchar(64) NOT NULL DEFAULT '',
  `audio_encoder` varchar(64) NOT NULL DEFAULT '',
  `audio_profile` varchar(64) NOT NULL DEFAULT '',
  `video_dataformat` varchar(64) NOT NULL DEFAULT '',
  `video_codec` varchar(64) NOT NULL DEFAULT '',
  `video_resolution_x` int(10) unsigned NOT NULL DEFAULT '0',
  `video_resolution_y` int(10) unsigned NOT NULL DEFAULT '0',
  `video_framerate` int(10) unsigned NOT NULL DEFAULT '0',
  `disc` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `number` smallint(5) unsigned DEFAULT NULL,
  `error` varchar(255) NOT NULL DEFAULT '',
  `album_id` varchar(11) NOT NULL DEFAULT '',
  `track_id` varchar(20) NOT NULL DEFAULT '',
  `transcoded` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `genre` text NULL,
  `track_artist` varchar(333) NULL,
  `comment` text NULL,
  `year` smallint(4) unsigned DEFAULT NULL,
  `dr` tinyint(3) unsigned  DEFAULT NULL,
  `composer` VARCHAR(333) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  KEY `artist` (`artist`(333)),
  KEY `title` (`title`(333)),
  KEY `relative_file` (`relative_file`(255)),
  KEY `audio_dataformat` (`audio_dataformat`),
  KEY `video_dataformat` (`video_dataformat`),
  KEY `album_id` (`album_id`,`disc`),
  KEY `track_id` (`track_id`),
  KEY `updated` (`updated`),
  KEY `error` (`error`),
  KEY `transcoded` (`transcoded`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(40) NOT NULL DEFAULT '',
  `seed` varchar(40) NOT NULL DEFAULT '',
  `version` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access_media` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access_popular` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access_favorite` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access_playlist` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access_play` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access_add` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access_stream` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access_download` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access_cover` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access_record` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access_statistics` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`user_id`),
  KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `update_progress`
--

CREATE TABLE IF NOT EXISTS `update_progress` (
  `update_status` int(11) NOT NULL DEFAULT '0',
  `structure_image` longtext NOT NULL,
  `file_info` longtext NOT NULL,
  `cleanup` text NOT NULL,
  `update_time` text NOT NULL,
  `last_update` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `album_id`
--

CREATE TABLE IF NOT EXISTS `album_id` (
  `album_id` varchar(14) NOT NULL,
  `path` text NOT NULL,
  `album_add_time` int(10) unsigned NOT NULL,
  `updated` tinytext NOT NULL,
  UNIQUE KEY `album_id` (`album_id`),
  KEY `album` (`album_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Country code
-- 

INSERT IGNORE INTO `country` VALUES ('af', 'Afghanistan', 4);
INSERT IGNORE INTO `country` VALUES ('ax', 'Aland Islands', 248);
INSERT IGNORE INTO `country` VALUES ('al', 'Albania', 8);
INSERT IGNORE INTO `country` VALUES ('dz', 'Algeria', 12);
INSERT IGNORE INTO `country` VALUES ('as', 'American Samoa', 16);
INSERT IGNORE INTO `country` VALUES ('ad', 'Andorra', 20);
INSERT IGNORE INTO `country` VALUES ('ao', 'Angola', 24);
INSERT IGNORE INTO `country` VALUES ('ai', 'Anguilla', 660);
INSERT IGNORE INTO `country` VALUES ('aq', 'Antarctica', 10);
INSERT IGNORE INTO `country` VALUES ('ag', 'Antigua and Barbuda', 28);
INSERT IGNORE INTO `country` VALUES ('ar', 'Argentina', 32);
INSERT IGNORE INTO `country` VALUES ('am', 'Armenia', 51);
INSERT IGNORE INTO `country` VALUES ('aw', 'Aruba', 533);
INSERT IGNORE INTO `country` VALUES ('au', 'Australia', 36);
INSERT IGNORE INTO `country` VALUES ('at', 'Austria', 40);
INSERT IGNORE INTO `country` VALUES ('az', 'Azerbaijan', 31);
INSERT IGNORE INTO `country` VALUES ('bs', 'Bahamas', 44);
INSERT IGNORE INTO `country` VALUES ('bh', 'Bahrain', 48);
INSERT IGNORE INTO `country` VALUES ('bd', 'Bangladesh', 50);
INSERT IGNORE INTO `country` VALUES ('bb', 'Barbados', 52);
INSERT IGNORE INTO `country` VALUES ('by', 'Belarus', 112);
INSERT IGNORE INTO `country` VALUES ('be', 'Belgium', 56);
INSERT IGNORE INTO `country` VALUES ('bz', 'Belize', 84);
INSERT IGNORE INTO `country` VALUES ('bj', 'Benin', 204);
INSERT IGNORE INTO `country` VALUES ('bm', 'Bermuda', 60);
INSERT IGNORE INTO `country` VALUES ('bt', 'Bhutan', 64);
INSERT IGNORE INTO `country` VALUES ('bo', 'Bolivia', 68);
INSERT IGNORE INTO `country` VALUES ('ba', 'Bosnia and Herzegovina', 70);
INSERT IGNORE INTO `country` VALUES ('bw', 'Botswana', 72);
INSERT IGNORE INTO `country` VALUES ('bv', 'Bouvet Island', 74);
INSERT IGNORE INTO `country` VALUES ('br', 'Brazil', 76);
INSERT IGNORE INTO `country` VALUES ('io', 'British Indian Ocean Territory', 86);
INSERT IGNORE INTO `country` VALUES ('bn', 'Brunei Darussalam', 96);
INSERT IGNORE INTO `country` VALUES ('bg', 'Bulgaria', 100);
INSERT IGNORE INTO `country` VALUES ('bf', 'Burkina Faso', 854);
INSERT IGNORE INTO `country` VALUES ('bi', 'Burundi', 108);
INSERT IGNORE INTO `country` VALUES ('kh', 'Cambodia', 116);
INSERT IGNORE INTO `country` VALUES ('cm', 'Cameroon', 120);
INSERT IGNORE INTO `country` VALUES ('ca', 'Canada', 124);
INSERT IGNORE INTO `country` VALUES ('cv', 'Cape Verde', 132);
INSERT IGNORE INTO `country` VALUES ('ky', 'Cayman Islands', 136);
INSERT IGNORE INTO `country` VALUES ('cf', 'Central African Republic', 140);
INSERT IGNORE INTO `country` VALUES ('td', 'Chad', 148);
INSERT IGNORE INTO `country` VALUES ('cl', 'Chile', 152);
INSERT IGNORE INTO `country` VALUES ('cn', 'China', 156);
INSERT IGNORE INTO `country` VALUES ('cx', 'Christmas Island', 162);
INSERT IGNORE INTO `country` VALUES ('cc', 'Cocos (Keeling) Islands', 166);
INSERT IGNORE INTO `country` VALUES ('co', 'Colombia', 170);
INSERT IGNORE INTO `country` VALUES ('km', 'Comoros', 174);
INSERT IGNORE INTO `country` VALUES ('cg', 'Congo', 178);
INSERT IGNORE INTO `country` VALUES ('cd', 'Congo, the Democratic Republic of the', 180);
INSERT IGNORE INTO `country` VALUES ('ck', 'Cook Islands', 184);
INSERT IGNORE INTO `country` VALUES ('cr', 'Costa Rica', 188);
INSERT IGNORE INTO `country` VALUES ('ci', 'Cote D''Ivoire', 384);
INSERT IGNORE INTO `country` VALUES ('hr', 'Croatia', 191);
INSERT IGNORE INTO `country` VALUES ('cu', 'Cuba', 192);
INSERT IGNORE INTO `country` VALUES ('cy', 'Cyprus', 196);
INSERT IGNORE INTO `country` VALUES ('cz', 'Czech Republic', 203);
INSERT IGNORE INTO `country` VALUES ('dk', 'Denmark', 208);
INSERT IGNORE INTO `country` VALUES ('dj', 'Djibouti', 262);
INSERT IGNORE INTO `country` VALUES ('dm', 'Dominica', 212);
INSERT IGNORE INTO `country` VALUES ('do', 'Dominican Republic', 214);
INSERT IGNORE INTO `country` VALUES ('ec', 'Ecuador', 218);
INSERT IGNORE INTO `country` VALUES ('eg', 'Egypt', 818);
INSERT IGNORE INTO `country` VALUES ('sv', 'El Salvador', 222);
INSERT IGNORE INTO `country` VALUES ('gq', 'Equatorial Guinea', 226);
INSERT IGNORE INTO `country` VALUES ('er', 'Eritrea', 232);
INSERT IGNORE INTO `country` VALUES ('ee', 'Estonia', 233);
INSERT IGNORE INTO `country` VALUES ('et', 'Ethiopia', 231);
INSERT IGNORE INTO `country` VALUES ('fk', 'Falkland Islands (Malvinas)', 238);
INSERT IGNORE INTO `country` VALUES ('fo', 'Faroe Islands', 234);
INSERT IGNORE INTO `country` VALUES ('fj', 'Fiji', 242);
INSERT IGNORE INTO `country` VALUES ('fi', 'Finland', 246);
INSERT IGNORE INTO `country` VALUES ('fr', 'France', 250);
INSERT IGNORE INTO `country` VALUES ('gf', 'French Guiana', 254);
INSERT IGNORE INTO `country` VALUES ('pf', 'French Polynesia', 258);
INSERT IGNORE INTO `country` VALUES ('tf', 'French Southern Territories', 260);
INSERT IGNORE INTO `country` VALUES ('ga', 'Gabon', 266);
INSERT IGNORE INTO `country` VALUES ('gm', 'Gambia', 270);
INSERT IGNORE INTO `country` VALUES ('ge', 'Georgia', 268);
INSERT IGNORE INTO `country` VALUES ('de', 'Germany', 276);
INSERT IGNORE INTO `country` VALUES ('gh', 'Ghana', 288);
INSERT IGNORE INTO `country` VALUES ('gi', 'Gibraltar', 292);
INSERT IGNORE INTO `country` VALUES ('gr', 'Greece', 300);
INSERT IGNORE INTO `country` VALUES ('gl', 'Greenland', 304);
INSERT IGNORE INTO `country` VALUES ('gd', 'Grenada', 308);
INSERT IGNORE INTO `country` VALUES ('gp', 'Guadeloupe', 312);
INSERT IGNORE INTO `country` VALUES ('gu', 'Guam', 316);
INSERT IGNORE INTO `country` VALUES ('gt', 'Guatemala', 320);
INSERT IGNORE INTO `country` VALUES ('gg', 'Guernsey', 831);
INSERT IGNORE INTO `country` VALUES ('gn', 'Guinea', 324);
INSERT IGNORE INTO `country` VALUES ('gw', 'Guinea-Bissau', 624);
INSERT IGNORE INTO `country` VALUES ('gy', 'Guyana', 328);
INSERT IGNORE INTO `country` VALUES ('ht', 'Haiti', 332);
INSERT IGNORE INTO `country` VALUES ('hm', 'Heard Island and Mcdonald Islands', 334);
INSERT IGNORE INTO `country` VALUES ('va', 'Holy See (Vatican City State)', 336);
INSERT IGNORE INTO `country` VALUES ('hn', 'Honduras', 340);
INSERT IGNORE INTO `country` VALUES ('hk', 'Hong Kong', 344);
INSERT IGNORE INTO `country` VALUES ('hu', 'Hungary', 348);
INSERT IGNORE INTO `country` VALUES ('is', 'Iceland', 352);
INSERT IGNORE INTO `country` VALUES ('in', 'India', 356);
INSERT IGNORE INTO `country` VALUES ('id', 'Indonesia', 360);
INSERT IGNORE INTO `country` VALUES ('ir', 'Iran, Islamic Republic of', 364);
INSERT IGNORE INTO `country` VALUES ('iq', 'Iraq', 368);
INSERT IGNORE INTO `country` VALUES ('ie', 'Ireland', 372);
INSERT IGNORE INTO `country` VALUES ('il', 'Israel', 376);
INSERT IGNORE INTO `country` VALUES ('it', 'Italy', 380);
INSERT IGNORE INTO `country` VALUES ('jm', 'Jamaica', 388);
INSERT IGNORE INTO `country` VALUES ('jp', 'Japan', 392);
INSERT IGNORE INTO `country` VALUES ('je', 'Jersey', 832);
INSERT IGNORE INTO `country` VALUES ('jo', 'Jordan', 400);
INSERT IGNORE INTO `country` VALUES ('kz', 'Kazakhstan', 398);
INSERT IGNORE INTO `country` VALUES ('ke', 'Kenya', 404);
INSERT IGNORE INTO `country` VALUES ('ki', 'Kiribati', 296);
INSERT IGNORE INTO `country` VALUES ('kp', 'Korea, Democratic People''s Republic of', 408);
INSERT IGNORE INTO `country` VALUES ('kr', 'Korea, Republic of', 410);
INSERT IGNORE INTO `country` VALUES ('kw', 'Kuwait', 414);
INSERT IGNORE INTO `country` VALUES ('kg', 'Kyrgyzstan', 417);
INSERT IGNORE INTO `country` VALUES ('la', 'Lao People''s Democratic Republic', 418);
INSERT IGNORE INTO `country` VALUES ('lv', 'Latvia', 428);
INSERT IGNORE INTO `country` VALUES ('lb', 'Lebanon', 422);
INSERT IGNORE INTO `country` VALUES ('ls', 'Lesotho', 426);
INSERT IGNORE INTO `country` VALUES ('lr', 'Liberia', 430);
INSERT IGNORE INTO `country` VALUES ('ly', 'Libyan Arab Jamahiriya', 434);
INSERT IGNORE INTO `country` VALUES ('li', 'Liechtenstein', 438);
INSERT IGNORE INTO `country` VALUES ('lt', 'Lithuania', 440);
INSERT IGNORE INTO `country` VALUES ('lu', 'Luxembourg', 442);
INSERT IGNORE INTO `country` VALUES ('mo', 'Macao', 446);
INSERT IGNORE INTO `country` VALUES ('mk', 'Macedonia, the Former Yugoslav Republic of', 807);
INSERT IGNORE INTO `country` VALUES ('mg', 'Madagascar', 450);
INSERT IGNORE INTO `country` VALUES ('mw', 'Malawi', 454);
INSERT IGNORE INTO `country` VALUES ('my', 'Malaysia', 458);
INSERT IGNORE INTO `country` VALUES ('mv', 'Maldives', 462);
INSERT IGNORE INTO `country` VALUES ('ml', 'Mali', 466);
INSERT IGNORE INTO `country` VALUES ('mt', 'Malta', 470);
INSERT IGNORE INTO `country` VALUES ('mh', 'Marshall Islands', 584);
INSERT IGNORE INTO `country` VALUES ('mq', 'Martinique', 474);
INSERT IGNORE INTO `country` VALUES ('mr', 'Mauritania', 478);
INSERT IGNORE INTO `country` VALUES ('mu', 'Mauritius', 480);
INSERT IGNORE INTO `country` VALUES ('yt', 'Mayotte', 175);
INSERT IGNORE INTO `country` VALUES ('mx', 'Mexico', 484);
INSERT IGNORE INTO `country` VALUES ('fm', 'Micronesia, Federated States of', 583);
INSERT IGNORE INTO `country` VALUES ('md', 'Moldova, Republic of', 498);
INSERT IGNORE INTO `country` VALUES ('mc', 'Monaco', 492);
INSERT IGNORE INTO `country` VALUES ('mn', 'Mongolia', 496);
INSERT IGNORE INTO `country` VALUES ('me', 'Montenegro', 499);
INSERT IGNORE INTO `country` VALUES ('me', 'Montenegro', 499);
INSERT IGNORE INTO `country` VALUES ('ms', 'Montserrat', 500);
INSERT IGNORE INTO `country` VALUES ('ma', 'Morocco', 504);
INSERT IGNORE INTO `country` VALUES ('mz', 'Mozambique', 508);
INSERT IGNORE INTO `country` VALUES ('mm', 'Myanmar', 104);
INSERT IGNORE INTO `country` VALUES ('na', 'Namibia', 516);
INSERT IGNORE INTO `country` VALUES ('nr', 'Nauru', 520);
INSERT IGNORE INTO `country` VALUES ('np', 'Nepal', 524);
INSERT IGNORE INTO `country` VALUES ('nl', 'Netherlands', 528);
INSERT IGNORE INTO `country` VALUES ('an', 'Netherlands Antilles', 530);
INSERT IGNORE INTO `country` VALUES ('nc', 'New Caledonia', 540);
INSERT IGNORE INTO `country` VALUES ('nz', 'New Zealand', 554);
INSERT IGNORE INTO `country` VALUES ('ni', 'Nicaragua', 558);
INSERT IGNORE INTO `country` VALUES ('ne', 'Niger', 562);
INSERT IGNORE INTO `country` VALUES ('ng', 'Nigeria', 566);
INSERT IGNORE INTO `country` VALUES ('nu', 'Niue', 570);
INSERT IGNORE INTO `country` VALUES ('nf', 'Norfolk Island', 574);
INSERT IGNORE INTO `country` VALUES ('mp', 'Northern Mariana Islands', 580);
INSERT IGNORE INTO `country` VALUES ('no', 'Norway', 578);
INSERT IGNORE INTO `country` VALUES ('om', 'Oman', 512);
INSERT IGNORE INTO `country` VALUES ('pk', 'Pakistan', 586);
INSERT IGNORE INTO `country` VALUES ('pw', 'Palau', 585);
INSERT IGNORE INTO `country` VALUES ('ps', 'Palestinian Territory, Occupied', 275);
INSERT IGNORE INTO `country` VALUES ('pa', 'Panama', 591);
INSERT IGNORE INTO `country` VALUES ('pg', 'Papua New Guinea', 598);
INSERT IGNORE INTO `country` VALUES ('py', 'Paraguay', 600);
INSERT IGNORE INTO `country` VALUES ('pe', 'Peru', 604);
INSERT IGNORE INTO `country` VALUES ('ph', 'Philippines', 608);
INSERT IGNORE INTO `country` VALUES ('pn', 'Pitcairn', 612);
INSERT IGNORE INTO `country` VALUES ('pl', 'Poland', 616);
INSERT IGNORE INTO `country` VALUES ('pt', 'Portugal', 620);
INSERT IGNORE INTO `country` VALUES ('pr', 'Puerto Rico', 630);
INSERT IGNORE INTO `country` VALUES ('qa', 'Qatar', 634);
INSERT IGNORE INTO `country` VALUES ('re', 'Reunion', 638);
INSERT IGNORE INTO `country` VALUES ('ro', 'Romania', 642);
INSERT IGNORE INTO `country` VALUES ('ru', 'Russian Federation', 643);
INSERT IGNORE INTO `country` VALUES ('rw', 'Rwanda', 646);
INSERT IGNORE INTO `country` VALUES ('sh', 'Saint Helena', 654);
INSERT IGNORE INTO `country` VALUES ('kn', 'Saint Kitts and Nevis', 659);
INSERT IGNORE INTO `country` VALUES ('lc', 'Saint Lucia', 662);
INSERT IGNORE INTO `country` VALUES ('pm', 'Saint Pierre and Miquelon', 666);
INSERT IGNORE INTO `country` VALUES ('vc', 'Saint Vincent and the Grenadines', 670);
INSERT IGNORE INTO `country` VALUES ('ws', 'Samoa', 882);
INSERT IGNORE INTO `country` VALUES ('sm', 'San Marino', 674);
INSERT IGNORE INTO `country` VALUES ('st', 'Sao Tome and Principe', 678);
INSERT IGNORE INTO `country` VALUES ('sa', 'Saudi Arabia', 682);
INSERT IGNORE INTO `country` VALUES ('sn', 'Senegal', 686);
INSERT IGNORE INTO `country` VALUES ('rs', 'Serbia', 688);
INSERT IGNORE INTO `country` VALUES ('rs', 'Serbia', 688);
INSERT IGNORE INTO `country` VALUES ('sc', 'Seychelles', 690);
INSERT IGNORE INTO `country` VALUES ('sl', 'Sierra Leone', 694);
INSERT IGNORE INTO `country` VALUES ('sg', 'Singapore', 702);
INSERT IGNORE INTO `country` VALUES ('sk', 'Slovakia', 703);
INSERT IGNORE INTO `country` VALUES ('si', 'Slovenia', 705);
INSERT IGNORE INTO `country` VALUES ('sb', 'Solomon Islands', 90);
INSERT IGNORE INTO `country` VALUES ('so', 'Somalia', 706);
INSERT IGNORE INTO `country` VALUES ('za', 'South Africa', 710);
INSERT IGNORE INTO `country` VALUES ('gs', 'South Georgia and the South Sandwich Islands', 239);
INSERT IGNORE INTO `country` VALUES ('es', 'Spain', 724);
INSERT IGNORE INTO `country` VALUES ('lk', 'Sri Lanka', 144);
INSERT IGNORE INTO `country` VALUES ('sd', 'Sudan', 736);
INSERT IGNORE INTO `country` VALUES ('sr', 'Suriname', 740);
INSERT IGNORE INTO `country` VALUES ('sj', 'Svalbard and Jan Mayen', 744);
INSERT IGNORE INTO `country` VALUES ('sz', 'Swaziland', 748);
INSERT IGNORE INTO `country` VALUES ('se', 'Sweden', 752);
INSERT IGNORE INTO `country` VALUES ('ch', 'Switzerland', 756);
INSERT IGNORE INTO `country` VALUES ('sy', 'Syrian Arab Republic', 760);
INSERT IGNORE INTO `country` VALUES ('tw', 'Taiwan, Province of China', 158);
INSERT IGNORE INTO `country` VALUES ('tj', 'Tajikistan', 762);
INSERT IGNORE INTO `country` VALUES ('tz', 'Tanzania, United Republic of', 834);
INSERT IGNORE INTO `country` VALUES ('th', 'Thailand', 764);
INSERT IGNORE INTO `country` VALUES ('tl', 'Timor-Leste', 626);
INSERT IGNORE INTO `country` VALUES ('tg', 'Togo', 768);
INSERT IGNORE INTO `country` VALUES ('tk', 'Tokelau', 772);
INSERT IGNORE INTO `country` VALUES ('to', 'Tonga', 776);
INSERT IGNORE INTO `country` VALUES ('tt', 'Trinidad and Tobago', 780);
INSERT IGNORE INTO `country` VALUES ('tn', 'Tunisia', 788);
INSERT IGNORE INTO `country` VALUES ('tr', 'Turkey', 792);
INSERT IGNORE INTO `country` VALUES ('tm', 'Turkmenistan', 795);
INSERT IGNORE INTO `country` VALUES ('tc', 'Turks and Caicos Islands', 796);
INSERT IGNORE INTO `country` VALUES ('tv', 'Tuvalu', 798);
INSERT IGNORE INTO `country` VALUES ('ug', 'Uganda', 800);
INSERT IGNORE INTO `country` VALUES ('ua', 'Ukraine', 804);
INSERT IGNORE INTO `country` VALUES ('ae', 'United Arab Emirates', 784);
INSERT IGNORE INTO `country` VALUES ('gb', 'United Kingdom', 826);
INSERT IGNORE INTO `country` VALUES ('us', 'United States', 840);
INSERT IGNORE INTO `country` VALUES ('um', 'United States Minor Outlying Islands', 581);
INSERT IGNORE INTO `country` VALUES ('uy', 'Uruguay', 858);
INSERT IGNORE INTO `country` VALUES ('uz', 'Uzbekistan', 860);
INSERT IGNORE INTO `country` VALUES ('vu', 'Vanuatu', 548);
INSERT IGNORE INTO `country` VALUES ('ve', 'Venezuela', 862);
INSERT IGNORE INTO `country` VALUES ('vn', 'Viet Nam', 704);
INSERT IGNORE INTO `country` VALUES ('vg', 'Virgin Islands, British', 92);
INSERT IGNORE INTO `country` VALUES ('vi', 'Virgin Islands, U.s.', 850);
INSERT IGNORE INTO `country` VALUES ('wf', 'Wallis and Futuna', 876);
INSERT IGNORE INTO `country` VALUES ('eh', 'Western Sahara', 732);
INSERT IGNORE INTO `country` VALUES ('ye', 'Yemen', 887);
INSERT IGNORE INTO `country` VALUES ('zm', 'Zambia', 894);
INSERT IGNORE INTO `country` VALUES ('zw', 'Zimbabwe', 716);


-- --------------------------------------------------------

-- 
-- Default users
--

INSERT INTO `user` VALUES ('admin', '4008750ce237101f5e39ec63c8ae46f134a40a65', 'xrR1KfV9FfLAwj2YMfeK1cttaMRHafauezAmbg51', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `user` VALUES ('anonymous', 'adf8efe68157cf37503f86d602bec6d593750c33', 'I33sJY_HNVMlbGL1nBzY0VdXebb4oSkJIGcnZzLZ', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

-- 
-- Default server
--

INSERT INTO `server` VALUES ('database_version', '44');
INSERT INTO `server` VALUES ('escape_char_hash', 'd41d8cd98f00b204e9800998ecf8427e');
INSERT INTO `server` VALUES ('getid3_hash', 'd41d8cd98f00b204e9800998ecf8427e');
INSERT INTO `server` VALUES ('image_quality', '0');
INSERT INTO `server` VALUES ('image_size', '0');
INSERT INTO `server` VALUES ('latest_version', '');
INSERT INTO `server` VALUES ('latest_version_idle_time', '0');
