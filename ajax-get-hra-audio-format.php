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

require_once('include/initialize.inc.php');
require_once('include/library.inc.php');

global $cfg, $db;

$album_id = $_POST["album_id"];

$data = array();

$h = new HraAPI;
$h->username = $cfg["hra_username"];
$h->password = $cfg["hra_password"];
if (NJB_WINDOWS) $h->fixSSLcertificate();
$conn = $h->connect();
if (!$conn){
	$data['return'] = $conn["return"];
	$data['response'] = $conn["error"];
	echo safe_json_encode($data);
	return;
}

$aq = $h->getAlbum(getHraId($album_id));
$data['audio_quality'] = $aq['data']['results']['tracks'][0]['format'];
$data['album_id'] = $album_id;
$data['audio_format'] = calculateAlbumFormat($data);
echo safe_json_encode($data);

?>

