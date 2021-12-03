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

global $cfg, $db, $t;

$type = $_POST["type"];
$size = $_POST["tileSize"];
$limit = $_POST["limit"];
$offset = $_POST["offset"];

authenticate('access_media');

/* $t = new TidalAPI;
$t->username = $cfg["tidal_username"];
$t->password = $cfg["tidal_password"];
$t->token = $cfg["tidal_token"];
$t->audioQuality = $cfg["tidal_audio_quality"];
$t->fixSSLcertificate(); */
//$t = tidal();
$conn = $t->connect();
$data = array();

if ($conn === true){
  switch ($type){
    case "suggested_artists":
      $results = $t->getSuggestedArtistsForYou($limit, $offset, true);
      break;
    case "suggested_new_tracks":
      $results = $t->getSuggestedNewTracks($limit, $offset, false);
      break;
  }
  if ($results['items']){
    switch ($type){
      case "suggested_artists":
        $i = 0;
        foreach($results['items'] as $artist) {
          $data["artists"][$i]["id"] = $artist["id"];
          $data["artists"][$i]["name"] = $artist["name"];
          if ($artist["picture"]) {
            $data["artists"][$i]["picture"] = $t->artistPictureToURL($artist["picture"]);
          }
          else {
            $data["artists"][$i]["picture"] = "";
          }
          $i++;
        }
        $data['items_count'] = $i;
        $data['size'] = $size;
        break;
      case "suggested_new_tracks":
        $data['tracks_results'] = $results['totalNumberOfItems'];
        $tracksList = tidalTracksList($results);
        $data['new_tracks'] = $tracksList;
        break;
    }
    
  }
  else {
    $data['return'] = 0;
    $data['items_count'] = 0;
  }
}
else {
  $data['return'] = $conn["return"];
  $data['response'] = $conn["error"];
}

echo safe_json_encode($data);

?>

