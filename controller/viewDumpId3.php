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
//  | View Dump ID3                                                          |
//  +------------------------------------------------------------------------+
function viewDumpId3() {
    global $cfg, $twig;

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


    $getID3 = new \getID3;
    $tagData = $getID3->analyze(get('filename'));
    \getid3_lib::CopyTagsToComments($tagData);
    \getid3_lib::ksort_recursive($tagData);

    $vars = array(
        'dumpvar' => $tagData,
        'getid3version' => $getID3->version(),
        'cfg' => $cfg
    );

    echo $twig->render('dumpId3.htm', $vars);

    // TODO: refacture to template based rendering of surrounding markup/footer
    require_once('include/footer.inc.php');
}
