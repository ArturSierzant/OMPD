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

require_once('include/initialize.inc.php');
require_once('include/library.inc.php');

global $cfg, $db;

$size = $_POST["tileSize"];

authenticate('access_media');

$query = mysqli_query($db, '
		SELECT * FROM
		(SELECT album_id, time FROM counter
		ORDER BY time DESC
		LIMIT 10) c
		GROUP BY c.album_id
		ORDER BY c.time DESC' );

$hra_session = false;
//if ($tileSizePHP) $size = $tileSizePHP;
$c = 0;
while ( $album = mysqli_fetch_assoc ($query)) {
	$c++;
	if ($c > 7) break; //7 is max number of tile displayed on page
	$albums = array();
	$a_id = $album['album_id'];
	$albums['album_id'] = $a_id;
	$tidal_cover = '';
	if (isTidal($a_id)){
		$query1 = mysqli_query($db, "SELECT album, cover, artist_alphabetic FROM tidal_album 
		WHERE album_id='" . getTidalId($a_id) . "' LIMIT 1");
		$a = mysqli_fetch_assoc ( $query1 );
		$albums['album'] = $a['album'];
		$tidal_cover = $a['cover'];
		$albums['artist_alphabetic'] = $a['artist_alphabetic'];
	}
	elseif (isHra($a_id)){
		if (!$hra_session) {
			$h = new HraAPI;
			$h->username = $cfg["hra_username"];
			$h->password = $cfg["hra_password"];
			if (NJB_WINDOWS) $t->fixSSLcertificate();
			$conn = $h->connect();
			if ($conn === true) $hra_session = true;
		}
		if ($hra_session === true){
			$results = $h->getAlbum(getHraId($a_id));
			if ($results['data']['results']){
				$albums['album'] = $results['data']['results']['title'];
				$albums['cover'] = 'https://' . $results['data']['results']['cover']['master']['file_url'];
				$albums['artist_alphabetic'] = $results['data']['results']['artist'];
			}
			else {
				$albums = null;
			}
		}
		else {
			$albums = null;
		}
		
	}
	else {
		$query1 = mysqli_query($db, "SELECT album, image_id, artist_alphabetic FROM album 
		WHERE album_id='" . $a_id . "' LIMIT 1");
		$a = mysqli_fetch_assoc ( $query1 );
		$albums['album'] = $a['album'];
		$albums['image_id'] = $a['image_id'];
		$albums['artist_alphabetic'] = $a['artist_alphabetic'];
	}
	if ($albums) draw_tile ( $size, $albums, '', 'echo', $tidal_cover );
}

?>

