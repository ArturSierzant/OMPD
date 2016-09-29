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
//  | phpinfo.php                                                            |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
authenticate('access_admin');

if ($cfg['php_info'] == false)
	message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]phpinfo disabled');

ob_start();
phpinfo();
$phpinfo = ob_get_contents();
ob_end_clean();

$phpinfo = preg_replace('#a:link \{.+?\}#', 'a:link, a:visited {color: #000099; text-decoration: none;}', $phpinfo);
$phpinfo = preg_replace('#PHP Version#', '<a href="config.php">netjukebox ' . html(NJB_VERSION) . '</a> | PHP Version', $phpinfo);
echo $phpinfo;
?>