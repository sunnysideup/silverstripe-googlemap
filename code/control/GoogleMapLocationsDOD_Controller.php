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
	 *
	 * @param HTTPRequest
	 */
	public function loadmap($request){
		$link = urldecode($request->param("ID"));
		$options = explode("/", $link);
		$this->addExtraLayersAsLinks($title = $options[3], $link);
		return array();
	}

	/**
	 * add a layer to a Google Map
	 *
	 * @param String $action - see GoogleMapDataResponse::allowed_actions to get a list of actions
	 * @param String $title
	 * @param float $lat - default LONGITUDE
	 * @param float $lng - default LATITUDE
	 * @param String $filter
	 *
	 * @return Array
	 */
	function addMap($action = "", $title = "", $lat = 0, $lng = 0, $filter = "") {
		$this->initiateMap();
		if(!$title) {
			$title = $this->owner->Title;
		}
		$linkForData = $this->getLinkForData($this->owner->ID, $action = "", $title = "", $lat = 0, $lng = 0, $filter = "");
		//where the magic happens...
		$this->googleMap->addLayer($linkForData);
		if(!Director::is_ajax()) {
			if($this->hasStaticMaps()) {
				$controller = new GoogleMapDataResponse();
				$allowedActions = Config::inst()->get("GoogleMapDataResponse", "allowed_actions");
				if(isset($allowedActions[$action])) {
					$controller->setOwner($this->owner);
					$controller->setTitle($title);
					$controller->setLat($lat);
					$controller->setLng($lng);
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
	 * @param Int $lat
	 * @param Int $lng
	 * @param String $filter
	 */
	public function addExtraLayersAsAction($action = "", $title = "", $lat = 0, $lng = 0, $filter = "") {
		$linkForData = $this->getLinkForData($this->owner->ID, $action = "", $title = "", $lat = 0, $lng = 0, $filter = "");
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
	 */
	function addAddress($address = '') {
		if($address) {
			$this->address = $address;
		}
		$this->initiateMap();
		if(!$this->address) {
			user_error("No address could be added.", E_USER_ERROR);
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
		$this->googleMap->allowAddPointsToMap();
	}

	/**
	 * removes user settings for map
	 * a custom map is a bunch of points that are customised.
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
		return (!Session::get("StaticMapsOff") && $this->googleMap->getShowStaticMapFirst()) ? true : false;
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
	 *  returns encoded link for the loadmap function
	 *
	 * @param SiteTree $page
	 * @param String $action
	 * @param String $title
	 * @param Int $lat
	 * @param Int $lng
	 * @param String $filter
	 *
	 * @return String
	 */
	public function LoadmapLink($page, $action = "", $title = "", $lat = 0, $lng = 0, $filter = "") {
		return urlencode($this->getLinkForData($page->ID, $action, $title, $lat, $lng, $filter));
	}

	/**
	 * @param String $action
	 * @param String $title
	 * @param Int $lat
	 * @param Int $lng
	 * @param String $filter
	 *
	 * @return String
	 */
	protected function getLinkForData($pageID, $action = "", $title = "", $lat = 0, $lng = 0, $filter = "") {
		if($lat && $lng) {
			$linkForData = "googlemapextensive/".$action."/".$pageID."/".urlencode($title)."/".$lat."/".$lng."/";
		}
		else {
			$linkForData = "googlemap/".$action."/".$this->owner->ID."/".urlencode($title)."/";
		}
		if($filter) {
			$linkForData .= urlencode($filter)."/";
		}
		return $linkForData;
	}

	/* ******************************
	 *  TEMPLATE FUNCTIONS
	 * ******************************
	 */


	/**
	 * @return GoogleMap
	 */
	function GoogleMapController() {
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
	function SearchByAddressForm(){
		return SearchByAddressForm::create($this->owner, "SearchByAddressForm", $this->address);
	}

	/**
	 *
	 * @param DataList $pageDataList
	 * @param GooglePointDataObject
	 * @param String $dataObjectTitle
	 * @param String $whereStatementDescription
	 *
	 * @return String (XML)
	 */
	public function returnMapDataFromAjaxCall($pageDataList = null, $googlePointsDataObject = null, $dataObjectTitle = '', $whereStatementDescription = '') {
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

