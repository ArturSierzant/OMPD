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
//  | Tile for album cover and info                                          |
//  +------------------------------------------------------------------------+
function draw_tile($size,$album,$multidisc = '', $retType = "echo",$tidal_cover = '') {
		global $db,$cfg, $t;
		$res = "";
		$md = "";
		$maxPlayed = $cfg['max_played'];
    $isAdded2library = false;
    
    //check if album is from a streaming service
    $query = mysqli_query($db,"SELECT path FROM album_id WHERE album_id = '" . mysqli_real_escape_string($db,$album['album_id']) . "' AND updated = '9'");
    if (mysqli_num_rows($query) > 0) {
      $streamAlbum = mysqli_fetch_assoc($query);
      $sA = explode(";",$streamAlbum['path']);
      $album['album_id'] = $sA[0];
      $album['audio_quality'] = $sA[2];
      if (isHra($album['album_id'])) {
        $album['cover'] = $sA[1];
      }
      $isAdded2library = true;
    }
    //do not show albums added from streaming service when one stopped to use that service
    if (isTidal($album['album_id']) && !$cfg['use_tidal']) {
      return;
    }
    if (isHra($album['album_id']) && !$cfg['use_hra']) {
      return;
    }
    
		$playedQuery = mysqli_query($db,"SELECT count(album_id) AS c FROM counter WHERE album_id ='" . $album['album_id'] . "'");
		$rows = mysqli_fetch_assoc($playedQuery);
		$played = $rows['c'];
		$pop = 0;
    if ($maxPlayed > 0 && $size > 0) {
      $pop = $played/$maxPlayed * $size;
    }
		if ($multidisc != '') {
			$md = '&md=' . $multidisc;
		}
		$res = '<div title="Go to album \'' . html($album['album']) .  '\'" class="tile pointer" style="width: ' . $size . 'px; height: ' . $size . 'px;">';
		if (isTidal($album['album_id']) && $cfg['use_tidal']) {
      if ($tidal_cover) {
				$pic = $tidal_cover;
			}
			else {
				$album_id = str_replace('tidal_','',$album['album_id']);
				$picQuery = mysqli_query($db,"SELECT cover FROM tidal_album 
				WHERE album_id = '" . $album_id . "'");
				$rows = mysqli_fetch_assoc($picQuery);
				$pic = $rows['cover'];
			}
			
			//album added before 'cover' field was added to 'tidal_album' table
			if (!$pic) {
				getAlbumFromTidal($album_id);
				$picQuery = mysqli_query($db,"SELECT cover FROM tidal_album 
				WHERE album_id = '" . $album_id . "'");
				$rows = mysqli_fetch_assoc($picQuery);
				$pic = $rows['cover'];
			}
			
			$cover = $t->albumCoverToURL($pic,'lq');
			if (!$cover) {
				$cover = 'image/no_image.jpg';
			}
			//$res .= '<img loading="lazy" decoding="async" onclick=\'location.href="index.php?action=view3&amp;album_id=' . $album['album_id'] . '"\' src="' . $cover . '" alt="" width="100%" height="100%">';
			$res .= '<img loading="lazy" decoding="async" onclick=\'location.href="index.php?action=view3&amp;album_id=' . $album['album_id'] . '"\' src="image.php?image_id=' . $album['album_id'] . '" alt="" width="100%" height="100%">';
		}
		elseif (isHra($album['album_id']) && $cfg['use_hra']) {
			//$res .= '<img loading="lazy" decoding="async" onclick=\'location.href="index.php?action=view3&amp;album_id=' . $album['album_id'] . '"\' src="' . $album["cover"] . '" alt="" width="100%" height="100%">';
			$res .= '<img loading="lazy" decoding="async" onclick=\'location.href="index.php?action=view3&amp;album_id=' . $album['album_id'] . '"\' src="image.php?image_id=' . $album['album_id'] . '&image_url=' . urlencode($album["cover"]) . '" alt="" width="100%" height="100%">';
		}
		else {
			$res .= '<img loading="lazy" decoding="async" onclick=\'location.href="index.php?action=view3&amp;album_id=' . $album['album_id'] . '"\' src="image.php?image_id=' . $album['image_id'] . '" alt="" width="100%" height="100%">';
		}
		if ($cfg['show_album_format'] == true && !isTidal($album['album_id']) && !isHra($album['album_id'])) {
			$query = mysqli_query($db, 'SELECT track.audio_bits_per_sample, track.audio_sample_rate, track.audio_dataformat, track.audio_profile, track.audio_encoder 
				FROM track left join album on album.album_id = track.album_id where album.album_id = "' .  mysqli_real_escape_string($db,$album['album_id']) . '"LIMIT 1');
			$album_info = mysqli_fetch_assoc($query);
			$audio_format = calculateAlbumFormat($album_info);
			if ($cfg['testing'] == 'on' && $audio_format <> 'CD'  && $audio_format <> 'UNKNOWN') {
				$res .= '   <div class="tile_format">' . html($audio_format) . '</div>';
			}
			elseif ($cfg['testing'] == 'off'  && $audio_format <> 'UNKNOWN') {
				$res .= '   <div class="tile_format">' . html($audio_format) . '</div>';
			}
		}
		elseif ($cfg['show_album_format'] == true && isTidal($album['album_id'])) {
			$audio_format = calculateAlbumFormat($album);
			if ($cfg['testing'] == 'on' && $audio_format <> 'CD' && $audio_format <> 'UNKNOWN') {
				$res .= '   <div class="tile_format">' . html($audio_format) . '</div>';
			}
			elseif ($cfg['testing'] == 'off' && $audio_format <> 'UNKNOWN') {
				$res .= '   <div class="tile_format">' . html($audio_format) . '</div>';
			}
		}
		elseif ($cfg['show_album_format'] == true && isHra($album['album_id'])) {
      if (isset($album['audio_quality_tag']) && $album['audio_quality_tag'] != '') {
        $res .= '   <div class="tile_format">' . html($album['audio_quality_tag']) . '</div>';
      }
      else {
        $res .= "<script>getHraAudioFormat('" . $album['album_id'] . "');</script>";
        $res .= '   <div style = "display: none;" class="tile_format" id="tile_format_' . $album['album_id'] . '"></div>';
      }
		}
    /* if ($isAdded2library) {
				$res .= '   <div class="tile_format" style="left: 0; right: auto; top: 0; "><i class="fa fa-fw fa-bookmark-o"></i></div>';

    } */
		//$res .= '	<div id="tile_title" class="tile_info">';
		$res .= '	<div class="tile_info" style="cursor: initial;">';
		$res .= '	<div class="tile_title">' . html($album['album']) . '</div>';
		$res .= '	<div class="tile_band">' . html($album['artist_alphabetic']) . '</div>';
		if ($cfg['show_quick_play']) {
			$res .= '<div class="quick-play">';
			if ($cfg['access_add']) $res .= '<i id="add_' . $album['album_id'] . '" title="Add album to playlist"  onclick="javascript:ajaxRequest(\'play.php?action=updateAddPlay&album_id=' . $album['album_id'] .  '\',updateAddPlay);ajaxRequest(\'play.php?action=addSelect&album_id=' . $album['album_id'] . $md . '\',evaluateAdd);" class="fa fa-plus-circle pointer" style="padding-right: 5px;"></i>';
			if ($cfg['access_play']) $res .= '<i id="play_' . $album['album_id'] . '" title="Play album" onclick="javascript: playAlbum(\'' . $album['album_id'] . '\',\'' . $multidisc . '\');" class="fa fa-play-circle-o pointer"></i>';
			$res .= '</div>';
		}		
			
		$res .= '</div>';
		if ($cfg['show_album_popularity']) {
			$res .= '<div class="in tile_popularity" style="width: ' . $pop . 'px;"></div>';
		}
		$res .= '</div>';
		if ($retType == 'echo') {
			echo $res;
		}
		else {
			return $res;
		}
			}


//  +---------------------------------------------------------------------------+
//  | getInbetweenStrings by https://stackoverflow.com/users/520896/ravi-verma  |
//  +---------------------------------------------------------------------------+

function getInbetweenStrings($start, $end, $str){
		global $cfg;
		$start = preg_quote($start);
		$end = preg_quote($end);
    $matches = array();
    $regex = "/$start(.*?)$end/";
    preg_match_all($regex, $str, $matches);
		if ($cfg['testing'] == 'on') $matches[1] = str_replace(";","&",$matches[1]);
    return $matches[1];
}


//  +---------------------------------------------------------------------------+
//  | multiexplode by php at metehanarslan dot com                              |
//  +---------------------------------------------------------------------------+

function multiexplode ($delimiters,$string) {
    if (empty($delimiters)) {
        return ($string);
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
		$pos = strpos($title,strtolower($separator[$i]),1); //start searching from position 1 to avoid empty results for e.g. "(You Said) You'd Gimme Some More"
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

function starSubMenu($i, $isFavorite, $isBlacklist, $track_id, $type = 'echo') {
	global $cfg, $db;
	if ($type == 'string'){
		ob_start();
	}
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
				<span id="blacklist-star-bg-sub<?php echo $track_id; ?>" class="larger blackstar<?php if ($isBlacklist) echo ' blackstar-selected'; ?>"><i id="blacklist_star-<?php echo $track_id; ?>" class="fa fa-star-o fa-fw"></i></span>
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
		echo listOfFavorites(true, true, $track_id);
	?>
		</select>
		<span id="playlistAddTo-<?php echo $track_id; ?>"><i class="fa fa-plus-circle fa-fw"></i> Add</span>
	</div>


</div>
<?php 
	if ($type == 'string'){
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}
}
	

	
//  +---------------------------------------------------------------------------+
//  | Draws sub menu for track                                                  |
//  +---------------------------------------------------------------------------+

function trackSubMenu($i, $track, $album_id = '', $type = 'echo') {
	if ($type == 'string'){
		ob_start();
	}
	global $cfg, $db;
	if (isset($track['tid'])) {
		$track['track_id'] = $track['tid']; // needed in search.php 'Track Artist'
	}
	$tidalAlbumId = '';
	
	//needed in play.php addTracks for TIDAL tracks not added to DB:
	if (isset($track['album']['id']) && is_numeric($track['album']['id'])) { 
		$tidalAlbumId = '&amp;album_id=tidal_' . $track['album']['id']; 
	}
	
	if (isset($track['relative_file'])) {
    $track['relative_file'] = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $track['relative_file']);
  }
?>
<div class="menuSub" id="menu-sub-track<?php echo $i ?>" onclick='//offMenuSub(<?php echo $i ?>);'> 
	
	<div><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes' . $tidalAlbumId .'&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();"><i id = "insertPlay_' . $track['track_id'] . '" class="fa fa-play-circle fa-fw icon-small"></i>Insert after currently playing track and play</a>'; ?>
	</div>
	
	<div>
	<?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect' . $tidalAlbumId .'&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Insert track ' . $track['number'] . '\');" onMouseOut="return nd();"><i id="insert_' . $track['track_id'] . '" class="fa fa-indent fa-fw icon-small"></i>Insert after currently playing track</a>';?>
	</div>
	
	<div>
	<?php if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect' . $tidalAlbumId .'&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track ' . $track['number'] . '\');" onMouseOut="return nd();"><i id="add_' . $track['track_id'] . '" class="fa fa-plus-circle fa-fw icon-small"></i>Add track to playlist</a>';?>
	</div>
	
	<div><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect' . $tidalAlbumId .'&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();"><i id="play_' . $track['track_id'] . '" class="fa fa-play-circle-o fa-fw icon-small"></i>Remove all from playlist and play track</a>'; ?>
	</div>
	<?php
	if (!isTidal($album_id) && !isHra($album_id) && !isYoutube($track['track_id'])) {
	?>
	<div><?php if ($cfg['access_stream']) echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['track_id'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track ' . $track['number'] . '\');" onMouseOut="return nd();"><i class="fa fa-rss fa-fw icon-small"></i>Stream track</a>'; ?>
	</div>
	
	<div><?php if ($cfg['access_download']) echo '<a href="download.php?action=downloadTrack&amp;track_id=' . $track['track_id'] .'&amp;download_id=' . $cfg['download_id'] . '" ' . onmouseoverDownloadTrack($track['track_id']) . '><i class="fa fa-download fa-fw icon-small"></i>Download track</a>'; ?>
	</div>
	
	<div><?php if ($cfg['access_play']) echo '<a href="getid3/demos/demo.browse.php?filename='. $cfg['media_dir'] . urlencode($track['relative_file']) . '" onClick="showSpinner();"><i class="fa fa-info-circle fa-fw icon-small"></i>File details</a>'; ?>
	</div>
	<?php 
	}
	?>
</div>
<?php
	if ($type == 'string'){
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}
	
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
		echo 'onclick="javascript:showSpinner();ajaxRequest(\'play.php?action=deleteIndex&amp;index=' . $i . '&amp;menu=playlist\');"'; ?>>Remove <i class="fa fa-times-circle fa-fw icon-small"></i></div>
		<div class="icon-anchor" id="track<?php echo $i; ?>_delete_album" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:showSpinner();ajaxRequest(\'play.php?action=deleteAlbum&amp;index=' . $i . '&amp;menu=playlist\');"'; ?>>Remove all from this album<i class="fa fa-dot-circle-o  fa-fw icon-small"></i></div>
    <div class="icon-anchor" id="track<?php echo $i; ?>_delete_below" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:showSpinner();ajaxRequest(\'play.php?action=deleteBelowIndex&amp;index=' . $i . '&amp;menu=playlist\');"'; ?>>Remove all below<i class="fa fa-times-circle-o fa-fw icon-small"></i></div>
		<div class="icon-anchor" id="track<?php echo $i; ?>_play_next" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:moveTrack(\'playNext\',' . $i . ',false);"'; ?>>Play next <i class="fa fa-caret-square-o-right fa-fw icon-small"></i></div>
		<div class="icon-anchor" id="track<?php echo $i; ?>_move_top" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:moveTrack(0,' . $i . ',true);"'; ?>>Move to top <i class="fa fa-long-arrow-up fa-fw icon-small"></i></div>
		<div class="icon-anchor" id="track<?php echo $i; ?>_move" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:toggleInsert(\'on\',' . $i . ')"'; ?>>Move <i class="fa fa-arrows-v fa-fw icon-small"></i></div>
		<div class="icon-anchor" id="track<?php echo $i; ?>_move_bottom" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:moveTrack(' . $bottom . ',' . $i . ',false);"'; ?>>Move to bottom <i class="fa fa-long-arrow-down fa-fw icon-small"></i></div>
		</div>
		
	</td>
</tr>
<?php
};



//  +---------------------------------------------------------------------------+
//  | List of Favorites                                                         |
//  +---------------------------------------------------------------------------+

function listOfFavorites($file = true, $stream = true, $track_id = "", $track_mpd_url = "") {
	global $cfg, $db;
	if ($track_mpd_url) {
		$url = parse_url($track_mpd_url);
		if ($url["scheme"]) { //track_mpd_url is stream (like 'http://' or 'https://'), not a local file
			parse_str($url["query"], $output);
			switch ($output['action']){
				case "streamYouTube":
					$track_id = "youtube_" . $output['track_id'];
					break;
				case "streamHRA":
					$track_id = "hra_" . $output['track_id'];
					break;
				case "streamTidal":
					$track_id = "tidal_" . $output['track_id'];
					break;
			}
		}
	}
	$favIds = array();
	$inPlaylistIndicator = '[&#9673;] ';
	if ($track_id){
		if (isTidal($track_id)) {
			$query = mysqli_query($db,"SELECT favorite_id FROM favoriteitem WHERE favorite_id NOT IN (SELECT favorite_id FROM favorite WHERE name = '" . $cfg['favorite_name'] . "' OR name = '" . $cfg['blacklist_name'] . "') AND (stream_url LIKE '%action=streamTidal%' AND stream_url LIKE '%" . getTidalId($track_id) ."%')");
		}
		elseif (isHra($track_id)) {
			$query = mysqli_query($db,"SELECT favorite_id FROM favoriteitem WHERE favorite_id NOT IN (SELECT favorite_id FROM favorite WHERE name = '" . $cfg['favorite_name'] . "' OR name = '" . $cfg['blacklist_name'] . "') AND (stream_url LIKE '%action=streamHRA%' AND stream_url LIKE '%" . getHraId($track_id) ."%')");
		}
		elseif (isYoutube($track_id)) {
			$query = mysqli_query($db,"SELECT favorite_id FROM favoriteitem WHERE favorite_id NOT IN (SELECT favorite_id FROM favorite WHERE name = '" . $cfg['favorite_name'] . "' OR name = '" . $cfg['blacklist_name'] . "') AND (stream_url LIKE '%action=streamYouTube%' AND stream_url LIKE '%" . getYouTubeId($track_id) ."%')");
		}
		else {
			$query = mysqli_query($db,"SELECT favorite_id FROM favoriteitem WHERE favorite_id NOT IN (SELECT favorite_id FROM favorite WHERE name = '" . $cfg['favorite_name'] . "' OR name = '" . $cfg['blacklist_name'] . "') AND (track_id ='" . $track_id ."' OR stream_url LIKE '%" . $track_id ."%')");
		}
		while ($rows = mysqli_fetch_assoc($query)) {
			$favIds[] = $rows['favorite_id'];
		}
	}
	if ($file) {
		$listOfFavorites = "
		<option class='listDivider' value='' selected disabled style='display: none;'>--- Select playlist ---</option>
		<option class='listDivider' value='' disabled>--- File and mixed playlists ---</option>";
		$query2 = mysqli_query($db,"SELECT name, favorite_id FROM favorite WHERE stream = 0 AND favorite_id NOT IN (SELECT favorite_id FROM favorite WHERE name = '" . $cfg['favorite_name'] . "' OR name = '" . $cfg['blacklist_name'] . "') ORDER BY name");
		while ($player = mysqli_fetch_assoc($query2)) {
			$inPlaylist = '';
			if (in_array($player['favorite_id'], $favIds)) {
				$inPlaylist = $inPlaylistIndicator;
			}
			$listOfFavorites .= "<option value=" . $player['favorite_id'] . ">" . $inPlaylist . html($player['name']) . "</option>";
		}
	}
	if ($stream) {
		$query2 = mysqli_query($db,'SELECT name, favorite_id FROM favorite WHERE stream = 1 ORDER BY name');
		if ($query2) {
			$listOfFavorites .= "<option class='listDivider' value='' disabled>--- Streams ---</option>";
			while ($player = mysqli_fetch_assoc($query2)) {
			$inPlaylist = '';
			if (in_array($player['favorite_id'], $favIds)) {
				$inPlaylist = $inPlaylistIndicator;
			}
			$listOfFavorites .= "<option value=" . $player['favorite_id'] . ">" . $inPlaylist . html($player['name']) . "</option>";
		}
		}
	}
		return $listOfFavorites;
}


	
//  +------------------------------------------------------------------------+
//  | Init Tidal object                                                      |
//  +------------------------------------------------------------------------+

function tidal() {
  global $cfg, $db;
  $t = new TidalAPI;
  $t->userId = $cfg['tidal_userid'];
  $t->countryCode = $cfg['tidal_countryCode'];
  $t->token = $cfg["tidal_token"];
  $t->refreshToken = $cfg["tidal_refresh_token"];
  $t->expiresAfter = $cfg["tidal_expires_after"];
  $t->audioQuality = $cfg["tidal_audio_quality"];
  $t->deviceCode = $cfg["tidal_deviceCode"];
  $t->fixSSLcertificate();
  return $t;
}


//  +------------------------------------------------------------------------+
//  | Refresh Tidal access_token                                             |
//  +------------------------------------------------------------------------+

function refreshTidalAccessToken() {
  global $cfg, $db, $t;
  $res = $t->refreshAccessToken();
  if (isset($res['access_token'])) {
    //write token to DB
    $tokenTime = time();
    $expires_after = $tokenTime + (int) $res['expires_in'];
    $sql = 'UPDATE tidal_token SET
            time = ' . $tokenTime . ',
            access_token = "' . mysqli_real_escape_string($db,$res['access_token']) . '",
            token_type = "' . mysqli_real_escape_string($db,$res['token_type']) . '",
            expires_in = ' . (int) $res['expires_in'] . ',
            expires_after = ' . $expires_after . ',
            userId = ' . (int) $res['user']['userId'] . ',
            countryCode = "' . mysqli_real_escape_string($db,$res['user']['countryCode']) . '",
            username = "' . mysqli_real_escape_string($db,$res['user']['username']) . '"
            WHERE 1';
    if (!mysqli_query($db, $sql)) {
      $errors = array();
      $errors['return'] = 1;
      $errors['error'] = "Token refresh failure: error in writing to DB.";
      return ($errors);
    }
    $res['expires_after'] = date('Y-m-d H:i:s',$expires_after);
  }
  return $res;
}


//  +------------------------------------------------------------------------+
//  | Login to Tidal stage 1: get device code                                |
//  +------------------------------------------------------------------------+

function getTidalDeviceCode() {
  global $cfg, $db, $t;
  $res = $t->getDeviceCode();
  if (isset($res['deviceCode'])) {
    //write deviceCode to DB
    $sql = 'UPDATE tidal_token SET
            time = 0,
            access_token = "",
            refresh_token = "",
            token_type = "",
            expires_in = 0,
            expires_after = 0,
            userId = 0,
            countryCode = "",
            username = "",
            deviceCode = "' . $res['deviceCode'] . '"
            WHERE 1';
    if (!mysqli_query($db, $sql)) {
      $errors = array();
      $errors['return'] = 1;
      $errors['error'] = "Get device code failure: error in writing to DB.";
      return ($errors);
    }
  }
  return $res;
}


//  +------------------------------------------------------------------------+
//  | Login to Tidal stage 2: check auth status                              |
//  +------------------------------------------------------------------------+

function checkTidalAuthStatus() {
  global $cfg, $db, $t;
  $token = mysqli_query($db,"SELECT * FROM tidal_token LIMIT 1");
  $rows = mysqli_fetch_assoc($token);
  $res = $t->checkAuthStatus();
  $res['auth_finished'] = false; 
  if (isset($res['access_token'])) {
    //write token to DB
    $tokenTime = time();
    $expires_after = $tokenTime + (int) $res['expires_in'];
    $sql = 'UPDATE tidal_token SET
            time = ' . $tokenTime . ',
            access_token = "' . mysqli_real_escape_string($db,$res['access_token']) . '",
            refresh_token = "' . mysqli_real_escape_string($db,$res['refresh_token']) . '",
            token_type = "' . mysqli_real_escape_string($db,$res['token_type']) . '",
            expires_in = ' . (int) $res['expires_in'] . ',
            expires_after = ' . $expires_after . ',
            userId = ' . (int) $res['user']['userId'] . ',
            countryCode = "' . mysqli_real_escape_string($db,$res['user']['countryCode']) . '",
            username = "' . mysqli_real_escape_string($db,$res['user']['username']) . '"
            WHERE 1';
    if (!mysqli_query($db, $sql)) {
      $errors = array();
      $errors['return'] = 1;
      $errors['error'] = "Get device code failure: error in writing to DB.";
      return ($errors);
    }
    $res['auth_finished'] = true; 
  }
  return $res;
}


//  +------------------------------------------------------------------------+
//  | Logout from Tidal                                                      |
//  +------------------------------------------------------------------------+

function logoutTidal() {
  global $cfg, $db, $t;
  $res = $t->logout();
  if ($res['logout_status'] === true || strpos($res['error'],'User does not have a valid session') !== false) {
    //reset token in DB
    $sql = 'UPDATE tidal_token SET
            time = 0,
            access_token = "",
            refresh_token = "",
            token_type = "",
            expires_in = 0,
            expires_after = 0,
            userId = 0,
            countryCode = "",
            username = "",
            deviceCode = ""
            WHERE 1';
    if (!mysqli_query($db, $sql)) {
      $errors = array();
      $errors['return'] = 1;
      $errors['error'] = "Error while resetting token in DB.";
      return ($errors);
    }
    $res['return'] = 0;
  }
  return $res;
}


//  +------------------------------------------------------------------------+
//  | Albums from Tidal                                                      |
//  +------------------------------------------------------------------------+
function showAlbumsFromTidal($artist, $size, $ajax, $tidalArtistId) {
	global $cfg, $db, $t;
	//echo $artist;
	//$artist = tidalEscapeChar($artist);
	//$artist = replaceAnds($artist);
	$sql = "SELECT MIN(last_update_time) as min_last_update_time 
	FROM tidal_album 
	WHERE artist LIKE '" . mysqli_real_escape_string($db,$artist) . "'
	AND last_update_time > 0";
	$query = mysqli_query($db, $sql);
	$res = mysqli_fetch_assoc($query);
	$minDate = $res['min_last_update_time'];
	
	$sql = "SELECT MAX(last_update_time) as min_last_update_time 
	FROM tidal_album 
	WHERE artist LIKE '" . mysqli_real_escape_string($db,$artist) . "'
	AND last_update_time > 0";
	$query = mysqli_query($db, $sql);
	$res = mysqli_fetch_assoc($query);
	
	$data = array();
	
	//prevent diaplaying albums deleted from Tidal
	$forceUpdate = false;
	if (abs($res['min_last_update_time'] - $minDate) > 10) $forceUpdate = true;
	
	if ($res['min_last_update_time'] < (time() - TIDAL_MAX_CACHE_TIME) || !$query || $forceUpdate) {
		/* $t = new TidalAPI;
		$t->username = $cfg["tidal_username"];
		$t->password = $cfg["tidal_password"];
		$t->token = $cfg["tidal_token"];
		if (NJB_WINDOWS) $t->fixSSLcertificate(); */
    //$t = tidal();
		$conn = $t->connect();
		
		if ($conn === true){
			if ($tidalArtistId) {
				$results = $t->getArtistAlbums($tidalArtistId,999);
				$resultsEPs = $t->getArtistEPsAndSingles($tidalArtistId,999);
			}
			else if ($artist) {
				$artist = tidalEscapeChar(strtolower($artist));
				$results = $t->search("artists",$artist);
				if (count($results) == 0) {
					if ($ajax) {
						$data['results'] = 0;
						echo safe_json_encode($data);
						return;
					}
					else {
						echo "No results found on TIDAL.";
						return;
					}
				}
				else {
					foreach($results["items"] as $res) {
						if (tidalEscapeChar(strtolower($res["name"])) == $artist) {
							$tidalArtistId = $res["id"];
							break;
						}
					}
					$results = $t->getArtistAlbums($tidalArtistId,999);
					$resultsEPs = $t->getArtistEPsAndSingles($tidalArtistId,999);
				}
			}
		}
		else {
			$data['return'] = $conn["return"];
			$data['response'] = $conn["error"];
			echo safe_json_encode($data);
			return;
		}

		if ($results['totalNumberOfItems'] === 0 && $resultsEPs['totalNumberOfItems'] === 0) {
			if ($ajax) {
				$data['results'] = 0;
				echo safe_json_encode($data);
				return;
			}
			else {
				echo "No results found on TIDAL.";
				return;
			}
		}

		if ($results['items'] || $resultsEPs['items']) {
			$sql = "DELETE FROM tidal_album WHERE artist_id = '" . mysqli_real_escape_string($db,$tidalArtistId) . "'";
			mysqli_query($db, $sql);

			for($i=0;$i<2;$i++) {
				if ($i == 0) {
					$albums = $results['items'];
				}
				else {
					if($albums = $resultsEPs['items']){;
						echo ('<h1>EPs and Singles</h1>');
					}
				}
				usort($albums, function ($a, $b) {
					return $a['releaseDate'] <=> $b['releaseDate'];
				});
				foreach ($albums as $album) {
					
					$artists = '';
					foreach ($album["artists"] as $a){
						if ($artists == ''){
							$artists = $a["name"];
						}
						else {
							$artists = $artists . " & " . $a["name"];
						}
					}
					if ($artists == '') $artists = $album["artist"]["name"];
					
					
					$sql = "REPLACE INTO tidal_album 
					(album_id, artist, artist_alphabetic, artist_id, album, album_date, genre_id, discs, seconds, last_update_time, cover, type, audio_quality)
					VALUES (
					'" . $album["id"] . "', '" . mysqli_real_escape_string($db,$artists) . "', '" . mysqli_real_escape_string($db,$artists) . "', '" . $album["artist"]["id"] . "', '" . mysqli_real_escape_string($db,$album["title"]) . "', '" . $album["releaseDate"] . "', '', 1, '" . $album["duration"] . "','" . time() . "', '" . $album["cover"] . "','" . $album["type"] . "','" . $album["audioQuality"] . "')";
					
					mysqli_query($db, $sql);
					
					$tidalAlbum["album_id"] = 'tidal_' . $album["id"];
					$tidalAlbum["album"] = $album["title"];
					$tidalAlbum["artist_alphabetic"] = $artists;
					$tidalAlbum["audio_quality"] = $album["audioQuality"];
					draw_tile($size, $tidalAlbum);
				}
			}
		}
		else {
			if ($ajax) {
				$data['results'] = 0;
				echo safe_json_encode($data);
				return;
			}
			else {
				echo "No results found on TIDAL.";
				return;
			}
		}
	}
	else {
		/* $sql = "SELECT album_id, album, artist FROM tidal_album
		WHERE artist LIKE '" . mysqli_real_escape_string($db,$artist) . "'"; */

		$art = replaceAnds($artist);
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
			$filter_query = '(
			artist = "' .  mysqli_real_escape_string($db,moveTheToBegining($artist)) . '" OR artist LIKE "' .mysqli_real_escape_string($db,moveTheToBegining($art)) . '" OR artist = "' .  mysqli_real_escape_string($db,moveTheToEnd($artist)) . '" OR artist LIKE "' .mysqli_real_escape_string($db,moveTheToEnd($art)) . '"' . $search_str . ')';
		}
		else {
			$filter_query = '(
			artist = "' .  mysqli_real_escape_string($db,$artist) . '" OR artist LIKE "' .mysqli_real_escape_string($db,$art) . '"' . $search_str . ') ORDER BY album_date';
		}
		for ($j=0;$j<2;$j++){
			if ($j==0) {
				$filter_query1 = 'WHERE type="album" AND ' . $filter_query;
				$sql = "SELECT album_id, album, artist, album_date, audio_quality FROM tidal_album " . $filter_query1;
				$query = mysqli_query($db,$sql);
			}
			else {
				$filter_query1 = 'WHERE type != "album" AND ' . $filter_query;
				$sql = "SELECT album_id, album, artist, album_date, audio_quality FROM tidal_album " . $filter_query1;
				$query = mysqli_query($db,$sql);
				if (mysqli_num_rows($query) > 0) {
					echo ('<h1>EPs and Singles</h1>');
				}
			}
		
			
			while($album = mysqli_fetch_assoc($query)) {
				$tidalAlbum["album_id"] = 'tidal_' . $album["album_id"];
				$tidalAlbum["album"] = $album["album"];
				$tidalAlbum["artist_alphabetic"] = $album["artist"];
				$tidalAlbum["audio_quality"] = $album["audio_quality"];
				draw_tile($size, $tidalAlbum);
			}
		}
	}
}


//  +------------------------------------------------------------------------+
//  | Album from Tidal                                                       |
//  +------------------------------------------------------------------------+
function getAlbumFromTidal($album_id) {
	global $cfg, $db, $t;

	$data = array();

	$conn = $t->connect();
	if ($conn === true){
		$results = $t->getAlbum($album_id);
	}
	else {
		$data['return'] = $conn["return"];
		$data['response'] = $conn["error"];
		echo safe_json_encode($data);
		return;
	}
	
	if (count($results) == 0) {
		$data['results'] = 0;
		return safe_json_encode($data);
	}
	
	$artists = '';
	foreach ($results["artists"] as $a){
		if ($artists == ''){
			$artists = $a["name"];
		}
		else {
			$artists = $artists . " & " . $a["name"];
		}
	}
	if ($artists == '') $artists = $results["artist"]["name"];
	
	$sql = "REPLACE INTO tidal_album 
	(album_id, artist, artist_alphabetic, artist_id, album, album_date, genre_id, discs, seconds, last_update_time, cover, type, audio_quality)
	VALUES (
	'" . $results["id"] . "', '" . mysqli_real_escape_string($db,$artists) . "', '" . mysqli_real_escape_string($db,$artists) . "', '" . $results["artist"]["id"] . "', '" . mysqli_real_escape_string($db,$results["title"]) . "', '" . $results["releaseDate"] . "', '', 1, '" . $results["duration"] . "','0','" . $results["cover"] . "','" . $results['type'] . "','" . $results['audioQuality'] . "')";
	
	mysqli_query($db, $sql);
	$data['results'] = 1;
	return safe_json_encode($data);
}


//  +------------------------------------------------------------------------+
//  | Album from Tidal with selected track                                   |
//  +------------------------------------------------------------------------+
function getTrackAlbumFromTidal($track_id) {
	global $cfg, $db, $t;

	$data = array();
	
  //$t = tidal();
	/* $t = new TidalAPI;
	$t->username = $cfg["tidal_username"];
	$t->password = $cfg["tidal_password"];
	$t->token = $cfg["tidal_token"];
	if (NJB_WINDOWS) $t->fixSSLcertificate(); */
	$conn = $t->connect();
	if ($conn === true){
		$results = $t->getTrack($track_id);
	}
	else {
		return false;
	}
	
	if ($album = $results["album"]["id"]) {
		return $album;
	}
	else {
		return false;
	}
}



//  +------------------------------------------------------------------------+
//  | Artist biography from Tidal                                            |
//  +------------------------------------------------------------------------+
function showArtistBio($artist_name, $size) {
	global $cfg, $db, $t;
	$artist_name = moveTheToBegining($artist_name);
	$data = array();

	$conn = $t->connect();
	if ($conn === true){
		$res = $t->search("artists",$artist_name);
		if ($res["totalNumberOfItems"] == 0) {
			$data["artist_count"] = 0;
			$data["return"] = 0;
		}
		else {
			//$data["test"] = $res["totalNumberOfItems"];
			foreach ($res["items"] as $artist) {
				if (tidalEscapeChar(strtolower($artist["name"])) == tidalEscapeChar(strtolower($artist_name))) {
					$id = $artist["id"];
					$data = $t->getArtistBio($id);
					if ($artist["picture"]){
						$data["picture"] = $t->artistPictureToURL($artist["picture"]);
						$data["pictureW"] = $t->artistPictureWToURL($artist["picture"]);
					}
					else {
						$data["picture"] = "";
					}
					$data["text"] = formatBio($data["text"]);
					$data["artist_id"] = $id;
					$data["related_artists"] = $t->getRelatedArtists($id);
					$i = 0;
					if ($data["related_artists"]) {
						foreach($data["related_artists"] as $rel_artist){
							//$rel_artist["picture"] = $t->artistPictureToURL($rel_artist["picture"]);
							if ($rel_artist["picture"]) {
								$data["related_artists"][$i]["picture"] = $t->artistPictureToURL($rel_artist["picture"]);
							}
							else {
								$data["related_artists"][$i]["picture"] = "";
							}
							$i++;
						}
					}
					$artist_links = $t->getArtistLinks($id);
          $data['artist_links']['links_found'] = 0;
          if (isset($artist_links['items'])) {
            foreach($artist_links['items'] as $al){
              if ($al['siteName'] == 'ALLMUSIC' || $al['siteName'] == 'DISCOGS' || $al['siteName'] == 'FACEBOOK' || $al['siteName'] == 'OFFICIAL_HOMEPAGE' || $al['siteName'] == 'WIKIPEDIA' || $al['siteName'] == 'YOUTUBE' || $al['siteName'] == 'MYSPACE' || $al['siteName'] == 'TWITTER') {
                $data['artist_links']['links_found']++;
                $data['artist_links']['items'][] = array('url' => $al['url'], 'siteName' => $al['siteName']);
              } 
            }
          }
					$data['size'] = $size;
					/* $data = $t->getArtistAll($id);
					$data["picture"] = $t->getArtistPicture($data["rows"][0]["modules"][0]["artist"]["picture"]);
					$data["text"] = formatBio($data["rows"][0]["modules"][0]["bio"]["text"]); */
					if ($data["status"] == 404 && strpos($data["userMessage"],"not found") === false) {
						$data["artist_count"] = 0; 
					}
					else {
						$data["artist_count"] = 1; 
					}
					$data["return"] = 0;
					break;
				}
				else {
					$data["artist_count"] = 0;
					$data["return"] = 0;
				}
			}
		}
	}
	else {
		$data['return'] = $conn["return"];
		$data['response'] = $conn["error"];
	}
	echo safe_json_encode($data);
}


//  +------------------------------------------------------------------------+
//  | Format artist bio from Tidal                                           |
//  +------------------------------------------------------------------------+
function formatBio($bio) {
	global $cfg, $db;
	
	$bio = str_replace("<br/><br/>","<br/>",$bio);
	$bio = str_replace("<br/>","<br/><br/>",$bio);
	$bio = str_replace("[/wimpLink]","</a>",$bio);
	$bio = str_replace('[wimpLink artistId="','<a target="_blank" href="' . TIDAL_ARTIST_URL,$bio);
	$bio = str_replace('[wimpLink albumId="','<a target="_blank" href="' . TIDAL_ALBUM_URL,$bio);
	$bio = str_replace('"]','">',$bio);
	//$bio = $bio . "<br/><br/>";
	return $bio;
	
}


//  +------------------------------------------------------------------------+
//  | Tracks from Tidal album                                                |
//  +------------------------------------------------------------------------+
function getTracksFromTidalAlbum($album_id, $order = '') {
	global $cfg, $db, $t;
	$field = 'albumTracks';
	$value = $album_id;
	
	$sql = "SELECT album_id FROM tidal_album WHERE album_id = " . $album_id;
	$query = mysqli_query($db,$sql);
	if (mysqli_num_rows($query) == 0) {
		getAlbumFromTidal($album_id);
	}
	 
   //$t = tidal();
	/* $t = new TidalAPI;
	$t->username = $cfg["tidal_username"];
	$t->password = $cfg["tidal_password"];
	$t->token = $cfg["tidal_token"];
	if (NJB_WINDOWS) $t->fixSSLcertificate(); */
	$conn = $t->connect();
	if ($conn === true){
		$results = $t->getAlbumTracks($album_id);
	}
	else {
		$data['return'] = $conn["return"];
		$data['response'] = $conn["error"];
		echo safe_json_encode($data);
		return;
	}
	
	if (!isset($results["totalNumberOfItems"])) {
		$data['results'] = 0;
		return safe_json_encode($data);
	}
	 
	$tracks = $results["items"];
	if (count($tracks) > 0) {
		if ($order == 'DESC') {
			usort($tracks, function ($a, $b) {
				return $b['volumeNumber'] <=> $a['volumeNumber'] ?: $b['trackNumber'] <=> $a['trackNumber'];
			});
		}
		else {
			usort($tracks, function ($a, $b) {
				return $a['volumeNumber'] <=> $b['volumeNumber'] ?: $a['trackNumber'] <=> $b['trackNumber'];
			});
		}
		foreach ($tracks as $track){
			$artists = '';
			foreach ($track["artists"] as $a){
				if ($artists == ''){
					$artists = $a["name"];
				}
				else {
					$artists = $artists . " & " . $a["name"];
				}
			}
			if ($artists == '') $artists = $track["artist"]["name"];
			$sql = "REPLACE INTO tidal_track 
			(track_id, title, artist, artist_alphabetic, genre_id, disc, seconds, number, album_id)
			VALUES (
			'" . $track["id"] . "', '" . mysqli_real_escape_string($db,$track["title"]) . "', '" . mysqli_real_escape_string($db,$artists) . "', '" . mysqli_real_escape_string($db,$artists) . "', '', '" . $track["volumeNumber"] . "', '" . $track["duration"] . "', '" . $track["trackNumber"] . "', '" . $album_id . "')";
			
			mysqli_query($db, $sql);
		}
		//if ($order == 'DESC') array_reverse($tracks);
		return safe_json_encode($tracks);
	}
	return false;
}


//  +------------------------------------------------------------------------+
//  | All from Tidal                                                         |
//  +------------------------------------------------------------------------+
function showAllFromTidal($searchStr, $size) {
	global $cfg, $db, $t;
	$field = 'all';
	$value = $searchStr;
	$artistsList = "";
	$albumsList = "";
	$data = array();
	
	/* $t = new TidalAPI;
	$t->username = $cfg["tidal_username"];
	$t->password = $cfg["tidal_password"];
	$t->token = $cfg["tidal_token"];
	if (NJB_WINDOWS) $t->fixSSLcertificate(); */
  //$t = tidal();
	$conn = $t->connect();
	if ($conn === true){
		$results = $t->searchAll($value);
	}
	else {
		$data['return'] = $conn["return"];
		$data['response'] = $conn["error"];
		echo safe_json_encode($data);
		return;
	}
	
	if (count($results['artists']['items']) == 0) {
		$data['artists_results'] = 0;
	}
	if ($results['artists']['items']) {
		$data['artists_results'] = count($results['artists']['items']);
		$artistsList = '<table class="border" cellspacing="0" cellpadding="0">';
		foreach ($results['artists']['items'] as $art) {
			$artistsList .= '<tr class="artist_list"><td class="space"></td><td><a href="index.php?action=view2&order=year&sort=asc&artist=' . rawurlencode($art['name']) . '&amp;tidalArtistId=' . rawurlencode($art['id']). '&amp;order=year">' . html($art['name']) . '</a></td></tr>';
			}
		$artistsList .= '</table>';
		$data['artists'] = $artistsList;
	}
	
	if (count($results['albums']['items']) == 0) {
		$data['albums_results'] = 0;
	}
	if ($results['albums']['items']) {
		$data['albums_results'] = count($results['albums']['items']);
		$albumsList = '<table class="border" cellspacing="0" cellpadding="0">';
		foreach ($results['albums']['items'] as $art) {
			$album['album_id'] = 'tidal_' . $art['id'];
			$album['artist_alphabetic'] = $art['artists'][0]['name'];
			$album['album'] = $art['title'];
			$album['audio_quality'] = $art['audioQuality'];
			$albumsList .= draw_tile($size, $album, '', 'string',$art['cover']);
			}
		$albumsList .= '</table>';
		$data['albums'] = $albumsList;
	}
	
	if (count($results['tracks']['items']) == 0) {
		$data['tracks_results'] = 0;
	}
	if ($results['tracks']['items']) {
		$data['tracks_results'] = count($results['tracks']['items']);
		$tracksList = tidalTracksList($results['tracks']);
		$data['tracks'] = $tracksList;
	}
	echo safe_json_encode($data);
}

//  +------------------------------------------------------------------------+
//  | Top tracks from Tidal                                                  |
//  +------------------------------------------------------------------------+
function showTopTracksFromTidal($artist, $tidalArtistId = "") {
	global $cfg, $db, $t;
	$value = $searchStr;
	$data = array();
	$data['tracks_results'] = 0;
	
	/* $t = new TidalAPI;
	$t->username = $cfg["tidal_username"];
	$t->password = $cfg["tidal_password"];
	$t->token = $cfg["tidal_token"];
	if (NJB_WINDOWS) $t->fixSSLcertificate(); */
  //$t = tidal();
	$conn = $t->connect();
	if ($conn === true){
		if ($tidalArtistId) {
			$results = $t->getArtistTopTracks($tidalArtistId);
		}
		elseif ($artist) {
			$artist = tidalEscapeChar(strtolower($artist));
				$results = $t->search("artists",$artist);
				if (count($results) == 0) {
					if ($ajax) {
						$data['results'] = 0;
						echo safe_json_encode($data);
						return;
					}
					else {
						echo "No results found on TIDAL.";
						return;
					}
				}
				else {
					foreach($results["items"] as $res) {
						if (tidalEscapeChar(strtolower($res["name"])) == $artist) {
							$tidalArtistId = $res["id"];
							break;
						}
					}
					$results = $t->getArtistTopTracks($tidalArtistId);
				}
		}
	}
	else {
		$data['return'] = $conn["return"];
		$data['response'] = $conn["error"];
		echo safe_json_encode($data);
		return;
	}
	if ($results['items']) {
		$data['tracks_results'] = $results['totalNumberOfItems'];
		$tracksList = tidalTracksList($results);
		$data['top_tracks'] = $tracksList;
	}
	echo safe_json_encode($data);
}


//  +------------------------------------------------------------------------+
//  | Draws list of tracks from Tidal                                        |
//  +------------------------------------------------------------------------+

function tidalTracksList($tracks) {
	global $cfg;
	$tracksList = '<table class="border" cellspacing="0" cellpadding="0">';
		$tracksList .= '
		<tr class="header">
			<td class="icon"></td><!-- track menu -->
			<td class="icon">';
		if ($cfg["access_add"] && false) {  
			$tracksList .= '<span onMouseOver="return overlib(\'Add all tracks\');" onMouseOut="return nd();"><i id="add_all_TOPT" class="fa fa-plus-circle fa-fw icon-small pointer"></i></span>';
		}
		$tracksList .= '
			</td><!-- add track -->
			<td class="track-list-artist">Track artist&nbsp;</td>
			<td>Title&nbsp;</td>
			<td>Album&nbsp;</td>
			<td></td>
			<td></td>
			<td align="right" class="time time_w">Time</td>
			<td class="space right"></td>
		</tr>';
		
		$i=40000;
		$TOPT_ids = ''; 
		foreach ($tracks['items'] as $track) {
			$track['track_id'] = 'tidal_' . $track['id'];
			$isFavorite = isInFavorite($track['track_id'], $cfg['favorite_id']);
			$isBlacklist = isInFavorite($track['track_id'], $cfg['blacklist_id']);
			$even_odd = ($i++ & 1) ? 'even' : 'odd';
			$tracksList .= '
			
			<tr class="' . $even_odd . ' mouseover">
				<td class="icon">
				<span id="menu-track'. $i .'">
				<div onclick="toggleMenuSub(' . $i . ');">
					<i id="menu-icon' . $i .'" class="fa fa-bars icon-small"></i>
				</div>
				</span>
				</td>
				
				<td class="icon">
				<span>';
			if ($cfg['access_add']) {
				$tracksList .= '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;album_id=tidal_' . $track['album']['id'] .'&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track ' . addslashes($track['title']) . '\');" onMouseOut="return nd();"><i id="add_tidal_' . $track['id'] . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';
			}
			$tracksList .= '
				</span>
				</td>
				<td class="track-list-artist">
				<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['artists'][0]['name']) . '&amp;order=year">' . html($track['artists'][0]['name']) . '</a>';
				if (count($track['artists']) > 1) {
					foreach ($track['artists'] as $key => $TOPT_art)
					if ($key > 0) {
						$tracksList .= ' & <a href="index.php?action=view2&amp;artist=' . rawurlencode($TOPT_art['name']) . '&amp;order=year">' . html($TOPT_art['name']) . '</a>';
					}
				}
				$tracksList .= '</td>
				<td><a id="a_play_track' . $i . '" href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;album_id=tidal_' . $track['album']['id'] .'&amp;track_id=' . $track['track_id'] . '&amp;position_id=' . $i . '\',evaluateAdd);" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();">' . $track['title'] . '</a>
				<span class="track-list-artist-narrow">by ' . html($track['artists'][0]['name']);
				if (count($track['artists']) > 1) {
					foreach ($track['artists'] as $key => $TOPT_art)
					if ($key > 0) {
						$tracksList .= ' & ' . html($TOPT_art['name']);
					}
				}
				$o = "";
				if (!$isFavorite) {
					$o = "-o";
				}
				$starClass = "";
				if ($isBlacklist) {
					$starClass = " blackstar blackstar-selected";
				}
				$tracksList .= '</span>
				</td>
				
				<td><a id="a_album' . $i . '" href="index.php?action=view3&amp;album_id=tidal_' . $track['album']['id'] . '">' . $track['album']['title'] . '</a>
				</td>
				
				<td onclick="toggleStarSub(' . $i . ',\'' . $track['track_id'] . '\');" class="pl-favorites">
				<span id="blacklist-star-bg' . $track['track_id'] . '" class="' . $starClass . '">
				<i class="fa fa-star' . $o . ' fa-fw" id="favorite_star-' . $track['track_id'] . '"></i>
				</span>
				</td>
				
				<td></td>
				<td>' . formattedTime($track['duration'] * 1000) . '</td>
				<td></td>
				</tr>
			
			';
			$tracksList .= '
				<tr>
					<td colspan="20">
					' . starSubMenu($i, $isFavorite, $isBlacklist, $track['track_id'], 'string') . '
					</td>
				</tr>';
			
			$tracksList .= '
				<tr>
				<td colspan="20">
				' . trackSubMenu($i, $track, 'tidal_' . $track['album']['id'], 'string') . '
				</td>
				</tr>';
			}
		$tracksList .= '</table>';
		
		return $tracksList;
}

//  +------------------------------------------------------------------------+
//  | Check if album/track is from Tidal                                     |
//  +------------------------------------------------------------------------+

function isTidal($id) {
	global $cfg;
	if (strpos($id,"tidal_") !== false || strpos($id,'tidal.com/') !== false || strpos($id,MPD_TIDAL_URL) !== false || ($cfg['upmpdcli_tidal'] && strpos($id,$cfg['upmpdcli_tidal']) !== false)) {
		return true;
	}
	return false;
}


//  +------------------------------------------------------------------------+
//  | Get pure Tidal id of item                                              |
//  +------------------------------------------------------------------------+

function getTidalId($id){
	global $cfg;
	//for stream url from getStreamURL() 
	if (strpos($id,TIDAL_TRACK_STREAM_URL) !== false) {
		$id = end(explode('&',$id));
		return end(explode('=',$id));
	}
	//for tidal://track/ or tidal://album/, https://tidal.com/browse/track/120884236 etc
	elseif (strpos($id,'tidal://') !== false || strpos($id,'tidal.com/') !== false) {
		return end(explode('/',$id));
	}
	elseif (strpos($id,'action=streamTidal') !== false) {
		return end(explode('=',$id));
	}
	elseif ($cfg['upmpdcli_tidal'] && strpos($id,$cfg['upmpdcli_tidal']) !== false) {
		return end(explode('=',$id));
	}
	else {
		return str_replace('tidal_','',$id);
	}
}


//  +------------------------------------------------------------------------+
//  | Artists from HRA                                                       |
//  +------------------------------------------------------------------------+
function showArtistsFromHRA($searchStr, $size) {
	global $cfg, $db;
	$value = $searchStr;
	$artistsList = "";
	$data = array();
	
	$h = new HraAPI;
	if (NJB_WINDOWS) $h->fixSSLcertificate();
	$results = $h->searchArtists($value);
	if (!$results['data']) {
		$data['artists_results'] = 0;
	}
	if ($results['data']) {
		$data['artists_results'] = count($results['data']);
		$artistsList = '<table class="border" cellspacing="0" cellpadding="0">';
		foreach ($results['data'] as $art) {
			$artistsList .= '<tr class="artist_list"><td class="space"></td><td><a href="index.php?action=view2&order=year&sort=asc&artist=' . rawurlencode($art['artist']) . '&amp;hraArtistId=' . rawurlencode($art['artistId']). '&amp;order=year">' . html($art['artist']) . '</a></td></tr>';
			}
		$artistsList .= '</table>';
		$data['artists'] = $artistsList;
	}
	
	echo safe_json_encode($data);
}



//  +------------------------------------------------------------------------+
//  | Genre from HRA                                                         |
//  +------------------------------------------------------------------------+
function showGenreFromHRA($showGenre = '') {
  global $cfg, $db;
  $data = array();

  $h = new HraAPI;
  if (NJB_WINDOWS) $h->fixSSLcertificate();
  $results = $h->getAllGenres();
  if (!$results['data']) {
    $data['genre_results'] = 0;
  }
  if ($results['data']) {
    $sorted = array();
    foreach ($results['data']['results'] as $genre) {
      if ($showGenre) {
        if ($showGenre == $genre['title']) {
          $subsorted = array();
          foreach($genre['subgenre'] as $subgenre) {
            $subsorted[$subgenre['title']] = $subgenre['prefix'];
          }
          ksort($subsorted);
          $sorted[$genre['title']] = array(0 => $genre['prefix'], 1 => $subsorted);
        }
      }
      else {
        $subsorted = array();
        foreach($genre['subgenre'] as $subgenre) {
          $subsorted[$subgenre['title']] = $subgenre['prefix'];
        }
        ksort($subsorted);
        $sorted[$genre['title']] = array(0 => $genre['prefix'], 1 => $subsorted);
      }
    }
    ksort($sorted);
    $data['genre_results'] = count($sorted);
    $genreList = '<table class="border" cellspacing="0" cellpadding="0">';
    if ($showGenre) {
      foreach ($sorted as $key=>$value) {
        $genreList .= '<tr class="artist_list"><td class="space"></td><td class= "lh2">';
        if (count($value[1])>0) {
          foreach($value[1] as $key=>$value) {
            $genreList .= '<a href="index.php?action=viewNewFromHRA&amp;prefix=' . rawurlencode($value). '">' . html($key) . '</a> | ';
          }
        }
        else {
          $genreList .= 'No subgenres found.';
        }
        $genreList .= '</td></tr>';
        $genreList .= '<tr class="line"><td></td><td></td></tr>';
      }
    }
    else {
      $genreList .= '<tr class="header"><td></td><td>Genre</td><td class="space"></td><td>Subgenre</td></tr>';
      foreach ($sorted as $key=>$value) {
        $genreList .= '<tr class="artist_list"><td class="space"></td><td><a href="index.php?action=viewNewFromHRA&amp;prefix=' . rawurlencode($value[0]). '">' . html($key) . '</a></td><td></td><td class= "lh2">';
        foreach($value[1] as $key=>$value) {
          $genreList .= '<a href="index.php?action=viewNewFromHRA&amp;prefix=' . rawurlencode($value). '">' . html($key) . '</a> | ';
        }
        $genreList .= '</td></tr>';
        $genreList .= '<tr class="line"><td></td><td></td><td></td><td></td></tr>';
      }
    }
    $genreList .= '</table>';
    $data['genreList'] = $genreList;
  }

  echo safe_json_encode($data);
}



//  +------------------------------------------------------------------------+
//  | Albums from HRA                                                        |
//  +------------------------------------------------------------------------+
function showAlbumsFromHRA($searchStr, $size) {
	global $cfg, $db;
	$value = $searchStr;
	$artistsList = "";
	$data = array();
	
	$h = new HraAPI;
	$h->username = $cfg["hra_username"];
	$h->password = $cfg["hra_password"];
	if (NJB_WINDOWS) $h->fixSSLcertificate();
	$conn = $h->connect();
	if (!$conn){
		$data['return'] = $conn["return"];
		$data['response'] = $conn["error"];
		echo safe_json_encode($data);
		return;
	}
	$results = $h->searchAlbums($value);
	if (!$results['data']) {
		$data['albums_results'] = 0;
	}
	
	if ($results['data']) {
		$data['albums_results'] = count($results['data']);
		$albumsList = '<table class="border" cellspacing="0" cellpadding="0">';
		foreach ($results['data'] as $art) {
      if (isset($art['cover'])) { //no cover -> album not published yet
        $album['album_id'] = 'hra_' . $art['albumId'];
        $album['artist_alphabetic'] = $art['artist'];
        $album['album'] = $art['title'];
        $album['cover'] = $art['cover'];
        if ($cfg['show_album_format']) {
          $aq = $h->getAlbum($art['albumId']);
          $album['audio_quality'] = $aq['data']['results']['tracks'][0]['format'];
        }
        $albumsList .= draw_tile($size, $album, '', 'string');
			}
    }
		$albumsList .= '</table>';
		$data['albums'] = $albumsList;
	}
	
	echo safe_json_encode($data);
}



//  +------------------------------------------------------------------------+
//  | Artist albums from HRA                                                 |
//  +------------------------------------------------------------------------+
function showArtistAlbumsFromHRA($searchStr, $size) {
	global $cfg, $db;
	$value = $searchStr;
	$artistsList = "";
	$data = array();
	
	$h = new HraAPI;
	$h->username = $cfg["hra_username"];
	$h->password = $cfg["hra_password"];
	if (NJB_WINDOWS) $h->fixSSLcertificate();
	$conn = $h->connect();
	if (!$conn){
		$data['return'] = $conn["return"];
		$data['response'] = $conn["error"];
		echo safe_json_encode($data);
		return;
	}
	
	$results = $h->getArtistAlbums($value);
	if (!$results['data']) {
		$data['albums_results'] = 0;
	}
	else {
		$data['albums_results'] = count($results['data']);
		$albumsList = '<table class="border" cellspacing="0" cellpadding="0">';
		foreach ($results['data'] as $art) {
      if (isset($art['cover'])) { //no cover -> album not published yet
        $album['album_id'] = 'hra_' . $art['albumId'];
        $album['artist_alphabetic'] = $art['artist'];
        $album['album'] = $art['title'];
        $album['cover'] = $art['cover'];
        /* if ($cfg['show_album_format']) {
          //$aq = $h->getAlbum($art['albumId']);
          $album['audio_quality'] = $aq['data']['results']['tracks'][0]['format'];
        } */
        $albumsList .= draw_tile($size, $album, '', 'string');
			}
      }
		$albumsList .= '</table>';
		$data['albums'] = $albumsList;
	}
	
	echo safe_json_encode($data);
}


//  +------------------------------------------------------------------------+
//  | Tracks from HRA album                                                  |
//  +------------------------------------------------------------------------+
function getTracksFromHraAlbum($album_id, $order = '') {
	global $cfg, $db;
	$field = 'albumTracks';
	$value = $album_id;
	 
	$h = new HraAPI;
	$h->username = $cfg["hra_username"];
	$h->password = $cfg["hra_password"];
	if (NJB_WINDOWS) $h->fixSSLcertificate();
	$conn = $h->connect();
	if ($conn === true){
		$tracks = $h->getAlbumTracks($album_id);
	}
	else {
		$data['return'] = $conn["return"];
		$data['response'] = $conn["error"];
		echo safe_json_encode($data);
		return;
	}
	
	if (!$tracks["tracks"]) {
		$data['results'] = 0;
		return safe_json_encode($data);
	}
	 
	if (count($tracks["tracks"]) > 0) {
		if ($order == 'DESC') {
			usort($tracks["tracks"], function ($a, $b) {
				return $b['trackNumber'] <=> $a['trackNumber'];
			});
		}
		return safe_json_encode($tracks);
	}
	return false;
}


//  +------------------------------------------------------------------------+
//  | New albums from HRA                                                    |
//  +------------------------------------------------------------------------+

function showNewHRAAlbumsByCategory($categoryName, $prefix) {
  $category = str_replace(" ", "_", $categoryName);
  echo '
  <h1>&nbsp;' . $categoryName . ' <a href="index.php?action=viewNewFromHRA&prefix=' . urlencode($prefix) . '&categoryName=' . urlencode($categoryName) . '">(more...)</a></h1>
	<script>
		calcTileSize();
		var size = $tileSize;
		var request = $.ajax({  
		url: "ajax-hra-new-albums.php",  
		type: "POST",
		data: { prefix: "' . $prefix . '", tileSize : size, limit : 10, offset : 0 },
		dataType: "html"
		}); 

	request.done(function(data) {
		if (data) {
			$( "#' . $category . '_hra" ).html(data);
		}
		else {
			$( "#' . $category . '_hra" ).html(\'<h1 class="">Error loading new albums from HRA.</h1>\');
		}
	});
	
	</script>
	<div class="full" id="' . $category . '_hra">
		<div style="display: grid; height: 100%;">
			<span id="albumsLoadingIndicator" style="margin: auto;">
				<i class="fa fa-cog fa-spin icon-small"></i> <span class="add-info-left">Loading albums from HighResAudio...</span>
			</span>
		</div>
	</div>
  ';
}


//  +------------------------------------------------------------------------+
//  | Check if album/track is from HRA                                       |
//  +------------------------------------------------------------------------+

function isHra($id) {
	global $cfg;
	if (strpos($id,"hra_") !== false || strpos($id,'highresaudio.com') !== false) {
		return true;
	}
	return false;
}


//  +------------------------------------------------------------------------+
//  | Get pure HRA id of item                                                |
//  +------------------------------------------------------------------------+

function getHraId($id){
		return str_replace('hra_','',$id);
}


//  +------------------------------------------------------------------------+
//  | Get HRA stream params                                                  |
//  +------------------------------------------------------------------------+

function getHRAMPDUrl($track_id){
	global $cfg;
	$h = new HraAPI;
	$h->username = $cfg["hra_username"];
	$h->password = $cfg["hra_password"];
	if (NJB_WINDOWS) $h->fixSSLcertificate();
	$conn = $h->connect();
	if ($conn === true){
		$results = $h->getTrack($track_id);
	}
	else {
		return false;
	}
  cliLog('$hraStreamUrl=' . $results["data"]["results"]["tracks"]["artist"]);
	if ($results["data"]["results"]["tracks"]) {
		$hraAlbumId = $results["data"]["results"]["tracks"]["album_id"];
		$album = $h->getAlbum($hraAlbumId);
		$results["data"]["results"]["tracks"]["productionYear"] = $album["data"]["results"]["productionYear"];
		$results["data"]["results"]["tracks"]["shop_url"] = $album["data"]["results"]["shop_url"];
		//$results["data"]["results"]["tracks"]["album_id"] = "zzzzz";
		$results["data"]["results"]["tracks"]["track_id"] = $track_id;
		$hraStreamUrl = createHRAMPDUrl($results["data"]["results"]["tracks"]);
	}
	else {
		$hraStreamUrl = false;
	}
	return $hraStreamUrl;
}


//  +------------------------------------------------------------------------+
//  | Create HRA stream params                                               |
//  +------------------------------------------------------------------------+

function createHRAMPDUrl($tracks){
	$hraArtist = $tracks["artist"];
	$hraTitle = $tracks["title"];
	$hraDuration = $tracks["playtime"];
	$hraThumbnail = "https://" . $tracks["cover"];
	$hraUrl = $tracks["url"];
	$hraGenre = $tracks["genre"];
	$hraYear = $tracks["productionYear"];
	$track_id = $tracks["track_id"];
	$album_id = $tracks["album_id"];
	$album_title = $tracks["album_title"];
	$shop_url = $tracks["shop_url"];

	//streamUrl MUST always be last in url!
	$hraStreamUrl = NJB_HOME_URL . 'stream.php?action=streamHRA&track_id=' . $track_id . '&ompd_title=' . urlencode($hraTitle) . '&ompd_duration=' . urlencode($hraDuration) . '&ompd_artist=' . urlencode($hraArtist) . '&ompd_thumbnail=' . urlencode($hraThumbnail) . '&ompd_year=' . urlencode($hraYear) . '&ompd_genre=' . urlencode($hraGenre) . '&ompd_album_id=' . urlencode($album_id) . '&ompd_album_title=' . urlencode($album_title) . '&ompd_shop_url=' . urlencode($shop_url) . '&streamUrl=' . urlencode($hraUrl);
	return $hraStreamUrl;
}

//  +------------------------------------------------------------------------+
//  | Check if album/track is from Youtube                                   |
//  +------------------------------------------------------------------------+

function isYoutube($id) {
	global $cfg;
	if (strpos($id,"youtube_") !== false) {
		return true;
	}
	else {
		$yt = striposa($id, $cfg['youtube_indicator']);
		if ($yt !== false) {
			return true;
		}
	}
	return false;
}


//  +------------------------------------------------------------------------+
//  | Get pure Youtube id of item                                            |
//  +------------------------------------------------------------------------+

function getYouTubeId($id){
	global $cfg;
	// /watch?v=tK1MqYLinQI" 
	if (strpos($id,'?v=') !== false) {
		return end(explode('?v=',$id));
	}
	//https://youtu.be/nrCf_ciAftM
	elseif (strpos($id,'youtu.be/') !== false) {
		return end(explode('youtu.be/',$id));
	}
	else {
		return str_replace('youtube_','',$id);
	}
	return false;
}




//  +------------------------------------------------------------------------+
//  | Get Youtube stream params                                              |
//  +------------------------------------------------------------------------+

function getYouTubeMPDUrl($url, $title = ''){
	global $cfg;
  //prevent 'youtube-dl: error: no such option: -O'
  if (strpos(getYouTubeId($url),'-') !== false) {
    $url = "http://www.youtube.com/watch?v=" . getYouTubeId($url);
  }
	$cmd = trim($cfg['python_path'] . ' ' . $cfg['youtube-dl_path'] . ' ' . $cfg['youtube-dl_options'] . ' "' . ($url) . '"');
	exec($cmd, $output, $ret);
	if ($ret == 0) {
		$js = json_decode($output[0],true);
		$id = $js['id'];
		$f = $cfg['youtube_audio_format_name'];
		preg_match_all('!\d+!', $f, $matches_f);
		
		$format = array_search($matches_f[0], array_column($js['formats'], 'format_id'));
		
		//$format = array_search($f, array_column($js['formats'], 'format'));

		if (isset($js['formats'][$format]['fragment_base_url'])){
			$yt_url = $js['formats'][$format]['fragment_base_url'];
		}else {
			$yt_url = $js['formats'][$format]['url'];
		}
		
		$ytArtist = "";
		if ($js['artist']) {
			$ytArtist = $js['artist'];
		}
		$ytTitle = "YouTube audio";
		if ($title){
			$ytTitle = $title;
		}
		
		if ($js['track'] && $js['track'] != '_') {
			$ytTitle = $js['track'];
		}
		elseif ($js['title'] && $js['title'] != '_') {
			$ytTitle = $js['title'];
		}
		elseif ($js['fulltitle'] && $js['fulltitle'] != '_') {
			$ytTitle = $js['fulltitle'];
		}
		elseif ($js['alt_title'] && $js['alt_title'] != '_') {
			$ytTitle = $js['alt_title'];
		}
		
		if (strpos($ytTitle," - ") !== false && !$ytArtist){
			$t = explode(" - ",$ytTitle);
			$ytArtist = $t[0];
		}
		if (strpos($js['title']," - ") !== false){
			//for 'title - artist' and when 'track' is defined but != then in 'title'
			//e.g.: https://www.youtube.com/watch?v=IeDMnyQzS88
			if (strpos($js['title'],$ytArtist) !== false){
				$ytTitle = str_replace($ytArtist,"",$js['title']);
				$ytTitle = str_replace(" - ","",$ytTitle);
			}
			else { 
				$t = explode(" - ",$js['title']);
				if ($ytTitle != $t[1]){
					$ytTitle = $t[1];
				}
			}
		}
		
		$ompd_title = $ytTitle;
		if ($ytArtist && strpos($ompd_title,$ytArtist) !== false){
			$ompd_title = str_replace($ytArtist . " - ","",$ytTitle);
		}
		
		$ytYear = "";
		if ($js['release_year']) {
			$ytYear = $js['release_year'];
		}
		else {
			$ytYear = substr($js['upload_date'],0,4);
		}
    $thumb = parse_url($js['thumbnail']);
    //cut thumb url for string like:
    //https://i.ytimg.com/vi/vPYFWnzjIy0/hqdefault.jpg?sqp=-oaymwEZCNACELwBSFXyq4qpAwsIARUAAIhCGAFwAQ==&rs=AOn4CLBlGIaJTB1qHkM1vuoNyUsCwpufyA
    $thumb = $thumb['scheme'] . "://" . $thumb['host'] . "/" . $thumb['path'];
		//streamUrl MUST always be last in url!
		$ytStreamUrl = NJB_HOME_URL . 'stream.php?action=streamYouTube&track_id=' . $id . '&ompd_title=' . urlencode($ompd_title) . '&ompd_duration=' . urlencode($js['duration']) . '&ompd_artist=' . urlencode($ytArtist) . '&ompd_thumbnail=' . urlencode($thumb) . '&ompd_year=' . urlencode($ytYear) . '&ompd_webpage=' . urlencode($js['webpage_url']) . '&streamUrl=' . urlencode($yt_url);
	}
	else {
		$ytStreamUrl = false;
	}
	return $ytStreamUrl;
}



//  +------------------------------------------------------------------------+
//  | Get Youtube stream URL                                                 |
//  +------------------------------------------------------------------------+

function getYouTubeStreamUrl($url){
	global $cfg;
	$cmd = trim($cfg['python_path'] . ' ' . $cfg['youtube-dl_path'] . ' ' . $cfg['youtube-dl_options'] . ' ' . ($url));
	exec($cmd, $output, $ret);
	if ($ret == 0) {
		$js = json_decode($output[0],true);
		$f = $cfg['youtube_audio_format_name'];
		preg_match_all('!\d+!', $f, $matches_f);
		$format = array_search($matches_f[0], array_column($js['formats'], 'format_id'));
		if (isset($js['formats'][$format]['fragment_base_url'])){
			$yt_url = $js['formats'][$format]['fragment_base_url'];
		}else {
			$yt_url = $js['formats'][$format]['url'];
		}
		/* $is_yt_url_query = strpos($yt_url,'?');
		if ($is_yt_url_query === false) {
			$yt_url = $yt_url . '?';
		} */
		$ytStreamUrl = $yt_url;
	}
	else {
		$ytStreamUrl = false;
	}
	return $ytStreamUrl;
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
  
  $source = implode(', ', $extensions);
	//$source = implode($extensions, ', ');
	
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
	if (isset($genre['genre'])) {
		$nav['name'][] = 'Genre';
		$nav['url'][]  = 'index.php?&action=viewGenre';
		$nav['name'][] = $genre['genre'];
		$nav['url'][]  = '';
	}
	/* $nav['name'][] = 'Show favorites '  . $genre['genre'] . ' tracks';
	$nav['url'][]  = 'search.php?action=fav4genre&genre_id=' . $genre_id; */
	$nav['open'] = true;
	
	//echo ('Show favorites tracks for ' . $genre['genre']);
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
	//$string = str_replace("/","ompdslashompd",$string);
	$bbcode = array(
		'#\[br\]#s',
		'#\[b\](.*?)\[/b\]#s',
		'#\[i\](.*?)\[/i\]#s',
		'#\[img\]([a-z_]+\.png)\[/img\]#s',
		'#\[url=([a-z]+\.php(?:\?.*)?)\](.*?)\[/url\]#s',
		'#\[url\]((?:http|https|ftp)://.*?)\[/url\]#s',
		'#\[url=((?:http|https|ftp)://.*?)\](.*?)\[/url\]#s',
		'#\[email\]([a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4})\[/email\]#si');
	$replace = array(
		'<br>',
		'<strong>$1</strong>',
		'<em>$1</em>',
		'<img src="' . $cfg['img'] . '$1" alt="" class="small">',
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
//  | Replace ' and ', ' & ', ' + ' with '%' for sql query                   |
//  +------------------------------------------------------------------------+

function replaceAnds($artist){
	global $cfg;
	$artist = strtolower($artist);
	$artist = str_replace(" and ", "%", $artist);
	$artist = str_replace(" & ", "%", $artist);
	$artist = str_replace(" + ", "%", $artist);
	return $artist;
}




//  +------------------------------------------------------------------------+
//  | Escape ", & for use in TIDAL search                                    |
//  +------------------------------------------------------------------------+

function tidalEscapeChar($str1){
	
	$str1 = str_replace('"','',$str1);
	$str1 = str_replace("'",'',$str1);
	$str1 = str_replace('&','',$str1);
	$str1 = str_replace(' and ','  ',$str1);
	$str1 = str_replace('+','',$str1);
	
	return $str1;
}



//  +------------------------------------------------------------------------+
//  | Replace 'The Beatles' with 'Beatles, The'                              |
//  +------------------------------------------------------------------------+

function moveTheToEnd($artist){
	global $cfg;
	if ($cfg['testing'] == 'on') {
		//$artist = urldecode($artist);
		if (strtolower(substr( $artist, 0, 4 )) == "the ") {
			$artist = str_replace("the ", "", strtolower($artist));
			$artist = $artist . ", the";
		}
	}
	return $artist;
}




//  +------------------------------------------------------------------------+
//  | Replace 'Beatles, The' with 'The Beatles'                              |
//  +------------------------------------------------------------------------+

function moveTheToBegining($artist){
	global $cfg;
	if ($cfg['testing'] == 'on') {
		//$artist = urldecode($artist);
		if (strtolower(substr( $artist, -5 )) == ", the") {
			$artist = str_replace(", the", "", strtolower($artist));
			$artist = "the " . $artist;
		}
	}
	return $artist;
}


//  +------------------------------------------------------------------------+
//  | Check if artist name contains 'the'                                    |
//  +------------------------------------------------------------------------+

function hasThe($artist){
	global $cfg;
	$hasThe = false;
	if (strtolower(substr( $artist, 0, 4 )) == "the " || strtolower(substr( $artist, -5 )) == ", the") {
		$hasThe = true;
	}
	return $hasThe;
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
		'aiff' => 'audio/aiff',
		'mp3' => 'audio/mpeg',
		'mp2' => 'audio/mpeg',
		'mpc' => 'audio/mpeg',
		'ogg' => 'audio/ogg', 
		'oga' => 'audio/ogg', 
		'ape' => 'audio/ape', 
		'dsf' => 'audio/dsf', 
		'dff' => 'audio/dff', 
		'flac' => 'audio/flac', 
		'wv' => 'audio/wv', 
		'wav' => 'audio/wav', 
		//'iso' => 'audio/wav',
		'wma' => 'audio/x-ms-wma',
		'aac' => 'audio/aac',
		'm4a' => 'audio/m4a',
		'm4b' => 'audio/m4b'
	);

	$exploded = explode('.',$filename);
	$ext = strtolower(array_pop($exploded));
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
	if ($result){
	array_walk(
		$result,
		function (&$entry) {
			$entry = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $entry);
		}
	);
};

    return $result;
} 




//  +------------------------------------------------------------------------+
//  | Gropuping of multidisc albums                                          |
//  +------------------------------------------------------------------------+

function albumMultidisc($query, $rp =''){
	global $cfg, $db;
	$album_multidisc = array();
	$mdTab = array();
	$i = 0;
	while ($album = mysqli_fetch_assoc($query)) {		
		$multidisc_count = 0;
		if ($album) {
			if ($cfg['group_multidisc'] == true && $rp == '') {
				$md_indicator = striposa($album['album'], $cfg['multidisk_indicator']);
				if ($md_indicator !== false) {
					$md_ind_pos = stripos($album['album'], $md_indicator);
					$md_title = substr($album['album'], 0, $md_ind_pos);
					$query_md = mysqli_query($db, 'SELECT album, image_id, album_id, genre_id 
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
					'year' => isset($album['year']) ? $album['year'] : null,
					'genre_id' => isset($album['genre_id']) ? $album['genre_id'] : null,
					'allDiscs' => 'allDiscs'
					);
				}
			}
			//TODO: check if not used anymore
			else {			
				if ($rp == 'rp') {
					$album_multidisc[$album['played_time'] . '_' . $album['album_id']] = array(
						'album_id' => $album['album_id'],
						'image_id' => $album['image_id'],
						'album' => $album['album'],
						'artist_alphabetic' => $album['artist_alphabetic'],
						'year' => $album['year'],
						'genre_id' => $album['genre_id'],
						'allDiscs' => '',
						'played_time' => $album['played_time']
						);
				}
				else {
					$album_multidisc[$album['album_add_time'] . '_' . $album['album_id']] = array(
						'album_id' => $album['album_id'],
						'image_id' => $album['image_id'],
						'album' => $album['album'],
						'artist_alphabetic' => $album['artist_alphabetic'],
						'year' => isset($album['year']) ? $album['year'] : null,
						'genre_id' => isset($album['genre_id']) ? $album['genre_id'] : null,
						'allDiscs' => ''
						);
				}
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
		
		//rebuild genre tab to avoid wrong sort in case of multi-genre albums
		$query = mysqli_query($db,'SELECT * FROM genre ORDER BY genre');	
		$genTab = array();
		$i = 1;
		while ($genres = mysqli_fetch_assoc($query)) {
			$genTab[$i] = $genres['genre'];
			$i++;
		}
		mysqli_query($db,'TRUNCATE genre');
		foreach ($genTab as $key => $value){
			mysqli_query($db,'INSERT INTO genre (genre_id, genre, updated)
									VALUES ("' . $key . '",
											"' . $db->real_escape_string($value) . '",
											1)');
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
						$genresSort = explode(';',ltrim($album_genre_ids,';'));
						asort($genresSort);
						$album_genre_ids = '';
						foreach($genresSort as $key => $value){
							$album_genre_ids = $album_genre_ids . $value . ';';
						}
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


//  +------------------------------------------------------------------------+
//  | Choose right config file to edit                                       |
//  +------------------------------------------------------------------------+

function choose_config_file()
{
    if (file_exists('include/config.local.inc.php')) {
			return 'include/config.local.inc.php';
		}
		
		if (file_exists('include/config.inc.php')) {
			return 'include/config.inc.php';
		}

    return 'not_found';
}


//  +------------------------------------------------------------------------+
//  | Check if jpg image is correct                                          |
//  | by  willertan1980 at yahoo dot com                                     |
//  +------------------------------------------------------------------------+

function is_jpg($f){
# check for jpg file header and footer
    if ( false !== (@$fd = fopen($f, 'r' )) ){
			if ( fread($fd,2)==chr(255).chr(216) ){
				fseek ( $fd, -2, SEEK_END );
				if ( fread($fd,2)==chr(255).chr(217) ){
					fclose($fd);
					return true;
				}
				else {
					fclose($fd);
					return false;
				}
			}
			else {
				fclose($fd); 
				return false;
			}
    }
		else {
			return false;
    }
}


//  +------------------------------------------------------------------------+
//  | Check if file is png image                                             |
//  +------------------------------------------------------------------------+

function is_png($filename){
	if ((list($width, $height, $type, $attr) = getimagesize($filename)) !== false ) {
		if ($type == 3) {
			return true;
		}
	}
	return false;
}




//  +------------------------------------------------------------------------+
//  | Get average color by Luciano Ropero                                    |
//  +------------------------------------------------------------------------+

function getAverageColor($img) {
    $w = imagesx($img);
    $h = imagesy($img);
    $r = $g = $b = 0;
    for($y = 0; $y < $h; $y++) {
        for($x = 0; $x < $w; $x++) {
            $rgb = imagecolorat($img, $x, $y);
            $r += $rgb >> 16;
            $g += $rgb >> 8 & 255;
            $b += $rgb & 255;
        }
    }
    $pxls = $w * $h;
    $r = dechex(round($r / $pxls));
    $g = dechex(round($g / $pxls));
    $b = dechex(round($b / $pxls));
    if(strlen($r) < 2) {
        $r = 0 . $r;
    }
    if(strlen($g) < 2) {
        $g = 0 . $g;
    }
    if(strlen($b) < 2) {
        $b = 0 . $b;
    }
    return "#" . $r . $g . $b;
}



//  +------------------------------------------------------------------------+
//  | Update favorite stream status                                          |
//  +------------------------------------------------------------------------+

function updateFavoriteStreamStatus($favorite_id) {
	global $cfg, $db;
  if ($favorite_id == $cfg['favorite_id'] || $favorite_id == $cfg['blacklist_id']) {
    //ensure that Favorites and Blacklist are not marked as stream playlists
    $files = true;
    $streams = false;
  }
  else {
    $files = false;
    $streams = false;
    $stream = 0;
    $query = mysqli_query($db,"SELECT track_id FROM favoriteitem WHERE favorite_id = '" . $favorite_id . "' AND track_id <> ''");
    if (mysqli_num_rows($query) > 0) {
      $files = true;
    }
    $query = mysqli_query($db,"SELECT stream_url FROM favoriteitem WHERE favorite_id = '" . $favorite_id . "' AND stream_url <> ''");
    if (mysqli_num_rows($query) > 0) {
      $streams = true;
    }
    if ($files) {
      $stream = 0;
    }
    if (!$files && $streams) {
      $stream = 1;
    }
  }

	mysqli_query($db,'UPDATE favorite
					SET stream			= "' . (int) $stream . '"
					WHERE favorite_id	= ' . (int) $favorite_id);
	
	return $stream;
}


//  +------------------------------------------------------------------------+
//  | Get minimal required url from long mpd 'file' field                    |
//  +------------------------------------------------------------------------+

function getTrackMpdUrl($track_mpd_url) {
	if ($track_mpd_url) {
		$parts = parse_url($track_mpd_url);
		parse_str(isset($parts['query']) ? $parts['query'] : '', $query);
		$action = urldecode(isset($query['action']) ? $query['action'] : '');
		if ($action == 'streamYouTube' && strpos($track_mpd_url,"&streamUrl=") !== false) {
			$track_mpd_url = substr($track_mpd_url, 0, strpos($track_mpd_url, "&streamUrl="));
		}
	}
	return $track_mpd_url;
}


//  +------------------------------------------------------------------------+
//  | Get track_id from url                                                  |
//  +------------------------------------------------------------------------+

function getTrackIdFromUrl($track_mpd_url) {
	$parts = parse_url($track_mpd_url);
	parse_str(isset($parts['query']) ? $parts['query'] : '', $query);
	$track_id = urldecode(isset($query['track_id']) ? $query['track_id'] : '');
	if ($track_id) {
		return $track_id;
	}
	return '';
}

//  +------------------------------------------------------------------------+
//  | Create stream url for streaming services for playing in mpd            |
//  +------------------------------------------------------------------------+

function createStreamUrlMpd($track_id) {
	global $cfg, $db;
	$stream_url_mpd = '';
	if (isTidal($track_id)){
		if ($cfg['tidal_direct']) {
			$stream_url_mpd = NJB_HOME_URL . 'stream.php?action=streamTidal&track_id=' . getTidalId($track_id);
		}
		elseif ($cfg['upmpdcli_tidal']) {
			$stream_url_mpd = $cfg['upmpdcli_tidal'] .  getTidalId($track_id);
		}
		else {
			$stream_url_mpd = MPD_TIDAL_URL . getTidalId($track_id);
		}
	}
  if (isHra($track_id)) {
    $stream_url_mpd = getHRAMPDUrl(getHraId($track_id));
  }
	elseif (isYoutube($track_id)){
		$stream_url_mpd = getYouTubeMPDUrl(getYouTubeId($track_id));
		$stream_url_mpd = getTrackMpdUrl($stream_url_mpd);
	}
	
	return $stream_url_mpd;
}


//  +------------------------------------------------------------------------+
//  | Check if track is in favorite                                          |
//  +------------------------------------------------------------------------+

function isInFavorite($track_id, $favorite_id) {
	global $cfg, $db;
	$inFavorite = false;
	if (isTidal($track_id)){
		$track_id = getTidalId($track_id);
		$query = mysqli_query($db,"SELECT position FROM favoriteitem WHERE favorite_id = '" . $favorite_id . "' AND (stream_url LIKE '%action=streamTidal&track_id=" . $track_id . "' OR stream_url = '" . mysqli_real_escape_string($db,$cfg['upmpdcli_tidal']) . $track_id . "' OR stream_url LIKE '" .mysqli_real_escape_string($db,MPD_TIDAL_URL) . $track_id . "')");
		if (mysqli_num_rows($query) > 0) $inFavorite = true;
	}
	elseif (isHra($track_id)){
		$track_id = getHraId($track_id);
		$query = mysqli_query($db,"SELECT position FROM favoriteitem WHERE favorite_id = '" . $favorite_id . "' AND stream_url LIKE '%action=streamHRA&track_id=" . $track_id . "%'");
		if (mysqli_num_rows($query) > 0) $inFavorite = true;
	}
	elseif (isYoutube($track_id)){
		$track_id = getYouTubeId($track_id);
		$query = mysqli_query($db,"SELECT position FROM favoriteitem WHERE favorite_id = '" . $favorite_id . "' AND stream_url LIKE '%action=streamYouTube&track_id=" . $track_id . "%'");
		if (mysqli_num_rows($query) > 0) $inFavorite = true;
	}
	else{
		$query = mysqli_query($db,"SELECT position FROM favoriteitem WHERE favorite_id = '" . $favorite_id . "' AND track_id = '" . $track_id . "'");
		if (mysqli_num_rows($query) > 0) $inFavorite = true;
	}
	
	return $inFavorite;
}

//  +------------------------------------------------------------------------+
//  | Calculate album format                                                 |
//  +------------------------------------------------------------------------+
function calculateAlbumFormat($album_information, $hra_tag = "") {
  if ($hra_tag) {
    $format = '';
    switch(strtolower($hra_tag)) {
      case 'fl441':
      case '44.1':
        $format = '24/44';
        break;
      case 'fl48':
      case '48':
        $format = '24/48';
        break;
      case 'fl882':
      case '88.2':
        $format = '24/88';
        break;
      case 'fl96':
      case '96':
        $format = '24/96';
        break;
      case 'fl1764':
      case '176.4':
        $format = '24/176';
        break;
      case 'fl192':
      case '192':
        $format = '24/192';
        break;
      case 'mqa':
        $format = 'MQA';
        break;
    }
    return $format;
  }
	if (isset($album_information['audio_quality'])) {
		if (isTidal($album_information['album_id'])) {
		switch (strtolower($album_information['audio_quality'])){
			case "high":
			case "lossless":
				return "CD";
			case "hi_res":
				return "Master";
			default: 
				return $album_information['audio_quality'];
			}
		}
		elseif (isHra($album_information['album_id'])) {
			return "24/" . round($album_information['audio_quality'],0);
		}
	}
	elseif (strpos($album_information['audio_profile'],'Lossless') === false) {
		if ($album_information['audio_dataformat']) {
      return $album_information['audio_dataformat'];
    }
    else {
      return "UNKNOWN";
    }
	}
	elseif (stripos($album_information['audio_encoder'],'mqa') !== false) {
		return "MQA";
	}
	elseif (strpos($album_information['audio_profile'],'Lossless') !== false && $album_information['audio_sample_rate'] == '44100' && $album_information['audio_bits_per_sample'] == '16') {
		return "CD";
	}
	elseif ($album_information['audio_dataformat'] == 'dsf') {
		switch ($album_information['audio_sample_rate']) {
			case '2822400':
				return "DSD64";
			case '5644800':
				return "DSD128";
			case '11289600':
				return "DSD256";
			case '22579200':
				return "DSD512";
			default:
				return "DSF";
		}
	}
	elseif ($album_information['audio_sample_rate'] >= '44100' && $album_information['audio_bits_per_sample'] >='16') {
		return $album_information['audio_bits_per_sample'] . '/' . round($album_information['audio_sample_rate']/1000,0);
	}
	else {
		return "UNKNOWN";
	}
}

//  +------------------------------------------------------------------------+
//  | Set config item in DB and load into $cfg                               |
//  +------------------------------------------------------------------------+
function setConfigItem($name, $value, $default_value = '') {
	global $cfg, $db;
	
	$query = mysqli_query($db, "SELECT * FROM config WHERE name='$name'");
  $items_count = mysqli_num_rows($query);
  if ($items_count > 0) {
    $config = mysqli_fetch_assoc ($query);
    $cfg[$name] = $config['value'];
  }
  else {
    if ($value) {
      $sql = "INSERT INTO config (name, value) VALUES ('" . $db->real_escape_string($name) ."', '" . $db->real_escape_string($value) . "')";
      mysqli_query($db, $sql);
      $cfg[$name] = $value;
    }
    else {
      $sql = "INSERT INTO config (name, value) VALUES ('" . $db->real_escape_string($name) ."', '" . $db->real_escape_string($default_value) . "')";
      mysqli_query($db, $sql);
      $cfg[$name] = $default_value;
    }
  }
}


?>
