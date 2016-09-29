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
//  | cache.php                                                              |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
require_once('include/stream.inc.php');

$action = get('action');

if		($action == 'css')			css();
elseif	($action == 'javascript')	javascript();
exit();




//  +------------------------------------------------------------------------+
//  | css                                                                    |
//  +------------------------------------------------------------------------+
function css() {
	global $cfg;
	
	$skin = get('skin');
	
	if (validateSkin($skin) == false)
		exit('/* cache css error */');
			
	$content = @file_get_contents('skin/' . $skin . '/styles.css') or exit('/* cache css error */');
	$content = str_replace('img/', 'skin/' . rawurlencode($skin) . '/img/', $content);
	$content = str_replace('flag/', 'skin/' . rawurlencode($skin) . '/flag/', $content);

	header('Cache-Control: max-age=31536000');
	streamData($content, 'text/css', false, false, '"never_expire"');
}




//  +------------------------------------------------------------------------+
//  | javascript                                                             |
//  +------------------------------------------------------------------------+
function javascript() {
	global $cfg;
	
	$source = array('javascript-src/initialize.js',
					'javascript-src/overlib.js',
					'javascript-src/overlib_cssstyle.js',
					'javascript-src/sha1.js');
	
	$content = '';
	foreach ($source as $file)
		$content .= @file_get_contents($file) or exit('/* cache javascript error */');
	
	header('Cache-Control: max-age=31536000');
	streamData($content, 'application/javascript', false, false, '"never_expire"');
}
?>