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

header('Content-Type: application/json');

ini_set('max_execution_time', 0);
ignore_user_abort(true);

require_once('include/initialize.inc.php');
require_once('include/play.inc.php');

global $cfg, $db;

$action	= get('action');
$response = array();
$response['action'] = $action;
$response['result'] = 'error';

if  ($action == 'on') turnOn();
elseif  ($action == 'off') turnOff();
elseif  ($action == 'status') status();
else echo safe_json_encode($response);

exit();

function status(){
  global $response;
  $response['result'] = isAutoQueueRunning() ? 'running' : 'stopped';
  echo safe_json_encode($response);
  exit();
}

function turnOn(){
  global  $db, $cfg, $response;

  if (isAutoQueueRunning()){
    $response['result'] = 'already running';
    echo safe_json_encode($response);
    exit();
  }

  file_put_contents(AUTO_QUEUE_FILE, getmypid());
  $response['result'] = 'running';
  echo safe_json_encode($response);
  @ob_flush();
  flush();

  while (file_exists(AUTO_QUEUE_FILE)){
    sleep(5);
    $query1 = mysqli_query($db, 'SELECT player_host, player_port, player_pass FROM player');
    $session1 = mysqli_fetch_assoc($query1);
    foreach ($query1 as $row ) {
      $host = $row['player_host'];
      $port = $row['player_port'];
      $pass = $row['player_pass'];
    
      /* $data['player'] = $session1['pl'];
      $cfg['player_host'] = $data['host'] = $session1['player_host'];
      $cfg['player_port'] = $session1['player_port'];
      $cfg['player_pass'] = $session1['player_pass']; */
      
      //$status = mpd('status',$host,$port);
      $status 	= mpdSilent('status',$host,$port);
      cliLog('host: ' . $host);
      cliLog('status: ' . $status);
      if (!$status) {
        continue;
      }
      //$currentsong = mpd('currentsong',$host,$port);
      $listpos = isset($status['song']) ? (int) $status['song'] : 0;
      $totalTracks= (int) $status['playlistlength'];
      if($listpos == $totalTracks - 1) {
        $response['result'] =  addSimilarSongToQueue($host, $port);
        if ($response['result']) {
          file_put_contents(AUTO_QUEUE_FILE, getmypid());
          echo 'song added to ' . $cfg['player_host'];
        }
      }
    }

    /* echo $listpos . '/' . $totalTracks . '<br>';
    @ob_flush();
    flush(); */
  }
}

function turnOff(){
  global $response;
  $response['result'] = 'already stopped';
  if (file_exists(AUTO_QUEUE_FILE)){
    $delFile = unlink(AUTO_QUEUE_FILE);
    if($delFile){
      $response['result'] = 'stopped';
    }
  }
  echo safe_json_encode($response);
  exit();
}
// Function to find and add a similar song to the MPD queue
function addSimilarSongToQueue($host, $port) {
  global $cfg, $db, $t, $response;

  $currentSong = mpd('currentsong',$host,$port);
  
  if (!$currentSong || !isset($currentSong['file'])) {
    cliLog("No song currently playing or unable to get current song info.");
    return false;
  }
  
  $currentSongFile = $currentSong['file'];
  
  if ($cfg['use_tidal']){
    if (isTidal($currentSongFile)) {
      $trackId = getTrackIdFromUrl($currentSongFile);
      cliLog("Tidal track_id: " . $trackId);
      $similarSongs = $t->getSimilarTracks($trackId);
      if(count($similarSongs['data']) > 0){
        $next_track_id = getNextTrackId($similarSongs);
      }
      if(!empty($next_track_id)){
        return addTrackToQueue($next_track_id, $host, $port);
      }
    }
    else{
      $res = $t->searchAll($currentSong['Artist'] . " " . $currentSong['Title]']);
      cliLog("Search result: " . print_r($res,true));
      if(count($res['tracks']['items']) > 0){
        $trackId = $res['tracks']['items'][0]['id'];
        cliLog("Found track in Tidal: track_id=" . $trackId);
        $similarSongs = $t->getSimilarTracks($trackId);
        if(isset($similarSongs['data']) && count($similarSongs['data']) > 0){
          $next_track_id = getNextTrackId($similarSongs);
        }
        else{
          $next_track_id = false;
        }
        if(!empty($next_track_id)){
          return addTrackToQueue($next_track_id, $host, $port);
        }
      }
    }
  }

  $relative_file = str_replace($cfg['media_dir'], '', $currentSongFile);
 
 $similarityConditions = array();

 if (!empty($currentSong['Artist'])) {
   $similarityConditions[] = "artist LIKE '%" . mysqli_real_escape_string($db, $currentSong['Artist']) . "%'";
    //$similarityConditions[] = "composer LIKE '%" . mysqli_real_escape_string($db, $currentSong['Artist']) . "%'";
  }

  if (!empty($currentSong['Genre'])) {
    $similarityConditions[] = "genre = '" . mysqli_real_escape_string($db, $currentSong['Genre']) . "'";
  }
  
  $whereClause = " relative_file != '" . mysqli_real_escape_string($db, $relative_file) . "'";

  if (!empty($currentSong['Date']) && isset($currentSong['Date'])) {
    $whereClause .= " AND year BETWEEN " . mysqli_real_escape_string($db, $currentSong['Date'] - 3) . " AND " . mysqli_real_escape_string($db, $currentSong['Date'] + 3);
  }

  //$whereClause = "";

  if (!empty($similarityConditions)) {
    $whereClause = "WHERE " . $whereClause . " AND (" . implode(" OR ", $similarityConditions) . ")";
  }

  cliLog("file: " . $currentSong['file']);
  $similarSongsQuery = "SELECT * FROM track $whereClause ORDER BY RAND() LIMIT 1";
  cliLog("Query: " . $similarSongsQuery);
  
  $similarSongsResult = mysqli_query($db, $similarSongsQuery);

  if (!$similarSongsResult || mysqli_num_rows($similarSongsResult) == 0) {
    cliLog("No similar songs found.");
    return false;
  }
  cliLog("Found similar song.");
  $similarSong = mysqli_fetch_assoc($similarSongsResult);

  $similarSongFile = $similarSong['relative_file'];

  //if ($mpdResult == 'ACK_ERROR_NO_EXIST') {
    //file not found in MPD database - add stream
    $extension = substr(strrchr($similarSong['relative_file'], '.'), 1);
		$extension = strtolower($extension);
    $stream_id = -1;
		if (sourceFile($extension, $similarSong['audio_bitrate'], $stream_id))
			$stream_extension = $extension;
		else
			$stream_extension = $cfg['encode_extension'][$stream_id];

		$url = NJB_HOME_URL . 'stream.php?action=streamTo&stream_id=' . $stream_id . '&track_id=' . $similarSong['track_id'] . '&ext=.' . $stream_extension;
  //}
  $mpdResult = mpd('add "' . mpdEscapeChar($url) . '"', $host, $port);
  if ($mpdResult === false) {
    cliLog("Error adding similar song to MPD queue.");
    return false;
  }

  cliLog("Added similar song to queue: " . $similarSongFile);
  return true;
}

function getNextTrackId($similarSongs){
  global $cfg, $db, $t, $response;
  $next_track_id = '';
  $next_track_id = $similarSongs['data'][rand(0,count($similarSongs['data'])-1)]['id'];
  $next_track = $t->getTrack($next_track_id);
  $album_id = $next_track['album']['id'];
  cliLog("Tidal album_id: " . $album_id);
  getAlbumFromTidal($album_id);
  getTracksFromTidalAlbum($album_id);
  return $next_track_id;
}

function addTrackToQueue($next_track_id, $host, $port){
  $next_track_url = createStreamUrlMpd("tidal_" . $next_track_id);
  $mpdResult = mpd('add "' . $next_track_url . '"', $host, $port);
  if ($mpdResult === false) {
    cliLog("Error adding similar song to MPD queue.");
    return false;
  }
  cliLog("Added similar song to queue: " . $next_track_id);
  return true;
}

?>