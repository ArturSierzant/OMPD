<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2018 Artur Sierzant                            |
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
//  | play.inc.php                                                           |
//  +------------------------------------------------------------------------+
if (PHP_SAPI == 'cli' && isset($cfg['player_id']) == false)
	$cfg['player_id'] = 0;

/* $query=mysqli_query($db,'SELECT player_id FROM player, session WHERE (sid = BINARY "' . cookie('netjukebox_sid') . '") and player.player_id=session.player_id');
$player = mysqli_fetch_assoc($query);

if ($player['player_id'] != $cfg['player_id'] && mysqli_num_rows($query) > 0) {
	$cfg['player_id'] = $cfg['player_id'];
}
 */
$query = mysqli_query($db,'SELECT player_name, player_type, player_host, player_port, player_pass, media_share, player_id
	FROM player
	WHERE player_id = ' . mysqli_real_escape_string($db,$cfg['player_id']));
if (mysqli_num_rows($query) == 0) {
	$query = mysqli_query($db,'SELECT player_name, player_type, player_host, player_port, player_pass, media_share, player_id
		FROM player
		ORDER BY player_name');
}
if (mysqli_num_rows($query) == 0) {
	mysqli_query($db,'INSERT INTO player (player_name, player_type, player_host, player_port, player_pass, media_share)
		VALUES (
		"Music Player Daemon (default)",
		"2",
		"127.0.0.1",
		"6600",
		"",
		"")');
	$query = mysqli_query($db,'SELECT player_name, player_type, player_host, player_port, player_pass, media_share, player_id
		FROM player');
}
$player = mysqli_fetch_assoc($query);
$cfg['player_name']	= $player['player_name'];
$cfg['player_type']	= $player['player_type'];
$cfg['player_host']	= $player['player_host'];
$cfg['player_port']	= $player['player_port'];
$cfg['player_pass']	= $player['player_pass'];
$cfg['media_share']	= $player['media_share'];
$cfg['player_id']	= $player['player_id'];




//  +------------------------------------------------------------------------+
//  | httpQ                                                                  |
//  +------------------------------------------------------------------------+
function httpq($action, $argument = false) {
	global $cfg;
	
	$request =  'GET /' . $action . '?p=' . rawurldecode($cfg['player_pass']) . (($argument) ? '&' . $argument : '') . ' HTTP/1.0' . "\r\n\r\n";
	
	$soket = @fsockopen($cfg['player_host'], $cfg['player_port'], $error_no, $error_string, 1) or message(__FILE__, __LINE__, 'error', '[b]Winamp httpQ error[/b][br]Failed to connect to: ' . $cfg['player_host'] . ':' . $cfg['player_port'] . '[br]' . $error_string);
	@fwrite($soket, $request) or message(__FILE__, __LINE__, 'error', '[b]Winamp httpQ error[/b][br]Failed to write to: ' . $cfg['player_host'] . ':' . $cfg['player_port']);
	$content = stream_get_contents($soket);
	fclose($soket);
	
	$temp = explode("\r\n\r\n", $content, 2);
	if (isset($temp[1])) {
		$header = $temp[0];
		$content = $temp[1];
		
		$header_array = explode("\r\n", $header);
		foreach ($header_array as $value) {
			if (preg_match('#^Server: Winamp httpQ.+?([0-9]\.[0-9])#', $value, $match) && $match[1] < 3.1)
				message(__FILE__, __LINE__, 'error', '[b]Winamp httpQ error[/b][br]netjukebox requires httpQ 3.1 or higher[/b][br]Now httpQ ' . $match[1] . ' is running on: ' . $cfg['player_host'] . ':' . $cfg['player_port']);
		}
	}	
	return $content;
}




//  +------------------------------------------------------------------------+
//  | videoLAN                                                               |
//  +------------------------------------------------------------------------+
function vlc($command) {
	global $cfg;
	
	$request = 'GET /requests/status.xml?command=' . $command . ' HTTP/1.1' . "\r\n";
	$request .= 'Connection: Close' . "\r\n\r\n";
	
	$soket = @fsockopen($cfg['player_host'], $cfg['player_port'], $error_no, $error_string, 1) or message(__FILE__, __LINE__, 'error', '[b]videoLAN error[/b][br]Failed to connect to: ' . $cfg['player_host'] . ':' . $cfg['player_port'] . '[br]' . $error_string);
	@fwrite($soket, $request) or message(__FILE__, __LINE__, 'error', '[b]videoLAN error[/b][br]Failed to write to: ' . $cfg['player_host'] . ':' . $cfg['player_port']);
	$content = stream_get_contents($soket);
	fclose($soket);
	
	$temp = explode("\r\n\r\n", $content, 2);
	if (isset($temp[1])) {
		$header = $temp[0];
		$content = $temp[1];
	}
	return $content;
}




//  +------------------------------------------------------------------------+
//  | Music Player Daemon                                                    |
//  +------------------------------------------------------------------------+
function mpd($command,$player_host="",$player_port="") {
	global $cfg;
	if ($player_host=="" && $player_port==""){
		$player_host = $cfg['player_host'];
		$player_port = $cfg['player_port'];
	}
	
	$soket = @fsockopen($player_host, $player_port, $error_no, $error_string, 3) or message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]Failed to connect to: ' . $cfg['player_host'] . ':' . $cfg['player_port'] . '[br]' . $error_string . '[br]Check player settings:[br] [url=config.php?action=playerProfile]config.php?action=playerProfile[/url]');
	if ($cfg['mpd_password'] != '') {
		@fwrite($soket, "password " . $cfg['mpd_password'] . "\n") or message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]Failed to write to: ' . $cfg['player_host'] . ':' . $cfg['player_port']);
	}
	@fwrite($soket, $command . "\n") or message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]Failed to write to: ' . $cfg['player_host'] . ':' . $cfg['player_port']);
	
	$line = trim(fgets($soket, 1024)); 
	if (substr($line, 0, 3) == 'ACK')		{fclose($soket); message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]Error: ' . $line . '[br]Command: ' . $command);}
	if (substr($line, 0, 6) != 'OK MPD') 	{fclose($soket); message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]No valid server detected at: ' . $cfg['player_host'] . ':' . $cfg['player_port']);}
	
	$cfg['mpd_version'] = '0.5.0';
	if (preg_match('#([0-9]+\.[0-9]+\.[0-9]+)$#', $line, $matches)) // OK MPD 0.16.0
		$cfg['mpd_version'] = $matches[1];
	
	$array = array();
	if ($cfg['mpd_password'] != '') {
		while (!feof($soket)) {
			$line = trim(@fgets($soket, 1024));
			if (substr($line, 0, 2) == 'OK') {
				break;
			}
		}
	};
	while (!feof($soket)) {
		$line = trim(@fgets($soket, 1024));
		if (substr($line, 0, 3) == 'ACK') {
			//file doesn't exist or no access to file
			if (substr($line, 0, 8) == 'ACK [50@' || substr($line, 0, 7) == 'ACK [4@') {
				fclose($soket);
				return 'ACK_ERROR_NO_EXIST';
			}
			elseif (substr($line, 0, 8) == 'ACK [5@0') {
				fclose($soket);
				return 'ACK_ERROR_UNKNOWN';
			}
			else {
				fclose($soket); 
				message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]Error: ' . $line . '[br]Command: ' . $command);
			}
		}
		if (substr($line, 0, 2) == 'OK') {
			fclose($soket);
			if ($command == 'status' && isset($array['time']) && version_compare($cfg['mpd_version'], '0.16.0', '<')) {
				list($seconds, $dummy) = explode(':', $array['time'], 2);
				$array['elapsed'] = $seconds;
			}
			elseif (strpos($command,'load') !== false) {
				$array[] = 'add_OK';
			}
			return $array;
		}
		if ($command == 'playlist' && version_compare($cfg['mpd_version'], '0.16.0', '<')) {
			// 0:directory/filename.extension
			list($key, $value) = explode(':', $line, 2);
			$array[] = iconv('UTF-8', NJB_DEFAULT_CHARSET, $value);
		}
		elseif ($command == 'playlist' || $command == 'playlist') {
			// 0:file: directory/filename.extension
			list($key, $value) = explode(': ', $line, 2);
			$array[] = iconv('UTF-8', NJB_DEFAULT_CHARSET, $value);
		}
		elseif ($command == 'playlistinfo') {
			// 0:file: directory/filename.extension
			list($key, $value) = explode(': ', $line ,2);
			$array[$key][] = iconv('UTF-8', NJB_DEFAULT_CHARSET, $value);
		}
		elseif ($command == 'listplaylists' || strpos($command,'listplaylistinfo') !== false) {
			// playlist: pl_1 Last-Modified: 2018-06-08T08:20:07Z
			list($key, $value) = explode(': ', $line);
			$array[$key][] = iconv('UTF-8', NJB_DEFAULT_CHARSET, $value);
		}
		
		else {
			// name: value
			list($key, $value) = explode(': ', $line, 2);
			//$array[$key] = $value;	
			$array[$key] = iconv('UTF-8', NJB_DEFAULT_CHARSET, $value);	
			
		}
	}    
	fclose($soket);
	message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]Connection unexpectedly closed');
}




//  +------------------------------------------------------------------------+
//  | Music Player Daemon                                                    |
//  +------------------------------------------------------------------------+
function mpd_OK($command,$player_host="",$player_port="") {
	global $cfg;
	
	if ($player_host=="" && $player_port==""){
		$player_host = $cfg['player_host'];
		$player_port = $cfg['player_port'];
	}
	
	$soket = @fsockopen($player_host, $player_port, $error_no, $error_string, 3) or message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]Failed to connect to: ' . $cfg['player_host'] . ':' . $cfg['player_port'] . '[br]' . $error_string);
	if ($cfg['mpd_password'] != '') {
		@fwrite($soket, "password " . $cfg['mpd_password'] . "\n") or message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]Failed to write to: ' . $cfg['player_host'] . ':' . $cfg['player_port']);
	}
	@fwrite($soket, $command . "\n") or message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]Failed to write to: ' . $cfg['player_host'] . ':' . $cfg['player_port']);
	
	$line = trim(fgets($soket, 1024)); 
	if (substr($line, 0, 3) == 'ACK')		{fclose($soket); message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]Error: ' . $line . '[br]Command: ' . $command);}
	if (substr($line, 0, 6) != 'OK MPD') 	{fclose($soket); message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]No valid server detected at: ' . $cfg['player_host'] . ':' . $cfg['player_port']);}
	
	$cfg['mpd_version'] = '0.5.0';
	if (preg_match('#([0-9]+\.[0-9]+\.[0-9]+)$#', $line, $matches)) // OK MPD 0.16.0
		$cfg['mpd_version'] = $matches[1];
	
	$array = array();
	if ($cfg['mpd_password'] != '') {
		while (!feof($soket)) {
			$line = trim(@fgets($soket, 1024));
			if (substr($line, 0, 2) == 'OK') {
				break;
			}
		}
	};
	while (!feof($soket)) {
		$line = trim(@fgets($soket, 1024));
		if (substr($line, 0, 3) == 'ACK') {
			fclose($soket);
			message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]Error: ' . $line . '[br]Command: ' . $command);
		}
		if (substr($line, 0, 2) == 'OK') {
			fclose($soket);
			if ($command == 'status' && isset($array['time']) && version_compare($cfg['mpd_version'], '0.16.0', '<')) {
				list($seconds, $dummy) = explode(':', $array['time'], 2);
				$array['elapsed'] = $seconds;
			}
			return $array;
		}
		if ($command == 'playlist' && version_compare($cfg['mpd_version'], '0.16.0', '<')) {
			// 0:directory/filename.extension
			list($key, $value) = explode(':', $line, 2);
			$array[] = iconv('UTF-8', NJB_DEFAULT_CHARSET, $value);
		}
		elseif ($command == 'playlist' || $command == 'playlist') {
			// 0:file: directory/filename.extension
			list($key, $value) = explode(': ', $line, 2);
			$array[] = iconv('UTF-8', NJB_DEFAULT_CHARSET, $value);
		}
		elseif ($command == 'playlistinfo') {
			// 0:file: directory/filename.extension
			list($key, $value) = explode(': ', $line ,2);
			$array[$key][] = iconv('UTF-8', NJB_DEFAULT_CHARSET, $value);
		}
		else {
			// name: value
			list($key, $value) = explode(': ', $line, 2);
			//$array[$key] = $value;	
			$array[$key] = iconv('UTF-8', NJB_DEFAULT_CHARSET, $value);	
		}
	}    
	fclose($soket);
	message(__FILE__, __LINE__, 'error', '[b]Music Player Daemon error[/b][br]Connection unexpectedly closed');
}




//  +------------------------------------------------------------------------+
//  | Music Player Daemon silent                                             |
//  +------------------------------------------------------------------------+
function mpdSilent($command,$player_host="",$player_port="") {
	global $cfg;
	
	$time_start = microtime(true);
	if ($player_host=="" && $player_port==""){
		$player_host = $cfg['player_host'];
		$player_port = $cfg['player_port'];
	}
	
	//$soket = @fsockopen($cfg['player_host'], $cfg['player_port'], $error_no, $error_string, 3);
	$soket = @fsockopen($player_host, $player_port, $error_no, $error_string, 3);
	if (!$soket)
		return false; 
	
	if ($cfg['mpd_password'] !== '') {
		@fwrite($soket, "password " . $cfg['mpd_password'] . "\n");
	}
	
	$write = @fwrite($soket, $command . "\n");
	if (!$write)
		return false;
	
	$line = trim(fgets($soket, 1024));
	if (substr($line, 0, 3) == 'ACK')			{fclose($soket); return false;}
	//if (substr($line, 0, 6) != 'OK NJB_MPD') 	{fclose($soket); return false;}
	if (substr($line, 0, 6) != 'OK MPD') 	{fclose($soket); return false;}
	
	$cfg['mpd_version'] = '0.5.0';
	if (preg_match('#([0-9]+\.[0-9]+\.[0-9]+)$#', $line, $matches)) // OK MPD 0.16.0
		$cfg['mpd_version'] = $matches[1];
	
	$array = array();
	
	if ($cfg['mpd_password'] !== '') {
		while (!feof($soket)) {
			$line = trim(@fgets($soket, 1024));
			if (substr($line, 0, 2) == 'OK') {
				break;
			}
		}
	};
	
	while (!feof($soket)) {
		$line = trim(@fgets($soket, 1024));
		if (substr($line, 0, 3) == 'ACK') {
			//file doesn't exists or no access to file
			if (substr($line, 0, 8) == 'ACK [50@' || substr($line, 0, 7) == 'ACK [4@') {
				fclose($soket);
				return 'ACK_ERROR_NO_EXIST';
			}
			else {
				fclose($soket);
				return false;
			}
		}
		if (substr($line, 0, 2) == 'OK') {
			fclose($soket);
			if ($command == 'status' && isset($array['time']) && version_compare($cfg['mpd_version'], '0.16.0', '<')) {
				list($seconds, $dummy) = explode(':', $array['time'], 2);
				$array['elapsed'] = $seconds;
			}
			$time_end = microtime(true);
			$cfg['delay'] = round(($time_end - $time_start) * 1000);
			return $array;
		}
		if ($command == 'playlist' && version_compare($cfg['mpd_version'], '0.16.0', '<')) {
			// 0:directory/filename.extension
			list($key, $value) = explode(':', $line, 2);
			$array[] = iconv('UTF-8', NJB_DEFAULT_CHARSET, $value);	
		}
		elseif ($command == 'playlist') {
			// 0:file: directory/filename.extension
			list($key, $value) = explode(': ', $line, 2);
			$array[] = iconv('UTF-8', NJB_DEFAULT_CHARSET, $value);
		}
		else {
			// name: value
			list($key, $value) = explode(': ', $line, 2);
			$array[$key] = $value;	
		}    
	}    
	fclose($soket);
	return false;
}




//  +------------------------------------------------------------------------+
//  | Music Player Daemon update                                             |
//  +------------------------------------------------------------------------+
function mpdUpdate($dir_to_scan = '') {
	global $cfg, $db;
	// Store current player settings
	$temp['player_host'] = $cfg['player_host'];
	$temp['player_port'] = $cfg['player_port'];
	$temp['player_pass'] = $cfg['player_pass'];
	
	if ($dir_to_scan != '') {
		$dir_to_scan = '"' . $dir_to_scan . '"';
	}
	
	// Music Player Daemon update
	$query = mysqli_query($db,'SELECT player_host, player_port, player_pass
		FROM player
		WHERE player_host != ""
		AND player_port != ""
		AND player_type = ' . NJB_MPD);
	while ($player = mysqli_fetch_assoc($query)) {
		$cfg['player_host'] = $player['player_host'];
		$cfg['player_port'] = $player['player_port'];
		$cfg['player_pass'] = $player['player_pass'];
		mpdSilent('update ' . $dir_to_scan);
		//mpd('update');
	}
	
	// Restore player settings
	$cfg['player_host'] = $temp['player_host'];
	$cfg['player_port'] = $temp['player_port'];
	$cfg['player_pass'] = $temp['player_pass'];
}




//  +------------------------------------------------------------------------+
//  | Play to                                                                |
//  +------------------------------------------------------------------------+
function playTo($insPos, $track_id = '', $filepath = '', $dirpath = '', $player_host = '', $player_port = '') {
	global $cfg, $db;
	
	$data = array();
	$stream_id		= -1;
	$track_id			= get('track_id') ? get('track_id') : $track_id;
	$album_id			= get('album_id');
	$disc					= get('disc');
	$favorite_id	= get('favorite_id');
	$random				= get('random');
	$sid					= get('sid');
	$player_id		= get('player_id');
	$filepath			= get('filepath') ? get('filepath') : $filepath;
	$in_media_dir	= get('in_media_dir');
	$dirpath			= get('dirpath') ? get('dirpath') : $dirpath;
	
	$data['playToResult'] = "playTo_Error";
	$data['player_id'] = $player_id;
	
	/* $playerQuery = mysqli_query($db,'SELECT * FROM player WHERE player_id="' . $player_id. '"' );
	$player = mysqli_fetch_assoc($playerQuery);
	$player_host		= $player['player_host'];
	$player_port		= $player['player_port'];
	$player_pass		= $player['player_pass']; */
	
	if ($sid) {
		// Share stream
		header('Expires: Mon, 9 Oct 2000 18:00:00 GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		
		mysqli_query($db,'UPDATE share_stream SET
			ip			= "' . mysqli_real_escape_string($db,$_SERVER['REMOTE_ADDR']) . '"
			WHERE sid	= BINARY "' . mysqli_real_escape_string($db,$sid) . '"
			AND ip		= ""');
		
		$query = mysqli_query($db,'SELECT album_id, stream_id
			FROM share_stream
			WHERE sid = BINARY "' . mysqli_real_escape_string($db,$sid) . '"
			AND ip = "' . mysqli_real_escape_string($db,$_SERVER['REMOTE_ADDR']) . '"
			AND expire_time > ' . (int) time());
		$share_stream = mysqli_fetch_assoc($query);
		
		if ($share_stream == false || $cfg['album_share_stream'] == false)
			message(__FILE__, __LINE__, 'error', '[b]Stream failed[/b][br]Authentication failed or share stream is disabled');
		
		$album_id 	= $share_stream['album_id'];
		$stream_id	= $share_stream['stream_id'];
	}
	else {
		// Common stream
		//authenticate('access_stream');
		//$sid 			= cookie('netjukebox_sid');
	}
		
	if ($sid) {
		$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track_id
			FROM track, share_stream
			WHERE share_stream.sid	= "' . mysqli_real_escape_string($db,$sid) . '" AND
			share_stream.album_id	= track.album_id
			ORDER BY relative_file');
	}
	elseif ($track_id && $filepath == '' && $dirpath == '') {
		$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track_id FROM track WHERE track_id = "' . mysqli_real_escape_string($db,$track_id) . '"');
	}
	elseif ($album_id) {
		if ($insPos) {
			$orderBy = 'disc DESC, number DESC, relative_file DESC';
		}
		else {
			$orderBy = 'disc, number, relative_file';
		}
		$part_of_set = '';
		if ($disc) {
			$part_of_set = ' AND disc = ' . $disc . ' ';
		}
		
		$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track_id FROM track WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '"' . $part_of_set . ' ORDER BY ' . $orderBy);
	}
	elseif ($favorite_id) {
		$query = mysqli_query($db,'SELECT stream
			FROM favorite
			WHERE favorite_id = ' . (int) $favorite_id . '
			AND stream = 1');
		if (mysqli_fetch_row($query))
			streamPlaylist($favorite_id);
		
		$query = mysqli_query($db,'SELECT track.artist, track.title, track.relative_file, track.miliseconds, track.audio_bitrate, track.track_id
			FROM track, favoriteitem
			WHERE favoriteitem.track_id = track.track_id 
			AND favorite_id = "' . mysqli_real_escape_string($db,$favorite_id) . '"
			ORDER BY position');
	}
	/* elseif ($random == 'database') {
		$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track.track_id
			FROM track, random
			WHERE random.sid	= "' . mysqli_real_escape_string($db,$cfg['sid']) . '" AND
			random.track_id		= track.track_id
			ORDER BY position');
	}
	elseif ($random == 'new') {
		//$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track_id FROM track WHERE track_id = "' . mysqli_real_escape_string($db,$track_id) . '"');
		$blacklist = explode(',', $cfg['random_blacklist']);
		$blacklist = '"' . implode('","', $blacklist) . '"';
		$query = mysqli_query($db,'SELECT track.artist, title, relative_file, miliseconds, audio_bitrate, track_id
			FROM track, album
			WHERE (genre_id = "" OR genre_id NOT IN (' . $blacklist . ')) AND
			audio_dataformat != "" AND
			video_dataformat = "" AND
			track.album_id = album.album_id
			ORDER BY RAND()
			LIMIT 1');
	} */
	
	elseif ($filepath) {
		$filepath = str_replace('ompd_ampersand_ompd','&',$filepath);
		$filepath = str_replace('ompd_plus_ompd','+',$filepath);
		$url = NJB_HOME_URL . 'stream.php?action=streamTo&stream_id=-1&filepath=' . urlencode($filepath);
		$pos = strpos($filepath,$cfg['media_dir']);
		if ($pos !== false) {
			$filepath = (str_ireplace($cfg['media_dir'],'', $filepath));	
			//check if file is in OMPD database
			$query = mysqli_query($db,'SELECT track_id FROM track WHERE relative_file = "' . mysqli_real_escape_string($db,$filepath) . '"');
			$track = mysqli_fetch_assoc($query);
			//if yes - create url with file_id
			if ($track['track_id']){
				$url = NJB_HOME_URL . 'stream.php?action=streamTo&stream_id=-1&track_id=' . urlencode($track['track_id']);
			}
		}
		
		mpd('addid "' . mpdEscapeChar($url) . '" ' . $insPos, $player_host, $player_port);
		return;
	}
	elseif ($dirpath) {
		$dirpath = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dirpath);
		if (is_dir($dirpath)) {
			if ($handle = opendir($dirpath)) {
				$dirpath = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $dirpath);
				$i = 0;
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						$rows[$i]['data'] = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $file);
						$rows[$i]['dir'] = is_dir(iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dirpath) . "/" . $file);
						$i++;
					}
				}
				closedir($handle);
			}
		}
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Unsupported query string[/b][br]' . $_SERVER['QUERY_STRING']);
			
	if ($stream_id != -1 && isset($cfg['encode_extension'][$stream_id]) == false)
		message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]stream_id');
	
	while ($track = mysqli_fetch_assoc($query)) {
		$extension = substr(strrchr($track['relative_file'], '.'), 1);
		$extension = strtolower($extension);
		if (sourceFile($extension, $track['audio_bitrate'], $stream_id))
			$stream_extension = $extension;
		else
			$stream_extension = $cfg['encode_extension'][$stream_id];
		
		$url = NJB_HOME_URL . 'stream.php?action=streamTo&stream_id=' . $stream_id . '&track_id=' . $track['track_id'] . '&ext=.' . $stream_extension;
		
		mpd('addid "' . mpdEscapeChar($url) . '" ' . $insPos, $player_host, $player_port);
		
	}
	
	
	if ($album_id){
		//updateCounter($album_id, NJB_COUNTER_STREAM);
	}
	
}

?>