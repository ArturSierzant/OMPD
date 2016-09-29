<?php
//  +------------------------------------------------------------------------+
//  | netjukebox, Copyright © 2001-2011 Willem Bartels                       |
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
//  | Line break converter                                                   |
//  +------------------------------------------------------------------------+
header('Expires: Mon, 9 Oct 2000 18:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');




exit('<strong>BEFORE USE MAKE A BACKUP</strong><br><br>netjukebox line break converter.<br>Comment out line ' . __LINE__ . ' to run this script.');




//  +------------------------------------------------------------------------+
//  | Configuration                                                          |
//  +------------------------------------------------------------------------+
$cfg['script'][] = 'php';
$cfg['script'][] = 'js';
$cfg['script'][] = 'sql';
$cfg['script'][] = 'css';
$cfg['script'][] = 'txt';

$cfg['input_line_break'][]	= "\r\n";	// Windows
$cfg['input_line_break'][]	= "\r";		// OSX
$cfg['output_line_break']	= "\n";		// Linux




//  +------------------------------------------------------------------------+
//  | Exclude this script from converting                                    |
//  +------------------------------------------------------------------------+
$cfg['exclude_script'] = basename($_SERVER['SCRIPT_NAME']);




//  +------------------------------------------------------------------------+
//  | Get home directory                                                     |
//  +------------------------------------------------------------------------+
$directory			= dirname(__FILE__);
$directory			= realpath($directory . '/..');
$cfg['home_dir']	= str_replace('\\', '/', $directory) . '/';




//  +------------------------------------------------------------------------+
//  | Convert                                                                |
//  +------------------------------------------------------------------------+
recursiveScan($cfg['home_dir']);
echo 'Ready';




//  +------------------------------------------------------------------------+
//  | Recursive convert                                                      |
//  +------------------------------------------------------------------------+
function recursiveScan($dir) {
	global $cfg;
	
	$entries = @scandir($dir) or exit('Failed to open directory:<br>' . htmlentities($dir));
	foreach ($entries as $entry) {
		if ($entry[0] != '.' && !in_array($entry, array($cfg['exclude_script'], 'lost+found', 'Temporary Items', 'Network Trash Folder', 'System Volume Information', 'RECYCLER', '$RECYCLE.BIN'))) {
			if (is_dir($dir . $entry . '/'))
				recursiveScan($dir . $entry . '/');
			else {
				$extension = substr(strrchr($entry, '.'), 1);
				$extension = strtolower($extension);
				if (in_array($extension, $cfg['script'])) {
					$file = $dir . $entry;
					$input_script = file_get_contents($file);
					$output_script = str_replace($cfg['input_line_break'], $cfg['output_line_break'], $input_script);
					if ($input_script != $output_script) {
						echo '<strong>Convert:</strong> ' . htmlentities($file) . '<br>';
						file_put_contents($file, $output_script);
					}
					else
						echo '<strong>No convertion needed:</strong> ' . htmlentities($file) . '<br>';
					@ob_flush();
					flush();
					
					
				}
			}
		}
	}
}
