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
//  | View 1                                                                 |
//  +------------------------------------------------------------------------+
function view1() {
    global $cfg, $db, $nav;
    authenticate('access_media');

    $artist     = get('artist');
    $genre_id   = get('genre_id');
    $filter     = get('filter');
    require_once('include/header.inc.php');
    
    
    if ($genre_id) {
        if (substr($genre_id, -1) == '~') {
            $query = mysqli_query($db, 'SELECT artist_alphabetic
                FROM album
                WHERE genre_id = "' .  mysqli_real_escape_string($db,substr($genre_id, 0, -1)) . '"
                GROUP BY artist_alphabetic
                ORDER BY artist_alphabetic');
        }
        else {
            $query = mysqli_query($db, 'SELECT artist_alphabetic
                FROM album
                WHERE genre_id LIKE "' . mysqli_real_escape_like($genre_id) . '%"
                GROUP BY artist_alphabetic
                ORDER BY artist_alphabetic');
        }
        
        if (mysqli_num_rows($query) == 1) {
            view2();
            exit();
        }
        
        // require_once('include/header.inc.php');
        genreNavigator($genre_id);
        
        $list_url       = 'index.php?action=view2&amp;thumbnail=0&amp;genre_id=' . rawurlencode($genre_id) . '&amp;order=artist';
        $thumbnail_url  = 'index.php?action=view2&amp;thumbnail=1&amp;genre_id=' . rawurlencode($genre_id) . '&amp;order=artist';
        }
    else {
        /* if ($filter == '' || $artist == '') {
            $artist = 'All album artists';
            $filter = 'all';
        } */
        $query = '';
        if ($filter == 'all')           $query = mysqli_query($db, 'SELECT artist FROM track WHERE 1 GROUP BY artist ORDER BY artist');
        elseif ($filter == 'exact')     $query = mysqli_query($db, 'SELECT artist FROM track WHERE artist = "' .  mysqli_real_escape_string($db,$artist) . '" OR artist = "' .  mysqli_real_escape_string($db,$artist) . '" GROUP BY artist ORDER BY artist');
        elseif ($filter == 'smart')     $query = mysqli_query($db, 'SELECT artist FROM track WHERE artist LIKE "%' . mysqli_real_escape_like($artist) . '%" OR artist LIKE "%' . mysqli_real_escape_like($artist) . '%" OR artist SOUNDS LIKE "' .  mysqli_real_escape_string($db,$artist) . '" GROUP BY artist ORDER BY artist');
        elseif ($filter == 'start')     $query = mysqli_query($db, 'SELECT artist FROM track WHERE artist LIKE "' . mysqli_real_escape_like($artist) . '%" GROUP BY artist ORDER BY artist');
        elseif ($filter == 'symbol')    $query = mysqli_query($db, 'SELECT artist FROM track WHERE artist REGEXP "^[^a-z]" GROUP BY artist ORDER BY artist');
        else                            message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]filter');
        
        if (mysqli_num_rows($query) == 1) {
            $album = mysqli_fetch_assoc($query);
            $_GET['artist'] = $album['artist'];
            $_GET['filter'] = 'exact';
            view2();
            exit();
        }
        
        // formattedNavigator
        $nav            = array();
        $nav['name'][]  = 'Library';
        $nav['url'][]   = 'index.php';
        if ($artist != '') $nav['name'][] = 'Artist: ' . $artist;
        elseif ($filter == 'symbol') $nav['name'][] = 'Artist: #';
        elseif ($filter == 'all') $nav['name'][] = 'All artists';
        
        
        $list_url       = 'index.php?action=view2&amp;thumbnail=0&amp;artist=' . rawurlencode($artist) . '&amp;filter=' . $filter . '&amp;order=artist';
        $thumbnail_url  = 'index.php?action=view2&amp;thumbnail=1&amp;artist=' . rawurlencode($artist) . '&amp;filter=' . $filter . '&amp;order=artist';
    } 
    
    if (count($nav['name']) == 1 )  echo '<span class="nav_home"></span>' . "\n";
    else {
    echo '<span class="nav_tree">' . "\n";
    for ($i=0; $i < count($nav['name']); $i++) {
        if ($i > 0) echo '<span class="nav_seperation">></span>' . "\n";
        if (empty($nav['url'][$i]) == false) echo '<a href="' . $nav['url'][$i] . '">' . html($nav['name'][$i]) . '</a>' . "\n";
        else echo html($nav['name'][$i]) . "\n";
    }
    echo '</span>' . "\n";
    }
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
    <td class="space left"></td>
    <td>Artist</td>
    <td align="right" class="right">
    <?php 
    $c = mysqli_num_rows($query); 
    $a = ($c > 1) ? 'artists' : 'artist';
    echo ('(' . $c . ' ' . $a . ' found)'); 
    ?> 
    <!--
    <a href="<?php echo $list_url; ?>"><img src="<?php echo $cfg['img']; ?>small_header_list.png" alt="" class="small"></a>
    -->
    &nbsp;
    </td>   
</tr>

<?php
    $i = 0;
    while ($album = mysqli_fetch_assoc($query)) {
?>
<tr class="artist_list">
    <td></td>
    <td>
    <?php 
    $artist = '';
    $exploded = multiexplode($cfg['artist_separator'],$album['artist']);
        $l = count($exploded);
        if ($l > 1) {
            for ($j=0; $j<$l; $j++) {
                $artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($exploded[$j]) . '">' . html($exploded[$j]) . '</a>';
                if ($j != $l - 1) $artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($album['artist']) . '&amp;order=year"><span class="artist_all">&</span></a>';
            }
            echo $artist;
        }
        else {
            echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($album['artist']) . '&amp;order=year">' . html($album['artist']) . '</a>';
        }
    ?>
    
    <!--
    <a href="index.php?action=view2&amp;artist=<?php echo rawurlencode($album['artist']); ?>"><?php echo html($album['artist']); ?></a>
    -->
    
    </td>
    <td></td>
</tr>
<?php
    }
    echo '</table>' . "\n";
    require_once('include/footer.inc.php');
}

