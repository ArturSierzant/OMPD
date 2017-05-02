<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant                            |
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
$dir = $_GET['dir'];
$limit = $_GET['limit'];
$id = $_GET['id'];
$file = array();
$file_count = 0;

//$dir = str_replace('ompd_ampersand_ompd','&',$dir);
$dir = myDecode($dir);
$dir = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir);

setcookie('random_limit', $limit, time() + (86400 * 30 * 365), "/"); // 86400 = 1 day
setcookie('random_dir', $dir, time() + (86400 * 30 * 365), "/");

$query1 = mysqli_query($db,'SELECT player.player_name as pl, player.player_host as host, player.player_port as port FROM player, session WHERE (sid = BINARY "' . cookie('netjukebox_sid') . '") and player.player_id=session.player_id');
$session1 = mysqli_fetch_assoc($query1);
$player1 = $session1['pl'];
$player1_host = $session1['host'];
$player1_port = $session1['port'];

recursiveScan($dir. '/');

$file_count = count($file);
if ($limit > $file_count) {
	$limit = $file_count;
}

$file = get_random_elements($file,$limit);

mpd('clear', $player1_host, $player1_port);
foreach ($file as $f) {
	$mpdCommand = mpd ('add "' . mpdEscapeChar(str_ireplace($cfg['media_dir'], '', $f)) . '"', $player1_host, $player1_port);
	if ($mpdCommand == 'ACK_ERROR_NO_EXIST') {
			//file not found in MPD database - add stream
			playTo(0,'',$f,'',$player1_host, $player1_port);	
	}
}
mpd('play', $player1_host, $player1_port);

$data['random_files_result'] = 'random_files_OK';
$data['id'] = $id;

echo safe_json_encode($data);	


//get_random_elements by john at brahy dot com
function get_random_elements( $array,$limit = 0 ) {
   
    shuffle($array);

    if ( $limit > 0 ) {
        $array = array_splice($array, 0, $limit);
    }
    return $array;
}

function recursiveScan($dir) {
	global $cfg, $db, $file;
	
	$album_id	= '';
	$filename	= array();
	
	if ($cfg['ignore_media_dir_access_error']) {
		$entries = @scandir($dir);
	}
	else {
		$entries = @scandir($dir) or err($dir);
	}
	
	foreach ($entries as $entry) {
		if ($entry[0] != '.' && in_array($entry, $cfg['directory_blacklist']) === FALSE) {
			if (is_dir($dir . $entry . '/'))
				recursiveScan($dir . $entry . '/');
			else {
				$extension = substr(strrchr($entry, '.'), 1);
				$extension = strtolower($extension);
				if (in_array($extension, $cfg['media_extension'])) {
					$entry = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $entry);
					$dir_d = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $dir);
					//$file[] = str_ireplace($cfg['media_dir'], '', $dir_d . $entry);
					$file[] = $dir_d . $entry;
				}
			}
		}
	}
}

function err($dir){
	global $data;
	$data['random_files_result'] = 'Error reading directory: ' . $dir;
	echo safe_json_encode($data);	
	exit();
}

?>
	
