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


//error_reporting(-1);
//ini_set('display_errors', 'On');

//  +------------------------------------------------------------------------+
//  | message.php                                                            |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
if (strpos($_SERVER['HTTP_REFERER'],'playlist.php') !== 0) require_once('include/mysqli.inc.php');

header('Expires: Mon, 9 Oct 2000 18:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
$message					= get('message');
$type						= get('type');
$file						= get('file');
$line						= get('line');
$skin						= get('skin');
$timestamp					= get('timestamp');
$cfg['align']		 		= true;										// required for header
$cfg['menu']				= get('menu');								// required for header
$cfg['username']			= get('username');							// required for footer
$cfg['sign']				= rawurlencode(get('sign'));				// required for header
$cfg['img']					= 'skin/' . rawurlencode($skin) . '/img/';	// required for header
$cfg['skin']				= $skin;									// required for header

if (validateSkin($skin) == false)
	exit('<h1>Wrong value</h1><p>Unsupported input value for <i>skin</i></p>');

if (in_array($type, array('ok', 'warning', 'error')) == false)
	$type = 'warning';

if (in_array($cfg['menu'], array('favorite', 'playlist', 'config')) == false)
	$cfg['menu'] = 'media';

if (time() - hexdec($timestamp) > 2) {
	$expired = bbcode($message);
	$message = '<strong>Message has expired</strong><br><div id="show" style="display: block;"><a href="javascript:showHide(\'show\', \'hide\');"><img src="' . $cfg['img'] . 'small_show.png" alt="" class="small space">Message</a></div>';
	$message .= '<div id="hide" style="display: none;"><a href="javascript:showHide(\'show\', \'hide\');"><img src="' . $cfg['img'] . 'small_hide.png" alt="" class="small space">Message</a><br>' . $expired . '</div>';
}
else
	$message = bbcode($message);

require_once('include/header.inc.php');
if ($cfg['debug']) { ?>
<table cellspacing="10" cellpadding="0" class="<?php echo $type; ?>">
<tr>
	<td rowspan="3" valign="top"><img src="<?php echo $cfg['img']; ?>medium_message_<?php echo $type; ?>.png" alt=""></td>
	<td><?php echo $message; ?></td>
</tr>
<tr class="line"><td></td></tr>
<!--
<tr>
	<td>
	<strong>file:</strong> <?php echo html($file); ?><br>
	<strong>line:</strong> <?php echo (int) $line; ?>
	</td>
</tr>
-->
</table>
<?php
}
else {
?>
<table cellspacing="10" cellpadding="0" class="<?php echo $type; ?>">
<tr>
	<td valign="top"><img src="<?php echo $cfg['img']; ?>medium_message_<?php echo $type; ?>.png" alt=""></td>
	<td valign="top"><?php echo $message ?></td>
</tr>
</table>
<?php
}
require_once('include/footer.inc.php');
?>