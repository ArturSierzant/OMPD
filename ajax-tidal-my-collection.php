<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015 Artur Sierzant                                 |
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
global $cfg, $db, $t;


$action = $_POST['action'];
$id = !empty($_POST['id']) ? $_POST['id'] : '';
$type = !empty($_POST['type']) ? $_POST['type'] : '';

$data = array();
$data["result"] = "error";

if ($action && $id && $type) {
  $conn = $t->connect();
  if ($conn === true){
    if ($action == 'add') {
      $res = $t->addToMyCollection($id, $type);
    }
    elseif ($action == 'remove') {
      $res = $t->removeFromMyCollection($id, $type);
    }
    if ($res){
      $data["result"] = $action . '_ok';
    }
  }
}
echo safe_json_encode($data);