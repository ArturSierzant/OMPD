<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015 Artur Sierzant                                 |
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
//  | about.php                                                              |
//  +------------------------------------------------------------------------+
require_once('include/initialize.inc.php');
require_once('getid3/getid3/getid3.php'); // for version info
//require_once('include/header.inc.php');

$cfg['menu'] = 'about';
$action = get('action');

if		($action == '')				about();
elseif	($action == 'license')		license();
else	message(__FILE__, __LINE__, 'error', '[b]Unsupported input value for[/b][br]action');
exit();




//  +------------------------------------------------------------------------+
//  | Version check                                                          |
//  +------------------------------------------------------------------------+
function versionCheck($ttl) {
	global $cfg, $db;
	
	if ($cfg['latest_version_idle_time'] < time() - $ttl) {
		if ($cfg['latest_version'] = @file_get_contents('https://ompd.pl/version.txt')) {
			$cfg['latest_version_idle_time'] = time();
			mysqli_query($db,'UPDATE server SET value = "' . mysqli_real_escape_string($db,$cfg['latest_version']) . '" WHERE name = "latest_version"');
			mysqli_query($db,'UPDATE server SET value = "' . (int) $cfg['latest_version_idle_time']  . '" WHERE name = "latest_version_idle_time"');
		}
		else
			$cfg['latest_version'] = 'Unresolved';		
	}
	if (version_compare(NJB_VERSION, $cfg['latest_version'], '<') || $cfg['latest_version'] == 'Unresolved')	return false;
	else																										return true;
}




//  +------------------------------------------------------------------------+
//  | About                                                                  |
//  +------------------------------------------------------------------------+
function about() {
	global $cfg, $db;
	authenticate('access_always');
	//authenticate('access_playlist');
	
	// formattedNavigator
	/* $nav			= array();
	$nav['name'][]	= 'About O!MPD';
	$cfg['menu'] = 'about'; */
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header header_bigger">
	<td class="space"></td>
	<td colspan="3" style="white-space: normal;">
	O!MPD&nbsp;<?php echo html(NJB_VERSION); ?>,&nbsp;Copyright&nbsp;&copy;&nbsp;2015&nbsp;Artur&nbsp;Sier&#380;ant<br>
	
	</td>
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="odd space"><td colspan="5"></td></tr>
<tr class="odd">
	<td></td>
	<td colspan="3">
	<i class="fa fa-globe fa-fw"></i>&nbsp;&nbsp;<a href="https://ompd.pl" target="_blank">https://ompd.pl</a><br>
	<i class="fa fa-github fa-fw"></i>&nbsp;&nbsp;<a href="https://github.com/ArturSierzant/OMPD" target="_blank">https://github.com/ArturSierzant/OMPD</a><br>
	<i class="fa fa-envelope-o fa-fw"></i>&nbsp;&nbsp;<a href="mailto:info@ompd.pl">info@ompd.pl</a><br><br>
	This program comes with <a href="about.php?action=license#nowarranty">ABSOLUTELY NO WARRANTY</a>.<br>
	This is free software and you are welcome to redistribute it.<br>
	under certain <a href="about.php?action=license#conditions">conditions</a>.
	<br><br>
	O!MPD is fork of netjukebox&nbsp;Copyright&nbsp;&copy;&nbsp;2001-2012&nbsp;Willem&nbsp;Bartels <a href="http://www.netjukebox.nl/" target = "_blank">http://www.netjukebox.nl</a>
	</td>
	<td></td>
</tr>
<tr class="odd space"><td colspan="5"></td></tr>
<?php
	if (file_exists(NJB_HOME_DIR . 'skin/' . $cfg['skin'] . '/about.txt') && $skin_message = @file_get_contents(NJB_HOME_DIR . 'skin/' . $cfg['skin'] . '/about.txt')) { ?>
<tr class="line"><td colspan="5"></td></tr>
<tr class="header header_bigger">
	<td></td>
	<td colspan="3">Current skin:</td>
	<td></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="odd space"><td colspan="5"></td></tr>
<tr class="odd">
	<td></td>
	<td colspan="3" valign="top"><?php echo bbcode($skin_message); ?></td>
	<td></td>
</tr>
<tr class="odd space"><td colspan="5"></td></tr>
<?php
	}
	if ($cfg['access_admin']) {
		$ttl = (get('forceVersionCheck') == '1') ? 0 : 3600; ?>
<tr class="line"><td colspan="5"></td></tr>
<tr class="header header_bigger">
	<td></td>
	<td colspan="3">Version check:</td>
	<td></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="<?php echo (versionCheck($ttl)) ? 'odd_ok' : 'odd_error'; ?>">
	<td></td>
	<td>Current version:</td>
	<td></td>
	<td style="color: <?php echo (versionCheck($ttl)) ? 'Green' : 'Red'; ?>"><?php echo '&nbsp;' . html(NJB_VERSION); ?></td>
	<td></td>
</tr>
<tr class="even">
	<td></td>
	<td>Latest version:</td>
	<td></td>
	<td><a href="about.php?forceVersionCheck=1" onMouseOver="return overlib('Re-Check version');" onMouseOut="return nd();"><?php echo html($cfg['latest_version']); ?></a></td>
	<td></td>
</tr>
<?php
	}
	$i = 0; ?>

<tr class="line"><td colspan="5"></td></tr>
<tr class="header header_bigger">
	<td></td>
	<td colspan="3">Changelog:</td>
	<td></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td>&nbsp;</td>
	<td style="vertical-align: top;">v1.08 <br>2025.04.30</td>
	<td></td>
	<td>
	<ul style="padding-left: 1em;">
    <li> added Radio Browser (based on <a href="https://radio-browser.info" target="_blank">radio-browser.info</a>)</li>
    <li> added Auto Queue (based on Tidal or local files)</li>
    <li> added support for more Tidal items (playlists, mixlists, podcasts)</li>
    <li> added more items in artist view (when using Tidal)</li>
    <li> most of settings from config.inc.php moved to Config -> Settings</li>
    <li> added [experimental] update DB from command line (<code>php update.php "/path/to/updated/directory"</code>) - thanks to <a href="https://github.com/tomchiverton" target="_blank">tomchiverton</a></li>
    <li> added new skin ompd_darker_2 (modified ompd_darker)</li>
	<li> fixed bugs</li>
	</ul>
	<br>
	</td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td>&nbsp;</td>
	<td style="vertical-align: top;">v1.07 <br>2021.12.03</td>
	<td></td>
	<td>
	<ul style="padding-left: 1em;">
	<li> added support for HighResAudio (thanks to <a target="_blank" href="https://github.com/marcbth">marcbth</a>)</li>
	<li> added badge with audio format info on album cover (thanks to <a target="_blank" href="https://github.com/paradix">paradix</a>)</li>
	<li> added support for Tidal OAuth2 login</li>
	<li> added 'Add to Library' for albums from streaming services (Tidal, HRA)</li>
	<li> added popularity bar on album cover</li>
	<li> added displaying lyrics on album cover in 'Now Playing' (based on <a href="https://github.com/Smile4ever/LyricsCore" target="_blank">LyricsCore API</a>; also Musixmatch supported)</li>
	<li> added new popularity category (by album year)</li>
  <li> compatibillity with PHP8</li>
	<li> fixed bugs</li>
	</ul>
	<br>
	</td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td>&nbsp;</td>
	<td style="vertical-align: top;">v1.06 <br>2020.07.22</td>
	<td></td>
	<td>
	<ul style="padding-left: 1em;">
	<li> added support for TIDAL direct play (play TIDAL with any version of MPD)</li>
	<li> added support for TIDAL user playlists</li>
	<li> added YouTube results in artist view and search (settable in config.inc.php)</li>
	<li> added support for internet radio covers (settable in config.inc.php)</li>
	<li> added default login option</li>
	<li> added possibility to login using URL (login.php)</li>
	<li> added support for PWA (Progressive Web App)</li>
	<li> added new skin (darker)</li>
	<li> improved TIDAL support (python API replaced with PHP API - works much faster)</li>
	<li> improved Favorites - mixed lists (streams and local files) are allowed</li>
	<li> improved Favorites - tracks from Tidal and YouTube now can be added to Favorites</li>
	<li> fixed bugs</li>
	</ul>
	<br>
	</td>
	<td></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td>&nbsp;</td>
	<td style="vertical-align: top;">v1.05 <br>2019.07.31</td>
	<td></td>
	<td>
	<ul style="padding-left: 1em;">
	<li> added support for TIDAL</li>
	<li> added support for COMPOSER tag (settable in config.inc.php)</li>
	<li> added 'Show favorite tracks' for selected genre</li>
	<li> added popularity of artists (Library -> Popular -> Artist)</li>
	<li> added info about Track Dynamic Range in track lists (settable in config.inc.php)</li>
	<li> changed genre view</li>
	<li> fixed bugs</li>
	</ul>
	<br>
	</td>
	<td></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="<?php echo ($i++ & 1) ? 
'even' : 'odd'; ?>">
	<td>&nbsp;</td>
	<td style="vertical-align: top;">v1.04 <br>2018.07.24</td>
	<td></td>
	<td>
	<ul style="padding-left: 1em;">
	<li> added ability to play audio streams from YouTube movies (requires <a href="https://github.com/rg3/youtube-dl" target="_blank">youtube-dl</a>)</li>
	<li> added new skins (skins are now based on <a href="http://lesscss.org/">{less}</a>)</li>
	<li> added miniplayer (settable in config.inc.php)</li>
	<li> added support for mpd playlists</li>
	<li> added discography browser in album view (settable in config.inc.php)</li>
	<li> added view for Album Dynamic Range (DR)</li>
	<li> added direct navigation to selected page in paginator</li>
	<li> added search action for album cover in album view (settable in config.inc.php)</li>
	<li> added 'Play next' and 'Remove all below' to track menu in 'Now Playing'</li>
	<li> added 'Go to album' in file browser (if album is in DB)</li>
	<li> added config file editor</li>
	<li> added consume mode on/off (long press on 'Delete played')</li>
	<li> added stop function in 'Now Playing' (long press on play/pause button)</li>
	<li> improved sorting by genre in search results</li>
	<li> fixed bugs</li>
	</ul>
	<br>
	</td>
	<td></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td>&nbsp;</td>
	<td style="vertical-align: top;">v1.03 <br>2017.05.02</td>
	<td></td>
	<td>
	<ul style="padding-left: 1em;">
	<li> added ability to play any track/folder www server has access to (i.e. files outside of MPD and O!MPD database) on any MPD in network</li>
	<li> added multi-genre support</li>
	<li> added STYLE tag support</li>
	<li> added file browser</li>
	<li> added ability to update selected directory only (no need to do full update when adding single albums)</li>
	<li> added ability to update album directly from album view</li>
	<li> added artist list in library</li>
	<li> added sort by 'Add time' in search results</li>
	<li> added display of multi-disc in album view</li>
	<li> added grouping of multi-disc albums in album search results</li>
	<li> added display of other versions of album</li>
	<li> added list of favorite tracks in artist search results</li>
	<li> added possibility to use more than one file name for album cover</li>
	<li> added random play from directory</li>
	<li> fixed bugs</li>
	</ul>
	<br>
	</td>
	<td></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td>&nbsp;</td>
	<td style="vertical-align: top;">v1.02 <br>2016.09.19</td>
	<td></td>
	<td>
	<ul style="padding-left: 1em;">
	<li> added possibility to synch, copy and add current playlist from one MPD to another (multiroom)</li>
	<li> added better support for Favorites: tracks can be saved/added to Favorites from any place (Now Playing, album view, search results)</li>
	<li> added possibility to add stream to current playlist and Favorites</li>
	<li> added Blacklist</li>
	<li> added time info ('End at' and 'Left') for current playlist</li>
	<li> added possibility to switch off tags from COMMENT</li>
	<li> added support for MDP password</li>
	<li> added proxy support (code by Stéphane Ardhuin)</li>
	<li> added 'Play to...' for albums (experimental)</li>
	<li> improvements in update procedure</li>
	<li> compatibillity with PHP7 and mySQL 5.7</li>
	<li> some visual improvements</li>
	<li> fixed bugs</li>
	</ul>
	<br>
	</td>
	<td></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td>&nbsp;</td>
	<td style="vertical-align: top;">v1.01 <br>2016.02.07</td>
	<td></td>
	<td>
	<ul style="padding-left: 1em;">
	<li> added possibility of arrange tracks in Now Playing and Favorites</li>
	<li> added 'Recently played albums' on start page</li>
	<li> added start page display options (section 'Start page display options' in configuration file)</li> 
	<li> added 'Crop' to Now Playing commands</li>
	<li> added 'Insert into playlist' and 'Insert and play' to album commands in album view</li>
	<li> added 'Quick play' and 'Quick add' to album mini-covers (settable in configuration file)</li>
	<li> added some 'Media statistics' (section 'Playtime')</li>
	<li> added total time of Now Playing playlist</li>
	<li> clicking the big play icon in album view now plays album and redirects to Now Playing
	<li> improved update procedure</li>
	<li> better support for streams and tracks not uploaded to MySQL data base</li>
	<li> O!MPD now uses PHP MySQLi extension instead of MySQL</li>
	<li> some performance improvements</li>
	<li> fixed bugs</li>
	</ul>
	<br>
	</td>
	<td></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>v1.0 <br>2015.07.10</td>
	<td>&nbsp;</td>
	<td>First release</td>
	<td></td>
</tr>	
	
<tr class="line"><td colspan="5"></td></tr>
<tr class="header header_bigger">
	<td></td>
	<td colspan="3">Included scripts, fonts and images:</td>
	<td></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>Google fonts</td>
	<td></td>
	<td><a href="http://www.google.com/fonts/" target="_blank">http://www.google.com/fonts/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>Font Awesome</td>
	<td></td>
	<td><a href="http://fontawesome.io/" target="_blank">http://fontawesome.io/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>Typicons</td>
	<td></td>
	<td><a href="http://typicons.com/" target="_blank">http://typicons.com/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>525icons</td>
	<td></td>
	<td><a href="https://525icons.com/index.html" target="_blank">https://525icons.com/index.html</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>jQuery</td>
	<td></td>
	<td><a href="http://jquery.com/" target="_blank">http://jquery.com/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>spin.js</td>
	<td></td>
	<td><a href="http://fgnass.github.io/spin.js/" target="_blank">http://fgnass.github.io/spin.js/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>getID3() <?php $getID3 = new getID3; echo $getID3->version(); ?></td>
	<td></td>
	<td><a href="http://www.getid3.org" target="_blank">http://www.getid3.org</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>overLIB <script type="text/javascript">
	<!--
		document.write(olInfo.version);
	//-->
	</script></td>
	<td></td>
	<td><a href="http://www.bosrup.com/web/overlib/" target="_blank">http://www.bosrup.com/web/overlib/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>SHA-1</td>
	<td></td>
	<td><a href="http://www.pajhome.org.uk/crypt/md5/" target="_blank">http://www.pajhome.org.uk/crypt/md5/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>PHP Paginator Class</td>
	<td></td>
	<td><a href="https://gist.github.com/daslicht/c319e18a1c8761f360ad" target="_blank">https://gist.github.com/daslicht</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>TouchSwipe</td>
	<td></td>
	<td><a href="http://labs.rampinteractive.co.uk/touchSwipe/" target="_blank">http://labs.rampinteractive.co.uk/touchSwipe/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>Browser</td>
	<td></td>
	<td><a href="http://tomicki.net/" target="_blank">http://tomicki.net/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>CodeMirror</td>
	<td></td>
	<td><a href="http://codemirror.net/" target="_blank">http://codemirror.net/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>Longpress</td>
	<td></td>
	<td><a href="http://github.com/vaidik/jquery-longpress/" target="_blank">http://github.com/vaidik/jquery-longpress/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>{less}</td>
	<td></td>
	<td><a href="http://lesscss.org/" target="_blank">http://lesscss.org/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>PHP Simple HTML DOM Parser</td>
	<td></td>
	<td><a href="https://simplehtmldom.sourceforge.io/" target="_blank">https://simplehtmldom.sourceforge.io/</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>Ping for PHP class</td>
	<td></td>
	<td><a href="https://github.com/geerlingguy/Ping" target="_blank">https://github.com/geerlingguy/Ping</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>LyricsCore API</td>
	<td></td>
	<td><a href="https://github.com/Smile4ever/LyricsCore" target="_blank">https://github.com/Smile4ever/LyricsCore</a></td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>Radio Browser</td>
	<td></td>
	<td><a href="https://github.com/adinan-cenci/radio-browser" target="_blank">https://github.com/adinan-cenci/radio-browser</a></br>
  Radio Browser is a library to fetch data from the <a href="https://radio-browser.info" target="_blank">radio-browser.info</a> catalog of Internet radio stations.
</td>
	<td></td>
</tr>
<tr class="<?php echo ($i++ & 1) ? 'even' : 'odd'; ?>">
	<td></td>
	<td>CD Icon</td>
	<td></td>
	<td><a href="https://www.svgrepo.com/svg/387093/cd" target="_blank">https://www.svgrepo.com/svg/387093/cd</a>
</td>
	<td></td>
</tr>




<?php
	if ($cfg['admin_about_message'] != '') { ?>
<tr class="line"><td colspan="5"></td></tr>
<tr class="header header_bigger">
	<td></td>
	<td colspan="3">Admin message:</td>
	<td></td>
</tr>
<tr class="line"><td colspan="5"></td></tr>
<tr class="odd space"><td colspan="5"></td></tr>
<tr class="odd">
	<td></td>
	<td colspan="3" valign="top"><?php echo bbcode($cfg['admin_about_message']); ?></td>
	<td></td>
</tr>
<tr class="odd space"><td colspan="5"></td></tr>
<?php
	} ?>
</table>
<?php
	require_once('include/footer.inc.php');
}




//  +------------------------------------------------------------------------+
//  | License                                                                |
//  +------------------------------------------------------------------------+
function license() {
	global $cfg;
	authenticate('access_always');
	
	// formattedNavigator
	/* $nav			= array();
	$nav['name'][]	= 'About O!MPD';
	$nav['url'][]	= 'about.php';
	$nav['name'][]	= 'Licence'; */
	$cfg['menu'] = 'about';
	require_once('include/header.inc.php');
?>
<table cellspacing="0" cellpadding="0" class="border">
<tr class="header">
	<td class="space"></td>
	<td>GNU GENERAL PUBLIC LICENSE</td>
	<td class="space"></td>
</tr>
<tr class="line"><td colspan="3"></td></tr>
<tr class="even">
	<td></td>
	<td>
<pre>

                    GNU GENERAL PUBLIC LICENSE
                       Version 3, 29 June 2007

 Copyright &copy; 2007 <a href="http://fsf.org/">Free Software Foundation, Inc.</a>
 Everyone is permitted to copy and distribute verbatim copies
 of this license document, but changing it is not allowed.

                            Preamble

  The GNU General Public License is a free, copyleft license for
software and other kinds of works.

  The licenses for most software and other practical works are designed
to take away your freedom to share and change the works.  By contrast,
the GNU General Public License is intended to guarantee your freedom to
share and change all versions of a program--to make sure it remains free
software for all its users.  We, the Free Software Foundation, use the
GNU General Public License for most of our software; it applies also to
any other work released this way by its authors.  You can apply it to
your programs, too.

  When we speak of free software, we are referring to freedom, not
price.  Our General Public Licenses are designed to make sure that you
have the freedom to distribute copies of free software (and charge for
them if you wish), that you receive source code or can get it if you
want it, that you can change the software or use pieces of it in new
free programs, and that you know you can do these things.

  To protect your rights, we need to prevent others from denying you
these rights or asking you to surrender the rights.  Therefore, you have
certain responsibilities if you distribute copies of the software, or if
you modify it: responsibilities to respect the freedom of others.

  For example, if you distribute copies of such a program, whether
gratis or for a fee, you must pass on to the recipients the same
freedoms that you received.  You must make sure that they, too, receive
or can get the source code.  And you must show them these terms so they
know their rights.

  Developers that use the GNU GPL protect your rights with two steps:
(1) assert copyright on the software, and (2) offer you this License
giving you legal permission to copy, distribute and/or modify it.

  For the developers' and authors' protection, the GPL clearly explains
that there is no warranty for this free software.  For both users' and
authors' sake, the GPL requires that modified versions be marked as
changed, so that their problems will not be attributed erroneously to
authors of previous versions.

  Some devices are designed to deny users access to install or run
modified versions of the software inside them, although the manufacturer
can do so.  This is fundamentally incompatible with the aim of
protecting users' freedom to change the software.  The systematic
pattern of such abuse occurs in the area of products for individuals to
use, which is precisely where it is most unacceptable.  Therefore, we
have designed this version of the GPL to prohibit the practice for those
products.  If such problems arise substantially in other domains, we
stand ready to extend this provision to those domains in future versions
of the GPL, as needed to protect the freedom of users.

  Finally, every program is threatened constantly by software patents.
States should not allow patents to restrict development and use of
software on general-purpose computers, but in those that do, we wish to
avoid the special danger that patents applied to a free program could
make it effectively proprietary.  To prevent this, the GPL assures that
patents cannot be used to render the program non-free.

  The precise terms and conditions for copying, distribution and
modification follow.

                       <a name="conditions" id="conditions"></a>TERMS AND CONDITIONS

  0. Definitions.

  &quot;This License&quot; refers to version 3 of the GNU General Public License.

  &quot;Copyright&quot; also means copyright-like laws that apply to other kinds of
works, such as semiconductor masks.

  &quot;The Program&quot; refers to any copyrightable work licensed under this
License.  Each licensee is addressed as &quot;you&quot;.  &quot;Licensees&quot; and
&quot;recipients&quot; may be individuals or organizations.

  To &quot;modify&quot; a work means to copy from or adapt all or part of the work
in a fashion requiring copyright permission, other than the making of an
exact copy.  The resulting work is called a &quot;modified version&quot; of the
earlier work or a work &quot;based on&quot; the earlier work.

  A &quot;covered work&quot; means either the unmodified Program or a work based
on the Program.

  To &quot;propagate&quot; a work means to do anything with it that, without
permission, would make you directly or secondarily liable for
infringement under applicable copyright law, except executing it on a
computer or modifying a private copy.  Propagation includes copying,
distribution (with or without modification), making available to the
public, and in some countries other activities as well.

  To &quot;convey&quot; a work means any kind of propagation that enables other
parties to make or receive copies.  Mere interaction with a user through
a computer network, with no transfer of a copy, is not conveying.

  An interactive user interface displays &quot;Appropriate Legal Notices&quot;
to the extent that it includes a convenient and prominently visible
feature that (1) displays an appropriate copyright notice, and (2)
tells the user that there is no warranty for the work (except to the
extent that warranties are provided), that licensees may convey the
work under this License, and how to view a copy of this License.  If
the interface presents a list of user commands or options, such as a
menu, a prominent item in the list meets this criterion.

  1. Source Code.

  The &quot;source code&quot; for a work means the preferred form of the work
for making modifications to it.  &quot;Object code&quot; means any non-source
form of a work.

  A &quot;Standard Interface&quot; means an interface that either is an official
standard defined by a recognized standards body, or, in the case of
interfaces specified for a particular programming language, one that
is widely used among developers working in that language.

  The &quot;System Libraries&quot; of an executable work include anything, other
than the work as a whole, that (a) is included in the normal form of
packaging a Major Component, but which is not part of that Major
Component, and (b) serves only to enable use of the work with that
Major Component, or to implement a Standard Interface for which an
implementation is available to the public in source code form.  A
&quot;Major Component&quot;, in this context, means a major essential component
(kernel, window system, and so on) of the specific operating system
(if any) on which the executable work runs, or a compiler used to
produce the work, or an object code interpreter used to run it.

  The &quot;Corresponding Source&quot; for a work in object code form means all
the source code needed to generate, install, and (for an executable
work) run the object code and to modify the work, including scripts to
control those activities.  However, it does not include the work's
System Libraries, or general-purpose tools or generally available free
programs which are used unmodified in performing those activities but
which are not part of the work.  For example, Corresponding Source
includes interface definition files associated with source files for
the work, and the source code for shared libraries and dynamically
linked subprograms that the work is specifically designed to require,
such as by intimate data communication or control flow between those
subprograms and other parts of the work.

  The Corresponding Source need not include anything that users
can regenerate automatically from other parts of the Corresponding
Source.

  The Corresponding Source for a work in source code form is that
same work.

  2. Basic Permissions.

  All rights granted under this License are granted for the term of
copyright on the Program, and are irrevocable provided the stated
conditions are met.  This License explicitly affirms your unlimited
permission to run the unmodified Program.  The output from running a
covered work is covered by this License only if the output, given its
content, constitutes a covered work.  This License acknowledges your
rights of fair use or other equivalent, as provided by copyright law.

  You may make, run and propagate covered works that you do not
convey, without conditions so long as your license otherwise remains
in force.  You may convey covered works to others for the sole purpose
of having them make modifications exclusively for you, or provide you
with facilities for running those works, provided that you comply with
the terms of this License in conveying all material for which you do
not control copyright.  Those thus making or running the covered works
for you must do so exclusively on your behalf, under your direction
and control, on terms that prohibit them from making any copies of
your copyrighted material outside their relationship with you.

  Conveying under any other circumstances is permitted solely under
the conditions stated below.  Sublicensing is not allowed; section 10
makes it unnecessary.

  3. Protecting Users' Legal Rights From Anti-Circumvention Law.

  No covered work shall be deemed part of an effective technological
measure under any applicable law fulfilling obligations under article
11 of the WIPO copyright treaty adopted on 20 December 1996, or
similar laws prohibiting or restricting circumvention of such
measures.

  When you convey a covered work, you waive any legal power to forbid
circumvention of technological measures to the extent such circumvention
is effected by exercising rights under this License with respect to
the covered work, and you disclaim any intention to limit operation or
modification of the work as a means of enforcing, against the work's
users, your or third parties' legal rights to forbid circumvention of
technological measures.

  4. Conveying Verbatim Copies.

  You may convey verbatim copies of the Program's source code as you
receive it, in any medium, provided that you conspicuously and
appropriately publish on each copy an appropriate copyright notice;
keep intact all notices stating that this License and any
non-permissive terms added in accord with section 7 apply to the code;
keep intact all notices of the absence of any warranty; and give all
recipients a copy of this License along with the Program.

  You may charge any price or no price for each copy that you convey,
and you may offer support or warranty protection for a fee.

  5. Conveying Modified Source Versions.

  You may convey a work based on the Program, or the modifications to
produce it from the Program, in the form of source code under the
terms of section 4, provided that you also meet all of these conditions:

    a) The work must carry prominent notices stating that you modified
    it, and giving a relevant date.

    b) The work must carry prominent notices stating that it is
    released under this License and any conditions added under section
    7.  This requirement modifies the requirement in section 4 to
    &quot;keep intact all notices&quot;.

    c) You must license the entire work, as a whole, under this
    License to anyone who comes into possession of a copy.  This
    License will therefore apply, along with any applicable section 7
    additional terms, to the whole of the work, and all its parts,
    regardless of how they are packaged.  This License gives no
    permission to license the work in any other way, but it does not
    invalidate such permission if you have separately received it.

    d) If the work has interactive user interfaces, each must display
    Appropriate Legal Notices; however, if the Program has interactive
    interfaces that do not display Appropriate Legal Notices, your
    work need not make them do so.

  A compilation of a covered work with other separate and independent
works, which are not by their nature extensions of the covered work,
and which are not combined with it such as to form a larger program,
in or on a volume of a storage or distribution medium, is called an
&quot;aggregate&quot; if the compilation and its resulting copyright are not
used to limit the access or legal rights of the compilation's users
beyond what the individual works permit.  Inclusion of a covered work
in an aggregate does not cause this License to apply to the other
parts of the aggregate.

  6. Conveying Non-Source Forms.

  You may convey a covered work in object code form under the terms
of sections 4 and 5, provided that you also convey the
machine-readable Corresponding Source under the terms of this License,
in one of these ways:

    a) Convey the object code in, or embodied in, a physical product
    (including a physical distribution medium), accompanied by the
    Corresponding Source fixed on a durable physical medium
    customarily used for software interchange.

    b) Convey the object code in, or embodied in, a physical product
    (including a physical distribution medium), accompanied by a
    written offer, valid for at least three years and valid for as
    long as you offer spare parts or customer support for that product
    model, to give anyone who possesses the object code either (1) a
    copy of the Corresponding Source for all the software in the
    product that is covered by this License, on a durable physical
    medium customarily used for software interchange, for a price no
    more than your reasonable cost of physically performing this
    conveying of source, or (2) access to copy the
    Corresponding Source from a network server at no charge.

    c) Convey individual copies of the object code with a copy of the
    written offer to provide the Corresponding Source.  This
    alternative is allowed only occasionally and noncommercially, and
    only if you received the object code with such an offer, in accord
    with subsection 6b.

    d) Convey the object code by offering access from a designated
    place (gratis or for a charge), and offer equivalent access to the
    Corresponding Source in the same way through the same place at no
    further charge.  You need not require recipients to copy the
    Corresponding Source along with the object code.  If the place to
    copy the object code is a network server, the Corresponding Source
    may be on a different server (operated by you or a third party)
    that supports equivalent copying facilities, provided you maintain
    clear directions next to the object code saying where to find the
    Corresponding Source.  Regardless of what server hosts the
    Corresponding Source, you remain obligated to ensure that it is
    available for as long as needed to satisfy these requirements.

    e) Convey the object code using peer-to-peer transmission, provided
    you inform other peers where the object code and Corresponding
    Source of the work are being offered to the general public at no
    charge under subsection 6d.

  A separable portion of the object code, whose source code is excluded
from the Corresponding Source as a System Library, need not be
included in conveying the object code work.

  A &quot;User Product&quot; is either (1) a &quot;consumer product&quot;, which means any
tangible personal property which is normally used for personal, family,
or household purposes, or (2) anything designed or sold for incorporation
into a dwelling.  In determining whether a product is a consumer product,
doubtful cases shall be resolved in favor of coverage.  For a particular
product received by a particular user, &quot;normally used&quot; refers to a
typical or common use of that class of product, regardless of the status
of the particular user or of the way in which the particular user
actually uses, or expects or is expected to use, the product.  A product
is a consumer product regardless of whether the product has substantial
commercial, industrial or non-consumer uses, unless such uses represent
the only significant mode of use of the product.

  &quot;Installation Information&quot; for a User Product means any methods,
procedures, authorization keys, or other information required to install
and execute modified versions of a covered work in that User Product from
a modified version of its Corresponding Source.  The information must
suffice to ensure that the continued functioning of the modified object
code is in no case prevented or interfered with solely because
modification has been made.

  If you convey an object code work under this section in, or with, or
specifically for use in, a User Product, and the conveying occurs as
part of a transaction in which the right of possession and use of the
User Product is transferred to the recipient in perpetuity or for a
fixed term (regardless of how the transaction is characterized), the
Corresponding Source conveyed under this section must be accompanied
by the Installation Information.  But this requirement does not apply
if neither you nor any third party retains the ability to install
modified object code on the User Product (for example, the work has
been installed in ROM).

  The requirement to provide Installation Information does not include a
requirement to continue to provide support service, warranty, or updates
for a work that has been modified or installed by the recipient, or for
the User Product in which it has been modified or installed.  Access to a
network may be denied when the modification itself materially and
adversely affects the operation of the network or violates the rules and
protocols for communication across the network.

  Corresponding Source conveyed, and Installation Information provided,
in accord with this section must be in a format that is publicly
documented (and with an implementation available to the public in
source code form), and must require no special password or key for
unpacking, reading or copying.

  7. Additional Terms.

  &quot;Additional permissions&quot; are terms that supplement the terms of this
License by making exceptions from one or more of its conditions.
Additional permissions that are applicable to the entire Program shall
be treated as though they were included in this License, to the extent
that they are valid under applicable law.  If additional permissions
apply only to part of the Program, that part may be used separately
under those permissions, but the entire Program remains governed by
this License without regard to the additional permissions.

  When you convey a copy of a covered work, you may at your option
remove any additional permissions from that copy, or from any part of
it.  (Additional permissions may be written to require their own
removal in certain cases when you modify the work.)  You may place
additional permissions on material, added by you to a covered work,
for which you have or can give appropriate copyright permission.

  Notwithstanding any other provision of this License, for material you
add to a covered work, you may (if authorized by the copyright holders of
that material) supplement the terms of this License with terms:

    a) Disclaiming warranty or limiting liability differently from the
    terms of sections 15 and 16 of this License; or

    b) Requiring preservation of specified reasonable legal notices or
    author attributions in that material or in the Appropriate Legal
    Notices displayed by works containing it; or

    c) Prohibiting misrepresentation of the origin of that material, or
    requiring that modified versions of such material be marked in
    reasonable ways as different from the original version; or

    d) Limiting the use for publicity purposes of names of licensors or
    authors of the material; or

    e) Declining to grant rights under trademark law for use of some
    trade names, trademarks, or service marks; or

    f) Requiring indemnification of licensors and authors of that
    material by anyone who conveys the material (or modified versions of
    it) with contractual assumptions of liability to the recipient, for
    any liability that these contractual assumptions directly impose on
    those licensors and authors.

  All other non-permissive additional terms are considered &quot;further
restrictions&quot; within the meaning of section 10.  If the Program as you
received it, or any part of it, contains a notice stating that it is
governed by this License along with a term that is a further
restriction, you may remove that term.  If a license document contains
a further restriction but permits relicensing or conveying under this
License, you may add to a covered work material governed by the terms
of that license document, provided that the further restriction does
not survive such relicensing or conveying.

  If you add terms to a covered work in accord with this section, you
must place, in the relevant source files, a statement of the
additional terms that apply to those files, or a notice indicating
where to find the applicable terms.

  Additional terms, permissive or non-permissive, may be stated in the
form of a separately written license, or stated as exceptions;
the above requirements apply either way.

  8. Termination.

  You may not propagate or modify a covered work except as expressly
provided under this License.  Any attempt otherwise to propagate or
modify it is void, and will automatically terminate your rights under
this License (including any patent licenses granted under the third
paragraph of section 11).

  However, if you cease all violation of this License, then your
license from a particular copyright holder is reinstated (a)
provisionally, unless and until the copyright holder explicitly and
finally terminates your license, and (b) permanently, if the copyright
holder fails to notify you of the violation by some reasonable means
prior to 60 days after the cessation.

  Moreover, your license from a particular copyright holder is
reinstated permanently if the copyright holder notifies you of the
violation by some reasonable means, this is the first time you have
received notice of violation of this License (for any work) from that
copyright holder, and you cure the violation prior to 30 days after
your receipt of the notice.

  Termination of your rights under this section does not terminate the
licenses of parties who have received copies or rights from you under
this License.  If your rights have been terminated and not permanently
reinstated, you do not qualify to receive new licenses for the same
material under section 10.

  9. Acceptance Not Required for Having Copies.

  You are not required to accept this License in order to receive or
run a copy of the Program.  Ancillary propagation of a covered work
occurring solely as a consequence of using peer-to-peer transmission
to receive a copy likewise does not require acceptance.  However,
nothing other than this License grants you permission to propagate or
modify any covered work.  These actions infringe copyright if you do
not accept this License.  Therefore, by modifying or propagating a
covered work, you indicate your acceptance of this License to do so.

  10. Automatic Licensing of Downstream Recipients.

  Each time you convey a covered work, the recipient automatically
receives a license from the original licensors, to run, modify and
propagate that work, subject to this License.  You are not responsible
for enforcing compliance by third parties with this License.

  An &quot;entity transaction&quot; is a transaction transferring control of an
organization, or substantially all assets of one, or subdividing an
organization, or merging organizations.  If propagation of a covered
work results from an entity transaction, each party to that
transaction who receives a copy of the work also receives whatever
licenses to the work the party's predecessor in interest had or could
give under the previous paragraph, plus a right to possession of the
Corresponding Source of the work from the predecessor in interest, if
the predecessor has it or can get it with reasonable efforts.

  You may not impose any further restrictions on the exercise of the
rights granted or affirmed under this License.  For example, you may
not impose a license fee, royalty, or other charge for exercise of
rights granted under this License, and you may not initiate litigation
(including a cross-claim or counterclaim in a lawsuit) alleging that
any patent claim is infringed by making, using, selling, offering for
sale, or importing the Program or any portion of it.

  11. Patents.

  A &quot;contributor&quot; is a copyright holder who authorizes use under this
License of the Program or a work on which the Program is based.  The
work thus licensed is called the contributor's &quot;contributor version&quot;.

  A contributor's &quot;essential patent claims&quot; are all patent claims
owned or controlled by the contributor, whether already acquired or
hereafter acquired, that would be infringed by some manner, permitted
by this License, of making, using, or selling its contributor version,
but do not include claims that would be infringed only as a
consequence of further modification of the contributor version.  For
purposes of this definition, &quot;control&quot; includes the right to grant
patent sublicenses in a manner consistent with the requirements of
this License.

  Each contributor grants you a non-exclusive, worldwide, royalty-free
patent license under the contributor's essential patent claims, to
make, use, sell, offer for sale, import and otherwise run, modify and
propagate the contents of its contributor version.

  In the following three paragraphs, a &quot;patent license&quot; is any express
agreement or commitment, however denominated, not to enforce a patent
(such as an express permission to practice a patent or covenant not to
sue for patent infringement).  To &quot;grant&quot; such a patent license to a
party means to make such an agreement or commitment not to enforce a
patent against the party.

  If you convey a covered work, knowingly relying on a patent license,
and the Corresponding Source of the work is not available for anyone
to copy, free of charge and under the terms of this License, through a
publicly available network server or other readily accessible means,
then you must either (1) cause the Corresponding Source to be so
available, or (2) arrange to deprive yourself of the benefit of the
patent license for this particular work, or (3) arrange, in a manner
consistent with the requirements of this License, to extend the patent
license to downstream recipients.  &quot;Knowingly relying&quot; means you have
actual knowledge that, but for the patent license, your conveying the
covered work in a country, or your recipient's use of the covered work
in a country, would infringe one or more identifiable patents in that
country that you have reason to believe are valid.

  If, pursuant to or in connection with a single transaction or
arrangement, you convey, or propagate by procuring conveyance of, a
covered work, and grant a patent license to some of the parties
receiving the covered work authorizing them to use, propagate, modify
or convey a specific copy of the covered work, then the patent license
you grant is automatically extended to all recipients of the covered
work and works based on it.

  A patent license is &quot;discriminatory&quot; if it does not include within
the scope of its coverage, prohibits the exercise of, or is
conditioned on the non-exercise of one or more of the rights that are
specifically granted under this License.  You may not convey a covered
work if you are a party to an arrangement with a third party that is
in the business of distributing software, under which you make payment
to the third party based on the extent of your activity of conveying
the work, and under which the third party grants, to any of the
parties who would receive the covered work from you, a discriminatory
patent license (a) in connection with copies of the covered work
conveyed by you (or copies made from those copies), or (b) primarily
for and in connection with specific products or compilations that
contain the covered work, unless you entered into that arrangement,
or that patent license was granted, prior to 28 March 2007.

  Nothing in this License shall be construed as excluding or limiting
any implied license or other defenses to infringement that may
otherwise be available to you under applicable patent law.

  12. No Surrender of Others' Freedom.

  If conditions are imposed on you (whether by court order, agreement or
otherwise) that contradict the conditions of this License, they do not
excuse you from the conditions of this License.  If you cannot convey a
covered work so as to satisfy simultaneously your obligations under this
License and any other pertinent obligations, then as a consequence you may
not convey it at all.  For example, if you agree to terms that obligate you
to collect a royalty for further conveying from those to whom you convey
the Program, the only way you could satisfy both those terms and this
License would be to refrain entirely from conveying the Program.

  13. Use with the GNU Affero General Public License.

  Notwithstanding any other provision of this License, you have
permission to link or combine any covered work with a work licensed
under version 3 of the GNU Affero General Public License into a single
combined work, and to convey the resulting work.  The terms of this
License will continue to apply to the part which is the covered work,
but the special requirements of the GNU Affero General Public License,
section 13, concerning interaction through a network will apply to the
combination as such.

  14. Revised Versions of this License.

  The Free Software Foundation may publish revised and/or new versions of
the GNU General Public License from time to time.  Such new versions will
be similar in spirit to the present version, but may differ in detail to
address new problems or concerns.

  Each version is given a distinguishing version number.  If the
Program specifies that a certain numbered version of the GNU General
Public License &quot;or any later version&quot; applies to it, you have the
option of following the terms and conditions either of that numbered
version or of any later version published by the Free Software
Foundation.  If the Program does not specify a version number of the
GNU General Public License, you may choose any version ever published
by the Free Software Foundation.

  If the Program specifies that a proxy can decide which future
versions of the GNU General Public License can be used, that proxy's
public statement of acceptance of a version permanently authorizes you
to choose that version for the Program.

  Later license versions may give you additional or different
permissions.  However, no additional obligations are imposed on any
author or copyright holder as a result of your choosing to follow a
later version.

  <a name="nowarranty" id="nowarranty"></a>15. Disclaimer of Warranty.

  THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY
APPLICABLE LAW.  EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT
HOLDERS AND/OR OTHER PARTIES PROVIDE THE PROGRAM &quot;AS IS&quot; WITHOUT WARRANTY
OF ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO,
THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
PURPOSE.  THE ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM
IS WITH YOU.  SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF
ALL NECESSARY SERVICING, REPAIR OR CORRECTION.

  16. Limitation of Liability.

  IN NO EVENT UNLESS REQUIRED BY APPLICABLE LAW OR AGREED TO IN WRITING
WILL ANY COPYRIGHT HOLDER, OR ANY OTHER PARTY WHO MODIFIES AND/OR CONVEYS
THE PROGRAM AS PERMITTED ABOVE, BE LIABLE TO YOU FOR DAMAGES, INCLUDING ANY
GENERAL, SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES ARISING OUT OF THE
USE OR INABILITY TO USE THE PROGRAM (INCLUDING BUT NOT LIMITED TO LOSS OF
DATA OR DATA BEING RENDERED INACCURATE OR LOSSES SUSTAINED BY YOU OR THIRD
PARTIES OR A FAILURE OF THE PROGRAM TO OPERATE WITH ANY OTHER PROGRAMS),
EVEN IF SUCH HOLDER OR OTHER PARTY HAS BEEN ADVISED OF THE POSSIBILITY OF
SUCH DAMAGES.

  17. Interpretation of Sections 15 and 16.

  If the disclaimer of warranty and limitation of liability provided
above cannot be given local legal effect according to their terms,
reviewing courts shall apply local law that most closely approximates
an absolute waiver of all civil liability in connection with the
Program, unless a warranty or assumption of liability accompanies a
copy of the Program in return for a fee.

                     END OF TERMS AND CONDITIONS
</pre>
</td>
	<td></td>
</tr>
</table>
<?php
	require_once('include/footer.inc.php');
}
?>
