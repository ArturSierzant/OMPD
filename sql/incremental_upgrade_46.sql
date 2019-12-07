-- --------------------------------------------------------
--
-- Zmiany w tabeli `tidal_album`
--

ALTER TABLE `tidal_album` ADD COLUMN `cover` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `tidal_album` ADD COLUMN `type` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

-- --------------------------------------------------------

--
-- Database version
--

UPDATE `server` SET `value` = '' WHERE `name` = 'latest_version' LIMIT 1;
UPDATE `server` SET `value` = '0' WHERE `name` = 'latest_version_idle_time' LIMIT 1;
UPDATE `server` SET `value` = '46' WHERE `name` = 'database_version' LIMIT 1;