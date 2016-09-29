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
//  | Initialize                                                             |
//  +------------------------------------------------------------------------+
if (version_compare(PHP_VERSION, '5.4.0', '>='))
	define('NJB_REMOVE_MAGIC_QUOTES', false);
else {
	@set_magic_quotes_runtime(0);
	define('NJB_REMOVE_MAGIC_QUOTES', (get_magic_quotes_gpc()) ? true : false);
}




//  +------------------------------------------------------------------------+
//  | Get                                                                    |
//  +------------------------------------------------------------------------+
function get($key) {
	if (NJB_REMOVE_MAGIC_QUOTES == false)	return @$_GET[$key];
	else									return removeMagicQuotes(@$_GET[$key]);
}




//  +------------------------------------------------------------------------+
//  | Get all                                                                |
//  +------------------------------------------------------------------------+
function getAll() {
	if (NJB_REMOVE_MAGIC_QUOTES == false)	return $_GET;
	else									return removeMagicQuotes($_GET);
}




//  +------------------------------------------------------------------------+
//  | Post                                                                   |
//  +------------------------------------------------------------------------+
function post($key) {
	if (NJB_REMOVE_MAGIC_QUOTES == false)	return @$_POST[$key];
	else									return removeMagicQuotes(@$_POST[$key]);
}




//  +------------------------------------------------------------------------+
//  | Cookie                                                                 |
//  +------------------------------------------------------------------------+
function cookie($key) {
	if (NJB_REMOVE_MAGIC_QUOTES == false)	return @$_COOKIE[$key];
	else									return removeMagicQuotes(@$_COOKIE[$key]);
}




//  +------------------------------------------------------------------------+
//  | Get and post                                                           |
//  +------------------------------------------------------------------------+
function getpost($key) {
	if (isset($_GET[$key]))					return get($key);
	else									return post($key);
}




//  +---------------------------------------------------------------------------+
//  | Remove magic quotes                                                       |
//  +---------------------------------------------------------------------------+
function removeMagicQuotes($data) {
	$data = is_array($data) ? array_map('removeMagicQuotes', $data) : stripslashes($data);
	return $data;
}
?>