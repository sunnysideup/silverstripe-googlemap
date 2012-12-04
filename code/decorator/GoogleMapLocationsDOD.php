<?php
/*
 *
 */


class GoogleMapLocationsDOD extends DataObjectDecorator {

	protected static $page_classes_without_map = array();
		static function get_page_classes_without_map(){return self::$page_classes_without_map;}
		static function set_page_classes_without_map(array $array){
			if(!is_array($array)) {debug::show("argument needs to be an array in GoogleMapLocationsDOD::set_page_classes_without_map()");}
			self::$page_classes_without_map = $array;
		}

	protected static $page_classes_with_map = array();
		static function get_page_classes_with_map(){return self::$page_classes_with_map;}
		static function set_page_classes_with_map(array $array){
			if(!is_array($array)) {debug::show("argument needs to be an array in GoogleMapLocationsDOD::set_page_classes_with_map()");}
			self::$page_classes_with_map = $array;
		}


	function extraStatics(){
		return array(
			'db' => array(
				"HasGeoInfo" => "Boolean",
			),
			'has_many' => array(
				"GeoPoints" => "GoogleMapLocationsObject"
			),
			'default' => array()
		);
	}

	function updateCMSFields(FieldSet &$fields) {
		if($this->classHasMap()) {
			$fields->addFieldToTab("Root", new Tab("Map"));
			$fields->addFieldToTab("Root.Map", new CheckboxField("HasGeoInfo", "Has Address(es)? - save and reload this page to start data-entry"));
			if($this->owner->HasGeoInfo) {
				$dataObject = new GoogleMapLocationsObject();
				$complexTableFields = $dataObject->complexTableFields();
				$popUpFields = $dataObject->getCMSFields_forPopup($this->owner->ID);
				$GeoPointsField = new ComplexTableField(
					$this->owner,
					'GeoPoints',
					'GoogleMapLocationsObject', //Classname
					$complexTableFields,
					"getCMSFields_forPopup",

					//'getCMSFields_forPopup',
					"ParentID = ".$this->owner->ID
				);
				$GeoPointsField->setParentClass($this->owner->class);
				//$GeoPointsField->setAddTitle( 'A Location' );
				$GeoPointsField->relationAutoSetting = true;
				$fields->addFieldToTab("Root.Map", $GeoPointsField);
			}
		}
		return $fields;
 }

	public function AjaxInfoWindowLink() {
		if($this->owner->hasMethod("CustomAjaxInfoWindow")) {
			return $this->owner->CustomAjaxInfoWindow();
		}
		if($this->owner->hasMethod("ajaxinfowindowreturn")) {
			return '<div class="viewMoreInformationHolder"><a href="'.$this->owner->Link().'" onclick="return !loadAjaxInfoWindow(this,\''.$this->owner->Link().'ajaxinfowindowreturn/\');">'.GoogleMap::get_ajax_info_window_text().'</a><div class="loadAjaxInfoWindowSpan"></div></div>';
		}
	}


	public function classHasMap() {
		//assumptions:
		//1. in general YES
		//2. if list of WITH is shown then it must be in that
		//3. otherwise check if it is specifically excluded (WITHOUT)
		$result = true;
		$inc =  self::get_page_classes_with_map();
		$exc =  self::get_page_classes_without_map();
		if(is_array($inc) && count($inc)) {
			$result = false;
			if(in_array($this->owner->ClassName,$inc)) {
				$result = true;
			}
		}
		elseif(is_array($exc) && count($exc) && in_array($this->owner->ClassName,$exc))  {
			$result = false;
		}
		return $result;
	}



	/**
	 * Recursively search children of current page to find a particular classtype
	 *
	 * @param $parentPage DataObject The Object of which you want to find the children
	 * @param $classType String The text string to match `ClassName` field
	 * @return DataObjectSet of items if Class $classType
	 */
	function getChildrenOfType($parentPage, $classType = null) {
		$children = $parentPage->AllChildren();
		if (!isset($childrenOfType)) {
			$childrenOfType = new DataObjectSet();
		}
		if ($children) {
			foreach($children as $item ) {
				$childrenOfType->merge($this->getChildrenOfType($item, $classType));
			}
		}
		if((isset($classType) && $CurrentPage->ClassName == $classType) || (!isset($classType))) {
			if($parentPage->HasGeoInfo) {
				$childrenOfType->push($parentPage);
			}
		}
		return ($childrenOfType) ? $childrenOfType : new DataObjectSet();
	}
	

}

class GoogleMapLocationsDOD_Controller extends Extension {

	static $allowed_actions = array("SearchByAddressForm");

	protected $address = false;

	protected $googleMap = null;

	protected $isAjax = false;


	protected static $class_name_only = '';
		static function set_class_name_only($v) {self::$class_name_only = $v;}

	function SearchByAddressForm($className = '') {
		return new Form(
			$this->owner,
			"SearchByAddressForm",
			new FieldSet(
				new TextField("Address", _t("GoogleMapLocationsDOD.ENTERLOCATION", "Enter your location"),$this->address),
				new HiddenField("ClassName", "ClassName", self::$class_name_only)
			),
			new FieldSet(new FormAction("findnearaddress", _t("GoogleMapLocationsDOD.SEARCH", "Search"))),
			new RequiredFields("Address")
		);
	}

	function findnearaddress($data, $form) {
		$address = Convert::raw2sql($data["Address"]);
		$className = Convert::raw2sql($data["ClassName"]);
		$pointArray = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($address);
		$this->address = $pointArray["address"];
		if(!isset($pointArray[0]) || !isset($pointArray[0])) {
			GoogleMapSearchRecord::create_new($address, $this->owner->ID, false);
			$form->addErrorMessage('Address', _t("GoogleMapLocationsDOD.ADDRESSNOTFOUND", "Sorry, address could not be found..."), 'warning');
			Director::redirectBack();
			return;
		}
		else {
			GoogleMapSearchRecord::create_new(Convert::raw2sql($address), $this->owner->ID, true);
		}
		$lng = $pointArray[0];
		$lat = $pointArray[1];
		//$form->Fields()->fieldByName("Address")->setValue($pointArray["address"]); //does not work ....
		//$this->owner->addMap($action = "showsearchpoint", "Your search",$lng, $lat);
		$this->owner->addMap($action = "showaroundmexml","Closests to your search", $lng, $lat, $className);
		return array();
	}


	function addMap($action = "", $title = "", $lng = 0, $lat = 0, $filter = "") {
		$this->initiateMap();
		if(!$title) {
			$title = $this->owner->Title;
		}
		$linkForData = "googlemap/".$action."/".$this->owner->ID."/".urlencode($title)."/".$lng."/".$lat."/";
		if($filter) {
			$linkForData .= "/".urlencode($filter)."/";
		}
		$this->googleMap->addLayer($linkForData);
		if(!Director::is_ajax()) {
			if($this->hasStaticMaps()) {
				$controller = new GoogleMapDataResponse();
				if(method_exists($controller, $action)) {
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

	public function addExtraLayersAsAction($action = "", $title = "", $lng = 0, $lat = 0, $filter = "") {
		$linkForData = "googlemap/".$action."/".$this->owner->ID."/".urlencode($title)."/".$lng."/".$lat."/";
		if($filter) {
			$linkForData .= "/".urlencode($filter)."/";
		}
		$this->addExtraLayersAsLinks($title, $linkForData);
	}

	public function addExtraLayersAsLinks($title, $link) {
		$this->initiateMap();
		$this->googleMap->addExtraLayersAsLinks($title, $link);
	}


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
		$_SESSION["addCustomGoogleMap"] =  null;
		$_SESSION["addCustomGoogleMap"] =  array();
	}

	function addCustomMap($pagesOrGoogleMapLocationsObjects, $retainOldSessionData = false, $title = '') {
		$sessionTitle = preg_replace('/[^a-zA-Z0-9]/', '', $title);
		$isGoogleMapLocationsObject = true;
		if($pagesOrGoogleMapLocationsObjects) {
			//Session::clear("addCustomGoogleMap");
			if(!$retainOldSessionData) {
				$this->clearCustomMaps();
			}
			else {
				if(is_array($_SESSION["addCustomGoogleMap"])) {	
					$customMapCount = count($_SESSION["addCustomGoogleMap"]);
				}
			}
			foreach($pagesOrGoogleMapLocationsObjects as $obj) {
				if($obj instanceOf SiteTree) {
					$isGoogleMapLocationsObject = false;
				}
				if(!$obj->ID) {
					user_error("Page provided to addCustomMap that does not have an ID", E_USER_ERROR);
				}
				$_SESSION["addCustomGoogleMap"][$title][] = $obj->ID;
			}
		}
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


	static function hasStaticMapsStaticFunction() {
  	return (!Session::get("StaticMapsOff") && $this->googleMap->getShowStaticMapFirst()) ? true : false;
	}

	private function initiateMap() {
		if(!$this->googleMap) {
			$this->googleMap = new GoogleMap();
		}
	}


/* ******************************
 *  GENERAL FUNCTIONS
 *  ******************************
 */
	function GoogleMapController() {
		$this->initiateMap();
		$this->googleMap->loadGoogleMap();
		return $this->googleMap;
	}

	public function hasMap() {
		if($this->googleMap && $this->owner->classHasMap()) {
			return true;
		}
		else {
			return false;
		}
	}


	function returnMapDataFromAjaxCall($PageDataObjectSet = null, $GooglePointsDataObject = null, $dataObjectTitle = '', $whereStatementDescription = '') {
		$this->googleMap = new GoogleMap();
		$this->googleMap->setDataObjectTitle($dataObjectTitle);
		$this->googleMap->setWhereStatementDescription($whereStatementDescription);
		if($GooglePointsDataObject) {
			$this->googleMap->setGooglePointsDataObject($GooglePointsDataObject);
		}
		elseif($PageDataObjectSet) {
			$this->googleMap->setPageDataObjectSet($PageDataObjectSet);
		}
		else {
			$this->googleMap->staticMapHTML = "<p>No points found</p>";
		}
		$data = $this->googleMap->createDataPoints();
		return $this->owner->renderWith("GoogleMapXml");
	}


}



