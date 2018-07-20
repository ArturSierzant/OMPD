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
require_once('include/play.inc.php');
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
		<span id="track_number">&nbsp;</span><span class="track_title_mini" id="track_title_mini">&nbsp;</span>
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
//var previous_hash			= '<?php echo $hash; ?>';
//var previous_listpos		= <?php echo $listpos; ?>;
var previous_hash			= '';
var previous_listpos		= '';
var previous_isplaying		= -1; // force update
var previous_repeat			= -1;
var previous_shuffle		= -1;
var previous_gain			= -1;
var previous_miliseconds	= -1;
var previous_track_id		= 'ff';
//var playtime				= <?php echo safe_json_encode($playtime); ?>;
//var track_id				= <?php echo safe_json_encode($track_id); ?>;
var track_id				= '';
var current_track_id		= '';
var timer_id				= 0;
var timer_function			= 'ajaxRequest("play.php?action=playlistStatus&menu=playlist", evaluateStatus)';
var timer_delay				= 1000;
//var list_length				= <?php echo $listlength;?>;
var list_length				= '';
//console.trace();
var fromPosition			= -1
 
var testing = '<?php echo $cfg['testing']; ?>';

getTrackIDs();
//ajaxRequest('ajax-playlist-mini.php', evaluatePlaylist);


//ajaxRequest('play.php?action=playlistTrack&track_id=' + track_id[<?php echo $listpos; ?>] + '&menu=playlist', evaluateTrack);

//ajaxRequest('play.php?action=playlistStatus&track_id=' + track_id[<?php echo $listpos; ?>] + '&menu=playlist', evaluateStatus);

ajaxRequest('play.php?action=playlistTrack&track_id=' + track_id[previous_listpos] + '&menu=playlist', evaluateTrack);

ajaxRequest('play.php?action=playlistStatus&track_id=' + track_id[previous_listpos] + '&menu=playlist', evaluateStatus);


function getTrackIDs() {
	$.ajax({
					url: 'ajax-playlist-mini.php',
					type: 'GET',
					async: false,
					//cache: false,
					//timeout: 1000,
					dataType : 'json',
					error: function(){
							return true;
					},
					success: function(json){ 
							evaluateTrackIDs(json);
					}
	});
}

	
function evaluateTrackIDs(data) {
	track_id = data.track_id;
	previous_hash = data.hash;
	previous_listpos = data.listpos;
	list_length = data.listlength;
	}

function evaluateStatus(data) {
	// data.hash, data.miliseconds, data.listpos, data.volume
	// data.isplaying, data.repeat, data.shuffle, data.gain
	
	if (previous_hash != data.hash) {
		getTrackIDs();
		//ajaxRequest('play.php?action=playlistTrack&track_id=' + track_id[<?php echo $listpos; ?>] + '&menu=playlist', evaluateTrack);
		ajaxRequest('play.php?action=playlistTrack&track_id=' + track_id[previous_listpos] + '&menu=playlist', evaluateTrack);
		//location.reload(false);
		//evaluateTrack(data);
	}
	
	document.getElementById('track_number').innerHTML =  (data.listpos + 1) + "/" + data.totalTracks + ". ";
	//document.getElementById('track_title_mini').innerHTML =  data.title;
	
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
		//getTrackIDs();
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
	
	if (data.other_track_version) {
		var track_ids = '';
		for (var i = 0; i < data['track_ids'].length; i++) {
			track_ids = track_ids + data['track_ids'][i] + ";";
		};
		other_title_enc = encodeURI(data['title']);
		$('#track_title_mini').addClass('icon-anchor');
		$('#title').addClass('icon-anchor');
		$('#track_title_mini').click(function(){
				window.location.href='index.php?action=view3all&track_ids=' + track_ids + '&other_title=' + other_title_enc;
				
			}
		);
		$( "#title" ).click(function() {
		  $( "#track_title_mini" ).click();
		})
	}
}

function evaluateTrack(data) {
	// data.artist, data.title, data.album, data.by, data.album_id, data.image_id
	// $('#track_title_mini').removeClass('icon-anchor');
	current_track_id = data.track_id;
	if (previous_track_id != data.track_id && data.track_id != null) {
		$('#track_title_mini').removeClass('icon-anchor');
		$('#title').removeClass('icon-anchor');
		$('#track_title_mini').off('click');
		$('#title').off('click');
		ajaxRequest('ajax-track-version.php?track_id=' + data.track_id + '&menu=playlist', evaluateTrackVersion);
		previous_track_id = data.track_id;
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
			if (data.track_artist[0] != '&nbsp;') {
			artist = '<a href="index.php?action=view2&order=year&sort=asc&artist=' + encodeURIComponent(data.track_artist_url[0]) + '">' + data.track_artist[0] + '</a>';
			}
		}
	}
	else {
		artist = '<a href="index.php?action=view2&order=year&sort=asc&artist=' + data.track_artist_url_all + '">' + data.track_artist + '</a>';
	}
	
	if (data.track_artist[0] == '&nbsp;') {
		document.getElementById('artist_mini').innerHTML = '&nbsp;';
		//$("#artist_mini").hide();
	}
	else {
		document.getElementById('artist_mini').innerHTML = artist;
		//$("#artist_mini").show();
	}
	$("#artist_mini").show();
	document.getElementById('track_title_mini').innerHTML = data.title;
	var al = data.album;
	if (data.album_id) {
		var albumLink = '<a href="index.php?action=view3&album_id=' + data.album_id + '">' + data.album + '</a>';
		document.getElementById('artist_mini').innerHTML = (data.album == '&nbsp;') ? '&nbsp' : 'by ' + document.getElementById('artist_mini').innerHTML + ' from ' + albumLink; 
	}
	else if (al.indexOf("://") > 0 && al.indexOf("://") < 6) {
		//e.g. stream from youtube 
		var albumLink = '<a href="' + data.album + '" target="_new">' + data.album + '</a>';
		document.getElementById('artist_mini').innerHTML = albumLink;
	}
	else if (data.relative_file && artist != '') {
		var albumLink = 'by ' + artist + ' from ' + '<a href="browser.php?dir=' + data.relative_file + '">' + data.album + '</a>';
		document.getElementById('artist_mini').innerHTML = albumLink;
	}
	else if (data.relative_file) {
		var albumLink = '<a href="browser.php?dir=' + data.relative_file + '">' + data.album + '</a>';
		document.getElementById('artist_mini').innerHTML = albumLink;
		}
	else if (data.album != '&nbsp;' && artist != '') {
		document.getElementById('artist_mini').innerHTML = 'by ' + artist + ' from ' + data.album;
	}
	else if (data.album != '&nbsp;') {
		document.getElementById('artist_mini').innerHTML = data.album;
	}
	else {
		/* document.getElementById('artist_mini').innerHTML = (data.album == '&nbsp;') ? '&nbsp' : data.album; */
		$("#artist_mini").hide();
	}
	
	console.log ('art: ' + artist);
	
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
		document.getElementById('image_container_mini').innerHTML = '<a href="playlist.php"><img id="image_in_mini" src="<?php echo 'image/'; ?>large_file_not_found.png" alt=""></a>';
	}
}

function evaluatePlaytime(data) {
	// data.miliseconds, data.max, ....
		//console.log("p_m: " + previous_miliseconds);
	if (previous_miliseconds != data.miliseconds) {
		var width_ = 0;
		var progress_bar_width = document.getElementById('info_area_mini').clientWidth;
		
		if (data.Time > 0)	width_ = Math.round(data.miliseconds / data.Time * progress_bar_width);
		if (width_ > progress_bar_width)	width_ = progress_bar_width;
		
		$('#timebar_mini').width(width_);
		previous_miliseconds = data.miliseconds;
		}
}


$(document).ready(function() {
				
				$('#play').longpress(function(e) {
					ajaxRequest('play.php?action=stop&menu=playlist', evaluateIsplaying);
				}, function(e) {
				});				
});



</script>

