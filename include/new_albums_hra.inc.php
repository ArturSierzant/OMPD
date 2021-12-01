<h1>&nbsp;New albums from <a href="index.php?action=viewHRA">HighResAudio</a> <a href="index.php?action=viewNewFromHRA&categoryName=New%20albums&prefix=/HIGHRES%20AUDIO/Musicstore/">(more...)</a></h1>
	<script>
		calcTileSize();
		var size = $tileSize;
		var request = $.ajax({  
		url: "ajax-hra-new-albums.php",  
		type: "POST",
		data: { categoryName: "New albums", prefix: "/HIGHRES AUDIO/Musicstore/", tileSize : size, limit : 15, offset : 0 },
		dataType: "html"
		}); 

	request.done(function(data) {
		if (data) {
			$( "#new_HRA" ).html(data);
		}
		else {
			$( "#new_HRA" ).html('<div style="line-height: initial;">Error loading albums from HRA.</div>');
		}
	});
	
	</script>
	<div class="full" id="new_HRA">
		<div style="display: grid; height: 100%;">
			<span id="albumsLoadingIndicator" style="margin: auto;">
				<i class="fa fa-cog fa-spin icon-small"></i> <span class="add-info-left">Loading albums from HighResAudio...</span>
			</span>
		</div>
	</div>