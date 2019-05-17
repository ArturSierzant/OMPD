import tidalapi
import subprocess
import sys
import json

if len(sys.argv) < 6:
	print("Missing some command line arguments")
	exit()

user = sys.argv[1] 
password = sys.argv[2] 
token = sys.argv[3] 
field = sys.argv[4] 
value = sys.argv[5] 
jsonResponse = []
albums = []
artists = []
i = 1
session = tidalapi.Session()
session.login(user, password, token)

if field == 'artist':
	searchResults = session.search(field, value)
	if searchResults.artists:
		artist_id = searchResults.artists[0].id
		artist_bio = session.get_artist_bio(artist_id)
		artist = searchResults.artists[0].name
		albums_list = session.get_artist_albums(artist_id)
		for album in albums_list:
			item = {"album_id" : album.id, "album_title" : album.name, "album_date" : str(album.release_date), "album_duration" : str(album.duration)}
			albums.append(item)
		jsonResponse = { "artist" : artist, "artist_id" : artist_id, "artist_bio" : artist_bio, "albums" : albums }

elif field == 'artists':
	searchResults = session.search('artist', value)
	if searchResults.artists:
		for arts in searchResults.artists:
			item = {"artist_id" : arts.id, "artist" : arts.name}
			artists.append(item)
		jsonResponse = {"artists" : artists} 
	
elif field == 'albumTracks':
	album_id = value.replace("'","")
	tracks_list = session.get_album_tracks(album_id)
	for track in tracks_list:
#		track_url = session.get_media_url(track.id)
		item = {"track_id" : str(track.id), "track_number": str(track.track_num), "track_title" : track.name, "track_duration" : str(track.duration), "disc_number" : str(track.disc_num), "track_artist" : track.artist.name}
#		, "track_url" : track_url}
		jsonResponse.append(item)

elif field == 'track':
	searchResults = session.search(field, value)
	for track in searchResults.tracks:
		item = {"track_id" : str(track.id), "track_number": str(track.track_num), "track_title" : track.name, "track_duration" : str(track.duration), "disc_number" : str(track.disc_num), "track_artist" : track.artist.name}
		jsonResponse.append(item)

elif field == 'album':
	album_id = value.replace("'","")
	album = session.get_album(album_id)
	item = {"album_id" : album.id, "album_title" : album.name, "album_date" : str(album.release_date), "album_duration" : str(album.duration), "artists" : (album.artists[0])}
	jsonResponse.append(item)

elif field == 'trackURL':
	searchResults = session.get_media_url(value)	
	item = {"track_id" : value, "trackURL": str(searchResults)}
	jsonResponse.append(item)

elif field == 'all':
	searchResults = session.search_all(value)	
	jsonResponse.append(searchResults)

print(json.dumps(jsonResponse))


#searchResults = session.get_album_searchResults(albums[0].id)
#tid = searchResults[0].id
#print (tid)
#print(session.get_media_url(track_id=searchResults[0].id))
#subprocess.call("mpc add tidal://track/" + str(tid), shell=True)	