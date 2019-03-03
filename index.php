<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2018 Artur Sierzant                            |
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
//  | index.php                                                              |
//  +------------------------------------------------------------------------+
//error_reporting(-1);
//ini_set("display_errors", 1);



require_once('include/initialize.inc.php');
require_once('include/library.inc.php');



if (cookie('netjukebox_width')<385) {$base_size = 90;}
elseif (cookie('netjukebox_width')<641) {$base_size = 120;}
else {$base_size = 150;}


$cfg['menu']	= 'Library';
$action 		= get('action');
$tileSizePHP	= get('tileSizePHP')	or $tileSizePHP = false;

if		($action == '')					home();
elseif	($action == 'view1')			view1();
elseif	($action == 'view2')			view2();
elseif	($action == 'view3')			view3();
elseif	($action == 'view1all')			view1all();
elseif	($action == 'view3all')			view3all();
elseif	($action == 'viewRandomAlbum')	viewRandomAlbum();
elseif	($action == 'viewRandomTrack')	viewRandomTrack();
elseif	($action == 'viewRandomFile')	viewRandomFile();
elseif	($action == 'viewYear')			viewYear();
elseif	($action == 'viewGenre')		viewGenre();
elseif	($action == 'viewDR')			viewDR();
elseif	($action == 'viewComposer')		viewComposer();
elseif	($action == 'viewNew')			viewNew();
elseif	($action == 'viewPopular')		viewPopular();
elseif	($action == 'viewRecentlyPlayed')		viewRecentlyPlayed();
elseif	($action == 'viewPlayedAtDay')			viewPlayedAtDay();
else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');
exit();







//  +------------------------------------------------------------------------+
//  | Home                                                                   |
//  +------------------------------------------------------------------------+
function home() {
	global $cfg, $db;
	
	authenticate('access_media');
	genreNavigator('start');
	
	?>
	
<script type="text/javascript">
<!--

var baseUrl = 'json.php?action=suggestAlbumArtist&artist=';

function showStatus() {
	alert ('ok');
}
	
function initialize() {
	//document.searchform.txt.name = 'artist';
	//document.searchform.txt.focus();
	//evaluateSuggest('');
}


function evaluateSuggest(list) {
	var suggest;
	if (list == '') {
		suggest = '<form action="">';
		suggest += '<input type="text" value="no suggestion" readonly class="autosugest_readonly">';
		suggest += '<\/form>';
	}
	else {
		suggest = '<form action="" name="suggest" id="suggest" onSubmit="suggestKeyStroke(1)" onClick="suggestKeyStroke(1)" onKeyDown="return suggestKeyStroke(event)">';
		suggest += '<select name="txt" size="6" class="autosugest">';
		for (var i in list)
			suggest += '<option value="' + list[i] + '">' + list[i] + '<\/option>';
		suggest += '<\/select><\/form>';
	}
	//document.getElementById('suggest').innerHTML = suggest;
}


function searchformKeyStroke(e) {
	var keyPressed;
	if (typeof e.keyCode != 'undefined') 	keyPressed = e.keyCode;
	else if (typeof e.which != 'undefined')	keyPressed = e.which;
	if (keyPressed == 40 && typeof document.suggest == 'object') // Down key
		{//document.suggest.txt.focus()
		};
}


function suggestKeyStroke(e) {
	var keyPressed;
	if (e == 1)									keyPressed = 13;
	else if (typeof e.keyCode != 'undefined')	keyPressed = e.keyCode;
	else if (typeof e.which != 'undefined')		keyPressed = e.which;
	if (keyPressed == 13 && document.suggest.txt.value != '') { // Enter key
		if (document.searchform.action.value == 'view1all')
			document.searchform.action.value = 'view3all';
		document.searchform.txt.value = document.suggest.txt.value;
		document.searchform.filter.value = 'exact';
		document.searchform.submit();
		return false;
	}
	else if (keyPressed == 38 && document.suggest.txt.selectedIndex == 0) { // Up key
		document.suggest.txt.selectedIndex = -1;
		document.searchform.txt.focus();
		return false;
	}
}
	

function selectTab(obj) {
	if (obj.id == 'albumartist') {
		document.getElementById('albumartist').className = 'tab_on';
		document.getElementById('trackartist').className = 'tab_off';
		document.getElementById('tracktitle').className  = 'tab_off';
		document.getElementById('albumtitle').className  = 'tab_off';
		document.getElementById('quicksearch').className  = 'tab_off';
		document.getElementById('searchform').style.visibility  = 'visible';
		document.getElementById('quicksearchform').style.visibility  = 'hidden';
		document.searchform.txt.select();
		document.searchform.txt.focus();
		document.searchform.txt.name = 'artist';
		document.searchform.action.value = 'view1';
		baseUrl = 'json.php?action=suggestAlbumArtist&artist=';
		ajaxRequest(baseUrl + <?php echo (NJB_DEFAULT_CHARSET == 'UTF-8') ? 'encodeURIComponent' : 'escape'; ?>(document.searchform.txt.value), evaluateSuggest);
	}
	else if (obj.id == 'albumtitle') {
		document.getElementById('albumartist').className = 'tab_off';
		document.getElementById('trackartist').className = 'tab_off';
		document.getElementById('tracktitle').className  = 'tab_off';
		document.getElementById('albumtitle').className  = 'tab_on';
		document.getElementById('quicksearch').className  = 'tab_off';
		document.getElementById('searchform').style.visibility  = 'visible';
		document.getElementById('quicksearchform').style.visibility  = 'hidden';
		document.searchform.txt.select();
		document.searchform.txt.focus();
		document.searchform.txt.name = 'title';
		document.searchform.action.value = 'view2';
		baseUrl = 'json.php?action=suggestAlbumTitle&title=';
		ajaxRequest(baseUrl + <?php echo (NJB_DEFAULT_CHARSET == 'UTF-8') ? 'encodeURIComponent' : 'escape'; ?>(document.searchform.txt.value), evaluateSuggest);
	}
	else if (obj.id == 'trackartist') {
		document.getElementById('albumartist').className = 'tab_off';
		document.getElementById('trackartist').className = 'tab_on';
		document.getElementById('tracktitle').className  = 'tab_off';
		document.getElementById('albumtitle').className  = 'tab_off';
		document.getElementById('quicksearch').className  = 'tab_off';
		document.getElementById('searchform').style.visibility  = 'visible';
		document.getElementById('quicksearchform').style.visibility  = 'hidden';
		document.searchform.txt.select();
		document.searchform.txt.focus();
		document.searchform.txt.name = 'artist';
		document.searchform.action.value = 'view1all';
		baseUrl = 'json.php?action=suggestTrackArtist&artist=';
		ajaxRequest(baseUrl + <?php echo (NJB_DEFAULT_CHARSET == 'UTF-8') ? 'encodeURIComponent' : 'escape'; ?>(document.searchform.txt.value), evaluateSuggest);
	}
	else if (obj.id == 'tracktitle') {
		document.getElementById('albumartist').className = 'tab_off';
		document.getElementById('trackartist').className = 'tab_off';
		document.getElementById('tracktitle').className  = 'tab_on';
		document.getElementById('albumtitle').className  = 'tab_off';
		document.getElementById('quicksearch').className  = 'tab_off';
		document.getElementById('searchform').style.visibility  = 'visible';
		document.getElementById('quicksearchform').style.visibility  = 'hidden';
		document.searchform.txt.select();
		document.searchform.txt.focus();
		document.searchform.txt.name = 'title';
		document.searchform.action.value = 'view3all';
		baseUrl = 'json.php?action=suggestTrackTitle&title=';
		ajaxRequest(baseUrl + <?php echo (NJB_DEFAULT_CHARSET == 'UTF-8') ? 'encodeURIComponent' : 'escape'; ?>(document.searchform.txt.value), evaluateSuggest);
	}
	
	else if (obj.id == 'quicksearch') {
		document.getElementById('albumartist').className = 'tab_off';
		document.getElementById('trackartist').className = 'tab_off';
		document.getElementById('tracktitle').className  = 'tab_off';
		document.getElementById('albumtitle').className  = 'tab_off';
		document.getElementById('quicksearch').className  = 'tab_on';
		document.getElementById('searchform').style.visibility  = 'hidden';
		document.getElementById('quicksearchform').style.visibility  = 'visible';
		document.searchform.txt.select();
		document.searchform.txt.focus();
		document.searchform.txt.name = 'title';
		document.searchform.action.value = 'view3all';
		baseUrl = 'json.php?action=suggestTrackTitle&title=';
		ajaxRequest(baseUrl + <?php echo (NJB_DEFAULT_CHARSET == 'UTF-8') ? 'encodeURIComponent' : 'escape'; ?>(document.searchform.txt.value), evaluateSuggest);
	}
}
//-->
</script>
<!-- <div style="height: 8px;"></div> -->
<div class="area">
<?php viewNewStartPage(); ?>  
</div>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | View 1                                                                 |
//  +------------------------------------------------------------------------+
function view1() {
	global $cfg, $db, $nav;
	authenticate('access_media');
	
	$artist	 	= get('artist');
	$genre_id 	= get('genre_id');
	$filter  	= get('filter');
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
		
		$list_url		= 'index.php?action=view2&amp;thumbnail=0&amp;genre_id=' . rawurlencode($genre_id) . '&amp;order=artist';
		$thumbnail_url	= 'index.php?action=view2&amp;thumbnail=1&amp;genre_id=' . rawurlencode($genre_id) . '&amp;order=artist';
		}
	else {
		/* if ($filter == '' || $artist == '') {
			$artist = 'All album artists';
			$filter = 'all';
		} */
		$query = '';
		if ($filter == 'all')			$query = mysqli_query($db, 'SELECT artist FROM track WHERE 1 GROUP BY artist ORDER BY artist');
		elseif ($filter == 'exact')		$query = mysqli_query($db, 'SELECT artist FROM track WHERE artist = "' .  mysqli_real_escape_string($db,$artist) . '" OR artist = "' .  mysqli_real_escape_string($db,$artist) . '" GROUP BY artist ORDER BY artist');
		elseif ($filter == 'smart')		$query = mysqli_query($db, 'SELECT artist FROM track WHERE artist LIKE "%' . mysqli_real_escape_like($artist) . '%" OR artist LIKE "%' . mysqli_real_escape_like($artist) . '%" OR artist SOUNDS LIKE "' .  mysqli_real_escape_string($db,$artist) . '" GROUP BY artist ORDER BY artist');
		elseif ($filter == 'start')		$query = mysqli_query($db, 'SELECT artist FROM track WHERE artist LIKE "' . mysqli_real_escape_like($artist) . '%" GROUP BY artist ORDER BY artist');
		elseif ($filter == 'symbol')	$query = mysqli_query($db, 'SELECT artist FROM track WHERE artist REGEXP "^[^a-z]" GROUP BY artist ORDER BY artist');
		else							message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]filter');
		
		if (mysqli_num_rows($query) == 1) {
			$album = mysqli_fetch_assoc($query);
			$_GET['artist'] = $album['artist'];
			$_GET['filter'] = 'exact';
			view2();
			exit();
		}
		
		// formattedNavigator
		$nav			= array();
		$nav['name'][]	= 'Library';
		$nav['url'][]	= 'index.php';
		if ($artist != '') $nav['name'][] = 'Artist: ' . $artist;
		elseif ($filter == 'symbol') $nav['name'][] = 'Artist: #';
		elseif ($filter == 'all') $nav['name'][] = 'All artists';
		
		
		$list_url		= 'index.php?action=view2&amp;thumbnail=0&amp;artist=' . rawurlencode($artist) . '&amp;filter=' . $filter . '&amp;order=artist';
		$thumbnail_url	= 'index.php?action=view2&amp;thumbnail=1&amp;artist=' . rawurlencode($artist) . '&amp;filter=' . $filter . '&amp;order=artist';
	} 
	
	if (count($nav['name']) == 1 )	echo '<span class="nav_home"></span>' . "\n";
	else {
	echo '<span class="nav_tree">' . "\n";
	for ($i=0; $i < count($nav['name']); $i++) {
		if ($i > 0)	echo '<span class="nav_seperation">></span>' . "\n";
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
				if ($j != $l - 1){
					$delimiter = getInbetweenStrings($exploded[$j],$exploded[$j + 1], $album['artist']);
					$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($album['artist']) . '&amp;order=year"><span class="artist_all">' . $delimiter[0] . '</span></a>';
				}
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




//  +------------------------------------------------------------------------+
//  | View 2                                                                 |
//  +------------------------------------------------------------------------+
function view2() {
	global $cfg, $db;
	authenticate('access_media');
	$genre_id = get('genre_id');
	if ($genre_id)
		genreNavigator($genre_id);
	
	
	
	$title	 	= get('title');
	$artist	 	= get('artist');
	$tag		= get('tag');
	$year		= (get('year') == 'Unknown' ? get('year'): (int) get('year'));
	$dr		= (get('dr') == 'Unknown' ? get('dr'): (int) get('dr'));
	$filter  	= get('filter')				or $filter = 'whole';
	$thumbnail	= 1;
	$order	 	= get('order')				or $order = ($year || $dr ? 'artist' : (in_array(strtolower($artist), $cfg['no_album_artist']) ? 'album' : 'year'));
	$sort	 	= get('sort') == 'desc'		? 'desc' : 'asc';
	$qsType 	= (int) get('qsType')				or $qsType = false;
	
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
				$where = 'artist_alphabetic = "' .  mysqli_real_escape_string($db,$artist) . '" OR artist = "' .  mysqli_real_escape_string($db,$artist) . '"';
			}
			$filter_query = 'WHERE (' . $where . ')';
		}
		elseif ($filter == 'like')		$filter_query = 'WHERE (artist_alphabetic LIKE "%' .  mysqli_real_escape_string($db,$artist) . '%" OR artist LIKE "%' .  mysqli_real_escape_string($db,$artist) . '%")';
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
				$art =  mysqli_real_escape_string($db,$artist);
				$as = $cfg['artist_separator'];
				$count = count($as);
				$i=0;
				$search_str = '';
				
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




if ($resultsFound == false && $group_found == 'none') echo 'No results found.';

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
?>

<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | View 3                                                                 |
//  +------------------------------------------------------------------------+
function view3() {
	global $cfg, $db;
	
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
			$counter	= mysqli_fetch_assoc($query);
			$album_id	= $counter['album_id'];
		}
		else {
			$query = mysqli_query($db, 'SELECT album_id, album_add_time
				FROM album
				ORDER BY album_add_time DESC
				LIMIT 1');
			$album		= mysqli_fetch_assoc($query);
			$album_id	= $album['album_id'];
		}
	
		header('Location: ' . NJB_HOME_URL . 'index.php?action=view3&album_id=' . rawurldecode($album_id));
		exit();
	}
	
	
	
	authenticate('access_media');
	
	$query = mysqli_query($db, 'SELECT artist_alphabetic, artist, album, year, month, image_id, album_add_time, album.genre_id, album_dr
		FROM album
		WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '"');
	$album = mysqli_fetch_assoc($query);
	$image_id = $album['image_id'];
	
	if ($album == false)
		message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]' . $album_id . ' not found in database');

	$album_genres = parseMultiGenreId($album['genre_id']);		
	
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
		$album_versions_count = 0;
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
			WHERE (' . $qs . ') AND artist = "' . mysqli_real_escape_string($db, $album['artist']) . '" 
			ORDER BY album');
			$album_versions_count = mysqli_num_rows($query_av);
		}
	}
	
	$featuring = false;
	
	$query = mysqli_query($db, 'SELECT track.audio_bits_per_sample, track.audio_sample_rate, track.audio_profile, track.audio_dataformat, track.comment, track.relative_file, album.album_dr FROM track left join album on album.album_id = track.album_id where album.album_id = "' .  mysqli_real_escape_string($db,$album_id) . '"
	LIMIT 1');
	$album_info = $rel_file = mysqli_fetch_assoc($query);
	
	$query = mysqli_query($db, 'SELECT COUNT(c.album_id) as counter, max(c.time) as time FROM (SELECT time, album_id FROM counter WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '" ORDER BY time DESC) c ORDER BY c.time');
	$played = mysqli_fetch_assoc($query);
	$rows_played = mysqli_num_rows($query);
	
	$query = mysqli_query($db, 'SELECT album_id, COUNT(*) AS counter
			FROM counter
			GROUP BY album_id
			ORDER BY counter DESC
			LIMIT 1');
	$max_played = mysqli_fetch_assoc($query);
	$rows_max_played = mysqli_num_rows($query);
	
	$query = mysqli_query($db, 'SELECT SUM(miliseconds) AS sum_miliseconds FROM track WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '"');
	$total_time = mysqli_fetch_assoc($query); 
	
	// formattedNavigator
	$nav			= array();

	$nav['name'][]	= $album['artist'] . ' - ' . $album['album'];
	
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
	
	if ($cfg['access_play'])
		$basic[] = '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;album_id=' . $album_id . '\',evaluateAdd);ajaxRequest(\'play.php?action=updateAddPlay&amp;album_id=' . $album_id . '\',updateAddPlay);"><i id="play_' . $album_id . '" class="fa fa-fw fa-play-circle-o  icon-small"></i>Play album</a>';
	if ($cfg['access_add']){
		$basic[] = '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&album_id=' . $album_id . '\',evaluateAdd);ajaxRequest(\'play.php?action=updateAddPlay&album_id=' . $album_id . '\',updateAddPlay);"><i id="add_' . $album_id . '" class="fa fa-fw  fa-plus-circle  icon-small"></i>Add to playlist</a>';
		$basic[] = '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;album_id=' . $album_id . '&amp;insertType=album\',evaluateAdd);ajaxRequest(\'play.php?action=updateAddPlay&amp;album_id=' . $album_id . '\',updateAddPlay);"><i id="insert_' . $album_id . '" class="fa fa-fw fa-indent icon-small"></i>Insert into playlist</a>';
	}
	if ($cfg['access_add'] && $cfg['access_play'])
		$basic[] = '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;album_id=' . $album_id . '&amp;insertType=album\',evaluateAdd);ajaxRequest(\'play.php?action=updateAddPlay&amp;album_id=' . $album_id . '\',updateAddPlay);"><i id="insertPlay_' . $album_id . '" class="fa fa-fw  fa-play-circle icon-small"></i>Insert and play</a>';
	if ($cfg['access_stream']){
		$basic[] = '<a href="stream.php?action=playlist&amp;album_id=' . $album_id . '&amp;stream_id=' . $cfg['stream_id'] . '"><i class="fa fa-fw  fa-rss  icon-small"></i>Stream album</a>';
	}
	if ($cfg['access_download'] && $cfg['album_download'])
		$basic[] = '<a href="download.php?action=downloadAlbum&amp;album_id=' . $album_id . '&amp;download_id=' . $cfg['download_id'] . '" ' . onmouseoverDownloadAlbum($album_id) . '><i class="fa fa-fw  fa-download  icon-small"></i>Download album</a>';
	if ($cfg['access_play']){
		$dir_path = rawurlencode(dirname($cfg['media_dir'] . $rel_file['relative_file']));
		$basic[] = '<a href="browser.php?dir=' . $dir_path . '"><i class="fa fa-fw  fa-folder-open  icon-small"></i>Browse...</a>';
	}
	if ($cfg['access_admin']){
		$dir_path = rawurlencode(dirname($cfg['media_dir'] . $rel_file['relative_file']));
		$basic[] = '<a href="update.php?action=update&amp;dir_to_update=' . $dir_path . '/&amp;sign=' . $cfg['sign'] . '"><i class="fa fa-fw fa-refresh fa-fw icon-small"></i>Update album</a>';
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
		$album_info['album_dr'] = '';
	}	
	elseif (strpos(strtolower($rel_file['relative_file']), strtolower($cfg['misc_tracks_misc_artists_folder'])) !== false) {
		$album['year'] = '';
		//$album['album_genre'] = '';
		$album_info['audio_bits_per_sample'] = '';
		$album_info['audio_sample_rate'] = '';
		$album_info['audio_dataformat'] = '';
		$album_info['audio_profile'] = '';
		$album_info['album_dr'] = '';
		
	}
	
	$idx = array_search($cfg['default_search_name'], $cfg['search_name']);
	
	
	
?>


<div id="album-info-area">

<div id="image_container">
	<script type="text/javascript">
	<?php if ($action != 'view3' && $action != 'downloadAlbum' && $action != 'downloadTrack' && $pos === false) echo ('showSpinner();'); 
	?>
</script>
	
	
	 
	<!--
	<div id="cover-spinner">
		<img src="image/loader.gif" alt="">
	</div>
	-->
	<span id="image">
		<a href="ridirect.php?search_id=<?php echo $idx; ?>&amp;album_id=<?php echo $album_id; ?>" target="_blank">
		<img id="image_in" src="image/transparent.gif" alt="">
		</a>
	</span>
	<div id="waitIndicatorImg"></div> 
	<?php 
	if ($cfg['show_discography_browser'] == true && !in_array($album['artist'],$cfg['VA'])) {
		
		$exploded = multiexplode($cfg['artist_separator'],$album['artist']);
		$l = count($exploded);
		$search_str = 'artist = "' .  mysqli_real_escape_string($db,$album['artist']) . '"';
		
		for ($j=0; $j<$l; $j++) {
			$art =  mysqli_real_escape_string($db,$exploded[$j]);
			$as = $cfg['artist_separator'];
			$count = count($as);
			$i=0;
			
			for($i=0; $i<$count; $i++) {
			$search_str .= ' OR artist = "' . $art . '" OR artist LIKE "' . $art . '' . $as[$i] . '%" 
			OR artist LIKE "%' . $as[$i] . '' . $art . '" 
			OR artist LIKE "%' . $as[$i] . '' . $art . '' . $as[$i] . '%" 
			OR artist LIKE "% & ' . $art . '' . $as[$i] . '%" 
			OR artist LIKE "%' . $as[$i] . '' . $art . ' & %"';
			//last 2 lines above for artist like 'Mitch & Mitch' in 'Zbigniew Wodecki; Mitch & Mitch; Orchestra and Choir'
			}
		}
		
		$queryStr = 'SELECT album, artist, artist_alphabetic, year, month, genre_id, image_id, album_id, album_dr FROM album WHERE (' . $search_str . '
			) ORDER BY year, month, artist_alphabetic, album';
		$query = mysqli_query($db, $queryStr);
		$discCount = mysqli_num_rows($query);
		if ($discCount > 1) {
	?>
		<div id="discBrowser">
		<?php 
		$thumbCount = 0;
		while ($discography = mysqli_fetch_assoc($query)){
			$selected = '';
			if ($album_id == $discography['album_id']) {
				$selected = ' selected';
				$thumbIDCount = $thumbCount;
			}
		echo '<img id="thumb' . $discography['album_id'] .  '" class="imgThumb' . $selected . '" onclick=\'location.href="index.php?action=view3&amp;album_id=' . $discography['album_id'] . '"\' src="image.php?image_id=' . $discography['image_id'] . '" onMouseOver="return overlib(\'' . htmlspecialchars(addslashes($discography['artist']), ENT_QUOTES) . '</div><div class=' . chr(92) . chr(39) . 'ol_line' . chr(92) . chr(39) . '><div>' . htmlspecialchars(addslashes($discography['album']), ENT_QUOTES) . '</div><div class=' . chr(92) . chr(39) . 'ol_line' . chr(92) . chr(39) . '></div><div>' . $discography['year'] . '</div>\', CAPTION , \'Go to album\');" onMouseOut="return nd();" alt="">';
		$thumbCount++;
		}
		?>
		<script>
			var thumbID = '#thumb<?php echo $album_id; ?>';
			var thumbIDCount = '<?php echo $thumbIDCount; ?>';
		</script>
		</div>
	<?php 
		}
	}
	?>
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
			for ($j=0; $j<$l; $j++) {
				$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($exploded[$j]) . '">' . html($exploded[$j]) . '</a>';
				if ($j != $l - 1) {
					$delimiter = getInbetweenStrings($exploded[$j],$exploded[$j + 1], $album['artist']);
					$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($album['artist']) . '&amp;order=year"><span 	class="artist_all">' . $delimiter[0] . '</span></a>';
				}
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
<div class="add-info-left"><a href="index.php?action=viewPopular&period=overall">Popularity:</a></div>
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

<div id="additional-info">
	<?php /* if ($album['album_genre'] != '') { ?>
	<div class="line">
		<div class="add-info-left">Genre:</div>
		<div class="add-info-right"><a href="<?php echo 'index.php?action=view2&order=artist&sort=asc&&genre_id=' . $album['genre_id'];?>"><?php echo trim($album['album_genre']);?></a></div>
	</div>
	<?php }; */ ?>
	
	<?php if (count($album_genres) > 0) { ?>
	<div class="line">
		<div class="add-info-left">Genre:</div>
		<div class="add-info-right">
		<?php 
		foreach($album_genres as $g_id => $ag) {
		?>
		
		<a href="<?php echo 'index.php?action=view2&order=artist&sort=asc&genre_id=' . $g_id;?>"><?php echo trim($ag);?></a><br>
		
		<?php } ?>
		</div>
	</div>
	<?php }; ?>
	
	<?php if ($album['year'] != '') { ?>
	<div class="line">
		<div class="add-info-left"><a href="index.php?action=viewYear">Year:</a></div>
		<div class="add-info-right"><a href="<?php echo 'index.php?action=view2&order=artist&sort=asc&year=' . $album['year'];?>"><?php echo trim($album['year']);?></a></div>
	</div>
	<?php }; ?>
	
	<?php if ($album['year'] != '') { ?>
	<div class="line">
		<div class="add-info-left">Total time:</div>
		<div class="add-info-right"><?php echo formattedTime($total_time['sum_miliseconds']);?></div>
	</div>
	<?php }; ?>
	
	<?php if (($album_info['audio_bits_per_sample'] != '') && ($album_info['audio_sample_rate'] != '')) { ?>
	<div class="line">
		<div class="add-info-left">File format:
		</div>
		<div class="add-info-right">
		<?php 
		echo   
		 '' . $album_info['audio_bits_per_sample'] . 'bit - ' . $album_info['audio_sample_rate']/1000 . 'kHz '; 
		 ?>
		</div>
	</div>
	<?php }; ?>
	
	<?php if (($album_info['album_dr'] != '')) { ?>
	<div class="line">
		<div class="add-info-left">
		<a href="index.php?action=viewDR">Album DR:</a>
		</div>
		<div class="add-info-right">
		<a href="<?php echo 'index.php?action=view2&sort=asc&dr=' . $album['album_dr'];?>"><?php echo $album['album_dr'];?></a>
		</div>
	</div>
	<?php }; ?>
	
	<?php if ($album_info['audio_dataformat'] != '' && $album_info['audio_profile'] != '') { ?>
	<div class="line">
		<div class="add-info-left">File type:
		</div>
		<div class="add-info-right">
		<?php 
		if ($album_info['audio_dataformat'] != '' && $album_info['audio_profile'] != '')
		echo strtoupper($album_info['audio_dataformat']) . ' - ' . $album_info['audio_profile'] . ''; ?>
		</div>
	</div>
	<?php }; ?>
	
	<div class="line">
		<div class="add-info-left">Added at:</div>
		<div class="add-info-right"><?php echo date("Y-m-d H:i:s",$album['album_add_time']); ?>
		</div>
	</div>
	
	<div class="line">
		<div class="add-info-left">Played:</div>
		<div class="add-info-right"><span id="played"><?php 
		if ($played['counter'] == 0) {
			echo 'Never';
		}
		else {
			echo $played['counter']; 
			echo ($played['counter'] == 1) ? ' time' : ' times'; 
		}
		?></span>
		</div>
	</div>
	
	<div class="line">
		<div class="add-info-left">Last time:</div>
		<div class="add-info-right"><span id="last_played"><?php echo ($played['time']) ? '<a href="index.php?action=viewPlayedAtDay&day=' . date("Y-m-d",$played['time']) . '">' . (date("Y-m-d H:i",$played['time']) . '</a><span id="playedCal" class=" icon-anchor" onclick="togglePlayedHistory();">&nbsp;&nbsp;<i class="fa fa-calendar fa-lg"></i></span>') : '-'; ?></span>
		</div>
	</div>
	
	<div id="playedHistory" class="line" style="display: none;">
		<div class="add-info-left"></div>
		<div class="add-info-right">Played on:</div>
		<?php 
		$queryHist = mysqli_query($db, 'SELECT time, album_id FROM counter WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '" ORDER BY time DESC');
		while($playedHistory = mysqli_fetch_assoc($queryHist)) { ?>
		<div class="add-info-left"></div>
		<div class="add-info-right"><span><?php echo ($playedHistory['time']) ? '<a href="index.php?action=viewPlayedAtDay&day=' . date("Y-m-d",$playedHistory['time']) . '">' . date("Y-m-d H:i",$playedHistory['time']) . '</a>' : '-'; ?></span>
		</div>
		<?php } ?>
	</div>
	
	<?php if ($album_info['comment'] && $cfg['show_comments_as_tags'] === true) { ?>
	<div class="line">
		<div class="add-info-left"><i class="fa fa-tags fa-lg"></i> Tags:</div>
		<div class="add-info-right"><div class="buttons">
		<?php
			$sep = 'no_sep';
			if (strpos($album_info['comment'],$cfg['tags_separator']) !== false) {
				$sep = $cfg['tags_separator'];
			}
			elseif ($cfg['testing'] == 'on' && strpos($album_info['comment']," ") !== false) {
				$sep = " ";
			}
			if ($sep != 'no_sep') {
				$tags = array_filter(explode($sep,$album_info['comment']));
				foreach ($tags as $value) { 
					echo '<span><a href="index.php?action=view2&order=artist&sort=asc&&tag=' . trim($value) . '">' . trim($value) . '</a></span>' ;
				}
			}
			else {
				echo '<span><a href="index.php?action=view2&order=artist&sort=asc&&tag=' . $album_info['comment'] . '">' . $album_info['comment'] . '</a></span>' ;
			}
		?>
		</div>
		</div>
	</div>
	<?php }; ?>
</div>

<br>	
<table cellspacing="0" cellpadding="0" id="basic" class="fullscreen">
<?php
	for ($i = 0; $i < 10; $i=$i+2) { ?>
<tr class="<?php echo ($i & 1) ? 'even_info' : 'odd_info'; ?> nowrap">
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
<tr class="<?php echo ($i & 1) ? 'even_info' : 'odd_info'; ?> nowrap">
	<td class="halfscreen"><?php echo (isset($search[$i])) ? $search[$i] : '&nbsp;'; ?></td>
	<td class="halfscreen"><?php echo (isset($search[$i+1])) ? $search[$i+1] : '&nbsp;'; ?></td>
</tr>
<?php
	} ?>
</table>

<table cellspacing="0" cellpadding="0" id="advanced" style="display: none;">
<?php
	for ($i = 0; $i < 10; $i=$i+2) { ?>
<tr class="<?php echo ($i & 1) ? 'even_info' : 'odd_info'; ?> nowrap">
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

if ($cfg['show_multidisc'] == true && $multidisc_count > 0) {
?>
<div id="multidisc">
<table>
<tr class="line"><td colspan="4"></td></tr>
<tr class="header">
<td colspan="4">
Other discs in this set:
</td>
</tr> 
<?php 
	while ($multidisc = mysqli_fetch_assoc($query_md)) {
		echo '<tr class="line"><td colspan="4"></td></tr>
		<tr>
		<td class="small_cover_md"><a><img src="image.php?image_id=' . rawurlencode($multidisc['image_id']) . '" width="100%"></a></td>
		<td><a href="index.php?action=view3&amp;album_id=' . rawurlencode($multidisc['album_id']) . '">' . $multidisc['album'] . '</a></td>
		<td class="icon">
		<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;album_id=' . $multidisc['album_id'] . '\',evaluateAdd);"><i id="play_' . $multidisc['album_id'] . '" class="fa fa-fw fa-play-circle-o  icon-small"></i></a>
		</td>
		<td class="icon">
		<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;album_id='. $multidisc['album_id'] . '\',evaluateAdd);"><i id="add_' . $multidisc['album_id'] . '" class="fa fa-fw  fa-plus-circle  icon-small"></i></a>
		</td>
		</tr>'; 
	}
if ($album_versions_count == 0) {
	echo '<tr class="line"><td colspan="4"></td></tr>';
}
?>

</table>
</div>
<?php 
}
?>

<?php 
if ($cfg['show_album_versions'] == true && $album_versions_count > 0) {
?>
<div id="album_versions">
<table>
<tr class="line"><td colspan="4"></td></tr>
<tr class="header">
<td colspan="4">
Other versions of this album:
</td>
</tr>
<?php 
	while ($multidisc = mysqli_fetch_assoc($query_av)) {
		echo '<tr class="line"><td colspan="4"></td></tr>
		<tr>
		<td class="small_cover_md"><a><img src="image.php?image_id=' . rawurlencode($multidisc['image_id']) . '" width="100%"></a></td>
		<td><a href="index.php?action=view3&amp;album_id=' . rawurlencode($multidisc['album_id']) . '">' . $multidisc['album'] . '</a></td>
		<td class="icon">
		<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;album_id=' . $multidisc['album_id'] . '\',evaluateAdd);"><i id="play_' . $multidisc['album_id'] . '" class="fa fa-fw fa-play-circle-o  icon-small"></i></a>
		</td>
		<td class="icon">
		<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;album_id='. $multidisc['album_id'] . '\',evaluateAdd);"><i id="add_' . $multidisc['album_id'] . '" class="fa fa-fw  fa-plus-circle  icon-small"></i></a>
		</td>
		</tr>'; 
	}
?>
<tr class="line"><td colspan="4"></td></tr>
</table>
</div>


<?php 
}
?>

</div>
<!-- end options -->	
</div>

<div id="playlist">
<span id="tracklist-header" class="playlist-title">
	<span id="trackLoadingIndicator">
			<i class="fa fa-cog fa-spin icon-small"></i> Loading track list...
	</span>
</span>
</div>

<script type="text/javascript">

var request = $.ajax({  
	url: "ajax-album-playlist.php",  
	type: "POST",  
	data: { 
		album_id : '<?php echo $album_id; ?>',
		image_id : '<?php echo $image_id; ?>'
	},  
	dataType: "html"
}); 

request.done(function(data) {  
	$("#playlist").html(data);
	$("#tracklist-header").html("Tracklist");
	$('[id^="playlist-table"]').show();	
	addFavSubmenuActions();
	setAnchorClick();
}); 

request.fail(function( jqXHR, textStatus ) {  
	$("#playlist").html( "Error loading playlist: " + textStatus );	
}); 

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
	$("#image_in").attr("src","image.php?image_id=<?php echo $image_id ?>&quality=hq");
	$("#cover-spinner").hide();
	addFavSubmenuActions();
	return(true);
};
</script>
<?php
	//echo '</div>' . "\n";
	require_once('include/footer.inc.php');
	
}




//  +------------------------------------------------------------------------+
//  | View 1 all                                                             |
//  +------------------------------------------------------------------------+
function view1all() {
	global $cfg, $db;
	authenticate('access_media');
	require_once('include/header.inc.php');
	
	$artist	 	= get('artist');
	$filter  	= get('filter');
	
	if ($artist == '') {
		$artist = 'All track artists';
		$filter = 'all';
	}
	
	if ($filter == 'all')		$query = mysqli_query($db, 'SELECT DISTINCT artist FROM track ORDER BY artist');
	elseif ($filter == 'smart')	$query = mysqli_query($db, 'SELECT artist FROM track WHERE artist LIKE "%' . mysqli_real_escape_like($artist) . '%" OR artist SOUNDS LIKE "' . mysqli_real_escape_like($artist) . '" GROUP BY artist ORDER BY artist');
	else						message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]filter');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= $artist;
	
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>Artist</td>
	<td class="space"></td>
</tr>

<?php
	//$query = mysqli_query($db, 'SELECT DISTINCT artist FROM track ORDER BY artist');
	$i = 0;
	while ($track = mysqli_fetch_assoc($query))	{ ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="index.php?action=view3all&amp;artist=<?php echo rawurlencode($track['artist']); ?>&amp;order=title"><?php echo html($track['artist']); ?></a></td>
	<td></td>
</tr>
<?php
}
echo '</table>' . "\n";
require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | View 3 all                                                             |
//  +------------------------------------------------------------------------+
function view3all() {
	global $cfg, $db;
	
	authenticate('access_media');
	
	$artist	 		= get('artist');
	$title	 		= get('title');
	$track_ids		= get('track_ids');
	$other_title	= get('other_title');
	$filter  		= get('filter')				or $filter	= 'start';
	$order	 		= get('order')				or $order	= 'title';
	$sort	 		= get('sort') == 'desc'		? 'desc' : 'asc';
	
	$sort_artist 			= 'asc';
	$sort_title 			= 'asc';
	$sort_featuring 		= 'asc';
	$sort_album 			= 'asc';
	
	$order_bitmap_artist	= '<span class="typcn"></span>';
	$order_bitmap_title		= '<span class="typcn"></span>';
	$order_bitmap_featuring = '<span class="typcn"></span>';
	$order_bitmap_album		= '<span class="typcn"></span>';
	
	if (strlen($title) >= 1) {
		// formattedNavigator
		$nav			= array();
		$nav['name'][]	= 'Library';
		$nav['url'][]	= 'index.php';
		$nav['name'][]	= $title;
		require_once('include/header.inc.php');
		
		if ($filter == 'start')	{
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
		elseif ($filter == 'smart')	$filter_query = 'WHERE (track.title LIKE "%' . mysqli_real_escape_like($title) . '%" OR track.title SOUNDS LIKE "' .  mysqli_real_escape_string($db,$title) . '") AND track.album_id = album.album_id';
		elseif ($filter == 'exact')	$filter_query = 'WHERE track.title = "' .  mysqli_real_escape_string($db,$title) . '" AND track.album_id = album.album_id';
		else						message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]filter');
		
		$url = 'index.php?action=view3all&amp;title=' . rawurlencode($title) . '&amp;filter=' . $filter;
	}
	elseif (strlen($artist) >= 1) {
		// formattedNavigator
		$nav			= array();
		$nav['name'][]	= 'Library';
		$nav['url'][]	= 'index.php';
		$nav['name'][]	= $artist;
		$nav['url'][]	= 'index.php?action=view2&amp;artist=' . rawurlencode($artist) . '&amp;order=year';
		$nav['name'][]	= 'All tracks';
		require_once('include/header.inc.php');
		
		$filter_query = 'WHERE track.artist="' .  mysqli_real_escape_string($db,$artist) . '" AND track.album_id = album.album_id';
		//$filter_query = 'WHERE track.artist="' .  mysqli_real_escape_string($db,$artist) . '"';
		$url = 'index.php?action=view3all&amp;artist=' . rawurlencode($artist);
	}
	elseif (strlen($track_ids) >= 1) {
		$nav			= array();
		$nav['name'][]	= 'Library';
		$nav['url'][]	= 'index.php';
		$nav['name'][]	= 'Title: \'' . $other_title . '\'';
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
	if (mysqli_fetch_row($query))	$featuring = true;
	else							$featuring = false;
	*/	
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="icon">&nbsp;</td><!-- track menu -->
	<td class="icon">
	<span onMouseOver="return overlib('Add all tracks');" onMouseOut="return nd();">
	<?php if ($cfg['access_add'])  echo '<i id="add_all_VER" class="fa fa-plus-circle fa-fw icon-small pointer"></i>';?>
	</span>
	</td><!-- add track -->
	<td class="track-list-artist"><a <?php echo ($order_bitmap_artist == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="<?php echo $url; ?>&amp;order=artist&amp;sort=<?php echo $sort_artist; ?>">Artist&nbsp;<?php echo $order_bitmap_artist; ?></a></td>
	<td><a <?php echo ($order_bitmap_title == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="<?php echo $url; ?>&amp;order=title&amp;sort=<?php echo $sort_title; ?>">Title&nbsp;<?php echo $order_bitmap_title; ?></a></td>
	<td><a <?php echo ($order_bitmap_album == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="<?php echo $url; ?>&amp;order=album&amp;sort=<?php echo $sort_album; ?>">Album&nbsp;<?php echo $order_bitmap_album; ?></a></td>
	<td class="time pl-genre">Genre&nbsp;</td>
	<td></td>
	<?php if ($cfg['show_DR']){ ?>
	<td class="time pl-tdr">DR</td>
	<?php } ?>
	<td align="right" class="time">Time</td>
	<td class="space right"></td>
</tr>

<?php
	$i=0;
	$VER_ids = '';
	//$query = mysqli_query($db, 'SELECT track.artist, track.title, track.number, track.featuring, track.album_id, track.track_id, track.miliseconds, track.relative_file, album.image_id, album.album FROM track, album ' . $filter_query . ' ' . $order_query);
	
	$q = 'SELECT * FROM
	(SELECT track.artist as track_artist, track.title, track.featuring, track.album_id, track.track_id as tid, track.miliseconds, track.number, track.relative_file, track.genre, track.dr, album.image_id, album.album, album.artist
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

	while ($track = mysqli_fetch_assoc($query)) { 
		$VER_ids = ($VER_ids == '' ? $track['tid'] : $VER_ids . ';' . $track['tid']);
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
	
	<td><?php if ($cfg['access_play']) 		echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Play track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
			elseif ($cfg['access_add'])		echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['tid'] . '\');" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
			elseif ($cfg['access_stream'])	echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['tid'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
			else 							echo html($track['title']); ?>
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
	?>
	</table>
	<script>
		$("#add_all_VER").click(function(){
			
			$.ajax({
				type: "GET",
				url: "play.php",
				data: { 'action': 'addMultitrack', 'track_ids': '<?php echo $VER_ids; ?>', 'addType':'all_VER'},
				dataType : 'json',
				success : function(json) {	
					evaluateAdd(json);
				},
				error : function() {
					$("#add_all_VER").removeClass('fa-cog fa-spin icon-selected').addClass('fa-plus-circle');
				}	
			});	
		});
	</script>
<?php	
	//echo '</table>' . "\n";
	require_once('include/footer.inc.php');
}



//  +------------------------------------------------------------------------+
//  | View random album                                                      |
//  +------------------------------------------------------------------------+
function viewRandomAlbum() {
	global $cfg, $db;
	global $base_size, $spaces, $scroll_bar_correction, $tileSizePHP;
	
	authenticate('access_media');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'Random';
	
	require_once('include/header.inc.php');
	
	$base		= (cookie('netjukebox_width') - 20) / ($base_size + 10);
	$colombs	= floor($base);
	$aval_width = (cookie('netjukebox_width') - 20 - $scroll_bar_correction) - ($colombs - 1) * $spaces;
	$size = floor($aval_width / $colombs);

	
?>
<table cellspacing="0" cellpadding="0" style="width: 100%;">
<tr>
	<td>
<!--  -->
<table cellspacing="0" cellpadding="0" class="tab">
<tr>
	<td class="tab_on" onClick="location.href='index.php?action=viewRandomAlbum';">Album</td>
	<td class="tab_none tabspace"></td>
	<td class="tab_off" onClick="location.href='index.php?action=viewRandomTrack';">Track</td>
	<td class="tab_off" onClick="location.href='index.php?action=viewRandomFile';">File</td>
	<td class="tab_none tabspace"></td>
	<td class="tab_off" onClick="location.href='genre.php?action=blacklist';">Blacklist</td>
	<td class="tab_none">&nbsp;</td>
	
</tr>
</table>

<div class="albums_container">
<?php
	$blacklist = explode(',', $cfg['random_blacklist']);
	$filter = '';
	foreach ($blacklist as $bl){
		if ($filter == '') {
				$filter = "genre_id NOT LIKE '%;" . $bl . ";%'";
		}
		else {
			$filter = $filter . " AND genre_id NOT LIKE '%;" . $bl . ";%'";
		}
	}
	$query = mysqli_query($db, 'SELECT artist_alphabetic, album, genre_id, year, month, image_id, album_id
		FROM album
		WHERE ' . $filter . '
		ORDER BY RAND()
		LIMIT ' . (int) $colombs * 2);
	while ($album = mysqli_fetch_assoc($query)) {		
			if ($album) {
			if ($tileSizePHP) $size = $tileSizePHP;
			draw_tile($size,$album);
			}
		} 
?>
</div>

<!--  -->
	</td>
</tr>
</table>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | View random track                                                      |
//  +------------------------------------------------------------------------+
function viewRandomTrack() {
	global $cfg, $db;
	
	authenticate('access_media');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'Random';
	
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
	<td colspan="3"><?php if ($cfg['access_play']) 		echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;random=new\');" onMouseOver="return overlib(\'Play random tracks\');" onMouseOut="return nd();">Play random tracks from list below:</a>';
	elseif ($cfg['access_add'])		echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=&amp;random=new\');" onMouseOver="return overlib(\'Add random tracks\');" onMouseOut="return nd();">Add random tracks from list below:</a>';
	elseif ($cfg['access_stream'])	echo '<a href="stream.php?action=playlist&amp;random=new&amp;stream_id=' . $cfg['stream_id']  . '" onMouseOver="return overlib(\'Stream random tracks\');" onMouseOut="return nd();">Stream random tracks from list below:</a>'; ?></td>
	<td></td>
</tr>
-->
<tr class="even mouseover">
	<td></td>
	<td class="icon"><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;random=database\');" onMouseOver="return overlib(\'Play playlist\');" onMouseOut="return nd();"><i class="fa fa-play-circle-o fa-fw icon-small"></i></a>'; ?></td>
	<td class="icon"><?php if ($cfg['access_add']) echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;random=database\',evaluateAdd);" onMouseOver="return overlib(\'Add playlist\');" onMouseOut="return nd();"><i id="add_random" class="fa fa-plus-circle fa-fw icon-small"></i></a>'; ?></td>
	<td class="icon"><?php if ($cfg['access_stream']) echo '<a href="stream.php?action=playlist&amp;random=database&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream playlist\');" onMouseOut="return nd();"><i class="fa fa-rss fa-fw icon-small"></i></a>'; ?></td>
	<td></td>
	<td colspan="3"><?php if ($cfg['access_play']) 		echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;random=database\');" onMouseOver="return overlib(\'Play playlist\');" onMouseOut="return nd();">Play random list shown below:</a>';
	elseif ($cfg['access_add'])		echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=&amp;random=database\');" onMouseOver="return overlib(\'Add playlist\');" onMouseOut="return nd();">Add random list shown below:</a>';
	elseif ($cfg['access_stream'])	echo '<a href="stream.php?action=playlist&amp;random=database&amp;stream_id=' . $cfg['stream_id']  . '" onMouseOver="return overlib(\'Stream playlist\');" onMouseOut="return nd();">Stream random list shown below:</a>'; ?></td>
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
	$filter = '';
	foreach ($blacklist as $bl){
		if ($filter == '') {
				$filter = "genre_id NOT LIKE '%;" . $bl . ";%'";
		}
		else {
			$filter = $filter . " AND genre_id NOT LIKE '%;" . $bl . ";%'";
		}
	}
	
	$query = mysqli_query($db, 'SELECT track.artist, title, track_id
		FROM track, album
		WHERE (' . $filter . ') AND
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
	<td><?php if ($cfg['access_play']) 		echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['track_id'] . '\');" onMouseOver="return overlib(\'Insert and play track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
			elseif ($cfg['access_add'])		echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['track_id'] . '\');" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
			elseif ($cfg['access_stream'])	echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['track_id'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
			else 							echo html($track['title']); ?></td>
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




//  +------------------------------------------------------------------------+
//  | View random file                                                       |
//  +------------------------------------------------------------------------+
function viewRandomFile() {
	global $cfg, $db;
	
	authenticate('access_media');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'Random';
	
	if(!isset($_COOKIE['random_limit'])) {
		$limit = $cfg['play_queue_limit'];
	} else {
		$limit = $_COOKIE['random_limit'];
	}
	
	if(!isset($_COOKIE['random_dir'])) {
		$dir = $cfg['media_dir'];
	} else {
		$dir = str_replace('ompd_ampersand_ompd','&',$_COOKIE['random_dir']);
	}
	
	/* $selectedDir = isset($_GET['selectedDir']) ? str_replace('ompd_ampersand_ompd','&',$_GET['selectedDir']) : $dir;
	$selectedDir = str_replace("'","&apos;",$selectedDir);
	$selectedDir = str_replace('"',"&quot;",$selectedDir); */
	
	
	$selectedDir = isset($_GET['selectedDir']) ? myHTMLencode($_GET['selectedDir']) : myHTMLencode($dir);
	
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
	<td class="tab_off" onClick="location.href='index.php?action=viewRandomTrack';">Track</td>
	<td class="tab_on" onClick="location.href='index.php?action=viewRandomFile';">File</td>
	<td class="tab_none tabspace"></td>
	<td class="tab_off" onClick="location.href='genre.php?action=blacklist';">Blacklist</td>
	<td class="tab_none">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" class="tab_border">
<?php
	if ($cfg['access_play'] || $cfg['access_add'] || $cfg['access_stream']) { ?>
<tr>
	<td>&nbsp;</td>
	<td></td>
	<td>&nbsp;</td>
	<td></td>
</tr>
<tr>
	<td></td>
	<td style="max-width: 4em;">Select directory:</td>
	<td></td>
	<td>
	<div class="buttons">
	<input id="randomDir" value="<?php 
		/* if ($selectedDir != '') {
			
			echo str_replace('ompd_ampersand_ompd','&',$selectedDir) . '/';
		}
		else {
			echo $cfg['media_dir'];
		} */
		echo $selectedDir;
	 ?>">
	<span id="randomBrowse"><i class="fa fa-folder-open-o fa-fw"></i> Browse...</span>
	</div>
	</td>
</tr>
<tr>
	<td></td>
	<td>Limit to:</td>
	<td></td>
	<td><input id="randomLimit" value="<?php echo $limit; ?>" style="max-width: 3em;"> tracks</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td></td>
	<td></td>
	<td></td>
</tr>
<?php 
	}
?>
</table>
<br>
<div class="buttons">
	<span id="playRandomFile" onmouseover="return overlib('Create playlist and play it');" onmouseout="return nd();">&nbsp;<i class="fa fa-play-circle-o fa-fw"></i> Create random list and play</span>
</div>
<div id="errorMessage"></div>
</td>
</tr>
</table>
<?php
	
	require_once('include/footer.inc.php');
}





//  +------------------------------------------------------------------------+
//  | View year                                                              |
//  +------------------------------------------------------------------------+
function viewYear() {
	global $cfg, $db;
	
	authenticate('access_media');
	
	$sort = get('sort') == 'asc' ? 'asc' : 'desc';
	
	if ($sort == 'asc') {
		$order_query = 'ORDER BY year';
		$order_bitmap_year = '<span class="fa fa-sort-numeric-asc"></span>';
		$sort_year = 'desc';
	}
	else {
		// desc
		$order_query = 'ORDER BY year DESC';
		$order_bitmap_year = '<span class="fa fa-sort-numeric-desc"></span>';
		$sort_year = 'asc';
	}
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'Year';
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space left"></td>
	<td width="80px"><a <?php echo ($order_bitmap_year == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="index.php?action=viewYear&amp;sort=<?php echo $sort_year; ?>">Year&nbsp;<?php echo $order_bitmap_year; ?></a></td>	
	<td align="left" class="bar">Graph</td>
	<td align="center" width="130px">Disc counts&nbsp;</td>
</tr>

<?php
	$query = mysqli_query($db, 'SELECT COUNT(*) AS counter
		FROM album
		WHERE year
		GROUP BY year
		ORDER BY counter DESC');
	$album = mysqli_fetch_assoc($query);
	$max = $album['counter'];
	
	$query = mysqli_query($db, 'SELECT COUNT(discs) AS albums, SUM(discs) AS discs FROM album');
	$album = mysqli_fetch_assoc($query);
	$all = $album['albums'];
	
	$i=0;
	$query = mysqli_query($db, 'SELECT album,
		COUNT(*) AS counter
		FROM album
		WHERE year is null');
	$album = mysqli_fetch_assoc($query);
	$yearNULL = $album['counter'];
	if ($yearNULL > 0) {
?>
<tr class="<?php echo ($i++ & 1) ? 'year' : 'year'; ?> mouseover">
	<td></td>
	<td><a href="index.php?action=view2&amp;year=Unknown">Unknown</a></td>
	<td class="bar" style="cursor: pointer;" onClick="window.location.href='<?php echo NJB_HOME_URL ?>index.php?action=view2&amp;year=Unknown';"><div class="out"><div id="yNULL" style="width: 0px;" class="in"></div></div></td>
	<td align="center"><?php echo $album['counter']; ?> (<?php echo  round($album['counter'] / $all * 100, 1); ?>%)</td>
	
	
</tr>
<?php	
	}
	$i=1;
	$query = mysqli_query($db, 'SELECT year,
		COUNT(*) AS counter
		FROM album
		WHERE year
		GROUP BY year ' . $order_query);
	while ($max && $album = mysqli_fetch_assoc($query)) {
?>
<tr class="<?php echo ($i++ & 1) ? 'year' : 'year'; ?> mouseover">
	<td></td>
	<td><a href="index.php?action=view2&amp;year=<?php echo $album['year']; ?>"><?php echo $album['year']; ?></a></td>
	<td class="bar" style="cursor: pointer;" onClick="window.location.href='<?php echo NJB_HOME_URL ?>index.php?action=view2&amp;year=<?php echo $album['year']; ?>';"><div class="out"><div id="y<?php echo $album['year']; ?>" style="width: 0px;" class="in"></div></div></td>
	<td align="center"><?php echo $album['counter']; ?> (<?php echo  round($album['counter'] / $all * 100, 1); ?>%)</td>
	
	
</tr>
<?php
	}
	$query = mysqli_query($db, 'SELECT year,
		COUNT(*) AS counter
		FROM album
		WHERE year
		GROUP BY year ' . $order_query);
		
	echo '</table>' . "\n";
	echo '<script type="text/javascript">' . "\n";
	echo 'function setYearBar() {' . "\n";
	if ($yearNULL>0) {
	echo 'document.getElementById(\'yNULL\').style.width="' . round($yearNULL / $max * 200) . 'px";' . "\n";}
	while ($max && $album = mysqli_fetch_assoc($query)) {
	//echo floor($album['counter'] / $max_played['counter'] * 100) . ' * 1/100 * $(\'#bar-popularity-out\').width() + \'px\';' . "\n";
		
		echo 'document.getElementById(\'y' . $album['year'] .'\').style.width="' . round($album['counter'] / $max * 200) . 'px";' . "\n";
		//echo '$(\'#y'. $album['year'] .'\').transition({ width: \'' . round($album['counter'] / $max * 200) .  'px\', duration: 2000 });' . "\n";
		}
	echo '}' . "\n";
	echo 'window.onload = function () {' . "\n";
    echo 'setYearBar();' . "\n";
	echo '};' . "\n";
	echo '</script>' . "\n";
	
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | View genre                                                             |
//  +------------------------------------------------------------------------+
function viewGenre() {
	global $cfg, $db;
	
	authenticate('access_media');
	
	$sort = get('sort') == 'desc' ? 'desc' : 'asc';
	
	if ($sort == 'asc') {
		$order_query = 'ORDER BY genre.genre';
		$order_bitmap_genre = '<span class="fa fa-sort-alpha-asc"></span>';
		$sort_genre = 'desc';
	}
	else {
		// desc
		$order_query = 'ORDER BY genre.genre DESC';
		$order_bitmap_genre = '<span class="fa fa-sort-alpha-desc"></span>';
		$sort_genre = 'asc';
	}
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'Genre';
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space left"></td>
	<td style="width: 200px; max-width: 30%;"><a <?php echo ($order_bitmap_genre == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="index.php?action=viewGenre&amp;sort=<?php echo $sort_genre; ?>">Genre&nbsp;<?php echo $order_bitmap_genre; ?></a></td>	
	<td align="left" class="bar">Graph</td>
	<td align="center" width="130px">Disc counts&nbsp;</td>
	
</tr>

<?php
	$query = mysqli_query($db, "SELECT genre.genre, genre.genre_id, count(album.album_id) AS albums
		FROM genre
		INNER JOIN album ON album.genre_id LIKE CONCAT( '%;', genre.genre_id, ';%' )
		GROUP BY genre.genre
		ORDER BY albums DESC");
	$album = mysqli_fetch_assoc($query);
	$max = $album['albums'];
	
	$query = mysqli_query($db, 'SELECT COUNT(discs) AS albums, SUM(discs) AS discs FROM album');
	$album = mysqli_fetch_assoc($query);
	$all = $album['albums'];
	
	$i=0;
	$query = mysqli_query($db, "SELECT genre.genre, genre.genre_id as gid, count(album.album_id) AS albums
		FROM genre
		INNER JOIN album ON album.genre_id LIKE CONCAT( '%;', genre.genre_id, ';%' )
		WHERE genre.genre = 'unknown genre'
		GROUP BY genre.genre");
	$album = mysqli_fetch_assoc($query);
	$genreNULL = $album['albums'];
	if ($genreNULL > 0) {
?>
<tr class="<?php echo ($i++ & 1) ? 'year' : 'year'; ?> mouseover">
	<td></td>
	<td><a href="index.php?action=view2&order=artist&sort=asc&genre_id=<?php echo $album['gid'] ?>">Unknown genre</a></td>
	<td class="bar" style="cursor: pointer;" onClick="window.location.href='<?php echo NJB_HOME_URL ?>index.php?action=view2&order=artist&sort=asc&genre_id=<?php echo $album['gid'] ?>';"><div class="out"><div id="yNULL" style="width: 0px;" class="in"></div></div></td>
	<td align="center"><?php echo $album['albums']; ?> (<?php echo  round($album['albums'] / $all * 100, 1); ?>%)</td>
	
	
</tr>
<?php	
	}
	$i=1;
	$query = mysqli_query($db, "SELECT genre.genre, genre.genre_id as gid, count(album.album_id) AS albums
		FROM genre
		INNER JOIN album ON album.genre_id LIKE CONCAT( '%;', genre.genre_id, ';%' )
		WHERE genre.genre <> 'unknown genre'
		GROUP BY genre.genre " . $order_query);
	while ($max && $album = mysqli_fetch_assoc($query)) {
?>
<tr class="<?php echo ($i++ & 1) ? 'year' : 'year'; ?> mouseover">
	<td></td>
	<td><a href="index.php?action=view2&order=artist&sort=asc&genre_id=<?php echo $album['gid'] ?>"><?php echo $album['genre']; ?></a></td>
	<td class="bar" style="cursor: pointer;" onClick="window.location.href='<?php echo NJB_HOME_URL ?>index.php?action=view2&order=artist&sort=asc&genre_id=<?php echo $album['gid'] ?>';"><div class="out"><div id="y<?php echo $album['gid']; ?>" style="width: 0px;" class="in"></div></div></td>
	<td align="center"><?php echo $album['albums']; ?> (<?php echo  round($album['albums'] / $all * 100, 1); ?>%)</td>

	
</tr>
<?php
	}
	$query = mysqli_query($db, "SELECT genre.genre, genre.genre_id as gid, count(album.album_id) AS albums
		FROM genre
		INNER JOIN album ON album.genre_id LIKE CONCAT( '%;', genre.genre_id, ';%' )
		WHERE genre.genre <> 'unknown genre'
		GROUP BY genre.genre " . $order_query);
		
	echo '</table>' . "\n";
	echo '<script type="text/javascript">' . "\n";
	echo 'function setYearBar() {' . "\n";
	if ($genreNULL>0) {
	echo 'document.getElementById(\'yNULL\').style.width="' . round($genreNULL / $max * 200) . 'px";' . "\n";}
	while ($max && $album = mysqli_fetch_assoc($query)) {
	
		
		echo 'document.getElementById(\'y' . $album['gid'] .'\').style.width="' . round($album['albums'] / $max * 200) . 'px";' . "\n";
		
		}
	echo '}' . "\n";
	echo 'window.onload = function () {' . "\n";
    echo 'setYearBar();' . "\n";
	echo '};' . "\n";
	echo '</script>' . "\n";
	
	require_once('include/footer.inc.php');
}





//  +------------------------------------------------------------------------+
//  | View DR                                                                |
//  +------------------------------------------------------------------------+
function viewDR() {
	global $cfg, $db;
	
	authenticate('access_media');
	
	$sort = get('sort') == 'asc' ? 'asc' : 'desc';
	
	if ($sort == 'asc') {
		$order_query = 'ORDER BY album_dr';
		$order_bitmap_dr = '<span class="fa fa-sort-numeric-asc"></span>';
		$sort_dr = 'desc';
	}
	else {
		// desc
		$order_query = 'ORDER BY album_dr DESC';
		$order_bitmap_dr = '<span class="fa fa-sort-numeric-desc"></span>';
		$sort_dr = 'asc';
	}
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'Album Dynamic Range (DR)';
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space left"></td>
	<td width="80px"><a <?php echo ($order_bitmap_dr == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="index.php?action=viewDR&amp;sort=<?php echo $sort_dr; ?>">DR&nbsp;<?php echo $order_bitmap_dr; ?></a></td>	
	<td align="left" class="bar">Graph</td>
	<td align="center" width="130px">Disc counts&nbsp;</td>
	
</tr>

<?php
	$query = mysqli_query($db, 'SELECT COUNT(*) AS counter
		FROM album
		WHERE album_dr
		GROUP BY album_dr
		ORDER BY counter DESC');
	$album = mysqli_fetch_assoc($query);
	$max = $album['counter'];
	
	$query = mysqli_query($db, 'SELECT COUNT(discs) AS albums, SUM(discs) AS discs FROM album');
	$album = mysqli_fetch_assoc($query);
	$all = $album['albums'];
	
	$i=0;
	$query = mysqli_query($db, 'SELECT album,
		COUNT(*) AS counter
		FROM album
		WHERE album_dr is null');
	$album = mysqli_fetch_assoc($query);
	$drNULL = $album['counter'];
	if ($drNULL > 0) {
?>
<tr class="<?php echo ($i++ & 1) ? 'year' : 'year'; ?> mouseover">
	<td></td>
	<td><a href="index.php?action=view2&amp;dr=Unknown">Unknown</a></td>
	<td class="bar" style="cursor: pointer;" onClick="window.location.href='<?php echo NJB_HOME_URL ?>index.php?action=view2&amp;dr=Unknown';"><div class="out"><div id="drNULL" style="width: 0px;" class="in"></div></div></td>
	<td align="center"><?php echo $album['counter']; ?> (<?php echo  round($album['counter'] / $all * 100, 1); ?>%)</td>
	
	
</tr>
<?php	
	}
	$i=1;
	$query = mysqli_query($db, 'SELECT album_dr,
		COUNT(*) AS counter
		FROM album
		WHERE album_dr
		GROUP BY album_dr ' . $order_query);
	while ($max && $album = mysqli_fetch_assoc($query)) {
?>
<tr class="<?php echo ($i++ & 1) ? 'year' : 'year'; ?> mouseover">
	<td></td>
	<td><a href="index.php?action=view2&amp;dr=<?php echo $album['album_dr']; ?>"><?php echo $album['album_dr']; ?></a></td>
	<td class="bar" style="cursor: pointer;" onClick="window.location.href='<?php echo NJB_HOME_URL ?>index.php?action=view2&amp;dr=<?php echo $album['album_dr']; ?>';"><div class="out"><div id="dr<?php echo $album['album_dr']; ?>" style="width: 0px;" class="in"></div></div></td>
	<td align="center"><?php echo $album['counter']; ?> (<?php echo  round($album['counter'] / $all * 100, 1); ?>%)</td>
	
	
</tr>
<?php
	}
	$query = mysqli_query($db, 'SELECT album_dr,
		COUNT(*) AS counter
		FROM album
		WHERE album_dr
		GROUP BY album_dr ' . $order_query);
		
	echo '</table>' . "\n";
	echo '<script type="text/javascript">' . "\n";
	echo 'function setYearBar() {' . "\n";
	if ($drNULL>0) {
	echo 'document.getElementById(\'drNULL\').style.width="' . round($drNULL / $max * 200) . 'px";' . "\n";}
	while ($max && $album = mysqli_fetch_assoc($query)) {
	//echo floor($album['counter'] / $max_played['counter'] * 100) . ' * 1/100 * $(\'#bar-popularity-out\').width() + \'px\';' . "\n";
		
		echo 'document.getElementById(\'dr' . $album['album_dr'] .'\').style.width="' . round($album['counter'] / $max * 200) . 'px";' . "\n";
		//echo '$(\'#y'. $album['year'] .'\').transition({ width: \'' . round($album['counter'] / $max * 200) .  'px\', duration: 2000 });' . "\n";
		}
	echo '}' . "\n";
	echo 'window.onload = function () {' . "\n";
    echo 'setYearBar();' . "\n";
	echo '};' . "\n";
	echo '</script>' . "\n";
	
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | View composer                                                          |
//  +------------------------------------------------------------------------+
function viewComposer() {
	global $cfg, $db, $nav;
	authenticate('access_media');
	
	$composer	 	= get('composer');
	$filter  	= get('filter');
	require_once('include/header.inc.php');
	

	$query = '';
	if ($filter == 'all')			$query = mysqli_query($db, 'SELECT composer FROM track WHERE composer !="" GROUP BY composer ORDER BY composer');
	elseif ($filter == 'exact')		$query = mysqli_query($db, 'SELECT composer FROM track WHERE composer = "' .  mysqli_real_escape_string($db,$composer) . '" OR composer = "' .  mysqli_real_escape_string($db,$composer) . '" GROUP BY composer ORDER BY composer');
	elseif ($filter == 'smart')		$query = mysqli_query($db, 'SELECT composer FROM track WHERE composer LIKE "%' . mysqli_real_escape_like($composer) . '%" OR composer LIKE "%' . mysqli_real_escape_like($composer) . '%" OR composer SOUNDS LIKE "' .  mysqli_real_escape_string($db,$composer) . '" GROUP BY composer ORDER BY composer');
	elseif ($filter == 'start')		$query = mysqli_query($db, 'SELECT composer FROM track WHERE composer LIKE "' . mysqli_real_escape_like($composer) . '%" GROUP BY composer ORDER BY composer');
	elseif ($filter == 'symbol')	$query = mysqli_query($db, 'SELECT composer FROM track WHERE composer REGEXP "^[^a-z]" GROUP BY composer ORDER BY composer');
	else							message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]filter');
	
	if (mysqli_num_rows($query) == 1) {
		$album = mysqli_fetch_assoc($query);
		$_GET['composer'] = $album['artist'];
		$_GET['filter'] = 'exact';
		view2();
		exit();
	}
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	if ($filter == 'all') $nav['name'][] = 'All composers';
	elseif ($filter == 'symbol') $nav['name'][] = 'Composer: #';
	elseif ($composer != '') $nav['name'][] = 'composer: ' . $composer;
	
	
	$list_url		= 'index.php?action=view2&amp;thumbnail=0&amp;artist=' . rawurlencode($composer) . '&amp;filter=' . $filter . '&amp;order=artist';
	$thumbnail_url	= 'index.php?action=view2&amp;thumbnail=1&amp;artist=' . rawurlencode($composer) . '&amp;filter=' . $filter . '&amp;order=artist';


	if (count($nav['name']) == 1 )	echo '<span class="nav_home"></span>' . "\n";
	else {
	echo '<span class="nav_tree">' . "\n";
	for ($i=0; $i < count($nav['name']); $i++) {
		if ($i > 0)	echo '<span class="nav_seperation">></span>' . "\n";
		if (empty($nav['url'][$i]) == false) echo '<a href="' . $nav['url'][$i] . '">' . html($nav['name'][$i]) . '</a>' . "\n";
		else echo html($nav['name'][$i]) . "\n";
	}
	echo '</span>' . "\n";
	}
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space left"></td>
	<td>Composer</td>
	<td align="right" class="right">
	<?php 
	$c = mysqli_num_rows($query); 
	$a = ($c > 1) ? 'composers' : 'composer';
	echo ('(' . $c . ' ' . $a . ' found)'); 
	?> 
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
	$composer = '';
	if ($cfg['testing'] == 'on' && !in_array(', ',$cfg['artist_separator'])) {
		$cfg['artist_separator'][] = ', ';
	}
	$exploded = multiexplode($cfg['artist_separator'],$album['composer']);
		$l = count($exploded);
		if ($l > 1) {
			for ($j=0; $j<$l; $j++) {
				$composer = $composer . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($exploded[$j]) . '">' . html($exploded[$j]) . '</a>';
				if ($j != $l - 1){
					$delimiter = getInbetweenStrings($exploded[$j],$exploded[$j + 1], $album['composer']);
					$composer = $composer . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($album['composer']) . '&amp;order=year"><span class="artist_all">' . $delimiter[0] . '</span></a>';
				}
			}
			echo $composer;
		}
		else {
			echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($album['composer']) . '&amp;order=year">' . html($album['composer']) . '</a>';
		}
	?>
	
	
	</td>
	<td></td>
</tr>
<?php
	}
	echo '</table>' . "\n";
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | View new on start page                                                 |
//  +------------------------------------------------------------------------+
function viewNewStartPage() {
	global $cfg, $db;
	global $base_size, $spaces, $scroll_bar_correction, $tileSizePHP;
	
	//authenticate('access_media');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'New';
	
	require_once('include/header.inc.php');
	
	$base		= (cookie('netjukebox_width') - 20) / ($base_size + 10);
	$colombs	= floor($base);
	$aval_width = (cookie('netjukebox_width') - 20 - $scroll_bar_correction) - ($colombs - 1) * $spaces;
	$size = floor($aval_width / $colombs);

	$i			= 0;
	$query = mysqli_query($db, 'SELECT SUM(discs) AS discs FROM album');
	$album = mysqli_fetch_assoc($query);
	
	$query = mysqli_query($db, 'SELECT genre_id FROM album WHERE genre_id LIKE "%;%"');
	
	
	if ($album['discs'] >= 1) {
		if (mysqli_num_rows($query) == 0) {
			?>
			<script type="text/javascript">
			hideSpinner();
			</script>
			<h1 id="dbUpdate">
			Please wait, preparing database for use with multi-genre.<br>
			This can take a while.
			</h1>
			<?php 
			@ob_flush();
			flush();
			updateGenre();	
			?>
			<script type="text/javascript">
			$('#dbUpdate').hide();
			</script>
			<?php 
			@ob_flush();
	flush();
		}
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
		$('#suggested_container [id^="add_"]').click(function(){
			$(this).removeClass('fa-plus-circle').addClass('fa-cog fa-spin icon-selected');
		});
		
		$('#suggested_container [id^="play_"]').click(function(){
			$(this).removeClass('fa-play-circle-o').addClass('fa-cog fa-spin icon-selected');
		});
		
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
		(SELECT album_id, MAX(time) AS m_time FROM counter GROUP BY album_id) as c
		ON c.album_id = album.album_id
		ORDER BY c.m_time DESC, album.album DESC
		LIMIT 10
		' );
	$rows = mysqli_num_rows($query);

	if ($rows > 0) {
	?>

	<h1>&nbsp;Recently played albums <a href="index.php?action=viewRecentlyPlayed">(more...)</a></h1>
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
	$query = mysqli_query($db, 'SELECT album, album_add_time, album_id, image_id, artist, artist_alphabetic
			FROM album
			WHERE album_add_time
			ORDER BY album
			');
	
	if ($tileSizePHP) $size = $tileSizePHP;
	$album_multidisc = albumMultidisc($query);
	krsort($album_multidisc);
	foreach (array_slice($album_multidisc,0,$cfg['max_items_per_page']) as $album_m) {
		draw_tile($size,$album_m,$album_m['allDiscs']);
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




//  +------------------------------------------------------------------------+
//  | View new                                                               |
//  +------------------------------------------------------------------------+
function viewNew() {
	global $cfg, $db;
	global $base_size, $spaces, $scroll_bar_correction;
	
	authenticate('access_media');
	
	$addedOn = get('addedOn');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'New ' . (isset($addedOn) ? ' from ' . $addedOn : '');
	require_once('include/header.inc.php');

	$i = 0;
	$tsStart = get('tsStart');
	$tsEnd = get('tsEnd');
	$page = (get('page') ? get('page') : 1);
	$max_item_per_page = $cfg['max_items_per_page'];
	$where = 'album_add_time';
	if (isSet($tsStart) && isSet($tsEnd)) {
		$where = 'album_add_time <= ' . $tsEnd . ' AND album_add_time >= ' . $tsStart;
	}
	 
	 $query = mysqli_query($db, 'SELECT album, album_add_time, album_id, image_id, artist, artist_alphabetic
		FROM album
		WHERE ' . $where . '
		ORDER BY album
		');
	
		$album_multidisc = albumMultidisc($query);
?>


<h1>
new albums <?php if (isset($addedOn)) echo ' added on ' . $addedOn; ?>
</h1>


<div class="albums_container">
<?php
	
	if ($tileSizePHP) $size = $tileSizePHP;
	krsort($album_multidisc);
	foreach (array_slice($album_multidisc,($page - 1) * $max_item_per_page,$max_item_per_page) as $album_m) {
		draw_tile($size,$album_m,'allDiscs');
		
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




//  +------------------------------------------------------------------------+
//  | View popular                                                           |
//  +------------------------------------------------------------------------+
function viewPopular() {
	global $cfg, $db;
	
	
	$period		= get('period');
	$user_id 	= (int) get('user_id');
	$flag	 	= (int) get('flag');
	
	if		($period == 'week')		$days = 7;
	elseif	($period == 'month')	$days = 31;
	elseif	($period == 'year')		$days = 365;
	elseif	($period == 'overall')	$days = 365 * 1000;
	elseif	($period == 'artist')	$days = 0;
	else							message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]period');
	
	if ($user_id == 0) {
		authenticate('access_popular');
		
		// formattedNavigator
		$nav			= array();
		$nav['name'][]	= 'Library';
		$nav['url'][]	= 'index.php';
		$nav['name'][]	= 'Popular';
		if ($period == 'artist') {
			$va = '';
			$vaArray = $cfg['VA'];
			array_walk($vaArray, function(&$value, &$key){
				$value = str_replace($value,"'" . $value . "'", $value);
			});
			$query_pop = mysqli_query($db,'SELECT album.artist, artist_alphabetic, COUNT(album.artist) as counter 
				FROM album 
				LEFT JOIN counter ON album.album_id = counter.album_id
				WHERE album.artist NOT IN (' . implode(',',$vaArray) . ')
				GROUP BY album.artist
				ORDER BY counter DESC, album.artist
				LIMIT 100');
		}
		else {
			$query_pop = mysqli_query($db, 'SELECT artist, artist_alphabetic, album, image_id, album.album_id, COUNT(*) AS counter
				FROM counter, album
				WHERE counter.flag <= 1
				AND counter.time > ' . (int) (time() - 86400 * $days) . '
				AND counter.album_id = album.album_id
				GROUP BY album.album_id
				ORDER BY counter DESC, time DESC
				LIMIT 50');
		}
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
		$nav			= array();
		$nav['name'][]	= 'Configuration';
		$nav['url'][]	= 'config.php';
		$nav['name'][]	= 'User statistics';
		$nav['url'][]	= 'users.php?action=userStatistics&amp;period=' . $period;
		if		($flag == 0) $nav['name'][] = 'Play: ' . $user['username'];
		elseif	($flag == 1) $nav['name'][] = 'Stream: ' . $user['username'];
		elseif	($flag == 2) $nav['name'][] = 'Download: ' . $user['username'];
		elseif	($flag == 3) $nav['name'][] = 'Cover: ' . $user['username'];
		elseif	($flag == 4) $nav['name'][] = 'Record: ' . $user['username'];
		else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]flag');
		
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
	
	<td class="<?php echo ($period == 'artist') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='<?php echo $url; ?>&amp;period=artist';">Artist</td>
	<td class="tab_none tabspace"></td>
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
	<td class="trackNumber">#</td>
	<td>Artist</td>
	<td><?php echo($period == "artist" ? "" : "Album"); ?></td>
	<td colspan="2"><?php echo($period == "artist" ? "Total albums played" : "Count"); ?></td>
</tr>

<?php
	
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
	<td class="trackNumber"><?php echo $i; ?></td>
	<td><a href="index.php?action=view2&amp;artist=<?php echo rawurlencode($album['artist_alphabetic']); ?>&amp;order=year"><?php echo html($album['artist']); ?></a></td>
	<td><a href="index.php?action=view3&amp;album_id=<?php echo $album['album_id']; ?>" <?php echo onmouseoverImage($album['image_id']); ?>><?php echo html($album['album']); ?></a></td>
	<td class="counter"><?php echo $album['counter']; ?> &nbsp;</td>
	<td class="bar" onMouseOver="return overlib('<?php echo $album['counter']; ?>');" onMouseOut="return nd();">
	<div class="out-popular"><div style="width: <?php echo  round($album['counter'] / $max * 100); ?>px;" class="in"></div></div>
	</td>
	</tr>
<tr class="line">
	<td></td>
	<td colspan="16"></td>
</tr>
<tr>
<td colspan="16">
 <?php 
	if ($period == 'artist') {
		?>
		<div class="menuSub menuSubLeft" id="menu-sub-track<?php echo $i ?>" onclick='//offMenuSub(<?php echo $i ?>);'>
		<?php
		$query_album = mysqli_query($db, "SELECT album.artist, artist_alphabetic, album.album, album.album_id, album.image_id, COUNT(*) as counter 
			FROM album 
			LEFT JOIN counter ON album.album_id = counter.album_id
			WHERE album.artist = '" . $album['artist'] . "'
			GROUP BY album.album
			ORDER BY counter DESC, album.album");
	?>
	<table cellspacing="0" cellpadding="0">
	<tr class="tab_header">
		<td class="space left"></td>
		<td>Album</td>
		<td></td>
		<td>Count</td>
	</tr>
	<?php
		while ($albums = mysqli_fetch_assoc($query_album)) {
			echo '<tr class="album_list">';
			echo '<td></td>';
			echo '<td class="small_cover_sub"><a href="index.php?action=view3&amp;album_id=' . $albums['album_id'] .'"><img class="small_cover_sub" src="image.php?image_id=' . $albums['image_id'] . '"></a></td>';
			echo '<td><a href="index.php?action=view3&amp;album_id=' . $albums['album_id'] .'">' . html($albums['album']) . '</a></td>';
			echo '<td>' . $albums['counter'] . '</td>';
			echo '</tr>';
		}
	?>
	</table>
	<?php
	}
	else { ?>
		<div class="menuSub" id="menu-sub-track<?php echo $i ?>" onclick='//offMenuSub(<?php echo $i ?>);'>

		<div><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;album_id=' . $album['album_id'] . '\',evaluateAdd);"><i id="play_' . $album['album_id'] . '" class="fa fa-play-circle-o fa-fw icon-small"></i>Play album</a>'; ?>
		</div>
		
		<div>
		<?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&album_id=' . $album['album_id'] . '\',evaluateAdd);ajaxRequest(\'play.php?action=updateAddPlay&album_id=' . $album['album_id'] . '\',updateAddPlay);"><i id="add_' . $album['album_id'] . '" class="fa fa-plus-circle fa-fw icon-small"></i>Add to playlist</a>';?>
		</div>
		
		<div onClick="offMenuSub('');">
		<?php if ($cfg['access_add'])  echo '<a href="stream.php?action=playlist&amp;album_id=' . $album['album_id'] . '&amp;stream_id=' . $cfg['stream_id'] . '"><i class="fa fa-rss fa-fw icon-small"></i>Stream album</a>';?>
		</div>
	
	<?php 
	}
	?>
	
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




//  +------------------------------------------------------------------------+
//  | View recently played                                                   |
//  +------------------------------------------------------------------------+
function viewRecentlyPlayed() {
	global $cfg, $db;
	global $base_size, $spaces, $scroll_bar_correction;
	
	authenticate('access_media');
	$type = (get('type') ? get('type') : '');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	if ($type=='day') {
		$nav['name'][]	= 'Recently played albums - day by day';
	}
	else {
		$nav['name'][]	= 'Recently played albums';
	}
	require_once('include/header.inc.php');

	$i = 0;
	$tsStart = get('tsStart');
	$tsEnd = get('tsEnd');
	if (isSet($tsStart) && isSet($tsEnd)) {
		$where = 'album_add_time <= ' . $tsEnd . ' AND album_add_time >= ' . $tsStart;
	}
	$page = (get('page') ? get('page') : 1);
	$max_item_per_page = $cfg['max_items_per_page'];
	
	if ($type == 'day') {
	$query_rp = mysqli_query($db, '
		SELECT a.album_id, a.image_id, a.album, a.artist_alphabetic, counter.time as played_time
		FROM counter JOIN (SELECT album_id, image_id, album, artist_alphabetic FROM album) as a on a.album_id = counter.album_id ORDER BY played_time DESC
		' );
	}
	else {
	$query_rp = mysqli_query($db, '
		SELECT DISTINCT album.album_id, album.image_id, album.album, album.artist_alphabetic, c.m_time as played_time
		FROM album RIGHT JOIN 
		(SELECT album_id, MAX(time) AS m_time FROM counter GROUP BY album_id) as c
		ON c.album_id = album.album_id
		ORDER BY c.m_time DESC
		' );
	}
		$album_multidisc = albumMultidisc($query_rp, 'rp');
?>


<h1>
<a href="index.php?action=viewRecentlyPlayed" <?php if($type=='') echo 'class="sort_selected"' ?>>Recently played albums</a>&nbsp;&nbsp;<a href="index.php?action=viewRecentlyPlayed&type=day" <?php if($type=='day') echo ' class="sort_selected"' ?>>Day by day</a>
</h1>


<div class="albums_container">
<?php
	
	if ($tileSizePHP) $size = $tileSizePHP;
	$prevDate = '';
	$currDate = '';
	foreach (array_slice($album_multidisc,($page - 1) * $max_item_per_page,$max_item_per_page) as $album_m) {
		$ts = $album_m['played_time'];
		$currDate = date('Y.m.d', $ts);
		//$start = mktime(0,0,0,$m,$d,$y);
		if ($currDate <> $prevDate && $type == 'day') {
			echo '<div class="decade">' . $currDate . ' - ' . date("l", ($ts)) . '</div>';
		}
		draw_tile($size,$album_m,'allDiscs');
		$prevDate = $currDate;
	}
?>
</div>

<table cellspacing="0" cellpadding="0" class="border">

<tr class="<?php echo $class; ?> smallspace"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr class="line"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<?php
	//$query = mysqli_query($db, 'SELECT artist FROM album ' . $filter_query . ' GROUP BY artist');
	if (mysqli_num_rows($query_rp) < 2) {
		$album = mysqli_fetch_assoc($query_rp);
		if ($album['artist'] == '') $album['artist'] = $artist;
		$query_rp = mysqli_query($db, 'SELECT album_id from track where artist = "' .  mysqli_real_escape_string($db,$album['artist']) . '"');
		$tracks = mysqli_num_rows($query_rp);
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




//  +------------------------------------------------------------------------+
//  | View played at given day                                               |
//  +------------------------------------------------------------------------+
function viewPlayedAtDay() {
	global $cfg, $db;
	global $base_size, $spaces, $scroll_bar_correction;
	
	authenticate('access_media');
	$type = (get('type') ? get('type') : '');
	$day = (get('day') ? get('day') : '');
	$ts = strtotime($day);
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'Albums played at ' . $day;
	require_once('include/header.inc.php');
	
	$beginOfDay = strtotime("midnight", $ts);
	$endOfDay   = strtotime("tomorrow", $beginOfDay) - 1;
	$i = 0;
	$page = (get('page') ? get('page') : 1);
	$max_item_per_page = $cfg['max_items_per_page'];
	
	
	$query_rp = mysqli_query($db, '
		SELECT a.album_id, a.image_id, a.album, a.artist_alphabetic, counter.time as played_time
		FROM counter JOIN (SELECT album_id, image_id, album, artist_alphabetic FROM album) as a on a.album_id = counter.album_id WHERE counter.time > ' . $beginOfDay . ' AND counter.time < ' . $endOfDay . ' ORDER BY played_time DESC
		' );
	
	$album_multidisc = albumMultidisc($query_rp, 'rp');
?>



<div class="albums_container">
<?php
	
	if ($tileSizePHP) $size = $tileSizePHP;
	$prevDate = '';
	$currDate = '';
	foreach (array_slice($album_multidisc,($page - 1) * $max_item_per_page,$max_item_per_page) as $album_m) {
		draw_tile($size,$album_m,'allDiscs');
	}
?>
</div>

<?php
	
	require_once('include/footer.inc.php');
}




?>