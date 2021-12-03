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

global $cfg, $db, $t;

$action = $_POST["action"];

if ($action == 'verifyAccessToken'){
  echo safe_json_encode($t->verifyAccessToken());
}
elseif ($action == 'refreshAccessToken'){
  $res = refreshTidalAccessToken();
  echo safe_json_encode($res);
}
elseif ($action == 'getTidalDeviceCode'){
  $res = getTidalDeviceCode();
  echo safe_json_encode($res);
}
elseif ($action == 'checkAuthStatus'){
  $res = checkTidalAuthStatus();
  echo safe_json_encode($res);
}
elseif ($action == 'logoutTidal'){
  $res = logoutTidal();
  echo safe_json_encode($res);
}


?>

	
