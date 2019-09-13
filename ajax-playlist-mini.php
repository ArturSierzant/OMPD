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
	
	
		//track not found in OMPD DB - take info from MPD, unless this is a stream of file
		if (!isset($table_track['artist']) && !$is_file_stream) {
			$tidalTrack = '';
			$playlistinfo = mpd('playlistinfo ' . $i);
			if (strpos($playlistinfo['file'],'ompd_title=') !== false){
				//stream from Youtube
				$parts = parse_url($playlistinfo['file']);
				parse_str($parts['query'], $query);
				$table_track['title'] = urldecode($query['ompd_title']);
				$table_track['album'] = urldecode($query['ompd_webpage']);
				$playlistinfo['Time'] = (int)urldecode($query['ompd_duration']);
			}
			elseif (strpos($playlistinfo['file'],'tidal://') !== false || strpos($playlistinfo['file'],$cfg['upmpdcli_tidal']) !== false || strpos($playlistinfo['file'],TIDAL_TRACK_STREAM_URL) !== false) {
				//stream from Tidal unrecognized by mpd
				/* $split = explode("/", $playlistinfo['file']);
				$tidalTrackId = $split[count($split)-1]; */
				$tidalTrackId = getTidalId($playlistinfo['file']);
				$track_id[$i] = 'tidal_' . $tidalTrackId;
				$query = mysqli_query($db, "SELECT tidal_track.title, tidal_track.artist, tidal_track.seconds, tidal_track.number, tidal_track.genre_id, tidal_album.album, tidal_album.album_date, tidal_album.album_id FROM tidal_track LEFT JOIN tidal_album ON tidal_track.album_id = tidal_album.album_id WHERE track_id = '" . $tidalTrackId . "' LIMIT 1");
				$tidalTrack = mysqli_fetch_assoc($query);
				
				if ($tidalTrack) {
					$table_track['title'] = $tidalTrack['title'];
					$table_track['track_artist'] = $tidalTrack['artist'];
					$table_track['album'] = $tidalTrack['album'];
					$table_track['miliseconds'] = ((int) $tidalTrack['seconds']) * 1000;
					$table_track['number'] = $tidalTrack['number'];
					$album_date = new DateTime($tidalTrack['album_date']);
					$table_track['trackYear'] = $album_date->format('Y');
					$table_track['track_id'] = 'tidal_' . $tidalTrack['album_id'];
				}
				else {
					$table_track['title'] = 'Tidal track_id ' . $tidalTrackId;
					$table_track['track_artist'] = "";
					$table_track['album'] = "";
				}
			}
			else {
				if (isset($playlistinfo['Artist'])) 
					$table_track['track_artist']	= $playlistinfo['Artist'];
				/* else 
					$table_track['track_artist']	= basename($playlistinfo['file']); */
				
				if (isset($playlistinfo['Name'])) 
					$table_track['title']	= $playlistinfo['Name'];
				else if (isset($playlistinfo['Title']))
					$table_track['title']	= $playlistinfo['Title'];
				else
					$table_track['title']	= basename($playlistinfo['file']);
				
				if (isset($playlistinfo['Album']))
					$table_track['album']	= $playlistinfo['Album'];
				else 
					$table_track['album']	= $playlistinfo['file'];
			}
			if (!$tidalTrack) {
				$table_track['number'] = $playlistinfo['Pos'] + 1;
				$table_track['trackYear'] = $playlistinfo['Date'];
				$table_track['genre'] = $playlistinfo['Genre'];
				$album_genres = parseMultiGenre($table_track['genre']);
				$table_track['miliseconds'] = $playlistinfo['Time'] * 1000;
			}
		}
		//this is stream of a file
		elseif ($is_file_stream) {
			//TODO: take info from file using getid3
			$playlistinfo = mpd('playlistinfo ' . $i);
			$table_track['number'] = $playlistinfo['Pos'] + 1;
			$filepath = substr($file[$i],$pos + 9, strlen($file[$i]) - $pos);
			$filepath = urldecode($filepath);
			$table_track['title'] = basename($filepath);
			$pos = strpos($filepath, $table_track['title']);
			$table_track['album'] = substr($filepath, 0, $pos);
		}
	}
	$data['track_id'] = $track_id;
	$data['hash'] = $hash;
	$data['listpos'] = $listpos;
	$data['listlength'] = $listlength;
	
	echo safe_json_encode($data);	
}

?>