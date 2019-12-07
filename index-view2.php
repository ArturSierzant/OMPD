<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2019 Artur Sierzant                            |
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


global $cfg, $db;
authenticate('access_media');
$genre_id = get('genre_id');
if ($genre_id)
	genreNavigator($genre_id);



$title	 	= get('title');
$artist	 	= get('artist');
$tidalArtistId	 	= get('tidalArtistId');
$tag		= get('tag');
$year		= (get('year') == 'Unknown' ? get('year'): (int) get('year'));
$dr		= (get('dr') == 'Unknown' ? get('dr'): (int) get('dr'));
$filter  	= get('filter')				or $filter = 'whole';
$thumbnail	= 1;
$order	 	= get('order')				or $order = ($year || $dr ? 'artist' : (in_array(strtolower($artist), $cfg['no_album_artist']) ? 'album' : 'year'));
$sort	 	= get('sort') == 'desc'		? 'desc' : 'asc';
$qsType 	= (int) get('qsType')				or $qsType = false;

//$artist = moveTheToEnd($artist);

$sort_artist			= 'asc';
$sort_album				= 'asc';
$sort_genre				= 'asc';
$sort_year 				= 'asc';
$sort_decade			= 'asc';
$sort_addtime 			= 'desc';

$order_bitmap_artist	= '<span class="typcn"></span>';
$order_bitmap_album		= '<span class="typcn"></span>';
$order_bitmap_genre		= '<span class="typcn"></span>';
$order_bitmap_year		= '<span class="typcn"></span>';
$order_bitmap_decade	= '<span class="typcn"></span>';
$order_bitmap_addtime	= '<span class="typcn"></span>';

$yearAct				= 0;
$yearPrev				= 1;

$page = (get('page') ? get('page') : 1);
$max_item_per_page = $cfg['max_items_per_page'];

$isVA = false;

if (isset($_GET['thumbnail'])) {
	mysqli_query($db, 'UPDATE session
		SET thumbnail	= ' . (int) $thumbnail . '
		WHERE sid		= BINARY "' .  mysqli_real_escape_string($db,$cfg['sid']) . '"');
}
else
	$thumbnail = $cfg['thumbnail'];


if ($genre_id || $title) {
	if ($genre_id) {							
		$filter_query = 'WHERE genre_id LIKE "%;' . mysqli_real_escape_like($genre_id) . ';%"';
	}
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

	$url			= 'index.php?action=view2&amp;genre_id=' . rawurlencode($genre_id);
	$list_url		= 'index.php?action=view2&amp;thumbnail=0&amp;genre_id=' . rawurlencode($genre_id) . '&amp;filter=' . $filter . '&amp;order=' . $order;
	$thumbnail_url	= 'index.php?action=view2&amp;thumbnail=1&amp;genre_id=' . rawurlencode($genre_id) . '&amp;filter=' . $filter . '&amp;order=' . $order;
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
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'Year';
	$nav['url'][]	= 'index.php?action=viewYear';
	if (is_numeric($minYear)) {
		$nav['name'][]	= $minYear;
		$nav['url'][]	= 'index.php?action=view2&year=' . ($minYear);
	}
	$nav['name'][] 	= $year;
	$nav['url'][]	= "";
	if (is_numeric($maxYear) && $year < date("Y")) {
		$nav['name'][]	= $maxYear;
		$nav['url'][]	= 'index.php?action=view2&year=' . ($maxYear);
	}
	require_once('include/header.inc.php');
	
	if ($year == 'Unknown') $filter_query = 'WHERE year is null ';
	else $filter_query = 'WHERE year = ' . (int) $year;
	$url			= 'index.php?action=view2&amp;year=' . $year;
	$list_url		= 'index.php?action=view2&amp;thumbnail=0&amp;year=' . $year . '&amp;order=' . $order . '&amp;sort=' . $sort;
	$thumbnail_url	= 'index.php?action=view2&amp;thumbnail=1&amp;year=' . $year . '&amp;order=' . $order . '&amp;sort=' . $sort;
}
elseif ($dr) {
	// formattedNavigator
	$queryDR = mysqli_query($db, "SELECT MIN(album_dr) as maxDR from album WHERE album_dr > '" . $dr . "'");
	$rst = mysqli_fetch_assoc($queryDR);
	$maxDR = $rst['maxDR'];
	
	$queryDR = mysqli_query($db, "SELECT MAX(album_dr) as minDR from album WHERE album_dr < '" . $dr . "'");
	$rst = mysqli_fetch_assoc($queryDR);
	$minDR = $rst['minDR'];
	
	
	
	$nav = array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'Album Dynamic Range (DR)';
	$nav['url'][]	= 'index.php?action=viewDR';
	if (is_numeric($minDR)) {
		$nav['name'][]	= $minDR;
		$nav['url'][]	= 'index.php?action=view2&dr=' . ($minDR);
	}
	$nav['name'][] 	= $dr;
	$nav['url'][]	= "";
	if (is_numeric($maxDR)) {
		$nav['name'][]	= $maxDR;
		$nav['url'][]	= 'index.php?action=view2&dr=' . ($maxDR);
	}
	require_once('include/header.inc.php');
	
	if ($dr == 'Unknown') $filter_query = 'WHERE album_dr is null ';
	else $filter_query = 'WHERE album_dr = ' . (int) $dr;
	$url			= 'index.php?action=view2&amp;dr=' . $dr;
	$list_url		= 'index.php?action=view2&amp;thumbnail=0&amp;dr=' . $dr . '&amp;order=' . $order . '&amp;sort=' . $sort;
	$thumbnail_url	= 'index.php?action=view2&amp;thumbnail=1&amp;dr=' . $dr . '&amp;order=' . $order . '&amp;sort=' . $sort;
}
else {
	if ($filter == 'all' || $artist == '') {
		$artist = 'All albums';
		$filter = 'all';
	}
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	if ($qsType) $nav['name'][] = $cfg['quick_search'][$qsType][0];
	elseif ($tag) 	$nav['name'][]	= $tag;
	else 	$nav['name'][]	= 'Artist: ' . $artist;
	
	require_once('include/header.inc.php');

	if ($filter == 'all')			$filter_query = 'WHERE 1';
	elseif ($filter == 'exact')		{
		if ($artist == 'Various Artists') {
			$where = '';
			foreach($cfg['VA'] as $va) {
				$where_part = 'artist_alphabetic = "' .  mysqli_real_escape_string($db,$va) . '" OR artist = "' .  mysqli_real_escape_string($db,$va) . '"';
				$where = ($where == '' ? $where_part : $where . ' OR ' . $where_part);
			}
		}
		else {
			if (hasThe($artist)){
				$where = 'artist = "' .  mysqli_real_escape_string($db,moveTheToEnd($artist)) . '" OR artist = "' .  mysqli_real_escape_string($db,moveTheToBegining($artist)) . '"';
			}
			else {
				$where = 'artist = "' .  mysqli_real_escape_string($db,$artist) . '"';
			}
		}
		$filter_query = 'WHERE (' . $where . ')';
	}
	elseif ($filter == 'like'){		
		if (hasThe($artist)){
			$filter_query = 'WHERE (artist LIKE "%' .  mysqli_real_escape_string($db,moveTheToEnd($artist)) . '%" OR artist LIKE "%' .  mysqli_real_escape_string($db,moveTheToBegining($artist)) . '%")';
		}
		else {
			$filter_query = 'WHERE (artist LIKE "%' .  mysqli_real_escape_string($db,$artist) . '%")';
		}
	}
	elseif ($filter == 'smart')		$filter_query = 'WHERE (artist_alphabetic  LIKE "%' . mysqli_real_escape_like($artist) . '%" OR artist LIKE "%' . mysqli_real_escape_like($artist) . '%" OR artist SOUNDS LIKE "' .  mysqli_real_escape_string($db,$artist) . '")';
	elseif ($filter == 'start')		$filter_query = 'WHERE (artist_alphabetic  LIKE "' . mysqli_real_escape_like($artist) . '%")';
	elseif ($filter == 'symbol')	$filter_query = 'WHERE (artist_alphabetic  NOT BETWEEN "a" AND "zzzzzz")';
	elseif ($filter == 'whole') {
		if (in_array($artist,$cfg['VA'])) {
			$where = '';
			foreach($cfg['VA'] as $va) {
				$where_part = 'artist_alphabetic = "' .  mysqli_real_escape_string($db,$va) . '" OR artist = "' .  mysqli_real_escape_string($db,$va) . '"';
				$where = ($where == '' ? $where_part : $where . ' OR ' . $where_part);
				$filter_query = 'WHERE (' . $where . ')';
				$isVA = true;
			}
		}
		else {
			//$art = str_replace(
			$art =  mysqli_real_escape_string($db,$artist);
			$art = replaceAnds($art);
			$as = $cfg['artist_separator'];
			$count = count($as);
			$i=0;
			$search_str = '';
			
			for($i=0; $i<$count; $i++) {
				if (hasThe($artist)){
					$search_str .= ' OR artist LIKE "' . moveTheToEnd($art) . $as[$i] . '%" 
					OR artist LIKE "%' . $as[$i] . moveTheToEnd($art) . '" 
					OR artist LIKE "%' . $as[$i] . moveTheToEnd($art) . $as[$i] . '%" 
					OR artist LIKE "% & ' . moveTheToEnd($art) . $as[$i] . '%" 
					OR artist LIKE "%' . $as[$i] . moveTheToEnd($art) . ' & %"';
					$search_str .= ' OR artist LIKE "' . moveTheToBegining($art) . $as[$i] . '%" 
					OR artist LIKE "%' . $as[$i] . moveTheToBegining($art) . '" 
					OR artist LIKE "%' . $as[$i] . moveTheToBegining($art) . $as[$i] . '%" 
					OR artist LIKE "% & ' . moveTheToBegining($art) . $as[$i] . '%" 
					OR artist LIKE "%' . $as[$i] . moveTheToBegining($art) . ' & %"';
				}
				else {
					$search_str .= ' OR artist LIKE "' . $art . '' . $as[$i] . '%" 
					OR artist LIKE "%' . $as[$i] . '' . $art . '" 
					OR artist LIKE "%' . $as[$i] . '' . $art . '' . $as[$i] . '%" 
					OR artist LIKE "% & ' . $art . '' . $as[$i] . '%" 
					OR artist LIKE "%' . $as[$i] . '' . $art . ' & %"';
					//last 2 lines above for artist like 'Mitch & Mitch' in 'Zbigniew Wodecki; Mitch & Mitch; Orchestra and Choir'
				}
			}
			
			if (hasThe($artist)){
				$filter_query = 'WHERE (
				artist = "' .  mysqli_real_escape_string($db,moveTheToBegining($artist)) . '" OR artist LIKE "' .mysqli_real_escape_string($db,moveTheToBegining($art)) . '" OR artist = "' .  mysqli_real_escape_string($db,moveTheToEnd($artist)) . '" OR artist LIKE "' .mysqli_real_escape_string($db,moveTheToEnd($art)) . '"' . $search_str . ')';
			}
			else {
				$filter_query = 'WHERE (
				artist = "' .  mysqli_real_escape_string($db,$artist) . '" OR artist LIKE "' .mysqli_real_escape_string($db,$art) . '"' . $search_str . ')';
			}
		}
	}
	else							message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]filter');
	
	$url			= 'index.php?action=view2&amp;artist=' . rawurlencode($artist) . '&amp;filter=' . $filter;
	$list_url		= 'index.php?action=view2&amp;thumbnail=0&amp;artist=' . rawurlencode($artist) . '&amp;filter=' . $filter . '&amp;order=' . $order . '&amp;sort=' . $sort;
	$thumbnail_url	= 'index.php?action=view2&amp;thumbnail=1&amp;artist=' . rawurlencode($artist) . '&amp;filter=' . $filter . '&amp;order=' . $order . '&amp;sort=' . $sort;
}
if (($artist || $year || $dr)) {
	if ($order == 'year' && $sort == 'asc') {
		$order_query = 'ORDER BY year, month, artist_alphabetic, album';
		$order_bitmap_year = '<span class="fa fa-sort-numeric-asc"></span>';
		$sort_year = 'desc';
	}
	elseif ($order == 'year' && $sort == 'desc') {
		$order_query = 'ORDER BY year DESC, month DESC, artist_alphabetic DESC, album DESC';
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
		$order_bitmap_album = '<span class="fa fa-sort-alpha-asc"></span>';
		$sort_album = 'desc';
	}
	elseif ($order == 'album' && $sort == 'desc') {
		$order_query = 'ORDER BY album DESC, artist_alphabetic DESC, year DESC, month DESC';
		$order_bitmap_album = '<span class="fa fa-sort-alpha-desc"></span>';
		$sort_album = 'asc';
	}
	elseif ($order == 'artist' && $sort == 'asc') {
		$order_query = 'ORDER BY artist_alphabetic, year, month, album';
		$order_bitmap_artist = '<span class="fa fa-sort-alpha-asc"></span>';
		$sort_artist = 'desc';
	}
	elseif ($order == 'artist' && $sort == 'desc') {
		$order_query = 'ORDER BY artist_alphabetic DESC, year DESC, month DESC, album DESC';
		$order_bitmap_artist = '<span class="fa fa-sort-alpha-desc"></span>';
		$sort_artist = 'asc';
	}
	elseif ($order == 'genre' && $sort == 'asc') {
		$order_query = 'ORDER BY length(SUBSTRING_INDEX(genre_id, ";", 2)), SUBSTRING_INDEX(genre_id, ";", 2), artist_alphabetic, year, month';
		//$order_query = 'ORDER BY length(genre_id), genre_id, artist_alphabetic, year, month';
		$order_bitmap_genre = '<span class="fa fa-sort-alpha-asc"></span>';
		$sort_genre = 'desc';
	}
	elseif ($order == 'genre' && $sort == 'desc') {
		$order_query = 'ORDER BY length(SUBSTRING_INDEX(genre_id, ";", 2)) DESC, SUBSTRING_INDEX(genre_id, ";", 2) DESC, artist_alphabetic DESC , year DESC, month DESC';
		//$order_query = 'ORDER BY length(genre_id) DESC, genre_id DESC, artist_alphabetic DESC , year DESC, month DESC';
		$order_bitmap_genre = '<span class="fa fa-sort-alpha-desc"></span>';
		$sort_genre = 'asc';
	}
	else message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]order or sort');
	
	$query = mysqli_query($db, 'SELECT album, artist, artist_alphabetic, year, month, genre_id, image_id, album_id, album_dr FROM album ' . $filter_query . ' ' . $order_query);
	//message(__FILE__, __LINE__, 'error', 'SELECT album, artist, artist_alphabetic, year, month, genre_id, image_id, album_id FROM album ' . $filter_query . ' ' . $order_query);
	//echo $filter_query;
}

if ($tag) {
	$order_query = 'ORDER BY album.artist, year, album';
	//ONLY_FULL_GROUP_BY
	$query_str = ('SELECT album, album.artist, artist_alphabetic, album.year, month, genre_id, image_id, album.album_id, comment FROM album, track WHERE album.album_id=track.album_id AND comment like "%' . $tag . '%" GROUP BY track.album_id ' . $order_query);		
	
	$order_bitmap_artist = '<span class="fa fa-sort-alpha-asc"></span>';
	$sort_album = 'desc';
	
	$query = mysqli_query($db, $query_str);
}

if ($qsType) {

	$order_query = 'ORDER BY album.artist, year, album';
	$query_str = ('SELECT album, album.artist, artist_alphabetic, album.year, month, genre_id, image_id, album.album_id FROM album, track WHERE album.album_id=track.album_id AND (' . $cfg['quick_search'][$qsType][1] . ') GROUP BY track.album_id ' . $order_query);		
	$order_bitmap_artist = '<span class="fa fa-sort-alpha-asc"></span>';
	$sort_album = 'desc';
	$query = mysqli_query($db, $query_str);	
}




//  +------------------------------------------------------------------------+
//  | View 2 - thumbnail mode                                                |
//  +------------------------------------------------------------------------+
if ($thumbnail) {

global $base_size, $spaces, $scroll_bar_correction, $tileSizePHP;
$rowsTA = 0;
$group_found = 'none';
$display_all_tracks = false;
$i			= 0;

$sort_url	= $url;
$size_url	= $url . '&amp;order=' . $order . '&amp;sort=' . $sort;

if ($cfg['use_tidal'] && $artist && !$qsType && !$tag && !in_array($artist,$cfg['VA']) ) {
?>


<div>
<h1 onclick='toggleSearchResults("TB");' class="pointer" id="tidalBio"><i id="iconSearchResultsTB" class="fa fa-chevron-circle-down icon-anchor"></i> Artist biography</h1>
<div id="searchResultsTB" class="">
<span id="bioLoadingIndicator">
	<i class="fa fa-cog fa-spin icon-small"></i> Loading information...
</span>
</div>
</div>
<script>

$('#tidalBio').click(function() {	
<?php 
if ($tileSizePHP) {
	$size = $tileSizePHP;
}
else {
	$size = '$tileSize';
}
?>
if ($( "#searchResultsTB" ).html().indexOf('Loading information') != -1){
	var size = <?php echo $size; ?>;
	console.log ('$tileSize: ' + $tileSize);
	var artist = "<?php echo str_replace('"','',$artist); ?>";
	var request = $.ajax({  
		url: "ajax-tidal-search.php",  
		type: "POST",  
		data: { search: "bio", tileSize : size, searchStr : artist, ajax : true },  
		dataType: "json"
	}); 

	request.done(function( data ) {
		if (data["artist_count"] > 0) { //check if any artist recieved
			//$("[id='suggested']").show();
			var bio = data["text"];
			var source = data["source"];
			var img = "";
			var pic = "";
			var related_artists = "";
			
			if (source) {
				source = '<div class="total-time artist_bio_source">Source: ' + source + '</div>';
			}
			else {
				source = "";
			}
			if(data["picture"]) {
				//pic = '<?php echo TIDAL_RESOURCES_URL; ?>' + data["picture"] + '/480x480.jpg';
				pic = data["picture"];
				img = '<img src="' + pic + '">'
			}
			else {
				img='<div class="artist_bio_pic_not_found"><i class="fa fa-user"></i></div>';
			}
			var artist_bio = '<div style="background-image: url(' + pic + '); background-position: -1000px -1000px;" class="artist_bio_pic">' + img + '</div>';
			if (bio) {
				artist_bio += '<div class="artist_bio_text">' + bio + '</div>';
			}
			artist_bio += source;
			if (data["related_artists"]) {
				related_artists = '<div style="text-transform: uppercase;"><h1>Related artists:</h1></div><br/><div class="artist_bio_related">';
				$.each(data["related_artists"], function(index, value){
					img = '<i class="fa fa-user" style="font-size: 6em;"></i>';
					if (value["picture"]) {
						img = '<img src="' + value["picture"] + '">';
					}
					related_artists += '<div class="artist_related"><a href="index.php?action=view2&artist=' + encodeURIComponent(value["name"]) + '&order=year"><div class="artist_container_small">' + img + '</div><div>' + value["name"] + '</div></a></div>';
				});
				related_artists +='</div>';
			}
			artist_bio = artist_bio + related_artists;
			$( "#searchResultsTB" ).html( artist_bio );
		}
		else {
			if (data["return"] != 0) {
				$("#bioLoadingIndicator").hide();
				$("#searchResultsTB").html('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution Tidal request.<br>Error message:<br><br>' + data["response"] + '</div>');
			}
			else {
				$("#bioLoadingIndicator").hide();
				$("#searchResultsTB").html('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on TIDAL.</span>');
			}
		}
		
		//console.log (data.length);
	}); 

	request.fail(function( jqXHR, textStatus ) {  
		//alert( "Request failed: " + textStatus );	
	}); 

	request.always(function() {
		$('[id^="add_tidal"]').click(function(){
			$(this).removeClass('fa-plus-circle').addClass('fa-cog fa-spin icon-selected');
		});

		$('[id^="play_tidal"]').click(function(){
			$(this).removeClass('fa-play-circle-o').addClass('fa-cog fa-spin icon-selected');
		});
		
	});
};
});

</script>
<?php
} //if($cfg['use_tidal'])

$rows = mysqli_num_rows($query);

$resultsFound = false;

if ($rows > 0) {
	$display_all_tracks = true;
	$resultsFound = true;
	$album_multidisc = albumMultidisc($query);
if($genre_id){
	$query = mysqli_query($db,"SELECT genre FROM genre WHERE genre_id=" . mysqli_real_escape_string($db,$genre_id));
	$rows = mysqli_fetch_assoc($query);
	$g = $rows['genre'];
?>
<div class="buttons"><span id="fav4genre">Show favorite <?php echo $g; ?> tracks</span></div>
<script>
$("#fav4genre").on("click",function(){
	window.location = "search.php?action=fav4genre&genre_id=<?php echo $genre_id; ?>"
})
</script>
<?php 
	}
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
		<span id="album_count">
		<?php 
		if ($cfg['items_count'] > 1) {
			echo '(' . $cfg['items_count'] .' albums)';
		}
		else {
			echo '(' . $cfg['items_count'] .' album)';
		}
		?>
		</span>&nbsp;
	</td>
</tr>
</table>
<!-- end table header -->
</td>
</tr>
</table>
<div class="albums_container">
<?php

if ($tileSizePHP) $size = $tileSizePHP;
foreach (array_slice($album_multidisc,($page - 1) * $max_item_per_page, $max_item_per_page) as $album_m) {	
	if ($order == 'decade') {
		$yearAct = floor(($album_m['year'])/10) * 10;
		if ($yearAct != $yearPrev){
			echo '<div class="decade">' . $yearAct . '\'s</div>';
		}
	}
	if ($order == 'genre') {
		$genreAct = $album_m['genre_id'];
		$genres = explode(";",$genreAct);
		$genreAct = $genres[1];
		$query = mysqli_query($db,'SELECT genre, genre_id
			FROM genre 
			WHERE genre_id ="' . $genreAct . '"
			ORDER BY genre');
		$genre = mysqli_fetch_assoc($query);
		
		if ($genreAct != $genrePrev){
			echo '<span class="nav_tree"><a href="index.php?action=view2&order=artist&sort=asc&genre_id=' . $genreAct. '">' . html($genre['genre']) . '</a></span>';
			//echo '<div class="decade">' . html($genre['genre']) . '</div>';
		}
	}
	draw_tile($size,$album_m,$album_m['allDiscs']);
	$yearPrev = $yearAct;
	$genrePrev = $genreAct;
}
?>
</div>
<?php
}; //if $rows > 0


//  +------------------------------------------------------------------------+
//  | albums and top tracks from Tidal                                       |
//  +------------------------------------------------------------------------+
if ($cfg['use_tidal'] && $artist && $artist != 'All albums' && !in_array($artist,$cfg['VA'])) {
?>
<div>
<h1 onclick='toggleSearchResults("TI");' class="pointer" id="tidalAlbums"><i id="iconSearchResultsTI" class="fa fa-chevron-circle-down icon-anchor"></i> Albums from Tidal</h1>
<div id="searchResultsTI" class="albums_container">
<span id="albumsLoadingIndicator">
	<i class="fa fa-cog fa-spin icon-small"></i> Loading albums, EPs and singles...
</span>
</div>
</div>

<div>
<h1 onclick='toggleSearchResults("TOPT");' class="pointer" id="tidalTopTracks"><i id="iconSearchResultsTOPT" class="fa fa-chevron-circle-down icon-anchor"></i> Top 10 tracks from Tidal</h1>
<div id="searchResultsTOPT">
<span id="topTracksLoadingIndicator">
	<i class="fa fa-cog fa-spin icon-small"></i> Loading top tracks...
</span>
</div>
</div>

<script>

$('#tidalAlbums').click(function() {	
<?php 
//$artist = replaceAnds($artist);
if ($tileSizePHP) {
	$size = $tileSizePHP;
}
else {
	$size = '$tileSize';
}
?>
//$('#iframeRefresh').removeClass("icon-anchor");
//$('#iframeRefresh').addClass("icon-selected fa-spin");
//var size = $tileSize;
var size = <?php echo $size; ?>;
console.log ('$tileSize: ' + $tileSize);
var artist = "<?php echo str_replace('"','', $artist); ?>";
var tidalArtistId = "<?php echo $tidalArtistId; ?>";

//var artist = '<?php echo ($artist); ?>';
var request = $.ajax({  
	url: "ajax-tidal-search.php",  
	type: "POST",  
	data: { search : "albums", tileSize : size, searchStr : artist, ajax : true, tidalArtistId : tidalArtistId },  
	dataType: "html"
}); 

request.done(function( data ) {  
	if (data.indexOf('tile') > 0) { //check if any album recieved
		//$("[id='suggested']").show();
		$( "#searchResultsTI" ).html( data );
	}
	else {
		var jsonObj = JSON.parse(data);
		if (jsonObj.return == 1) {
			$("#albumsLoadingIndicator").hide();
			$("#searchResultsTI").html('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution Tidal request.<br>Error message:<br><br>' + jsonObj.response + '</div>');
		}
		else {
			$("#albumsLoadingIndicator").hide();
			$("#searchResultsTI").html('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on TIDAL.</span>');
		}
	}
	calcTileSize();
	
	//console.log (data.length);
}); 

request.fail(function( jqXHR, textStatus ) {  
	//alert( "Request failed: " + textStatus );	
}); 

request.always(function() {
	$('[id^="add_tidal"]').click(function(){
		$(this).removeClass('fa-plus-circle').addClass('fa-cog fa-spin icon-selected');
	});

	$('[id^="play_tidal"]').click(function(){
		$(this).removeClass('fa-play-circle-o').addClass('fa-cog fa-spin icon-selected');
	});
	
});

});
<?php
	$artist = replaceAnds($artist);
	if (hasThe($artist)){
		$sql = "SELECT MIN(last_update_time) as last_update_time 
		FROM tidal_album 
		WHERE (artist LIKE '" . mysqli_real_escape_string($db,moveTheToEnd($artist)) . "'
		OR artist LIKE '" . mysqli_real_escape_string($db,moveTheToBegining($artist)) . "')
		AND last_update_time > 0";
	}
	else {
		$sql = "SELECT MIN(last_update_time) as last_update_time 
		FROM tidal_album 
		WHERE artist LIKE '" . mysqli_real_escape_string($db,$artist) . "'
		AND last_update_time > 0";
	}
	$query = mysqli_query($db, $sql);
	$res = mysqli_fetch_assoc($query);
	if ($res['last_update_time'] > (time() - TIDAL_MAX_CACHE_TIME)) {
?>
$('#tidalAlbums').click();
<?php
	}
?>

$('#tidalTopTracks').click(function() {	
var artist = "<?php echo str_replace('"','', $artist); ?>";
var tidalArtistId = "<?php echo $tidalArtistId; ?>";

var request = $.ajax({  
	url: "ajax-tidal-search.php",  
	type: "POST",  
	data: { search : "topTracks", searchStr : artist, tidalArtistId : tidalArtistId },
	dataType: "json"
}); 

request.done(function( data ) {  
	if (data.tracks_results > 0) { //check if any album recieved
		$( "#searchResultsTOPT" ).html( data.top_tracks );
	}
	else {
		if (data.return == 1) {
			$("#topTracksLoadingIndicator").hide();
			$("#searchResultsTOPT").html('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution Tidal request.<br>Error message:<br><br>' + data.response + '</div>');
		}
		else {
			$("#topTracksLoadingIndicator").hide();
			$("#searchResultsTOPT").html('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on TIDAL.</span>');
		}
	}
	setAnchorClick();
}); 

request.fail(function( jqXHR, textStatus ) {  
	//alert( "Request failed: " + textStatus );	
}); 

request.always(function() {
	$('[id^="add_tidal"]').click(function(){
		$(this).removeClass('fa-plus-circle').addClass('fa-cog fa-spin icon-selected');
	});

	$('[id^="play_tidal"]').click(function(){
		$(this).removeClass('fa-play-circle-o').addClass('fa-cog fa-spin icon-selected');
	});
	
});

});
</script>




<?php
} //use_tidal



if ($filter == 'whole' && !$genre_id && !$year && !$isVA) {
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
<td class="icon">
<span onMouseOver="return overlib('Add all tracks');" onMouseOut="return nd();">
<?php if ($cfg['access_add'])  echo '<i id="add_all_TA" class="fa fa-plus-circle fa-fw icon-small pointer"></i>';?>
</span>
</td><!-- add track -->
<td class="track-list-artist">Track artist&nbsp;</td>
<td>Title&nbsp;</td>
<td>Album&nbsp;</td>
<td class="time pl-genre">Genre&nbsp;</td>
<td class="icon"></td><!-- star -->
<?php if ($cfg['show_DR']){ ?>
<td class="time pl-tdr">DR</td>
<?php } ?>
<td align="right" class="time time_w">Time</td>
<td class="space right"></td>
</tr>

<?php
$i=0;
$TA_ids = '';
$search_string = get('artist');

$queryTA = mysqli_query($db,'SELECT * FROM
(SELECT track.artist as track_artist, track.title, track.featuring, track.album_id, track.track_id as tid, track.miliseconds, track.number, track.relative_file, track.genre, track.dr, album.image_id, album.album, album.artist
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
		$resultsFound = true;
		$TA_ids = ($TA_ids == '' ? $track['tid'] : $TA_ids . ';' . $track['tid']);
?>
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
<!--	
<td class="track-list-artist"><?php if (mysqli_num_rows(mysqli_query($db, 'SELECT track_id FROM track WHERE track.artist="' .  mysqli_real_escape_string($db,$track['track_artist']) . '"')) > 1) echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year">' . html($track['track_artist']) . '</a>'; else echo html($track['track_artist']); ?></td>
-->

<td class="track-list-artist">
<?php 
$artist = '';
$exploded = multiexplode($cfg['artist_separator'],$track['track_artist']);
$l = count($exploded);
if ($l > 1) {
	for ($j=0; $j<$l; $j++) {
		$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($exploded[$j]) . '">' . html($exploded[$j]) . '</a>';
		if ($j != $l - 1) {
			$delimiter = getInbetweenStrings($exploded[$j],$exploded[$j + 1], $track['track_artist']);
			$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year"><span 	class="artist_all">' . $delimiter[0] . '</span></a>';
		}
	}
	echo $artist;
}
else {
	echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year">' . html($track['track_artist']) . '</a>';
} 
?>
</td>

<td><?php if ($cfg['access_play']) 		echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
		elseif ($cfg['access_add'])		echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
		elseif ($cfg['access_stream'])	echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['tid'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
		else 							echo html($track['title']); ?>
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

<td class="time pl-genre"><?php 
	$album_genres = parseMultiGenre($track['genre']);
	if (count($album_genres) > 0) { 
		foreach($album_genres as $g_id => $ag) {
	?>
		<a href="index.php?action=view2&order=artist&sort=asc&genre_id=<?php echo $g_id; ?>"><?php echo $ag; ?></a><br>
	<?php 
		}
	}
?>
</td>

<td onclick="toggleStarSub(<?php echo $i ?>,'<?php echo $tid ?>');" class="pl-favorites">
	<span id="blacklist-star-bg<?php echo $tid ?>" class="<?php if ($isBlacklist) echo ' blackstar blackstar-selected'; ?>">
	<i class="fa fa-star<?php if (!$isFavorite) echo '-o'; ?> fa-fw" id="favorite_star-<?php echo $tid; ?>"></i>
	</span>
</td>

<?php if ($cfg['show_DR']){ ?>
<td class="pl-tdr">
<?php
	$tdr = ($track['dr'] === NULL ? '-' : $track['dr']);
	echo $tdr;
?>
</td>
<?php } ?>

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
?>
</table>
</div>
<?php 
if ($group_found != 'none') { 
?>
<script>
	toggleSearchResults("<?php echo $group_found; ?>");
	$("#add_all_TA").click(function(){
		
		$.ajax({
			type: "GET",
			url: "play.php",
			data: { 'action': 'addMultitrack', 'track_ids': '<?php echo $TA_ids; ?>', 'addType':'all_TA' },
			dataType : 'json',
			success : function(json) {
				evaluateAdd(json);
			},
			error : function() {
				$("#add_all_TA").removeClass('fa-cog fa-spin icon-selected').addClass('fa-plus-circle');
			}	
		});	
		
		
	});
</script>
<?php 
}
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
	
	$queryFav = mysqli_query($db, 'SELECT track.artist as track_artist, track.title, track.featuring, track.album_id, track.track_id,  track.miliseconds, track.number
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
<td class="icon">
<span onMouseOver="return overlib('Add all tracks');" onMouseOut="return nd();">
<?php if ($cfg['access_add'])  echo '<i id="add_all_FAV" class="fa fa-plus-circle fa-fw icon-small pointer"></i>';?>
</span>
</td><!-- add track -->
<td class="track-list-artist">Track artist&nbsp;</td>
<td>Title&nbsp;</td>
<td>Album&nbsp;</td>
<td class="time pl-genre">Genre&nbsp;</td>
<td class="icon"></td><!-- star -->
<?php if ($cfg['show_DR']){ ?>
<td class="time pl-tdr">DR</td>
<?php } ?>
<td align="right" class="time time_w">Time</td>
<td class="space right"></td>
</tr>

<?php
$i=0;
$FAV_ids = '';
$search_string = get('artist');
$filter_queryFAV = str_replace('artist ','track.artist ',$filter_query);
$queryFav = mysqli_query($db, 'SELECT track.artist as track_artist, track.title, track.featuring, track.album_id, track.track_id as tid, track.relative_file, track.miliseconds, track.number, track.genre, track.dr, favoriteitem.favorite_id, album.album
	FROM track
	INNER JOIN favoriteitem ON track.track_id = favoriteitem.track_id 
	LEFT JOIN album ON track.album_id = album.album_id '
	. $filter_queryFAV . 
	' AND (favoriteitem.favorite_id = "' . $favId . '") 
	');

//$rowsTA = mysqli_num_rows($queryFav);
while ($track = mysqli_fetch_assoc($queryFav)) {
		$resultsFound = true;
		$FAV_ids = ($FAV_ids == '' ? $track['tid'] : $FAV_ids . ';' . $track['tid']);
?>
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
	
<td class="track-list-artist">
<?php 
$artist = '';
$exploded = multiexplode($cfg['artist_separator'],$track['track_artist']);
$l = count($exploded);
if ($l > 1) {
	for ($j=0; $j<$l; $j++) {
		$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($exploded[$j]) . '">' . html($exploded[$j]) . '</a>';
		if ($j != $l - 1) {
			$delimiter = getInbetweenStrings($exploded[$j],$exploded[$j + 1], $track['track_artist']);
			$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year"><span 	class="artist_all">' . $delimiter[0] . '</span></a>';
		}
	}
	echo $artist;
}
else {
	echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year">' . html($track['track_artist']) . '</a>';
} 
?>
</td>

<td><?php if ($cfg['access_play']) 		echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
		elseif ($cfg['access_add'])		echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
		elseif ($cfg['access_stream'])	echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['tid'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
		else 							echo html($track['title']); ?>
<span class="track-list-artist-narrow">by <?php echo html($track['track_artist']); ?></span> 
</td>
<td><a href="index.php?action=view3&amp;album_id=<?php echo $track['album_id']; ?>" <?php echo onmouseoverImage($track['image_id']); ?>><?php echo html($track['album']); ?></a></td>

<td class="time pl-genre"><?php 
	$album_genres = parseMultiGenre($track['genre']);
	if (count($album_genres) > 0) { 
		foreach($album_genres as $g_id => $ag) {
	?>
		<a href="index.php?action=view2&order=artist&sort=asc&genre_id=<?php echo $g_id; ?>"><?php echo $ag; ?></a><br>
	<?php 
		}
	}
?>
</td>

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

<?php if ($cfg['show_DR']){ ?>
<td class="pl-tdr">
<?php
	$tdr = ($track['dr'] === NULL ? '-' : $track['dr']);
	echo $tdr;
?>
</td>
<?php } ?>

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
?>
</table>
</div>
<?php	
if ($group_found != 'none') { 
?>
<script>
	toggleSearchResults("<?php echo $group_found; ?>");
	$("#add_all_FAV").click(function(){
		
		$.ajax({
			type: "GET",
			url: "play.php",
			data: { 'action': 'addMultitrack', 'track_ids': '<?php echo $FAV_ids; ?>', 'addType':'all_FAV'},
			dataType : 'json',
			success : function(json) {	
				evaluateAdd(json);
			},
			error : function() {
				$("#add_all_FAV").removeClass('fa-cog fa-spin icon-selected').addClass('fa-plus-circle');
			}	
		});	
		
		
	});
</script>
<?php
}
}
//End of Tracks in Favorite



//  +------------------------------------------------------------------------+
//  | track composer                                                         |
//  +------------------------------------------------------------------------+

$filter_queryTC = str_replace('artist ','track.composer ',$filter_query);
$art = mysqli_real_escape_string($db,get('artist'));
//temporary add ', ' as artist separator
if ($cfg['testing'] == 'on') {
	$aux_search_str = ' OR track.composer LIKE "' . $art . ', %" 
			OR track.composer LIKE "%, ' . $art . '" 
			OR track.composer LIKE "%, ' . $art . ', %" 
			OR track.composer LIKE "% & ' . $art . ', %" 
			OR track.composer LIKE "%, ' . $art . ' & %"';
	$filter_queryTC = str_replace(')','',$filter_queryTC) . $aux_search_str . ')';
}
$queryTC = mysqli_query($db, 'SELECT track.composer as track_composer, track.title, track.featuring, track.album_id, track.track_id, track.miliseconds, track.number, album.image_id, album.album, album.artist
FROM track
INNER JOIN album ON track.album_id = album.album_id '
. $filter_queryTC . 
' AND (track.composer <> album.artist) 
AND (album.artist NOT LIKE "%' .  mysqli_real_escape_string($db,$art) . '%")
GROUP BY track.composer');

$rows = mysqli_num_rows($queryTC);

if ($rows > 0) {
	if($rows > 1) $display_all_tracks = false;
	$match_found = true;
	//if ($group_found == 'none') 
	$group_found = 'TC';
?>
<h1 onclick='toggleSearchResults("TC");' class="pointer"><i id="iconSearchResultsTC" class="fa fa-chevron-circle-down icon-anchor"></i> Track composer (<?php if ($rows > 1) {
		echo $rows . " matches found";
	}
	else {
		$album = mysqli_fetch_assoc($queryTC);
		echo $rows . " match found: " . $album['track_composer'];
	}
	?>)
</h1>
<div id="searchResultsTC">
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
<td class="icon"></td><!-- track menu -->
<td class="icon">
<span onMouseOver="return overlib('Add all tracks');" onMouseOut="return nd();">
<?php if ($cfg['access_add'])  echo '<i id="add_all_TC" class="fa fa-plus-circle fa-fw icon-small pointer"></i>';?>
</span>
</td><!-- add track -->
<td class="track-list-artist">Track artist&nbsp;</td>
<td>Title&nbsp;</td>
<td>Album&nbsp;</td>
<td class="time pl-genre">Genre&nbsp;</td>
<td class="icon"></td><!-- star -->
<?php if ($cfg['show_DR']){ ?>
<td class="time pl-tdr">DR</td>
<?php } ?>
<td align="right" class="time time_w">Time</td>
<td class="space right"></td>
</tr>

<?php
$i=10000000;
$TC_ids = '';
$search_string = get('artist');

$queryTCstring = 'SELECT * FROM
(SELECT track.artist as track_artist, track.composer as track_composer, track.title, track.featuring, track.album_id, track.track_id as tid, track.miliseconds, track.number, track.relative_file, track.genre, track.dr, album.image_id, album.album, album.artist
FROM track
INNER JOIN album ON track.album_id = album.album_id ' . $filter_queryTC . ' 
AND album.artist NOT LIKE "%' . mysqli_real_escape_string($db,$search_string) . '%"
ORDER BY track.artist, album.album, track.title) as a
LEFT JOIN 
(SELECT track_id, favorite_id FROM favoriteitem WHERE favorite_id = "' . $cfg['favorite_id'] . '") as b ON b.track_id = a.tid
LEFT JOIN 
(SELECT track_id, favorite_id as blacklist_id FROM favoriteitem WHERE favorite_id = "' . $cfg['blacklist_id'] . '") as bl ON bl.track_id = a.tid
ORDER BY a.track_composer
';
$queryTC = mysqli_query($db,$queryTCstring);

//$rowsTA = mysqli_num_rows($queryTC);
$prevComp = '';
$currComp = '';
$k = 1;
while ($track = mysqli_fetch_assoc($queryTC)) {
		$resultsFound = true;
		$TC_ids = ($TC_ids == '' ? $track['tid'] : $TC_ids . ';' . $track['tid']);
if ($rows > 1) {
		$currComp = $track['track_composer'];
		if ($prevComp != $currComp){
?>
		<tr class="header">
			<td colspan="20" class="break-word padding3">
			<?php 
			echo $k . ". ";
			$artist = '';
			
			if ($cfg['testing'] == 'on' && !in_array(', ',$cfg['artist_separator'])) {
				$cfg['artist_separator'][] = ', ';
			}
			$exploded = multiexplode($cfg['artist_separator'],$track['track_composer']);
			
			$l = count($exploded);
			if ($l > 1) {
				for ($j=0; $j<$l; $j++) {
					$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($exploded[$j]) . '">' . html($exploded[$j]) . '</a>';
					if ($j != $l - 1) {
						$delimiter = getInbetweenStrings($exploded[$j],$exploded[$j + 1], $track['track_composer']);
						$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_composer']) . '&amp;order=year"><span 	class="artist_all">' . $delimiter[0] . '</span></a>';
					}
				}
				echo $artist;
			}
			else {
				echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_composer']) . '&amp;order=year">' . html($track['track_composer']) . '</a>';
			}
			echo ":";
			?>
			</td>
		</tr>
<?php 
		$prevComp = $track['track_composer'];
		$k++;
		}
	}
?>
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
<!--	
<td class="track-list-artist"><?php if (mysqli_num_rows(mysqli_query($db, 'SELECT track_id FROM track WHERE track.artist="' .  mysqli_real_escape_string($db,$track['track_artist']) . '"')) > 1) echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year">' . html($track['track_artist']) . '</a>'; else echo html($track['track_artist']); ?></td>
-->

<td class="track-list-artist">
<?php 
$artist = '';
$exploded = multiexplode($cfg['artist_separator'],$track['track_artist']);
$l = count($exploded);
if ($l > 1) {
	for ($j=0; $j<$l; $j++) {
		$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($exploded[$j]) . '">' . html($exploded[$j]) . '</a>';
		if ($j != $l - 1) {
			$delimiter = getInbetweenStrings($exploded[$j],$exploded[$j + 1], $track['track_artist']);
			$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year"><span 	class="artist_all">' . $delimiter[0] . '</span></a>';
		}
	}
	echo $artist;
}
else {
	echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year">' . html($track['track_artist']) . '</a>';
} 
?>
</td>

<td><?php if ($cfg['access_play']) 		echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
		elseif ($cfg['access_add'])		echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
		elseif ($cfg['access_stream'])	echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['tid'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
		else 							echo html($track['title']); ?>
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

<td class="time pl-genre"><?php 
	$album_genres = parseMultiGenre($track['genre']);
	if (count($album_genres) > 0) { 
		foreach($album_genres as $g_id => $ag) {
	?>
		<a href="index.php?action=view2&order=artist&sort=asc&genre_id=<?php echo $g_id; ?>"><?php echo $ag; ?></a><br>
	<?php 
		}
	}
?>
</td>

<td onclick="toggleStarSub(<?php echo $i ?>,'<?php echo $tid ?>');" class="pl-favorites">
	<span id="blacklist-star-bg<?php echo $tid ?>" class="<?php if ($isBlacklist) echo ' blackstar blackstar-selected'; ?>">
	<i class="fa fa-star<?php if (!$isFavorite) echo '-o'; ?> fa-fw" id="favorite_star-<?php echo $tid; ?>"></i>
	</span>
</td>

<?php if ($cfg['show_DR']){ ?>
<td class="pl-tdr">
<?php
	$tdr = ($track['dr'] === NULL ? '-' : $track['dr']);
	echo $tdr;
?>
</td>
<?php } ?>

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
?>
</table>
</div>
<?php 
if ($group_found != 'none') { 
?>
<script>
	toggleSearchResults("<?php echo $group_found; ?>");
	$("#add_all_TC").click(function(){
		
		$.ajax({
			type: "GET",
			url: "play.php",
			data: { 'action': 'addMultitrack', 'track_ids': '<?php echo $TC_ids; ?>', 'addType':'all_TC' },
			dataType : 'json',
			success : function(json) {
				evaluateAdd(json);
			},
			error : function() {
				$("#add_all_TC").removeClass('fa-cog fa-spin icon-selected').addClass('fa-plus-circle');
			}	
		});	
		
		
	});
</script>
<?php 
}
}
//End of Track composer	




if ($resultsFound == false && $group_found == 'none') echo '<h1>No results found in local DB.</h1>';

} //if ($filter == 'whole')


?>

<table cellspacing="0" cellpadding="0" class="border">
<tr style="display:none" class="smallspace"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr style="display:none" class="line"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<?php
$filter_query = str_replace('track.artist ','artist ',$filter_query);
$query = mysqli_query($db, 'SELECT artist FROM album ' . $filter_query . ' GROUP BY artist');
if ((mysqli_num_rows($query) < 2) && $display_all_tracks) {
	$album = mysqli_fetch_assoc($query);
	if ($album['artist'] == '') $album['artist'] = $artist;
	$query = mysqli_query($db, 'SELECT album_id from track where artist = "' .  mysqli_real_escape_string($db,$album['artist']) . '"');
	$tracks = mysqli_num_rows($query);
?>

<tr class="footer">
<td colspan="<?php echo $colombs; ?>">&nbsp;
<a href="index.php?action=view3all&amp;artist=<?php echo rawurlencode($album['artist']); ?>&amp;order=title">View all tracks from <?php echo html($album['artist']); ?> 
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
require_once('include/footer.inc.php');

?>
