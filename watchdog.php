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


$host = $_GET["host"];
$port = $_GET["port"];
$track_id = $_GET["track_id"];
$logWD = $_GET["logWD"];

$temp = dirname(__FILE__);
$temp = realpath($temp . '/..');
define('NJB_HOME_DIR', str_replace('\\', '/', $temp) . '/');

cliLog('----------------------------------------------');
cliLog('Watchdog for track_id: ' . $track_id);
cliLog('Time: ' . time());

$status = mpd("status",$host,$port);
cliLog('state: ' . $status['state']);
if ($status['state'] != 'play') {
  cliLog('State <> "play" -> exiting function');
}

$song = mpd("currentsong",$host,$port);
$file = $song['file'];
cliLog('file: ' . $file);


$counter = 0;
$maxCounter = 100;

while (strpos($file,$track_id) === false && $counter < $maxCounter){
  $counter++;
  cliLog('Waiting for track to be loaded, attempt: ' . $counter);
  $song = mpd("currentsong",$host,$port);
  $file = $song['file'];
  usleep(100000);
}

cliLog('Checking if stream is playing...');
$status = mpd("status",$host,$port);
cliLog('"time"=' . $status['time']);

if ($status['time'] !== '0:0') {
  cliLog('Stream IS playing, "time"=' . $status['time']);
  cliLog('END of watchdog for track_id: ' . $track_id);
  cliLog('----------------------------------------------');
  return;
}

if ($counter == $maxCounter) {
  cliLog('ERROR: Song not loaded in mpd : ' . $file);
  cliLog('END of watchdog for track_id: ' . $track_id);
  cliLog('----------------------------------------------');
  return;
}

usleep(500000);
cliLog('Checking if stream is playing after 0.5s pause...');
$status = mpd("status",$host,$port);
cliLog('"time"=' . $status['time']);
if ($status['time'] == '0:0' || !$status['time']) {
  cliLog('Stream is NOT playing, "time"=' . $status['time']);
  cliLog('Trying to STOP/PLAY again...');
  mpd("stop",$host,$port);
  //usleep(100000);
  mpd("play",$host,$port);
  //usleep(1000000);
  cliLog('END of watchdog for track_id: ' . $track_id);
  cliLog('----------------------------------------------');
  return;
}

$status = mpd("status",$host,$port);
cliLog('Stream SHOULD be playing, "time"=' . $status['time']);
cliLog('END of watchdog for track_id: ' . $track_id);
cliLog('----------------------------------------------');

return;


//  +------------------------------------------------------------------------+
//  | Music Player Daemon                                                    |
//  +------------------------------------------------------------------------+

function mpd($command,$player_host="",$player_port="") {
	$cfg = array();
  
	$timeout = 3;
  
  $time_start = microtime(true);
	
	$soket = @fsockopen($player_host, $player_port, $error_no, $error_string, $timeout);
	if (!$soket)
		return false; 
	
	$write = @fwrite($soket, $command . "\n");
	if (!$write)
		return false;
	
	$line = trim(fgets($soket, 1024));
	//if (substr($line, 0, 3) == 'ACK')			{fclose($soket); return false;}
	//if (substr($line, 0, 6) != 'OK MPD') 	{fclose($soket); return false;}
	
  $cfg['mpd_version'] = '0.5.0';
	if (preg_match('#([0-9]+\.[0-9]+\.[0-9]+)$#', $line, $matches)) // OK MPD 0.16.0
		$cfg['mpd_version'] = $matches[1];
  
	$array = array();
	
	while (!feof($soket)) {
		$line = trim(@fgets($soket, 2048));
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
//  | Log messages                                                           |
//  +------------------------------------------------------------------------+

function cliLog($message){
  global $logWD;
  if ($logWD == 'true') {
    error_log($message . "\n", 3, NJB_HOME_DIR . 'ompd/tmp/update_log.txt');
  }
}
?>