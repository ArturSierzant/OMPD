<?php

//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2019 Artur Sierzant                            |
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
//  | tag properties                                                         |
//  +------------------------------------------------------------------------+
function parseTrackArtist($data) {
    if (isset($data['comments']['artist'][0])) {
        return $data['comments']['artist'][0];
    }
    return 'Unknown TrackArtist';
}

function parseAlbumTitle($data) {
    if(isset($data['comments']['album'][0])) {
        return $data['comments']['album'][0];
    }
    return 'Unknown Album Title';
}

function parseTrackTitle($data) {
    if(isset($data['comments']['title'][0])) {
        return $data['comments']['title'][0];
    }
    return 'Unknown Title';
}

function parseStyle($data, $genre) {
		global $cfg;
		$style = $genre;
		$genre_separator = 'ompd_genre_ompd';
    if (isset($data['tags']['id3v2']['style'][0])) {
				$style_array = $data['tags']['id3v2']['style'];
        foreach($style_array as $g){
					if ($style == '') {
						$style = $g;
					}
					else{
						$style = $style . $genre_separator . $g;		
					}
				}
				$style = str_replace($cfg['multigenre_separator'],$genre_separator,$style);
				return preg_replace('/' . $genre_separator . '$/', '', $style);
    }
    if (isset($data['tags']['vorbiscomment']['style'][0])) {
				$style_array = $data['tags']['vorbiscomment']['style'];
        foreach($style_array as $g){
					if ($style == '') {
						$style = $g;
					}
					else{
						$style = $style . $genre_separator . $g;		
					}
				}
				$style = str_replace($cfg['multigenre_separator'],$genre_separator,$style);
				return preg_replace('/' . $genre_separator . '$/', '', $style);
    }
		if (isset($data['comments']['style'][0])) {
				$style = str_replace($cfg['multigenre_separator'],$genre_separator,$data['comments']['style'][0]);
				return preg_replace('/' . $genre_separator . '$/', '', $style);
    }
    return $genre;
}

function parseGenre($data) {
		global $cfg;
		$genre = '';
		$style = '';
		$genre_separator = 'ompd_genre_ompd';
    if (isset($data['tags']['id3v2']['genre'][0])) {
				$genre_array = $data['tags']['id3v2']['genre'];
        foreach($genre_array as $g){
					if ($genre == '') {
						$genre = $g;
					}
					else{
						$genre = $genre . $genre_separator . $g;		
					}
				}
				$genre = str_replace($cfg['multigenre_separator'],$genre_separator,$genre);
				$genre = preg_replace('/' . $genre_separator . '$/', '', $genre);
				if ($cfg['style_enable']) {
					$genre = parseStyle($data, $genre);
				}
				return $genre;
    }
    if (isset($data['tags']['vorbiscomment']['genre'][0])) {
				$genre_array = $data['tags']['vorbiscomment']['genre'];
        foreach($genre_array as $g){
					if ($genre == '') {
						$genre = $g;
					}
					else{
						$genre = $genre . $genre_separator . $g;		
					}
				}
				$genre = str_replace($cfg['multigenre_separator'],$genre_separator,$genre);
				$genre = preg_replace('/' . $genre_separator . '$/', '', $genre);
				if ($cfg['style_enable']) {
					$genre = parseStyle($data, $genre);
				}
				return $genre;
    }
		if (isset($data['comments']['genre'][0])) {
				$genre = str_replace($cfg['multigenre_separator'],$genre_separator,$data['comments']['genre'][0]);
				$genre = preg_replace('/' . $genre_separator . '$/', '', $genre);
				if ($cfg['style_enable']) {
					$genre = parseStyle($data, $genre);
				}
				return $genre;
    }
		
		return 'Unknown Genre';
 
}

function parseMultiGenreId($genre_id){
	global $db;
	$album_genres = array();
	$genres = explode(';',$genre_id);
	$where = '';
	foreach ($genres as $g){
		$where = ($where == '') ? ' genre_id LIKE "' . $g . '"' : $where . ' OR genre_id LIKE "' . $g . '"';
	}
	$query = mysqli_query($db,'SELECT genre, genre_id FROM genre WHERE ' . $where . ' ORDER BY genre');
	while ($genre = mysqli_fetch_assoc($query)){
		$album_genres[$genre['genre_id']] = $genre['genre'];
	}
	return $album_genres;
}

function parseMultiGenre($genre){
	global $db;
	$album_genres = array();
	$genres = explode('ompd_genre_ompd',$genre);
	$where = '';
	foreach ($genres as $g){
		$where = ($where == '') ? ' genre LIKE "' . $g . '"' : $where . ' OR genre LIKE "' . $g . '"';
	}
	$query = mysqli_query($db,'SELECT genre, genre_id FROM genre WHERE ' . $where . ' ORDER BY genre');
	while ($genre = mysqli_fetch_assoc($query)){
		$album_genres[$genre['genre_id']] = $genre['genre'];
	}
	return $album_genres;
}

function parseTrackNumber($data) {
    if (isset($data['comments']['track'][0])) {
        return postProcessTrackNumber($data['comments']['track'][0]);
    }
    if (isset($data['comments']['tracknumber'][0])) {
        return postProcessTrackNumber($data['comments']['tracknumber'][0]);
    }
    if (isset($data['comments']['track_number'][0])) {
        return postProcessTrackNumber($data['comments']['track_number'][0]);
    }
    // TODO: handling NULL-values when building the query. for now return a string
    return 'NULL';
}

function postProcessTrackNumber($numberString) {
    //support for track_number in form: 01/11
    $numbers = explode("/", $numberString);
    return $numbers[0];
}

function parseDiscNumber($data) {
    if (isset($data['comments']['part_of_a_set'][0])) {
        return postProcessDiscNumber($data['comments']['part_of_a_set'][0]);
    }
		if (isset($data['comments']['discnumber'][0])) {
        return postProcessDiscNumber($data['comments']['discnumber'][0]);
    }
		//support for discnumber in tracknumber, i.e. 101 -> CD#1, 201 -> CD#2
		$track_number = parseTrackNumber($data);
		if (strlen($track_number) > 2 && $track_number != 'NULL' ) {
				return substr(parseTrackNumber($data), 0, strlen(parseTrackNumber($data)) - 2);
		}
    
    return '1';
}

function postProcessDiscNumber($numberString) {
    //support for part_of_a_set/discnumber in form: 01/02
    $numbers = explode("/", $numberString);
    return $numbers[0];
}

function parseYear($data) {
	//for FLAC:
    if (isset($data['comments']['originalyear'][0])) {
        return postProcessYear($data['comments']['originalyear'][0]);
    }
	if (isset($data['comments']['originaldate'][0])) {
        return postProcessYear($data['comments']['originaldate'][0]);
    }
	if (isset($data['comments']['origyear'][0])) {
        return postProcessYear($data['comments']['origyear'][0]);
    }
	//for mp3:
	if(isset($data['tags']['id3v2']['text']['originalyear'])) {
        return intval($data['tags']['id3v2']['text']['originalyear']);
    }
	if(isset($data['tags']['id3v2']['text']['ORIGINALYEAR'])) {
        return intval($data['tags']['id3v2']['text']['ORIGINALYEAR']);
    }
	if (isset($data['comments']['original_year'][0])) {
        return postProcessYear($data['comments']['original_year'][0]);
    }
	if (isset($data['comments']['original_release_time'][0])) {
        return postProcessYear($data['comments']['original_release_time'][0]);
    }
	//common:
	if (isset($data['comments']['year'][0])) {
        return postProcessYear($data['comments']['year'][0]);
    }
    if (isset($data['comments']['date'][0])) {
        return postProcessYear($data['comments']['date'][0]);
    }
    if (isset($data['comments']['creation_date'][0])) {
        return postProcessYear($data['comments']['creation_date'][0]);
    }
    // TODO: handling NULL-values when building the query. for now return a string
    return 'NULL';
}

function postProcessYear($yearString) {
    if (preg_match('#[1][9][0-9]{2}|[2][0-9]{3}#', $yearString, $match)) {
        $yearString = $match[0];
		return intval($yearString);
    }
	return $yearString;
}

function parseComment($data) {
    if(isset($data['comments']['comment']) === FALSE) {
        return '';
    }
    if(is_array($data['comments']['comment']) === FALSE) {
        return '';
    }
    $commentsArray = array_values($data['comments']['comment']);
    if(isset($commentsArray[0])) {
        return $commentsArray[0];
    }
    return '';
}

function parseComposer($data) {
    if (isset($data['comments']['composer'][0])) {
        return $data['comments']['composer'][0];
    }
    return '';
}


// TODO: this function is currently not used but removed from old fileInfo() code-mess
// consider to make use of it within fileStructure() or whereelse needed
function parseAlbumArtist($data) {
    if (isset($data['comments']['albumartist'][0])) {
        return $data['comments']['albumartist'][0];
    }
    if (isset($data['comments']['band'][0])) {
        return $data['comments']['band'][0];
    }
    return 'Unknown AlbumArtist';
}




function parseMimeType($data) {
    if(isset($data['mime_type'])) {
        return $data['mime_type'];
    }
    return 'application/octet-stream';
}

function parseError($data) {
    //if (!empty($data['error'])) {
    if (isset($data['error'])) {
				return implode('<br>', $data['error']);
    }
    return '';
}

//  +------------------------------------------------------------------------+
//  | audio tech properties                                                  |
//  +------------------------------------------------------------------------+
function parseMiliseconds($data) {
    if(isset($data['playtime_seconds'])) {
        return round($data['playtime_seconds'] * 1000);
    }
    return 0;
}

function parseAudioBitRate($data) {
    if(isset($data['audio']['bitrate'])) {
        return round($data['audio']['bitrate']); // integer in database
    }
    return 0;
}

function parseAudioBitRateMode($data) {
    if(isset($data['audio']['bitrate_mode'])) {
        return $data['audio']['bitrate_mode'];
    }
    return '';
}

function parseAudioBitsPerSample($data) {
    if(isset($data['audio']['bits_per_sample'])) {
        return $data['audio']['bits_per_sample'];
    }
    return 16;
}

function parseAudioSampleRate($data) {
    if(isset($data['audio']['sample_rate'])) {
        return $data['audio']['sample_rate'];
    }
    return 44100;
}

function parseAudioChannels($data) {
    if(isset($data['audio']['channels'])) {
        return $data['audio']['channels'];
    }
    return 2;
}

function parseAudioLossless($data) {
    if(empty($data['audio']['lossless']) == false) {
        return 1;
    }
    return 0;
}

function parseAudioCompressionRatio($data) {
    if(isset($data['audio']['compression_ratio'])) {
        return $data['audio']['compression_ratio'];
    }
    return 0;
}

function parseAudioDataformat($data) {
    if(isset($data['audio']['dataformat'])) {
        return $data['audio']['dataformat'];
    }
    return '';
}

function parseAudioEncoder($data) {
    if(isset($data['audio']['encoder'])) {
        return $data['audio']['encoder'];
    }
    return 'Unknown encoder';
}

function parseAudioProfile($data) {
    if(parseAudioLossless($data) === 1) {
        return (parseAudioCompressionRatio($data) == 1)
            ? 'Lossless'
            : 'Lossless compression';
    }
    if(isset($data['aac']['header']['profile_text'])) {
        return $data['aac']['header']['profile_text'];
    }
    if(isset($data['mpc']['header']['profile'])) {
        return $data['mpc']['header']['profile'];
    }
    return parseAudioBitRateMode($data) . ' ' . round(parseAudioBitRate($data) / 1000, 1) . ' kbps';
}

function parseAudioDynamicRange($data) {
    if(isset($data['comments']['dynamic range'][0])) {
        return intval($data['comments']['dynamic range'][0]);
    }
    if(isset($data['tags']['id3v2']['text']['DYNAMIC RANGE'])) {
        return intval($data['tags']['id3v2']['text']['DYNAMIC RANGE']);
    }
    // TODO: handling NULL-values when building the query. for now return a string
    return 'NULL';
}

function parseAlbumDynamicRange($data) {
    if(isset($data['comments']['album dynamic range'][0])) {
        return intval($data['comments']['album dynamic range'][0]);
    }
    if(isset($data['tags']['id3v2']['text']['ALBUM DYNAMIC RANGE'])) {
        return intval($data['tags']['id3v2']['text']['ALBUM DYNAMIC RANGE']);
    }
    // TODO: handling NULL-values when building the query. for now return a string
    return 'NULL';
}


//  +------------------------------------------------------------------------+
//  | video tech properties                                                  |
//  +------------------------------------------------------------------------+
function parseVideoDataformat($data) {
    if(isset($data['video']['dataformat'])) {
        return $data['video']['dataformat'];
    }
    return '';
}

function parseVideoCodec($data) {
    if(isset($data['video']['codec'])) {
        return $data['video']['codec'];
    }
    return 'Unknown codec';
}

function parseVideoResolutionX($data) {
    if(isset($data['video']['resolution_x'])) {
        return intval($data['video']['resolution_x']);
    }
    return 0;
}

function parseVideoResolutionY($data) {
    if(isset($data['video']['resolution_y'])) {
        return intval($data['video']['resolution_y']);
    }
    return 0;
}

function parseVideoFrameRate($data) {
    if(isset($data['video']['frame_rate'])) {
        return intval($data['video']['frame_rate']);
    }
    return 0;
}

