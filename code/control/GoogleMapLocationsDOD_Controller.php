<?php

/**
 * Adds functions to a Page_Controller
 * to action a map.
 *
 *
 */

class GoogleMapLocationsDOD_Controller extends Extension {





	#####################
	# INITS
	#####################

	/**
	 * @inherited
	 */
	private static $allowed_actions = array(
		"SearchByAddressForm",
		"loadmap"
	);

	/**
	 * @var GoogleMap
	 */
	protected $googleMap = null;

	/**
	 * @var String
	 */
	protected $address = "";

	/**
	 * look for address
	 *
	 */
	public function onAfterInit(){
		if(!$this->address && isset($_REQUEST["address"])) {
			$this->address = urldecode($_REQUEST["address"]);
		}
		if($this->address) {
			$this->initiateMap();
			$this->googleMap->setAddress($this->address);
		}
	}

	/**
	 * initialise GoogleMap
	 * return GoogleMap
	 */
	protected function initiateMap() {
		if(!$this->googleMap) {
			$this->googleMap = GoogleMap::create();
		}
		return $this->googleMap;
	}







	#####################
	# ACTIONS
	#####################


	/**
	 * provides a link to any map you like.
	 * e.g. mysite.com/mypage/mysub-page/loadmap/optionsHereURLEncoded/
	 * optionsHereURLEncoded are basically the link to the map.
	 * @param HTTPRequest
	 */
	public function loadmap($request){
		$link = urldecode($request->param("ID"));
		$options = explode("/", $link);
		$title = $options[3];
		$this->addExtraLayersAsLinks($title, $link);
		return array();
	}

	/**
	 * returns encoded link for the loadmap function
	 *
	 * @param SiteTree $page
	 * @param String $action
	 * @param String $title
	 * @param Int $lng
	 * @param Int $lat
	 * @param String $filter
	 *
	 * @return String
	 */
	public function LoadmapLink($page, $action = "", $title = "", $lng = 0, $lat = 0, $filter = "") {
		return urlencode($this->getLinkForData($page->ID, $action, $title, $lng, $lat, $filter));
	}







	#####################
	# TEMPLATE METHODS
	#####################


	/**
	 * @return GoogleMap
	 */
	public function GoogleMapController() {
		$this->initiateMap();
		$this->googleMap->loadGoogleMap();
		return $this->googleMap;
	}

	/**
	 * @return Boolean
	 */
	public function HasGoogleMap() {
		if($this->googleMap && $this->owner->classHasGoogleMap()) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * @return Form
	 */
	public function SearchByAddressForm(){
		return SearchByAddressForm::create($this->owner, "SearchByAddressForm", $this->address);
	}








	#####################
	# CREATE MAPS
	#####################


	/**
	 * add a layer to a Google Map
	 *
	 * @param String $action - see GoogleMapDataResponse::allowed_actions to get a list of actions
	 * @param String $title
	 * @param float $lng - default LATITUDE
	 * @param float $lat - default LONGITUDE
	 * @param String $filter - can be a SiteTree class name, e.g. "ProductPage"
	 *                         filter depends on the type of action
	 *
	 */
	function addMap($action = "", $title = "", $lng = 0, $lat = 0, $filter = "") {
		$this->initiateMap();
		if(!$title) {
			$title = $this->owner->Title;
		}
		$allowedActions = Config::inst()->get("GoogleMapDataResponse", "allowed_actions");
		if(isset($allowedActions[$action]) || in_array($action, $allowedActions)) {
			$linkForData = $this->getLinkForData($this->owner->ID, $action, $title, $lng, $lat, $filter);
			//where the magic happens...
			$this->googleMap->addLayer($linkForData);
		}
		else {
			user_error("Could not find $action action in GoogleMapDataResponse", E_USER_NOTICE );
		}
	}

	/**
	 * add an additional layer to an existing map
	 *
	 * @param String $action
	 * @param String $title
	 * @param Int $lng
	 * @param Int $lat
	 * @param String $filter
	 */
	public function addExtraLayersAsAction($action = "", $title = "", $lng = 0, $lat = 0, $filter = "") {
		$this->initiateMap();
		$linkForData = $this->getLinkForData($this->owner->ID, $action, $title, $lng, $lat, $filter);
		$this->addExtraLayersAsLinks($title, $linkForData);
	}

	/**
	 * Make up your own link and add this as a layer
	 *
	 * @param String $title
	 * @param String $link
	 */
	public function addExtraLayersAsLinks($title, $link) {
		$this->initiateMap();
		$this->googleMap->addExtraLayersAsLinks($title, $link);
	}


	/**
	 * add an address to the map
	 * 
	 * @param String $address
	 * @param Boolean $addShowAroundAdress
	 * @param String $filter - usuall a SiteTree ClassName (e.g. ProductPage)
	 */
	function addAddress($address, $addShowAroundAdress = false, $filter = "") {
		if($addShowAroundAdress) {
			$pointArray = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($address);
			if($pointArray) {
				$title = $pointArray["FullAddress"];
				$lng = $pointArray["Longitude"];
				$lat = $pointArray["Latitude"];
				$this->owner->addMap("showaroundmexml", $title, $lng, $lat, $filter );
			}
		}
		else {
			$this->address = $address;
			$this->initiateMap();
		}
	}



	/**
	 * @param DataList $pagesOrGoogleMapLocationsObjects
	 * @param Boolean $retainOldSessionData
	 * @param String $title
	 */
	function addCustomMap($pagesOrGoogleMapLocationsObjects, $retainOldSessionData = false, $title = '') {
		$filterCode = $this->ID."_".$this->request->
		$this->initiateMap();
		$isGoogleMapLocationsObject = true;
		$addCustomGoogleMapArray = GoogleMapDataResponse::get_custom_google_map_session_data($this->owner->ID, "addCustomMap");
		if($pagesOrGoogleMapLocationsObjects) {
			if(!$retainOldSessionData) {
				$this->clearCustomMaps();
			}
			else {
				if(is_array($addCustomGoogleMapArray)) {
					$customMapCount = count($addCustomGoogleMapArray);
				}
			}
			foreach($pagesOrGoogleMapLocationsObjects as $obj) {
				if($obj instanceof SiteTree) {
					$isGoogleMapLocationsObject = false;
				}
				if(!$obj->ID) {
					user_error("Page provided to addCustomMap that does not have an ID", E_USER_ERROR);
				}
				if(!isset($addCustomGoogleMapArray[$title])) {
					$addCustomGoogleMapArray[$title] = array();
				}
				$addCustomGoogleMapArray[$title][] = $obj->ID;
			}
		}
		GoogleMapDataResponse::set_custom_google_map_session_data($addCustomGoogleMapArray, $this->owner->ID, "addCustomMap");
		Session::save();
		if($isGoogleMapLocationsObject) {
			$fn = "showcustomdosmapxml";
		}
		else {
			$fn = "showcustompagesmapxml";
		}
		$this->addMap($fn, $title);
	}









	#####################
	# MAP SETTINGS
	#####################


	/**
	 * @param String $updateServerUrlAddPoint
	 */
	function addUpdateServerUrlAddressSearchPoint($updateServerUrlAddPoint = "/googlemap/showaroundmexml/") {
		$link = Controller::join_links($updateServerUrlAddPoint, $this->owner->ID);
		$this->googleMap->setUpdateServerUrlAddressSearchPoint($link);
	}

	/**
	 * @param String $updateServerUrlDragend
	 */
	function addUpdateServerUrlDragend($updateServerUrlDragend = "googlemap/updatemexml/") {
		$link = Controller::join_links($UpdateServerUrlDragend, $this->owner->ID);;
		$this->googleMap->setUpdateServerUrlDragend($link);
	}

	/**
	 * make the map editable
	 */
	function addAllowAddingAndDeletingPoints() {
		$this->googleMap->AllowAddPointsToMap();
	}

	/**
	 * removes user settings for map
	 * a custom map is a bunch of points that are customised via a session
	 */
	function clearCustomMaps($id = 0, $action = "") {
		GoogleMapDataResponse::clear_custom_google_map_session_data($id, $ation)
	}


	/**
	 * @param String $action
	 * @param String $title
	 * @param Int $lng
	 * @param Int $lat
	 * @param String $filter
	 *
	 * @return String
	 */
	protected function getLinkForData($pageID = 0, $action = "", $title = "", $lng = 0, $lat = 0, $filter = "") {
		if(!$pageID) {
			$pageID = $this->owner->ID;
		}
		$linkForData = "googlemap/".$action."/".$pageID."/".urlencode($title)."/";
		if($lng && $lat) {
			$linkForData .= $lng."/".$lat."/";
		}
		if($filter) {
			$linkForData .= urlencode($filter)."/";
		}
		return $linkForData;
	}

}

