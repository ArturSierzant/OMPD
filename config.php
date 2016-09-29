<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant	                         |
//  | http://www.ompd.pl                                             		 |
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
//  | config.php                                                             |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
$cfg['menu'] = 'config';


$action = getpost('action');
$update = getpost('update');

if 		($action == '')							config();

elseif 	($action == 'playerProfile')			playerProfile();
elseif 	($action == 'editPlayerProfile')		editPlayerProfile();
elseif	($action == 'setPlayerProfile')			{setPlayerProfile();			playerProfile();}
elseif	($action == 'setDefaultPlayerProfile')	{setDefaultPlayerProfile();		playerProfile();}
elseif	($action == 'savePlayerProfile')		{savePlayerProfile();			playerProfile();}
elseif	($action == 'deletePlayerProfile')		{deletePlayerProfile();			playerProfile();}

elseif	($action == 'streamProfile')			streamProfile();
elseif	($action == 'setStreamProfile')			{setStreamProfile();			streamProfile();}
elseif	($action == 'setDefaultStreamProfile')	{setDefaultStreamProfile();		streamProfile();}

elseif	($action == 'downloadProfile')			downloadProfile();
elseif	($action == 'setDownloadProfile')		{setDownloadProfile();			downloadProfile();}
elseif	($action == 'setDefaultDownloadProfile'){setDefaultDownloadProfile();	downloadProfile();}

elseif	($action == 'skinProfile')				skinProfile();
elseif	($action == 'setSkinProfile')			{setSkinProfile();				skinProfile();}
elseif	($action == 'setDefaultSkinProfile')	{setDefaultSkinProfile();		skinProfile();}

elseif 	($action == 'shareImage' && $cfg['image_share']) 						shareImage();

elseif 	($action == 'batchTranscode')			batchTranscode();
elseif 	($action == 'cacheDeleteProfile')		{cacheDeleteProfile();			batchTranscode();}

elseif	($action == 'externalStorage')			externalStorage();
elseif	($action == 'deleteExternalStorage')	{deleteExternalStorage();		externalStorage();}

else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');




//  +------------------------------------------------------------------------+
//  | Config                                                                 |
//  +------------------------------------------------------------------------+
function config() {
	global $cfg, $db, $update;
	authenticate('access_logged_in');
	require_once('include/play.inc.php');
	
	if ($cfg['stream_id'] == -1)	$stream = 'Source';
	else							$stream = $cfg['encode_name'][$cfg['stream_id']];
	if ($cfg['download_id'] == -1)	$download = 'Source';
	else							$download = $cfg['encode_name'][$cfg['download_id']];
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	require_once('include/header.inc.php');
	$i = 0;
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>Session profile</td>
	<td class="textspace"></td>
	<td>Comment</td>
	<td class="space"></td>
</tr>

<?php
	if ($cfg['access_playlist'] || $cfg['access_play'] || $cfg['access_add'] || $cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="config.php?action=playerProfile"><i class="fa fa-music fa-fw icon-small"></i>Player&nbsp;profile</a></td>
	<td></td>
	<td><?php echo html($cfg['player_name']); ?></td>
	<td></td>
</tr>
<?php
	}
	if ($cfg['access_stream'] || $cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="config.php?action=streamProfile"><i class="fa fa-rss fa-fw icon-small"></i>Stream&nbsp;profile</a></td>
	<td></td>
	<td><?php echo html($stream); ?></td>
	<td></td>
</tr>
<?php
	}
	if ($cfg['access_download'] || $cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="config.php?action=downloadProfile"><i class="fa fa-download fa-fw icon-small"></i>Download&nbsp;profile</a></td>
	<td></td>
	<td><?php echo html($download); ?></td>
	<td></td>
</tr>
<?php
	}
?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="config.php?action=skinProfile"><i class="fa fa-eye fa-fw icon-small"></i>Skin&nbsp;profile</a></td>
	<td></td>
	<td><?php echo html($cfg['skin']); ?></td>
	<td></td>
</tr>

<tr class="header">
	<td class="space"></td>
	<td>Configuration</td>
	<td class="textspace"></td>
	<td>Comment</td>
	<td class="space"></td>
</tr>

<?php
	$i = 0;
	if ($cfg['access_admin'] == false) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="users.php?action=accessRight"><i class="fa fa-user fa-fw icon-small"></i>Access&nbsp;right</a></td>
	<td></td>
	<td><?php echo html($cfg['username']); ?></td>
	<td></td>
</tr>
<?php
	}
	if ($cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="users.php"><i class="fa fa-users fa-fw icon-small"></i>Users</a></td>
	<td></td>
	<td>Users access rights</td>
	<td></td>
</tr>
<?php
	}
	if ($cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="users.php?action=online">
  <i class="fa fa-bolt fa-fw icon-small"></i>Online</a></td>
	<td></td>
	<td>Online in the last 24 hours</td>
	<td></td>
</tr>
<?php
	}
	if ($cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="users.php?action=userStatistics&amp;period=overall"><i class="fa fa-bar-chart fa-fw icon-small"></i>User statistics</a></td>
	<td></td>
	<td>Show user statistics</td>
	<td></td>
</tr>
<?php
	}
	if ($cfg['access_statistics']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="statistics.php" onclick="showSpinner();"><i class="fa fa-line-chart fa-fw icon-small"></i>Media&nbsp;statistics</a></td>
	<td></td>
	<td>Show media statistics</td>
	<td></td>
</tr>
<?php
	}
/*
	if ($cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="genre.php?action=genreStructure"><img src="<?php echo $cfg['img']; ?>medium_genre.png" alt="" class="medium">Genre&nbsp;structure</a></td>
	<td></td>
	<td>Edit genre structure</td>
	<td></td>
</tr>
<?php
	}
*/
	if ($update == 'cancel') 
		mysqli_query($db, "UPDATE update_progress 
		SET update_status = 0,
		last_update = 'canceled',
		update_time = '" . date('Y-m-d, H:i') . "'");
	if ($cfg['access_admin']) { 
		$update_info = '';
		$result = mysqli_query($db, "SELECT * FROM update_progress");
		$row=mysqli_fetch_assoc($result);
		if ($row["update_status"] <> 1 && mysqli_num_rows($result)>0) {
			$last_update=explode(', ',$row["last_update"]);
			$update_time=$row["update_time"];
			if ($last_update || $update_time) {
				$update_info = '<span class="update_info"><br>last update: ' . $last_update[0] . '<br> at ' . $last_update[1] . ' (' . $row["update_time"] .')</span>';
			}
		} 
		elseif (mysqli_num_rows($result)>0) {
			$update_info = '<span class="update_info"><br>' . $row['last_update'] . '</span><span onClick="$(\'#cancel\').show();" class="pointer">.&nbsp;</span>&nbsp;<a href="config.php?update=cancel" id="cancel" class="no-display"><i class="fa fa-times-circle pointer icon-selected"></i></a>';
			$spin = ' fa-spin icon-selected';
		}
	?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="update"><a href="update.php?action=update&amp;sign=<?php echo $cfg['sign']; ?>"><i class="fa fa-refresh fa-fw icon-small <?php echo $spin ?>"></i>Update</a></td>
	<td></td>
	<td>Update media <?php echo $update_info?></td>
	<td></td>
</tr>
<?php
	}
/*	
	$no_image		= mysqli_num_rows(mysqli_query($db, 'SELECT album_id FROM bitmap WHERE flag = 0'));
	$skipped_image	= mysqli_num_rows(mysqli_query($db, 'SELECT album_id FROM bitmap WHERE flag = 1 OR flag = 2'));

	if ($cfg['access_admin'] && $no_image > 0) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="update.php?action=imageUpdate&amp;flag=0"><img src="<?php echo $cfg['img']; ?>medium_image.png" alt="" class="medium">Update&nbsp;image</a></td>
	<td></td>
	<td><?php echo $no_image . (($no_image == 1) ? ' undefined image' : ' undefined images'); ?></td>
	<td></td>
</tr>
<?php
	}

	if ($cfg['access_admin'] && $skipped_image > 0)	{ ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="update.php?action=imageUpdate&amp;flag=2"><img src="<?php echo $cfg['img']; ?>medium_image.png" alt="" class="medium">Update&nbsp;image</a></td>
	<td></td>
	<td><?php echo $skipped_image . (($skipped_image == 1) ? ' skipped image' : ' skipped images'); ?></td>
	<td></td>
</tr>
<?php
	}

	if ($cfg['access_admin'] && is_dir($cfg['external_storage'])) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="config.php?action=externalStorage"><img src="<?php echo $cfg['img']; ?>medium_external_storage.png" alt="" class="medium">External&nbsp;storage</a></td>
	<td></td>
	<td>File manager for external storage</td>
	<td></td>
</tr>
<?php
	}	
	if ($cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="config.php?action=batchTranscode"><img src="<?php echo $cfg['img']; ?>medium_transcode.png" alt="" class="medium">Batch&nbsp;transcode</a></td>
	<td></td>
	<td>Batch transcode media</td>
	<td></td>
</tr>
<?php
	}	
	if ($cfg['access_admin'] && $cfg['image_share']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="config.php?action=shareImage"><img src="<?php echo $cfg['img']; ?>medium_share.png" alt="" class="medium">Share&nbsp;image</a></td>
	<td></td>
	<td>Enabled in the configuration file</td>
	<td></td>
</tr>
<?php
	}
*/
	if ($cfg['access_admin'] && $cfg['php_info']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td><a href="phpinfo.php"><i class="fa fa-info-circle fa-fw icon-small"></i>PHP information</a></td>
	<td></td>
	<td>Enabled in the configuration file</td>
	<td></td>
</tr>
<?php
	} 

?>
</table>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Player profile                                                         |
//  +------------------------------------------------------------------------+
function playerProfile() {
	global $cfg, $db;
	authenticate(array('access_playlist', 'access_play','access_add', 'access_admin'));
	require_once('include/play.inc.php');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Player profile';
	require_once('include/header.inc.php');
	
	// Get default player_id
	$query = mysqli_query($db, 'DESCRIBE session player_id');
	$default = mysqli_fetch_assoc($query);
	$default = $default['Default'];
	$query = mysqli_query($db, 'SELECT player_id
		FROM player
		WHERE player_id = ' . mysqli_real_escape_string($db, $default));
	if (mysqli_num_rows($query) == 0) {
		$query = mysqli_query($db, 'SELECT player_id
			FROM player
			ORDER BY player_name');
	}
	$default = mysqli_fetch_assoc($query);
?>

	<?php if ($cfg['access_admin']) echo '<div class="buttons">
	<span><a href="config.php?action=editPlayerProfile&amp;player_id=0&amp;sign=' . $cfg['sign'] . '" onMouseOver="return overlib(\'Add player profile\');" onMouseOut="return nd();">Add new</a></span></div>'; ?>
	

<table cellspacing="0" cellpadding="0" class="border">

<tr class="header">
	<td class="space"></td>
	<td>Player profile</td>
	<td>Default</td>
	<td>Delete</td>
	<td>Edit</td>
	
	
</tr>

<?php
	$i=0;
	$query = mysqli_query($db, 'SELECT player_name, player_type, player_id FROM player ORDER BY player_name');
	
	while ($player = mysqli_fetch_assoc($query)) {
		$check = ($player['player_id'] == $default['player_id']) ? '<i class="fa fa-check-circle-o fa-fw icon-small"></i>' : '<i class="fa fa-circle-o fa-fw icon-small"></i>'; ?>
<tr class="<?php if ($player['player_id'] == $cfg['player_id']) echo 'select'; else echo ($i & 1) ? 'even' : 'odd'; $i++; ?>">
	<td class="space"></td>
	<td><a href="config.php?action=setPlayerProfile&amp;player_id=<?php echo $player['player_id']; ?>&amp;sign=<?php echo $cfg['sign']; ?>"><i class="fa fa-music fa-fw icon-small"></i><?php echo html($player['player_name']); ?></a>
	</td>
	<td><?php if ($cfg['access_admin']) echo '<a href="config.php?action=setDefaultPlayerProfile&amp;player_id=' . $player['player_id'] . '&amp;sign=' . $cfg['sign'] . '" onMouseOver="return overlib(\'Set default player profile\');" onMouseOut="return nd();">' . $check . '</a>'; ?></td>
	<td><?php if ($cfg['access_admin']) echo '<a href="config.php?action=deletePlayerProfile&amp;player_id=' . $player['player_id'] . '&amp;sign=' . $cfg['sign'] . '" onClick="return confirm(\'Are you sure you want to delete player profile: ' . addslashes(html($player['player_name'])) . '?\');" onMouseOver="return overlib(\'Delete\');" onMouseOut="return nd();"><i class="fa fa-times-circle fa-fw icon-small"></i></a>'; ?></td>
	<td><?php if ($cfg['access_admin']) echo '<a href="config.php?action=editPlayerProfile&amp;player_id=' . $player['player_id'] . '" onMouseOver="return overlib(\'Edit\');" onMouseOut="return nd();"><i class="fa fa-pencil fa-fw icon-small"></i></a>'; ?></td>
</tr>
<?php
	}
	echo '</table>' . "\n";
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Edit player profile                                                    |
//  +------------------------------------------------------------------------+
function editPlayerProfile() {
	global $cfg, $db;
	$player_id = (int) get('player_id');
	
	if ($player_id == 0) {
		// Add configuraton
		authenticate('access_admin', false, true);
		require_once('include/play.inc.php'); // Get default profile or selected profile
			
		$txt_menu = 'Add profile';
		mysqli_query($db, 'INSERT INTO player (player_name) VALUES ("")');
		$player_id = mysqli_insert_id($db);
	}
	else {
		// Edit configutaion
		authenticate('access_admin');
		
		$txt_menu = 'Edit profile';
		$query = mysqli_query($db, 'SELECT player_name, player_type, player_host, player_port, player_pass, media_share FROM player WHERE player_id = ' . (int) $player_id);
		$player = mysqli_fetch_assoc($query);
		
		if ($player == false)
			message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]player_id not found in database');
		
		$txt_menu			= 'Edit profile';
		$cfg['player_name']	= $player['player_name'];
		$cfg['player_type']	= $player['player_type'];
		$cfg['player_host']	= $player['player_host'];
		$cfg['player_port']	= $player['player_port'];
		$cfg['player_pass']	= $player['player_pass'];
		$cfg['media_share']	= $player['media_share'];
	}
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Player profile';
	$nav['url'][]	= 'config.php?action=playerProfile';
	$nav['name'][]	= $txt_menu;
	require_once('include/header.inc.php');
?>
<form action="config.php" method="post" name="config" id="config" autocomplete="off">
		<input type="hidden" name="action" value="savePlayerProfile">
		<input type="hidden" name="player_id" value="<?php echo $player_id ; ?>">
		<input type="hidden" name="sign" value="<?php echo $cfg['sign']; ?>">
<table cellspacing="0" cellpadding="1">
<tr>
	<td>Name:</td>
	<td class="textspace"></td>
	<td><input type="text" name="name" value="<?php echo html($cfg['player_name']); ?>" maxlength="255" class="edit"></td>
</tr>

<tr class="textspace"><td colspan="3"></td></tr>

<tr>
	<td>Player:</td>
	<td></td>
	<td><input type="radio" name="player_type" value="2" <?php if ($cfg['player_type'] == NJB_MPD) echo 'checked '; ?>class="space" onClick="mpdDefault()"> Only! Music Player Daemon</td>
</tr>
<tr class="textspace"><td colspan="3"></td></tr>

<tr>
	<td>Auto detect:</td>
	<td></td>
	<td><a href="javascript:serverDefault();"><i class="fa fa-database fa-fw icon-small"></i>Server</a></td>
</tr>
<tr>
	<td></td>
	<td></td>
	<td><a href="javascript:clientDefault();"><i class="fa fa-desktop fa-fw icon-small"></i>Client</a></td>
</tr>

<tr class="textspace"><td colspan="3"></td></tr>

<tr>
	<td>Player host:</td>
	<td></td>
	<td><input type="text" name="player_host" value="<?php echo html($cfg['player_host']); ?>" maxlength="255" class="edit"></td>
</tr>
<tr>
	<td>Player port:</td>
	<td></td>
	<td><input type="text" name="player_port" value="<?php echo $cfg['player_port']; ?>" maxlength="5" class="edit"></td>
</tr>
<tr class="textspace"><td colspan="3"></td></tr>

<tr>
	<td colspan="2"></td>
	<td>
		<div class="buttons">
		<span>
		<a href="#" onclick="$(config).submit();">Save</a>
		</span>
		<span>
		<a href="config.php?action=playerProfile" class="align">Cancel</a>
		</span>
		</div>
	</td>
</tr>
</table>
</form>

<?php
	$temp = explode('/', $cfg['media_dir']);
?>
<script type="text/javascript">
<!--
function initialize() {
	document.config.name.focus();
<?php
	if ($cfg['player_type'] == NJB_MPD) {
		echo "\tdocument.config.player_pass.className = 'edit readonly';\n";
		echo "\tdocument.config.media_share.className = 'edit readonly';\n";
		echo "\tdocument.config.player_pass.disabled = true;\n";
		echo "\tdocument.config.media_share.disabled = true;\n";
	}
	if ($cfg['player_type'] == NJB_VLC){
		echo "\tdocument.config.player_pass.className = 'edit readonly';\n";
		echo "\tdocument.config.player_pass.disabled = true;\n";
	} ?>
}
	
	
	
function serverDefault() {
	document.config.player_host.value = '127.0.0.1';
	if (document.config.media_share.className != 'edit readonly')
		document.config.media_share.value = '<?php echo $cfg['media_dir']; ?>';
}
	
	
function clientDefault() {
	document.config.player_host.value = '<?php echo gethostbyaddr($_SERVER['REMOTE_ADDR']); ?>';
	if (document.config.media_share.className != 'edit readonly')
		document.config.media_share.value = '<?php echo (NJB_WINDOWS) ? '//' : '/'; echo (isset($_SERVER['SERVER_ADDR'])) ? $_SERVER['SERVER_ADDR'] : $_SERVER['SERVER_NAME']; ?>/<?php echo $temp[count($temp) - 2]; ?>/';
}

		
function httpqDefault()	{
	document.config.name.value = 'httpQ (<?php echo $player_id; ?>)';
	document.config.player_port.value = '4800';
	document.config.player_pass.value = 'pass';
	document.config.player_pass.className = 'edit';
	document.config.media_share.className = 'edit';
	document.config.player_pass.disabled = false;
	document.config.media_share.disabled = false;
	serverDefault();
}
	

function vlcDefault() {
	document.config.name.value = 'VideoLAN (<?php echo $player_id; ?>)';
	document.config.player_port.value = '8080';
	document.config.player_pass.value = '';
	document.config.player_pass.className = 'edit readonly';
	document.config.media_share.className = 'edit';
	document.config.player_pass.disabled = true;
	document.config.media_share.disabled = false;
	serverDefault();
}


function mpdDefault() {
	document.config.name.value = 'Music Player Daemon (<?php echo $player_id; ?>)';
	document.config.player_port.value = '6600';
	document.config.player_pass.value = '';
	document.config.media_share.value = '';
	document.config.player_pass.className = 'edit readonly';
	document.config.media_share.className = 'edit readonly';
	document.config.player_pass.disabled = true;
	document.config.media_share.disabled = true;
	serverDefault();
}
//-->
</script>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | URL syntax fix                                                         |
//  +------------------------------------------------------------------------+
function urlSyntaxFix($url) {
	$url = trim($url);
	$url = str_replace('\\', '/', $url);
	return rtrim($url, '/') . '/';
}




//  +------------------------------------------------------------------------+
//  | Save player profile                                                    |
//  +------------------------------------------------------------------------+
function savePlayerProfile() {
	global $cfg, $db;
	authenticate('access_admin', false, true, true);
	
	$player_id 		= post('player_id');
	$player_name	= post('name');
	$player_type	= post('player_type');
	$player_host	= post('player_host');
	$player_port	= post('player_port');
	$player_pass	= post('player_pass');
	$media_share	= post('media_share');
	$media_share	= urlSyntaxFix($media_share);
	
	if ($player_type == NJB_VLC) {
		$player_pass = '';
	}
		
	if ($player_type == NJB_MPD) {
		$player_pass = '';
		$media_share = '';
	}
		
	mysqli_query($db, 'UPDATE player SET
		player_name	= "' . mysqli_real_escape_string($db, $player_name) . '",
		player_type	= ' . (int) $player_type . ',
		player_host	= "' . mysqli_real_escape_string($db, $player_host) . '",
		player_port	= ' . (int) $player_port . ',
		player_pass	= "' . mysqli_real_escape_string($db, $player_pass) . '",
		media_share	= "' . mysqli_real_escape_string($db, $media_share) . '"
		WHERE player_id = ' . (int) $player_id);
}




//  +------------------------------------------------------------------------+
//  | Set player profile                                                     |
//  +------------------------------------------------------------------------+

function setPlayerProfile() {
	global $cfg, $db;
	authenticate(array('access_playlist', 'access_play','access_add', 'access_admin'), false, true);
	
	$player_id = (int) get('player_id');
	
	mysqli_query($db, 'UPDATE session SET
		player_id		= ' . (int) $player_id . '
		WHERE sid		= BINARY "' . mysqli_real_escape_string($db, $cfg['sid']) . '"');
}




//  +------------------------------------------------------------------------+
//  | Set default player profile                                             |
//  +------------------------------------------------------------------------+
function setDefaultPlayerProfile() {
	global $cfg, $db;
	authenticate('access_admin', false, true, true);
	
	$player_id = (int) get('player_id');
	
	mysqli_query($db, 'ALTER TABLE session
		CHANGE player_id player_id INT( 10 ) NOT NULL DEFAULT ' . (int) $player_id);
}




//  +------------------------------------------------------------------------+
//  | Delete player profile                                                  |
//  +------------------------------------------------------------------------+
function deletePlayerProfile() {
	global $db;
	authenticate('access_admin', false, true, true);
	
	$player_id = (int) get('player_id');
	
	mysqli_query($db, 'DELETE FROM player WHERE player_id = ' . (int) $player_id);
}




//  +------------------------------------------------------------------------+
//  | Stream profile                                                         |
//  +------------------------------------------------------------------------+
function streamProfile() {
	global $cfg, $db;
	authenticate(array('access_stream', 'access_admin'));
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Stream profile';
	require_once('include/header.inc.php');
	
	// Get default stream_id
	$query = mysqli_query($db, 'DESCRIBE session stream_id');
	$default = mysqli_fetch_assoc($query);
	$default = $default['Default'];
	if ($default != -1 && isset($cfg['encode_extension'][$default]) == false) {
		$default = -1;
		mysqli_query($db, 'ALTER TABLE session
			CHANGE download_id download_id INT( 10 ) NOT NULL DEFAULT ' . (int) $default);
	}
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>Stream profile</td>
	<td class="textspace"></td>
	<td align="right">Bitrate</td>
	<td<?php if ($cfg['access_admin']) echo ' class="textspace"'; ?>></td>
	<td>Default</td><!-- optional default stream profile -->
	<td class="space"></td>
</tr>

<?php
	$i = 0;
	foreach ($cfg['encode_name'] as $profile => $value) {
		$check = ($profile == $default) ? '<i class="fa fa-check-circle-o fa-fw icon-small"></i>' : '<i class="fa fa-circle-o fa-fw icon-small"></i>'; ?>
<tr class="<?php if ($profile == $cfg['stream_id']) echo 'select'; else echo ($i & 1) ? 'even' : 'odd'; $i++ ?>">
	<td></td>
	<td>&nbsp;<a href="config.php?action=setStreamProfile&amp;stream_id=<?php echo $profile; ?>&amp;sign=<?php echo $cfg['sign']; ?>"><i class="fa fa-rss icon-small"></i><?php echo html($value); ?></a></td>
	<td></td>
	<td align="right"><?php if ($cfg['encode_vbr'][$profile]) echo '&plusmn; '; echo formattedBirate($cfg['encode_bitrate'][$profile]); ?></td>
	<td></td>
	<td><?php if ($cfg['access_admin']) echo '<a href="config.php?action=setDefaultStreamProfile&amp;stream_id=' . $profile . '&amp;sign=' . $cfg['sign'] . '" onMouseOver="return overlib(\'Set default stream profile\');" onMouseOut="return nd();">' . $check . '</a>'; ?></td>	
	<td></td>
</tr>
<?php
	}
	$check = ($default == -1) ? '<i class="fa fa-check-circle-o fa-fw icon-small"></i>' : '<i class="fa fa-circle-o fa-fw icon-small"></i>'; ?>
<tr class="<?php if ($cfg['stream_id'] == -1) echo 'select'; else echo ($i & 1) ? 'even' : 'odd'; $i++ ?>">
	<td></td>
	<td>&nbsp;<a href="config.php?action=setStreamProfile&amp;stream_id=-1&amp;sign=<?php echo $cfg['sign']; ?>"><i class="fa fa-rss icon-small"></i>Source</a></td>
	<td></td>
	<td></td>
	<td></td>
	<td><?php if ($cfg['access_admin']) echo '<a href="config.php?action=setDefaultStreamProfile&amp;stream_id=-1&amp;sign=' . $cfg['sign'] . '" onMouseOver="return overlib(\'Set default stream profile\');" onMouseOut="return nd();">' . $check . '</a>'; ?></td>
	<td></td>
</tr>
</table>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Set stream profile                                                     |
//  +------------------------------------------------------------------------+
function setStreamProfile() {
	global $cfg, $db;
	authenticate(array('access_stream', 'access_admin'), false, true);
	
	$stream_id = (int) get('stream_id');
	
	if ($stream_id != -1 && isset($cfg['encode_extension'][$stream_id]) == false)
		message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]stream_id');
	
	mysqli_query($db, 'UPDATE session
		SET stream_id	= ' . (int) $stream_id . '
		WHERE sid		= BINARY "' . mysqli_real_escape_string($db, $cfg['sid']) . '"');
}




//  +------------------------------------------------------------------------+
//  | Set default stream profile                                             |
//  +------------------------------------------------------------------------+
function setDefaultStreamProfile() {
	global $cfg, $db;
	authenticate('access_admin', false, true, true);
	
	$stream_id = (int) get('stream_id');
	
	if ($stream_id != -1 && isset($cfg['encode_extension'][$stream_id]) == false)
		message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]stream_id');
	
	mysqli_query($db, 'ALTER TABLE session
		CHANGE stream_id stream_id INT( 10 ) NOT NULL DEFAULT ' . (int) $stream_id);
}




//  +------------------------------------------------------------------------+
//  | Download profile                                                       |
//  +------------------------------------------------------------------------+
function downloadProfile() {
	global $cfg, $db;
	authenticate(array('access_download', 'access_admin'));
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Download profile';
	require_once('include/header.inc.php');
	
	// Get default download_id
	$query = mysqli_query($db, 'DESCRIBE session download_id');
	$default = mysqli_fetch_assoc($query);
	$default = $default['Default'];
	if ($default != -1 && isset($cfg['encode_extension'][$default]) == false) {
		$default = -1;
		mysqli_query($db, 'ALTER TABLE session
			CHANGE download_id download_id INT( 10 ) NOT NULL DEFAULT ' . (int) $default);
	}
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>Download profile</td>
	<td class="textspace"></td>
	<td align="right">Bitrate</td>
	<td<?php if ($cfg['access_admin']) echo ' class="textspace"'; ?>></td>
	<td>Default</td><!-- optional default download profile -->
	<td class="space"></td>
</tr>

<?php
	$i = 0;
	foreach ($cfg['encode_name'] as $profile => $value) {
		$check = ($profile == $default) ? '<i class="fa fa-check-circle-o fa-fw icon-small"></i>' : '<i class="fa fa-circle-o fa-fw icon-small"></i>'; ?>
<tr class="<?php if ($profile == $cfg['download_id']) echo 'select'; else echo ($i & 1) ? 'even' : 'odd'; $i++ ?>">
	<td></td>
	<td><a href="config.php?action=setDownloadProfile&amp;download_id=<?php echo $profile; ?>&amp;sign=<?php echo $cfg['sign']; ?>"><i class="fa fa-download fa-fw icon-small"></i><?php echo html($value); ?></a></td>
	<td></td>
	<td align="right"><?php if ($cfg['encode_vbr'][$profile]) echo '&plusmn; '; echo formattedBirate($cfg['encode_bitrate'][$profile]); ?></td>	
	<td></td>
	<td><?php if ($cfg['access_admin']) echo '<a href="config.php?action=setDefaultDownloadProfile&amp;download_id=' . $profile . '&amp;sign=' . $cfg['sign'] . '" onMouseOver="return overlib(\'Set default download profile\');" onMouseOut="return nd();">' . $check . '</a>'; ?></td>
	<td></td>
</tr>
<?php
	}
	$check = ($default == -1) ? '<i class="fa fa-check-circle-o fa-fw icon-small"></i>' : '<i class="fa fa-circle-o fa-fw icon-small"></i>'; ?>
<tr class="<?php if ($cfg['download_id'] == -1) echo 'select'; else echo ($i & 1) ? 'even' : 'odd'; $i++ ?>">
	<td></td>
	<td><a href="config.php?action=setDownloadProfile&amp;download_id=-1&amp;sign=<?php echo $cfg['sign']; ?>"><i class="fa fa-download fa-fw icon-small"></i>Source</a></td>
	<td></td>
	<td></td>
	<td></td>
	<td><?php if ($cfg['access_admin']) echo '<a href="config.php?action=setDefaultDownloadProfile&amp;download_id=-1&amp;sign=' . $cfg['sign'] . '" onMouseOver="return overlib(\'Set default download profile\');" onMouseOut="return nd();">' . $check . '</a>'; ?></td>
	<td></td>
</tr>
</table>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Set download profile                                                   |
//  +------------------------------------------------------------------------+
function setDownloadProfile() {
	global $cfg, $db;
	authenticate(array('access_download', 'access_admin'), false, true);
	
	$download_id = (int) get('download_id');
	
	if ($download_id != -1 && isset($cfg['encode_extension'][$download_id]) == false)
		message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]download_id');
	
	mysqli_query($db, 'UPDATE session
		SET download_id	= ' . (int) $download_id . '
		WHERE sid		= BINARY "' . mysqli_real_escape_string($db, $cfg['sid']) . '"');
}




//  +------------------------------------------------------------------------+
//  | Set default download profile                                           |
//  +------------------------------------------------------------------------+
function setDefaultDownloadProfile() {
	global $cfg, $db;
	authenticate('access_admin', false, true, true);
	
	$download_id = (int) get('download_id');
	
	if ($download_id != -1 && isset($cfg['encode_extension'][$download_id]) == false)
		message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]download_id');
	
	mysqli_query($db, 'ALTER TABLE session
		CHANGE download_id download_id INT( 10 ) NOT NULL DEFAULT ' . (int) $download_id);
}




//  +------------------------------------------------------------------------+
//  | Skin profile                                                           |
//  +------------------------------------------------------------------------+
function skinProfile() {
	global $cfg, $db;
	authenticate('access_logged_in');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Skin profile';
	require_once('include/header.inc.php');
	
	// Get default skin
	$query = mysqli_query($db, 'DESCRIBE session skin');
	$default = mysqli_fetch_assoc($query);
	$default = $default['Default'];
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>Skin profile</td>
	<td<?php if ($cfg['access_admin']) echo ' class="textspace"'; ?>></td>
	<td>Default</td><!-- optional default skin profile -->
	<td class="space"></td>
</tr>

<?php
	$i = 0;
	$dir = NJB_HOME_DIR . 'skin/';
	$entries = @scandir($dir) or message(__FILE__, __LINE__, 'error', '[b]Failed to open directory:[/b][br]' . $dir);
	foreach ($entries as $entry)
		if ($entry[0] != '.' && is_dir($dir . $entry . '/')) {
			$check = ($entry == $default) ? '<i class="fa fa-check-circle-o fa-fw icon-small"></i>' : '<i class="fa fa-circle-o fa-fw icon-small"></i>';
?>
<tr class="<?php if ($cfg['skin'] ==  $entry) echo 'select'; else echo ($i & 1) ? 'even' : 'odd'; $i++ ?>">
	<td></td>
	<td><a href="config.php?action=setSkinProfile&amp;skin=<?php echo rawurlencode($entry); ?>&amp;sign=<?php echo $cfg['sign']; ?>"><i class="fa fa-eye fa-fw icon-small"></i><?php echo html($entry); ?></a></td>
	<td></td>
	<td><?php if ($cfg['access_admin']) echo '<a href="config.php?action=setDefaultSkinProfile&amp;skin=' . rawurlencode($entry) . '&amp;sign=' . $cfg['sign'] . '" onMouseOver="return overlib(\'Set default skin profile\');" onMouseOut="return nd();">' .  $check . '</a>'; ?></td>
	<td></td>
</tr>
<?php
		} ?>
</table>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Set skin profile                                                       |
//  +------------------------------------------------------------------------+
function setSkinProfile() {
	global $cfg, $db;
	authenticate('access_logged_in', false, true, true);
	
	$skin = get('skin');
		
	if (validateSkin($skin) == false)
		message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]skin');
	
	mysqli_query($db, 'UPDATE session
		SET skin	= "' . mysqli_real_escape_string($db, $skin) . '"
		WHERE sid	= BINARY "' . mysqli_real_escape_string($db, $cfg['sid']) . '"');
}




//  +------------------------------------------------------------------------+
//  | Set default skin profile                                               |
//  +------------------------------------------------------------------------+
function setDefaultSkinProfile() {
	global $cfg, $db;
	authenticate('access_admin', false, true, true);
	
	$skin = get('skin');
	
	if (validateSkin($skin) == false)
		message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]skin');
	
	mysqli_query($db, 'ALTER TABLE session
		CHANGE skin skin VARCHAR(255) NOT NULL DEFAULT "' . mysqli_real_escape_string($db, $skin) . '"');
}




//  +------------------------------------------------------------------------+
//  | Share image                                                            |
//  +------------------------------------------------------------------------+
function shareImage() {
	global $cfg;
	authenticate('access_admin');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Share image';
	require_once('include/header.inc.php');
	
	$bbcode	= '[url=' . NJB_HOME_URL . 'index.php?action=view3][img]' . NJB_HOME_URL . 'image.php/image.png[/img][/url]';
	$html	= '<a href="' . NJB_HOME_URL . 'index.php?action=view3"><img src="' . NJB_HOME_URL . 'image.php" alt="" border="0"></a>';
	$url	= NJB_HOME_URL . 'image.php';
?>
<form action="" name="form" id="form">
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td colspan="3">Copy and paste</td>
	<td class="space"></td>
</tr>

<tr class="odd">
	<td></td>
	<td>BB-Code:</td>
	<td class="textspace"></td>
	<td><input type="text" value="<?php echo html($bbcode); ?>" readonly class="url" onClick="focus(this); select(this);"></td>
	<td></td>
</tr>
<tr class="even">
	<td></td>
	<td>HTML-Code:</td>
	<td class="textspace"></td>
	<td><input type="text" value="<?php echo html($html); ?>" readonly class="url" onClick="focus(this); select(this);"></td>
	<td></td>
</tr>
<tr class="odd">
	<td></td>
	<td>URL only:</td>
	<td></td>
	<td><input type="text" value="<?php echo $url; ?>" readonly class="url" onClick="focus(this); select(this);"></td>
	<td></td>
</tr>
</table>
</form>
<br><br>
<?php
	echo $html;
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Batch transcode                                                        |
//  +------------------------------------------------------------------------+
function batchTranscode() {
	global $cfg, $db;
	authenticate('access_admin');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Batch transcode';
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>Batch transcode</td>
	<td class="textspace"></td>
	<td align="right">Bitrate</td>
	<td class="textspace"></td>
	<td align="right">Cache size</td>
	<td class="textspace"></td>
	<td></td>
	<td class="space"></td>
</tr>

<?php
	$i = 0;
	foreach ($cfg['encode_name'] as $profile => $value) {
		$query = mysqli_query($db, 'SELECT SUM(filesize) AS sumsize
			FROM cache
			WHERE profile = ' . (int) $profile . ' &&
			LOWER(SUBSTRING_INDEX(relative_file, ".", -1)) != "' . mysqli_real_escape_string($db, $cfg['download_album_extension']) . '"');
		$cache = mysqli_fetch_assoc($query)
?>
<tr class="<?php echo ($i & 1) ? 'even' : 'odd'; $i++ ?> mouseover">
	<td></td>
	<td><a href="download.php?action=batchTranscodeInit&amp;profile=<?php echo $profile; ?>&amp;sign=<?php echo $cfg['sign']; ?>" onClick="return confirm('Run only one instance of batch transcodig at once!\nAre you sure you want to start batch transcoding?');"><img src="<?php echo $cfg['img']; ?>small_transcode.png" alt="" class="small space"><?php echo html($value); ?></a></td>
	<td></td>
	<td align="right"><?php if ($cfg['encode_vbr'][$profile]) echo '&plusmn; '; echo formattedBirate($cfg['encode_bitrate'][$profile]); ?></td>
	<td></td>
	<td align="right"><?php echo formattedSize($cache['sumsize']); ?></td>
	<td></td>
	<td><a href="config.php?action=cacheDeleteProfile&amp;profile=<?php echo $profile; ?>&amp;sign=<?php echo $cfg['sign']; ?>" onClick="return confirm('Are you sure you want to delete all &quot;<?php echo html($value); ?>&quot; files from the cache?');" onMouseOver="return overlib('Delete');" onMouseOut="return nd();"><img src="<?php echo $cfg['img']; ?>small_delete.png" alt="" class="small space"></a></td>
	<td></td>
</tr>
<?php
	}
	$query = mysqli_query($db, 'SELECT SUM(filesize) AS sumsize
		FROM cache
		WHERE profile = -2');
	$cache = mysqli_fetch_assoc($query);
	$i = 0; ?>
<tr class="header">
	<td></td>
	<td>Maintain cache</td>
	<td></td>
	<td align="right">Bitrate</td>
	<td></td>
	<td align="right">Cache size</td>
	<td colspan="2"></td>
	<td></td>	
</tr>

<tr class="<?php echo ($i & 1) ? 'even' : 'odd'; $i++ ?> mouseover">
	<td></td>
	<td><a href="config.php?action=cacheDeleteProfile&amp;profile=-2&amp;sign=<?php echo $cfg['sign']; ?>" onClick="return confirm('Are you sure you want to delete all &quot;wave&quot; files from the cache?');"><img src="<?php echo $cfg['img']; ?>small_delete.png" alt="" class="small space">Delete wave</a></td>
	<td></td>
	<td align="right">1411.20 kbps</td>
	<td></td>
	<td align="right"><?php echo formattedSize($cache['sumsize']); ?></td>
	<td colspan="2"></td>
	<td></td>
</tr>
<?php
	$query = mysqli_query($db, 'SELECT SUM(filesize) AS sumsize
		FROM cache
		WHERE LOWER(SUBSTRING_INDEX(relative_file, ".", -1)) = "' . mysqli_real_escape_string($db, $cfg['download_album_extension']) . '"');
	$cache = mysqli_fetch_assoc($query) ?>
<tr class="<?php echo ($i & 1) ? 'even' : 'odd'; $i++ ?> mouseover">
	<td></td>
	<td><a href="config.php?action=cacheDeleteProfile&amp;profile=<?php echo rawurlencode($cfg['download_album_extension']); ?>&amp;sign=<?php echo $cfg['sign']; ?>" onClick="return confirm('Are you sure you want to delete all &quot;<?php echo html($cfg['download_album_extension']); ?>&quot; files from the cache?');"><img src="<?php echo $cfg['img']; ?>small_delete.png" alt="" class="small space">Delete <?php echo html($cfg['download_album_extension']); ?></a></td>
	<td></td>
	<td align="right">-</td>
	<td></td>
	<td align="right"><?php echo formattedSize($cache['sumsize']); ?></td>
	<td colspan="2"></td>
	<td></td>
</tr>
<tr class="<?php echo ($i & 1) ? 'even' : 'odd'; $i++ ?> mouseover">
	<td></td>
	<td colspan="7"><a href="download.php?action=batchValidateCache&amp;sign=<?php echo $cfg['sign']; ?>"><img src="<?php echo $cfg['img']; ?>small_update.png" alt="" class="small space">Validate cache</a></td>
	<td></td>
</tr>
</table>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Cache delete profile                                                   |
//  +------------------------------------------------------------------------+
function cacheDeleteProfile() {
	global $cfg, $db;
	authenticate('access_admin', false, true, true);
	
	$profile = get('profile');
	
	if (isset($cfg['encode_name'][$profile]) == false && $profile != -2 && $profile != $cfg['download_album_extension'])
		message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]profile');
	
	if ($profile == $cfg['download_album_extension'])
		$query = mysqli_query($db, 'SELECT relative_file
			FROM cache
			WHERE LOWER(SUBSTRING_INDEX(relative_file, ".", -1)) = "' . mysqli_real_escape_string($db, $cfg['download_album_extension']) . '"');
	else
		$query = mysqli_query($db, 'SELECT relative_file
			FROM cache
			WHERE profile = ' . (int) $profile);
	
	while ($cache = mysqli_fetch_assoc($query)) {
		$file = NJB_HOME_DIR . $cache['relative_file'];
		
		if (is_file($file) && @unlink($file) == false)
			message(__FILE__, __LINE__, 'error', '[b]Failed to delete file:[/b][br]' . $file);
		
		mysqli_query($db, 'DELETE FROM cache
			WHERE relative_file = "' . mysqli_real_escape_string($db, $cache['relative_file']) . '"');
	}
}




//  +------------------------------------------------------------------------+
//  | External storage                                                       |
//  +------------------------------------------------------------------------+
function externalStorage() {
	global $cfg, $db;
	authenticate('access_admin');
	
	$path = get('path');
	$dir = $cfg['external_storage'] . $path;
	
	if (is_dir($dir) == false || $dir != str_replace('\\', '/', realpath($dir)) . '/')
		message(__FILE__, __LINE__, 'error', '[b]Failed to open directory:[/b][br]' . $dir . '[br][url=config.php][img]small_back.png[/img]Back to config page[/url]');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'External storage';
	$nav['url'][]	= ($path == '') ? null : 'config.php?action=externalStorage';
	
	$i = 0;
	$url = '';
	$items = explode('/', $path, -1);
	$n = count($items);
	foreach ($items as $item) {
		$i++;
		$url .= $item . '/';
		$nav['name'][]	= $item;
		$nav['url'][]	= ($i >= $n) ? null : 'config.php?action=externalStorage&amp;path=' . rawurlencode($url);		
	}
		 
	require_once('include/header.inc.php');
?>
<form action="config.php" method="post" name="externalstorage" id="externalstorage">
	<input type="hidden" name="action" value="deleteExternalStorage">
	<input type="hidden" name="sign" value="<?php echo $cfg['sign']; ?>">
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>External storage</td>
	<td class="space"></td>
</tr>

<?php
	$i = 0;
	$entries = @scandir($dir) or message(__FILE__, __LINE__, 'error', '[b]Failed to open directory:[/b][br]' . $dir);
	foreach ($entries as $entry)
		if ($entry[0] != '.') {
?>
<tr class="<?php echo ($i & 1) ? 'even' : 'odd'; $i++ ?>">
	<td></td>
	<td><?php if (is_dir($dir . $entry . '/')) echo '<input type="checkbox" name="delete[]" value="' . html($path . $entry . '/') .'" class="space"><a href="config.php?action=externalStorage&amp;path=' . rawurlencode($path . $entry . '/') . '">' . html($entry) .'</a>';
	elseif (is_file($dir . $entry)) echo '<input type="checkbox" name="delete[]" value="' . html($path . $entry) .'" class="space">' . html($entry); ?></td>
	<td></td>
</tr>
<?php
		} ?>
<tr class="line"><td colspan="3"></td></tr>
<tr class="footer">
	<td></td>
	<td><img src="<?php echo $cfg['img']; ?>button_small_inverse.png" alt="" class="space" style="cursor: pointer;" onClick="inverseCheckbox(document.externalstorage);" onMouseOver="return overlib('Inverse selection');" onMouseOut="return nd();"><input type="image" src="<?php echo $cfg['img']; ?>button_small_delete.png" class="space" onClick="return confirm('Are you sure you want to delete the selected directory(s) / file(s)?');" onMouseOver="return overlib('Delete selected directory(s) / file(s)');" onMouseOut="return nd();"></td>
	<td></td>
</tr>
</table>
</form>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Delete external storage                                                |
//  +------------------------------------------------------------------------+
function deleteExternalStorage() {
	global $cfg, $db;
	authenticate('access_admin', false, true, true);
	
	$entries = post('delete');
	
	if (empty($entries) || is_array($entries) == false)
		message(__FILE__, __LINE__, 'warning', '[b]No directory or file selected[/b][br]Select at least one directory or file.[br][url=config.php?action=externalStorage][img]small_back.png[/img]Back to previous page[/url]');
		
	foreach ($entries as $entry) {
		$entry = $cfg['external_storage'] . $entry;
		
		if (is_dir($entry) && $entry != str_replace('\\', '/', realpath($entry)) . '/')
			message(__FILE__, __LINE__, 'error', '[b]Failed to open directory:[/b][br]' . $entry . '[br][url=config.php][img]small_back.png[/img]Back to config page[/url]');
		
		if (is_file($entry) && $entry != str_replace('\\', '/', realpath($entry)))
			message(__FILE__, __LINE__, 'error', '[b]Failed to open file:[/b][br]' . $entry . '[br][url=config.php][img]small_back.png[/img]Back to config page[/url]');
				
		if 		(is_dir($entry))	rrmdir($entry);
		else						@unlink($entry) or message(__FILE__, __LINE__, 'error', '[b]Failed to delete file:[/b][br]' . $entry);
	}
}
?>