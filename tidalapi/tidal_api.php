<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2019 Artur Sierzant                            |
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
//  | MERcurlANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
//  | GNU General Public License for more details.                           |
//  |                                                                        |
//  | You should have received a copy of the GNU General Public License      |
//  | along with this program.  If not, see <http://www.gnu.org/licenses/>.  |
//  +------------------------------------------------------------------------+


class TidalAPI {
	public $username;
	public $password;
	public $token;
	public $audioQuality = "HIGH";
	public $curl;
	public $fixSSLcertificate = false;
	protected $sessionId;
	protected $countryCode;
	protected $userId;
	
	const AUTH_URL = "https://api.tidalhifi.com/v1/login/username";
	const API_URL = "https://api.tidalhifi.com/v1/";
	const RESOURCES_URL = "https://resources.tidal.com/images/";
	
	public function __construct(){
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		//to fix "SSL certificate problem: unable to get local issuer certificate" under Windows uncomment below lines or use function fixSSLcertificate():
		//curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
		//curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
	}
	
	public function __destruct(){
		curl_close($this->curl);
	}
	
	//fix "SSL certificate problem: unable to get local issuer certificate" under Windows
	function fixSSLcertificate(){
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
	}
	
	function connect() {
		curl_setopt($this->curl, CURLOPT_URL,self::AUTH_URL);
		curl_setopt($this->curl, CURLOPT_POST, 1);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS,
			http_build_query(array('username' => $this->username,'password' => $this->password,'token' => $this->token)));
	
		$server_output = curl_exec($this->curl);
		if (!$server_output) {
			$errors = array();
			$errors['return'] = 1;
			$errors['error'] = "Error code: " . curl_errno($this->curl) . ": " . curl_error($this->curl);
			return($errors);
		}
		else {
			$res_json = json_decode($server_output, true);
			if ($res_json["sessionId"] && $res_json["countryCode"] && $res_json["userId"]) {
				$this->sessionId = $res_json["sessionId"];
				$this->countryCode = $res_json["countryCode"];
				$this->userId = $res_json["userId"];
				return true;
			}
			else {
				$errors = array();
				$errors['return'] = 1;
				$errors['error'] = $res_json["userMessage"];
				return $errors;
			}
		};
	}
	
	function search($type, $query, $limit = 50) {
		$query = urlencode($query);
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "search/" . $type . "?sessionId=" . $this->sessionId . "&query=" . $query . "&countryCode=" . $this->countryCode . "&limit=" . $limit);
		return $this->request();
	}
	
	function searchAll($query, $limit = 50) {
		$query = urlencode($query);
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "search?sessionId=" . $this->sessionId . "&query=" . $query . "&countryCode=" . $this->countryCode . "&limit=" . $limit . "&types=ARTISTS,ALBUMS,TRACKS,PLAYLISTS");
		return $this->request();
	}
	
	function getTrack($track_id) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "tracks/" . $track_id . "?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode);
		return $this->request();
	}
	
	function getAlbum($album_id) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "albums/" . $album_id . "?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode);
		return $this->request();
	}
	
	function getAlbumTracks($album_id) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "albums/" . $album_id . "/tracks?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode);
		return $this->request();
	}
	
	function getArtistAll($artist_id) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "pages/artist?artistId=" . $artist_id . "&sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&deviceType=BROWSER");
		return $this->request();
	}
	
	function getArtistAlbums($artist_id, $limit = 50) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "artists/" . $artist_id . "/albums?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&limit=" . $limit);
		return $this->request();
	}
	
	function getArtistEPsAndSingles($artist_id, $limit = 50) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "artists/" . $artist_id . "/albums?filter=EPSANDSINGLES&sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&limit=" . $limit);
		return $this->request();
	}
	
	function getArtistBio($artist_id) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "artists/" . $artist_id . "/bio?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&limit=" . $limit);
		return $this->request();
	}
	
	function getRelatedArtists($artist_id) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "pages/artist?artistId=" . $artist_id . "&sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&deviceType=BROWSER");
		$artistAll = $this->request();
		foreach ($artistAll["rows"] as $module){
			if ($module["modules"][0]["type"]=='ARTIST_LIST') {
				return $module["modules"][0]["pagedList"]["items"];
			};
		}
	}
	
	function getNewAlbums($limit = 100) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "pages/show_more_featured_albums?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&limit=" . $limit . "&deviceType=BROWSER");
		$res = $this->request();
		$s = array_search("featured-new",array_column($res["rows"][0]["modules"][0]["tabs"],"key"));
		return $res["rows"][0]["modules"][0]["tabs"][$s]["pagedList"]["items"];
	}
	
	function getFeatured() {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "featured/new/albums?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode);
		return $this->request();
	}
	
	function getStreamURL($track_id) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "tracks/" . $track_id . "/streamUrl?soundQuality=" . $this->audioQuality . "&sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode);
		return $this->request();
	}
	
	function getUserPlaylists() {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "users/" . $this->userId . "/playlists?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&limit=" . $limit);
		return $this->request();
	}
	
	function getUserPlaylistTracks($playlist_id, $limit = 1000) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "playlists/" . $playlist_id . "/tracks?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&limit=" . $limit);
		return $this->request();
	}
	
	static function artistPictureToURL($pic) {
		$pic = str_replace("-","/",$pic);
		$pic = self::RESOURCES_URL . $pic . '/480x480.jpg';
		//$pic = self::RESOURCES_URL . $pic . '/640x428.jpg';
		return $pic;
	}
	
	static function artistPictureWToURL($pic) {
		$pic = str_replace("-","/",$pic);
		//$pic = self::RESOURCES_URL . $pic . '/480x480.jpg';
		$pic = self::RESOURCES_URL . $pic . '/640x428.jpg';
		return $pic;
	}
	
	function request() {
		curl_setopt($this->curl, CURLOPT_POST, 0);
		$server_output = curl_exec($this->curl);
		$res_json = json_decode($server_output, true);
		return $res_json;
	}
	
}

?>