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
global $base_size, $spaces, $scroll_bar_correction;

authenticate('access_media');

$size = $_POST["tileSize"];
$search = $_POST["search"];
$searchStr = $_POST["searchStr"];
$ajax = $_POST["ajax"];
$artist = $_POST["artist"];

if ($search == 'albums') {
	//if ($searchStr) $searchStr = moveTheToBegining($searchStr);
	showAlbumsFromHRA($searchStr, $size);
}
elseif ($search == 'artistalbums') {
	$searchStr = moveTheToBegining($searchStr);
	//$searchStr = str_replace("the ", "", strtolower($searchStr));
	//$searchStr = str_replace("&", "", strtolower($searchStr));
	showArtistAlbumsFromHRA($searchStr, $size);
}
elseif ($search == 'artists') {
	if ($searchStr) $searchStr = moveTheToBegining($searchStr);
	//$searchStr = str_replace("the ", "", strtolower($searchStr));
	//$searchStr = str_replace("&", "", strtolower($searchStr));
	showArtistsFromHRA($searchStr, $size);
}
/* elseif ($search == 'all') {
	showAllFromTidal($searchStr, $size);
}
elseif ($search == 'bio') {
	showArtistBio($searchStr);
}
elseif ($search == 'topTracks') {
	$searchStr = moveTheToBegining($searchStr);
	showTopTracksFromTidal($searchStr, $artistId);
} */
?>

	
