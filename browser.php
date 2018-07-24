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

/* (c) 2004 by £ukasz Tomicki <tomicki(at)o2(dot)pl> 		*/
/* Most recent version available @ : http://tomicki.net/	*/
/* Version: 0.8    											*/
/*
/*  This program is free software; you can redistribute it and/or modify
/*  it under the terms of the GNU General Public License as published by
/*  the Free Software Foundation; either version 2 of the License, or
/*  (at your option) any later version.
/*
/*  This program is distributed in the hope that it will be useful,
/*  but WITHOUT ANY WARRANTY; without even the implied warranty of
/*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
/*  GNU Library General Public License for more details.
/*
/*  You should have received a copy of the GNU General Public License
/*  along with this program; if not, write to the Free Software
/*  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
/*															*/



//  +------------------------------------------------------------------------+
//  | playlist.php                                                           |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
$cfg['menu'] = 'Library';

// formattedNavigator
$nav			= array();
$nav['name'][]	= 'Browser';

authenticate('access_playlist');
require_once('include/header.inc.php');
require_once('include/library.inc.php');
require_once('include/play.inc.php');

error_reporting(-1);
@ini_set('display_errors', 0);


function sortRows($data)
{
	$size = count($data);

	for ($i = 0; $i < $size; ++$i) {
		$row_num = findSmallest($i, $size, $data);
		$tmp = $data[$row_num];
		$data[$row_num] = $data[$i];
		$data[$i] = $tmp;
	}

	return ($data);
}

function findSmallest($i, $end, $data)
{
	$min['pos'] = $i;
	$min['value'] = $data[$i]['data'];
	$min['dir'] = $data[$i]['dir'];
	for (; $i < $end; ++$i) {
		if ($data[$i]['dir']) {
			if ($min['dir']) {
				if ($data[$i]['data'] < $min['value']) {
					$min['value'] = $data[$i]['data'];
					$min['dir'] = $data[$i]['dir'];
					$min['pos'] = $i;
				}
			} else {
				$min['value'] = $data[$i]['data'];
				$min['dir'] = $data[$i]['dir'];
				$min['pos'] = $i;
			}
		} else {
			if (!$min['dir'] && $data[$i]['data'] < $min['value']) {
				$min['value'] = $data[$i]['data'];
				$min['dir'] = $data[$i]['dir'];
				$min['pos'] = $i;
			}
		}
	}
	return ( $min['pos'] );
}


$self = $_SERVER['PHP_SELF'];
$showSelect = isset($_GET['showSelect']) ? $_GET['showSelect'] : '';
$showUpdateSelect = isset($_GET['showUpdateSelect']) ? $_GET['showUpdateSelect'] : '';
$showSelectQS = '';
$tileSizePHP = isset($_GET['tileSizePHP']) ? ('tileSizePHP=' . $_GET['tileSizePHP'] . '&') : '';
//show 'Select this dir' button
if ($showSelect == 'true') {
	$showSelectQS = 'showSelect=true&';
}
if ($showUpdateSelect == 'true') {
	$showSelectQS = 'showUpdateSelect=true&';
}
if (isset($_GET['dir'])) {
	$dir = str_replace('ompd_ampersand_ompd','&',$_GET['dir']);
	//$dir = myDecode($_GET['dir']);
	//echo $dir;
	$allowAccess = false;
	//restrict acccess to files/folders outside media_dir
	if (!$cfg['allow_access_to_all_files']) {
		//$pos = strpos($_GET['dir'],$cfg['media_dir']);
		$pos = strpos($dir,$cfg['media_dir']);
		if ($pos !== false) {
			$allowAccess = true;
		}
	}
	else {
		$allowAccess = true;
	}
	if ($allowAccess) {
		//$dir = $_GET['dir'];
		$size = strlen($dir);
		if ($dir == '/') {
			//echo print_r($dir . '<br><br>');
			$dir = '';
			//echo print_r($dir . '<br><br>');
		}
		else {
			while ($dir[$size - 1] == '/') {
				$dir = substr($dir, 0, $size - 1);
				$size = strlen($dir);
			}
		}
	}
	else {
		$dir = substr($cfg['media_dir'], 0, -1);
	}
} 
else {
	/* $dir = $_SERVER["SCRIPT_FILENAME"];
	$size = strlen($dir);
	while ($dir[$size - 1] != '/') {
		$dir = substr($dir, 0, $size - 1);
		$size = strlen($dir);
	}
	$dir = substr($dir, 0, $size - 1); */
	$dir = substr($cfg['media_dir'], 0, -1);
}
//echo print_r($dir . '<br><br>');
$actDir = ($dir == '') ? '/' : $dir;
if ($actDir != '/') {
	$actDirSplitted = explode('/',$actDir);
	$actDirToHref = '';
	$actDirToShow = '';
	//echo print_r($dir . '<br><br>');
	foreach ($actDirSplitted as $part){
		if (NJB_WINDOWS == 1) {
			if ($part != '') $actDirToHref .= $part . '/';
		}
		else {
			if ($part != '') $actDirToHref .= '/'. $part;
		}
		$actDirToShow .= '<a href="' . $self. '?' . $showSelectQS . 'dir=' . rawurlencode($actDirToHref) . '">' . $part . '</a>/';
	}
} 
else {
	$actDirToShow = '/';
}
echo '<span class="nav_tree break-word">DIR: ' . $actDirToShow . '</span>';
if ($showSelect == 'true') {
	?>
	<div class="buttons">
	<span id="selectDir" onclick="window.location.href='index.php?action=viewRandomFile&<?php echo $tileSizePHP; ?>selectedDir=<?php echo str_replace('%26','ompd_ampersand_ompd',rawurlencode($dir));?>'">Select this directory</span>
	</div>
	<?php
}

$inMediaDir = false;
$pos = strpos($dir,substr($cfg['media_dir'], 0, -1));
if ($pos !== false) {
	$inMediaDir = true;
}

if ($showUpdateSelect == 'true' && $inMediaDir == true) {
	?>
	<div class="buttons">
	<span id="selectDir" onclick="window.location.href='config-update-select.php?action=updateSelect&<?php echo $tileSizePHP; ?>selectedDir=<?php echo str_replace('%26','ompd_ampersand_ompd',rawurlencode($dir));?>'">Select this directory</span>
	</div>
	<?php
}
?>

<div id="goToAlbum" class="buttons no-display">
	<span>Go to album</span>
</div>

<script>
$(window).on('load', function (e) {
	var h = $("#fixedMenu").height();
	h =  -(h + 5); 
	//only this works in Chrome
	setTimeout(function(){ window.scrollBy(0, h); }, 1);
});
</script>
<?php
$dir = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir);
if (is_dir($dir) || $dir == '') {
	if ($dir == '') $dir = '/';
	/* if ($handle = opendir($dir)) {
		$dir = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $dir);
		$size_document_root = strlen($_SERVER['DOCUMENT_ROOT']);
		$pos = strrpos($dir, "/");
		$topdir = substr($dir, 0, $pos + 1);
		if ($dir == '/') $topdir = '/';
		$i = 0;
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				$rows[$i]['data'] = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $file);
				$rows[$i]['dir'] = is_dir(iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir) . "/" . $file);
				$i++;
			}
		}
		closedir($handle);
	} */
	if ($handle = opendir($dir)) {
		$rows = array();
		$rows_non_alphanumeric = array();
		$rows_f = array();
		$rows_f_non_alphanumeric = array();
		$dir = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $dir);
		$size_document_root = strlen($_SERVER['DOCUMENT_ROOT']);
		$pos = strrpos($dir, "/");
		$topdir = substr($dir, 0, $pos + 1);
		if ($dir == '/') $topdir = '/';
		$i = 0;
		while ($files[] = readdir($handle)); 
		natcasesort($files);
		closedir($handle);
		foreach ($files as $file) {
			if ($file != "." && $file != ".." && $file) {
				if (ctype_alnum(substr($file,0,1))) {
					if (is_dir(iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir) . "/" . $file)) {
						$rows[$i]['data'] = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $file);
						$rows[$i]['dir'] = is_dir(iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir) . "/" . $file);
					}
					else {
						$rows_f[$i]['data'] = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $file);
						$rows_f[$i]['dir'] = is_dir(iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir) . "/" . $file);
					}
				}
				else {
					if (is_dir(iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir) . "/" . $file)) {
						$rows_non_alphanumeric[$i]['data'] = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $file);
						$rows_non_alphanumeric[$i]['dir'] = is_dir(iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir) . "/" . $file);
					}
					else {
						$rows_f_non_alphanumeric[$i]['data'] = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $file);
						$rows_f_non_alphanumeric[$i]['dir'] = is_dir(iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir) . "/" . $file);
					}
				}
				$i++;
			}
			
		}
		$rows = array_merge($rows_non_alphanumeric, $rows, $rows_f_non_alphanumeric, $rows_f);
	}

	$size = count($rows);
	//$rows = sortRows($rows);
	//echo print_r($rows);
	echo "<table class='border tabFixed' cellspacing='0' cellpadding='0'>";
	$showUpDir = false;
	
	if ($dir != '/') {
		$pos = strpos($topdir,$cfg['media_dir']);
		if ($cfg['allow_access_to_all_files']) {
			$showUpDir = true;
		}
		elseif ($pos !== false) {
			$showUpDir = true; 
		}
	}
	
	if ($showUpDir) {
		if ($actDir <> '/') {
			$pos = strrpos($actDir,'/');
			$inPagePos = substr($actDir,$pos + 1,strlen($actDir) - $pos);
			$inPagePos = myUrlencode($inPagePos);
		}
		echo "<tr class='artist_list'>";
		echo "<td class='icon'><i class='fa fa-level-up icon-small'></i></td>";
		echo "<td class='icon'>";
		echo "</td>";
		echo '<td class="fileBrowserItemName">';
		echo "<a href='" . $self . "?" . $showSelectQS . "dir=" . rawurlencode($topdir) . "#" . $inPagePos . "'>Up to parent dir</a>\n";
		echo "</td>";
		echo "<td>";
		//echo "size (bytes)";
		echo "</td>";
		echo "<td>    ";
		echo "</td>";
		echo "</tr>";
	}
	$firstMediaFile = false;
	for ($i = 0; $i < $size; ++$i) {
		$j=0;
		$file_type = "";
		if ($dir == '/') {
			$topdir = "/" . $rows[$i]['data'];
		}
		else {
			$topdir = $dir . "/" . $rows[$i]['data'];
		}
		if ($rows[$i]['dir']) {
			$file_type = "dir";
		}
		else {
			$mime = mime_content_type_replacement($topdir);
			if (strpos($mime, 'audio') !== false) {
				$file_type = "file";
				$rows[$i]['file_type'] = 'audio';
			}
			elseif (strpos($mime, 'image') !== false) {
				$file_type = "file";
				$rows[$i]['file_type'] = 'image';
				$rows[$i]['mime'] = $mime;
			}
		}
		
		if ($file_type == 'dir' || $file_type == 'file') {
			echo '<tr class="artist_list">';
			if ($file_type == 'dir') {
				$dirpath = str_ireplace($cfg['media_dir'],'', $topdir);
				$aName = myUrlencode($rows[$i]['data']);
				//echo "<td class='icon'>";
				//echo "<i class='fa fa-folder-o icon-small'></i>";
				//echo "</td>";
				$j = $i + 100000
				?>	
				<td class="icon">
				<a name="<?php echo $aName; ?>"></a>
				<span id="menu-track<?php echo $j ?>">
				<div onclick='toggleMenuSub(<?php echo $j ?>);'>
					<i id="menu-icon<?php echo $j ?>" class="fa fa-folder-o icon-small"></i>
				</div>
				</span>
				</td>
				<?php
				echo "<td class='icon'>";
				
				$dirpath = myUrlencode($dirpath);
				$fulldirpath = myUrlencode($topdir);
				
				echo '<a href=\'javascript:ajaxRequest("play.php?action=addSelect&amp;dirpath=' . $dirpath . '&amp;track_id=' . $i . '&amp;fulldirpath=' . $fulldirpath . '",evaluateAdd);\' onMouseOver="return overlib(\'Add this directory to playlist\');" onMouseOut="return nd();"><i id="add_' . $i . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';
				echo "</td>";
				echo '<td class="fileBrowserItemName">';
				echo "<a href='" . $self . "?" . $showSelectQS . "dir=" . rawurlencode($topdir) . "'>" . $rows[$i]['data'] . "</a>\n";
				echo '';
				echo "</td>";
				echo "<td>";
				//echo filesize($topdir);
				echo "</td>";
				echo "<td>    ";
				//echo "<a href='", substr($topdir, $size_document_root,  strlen($topdir) - $size_document_root), "'>open ", $file_type, "</a>\n";
				echo "</td>";
				echo "</tr>";
				?>
				<tr>
				<td colspan="6">
				<?php dirSubMenu($j, $dir . '/'. $rows[$i]['data']);?>
				</td>
				</tr>
			<?php
			} 
			elseif ($file_type == 'file') {
				if (!$firstMediaFile) {
					$firstMediaFile = true;
					$query = mysqli_query($db,'SELECT album_id FROM track WHERE relative_file LIKE "%' . mysqli_real_escape_string($db,str_replace($cfg['media_dir'],"",$actDir) . DIRECTORY_SEPARATOR . $rows[$i]['data']) . '"');
					$pathInDB = mysqli_num_rows($query);
					if ($pathInDB > 0) {
						$album = mysqli_fetch_assoc($query);
						?>
						<script>
						$("#goToAlbum").on("click", function() {
							window.location = "index.php?action=view3&album_id=<?php echo $album['album_id']; ?>";
						});
						$("#goToAlbum").show();
						</script>
						<?php
					}
				}
				
				
				$filepath = myUrlencode($topdir);
				if ($rows[$i]['file_type'] == 'audio') {
				?>
					<td class="icon">
					<span id="menu-track<?php echo $i ?>">
					<div onclick='toggleMenuSub(<?php echo $i ?>);'>
						<i id="menu-icon<?php echo $i ?>" class="fa fa-bars icon-small"></i>
					</div>
					</span>
					</td>
				<?php
					//echo '<td class="icon pointer" onclick="doPlayAction(\'addSelect\',\'' . $filepath . '\',\'' . $i . '\',\'\',evaluateAdd)" onMouseOver="return overlib(\'Add file\');" onMouseOut="return nd();">';
					echo '<td class="icon">';
					echo '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;filepath=' . $filepath . '&amp;track_id=' . $i . '\',evaluateAdd);" onMouseOver="return overlib(\'Add file\');" onMouseOut="return nd();"><i id="add_' . $i . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';
					//echo '<i id="add_' . $i . '" class="fa fa-plus-circle fa-fw icon-small"></i>';
					echo "</td>";
					//echo '<td class="icon-anchor" onclick="doPlayAction(\'insertSelect\',\'' . $filepath . '\',\'' . $i . '\',\'yes\',evaluateAdd)"  onMouseOver="return overlib(\'Insert and play file\');" onMouseOut="return nd();">';
					echo '<td class="fileBrowserItemName">';
					echo '<a href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;filepath=' . $filepath . '&amp;track_id=' . $i . '\',evaluateAdd);" onMouseOver="return overlib(\'Play file\');" onMouseOut="return nd();">' . $rows[$i]['data'] . '</a>';
					//echo $rows[$i]['data'];
					echo '';
					echo "</td>";
					echo "<td>";
					//echo filesize($topdir);
					echo "</td>";
					echo "<td>    ";
					//echo "<a href='", substr($topdir, $size_document_root,  strlen($topdir) - $size_document_root), "'>open ", $file_type, "</a>\n";
					echo "</td>";
					echo "</tr>";
					?>
					<tr>
					<td colspan="6">
					<?php fileSubMenu($i, $filepath, $mime);?>
					</td>
					</tr>
					<?php
				}
				elseif ($rows[$i]['file_type'] == 'image') {
					echo "<td class='icon'>";
					echo '<i id="add_' . $i . '" class="fa fa-file-image-o fa-fw icon-small"></i>';
					echo "</td>";
					echo "<td></td>";
					echo "<td>";
					echo "<a href='image.php?image_path=" . rawurlencode($topdir) . "&amp;mime=" . $mime . "'>" . $rows[$i]['data'] . "</a>\n";
					echo '';
					echo "</td>";
					echo "<td>";
					//echo filesize($topdir);
					echo "</td>";
					echo "<td>    ";
					//echo "<a href='", substr($topdir, $size_document_root,  strlen($topdir) - $size_document_root), "'>open ", $file_type, "</a>\n";
					echo "</td>";
					echo "</tr>";
				}
			}
		}
	}
	echo "</table>";
} 
else if (is_file($dir)) {
	
	$pos = strrpos($dir, "/");
	$topdir = substr($dir, 0, $pos);
	echo "<a href='", $self, "?dir=", $topdir, "'>", $topdir, "</a>\n\n";
	$file = file($dir);
	$size = count($file);
	for ($i = 0; $i < $size; ++$i)
		echo htmlentities($file[$i], ENT_QUOTES);
} 
else {
	echo "Unable to open";
}


require_once('include/footer.inc.php');
?>
