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
//  | View 3 all                                                             |
//  +------------------------------------------------------------------------+
function view3all() {
    global $cfg, $db;
    authenticate('access_media');

    $artist         = get('artist');
    $title          = get('title');
    $track_ids      = get('track_ids');
    $other_title    = get('other_title');
    $filter         = get('filter')             or $filter  = 'start';
    $order          = get('order')              or $order   = 'title';
    $sort           = get('sort') == 'desc'     ? 'desc' : 'asc';
    
    $sort_artist            = 'asc';
    $sort_title             = 'asc';
    $sort_featuring         = 'asc';
    $sort_album             = 'asc';
    
    $order_bitmap_artist    = '<span class="typcn"></span>';
    $order_bitmap_title     = '<span class="typcn"></span>';
    $order_bitmap_featuring = '<span class="typcn"></span>';
    $order_bitmap_album     = '<span class="typcn"></span>';
    
    if (strlen($title) >= 1) {
        // formattedNavigator
        $nav            = array();
        $nav['name'][]  = 'Library';
        $nav['url'][]   = 'index.php';
        $nav['name'][]  = $title;
        require_once('include/header.inc.php');
        
        if ($filter == 'start') {
            /* $title = strtolower($title);
            $separator = $cfg['separator'];
            $count = count($separator);
            $i=0;
            for ($i=0; $i<$count; $i++) {
                $pos = strpos($title,strtolower($separator[$i]));
                if ($pos !== false) {
                    $title = trim(substr($title, 0 , $pos));
                    //break;
                }
            } */
            $separator = $cfg['separator'];
            $count = count($separator);
            $title = findCoreTrackTitle($title);
            $title = mysqli_real_escape_like($title);
            
            $query_string = '';
            $i=0;
            for ($i=0; $i<$count; $i++) {
                $query_string = $query_string . ' OR LOWER(track.title) LIKE "' . $title . $separator[$i] . '%"'; 
            }
                
            $filter_query = 'WHERE (LOWER(track.title) = "' . $title . '" ' . $query_string . ') AND track.album_id = album.album_id';
            //echo $filter_query;
        }
        elseif ($filter == 'smart') $filter_query = 'WHERE (track.title LIKE "%' . mysqli_real_escape_like($title) . '%" OR track.title SOUNDS LIKE "' .  mysqli_real_escape_string($db,$title) . '") AND track.album_id = album.album_id';
        elseif ($filter == 'exact') $filter_query = 'WHERE track.title = "' .  mysqli_real_escape_string($db,$title) . '" AND track.album_id = album.album_id';
        else                        message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]filter');
        
        $url = 'index.php?action=view3all&amp;title=' . rawurlencode($title) . '&amp;filter=' . $filter;
    }
    elseif (strlen($artist) >= 1) {
        // formattedNavigator
        $nav            = array();
        $nav['name'][]  = 'Library';
        $nav['url'][]   = 'index.php';
        $nav['name'][]  = $artist;
        $nav['url'][]   = 'index.php?action=view2&amp;artist=' . rawurlencode($artist) . '&amp;order=year';
        $nav['name'][]  = 'All tracks';
        require_once('include/header.inc.php');
        
        $filter_query = 'WHERE track.artist="' .  mysqli_real_escape_string($db,$artist) . '" AND track.album_id = album.album_id';
        //$filter_query = 'WHERE track.artist="' .  mysqli_real_escape_string($db,$artist) . '"';
        $url = 'index.php?action=view3all&amp;artist=' . rawurlencode($artist);
    }
    elseif (strlen($track_ids) >= 1) {
        $nav            = array();
        $nav['name'][]  = 'Library';
        $nav['url'][]   = 'index.php';
        $nav['name'][]  = 'Title: \'' . $other_title . '\'';
        require_once('include/header.inc.php');
        $ids = explode(";",$track_ids);
        $subquery = '';
        for ($j = 0; $j < (count($ids) - 1); $j++) {
            $subquery .= '"' . $ids[$j] . '",'; 
        }
        $subquery = substr($subquery,0, -1);
        //echo $subquery;
        $filter_query = 'WHERE (track.track_id in (' . $subquery . ')) AND track.album_id = album.album_id';
        //echo ("query: " . $filter_query);
    }   
    else
        message(__FILE__, __LINE__, 'warning', '[b]Search string too short - min. 2 characters[/b][br][url=index.php][img]small_back.png[/img]Back to previous page[/url]');
    
    if ($order == 'artist' && $sort == 'asc') {
        $order_query = 'ORDER BY artist, title';
        $order_bitmap_artist = '<span class="fa fa-sort-alpha-asc"></span>';
        $sort_artist = 'desc';
    }
    elseif ($order == 'artist' && $sort == 'desc') {
        $order_query = 'ORDER BY artist DESC, title DESC';
        $order_bitmap_artist = '<span class="fa fa-sort-alpha-desc"></span>';
        $sort_artist = 'asc';
    }
    elseif ($order == 'title' && $sort == 'asc') {
        $order_query = 'ORDER BY title, artist, album';
        $order_bitmap_title = '<span class="fa fa-sort-alpha-asc"></span>';
        $sort_title = 'desc';
    }
    elseif ($order == 'title' && $sort == 'desc') {
        $order_query = 'ORDER BY title DESC, artist DESC, album DESC';
        $order_bitmap_title = '<span class="fa fa-sort-alpha-desc"></span>';
        $sort_title = 'asc';
    }
    elseif ($order == 'featuring' && $sort == 'asc') {
        $order_query = 'ORDER BY featuring, title, artist';
        $order_bitmap_featuring = '<span class="fa fa-sort-alpha-asc"></span>';
        $sort_featuring = 'desc';
    }
    elseif ($order == 'featuring' && $sort == 'desc') {
        $order_query = 'ORDER BY featuring DESC, title DESC, artist DESC';
        $order_bitmap_featuring = '<span class="fa fa-sort-alpha-desc"></span>';
        $sort_featuring = 'asc';
    }
    elseif ($order == 'album' && $sort == 'asc') {
        $order_query = 'ORDER BY album, relative_file';
        $order_bitmap_album = '<span class="fa fa-sort-alpha-asc"></span>';
        $sort_album = 'desc';
    }
    elseif ($order == 'album' && $sort == 'desc') {
        $order_query = 'ORDER BY album DESC, relative_file DESC';
        $order_bitmap_album = '<span class="fa fa-sort-alpha-desc"></span>';
        $sort_album = 'asc';
    }
    else
        message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]order');
    
    /*$query = mysqli_query($db, 'SELECT featuring FROM track, album ' . $filter_query . ' AND featuring <> ""');
    if (mysqli_fetch_row($query))   $featuring = true;
    else                            $featuring = false;
    */  
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
    <td class="icon">&nbsp;</td><!-- track menu -->
    <td class="icon">&nbsp;</td><!-- add track -->
    <td class="track-list-artist"><a <?php echo ($order_bitmap_artist == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="<?php echo $url; ?>&amp;order=artist&amp;sort=<?php echo $sort_artist; ?>">Artist&nbsp;<?php echo $order_bitmap_artist; ?></a></td>
    <td><a <?php echo ($order_bitmap_title == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="<?php echo $url; ?>&amp;order=title&amp;sort=<?php echo $sort_title; ?>">Title&nbsp;<?php echo $order_bitmap_title; ?></a></td>
    <td><a <?php echo ($order_bitmap_album == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="<?php echo $url; ?>&amp;order=album&amp;sort=<?php echo $sort_album; ?>">Album&nbsp;<?php echo $order_bitmap_album; ?></a></td>
    <td></td>
    <td align="right" class="time">Time</td>
    <td class="space right"></td>
</tr>

<?php
    $i=0;
    //$query = mysqli_query($db, 'SELECT track.artist, track.title, track.number, track.featuring, track.album_id, track.track_id, track.miliseconds, track.relative_file, album.image_id, album.album FROM track, album ' . $filter_query . ' ' . $order_query);
    
    $q = 'SELECT * FROM
    (SELECT track.artist as track_artist, track.title, track.featuring, track.album_id, track.track_id as tid, track.miliseconds, track.number, track.relative_file, album.image_id, album.album, album.artist
    FROM track
    INNER JOIN album ON track.album_id = album.album_id '
    . $filter_query . ' ' . $order_query .') as a
    LEFT JOIN 
    (SELECT track_id, favorite_id FROM favoriteitem WHERE favorite_id = "' . $cfg['favorite_id'] . '") as b ON b.track_id = a.tid
    LEFT JOIN 
    (SELECT track_id, favorite_id as blacklist_id FROM favoriteitem WHERE favorite_id = "' . $cfg['blacklist_id'] . '") as bl ON bl.track_id = a.tid
    ORDER BY a.track_artist
    ';
    
    //echo ($q);
    
    $query = mysqli_query($db,$q);
    
    //$query = mysqli_query($db, 'SELECT track.artist, track.title, track.featuring, track.album_id, track.track_id, track.miliseconds  FROM track ' . $filter_query . ' ' . $order_query);

    while ($track = mysqli_fetch_assoc($query)) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
    <td class="icon">
    <span id="menu-track<?php echo $i ?>">
    <div onclick='toggleMenuSub(<?php echo $i ?>);'>
        <i id="menu-icon<?php echo $i ?>" class="fa fa-bars icon-small"></i>
    </div>
    </span>
    </td>
    
    <td class="icon">
    <span>
    <?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['tid'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track ' . $track['number'] . '\');" onMouseOut="return nd();"><i id="add_' . $track['tid'] . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';?>
    </span>
    </td>
        
    <td class="track-list-artist"><?php if (mysqli_num_rows(mysqli_query($db, 'SELECT track_id FROM track WHERE artist="' .  mysqli_real_escape_string($db,$track['track_artist']) . '"')) > 1) echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year">' . html($track['track_artist']) . '</a>'; else echo html($track['track_artist']); ?></td>
    
    <td><?php if ($cfg['access_play'])      echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Play track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            elseif ($cfg['access_add'])     echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            elseif ($cfg['access_stream'])  echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['tid'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            else                            echo html($track['title']); ?>
        <span class="track-list-artist-narrow">by <?php echo html($track['track_artist']); ?></span>
    </td>
    
    <td><a href="index.php?action=view3&amp;album_id=<?php echo $track['album_id']; ?>" <?php echo onmouseoverImage($track['image_id']); ?>><?php echo html($track['album']); ?></a>
    </td>
    
    <?php
    $isFavorite = false;
    $isBlacklist = false;
    if ($track['favorite_id']) $isFavorite = true;
    if ($track['blacklist_id']) $isBlacklist = true;
    $tid = $track['tid'];
    ?>
    
    <td onclick="toggleStarSub(<?php echo $i ?>,'<?php echo $tid ?>');" class="pl-favorites">
        <span id="blacklist-star-bg<?php echo $tid ?>" class="<?php if ($isBlacklist) echo ' blackstar blackstar-selected'; ?>">
        <i class="fa fa-star<?php if (!$isFavorite) echo '-o'; ?> fa-fw" id="favorite_star-<?php echo $tid; ?>"></i>
        </span>
    </td>
    
    <td align="right"><?php echo formattedTime($track['miliseconds']); ?></td>
    <td></td>
</tr>

<tr class="line">
    <td></td>
    <td colspan="16"></td>
</tr>

<tr>
    <td colspan="20">
    <?php starSubMenu($i, $isFavorite, $isBlacklist, $tid);?>
    </td>
</tr>

<tr>
    <td colspan="20">
    <?php trackSubMenu($i, $track);?>
    </td>
</tr>

<?php
    }
    echo '</table>' . "\n";
    require_once('include/footer.inc.php');
}

