<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2021 Artur Sierzant                            |
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
$fromPosition = $_POST['fromPosition'];
$toPosition = $_POST['toPosition'];
$isMoveToTop = $_POST['isMoveToTop'];

$data = array();

$query1=mysqli_query($db,'SELECT player.player_name as pl, player_host, player_port, player_pass FROM player, session WHERE (sid = BINARY "' . cookie('netjukebox_sid') . '") and player.player_id=session.player_id');
$session1 = mysqli_fetch_assoc($query1);
$cfg['player_host'] = $session1['player_host'];
$cfg['player_port'] = $session1['player_port'];
$cfg['player_pass'] = $session1['player_pass'];

	
if ($action == 'moveItem') {
	if ($isMoveToTop == 'true') {
		mpd('playlistmove "' . $favorite_id . '" ' . $fromPosition . ' 0');
	}
	else {
		if ($fromPosition > $toPosition) $toPosition++;
		mpd('playlistmove "' . $favorite_id . '" ' . $fromPosition . ' ' . $toPosition);
	}
}
elseif ($action == 'removeItem') {
	
	$pl = mpd('playlistdelete "' . $favorite_id . '" ' . $fromPosition);
}

$playlist = mpd('listplaylistinfo "' . $favorite_id . '"');

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
	<td></td><!-- delete -->
</tr>
<?php
	//$cfg['access_play'] = true;
	$bottom = count($playlist['file']);
	if ($bottom > 0) $bottom--;
	
	$i = 0;
	for ($i = 0; $i < count($playlist['file']); $i++) {
			$artist	= $playlist['Artist'][$i];
			$title	= (isset($playlist['Title'][$i]) ? $playlist['Title'][$i] : $playlist['file'][$i]);
			//$title	= $playlist['Title'][$i];
		
		?>
<tr class="<?php echo ($i & 1) ? 'even' : 'odd'; ?> mouseover" id="track<?php echo $i; ?>" style="display:table-row;">
	
	<td><?php if ($cfg['access_play']) {
		echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;menu=favorite&amp;filepath=' . myUrlencode($playlist['file'][$i]) . '&amp;track_id=' . $i . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track\');" onMouseOut="return nd();"><i  id="add_' . $i . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';
	}
	
	?></td>
	<td><?php // echo $artist; ?></td>
	<td><?php 
	if ($cfg['access_play']) {
		echo '<a id="fav_play_track' . $i . '" href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;filepath=' . myUrlencode($playlist['file'][$i]) . '&amp;menu=favorite&amp;track_id=' . $i . '\',evaluateAdd);" onMouseOver="return overlib(\'play track\');" onMouseOut="return nd();">' . html($title) . '</a>';
	}
	else echo html($title); ?>
	</td>
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
	<td></td>
	<td class="<?php echo $break_method;?>"><?php echo html($artist); ?></td>
	<td align="right" class="iconDel" style="position: relative">
		<div  id="menu-icon-div<?php echo $i ?>" <?php echo 'onclick="toggleMenuSub(' . $i . ');"'; ?>>
			<i id="menu-icon<?php echo $i ?>" class="fa fa-fw fa-bars sign"></i>
		</div>
		<div  id="menu-insert-div<?php echo $i ?>" style="display: none; position: absolute; bottom: 0;" onclick="arrangeFavItemMPD(<?php echo ($i >= $bottom ? $bottom : $i); ?>,-1,false, '<?php echo $favorite_id; ?>')">
			<i class="fa fa-fw fa-angle-down sign"></i>
		</div>
	</td>
</tr>

<tr id="track-line<?php echo $i; ?>" class="line"><td colspan="12"></td></tr>

<tr id="track-menu<?php echo $i; ?>">
	<td colspan="12">
		<div class="menuSubRight" id="menu-sub-track<?php echo $i ?>" onclick='offMenuSub(<?php echo $i ?>);'> 
		
		<div class="icon-anchor" id="track<?php echo $i; ?>_delete" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:arrangeFavItemMPD(-1,' . $i . ',false,' . chr(39) . $favorite_id . chr(39) . ');"'; ?>>
		Remove <i class="fa fa-times-circle fa-fw icon-small"></i></a>
		</div>
		
		<div class="icon-anchor" id="track<?php echo $i; ?>_move_top" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:arrangeFavItemMPD(0,' . $i . ',true,' . chr(39) . $favorite_id . chr(39) . ');"'; ?>>Move to top<i class="fa fa-long-arrow-up fa-fw icon-small"></i></div>
		<div class="icon-anchor" id="track<?php echo $i; ?>_move" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:toggleInsert(\'on\',' . $i . ',false,' . chr(39) . $favorite_id . chr(39) . ')"'; ?>>Move <i class="fa fa-arrows-v fa-fw icon-small"></i></div>
		<div class="icon-anchor" id="track<?php echo $i; ?>_move_bottom" <?php if ($cfg['access_play']) 
		echo 'onclick="javascript:arrangeFavItemMPD(' . $bottom . ',' . $i . ',false,' . chr(39) . $favorite_id . chr(39) . ');"'; ?>>Move to bottom<i class="fa fa-long-arrow-down fa-fw icon-small"></i></div>
		</div>
		
	</td>
</tr>
<?php
	} ?>
</table>
<script>
setAnchorClick();
</script>
