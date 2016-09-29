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




//  +------------------------------------------------------------------------+
//  | Stream file                                                            |
//  +------------------------------------------------------------------------+
function streamFile($file, $mime_type, $content_disposition = '', $filename = '', $etag = '') {
	ini_set('zlib.output_compression', 'off');
	ini_set('max_execution_time', 0);
	
	$filename	= str_replace('"', '\"', $filename); // Needed for double quoted content disposition
	$filesize	= filesize($file);
	$filemtime	= filemtime($file);
	$etag 		= ($etag == '') ? '"' . md5($filesize . '-' . $filemtime) . '"' : $etag;
	
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
		header('HTTP/1.1 304 Not Modified');
		header('ETag: ' . $etag);
		exit();
	}
	
	if (isset($_SERVER['HTTP_RANGE']) && isset($_SERVER['HTTP_IF_RANGE']) && $_SERVER['HTTP_IF_RANGE'] != $etag) {
		header('HTTP/1.1 412 Precondition Failed');
		exit();
	}
	
	if (isset($_SERVER['HTTP_RANGE']) && preg_match('#bytes=([0-9]*)-([0-9]*)#', $_SERVER['HTTP_RANGE'], $match)) {
		// Some range examples:
		// $filesize = 1000;
		// bytes=600- or bytes=-400 or bytes=600-999
		// header('Content-Range: bytes 600-999/1000');
		// header('Content-Length: 400');
		
		$range_start	= $match[1];
		$range_end   	= $match[2];
		
		if ($range_start >= 0 && $range_end == '') {
			$range_end		= $filesize - 1;
		}
		elseif ($range_start == '' && $range_end >= 0) {
			$range_start	= $filesize - $range_end;
			$range_end		= $filesize - 1;
		}
		
		if ($range_start == '' || $range_end == '' || $range_start < 0 || $range_start > $range_end || $range_end > $filesize - 1) {
	    	header('Status: 416 Requested Range Not Satisfiable');
	    	header('Content-Range: */' . $filesize);
			exit();
		}
		
		$length	= $range_end - $range_start + 1;
		
		header('HTTP/1.1 206 Partial Content');
		header('ETag: ' . $etag);
		header('Accept-Ranges: bytes');
		header('Content-Length: ' . $length);
		header('Content-Range: bytes ' . $range_start . '-' . $range_end . '/' . $filesize);
		header('Content-Type: ' . $mime_type);
		
		// Content-Disposition: attachment; filename="album.zip"
		// Content-Disposition: inline; filename="cover.pdf"
		if ($content_disposition != '' && $filename != '')
			header('Content-Disposition: ' . $content_disposition . '; filename="' . $filename . '"');
					
		$buffer		= 1024 * 8;
		$bytes_left	= $length;
		
		$filehandle = @fopen($file, 'rb') or exit();
		fseek($filehandle, $range_start);
		while ($bytes_left > 0 && !feof($filehandle)) {
			$read_bytes = ($bytes_left > $buffer) ? $buffer : $bytes_left;
			$bytes_left = $bytes_left - $read_bytes;
			echo fread($filehandle, $read_bytes);
		}
		fclose($filehandle);
	}
	else {
		header('ETag: ' . $etag);
		header('Accept-Ranges: bytes');
		header('Content-Length: ' . $filesize);
		header('Content-Type: ' . $mime_type);
		if ($content_disposition != '' && $filename != '')
			header('Content-Disposition: ' . $content_disposition . '; filename="' . $filename . '"');
		
		$filehandle = @fopen($file, 'rb') or exit();
		while (!feof($filehandle))
			echo fread($filehandle, 1024 * 8);
	}
}




//  +------------------------------------------------------------------------+
//  | Stream data                                                            |
//  +------------------------------------------------------------------------+
function streamData($data, $mime_type, $content_disposition = '', $filename = '', $etag = '') {
	ini_set('zlib.output_compression', 'off');
	ini_set('max_execution_time', 0);
	
	$filename	= str_replace('"', '\"', $filename); // Needed for double quoted content disposition
	$filesize	= strlen($data);
	$etag 		= ($etag == '') ? '"' . md5($data) . '"' : $etag;
	
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
		header('HTTP/1.1 304 Not Modified');
		header('ETag: ' . $etag);
		exit();
	}
	
	if (isset($_SERVER['HTTP_RANGE']) && isset($_SERVER['HTTP_IF_RANGE']) && $_SERVER['HTTP_IF_RANGE'] != $etag) {
		header('HTTP/1.1 412 Precondition Failed');
		exit();
	}
	
	if (isset($_SERVER['HTTP_RANGE']) && preg_match('#bytes=([0-9]*)-([0-9]*)#', $_SERVER['HTTP_RANGE'], $match)) {
		$range_start	= $match[1];
		$range_end   	= $match[2];
		
		if ($range_start >= 0 && $range_end == '') {
			$range_end		= $filesize - 1;
		}
		elseif ($range_start == '' && $range_end >= 0) {
			$range_start	= $filesize - $range_end;
			$range_end		= $filesize - 1;
		}
		
		if ($range_start == '' || $range_end == '' || $range_start < 0 || $range_start > $range_end || $range_end > $filesize - 1) {
	    	header('Status: 416 Requested Range Not Satisfiable');
	    	header('Content-Range: */' . $filesize);
			exit();
		}
		
		$length	= $range_end - $range_start + 1;
		
		header('HTTP/1.1 206 Partial Content');
		header('ETag: ' . $etag);
		header('Accept-Ranges: bytes');
		header('Content-Length: ' . $length);
		header('Content-Range: bytes ' . $range_start . '-' . $range_end . '/' . $filesize);
		header('Content-Type: ' . $mime_type);
		
		// Content-Disposition: attachment; filename="album.zip"
		// Content-Disposition: inline; filename="cover.pdf"
		if ($content_disposition != '' && $filename != '')
			header('Content-Disposition: ' . $content_disposition . '; filename="' . $filename . '"');
		
		echo substr($data, $range_start, $range_end);
	}
	else {
		header('ETag: ' . $etag);
		header('Accept-Ranges: bytes');
		header('Content-Length: ' . $filesize);
		header('Content-Type: ' . $mime_type);
		if ($content_disposition != '' && $filename != '')
			header('Content-Disposition: ' . $content_disposition . '; filename="' . $filename . '"');
		
		echo $data;
	}
}
?>