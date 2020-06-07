<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2020 Artur Sierzant                            |
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
//  | users.php                                                              |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
$cfg['menu'] = 'config';

$action		= getpost('action');
$user_id	= getpost('user_id');

if		($action == '')						home();

elseif	($action == 'editUser')				editUser($user_id);
elseif	($action == 'updateUser')			{updateUser($user_id);	home();}
elseif	($action == 'deleteUser')			{deleteUser($user_id);	home();}

elseif	($action == 'online')				online();
elseif	($action == 'resetSessions')		{resetSessions();		online();}

elseif	($action == 'accessRight')			accessRight();
elseif	($action == 'userStatistics')		userStatistics();
elseif	($action == 'resetUserStatistics')	{resetUserStatistics();	userStatistics();}

else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');
exit();




//  +------------------------------------------------------------------------+
//  | Home                                                                   |
//  +------------------------------------------------------------------------+
function home() {
	global $cfg, $db;
	authenticate('access_admin');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Users';
	require_once('include/header.inc.php');
?>
<div class="buttons">
	<span>
	<?php if ($cfg['access_admin']) echo '<a href="users.php?action=editUser&amp;user_id=0" onMouseOver="return overlib(\'Add new user\');" onMouseOut="return nd();">Add new user</a>'; ?>
	</span>
</div>
<div id="usersTab" class="noSwipe">
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td class="matrix vert"><div><span>Username</span></div></td>
	<td class="textspace"></td>
	<td class="matrix vert"><div><span>Media</span></div></td>
	<td class="matrix vert"><div><span>Popular</span></div></td>
	<td class="matrix vert"><div><span>Favorite</span></div></td>
	<td class="matrix vert"><div><span>Playlist</span></div></td>
	<td class="matrix vert"><div><span>Play</span></div></td>
	<td class="matrix vert"><div><span>Add</span></div></td>
	<td class="matrix vert"><div><span>Stream</span></div></td>
	<td class="matrix vert"><div><span>Download</span></div></td>
	<!--
	<td class="matrix vert"><div><span>Cover</span></div></td>
	<td class="matrix vert"><div><span>Record</span></div></td>
	-->
	<td class="matrix vert"><div><span>Statistics</span></div></td>
	<td class="matrix vert"><div><span>Admin</span></div></td>
	<td class="space"></td>
	<td class="matrix vert"><div><span>Delete</span></div></td>
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="18"></td></tr>
<?php
	$i=0;
	$check = '<i class="fa fa-check fa-fw icon-selected"></i>';
	$uncheck = '<i class="fa fa-times fa-fw icon-unava"></i>';
	$query = mysqli_query($db,'SELECT username, access_media, access_popular, access_cover, access_stream, access_playlist, access_play, access_add, access_record, access_download, access_favorite, access_statistics, access_admin, user_id FROM user ORDER BY username');
	while ($user = mysqli_fetch_assoc($query)) { ?>
<tr class="<?php if ($cfg['username'] == $user['username']) echo 'select'; else echo ($i & 1) ? 'even mouseover' : 'odd mouseover'; $i++ ?>">
	<td></td>
	<td style="white-space: nowrap"><a href="users.php?action=editUser&amp;user_id=<?php echo $user['user_id']; ?>"><i class="fa fa-user fa-fw icon-small"></i>&nbsp;<?php echo html($user['username']); ?></a></td>
	<td></td>
	<td align="center" <?php echo onmouseoverAccessInfo('media'); ?>><?php echo $user['access_media'] ? $check : $uncheck; ?></td>
	<td align="center" <?php echo onmouseoverAccessInfo('popular'); ?>><?php echo $user['access_popular'] ? $check : $uncheck; ?></td>
	<td align="center" <?php echo onmouseoverAccessInfo('favorite'); ?>><?php echo $user['access_favorite'] ? $check : $uncheck; ?></td>
	<td align="center" <?php echo onmouseoverAccessInfo('playlist'); ?>><?php echo $user['access_playlist'] ? $check : $uncheck; ?></td>
	<td align="center" <?php echo onmouseoverAccessInfo('play'); ?>><?php echo $user['access_play'] ? $check : $uncheck; ?></td>
	<td align="center" <?php echo onmouseoverAccessInfo('add'); ?>><?php echo $user['access_add'] ? $check : $uncheck; ?></td>
	<td align="center" <?php echo onmouseoverAccessInfo('stream'); ?>><?php echo $user['access_stream'] ? $check : $uncheck; ?></td>
	<td align="center" <?php echo onmouseoverAccessInfo('download'); ?>><?php echo $user['access_download'] ? $check : $uncheck; ?></td>
	<!--
	<td align="center" <?php echo onmouseoverAccessInfo('cover'); ?>><?php echo $user['access_cover'] ? $check : $uncheck; ?></td>
	<td align="center" <?php echo onmouseoverAccessInfo('record'); ?>><?php echo $user['access_record'] ? $check : $uncheck; ?></td>
	-->
	<td align="center" <?php echo onmouseoverAccessInfo('statistics'); ?>><?php echo $user['access_statistics'] ? $check : $uncheck; ?></td>
	<td align="center" <?php echo onmouseoverAccessInfo('admin'); ?>><?php echo $user['access_admin'] ? $check : $uncheck; ?></td>
	<td></td>
	<td><a href="users.php?action=deleteUser&amp;user_id=<?php echo $user['user_id']; ?>&amp;sign=<?php echo $cfg['sign']; ?>" onClick="return confirm('Are you sure you want to delete user: <?php echo addslashes(html($user['username'])); ?>?');" onMouseOver="return overlib('Delete');" onMouseOut="return nd();"><i class="fa fa-times-circle fa-fw icon-small"></i></a></td>
	<td></td>
</tr>
<?php
	}
	echo '</table>' . "\n";
	echo '</div>' . "\n";
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Edit user                                                              |
//  +------------------------------------------------------------------------+
function editUser($user_id) {
	global $cfg, $db;
	authenticate('access_admin');
	
	if ($user_id == '0') {
		// Add user configuraton
		$user['username']			= 'user_' . sprintf('%04x', mt_rand(0, 0xffff));
		$user['access_media']		= true;
		$user['access_popular']		= false;
		$user['access_favorite']	= false;
		$user['access_cover']		= false;
		$user['access_stream']		= false;
		$user['access_download']	= false;
		$user['access_playlist']	= false;
		$user['access_play']		= false;
		$user['access_add']			= false;
		$user['access_record']		= false;
		$user['access_statistics']	= false;
		$user['access_admin']		= false;
		$txt_menu					= 'Add user';
		$txt_password				= 'Password:';
	}
	else {
		// Edit user configutaion
		$query = mysqli_query($db,'SELECT
			username,
			access_media,
			access_popular,
			access_favorite,
			access_cover,
			access_stream,
			access_download,
			access_playlist,
			access_play,
			access_add,
			access_record,
			access_statistics,
			access_admin
			FROM user
			WHERE user_id = ' . (int) $user_id);
		$user = mysqli_fetch_assoc($query);
		if ($user == false)
			message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]user_id not found in database');
		
		$txt_menu		= 'Edit user';
		$txt_password	= 'New password:';
	}
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Users';
	$nav['url'][]	= 'users.php';
	$nav['name'][]	= $txt_menu;
	require_once('include/header.inc.php');
	
	// Store seed temporarily in the session database
	// After acepting a new password copy the seed to the user database
	$session_seed = randomKey();
	mysqli_query($db,'UPDATE session
		SET seed	= "' . mysqli_real_escape_string($db,$session_seed) . '"
		WHERE sid	= BINARY "' . mysqli_real_escape_string($db,$cfg['sid']) . '"');
?>
<script type="text/javascript">
<!--
if (hmacsha1('key', 'The quick brown fox jumps over the lazy dog') != 'de7c9b85b8b78aa6bc8a7a36f70a90701c9db4d9') {
	document.write('<table cellspacing="10" cellpadding="0" class="error">');
	document.write('<tr>');
	document.write('	<td valign="top"><img src="<?php echo $cfg['img']; ?>medium_message_error.png" alt=""><\/td>');
	document.write('	<td valign="top"><strong>JavaScript error<\/strong><br>Unexpected SHA1 checksum result.<\/td>');
	document.write('<\/tr>');
	document.write('<\/table>');
}
else if (typeof XMLHttpRequest == 'undefined') {
	document.write('<table cellspacing="10" cellpadding="0" class="error">');
	document.write('<tr>');
	document.write('	<td valign="top"><img src="<?php  echo $cfg['img']; ?>medium_message_error.png" alt=""><\/td>');
	document.write('	<td valign="top"><strong>Native XMLHttpRequest support is required<\/strong><br>');
	document.write('	Enable XMLHttpRequest or get a modern web browser.<\/td>');
	document.write('<\/tr>');
	document.write('<\/table>');
}
else {
	document.write('<form id="editUser" action="users.php" method="post" onSubmit="return hashPassword(this);" autocomplete="off">');
	document.write('	<input type="hidden" name="action" value="updateUser">');
	document.write('	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">');
	document.write('	<input type="hidden" name="sign" value="<?php echo $cfg['sign']; ?>">');
	document.write('<table cellspacing="0" cellpadding="0" class="border">');
	document.write('<tr class="header">');
	document.write('	<td ><\/td>');
	document.write('	<td>Access<\/td>');
	document.write('	<td ><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="line"><td colspan="4"><\/td><\/tr>');
	document.write('<tr class="lh3" <?php echo addslashes(onmouseoverAccessInfo('media')); ?>>');
	document.write('	<td><\/td>');
	document.write('	<td>&nbsp;&nbsp;<input type="checkbox" name="access_media" value="1" <?php if ($user['access_media']) echo ' checked'; ?>>&nbsp;Media<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="lh3" <?php echo addslashes(onmouseoverAccessInfo('popular')); ?>>');
	document.write('	<td><\/td>');
	document.write('	<td>&nbsp;&nbsp;<input type="checkbox" name="access_popular" value="1" <?php if ($user['access_popular']) echo ' checked'; ?>>&nbsp;Popular<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="lh3" <?php echo addslashes(onmouseoverAccessInfo('favorite')); ?>>');
	document.write('	<td><\/td>');
	document.write('	<td>&nbsp;&nbsp;<input type="checkbox" name="access_favorite" value="1" <?php if ($user['access_favorite']) echo ' checked'; ?>>&nbsp;Favorite<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="lh3" <?php echo addslashes(onmouseoverAccessInfo('playlist')); ?>>');
	document.write('	<td><\/td>');
	document.write('	<td>&nbsp;&nbsp;<input type="checkbox" name="access_playlist" value="1" <?php if ($user['access_playlist']) echo ' checked'; ?>>&nbsp;Playlist<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="lh3" <?php echo addslashes(onmouseoverAccessInfo('play')); ?>>');
	document.write('	<td><\/td>');
	document.write('	<td>&nbsp;&nbsp;<input type="checkbox" name="access_play" value="1" <?php if ($user['access_play']) echo ' checked'; ?>>&nbsp;Play<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="lh3" <?php echo addslashes(onmouseoverAccessInfo('add')); ?>>');
	document.write('	<td><\/td>');
	document.write('	<td>&nbsp;&nbsp;<input type="checkbox" name="access_add" value="1" <?php if ($user['access_add']) echo ' checked'; ?>>&nbsp;Add<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="lh3" <?php echo addslashes(onmouseoverAccessInfo('stream')); ?>>');
	document.write('	<td><\/td>');
	document.write('	<td>&nbsp;&nbsp;<input type="checkbox" name="access_stream" value="1" <?php if ($user['access_stream']) echo ' checked'; ?>>&nbsp;Stream<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="lh3" <?php echo addslashes(onmouseoverAccessInfo('download')); ?>>');
	document.write('	<td><\/td>');
	document.write('	<td>&nbsp;&nbsp;<input type="checkbox" name="access_download" value="1" <?php if ($user['access_download']) echo ' checked'; ?>>&nbsp;Download<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	/* document.write('<tr class="lh3" <?php echo addslashes(onmouseoverAccessInfo('cover')); ?>>');
	document.write('	<td><\/td>');
	document.write('	<td>&nbsp;&nbsp;<input type="checkbox" name="access_cover" value="1" <?php if ($user['access_cover']) echo ' checked'; ?>>Cover<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="lh3" <?php echo addslashes(onmouseoverAccessInfo('record')); ?>>');
	document.write('	<td><\/td>');
	document.write('	<td>&nbsp;&nbsp;<input type="checkbox" name="access_record" value="1" <?php if ($user['access_record']) echo ' checked'; ?>>Record<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>'); */
	document.write('<tr class="lh3" <?php echo addslashes(onmouseoverAccessInfo('statistics')); ?>>');
	document.write('	<td><\/td>');
	document.write('	<td>&nbsp;&nbsp;<input type="checkbox" name="access_statistics" value="1" <?php if ($user['access_statistics']) echo ' checked'; ?>>&nbsp;Statistics<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="lh3" <?php echo addslashes(onmouseoverAccessInfo('admin')); ?>>');
	document.write('	<td><\/td>');
	document.write('	<td>&nbsp;&nbsp;<input type="checkbox" name="access_admin" value="1" <?php if ($user['access_admin']) echo ' checked'; ?>>&nbsp;Admin<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="line"><td colspan="3"><\/td><\/tr>');
	document.write('<tr class="footer">');
	document.write('	<td><\/td>');
	document.write('	<td>Username:<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="footer">');
	document.write('	<td><\/td>');
	document.write('	<td><input type="text" name="new_username" value="<?php echo addslashes(html($user['username'])); ?>" maxlength="255" <?php echo ($user['username'] == $cfg['anonymous_user']) ? 'readonly class="login readonly" onfocus="this.blur();"' : 'class="login"'; ?>><\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="footer">');
	document.write('	<td><\/td>');
	document.write('	<td><?php echo $txt_password; ?><\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="footer">');
	document.write('	<td><\/td>');
	document.write('	<td><input type="password" name="new_password" <?php echo ($user['username'] == $cfg['anonymous_user']) ? 'readonly class="login readonly" onfocus="this.blur();"' : 'class="login"'; ?>><\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="footer">');
	document.write('	<td><\/td>');
	document.write('	<td>Confirm password:<\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="footer">');
	document.write('	<td><\/td>');
	document.write('	<td><input type="password" name="chk_password" <?php echo ($user['username'] == $cfg['anonymous_user']) ? 'readonly class="login readonly" onfocus="this.blur();"' : 'class="login"'; ?>><\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="footer"><td colspan="3"><\/td><\/tr>');
	document.write('<\/table>');
	document.write('<br>');
	document.write('<div class="buttons"><span><a href="#" onclick="$(\'#editUser\').submit();">Save</a><\/span>');
	document.write('<span><a href="users.php">Cancel<\/a></span>');
	document.write('<\/div><\/form>');
	
	
	function hashPassword(thisform)	{
		thisform.new_username.className = 'login readonly';
		thisform.new_password.className = 'login readonly';
		thisform.chk_password.className = 'login readonly';
		thisform.new_password.value = hmacsha1(hmacsha1(thisform.new_password.value, '<?php echo $session_seed; ?>'), '<?php echo $session_seed; ?>');
		thisform.chk_password.value = hmacsha1(hmacsha1(thisform.chk_password.value, '<?php echo $session_seed; ?>'), '<?php echo $session_seed; ?>');
		return true;
	}
}
//-->
</script>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Update user                                                            |
//  +------------------------------------------------------------------------+
function updateUser($user_id) {
	global $cfg, $db;
	authenticate('access_admin', false, true, true);
	
	$new_username		= post('new_username');
	$new_password		= post('new_password');
	$chk_password		= post('chk_password');
	$access_media		= post('access_media')		? 1 : 0;
	$access_popular		= post('access_popular')	? 1 : 0;
	$access_favorite	= post('access_favorite')	? 1 : 0;
	$access_playlist	= post('access_playlist')	? 1 : 0;
	$access_play		= post('access_play')		? 1 : 0;
	$access_add			= post('access_add')		? 1 : 0;
	$access_stream		= post('access_stream')		? 1 : 0;
	$access_download	= post('access_download')	? 1 : 0;
	$access_cover		= post('access_cover')		? 1 : 0;
	$access_record		= post('access_record')		? 1 : 0;
	$access_statistics	= post('access_statistics')	? 1 : 0;
	$access_admin		= post('access_admin')		? 1 : 0;
	
	
	$query = mysqli_query($db,'SELECT user_id FROM user WHERE user_id = ' . (int) $user_id);
	if (mysqli_fetch_row($query) == false && $user_id != '0')
		message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]user_id not found in database');
	
	$query = mysqli_query($db,'SELECT user_id FROM user WHERE user_id != ' . (int) $user_id . ' AND username = "' . mysqli_real_escape_string($db,$new_username) . '"');
	if (mysqli_fetch_row($query))
		message(__FILE__, __LINE__, 'warning', '[b]Username already exist[/b][br]Choose another username[br][url=users.php?action=editUser&user_id='. rawurlencode($user_id) . '][img]small_back.png[/img]Back to previous page[/url]');
	
	
	if ($new_password == hmacsha1(hmacsha1('', $cfg['session_seed']), $cfg['session_seed']))	$password_set = false;
	else																						$password_set = true;
	
	if (preg_match('#^[0-9a-f]{40}$#', $new_password) == false)							message(__FILE__, __LINE__, 'error', '[b]Password error[/b][br]This is not a valid hash');
	if ($new_password != $chk_password) 												message(__FILE__, __LINE__, 'warning', '[b]Passwords are not identical[/b][br][url=users.php?action=editUser&user_id='. rawurlencode($user_id) .'][img]small_back.png[/img]Back to previous page[/url]');
	if (!$password_set && $user_id == '0' && $new_username != $cfg['anonymous_user'])	message(__FILE__, __LINE__, 'warning', '[b]Password must be set for a new user[/b][br][url=users.php?action=editUser&user_id=0][img]small_back.png[/img]Back to previous page[/url]');
	if ($new_username == '') 															message(__FILE__, __LINE__, 'warning', '[b]Username must be set[/b][br][url=users.php?action=editUser&user_id='. rawurlencode($user_id) .'][img]small_back.png[/img]Back to previous page[/url]');
	if ($access_admin == false) {
		if (checkAdminAcount($user_id) == false)
				message(__FILE__, __LINE__, 'warning', '[b]There must be at least one user with admin privilege[/b][br][url=users.php?action=editUser&user_id='. rawurlencode($user_id) .'][img]small_back.png[/img]Back to previous page[/url]');
	}
	
	if (($password_set || $user_id == '0') && $new_username == $cfg['anonymous_user']) {
		$new_password = hmacsha1(hmacsha1($cfg['anonymous_user'], $cfg['session_seed']), $cfg['session_seed']);
		$password_set = true;
	}
	
	if ($user_id == '0') {
		mysqli_query($db,'INSERT INTO user (username) VALUES ("")');
		$user_id = mysqli_insert_id($db);
	}
	
	if ($password_set) {
		mysqli_query($db,'UPDATE user SET
			username			= "' . mysqli_real_escape_string($db,$new_username) . '",
			password			= "' . mysqli_real_escape_string($db,$new_password) . '",
			seed				= "' . mysqli_real_escape_string($db,$cfg['session_seed']) . '",
			version				= 1,
			access_media		= ' . (int) $access_media . ',
			access_popular		= ' . (int) $access_popular . ',
			access_favorite 	= ' . (int) $access_favorite . ',
			access_playlist		= ' . (int) $access_playlist . ',
			access_play			= ' . (int) $access_play . ',
			access_add			= ' . (int) $access_add . ',
			access_stream		= ' . (int) $access_stream . ',
			access_download 	= ' . (int) $access_download . ',
			access_cover		= ' . (int) $access_cover . ',
			access_record		= ' . (int) $access_record . ',
			access_statistics	= ' . (int) $access_statistics . ',
			access_admin		= ' . (int) $access_admin . '
			WHERE user_id		= ' . (int) $user_id);
		
		mysqli_query($db,'UPDATE session
			SET logged_in	= 0
			WHERE user_id	= ' . (int) $user_id);
	}
	else {
		mysqli_query($db,'UPDATE user SET
			username			= "' . mysqli_real_escape_string($db,$new_username) . '",
			access_media		= ' . (int) $access_media . ',
			access_popular		= ' . (int) $access_popular . ',
			access_favorite		= ' . (int) $access_favorite . ',
			access_playlist		= ' . (int) $access_playlist . ',
			access_play			= ' . (int) $access_play . ',
			access_add			= ' . (int) $access_add . ',
			access_stream		= ' . (int) $access_stream . ',
			access_download 	= ' . (int) $access_download . ',
			access_cover		= ' . (int) $access_cover . ',
			access_record		= ' . (int) $access_record . ',
			access_statistics	= ' . (int) $access_statistics . ',
			access_admin		= ' . (int) $access_admin . '
			WHERE user_id		= ' . (int) $user_id);
	}
}




//  +------------------------------------------------------------------------+
//  | Delete user                                                            |
//  +------------------------------------------------------------------------+
function deleteUser($user_id) {
	global $db;
	authenticate('access_admin', false, true, true);
	if (checkAdminAcount($user_id) == false)
		message(__FILE__, __LINE__, 'warning', '[b]There must be at least one user with admin privilege[/b][br][url=users.php][img]small_back.png[/img]Back to previous page[/url]');
	mysqli_query($db,'DELETE FROM user WHERE user_id = ' . (int) $user_id);
	mysqli_query($db,'DELETE FROM session WHERE user_id = ' . (int) $user_id);
}




//  +------------------------------------------------------------------------+
//  | Check admin acount                                                     |
//  +------------------------------------------------------------------------+
function checkAdminAcount($user_id) {
	global $db;
	$query = mysqli_query($db,'SELECT user_id 
		FROM user 
		WHERE user_id != ' . (int) $user_id . '
		AND access_admin');
	$user = mysqli_fetch_assoc($query);
	if ($user['user_id'] == '') return false;
	else						return true;
}




//  +------------------------------------------------------------------------+
//  | Online                                                                 |
//  +------------------------------------------------------------------------+
function online() {
	global $cfg, $db;
	authenticate('access_admin');
		
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Online';
	require_once('include/header.inc.php');	
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>User</td>
	<td class="textspace"></td>
	<td align="right">Visit</td>
	<td class="textspace"></td>
	<td align="right">Hit</td>
	<td class="textspace"></td>
	<td>IP</td>
	<td align="right">Idle</td>
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="13"></td></tr>
<?php
	$i = 0;
	$cfg['ip_tools'] = str_replace('&', '&amp;', $cfg['ip_tools']);
	$query = mysqli_query($db,'SELECT logged_in, hit_counter, visit_counter, idle_time, ip, user_agent,
		user.username,
		user.user_id
		FROM session, user
		WHERE idle_time > ' . (int) (time() - 86400) . '
		AND hit_counter > 0
		AND session.user_id = user.user_id
		ORDER BY idle_time DESC');
	while ($session = mysqli_fetch_assoc($query)) {
		$country_name = '';
		// Get local network
		$ip = array();
		$ip['lower'][]	= '192.168.0.0';
		$ip['upper'][]	= '192.168.255.255';
		$ip['name'][]	= 'Local area network';
		
		$ip['lower'][]	= '172.16.0.0';
		$ip['upper'][]	= '172.31.255.255';
		$ip['name'][]	= 'Local area network';
				
		$ip['lower'][]	= '10.0.0.0';
		$ip['upper'][]	= '10.255.255.255';
		$ip['name'][]	= 'Local area network';
				
		$ip['lower'][]	= '169.254.0.0';
		$ip['upper'][]	= '169.254.255.255';
		$ip['name'][]	= 'Automatic private IP range';
		
		$ip['lower'][]	= '127.0.0.0';
		$ip['upper'][]	= '127.255.255.255';
		$ip['name'][]	= 'Loopback';
		
		$session_ip = ip2long($session['ip']);
		foreach ($ip['name'] as $key => $value) {
			if ($session_ip >= ip2long($ip['lower'][$key]) && $session_ip <= ip2long($ip['upper'][$key])) {
				$country_name = $ip['name'][$key];
				$flag = $cfg['img'] . 'small_uncheck.png';
				break;
			}
		}
		
		if (in_array($session['ip'], array('::1', '0:0:0:0:0:0:0:1'))) {
			$country_name = 'Loopback';
			$flag = $cfg['img'] . 'small_uncheck.png';
		}
			
		if ($country_name == '') {
			// Get country code
			$reverse_ip = explode('.', $session['ip']);
			$reverse_ip = array_reverse($reverse_ip);
			$reverse_ip = implode('.', $reverse_ip);
			$lookup = $reverse_ip . '.zz.countries.nerd.dk';
			$code = @gethostbyname($lookup);
			if ($code != $lookup) {
				$code = explode('.', $code);
				$code = 256 * (int) $code[2] + (int) $code[3];
				$query3 = mysqli_query($db,'SELECT iso, name FROM country WHERE code = ' . (int) $code);
				$country = mysqli_fetch_assoc($query3);
				if (is_file('skin/' . $cfg['skin'] . '/flag/' . $country['iso'] . '.png')) {
					$country_name = $country['name'];
					$flag = 'skin/' . rawurlencode($cfg['skin']) . '/flag/' . $country['iso'] . '.png';
				}
			}
		}
		
		if ($country_name == '') {
			$country_name = 'Unresolved / Unknown';
			$flag = $cfg['img'] . 'small_uncheck.png';
		}
?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>
	<td><a href="users.php?action=editUser&amp;user_id=<?php echo $session['user_id'];?>">
	<i class="fa fa-sign-<?php echo ($session['logged_in']) ? 'in' : 'out'; ?> fa-fw icon-small"></i>	
	<?php echo html($session['username']); ?></a>
	<div><?php echo addslashes(html($session['user_agent'])); ?></div>
	</td>	
	<td></td>
	<td align="right"><?php echo $session['visit_counter']; ?></td>	
	<td></td>
	<td align="right"><?php echo $session['hit_counter']; ?></td>
	<td></td>
	<td><a href="<?php echo str_replace('%ip', rawurlencode($session['ip']), $cfg['ip_tools']); ?>" target="_blank"><?php echo html($session['ip']); ?></a></td>
	<td align="right"><?php echo formattedTime((time() - $session['idle_time']) * 1000); ?></td>
	<td></td>
</tr>
<?php
	}
	$query = mysqli_query($db,'SELECT idle_time AS start_time FROM session WHERE logged_in ORDER BY idle_time ASC LIMIT 1');
	$session = mysqli_fetch_assoc($query);
?>
<tr class="line"><td colspan="13"></td></tr>
<tr class="footer">
	<td class="space"></td>
	<td colspan="11">Visit and hit count since: <?php echo date($cfg['date_format'], $session['start_time']); ?></td>
	<td class="space"></td>
</tr>
</table>
<br>
<div class="buttons">
<span><a href="users.php?action=online">Refresh</a></span>
<span><a onclick="return confirm('Are you sure you want to reset all sessions?')" href="users.php?action=resetSessions&amp;sign=<?php echo $cfg['sign']; ?>">Reset</a></span>
</div>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Reset sessions                                                         |
//  +------------------------------------------------------------------------+
function resetSessions() {
	global $db;
	authenticate('access_admin', false, true, true);
	
	mysqli_query($db,'TRUNCATE TABLE session');
}




//  +------------------------------------------------------------------------+
//  | Access right                                                           |
//  +------------------------------------------------------------------------+
function accessRight() {
	global $cfg;
	authenticate('access_logged_in');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Access right';
	$nav['url'][]	= '';
	require_once('include/header.inc.php');
	
	$check = '<img src="' . $cfg['img'] . 'small_check.png" alt="" class="small">';
	$uncheck = '<img src="' . $cfg['img'] . 'small_uncheck.png" alt="" class="small">';
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td colspan="3"><?php echo html($cfg['username']); ?></td>
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="odd" <?php echo onmouseoverAccessInfo('media'); ?>>
	<td></td>
	<td>Media</td>
	<td class="textspace"></td>	
	<td align="right"><?php echo $cfg['access_media'] ? $check : $uncheck; ?></td>
	<td></td>
</tr>
<tr class="even" <?php echo onmouseoverAccessInfo('popular'); ?>>
	<td></td>
	<td>Popular</td>
	<td class="textspace"></td>	
	<td align="right"><?php echo $cfg['access_popular'] ? $check : $uncheck; ?></td>
	<td></td>
</tr>
<tr class="odd" <?php echo onmouseoverAccessInfo('favorite'); ?>>
	<td></td>
	<td>Favorite</td>
	<td></td>
	<td align="right"><?php echo $cfg['access_favorite'] ? $check : $uncheck; ?></td>
	<td></td>
</tr>
<tr class="even" <?php echo onmouseoverAccessInfo('playlist'); ?>>
	<td></td>
	<td>Playlist</td>
	<td></td>
	<td align="right"><?php echo $cfg['access_playlist'] ? $check : $uncheck; ?></td>
	<td></td>
</tr>
<tr class="odd" <?php echo onmouseoverAccessInfo('play'); ?>>
	<td></td>
	<td>Play</td>
	<td></td>
	<td align="right"><?php echo $cfg['access_play'] ? $check : $uncheck; ?></td>
	<td></td>
</tr>
<tr class="even" <?php echo onmouseoverAccessInfo('add'); ?>>
	<td></td>
	<td>Add</td>
	<td></td>
	<td align="right"><?php echo $cfg['access_add'] ? $check : $uncheck; ?></td>
	<td></td>
</tr>
<tr class="odd" <?php echo onmouseoverAccessInfo('stream'); ?>>
	<td></td>
	<td>Stream</td>
	<td></td>
	<td align="right"><?php echo $cfg['access_stream'] ? $check : $uncheck; ?></td>
	<td></td>
</tr>
<tr class="even" <?php echo onmouseoverAccessInfo('download'); ?>>
	<td></td>
	<td>Download</td>
	<td></td>
	<td align="right"><?php echo $cfg['access_download'] ? $check : $uncheck; ?></td>
	<td></td>
</tr>
<!--
<tr class="odd" <?php echo onmouseoverAccessInfo('cover'); ?>>
	<td></td>
	<td>Cover</td>
	<td></td>
	<td align="right"><?php echo $cfg['access_cover'] ? $check : $uncheck; ?></td>
	<td></td>
</tr>
<tr class="even" <?php echo onmouseoverAccessInfo('record'); ?>>
	<td></td>
	<td>Record</td>
	<td></td>
	<td align="right"><?php echo $cfg['access_record'] ? $check : $uncheck; ?></td>
	<td></td>
</tr>
-->
<tr class="even" <?php echo onmouseoverAccessInfo('statistics'); ?>>
	<td></td>
	<td>Statistics</td>
	<td></td>
	<td align="right"><?php echo $cfg['access_statistics'] ? $check : $uncheck; ?></td>
	<td></td>
</tr>
<tr class="odd" <?php echo onmouseoverAccessInfo('admin'); ?>>
	<td></td>
	<td>Admin</td>
	<td></td>
	<td align="right"><?php echo $cfg['access_admin'] ? $check : $uncheck; ?></td>
	<td></td>
</tr>
</table>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | User statistics                                                        |
//  +------------------------------------------------------------------------+
function userStatistics() {
	global $cfg, $db;
	authenticate('access_admin');
	//authenticate('access_statistics');
	$period = get('period');
	
	if		($period == 'week')		$days = 7;
	elseif	($period == 'month')	$days = 31;
	elseif	($period == 'year')		$days = 365;
	elseif	($period == 'overall')	$days = 365 * 1000;
	else							message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]period');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'User statistics';
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="tab">
<tr>
	<td>
<!--  -->
<table cellspacing="0" cellpadding="0" class="tab">
<tr>
	<td class="<?php echo ($period == 'week') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='users.php?action=userStatistics&amp;period=week';">Week</td>
	<td class="tab_none tabspace"></td>
	<td class="<?php echo ($period == 'month') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='users.php?action=userStatistics&amp;period=month';">Month</td>
	<td class="tab_none tabspace"></td>
	<td class="<?php echo ($period == 'year') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='users.php?action=userStatistics&amp;period=year';">Year</td>
	<td class="tab_none tabspace"></td>
	<td class="<?php echo ($period == 'overall') ? 'tab_on' : 'tab_off'; ?>" onClick="location.href='users.php?action=userStatistics&amp;period=overall';">Overall</td>
	<td class="tab_none"></td>
</tr>
</table>
<table cellspacing="0" cellpadding="0" class="tab_border">
<tr class="tab_header">
	<td class="space"></td>
	<td id="header_user_name">&nbsp;Username</td>
	<td class="matrix">Play</td>
	<td class="matrix">Stream</td>
	<td class="matrix">Download</td>
	<!-- <td class="matrix">Cover</td> -->
	<td> </td>
</tr>
<tr class="line"><td colspan="7"></td></tr>
<?php
	$i= 0;
	$query = mysqli_query($db,'SELECT username, access_play, access_add, access_stream, access_download, access_cover, access_record, user_id FROM user ORDER BY username');
	while ($user = mysqli_fetch_assoc($query)) {
		$n[0] = $n[1] = $n[2] = $n[3] = $n[4] = 0;
		$query2 = mysqli_query($db,'SELECT
			flag,
			COUNT(*) AS counter 
			FROM counter 
			WHERE user_id = "' . (int) $user['user_id'] . '" 
			AND time > ' . (int) (time() - 86400 * $days) . '
			GROUP BY flag');
		while ($album = mysqli_fetch_assoc($query2)) {
			$n[ $album['flag'] ] = $album['counter'];
		}
?>
<tr class="<?php if ($cfg['username'] == $user['username']) echo 'select'; else echo ($i & 1) ? 'even mouseover' : 'odd mouseover'; $i++ ?>">
	<td ></td>
	<td class="nowrap"><a href="users.php?action=editUser&amp;user_id=<?php echo $user['user_id']; ?>">&nbsp;<i class="fa fa-user fa-fw icon-small"></i><?php echo html($user['username']); ?></a></td>
	<td class="matrix"><?php echo ($user['access_play'] || $user['access_add'])	? '<a href="index.php?action=viewPopular&amp;flag=0&amp;period=' . $period . '&amp;user_id=' . $user['user_id'] . '">' . $n[0] . '</a>' : '<img src="' . $cfg['img'] . 'small_uncheck.png" alt="" class="small">'; ?></td>
	<td class="matrix"><?php echo ($user['access_stream'])						? '<a href="index.php?action=viewPopular&amp;flag=1&amp;period=' . $period . '&amp;user_id=' . $user['user_id'] . '">' . $n[1] . '</a>' : '<img src="' . $cfg['img'] . 'small_uncheck.png" alt="" class="small">'; ?></td>
	<td class="matrix"><?php echo ($user['access_download'])					? '<a href="index.php?action=viewPopular&amp;flag=2&amp;period=' . $period . '&amp;user_id=' . $user['user_id'] . '">' . $n[2] . '</a>' : '<img src="' . $cfg['img'] . 'small_uncheck.png" alt="" class="small">'; ?></td>
	<!-- <td class="matrix"><?php echo ($user['access_cover'])						? '<a href="index.php?action=viewPopular&amp;flag=3&amp;period=' . $period . '&amp;user_id=' . $user['user_id'] . '">' . $n[3] . '</a>' : '<img src="' . $cfg['img'] . 'small_uncheck.png" alt="" class="small">'; ?></td> -->
	<td></td>
</tr>
<?php
	}
	$n[0] = $n[1] = $n[2] = $n[3] = $n[4] = 0;
	$query = mysqli_query($db,'SELECT
		flag,
		COUNT(*) AS counter 
		FROM counter 
		WHERE time > ' . (int) (time() - 86400 * $days) . '
		GROUP BY flag');
	while ($album = mysqli_fetch_assoc($query)) {
		$n[ $album['flag'] ] = $album['counter'];
	}
?>
<tr class="line"><td colspan="9"></td></tr>
<tr class="footer">
	<td></td>
	<td>&nbsp;<i class="fa fa-users fa-fw icon-small"></i>All users</td>
	<td class="matrix"><?php echo $n[NJB_COUNTER_PLAY]; ?></td>
	<td class="matrix"><?php echo $n[NJB_COUNTER_STREAM]; ?></td>
	<td class="matrix"><?php echo $n[NJB_COUNTER_DOWNLOAD]; ?></td>
	<!-- <td class="matrix"><?php echo $n[NJB_COUNTER_COVER]; ?></td> -->
	<td></td>
</tr>
</table>
<!--  -->
	</td>
</tr>
</table>
<br>
<div class="buttons">
<span><a href="users.php?action=userStatistics&amp;period=<?php echo $period; ?>">Refresh</a></span>
<span><a href="users.php?action=resetUserStatistics&amp;period=<?php echo $period; ?>&amp;sign=<?php echo $cfg['sign']; ?>" onClick="return confirm('Are you sure you want to reset all user statistics?')">Reset</a></span>
</div>

<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Reset user statistics                                                  |
//  +------------------------------------------------------------------------+
function resetUserStatistics() {
	global $db;
	authenticate('access_admin', false, true, true);
	
	mysqli_query($db,'TRUNCATE TABLE counter');
}
?>
