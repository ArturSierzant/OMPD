<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2021 Artur Sierzant                            |
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
global $base_size, $spaces, $scroll_bar_correction;

authenticate('access_media');

$size = $_POST["tileSize"];
$search = $_POST["search"];
$searchStr = $_POST["searchStr"];
$ajax = $_POST["ajax"];
$artistId = $_POST["tidalArtistId"];

if ($search == 'albums') {
	$searchStr = moveTheToBegining($searchStr);
	showAlbumsFromTidal($searchStr, $size, $ajax, $artistId);
}
elseif ($search == 'artists') {
	showArtistsFromTidal($searchStr, $size);
}
elseif ($search == 'all') {
	showAllFromTidal($searchStr, $size);
}
elseif ($search == 'bio') {
	showArtistBio($searchStr, $size, $artistId);
}
elseif ($search == 'topTracks') {
	$searchStr = moveTheToBegining($searchStr);
	showTopTracksFromTidal($searchStr, $artistId);
}
?>

	
