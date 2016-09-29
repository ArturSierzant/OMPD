<?php
//  +------------------------------------------------------------------------+
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
//  | mysqli2mysql.php                                                       |
//  +------------------------------------------------------------------------+
header('Expires: Mon, 9 Oct 2000 18:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');




//exit('<strong>BEFORE USE MAKE A BACKUP</strong><br><br>netjukebox MySQLi to MySQL script converter.<br>Comment out line ' . __LINE__ . ' to run this script.');




//  +------------------------------------------------------------------------+
//  | Get home directory                                                     |
//  +------------------------------------------------------------------------+
$directory = dirname(__FILE__);
$directory = realpath($directory . '/..');
define('NJB_HOME_DIR', str_replace('\\', '/', $directory) . '/');




//  +------------------------------------------------------------------------+
//  | Rename                                                                 |
//  +------------------------------------------------------------------------+
echo '<strong>rename:</strong> include/mysqli.inc.php to include/mysql.inc.php';
@ob_flush();
flush();

if (file_exists(NJB_HOME_DIR . 'include/mysqli.inc.php')) {
	@unlink(NJB_HOME_DIR . 'include/mysql.inc.php');
	@rename(NJB_HOME_DIR . 'include/mysqli.inc.php', NJB_HOME_DIR . 'include/mysql.inc.php') or exit(' <font color="#FF0000">Failed to rename</font>');
}
elseif (file_exists(NJB_HOME_DIR . 'include/mysql.inc.php') == false)
	exit(' <font color="#FF0000">Failed to rename</font>');

echo ' <font color="#008000">successful</font><br>';
@ob_flush();
flush();




//  +------------------------------------------------------------------------+
//  | Convert                                                                |
//  +------------------------------------------------------------------------+
$files = array(
	'about.php',
	'cache.php',
	'config.php',
	'cover.php',
	'download.php',
	'favorite.php',
	'genre.php',
	'image.php',
	'index.php',
	'json.php',
	'message.php',
	'opensearch.php',
	'phpinfo.php',
	'play.php',
	'playlist.php',
	'record.php',
	'ridirect.php',
	'statistics.php',
	'stream.php',
	'update.php',
	'users.php',
	'include/cache.inc.php',
	'include/config.inc.php',
	'include/footer.inc.php',
	'include/globalize.inc.php',
	'include/header.inc.php',
	'include/initialize.inc.php',
	'include/library.inc.php',
	'include/mysql.inc.php',
	'include/play.inc.php',
	'include/stream.inc.php');

foreach ($files as $file) {
	echo '<strong>convert:</strong> ' . htmlentities($file);
	@ob_flush();
	flush();
	
	$content = @file_get_contents(NJB_HOME_DIR . $file) or exit(' <font color="#FF0000">Failed to open file</font>');
	$content = mysqli2mysql($content);
	if (file_put_contents(NJB_HOME_DIR . $file, $content) === false)
		exit(' <font color="#FF0000">Failed to write file</font>');
	
	echo ' <font color="#008000">successful</font><br>';
	@ob_flush();
	flush();
}




//  +------------------------------------------------------------------------+
//  | MySQLi to MySQL                                                        |
//  +------------------------------------------------------------------------+
function mysqli2mysql($string) {
	$mysqli = array(
		'#mysqli_([a-z_]+?)\(\$db, #',
		'#mysqli_([a-z_]+?)\((.+?)\)#',
		'#\$cfg\[\'mysqli_([a-z_]+?)\'\]#',
		'#\| (mysql)i(.+?)\|#i',
		'#(mysql)i#i');
	$mysql = array(
		'mysql_$1(',
		'mysql_$1($2)',
		'$cfg[\'mysql_$1\']',
		'| $1$2 |',
		'$1');
	
	return preg_replace($mysqli, $mysql, $string);
}
