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
//  | favorite.php                                                           |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
$cfg['menu'] = 'favorite';

$action 		= getpost('action');
$favorite_id	= getpost('favorite_id');
$favorite_name	= getpost('plName');


if		($action == '')							home();
elseif	($action == 'editFavorite')				editFavorite($favorite_id);
elseif	($action == 'editFavoriteMPD')		editFavoriteMPD($favorite_id);
elseif	($action == 'viewTidalPlaylist')		viewTidalPlaylist($favorite_id, $favorite_name);
elseif	($action == 'addFavorite')	 			addFavorite();
elseif	($action == 'saveFavorite') 			saveFavorite($favorite_id);
elseif	($action == 'importPlaylist')			importFavorite($favorite_id, 'import');
elseif	($action == 'addPlaylist')				importFavorite($favorite_id, 'add');
elseif	($action == 'importPlaylistUrl')		importFavorite($favorite_id, 'importUrl');
elseif	($action == 'addPlaylistUrl')			importFavorite($favorite_id, 'addUrl');
elseif	($action == 'deleteFavoriteMPD') 			deleteFavoriteMPD($favorite_id);
elseif	($action == 'deleteFavorite') 			deleteFavorite($favorite_id);
elseif	($action == 'deleteFavoriteItem')		deleteFavoriteItem($favorite_id);
else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');
exit();




//  +------------------------------------------------------------------------+
//  | Home                                                                   |
//  +------------------------------------------------------------------------+
function home() {
	global $cfg, $db, $t;
	authenticate('access_favorite');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Favorites';
	require_once('include/header.inc.php');
	
	$i = 0;
	$previous_stream = 0;

	if ($cfg['access_admin']) {
?>
<div class="buttons">
	<span><a href="favorite.php?action=addFavorite&amp;sign=<?php echo $cfg['sign'] ?>" onmouseover="return overlib('Add new playlist');" onmouseout="return nd();">Add new</a></span>
</div>
<?php }
	?>
<table cellspacing="0" cellpadding="0" class="border tabFixed break-word">
<tr class="header">
	
	<td class="icon"></td><!-- optional play -->
	<td class="icon"></td><!-- optional add -->
	<td class="icon"></td><!-- optional stream -->
	<td>Playlist</td>
	<td>Comment</td>
	<td class="icon"></td><!-- optional delete -->
	<td class="icon"></td>
	<td class="space"></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	
	<td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;random=new\',evaluateAdd);" onMouseOver="return overlib(\'Play random tracks\');" onMouseOut="return nd();"><i id="play_random" class="fa fa-play-circle-o fa-fw icon-small"></i></a>'; ?></td>
	
	<td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;random=new\',evaluateAdd);" onMouseOver="return overlib(\'Add random tracks to playlist\');" onMouseOut="return nd();"><i id="add_random" class="fa fa-plus-circle fa-fw icon-small"></i></a>'; ?></td>
	
	<td><?php if ($cfg['access_stream']) echo '<a href="stream.php?action=playlist&amp;random=new&amp;stream_id=' . $cfg['stream_id'] . '" onMouseOver="return overlib(\'Stream random tracks\');" onMouseOut="return nd();"><i class="fa fa-rss fa-fw icon-small"></i></a>'; ?></td>
	
	<td><?php if ($cfg['access_play']) 		echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;random=new\');" onMouseOver="return overlib(\'Play random tracks\');" onMouseOut="return nd();">Random tracks</a>';
						elseif ($cfg['access_stream'])	echo '<a href="stream.php?action=playlist&amp;random=new&amp;stream_id=' . $cfg['stream_id']  . '" onMouseOver="return overlib(\'Stream random tracks\');" onMouseOut="return nd();">Random tracks</a>'; 
						else echo 'Random tracks'; ?>
	</td>
						
	<td>Play random tracks from library</td>
	
	<td></td>
	
	<td><?php if ($cfg['access_media']) echo '<a href="genre.php?action=blacklist" onMouseOver="return overlib(\'Edit random blacklist\');" onMouseOut="return nd();"><i class="fa fa-pencil fa-fw icon-small"></i></a>'; ?></td>
	
	<td></td>
</tr>
<?php
	$query = mysqli_query($db,'SELECT name, comment, stream, favorite_id FROM favorite WHERE 1 ORDER BY stream, name, comment');
	while ($favorite = mysqli_fetch_assoc($query)) {
		if ($previous_stream != $favorite['stream'] && $i > 0) {
			$i = 0;
			//echo '<tr class="line"><td colspan="8"></td></tr>' . "\n";
			echo '<tr class="header">
			
			<td class="icon"></td>
			<td class="icon"></td>
			<td class="icon"></td>
			<td>Stream</td>
			<td colspan="4">Comment</td>
			</tr>' . "\n";
			//echo '<tr class="line"><td colspan="11"></td></tr>' . "\n";
		} ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	
	<td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;favorite_id=' . $favorite['favorite_id'] . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Play\');" onMouseOut="return nd();"><i id="play_' . $favorite['favorite_id'] . '" class="fa fa-play-circle-o fa-fw icon-small"></i></a>'; ?></td>
	
	<td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;favorite_id=' . $favorite['favorite_id'] . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Add to playlist\');" onMouseOut="return nd();"><i id="add_' . $favorite['favorite_id'] . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>'; ?></td>
	
	<td><?php if ($cfg['access_stream']) echo '<a href="stream.php?action=playlist&amp;favorite_id=' . $favorite['favorite_id'] . ($favorite['stream'] == false ? '&amp;stream_id=' . $cfg['stream_id'] : '') . '" onMouseOver="return overlib(\'Stream\');" onMouseOut="return nd();"><i class="fa fa-rss fa-fw icon-small"></i></a>'; ?></td>
	
	<td><?php if ($cfg['access_play'])								echo '<a href="javascript:ajaxRequest(\'play.php?action=playSelect&amp;favorite_id=' . $favorite['favorite_id'] . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Play\');" onMouseOut="return nd();">' . html($favorite['name']) . '</a>';
			elseif (!$cfg['access_play'] && $cfg['access_stream'])	echo '<a href="stream.php?action=playlist&amp;favorite_id=' . $favorite['favorite_id'] . ($favorite['stream'] == false ? '&amp;stream_id=' . $cfg['stream_id'] : '') . '" onMouseOver="return overlib(\'Stream\');" onMouseOut="return nd();">' . html($favorite['name']) . '</a>';
			else 													echo html($favorite['name']); ?></td>
	
	<td><?php echo bbcode($favorite['comment']); ?></td>
	
	<td><?php if ($cfg['access_admin']) echo '<a href="favorite.php?action=deleteFavorite&amp;favorite_id=' . $favorite['favorite_id'] . '&amp;sign=' . $cfg['sign'] . '" onClick="return confirm(\'Are you sure you want to delete favorite: ' . addslashes(html($favorite['name'])) . '?\');" onMouseOver="return overlib(\'Delete\');" onMouseOut="return nd();"><i class="fa fa-times-circle fa-fw icon-small"></i></a>'; ?></td>
	
	<td><?php if ($cfg['access_admin']) echo '<a href="favorite.php?action=editFavorite&amp;favorite_id=' . $favorite['favorite_id'] . '" onMouseOver="return overlib(\'Edit\');" onMouseOut="return nd();"><i class="fa fa-pencil fa-fw icon-small"></i></a>'; ?></td>
	
	<td></td>
</tr>
<?php
		$previous_stream = $favorite['stream'];
	}
	
	require_once('include/play.inc.php');
	$playlists = mpd('listplaylists');
	sort($playlists, SORT_NATURAL);
	
	if (count($playlists) > 0 && $playlists !== 'ACK_ERROR_UNKNOWN') {
?>
		<tr class="header">
			
			<td class="icon"></td><!-- optional play -->
			<td class="icon"></td><!-- optional add -->
			<td class="icon"></td><!-- optional stream -->
			<td>MPD's Playlists</td>
			<td></td>
			<td class="icon"></td><!-- optional delete -->
			<td class="icon"></td>
			<td class="space"></td>
		</tr>

		<?php	
		for ($j = 0; $j < count($playlists); $j++) {
			$plName = $playlists[$j];
			//$plLastMod = $playlists['Last-Modified'][$j];
		?>		
			<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
				
				<td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playMPDplaylist&amp;favorite_id=' . $plName . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Play\');" onMouseOut="return nd();"><i id="play_' . $plName . '" class="fa fa-play-circle-o fa-fw icon-small"></i></a>'; ?></td>
				
				<td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=addMPDplaylist&amp;favorite_id=' . $plName . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Add to playlist\');" onMouseOut="return nd();"><i id="add_' . $plName . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>'; ?></td>
				
				<td>
				<!--
				<?php if ($cfg['access_stream']) echo '<a href="stream.php?action=playlist&amp;favorite_id=' . $favorite['favorite_id'] . ($favorite['stream'] == false ? '&amp;stream_id=' . $cfg['stream_id'] : '') . '" onMouseOver="return overlib(\'Stream\');" onMouseOut="return nd();"><i class="fa fa-rss fa-fw icon-small"></i></a>'; ?>
				-->
				</td>
				
				<td><?php if ($cfg['access_play'])								echo '<a href="javascript:ajaxRequest(\'play.php?action=playMPDplaylist&amp;favorite_id=' . $plName . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Play\');" onMouseOut="return nd();">' . html($plName) . '</a>';
						elseif (!$cfg['access_play'] && $cfg['access_stream'])	echo '<a href="stream.php?action=playlist&amp;favorite_id=' . $favorite['favorite_id'] . ($favorite['stream'] == false ? '&amp;stream_id=' . $cfg['stream_id'] : '') . '" onMouseOver="return overlib(\'Stream\');" onMouseOut="return nd();">' . html($favorite['name']) . '</a>';
						else 													echo html($plName); ?>
				</td>
				
				<td><?php //echo $plLastMod; ?></td>
				
				<td>
				
				<?php if ($cfg['access_admin']) echo '<a href="favorite.php?action=deleteFavoriteMPD&amp;favorite_id=' . $plName . '&amp;sign=' . $cfg['sign'] . '" onClick="return confirm(\'Are you sure you want to delete favorite: ' . addslashes(html($plName)) . '?\');" onMouseOver="return overlib(\'Delete\');" onMouseOut="return nd();"><i class="fa fa-times-circle fa-fw icon-small"></i></a>'; ?>
				
				</td>
				
				<td>
				
				<?php if ($cfg['access_admin']) echo '<a href="favorite.php?action=editFavoriteMPD&amp;favorite_id=' . $plName . '" onMouseOver="return overlib(\'Edit\');" onMouseOut="return nd();"><i class="fa fa-pencil fa-fw icon-small"></i></a>'; ?>
				
				</td>
				
				<td></td>
			</tr>
		<?php	
		}
	}
	
	
	if ($cfg['use_tidal']) {
		/* $t = new TidalAPI;
		$t->username = $cfg["tidal_username"];
		$t->password = $cfg["tidal_password"];
		$t->token = $cfg["tidal_token"];
		if (NJB_WINDOWS) $t->fixSSLcertificate(); */
    //$t = tidal();
		$conn = $t->connect();
		if ($conn === true){
			$playlists = $t->getUserPlaylists();
			if ($playlists['totalNumberOfItems'] > 0) {
?>
		<tr class="header">
			
			<td class="icon"></td><!-- optional play -->
			<td class="icon"></td><!-- optional add -->
			<td class="icon"></td><!-- optional stream -->
			<td>Playlists from Tidal</td>
			<td></td>
			<td class="icon"></td><!-- optional delete -->
			<td class="icon"></td>
			<td class="space"></td>
		</tr>

		<?php
				for ($j = 0; $j < $playlists['totalNumberOfItems']; $j++) {
					$plName = $playlists['items'][$j]['title'];
					$plId = $playlists['items'][$j]['uuid'];
					//$plLastMod = $playlists['Last-Modified'][$j];
				?>		
					<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
						
						<td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=playTidalPlaylist&amp;favorite_id=' . $plId . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Play\');" onMouseOut="return nd();"><i id="play_' . $plId . '" class="fa fa-play-circle-o fa-fw icon-small"></i></a>'; ?></td>
						
						<td><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=addTidalPlaylist&amp;favorite_id=' . $plId . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Add to playlist\');" onMouseOut="return nd();"><i id="add_' . $plId . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>'; ?></td>
						
						<td>
						</td>
						
						<td><?php if ($cfg['access_play'])	echo '<a href="javascript:ajaxRequest(\'play.php?action=playTidalPlaylist&amp;favorite_id=' . $plId . '&amp;menu=favorite\',evaluateAdd);" onMouseOver="return overlib(\'Play\');" onMouseOut="return nd();">' . html($plName) . '</a>';
								else 													echo html($plName); ?>
						</td>
						
						<td>
							<?php echo $playlists['items'][$j]['description']; ?></td>
						<td>
						</td>
						
						<td>
							<?php if ($cfg['access_admin']) echo '<a href="favorite.php?action=viewTidalPlaylist&amp;favorite_id=' . $plId . '&plName=' . $plName . '" onMouseOver="return overlib(\'See tracks\');" onMouseOut="return nd();"><i class="fa fa-list fa-fw icon-small"></i></a>'; ?>
						</td>
						
						<td></td>
					</tr>
				<?php	
				}
			}
		}
	}
	
	
	
	echo '</table>' . "\n";
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Edit favorite                                                          |
//  +------------------------------------------------------------------------+
function editFavorite($favorite_id) {
	global $cfg, $db;
	authenticate('access_admin');
	
	require_once('include/play.inc.php');
	
	$query = mysqli_query($db,'SELECT name, comment, stream FROM favorite WHERE favorite_id = ' . (int) $favorite_id);
	$favorite = mysqli_fetch_assoc($query);
	if ($favorite == false)
		message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]favorite_id not found in database');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Favorites';
	$nav['url'][]	= 'favorite.php';
	$nav['name'][]	= 'Edit';
	require_once('include/header.inc.php');
	if ($favorite_id == $cfg['favorite_id'] || $favorite_id == $cfg['blacklist_id'])
		$disabled = ' disabled';
	else	
		$disabled = '';
	//$disabled_favorite = ($favorite_id == $cfg['favorite_id'] ? ' disabled' : '');
	//$disabled = ($favorite_id == $cfg['blacklist_id'] ? ' disabled' : '');
?>

<form action="favorite.php" method="post" name="favorite" id="favorite">
	<input type="hidden" name="action" value="saveFavorite">
	<input type="hidden" name="favorite_id" value="<?php echo $favorite_id; ?>">
	<input type="hidden" name="sign" value="<?php echo $cfg['sign']; ?>">
<table cellspacing="0" cellpadding="0" id="favoriteTable">
<tr class="header">
	<td colspan="3">&nbsp;Playlist info:</td>
</tr>
</tr>
<tr class="textspace"><td colspan="3"></td></tr>
<tr>
<tr>
	<td id="favoriteTableFirstCol">Name:</td>
	<td class="textspace">&nbsp;</td>
	<td class="fullscreen"><input type="text" name="name" id="name" value="<?php echo html($favorite['name']); ?>" maxlength="255" style="width: 100%;"<?php echo $disabled ;?>></td>
</tr>
<tr>
	<td>Comment:</td>
	<td></td>
	<td><input type="text" name="comment" id="comment" value="<?php echo html($favorite['comment']); ?>" maxlength="255" style="width: 100%;" <?php echo onmouseoverBbcodeReference(); ?> <?php echo $disabled; ?>></td>
</tr>
<tr class="space"><td colspan="3"></td></tr>
<tr>
	<td></td>
	<td></td>
	<td>
	<?php if ($disabled =='') {?>
	<div class="buttons"><span><a href="#" onclick="$('#favorite').submit();">Save</a></span><span><a href="favorite.php">Cancel</a></span></div>
	<?php } ?>
	</td>
</tr>

<tr class="textspace"><td colspan="3"></td></tr>
<tr class="header">
	<td colspan="3">&nbsp;Add now playing playlist on:</td>
</tr>
<tr class="textspace"><td colspan="3"></td></tr>
<tr>
	<td>select player:</td>
	<td></td>
	<td>
	<select id="selectPlaylistFrom" name="selectPlaylistFrom">
		<?php
		$query2 = mysqli_query($db,'SELECT player_name, player_type, player_id FROM player ORDER BY player_name');
		while ($player = mysqli_fetch_assoc($query2)) {
		?>
			<option value="<?php echo $player['player_id']; ?>"
			<?php if($cfg['player_id'] == $player['player_id']) {
				echo " selected";
			}?>
			><?php echo html($player['player_name']); ?></option>
		<?php
		}
		?>
		</select>
	</td>
</tr>
<tr class="space"><td colspan="3"></td></tr>		
<tr>
	<td></td>
	<td></td>
	<td>
		<div class="buttons">
		<span><a href="#" onClick="addPlaylist()">Add</a></span>
		<span><a href="#" onClick="importPlaylist();">Remove all and add</a></span>
		</div>
	</td>
</tr>
<tr class="textspace"><td colspan="3"></td></tr>
<tr class="header">
	<td colspan="3">&nbsp;Add stream, playlist (.pls/.m3u) or file:</td>
</tr>
<tr class="textspace"><td colspan="3"></td></tr>
<tr>
	<td>URL or path:</td>
	<td></td>
	<td><input type="text" name="url" value="" maxlength="255" style="width: 100%;" ></td>
</tr>
<tr class="space"><td colspan="3"></td></tr>		
<tr>
	<td></td>
	<td></td>
	<td>
		<div class="buttons">
		<span><a href="#" onClick="addPlaylistUrl()">Add</a></span>
		<span><a href="#" onClick="importPlaylistUrl();">Remove all and add</a></span>
		</div>
	</td>
</tr>
<tr class="textspace"><td colspan="3"></td></tr>
<tr class="header">
	<td colspan="3">&nbsp;Tracks in this playlist:</td>
</tr>		
<tr class="line"><td colspan="9"></td></tr>
<tr>
	<td colspan="3">
	<!-- begin indent -->
<div id="favoriteList">
<div style="text-align: center; padding: 1em;">
 <i class="fa fa-cog fa-spin icon-small"></i> Loading track list...
</div>
 </div>
	<!-- end indent -->
	</td>
</tr>
</table>
</form>

<script type="text/javascript">
<!--
document.favorite.name.focus();

$(document).ready(function() {
	var request = $.ajax({  
		url: "ajax-favorite-arrange.php",  
		type: "POST",  
		data: { action : 'display',
				favorite_id : <?php echo $favorite_id; ?>
			  },  
		dataType: "html"
	}); 

	request.done(function( data ) {  
		$( "#favoriteList" ).html( data );
		//calcTileSize();
	}); 

	request.fail(function( jqXHR, textStatus ) {  
		alert( "Request failed: " + textStatus );	
	}); 
});

function importPlaylist() {
	showSpinner();
	document.favorite.action.value='importPlaylist'; 
	$('#favorite').submit();
}

function addPlaylist() {
	showSpinner();
	document.favorite.action.value='addPlaylist'; 
	$('#favorite').submit();
}

function importPlaylistUrl() {
	showSpinner();
	document.favorite.action.value='importPlaylistUrl'; 
	$('#favorite').submit();
}

function addPlaylistUrl() {
	showSpinner();
	document.favorite.action.value='addPlaylistUrl'; 
	$('#favorite').submit();
}

//-->
</script>

<?php
	require_once('include/footer.inc.php');
}





//  +------------------------------------------------------------------------+
//  | Edit favorite MPD                                                      |
//  +------------------------------------------------------------------------+
function editFavoriteMPD($favorite_id) {
	global $cfg, $db;
	authenticate('access_admin');
	
	require_once('include/play.inc.php');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Favorites';
	$nav['url'][]	= 'favorite.php';
	$nav['name'][]	= 'Edit';
	require_once('include/header.inc.php');
	
		$disabled = ' disabled';
	
?>	
<form action="favorite.php" method="post" name="favorite" id="favorite">
	<input type="hidden" name="action" value="saveFavorite">
	<input type="hidden" name="favorite_id" value="<?php echo $favorite_id; ?>">
	<input type="hidden" name="sign" value="<?php echo $cfg['sign']; ?>">
<table cellspacing="0" cellpadding="0" id="favoriteTable">
<tr class="header">
	<td colspan="3">&nbsp;Playlist info:</td>
</tr>
</tr>
<tr class="textspace"><td colspan="3"></td></tr>
<tr>
<tr>
	<td id="favoriteTableFirstCol">Name:</td>
	<td class="textspace">&nbsp;</td>
	<td class="fullscreen"><?php echo $favorite_id; ?></td>
</tr>

<tr class="space"><td colspan="3"></td></tr>
<tr>
	<td></td>
	<td></td>
	<td>
	<?php if ($disabled =='') {?>
	<div class="buttons"><span><a href="#" onclick="$('#favorite').submit();">Save</a></span><span><a href="favorite.php">Cancel</a></span></div>
	<?php } ?>
	</td>
</tr>

<tr class="textspace"><td colspan="3"></td></tr>

<tr class="header">
	<td colspan="3">&nbsp;Tracks in this playlist:</td>
</tr>		
<tr class="line"><td colspan="9"></td></tr>
<tr>
	<td colspan="3">
	<!-- begin indent -->
<div id="favoriteList">
<div style="text-align: center; padding: 1em;">
 <i class="fa fa-cog fa-spin icon-small"></i> Loading track list...
</div>
 </div>
	<!-- end indent -->
	</td>
</tr>
</table>
</form>

<script type="text/javascript">
<!--

$(document).ready(function() {
	var request = $.ajax({  
		url: "ajax-favorite-arrange-MPD.php",  
		type: "POST",  
		data: { action : 'display',
				favorite_id : '<?php echo $favorite_id; ?>'
			  },  
		dataType: "html"
	}); 

	request.done(function( data ) {  
		$( "#favoriteList" ).html( data );
		//calcTileSize();
	}); 

	request.fail(function( jqXHR, textStatus ) {  
		alert( "Request failed: " + textStatus );	
	}); 
});

function importPlaylist() {
	showSpinner();
	document.favorite.action.value='importPlaylist'; 
	$('#favorite').submit();
}

function addPlaylist() {
	showSpinner();
	document.favorite.action.value='addPlaylist'; 
	$('#favorite').submit();
}

function importPlaylistUrl() {
	showSpinner();
	document.favorite.action.value='importPlaylistUrl'; 
	$('#favorite').submit();
}

function addPlaylistUrl() {
	showSpinner();
	document.favorite.action.value='addPlaylistUrl'; 
	$('#favorite').submit();
}

//-->
</script>

<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | View Tidal playlist                                                    |
//  +------------------------------------------------------------------------+
function viewTidalPlaylist($favorite_id, $favorite_name) {
	global $cfg, $db;
	authenticate('access_admin');
	
	require_once('include/play.inc.php');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Favorites';
	$nav['url'][]	= 'favorite.php';
	$nav['name'][]	= 'View';
	require_once('include/header.inc.php');
	
?>	
<form action="favorite.php" method="post" name="favorite" id="favorite">
	<input type="hidden" name="action" value="saveFavorite">
	<input type="hidden" name="favorite_id" value="<?php echo $favorite_id; ?>">
	<input type="hidden" name="sign" value="<?php echo $cfg['sign']; ?>">
<table cellspacing="0" cellpadding="0" id="favoriteTable">
<tr class="header">
	<td colspan="3">&nbsp;Playlist info:</td>
</tr>
</tr>
<tr class="textspace"><td colspan="3"></td></tr>
<tr>
<tr>
	<td id="favoriteTableFirstCol">Name:</td>
	<td class="textspace">&nbsp;</td>
	<td class="fullscreen"><?php echo $favorite_name; ?></td>
</tr>

<tr class="space"><td colspan="3"></td></tr>
<tr>
	<td></td>
	<td></td>
	<td>
	</td>
</tr>

<tr class="textspace"><td colspan="3"></td></tr>

<tr class="header">
	<td colspan="3">&nbsp;Tracks in this playlist:</td>
</tr>		
<tr class="line"><td colspan="9"></td></tr>
<tr>
	<td colspan="3">
	<!-- begin indent -->
<div id="favoriteList">
<div style="text-align: center; padding: 1em;">
 <i class="fa fa-cog fa-spin icon-small"></i> Loading track list...
</div>
 </div>
	<!-- end indent -->
	</td>
</tr>
</table>
</form>

<script type="text/javascript">
<!--

$(document).ready(function() {
	var request = $.ajax({  
		url: "ajax-favorite-arrange-Tidal.php",  
		type: "POST",  
		data: { action : 'display',
				favorite_id : '<?php echo $favorite_id; ?>'
			  },  
		dataType: "html"
	}); 

	request.done(function( data ) {  
		$( "#favoriteList" ).html( data );
		//calcTileSize();
	}); 

	request.fail(function( jqXHR, textStatus ) {  
		alert( "Request failed: " + textStatus );	
	}); 
});

function importPlaylist() {
	showSpinner();
	document.favorite.action.value='importPlaylist'; 
	$('#favorite').submit();
}

function addPlaylist() {
	showSpinner();
	document.favorite.action.value='addPlaylist'; 
	$('#favorite').submit();
}

function importPlaylistUrl() {
	showSpinner();
	document.favorite.action.value='importPlaylistUrl'; 
	$('#favorite').submit();
}

function addPlaylistUrl() {
	showSpinner();
	document.favorite.action.value='addPlaylistUrl'; 
	$('#favorite').submit();
}

//-->
</script>

<?php
	require_once('include/footer.inc.php');
}





//  +------------------------------------------------------------------------+
//  | Add favorite                                                           |
//  +------------------------------------------------------------------------+
function addFavorite() {
	global $cfg, $db;
	authenticate('access_admin', false, true, true);
	mysqli_query($db,'INSERT INTO favorite (name) VALUES ("")');
	$favorite_id = mysqli_insert_id($db);
	
	editFavorite($favorite_id);
}




//  +------------------------------------------------------------------------+
//  | Save favorite                                                          |
//  +------------------------------------------------------------------------+
function saveFavorite($favorite_id) {
	global $db;
	authenticate('access_admin', false, true, true);
	$name	 = getpost('name');
	$comment = getpost('comment');
	mysqli_query($db,'UPDATE favorite SET
		name	= "' . mysqli_real_escape_string($db,$name) . '",
		comment	= "' . mysqli_real_escape_string($db,$comment) . '"
		WHERE favorite_id = ' . (int) $favorite_id);
	
	home();
}




//  +------------------------------------------------------------------------+
//  | Import favorite                                                        |
//  +------------------------------------------------------------------------+

function importFavorite($favorite_id, $mode) {
	global $cfg, $db;
	authenticate('access_admin', false, true, true);
	require_once('include/play.inc.php');

	if ($player_host=="" && $player_port==""){
		$player_host = $cfg['player_host'];
		$player_port = $cfg['player_port'];
	}
	
	$name = post('name');
	$comment = post('comment');
	//fix #114
	//$url = strtolower(post('url'));
	$url = post('url');
	$selectPlaylistFrom = post('selectPlaylistFrom');
	
	$query = mysqli_query($db,'SELECT player_host, player_port	FROM player WHERE player_id = ' . mysqli_real_escape_string($db,$selectPlaylistFrom) . '');

	$source_f = mysqli_fetch_assoc($query);

	$player_host = $source_f['player_host'];
	$player_port = $source_f['player_port'];
	
	if ($url != '') {
		if ($mode == 'addUrl' || $mode == 'importUrl') {
			$file = array();
			
			if (preg_match('#\.(m3u|pls)$#', strtolower($url))) {
				$items = @file($url, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				//message(__FILE__, __LINE__, 'error', '[b]Failed to open url:[/b][br]' . $url);
				if ($items) {
					foreach ($items as $item) {
						// pls:		
						// File1=http://example.com:80
						// m3u:
						// http://example.com:80
						if (preg_match('#^(?:File[0-9]{1,3}=|)((?:tidal|ftp|http|https|mms|mmst|pnm|rtp|rtsp|sdp)://.+)#', $item, $match))
							$file[] = $match[1];
						//print_r($item) . '<br>';
					}
				}
				else {
					$addURLresult = 'add_error';
					return $addURLresult;
				}
			}
			elseif (isTidal($url)) {
				$id = getTidalId($url);
				//TIDAL track
				if (strpos($url, MPD_TIDAL_URL) !== false || strpos($url, TIDAL_APP_TRACK_URL) !== false || strpos($url, TIDAL_TRACK_URL) !== false || strpos($url, TIDAL_TRACK_STREAM_URL) !== false) {
					//check if album is in OMPD DB:
					$query = mysqli_query($db,'SELECT track_id FROM tidal_track WHERE 	track_id = "' . mysqli_real_escape_string($db,$id) . '"');
					if (mysqli_num_rows($query) == 0) {
						$album_id = getTrackAlbumFromTidal($id);
						if (!$album_id) {
							$addURLresult = 'add_error';
							return $addURLresult;
						}
						$tidal_tracks = getTracksFromTidalAlbum($album_id);
					}
					$file[] = createStreamUrlMpd('tidal_' . $id);
				}
				//TIDAL album
				elseif (strpos($url,TIDAL_ALBUM_URL) !== false || strpos($url,TIDAL_ALBUM_URL_2) !== false || strpos($url,TIDAL_APP_ALBUM_URL) !== false) {
					$tidal_tracks = getTracksFromTidalAlbum($id);
					$tidal_tracks = json_decode($tidal_tracks, true);
					
					foreach ($tidal_tracks as $tidal_track) {
						$file[] = createStreamUrlMpd('tidal_' . $tidal_track['id']);
						//$mpdCommand = mpdAddTidalTrack('tidal_' . $tidal_track['id']);
					}
				}
			}
			elseif (isYoutube($url)){
				if ($ytUrl = getYouTubeMPDUrl($url)) {
					$file[] = mpdEscapeChar($ytUrl);
				}
			}
			else {
				$file[] = $url;
			}
		}
		else {
			editFavorite($favorite_id);
		}	
	}
	elseif ($mode == 'addUrl' || $mode == 'importUrl'){
		editFavorite($favorite_id);
	}
	elseif ($cfg['player_type'] == NJB_MPD) {
		$file = mpd('playlist',$player_host,$player_port);
		$file = implode('<seperation>', $file);
		$file = iconv(NJB_DEFAULT_CHARSET, 'UTF-8', $file);
		$file = explode('<seperation>', $file);
  }
	else
		message(__FILE__, __LINE__, 'error', '[b]Player not supported[/b]');

	
	$stream = 0;
	$hasStream = 0;
	$hasFiles = 0;
	$isFavStream = 0;
	
	if (count($file) > 0) {
		
		$query = mysqli_query($db,'SELECT stream FROM favorite WHERE favorite_id = ' . (int) $favorite_id);
		$favType = mysqli_fetch_assoc($query);
		$isFavStream = $favType['stream'];
		
		if ($mode == 'import' || $mode == 'importUrl' ) {
			mysqli_query($db,'DELETE FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id);
			$offset = 0;
		}
		
		if ($mode == 'add' || $mode == 'addUrl') {
			$query = mysqli_query($db,'SELECT max(position) as pos FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id);
			$track = mysqli_fetch_assoc($query);
			if (is_null($track['pos']))
				$offset = 0;
			else
				$offset = $track['pos'];
		}
	}
	
			
	for ($i = 0; $i < count($file); $i++) {
		$query = mysqli_query($db,'SELECT track_id FROM track WHERE relative_file = "' . mysqli_real_escape_string($db,$file[$i]) . '"');
		$track = mysqli_fetch_assoc($query);
		$isStream = 0;
		if (preg_match('#^(tidal|ftp|http|https|mms|mmst|pnm|rtp|rtsp|sdp)://#', strtolower($file[$i]))) {
			$isStream = 1;
		}
		
		if ($isStream == 0 && $track['track_id']) {
			$hasFiles = 1;
			$position = $i + $offset + 1;
      cliLog('INSERT INTO favoriteitem (track_id, position, favorite_id)
				VALUES ("' . mysqli_real_escape_string($db,$track['track_id']) . '",
				' . (int) $position . ',
				' . (int) $favorite_id . ')');
			mysqli_query($db,'INSERT INTO favoriteitem (stream_url, track_id, position, favorite_id)
				VALUES ("", "' . mysqli_real_escape_string($db,$track['track_id']) . '",
				' . (int) $position . ',
				' . (int) $favorite_id . ')');
		}
		
		if ($isStream == 1) {
			$hasStream = 1;
			$position = $i + $offset + 1;
			mysqli_query($db,'INSERT INTO favoriteitem (stream_url, position, favorite_id)
				VALUES ("' . mysqli_real_escape_string($db, getTrackMpdUrl($file[$i])) . '",
				' . (int) $position . ',
				' . (int) $favorite_id . ')');
		}
	}
	updateFavoriteStreamStatus($favorite_id);

	editFavorite($favorite_id);
}




//  +------------------------------------------------------------------------+
//  | Delete favorite MPD                                                    |
//  +------------------------------------------------------------------------+
function deleteFavoriteMPD($favorite_id) {
	require_once('include/play.inc.php');
	require_once('include/library.inc.php');
	global $db, $cfg;
	authenticate('access_admin', false, true, true);
	$query1=mysqli_query($db,'SELECT player.player_name as pl, player_host, player_port, player_pass FROM player, session WHERE (sid = BINARY "' . cookie('netjukebox_sid') . '") and player.player_id=session.player_id');
	$session1 = mysqli_fetch_assoc($query1);
	$cfg['player_host'] = $session1['player_host'];
	$cfg['player_port'] = $session1['player_port'];
	$cfg['player_pass'] = $session1['player_pass'];
	mpd('rm "' . $favorite_id . '"');
	home();
}




//  +------------------------------------------------------------------------+
//  | Delete favorite                                                        |
//  +------------------------------------------------------------------------+
function deleteFavorite($favorite_id) {
	global $db;
	authenticate('access_admin', false, true, true);
	mysqli_query($db,'DELETE FROM favorite WHERE favorite_id = ' . (int) $favorite_id);
	mysqli_query($db,'DELETE FROM favoriteitem WHERE favorite_id = ' . (int) $favorite_id);
	home();
}




//  +------------------------------------------------------------------------+
//  | Delete favorite item                                                   |
//  +------------------------------------------------------------------------+
function deleteFavoriteItem($favorite_id) {
	global $db;
	authenticate('access_admin', false, true, true);
	$position = get('position');
	mysqli_query($db,'DELETE FROM favoriteitem
		WHERE favorite_id = ' . (int) $favorite_id . '
		AND position = ' . (int) $position);
	editFavorite($favorite_id);
}


?>