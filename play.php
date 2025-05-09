<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015 Artur Sierzant                                 |
//  | http://www.ompd.pl                                                     |
//  |                                                                        |
//  |                                                                        |
//  | netjukebox, Copyright © 2001-2012 Willem Bartels                       |
//  |                                                                        |
//  | http://www.netjukebox.nl                                               |
//  | http://forum.netjukebox.nl                                             |
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




//  +------------------------------------------------------------------------+
//  | play.php                                                               |
//  +------------------------------------------------------------------------+

//$tidalAutoRefresh is set to prevent token refreshing when miniplayer is used
$tidalAutoRefresh = false;

require_once('include/initialize.inc.php');
require_once('include/library.inc.php');
/* require_once('include/play.inc.php'); */

header('Content-type: application/json');

$action	= get('action');

if		($action == 'play')				play();
elseif	($action == 'playStreamDirect')	playStreamDirect();
elseif	($action == 'addStreamDirect')	addStreamDirect();
elseif	($action == 'pause')			pause();
elseif	($action == 'stop')				stop();
elseif	($action == 'prev')				prev_();
elseif	($action == 'beginOfTrack')		beginOfTrack();
elseif	($action == 'next')				next_();
elseif	($action == 'playMPDplaylist')	playMPDplaylist();
elseif	($action == 'addMPDplaylist')	addMPDplaylist();
elseif	($action == 'playTidalList')	playTidalList();
elseif	($action == 'addTidalList')	addTidalList();
elseif	($action == 'playSelect')		playSelect();
elseif	($action == 'addSelect')		addSelect();
elseif	($action == 'addSelectUrl')		addSelectUrl();
elseif	($action == 'addMultitrack')	addMultitrack();
elseif	($action == 'insertSelect')		insertSelect();
elseif	($action == 'seekImageMap')		seekImageMap();
elseif	($action == 'playIndex')		playIndex();
elseif	($action == 'deleteIndex')		deleteIndex();
elseif	($action == 'deleteBelowIndex')	deleteBelowIndex();
elseif	($action == 'deleteAlbum')	deleteAlbum();
elseif	($action == 'deleteIndexAjax')	deleteIndexAjax();
elseif	($action == 'deletePlayed')		deletePlayed();
elseif	($action == 'crop')				crop();
elseif	($action == 'consume')			consume();
elseif	($action == 'moveTrack')		moveTrack();
elseif	($action == 'volumeImageMap')	volumeImageMap();
elseif	($action == 'toggleMute')		toggleMute();
elseif	($action == 'toggleShuffle')	toggleShuffle();
elseif	($action == 'toggleRepeat') 	toggleRepeat();
elseif	($action == 'loopGain')			loopGain();
elseif	($action == 'playlistStatus')	playlistStatus();
elseif	($action == 'playlistTrack')	playlistTrack();
elseif	($action == 'updateAddPlay')	updateAddPlay();
elseif	($action == 'test')				addTracks();
else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');
exit();




//  +------------------------------------------------------------------------+
//  | Play                                                                   |
//  +------------------------------------------------------------------------+
function play() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$data = array();
	
	$status = mpd('status');
	if ($status['state'] == 'stop') {
		$current_song = $status['song'];
		mpd('play ' . $current_song);
	}
	else {
		mpd('play');
	}
	
	if (get('menu') == 'playlist') {
		$status = mpd('status');
		if ($status['state'] == 'stop')		$data['state'] = '0'; // stop
		if ($status['state'] == 'play')		$data['state'] = '1'; // play
		if ($status['state'] == 'pause')	$data['state'] = '3'; // pause
		$data['idx'] = $status['song'];
		$data['duration'] = $status['duration'];
		echo safe_json_encode($data);
	}
}




//  +------------------------------------------------------------------------+
//  | Pause                                                                  |
//  +------------------------------------------------------------------------+
function pause() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$data = array();
	
	mpd('pause');
	if (get('menu') == 'playlist') {
		$status = mpd('status');
		if ($status['state'] == 'stop')		$data['state'] = '0'; // stop
		if ($status['state'] == 'play')		$data['state'] = '1'; // play
		if ($status['state'] == 'pause')	$data['state'] = '3'; // pause
		$data['idx'] = $status['song'];
    $data['duration'] = $status['duration'];
		echo safe_json_encode($data);
	}
}




//  +------------------------------------------------------------------------+
//  | Stop                                                                   |
//  +------------------------------------------------------------------------+
function stop() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$data = array();
	
	mpd('stop');
	if (get('menu') == 'playlist') {
		$status = mpd('status');
		if ($status['state'] == 'stop')		$data['state'] = '0'; // stop
		if ($status['state'] == 'play')		$data['state'] = '1'; // play
		if ($status['state'] == 'pause')	$data['state'] = '3'; // pause
		$data['idx'] = $status['song'];
    $data['duration'] = $status['duration'];
		echo safe_json_encode($data);
	}
}




//  +------------------------------------------------------------------------+
//  | Prev                                                                   |
//  +------------------------------------------------------------------------+
function prev_() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$data = array();
	$status = mpd('status');
	/* if ($status['state'] == 'stop') {
		mpd('play');
	}
	mpd('previous'); */
  if ($status['song'] > 0) {
    if ($status['state'] == 'stop') {
      mpd('play');
    }
    mpd('previous');
    //mpd('play ' . $status['song'] - 1);
  }
	if (get('menu') == 'playlist') {
		$status = mpd('status');
		if ($status['state'] == 'stop')		$data['state'] = '0'; // stop
		if ($status['state'] == 'play')		$data['state'] = '1'; // play
		if ($status['state'] == 'pause')	$data['state'] = '3'; // pause
		$data['idx'] = $status['song'];
    $data['duration'] = $status['duration'];
		echo safe_json_encode($data);
	}
}



//  +------------------------------------------------------------------------+
//  |beginOfTrack                                                            |
//  +------------------------------------------------------------------------+
function beginOfTrack() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	$currentsong	= mpd('currentsong');
	$pos = $currentsong['Pos'];
	//mpd('seekcur 0');
	mpd('seek ' . $pos . ' 0');
}




//  +------------------------------------------------------------------------+
//  | Next                                                                   |
//  +------------------------------------------------------------------------+
function next_() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$data = array();
	$status = mpd('status');
	/*
	if ($status['state'] == 'stop') {
		mpd('play');
	}
	mpd('next'); */
  
  if ($status['song'] < $status['playlistlength'] - 1) {
    if ($status['state'] == 'stop') {
      mpd('play');
    }
    mpd('next');
    //mpd('play ' . $status['song'] + 1);
  }
  
	if (get('menu') == 'playlist') {
		$status = mpd('status');
		if ($status['state'] == 'stop')		$data['state'] = '0'; // stop
		if ($status['state'] == 'play')		$data['state'] = '1'; // play
		if ($status['state'] == 'pause')	$data['state'] = '3'; // pause
		$data['idx'] = $status['song'];
    $data['duration'] = $status['duration'];
		echo safe_json_encode($data);
	}
}




//  +------------------------------------------------------------------------+
//  | Play select                                                            |
//  +------------------------------------------------------------------------+
function playSelect() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	$album_id = get('album_id');
	if(!$album_id) $album_id = get('id');
	$track_id = get('track_id');
	$favorite_id	= get('favorite_id');
	$random	= get('random');
	$disc	= get('disc');
	$data = array();
	$playResult = 'play_error';
	
	if ($cfg['player_type'] == NJB_HTTPQ) {
		httpq('stop');
		if ($cfg['play_queue'] == false)
			httpq('delete');
		addTracks('play');
	}
	elseif ($cfg['player_type'] == NJB_VLC) {
		vlc('pl_empty');
		addTracks('play');
	}
	elseif ($cfg['player_type'] == NJB_MPD) {
		mpd('stop');
		if ($cfg['play_queue'] == false)
			mpd('clear');
		$playResult = addTracks('play');
	}
	if ($playResult == 'add_OK') {
		$data['playResult'] = 'play_OK';
	}
	else {
		$data['playResult'] = 'play_error';
	}
	
	$data['album_id'] = $album_id;
	$data['track_id'] = $track_id;
	$data['favorite_id'] = $favorite_id;
	$data['random'] = $random;
	$data['disc'] = $disc;
	ob_start();
	echo safe_json_encode($data);
	header('Connection: close');
	header('Content-Length: ' . ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();
	if ($album_id) {	
		backgroundQueries();
	}
}




//  +------------------------------------------------------------------------+
//  | Play MPD playlist                                                      |
//  +------------------------------------------------------------------------+
function playMPDplaylist() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	$favorite_id	= get('favorite_id');
	$data = array();
	$playResult = 'play_error';
	
	mpd('stop');
	if ($cfg['play_queue'] == false)
		mpd('clear');
	$playResult = mpd('load "' . $favorite_id . '"');
	if ($playResult == 'add_OK') {
		$playResult = 'play_OK';
	}
	mpd('play');
	if ($playResult == 'ACK_ERROR_NO_EXIST') {
		$playResult = 'play_error';
	}
	$data['playResult'] = $playResult;
	$data['favorite_id'] = $favorite_id;
	ob_start();
	echo safe_json_encode($data);
	header('Connection: close');
	header('Content-Length: ' . ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();
}




//  +------------------------------------------------------------------------+
//  | Add MPD playlist                                                       |
//  +------------------------------------------------------------------------+
function addMPDplaylist() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	$favorite_id	= get('favorite_id');
	$data = array();
	$playResult = 'play_error';
	
	$playResult = mpd('load "' . $favorite_id . '"');
	
	if ($playResult == 'ACK_ERROR_NO_EXIST') {
		$playResult = 'add_error';
	}
	$data['addResult'] = $playResult;
	$data['favorite_id'] = $favorite_id;
	ob_start();
	echo safe_json_encode($data);
	header('Connection: close');
	header('Content-Length: ' . ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();
}




//  +------------------------------------------------------------------------+
//  | Play Tidal playlist                                                    |
//  +------------------------------------------------------------------------+
function playTidalList() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	$tidal_id	= getTidalId(get('tidal_id'));
	$type	= !empty(get('type')) ? get('type') : 'playlist';
	$data = array();
	$playResult = 'play_error';
	
	mpd('stop');
	if ($cfg['play_queue'] == false) {
		mpd('clear');
	}
	
	$playResult = loadTidalPlaylist($tidal_id, $type);
	
	mpd('play');
	if (strpos((string)$playResult,'ACK') !== false) {
		$playResult = 'play_error';
	}
	else {
		$playResult = 'play_OK';
	}
	$data['playResult'] = $playResult;
	//$data['tidal_id'] = $tidal_id;
  $data['album_id'] = 'tidal_' . $tidal_id;
  /* if (strpos($tidal_id,'tidal_') !== false){
    $data['album_id'] = $tidal_id;
  } 
  else {
    $data['album_id'] = 'tidal_' . $tidal_id;
  } */
	ob_start();
	echo safe_json_encode($data);
	header('Connection: close');
	header('Content-Length: ' . ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();
}




//  +------------------------------------------------------------------------+
//  | Add Tidal playlist                                                     |
//  +------------------------------------------------------------------------+

function addTidalList() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	$tidal_id	= getTidalId(get('tidal_id'));
  $type	= !empty(get('type')) ? get('type') : 'playlist';
	$data = array();
	$playResult = 'add_error';
	
	$playResult = loadTidalPlaylist($tidal_id, $type);
	
	if (strpos((string)$playResult,'ACK') !== false) {
		$playResult = 'add_error';
	}
	else {
		$playResult = 'add_OK';
	}
	$data['addResult'] = $playResult;
	//$data['tidal_id'] = $tidal_id;
  $data['album_id'] = 'tidal_' . $tidal_id;
	/* if (strpos($tidal_id,'tidal_') !== false){
    $data['album_id'] = $tidal_id;
  } 
  else {
    $data['album_id'] = 'tidal_' . $tidal_id;
  } */
  ob_start();
	echo safe_json_encode($data);
	header('Connection: close');
	header('Content-Length: ' . ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();
}




//  +------------------------------------------------------------------------+
//  | Load Tidal playlist                                                    |
//  +------------------------------------------------------------------------+

function loadTidalPlaylist($tidal_id, $type = 'playlist') {
	global $cfg, $db, $t;

  $tidal_id = getTidalId($tidal_id);
	$conn = $t->connect();

	if ($conn === true){
    if ($type == 'playlist') {
      $trackList = $t->getPlaylistTracks($tidal_id);
		}
    if ($type == 'mixlist') {
      $trackList = $t->getMixlistTracks($tidal_id);
		}
		for ($i = 0; $i < $trackList['totalNumberOfItems']; $i++) {
        $track = getTidalTrack($trackList, $i);
				$playResult = mpdAddTidalTrack('tidal_' . $track['id']);
				cliLog("loadTidalPlaylist id: " . $id);
				cliLog("loadTidalPlaylist: " . $playResult);
		}
	}
  
  if (strpos((string)$playResult,'ACK') === false) {
    updateCounter('tidal_' . $type . '_' . $tidal_id, NJB_COUNTER_PLAY);
	}
	return $playResult;
}



//  +------------------------------------------------------------------------+
//  | Add select                                                             |
//  +------------------------------------------------------------------------+
function addSelect() {
	global $cfg, $db;
	authenticate('access_add');
	require_once('include/play.inc.php');
	$album_id = get('album_id');
	$track_id = get('track_id');
	$disc = get('disc');
	//$file_id = get('file_id');
	$favorite_id = get('favorite_id');
	$random	= get('random');
	$data = array();
	$addResult = 'add_error';
	
	if ($cfg['player_type'] == NJB_HTTPQ) {
		if ($cfg['add_autoplay'] && httpq('getlistlength') == 0)	addTracks('play');
		else addTracks('add');
	}
	elseif ($cfg['player_type'] == NJB_VLC) {
		addTracks('add');
	}
	elseif ($cfg['player_type'] == NJB_MPD) {
		$status = mpd('status');
		if ($cfg['add_autoplay'] && $status['playlistlength'] == 0)	
			$addResult = addTracks('play');
		else
			$addResult = addTracks('add');
	}
	$data['addResult'] = $addResult; 
	if ($random) 
		$data['album_id'] = 'random';
	else
		$data['album_id'] = $album_id;
	$data['track_id'] = $track_id;
	//$data['file_id'] = $file_id;
	$data['disc'] = $disc;
	$data['favorite_id'] = $favorite_id;
	$data['random'] = $random;
	ob_start();
	echo safe_json_encode($data);
	header('Connection: close');
	header('Content-Length: ' . ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();
	if ($album_id) {	
		backgroundQueries();
	}
	//return 'add_OK';
}




//  +------------------------------------------------------------------------+
//  | Add multitrack                                                         |
//  +------------------------------------------------------------------------+
function addMultitrack() {
	global $cfg, $db;
	authenticate('access_add');
	require_once('include/play.inc.php');
	
	$track_ids = explode(';', get('track_ids'));
	$addType = get('addType');
	$data = array();
	$addResult = 'add_error';
	
	
	$status = mpd('status');
	if ($cfg['add_autoplay'] && $status['playlistlength'] == 0)	{
		$addResult = addTracks('play', '', '', $track_ids[0]);
		if (count($track_ids) > 1) {
			foreach($track_ids as $key => $value) {
				if ($key > 0) {
					$addResult = addTracks('add', '', '', $value);	
				}
			}
		}
	}
	else {
		foreach($track_ids as $value) {
			$addResult = addTracks('add', '', '', $value);	
		}
	}
	
	$data['addResult'] = $addResult; 
	$data['addType'] = $addType; 
	
	ob_start();
	echo safe_json_encode($data);
	header('Connection: close');
	header('Content-Length: ' . ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();
}

//  +------------------------------------------------------------------------+
//  | Add select url                                                         |
//  +------------------------------------------------------------------------+
function addSelectUrl() {
	global $cfg, $db;
	authenticate('access_add');
	require_once('include/play.inc.php');
	
	$url = get('url');
	$data = array();
	$tidal_tracks = array();
	$addResult = 'add_error';
	
	if ($url!="") {
		if (isTidal($url) && $cfg['use_tidal']) {
			$id = getTidalId($url);
			//TIDAL track
			if (strpos($url, MPD_TIDAL_URL) !== false || strpos($url, TIDAL_APP_TRACK_URL) !== false || strpos($url, TIDAL_TRACK_URL) !== false || strpos($url, TIDAL_TRACK_STREAM_URL) !== false) {
				//check if album is in OMPD DB:
				$query = mysqli_query($db,'SELECT track_id FROM tidal_track WHERE 	track_id = "' . mysqli_real_escape_string($db,$id) . '"');
				if (mysqli_num_rows($query) == 0) {
					$album_id = getTrackAlbumFromTidal($id);
					if (!$album_id) {
						return 'add_error';
					}
					$tidal_tracks = getTracksFromTidalAlbum($album_id);
          $tidal_tracks = json_decode($tidal_tracks, true);
				}
				$tidal_tracks['track_id'] = $id;
				$mpdCommand = mpdAddTidalTrack('tidal_' . $id);
				
				if ($mpdCommand == 'ACK_ERROR_UNKNOWN' || $mpdCommand == 'ACK_ERROR_NO_EXIST' || $mpdCommand == 'TIDAL_CONNECT_ERROR') {
					return 'add_error';
				}
				$data['track_id'] = 'tidal_' . $id;
			}
			//TIDAL album
			elseif (strpos($url,TIDAL_ALBUM_URL) !== false || strpos($url,TIDAL_ALBUM_URL_2) !== false || strpos($url,TIDAL_APP_ALBUM_URL) !== false) {
				$tidal_tracks = getTracksFromTidalAlbum($id);
				$tidal_tracks = json_decode($tidal_tracks, true);
				
				foreach ($tidal_tracks as $tidal_track) {
					$mpdCommand = mpdAddTidalTrack('tidal_' . $tidal_track['id']);
					if ($mpdCommand == 'ACK_ERROR_UNKNOWN' || $mpdCommand == 'ACK_ERROR_NO_EXIST' || $mpdCommand == 'TIDAL_CONNECT_ERROR') {
						return 'add_error';
					}
				}
				updateCounter('tidal_' . $id, NJB_COUNTER_PLAY);
			}
			if ($first && $mode == 'play') {
				mpd('play ' . $index);
			}
			$addResult = 'add_OK';
		}
		elseif (isYoutube($url)){
			if ($ytUrl = getYouTubeMPDUrl($url)) {
				mpd('add "' . mpdEscapeChar($ytUrl) . '"');
				$addResult = 'add_OK';
			}
		}
		else {
			$status = mpd('status');
			if ($cfg['add_autoplay'] && $status['playlistlength'] == 0)
				$addResult = addUrl('play');
			else
				$addResult = addUrl('add');
		}
	}
	$data['addResult'] = $addResult; 
	ob_start();
	echo safe_json_encode($data);
	header('Connection: close');
	header('Content-Length: ' . ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();
}


//  +------------------------------------------------------------------------+
//  | Insert select                                                          |
//  +------------------------------------------------------------------------+
function insertSelect() {
	global $cfg, $db;
	authenticate('access_add');
	require_once('include/play.inc.php');
	$album_id = get('album_id');
	$track_id = get('track_id');
	$position_id = get('position_id');
	$data = array();
	$addResult = 'insert_error';
	
	if ($cfg['player_type'] == NJB_HTTPQ) {
	}
	elseif ($cfg['player_type'] == NJB_VLC) {	
	}
	elseif ($cfg['player_type'] == NJB_MPD) {
		$status = mpd('status');
    if ($status['state'] == 'stop') {
      $insPos = $status['playlistlength'];
    }
    else {
      $insPos = $status['song'] + 1;
    }
		$playAfterInsert= get('playAfterInsert');
		if ($status['playlistlength'] == 0)	
			$addResult = addTracks('play');
		else
			$addResult = addTracks('addid',$insPos, $playAfterInsert);		
	}
	if ($addResult == 'add_OK') {
		$addResult = 'insert_OK';
	}
	else {
		$addResult = 'insert_error';
	}
	if ($playAfterInsert == 'yes') { 
		$data['insertPlayResult'] = $addResult;
	}
	else {
		$data['insertResult'] = $addResult; 
	}
	$data['album_id'] = $album_id;
	$data['track_id'] = $track_id;
	$data['position_id'] = $position_id;
	
	ob_start();
	echo safe_json_encode($data);
	header('Connection: close');
	header('Content-Length: ' . ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();
	if ($album_id) {	
		backgroundQueries();
	}
	//return 'add_OK';
}


//  +------------------------------------------------------------------------+
//  | Add tracks                                                             |
//  +------------------------------------------------------------------------+
function addTracks($mode = 'play', $insPos = '', $playAfterInsert = '', $track_id = '') {
	global $cfg, $db;
	
	$track_id			= ($track_id == '' ? get('track_id') : $track_id);
	$album_id			= get('album_id');
	$disc					= get('disc');
	$filepath			= get('filepath');
	$dirpath			= get('dirpath');
	$fulldirpath	= get('fulldirpath');
	$favorite_id	= get('favorite_id');
	$random				= get('random');
	$insertType		= get('insertType');
	$md					  = get('md') !== null ? get('md') : '';
	//$md						= get('md');
	$isYoutube 		= false;
	$isHRA 		= false;
  $multidiscs_count = 1;
	
	if ($track_id) {
		if (isTidal($track_id)) {
      //check if track and album are in DB
      $query = mysqli_query($db,'SELECT track_id, album_id FROM tidal_track WHERE 	track_id = "' . mysqli_real_escape_string($db,getTidalId($track_id)) . '"');
      if (mysqli_num_rows($query) > 0) {
        //track is in DB, check if album is too
        $a = mysqli_fetch_assoc($query);
        $album_id = $a['album_id'];
				$query1 = mysqli_query($db,'SELECT album_id FROM tidal_album WHERE  album_id = "' . mysqli_real_escape_string($db, $album_id) . '"');
        if (mysqli_num_rows($query1) == 0) {
          //if not then add album to DB
          $tidal_tracks_tmp = getTracksFromTidalAlbum(getTidalId($album_id));
        }
      }
      else {
        //track from album not added to DB yet (e.g. result of search)
        if (!isset($album_id)) {
          $album_id = getTrackAlbumFromTidal(getTidalId($track_id));
        }
				$tidal_tracks_tmp = getTracksFromTidalAlbum(getTidalId($album_id));
      }

			if ($cfg['tidal_direct']) {
				$query = mysqli_query($db,'SELECT CONCAT("' . mysqli_real_escape_string($db,NJB_HOME_URL) . 'stream.php?action=streamTidal&track_id=", track_id) as relative_file, track_id, album_id FROM tidal_track WHERE 	track_id = "' . mysqli_real_escape_string($db,getTidalId($track_id)) . '"');
			}
			elseif ($cfg['upmpdcli_tidal']) {
				$query = mysqli_query($db,'SELECT CONCAT("' . mysqli_real_escape_string($db,$cfg['upmpdcli_tidal']) . '", track_id) as relative_file, track_id, album_id FROM tidal_track WHERE 	track_id = "' . mysqli_real_escape_string($db,getTidalId($track_id)) . '"');
			}
			else {
				$query = mysqli_query($db,'SELECT CONCAT("' . MPD_TIDAL_URL . '", track_id) as relative_file, track_id, album_id FROM tidal_track WHERE 	track_id = "' . mysqli_real_escape_string($db,getTidalId($track_id)) . '"');
			}
		}
		elseif (isHra($track_id)){
			$isHRA = true;
		}
		elseif (isYoutube($track_id)){
			$isYoutube = true;
		}
		else {
			$query = mysqli_query($db,'SELECT relative_file, track_id FROM track WHERE track_id = "' . mysqli_real_escape_string($db,$track_id) . '"');
		}
	}
	//only whole albums, not single tracks from e.g. search results
	elseif ($album_id && !isTidal($track_id) && !isHra($track_id)) {
		if (isTidal($album_id)) {
			$tidal_album_id = getTidalId($album_id);
      switch ($insertType) {
        case 'mixlist_list':
          if ($insertType == 'mixlist_list' && $insPos > 0) {
            $tidal_tracks = getTracksFromTidalAlbum($tidal_album_id, 'DESC', 'mixlist_list');
          }
          else {
            $tidal_tracks = getTracksFromTidalAlbum($tidal_album_id,'','mixlist_list');
          }
          $album_id = 'tidal_mixlist_' . $tidal_album_id;
          break;
        case 'playlist_list':
          if ($insertType == 'playlist_list' && $insPos > 0) {
            $tidal_tracks = getTracksFromTidalAlbum($tidal_album_id, 'DESC','playlist_list');
          }
          else {
            $tidal_tracks = getTracksFromTidalAlbum($tidal_album_id, '','playlist_list');
          }
          $album_id = 'tidal_playlist_' . $tidal_album_id;
          break;
        default:
          if ($insertType == 'album' && $insPos > 0) {
            $tidal_tracks = getTracksFromTidalAlbum($tidal_album_id, 'DESC');
          }
          else {
            $tidal_tracks = getTracksFromTidalAlbum($tidal_album_id);
          }
          break;
      }
			$mds_updateCounter = array();
			$mds_updateCounter[] = $album_id;
		}
		elseif (isHra($album_id)) {
			$hra_album_id = getHraId($album_id);
			if ($insertType == 'album' && $insPos > 0) {
				$hra_tracks = getTracksFromHraAlbum($hra_album_id, 'DESC');
			}
			else {
				$hra_tracks = getTracksFromHraAlbum($hra_album_id);
			}
			$mds_updateCounter = array();
			$mds_updateCounter[] = $album_id;
		} 
		else {
			$select_md = ''; 
			$md_indicator = '';
			$mds_updateCounter = array();
			if ($cfg['group_multidisc'] == true && $md == 'allDiscs') {
				$query_md = mysqli_query($db,'SELECT album, artist FROM album WHERE album_id = "' . $album_id . '"');
				$album = mysqli_fetch_assoc($query_md);
				$md_indicator = striposa($album['album'], $cfg['multidisk_indicator']);
				if ($md_indicator !== false) {
					$md_ind_pos = stripos($album['album'], $md_indicator);
					$md_title = substr($album['album'], 0,  $md_ind_pos);
					$query_md = mysqli_query($db, 'SELECT album, image_id, album_id 
					FROM album 
					WHERE album LIKE "' . mysqli_real_escape_string($db, $md_title) . '%" AND artist = "' . mysqli_real_escape_string($db, $album['artist']) . '"
					ORDER BY album');
					$mds = '';
					while ($album_md = mysqli_fetch_assoc($query_md)) {
						$mds = ($mds == '' ? '"' . $album_md['album_id'] . '"' : $mds . ', "' . $album_md['album_id'] . '"'); 
						$mds_updateCounter[] = $album_md['album_id'];
					};
					if ($mds != ''){
						$select_md_1 = ' WHERE track.album_id IN (' . $mds . ') ';
						$select_md_2 = ' track.album_id IN (' . $mds . ') AND ';
					}
				}
        cliLog ("select_md -> " . $select_md_1);
        cliLog ("select_md -> " . $select_md_2);
				//check if multidiscs have different values for 'disc' to keep right track order in playlist (prior update procedure always put '1' as 'disc', no matter what value of 'part_of_set'/'discnumber' tag was)
				$query = mysqli_query($db,'SELECT disc 
				FROM track LEFT JOIN album ON track.album_id = album.album_id 
				' . $select_md_1 . ' GROUP BY disc');
        if ($query) {
          $multidiscs_count = mysqli_num_rows($query);
				}
				$order_by = 'track.relative_file, track.disc, track.number';
				if ($multidiscs_count > 1) {
					$order_by = 'track.disc, track.number, track.relative_file';
				}
				
				$query_str = 'SELECT relative_file, track_id 
				FROM track LEFT JOIN album ON track.album_id = album.album_id 
				WHERE ' . $select_md_2 . ' track.track_id NOT IN 
				(SELECT track_id FROM favoriteitem WHERE favorite_id = "' . $cfg['blacklist_id'] . '") 
				ORDER BY ' . $order_by;
			}
			if ($cfg['group_multidisc'] == false || $md_indicator == '' || $md != 'allDiscs') {
				$part_of_set = '';
				if ($disc) {
					$part_of_set = ' AND disc = ' . $disc . ' ';
				}
				$query_str = 'SELECT relative_file, track_id 
				FROM track 
				WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '"'
				. $part_of_set . 
				' AND track_id NOT IN 
				(SELECT track_id FROM favoriteitem WHERE favorite_id = "' . $cfg['blacklist_id'] . '") ';
				$mds_updateCounter[] = $album_id;
				if ($insertType == 'album' && $insPos > 0) {
					$query_str = $query_str . ' ORDER BY disc DESC, number DESC, relative_file DESC';
				}
				else {
					$query_str = $query_str . ' ORDER BY disc, number, relative_file';
				}
			}

			$query = mysqli_query($db,$query_str);
		}
	}
	elseif ($favorite_id) {
		/* $query = mysqli_query($db,'SELECT stream FROM favorite WHERE favorite_id = ' . (int) $favorite_id . ' AND stream = 1');
		if (mysqli_fetch_row($query)) {	
			playStream($favorite_id);
		}
		
		$query	= mysqli_query($db,'SELECT relative_file, track.track_id
			FROM track, favoriteitem
			WHERE favoriteitem.track_id = track.track_id 
			AND favorite_id = "' . mysqli_real_escape_string($db,$favorite_id) . '"
			ORDER BY position');
			*/
		$query	= mysqli_query($db,'SELECT relative_file, track.track_id, stream_url, position
			FROM track RIGHT JOIN favoriteitem ON favoriteitem.track_id = track.track_id
			WHERE favorite_id = "' . mysqli_real_escape_string($db,$favorite_id) . '"
			ORDER BY position');
	}
	elseif ($random == 'database') {
		$query = mysqli_query($db,'SELECT relative_file
			FROM track, random
			WHERE random.sid	= "' . mysqli_real_escape_string($db,cookie('netjukebox_sid')) . '" AND
			random.track_id		= track.track_id
			ORDER BY position');
	}
	elseif ($random == 'new') {
		$blacklist = explode(',', $cfg['random_blacklist']);
		$blacklist = '"' . implode('","', $blacklist) . '"';
		$query = mysqli_query($db,'SELECT relative_file, track_id
			FROM track, album
			WHERE (genre_id = "" OR genre_id NOT IN (' . $blacklist . ')) AND
			audio_dataformat != "" AND
			video_dataformat = "" AND
			track.album_id = album.album_id
			ORDER BY RAND()
			LIMIT 30');
	}
	elseif ($filepath || $dirpath || $fulldirpath) {}
	else {
		message(__FILE__, __LINE__, 'error', '[b]Unsupported query string[/b][br]' . $_SERVER['QUERY_STRING']);
	}
	
	if ($cfg['play_queue'] == false)
		$index = 0;
	elseif ($cfg['player_type'] == NJB_HTTPQ) {
		$index = httpq('getlistlength');
	}
	elseif ($cfg['player_type'] == NJB_VLC) {
		$index = 0;
	}
	elseif ($cfg['player_type'] == NJB_MPD) {
		$status = mpd('status');
		$index = $status['playlistlength'];
		$insPos = $status['song'];
	}
	
	$n = $index;
	$first = true;
	//only whole albums, not single tracks from e.g. search results
	if (isTidal($album_id) && !isTidal($track_id)) {
		if ($tidal_tracks) {
			$tidal_tracks = json_decode($tidal_tracks, true);
			foreach ($tidal_tracks as $tidal_track) {
				if (!isInFavorite('tidal_' . $tidal_track['id'],$cfg['blacklist_id'])){
					$mpdCommand = mpdAddTidalTrack('tidal_' . $tidal_track['id'], $insPos);
					if ($mpdCommand == 'ACK_ERROR_UNKNOWN' || $mpdCommand == 'ACK_ERROR_NO_EXIST' || $mpdCommand == 'TIDAL_CONNECT_ERROR') {
						return 'add_error';
					}
					$n++;
				}
			}
			if ($playAfterInsert) {mpd('play ' . $insPos);}
			if ($first && $mode == 'play') {
				mpd('play ' . $index);
			}
			$first = false;
		}
		else {
			return 'add_error';
		}
	}
	elseif (isHra($album_id) && !isHra($track_id)) {
		if ($hra_tracks) {
			$hra_tracks = json_decode($hra_tracks, true);
			$productionYear = $hra_tracks["productionYear"];
			$cover = $hra_tracks["cover"]["master"]["file_url"];
			$album_id = $hra_tracks["id"];
			$album_title = $hra_tracks["title"];
			foreach ($hra_tracks["tracks"] as $hra_track) {
				if (!isInFavorite('hra_' . $hra_track['playlistAdd'],$cfg['blacklist_id'])){
					$hra_track["productionYear"] = $productionYear;
					$hra_track["cover"] = $cover;
					$hra_track["track_id"] = $hra_track['playlistAdd'];
					$hra_track["album_id"] = $album_id;
					$hra_track["album_title"] = $album_title;
					$url = createHRAMPDUrl($hra_track);
					//$url = getHRAMPDUrl(getHraId($hra_track['playlistAdd']));
					if (!$url) {
						return 'add_error';
					}
					$mpdCommand = mpd('addid "' . mpdEscapeChar($url) . '" ' . $insPos);
					if (is_string($mpdCommand) && strpos($mpdCommand,'ERROR') !== false) {
						return 'add_error';
					}
					
					/* $mpdCommand = mpdAddHraTrack('hra_' . $hra_track['playlistAdd'], $insPos);
					if ($mpdCommand == 'ACK_ERROR_UNKNOWN' || $mpdCommand == 'ACK_ERROR_NO_EXIST' || $mpdCommand == 'TIDAL_CONNECT_ERROR') {
						return 'add_error';
					} */
					$n++;
				}
			}
			if ($playAfterInsert) {mpd('play ' . $insPos);}
			if ($first && $mode == 'play') {
				mpd('play ' . $index);
			}
			$first = false;
		}
		else {
			return 'add_error';
		}
	} 
	elseif($isHRA){
		$url = getHRAMPDUrl(getHraId($track_id));
		if (!$url) {
			return 'add_error';
		}
		$mpdCommand = mpd('addid "' . mpdEscapeChar($url) . '" ' . $insPos);
		if (is_string($mpdCommand) && strpos($mpdCommand,'ERROR') !== false) {
			return 'add_error';
		}
		if ($playAfterInsert) {mpd('play ' . $insPos);}
		if ($first && $mode == 'play') mpd('play ' . $index);
	}
	elseif($isYoutube){
		$url = getYouTubeMPDUrl(getYouTubeId($track_id));
		//$mpdCommand = mpd('addid "' . mpdEscapeChar($url) . '" ' . $insPos);
		if (!$url) {
			return 'add_error';
		}
		$mpdCommand = mpd('addid "' . mpdEscapeChar($url) . '" ' . $insPos);
		if (is_string($mpdCommand) && strpos($mpdCommand,'ERROR') !== false) {
			return 'add_error';
		}
		if ($playAfterInsert) {mpd('play ' . $insPos);}
		if ($first && $mode == 'play') mpd('play ' . $index);
	}
	elseif ($filepath){
		$filepath = str_replace('ompd_ampersand_ompd','&',$filepath);
		$filepath = str_replace('ompd_plus_ompd','+',$filepath);
		//$filepath = str_replace('"','\"',$filepath);
		$mpdCommand = mpd('addid "' . mpdEscapeChar($filepath) . '" ' . $insPos);
		if ($mpdCommand == 'ACK_ERROR_NO_EXIST') {
			//file not found in MPD database - add stream
			playTo($insPos);
			if ($playAfterInsert) {mpd('play ' . $insPos);}
			if ($first && $mode == 'play') {
				mpd('play ' . $index);
			}
			//return 'add_OK';	
		}
		if ($playAfterInsert) {mpd('play ' . $insPos);}
		if ($first && $mode == 'play') mpd('play ' . $index);
	}
	elseif ($dirpath){
		$dirpath = str_replace('ompd_ampersand_ompd','&',$dirpath);
		$dirpath = str_replace('ompd_plus_ompd','+',$dirpath);
		$mpdCommand = mpd('add "' . mpdEscapeChar($dirpath) . '"');
		if ($mpdCommand == 'ACK_ERROR_NO_EXIST') {
			//dir not found in MPD database - add stream
			$fulldirpath = str_replace('ompd_ampersand_ompd','&',$fulldirpath);
			$fulldirpath = str_replace('ompd_plus_ompd','+',$fulldirpath);
			$mediafiles = find_all_files($fulldirpath);
			foreach($mediafiles as $key => $val) {
				playTo($insPos,'',$val);
			}
			/* if ($playAfterInsert) {mpd('play ' . $insPos);}
			if ($first && $mode == 'play') mpd('play ' . $index);
			return 'add_OK'; */
		}
		if ($playAfterInsert) {mpd('play ' . $insPos);}
		if ($first && $mode == 'play') mpd('play ' . $index);
	}

	else {
		while ($track = mysqli_fetch_assoc($query)) {
			if ($file = $track['relative_file']){
				$file = iconv(NJB_DEFAULT_CHARSET, 'UTF-8', $file);
			}
			elseif ($file = $track['stream_url']){
				//$file = iconv(NJB_DEFAULT_CHARSET, 'UTF-8', $file);
				$file = iconv(NJB_DEFAULT_CHARSET, 'UTF-8', str_replace('_NJB_HOME_URL_', NJB_HOME_URL, $file));
			}
			$mpdCommand = mpd('addid "' . mpdEscapeChar($file) . '" ' . $insPos);
			if ($mpdCommand == 'ACK_ERROR_NO_EXIST') {
				//file not found in MPD database - add stream
				playTo($insPos, $track['track_id']);
			}
			elseif ($mpdCommand == 'ACK_ERROR_UNKNOWN') {
				return 'add_error';
			}
		}
		$n++;
		if ($playAfterInsert) {mpd('play ' . $insPos);}
		if ($first && $mode == 'play') {
			mpd('play ' . $index);
		}
			$first = false;
		
	}
	
	if ($cfg['play_queue'] && $mode == 'play' && $n > $cfg['play_queue_limit']) {		
		if ($cfg['player_type'] == NJB_HTTPQ) {
			for ($i = 0; $i < $n - $cfg['play_queue_limit']; $i++) {
				httpq('deletepos', 'index=0');
			}
		}
		elseif ($cfg['player_type'] == NJB_MPD) {
			$status = mpd('status');
			if (version_compare($cfg['mpd_version'], '0.16.0', '<')) {
				for ($i = 0; $i < $n- $cfg['play_queue_limit']; $i++) {
					mpd('delete 0');
				}
			}
			else {
				mpd('delete 0:' . ($n - $cfg['play_queue_limit']));
			}
		}
	}
	if ($album_id && $mds_updateCounter && count($mds_updateCounter) > 0) {
		foreach ($mds_updateCounter as $md_album_id) {
			updateCounter($md_album_id, NJB_COUNTER_PLAY);
		}
	}
	
	return 'add_OK';
}



//  +------------------------------------------------------------------------+
//  | Add url                                                                |
//  +------------------------------------------------------------------------+
function addUrl($mode = 'play') {
	global $cfg, $db;
	
	$url = get('url');
	$urlLower = strtolower($url);
	$addURLresult = 'add_OK';
	
	$file = array();		
	//if (preg_match('#\.[a-zA-Z0-9]{1,4}$#', $url)) {
	if (preg_match('#\.(m3u|pls)$#', $urlLower)) {
		$items = @file($urlLower, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		//message(__FILE__, __LINE__, 'error', '[b]Failed to open url:[/b][br]' . $url);
		if ($items) {
			foreach ($items as $item) {
				// pls:		
				// File1=http://example.com:80
				// m3u:
				// http://example.com:80
				if (preg_match('#^(?:File[0-9]{1,3}=|)((?:tidal|ftp|http|https|mms|mmst|pnm|rtp|rtsp|sdp)://.+)#', $item, $match))
					$file[] = $match[1];
				//print_r($item) . '<br>';
			}
		}
		else {
			$addURLresult = 'add_error';
			return $addURLresult;
		}
	}
	else {
		$file[] = $url;
	}

	if ($cfg['play_queue'] == false)
		$index = 0;
	elseif ($cfg['player_type'] == NJB_MPD) {
		$status = mpd('status');
		$index = $status['playlistlength'];
		$insPos = $status['song'];
	}
	
	$n = $index;
	$first = true;
	
	for ($i = 0; $i < count($file); $i++) {
		$file[$i] = iconv(NJB_DEFAULT_CHARSET, 'UTF-8', $file[$i]);
		mpd('add "' . mpdEscapeChar($file[$i]) . '"');
	}
	
	/* 
	$url = iconv(NJB_DEFAULT_CHARSET, 'UTF-8', $url);
	mpd('add "' . $url . '"');
	 */
	if ($first && $mode == 'play')
		mpd('play ' . $index);
		
		$n++;
		$first = false;
	
	if ($cfg['play_queue'] && $mode == 'play' && $n > $cfg['play_queue_limit']) {	
		if ($cfg['player_type'] == NJB_MPD) {
			$status = mpd('status');
			if (version_compare($cfg['mpd_version'], '0.16.0', '<')) {
				for ($i = 0; $i < $n- $cfg['play_queue_limit']; $i++) {
					mpd('delete 0');
				}
			}
			else {
				mpd('delete 0:' . ($n - $cfg['play_queue_limit']));
			}
		}
	}
	
	return $addURLresult;
}



//  +------------------------------------------------------------------------+
//  | Play Stream direct                                                     |
//  +------------------------------------------------------------------------+
function playStreamDirect() {
	global $db, $cfg;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$favorite_id 	= get('favorite_id');
	$position 		= get('position');
	$url 		= get('url');
	
	$data			= array();
	
	$status = mpd('status');
  if ($status['state'] == 'stop') {
    $insPos = $status['playlistlength'];
  }
  else {
	  $insPos = $status['song'] + 1;
  }
  
	if (!isset($url)) {
    $query = mysqli_query($db,'SELECT stream_url FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id . ' AND position = ' . (int) $position . ' LIMIT 1');    
    $favoriteitem = mysqli_fetch_assoc($query);
    $url = $favoriteitem['stream_url'];
  }
  $url = str_replace('_NJB_HOME_URL_',NJB_HOME_URL,$url);
  mpd('addid "' . mpdEscapeChar($url) . '" ' . $insPos);
	mpd('play ' . $insPos);
	$data['album_id'] = $position;
	$data['playResult'] = 'play_OK'; 
	$data['addResult'] = 'add_OK'; 
	$data['insPos'] = $insPos; 
	
	echo safe_json_encode($data);
}



//  +------------------------------------------------------------------------+
//  | Add Stream direct                                                      |
//  +------------------------------------------------------------------------+
function addStreamDirect() {
	global $db, $cfg;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$favorite_id 	= get('favorite_id');
	$position 		= get('position');
	
	$data			= array();
	
	$query = mysqli_query($db,'SELECT stream_url FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id . ' AND position = ' . (int) $position . ' LIMIT 1');
	
	$favoriteitem = mysqli_fetch_assoc($query);
  $url = $favoriteitem['stream_url'];
  $url = str_replace('_NJB_HOME_URL_',NJB_HOME_URL,$url);

	mpd('add ' . mpdEscapeChar($url));
	
	$data['album_id'] = $position;
	$data['addResult'] = 'add_OK'; 
	//$data['url'] = $url; 
	
	echo safe_json_encode($data);
}




//  +------------------------------------------------------------------------+
//  | Seek image map                                                         |
//  +------------------------------------------------------------------------+
function seekImageMap() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$dx	= get('dx');
	$x	= get('x');
	
	if ($cfg['player_type'] == NJB_HTTPQ) {
		$file	= httpq('getplaylistfile');
		
		$relative_file = str_replace('\\', '/', $file);
		$relative_file = substr($relative_file, strlen($cfg['media_share']));
		
		$query 	= mysqli_query($db,'SELECT miliseconds FROM track WHERE relative_file = "' . mysqli_real_escape_string($db,$relative_file) . '"');
		$track 	= mysqli_fetch_assoc($query);
		
		$miliseconds = round($track['miliseconds'] * $x / ($dx-1));
		httpq('jumptotime', 'ms=' . $miliseconds);
		
		if (get('menu') == 'playlist') {
			$data = array();
			$data['miliseconds']	= (int) $miliseconds;
			$data['max']			= (int) $track['miliseconds'];
			echo safe_json_encode($data);			
		}
	}
	elseif ($cfg['player_type'] == NJB_MPD) {
		$currentsong	= mpd('currentsong');
		$status	= mpd('status');
		
		$parts = parse_url($currentsong['file']);
		parse_str($parts['query'], $url_query);
		$track_id = $url_query['track_id'];
		if ($track_id) 
			$query = mysqli_query($db,'SELECT miliseconds FROM track WHERE relative_file = "' . mysqli_real_escape_string($db,$currentsong['file']) . '" OR track_id = "' .$track_id . '"');
		else
			$query = mysqli_query($db,'SELECT miliseconds FROM track WHERE relative_file = "' . mysqli_real_escape_string($db,$currentsong['file']) . '"');
		
		$track = mysqli_fetch_assoc($query);
		if ($track)
			$track_ms = $track['miliseconds'];
		else {
			//$track_ms = $currentsong['Time'] * 1000;
			$times = explode(":",$status['time']);
			$track_ms	= $times[1] * 1000;
		}
		$miliseconds = round($track_ms * $x / ($dx-1));
		mpd('seek ' . $currentsong['Pos'] .  ' ' . (round($miliseconds / 1000))); //seek in seconds
		
		if (get('menu') == 'playlist') {
			$data = array();
			$data['miliseconds']	= (int) $miliseconds;
			$data['max']			= (int) $track_ms;
			echo safe_json_encode($data);
		}
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Command not supported for this player[/b]');
}




//  +------------------------------------------------------------------------+
//  | Play index                                                             |
//  +------------------------------------------------------------------------+
function playIndex() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$index = (int) get('index');
	
	if ($cfg['player_type'] == NJB_HTTPQ) {
		httpq('stop');
		httpq('setplaylistpos', 'index=' . $index);
		httpq('play');
		if (get('menu') == 'playlist') {
			echo $index;
		}
	}
	elseif ($cfg['player_type'] == NJB_MPD) {
		//mpd('stop');
		mpd('play ' . $index);
		if (get('menu') == 'playlist') {
			echo $index;
		}
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Command not supported for this player[/b]');
}



//  +------------------------------------------------------------------------+
//  | Delete index (Ajax)                                                    |
//  +------------------------------------------------------------------------+
function deleteIndexAjax() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$index = (int) get('index');
	echo $index;
	if ($cfg['player_type'] == NJB_HTTPQ) {
		httpq('deletepos', 'index=' . $index);
		if (get('menu') == 'playlist') {
			header('HTTP/1.1 500 Internal Server Error');
			echo NJB_HOME_URL . 'playlist.php';
		}
	}
	elseif ($cfg['player_type'] == NJB_MPD) {	
		mpd('delete ' . $index);
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Command not supported for this player[/b]');
}



//  +------------------------------------------------------------------------+
//  | Delete index                                                           |
//  +------------------------------------------------------------------------+
function deleteIndex() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$index = (int) get('index');
	
	if ($cfg['player_type'] == NJB_HTTPQ) {
		httpq('deletepos', 'index=' . $index);
		if (get('menu') == 'playlist') {
			header('HTTP/1.1 500 Internal Server Error');
			echo NJB_HOME_URL . 'playlist.php';
		}
	}
	elseif ($cfg['player_type'] == NJB_MPD) {
		
		mpd('delete ' . $index);
		if (get('menu') == 'playlist') {
			$data = array();
			$data['index'] = (string) $index;
			echo safe_json_encode($data);
		}		
		
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Command not supported for this player[/b]');
}




//  +------------------------------------------------------------------------+
//  | Delete all below index                                                 |
//  +------------------------------------------------------------------------+
function deleteBelowIndex() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$index = (int) get('index');
	$status = mpd('status');
		
	mpd('delete ' . ($index + 1) . ':' . $status['playlistlength']);
	if (get('menu') == 'playlist') {
		$data = array();
		$data['index'] = (string) $index;
		echo safe_json_encode($data);
	}
}



//  +------------------------------------------------------------------------+
//  | Delete all from album                                                  |
//  +------------------------------------------------------------------------+
function deleteAlbum() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$index = (int) get('index');
	$playlist = mpd('playlist');
  $source = $playlist[$index];
	$itemsToDel = array();
  $tracks = array();
  
  if (strpos($source,"action=streamTidal") !== false){
    $queryURL = parse_url($source, PHP_URL_QUERY);
    parse_str($queryURL, $output);
    $trackId = $output['track_id'];
    
    $query = mysqli_query($db,"SELECT album_id FROM tidal_track WHERE track_id='" . $trackId . "' LIMIT 1");
    $album = mysqli_fetch_assoc($query);
    $albumId = $album['album_id'];
    
    $query = mysqli_query($db,"SELECT track_id FROM tidal_track WHERE album_id='" . $albumId . "'");
    while ($track = mysqli_fetch_assoc($query)){
      $tracks[] = $track['track_id'];
    };
    
    foreach ($playlist as $key => $playlistItem) {
      if (strpos($playlistItem,"action=streamTidal") !== false){
        $queryURL = parse_url($playlistItem, PHP_URL_QUERY);
        parse_str($queryURL, $output);
        $trackId = $output['track_id'];
        if (in_array($trackId,$tracks)) {
          $itemsToDel[] = $key;
        }
      }
    }
  }
  elseif (strpos($source,"action=streamHRA") !== false){
    $queryURL = parse_url($source, PHP_URL_QUERY);
    parse_str($queryURL, $output);
    $albumId = $output['ompd_album_id'];
    foreach ($playlist as $key => $playlistItem) {
      if (strpos($playlistItem,"action=streamHRA") !== false){
        $queryURL = parse_url($playlistItem, PHP_URL_QUERY);
        parse_str($queryURL, $output);
        if ($output['ompd_album_id'] == $albumId) {
          $itemsToDel[] = $key;
        }
      }
    }
  }
  elseif (strpos($source,"action=streamYouTube") !== false){
    $queryURL = parse_url($source, PHP_URL_QUERY);
    parse_str($queryURL, $output);
    $trackId = $output['track_id'];
    foreach ($playlist as $key => $playlistItem) {
      if (strpos($playlistItem,"action=streamYouTube") !== false){
        $queryURL = parse_url($playlistItem, PHP_URL_QUERY);
        parse_str($queryURL, $output);
        if ($output['track_id'] == $trackId) {
          $itemsToDel[] = $key;
        }
      }
    }
  }
  elseif (strpos($source,"action=streamTo") !== false && strpos($source,"track_id=") !== false){
    $queryURL = parse_url($source, PHP_URL_QUERY);
    parse_str($queryURL, $output);
    $trackId = $output['track_id'];
    
    $query = mysqli_query($db,"SELECT album_id FROM track WHERE track_id='" . mysqli_real_escape_string($db,$trackId) . "' LIMIT 1");
    $album = mysqli_fetch_assoc($query);
    $albumId = $album['album_id'];
    
    foreach ($playlist as $key => $playlistItem) {
      if (strpos($playlistItem,"track_id=") !== false){
        $queryURL = parse_url($playlistItem, PHP_URL_QUERY);
        parse_str($queryURL, $output);
        $trackId = $output['track_id'];
        
        $query = mysqli_query($db,"SELECT album_id FROM track WHERE track_id='" . mysqli_real_escape_string($db,$trackId) . "' LIMIT 1");
        $album = mysqli_fetch_assoc($query);
        
        if ($album['album_id'] == $albumId) {
          $itemsToDel[] = $key;
        }
      }
    }
  }
  elseif (strpos($source,"action=streamTo") !== false && strpos($source,"filepath=") !== false){
    $queryURL = parse_url($source, PHP_URL_QUERY);
    parse_str($queryURL, $output);
    $filepath = $output['filepath'];
    $dir = dirname(urldecode($filepath));
    
    foreach ($playlist as $key => $playlistItem) {
      if (strpos($playlistItem,"filepath=") !== false){
        $queryURL = parse_url($playlistItem, PHP_URL_QUERY);
        parse_str($queryURL, $output);
        if (dirname(urldecode($output['filepath'])) == $dir) {
          $itemsToDel[] = $key;
        }
      }
    }
  }
  elseif (strpos($source,"://") !== false){
    foreach ($playlist as $key => $playlistItem) {
      if ($playlistItem == $source) {
        $itemsToDel[] = $key;
      }
    }
  }
  else {
    $query = mysqli_query($db,"SELECT album_id FROM track WHERE relative_file='" . mysqli_real_escape_string($db,$source) . "' LIMIT 1");
    $album = mysqli_fetch_assoc($query);
    $albumId = $album['album_id'];
    
    foreach ($playlist as $key => $playlistItem) {
      if (strpos($playlistItem,"://") === false){
        $query = mysqli_query($db,"SELECT album_id FROM track WHERE relative_file='" . mysqli_real_escape_string($db,$playlistItem) . "' LIMIT 1");
        $album = mysqli_fetch_assoc($query);
        if ($album['album_id'] == $albumId) {
          $itemsToDel[] = $key;
        }
      }
    }
  }
  
  foreach ( array_reverse($itemsToDel) as $delIndex ) {
    mpd('delete ' . $delIndex);
  }
  
  
	if (get('menu') == 'playlist') {
		$data = array();
		$data['index'] = $index;
		//$data['playlist'] = $playlist;
		$data['source'] = $source;
		$data['trackId'] = $trackId;
		$data['albumId'] = $albumId;
		//$data['tracks'] = $tracks;
		$data['itemsToDel'] = $itemsToDel;
		echo safe_json_encode($data);
	}
}




//  +------------------------------------------------------------------------+
//  | Delete played                                                          |
//  +------------------------------------------------------------------------+
function deletePlayed() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	if ($cfg['player_type'] == NJB_HTTPQ) {
		$listpos = httpq('getlistpos');
		for ($i = 0; $i < $listpos; $i++) {
			httpq('deletepos', 'index=0');
		}
		if (get('menu') == 'playlist' && $listpos > 0) {
			header('HTTP/1.1 500 Internal Server Error');
			echo NJB_HOME_URL . 'playlist.php';
		}
	}
	elseif ($cfg['player_type'] == NJB_VLC) {
		vlc('pl_empty'); // Not supported yet, clear whole playlist instead!
	}
	elseif ($cfg['player_type'] == NJB_MPD) {
		$status = mpd('status');
		if (version_compare($cfg['mpd_version'], '0.16.0', '<')) {
			for ($i = 0; $i < $status['song']; $i++) {
				mpd('delete 0');
			}
		}
		else {
			mpd('delete 0:' . $status['song']);
		}
		if (get('menu') == 'playlist' && $status['song'] > 0) {
			header('HTTP/1.1 500 Internal Server Error');
			echo NJB_HOME_URL . 'playlist.php';
		}
	}
}



//  +------------------------------------------------------------------------+
//  | Crop			                                                             |
//  +------------------------------------------------------------------------+
function crop() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	
	$status = mpd('status');
	if (version_compare($cfg['mpd_version'], '0.16.0', '<')) {
		for ($i = 0; $i < $status['song']; $i++) {
			mpd('delete 0');
		}
	}
	else {
		mpd('delete 0:' . $status['song']);
		$status = mpd('status');
		if (($status['song'] + 1) < $status['playlistlength'])
		mpd('delete ' . ($status['song'] + 1) . ':' . ($status['playlistlength']));
	}
	if (get('menu') == 'playlist' && $status['song'] > 0) {
		header('HTTP/1.1 500 Internal Server Error');
		echo NJB_HOME_URL . 'playlist.php';
	}

}



//  +------------------------------------------------------------------------+
//  | Consume		                                                             |
//  +------------------------------------------------------------------------+
function consume() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$consume = get('consume');
	if ($consume == '1' || $consume == '0') {
		mpd('consume ' . $consume);
	}

	$status = mpd('status');
	$consume = $status['consume'];
	$data = array();
	$data['consume'] =  $consume;
	
	echo safe_json_encode($data);
	
}



//  +------------------------------------------------------------------------+
//  | Move track                                                             |
//  +------------------------------------------------------------------------+
function moveTrack() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$fromPosition		= (int) get('fromPosition');
	$toPosition  = get('toPosition') == 'playNext' ? 'playNext' : (int) get('toPosition');
	$isMoveToTop		= get('isMoveToTop');
	
	
	if ($toPosition === 'playNext') {
		$status = mpd('status');
		$curPos = (int) $status['song'];
		if ($curPos > $fromPosition) $toPosition = $curPos;
		if ($curPos < $fromPosition) $toPosition = $curPos + 1;
		if ($curPos == $fromPosition) return;
	}
	else {
		if ($fromPosition > $toPosition && $isMoveToTop == 'false') ++$toPosition; //only when moving up but not to top
	}
	
	$comm = 'move ' . $fromPosition . ' ' . $toPosition;
	mpd($comm);
	

	
	/* $fromPosition		= (int) get('fromPosition');
	$toPosition			= (int) get('toPosition');
	$isMoveToTop		= get('isMoveToTop');
	
	//$status = mpd('status');
	if ($fromPosition > $toPosition && $isMoveToTop == 'false') ++$toPosition; //only when moving up but not to top
	$comm = 'move ' . $fromPosition . ' ' . $toPosition;
	mpd($comm); */
	
}




//  +------------------------------------------------------------------------+
//  | Volume image map                                                       |
//  +------------------------------------------------------------------------+
function volumeImageMap() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	$dx         = (int) get('dx');
	$x			= (int) get('x');
	
	if ($cfg['player_type'] == NJB_HTTPQ) {
		$volume		= round(255 * $x / ($dx-1));
		if ($volume < round(255 * 0.05)) $volume = 0; // set volume to zero
		if ($volume > round(255 * 0.95)) $volume = 255; // set volume to max
		httpq('setvolume', 'level=' . $volume);
		
		mysqli_query($db,'UPDATE player
					SET mute_volume	= ' . (int) $volume . '
					WHERE player_id	= ' . (int) $cfg['player_id']);
		
		if (get('menu') == 'playlist')
			echo $volume;
	}
	elseif ($cfg['player_type'] == NJB_MPD) {
		$data = array();
		$volume		= round(100 * $x / ($dx-1));
		if ($volume < round(100 * 0.05)) $volume = 0; // set volume to zero
		if ($volume > round(100 * 0.95)) $volume = 100; // set volume to max
		mpd('setvol ' . $volume);
		
		mysqli_query($db,'UPDATE player
					SET mute_volume	= ' . (int) $volume . '
					WHERE player_id	= ' . (int) $cfg['player_id']);
		
		//if (get('menu') == 'playlist')
			//echo json_encode($volume);
				$data['volume'] = $volume;
				$data['player_id'] = $cfg['player_id'];
				echo safe_json_encode($data);
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Command not supported for this player[/b]');
}




//  +------------------------------------------------------------------------+
//  | Toggle mute                                                            |
//  +------------------------------------------------------------------------+
function toggleMute() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	if ($cfg['player_type'] == NJB_HTTPQ) {
		$volume	= httpq('getvolume');
		
		if ($volume == 0) {
			$query = mysqli_query($db,'SELECT mute_volume FROM player WHERE player_id = ' . (int) $cfg['player_id']);
			$player = mysqli_fetch_assoc($query);
			
			httpq('setvolume', 'level=' . $player['mute_volume']);
			mysqli_query($db,'UPDATE player
				SET mute_volume	= 0
				WHERE player_id	= ' . (int) $cfg['player_id']);
			$volume = $player['mute_volume'];
		}
		else {
			httpq('setvolume', 'level=0');
			mysqli_query($db,'UPDATE player
				SET mute_volume	= ' . (int) $volume . '
				WHERE player_id	= ' . (int) $cfg['player_id']);
			$volume = -$volume;
		}
		
		if (get('menu') == 'playlist')
			echo $volume;
	}
	elseif ($cfg['player_type'] == NJB_MPD) {
		$status = mpd('status');
		$volume	= $status['volume'];
		
		if ($volume == 0) {
			$query = mysqli_query($db,'SELECT mute_volume FROM player WHERE player_id = ' . (int) $cfg['player_id']);
			$player = mysqli_fetch_assoc($query);
			
			mpd('setvol ' . $player['mute_volume']);
			mysqli_query($db,'UPDATE player
				SET mute_volume	= 0
				WHERE player_id	= ' . (int) $cfg['player_id']);
			$volume = $player['mute_volume'];
		}
		else {
			mpd('setvol 0');
			mysqli_query($db,'UPDATE player
				SET mute_volume	= ' . (int) $volume . '
				WHERE player_id	= ' . (int) $cfg['player_id']);
			$volume = -$volume;
		}
		if (get('menu') == 'playlist')
			echo $volume;
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Command not supported for this player[/b]');
}




//  +------------------------------------------------------------------------+
//  | Toggle shuffle                                                         |
//  +------------------------------------------------------------------------+
function toggleShuffle() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	if ($cfg['player_type'] == NJB_HTTPQ) {
		$invert = (int) (httpq('shuffle_status') xor 1);
		
		httpq('shuffle', 'enable=' . $invert);
		if (get('menu') == 'playlist')
			echo $invert;
	}
	elseif ($cfg['player_type'] == NJB_MPD) {
		$status = mpd('status');
		$invert = (int) ($status['random'] xor 1);
		
		mpd('random ' . $invert);
		if (get('menu') == 'playlist')
			echo $invert;
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Command not supported for this player[/b]');
}




//  +------------------------------------------------------------------------+
//  | Toggle repeat                                                          |
//  +------------------------------------------------------------------------+
function toggleRepeat() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	if ($cfg['player_type'] == NJB_HTTPQ) {
		$invert = (int) (httpq('repeat_status') xor 1);
		httpq('repeat', 'enable=' . $invert);
		
		if (get('menu') == 'playlist')
			echo $invert;
	}	
	elseif ($cfg['player_type'] == NJB_MPD) {
		$status = mpd('status');
		$invert = (int) ($status['repeat'] xor 1);
		
		mpd('repeat ' . $invert);
		if (get('menu') == 'playlist')
			echo $invert;
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Command not supported for this player[/b]');
}




//  +------------------------------------------------------------------------+
//  | Loop gain                                                              |
//  +------------------------------------------------------------------------+
function loopGain() {
	global $cfg, $db;
	authenticate('access_play');
	require_once('include/play.inc.php');
	
	if ($cfg['player_type'] == NJB_MPD) {
		$gain = mpd('replay_gain_status');
		if ($gain['replay_gain_mode'] == 'off')	{
			$mode = 'album';
		}
		if ($gain['replay_gain_mode'] == 'album') {
			$mode = 'auto';
		}
		if ($gain['replay_gain_mode'] == 'auto') {
			$mode = 'track';
		}
		if ($gain['replay_gain_mode'] == 'track') {
			$mode = 'off';
		}
		
		mpd('replay_gain_mode ' . $mode);
		if (get('menu') == 'playlist')
			echo '"' . $mode . '"';
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Command not supported for this player[/b]');
}


//  +---------------------------------------------------------------------------+
//  | Playlist status                                                           |
//  +---------------------------------------------------------------------------+
function playlistStatus() {
	global $cfg, $db;
	authenticate('access_playlist', false, false, true);
	require_once('include/play.inc.php');
	
	$track_id = get('track_id');
	$data = array();
	
	if ($cfg['player_type'] == NJB_HTTPQ) {
		// volume
		$volume	= (int) httpq('getvolume');
		
		// get mute volume
		if ($volume == 0) {
			$query	= mysqli_query($db,'SELECT mute_volume FROM player WHERE player_id = ' . (int) $cfg['player_id']);
			$temp	= mysqli_fetch_assoc($query);
			$volume = -$temp['mute_volume'];
		}
		
		$data = array();
		$data['hash']			= (string) httpq('gethash');
		$data['miliseconds']	= (int) httpq('getoutputtime', 'frmt=0');
		$data['listpos']		= (int) httpq('getlistpos');
		$data['isplaying']		= (int) httpq('isplaying');
		$data['repeat']			= (int) httpq('repeat_status');
		$data['shuffle']		= (int) httpq('shuffle_status');
		$data['volume']			= (int) $volume;
		$data['gain']			= -1;
		echo safe_json_encode($data);
	}
	if ($cfg['player_type'] == NJB_MPD) {
		
		$playlist	= mpd('playlist');
		$hasStream = preg_grep('#^(tidal|ftp|http|https|mms|mmst|pnm|rtp|rtsp|sdp)://#', $playlist);
		
		if (count($hasStream) == 0) $hasStream = 'false';
		else $hasStream = 'true';
		
		$data['hasStream'] = $hasStream;
		
		//$playlistInfo	= mpd('playlistinfo');
		$status 	= mpd('status');
		$currentsong = mpd('currentsong');
		
		$data['hash']			= md5(implode('<seperation>', $playlist));
		$data['listpos']		= isset($status['song']) ? (int) $status['song'] : 0;
		$data['totalTracks']		= (int) $status['playlistlength'];
		$vol = 0;
    if (isset($status['volume'])) {
      $vol = (int) $status['volume'];
    }
    $data['volume']			= $vol;
		$data['repeat']			= (int) $status['repeat'];
		$data['shuffle']		= (int) $status['random'];
		$data['consume']		= $status['consume'];
		
		if (strpos($currentsong['file'],'ompd_title=') !== false){
			//stream from Youtube
			$parts = parse_url($currentsong['file']);
			parse_str($parts['query'], $query);
			$data['title'] = urldecode($query['ompd_title']);
      $data['title_core'] = urldecode(findCoreTrackTitle($query['ompd_title']));
			$data['track_artist'] = urldecode($query['ompd_artist']);
			$data['year'] = urldecode($query['ompd_year']);
		}
		else {
			if (isset($currentsong['Name'])) {
				$data['name'] = $currentsong['Name'];
			}
			else {
				$data['name'] = '';
			}
			
			if (isset($currentsong['Title'])) {
				$data['title']	= $currentsong['Title'];
        $data['title_core'] = findCoreTrackTitle($currentsong['Title']);
			}
			elseif (strpos($currentsong['file'],'filepath=') !== false){
				$pos = strpos($currentsong['file'],'filepath=');
				$filepath = substr($currentsong['file'],$pos + 9, strlen($currentsong['file']) - $pos);
				$filepath = urldecode($filepath);
				$data['title'] = basename($filepath);
			}
      elseif (isRadio($currentsong['file'])) {
        if (isset($currentsong['Name'])) {
          $data['title'] = $currentsong['Name'];
        } 
        else {
          $browser = initRadioBrowser();
          if ($browser) {
            $radio = $browser->getStationsByUuid(getRadioId($currentsong['file']));
            if ($radio) {
              $data['title'] = $radio[0]['name'];
              $data['title_core'] = $radio[0]['name'];
            }
          } 
          else {
            $data['title']	= basename($currentsong['file']);
            $data['title_core']	= pathinfo($currentsong['file'],PATHINFO_FILENAME);
          }
        }
      }
		}
		if (!isset($data['track_artist'])) $data['track_artist'] = isset($currentsong['Artist']) ? $currentsong['Artist'] : null;
		$data['relative_file'] = $currentsong['file'];
		
		//$data['Time'] = $currentsong['Time'] * 1000;
		$times = explode(":",$status['time']);
		$data['Time'] = $times[1] * 1000;
		
		$data['isStream'] = (string) 'false';
		
		//if (strpos($currentsong['file'],"://") !== false) $data['isStream'] = (string) 'true';
		//if ($data['isStream'] == 'true') {
			if (strpos($currentsong['file'],"://") !== false) {
				$data['isStream'] = (string) 'true';
				$data['audio_dataformat']	= (string) 'Stream';
        $data['stream_source']	= (string) '';
				if (strpos($currentsong['file'],"action=streamTidal") !== false) {
          $data['stream_source']	= (string) 'Tidal&nbsp;';
        }
        elseif (strpos($currentsong['file'],"action=streamHRA") !== false) {
          $data['stream_source']	= (string) 'HRA&nbsp;';
        }
        elseif (strpos($currentsong['file'],"action=streamYouTube") !== false) {
          $data['stream_source']	= (string) 'YouTube&nbsp;';
        }
			}
			else {
				$data['audio_dataformat']	= (string) strtoupper(pathinfo($currentsong['file'], PATHINFO_EXTENSION));
				$plLen = $status['playlistlength'];
				$totalTime = floor(-$status['elapsed']);
				$pl = mpd('playlistinfo');
				$i = 0;
				if ($currentsong['Pos']) $i = $currentsong['Pos'];
				for($i;$i<$plLen;$i++) {
					//$pl = mpd('playlistinfo ' . $i);
					$totalTime = $totalTime + $pl['Time'][$i];
				}
				$totalPlaylistTime = 0;
				for($i=0;$i<$plLen;$i++) {
					$totalPlaylistTime = $totalPlaylistTime + $pl['Time'][$i];
				}
				$data['end_time'] = date('H:i', time()+$totalTime);
				$data['end_in'] = gmdate('H:i:s',$totalTime);
				$data['total_time'] = gmdate('H:i:s', $totalPlaylistTime);
        $data['stream_source']	= '';
			}
			$audio = array();
			if ($status['audio']){
				$audio = explode(':',$status['audio']);
				if ($audio[1] == 'f') $audio[1] = '16';
			}
			else {
				$audio[0] = $audio[1] = '0 ';
			}
			$data['audio_bits_per_sample']	= (string) $audio[1];
			$data['audio_sample_rate']	= (string) $audio[0];
			$data['audio_profile']	= (string) ($status['bitrate'] ? $status['bitrate'] . ' kbps' : '0 kbps');
		//}
		$data['isplaying'] = 0;
		if ($status['state'] == 'stop')		$data['isplaying'] = 0;
		if ($status['state'] == 'play')		$data['isplaying'] = 1;
		if ($status['state'] == 'pause')	$data['isplaying'] = 3;
		
		$data['miliseconds'] = ($status['state'] == 'stop') ? 0 : (int) round($status['elapsed'] * 1000);
		
		$data['gain'] = -1;
		if (version_compare($cfg['mpd_version'], '0.16.0', '>=')) {
			$gain = mpd('replay_gain_status');
			$data['gain'] = (string) $gain['replay_gain_mode'];
		}
		
    $data['auto_queue'] = 'off';
    if (isAutoQueueRunning()) {
      $data['auto_queue'] = 'on';
    }

		// get mute volume
		if ($data['volume'] == 0) {
			$query	= mysqli_query($db,'SELECT mute_volume FROM player WHERE player_id = ' . (int) $cfg['player_id']);
			$temp	= mysqli_fetch_assoc($query);
			$data['volume'] = -$temp['mute_volume'];
		}
		//$data['track_id'] = $track_id;
		echo safe_json_encode($data);	
	}
}




//  +---------------------------------------------------------------------------+
//  | Playlist track                                                            |
//  +---------------------------------------------------------------------------+
function playlistTrack() {
	global $cfg, $db, $t;
	authenticate('access_playlist', false, false, true);
	require_once('include/play.inc.php');
	
	$track_id = get('track_id');
	$data = array();
	$data['audio_sample_rate'] = '---';
	$title = '';
	$currentsong	= mpd('currentsong');
  $status 	= mpd('status');

/*
	if (!$currentsong['file']) { //mpd in unknown state
		$data['album_artist'] = '&nbsp;';
		$data['track_artist'] = '&nbsp;';
		$data['track_composer'] = '-';
		$data['title'] = '-';
		$data['album'] = '-';
		$data['by'] = '-';
		$data['year'] = '&nbsp;';
		$data['genre'] = '-';
		$data['audio_dataformat'] = '---';
		$data['audio_bits_per_sample'] = '---';
		$data['audio_sample_rate'] = '---';
		$data['audio_profile'] = '---';
		$data['number'] = '';
		$data['miliseconds'] = '---'; 
		$data['inFavorite'] = false;
		$data['onBlacklist'] = false;
		//echo safe_json_encode($data);
		//return;
	}
*/
	
	$track_artist = array();

  $data['listpos']		= isset($status['song']) ? (int) $status['song'] : 0;
	$data['totalTracks']		= (int) $status['playlistlength'];
	$data['track_mpd_url'] = getTrackMpdUrl($currentsong['file']);
	
	if ($track_id !='') {
		if (isTidal($track_id)) {
			$track_id = getTidalId($track_id);
      $sql='SELECT tidal_track.artist, NULL as track_composer, tidal_album.artist AS album_artist, tidal_track.title, NULL as featuring, (tidal_track.seconds * 1000) as miliseconds, NULL as relative_file, tidal_album.album, CONCAT("tidal_", tidal_album.album_id) as image_id, CONCAT("tidal_",tidal_album.album_id) as album_id, tidal_track.genre_id as genre, NULL as audio_bitrate, NULL as audio_dataformat, NULL as audio_bits_per_sample, NULL as audio_sample_rate, tidal_album.genre_id, NULL as audio_profile, tidal_track.artist as track_artist, SUBSTRING(tidal_album.album_date,1,4) as year, tidal_track.number, NULL as comment, CONCAT("tidal_", tidal_track.track_id) as track_id, NULL as trackYear, NULL as dr, NULL as album_dr
				FROM tidal_track, tidal_album 
				WHERE tidal_track.album_id = tidal_album.album_id
				AND tidal_track.track_id = "' . mysqli_real_escape_string($db,$track_id) . '"';
			$query = mysqli_query($db,$sql);
      $rows = mysqli_num_rows($query);
      if ($rows == 0) {
        $tidal_album_id =getTrackAlbumFromTidal($track_id);
        if ($tidal_album_id) {
          getAlbumFromTidal($tidal_album_id);
        }
			$query = mysqli_query($db,$sql);
      }
			$track = mysqli_fetch_assoc($query);
    	$conn = $t->connect();
      if ($conn === true){
        $trackTidalInfo = $t->getTrack($track_id);
        if ($trackTidalInfo) {
          if ($trackTidalInfo['artists'][0]) {
            foreach ($trackTidalInfo['artists'] as $artist) {
              $data['track_artist_tidal_id'][]= $artist['id'];
            }
          }
          elseif ($trackTidalInfo['artist']['id']){
            $data['track_artist_tidal_id'][]= $trackTidalInfo['artist']['id'];
          }
        }
      }
			/* $query = mysqli_query($db,'SELECT image_front FROM bitmap WHERE image_id="' . mysqli_real_escape_string($db,$track['image_id']) . '"');
			$bitmap = mysqli_fetch_assoc($query); */
		}
		else {
			$query = mysqli_query($db,'SELECT track.artist, track.composer as track_composer, album.artist AS album_artist, title, featuring, miliseconds, relative_file, album, album.image_id, album.album_id, track.genre, track.audio_bitrate, track.audio_dataformat, track.audio_bits_per_sample, track.audio_sample_rate, album.genre_id, track.audio_profile, track.track_artist, album.year as year, track.number, track.comment, track.track_id, track.year as trackYear, track.dr, album.album_dr
				FROM track, album 
				WHERE track.album_id = album.album_id
				AND track_id = "' . mysqli_real_escape_string($db,$track_id) . '"');
			$track = mysqli_fetch_assoc($query);
			
			$query = mysqli_query($db,'SELECT image_front FROM bitmap WHERE image_id="' . mysqli_real_escape_string($db,$track['image_id']) . '"');
			$bitmap = mysqli_fetch_assoc($query);
		}


		$other_track_version = false;
		$title = $track['title'];
		
		$exploded = multiexplode($cfg['artist_separator'],$track['track_artist']);
		
		if ($cfg['testing'] == 'on' && !in_array(', ',$cfg['artist_separator'])) {
			$cfg['artist_separator'][] = ', ';
		}
		$explodedComposer = multiexplode($cfg['artist_separator'],$track['track_composer']);
		
		$isStream = false;
		if (strpos($currentsong['file'],"://") !== false) $isStream = true;
		
		$data['album_artist'] = (string) ($track['album_artist'] == "Various Artists") ? rawurlencode($track['track_artist']) : rawurlencode($track['album_artist']);
		$data['track_artist']	= $exploded;
		$data['track_artist_all']	= $track['track_artist'];
		$data['track_artist_url']	= $exploded;
		$data['track_artist_url_all']	= (string) rawurlencode($track['track_artist']);
		$data['track_composer']	= $explodedComposer;
		$data['track_composer_all']	= $track['track_composer'];
		$data['track_composer_url']	= $explodedComposer;
		$data['track_composer_url_all']	= (string) rawurlencode($track['track_composer']);
		$data['title']		= (string) $track['title'];
		$data['album']		= (string) $track['album'];
		//$data['album']		= (string) $title;
		//$data['by']			= (string) $by;
		$data['image_id']	= (string) $track['image_id'];
		$data['album_id']	= (string) $track['album_id'];
		$data['year']	= ((is_null($track['year'])) ? (string) $track['trackYear'] : (string) $track['year']);
		$data['genres'] = parseMultiGenre($track['genre']);
		/* $data['genre']	= (string) $track['genre'];
		$data['genre_id']	= (string) $track['genre_id']; */
		$data['audio_dataformat']	= (string) strtoupper($track['audio_dataformat']);
		if ($isStream) {
			$data['audio_dataformat'] = $data['audio_dataformat'] . " (stream)";
		}
		$data['audio_bits_per_sample']	= (string) $track['audio_bits_per_sample'];
		$data['audio_sample_rate']	= (string) $track['audio_sample_rate'];
		if ($track['audio_profile'] == 'Lossless compression')
			$data['audio_profile']	= (string) (floor($track['audio_bitrate']/1000)) . ' kbps';
		else
			$data['audio_profile']	= (string) $track['audio_profile'];
		
		$data['number']	= (string) $track['number'] . '. ';
		$data['miliseconds']	= (string) $track['miliseconds'];
		$data['other_track_version']	= (boolean) $other_track_version;
		$data['comment']	= (string) $track['comment'];
		$data['track_id']	= (string) $track['track_id'];
		$data['relative_file']	= (string) $track['relative_file'];
		/* $data['inFavorite'] = (boolean) $inFavorite;
		$data['onBlacklist'] = (boolean) $onBlacklist; */
		$data['dr']	= (string) $track['dr'];
		$data['album_dr']	= (string) $track['album_dr'];
		//$data['title_core'] = $title;
		$data['title_core'] = findCoreTrackTitle($title);
	}
	else { //track not found in OMPD DB - read info from MPD
		//require_once('include/play.inc.php');
		$album_id = '';
		$status 		= mpd('status');
		if ($status['time'] == '0:00') {
			//wait 1s to mpd reads info from stream
			sleep(1);
			$status = mpd('status');
		}
		$times = explode(":",$status['time']);
    
		//$currentsong	= mpd('currentsong');
		
		$audio = array();
		$audio = explode(':',$status['audio']);
		if ($audio[1] == 'f') $audio[1] = '16';
			
		$data['isStream'] = 'false';
		if (strpos($currentsong['file'],"://") !== false) {
			$data['isStream'] = (string) 'true';
			//image for e.g. radio stations
			$url_path = '';
			if ($url_path = parse_url($currentsong['file'], PHP_URL_PATH)) {
				$url_path = str_replace('/','__',$url_path);
			}
			$imageFile = $cfg['stream_covers_dir'] . parse_url($currentsong['file'], PHP_URL_HOST) . $url_path;
			if (file_exists($imageFile . '.jpg')) {
				$data['imageFile'] = $imageFile . '.jpg';
			}
			elseif (file_exists($imageFile . '.png')) {
				$data['imageFile'] = $imageFile . '.png';
			}
			if (strpos($currentsong['file'],'filepath=') !== false){
				$pos = strpos($currentsong['file'],'filepath=');
				$filepath = substr($currentsong['file'],$pos + 9, strlen($currentsong['file']) - $pos);
				$filepath = urldecode($filepath);
				$relative_file = urlencode(dirname($filepath));
			}
		} 
		else {
			$f = $cfg['media_dir'] . $currentsong['file'];
			if (file_exists($f)) {
				$relative_file = urlencode(dirname($f));
			}
		}
		
		/* if (isset($currentsong['Artist'])) 
			$artist	= $currentsong['Artist'];
		else 
			$$artist	= $currentsong['file'];
		 */
		 
		if (strpos($currentsong['file'],'streamHRA') !== false){
			//stream from HRA
			$parts = parse_url($currentsong['file']);
			parse_str($parts['query'], $query);
			$title = urldecode($query['ompd_title']);
			//$track_artist = urldecode($query['ompd_artist']);
			$currentsong['Artist'] = urldecode($query['ompd_artist']);
			$album = urldecode($query['ompd_album_title']);
			$album_id = urldecode("hra_" . $query['ompd_album_id']);
			$currentsong['Date'] = urldecode($query['ompd_year']);
			$times[1] = (int)urldecode($query['ompd_duration']);
			$data['thumbnail'] = urldecode($query['ompd_thumbnail']);
			$currentsong['Genre'] = urldecode($query['ompd_genre']);;
		}
		elseif (strpos($currentsong['file'],'ompd_title=') !== false){
			//stream from Youtube
			$parts = parse_url($currentsong['file']);
			parse_str($parts['query'], $query);
			$title = urldecode($query['ompd_title']);
			//$track_artist = urldecode($query['ompd_artist']);
			$currentsong['Artist'] = urldecode($query['ompd_artist']);
			$album = urldecode($query['ompd_webpage']);
			$currentsong['Date'] = urldecode($query['ompd_year']);
			$times[1] = (int)urldecode($query['ompd_duration']);
			$data['thumbnail'] = urldecode($query['ompd_thumbnail']);
			$currentsong['Genre'] = '&nbsp;';
		}
		else {
			if (isset($currentsong['Name'])) {
				$album	= $currentsong['Name'];
			}
			elseif (isset($currentsong['Album'])) {
				$album	= $currentsong['Album'];
			}
			elseif (strpos($currentsong['file'],'filepath=') !== false){
				
				$title = basename($filepath);
				$pos = strpos($filepath, $title);
				$album = substr($filepath, 0, $pos);
			}
			else {
				$album = '&nbsp;';
			}
			if (isset($currentsong['Title'])) {
				$title	= $currentsong['Title'];
			}
			elseif ($title == '') {
        $title	= basename($currentsong['file']);
      }
		}
		/* else
			$table_track['title']	= $currentsong['file']; */
    $data['image_url'] = '';
		if (isRadio($data['track_mpd_url'])){
      $browser = initRadioBrowser();
      if($browser) {
        $radio = $browser->getStationsByUuid(getRadioId($data['track_mpd_url']));
        if ($radio) {
          $data['image_url'] = $radio[0]['homepage'];
          if ($title	== basename($currentsong['file'])) {
            $title = $radio[0]['name'];
            $track_artist = $currentsong['Artist'];
          }
          $album = $radio[0]['name'];
          if ($radio[0]['homepage']) {
            $album = '<a href = "' . $radio[0]['homepage'] .'" target="_blank">' . $radio[0]['name'] . '</a>';
          }
        }
      }
    }
		$data['album_artist'] = (string) ($currentsong['AlbumArtist']);
		//$track_artist = array();
		//$track_artist[] = $currentsong['Artist'];
		//if (!$track_artist) $track_artist = $currentsong['Artist'];
		$track_artist = $currentsong['Artist'];
		$exploded = multiexplode($cfg['artist_separator'],$track_artist);
		if (($exploded[0]) == '') $exploded[0] = '&nbsp;';
		$data['track_artist']	= $exploded;
		$data['track_artist_url']	= $exploded;
		$data['track_artist_url_all']	= (string) rawurlencode($currentsong['Artist']);
		$data['track_artist_all']	= (string) ($currentsong['Artist']);
		$data['track_composer']	= '';
		$data['track_composer_all']	= '';
		$data['track_composer_url']	= '';
		$data['track_composer_url_all']	='';
		$data['title'] = (string) $title;
		$data['album']		= (string) $album;
		$data['by']			= (string) '';
		$data['image_id']	= (string) '';
		$data['album_id']	= (string) $album_id;
		//$data['year']	= postProcessYear($currentsong['Date']);
		$y = postProcessYear($currentsong['Date']);
		$data['year'] = $y == 'NULL' ? '' : $y;
		$data['genre']	= (string) trim($currentsong['Genre']);
		if (empty($data['genre'])) $data['genre'] = '&nbsp;';
		$query = mysqli_query($db,'SELECT genre, genre_id FROM genre WHERE genre = "' . $data['genre'] . '" LIMIT 1');
		if (mysqli_num_rows($query) > 0) {
			$genre = mysqli_fetch_assoc($query);
			$data['genres'] = parseMultiGenreId($genre['genre_id']);
		}
		else {
			$data['genre_id']	= (string) '-1';
			$data['genres'] = parseMultiGenre(trim($currentsong['Genre']));
		}
		if ($data['isStream'] == 'true')
			$data['audio_dataformat']	= (string) 'Stream';
		else
			$data['audio_dataformat']	= (string) strtoupper(pathinfo($currentsong['file'], PATHINFO_EXTENSION));
		$data['audio_bits_per_sample']	= (string) $audio[1];
		$data['audio_sample_rate']	= (string) $audio[0];
		$data['audio_profile']	= (string) $status['bitrate'] . ' kbps';
		//if ($currentsong['Pos']) {
			$data['number']	= (string) ($currentsong['Pos'] + 1 . '. ');
		//}
		//else {
		//	$data['number']	= '';
		//}
		//$times = explode(":",$status['time']);
		$data['miliseconds']	= $times[1] * 1000;
		//$data['miliseconds']	= ($currentsong['Time'] * 1000);
		$data['other_track_version']	= (boolean) false;
		$data['comment']	= (string) '';
		$data['track_id']	= (string) '';
		$data['relative_file']	= (string) $relative_file;
		/* $data['inFavorite'] = (boolean) '';
		$data['onBlacklist'] = (boolean) ''; */
		$data['dr']	= (string) '';
		$data['album_dr']	= (string) '';
		//$data['title_core'] = $title;
		$data['title_core'] = findCoreTrackTitle($title);
	}
	
	$track_id_url = getTrackIdFromUrl($currentsong['file']);
	$inFavorite = false;
	$track_id = get('track_id'); //redefine because $track_id can be changed above
	if (isset($cfg['favorite_id'])) {
		if ($track_id) { //track_id is set only for local files and Tidal streams
			if (isTidal($track_id)) {
				$query = mysqli_query($db,"SELECT track_id, stream_url FROM favoriteitem WHERE (stream_url LIKE '%track_id=" . mysqli_real_escape_string($db, getTidalId($track_id)) . "%' OR stream_url = '" . $cfg['upmpdcli_tidal'] . mysqli_real_escape_string($db,getTidalId($track_id)) . "' OR stream_url = '" . MPD_TIDAL_URL . mysqli_real_escape_string($db,getTidalId($track_id)) . "') AND favorite_id = '" . mysqli_real_escape_string($db, $cfg['favorite_id']) . "' LIMIT 1");
			}
			else { //local files
				$query = mysqli_query($db,"SELECT track_id, stream_url FROM favoriteitem WHERE track_id = '" . $track_id . "' AND favorite_id = '" . mysqli_real_escape_string($db, $cfg['favorite_id']) . "' LIMIT 1");
			}
		}
		elseif ($track_id_url) { //streams from e.g. YouTube
			$query = mysqli_query($db,"SELECT track_id, stream_url FROM favoriteitem WHERE stream_url LIKE '%track_id=" . mysqli_real_escape_string($db, $track_id_url) . "%' AND favorite_id = '" . mysqli_real_escape_string($db, $cfg['favorite_id']) . "' LIMIT 1");
		}
		else { //other streams
			if ($currentsong['file']) {
				$query = mysqli_query($db,"SELECT track_id, stream_url FROM favoriteitem WHERE stream_url = '" . mysqli_real_escape_string($db, $currentsong['file']) . "' AND favorite_id = '" . mysqli_real_escape_string($db, $cfg['favorite_id']) . "' LIMIT 1");
			}
			else {//create fake query to avoid displaying favorite star in NowPlaying
				$query = mysqli_query($db,"SELECT track_id, stream_url FROM favoriteitem WHERE stream_url = 'fake_stream'");
			}
		}
		if (mysqli_num_rows($query) > 0) $inFavorite = true;
	}
	
	$onBlacklist = false;
	if (isset($cfg['blacklist_id'])) {
		if ($track_id) { //track_id is set only for local files and Tidal streams
			if (isTidal($track_id)) {
				$query = mysqli_query($db,"SELECT track_id, stream_url FROM favoriteitem WHERE (stream_url LIKE '%track_id=" . mysqli_real_escape_string($db, getTidalId($track_id)) . "%' OR stream_url = '" . $cfg['upmpdcli_tidal'] . mysqli_real_escape_string($db,getTidalId($track_id)) . "' OR stream_url = '" . MPD_TIDAL_URL . mysqli_real_escape_string($db,getTidalId($track_id)) . "') AND favorite_id = '" . mysqli_real_escape_string($db, $cfg['blacklist_id']) . "' LIMIT 1");
			}
			else { //local files
				$query = mysqli_query($db,"SELECT track_id, stream_url FROM favoriteitem WHERE track_id = '" . $track_id . "' AND favorite_id = '" . mysqli_real_escape_string($db, $cfg['blacklist_id']) . "' LIMIT 1");
			}
		}
		elseif ($track_id_url) { //streams from e.g. YouTube
			$query = mysqli_query($db,"SELECT track_id, stream_url FROM favoriteitem WHERE stream_url LIKE '%track_id=" . mysqli_real_escape_string($db, $track_id_url) . "%' AND favorite_id = '" . mysqli_real_escape_string($db, $cfg['blacklist_id']) . "' LIMIT 1");
		}
		else { //other streams
			if ($currentsong['file']) {
				$query = mysqli_query($db,"SELECT track_id, stream_url FROM favoriteitem WHERE stream_url = '" . mysqli_real_escape_string($db, $currentsong['file']) . "' AND favorite_id = '" . mysqli_real_escape_string($db, $cfg['blacklist_id']) . "' LIMIT 1");
			}
			else {//create fake query to avoid displaying blacklist star in NowPlaying
				$query = mysqli_query($db,"SELECT track_id, stream_url FROM favoriteitem WHERE stream_url = 'fake_stream'");
			}
		}
		if (mysqli_num_rows($query) > 0) $onBlacklist = true;
	}
	
	$data['inFavorite'] = (boolean) $inFavorite;
	$data['onBlacklist'] = (boolean) $onBlacklist;
	$data['isplaying'] = 0;
		if ($status['state'] == 'stop')		$data['isplaying'] = 0;
		if ($status['state'] == 'play')		$data['isplaying'] = 1;
		if ($status['state'] == 'pause')	$data['isplaying'] = 3;
	
	echo safe_json_encode($data);
}



//  +------------------------------------------------------------------------+
//  | updateAddPlay                                                          |
//  +------------------------------------------------------------------------+
function updateAddPlay() {
	global $cfg, $db;
	//authenticate('access_playlist', false, false, true);
	$album_id = get('album_id');
	$data = array();
  
  $where = 'WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '"';
  
  if (isTidal($album_id)) {
    $where = 'WHERE album_id LIKE "tidal_%' . mysqli_real_escape_string($db,getTidalId($album_id)) . '"';
  }
  
  $query = mysqli_query($db,'SELECT COUNT(album_id) as counter, max(time) as time FROM counter ' . $where);
	$played = mysqli_fetch_assoc($query);
	
	$query = mysqli_query($db,'SELECT artist, artist_alphabetic, album, image_id, album.album_id, COUNT(*) AS counter
			FROM counter, album
			WHERE counter.album_id = album.album_id
			GROUP BY album.album_id
			ORDER BY counter DESC, time DESC
			LIMIT 1');
	$max_played = mysqli_fetch_assoc($query);
	
	$popularity = round($played['counter'] / $max_played['counter'] * 100);
	
	$data['played']			= (string) $played['counter'] . " " . (($played['counter'] == 1) ? " time" : " times");
	$data['last_played']	= date("Y-m-d H:i",$played['time']);
	$data['popularity']		= (int) $popularity;
	$data['album_id']		= $album_id;
	//$data['bar_popularity']		= (string) floor($popularity * 1.8);

	echo safe_json_encode($data);
}

?>