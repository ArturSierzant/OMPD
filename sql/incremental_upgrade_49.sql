-- --------------------------------------------------------
--
-- Zmiany w tabeli `tidal_album`
--

ALTER TABLE `tidal_album` ADD COLUMN `audio_quality` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';


-- --------------------------------------------------------

--
-- Database version
--

UPDATE `server` SET `value` = '' WHERE `name` = 'latest_version' LIMIT 1;
UPDATE `server` SET `value` = '0' WHERE `name` = 'latest_version_idle_time' LIMIT 1;
UPDATE `server` SET `value` = '49' WHERE `name` = 'database_version' LIMIT 1;