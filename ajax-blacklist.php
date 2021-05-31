<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2020 Artur Sierzant                            |
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

$data = array();
$data['action'] = "";
$track_id = $_GET['track_id'];
$track_mpd_url = $_GET['track_mpd_url'];
$action = $_GET['action'];
$data['group_type'] = $_GET['group_type']; //used in search.php
//$data['track_id_'] = var_dump($track_id);
//$data['track_mpd_url_'] = var_dump($track_mpd_url);

//avoid adding empty record when mpd is in unknown state
if (!$track_id && $track_mpd_url == 'null') {
	$data['not_compatible'] = true;
	echo safe_json_encode($data);
	return;
}

$addTidalPrefix = false;
$addYouTubePrefix = false;
$addHraPrefix = false;
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
  elseif (isHra($track_id)){
		$hra_track_id = getHraId($track_id);
		//create fake url to get track_id from it later in action == remove
		$track_mpd_url = "http://test.it/test.php?track_id=" . $hra_track_id;
		if ($action == 'add') {
			$track_mpd_url = createStreamUrlMpd($track_id);
		}
		$track_id = '';
		$addHraPrefix = true;
	}
	elseif (isYouTube($track_id)){
		$yt_track_id = getYouTubeId($track_id);
		//create fake url to get track_id from it later in action == remove
		$track_mpd_url = "http://test.it/test.php?track_id=" . $yt_track_id;
		if ($action == 'add') {
			$track_mpd_url = createStreamUrlMpd($track_id);
		}
		$track_id = '';
		$addYouTubePrefix = true;
	}
	else {
		//check if track_id is from local files indexed by DB
		$query = mysqli_query($db,"SELECT track_id FROM track WHERE track_id = '" .$track_id . "'");
		if (mysqli_num_rows($query) == 0) {
			$track_id = '';
		}
		else {
			$track_mpd_url = '';
		}
	}
}

if ($action == 'add') {
	//checking if track already in fav because of strange behavior in Android Chrome: multiple adding tracks to fav 
	if ($track_id){
		$query = mysqli_query($db,"SELECT position FROM favoriteitem WHERE 
		track_id='" . mysqli_real_escape_string($db, $track_id) . "'
		AND favorite_id = '" . mysqli_real_escape_string($db, $cfg['blacklist_id']) . "'");
	}
	if ($track_mpd_url){
		/* $query = mysqli_query($db,"SELECT position FROM favoriteitem WHERE 
		stream_url='" . mysqli_real_escape_string($db, $track_mpd_url) . "'
		AND favorite_id = '" . mysqli_real_escape_string($db, $cfg['blacklist_id']) . "'"); */
    
    $track_id_url = getTrackIdFromUrl($track_mpd_url);
		if ($track_id_url) {
      $query = mysqli_query($db,"SELECT position FROM favoriteitem WHERE 
      stream_url LIKE '%track_id=" . mysqli_real_escape_string($db, $track_id_url) . "%' AND favorite_id = '" . mysqli_real_escape_string($db, $cfg['blacklist_id']) . "'");
    }
	}
	if (mysqli_num_rows($query) == 0) {
		$query = mysqli_query($db,"SELECT MAX(position) as maxPosition FROM favoriteitem WHERE favorite_id = '" .$cfg['blacklist_id'] . "'");
		$favoriteitem = mysqli_fetch_assoc($query);
		$maxPosition = (int) $favoriteitem['maxPosition'];
		$maxPosition++;
		mysqli_query($db,"INSERT INTO favoriteitem (track_id, stream_url, position, favorite_id) VALUES(
		'" . mysqli_real_escape_string($db, $track_id) . "',
		'" . mysqli_real_escape_string($db, $track_mpd_url) . "',
		'" . $maxPosition . "',
		'" . mysqli_real_escape_string($db, $cfg['blacklist_id']) . "'
		)");
		if (mysqli_affected_rows($db) > 0) $data['action'] = "add";
	}
}

elseif ($action == 'remove') {
	if ($track_id){
		mysqli_query($db,"DELETE FROM favoriteitem WHERE track_id='" . mysqli_real_escape_string($db, $track_id) . "' and favorite_id='" . mysqli_real_escape_string($db, $cfg['blacklist_id']) . "'");
	}
	else{
		$track_id_url = getTrackIdFromUrl($track_mpd_url);
		if ($track_id_url) {
			mysqli_query($db,"DELETE FROM favoriteitem WHERE stream_url LIKE '%track_id=" . mysqli_real_escape_string($db, $track_id_url) . "%' and favorite_id='" . mysqli_real_escape_string($db, $cfg['blacklist_id']) . "'");
		}
		else {
			mysqli_query($db,"DELETE FROM favoriteitem WHERE stream_url = '" . mysqli_real_escape_string($db, $track_mpd_url) . "' and favorite_id='" . mysqli_real_escape_string($db, $cfg['blacklist_id']) . "'");
		}
	}
	if (mysqli_affected_rows($db) > 0) $data['action'] = "remove";
}

if (!$track_id) {
	$track_id = getTrackIdFromUrl($track_mpd_url);
	if ($addTidalPrefix) $track_id = "tidal_" . $track_id;
  if ($addHraPrefix) $track_id = "hra_" . $track_id;
	if ($addYouTubePrefix) $track_id = "youtube_" . $track_id;
}
$data['track_id'] = $track_id;

$data['favorite_type'] = updateFavoriteStreamStatus($cfg['blacklist_id']);

echo safe_json_encode($data);	
?>
	
