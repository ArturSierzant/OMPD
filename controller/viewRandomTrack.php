<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant                            |
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
//  | View random track                                                      |
//  +------------------------------------------------------------------------+
function viewRandomTrack() {
    global $cfg, $db;
    
    authenticate('access_media');
    
    // formattedNavigator
    $nav            = array();
    $nav['name'][]  = 'Library';
    $nav['url'][]   = 'index.php';
    $nav['name'][]  = 'Random';
    
    require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" style="width: 100%;">
<tr>
    <td>
<!--  -->
<table cellspacing="0" cellpadding="0" class="tab">
<tr>
    <td class="tab_off" onClick="location.href='index.php?action=viewRandomAlbum';">Album</td>
    <td class="tab_none tabspace"></td>
    <td class="tab_on" onClick="location.href='index.php?action=viewRandomTrack';">Track</td>
    <td class="tab_off" onClick="location.href='index.php?action=viewRandomFile';">File</td>
    <td class="tab_none tabspace"></td>
    <td class="tab_off" onClick="location.href='genre.php?action=blacklist';">Blacklist</td>
    <td class="tab_none">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" class="tab_border">
<?php
    if ($cfg['access_play'] || $cfg['access_add'] || $cfg['access_stream']) { ?>
<tr class="tab_header">
    <td></td>
    <td></td><!-- optional play -->
    <td></td><!-- optional add -->
    <td></td><!-- optional stream -->
    <td colspan="4"></td>
    <td></td>
</tr>
<!--
<tr class="odd mouseover">
    <td></td>
    <td class="icon"><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;random=new\');" onMouseOver="return overlib(\'Play random tracks\');" onMouseOut="return nd();"><i class="fa fa-play-circle-o fa-fw icon-small"></i></a>'; ?></td>
    <td class="icon"><?php if ($cfg['access_add']) echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;random=new\');" onMouseOver="return overlib(\'Add random tracks\');" onMouseOut="return nd();"><i class="fa fa-plus-circle fa-fw icon-small"></i></a>'; ?></td>
    <td class="icon"><?php if ($cfg['access_stream']) echo '<a href="stream.php?action=playlist&amp;random=new&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream random tracks\');" onMouseOut="return nd();"><i class="fa fa-rss fa-fw icon-small"></i></a>'; ?></td>
    <td></td>
    <td colspan="3"><?php if ($cfg['access_play'])      echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;random=new\');" onMouseOver="return overlib(\'Play random tracks\');" onMouseOut="return nd();">Play random tracks from list below:</a>';
    elseif ($cfg['access_add'])     echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=&amp;random=new\');" onMouseOver="return overlib(\'Add random tracks\');" onMouseOut="return nd();">Add random tracks from list below:</a>';
    elseif ($cfg['access_stream'])  echo '<a href="stream.php?action=playlist&amp;random=new&amp;stream_id=' . $cfg['stream_id']  . '" onMouseOver="return overlib(\'Stream random tracks\');" onMouseOut="return nd();">Stream random tracks from list below:</a>'; ?></td>
    <td></td>
</tr>
-->
<tr class="even mouseover">
    <td></td>
    <td class="icon"><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;random=database\');" onMouseOver="return overlib(\'Play playlist\');" onMouseOut="return nd();"><i class="fa fa-play-circle-o fa-fw icon-small"></i></a>'; ?></td>
    <td class="icon"><?php if ($cfg['access_add']) echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;random=database\',evaluateAdd);" onMouseOver="return overlib(\'Add playlist\');" onMouseOut="return nd();"><i id="add_random" class="fa fa-plus-circle fa-fw icon-small"></i></a>'; ?></td>
    <td class="icon"><?php if ($cfg['access_stream']) echo '<a href="stream.php?action=playlist&amp;random=database&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream playlist\');" onMouseOut="return nd();"><i class="fa fa-rss fa-fw icon-small"></i></a>'; ?></td>
    <td></td>
    <td colspan="3"><?php if ($cfg['access_play'])      echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;random=database\');" onMouseOver="return overlib(\'Play playlist\');" onMouseOut="return nd();">Play random list shown below:</a>';
    elseif ($cfg['access_add'])     echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=&amp;random=database\');" onMouseOver="return overlib(\'Add playlist\');" onMouseOut="return nd();">Add random list shown below:</a>';
    elseif ($cfg['access_stream'])  echo '<a href="stream.php?action=playlist&amp;random=database&amp;stream_id=' . $cfg['stream_id']  . '" onMouseOver="return overlib(\'Stream playlist\');" onMouseOut="return nd();">Stream random list shown below:</a>'; ?></td>
    <td></td>
</tr>
<tr class="line"><td colspan="9"></td></tr>
<?php
    } ?>
<tr class="tab_header">
    <td class="space"></td>
    <td></td><!-- optional play -->
    <td></td><!-- optional add -->
    <td></td><!-- optional stream -->
    <td<?php if ($cfg['access_play'] || $cfg['access_add'] || $cfg['access_stream']) echo' class="space"'; ?>></td>
    <td>&nbsp;&nbsp;Artist</td>
    <td class="textspace"></td>
    <td>Title</td>
    <td class="space"></td>
</tr>
<?php
    mysqli_query($db, 'DELETE FROM random WHERE sid = "' .  mysqli_real_escape_string($db,$cfg['sid']) . '"');
    
    $i = 0;
    $blacklist = explode(',', $cfg['random_blacklist']);
    $blacklist = '"' . implode('","', $blacklist) . '"';
    $query = mysqli_query($db, 'SELECT track.artist, title, track_id
        FROM track, album
        WHERE (genre_id = "" OR genre_id NOT IN (' . $blacklist . ')) AND
        audio_dataformat != "" AND
        video_dataformat = "" AND
        track.album_id = album.album_id
        ORDER BY RAND()
        LIMIT 30');
    while ($track = mysqli_fetch_assoc($query)) {
        mysqli_query($db, 'INSERT INTO random (sid, track_id, position, create_time) VALUES (
            "' .  mysqli_real_escape_string($db,$cfg['sid']) . '",
            "' .  mysqli_real_escape_string($db,$track['track_id']) . '",
            "' .  mysqli_real_escape_string($db,$i) . '",
            "' .  mysqli_real_escape_string($db,time()) . '")'); ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
    <td></td>
    <td class="icon"><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['track_id'] . '\');" onMouseOver="return overlib(\'Insert and play track\');" onMouseOut="return nd();"><i class="fa fa-play-circle-o fa-fw icon-small"></i></a>'; ?></td>
    <td class="icon"><?php if ($cfg['access_add']) echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();"><i id="add_' . $track['track_id'] . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>'; ?></td>
    <td class="icon"><?php if ($cfg['access_stream']) echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['track_id'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track\');" onMouseOut="return nd();"><i class="fa fa-rss fa-fw icon-small"></i></a>'; ?></td>
    <td></td>
    <td><?php if (mysqli_num_rows(mysqli_query($db, 'SELECT track_id FROM track WHERE artist="' .  mysqli_real_escape_string($db,$track['artist']) . '"')) > 1) echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['artist']) . '&amp;order=year">' . html($track['artist']) . '</a>'; else echo html($track['artist']); ?></td>
    <td></td>
    <td><?php if ($cfg['access_play'])      echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['track_id'] . '\');" onMouseOver="return overlib(\'Insert and play track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            elseif ($cfg['access_add'])     echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['track_id'] . '\');" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            elseif ($cfg['access_stream'])  echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['track_id'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            else                            echo html($track['title']); ?></td>
    <td></td>
</tr>
<?php
    } ?>
</table>
<!--  -->
    </td>
</tr>
</table>
<?php
    require_once('include/footer.inc.php');
}
