<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015 Artur Sierzant                                 |
//  | http://www.ompd.pl                                                     |
//  |                                                                        |
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



require_once('include/initialize.inc.php');
require_once('include/library.inc.php');
global $cfg, $db;
authenticate('access_favorite');

$data = array();

$action = $_POST['action'];
$favorite_id = $_POST['favorite_id'];
$fromPosition = $_POST['fromPosition'];
$toPosition = $_POST['toPosition'];
$isMoveToTop = $_POST['isMoveToTop'];



$query = mysqli_query($db,"SELECT position FROM favoriteitem WHERE favorite_id = '" . (int) $favorite_id . "' ORDER BY position");
	$favoriteitems_count = mysqli_num_rows($query);
	//re-numbering all items from 1 to [favoriteitems_count]
	$i = 1;
	while ($favoriteitem = mysqli_fetch_assoc($query)) {
		$pos = $favoriteitem['position'];
		mysqli_query($db,'UPDATE favoriteitem
			SET position	= "' . $i . '"
			WHERE position	= "' . $pos . '" 
			AND favorite_id = "' . $favorite_id . '"');
		$i++;
	}

	
	
$query = mysqli_query($db,'SELECT name, comment, stream FROM favorite WHERE favorite_id = ' . (int) $favorite_id);
	$favorite = mysqli_fetch_assoc($query);	

if ($action == 'moveItem') {
	
	mysqli_query($db,'UPDATE favoriteitem
				SET position	= "65535"
				WHERE position	= "' . $fromPosition . '"
				AND favorite_id = "' .$favorite_id . '"');

	if ($isMoveToTop == 'true') {
		
		/* mysqli_query($db,'UPDATE favoriteitem
				SET position	= "65535"
				WHERE position	= "' . $fromPosition . '"
				AND favorite_id = "' .$favorite_id . '"'); */
		
		//$query = mysqli_query($db,"SELECT position FROM favoriteitem WHERE favorite_id = '" .$favorite_id . "' AND position != '65535' ORDER BY position");

		mysqli_query($db,'UPDATE favoriteitem
				SET position	= position + 1
				WHERE favorite_id = "' .$favorite_id . '"
				AND position != "65535"');
		mysqli_query($db,'UPDATE favoriteitem
				SET position	= "1"
				WHERE favorite_id = "' .$favorite_id . '"
				AND position = "65535"');
		
	}
	else {
		/* mysqli_query($db,'UPDATE favoriteitem
				SET position	= "65535"
				WHERE position	= "' . $fromPosition . '"
				AND favorite_id = "' .$favorite_id . '"'); */
		
		//$query = mysqli_query($db,"SELECT position FROM favoriteitem WHERE favorite_id = '" .$favorite_id . "' AND position != '65535' AND position < '" . $fromPosition . "' AND position >= '" . $toPosition . "' ORDER BY position");

		mysqli_query($db,'UPDATE favoriteitem
				SET position	= position + 1
				WHERE favorite_id = "' .$favorite_id . '"
				AND position != "65535" AND position > "' . $toPosition . '"');
		mysqli_query($db,'UPDATE favoriteitem
				SET position	= "' . ($toPosition + 1) . '"
				WHERE favorite_id = "' .$favorite_id . '"
				AND position = "65535"');
	}
}
elseif ($action == 'removeItem') {
	mysqli_query($db,'DELETE FROM favoriteitem WHERE
				position = "' . mysqli_real_escape_string($db,$fromPosition) . '"
				AND favorite_id = "' . mysqli_real_escape_string($db,$favorite_id) . '"');
	
	/* $query = mysqli_query($db,'SELECT track_id FROM favoriteitem WHERE track_id <> "" AND favorite_id = "' . mysqli_real_escape_string($db,$favorite_id) . '"');
	//if favorite contains files, change stream to 0
	if (mysqli_num_rows($query) > 0) {
		$stream = 0;
	}
	else {
		$query = mysqli_query($db,'SELECT stream_url FROM favoriteitem WHERE stream_url <> "" AND favorite_id = "' . mysqli_real_escape_string($db,$favorite_id) . '"');
		//if favorite contains only streams, change stream to 1
		if (mysqli_num_rows($query) > 0) {
			$stream = 1;
		}
	}
	mysqli_query($db,'UPDATE favorite
				SET stream			= "' . (int) $stream . '"
				WHERE favorite_id	= ' . (int) $favorite_id); */
	updateFavoriteStreamStatus($favorite_id);
}

?>
<script type="text/javascript">
/* $('[id^="add_"]').click(function(){
		addClick();
	}); */
</script>
<table width="100%" cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	
	<td class="icon"></td><!-- optional play -->
	<td><?php echo $favorite['stream'] ? 'Stream/title' : 'Title' ?></td>
	<td class="_delArtist"><?php echo $favorite['stream'] ? 'Artist' : 'Artist' ?></td>
	<td class="_delSource center">Source</td>
	<td class="_delFrom">Album</td>
	<td></td><!-- delete -->
</tr>
<?php
	//$cfg['access_play'] = true;
	$i = 0;
	$delArtist = true;
	$delSource = true;
	$delFrom = true;
	$query1 = mysqli_query($db,'SELECT track_id, stream_url, position FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id . ' ORDER BY position');
	while ($favoriteitem = mysqli_fetch_assoc($query1)) {
		$extUrl = '';
		$album = '';
		$album_id = '';
		$title = '';
		if ($favoriteitem['track_id']) {
			
			$query2	= mysqli_query($db,'SELECT track.artist, track.title, album.album, album.album_id FROM track LEFT JOIN album ON track.album_id = album.album_id WHERE track_id = "' . mysqli_real_escape_string($db,$favoriteitem['track_id']) . '"');
			$track	= mysqli_fetch_assoc($query2);
			$artist	= $track['artist'];
			$title	= $track['title'];
			$album	= $track['album'];
			$album_id	= $track['album_id'];
			$delArtist = false;
			$delFrom = false;
		}
		elseif ($favoriteitem['stream_url']) {
			$artist	= $favoriteitem['stream_url'];
			$parts = parse_url($artist);
			parse_str($parts['query'], $queryUrl);
			$a = $queryUrl['action'];
			if ($a == 'streamYouTube') {
				$artist = $queryUrl['ompd_artist'];
				$title = $queryUrl['ompd_title'];
				$extUrl = $queryUrl['ompd_webpage'];
				$delArtist = false;
				$delSource = false;
			}
      elseif ($a == 'streamHRA') {
				$artist = $queryUrl['ompd_artist'];
				$title = $queryUrl['ompd_title'];
				$album = $queryUrl['ompd_album_title'];
				//$extUrl = $queryUrl['ompd_webpage'];
        $extUrl = NJB_HOME_URL . "index.php?action=view3&album_id=hra_". $queryUrl['ompd_album_id'];
				$extUrlSource = $queryUrl['ompd_shop_url'];
				$delArtist = false;
				$delSource = false;
			}
			elseif ($a == 'streamTidal' || isTidal($favoriteitem['stream_url'])){
				if (isTidal($favoriteitem['stream_url'])){ //Tidal by mpd or upmpdcli
					$tid = getTidalId($favoriteitem['stream_url']);
				}
				else { //Tidal direct
					$tid = $queryUrl['track_id'];
				}
				$queryT = mysqli_query($db,"SELECT tidal_track.artist, tidal_track.title, tidal_track.album_id, album FROM tidal_track LEFT JOIN tidal_album ON tidal_track.album_id = tidal_album.album_id WHERE track_id = '" . (int) $tid . "'");
				$tidalItem = mysqli_fetch_assoc($queryT);
				$artist = $tidalItem['artist'];
				$title = $tidalItem['title'];
				$album = $tidalItem['album'];
				$extUrl = NJB_HOME_URL . "index.php?action=view3&album_id=tidal_". $tidalItem['album_id'];
				$extUrlSource = TIDAL_ALBUM_URL . $tidalItem['album_id'];
				$delArtist = false;
				$delSource = false;
				$delFrom = false;
			}
			elseif ($a == 'streamTo'){
				if ($tid = $queryUrl['track_id']){ //local files played as streams
					$query2	= mysqli_query($db,'SELECT track.artist, track.title, album.album, album.album_id FROM track LEFT JOIN album ON track.album_id = album.album_id WHERE track_id = "' . mysqli_real_escape_string($db,$tid) . '"');
					$track	= mysqli_fetch_assoc($query2);
					$artist	= $track['artist'];
					$title	= $track['title'];
					$album	= $track['album'];
					$album_id	= $track['album_id'];
				}
				elseif ($filePath = $queryUrl['filepath']){ //local files not added to DB
					$artist = '';
					$title = $filePath;
				}
			}
			else {
				$artist = '';
				$title = $favoriteitem['stream_url'];
			}
		}
		
		$bottom = mysqli_num_rows($query1);
		?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover" id="track<?php echo $i; ?>" style="display:table-row;">
	
	<td><?php if ($cfg['access_play'] && $favoriteitem['track_id']) {
		echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;&amp;track_id=' . $favoriteitem['track_id'] . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();"><i  id="add_' . $favoriteitem['track_id'] . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';
	}
	else if ($cfg['access_play'] && $favoriteitem['stream_url']) {
		echo '<a href="javascript:ajaxRequest(\'play.php?action=addStreamDirect&amp;position=' . $favoriteitem['position'] . '&amp;favorite_id=' . $favorite_id . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Add stream\');" onMouseOut="return nd();"><i  id="add_' . $favoriteitem['position'] . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';
	}
	
	?></td>
	<?php 
	$title_array = explode(" ", $title);
	$lengths = array_map('strlen', $title_array);
	if (max($lengths) > 9) {
		$break_method = 'break-all';
	} 
	else {
		$break_method = 'break-word';
	}
	?>
	<td class="<?php echo $break_method;?>"><?php 
	if ($cfg['access_play'] && $favoriteitem['track_id']) {
		echo '<a id="fav_play_track' . $favoriteitem['track_id'] . '" href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $favoriteitem['track_id'] . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'play track\');" onMouseOut="return nd();">' . html($title) . '</a>';
	}
	elseif ($cfg['access_play'] && $favoriteitem['stream_url']) {
    if (isRadio($favoriteitem['stream_url'])){
      $uuid = getRadioId($favoriteitem['stream_url']);
      $radio = getRadioById($uuid);
      $title = $favoriteitem['stream_url'];
      if ($radio){
        $title = $radio['name'] . '<br><span style="font-size: smaller;">' . $radio['url'] . '</span>';
      }
    }
		echo '<a id="fav_play_track' . $favoriteitem['position'] . '" href="javascript:ajaxRequest(\'play.php?action=playStreamDirect&amp;playAfterInsert=yes&amp;position=' . $favoriteitem['position'] . '&amp;favorite_id=' . $favorite_id . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Play stream\');" onMouseOut="return nd();">' . ($title) . '</a>';
	}
	else echo html($title); ?>
	</td>
	<?php 
	$artist_array = explode(" ", $artist);
	$lengths = array_map('strlen', $artist_array);
	if (max($lengths) > 9) {
		$break_method = 'break-all';
	} 
	else {
		$break_method = 'break-word';
	}
	?>
	<td class="<?php echo $break_method;?> _delArtist">
	<a href="index.php?action=view2&artist=<?php echo rawurlencode($artist); ?>">
	<?php echo html($artist); ?>
	</a>
	</td>
	<td class="_delSource center">
	<?php
  if ($extUrl && $a == 'streamYouTube') {
  echo '<a href="' . $extUrl . '" target="_blank"><i class="fa fa-youtube-play fa-fw icon-small"></i></a>';
	}
  elseif ($extUrl && $a == 'streamHRA') {
    echo '<a href="' . $extUrlSource . '" target="_blank">' . HRA_LOGO . '</a>';
	}
	elseif ($extUrl && ($a == 'streamTidal' || isTidal($favoriteitem['stream_url']))) {
		echo '<a href="' . $extUrlSource . '" target="_blank"><i class="ux ico-tidal icon-small fa-fw"></i></a>';
	}
	?>
	</td>
  <?php
  	$album_array = explode(" ", $album);
    $lengths = array_map('strlen', $album_array);
    if (max($lengths) > 9) {
      $break_method = 'break-all';
    } 
    else {
      $break_method = 'break-word';
    }
  ?>
	<td class="<?php echo $break_method;?> _delFrom">
	<?php
  if ($extUrl && $a == 'streamYouTube') {
    echo '';
	}
  elseif ($extUrl && $a == 'streamHRA') {
		echo '<a href="' . $extUrl . '">' . $album . '</a>';
	}
	elseif ($extUrl && ($a == 'streamTidal' || isTidal($favoriteitem['stream_url']))) {
		echo '<a href="' . $extUrl . '">' . $album . '</a>';
	}
	else {
		echo '<a href="index.php?action=view3&album_id=' . $album_id . '">' . $album . '</a>';
	}
	?>
	</td>
	<td align="right" class="iconDel" style="position: relative">
		<div  id="menu-icon-div<?php echo $i ?>" <?php echo 'onclick="toggleMenuSub(' . $i . ');"'; ?>>
			<i id="menu-icon<?php echo $i ?>" class="fa fa-fw fa-bars sign"></i>
		</div>
		<div  id="menu-insert-div<?php echo $i ?>" style="display: none; position: absolute; bottom: 0;" onclick="arrangeFavItem(<?php echo $i ?>,-1,false, <?php echo $favorite_id; ?>)">
			<i class="fa fa-fw fa-angle-down sign"></i>
		</div>
	</td>
</tr>

<tr id="track-line<?php echo $i; ?>" class="line"><td colspan="12"></td></tr>

<tr id="track-menu<?php echo $i; ?>">
	<td colspan="12">
		<div class="menuSubRight" id="menu-sub-track<?php echo $i ?>" onclick='offMenuSub(<?php echo $i ?>);'> 
		
		<div class="icon-anchor" id="track<?php echo $i; ?>_delete" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:arrangeFavItem(-1,' . $i . ',false,' . $favorite_id . ');"'; ?>>
		Remove <i class="fa fa-times-circle fa-fw icon-small"></i></a>
		</div>
		
		<div class="icon-anchor" id="track<?php echo $i; ?>_move_top" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:arrangeFavItem(1,' . $i . ',true,' . $favorite_id . ');"'; ?>>Move to top<i class="fa fa-long-arrow-up fa-fw icon-small"></i></div>
		<div class="icon-anchor" id="track<?php echo $i; ?>_move" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:toggleInsert(\'on\',' . $i . ',false,' . $favorite_id . ')"'; ?>>Move <i class="fa fa-arrows-v fa-fw icon-small"></i></div>
		<div class="icon-anchor" id="track<?php echo $i; ?>_move_bottom" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:arrangeFavItem(' . $bottom . ',' . $i . ',false,' . $favorite_id . ');"'; ?>>Move to bottom<i class="fa fa-long-arrow-down fa-fw icon-small"></i></div>
		</div>
		
	</td>
</tr>
<?php
	//if ($artist) $delArtist = false;
	//if ($extUrl) $delFrom = false;
	} 
	?>
</table>
<script>
<?php
if ($delArtist) {
	echo ("$('._delArtist').hide();");
}
if ($delSource) {
	echo ("$('._delSource').hide();");
}
if ($delFrom) {
	echo ("$('._delFrom').hide();");
}
?>
setAnchorClick();
</script>
	
