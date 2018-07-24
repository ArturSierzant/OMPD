<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2018 Artur Sierzant                            |
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
require_once('include/play.inc.php');

if ($cfg['player_type'] == NJB_MPD) {
	$data = array();
	
	$query1=mysqli_query($db,'SELECT player.player_name as pl, player_host, player_port, player_pass FROM player, session WHERE (sid = BINARY "' . cookie('netjukebox_sid') . '") and player.player_id=session.player_id');
	$session1 = mysqli_fetch_assoc($query1);
	$data['player'] = $session1['pl'];
	//$data['host'] = $session1['player_host'];
	$cfg['player_host'] = $data['host'] = $session1['player_host'];
	$cfg['player_port'] = $session1['player_port'];
	$cfg['player_pass'] = $session1['player_pass'];
	
	$status 	= mpd('status');
	$listpos		= isset($status['song']) ? $status['song'] : 0;
	$file			= mpd('playlist');
	$hash			= md5(implode('<seperation>', $file));
	$listlength		= $status['playlistlength'];
	$bottom = ($listlength > 1) ? ($listlength - 1) : 0;

	
	$playtime = array();
	$track_id = array();
	for ($i=0; $i < $listlength; $i++) {
		//streaming track outside of mpd library	
		$pos = strpos($file[$i],'track_id=');	
		if ($pos === false) {
			$query = mysqli_query($db,'SELECT track.title, track.artist, track.track_artist, track.featuring, track.miliseconds, track.track_id, track.genre, album.genre_id, track.audio_dataformat, track.audio_bits_per_sample, track.audio_sample_rate, track.album_id, track.number, track.track_id, track.year as trackYear FROM track, album WHERE track.album_id=album.album_id AND track.relative_file = "' . 	mysqli_real_escape_string($db,$file[$i]) . '"');
		} 
		else {
			$t_id = substr($file[$i],$pos + 9, 19);
			$query = mysqli_query($db,'SELECT track.title, track.artist, track.track_artist, track.featuring, track.miliseconds, track.track_id, track.genre, album.genre_id, track.audio_dataformat, track.audio_bits_per_sample, track.audio_sample_rate, track.album_id, track.number, track.track_id, track.year as trackYear FROM track, album WHERE track.album_id=album.album_id AND track.track_id = "' . 	mysqli_real_escape_string($db,$t_id) . '"');
		}
		$table_track = mysqli_fetch_assoc($query);
		$track_id[] = (string) $table_track['track_id'];
		
		$is_file_stream = false;
		$pos = strpos($file[$i],'filepath=');
		if ($pos !== false) {
			$is_file_stream = true;
		}
	}
	$data['track_id'] = $track_id;
	$data['hash'] = $hash;
	$data['listpos'] = $listpos;
	$data['listlength'] = $listlength;
	
	echo safe_json_encode($data);	
}

?>