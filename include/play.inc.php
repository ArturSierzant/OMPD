<?php
//  +------------------------------------------------------------------------+
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
if (PHP_SAPI == 'cli' || isset($cfg['player_id']) == false)
	$cfg['player_id'] = 0;

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
	
    if(substr($command, -1) == '/') {
        $command = substr($command, 0, -1);
    }
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
			fclose($soket);
			return false;
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
function mpdUpdate($subfolder="") {
	global $cfg, $db;
	// Store current player settings
	$temp['player_host'] = $cfg['player_host'];
	$temp['player_port'] = $cfg['player_port'];
	$temp['player_pass'] = $cfg['player_pass'];
	
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
		mpdSilent('update '.$subfolder);
		//mpd('update');
	}
	
	// Restore player settings
	$cfg['player_host'] = $temp['player_host'];
	$cfg['player_port'] = $temp['player_port'];
	$cfg['player_pass'] = $temp['player_pass'];
}
?>