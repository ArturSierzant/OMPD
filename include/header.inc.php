<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant                            |
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
//  | css hash                                                               |
//  +------------------------------------------------------------------------+
function css_hash() {
	global $cfg;
	
	$hash_data =  filemtime(NJB_HOME_DIR . 'cache.php');
	$hash_data .= filemtime(NJB_HOME_DIR . 'skin/' . $cfg['skin'] . '/styles.css');
	
	return md5($hash_data);
}





//  +------------------------------------------------------------------------+
//  | javascript hash                                                        |
//  +------------------------------------------------------------------------+
function javascript_hash() {
	global $cfg;
	
	$source = array('javascript-src/initialize.js',
					'javascript-src/overlib.js',
					'javascript-src/overlib_cssstyle.js',
					'javascript-src/sha1.js');
	
	$hash_data = filemtime(NJB_HOME_DIR . 'cache.php');
	foreach ($source as $file)
		$hash_data .= filemtime(NJB_HOME_DIR . $file);
	
	return md5($hash_data);
}




//  +------------------------------------------------------------------------+
//  | Head & Body                                                            |
//  +------------------------------------------------------------------------+
$header['title'] = 'O!MPD &bull; ';
if (NJB_SCRIPT == 'message.php')									$header['title'] .= 'Message';
elseif ($cfg['username'] == '')										$header['title'] .= 'Live @ ' . html($_SERVER['HTTP_HOST']);
elseif (NJB_SCRIPT == 'playlist.php')								$header['title'] .= 'Now playing';
elseif (get('authenticate') == 'logout')							$header['title'] .= 'Logout';
elseif (get('authenticate') == 'logoutSession' && get('sign'))		$header['title'] .= 'Signed (Logout session)';
elseif (get('authenticate') == 'logoutAllSessions' && get('sign'))	$header['title'] .= 'Signed (Logout all sessions)';
elseif (getpost('sign'))											$header['title'] .= 'Signed (' . html(implode(' - ', $nav['name'])) . ')';
elseif (empty($nav['name']))										$header['title'] .= 'Undefined';
else																$header['title'] .=  html(implode(' - ', $nav['name']));

$header['head']  = "\t" . '<meta http-equiv="Content-Type" content="text/html; charset=' . html(NJB_DEFAULT_CHARSET) .'">' . "\n";

$header['head'] .= "\t" . '<meta name="generator" content="netjukebox, Copyright (C) 2001-2012 Willem Bartels; O!MPD, Copyright (C) 2015 Artur Sierzant">' . "\n";
$header['head'] .= "\t" . '<title>' . $header['title'] . '</title>' . "\n";
$header['head'] .= "\t" . '<link rel="manifest" href="manifest.json">' . "\n";
if (isset($cfg['access_media']) && $cfg['access_media']) {
	$header['head'] .= "\t" . '<link rel="search" type="application/opensearchdescription+xml" title="O!MPD - Album Artist" href="' . NJB_HOME_URL . 'opensearch.php?action=installAlbumArtist">' . "\n";
	$header['head'] .= "\t" . '<link rel="search" type="application/opensearchdescription+xml" title="O!MPD - Track Artist" href="' . NJB_HOME_URL . 'opensearch.php?action=installTrackArtist">' . "\n";
	$header['head'] .= "\t" . '<link rel="search" type="application/opensearchdescription+xml" title="O!MPD - Title" href="' . NJB_HOME_URL . 'opensearch.php?action=installTrackTitle">' . "\n";
}
$header['head'] .= "\t" . '<link rel="shortcut icon" type="image/png" href="image/icon.png">' . "\n";

//$header['head'] .= "\t" . '<link href="http://fonts.googleapis.com/css?family=Noto+Sans:400,700" rel="stylesheet" type="text/css">' . "\n";
//$header['head'] .= "\t" . '<link href="http://fonts.googleapis.com/css?family=Roboto:400,300,100&subset=latin,latin-ext" rel="stylesheet" type="text/css">' . "\n";


if ($cfg['download_font_awesome'])
	$header['head'] .= "\t" . '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">' . "\n";
else
	$header['head'] .= "\t" . '<link rel="stylesheet" href="fonts/font-awesome-4.5.0/css/font-awesome.min.css">' . "\n";

$header['head'] .= "\t" . '<link rel="stylesheet" type="text/css" href="fonts/typicons/typicons.css">' . "\n";
$header['head'] .= "\t" . '<link rel="stylesheet" type="text/css" href="cache.php?action=css&amp;skin=' . rawurlencode($cfg['skin']) . '&amp;hash=' . css_hash() . '">' . "\n";
$header['head'] .= "\t" . '<script src="cache.php?action=javascript&amp;hash=' . javascript_hash() . '" type="text/javascript"></script>' . "\n";

//$header['head'] .= "\t" . '<script src="vendor-dist/components/jquery/jquery.min.js"></script>' . "\n";


$header['body'] = 'onload="javascript: if (window.initialize) initialize(); cookie(); "';




//  +------------------------------------------------------------------------+
//  | Menu                                                                   |
//  +------------------------------------------------------------------------+
$header['seperation'] = ' <span class="seperation">|</span> ' . "\n";
if ($cfg['menu'] == 'Library') {
	/* 
	Defined in template.header.php
	*/
	}

elseif ($cfg['menu'] == 'playlist')	{
	/* 
	Defined in template.header.php
	*/
}

elseif ($cfg['menu'] == 'favorite')	{
	$header['menu'] = "\t" . '<a href="favorite.php">favorites</a>' . "\n";
}

elseif ($cfg['menu'] == 'config') {
	$header['menu'] = "\t" . '<a href="config.php?action=playerProfile">player profile</a>' . $header['seperation'];
	$header['menu'] .= "\t" . '<a href="config.php?action=streamProfile">stream profile</a>' . $header['seperation'];
	$header['menu'] .= "\t" . '<a href="config.php?action=downloadProfile">download profile</a>' . $header['seperation'];
	$header['menu'] .= "\t" . '<a href="config.php?action=skinProfile">skin profile</a>' . $header['seperation'];
	$header['menu'] .= "\t" . '<a href="users.php">users</a>' . $header['seperation'];
	$header['menu'] .= "\t" . '<a href="update.php?action=update&amp;sign=' . $cfg['sign'] . '">update</a>' . "\n";
}

elseif ($cfg['menu'] == 'about') {
	$header['menu'] = "\t" . '<a href="about.php">about O!MPD</a>' . $header['seperation'];
	$header['menu'] .= "\t" . '<a href="about.php?action=license">license</a>' . $header['seperation'];
}




//  +------------------------------------------------------------------------+
//  | Header template                                                        |
//  +------------------------------------------------------------------------+
require_once(NJB_HOME_DIR . 'skin/' . $cfg['skin'] . '/template.header.php');
$header = null;




//  +------------------------------------------------------------------------+
//  | No script                                                              |
//  +------------------------------------------------------------------------+
if (NJB_SCRIPT != 'about.php') {
	echo '<noscript>' . "\n";
	echo '<table cellspacing="10" cellpadding="0" class="error">' . "\n";
	echo '<tr>' . "\n";
	echo "\t" . '<td valign="top"><img src="' . $cfg['img'] . 'medium_message_error.png" alt=""></td>' . "\n";
	echo "\t" . '<td valign="top"><strong>JavaScript is required</strong><br>Enable JavaScript in the web browser.</td>' . "\n";
	echo '</tr>' . "\n";
	echo '</table>' . "\n";
	echo '</noscript>' . "\n";
}




//  +------------------------------------------------------------------------+
//  | Navigator                                                              |
//  +------------------------------------------------------------------------+
if (empty($nav['name']) == false) {
	if (count($nav['name']) == 1 )	echo '<span class="nav_home"></span>' . "\n";
	else {
	echo '<span class="nav_tree">' . "\n";
	for ($i=0; $i < count($nav['name']); $i++) {
		if ($i > 0)								echo '<span class="nav_seperation">></span>' . "\n";
		if (empty($nav['url'][$i]) == false)	echo '<a href="' . $nav['url'][$i] . '">' . html($nav['name'][$i]) . '</a>' . "\n";
		else									echo html($nav['name'][$i]) . "\n";
	}
	echo '</span>' . "\n";
	}
}



?>