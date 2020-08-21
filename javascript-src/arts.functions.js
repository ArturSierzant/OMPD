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


function changeTileSizeInfo() {
	$("a[href]")
	.each(function() {
	if (this.href.indexOf('tileSizePHP')<0) {
		if (this.href.indexOf('index.php?')<0) {
			this.href = this.href.replace('index.php','index.php?tileSizePHP=' + $tileSizeArr[0]);
			}
		else {
			this.href = this.href.replace('index.php?','index.php?tileSizePHP=' + $tileSizeArr[0] + '&');
		}
	}
	else {
		pos1 = this.href.indexOf('PHP=');
		ts = this.href.substr(pos1 + 4, 3);
		/* pos2 = this.href.indexOf('&', pos1);
		if (pos2<0) {
			ts = this.href.substr(pos1 + 4, this.href.length - pos1 + 4);
		}
		else {
			ts = this.href.substr(pos1 + 4, pos2 - pos1);
		} 
		console.log ('ts: ' + ts);
		*/
		this.href = this.href.replace('?tileSizePHP=' + ts,'?tileSizePHP=' + $tileSizeArr[0]);
	}
	});
	
	$("[onclick]")
	.each(function() {
		v1 = $(this).attr('onclick');
		if (v1.indexOf('tileSizePHP')<0) {
			if (v1.indexOf('index.php?')<0) {
			v2 = v1.replace('index.php','index.php?tileSizePHP=' + $tileSizeArr[0]);
			}
			else {
			v2 = v1.replace('index.php?','index.php?tileSizePHP=' + $tileSizeArr[0] + '&');
			}
			$(this).attr('onclick', v2);	
		}
		else {
			pos1 = v1.indexOf('PHP=');
			ts = v1.substr(pos1 + 4, 3);
			v2 = v1.replace('tileSizePHP=' + ts,'tileSizePHP=' + $tileSizeArr[0]);
			$(this).attr('onclick', v2);	
		}
	});
}

function calcTileSize() {
  var $containerWidth = $(window).width();
	//if ($containerWidth > 1280) $containerWidth = 1280;
	if (!$useMaxWidth) { //special case for skin dark-wide
		if ($containerWidth > 1280) $containerWidth = 1280;
	}
	
	if ($containerWidth <= 639) {
	$tileCount=3;
	$tileSize = Math.floor(($containerWidth/$tileCount) - 1);
	//$tileSize = Math.floor((($containerWidth/$tileCount) - 1) * 10) / 10;
		$('.tile_info').css('font-size', function() { return Math.floor($tileSize/12) + 'px'; });
	}
	
	if ($containerWidth > 639 && $containerWidth <= 1024) {
	$tileCount=5;
	$tileSize = Math.floor(($containerWidth/$tileCount) - 1);
	//$tileSize = Math.floor((($containerWidth/$tileCount) - 1) * 10)/10;
			$('.tile_info').css('font-size', function() { return Math.floor($tileSize/12) + 'px'; });
	}
	
	//if ($containerWidth > 1024 && $containerWidth <=1280) {
	if ($containerWidth > 1024) {
		$tileCount=7;
		$tileSize = Math.floor((($containerWidth)/$tileCount) - 1);
		//$tileSize = Math.floor((($containerWidth/$tileCount) - 1) * 10)/10;
    	$('.tile_info').css('font-size', function() { return Math.floor($tileSize/13) + 'px'; });
    	//$('.tile_info').css('font-size', function() { return '0.8em'; });
	}
	//console.log ('tileSize: ' + $tileSize);
	return [$tileSize,$containerWidth];
}

function updateAddPlay(data) {

	$('#played').html(data.played);
	$('#last_played').html(data.last_played + '<span id="playedCal" class=" icon-anchor" onclick="togglePlayedHistory();">&nbsp;&nbsp;<i class="fa fa-calendar fa-lg"></i></span>');
	$('#popularity').html(data.popularity);
	var bar_pop = $('#bar-popularity-out').width() * data.popularity/100;
	//console.log ('bar_pop:' + bar_pop);
	$('#bar_popularity').css('width', bar_pop);
};

function evaluateRandom(data) {
	var timeOut = 2000;
	if (data.random_files_result == 'random_files_OK') {
		$('[id="randomPlay_' + data.id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
		offMenuSub('');
		
		setTimeout(function(){
			$('[id="randomPlay_' + data.id +'"]').removeClass('fa-check-square icon-ok').addClass('fa-random');
		}, timeOut);
	}
	else {
		$('[id="randomPlay_' + data.id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-triangle icon-nok');
		
		setTimeout(function(){
			$('[id="randomPlay_' + data.id +'"]').removeClass('fa-exclamation-triangle icon-nok').addClass('fa-random');
		}, timeOut);
		
	}
};


function evaluatePlayTo(data) {
	var timeOut = 2000;
	if (data.playToResult == 'playTo_OK') {
		$('[id="playTo_' + data.player_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
		
		setTimeout(function(){
			$('[id="playTo_' + data.player_id +'"]').removeClass('fa-check-square icon-ok').addClass('fa-share-square-o');
		}, timeOut);
	}
	else {
		$('[id="playTo_' + data.player_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-triangle icon-nok');
		
		setTimeout(function(){
			$('[id="playTo_' + data.player_id +'"]').removeClass('fa-exclamation-triangle icon-nok').addClass('fa-share-square-o');
		}, timeOut);
		
	}
}

function evaluateAdd(data) {
	var timeOut = 2000;
	albumId = data.album_id;
	if (data.favorite_id) {data.album_id = data.favorite_id};
	if (data.file_id) {data.album_id = data.file_id};
	if (data.random) {data.album_id = 'random'};
	if (data.disc) {data.album_id = data.album_id + '_' + data.disc};
	if (data.addType) {data.album_id = data.addType};
	if (data.album_id && !data.track_id) { 
		if (data.addResult == 'add_OK') {
			$('[id="add_' + data.album_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
			offMenuSub('');
			
			setTimeout(function(){
			  $('[id="add_' + data.album_id +'"]').removeClass('fa-check-square icon-ok').addClass('fa-plus-circle');
			}, timeOut);
		}
		else if (data.addResult == 'add_error') {
			$('[id="add_' + data.album_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-triangle icon-nok');
			
			setTimeout(function(){
			  $('[id="add_' + data.album_id +'"]').removeClass('fa-exclamation-triangle icon-nok').addClass('fa-plus-circle');
			}, timeOut);
			
		}
		else if (data.playResult == 'play_OK') {
			$('[id="play_' + data.album_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
			offMenuSub('');
			
			setTimeout(function(){
			  $('[id="play_' + data.album_id +'"]').removeClass('fa-check-square icon-ok').addClass('fa-play-circle-o');
			}, timeOut);
		}
		else if (data.playResult == 'play_error') {
			$('[id="play_' + data.album_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-triangle icon-nok');
			
			setTimeout(function(){
			  $('[id="play_' + data.album_id +'"]').removeClass('fa-exclamation-triangle icon-nok').addClass('fa-play-circle-o');
			}, timeOut);
			
		}
		else if (data.insertResult == 'insert_OK') {
			$('[id="insert_' + data.album_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
			offMenuSub('');
			
			setTimeout(function(){
			  $('[id="insert_' + data.album_id +'"]').removeClass('fa-check-square icon-ok').addClass('fa-indent');
			}, timeOut);
		}	
		else if (data.insertPlayResult == 'insert_OK') {
			$('[id="insertPlay_' + data.album_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
			offMenuSub('');
			
			setTimeout(function(){
			  $('[id="insertPlay_' + data.album_id +'"]').removeClass('fa-check-square icon-ok').addClass('fa-play-circle');
			}, timeOut);
		}
		else if (data.insertResult == 'insert_error') {
			$('[id="insert_' + data.album_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-triangle icon-nok');
			
			setTimeout(function(){
			  $('[id="insert_' + data.album_id +'"]').removeClass('fa-exclamation-triangle icon-nok').addClass('fa-indent');
			}, timeOut);
		}
	}
	
	else if (data.track_id){
		if (data.addResult == 'add_OK') {
			$('[id="add_' + data.track_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
			offMenuSub('');
			
			setTimeout(function(){
			  $('[id="add_' + data.track_id +'"]').removeClass('fa-check-square icon-ok').addClass('fa-plus-circle');
			}, timeOut);
		}
		else if (data.addResult == 'add_error') {
			$('[id="add_' + data.track_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-triangle icon-nok');
			
			setTimeout(function(){
			  $('[id="add_' + data.track_id +'"]').removeClass('fa-exclamation-triangle icon-nok').addClass('fa-plus-circle');
			}, timeOut);
			
		}
		
		else if (data.playResult == 'play_OK') {
			$('[id="play_' + data.track_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
			offMenuSub('');
			
			setTimeout(function(){
			  $('[id="play_' + data.track_id +'"]').removeClass('fa-check-square icon-ok').addClass('fa-play-circle-o');
			}, timeOut);
		}
		else if (data.playResult == 'play_error') {
			$('[id="play_' + data.track_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-triangle icon-nok');
			
			setTimeout(function(){
			  $('[id="play_' + data.track_id +'"]').removeClass('fa-exclamation-triangle icon-nok').addClass('fa-play-circle-o');
			}, timeOut);
			
		}
		
		if (data.insertResult == 'insert_OK') {
			$('[id="insert_' + data.track_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
			offMenuSub('');
			
			setTimeout(function(){
			  $('[id="insert_' + data.track_id +'"]').removeClass('fa-check-square icon-ok').addClass('fa-indent');
			}, timeOut);
		}
		else if (data.insertResult == 'insert_error') {
			$('[id="insert_' + data.track_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-triangle icon-nok');
			
			setTimeout(function(){
			  $('[id="insert_' + data.track_id +'"]').removeClass('fa-exclamation-triangle icon-nok').addClass('fa-indent');
			}, timeOut);
			
		}
		else if (data.insertPlayResult == 'insert_OK') {
			console.log ("in proc");
			$('[id="insertPlay_' + data.track_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
			$('[id="menu-icon' + data.position_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
			offMenuSub('');
			//for playing tracks from Favorite list:
			if ($('[id="add_' + data.track_id +'"]').hasClass("fa-cog")) {
				$('[id="add_' + data.track_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square icon-ok');
			}
			setTimeout(function(){
			  $('[id="insertPlay_' + data.track_id +'"]').removeClass('fa-check-square icon-ok').addClass('fa-play-circle');
			  $('[id="menu-icon' + data.position_id +'"]').removeClass('fa-check-square icon-ok').addClass('fa-bars');
			  $('[id="add_' + data.track_id +'"]').removeClass('fa-check-square icon-ok').addClass('fa-plus-circle');
			  
			}, timeOut);
		}
		else if (data.insertPlayResult == 'insert_error') {
			$('[id="insertPlay_' + data.track_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-triangle icon-nok');
			$('[id="menu-icon' + data.position_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-triangle icon-nok');
			//for playing tracks from Favorite list:
			if ($('[id="add_' + data.track_id +'"]').hasClass("fa-cog")) {
				$('[id="add_' + data.track_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-triangle icon-nok');
			}
			setTimeout(function(){
			  $('[id="insertPlay_' + data.track_id +'"]').removeClass('fa-exclamation-triangle icon-nok').addClass('fa-play-circle');
			  $('[id="menu-icon' + data.position_id +'"]').removeClass('fa-exclamation-triangle icon-nok').addClass('fa-bar');
				$('[id="add_' + data.track_id +'"]').removeClass('fa-exclamation-triangle icon-nok').addClass('fa-plus-circle');
			}, timeOut);
			
		}
	}
	if (albumId){
	$.ajax({  
			url: "play.php",  
			type: "GET",  
			data: { action : 'updateAddPlay',
					album_id : albumId,
					},  
			dataType: "json"
		}).done(function(data) {
			updateAddPlay(data);
		}); 
	}
};

function playAlbum(albumId, md){
	var request = $.ajax({  
			url: "play.php",  
			type: "GET",  
			data: { action : 'playSelect',
					album_id : albumId,
					md : md,
					},  
			dataType: "html"
		}); 
		
		request.done(function( data ) {  
			redirToNowPlaying();
		}); 
		
};

function redirToNowPlaying(){
	location.href = 'playlist.php';
}

function resizeTile($tileSize,$containerWidth) {
	$('.tile').css('width', function() { return $tileSize; });
	$('.tile').css('height', function() { return $tileSize; });
	resizeSuggested($tileSize,$containerWidth);
	resizeUsersTab($tileSize,$containerWidth);
	
}

function resizeSuggested($tileSize,$containerWidth) {
	$('.full').css('height', function() { return ($tileSize + 4); });
	$('.full').css('width', function() { 
	return ($tileCount * $tileSize + (($tileCount - 1) * 1) + 'px'); 
	});
}

function resizeUsersTab($tileSize,$containerWidth) {
	$('#usersTab').css('width', function() { 
	return ($tileCount * $tileSize + (($tileCount - 1) * 2) + 'px'); 
	});
}

function resizeFormSettings(containerWidth, myCodeMirror) {
		if (containerWidth > 1280) {containerWidth = 1280};
		//winH = $(window).height() - $('#menu').height() - 200;
		winH = window.innerHeight - $('#menu').height() - 200;
		if (winH < 200) {winH = 180};
		myCodeMirror.setSize(containerWidth, winH);
}

function resizeImgContainer() {
	//winW = $(window).width();
	var winW = window.innerWidth;
	var winH = window.innerHeight;
	var bodyMaxWidth = $('body').css('max-width');
	var maxH;
	var minW;
	var imageH;
	
	miniplayerW = $("#miniplayer").width();
	if (miniplayerW){
		console.log("miniplayerW: " + miniplayerW);
		if (miniplayerW > winW) miniplayerW = winW;
		$("#file-info-mini").css("min-width",(miniplayerW - $("#image_container_mini").width() - $("#media_control_mini").width()));
		$("#file-info-mini").css("max-width",(miniplayerW - $("#image_container_mini").width() - $("#media_control_mini").width()));
		$("#media_control_mini").css("display","table-cell");
	}
	
	//prevent resizing when virtual keybord is visible on mobile devices 
	//if ($("#savePlaylistAsName").is(":focus") || $("#savePlaylistComment").is(":focus") || $("#addUrlAddress").is(":focus")) 
	if ($("input").is(":focus")) 
		return;
		//$("#savePlaylistAsName").blur();
	
	$("#discBrowser").hide();
	
	var showMenuSave = false;
	var showMenuUrl = false;
	var showMenuSearch = false;
	if ($("#menuSubMiddleMediaSavePlaylist").css('display')!=='none') {
		$("#menuSubMiddleMediaSavePlaylist").hide();
		showMenuSave = true;
	}
	
	if ($("#menuSubMiddleMediaAddUrl").css('display')!=='none') {
		$("#menuSubMiddleMediaAddUrl").hide();
		showMenuUrl = true;
	}
	
	if ($("#searchFormAll").css('display')!=='none') {
		$("#searchFormAll").hide();
		showMenuSearch = true;
	}
		
	if (winW > parseInt(bodyMaxWidth)) winW = bodyMaxWidth;
	
	$('#image_container').css('width', '');
	$('#image_container').css('height', '');
	$('#image_container').css('max-height', '');
	$('#image').css('max-width', '');
	$('#image').css('height', '');
	$('#image').css('max-height', '');
	$('.pl-track-info-right').css('width', '');
	$('.album-info-area-right').css('width', '');
	
	$('#image_in').css("top", "0");
	
	if (winW < 530) {
		
		$('#image').css('max-width', (winW));
		//maxH = $(window).height() - $('.pl-track-info-right').height() - $('#menu').height() - 3;
		//use fixedMenuFill and menu_middle because they have defined height property in css file.
		//maxH = $(window).height() - $('.pl-track-info-right').height() - $('#fixedMenuFill').height()- $('.menu_middle').height() - 3;
		maxH = winH - $('.pl-track-info-right').height() - $('#fixedMenuFill').height()- $('.menu_middle').height() - 3;
		//maxH = $(window).height() - $('#menu').height() - 3;
		//console.log ('maxH=' + maxH);
		maxH = Math.round(maxH);
		if ((winW - maxH) > 20) {
			$('#image').css('width', maxH);
			//$('#image').css('height', '');
		}
		else {
			$('#image').css('width', '');
			//$('#image').css('height', '');
		}
		//return;
		$('#image').css('height', maxH);
		$('#image').css('max-height', maxH);

		
		//$('#image_container').css('max-height', function(){return (winW) * 1.1;});
		
		/* imageH = $('#image').height();
		if (imageH > maxH * 1.1) {
			var imgTop = (imageH - maxH)/2;
			//console.log ("maxH=" + maxH + " imageH=" + imageH);
			$('#image_in').css("top", function() { return ("-" + (imageH - maxH)/2) + "px"});
		} */
		
	} 
	else {
		$('#image').css('width', '');
		minW = parseInt($('#image_container').css('min-width'));
		maxHpx = (winH - $('.menu_top').height() - $('.menu_middle').height() - 5);
		maxH = maxHpx/winW * 100;
		maxH = Math.floor(maxH);
		if (maxHpx<minW){
			// $('#image_container').css('width', '');
			// $('.pl-track-info-right').css('width', '');
			// $('.album-info-area-right').css('width', '');
		}
		else if (maxH<50 || winW == bodyMaxWidth) {
			$('#image_container').css('width', maxH  + "%");
			$('.pl-track-info-right').css('width', (100 - maxH - 2) + "%");
			$('.album-info-area-right').css('width', (100 - maxH - 3) + "%");
		} 
		else {
			$('#image_container').css('width', '50%');
			$('.pl-track-info-right').css('width', '48%');
			$('.album-info-area-right').css('width', '47%');
		}
		$('#image').css('max-height', maxHpx);
		$('#image').css('min-height', '220px');
		
		imageH = $('#image').height();
		if (imageH > maxHpx * 1.1) {
			var imgTop = (imageH - maxH)/2;
			$('#image_in').css("top", function() { return ("-" + (imageH - maxHpx)/2) + "px"});
		}
	} 
	
	if (showMenuSave == true) $("#menuSubMiddleMediaSavePlaylist").show();
	if (showMenuUrl == true) $("#menuSubMiddleMediaAddUrl").show();
	if (showMenuSearch == true) $("#searchFormAll").show();
	
	if (typeof thumbID !== 'undefined') {
		$('[id^=thumb]').css('pointer-events','none');
		$("#discBrowser").css("max-width",$("#image_container").width());
		$("#discBrowser").css("width",$("#image_container").width());
		$("#discBrowser").show();
			
		//scroll discography browser to active album		
		// '+3' is for right margin of thumb 
		$("#discBrowser").animate({scrollLeft: (($(thumbID).width() + 3)  * thumbIDCount - $("#discBrowser").width()/2 + $(thumbID).width()/2) }, 500);
			
		setTimeout(function() {
				$('[id^=thumb]').css('pointer-events','all');
			}, 1000);
	}
	
	/* miniplayerW = $("#miniplayer").width();
	if (miniplayerW > winW) miniplayerW = winW;
	$("#file-info-mini").css("min-width",(miniplayerW - $("#image_container_mini").width() - $("#media_control_mini").width()));
	$("#file-info-mini").css("max-width",(miniplayerW - $("#image_container_mini").width() - $("#media_control_mini").width()));
	
	$("#media_control_mini").css("display","table-cell"); */
	
	if ($(window).width() > 1280) {
		$(".back-to-top").css("right",($(window).width() - 1280)/2);
	}
	else {
		$(".back-to-top").css("right",10)
	}
	
	
} 



function toggleMenuSub(id) {
	$('[id^=menu-sub-track]').slideUp("slow", function() {    
	});
	$('[id^=menu-icon]').removeClass("icon-small-selected");
	$('[id^=menu-star-track]').slideUp("slow", function() {    
	});
	//$('[id^=favorite_star-]').removeClass("icon-selected");
	$('[id^=blacklist-star-bg]').removeClass("icon-selected");
	e = $('#menu-sub-track' + id);
	
	if (e.css("display")=="none"){
		$('#menu-icon' + id).addClass("icon-small-selected");
		e.slideDown( "slow", function() {}).promise().done(function(){
			if(!e.is_on_screen()) {
				scrollToShow($("#menu-sub-track" + id));
			}	
			
		});
	}
	else {
		e.slideUp( "slow", function() { });
		$('#menu-icon' + id).removeClass("icon-small-selected");
	}
	
};

function offMenuSub(id) {
	$('[id^=menu-sub-track' + id + ']').slideUp( "slow", function() {    
	});
	$('[id^=menu-icon' + id + ']').removeClass("icon-small-selected");
};


function toggleStarSub(id, track_id) {
	$('[id^=menu-sub-track]').slideUp("slow", function() {});
	$('[id^=menu-icon]').removeClass("icon-small-selected");
	$('[id^=menu-star-track]').slideUp("slow", function() {});
	$('[id^=blacklist-star-bg]').removeClass("icon-selected");
	e = $("#menu-star-track" + id);
	b = $('#blacklist-star-bg' + track_id);
	
	
	if (e.css("display")=="none"){
		b.addClass("icon-selected");
		e.slideDown( "slow", function() {}).promise().done(function(){
			if(!e.is_on_screen()) {
				scrollToShow(e);
			}		
		});
	}
	else {
		e.slideUp( "slow", function() {});
		b.removeClass('icon-selected');
	}
	//$('#menu-star-track' + id + ' input').first().focus();
	
};

function scrollToShow(el) {
	var elOffset = el.offset().top;
	var elHeight = el.height();
	//var windowHeight = $(window).height();
	var windowHeight = window.innerHeight;
	var windowScrollTop = $(window).scrollTop();
	var offset;
	var toTopMB = $(".back-to-top").css("margin-bottom");
	var toTopB = $(".back-to-top").css("bottom");
	var toTopMB = parseInt(toTopMB.replace("px",""));
	var toTopB = parseInt(toTopB.replace("px",""));
	console.log (toTopB + toTopMB);
	console.log ("elOffset: " + elOffset);
	console.log ("windowScrollTop: " + windowScrollTop);
	console.log ("elHeight: " + elHeight);
	console.log ("windowHeight: " + windowHeight);
	if (elHeight < windowHeight) {
		if ((elOffset - 130)  < windowScrollTop) {
			console.log("0");
			offset = elOffset - 130;
		}
		else {
			offset = elOffset - ((windowHeight - elHeight)) + toTopB + toTopMB + $(".back-to-top").height() + 12;
			console.log("1");
			if (elHeight > (windowHeight / 2)) {
				console.log("2");
				//offset = elOffset - ((windowHeight - elHeight));
				offset = elOffset - 130;
			}
		}	
	}
	else {
		console.log("3");
		console.log(el);
		offset = elOffset - 130;
	}
	var speed = 200;
	$('html, body').animate({scrollTop:offset}, speed);
}

$.fn.is_on_screen = function(){
    var win = $(window);
    var viewport = {
        top : win.scrollTop(),
        left : win.scrollLeft()
    };
    viewport.right = viewport.left + win.width();
    viewport.bottom = viewport.top + win.height();
 
    var bounds = this.offset();
    bounds.right = bounds.left + this.outerWidth();
    bounds.bottom = bounds.top + this.outerHeight() + 130;
	bounds.top = bounds.top - 110;
	console.log("viewport.top: " + viewport.top);
	console.log("viewport.bottom: " + viewport.bottom);
	console.log("bounds.top: " + bounds.top);
	console.log("bounds.bottom: " + bounds.bottom);
 
    return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.bottom || viewport.top > bounds.top));
};

function sleep(miliseconds) {
	var currentTime = new Date().getTime();
	while (currentTime + miliseconds >= new Date().getTime()) {
	}
}

function offStarSub(id) {
	$( '#menu-star-track' + id ).slideUp( "slow", function() {    
	});
	$('#favorite_star-' + id).removeClass("icon-selected");
};

function toggleSubMiddle(id,showTrackOptions) {
	if (typeof showTrackOptions === 'undefined') { showTrackOptions = false; }
	$('[id^=menuSubMiddleMedia]').slideUp("slow", function() {    
	});
	$('[id^=iconmenu]').removeClass("icon-selected");
	
	if ($( '#menuSubMiddleMedia' + id ).css("display")=="none"){
		if(showTrackOptions) {
			showSaveCurrentTrack();
			//getFavoritesList(current_track_id);
		}
		else {
			hideSaveCurrentTrack();
			//getFavoritesList();
		}
		$( '#menuSubMiddleMedia' + id ).slideDown( "slow", function() {});
		$( '#iconmenuSubMiddleMedia' + id ).addClass("icon-selected");
	}
	else {
		$( '#menuSubMiddleMedia' + id ).slideUp( "slow", function() { });
		$( '#iconmenuSubMiddleMedia' + id ).removeClass("icon-selected");
		
	}
	//$('#menuSubMiddleMedia' + id + ' input').first().focus();
	$('#addUrlAddress').focus();

};

function hideSaveCurrentTrack() {
	$('#trackOptions').hide();
	$('#saveCurrentPlaylist i').removeClass("fa-check-circle-o fa-circle-o").addClass("fa-check-circle-o");
	$('#saveCurrentTrack i').removeClass("fa-check-circle-o fa-circle-o").addClass("fa-circle-o");
	$('#saveCurrentPlaylist').click();
}

function showSaveCurrentTrack() {
	$('#trackOptions').show();
	$("#saveCurrentTrack").click();
	$('#saveCurrentPlaylist i').removeClass("fa-check-circle-o fa-circle-o").addClass("fa-circle-o");
	$('#saveCurrentTrack i').removeClass("fa-check-circle-o fa-circle-o").addClass("fa-check-circle-o");
}

function resetSaveCurrentOptions() {
	$('#saveCurrentPlaylist i').removeClass("fa-check-circle-o");
	$('#saveCurrentTrack i').removeClass("fa-check-circle-o");
	$('#saveCurrentPlaylist i').addClass("fa-circle-o");
	$('#saveCurrentTrack i').addClass("fa-circle-o");
}


function toggleSearchResults($id) {
	if ($( '#searchResults' + $id ).css("display")=="none"){
		$( '#searchResults' + $id ).slideDown( "slow", function() {});
		$( '#iconSearchResults' + $id ).addClass("icon-selected");
		$('#iconSearchResults' + $id).removeClass("icon-anchor");
	}
	else {
		$( '#searchResults' + $id ).slideUp( "slow", function() { });
		$('#iconSearchResults' + $id).removeClass("icon-selected");
		$('#iconSearchResults' + $id).addClass("icon-anchor");
	}
};

function toggleSearch() {
	$('#iconPlayerToggler').removeClass("icon-selected-main-menu");
	$('#iconVolumeToggler').removeClass("icon-selected-main-menu");
	$('#playerList').slideUp( "slow", function() {});
	$('#volumeArea').slideUp( "slow", function() { });
	if ($('#searchFormAll').css("display")=="none"){
		$('#searchFormAll').slideDown( "slow", function() {});
		$('#iconSearchToggler').addClass("icon-selected-main-menu");
		$('#search_string').focus();
	}
	else {
		$('#searchFormAll').slideUp( "slow", function() { });
		$('#iconSearchToggler').removeClass("icon-selected-main-menu");
		$('#iconSearchToggler').focus();
		}
};

function goSearch () {
	$('#searchFormAll').submit();
};

function toggleChangePlayer() {
	ajaxRequest('ajax-evaluate-status.php', evaluateVolume);
	$('#iconSearchToggler').removeClass("icon-selected-main-menu");
	$('#iconVolumeToggler').removeClass("icon-selected-main-menu");
	$('#searchFormAll').slideUp( "slow", function() {});
	$('#volumeArea').slideUp( "slow", function() { });
	if ($('#playerList').css("display")=="none"){
		$('#playerList').slideDown( "slow", function() {});
		$('#iconPlayerToggler').addClass("icon-selected-main-menu");
	}
	else {
		$('#playerList').slideUp( "slow", function() { });
		$('#iconPlayerToggler').removeClass("icon-selected-main-menu");
		}
};

function toggleVolume() {
	ajaxRequest('ajax-evaluate-status.php', evaluateVolume);
	$('#iconSearchToggler').removeClass("icon-selected-main-menu");
	$('#iconPlayerToggler').removeClass("icon-selected-main-menu");
	$('#searchFormAll').slideUp( "slow", function() {});
	$('#playerList').slideUp( "slow", function() {});
	if ($('#volumeArea').css("display")=="none"){
		$('#volumeArea').slideDown( "slow", function() {});
		$('#iconVolumeToggler').addClass("icon-selected-main-menu");
	}
	else {
		$('#volumeArea').slideUp( "slow", function() { });
		$('#iconVolumeToggler').removeClass("icon-selected-main-menu");
		}
};

function togglePlayedHistory() {
	$('#playedHistory').slideUp("slow", function() {    
	});
	if ($('#playedHistory').css("display")=="none"){
		$('#playedHistory').slideDown( "slow", function() {});
		$('#playedCal i').addClass("icon-selected");
		//console.log('#menu-icon' + $id);
	}
	else {
		$('#playedHistory').slideUp( "slow", function() { });
		$('#playedCal i').removeClass("icon-selected");
	}
};



function showSpinnerImg(targetImg, spinnerImg) {
	targetImg.style.width = $("#image_container").width() + 'px';
	targetImg.style.height = $("#image_container").width() + 'px';
	spinnerImg.spin(targetImg);
};


function showSpinner() {
	target.style.width = $( window ).width();
	target.style.height = $( window ).height();
	target.style.marginTop = "-" + ($( window ).height())/2 + "px";
	target.style.marginLeft = "-" + ($( window ).width())/2 + "px";
	target.style.display = "block";
	spinner.spin(target);
};

function hideSpinner() {
	spinner.stop();
	target.style.display = "none";
};


function toggleInsert(state,i) {
	if (state=='on') {
		$("[id^='menu-insert-div']").show();
		$("[id^='menu-icon-div']").hide();
		$("#menu-insert-div" + i).hide();
		$("#menu-icon-div" + i).show();
		$("#menu-icon" + i).removeClass("fa-bars").addClass("fa-undo");
		$('#menu-icon-div' + i).attr('onclick','').unbind('click');
		$('#menu-icon-div' + i).click(function(){
			toggleInsert('off',i);
		})
		fromPosition = i;
	}
	else {
		$("[id^='menu-insert-div']").hide();
		$("[id^='menu-icon-div']").show();
		$("#menu-icon" + i).removeClass("fa-undo").addClass("fa-bars");
		$('#menu-icon-div' + i).attr('onclick','').unbind('click');
		$('#menu-icon-div' + i).click(function(){
			toggleMenuSub(i);
		})
	}
	
}

function moveTrack(toPosition,i,isMoveToTop){
	if (i >= 0) fromPosition = i;
	
	if (toPosition == 'playNext') {
		showSpinner();
		ajaxRequest('play.php?action=moveTrack&fromPosition=' + fromPosition + '&toPosition=' + toPosition + '&isMoveToTop=' + isMoveToTop + '&menu=playlist',evaluateListpos);
	}
	else {	
		if (fromPosition - toPosition == 1) toggleInsert('off',toPosition+1);
		else toggleInsert('off');
		console.log('fromPosition: ' + fromPosition + ' toPosition: ' + toPosition);
		if (fromPosition != toPosition) {
			if (isMoveToTop) { //move to top
				/* $('#track' + fromPosition).insertBefore('#track' + toPosition);
				$('#track-line' + fromPosition).insertAfter('#track' + fromPosition);
				$('#track-menu' + fromPosition).insertAfter('#track-line' + fromPosition); */
				showSpinner();
				ajaxRequest('play.php?action=moveTrack&fromPosition=' + fromPosition + '&toPosition=' + toPosition + '&isMoveToTop=' + isMoveToTop + '&menu=playlist',evaluateListpos);
			}
			else if (fromPosition - toPosition != 1){
				/* $('#track' + fromPosition).insertAfter('#track-menu' + toPosition);
				$('#track-line' + fromPosition).insertAfter('#track' + fromPosition);
				$('#track-menu' + fromPosition).insertAfter('#track-line' + fromPosition); */
				showSpinner();
				ajaxRequest('play.php?action=moveTrack&fromPosition=' + fromPosition + '&toPosition=' + toPosition + '&isMoveToTop=' + isMoveToTop + '&menu=playlist',evaluateListpos);
			}
		}
	}
}


function arrangeFavItem(toPosition,i,isMoveToTop,favorite_id){
	if (i >= 0) fromPosition = i;
	
	if (toPosition < 0) removeItem = true 
	else removeItem = false;
	
	toggleInsert('off');
	console.log('fromPosition: ' + fromPosition + ' toPosition: ' + toPosition);
	if (fromPosition != toPosition) {
		if (removeItem) {
			action = 'removeItem';
			//console.log ('remove ' + i);
		}
		else {
			action = 'moveItem';
			if (isMoveToTop) { //move to top
				$('#track' + fromPosition).insertBefore('#track' + toPosition);
				$('#track-line' + fromPosition).insertAfter('#track' + fromPosition);
				$('#track-menu' + fromPosition).insertAfter('#track-line' + fromPosition);
				//showSpinner();
				
			}
			else if (fromPosition - toPosition != 1){
				$('#track' + fromPosition).insertAfter('#track-menu' + toPosition);
				$('#track-line' + fromPosition).insertAfter('#track' + fromPosition);
				$('#track-menu' + fromPosition).insertAfter('#track-line' + fromPosition);
				//showSpinner();
				//ajaxRequest('play.php?action=moveTrack&fromPosition=' + fromPosition + '&toPosition=' + toPosition + '&isMoveToTop=' + isMoveToTop + '&menu=playlist',evaluateListpos);
			}
		}
		
		var size = $tileSize;
		var request = $.ajax({  
			url: "ajax-favorite-arrange.php",  
			type: "POST",  
			data: { action : action,
					favorite_id : favorite_id,
					toPosition: toPosition,
					fromPosition: fromPosition,
					isMoveToTop: isMoveToTop},  
			dataType: "html"
		}); 
		
		request.done(function( data ) {  
			$( "#favoriteList" ).html( data );
			//calcTileSize();
			//hideSpinner();
		}); 
		
		request.fail(function( jqXHR, textStatus ) {  
			alert( "Request failed: " + textStatus );	
		}); 
	}
};


function arrangeFavItemMPD(toPosition,i,isMoveToTop,favorite_id){
	if (i >= 0) fromPosition = i;
	
	
	if (toPosition < 0) removeItem = true 
	else removeItem = false;
	
	toggleInsert('off');
	console.log('fromPosition: ' + fromPosition + ' toPosition: ' + toPosition);
	if (fromPosition != toPosition) {
		if (removeItem) {
			action = 'removeItem';
			//console.log ('remove ' + i);
		}
		else {
			action = 'moveItem';
		}
		
		var size = $tileSize;
		var request = $.ajax({  
			url: "ajax-favorite-arrange-MPD.php",  
			type: "POST",  
			data: { action : action,
					favorite_id : favorite_id,
					toPosition: toPosition,
					fromPosition: fromPosition,
					isMoveToTop: isMoveToTop},  
			dataType: "html"
		}); 
		
		request.done(function( data ) {  
			$( "#favoriteList" ).html( data );
			//calcTileSize();
			//hideSpinner();
		}); 
		
		request.fail(function( jqXHR, textStatus ) {  
			alert( "Request failed: " + textStatus );	
		}); 
	}
};


function playlistSave(action, id, saveTrackId, host, port, track_mpd_url) {
	
	var saveTrack = 'false';
	
	if (id == '') { //request from NowPlaying
		if ($('#saveCurrentTrack i').hasClass('fa-check-circle-o')) saveTrack = 'true';
	}
	else { //request from list of tracks
		/* id = id.split("-");
		saveTrackId = id[1];
		id = "-" + id[1]; */
		
		dashPos = id.indexOf("-");
		l = id.length;
		saveTrackId = id.substr(dashPos + 1, l - dashPos);
		id = "-" + saveTrackId;
		saveTrack = 'true';
		//console.log("saveTrackId: " + saveTrackId);
	}
	
	switch(action){
		case "SaveAs":
			$('[id="playlistSaveAs' + id + '"] > i').removeClass('fa-floppy-o').addClass('fa-cog fa-spin');
			break;
		case "AddTo":
			$('[id="playlistAddTo' + id + '"] > i').removeClass('fa-plus-circle').addClass('fa-cog fa-spin');
			break;
	}
	
	var request = $.ajax({
			type: "GET",
			url: "ajax-playlist-save.php",
			data: { 'action': action, 'name': $('#savePlaylistAsName' + id).val(), 'saveAs': $('#savePlaylistAddTo' + id).val(), 'comment': $('#savePlaylistComment' + id).val(), 'host': host, 'port': port, 'saveTrackId': saveTrackId, 'saveTrack': saveTrack, 'track_mpd_url' : track_mpd_url},
			dataType : 'json'
	});
	
	request.done(function(json) {
				if(json.action_status && !json.not_compatible) {
							$('[id="playlist' + action + id + '"] > i').removeClass('fa-cog fa-spin icon-selected').addClass('fa-check-square');
							//toggleStarSub(-1);
				} 
				else {
					$('[id="playlist' + action + id + '"] > i').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-circle icon-nok');
				}
				switch(action){
					case "SaveAs":
						if(json.action_status && !json.not_compatible) {
							$('select[id^="savePlaylistAddTo"]').empty();
							$('select[id^="savePlaylistAddTo"]').html(json.select_options);
							setTimeout(function(){
							  $('[id="playlistSaveAs' + id + '"] > i').removeClass('fa-check-square icon-ok').addClass('fa-floppy-o');
							}, 2000);
						} 
						else {
							setTimeout(function(){
							  $('[id="playlistSaveAs' + id + '"] > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-floppy-o');
							}, 2000);
						}
						break;
					case "AddTo":
						if(json.action_status && !json.not_compatible) {
							setTimeout(function(){
							  $('[id="playlistAddTo' + id + '"] > i').removeClass('fa-check-square icon-ok').addClass('fa-plus-circle');
							}, 2000);
						} 
						else {
							setTimeout(function(){
							  $('[id="playlistAddTo' + id + '"] > i').removeClass('fa-exclamation-circle icon-nok').addClass('fa-plus-circle');
							}, 2000);
						}
						break;
				}
				//getFavoritesList(saveTrackId);
	});
};



function setFavorite(data) {
	if (data.action == "add") {
		$("#save_favorite_star-" + data.track_id).removeClass("fa-cog fa-spin icon-selected").addClass("fa-star");
		$("#favorite_star-" + data.track_id).removeClass("fa-star-o").addClass("fa-star");
		$("#addToFavorite_txt-" + data.track_id).text("Remove from ");
	}
	else if (data.action == "remove") {
		$("#save_favorite_star-" + data.track_id).removeClass("fa-cog fa-spin icon-selected").addClass("fa-star-o");
		$("#favorite_star-" + data.track_id).removeClass("fa-star").addClass("fa-star-o");
		$("#addToFavorite_txt-" + data.track_id).text("Add to ");
		//remove row from 'Favorites tracks of' list in artist search:
		$("#fav_" + data.track_id).hide();
		
	}
	
	toggleStarSub(-1);
};

function setBlacklist(data) {
	if (data.action == "add") {
		$("#blacklist_star-" + data.track_id).removeClass("fa-cog fa-spin icon-selected").addClass("fa-star-o");
		$("#blacklist-star-bg" + data.track_id).addClass("blackstar-selected blackstar");
		$("#blacklist-star-bg" + data.track_id + "_fav").addClass("blackstar-selected blackstar");
		$("#blacklist-star-bg-sub" + data.track_id).addClass("blackstar-selected");
		$("#blacklist-star-bg-sub" + data.track_id + "_fav").addClass("blackstar-selected");
		$("#addToBlacklist_txt-" + data.track_id).text("Remove from ");
	}
	else if (data.action == "remove") {
		$("#blacklist_star-" + data.track_id).removeClass("fa-cog fa-spin icon-selected").addClass("fa-star-o");
		$("#blacklist-star-bg" + data.track_id).removeClass("blackstar-selected blackstar");
		$("#blacklist-star-bg" + data.track_id + "_fav").removeClass("blackstar-selected blackstar");
		$("#blacklist-star-bg-sub" + data.track_id).removeClass("blackstar-selected");
		$("#blacklist-star-bg-sub" + data.track_id + "_fav").removeClass("blackstar-selected");
		$("#addToBlacklist_txt-" + data.track_id).text("Add to ");
	}
	
	toggleStarSub(-1);
};

function addFavSubmenuActions() {
	$('[id^=playlistAddTo-]').off("click");
	$('[id^=playlistAddTo-]').on("click",function(){
		playlistSave('AddTo',$(this).attr("id"),'','','');
	});
	
	$('[id^=playlistSaveAs-]').off("click");
	$('[id^=playlistSaveAs-]').on("click",function(){
			playlistSave('SaveAs',$(this).attr("id"),'','','');
		});

	$('[id^=savePlaylistAsName-]').keypress(function(event) {
		  if ( event.which == 13 ) {
			var track_id = $(this).attr('id');
			track_id = track_id.split('-');
			tid = track_id[1];
			 $('#playlistSaveAs-' + tid).click();
		  }
	});

	
	$('[id^=savePlaylistComment-]').keypress(function(event) {
		  if ( event.which == 13 ) {
			var track_id = $(this).attr('id');
			track_id = track_id.split('-');
			tid = track_id[1];
			 $('#playlistSaveAs-' + tid).click();
		  }
	});

	$("[id^=track_addToFavorite]").off("click");
	$("[id^=track_addToFavorite]").on("click",function() {
			var action = '';
			var track_id = $(this).attr('id');
			track_id = track_id.split('-');
			tid = track_id[1];
			
			if ($("#save_favorite_star-" + tid).hasClass("fa-star")) {
				action = 'remove';
				$("#save_favorite_star-" + tid).removeClass('fa-star').addClass('fa-cog fa-spin icon-selected');
				}
			else {
				action = 'add';
				$("#save_favorite_star-" + tid).removeClass('fa-star-o').addClass('fa-cog fa-spin icon-selected');
			}
			ajaxRequest('ajax-favorite.php?action=' + action + '&track_id=' + tid, setFavorite);
			
		});

	$("[id^=track_addToBlacklist]").off("click");
	$("[id^=track_addToBlacklist]").on("click",function() {
			var action = '';
			var track_id = $(this).attr('id');
			track_id = track_id.split('-');
			tid = track_id[1];
			console.log('tid=' + tid);
			
			if ($("#blacklist-star-bg-sub" + tid).hasClass("blackstar-selected")) {
				action = 'remove';
				$("#blacklist_star-" + tid).removeClass('fa-star-o').addClass('fa-cog fa-spin icon-selected');
				}
			else {
				action = 'add';
				$("#blacklist_star-" + tid).removeClass('fa-star-o').addClass('fa-cog fa-spin icon-selected');
			}
		
			ajaxRequest('ajax-blacklist.php?action=' + action + '&track_id=' + tid, setBlacklist);
			
		});
};

function doPlayAction(action, filepath, track_id, playAfterInsert, doFunction) {
	$.ajax({
			type: "GET",
			url: "play.php",
			data: { 'action': action, 'filepath': filepath, 'track_id' : track_id, 'playAfterInsert' : playAfterInsert },
			dataType : 'json',
			success : function(json) {
				doFunction(json);
			},
			error : function(jqXHR, textStatus, error) {
				/* var http = new XMLHttpRequest();
				http.send(jqXHR.responseText); */
		
				var timeOut = 2000;
				$('[id="add_' + track_id +'"]').removeClass('fa-cog fa-spin icon-selected').addClass('fa-exclamation-triangle icon-nok');
			
				setTimeout(function(){
					$('[id="add_' + track_id +'"]').removeClass('fa-exclamation-triangle icon-nok').addClass('fa-plus-circle');
				}, timeOut);
				
			}	
		});
};


function setAnchorClick() {
	$('a').click(function(){
		if (this.hasAttribute('id')) {
			if ($(this).attr('id').indexOf("a_play_track") > -1) {
				var id = $(this).attr('id').replace("a_play_track","");
				$("#menu-icon" + id).removeClass('fa-bars').addClass('fa-cog fa-spin icon-selected');
			}
			if ($(this).attr('id').indexOf("a_album") > -1) {
				var id = $(this).attr('id').replace("a_album","");
				$("#menu-icon" + id).removeClass('fa-bars').addClass('fa-cog fa-spin icon-selected');
			}
			if ($(this).attr('id').indexOf("fav_play_track") > -1) {
				var id = $(this).attr('id').replace("fav_play_track","");
				$("#add_" + id).removeClass('fa-plus-circle').addClass('fa-cog fa-spin icon-selected');
			}
		}
		$(this).find('> i[id^="play_"]').removeClass('fa-play-circle-o').addClass('fa-cog fa-spin icon-selected');
		$(this).find('> i[id^="add_"]').removeClass('fa-plus-circle').addClass('fa-cog fa-spin icon-selected');
		$(this).find('> i[id^="insertPlay_"]').removeClass('fa-play-circle').addClass('fa-cog fa-spin icon-selected');
		$(this).find('> i[id^="insert_"]').removeClass('fa-indent').addClass('fa-cog fa-spin icon-selected');
		$(this).find('> i[id^="playTo_"]').removeClass('fa-share-square-o').addClass('fa-cog fa-spin icon-selected');
		$(this).find('> i[id^="randomPlay"]').removeClass('fa-random').addClass('fa-cog fa-spin icon-selected');
		
	})
};

function getFavoritesList(track_id, track_mpd_url){
	var request = $.ajax({  
	url: "ajax-favorite-list.php",  
	type: "GET",  
	data: { 
		file : 'true',
		track_id : track_id,
		track_mpd_url : track_mpd_url
	},  
	dataType: "html"
	}); 

	request.done(function(data) {  
	$("#savePlaylistAddTo").html(data);
	}); 
};

//escapeRegExp() from https://stackoverflow.com/questions/3446170/escape-string-for-use-in-javascript-regex
function escapeRegExp(string) {
	return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
}