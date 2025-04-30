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

global $cfg, $db;
$data = array();
$data['artist'] = $artist = get('artist');
$data['title'] = $title = get('title');
$data['result'] = 'error';



//for data from radio stream (%A0 is urlencoded &nbsp;):
if ($artist == '%A0' || $artist == '') {
  //try to find artist and title in $title:
  $t = explode("-", $title);
  if (count($t) > 1) {
    $artist = $t[0];
    $title = $t[1];
    $data['artist'] = $artist;
    $data['title'] = $title;
  }
}

$titleUrl = urlencode($title);
$artistUrl = urlencode($artist);

if ((!$artist || $artist == 'undefined') || (!$title || $title == 'undefined')) {
  echo json_encode($data);
  exit();
}

if ($cfg['musixmatch_api_key']) {
  $url = "https://api.musixmatch.com/ws/1.1/track.search?format=json&callback=x&q_track=$titleUrl&q_artist=$artistUrl&page_size=20&page=1&quorum_factor=1&apikey=" . $cfg['musixmatch_api_key'];
  $opts = array(
      'http' => array('ignore_errors' => true)
    );
  $context = stream_context_create($opts);

  $response = file_get_contents($url, false, $context);
  if ($response) {
    $data['url'] = $url;
    $data['response']['instrumental'] = 0;
    //$data['response_raw'] = $response;
    $data['response'] = json_decode($response, true);
    $tracks = count($data['response']['message']['body']['track_list']);
    $maxTry = 20;
    if ($tracks < $maxTry) {
      $maxTry = $tracks;
    }
    $data['musixmatch_tracks'] = $tracks;
    
    for ($i=0; $i<$maxTry; $i++) {
      $data['musixmatch_iterations'] = $i;
      
      if ($data['response']['message']['body']['track_list'][$i]) {
        $data['response']['source'] = "Musixmatch";
        $data['response']['url'] = $data['response']['message']['body']['track_list'][$i]['track']['track_share_url'];
        $data['response']['title'] = $data['response']['message']['body']['track_list'][$i]['track']['track_name'];
        $data['response']['artist'] = $data['response']['message']['body']['track_list'][$i]['track']['artist_name'];
        $data['response']['instrumental'] = $data['response']['message']['body']['track_list'][$i]['track']['instrumental'];
        if ($data['response']['instrumental'] == 1) {
          $data['result'] = 'ok';
          break;
        }
        
        if ($data['response']['url']) {
          require_once('PHPsimpleHTMLDomParser/simple_html_dom.php');
          $html = new simple_html_dom();
          $urlSearch = $data['response']['url'];

          $data['response']['lyrics'] = "";
          $html -> load_file($urlSearch);
          if ($html->root) {
            foreach($html->find ('script#__NEXT_DATA__') as $r){
              if($r->innertext){
                $jArr = array();
                $jArr = json_decode($r->innertext, true);
                $lyrics = $jArr['props']['pageProps']['data']['trackInfo']['data']['lyrics']['body'];
                break;
              };
            }
            $data['response']['lyrics'] .= nl2br($lyrics);
            
            if ($data['response']['lyrics'] !== "") {
              $data['result'] = 'ok';
              break;
            }
          }
        }
      }
    }
  }
}


if ($data['result'] == 'error') {
  $url = NJB_HOME_URL . "api/LyricsCore/index.php?artist=$artistUrl&title=$titleUrl&format=json";
  $data['url'] = $url;
  $opts = array(
      'http' => array('ignore_errors' => true)
    );
  $context = stream_context_create($opts);

  $response = file_get_contents($url, false, $context);

  if ($response) {
    //$data['response_raw'] = $response;
    $data['response'] = json_decode($response, true);
    if ($data['response']) {
      $data['result'] = 'ok';
    }
  }
}
echo json_encode($data);
?>