<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2018 Artur Sierzant	                           |
//  | http://www.ompd.pl                                             		     |
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
//  | statistics.php                                                         |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
require_once('include/cache.inc.php');
$cfg['menu'] = 'config';
@ob_flush();
flush();

authenticate('access_statistics');

// formattedNavigator
$nav			= array();
$nav['name'][]	= 'Configuration';
$nav['url'][]	= 'config.php';
$nav['name'][]	= 'Media statistics';
$nav['url'][]	= 'statistics.php';
$nav['name'][]	= $title;
require_once('include/header.inc.php');

$action	 			= get('action');
$audio_dataformat 	= get('audio_dataformat');
$video_dataformat	= get('video_dataformat');
$page 				= (get('page') ? get('page') : 1);
$max_item_per_page 	= $cfg['max_items_per_page'];

if	($audio_dataformat)	{
	$title = $audio_dataformat . ' audio';
	//$onmouseoverImage = true;
	$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.image_id, album.album_id
		FROM track, album 
		WHERE track.audio_dataformat = "' . mysqli_real_escape_string($db,$audio_dataformat) . '"
		AND track.video_dataformat = ""
		AND track.album_id = album.album_id 
		GROUP BY album.album_id 
		ORDER BY album.artist_alphabetic, album.album');
	$cfg['items_count'] = $album_count = mysqli_num_rows($query);
	if ($album_count > $max_item_per_page) {
			$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.image_id, album.album_id
			FROM track, album 
			WHERE track.audio_dataformat = "' . mysqli_real_escape_string($db,$audio_dataformat) . '"
			AND track.video_dataformat = ""
			AND track.album_id = album.album_id 
			GROUP BY album.album_id 
			ORDER BY album.artist_alphabetic, album.album
			LIMIT ' . ($page - 1) * $max_item_per_page . ','  . ($max_item_per_page));
	}
}
elseif ($video_dataformat) {
	$title = $video_dataformat . ' video';
	$onmouseoverImage = true;
	$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.image_id, album.album_id
		FROM track, album 
		WHERE track.video_dataformat = "' . mysqli_real_escape_string($db,$video_dataformat) . '"
		AND track.album_id = album.album_id 
		GROUP BY album.album_id 
		ORDER BY album.artist_alphabetic, album.album');
}
elseif ($action == 'all') {
	$title = 'All';
	//$onmouseoverImage = true;
	$query = mysqli_query($db,'SELECT artist_alphabetic, album, album.image_id, album.album_id
		FROM album 
		ORDER BY artist_alphabetic, album');
	$cfg['items_count'] = $album_count = mysqli_num_rows($query);
	if ($album_count > $max_item_per_page) {
			$query = mysqli_query($db,'SELECT artist_alphabetic, album, album.image_id, album.album_id
		FROM album 
		ORDER BY artist_alphabetic, album
			LIMIT ' . ($page - 1) * $max_item_per_page . ','  . ($max_item_per_page));
	}
}
elseif ($action == 'unique_played') {
	$title = 'Played albums';
	//$onmouseoverImage = true;
	$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.image_id, album.album_id
		FROM album RIGHT JOIN (SELECT DISTINCT counter.album_id FROM counter) as c
		ON album.album_id = c.album_id
		ORDER BY album.artist_alphabetic, album.album');
	$cfg['items_count'] = $album_count = mysqli_num_rows($query);
	if ($album_count > $max_item_per_page) {
			$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.image_id, album.album_id
			FROM album RIGHT JOIN (SELECT DISTINCT counter.album_id FROM counter) as c
			ON album.album_id = c.album_id
			ORDER BY album.artist_alphabetic, album.album
			LIMIT ' . ($page - 1) * $max_item_per_page . ','  . ($max_item_per_page));
	}	
	
}
elseif ($action == 'not_played') {
	$title = 'Never played albums';
	//$onmouseoverImage = true;
	$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.image_id, album.album_id
		FROM album 
		WHERE album_id NOT IN  
		(SELECT DISTINCT counter.album_id FROM counter)
		ORDER BY album.artist_alphabetic, album.album');
	$cfg['items_count'] = $album_count = mysqli_num_rows($query);
	if ($album_count > $max_item_per_page) {
			$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.image_id, album.album_id
			FROM album 
			WHERE album_id NOT IN  
			(SELECT DISTINCT counter.album_id FROM counter)
			ORDER BY album.artist_alphabetic, album.album
			LIMIT ' . ($page - 1) * $max_item_per_page . ','  . ($max_item_per_page));
	}
}
elseif ($action == 'noImageFront') {
	$title = 'No image_front file';
	//$onmouseoverImage = false;
	$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.album_id
		FROM album, bitmap
		WHERE image_front = ""
		AND album.album_id = bitmap.album_id 
		ORDER BY album.artist_alphabetic, album.album');
	$cfg['items_count']  = $noImageFrontCount = mysqli_num_rows($query);
	if ($noImageFrontCount > $max_item_per_page) {
		$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.album_id
		FROM album, bitmap
		WHERE image_front = ""
		AND album.album_id = bitmap.album_id 
		ORDER BY album.artist_alphabetic, album.album
		LIMIT ' . ($page - 1) * $max_item_per_page . ','  . ($max_item_per_page));
	}
}
elseif ($action == 'imageError') {
	$title = 'image_front file error';
	//$onmouseoverImage = false;
	$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.album_id
		FROM album, bitmap
		WHERE flag = "10"
		AND album.album_id = bitmap.album_id 
		ORDER BY album.artist_alphabetic, album.album');
}
elseif ($action == 'noImageFrontCover') {
	$title = 'image_front file has less then ' . $cfg['image_front_cover_treshold'] . 'px';
	//$onmouseoverImage = false;
	$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.album_id, album.image_id
		FROM album, bitmap
		WHERE image_front_width * image_front_height < ' . $cfg['image_front_cover_treshold'] . '
		AND album.album_id = bitmap.album_id 
		ORDER BY album.artist_alphabetic, album.album');
		$cfg['items_count']  = $noImageFrontCoverCount = mysqli_num_rows($query);
	if ($noImageFrontCoverCount > $max_item_per_page) {
		$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.album_id, album.image_id
		FROM album, bitmap
		WHERE image_front_width * image_front_height < ' . $cfg['image_front_cover_treshold'] . '
		AND album.album_id = bitmap.album_id 
		ORDER BY album.artist_alphabetic, album.album
		LIMIT ' . ($page - 1) * $max_item_per_page . ','  . ($max_item_per_page));
	}
}
elseif ($action == 'noImageBackCover') {
	$title = 'No ' . $cfg['image_back'] . ' for cover';
	$onmouseoverImage = false;
	$query = mysqli_query($db,'SELECT album.artist_alphabetic, album.album, album.album_id
		FROM album, bitmap
		WHERE image_back = ""
		AND album.album_id = bitmap.album_id 
		ORDER BY album.artist_alphabetic, album.album');
}
elseif 	($action == 'duplicateContent')			duplicateContent();
elseif 	($action == 'duplicateName')			duplicateName();
elseif 	($action == 'duplicateFileName')		duplicateFileName();
elseif 	($action == 'fileError')				fileError();
elseif 	($action == 'deleteFile')				deleteFile();
elseif	($action == '')							mediaStatistics();
else											message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');

?>


<div class="albums_container">
<?php
	while ($album = mysqli_fetch_assoc($query)) {		
			if ($album) {
				if ($tileSizePHP) $size = $tileSizePHP;
				draw_tile($size,$album);
				$yearPrev = $yearAct;
			}
		} 
?>
</div>


<?php
require_once('include/footer.inc.php');
exit();




//  +------------------------------------------------------------------------+
//  | Media statistics                                                       |
//  +------------------------------------------------------------------------+
function mediaStatistics() {
	global $cfg, $db;
	authenticate('access_statistics');
	@ob_flush();
	flush();
	
	$query = mysqli_query($db,'SELECT artist FROM album GROUP BY artist');
	$artists = mysqli_affected_rows($db);
	
	$query = mysqli_query($db,'SELECT COUNT(discs) AS albums, SUM(discs) AS discs FROM album');
	$album = mysqli_fetch_assoc($query);
	
	$query = mysqli_query($db, 'SELECT album, album_add_time, album_id, image_id, artist, artist_alphabetic
			FROM album
			WHERE album_add_time
			');
	$album_multidisc = albumMultidisc($query);
	
	$query = mysqli_query($db,'SELECT COUNT(relative_file) AS all_tracks,
		SUM(miliseconds) AS sum_miliseconds,
		SUM(filesize) AS sum_size
		FROM track');
	$track = mysqli_fetch_assoc($query);
	$total_miliseconds = $track['sum_miliseconds'];
	
	$query = mysqli_query($db,'SELECT
		SUM(filesize) AS sum_size
		FROM cache');
	$cache = mysqli_fetch_assoc($query);
	
	$database_size = 0;
	$query = mysqli_query($db,'SHOW TABLE STATUS');
	while ($database = mysqli_fetch_assoc($query))
		$database_size += $database['Data_length'] + $database['Index_length'];
		
	$query = mysqli_query($db,'SELECT artist, title, COUNT(artist) AS n1, COUNT(title) AS n2
		FROM track
		GROUP BY artist, title
		HAVING n1 > 1 AND n2 > 1');
	$duplicate_name = mysqli_affected_rows($db);
	
	$query = mysqli_query($db,'SELECT COUNT(*) as played FROM counter');
	$rsPlayed = mysqli_fetch_assoc($query);
	$total_played_albums = $rsPlayed['played'];
	
	$query = mysqli_query($db,'SELECT COUNT(c.album_id) as played FROM (SELECT DISTINCT album_id FROM counter) as c');
	$rsPlayedUnique = mysqli_fetch_assoc($query);
	$unique_played_albums = $rsPlayedUnique['played'];
	
	/* $query = mysqli_query($db,'SELECT COUNT(c.album_id) as not_played FROM (SELECT DISTINCT album.album_id FROM album WHERE album.album_id NOT IN 
	(SELECT DISTINCT counter.album_id FROM counter)) as c');
	$rsNotPlayed = mysqli_fetch_assoc($query);
	$not_played_albums = $rsNotPlayed['not_played'];
	 */
	$query = mysqli_query($db,'SELECT SUBSTRING_INDEX( track_id, "_", -1 ) AS hash, filesize, COUNT( SUBSTRING_INDEX( track_id, "_", -1 ) ) AS n1, COUNT( filesize ) AS n2
	FROM track
	GROUP BY filesize, hash
	HAVING n1 > 1 AND n2 > 1');
	$duplicate_content = mysqli_affected_rows($db);
		
	$media_total_space = disk_total_space($cfg['media_dir']);
	$media_free_space = disk_free_space($cfg['media_dir']);
	$media_used_space = $media_total_space - $media_free_space;
		
	$cache_total_space = disk_total_space(NJB_HOME_DIR . 'cache/');
	$cache_free_space = disk_free_space(NJB_HOME_DIR . 'cache/');
	$cache_used_space = $cache_total_space - $cache_free_space;
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Media statistics';
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>Quantity:</td>
	<td class="textspace"></td>
	<td></td>
	<td class="textspace"></td>
	<td colspan="3"></td>
	<td class="space"></td>
</tr>

<tr class="odd mouseover">
	<td></td>
	<td>Number of album artists:</td>
	<td></td>
	<td align="right"><?php echo $artists; ?></td>
	<td colspan="5"></td>
</tr>
<tr class="even mouseover">
	<td></td>
	<td>Number of albums:</td>
	<td></td>
	<td align="right"><?php echo $cfg['items_count']; ?></td>
	<td colspan="5"></td>
</tr>
<tr class="odd mouseover">
	<td></td>
	<td>Number of discs:</td>
	<td></td>
	<td align="right"><?php echo $album['albums']; ?></td>
	<td colspan="5"></td>
</tr>
<!--
<tr class="odd mouseover">
	<td></td>
	<td>Number of discs:</td>
	<td></td>
	<td align="right"><?php echo $album['discs']; ?></td>
	<td colspan="5"></td>
</tr>
-->
<tr class="even mouseover">
	<td></td>	
	<td>Number of tracks:</td>
	<td></td>
	<td align="right"><?php echo $track['all_tracks']; ?></td>
	<td colspan="5"></td>
</tr>

<?php 
$max = 0;
$max_year = 0;
$histogram = array();
$histogram_year = array();
$date = new DateTime();
$query = mysqli_query($db,'SELECT album_add_time FROM album ORDER BY album_add_time DESC');
while ($rs = mysqli_fetch_assoc($query)) {
	$date->setTimestamp($rs['album_add_time']);
	$period = $date->format('Y-m');
	$period_year = $date->format('Y');
	$histogram[$period] += 1;
	$histogram_year[$period_year] += 1;
	if ($histogram[$period] > $max) $max = $histogram[$period];
	if ($histogram_year[$period_year] > $max_year) $max_year = $histogram_year[$period_year];
};


$histogram_count = count($histogram);
for ($i=0; $i<$histogram_count; $i++)
{
        if ($histogram[$i] > $max)
        {
                $max = $histogram[$i];
        }
};

?>
<tr class="header">
	<td class="space"></td>
	<td>Increase of discs:</td>
	<td class="textspace"></td>
	<td></td>
	<td class="textspace"></td>
	<td colspan="2"></td>
	<td></td>
	<td class="space"></td>
</tr>

<tr class="odd mouseover">
	<td></td>
	<td>By year:</td>
	<td></td>
	<td align="right">
		<?php 
		foreach ($histogram_year as $key => $value) {
		echo '<div>' . $key . '</div>';
		}
		?>
	</td>
	<td></td>
	<td></td>
	<td class="bar">
		<?php 
		foreach ($histogram_year as $key => $value) {
		echo '<div><div class="out-statistics"><div style="width: ' . ($value/$max_year)*100 . 'px;" class="in"></div></div></div>';
		}
		?>
	</td>
	<td>
	<?php 
		foreach ($histogram_year as $key => $value) {
		echo '<div style="text-align: right;">' . $value . '</div>';
		}
		?>
	</td>
	<td></td>
</tr>

<tr class="odd mouseover">
	<td></td>
	<td>By month:</td>
	<td></td>
	<td align="right">
		<?php
		$isHidden = false;
		$maxItems = 24;
		$i = 0;
		foreach ($histogram as $key => $value) {		
			echo '<div' . (($i > $maxItems) ? ' class="no-display"' : '') . ' id="incOfDiscs' . $i . '">' . $key . '</div>';
			$i++;
			if ($i > $maxItems) $isHidden = true;
		}
		?>
	</td>
	<td></td>
	<td></td>
	<td class="bar">
		<?php
		$i = 0;
		foreach ($histogram as $key => $value) {
			$month = (int)substr($key,-2);
			$year  = (int)substr($key,0,4);
			
			$first = mktime(0,0,0,$month,1,$year);
			$first = new DateTime(date('r', $first));
			$tsStart = $first->getTimestamp();
			
			$last = mktime(23,59,00,$month+1,0,$year);
			$last = new DateTime(date('r', $last));
			$tsEnd = $last->getTimestamp();
			
			echo '<div onClick="window.location.href=\'' . NJB_HOME_URL . 'index.php?action=viewNew&tsStart=' . $tsStart . '&tsEnd=' . $tsEnd . '&addedOn=' . $key . '\'" id="incOfDiscsCount' . $i . '"' . (($i > $maxItems) ? ' class="no-display pointer"' : ' class="pointer"') . ' onMouseOver="return overlib(\'See new albums from ' . $key . '\');" onMouseOut="return nd();"><div class="out-statistics"><div style="width: ' . ($value/$max)*100 . 'px;" class="in"></div></div></div>';
			$i++;}
		?>
		
	</td>
	<td>
	<?php 
		$i = 0;
		foreach ($histogram as $key => $value) {
		echo '<div style="text-align: right;" id="incOfDiscsCount' . $i . '"' . (($i > $maxItems) ? ' class="no-display"' : '') . '>' . $value . '</div>';
		$i++;
		}
		?>
	</td>
	<td></td>
</tr>
<?php 
if ($isHidden) {
?>
<tr id="histMoreRow" class="odd mouseover">
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td><div class="buttons" style="text-align: center;"><span id="histMore" style="padding: 5px;">more...</span></div></td>
	<td></td>
	<td></td>
</tr>
<?php
}
?>


<tr class="header">
	<td></td>
	<td>Filesize:</td>
	<td colspan="7"></td>
</tr>

<tr class="odd mouseover">
	<td></td>
	<td>Media:</td>
	<td></td>
	<td align="right"><?php echo formattedSize($track['sum_size']); ?> (<?php echo formattedSize($media_used_space) . ' [' . number_format($media_used_space / $media_total_space * 100, 1) . '%] used of ' . formattedSize($media_total_space) . ' total'; ?>)</td>
	<td></td>
	<td></td>
	<td class="bar" onMouseOver="return overlib('<?php echo number_format($media_used_space / $media_total_space * 100, 1) . '%<br>' . formattedSize($media_used_space) . ' / ' . formattedSize($media_total_space); ?>');" onMouseOut="return nd();"><div class="out-statistics"><div style="width: <?php echo round($media_used_space / $media_total_space * 100); ?>px;" class="in"></div></div></td>
	<td></td>
	
	<td></td>
</tr>
<tr class="even mouseover">
	<td></td>
	<td>Cache:</td>
	<td></td>
	<td align="right"><?php echo formattedSize($cache['sum_size']); ?> (<?php echo formattedSize($cache_used_space) . ' [' . number_format($cache_used_space / $cache_total_space * 100, 1) . '%] used of ' . formattedSize($cache_total_space) . ' total'; ?>)</td>
	<td></td>
	
	<td></td>
	<td class="bar" onMouseOver="return overlib('<?php echo number_format($cache_used_space / $cache_total_space * 100, 1) . '%<br>' . formattedSize($cache_used_space) . ' / ' . formattedSize($cache_total_space); ?>');" onMouseOut="return nd();"><div class="out-statistics"><div style="width: <?php echo round($cache_used_space / $cache_total_space * 100); ?>px;" class="in"></div></div></td>
	<td></td>
		
	<td></td>
</tr>
<tr class="odd mouseover">
	<td></td>
	<td>Database:</td>
	<td></td>
	<td align="right"><?php echo formattedSize($database_size); ?></td>
	<td colspan="5"></td>
</tr>
<?php
	if (is_dir($cfg['external_storage'])) {
		$external_storage_total_space = disk_total_space($cfg['external_storage']);
		$external_storage_free_space = disk_free_space($cfg['external_storage']);
		$external_storage_used_space = $external_storage_total_space - $external_storage_free_space; ?>
	<tr class="even mouseover">
		<td></td>
		<td>External storage:</td>
		<td></td>
		<td align="right"><?php echo formattedSize($external_storage_used_space); ?></td>
		<td></td>
		<td></td>
		<td class="bar" onMouseOver="return overlib('<?php echo number_format($external_storage_used_space / $external_storage_total_space * 100, 1) . '%<br>' . formattedSize($external_storage_used_space) . ' / ' . formattedSize($external_storage_total_space); ?>');"  onMouseOut="return nd();"><div class="out-statistics"><div style="width: <?php echo round($external_storage_used_space / $external_storage_total_space * 100); ?>px; overflow: hidden;" class="in"></div></div></td>
		<td></td>	
		<td></td>
	</tr>
<?php
	}
?>

<tr class="header">
	<td></td>
	<td>Playtime:</td>
	<td colspan="7"></td>
</tr>

<?php
	$i = 0;
	$query = mysqli_query($db,'SELECT audio_dataformat FROM track WHERE audio_dataformat != "" AND video_dataformat = "" GROUP BY audio_dataformat ORDER BY audio_dataformat');
	while($track = mysqli_fetch_assoc($query)) {
		$audio_dataformat = $track['audio_dataformat'];
		$track = mysqli_fetch_assoc(mysqli_query($db,'SELECT SUM(miliseconds) AS sum_miliseconds FROM track WHERE audio_dataformat = "' . mysqli_real_escape_string($db,$audio_dataformat) . '" AND video_dataformat = ""')); ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>
	<td><a href="statistics.php?audio_dataformat=<?php echo $audio_dataformat; ?>"><?php echo $audio_dataformat;?>:</a></td>
	<td></td>
	<td align="right"><?php echo formattedTime($track['sum_miliseconds']); ?> [<?php echo number_format($track['sum_miliseconds'] / $total_miliseconds * 100, 1); ?> %]</td>
	<td></td>
	<td></td>
	<td class="bar" style="cursor: pointer;" onClick="window.location.href='<?php echo NJB_HOME_URL ?>statistics.php?audio_dataformat=<?php echo $audio_dataformat; ?>';" onMouseOver="return overlib('<?php echo number_format($track['sum_miliseconds'] / $total_miliseconds * 100, 1); ?> %');" onMouseOut="return nd();"><div class="out-statistics"><div style="width: <?php echo round($track['sum_miliseconds'] / $total_miliseconds * 100); ?>px; overflow: hidden;" class="in"></div></div></td>
	<td></td>
	<td></td>
</tr>

<?php
	}
?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>	
	<td><a href="statistics.php?action=all">Total playtime:</a></td>
	<td></td>
	<td align="right"><?php echo formattedTime($total_miliseconds); ?></td>
	<td colspan="5"></td>
</tr>

<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>	
	<td>Total albums plays:</td>
	<td></td>
	<td align="right"><?php echo $total_played_albums; ?> times</td>
	<td colspan="5"></td>
</tr>

<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>	
	<td><a href="statistics.php?action=unique_played">Unique albums plays:</a></td>
	<td></td>
	<td align="right"><?php echo $unique_played_albums; ?></td>
	<td colspan="5"></td>
</tr>

<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>	
	<td><a href="statistics.php?action=not_played">Never played albums:</a></td>
	<td></td>
	<td align="right"><?php echo ($album['albums'] - $unique_played_albums); ?></td>
	<td colspan="5"></td>
</tr>

<?php 
	if ($cfg['access_admin']) { ?>

<tr class="header">
	<td></td>
	<td>Duplicate:</td>
	<td colspan="7"></td>
</tr>

<tr class="odd mouseover">
	<td></td>
	<td><a href="statistics.php?action=duplicateContent">Content:</a></td>
	<td></td>
	<td align="right"><?php echo (int) $duplicate_content; ?></td>
	<td colspan="5"></td>
</tr>
<tr class="even mouseover">
	<td></td>
	<td><a href="statistics.php?action=duplicateName">Name:</a></td>
	<td></td>
	<td align="right"><?php echo (int) $duplicate_name; ?></td>
	<td colspan="5"></td>
</tr>
<?php
	}
	$i = 0;
	$no_image_front			= mysqli_num_rows(mysqli_query($db,'SELECT album_id FROM bitmap WHERE image_front = ""'));
	$image_error			= mysqli_num_rows(mysqli_query($db,'SELECT album_id FROM bitmap WHERE flag = "10"'));
	$no_image_front_cover	= mysqli_num_rows(mysqli_query($db,'SELECT album_id FROM bitmap WHERE image_front_width * image_front_height < ' . $cfg['image_front_cover_treshold']));
	//$no_image_back_cover	= mysqli_num_rows(mysqli_query($db,'SELECT album_id FROM bitmap WHERE image_back = ""'));
	if ($cfg['access_admin'] && ($no_image_front > 0 || $no_image_front_cover > 0 || $no_image_back_cover > 0 || $image_error > 0)) { ?>

<tr class="header">
	<td></td>
	<td>No image:</td>
	<td colspan="7"></td>
</tr>

<?php
		if ($no_image_front > 0) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>
	<td><a href="statistics.php?action=noImageFront">No <b>image_front</b> file:</a></td>
	<td></td>
	<td align="right"><?php echo $no_image_front; ?></td>
	<td colspan="5"></td>
</tr>
<?php
		}
		if ($no_image_front_cover > 0) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>
	<td><a href="statistics.php?action=noImageFrontCover"><b>image_front</b> file has less then <?php echo $cfg['image_front_cover_treshold']; ?> px:</a></td>
	<td></td>
	<td align="right"><?php echo $no_image_front_cover; ?></td>
	<td colspan="5"></td>
</tr>
<?php
		}
		if ($image_error > 0) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>
	<td><a href="statistics.php?action=imageError">image error:</a></td>
	<td></td>
	<td align="right"><?php echo $image_error; ?></td>
	<td colspan="5"></td>
</tr>
<?php
		}
		/* if ($no_image_back_cover > 0) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>	
	<td><a href="statistics.php?action=noImageBackCover"><?php echo $cfg['image_back']; ?> for cover:</a></td>
	<td></td>
	<td align="right"><?php echo $no_image_back_cover; ?></td>
	<td colspan="5"></td>
</tr>
<?php
		} */
	}
	$error = mysqli_num_rows(mysqli_query($db,'SELECT error FROM track WHERE error != ""'));
	if ($cfg['access_admin'] && $error > 0) { ?>

<tr class="header">
	<td></td>
	<td>File:</td>
	<td colspan="9"></td>
</tr>

<tr class="odd mouseover">
	<td></td>
	<td><a href="statistics.php?action=fileError">Error:</a></td>
	<td></td>
	<td align="right"><?php echo $error; ?></td>
	<td colspan="5"></td>
</tr>
<?php
	}
	if ($cfg['access_admin'] == false) { ?>

<tr class="footer">
	<td></td>
	<td colspan="7">Other rows are only visible with administrator rights.</td>
	<td></td>
</tr>
<?php
	} ?>
</table>
<script>
$('#histMore').click(function(){
	$("[id^='incOfDiscs']").show();
	$("#histMoreRow").hide();
});
</script>
<?php
	$cfg['items_count'] = 0; //to avoid pagination on statistic page
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Duplicate content                                                      |
//  +------------------------------------------------------------------------+
function duplicateContent() {
	global $cfg, $db;
	authenticate('access_admin');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Media statistics';
	$nav['url'][]	= 'statistics.php';
	$nav['name'][]	= 'Duplicate content';
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<?php
	$i=0;
	$query = mysqli_query($db,'SELECT SUBSTRING_INDEX(track_id, "_", -1) AS hash, filesize, COUNT(SUBSTRING_INDEX(track_id, "_", -1)) AS n1, COUNT(filesize) AS n2
		FROM track
		GROUP BY filesize, hash
		HAVING n1 > 1 AND n2 > 1
		ORDER BY filesize');
	while ($track = mysqli_fetch_assoc($query)) {
		if ($i > 1) echo '<tr class="line"><td colspan="11"></td></tr>'; ?>
<tr class="header">
	<td class="space"></td>
	<td class="icon"></td><!-- optional play -->
	<td class="icon"></td><!-- optional add -->
	<td class="icon"></td><!-- optional stream -->
	<td<?php if ($cfg['access_play'] || $cfg['access_add'] || $cfg['access_stream']) echo' class="space"'; ?>></td>
	<td>Relative file</td>
	<td class="textspace"></td>
	<td align="right">Filesize</td>
	<td<?php if ($cfg['delete_file']) echo' class="space"'; ?>></td>
	<td></td><!-- optional delete -->
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="11"></td></tr>
<?php
	$hash = $track['hash'];
	$filesize = $track['filesize'];
	$i=0;
	$query2 = mysqli_query($db,'SELECT relative_file, miliseconds, track_id FROM track
		WHERE SUBSTRING_INDEX(track_id, "_", -1) = "' . mysqli_real_escape_string($db,$hash) . '"
		AND filesize = ' . (int) $filesize . '
		ORDER BY relative_file');
	while ($track = mysqli_fetch_assoc($query2)) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>
	<td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['track_id'] . '\');" onMouseOver="return overlib(\'Insert and play track\');" onMouseOut="return nd();"><i class="fa fa-play-circle-o fa-fw icon-small"></i></a>'; ?></td>
	<td><?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['track_id'] . '\');" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();"><i class="fa fa-plus-circle fa-fw icon-small"></i></a>';?></td>
	<td><?php if ($cfg['access_stream']) echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['track_id'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track\');" onMouseOut="return nd();"><i class="fa fa-rss fa-fw icon-small"></i></a>'; ?></td>
	<td></td>
	<td><?php echo html($track['relative_file']); ?></td>
	<td></td>
	<td align="right"><?php echo formattedSize($filesize); ?></td>
	<td></td>
	<td><?php if ($cfg['delete_file']) echo '<a href="statistics.php?action=deleteFile&amp;referer=statistics.php%3faction%3dduplicateContent&amp;relative_file=' . rawurlencode($track['relative_file']) . '&amp;sign=' . $cfg['sign'] . '" onClick="return confirm(\'Are you sure you want to delete: ' . addslashes(html($track['relative_file'])) . '?\');" onMouseOver="return overlib(\'Delete\');" onMouseOut="return nd();"><img src="' . $cfg['img'] . 'small_delete.png" alt="" class="small"></a>'; ?></td>
	<td></td>
</tr>
<?php
		}
	}
echo '</table>' . "\n";
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Duplicate name                                                         |
//  +------------------------------------------------------------------------+
function duplicateName() {
	global $cfg, $db;
	authenticate('access_admin');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Media statistics';
	$nav['url'][]	= 'statistics.php';
	$nav['name'][]	= 'Duplicate name';
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>Artist</td>
	<td class="textspace"></td>
	<td>Title</td>
	<td class="textspace"></td>
	<td>Count</td>
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="7"></td></tr>
<?php
	$i=0;
	$query = mysqli_query($db,'SELECT artist, title, COUNT(artist) AS n1, COUNT(title) AS n2
		FROM track
		GROUP BY artist, title
		HAVING n1 > 1
		AND n2 > 1
		ORDER BY artist, title');
	while ($track = mysqli_fetch_assoc($query)) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>
	<td><a href="statistics.php?action=duplicateFileName&amp;artist=<?php echo rawurlencode($track['artist']); ?>&amp;title=<?php echo rawurlencode($track['title']); ?>"><?php echo html($track['artist']); ?></a></td>
	<td></td>
	<td><a href="statistics.php?action=duplicateFileName&amp;artist=<?php echo rawurlencode($track['artist']); ?>&amp;title=<?php echo rawurlencode($track['title']); ?>"><?php echo html($track['title']); ?></a></td>
	<td></td>
	<td align="right"><?php echo (int) $track['n1']; ?></td>
	<td></td>
</tr>
<?php
	}
echo '</table>' . "\n";
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Duplicate file name                                                    |
//  +------------------------------------------------------------------------+
function duplicateFileName() {
	global $cfg, $db;
	authenticate('access_admin');
	
	$artist	 		= get('artist');
	$title			= get('title');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Media statistics';
	$nav['url'][]	= 'statistics.php';
	$nav['name'][]	= 'Duplicate name';
	$nav['url'][]	= 'statistics.php?action=duplicateName';
	$nav['name'][]	= $artist . ' - ' . $title;
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td class="icon"></td><!-- optional play -->
	<td class="icon"></td><!-- optional add -->
	<td class="icon"></td><!-- optional stream -->
	<td<?php if ($cfg['access_play'] || $cfg['access_add'] || $cfg['access_stream']) echo' class="space"'; ?>></td>
	<td>Relative file</td>
	<td class="textspace"></td>
	<td align="right">Filesize</td>
	<td<?php if ($cfg['delete_file']) echo' class="space"'; ?>></td>
	<td></td><!-- optional delete -->
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="11"></td></tr>
<?php
	$i=0;
	$query = mysqli_query($db,'SELECT relative_file, filesize, miliseconds, track_id FROM track
		WHERE artist	= "' . mysqli_real_escape_string($db,$artist) . '"
		AND title		= "' . mysqli_real_escape_string($db,$title) . '"
		ORDER BY relative_file');
	while ($track = mysqli_fetch_assoc($query)) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>
	<td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['track_id'] . '\');" onMouseOver="return overlib(\'Insert and play track\');" onMouseOut="return nd();"><i class="fa fa-play-circle-o fa-fw icon-small"></i></a>'; ?></td>
	<td><?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['track_id'] . '\');" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();"><i class="fa fa-plus-circle fa-fw icon-small"></i></a>';?></td>
	<td><?php if ($cfg['access_stream']) echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['track_id'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track\');" onMouseOut="return nd();"><i class="fa fa-rss fa-fw icon-small"></i></a>'; ?></td>
	<td></td>
	<td><?php echo html($track['relative_file']); ?></td>
	<td></td>
	<td align="right"><?php echo formattedSize($track['filesize']); ?></td>
	<td></td>
	<td><?php if ($cfg['delete_file']) echo '<a href="statistics.php?action=deleteFile&amp;referer=statistics.php%3faction%3dduplicateName&amp;relative_file=' . rawurlencode($track['relative_file']) . '&amp;sign=' . $cfg['sign'] . '" onClick="return confirm(\'Are you sure you want to delete: ' . addslashes(html($track['relative_file'])) . '?\');" onMouseOver="return overlib(\'Delete\');" onMouseOut="return nd();"><img src="' . $cfg['img'] . 'small_delete.png" alt="" class="small"></a>'; ?></td>
	<td></td>
</tr>
<?php
	}
echo '</table>' . "\n";
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | File error                                                             |
//  +------------------------------------------------------------------------+
function fileError() {
	global $cfg, $db;
	authenticate('access_admin');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Media statistics';
	$nav['url'][]	= 'statistics.php';
	$nav['name'][]	= 'File error';
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td<?php if ($cfg['access_play'] || $cfg['access_add'] || $cfg['access_stream']) echo' class="space"'; ?>></td>
	<td>Relative file</td>
	<td class="textspace"></td>
	<td>Error message</td>
	<td class="textspace"></td>
	<td align="right">Filesize</td>
	<td<?php if ($cfg['delete_file']) echo' class="space"'; ?>></td>
	<td></td><!-- optional delete -->
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="13"></td></tr>
<?php
	$i=0;
	$query = mysqli_query($db,'SELECT relative_file, filesize, error, track_id FROM track WHERE error != "" ORDER BY relative_file');
	while ($track = mysqli_fetch_assoc($query)) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>_error mouseover">
	<td></td>
	<td></td>
	<td><?php echo html($track['relative_file']); ?></td>
	<td></td>
	<td><?php echo html($track['error']); ?></td>
	<td></td>
	<td align="right"><?php echo formattedSize($track['filesize']); ?></td>
	<td></td>
	<td><?php if ($cfg['delete_file']) echo '<a href="statistics.php?action=deleteFile&amp;referer=statistics.php%3faction%3dfileError&amp;relative_file=' . rawurlencode($track['relative_file']) . '&amp;sign=' . $cfg['sign'] . '" onClick="return confirm(\'Are you sure you want to delete: ' . addslashes(html($track['relative_file'])) . '?\');" onMouseOver="return overlib(\'Delete\');" onMouseOut="return nd();"><img src="' . $cfg['img'] . 'small_delete.png" alt="" class="small"></a>'; ?></td>
	<td></td>
</tr>
<?php
	}
echo '</table>' . "\n";
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Delete file                                                            |
//  +------------------------------------------------------------------------+
function deleteFile() {
	global $cfg, $db;
	authenticate('access_admin', false, true, true);
	
	if ($cfg['delete_file'] == false)
		message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]Delete file disabled');
	
	$referer 		= get('referer');
	$relative_file	= get('relative_file');
	$file			= $cfg['media_dir'] . $relative_file;
	
	$query = mysqli_query($db,'SELECT relative_file
		FROM track
		WHERE relative_file	= BINARY "' . mysqli_real_escape_string($db,$relative_file) . '"');
	$track = mysqli_fetch_assoc($query);
	
	if ($track == false)
		message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]relative_file not found in database');
		
	if (is_file($file) && @unlink($file) == false)
		message(__FILE__, __LINE__, 'error', '[b]Failed to delete file:[/b][br]' . $file);
	
	mysqli_query($db,'DELETE FROM track 
		WHERE relative_file	= BINARY "' . mysqli_real_escape_string($db,$relative_file) . '"');
	
	cacheCleanup();
	
	header('Location: ' . NJB_HOME_URL . $referer);
	exit();
}
?>
