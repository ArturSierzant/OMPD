<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant	                         |
//  | http://www.ompd.pl           		                                     |
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

global $cfg, $db;
$track_id = get('track_id');
$data = array();

/* $query = mysqli_query($db,'SELECT track.artist, album.artist AS album_artist, title, featuring, miliseconds, relative_file, album, album.image_id, album.album_id, track.genre, track.audio_bitrate, track.audio_dataformat, track.audio_bits_per_sample, track.audio_sample_rate, album.genre_id, track.audio_profile, track.track_artist, album.year as year, track.number, track.comment, track.track_id, track.year as trackYear, track.dr, album.album_dr
		FROM track, album 
		WHERE track.album_id = album.album_id
		AND track_id = "' . mysqli_real_escape_string($db,$track_id) . '"'); */
$query = mysqli_query($db,'SELECT title	FROM track WHERE track_id = "' . mysqli_real_escape_string($db,$track_id) . '"');
	
$track = mysqli_fetch_assoc($query);

$title = $track['title'];

$title = findCoreTrackTitle($title);
$title = mysqli_real_escape_like($title);
//$title = strtolower($title);

$separator = $cfg['separator'];
$count = count($separator);


$query_string = '';
$i=0;
for ($i=0; $i<$count; $i++) {
	$query_string = $query_string . ' OR LOWER(track.title) LIKE "' . $title . $separator[$i] . '%"'; 
}
	
$filter_query = 'WHERE (LOWER(track.title) = "' . $title . '" ' . $query_string . ') AND track.album_id = album.album_id';

$order_query = 'ORDER BY title, artist, album';

$query = mysqli_query($db, 'SELECT track.artist, track.title, track.number, track.featuring, track.album_id, track.track_id, track.miliseconds, track.relative_file, album.image_id, album.album FROM track, album ' . $filter_query . ' ' . $order_query);


$q = 'SELECT * FROM
	(SELECT track.artist as track_artist, track.title, track.featuring, track.album_id, track.track_id as tid, track.miliseconds, track.number, track.relative_file, album.image_id, album.album, album.artist
	FROM track
	INNER JOIN album ON track.album_id = album.album_id '
	. $filter_query . ' ' . $order_query .') as a
	LEFT JOIN 
	(SELECT track_id, favorite_id FROM favoriteitem WHERE favorite_id = "' . $cfg['favorite_id'] . '") as b ON b.track_id = a.tid
	LEFT JOIN 
	(SELECT track_id, favorite_id as blacklist_id FROM favoriteitem WHERE favorite_id = "' . $cfg['blacklist_id'] . '") as bl ON bl.track_id = a.tid
	ORDER BY a.track_artist
	';
	
$query = mysqli_query($db,$q);

if (strlen($title) > 0) {
	$num_rows = mysqli_num_rows($query);
	if ($num_rows > 1) {
		$other_track_version = true;
	}
}
else {
	$other_track_version = false;
}

$track_id = array();
while ($track2 = mysqli_fetch_assoc($query)) {
	$track_id[] = $track2['tid'];
}


$data['other_track_version']	= (boolean) $other_track_version;
$data['title']					= $track['title'];
$data['track_ids']				= $track_id;
echo json_encode($data);
?>