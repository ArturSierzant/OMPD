<?php
//  +------------------------------------------------------------------------+
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
//  | genre.php                                                              |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
$cfg['menu'] = 'Library';

$action			= getpost('action');

if		($action == 'edit')					edit();
elseif	($action == 'save')					save();
elseif	($action == 'genreStructure')		genreStructure();
elseif	($action == 'editGenreStructure')	editGenreStructure();
elseif	($action == 'saveGenreStructure')	{saveGenreStructure();	genreStructure();}
elseif	($action == 'addGenre')				addGenre();
elseif	($action == 'saveAddGenre')			{saveAddGenre();		genreStructure();}
elseif	($action == 'deleteGenre')			{deleteGenre();			genreStructure();}
elseif	($action == 'blacklist')			blacklist();
elseif	($action == 'saveBlacklist')		{saveBlacklist();		blacklist();}
else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');
exit();




//  +------------------------------------------------------------------------+
//  | Genre tree                                                             |
//  +------------------------------------------------------------------------+
function genreTree($genre_id, &$genre_id_array, &$genre_array) {
	global $db;
	$query = mysqli_query($db,"SELECT * FROM genre WHERE genre != '' ORDER BY genre");
	
	while ($genre = mysqli_fetch_assoc($query)) {
		$genre_id_array[] = $genre['genre_id'];
		$genre_array[]    = $genre['genre'];
	    //genreTree($genre['genre_id'], $genre_id_array, $genre_array);
	}
}




//  +------------------------------------------------------------------------+
//  | Edit genre                                                             |
//  +------------------------------------------------------------------------+
function edit() {
	global $cfg, $db;
	authenticate('access_admin');
	
	$album_id		= get('album_id');
	$album_id_array	= post('album_id_array');
	$genre_id		= post('genre_id');
	$artist			= post('artist');
	$filter			= post('filter');
	$order			= post('order');
	$sort			= post('sort');
	
	if ($album_id && $cfg['album_edit_genre'] == false)
		message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]Edit album genre disabled');
	elseif ($album_id) {
		$referer = 'index.php?action=view3&amp;album_id=' . rawurlencode($album_id);
		$album_id_array = array($album_id);
		
		$query = mysqli_query($db,'SELECT artist_alphabetic, artist, album, year, month
			FROM album
			WHERE album_id = "' . mysqli_real_escape_string($db,$album_id) . '"');
		$album = mysqli_fetch_assoc($query);
		
		// formattedNavigator
		$nav			= array();
		$nav['name'][]	= 'Media';
		$nav['url'][]	= 'index.php';
		$nav['name'][]	= $album['artist_alphabetic'];
		$nav['url'][]	= 'index.php?action=view2&amp;artist=' . rawurlencode($album['artist_alphabetic']);
		$nav['name'][]	= $album['album'];
		$nav['url'][]	= 'index.php?action=view3&amp;album_id=' . rawurlencode($album_id);
		$nav['name'][]	= 'Edit genre';
	}
	else {
		$referer = 'index.php?action=view2';
		$referer .= ($artist == '') ? '&amp;genre_id=' . rawurlencode($genre_id) : '&amp;artist=' . rawurlencode($artist);
		$referer .= '&amp;filter=' . rawurlencode($filter);
		$referer .= '&amp;order=' . rawurlencode($order);
		$referer .= '&amp;sort=' . rawurlencode($sort);
		
		// formattedNavigator
		$nav			= array();
		$nav['name'][]	= 'Media';
		$nav['url'][]	= 'index.php';
		$nav['name'][]	= 'Back';
		$nav['url'][]	= $referer;
		$nav['name'][]	= 'Edit genre';
	}
	
	if (empty($album_id_array) || is_array($album_id_array) == false) {
		$referer = str_replace('&amp;', '&', $referer);
		message(__FILE__, __LINE__, 'warning', '[b]No album selected[/b][br]Select at least one album.[br][url=' . $referer . '][img]small_back.png[/img]Back to previous page[/url]');
	}
	
	require_once('include/header.inc.php');
?>
<form action="genre.php" method="post" name="genreform" id="genreform">
	<input type="hidden" name="action" value="save">
	<input type="hidden" name="album_id" value="<?php echo html($album_id); ?>">
	<input type="hidden" name="sign" value="<?php echo $cfg['sign']; ?>">
	<input type="hidden" name="artist" value="<?php echo html($artist); ?>">
	<input type="hidden" name="filter" value="<?php echo html($filter); ?>">
	<input type="hidden" name="order" value="<?php echo html($order); ?>">
	<input type="hidden" name="sort" value="<?php echo html($sort); ?>">
	<input type="hidden" name="genre_id" value="">
<?php
	for ($i=0; $i < count($album_id_array); $i++)
		echo "\t" . '<input type="hidden" name="album_id_array[]" value="' . html($album_id_array[$i]) . '">' . "\n";
	
	function escape_mysql($string) {
		global $db;
		return mysqli_real_escape_string($db,$string);
	}
	
	$list = array_map('escape_mysql', $album_id_array);
	$list = '"' . implode('","', $list) . '"';
	
	$query = mysqli_query($db,'SELECT genre_id, COUNT(genre_id) AS counter
		FROM album
		WHERE genre_id != "" AND album_id IN (' . $list . ')
		GROUP BY genre_id
		ORDER BY counter DESC');
	$check_genre_id = mysqli_fetch_assoc($query);
	$check_genre_id = $check_genre_id['genre_id'];
	
	$genre_id_array = array();
	$genre_array 	= array();
	genreTree('', $genre_id_array, $genre_array);
	$i=0;
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>Genre</td>
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="3"></td></tr>
<tr class="<?php if (empty($check_genre_id)) echo 'select'; else echo ($i & 1) ? 'even mouseover' : 'odd mouseover'; $i++ ?>">
	<td></td>
	<td><a href="javascript:document.genreform.genre_id.value='';document.genreform.submit();"><img src="<?php echo $cfg['img']; ?>small_genre.png" alt="" class="small space">Root</a></td>
	<td></td>
</tr>
<?php
	foreach ($genre_array as $key => $genre) {
		$genre_id = $genre_id_array[$key];
		$lenght = strlen($genre_id);
		$tab = str_repeat('&nbsp;', $lenght * 4); 
?>
<tr class="<?php if ($check_genre_id == $genre_id) echo 'select'; else echo ($i & 1) ? 'even mouseover' : 'odd mouseover'; $i++ ?>">
	<td></td>
	<td><?php echo $tab; ?><a href="javascript:document.genreform.genre_id.value='<?php echo $genre_id; ?>';document.genreform.submit();"><img src="<?php echo $cfg['img']; ?>small_genre.png" alt="" class="small space"><?php echo html($genre); ?></a></td>
	<td></td>
</tr>
<?php
	} ?>
</table>
</form>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Save                                                                   |
//  +------------------------------------------------------------------------+
function save() {
	global $db;
	authenticate('access_admin', false, true, true);
	
	$album_id		= post('album_id');
	$album_id_array	= post('album_id_array');
	$genre_id		= post('genre_id');
	$artist			= post('artist');
	$filter			= post('filter');
	$order			= post('order');
	$sort			= post('sort');
	
	for ($i=0; $i < count($album_id_array); $i++) {
		mysqli_query($db,'UPDATE album
			SET genre_id	= "' . mysqli_real_escape_string($db,$genre_id) . '"
			WHERE album_id	= "' . mysqli_real_escape_string($db,$album_id_array[$i]) . '"');
	}
	
	if ($album_id) {
		$referer = 'index.php?action=view3&album_id=' . rawurlencode($album_id);
	}
	else {
		if ($genre_id == '')
			$genre_id = '~';
		$referer = 'index.php?action=view2';
		$referer .= ($artist == '') ? '&genre_id=' . rawurlencode($genre_id) : '&artist=' . rawurlencode($artist);
		$referer .= '&filter=' . rawurlencode($filter);
		$referer .= '&order=' . rawurlencode($order);
		$referer .= '&sort=' . rawurlencode($sort);
	}
	
	header('Location: ' . NJB_HOME_URL . $referer);
	exit();
}




//  +------------------------------------------------------------------------+
//  | Genre Structure                                                        |
//  +------------------------------------------------------------------------+
function genreStructure() {
	global $cfg, $db;
	authenticate('access_admin');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Genre structure';
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>Genre</td>
	<td class="textspace"></td>
	<td><a href="genre.php?action=addGenre" onMouseOver="return overlib('Add genre');" onMouseOut="return nd();"><img src="<?php echo $cfg['img']; ?>small_header_new.png" alt="" class="small"></a></td>
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<?php
	$i=0;
	$genre_id_array = array();
	$genre_array 	= array();
	genreTree('', $genre_id_array, $genre_array);
	foreach ($genre_array as $key => $genre) {
		$genre_id = $genre_id_array[$key];
		$lenght = strlen($genre_id);
		$tab = str_repeat('&nbsp;', ($lenght - 1) * 4); ?>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?> mouseover">
	<td></td>
	<td><?php echo $tab; ?><a href="genre.php?action=editGenreStructure&amp;genre_id=<?php echo $genre_id; ?>" onMouseOver="return overlib('Edit');" onMouseOut="return nd();"><img src="<?php echo $cfg['img']; ?>small_edit.png" alt="" class="small space"><?php echo html($genre); ?></a></td>
	<td></td>
	<td><a href="genre.php?action=deleteGenre&amp;genre_id=<?php echo $genre_id; ?>&amp;sign=<?php echo $cfg['sign']; ?>" onClick="return confirm('Are you sure you want to delete genre: <?php echo html($genre); ?>?');" onMouseOver="return overlib('Delete');" onMouseOut="return nd();"><img src="<?php echo $cfg['img']; ?>small_delete.png" alt="" class="small"></a></td>
	<td></td>
</tr>
<?php
	} ?>
</table>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Edit genre structure                                                   |
//  +------------------------------------------------------------------------+
function editGenreStructure() {
	global $cfg, $db;
	authenticate('access_admin');
	
	$genre_id = get('genre_id');
	
	if (preg_match('#[^a-z]#', $genre_id))
		message(__FILE__, __LINE__, 'error', '[b]This is not a valid genre_id:[/b][br]' . $genre_id);
	
	$query = mysqli_query($db,'SELECT genre FROM genre WHERE genre_id = "' . mysqli_real_escape_like($genre_id) . '"');
	$temp = mysqli_fetch_assoc($query);
	$genre = $temp['genre'];
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Genre structure';
	$nav['url'][]	= 'genre.php?action=genreStructure';
	$nav['name'][]	= 'Edit genre';
	require_once('include/header.inc.php');
?>
<form action="genre.php" method="post">
	<input type="hidden" name="action" value="saveGenreStructure">
	<input type="hidden" name="genre_id" value="<?php echo $genre_id; ?>">
	<input type="hidden" name="sign" value="<?php echo $cfg['sign']; ?>">
<table cellspacing="0" cellpadding="1">
<tr>
	<td>Name:</td>
	<td class="textspace"></td>
	<td><input type="text" name="genre" value="<?php echo $genre; ?>" maxlength="255" class="edit"></td>
</tr>
<tr>
	<td>Parent:</td>
	<td class="textspace"></td>
	<td>
<select name="new_genre_id" class="edit">
	<option value="">Root</option>
<?php
	genreTree('', $genre_id_array, $genre_array);
	foreach ($genre_array as $key => $genre) {
		$new_genre_id = $genre_id_array[$key];
		$lenght = strlen($new_genre_id);
		$tab = str_repeat('&nbsp;', $lenght * 4);
		if ($genre_id != substr($new_genre_id, 0, strlen($genre_id))) { ?>
		<option value="<?php echo $new_genre_id ; ?>"<?php if ($new_genre_id == substr($genre_id, 0, -1)) echo ' selected'; ?>><?php echo $tab . html($genre); ?></option>
<?php
		}
	} ?>
</select>
	</td>
</tr>
<tr class="textspace"><td colspan="3"></td></tr>
<tr>
	<td colspan="2"></td>
	<td>
		<input type="image" src="<?php echo $cfg['img']; ?>button_save.png" class="space">
		<a href="genre.php?action=genreStructure" class="align"><img src="<?php echo $cfg['img']; ?>button_cancel.png" alt="" class="align"></a>
	</td>
</tr>
</table>
</form>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Save genre structure                                                   |
//  +------------------------------------------------------------------------+
function saveGenreStructure() {
	global $cfg, $db;
	authenticate('access_admin', false, true);
	
	$genre			= post('genre');
	$genre_id		= post('genre_id');
	$new_genre_id	= post('new_genre_id');
	
	
	if (preg_match('#[^a-z]#', $genre_id))
		message(__FILE__, __LINE__, 'error', '[b]This is not a valid genre_id:[/b][br]' . $genre_id);
	
	if (preg_match('#[^a-z]#', $new_genre_id))
		message(__FILE__, __LINE__, 'error', '[b]This is not a valid genre_id:[/b][br]' . $new_genre_id);
	
	if ($genre_id == substr($new_genre_id, 0, strlen($genre_id)))
		message(__FILE__, __LINE__, 'error', '[b]Failed to set this genre.[/b]');
	
	if ($genre == '')
		message(__FILE__, __LINE__, 'warning', '[b]Genre name is to short[/b][br][url=genre.php?action=editGenreStructure&genre_id=' . $genre_id . '][img]small_back.png[/img]Back to previous page[/url]');
	
	
	if ($new_genre_id == substr($genre_id, 0, -1)) {
		mysqli_query($db,'UPDATE genre
			SET genre		= "' . mysqli_real_escape_string($db,$genre) . '"
			WHERE genre_id	= "' . mysqli_real_escape_string($db,$genre_id) . '"');
	}
	else {
		// Loop trough source genre's
		$query1 = mysqli_query($db,'SELECT genre, genre_id
			FROM genre
			WHERE genre_id LIKE "' . mysqli_real_escape_like($genre_id) . '%"
			ORDER BY genre_id');
		while ($result1 = mysqli_fetch_assoc($query1)) {
			if ($genre_id == $result1['genre_id']) {
				// Get first available target genre
				$previous_ord = ord('a') - 1;
				$query2 = mysqli_query($db,'SELECT genre_id
					FROM genre
					WHERE genre_id LIKE "' . mysqli_real_escape_like($new_genre_id) . '_"
					ORDER BY genre_id');
				while ($result2 = mysqli_fetch_assoc($query2)) {
					if (ord(substr($result2['genre_id'], -1)) - $previous_ord > 1)
						break;
					$previous_ord = ord(substr($result2['genre_id'], -1));
				}
				if ($previous_ord >= ord('z'))
					message(__FILE__, __LINE__, 'error', '[b]Maximum 26 genre\'s per level[/b][br][url=genre.php?action=editGenreStructure&genre_id=' . $genre_id . '][img]small_back.png[/img]Back to previous page[/url]');
				$old_genre_id	= $genre_id;
				$new_genre_id	= $new_genre_id . chr($previous_ord + 1);
				$new_root_id	= $new_genre_id;
			}
			else {
				$genre			= $result1['genre'];
				$old_genre_id	= $result1['genre_id'];
				$new_genre_id	= $new_root_id . substr($old_genre_id, strlen($genre_id));
			}
			
			mysqli_query($db,'UPDATE album
				SET genre_id = "' . mysqli_real_escape_string($db,$new_genre_id) . '"
				WHERE genre_id = "' . mysqli_real_escape_string($db,$old_genre_id) . '"');
			
			mysqli_query($db,'UPDATE genre
				SET genre_id	= "' . mysqli_real_escape_string($db,$new_genre_id) . '",
				genre			= "' . mysqli_real_escape_string($db,$genre) . '"
				WHERE genre_id	= "' . mysqli_real_escape_string($db,$old_genre_id) . '"');
		}
	}
}




//  +------------------------------------------------------------------------+
//  | Add genre                                                              |
//  +------------------------------------------------------------------------+
function addGenre()
{
	global $cfg, $db;
	authenticate('access_admin');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Genre structure';
	$nav['url'][]	= 'genre.php?action=genreStructure';
	$nav['name'][]	= 'Add genre';
	require_once('include/header.inc.php');
?>
<form action="genre.php" method="post">
	<input type="hidden" name="action" value="saveAddGenre">
	<input type="hidden" name="sign" value="<?php echo $cfg['sign']; ?>">
<table cellspacing="0" cellpadding="1">
<tr>
	<td>Name:</td>
	<td class="textspace"></td>
	<td><input type="text" name="genre" maxlength="255" class="edit"></td>
</tr>
<tr>
	<td>Parent:</td>
	<td class="textspace"></td>
	<td>
<select name="genre_id" class="edit">
	<option value="" selected>Root</option>
<?php
	genreTree('', $genre_id_array, $genre_array);
	foreach ($genre_array as $key => $genre) {
		$genre_id = $genre_id_array[$key];
		$lenght = strlen($genre_id);
		$tab = str_repeat('&nbsp;', $lenght * 4); ?>
	<option value="<?php echo $genre_id ; ?>"><?php echo $tab . html($genre); ?></option>
<?php
	} ?>
</select>
	</td>
</tr>
<tr class="textspace"><td colspan="3"></td></tr>
<tr>
	<td colspan="2"></td>
	<td>
		<input type="image" src="<?php echo $cfg['img']; ?>button_save.png" class="space">
		<a href="genre.php?action=genreStructure" class="align"><img src="<?php echo $cfg['img']; ?>button_cancel.png" alt="" class="align"></a>
	</td>
</tr>
</table>
</form>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Save add genre                                                         |
//  +------------------------------------------------------------------------+
function saveAddGenre() {
	global $cfg, $db;
	authenticate('access_admin', false, true);
	
	$genre		= post('genre');
	$genre_id 	= post('genre_id');
	
	if (preg_match('#[^a-z]#', $genre_id))
		message(__FILE__, __LINE__, 'error', '[b]This is not a valid genre_id:[/b][br]' . $genre_id);
	
	if ($genre == '')
		message(__FILE__, __LINE__, 'warning', '[b]Genre name to short[/b][br][url=genre.php?action=addGenre][img]small_back.png[/img]Back to previous page[/url]');
	
	$previous_ord = ord('a') - 1;
	$query = mysqli_query($db,'SELECT genre, genre_id
		FROM genre
		WHERE genre_id LIKE "' . mysqli_real_escape_like($genre_id) . '_"
		ORDER BY genre_id');
	
	while ($result = mysqli_fetch_assoc($query)) {
		if (ord(substr($result['genre_id'], -1)) - $previous_ord > 1)
			break;
		$previous_ord = ord(substr($result['genre_id'], -1));
	}
	
	if ($previous_ord >= ord('z'))
		message(__FILE__, __LINE__, 'error', '[b]Maximum 26 genre\'s per level[/b][br][url=genre.php?action=addGenre][img]small_back.png[/img]Back to previous page[/url]');
	$genre_id = $genre_id . chr($previous_ord + 1);
	
	mysqli_query($db,'INSERT INTO genre (genre, genre_id)
		VALUES ("' . mysqli_real_escape_string($db,$genre) . '",
		"' . mysqli_real_escape_string($db,$genre_id) . '")');
}




//  +------------------------------------------------------------------------+
//  | Delete genre                                                           |
//  +------------------------------------------------------------------------+
function deleteGenre() {
	global $cfg, $db;
	authenticate('access_admin', false, true);
	
	$genre_id = get('genre_id');
	
	$target_genre_id = substr($genre_id, 0, -1);
	mysqli_query($db,'UPDATE album
		SET genre_id = "' . mysqli_real_escape_string($db,$target_genre_id) . '"
		WHERE genre_id LIKE "' . mysqli_real_escape_like($genre_id) . '%"');
	
	mysqli_query($db,'DELETE FROM genre 
		WHERE genre_id LIKE "' . mysqli_real_escape_like($genre_id) . '%"');
}




//  +------------------------------------------------------------------------+
//  | Blacklist                                                              |
//  +------------------------------------------------------------------------+
function blacklist() {
	global $cfg, $db;
	authenticate('access_media');
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Media';
	$nav['url'][]	= 'index.php';
	$nav['name'][]	= 'Random';
	
	$blacklist = explode(',', $cfg['random_blacklist']);
	
	require_once('include/header.inc.php');
?>
<form id="frmBlacklist" action="genre.php" method="post">
	<input type="hidden" name="action" value="saveBlacklist">
	<input type="hidden" name="sign" value="<?php echo $cfg['sign']; ?>">
<table cellspacing="0" cellpadding="0" style="width: 100%;">
<tr>
	<td>
<!--  -->
<table cellspacing="0" cellpadding="0" class="tab">
<tr>
	<td class="tab_none tabspace"></td>
	<td class="tab_off" onClick="location.href='index.php?action=viewRandomAlbum';">Album</td>
	<td class="tab_none tabspace"></td>
	<td class="tab_off" onClick="location.href='index.php?action=viewRandomTrack';">Track</td>
	<td class="tab_off" onClick="location.href='index.php?action=viewRandomFile';">File</td>
	<td class="tab_none tabspace"></td>
	<td class="tab_on" onClick="location.href='genre.php?action=blacklist';">Blacklist</td>
	<td class="tab_none">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" class="tab_border">
<?php
	$genre_id_array = array();
	$genre_array 	= array();
	genreTree('', $genre_id_array, $genre_array);
	$i=0; ?>
<tr class="tab_header">
	<td class="space"></td>
	<td></td>
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="3"></td></tr>

<tr class="lh3">
	<td></td>
	<td>
	<div id="genreList">
<?php
	foreach ($genre_array as $key => $genre) {
		$genre_id = $genre_id_array[$key];
		// $lenght = strlen($genre_id) - 1;
		// $tab = str_repeat('&nbsp;', $lenght * 4); 
?>	<p>
	<?php echo $tab; ?><input type="checkbox" name="genre_id_array[]" value="<?php echo $genre_id; ?>"<?php echo (in_array($genre_id, $blacklist)) ? ' checked' : ''; ?>>&nbsp;<?php echo html($genre); ?></input>
<?php
	}
?>
	</div>
	</td>
	<td></td>
</tr>

</table>
<!--  -->
	</td>
</tr>
</table>
<br>
<div class="buttons">
	<span>
	<a href="#" onclick="$(frmBlacklist).submit();">Save</a>
	</span>
</div>
</form>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Save blacklist                                                         |
//  +------------------------------------------------------------------------+
function saveBlacklist() {
	global $cfg, $db;
	authenticate('access_media', false, true, true);
	
	$genre_id_array = post('genre_id_array');
	$blacklist = implode(',', $genre_id_array);
	
	//if (preg_match('#^[a-z,]*$#', $blacklist) == false)
	//	message(__FILE__, __LINE__, 'error', '[b]This is not a valid genre[/b]');
	
	mysqli_query($db,'UPDATE session
		SET random_blacklist	= "' . mysqli_real_escape_string($db,$blacklist) . '"
		WHERE sid				= BINARY "' . mysqli_real_escape_string($db,$cfg['sid']) . '"');
}
?>
