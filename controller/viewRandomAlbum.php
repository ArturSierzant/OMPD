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
//  | View random album                                                      |
//  +------------------------------------------------------------------------+
function viewRandomAlbum() {
    global $cfg, $db, $base_size, $spaces, $scroll_bar_correction, $tileSizePHP, $twig;
    authenticate('access_media');

    // formattedNavigator
    $nav            = array();
    $nav['name'][]  = 'Library';
    $nav['url'][]   = 'index.php';
    $nav['name'][]  = 'Random';

    // TODO: refacture to template based rendering of surrounding markup/header
    require_once('include/header.inc.php');

    $base       = (cookie('netjukebox_width') - 20) / ($base_size + 10);
    $colombs    = floor($base);
    $aval_width = (cookie('netjukebox_width') - 20 - $scroll_bar_correction) - ($colombs - 1) * $spaces;

    // variables used in twig template
    $vars = array(
        'colombs' => $colombs,
        'size' => floor($aval_width / $colombs),
        'albums' => array(),
        'cfg' => $cfg
    );

    $blacklist = explode(',', $cfg['random_blacklist']);
    $blacklist = '"' . implode('","', $blacklist) . '"';
    $query = '
        SELECT artist_alphabetic, album, genre_id, year, month, image_id, album_id
        FROM album
        WHERE genre_id = "" OR genre_id NOT IN (' . $blacklist . ')
        ORDER BY RAND()
        LIMIT ' . (int) $colombs * 2;
    $result = $db->query($query);

    while ($album = $result->fetch_assoc()) {
        $vars['albums'][] = $album;
    }
    echo $twig->render('randomAlbum.htm', $vars);

    // TODO: refacture to template based rendering of surrounding markup/header
    require_once('include/footer.inc.php');
}
