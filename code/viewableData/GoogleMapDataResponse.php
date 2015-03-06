<?php

/**
 * a controller that returns the Google Map
 * You can show one point by adding ?i=123
 * Where 123 is the ID of a GoogleMapLocationsObject
 *
 * returns Data For Map ....
 *
 * HERE are the options
 * ======================
 *
 * 'turnonstaticmaps' => start with static maps
 *
 * 'turnoffstaticmaps' => go straight to interactive maps
 *
 * 'showemptymap' => map without anything on it, fallback
 *
 * 'showpagepointsmapxml' => show points from the current page
 *
 * 'showchildpointsmapxml' => show points from the child pages
 *
 * 'showsearchpoint' =>
 *
 * 'showcustompagesmapxml' =>
 *
 * 'showcustomdosmapxml' =>
 *
 * 'showdataobjects' =>
 *
 * 'updatemexml' =>
 *
 * 'showaroundmexml' =>
 *
 * 'showpointbyid' =>
 *
 */

class GoogleMapDataResponse extends Controller {






	#################
	# UPDATE SESSION
	#################
	/**
	 *
	 * @param Array $addCustomGoogleMapArrayNEW
	 */
	public static function add_custom_google_map_session_data($addCustomGoogleMapArrayNEW){
		$addCustomGoogleMapArrayOLD = Session::get("addCustomGoogleMap");
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








	#################
	# BASICS
	#################
	/**
	 * @inherited
	 */
	private static $allowed_actions = array(
		'turnonstaticmaps',
		'turnoffstaticmaps',
		'showemptymap',
		'showpagepointsmapxml',
		'showchildpointsmapxml',
		'showsearchpoint',
		'showcustompagesmapxml',
		'showcustomdosmapxml',
		'showdataobjects',
		'updatemexml',
		'showaroundmexml',
		'showpointbyid'
	);

	/**
	 * @var Array
	 */
	private static $actions_without_owner = array(
		'turnonstaticmaps',
		'turnoffstaticmaps',
		'showemptymap'
	);

	/**
	 * The Page that is displaying the
	 * @var SiteTree
	 */
	protected $owner = null;

	/**
	 * @var Float
	 */
	protected $lng = 0;

	/**
	 * @var Float
	 */
	protected $lat = 0;

	/**
	 * @var String
	 */
	protected $title = "";

	/**
	 * @var String
	 */
	protected $sessionTitle = "";

	/**
	 * @var String
	 */
	protected $filter = "";

	/**
	 * @var GoogleMap
	 */
	protected $map = null;











	#################
	# SET AND GET VARIABLES
	#################

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
		$this->sessionTitle = $sessionTitle = preg_replace('/[^a-zA-Z0-9]/', '', $this->title. "_" . $this->owner->ID);
		$this->lng = floatval($this->request->param("Longitude"));
		$this->lat = floatval($this->request->param("Latitude"));
		$this->filter = urldecode($this->request->param("Filter"));
		if(!$this->title && $this->owner) {
			$this->title = $this->owner->Title;
		}
	}

	/**
	 * @param String
	 */
	function setOwner($owner) {$this->owner = $owner;}

	/**
	 * @param String
	 */
	function setTitle($title) {$this->title = $title;}

	/**
	 * @param Float
	 */
	function setLat($lat) {$this->lat = $lat;}

	/**
	 * @param Float
	 */
	function setLng($lng) {$this->lng = $lng;}

	/**
	 * @param String
	 */
	function setFilter($filter) {$this->filter = $filter;}











	#################
	# ACTIONS
	#################

	/**
	 * @param SS_HTTPRequest
	 *
	 * @return String (XML)
	 */
	function index($request) {
		return $this->showemptymap();
	}

	/**
	 * @param SS_HTTPRequest
	 *
	 * @return String
	 */
	public function turnonstaticmaps($request) {
		Session::set("StaticMapsOff", 0);
		return _t("GoogleMap.STATIC_MAPS_LOADED_FIRST", "static maps will be loaded first");
	}

	/**
	 * @param SS_HTTPRequest
	 *
	 * @return String
	 */
	public function turnoffstaticmaps($request) {
		Session::set("StaticMapsOff", 1);
		return _t("GoogleMap.INTERACTIVE_MAPS_LOADED_IMMEDIATELY", "interactive maps will be loaded immediately");
	}

	/**
	 * @param SS_HTTPRequest
	 *
	 * @return String (XML)
	 */
	public function showemptymap($request) {
		return $this->makeXMLData(null, null, $this->title, _t("GoogleMap.POINT_RELATED_TO", "Points related to ").$this->title);
	}

	/**
	 * @param SS_HTTPRequest
	 *
	 * @return String (XML)
	 */
	public function showpagepointsmapxml($request) {
		$data = GoogleMapLocationsObject::get()->filter(array("ParentID" => $this->owner->ID));
		if($data->count()) {
			return $this->makeXMLData(null, $data, $this->title, $this->title);
		}
		return $this->showemptymap();
	}

	/**
	 * @param SS_HTTPRequest
	 *
	 * @return String (XML)
	 */
	public function showchildpointsmapxml($request) {
		if($children = $this->owner->getChildrenOfType($this->owner, null)) {
			return $this->makeXMLData($children, null, "Points related to ".$this->title, "Points related to ".$this->title);
		}
		return $this->showemptymap();
	}

	/**
	 * @param SS_HTTPRequest
	 *
	 * @return String (XML)
	 */
	public function showsearchpoint($request) {
		if($this->lat && $this->lng) {
			$point = GoogleMapLocationsObject::create();
			$point->ParentID = $this->owner->ID;
			$point->Latitude = $this->lat;
			$point->Longitude = $this->lng;
			$point->CustomPopUpWindowTitle = $this->title;
			if($this->address) {
				die("get address to do");
				$point->CustomPopUpWindowInfo = $this->address;
			}
			if($point) {
				$data = new ArrayList();
				$data->push($point);
				return $this->makeXMLData(null, $data, $this->title, $this->title);
			}
		}
		return $this->showemptymap();
	}

	/**
	 * @param SS_HTTPRequest
	 *
	 * @return String (XML)
	 */
	public function showpointbyid() {
		die("To be completed");
		$id = 0;
		$googleMapLocationsObjects = GoogleMapLocationsObject::get()->filter(array("ID" => $id));
		return $this->makeXMLData(null, $googleMapLocationsObjects, $this->title, $this->title);
	}

	/**
	 * load data from session
	 *
	 * @param SS_HTTPRequest
	 *
	 * @return String (XML)
	 */
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

	/**
	 * load a custom set of GoogleMapLocationsObjects
	 *
	 * @param SS_HTTPRequest
	 *
	 * @return String (XML)
	 */
	public function showcustomdosmapxml($request) {
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

	/**
	 * Show what is around my points
	 *
	 * @param SS_HTTPRequest
	 *
	 * @return String (XML)
	 */
	public function showaroundmexml($request) {
		$lng = 0;
		$lat = 0;
		$excludeIDList = array();
		if($this->lat && $this->lng) {
			$lat = $this->lat;
			$lng = $this->lng;
		}
		elseif($this->owner->ID) {
			//find the average!
			$objects = GoogleMapLocationsObject::get()->filter(array("ParentID" => $this->owner->ID));
			if($count = $objects->count()) {
				foreach($objects as $point) {
					$lat += $point->Latitude;
					$lng += $point->Longitude;
					$excludeIDList[] = $point->ID;
				}
				$lat = $lat / $count;
				$lng = $lng / $count;
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
			$title = _t("GoogleMap.CLOSES_TO_ME", "Closest to me");
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
				return $this->makeXMLData(null, $objects, $title, Config::inst()->get("GoogleMap", "number_shown_in_around_me") . " "._t("GoogleMap.CLOSEST_POINTS", "closest points"));
			}
		}
		return $this->showemptymap();
	}

	/**
	 * URL must contain for GET variables
	 * i - ID of owner
	 * a - action
	 * x - lng
	 * y - lat
	 *
	 * actions are:
	 *   - add
	 *   - move
	 *   - remove
	 *
	 * @param SS_HTTPRequest
	 *
	 * @return String (message)
	 */
	public function updatemexml($request) {
		//we use request here, because the data comes from javascript!
		if($this->owner->canEdit()) {
			if(isset($_REQUEST["x"]) && isset($_REQUEST["y"]) && isset($_REQUEST["i"]) && isset($_REQUEST["a"]) ) {
				$lng = floatval($_REQUEST["x"]);
				$lat = floatval($_REQUEST["y"]);
				$id = intval($_REQUEST["i"]);
				$action = $_REQUEST["a"];
				if($lat && $lng) {
					if( 0 == $id && "add" == $action ) {
						$point = new GoogleMapLocationsObject;
						$point->ParentID = $this->owner->ID;
						$point->Longitude = $lng;
						$point->Latitude = $lat;
						$point->write();
						return $point->ID;
					}
					elseif($id > 0 && "move" == $action) {
						$point = GoogleMapLocationsObject::get()->byID($id);
						if($point) {
							if($point->ParentID == $this->owner->ID) {
								$point->Longitude = $lng;
								$point->Latitude = $lat;
								$point->Address = "";
								$point->FullAddress = "";
								$point->write();
								return  _t("GoogleMap.LOCATION_UPDATED", "location updated");
							}
							else {
								return _t("GoogleMap.NO_PERMISSION_TO_UPDATE", "you dont have permission to update that location");
							}
						}
						else {
							return _t("GoogleMap.COULD_NOT_FIND_LOCATION", "could not find location");
						}
					}
					elseif($id && "remove" == $action) {
						$point = GoogleMapLocationsObject::get()->byID($id);
						if($point) {
							if($point->ParentID == $this->owner->ID) {
								$point->delete();
								$point = null;
								return _t("GoogleMap.LOCATION_DELETED", "location deleted");
							}
							else {
								return _t("GoogleMap.NO_DELETE_PERMISSION", "you dont have permission to delete that location");
							}
						}
						else {
							return _t("GoogleMap.COULD_NOT_FIND_LOCATION", "could not find location.");
						}
					}
				}
				else {
					return _t("GoogleMap.LOCATION_NOT_DEFINED", "point not defined.");
				}
			}
			else {
				return _t("GoogleMap.MISSING_VARIABLES", "not enough information was provided.");
			}
		}
		return  _t("GoogleMap.POINT_NOT_UPDATED", "You do not have permission to change the map.");
	}







	#################
	# TEMPLATE METHODS
	#################
	/**
	 *
	 * @return GoogleMap
	 */
	function GoogleMapController() {
		if(!$this->map) {
			user_error("No map has been created");
		}
		return $this->map;
	}








	#################
	# PRIVATE PARTY
	#################

	/**
	 *
	 * @return String (XML)
	 */
	protected function makeXMLData($PageDataObjectSet = null, $GooglePointsDataObject = null, $dataObjectTitle = '', $whereStatementDescription = '') {
		$this->map = GoogleMap::create();
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
			user_error("Could not provide data for map", E_USER_NOTICE);
			return "error";
		}
	}

}
