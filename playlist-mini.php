<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2018 Artur Sierzant                            |
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
//  | playlist-mini.php                                                      |
//  +------------------------------------------------------------------------+
//require_once('include/initialize.inc.php');
//$cfg['menu'] = 'playlist';

//authenticate('access_play');
//require_once('include/header.inc.php');
require_once('include/play.inc.php');

if ($cfg['player_type'] == NJB_MPD)	{
	$status 		= mpd('status');
	$listpos		= isset($status['song']) ? $status['song'] : 0;
	$file			= mpd('playlist');
	$hash			= md5(implode('<seperation>', $file));
	$listlength		= $status['playlistlength'];
	$bottom = ($listlength > 1) ? ($listlength - 1) : 0;
	$volume			= (isset($status['volume']) == false || $status['volume'] == -1) ? false : true;
	$max_volume		= 100;	
}
else
	message(__FILE__, __LINE__, 'error', '[b]Player not supported[/b]');


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
			//echo $artist;
		}
		else {
			//echo '<a href="index.php?action=view2&amp;artist=' . rawurlencode($table_track['track_artist']) . '&amp;order=year">' . html($table_track['track_artist']) . '</a>';
		}
}
	?>
	





<!-- info + control -->
<div id="info_area_mini">
<div id="timebar_mini"></div>
<div id="image_container_mini" title="Go to Now Playing">
	<!-- <div id="image_mini"> -->
		<a href="playlist.php"><img id="image_in_mini" src="image/transparent.gif" alt=""></a>
	<!-- </div> -->
</div>

<div id="file-info-mini">
		<div class="track_title_mini" id="track_artist_mini">&nbsp;</div>
		<div class="artist_mini" id="artist_mini">&nbsp;</div>
</div>

<!-- <div class="pl-track-info-rightMini"> -->

<!-- begin controll bar -->

<div id="media_control_mini">
	
	
<div class="control-rowMini">

	<div class="playlist_button"><div class="playlist_status_off" name="previous" id="previous" onclick="javascript:ajaxRequest('play.php?action=prev&amp;menu=playlist');">
		<i class="fa fa-fast-backward sign-ctrl"></i>
	</div></div>
	
	<div class="playlist_button"><div class="playlist_status_off" name="play" id="play" onclick="javascript:ajaxRequest('play.php?action=play&amp;menu=playlist', evaluateIsplaying);">
		<i class="fa fa-play sign-ctrl"></i>
	</div></div>
	
	<div class="playlist_button" style="display: none;"><div class="playlist_status_off" name="stop" id="stop" onclick="javascript:ajaxRequest('play.php?action=stop&amp;menu=playlist', evaluateIsplaying);">
		<i class="fa fa-stop sig"></i>
	</div></div>
	
	<div class="playlist_button" style=""><div class="playlist_status_off" name="next" id="next" onclick="javascript:ajaxRequest('play.php?action=next&amp;menu=playlist', evaluateIsplaying);">
		<i class="fa fa-fast-forward sign-ctrl"></i>
	</div></div>
	
</div>

</div>
<!-- </div> -->
<!-- end controll bar -->
</div>
<!-- end info + controll -->


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

ajaxRequest('play.php?action=playlistTrack&track_id=' + track_id[<?php echo $listpos; ?>] + '&menu=playlist', evaluateTrack);
ajaxRequest('play.php?action=playlistStatus&track_id=' + track_id[<?php echo $listpos; ?>] + '&menu=playlist', evaluateStatus);


/* function evaluateStatus(data) {
	var title = data.title;
	document.getElementById('title1').innerHTML =  title;
	var artist = data.track_artist;
	document.getElementById('artist1').innerHTML =  artist;
	evaluatePlaytime(data);
} */


function evaluateStatus(data) {
	// data.hash, data.miliseconds, data.listpos, data.volume
	// data.isplaying, data.repeat, data.shuffle, data.gain
	
	if (previous_hash != data.hash) {
		//window.location.href="<?php echo NJB_HOME_URL ?>playlist.php";
		location.reload(false);
		//window.location.href = window.location.href;
		//history.go();
	}
	//data.max = playtime[data.listpos];
	if (!current_track_id) { //track not found in DB, get data from MPD
		data.max = data.Time;
		var title = data.title;
		/* if (title.indexOf("action=streamTo") != -1) {
			title = data.name; 
		} */
		document.getElementById('track_artist_mini').innerHTML =  title;
		var rel_file = encodeURIComponent(data.relative_file);
		//console.log ("rel_file=" + rel_file);
		
		var query_artist = '';
		if (data.track_artist) {
			query_artist = data.track_artist;
		}
	}
	
	evaluateListpos(data.listpos);
	evaluatePlaytime(data);
	//evaluateRepeat(data.repeat);
	//evaluateShuffle(data.shuffle);
	evaluateIsplaying(data.isplaying, data.listpos);
	//evaluateVolume(data.volume);
	//evaluateGain(data.gain);
	//evaluateConsume(data);
	 
	
}


function evaluateListpos(listpos) {
	if (previous_listpos != listpos) {
		document.getElementById('timebar_mini').style.width = 0; 
		ajaxRequest('play.php?action=playlistTrack&track_id=' + track_id[listpos] + '&menu=playlist', evaluateTrack);
		previous_miliseconds = 0;
		previous_listpos = listpos;
	}
	else hideSpinner();
	//resizeImgContainer();
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
			/* document.getElementById('track' + idx + '_play').style.visibility = 'hidden';
			document.getElementById('time').innerHTML = formattedTime(0);
			document.getElementById('timebar').style.width = 0; */
			previous_miliseconds = 0;
		}
		else if (isplaying == 1) {
			// play	
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
		}
		previous_isplaying = isplaying;
		console.log('isplaying:' + isplaying + '; idx: ' + idx);
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
		$('#track_artist_mini').addClass('icon-anchor');
		$('#title').addClass('icon-anchor');
		$('#track_artist_mini').click(function(){
				window.location.href='index.php?action=view3all&track_ids=' + track_ids + '&other_title=' + other_title_enc;
				
			}
		);
		$( "#title" ).click(function() {
		  $( "#track_artist_mini" ).click();
		})
	}
	else {
		$('#track_artist_mini').removeClass('icon-anchor');
		$('#title').removeClass('icon-anchor');
		$('#track_artist_mini').off('click');
		$('#title').off('click');
	}
}

function evaluateTrack(data) {
	// data.artist, data.title, data.album, data.by, data.album_id, data.image_id
	$('#track_artist_mini').removeClass('icon-anchor');
	$('#title').removeClass('icon-anchor');
	$('#title1_wait_indicator').hide();
	$('#title_wait_indicator').hide();
	$('#fileInfoForDbTracks').css('visibility', 'visible');
	current_track_id = data.track_id;
	if (previous_track_id != data.track_id && data.track_id != null) {
		//console.log('previous_track_id=' + previous_track_id);
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
	
	
	//document.getElementById('tracktime').innerHTML = m + ':' + s;
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
	
	if (data.track_artist[0] == '&nbsp;') {
		document.getElementById('artist_mini').innerHTML = '&nbsp;';
		$("#artist_mini").hide();
	}
	else {
		document.getElementById('artist_mini').innerHTML = artist;
		$("#artist_mini").show();
	}
	// document.getElementById('artist1').innerHTML = (data.track_artist[0] == '&nbsp;') ? '&nbsp;' : artist;
	
	document.getElementById('track_artist_mini').innerHTML = data.title;
	var al = data.album;
	if (data.album_id) {
		/* var albumLink = '<a href="index.php?action=view3&album_id=' + data.album_id + '">' + data.album + '</a>';
		document.getElementById('album1').innerHTML = (data.album == '&nbsp;') ? '&nbsp' : 'from ' + albumLink; 
		document.getElementById('album').innerHTML = albumLink; */
	}
	else if (al.indexOf("://") > 0) {
		//e.g. stream from youtube 
		var albumLink = '<a href="' + data.album + '" target="_new">' + data.album + '</a>';
		//document.getElementById('album1').innerHTML = (data.album == '&nbsp;') ? '&nbsp' : 'from ' + albumLink; 
		//document.getElementById('album').innerHTML = albumLink;
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
	
	var query_artist = '';
	if (data.track_artist) {
		query_artist = data.track_artist;
	}
	if (data.album_id) {
		$("#image_in_mini").attr("src","image.php?image_id=" + data.image_id + "&track_id=" + data.track_id);
		//$("#image a").attr("href","index.php?action=view3&album_id=" + data.album_id);
		
	}
	else if (data.thumbnail) {
		//thumbnail e.g. from Youtube
		$("#image_in_mini").attr("src","image_crop.php?thumbnail=" + encodeURIComponent(data.thumbnail));
		//$("#image_in").attr("src",data.thumbnail);
		//$("#image a").attr("href",data.thumbnail);
	}
	else {
		document.getElementById('image_container_mini').innerHTML = '<a href="#"><img id="image_in_mini" src="<?php echo 'image/'; ?>large_file_not_found.png" alt=""></a>';
		$("#waitIndicatorImg").hide();
	}
}

function evaluatePlaytime(data) {
	// data.miliseconds, data.max, ....
		console.log("p_m: " + previous_miliseconds);
	if (previous_miliseconds != data.miliseconds) {
		var width_ = 0;
		var progress_bar_width = document.getElementById('info_area_mini').clientWidth;
		
		if (data.Time > 0)	width_ = Math.round(data.miliseconds / data.Time * progress_bar_width);
		if (width_ > progress_bar_width)	width_ = progress_bar_width;
		
		$('#timebar_mini').width(width_);
		previous_miliseconds = data.miliseconds;
		console.log(data.Time);
	}
}


$(document).ready(function() {
				
				$('#play').longpress(function(e) {
					ajaxRequest('play.php?action=stop&menu=playlist', evaluateIsplaying);
				}, function(e) {
				});				
});



</script>

