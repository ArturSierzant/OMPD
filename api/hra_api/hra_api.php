<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015 Artur Sierzant                                 |
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


class HraAPI {
	public $username;
	public $password;
	public $lang = "en";
	public $audioQuality = "HIGH";
	public $curl;
	public $fixSSLcertificate = false;
	protected $sessionId;
	protected $userId;
	protected $userData;
	
	const AUTH_URL = "https://streaming.highresaudio.com:8182/vault3/user/login";
	const API_URL = "https://streaming.highresaudio.com:8182/vault3/";
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
			http_build_query(array('username' => $this->username,'password' => $this->password)));
	
		$server_output = curl_exec($this->curl);
		if (!$server_output) {
			$errors = array();
			$errors['return'] = 1;
			$errors['error'] = "Error code: " . curl_errno($this->curl) . ": " . curl_error($this->curl);
			return($errors);
		}
		else {
			$res_json = json_decode($server_output, true);
			if ($res_json["session_id"] && $res_json["user_id"]) {
				$this->sessionId = $res_json["session_id"];
				$this->userId = $res_json["user_id"];
				$this->userData = '{"user_id":"' . $this->userId . '","session_id":"' . $this->sessionId . '"}';
				return true;
			}
			else {
				$errors = array();
				$errors['return'] = 1;
				$errors['error'] = $res_json["error_code"];
				return $errors;
			}
		};
	}
	
	function quickSearch($query) {
		$query = urlencode($query);
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/search/quickSearch/?search=" . $query ."&lang=" . $this->lang);
		return $this->request();
	}
	
	function searchInCategory($query, $category) {
		$query = urlencode($query);
		$category = urlencode($category);
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/SearchInCategory/?search=" . $query ."&category=" . $category. "&lang=" . $this->lang);
		return $this->request();
	}
	
	function searchArtists($query, $limit = 50) {
		//do some voodoo
		$artists = explode("&", $query);
		$query = strtolower(urlencode($artists[0]));
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/search/quickSearch/?search=" . $query . "&type=artist&lang=" . $this->lang);
		$res = $this->request();
		//return $res;
		if ($res["data"]) {
			$data = array();
			foreach ($res["data"] as $item) {
				if ($item["type"] == "Artist") {
					$data[] = array("artistId" => $item["artistId"],
					"cover" => $item["cover"],
					"title" => $item["title"],
					"artist" => $item["artist"]);
				}
			}
			$artistCol = array_column($data, 'artist');
			array_multisort($artistCol, SORT_ASC, $data);
			$ret = array();
			$ret["data"] = $data;
			$ret["search_string"] = $query;
			return $ret;
		}
		else {
			return $this->request();
		}
	}
	
	
	function searchAlbums($query, $limit = 50) {
		$query = urlencode($query);
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/search/quickSearch/?search=" . $query . "&lang=" . $this->lang);
		$res = $this->request();
		if ($res["data"]) {
			$data = array();
			foreach ($res["data"] as $key => $item) {
				if ($item["type"] == "Album") {
					$data[] = array(
					"title" => $item["title"],
					"artist" => $item["artist"],
					"artistId" => $item["artistId"],
					"cover" => $item["cover"],
					"albumId" => $key
					);
				}
			}
			$artistCol = array_column($data, 'artist');
			array_multisort($artistCol, SORT_ASC, $data);
			$ret = array();
			$ret["data"] = $data;
			return $ret;
		}
		else {
			return $this->request();
		}
	}
	
	function getArtists($limit = 50) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "/vault/ListAllArtists/?limit=" . $limit);
		return $this->request();
	}
	
	function getAlbum($album_id) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/album/?album_id=" . $album_id . "&userData=" . $this->userData . "&lang=" . $this->lang);
		return $this->request();
	}
	
	
	function getArtistAlbums($artist, $limit = 50) {
		$ret = array();
		//do some voodoo
		$artist = str_replace("&", "", strtolower($artist));
		
		$artist =  rawurlencode($artist);
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/AdvancedSearch/?lang=" . $this->lang . "&limit=" . $limit . "&search=" . $artist);
		$res = $this->request();
		//return $res;
		if ($res["data"]["results"]["album"]) {
			$data = array();
			foreach ($res["data"]["results"]["album"] as $key => $item) {
				$data[] = array(
				"albumId" => $item["id"],
				"title" => $item["title"],
				"artist" => $item["artist"],
				"artistId" => $item["artistId"],
				"cover" => $item["cover"],
				"genre" => $item["genre"],
				"playtime" => $item["playtime"],
				"year" => $item["productionYear"]
				);
			}
			$artistCol = array_column($data, 'year');
			array_multisort($artistCol, SORT_ASC, $data);
			$ret["data"] = $data;
			return $ret;
		}
		else {
			$ret["data"] = "";
			return $ret;
			//return $this->request();
		}
	}
	
	function getAlbumTracks($album_id) {
		return $this->getAlbum($album_id)["data"]["results"];
	}

	function getTrack($track_id) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/track/?track_id=" . $track_id . "&userData=" . $this->userData . "&lang=" . $this->lang);
		return $this->request();
	}
	
	function getFormats() {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/getAvailableFormats/?userData=" . $this->userData . "&lang=" . $this->lang);
		return $this->request();
	}
  
	function getEditorPlaylists() {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/editorPlaylists/?category=" . urlencode('New Releases') . "&userData=" . $this->userData . "&lang=" . $this->lang);
		return $this->request();
	}
  
	function getSingleEditorPlaylists($id) {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/getSingleEditorPlaylists/?id=" . $id . "&userData=" . $this->userData . "&lang=" . $this->lang);
		return $this->request();
	}
  
  function getAllGenres() {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/categories/ListAllGenre/?userData=" . $this->userData . "&lang=" . $this->lang);
		return $this->request();
	}
  
  function getAllCategories() {
		curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/categories/ListAllCategories/?userData=" . $this->userData . "&lang=" . $this->lang);
		return $this->request();
	}
  
  function getCategorieContent($categorie, $limit=30, $offset=0) {
    //$categorie = "/HIGHRES%20AUDIO/Musicstore/&genre=Latin/Alternativo%20%26%20Rock%20Latino";
    
    $exploded = explode("/",$categorie);
    $counter = count($exploded);
    
    if (strpos($categorie,"/Genre/") !== false) {
      $genre = "&genre=" . urlencode($exploded[4]);
      if ($counter == 6) { // with subgenre
        $genre .= "/" . urlencode($exploded[5]);
      }
      $categorie = "/HIGHRES%20AUDIO/Musicstore/" . $genre . "&sort=-releaseDate";
    }
    else {
      $categorie = "";
      for ($i =1; $i<$counter; $i++) {
        $categorie .= "/" . urlencode($exploded[$i]);
      }
        $categorie .= "&sort=-releaseDate";
    }
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "vault/categories/ListCategorieContent/?category=" . $categorie . "&limit=" . $limit . "&offset=" . $offset . "&lang=" . $this->lang);
		return $this->request();
	}


	function request() {
		curl_setopt($this->curl, CURLOPT_POST, 0);
    for ($i=0; $i<3; $i++) {
      $server_output = curl_exec($this->curl);
      if (curl_errno($this->curl) == 0 ) {
        break;
      }
    }
		$res_json = json_decode($server_output, true);
		return $res_json;
	}
	
}

?>