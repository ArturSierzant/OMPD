<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2021 Artur Sierzant                            |
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

	
// public: Settings
$getID3->encoding        			= 'UTF-8';	// CASE SENSITIVE! - i.e. (must be supported by iconv()) Examples:  ISO-8859-1  UTF-8  UTF-16  UTF-16BE
$getID3->encoding_id3v1  			= 'ISO-8859-1';	// Should always be 'ISO-8859-1', but some tags may be written in other encodings such as 'EUC-CN'
// public: Optional tag checks - disable for speed.
$getID3->option_tag_id3v1			= true;			// Read and process ID3v1 tags
$getID3->option_tag_id3v2			= true;		// Read and process ID3v2 tags
$getID3->option_tag_lyrics3			= true;		// Read and process Lyrics3 tags
$getID3->option_tag_apetag			= true;		// Read and process APE tags
$getID3->option_tags_process		= true;		// Copy tags to root key 'tags' and encode to $this->encoding
$getID3->option_tags_html			= false;		// Copy tags to root key 'tags_html' properly translated from various encodings to HTML entities
// public: Optional tag/comment calucations
$getID3->option_extra_info			= true;			// Calculate additional info such as bitrate, channelmode etc
// public: Optional handling of embedded attachments (e.g. images)
$getID3->option_save_attachments	= true;			// defaults to true (ATTACHMENTS_INLINE) for backward compatibility
// public: Optional calculations
$getID3->option_md5_data			= false;		// Get MD5 sum of data part - slow
$getID3->option_md5_data_source		= false;		// Use MD5 of source file if availble - only FLAC and OptimFROG
$getID3->option_sha1_data			= false;		// Get SHA1 sum of data part - slow
$getID3->option_max_2gb_check		= null;			// Check whether file is larger than 2 Gb and thus not supported by PHP
?>