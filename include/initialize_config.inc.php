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
//  | Tidal                                                                  |
//  +------------------------------------------------------------------------+

setConfigItem('tidal_client_id',$cfg['tidal_client_id'],'');
setConfigItem('tidal_client_secret',$cfg['tidal_client_secret'],'');
setConfigItem('tidal_audio_quality',$cfg['tidal_audio_quality'],'LOSSLESS');
setConfigItem('fix_tidal_freezes',$cfg['fix_tidal_freezes'],'false');
setConfigItem('tidal_direct',$cfg['tidal_direct'],'true');
setConfigItem('upmpdcli_tidal',$cfg['upmpdcli_tidal'],'');

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
setConfigItem('album_download',$cfg['album_download'],'false');
setConfigItem('album_copy',$cfg['album_copy'],'false');
setConfigItem('show_composer',$cfg['show_composer'],'false');
setConfigItem('show_multidisc',$cfg['show_multidisc'],'true');
setConfigItem('group_multidisc',$cfg['group_multidisc'],'true');
setConfigItem('show_DR',$cfg['show_DR'],'false');
setConfigItem('max_items_per_page',$cfg['max_items_per_page'],'63');

//  +------------------------------------------------------------------------+
//  | Playback options                                                       |
//  +------------------------------------------------------------------------+

setConfigItem('add_autoplay',$cfg['add_autoplay'],'true');
setConfigItem('play_queue',$cfg['play_queue'],'false');
setConfigItem('play_queue_limit',$cfg['play_queue_limit'],'250');

//  +------------------------------------------------------------------------+
//  | Update options                                                         |
//  +------------------------------------------------------------------------+

setConfigItem('media_dir',$cfg['media_dir'],'/var/lib/mpd/music/');
setConfigItem('style_enable',$cfg['style_enable'],'false');
setConfigItem('show_comments_as_tags',$cfg['show_comments_as_tags'],'false');
setConfigItem('tags_separator',$cfg['tags_separator'],';');
setConfigItem('multigenre_separator',$cfg['multigenre_separator'],';');

//  +------------------------------------------------------------------------+
//  | Login options                                                          |
//  +------------------------------------------------------------------------+

setConfigItem('default_username',$cfg['default_username'],'');
setConfigItem('default_password',$cfg['default_password'],'');
setConfigItem('anonymous_user',$cfg['anonymous_user'],'anonymous');
setConfigItem('session_lifetime',$cfg['session_lifetime'],'31536000'); //3600s * 24h * 365d 
//setConfigItem('mpd_password',$cfg['mpd_password'],'');

//  +------------------------------------------------------------------------+
//  | Youtube options                                                        |
//  +------------------------------------------------------------------------+

setConfigItem('show_youtube_results',$cfg['show_youtube_results'],'true');
setConfigItem('youtube_key',$cfg['youtube_key'],'AIzaSyCasdVt44uKVWymVBVtILwtu1Sgyx2sdl0');
setConfigItem('youtube_max_results',$cfg['youtube_max_results'],'30');

//  +------------------------------------------------------------------------+
//  | Quick search                                                           |
//  +------------------------------------------------------------------------+

$query = mysqli_query($db, "SELECT * FROM config WHERE name='quick_search'");
$items_count = mysqli_num_rows($query);
if ($items_count > 0) { //copy from DB
  unset($cfg['quick_search']);
  while ($quick_search = mysqli_fetch_assoc($query)) {
    $cfg['quick_search'][$quick_search['index']] = json_decode($quick_search['value'],true);
  }
}
else {
  if (isset($cfg['quick_search'])) { //copy from existing $cfg 
    foreach($cfg['quick_search'] as $key => $value) {
      setConfigItem('quick_search', $cfg['quick_search'][$key], $value, $key); 
    }
  }
  else { //set default values
    setConfigItem('quick_search',$cfg['quick_search'][1], array("Live Concerts","album LIKE '%live%'") , 1);
    setConfigItem('quick_search',$cfg['quick_search'][2], array("HD Audio","audio_bits_per_sample > 16 OR audio_sample_rate > 48000"), 2);
    setConfigItem('quick_search',$cfg['quick_search'][3], array("Japanese Editions","album LIKE '%japan%' OR comment LIKE '%SHM-CD%'"), 3);
    setConfigItem('quick_search',$cfg['quick_search'][4], array("Pop of the 80's","genre ='Pop' and ((album.year BETWEEN 1980 AND 1989) or comment like '%80s%')"), 4);
  }
}

?>
