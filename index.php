<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2021 Artur Sierzant                            |
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


if (cookie('netjukebox_width')<385) {$base_size = 90;}
elseif (cookie('netjukebox_width')<641) {$base_size = 120;}
else {$base_size = 150;}


$cfg['menu']	= 'Library';
$action 		= get('action');
$service 		= get('service');
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
elseif	($action == 'viewHRA')			viewHRA();
elseif	($action == 'viewNewFromHRA')			viewNewFromHRA();
elseif	($action == 'viewTidal')			viewTidal();
elseif	($action == 'viewNewFromTidal')			viewNewFromTidal();
elseif	($action == 'viewMoreFromTidal')			viewMoreFromTidal();
elseif	($action == 'viewTidalPlaylist')			viewTidalPlaylist();
elseif	($action == 'viewTidalMixlist')			viewTidalMixlist();
elseif	($action == 'viewPopular')		viewPopular();
elseif	($action == 'viewRecentlyPlayed')	viewRecentlyPlayed();
elseif	($action == 'viewPlayedAtDay')	viewPlayedAtDay();
elseif	($action == 'viewTidalAlbums')	viewTidalAlbums();
elseif	($action == 'viewAlbumsFromStreamingService')	viewAlbumsFromStreamingService($service);
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
	require_once("index-view2.php");
}

//  +------------------------------------------------------------------------+
//  | View 3                                                                 |
//  +------------------------------------------------------------------------+

function view3() {
	require_once("index-view3.php");
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
		LIMIT ' . (int) $cfg['max_items_per_page']/3);
		//LIMIT ' . (int) $colombs * 2);
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
	<td align="center" width="130px">Discs count&nbsp;</td>
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
	<td align="center" width="130px">Discs count&nbsp;</td>
	
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
<tr class="line">
	<td></td>
	<td colspan="16"></td>
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
<tr class="line">
	<td></td>
	<td colspan="16"></td>
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
	<td align="center" width="130px">Discs count&nbsp;</td>
	
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
			$( "#suggested_full" ).html( data );	
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
		$('#suggested_full [id^="add_"]').click(function(){
			$(this).removeClass('fa-plus-circle').addClass('fa-cog fa-spin icon-selected');
		});
		
		$('#suggested_full [id^="play_"]').click(function(){
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
		SELECT * FROM
		(SELECT album_id, time FROM counter
		ORDER BY time DESC
		LIMIT 10) c
		GROUP BY c.album_id
		ORDER BY c.time DESC' );
	
	$rows = mysqli_num_rows($query);

	if ($rows > 0) {
		if ($tileSizePHP) $size = $tileSizePHP;
	?>

	<h1>&nbsp;Recently played albums <a href="index.php?action=viewRecentlyPlayed">(more...)</a></h1>
	<script>
		calcTileSize();
		var size = $tileSize;
		var request = $.ajax({  
		url: "ajax-last-played.php",  
		type: "POST",
		data: { tileSize : size,
            user_id : <?php echo $cfg['user_id']; ?>},
		dataType: "html"
		}); 

	request.done(function(data) {
		if (data) {
			$( "#recently_played" ).html(data);
		}
		else {
			$( "#recently_played" ).html('<div style="line-height: initial;">Error loading albums.</div>');
		}
		//calcTileSize();
	});
	
	</script>
	<div class="full" id="recently_played">
		<div style="display: grid; height: 100%;">
			<span id="albumsLoadingIndicator" style="margin: auto;">
				<i class="fa fa-cog fa-spin icon-small"></i> <span class="add-info-left">Loading recently played albums...</span>
			</span>
		</div>
	</div>
	<?php 
	}
} //recently_played

if ($cfg['use_tidal']) {
  include 'include/new_albums_tidal.inc.php';
} //new albums from Tidal

if ($cfg['use_hra']) {
  include 'include/new_albums_hra.inc.php';
} //new albums from HRA

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
		//draw_tile($size,$album_m,'allDiscs');
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
  if ($cfg['use_tidal']) {
    include 'include/new_albums_tidal.inc.php';
  } //new from Tidal

  if ($cfg['use_hra']) {
    include 'include/new_albums_hra.inc.php';
  } //new from HRA
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
//  | Tidal                                                                  |
//  +------------------------------------------------------------------------+

function viewTidal() {
	require_once("index-tidal.php");
}




//  +------------------------------------------------------------------------+
//  | View new from Tidal                                                    |
//  +------------------------------------------------------------------------+
function viewNewFromTidal() {
	global $cfg, $db, $t;
	global $base_size, $spaces, $scroll_bar_correction;
	
  $type = get('type');
	
  authenticate('access_media');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
  $nav['name'][]	= 'Tidal';
	$nav['url'][]	= 'index.php?action=viewTidal';
  switch ($type) {
    case "suggested_new":
      $nav['name'][] = 'Suggested new albums:';
      require_once('include/header.inc.php');
      echo ('<h1>Suggested new albums</h1>');
      break;
    case "featured_new":
      $nav['name'][] = 'Featured new albums:';
      require_once('include/header.inc.php');
      echo ('<h1>Featured new albums</h1>');
      break;
    case "featured_recommended":
      $nav['name'][] = 'Featured recommended albums:';
      require_once('include/header.inc.php');
      echo ('<h1>Featured recommended albums</h1>');
      break;
    case "featured_local":
      $nav['name'][] = 'Featured local albums:';
      require_once('include/header.inc.php');
      echo ('<h1>Featured local albums</h1>');
      break;
    case "featured_top":
      $nav['name'][] = 'Featured top albums:';
      require_once('include/header.inc.php');
      echo ('<h1>Featured top albums</h1>');
      break;
    case "new_for_you":
      $nav['name'][] = 'New albums for you:';
      require_once('include/header.inc.php');
      echo ('<h1>New albums for you</h1>');
      break;
    case "suggested_for_you":
      $nav['name'][] = 'Suggested albums for you:';
      require_once('include/header.inc.php');
      echo ('<h1>Suggested albums for you</h1>');
      break;
    case "suggested_new_tracks":
      $nav['name'][] = 'Suggested new tracks:';
      require_once('include/header.inc.php');
      echo ('<h1>Suggested new tracks</h1>');
      break;
  }
?>

<?php
if ($tileSizePHP) $size = $tileSizePHP;
/* $t = new TidalAPI;
$t->username = $cfg["tidal_username"];
$t->password = $cfg["tidal_password"];
$t->token = $cfg["tidal_token"];
$t->audioQuality = $cfg["tidal_audio_quality"];
$t->fixSSLcertificate(); */
//$t = tidal();
$conn = $t->connect();
if ($conn === true){
  $mediaType = 'album';
  $curr_page = (get('page') ? get('page') : 1);
  //50 is Tidal limitation for getting data by dataApiPath
  $tidalMaxItemPerPage = $cfg['max_items_per_page'];
  if ($tidalMaxItemPerPage > 50) $tidalMaxItemPerPage = 50;
  switch ($type){
    case "suggested_new":
      $results = $t->getSuggestedNew($tidalMaxItemPerPage, $tidalMaxItemPerPage * ($curr_page - 1), true);
      break;
    case "featured_new":
      $results = $t->getFeatured($cfg['max_items_per_page'], $cfg['max_items_per_page'] * ($curr_page - 1));
      break;
    case "featured_recommended":
      $results = $t->getFeaturedRecommended($cfg['max_items_per_page'], $cfg['max_items_per_page'] * ($curr_page - 1));
      break;
    case "featured_local":
      $results = $t->getFeaturedLocal($cfg['max_items_per_page'], $cfg['max_items_per_page'] * ($curr_page - 1));
      break;
    case "featured_top":
      $results = $t->getFeaturedTop($cfg['max_items_per_page'], $cfg['max_items_per_page'] * ($curr_page - 1));
      break;
    case "new_for_you":
      $results = $t->getNewForYou($tidalMaxItemPerPage, $tidalMaxItemPerPage * ($curr_page - 1), true);
      break;
    case "suggested_for_you":
      $results = $t->getSuggestedForYou($tidalMaxItemPerPage, $tidalMaxItemPerPage * ($curr_page - 1), true);
      break;
    case "suggested_new_tracks":
      $results = $t->getSuggestedNewTracks($tidalMaxItemPerPage, $tidalMaxItemPerPage * ($curr_page - 1), true);
      $mediaType = 'track';
      break;
  }
  if ($results['items']){
    if ($mediaType == 'album'){
      echo '<div class="albums_container">';
      foreach($results['items'] as $res) {
        $albums = array();
        $albums['album_id'] = 'tidal_' . $res['id'];
        $albums['album'] = $res['title'];
        $albums['cover'] = $t->albumCoverToURL($res['cover'],'lq');
        $albums['artist_alphabetic'] = $res['artists'][0]['name'];
        if ($cfg['show_album_format']) {
          $albums['audio_quality'] = $res['audioQuality'];
        }
        draw_tile ( $size, $albums, '', 'echo', '' );
      }
      $cfg['items_count'] = $results['totalNumberOfItems'];
    }
    elseif ($mediaType == 'track') {
      $tracksList = tidalTracksList($results);
      echo '<div>';
      echo $tracksList;
    }
  }
  else {
    echo ('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on TIDAL.</span>');
  }
}
else {
  echo('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution Tidal request.<br>Error message:<br><br>' . $conn["error"] . '</div>');
}
?>
</div>

<table cellspacing="0" cellpadding="0" class="border">

<tr class="<?php echo $class; ?> smallspace"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr class="line"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>

</table>

<?php
	require_once('include/footer.inc.php');
}


//  +------------------------------------------------------------------------+
//  | View more items from Tidal                                             |
//  +------------------------------------------------------------------------+
function viewMoreFromTidal() {
  global $cfg, $db, $t;
  global $base_size, $spaces, $scroll_bar_correction;

  $type = get('type');
  $apiPath = get('apiPath');

  authenticate('access_media');

  // formattedNavigator
  $nav			= array();
  $nav['name'][]	= 'Library';
  $nav['url'][]	= 'index.php';
  $nav['name'][]	= 'Tidal';
  $nav['url'][]	= 'index.php?action=viewTidal';

$conn = $t->connect();
if ($conn === true){
  //$mediaType = 'album';
  $curr_page = (get('page') ? get('page') : 1);
  //50 is Tidal limitation for getting data by dataApiPath
  $tidalMaxItemPerPage = $cfg['max_items_per_page'];
  if ($tidalMaxItemPerPage > 50) $tidalMaxItemPerPage = 50;
  $results = $t->getByApiPath($tidalMaxItemPerPage, $tidalMaxItemPerPage * ($curr_page - 1), $apiPath);
  
  $nav['name'][] = $results['title'] . ':';
  require_once('include/header.inc.php');
  echo ('<h1>&nbsp;' . $results['title'] .'</h1>');
  if ($tileSizePHP) $size = $tileSizePHP;
  
  if ($results['rows']){
    if ($type == 'album_list'){
      echo '<div class="albums_container">';
      foreach($results['rows'][0]['modules'][0]['pagedList']['items'] as $res) {
        $albums = array();
        $albums['album_id'] = 'tidal_' . $res['id'];
        $albums['album'] = $res['title'];
        $albums['cover'] = $t->albumCoverToURL($res['cover'],'lq');
        $albums['artist_alphabetic'] = $res['artists'][0]['name'];
        if ($cfg['show_album_format']) {
          $albums['audio_quality'] = $res['audioQuality'];
        }
        draw_tile ( $size, $albums, '', 'echo', '' );
      }
      $cfg['items_count'] = $results['totalNumberOfItems'];
    }
    elseif ($type == 'playlist_list'){
      echo '<div class="albums_container">';
      foreach($results['rows'][0]['modules'][0]['pagedList']['items'] as $res) {
        $albums = array();
        $albums['album_id'] = 'tidal_' . $res['uuid'];
        $albums['album'] = $res['title'];
        $albums['cover'] = $t->albumCoverToURL($res['squareImage'],'lq');
        if (!$albums['cover']) {
          $albums['cover'] = $t->albumCoverToURL($res['image'],'');
        }
        $albums['artist_alphabetic'] = getTidalPlaylistCreator($res);
        draw_Tidal_tile ( $size, $albums, '', 'echo', $albums['cover'],"playlist");
      }
      $cfg['items_count'] = $results['totalNumberOfItems'];
    }
    elseif ($type == 'mixlist_list'){
      echo '<div class="albums_container">';
      foreach($results['rows'][0]['modules'][0]['pagedList']['items'] as $res) {
        $albums = array();
        $albums['album_id'] = 'tidal_' . $res['id'];
        $albums['album'] = $res['title'];
        $albums['cover'] = getTidalMixlistPicture($res, 'fromMixlist');
        $albums['artist_alphabetic'] = ($res['subTitle']);
        draw_Tidal_tile ( $size, $albums, '', 'echo', $albums['cover'],"mixlist");
      }
      $cfg['items_count'] = $results['totalNumberOfItems'];
    }
    elseif ($type == 'mixed_types_list'){
      echo '<div class="albums_container">';
      foreach($results['rows'][0]['modules'][0]['pagedList']['items'] as $res) {
        $albums = array();
        $albums['album_id'] = 'tidal_' . $res['item']['uuid'];
        $albums['album'] = $res['item']['title'];
        $albums['cover'] = $t->albumCoverToURL($res['item']['squareImage'],'lq');
        if (!$albums['cover']) {
          $albums['cover'] = $t->albumCoverToURL($res['item']['image'],'');
        }
        $albums['artist_alphabetic'] = getTidalPlaylistCreator($res['item']);
        draw_Tidal_tile ( $size, $albums, '', 'echo', $albums['cover'],"playlist");
      }
      $cfg['items_count'] = $results['totalNumberOfItems'];
    }
    elseif ($type == 'track_list') {
      $tracksList = tidalTracksList($results['rows'][0]['modules'][0]['pagedList']);
      echo '<div>';
      echo $tracksList;
    }
  }
  else {
    echo ('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on TIDAL.</span>');
  }
}
else {
  echo('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution Tidal request.<br>Error message:<br><br>' . $conn["error"] . '</div>');
}
?>
</div>

<table cellspacing="0" cellpadding="0" class="border">

<tr class="<?php echo $class; ?> smallspace"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr class="line"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>

</table>

<?php
	require_once('include/footer.inc.php');
}



//  +------------------------------------------------------------------------+
//  | View Tidal playlist                                                    |
//  +------------------------------------------------------------------------+
function viewTidalPlaylist() {
  global $cfg, $db, $t;
  global $base_size, $spaces, $scroll_bar_correction;

  $type = get('type');
  $apiPath = get('apiPath');
  $playlist_id = getTidalId(get('album_id'));
  
  authenticate('access_media');

  // formattedNavigator
  /* $nav			= array();
  $nav['name'][]	= 'Library';
  $nav['url'][]	= 'index.php';
  $nav['name'][]	= 'Tidal';
  $nav['url'][]	= 'index.php?action=viewTidal'; */

$conn = $t->connect();
if ($conn === true){
  
  $results = $t->getPlaylist($playlist_id);
  
  $nav['name'][] = $results['title'] . ':';
  require_once('include/header.inc.php');
  //echo ('<h1>' . $results['title'] .'</h1>');
  if ($tileSizePHP) $size = $tileSizePHP;
  
  if ($results['uuid']){
    tidalPlaylist($playlist_id, $results);
  }
  else {
    echo ('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on TIDAL.</span>');
  }
}
else {
  echo('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution Tidal request.<br>Error message:<br><br>' . $conn["error"] . '</div>');
}
?>
</div>

<table cellspacing="0" cellpadding="0" class="border">

<tr class="<?php echo $class; ?> smallspace"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr class="line"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>

</table>

<?php
	require_once('include/footer.inc.php');
}



//  +------------------------------------------------------------------------+
//  | View Tidal mix list                                                    |
//  +------------------------------------------------------------------------+
function viewTidalMixlist() {
  global $cfg, $db, $t;
  global $base_size, $spaces, $scroll_bar_correction;

  $type = get('type');
  $apiPath = get('apiPath');
  $mixlist_id = getTidalId(get('album_id'));
  
  authenticate('access_media');

  // formattedNavigator
  /* $nav			= array();
  $nav['name'][]	= 'Library';
  $nav['url'][]	= 'index.php';
  $nav['name'][]	= 'Tidal';
  $nav['url'][]	= 'index.php?action=viewTidal'; */

$conn = $t->connect();
if ($conn === true){
  
  $results = $t->getMixList($mixlist_id);
  
  $nav['name'][] = $results['title'] . ':';
  require_once('include/header.inc.php');

  if ($tileSizePHP) $size = $tileSizePHP;
  
  if ($results['id']){
    tidalMixList($mixlist_id, $results);
  }
  else {
    echo ('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on TIDAL.</span>');
  }
}
else {
  echo('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution Tidal request.<br>Error message:<br><br>' . $conn["error"] . '</div>');
}
?>
</div>

<table cellspacing="0" cellpadding="0" class="border">

<tr class="<?php echo $class; ?> smallspace"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr class="line"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>

</table>

<?php
	require_once('include/footer.inc.php');
}



//  +------------------------------------------------------------------------+
//  | HRA                                                                    |
//  +------------------------------------------------------------------------+
function viewHRA() {
	global $cfg, $db;
	global $base_size, $spaces, $scroll_bar_correction, $tileSize;
	
	authenticate('access_media');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'HighResAudio:';
	require_once('include/header.inc.php');
  ?>
  <div>
  <h1 onclick='toggleSearchResults("GE");' class="pointer" id="hraGenres"><i id="iconSearchResultsGE" class="fa fa-chevron-circle-down icon-anchor"></i> New albums by genre</h1>
  <div id="searchResultsGE">
  <span id="albumsLoadingIndicator">
    <i class="fa fa-cog fa-spin icon-small"></i> Loading genre list...
  </span>
  </div>
  </div>
  <script>
  $('#hraGenres').click(function() {
    var request = $.ajax({  
      url: "ajax-hra-search.php",
      type: "POST",
      data: { search : "genre" },
      dataType: "json"
    }); 

    request.done(function( data ) {  
      if (data.genre_results > 0) { //check if any genre recieved
        $( "#searchResultsGE" ).html( data.genreList );
      }
      else {
        if (data.return == 1) {
          $("#albumsLoadingIndicator").hide();
          $("#searchResultsGE").html('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution HighResAudio request.<br>Error message:<br><br>' + data.response + '</div>');
        }
        else {
          $("#albumsLoadingIndicator").hide();
          $("#searchResultsGE").html('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on HighResAudio.</span>');
        }
      }
      changeTileSizeInfo();
    }); 

    request.fail(function( jqXHR, textStatus ) {  
      $("#albumsLoadingIndicator").hide();
      $("#searchResultsGE").html('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution HRA request.</div>');
    }); 

    request.always(function() {
    });
  });
  </script>
  <div class="area">
  <?php
  showNewHRAAlbumsByCategory('New albums', '/HIGHRES AUDIO/Musicstore/');
  showNewHRAAlbumsByCategory('Editors Choice', '/HIGHRES AUDIO/Musicstore/Editors Choice');
  showNewHRAAlbumsByCategory('High-Res Essentials', '/HIGHRES AUDIO/Musicstore/High-Res Essentials');
  showNewHRAAlbumsByCategory('Bestsellers', '/HIGHRES AUDIO/Musicstore/Bestsellers');
  showNewHRAAlbumsByCategory('Top albums', '/HIGHRES AUDIO/Musicstore/Top Alben');
  showNewHRAAlbumsByCategory('Listening tips', '/HIGHRES AUDIO/Musicstore/Hörtipps');
  
  $query = mysqli_query($db,"SELECT * FROM album WHERE album_id IN (SELECT album_id FROM album_id WHERE path LIKE 'hra_%') ORDER BY album_add_time DESC LIMIT 14");
  if (mysqli_num_rows($query) > 0) {
  ?>
  <h1>&nbsp;Albums from HRA added to local library <a href="index.php?action=viewAlbumsFromStreamingService&service=HRA">(more...)</a></h1>
  <div class="albums_container">
  <?php
  while ($album = mysqli_fetch_assoc($query)){
      draw_tile($tileSizePHP, $album);
    }
  ?>
  </div>

  <?php
  }
  echo '</div>';

  require_once('include/footer.inc.php');
}





//  +------------------------------------------------------------------------+
//  | View new from HRA                                                      |
//  +------------------------------------------------------------------------+
function viewNewFromHRA() {
	global $cfg, $db;
	global $base_size, $spaces, $scroll_bar_correction;
	
  //$type = get('type');
  $prefix = get('prefix');
  $categoryName = get('categoryName');
  $genreM = '';
  if ($prefix) {
    if (strpos($prefix,"/Genre/") !== false) {
      $exploded = explode("/",$prefix);
      $counter = count($exploded);
      $genreM = $exploded[4];
      $categoryName = "New " . $genreM . " albums";
      if ($counter == 6) { // with subgenre
        $subgenre = $exploded[5];
        $categoryName = 'New ' . $genreM . ' > ' . $subgenre . ' albums';
      }
    }
  }
  authenticate('access_media');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
  $nav['name'][]	= 'HighResAudio';
	$nav['url'][]	= 'index.php?action=viewHRA';
  if ($subgenre) {
    $nav['name'][]	= $genreM;
    $nav['url'][]	= 'index.php?action=viewNewFromHRA&amp;prefix=' . urlencode("/" . $exploded[1] . "/" . $exploded[2] . "/" . $exploded[3] . "/" . $exploded[4]);
    $nav['name'][]	= $subgenre;
  }
  else {
    $nav['name'][]	= $categoryName . ':';
  }
  require_once('include/header.inc.php');
?>
 <div>
  <h1 onclick='toggleSearchResults("GE");' class="pointer" id="hraGenres"><i id="iconSearchResultsGE" class="fa fa-chevron-circle-down icon-anchor"></i> Show subgenres<?php echo $genreM ? " for " . $genreM : ""; ?></h1>
  <div id="searchResultsGE">
  <span id="albumsLoadingIndicator">
    <i class="fa fa-cog fa-spin icon-small"></i> Loading subgenre list...
  </span>
  </div>
  </div>
  <script>
  $('#hraGenres').click(function() {
    var request = $.ajax({  
      url: "ajax-hra-search.php",
      type: "POST",
      data: { search : "genre", showGenre : "<?php echo $genreM; ?>"},
      dataType: "json"
    }); 

    request.done(function( data ) {  
      if (data.genre_results > 0) { //check if any genre recieved
        $( "#searchResultsGE" ).html( data.genreList );
      }
      else {
        if (data.return == 1) {
          $("#albumsLoadingIndicator").hide();
          $("#searchResultsGE").html('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution HighResAudio request.<br>Error message:<br><br>' + data.response + '</div>');
        }
        else {
          $("#albumsLoadingIndicator").hide();
          $("#searchResultsGE").html('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on HighResAudio.</span>');
        }
      }
      changeTileSizeInfo();
    }); 

    request.fail(function( jqXHR, textStatus ) {  
      $("#albumsLoadingIndicator").hide();
      $("#searchResultsGE").html('<div style="line-height: initial;"><i class="fa fa-exclamation-circle icon-small"></i> Error in execution HRA request.</div>');
    }); 

    request.always(function() {
    });
  });
  </script>
<h1><?php echo $categoryName; ?></h1>
<div class="albums_container">
<?php
	if ($tileSizePHP) $size = $tileSizePHP;
  $h = new HraAPI;
  $h->username = $cfg["hra_username"];
  $h->password = $cfg["hra_password"];
  if (NJB_WINDOWS) $t->fixSSLcertificate();
  $conn = $h->connect();
  if ($conn === true) {
    $curr_page = (get('page') ? get('page') : 1);
    $results = $h->getCategorieContent($prefix, $cfg['max_items_per_page'], $cfg['max_items_per_page'] * ($curr_page - 1));
    if ($results['data']['results']){
      foreach($results['data']['results'] as $res) {
        if ($res['publishingStatus'] == 'published') {
          $albums = array();
          $albums['album_id'] = 'hra_' . $res['id'];
          $albums['album'] = $res['title'];
          $albums['cover'] = $res['cover'];
          $albums['artist_alphabetic'] = $res['artist'];
          if ($cfg['show_album_format']) {
            $albums['audio_quality_tag'] = calculateAlbumFormat("",$res['tags']);
          }
          draw_tile ( $size, $albums, '', 'echo', '' );
        }
      }
      if (isset($results['data']['totalCount'])){
        $cfg['items_count'] = $results['data']['totalCount'];
      }
      else {
        $cfg['items_count'] = $cfg['max_items_per_page'] * 30;
      }
    }
    else {
      $albums = null;
    }
  }
  else {
  $albums = null;
  }
  if ($albums === null) {
    echo ('<span><i class="fa fa-exclamation-circle icon-small"></i> No results found on HRA.</span>');
  }
?>
</div>

<table cellspacing="0" cellpadding="0" class="border">

<tr class="<?php echo $class; ?> smallspace"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr class="line"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>

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
	elseif	($period == 'byyear')	$days = 0;
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
				JOIN counter ON album.album_id = counter.album_id
				WHERE album.artist NOT IN (' . implode(',',$vaArray) . ')
				GROUP BY album.artist
				ORDER BY counter DESC, album.artist
				LIMIT 100');
		}
    elseif ($period == 'byyear') {
      $query_pop = mysqli_query($db,"SELECT album.album_id, album.year, COUNT(*) as counter FROM counter left JOIN album on counter.album_id = album.album_id 
      WHERE counter.album_id NOT LIKE '%\_%' AND year IS NOT NULL
      GROUP BY year
      ORDER BY year DESC");
      
      $count_year = mysqli_query($db,"SELECT MAX(c.counter) as max_c FROM (SELECT album.album_id, album.year, COUNT(*) as counter FROM counter left JOIN album on counter.album_id = album.album_id 
      WHERE counter.album_id NOT LIKE '%\_%' AND year IS NOT NULL
      GROUP BY year) c");
      $max_played = mysqli_fetch_assoc($count_year)['max_c'];
      
      $count_all_played = mysqli_query($db,"SELECT COUNT(*) as counter FROM counter
      WHERE album_id NOT LIKE '%\_%'");
      $all_played = mysqli_fetch_assoc($count_all_played)['counter']; 
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
	
	<td class="<?php echo ($period == 'artist') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='<?php echo $url; ?>&amp;period=artist';">By Artist</td>
	<td class="tab_none tabspace"></td>
  <td class="<?php echo ($period == 'byyear') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='<?php echo $url; ?>&amp;period=byyear';">By Album Year</td>
	<td class="tab_none tabspace"></td>
	<td class="<?php echo ($period == 'week') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='<?php echo $url; ?>&amp;period=week';">Last Week</td>
	<td class="tab_none tabspace"></td>
	<td class="<?php echo ($period == 'month') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='<?php echo $url; ?>&amp;period=month';">Last Month</td>
	<td class="tab_none tabspace"></td>
	<td class="<?php echo ($period == 'year') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='<?php echo $url; ?>&amp;period=year';">Last Year</td>
	<td class="tab_none tabspace"></td>
	<td class="<?php echo ($period == 'overall') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='<?php echo $url; ?>&amp;period=overall';">Overall</td>
	<td class="tab_none">&nbsp;</td>
</tr>
</table>
<?php if ($period == 'byyear') {
?>
<table width="100%" cellspacing="0" cellpadding="0" class="tab_border">
<tr class="tab_header">
	<td class="space left"></td>
	<td width="80px">Year</td>
	<td class="bar" align="left">Graph</td>
	<td width="130px" align="center">Number of plays</td>
</tr>
<?php
$i=1;
while ($album = mysqli_fetch_assoc($query_pop)) {
?>
<tr class="<?php echo ($i++ & 1) ? 'year' : 'year'; ?> mouseover">
	<td></td>
	<td><a href="index.php?action=view2&amp;year=<?php echo $album['year']; ?>"><?php echo $album['year']; ?></a></td>
	<td class="bar" style="cursor: pointer;" onClick="window.location.href='<?php echo NJB_HOME_URL ?>index.php?action=view2&amp;year=<?php echo $album['year']; ?>';"><div class="out"><div id="y<?php echo $album['year']; ?>" style="width: <?php echo round($album['counter'] / $max_played * 200)?>px;" class="in"></div></div></td>
	<td align="center"><?php echo $album['counter']; ?> (<?php echo  round($album['counter'] / $all_played * 100, 1); ?>%)</td>
</tr>

<?php
}
?>
<tr class="<?php echo ($i++ & 1) ? 'year' : 'year'; ?> mouseover">
	<td></td>
	<td></td>
	<td></td>
	<td align="center">Total: <?php echo $all_played; ?></td>
</tr>
</table>
<?php 
}
else {
?>  
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
			WHERE album.artist = '" . mysqli_real_escape_string($db, $album['artist']) . "'
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
<?php
}
?>
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
	$uid = $cfg['user_id'];
	if ($type == 'day') {
		$query_rp = mysqli_query($db, "
		SELECT album_id, time FROM counter
    WHERE user_id = $uid
		ORDER BY time DESC");
	}
	else {
    $query_rp = mysqli_query($db, "SELECT album_id, max(time) as time FROM counter
    WHERE user_id = $uid
    GROUP BY album_id ORDER BY max(time) DESC");
	}
	$album_multidisc = array();
	while ( $album = mysqli_fetch_assoc ($query_rp)) {
		$album_multidisc[$album['time'] . '_' .$album['album_id']] = array(
					'album_id' => $album['album_id'],
					'played_time' => $album['time']
					);
	}
	$cfg['items_count'] = count($album_multidisc);
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
    $a_id = $album_m['album_id'];
    if (isTidal($a_id) && !$cfg['use_tidal']) {
      continue;
    }
		if (isHra($a_id) && !$cfg['use_hra']) {
      continue;
    }
		$ts = $album_m['played_time'];
		$currDate = date('Y.m.d', $ts);
		//$start = mktime(0,0,0,$m,$d,$y);
		if ($currDate <> $prevDate && $type == 'day') {
			echo '<div class="decade">' . $currDate . ' - ' . date("l", ($ts)) . '</div>';
		}
		
		
			$albums = array();
			$albums['album_id'] = $a_id;
			$tidal_cover = '';
			if (isTidal($a_id)){
				$query1 = mysqli_query($db, "SELECT album, cover, artist_alphabetic, audio_quality FROM tidal_album 
				WHERE album_id='" . getTidalId($a_id) . "' LIMIT 1");
				$a = mysqli_fetch_assoc ( $query1 );
				$albums['album'] = $a['album'];
				$tidal_cover = $a['cover'];
				$albums['artist_alphabetic'] = $a['artist_alphabetic'];
				$albums['audio_quality'] = $a['audio_quality'];
			}
			elseif (isHra($a_id)){
				if (!$hra_session) {
					$h = new HraAPI;
					$h->username = $cfg["hra_username"];
					$h->password = $cfg["hra_password"];
					if (NJB_WINDOWS) $h->fixSSLcertificate();
					$conn = $h->connect();
					if ($conn === true) $hra_session = true;
				}
				if ($hra_session === true){
					$results = $h->getAlbum(getHraId($a_id));
					if ($results['data']['results']){
						$albums['album'] = $results['data']['results']['title'];
						$albums['cover'] = 'https://' . $results['data']['results']['cover']['master']['file_url'];
						$albums['artist_alphabetic'] = $results['data']['results']['artist'];
						if ($cfg['show_album_format']) {
							$albums['audio_quality'] = $results['data']['results']['tracks'][0]['format'];
						}
					}
					else {
						$albums = null;
					}
				}
				else {
					$albums = null;
				}
				
			}
			else {
				$query1 = mysqli_query($db, "SELECT album, image_id, artist_alphabetic FROM album 
				WHERE album_id='" . $a_id . "' LIMIT 1");
				$a = mysqli_fetch_assoc ( $query1 );
				$albums['album'] = $a['album'];
				$albums['image_id'] = $a['image_id'];
				$albums['artist_alphabetic'] = $a['artist_alphabetic'];
			}
			if ($albums) {
				$album_multidisc_1[$a_id] = array(
						'album_id' => $a_id,
						'image_id' => $albums['image_id'],
						'album' => $albums['album'],
						'artist_alphabetic' => $albums['artist_alphabetic'],
						'cover' => $albums['cover'],
						'audio_quality' => $albums['audio_quality']
						);
				draw_tile($size,$album_multidisc_1[$a_id],'allDiscs', 'echo', $tidal_cover);
			}
		
		
		
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
//  | View Tidal albums of given artist                                      |
//  +------------------------------------------------------------------------+
function viewTidalAlbums() {
	global $cfg, $db;
	global $base_size, $spaces, $scroll_bar_correction;
	
	authenticate('access_media');
	$tidalArtist = get('tidalArtist');
	$tidalArtistId = get('tidalArtistId');
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'Tidal: ablums by ' . ($tidalArtist);
	
	require_once('include/header.inc.php');

?>

<div class="albums_container">
<?php
	
	if ($tileSizePHP) $size = $tileSizePHP;
	showAlbumsFromTidal($tidalArtist, $size, false, $tidalArtistId);
?>
</div>

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
	SELECT * FROM
	(SELECT album_id, time FROM counter
	WHERE time > ' . $beginOfDay . ' AND time < ' . $endOfDay . '
	ORDER BY time DESC) c
	GROUP BY c.album_id
	ORDER BY c.time DESC' );

	$album_multidisc = array();
	while ( $album = mysqli_fetch_assoc ($query_rp)) {
		$album_multidisc[$album['time'] . '_' .$album['album_id']] = array(
					'album_id' => $album['album_id'],
					'played_time' => $album['time']
					);
	}
	$cfg['items_count'] = count($album_multidisc);
	
?>



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
		
		
			$albums = array();
			$a_id = $album_m['album_id'];
			$albums['album_id'] = $a_id;
			$tidal_cover = '';
			if (isTidal($a_id)){
				$query1 = mysqli_query($db, "SELECT album, cover, artist_alphabetic FROM tidal_album 
				WHERE album_id='" . getTidalId($a_id) . "' LIMIT 1");
				$a = mysqli_fetch_assoc ( $query1 );
				$albums['album'] = $a['album'];
				$tidal_cover = $a['cover'];
				$albums['artist_alphabetic'] = $a['artist_alphabetic'];
			}
			elseif (isHra($a_id)){
				if (!$hra_session) {
					$h = new HraAPI;
					$h->username = $cfg["hra_username"];
					$h->password = $cfg["hra_password"];
					if (NJB_WINDOWS) $h->fixSSLcertificate();
					$conn = $h->connect();
					if ($conn === true) $hra_session = true;
				}
				if ($hra_session === true){
					$results = $h->getAlbum(getHraId($a_id));
					if ($results['data']['results']){
						$albums['album'] = $results['data']['results']['title'];
						$albums['cover'] = 'https://' . $results['data']['results']['cover']['master']['file_url'];
						$albums['artist_alphabetic'] = $results['data']['results']['artist'];
					}
					else {
						$albums = null;
					}
				}
				else {
					$albums = null;
				}
				
			}
			else {
				$query1 = mysqli_query($db, "SELECT album, image_id, artist_alphabetic FROM album 
				WHERE album_id='" . $a_id . "' LIMIT 1");
				$a = mysqli_fetch_assoc ( $query1 );
				$albums['album'] = $a['album'];
				$albums['image_id'] = $a['image_id'];
				$albums['artist_alphabetic'] = $a['artist_alphabetic'];
			}
			if ($albums) {
				$album_multidisc_1[$a_id] = array(
						'album_id' => $a_id,
						'image_id' => $albums['image_id'],
						'album' => $albums['album'],
						'artist_alphabetic' => $albums['artist_alphabetic'],
						'cover' => $albums['cover']
						);
				draw_tile($size,$album_multidisc_1[$a_id],'allDiscs', 'echo', $tidal_cover);
			}
		
		
		
		$prevDate = $currDate;
	}
?>
</div>

<?php
	
	require_once('include/footer.inc.php');
}

//  +------------------------------------------------------------------------+
//  | View albums added to library from streaming services                   |
//  +------------------------------------------------------------------------+
function viewAlbumsFromStreamingService($service) {
	global $cfg, $db;
	global $base_size, $spaces, $scroll_bar_correction, $tileSizePHP;
	
  $type = get('type');
  authenticate('access_media');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Library';
	$nav['url'][]	= 'index.php';
  if ($service == 'HRA') {
    $nav['name'][]	= 'HighResAudio';
  }
  else {
    $nav['name'][]	= $service;
  }
	$nav['url'][]	= 'index.php?action=view' . $service;
  $nav['name'][] = "Albums from $service added to local library:";
  require_once('include/header.inc.php');
  echo ("<h1>Albums from $service in library</h1>");
  if ($tileSizePHP) $size = $tileSizePHP;
  echo '<div class="albums_container">';
  
  $query = mysqli_query($db,"SELECT * FROM album WHERE album_id IN (SELECT album_id FROM album_id WHERE path LIKE '" . strtolower($service) . "_%') ORDER BY album_add_time DESC");
  
  while ($album = mysqli_fetch_assoc($query)){
    draw_tile($size, $album);
  }
  echo '</div>';

?>

<table cellspacing="0" cellpadding="0" class="border">
<tr class="<?php echo $class; ?> smallspace"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr class="line"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
</table>

<?php
	require_once('include/footer.inc.php');
}


?>