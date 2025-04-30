<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015 Artur Sierzant                                 |
//  | http://www.ompd.pl                                                     |
//  |                                                                        |
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


global $cfg, $db;

	
	
	$url = 'config.php?action=editSettings';
	//$file = 'include/config.local.inc.php';
	$file = choose_config_file();
	$writeResult = 0;
	if (isset($_POST['configFile']))
	{
		$writeResult = 1;
		@file_put_contents($file, $_POST['configFile'])
			or message(__FILE__, __LINE__, 'error', '[b]Failed to write to file:[/b][br]' . $file . '[list][*]Check file permission[/list]');
	}

	$configFile = file_get_contents($file);
	
?>
	<script src="codemirror/lib/codemirror.js"></script>
	<link rel="stylesheet" href="codemirror/lib/codemirror.css">
	<link rel="stylesheet" href="codemirror/theme/eclipse.css">
	<script src="codemirror/mode/javascript/javascript.js"></script>
	<script src="codemirror/addon/search/search.js"></script>
	<script src="codemirror/addon/search/searchcursor.js"></script>
	<form id="formSettings" action="" method="post">
	<textarea id="configFile" name="configFile" spellcheck="false" class=""><?php echo htmlspecialchars($configFile) ?></textarea>
	<br>
	<div class="buttons">
	<div><span onClick="javascript: $('#formSettings').submit();">Save</span>
	<span onClick="javascript: window.location='config.php';">Cancel</span></div>
	</div>
	</form>
	<script>
		var myCodeMirror = CodeMirror.fromTextArea(configFile,{lineNumbers: false, theme: 'eclipse'});
	</script>
	
