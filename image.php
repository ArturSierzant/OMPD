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
//  | image.php                                                              |
//  +------------------------------------------------------------------------+

//error_reporting(-1);
//ini_set('display_errors', 'On');

require_once('include/initialize.inc.php');
require_once('include/stream.inc.php');

$image_id 	= get('image_id');
$track_id 	= get('track_id');
$quality	= get('quality') == 'hq' ? 'hq' : 'lq';
//$quality	= 'hq';
$image	 	= get('image');
$image_path	= get('image_path');
$mime	 	= get('mime');

if		(isset($image_id))		image($image_id, $quality, $track_id);
elseif	(isset($image))			resampleImage($image);
elseif	(isset($image_path))	streamImage($image_path, $mime);
elseif	($cfg['image_share'])	shareImage();
exit();




//  +------------------------------------------------------------------------+
//  | Image                                                                  |
//  +------------------------------------------------------------------------+
function image($image_id, $quality, $track_id) {
	global $cfg, $db;
	require_once('getid3/getid3/getid3.php');
	/* $query  = mysqli_query($db,'SELECT image, image_front FROM bitmap WHERE image_id = "' . mysqli_real_escape_string($db,$image_id) . '" LIMIT 1');
	$bitmap = mysqli_fetch_assoc($query) or imageError(); */
	
	if (!empty($track_id)) 
	$query  = mysqli_query($db,'SELECT bitmap.image, bitmap.image_front, track.relative_file, track.track_id, bitmap.image_id  FROM bitmap LEFT JOIN track on bitmap.album_id = track.album_id WHERE bitmap.image_id = "' . mysqli_real_escape_string($db,$image_id) . '" AND track.track_id = "' . mysqli_real_escape_string($db,$track_id) . '" LIMIT 1');

	else
	$query  = mysqli_query($db,'SELECT bitmap.image, bitmap.image_front, bitmap.image_id, track.relative_file  FROM bitmap LEFT JOIN track on bitmap.album_id = track.album_id WHERE bitmap.image_id = "' . mysqli_real_escape_string($db,$image_id) . '" LIMIT 1');
	
	$bitmap = mysqli_fetch_assoc($query) or imageError();
	
	$path_parts = pathinfo($bitmap['image_front']);
	$file_ext = $path_parts['extension'];
	
	//get embedded picture for misc tracks
	if ((!empty($track_id)) && ((strpos(strtolower($bitmap['relative_file']), strtolower($cfg['misc_tracks_folder'])) !== false) || (strpos(strtolower($bitmap['relative_file']), strtolower($cfg['misc_tracks_misc_artists_folder'])) !== false))) {
		// Initialize getID3
		$getID3 = new getID3;
		//initial settings for getID3:
		include 'include/getID3init.inc.php';
		$path2file = $cfg['media_dir'] . $bitmap['relative_file'];
		$getID3->analyze($path2file);
		
		if (isset($getID3->info['error']) == false &&
			isset($getID3->info['comments']['picture'][0]['image_mime']) &&
			isset($getID3->info['comments']['picture'][0]['data']) &&
			($getID3->info['comments']['picture'][0]['image_mime'] == 'image/jpeg' || $getID3->info['comments']['picture'][0]['image_mime'] == 'image/png')) {
				$redImg = $getID3->info['comments']['picture'][0]['data'];
				header('Cache-Control: max-age=31536000');
				//streamData($redImg, 'image/jpeg', false, false, '"never_expire"');	
				streamData($redImg, $getID3->info['comments']['picture'][0]['image_mime'], false, false, '"never_expire"');	
		}
		else {
			/* $image = imagecreatefromjpeg(NJB_HOME_DIR . 'image/misc_image.jpg');
			header("Content-type: image/jpeg");
			imagejpeg($image);
			imagedestroy($image); */
			header('Cache-Control: max-age=31536000');
			streamData($bitmap['image'], 'image/jpeg', false, false, '"never_expire"');
		}
		
	}
	elseif ($bitmap['image_front'] == '') {
		header('Cache-Control: max-age=31536000');
		streamData($bitmap['image'], 'image/jpeg', false, false, '"never_expire"');
	}
	elseif ($quality == 'hq') {
		if (strpos($bitmap['image_front'],"misc_image.jpg") === false){ 
			$path2file = $cfg['media_dir'] . $bitmap['image_front'];
			if (is_file($path2file)) {
				if ($file_ext == 'jpg') {
					$image = imagecreatefromjpeg($path2file);
					header("Content-type: image/jpeg");
					imagejpeg($image);
					imagedestroy($image);
				}
				elseif ($file_ext == 'png') {
					$image = imagecreatefrompng($path2file);
					header("Content-type: image/png");
					imagepng($image);
					imagedestroy($image);
				}
				else imageError();
				
			}
			elseif (strpos($bitmap['image_id'],"no_image") !== false) {
				$image = imagecreatefromjpeg(NJB_HOME_DIR . 'image/no_image.jpg');
				header("Content-type: image/jpeg");
				imagejpeg($image);
				imagedestroy($image);
			}
			else {
				//$image = imagecreatefromjpeg('image/no_image.jpg');
				
				header('Cache-Control: max-age=31536000');
				streamData($bitmap['image'], 'image/jpeg', false, false, '"never_expire"');	
			}
		}
		else {
			if (is_file(NJB_HOME_DIR . 'image/misc_image.jpg')) {
				$image = imagecreatefromjpeg(NJB_HOME_DIR . 'image/misc_image.jpg');
				header("Content-type: image/jpeg");
				imagejpeg($image);
				imagedestroy($image);
			}
			else imageError();
		}			
	}
	else {
		/* $nFile = str_replace('folder.jpg', 'th_folder.jpg',$bitmap['image_front']);
		if (file_exists($cfg['media_dir'] . $nFile)) {
			$image = imagecreatefromjpeg($cfg['media_dir'] . $nFile);
			header("Content-type: image/jpeg");
			imagejpeg($image);
			imagedestroy($image);		
		}
		else {
		 */
		header('Cache-Control: max-age=31536000');
		streamData($bitmap['image'], 'image/jpeg', false, false, '"never_expire"');	
		//}
	}
	
}




//  +------------------------------------------------------------------------+
//  | Resample image                                                         |
//  +------------------------------------------------------------------------+
function resampleImage($image, $size = NJB_IMAGE_SIZE) {
	global $cfg, $db;
	authenticate('access_admin', true);
	
	if (substr($image, 0, 7) != 'http://' && substr($image, 0, 8) != 'https://')
		imageError();
	
	$extension = substr(strrchr($image, '.'), 1);
	$extension = strtolower($extension);
	
	if		($extension == 'jpg')	$src_image = @imageCreateFromJpeg($image) 	or imageError();
	elseif	($extension == 'jpeg')	$src_image = @imageCreateFromJpeg($image)	or imageError();
	elseif	($extension == 'png')	$src_image = @imageCreateFromPng($image)	or imageError();
	else {
		$imagesize = @getimagesize($image) or imageError();
		if ($imagesize[2] == IMAGETYPE_JPEG) {
			$src_image = @imageCreateFromJpeg($image) or imageError();
			$extension = 'jpg';
		}
		elseif ($imagesize[2] == IMAGETYPE_PNG) {
			$src_image = @imageCreateFromJpeg($image) or imageError();
			$extension = 'png';
		}
		else
			imageCreateFromPng('image/image_error.png');
		
	}
	
	if (($extension == 'jpg' || $extension == 'jpeg') && imageSX($src_image) == $size && imageSY($src_image) == $size) {
		$data = @file_get_contents($image) or imageError();
	}
	elseif (imageSY($src_image) / imageSX($src_image) <= 1) {
		// Crops from left and right to get a squire image.
		$sourceWidth		= imageSY($src_image);
		$sourceHeight		= imageSY($src_image);
		$sourceX			= round((imageSX($src_image) - imageSY($src_image)) / 2);
		$sourceY			= 0;
	}
	else {
		// Crops from top and bottom to get a squire image.
		$sourceWidth		= imageSX($src_image);
		$sourceHeight		= imageSX($src_image);
		$sourceX			= 0;
		$sourceY			= round((imageSY($src_image) - imageSX($src_image)) / 2);
	}
	if (isset($sourceWidth)) {
		$dst_image = ImageCreateTrueColor($size, $size);
		imageCopyResampled($dst_image, $src_image, 0, 0, $sourceX, $sourceY, $size, $size, $sourceWidth, $sourceHeight);
		ob_start();
		imageJpeg($dst_image, NULL, NJB_IMAGE_QUALITY);
		$data = ob_get_contents();
		ob_end_clean();
		imageDestroy($dst_image);
	}
	imageDestroy($src_image);
	
	header('Cache-Control: max-age=600');
	streamData($data, 'image/jpeg');
}




//  +------------------------------------------------------------------------+
//  | Stream image                                                           |
//  +------------------------------------------------------------------------+
function streamImage($image_path, $mime) {
	global $cfg, $db;
	if (file_exists($image_path)) {//this can also be a png or jpg
		$name = $image_path;
		$fp = fopen($name, 'rb');

		// send the right headers
		header("Content-Type: " . $mime);
		header("Content-Length: " . filesize($name));

		// dump the picture and stop the script
		fpassthru($fp);
		exit;
	}
}
	
	
//  +------------------------------------------------------------------------+
//  | Share image                                                            |
//  +------------------------------------------------------------------------+
function shareImage() {
	global $cfg, $db;
	
	if ($cfg['image_share_mode'] == 'played') {
		$query = mysqli_query($db,'SELECT image, artist, album, filesize, filemtime, album.album_id
			FROM counter, album, bitmap
			WHERE counter.flag <= 1
			AND counter.album_id = album.album_id
			AND counter.album_id = bitmap.album_id
			ORDER BY counter.time DESC
			LIMIT 1');
		$bitmap = mysqli_fetch_assoc($query);
		$text	=  'Recently played:';
	}
	else {
		$query	= mysqli_query($db,'SELECT image, artist, album, filesize, filemtime, album.album_id
			FROM album, bitmap 
			WHERE album.album_id = bitmap.album_id 
			ORDER BY album_add_time DESC
			LIMIT 1');
		$bitmap = mysqli_fetch_assoc($query);
		$text	=  'New album:';
		$cfg['image_share_mode'] = 'new';
	}
	
	$etag = '"' . md5($bitmap['album_id'] . $cfg['image_share_mode'] . $bitmap['filemtime'] . '-' . $bitmap['filesize'] . '-' . filemtime('image/share.png')) . '"';
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
		header('HTTP/1.1 304 Not Modified');
		header('ETag: ' . $etag);
		header('Cache-Control: max-age=5');
		exit();
	}
	
	// Background (253 x 52 pixel)
	$dst_image = imageCreateFromPng('image/share.png');
	
	// Image copy source NJB_IMAGE_SIZE x NJB_IMAGE_SIZE => 50x50
	$src_image = imageCreateFromString($bitmap['image']);
	imageCopyResampled($dst_image, $src_image, 1, 1, 0, 0, 50, 50, NJB_IMAGE_SIZE, NJB_IMAGE_SIZE);
	imageDestroy($src_image);
	
	// Text
	$font		= NJB_HOME_DIR . 'fonts/DejaVuSans.ttf';
	$font_color = imagecolorallocate($dst_image, 0, 0, 99);
	imagettftext($dst_image, 8, 0, 55, 13, $font_color, $font, $text);
	imagettftext($dst_image, 8, 0, 55, 30, $font_color, $font, $bitmap['artist']);
	imagettftext($dst_image, 8, 0, 55, 47, $font_color, $font, $bitmap['album']);
	
	// For to long text overwrite 4 pixels right margin
	$src_image = imageCreateFromPng('image/share.png');
	ImageCopy($dst_image, $src_image, 249, 0, 249, 0, 4, 52);
	imageDestroy($src_image);
	
	// Buffer data
	ob_start();
	ImagePng($dst_image);
	$data = ob_get_contents();
	ob_end_clean();
	
	imageDestroy($dst_image);
	
	header('Cache-Control: max-age=5');
	streamData($data, 'image/jpeg', false, false, $etag);
}




//  +------------------------------------------------------------------------+
//  | Image error                                                            |
//  +------------------------------------------------------------------------+
function imageError() {
	$etag = '"image_error_' . dechex(filemtime('image/image_error.png')) . '"';
	//$etag = "never_expire";
	streamData(file_get_contents('image/image_error.png'), 'image/png', false, false, $etag);
	exit();
}
?>