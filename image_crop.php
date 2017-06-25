<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant                            |
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


$thumbnail = $_GET['thumbnail'];

$img = imagecreatefromjpeg($thumbnail);
if ($img) {
	//$size = min(imagesx($img), imagesy($img));
	$size = imagesy($img);
	$imgCrop = imagecrop($img, ['x' => (imagesx($img) - $size)/2, 'y' => 0, 'width' => $size, 'height' => $size]);
	if ($imgCrop !== FALSE) {
		return imagejpeg($imgCrop);
	}
}
header("Content-type: image/png");
imagepng(imageCreateFromPng('image/large_file_not_found.png'));

?>