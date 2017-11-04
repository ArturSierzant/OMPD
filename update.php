<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant	                           |
//  | http://www.ompd.pl                                             		     |
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
//  | update.php                                                             |
//  +------------------------------------------------------------------------+


ini_set('max_execution_time', '0');
//$updateStage = $_GET["updateStage"];
require_once('include/initialize.inc.php');
require_once('include/cache.inc.php');
require_once('include/library.inc.php');

ignore_user_abort(true);

//exit();

$cfg['menu'] = 'config';
$cfg['force_filename_update'] = false;

$action = getpost('action');
$dir_to_update = getpost('dir_to_update');
if (!isset($dir_to_update)) {
	$dir_to_update = '';
}
else {
	$dir_to_update = myDecode($dir_to_update);
	setcookie('update_dir', rtrim($dir_to_update,'/'), time() + (86400 * 30 * 365), "/");
	$cfg['force_filename_update'] = true;
}

$flag	= (int) getpost('flag');

cliLog("Update started");

if		(PHP_SAPI == 'cli')					cliUpdate();
elseif	($action == 'update')				update($dir_to_update);
elseif	($action == 'imageUpdate')			imageUpdate($flag);
elseif	($action == 'saveImage')			saveImage($flag);
elseif	($action == 'selectImageUpload')	selectImageUpload($flag);
elseif	($action == 'imageUpload')			imageUpload($flag);
else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');
exit();





//  +------------------------------------------------------------------------+
//  | Update                                                                 |
//  +------------------------------------------------------------------------+
function update($dir_to_update = '') {

	global $cfg, $db, $lastGenre_id, $getID3, $dirsCounter, $filesCounter, $curFilesCounter, $curDirsCounter, $last_update, $file;
	authenticate('access_admin', false, true);
	
	
	require_once('getid3/getid3/getid3.php');
	require_once('include/play.inc.php'); // Needed for mpdUpdate()
	
	$cfg['cli_update'] = false;
	$startTime = new DateTime();
	
	cliLog("Update start time: " . date("Ymd H:i:s"));

	$path = $cfg['media_dir'];
	$curFilesCounter = 0;
	$curDirsCounter = 0;
	$dirsCounter = 1;
	$filesCounter = 0;
	$prevDirsCounter = 0;
	$prevFilesCounter = 0;
	$dirs = array();
	$lastGenre_id = 1;
	
	
	$query = mysqli_query($db,'SELECT last_update FROM update_progress');
	$f = mysqli_fetch_assoc($query);
	$last_update = strtotime($f['last_update']);
	
	/* $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
	foreach($objects as $name){
		if ($name->isDir()) {
			++$dirsCounter;
		}
		else {
			++$filesCounter;
		}
	} */
	
	//mysqli_query($db,'DELETE FROM genre');
	//$lastGenre_id = 1;
	
	// formattedNavigator
	$nav			= array();
	$nav['name'][]	= 'Configuration';
	$nav['url'][]	= 'config.php';
	$nav['name'][]	= 'Update';
	require_once('include/header.inc.php');
?>
<table width="100%" cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td class="update_text">Update</td>
	<td>Progress</td>
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="4"></td></tr>
<tr class="odd">
	<td></td>
	<td>Structure &amp; image:</td>
	<td><span id="structure"></span></td>
	<td></td>
</tr>
<tr class="even">
	<td></td>
	<td>File info:</td>
	<td><span id="fileinfo"></span></td>
	<td></td>
</tr>
<tr class="odd">
	<td></td>
	<td>Cleanup:</td>
	<td><span id="cleanup"></span></td>
	<td></td>
</tr>
<tr class="even">
	<td></td>
	<td>Update time:</td>
	<td><span id="updateTime"></span></td>
	<td></td>
</tr>
</table>
<script>
	hideSpinner();
	window.setInterval(function() {
		show_update_progress();
	}, 500);
	
	function show_update_progress() {
		$.ajax({
			type: "POST",
			url: "ajax-update-progress.php",
			dataType : 'json',
			success : function(json) {
				var s = json['structure_image'];
				if (s.indexOf("fa-spin") > -1) {
					if (!$("#structure").hasClass("fa-spin"))
						$("#structure").html(json['structure_image']);
				}
				else
					$("#structure").html(json['structure_image']);
				
				s = json['file_info'];
				if (s.indexOf("fa-spin") > -1) {
					if (!$("#fileinfo").hasClass("fa-spin"))
						$("#fileinfo").html(json['file_info']);
				}	
				else
					$("#fileinfo").html(json['file_info']);
				
				s = json['cleanup'];
				if (s.indexOf("fa-spin") > -1) {
					if (!$("#cleanup").hasClass("fa-spin"))
						$("#cleanup").html(json['cleanup']);
				}
				else
					$("#cleanup").html(json['cleanup']);
				
				$("#updateTime").html(json['update_time']);
				
			}
		});
	}
	
	</script>
	
	<?php
	
	@ob_flush();
	flush();
	
	$cfg['footer'] = 'dynamic';
	require('include/footer.inc.php');
	
	$getID3 = new getID3;
	//initial settings for getID3:
	include 'include/getID3init.inc.php';
	
	
	$result = mysqli_query($db,'SELECT * FROM update_progress');
	
	if (mysqli_num_rows($result)==0) {	
			mysqli_query($db,'INSERT INTO update_progress (update_status, structure_image, file_info, cleanup, update_time, last_update)
						VALUES ("0", "", "", "", "", "")');
			$update_status = 0;
		} 
		else {
			$row=mysqli_fetch_assoc($result);
			$update_status=$row["update_status"];
		}
	
	if ($update_status <> 1) {
		
		mysqli_query($db,"update update_progress set 
			update_status = 1,
			structure_image = '',
			file_info = '',
			cleanup = '',
			update_time = '',
			last_update = 'Update in progress..'");
		
		//@ob_flush();
		//flush();
		
		mysqli_query($db,"update update_progress set 
			structure_image = 'Requesting MPD update...'");
		
		$rel_file = str_replace($cfg['media_dir'],'',$dir_to_update);
		$rel_file_mpd = rtrim($rel_file,'/');
		mpdUpdate($rel_file_mpd);
		
		//exit();
		
		//@ob_flush();
		//flush();
		
		mysqli_query($db,"update update_progress set 
			structure_image = '<i class=\"fa fa-cog larger icon-selected fa-spin\"></i>'");
		
		
		// Short sleep to prevent update problems with a previous update process that has not stopped yet.
		//sleep(1);
		
		$cfg['new_escape_char_hash']	= hmacmd5(print_r($cfg['escape_char'], true), file_get_contents(NJB_HOME_DIR . 'update.php'));
		//$cfg['force_filename_update']	= ($cfg['new_escape_char_hash'] != $cfg['escape_char_hash']) ? true : false;

		//$cfg['force_filename_update'] = false;
		
		
		
		if ($cfg['image_size'] != NJB_IMAGE_SIZE || $cfg['image_quality'] != NJB_IMAGE_QUALITY) {
			mysqli_query($db,'TRUNCATE TABLE bitmap');
			mysqli_query($db,'UPDATE server SET value = "' . $db->real_escape_string(NJB_IMAGE_SIZE) . '" WHERE name = "image_size" LIMIT 1');
			mysqli_query($db,'UPDATE server SET value = "' . $db->real_escape_string(NJB_IMAGE_QUALITY) . '" WHERE name = "image_quality" LIMIT 1');
		}
		
		
		if ($dir_to_update != '') {
			mysqli_query($db,'UPDATE album_id SET updated = 0 WHERE path LIKE "' . mysqli_real_escape_string($db,$dir_to_update) . '%"');
			mysqli_query($db,'UPDATE album SET updated = 0 WHERE album_id IN
			(SELECT album_id FROM album_id WHERE path LIKE "' . mysqli_real_escape_string($db,$dir_to_update) . '%" )');
			mysqli_query($db,'UPDATE bitmap SET updated = 0 WHERE album_id IN
			(SELECT album_id FROM album_id WHERE path LIKE "' . mysqli_real_escape_string($db,$dir_to_update) . '%" )');
			mysqli_query($db,'UPDATE track SET updated = 0 WHERE relative_file LIKE "' . mysqli_real_escape_string($db,$rel_file) . '%"');
			cliLog('dir_to_update: ' . 	$dir_to_update);
		} 
		else {
			mysqli_query($db,'UPDATE album SET updated = 0');
			mysqli_query($db,'UPDATE track SET updated = 0');
			mysqli_query($db,'UPDATE bitmap SET updated = 0');
			mysqli_query($db,'UPDATE album_id SET updated = 0');
		}
		
		
		//mysqli_query($db,'TRUNCATE album_id');
		
		//mysqli_query($db,'UPDATE genre SET updated = 0 WHERE genre <> ""');
		/* $query = mysqli_query($db,'SELECT MAX(CAST(genre_id AS UNSIGNED)) AS last_genre_id FROM genre');
		$rsGenre = mysqli_fetch_assoc($query);
		if ($rsGenre['last_genre_id'] > 0) {
			$lastGenre_id = ($rsGenre['last_genre_id'] + 1);
			}
		else {
			$lastGenre_id = 1;
		} */
		
		$cfg['timer'] = 0; // force update
	
		//recursiveScanCount_add2table($cfg['media_dir']);
		//recursiveScanCount($cfg['media_dir']);
		clearstatcache();
		$dir_to_scan = $cfg['media_dir'];
		if ($dir_to_update != '') {
			$dir_to_scan = $dir_to_update;
		}
		countDirectories($dir_to_scan);
		
		if ($dirsCounter == 1) $dirsCounter = 0;
		/* $result = mysqli_query($db,"update update_progress set 
			update_status = 0,
			update_time = '" . $updateTime . "',
			last_update = '" . date('Y-m-d, H:i:s')   . "'
			");
		exit(); */
		
		recursiveScan($dir_to_scan);
		
		//exit();
		
		mysqli_query($db,'UPDATE update_progress SET structure_image = "<div class=\'out\'><div class=\'in\' style=\'width: 200px\'></div></div> 100%"');
		
		sleep(1);
		
		mysqli_query($db,'DELETE FROM album WHERE NOT updated');
		mysqli_query($db,'DELETE FROM track WHERE NOT updated');
		mysqli_query($db,'DELETE FROM bitmap WHERE NOT updated');
		//mysqli_query($db,'DELETE FROM genre WHERE NOT updated');
		
		
		mysqli_query($db,'UPDATE server SET value = "' . $db->real_escape_string($cfg['new_escape_char_hash']) . '" WHERE name = "escape_char_hash" LIMIT 1');
			
		$no_image = mysqli_num_rows(mysqli_query($db,'SELECT album_id FROM bitmap WHERE flag = 0'));
		$image_error = mysqli_num_rows(mysqli_query($db,'SELECT album_id FROM bitmap WHERE flag = 10'));
		/* if ($no_image > 0)	{
			mysqli_query($db,'update update_progress set 
			structure_image = "<a href=\'update.php?action=imageUpdate&amp;flag=0\'><img src=\'' . $cfg['img'] . 'small_image.png\' alt=\'\' class=\'small space\'>Update ' . $no_image . (($no_image == 1) ? ' image' : ' images') . ' from internet</a>"');
		} */
		
		$image_problems = '';
		
		
		if ($no_image > 0)	{
			$image_problems .= '&nbsp;<a href=\'statistics.php?action=noImageFront\'>No image for ' . $no_image . (($no_image == 1) ? ' folder' : ' folders') . '.</a>';
		}
		if ($image_error > 0)	{
			$image_problems .= '&nbsp;<a href=\'statistics.php?action=imageError\'>Image error for ' . $image_error . (($image_error == 1) ? ' folder' : ' folders') . '.</a>';
		}
		
		if ($image_problems != '')	{
			mysqli_query($db,'update update_progress set 
			structure_image = "' . $image_problems .'"');
		}
		else {
			mysqli_query($db,'update update_progress set 
			structure_image = "<i class=\"fa fa-check icon-ok \"></i> "');
		}
		// @ob_flush();
		// flush();
		
		mysqli_query($db,'update update_progress set 
			file_info = "<i class=\"fa fa-cog larger icon-selected fa-spin\"></i>"');
		
		$cfg['timer'] = 0; // force update
		
		cliLog("going into fileInfoLoop: " . $rel_file);
		fileInfoLoop($rel_file);
		
		mysqli_query($db,'UPDATE update_progress SET	file_info = "<div class=\'out\'><div class=\'in\' style=\'width: 200px\'></div></div> 100%"');
		
		sleep(1);
		
		mysqli_query($db,'UPDATE update_progress SET	file_info = "<i class=\"fa fa-cog larger icon-selected fa-spin\"></i> Updating genre list..."');
		
		updateGenre();
		
		sleep(1);
		
		$error = mysqli_num_rows(mysqli_query($db,'SELECT error FROM track WHERE error != ""'));
		if ($error > 0)	{
			mysqli_query($db,'update update_progress set 
			file_info = "<a href=\'statistics.php?action=fileError\'><i class=\"fa fa-minus-circle icon-nok\"></i> ' . $error . (($error == 1) ? ' error' : ' errors') . '</a>"');
		}
		else {
			mysqli_query($db,'update update_progress set 
			file_info = "<i class=\"fa fa-check icon-ok\"></i> "');
		}
		
		// @ob_flush();
		// flush();
		
		mysqli_query($db,'update update_progress set 
			cleanup = "<i class=\"fa fa-cog larger icon-selected fa-spin\"></i> "');
		
		databaseCleanup();
		
		// @ob_flush();
		// flush();
		
		mysqli_query($db,'update update_progress set 
			cleanup = "<i class=\"fa fa-check icon-ok\"></i> "');
		
		$stopTime = new DateTime();
		
		$updateTime = $stopTime->diff($startTime);
		
		$updateTime = $updateTime->h . 'h ' . $updateTime->i . 'm ' . $updateTime->s . 's';
		
		$result = mysqli_query($db,"update update_progress set 
			update_status = 0,
			update_time = '" . $updateTime . "',
			last_update = '" . date('Y-m-d, H:i:s')   . "'
			");
		
		cliLog("Update stop time: " . date("Ymd H:i:s"));
		backgroundQueries();
	}
	else {
		$structure_image=$row["structure_image"];
		echo '<script type="text/javascript"> document.getElementById(\'structure\').innerHTML=" ' . $structure_image . '";</script>' . "\n";
		
		$file_info=$row["file_info"];
		echo '<script type="text/javascript"> document.getElementById(\'fileinfo\').innerHTML=" ' . $file_info . '";</script>' . "\n";
		
		$cleanup=$row["cleanup"];
		echo '<script type="text/javascript"> document.getElementById(\'cleanup\').innerHTML=" ' . $cleanup . '";</script>' . "\n";
	
		$update_time=$row["update_time"];
		echo '<script type="text/javascript"> document.getElementById(\'updateTime\').innerHTML=" ' . $update_time . '";</script>' . "\n";
	}
	$cfg['footer'] = 'close';
	require('include/footer.inc.php');
}


//  +------------------------------------------------------------------------+
//  | Recursive scan                                                         |
//  +------------------------------------------------------------------------+
function recursiveScan($dir) {
    global $cfg, $db;

    $album_id = '';
    $file     = array();
    $filename = array();
	
	$dir = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir);
	
    cliLog("recursiveScan: " . $dir);
    // TODO: consider to remove. @see https://github.com/ArturSierzant/OMPD/issues/15
    if ($cfg['ignore_media_dir_access_error']) {
        $entries = @scandir($dir);
    }
    else {
        $entries = @scandir($dir) or message(__FILE__, __LINE__, 'error', '[b]Failed to open directory:[/b][br]' . $dir . '[list][*]Check media_dir value in the config.inc.php file[*]Check file permission[/list]');
    }
	
	//$dir = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $dir);
	
	$isIdFromFile = false;
    
	foreach ($entries as $entry) {
		cliLog('entry: ' . $entry);
        if ($entry[0] === '.' || in_array($entry, $cfg['directory_blacklist']) === TRUE) {
            continue;
        }
        if (is_dir($dir . $entry . '/')) {
            recursiveScan (iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $dir . $entry . '/'));
            continue;
        }

        $extension = strtolower(substr(strrchr($entry, '.'), 1));
        if (in_array($extension, $cfg['media_extension'])) {
            $entry = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $entry);
            $dir_d = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $dir);
            $file[] = $dir_d . $entry;
            // $file[] = $dir . $entry;
            $filename[] = substr($entry, 0, -strlen($extension) - 1);
            continue;
        }
        if ($extension == 'id') {
            $album_id = substr($entry, 0, -3);
			$isIdFromFile = true;
        }
	}

    if (count($file) === 0) {
        unset($entries);
        unset($entry);
        unset($filename);
        unset($file);
        unset($dir);
        return;
    }
	
	$dir = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $dir);
	
	if ($isIdFromFile) {
		$db->query("
			UPDATE album_id
			SET updated = '1', path = '" . $db->real_escape_string($dir) . "'
			WHERE album_id = '" . $db->real_escape_string($album_id) . "'
			LIMIT 1"
		);
	}
	else {
		$db->query("
			UPDATE album_id
			SET updated = '1'
			WHERE path = '" . $db->real_escape_string($dir) . "'
			LIMIT 1"
		);
	}
    if ($db->affected_rows == 0) {
        $album_id = ($album_id == '') ? base_convert(uniqid(), 16, 36) : $album_id;
        $album_add_time = time();
        $db->query("
            INSERT INTO album_id
                (album_id, path, album_add_time, updated)
            VALUES
                ('" . $album_id . "',
                '" . $db->real_escape_string($dir) . "',
                '" . $album_add_time . "','1')"
        );
    } else {
        $result = $db->query("
           SELECT album_id, album_add_time
           FROM album_id
           WHERE path = '" . $db->real_escape_string($dir) . "'
           LIMIT 1"
        );
        $row = $result->fetch_assoc();
        $album_id = $row["album_id"];
        $album_add_time = $row["album_add_time"];
        $result->free_result();
    }
    cliLog("Going into fileStructure for album_id: " . $album_id);
    fileStructure($dir, $file, $filename, $album_id, $album_add_time);
}




//  +------------------------------------------------------------------------+
//  | Recursive scan - count directories                                     |
//  +------------------------------------------------------------------------+

function countDirectories($base_dir) {
	global $cfg, $db, $dirsCounter, $filesCounter, $isMediaDir;
	$isMediaDir = 0;
	//$entries = @scandir($base_dir) or message(__FILE__, __LINE__, 'error', '[b]Failed to open directory:[/b][br]' . $base_dir . '[list][*]Check media_dir value in the config.inc.php file[*]Check file permission[/list]');
	cliLog('countDirectories for ' . $base_dir);
	
	$base_dir = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $base_dir);
	
	if ($cfg['ignore_media_dir_access_error']) {
		$entries = @scandir($base_dir);
	}
	else {
		$entries = @scandir($base_dir) or message(__FILE__, __LINE__, 'error', '[b]Failed to open directory:[/b][br]' . $base_dir . '[list][*]Check media_dir value in the config.inc.php file[*]Check file permission[/list]');
	}
	$directories = array();
	foreach(scandir($base_dir) as $file) {
		$extension = substr(strrchr($file, '.'), 1);
		$extension = strtolower($extension);
		if (in_array($extension, $cfg['media_extension'])) $isMediaDir = 1;
		$dir = $base_dir.DIRECTORY_SEPARATOR.$file;
		if($file == '.' || $file == '..' || (is_dir($dir) === TRUE && in_array($file, $cfg['directory_blacklist']) === TRUE)) continue;
		if(is_dir($dir)) {
			$directories []= iconv(NJB_DEFAULT_FILESYSTEM_CHARSET,'UTF-8', $dir);
			if ($isMediaDir == 1) {
					$dirsCounter = $dirsCounter + 1;
				}
			mysqli_query($db,"UPDATE update_progress SET 
				structure_image = 'Counting media directories: " . $dirsCounter . "'");
			$directories = array_merge($directories, countDirectories(iconv(NJB_DEFAULT_FILESYSTEM_CHARSET,'UTF-8', $dir)));
		}
	}
	return $directories;
}




//  +------------------------------------------------------------------------+
//  | File structure                                                         |
//  +------------------------------------------------------------------------+
Function fileStructure($dir, $file, $filename, $album_id, $album_add_time) {
	global $cfg, $db, $lastGenre_id, $getID3, $dirsCounter, $filesCounter, $curFilesCounter, $curDirsCounter, $prevDirsCounter, $last_update, $flag, $image_front;
	
	
	// Also needed for track update!
	$discs 			= 1;
	$disc_digits	= 0;
	$track_digits	= 0;
	
	$year				= NULL;
	$month				= NULL;
	$artist 			= 'Unknown AlbumArtist';
	$aGenre 			= 'Unknown Genre';
	$aGenre_id			= 0;
	$album_dr			= NULL;

	$isUpdateRequired	= false;
    $new_array          = array();
	
	if ($cfg['name_source'] != 'tags') {
		if (preg_match('#^(0{0,1}1)(0{1,3}1)+\.\s+.+#', $filename[0], $match) && preg_match('#^(\d{' . strlen($match[1] . $match[2]) . '})+\.\s+.+#', $filename[count($filename)-1])) {
			// Multi disc
			$disc_digits	= strlen($match[1]);
			$track_digits	= strlen($match[2]);
			preg_match('#^(\d{' . $disc_digits . '})\d{' . $track_digits . '}+\.\s+#', $filename[count($filename)-1], $match);
			$discs = $match[1];
		}
		elseif (preg_match('#^(\d{2,4})+\.\s+.+#', $filename[0], $match)) {
			// Single disc
			$track_digits	= strlen($match[1]);
		}
			
		$temp				= decodeEscapeChar($dir);
		$temp   			= explode('/', $temp);
		$n					= count($temp);
		
		$artist_alphabetic 	= $temp[$n - 3];
		$artist 			= $artist_alphabetic;
		$album				= $temp[$n - 2];
		
		if (preg_match('#^(\d{4})\s+-\s+(.+)#', $album, $match)) {
	    $year	= $match[1];
		$album	= $match[2];
		}
		elseif (preg_match('#^(\d{4})(0[1-9]|1[012])\s+-\s+(.+)#', $album, $match)) {
			$year	= $match[1];
			$month	= $match[2];
			$album	= $match[3];
		}	
	}
	
	
	if ($cfg['name_source'] == 'tags') {
	
	++$curDirsCounter; 	
	if ($cfg['cli_update'] == false && ((microtime(true) - $cfg['timer']) * 1000) > $cfg['update_refresh_time'] && ($curDirsCounter/$dirsCounter > ($prevDirsCounter/$dirsCounter + 0.005))) {
		
		$prevDirsCounter = $curDirsCounter;
		
		mysqli_query($db,'update update_progress set 
			structure_image = "<div class=\'out\'><div class=\'in\' style=\'width:' . html(floor($curDirsCounter/$dirsCounter * 200)) . 'px\'></div></div> ' . html(floor($curDirsCounter/$dirsCounter * 100)) . '%"');
		$cfg['timer'] = microtime(true);
	}

	
	//dir modified: files or dirs added
	if (filemtime(dirname($file[0])) > $last_update) {
		$isUpdateRequired = true;
	}
	else {
		$q = mysqli_query($db,'SELECT relative_file, filemtime FROM track WHERE album_id = BINARY "' . $db->real_escape_string($album_id) . '"');
		while($row = mysqli_fetch_assoc($q)){
				$row['relative_file'] = $cfg['media_dir'] . $row['relative_file'];
				//$new_array[$row['relative_file']] = $row['filemtime'];
				$new_array[] = $row['relative_file'];
				//echo $row['relative_file'] . '<br>';
		};

		
		for ($i=0; $i < count($filename); $i++) {
			//check if file is modified
			if (filemtime($file[$i]) > $last_update) {
				$isUpdateRequired = true;
				break;
			}
			else {
				if (!in_array($file[$i],$new_array)) {
					$isUpdateRequired = true;
					break;
				}
			}
		}
		
		
	}
	
	if ($isUpdateRequired) {
		cliLog("fileStructure file[0]: " . $file[0]);

		$file_d = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $file[0]);
		
		$ThisFileInfo = $getID3->analyze($file_d);
		getid3_lib::CopyTagsToComments($ThisFileInfo); 
		
		$artist = parseAlbumArtist($ThisFileInfo);
		
		if ($artist == 'Unknown AlbumArtist') {
			
			$artist = parseTrackArtist($ThisFileInfo);
			
		};
		
		$artist_alphabetic	= $artist;
		
		$year = parseYear($ThisFileInfo);
	
		$album_dr = parseAlbumDynamicRange($ThisFileInfo);
		
		$aGenre = parseGenre($ThisFileInfo);
		
		if ((strpos(strtolower($dir), strtolower($cfg['misc_tracks_folder'])) === false) && (strpos(strtolower($dir), strtolower($cfg['misc_tracks_misc_artists_folder'])) === false)) {
			
			$album = parseAlbumTitle($ThisFileInfo);
			
		}
		elseif (strpos(strtolower($dir), strtolower($cfg['misc_tracks_folder'])) !== false) {
			$year = NULL;
			$album = $cfg['misc_tracks_folder'] . $artist;
			/* if (strtolower(basename($dir)) == strtolower($cfg['misc_tracks_folder'])) 
				$album = $cfg['misc_tracks_folder'] . $artist;				
			else
				$album = basename($dir); */
			
			
		}
		elseif (strpos(strtolower($dir), strtolower($cfg['misc_tracks_misc_artists_folder'])) !== false) {
			$artist = 'Various Artists';
			$artist_alphabetic	= $artist;
			$aGenre = '';
			$year = NULL;
			if (strtolower(basename($dir)) == strtolower($cfg['misc_tracks_misc_artists_folder'])) 
				$album = $cfg['misc_tracks_misc_artists_folder'];
			else
				$album = basename($dir);
		}
		
		/* $result = mysqli_query($db,'SELECT genre_id FROM genre WHERE genre="' . $db->real_escape_string($aGenre) . '"');
		$row=mysqli_fetch_assoc($result);
		$aGenre_id=$row["genre_id"];
		
		if (mysqli_num_rows($result)==0) {	
			mysqli_query($db,'INSERT INTO genre (genre_id, genre, updated)
						VALUES ("' . $db->real_escape_string($lastGenre_id) . '",
								"' . $db->real_escape_string($aGenre) . '",
								1)');
			$aGenre_id = $lastGenre_id;
			++$lastGenre_id;
			
		} else {		
				$result = mysqli_query($db,"update genre set 
				updated = 1
				WHERE genre = '". $db->real_escape_string($aGenre) ."';"); 
		} */

		
		
		
		//
		// Update/Insert album information on the end of this function to be able to include image_id
		//
		
		// TODO: cli_update is not possible anymore, right?
		// if ($cfg['cli_update'] && $cfg['cli_silent_update'] == false)
	    //	echo $artist_alphabetic  . ' - ' . $album . "\n";
		
			
		// Track update
		$disc		= 1;
		$number		= NULL;
		
		for ($i=0; $i < count($filename); $i++) {
			cliLog("fileStructure TrackUpdate: " . $file[$i]);
			
			$relative_file = substr($file[$i], strlen($cfg['media_dir']));
			
			mysqli_query($db,'UPDATE track SET
				updated				= 1
				WHERE album_id		= "' . $db->real_escape_string($album_id) . '"
				AND relative_file	= BINARY "' . $db->real_escape_string($relative_file) . '"
				LIMIT 1');
			if ($cfg['force_filename_update'] || mysqli_affected_rows($db) == 0)
				{
				$temp = decodeEscapeChar($filename[$i]);
				if ($cfg['name_source'] != 'tags') {
					//if (preg_match('#^(\d{' . $disc_digits . '})(\d{' . $track_digits . '})\s+-\s+(.+)#', $temp, $match)) {
					if (preg_match('#^(\d{' . $disc_digits . '})(\d{' . $track_digits . '})+\.\s+(.+)#', $temp, $match)) {	
						if ($disc_digits > 0) {
							// Multiple disc
							$disc		= $match[1];
							$number		= $match[2];
						}
						else {
							// Single disc
							$number		= $match[2];
						}
						$temp = $match[3]; // Strip disc and track number
					}
					if (preg_match('#^(.+?)\s+-\s+(.+?)(?:\s+Ft\.\s+(.+))?$#i', $temp, $match)) {
						$track_artist	= $match[1];
						$title			= $match[2];
						$featuring		= (isset($match[3])) ? $match[3] : '';
					}  
					elseif (preg_match('#^(.+?)(?:\s+Ft\.\s+(.+))?$#i', $temp, $match)) {
						$track_artist	= $artist;
						$title			= $match[1];
						$featuring		= (isset($match[2])) ? $match[2] : '';
					}
					else {
						$track_artist	= '*** UNSUPPORTED FILENAME FORMAT ***';
						$title			= '(' . $filename[$i] . ')';
						$featuring		= '';
					}
				}	
				
				
				if (mysqli_affected_rows($db) == 0)
					mysqli_query($db,'INSERT INTO track (artist, featuring, title, relative_file, disc, number, album_id, updated)
						VALUES ("' . $db->real_escape_string($track_artist) . '",
						"' . $db->real_escape_string($featuring) . '",
						"' . $db->real_escape_string($title) . '",
						"' . $db->real_escape_string($relative_file) . '",
						' . (int) $disc . ',
						' . ((is_null($number)) ? 'NULL' : (int) $number) . ',
						"' . $db->real_escape_string($album_id) . '",
						1)');
				else
					mysqli_query($db,'UPDATE track SET
						artist				= "' . $db->real_escape_string($track_artist) . '",
						featuring			= "' . $db->real_escape_string($featuring) . '",
						title				= "' . $db->real_escape_string($title) . '",
						relative_file		= "' . $db->real_escape_string($relative_file) . '",
						disc				= ' . (int) $disc . ',
						number				= ' . ((is_null($number)) ? 'NULL' : (int) $number) . ',
						album_id			= "' . $db->real_escape_string($album_id) . '",
						updated				= 1
						WHERE album_id		= "' . $db->real_escape_string($album_id) . '"
						AND relative_file	= BINARY "' . $db->real_escape_string($relative_file) . '"
						LIMIT 1');
			}
		}
        unset($ThisFileInfo);
	}
	else {
		$q = mysqli_query($db,'SELECT relative_file FROM track 
		WHERE album_id = "' . $db->real_escape_string($album_id) . '"');
		$rows = mysqli_num_rows($q);
		if ($rows == count($filename)) {
			mysqli_query($db,'UPDATE track SET
			updated				= 1
			WHERE album_id		= "' . $db->real_escape_string($album_id) . '"
			');
		}
		else {
			for ($i=0; $i < count($filename); $i++) {
				$relative_file = substr($file[$i], strlen($cfg['media_dir']));
				
				mysqli_query($db,'UPDATE track SET
					updated				= 1
					WHERE album_id		= "' . $db->real_escape_string($album_id) . '"
					AND relative_file	= BINARY "' . $db->real_escape_string($relative_file) . '"
					LIMIT 1');
			}
		}
	}
	
	
	// Image update
	$image = NJB_HOME_DIR . 'image/no_image.png';
	$flag = 0; // No image
	$misc_tracks = false;
	
	cliLog("fileStructure ImageUpdate: " . $file[0]);
	
	$dir = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir);
	
	$image_front_arr = explode(";", $cfg['image_front']);
	$relative_dir = substr($dir, strlen($cfg['media_dir']));
	$is_image_set = false;
	
	foreach ($image_front_arr as $img)  {
		if (is_file($dir . $img . '.jpg')) { 
			$image = $dir . $img . '.jpg'; 
			$image_front = $relative_dir . $img . '.jpg';
			$is_image_set = true;
			$flag = 3;
		} // Stored image
		elseif (is_file($dir . $img . '.png')) { 
			$image = $dir . $img . '.png'; 
			$image_front = $relative_dir . $img . '.png';
			$is_image_set = true;
			$flag = 3; 
		} // Stored image
	}
	
	if (((strpos(strtolower($dir), strtolower($cfg['misc_tracks_folder'])) !== false) || (strpos(strtolower($dir), strtolower($cfg['misc_tracks_misc_artists_folder'])) !== false)) && $flag == 0){
		$image = NJB_HOME_DIR . 'image/misc_image.jpg';
		$flag = 3; // Stored image
		$misc_tracks = true;
	}
	elseif	($cfg['image_read_embedded'] && $flag == 0) {
		
		cliLog("fileStructure ImageUpdateEmbeded: " . $file[0]);
		$file_d = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $file[0]);
		$ThisFileInfo = $getID3->analyze($file_d);
		getid3_lib::CopyTagsToComments($ThisFileInfo); 
		if (isset($ThisFileInfo['error']) == false &&
			isset($ThisFileInfo['comments']['picture'][0]['image_mime']) &&
			isset($ThisFileInfo['comments']['picture'][0]['data']) &&
			($ThisFileInfo['comments']['picture'][0]['image_mime'] == 'image/jpeg' || $ThisFileInfo['comments']['picture'][0]['image_mime'] == 'image/png')) {
				if ($ThisFileInfo['comments']['picture'][0]['image_mime'] == 'image/jpeg')	$image = NJB_HOME_DIR . 'tmp/' . $image_front_arr[0] . '.jpg';
				if ($ThisFileInfo['comments']['picture'][0]['image_mime'] == 'image/png')	$image = NJB_HOME_DIR . 'tmp/' . $image_front_arr[0] . '.png';
				if (file_put_contents($image, $ThisFileInfo['comments']['picture'][0]['data']) === false) 
					message(__FILE__, __LINE__, 'error', '[b]Failed to write image to:[/b][br]' . $image .'[br] file: ' . $file[0] .'[br] data: [br]Check write permissions.');
				$flag = 0; // No image
				
		}
					
		unset($getID3);
		unset($ThisFileInfo);
	}
	
	if (!$is_image_set) {
		if ($misc_tracks){
			$image_front = $image;
		}
		else {
			$image_front = '';
		}	
	}
	
	cliLog("fileStructure ImageUpdate image: " . $image);
	cliLog("fileStructure ImageUpdate image_front: " . $image_front);
	
	
	if		(is_file($dir . $cfg['image_back'] . '.jpg'))	$image_back = $relative_dir . $cfg['image_back'] . '.jpg';
	elseif	(is_file($dir . $cfg['image_back'] . '.png'))	$image_back = $relative_dir . $cfg['image_back'] . '.png';
	else													$image_back = '';

	cliLog("fileStructure ImageUpdate image_front filesize:");
	$filesize	= filesize($image);
	cliLog($filesize);
	cliLog("fileStructure ImageUpdate image_front filemtime:");
	$filemtime	= filemtime($image);
	cliLog($filemtime);
	
	$query	= mysqli_query($db,'SELECT filesize, filemtime, image_id, flag FROM bitmap WHERE album_id = "' . $db->real_escape_string($album_id) . '"');
	$bitmap	= mysqli_fetch_assoc($query);
	
	if ($bitmap['filesize'] == $filesize && filemtimeCompare($bitmap['filemtime'], $filemtime)) {
		/* image_front			= "' . $db->real_escape_string($image_front) . '",
			image_back			= "' . $db->real_escape_string($image_back) . '", */
		mysqli_query($db,'UPDATE bitmap SET
			
			updated				= 1
			WHERE album_id		= "' . $db->real_escape_string($album_id) . '"
			LIMIT 1');
		$image_id = $bitmap['image_id'];
	}
	else {
		//$imagesize = @getimagesize($image) or message(__FILE__, __LINE__, 'error', '[b]Failed to read image information from:[/b][br]' . $image);
		cliLog("fileStructure ImageUpdate image_front getimagesize:");
		$imagesize = @getimagesize($image) or logImageError();
		cliLog("w: " . $imagesize[0] . " h: " . $imagesize[1]);
		cliLog("fileStructure ImageUpdate image_front imagesize flag: " . $flag);
		$image_id = (($flag == 3) ? $album_id : 'no_image');
		$image_id .= '_' . base_convert(NJB_IMAGE_SIZE * 100 + NJB_IMAGE_QUALITY, 10, 36) . base_convert($filemtime, 10, 36) . base_convert($filesize, 10, 36);
		
		//cliLog("imagesize - image_id - image: " . $imagesize . " - " . $image_id . " - " . $image . " - " . $filesize . " - " . $filemtime);
		
		$image_front = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $image_front);
	
		if ($bitmap['filemtime'])
			mysqli_query($db,'UPDATE bitmap SET
				image				= "' . $db->real_escape_string(resampleImage($image)) . '",
				filesize			= ' . (int) $filesize . ',
				filemtime			= ' . (int) $filemtime . ',
				flag				= ' . (int) $flag . ',
				image_front			= "' . $db->real_escape_string($image_front) . '",
				image_back			= "' . $db->real_escape_string($image_back) . '",
				image_front_width	= ' . ($flag == 3 ? $imagesize[0] : 0) . ',
				image_front_height	= ' . ($flag == 3 ? $imagesize[1] : 0) . ',
				image_id			= "' . $db->real_escape_string($image_id) . '",
				updated				= 1
				WHERE album_id	= "' . $db->real_escape_string($album_id) . '"
				LIMIT 1');
		else
			mysqli_query($db,'INSERT INTO bitmap (image, filesize, filemtime, flag, image_front, image_back, image_front_width, image_front_height, image_id, album_id, updated)
				VALUES ("' . $db->real_escape_string(resampleImage($image)) . '",
				' . (int) $filesize . ',
				' . (int) $filemtime . ',
				' . (int) $flag . ',
				"' . $db->real_escape_string($image_front) . '",
				"' . $db->real_escape_string($image_back) . '",
				' . ($flag == 3 ? $imagesize[0] : 0) . ',
				' . ($flag == 3 ? $imagesize[1] : 0) . ',
				"' . $db->real_escape_string($image_id) . '",
				"' . $db->real_escape_string($album_id) . '",
				1)');
	}
	
	if ($isUpdateRequired) {
		mysqli_query($db,'UPDATE album SET
		artist_alphabetic	= "' . $db->real_escape_string($artist_alphabetic) . '",
		artist				= "' . $db->real_escape_string($artist) . '",
		album				= "' . $db->real_escape_string($album) . '",
		year				= ' . ((is_null($year) || $year == 'NULL') ? 'NULL' : (int) $year) . ',
		month				= ' . ((is_null($month)) ? 'NULL' : (int) $month) . ',
		discs				= ' . (int) $discs . ',
		image_id			= "' . $db->real_escape_string($image_id) . '",
		genre_id				= "' . $db->real_escape_string($aGenre_id) .'",
		updated				= 1,
		album_dr			= ' . ((is_null($album_dr) || $album_dr == 'NULL') ? 'NULL' : (int) $album_dr) . '
		WHERE album_id		= "' . $db->real_escape_string($album_id) . '"
		LIMIT 1');
	}
	else {
		mysqli_query($db,'UPDATE album SET
		image_id			= "' . $db->real_escape_string($image_id) . '",
		updated				= 1
		WHERE album_id		= "' . $db->real_escape_string($album_id) . '"
		LIMIT 1');
	}
	if (mysqli_affected_rows($db) == 0)
		mysqli_query($db,'INSERT INTO album (artist_alphabetic, artist, album, year, month, genre_id, album_add_time, discs, image_id, album_id, updated, album_dr)
			VALUES (
			"' . $db->real_escape_string($artist_alphabetic) . '",
			"' . $db->real_escape_string($artist) . '",
			"' . $db->real_escape_string($album) . '",
			' . ((is_null($year) || $year == 'NULL') ? 'NULL' : (int) $year) . ',
			' . ((is_null($month)) ? 'NULL' : (int) $month) . ',
			' . (int) $aGenre_id . ',
			' . (int) $album_add_time . ',
			' . (int) $discs . ',
			"' . $db->real_escape_string($image_id) . '",
			"' . $db->real_escape_string($album_id) . '",
			1,
			' . ((is_null($album_dr) || $album_dr == 'NULL') ? 'NULL' : (int) $album_dr) . ')');
	// Close getID3				
	unset($getID3);
}
};


//  +------------------------------------------------------------------------+
//  | File info loop                                                         |
//  +------------------------------------------------------------------------+
function fileInfoLoop($rel_file = '') {
	global $cfg, $db, $dirsCounter, $filesCounter, $curFilesCounter, $curDirsCounter, $prevDirsCounter, $prevFilesCounter;
	
	if ($rel_file != '') {
		$filesCounter = $db->query("
			SELECT COUNT(track_id) AS totalTracks
			FROM track
			WHERE updated
			AND relative_file LIKE '" . mysqli_real_escape_string($db,$rel_file) . "%';")->fetch_assoc()['totalTracks'];
	}
	else {
		$filesCounter = $db->query("
			SELECT COUNT(track_id) AS totalTracks
			FROM track
			WHERE updated;")->fetch_assoc()['totalTracks'];
	}
    cliLog( "TOTAL FILES TO SCAN: " . $filesCounter);

    // Initialize getID3
    $getID3 = new getID3;
    //initial settings for getID3:
    include 'include/getID3init.inc.php';

    $updated = FALSE;
    $continue = TRUE;
    $batchSize = 1000;
    $batchCounter = 0;
    while($continue === TRUE) {
        cliLog("processing batch LIMIT  " . $curFilesCounter . ",". $batchSize);
        $query = "
            SELECT relative_file, filesize, filemtime, album_id
            FROM track
            WHERE updated
            ORDER BY relative_file
            LIMIT " . $curFilesCounter . "," . $batchSize . ";";
		if ($rel_file != ''){
			$query = "
            SELECT relative_file, filesize, filemtime, album_id, track_id
            FROM track
            WHERE updated
			AND relative_file LIKE '" . mysqli_real_escape_string($db,$rel_file) . "%'
            ORDER BY relative_file
            LIMIT " . $curFilesCounter . "," . $batchSize . ";";
		}
        $result = $db->query($query);
        while($track = $result->fetch_assoc()) {
            $curFilesCounter++;
            fileInfo($track, $getID3);
        }
        // free some memory
        $result->free_result();

        // reset getID3
        unset($getID3);
        $getID3 = new getID3;
        include 'include/getID3init.inc.php';

        if($curFilesCounter >= $filesCounter) {
            $continue = FALSE;
        }
    }
}


//  +------------------------------------------------------------------------+
//  | File info for a single file                                            |
//  +------------------------------------------------------------------------+
function fileInfo($track, $getID3 = NULL) {
    global $cfg, $db, $dirsCounter, $filesCounter, $curFilesCounter, $curDirsCounter, $prevDirsCounter, $prevFilesCounter, $updated; 

    if($getID3 === NULL) {
        $getID3 = new getID3;
        include 'include/getID3init.inc.php';
    }

    $file = $cfg['media_dir'] . $track['relative_file'];
    //convert file names to default charset
    $file = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $file);
    cliLog( "fileInfo: [" . $curFilesCounter . "] " . $file);

    // TODO: do not exit the whole import/update procedure in case single files are not processable
    if (is_file($file) == false) {
        //message(__FILE__, __LINE__, 'error', '[b]Failed to read file:[/b][br]' . $file . '[list][*]Update again[*]Check file permission[/list]');
		$query = 'UPDATE track SET
            error = "Failed to read file. Check file permission or file name."
			WHERE relative_file = BINARY "' . $db->real_escape_string($track['relative_file']) . '";';
		mysqli_query($db, $query);	
		return;
	}

    if ($cfg['cli_update'] == false && ((microtime(true) - $cfg['timer']) * 1000) > $cfg['update_refresh_time'] && ($curFilesCounter/$filesCounter > ($prevFilesCounter/$filesCounter + 0.005))) {
        // write import/update progress to database
        $prevFilesCounter = $curFilesCounter;
        // TODO: add some decimals to displayed percent value for avouiding visually "freezes" on huge collections
        mysqli_query($db,'UPDATE update_progress SET
        file_info = "<div class=\'out\'><div class=\'in\' style=\'width:' . html(floor($curFilesCounter/$filesCounter * 200)) . 'px\'></div></div> ' . html(floor($curFilesCounter/$filesCounter * 100)) . '%"');
        $cfg['timer'] = microtime(true);
    }

    $metaData = array();
    $filesize = filesize($file);
    $filemtime = filemtime($file);
    //$force_filename_update = false;

    if ($filesize != $track['filesize'] || filemtimeCompare($filemtime, $track['filemtime']) == false || $cfg['force_filename_update']) {
        // TODO: does cli_update still exist? would be great but obviously this is old netjukebox code
        //if ($cfg['cli_update'] && $cfg['cli_silent_update'] == false)
        //    echo $file . "\n";

        $metaData = $getID3->analyze($file);
        getid3_lib::CopyTagsToComments($metaData);
				
				//prevent changing track_id if already set to avoid deleting from favorites
				if (strpos($track['track_id'],'_') === false) {
					$track_id = $db->real_escape_string($track['album_id'] . '_' . fileId($file));
				}
				else {
					$track_id = $track['track_id'];
				}
				
        // TODO: does it make sense to populate artist and track_artist with the same value?
        $query = 'UPDATE track SET
            mime_type               = "' . $db->real_escape_string( parseMimeType($metaData)) . '",
            filesize                = ' . (int) $filesize . ',
            filemtime               = ' . (int) $filemtime . ',
            miliseconds             = ' . (int) parseMiliseconds($metaData) . ',
            audio_bitrate           = ' . (int) parseAudioBitRate($metaData) . ',
            audio_bits_per_sample   = ' . (int) parseAudioBitsPerSample($metaData) . ',
            audio_sample_rate       = ' . (int) parseAudioSampleRate($metaData) . ',
            audio_channels          = ' . (int) parseAudioChannels($metaData) . ',
            audio_lossless          = ' . (int) parseAudioLossless($metaData) . ',
            audio_compression_ratio = ' . (float) parseAudioCompressionRatio($metaData) . ',
            audio_dataformat        = "' . $db->real_escape_string( parseAudioDataformat($metaData)) . '",
            audio_encoder           = "' . $db->real_escape_string( parseAudioEncoder($metaData)) . '",
            audio_profile           = "' . $db->real_escape_string( parseAudioProfile($metaData)) . '",
            video_dataformat        = "' . $db->real_escape_string( parseVideoDataformat($metaData)) . '",
            video_codec             = "' . $db->real_escape_string(parseVideoCodec($metaData)) . '",
            video_resolution_x      = ' . (int) parseVideoResolutionX($metaData) . ',
            video_resolution_y      = ' . (int) parseVideoResolutionY($metaData) . ',
            video_framerate         = ' . (int) parseVideoFrameRate($metaData) . ',
            error                   = "' . $db->real_escape_string(parseError($metaData)) . '",
            track_id                = "' . $track_id . '",
            disc                    = ' . (int)(parseDiscNumber($metaData)) . ',
            number                  = ' . $db->real_escape_string(parseTrackNumber($metaData)) . ',
            genre                   = "' . $db->real_escape_string(parseGenre($metaData)) . '",
            title                   = "' . $db->real_escape_string(parseTrackTitle($metaData)) . '",
            artist                  = "' . $db->real_escape_string(parseTrackArtist($metaData)) . '",
            comment                 = "' . $db->real_escape_string(parseComment($metaData)) . '",
            track_artist            = "' . $db->real_escape_string(parseTrackArtist($metaData)) . '",
            year                    = ' . parseYear($metaData) . ',
            dr                      = ' . parseAudioDynamicRange($metaData) . '
            WHERE relative_file     = BINARY "' . $db->real_escape_string($track['relative_file']) . '";';
        mysqli_query($db, $query);
    }

    // TODO: is this code really used or an old netjukebox corpse?
    if ($cfg['name_source'] === 'tags') {
        return;
    }
    $result = mysqli_query($db,'SELECT genre_id FROM genre WHERE genre="' . $db->real_escape_string( parseGenre($metaData)) . '"');
    if (mysqli_num_rows($result)==0) {
        mysqli_query($db,'INSERT INTO genre (genre_id, genre)
                    VALUES ("' . $db->real_escape_string( $lastGenre_id) . '",
                            "' . $db->real_escape_string( parseGenre($metaData)) . '")');
        $aGenre_id = $lastGenre_id;
        ++$lastGenre_id;
        return;
    }
    $row = mysqli_fetch_assoc($result);
    $genre_id = $row["genre_id"];
}


//  +------------------------------------------------------------------------+
//  | File identification                                                    |
//  +------------------------------------------------------------------------+
function fileId($file) {
	$filesize = filesize($file);
	
	if ($filesize > 5120) {
		$filehandle	= @fopen($file, 'rb') or message(__FILE__, __LINE__, 'error', '[b]Failed to open file:[/b][br]' . $file . '[list][*]Check file permission[/list]');
		fseek($filehandle, round(0.5 * $filesize - 2560 - 1));
		$data = fread($filehandle, 5120);
		$data .= ($filesize + (microtime(true) * mt_rand(1,1000)));
		fclose($filehandle);
	}
	else
		$data = @file_get_contents($file) or message(__FILE__, __LINE__, 'error', '[b]Failed to open file:[/b][br]' . $file . '[list][*]Check file permission[/list]');
	
	$crc32 = dechex(crc32($data));
	return str_pad($crc32, 8, '0', STR_PAD_LEFT);
}




//  +------------------------------------------------------------------------+
//  | Database cleanup                                                       |
//  +------------------------------------------------------------------------+
function databaseCleanup() {
	global $cfg, $db;
	// Clean up database
	mysqli_query($db,'DELETE FROM session WHERE idle_time = 0 AND create_time < ' . (int) (time() - 600));
	mysqli_query($db,'DELETE FROM random WHERE create_time < ' . (int) (time() - 3600));
	mysqli_query($db,'DELETE FROM share_download WHERE expire_time < ' . (int) time());
	mysqli_query($db,'DELETE FROM share_stream WHERE expire_time < ' . (int) time());
	mysqli_query($db,'DELETE share_download
		FROM share_download LEFT JOIN album
		ON share_download.album_id = album.album_id
		WHERE album.album_id IS NULL');
	mysqli_query($db,'DELETE share_stream
		FROM share_stream LEFT JOIN album
		ON share_stream.album_id = album.album_id
		WHERE album.album_id IS NULL');
	mysqli_query($db,'DELETE counter
		FROM counter LEFT JOIN album
		ON counter.album_id = album.album_id
		WHERE album.album_id IS NULL');
	mysqli_query($db,'DELETE counter
		FROM counter LEFT JOIN user
		ON counter.user_id = user.user_id
		WHERE user.user_id IS NULL');
	mysqli_query($db,'DELETE FROM favoriteitem WHERE track_id NOT IN (SELECT track_id FROM track) AND stream_url = ""');
	
	// Delete unavailable files from cache
	cacheCleanup();
	
	// Optimize tables
	$list	= array();
	$query	= mysqli_query($db,'SHOW TABLES');
	while ($table = mysqli_fetch_row($query))
		$list[] = $table[0];
	$list = implode(', ', $list);
	mysqli_query($db,'OPTIMIZE TABLE ' . $list);
}




//  +------------------------------------------------------------------------+
//  | Image update                                                           |
//  +------------------------------------------------------------------------+
function imageUpdate($flag) {
	global $cfg, $db;
	authenticate('access_admin');
	
	$size				= get('size');
	$artistSearch		= post('artist');
	$albumSearch		= post('album');
	$image_service_id	= (int) post('image_service_id');
	
	if (in_array($size, array('50', '100', '200'))) {
		mysqli_query($db,'UPDATE session
			SET thumbnail_size	= ' . (int) $size . '
			WHERE sid			= BINARY "' . $db->real_escape_string($cfg['sid']) . '"');
	}
	else
		$size = $cfg['thumbnail_size'];
		
	if (isset($cfg['image_service_name'][$image_service_id]) == false)
		message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]image_service_id');
	
	// flag 0 = No image
	// flag 1 = Skipped
	// flag 2 = Skipped not updated in this run
	// flag 3 = Stored image
	// flag 9 = Update one image by album_id, Needed for redirect to saveImage() (store as flag 1 or 3 in database)
	
	if ($flag == 2) {
		mysqli_query($db,'UPDATE bitmap SET flag = 2 WHERE flag = 1');
		$flag = 1;
	}
	if ($flag == 1) {
		$query = mysqli_query($db,'SELECT album.artist, album.album, album.album_id
			FROM album, bitmap
			WHERE bitmap.flag = 2
			AND bitmap.album_id = album.album_id
			ORDER BY album.artist_alphabetic, album.album');
	}
	elseif ($flag == 0) {
		$query = mysqli_query($db,'SELECT album.artist, album.album, album.album_id
			FROM album, bitmap
			WHERE bitmap.flag = 0
			AND bitmap.album_id = album.album_id
			ORDER BY album.artist_alphabetic, album.album');
	}
	elseif ($flag == 9 && $cfg['album_update_image']) {
		$album_id = getpost('album_id');
		$query = mysqli_query($db,'SELECT album.artist, album.artist_alphabetic, album.album, album.image_id, album.album_id,
			bitmap.flag, bitmap.image_front_width, bitmap.image_front_height
			FROM album, bitmap
			WHERE album.album_id = "' . $db->real_escape_string($album_id) . '"
			AND bitmap.album_id = album.album_id');
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Error internet image update[/b][br]Unsupported flag set');
	
	
	$album = mysqli_fetch_assoc($query);
	if ($album == '') {
		header('Location: ' . NJB_HOME_URL . 'config.php');
		exit();
	}
		
	if ($artistSearch == '' && $albumSearch == '') {
		// Remove (...) [...] {...} from the end
		$artistSearch	= preg_replace('#^(.+?)(?:\s*\(.+\)|\s*\[.+\]|\s*{.+})?$#', '$1', $album['artist']);
		$albumSearch	= preg_replace('#^(.+?)(?:\s*\(.+\)|\s*\[.+\]|\s*{.+})?$#', '$1', $album['album']);
	}
	
	$responce_url			= array();
	$responce_pixels		= array();
	$responce_resolution	= array();
	$responce_squire		= array();
	
	$url = $cfg['image_service_url'][$image_service_id];
	$url = str_replace('%artist', rawurlencode(iconv(NJB_DEFAULT_CHARSET, $cfg['image_service_charset'][$image_service_id], $artistSearch)), $url);
	$url = str_replace('%album', rawurlencode(iconv(NJB_DEFAULT_CHARSET, $cfg['image_service_charset'][$image_service_id], $albumSearch)), $url);
	
	if ($cfg['image_service_process'][$image_service_id] == 'amazon') {
		// Amazon web services
		if (function_exists('hash_hmac') == false)
		 	message(__FILE__, __LINE__, 'error', '[b]Missing hash_hmac function[/b][br]For the Amazone Web Service the hash_hmac function is required.');

		$url = str_replace('%awsaccesskeyid', rawurlencode($cfg['image_AWSAccessKeyId']), $url);
		$url = str_replace('%associatetag', rawurlencode($cfg['image_AWSAssociateTag'] ), $url);
		$url = str_replace('%timestamp', rawurlencode(gmdate('Y-m-d\TH:i:s\Z')), $url);
		
		$url_array = parse_url($url);
		
		// Sort on query key
		$query = $url_array['query'];
		$query = explode('&', $query);
		sort($query);
		$query = implode('&', $query);
		
		$signature = 'GET' . "\n";
		$signature .= $url_array['host'] . "\n";
		$signature .= $url_array['path'] . "\n";
		$signature .= $query;
		$signature = rawurlencode(base64_encode(hash_hmac('sha256', $signature, $cfg['image_AWSSecretAccessKey'], true)));
		
		// $url = $url_array['scheme'] . '://' . $url_array['host'] . $url_array['path'] . '?' . $query;
		$url .= '&Signature=' . $signature;
		$xml = @simplexml_load_file($url) or message(__FILE__, __LINE__, 'error', '[b]Failed to open XML file:[/b][br]' . $url);
				
		foreach ($xml->Items->Item as $item) {
			if (@$item->LargeImage->URL && @$item->LargeImage->Width && @$item->LargeImage->Height) {
				$responce_url[]			= $item->LargeImage->URL;
				$responce_pixels[]		= $item->LargeImage->Width * $item->LargeImage->Height;
				$responce_resolution[]	= $item->LargeImage->Width . ' x ' . $item->LargeImage->Height;
				$responce_squire[]		= ($item->LargeImage->Width/$item->LargeImage->Height > 0.95 && $item->LargeImage->Width/$item->LargeImage->Height < 1.05) ? true : false;
				
			}
		}
	}
	elseif ($cfg['image_service_process'][$image_service_id] == 'lastfm') {
		// Last.fm web services
		$url = str_replace('%api_key', rawurlencode($cfg['image_lastfm_api_key']), $url);
		$xml = @simplexml_load_file($url) or message(__FILE__, __LINE__, 'error', '[b]Failed to open XML file:[/b][br]' . $url);
			
		foreach ($xml->album->image as $image) {
			$imagesize = @getimagesize($image);
			$width = $imagesize[0];
			$height = $imagesize[1];

			$responce_url[]			= $image;
			$responce_pixels[]		= $width * $height;
			$responce_resolution[]	= $width . 'x' . $height;
			$responce_squire[]		= ($width/$height > 0.95 && $width/$height < 1.05) ? true : false;
		}
	}	
	else {
		// Regular expression
		$content = @file_get_contents($url) or message(__FILE__, __LINE__, 'error', '[b]Failed to open url:[/b][br]' . $url);
		
		if (preg_match_all($cfg['image_service_process'][$image_service_id], $content, $match)) {
			foreach ($match[1] as $key => $image) {
				if ($cfg['image_service_urldecode'][$image_service_id])
					$image = rawurldecode($image);
				$extension = substr(strrchr($image, '.'), 1);
				$extension = strtolower($extension);
				if (!in_array($extension, array('gif', 'bmp'))) {
					if (isset($match[2][$key]) && isset($match[3][$key])) {
						$width = $match[2][$key];
						$height = $match[3][$key];
					}
					else {
						$imagesize = @getimagesize($image);
						$width = $imagesize[0];
						$height = $imagesize[1];
					}
					$responce_url[]			= $image;
					$responce_pixels[]		= $width * $height;
					$responce_resolution[]	= $width . 'x' . $height;
					$responce_squire[]		= ($width/$height > 0.95 && $width/$height < 1.05) ? true : false;
				}
			}
		}
	}
	
	// squire images first:
	array_multisort($responce_squire, SORT_DESC, $responce_pixels, SORT_DESC, $responce_url, $responce_resolution);
		
	$colombs = floor((cookie('netjukebox_width') - 20) / ($size + 10));
	$max_images = count($responce_squire) + 2; // n + "no image available" + "upload"
	if (isset($album['flag']) && $album['flag'] == 3)
		$max_images += 1; // Current image
		
	if ($flag == 9) {
		$cfg['menu'] = 'media';
		// formattedNavigator
		$nav			= array();
		$nav['name'][]	= 'Media';
		$nav['url'][]	= 'index.php';
		$nav['name'][]	= $album['artist_alphabetic'];
		$nav['url'][]	= 'index.php?action=view2&amp;artist=' . rawurlencode($album['artist_alphabetic']);
		$nav['name'][]	= $album['album'];
		$nav['url'][]	= 'index.php?action=view3&amp;album_id=' . rawurlencode($album_id);
		$nav['name'][]	= 'Update image';
	}
	else {
		// formattedNavigator
		$nav			= array();
		$nav['name'][]	= 'Configuration';
		$nav['url'][]	= 'config.php';
		$nav['name'][]	= 'Update image';
	}
	
	require_once('include/header.inc.php');
?>
<form action="update.php" method="post">
		<input type="hidden" name="action" value="imageUpdate">
		<input type="hidden" name="flag" value="<?php echo $flag; ?>">
		<input type="hidden" name="album_id" value="<?php if (isset($album_id)) echo $album_id; ?>">
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td colspan="<?php echo $colombs + 2; ?>">
	<!-- begin table header -->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr class="header">
		<td class="space"></td>
		<td><?php echo html($album['artist']) . ' - ' . html($album['album']); ?></td>
		<td align="right">
			<!-- Brake image tag to prevent space -->
			<a href="update.php?action=imageUpdate<?php if (isset($album_id)) echo '&amp;album_id=' . $album_id; ?>&amp;flag=<?php echo $flag; ?>&amp;size=50"><img src="<?php echo $cfg['img']; ?>small_header_image50_<?php echo ($size == '50') ? 'on' : 'off'; ?>.png" alt="" class="small align"></a><a href="update.php?action=imageUpdate<?php if (isset($album_id)) echo '&amp;album_id=' . $album_id; ?>&amp;flag=<?php echo $flag; ?>&amp;size=100"><img src="<?php echo $cfg['img']; ?>small_header_image100_<?php echo ($size == '100') ? 'on' : 'off'; ?>.png" alt="" class="small align"></a><a href="update.php?action=imageUpdate<?php if (isset($album_id)) echo '&amp;album_id=' . $album_id; ?>&amp;flag=<?php echo $flag; ?>&amp;size=200"><img src="<?php echo $cfg['img']; ?>small_header_image200_<?php echo ($size == '200') ? 'on' : 'off'; ?>.png" alt="" class="small align"></a>
		</td>
	</tr>
	</table>
	<!-- end table header -->
	</td>
</tr>
<tr class="line"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr class="odd smallspace"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<?php
	for ($i=0; $i < ceil($max_images / $colombs); $i++) {
		$class = ($i & 1) ? 'even' : 'odd';
?>
<tr class="<?php echo $class; ?>">
	<td class="smallspace">&nbsp;</td>
<?php
		for ($j=1; $j <= $colombs; $j++) { ?>
	<td width="<?php echo $size + 10; ?>" height="<?php echo $size + 10; ?>" align="center">
	<span id="image<?php echo $i * $colombs + $j; ?>"><img src="image/transparent.gif" alt="" width="<?php echo $size; ?>" height="<?php echo $size; ?>" class="align"></span>
	</td>
<?php
		} ?>
	<td class="smallspace">&nbsp;</td>
</tr>
<?php
	} ?>
<tr class="<?php echo $class; ?> smallspace"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr class="line"><td colspan="<?php echo $colombs + 2; ?>"></td></tr>
<tr class="footer">
	<td colspan="<?php echo $colombs + 2; ?>">
	<!-- begin table footer -->
	<table cellspacing="0" cellpadding="0">
	<tr class="footer smallspace"><td colspan="6"></td></tr>
	<tr class="footer">
		<td class="space"></td>
		<td>Artist:</td>
		<td class="space"></td>
		<td><input type="text" name="artist" value="<?php echo html($artistSearch); ?>" class="edit"></td>
		<td class="textspace"></td>
		<td>		
		<select name="image_service_id">
<?php
	foreach ($cfg['image_service_name'] as $key => $value)
		echo "\t\t" . '<option value="' . $key . '"' . (($image_service_id == $key) ? ' selected' : ''). '>' . html($value) . '</option>' . "\n"; ?>
		</select>		
		</td>
	</tr>
	<tr class="footer smallspace"><td colspan="6"></td></tr>
	<tr class="footer">
		<td></td>		
		<td>Album:</td>
		<td></td>
		<td><input type="text" name="album" value="<?php echo html($albumSearch); ?>" class="edit"></td>
		<td></td>
		<td><input type="image" src="<?php echo $cfg['img']; ?>button_small_search.png"></td>
	</tr>
	<tr class="footer smallspace">
		<td colspan="10"></td>
	</tr>
	</table>
	<!-- end table footer -->
	</td>
</tr>
</table>
</form>
<?php
	$cfg['footer'] = 'dynamic';
	require('include/footer.inc.php');

	$i = 0;
	if (isset($album['flag']) && $album['flag'] == 3) {
		// Show current image
		$i++;
		$mouseover = ' onMouseOver="return overlib(\\\'' . $album['image_front_width'] . ' x ' . $album['image_front_height'] . '\\\', CAPTION, \\\'Current image:&nbsp;\\\');" onMouseOut="return nd();"';
		$url = '<a href="index.php?action=view3&amp;album_id=' . rawurlencode($album_id) . '"' . $mouseover . '><img src="image.php?image_id=' . $album['image_id'] . '" alt="" width="' . $size . '" height="' . $size . '" class="align"><\/a>';
		echo '<script type="text/javascript">document.getElementById(\'image' . $i . '\').innerHTML=\'' . $url . '\';</script>' . "\n";
	}
	
	foreach ($responce_url as $key => $image) {
		$i++;
		$mouseover = ' onMouseOver="return overlib(\\\'' . html($responce_resolution[$key]) . '\\\');" onMouseOut="return nd();"';
		$url = '<a href="update.php?action=saveImage&flag=' . $flag . '&amp;album_id=' . $album['album_id'] . '&amp;image=' . rawurlencode($image) . '&amp;sign=' . $cfg['sign'] . '"' . $mouseover . '><img src="image.php?image=' . rawurlencode($image) . '" alt="" width="' . $size . '" height="' . $size . '" class="align"><\/a>';
		echo '<script type="text/javascript">document.getElementById(\'image' . $i . '\').innerHTML=\'' . $url . '\';</script>' . "\n";
	}
	
	$i++;
	$mouseover = ' onMouseOver="return overlib(\\\'No image\\\');" onMouseOut="return nd();"';
	$url = '<a href="update.php?action=saveImage&amp;flag=' . $flag . '&amp;album_id=' . $album['album_id'] . '&amp;image=noImage&amp;sign=' . $cfg['sign'] . '"' . $mouseover . '><img src="image/no_image.png" alt="" width="' . $size . '" height="' . $size . '" class="align"><\/a>';
	echo '<script type="text/javascript">document.getElementById(\'image' . $i . '\').innerHTML=\'' . $url . '\';</script>' . "\n";
	$i++;
	
	$mouseover = ' onMouseOver="return overlib(\\\'Upload\\\');" onMouseOut="return nd();"';
	$url = '<a href="update.php?action=selectImageUpload&amp;flag=' . $flag . '&amp;album_id=' . $album['album_id'] . '"' . $mouseover . '><img src="skin/' . rawurlencode($cfg['skin']) . '/img/large_upload.png" alt="" width="' . $size . '" height="' . $size . '" class="align"><\/a>';
	echo '<script type="text/javascript">document.getElementById(\'image' . $i . '\').innerHTML=\'' . $url . '\';</script>' . "\n";
	
	$cfg['footer'] = 'close';
	require('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Save image                                                             |
//  +------------------------------------------------------------------------+
function saveImage($flag_flow) {
	global $cfg, $db;
	authenticate('access_admin', false, true);
	
	$source = get('image');
	$album_id = get('album_id');
	
	$query		= mysqli_query($db,'SELECT relative_file FROM track WHERE album_id = "' . $db->real_escape_string($album_id) . '"');
	$track		= mysqli_fetch_assoc($query);
	$image_dir	= $cfg['media_dir'] . $track['relative_file'];
	$image_dir	= substr($image_dir, 0, strrpos($image_dir, '/') + 1);
	
	if ($track == false)
		message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]album_id not found in database');
	
	if ($source == 'noImage') {
		$image = NJB_HOME_DIR . 'image/no_image.png';
		if (is_file($image_dir . $cfg['image_front'] . '.jpg') && @unlink($image_dir . $cfg['image_front'] . '.jpg') == false)
			message(__FILE__, __LINE__, 'error', '[b]Failed to delete file:[/b][br]' . $image_dir . $cfg['image_front'] . '.jpg');
		if (is_file($image_dir . $cfg['image_front'] . '.png') && @unlink($image_dir . $cfg['image_front'] . '.png') == false)
			message(__FILE__, __LINE__, 'error', '[b]Failed to delete file:[/b][br]' . $image_dir . $cfg['image_front'] . '.png');
		
		$flag = 1; // Skipped (or Delete)		
	}
	else {
		$imagesize = @getimagesize($source) or message(__FILE__, __LINE__, 'error', '[b]Save image error[/b][br]Unsupported file.');
		if ($imagesize[2] == IMAGETYPE_JPEG) {
			$image = $image_dir . $cfg['image_front'] . '.jpg';
			$delete = $image_dir . $cfg['image_front'] . '.png';
		}
		elseif ($imagesize[2] == IMAGETYPE_PNG) {
			$image = $image_dir . $cfg['image_front'] . '.png';
			$delete = $image_dir . $cfg['image_front'] . '.jpg';
		}
		else
			message(__FILE__, __LINE__, 'error', '[b]Save image error[/b][br]Unsupported file.');

		if (copy($source, $image) == false)
			message(__FILE__, __LINE__, 'error', '[b]Failed to copy[/b][br]from: ' . $source . '[br]to: ' . $image);
		if (is_file($delete) && @unlink($delete) == false)
			message(__FILE__, __LINE__, 'error', '[b]Failed to delete file:[/b][br]' . $delete);
		
		$flag = 3; // Stored image
	}
	
	$filemtime	= filemtime($image);
	$filesize	= filesize($image);
	$imagesize	= @getimagesize($image) or message(__FILE__, __LINE__, 'error', '[b]Failed to read image information from:[/b][br]' . $image);
	$image_id	= (($flag == 3) ? $album_id : 'no_image');
	$image_id	.= '_' . base_convert(NJB_IMAGE_SIZE * 100 + NJB_IMAGE_QUALITY, 10, 36) . base_convert($filemtime, 10, 36) . base_convert($filesize, 10, 36); 
	 
	$relative_image = substr($image, strlen($cfg['media_dir']));
	mysqli_query($db,'UPDATE bitmap SET
		image				= "' . $db->real_escape_string(resampleImage($image)) . '",
		filesize			= ' . (int) $filesize . ',
		filemtime			= ' . (int) $filemtime . ',
		flag				= ' . (int) $flag . ',
		image_front			= "' . ($flag == 3 ? $db->real_escape_string($relative_image) : '') . '",
		image_front_width	= ' . ($flag == 3 ? $imagesize[0] : 0) . ',
		image_front_height	= ' . ($flag == 3 ? $imagesize[1] : 0) . ',
		image_id			= "' . $db->real_escape_string($image_id) . '"
		WHERE album_id		= "' . $db->real_escape_string($album_id) . '"');
		
	mysqli_query($db,'UPDATE album SET
		image_id			= "' . $db->real_escape_string($image_id) . '"
		WHERE album_id		= "' . $db->real_escape_string($album_id) . '"');
	
	if ($flag_flow == 9) {
		header('Location: ' . NJB_HOME_URL . 'index.php?action=view3&album_id=' . $album_id);
		exit();
	}
	else
		imageUpdate($flag_flow);
}




//  +------------------------------------------------------------------------+
//  | Select image upload                                                    |
//  +------------------------------------------------------------------------+
function selectImageUpload($flag) {
	global $cfg, $db;
	authenticate('access_admin');
	
	$album_id = get('album_id');
	
	$query = mysqli_query($db,'SELECT artist, artist_alphabetic, album, album_id
		FROM album
		WHERE album_id = "' . $db->real_escape_string($album_id) . '"');
	$album = mysqli_fetch_assoc($query);
	
	if ($album == false)
		message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]album_id not found in database');
	
	if ($flag == 0 || $flag == 1) {
		$cancel = 'update.php?action=imageUpdate&amp;flag=' . rawurlencode($flag);
		// formattedNavigator
		$nav			= array();
		$nav['name'][]	= 'Configuration';
		$nav['url'][]	= 'config.php';
		$nav['name'][]	= 'Update image';
		$nav['url'][]	= 'update.php?action=imageUpdate&amp;flag=' . rawurlencode($flag);
		$nav['name'][]	= 'Upload';
	}
	elseif ($flag == 9 && $cfg['album_update_image']) {
		$cfg['menu'] = 'media';
		$cancel = 'index.php?action=view3&amp;album_id=' . rawurlencode($album_id);
		// formattedNavigator
		$nav			= array();
		$nav['name'][]	= 'Media';
		$nav['url'][]	= 'index.php';
		$nav['name'][]	= $album['artist_alphabetic'];
		$nav['url'][]	= 'index.php?action=view2&amp;artist=' . rawurlencode($album['artist_alphabetic']);
		$nav['name'][]	= $album['album'];
		$nav['url'][]	= 'index.php?action=view3&amp;album_id=' . rawurlencode($album_id);
		$nav['name'][]	= 'Update image';
		$nav['url'][]	= 'update.php?action=imageUpdate&amp;flag=9&amp;album_id=' . rawurlencode($album_id);
		$nav['name'][]	= 'Upload';
	}
	else
		message(__FILE__, __LINE__, 'error', '[b]Error internet image update[/b][br]Unsupported flag set');
	
	require_once('include/header.inc.php');
?>
<form action="update.php" method="post" enctype="multipart/form-data">
		<input type="hidden" name="action" value="imageUpload">
		<input type="hidden" name="flag" value="<?php echo $flag; ?>">
		<input type="hidden" name="album_id" value="<?php echo html($album_id); ?>">
		<input type="hidden" name="sign" value="<?php echo html($cfg['sign']); ?>">
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td></td>
	<td colspan="3">Upload</td>
	<td></td>
</tr>
<tr class="odd">
	<td class="space"></td>
	<td>Front image:</td>
	<td class="textspace"></td>
	<td><input type="file" name="image_front"></td>
	<td class="space"></td>
</tr>
<tr class="even">
	<td class="space"></td>
	<td>Back image:</td>
	<td class="textspace"></td>
	<td><input type="file" name="image_back"></td>
	<td class="space"></td>
</tr>
</table>
<br>
<input type="image" src="<?php echo $cfg['img']; ?>button_upload.png" class="space">&nbsp;
<a href="<?php echo $cancel; ?>"><img src="<?php echo $cfg['img']; ?>button_cancel.png" alt="" class="align"></a>
</form>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | Image upload                                                           |
//  +------------------------------------------------------------------------+
function imageUpload($flag_flow) {
	global $cfg, $db;
	authenticate('access_admin', false, true);
	
	if (ini_get('file_uploads') == false)
		message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]File uploads disabled in the php.ini.');
	
	if ($_FILES['image_front']['error'] == UPLOAD_ERR_NO_FILE && $_FILES['image_back']['error'] == UPLOAD_ERR_NO_FILE)
		message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]There is no file uploaded');
	
	if ($_FILES['image_front']['error'] != UPLOAD_ERR_OK && $_FILES['image_front']['error'] != UPLOAD_ERR_NO_FILE) {
		if ($_FILES['image_front']['error'] == UPLOAD_ERR_INI_SIZE)			message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]The file is larger than the value set in php.ini for upload_max_file');
		elseif ($_FILES['image_front']['error'] == UPLOAD_ERR_PARTIAL)		message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]The file is not fully uploaded');
		elseif ($_FILES['image_front']['error'] == UPLOAD_ERR_NO_TMP_DIR)	message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]PHP, the directory for the temporary file not found');
		elseif ($_FILES['image_front']['error'] == UPLOAD_ERR_CANT_WRITE)	message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]PHP could not write the temporary file');
		else																message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]Error code: ' . $_FILES['image_front']['error']);
	}
	
	if ($_FILES['image_back']['error'] != UPLOAD_ERR_OK && $_FILES['image_back']['error'] != UPLOAD_ERR_NO_FILE) {
		if ($_FILES['image_back']['error'] == UPLOAD_ERR_INI_SIZE)			message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]The file is larger than the value set in php.ini for upload_max_file');
		elseif ($_FILES['image_back']['error'] == UPLOAD_ERR_PARTIAL)		message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]The file is not fully uploaded');
		elseif ($_FILES['image_back']['error'] == UPLOAD_ERR_NO_TMP_DIR)	message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]PHP, the directory for the temporary file not found');
		elseif ($_FILES['image_back']['error'] == UPLOAD_ERR_CANT_WRITE)	message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]PHP could not write the temporary file');
		else																message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]Error code: ' . $_FILES['image_back']['error']);
	}
	
	$album_id	= post('album_id');
	$query		= mysqli_query($db,'SELECT relative_file FROM track WHERE album_id = "' . $db->real_escape_string($album_id) . '"');
	$track		= mysqli_fetch_assoc($query);
	$image_dir	= $cfg['media_dir'] . $track['relative_file'];
	$image_dir	= substr($image_dir, 0, strrpos($image_dir, '/') + 1);
	
	if ($track == false)
		message(__FILE__, __LINE__, 'error', '[b]Error[/b][br]album_id not found in database');
	
	if ($_FILES['image_front']['error'] == UPLOAD_ERR_OK)
		{
		$imagesize = @getimagesize($_FILES['image_front']['tmp_name']) or message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]Unsupported file.');
		if ($imagesize[2] == IMAGETYPE_JPEG) {
			$image = $image_dir . $cfg['image_front'] . '.jpg';
			$delete = $image_dir . $cfg['image_front'] . '.png';
		}
		elseif ($imagesize[2] == IMAGETYPE_PNG) {
			$image = $image_dir . $cfg['image_front'] . '.png';
			$delete = $image_dir . $cfg['image_front'] . '.jpg';
		}
		else
			message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]Unsupported file.');
		
		if (copy($_FILES['image_front']['tmp_name'], $image) == false)
			message(__FILE__, __LINE__, 'error', '[b]Failed to copy[/b][br]from: ' . $_FILES['image_front']['tmp_name'] . '[br]to: ' . $image);
		if (is_file($delete) && @unlink($delete) == false)
			message(__FILE__, __LINE__, 'error', '[b]Failed to delete file:[/b][br]' . $delete);
		
		$flag		= 3; // stored
		$filemtime	= filemtime($image);
		$filesize	= filesize($image);
		$image_id	= $album_id . '_' . base_convert(NJB_IMAGE_SIZE * 100 + NJB_IMAGE_QUALITY, 10, 36) . base_convert($filemtime, 10, 36) . base_convert($filesize, 10, 36);
				
		$relative_image = substr($image, strlen($cfg['media_dir']));
		mysqli_query($db,'UPDATE bitmap SET
			image				= "' . $db->real_escape_string(resampleImage($image)) . '",
			filesize			= ' . (int) $filesize . ',
			filemtime			= ' . (int) $filemtime . ',
			flag				= ' . (int) $flag . ',
			image_front			= "' . $db->real_escape_string($relative_image) . '",
			image_front_width	= ' . (int) $imagesize[0] . ',
			image_front_height	= ' . (int) $imagesize[1] . ',
			image_id			= "' . $db->real_escape_string($image_id) . '"
			WHERE album_id		= "' . $db->real_escape_string($album_id) . '"');
		
		mysqli_query($db,'UPDATE album SET
			image_id			= "' . $db->real_escape_string($image_id) . '"
			WHERE album_id		= "' . $db->real_escape_string($album_id) . '"');
	}
	
	if ($_FILES['image_back']['error'] == UPLOAD_ERR_OK) {
		$imagesize = @getimagesize($_FILES['image_back']['tmp_name']) or message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]Unsupported file.');
		if ($imagesize[2] == IMAGETYPE_JPEG) {
			$image = $image_dir . $cfg['image_back'] . '.jpg';
			$delete = $image_dir . $cfg['image_back'] . '.png';
		}
		elseif ($imagesize[2] == IMAGETYPE_PNG) {
			$image = $image_dir . $cfg['image_back'] . '.png';
			$delete = $image_dir . $cfg['image_back'] . '.jpg';
		}
		else message(__FILE__, __LINE__, 'error', '[b]Upload error[/b][br]Unsupported file.');
		
		if (copy($_FILES['image_back']['tmp_name'], $image) == false)
			message(__FILE__, __LINE__, 'error', '[b]Failed to copy[/b][br]from: ' . $_FILES['image_back']['tmp_name'] . '[br]to: ' . $image);
		if (is_file($delete) && @unlink($delete) == false)
			message(__FILE__, __LINE__, 'error', '[b]Failed to delete file:[/b][br]' . $delete);
		
		$relative_image = substr($image, strlen($cfg['media_dir']));
		mysqli_query($db,'UPDATE bitmap SET
			image_back			= "' . $db->real_escape_string($relative_image) . '"
			WHERE album_id		= "' . $db->real_escape_string($album_id) . '"');
	}
	
	if ($flag_flow == 9) {
		header('Location: ' . NJB_HOME_URL . 'index.php?action=view3&album_id=' . $album_id);
		exit();
	}
	else
		imageUpdate($flag_flow);
}




//  +------------------------------------------------------------------------+
//  | Resample image                                                         |
//  +------------------------------------------------------------------------+
function resampleImage($image, $size = NJB_IMAGE_SIZE) {
	$extension = strtolower(substr(strrchr($image, '.'), 1));
	/* 
	if		($extension == 'jpg')	$src_image = @imageCreateFromJpeg($image)	or message(__FILE__, __LINE__, 'error', '[b]Failed to resample image:[/b][br]' . $image);
	elseif	($extension == 'png')	$src_image = @imageCreateFromPng($image)	or message(__FILE__, __LINE__, 'error', '[b]Failed to resample image:[/b][br]' . $image);
	 */
	 
	 
	/* if ($extension == 'jpg') {
		cliLog("resampleImage jpg");
		$src_image = @imageCreateFromJpeg($image);
	}
	elseif ($extension == 'png') {
		cliLog("resampleImage png");
		$src_image = @imageCreateFromPng($image);	
	}
	else {
		cliLog("resampleImage wrong extension");
		message(__FILE__, __LINE__, 'error', '[b]Failed to resample image:[/b][br]Unsupported extension.');
	} */
	
	$src_image = imageCreateFromAny($image);
	
	if (!$src_image) {
		cliLog("resampleImage error");
		logImageError();
		return @file_get_contents(NJB_HOME_DIR . 'image/no_image.png');
	}
	
	if (is_jpg($image) && imageSX($src_image) == $size && imageSY($src_image) == $size) {
		cliLog("resampleImage data");
		$data = @file_get_contents($image) or message(__FILE__, __LINE__, 'error', '[b]Failed to open file:[/b][br]' . $image);
	}
	elseif (imageSY($src_image) / imageSX($src_image) <= 1) {
		cliLog("resampleImage crop 1");
		// Crops from left and right to get a squire image.
		$sourceWidth		= imageSY($src_image);
		$sourceHeight		= imageSY($src_image);
		$sourceX			= round((imageSX($src_image) - imageSY($src_image)) / 2);
		$sourceY			= 0;
	}
	else {
		cliLog("resampleImage crop 2");
		// Crops from top and bottom to get a squire image.
		$sourceWidth		= imageSX($src_image);
		$sourceHeight		= imageSX($src_image);
		$sourceX			= 0;
		$sourceY			= round((imageSY($src_image) - imageSX($src_image)) / 2);
	}
	if (isset($sourceWidth)) {
		cliLog("resampleImage imageCreateTrueColor");
		$dst_image = ImageCreateTrueColor($size, $size);
		cliLog("resampleImage imageCopyResampled");
		imageCopyResampled($dst_image, $src_image, 0, 0, $sourceX, $sourceY, $size, $size, $sourceWidth, $sourceHeight);
		ob_start();
		cliLog("resampleImage imageJpeg");
		imageJpeg($dst_image, NULL, NJB_IMAGE_QUALITY); 
		$data = ob_get_contents();
		ob_end_clean();
		cliLog("resampleImage imageDestroy 1");
		imageDestroy($dst_image);
	}
	cliLog("resampleImage imageDestroy 2");
	imageDestroy($src_image);
	cliLog("resampleImage done");
	//cliLog("image data: " . $data);
	
	return $data;
}

//  +------------------------------------------------------------------------+
//  | Create image from any file type by                                     |
//  | matt dot squirrell dot php at hsmx dot com                             |
//  +------------------------------------------------------------------------+
function imageCreateFromAny($filepath) {
		//cliLog("imageCreateFromAny exif_imagetype");
		
    /* $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
    $allowedTypes = array(
        // 1,  // [] gif
        2,  // [] jpg
        3  // [] png
        //6   // [] bmp
    );
    if (!in_array($type, $allowedTypes)) {
        return false;
    } */
		
		cliLog("imageCreateFromAny check file");
		if (is_jpg($filepath)) {
			$type = 2; //jpg file
		}
		else if (is_png($filepath)) {
			$type = 3; //png file
		}
		else {
			return false;
		}
		
    switch ($type) {
        case 1 :
            $im = imageCreateFromGif($filepath);
        break;
        case 2 :
						cliLog("imageCreateFromAny exif_imagetype JPG");
            $im = imageCreateFromJpeg($filepath);
        break;
        case 3 :
						cliLog("imageCreateFromAny exif_imagetype PNG");
            $im = imageCreateFromPng($filepath);
        break;
        case 6 :
            $im = imageCreateFromBmp($filepath);
        break;
    }   
    return $im; 
} 

//  +------------------------------------------------------------------------+
//  | Mark images with error                                                 |
//  +------------------------------------------------------------------------+
function logImageError() {
	global $image, $flag, $image_front;
	
	$image_front = $image = NJB_HOME_DIR . 'image/no_image.png';
	$flag = 10; // image error
}

?>
