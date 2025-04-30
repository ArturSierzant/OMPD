<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015 Artur Sierzant                                 |
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

global $cfg, $db, $t;

$type = $_POST["type"];
$size = $_POST["tileSize"];
$limit = $_POST["limit"];
$offset = $_POST["offset"];
$sessionId = isset($_POST["sessionId"]) ? $_POST["sessionId"] : '';
$countryCode = isset($_POST["countryCode"]) ? $_POST["countryCode"] : '';

authenticate('access_media');

$conn = $t->connect($sessionId, $countryCode);
if ($conn === true){
  switch ($type){
    case "suggested_new":
      $results = $t->getSuggestedNew();
      break;
    case "featured_new":
      $results = $t->getFeatured($limit, $offset);
      break;
    case "featured_recommended":
      $results = $t->getFeaturedRecommended($limit, $offset);
      break;
    case "featured_local":
      $results = $t->getFeaturedLocal($limit, $offset);
      break;
    case "featured_top":
      $results = $t->getFeaturedTop($limit, $offset);
      break;
    case "new_for_you":
      $results = $t->getNewForYou($limit, $offset);
      break;
    case "suggested_for_you":
      $results = $t->getSuggestedForYou($limit, $offset);
      break;
  }
/*   if ($results['items']){
    foreach($results['items'] as $res) {
      $albums = array();
      $albums['album_id'] = 'tidal_' . $res['id'];
      $albums['album'] = $res['title'];
      $albums['cover'] = $t->albumCoverToURL($res['cover'],'lq');
      $albums['artist_alphabetic'] = $res['artists'][0]['name'];
      if ($cfg['show_album_format']) {
        //$albums['audio_quality'] = $res['audioQuality'];
        $albums['audio_quality'] = getTidalAudioQualityMediaMetadata($res);
      }
      draw_tile ( $size, $albums, '', 'echo', $res['cover'] );
    }
  } */
  if (strtolower($results[0]['type'])=='album'){
    foreach($results as $res) {
      drawTidalTileItems($res, $size);
    }
  }
  else {
    echo '<div style="line-height: normal;">No albums found.</div>';
  }
}
else {
  echo '<div style="line-height: normal;">Error in connection to Tidal (' . $conn['error'] . ').</div>';
}

?>

