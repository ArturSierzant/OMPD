<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2021 Artur Sierzant                            |
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

global $cfg, $db;



	$query = mysqli_query($db,'SELECT * FROM update_progress');
	$row = mysqli_fetch_assoc($query);
	
	$update_progress = array();
	$update_progress['structure_image']=$row["structure_image"];
	$update_progress['file_info']=$row["file_info"];
	$update_progress['cleanup']=$row["cleanup"];
	$update_progress['update_time']=$row["update_time"];
	
	echo json_encode($update_progress); 
	
?>
	
