<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2021 Artur Sierzant                            |
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
//  | Tidal                                                                  |
//  +------------------------------------------------------------------------+

global $cfg, $db, $t;
global $base_size, $spaces, $scroll_bar_correction;

authenticate('access_media');

// formattedNavigator
$nav			= array();
$nav['name'][]	= 'Library';
$nav['url'][]	= 'index.php';
$nav['name'][]	= 'Tidal:';
require_once('include/header.inc.php');

$tileSize = $_GET['tileSizePHP'];

$conn = $t->connect();
$sessionId = '';
$countryCode = '';
if ($conn){
  $sessionId = $t->sessionId;
  $countryCode = $t->countryCode;
  $hp = $t->getHomePage_v2();
  if (count($hp['items']) < 1) {
    echo ("Error getting Home Page from Tidal");
    require_once('include/footer.inc.php');
    exit();
  }
  if (strlen($hp['page']['cursor']) > 1){
    $hp2 = $t->getHomePage_v2($hp['page']['cursor']);
    if (count($hp2['items']) > 0) {
      foreach($hp2['items'] as $key1 => $row1){
        $hp['items'][]=$hp2['items'][$key1];
      }
    }
  }
}
?>
<div class="area">
<?php
$favIdxCounter = 0;
foreach($hp['items'] as $key => $row){
  $mod = $hp['items'][$key];
  //echo count($hp['items']);
  if ($mod['type'] == 'HORIZONTAL_LIST' && strtolower($mod['items'][0]['type']) == 'album') {
    $headerTitile = $mod['title'] . '<a href="index.php?action=viewMoreFromTidal_v2&type=album_list&apiPath=' . urlencode($mod['viewAll']) . '&moduleName=' . urldecode($mod['title']) . '"> (more...)</a>';
    if (strtolower($mod['preTitle']) == 'because you listened to') {
      $cover = $t->albumCoverToURL($mod['header']['item']['cover'],'lq');
      $headerTitile = '<img class="pointer" style="width: 3em; float: left; margin: 0 7px 5px 0;" src="' . $cover . '" onclick=\'location.href="index.php?action=view3&album_id=tidal_' . $mod['header']['item']['id'] .'"\'><div style="line-height: 1.5em">Because you listened to <br><a href="index.php?&action=view3&album_id=tidal_' . $mod['header']['item']['id'] . '"> `' . urldecode($mod['title']) . '`</a> by <a href="index.php?action=view2&artist=' . $mod['header']['item']['artists'][0]['name'] .'">' . urldecode($mod['header']['item']['artists'][0]['name']) . '</a>';
    }
    echo '<h1>' . $headerTitile . '</h1>';
    echo '<div class="full" id="' . $mod['moduleId'] . '">';
    foreach($mod['items'] as $res) {
      $albums = array();
      $albums['album_id'] = 'tidal_' . $res['data']['id'];
      $albums['album'] = $res['data']['title'];
      $albums['cover'] = $t->albumCoverToURL($res['data']['cover'],'lq');
      $albums['artist_alphabetic'] = $res['data']['artists'][0]['name'];
      if ($cfg['show_album_format']) {
        //$albums['audio_quality'] = $res['data']['audioQuality'];
        $albums['audio_quality'] = getTidalAudioQualityMediaMetadata($res['data']);
      }
      draw_tile ( $tileSize, $albums, '', 'echo', $res['data']['cover'] );
    }
    echo '</div>';
  } //ALBUM_LIST
  
  if ($mod['type'] == 'HORIZONTAL_LIST' && strtolower($mod['items'][0]['type']) == 'mix') {
    $headerTitile = $mod['title'] . '<a href="index.php?action=viewMoreFromTidal_v2&type=mixlist_list&apiPath=' . urlencode($mod['viewAll']) . '&moduleName=' . urldecode($mod['title']) . '"> (more...)</a>';
    echo '<h1>&nbsp;' . $headerTitile . '</h1>';
    echo '<div class="full" id="' . $mod['moduleId'] . '">';
    foreach($mod['items'] as $res) {
      $albums = array();
      $albums['album_id'] = 'tidal_' . $res['data']['id'];
      $albums['album'] = $res['data']['titleTextInfo']['text'];
      $albums['cover'] = $res['data']['mixImages'][0]['url'];
      $albums['artist_alphabetic'] = $res['data']['subtitleTextInfo']['text'];
      //draw_Tidal_tile ( $tileSize, $albums, '', 'echo', $res['images']['SMALL']['url'], "mixlist");
      draw_tile ( $tileSize, $albums, '', 'echo', $res['data']['mixImages'][0]['url'], "mixlist");
    }
    echo '</div>';
  } //MIX_LIST
       
  if ($mod['type'] == 'HORIZONTAL_LIST' && strtolower($mod['items'][0]['type']) == 'playlist') {
    $headerTitile = $mod['title'] . '<a href="index.php?action=viewMoreFromTidal_v2&type=playlist_list&apiPath=' . urlencode($mod['viewAll']) . '&moduleName=' . urldecode($mod['title']) . '"> (more...)</a>';
    echo '<h1>&nbsp;' . $headerTitile . '</h1>';
    echo '<div class="full" id="' . $mod['moduleId'] . '">';
    foreach($mod['items'] as $res) {
      $albums = array();
      $albums['album_id'] = 'tidal_' . $res['data']['uuid'];
      $albums['album'] = $res['data']['title'];
      $albums['cover'] = $t->albumCoverToURL($res['data']['squareImage'],"lq");
      if (!$albums['cover']) {
        $albums['cover'] = $t->albumCoverToURL($res['data']['image'],'');
      }
      $albums['artist_alphabetic'] = getTidalPlaylistCreator($res['data']);
      //draw_Tidal_tile ( $tileSize, $albums, '', 'echo', $albums['cover'],"playlist");
      draw_tile ( $tileSize, $albums, '', 'echo', $albums['cover'],"playlist");
    }
    echo '</div>';
  } //PLAYLIST_LIST
  
  
  if ($mod['type'] == 'TRACK_LIST') {
    if ($favIdxCounter == 0) {
      $favIdxCounter = 40000;
    }
    else {
      $favIdxCounter = $favIdxCounter + 10000;
    }
    $divId = str_replace(' ','_',$mod['title']);
    $headerTitile = $mod['title'] . '<a href="index.php?action=viewMoreFromTidal_v2&type=track_list&apiPath=' . urlencode($mod['viewAll']) . '&moduleName=' . urldecode($mod['title']) . '"> (more...)</a>';
    
    $newTracks = $mod['items'];
    $tracksList = tidalTracksList_v2($newTracks, $favIdxCounter);
 
    echo '<h1>&nbsp;' . $headerTitile . '</h1>';
    echo '<div id="' . $divId . '">';
    echo ($tracksList);
    echo '</div>';
?>
  <script>
    $( "#<?php echo $divId; ?>" ).css("height","auto");
    setAnchorClick();
    addFavSubmenuActions();
  </script>
<?php
    
  } //TRACK_LIST
} //foreach

?>


<h1 id="suggested_artists_for_you_header">&nbsp;Suggested artists for you</h1>
<div id="suggested_artists_for_you" class="full">
  <div style="display: grid; height: 100%;">
    <span id="albumsLoadingIndicator" style="margin: auto;">
      <i class="fa fa-cog fa-spin icon-small"></i> <span class="add-info-left">Loading artists from Tidal...</span>
    </span>
  </div>
</div>

<?php
if ($conn === true){
  $artists = $t->getSuggestedArtistsForYou(5, 0, false);
  if ($artists['items']){
    $artistList = '<div class="artist_bio_related" style="line-height: initial;">';
    $img = '<i class="fa fa-user" style="font-size: 6em;"></i>';
    $i = 0;
    foreach($artists['items'] as $artist) {
      if ($artist["picture"]) {
        $img = '<img src="' . $t->artistPictureToURL($artist["picture"]) . '">';
      }
      else {
        $img = '<i class="fa fa-user" style="font-size: 6em;"></i>';
      }
      $artistList .= '<div class="artist_related" onmouseover="return overlib(\'' . $artist["name"] . '\', CAPTION , \'Go to artist\');" onmouseout="return nd();"><a href="index.php?tileSizePHP=' . $tileSize . '&action=view2&artist=' . urlencode($artist["name"]) . '&order=year"><div class="artist_container_small">' . $img . '</div><div>' . $artist["name"] . '</div></a></div>';
    }
    $artistList .= '</div>';
?>
  <script>
    $( "#suggested_artists_for_you" ).html(<?php echo safe_json_encode($artistList); ?>);
  </script>
<?php
  }
  else {
?>
  <script>
    $('[id^="suggested_artists_for_you"]').hide();
    //$("#suggested_artists_for_you").html('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on TIDAL.</span>');
  </script>
<?php
  }
}
else {
?>
  <script>
    $("#suggested_artists_for_you").html('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution Tidal request.<br>Error message:<br><br><?php echo $conn["error"];?></div>');
  </script>
<?php
}

?>


<?php

if ($conn === true){
  $playlists = $t->getUserPlaylists();
  if ($playlists['totalNumberOfItems'] > 0) {
?>
    <h1>&nbsp;Your playlists</h1>
    <table cellspacing="0" cellpadding="0" class="border tabFixed break-word">
    <?php
      tidalUserPlaylists($playlists);
  }
?>
    </table>
<?php
}

if ($conn === true){
  $mixlists = $t->getUserMixlists();
  if (count($mixlists['items']) > 0) {
?>
    <h1>&nbsp;Your Mixlists & Radios</h1>
    <table cellspacing="0" cellpadding="0" class="border tabFixed break-word">
    <?php
      tidalUserMixlists($mixlists);
  }
?>
    </table>
<?php
}

$query = mysqli_query($db,"SELECT * FROM album WHERE album_id IN (SELECT album_id FROM album_id WHERE path LIKE 'tidal_%') ORDER BY album_add_time DESC LIMIT 14");
if (mysqli_num_rows($query) > 0) {
?>
<h1>&nbsp;Albums from Tidal added to local library <a href="index.php?action=viewAlbumsFromStreamingService&service=Tidal">(more...)</a></h1>
<div class="albums_container">
<?php
while ($album = mysqli_fetch_assoc($query)){
    draw_tile($tileSize, $album);
  }
?>
</div>

<?php
}
echo '</div>'; //<div class="area">
require_once('include/footer.inc.php');

?>