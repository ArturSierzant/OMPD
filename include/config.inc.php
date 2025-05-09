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
//  | MySQL configuration                                                    |
//  +------------------------------------------------------------------------+
$cfg['mysqli_host']                 = '127.0.0.1';
$cfg['mysqli_db']                   = 'ompd';
$cfg['mysqli_user']                 = '';
$cfg['mysqli_password']             = '';
$cfg['mysqli_port']                 = '3306';
$cfg['mysqli_socket']               = '';
$cfg['mysqli_auto_create_db']       = true;




//  +------------------- NEW IN O!MPD 1.08 ----------------------------------+


//  +------------------------------------------------------------------------+
//  | HighResAudio                                                           |
//  +------------------------------------------------------------------------+
//  |                                                                        |
//  | HRA setings were moved to GUI: Config->HRA                             |
//  |                                                                        |
//  +------------------------------------------------------------------------+

//  +------------------------------------------------------------------------+
//  | Options moved to GUI: Config->Settings:                                |
//  +------------------------------------------------------------------------+
//  | - Media directory                                                      |
//  | - Start page display options                                           |
//  | - Miniplayer                                                           |
//  | - Display quick play and add at albums covers                          |
//  | - Album format badge and album popularity bar                          |
//  | - Discography browser                                                  |
//  | - Other versions of album                                              |
//  | - Track composer                                                       |
//  | - Multi-disc                                                           |
//  | - Track dynamic range                                                  |
//  | - Pagination settings                                                  |
//  | - Playback settings                                                    |
//  | - Update settings                                                      |
//  | - Login and authenticate settings                                      |
//  | - Youtube search settings                                              |
//  | - Album features                                                       |
//  | - Tidal settings                                                       |
//  | - Quick search                                                         |
//  +------------------------------------------------------------------------+

//  +------------------- END OF NEW IN O!MPD 1.08 ---------------------------+





//  +------------------------------------------------------------------------+
//  | Internet stations image                                                |
//  +------------------------------------------------------------------------+
//  | Path to images for radio stations and other streams.                   |
//  | jpg and png files are supported.                                       |
//  |                                                                        |
//  | Use host name as name for image file, e.g. for stream:                 |
//  | http://stream14.shoutcastsolutions.com:8057/256stream                  |
//  | copy to dir defined in $cfg['stream_covers_dir'] file named:           |
//  | stream14.shoutcastsolutions.com.png                                    |
//  |                                                                        |
//  | In URLs like this:                                                     |
//  | http://poznan5-3.radio.pionier.net.pl:8000/afera.mp3                   |
//  | ommit port path:                                                       |
//  | poznan5-3.radio.pionier.net.pl__afera.mp3.png                          |
//  |                                                                        |
//  | For URLs with path replace '/' with double undersoce '__', e.g. for:   |
//  | http://ice3.somafm.com/seventies-128-aac                               |
//  | use:                                                                   |
//  | ice3.somafm.com__seventies-128-aac.jpg                                 |
//  |                                                                        |
//  | O!MPD tries to download and use favicons for internet radio stations   |
//  | searched in Library -> Radio Browser. To use this feature you need     |
//  | to add write rights to this directory for your web server.             |
//  |                                                                        |
//  +------------------------------------------------------------------------+

$cfg['stream_covers_dir'] = 'covers/';




//  +------------------------------------------------------------------------------+
//  | Play audio from Youtube                                                      |
//  +------------------------------------------------------------------------------+
//  | You can listen to YT movie audio stream by inserting address of YT           |
//  | movie into Now Playing -> Add -> File/stream                                 |
//  |                                                                              |
//  | It requires youtube-dl (https://github.com/rg3/youtube-dl)                   |
//  |                                                                              |
//  | In general it should work with:                                              |
//  | $cfg['python_path'] = '' and  $cfg['youtube-dl_path'] = 'youtube-dl'         |
//  | but in my case I have to define full path to python and youtube-dl:          |
//  | $cfg['python_path'] = '/share/HDA_DATA/.qpkg/Python3/src/bin/python3';       |
//  | $cfg['youtube-dl_path'] = '/share/HDA_DATA/Download/youtube-dl/__main__.py'; |
//  +------------------------------------------------------------------------------+
unset($cfg['youtube_indicator']);

$cfg['python_path'] = '';
$cfg['youtube-dl_path'] = 'youtube-dl';
$cfg['youtube-dl_options'] = '-j --no-check-certificate --prefer-insecure';
$cfg['youtube_audio_format_name'] = '140 - audio only (tiny)';
$cfg['youtube_indicator'][] = 'www.youtube.';
$cfg['youtube_indicator'][] = 'youtu.be/';
$cfg['youtube_indicator'][] = 'm.youtube.';



//  +------------------------------------------------------------------------+
//  | Default album search action                                            |
//  +------------------------------------------------------------------------+
//  | One of $cfg['search_name'] from section 'Internet search'              |
//  | which will be used as default search after clicking album cover        |
//  | in album view                                                          |
//  +------------------------------------------------------------------------+

$cfg['default_search_name'] = 'Google';




//  +------------------------------------------------------------------------+
//  | Various artists                                                        |
//  +------------------------------------------------------------------------+
//  | Names used in ALBUM ARTIST or ARTIST tag for various artist albums     |
//  +------------------------------------------------------------------------+
unset($cfg['VA']);

$cfg['VA'][]           = 'Various Artists';
$cfg['VA'][]           = 'VA';
$cfg['VA'][]           = 'Różni wykonawcy';




//  +------------------------------------------------------------------------+
//  | Multi-disc indicators                                                  |
//  +------------------------------------------------------------------------+
//  | Strings in album title indicating that album is a part                 |
//  | of a multi-disc set                                                    |
//  +------------------------------------------------------------------------+
unset($cfg['multidisk_indicator']);

$cfg['multidisk_indicator'][] = "Disc ";
$cfg['multidisk_indicator'][] = "Disk ";
$cfg['multidisk_indicator'][] = "CD ";
$cfg['multidisk_indicator'][] = "CD#";
$cfg['multidisk_indicator'][] = " CD";
$cfg['multidisk_indicator'][] = " Vol.";
$cfg['multidisk_indicator'][] = " Part ";




//  +------------------------------------------------------------------------+
//  | Other versions of album indicators                                     |
//  +------------------------------------------------------------------------+
//  | Strings in album title indicating that album may have other versions   |
//  | e.g. " (" in:                                                          |
//  |   Brothers In Arms (24bit-96kHz)                                       |
//  |   Brothers In Arms (2013 Remaster, MFSL, 24bit-88.2kHz)                |
//  +------------------------------------------------------------------------+
unset($cfg['album_versions_indicator']);

$cfg['album_versions_indicator'][] = " (";
$cfg['album_versions_indicator'][] = " [";
$cfg['album_versions_indicator'][] = ", ";
$cfg['album_versions_indicator'][] = ": ";
$cfg['album_versions_indicator'][] = " - ";





//  +------------------------------------------------------------------------+
//  | Allow access to all files                                              |
//  +------------------------------------------------------------------------+
//  | When set to true, it allows to access to files outside of media        |
//  | directory in file browser and for download.                            |
//  | WARNING! Can be dangerous - it allows to access all files your web     |
//  | server has access to - including files with passwords                  |
//  | USE WITH CARE!                                                         |
//  +------------------------------------------------------------------------+

$cfg['allow_access_to_all_files'] = false;




//  +------------------------------------------------------------------------+
//  | Directory Blacklist (those dirnames will not be scanned)               |
//  +------------------------------------------------------------------------+
unset($cfg['directory_blacklist']);

$cfg['directory_blacklist'][]       = 'lost+found';
$cfg['directory_blacklist'][]       = 'Temporary Items';
$cfg['directory_blacklist'][]       = 'Network Trash Folder';
$cfg['directory_blacklist'][]       = 'System Volume Information';
$cfg['directory_blacklist'][]       = 'RECYCLER';
$cfg['directory_blacklist'][]       = '$RECYCLE.BIN';
$cfg['directory_blacklist'][]       = '.@__thumb';




//  +------------------------------------------------------------------------+
//  | Timezone Setting                                                       |
//  +------------------------------------------------------------------------+
//  | Leave this empty to use PHP's default                                  |
//  +------------------------------------------------------------------------+

$cfg['timezone'] = '';




//  +------------------------------------------------------------------------+
//  | MPD password                                                           |
//  +------------------------------------------------------------------------+

$cfg['mpd_password']				= "";




//  +------------------------------------------------------------------------+
//  | Default blacklist                                                      |
//  +------------------------------------------------------------------------+
//  | Name and description for blacklist                                     |
//  +------------------------------------------------------------------------+

$cfg['blacklist_name']				  = 'Blacklist';
$cfg['blacklist_comment']			  = 'Tracks to be skipped';



//  +------------------------------------------------------------------------+
//  | Ignore media dir access error                                          |
//  +------------------------------------------------------------------------+
//  | Set to true - update process continues w/o error message when trying   |
//  | to scan directories with no access                                     |
//  | Set to false - error message is displayed and update stops             |
//  +------------------------------------------------------------------------+
$cfg['ignore_media_dir_access_error'] 	= false;




//  +------------------------------------------------------------------------+
//  | Proxy                                                                  |
//  +------------------------------------------------------------------------+

$cfg['proxy_enable'] 					= false;
$cfg['proxy_server'] 					= '192.168.1.1';
$cfg['proxy_port'] 						= '80'; 




//  +------------------------------------------------------------------------+
//  | If you have problem with displaying some icons, set this to true       |
//  +------------------------------------------------------------------------+

$cfg['download_font_awesome']			= false;




//  +------------------------------------------------------------------------+
//  | Source of artist/album title/track title                               |
//  +------------------------------------------------------------------------+
//  | 'tags' - data from tags in file                                        |
//  | Others values are not allowed now - don't change this setting or update|
//  | of library will crash. This is for future use.                         |
//  +------------------------------------------------------------------------+
$cfg['name_source']						= 'tags';




//  +------------------------------------------------------------------------+
//  | Songs separators                                                       |
//  +------------------------------------------------------------------------+
//  | Separators in song title for search for another versions of song       |
//  +------------------------------------------------------------------------+
unset($cfg['separator']);

$cfg['separator'][] = 	" (";
$cfg['separator'][] = 	" [";
$cfg['separator'][] = 	" {";
$cfg['separator'][] = 	"(";
$cfg['separator'][] = 	"[";
$cfg['separator'][] = 	"{";
$cfg['separator'][] = 	".";
$cfg['separator'][] = 	",";
$cfg['separator'][] = 	"/";
$cfg['separator'][] = 	" /";
$cfg['separator'][] = 	"\\";
$cfg['separator'][] = 	" \\";
$cfg['separator'][] = 	"-";
$cfg['separator'][] = 	" -";
$cfg['separator'][] = 	", part";
$cfg['separator'][] = 	", pt";
$cfg['separator'][] = 	", vol";
$cfg['separator'][] = 	" part";
$cfg['separator'][] = 	" pt";
$cfg['separator'][] = 	" vol";
$cfg['separator'][] = 	", część ";
$cfg['separator'][] = 	" część ";
$cfg['separator'][] = 	" '";
$cfg['separator'][] = 	"?";
$cfg['separator'][] = 	"!";
$cfg['separator'][] = 	":";
$cfg['separator'][] = 	" 1";
$cfg['separator'][] = 	" 2";
$cfg['separator'][] = 	" 3";
$cfg['separator'][] = 	" 4";
$cfg['separator'][] = 	" 5";
$cfg['separator'][] = 	" One";
$cfg['separator'][] = 	" Two";
$cfg['separator'][] = 	" Three";
$cfg['separator'][] = 	" Four";
//$cfg['separator'][] = 	" I";
$cfg['separator'][] = 	" II";
$cfg['separator'][] = 	" III";
$cfg['separator'][] = 	" IV";
//$cfg['separator'][] = 	" V";
$cfg['separator'][] = 	" VI";
$cfg['separator'][] = 	" VII";
$cfg['separator'][] = 	" VIII";
$cfg['separator'][] = 	" IX";
$cfg['separator'][] = 	" aka ";





//  +------------------------------------------------------------------------+
//  | Multiple artists                                                       |
//  +------------------------------------------------------------------------+
//  | Separator for multiple artist (works like 'ft.', 'feat.', etc)         |
//  +------------------------------------------------------------------------+
unset($cfg['artist_separator']);

$cfg['artist_separator'][] = 	" i ";
$cfg['artist_separator'][] = 	"; ";
$cfg['artist_separator'][] = 	" & ";
$cfg['artist_separator'][] = 	" and ";
$cfg['artist_separator'][] = 	" with ";
$cfg['artist_separator'][] = 	" feat. ";
$cfg['artist_separator'][] = 	" ft. ";
$cfg['artist_separator'][] = 	" Featuring ";
$cfg['artist_separator'][] = 	" + ";
//$cfg['artist_separator'][] = 	", ";




//  +------------------------------------------------------------------------+
//  | Default Favorites                                                      |
//  +------------------------------------------------------------------------+
//  | Name and Description for playlist with tracks marked with star         |
//  +------------------------------------------------------------------------+

$cfg['favorite_name'] 					= 'Favorites';
$cfg['favorite_comment'] 				= 'My favorites tracks';




//  +------------------------------------------------------------------------+
//  | Lyrics search string                                                   |
//  +------------------------------------------------------------------------+
//  | Additional query added to Google search string:                        |
//  | https://www.google.com/search?q={track artist}+{title}+{string below}  |
//  | example:                                                               |
//  | https://www.google.com/search?q=Duran+Duran+Big+Thing+lyrics           |
//  +------------------------------------------------------------------------+

$cfg['lyrics_search'] 					= 'lyrics';




//  +------------------------------------------------------------------------+
//  | Search lyrics in Musixmatch                                            |
//  +------------------------------------------------------------------------+
//  | Get free api key from https://developer.musixmatch.com/plans           |
//  | 'Free' plan is enough.                                                 |
//  +------------------------------------------------------------------------+

$cfg['musixmatch_api_key'] = "";



//  +------------------------------------------------------------------------+
//  | Misc artists folder                                                    |
//  +------------------------------------------------------------------------+
//  | Name of folder with misc tracks of misc artists                        |
//  +------------------------------------------------------------------------+

$cfg['misc_tracks_misc_artists_folder']	= 'Various Tracks';




//  +------------------------------------------------------------------------+
//  | Misc tracks folder                                                     |
//  +------------------------------------------------------------------------+
//  | Name of folder with misc tracks of one artist (must be different then  |
//  | above).                                                                |
//  +------------------------------------------------------------------------+

$cfg['misc_tracks_folder']				= 'Various Tracks of ';
 





//  +------------------------------------------------------------------------+
//  | Binary directory                                                       |
//  +------------------------------------------------------------------------+
//  | Use the native directory scheme with a trailing slash or backslash.    |
//  | ESCAPE THE LAST BACKSLASH WITH A BACKSLASH OR USE DOUBLE QUOTES!       |
//  |                                                                        |
//  | Windows:        'D:\Codec\\';                                          |
//  | Linux/Unix/OSX: '/usr/bin/';                                           |
//  |                 '/usr/local/bin/';                                     |
//  |                 '/opt/bin/';                                           |
//  |                 '/opt/local/bin/';                                     |
//  |                                                                        |
//  | BE AWARE THAT START CAN SUPPRESS ERROR MESSAGES ON SOME SETUPS!        |
//  | The process priority can be set with start or nice depending on the    |
//  | operating system:                                                      |
//  |                                                                        |
//  | Windows:        'start /b /low ...';                                   |
//  | Linux/Unix/OSX: 'nice -n 20 ...';                                      |
//  +------------------------------------------------------------------------+

//$cfg['bin_dir']                     = 'nice -n 20 /usr/bin/';
$cfg['bin_dir']                     = '/opt/bin/';




//  +------------------------------------------------------------------------+
//  | External storage (portable media player)                               |
//  +------------------------------------------------------------------------+
//  | Use a UNIX style directory scheme with a trailing slash.               |
//  |                                                                        |
//  | Windows:        'G:/MUSIC/';                                           |
//  | OSX:            '/Volumes/MP3 PLAYER/MUSIC/';                          |
//  | Linux/Unix:     '/mnt/MUSIC/';                                         |
//  |                                                                        |
//  | External storage features are only visible in netjukebox when the      |
//  | web server has access to the path set in $cfg['external_storage']      |
//  +------------------------------------------------------------------------+

$cfg['external_storage']            = '/share/Usb/';




//  +------------------------------------------------------------------------+
//  | Media extensions                                                       |
//  +------------------------------------------------------------------------+
unset($cfg['media_extension']);

// Audio
$cfg['media_extension'][]           = 'aac';
$cfg['media_extension'][]           = 'm4a';
$cfg['media_extension'][]           = 'm4b';
$cfg['media_extension'][]           = 'mp2';
$cfg['media_extension'][]           = 'mp3';
$cfg['media_extension'][]           = 'mpc';
$cfg['media_extension'][]           = 'ogg';
$cfg['media_extension'][]           = 'oga';
$cfg['media_extension'][]           = 'wma';
// Losless audio
$cfg['media_extension'][]           = 'ape';
$cfg['media_extension'][]           = 'aif';
$cfg['media_extension'][]           = 'aiff';
$cfg['media_extension'][]           = 'flac';
$cfg['media_extension'][]           = 'wv';
$cfg['media_extension'][]           = 'wav';
$cfg['media_extension'][]           = 'dsf';
$cfg['media_extension'][]           = 'dff';




//  +------------------------------------------------------------------------+
//  | Decode audio (for stream, download & record)                           |
//  +------------------------------------------------------------------------+
unset($cfg['decode_stdout']);

$cfg['decode_stdout']['aac']        = $cfg['bin_dir'] . 'faad -d -o - %source';
$cfg['decode_stdout']['ape']        = $cfg['bin_dir'] . 'mac %source - -d';
$cfg['decode_stdout']['flac']       = $cfg['bin_dir'] . 'flac --decode --totally-silent --stdout %source';
$cfg['decode_stdout']['m4a']        = $cfg['bin_dir'] . 'faad -d -o - %source';
$cfg['decode_stdout']['mp3']        = $cfg['bin_dir'] . 'lame --decode --silent %source -';
$cfg['decode_stdout']['mpc']        = $cfg['bin_dir'] . 'mpcdec %source -';									
$cfg['decode_stdout']['ogg']        = $cfg['bin_dir'] . 'oggdec --dither 3 --downmix --stdout %source';
$cfg['decode_stdout']['wma']        = $cfg['bin_dir'] . 'wmadec -w -q %source';
$cfg['decode_stdout']['wv']         = $cfg['bin_dir'] . 'wvunpack -q %source -';




//  +------------------------------------------------------------------------+
//  | Encode audio (for stream & download)                                   |
//  +------------------------------------------------------------------------+
//  | Tag writing is done by the getID3() library, attached picture is       |
//  | currently only supported with the id3v2.3 tag                          |
//  +------------------------------------------------------------------------+
unset($cfg['encode_name']);
unset($cfg['encode_mime_type']);
unset($cfg['encode_extension']);
unset($cfg['encode_stdout']);
unset($cfg['encode_file']);
unset($cfg['encode_bitrate']);
unset($cfg['encode_vbr']);
unset($cfg['tag_format']);
unset($cfg['tag_encoding']);
unset($cfg['tag_padding']);

$cfg['transcode_treshold']          = 150;

$cfg['encode_name'][]               = 'MP3 @ Low';
$cfg['encode_mime_type'][]          = 'audio/mpeg';
$cfg['encode_extension'][]          = 'mp3';
$cfg['encode_stdout'][]             = $cfg['bin_dir'] . 'lame --abr 64 --quiet --noreplaygain - -';
$cfg['encode_file'][]               = $cfg['bin_dir'] . 'lame --abr 64 --quiet --replaygain-accurate - %destination';
$cfg['encode_bitrate'][]            = 64000;
$cfg['encode_vbr'][] 	            = true;
$cfg['tag_format'][]                = 'id3v2.3';
$cfg['tag_encoding'][]              = 'UTF-8';
$cfg['tag_padding'][]               = 25600;

$cfg['encode_name'][]               = 'MP3 @ Portable';
$cfg['encode_mime_type'][]          = 'audio/mpeg';
$cfg['encode_extension'][]          = 'mp3';
$cfg['encode_stdout'][]             = $cfg['bin_dir'] . 'lame -V5 --quiet --noreplaygain - -';
$cfg['encode_file'][]               = $cfg['bin_dir'] . 'lame -V5 --quiet --replaygain-accurate - %destination';
$cfg['encode_bitrate'][]            = 128000;
$cfg['encode_vbr'][] 	            = true;
$cfg['tag_format'][]                = 'id3v2.3';
$cfg['tag_encoding'][]              = 'UTF-8';
$cfg['tag_padding'][]               = 25600;


$cfg['encode_name'][]               = 'MP3 @ HiFi';
$cfg['encode_mime_type'][]          = 'audio/mpeg';
$cfg['encode_extension'][]          = 'mp3';
$cfg['encode_stdout'][]             = $cfg['bin_dir'] . 'lame -V2 --quiet --noreplaygain - -';
$cfg['encode_file'][]               = $cfg['bin_dir'] . 'lame -V2 --quiet --replaygain-accurate - %destination';
$cfg['encode_file'][]               = $cfg['bin_dir'] . 'lame -V2 --quiet --replaygain-accurate - %destination';
$cfg['encode_bitrate'][]            = 190000;
$cfg['encode_vbr'][] 	            = true;
$cfg['tag_format'][]                = 'id3v2.3';
$cfg['tag_encoding'][]              = 'UTF-8';
$cfg['tag_padding'][]               = 25600;




//  +------------------------------------------------------------------------+
//  | Download album (with 7-Zip)                                            |
//  +------------------------------------------------------------------------+
//  | netjukebox always encodes the list file in UTF-8                       |
//  | The command -scsutf-8 is not working on all operating systems! This    |
//  | shouldn't be a problem because 7-Zip by default uses UTF-8 encoding    |
//  | for the list file.                                                     |
//  |                                                                        |
//  | http://www.7-zip.org                                                   |
//  +------------------------------------------------------------------------+

$cfg['download_album_mime_type']	= 'application/zip';
$cfg['download_album_extension']    = 'zip';
$cfg['download_album_cmd']          = $cfg['bin_dir'] . '7za a -tzip -mx0 -- %destination @%list';




//  +------------------------------------------------------------------------+
//  | Cache                                                                  |
//  +------------------------------------------------------------------------+
//  | Decoding to wav and creating zip files are relatively fast.            |
//  | When expire these files there will be more space for slower to         |
//  | transcode (mp3/ogg) files in the cache. It is advisable to set the     |
//  | expire time to at least the expected download or record time.          |
//  | When setting the expire value to 0 these files will not expire.        |
//  | The cache will maximum use 95% of the total available space.           |
//  +------------------------------------------------------------------------+

$cfg['cache_expire_wav']            = 3600;
$cfg['cache_expire_zip']            = 14400; // 3600 * 4




//  +------------------------------------------------------------------------+
//  | Image                                                                  |
//  +------------------------------------------------------------------------+
//  | $cfg['image_read_embedded'] = true;                                    |
//  | Read embeded APIC or PICTURE image from first media file if no other   |
//  | image is found.                                                        |
//  |                                                                        |
//  | $cfg['image_share'] = true;                                            |
//  | Share image for another forum or website.                              |
//  | See the webinterface for the BB-Code, HTML-Code or URL only code.      |
//  |                                                                        |
//  | $cfg['image_share_mode'] = 'mode';                                     |
//  | new: New added album.                                                  |
//  | played: Recently played or streamed album.                             |
//  |                                                                        |
//  | $cfg['image_front']                                                    |
//  | Semicolon seperated list of picture file names (jpg or png) that       |
//  | will be used for cover                                                 |
//  +------------------------------------------------------------------------+

$cfg['image_read_embedded']         = true;
$cfg['image_share']                 = true;
$cfg['image_share_mode']            = 'played';
$cfg['image_front']                 = 'folder;cover;front;Folder;Cover;Front'; // .jpg and .png
$cfg['image_back']                  = 'cd_back';  // .jpg and .png
$cfg['image_front_cover_treshold']  = 90000;      // 300 * 300




//  +------------------------------------------------------------------------+
//  | No album artist                                                        |
//  +------------------------------------------------------------------------+
unset($cfg['no_album_artist']);

$cfg['no_album_artist'][]           = 'compilation';
$cfg['no_album_artist'][]           = 'radio';
$cfg['no_album_artist'][]           = 'remix';
$cfg['no_album_artist'][]           = 'sampler';
$cfg['no_album_artist'][]           = 'singles';
$cfg['no_album_artist'][]           = 'various';




//  +------------------------------------------------------------------------+
//  | Internet search                                                        |
//  +------------------------------------------------------------------------+
unset($cfg['search_name']);
unset($cfg['search_url_artist']);
unset($cfg['search_url_album']);
unset($cfg['search_url_combined']);
unset($cfg['search_method']);
unset($cfg['search_charset']);

$cfg['search_name'][]               = 'AllMusic';
$cfg['search_url_artist'][]         = 'http://www.allmusic.com/search/artist/%artist';
$cfg['search_url_album'][]          = 'http://www.allmusic.com/search/album/%album';
$cfg['search_url_combined'][]       = '';
$cfg['search_method'][]             = 'get';
$cfg['search_charset'][]            = 'UTF-8';


$cfg['search_name'][]               = 'Discogs';
$cfg['search_url_artist'][]         = 'http://www.discogs.com/search/?q=%artist';
$cfg['search_url_album'][]          = 'http://www.discogs.com/search/?q=%album';
$cfg['search_url_combined'][]       = 'http://www.discogs.com/search/?q=%artist+%album';
$cfg['search_method'][]             = 'get';
$cfg['search_charset'][]            = 'UTF-8';

$cfg['search_name'][]               = 'Google';
$cfg['search_url_artist'][]         = 'http://www.google.com/search?q=%artist';
$cfg['search_url_album'][]          = 'http://www.google.com/search?q=%album';
$cfg['search_url_combined'][]       = 'http://www.google.com/search?q=%artist+%album';
$cfg['search_method'][]             = 'get';
$cfg['search_charset'][]            = 'UTF-8';

$cfg['search_name'][]               = 'Wikipedia';
$cfg['search_url_artist'][]         = 'http://www.google.com/search?q=%artist+site%3Awikipedia.org';
$cfg['search_url_album'][]          = 'http://www.google.com/search?q=%album+site%3Awikipedia.org';
$cfg['search_url_combined'][]       = 'http://www.google.com/search?q=%artist+%album+site%3Awikipedia.org';
$cfg['search_method'][]             = 'get';
$cfg['search_charset'][]            = 'UTF-8';


$cfg['search_name'][]               = 'Last.fm';
$cfg['search_url_artist'][]         = 'http://www.last.fm/search?m=artists&q=%artist';
$cfg['search_url_album'][]          = 'http://www.last.fm/search?m=albums&q=%album';
$cfg['search_url_combined'][]       = '';
$cfg['search_method'][]             = 'get';
$cfg['search_charset'][]            = 'UTF-8';

 
$cfg['search_name'][]               = 'Rate your music';
$cfg['search_url_artist'][]         = 'http://rateyourmusic.com/search?type=a&searchterm=%artist';
$cfg['search_url_album'][]          = 'http://rateyourmusic.com/search?type=l&searchterm=%album';
$cfg['search_url_combined'][]       = '';
$cfg['search_method'][]             = 'get';
$cfg['search_charset'][]            = 'UTF-8';
 
 
$cfg['search_name'][]               = 'MusicBrainz';
$cfg['search_url_artist'][]         = 'https://musicbrainz.org/search?query=%artist&type=artist&method=indexed';
$cfg['search_url_album'][]          = 'https://musicbrainz.org/search?query=%album&type=release_group&method=indexed
';
$cfg['search_url_combined'][]       = '';
$cfg['search_method'][]             = 'get';
$cfg['search_charset'][]            = 'UTF-8';


$cfg['search_name'][]               = 'Youtube';
$cfg['search_url_artist'][]         = 'https://www.youtube.com/results?search_query=%artist';
$cfg['search_url_album'][]          = 'https://www.youtube.com/results?search_query=%album';
$cfg['search_url_combined'][]       = '';
$cfg['search_method'][]             = 'get';
$cfg['search_charset'][]            = 'UTF-8';




//  +------------------------------------------------------------------------+
//  | Internet ip tools                                                      |
//  +------------------------------------------------------------------------+

$cfg['ip_tools']                    = 'http://www.infosniper.net/index.php?ip_address=%ip&map_source=1&overview_map=1&lang=1&map_type=1&zoom_level=5';




//  +------------------------------------------------------------------------+
//  | Auto suggest limit (search results)                                    |
//  +------------------------------------------------------------------------+

$cfg['autosuggest_limit']           = 25;




//  +------------------------------------------------------------------------+
//  | Update refresh time (in miliseconds)                                   |
//  +------------------------------------------------------------------------+

$cfg['update_refresh_time']         = 1000;




//  +------------------------------------------------------------------------+
//  | Date                                                                   |
//  +------------------------------------------------------------------------+
//  | The date_format syntax is identical to the PHP date() function.        |
//  +------------------------------------------------------------------------+

$cfg['date_format']                 = 'r';




//  +------------------------------------------------------------------------+
//  | Default characterset                                                   |
//  +------------------------------------------------------------------------+
//  | When leaving empty it will use the ISO-8859-1 characterset for Windows |
//  | and UTF-8 for all other operating systems.                             |
//  +------------------------------------------------------------------------+

$cfg['default_charset']             = '';
$cfg['default_filesystem_charset']  = '';




//  +------------------------------------------------------------------------+
//  | Allow deleting duplicate and error files.                              |
//  +------------------------------------------------------------------------+

$cfg['delete_file']                = false;




//  +------------------------------------------------------------------------+
//  | File system escape characters                                          |
//  +------------------------------------------------------------------------+
//  | DON'T DELETE THESE SETTINGS! Even if your operating system fully       |
//  | supported these caracters. Don't use forwardslash or backslash in the  |
//  | filenames                                                              |
//  +------------------------------------------------------------------------+

$cfg['escape_char']['?']            = '^';   // question mark
$cfg['escape_char'][':']            = ';';   // colon
$cfg['escape_char']['"']            = "''";  // double quote
$cfg['escape_char']['*']            = '%2A'; // asterisk
$cfg['escape_char']['<']            = '%3C'; // less than
$cfg['escape_char']['>']            = '%3E'; // greater than
$cfg['escape_char']['|']            = '%7C'; // pipe

// Client detection based on useragent
$cfg['client_char_limit']['#Macintosh|Mac OS X#i']  = array(':');
$cfg['client_char_limit']['#Windows|OS/2#i']        = array('"', '*', ':', '<', '>', '?', '|');
// Server detection based on PHP_OS
$cfg['server_char_limit']['#^Darwin#i']             = array(':');
$cfg['server_char_limit']['#^WIN#i']                = array('"', '*', ':', '<', '>', '?', '|');
// Album copy directory
$cfg['album_copy_char_limit']                       = array('"', '*', ':', '<', '>', '?', '|');




//  +------------------------------------------------------------------------+
//  | Authenticate                                                           |
//  +------------------------------------------------------------------------+

$cfg['login_delay']                 = 2000;




//  +------------------------------------------------------------------------+
//  | Admin message                                                          |
//  +------------------------------------------------------------------------+
//  | [br]                                                                   |
//  | [b]bold[/b]                                                            |
//  | [i]italic[/i]                                                          |
//  | [img]small_back.png[/img]                                              |
//  | [url]http://www.example.com[/url]                                      |
//  | [url=http://www.example.com]example[/url]                              |
//  | [email]info@example.com[/email]                                        |
//  | [list][*]first[*]second[/list]                                         |
//  +------------------------------------------------------------------------+

$cfg['admin_about_message']         = '';
$cfg['admin_login_message']         = '';




//  +------------------------------------------------------------------------+
//  | Offline message                                                        |
//  +------------------------------------------------------------------------+

$cfg['offline']                     = false;
$cfg['offline_message']             = '[b]This site is temporarily unavailable.[/b][br]We apologize for the inconvenience.';



//  +------------------------------------------------------------------------+
//  | Debug                                                                  |
//  +------------------------------------------------------------------------+
//  | $cfg['debug'] - since O!MPD 1.01 this option set to true               |
//  | logs update process to /tmp/update_log.txt file                        |
//  |                                                                        |
//  | $cfg['php_info'] - displays 'PHP information' in 'Configuration'       |
//  +------------------------------------------------------------------------+

$cfg['debug']                       = false;
$cfg['debug_memory']                = false;
$cfg['php_info']                    = true;




//  +------------------------------------------------------------------------+
//  | For testing some stuff (on my system only) - should be set to off      |
//  +------------------------------------------------------------------------+

$cfg['testing']                     = 'off';




//  +------------------------------------------------------------------------+
//  | Load file with local setings (if exists) overriding settings           |
//  | in this file                                                           |
//  +------------------------------------------------------------------------+

if(file_exists(NJB_HOME_DIR . 'include/config.local.inc.php') === true) {
	require_once(NJB_HOME_DIR . 'include/config.local.inc.php');
} 
?>
