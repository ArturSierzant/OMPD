<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2020 Artur Sierzant                            |
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
//  | ridirect.php                                                           |
//  +------------------------------------------------------------------------+

require_once('include/initialize.inc.php');
//authenticate('access_media');

if ($_GET["query_type"] === 'lyrics') {
				header('Location: ' . 'https://www.google.com/search?q=' . rawurlencode($_GET["q"]) . ' ' . $cfg['lyrics_search']);
				exit();
};


$search_id	= (int) get('search_id');
$album_id	= get('album_id');

if (isset($cfg['search_name'][$search_id]) == false)
	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]search_id');

if ($cfg['search_method'][$search_id] != 'get' && $cfg['search_method'][$search_id] != 'post')
	message(__FILE__, __LINE__, 'error', '[b]Unsupported value for[/b][br]search_methode');
	
$cfg['search_name']				= $cfg['search_name'][$search_id];
$cfg['search_url_artist']		= $cfg['search_url_artist'][$search_id];
$cfg['search_url_album']		= $cfg['search_url_album'][$search_id];
$cfg['search_url_combined']		= $cfg['search_url_combined'][$search_id];
$cfg['search_method']			= $cfg['search_method'][$search_id];	
$cfg['search_charset']			= $cfg['search_charset'][$search_id];

if (isTidal($album_id)) {
$query = mysqli_query($db,'SELECT artist, album
	FROM tidal_album
	WHERE album_id = "' . mysqli_real_escape_string($db,getTidalId($album_id)) . '"');
	$album = mysqli_fetch_assoc($query);
}
elseif (isHra($album_id)) {
	$album_id = getHraId($album_id);
	$h = new HraAPI;
	$h->username = $cfg["hra_username"];
	$h->password = $cfg["hra_password"];
	if (NJB_WINDOWS) $t->fixSSLcertificate();
	$conn = $h->connect();
	if ($conn === true){
		$albumHra = $h->getAlbum($album_id)['data']['results'];
		if (!$albumHra) {
			message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]could not get data from HighResAudio');
		}
		$album = array();
		$album['artist'] = $albumHra['artist'];
		$album['album'] = $albumHra['title'];
	}
	else {
		$data['return'] = $conn["return"];
		$data['response'] = $conn["error"];
		message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]could not connect to HighResAudio');
	}
}
else {
	$query = mysqli_query($db,'SELECT artist, album
	FROM album
	WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '"');
	$album = mysqli_fetch_assoc($query);
}

if ($album == false)
	message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]album_id not found in database');

$artist = $album['artist'];
$album = $album['album'];

if (in_array(strtolower($artist), $cfg['no_album_artist'])) {
	// Search album
	$url = $cfg['search_url_album'];
}
elseif ($cfg['search_url_combined'] != '') {
	// Search combined
	$url = $cfg['search_url_combined'];
}
else {
	// Search artist
	$url = $cfg['search_url_artist'];
}

//$url = $cfg['search_url_album'];

// Remove (...) [...] {...} from the end
$artist = preg_replace('#^(.+?)(?:\s*\(.+\)|\s*\[.+\]|\s*{.+})?$#', '$1', $artist);
$album = preg_replace('#^(.+?)(?:\s*\(.+\)|\s*\[.+\]|\s*{.+})?$#', '$1', $album);

$artist = iconv(NJB_DEFAULT_CHARSET, $cfg['search_charset'], $artist);
$album = iconv(NJB_DEFAULT_CHARSET, $cfg['search_charset'], $album);


// get
if ($cfg['search_method'] == 'get') {
	$url = str_replace('%artist', urlencode($artist), $url);
	$url = str_replace('%album', urlencode($album), $url);
	header('Location: ' . $url);
	exit();
}


// post
ini_set('default_charset', $cfg['search_charset']);
list($url, $query) = explode('?', $url, 2);
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo html($cfg['search_charset']); ?>">
	<meta name="generator" content="netjukebox, Copyright (C) 2001-2012 Willem Bartels">
	<title>O!MPD &bull; Redirect</title>
	<link rel="shortcut icon" type="image/png" href="image/favicon.png">
	<style type=text/css>
	span.large {font-size: 32px; font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;}
	body {background: White; color: Silver;}
	</style>
</head>
<body onLoad="document.AutoPost.submit();">
<span class="large">loading</span>
<form action="<?php echo $url; ?>" method="post" name="AutoPost" id="AutoPost">
<?php
$query_array = explode ('&', $query);
foreach ($query_array as $sub_query)	{
	list($key, $value) = explode('=', $sub_query, 2);
	$value = str_replace('%artist', $artist, $value);
	$value = str_replace('%album', $album, $value);
	echo "\t" . '<input type="hidden" name="'. htmlspecialchars($key, ENT_COMPAT, $cfg['search_charset']) . '" value="' . htmlspecialchars($value, ENT_COMPAT, $cfg['search_charset']) . '">' . "\n";
}
?>
</form>
</body>
</html>
