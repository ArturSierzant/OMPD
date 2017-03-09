ALTER TABLE `album` DROP INDEX `artist`;
ALTER TABLE `album` DROP INDEX `artist_alphabetic`;
ALTER TABLE `album` DROP INDEX `album`;

ALTER TABLE `album` CHANGE `artist` `artist` VARCHAR(333) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `album` CHANGE `artist_alphabetic` `artist_alphabetic` VARCHAR(333) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `album` CHANGE `album` `album` VARCHAR(333) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `album` CHANGE `genre_id` `genre_id` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

ALTER TABLE `album` ADD INDEX(`artist`(333));
ALTER TABLE `album` ADD INDEX(`artist_alphabetic`(333));
ALTER TABLE `album` ADD INDEX(`album`(333));

ALTER TABLE `track` DROP INDEX `artist`;
ALTER TABLE `track` DROP INDEX `title`;

ALTER TABLE `track` CHANGE `artist` `artist` VARCHAR(333) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `track` CHANGE `title` `title` VARCHAR(333) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `track` CHANGE `track_artist` `track_artist` VARCHAR(333) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `track` ADD INDEX(`artist`(333));
ALTER TABLE `track` ADD INDEX(`title`(333));





-- --------------------------------------------------------

--
-- Database version
--

UPDATE `server` SET `value` = '' WHERE `name` = 'latest_version' LIMIT 1;
UPDATE `server` SET `value` = '0' WHERE `name` = 'latest_version_idle_time' LIMIT 1;
UPDATE `server` SET `value` = '42' WHERE `name` = 'database_version' LIMIT 1;