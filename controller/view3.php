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
//  | View 3                                                                 |
//  +------------------------------------------------------------------------+
function view3() {
    global $cfg, $db, $twig;

    $album_id = get('album_id');
    
    if ($album_id == '') {
        message(__FILE__, __LINE__, 'error', '[b]Album not found in database.[/b]');
        exit;
    }
    
    if ($album_id == '' && $cfg['image_share']) {
        if ($cfg['image_share_mode'] == 'played') {
            $query = mysqli_query($db, 'SELECT album_id
                FROM counter
                WHERE flag <= 1
                ORDER BY time DESC
                LIMIT 1');
            $counter    = mysqli_fetch_assoc($query);
            $album_id   = $counter['album_id'];
        }
        else {
            $query = mysqli_query($db, 'SELECT album_id, album_add_time
                FROM album
                ORDER BY album_add_time DESC
                LIMIT 1');
            $album      = mysqli_fetch_assoc($query);
            $album_id   = $album['album_id'];
        }
    
        header('Location: ' . NJB_HOME_URL . 'index.php?action=view3&album_id=' . rawurldecode($album_id));
        exit();
    }
    
    
    
    authenticate('access_media');
    
    $query = mysqli_query($db, 'SELECT artist_alphabetic, artist, album, year, month, image_id, album_add_time, genre.genre as album_genre, album.genre_id
        FROM album, genre
        WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '"
        AND genre.genre_id=album.genre_id');
    $album = mysqli_fetch_assoc($query);
    $image_id = $album['image_id'];
    
    if ($album == false)
        message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]' . $album_id . ' not found in database');
    
    if ($cfg['show_multidisc'] == true) {
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
    
    if ($cfg['show_album_versions'] == true) {
        $av_indicator = striposa($album['album'], $cfg['album_versions_indicator']);
        if ($av_indicator !== false) {
            $mdqs = '';
            $md_indicator = striposa($album['album'], $cfg['multidisk_indicator']);
            if ($md_indicator !== false) {
                $md_ind_pos = stripos($album['album'], $md_indicator);
                $md_title = substr($album['album'], 0,  $md_ind_pos);
                $mdqs = ' AND album NOT IN (SELECT album 
                FROM album 
                WHERE album LIKE "' . mysqli_real_escape_string($db, $md_title) . '%" AND artist = "' . mysqli_real_escape_string($db,$album['artist']) . '" AND album <> "' . mysqli_real_escape_string($db, $album['album']) . '"
                ORDER BY album) ';
                //$multidisc_count = mysqli_num_rows($query_md);
            }
            
            $av_ind_pos = stripos($album['album'], $av_indicator);
            $av_title = substr($album['album'], 0,  $av_ind_pos);
            $qs = 'SELECT album, image_id, album_id 
            FROM album 
            WHERE album LIKE "' . mysqli_real_escape_string($db, $av_title) . '%" AND artist = "' . mysqli_real_escape_string($db, $album['artist']) . '" AND album <> "' . mysqli_real_escape_string($db, $album['album']) . '"
            ' . $mdqs . '
            ORDER BY album';
            $query_av = mysqli_query($db, $qs);
            $album_versions_count = mysqli_num_rows($query_av);
        }
        else {
            $qs = "";
            $isSet = false;
            foreach ($cfg['album_versions_indicator'] as $v) {
                $conjunction = ($isSet ? " OR " : "");
                $qs = $qs . $conjunction . 'album LIKE "' . mysqli_real_escape_string($db, $album['album']) . $v . '%"' ;
                $isSet = true;
            }
            $query_av = mysqli_query($db, 'SELECT album, image_id, album_id 
            FROM album 
            WHERE ' . $qs . ' AND artist = "' . mysqli_real_escape_string($db, $album['artist']) . '" 
            ORDER BY album');
            $album_versions_count = mysqli_num_rows($query_av);
        }
    }
    
    $featuring = false;
    
    $query = '
        SELECT
            track.audio_bits_per_sample,
            track.audio_sample_rate,
            track.audio_profile,
            track.audio_dataformat,
            track.comment,
            track.relative_file
        FROM track
        LEFT JOIN album ON album.album_id = track.album_id
        WHERE album.album_id = "' .  $db->real_escape_string($album_id) . '"
        LIMIT 1;';
    $album_info = $rel_file = $db->query($query)->fetch_assoc();

    $query = '
        SELECT
            COUNT(c.album_id) AS counter,
            MAX(c.time) AS time
        FROM (
            SELECT time, album_id
            FROM counter
            WHERE album_id = "' .  $db->real_escape_string($album_id) . '"
            ORDER BY time DESC
        ) c
        ORDER BY c.time;';
    $result = $db->query($query);
    $played = $result->fetch_assoc();
    $rows_played = $result->num_rows;

    $query = '
        SELECT
            album_id,
            COUNT(*) AS counter
        FROM counter
        GROUP BY album_id
        ORDER BY counter DESC
        LIMIT 1;';
    $result = $db->query($query);
    $max_played = $result->fetch_assoc();
    $rows_max_played = $result->num_rows;


    // formattedNavigator
    $nav            = array();
    $nav['name'][]  = $album['artist'] . ' - ' . $album['album'];
    require_once('include/header.inc.php');

    $advanced = array();
    if ($cfg['access_admin'] && $cfg['album_copy'] && is_dir($cfg['external_storage']))
        $advanced[] = '<a href="download.php?action=copyAlbum&amp;album_id='. $album_id . '&amp;sign=' . $cfg['sign'] . '"><i class="fa fa-fw  fa-copy icon-small"></i>Copy album</a>';
    if ($cfg['access_admin'] && $cfg['album_update_image']) {
        $advanced[] = '<a href="update.php?action=imageUpdate&amp;flag=9&amp;album_id='. $album_id . '"><img src="' . $cfg['img'] . 'small_image.png" alt="" class="small space">Update image</a>';
        $advanced[] = '<a href="update.php?action=selectImageUpload&amp;flag=9&amp;album_id='. $album_id . '"><img src="' . $cfg['img'] . 'small_upload.png" alt="" class="small space">Upload image</a>';
    }
    if ($cfg['access_admin'] && $cfg['album_edit_genre'])
        $advanced[] = '<a href="genre.php?action=edit&amp;album_id=' . $album_id . '"><img src="' . $cfg['img'] . 'small_genre.png" alt="" class="small space">Edit genre</a>';
    if ($cfg['access_admin'])
        $advanced[] = '<a href="javascript:showHide(\'basic\',\'advanced\');"><i class="fa fa-fw  fa-reply  icon-small"></i>Go back</a>';
    
    $basic = array();
    $search = array();
    
    
    $playerQuery = mysqli_query($db, 'SELECT * FROM player ORDER BY player_name');
    $playerCount = mysqli_num_rows($playerQuery);
    if ($playerCount > 1) {
        $playTo = array();
        while($player = mysqli_fetch_assoc($playerQuery)){
            $playTo[] = '<a href="javascript:ajaxRequest(\'stream.php?action=playTo&amp;album_id=' . $album_id . '&amp;stream_id=' . $cfg['stream_id'] . '&amp;player_id=' . $player['player_id'] . '\',evaluatePlayTo)"><i id="playTo_' . $player['player_id'] . '" class="fa fa-fw  fa-share-square-o  icon-small"></i>' . $player['player_name'] . '</a>';
    
            //$playTo[] = '<a href="stream.php?action=playTo&amp;album_id=' . $album_id . '&amp;stream_id=' . $cfg['stream_id'] . '&amp;player_host=' . $player['player_host'] . '&amp;player_port=' . $player['player_port'] . '"><i class="fa fa-fw  fa-share-square-o  icon-small"></i>' . $player['player_name'] . '</a>';
            
        }
        $playTo[] = '<a href="javascript:showHide(\'basic\',\'playTo\');"><i class="fa fa-fw  fa-reply  icon-small"></i>Go back</a>';
    }
    
    if ($cfg['access_play'])
        $basic[] = '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;album_id=' . $album_id . '\',evaluateAdd);ajaxRequest(\'play.php?action=updateAddPlay&amp;album_id=' . $album_id . '\',updateAddPlay);"><i id="play_' . $album_id . '" class="fa fa-fw fa-play-circle-o  icon-small"></i>Play album</a>';
    if ($cfg['access_add']){
        //ajaxRequest(\'play.php?action=addSelect&amp;album_id=' . $album_id . '\');
        $basic[] = '<a href="javascript:ajaxRequest(\'play.php?action=updateAddPlay&album_id=' . $album_id . '\',updateAddPlay);ajaxRequest(\'play.php?action=addSelect&album_id=' . $album_id . '\',evaluateAdd);"><i id="add_' . $album_id . '" class="fa fa-fw  fa-plus-circle  icon-small"></i>Add to playlist</a>';
        $basic[] = '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;album_id=' . $album_id . '&amp;insertType=album\',evaluateAdd);ajaxRequest(\'play.php?action=updateAddPlay&amp;album_id=' . $album_id . '\',updateAddPlay);"><i id="insert_' . $album_id . '" class="fa fa-fw fa-indent icon-small"></i>Insert into playlist</a>';
    }
    if ($cfg['access_add'] && $cfg['access_play'])
        $basic[] = '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;album_id=' . $album_id . '&amp;insertType=album\',evaluateAdd);ajaxRequest(\'play.php?action=updateAddPlay&amp;album_id=' . $album_id . '\',updateAddPlay);"><i id="insertPlay_' . $album_id . '" class="fa fa-fw  fa-play-circle icon-small"></i>Insert and play</a>';
    if ($cfg['access_stream']){
        $basic[] = '<a href="stream.php?action=playlist&amp;album_id=' . $album_id . '&amp;stream_id=' . $cfg['stream_id'] . '"><i class="fa fa-fw  fa-rss  icon-small"></i>Stream album</a>';
        if ($playerCount > 1) {
            $basic[] = '<a  href="javascript:showHide(\'basic\',\'playTo\');"><i class="fa fa-fw  fa-share-square-o  icon-small"></i>Play to...</a>';
        }
    }
    if ($cfg['access_download'] && $cfg['album_download'])
        $basic[] = '<a href="download.php?action=downloadAlbum&amp;album_id=' . $album_id . '&amp;download_id=' . $cfg['download_id'] . '" ' . onmouseoverDownloadAlbum($album_id) . '><i class="fa fa-fw  fa-download  icon-small"></i>Download album</a>';
    if ($cfg['access_play']){
        $dir_path = rawurlencode(dirname($cfg['media_dir'] . $rel_file['relative_file']));
        $basic[] = '<a href="browser.php?dir=' . $dir_path . '"><i class="fa fa-fw  fa-folder-open  icon-small"></i>Browse...</a>';
    }
    if ($cfg['access_admin'] && $cfg['album_share_stream'])
        $basic[] = '<a href="stream.php?action=shareAlbum&amp;album_id='. $album_id . '&amp;sign=' . $cfg['sign'] . '"><i class="fa fa-fw  fa-share-square-o  icon-small"></i>Share stream</a>';
    if ($cfg['access_admin'] && $cfg['album_share_download'])
        $basic[] = '<a href="download.php?action=shareAlbum&amp;album_id=' . $album_id . '&amp;sign=' . $cfg['sign'] . '"><i class="fa fa-fw  fa-share-square-o  icon-small"></i>Share download</a>';
    
    $count_basic = count($basic);
    $advanced_enabled = (count($advanced) > 1) ? 1 : 0;
    if (10 - $count_basic - $advanced_enabled < count($cfg['search_name']) ) {
        $basic[] = '<a href="javascript:showHide(\'basic\',\'search\');"><i class="fa fa-fw  fa-search  icon-small"></i>Search...</a>';
        for ($i = 0; $i < count($cfg['search_name']) && $i < 9; $i++)
            $search[] = '<a href="ridirect.php?search_id=' . $i . '&amp;album_id=' . $album_id . '" target="_blank"><i class="fa fa-fw  fa-search  icon-small"></i>' . html($cfg['search_name'][$i]) .'</a>';
        $search[] = '<a href="javascript:showHide(\'basic\',\'search\');"><i class="fa fa-fw  fa-reply  icon-small"></i>Go back</a>';
    }
    else {
        for ($i = 0; $i < count($cfg['search_name']) && $i < 10 - $count_basic; $i++)
            $basic[] = '<a href="ridirect.php?search_id=' . $i . '&amp;album_id=' . $album_id . '" target="_blank"><i class="fa fa-fw  fa-search  icon-small"></i>' . html($cfg['search_name'][$i]) .'</a>';
    }
    if ($cfg['access_admin'] && $advanced_enabled)
        $basic[] = '<a href="javascript:showHide(\'basic\',\'advanced\');"><i class="fa fa-fw  fa-cogs icon-small"></i>Advanced...</a>';

    
    
    if (strpos(strtolower($rel_file['relative_file']), strtolower($cfg['misc_tracks_folder'])) !== false) {
        $album['year'] = '';
        $album_info['audio_bits_per_sample'] = '';
        $album_info['audio_sample_rate'] = '';
        $album_info['audio_dataformat'] = '';
        $album_info['audio_profile'] = '';
    }   
    elseif (strpos(strtolower($rel_file['relative_file']), strtolower($cfg['misc_tracks_misc_artists_folder'])) !== false) {
        $album['year'] = '';
        $album['album_genre'] = '';
        $album_info['audio_bits_per_sample'] = '';
        $album_info['audio_sample_rate'] = '';
        $album_info['audio_dataformat'] = '';
        $album_info['audio_profile'] = '';
        
    }
    
?>


<div id="album-info-area">
<div id="image_container">
    <div id="cover-spinner">
        <img src="image/loader.gif" alt="">
    </div>
    <span id="image">
        <img id="image_in" src="image.php?image_id=<?php echo $image_id ?>&quality=hq" alt="">
    </span>
</div>


<!-- start options -->



<div class="album-info-area-right">

<div id="album-info" class="line">
    <div class="sign-play">
    <i class="fa fa-play-circle-o pointer"></i>
    </div>
    <div class="col-right">
        <div id="album-info-title"><?php echo $album['album']?></div>
        <div id="album-info-artist"><?php 
        $artist = '';
        $exploded = multiexplode($cfg['artist_separator'],$album['artist']);
        $l = count($exploded);
        if ($l > 1) {
            for ($i=0; $i<$l; $i++) {
                $artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($exploded[$i]) . '">' . html($exploded[$i]) . '</a>';
                if ($i != $l - 1) $artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($album['artist']) . '&amp;order=year"><span class="artist_all">&</span></a>';
            }
            echo $artist;
        }
        else {
            echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($album['artist']) . '&amp;order=year">' . html($album['artist']) . '</a>';
        }
        ?></div>
    </div>
</div>
<div class="line">
<div class="add-info-left">Popularity:</div>
<div id="bar-popularity-out" class="out"><div id="bar_popularity" class="in"></div></div>
&nbsp;
<?php 
$popularity = 0;
if ($rows_max_played == 0 || $rows_played == 0) 
    $popularity = 0;
else
    $popularity = round($played['counter'] / $max_played['counter'] * 100);
?>
<span id="popularity"><?php echo $popularity; ?></span>%
</div>


<?php

//  +------------------------------------------------------------------------+
//  | Additional Info                                                        |
//  +------------------------------------------------------------------------+
    $vars = array(
        'album' => array_merge($album, $album_info),
        'played' => $played,
        'cfg' => $cfg
    );

    // fetch played history for albumtracks
    $query = '
        SELECT time, album_id
        FROM counter
        WHERE album_id="' .  $db->real_escape_string($album_id) . '" ORDER BY time DESC
    ';
    $result = $db->query($query);
    while($historyEntry = $result->fetch_assoc()) {
        $vars['playedHistory'][] = $historyEntry;
    }

    // fetch comment and split
    $sep = 'no_sep';
    if (strpos($album_info['comment'], $cfg['tags_separator']) !== false) {
        $sep = $cfg['tags_separator'];
    }
    elseif ($cfg['testing'] == 'on' && strpos($album_info['comment']," ") !== false) {
        $sep = " ";
    }
    if ($sep != 'no_sep') {
        foreach (array_filter(explode($sep, $album_info['comment'])) as $value) {
            $vars['tags'][] = trim($value);
        }
    }
    else {
        $vars['tags'][] = $album_info['comment'];
    }
    echo $twig->render('albumdetail/partials/additionalInfo.htm', $vars);

?>

<br>    
<table cellspacing="0" cellpadding="0" id="basic" class="fullscreen">
<?php
    for ($i = 0; $i < 10; $i=$i+2) { ?>
<tr class="<?php echo ($i & 1) ? 'even_info' : 'odd_info'; ?> nowrap" style="height: 35px;">
    <td class="halfscreen"><?php echo (isset($basic[$i])) ? $basic[$i] : '&nbsp;'; ?></td>
    <td class="halfscreen"><?php echo (isset($basic[$i+1])) ? $basic[$i+1] : '&nbsp;'; ?></td>
    <td></td>
</tr>

<?php
    } ?>

</table>
<table cellspacing="0" cellpadding="0" id="search" style="display: none;" class="fullscreen">
<?php
    for ($i = 0; $i < 10; $i=$i+2) { ?>
<tr class="<?php echo ($i & 1) ? 'even_info' : 'odd_info'; ?> nowrap" style="height: 35px;">
    <td class="halfscreen"><?php echo (isset($search[$i])) ? $search[$i] : '&nbsp;'; ?></td>
    <td class="halfscreen"><?php echo (isset($search[$i+1])) ? $search[$i+1] : '&nbsp;'; ?></td>
</tr>
<?php
    } ?>
</table>
<table cellspacing="0" cellpadding="0" id="playTo" style="display: none;" class="fullscreen">
<?php
    for ($i = 0; $i < 10; $i=$i+2) { ?>
<tr class="<?php echo ($i & 1) ? 'even_info' : 'odd_info'; ?> nowrap" style="height: 35px;">
    <td class="halfscreen"><?php echo (isset($playTo[$i])) ? $playTo[$i] : '&nbsp;'; ?></td>
    <td class="halfscreen"><?php echo (isset($playTo[$i+1])) ? $playTo[$i+1] : '&nbsp;'; ?></td>
</tr>
<?php
    } ?>
</table>
<table cellspacing="0" cellpadding="0" id="advanced" style="display: none;">
<?php
    for ($i = 0; $i < 10; $i=$i+2) { ?>
<tr class="<?php echo ($i & 1) ? 'even_info' : 'odd_info'; ?> nowrap" style="height: 35px;">
    <td<?php echo ($i == 0) ? ' class="space"' : ''; ?>></td>
    <td><?php echo (isset($advanced[$i])) ? $advanced[$i] : '&nbsp;'; ?></td>
    <td<?php echo ($i == 0) ? ' class="vertical_line"' : ''; ?>></td>
    <td><?php echo (isset($advanced[$i+1])) ? $advanced[$i+1] : '&nbsp;'; ?></td>
</tr>
<?php
    } ?>
</table>
<br>
<?php


    //  +------------------------------------------------------------------------+
    //  | Multidiscs                                                             |
    //  +------------------------------------------------------------------------+
    if ($cfg['show_multidisc'] == true && $multidisc_count > 0) {
        while($multidisc = $query_md->fetch_assoc()) {
            $vars['multidiscs'][] = $multidisc;
        }
        echo $twig->render('albumdetail/partials/multidiscs.htm', $vars);
    }

    //  +------------------------------------------------------------------------+
    //  | Album versions                                                         |
    //  +------------------------------------------------------------------------+
    if ($cfg['show_album_versions'] == true && $album_versions_count > 0) {
        while($multidisc = $query_av->fetch_assoc()) {
            $vars['albumVersions'][] = $multidisc;
        }
        echo $twig->render('albumdetail/partials/albumVersions.htm', $vars);
    }


?>
<br>
</div>
<!-- end options -->    
</div>

<div id="playlist">
<span  class="playlist-title">Tracklist</span>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
    <td class="icon"></td><!-- track menu -->
    <td class="icon"></td>
    <td class="trackNumber">#</td>
    <td>Title</td>
    <td class="track-list-artist">Artist</td>
    <td class="textspace track-list-artist"></td>
    <td><?php if ($featuring) echo'Featuring'; ?></td><!-- optional featuring -->
    <td ></td>
    <td align="right" class="time">Time</td>
    <td class="space right"><div class="space"></div></td>
</tr>
<?php
    $query = mysqli_query($db, 'SELECT discs FROM album WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '"');
    $album = mysqli_fetch_assoc($query);
    for ($disc = 1; $disc <= $album['discs']; $disc++) {
        /* $query = mysqli_query($db, '
        SELECT track.track_artist, track.artist, track.title, track.featuring, track.miliseconds, track.track_id, track.number, favoriteitem.favorite_id
        FROM track LEFT JOIN favoriteitem ON track.track_id = favoriteitem.track_id
        WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '" AND disc = ' . (int) $disc . ' 
        GROUP BY track.track_id
        ORDER BY relative_file');
         */
        /* $query = mysqli_query($db, '
        SELECT track.track_artist, track.artist, track.title, track.featuring, track.miliseconds, track.track_id, track.number, track.relative_file
        FROM track 
        WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '" AND disc = ' . (int) $disc . ' 
        
        ORDER BY number,relative_file'); */

        $query = '
        SELECT
            track.track_artist,
            track.artist,
            track.title,
            track.featuring,
            track.miliseconds,
            track.track_id,
            track.number,
            track.relative_file,
            f.blacklist_pos as blacklist_pos,
            f.favorite_pos as favorite_pos
        FROM track
        LEFT JOIN (
            SELECT
                favoriteitem.track_id as track_id,
                b.position as blacklist_pos,
                f.position as favorite_pos
            FROM favoriteitem
            LEFT JOIN (
                SELECT track_id, position
                FROM favoriteitem
                WHERE favorite_id = "' . $cfg['blacklist_id'] . '"
            ) b ON favoriteitem.track_id = b.track_id
            LEFT JOIN (
                SELECT track_id, position
                FROM favoriteitem
                WHERE favorite_id= "' . $cfg['favorite_id'] . '"
            ) f ON favoriteitem.track_id = f.track_id
        ) f ON track.track_id = f.track_id
        WHERE album_id = "' .  $db->real_escape_string($album_id) . '" AND disc=' . (int) $disc . '
        GROUP BY track.track_id
        ORDER BY number,relative_file;';
        
        $result = $db->query($query);
        $i = 0;
        while ($track = $result->fetch_assoc()) { ?>
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
    <?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track ' . $track['number'] . '\');" onMouseOut="return nd();"><i id="add_' . $track['track_id'] . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';?>
    </span>
    </td>
    
    <td class="trackNumber"><?php if ($cfg['access_play'])      echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['track_id'] . '\');" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['number']) . '.</a>';?></td>
    <td><?php if ($cfg['access_play'])      echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['track_id'] . '\');" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            elseif ($cfg['access_add'])     echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            elseif ($cfg['access_stream'])  echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['track_id'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            else                            echo html($track['title']); ?>
    <span class="track-list-artist-narrow">by <?php echo html($track['track_artist']); ?></span>        
    </td>
    
    <td class="track-list-artist">
    <?php
    $artist = '';
        $exploded = multiexplode($cfg['artist_separator'],$track['track_artist']);
        $l = count($exploded);
        if ($l > 1) {
            for ($j=0; $j<$l; $j++) {
                $artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($exploded[$j]) . '">' . html($exploded[$j]) . '</a>';
                if ($j != $l - 1) $artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year"><span class="artist_all">&</span></a>';
            }
            echo $artist;
        }
        else {
            echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year">' . html($track['track_artist']) . '</a>';
        }
        ?>
    
    <?php /* if (mysqli_num_rows(mysqli_query($db, 'SELECT track_id FROM track WHERE track_artist like "%' .  mysqli_real_escape_string($db,$track['track_artist']) . '%"')) > 1) echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year">' . html($track['track_artist']) . '</a>'; else echo html($track['track_artist']);  */?>
    </td>
    <td class="track-list-artist"></td>
    <td><?php if ($track['featuring']) echo html($track['featuring']); ?></td>
    <?php
    //$queryFav = mysqli_query($db, "SELECT favorite_id FROM favoriteitem WHERE track_id = '" . $track['track_id'] . "' AND favorite_id = '" . $cfg['favorite_id'] . "'");
    $isFavorite = false;
    $isBlacklist = false;
    if ($track['favorite_pos']) $isFavorite = true;
    if ($track['blacklist_pos']) $isBlacklist = true;
    $tid = $track['track_id'];
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
<td colspan="10">
<?php starSubMenu($i, $isFavorite, $isBlacklist, $tid);?>
</td>
</tr>

<tr>
<td colspan="10">
<?php trackSubMenu($i, $track);?>
</td>
</tr>
<?php
        }
        $query = mysqli_query($db, 'SELECT SUM(miliseconds) AS sum_miliseconds FROM track WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '" AND disc = ' . (int) $disc);
        $track = mysqli_fetch_assoc($query); ?>

<!--
<tr class="footer">
    <td class="track-list-artist"></td>
    <td class="track-list-artist"></td>
    <td class="track-list-artist"></td>
    <td colspan="7" align="right">Total: <?php echo formattedTime($track['sum_miliseconds']); ?></td>
    
    <td></td>
</tr>
-->

<?php if ($disc < $album['discs']) echo '<tr class="line"><td colspan="15"></td></tr>' . "\n";
    }
    echo '</table>';
?>
<div><h1><div class="total-time">Total: <?php echo formattedTime($track['sum_miliseconds']); ?></div></h1>
</div>
<script type="text/javascript">

$(".sign-play").click(function(){
    playAlbum('<?php echo $album_id; ?>');
});


function setBarLength() {
    $('#bar_popularity').css('width',function() { return (<?php echo floor($popularity) ?> * 1/100 * $('#bar-popularity-out').width())} );
    return(true);
};

function setAlbumInfoWidth() {
    $('#album-info').css('maxWidth', function() {return ($(window).width() - 10 +'px')});
};



window.onload = function () {
    //setAlbumInfoWidth();
    setBarLength();
    $("#cover-spinner").hide();
    //addFavSubmenuActions();
    return(true);
};
</script>
<?php
    echo '</div>' . "\n";
    require_once('include/footer.inc.php');
}

