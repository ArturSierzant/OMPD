<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant	                         |
//  | http://www.ompd.pl           		                                     |
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


global $cfg, $db;
require_once('include/initialize.inc.php');
require_once('include/library.inc.php');

$data = array();
$data['action'] = "";
$track_id = $_GET['track_id'];
$action = $_GET['action'];
$data['track_id'] = $track_id;
$data['group_type'] = $_GET['group_type']; //used in search.php
if ($action == 'add') {
	$query = mysqli_query($db,"SELECT MAX(position) as maxPosition FROM favoriteitem WHERE favorite_id = '" .$cfg['favorite_id'] . "'");
	$favoriteitem = mysqli_fetch_assoc($query);
	$maxPosition = (int) $favoriteitem['maxPosition'];
	$maxPosition++;
	mysqli_query($db,"INSERT INTO favoriteitem (track_id, stream_url, position, favorite_id) VALUES(
	'" . $track_id . "',
	'',
	'" . $maxPosition . "',
	'" . $cfg['favorite_id'] . "'
	)");
	if (mysqli_affected_rows($db) > 0) $data['action'] = "add";
}
elseif ($action == 'remove') {
	mysqli_query($db,"DELETE FROM favoriteitem WHERE track_id='" . $track_id . "' and favorite_id='" . $cfg['favorite_id'] . "'");
	if (mysqli_affected_rows($db) > 0) $data['action'] = "remove";
}

echo safe_json_encode($data);	
?>
	
