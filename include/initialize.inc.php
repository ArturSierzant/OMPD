<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant                            |
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
//  | Initialize                                                             |
//  +------------------------------------------------------------------------+
error_reporting(E_ALL ^ E_NOTICE);
@ini_set('display_errors', 0);

define('NJB_START_TIME', microtime(true));

define('NJB_VERSION', '1.02');
define('NJB_DATABASE_VERSION', 41);
define('NJB_IMAGE_SIZE', 300);
define('NJB_IMAGE_QUALITY', 85);
define('NJB_WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
define('NJB_SCRIPT', basename($_SERVER['SCRIPT_NAME']));
define('NJB_HTTPS', (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ? true : false);
//define('NJB_HTTPS', ($_SERVER['HTTPS'] == 'off' ? false : true));



define('NJB_HTTPQ', 0);
define('NJB_VLC', 1);
define('NJB_MPD', 2);

define('NJB_COUNTER_PLAY', 0);
define('NJB_COUNTER_STREAM', 1);
define('NJB_COUNTER_DOWNLOAD', 2);
define('NJB_COUNTER_COVER', 3);
define('NJB_COUNTER_RECORD', 4);

$cfg						= array();
$cfg['menu']				= 'media';
$cfg['sign']				= '';
$cfg['skin']				= 'ompd_default';
$cfg['img']					= 'skin/ompd_default/img/';
$cfg['username']			= '';
$cfg['sign_validated']		= false;
$cfg['align']				= false;

//  +------------------------------------------------------------------------+
//  | Initialize by ArtS                                                     |
//  +------------------------------------------------------------------------+
$cfg['player_id'] = 0;
$scroll_bar_correction = 20;
$base_size = 150;
$spaces = 1;
$str_limit = 20;

date_default_timezone_set('Europe/Warsaw');


//  +------------------------------------------------------------------------+
//  | Get home directory & load config file                                  |
//  +------------------------------------------------------------------------+
$temp = dirname(__FILE__);
$temp = realpath($temp . '/..');
define('NJB_HOME_DIR', str_replace('\\', '/', $temp) . '/');

require_once(NJB_HOME_DIR . 'include/config.inc.php');


 // +------------------------------------------------------------------------+
// | Proxy settings                                                         |
// +------------------------------------------------------------------------+
if ($cfg['proxy_enable'] == true) {
stream_context_set_default(
 array(
  'http' => array(
    'proxy' => "tcp://".$cfg['proxy_server'].":".$cfg['proxy_port'],
    'request_fulluri' => true
   )
  )
);
}

//  +------------------------------------------------------------------------+
//  | Default charset                                                        |
//  +------------------------------------------------------------------------+
if (NJB_WINDOWS)	define('NJB_DEFAULT_CHARSET', ($cfg['default_charset'] == '') ? 'ISO-8859-1' : $cfg['default_charset']);
else				define('NJB_DEFAULT_CHARSET', ($cfg['default_charset'] == '') ? 'UTF-8' : $cfg['default_charset']);

ini_set('default_charset', NJB_DEFAULT_CHARSET);


if (NJB_WINDOWS)	define('NJB_DEFAULT_FILESYSTEM_CHARSET', ($cfg['default_filesystem_charset'] == '') ? 'ISO-8859-1' : $cfg['default_filesystem_charset']);
else				define('NJB_DEFAULT_FILESYSTEM_CHARSET', ($cfg['default_filesystem_charset'] == '') ? 'UTF-8' : $cfg['default_filesystem_charset']);




//  +------------------------------------------------------------------------+
//  | Get home url                                                           |
//  +------------------------------------------------------------------------+
if (PHP_SAPI != 'cli') {
	$temp = rawurlencode(dirname($_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']));
	$temp = str_replace('%2F', '/', $temp);
	$temp = str_replace('%3A', ':', $temp);
	define('NJB_HOME_URL', (NJB_HTTPS ? 'https://' : 'http://') . $temp . '/');
}
else 
	define('NJB_HOME_URL', '');





//  +------------------------------------------------------------------------+
//  | Check for default stylesheets (skin)                                   |
//  +------------------------------------------------------------------------+
if (file_exists(NJB_HOME_DIR . 'skin/' . $cfg['skin'] . '/styles.css') == false && PHP_SAPI != 'cli')
	exit('<h1>Missing stylesheets</h1><p>O!MPD is missing the default stylesheets <i>skin/' . htmlspecialchars($cfg['skin'], ENT_COMPAT, NJB_DEFAULT_CHARSET) . '/styles.css</i></p>');
	



//  +------------------------------------------------------------------------+
//  | Offline                                                                |
//  +------------------------------------------------------------------------+
if ($cfg['offline'])
	message(__FILE__, __LINE__, 'warning', $cfg['offline_message']);




//  +------------------------------------------------------------------------+
//  | Check PHP version                                                      |
//  +------------------------------------------------------------------------+
if (version_compare(PHP_VERSION, '5.2.0', '<'))
	message(__FILE__, __LINE__, 'error', '[b]netjukebox ' . NJB_VERSION . ' requires PHP 5.2.0 or higher[/b][br]Now PHP ' . PHP_VERSION . ' is running.');




//  +------------------------------------------------------------------------+
//  | Check for required extensions                                          |
//  +------------------------------------------------------------------------+
if (function_exists('imagecreatetruecolor') == false)
	message(__FILE__, __LINE__, 'error', '[b]GD2 not loaded[/b][list][*]Compile PHP with GD2 support.[*]Or use a loadable module in the php.ini[/list]');
if (function_exists('mysqli_connect') == false)
	message(__FILE__, __LINE__, 'error', '[b]MYSQLi not loaded[/b][list][*]Compile PHP with MYSQL support.[*]Or use a loadable module in the php.ini[/list]');
if (function_exists('iconv') == false)
	message(__FILE__, __LINE__, 'error', '[b]ICONV not loaded[/b][list][*]Compile PHP with ICONV support.[*]Or use a loadable module in the php.ini[/list]');




//  +------------------------------------------------------------------------+
//  | Require once                                                           |
//  +------------------------------------------------------------------------+
require_once(NJB_HOME_DIR . 'include/library.inc.php');
require_once(NJB_HOME_DIR . 'include/globalize.inc.php');
require_once(NJB_HOME_DIR . 'include/tagProcessor.inc.php');

// To prevent mysql error snowball effect, and to speed up the message.php and cache.php script.
if (NJB_SCRIPT != 'message.php' && NJB_SCRIPT != 'cache.php')
	require_once(NJB_HOME_DIR . 'include/mysqli.inc.php');

//  +------------------------------------------------------------------------+
//  | Check and set default favorite and blacklist playlist					 |
//  +------------------------------------------------------------------------+

checkDefaultFavorites();
checkDefaultBlacklist();

//  +------------------------------------------------------------------------+
//  | Authenticate                                                           |
//  +------------------------------------------------------------------------+
function authenticate($access, $cache = false, $validate_sign = false, $disable_counter = false) {
	global $cfg, $db;
	
	//checkDefaultFavorites();
	
	if ($cache == false && headers_sent() == false)	{
		header('Expires: Mon, 9 Oct 2000 18:00:00 GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
	}
	
	$sid 			= cookie('netjukebox_sid');
	$authenticate	= getpost('authenticate');
	
	$query			= mysqli_query($db, 'SELECT logged_in, user_id, idle_time, ip, user_agent, sign, seed, skin,
						random_blacklist, thumbnail, thumbnail_size, stream_id, download_id, player_id
						FROM session
						WHERE sid = BINARY "' . mysqli_real_escape_string($db, $sid) . '"');
	$session		= mysqli_fetch_assoc($query);
	
	setSkin($session['skin']);
	//message(__FILE__, __LINE__,'test',$session['ip']);
	// Validate login
	if ($authenticate == 'validate') {
		$username	= post('username');
		$hash1		= post('hash1');
		$hash2		= post('hash2');
		$sign		= post('sign');
		
		if ($session['ip'] == '')
			//message(__FILE__, __LINE__,'test',$session['ip']);
			message(__FILE__, __LINE__, 'error', '[b]Login failed[/b][br]netjukebox requires cookies to login.[br]Enable cookies in your browser and try again.[br][url=index.php][img]small_login.png[/img]login[/url]');
			
		if ($session['ip'] != $_SERVER['REMOTE_ADDR'])
			message(__FILE__, __LINE__, 'error', '[b]Login failed[/b][br]Unexpected IP address[br][url=index.php][img]small_login.png[/img]login[/url]');
				
		$query		= mysqli_query($db, 'SELECT ' . (string) round(microtime(true) * 1000) . ' - pre_login_time AS login_delay FROM session WHERE ip = "' . mysqli_real_escape_string($db, $_SERVER['REMOTE_ADDR']) . '" ORDER BY pre_login_time DESC LIMIT 1');
		$ip			= mysqli_fetch_assoc($query);
		
		$query		= mysqli_query($db, 'SELECT password, seed, version, user_id FROM user WHERE username = "' . mysqli_real_escape_string($db, $username) . '"');
		$user		= mysqli_fetch_assoc($query);
		$user_id	= $user['user_id'];
		
		if (// validate password
			($user['version'] == 0 && $user['password'] == sha1($hash1) ||
			$user['version'] == 1 && $user['password'] == hmacsha1($hash1, $user['seed'])) &&
			// sha1 collision protection
			preg_match('#^[0-9a-f]{40}$#', $hash1) &&
			// new password validation as far as possible
			preg_match('#^[0-9a-f]{40}$#', $hash2) &&
			(($username == $cfg['anonymous_user'] && $hash2 == hmacsha1(hmacsha1($cfg['anonymous_user'], $session['seed']), $session['seed'])) ||
			($username != $cfg['anonymous_user'] && $hash2 != hmacsha1(hmacsha1('', $session['seed']), $session['seed']))) &&
			// brute force & hack attack protection
			$ip['login_delay'] > $cfg['login_delay'] &&
			$session['user_agent'] == substr($_SERVER['HTTP_USER_AGENT'], 0, 255) &&
			$session['sign'] == $sign) {
			
			mysqli_query($db, 'UPDATE user SET
				password		= "' . mysqli_real_escape_string($db, $hash2) . '",
				seed			= "' . mysqli_real_escape_string($db, $session['seed']) . '",
				version			= 1
				WHERE username	= "' . mysqli_real_escape_string($db, $username) . '"');
			
			$sign = randomKey();
			$sid = randomKey();
			
			mysqli_query($db, 'UPDATE session SET
				logged_in		= 1,
				user_id			= ' . (int) $user_id . ',
				login_time		= ' . (int) time() . ',
				idle_time		= ' . (int) time() . ',
				sid				= "' . mysqli_real_escape_string($db, $sid) . '",
				sign			= "' . mysqli_real_escape_string($db, $sign) . '",
				hit_counter		= hit_counter + ' . ($disable_counter ? 0 : 1) . ',
				visit_counter	= visit_counter + ' . (time() > $session['idle_time'] + 3600 ? 1 : 0) . '
				WHERE sid		= BINARY "' . mysqli_real_escape_string($db, cookie('netjukebox_sid')) . '"');
			
			setcookie('netjukebox_sid', $sid, time() + 31536000, null, null, NJB_HTTPS, true);
			@ob_flush();
			flush();
		}
		else
			logoutSession();
	}
	else {
		// Validate current session
		$user_id = $session['user_id'];
		
		if ($session['logged_in'] &&
			$session['ip']			== $_SERVER['REMOTE_ADDR'] &&
			$session['user_agent']	== substr($_SERVER['HTTP_USER_AGENT'], 0, 255) &&
			$session['idle_time'] + $cfg['session_lifetime'] > time()) {
			
			mysqli_query($db, 'UPDATE session SET
				idle_time		= ' . (int) time() . ',
				hit_counter		= hit_counter + ' . ($disable_counter ? 0 : 1) . ',
				visit_counter	= visit_counter + ' . (time() > $session['idle_time'] + 3600 ? 1 : 0) . '
				WHERE sid		= BINARY "' . mysqli_real_escape_string($db, $sid) . '"');
		}
		elseif ($access == 'access_always')	{
			$cfg['access_media']		= false;
			$cfg['access_popular']		= false;
			$cfg['access_favorite']		= false;
			$cfg['access_cover']		= false;
			$cfg['access_stream']		= false;
			$cfg['access_download']		= false;
			$cfg['access_playlist']		= false;
			$cfg['access_play']			= false;
			$cfg['access_add']			= false;
			$cfg['access_record']		= false;
			$cfg['access_statistics']	= false;
			$cfg['access_admin']		= false;
			return true;
		}
		else
			logoutSession();
	}
	
	// Username & user privalages
	unset($cfg['username']);
	$query = mysqli_query($db, 'SELECT
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
	$cfg += mysqli_fetch_assoc($query);
	
	// Validate privilege
	$access_validated = false;
	if (is_array($access)) {
		foreach ($access as $value)
			if (isset($cfg[$value]) && $cfg[$value])	$access_validated = true;
	}
	elseif (isset($cfg[$access]) && $cfg[$access])		$access_validated = true;
	elseif ($access == 'access_logged_in')				$access_validated = true;
	elseif ($access == 'access_always')					$access_validated = true;
	if ($access_validated == false)
		message(__FILE__, __LINE__, 'warning', '[b]You have no privilege to access this page[/b][br][url=index.php?authenticate=logout][img]small_login.png[/img]Login as another user[/url]');
	
	// Validate signature
	if	($cfg['sign_validated'] == false &&
		($validate_sign ||
		$authenticate == 'logoutAllSessions' ||
		$authenticate == 'logoutSession')) {
		
		$cfg['sign'] = randomKey();
		mysqli_query($db, 'UPDATE session
			SET	sign		= "' . mysqli_real_escape_string($db, $cfg['sign']) . '"
			WHERE sid		= BINARY "' . mysqli_real_escape_string($db, $sid) . '"');
		if ($session['sign'] == getpost('sign'))
			$cfg['sign_validated'] = true;
		else
			message(__FILE__, __LINE__, 'error', '[b]Signature expired[/b]');
	}
	else
		$cfg['sign'] = $session['sign'];
	
	// Logout
	if ($authenticate == 'logout' && $cfg['username'] != $cfg['anonymous_user']) {
		$query = mysqli_query($db, 'SELECT user_id FROM session
			WHERE logged_in
			AND user_id		= ' . (int) $user_id . '
			AND idle_time	> ' . (int) (time() - $cfg['session_lifetime']) );
		
		if (mysqli_affected_rows($db) > 1)	logoutMenu();
		else								logoutSession();	
	}
	elseif ($authenticate == 'logoutAllSessions' && $cfg['username'] != $cfg['anonymous_user']) {
		mysqli_query($db, 'UPDATE session
			SET logged_in	= 0
			WHERE user_id	= ' . (int) $user_id);
		logoutSession();
	}
	elseif ($authenticate == 'logoutSession' || $authenticate == 'logout')
		logoutSession();
	
	$cfg['user_id']				= $user_id;
	$cfg['sid']					= $sid;
	$cfg['session_seed']		= $session['seed'];
	$cfg['random_blacklist']	= $session['random_blacklist'];
	//$cfg['thumbnail']			= $session['thumbnail'];
	$cfg['thumbnail']			= 1;
	//$cfg['thumbnail_size']		= $session['thumbnail_size'];
	$cfg['thumbnail_size']		= 100;
	$cfg['stream_id']			= (isset($cfg['encode_extension'][$session['stream_id']])) ? $session['stream_id'] : -1;
	$cfg['download_id']			= (isset($cfg['encode_extension'][$session['download_id']])) ? $session['download_id'] : -1;
	$cfg['player_id']			= $session['player_id'];
}




//  +------------------------------------------------------------------------+
//  | Logout menu                                                            |
//  +------------------------------------------------------------------------+
function logoutMenu() {
	global $cfg;
	$cfg['align'] = true;
	
	require_once(NJB_HOME_DIR . 'include/header.inc.php');
?>
<form action="index.php">
<table cellspacing="10" cellpadding="0" class="warning">
<tr>
	<td rowspan="2" valign="top"><img src="<?php echo $cfg['img']; ?>medium_online.png" alt=""></td>
	<td><input type="radio" name="authenticate" value="logoutSession" checked class="space">Logout this session only<br>
	<input type="radio" name="authenticate" value="logoutAllSessions" class="space">Logout all sessions</td>
</tr>
<tr>
	<td align="right"><input type="submit" value="logout" class="button"></td>
</tr>
</table>
	<input type="hidden" name="sign" value="<?php echo $cfg['sign']; ?>">
</form>
<?php
	require_once(NJB_HOME_DIR . 'include/footer.inc.php');
	exit();
}




//  +------------------------------------------------------------------------+
//  | Logout session                                                         |
//  +------------------------------------------------------------------------+
function logoutSession() {
	global $cfg, $db;
	
	$cfg['username'] = ''; // Footer
	$cfg['access_media'] = ''; // Header opensearch
	
	$sid			= cookie('netjukebox_sid');
	$sign			= randomKey();
	$session_seed	= randomKey();
	
	// Update current session
	mysqli_query($db, 'UPDATE session SET
		logged_in			= 0,
		ip					= "' . mysqli_real_escape_string($db, $_SERVER['REMOTE_ADDR']) . '",
		user_agent			= "' . mysqli_real_escape_string($db, $_SERVER['HTTP_USER_AGENT']) . '",
		sign				= "' . mysqli_real_escape_string($db, $sign) . '",
		seed				= "' . mysqli_real_escape_string($db, $session_seed) . '"
		WHERE sid			= BINARY "' . mysqli_real_escape_string($db, $sid) . '"');
	if (mysqli_affected_rows($db) == 0) {
		// Create new session
		$sid = randomKey();
		
		mysqli_query($db, 'INSERT INTO session (logged_in, create_time, ip, user_agent, sid, sign, seed) VALUES (
			0,
			' . (int) time() . ',
			"' . mysqli_real_escape_string($db, $_SERVER['REMOTE_ADDR']) . '",
			"' . mysqli_real_escape_string($db, $_SERVER['HTTP_USER_AGENT']) . '",
			"' . mysqli_real_escape_string($db, $sid) . '",
			"' . mysqli_real_escape_string($db, $sign) . '",
			"' . mysqli_real_escape_string($db, $session_seed) . '")');
		
		setcookie('netjukebox_sid', $sid, time() + 31536000, null, null, NJB_HTTPS, true);
		@ob_flush();
		flush();
	}




//  +------------------------------------------------------------------------+
//  | Login                                                                  |
//  +------------------------------------------------------------------------+
	$query		= mysqli_query($db, 'SELECT username FROM user WHERE username = "' . mysqli_real_escape_string($db, $cfg['anonymous_user']) . '"');
	$user		= mysqli_fetch_assoc($query);
	$anonymous	= $user['username'];
	
	$action = get('action');
	if (NJB_SCRIPT == 'index.php' && substr($action, 0, 4) == 'view') {
		$url = 'index.php?';
		$get = getAll();
		foreach ($get as $key => $value) {
			$url .= rawurlencode($key) . '=' . rawurlencode($value) . '&amp;';
		}
		$url = substr($url, 0, -5);
	}
	else
		$url = 'index.php';
	
	$cfg['align'] = true;
	require_once(NJB_HOME_DIR . 'include/header.inc.php');
?>
<script type="text/javascript">
<!--
if (hmacsha1('key', 'The quick brown fox jumps over the lazy dog') != 'de7c9b85b8b78aa6bc8a7a36f70a90701c9db4d9') {
	document.write('<table cellspacing="10" cellpadding="0" class="error">');
	document.write('<tr>');
	document.write('	<td valign="top"><img src="<?php  echo $cfg['img']; ?>medium_message_error.png" alt=""><\/td>');
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
	document.write('<form action="<?php echo $url; ?>" method="post" name="loginform" id="loginform" onSubmit="loginStage1(this.username.value); return false;">');
	document.write('	<input type="hidden" name="authenticate" value="validate">');
	document.write('	<input type="hidden" name="hash1" value="">');
	document.write('	<input type="hidden" name="hash2" value="">');
	document.write('	<input type="hidden" name="sign" value="">');
	document.write('<table cellspacing="0" cellpadding="0" class="warning">');
	document.write('<tr class="space"><td colspan="5"><\/td><\/tr>');
	document.write('<tr>');
	document.write('	<td class="space"><\/td>');
	document.write('	<td>Username:<\/td>');
	document.write('	<td class="space"><\/td>');
	document.write('	<td><input type="text" name="username" value="<?php echo addslashes(html($anonymous)); ?>" maxlength="255" class="login" onKeyUp="anonymousPassword();"><\/td>');
	document.write('	<td class="space"><\/td>');
	document.write('<\/tr>');
	document.write('<tr>');
	document.write('	<td><\/td>');
	document.write('	<td>Password:<\/td>');
	document.write('	<td><\/td>');
	document.write('	<td><input type="password" name="password" class="login"><\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="space"><td colspan="5"><\/td><\/tr>');
	document.write('<tr>');
	document.write('	<td><\/td>');
	document.write('	<td colspan="3" align="right"><input type="submit" value="login" class="button"><\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="space"><td colspan="5"><\/td><\/tr>');
	document.write('<tr>');
	document.write('	<td><\/td>');
	document.write('	<td colspan="3" class="line"><\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
	document.write('<tr class="space"><td colspan="5"><\/td><\/tr>');
<?php
if ($cfg['admin_login_message'] == '') { ?>
	document.write('<tr>');
	document.write('	<td><\/td>');
	document.write('	<td colspan="3"><span class="login_message">Cookies and JavaScript are required to login.<br>');
	document.write('	Browser must support native XMLHttpRequest.<\/span><\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
<?php
}
else { ?>
	document.write('<tr>');
	document.write('	<td><\/td>');
	document.write('	<td colspan="3"><span class="login_message">');
	document.write('	<?php echo addslashes(bbcode($cfg['admin_login_message'])); ?><\/span><\/td>');
	document.write('	<td><\/td>');
	document.write('<\/tr>');
<?php
} ?>
	document.write('<tr class="space"><td colspan="5"><\/td><\/tr>');
	document.write('<\/table>');
	document.write('<\/form>');
}


function initialize() {
	if (typeof XMLHttpRequest != 'undefined') {
		document.loginform.username.focus();
		document.loginform.username.select();
		anonymousPassword();
	}
}


function anonymousPassword() {
	if (<?php echo ($anonymous) ? 'true' : 'false'; ?> && document.loginform.username.value == '<?php echo addslashes(html($anonymous)); ?>') {
		document.loginform.password.value = '';
		document.loginform.password.className = 'login readonly';
		// document.loginform.password.disabled = true;
	}
	else {
		document.loginform.password.className = 'login';
		// document.loginform.password.disabled = false;
	}
}


function loginStage1(username) {
	document.loginform.username.value = '';
	document.loginform.username.value = username;
	document.loginform.username.className = 'login readonly';
	document.loginform.password.className = 'login readonly';
	ajaxRequest('json.php', loginStage2, 'action=loginStage1&username=' + <?php echo (NJB_DEFAULT_CHARSET == 'UTF-8') ? 'encodeURIComponent' : 'escape'; ?>(username) + '&sign=<?php echo hmacsha1($cfg['server_seed'], $sign); ?>');
}


function loginStage2(data) {
	// data.user_seed, data.session_seed, data.sign;	
	var password = document.loginform.password.value;
	document.loginform.password.value = '';
	if (<?php echo ($anonymous) ? 'true' : 'false'; ?> && document.loginform.username.value == '<?php echo addslashes(html($anonymous)); ?>')
		password = '<?php echo addslashes(html($anonymous)); ?>';
	document.loginform.hash1.value = hmacsha1(password, data.user_seed);
	document.loginform.hash2.value = hmacsha1(hmacsha1(password, data.session_seed), data.session_seed);
	document.loginform.sign.value = data.sign;
	password = '';
	setTimeout('document.loginform.submit();', <?php echo $cfg['login_delay']; ?>);
}
//-->
</script>
<?php
	require_once(NJB_HOME_DIR . 'include/footer.inc.php');
	exit();
}




//  +------------------------------------------------------------------------+
//  | Set skin                                                               |
//  +------------------------------------------------------------------------+
function setSkin($skin) {
	global $cfg, $db;
	
	if ($skin != '' && file_exists(NJB_HOME_DIR . 'skin/' . $skin . '/styles.css')) {
		$cfg['skin']	= $skin;
		$cfg['img']		= 'skin/' . rawurlencode($skin) . '/img/';
		return true;
	}
	
	// Get session default skin
	$sid		= cookie('netjukebox_sid');
	$query		= mysqli_query($db, 'DESCRIBE session skin');
	$session 	= mysqli_fetch_assoc($query);
	if (file_exists(NJB_HOME_DIR . 'skin/' . $session['Default'] . '/styles.css'))	{
		if ($skin == '') {
			mysqli_query($db, 'UPDATE session
				SET skin	= "' . mysqli_real_escape_string($db, $session['Default']) . '"
				WHERE sid	= BINARY "' . mysqli_real_escape_string($db, $sid) . '"');
		}
		$cfg['skin']	= $session['Default'];
		$cfg['img']		= 'skin/' . rawurlencode($session['Default']) . '/img/';
		return true;
	}
	
	// Leave netjukebox skin set on top of this page and set it as default skin.
	mysqli_query($db, 'ALTER TABLE session CHANGE skin skin VARCHAR(255) NOT NULL DEFAULT "' . mysqli_real_escape_string($db, $cfg['skin']) . '"');
	return true;
}




//  +------------------------------------------------------------------------+
//  | Message: ok / warning / error                                          |
//  +------------------------------------------------------------------------+
function message($file, $line, $type, $message)	{
	global $cfg, $db;
	if (php_sapi_name() == 'cli') {
		// Command line error message
		require_once(NJB_HOME_DIR . 'include/library.inc.php');
		echo "\n";
		echo strtoupper($type) . "\n";
		echo str_repeat('-', 79) . "\n";
		echo bbcode2txt($message);
		if ($cfg['debug']) {
			echo "\n";
			echo str_repeat('-', 79) . "\n";
			echo 'File: ' . $file . "\n";
			echo 'Line: ' . $line;
		}
		exit();
	}
	elseif (NJB_SCRIPT != 'message.php') {
		if (in_array(@$_GET['menu'], array('favorite', 'playlist', 'config')))
			$cfg['menu'] = $_GET['menu'];
		
		if ($cfg['menu'] == 'config' && $message <> '[b]Signature expired[/b]')
			mysqli_query($db, "UPDATE update_progress SET 
			update_status = 0,
			last_update = 'Error:" . $message . "' ,
			last_update = '" . date('Y-m-d, H:i:s')   . "'
			");
		
		
		$url = NJB_HOME_URL;
		$url .= 'message.php';
		$url .= '?message=' . rawurlencode($message);
		$url .= '&type=' . rawurlencode($type);
		if ($cfg['debug']) {
			$url .= '&file=' . rawurlencode($file);
			$url .= '&line=' . rawurlencode($line);
		}
		$url .= '&menu=' . rawurlencode($cfg['menu']);
		$url .= '&skin=' . rawurlencode($cfg['skin']);
		$url .= '&username=' . rawurlencode($cfg['username']);
		$url .= '&sign=' . rawurlencode($cfg['sign']);
		$url .= '&timestamp=' . dechex(time());
		
		if (@$_GET['ajax'] == '1') {
			header('HTTP/1.1 500 Internal Server Error');
			exit($url);
		}
		elseif (headers_sent() == false) {
			header('Location: ' . $url);
			exit();
		}
		else
			exit('<script type="text/javascript">window.location="' . $url . '";</script>');
	}
}

//  +------------------------------------------------------------------------+
//  | Check if default favorites playlist is created						 |
//  +------------------------------------------------------------------------+
function checkDefaultFavorites() {
	global $cfg, $db;
	
	$query = @mysqli_query($db, "SELECT * FROM favorite WHERE name = '" . mysqli_real_escape_string($db, $cfg['favorite_name']) . "' AND comment = '" . mysqli_real_escape_string($db, $cfg['favorite_comment']) . "' LIMIT 1");
	
	if (@mysqli_num_rows($query) == 0) {
		@mysqli_query($db, "INSERT INTO favorite (name, comment, stream) 
		VALUES (
		'" . mysqli_real_escape_string($db, $cfg['favorite_name']) . "',
		'" . mysqli_real_escape_string($db, $cfg['favorite_comment']) . "',
		'0'
		)");
		$cfg['favorite_id'] = @mysqli_insert_id($db);
	}
	else {
		$favorite = @mysqli_fetch_assoc($query);
		$cfg['favorite_id'] = $favorite['favorite_id'];
	}
	//echo 'id=' . $cfg['favorite_id'];
}


//  +------------------------------------------------------------------------+
//  | Check if default blacklist is created									 |
//  +------------------------------------------------------------------------+
function checkDefaultBlacklist() {
	global $cfg, $db;
	
	$query = @mysqli_query($db, "SELECT * FROM favorite WHERE name = '" . mysqli_real_escape_string($db, $cfg['blacklist_name']) . "' AND comment = '" . mysqli_real_escape_string($db, $cfg['blacklist_comment']) . "' LIMIT 1");
	
	if (@mysqli_num_rows($query) == 0) {
		@mysqli_query($db, "INSERT INTO favorite (name, comment, stream) 
		VALUES (
		'" . mysqli_real_escape_string($db, $cfg['blacklist_name']) . "',
		'" . mysqli_real_escape_string($db, $cfg['blacklist_comment']) . "',
		'0'
		)");
		$cfg['blacklist_id'] = @mysqli_insert_id($db);
	}
	else {
		$favorite = @mysqli_fetch_assoc($query);
		$cfg['blacklist_id'] = $favorite['favorite_id'];
	}
	//echo 'id=' . $cfg['favorite_id'];
}


//  +------------------------------------------------------------------------+
//  | Log into file for debuggung purposes                                   |
//  | TODO: why is this accomplished by php's error_log() function?          |
//  +------------------------------------------------------------------------+
function cliLog($message) {
    global $cfg;

    if (!$cfg['debug']) {
        return;
    }
    if($cfg['debug_memory'] !== FALSE) {
        $message = "[" . convert(memory_get_usage(true)) . "] " . $message;
    }
    ini_set('log_errors', 'On');
    error_log($message . "\n", 3, NJB_HOME_DIR . 'tmp/update_log.txt');
}

function convert($size) {
    $unit = array('B','K','M','G','T','P');
    return @number_format($size/pow(1024,($i=floor(log($size,1024)))),1).$unit[$i];
}

?>