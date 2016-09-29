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
//  | cover.php                                                              |
//  +------------------------------------------------------------------------+
//  | Update to PDFlib 5                                                     |
//  +------------------------------------------------------------------------+
//  | pdf_find_font         > pdf_load_font                                  |
//  | pdf_open_image_file   > pdf_load_image                                 |
//  | pdf_place_image       > pdf_fit_image                                  |
//  +------------------------------------------------------------------------+
//  | Update to PDFlib 6 (for future reference)                              |
//  +------------------------------------------------------------------------+
//  | pdf_open_file         > pdf_begin_document                             |
//  | pdf_begin_page        > pdf_begin_page_ext                             |
//  | pdf_show_boxed        > pdf_*_textflow                                 |
//  | pdf_close             > pdf_end_document                               |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
require_once('include/stream.inc.php');
authenticate('access_cover', true);

if (function_exists('pdf_new') == false)
	message(__FILE__, __LINE__, 'error', '[b]PDFlib not loaded[/b][list][*]Compile PHP with PDFlib support.[*]Or use a loadable module in the php.ini[/list]');

$album_id = get('album_id');

$query	= mysqli_query($db,'SELECT artist, album FROM album WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '"');
$album	= mysqli_fetch_assoc($query);

if ($album == false)
	message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]album_id not found in database');

$query	= mysqli_query($db,'SELECT image_front, image_back, image_front_width * image_front_height AS front_resolution, album_id
	FROM bitmap
	WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '"');
$bitmap	= mysqli_fetch_assoc($query);




//  +------------------------------------------------------------------------+
//  | Initialize PDF                                                         |
//  +------------------------------------------------------------------------+
$pdf = pdf_new();

if (pdf_get_value($pdf, 'major', 0) < 5) {
	pdf_close($pdf);
	message(__FILE__, __LINE__, 'error', '[b]netjukebox requires PDFlib 5 or later[/b]');
}

pdf_open_file($pdf, '');
$font = pdf_load_font($pdf, 'Helvetica', 'host', ''); // winansi, host, iso8859-1, unicode >> unicode and glyph id addressing not supported in PDFlib Lite!!! 
if (NJB_DEFAULT_CHARSET == 'UTF-8')
	pdf_set_parameter($pdf, 'textformat', 'utf8');

pdf_set_info($pdf, 'Creator', 'netjukebox ' . NJB_VERSION);
pdf_set_info($pdf, 'Title', $album['artist'] . ' - ' . $album['album']);

$width	= 210;			// A4
$height	= 297;			// A4
$scale	= 72 / 25.4;	// mm to dtp-point (1 point = 1/72 inch; 1 inch = 25.4 mm)

$hash_data = pdf_get_value($pdf, 'major', 0) . '-' . pdf_get_value($pdf, 'minor', 0) . '-' . NJB_VERSION;
$hash_data .= '-' . $album['artist'] . ' - ' . $album['album'] . '-' . $width . '-' . $height;

pdf_begin_page($pdf, $width * $scale, $height * $scale);
pdf_scale($pdf, $scale, $scale);
pdf_setlinewidth($pdf, .1);




//  +------------------------------------------------------------------------+
//  | PDF back cover                                                         |
//  +------------------------------------------------------------------------+
$x0 = 30;
$y0 = 22;
pdf_translate($pdf, $x0, $y0);

pdf_moveto($pdf, 0, -1);
pdf_lineto($pdf, 0, -11);
pdf_moveto($pdf, 6.5, -1);
pdf_lineto($pdf, 6.5, -11);
pdf_moveto($pdf, 144.5, -1);
pdf_lineto($pdf, 144.5, -11);
pdf_moveto($pdf, 151, -1);
pdf_lineto($pdf, 151, -11);
pdf_moveto($pdf, 0, 119);
pdf_lineto($pdf, 0, 129);
pdf_moveto($pdf, 6.5, 119);
pdf_lineto($pdf, 6.5, 129);
pdf_moveto($pdf, 144.5, 119);
pdf_lineto($pdf, 144.5, 129);
pdf_moveto($pdf, 151, 119);
pdf_lineto($pdf, 151, 129);
pdf_moveto($pdf, -11, 0);
pdf_lineto($pdf, -1 , 0);
pdf_moveto($pdf, -11, 118);
pdf_lineto($pdf, -1 , 118);
pdf_moveto($pdf, 152, 0);
pdf_lineto($pdf, 162, 0);
pdf_moveto($pdf, 152, 118);
pdf_lineto($pdf, 162, 118);
pdf_stroke($pdf);

if ($bitmap['image_back']) {
	$pdfdfimage = pdf_load_image($pdf, 'auto', $cfg['media_dir'] . $bitmap['image_back'], '');
	pdf_fit_image($pdf, $pdfdfimage, 0, 0, 'boxsize {151 118} position 0 fitmethod entire');
	$hash_data .= '-' . filesize($cfg['media_dir'] . $bitmap['image_back']) . '-' . filemtime($cfg['media_dir'] . $bitmap['image_back']);
}
else {
	$same_artist = false;
	$query = mysqli_query($db,'SELECT artist FROM track WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '" GROUP BY artist');
	if (mysqli_num_rows($query) == 1) 
		$same_artist = true;
	
	$text = '';
	$previous_disc = 1;
	$query = mysqli_query($db,'SELECT title, artist, disc FROM track WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '" ORDER BY relative_file');
	while ($track = mysqli_fetch_assoc($query)) {
		if ($previous_disc != $track['disc'])	$text .= "\n";
		if ($same_artist) 						$text .= $track['title'] . "\n";
		else 									$text .= $track['artist'] . ' - ' . $track['title'] . "\n";
		$previous_disc = $track['disc'];
	}
	// $text = iconv(NJB_DEFAULT_CHARSET, 'UTF-8', $text);
	pdf_setfont($pdf, $font, 3);
	pdf_show_boxed($pdf, $text, 6.5, 0, 138, 108, 'center', '');
	
	$query = mysqli_query($db,'SELECT artist, album FROM album WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '"');
	$album = mysqli_fetch_assoc($query);
	
	if (in_array(strtolower($album['artist']), $cfg['no_album_artist']))	$title = $album['album'];
	else																	$title = $album['artist'] . ' - ' . $album['album'];
	// $title = iconv(NJB_DEFAULT_CHARSET, 'UTF-8', $title);
	
	pdf_setfont($pdf, $font, 4);
	
	pdf_save($pdf);
	pdf_rotate($pdf, 90);
	pdf_set_text_pos($pdf, 2, -4.5); // y, -x
	pdf_show($pdf, $title);
	pdf_restore($pdf); // Restore rotate
	
	pdf_save($pdf);
	pdf_rotate($pdf, -90);
	pdf_set_text_pos($pdf, -116 , 151 - 4.5); // -y, x
	pdf_show($pdf, $title);
	pdf_restore($pdf); // Restore rotate
	
	$hash_data .= '-' . $text . '-' . $title;
}




//  +------------------------------------------------------------------------+
//  | PDF front cover                                                        |
//  +------------------------------------------------------------------------+
$x0 = 44 - $x0;
$y0 = 160 - $y0;
pdf_translate($pdf, $x0, $y0);

pdf_moveto($pdf, 0, -1);
pdf_lineto($pdf, 0, -11);
pdf_moveto($pdf, 121, -1);
pdf_lineto($pdf, 121, -11);
pdf_moveto($pdf, 0, 121);
pdf_lineto($pdf, 0, 131);
pdf_moveto($pdf, 121, 121);
pdf_lineto($pdf, 121, 131);
pdf_moveto($pdf, -1, 0);
pdf_lineto($pdf, -11, 0);
pdf_moveto($pdf, -1, 120);
pdf_lineto($pdf, -11, 120);
pdf_moveto($pdf, 122, 0);
pdf_lineto($pdf, 132, 0);
pdf_moveto($pdf, 122, 120);
pdf_lineto($pdf, 132, 120);
pdf_stroke($pdf);

if ($bitmap['front_resolution'] >= $cfg['image_front_cover_treshold']) {
	$pdfdfimage = pdf_load_image($pdf, 'auto', $cfg['media_dir'] . $bitmap['image_front'], '');
	pdf_fit_image($pdf, $pdfdfimage, 0, 0, 'boxsize {121 120} position {50 50} fitmethod slice');
	$hash_data .= '-' . filesize($cfg['media_dir'] . $bitmap['image_front']) . '-' . filemtime($cfg['media_dir'] . $bitmap['image_front']);
}




//  +------------------------------------------------------------------------+
//  | Close and download PDF                                                 |
//  +------------------------------------------------------------------------+
pdf_end_page($pdf);
pdf_close($pdf);
$data = pdf_get_buffer($pdf);
pdf_delete($pdf);

$filename = $album['artist'] . ' - ' . $album['album'] . '.pdf';
$filename = downloadFilename($filename);
$etag = '"' . md5($hash_data) . '"';

streamData($data, 'application/pdf', 'inline', $filename, $etag);
updateCounter($album_id, NJB_COUNTER_COVER);
?>
