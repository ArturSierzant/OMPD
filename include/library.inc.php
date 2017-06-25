<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright � 2015-2016 Artur Sierzant                            |
//  | http://www.ompd.pl                                                     |
//  |                                                                        |
//  |                                                                        |
//  | netjukebox, Copyright � 2001-2012 Willem Bartels                       |
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
//  | Tile for album cover and info                                          |
//  +------------------------------------------------------------------------+
function draw_tile($size,$album,$multidisc = '') {
		global $cfg;
		if ($multidisc != '') {
			$md = '&md=' . $multidisc;
		}
		echo '<div title="Go to album" class="tile pointer" style="width: ' . $size . 'px; height: ' . $size . 'px;">';
		echo '<img onclick=\'location.href="index.php?action=view3&amp;album_id=' . $album['album_id'] . '"\' src="image.php?image_id=' . $album['image_id'] . '" alt="" width="100%" height="100%">';
		//echo '	<div id="tile_title" class="tile_info">';
		echo '	<div class="tile_info" style="cursor: initial;">';
		echo '	<div class="tile_title">' . html($album['album']) . '</div>';
		echo '	<div class="tile_band">' . html($album['artist_alphabetic']) . '</div>';
		if ($cfg['show_quick_play']) {
			echo '<div class="quick-play">';
			if ($cfg['access_add']) echo '<i id="add_' . $album['album_id'] . '" title="Add album to playlist"  onclick="javascript:ajaxRequest(\'play.php?action=updateAddPlay&album_id=' . $album['album_id'] .  '\',updateAddPlay);ajaxRequest(\'play.php?action=addSelect&album_id=' . $album['album_id'] . $md . '\',evaluateAdd);" class="fa fa-plus-circle pointer" style="padding-right: 5px;"></i>';
			if ($cfg['access_play']) echo '<i title="Play album" onclick="javascript: playAlbum(\'' . $album['album_id'] . '\',\'' . $multidisc . '\');" class="fa fa-play-circle-o pointer"></i>';
			echo '</div>';
		}		
			
		echo '</div>';
		echo '</div>';
}


//  +---------------------------------------------------------------------------+
//  | multiexplode by php at metehanarslan dot com                              |
//  +---------------------------------------------------------------------------+

function multiexplode ($delimiters,$string) {
    if (empty($delimiters)) {
        return array($string);
    }
    $ready = str_ireplace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return  $launch;
}		
		

//  +---------------------------------------------------------------------------+
//  | find core of track title                                                  |
//  +---------------------------------------------------------------------------+

function findCoreTrackTitle($title) {
	global $cfg;
	$title = strtolower($title);
	//$to_replace = array(',',';','.');
	//$title = str_replace($to_replace,'',$title);
	$separator = $cfg['separator'];
	$count = count($separator);
	$i=0;
	
	for ($i=0; $i<$count; $i++) {
		$pos = strpos($title,strtolower($separator[$i]));
		if ($pos !== false) {
			$title = trim(substr($title, 0 , $pos));
			//break;
		}
	}  
	
	return $title;

}

//  +---------------------------------------------------------------------------+
//  | Draws sub menu for add/remove to/from favorite                            |
//  +---------------------------------------------------------------------------+

function starSubMenu($i, $isFavorite, $isBlacklist, $track_id) {
	global $cfg, $db;
	$addFavorite_txt = ($isFavorite ? 'Remove from ' : 'Add to ');
	$addBlacklist_txt = ($isBlacklist ? 'Remove from ' : 'Add to ');
	?>
<div class="menuSubRight" id="menu-star-track<?php echo $i; ?>">

	
	<div>
		<span id="track_addToFavorite-<?php echo $track_id; ?>" class="icon-anchor">
			<span id="addToFavorite_txt-<?php echo $track_id; ?>"><?php echo $addFavorite_txt ?></span><?php echo $cfg['favorite_name'];?>
				<i id="save_favorite_star-<?php echo $track_id; ?>" class="fa fa-star<?php if (!$isFavorite) echo '-o' ?> fa-fw larger"></i>
		</span>
	</div>
	
	<div>
		<span id="track_addToBlacklist-<?php echo $track_id; ?>" class="icon-anchor">
			<span id="addToBlacklist_txt-<?php echo $track_id; ?>"><?php echo $addBlacklist_txt ?></span><?php echo $cfg['blacklist_name'];?>
				<span id="blacklist-star-bg-sub<?php echo $track_id; ?>" class="larger blackstar<?php if ($isBlacklist) echo ' blackstar-selected'; ?>"><i id="blacklist_star-<?php echo $i; ?>" class="fa fa-star-o fa-fw"></i></span>
		</span>
	</div>
	
		
	<div class="menuSubStarSave">
		Save as&nbsp;<input id="savePlaylistAsName-<?php echo $track_id; ?>"><span class="savePlaylistCol3"></span><br>
		Comment&nbsp;<input id="savePlaylistComment-<?php echo $track_id; ?>">
		<span id="playlistSaveAs-<?php echo $track_id; ?>"><i class="fa fa-floppy-o fa-fw"></i> Save</span>
	</div>
	
	<div class="menuSubStarAdd">
	Add to&nbsp;
		<select id="savePlaylistAddTo-<?php echo $track_id; ?>">
	<?php 
		echo listOfFavorites();
	?>
		</select>
		<span id="playlistAddTo-<?php echo $track_id; ?>"><i class="fa fa-plus-circle fa-fw"></i> Add</span>
	</div>


</div>
<?php 
}
	

	
//  +---------------------------------------------------------------------------+
//  | Draws sub menu for track                                                  |
//  +---------------------------------------------------------------------------+

function trackSubMenu($i, $track) {
	global $cfg, $db;
	if ($track['tid']) {
		$track['track_id'] = $track['tid']; // needed in search.php 'Track Artist'
	}
	$track['relative_file'] = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $track['relative_file']);
?>
<div class="menuSub" id="menu-sub-track<?php echo $i ?>" onclick='//offMenuSub(<?php echo $i ?>);'> 
	
	<div><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();"><i id = "insertPlay_' . $track['track_id'] . '" class="fa fa-play-circle fa-fw icon-small"></i>Insert after currently playing track and play</a>'; ?>
	</div>
	
	<div>
	<?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Insert track ' . $track['number'] . '\');" onMouseOut="return nd();"><i id="insert_' . $track['track_id'] . '" class="fa fa-indent fa-fw icon-small"></i>Insert after currently playing track</a>';?>
	</div>
	
	<div>
	<?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track ' . $track['number'] . '\');" onMouseOut="return nd();"><i id="add_' . $track['track_id'] . '" class="fa fa-plus-circle fa-fw icon-small"></i>Add track to playlist</a>';?>
	</div>
	
	<div><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();"><i id="play_' . $track['track_id'] . '" class="fa fa-play-circle-o fa-fw icon-small"></i>Remove all from playlist and play track</a>'; ?>
	</div>
	
	<div><?php if ($cfg['access_stream']) echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['track_id'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track ' . $track['number'] . '\');" onMouseOut="return nd();"><i class="fa fa-rss fa-fw icon-small"></i>Stream track</a>'; ?>
	</div>
	
	<!--
	<div><?php if ($cfg['access_stream']) {
		$playerQuery = mysqli_query($db, 'SELECT * FROM player ORDER BY player_name');
		$player1 = 11;
		$player2 = 'RasPi';
		echo '<a href="javascript:ajaxRequest(\'stream.php?action=playTo&amp;track_id=' . $track['track_id'] . '&amp;stream_id=' . $cfg['stream_id'] . '&amp;player_id=' . $player1 . '\',evaluatePlayTo)"><i id="playTo_' . $player1 . '" class="fa fa-fw  fa-share-square-o  icon-small"></i>Play to </a>';
		echo '<select id="selectSource">';
		while ($player = mysqli_fetch_assoc($playerQuery)) {
		?>
			<option value="<?php echo $player['player_id']; ?>"
			<?php if($cfg['player_id'] == $player['player_id']) {
				echo " selected";
			}?>
			><?php echo html($player['player_name']); ?></option>
		<?php
		}
		echo '</select>'; 
	}
	?>
	</div>
	-->
	
	<div><?php if ($cfg['access_download']) echo '<a href="download.php?action=downloadTrack&amp;track_id=' . $track['track_id'] .'&amp;download_id=' . $cfg['download_id'] . '" ' . onmouseoverDownloadTrack($track['track_id']) . '><i class="fa fa-download fa-fw icon-small"></i>Download track</a>'; ?>
	</div>
	
	<div><?php if ($cfg['access_play']) echo '<a href="getid3/demos/demo.browse.php?filename='. $cfg['media_dir'] . urlencode($track['relative_file']) . '" onClick="showSpinner();"><i class="fa fa-info-circle fa-fw icon-small"></i>File details</a>'; ?>
	</div>
	
</div>
<?php
}
	

	
//  +---------------------------------------------------------------------------+
//  | Draws sub menu for file                                                   |
//  +---------------------------------------------------------------------------+

function fileSubMenu($i, $filepath, $mime) {
	global $cfg, $db;
	
?>
<div class="menuSub" id="menu-sub-track<?php echo $i ?>" onclick='//offMenuSub(<?php echo $i ?>);'> 
	<div><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;filepath=' . $filepath . '&amp;track_id=' . $i . '\',evaluateAdd);" onMouseOver="return overlib(\'Play file\');" onMouseOut="return nd();"><i id = "insertPlay_' . $i . '" class="fa fa-play-circle fa-fw icon-small"></i>Insert after currently playing track and play</a>'; ?>
	</div>
	
	<div>
	<?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;filepath=' . $filepath . '&amp;track_id=' . $i . '\',evaluateAdd);" onMouseOver="return overlib(\'Insert file\');" onMouseOut="return nd();"><i id="insert_' . $i . '" class="fa fa-indent fa-fw icon-small"></i>Insert after currently playing track</a>';?>
	</div>
	
	<div>
	<?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;filepath=' . $filepath . '&amp;track_id=' . $i . '\',evaluateAdd);" onMouseOver="return overlib(\'Add file\');" onMouseOut="return nd();"><i id="add_' . $i . '" class="fa fa-plus-circle fa-fw icon-small"></i>Add file to playlist</a>';?>
	</div>
	
	<div><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;filepath=' . $filepath . '&amp;track_id=' . $i . '\',evaluateAdd);" onMouseOver="return overlib(\'Play file\');" onMouseOut="return nd();"><i id="play_' . $i . '" class="fa fa-play-circle-o fa-fw icon-small"></i>Remove all from playlist and play file</a>'; ?>
	</div>
	
	<div><?php if ($cfg['access_download']) echo '<a href="download.php?action=downloadFile&amp;filepath=' . $filepath .'&amp;mime=' . $mime . '"><i class="fa fa-download fa-fw icon-small"></i>Download file</a>'; ?>
	</div>
	<?php 
	
	$filepath = myUrldecode($filepath);
	?>
	<div><?php if ($cfg['access_play']) echo '<a href="getid3/demos/demo.browse.php?filename=' . $filepath . '" onClick="showSpinner();"><i class="fa fa-info-circle fa-fw icon-small"></i>File details</a>'; ?>
	</div>
	
</div>
<?php
}


//  +---------------------------------------------------------------------------+
//  | Draws sub menu for directory                                              |
//  +---------------------------------------------------------------------------+

function dirSubMenu($i, $dir) {
	global $cfg, $db;
	
	if(!isset($_COOKIE['random_limit'])) {
		$limit = $cfg['play_queue_limit'];
	} else {
		$limit = $_COOKIE['random_limit'];
	}
	
	$showUpdate = false;
	$pos = strpos($dir,$cfg['media_dir']);
	if ($pos !== false) {
		$showUpdate = true;
	}
	
	$dirpath = str_ireplace($cfg['media_dir'],'', $dir);
	//$dirpath = str_replace('%26','ompd_ampersand_ompd',urlencode($dirpath));
	//$dir = str_replace('%26','ompd_ampersand_ompd',urlencode($dir));
	$dirpath = myUrlencode($dirpath);
	$dir = myUrlencode($dir);
?>
<div class="menuSub" id="menu-sub-track<?php echo $i ?>" onclick='//offMenuSub(<?php echo $i ?>);'> 
	<?php if ($cfg['access_play']) {
		echo '<div><a href="javascript:ajaxRequest(\'play.php?dirpath=' . $dirpath . '&amp;fulldirpath=' . $dir . '&amp;action=playSelect&amp;id=' . $i .'\',evaluateAdd);" onMouseOver="return overlib(\'Play all files from this dir\');" onMouseOut="return nd();"><i id = "play_' . $i . '" class="fa fa-play-circle-o fa-fw icon-small"></i>Play all files from this dir</a></div>'; 
		}
		
		if ($cfg['access_play']) {
		echo '<div><a href="javascript:ajaxRequest(\'play.php?dirpath=' . $dirpath . '&amp;fulldirpath=' . $dir . '&amp;action=addSelect&amp;track_id=' . ($i - 100000) .'\',evaluateAdd);" onMouseOver="return overlib(\'Add all files from this dir\');" onMouseOut="return nd();"><i id = "add_' . ($i - 100000) . '" class="fa fa-plus-circle fa-fw icon-small"></i>Add all files from this dir</a></div>'; 
		}
		
		if ($cfg['access_play']) {
		echo '<div><a href="javascript:ajaxRequest(\'ajax-random-files.php?dir=' . $dir . '&amp;limit=' . $limit  . '&amp;id=' . $i .'\',evaluateRandom);" onMouseOver="return overlib(\'Play random files from this dir\');" onMouseOut="return nd();"><i id = "randomPlay_' . $i . '" class="fa fa-random fa-fw icon-small"></i>Play random files from this dir</a></div>'; 
		}
		
		if ($cfg['access_admin'] && $showUpdate) {
		echo '<div><a href="update.php?action=update&amp;dir_to_update=' . $dir . '/&amp;sign=' . $cfg['sign'] . '" onMouseOver="return overlib(\'Import this dir to database and MPD\');" onMouseOut="return nd();"><i id = "update_' . $i . '" class="fa fa-refresh fa-fw icon-small"></i>Update this directory in database and MPD</a></div>'; 
		}
	?>
	
</div>
<?php
}


//  +---------------------------------------------------------------------------+
//  | Draws sub menu for track moving/deleting                                  |
//  +---------------------------------------------------------------------------+

function moveSubMenu($i, $bottom) {
global $cfg, $db;
?>
<tr id="track-menu<?php echo $i; ?>">
	<td colspan="12">
		<div class="menuSubRight" id="menu-sub-track<?php echo $i ?>" onclick='offMenuSub(<?php echo $i ?>);'> 
		<div class="icon-anchor" id="track<?php echo $i; ?>_delete" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:showSpinner();ajaxRequest(\'play.php?action=deleteIndex&amp;index=' . $i . '&amp;menu=playlist\',evaluateListpos);"'; ?>>Remove <i class="fa fa-times-circle fa-fw icon-small"></i></div>
		<div class="icon-anchor" id="track<?php echo $i; ?>_move_top" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:moveTrack(0,' . $i . ',true);"'; ?>>Move to top<i class="fa fa-long-arrow-up fa-fw icon-small"></i></div>
		<div class="icon-anchor" id="track<?php echo $i; ?>_move" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:toggleInsert(\'on\',' . $i . ')"'; ?>>Move <i class="fa fa-arrows-v fa-fw icon-small"></i></div>
		<div class="icon-anchor" id="track<?php echo $i; ?>_move_bottom" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:moveTrack(' . $bottom . ',' . $i . ',false);"'; ?>>Move to bottom<i class="fa fa-long-arrow-down fa-fw icon-small"></i></div>
		</div>
		
	</td>
</tr>
<?php
};



//  +---------------------------------------------------------------------------+
//  | List of Favorites                                                         |
//  +---------------------------------------------------------------------------+

function listOfFavorites($file = true, $stream = true) {
	global $cfg, $db;
	if ($file) {
	$listOfFavorites = "
	<option class='listDivider' value='' selected disabled style='display: none;'>--- Select playlist ---</option>
	<option class='listDivider' value='' disabled>--- File playlists ---</option>";
		$query2 = mysqli_query($db,'SELECT name, favorite_id FROM favorite WHERE stream = 0 ORDER BY name');
		while ($player = mysqli_fetch_assoc($query2)) {
			$listOfFavorites .= "<option value=" . $player['favorite_id'] . ">" . html($player['name']) . "</option>";
		}
	}
	if ($stream) {
		$query2 = mysqli_query($db,'SELECT name, favorite_id FROM favorite WHERE stream = 1 ORDER BY name');
		if ($query2) {
			$listOfFavorites .= "<option class='listDivider' value='' disabled>--- Streams ---</option>";
			while ($player = mysqli_fetch_assoc($query2)) {
				$listOfFavorites .= "<option value='-" . $player['favorite_id'] . "'>" . html($player['name']) . "</option>";
			}
		}
	}
		return $listOfFavorites;
}



	
//  +------------------------------------------------------------------------+
//  | onMouseOver download album                                             |
//  +------------------------------------------------------------------------+
function onmouseoverDownloadAlbum($album_id) {
	global $cfg, $db;
	
	$filesize	= 0;
	$transcode	= false;
	$exact		= true;
	$extensions	= array();
	$query = mysqli_query($db, 'SELECT track.filesize, cache.filesize AS cache_filesize,
		miliseconds, audio_bitrate, track_id,
		LOWER(SUBSTRING_INDEX(track.relative_file, ".", -1)) AS extension
		FROM track LEFT JOIN cache
		ON track.track_id = cache.id
		AND cache.profile = ' . (int) $cfg['download_id'] . '
		WHERE album_id = "' . mysqli_real_escape_string($db, $album_id) . '"');
	if (mysqli_num_rows($query) == 1) {
		// By one track return onmouseoverDownloadTrack() info
		$track = mysqli_fetch_assoc($query);
		return onmouseoverDownloadTrack($track['track_id']);
	}
	while($track = mysqli_fetch_assoc($query)) {
		if (in_array($track['extension'], $extensions) == false)
			$extensions[] = $track['extension'];
		$transcode_track = false;
		if (sourceFile($track['extension'], $track['audio_bitrate'], $cfg['download_id']) == false) {
			$transcode_track	= true;
			$transcode			= true;
		}
		if ($track['cache_filesize'])
			$filesize += $track['cache_filesize'];
		elseif ($transcode_track) {
			$filesize += round($cfg['encode_bitrate'][$cfg['download_id']] * $track['miliseconds'] / 8 / 1000);
			$exact = false;
		}
		else
			$filesize += $track['filesize'];
	}
	
	sort($extensions);
	$source = implode($extensions, ', ');
	
	if ($exact)	$list = formattedSize($filesize);
	else		$list = html_entity_decode('&plusmn; ', null, NJB_DEFAULT_CHARSET) . formattedSize($filesize);
	
	$list .= '<div class=\'ol_line\'></div>';
	if ($transcode && count($extensions) == 1)		$list .= $cfg['encode_name'][$cfg['download_id']] . ' (' . $source . ' source)';
	elseif ($transcode && count($extensions) > 4)	$list .= $cfg['encode_name'][$cfg['download_id']] . ' (mixed source)';
	elseif ($transcode)								$list .= $cfg['encode_name'][$cfg['download_id']] . '<br>(' . $source . ' source)';
	else 											$list .= $source;
	$list .= '<div class=\'ol_line\'></div>';
	
	if ($transcode && $exact)		$list .= 'Transcoded:<img src=\'' . $cfg['img'] . 'tiny_check.png\' class=\'tiny\'><br>';
	elseif ($transcode && !$exact)	$list .= 'Transcoded:<img src=\'' . $cfg['img'] . 'tiny_uncheck.png\' class=\'tiny\'><br>';
	else							$list .= 'Source:<img src=\'' . $cfg['img'] . 'tiny_check.png\' class=\'tiny\'><br>';
	
	return 'onMouseOver="return overlib(\'' . addslashes(html($list)) . '\', CAPTION, \'Download album:\', WIDTH, 200);" onMouseOut="return nd();"';
}




//  +------------------------------------------------------------------------+
//  | strpos for arrays                                                      |
//  | source: http://stackoverflow.com/questions/6284553                     |
//  +------------------------------------------------------------------------+
function striposa($haystack, $needle, $offset=0) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $query) {
        if(stripos($haystack, $query, $offset) !== false) return $query; // stop on first true result
    }
    return false;
}



//  +------------------------------------------------------------------------+
//  | onMouseOver download track                                             |
//  +------------------------------------------------------------------------+
function onmouseoverDownloadTrack($track_id) {
	global $cfg, $db;
	$query = mysqli_query($db, 'SELECT
		LOWER(SUBSTRING_INDEX(relative_file, ".", -1)) AS extension,
		relative_file,
		miliseconds,
		filesize,
		audio_bitrate, audio_dataformat, audio_encoder, audio_profile, audio_bits_per_sample, audio_sample_rate, audio_channels,
		video_codec, video_resolution_x, video_resolution_y, video_framerate
		FROM track
		WHERE track_id = "' . mysqli_real_escape_string($db, $track_id) . '"');
	$track = mysqli_fetch_assoc($query);
	
	if (sourceFile($track['extension'], $track['audio_bitrate'], $cfg['download_id']))	$transcode = false;
	else																				$transcode = true;
	
	$list = '';
	if ($transcode) {
		$query = mysqli_query($db, 'SELECT filesize
			FROM cache 
			WHERE id		= "' . mysqli_real_escape_string($db, $track_id) . '"
			AND  profile	= "' . mysqli_real_escape_string($db, $cfg['download_id']) . '"');
		if ($cache = mysqli_fetch_assoc($query)) {
			$list .= formattedSize($cache['filesize']);
			$list .= '<div class=\'ol_line\'></div>';
			$list .= $cfg['encode_name'][$cfg['download_id']];
			$list .= ' (' . $track['extension'] . ' source)';
			$list .= '<div class=\'ol_line\'></div>';
			$list .= 'Transcoded:<img src=\'' . $cfg['img'] . 'tiny_check.png\' class=\'tiny\'>';
		}
		else {
			$list .= html_entity_decode('&plusmn; ', null, NJB_DEFAULT_CHARSET) . formattedSize($cfg['encode_bitrate'][$cfg['download_id']] * $track['miliseconds'] / 8 / 1000);
			$list .= '<div class=\'ol_line\'></div>';
			$list .= $cfg['encode_name'][$cfg['download_id']];
			$list .= ' (' . $track['extension'] . ' source)';
			$list .= '<div class=\'ol_line\'></div>';
			$list .= 'Transcoded:<img src=\'' . $cfg['img'] . 'tiny_uncheck.png\' class=\'tiny\'>';
		}
	}
	else {
		$list .= formattedSize($track['filesize']);
		$list .= '<div class=\'ol_line\'></div>';
	}
	
	if ($track['video_codec'] && $transcode == false) {
		$list .= $track['video_codec'] . '<br>';
		$list .= $track['video_resolution_x'] . 'x';
		$list .= $track['video_resolution_y'] . '<br>';
		$list .= $track['video_framerate'] . ' fps';
	}
	
	if ($track['audio_dataformat'] && $transcode == false) {
		if ($track['video_codec']) $list .= '<div class=ol_line></div>';
		$list .= $track['audio_dataformat'] . '<br>';
		$list .= $track['audio_encoder'] . '<br>';
		$list .= $track['audio_profile'];
		if		($track['audio_channels'] == 1)	$channels = 'Mono';
		elseif	($track['audio_channels'] == 2)	$channels = 'Stereo';
		else									$channels = $track['audio_channels'] . ' Channels';
		$list .= '<div class=\'ol_line\'></div>';
		$list .= $track['audio_bits_per_sample'] . ' bit | ' . $channels . ' | ' . formattedFrequency($track['audio_sample_rate']);
	}
	
	if ($transcode == false) {
		$list .= '<div class=\'ol_line\'></div>';
		$list .= 'Source:<img src=\'' . $cfg['img'] . 'tiny_check.png\' class=\'tiny\'>';
	}
	
	if (!$track['video_codec'] && !$track['audio_dataformat'] && $transcode == false)
		$list .= '-';
	
	return 'onMouseOver="return overlib(\'' . addslashes(html($list)) . '\', CAPTION, \'Download track:\', WIDTH, 200);" onMouseOut="return nd();"';
}




//  +------------------------------------------------------------------------+
//  | onMouseOver view cover                                                 |
//  +------------------------------------------------------------------------+
function onmouseoverViewCover($album_id) {
	global $cfg, $db;
	$query	= mysqli_query($db, 'SELECT image_front_width * image_front_height AS front_resolution, image_back
		FROM bitmap
		WHERE album_id = "' . mysqli_real_escape_string($db, $album_id) . '"');
	$bitmap = mysqli_fetch_assoc($query);
	$list = 'Front image:<img src="' . $cfg['img'] . 'tiny_' . ($bitmap['front_resolution'] >= $cfg['image_front_cover_treshold'] ? 'check' : 'uncheck') . '.png" alt="" class="tiny"><br>';
	$list .= 'Back image:<img src="' . $cfg['img'] . 'tiny_' . ($bitmap['image_back'] ? 'check' : 'uncheck') . '.png" alt="" class="tiny">';
	
	return 'onMouseOver="return overlib(\'' . addslashes(html($list)) . '\', CAPTION, \'View pdf cover:&nbsp;\');" onMouseOut="return nd();"';
}



//  +------------------------------------------------------------------------+
//  | Genre navigator                                                        |
//  +------------------------------------------------------------------------+
function genreNavigator($genre_id) {
	//echo 'id: ' . $genre_id;
	global $cfg, $db;
	$genre_seperation = '<span class="seperation">|</span>';
	if ($genre_id) {
		$nav			= array();
		$nav['name'][]	= 'Library';
		$nav['url'][]	= 'index.php';
	}
	else {
		$nav			= array();
		$nav['name'][]	= 'Library';
		$nav['url'][]	= '';
	}
	
	$query = mysqli_query($db, 'SELECT genre, genre_id
		FROM genre
		WHERE genre_id = "' . mysqli_real_escape_string($db, $genre_id) . '"
		ORDER BY genre');
	$genre = mysqli_fetch_assoc($query);
	if (substr($genre_id, -1) == '~') {
		$nav['name'][] = 'General';
		$nav['url'][]  = '';
	}
	if ($genre['genre']) {
		$nav['name'][] = $genre['genre'];
		$nav['url'][]  = '';
	}
	$nav['open'] = true;
	require_once('include/header.inc.php');

}


//  +------------------------------------------------------------------------+
//  | Execution time                                                         |
//  +------------------------------------------------------------------------+
function executionTime() {
	$miliseconds = round((microtime(true) - NJB_START_TIME) * 1000);
	$seconds = round($miliseconds / 1000);
	
	if ($miliseconds < 1000)	return $miliseconds . ' ms';
	if ($seconds < 60)			return $seconds . ' seconds';
								return formattedTime($miliseconds);
}




//  +------------------------------------------------------------------------+
//  | Formatted time                                                         |
//  +------------------------------------------------------------------------+
function formattedTime($miliseconds) {
	$seconds 	= round($miliseconds / 1000);
	$hours		= floor($seconds / 3600);
	$minutes 	= floor($seconds / 60) % 60;
	$seconds 	= $seconds % 60;
	
	if ($hours >= 48)		return '(' . floor($hours / 24) . ' days) ' . $hours . ':' . sprintf('%02d:%02d', $minutes, $seconds);
	elseif ($hours >= 24)	return '(' . floor($hours / 24) . ' day) ' . $hours . ':' . sprintf('%02d:%02d', $minutes, $seconds);
	elseif ($hours > 0)		return $hours . ':' . sprintf('%02d:%02d', $minutes, $seconds);
							return $minutes . sprintf(':%02d', $seconds);
}




//  +------------------------------------------------------------------------+
//  | Formatted size                                                         |
//  +------------------------------------------------------------------------+
function formattedSize($filesize) {
	$weight = array('bytes', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
	
	for ($i = 0; $filesize >= 1024; $i++)
		$filesize /= 1024;
	
	if ($i == 0)		return (int) $filesize . ' ' . $weight[$i];
						return number_format($filesize, 2, '.', '') . ' ' . $weight[$i];
}




//  +------------------------------------------------------------------------+
//  | Formatted bitrate                                                      |
//  +------------------------------------------------------------------------+
function formattedBirate($bitrate) {
	$weight = array('bps', 'kbps', 'Mbps', 'Gbps', 'Tbps', 'Pbps', 'Ebps', 'Zbps', 'Ybps');
	
	for ($i = 0; $bitrate >= 1000; $i++)
		$bitrate /= 1000;
	
	return round($bitrate) . ' ' . $weight[$i];
}




//  +------------------------------------------------------------------------+
//  | Formatted frequency                                                    |
//  +------------------------------------------------------------------------+
function formattedFrequency($frequency) {
	$weight = array('Hz', 'kHz', 'MHz', 'GHz', 'THz', 'PHz', 'EHz', 'ZHz', 'YHz');
	
	for ($i = 0; $frequency >= 1000; $i++)
		$frequency /= 1000;
	
	return number_format($frequency, 1) . ' ' . $weight[$i];
}




//  +------------------------------------------------------------------------+
//  | Formatted date                                                         |
//  +------------------------------------------------------------------------+
function formattedDate($year = NULL, $month = NULL, $day = NULL) {
	$date = '';
	if (isset($day))	$date .= str_pad($day, 2, 0, STR_PAD_LEFT) . '&nbsp;';
	if (isset($month))	$date .= formattedMonth($month) . '&nbsp;';
	if (isset($year))	$date .= $year;
	
	return $date;
}




//  +------------------------------------------------------------------------+
//  | Formatted month                                                        |
//  +------------------------------------------------------------------------+
function formattedMonth($number) {
	$month = array(1 =>	'January', 'February', 'March', 'April', 'May', 'June',
					'July', 'August', 'September', 'October', 'November', 'December');
	
	return $month[$number];
}




//  +------------------------------------------------------------------------+
//  | HTML                                                                   |
//  +------------------------------------------------------------------------+
function html($string) {
	return htmlspecialchars($string, ENT_SUBSTITUTE, NJB_DEFAULT_CHARSET);
}




//  +------------------------------------------------------------------------+
//  | Safe JSON encode                                                       |
//  +------------------------------------------------------------------------+
function safe_json_encode($data) {
	if (NJB_DEFAULT_CHARSET == 'UTF-8' && version_compare(PHP_VERSION, '5.4.0', '>='))
		return json_encode($data, JSON_UNESCAPED_UNICODE); 
	elseif (NJB_DEFAULT_CHARSET == 'UTF-8')
		return json_encode($data);
	else
		return json_encode(recursive_iconv_to_utf8($data));
}




//  +------------------------------------------------------------------------+
//  | Recursive iconv to utf8                                                |
//  +------------------------------------------------------------------------+
function recursive_iconv_to_utf8($data) {
	if (is_string($data)) return iconv(NJB_DEFAULT_CHARSET, 'UTF-8', $data);
	if (!is_array($data)) return $data;
	
	$data = array_map('recursive_iconv_to_utf8', $data);
	
	return $data;
}




//  +------------------------------------------------------------------------+
//  | Escape CMD arg                                                         |
//  +------------------------------------------------------------------------+
function escapeCmdArg($arg) {
	if (NJB_WINDOWS) {
		// No need to escape " because this symbol isn't used by Windows
		return '"' . str_replace('/', '\\', $arg) . '"';
	}
	else {
		// Didn't use escapeshellarg() because of problems with UTF8
		// Thanks Marc Maurice: http://en.positon.org/post/PHP-escapeshellarg-function-UTF8-and-locales
		return "'" . str_replace("'", "'\\''", $arg) . "'";
	}
}




//  +------------------------------------------------------------------------+
//  | Encode escape character                                                |
//  +------------------------------------------------------------------------+
function encodeEscapeChar($filename) {
	global $cfg;
	
	foreach ($cfg['escape_char'] as $key => $value)
		$filename = str_replace($key, $value, $filename); // Example: ? to %3F
	
	return $filename;
}




//  +------------------------------------------------------------------------+
//  | Decode escape character                                                |
//  +------------------------------------------------------------------------+
function decodeEscapeChar($filename) {
	global $cfg;
	
	foreach ($cfg['escape_char'] as $key => $value)
		$filename = str_replace($value, $key, $filename); // Example: %3F to ?
	
	return $filename;
}




//  +------------------------------------------------------------------------+
//  | Download filename                                                      |
//  +------------------------------------------------------------------------+
function downloadFilename($filename, $client_compatible = true, $server_compatible = false) {
	global $cfg;
	
	// Decode filename
	$filename = decodeEscapeChar($filename); // Example: %3F to ?
	
	// Encode for client compatibility
	if ($client_compatible)	{
		foreach ($cfg['client_char_limit'] as $regex => $value)	{
			if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
				foreach ($cfg['client_char_limit'][$regex] as $key => $value)
					$filename = str_replace($value, $cfg['escape_char'][$value], $filename); // Example: ? to %3F
				break;
			}
		}
	}
	
	// Encode for server compatibility
	if ($server_compatible)	{
		foreach ($cfg['server_char_limit'] as $regex => $value)	{
			if (preg_match($regex, PHP_OS)) {
				foreach ($cfg['server_char_limit'][$regex] as $key => $value)
					$filename = str_replace($value, $cfg['escape_char'][$value], $filename); // Example: ? to %3F
				break;
			}
		}
	}
	
	return $filename;
}




//  +------------------------------------------------------------------------+
//  | Copy filename                                                          |
//  +------------------------------------------------------------------------+
function copyFilename($filename) {
	global $cfg;
	
	// Decode filename
	$filename = decodeEscapeChar($filename); // Example: %3F to ?
	
	// Encode for compatibility
	foreach ($cfg['album_copy_char_limit'] as $key => $value)
		$filename = str_replace($value, $cfg['escape_char'][$value], $filename); // Example: ? to %3F

	return $filename;
}




//  +------------------------------------------------------------------------+
//  | bbcode                                                                 |
//  +------------------------------------------------------------------------+
function bbcode($string) {
	global $cfg;
	$bbcode = array(
		'#\[br\]#s',
		'#\[b\](.*?)\[\/b\]#s',
		'#\[i\](.*?)\[\/i\]#s',
		'#\[img\]([a-z_]+\.png)\[\/img\]#s',
		'#\[url=([a-z]+\.php(?:\?.*)?)\](.*?)\[\/url\]#s',
		'#\[url\]((?:http|https|ftp)://.*?)\[\/url\]#s',
		'#\[url=((?:http|https|ftp)://.*?)\](.*?)\[\/url\]#s',
		'#\[email\]([a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4})\[\/email\]#si');
	$replace = array(
		'<br>',
		'<strong>$1</strong>',
		'<em>$1</em>',
		'<img src="' . $cfg['img'] . '$1" alt="" class="small space">',
		'<a href="$1">$2</a>',
		'<a href="$1">$1</a>',
		'<a href="$1">$2</a>',
		'<a href="mailto:$1">$1</a>');
	
	$string = html($string);
	$string = preg_replace($bbcode, $replace, $string);
	$string = preg_replace_callback('#\[list\](.*?)\[\/list\]#s', 'bblist', $string);
	
	return $string;
}




//  +------------------------------------------------------------------------+
//  | bbcode list                                                            |
//  +------------------------------------------------------------------------+	
function bblist($maches) {
	$list = '';
	$list_array = explode('[*]', $maches[1]);
	foreach ($list_array as $key => $value)	{
		if ($key == 0) $list .= $value;
		else $list .= '<li>' . $value . '</li>';
	}
	
	return '<ul>' . $list . '</ul>';
}




//  +------------------------------------------------------------------------+
//  | bbcode to txt                                                          |
//  +------------------------------------------------------------------------+
function bbcode2txt($string) {
	$bbcode = array(
		"#\r\n|\n|\r#",
		'#\[br\]#s',
		'#\[b\](.*?)\[\/b\]#s',
		'#\[i\](.*?)\[\/i\]#s',
		'#\[list\](.*?)\[\/list\]#s',
		'#\[\*\]#s',
		'#\[img\]([a-z_]+\.png)\[\/img\]#s',
		'#\[url=([a-z]+\.php(?:\?.*)?)\](.*?)\[\/url\]#s',
		'#\[url\]((?:http|https|ftp)://.*?)\[\/url\]#s',
		'#\[url=((?:http|https|ftp)://.*?)\](.*?)\[\/url\]#s',
		'#\[email\]([a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4})\[\/email\]#si');
	
	$replace = array(
		'',
		"\n",
		'$1',
		'$1',
		'$1',
		"\n* ",
		'',
		'$2 <$1>',
		'<$1>',
		'$2 <$1>',
		'$1');
	
	return preg_replace($bbcode, $replace, $string);
}




//  +------------------------------------------------------------------------+
//  | onMouseOver bbcode reference                                           |
//  +------------------------------------------------------------------------+
function onmouseoverBbcodeReference() {
	$list = '[br]<br>';
	$list .= '[b]bold[/b]<br>';
	$list .= '[i]italic[/i]<br>';
	$list .= '[img]small_back.png[/img]<br>';
	$list .= '[url]http://www.example.com[/url]<br>';
	$list .= '[url=http://www.example.com]example[/url]<br>';
	$list .= '[email]info@example.com[/email]<br>';
	$list .= '[list][*]first[*]second[/list]';
	
	return 'onMouseOver="return overlib(\'' . addslashes(html($list)) . '\', CAPTION, \'BBCode reference:\');" onMouseOut="return nd();"';
}




//  +------------------------------------------------------------------------+
//  | onMouseOver image                                                      |
//  +------------------------------------------------------------------------+
function onmouseoverImage($image_id) {
	$image =  '<img src="image.php?image_id=' . rawurlencode($image_id) . '" alt="" width="50" height="50" border="0">';
	
	return 'onMouseOver="return overlib(\'' . addslashes(html($image)) . '\', CELLPAD, 0);" onMouseOut="return nd();"';
}




//  +------------------------------------------------------------------------+
//  | onMouseOver access info                                                |
//  +------------------------------------------------------------------------+
function onmouseoverAccessInfo($access) {
	switch ($access) {
		case 'media':		$info = 'View media';						break;
		case 'popular':		$info = 'View popular albums';				break;
		case 'favorite':	$info = 'View favorites';					break;
		case 'cover':		$info = 'Download pdf cover';				break;
		case 'stream':		$info = 'Stream media';						break;
		case 'download':	$info = 'Download media';					break;
		case 'playlist':	$info = 'View playlist';					break;
		case 'play':		$info = 'Play media';						break;
		case 'add':			$info = 'Add media to playlist';			break;
		case 'record':		$info = 'Allow access to files outside of music library';		break;
		case 'statistics':	$info = 'View media statistics';			break;
		case 'admin':		$info = 'Administrator';					break;
	}
	
	return 'onMouseOver="return overlib(\'' . addslashes(html($info)) .'\');" onMouseOut="return nd();"';
}




//  +------------------------------------------------------------------------+
//  | Random key                                                             |
//  +------------------------------------------------------------------------+
function randomKey() {
	$key = substr(randomHex(), 0, 30);
	$key .= substr(randomHex(), 0, 30);
	$key = base64_encode(pack('H*', $key));
	$key = str_replace('+', '-', $key); // modified Base64 for URL
	$key = str_replace('/', '_', $key);
	
	return $key;
}




//  +------------------------------------------------------------------------+
//  | Random hex                                                             |
//  +------------------------------------------------------------------------+
function randomHex() {
	ob_start();
	phpinfo();
	$data = ob_get_contents();
	ob_end_clean();

	return hmacsha1(uniqid('', true), $data);
}




//  +------------------------------------------------------------------------+
//  | HMAC MD5                                                               |
//  +------------------------------------------------------------------------+
function hmacmd5($key, $data, $raw = false) {
	if (function_exists('hash_hmac'))
		return hash_hmac('md5', $data, $key, $raw);
	
	$blocksize = 64;
	if (strlen($key) > $blocksize)
		$key = md5($key, true);
	
	$key	= str_pad($key, $blocksize, chr(0x00));
	$ipad	= str_repeat(chr(0x36), $blocksize);
	$opad	= str_repeat(chr(0x5c), $blocksize);
	
	return md5(($key^$opad) . md5(($key^$ipad) . $data, true), $raw);
}




//  +------------------------------------------------------------------------+
//  | HMAC SHA-1                                                             |
//  +------------------------------------------------------------------------+
function hmacsha1($key, $data, $raw = false) {
	if (function_exists('hash_hmac'))
		return hash_hmac('sha1', $data, $key, $raw);
	
	$blocksize = 64;
	if (strlen($key) > $blocksize)
		$key = sha1($key, true);
	
	$key	= str_pad($key, $blocksize, chr(0x00));
	$ipad	= str_repeat(chr(0x36), $blocksize);
	$opad	= str_repeat(chr(0x5c), $blocksize);
	
	return sha1(($key^$opad) . sha1(($key^$ipad) . $data, true), $raw);
}




//  +------------------------------------------------------------------------+
//  | Filemtime compare                                                      |
//  +------------------------------------------------------------------------+
function filemtimeCompare($filemtime1, $filemtime2) {
	if ($filemtime1 == $filemtime2) return true;
	if (NJB_WINDOWS && $filemtime1 == $filemtime2 + 3600) return true;
	if (NJB_WINDOWS && $filemtime1 == $filemtime2 - 3600) return true;
	
	return false;
}




//  +------------------------------------------------------------------------+
//  | Source file                                                            |
//  +------------------------------------------------------------------------+
function sourceFile($extension, $bitrate, $id) {
	global $cfg;
	if ($id == -1 ||
		array_key_exists($extension, $cfg['decode_stdout']) == false ||
		$extension == $cfg['encode_extension'][$id] &&
		$bitrate <= round($cfg['encode_bitrate'][$id] * $cfg['transcode_treshold'] / 100))
		return true;
	else
		return false;
}




//  +------------------------------------------------------------------------+
//  | Validate skin                                                          |
//  +------------------------------------------------------------------------+
function validateSkin($skin) {
	$dir = NJB_HOME_DIR . 'skin/' . $skin . '/';
	
	if (file_exists($dir . 'styles.css') &&
		file_exists($dir . 'template.footer.php') &&
		file_exists($dir . 'template.header.php') &&
		$dir == str_replace('\\', '/', realpath($dir)) . '/')
		return true;
	else
		return false;
}




//  +------------------------------------------------------------------------+
//  | Update counter: play / add / stream / download / cover                 |
//  +------------------------------------------------------------------------+
function updateCounter($album_id, $flag){
	global $cfg, $db;
	// flag 0 = play/add
	// flag 1 = stream
	// flag 2 = download
	// flag 3 = cover
	// flag 4 = record
	
	$query = mysqli_query($db, 'SELECT time FROM counter
		WHERE album_id	= "' . mysqli_real_escape_string($db, $album_id) . '"
		AND sid			= BINARY "' . mysqli_real_escape_string($db, $cfg['sid']) . '"
		AND flag		= ' . (int) $flag . '
		ORDER BY time DESC LIMIT 1');
	$counter = mysqli_fetch_assoc($query);
	$counter_time = $counter['time'];
	
	if ($counter_time + 60 - time() < 0) {
		mysqli_query($db, 'INSERT INTO counter (sid, album_id, user_id, flag, time) VALUES (
			"' . mysqli_real_escape_string($db, $cfg['sid']) . '",
			"' . mysqli_real_escape_string($db, $album_id) . '",
			' . (int) $cfg['user_id'] . ',
			' . (int) $flag . ',
			' . (int) time() . ')');
	}
	else {
		mysqli_query($db, 'UPDATE counter
			SET time = 			' . (int) time() . '
			WHERE album_id = 	"' . mysqli_real_escape_string($db, $album_id) . '"
			AND sid =			BINARY "' . mysqli_real_escape_string($db, $cfg['sid']) . '"
			AND flag =			' . (int) $flag . ',
			AND time =			' . (int) $counter_time);
	}
}




//  +------------------------------------------------------------------------+
//  | Create hiden dir                                                       |
//  +------------------------------------------------------------------------+
function createHiddenDir($dir) {
	$file = $dir . 'index.php';
	$content = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><title></title></head><body></body></html>';
	
	if (is_dir($dir) == false && @mkdir($dir, 0777) == false)
		message(__FILE__, __LINE__, 'error', '[b]Failed to create directory:[/b][br]' . $dir);
	
	if (@filesize($file) != strlen($content) && file_put_contents($file, $content) === false)
		message(__FILE__, __LINE__, 'error', '[b]Failed to create file:[/b][br]' . $file);
}




//  +------------------------------------------------------------------------+
//  | Recursive rmdir                                                        |
//  +------------------------------------------------------------------------+
function rrmdir($dir) {
	if (is_dir($dir)) {
		$entries = scandir($dir);
		foreach ($entries as $entry) {
			if ($entry != '.' && $entry != '..') {
				if (is_dir($dir . $entry . '/'))	@rrmdir($dir . $entry . '/');
				else								@unlink($dir . $entry) or message(__FILE__, __LINE__, 'error', '[b]Failed to delete file:[/b][br]' . $dir . $entry);
			}
		}
		rmdir($dir) or message(__FILE__, __LINE__, 'error', '[b]Failed to delete directory:[/b][br]' . $dir);
	}
} 




//  +------------------------------------------------------------------------+
//  | Do some actions in the background to speed up statistics results       |
//  +------------------------------------------------------------------------+
function backgroundQueries(){
global $cfg, $db;
	$query = mysqli_query($db,'SELECT artist FROM album GROUP BY artist');
	
	$query = mysqli_query($db,'SELECT COUNT(discs) AS albums, SUM(discs) AS discs FROM album');
		
	$query = mysqli_query($db,'SELECT COUNT(relative_file) AS all_tracks,
		SUM(miliseconds) AS sum_miliseconds,
		SUM(filesize) AS sum_size
		FROM track');
		
	$query = mysqli_query($db,'SELECT
		SUM(filesize) AS sum_size
		FROM cache');
		
	$query = mysqli_query($db,'SELECT artist, title, COUNT(artist) AS n1, COUNT(title) AS n2
		FROM track
		GROUP BY artist, title
		HAVING n1 > 1 AND n2 > 1');
	
	$query = mysqli_query($db,'SELECT COUNT(*) as played FROM counter');
	
	$query = mysqli_query($db,'SELECT COUNT(c.album_id) as played FROM (SELECT DISTINCT album_id FROM counter) as c');
	
	$query = mysqli_query($db,'SELECT SUBSTRING_INDEX( track_id, "_", -1 ) AS hash, filesize, COUNT( SUBSTRING_INDEX( track_id, "_", -1 ) ) AS n1, COUNT( filesize ) AS n2
	FROM track
	GROUP BY filesize, hash
	HAVING n1 > 1 AND n2 > 1');
	
	$query = mysqli_query($db,'SELECT audio_dataformat FROM track WHERE audio_dataformat != "" AND video_dataformat = "" GROUP BY audio_dataformat ORDER BY audio_dataformat');
}




//  +------------------------------------------------------------------------+
//  | HTMLencode &, ', "                                                     |
//  +------------------------------------------------------------------------+

function myHTMLencode($str1){
	
	$str1 = str_replace('ompd_ampersand_ompd','&',$str1);
	//$str1 = str_replace("'","&apos;",$str1);
	//$str1 = str_replace('"',"&quot;",$str1);
	$str1 = htmlentities($str1, ENT_QUOTES);
	
	return $str1;
}



//  +------------------------------------------------------------------------+
//  | Urlencode &, ', "                                                      |
//  +------------------------------------------------------------------------+

function myUrlencode($str1){
	
	$str1 = str_replace('\\','\\\\',$str1);
	$str1 = str_replace('+','ompd_plus_ompd',$str1);
	$str1 = str_replace('%26','ompd_ampersand_ompd',urlencode($str1));
	$str1 = str_replace('%22','%5C%22',$str1);
	$str1 = str_replace('%27','%5C%27',$str1);
	
	return $str1;
}



//  +------------------------------------------------------------------------+
//  | Urldecode &, ', "                                                      |
//  +------------------------------------------------------------------------+

function myUrldecode($str1){
	
	$str1 = str_replace('ompd_ampersand_ompd','%26',$str1);
	$str1 = str_replace('%5C%22','%22',$str1);
	$str1 = str_replace('%5C%27','%27',$str1);
	$str1 = str_replace("ompd_plus_ompd","%2B",$str1);
	
	return $str1;
}




//  +------------------------------------------------------------------------+
//  | Decode &, ', "                                                         |
//  +------------------------------------------------------------------------+

function myDecode($str1){
	
	$str1 = str_replace('ompd_ampersand_ompd','&',$str1);
	$str1 = str_replace('\"','"',$str1);
	$str1 = str_replace("\'","'",$str1);
	$str1 = str_replace("ompd_plus_ompd","+",$str1);
	
	return $str1;
}




//  +------------------------------------------------------------------------+
//  | Escape ", \ for use in MPD command                                     |
//  +------------------------------------------------------------------------+

function mpdEscapeChar($str1){
	
	$str1 = str_replace('\\','\\\\',$str1);
	$str1 = str_replace('"','\"',$str1);
	
	return $str1;
}




//  +------------------------------------------------------------------------+
//  | mime_content_type replacement by svogal:                               |
//  | http://php.net/manual/en/function.mime-content-type.php#87856          |
//  +------------------------------------------------------------------------+


function mime_content_type_replacement($filename) {

	$mime_types = array(

		// images
		'png' => 'image/png',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'gif' => 'image/gif',
		'bmp' => 'image/bmp',
		'ico' => 'image/vnd.microsoft.icon',
		'tiff' => 'image/tiff',
		'tif' => 'image/tiff',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',

		// audio/video
		'aif' => 'audio/aiff',
		'mp3' => 'audio/mpeg',
		'mp2' => 'audio/mpeg',
		'mpc' => 'audio/mpeg',
		'ogg' => 'audio/ogg', 
		'oga' => 'audio/ogg', 
		'ape' => 'audio/ape', 
		'dsf' => 'audio/dsf', 
		'flac' => 'audio/flac', 
		'wv' => 'audio/wv', 
		'wav' => 'audio/wav', 
		'wma' => 'audio/x-ms-wma',
		'aac' => 'audio/aac',
		'm4a' => 'audio/m4a',
		'm4b' => 'audio/m4b'
	);

	$ext = strtolower(array_pop(explode('.',$filename)));
	if (array_key_exists($ext, $mime_types)) {
		return $mime_types[$ext];
	}
	/* elseif (function_exists('finfo_open')) {
		$finfo = finfo_open(FILEINFO_MIME);
		$mimetype = finfo_file($finfo, $filename);
		finfo_close($finfo);
		return $mimetype;
	} */
	else {
		return 'not_allowed';
	}
}

//  +------------------------------------------------------------------------+
//  | find_all_files by  kodlee at kodleeshare dot net                       |
//  | http://php.net/manual/en/function.scandir.php#107117                   |
//  +------------------------------------------------------------------------+

function find_all_files($dir){
  global $cfg;
	$dir = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir);
	$root = @scandir($dir);
    foreach($root as $value)
    {
        if($value === '.' || $value === '..' || in_array($value, $cfg['directory_blacklist']) === true) {continue;}
        if(is_file("$dir/$value")) {
			$ext = pathinfo($value, PATHINFO_EXTENSION);
			if (in_array($ext,$cfg['media_extension'])) {
				$result[] = "$dir/$value";
			}
			continue;
		}
        foreach(find_all_files("$dir/$value") as $value)
        {
            $result[] = $value;
        }
    }
	
	array_walk(
		$result,
		function (&$entry) {
			$entry = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $entry);
		}
	);

    return $result;
} 




//  +------------------------------------------------------------------------+
//  | Gropuping of multidisc albums                                          |
//  +------------------------------------------------------------------------+

function albumMultidisc($query){
	global $cfg, $db;
	$album_multidisc = array();
	while ($album = mysqli_fetch_assoc($query)) {		
		$multidisc_count = 0;
		if ($album) {
			if ($cfg['group_multidisc'] == true) {
				$md_indicator = striposa($album['album'], $cfg['multidisk_indicator']);
				if ($md_indicator !== false) {
					$md_ind_pos = stripos($album['album'], $md_indicator);
					$md_title = substr($album['album'], 0, $md_ind_pos);
					$query_md = mysqli_query($db, 'SELECT album, image_id, album_id 
					FROM album 
					WHERE album LIKE "' . mysqli_real_escape_string($db, $md_title) . '%" AND artist = "' . mysqli_real_escape_string($db, $album['artist']) . '" AND album <> "' . mysqli_real_escape_string($db, $album['album']) . '"
					ORDER BY album');
					$multidisc_count = mysqli_num_rows($query_md);
				}
			}
			
			if ($multidisc_count > 0) {
				if (!in_array($md_title, $mdTab)) {
					$mdTab[] = $md_title;
					$album_multidisc[$album['album_add_time'] . '_' . $album['album_id']] = array(
					'album_id' => $album['album_id'],
					'image_id' => $album['image_id'],
					'album' => $album['album'],
					'artist_alphabetic' => $album['artist_alphabetic'],
					'year' => $album['year'],
					'allDiscs' => 'allDiscs'
					);
				}
			}
			else {			
				$album_multidisc[$album['album_add_time'] . '_' . $album['album_id']] = array(
				'album_id' => $album['album_id'],
				'image_id' => $album['image_id'],
				'album' => $album['album'],
				'artist_alphabetic' => $album['artist_alphabetic'],
				'year' => $album['year'],
				'allDiscs' => ''
				);
			}
		}
	}
	//krsort($album_multidisc);
	$cfg['items_count'] = count($album_multidisc);
	return $album_multidisc;
}



//  +------------------------------------------------------------------------+
//  | Update genre table and genre in album table                            |
//  +------------------------------------------------------------------------+
function updateGenre() {
	global $cfg, $db;
	$i = 1;
	mysqli_query($db,'TRUNCATE genre');
	$query = mysqli_query($db,'SELECT genre FROM track WHERE genre<>"" GROUP BY genre ORDER BY genre');
	$genre_count = mysqli_num_rows($query);
	if ($genre_count > 0) {
		while ($track = mysqli_fetch_assoc($query)) {
			$genres = explode('ompd_genre_ompd',$track['genre']);
			foreach ($genres as $g){
				$q = mysqli_query($db,'SELECT genre_id FROM genre WHERE genre="' . $g . '"');
				if (mysqli_num_rows($q) == 0) {
					mysqli_query($db,'INSERT INTO genre (genre_id, genre, updated)
										VALUES ("' . $i . '",
												"' . $db->real_escape_string($g) . '",
												1)');
					++$i;
				}
			}
		}
	}
	$res = mysqli_query($db,"UPDATE album SET genre_id = ';'"); 
	
	$query = mysqli_query($db,'SELECT DISTINCT genre, album_id FROM track');
	while ($album = mysqli_fetch_assoc($query)) { 
			$genres = explode('ompd_genre_ompd',$album['genre']);
			foreach ($genres as $g){
				//get genre_id for actual genre from multigenre
				$q1 = mysqli_query($db,'SELECT genre_id FROM genre WHERE genre = "' . $g . '"');
				$a1 = mysqli_fetch_assoc($q1);
				
				//get all genre_ids from album
				$q2 = mysqli_query($db,'SELECT genre_id FROM album WHERE album_id = "' . $album['album_id'] . '"');
				$a2 = mysqli_fetch_assoc($q2);
				$album_genre_ids = $a2['genre_id'];
				
				//check if genre_id is already added to album
				if ($album_genre_ids == ';') { //no genre_id yet -> add first now
					$album_genre_ids = ';' . $a1['genre_id'] . ';';
				}
				elseif (strpos($album_genre_ids,';' . $a1['genre_id'] . ';') === false) {
						//genre_id not added to album yet -> add it now
						$album_genre_ids = $album_genre_ids . $a1['genre_id'] . ';';
				}
				
				$res = mysqli_query($db,"UPDATE album SET 
							genre_id = '" . $db->real_escape_string($album_genre_ids) . "'
							WHERE album_id = '". $album['album_id'] ."';"); 
			}
	}	
}



//  +------------------------------------------------------------------------+
//  | array_find_deep by https://www.sitepoint.com/community/users/ScallioXTX|
//  +------------------------------------------------------------------------+

function array_find_deep($array, $search, $keys = array())
{
    foreach($array as $key => $value) {
        if (is_array($value)) {
            $sub = array_find_deep($value, $search, array_merge($keys, array($key)));
            if (count($sub)) {
                return $sub;
            }
        } elseif ($value === $search) {
            return array_merge($keys, array($key));
        }
    }

    return array();
}
?>