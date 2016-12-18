var ompd = {
    charset : "{{ ompd.NJB_DEFAULT_CHARSET }}",
    escapeFunc : "{{ (ompd.NJB_DEFAULT_CHARSET == 'UTF-8') ? 'encodeURIComponent' : 'escape' }}",
    urls: {
        json: {
            albumArtist : "json.php?action=suggestAlbumArtist&artist=",
            albumTitle :  "json.php?action=suggestAlbumTitle&title=",
            trackArtist : "json.php?action=suggestTrackArtist&artist=",
            trackTitle :  "json.php?action=suggestTrackTitle&title=",
            quickSearch : "json.php?action=suggestTrackTitle&title="
        }
    }
}
