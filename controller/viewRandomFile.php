<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant                            |
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
//  | View random file                                                       |
//  +------------------------------------------------------------------------+
function viewRandomFile() {
    global $cfg, $db;
    
    authenticate('access_media');
    
    // formattedNavigator
    $nav            = array();
    $nav['name'][]  = 'Library';
    $nav['url'][]   = 'index.php';
    $nav['name'][]  = 'Random';
    
    if(!isset($_COOKIE['random_limit'])) {
        $limit = $cfg['play_queue_limit'];
    } else {
        $limit = $_COOKIE['random_limit'];
    }
    
    if(!isset($_COOKIE['random_dir'])) {
        $dir = $cfg['media_dir'];
    } else {
        $dir = str_replace('ompd_ampersand_ompd','&',$_COOKIE['random_dir']);
    }
    
    $selectedDir = isset($_GET['selectedDir']) ? $_GET['selectedDir'] . '/' : $dir;

    require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" style="width: 100%;">
<tr>
    <td>
<!--  -->
<table cellspacing="0" cellpadding="0" class="tab">
<tr>
    <td class="tab_off" onClick="location.href='index.php?action=viewRandomAlbum';">Album</td>
    <td class="tab_none tabspace"></td>
    <td class="tab_off" onClick="location.href='index.php?action=viewRandomTrack';">Track</td>
    <td class="tab_on" onClick="location.href='index.php?action=viewRandomFile';">File</td>
    <td class="tab_none tabspace"></td>
    <td class="tab_off" onClick="location.href='genre.php?action=blacklist';">Blacklist</td>
    <td class="tab_none">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" class="tab_border">
<?php
    if ($cfg['access_play'] || $cfg['access_add'] || $cfg['access_stream']) { ?>
<tr class="tab_header">
    <td>&nbsp;</td>
    <td></td>
    <td>&nbsp;</td>
    <td></td>
</tr>
<tr>
    <td></td>
    <td style="max-width: 4em;">Select directory:</td>
    <td></td>
    <td>
    <div class="buttons">
    <input id="randomDir" value="<?php 
        /* if ($selectedDir != '') {
            
            echo str_replace('ompd_ampersand_ompd','&',$selectedDir) . '/';
        }
        else {
            echo $cfg['media_dir'];
        } */
        echo $selectedDir;
     ?>">
    <span id="randomBrowse"><i class="fa fa-folder-open-o fa-fw"></i> Browse...</span>
    </div>
    </td>
</tr>
<tr>
    <td></td>
    <td>Limit to:</td>
    <td></td>
    <td><input id="randomLimit" value="<?php echo $limit; ?>" style="max-width: 3em;"> tracks</td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td></td>
    <td></td>
    <td></td>
</tr>
<?php 
    }
?>
</table>
<br>
<div class="buttons">
    <span id="playRandomFile" onmouseover="return overlib('Create playlist and play it');" onmouseout="return nd();">&nbsp;<i class="fa fa-play-circle-o fa-fw"></i> Create random list and play</span>
</div>
<div id="errorMessage"></div>
</td>
</tr>
</table>
<?php
    
    require_once('include/footer.inc.php');
}
