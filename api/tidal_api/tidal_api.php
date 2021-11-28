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
  public $userId;
  public $countryCode;
  public $token;
  public $refreshToken;
  public $expiresAfter;
  public $audioQuality = "HIGH";
  public $curl;
  public $fixSSLcertificate = false;
  public $deviceCode;
  public $apiKey;
  
  const AUTH_URL = 'https://auth.tidal.com/v1/oauth2';
  const TOKEN_VERIFY_URL = 'https://api.tidal.com/v1/sessions';
  const LOGOUT_URL = 'https://api.tidal.com/v1/logout';
  const API_URL = "https://api.tidal.com/v1/";
  const API_V2_URL = "https://api.tidal.com/v2/";
  const RESOURCES_URL = "https://resources.tidal.com/images/";
  

  public function __construct(){
    //$this->apiKey = array('clientId' => base64_decode('YVI3Z1VhVEsxaWhwWE9FUA=='), 'clientSecret' => base64_decode('ZVZXQkVrdUwyRkNqeGdqT2tSM3lLMFJZWkViY3JNWFJjMmw4ZlUzWkNkRT0='));
    $this->apiKey = array('clientId' => base64_decode('T21EdHJ6Rmd5VlZMNnVXNTZPbkZBMkNPaWFicW0='), 'clientSecret' => base64_decode('enhlbjFyM3BPMGhndE9DN2o2dHdNbzlVQXFuZ0dybVJpV3BWN1FDMXpKOD0='));
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
    //return false;
    if ($this->expiresAfter > time()) {
      return true;
    }
    return false;
  }
  
  function verifyAccessToken(){
    curl_setopt($this->curl, CURLOPT_URL, self::TOKEN_VERIFY_URL);
    return $this->request();
  }

  function refreshAccessToken(){
    curl_setopt($this->curl, CURLOPT_URL,self::AUTH_URL . '/token');
    curl_setopt($this->curl, CURLOPT_POST, 1);
    curl_setopt($this->curl, CURLOPT_POSTFIELDS,
      http_build_query(array('client_id' => $this->apiKey['clientId'],'refresh_token' => $this->refreshToken ,'grant_type' => 'refresh_token','scope' => 'r_usr+w_usr+w_sub')));
      
    curl_setopt($this->curl, CURLOPT_USERPWD, $this->apiKey['clientId'] . ":" . $this->apiKey['clientSecret']);
    
    $server_output = curl_exec($this->curl);
    
    $res_json = json_decode($server_output, true);
    if (isset($res_json['status']) && $res_json['status'] != 200) {
      $errors = array();
      $errors['return'] = 1;
      $errors['error'] = $server_output;
      return ($errors);
    }
    
    return $res_json;
  }
  
  function getDeviceCode(){
    curl_setopt($this->curl, CURLOPT_URL,self::AUTH_URL . '/device_authorization');
    curl_setopt($this->curl, CURLOPT_POST, 1);
    curl_setopt($this->curl, CURLOPT_POSTFIELDS,
      http_build_query(array('client_id' => $this->apiKey['clientId'],'scope' => 'r_usr+w_usr+w_sub')));
    
    $server_output = curl_exec($this->curl);
    
    $res_json = json_decode($server_output, true);
    if (isset($res_json['status']) && $res_json['status'] != 200) {
      $errors = array();
      $errors['return'] = 1;
      $errors['error'] = $server_output;
      return ($errors);
    }
    
    return $res_json;
    
  }
  
  function checkAuthStatus(){
    curl_setopt($this->curl, CURLOPT_URL,self::AUTH_URL . '/token');
    curl_setopt($this->curl, CURLOPT_POST, 1);
    curl_setopt($this->curl, CURLOPT_POSTFIELDS,
      http_build_query(array('client_id' => $this->apiKey['clientId'],'device_code' => $this->deviceCode ,'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code','scope' => 'r_usr+w_usr+w_sub')));
    
    curl_setopt($this->curl, CURLOPT_USERPWD, $this->apiKey['clientId'] . ":" . $this->apiKey['clientSecret']);
    
    $server_output = curl_exec($this->curl);
    
    $res_json = json_decode($server_output, true);
    if (isset($res_json['status']) && $res_json['status'] != 200) {
      if ($res_json['status'] == 400 && $res_json['sub_status'] == 1002){
        $res_json['auth_status'] = "authorization pending";
      }
      else {
        $errors = array();
        $errors['return'] = 1;
        $errors['error'] = $server_output;
        return ($errors);
      }
    }

    return $res_json;
    
  }
  
  function logout() {
    curl_setopt($this->curl, CURLOPT_URL,self::LOGOUT_URL);
    curl_setopt($this->curl, CURLOPT_POST, 1);
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('authorization: Bearer ' . $this->token));
    
    $server_output = curl_exec($this->curl);
    
    $res_json = json_decode($server_output, true);
    $res_json['logout_status'] = false;
    if (isset($res_json['status'])) {
        $errors = array();
        $errors['return'] = 1;
        $errors['error'] = $server_output;
        return ($errors);
    }
    $res_json['logout_status'] = true;
    return $res_json;
  }

  function search($type, $query, $limit = 50) {
    $query = urlencode($query);
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "search/" . $type . "?query=" . $query . "&countryCode=" . $this->countryCode . "&limit=" . $limit);
    return $this->request();
  }

  function searchAll($query, $limit = 50) {
    $query = urlencode($query);
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "search?query=" . $query . "&countryCode=" . $this->countryCode . "&limit=" . $limit . "&types=ARTISTS,ALBUMS,TRACKS,PLAYLISTS");
    return $this->request();
  }

  function getTrack($track_id) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "tracks/" . $track_id . "?countryCode=" . $this->countryCode);
    return $this->request();
  }

  function getAlbum($album_id) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "albums/" . $album_id . "?countryCode=" . $this->countryCode);
    return $this->request();
  }

  function getAlbumTracks($album_id) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "albums/" . $album_id . "/tracks?countryCode=" . $this->countryCode);
    return $this->request();
  }
    
  function getAlbumInfo($album_id) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "pages/album?albumId=" . $album_id . "&deviceType=BROWSER&countryCode=" . $this->countryCode);
    return $this->request();
  }

  function getAlbumReview($album_id) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "albums/" . $album_id . "/review?countryCode=" . $this->countryCode);
    return $this->request();
  }

  function getArtistAll($artist_id) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "pages/artist?artistId=" . $artist_id . "&countryCode=" . $this->countryCode . "&deviceType=BROWSER");
    return $this->request();
  }

  function getArtistAlbums($artist_id, $limit = 50) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "artists/" . $artist_id . "/albums?countryCode=" . $this->countryCode . "&limit=" . $limit);
    return $this->request();
  }

  function getArtistEPsAndSingles($artist_id, $limit = 50) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "artists/" . $artist_id . "/albums?filter=EPSANDSINGLES&countryCode=" . $this->countryCode . "&limit=" . $limit);
    return $this->request();
  }

  function getArtistTopTracks($artist_id, $limit = 10) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "artists/" . $artist_id . "/toptracks?filter=ALL&countryCode=" . $this->countryCode . "&limit=" . $limit);
    return $this->request();
  }

  function getArtistBio($artist_id, $limit = 10) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "artists/" . $artist_id . "/bio?countryCode=" . $this->countryCode . "&limit=" . $limit);
    return $this->request();
  }

  function getArtistLinks($artist_id, $limit = 20) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "artists/" . $artist_id . "/links?countryCode=" . $this->countryCode . "&limit=" . $limit);
    return $this->request();
  }

  function getRelatedArtists($artist_id) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "pages/artist?artistId=" . $artist_id . "&countryCode=" . $this->countryCode . "&deviceType=BROWSER");
    $artistAll = $this->request();
    foreach ($artistAll["rows"] as $module){
      if ($module["modules"][0]["type"]=='ARTIST_LIST') {
        return $module["modules"][0]["pagedList"]["items"];
      };
    }
  }

  function getNewAlbums($limit = 100) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "pages/show_more_featured_albums?countryCode=" . $this->countryCode . "&limit=" . $limit . "&deviceType=BROWSER");
    $res = $this->request();
    return $res;
    $s = array_search("featured-new",array_column($res["rows"][0]["modules"][0]["tabs"],"key"));
    return $res["rows"][0]["modules"][0]["tabs"][$s]["pagedList"]["items"];
  }

  function getFeatured($limit = 100, $offset = 0) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "featured/new/albums?countryCode=" . $this->countryCode . "&limit=" . $limit . "&offset=" . $offset);
    return $this->request();
  }	

  function getFeaturedRecommended($limit = 100, $offset = 0) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "featured/recommended/albums?countryCode=" . $this->countryCode . "&limit=" . $limit . "&offset=" . $offset);
    return $this->request();
  }

  function getFeaturedTop($limit = 100, $offset = 0) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "featured/top/albums?countryCode=" . $this->countryCode . "&limit=" . $limit . "&offset=" . $offset);
    return $this->request();
  }

  function getFeaturedLocal($limit = 100, $offset = 0) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "featured/local/albums?countryCode=" . $this->countryCode . "&limit=" . $limit . "&offset=" . $offset);
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

  function getByApiPath($limit, $offset, $apiPath) {
    if ($limit > 50) $limit = 50; //Tidal API limitation
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . $apiPath ."?locale=en_US&deviceType=BROWSER&countryCode=" . $this->countryCode . "&limit=" . $limit . "&offset=" . $offset);
    return $this->request();
  }

  function getStreamURL_old($track_id) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "tracks/" . $track_id . "/streamUrl?soundQuality=" . $this->audioQuality . "&countryCode=" . $this->countryCode);
    return $this->request();
  }

  function getStreamURL($track_id) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "tracks/" . $track_id . "/playbackinfopostpaywall?audioquality=" . $this->audioQuality . "&countryCode=" . $this->countryCode . "&playbackmode=STREAM&assetpresentation=FULL");
    $res = $this->request();
    if (strpos($res['manifestMimeType'],"vnd.tidal.bt") !== false) {
      $manifest = json_decode(base64_decode($res['manifest']),true);
      $res['manifest_b64_decoded'] = $manifest;
      $res['url'] = $manifest['urls'][0];
    }
    return $res;
  }

  function getUserPlaylists() {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "users/" . $this->userId . "/playlists?countryCode=" . $this->countryCode . "&limit=" . $limit);
    return $this->request();
  }

  function getUserPlaylistTracks($playlist_id, $limit = 1000) {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "playlists/" . $playlist_id . "/tracks?countryCode=" . $this->countryCode . "&limit=" . $limit);
    return $this->request();
  }

  function getHomePage() {
    //curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "pages/home?locale=en_US&deviceType=DESKTOP&countryCode=" . $this->countryCode);
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "pages/home?locale=en_US&deviceType=BROWSER&countryCode=" . $this->countryCode);
    return $this->request();
  }

  function getUserClients() {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "users/" . $this->userId . "/clients?countryCode=" . $this->countryCode);
    return $this->request();
  }

  function getUserSubscription() {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "users/" . $this->userId . "/subscription?countryCode=" . $this->countryCode);
    return $this->request();
  }

  function getUserFeedActivities() {
    curl_setopt($this->curl, CURLOPT_URL, self::API_V2_URL . "feed/activities/?userId=" . $this->userId . "&countryCode=" . $this->countryCode . '&locale=en-us');
    return $this->request();
  }

  function getExplorePage() {
    curl_setopt($this->curl, CURLOPT_URL, self::API_URL . "pages/explore?locale=en_US&deviceType=BROWSER&countryCode=" . $this->countryCode);
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
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('authorization: Bearer ' . $this->token));
    for ($i=0; $i<3; $i++) {
      $server_output = curl_exec($this->curl);
      if (curl_errno($this->curl) == 0 ) {
        break;
      }
    }
    $res_json = json_decode($server_output, true);
    if (isset($res_json['status']) && ($res_json['status']) != 200) {
      $res_json['return'] = 1;
      $res_json['response'] = $res_json['userMessage'];
    }
    return $res_json;
  }

}

?>