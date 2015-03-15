<?php

/**
 * Adds functions to a Page_Controller
 * to action a map.
 *
 *
 */

class GoogleMapLocationsDOD_Controller extends Extension {

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
	 * e.g. mysite.com/mypage/mysub-page/loadmap/optionsHereURLEncoded/
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


	/* ******************************
	 *  TEMPLATE FUNCTIONS
	 * ******************************
	 */


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



	/**
	 * add a layer to a Google Map
	 *
	 * @param String $action - see GoogleMapDataResponse::allowed_actions to get a list of actions
	 * @param String $title
	 * @param float $lng - default LATITUDE
	 * @param float $lat - default LONGITUDE
	 * @param String $filter
	 *
	 * @return Array
	 */
	function addMap($action = "", $title = "", $lng = 0, $lat = 0, $filter = "") {
		$this->initiateMap();
		if(!$title) {
			$title = $this->owner->Title;
		}
		$linkForData = $this->getLinkForData($this->owner->ID, $action, $title, $lng, $lat, $filter);
		//where the magic happens...
		$this->googleMap->addLayer($linkForData);
		if(!Director::is_ajax()) {
			if($this->hasStaticMaps()) {
				$controller = new GoogleMapDataResponse();
				$allowedActions = Config::inst()->get("GoogleMapDataResponse", "allowed_actions");
				if(isset($allowedActions[$action])) {
					$controller->setOwner($this->owner);
					$controller->setTitle($title);
					$controller->setLng($lng);
					$controller->setLat($lat);
					$controller->setFilter($filter);
					return $controller->$action();
				}
				else {
					user_error("Could not find $action action in GoogleMapDataResponse", E_USER_NOTICE );
				}
			}
		}
		return Array();
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
	 * @param String $address
	 * @param Boolean $addShowAroundAdress
	 * @param String $filter
	 */
	function addAddress($address, $addShowAroundAdress = false, $filter = "") {
		if($addShowAroundAdress) {
			$pointArray = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($address);
			if($pointArray) {
				$title = $pointArray["FullAddress"];
				$lng = $pointArray["Longitude"];
				$lat = $pointArray["Latitude"];
				$this->addMap("showaroundmexml", $title, $lng, $lat, $filter );
			}
		}
		else {
			$this->address = $address;
			$this->initiateMap();
		}
	}

	/**
	 * @param String $updateServerUrlAddPoint
	 */
	function addUpdateServerUrlAddressSearchPoint($updateServerUrlAddPoint = "/googlemap/showaroundmexml/") {
		$this->initiateMap();
		$this->googleMap->setUpdateServerUrlAddressSearchPoint($updateServerUrlAddPoint);
	}

	/**
	 * @param String $updateServerUrlDragend
	 */
	function addUpdateServerUrlDragend($updateServerUrlDragend = "googlemap/updatemexml/") {
		$this->initiateMap();
		$UpdateServerUrlDragend .= $this->owner->ID.'/';
		$this->googleMap->setUpdateServerUrlDragend($updateServerUrlDragend);
	}

	/**
	 * make the map editable
	 */
	function addAllowAddingAndDeletingPoints() {
		$this->initiateMap();
		$this->googleMap->AllowAddPointsToMap();
	}

	/**
	 * removes user settings for map
	 * a custom map is a bunch of points that are customised via a session
	 */
	function clearCustomMaps() {
		Session::clear("addCustomGoogleMap");
		Session::set("addCustomGoogleMap", serialize(array()));
		Session::save();
	}

	/**
	 * @param DataList $pagesOrGoogleMapLocationsObjects
	 * @param Boolean $retainOldSessionData
	 * @param String $title
	 *
	 * @return Array
	 */
	function addCustomMap($pagesOrGoogleMapLocationsObjects, $retainOldSessionData = false, $title = '') {
		$this->initiateMap();
		$sessionTitle = preg_replace('/[^a-zA-Z0-9]/', '', $title);
		$isGoogleMapLocationsObject = true;
		$addCustomGoogleMapArray = GoogleMapDataResponse::get_custom_google_map_session_data();
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
		GoogleMapDataResponse::set_custom_google_map_session_data($addCustomGoogleMapArray);
		Session::save();
		if($isGoogleMapLocationsObject) {
			$fn = "showcustomdosmapxml";
		}
		else {
			$fn = "showcustompagesmapxml";
		}
		$this->addMap($fn, $title);
		return Array();
	}

	/**
	 * return Boolean
	 */
	protected function hasStaticMaps() {
		return (!Session::get("StaticMapsOff") && $this->googleMap->ShowStaticMapFirst()) ? true : false;
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


	/**
	 * @param String $action
	 * @param String $title
	 * @param Int $lng
	 * @param Int $lat
	 * @param String $filter
	 *
	 * @return String
	 */
	protected function getLinkForData($pageID, $action = "", $title = "", $lng = 0, $lat = 0, $filter = "") {
		if($lng && $lat) {
			$linkForData = "googlemapextensive/".$action."/".$pageID."/".urlencode($title)."/".$lng."/".$lat."/";
		}
		else {
			$linkForData = "googlemap/".$action."/".$this->owner->ID."/".urlencode($title)."/";
		}
		if($filter) {
			$linkForData .= urlencode($filter)."/";
		}
		return $linkForData;
	}

	/**
	 * todo: why
	 * @param DataList $pageDataList
	 * @param GooglePointDataObject
	 * @param String $dataObjectTitle
	 * @param String $whereStatementDescription
	 *
	 * @return String (XML)
	 */
	protected function returnMapDataFromAjaxCall($pageDataList = null, $googlePointsDataObject = null, $dataObjectTitle = '', $whereStatementDescription = '') {
		if($pageDataList && $googlePointsDataObject) {
			user_error("for GoogleMapLocationsDOD_Controller::returnMapDataFromAjaxCall you need to set pageDataList or googlePointsDataObject NOT both");
		}
		$this->initiateMap();
		$this->googleMap->setDataObjectTitle($dataObjectTitle);
		$this->googleMap->setWhereStatementDescription($whereStatementDescription);
		if($googlePointsDataObject) {
			$this->googleMap->setGooglePointsDataObject($googlePointsDataObject);
		}
		elseif($pageDataList) {
			$this->googleMap->setPageDataObjectSet($pageDataList);
		}
		else {
			$this->googleMap->staticMapHTML = "<p>"._t("GoogleMap.NO_POINTS_FOUND", "no points found")."</p>";
		}
		$data = $this->googleMap->createDataPoints();
		return $this->owner->renderWith("GoogleMapXml");
	}


}

