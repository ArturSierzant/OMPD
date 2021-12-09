<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2021 Artur Sierzant                            |
//  | http://www.ompd.pl                                                     |
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
//  | HighResAudio                                                           |
//  +------------------------------------------------------------------------+

setConfigItem('hra_username',$cfg['hra_username'],'');
setConfigItem('hra_password',$cfg['hra_password'],'');
setConfigItem('hra_lang',$cfg['hra_lang'],'en');

//  +------------------------------------------------------------------------+
//  | Display options                                                        |
//  +------------------------------------------------------------------------+

setConfigItem('show_suggested',$cfg['show_suggested'],'true');
setConfigItem('show_last_played',$cfg['show_last_played'],'true');
setConfigItem('show_miniplayer',$cfg['show_miniplayer'],'true');
setConfigItem('show_quick_play',$cfg['show_quick_play'],'true');
setConfigItem('show_album_format',$cfg['show_album_format'],'false');
setConfigItem('show_album_popularity',$cfg['show_album_popularity'],'false');
setConfigItem('show_discography_browser',$cfg['show_discography_browser'],'true');
setConfigItem('show_album_versions',$cfg['show_album_versions'],'true');
setConfigItem('show_composer',$cfg['show_composer'],'false');
setConfigItem('show_multidisc',$cfg['show_multidisc'],'true');
setConfigItem('group_multidisc',$cfg['group_multidisc'],'true');
setConfigItem('show_DR',$cfg['show_DR'],'false');
//setConfigItem('max_items_per_page',$cfg['max_items_per_page'],'63');


?>
