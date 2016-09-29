<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant                            |
//  | http://www.ompd.pl                                                     |
//  |                                                                        |
//  |                                                                        |
//  | This program is free software: you can redistribute it and/or modify   |
//  | it under the terms of the GNU General Public License as published by   |
//  | the Free Software Foundation, either version 3 of the License, or      |
//  | (at your option) any later version.                                    |
//  |                                                                        |
//  | This program is distributed in the hope that it will be useful,        |
//  | but WITHOUT ANY WARRANTY; without even the implied warranty of         |
//  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          |
//  | GNU General Public License for more details.                           |
//  |                                                                        |
//  | You should have received a copy of the GNU General Public License      |
//  | along with this program.  If not, see <http://www.gnu.org/licenses/>.  |
//  +------------------------------------------------------------------------+


require_once('include/initialize.inc.php');

global $cfg, $db;
global $base_size, $spaces, $scroll_bar_correction;

authenticate('access_media');

$size = $_POST["tileSize"];

	/* $query = mysqli_query($db,"SELECT UPDATE_TIME
		FROM   information_schema.tables
		WHERE  TABLE_SCHEMA = '" . $cfg['mysqli_db'] . "'
				AND TABLE_NAME = 'counter' LIMIT 1");
	$suggested = mysqli_fetch_assoc($query);
	$modTime = strtotime($suggested['UPDATE_TIME']); */
	
	$time4months = time() - (60 * 60 * 24 * 7 * 12); //12 weeks =~ 3 months
	$query = mysqli_query($db,"SELECT * FROM album 
	WHERE album_id NOT IN 
		(SELECT album_id FROM counter WHERE time > " . $time4months  . ")
	ORDER BY RAND() LIMIT 10");
	
	while ($album = mysqli_fetch_assoc($query)) {
		draw_tile($size,$album);
	}
	
?>
	
