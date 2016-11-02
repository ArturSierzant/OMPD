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
authenticate('access_playlist');
require_once('include/header.inc.php');
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

	return ( $data );
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

// mime_content_type replacement by svogal:
// http://php.net/manual/en/function.mime-content-type.php#87856
//if(!function_exists('mime_content_type')) {

    function mime_content_type_new($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'mp2' => 'audio/mpeg',
            'mpc' => 'audio/mpeg',
			'ogg' => 'audio/ogg', 
			'oga' => 'audio/ogg', 
			'ape' => 'audio/ape', 
			'dsf' => 'audio/dsf', 
			'flac' => 'audio/flac', 
			'wv' => 'audio/wv', 
			'wav' => 'audio/wav', 
			'wma' => 'audio/x-ms-wma',
			'aac' => 'audio/aac',
			'm4a' => 'audio/m4a',
			'm4b' => 'audio/m4b',
			'm4b' => 'audio/m4b',
			
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
//}



	$self = $_SERVER['PHP_SELF'];
	if (isset($_GET['dir'])) {
		$dir = $_GET['dir'];
		$size = strlen($dir);
		while ($dir[$size - 1] == '/') {
			$dir = substr($dir, 0, $size - 1);
			$size = strlen($dir);
		}
	} else {
		/* $dir = $_SERVER["SCRIPT_FILENAME"];
		$size = strlen($dir);
		while ($dir[$size - 1] != '/') {
			$dir = substr($dir, 0, $size - 1);
			$size = strlen($dir);
		}
		$dir = substr($dir, 0, $size - 1); */
		$dir = substr($cfg['media_dir'], 0, -1);
	}

	echo '<span class="nav_tree">DIR : ' . $dir . '</span>';
	echo "\n\n";
	$dir = iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir);
	if (is_dir($dir)) {
		if ($handle = opendir($dir)) {
			$dir = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $dir);
			$size_document_root = strlen($_SERVER['DOCUMENT_ROOT']);
			$pos = strrpos($dir, "/");
			$topdir = substr($dir, 0, $pos + 1);
			$i = 0;
  	  		while (false !== ($file = readdir($handle))) {
        		if ($file != "." && $file != "..") {
					$rows[$i]['data'] = iconv(NJB_DEFAULT_FILESYSTEM_CHARSET, 'UTF-8', $file);
					$rows[$i]['dir'] = is_dir(iconv('UTF-8', NJB_DEFAULT_FILESYSTEM_CHARSET, $dir) . "/" . $file);
					$i++;
				}
			}
    		closedir($handle);
		}

		$size = count($rows);
		$rows = sortRows($rows);
		echo "<table class='border' cellspacing='0' cellpadding='0'>";
		echo "<tr class='artist_list'>";
		echo "<td class='icon'><i class='fa fa-level-up icon-small'></i></td>";
		echo "<td class='icon'>";
		echo "</td>";
		echo "<td>    ";
		echo "<a href='", $self, "?dir=", rawurlencode($topdir), "'>Up to parent dir</a>\n";
		echo "</td>";
		echo "<td>";
		//echo "size (bytes)";
		echo "</td>";
		echo "<td>    ";
		echo "</td>";
		echo "</tr>";
		
		for ($i = 0; $i < $size; ++$i) {
			$file_type = "";
			$topdir = $dir . "/" . $rows[$i]['data'];
			if ($rows[$i]['dir']) {
				$file_type = "dir";
			}
			else {
				$mime = mime_content_type_new($topdir);
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
					echo "<td class='icon'>";
					echo "<i class='fa fa-folder-o icon-small'></i>";
					echo "</td>";
					echo "<td class='icon'>";
					echo '<a href=\'javascript:ajaxRequest("play.php?action=addSelect&amp;dirpath=' . str_replace('%26','ompd_ampersand_ompd',urlencode($dirpath)) . '&amp;track_id=' . $i . '",evaluateAdd);\' onMouseOver="return overlib(\'Add directory ' . $dirpath . '\');" onMouseOut="return nd();"><i id="add_' . $i . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';
					echo "</td>";
					echo "<td>";
					echo "<a href='" . $self . "?dir=" . rawurlencode($topdir) . "'>" . $rows[$i]['data'] . "</a>\n";
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
				elseif ($file_type == 'file') {
					$filepath = urlencode(str_ireplace($cfg['media_dir'],'', $topdir));
					$filepath = str_replace('%26','ompd_ampersand_ompd',$filepath);
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
						echo "<td class='icon'>";
						echo '<a href=\'javascript:ajaxRequest("play.php?action=addSelect&amp;filepath=' . $filepath . '&amp;track_id=' . $i . '",evaluateAdd);\' onMouseOver="return overlib(\'Add track ' . str_ireplace($cfg['media_dir'],'', $topdir) . '\');" onMouseOut="return nd();"><i id="add_' . $i . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';
						echo "</td>";
						echo "<td>";
						echo '<a href=\'javascript:ajaxRequest("play.php?action=insertSelect&amp;filepath=' . $filepath . '&amp;track_id=' . $i . '&amp;playAfterInsert=yes",evaluateAdd);\' onMouseOver="return overlib(\'Insert and play track ' . str_ireplace($cfg['media_dir'],'', $topdir) . '\');" onMouseOut="return nd();">' . $rows[$i]['data'] . '</a>';
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
	} else if (is_file($dir)) {
		
		$pos = strrpos($dir, "/");
		$topdir = substr($dir, 0, $pos);
		echo "<a href='", $self, "?dir=", $topdir, "'>", $topdir, "</a>\n\n";
		$file = file($dir);
		$size = count($file);
		for ($i = 0; $i < $size; ++$i)
			echo htmlentities($file[$i], ENT_QUOTES);
	} else {
		echo "bad file or unable to open";
	}


require_once('include/footer.inc.php');
?>
