<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2018 Artur Sierzant                            |
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



//  +------------------------------------------------------------------------+
//  | update_progress.php                                                             |
//  +------------------------------------------------------------------------+

//error_reporting(-1);
//ini_set('display_errors', 'On');
//$updateStage = $_GET["updateStage"];


require_once('include/initialize.inc.php');
require_once('include/cache.inc.php');

$cfg['menu'] = 'config';
$cfg['sign'] = $_GET["sign"];
// formattedNavigator
$nav			= array();
$nav['name'][]	= 'Configuration';
$nav['url'][]	= 'config.php';
$nav['name'][]	= 'Update';
require_once('include/header.inc.php');
?>
<table width="100%" cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td class="update_text">Update</td>
	<td>Progress</td>
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="4"></td></tr>
<tr class="odd">
	<td></td>
	<td>Structure &amp; image:</td>
	<td><span id="structure"></span></td>
	<td></td>
</tr>
<tr class="even">
	<td></td>
	<td>File info:</td>
	<td><span id="fileinfo"></span></td>
	<td></td>
</tr>
<tr class="odd">
	<td></td>
	<td>Cleanup:</td>
	<td><span id="cleanup"></span></td>
	<td></td>
</tr>
<tr class="even">
	<td></td>
	<td>Update time:</td>
	<td><span id="updateTime"></span></td>
	<td></td>
</tr>
</table>
<script>
	
	window.setInterval(function() {
		show_update_progress();
	}, 1000);
	
	function show_update_progress() {
		$.ajax({
			type: "POST",
			url: "ajax-update-progress.php",
			dataType : 'json',
			success : function(json) {
				$("#structure").html(json['structure_image']);
				$("#fileinfo").html(json['file_info']);
				$("#cleanup").html(json['cleanup']);
				$("#updateTime").html(json['update_time']);
				
			}
		});
	}
	
	</script>
<?php
require('include/footer.inc.php');
?>