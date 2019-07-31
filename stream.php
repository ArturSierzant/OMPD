<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2019 Artur Sierzant                            |
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
//  | stream.php                                                             |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
require_once('include/stream.inc.php');
require_once('include/cache.inc.php');
require_once('include/play.inc.php');

$action		= get('action');
$album_id	= get('album_id');

if		($action == 'playlist')		playlist();
//elseif	($action == 'playTo')		playTo();
elseif	($action == 'stream')		stream();
elseif	($action == 'streamTo')		streamTo();
elseif	($action == 'shareAlbum')	shareAlbum($album_id);
else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');
exit();




//  +------------------------------------------------------------------------+
//  | Playlist                                                               |
//  +------------------------------------------------------------------------+
function playlist() {
	global $cfg, $db;
	
	$stream_id		= get('stream_id');
	$track_id		= get('track_id');
	$album_id		= get('album_id');
	$favorite_id	= get('favorite_id');
	$random			= get('random');
	$sid			= get('sid');
	
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
		authenticate('access_stream');
	}
		
	if ($sid) {
		$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track_id
			FROM track, share_stream
			WHERE share_stream.sid	= "' . mysqli_real_escape_string($db,$sid) . '" AND
			share_stream.album_id	= track.album_id
			ORDER BY relative_file');
	}
	elseif ($track_id) {
		$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track_id FROM track WHERE track_id = "' . mysqli_real_escape_string($db,$track_id) . '"');
	}
	elseif ($album_id) {
		$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track_id FROM track WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '" ORDER BY relative_file');
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
	elseif ($random == 'database') {
		$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track.track_id
			FROM track, random
			WHERE random.sid	= "' . mysqli_real_escape_string($db,$cfg['sid']) . '" AND
			random.track_id		= track.track_id
			ORDER BY position');
	}
	elseif ($random == 'new') {
		$blacklist = explode(',', $cfg['random_blacklist']);
		$blacklist = '"' . implode('","', $blacklist) . '"';
		$query = mysqli_query($db,'SELECT track.artist, title, relative_file, miliseconds, audio_bitrate, track_id
			FROM track, album
			WHERE (genre_id = "" OR genre_id NOT IN (' . $blacklist . ')) AND
			audio_dataformat != "" AND
			video_dataformat = "" AND
			track.album_id = album.album_id
			ORDER BY RAND()
			LIMIT 30');
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Unsupported query string[/b][br]' . $_SERVER['QUERY_STRING']);
			
	if ($stream_id != -1 && isset($cfg['encode_extension'][$stream_id]) == false)
		message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]stream_id');
	
	createHiddenDir(NJB_HOME_DIR . 'stream/');
	$m3u = 'stream/netjukebox_' . randomHex() . '.m3u';
	
	$m3u_content = '#EXTM3U' . "\n";
	while ($track = mysqli_fetch_assoc($query)) {
		$extension = substr(strrchr($track['relative_file'], '.'), 1);
		$extension = strtolower($extension);
		if (sourceFile($extension, $track['audio_bitrate'], $stream_id))
			$stream_extension = $extension;
		else
			$stream_extension = $cfg['encode_extension'][$stream_id];
		
		if ($sid) {
			// Share stream
			$url = NJB_HOME_URL . 'stream.php?action=stream&stream_id=' . $stream_id . '&track_id=' . $track['track_id'] . '&sid=' . $sid . '&ext=.' . $stream_extension;
		}
		else {
			// Common stream
			$hash = hmacsha1($cfg['server_seed'], $track['track_id'] . $stream_id . $cfg['sid']);
			$url = NJB_HOME_URL . 'stream.php?action=stream&stream_id=' . $stream_id . '&track_id=' . $track['track_id'] . '&sid=' . $cfg['sid'] . '&hash=' . $hash . '&ext=.' . $stream_extension;
		}
		
		$m3u_content .= '#EXTINF:' . round($track['miliseconds'] / 1000) . ',' . $track['artist'] . ' - ' . $track['title'] . "\n";
		$m3u_content .= $url . "\n";	
	}
	$m3u_content .= '#EXT-X-ENDLIST' . "\n";
	
	if (file_put_contents(NJB_HOME_DIR . $m3u, $m3u_content) === false)
		message(__FILE__, __LINE__, 'error', '[b]Failed to write file:[/b][br]' . NJB_HOME_DIR . $m3u);
		
	// Cleanup stream directory
	$dir = NJB_HOME_DIR . 'stream/';
	
	$entries = @scandir($dir) or message(__FILE__, __LINE__, 'error', '[b]Failed to open directory:[/b][br]' . $dir);
	foreach ($entries as $entry) {
		$file = $dir . $entry;
		if (!in_array($entry, array('.', '..', 'index.php','.gitignore')) && is_file($file) && filemtime($file) < filemtime(NJB_HOME_DIR . $m3u) - 86400)
			@unlink($file);
	}
	
	if ($album_id)
		updateCounter($album_id, NJB_COUNTER_STREAM);
	
	header('Location: ' . NJB_HOME_URL . $m3u);
	
	exit();
}

/* 

//  +------------------------------------------------------------------------+
//  | Play to                                                                |
//  +------------------------------------------------------------------------+
function playTo() {
	global $cfg, $db;
	
	$data = array();
	$stream_id		= get('stream_id');
	$track_id			= get('track_id');
	$album_id			= get('album_id');
	$favorite_id	= get('favorite_id');
	$random				= get('random');
	$sid					= get('sid');
	$player_id		= get('player_id');
	
	$data['playToResult'] = "playTo_Error";
	$data['player_id'] = $player_id;
	
	$playerQuery = mysqli_query($db,'SELECT * FROM player WHERE player_id="' . $player_id. '"' );
	$player = mysqli_fetch_assoc($playerQuery);
	$player_host		= $player['player_host'];
	$player_port		= $player['player_port'];
	$player_pass		= $player['player_pass'];
	
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
		authenticate('access_stream');
		//$sid 			= cookie('netjukebox_sid');
	}
		
	if ($sid) {
		$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track_id
			FROM track, share_stream
			WHERE share_stream.sid	= "' . mysqli_real_escape_string($db,$sid) . '" AND
			share_stream.album_id	= track.album_id
			ORDER BY relative_file');
	}
	elseif ($track_id) {
		$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track_id FROM track WHERE track_id = "' . mysqli_real_escape_string($db,$track_id) . '"');
	}
	elseif ($album_id) {
		$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track_id FROM track WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '" ORDER BY relative_file');
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
	elseif ($random == 'database') {
		$query = mysqli_query($db,'SELECT artist, title, relative_file, miliseconds, audio_bitrate, track.track_id
			FROM track, random
			WHERE random.sid	= "' . mysqli_real_escape_string($db,$cfg['sid']) . '" AND
			random.track_id		= track.track_id
			ORDER BY position');
	}
	elseif ($random == 'new') {
		$blacklist = explode(',', $cfg['random_blacklist']);
		$blacklist = '"' . implode('","', $blacklist) . '"';
		$query = mysqli_query($db,'SELECT track.artist, title, relative_file, miliseconds, audio_bitrate, track_id
			FROM track, album
			WHERE (genre_id = "" OR genre_id NOT IN (' . $blacklist . ')) AND
			audio_dataformat != "" AND
			video_dataformat = "" AND
			track.album_id = album.album_id
			ORDER BY RAND()
			LIMIT 30');
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Unsupported query string[/b][br]' . $_SERVER['QUERY_STRING']);
			
	if ($stream_id != -1 && isset($cfg['encode_extension'][$stream_id]) == false)
		message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]stream_id');
	//$sid 			= cookie('netjukebox_sid');
	createHiddenDir(NJB_HOME_DIR . 'stream/');
	$m3u = 'stream/netjukebox_' . randomHex() . '.m3u';
	
	$m3u_content = '#EXTM3U' . "\n";
	while ($track = mysqli_fetch_assoc($query)) {
		$extension = substr(strrchr($track['relative_file'], '.'), 1);
		$extension = strtolower($extension);
		if (sourceFile($extension, $track['audio_bitrate'], $stream_id))
			$stream_extension = $extension;
		else
			$stream_extension = $cfg['encode_extension'][$stream_id];
		
		if ($sid) {
			// Share stream
			$url = NJB_HOME_URL . 'stream.php?action=streamTo&stream_id=' . $stream_id . '&track_id=' . $track['track_id'] . '&sid=' . $sid . '&ext=.' . $stream_extension;
		}
		else {
			// Common stream
			$hash = hmacsha1($cfg['server_seed'], $track['track_id'] . $stream_id . $cfg['sid']);
			$url = NJB_HOME_URL . 'stream.php?action=streamTo&stream_id=' . $stream_id . '&track_id=' . $track['track_id'] . '&sid=' . $cfg['sid'] . '&hash=' . $hash . '&ext=.' . $stream_extension;
		}
		
		$m3u_content .= '#EXTINF:' . round($track['miliseconds'] / 1000) . ',' . $track['artist'] . ' - ' . $track['title'] . "\n";
		$m3u_content .= $url . "\n";	
	}
	$m3u_content .= '#EXT-X-ENDLIST' . "\n";
	
	if (file_put_contents(NJB_HOME_DIR . $m3u, $m3u_content) === false)
		message(__FILE__, __LINE__, 'error', '[b]Failed to write file:[/b][br]' . NJB_HOME_DIR . $m3u);
		
	// Cleanup stream directory
	$dir = NJB_HOME_DIR . 'stream/';
	
	$entries = @scandir($dir) or message(__FILE__, __LINE__, 'error', '[b]Failed to open directory:[/b][br]' . $dir);
	foreach ($entries as $entry) {
		$file = $dir . $entry;
		if (!in_array($entry, array('.', '..', 'index.php')) && is_file($file) && filemtime($file) < filemtime(NJB_HOME_DIR . $m3u) - 86400)
			@unlink($file);
	}
	
	if ($album_id){
		//updateCounter($album_id, NJB_COUNTER_STREAM);
	}
	
	$status = mpd('status', $player_host, $player_port);
	$index = $status['playlistlength'];
	mpd("load " . NJB_HOME_URL . $m3u, $player_host, $player_port);
	if ($index > 0) {
		mpd("play " . $index, $player_host, $player_port);
	}
	else {
		mpd("play", $player_host, $player_port);
	}
	
	$data['playToResult'] = 'playTo_OK';
	echo safe_json_encode($data);	
	exit();
}
 */



//  +------------------------------------------------------------------------+
//  | Stream playlist                                                        |
//  +------------------------------------------------------------------------+
function streamPlaylist($favorite_id) {
	global $cfg, $db;
	
	createHiddenDir(NJB_HOME_DIR . 'stream/');
	$m3u = 'stream/netjukebox_' . randomHex() . '.m3u';
	
	$m3u_content = '#EXTM3U' . "\n";
	$query = mysqli_query($db,'SELECT stream_url FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id . ' AND stream_url != "" ORDER BY position');
	while ($favoriteitem = mysqli_fetch_assoc($query))
		$m3u_content .= $favoriteitem['stream_url'] . "\n";
	$m3u_content .= '#EXT-X-ENDLIST' . "\n";
	
	if (file_put_contents(NJB_HOME_DIR . $m3u, $m3u_content) === false)
		message(__FILE__, __LINE__, 'error', '[b]Failed to write file:[/b][br]' . NJB_HOME_DIR . $m3u);
	
	header('Location: ' . NJB_HOME_URL . $m3u);
	exit();
}




//  +------------------------------------------------------------------------+
//  | Stream                                                                 |
//  +------------------------------------------------------------------------+
function stream() {
	global $cfg, $db;
	
	$track_id	= get('track_id');
	$stream_id	= (int) get('stream_id');
	$sid		= get('sid');
	$hash		= get('hash');
	
	if ($stream_id != -1 && isset($cfg['encode_extension'][$stream_id]) == false) {
		header('HTTP/1.1 400 Bad Request');
		exit();
	}
	
	if ($hash)	authenticateStream();
	else		authenticateShareStream();
	
	$query = mysqli_query($db,'SELECT
		LOWER(SUBSTRING_INDEX(relative_file, ".", -1)) AS extension,
		track.artist, title, album, album.year, disc, discs, number,
		relative_file, mime_type, miliseconds, filesize, filemtime, audio_bitrate
		FROM track, album
		WHERE track_id = "' . mysqli_real_escape_string($db,$track_id) . '"
		AND track.album_id = album.album_id');
	$track = mysqli_fetch_assoc($query);
	$file = $cfg['media_dir'] . $track['relative_file'];
	
	if (sourceFile($track['extension'], $track['audio_bitrate'], $stream_id)) {
		// Stream from source
		streamFile($file, $track['mime_type']);
	}
	elseif ($cache = cacheGetFile($track_id, $stream_id)) {
		// Stream from cache
		cacheUpdateTag($track_id, $stream_id, $cache);
		streamFile($cache, $cfg['encode_mime_type'][$stream_id]);
	}
	else {
		// Real time transcode stream
		ini_set('zlib.output_compression', 'off');
		ini_set('max_execution_time', 0);
		
		if (file_exists(NJB_HOME_DIR . '-'))
			@unlink(NJB_HOME_DIR . '-');
		
		$cmd = $cfg['decode_stdout'][$track['extension']] . ' | ' . $cfg['encode_stdout'][$stream_id];
		$cmd = str_replace('%source', escapeCmdArg($file), $cmd);
				
		header('Accept-Ranges: none');
		header('Content-Type: ' . $cfg['encode_mime_type'][$stream_id]);
		
		if (@passthru($cmd) == false) {
			header('HTTP/1.1 500 Internal Server Error');
			exit();
		}
	}
}




//  +------------------------------------------------------------------------+
//  | Stream to other mpd in network                                         |
//  +------------------------------------------------------------------------+
function streamTo() {
	global $cfg, $db;
	
	$track_id	= get('track_id');
	$filepath	= get('filepath');
	$filepath 	= str_replace('ompd_ampersand_ompd','&',$filepath);
	$stream_id	= (int) get('stream_id');
	$sid		= get('sid');
	$hash		= get('hash');
	
	if ($stream_id != -1 && isset($cfg['encode_extension'][$stream_id]) == false) {
		header('HTTP/1.1 400 Bad Request');
		exit();
	}
	
	/* if ($hash)	authenticateStream();
	else		authenticateShareStream(); */
	
	if ($filepath) {
		$mime = mime_content_type_replacement($filepath);
		if (strpos($mime, 'audio') !== false) {
			streamFile($filepath, $mime);
		}
	}
	
	$query = mysqli_query($db,'SELECT
		LOWER(SUBSTRING_INDEX(relative_file, ".", -1)) AS extension,
		track.artist, title, album, album.year, disc, discs, number,
		relative_file, mime_type, miliseconds, filesize, filemtime, audio_bitrate
		FROM track, album
		WHERE track_id = "' . mysqli_real_escape_string($db,$track_id) . '"
		AND track.album_id = album.album_id');
	$track = mysqli_fetch_assoc($query);
	$file = $cfg['media_dir'] . $track['relative_file'];
	
	if (sourceFile($track['extension'], $track['audio_bitrate'], $stream_id)) {
		// Stream from source
		streamFile($file, $track['mime_type']);
	}
	elseif ($cache = cacheGetFile($track_id, $stream_id)) {
		// Stream from cache
		cacheUpdateTag($track_id, $stream_id, $cache);
		streamFile($cache, $cfg['encode_mime_type'][$stream_id]);
	}
	else {
		// Real time transcode stream
		ini_set('zlib.output_compression', 'off');
		ini_set('max_execution_time', 0);
		
		if (file_exists(NJB_HOME_DIR . '-'))
			@unlink(NJB_HOME_DIR . '-');
		
		$cmd = $cfg['decode_stdout'][$track['extension']] . ' | ' . $cfg['encode_stdout'][$stream_id];
		$cmd = str_replace('%source', escapeCmdArg($file), $cmd);
				
		header('Accept-Ranges: none');
		header('Content-Type: ' . $cfg['encode_mime_type'][$stream_id]);
		
		if (@passthru($cmd) == false) {
			header('HTTP/1.1 500 Internal Server Error');
			exit();
		}
	}
}




//  +------------------------------------------------------------------------+
//  | Authenticate stream                                                    |
//  +------------------------------------------------------------------------+
function authenticateStream() {
	global $cfg, $db;
	header('Expires: Mon, 9 Oct 2000 18:00:00 GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	
	$track_id	= get('track_id');
	$stream_id	= (int) get('stream_id');
	$sid		= get('sid');
	$hash		= get('hash');
	
	$query 		= mysqli_query($db,'SELECT logged_in, idle_time, user_id, ip FROM session WHERE sid = BINARY "' . mysqli_real_escape_string($db,$sid) . '"');
	$session	= mysqli_fetch_assoc($query);
	$query 		= mysqli_query($db,'SELECT access_stream FROM user WHERE user_id = ' . (int) $session['user_id']);
	$user 		= mysqli_fetch_assoc($query);
	
	if ($session['logged_in'] &&
		$session['idle_time'] + $cfg['session_lifetime'] > time() &&
		$session['ip'] == $_SERVER['REMOTE_ADDR'] &&
		$hash == hmacsha1($cfg['server_seed'], $track_id . $stream_id . $sid) &&
		$user['access_stream']) {
		mysqli_query($db,'UPDATE session SET
			idle_time		= ' . (int) time() . ',
			hit_counter		= hit_counter + 1,
			visit_counter	= visit_counter + ' . (time() > $session['idle_time'] + 3600 ? 1 : 0) . '
			WHERE sid		= BINARY "' . mysqli_real_escape_string($db,$sid) . '"');
		return true;
	}
	
	header('HTTP/1.1 403 Forbidden');
	exit();
}




//  +------------------------------------------------------------------------+
//  | Authenticate share stream                                              |
//  +------------------------------------------------------------------------+
function authenticateShareStream() {
	global $cfg, $db;
	header('Expires: Mon, 9 Oct 2000 18:00:00 GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	
	$sid		= get('sid');
	$track_id	= get('track_id');
	$album_id	= substr($track_id, 0, strpos($track_id, '_'));
	
	$query = mysqli_query($db,'SELECT ip, album_id, stream_id, expire_time FROM share_stream
		WHERE sid = BINARY "' . mysqli_real_escape_string($db,$sid) . '"');
	$share_stream = mysqli_fetch_assoc($query);
	
	if ($share_stream['ip']	== $_SERVER['REMOTE_ADDR'] &&
		$share_stream['album_id'] == $album_id &&
		$share_stream['expire_time'] > time())
		return true;
	
	header('HTTP/1.1 403 Forbidden');
	exit();
}




//  +------------------------------------------------------------------------+
//  | Share album                                                            |
//  +------------------------------------------------------------------------+
function shareAlbum($album_id) {
	global $cfg, $db;
	authenticate('access_admin', false, true);
	
	if ($cfg['album_share_stream'] == false)
		message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]Share album disabled');
	
	$query = mysqli_query($db,'SELECT artist_alphabetic, album, year
		FROM album
		WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '"');
	$album = mysqli_fetch_assoc($query);
	
	if ($album == false)
		message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]album_id not found in database');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Media';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= $album['artist_alphabetic'];
	$nav['url'][]	= 'index.php?action=view2&amp;artist=' . rawurlencode($album['artist_alphabetic']);
	$nav['name'][]	= $album['album'];
	$nav['url'][]	= 'index.php?action=view3&amp;album_id=' . $album_id;
	$nav['name'][]	= 'Share stream';
	require_once('include/header.inc.php');
	
	$expire_time = time() + $cfg['share_stream_lifetime'];
	$sid = randomKey();
	mysqli_query($db,'INSERT INTO share_stream (sid, album_id, stream_id, expire_time) VALUES (
		"' . mysqli_real_escape_string($db,$sid) . '",
		"' . mysqli_real_escape_string($db,$album_id) . '",
		' . (int) $cfg['stream_id'] . ',
		' . (int) $expire_time . ')');
	
	$url		= NJB_HOME_URL . 'stream.php?action=playlist&amp;sid=' . $sid;
	
	$name	= $album['artist_alphabetic'] . ' - ';
	$name	.=  ($album['year']) ? $album['year'] . ' - ' : '';
	$name	.= $album['album'];
	// $name 	= encodeEscapeChar($name);
		
	$transcode		= false;
	$exact			= true;
	$extensions		= array();
	$miliseconds	= 0;
	$query = mysqli_query($db,'SELECT track.filesize, cache.filesize AS cache_filesize,
		miliseconds, audio_bitrate, track_id,
		LOWER(SUBSTRING_INDEX(track.relative_file, ".", -1)) AS extension
		FROM track LEFT JOIN cache
		ON track.track_id = cache.id
		AND cache.profile = ' . (int) $cfg['stream_id'] . '
		WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '"');
	while($track = mysqli_fetch_assoc($query)) {
		if (in_array($track['extension'], $extensions) == false) {
			$extensions[] = $track['extension'];
		}
		if (sourceFile($track['extension'], $track['audio_bitrate'], $cfg['stream_id']) == false) {
			$transcode = true;
			if ($track['cache_filesize'] == false)
				$exact = false;
		}
		$miliseconds += $track['miliseconds'];
	}
	
	sort($extensions);
	$source = implode($extensions, ', ');
	
	$profile_name = ($transcode) ? $cfg['encode_name'][$cfg['stream_id']] . ' (' . $source . ' source)' : 'Source (' . $source . ')';
	
	if ($transcode && $exact)		{$cache_txt = 'Transcoded:'; 	$cache_png = $cfg['img'] . 'small_check.png';}
	elseif ($transcode && !$exact)	{$cache_txt = 'Transcoded:'; 	$cache_png = $cfg['img'] . 'small_uncheck.png';}
	else							{$cache_txt = 'Source:'; 		$cache_png = $cfg['img'] . 'small_check.png';}
?>
<form action="" name="form" id="form">
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td colspan="3"><?php echo html($name); ?></td>
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="odd">
	<td></td>
	<td>Play time:</td>
	<td></td>
	<td><?php echo formattedTime($miliseconds); ?></td>
	<td class="space"></td>
</tr>
<tr class="even">
	<td class="space"></td>
	<td>Stream profile:</td>
	<td class="textspace"></td>
	<td><?php echo html($profile_name); ?></td>
	<td class="space"></td>
</tr>
<tr class="odd">
	<td></td>
	<td><?php echo $cache_txt; ?></td>
	<td></td>
	<td><img src="<?php echo $cache_png; ?>" alt="" class="small"></td>
	<td class="space"></td>
</tr>
<tr class="even">
	<td></td>
	<td>Mail:</td>
	<td></td>
	<td><a href="mailto:?SUBJECT=<?php echo rawurlencode($name); ?>&amp;BODY=---%0APlay%20time%3A%20<?php echo rawurlencode(formattedTime($miliseconds));?>%0AStream%3A%20<?php echo rawurlencode($name); ?>%0A<?php echo rawurlencode(str_replace('&amp;', '&', $url)); ?>%0A%0AThis%20stream%20will%20expire%20<?php echo  rawurlencode(date($cfg['date_format'], $expire_time)); ?>%20and%20locked%20to%20the%20first%20used%20IP%20address."><img src="<?php echo $cfg['img']; ?>small_mail.png" alt="" class="small"></a></td>
	<td></td>
</tr>
<tr class="odd">
	<td></td>
	<td>URL:</td>
	<td></td>
	<td><input type="text" value="<?php echo $url; ?>" readonly class="url" onClick="focus(this); select(this);"></td>
	<td></td>
</tr>
<tr class="even">
	<td></td>
	<td>QR Code:</td>
	<td></td>
	<td><img src="qrcode.php?d=<?php echo rawurlencode(str_replace('&amp;', '&', $url)); ?>&amp;e=l&amp;s=3" alt=""></td>
	<td></td>
</tr>
</table>
</form>
<?php
	require_once('include/footer.inc.php');
}
?>