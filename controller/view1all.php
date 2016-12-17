<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant                            |
//  | http://www.ompd.pl                                                     |
//  |                                                                        |
//  |                                                                        |
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
//  | View 1 all                                                             |
//  +------------------------------------------------------------------------+
function view1all() {
    global $cfg, $db;
    authenticate('access_media');
    require_once('include/header.inc.php');
    
    $artist     = get('artist');
    $filter     = get('filter');
    
    if ($artist == '') {
        $artist = 'All track artists';
        $filter = 'all';
    }
    
    if ($filter == 'all')       $query = mysqli_query($db, 'SELECT DISTINCT artist FROM track ORDER BY artist');
    elseif ($filter == 'smart') $query = mysqli_query($db, 'SELECT artist FROM track WHERE artist LIKE "%' . mysqli_real_escape_like($artist) . '%" OR artist SOUNDS LIKE "' . mysqli_real_escape_like($artist) . '" GROUP BY artist ORDER BY artist');
    else                        message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]filter');
    
    // formattedNavigator
    $nav            = array();
    $nav['name'][]  = 'Library';
    $nav['url'][]   = 'index.php';
    $nav['name'][]  = $artist;
    
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
    <td class="space"></td>
    <td>Artist</td>
    <td class="space"></td>
</tr>

<?php
    //$query = mysqli_query($db, 'SELECT DISTINCT artist FROM track ORDER BY artist');
    $i = 0;
    while ($track = mysqli_fetch_assoc($query)) { ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
    <td></td>
    <td><a href="index.php?action=view3all&amp;artist=<?php echo rawurlencode($track['artist']); ?>&amp;order=title"><?php echo html($track['artist']); ?></a></td>
    <td></td>
</tr>
<?php
}
echo '</table>' . "\n";
require_once('include/footer.inc.php');
}


