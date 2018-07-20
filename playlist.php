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
//  | playlist.php                                                           |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
$cfg['menu'] = 'playlist';

authenticate('access_playlist');
require_once('include/header.inc.php');
require_once('include/play.inc.php');


if ($cfg['player_type'] == NJB_HTTPQ) {
	$hash		= httpq('gethash');
	$listpos	= httpq('getlistpos');
	$file		= httpq('getplaylistfilelist', 'delim=*');
	$file		= str_replace('\\', '/', $file);
	$file		= explode('*', $file);
	$listlength	= (empty($file[0])) ? 0 : count($file);
	$volume		= true;
	$max_volume	= 255;
		
	// Get relative directory based on $cfg['media_share']
	foreach ($file as $i => $value)	{
		if (strtolower(substr($file[$i], 0, strlen($cfg['media_share']))) == strtolower($cfg['media_share']))
			$file[$i] = substr($file[$i], strlen($cfg['media_share']));
	}
}
elseif ($cfg['player_type'] == NJB_MPD)	{
	$status 		= mpd('status');
	$listpos		= isset($status['song']) ? $status['song'] : 0;
	$file			= mpd('playlist');
	$hash			= md5(implode('<seperation>', $file));
	$listlength		= $status['playlistlength'];
	$bottom = ($listlength > 1) ? ($listlength - 1) : 0;
	$volume			= (isset($status['volume']) == false || $status['volume'] == -1) ? false : true;
	$max_volume		= 100;	
}
elseif ($cfg['player_type'] == NJB_VLC)
	message(__FILE__, __LINE__, 'warning', '[b]videoLAN playlist not supported yet[/b]');
else
	message(__FILE__, __LINE__, 'error', '[b]Player not supported[/b]');


$featuring = false;
for ($i=0; $i < $listlength && !$featuring; $i++) {
	$query = mysqli_query($db,'SELECT featuring FROM track WHERE featuring != "" AND relative_file = "' . mysqli_real_escape_string($db,$file[$i]) . '"');
	if (mysqli_fetch_row($query)) $featuring = true;
}
if (count($file) == 0) {
	message(__FILE__, __LINE__, 'warning', '[b]Playlist is empty[/b][br][br]
	[url=index.php]Add[/url] some music!');
	require_once('include/footer.inc.php');
	exit;
}
?>

<!-- info + control -->
<div id="info_area">
<div id="image_container">
	<!--
	<div id="cover-spinner">
		<img src="image/loader.gif" alt="">
	</div>
	-->
	<div id="image">
		<a href="index.php"><img id="image_in" src="image/transparent.gif" alt=""></a>
	</div>
		<div id="waitIndicatorImg"></div>
</div>

<script>
/* 	$('#image').css('position', 'absolute');
	$('#image').css('top', '0'); */
</script>

<div class="pl-track-info-right">
<div class="pl-track-info" id="pl-track-info">
	<div class="pl-track-title"><span id="track_number" class="pl-track-number">&nbsp;</span><span id="title">&nbsp;</span><span id="title_wait_indicator" class="pl-track-title icon-selected">&nbsp;<i class="fa fa-cog fa-spin"></i></span></div>
	<div class="pl-fld-name">track title</div>
	<div class="pl-track-artist"><span id="artist">&nbsp;</span></div>
	<div class="pl-fld-name">track artist</div>
	<div class="pl-track-artist"><span id="album">&nbsp;</span></div>
	<div class="pl-fld-name">album</div>
	<div class="pl-track-artist"><span id="genre">&nbsp;</span></div>
	<div class="pl-fld-name">genre</div>
	<div class="pl-track-artist"><span id="year">&nbsp;</span></div>
	<div class="pl-fld-name">year</div>
	
	<!-- <div class="pl-fld-name">file info</div> -->
	<div class="pl-track-artist"><span id="lyrics">&nbsp;</span></div>
	<div class="pl-fld-name">search</div>
	
	<div class="pl-track-favorites"><span id="favorites">&nbsp;</span></div>
	<div class="pl-fld-name">favorites/blacklist</div>
	
	
</div>

<div class="pl-track-info" id="pl-track-info-narrow" style="text-align: center;">
	<div class="pl-track-number-title">
		<span class="pl-track-number" id="track_number1">&nbsp;</span><span id="title1" class="pl-track-title">&nbsp;</span><span id="title1_wait_indicator" class="pl-track-title icon-selected">&nbsp;<i class="fa fa-cog fa-spin"></i></span>
	</div>
	<div class="pl-file-info">
		<span class="pl-track-artist" id="artist1">&nbsp;</span>
		<span class="pl-track-artist" id="album1">&nbsp;</span>
	</div>
	<div id="fileInfoForDbTracks" class="pl-file-info">
		<span class="pl-track-artist" id="genre1">&nbsp;</span> &bull; 
		<span class="pl-track-artist" id="year1">&nbsp;</span> &bull; 
		<span class="pl-track-artist"><span id="lyrics1">&nbsp;</span></span> &bull;
		<span class="pl-track-favorites"><span id="favorites1">&nbsp;</span></span> 
	</div>
	
	
</div>


<!-- begin controll bar -->

<div class="media_control">

<div class="playlist_indicator"><div>
		<span class="icon-anchor" name="time" id="time" style="text-align: right; padding-right:1px;"></span>
		<div id="track-progress" class="out pointer" style="display:inline-block;" onClick="ajaxRequest('play.php?action=seekImageMap&amp;dx=' + this.clientWidth + '&amp;x=' + getRelativeX(event, this) + '&amp;menu=playlist', evaluatePlaytime);">
			<div id="bar-indicator"></div>
			<div id="timebar" style=" overflow: hidden;" class="in"></div>
			
		</div>
		<span class="playlist_status_off" name="tracktime" id="tracktime" style="text-align: left; padding-left: 1px; display: inline;"></span>
	</div>
</div>	
<div id="parameters">&nbsp;</div>	
<div class="control-row">
	<div class="playlist_button"><div class="playlist_status_off" name="shuffle" id="shuffle" onclick="javascript:ajaxRequest('play.php?action=toggleShuffle&amp;menu=playlist', evaluateShuffle);">
		<i style="top: -2px;" class="typcn typcn-arrow-shuffle cb-typcn"></i>	
	</div></div>
	

	<div class="playlist_button"><div class="playlist_status_off" name="previous" id="previous" onclick="javascript:ajaxRequest('play.php?action=prev&amp;menu=playlist');">
		<i class="fa fa-fast-backward sign-ctrl"></i>
	</div></div>
	
	<div class="playlist_button"><div class="playlist_status_off" name="play" id="play" onclick="javascript:ajaxRequest('play.php?action=play&amp;menu=playlist', evaluateIsplaying);">
		<i class="fa fa-play sign-ctrl"></i>
	</div></div>
	
	<!--
	<div class="playlist_button"><div class="playlist_status_off" name="pause" id="pause" onclick="javascript:ajaxRequest('play.php?action=pause&amp;menu=playlist', evaluateIsplaying);">
		<i class="fa fa-pause sign-ctrl"></i>
	</div></div>
	-->
	
	
	<div class="playlist_button" style="display: none;"><div class="playlist_status_off" name="stop" id="stop" onclick="javascript:ajaxRequest('play.php?action=stop&amp;menu=playlist', evaluateIsplaying);">
		<i class="fa fa-stop sig"></i>
	</div></div>
	
	<div class="playlist_button" style=""><div class="playlist_status_off" name="next" id="next" onclick="javascript:ajaxRequest('play.php?action=next&amp;menu=playlist', evaluateIsplaying);">
		<i class="fa fa-fast-forward sign-ctrl"></i>
	</div></div>
	
	<div class="playlist_button"><div class="playlist_status_off" name="repeat" id="repeat" onclick="javascript:ajaxRequest('play.php?action=toggleRepeat&amp;menu=playlist', evaluateRepeat);">
		<i style="top: -2px;" class="typcn typcn-arrow-repeat cb-typcn"></i>
	</div></div>
</div>

</div>
</div>
<!-- end controll bar -->
<div id="" style="clear:both;height:0px;"></div>
</div>
<!-- end info + controll -->


<div id="playlist">
<!--
<span  class="playlist-title">Play list</span><span class="hidePL">&nbsp;(hide)</span>
-->
<span  class="playlist-title">Playlist</span><span id="end_time_1"></span><span id="end_in_1"></span><span id="total_time_1"></span>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="small_cover"></td>
	<td class="play-indicator"></td>
	<td class="trackNumber">#</td>
	<td class="time">Title</td>
	<td class="time">Artist</td>
	<td class="time pl-genre">Genre</td>
	<td class="time pl-year">Year</td>
	<td class="time">Time</td>
	<td class="iconDel"></td><!-- optional delete -->
</tr>
<?php
$playtime = array();
$track_id = array();
$playlistTT = 0;
for ($i=0; $i < $listlength; $i++) {
	//streaming track outside of mpd library	
	$pos = strpos($file[$i],'track_id=');	
	if ($pos === false) {
		$query = mysqli_query($db,'SELECT track.title, track.artist, track.track_artist, track.featuring, track.miliseconds, track.track_id, track.genre, album.genre_id, track.audio_dataformat, track.audio_bits_per_sample, track.audio_sample_rate, track.album_id, track.number, track.track_id, track.year as trackYear FROM track, album WHERE track.album_id=album.album_id AND track.relative_file = "' . 	mysqli_real_escape_string($db,$file[$i]) . '"');
	} 
	else {
		$t_id = substr($file[$i],$pos + 9, 19);
		$query = mysqli_query($db,'SELECT track.title, track.artist, track.track_artist, track.featuring, track.miliseconds, track.track_id, track.genre, album.genre_id, track.audio_dataformat, track.audio_bits_per_sample, track.audio_sample_rate, track.album_id, track.number, track.track_id, track.year as trackYear FROM track, album WHERE track.album_id=album.album_id AND track.track_id = "' . 	mysqli_real_escape_string($db,$t_id) . '"');
	}
	$table_track = mysqli_fetch_assoc($query);
	$playtime[] = (int) $table_track['miliseconds'];
	$playlistTT = $playlistTT + (int) $table_track['miliseconds'];
	$track_id[] = (string) $table_track['track_id'];
	
	$album_genres = parseMultiGenre($table_track['genre']);
	
	/* $genres = explode(';',$table_track['genre_id']);
	$where = '';
	foreach ($genres as $g){
		$where = ($where == '') ? ' genre_id LIKE "' . $g . '"' : $where . ' OR genre_id LIKE "' . $g . '"';
	}
	$query = mysqli_query($db,'SELECT genre, genre_id FROM genre WHERE ' . $where);
	while ($genre = mysqli_fetch_assoc($query)){
		$album_genres[$genre['genre_id']] = $genre['genre'];
		//$album['album_genre'] = $album['album_genre'] . '; ' . $genre['genre'];
	} */
	
	//$genre_id[] = (string) $table_track['genre_id'];
	$number[] = (string) $table_track['number'];
	
	$is_file_stream = false;
	$pos = strpos($file[$i],'filepath=');
	if ($pos !== false) {
		$is_file_stream = true;
	}
	//track not found in OMPD DB - take info from MPD, unless this is a stream of file
	if (!isset($table_track['artist']) && !$is_file_stream) {
		
		$playlistinfo = mpd('playlistinfo ' . $i);
		if (strpos($playlistinfo['file'],'ompd_title=') !== false){
			//stream from Youtube
			$parts = parse_url($playlistinfo['file']);
			parse_str($parts['query'], $query);
			$table_track['title'] = urldecode($query['ompd_title']);
			$table_track['album'] = urldecode($query['ompd_webpage']);
			$playlistinfo['Time'] = (int)urldecode($query['ompd_duration']);
		}
		else {
			if (isset($playlistinfo['Artist'])) 
				$table_track['track_artist']	= $playlistinfo['Artist'];
			/* else 
				$table_track['track_artist']	= basename($playlistinfo['file']); */
			
			if (isset($playlistinfo['Name'])) 
				$table_track['title']	= $playlistinfo['Name'];
			else if (isset($playlistinfo['Title']))
				$table_track['title']	= $playlistinfo['Title'];
			else
				$table_track['title']	= basename($playlistinfo['file']);
			
			if (isset($playlistinfo['Album']))
				$table_track['album']	= $playlistinfo['Album'];
			else 
				$table_track['album']	= $playlistinfo['file'];
		}
		
		$table_track['number'] = $playlistinfo['Pos'] + 1;
		$table_track['trackYear'] = $playlistinfo['Date'];
		$table_track['genre'] = $playlistinfo['Genre'];
		$album_genres = parseMultiGenre($table_track['genre']);
		$table_track['miliseconds'] = $playlistinfo['Time'] * 1000;
		
	}
	//this is stream of a file
	elseif ($is_file_stream) {
		//TODO: take info from file using getid3
		$playlistinfo = mpd('playlistinfo ' . $i);
		$table_track['number'] = $playlistinfo['Pos'] + 1;
		$filepath = substr($file[$i],$pos + 9, strlen($file[$i]) - $pos);
		$filepath = urldecode($filepath);
		$table_track['title'] = basename($filepath);
		$pos = strpos($filepath, $table_track['title']);
		$table_track['album'] = substr($filepath, 0, $pos);
	}
	$query2 = mysqli_query($db,'SELECT album, year, image_id FROM album WHERE album_id="' . $table_track['album_id'] . '"');
	$image_id = mysqli_fetch_assoc($query2);
?>
<tr class="<?php if ($i == $listpos) echo 'select'; else echo ($i & 1) ? 'even mouseover' : 'odd mouseover'; ?>" id="track<?php echo $i; ?>" style="display:table-row;">
	
	<td class="small_cover">
	<a id="track<?php echo $i; ?>_image" href="javascript:ajaxRequest('play.php?action=playIndex&amp;index=<?php echo $i ?>&amp;menu=playlist',evaluateListpos);"><img src="image.php?image_id=<?php echo $image_id['image_id'] ?>&track_id=<?php echo $table_track['track_id'] ?>" alt="" width="100%"></a></td>
	
	<td class="play-indicator">
	<div id="track<?php echo $i; ?>_play" style="<?php if ($i == $listpos) echo 'visibility: visible;'; else echo 'visibility: hidden;'; ?>" onclick="javascript:ajaxRequest('play.php?action=playIndex&amp;index=<?php echo $i ?>&amp;menu=playlist',evaluateListpos);">
			<img src="skin/ompd_default/img/playing.gif">
			
			<!--<i id="track<?php echo $i; ?>_play_indicator" class="fa fa-play-circle-o"></i>
			-->
	</div>
	</td>
	
	<td class="trackNumber"><a class="trackNumber" href="javascript:ajaxRequest('play.php?action=playIndex&amp;index=<?php echo $i ?>&amp;menu=playlist',evaluateListpos);" id="track<?php echo $i; ?>_number"><div class="trackNumber"><?php echo html($table_track['number']); ?></div></a></td>
	
	<?php 
	$track_title_array = explode(" ", $table_track['title']);
	$lengths = array_map('strlen', $track_title_array);
	if (max($lengths) > 30) {
		$break_method = 'break-all';
	} 
	else {
		$break_method = 'break-word';
	}
	?>
	<td class="time"><a href="javascript:ajaxRequest('play.php?action=playIndex&amp;index=<?php echo $i ?>&amp;menu=playlist',evaluateListpos);" id="track<?php echo $i; ?>_title"><div class="playlist_title <?php echo $break_method; ?>"><?php echo html($table_track['title']) ?></div>
		<?php 
		if ($image_id['album']) {
			$album_name = $image_id['album'];
		} 
		else { 
			$album_name = $table_track['album']; 
		}
		$album_name_array = explode(" ", $album_name);
		$lengths = array_map('strlen', $album_name_array);
		if (max($lengths) > 30) {
			$break_method = 'break-all';
		} 
		else {
			$break_method = 'break-word';
		}
		?>
		<div class="playlist_title_album <?php echo $break_method; ?>">
			<?php echo $album_name; ?>
		</div>
	</a></td>
	
	<td class="time break-word">
	<?php
	$artist = '';
		$exploded = multiexplode($cfg['artist_separator'],$table_track['track_artist']);
		$l = count($exploded);
		if ($l > 1) {
			for ($j=0; $j<$l; $j++) {
				$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($exploded[$j]) . '">' . html($exploded[$j]) . '</a>';
				if ($j != $l - 1) {
					$delimiter = getInbetweenStrings($exploded[$j],$exploded[$j + 1], $table_track['track_artist']);
					$artist = $artist . '<a href="index.php?action=view2&amp;artist=' . rawurlencode($table_track['track_artist']) . '&amp;order=year"><span class="artist_all">' . $delimiter[0] . '</span></a>';
				}
			}
			echo $artist;
		}
		else {
			echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($table_track['track_artist']) . '&amp;order=year">' . html($table_track['track_artist']) . '</a>';
		}
	?>
	</td>

	<td class="time pl-genre">
	
	<?php if (count($album_genres) > 0) { 
		foreach($album_genres as $g_id => $ag) {
	?>
		<a href="index.php?action=view2&order=artist&sort=asc&genre_id=<?php echo $g_id; ?>"><?php echo $ag; ?></a><br>
	<?php 
		}
	}
	else {
			echo $table_track['genre'];
	}
	?>
	</td>
	

	<?php
	$year	= ((is_null($image_id['year'])) ? (string) $table_track['trackYear'] : (string) $image_id['year']);
	?>
	<td class="time pl-year">
	<a href="index.php?action=view2&order=artist&sort=asc&year=<?php echo $year ?>"><?php echo $year ?></a>
	</td>
	
	<td class="time"><?php if (isset($table_track['miliseconds'])) echo formattedTime($table_track['miliseconds']); ?></td>

	
	<td class="iconDel">
		<div  id="menu-icon-div<?php echo $i ?>" <?php echo 'onclick="toggleMenuSub(' . $i . ');"'; ?>>
			<i id="menu-icon<?php echo $i ?>" class="fa fa-bars fa-fw sign"></i>
		</div>
		<div  id="menu-insert-div<?php echo $i ?>" style="display: none; /* position: absolute; */ bottom: 0;" onclick="moveTrack(<?php echo $i ?>,-1,false)">
			<i class="fa fa-angle-down fa-fw sign"></i>
		</div>
	</td>
</tr>

<tr id="track-line<?php echo $i; ?>" class="line"><td colspan="12"></td></tr>
<?php
	moveSubMenu($i, $bottom);
	}
?>


</table>

<div>
	<h1>
		<div class="total-time">
		<span id="end_time"></span>
		<span id="end_in"></span>
		<span id="total_time">
		<?php 
		/* $uSec = $playlistTT % 1000;
		$playlistTT = floor($playlistTT / 1000);

		$seconds = $playlistTT % 60;
		$playlistTT = floor($playlistTT / 60);

		$minutes = $playlistTT % 60;
		$playlistTT = floor($playlistTT / 60); 

		$hours = $playlistTT % 60;
		$playlistTT = floor($playlistTT / 60); 
		echo "Total: " . sprintf("%02d",$hours) . ':' . sprintf("%02d",$minutes) . ':' . sprintf("%02d",$seconds); */
		?>
		</span>
		</div>
	</h1>
</div>
</div> <!-- playlist -->

<script type="text/javascript">
<!--

var previous_hash			= '<?php echo $hash; ?>';
var previous_listpos		= <?php echo $listpos; ?>;
var previous_isplaying		= -1; // force update
var previous_repeat			= -1;
var previous_shuffle		= -1;
var previous_gain			= -1;
var previous_miliseconds	= -1;
var previous_track_id		= 'ff';
//var previous_volume			= -1;
var playtime				= <?php echo safe_json_encode($playtime); ?>;
var track_id				= <?php echo safe_json_encode($track_id); ?>;
var current_track_id		= '';
var timer_id				= 0;
var timer_function			= 'ajaxRequest("play.php?action=playlistStatus&menu=playlist", evaluateStatus)';
var timer_delay				= 1000;
var list_length				= <?php echo $listlength;?>;
//console.trace();
var fromPosition			= -1

$("#time").click(function(){
	$.ajax({url: "play.php?action=beginOfTrack&menu=playlist"});
});
 
var testing = '<?php echo $cfg['testing']; ?>';


function hidePL() {
	window.scrollTo(0,0);
}

function showPL() {
	window.scrollTo(0,window.innerHeight);
}

function deletePLitem(data) {

	var idx = parseInt(data.index);
	//var idx = parseInt(idx2del);
	console.log ("idx: %s", idx);
	
	var row2del = document.getElementById('track' + idx);
	var newId = Date.parse(new Date());
	row2del.id = 'track' + newId;
	
	//$('#track' + newId).fadeOut(700, function(){ $('#track' + newId).remove();});
	row2del.parentNode.removeChild(row2del);
	//row2del.style.display='none';
	
	list_length = list_length-1;
	var i = idx+1;
	//console.log("i= %s", i);
	//console.log("list_length= %s", list_length);
	
	for (i; i<=list_length; i++) {
		var j = i-1;
		document.getElementById('track' + i).id = 'track' + j;
		document.getElementById('track' + i + '_image').id = 'track' + j + '_image';
		document.getElementById('track' + i + '_number').id = 'track' + j + '_number';
		document.getElementById('track' + i + '_title').id = 'track' + j + '_title';
		document.getElementById('track' + i + '_delete').id = 'track' + j + '_delete';
		
		
		
		var oldClassName = document.getElementById('track' + j).className;
		var t = oldClassName.search('even');
		//console.log("className: %s, 'even' pos: %s", oldClassName, t);
		document.getElementById('track' + j).className = ((oldClassName.search('select') == 0 ) ? 'select' : ((oldClassName.search('even') < 0 ) ? 'even mouseover' : 'odd mouseover'));
		
		var newHref = 'javascript:ajaxRequest(\'play.php?action=playIndex&amp;index=' + j + '&amp;menu=playlist\',evaluateListpos);';
		
		document.getElementById('track' + j + '_image').href = newHref;
		document.getElementById('track' + j + '_number').href = newHref;
		document.getElementById('track' + j + '_title').href = newHref;
		document.getElementById('track' + j + '_delete').innerHTML='<a href="javascript:ajaxRequest(\'play.php?action=deleteIndex&amp;index=' + j + '&amp;menu=playlist\',deletePLitem);"><span class="typcn typcn-delete" style="font-size: 30px; color: #555555;"><span></a>';
		
	}
	resizeImgContainer();

}

function initialize() {
	ajaxRequest('play.php?action=playlistTrack&track_id=' + track_id[<?php echo $listpos; ?>] + '&menu=playlist', evaluateTrack);
	ajaxRequest('play.php?action=playlistStatus&track_id=' + track_id[<?php echo $listpos; ?>] + '&menu=playlist', evaluateStatus);
	//ajaxRequest('ajax-track-version.php?track_id=' + track_id[<?php echo $listpos; ?>] + '&menu=playlist', evaluateTrackVersion);
}


function evaluateStatus(data) {
	// data.hash, data.miliseconds, data.listpos, data.volume
	// data.isplaying, data.repeat, data.shuffle, data.gain
	
	if (previous_hash != data.hash) {
		//window.location.href="<?php echo NJB_HOME_URL ?>playlist.php";
		location.reload(false);
		//window.location.href = window.location.href;
		//history.go();
	}
	data.max = playtime[data.listpos];
	if (!current_track_id) { //track not found in DB, get data from MPD
		data.max = data.Time;
		var title = data.title;
		/* if (title.indexOf("action=streamTo") != -1) {
			title = data.name; 
		} */
		document.getElementById('title1').innerHTML = document.getElementById('title').innerHTML =  title;
		var rel_file = encodeURIComponent(data.relative_file);
		//console.log ("rel_file=" + rel_file);
		var params = data.audio_dataformat + '&nbsp;&bull;&nbsp;' + data.audio_bits_per_sample + 'bit - ' + data.audio_sample_rate/1000 + 'kHz&nbsp;&bull;&nbsp;' + data.audio_profile;
		document.getElementById('parameters').innerHTML = params;
		
		var query_artist = '';
		if (data.track_artist) {
			query_artist = data.track_artist;
		}
		document.getElementById('lyrics1').innerHTML = document.getElementById('lyrics').innerHTML = '<a href="ridirect.php?query_type=lyrics&q=' + encodeURIComponent(query_artist) + '+' + encodeURIComponent(data.title) + '" target="_blank"><i class="fa fa-search"></i>&nbsp;Lyrics</a>';
		
		$('#favorites').html('&nbsp;');
		$('#favorites1').html('&nbsp;');
		//$("#saveCurrentPlaylist").click();
		$("#saveCurrentTrack").hide();
		$("#trackOptions").hide();
		$('#saveCurrentPlaylist i').removeClass("fa-circle-o fa-check-circle-o").addClass("fa-check-circle-o");
		$('#saveCurrentTrack i').removeClass("fa-check-circle-o fa-circle-o").addClass("fa-circle-o");
		/* if (data.isStream == 'true') {
			$('#lyrics').html('&nbsp;');
			$('#lyrics1').html('&nbsp;');
			$('#fileInfoForDbTracks').css('visibility', 'hidden');
		} */
	}
	else {
		$("#saveCurrentTrack").show();
	}
	evaluateListpos(data.listpos);
	evaluatePlaytime(data);
	evaluateRepeat(data.repeat);
	evaluateShuffle(data.shuffle);
	evaluateIsplaying(data.isplaying, data.listpos);
	evaluateVolume(data.volume);
	evaluateGain(data.gain);
	evaluateConsume(data);
	 
	
}


function evaluateListpos(listpos) {
	if (previous_listpos != listpos) {
		document.getElementById('track' + previous_listpos).className = (previous_listpos & 1) ? 'even mouseover' : 'odd mouseover';
		document.getElementById('track' + listpos).className = 'select';
		document.getElementById('track' + previous_listpos + '_play').style.visibility  = 'hidden';
		document.getElementById('track' + listpos + '_play').style.visibility = 'visible';
		document.getElementById('time').innerHTML = formattedTime(0);
		document.getElementById('timebar').style.width = 0;
		ajaxRequest('play.php?action=playlistTrack&track_id=' + track_id[listpos] + '&menu=playlist', evaluateTrack);
		previous_miliseconds = 0;
		previous_listpos = listpos;
	}
	else hideSpinner();
	//resizeImgContainer();
}


function evaluatePlaytime(data) {
	// data.miliseconds, data.max, ....
	if (previous_miliseconds != data.miliseconds) {
		document.getElementById('time').innerHTML = formattedTime(data.miliseconds);
		var width_ = 0;
		var progress_bar_width = document.getElementById('track-progress').clientWidth;
		
		if (data.max > 0)	width_ = Math.round(data.miliseconds / data.max * progress_bar_width);
		if (width_ > progress_bar_width)	width_ = progress_bar_width;
		
		//document.getElementById('timebar').style.width = width_;
		$('#timebar').width(width_);
		previous_miliseconds = data.miliseconds;
	}
	if(data.hasStream != 'true' && !data.repeat && !data.single && !data.shuffle){
		$("span[id^='end_time'").show();
		$("span[id^='end_time'").html('End at: ' + data.end_time + '&nbsp;&bull;&nbsp;');
		$("span[id^='end_in'").show();
		$("span[id^='end_in'").html('Left: ' + data.end_in + '&nbsp;&bull;&nbsp;');
	}
	else {
		$("span[id^='end_time'").hide();
		$("span[id^='end_in'").hide();
	}
	if(data.hasStream != 'true'){
		$("span[id^='total_time'").show();
		$("span[id^='total_time'").html('Total: ' + data.total_time);
	}
	else {
		$("span[id^='total_time'").hide();
	}
}


function evaluateVolume_old(volume) {
	if (previous_volume != volume && volume >= 0) {
		// Volume
		var volume_percentage	= Math.round(100 * volume / <?php echo $max_volume; ?>);
		var width				= Math.round(200 * volume / <?php echo $max_volume; ?>);
		document.getElementById('volume').innerHTML = volume_percentage + '%';
		document.getElementById('volumeimage').src = '<?php echo $cfg['img']; ?>playlist_bar_on.png';
		document.getElementById('volumebar').style.width = width;
		previous_volume = volume;
	}
	if (previous_volume != volume && volume < 0) {
		// Mute volume
		var mute_volume = -1 * volume;
		var volume_percentage	= Math.round(100 * mute_volume / <?php echo $max_volume; ?>);
		var width				= Math.round(200 * mute_volume / <?php echo $max_volume; ?>);
		document.getElementById('volume').innerHTML = 'mute';
		document.getElementById('volumeimage').src = '<?php echo $cfg['img']; ?>playlist_bar_off.png';
		document.getElementById('volumebar').style.width = width;
		previous_volume = volume;
	}
}


function evaluateIsplaying(isplaying, idx) {
	if (previous_isplaying != isplaying) {
		if (isplaying.state){
			idx = isplaying.idx;
			isplaying = isplaying.state;
		}
		if (isplaying == 0) {
			// stop
			$("#time").removeClass();
			$("#time").addClass("icon-anchor");
			$("#play").removeClass();
			$("#play").addClass("playlist_status_off");
			$("#play").html('<i class="fa fa-play sign-ctrl"></i>');
			$("#play").attr("onclick","javascript:ajaxRequest('play.php?action=play&menu=playlist', evaluateIsplaying);");
			//$('#track' + idx + '_play').hide();
			document.getElementById('track' + idx + '_play').style.visibility = 'hidden';
			document.getElementById('time').innerHTML = formattedTime(0);
			document.getElementById('timebar').style.width = 0;
			previous_miliseconds = 0;
		}
		else if (isplaying == 1) {
			// play
			document.getElementById('track' + idx + '_play').style.visibility = 'visible';		
			$("#time").removeClass();
			$("#time").addClass("icon-anchor");
			$("#play").html('<i class="fa fa-pause sign-ctrl"></i>');
			$("#play").removeClass();
			//$("#play").addClass("playlist_status_on");
			$("#play").addClass("playlist_status_off");
			$("#play").attr("onclick","javascript:ajaxRequest('play.php?action=pause&menu=playlist', evaluateIsplaying);");
			//$('#track' + idx + '_play').show();
		}
		else if (isplaying == 3) {
			// pause
			$("#time").removeClass();
			$("#time").addClass("blink_me icon-anchor");
			$("#play").html('<i class="fa fa-play sign-ctrl"></i>');
			$("#play").removeClass();
			//$("#play").addClass("blink_me playlist_status_on");
			$("#play").addClass("playlist_status_off");
			$("#play").attr("onclick","javascript:ajaxRequest('play.php?action=play&menu=playlist', evaluateIsplaying);");
			//$('#track' + idx + '_play').hide();
			document.getElementById('track' + idx + '_play').style.visibility = 'hidden';
		}
		previous_isplaying = isplaying;
		console.log('isplaying:' + isplaying + '; idx: ' + idx);
	}
}


function evaluateRepeat(repeat) {
	if (previous_repeat != repeat) {
		if (repeat == 0) document.getElementById('repeat').className = 'playlist_status_off';
		if (repeat == 1) document.getElementById('repeat').className = 'playlist_status_on';
		previous_repeat = repeat;
	}
}


function evaluateShuffle(shuffle) {
	if (previous_shuffle != shuffle) {
		if (shuffle == 0) document.getElementById('shuffle').className = 'playlist_status_off';
		if (shuffle == 1) document.getElementById('shuffle').className = 'playlist_status_on';
		previous_shuffle = shuffle;
	}
}


function evaluateGain(gain) {
	if (previous_gain != gain) {
		document.getElementById('gain').className="playlist_status_on";
		if (gain == 'off')		{document.getElementById('gain_text').innerHTML = 'gain: off';
		document.getElementById('gain').className="playlist_status_off";}
		if (gain == 'album')	document.getElementById('gain_text').innerHTML = 'gain: album';
		if (gain == 'auto')		document.getElementById('gain_text').innerHTML = 'gain: auto';
		if (gain == 'track')	document.getElementById('gain_text').innerHTML = 'gain: track';
		previous_gain = gain;
		
	}
}

function setFavoriteSubMiddle(data) {
	if (data.action == "add") {
		$("i[id^='favorite_star']").removeClass("fa-star-o").addClass("fa-star");
		$("#save_favorite_star").removeClass("fa-star-o").addClass("fa-star");
		$("#addToFav_txt").html(" Remove from ");
	}
	else if (data.action == "remove") {
		$("i[id^='favorite_star']").removeClass("fa-star").addClass("fa-star-o");
		$("#save_favorite_star").removeClass("fa-star").addClass("fa-star-o");
		$("#addToFav_txt").html(" Add to ");
	}
	toggleSubMiddle("SavePlaylist");
}


function setBlacklistSubMiddle(data) {
	if (data.action == "add") {
		$("span[id^='blacklist_star_bg']").addClass("blackstar-selected");
		$("#favorites").addClass("blackstar-selected blackstar");
		$("#favorites1").addClass("blackstar-selected blackstar");
		$("#addToBlacklist_txt").html(" Remove form ");
	}
	else if (data.action == "remove") {
		$("span[id^='blacklist_star_bg']").removeClass("blackstar-selected");
		$("#favorites").removeClass("blackstar-selected blackstar");
		$("#favorites1").removeClass("blackstar-selected blackstar");
		$("#addToBlacklist_txt").html(" Add to ");
	}
	toggleSubMiddle("SavePlaylist");
}


function _evaluateFavorite(data) {
	if (data.inFavorite) {
		$("i[id^='favorite_star']").removeClass("fa fa-star-o").addClass("fa fa-star");
		$("#save_favorite_star").removeClass("fa fa-star-o").addClass("fa fa-star");
	}
	else {
		$("i[id^='favorite_star']").removeClass("fa fa-star").addClass("fa fa-star-o");
		$("#save_favorite_star").removeClass("fa fa-star").addClass("fa fa-star-o");
	}
}


function evaluateTrackVersion(data) {
	$('#title1_wait_indicator').hide();
	$('#title_wait_indicator').hide();

	if (data.other_track_version) {
		var track_ids = '';
		for (var i = 0; i < data['track_ids'].length; i++) {
			track_ids = track_ids + data['track_ids'][i] + ";";
		};
		other_title_enc = encodeURI(data['title']);
		$('#title1').addClass('icon-anchor');
		$('#title').addClass('icon-anchor');
		$('#title1').click(function(){
				window.location.href='index.php?action=view3all&track_ids=' + track_ids + '&other_title=' + other_title_enc;
				
			}
		);
		$( "#title" ).click(function() {
		  $( "#title1" ).click();
		})
	}
	/* else {
		$('#title1').removeClass('icon-anchor');
		$('#title').removeClass('icon-anchor');
		$('#title1').off('click');
		$('#title').off('click');
	} */
}

function evaluateTrack(data) {
	// data.artist, data.title, data.album, data.by, data.album_id, data.image_id
	$('#title1').removeClass('icon-anchor');
	$('#title').removeClass('icon-anchor');
	$('#title1_wait_indicator').hide();
	$('#title_wait_indicator').hide();
	$('#fileInfoForDbTracks').css('visibility', 'visible');
	current_track_id = data.track_id;
	if (previous_track_id != data.track_id && data.track_id != null) {
		//console.log('previous_track_id=' + previous_track_id);
		$('#title1').removeClass('icon-anchor');
		$('#title').removeClass('icon-anchor');
		$('#title1').off('click');
		$('#title').off('click');
		$('#title1_wait_indicator').show();
		$('#title_wait_indicator').show();
		//console.log('track_id=' + data.track_id);
		ajaxRequest('ajax-track-version.php?track_id=' + data.track_id + '&menu=playlist', evaluateTrackVersion);
		previous_track_id = data.track_id;
	} 
	
	if (data.isStream == 'true' && (!data.genre || !data.year)) {
			$('#lyrics').html('&nbsp;');
			$('#lyrics1').html('&nbsp;');
			$('#fileInfoForDbTracks').css('visibility', 'hidden');
	}
	
	//stream from Youtube
	var yt_album = data.album;
	if (yt_album.indexOf('www.youtube') != -1) {
			$('#fileInfoForDbTracks').css('visibility', 'visible');
	}
	
	var s = Math.floor(data.miliseconds / 1000);  
	var m = Math.floor(s / 60);  
	s = s % 60;
	if (s < 10) s = '0' +  s;
	
	
	document.getElementById('tracktime').innerHTML = m + ':' + s;
	artist = '';
	if ($.isArray(data.track_artist)) {
		l = data.track_artist.length;
		if (l>1) {
			for (i=0; i<l; i++) {
				artist = artist + '<a href="index.php?action=view2&order=year&sort=asc&artist=' + encodeURIComponent(data.track_artist_url[i]) + '">' + data.track_artist[i] + '</a>';
				if (i!=l-1) {
					var delimiter = data.track_artist_all.match(data.track_artist_url[i] + "(.*)" + data.track_artist_url[i+1]);
					if (testing == 'on') {
						delimiter[1] = delimiter[1].replace(';','&');
					}
					artist = artist + '<a href="index.php?action=view2&order=artist&sort=asc&artist=' + data.track_artist_url_all + '"><span class="artist_all">' + delimiter[1] + '</span></a>';
				}
			}
		} 
		else if (l>0) {
			artist = '<a href="index.php?action=view2&order=year&sort=asc&artist=' + encodeURIComponent(data.track_artist_url[0]) + '">' + data.track_artist[0] + '</a>';
		}
	}
	else {
		artist = '<a href="index.php?action=view2&order=year&sort=asc&artist=' + data.track_artist_url_all + '">' + data.track_artist + '</a>';
	}
	
	document.getElementById('artist1').innerHTML = (data.track_artist[0] == '&nbsp;') ? '&nbsp;' : 'by ' + artist;
	document.getElementById('artist').innerHTML = artist; 
	document.getElementById('track_number1').innerHTML = document.getElementById('track_number').innerHTML = data.number;
	document.getElementById('title1').innerHTML = document.getElementById('title').innerHTML =  data.title;
	var al = data.album;
	if (data.album_id) {
		var albumLink = '<a href="index.php?action=view3&album_id=' + data.album_id + '">' + data.album + '</a>';
		document.getElementById('album1').innerHTML = (data.album == '&nbsp;') ? '&nbsp' : 'from ' + albumLink; 
		document.getElementById('album').innerHTML = albumLink;
	}
	else if (al.indexOf("://") > 0 && al.indexOf("://") < 6) {
		//e.g. stream from youtube 
		var albumLink = '<a href="' + data.album + '" target="_new">' + data.album + '</a>';
		document.getElementById('album1').innerHTML = (data.album == '&nbsp;') ? '&nbsp' : 'from ' + albumLink; 
		document.getElementById('album').innerHTML = albumLink;
	}
	else if (data.relative_file) {
		var albumLink = '<a href="browser.php?dir=' + data.relative_file + '">' + data.album + '</a>';
		document.getElementById('album1').innerHTML = (data.album == '&nbsp;') ? '&nbsp' : 'from ' + albumLink; 
		document.getElementById('album').innerHTML = albumLink;
	}
	else {
		document.getElementById('album1').innerHTML = (data.album == '&nbsp;') ? '&nbsp' : 'from ' + data.album;
		document.getElementById('album').innerHTML = (data.album == '&nbsp;') ? '&nbsp' : data.album;
	}
	if (data.year) document.getElementById('year1').innerHTML = document.getElementById('year').innerHTML = '<a href="index.php?action=view2&order=artist&sort=asc&year=' + data.year + '">' + data.year + '</a>';
	else document.getElementById('year1').innerHTML = document.getElementById('year').innerHTML = '&nbsp;';
	
	/* if (data.genre && data.genre_id != '-1') document.getElementById('genre1').innerHTML = document.getElementById('genre').innerHTML = '<a href="index.php?action=view2&order=artist&sort=asc&&genre_id=' + data.genre_id + '">' + data.genre + '</a>';
	else if(data.genre) document.getElementById('genre1').innerHTML = document.getElementById('genre').innerHTML = data.genre;
	else document.getElementById('genre1').innerHTML = document.getElementById('genre').innerHTML = '&nbsp;';
	 */
	if (data.genres) {
		var inner_html = '';
		$.each(data.genres, function(key, value){
			if (inner_html == ''){
				inner_html = '<a href="index.php?action=view2&order=artist&sort=asc&&genre_id=' + key + '">' + value + '</a>'
			}
			else {
				inner_html = inner_html + ', <a href="index.php?action=view2&order=artist&sort=asc&&genre_id=' + key + '">' + value + '</a>'
			}
		});
		document.getElementById('genre1').innerHTML = document.getElementById('genre').innerHTML = inner_html;
	}
	else if(data.genre_id == '-1') document.getElementById('genre1').innerHTML = document.getElementById('genre').innerHTML = data.genre;
	else document.getElementById('genre1').innerHTML = document.getElementById('genre').innerHTML = '&nbsp;';
	
	//var rel_file = encodeURIComponent(data.relative_file);
	var rel_file = encodeURIComponent(data.relative_file);
	//console.log ("rel_file=" + rel_file);
	var params = data.audio_dataformat + '&nbsp;&bull;&nbsp;' + data.audio_bits_per_sample + 'bit - ' + data.audio_sample_rate/1000 + 'kHz&nbsp;&bull;&nbsp;' + data.audio_profile;
	if (data.dr) params = params + '&nbsp;&bull;&nbsp;DR=' + data.dr;
	params = params + '&nbsp;&bull;<a href="getid3/demos/demo.browse.php?filename=<?php echo $cfg['media_dir']; ?>' + rel_file + '">&nbsp;<i class="fa fa-info-circle"></i>&nbsp;file details</a>';
	
	document.getElementById('parameters').innerHTML = params;
	var query_artist = '';
	if (data.track_artist) {
		query_artist = data.track_artist;
	}
	document.getElementById('lyrics1').innerHTML = document.getElementById('lyrics').innerHTML = '<a href="ridirect.php?query_type=lyrics&q=' + query_artist + '+' + data.title_core + '" target="_blank"><i class="fa fa-search"></i>&nbsp;Lyrics</a>'; 
	
	if (data.inFavorite) {
		document.getElementById('favorites').innerHTML  = '<i id="favorite_star" class="fa fa-star fa-fw"></i>'; 
		document.getElementById('favorites1').innerHTML = '<i id="favorite_star" class="fa fa-star fa-fw"></i>'; 
		$("#save_favorite_star").removeClass("fa fa-star-o").addClass("fa fa-star");
		$("#addToFav_txt").html(" Remove form ");
	}
	else {
		document.getElementById('favorites').innerHTML  = '<i id="favorite_star" class="fa fa-star-o fa-fw"></i>';
		document.getElementById('favorites1').innerHTML = '<i id="favorite_star" class="fa fa-star-o fa-fw"></i>';
		$("#save_favorite_star").removeClass("fa fa-star").addClass("fa fa-star-o");
		$("#addToFav_txt").html(" Add to ");
	}
	
	if (data.onBlacklist) { 
		$("span[id^='blacklist_star_bg']").addClass("blackstar-selected");
		$("#favorites").addClass("blackstar-selected blackstar");
		$("#favorites1").addClass("blackstar-selected blackstar");
		$("#addToBlacklist_txt").html(" Remove form ");
	}
	else {
		$("span[id^='blacklist_star_bg']").removeClass("blackstar-selected");
		$("#favorites").removeClass("blackstar-selected blackstar");
		$("#favorites1").removeClass("blackstar-selected blackstar");
		$("#addToBlacklist_txt").html(" Add to ");
	}
	
	$("#addToBlacklist").unbind("click");
	
	$("#addToBlacklist").click(function() {
		var action = '';
		if ($("#blacklist_star_bg_save").hasClass("blackstar-selected")) {
			action = 'remove';
			}
		else {
			action = 'add';
		}
		ajaxRequest('ajax-blacklist.php?action=' + action + '&track_id=' + data.track_id, setBlacklistSubMiddle);
		
	});
	
	$("#addToFav").unbind("click");
	
	$("#addToFav").click(function() {
		var action = '';
		//if ($("i[id^='favorite_star']").attr('class') == 'fa fa-star-o fa-fw') {
		if ($('#save_favorite_star').hasClass('fa-star-o')) {
			action = 'add';
			}
		else {
			action = 'remove';
		}
		ajaxRequest('ajax-favorite.php?action=' + action + '&track_id=' + data.track_id, setFavoriteSubMiddle);
		
	});
	
	$("i[id^='favorite_star']").unbind("click");
	
	$("i[id^='favorite_star']").click(function() {
		toggleSubMiddle("SavePlaylist",true);	
		//$("#saveCurrentTrack").click();
		$("html, body").animate({ scrollTop: 0 }, "slow");
	});
	
	//console.log ('data.album_id = ' + data.album_id);
	if (data.album_id) {
		$("#image_in").attr("src","image.php?image_id=" + data.image_id + "&quality=hq&track_id=" + data.track_id);
		$("#image a").attr("href","index.php?action=view3&album_id=" + data.album_id);
		
	}
	else if (data.thumbnail) {
		//thumbnail e.g. from Youtube
		$("#image_in").attr("src","image_crop.php?thumbnail=" + encodeURIComponent(data.thumbnail));
		//$("#image_in").attr("src",data.thumbnail);
		$("#image a").attr("href",data.thumbnail);
	}
	else {
		document.getElementById('image').innerHTML = '<a href="#"><img id="image_in" src="<?php echo 'image/'; ?>large_file_not_found.png" alt=""></a>';
		$("#waitIndicatorImg").hide();
	}
	//$("#cover-spinner").hide();
	
	
	changeTileSizeInfo();
	resizeImgContainer();
	
	/* spinnerImg.stop();
	$('#image').css('position', 'relative');*/
	//$("#waitIndicatorImg").hide(); 
}



$(document).ready(function() {
	
				resizeImgContainer();
				
				$('.showPL').click(function(){

					$('html, body').animate({
						scrollTop: ($(".select").offset().top - $("#fixedMenu").height())
					}, 1000);

				 });

				$('.hidePL').click(function(){

					$('html, body').animate({
						scrollTop: $(".overlib").offset().top
					}, 1000);

				 });
				
				//resizeCover();
				
				$('#pl-track-info-narrow').bind("DOMSubtreeModified",function() {
					//resizeImgContainer();
				});
				
				$(window).resize(function() {
					//resizeCover();
					resizeImgContainer();
				});
				
				$('#play').longpress(function(e) {
					ajaxRequest('play.php?action=stop&menu=playlist', evaluateIsplaying);
				}, function(e) {
						//ajaxRequest('play.php?action=play&menu=playlist', evaluateIsplaying);
				});
				/* //proper display cover when using spin.js as spinner
				$('#image').css('position', 'absolute');
				$('#image').css('top', '0'); */
});



</script>
<?php
require_once('include/footer.inc.php');
?>
