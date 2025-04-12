<h1>&nbsp;<span id="suggestedNewTidal">Suggested new albums from</span> <a href="index.php?action=viewTidal">Tidal</a> <a id="suggestedNewLink" href="index.php?action=viewMoreFromTidal_v2&type=general_list&apiPath=home%2Fpages%2FNEW_ALBUM_SUGGESTIONS%2Fview-all&moduleName=Suggested new albums for you">(more...)</a></h1>
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
  
  request.fail(function(data) {
			$( "#new_tidal" ).html('<div style="line-height: initial;">Error loading albums from Tidal.</div>');
	});
	
  function changeSuggested(dataApiPath){
    $("#suggestedNewTidal").html("Suggested new albums for you from ");
    $("#suggestedNewLink").attr('href','index.php?action=viewMoreFromTidal&type=album_list&apiPath=' + encodeURIComponent(dataApiPath));
  }

	</script>
	<div class="full" id="new_tidal">
		<div style="display: grid; height: 100%;">
			<span id="albumsLoadingIndicator" style="margin: auto;">
				<i class="fa fa-cog fa-spin icon-small"></i> <span class="add-info-left">Loading albums from Tidal...</span>
			</span>
		</div>
	</div>