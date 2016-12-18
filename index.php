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
//  | index.php                                                              |
//  +------------------------------------------------------------------------+
//error_reporting(-1);
//ini_set("display_errors", 1);

require_once('include/initialize.inc.php');

if (cookie('netjukebox_width')<385) {$base_size = 90;}
elseif (cookie('netjukebox_width')<641) {$base_size = 120;}
else {$base_size = 150;}

$cfg['menu']	= 'Library';
$tileSizePHP	= get('tileSizePHP')	or $tileSizePHP = false;

switch(get('action')) {
    case '':                viewHome(); break;
    case 'view1':           view1(); break;
    case 'view2':           view2(); break;
    case 'view3':           view3(); break;
    case 'view1all':        view1all(); break;
    case 'view3all':        view3all(); break;
    case 'viewRandomAlbum': viewRandomAlbum(); break;
    case 'viewRandomTrack': viewRandomTrack(); break;
    case 'viewRandomFile':  viewRandomFile(); break;
    case 'viewYear':        viewYear(); break;
    case 'viewNew':         viewNew(); break;
    case 'viewPopular':     viewPopular(); break;
    case 'jsConf':
        header('Content-Type: application/javascript');
        echo $twig->render('js/ompd-conf.js', ['cfg' => $cfg]);
        break;
    default: message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action'); break;
}
