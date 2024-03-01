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

$ret = getTidalAPIkeys();
if (is_array($ret)){
  $keys = $ret['keys'];
  $count = count($keys);
  if ($count > 0) {
    $s = "";
    if ($count > 1) $s = "s";
  ?>
  <h1>Found <?= array_count_values(array_column($keys, 'valid'))['True']; ?> key<?= $s ?> marked as VALID:</h1>
  <table id="tidalApiKeys" class="border">
    
    <?php
    foreach($keys as $id => $key){
      if($key["valid"]=="True") {
        $from = explode("(",$key["from"]);
  ?>
    <tr>
      <td>&nbsp;Platform:</td>
      <td><?= $key["platform"];?></td>
    </tr>
    <tr>
      <td>&nbsp;Formats:</td>
      <td><?= $key["formats"];?></td>
    </tr>
    <tr>
      <td>&nbsp;client_id:</td>
      <td id="clientId<?= $id; ?>"><?= $key["clientId"];?></td>
    </tr>
    <tr>
      <td>&nbsp;client_secret:</td>
      <td id="clientSecret<?= $id; ?>" class="break-all"><?= $key["clientSecret"];?></td>
    </tr>
    <tr>
      <td>&nbsp;Source:</td>
      <td><a href="<?= rtrim($from[1],')') ;?>" target="_blank"><?= $from[0]; ?></a></td>
    </tr>
    <tr class="line">
      <td>
        <div class="buttons"><span id="<?= $id; ?>" style="width: 100%;">Use this</span></div>
      </td>
      <td>
        <div class="buttons"></div>
      </td>
    </tr>
    <?php
      }
    }
    echo "</table>";
  }
  ?>
    <script>
    $("#tidalKeys").slideDown("slow", function() {});
    $("#tidalApiKeys .buttons span").on("click", function() {
      $("#tidal_client_id").val($("#clientId" + $(this).attr("id")).html());
      $("#tidal_client_secret").val($("#clientSecret" + $(this).attr("id")).html());
    });
    </script>
<?php
}
else {
?>
  <h1><i class="fa fa-fw fa-exclamation-triangle icon-nok icon-small"></i>Error getting keys.</h1>
  <?= $ret; ?>
<?php
}
?>