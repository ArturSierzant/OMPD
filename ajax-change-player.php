<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2019 Artur Sierzant                            |
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
	$player = array();
	$player_id = (int) $_GET['player_id'];
	//$sid = $_GET['sid'];
	$sid = $_COOKIE['netjukebox_sid'];
	mysqli_query($db,'UPDATE session SET player_id="' . $player_id . '" WHERE
	sid="' . $sid . '"');
	$query = mysqli_query($db,'SELECT player_name, player_host, player_port FROM player WHERE player_id="' . $player_id . '"');
	$playerDB = mysqli_fetch_assoc($query);
	$player['player_id'] = $cfg['player_id'] = $player_id;
	$player['player_host'] = $cfg['player_host'] = $playerDB['player_host'];
	$player['player_port'] = $cfg['player_port'] = $playerDB['player_port'];
	$player['player'] = $playerDB['player_name']; 
	echo json_encode($player);
?>