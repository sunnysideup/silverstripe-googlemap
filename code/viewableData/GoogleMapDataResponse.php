<?php

class GoogleMapDataResponse extends Controller {

	private static $allowed_actions = array(
		'turnonstaticmaps',
		'turnoffstaticmaps',
		'showpagepointsmapxml',
		'showchildpointsmapxml',
		'showemptymap',
		'showcustompagesmapxml',
		'showcustomdosmapxml',
		'showdataobjects',
		'updatemexml',
		'showaroundmexml',
		'showsearchpoint',
		'showpointbyid'
	);

	private static $actions_without_owner = array(
		'turnonstaticmaps',
		'turnoffstaticmaps',
		'showemptymap'
	);

	protected $owner = null;
	protected $lng = 0;
	protected $lat = 0;
	protected $title = "";
	protected $sessionTitle = "";
	protected $filter = "";
	protected $map = null;

/* ******************************
 * RETURNING DATA FOR MAPS USING AJAX
 * ******************************
 */

	function init() {
		parent::init();
		$id = 0;
		if($this->request->param("OwnerID")) {
			$id = intval($this->request->param("OwnerID"));
		}
		elseif(isset($_GET["i"])) {
			$i = intval($_GET["i"]);
			$point = GoogleMapLocationsObject::get()->byID($i);
			if(!$point) {
				//New POINT
			}
			else {
				$id = $point->ParentID;
			}
		}
		if($id) {
			$this->owner = SiteTree::get()->byID($id);
		}
		//HACK
		elseif(!$this->owner) {
			$this->owner = SiteTree::get()->filter(array(
				"Title" => Convert::raw2sql($this->request->param("Title"))
			))->First();
		}
		if(!$this->owner  & !in_array($this->request->param("Action"), self::$actions_without_owner)) {
			//user_error("no owner has been identified for GoogleMapDataResponse", E_USER_NOTICE);
			$this->owner = SiteTree::get()->First();
		}
		//END HACK
		$this->title = urldecode($this->request->param("Title"));
		$this->sessionTitle = $sessionTitle = preg_replace('/[^a-zA-Z0-9]/', '', $this->title);
		$this->lng = floatval($this->request->param("Longitude"));
		$this->lat = floatval($this->request->param("Latitude"));
		$this->filter = urldecode($this->request->param("Filter"));
		if(!$this->title && $this->owner) {
			$this->title = $this->owner->Title;
		}
	}
	function setOwner($owner) {$this->owner = $owner;}
	function setTitle($title) {$this->title = $title;}
	function setLng($lng) {$this->lng = $lng;}
	function setLat($lat) {$this->lat = $lat;}
	function setFilter($filter) {$this->filter = $filter;}

	//3.0TODO check that this
	function GoogleMapController() {
		return $this->map;
	}

	function index() {
		return $this->showemptymap();
	}

	public function turnonstaticmaps() {
		Session::set("StaticMapsOff", 0);
		return "static maps will be loaded first";
	}

	public function turnoffstaticmaps() {
		Session::set("StaticMapsOff", 1);
	}

	public function showpagepointsmapxml() {
		$data = GoogleMapLocationsObject::get()->filter(array("ParentID" => $this->owner->ID));
		if($data->count()) {
			return $this->makeXMLData(null, $data, $this->title, $this->title);
		}
	}

	public function showchildpointsmapxml() {
		if($children = $this->owner->getChildrenOfType($this->owner, null)) {
			return $this->makeXMLData($children, null, "Points related to ".$this->title, "Points related to ".$this->title);
		}
	}

	public function showemptymap() {
		return $this->makeXMLData(null, null, "Points related to ".$this->title, "Points related to ".$this->title);
	}

	public function showsearchpoint() {
		if($this->lng && $this->lat) {
			$point = new GoogleMapLocationsObject;
			$point->ParentID = $this->owner->ID;
			$point->Latitude = $this->lat;
			$point->Longitude = $this->lng;
			$point->CustomPopUpWindowTitle = $this->title;
			$point->CustomPopUpWindowInfo = $this->address;
			if($point) {
				$data = new ArrayList();
				$data->push($point);
				return $this->makeXMLData(null, $data, $this->title, $this->title);
			}
		}
	}

	public function showpointbyid() {

	}

	public function showcustompagesmapxml($request) {
		$array = Array(-1);
		$addCustomGoogleMapArray = GoogleMapDataResponse::get_custom_google_map_session_data();
		if(isset($addCustomGoogleMapArray[$this->title])) {
			$array = $addCustomGoogleMapArray[$this->title];
		}
		//print_r($array);
		if(is_array($array) && count($array)) {
			$where = " \"SiteTree_Live\".\"ID\" IN (".implode(",",$array).")";
		}
		else {
			$where = " \"SiteTree_Live\".\"ID\" < 0";
		}
		$pages = Versioned::get_by_stage("SiteTree", "Live", $where);
		return $this->makeXMLData($pages, null, $this->title, $this->title);
	}

	public function showcustomdosmapxml() {
		$array = Array(-1);
		$addCustomGoogleMapArray = GoogleMapDataResponse::get_custom_google_map_session_data();
		if(isset($addCustomGoogleMapArray[$this->title])) {
			$array = $addCustomGoogleMapArray[$this->title];
		}
		//print_r($array);
		if(is_array($array) && count($array)) {
			$where = array("GoogleMapLocationsObject.ID" => $array);
		}
		else {
				//3.0TODO check this
			$where = array("GoogleMapLocationsObject.ID:LessThan" => 0);
			//$where = " \"GoogleMapLocationsObject\".\"ID\" < 0";
		}
		$googleMapLocationsObjects = GoogleMapLocationsObject::get()->filter($where);
		return $this->makeXMLData(null, $googleMapLocationsObjects, $this->title, $this->title);
	}

	public function showaroundmexml() {
		$lng = 0;
		$lat = 0;
		$excludeIDList = array();
		if($this->lng && $this->lat) {
			$lng = $this->lng;
			$lat = $this->lat;
		}
		elseif($this->owner->ID) {
			$objects = GoogleMapLocationsObject::get()->filter(array("ParentID" => $this->owner->ID));
			if($count = $objects->count()) {
				foreach($objects as $point) {
					$lng += $point->Longitude;
					$lat += $point->Latitude;
					$excludeIDList[] = $point->ID;
				}
				$lng = $lng / $count;
				$lat = $lat / $count;
			}
		}
		$classNameForParent = '';
		if($otherClass = $this->filter) {
			$classNameForParent = $otherClass;
		}
		if($this->title) {
			$title = $this->title;
		}
		else {
			$title = "Closest to me";
		}
		if($lng && $lat) {
			$orderByRadius = GoogleMapLocationsObject::radiusDefinition($lng, $lat);
			$where = "(".$orderByRadius.") > 0 AND \"GoogleMapLocationsObject\".\"Latitude\" <> 0 AND \"GoogleMapLocationsObject\".\"Longitude\" <> 0";
			if($classNameForParent && !is_object($classNameForParent)) {
				$where .= " AND \"SiteTree_Live\".\"ClassName\" = '".$classNameForParent."'";
			}
			if(count($excludeIDList)) {
				$where .= " AND \"GoogleMapLocationsObject\".\"ID\" NOT IN (".implode(",",$excludeIDList).") ";
			}
			$join = "LEFT JOIN \"SiteTree_Live\" ON \"SiteTree_Live\".\"ID\" = \"GoogleMapLocationsObject\".\"ParentID\"";
			$objects = GoogleMapLocationsObject::get()
				->where($where)
				->sort($orderByRadius)
				->leftJoin("SiteTree_Live", "SiteTree_Live.ID = GoogleMapLocationsObject.ParentID")
				->limit(GoogleMap::get_number_shown_in_around_me());
			if($objects->count()) {
				return $this->makeXMLData(null, $objects, $title, Config::inst()->get("GoogleMap", "number_shown_in_around_me") . " closest points");
			}
			else {
				return "no data 1";//return false;
			}
		}
		return "no lng and lat";
	}

	public function updatemexml() {
		//we use request here, because the data comes from javascript!
		if($this->owner->canEdit()) {
			if(isset($_REQUEST["x"]) && isset($_REQUEST["y"]) && isset($_REQUEST["i"]) && isset($_REQUEST["a"]) ) {
				$lng = floatval($_REQUEST["x"]);
				$lat = floatval($_REQUEST["y"]);
				$id = intval($_REQUEST["i"]);
				$action = $_REQUEST["a"];
				if($lng && $lat) {
					if( 0 == $id && "add" == $action ) {
						$point = new GoogleMapLocationsObject;
						$point->ParentID = $this->owner->ID;
						$point->Latitude = $lat;
						$point->Longitude = $lng;
						$point->write();
						return $point->ID;
					}
					elseif($id > 0 && "move" == $action) {
						$point = GoogleMapLocationsObject::get()->byID($id);
						if($point) {
							if($point->ParentID == $this->owner->ID) {
								$point->Latitude = $lat;
								$point->Longitude = $lng;
								$point->Address = "";
								$point->FullAddress = "";
								$point->write();
								return  "location updated";
							}
							else {
								return "you dont have permission to update that location";
							}
						}
						else {
							return "could not find location";
						}
					}
					elseif($id && "remove" == $action) {
						$point = GoogleMapLocationsObject::get()->byID($id);
						if($point) {
							if($point->ParentID == $this->owner->ID) {
								$point->delete();
								$point = null;
								return "location deleted";
							}
							else {
								return "you dont have permission to delete that location";
							}
						}
						else {
							return "could not find location.";
						}
					}
				}
				else {
					return "point not defined.";
				}
			}
			else {
				return "not enough information was provided.";
			}
		}
		return  "point could NOT be updated.";
	}


/* ******************************
 * PRIVATE PARTY BELOW
 * ******************************
 */


	protected function makeXMLData($PageDataObjectSet = null, $GooglePointsDataObject = null, $dataObjectTitle = '', $whereStatementDescription = '') {
		$this->map = new GoogleMap();
		$this->map->setDataObjectTitle($dataObjectTitle);
		$this->map->setWhereStatementDescription($whereStatementDescription);
		if($GooglePointsDataObject) {
			$this->map->setGooglePointsDataObject($GooglePointsDataObject);
		}
		elseif($PageDataObjectSet) {
			$this->map->setPageDataObjectSet($PageDataObjectSet);
		}
		else {
			$this->staticMapHTML = "<p>No points found</p>";
		}
		$data = $this->map->createDataPoints();

		if(Director::is_ajax() || $this->owner->ID) {
			//$this->dataPointsXML = $data[1];
			$this->turnoffstaticmaps();
			$this->response->addHeader("Content-Type", "text/xml; charset=\"utf-8\"");
			return $this->renderWith("GoogleMapXml");
		}
		else {
			return "error";
			//$this->dataMapObjectSet = $data[0];
			//$this->staticMapHTML = $data[2];
		}
	}

	/**
	 *
	 * @param Array $addCustomGoogleMapArrayNEW
	 */
	public static function add_custom_google_map_session_data($addCustomGoogleMapArrayNEW){
		$addCustomGoogleMapArrayNEW = array_merge($addCustomGoogleMapArrayOLD, $addCustomGoogleMapArrayNEW);
		Session::set("addCustomGoogleMap", serialize($addCustomGoogleMapArrayNEW));
	}

	/**
	 *
	 * @param Array $addCustomGoogleMapArray
	 */
	public static function set_custom_google_map_session_data($addCustomGoogleMapArray){
		if(!is_array($addCustomGoogleMapArray)) {
			user_error("addCustomGoogleMapArray should be an array!");
		}
		Session::set("addCustomGoogleMap", serialize($addCustomGoogleMapArray));
	}

	/**
	 *
	 * @return Array
	 */
	public static function get_custom_google_map_session_data(){
		$data = Session::get("addCustomGoogleMap");
		if(is_array($data)) {
			$addCustomGoogleMapArray = $data;
		}
		else {
			try {
				$addCustomGoogleMapArray = unserialize($data);
			}
			catch (Exception $e) {
				$addCustomGoogleMapArray = array();
			}
		}
		return $addCustomGoogleMapArray;
	}

}
