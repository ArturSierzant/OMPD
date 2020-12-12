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

$type = $_POST["type"];
$size = $_POST["tileSize"];
$limit = $_POST["limit"];
$offset = $_POST["offset"];

authenticate('access_media');


$h = new HraAPI;
if (NJB_WINDOWS) $t->fixSSLcertificate();

switch ($type){
  case "new":
    $results = $h->getCategorieContent("new", $limit, $offset);
    break;
  case "pop":
    $results = $h->getCategorieContent("pop", $limit, $offset);
    break;
  case "rock":
    $results = $h->getCategorieContent("rock", $limit, $offset);
    break;
  case "jazz":
    $results = $h->getCategorieContent("jazz", $limit, $offset);
    break;
  case "classical":
    $results = $h->getCategorieContent("classical", $limit, $offset);
    break;
  case "blues":
    $results = $h->getCategorieContent("blues", $limit, $offset);
    break;
}
if ($results['data']['results']){
  foreach($results['data']['results'] as $res) {
    $albums = array();
    $albums['album_id'] = 'hra_' . $res['id'];
    $albums['album'] = $res['title'];
    $albums['cover'] = $res['cover'];
    $albums['artist_alphabetic'] = $res['artist'];
    if ($cfg['show_album_format']) {
      $albums['audio_quality_tag'] = calculateAlbumFormat("",$res['tags']);
    }
    draw_tile ( $size, $albums, '', 'echo', '' );
  }
}
else {
  $albums = null;
}
?>

