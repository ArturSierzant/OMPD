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
//  | View Home                                                              |
//  +------------------------------------------------------------------------+
function viewHome() {
    global $cfg, $db, $base_size, $spaces, $scroll_bar_correction, $tileSizePHP, $twig;

    authenticate('access_media');
    genreNavigator('start');

    // TODO: refacture to template based rendering of surrounding markup/header
    // formattedNavigator
    $nav = array(
        'name' => array('Library'),
        'url'  => array('index.php'),
        'name' => array('New')
    );
    require_once('include/header.inc.php');

    $base       = (cookie('netjukebox_width') - 20) / ($base_size + 10);
    $colombs    = floor($base);
    $aval_width = (cookie('netjukebox_width') - 20 - $scroll_bar_correction) - ($colombs - 1) * $spaces;
    $vars = array(
        'size' => floor($aval_width / $colombs),
        'show_suggested' => false,
        'show_last_played' => $cfg['show_last_played'],
        'albums_lastplayed' => array(),
        'disc_count' => (int) $db->query('SELECT SUM(discs) AS disc_count FROM album;')->fetch_assoc()['disc_count'],
        'album_count' => (int) $db->query('SELECT COUNT(*) AS album_count FROM album WHERE album_add_time;')->fetch_assoc()['album_count'],
        'cfg' => $cfg
    );
    
    // is this used in paginator?
    $cfg['items_count'] = $vars['album_count'];


    if($vars['disc_count'] < 1) {
        // nothing more to do with empty database
        echo $twig->render('home/layout.htm', $vars);
        require_once('include/footer.inc.php');
        return;
    }

    $vars['show_suggested'] = $cfg['show_suggested'];

    if($cfg['show_last_played'] = true) {
        $vars['show_last_played'] = true;
        $query = '
            SELECT DISTINCT album.album_id, album.image_id, album.album, album.artist_alphabetic
            FROM album
            RIGHT JOIN
                (SELECT album_id, MAX(time) AS m_time
                 FROM counter
                 GROUP BY album_id
                 ORDER BY m_time DESC) as c
            ON c.album_id = album.album_id
            LIMIT 10
        ';
        $result = $db->query($query);
        while ($album = $result->fetch_assoc()) {
            // TODO: do we really need a different "size" for drawTile. there was some code with $tileSizePHP
            $vars['albums_lastplayed'][] = $album;
        }
    }

    $query = '
        SELECT *
        FROM album
        WHERE album_add_time
        ORDER BY album_add_time DESC, album DESC
        LIMIT ' . $cfg['max_items_per_page'];

    $result = $db->query($query);
    $mdTab = array();
    while ($album = $result->fetch_assoc()) {
        $multidisc_count = 0;
        if ($cfg['group_multidisc'] == true) {
            $md_indicator = striposa($album['album'], $cfg['multidisk_indicator']);
            if ($md_indicator !== false) {
                $md_ind_pos = stripos($album['album'], $md_indicator);
                $md_title = substr($album['album'], 0,  $md_ind_pos);
                $query = '
                    SELECT album, image_id, album_id
                    FROM album
                    WHERE
                        album LIKE "' . $db->real_escape_string($md_title) . '%"
                        AND artist = "' . $db->real_escape_string($album['artist']) . '"
                        AND album <> "' . $db->real_escape_string($album['album']) . '"
                    ORDER BY album;';
                $resultMd = $db->query($query);
                $multidisc_count = $resultMd->num_rows;
            }
        }
        if ($multidisc_count === 0) {
            // TODO: do we really need a different "size" for drawTile. there was some code with $tileSizePHP
            $vars['albums'][] = $album;
            continue;
        }
        if (in_array($md_title, $mdTab)) {
            //$album_count--; // TODO: variable $album_count seems to be unused at all!?
            continue;
        }
        $mdTab[] = $md_title;
        $album['multidisc'] = 'allDiscs';
        $vars['albums'][] = $album;
    }
    echo $twig->render('home/layout.htm', $vars);

    // TODO: refacture to template based rendering of surrounding markup/footer
    require_once('include/footer.inc.php');
}
