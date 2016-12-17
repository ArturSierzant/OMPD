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
//  | Home                                                                   |
//  +------------------------------------------------------------------------+
function viewHome() {
    global $cfg, $db;
    authenticate('access_media');
    genreNavigator('start');
    
    ?>
    
<script type="text/javascript">
<!--

var baseUrl = 'json.php?action=suggestAlbumArtist&artist=';

function showStatus() {
    alert ('ok');
}
    
function initialize() {
    //document.searchform.txt.name = 'artist';
    //document.searchform.txt.focus();
    //evaluateSuggest('');
}


function evaluateSuggest(list) {
    var suggest;
    if (list == '') {
        suggest = '<form action="">';
        suggest += '<input type="text" value="no suggestion" readonly class="autosugest_readonly">';
        suggest += '<\/form>';
    }
    else {
        suggest = '<form action="" name="suggest" id="suggest" onSubmit="suggestKeyStroke(1)" onClick="suggestKeyStroke(1)" onKeyDown="return suggestKeyStroke(event)">';
        suggest += '<select name="txt" size="6" class="autosugest">';
        for (var i in list)
            suggest += '<option value="' + list[i] + '">' + list[i] + '<\/option>';
        suggest += '<\/select><\/form>';
    }
    //document.getElementById('suggest').innerHTML = suggest;
}


function searchformKeyStroke(e) {
    var keyPressed;
    if (typeof e.keyCode != 'undefined')    keyPressed = e.keyCode;
    else if (typeof e.which != 'undefined') keyPressed = e.which;
    if (keyPressed == 40 && typeof document.suggest == 'object') // Down key
        {//document.suggest.txt.focus()
        };
}


function suggestKeyStroke(e) {
    var keyPressed;
    if (e == 1)                                 keyPressed = 13;
    else if (typeof e.keyCode != 'undefined')   keyPressed = e.keyCode;
    else if (typeof e.which != 'undefined')     keyPressed = e.which;
    if (keyPressed == 13 && document.suggest.txt.value != '') { // Enter key
        if (document.searchform.action.value == 'view1all')
            document.searchform.action.value = 'view3all';
        document.searchform.txt.value = document.suggest.txt.value;
        document.searchform.filter.value = 'exact';
        document.searchform.submit();
        return false;
    }
    else if (keyPressed == 38 && document.suggest.txt.selectedIndex == 0) { // Up key
        document.suggest.txt.selectedIndex = -1;
        document.searchform.txt.focus();
        return false;
    }
}
    

function selectTab(obj) {
    if (obj.id == 'albumartist') {
        document.getElementById('albumartist').className = 'tab_on';
        document.getElementById('trackartist').className = 'tab_off';
        document.getElementById('tracktitle').className  = 'tab_off';
        document.getElementById('albumtitle').className  = 'tab_off';
        document.getElementById('quicksearch').className  = 'tab_off';
        document.getElementById('searchform').style.visibility  = 'visible';
        document.getElementById('quicksearchform').style.visibility  = 'hidden';
        document.searchform.txt.select();
        document.searchform.txt.focus();
        document.searchform.txt.name = 'artist';
        document.searchform.action.value = 'view1';
        baseUrl = 'json.php?action=suggestAlbumArtist&artist=';
        ajaxRequest(baseUrl + <?php echo (NJB_DEFAULT_CHARSET == 'UTF-8') ? 'encodeURIComponent' : 'escape'; ?>(document.searchform.txt.value), evaluateSuggest);
    }
    else if (obj.id == 'albumtitle') {
        document.getElementById('albumartist').className = 'tab_off';
        document.getElementById('trackartist').className = 'tab_off';
        document.getElementById('tracktitle').className  = 'tab_off';
        document.getElementById('albumtitle').className  = 'tab_on';
        document.getElementById('quicksearch').className  = 'tab_off';
        document.getElementById('searchform').style.visibility  = 'visible';
        document.getElementById('quicksearchform').style.visibility  = 'hidden';
        document.searchform.txt.select();
        document.searchform.txt.focus();
        document.searchform.txt.name = 'title';
        document.searchform.action.value = 'view2';
        baseUrl = 'json.php?action=suggestAlbumTitle&title=';
        ajaxRequest(baseUrl + <?php echo (NJB_DEFAULT_CHARSET == 'UTF-8') ? 'encodeURIComponent' : 'escape'; ?>(document.searchform.txt.value), evaluateSuggest);
    }
    else if (obj.id == 'trackartist') {
        document.getElementById('albumartist').className = 'tab_off';
        document.getElementById('trackartist').className = 'tab_on';
        document.getElementById('tracktitle').className  = 'tab_off';
        document.getElementById('albumtitle').className  = 'tab_off';
        document.getElementById('quicksearch').className  = 'tab_off';
        document.getElementById('searchform').style.visibility  = 'visible';
        document.getElementById('quicksearchform').style.visibility  = 'hidden';
        document.searchform.txt.select();
        document.searchform.txt.focus();
        document.searchform.txt.name = 'artist';
        document.searchform.action.value = 'view1all';
        baseUrl = 'json.php?action=suggestTrackArtist&artist=';
        ajaxRequest(baseUrl + <?php echo (NJB_DEFAULT_CHARSET == 'UTF-8') ? 'encodeURIComponent' : 'escape'; ?>(document.searchform.txt.value), evaluateSuggest);
    }
    else if (obj.id == 'tracktitle') {
        document.getElementById('albumartist').className = 'tab_off';
        document.getElementById('trackartist').className = 'tab_off';
        document.getElementById('tracktitle').className  = 'tab_on';
        document.getElementById('albumtitle').className  = 'tab_off';
        document.getElementById('quicksearch').className  = 'tab_off';
        document.getElementById('searchform').style.visibility  = 'visible';
        document.getElementById('quicksearchform').style.visibility  = 'hidden';
        document.searchform.txt.select();
        document.searchform.txt.focus();
        document.searchform.txt.name = 'title';
        document.searchform.action.value = 'view3all';
        baseUrl = 'json.php?action=suggestTrackTitle&title=';
        ajaxRequest(baseUrl + <?php echo (NJB_DEFAULT_CHARSET == 'UTF-8') ? 'encodeURIComponent' : 'escape'; ?>(document.searchform.txt.value), evaluateSuggest);
    }
    
    else if (obj.id == 'quicksearch') {
        document.getElementById('albumartist').className = 'tab_off';
        document.getElementById('trackartist').className = 'tab_off';
        document.getElementById('tracktitle').className  = 'tab_off';
        document.getElementById('albumtitle').className  = 'tab_off';
        document.getElementById('quicksearch').className  = 'tab_on';
        document.getElementById('searchform').style.visibility  = 'hidden';
        document.getElementById('quicksearchform').style.visibility  = 'visible';
        document.searchform.txt.select();
        document.searchform.txt.focus();
        document.searchform.txt.name = 'title';
        document.searchform.action.value = 'view3all';
        baseUrl = 'json.php?action=suggestTrackTitle&title=';
        ajaxRequest(baseUrl + <?php echo (NJB_DEFAULT_CHARSET == 'UTF-8') ? 'encodeURIComponent' : 'escape'; ?>(document.searchform.txt.value), evaluateSuggest);
    }
}
//-->
</script>
<!-- <div style="height: 8px;"></div> -->
<div class="area">
<?php viewNewStartPage(); ?>  
</div>
<?php
    require_once('include/footer.inc.php');
}

