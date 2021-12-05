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

global $cfg, $db;

$settings = $_POST["settings"];
$idx_tmp = 0; //temporary set index to 0 - change in future, when index is needed
$data = array();
$data['return'] = 1;

foreach($settings as $key=>$value) {
  $sql = "REPLACE INTO config (name, `index`, value) VALUES ('" . $db->real_escape_string($key) ."'," . $idx_tmp . ", '" . $db->real_escape_string($value) . "')";
  $res = mysqli_query($db, $sql);
  if ($res) {
    $data['return'] = 0;
  }
  else {
    break;
  }
  $data[$key] = $value;
}

echo json_encode($data);
?>