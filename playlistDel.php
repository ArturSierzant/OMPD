<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2018 Artur Sierzant                            |
//  | http://www.ompd.pl                                                     |
//  |                                                                        |
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


require_once('include/initialize.inc.php');
$cfg['menu'] = 'playlist';

authenticate('access_playlist');
require_once('include/play.inc.php');
require_once('include/header.inc.php');


global $cfg, $db;
$index = (int) get('index');
mpd('delete ' . $index);


?>

<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space left"></td>
	<td class="trackNumber">&nbsp;#&nbsp;</td>
	<td>Title</td>
	<td>Artist</td>
	<td>Genre</td>
	<td><?php if ($featuring) echo'Featuring'; ?></td><!-- optional featuring -->
	<td<?php if ($featuring) echo' class="textspace"'; ?>></td>
	<td class="icon"></td><!-- optional delete -->
	<td class="space"></td>
	<td align="right" class="time">Time</td>
	<td class="space right"></td>
</tr>
<tr class="line"><td colspan="11"></td></tr>
<?php
$playtime = array();
$track_id = array();
for ($i=0; $i < $listlength; $i++)
	{
	$query = mysqli_query($db,'SELECT track.title, track.artist, track.track_artist, track.featuring, track.miliseconds, track.track_id, track.genre, album.genre_id, track.audio_dataformat, track.audio_bits_per_sample, track.audio_sample_rate, track.album_id, track.number FROM track, album WHERE track.album_id=album.album_id AND track.relative_file = "' . mysqli_real_escape_string($db,$file[$i]) . '"');
	$table_track = mysqli_fetch_assoc($query);
	$playtime[] = (int) $table_track['miliseconds'];
	$track_id[] = (string) $table_track['track_id'];
	$genre_id[] = (string) $table_track['genre_id'];
	$number[] = (string) $table_track['number'];
	if (!isset($table_track['artist'])) {
		$table_track['artist']	= $file[$i];
		$table_track['title']	= 'Unknown';
	}
	$query2 = mysqli_query($db,'SELECT album, year, image_id FROM album WHERE album_id="' . $table_track['album_id'] . '"');
	$image_id = mysqli_fetch_assoc($query2);
?>
<tr class="<?php if ($i == $listpos) echo 'select'; else echo ($i & 1) ? 'even mouseover' : 'odd mouseover'; ?>" id="track<?php echo $i; ?>" style="display:table-row;">
	
	<td class="small_cover">
	<a id="track<?php echo $i; ?>_image" href="javascript:ajaxRequest('play.php?action=playIndex&amp;index=<?php echo $i ?>&amp;menu=playlist', evaluateListpos);"><img src="image.php?image_id=<?php echo $image_id['image_id'] ?>" alt="" width="50" height="50"></a></td>
	
	
	<td><a class="trackNumber" href="javascript:ajaxRequest('play.php?action=playIndex&amp;index=<?php echo $i ?>&amp;menu=playlist', evaluateListpos);" id="track<?php echo $i; ?>_number"><div class="trackNumber">&nbsp;<?php echo html($table_track['number']); ?>&nbsp;</div></a></td>
	
	<td><a href="javascript:ajaxRequest('play.php?action=playIndex&amp;index=<?php echo $i ?>&amp;menu=playlist', evaluateListpos);" id="track<?php echo $i; ?>_title"><div class="playlist_title"><?php echo html($table_track['title']) ?></div>
		<div class="playlist_title_album"><?php echo $image_id['album'] ?> (<?php echo $image_id['year'] ?>)</div>
	</a></td>
	
	<td>
	<a href="index.php?action=view2&order=artist&sort=asc&artist=<?php echo html($table_track['track_artist']) ?>"> <?php echo html($table_track['track_artist']) ?> </a>
	</td>
	
	<td>
	<a href="index.php?action=view2&order=artist&sort=asc&&genre_id=<?php echo $table_track['genre_id'] ?>"><?php echo $table_track['genre'] ?></a>
	</td>
	
	<td><?php if (isset($table_track['featuring'])) echo html($table_track['featuring']); ?></td>
	
	<td></td>
	
	<!-- <td id="track<?php echo $i; ?>_delete"><?php if ($cfg['access_play']) echo '<a href="javascript:delItemAjax(\'' . $i . '\');"><span class="typcn typcn-delete" style="font-size: 30px; color: #555555;"><span></a>'; ?></td> -->
	
	<td id="track<?php echo $i; ?>_delete"><?php if ($cfg['access_play']) echo '<a href="javascript:ajaxRequest(\'play.php?action=deleteIndex&amp;index=' . $i . '&amp;menu=playlist\',deletePLitem);"><span class="typcn typcn-delete" style="font-size: 30px; color: #555555;"><span></a>'; ?></td>
	
	<td></td>

	<td align="right"><?php if (isset($table_track['miliseconds'])) echo formattedTime($table_track['miliseconds']); ?></td>
	<td></td>
</tr>
<?php
	}
?>
</table>


<script type="text/javascript">
<!--
var previous_hash			= '<?php echo $hash; ?>';
var previous_listpos		= <?php echo $listpos; ?>;
var previous_isplaying		= -1; // force update
var previous_repeat			= -1;
var previous_shuffle		= -1;
var previous_gain			= -1;
var previous_miliseconds	= -1;
var previous_volume			= -1;
var playtime				= <?php echo safe_json_encode($playtime); ?>;
var track_id				= <?php echo safe_json_encode($track_id); ?>;
var timer_id				= 0;
var timer_function			= 'ajaxRequest("play.php?action=playlistStatus&menu=playlist", evaluateStatus)';
//var timer_function			= '';
var timer_delay				= 1000;
var list_length				= <?php echo $listlength;?>;
//console.trace();

function deletePLitem(data) {
	//console.trace();
	
	var idx = parseInt(data.index);
	//var idx = parseInt(idx2del);
	console.log ("idx: %s", idx)
	
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
		
		var newHref = 'javascript:ajaxRequest(\'play.php?action=playIndex&amp;index=' + j + '&amp;menu=playlist\', evaluateListpos);';
		
		document.getElementById('track' + j + '_image').href = newHref;
		document.getElementById('track' + j + '_number').href = newHref;
		document.getElementById('track' + j + '_title').href = newHref;
		document.getElementById('track' + j + '_delete').innerHTML='<a href="javascript:ajaxRequest(\'play.php?action=deleteIndex&amp;index=' + j + '&amp;menu=playlist\',deletePLitem);"><span class="typcn typcn-delete" style="font-size: 30px; color: #555555;"><span></a>';
		
	}


}

function initialize() {
	ajaxRequest('play.php?action=playlistTrack&track_id=' + track_id[<?php echo $listpos; ?>] + '&menu=playlist', evaluateTrack);
	ajaxRequest('play.php?action=playlistStatus&menu=playlist', evaluateStatus);
}


function evaluateStatus(data) {
	// data.hash, data.miliseconds, data.listpos, data.volume
	// data.isplaying, data.repeat, data.shuffle, data.gain
	if (previous_hash != data.hash) {
		//window.location.href="<?php echo NJB_HOME_URL ?>playlist.php";
		location.reload(false);
	}
	data.max = playtime[data.listpos];
	evaluateListpos(data.listpos);
	evaluatePlaytime(data);
	evaluateRepeat(data.repeat);
	evaluateShuffle(data.shuffle);
	evaluateIsplaying(data.isplaying);
	evaluateVolume(data.volume);
	evaluateGain(data.gain);
}


function evaluateListpos(listpos) {
	if (previous_listpos != listpos) {
		document.getElementById('track' + previous_listpos).className = (previous_listpos & 1) ? 'even mouseover' : 'odd mouseover';
		document.getElementById('track' + listpos).className = 'select';
		document.getElementById('time').innerHTML = formattedTime(0);
		document.getElementById('timebar').style.width = 0;
		ajaxRequest('play.php?action=playlistTrack&track_id=' + track_id[listpos] + '&menu=playlist', evaluateTrack);
		previous_miliseconds = 0;
		previous_listpos = listpos;
	}
}


function evaluatePlaytime(data) {
	// data.miliseconds, data.max, ....
	if (previous_miliseconds != data.miliseconds) {
		document.getElementById('time').innerHTML = formattedTime(data.miliseconds);
		var width = 0;
		if (data.max > 0)	width = Math.round(data.miliseconds / data.max * 200);
		if (width > 200)	width = 200;
		document.getElementById('timebar').style.width = width;
		previous_miliseconds = data.miliseconds;
	}
}


function evaluateVolume(volume) {
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


function evaluateIsplaying(isplaying) {
	if (previous_isplaying != isplaying) {
		if (isplaying == 0) {
			// stop
			document.getElementById('play').src = '<?php echo $cfg['img']; ?>playlist_play_off.png';
			document.getElementById('pause').src = '<?php echo $cfg['img']; ?>playlist_pause_off.png';
			document.getElementById('time').innerHTML = formattedTime(0);
			document.getElementById('timebar').style.width = 0;
			previous_miliseconds = 0;
		}
		else if (isplaying == 1) {
			// play
			document.getElementById('play').src = '<?php echo $cfg['img']; ?>playlist_play_on.png';
			document.getElementById('pause').src = '<?php echo $cfg['img']; ?>playlist_pause_off.png';
		}
		else if (isplaying == 3) {
			// pause
			document.getElementById('play').src = '<?php echo $cfg['img']; ?>playlist_play_off.png';
			document.getElementById('pause').src = '<?php echo $cfg['img']; ?>playlist_pause_on.png';
		}
		previous_isplaying = isplaying
	}
}


function evaluateRepeat(repeat) {
	if (previous_repeat != repeat) {
		if (repeat == 0) document.getElementById('repeat').src = '<?php echo $cfg['img']; ?>playlist_repeat_off.png';
		if (repeat == 1) document.getElementById('repeat').src = '<?php echo $cfg['img']; ?>playlist_repeat_on.png';
		previous_repeat = repeat;
	}
}


function evaluateShuffle(shuffle) {
	if (previous_shuffle != shuffle) {
		if (shuffle == 0) document.getElementById('shuffle').src = '<?php echo $cfg['img']; ?>playlist_shuffle_off.png';
		if (shuffle == 1) document.getElementById('shuffle').src = '<?php echo $cfg['img']; ?>playlist_shuffle_on.png';
		previous_shuffle = shuffle;
	}
}


function evaluateGain(gain) {
	if (previous_gain != gain) {
		if (gain == 'off')		document.getElementById('gain').src = '<?php echo $cfg['img']; ?>playlist_gain_off.png';
		if (gain == 'album')	document.getElementById('gain').src = '<?php echo $cfg['img']; ?>playlist_gain_album.png';
		if (gain == 'auto')		document.getElementById('gain').src = '<?php echo $cfg['img']; ?>playlist_gain_auto.png';
		if (gain == 'track')	document.getElementById('gain').src = '<?php echo $cfg['img']; ?>playlist_gain_track.png';
		previous_gain = gain;
	}
}


function evaluateTrack(data) {
	// data.artist, data.title, data.album, data.by, data.album_id, data.image_id
	document.getElementById('artist').innerHTML = '<a href="index.php?action=view2&order=artist&sort=asc&artist=' + data.album_artist + '">' + data.track_artist + '</a>'; 
	
	document.getElementById('title').innerHTML = data.number + '. ' + data.title;
	document.getElementById('album').innerHTML = '<a href="index.php?action=view3&album_id=' + data.album_id + '">' + data.album + '</a>'; 
	document.getElementById('year').innerHTML = '<a href="index.php?action=view2&order=artist&sort=asc&year=' + data.year + '">' + data.year + '</a>';
	document.getElementById('genre').innerHTML = '<a href="index.php?action=view2&order=artist&sort=asc&&genre_id=' + data.genre_id + '">' + data.genre + '</a>';
	
	document.getElementById('parameters').innerHTML =  data.audio_dataformat + ' (' + data.audio_bits_per_sample + '/' + data.audio_sample_rate/1000 + ') [' + data.audio_profile + ']';
	
	document.getElementById('lyrics').innerHTML = '<a href="ridirect.php?query_type=lyrics&q=' + data.track_artist + '+' + data.title + '+lyrics site:.pl" target="_blank"><img src="<?php echo $cfg['img'] ?>small_search.png" alt="" class="small space">Search</a>'; 
	//document.getElementById('lyrics').innerHTML = '<a href="http://www.google.pl/search?q=' + data.track_artist + '+' + data.title + '+lyrics site:.pl" target="_blank"><img src="<?php echo $cfg['img'] ?>small_search.png" alt="" class="small space">Search</a>'; 
	
	if (data.album_id) document.getElementById('image').innerHTML = '<a href="index.php?action=view3&album_id=' + data.album_id + '"><img src="' + data.image_front + '" alt="" width="250" height="250"  onMouseOver="return overlib(\'Go to album\');" onMouseOut="return nd();"><\/a>';
	else document.getElementById('image').innerHTML = '<img src="<?php echo $cfg['img']; ?>large_file_not_found.png" alt="" width="100" height="100">';
}
//-->
</script>