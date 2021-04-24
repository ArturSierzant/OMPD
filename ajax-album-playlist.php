<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2020 Artur Sierzant                            |
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

authenticate('access_media');

$album_id = $_POST['album_id'];
$image_id = $_POST['image_id'];
$total_time = $_POST['total_time'];
$tracksList = json_decode($_POST['tracks'],true);
$showGenre = false;


if (!isHra($album_id)){
	$query = mysqli_query($db,'SELECT genre FROM track WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '" GROUP BY genre');
	if (mysqli_num_rows($query) > 1) $showGenre = true;
}
$disc = 1;
$max_disc = 1;
$discs = 1;
$tracks = array();
	
if ($cfg['show_multidisc']) {
	$query = mysqli_query($db, 'SELECT disc FROM track WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '" GROUP BY disc');
	$discs = mysqli_num_rows($query);
	
	$query = mysqli_query($db, 'SELECT max(disc) as max_disc FROM track WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '"');
	$album = mysqli_fetch_assoc($query);
	$max_disc = $album['max_disc'];
	
	$query = mysqli_query($db, 'SELECT min(disc) as min_disc FROM track WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '"');
	$album = mysqli_fetch_assoc($query);
	$disc = $album['min_disc'];
	
	
}
for ($disc; $disc <= $max_disc; $disc++) {
	$queryPart = '';
	if ($cfg['show_multidisc']) {
		$queryPart = ' AND disc = ' . (int) $disc . ' ';
	}
	
	if (isTidal($album_id)) {
		$queryStr = "
		SELECT CONCAT('tidal_',tidal_track.track_id) as track_id, tidal_track.title, tidal_track.artist as track_artist, (tidal_track.seconds * 1000) as miliseconds, tidal_track.disc, tidal_track.number, tidal_track.genre_id, tidal_album.album, tidal_album.album_date, tidal_album.album_id FROM tidal_track LEFT JOIN tidal_album ON tidal_track.album_id = tidal_album.album_id WHERE tidal_album.album_id='" . str_replace('tidal_','',$album_id) . "'
		ORDER BY disc, number";
		$query = mysqli_query($db,$queryStr);
		$track_count = mysqli_num_rows($query);
		if ($track_count < 2){ //0 - no tracks found; 1 - album added from tidal playlist or has only one track
			getTracksFromTidalAlbum(str_replace('tidal_','',$album_id));
			$query = mysqli_query($db,$queryStr);
		}
	}
	elseif (isHra($album_id)) {
		foreach ($tracksList as $key => $t) {
			$tracks[$key]["number"] = $t["trackNumber"]; 
			$tracks[$key]["track_artist"] = $t["artist"]; 
			$tracks[$key]["title"] = $t["title"]; 
			$tracks[$key]["track_id"] = "hra_" . $t["playlistAdd"]; 
			$tracks[$key]["miliseconds"] = 1000 * $t["playtime"]; 
			if ($t['genre']) {
				$tracks[$key]["genre"] = $t["genre"]; 
				$showGenre = true;
			}
		}
	}
	else {
		$query = mysqli_query($db,'SELECT track.track_artist, track.artist, track.title, track.featuring, track.dr, track.miliseconds, track.track_id, track.number, track.relative_file, track.genre, f.blacklist_pos as blacklist_pos, f. favorite_pos as favorite_pos
		FROM track left join 
			(
			SELECT favoriteitem.track_id as track_id, b.position as blacklist_pos, f.position as favorite_pos
					FROM favoriteitem 
					LEFT JOIN 
				(SELECT track_id, position FROM favoriteitem WHERE favorite_id = "' . $cfg['blacklist_id'] . '") b ON favoriteitem.track_id = b.track_id 
						LEFT JOIN 
							(SELECT track_id, position FROM favoriteitem WHERE favorite_id = "' . $cfg['favorite_id'] . '") f ON favoriteitem.track_id = f.track_id
			) f
		ON track.track_id = f.track_id
		WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '"' . $queryPart . ' AND track.error = "" 
		GROUP BY track.track_id
		ORDER BY number,relative_file');
	}
	$hasTrack = false;
	$track_count = mysqli_num_rows($query);
	if ($track_count > 0) $hasTrack = true;
	
	?>
	<?php
	if ($hasTrack) {
		if ($discs > 1 && $cfg['show_multidisc']) {
		?>
		<div>
			<table cellspacing="0" cellpadding="0">
				<tr class="multidisc_header">
					<td class="small_cover_md"><a><img src="image.php?image_id=<?php echo $image_id; ?>" width="100%"></a></td>
					<td class="icon">
					<a href="javascript:ajaxRequest('play.php?action=playSelect&amp;album_id=<?php echo $album_id; ?>&amp;disc=<?php echo $disc; ?>',evaluateAdd);"><i id="play_<?php echo $album_id . '_' . $disc; ?>" class="fa fa-fw fa-play-circle-o  icon-small"></i></a>
					</td>
					<td class="icon">
					<a href="javascript:ajaxRequest('play.php?action=addSelect&amp;album_id=<?php echo $album_id; ?>&amp;disc=<?php echo $disc; ?>',evaluateAdd);"><i id="add_<?php echo $album_id . '_' . $disc; ?>" class="fa fa-fw  fa-plus-circle  icon-small"></i></a>
					</td> 
					<td class="small_cover_md">Disc #<?php echo $disc;?></td>
					<td></td>
				</tr>
				<tr class="line"><td colspan="5"></td></tr>
			</table>
		</div>
		<?php } ?>
		<table id="playlist-table<?php echo $disc; ?>" cellspacing="0" cellpadding="0" class="border no-display">
			<tr class="header">
				<td class="icon"></td><!-- track menu -->
				<td class="icon"></td>
				<td class="trackNumber">#</td>
				<td>Title</td>
				<td class="track-list-artist">Artist</td>
				<td class="textspace track-list-artist"></td>
				<td class="time pl-genre"><?php if ($showGenre) echo'Genre'; ?></td>
				<?php if (!isHra($album_id)){ ?>
				<td></td>
				<?php } ?>
				<?php if ($cfg['show_DR']){ ?>
				<td class="time pl-tdr">DR</td>
				<?php } ?>
				<td align="right" class="time time_w">Time</td>
				<td class="space right"><div class="space"></div></td>
			</tr>
		<?php
			/* echo ("<pre>");
			print_r($tracksList);
			echo ("</pre>"); */
		$i = 0;
		if (!isHra($album_id)) {
      $tracks = array();
			while ($trackItem = mysqli_fetch_assoc($query)) {
				$tracks[] = $trackItem; 
			}
		}
		//while ($track = mysqli_fetch_assoc($query)) { 
		foreach ($tracks as $track) { 
		?>
			<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
				<?php 
				$position_id = $i + $disc * 100;
				?>
				<td class="icon">
				<span id="menu-track<?php echo $position_id; ?>">
				<div onclick='toggleMenuSub(<?php echo $position_id; ?>);'>
					<i id="menu-icon<?php echo $position_id; ?>" class="fa fa-bars icon-small"></i>
				</div>
				</span>
				</td>
				
				<td class="icon">
				<span>
				<?php 
				if ($cfg['access_add'])  echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track ' . $track['number'] . '\');" onMouseOut="return nd();"><i id="add_' . $track['track_id'] . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';?>
				</span>
				</td>
				
				<td class="trackNumber"><?php if ($cfg['access_play']) 		echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['track_id'] . '\');" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['number']) . '.</a>';?></td>
				<td><?php if ($cfg['access_play']) 		echo '<a id="a_play_track'. $position_id .'" href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['track_id'] . '&amp;position_id=' . $position_id . '\',evaluateAdd);" onMouseOver="return overlib(\'Play track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
						elseif ($cfg['access_add'])		echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
						elseif ($cfg['access_stream'])	echo '<a href="stream.php?action=playlist&amp;track_id=' . $track['track_id'] . '&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream track ' . $track['number'] . '\');" onMouseOut="return nd();">' . html($track['title']) . '</a>';
						else 							echo html($track['title']); ?>
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
							if ($j != $l - 1) {
								$delimiter = getInbetweenStrings($exploded[$j],$exploded[$j + 1], $track['track_artist']);
								$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year"><span class="artist_all">' . $delimiter[0] . '</span></a>';
							}
						}
						echo $artist;
					}
					else {
						echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($track['track_artist']) . '&amp;order=year">' . html($track['track_artist']) . '</a>';
					}
					?>
				
				</td>
				<td class="track-list-artist"></td>
				<td class="time pl-genre"><?php 
				if ($showGenre) {
					//echo html($track['genre']); 
					$album_genres = parseMultiGenre($track['genre']);
					if (count($album_genres) > 0) { 
						foreach($album_genres as $g_id => $ag) {
					?>
						<a href="index.php?action=view2&order=artist&sort=asc&genre_id=<?php echo $g_id; ?>"><?php echo $ag; ?></a><br>
					<?php 
						}
					}
				} ?>
				</td>
				<?php
				
				$isFavorite = false;
				$isBlacklist = false;
				$tid = $track['track_id'];
				if (isTidal($tid)){
					$track_id = getTidalId($tid);
					$isFavorite = isInFavorite($tid, $cfg['favorite_id']);
					$isBlacklist = isInFavorite($tid, $cfg['blacklist_id']);
					
				}
				else {
					if ($track['favorite_pos']) $isFavorite = true;
					if ($track['blacklist_pos']) $isBlacklist = true;
				}
				if (!isHra($album_id)){
				?>
					<td onclick="toggleStarSub(<?php echo $i + $disc * 100 ?>,'<?php echo $tid ?>');" class="pl-favorites">
						<span id="blacklist-star-bg<?php echo $tid ?>" class="<?php if ($isBlacklist) echo ' blackstar blackstar-selected'; ?>">
						<i class="fa fa-star<?php if (!$isFavorite) echo '-o'; ?> fa-fw" id="favorite_star-<?php echo $tid; ?>"></i>
						</span>
					</td>
				<?php 
				}
				if ($cfg['show_DR']){ ?>
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
			<?php starSubMenu($i + $disc * 100, $isFavorite, $isBlacklist, $tid);?>
			</td>
			</tr>

			<tr>
			<td colspan="10">
			<?php trackSubMenu($i + $disc * 100, $track, $album_id);?>
			</td>
			</tr>
			<?php
		}
		$track = array();
		if (isHra($album_id) || isTidal($album_id)) {
			$track['sum_miliseconds'] = $total_time;
		}
		/* elseif (isTidal($album_id)) {
			$query = mysqli_query($db, 'SELECT (seconds * 1000) AS sum_miliseconds FROM tidal_album WHERE album_id = "' .  mysqli_real_escape_string($db,getTidalId($album_id)) . '"');
			$track = mysqli_fetch_assoc($query);
		} */
		else {
			$query = mysqli_query($db, 'SELECT SUM(miliseconds) AS sum_miliseconds FROM track WHERE album_id = "' .  mysqli_real_escape_string($db,$album_id) . '" AND disc = ' . (int) $disc);
			$track = mysqli_fetch_assoc($query);
		}
	
		echo '</table>';
		?>
		<div><h1><div class="total-time">Total: <?php echo formattedTime($track['sum_miliseconds']); ?></div></h1>
	</div>
	
	<?php
	}
}

?>

