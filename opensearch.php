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
//  | opensearch.php                                                         |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');

$action = get('action');

if		($action == 'installAlbumArtist')	installAlbumArtist();
elseif	($action == 'installTrackArtist')	installTrackArtist();
elseif	($action == 'installTrackTitle')	installTrackTitle();
elseif	($action == 'suggestAlbumArtist')	suggestAlbumArtist();
elseif	($action == 'suggestTrackArtist')	suggestTrackArtist();
elseif	($action == 'suggestTrackTitle')	suggestTrackTitle();
exit();




//  +------------------------------------------------------------------------+
//  | Install album artist plugin                                            |
//  +------------------------------------------------------------------------+
function installAlbumArtist() {
	global $cfg;
	header("Content-type: text/xml");
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
	<ShortName>netjukebox - Album Artist</ShortName>
	<Description>netjukebox - Album Artist</Description>
	<InputEncoding><?php echo NJB_DEFAULT_CHARSET; ?></InputEncoding>
	<OutputEncoding><?php echo NJB_DEFAULT_CHARSET; ?></OutputEncoding>
	<Image height="16" width="16" type="image/png"><?php echo NJB_HOME_URL; ?>image/favicon.png</Image>
	<Url type="text/html" method="get" template="<?php echo NJB_HOME_URL; ?>index.php?action=view1&amp;filter=exact&amp;artist={searchTerms}"/>
	<Url type="application/x-suggestions+json" template="<?php echo NJB_HOME_URL; ?>opensearch.php?action=suggestAlbumArtist&amp;artist={searchTerms}&amp;version=1"/>
</OpenSearchDescription>
<?php
}





//  +------------------------------------------------------------------------+
//  | Install track artist plugin                                            |
//  +------------------------------------------------------------------------+
function installTrackArtist() {
	global $cfg;
	header("Content-type: text/xml");
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
	<ShortName>netjukebox - Track Artist</ShortName>
	<Description>netjukebox - Track Artist</Description>
	<InputEncoding><?php echo NJB_DEFAULT_CHARSET; ?></InputEncoding>
	<OutputEncoding><?php echo NJB_DEFAULT_CHARSET; ?></OutputEncoding>
	<Image height="16" width="16" type="image/png"><?php echo NJB_HOME_URL; ?>image/favicon.png</Image>
	<Url type="text/html" method="get" template="<?php echo NJB_HOME_URL; ?>index.php?action=view3all&amp;filter=exact&amp;artist={searchTerms}"/>
	<Url type="application/x-suggestions+json" template="<?php echo NJB_HOME_URL; ?>opensearch.php?action=suggestTrackArtist&amp;artist={searchTerms}&amp;version=1"/>
</OpenSearchDescription>
<?php
}




//  +------------------------------------------------------------------------+
//  | Install track title plugin                                             |
//  +------------------------------------------------------------------------+
function installTrackTitle() {
	global $cfg;
	header("Content-type: text/xml");
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
	<ShortName>netjukebox - Title</ShortName>
	<Description>netjukebox - Title</Description>
	<InputEncoding><?php echo NJB_DEFAULT_CHARSET; ?></InputEncoding>
	<OutputEncoding><?php echo NJB_DEFAULT_CHARSET; ?></OutputEncoding>
	<Image height="16" width="16" type="image/png"><?php echo NJB_HOME_URL; ?>image/favicon.png</Image>
	<Url type="text/html" method="get" template="<?php echo NJB_HOME_URL; ?>index.php?action=view3all&amp;filter=exact&amp;title={searchTerms}"/>													
	<Url type="application/x-suggestions+json" template="<?php echo NJB_HOME_URL; ?>opensearch.php?action=suggestTrackTitle&amp;title={searchTerms}&amp;version=1"/>
</OpenSearchDescription>
<?php
}




//  +------------------------------------------------------------------------+
//  | Suggest album artist                                                   |
//  +------------------------------------------------------------------------+
function suggestAlbumArtist() {
	global $cfg, $db;
	header('Content-type: application/json');
		
	$artist = get('artist');
	authenticateOpensearch($artist);
	
	$query = mysqli_query($db,'SELECT artist_alphabetic FROM album
		WHERE artist_alphabetic LIKE "%' . mysqli_real_escape_like($artist) . '%"
		OR artist LIKE "%' . mysqli_real_escape_like($artist) . '%"
		OR artist SOUNDS LIKE "' . mysqli_real_escape_string($db,$artist) . '"
		GROUP BY artist_alphabetic ORDER BY artist_alphabetic LIMIT ' . (int) $cfg['autosuggest_limit']);
	
	$data = array();
	while ($album = mysqli_fetch_assoc($query))
		$data[] = (string) $album['artist_alphabetic'];
	$data = array($artist, $data);
		
	echo safe_json_encode($data);
}




//  +------------------------------------------------------------------------+
//  | Suggest track artist                                                   |
//  +------------------------------------------------------------------------+
function suggestTrackArtist() {
	global $cfg, $db;
	header('Content-type: application/json');
	
	$artist = get('artist');
	authenticateOpensearch($artist);
	
	$query = mysqli_query($db,'SELECT artist FROM track
		WHERE artist LIKE "%' . mysqli_real_escape_like($artist) . '%"
		OR artist SOUNDS LIKE "' . mysqli_real_escape_string($db,$artist) . '"
		GROUP BY artist ORDER BY artist LIMIT ' . (int) $cfg['autosuggest_limit']);
	
	$data = array();
	while ($track = mysqli_fetch_assoc($query))
		$data[] = (string) $track['artist'];
	$data = array($artist, $data);
			
	echo safe_json_encode($data);
}




//  +------------------------------------------------------------------------+
//  | Suggest track title                                                    |
//  +------------------------------------------------------------------------+
function suggestTrackTitle() {
	global $cfg, $db;
	header('Content-type: application/json');
	
	$title = get('title');
	authenticateOpensearch($title);
	
	$query = mysqli_query($db,'SELECT title FROM track
		WHERE title LIKE "%' . mysqli_real_escape_like($title) . '%"
		OR title SOUNDS LIKE "' . mysqli_real_escape_string($db,$title) . '"
		GROUP BY title ORDER BY title LIMIT ' . (int) $cfg['autosuggest_limit']);
	
	$data = array();
	while ($track = mysqli_fetch_assoc($query))
		$data[] = (string) $track['title'];
	$data = array($title, $data);
		
	echo safe_json_encode($data);
}




//  +------------------------------------------------------------------------+
//  | Authenticate opensearch                                                |
//  +------------------------------------------------------------------------+
function authenticateOpensearch($input) {
	global $cfg, $db;
	header('Expires: Mon, 9 Oct 2000 18:00:00 GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
		
	$sid 		= cookie('netjukebox_sid');
	$version	= get('version');
	$query		= mysqli_query($db,'SELECT logged_in, idle_time, ip, user_agent FROM session WHERE sid = BINARY "' . mysqli_real_escape_string($db,$sid) . '"');
	$session	= mysqli_fetch_assoc($query);
	
	if ($sid == '') {
		$data = array('Allow third-party cookies,', 'or add an exception for this domain!');
		$data = array($input, $data);
		echo safe_json_encode($data);
		exit();
	}
	
	if ($version != 1) {
		$data = array('Reinstall opensearch plugin!');
		$data = array($input, $data);
		echo safe_json_encode($data);
		exit();
	}
	
	if ($session['logged_in'] &&
		$session['ip']			== $_SERVER['REMOTE_ADDR'] &&
		$session['user_agent']	== substr($_SERVER['HTTP_USER_AGENT'], 0, 255) &&
		$session['idle_time'] + $cfg['session_lifetime'] > time())
		return true;
	
	$data = array('Login netjukebox!');
	$data = array($input, $data);
	echo safe_json_encode($data);
	exit();
}
?>