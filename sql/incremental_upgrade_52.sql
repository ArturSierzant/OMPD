--
-- Changes in `counter` table
--

ALTER TABLE `counter` CHANGE `album_id` `album_id` VARCHAR(100) NOT NULL DEFAULT '';

-- --------------------------------------------------------

--
-- Database version
--

UPDATE `server` SET `value` = '' WHERE `name` = 'latest_version' LIMIT 1;
UPDATE `server` SET `value` = '0' WHERE `name` = 'latest_version_idle_time' LIMIT 1;
UPDATE `server` SET `value` = '52' WHERE `name` = 'database_version' LIMIT 1;