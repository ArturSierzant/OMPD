<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2020 Artur Sierzant                            |
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

global $cfg, $db;
require_once('include/initialize.inc.php');
require_once('include/play.inc.php');

if ($cfg['player_type'] == NJB_MPD) {
	$data = array();
	
	$query1=mysqli_query($db,'SELECT player.player_name as pl, player_host, player_port, player_pass FROM player, session WHERE (sid = BINARY "' . cookie('netjukebox_sid') . '") and player.player_id=session.player_id');
	$session1 = mysqli_fetch_assoc($query1);
	$data['player'] = $session1['pl'];
	//$data['host'] = $session1['player_host'];
	$cfg['player_host'] = $data['host'] = $session1['player_host'];
	$cfg['player_port'] = $session1['player_port'];
	$cfg['player_pass'] = $session1['player_pass'];
	$status 	= mpdSilent('status');
	
	if ($status != false) {
		$data['volume']	= (int) $status['volume'];
	}
	else {
		$data['volume']	= -1;
	}
	
	// get mute volume
	if ($data['volume'] == 0) {
		$query	= mysqli_query($db,'SELECT mute_volume FROM player WHERE player_id = ' . (int) $cfg['player_id']);
		$temp	= mysqli_fetch_assoc($query);
		$data['volume'] = -$temp['mute_volume'];
	}
	
	echo safe_json_encode($data);	
}

	
?>
	
