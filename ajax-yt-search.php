<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2020 Artur Sierzant                            |
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
require_once('include/library.inc.php');
require_once('PHPsimpleHTMLDomParser/simple_html_dom.php');

//error_reporting(E_ALL ^ E_NOTICE);
//@ini_set('display_errors', 'On');

global $cfg, $db;

authenticate('access_media');

$data = array();
$results = array();
$search = $_GET['searchStr'];
$search = str_replace(" ", "+", $search);
$html = new simple_html_dom();
//$html -> load_file("https://www.youtube.com/results?search_query=" . $search);
$urlSearch = "https://www.google.com/search?q=site:youtube.com+" . $search;
$html -> load_file($urlSearch);
$i = 0;
$data['return'] = 0;


for ($j=1;$j<=2;$j++) {
	/* foreach($html->find('ol.item-section') as $ol){
		foreach($ol->find('li') as $li) {
				foreach($li->find('div.yt-lockup') as $d){
					foreach($d->find('span.video-time') as $vt){
						if($vt->innertext){
							foreach($d->find('div.yt-lockup-content h3 a') as $a){
								$results['items'][$i]['id'] = getYouTubeId($a->href);
								$results['items'][$i]['title'] = $a->innertext;
								$results['items'][$i]['url'] = $a->href;
								$results['items'][$i]['time'] = $vt->innertext;
								$i++;
							}
						}
					}
				}
		}
	} */
	foreach($html->find('div[id=main]') as $ol){
		for ($k = 3; $k <= 15; $k++) {
			//echo $ol;
			if ($ol->children($k) !== null && $ol->children($k)->find('div',0) !== null && $ol->children($k)->find('div',0)->find('div',0) !== null) {
				foreach($ol->children($k)->find('div',0)->find('div',0)->find('a') as $a) {
					$watch = $a->find('div',1)->innertext;
					if (strpos($watch,'watch') !== false) {
						$url = urldecode(str_replace('/url?q=','',$a->href));
						$query = parse_url($url, PHP_URL_QUERY);
						parse_str($query, $output);
						$results['items'][$i]['id'] = $output['v'];
						$title = $a->find('h3',0)->plaintext;
						$title = mb_convert_encoding($title,'UTF-8','UTF-8');
						$title = str_replace(' - YouTube','',$title);
						$results['items'][$i]['title'] = $title;
						$results['items'][$i]['url'] = "/watch?v=" . $output['v'];
						
						if ($ol->children($k)->find('div',0)->children(2) !== null) {
							$t = $ol->children($k)->find('div',0)->children(2)->plaintext;
							$t = preg_match('/\d{0,}\d{0,}:{0,}\d{0,}\d:\d\d/',$t,$matches);
							$results['items'][$i]['time'] = $matches[0];
						}
					}
					$i++;
				}
			}
		}
	}
	if ($i==0) {
		//try to load YT page up to 2 times because sometimes it returned 0 results
		$j++;
		$html -> load_file($urlSearch);
	}
	else {
		break;
	}
}

$data['tracks_results'] = $i;

if ($i > 0) {
	$tracksList = '<table class="border" cellspacing="0" cellpadding="0">';
	$tracksList .= '
	<tr class="header">
		<td class="icon"></td><!-- track menu -->
		<td class="icon">';
	if ($cfg["access_add"] && false) {  
		$tracksList .= '<span onMouseOver="return overlib(\'Add all tracks\');" onMouseOut="return nd();"><i id="add_all_YT" class="fa fa-plus-circle fa-fw icon-small pointer"></i></span>';
	}
	$tracksList .= '
		</td><!-- add track -->
		<td>Title&nbsp;</td>
		<td>Source</td>
		<td></td>
		<td align="right" class="time time_w">Time</td>
		<td class="space right"></td>
	</tr>';
	
	$i=50000;
	$YT_ids = ''; 
	foreach ($results['items'] as $track) {
		$track['track_id'] = 'youtube_' . $track['id'];
		$isFavorite = isInFavorite($track['track_id'], $cfg['favorite_id']);
		$isBlacklist = isInFavorite($track['track_id'], $cfg['blacklist_id']);
		$even_odd = ($i++ & 1) ? 'even' : 'odd';
		$tracksList .= '
		
		<tr class="' . $even_odd . ' mouseover">
			<td class="icon">
			<span id="menu-track'. $i .'">
			<div onclick="toggleMenuSub(' . $i . ');">
				<i id="menu-icon' . $i .'" class="fa fa-bars icon-small"></i>
			</div>
			</span>
			</td>
			
			<td class="icon">
			<span>';
		if ($cfg['access_add']) {
			$tracksList .= '<a href="javascript:ajaxRequest(\'play.php?action=addSelect&amp;track_id=' . $track['track_id'] . '\',evaluateAdd);" onMouseOver="return overlib(\'Add track ' . addslashes($track['title']) . '\');" onMouseOut="return nd();"><i id="add_youtube_' . $track['id'] . '" class="fa fa-plus-circle fa-fw icon-small"></i></a>';
		}
		
		$o = "";
		if (!$isFavorite) {
			$o = "-o";
		}
		$starClass = "";
		if ($isBlacklist) {
			$starClass = " blackstar blackstar-selected";
		}
		
		$tracksList .= '
			</span>
			</td>
			<td><a id="a_play_track' . $i . '" href="javascript:ajaxRequest(\'play.php?action=insertSelect&amp;playAfterInsert=yes&amp;track_id=' . $track['track_id'] . '&amp;position_id=' . $i . '\',evaluateAdd);" onMouseOver="return overlib(\'Play track ' . $track['id'] . '\');" onMouseOut="return nd();">' . $track['title'] . '</a>
			</td>
			
			<td class="icon"><a href="https://youtube.com' . $track['url'] . '" target="_blank"><i class="fa fa-youtube-play fa-fw icon-small"></i></a>
			</td>
			
			<td onclick="toggleStarSub(' . $i . ',\'' . $track['track_id'] . '\');" class="pl-favorites">
				<span id="blacklist-star-bg' . $track['track_id'] . '" class="' . $starClass . '">
				<i class="fa fa-star' . $o . ' fa-fw" id="favorite_star-' . $track['track_id'] . '"></i>
				</span>
				</td>
			
			<td align="right">' . $track['time'] . '</td>
			<td></td>
			</tr>';
		$tracksList .= '
			<tr>
				<td colspan="20">
				' . starSubMenu($i, $isFavorite, $isBlacklist, $track['track_id'], 'string') . '
				</td>
			</tr>';
				
		$tracksList .= '
			<tr>
			<td colspan="20">
			' . trackSubMenu($i, $track, '', 'string') . '
			</td>
			</tr>';
		}
	$tracksList .= '</table>';
	$data['tracks'] = $tracksList;
}


echo safe_json_encode($data);

?>