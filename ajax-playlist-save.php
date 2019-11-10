<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2019 Artur Sierzant                            |
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


global $cfg, $db;
require_once('include/initialize.inc.php');
require_once('include/library.inc.php');
require_once('include/play.inc.php');

$data = array();
$data['action_status'] = false;
$data['not_compatible'] = false;
$action = $_GET['action'];
$name = $_GET['name'];
$saveAs = (int) $_GET['saveAs'];
$comment = $_GET['comment'];
$host = $_GET['host'];
$port = $_GET['port'];
$saveTrackId = $_GET['saveTrackId'];
$saveTrack = $_GET['saveTrack'];

/* if ($cfg['username'] == '') {
	$data['user'] = $cfg;
	echo safe_json_encode($data);
	return;
} */

if ($action == 'SaveAs') {
	if ($name != '') {
		authenticate('access_admin');
		mysqli_query($db,'INSERT INTO favorite (name,comment) VALUES ("' . mysqli_real_escape_string($db,$name) . '","' . mysqli_real_escape_string($db,$comment) . '")');
		$favorite_id = mysqli_insert_id($db);
		if ($saveTrack == 'true')
			importTrack($favorite_id, $saveTrackId);
		else
			importFavorite($favorite_id, $host, $port, 'import');
		$data['action_status'] = true;
		$data['select_options']= listOfFavorites(true,false,$saveTrackId);
		
	}	
}
elseif ($action == 'AddTo') {
	if ($saveAs) {
		authenticate('access_admin');
		if ($saveTrack == 'true') {
			if ($saveAs > 0) {//add track only if playlist is file playlist
				importTrack($saveAs, $saveTrackId);
				$data['select_options']= listOfFavorites(true,false,$saveTrackId);
			}
			else {
				$data['not_compatible'] = true;
			}
		}
		else {
			importFavorite($saveAs, $host, $port, 'add');
		}
		
		$data['action_status'] = true;
	}
	
}

echo safe_json_encode($data);	


function importTrack($favorite_id, $track_id) {
	global $cfg, $db, $data;
	
	$query = mysqli_query($db,"SELECT MAX(position) as maxPosition FROM favoriteitem WHERE favorite_id = '" .$favorite_id . "'");
	$favoriteitem = mysqli_fetch_assoc($query);
	$maxPosition = (int) $favoriteitem['maxPosition'];
	$maxPosition++;
	mysqli_query($db,"INSERT INTO favoriteitem (track_id, stream_url, position, favorite_id) VALUES(
	'" . $track_id . "',
	'',
	'" . $maxPosition . "',
	'" . $favorite_id . "'
	)");
	$data['affRows'] = mysqli_affected_rows($db);
	$data['favorite_id'] = $favorite_id;
	//if (mysqli_affected_rows($db) > 0) $data['action'] = "add";
};

function importFavorite($favorite_id, $host, $port, $mode) {
	global $cfg, $db, $data;
	
	if ($favorite_id < 0) $isStreamPlaylist = 1;
	elseif ($favorite_id >= 0) $isStreamPlaylist = 0;
	
	$favorite_id = abs($favorite_id);
	
	

	if ($cfg['player_type'] == NJB_MPD) {
		$file = mpd('playlist',$host, $port);
		$file = implode('<seperation>', $file);
		$file = iconv(NJB_DEFAULT_CHARSET, 'UTF-8', $file);
		$file = explode('<seperation>', $file);
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Player not supported[/b]');

	
	$stream = 0;
	$streamCount = 0;
	for ($i = 0; $i < count($file); $i++) {
		if (preg_match('#^(ftp|http|https|mms|mmst|pnm|rtp|rtsp|sdp)://#', $file[$i])) {
				$stream = 1;
				$streamCount = $streamCount + 1 ;
		}
	}
	
	
	if (count($file) > 0) {
		if ($mode == 'import') {
			mysqli_query($db,'DELETE FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id);
			$offset = 0;
		}
		
		if ($mode == 'add') {
			$query = mysqli_query($db,'SELECT position FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id . ' ORDER BY position DESC');
			$track = mysqli_fetch_assoc($query);
			$offset = $track['position'];
		}	
		
		//if playlist contains only streams
		if ($streamCount == count($file) && $mode == 'import'){
			$isStreamPlaylist = 1;
			// Update favorite stream status
			mysqli_query($db,'UPDATE favorite
						SET stream			= "' . (int) $stream . '"
						WHERE favorite_id	= ' . (int) $favorite_id);
			
			// Don't allow stream_url and track_id in the same playlist!
			if ($stream)	mysqli_query($db,'DELETE FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id . ' AND track_id != ""');
			else			mysqli_query($db,'DELETE FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id . ' AND stream_url != ""');
		}
	}
	
			
	for ($i = 0; $i < count($file); $i++) {
		$query = mysqli_query($db,'SELECT track_id FROM track WHERE relative_file = "' . mysqli_real_escape_string($db,$file[$i]) . '"');
		$track = mysqli_fetch_assoc($query);
		$isStream = 0;
		if (preg_match('#^(ftp|http|https|mms|mmst|pnm|rtp|rtsp|sdp)://#', $file[$i])) {
			$isStream = 1;
		}
		
		if ($isStream == 0 && $track['track_id'] && $isStreamPlaylist == 0) {
			$position = $i + $offset + 1;
			mysqli_query($db,'INSERT INTO favoriteitem (track_id, position, favorite_id)
				VALUES ("' . mysqli_real_escape_string($db,$track['track_id']) . '",
				' . (int) $position . ',
				' . (int) $favorite_id . ')');
		}
	
		
		elseif ($isStream == 1 && $isStreamPlaylist == 1) {
			$position = $i + $offset + 1;
			mysqli_query($db,'INSERT INTO favoriteitem (stream_url, position, favorite_id)
				VALUES ("' . mysqli_real_escape_string($db,$file[$i]) . '",
				' . (int) $position . ',
				' . (int) $favorite_id . ')');
		}
		
		else { //trying to add file to stream playlist or stream to file playlist
			$data['not_compatible'] = true;
		}
	}
}
?>
	
