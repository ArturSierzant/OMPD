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
//  | footer.inc.php                                                         |
//  +------------------------------------------------------------------------+
if (isset($cfg['footer']) && $cfg['footer'] == 'close') {
	echo '<script type="text/javascript">document.getElementById(\'execution_time\').innerHTML=\'' . executionTime() . '\';</script>' . "\n";
	echo '</body>' . "\n";
	echo '</html>' . "\n";
	echo '<!-- end of file -->' . "\n";
	exit();
}
$footer = ($cfg['username'] != '') ? '| <a href="index.php?authenticate=logout">Logout: ' . html($cfg['username']) . '</a> ' : '';
$footer .= '| <a href="about.php">O!MPD ' . html(NJB_VERSION) . '</a> ';
$footer .= '| Script execution time: <span id="execution_time">' . executionTime() . '</span> |';
require_once(NJB_HOME_DIR . 'skin/' . $cfg['skin'] . '/template.footer.php');

if (isset($cfg['footer']) && $cfg['footer'] == 'dynamic') {
	echo '<!-- dynamic content -->' . "\n";
	@ob_flush();
	flush();
}
else { 
	echo '<script type="text/javascript">';
	echo 'hideSpinner();';
	echo '</script>';
	echo '</body>' . "\n";
	echo '</html>' . "\n";
	echo '<!-- end of file -->' . "\n";
	exit();
}
?>
