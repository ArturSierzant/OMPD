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
//  | View 3                                                                 |
//  +------------------------------------------------------------------------+

global $cfg, $db;

$album_id = get('album_id');
//$albumType = 'local';

if ($album_id == '') {
	message(__FILE__, __LINE__, 'error', '[b]Album not found in database.[/b]');
	exit;
}

authenticate('access_media');

if (strpos($album_id, 'tidal_') !== false) {
	$query = mysqli_query($db, 'SELECT *, REPLACE(album_date," 00:00:00","") as year
	FROM tidal_album
	WHERE album_id = "' .  mysqli_real_escape_string($db,getTidalId($album_id)) . '" AND artist !=""'); //artist != "" for albums added from tidal playlists
	
	//$albumType = 'tidal';
	//album already in OMPD database
	if (mysqli_num_rows($query) > 0) {
		$album = mysqli_fetch_assoc($query);
		$image_id = $album_id;
		$total_time['sum_miliseconds'] = $album['seconds'] * 1000;
	}
	else {
		$getAlbum = json_decode(getAlbumFromTidal(getTidalId($album_id)),true);
		if ($getAlbum['return'] == 1) {
			$errMessage = '[b]Error in execution Tidal request.[/b][br]Error message:[br]'; 
			foreach($getAlbum['response'] as $res){
				$errMessage .= $res . '[br]';
			}
			message(__FILE__, __LINE__, 'error', $errMessage);
		}
		if ($getAlbum['results'] == 0) {
			$album = false;
		}
		else {
			$query = mysqli_query($db, 'SELECT *, REPLACE(album_date," 00:00:00","") as year
			FROM tidal_album
			WHERE album_id = "' .  mysqli_real_escape_string($db,getTidalId($album_id)) . '"');
			$album = mysqli_fetch_assoc($query);
			$image_id = $album_id;
			$total_time['sum_miliseconds'] = $album['seconds'] * 1000;
		}
	}
}
else {
	$query = mysqli_query($db, 'SELECT artist_alphabetic, artist, album, year, month, image_id, album_add_time, album.genre_id, album_dr
	FROM album
	WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '"');	
	$album = mysqli_fetch_assoc($query);
	$image_id = $album['image_id'];
}
	


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
if (!$total_time) $total_time = mysqli_fetch_assoc($query); 

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
	$basic[] = '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;album_id=' . $album_id . '\',evaluateAdd);"><i id="play_' . $album_id . '" class="fa fa-fw fa-play-circle-o  icon-small"></i>Play album</a>';
if ($cfg['access_add']){
	$basic[] = '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&album_id=' . $album_id . '\',evaluateAdd);"><i id="add_' . $album_id . '" class="fa fa-fw  fa-plus-circle  icon-small"></i>Add to playlist</a>';
	$basic[] = '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;album_id=' . $album_id . '&amp;insertType=album\',evaluateAdd);"><i id="insert_' . $album_id . '" class="fa fa-fw fa-indent icon-small"></i>Insert into playlist</a>';
}
if ($cfg['access_add'] && $cfg['access_play'])
	$basic[] = '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;album_id=' . $album_id . '&amp;insertType=album\',evaluateAdd);"><i id="insertPlay_' . $album_id . '" class="fa fa-fw  fa-play-circle icon-small"></i>Insert and play</a>';
if ($cfg['access_stream'] && !isTidal($album_id)){
	$basic[] = '<a href="stream.php?action=playlist&amp;album_id=' . $album_id . '&amp;stream_id=' . $cfg['stream_id'] . '"><i class="fa fa-fw  fa-rss  icon-small"></i>Stream album</a>';
}
if ($cfg['access_download'] && $cfg['album_download'] && !isTidal($album_id))
	$basic[] = '<a href="download.php?action=downloadAlbum&amp;album_id=' . $album_id . '&amp;download_id=' . $cfg['download_id'] . '" ' . onmouseoverDownloadAlbum($album_id) . '><i class="fa fa-fw  fa-download  icon-small"></i>Download album</a>';
if ($cfg['access_play'] && !isTidal($album_id)){
	$dir_path = rawurlencode(dirname($cfg['media_dir'] . $rel_file['relative_file']));
	$basic[] = '<a href="browser.php?dir=' . $dir_path . '"><i class="fa fa-fw  fa-folder-open  icon-small"></i>Browse...</a>';
}
if ($cfg['access_admin'] && !isTidal($album_id)){
	$dir_path = rawurlencode(dirname($cfg['media_dir'] . $rel_file['relative_file']));
	$basic[] = '<a href="update.php?action=update&amp;dir_to_update=' . $dir_path . '/&amp;sign=' . $cfg['sign'] . '"><i class="fa fa-fw fa-refresh fa-fw icon-small"></i>Update album</a>';
}
if ($cfg['access_admin'] && $cfg['album_share_stream'] && !isTidal($album_id))
	$basic[] = '<a href="stream.php?action=shareAlbum&amp;album_id='. $album_id . '&amp;sign=' . $cfg['sign'] . '"><i class="fa fa-fw  fa-share-square-o  icon-small"></i>Share stream</a>';
if ($cfg['access_admin'] && $cfg['album_share_download'] && !isTidal($album_id))
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
	if (hasThe($album['artist'])){
		$search_str = 'artist = "' .  mysqli_real_escape_string($db,moveTheToBegining($album['artist'])) . '" OR artist = "' .  mysqli_real_escape_string($db,moveTheToEnd($album['artist'])) . '"';
	}
	else {
		$search_str = 'artist = "' .  mysqli_real_escape_string($db,$album['artist']) . '"';
	}
	
	for ($j=0; $j<$l; $j++) {
		$art =  mysqli_real_escape_string($db,$exploded[$j]);
		$art = replaceAnds($art);
		$as = $cfg['artist_separator'];
		$count = count($as);
		$i=0;
		
		for($i=0; $i<$count; $i++) {
			if (hasThe($art)){
				$search_str .= ' OR artist LIKE "' . moveTheToEnd($art) . '" 
				OR artist LIKE "' . moveTheToEnd($art) . $as[$i] . '%" 
				OR artist LIKE "%' . $as[$i] . moveTheToEnd($art) . '" 
				OR artist LIKE "%' . $as[$i] . moveTheToEnd($art) . $as[$i] . '%" 
				OR artist LIKE "% & ' . moveTheToEnd($art) . $as[$i] . '%" 
				OR artist LIKE "%' . $as[$i] . moveTheToEnd($art) . ' & %"';
				$search_str .= ' OR artist LIKE "' . moveTheToBegining($art) . '" 
				OR artist LIKE "' . moveTheToBegining($art) . $as[$i] . '%" 
				OR artist LIKE "%' . $as[$i] . moveTheToBegining($art) . '" 
				OR artist LIKE "%' . $as[$i] . moveTheToBegining($art) . $as[$i] . '%" 
				OR artist LIKE "% & ' . moveTheToBegining($art) . $as[$i] . '%" 
				OR artist LIKE "%' . $as[$i] . moveTheToBegining($art) . ' & %"';
			}
			else {
				$search_str .= ' OR artist LIKE "' . $art .'" 
				OR artist LIKE "' . $art . '' . $as[$i] . '%" 
				OR artist LIKE "%' . $as[$i] . '' . $art . '" 
				OR artist LIKE "%' . $as[$i] . '' . $art . '' . $as[$i] . '%" 
				OR artist LIKE "% & ' . $art . '' . $as[$i] . '%" 
				OR artist LIKE "%' . $as[$i] . '' . $art . ' & %"';
				//last 2 lines above for artist like 'Mitch & Mitch' in 'Zbigniew Wodecki; Mitch & Mitch; Orchestra and Choir'
			}
		}
	}
	//echo $search_str;
	$queryStr = 'SELECT album, artist, artist_alphabetic, year, month, genre_id, image_id, album_id, album_dr FROM album WHERE (' . $search_str . '
		) ORDER BY year, month, artist_alphabetic, album';
	$query = mysqli_query($db, $queryStr);
	$discCount = mysqli_num_rows($query);
	if ($discCount > 1 or isTidal($album_id)) {
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
		//$playCounter = 0;
		$query_pop = mysqli_query($db, 'SELECT COUNT(*) AS counter
		FROM counter
		WHERE album_id = "' . $discography['album_id'] . '"');
		$playCounter = mysqli_fetch_assoc($query_pop);
		//$albumPopularity = $playCounter['counter'];
	echo '<img id="thumb' . $discography['album_id'] .  '" class="imgThumb' . $selected . '" onclick=\'location.href="index.php?action=view3&amp;album_id=' . $discography['album_id'] . '"\' src="image.php?image_id=' . $discography['image_id'] . '" onMouseOver="return overlib(\'' . htmlspecialchars(addslashes($discography['artist']), ENT_QUOTES) . '</div><div class=' . chr(92) . chr(39) . 'ol_line' . chr(92) . chr(39) . '><div>' . htmlspecialchars(addslashes($discography['album']), ENT_QUOTES) . '</div><div class=' . chr(92) . chr(39) . 'ol_line' . chr(92) . chr(39) . '></div><div>' . $discography['year'] . '</div><div class=' . chr(92) . chr(39) . 'ol_line' . chr(92) . chr(39) . '></div><div>Played: ' . $playCounter['counter'] .  ($playCounter['counter'] == 1 ? " time" : " times") . '</div>\', CAPTION , \'Go to album\');" onMouseOut="return nd();" alt="">';
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
<?php }; 

if (isTidal($album_id)) {
?>

<div class="line">
	<div class="add-info-left">Source:</div>
	<div class="add-info-right"><a href="<?php echo TIDAL_ALBUM_URL . getTidalId($album_id) ?>" target="new">TIDAL</a>
	</div>
</div>
<?php
}

?>

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
<?php }; 
if ($album['album_add_time']) {
?>

<div class="line">
	<div class="add-info-left">Added at:</div>
	<div class="add-info-right"><?php echo date("Y-m-d H:i:s",$album['album_add_time']); ?>
	</div>
</div>
<?php
}
?>

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
?>
