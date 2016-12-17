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
//  | View new                                                               |
//  +------------------------------------------------------------------------+
function viewNew() {
    global $cfg, $db;
    global $base_size, $spaces, $scroll_bar_correction;
    authenticate('access_media');

    // formattedNavigator
    $nav            = array();
    $nav['name'][]  = 'Library';
    $nav['url'][]   = 'index.php';
    $nav['name'][]  = 'New';
require_once('include/header.inc.php');

    //$size = get('size');
    //$size = $cfg['thumbnail_size'];
    $i          = 0;
    //$colombs  = floor((cookie('netjukebox_width') - 20) / ($size + 10));
    
    /*$base     = (cookie('netjukebox_width') - 20) / ($base_size + 10);
    $colombs    = floor($base);
    $aval_width = (cookie('netjukebox_width') - 20 - $scroll_bar_correction) - ($colombs - 1) * $spaces;
    $size = floor($aval_width / $colombs);
    */
    
    //$sort_url = $url;
    //$size_url = $url . '&amp;order=' . $order . '&amp;sort=' . $sort;
    
    $query = mysqli_query($db, 'SELECT COUNT(*) AS counter
        FROM album
        WHERE album_add_time');
    $items_count = mysqli_fetch_assoc($query);
    
    $cfg['items_count'] = $items_count['counter'];
    
    $page = get('page');
    $max_item_per_page = $cfg['max_items_per_page'];
    
    $query = mysqli_query($db, 'SELECT *
        FROM album
        WHERE album_add_time
        ORDER BY album_add_time DESC
        LIMIT ' . ($page - 1) * $max_item_per_page . ','  . ($max_item_per_page));  
        //$colombs * 20);
    
?>


<h1>
new albums
</h1>


<div class="albums_container">
<?php
    /* while ($album = mysqli_fetch_assoc($query)) {        
            if ($album) {
            if ($tileSizePHP) $size = $tileSizePHP;
            draw_tile($size,$album);
            }
        } */ 
    while ($album = mysqli_fetch_assoc($query)) {       
        $multidisc_count = 0;
        if ($album) {
            if ($cfg['group_multidisc'] == true) {
                $md_indicator = striposa($album['album'], $cfg['multidisk_indicator']);
                if ($md_indicator !== false) {
                    $md_ind_pos = stripos($album['album'], $md_indicator);
                    $md_title = substr($album['album'], 0,  $md_ind_pos);
                    $query_md = mysqli_query($db, 'SELECT album, image_id, album_id 
                    FROM album 
                    WHERE album LIKE "' . mysqli_real_escape_string($db, $md_title) . '%" AND artist = "' . mysqli_real_escape_string($db, $album['artist']) . '" AND album <> "' . mysqli_real_escape_string($db, $album['album']) . '"
                    ORDER BY album');
                    $multidisc_count = mysqli_num_rows($query_md);
                }
            }
            if ($tileSizePHP) $size = $tileSizePHP;
            if ($multidisc_count > 0) {
                if (!in_array($md_title, $mdTab)) {
                    $mdTab[] = $md_title;
                    draw_tile($size,$album,'allDiscs');
                }
                else {
                    $album_count--;
                }
            }
            else {
                draw_tile($size,$album);
            }
        }
    } 
?>
</div>

<table cellspacing="0" cellpadding="0" class="border">

<tr class="<?php echo $class; ?> smallspace"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr class="line"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<?php
    //$query = mysqli_query($db, 'SELECT artist FROM album ' . $filter_query . ' GROUP BY artist');
    if (mysqli_num_rows($query) < 2) {
        $album = mysqli_fetch_assoc($query);
        if ($album['artist'] == '') $album['artist'] = $artist;
        $query = mysqli_query($db, 'SELECT album_id from track where artist = "' .  mysqli_real_escape_string($db,$album['artist']) . '"');
        $tracks = mysqli_num_rows($query);
?>

<tr class="footer">
    <td></td>
    <td colspan="<?php echo $colombs; ?>"><a href="index.php?action=view3all&amp;artist=<?php echo rawurlencode($album['artist']); ?>&amp;order=title">View all tracks from <?php echo html($album['artist']); ?> 
    <!-- <?php echo $tracks . (($tracks == 1) ? ' track from ' : ' tracks from ') . html($album['artist']); ?> -->
    </a></td>
    <td></td>
</tr>
<?php
    } ?>

</table>


<?php
    
    require_once('include/footer.inc.php');
}
