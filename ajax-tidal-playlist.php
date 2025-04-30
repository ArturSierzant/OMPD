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
require_once('include/play.inc.php');
global $cfg, $db, $t;
authenticate('access_favorite');

$action = $_POST['action'];
$playlist_id = !empty($_POST['playlist_id']) ? $_POST['playlist_id'] : '';
$playlist_type = !empty($_POST['playlist_type']) ? $_POST['playlist_type'] : '';
$mixlist_id = !empty($_POST['mixlist_id']) ? $_POST['mixlist_id'] : '';

$data = array();

$query1=mysqli_query($db,'SELECT player.player_name as pl, player_host, player_port, player_pass FROM player, session WHERE (sid = BINARY "' . cookie('netjukebox_sid') . '") and player.player_id=session.player_id');
$session1 = mysqli_fetch_assoc($query1);
$cfg['player_host'] = $session1['player_host'];
$cfg['player_port'] = $session1['player_port'];
$cfg['player_pass'] = $session1['player_pass'];

$conn = $t->connect();

if ($conn === true){
  if ($playlist_id) {
    $trackList = $t->getPlaylistTracks($playlist_id);
  }
  if ($mixlist_id) {
    $trackList = $t->getMixlistTracks($mixlist_id);
  }

  echo tidalTracksList($trackList, 0, $playlist_type);
}
?>
<script>
addFavSubmenuActions();
setAnchorClick();
</script>
