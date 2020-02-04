<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2019 Artur Sierzant                            |
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
require_once('include/play.inc.php');
global $cfg, $db;
authenticate('access_favorite');

$action = $_POST['action'];
$favorite_id = $_POST['favorite_id'];

$data = array();

$query1=mysqli_query($db,'SELECT player.player_name as pl, player_host, player_port, player_pass FROM player, session WHERE (sid = BINARY "' . cookie('netjukebox_sid') . '") and player.player_id=session.player_id');
$session1 = mysqli_fetch_assoc($query1);
$cfg['player_host'] = $session1['player_host'];
$cfg['player_port'] = $session1['player_port'];
$cfg['player_pass'] = $session1['player_pass'];

$t = new TidalAPI;
$t->username = $cfg["tidal_username"];
$t->password = $cfg["tidal_password"];
$t->token = $cfg["tidal_token"];
if (NJB_WINDOWS) $t->fixSSLcertificate();
$conn = $t->connect();

if ($conn === true){
	$trackList = $t->getUserPlaylistTracks($favorite_id);
	?>
	<script type="text/javascript">
	/* $('[id^="add_"]').click(function(){
			addClick();
		}); */
	</script>
	<table width="100%" cellspacing="0" cellpadding="0" class="border">
	<tr class="header">
		
		<td class="icon"></td><!-- optional play -->
		<td<?php if ($cfg['access_play']) echo' class="space"'; ?>></td>
		<td>Title/file</td>
		<td class="textspace"></td>
		<td>Artist</td>
		<td align="center">Open in TIDAL</td>
		<td></td><!-- delete -->
	</tr>
	<?php
		$i = 0;
		for ($i = 0; $i < $trackList['totalNumberOfItems']; $i++) {
				$artist	= $trackList['items'][$i]['artist']['name'];
				$title	= $trackList['items'][$i]['title'];
				$id	= $trackList['items'][$i]['id'];
				$volumeNumber	= $trackList['items'][$i]['volumeNumber'];
				$duration	= $trackList['items'][$i]['duration'];
				$trackNumber	= $trackList['items'][$i]['trackNumber'];
				$album_id	= $trackList['items'][$i]['album']['id'];
				$album	= $trackList['items'][$i]['album']['title'];
				$cover	= $trackList['items'][$i]['album']['cover'];
				$releaseDate	= $trackList['items'][$i]['album']['releaseDate'];
				$extUrl = TIDAL_ALBUM_URL . $album_id;
				$sql = "SELECT album_id FROM tidal_album WHERE album_id = '" . $album_id . "'";
				$rows = mysqli_num_rows($sql);
				if ($rows == 0) {
					$sql = "INSERT INTO tidal_album 
					(album_id, artist, artist_alphabetic, artist_id, album, album_date, genre_id, discs, seconds, last_update_time, cover, type)
					VALUES (
					'" . $album_id . "', '', '', '', '" . mysqli_real_escape_string($db,$album) . "', '" . $releaseDate . "', '', 1, '','" . time() . "','" . $cover . "','playlist')";
					$query2=mysqli_query($db,$sql);
				}
				$sql = "REPLACE INTO tidal_track 
				(track_id, title, artist, artist_alphabetic, genre_id, disc, seconds, number, album_id)
				VALUES (
				'" . $id . "', '" . mysqli_real_escape_string($db,$title) . "', '" . mysqli_real_escape_string($db,$artist) . "', '" . mysqli_real_escape_string($db,$artist) . "', '', '" . $volumeNumber . "', '" . $duration . "', '" . $trackNumber . "', '" . $album_id . "')";
				
				mysqli_query($db, $sql);
				
			
			?>
	<tr class="<?php echo ($i & 1) ? 'even' : 'odd'; ?> mouseover" id="track<?php echo $i; ?>" style="display:table-row;">
		
		<td><?php if ($cfg['access_play']) {
			echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=tidal_' . $id . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();"><i id="add_tidal_' . $id . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';
		}
		
		?></td>
		<td><?php // echo $artist; ?></td>
		<td><?php 
		if ($cfg['access_play']) {
			echo '<a id="fav_play_tracktidal_' . $id . '" href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=tidal_' . $id . '\',evaluateAdd);" onMouseOver="return overlib(\'play track\');" onMouseOut="return nd();">' . html($title) . '</a>';
		}
		else echo html($title); ?>
		</td>
		<td></td>
		<?php 
		$artist_array = explode(" ", $artist);
		$lengths = array_map('strlen', $artist_array);
		if (max($lengths) > 30) {
			$break_method = 'break-all';
		} 
		else {
			$break_method = 'break-word';
		}
		?>
		<td class="<?php echo $break_method;?>"><?php echo html($artist); ?></td>
		<td align="center"><a href="<?php echo $extUrl; ?>" target="_blank"><i class="ux ico-tidal icon-small fa-fw"></i></a></td>
		<td align="right" class="iconDel" style="position: relative"></td>
	</tr>
<?php
	}	
}?>
</table>
</table>
<script>
setAnchorClick();
</script>
