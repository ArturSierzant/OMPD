<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015 Artur Sierzant                                 |
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
elseif	($action == 'editSettings')			editSettings();
elseif	($action == 'settings')			settings();
elseif	($action == 'tidal')			editTidal();
elseif	($action == 'hra')			editHRA();

else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');




//  +------------------------------------------------------------------------+
//  | Config                                                                 |
//  +------------------------------------------------------------------------+
function config() {
	global $cfg, $db, $update, $t;
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
	
	//precompile update.php to speed up update process
	if (function_exists("opcache_compile_file")){
		$isCompiled = opcache_compile_file (NJB_HOME_DIR . "update.php");
	}
	
	$i = 0;
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>Session profile</td>
	<td class="space"></td>
	<td>Comment</td>
	<td class="space"></td>
</tr>

<?php
	if ($cfg['access_playlist'] || $cfg['access_play'] || $cfg['access_add'] || $cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="config.php?action=playerProfile"><i class="fa fa-hdd-o fa-fw icon-small"></i>Player&nbsp;profile</a></td>
	<td></td>
	<td><?php echo html($cfg['player_name']); ?></td>
	<td></td>
</tr>
<?php
	}
	if ($cfg['access_stream'] || $cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="config.php?action=streamProfile"><i class="fa fa-rss fa-fw icon-small"></i>Stream&nbsp;profile</a></td>
	<td></td>
	<td><?php echo html($stream); ?></td>
	<td></td>
</tr>
<?php
	}
	if ($cfg['access_download'] || $cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="config.php?action=downloadProfile"><i class="fa fa-download fa-fw icon-small"></i>Download&nbsp;profile</a></td>
	<td></td>
	<td><?php echo html($download); ?></td>
	<td></td>
</tr>
<?php
	}
?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="config.php?action=skinProfile"><i class="fa fa-eye fa-fw icon-small"></i>Skin&nbsp;profile</a></td>
	<td></td>
	<td><?php echo html($cfg['skin']); ?></td>
	<td></td>
</tr>

<tr class="header">
	<td class="space"></td>
	<td>Configuration</td>
	<td class="space"></td>
	<td>Comment</td>
	<td class="space"></td>
</tr>

<?php
	$i = 0;
	if ($cfg['access_admin'] == false) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="users.php?action=accessRight"><i class="fa fa-user fa-fw icon-small"></i>Access&nbsp;right</a></td>
	<td></td>
	<td><?php echo html($cfg['username']); ?></td>
	<td></td>
</tr>
<?php
	}
	if ($cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="config.php?action=settings"><i class="fa fa-cog fa-fw icon-small"></i>Settings</a></td>
	<td></td>
	<td>Edit settings</td>
	<td></td>
</tr>
<?php
	}
		if ($cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="config.php?action=editSettings"><i class="fa fa-cogs fa-fw icon-small"></i>Advanced settings</a></td>
	<td></td>
	<td>Edit configuration file (<?php echo choose_config_file(); ?>)</td>
	<td></td>
</tr>
<?php
	}
	if ($cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="users.php"><i class="fa fa-users fa-fw icon-small"></i>Users</a></td>
	<td></td>
	<td>Users access rights</td>
	<td></td>
</tr>
<?php
	}
	if ($cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="users.php?action=online">
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
	<td class="nowrap"><a href="users.php?action=userStatistics&amp;period=overall"><i class="fa fa-bar-chart fa-fw icon-small"></i>User&nbsp;statistics</a></td>
	<td></td>
	<td>Show user statistics</td>
	<td></td>
</tr>
<?php
	}
	if ($cfg['access_statistics']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="statistics.php" onclick="showSpinner();"><i class="fa fa-line-chart fa-fw icon-small"></i>Media&nbsp;statistics</a></td>
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
	if ($update == 'cancel') {
		$result = mysqli_query($db, "SELECT * FROM update_progress");
		$row=mysqli_fetch_assoc($result);
		if ($row["update_status"] == 1 && mysqli_num_rows($result)>0) {
			mysqli_query($db, "UPDATE update_progress 
			SET update_status = 0,
			last_update = 'canceled',
			update_time = '" . date('Y-m-d, H:i') . "'");
			mysqli_query($db, "UPDATE album 
				SET updated = 1
				WHERE 1");
			mysqli_query($db, "UPDATE album_id 
				SET updated = 1
				WHERE 1");
			mysqli_query($db, "UPDATE bitmap 
				SET updated = 1
				WHERE 1");
			mysqli_query($db, "UPDATE track 
				SET updated = 1
				WHERE 1");
		}
	}
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
	<td class="update"><a href="config-update-select.php?action=updateSelect"><i class="fa fa-refresh fa-fw icon-small <?php echo $spin ?>"></i>Update</a></td>
	<td></td>
	<td>Update media <?php echo $update_info?></td>
	<td></td>
</tr>
<?php
	}
  
$query = mysqli_query($db, 'SELECT * FROM tidal_token LIMIT 1');
if ($query) $tidal_token = mysqli_fetch_assoc($query);  
if ($tidal_token['time'] == 0) {
  $update_info = '<span class="update_info"><br>Not logged in</span>';
}
elseif (time() > $tidal_token['expires_after']) {
  $update_info = '<span class="update_info"><br>Token expired.</span>';
}
elseif (time() < $tidal_token['expires_after']) {
  $tokenStatus = $t->verifyAccessToken();
  if (isset($tokenStatus['sessionId'])) {
    $exDate = date('Y-m-d H:i',$tidal_token['expires_after']);
    $update_info = '<span class="update_info"><br>Logged in, token valid until ' . $exDate . '</span>';
  }
}

if ($cfg['access_admin']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="config.php?action=tidal"><i class="ux ico-tidal icon-small fa-fw"></i>Tidal</a></td>
	<td></td>
	<td>Tidal login and status<?php echo $update_info?></td>
	<td></td>
</tr>

<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="config.php?action=hra"><span class="sign track_title_mini">H<span style="padding: 0px 1px;font-weight: bold;border: 0px solid;">R</span>A</span> HighResAudio</a></td>
	<td></td>
	<td>HighResAudio settings</td>
	<td></td>
</tr>

<?php
}
if ($cfg['access_admin'] && $cfg['php_info']) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td class="nowrap"><a href="phpinfo.php"><i class="fa fa-info-circle fa-fw icon-small"></i>PHP&nbsp;information</a></td>
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
	<td><a href="config.php?action=setPlayerProfile&amp;player_id=<?php echo $player['player_id']; ?>&amp;sign=<?php echo $cfg['sign']; ?>"><i class="fa fa-hdd-o fa-fw icon-small"></i><?php echo html($player['player_name']); ?></a>
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
		$player2 = mysqli_fetch_assoc($query);
		
		if ($player2 == false)
			message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]player_id not found in database');
		
		$txt_menu			= 'Edit profile';
		/* $cfg['player_name']	= $player['player_name'];
		$cfg['player_type']	= $player['player_type'];
		$cfg['player_host']	= $player['player_host'];
		$cfg['player_port']	= $player['player_port'];
		$cfg['player_pass']	= $player['player_pass'];
		$cfg['media_share']	= $player['media_share']; */
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
	<td><input type="text" name="name" value="<?php echo html($player2['player_name']); ?>" maxlength="255" class="edit"></td>
</tr>

<tr class="textspace"><td colspan="3"></td></tr>

<tr>
	<td>Player:</td>
	<td></td>
	<td><input type="radio" name="player_type" value="2" checked class="space" onClick="mpdDefault()"> Only! Music Player Daemon</td>
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
	<td><input type="text" name="player_host" value="<?php echo html($player2['player_host']); ?>" maxlength="255" class="edit"></td>
</tr>
<tr>
	<td>Player port:</td>
	<td></td>
	<td><input type="text" name="player_port" value="<?php echo ($player_id == 0 ?  '6600' : $player2['player_port']); ?>" maxlength="5" class="edit"></td>
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
<?php
	if (get('player_id') == '0') {
		echo('mpdDefault();');
	}
?>
<!--
function initialize() {
	document.config.name.focus();
<?php
	if ($cfg['player_type'] == NJB_MPD) {
		/* echo "\tdocument.config.player_pass.className = 'edit readonly';\n";
		echo "\tdocument.config.media_share.className = 'edit readonly';\n";
		echo "\tdocument.config.player_pass.disabled = true;\n";
		echo "\tdocument.config.media_share.disabled = true;\n"; */
	}
	if ($cfg['player_type'] == NJB_VLC){
		echo "\tdocument.config.player_pass.className = 'edit readonly';\n";
		echo "\tdocument.config.player_pass.disabled = true;\n";
	} ?>
}
	
	
	
function serverDefault() {
	document.config.player_host.value = '127.0.0.1';
	/* if (document.config.media_share.className != 'edit readonly')
		document.config.media_share.value = '<?php echo $cfg['media_dir']; ?>'; */
}
	
	
function clientDefault() {
	document.config.player_host.value = '<?php echo gethostbyaddr($_SERVER['REMOTE_ADDR']); ?>';
	/* if (document.config.media_share.className != 'edit readonly')
		document.config.media_share.value = '<?php echo (NJB_WINDOWS) ? '//' : '/'; echo (isset($_SERVER['SERVER_ADDR'])) ? $_SERVER['SERVER_ADDR'] : $_SERVER['SERVER_NAME']; ?>/<?php echo $temp[count($temp) - 2]; ?>/'; */
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
	document.config.name.value = 'MPD ID=<?php echo $player_id; ?>';
	document.config.player_port.value = '6600';
	document.config.player_pass.value = '';
	document.config.media_share.value = '';
	//document.config.player_pass.className = 'edit readonly';
	//document.config.media_share.className = 'edit readonly';
	//document.config.player_pass.disabled = true;
	//document.config.media_share.disabled = true;
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



//  +------------------------------------------------------------------------+
//  | Edit settings                                                          |
//  +------------------------------------------------------------------------+
function settings() {
  global $cfg, $db;
  authenticate('access_admin');

  // formattedNavigator
  $nav			= array();
  $nav['name'][]	= 'Configuration';
  $nav['url'][]	= 'config.php';
  $nav['name'][]	= 'Settings';

  require_once('include/header.inc.php');
  require_once('include/settings_gui.inc.php');
  require_once('include/footer.inc.php');
}



//  +------------------------------------------------------------------------+
//  | Edit advanced settings (config.inc.php or config.local.inc.php)        |
//  +------------------------------------------------------------------------+
function editSettings() {
	global $cfg, $db;
	authenticate('access_admin');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Advanced settings (' . choose_config_file() . ')';
	
	require_once('include/header.inc.php');
	require_once('include/settings.inc.php');
	require_once('include/footer.inc.php');
}


//  +------------------------------------------------------------------------+
//  | Tidal login                                                            |
//  +------------------------------------------------------------------------+
function editTidal() {
  global $cfg, $db, $t;
  authenticate('access_logged_in');

  // formattedNavigator
  $nav			= array();
  $nav['name'][]	= 'Configuration';
  $nav['url'][]	= 'config.php';
  $nav['name'][]	= 'Tidal';
  require_once('include/header.inc.php');
  $showLogin = true;
  $showRefresh = false;

  $query = mysqli_query($db, 'SELECT * FROM tidal_token LIMIT 1');
  if ($query) $tidal_token = mysqli_fetch_assoc($query);
  
  $queryT = mysqli_query($db, "SELECT * FROM config where name like '%tidal_%'");
  if ($queryT) {
    while ($tidal = mysqli_fetch_assoc($queryT)) {
      $a = $tidal['name'];
      $$a = $tidal['value'];
    }
  }
?>

<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
  <td class="space"></td>
  <td style="width:0.1%; white-space: nowrap;">Tidal settings</td>
  <td class="textspace"></td>
  <td></td>
  <td class="space"></td>
</tr>
<tr class="even">
<td></td>
<td colspan="4"><b>client_id</b> and <b>client_secret</b> are required to login to Tidal. You can get them from FireTV stick (or other limited input devices). Instructions can be found <a href="https://ompd.pl/getting-tidal-login-data" target="_blank">here</a>.<br/>
There is also possibility to get them from Internet - for example from <a href="https://github.com/yaronzz/Tidal-Media-Downloader" target="_blank">Tidal-Media-Downloader</a> project: <div class="buttons" style="display:inline;"><span id="getKeys">&nbsp;try now</span></div>
</td>
</tr>
<tr>
  <td colspan="5">
    <div id="tidalKeys" style="display:none;">
    <span id="albumsLoadingIndicator">
      <i class="fa fa-cog fa-spin icon-small"></i> Trying to get keys...
    </span>
    </div>
  </td>
</tr>
<tr class="even">
  <td></td>
	<td>client_id:</td>
  <td></td>
	<td><input type="text" id="tidal_client_id" value="<?php echo $tidal_client_id; ?>" maxlength="255" class="edit"></td>
  <td class="space"></td>
</tr>
<tr class="even">
  <td></td>
	<td>client_secret:</td>
  <td></td>
	<td><input type="text" id="tidal_client_secret" value="<?php echo $tidal_client_secret; ?>" maxlength="255" class="edit"></td>
  <td class="space"></td>
</tr>
<tr class="even">
	<td></td>
	<td>Audio quality:</td>
  <td></td>
	<td>
    <select id="tidal_audio_quality">
    <option value="LOW" <?php if ($tidal_audio_quality == 'LOW') echo 'selected'; ?>>LOW (96kbps, AAC)</option>
    <option value="HIGH" <?php if ($tidal_audio_quality == 'HIGH') echo 'selected'; ?>>HIGH (320kbps, AAC)</option>
    <option value="LOSSLESS" <?php if ($tidal_audio_quality == 'LOSSLESS') echo 'selected'; ?>>LOSSLESS</option>
    <option value="HI_RES" <?php if ($tidal_audio_quality == 'HI_RES') echo 'selected'; ?>>HI_RES</option>
    <option value="HI_RES_LOSSLESS" <?php if ($tidal_audio_quality == 'HI_RES_LOSSLESS') echo 'selected'; ?>>HI_RES_LOSSLESS</option>
    </select>
  </td>
  <td class="space"></td>
</tr>
<tr class="even">
	<td></td>
	<td>Fix Tidal freezes:<br>
  (requires <b>curl</b>)</td>
  <td></td>
	<td>
    <?php setChkBox($cfg['fix_tidal_freezes'],'fix_tidal_freezes');?>
  </td>
  <td class="space"></td>
</tr>
<tr>
<tr class="even">
  <td></td>
	<td>
  <div class="buttons">
  <span id="saveTidalSettings" onmouseover="return overlib('Save settings');" onmouseout="return nd();">&nbsp;<i class="fa fa-floppy-o fa-fw"></i> Save settings</span>
  </div>
  </td>
  <td></td>
	<td></td>
  <td class="space"></td>
</tr>
<script>
$("i[id^='cfg_']").click(function() {
  if ($(this).hasClass("fa-check-circle-o")) {
    $(this).removeClass("fa-check-circle-o").addClass("fa-circle-o");
    $(this).attr("data-val","false");
  }
  else if ($(this).hasClass("fa-circle-o")) {
    $(this).removeClass("fa-circle-o").addClass("fa-check-circle-o");
    $(this).attr("data-val","true");
  }
})

$('#saveTidalSettings').click(function() {
  $('#saveTidalSettings > i').removeClass('fa-floppy-o').addClass('fa-cog fa-spin');
  saveTidalSettings();
});

function saveTidalSettings(){
  var config = {};
  config['tidal_client_id'] =  $('#tidal_client_id').val();
  config['tidal_client_secret'] =  $('#tidal_client_secret').val();
  config['tidal_audio_quality'] =  $('#tidal_audio_quality').val();
  config['fix_tidal_freezes'] =  $('#fix_tidal_freezes').val();
  $("i[id^='cfg_']").each(function(){
      config[$(this).attr("data-name")] = $(this).attr("data-val");
    })
  $.ajax({
    url: "ajax-save-config.php",  
    type: "POST",  
    data: { settings : config },  
    dataType: "json",
    success: function(json) {
      if (json.return == 1) {
        $('#saveTidalSettings > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $('#saveTidalSettings > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-floppy-o');
        }, 2000);
        return;
      }
      else {
        $('#saveTidalSettings > i').removeClass('fa-cog fa-spin').addClass('fa-check-square');
        setTimeout(function() {
          $('#saveTidalSettings > i').removeClass('fa-check-square').addClass('fa-floppy-o');
        }, 2000);
        return;
      }
      //location.reload();
    },
    error: function() {
      $('#saveTidalSettings > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $('#saveTidalSettings > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-floppy-o');
        }, 2000);
    }
  });
};

$("#getKeys").on("click",function(){
  $("#tidalKeys").slideDown();
  $.ajax({
    url: "ajax-tidal-keys.php",  
    type: "POST",
    dataType: "html",
    success: function(data) {
      $("#tidalKeys").html(data);
    },
    error: function(data) {
      $("#tidalKeys").html("Error getting data");
    }
    
  })
});

</script>


<tr class="header">
  <td class="space"></td>
  <td colspan="4" style="width:0.1%; white-space: nowrap;">Tidal account login and status</td>
</tr>

<?php
if (!$query) {
?>
<tr class="even">
  <td></td>
  <td>Fatal error: </td>
  <td></td>
  <td>token table not found in DB</td>
  <td></td>
</tr>
<?php
}
elseif ($tidal_token['time'] == 0) {
  $showRefresh = false;
?>
<tr class="even">
  <td></td>
  <td>Login status:</td>
  <td></td>
  <td id="loginStatusInfo">not logged in</td>
  <td></td>
</tr>
<?php
} 
elseif (time() > $tidal_token['expires_after']) {
  $showLogin = false;
?>
<tr class="even">
  <td></td>
  <td>Token expired:</td>
  <td></td>
  <td>try to refresh token</td>
  <td></td>
</tr>
<?php
}
elseif (time() < $tidal_token['expires_after']) {
  $showLogin = false;
  $showRefresh = true;
  $tokenStatus = $t->verifyAccessToken();
  if (isset($tokenStatus['sessionId'])) {
    $exDate = date('Y-m-d H:i',$tidal_token['expires_after']);
    $tLeft = $tidal_token['expires_after'] - time();
    $m = floor(($tLeft%3600)/60);
    ($m == 1 ? $mt=" minute" : $mt=" minutes");
    $h = floor(($tLeft%86400)/3600);
    ($h == 1 ? $ht=" hour " : $ht=" hours ");
    $d = floor(($tLeft%2592000)/86400);
    ($d == 1 ? $dt=" day " : $dt=" days ");
    $tLeft = $d . $dt . $h . $ht . $m . $mt;
?>
<tr class="even">
  <td></td>
  <td>Login status:</td>
  <td></td>
  <td>logged in</td>
  <td></td>
</tr>
<tr class="even">
  <td></td>
  <td>Token status:</td>
  <td></td>
  <td>valid</td>
  <td></td>
</tr>
<tr class="even">
  <td></td>
  <td>Last token refresh: </td>
  <td></td>
  <td><?php echo date('Y-m-d H:i',$tidal_token['time']); ?></td>
  <td></td>
</tr>
<tr class="even">
  <td></td>
  <td>Token valid until: </td>
  <td></td>
  <td><?php echo $exDate; ?> (<?php echo $tLeft; ?> left)</td>
  <td></td>
</tr>
<?php
  }
  else {
?>
<tr class="even">
  <td></td>
  <td>Token is invalid:</td>
  <td></td>
  <td><?php echo $tokenStatus['response']; ?><br><br>
  Try to refresh token or logout and login to Tidal again.</td>
  <td></td>
</tr>
<?php
  }
}

if ($showLogin) {
?>
<tr class="even">
  <td></td>
  <td><div class="buttons">
  <span id="loginTidal" onmouseover="return overlib('Login to Tidal');" onmouseout="return nd();">&nbsp;<i class="fa fa-sign-in fa-fw"></i> Tidal login</span>
  </div></td>
  <td></td>
  <td id="loginInfo">Login to Tidal and start using Tidal in O!MPD.</td>
  <td></td>
</tr>
<?php
}
if ($showRefresh) {
?>
<tr class="even">
  <td></td>
  <td><div class="buttons">
  <span id="refreshToken" onmouseover="return overlib('Refresh token');" onmouseout="return nd();">&nbsp;<i class="fa fa-refresh fa-fw"></i> Refresh token</span></div>
  </div></td>
  <td></td>
  <td id="refreshInfo">No need to use this button when token is valid. When token expires it should be refreshed automatically. In case something is wrong with autorefreshing, you can try to do it manually. </td>
  <td></td>
</tr>

<tr class="even">
  <td></td>
  <td><div class="buttons">
  <span id="logoutTidal" onmouseover="return overlib('Logout from Tidal');" onmouseout="return nd();">&nbsp;<i class="fa fa-sign-out fa-fw"></i> Tidal logout</span>
  </div></td>
  <td></td>
  <td id="logoutInfo">Logout Tidal session and stop using Tidal in O!MPD.</td>
  <td></td>
</tr>
<?php
}
?>

</table>

<script>

var interval = 2000,
    timeLeft = 300,
    xhr,
    checkAuthStatus = function () {
     xhr = $.ajax({
            url: "ajax-tidal-auth.php",  
            type: "POST",  
            data: { action : "checkAuthStatus" },  
            dataType: "json",
            success: function(json) {
               if (json.auth_finished) {
                $('#loginTidal > i').removeClass('fa-cog fa-spin').addClass('fa-check-square');
                setTimeout(function() {
                  $('#loginTidal > i').removeClass('fa-check-square').addClass('fa-sign-in');
                }, 2000);
                //timeLeft = 0;
                location.reload();
                xhr.abort();
                return;
               }
               if (json.return == 1) {
                $('#loginInfo').html("Login failed. Error:<br><br>" + json.error + "<br><br>Error description:>br><br>" + json.error_description);
                $('#loginTidal > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
                setTimeout(function() {
                  $('#loginTidal > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-sign-in');
                }, 2000);
                timeLeft = 0;
                xhr.abort();
                return;
              }
              
              $('#loginStatusInfo').html(json.auth_status);
              timeLeft = timeLeft - (interval/1000);
              $('#verificationTimer').html(timeLeft);
            },
            complete: function() {
              if (timeLeft > 0) {
                setTimeout(checkAuthStatus, interval);
              }
              else {
                $('#loginStatusInfo').html("login failed");
                $('#loginInfo').html("Login timeout occured. Try to login again.");
                $('#loginTidal > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
                setTimeout(function() {
                  $('#loginTidal > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-sign-in');
                }, 2000);
              }
            },
            error: function() {
              $('#loginTidal > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
                setTimeout(function() {
                  $('#loginTidal > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-sign-in');
                }, 2000);
            }
          });
    };



$('#loginTidal').click(function() {
  $('#loginTidal > i').removeClass('fa-sign-in').addClass('fa-cog fa-spin');
  loginTidal();
});

$('#logoutTidal').click(function() {
  $('#logoutTidal > i').removeClass('fa-sign-out').addClass('fa-cog fa-spin');
  logoutTidal();
});

$('#refreshToken').click(function() {
  $('#refreshToken > i').removeClass('fa-refresh').addClass('fa-cog fa-spin');
  refreshToken();
});

function loginTidal(){
    $.ajax({
    url: "ajax-tidal-auth.php",  
    type: "POST",  
    data: { action : "getTidalDeviceCode" },  
    dataType: "json",
    success: function(json) {
       if (json.return == 1) {
        $('#loginInfo').html("Login failed. Error:<br>" + json.error);
        $('#loginTidal > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $('#loginTidal > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-sign-in');
        }, 2000);
        return;
      }
      $('#loginInfo').html('Go to <a href="https://' + json.verificationUriComplete + '" target="_blank">https://' + json.verificationUriComplete + '</a> within <span id="verificationTimer">' + json.expiresIn + '</span> seconds and login to Tidal to finish authorization.');
      interval = json.interval * 1000;
      timeLeft = json.expiresIn;
      checkAuthStatus();
      //$('#loginTidal > i').removeClass('fa-cog fa-spin').addClass('fa-sign-in');
    },
    error: function() {
      $('#loginTidal > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $('#loginTidal > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-sign-in');
        }, 2000);
    }
  });
};

function logoutTidal(){
    $.ajax({
    url: "ajax-tidal-auth.php",  
    type: "POST",  
    data: { action : "logoutTidal" },  
    dataType: "json",
    success: function(json) {
      if (json.return == 1) {
        $('#logoutInfo').html("Logout failed. Error:<br>" + json.error);
        $('#logoutTidal > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $('#logoutTidal > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-sign-out');
        }, 2000);
        return;
      }
      location.reload();
    },
    error: function() {
      $('#logoutTidal > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $('#logoutTidal > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-sign-out');
        }, 2000);
    }
  });
};

function refreshToken(){
    $.ajax({
    url: "ajax-tidal-auth.php",  
    type: "POST",  
    data: { action : "refreshAccessToken" },  
    dataType: "json",
    success: function(json) {
      if (json.return == 1) {
        $('#refreshInfo').html("Token refresh failed. Error:<br>" + json.error);
        $('#refreshToken > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $('#refreshToken > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-refresh');
        }, 2000);
        return;
      }
      location.reload();
    },
    error: function() {
      $('#refreshToken > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $('#refreshToken > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-refresh');
        }, 2000);
    }
  });
};

</script>
<?php
  require_once('include/footer.inc.php');
}


//  +------------------------------------------------------------------------+
//  | HRA login                                                              |
//  +------------------------------------------------------------------------+
function editHRA() {
  global $cfg, $db;
  authenticate('access_logged_in');

  // formattedNavigator
  $nav			= array();
  $nav['name'][]	= 'Configuration';
  $nav['url'][]	= 'config.php';
  $nav['name'][]	= 'HighResAudio';
  require_once('include/header.inc.php');

  $query = mysqli_query($db, "SELECT * FROM config where name like 'hra_%'");
  if ($query) {
    while ($hra = mysqli_fetch_assoc($query)) {
      $a = $hra['name'];
      $$a = $hra['value'];
    }
  }
?>
<h1>HighResAudio account and settings</h1>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="textspace"><td colspan="4"></td></tr>
<tr>
  <td class="space"></td>
	<td style="width:0.1%; white-space: nowrap;">Username:</td>
	<td class="textspace"></td>
	<td><input type="text" id="hra_username" value="<?php echo $hra_username; ?>" maxlength="255" class="edit"></td>
  <td class="space"></td>
</tr>
<tr class="textspace"><td colspan="5"></td></tr>
<tr>
  <td></td>
	<td>Password:</td>
	<td></td>
	<td><input type="text" id="hra_password" value="<?php echo $hra_password; ?>" maxlength="255" class="edit"></td>
  <td class="space"></td>
</tr>
<tr class="textspace"><td colspan="5"></td></tr>
<tr>
	<td></td>
	<td>Language:</td>
	<td></td>
	<td>
    <select id="hra_lang">
    <option value="en" <?php if ($hra_lang == 'en') echo 'selected'; ?>>English</option>
    <option value="de" <?php if ($hra_lang == 'de') echo 'selected'; ?>>Deutsch</option>
    </select>
  </td>
  <td class="space"></td>
</tr>
<tr>
<tr class="textspace"><td colspan="5"></td></tr>
<tr>
  <td></td>
	<td>
  <div class="buttons">
  <span id="saveHRAsettings" onmouseover="return overlib('Save settings');" onmouseout="return nd();">&nbsp;<i class="fa fa-floppy-o fa-fw"></i> Save settings</span>
  </div>
  </td>
	<td></td>
	<td></td>
  <td class="space"></td>
</tr>
</table>
<script>
$('#saveHRAsettings').click(function() {
  $('#saveHRAsettings > i').removeClass('fa-floppy-o').addClass('fa-cog fa-spin');
  saveHRAsettings();
});

function saveHRAsettings(){
  var config = {};
  config['hra_username'] =  $('#hra_username').val();
  config['hra_password'] =  $('#hra_password').val();
  config['hra_lang'] =  $('#hra_lang').val();
  $.ajax({
    url: "ajax-save-config.php",  
    type: "POST",  
    data: { settings : config },  
    dataType: "json",
    success: function(json) {
      if (json.return == 1) {
        $('#saveHRAsettings > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $('#saveHRAsettings > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-floppy-o');
        }, 2000);
        return;
      }
      else {
        $('#saveHRAsettings > i').removeClass('fa-cog fa-spin').addClass('fa-check-square');
        setTimeout(function() {
          $('#saveHRAsettings > i').removeClass('fa-check-square').addClass('fa-floppy-o');
        }, 2000);
        return;
      }
      //location.reload();
    },
    error: function() {
      $('#saveHRAsettings > i').removeClass('fa-cog fa-spin').addClass('fa-exclamation-circle icon-nok');
        setTimeout(function() {
          $('#saveHRAsettings > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-floppy-o');
        }, 2000);
    }
  });
};
</script>
<?php
require_once('include/footer.inc.php');
}

?>