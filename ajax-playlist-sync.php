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


require_once('include/initialize.inc.php');
require_once('include/play.inc.php');

global $cfg, $db;
$action = get('action');
$source = get('source');
$dest = get('dest');
$data = array();
$data['action_status'] = false;

$query = mysqli_query($db,'SELECT player_host, player_port	FROM player WHERE player_id = ' . mysqli_real_escape_string($db,$source) . '');

$source_f = mysqli_fetch_assoc($query);

$source_host = $source_f['player_host'];
$source_port = $source_f['player_port'];

$query = mysqli_query($db,'SELECT player_host, player_port	FROM player WHERE player_id = ' . mysqli_real_escape_string($db,$dest) . '');

$dest_f = mysqli_fetch_assoc($query);

$dest_host = $dest_f['player_host'];
$dest_port = $dest_f['player_port'];

$source_playlist = mpdSilent('playlist', $source_host, $source_port);
if (!$source_playlist) {
	echo json_encode($data);
	exit();
}


$cfg['delay'] = 0;
$isDestAlive = mpdSilent('status', $dest_host, $dest_port);
$dest_delay = $cfg['delay'] / 1000;
$dest_mpd_ver = $cfg['mpd_version'];
if (!$isDestAlive) {
	echo json_encode($data);
	exit();
}
if ($action == 'Sync'){
	$action_status = mpdSilent('clear', $dest_host, $dest_port);
	$playlist_len = count($source_playlist);
	for ($i = 0; $i < $playlist_len; $i++){
		$action_status = mpdSilent('add "' . $source_playlist[$i] . '"', $dest_host, $dest_port);
		if ($action_status == 'ACK_ERROR_NO_EXIST'){
			//file not found in MPD database - add stream
			add_stream($source_playlist[$i], $i);	
		}
	}
	$cfg['delay'] = 0;
	$source_status = mpdSilent('status', $source_host, $source_port);
	$source_delay = $cfg['delay'] / 1000;
	$source_mpd_ver = $cfg['mpd_version'];
	$song = $source_status['song'];
	$seek = (float)$source_status['elapsed'];
	$data['seek'] = $seek;
	$data['song'] = $song;
	$data['source_delay'] = $source_delay;
	$data['dest_delay'] = $dest_delay;
	$seek = $seek + $dest_delay + $source_delay;
	if (version_compare($dest_mpd_ver, '0.18.0', '<')) {
		$seek = round($seek);
	}
	$data['seek fin'] = $seek;
	$action_status = mpdSilent('seek ' . $song . ' ' . $seek, $dest_host, $dest_port);
}
elseif ($action == 'Copy') {
	$action_status = mpdSilent('clear', $dest_host, $dest_port);
	$playlist_len = count($source_playlist);
	for ($i = 0; $i < $playlist_len; $i++){
		$action_status = mpdSilent('add "' . $source_playlist[$i] . '"', $dest_host, $dest_port);
		if ($action_status == 'ACK_ERROR_NO_EXIST'){
			//file not found in MPD database - add stream
			add_stream($source_playlist[$i], $i);	
		}
	}
}
elseif ($action == 'Add') {
	$playlist_len = count($source_playlist);
	for ($i = 0; $i < $playlist_len; $i++){
		$action_status = mpdSilent('add "' . $source_playlist[$i] . '"', $dest_host, $dest_port);
		if ($action_status == 'ACK_ERROR_NO_EXIST'){
			//file not found in MPD database - add stream
			$dest_playlist = mpdSilent('playlist', $dest_host, $dest_port);
			if (!$dest_playlist && count($dest_playlist) <> 0) {
				$data['dest_playlist'] = $dest_playlist[0];
				echo json_encode($data);
				exit();
			}
			$dest_playlist_len = count($dest_playlist);
			add_stream($source_playlist[$i], $dest_playlist_len);	
		}
	}
	
}

if(!$action_status) $action_status = true;

$data['source_host']		= $source_host;
$data['dest_host']			= $dest_host;
$data['action_status']		= $action_status;

echo json_encode($data);

function add_stream($source_filepath, $i){
	global $db, $cfg, $dest_host, $dest_port;
	//$source_filepath = $source_playlist[$i];
	$query = mysqli_query($db,'SELECT track_id FROM track WHERE relative_file = "' . mysqli_real_escape_string($db,$source_filepath) . '"');
	$track = mysqli_fetch_assoc($query);
	if ($track['track_id']){
		playTo($i, $track['track_id'], '', '', $dest_host, $dest_port);
	}
	else {
		playTo($i, '', $source_filepath, '', $dest_host, $dest_port);
	}
}
?>