<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2020 Artur Sierzant                            |
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
}
?>
<div class="area">
<h1>&nbsp;Featured new albums <a href="index.php?action=viewNewFromTidal&type=featured_new">(more...)</a></h1>
<script>
  calcTileSize();
  var size = $tileSize;
  var request = $.ajax({  
  url: "ajax-tidal-new-albums.php",  
  type: "POST",
  data: { type: "featured_new", tileSize : size, limit : 10, offset : 0, sessionId : "<?php echo $sessionId; ?>", countryCode : "<?php echo $countryCode; ?>" },
  dataType: "html"
  }); 

request.done(function(data) {
  if (data) {
    $( "#new_tidal" ).html(data);
  }
  else {
    $( "#new_tidal" ).html('<div style="line-height: initial;">Error loading albums from Tidal.</div>');
  }
});

</script>
<div class="full" id="new_tidal">
  <div style="display: grid; height: 100%;">
    <span id="albumsLoadingIndicator" style="margin: auto;">
      <i class="fa fa-cog fa-spin icon-small"></i> <span class="add-info-left">Loading albums from Tidal...</span>
    </span>
  </div>
</div>

<h1>&nbsp;Suggested new albums <a href="index.php?action=viewNewFromTidal&type=suggested_new">(more...)</a></h1>
<script>
  calcTileSize();
  var size = $tileSize;
  var request = $.ajax({  
  url: "ajax-tidal-new-albums.php",  
  type: "POST",
  data: { type: "suggested_new", tileSize : size, limit : 10, offset : 0, sessionId : "<?php echo $sessionId; ?>", countryCode : "<?php echo $countryCode; ?>" },
  dataType: "html"
  }); 

request.done(function(data) {
  if (data) {
    $( "#suggested_new" ).html(data);
  }
  else {
    $( "#suggested_new" ).html('<div style="line-height: initial;">Error loading albums from Tidal.</div>');
  }
});

</script>
<div class="full" id="suggested_new">
  <div style="display: grid; height: 100%;">
    <span id="albumsLoadingIndicator" style="margin: auto;">
      <i class="fa fa-cog fa-spin icon-small"></i> <span class="add-info-left">Loading albums from Tidal...</span>
    </span>
  </div>
</div>


<h1>&nbsp;Suggested new tracks <a href="index.php?action=viewNewFromTidal&type=suggested_new_tracks">(more...)</a></h1>
<div id="suggested_new_tracks">
  <div style="text-align: center;">
    <span id="tracksLoadingIndicator" style="margin: auto;">
      <i class="fa fa-cog fa-spin icon-small"></i> <span class="add-info-left" style="display: inline;">Loading tracks from Tidal...</span>
    </span>
  </div>
</div><br/>
<?php
if ($conn === true){
  $newTracks = $t->getSuggestedNewTracks(5, 0, false);
  if ($newTracks['items']){
    $tracksList = tidalTracksList($newTracks);
?>
  <script>
    $( "#suggested_new_tracks" ).html(<?php echo safe_json_encode($tracksList); ?>);
    $( "#suggested_new_tracks" ).css("height","auto");
    setAnchorClick();
    addFavSubmenuActions();
  </script>
<?php
  }
  else {
?>
  <script>
    $("#suggested_new_tracks").html('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on TIDAL.</span>');
  </script>
<?php
  }
}
else {
?>
  <script>
    $("#suggested_new_tracks").html('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution Tidal request.<br>Error message:<br><br><?php echo $conn["error"];?></div>');
  </script>
<?php
}
?>


<h1>&nbsp;Featured local albums <a href="index.php?action=viewNewFromTidal&type=featured_local">(more...)</a></h1>
<script>
  calcTileSize();
  var size = $tileSize;
  var request = $.ajax({  
  url: "ajax-tidal-new-albums.php",  
  type: "POST",
  data: { type: "featured_local", tileSize : size, limit : 10, offset : 0, sessionId : "<?php echo $sessionId; ?>", countryCode : "<?php echo $countryCode; ?>" },
  dataType: "html"
  }); 

request.done(function(data) {
  if (data) {
    $( "#new_local_tidal" ).html(data);
  }
  else {
    $( "#new_local_tidal" ).html('<div style="line-height: initial;">Error loading albums from Tidal.</div>');
  }
});

</script>
<div class="full" id="new_local_tidal">
  <div style="display: grid; height: 100%;">
    <span id="albumsLoadingIndicator" style="margin: auto;">
      <i class="fa fa-cog fa-spin icon-small"></i> <span class="add-info-left">Loading albums from Tidal...</span>
    </span>
  </div>
</div>

<h1>&nbsp;New albums for you <a href="index.php?action=viewNewFromTidal&type=new_for_you">(more...)</a></h1>
<script>
  calcTileSize();
  var size = $tileSize;
  var request = $.ajax({  
  url: "ajax-tidal-new-albums.php",  
  type: "POST",
  data: { type: "new_for_you", tileSize : size, limit : 10, offset : 0, sessionId : "<?php echo $sessionId; ?>", countryCode : "<?php echo $countryCode; ?>" },
  dataType: "html"
  }); 

request.done(function(data) {
  if (data) {
    $( "#new_for_you" ).html(data);
  }
  else {
    $( "#new_for_you" ).html('<div style="line-height: initial;">Error loading albums from Tidal.</div>');
  }
});

</script>
<div class="full" id="new_for_you">
  <div style="display: grid; height: 100%;">
    <span id="albumsLoadingIndicator" style="margin: auto;">
      <i class="fa fa-cog fa-spin icon-small"></i> <span class="add-info-left">Loading albums from Tidal...</span>
    </span>
  </div>
</div>

<h1>&nbsp;Suggested albums for you <a href="index.php?action=viewNewFromTidal&type=suggested_for_you">(more...)</a></h1>
<script>
  calcTileSize();
  var size = $tileSize;
  var request = $.ajax({  
  url: "ajax-tidal-new-albums.php",  
  type: "POST",
  data: { type: "suggested_for_you", tileSize : size, limit : 10, offset : 0, sessionId : "<?php echo $sessionId; ?>", countryCode : "<?php echo $countryCode; ?>" },
  dataType: "html"
  }); 

request.done(function(data) {
  if (data) {
    $( "#suggested_for_you" ).html(data);
  }
  else {
    $( "#suggested_for_you" ).html('<div style="line-height: initial;">Error loading albums from Tidal.</div>');
  }
});

</script>
<div class="full" id="suggested_for_you">
  <div style="display: grid; height: 100%;">
    <span id="albumsLoadingIndicator" style="margin: auto;">
      <i class="fa fa-cog fa-spin icon-small"></i> <span class="add-info-left">Loading albums from Tidal...</span>
    </span>
  </div>
</div>

<h1>&nbsp;Suggested artists for you</h1>
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
    $("#suggested_artists_for_you").html('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on TIDAL.</span>');
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
<tr class="header">

<td class="icon"></td><!-- optional play -->
<td class="icon"></td><!-- optional add -->
<td class="icon"></td><!-- optional stream -->
<td></td>
<td></td>
<td class="icon"></td><!-- optional delete -->
<td class="icon"></td>
<td class="space"></td>
</tr>
<?php
    for ($j = 0; $j < $playlists['totalNumberOfItems']; $j++) {
      $plName = $playlists['items'][$j]['title'];
      $plId = $playlists['items'][$j]['uuid'];
      //$plLastMod = $playlists['Last-Modified'][$j];
    ?>		
      <tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
        
        <td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playTidalPlaylist&amp;favorite_id=' . $plId . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Play\');" onMouseOut="return nd();"><i id="play_' . $plId . '" class="fa fa-play-circle-o fa-fw icon-small"></i></a>'; ?></td>
        
        <td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=addTidalPlaylist&amp;favorite_id=' . $plId . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Add to playlist\');" onMouseOut="return nd();"><i id="add_' . $plId . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>'; ?></td>
        
        <td>
        </td>
        
        <td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playTidalPlaylist&amp;favorite_id=' . $plId . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Play\');" onMouseOut="return nd();">' . html($plName) . '</a>';
            else echo html($plName); ?>
        </td>
        
        <td>
          <?php echo $playlists['items'][$j]['description']; ?></td>
        <td>
        </td>
        
        <td>
          <?php if ($cfg['access_admin']) echo '<a href="favorite.php?action=viewTidalPlaylist&amp;favorite_id=' . $plId . '&plName=' . $plName . '" onMouseOver="return overlib(\'See tracks\');" onMouseOut="return nd();"><i class="fa fa-list fa-fw icon-small"></i></a>'; ?>
        </td>
        
        <td></td>
      </tr>
    <?php
    }
  }
}
echo '</table>' . "\n";
echo '</div>'; //<div class="area">
require_once('include/footer.inc.php');

?>