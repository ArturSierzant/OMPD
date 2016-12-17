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
//  | View popular                                                           |
//  +------------------------------------------------------------------------+
function viewPopular() {
    global $cfg, $db;
    
    
    $period     = get('period');
    $user_id    = (int) get('user_id');
    $flag       = (int) get('flag');
    
    if      ($period == 'week')     $days = 7;
    elseif  ($period == 'month')    $days = 31;
    elseif  ($period == 'year')     $days = 365;
    elseif  ($period == 'overall')  $days = 365 * 1000;
    else                            message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]period');
    
    if ($user_id == 0) {
        authenticate('access_popular');
        
        // formattedNavigator
        $nav            = array();
        $nav['name'][]  = 'Library';
        $nav['url'][]   = 'index.php';
        $nav['name'][]  = 'Popular';
        
        $query_pop = mysqli_query($db, 'SELECT artist, artist_alphabetic, album, image_id, album.album_id, COUNT(*) AS counter
            FROM counter, album
            WHERE counter.flag <= 1
            AND counter.time > ' . (int) (time() - 86400 * $days) . '
            AND counter.album_id = album.album_id
            GROUP BY album.album_id
            ORDER BY counter DESC, time DESC
            LIMIT 50');
        //echo 'num_rows: ' . mysqli_num_rows($query);
        $url = 'index.php?action=viewPopular';
    }
    else {
        authenticate('access_admin');
        
        $cfg['menu'] = 'config';
        $query = mysqli_query($db, 'SELECT username FROM user WHERE user_id = ' . (int) $user_id);
        $user = mysqli_fetch_assoc($query);
        if ($user == false)
            message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]user_id not found in database');
        
        // formattedNavigator
        $nav            = array();
        $nav['name'][]  = 'Configuration';
        $nav['url'][]   = 'config.php';
        $nav['name'][]  = 'User statistics';
        $nav['url'][]   = 'users.php?action=userStatistics&amp;period=' . $period;
        if      ($flag == 0) $nav['name'][] = 'Play: ' . $user['username'];
        elseif  ($flag == 1) $nav['name'][] = 'Stream: ' . $user['username'];
        elseif  ($flag == 2) $nav['name'][] = 'Download: ' . $user['username'];
        elseif  ($flag == 3) $nav['name'][] = 'Cover: ' . $user['username'];
        elseif  ($flag == 4) $nav['name'][] = 'Record: ' . $user['username'];
        else    message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]flag');
        
        $query_pop = mysqli_query($db, 'SELECT artist, artist_alphabetic, album, image_id, album.album_id, COUNT(*) AS counter
            FROM counter, album
            WHERE user_id = ' . (int) $user_id . '
            AND counter.flag = ' . $flag . '
            AND counter.time > ' . (int) (time() - 86400 * $days) . '
            AND counter.album_id = album.album_id
            GROUP BY album.album_id
            ORDER BY counter DESC, time DESC
            LIMIT 50');
        
        $url = 'index.php?action=viewPopular&amp;flag=' . $flag . '&amp;user_id=' . $user_id;
    }
    require_once('include/header.inc.php'); 
    ?>
<table cellspacing="0" cellpadding="0">
<tr>
    <td>
<!--  -->
<table cellspacing="0" cellpadding="0" class="tab">
<tr>
    
    <td class="<?php echo ($period == 'week') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='<?php echo $url; ?>&amp;period=week';">Week</td>
    <td class="tab_none tabspace"></td>
    <td class="<?php echo ($period == 'month') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='<?php echo $url; ?>&amp;period=month';">Month</td>
    <td class="tab_none tabspace"></td>
    <td class="<?php echo ($period == 'year') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='<?php echo $url; ?>&amp;period=year';">Year</td>
    <td class="tab_none tabspace"></td>
    <td class="<?php echo ($period == 'overall') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='<?php echo $url; ?>&amp;period=overall';">Overall</td>
    <td class="tab_none">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" class="tab_border">
<tr class="tab_header">
    <td class="icon"></td><!-- menu -->
    <td<?php if ($cfg['access_play'] || $cfg['access_add'] || $cfg['access_stream']) echo' class="space"'; ?>></td>
    <td>Artist</td>
    <td class="textspace"></td>
    <td>Album</td>
    <td class="textspace"></td>
    <td colspan="2">Count</td>
    <td colspan="2"></td>
    <td class="space"></td>
</tr>

<?php
    $query = mysqli_query($db, 'SELECT artist, artist_alphabetic, album, image_id, album.album_id, COUNT(*) AS counter
            FROM counter, album
            WHERE user_id = ' . (int) $user_id . '
            AND counter.flag = ' . $flag . '
            AND counter.time > ' . (int) (time() - 86400 * $days) . '
            AND counter.album_id = album.album_id
            GROUP BY album.album_id
            ORDER BY counter DESC, time DESC
            LIMIT 50');
    $i=0;
    while ($album = mysqli_fetch_assoc($query_pop)) {
        if ($i == 0) $max = $album['counter']; ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
    <td class="icon">
    <span id="menu-track<?php echo $i ?>">
    <div onclick='toggleMenuSub(<?php echo $i ?>);'>
        <i id="menu-icon<?php echo $i ?>" class="fa fa-bars icon-small"></i>
    </div>
    </span>
    </td>
    <td></td>
    <td><a href="index.php?action=view2&amp;artist=<?php echo rawurlencode($album['artist_alphabetic']); ?>&amp;order=year"><?php echo html($album['artist']); ?></a></td>
    <td></td>
    <td><a href="index.php?action=view3&amp;album_id=<?php echo $album['album_id']; ?>" <?php echo onmouseoverImage($album['image_id']); ?>><?php echo html($album['album']); ?></a></td>
    <td></td>
    <td class="bar_space">&nbsp;</td>
    <td><?php echo $album['counter']; ?> &nbsp;</td>
    <td class="bar" onMouseOver="return overlib('<?php echo $album['counter']; ?>');" onMouseOut="return nd();">
    <div class="out-popular"><div style="width: <?php echo  round($album['counter'] / $max * 100); ?>px;" class="in"></div></div>
    </td>
    <td class="bar_space">&nbsp;</td>
    <td></td>
</tr>
<tr class="line">
    <td></td>
    <td colspan="16"></td>
</tr>
<tr>
<td colspan="16">
<div class="menuSub" id="menu-sub-track<?php echo $i ?>" onclick='//offMenuSub(<?php echo $i ?>);'> 
    
    <div><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;album_id=' . $album['album_id'] . '\',evaluateAdd);"><i id="play_' . $album['album_id'] . '" class="fa fa-play-circle-o fa-fw icon-small"></i>Play album</a>'; ?>
    </div>
    
    <div>
    <?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=updateAddPlay&album_id=' . $album['album_id'] . '\',updateAddPlay);ajaxRequest(\'play.php?action=addSelect&album_id=' . $album['album_id'] . '\',evaluateAdd);"><i id="add_' . $album['album_id'] . '" class="fa fa-plus-circle fa-fw icon-small"></i>Add to playlist</a>';?>
    </div>
    
    <div onClick="offMenuSub('');">
    <?php if ($cfg['access_add'])  echo '<a href="stream.php?action=playlist&amp;album_id=' . $album['album_id'] . '&amp;stream_id=' . $cfg['stream_id'] . '"><i class="fa fa-rss fa-fw icon-small"></i>Stream album</a>';?>
    </div>
    
    
</div>
</td>
</tr>

<?php
    }
?>
</table>
<!--  -->
    </td>
</tr>
</table>
<?php
    require_once('include/footer.inc.php');
}
