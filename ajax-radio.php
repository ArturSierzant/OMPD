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

global $cfg;

use AdinanCenci\RadioBrowser\RadioBrowser;

require_once 'vendor/autoload.php';
require_once('include/initialize.inc.php');
require_once('include/library.inc.php');

$name = $_POST['name'];
$tag = $_POST['tag'];
$countrycode = $_POST['countrycode'];
$action = $_GET['action'];
$picUrl = $_GET['picUrl'];
$streamUrl = $_GET['streamUrl'];
$orderBy = 'votes';
$reverse = true;
if ($name) {
  $orderBy = 'name';
  $reverse = false;
}
$limit = 1000;

authenticate('access_media');

if ($action == 'savePic') {
  echo savePic($picUrl, $streamUrl);
  return;
}

$browser = new RadioBrowser();

if ($countrycode == '0') $countrycode = null;

$searchTerms = array('tag'=>$tag,'name'=>$name,'countrycode'=>$countrycode, 'limit'=>$limit,'order'=>$orderBy,'reverse'=>$reverse);

$stations = $browser->searchStation($searchTerms);

$stationsCount = count($stations);

if ($stationsCount == 0){
  ?>
  <script>
     $("#stationsCount").html('');
  </script>
  <br>No matching stations found.
  <?php
  return;
}

$disc = 1;

?>
<table id="playlist-table<?php echo $disc; ?>" cellspacing="0" cellpadding="0" class="border">
  <tr class="header">
    <td class="small_cover_md"></td>
    <td class="icon"></td>
    <td>Name</td>
    <td class="pl-genre">Tags</td>
    <td class="pl-genre">Web</td>
    <td>Quality</td>
    <td class="pl-genre">Votes</td>
    <td></td>
  </tr>
  <?php

$i = 0;
$tagId = 0;

foreach ($stations as $track) { 
?>
  <tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
    <?php 
    $position_id = $i + $disc * 100;
    $url = $track['url'];
    if ($track['url_resolved']) {
      $url = $track['url_resolved'];
    }
    $picUrl = $track['favicon'];
    ?>
    <td class="small_cover_md">
      <?php 
      if ($track['favicon']) {
      ?>
      <img loading="lazy" decoding="async"
        src="image.php?source=radio&amp;image_url=<?= urlencode($track['favicon']); ?>" alt="" width="100%">
      <?php
      }
      ?>
    </td>

    <td class="icon">
      <?php 
    if ($cfg['access_add'])  echo '<span id="add_' . $position_id . '" streamUrl="' . $url . '" picUrl="' . $picUrl . '" class="pointer" onMouseOver="return overlib(\'Add stream\');" onMouseOut="return nd();"><i class="fa fa-plus-circle fa-fw icon-small"></i></span>'; 
    ?>

    </td>

    <td class="time"><?php 
    
    if ($cfg['access_play']) 		echo '<span id="a_play_track'. $position_id .'" class="pointer" streamUrl="' . ($url) . '" picUrl="' . $picUrl . '" position_id="' . $position_id . '"><div class="playlist_title break-word">' . html($track['name']) . '</div><div class="playlist_title_album break-all favoritePlaylistDescription">' . html($track['url']) . '</div></span>';
    // if ($cfg['access_play']) 		echo '<a id="a_play_track'. $position_id .'" href="javascript:ajaxRequest(\'play.php?action=playStreamDirect&amp;playAfterInsert=yes&amp;url=' . urlencode($url) . '&amp;position_id=' . $position_id . '\',evaluateAdd);" onMouseOver="return overlib(\'Play stream ' . $track['number'] . '\');" onMouseOut="return nd();"><div class="playlist_title break-word">' . html($track['name']) . '</div><div class="playlist_title_album break-all favoritePlaylistDescription">' . html($track['url']) . '</div></a>';
    
    else echo html($track['name']);
    
    ?>

    </td>
    <td class="track-list-artist">
      <div>
        <?php 
      $sp = explode(",",$track['tags']);
      foreach($sp as $s){
        if ($s){
          echo '<span id="tagId' . $tagId . '" class="artist_all pointer" style="white-space: break-spaces; margin-bottom: 4px;">' . $s .'</a></span>';
          $tagId ++;
        }
      } 
      ?>
      </div>
    </td>
    <td class="time pl-genre">
      <?php if($track['homepage'] !== '') { ?>
      <a href="<?= $track['homepage'] ?>" target="_NEW"><i class="fa fa-globe icon-small" aria-hidden="true"></i>
      </a>
      <?php }; ?>
    </td>
    <td>
      <?php if ($track['codec'] != "UNKNOWN") $codec = $track['codec']; 
      else $codec = "---";
      if ($track['bitrate'] != "0") $bitrate = "@" . $track['bitrate']; 
      else $bitrate = "";
      echo $codec . $bitrate;
      ?>
    </td>

    <td class="pl-genre">
      <?php echo $track['votes']; ?>
    </td>

    <?php
    
    $isFavorite = false;
    $isBlacklist = false;
    $tid = $track['changeuuid'];
  
    if ($track['favorite_pos']) $isFavorite = true;
    if ($track['blacklist_pos']) $isBlacklist = true;
    ?>
    <td></td>
<!--     <td onclick="toggleStarSub(<?php echo $i + $disc * 100 ?>,'<?php echo $tid ?>');" class="pl-favorites">
      <span id="blacklist-star-bg<?php echo $tid ?>"
        class="<?php if ($isBlacklist) echo ' blackstar blackstar-selected'; ?>">
        <i class="fa fa-star<?php if (!$isFavorite) echo '-o'; ?> fa-fw" id="favorite_star-<?php echo $tid; ?>"></i>
      </span>
    </td> -->

  </tr>
  <tr class="line">
    <td></td>
    <td colspan="16"></td>
  </tr>

  <tr>
    <td colspan="10">
      <?php starSubMenu($i + $disc * 100, $isFavorite, $isBlacklist, $tid);?>
    </td>
  </tr>

  <tr>
    <td colspan="10">
      <?php trackSubMenu($i + $disc * 100, $track, $album_id);?>
    </td>
  </tr>
  <?php
}
?>
</table>
<script>
  <?php
  $s = '';
  if ($stationsCount > 1) $s="s";
  if ($stationsCount > 999) {
  ?>
    $("#stationsCount").html(" - over 1000 stations found:");
  <?php
  }
  else {
  ?>
    $("#stationsCount").html(" - <?= $stationsCount; ?> station<?= $s; ?> found:");
  <?php
  }
  ?>

  $("span[id^='tagId']").on('click', (function() {
    $("#stationsCount").html('');
    $("#name").val('');
    $("#tag").val($(this).html());
    searchRadio();
  }));

  $("span[id^='a_play_track']").click(function() {

    savePic($(this).attr('picUrl'), $(this).attr('streamUrl'));

    $.ajax({
      type: "GET",
      url: "play.php",
      data: {
        'action': 'playStreamDirect',
        'playAfterInsert': 'yes',
        'url': $(this).attr('streamUrl'),
        'position_id': $(this).attr('position_id')
      },
    });
  })


  $("span[id^='add_']").click(function() {

    savePic($(this).attr('picUrl'), $(this).attr('streamUrl'));

    $obj = $(this).find("i");
    $obj.removeClass('fa-plus-circle').addClass('fa-cog fa-spin');
    $.ajax({
      type: "GET",
      url: "play.php",
      data: {
        'action': 'addSelectUrl',
        'url': $(this).attr('streamUrl')
      },
      dataType: 'json',
      success: function(json) {
        if (json.addResult == 'add_OK') {
          $obj.removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
          setTimeout(function() {
            $obj.removeClass('fa-check-square icon-ok').addClass('fa-plus-circle');
          }, 2000);
        } else {
          $obj.removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-circle icon-nok');
          setTimeout(function() {
            $obj.removeClass('fa-exclamation-circle icon-nok').addClass('fa-plus-circle');
          }, 2000);
        }
      },
      error: function() {
        $obj.removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $obj.removeClass('fa-exclamation-circle icon-nok').addClass('fa-plus-circle');
        }, 2000);
      }
    });
  });

  function savePic(picUrl, streamUrl) {
    $.ajax({
      type: "GET",
      url: "ajax-radio.php",
      data: {
        'action': 'savePic',
        'picUrl': picUrl,
        'streamUrl': streamUrl
      },
    });
  }
</script>
<?php 

function savePic($picUrl, $streamUrl){
  global $cfg;
  if (!$picUrl) {
    return false;
  }
  $path = parse_url($picUrl, PHP_URL_PATH);
  $path_parts = explode('.', $path);
  $extention = end($path_parts);
  if ($extention == "ico"){
    $extention = "png";
  }
  if ($url_path = parse_url($streamUrl, PHP_URL_PATH)) {
    $url_path = str_replace('/','__',$url_path);
  }
  $imageFile = $cfg['stream_covers_dir'] . parse_url($streamUrl, PHP_URL_HOST) . $url_path . "." . $extention;

  $img = file_get_contents($picUrl);
  if (!$img){
    return false;
  }

  if (file_put_contents($imageFile, $img)) { 
    return "File downloaded successfully"; 
  } 
  else { 
    return "File downloading failed."; 
  } 
}
?>