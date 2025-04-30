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

global $cfg,$db;

//use AdinanCenci\RadioBrowser\RadioBrowser;

require_once('include/initialize.inc.php');
require_once('include/library.inc.php');

$name = $_POST['name'];
$tag = $_POST['tag'];
$countrycode = ($_POST['countrycode'] != "") ? substr($_POST['countrycode'], 0, 2) : null;
$action = $_POST['action'];
$picUrl = $_POST['picUrl'];
$streamUrl = $_POST['streamUrl'];
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
//$server = RadioBrowser::pickAServer();
//$browser = new RadioBrowser($server);
$browser = initRadioBrowser();

if ($action == 'searchRadios') {
  //if ($countrycode == '0') $countrycode = null;
  $searchTerms = array('tag'=>$tag,'name'=>$name,'countrycode'=>$countrycode, 'limit'=>$limit,'order'=>$orderBy,'reverse'=>$reverse);
  if ($browser){
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
  }
  else{
    ?>
    <script>
      $("#stationsCount").html('');
    </script>
    <br>Error in connection to the Radio Browser server.
    <?php
    return;
  }
}

elseif ($action == 'showSavedRadios') {
  $query = mysqli_query($db, "SELECT DISTINCT SUBSTRING_INDEX(stream_url,'ompd_stationuuid=',-1) AS 'stationuuid', stream_url FROM favoriteitem WHERE stream_url LIKE '%ompd_stationuuid=%'");
  $stationsCount = mysqli_num_rows($query);
  if ($stationsCount == 0){
    ?>
    <script>
    $("#stationsCount").html('');
    </script>
    <br>No saved stations found.
    <?php
    return;
  }
  else {
    $stations = array();
    while ($row = mysqli_fetch_assoc($query)) {
      $stationuuid = $row['stationuuid'];
      if ($browser){
        $station = $browser->getStationsByUuid($stationuuid);
      }
      if ($station) {
        $stations[] = $station[0];
      }
      else {
        $stations[] = array('name'=>$row['stream_url'],'url'=>$row['stream_url'],'tags'=>'','bitrate'=>'0','homepage'=>'','stationuuid'=>$stationuuid,'connection'=>'error');
      }
    }
  }
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

foreach ($stations as $track) {

  radioListItem($track, $i, $disc);
  $i++;
}
?>
</table>
<script>
  <?php
  $s = '';
  if ($stationsCount > 1) $s = "s";
  if ($stationsCount > 999) {
  ?>
    $("#stationsCount").html(" - over 1000 stations found:");
  <?php
  }
  else {
    if ($action == 'showSavedRadios') {
      ?>
      $("#stationsCount").html(" - <?= $stationsCount; ?> station<?= $s; ?> found in Favorites:");
      <?php
    }
    else {
      ?>
        $("#stationsCount").html(" - <?= $stationsCount; ?> station<?= $s; ?> found:");
      <?php
    }
  }
  ?>

  $("span[id^='tagId']").on('click', (function() {
    $("#stationsCount").html('');
    $("#name").val('');
    // $("#country").val('0');
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
      type: "POST",
      url: "ajax-radio.php",
      data: {
        'action': 'savePic',
        'picUrl': picUrl,
        'streamUrl': streamUrl
      },
    });
  }

 addFavSubmenuActions();

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