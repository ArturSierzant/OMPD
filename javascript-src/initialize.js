//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2019 Artur Sierzant                            |
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
//  | overLIB initialize (overlib.js & overlib_cssstyle.js)                  |
//  +------------------------------------------------------------------------+
var ol_width			= 0;
var ol_vauto			= 1;
var ol_fgclass			= 'ol_foreground';
var ol_bgclass			= 'ol_background';
var ol_textfontclass	= 'ol_text';
var ol_captionfontclass	= 'ol_caption';
// Disable overLIB by increasing delay
if (navigator.userAgent.match(/iPad|iPhone|iPod|android|symbian/i))
	var ol_delay = 3600000;




//  +------------------------------------------------------------------------+
//  | SHA1 (sha1.js)                                                         |
//  +------------------------------------------------------------------------+
function sha1(data) {
	return rstr2hex(rstr_sha1(str2rstr_utf8(data)));
}




//  +------------------------------------------------------------------------+
//  | HMAC-SHA1 (sha1.js)                                                    |
//  +------------------------------------------------------------------------+
function hmacsha1(key, data) {
	return rstr2hex(rstr_hmac_sha1(str2rstr_utf8(key), str2rstr_utf8(data)));
}




//  +------------------------------------------------------------------------+
//  | makeSid from                                                           |
//  | https://stackoverflow.com/questions/1349404/                           |
//  | generate-random-string-characters-in-javascript                        |
//  +------------------------------------------------------------------------+
function makeSid(length) {
	var result           = '';
	var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	var charactersLength = characters.length;
	for ( var i = 0; i < length; i++ ) {
		result += characters.charAt(Math.floor(Math.random() * charactersLength));
	}
return result;
}



//  +------------------------------------------------------------------------+
//  | Set screen width in a session cookie for server side PHP script.       |
//  +------------------------------------------------------------------------+
function cookie() {
	var														width = 1024;
	if		(typeof window.innerWidth == 'number')			width = window.innerWidth;
	else if	(typeof document.body.clientWidth == 'number')	width = document.body.clientWidth; // IE < 9
	document.cookie = 'netjukebox_width=' + width + ';samesite=strict';
	if (!getCookie('netjukebox_sid')){
		document.cookie = 'netjukebox_sid=' + makeSid(40) + '; Max-Age = 31536000';
	}
}




//  +------------------------------------------------------------------------+
//  | getCookie from https://www.w3schools.com/js/js_cookies.asp             |
//  +------------------------------------------------------------------------+
	function getCookie(cname) {
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
	}




//  +------------------------------------------------------------------------+
//  | Formatted time                                                         |
//  +------------------------------------------------------------------------+
function formattedTime(miliseconds) {
	var seconds 	= Math.round(miliseconds / 1000);
	var hour 		= Math.floor(seconds / 3600);
	var minutes 	= Math.floor(seconds / 60) % 60;
	seconds 		= seconds % 60;
		
	if (hour > 0)	return hour + ':' + zeroPad(minutes, 2) + ':' + zeroPad(seconds, 2);
	else			return minutes + ':' + zeroPad(seconds, 2);
}




//  +------------------------------------------------------------------------+
//  | Zero pad                                                               |
//  +------------------------------------------------------------------------+
function zeroPad(number, n) { 
	var zeroPad = '' + number;
	
	while(zeroPad.length < n)
		zeroPad = '0' + zeroPad; 
	
	return zeroPad;
}




//  +------------------------------------------------------------------------+
//  | Show hide                                                              |
//  +------------------------------------------------------------------------+
function showHide(a, b) {
	document.getElementById(a).style.display = (document.getElementById(a).style.display == 'none') ? '' : 'none';
	document.getElementById(b).style.display = (document.getElementById(b).style.display == 'none') ? '' : 'none';
}




//  +------------------------------------------------------------------------+
//  | Inverse checkbox                                                       |
//  +------------------------------------------------------------------------+
function inverseCheckbox(frm) {
	for (var i = 0; i < frm.elements.length; i++) {
		if (frm.elements[i].type == 'checkbox') 
			frm.elements[i].checked = !frm.elements[i].checked;
	}
}




//  +------------------------------------------------------------------------+
//  | Get relative X                                                         |
//  +------------------------------------------------------------------------+
function getRelativeX(event, object) {
	if (typeof event.pageX == 'number' && typeof object.offsetLeft == 'number') {	
		var totalOffsetLeft = 0;
		while (object.offsetParent) { 
			totalOffsetLeft += object.offsetLeft;
			object = object.offsetParent; 
		} 
		return event.pageX - totalOffsetLeft;
	}
	else if (typeof event.offsetX == 'number')
		return event.offsetX;
	else
		return 0;
}




//  +---------------------------------------------------------------------------+
//  | AJAX request                                                              |
//  +---------------------------------------------------------------------------+
function ajaxRequest(url, callback, postData) {
	if (typeof timer_id == 'number' && typeof timer_function == 'string' && typeof timer_delay == 'number') {
		clearTimeout(timer_id);
		timer_id = setTimeout(timer_function, timer_delay);
	}
	
	if (typeof XMLHttpRequest != 'undefined') {
		url += (url.indexOf('?') == -1) ? '?ajax=1' : '&ajax=1';
		var method = (postData) ? 'post' : 'get';
		var http = new XMLHttpRequest();
		
		
		http.open(method, url, true);
		
		if (postData)
		    http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		http.onreadystatechange = function() {
			if (http.readyState == 4 && http.status == 200 && typeof callback != 'undefined') {
				if (typeof JSON != 'undefined')	var data = JSON.parse(http.responseText);
				else							var data = eval('(' + http.responseText + ')');
				callback(data);
			}
			else if (http.readyState == 4 && http.status == 500 && (http.responseText.substr(0,7) == 'http://' || http.responseText.substr(0,8) == 'https://'))
				window.location.href=http.responseText;
		}
		
		http.send(postData);
	}
}
