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
//  | View new on start page                                                 |
//  +------------------------------------------------------------------------+
function viewNewStartPage() {
    global $cfg, $db;
    global $base_size, $spaces, $scroll_bar_correction, $tileSizePHP;
    
    //authenticate('access_media');
    
    // formattedNavigator
    $nav            = array();
    $nav['name'][]  = 'Library';
    $nav['url'][]   = 'index.php';
    $nav['name'][]  = 'New';
    
    require_once('include/header.inc.php');
    
    $base       = (cookie('netjukebox_width') - 20) / ($base_size + 10);
    $colombs    = floor($base);
    $aval_width = (cookie('netjukebox_width') - 20 - $scroll_bar_correction) - ($colombs - 1) * $spaces;
    $size = floor($aval_width / $colombs);

    $i          = 0;
    $query = mysqli_query($db, 'SELECT SUM(discs) AS discs FROM album');
    $album = mysqli_fetch_assoc($query);
    
    
    if ($album['discs'] >= 1) {
        if (!isset($cfg['show_suggested'])) $cfg['show_suggested'] = true;
        if ($cfg['show_suggested'] == true) {
?>
<div id="suggested">
    <h1>&nbsp;Albums not played for more than 3 months (random)&nbsp;&nbsp;&nbsp;<i class="fa fa-refresh pointer icon-anchor larger" id="iframeRefresh"></i></h1>

    <div id="suggested_full" class="full">
        <div id="suggested_container">
        </div>
    </div>
</div>

<script>

$('#iframeRefresh').click(function() {  
    $('#iframeRefresh').removeClass("icon-anchor");
    $('#iframeRefresh').addClass("icon-selected fa-spin");
    var size = $tileSize;
    var request = $.ajax({  
        url: "ajax-suggested.php",  
        type: "POST",  
        data: { tileSize : size },  
        dataType: "html"
    }); 
    
    request.done(function( data ) {  
        if (data.indexOf('tile') > 0) { //check if any album recieved
            $("[id='suggested']").show();
            $( "#suggested_container" ).html( data );   
        }
        else {
            $("[id='suggested']").hide();
        }
        calcTileSize();
        console.log (data.length);
    }); 
    
    request.fail(function( jqXHR, textStatus ) {  
        //alert( "Request failed: " + textStatus ); 
    }); 

    request.always(function() {
        $('#iframeRefresh').addClass("icon-anchor");
        $('#iframeRefresh').removeClass("icon-selected fa-spin");
    });

});

$(document).ready(function () {
    $('#iframeRefresh').click();
});

</script>

<?php 
}; //show_suggested 
if (!isset($cfg['show_last_played'])) $cfg['show_last_played'] = true;
if ($cfg['show_last_played'] == true) {
        $query = mysqli_query($db, '
        SELECT DISTINCT album.album_id, album.image_id, album.album, album.artist_alphabetic
        FROM album RIGHT JOIN 
        (SELECT album_id, MAX(time) AS m_time FROM counter GROUP BY album_id ORDER BY m_time DESC) as c
        ON c.album_id = album.album_id
        LIMIT 10
        ' );
    $rows = mysqli_num_rows($query);

    if ($rows > 0) {
    ?>

    <h1>&nbsp;Recently played albums</h1>
    <div class="full">
    <?php
            while ( $album = mysqli_fetch_assoc ( $query ) ) {
                if ($album) {
                    if ($tileSizePHP)
                        $size = $tileSizePHP;
                    draw_tile ( $size, $album );
                }
            }
            ?>
    </div>
    <?php 
    }
} //last_played
?>
<h1>&nbsp;New albums</h1>

<div class="albums_container">
<?php
    $query = mysqli_query($db, 'SELECT COUNT(*) AS counter
        FROM album
        WHERE album_add_time');
    $items_count = mysqli_fetch_assoc($query);
    
    $cfg['items_count'] = $items_count['counter'];

    $query = mysqli_query($db, 'SELECT *
        FROM album
        WHERE album_add_time
        ORDER BY album_add_time DESC, album DESC
        LIMIT ' . $cfg['max_items_per_page']);
        
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

<?php
} //albums > 0
else {
?>
<div>
<h1>
<br>
Welcome to O!MPD.<br><br>
Your database is empty. Please <a href="config.php">update it.</a><br><br>
</h1>
</div>
<?php
}
?>


<table cellspacing="0" cellpadding="0" class="border">
    <tr class="line"><td colspan="11"></td></tr>
</table>

<?php
    require_once('include/footer.inc.php');
}
