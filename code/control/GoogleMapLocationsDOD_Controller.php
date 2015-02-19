<?php

/**
 *
 *
 *
 */

class GoogleMapLocationsDOD_Controller extends Extension {

	private static $allowed_actions = array("SearchByAddressForm");

	protected $googleMap = null;

	/**
	 * add a layer to a Google Map
	 * @param String $action
	 * @param String $title
	 * @param Int $lng
	 * @param Int $lat
	 * @param String $filter
	 *
	 * @return Array
	 */
	function addMap($action = "", $title = "", $lng = 0, $lat = 0, $filter = "") {
		$this->initiateMap();
		if(!$title) {
			$title = $this->owner->Title;
		}
		if($lng && $lat) {
			$linkForData = "googlemapextensive/".$action."/".$this->owner->ID."/".urlencode($title)."/".$lng."/".$lat."/";
		}
		else {
			$linkForData = "googlemap/".$action."/".$this->owner->ID."/".urlencode($title);
		}
		if($filter) {
			$linkForData .= urlencode($filter)."/";
		}
		//where the magic happens...
		$this->googleMap->addLayer($linkForData);
		if(!Director::is_ajax()) {
			if($this->hasStaticMaps()) {
				$controller = new GoogleMapDataResponse();
				if($controller->hasMethod($action)) {
					$controller->setOwner($this->owner);
					$controller->setTitle($title);
					$controller->setLng($lng);
					$controller->setLat($lat);
					$controller->setFilter($filter);
					return $controller->$action();
				}
			}
		}
		return Array();
	}

	/**
	 * @param String $action
	 * @param String $title
	 * @param Int $lng
	 * @param Int $lat
	 * @param String $filter
	 */
	public function addExtraLayersAsAction($action = "", $title = "", $lng = 0, $lat = 0, $filter = "") {
		if($lng && $lat) {
			$linkForData = "googlemapextensive/".$action."/".$this->owner->ID."/".urlencode($title)."/".$lng."/".$lat."/";
		}
		else {
			$linkForData = "googlemap/".$action."/".$this->owner->ID."/".urlencode($title);
		}
		if($filter) {
			$linkForData .= "/".urlencode($filter)."/";
		}
		$this->addExtraLayersAsLinks($title, $linkForData);
	}

	/**
	 * @param String $action
	 * @param String $title
	 */
	public function addExtraLayersAsLinks($title, $link) {
		$this->initiateMap();
		$this->googleMap->addExtraLayersAsLinks($title, $link);
	}


	/**
	 * @param String $address
	 */
	function addAddress($address = '') {
		$this->initiateMap();
		if(!$address && isset($_REQUEST["address"])) {
			$address = urlencode($_REQUEST["address"]);
		}
		if($address) {
			$this->googleMap->setAddress($address);
		}
		else {
			user_error("No address could be added.", E_USER_ERROR);
		}
	}

	function addUpdateServerUrlAddressSearchPoint($UpdateServerUrlAddPoint = "/googlemap/showaroundmexml/") {
		$this->initiateMap();
		$this->googleMap->setUpdateServerUrlAddressSearchPoint($UpdateServerUrlAddPoint);
	}

	function addUpdateServerUrlDragend($UpdateServerUrlDragend = "googlemap/updatemexml/") {
		$this->initiateMap();
		$UpdateServerUrlDragend .= $this->owner->ID.'/';
		$this->googleMap->setUpdateServerUrlDragend($UpdateServerUrlDragend);
	}

	function addAllowAddingAndDeletingPoints() {
		$this->initiateMap();
		$this->googleMap->allowAddPointsToMap();
	}

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


	protected function hasStaticMaps() {
		return (!Session::get("StaticMapsOff") && $this->googleMap->getShowStaticMapFirst()) ? true : false;
	}


	public static function has_static_maps() {
		return (!Session::get("StaticMapsOff") && $this->googleMap->getShowStaticMapFirst()) ? true : false;
	}

	protected function initiateMap() {
		if(!$this->googleMap) {
			$this->googleMap = new GoogleMap();
		}
	}


/* ******************************
 *  GENERAL FUNCTIONS
 * ******************************
 */
	function GoogleMapController() {
		$this->initiateMap();
		$this->googleMap->loadGoogleMap();
		return $this->googleMap;
	}

	public function hasGoogleMap() {
		if($this->googleMap && $this->owner->classHasGoogleMap()) {
			return true;
		}
		else {
			return false;
		}
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
	public function returnMapDataFromAjaxCall($pageDataList = null, $GooglePointsDataObject = null, $dataObjectTitle = '', $whereStatementDescription = '') {
		$this->googleMap = new GoogleMap();
		$this->googleMap->setDataObjectTitle($dataObjectTitle);
		$this->googleMap->setWhereStatementDescription($whereStatementDescription);
		if($GooglePointsDataObject) {
			$this->googleMap->setGooglePointsDataObject($GooglePointsDataObject);
		}
		elseif($pageDataList) {
			$this->googleMap->setPageDataObjectSet($pageDataList);
		}
		else {
			$this->googleMap->staticMapHTML = "<p>No points found</p>";
		}
		$data = $this->googleMap->createDataPoints();
		return $this->owner->renderWith("GoogleMapXml");
	}


}

