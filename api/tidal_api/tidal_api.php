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
	public $sessionId;
	public $countryCode;
	public $userId;
  private $trials = 1;
	
	const AUTH_URL = "https://api.tidalhifi.com/v1/login/username";
	const API_URL = "https://api.tidalhifi.com/v1/";
	const RESOURCES_URL = "https://resources.tidal.com/images/";
  const MAX_CONNECTION_REPEAT = 5;
	
	public function __construct(){
    //$this->trials = 0;
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		//to fix "SSL certificate problem: unable to get local issuer certificate" under Windows uncomment below lines or use function fixSSLcertificate():
		//curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
		//curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
	}
	
	public function __destruct(){
    //$this->logout($this->sessionId);
		curl_close($this->curl);
	}
	
	//fix "SSL certificate problem: unable to get local issuer certificate" under Windows
	function fixSSLcertificate(){
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
	}
	
	function connect($sessionId = '', $countryCode = '') {
    if ($sessionId && $countryCode){
      $this->sessionId = $sessionId;
      $this->countryCode = $countryCode;
      //$this->userId = $res_json["userId"];
      return true;
    }
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
      elseif ($res_json["userMessage"] == 'Server at capacity' && $this->trials < self::MAX_CONNECTION_REPEAT){
        usleep(100000);
        $this->trials++;
        return $this->connect();
      }
			else {
				$errors = array();
				$errors['return'] = 1;
				$errors['error'] = $res_json["userMessage"];
				$errors['trials'] = $this->trials;
				return $errors;
			}
		};
  }
  function logout($sessionId, $countryCode='') {
    curl_setopt($this->curl, CURLOPT_URL,self::API_URL . 'logout');
    curl_setopt($this->curl, CURLOPT_POST, 1);
    curl_setopt($this->curl, CURLOPT_POSTFIELDS,
    http_build_query(array('sessionId' => $sessionId,'countryCode' => $countryCode)));
    $server_output = curl_exec($this->curl);
    return $server_output;
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
		
	function getAlbumInfo($album_id) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "pages/album?albumId=" . $album_id . "&deviceType=BROWSER&sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode);
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
	
	function getArtistTopTracks($artist_id, $limit = 10) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "artists/" . $artist_id . "/toptracks?filter=ALL&sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&limit=" . $limit);
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
    return $res;
    $s = array_search("featured-new",array_column($res["rows"][0]["modules"][0]["tabs"],"key"));
		return $res["rows"][0]["modules"][0]["tabs"][$s]["pagedList"]["items"];
	}
	
	function getFeatured($limit = 100, $offset = 0) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "featured/new/albums?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&limit=" . $limit . "&offset=" . $offset);
		return $this->request();
	}	
  
  function getFeaturedRecommended($limit = 100, $offset = 0) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "featured/recommended/albums?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&limit=" . $limit . "&offset=" . $offset);
		return $this->request();
	}
  
	function getFeaturedTop($limit = 100, $offset = 0) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "featured/top/albums?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&limit=" . $limit . "&offset=" . $offset);
		return $this->request();
	}
	
  function getFeaturedLocal($limit = 100, $offset = 0) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "featured/local/albums?sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode . "&limit=" . $limit . "&offset=" . $offset);
		return $this->request();
	}
	  
  function getSuggestedNew($limit = 50, $offset = 0, $getMore = false) {
    $res = $this->getHomePage();
    foreach($res['rows'] as $key => $row){
      if ($row['modules'][0]['title'] == 'Suggested New Albums') {
        if ($getMore) {
          $apiPath = $res['rows'][$key]['modules'][0]['pagedList']['dataApiPath'];
          $more = $this->getByApiPath($limit, $offset, $apiPath);
          return $more;
        }
        else {
          return ($res['rows'][$key]['modules'][0]['pagedList']);
        }
      }
    }
		return false;
	}
	
  function getNewForYou($limit = 50, $offset = 0, $getMore = false) {
		$res = $this->getHomePage();
    foreach($res['rows'] as $key => $row){
      if ($row['modules'][0]['title'] == 'New Releases For You') {
        if ($getMore) {
          $apiPath = $res['rows'][$key]['modules'][0]['pagedList']['dataApiPath'];
          $more = $this->getByApiPath($limit, $offset, $apiPath);
          return $more;
        }
        else {
          return ($res['rows'][$key]['modules'][0]['pagedList']);
        }
      }
    }
		return false;
	}
  
  function getSuggestedForYou($limit = 50, $offset = 0, $getMore = false) {
		$res = $this->getExplorePage();
    foreach($res['rows'] as $key => $row){
      if ($row['modules'][0]['title'] == 'Suggested Albums for You') {
        if ($getMore) {
          $apiPath = $res['rows'][$key]['modules'][0]['pagedList']['dataApiPath'];
          $more = $this->getByApiPath($limit, $offset, $apiPath);
          return $more;
        }
        else {
          return ($res['rows'][$key]['modules'][0]['pagedList']);
        }
      }
    }
		return false;
	}

  function getSuggestedArtistsForYou($limit = 50, $offset = 0, $getMore = false) {
		$res = $this->getExplorePage();
    foreach($res['rows'] as $key => $row){
      if ($row['modules'][0]['title'] == 'Suggested Artists for You') {
        if ($getMore) {
          $apiPath = $res['rows'][$key]['modules'][0]['pagedList']['dataApiPath'];
          $more = $this->getByApiPath($limit, $offset, $apiPath);
          return $more;
        }
        else {
          return ($res['rows'][$key]['modules'][0]['pagedList']);
        }
      }
    }
		return false;
	}
  
  function getSuggestedNewTracks($limit = 50, $offset = 0, $getMore = false) {
		$res = $this->getHomePage();
    foreach($res['rows'] as $key => $row){
      if ($row['modules'][0]['title'] == 'Suggested New Tracks') {
        if ($getMore) {
          $apiPath = $res['rows'][$key]['modules'][0]['pagedList']['dataApiPath'];
          $more = $this->getByApiPath($limit, $offset, $apiPath);
          return $more;
        }
        else {
          return ($res['rows'][$key]['modules'][0]['pagedList']);
        }
      }
    }
		return false;
	}

  function getByApiPath($limit = 50, $offset = 0, $apiPath) {
    if ($limit > 50) $limit = 50; //Tidal API limitation
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . $apiPath ."?sessionId=" . $this->sessionId . "&locale=en_US&deviceType=BROWSER&countryCode=" . $this->countryCode . "&limit=" . $limit . "&offset=" . $offset);
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
  
	function getHomePage() {
		//curl_setopt($this->curl, CURLOPT_URL, self::API_URL . " pages/home?locale=en_US&deviceType=DESKTOP&sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode);
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . " pages/home?locale=en_US&deviceType=BROWSER&sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode);
		return $this->request();
	}
	
  function getExplorePage() {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . " pages/explore?locale=en_US&deviceType=BROWSER&sessionId=" . $this->sessionId . "&countryCode=" . $this->countryCode);
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
	
	static function albumCoverToURL($pic,$quality = 'hq') {
		if (!$pic) {
			return false;
		}
		$pic = str_replace("-","/",$pic);
		if ($quality == 'hq') {
			$pic = self::RESOURCES_URL . $pic . '/1280x1280.jpg';
		}
		else {
			$pic = self::RESOURCES_URL . $pic . '/320x320.jpg';
		}
		return $pic;
	}
	
	function request() {
		curl_setopt($this->curl, CURLOPT_POST, 0);
    $server_output = curl_exec($this->curl);
    $res_json = json_decode($server_output, true);
    $res_json['trials'] = $this->trials;
    return $res_json;
	}
	
}

?>