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
//  | json.php                                                               |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
header('Content-type: application/json');

$action = getpost('action');

if		($action == 'suggestAlbumArtist')	suggestAlbumArtist();
elseif	($action == 'suggestTrackArtist')	suggestTrackArtist();
elseif	($action == 'suggestTrackTitle')	suggestTrackTitle();
elseif	($action == 'suggestAlbumTitle')	suggestAlbumTitle();
elseif	($action == 'loginStage1')			loginStage1();
else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');
exit();




//  +------------------------------------------------------------------------+
//  | Suggest album artist                                                   |
//  +------------------------------------------------------------------------+
function suggestAlbumArtist() {
	global $cfg, $db;
	authenticate('access_media', false, false, true);
	$artist = get('artist');
	
	if ($artist == '')
		exit('[""]');
		
	$query = mysqli_query($db, 'SELECT artist_alphabetic FROM album
		WHERE artist_alphabetic LIKE "%' . mysqli_real_escape_like($artist) . '%"
		OR artist LIKE "%' . mysqli_real_escape_like($artist) . '%"
		OR artist SOUNDS LIKE "' . mysqli_real_escape_string($db, $artist) . '"
		GROUP BY artist_alphabetic ORDER BY artist_alphabetic LIMIT ' . (int) $cfg['autosuggest_limit']);
	
	$data = array();
	while ($album = mysqli_fetch_assoc($query))
		$data[] = (string) $album['artist_alphabetic'];
	
	echo safe_json_encode($data);
}




//  +------------------------------------------------------------------------+
//  | Suggest track artist                                                   |
//  +------------------------------------------------------------------------+
function suggestTrackArtist() {
	global $cfg, $db;
	authenticate('access_media', false, false, true);
	$artist = get('artist');
	
	if ($artist == '')
		exit('[""]');
		
	$query = mysqli_query($db, 'SELECT artist FROM track
		WHERE artist LIKE "%' . mysqli_real_escape_like($artist) . '%"
		OR artist SOUNDS LIKE "' . mysqli_real_escape_string($db, $artist) . '"
		GROUP BY artist ORDER BY artist LIMIT ' . (int) $cfg['autosuggest_limit']);
	
	$data = array();
	while ($album = mysqli_fetch_assoc($query))
		$data[] = (string) $album['artist'];
	
	echo safe_json_encode($data);
}




//  +------------------------------------------------------------------------+
//  | Suggest track title                                                    |
//  +------------------------------------------------------------------------+
function suggestTrackTitle() {
	global $cfg, $db;
	authenticate('access_media', false, false, true);
	$title = get('title');
	
	if ($title == '')
			exit('[""]');
		
	$query = mysqli_query($db, 'SELECT title FROM track
		WHERE title LIKE "%' . mysqli_real_escape_like($title) . '%"
		OR title SOUNDS LIKE "' . mysqli_real_escape_string($db, $title) . '"
		GROUP BY title ORDER BY title LIMIT ' . (int) $cfg['autosuggest_limit']);
	
	$data = array();
	while ($track = mysqli_fetch_assoc($query))
		$data[] = (string) $track['title'];
		
	echo safe_json_encode($data);
}


//  +------------------------------------------------------------------------+
//  | Suggest album title                                                    |
//  +------------------------------------------------------------------------+
function suggestAlbumTitle() {
	global $cfg, $db;
	authenticate('access_media', false, false, true);
	$title = get('title');
	
	if ($title == '')
			exit('[""]');
		
	$query = mysqli_query($db, 'SELECT album FROM album
		WHERE album LIKE "%' . mysqli_real_escape_like($title) . '%"
		OR album SOUNDS LIKE "' . mysqli_real_escape_string($db, $title) . '"
		GROUP BY album ORDER BY album LIMIT ' . (int) $cfg['autosuggest_limit']);
	
	$data = array();
	while ($track = mysqli_fetch_assoc($query))
		$data[] = (string) $track['album'];
		
	echo safe_json_encode($data);
}







//  +------------------------------------------------------------------------+
//  | Login stage 1                                                          |
//  +------------------------------------------------------------------------+
function loginStage1() {
	global $cfg, $db;
	header('Expires: Mon, 9 Oct 2000 18:00:00 GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
		
	$sid		= cookie('netjukebox_sid');
	$username	= post('username');
	$sign		= post('sign');
	
	$query		= mysqli_query($db, 'SELECT seed FROM user WHERE username = "' . mysqli_real_escape_string($db, $username) . '"');
	$user 		= mysqli_fetch_assoc($query);
	
	$query		= mysqli_query($db, 'SELECT ip, seed, sign FROM session WHERE sid = BINARY "' . mysqli_real_escape_string($db, $sid) . '"');
	$session	= mysqli_fetch_assoc($query);
	
	if ($session['ip'] == '')
		message(__FILE__, __LINE__, 'error', '[b]Login failed[/b][br]netjukebox requires cookies to login.[br]Enable cookies in your browser and try again.[br][url=index.php][img]small_login.png[/img]login[/url]');
	
	if ($session['ip'] != $_SERVER['REMOTE_ADDR'])
		message(__FILE__, __LINE__, 'error', '[b]Login failed[/b][br]Unexpected IP address[br][url=index.php][img]small_login.png[/img]login[/url]');
	
	if (hmacsha1($cfg['server_seed'], $session['sign']) == $sign) {
		$sign = randomKey();
		mysqli_query($db, 'UPDATE session
			SET	sign		= "' . mysqli_real_escape_string($db, $sign) . '",
			pre_login_time	= ' . (string) round(microtime(true) * 1000) . '
			WHERE sid		= BINARY "' . mysqli_real_escape_string($db, $sid) . '"');
	}
	else {
		// login will fail!
		$sign = randomKey();
	}
	
	// Always calculate fake seed to prevent script execution time differences
	$fake_seed		= substr(hmacsha1($cfg['server_seed'], $username . 'NeZlFgqDoh9hc-BkczryQFIcpoBng3I_vXaWtOKS'), 0, 30);
	$fake_seed		.= substr(hmacsha1($cfg['server_seed'], $username . 'g-FE6H0MJ1n0lNo2D7XLachV8WE-xmEcwsXNZqlQ'), 0, 30);
	$fake_seed		= base64_encode(pack('H*', $fake_seed));
	$fake_seed		= str_replace('+', '-', $fake_seed); // modified Base64 for URL
	$fake_seed		= str_replace('/', '_', $fake_seed);
		
	$data = array();
	$data['user_seed']		= ($user['seed'] == '') ? $fake_seed : $user['seed'];
	$data['session_seed']	= $session['seed'];
	$data['sign']			= $sign;
	echo safe_json_encode($data);
}
?>