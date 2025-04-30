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


global $cfg, $db;

?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
  <td class="space"></td>
	<td>Display options</td>
	<td class="textspace"></td>
	<td style="min-width: 40%"></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td class="space"></td>
	<td>Show 'Albums not played for more than 3 months' on Library main page</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_suggested'],'show_suggested');?></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td class="space"></td>
	<td>Show 'Recently played albums' on Library main page</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_last_played'],'show_last_played');?></td>
  <td class="space"></td>
</tr>
<tr class="odd">
<td class="space"></td>
	<td>Show miniplayer at bottom of every the page</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_miniplayer'],'show_miniplayer');?></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td class="space"></td>
	<td>Show 'play' and 'add' buttons at albums covers</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_quick_play'],'show_quick_play');?></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td class="space"></td>
	<td>Show album format badge on album cover</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_album_format'],'show_album_format');?></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td class="space"></td>
	<td>Show popularity bar on album cover</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_album_popularity'],'show_album_popularity');?></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td class="space"></td>
	<td>Show discography browser in album view</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_discography_browser'],'show_discography_browser');?></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td class="space"></td>
	<td>Show other versions of album in album view</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_album_versions'],'show_album_versions');?></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td class="space"></td>
	<td>Show 'Download album' in album view ('Download album' section in <a href="config.php?action=editSettings">Advanced settings</a> must be properly configured)</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['album_download'],'album_download');?></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td class="space"></td>
	<td>Show 'Copy album' in album view ('External storage' section in <a href="config.php?action=editSettings">Advanced settings</a> must be properly configured)</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['album_copy'],'album_copy');?></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td class="space"></td>
	<td>Show disc number in album view</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_album_disc_number'],'show_album_disc_number');?></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td class="space"></td>
	<td>Show all of the multi-disc albums in album view</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_multidisc'],'show_multidisc');?></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td class="space"></td>
	<td>Group multi-disc albums into one in search results</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['group_multidisc'],'group_multidisc');?></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td class="space"></td>
	<td>Show track composer in Now Playing</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_composer'],'show_composer');?></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td class="space"></td>
	<td>Show DR (dynamic range) colum for track in Now Playing, album view and search results</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_DR'],'show_DR');?></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td></td>
	<td>Number of albums displayed per page</td>
	<td></td>
	<td><input data-name="max_items_per_page" type="text" id="cfg_max_items_per_page" value="<?php echo $cfg['max_items_per_page']; ?>" size="3" class="center"></td>
  <td class="space"></td>
</tr>


<tr class="header">
  <td class="space"></td>
	<td>Playback options</td>
	<td class="textspace"></td>
	<td></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td class="space"></td>
	<td>Automatic start playing when "Add" a track or album to a empty playlist</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['add_autoplay'],'add_autoplay');?></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td class="space"></td>
	<td>Queue files to the playlist with "Play" and start playing the last queued track or album</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['play_queue'],'play_queue');?></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td></td>
	<td>Play queue limit</td>
	<td></td>
	<td><input data-name="play_queue_limit" type="text" id="cfg_play_queue_limit" value="<?php echo $cfg['play_queue_limit']; ?>" size="3" class="center"></td>
  <td class="space"></td>
</tr>


<tr class="header">
  <td class="space"></td>
	<td>Media update options</td>
	<td class="textspace"></td>
	<td></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td></td>
	<td>Media directory (use a UNIX style directory scheme with a trailing slash)
  </td>
	<td></td>
	<td><input data-name="media_dir" type="text" id="cfg_media_dir" value="<?php echo $cfg['media_dir']; ?>" style="width: 100%;" class="center"></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td class="space"></td>
	<td>Add values from STYLE tag to genres</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['style_enable'],'style_enable');?></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td></td>
	<td>String separating genres and styles in GENRE and STYLE tags</td>
	<td></td>
	<td><input data-name="multigenre_separator" type="text" id="cfg_multigenre_separator" value="<?php echo $cfg['multigenre_separator']; ?>" size="3" class="center"></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td class="space"></td>
	<td>Use COMMENT as tags and display it in album view</td>
	<td class="textspace"></td>
	<td><?php setChkBox($cfg['show_comments_as_tags'],'show_comments_as_tags');?></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td></td>
	<td>Separator in COMMENT splitting COMMENT into tags</td>
	<td></td>
	<td><input data-name="tags_separator" type="text" id="cfg_tags_separator" value="<?php echo $cfg['tags_separator']; ?>" size="3" class="center"></td>
  <td class="space"></td>
</tr>


<tr class="header">
  <td class="space"></td>
	<td>Login options</td>
	<td class="textspace"></td>
	<td></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td></td>
	<td>Default username and password that will be automatically entered into login form.<br> If empty, 'Anonymous user' (below) will be used as username</td>
	<td></td>
	<td>User name:<br><input data-name="default_username" type="text" id="cfg_default_username" value="<?php echo $cfg['default_username']; ?>" size="8" class="center"><br><br>
  Password:<br><input data-name="default_password" type="text" id="cfg_default_password" value="<?php echo $cfg['default_password']; ?>" size="8" class="center"></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td></td>
	<td>Anonymous user</td>
	<td></td>
	<td><input data-name="anonymous_user" type="text" id="cfg_anonymous_user" value="<?php echo $cfg['anonymous_user']; ?>" size="8" class="center"></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td></td>
	<td>Session lifetime (in seconds)</td>
	<td></td>
	<td><input data-name="session_lifetime" type="text" id="cfg_session_lifetime" value="<?php echo $cfg['session_lifetime']; ?>" size="8" class="center"></td>
  <td class="space"></td>
</tr>
<!--
<tr class="odd">
  <td></td>
	<td>Music Player Daemon password (leave empty if not used)</td>
	<td></td>
	<td><input data-name="mpd_password" type="text" id="cfg_mpd_password" value="<?php echo $cfg['mpd_password']; ?>" size="8" class="center"></td>
  <td class="space"></td>
</tr>
-->

<tr class="header">
  <td class="space"></td>
	<td>Youtube options</td>
	<td class="textspace"></td>
	<td></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td></td>
	<td>Show Youtube search results</td>
	<td></td>
	<td><?php setChkBox($cfg['show_youtube_results'],'show_youtube_results');?></td>
  <td class="space"></td>
</tr>
<tr class="odd">
  <td></td>
	<td>Google API key</td>
	<td></td>
	<td><input data-name="youtube_key" type="text" id="cfg_youtube_key" value="<?php echo $cfg['youtube_key']; ?>" style="width: 100%;" class="center"></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td></td>
	<td>Max displayed results</td>
	<td></td>
	<td><input data-name="youtube_max_results" type="text" id="cfg_youtube_max_results" value="<?php echo $cfg['youtube_max_results']; ?>" size="4" class="center"></td>
  <td class="space"></td>
</tr>


<tr class="header">
  <td class="space"></td>
	<td>Quick search</td>
	<td class="textspace"></td>
	<td></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td></td>
  <td colspan="4">More info about quick search can be found <a href="https://ompd.pl/quick-search" target="_blank">here</a>.</td>
</tr>
<tr class="textspace"><td colspan="5">
<table cellspacing="0" cellpadding="0" class="border" id="qs">
<tr class="header" id="qs_header">
  <td class="space"></td>
  <td class="icon">#&nbsp;</td>
  <td>Name</td>
  <td style="width: 100%;">Query</td>
  <td class="icon"></td>
</tr>
<?php 
$query = mysqli_query($db, "SELECT * FROM config WHERE name='quick_search' ORDER BY `index`");
while ($quick_search = mysqli_fetch_assoc($query)) {
  $idx = $quick_search['index'];
  $value = json_decode($quick_search['value']);
?>
<tr class="even" id="qs_row_<?php echo $idx; ?>">
<td></td>
<td class="icon"><?php echo $idx; ?>&nbsp;</td>
<td><input id="qs_name_<?php echo $idx; ?>" type="text" value="<?php echo $value[0]; ?>" size="12"></td>
<td><input id="qs_value_<?php echo $idx; ?>" type="text" value="<?php echo $value[1]; ?>" style="width: 100%;"></td>
<td><i class="fa fa-times-circle fa-fw icon-small pointer" id="qs_del_<?php echo $idx; ?>"></i></td>
</tr>
<?php
  }
?>
<tr class="even" id="qs_add">
<td></td>
<td></td>
<td></td>
<td></td>
<td><i class="fa fa-plus-circle fa-fw icon-small pointer" id="qs_add_new"></i></td>
</tr>
</table>
</td></tr>

<tr class="textspace"><td colspan="5"></td></tr>
</table>


<table>
<tr>
  <td class="space"></td>
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

$("i[id^='qs_del_']").click(function() {
  var iId = $(this).attr("id");
  var splitted = iId.split("_");
  var idx = splitted[2];
  $("#qs_row_" + idx).remove();
})

$("#qs_add_new").click(function() {
  var iId = $('#qs tr').last().prev().attr("id");
  if (iId == "qs_header") {
    idx = 1;
  } else {
    var splitted = iId.split("_");
    var idx = parseInt(splitted[2]) + 1;
  }
  var newRow = '<tr class="even" id="qs_row_' + idx + '"><td></td><td class="icon">' + idx + '&nbsp;</td><td><input id="qs_name_' + idx + '" type="text" value="" size="12"></td><td><input id="qs_value_' + idx + '" type="text" value="" style="width: 100%;"></td><td><i class="fa fa-times-circle fa-fw icon-small pointer" id="qs_del_' + idx + '"></i></td></tr>';
  $('#qs_add').before(newRow);
  $('#qs_del_' + idx).click(function() {
    $("#qs_row_" + idx).remove();
  })
})

function validateNumber(item, value){
  //validate number-type input
  console.log ("validating: " + item + " " + value);
  if (!isNaN(parseInt(item.val()))) {
    if (item.val() < 1) {
      item.val(value);
    }
    else {
      item.val(Math.ceil(parseInt(item.val())));
    }
  }
  else {
    item.val(value);
  };
}

$("#saveSettings").click(function(){
  validateNumber($("#cfg_max_items_per_page"),63);
  validateNumber($("#cfg_play_queue_limit"),250);
  validateNumber($("#cfg_session_lifetime"),31536000);
  validateNumber($("#cfg_youtube_max_results"),30);
  var config = {};
  var qs = {};
  qs[0] = 'empty'; //indicates that quick_search has no values
  
  $("i[id^='cfg_']").each(function(){
    config[$(this).attr("data-name")] = $(this).attr("data-val");
  })
  
  $("input[id^='cfg_']").each(function(){
    config[$(this).attr("data-name")] = $(this).val();
  })
  
  $("input[id^='qs_name_']").each(function(){
    qs[0] = 1; //indicates that quick_search has some settings
    var splitted = $(this).attr("id").split("_");
    var idx = splitted[2];
    qs[idx] = [$(this).val(),$('#qs_value_' + idx).val()];
  })
  config['quick_search'] = qs;
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
