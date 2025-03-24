<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2021 Artur Sierzant                            |
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
//  | Radio browser                                                          |
//  +------------------------------------------------------------------------+

use AdinanCenci\RadioBrowser\RadioBrowser;

global $cfg, $db;
global $base_size, $spaces, $scroll_bar_correction;

authenticate('access_media');


// formattedNavigator
$nav			= array();
$nav['name'][]	= 'Library';
$nav['url'][]	= 'index.php';
$nav['name'][]	= 'Radio browser';

require_once('include/header.inc.php');
require 'vendor/autoload.php';

$tileSize = $_GET['tileSizePHP'];
$searchTag = $_GET['searchTag'];

//$languages = $browser->getLanguages();
$server = RadioBrowser::pickAServer();
$browser = new RadioBrowser($server);
$countries = $browser->getCountries();
?>
<h1>Search radio stations</h1>
<table cellspacing="0" cellpadding="0" id="searchRadio" class="border">

<tr class="textspace"><td colspan="2"></td></tr>
<tr>
	<td>Name:</td>
	<td><input type="text" id="name" value=""  style="width: 100%; max-width: 400px; margin-bottom: 4px"></td>
</tr>
<tr>
	<td>Tag:</td>
	<td><input type="text" id="tag" value="<?= $searchTag ?>" style="width: 100%; max-width: 400px; margin-bottom: 4px;"></td>
</tr>
<tr>
	<td>Country:</td>
	<td>
    <select id="country" style="width: 100%; max-width: 400px;">
    <option value="0">All countries</option>
    <?php 
    foreach ($countries as $country) {
      echo '<option value="' . $country['iso_3166_1'] . '">' . $country['name'] . '</option>';
    }
    ?>
  </select>
  </td>
</tr>

<tr class="space"><td colspan="2"></td></tr>
<tr>
	<td colspan="2">
  <div class="buttons">
		<span id="btnSearch" onClick="searchRadio()">Search</span>
    <span id="btnSaved" onClick="showSavedRadios()">Show saved</span>
    <span id="btnClear" onClick="clearForm()">Clear</span>
	</div>
  </td>
	<td></td>
</tr>
</table>

<div id="searchResults">
<h1><span id="searchType"></span> <span id="stationsCount"></span></h1>
  <div id="stationContainer"></div>
  <div style="text-align: center; padding: 1em;" id="loadingIndicator">
    <i class="fa fa-cog fa-spin icon-small"></i> Loading stations list...
  </div>
</div>

<script>
  $("#name").focus();
  <?php
  if ($searchTag){
    //echo 'searchRadio();';
  }
  ?>
  if ($("#tag").val() != '' || $("#name").val() != ''){
    searchRadio();
  }
  else {
    showSavedRadios();
  }

  $('#searchRadio :input').keypress(function (e) {
    var key = e.which;
    if(key == 13)  // the enter key code
      {
       searchRadio();
       return false;  
      }
  });
  
  function searchRadio(){
    $("#searchType").html('Search results');
    $("#stationsCount").html('');
    $("#searchResults").show();
    $("#stationContainer").html('');
    $("#loadingIndicator").show();
    var request = $.ajax({
      url: "ajax-radio.php",
      type: "POST",
      data: {
        action : "searchRadios",
        name : $("#name").val().trim(),
        tag : $("#tag").val().toLowerCase().trim(),
        countrycode : $("#country").val()
        },  
      dataType: "html"
    }); 
    
    request.done(function(data) {  
      $("#stationContainer").html(data);
      $("#loadingIndicator").hide();
    }); 
    
    request.fail(function( jqXHR, textStatus ) {  
      alert( "Request failed: " + textStatus );	
    }); 
  }

  function clearForm(){
    $("#name").val('');
    $("#tag").val('');
    $("#country").val('0');
    $("#searchResults").hide();
    //showSavedRadios();
  }

  function showSavedRadios(){
    $("#searchType").html('Saved radio stations');
    $("#stationsCount").html('');
    $("#searchResults").show();
    $("#stationContainer").html('');
    $("#loadingIndicator").show();

    var request = $.ajax({
      url: "ajax-radio.php",
      type: "POST",
      data: {
        action : "showSavedRadios"
        },  
      dataType: "html"
    }); 
    
    request.done(function(data) {
      if (data){
        /*$("#searchType").html('Saved radio stations');
        $("#stationsCount").html('');
        $("#searchResults").show();
        $("#stationContainer").html('');
        */
        $("#stationContainer").html(data);
      }
      else {
        $("#stationContainer").hide();
      }
      $("#loadingIndicator").hide();
    }); 
    
    request.fail(function( jqXHR, textStatus ) {  
      alert( "Request failed: " + textStatus );	
    }); 
  }

</script>

<?php
require_once('include/footer.inc.php');
?>