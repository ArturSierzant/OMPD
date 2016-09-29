<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant                            |
//  | http://www.ompd.pl           		                                     |
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
				position = "' . $fromPosition . '"
				AND favorite_id = "' . $favorite_id . '"');
}

?>
<script type="text/javascript">
$('[id^="add_"]').click(function(){
		addClick();
	});
</script>
<table width="100%" cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	
	<td class="icon"></td><!-- optional play -->
	<td<?php if ($cfg['access_play'] && $favorite['stream'] == false) echo' class="space"'; ?>></td>
	<td><?php echo $favorite['stream'] ? 'Stream' : 'Artist' ?></td>
	<td<?php if ($favorite['stream'] == false) echo ' class="textspace"'; ?>></td>
	<td><?php echo $favorite['stream'] ? '' : 'Title' ?></td>
	<td></td><!-- delete -->
</tr>
<?php
	//$cfg['access_play'] = true;
	$i = 0;
	$query1 = mysqli_query($db,'SELECT track_id, stream_url, position FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id . ' ORDER BY position');
	while ($favoriteitem = mysqli_fetch_assoc($query1)) {
		if ($favoriteitem['track_id']) {
			
			$query2	= mysqli_query($db,'SELECT artist, title FROM track WHERE track_id = "' . mysqli_real_escape_string($db,$favoriteitem['track_id']) . '"');
			$track	= mysqli_fetch_assoc($query2);
			$artist	= $track['artist'];
			$title	= $track['title'];
		}
		elseif ($favoriteitem['stream_url']) {
			$artist	= $favoriteitem['stream_url'];
			$title	= '';
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
	<td><?php //echo 'acc pl: ' . print_r($cfg); ?></td>
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
	<td class="<?php echo $break_method;?>"><?php 
	
	if ($cfg['access_play'] && $favoriteitem['stream_url']) {
		echo '<a href="javascript:ajaxRequest(\'play.php?action=playStreamDirect&amp;playAfterInsert=yes&amp;position=' . $favoriteitem['position'] . '&amp;favorite_id=' . $favorite_id . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Play stream\');" onMouseOut="return nd();">' . html($artist) . '</a>';
	}
	else echo html($artist); ?></td>
	<td></td>
	<td><?php 
	if ($cfg['access_play'] && $favoriteitem['track_id']) {
		echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $favoriteitem['track_id'] . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'play track\');" onMouseOut="return nd();">' . html($title) . '</a>';
	}
	else echo html($title); ?></td>
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
	} ?>
</table>

	
