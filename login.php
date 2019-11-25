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

global $cfg, $db;
require_once('include/initialize.inc.php');
$username = $_GET['username'];
$password = $_GET['password'];
$player_name = $_GET['player'];

$create_session = true;

$sid = cookie('netjukebox_sid');

if ($sid) {
	$query = mysqli_query($db, 'SELECT sign, seed FROM session WHERE sid = "' . mysqli_real_escape_string($db, $sid) . '"');
	if (mysqli_num_rows($query) > 0) {
		$session = mysqli_fetch_assoc($query);
		$sign = $session['sign'];
		$session_seed = $session['seed'];
		$create_session = false;
	}
}
if ($create_session) {
// Create new session
	$sid = randomKey();
	$sign = randomKey();
	$session_seed = randomKey();

	mysqli_query($db, 'INSERT INTO session (logged_in, create_time, ip, user_agent, sid, sign, seed) VALUES (
		0,
		' . (int) time() . ',
		"' . mysqli_real_escape_string($db, $_SERVER['REMOTE_ADDR']) . '",
		"' . mysqli_real_escape_string($db, $_SERVER['HTTP_USER_AGENT']) . '",
		"' . mysqli_real_escape_string($db, $sid) . '",
		"' . mysqli_real_escape_string($db, $sign) . '",
		"' . mysqli_real_escape_string($db, $session_seed) . '")');
}



$query		= mysqli_query($db, 'SELECT seed FROM user WHERE username = "' . mysqli_real_escape_string($db, $username) . '"');
$user 		= mysqli_fetch_assoc($query);
$fake_seed		= substr(hmacsha1($cfg['server_seed'], $username . 'NeZlFgqDoh9hc-BkczryQFIcpoBng3I_vXaWtOKS'), 0, 30);
$fake_seed		.= substr(hmacsha1($cfg['server_seed'], $username . 'g-FE6H0MJ1n0lNo2D7XLachV8WE-xmEcwsXNZqlQ'), 0, 30);
$fake_seed		= base64_encode(pack('H*', $fake_seed));
$fake_seed		= str_replace('+', '-', $fake_seed); // modified Base64 for URL
$fake_seed		= str_replace('/', '_', $fake_seed);

$hash1 = hmacsha1($password, ($user['seed'] == '') ? $fake_seed : $user['seed']);
$hash2 = hmacsha1(hmacsha1($password, $session_seed),$session_seed);

$query		= mysqli_query($db, 'SELECT player_id FROM player WHERE player_name = "' . mysqli_real_escape_string($db, $player_name) . '"');
$player 		= mysqli_fetch_assoc($query);

$player_id = $player['player_id'];

$url = 'index.php?sid=' . $sid . '&authenticate=validate&username=' . $username . '&password=' . $password . '&player_id=' . $player_id . '&hash1=' . $hash1 . '&hash2=' . $hash2 . '&sign=' . $sign;
if ($create_session) {
	header('Set-Cookie: netjukebox_sid = ' . $sid . '; Path=/; Max-Age = 31536000; samesite=strict');
}
//setcookie('netjukebox_sid', $sid, time() + 31536000, null, null, NJB_HTTPS, true);
header('Location: '.$url);

?>