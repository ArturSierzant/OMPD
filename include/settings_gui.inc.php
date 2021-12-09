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


global $cfg, $db;

function setChkBox ($value, $item) {
  if ($value === true) {
    echo '<i data-name="' . $item . '" data-val="true" id="cfg_' . $item . '" class="fa fa-check-circle-o fa-fw icon-small"></i>';
  }
  if ($value === false) {
    echo '<i data-name="' . $item . '" data-val="false" id="cfg_' . $item . '" class="fa fa-circle-o fa-fw icon-small"></i>';
  }
  
}
?>
<h1>Display options</h1>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="textspace"><td colspan="4"></td></tr>
<tr>
  <td class="space"></td>
	<td style="/*width: 100%;*/">Show 'Albums not played for more than 3 months' on Library main page</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_suggested'],'show_suggested');?></td>
  <td class="space"></td>
</tr>
<tr>
  <td class="space"></td>
	<td>Show 'Recently played albums' on Library main page</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_last_played'],'show_last_played');?></td>
  <td class="space"></td>
</tr>
<tr>
<td class="space"></td>
	<td>Show miniplayer at bottom of every the page</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_miniplayer'],'show_miniplayer');?></td>
  <td class="space"></td>
</tr>
<tr>
<td class="space"></td>
	<td>Show 'play' and 'add' buttons at albums covers</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_quick_play'],'show_quick_play');?></td>
  <td class="space"></td>
</tr>
<td class="space"></td>
	<td>Show album format badge on album cover</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_album_format'],'show_album_format');?></td>
  <td class="space"></td>
</tr>
<td class="space"></td>
	<td>Show popularity bar on album cover</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_album_popularity'],'show_album_popularity');?></td>
  <td class="space"></td>
</tr>
<td class="space"></td>
	<td>Show discography browser in album view</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_discography_browser'],'show_discography_browser');?></td>
  <td class="space"></td>
</tr>
<td class="space"></td>
	<td>Show other versions of album in album view</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_album_versions'],'show_album_versions');?></td>
  <td class="space"></td>
</tr>
<td class="space"></td>
	<td>Show all of the multi-disc albums in album view</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_multidisc'],'show_multidisc');?></td>
  <td class="space"></td>
</tr>
<td class="space"></td>
	<td>Group multi-disc albums into one in search results</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['group_multidisc'],'group_multidisc');?></td>
  <td class="space"></td>
</tr>
<td class="space"></td>
	<td>Show track composer in Now Playing</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_composer'],'show_composer');?></td>
  <td class="space"></td>
</tr>
<td class="space"></td>
	<td>Show DR (dynamic range) colum for track in Now Playing, album view and search results</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_DR'],'show_DR');?></td>
  <td class="space"></td>
</tr>

<tr class="textspace"><td colspan="5"></td></tr>
<tr>
  <td></td>
	<td>
  <div class="buttons">
  <span id="saveSettings" onmouseover="return overlib('Save settings');" onmouseout="return nd();">&nbsp;<i class="fa fa-floppy-o fa-fw"></i> Save settings</span>
  </div>
  </td>
	<td></td>
	<td></td>
  <td class="space"></td>
</tr>
</table>

<script>
$("i[id^='cfg_']").click(function() {
  if ($(this).hasClass("fa-check-circle-o")) {
    $(this).removeClass("fa-check-circle-o").addClass("fa-circle-o");
    $(this).attr("data-val","false");
  }
  else if ($(this).hasClass("fa-circle-o")) {
    $(this).removeClass("fa-circle-o").addClass("fa-check-circle-o");
    $(this).attr("data-val","true");
  }
})

$("#saveSettings").click(function(){
  var config = {};
    $("i[id^='cfg_']").each(function(){
      config[$(this).attr("data-name")] = $(this).attr("data-val");
    })
    //console.log(config);
  $.ajax ({
    url: "ajax-save-config.php",  
    type: "POST",  
    data: { settings : config },  
    dataType: "json",
    success: function(json) {
      if (json.return == 1) {
        $('#saveSettings > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $('#saveSettings > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-floppy-o');
        }, 2000);
        return;
      }
      else {
        $('#saveSettings > i').removeClass('fa-cog fa-spin').addClass('fa-check-square');
        setTimeout(function() {
          $('#saveSettings > i').removeClass('fa-check-square').addClass('fa-floppy-o');
        }, 2000);
        //location.reload();
        return;
      }
      //location.reload();
    },
    error: function() {
      $('#saveSettings > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $('#saveSettings > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-floppy-o');
        }, 2000);
    }
  })
})
</script>
