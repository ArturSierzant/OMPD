<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2021 Artur Sierzant                            |
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
$saveTrackMpdUrl = getTrackMpdUrl($_GET['track_mpd_url']);
//add to/save as from track submenu for streams (Tidal/YT)
if (!$saveTrackMpdUrl){
  $saveTrackMpdUrl = createStreamUrlMpd($saveTrackId);
  if (!$saveTrackMpdUrl){
    $data['not_compatible'] = true;
    echo safe_json_encode($data);
    return;
  }
}
//$saveTrackMpdUrl = str_replace(NJB_HOME_URL, '_NJB_HOME_URL_',$saveTrackMpdUrl);
//$data['saveTrackMpdUrl'] = $saveTrackMpdUrl;
$saveTrack = $_GET['saveTrack'];

//avoid adding empty record when mpd is in unknown state
if ($saveTrack == 'true' && !$saveTrackMpdUrl && !$saveTrackId) {
	$data['not_compatible'] = true;
	echo safe_json_encode($data);
	return;
}

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
			importTrack($favorite_id, $saveTrackId, $saveTrackMpdUrl);
		else
			importFavorite($favorite_id, $host, $port, 'import');
		$data['action_status'] = true;
		$data['select_options']= listOfFavorites(true,true,$saveTrackId);
		
	}	
}
elseif ($action == 'AddTo') {
	if ($saveAs) {
		authenticate('access_admin');
		if ($saveTrack == 'true') {
			if ($saveAs > 0) {//add track only if playlist is file playlist
				importTrack($saveAs, $saveTrackId, $saveTrackMpdUrl);
				$data['select_options']= listOfFavorites(true,true,$saveTrackId);
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


function importTrack($favorite_id, $track_id, $track_mpd_url) {
	global $cfg, $db, $data;
	$track_id = $track_id ? $track_id :  '';
	$track_mpd_url = $track_mpd_url ? $track_mpd_url :  '';
	if ($track_id) {
		//from Tidal album view or search results
		if (isTidal($track_id)){
			$tidal_track_id = getTidalId($track_id);
			$quertT = mysqli_query($db,"SELECT track_id FROM tidal_track WHERE track_id = '" . $tidal_track_id . "'");
			if (mysqli_affected_rows($db) == 0) {
				//track not in DB, e.g. result of search - get album and add it to DB
				$album_id = getTrackAlbumFromTidal($tidal_track_id);
				$get_album_tracks = getTracksFromTidalAlbum($album_id);
			}
			$track_mpd_url = createStreamUrlMpd($track_id);
			$track_id = '';
			$addTidalPrefix = true;
		}
		else {
			//check if track_id is from local files indexed by DB
			$query = mysqli_query($db,"SELECT track_id FROM track WHERE track_id = '" .$track_id . "'");
			if (mysqli_num_rows($query) == 0) {
				//if not then use $track_mpd_url as stream source
				$track_id = '';
			}
			else {
				$track_mpd_url = '';
			}
		}
	}

  $track_mpd_url = str_replace(NJB_HOME_URL, '_NJB_HOME_URL_',$track_mpd_url);

	$query = mysqli_query($db,"SELECT MAX(position) as maxPosition FROM favoriteitem WHERE favorite_id = '" .$favorite_id . "'");
	$favoriteitem = mysqli_fetch_assoc($query);
	$maxPosition = (int) $favoriteitem['maxPosition'];
	$maxPosition++;
	mysqli_query($db,"INSERT INTO favoriteitem (track_id, stream_url, position, favorite_id) VALUES(
	'" . $track_id . "',
	'" . $track_mpd_url . "',
	'" . $maxPosition . "',
	'" . $favorite_id . "'
	)");
	$data['affRows'] = mysqli_affected_rows($db);
	$data['favorite_id'] = $favorite_id;

	// Update favorite stream status
	updateFavoriteStreamStatus($favorite_id);
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
		if (preg_match('#^(tidal|ftp|http|https|mms|mmst|pnm|rtp|rtsp|sdp)://#', $file[$i])) {
				//$stream = 1;
				$streamCount = $streamCount + 1 ;
		}
	}
	
	
	if (count($file) > 0) {
		/* $query = mysqli_query($db,'SELECT stream FROM favorite WHERE favorite_id = ' . (int) $favorite_id);
		$favType = mysqli_fetch_assoc($query);
		$isFavStream = $favType['stream']; */
		
		if ($mode == 'import') {
			mysqli_query($db,'DELETE FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id);
			$offset = 0;
		}
		
		if ($mode == 'add') {
			$query = mysqli_query($db,'SELECT position FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id . ' ORDER BY position DESC');
			$track = mysqli_fetch_assoc($query);
			$offset = $track['position'];
		}	
	}
			
	for ($i = 0; $i < count($file); $i++) {
		$query = mysqli_query($db,'SELECT track_id FROM track WHERE relative_file = "' . mysqli_real_escape_string($db,$file[$i]) . '"');
		$track = mysqli_fetch_assoc($query);
		$isStream = 0;
		if (preg_match('#^(tidal|ftp|http|https|mms|mmst|pnm|rtp|rtsp|sdp)://#', $file[$i])) {
			$isStream = 1;
		}
		
		if ($isStream == 0 && $track['track_id']) {
			$position = $i + $offset + 1;
			/* mysqli_query($db,'INSERT INTO favoriteitem (track_id, position, favorite_id)
				VALUES ("' . mysqli_real_escape_string($db,$track['track_id']) . '",
				' . (int) $position . ',
				' . (int) $favorite_id . ')'); */
        mysqli_query($db,'INSERT INTO favoriteitem (stream_url, track_id, position, favorite_id)
        VALUES ("", "' . mysqli_real_escape_string($db,$track['track_id']) . '",
        ' . (int) $position . ',
        ' . (int) $favorite_id . ')');
		}
	
		
		if ($isStream == 1) {
			$position = $i + $offset + 1;
			mysqli_query($db,'INSERT INTO favoriteitem (stream_url, position, favorite_id)
				VALUES ("' . mysqli_real_escape_string($db,getTrackMpdUrl($file[$i])) . '",
				' . (int) $position . ',
				' . (int) $favorite_id . ')');
		}
		
		/* else { //trying to add file to stream playlist or stream to file playlist
			$data['not_compatible'] = true;
		} */
	}
	updateFavoriteStreamStatus($favorite_id);
}
?>
	
