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
$limit = $_POST["limit"];
$offset = $_POST["offset"];

authenticate('access_media');

$t = new TidalAPI;
$t->username = $cfg["tidal_username"];
$t->password = $cfg["tidal_password"];
$t->token = $cfg["tidal_token"];
$t->audioQuality = $cfg["tidal_audio_quality"];
$t->fixSSLcertificate();
$conn = $t->connect();
if ($conn === true){
  $results = $t->getFeatured($limit, $offset);
  if ($results['items']){
    foreach($results['items'] as $res) {
      $albums = array();
      $albums['album_id'] = 'tidal_' . $res['id'];
      $albums['album'] = $res['title'];
      $albums['cover'] = $t->albumCoverToURL($res['cover'],'lq');
      $albums['artist_alphabetic'] = $res['artist']['name'];
      if ($cfg['show_album_format']) {
        $albums['audio_quality'] = $res['audioQuality'];
      }
      draw_tile ( $size, $albums, '', 'echo', '' );
    }
  }
  else {
    $albums = null;
  }
}
else {
  $albums = null;
}

?>

