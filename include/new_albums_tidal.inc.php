<h1>&nbsp;Suggested new albums from <a href="index.php?action=viewTidal">Tidal</a> <a href="index.php?action=viewNewFromTidal&type=suggested_new">(more...)</a></h1>
	<script>
		calcTileSize();
		var size = $tileSize;
		var request = $.ajax({  
		url: "ajax-tidal-new-albums.php",  
		type: "POST",
		data: { type: "suggested_new", tileSize : size, limit : 10, offset : 0 },
		dataType: "html"
		}); 

	request.done(function(data) {
		if (data) {
			$( "#new_tidal" ).html(data);
		}
		else {
			$( "#new_tidal" ).html('<div style="line-height: initial;">Error loading albums from Tidal.</div>');
		}
	});
	
	</script>
	<div class="full" id="new_tidal">
		<div style="display: grid; height: 100%;">
			<span id="albumsLoadingIndicator" style="margin: auto;">
				<i class="fa fa-cog fa-spin icon-small"></i> <span class="add-info-left">Loading albums from Tidal...</span>
			</span>
		</div>
	</div>