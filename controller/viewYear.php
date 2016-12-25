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
//  | View year                                                              |
//  +------------------------------------------------------------------------+
function viewYear() {
    global $cfg, $db;
    
    authenticate('access_media');
    
    $sort = get('sort') == 'asc' ? 'asc' : 'desc';
    
    if ($sort == 'asc') {
        $order_query = 'ORDER BY year';
        $order_bitmap_year = '<span class="fa fa-sort-numeric-asc"></span>';
        $sort_year = 'desc';
    }
    else {
        // desc
        $order_query = 'ORDER BY year DESC';
        $order_bitmap_year = '<span class="fa fa-sort-numeric-desc"></span>';
        $sort_year = 'asc';
    }
    
    // formattedNavigator
    $nav            = array();
    $nav['name'][]  = 'Library';
    $nav['url'][]   = 'index.php';
    $nav['name'][]  = 'Year';
    require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
    <td class="space left"></td>
    <td width="80px"><a <?php echo ($order_bitmap_year == '<span class="typcn"></span>') ? '':'class="sort_selected"';?> href="index.php?action=viewYear&amp;sort=<?php echo $sort_year; ?>">Year&nbsp;<?php echo $order_bitmap_year; ?></a></td>   
    <td align="left" class="bar">Graph</td>
    <td align="center" width="130px">Album counts</td>
    <td class="right">&nbsp;</td>
</tr>

<?php
    $query = mysqli_query($db, 'SELECT COUNT(*) AS counter
        FROM album
        WHERE year
        GROUP BY year
        ORDER BY counter DESC');
    $album = mysqli_fetch_assoc($query);
    $max = $album['counter'];
    
    $query = mysqli_query($db, 'SELECT COUNT(discs) AS albums, SUM(discs) AS discs FROM album');
    $album = mysqli_fetch_assoc($query);
    $all = $album['albums'];
    
    $i=0;
    $query = mysqli_query($db, 'SELECT album,
        COUNT(*) AS counter
        FROM album
        WHERE year is null');
    $album = mysqli_fetch_assoc($query);
    $yearNULL = $album['counter'];
    if ($yearNULL > 0) {
?>
<tr class="<?php echo ($i++ & 1) ? 'year' : 'year'; ?> mouseover">
    <td></td>
    <td><a href="index.php?action=view2&amp;year=Unknown">Unknown</a></td>
    <td class="bar" style="cursor: pointer;" onClick="window.location.href='<?php echo NJB_HOME_URL ?>index.php?action=view2&amp;year=Unknown';"><div class="out"><div id="yNULL" style="width: 0px;" class="in"></div></div></td>
    <td align="center"><?php echo $album['counter']; ?> (<?php echo  round($album['counter'] / $all * 100, 1); ?>%)</td>
    <td> </td>
    
</tr>
<?php   
    }
    $i=1;
    $query = mysqli_query($db, 'SELECT year,
        COUNT(*) AS counter
        FROM album
        WHERE year
        GROUP BY year ' . $order_query);
    while ($max && $album = mysqli_fetch_assoc($query)) {
?>
<tr class="<?php echo ($i++ & 1) ? 'year' : 'year'; ?> mouseover">
    <td></td>
    <td><a href="index.php?action=view2&amp;year=<?php echo $album['year']; ?>"><?php echo $album['year']; ?></a></td>
    <td class="bar" style="cursor: pointer;" onClick="window.location.href='<?php echo NJB_HOME_URL ?>index.php?action=view2&amp;year=<?php echo $album['year']; ?>';"><div class="out"><div id="y<?php echo $album['year']; ?>" style="width: 0px;" class="in"></div></div></td>
    <td align="center"><?php echo $album['counter']; ?> (<?php echo  round($album['counter'] / $all * 100, 1); ?>%)</td>
    <td> </td>
    
</tr>
<?php
    }
    $query = mysqli_query($db, 'SELECT year,
        COUNT(*) AS counter
        FROM album
        WHERE year
        GROUP BY year ' . $order_query);
        
    echo '</table>' . "\n";
    echo '<script type="text/javascript">' . "\n";
    echo 'function setYearBar() {' . "\n";
    if ($yearNULL>0) {
    echo 'document.getElementById(\'yNULL\').style.width="' . round($yearNULL / $max * 200) . 'px";' . "\n";}
    while ($max && $album = mysqli_fetch_assoc($query)) {
    //echo floor($album['counter'] / $max_played['counter'] * 100) . ' * 1/100 * $(\'#bar-popularity-out\').width() + \'px\';' . "\n";
        
        echo 'document.getElementById(\'y' . $album['year'] .'\').style.width="' . round($album['counter'] / $max * 200) . 'px";' . "\n";
        //echo '$(\'#y'. $album['year'] .'\').transition({ width: \'' . round($album['counter'] / $max * 200) .  'px\', duration: 2000 });' . "\n";
        }
    echo '}' . "\n";
    echo 'window.onload = function () {' . "\n";
    echo 'setYearBar();' . "\n";
    echo '};' . "\n";
    echo '</script>' . "\n";
    
    require_once('include/footer.inc.php');
}
