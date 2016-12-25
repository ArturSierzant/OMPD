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
//  | View 2                                                                 |
//  +------------------------------------------------------------------------+
function view2() {
    global $cfg, $db;
    authenticate('access_media');

    $isGenreRequired = get('genre_id');
    if ($isGenreRequired)
        genreNavigator($isGenreRequired);

    
    $title      = get('title');
    $artist     = get('artist');
    $genre_id   = get('genre_id');
    $tag        = get('tag');
    $year       = (get('year') == 'Unknown' ? get('year'): (int) get('year'));
    $filter     = get('filter')             or $filter = 'whole';
    //$thumbnail    = get('thumbnail')          ? 1 : 0;
    $thumbnail  = 1;
    $order      = get('order')              or $order = ($year ? 'artist' : (in_array(strtolower($artist), $cfg['no_album_artist']) ? 'album' : 'year'));
    $sort       = get('sort') == 'desc'     ? 'desc' : 'asc';
    $qsType     = (int) get('qsType')               or $qsType = false;
    
    $sort_artist            = 'asc';
    $sort_album             = 'asc';
    $sort_genre             = 'asc';
    $sort_year              = 'asc';
    $sort_decade            = 'asc';
    $sort_addtime           = 'desc';
    
    $order_bitmap_artist    = '<span class="typcn"></span>';
    $order_bitmap_album     = '<span class="typcn"></span>';
    $order_bitmap_genre     = '<span class="typcn"></span>';
    $order_bitmap_year      = '<span class="typcn"></span>';
    $order_bitmap_decade    = '<span class="typcn"></span>';
    $order_bitmap_addtime   = '<span class="typcn"></span>';
    
    $yearAct                = 0;
    $yearPrev               = 1;
    
    $page = (get('page') ? get('page') : 1);
    $max_item_per_page = $cfg['max_items_per_page'];
    
    if (isset($_GET['thumbnail'])) {
        mysqli_query($db, 'UPDATE session
            SET thumbnail   = ' . (int) $thumbnail . '
            WHERE sid       = BINARY "' .  mysqli_real_escape_string($db,$cfg['sid']) . '"');
    }
    else
        $thumbnail = $cfg['thumbnail'];
    
    
    if ($genre_id || $title) {
        if ($genre_id) {
        //genreNavigator($genre_id);
        
        //if (substr($genre_id, -1) == '~') $filter_query = 'WHERE genre_id = "' .  mysqli_real_escape_string($db,substr($genre_id, 0, -1)) . '"';
        //else                              
        $filter_query = 'WHERE genre_id LIKE "' . mysqli_real_escape_like($genre_id) . '"';}
        
        else if ($title) {
            genreNavigator('');
            $filter_query = 'WHERE album LIKE "%' . mysqli_real_escape_like($title) . '%"';
        }
        
        
        if ($order == 'artist' && $sort == 'asc') {
            $order_query = 'ORDER BY artist_alphabetic, year, month, album';
            $order_bitmap_artist = '<span class="fa fa-sort-alpha-asc"></span>';
            $sort_artist = 'desc';
        }
        elseif ($order == 'artist' && $sort == 'desc') {
            $order_query = 'ORDER BY artist_alphabetic DESC, year DESC, month DESC, album DESC';
            $order_bitmap_artist = '<span class="fa fa-sort-alpha-desc"></span>';
            $sort_artist = 'asc';
        }
        elseif ($order == 'album' && $sort == 'asc') {
            $order_query = 'ORDER BY album, artist_alphabetic, year, month';
            $order_bitmap_album = '<span class="fa fa-sort-alpha-asc"></span>';
            $sort_album = 'desc';
        }
        elseif ($order == 'album' && $sort == 'desc') {
            $order_query = 'ORDER BY album DESC, artist_alphabetic DESC, year DESC, month DESC';
            $order_bitmap_album = '<span class="fa fa-sort-alpha-desc"></span>';
            $sort_album = 'asc';
        }
        elseif ($order == 'genre' && $sort == 'asc') {
            $order_query = 'ORDER BY genre_id, artist_alphabetic, year, month, album';
            $order_bitmap_genre = '<span class="fa fa-sort-alpha-asc"></span>';
            $sort_genre = 'desc';
        }
        elseif ($order == 'genre' && $sort == 'desc') {
            $order_query = 'ORDER BY genre_id DESC, artist_alphabetic DESC, year DESC, month DESC, album DESC';
            $order_bitmap_genre = '<span class="fa fa-sort-alpha-desc"></span>';
            $sort_genre = 'asc';
        }
        elseif ($order == 'year' && $sort == 'asc') {
            $order_query = 'ORDER BY year, month, artist_alphabetic, album';
            $order_bitmap_year = '<span class="fa fa-sort-numeric-asc"></span>';
            $sort_year = 'desc';
        }
        elseif ($order == 'year' && $sort == 'desc') {
            $order_query = 'ORDER BY year DESC, month DESC, artist_alphabetic DESC, album DESC';
            $order_bitmap_year = '<span class="fa fa-sort-numeric-desc"></span>';
            $sort_year = 'asc';
        }
        elseif ($order == 'decade' && $sort == 'asc') {
            $order_query = 'ORDER BY year, month, artist_alphabetic, album';
            $order_bitmap_decade = '<span class="fa fa-sort-numeric-asc"></span>';
            $sort_decade = 'desc';
        }
        elseif ($order == 'decade' && $sort == 'desc') {
            $order_query = 'ORDER BY year DESC, month DESC, artist_alphabetic DESC, album DESC';
            $order_bitmap_decade = '<span class="fa fa-sort-numeric-desc"></span>';
            $sort_decade = 'asc';
        }
        elseif ($order == 'addtime' && $sort == 'asc') {
            $order_query = 'ORDER BY album_add_time, artist_alphabetic, album';
            $order_bitmap_addtime = '<span class="fa fa-sort-numeric-asc"></span>';
            $sort_addtime = 'desc';
        }
        elseif ($order == 'addtime' && $sort == 'desc') {
            $order_query = 'ORDER BY album_add_time DESC, artist_alphabetic DESC, album DESC';
            $order_bitmap_addtime = '<span class="fa fa-sort-numeric-desc"></span>';
            $sort_addtime = 'asc';
        }
        else
            message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]order');
        
        $query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, genre_id, image_id, album_id FROM album ' . $filter_query . ' ' . $order_query);
        
        $cfg['items_count'] = $album_count = mysqli_num_rows($query);
        
        if ($album_count > $max_item_per_page) {
            $query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, genre_id, image_id, album_id FROM album ' . $filter_query . ' ' . $order_query .
            ' LIMIT ' . ($page - 1) * $max_item_per_page . ','  . ($max_item_per_page));
        }
        
        $url            = 'index.php?action=view2&amp;genre_id=' . rawurlencode($genre_id);
        $list_url       = 'index.php?action=view2&amp;thumbnail=0&amp;genre_id=' . rawurlencode($genre_id) . '&amp;filter=' . $filter . '&amp;order=' . $order;
        $thumbnail_url  = 'index.php?action=view2&amp;thumbnail=1&amp;genre_id=' . rawurlencode($genre_id) . '&amp;filter=' . $filter . '&amp;order=' . $order;
    }
    elseif ($year) {
        // formattedNavigator
        $queryYear = mysqli_query($db, "SELECT MIN(year) as maxYear from album WHERE year > '" . $year . "'");
        $rst = mysqli_fetch_assoc($queryYear);
        $maxYear = $rst['maxYear'];
        
        $queryYear = mysqli_query($db, "SELECT MAX(year) as minYear from album WHERE year < '" . $year . "'");
        $rst = mysqli_fetch_assoc($queryYear);
        $minYear = $rst['minYear'];
        
        $nav = array();
        $nav['name'][]  = 'Library';
        $nav['url'][]   = 'index.php';
        $nav['name'][]  = 'Year';
        $nav['url'][]   = 'index.php?action=viewYear';
        if (is_numeric($minYear)) {
            $nav['name'][]  = $minYear;
            $nav['url'][]   = 'index.php?action=view2&year=' . ($minYear);
        }
        $nav['name'][]  = $year;
        $nav['url'][]   = "";
        if (is_numeric($maxYear) && $year < date("Y")) {
            $nav['name'][]  = $maxYear;
            $nav['url'][]   = 'index.php?action=view2&year=' . ($maxYear);
        }
        require_once('include/header.inc.php');
        
        if ($year == 'Unknown') $filter_query = 'WHERE year is null ';
        else $filter_query = 'WHERE year = ' . (int) $year;
        $url            = 'index.php?action=view2&amp;year=' . $year;
        $list_url       = 'index.php?action=view2&amp;thumbnail=0&amp;year=' . $year . '&amp;order=' . $order . '&amp;sort=' . $sort;
        $thumbnail_url  = 'index.php?action=view2&amp;thumbnail=1&amp;year=' . $year . '&amp;order=' . $order . '&amp;sort=' . $sort;
    }
    else {
        if ($filter == 'all' || $artist == '') {
            $artist = 'All albums';
            $filter = 'all';
        }
        
        // formattedNavigator
        $nav            = array();
        $nav['name'][]  = 'Library';
        $nav['url'][]   = 'index.php';
        if ($qsType) $nav['name'][] = $cfg['quick_search'][$qsType][0];
        elseif ($tag)   $nav['name'][]  = $tag;
        else    $nav['name'][]  = 'Artist: ' . $artist;
        
        
        
        require_once('include/header.inc.php');
        
        
        
        if ($filter == 'all')           $filter_query = 'WHERE 1';
        elseif ($filter == 'exact')     $filter_query = 'WHERE (artist_alphabetic = "' .  mysqli_real_escape_string($db,$artist) . '" OR artist = "' .  mysqli_real_escape_string($db,$artist) . '")';
        elseif ($filter == 'like')      $filter_query = 'WHERE (artist_alphabetic LIKE "%' .  mysqli_real_escape_string($db,$artist) . '%" OR artist LIKE "%' .  mysqli_real_escape_string($db,$artist) . '%")';
        elseif ($filter == 'smart')     $filter_query = 'WHERE (artist_alphabetic  LIKE "%' . mysqli_real_escape_like($artist) . '%" OR artist LIKE "%' . mysqli_real_escape_like($artist) . '%" OR artist SOUNDS LIKE "' .  mysqli_real_escape_string($db,$artist) . '")';
        elseif ($filter == 'start')     $filter_query = 'WHERE (artist_alphabetic  LIKE "' . mysqli_real_escape_like($artist) . '%")';
        elseif ($filter == 'symbol')    $filter_query = 'WHERE (artist_alphabetic  NOT BETWEEN "a" AND "zzzzzz")';
        //elseif ($filter == 'symbol')  $filter_query = '';
        elseif ($filter == 'whole') {
            $art =  mysqli_real_escape_string($db,$artist);
            $as = $cfg['artist_separator'];
            $count = count($as);
            $i=0;
            $search_str = '';
            
            /* for($i=0; $i<$count; $i++) {
            $search_str .= ' OR artist REGEXP "^(' . $art . ')[[.space.]]*[[.' . $as[$i] . '.]]" 
            OR artist REGEXP "[[.' . $as[$i] . '.]][[.space.]]*(' . $art . ')$" 
            OR artist REGEXP "[[.' . $as[$i] . '.]][[.space.]]*(' . $art . ')[[.space.]]*[[.' . $as[$i] . '.]]"';
            } */
            
            for($i=0; $i<$count; $i++) {
            $search_str .= ' OR artist LIKE "' . $art . '' . $as[$i] . '%" 
            OR artist LIKE "%' . $as[$i] . '' . $art . '" 
            OR artist LIKE "%' . $as[$i] . '' . $art . '' . $as[$i] . '%" 
            OR artist LIKE "% & ' . $art . '' . $as[$i] . '%" 
            OR artist LIKE "%' . $as[$i] . '' . $art . ' & %"';
            //last 2 lines above for artist like 'Mitch & Mitch' in 'Zbigniew Wodecki; Mitch & Mitch; Orchestra and Choir'
            }
            
            
            $filter_query = 'WHERE (
            artist = "' .  mysqli_real_escape_string($db,$artist) . '"' . $search_str . ')';
            //echo $filter_query;
            /* 
            OR artist LIKE "%' . $as .  mysqli_real_escape_string($db,$artist) . '" 
            OR artist LIKE "%' . $as . ' ' .  mysqli_real_escape_string($db,$artist) . '" 
            OR artist LIKE "' .  mysqli_real_escape_string($db,$artist) . $as . '%"
            OR artist LIKE "' .  mysqli_real_escape_string($db,$artist) . ' ' . $as . '%"
            
            OR artist LIKE "% ' .  mysqli_real_escape_string($db,$artist) . '" 
            OR artist LIKE "' .  mysqli_real_escape_string($db,$artist) . ' %" 
            OR artist LIKE "' .  mysqli_real_escape_string($db,$artist) . ';%"
            OR artist LIKE "' .  mysqli_real_escape_string($db,$artist) . ',%"
            OR artist LIKE "% ' .  mysqli_real_escape_string($db,$artist) . ';%"
            OR artist LIKE "% ' .  mysqli_real_escape_string($db,$artist) . ',%"
            OR artist LIKE "% ' .  mysqli_real_escape_string($db,$artist) . ' %"
             */
        }
        else                            message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]filter');
        
        $url            = 'index.php?action=view2&amp;artist=' . rawurlencode($artist) . '&amp;filter=' . $filter;
        $list_url       = 'index.php?action=view2&amp;thumbnail=0&amp;artist=' . rawurlencode($artist) . '&amp;filter=' . $filter . '&amp;order=' . $order . '&amp;sort=' . $sort;
        $thumbnail_url  = 'index.php?action=view2&amp;thumbnail=1&amp;artist=' . rawurlencode($artist) . '&amp;filter=' . $filter . '&amp;order=' . $order . '&amp;sort=' . $sort;
    }
    if (($artist || $year)) {
    
        if ($order == 'year' && $sort == 'asc') {
            $order_query = 'ORDER BY year, month, artist_alphabetic, album';
            //$query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, genre_id, image_id, album_id FROM album ' . $filter_query . ' ' . $order_query);
            $order_bitmap_year = '<span class="fa fa-sort-numeric-asc"></span>';
            $sort_year = 'desc';
        }
        elseif ($order == 'year' && $sort == 'desc') {
            $order_query = 'ORDER BY year DESC, month DESC, artist_alphabetic DESC, album DESC';
            //$query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, genre_id, image_id, album_id FROM album ' . $filter_query . ' ' . $order_query);
            $order_bitmap_year = '<span class="fa fa-sort-numeric-desc"></span>';
            $sort_year = 'asc';
        }
        elseif ($order == 'addtime' && $sort == 'asc') {
            $order_query = 'ORDER BY album_add_time, artist_alphabetic, album';
            $order_bitmap_addtime = '<span class="fa fa-sort-numeric-asc"></span>';
            $sort_addtime = 'desc';
        }
        elseif ($order == 'addtime' && $sort == 'desc') {
            $order_query = 'ORDER BY album_add_time DESC, artist_alphabetic DESC, album DESC';
            $order_bitmap_addtime = '<span class="fa fa-sort-numeric-desc"></span>';
            $sort_addtime = 'asc';
        }
        elseif ($order == 'decade' && $sort == 'asc') {
            $order_query = 'ORDER BY year, month, artist_alphabetic, album';
            $order_bitmap_decade = '<span class="fa fa-sort-numeric-asc"></span>';
            $sort_decade = 'desc';
        }
        elseif ($order == 'decade' && $sort == 'desc') {
            $order_query = 'ORDER BY year DESC, month DESC, artist_alphabetic DESC, album DESC';
            $order_bitmap_decade = '<span class="fa fa-sort-numeric-desc"></span>';
            $sort_decade = 'asc';
        }
        elseif ($order == 'album' && $sort == 'asc') {
            $order_query = 'ORDER BY album, artist_alphabetic, year, month';
            //$query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, genre_id, image_id, album_id FROM album ' . $filter_query . ' ' . $order_query);
            $order_bitmap_album = '<span class="fa fa-sort-alpha-asc"></span>';
            $sort_album = 'desc';
        }
        elseif ($order == 'album' && $sort == 'desc') {
            $order_query = 'ORDER BY album DESC, artist_alphabetic DESC, year DESC, month DESC';
            //$query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, genre_id, image_id, album_id FROM album ' . $filter_query . ' ' . $order_query);
            $order_bitmap_album = '<span class="fa fa-sort-alpha-desc"></span>';
            $sort_album = 'asc';
        }
        elseif ($order == 'artist' && $sort == 'asc') {
            $order_query = 'ORDER BY artist_alphabetic, year, month, album';
            //$query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, genre_id, image_id, album_id FROM album ' . $filter_query . ' ' . $order_query);
            $order_bitmap_artist = '<span class="fa fa-sort-alpha-asc"></span>';
            $sort_artist = 'desc';
        }
        elseif ($order == 'artist' && $sort == 'desc') {
            $order_query = 'ORDER BY artist_alphabetic DESC, year DESC, month DESC, album DESC';
            //$query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, genre_id, image_id, album_id FROM album ' . $filter_query . ' ' . $order_query);
            $order_bitmap_artist = '<span class="fa fa-sort-alpha-desc"></span>';
            $sort_artist = 'asc';
        }
        elseif ($order == 'genre' && $sort == 'asc') {
            $order_query = 'ORDER BY genre, artist_alphabetic, year, month';
            //$query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, album.genre_id, image_id, album_id FROM album, genre ' . $filter_query . ' AND album.genre_id = genre.genre_id ' . $order_query);
            $order_bitmap_genre = '<span class="fa fa-sort-alpha-asc"></span>';
            $sort_genre = 'desc';
        }
        elseif ($order == 'genre' && $sort == 'desc') {
            $order_query = 'ORDER BY genre DESC, artist_alphabetic DESC , year DESC, month DESC';
            //$query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, album.genre_id, image_id, album_id FROM album, genre ' . $filter_query . ' AND album.genre_id = genre.genre_id ' . $order_query);
            $order_bitmap_genre = '<span class="fa fa-sort-alpha-desc"></span>';
            $sort_genre = 'asc';
        }
        else message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]order or sort');
        
        $query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, album.genre_id, image_id, album_id FROM album, genre ' . $filter_query . ' AND album.genre_id = genre.genre_id ' . $order_query);
        
        $cfg['items_count'] = $album_count = mysqli_num_rows($query);
        //echo $filter_query;
        if ($album_count > $max_item_per_page) {
            $query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, album.genre_id, image_id, album_id FROM album, genre ' . $filter_query . ' AND album.genre_id = genre.genre_id ' . $order_query .
            ' LIMIT ' . ($page - 1) * $max_item_per_page . ','  . ($max_item_per_page));    
        }
        
    }
    
    if ($tag) {
        $order_query = 'ORDER BY album.artist, year';
        //ONLY_FULL_GROUP_BY
        $query_str = ('SELECT album, album.artist, artist_alphabetic, album.year, month, genre_id, image_id, album.album_id, comment FROM album, track WHERE album.album_id=track.album_id AND comment like "%' . $tag . '%" GROUP BY track.album_id ' . $order_query);     
        //$query_str = ('SELECT album, album.artist, artist_alphabetic, album.year, month, genre_id, image_id, album.album_id, comment FROM album, track WHERE album.album_id=track.album_id AND comment like "%' . $tag . '%" GROUP BY track.album_id, album, album.artist, artist_alphabetic, album.year, month, genre_id, image_id, album.album_id, comment ' . $order_query);       
        //$query_str = ('SELECT album, album.artist, artist_alphabetic, album.year, month, genre_id, image_id, album.album_id, comment FROM album LEFT JOIN track ON album.album_id=track.album_id WHERE comment like "%' . $tag . '%" ' . $order_query);       
        $order_bitmap_artist = '<span class="fa fa-sort-alpha-asc"></span>';
        $sort_album = 'desc';
        
        $query = mysqli_query($db, $query_str);
            $cfg['items_count'] = $album_count = mysqli_num_rows($query);
            if ($album_count > $max_item_per_page) {
                $query_str = $query_str . ' LIMIT ' . ($page - 1) * $max_item_per_page . ','  . ($max_item_per_page);
                $query = mysqli_query($db, $query_str);
            }
        
    }
    
    if ($qsType) {
    
        $order_query = 'ORDER BY album.artist, year, album';
        $query_str = ('SELECT album, album.artist, artist_alphabetic, album.year, month, genre_id, image_id, album.album_id FROM album, track WHERE album.album_id=track.album_id AND (' . $cfg['quick_search'][$qsType][1] . ') GROUP BY track.album_id ' . $order_query);     
        $order_bitmap_artist = '<span class="fa fa-sort-alpha-asc"></span>';
        $sort_album = 'desc';
            
            $query = mysqli_query($db, $query_str);
            $cfg['items_count'] = $album_count = mysqli_num_rows($query);
            if ($album_count > $max_item_per_page) {
                $query_str = $query_str . ' LIMIT ' . ($page - 1) * $max_item_per_page . ','  . ($max_item_per_page);
                $query = mysqli_query($db, $query_str);
            }   
        }
    
    
    
    
//  +------------------------------------------------------------------------+
//  | View 2 - thumbnail mode                                                |
//  +------------------------------------------------------------------------+
    if ($thumbnail) {
    
    global $base_size, $spaces, $scroll_bar_correction, $tileSizePHP;
    $rowsTA = 0;
    $group_found = 'none';
    $display_all_tracks = false;
    $i          = 0;
    
    //$colombs  = floor((cookie('netjukebox_width') - 20) / ($size + 10));
    $sort_url   = $url;
    $size_url   = $url . '&amp;order=' . $order . '&amp;sort=' . $sort;
    
    /* $base        = (cookie('netjukebox_width') - 20) / ($base_size + 10);
    $colombs    = floor($base);
    $aval_width = (cookie('netjukebox_width') - 20 - $scroll_bar_correction) - ($colombs - 1) * $spaces;
    $size = floor($aval_width / $colombs);
     */
    $rows = mysqli_num_rows($query);
    
    $resultsFound = false;
    
    if ($rows > 0) {
        $display_all_tracks = true;
        $resultsFound = true;
    
    ?>
    
<table cellspacing="0" cellpadding="0" class="border">
<tr>
    <td colspan="<?php echo $colombs + 2; ?>">
    <!-- begin table header -->
    <table width="100%" cellspacing="0" cellpadding="0">
    <tr class="header">
        
        <td>
        <?php if (!($tag || $qsType)) {?>
            <a <?php echo ($order_bitmap_artist == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="<?php echo $sort_url; ?>&amp;order=artist&amp;sort=<?php echo $sort_artist; ?>">&nbsp;Artist <?php echo $order_bitmap_artist; ?></a>
            &nbsp;<a <?php echo ($order_bitmap_album == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="<?php echo $sort_url; ?>&amp;order=album&amp;sort=<?php echo $sort_album; ?>">Album <?php echo $order_bitmap_album; ?></a>
            &nbsp;<a <?php echo ($order_bitmap_genre == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="<?php echo $sort_url; ?>&amp;order=genre&amp;sort=<?php echo $sort_genre; ?>">Genre <?php echo $order_bitmap_genre; ?></a>
            &nbsp;<a <?php echo ($order_bitmap_year == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="<?php echo $sort_url; ?>&amp;order=year&amp;sort=<?php echo $sort_year; ?>">Year <?php echo $order_bitmap_year; ?></a>
            &nbsp;<a <?php echo ($order_bitmap_decade == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="<?php echo $sort_url; ?>&amp;order=decade&amp;sort=<?php echo $sort_decade; ?>">Decade <?php echo $order_bitmap_decade; ?></a>
            &nbsp;<a <?php echo ($order_bitmap_addtime == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="<?php echo $sort_url; ?>&amp;order=addtime&amp;sort=<?php echo $sort_addtime; ?>">Add time <?php echo $order_bitmap_addtime; ?></a>
        <?php };?>
        </td>
        <td align="right" class="right">
            (<span id="album_count"><?php echo ($album_count > 1) ? $album_count . '</span> albums' :  $album_count . '</span> album' ?>)&nbsp;
        </td>
    </tr>
    </table>
    <!-- end table header -->
    </td>
</tr>
</table>
<div class="albums_container">
<?php
    $mdTab = array();
    while ($album = mysqli_fetch_assoc($query)) {       
            $multidisc_count = 0;
            if ($album) {
                if ($order == 'decade') {
                    $yearAct = floor(($album['year'])/10) * 10;
                    if ($yearAct != $yearPrev){
                        echo '<div class="decade">' . $yearAct . '\'s</div>';
                    }
                    else {
                        //echo '<div style="clear: both;">Act: ' . $yearAct . ' Prev: ' . $yearPrev . '</div>';
                    }
                }
                
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
                $yearPrev = $yearAct;
            }
        } 
?>
</div>
<script>
    $("#album_count").text("<?php echo $album_count; ?>");
</script>


<?php
}; //if $rows > 0


if ($filter == 'whole' && !$genre_id && !$year) {
//  +------------------------------------------------------------------------+
//  | track artist                                                           |
//  +------------------------------------------------------------------------+
    
    
    $filter_queryTA = str_replace('artist ','track.artist ',$filter_query);
    $queryTA = mysqli_query($db, 'SELECT track.artist as track_artist, track.title, track.featuring, track.album_id, track.track_id, track.miliseconds, track.number, album.image_id, album.album, album.artist
    FROM track
    INNER JOIN album ON track.album_id = album.album_id '
    . $filter_queryTA . 
    ' AND (track.artist <> album.artist) 
    AND (album.artist NOT LIKE "%' .  mysqli_real_escape_string($db,get('artist')) . '%")
    GROUP BY track.artist');
    
    
    //WHERE track.artist LIKE "%' .  mysqli_real_escape_string($db,$search_string) . '%"
    $rows = mysqli_num_rows($queryTA);
    
    if ($rows > 0) {
        if($rows > 1) $display_all_tracks = false;
        $match_found = true;
        if ($group_found == 'none') $group_found = 'TA';
?>
<h1 onclick='toggleSearchResults("TA");' class="pointer"><i id="iconSearchResultsTA" class="fa fa-chevron-circle-down icon-anchor"></i> Track artist (<?php if ($rows > 1) {
            echo $rows . " matches found";
        }
        else {
            $album = mysqli_fetch_assoc($queryTA);
            echo $rows . " match found: " . $album['track_artist'];
        }
        ?>)
</h1>
<div id="searchResultsTA">
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
    <td class="icon"></td><!-- track menu -->
    <td class="icon"></td><!-- add track -->
    <td class="track-list-artist">Track artist&nbsp;</td>
    <td>Title&nbsp;</td>
    <td>Album&nbsp;</td>
    <td class="icon"></td><!-- star -->
    <td align="right" class="time">Time</td>
    <td class="space right"></td>
</tr>

<?php
    $i=0;
    /* $queryTA = mysqli_query($db, 'SELECT track.artist as track_artist, track.title, track.featuring, track.album_id, track.track_id, track.miliseconds, track.relative_file, track.number, album.image_id, album.album, album.artist
    FROM track
    INNER JOIN album ON track.album_id = album.album_id '
    . $filter_queryTA .
    ' AND (track.artist <> album.artist)
    AND (album.artist NOT LIKE "%' .  mysqli_real_escape_string($db,get('artist')) . '%")
    ORDER BY track.artist, album.album'); */
    
    $search_string = get('artist');
    
    $queryTA = mysqli_query($db,'SELECT * FROM
    (SELECT track.artist as track_artist, track.title, track.featuring, track.album_id, track.track_id as tid, track.miliseconds, track.number, track.relative_file, album.image_id, album.album, album.artist
    FROM track
    INNER JOIN album ON track.album_id = album.album_id '
    . $filter_queryTA .
    ' AND track.artist <> album.artist
    AND album.artist NOT LIKE "%' . mysqli_real_escape_string($db,$search_string) . '%"
    ORDER BY track.artist, album.album, track.title) as a
    LEFT JOIN 
    (SELECT track_id, favorite_id FROM favoriteitem WHERE favorite_id = "' . $cfg['favorite_id'] . '") as b ON b.track_id = a.tid
    LEFT JOIN 
    (SELECT track_id, favorite_id as blacklist_id FROM favoriteitem WHERE favorite_id = "' . $cfg['blacklist_id'] . '") as bl ON bl.track_id = a.tid
    ORDER BY a.track_artist
    ');
    
    $rowsTA = mysqli_num_rows($queryTA);
    
    while ($track = mysqli_fetch_assoc($queryTA)) {
            $resultsFound = true;?>
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
        
    <td class="track-list-artist"><?php if (mysqli_num_rows(mysqli_query($db, 'SELECT track_id FROM track WHERE track.artist="' .  mysqli_real_escape_string($db,$track['track_artist']) . '"')) > 1) echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year">' . html($track['track_artist']) . '</a>'; else echo html($track['track_artist']); ?></td>
    
    <td><?php if ($cfg['access_play'])      echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            elseif ($cfg['access_add'])     echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            elseif ($cfg['access_stream'])  echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['tid'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            else                            echo html($track['title']); ?>
    <span class="track-list-artist-narrow">by <?php echo html($track['track_artist']); ?></span> 
    </td>
    <td><a href="index.php?action=view3&amp;album_id=<?php echo $track['album_id']; ?>" <?php echo onmouseoverImage($track['image_id']); ?>><?php echo html($track['album']); ?></a></td>
    
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
        <td colspan="10">
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
    echo "</table>";
    echo "</div>";
    echo '<script>';
    if ($group_found != 'none') { echo 'toggleSearchResults("' . $group_found . '")';}
    echo '</script>';
}
//End of Track artist   



//  +------------------------------------------------------------------------+
//  | tracks in Favorite                                                     |
//  +------------------------------------------------------------------------+
    $rows = 0;
    
    $queryFav = mysqli_query($db,"SELECT * FROM favorite WHERE name='" . $cfg['favorite_name'] . "' AND comment = '" . $cfg['favorite_comment'] . "'");
    
    $fav_rows = mysqli_num_rows($queryFav);
    
    if ($fav_rows > 0) {
        $favorites = mysqli_fetch_assoc($queryFav);
        $favId = $favorites['favorite_id'];
        
    
        //$filter_queryTA = str_replace('artist ','track.artist ',$filter_query);
        
        $queryFav = mysqli_query($db, 'SELECT track.artist as track_artist, track.title, track.featuring, track.album_id, track.track_id, track.miliseconds, track.number
        FROM track
        INNER JOIN favoriteitem ON track.track_id = favoriteitem.track_id '
        . $filter_query . 
        ' AND (favoriteitem.favorite_id = "' . $favId . '") 
        GROUP BY track.artist');
        
        $rows = mysqli_num_rows($queryFav);
    
    }
    if ($rows > 0) {
        if($rows > 1) $display_all_tracks = false;
        $match_found = true;
        //if ($group_found == 'none') 
            $group_found = 'FAV';
        $tracksFav = mysqli_fetch_assoc($queryFav);
?>
<h1 onclick='toggleSearchResults("FAV");' class="pointer"><i id="iconSearchResultsFAV" class="fa fa-chevron-circle-down icon-anchor"></i> Favorites tracks by <?php 
        echo ($tracksFav['track_artist']);
        ?>
</h1>
<div id="searchResultsFAV">
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
    <td class="icon"></td><!-- track menu -->
    <td class="icon"></td><!-- add track -->
    <td class="track-list-artist">Track artist&nbsp;</td>
    <td>Title&nbsp;</td>
    <td>Album&nbsp;</td>
    <td class="icon"></td><!-- star -->
    <td align="right" class="time">Time</td>
    <td class="space right"></td>
</tr>

<?php
    $i=0;
    
    $search_string = get('artist');
    $filter_query = str_replace('artist ','track.artist ',$filter_query);
    $queryFav = mysqli_query($db, 'SELECT track.artist as track_artist, track.title, track.featuring, track.album_id, track.track_id as tid, track.miliseconds, track.number, favoriteitem.favorite_id, album.album
        FROM track
        INNER JOIN favoriteitem ON track.track_id = favoriteitem.track_id 
        LEFT JOIN album ON track.album_id = album.album_id '
        . $filter_query . 
        ' AND (favoriteitem.favorite_id = "' . $favId . '") 
        ');
    
    //$rowsTA = mysqli_num_rows($queryFav);
    while ($track = mysqli_fetch_assoc($queryFav)) {
            $resultsFound = true;?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover" id="fav_<?php echo $track['tid']; ?>">
    <td class="icon">
    <span id="menu-track<?php echo $i ?>_fav">
    <div onclick='toggleMenuSub("<?php echo $i ?>_fav");'>
        <i id="menu-icon<?php echo $i ?>_fav" class="fa fa-bars icon-small"></i>
    </div>
    </span>
    </td>
    
    <td class="icon">
    <span>
    <?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['tid'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track ' . $track['number'] . '\');" onMouseOut="return nd();"><i id="add_' . $track['tid'] . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';?>
    </span>
    </td>
        
    <td class="track-list-artist"><?php if (mysqli_num_rows(mysqli_query($db, 'SELECT track_id FROM track WHERE track.artist="' .  mysqli_real_escape_string($db,$track['track_artist']) . '"')) > 1) echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year">' . html($track['track_artist']) . '</a>'; else echo html($track['track_artist']); ?></td>
    
    <td><?php if ($cfg['access_play'])      echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            elseif ($cfg['access_add'])     echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            elseif ($cfg['access_stream'])  echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['tid'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
            else                            echo html($track['title']); ?>
    <span class="track-list-artist-narrow">by <?php echo html($track['track_artist']); ?></span> 
    </td>
    <td><a href="index.php?action=view3&amp;album_id=<?php echo $track['album_id']; ?>" <?php echo onmouseoverImage($track['image_id']); ?>><?php echo html($track['album']); ?></a></td>
    
    <?php
    $isFavorite = false;
    $isBlacklist = false;
    if ($track['favorite_id']) $isFavorite = true;
    if ($track['blacklist_id']) $isBlacklist = true;
    $tid = $track['tid'];
    ?>
    
    <td onclick="toggleStarSub('<?php echo $i ?>_fav','<?php echo $tid ?>_fav');" class="pl-favorites">
        <span id="blacklist-star-bg<?php echo $tid ?>_fav" class="<?php if ($isBlacklist) echo ' blackstar blackstar-selected'; ?>">
        <i class="fa fa-star<?php if (!$isFavorite) echo '-o'; ?> fa-fw" id="favorite_star-<?php echo $tid; ?>_fav"></i>
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
        <?php starSubMenu($i . '_fav', $isFavorite, $isBlacklist, $tid);?>
        </td>
    </tr>

<tr>
<td colspan="20">
<?php trackSubMenu($i . '_fav', $track);?>
</td>
</tr>

<?php
    }
    echo "</table>";
    echo "</div>";
    echo '<script>';
    if ($group_found != 'none') { echo 'toggleSearchResults("' . $group_found . '")';}
    echo '</script>';
}
//End of Tracks in Favorite



if ($resultsFound == false && $group_found == 'none') echo 'No results found.';

} //if ($filter == 'whole')

?>

<table cellspacing="0" cellpadding="0" class="border">
<tr style="display:none" class="smallspace"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr style="display:none" class="line"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<?php
    $query = mysqli_query($db, 'SELECT artist FROM album ' . $filter_query . ' GROUP BY artist');
    if ((mysqli_num_rows($query) < 2) && $display_all_tracks) {
        $album = mysqli_fetch_assoc($query);
        if ($album['artist'] == '') $album['artist'] = $artist;
        $query = mysqli_query($db, 'SELECT album_id from track where artist = "' .  mysqli_real_escape_string($db,$album['artist']) . '"');
        $tracks = mysqli_num_rows($query);
?>

<tr class="footer">
    <td colspan="<?php echo $colombs; ?>">&nbsp;<a href="index.php?action=view3all&amp;artist=<?php echo rawurlencode($album['artist']); ?>&amp;order=title">View all tracks from <?php echo html($album['artist']); ?> 
    <!-- <?php echo ($tracks + $rowsTA) . ((($tracks + $rowsTA) == 1) ? ' track from ' : ' tracks from ') . html($album['artist']); ?> -->
    </a></td>
    <td></td>
    <td></td>
</tr>
<?php
    } ?>

</table>

<?php
}
?>

<?php
    require_once('include/footer.inc.php');
}
