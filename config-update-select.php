<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015 Artur Sierzant                                 |
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
//  | config.php                                                             |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
$cfg['menu'] = 'config';

$action = getpost('action');
$update = getpost('update');

if 		($action == 'updateSelect')		updateSelect();

else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');




//  +------------------------------------------------------------------------+
//  | Select update range                                                    |
//  +------------------------------------------------------------------------+
function updateSelect() {
	global $cfg, $db;
	authenticate('access_admin');
	require_once('include/play.inc.php');
	
	$result = mysqli_query($db,'SELECT * FROM update_progress');
	$row = mysqli_fetch_assoc($result);
	$update_status = $row["update_status"];
	
	if ($update_status == 1) {
		header('Location: update.php?action=update&sign=' . $cfg['sign']);
		exit();
	}
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Update';
	require_once('include/header.inc.php');
	
	
	
	if(!isset($_COOKIE['update_dir']) || $_COOKIE['update_dir'] == '') {
		$dir = $cfg['media_dir'];
	} else {
		$dir = myHTMLencode($_COOKIE['update_dir']);
	}
	
	//$selectedDir = isset($_GET['selectedDir']) ? str_replace('ompd_ampersand_ompd','&',$_GET['selectedDir']) : $dir;
	
	$selectedDir = isset($_GET['selectedDir']) ? myHTMLencode($_GET['selectedDir']) : $dir;
	
	
?>

<table width="100%" cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td>&nbsp;</td>
	<td colspan="3">Update only selected directory</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td></td>
	<td>&nbsp;</td>
	<td></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td style="max-width: 4em;">Select directory:</td>
	<td>&nbsp;</td>
	<td>
	<div class="buttons">
	<input id="updateDir" value="<?php echo $selectedDir; ?>">
	<span id="updateBrowse"><i class="fa fa-folder-open-o fa-fw"></i> Browse...</span>
	</div>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td></td>
	<td>&nbsp;</td>
	<td>
		<div class="buttons">
		<span id="updateSelectedDir" onmouseover="return overlib('Update this dir');" onmouseout="return nd();">&nbsp;<i class="fa fa-refresh fa-fw"></i> Update this directory only</span>
		</div>
		<div id="errorMessage"></div>	
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td></td>
	<td>(This is force update of ALL files)</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td></td>
	<td>&nbsp;</td>
	<td></td>
</tr>
<tr class="header">
	<td>&nbsp;</td>
	<td colspan="3">Update whole media directory (<?php echo $cfg['media_dir'];?>)</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td></td>
	<td>&nbsp;</td>
	<td></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>
		<div class="buttons">
		<span id="updateAll" onmouseover="return overlib('Update all');" onmouseout="return nd();">&nbsp;<i class="fa fa-refresh fa-fw"></i> Update all</span>
		</div>
		<div id="errorMessage"></div>	
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td></td>
	<td>(This updates only new and changed files)</td>
</tr>
</table>


<?php
	
	echo '</table>' . "\n";
	require_once('include/footer.inc.php');
}




?>