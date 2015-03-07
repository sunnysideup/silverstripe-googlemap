<?php
/**
 * This is the basic engine for the Google Map
 *
 * There are three options
 * 1. setAddress: set address
 * 2. setPageDataObjectSet: set Page Data List (who have geo points as children)
 * 3. setGooglePointsDataObject: set Points Directly Class = GoogleMapLocationsObject
 *
 * Extras include:
 * 1. setDataObjectTitle: set title
 * 2. setWhereStatementDescription: set where statement
 * 3. setFilteredClassNameArray: set an array of classnames
 *
 * The static configs are explained in googlemap/_config/googlemap.yml.example
 *
 *
 **/
class GoogleMap extends ViewableData {


	/**
	 * var arrayOfLatitudeAndLongitude: Array (Latitude" => 123, "Longitude" => 123, "Marker" => "red1");
	 * Marker is optional
	 * @param Array arrayOfLatitudeAndLongitude
	 * @param String title
	 *
	 * @return String (HTML - img tag)
	 */

	public static function quick_static_map($arrayOfLatitudeAndLongitude, $title) {
		$staticMapURL = '';
		$count = 0;
		//width
		$staticMapWidth = Config::inst()->get("GoogleMap", "google_map_width");
		if($staticMapWidth > 512) { $staticMapWidth = 512;}
		//height
		$staticMapHeight = Config::inst()->get("GoogleMap", "google_map_height");
		if($staticMapHeight > 512) { $staticMapHeight = 512;}
		$staticMapURL = "size=".$staticMapWidth."x".$staticMapHeight;
		if(count($arrayOfLatitudeAndLongitude)) {
			//http://maps.google.com/maps/api/staticmap?sensor=true&maptype=map&size=209x310&
			//markers=color:green%7Clabel:A%7C-45.0302,168.663
			//&markers=color:red%7Clabel:Q%7C-36.8667,174.767
			foreach($arrayOfLatitudeAndLongitude as $row) {
				$staticMapURL .= '&amp;markers=color:'.$row["Colour"].'%7Clabel:'.$row["Label"].'%7C';
				$staticMapURL .= round($row["Latitude"], 6).",".round($row["Longitude"], 6);
				$count++;
			}
			if($count == 1) {
				$staticMapURL .= '&amp;center='.$defaultCenter.'&amp;zoom='. Config::inst()->get("GoogleMap", "default_zoom");
			}
		}
		return self::make_static_map_url_into_image($staticMapURL, $title);
	}

	/**
	 * @param String $staticMapURL
	 * @param String $title
	 *
	 * @return String (HTML - img tag)
	 */
	protected static function make_static_map_url_into_image($staticMapURL, $title) {
		$fullStaticMapURL  = '';
		$uses_sensor = "false";
		if($this->Config()->get("uses_sensor")) {
			$uses_sensor = "true";
		}
		$fullStaticMapURL =
			'http://maps.google.com/maps/api/staticmap?'
				.'sensor='.$uses_sensor.'&amp;'
				.$this->Config()->get("static_map_settings").'&amp;'
				.$staticMapURL.'&amp;'
				.'key='.Config::inst()->get("GoogleMap", "google_map_api_key");
		if($this->Config()->get("save_static_map_locally")) {
			$fileName = str_replace(array('&', '|', ',', '=', ';'), array('', '', '', '', ''), $staticMapURL);
			$length = strlen($fileName);
			$fileName = "_sGMap".substr(hash("md5", $fileName), 0, 35)."_".$length.".gif";
			$fullStaticMapURL = StaticMapSaverForHTTPS::convert_to_local_file(str_replace('&amp;', '&', $fullStaticMapURL), $fileName);
		}
		return '<img class="staticGoogleMap" src="'.$fullStaticMapURL.'" alt="map: '.Convert::raw2att($title).'" />';
	}


	private static $_includes_are_done = false;// this is a hack to avoid having multiple includes

	/* BASIC MAP SETTINGS */
	private static $google_map_api_key = "";
	private static $uses_sensor = true;
	private static $default_latitude = 0.000000001; //MOVE TO SITECONFIG
	private static $default_longitude = 0.0000000001; //MOVE TO SITECONFIG
	private static $default_zoom = 0; //MOVE TO SITECONFIG
	private static $google_map_width = 500;
		public function getGoogleMapWidth() {return Config::inst()->get("GoogleMap", "google_map_width");}
	private static $google_map_height = 500;
		public function getGoogleMapHeight() {return Config::inst()->get("GoogleMap", "google_map_height");}

	/* MAP CONTROLS*/
	private static $map_type_default_zero_to_two = 0; //MOVE TO SITECONFIG
	private static $view_finder_size = 100; //MOVE TO SITECONFIG
	private static $map_add_type_control = false; //MOVE TO SITECONFIG
	private static $map_control_size_one_to_three = 3; //MOVE TO SITECONFIG
	private static $map_scale_info_size_in_pixels = 100; //MOVE TO SITECONFIG

	/* INFORMATION AROUND THE MAP */
	private static $default_title = ""; //MOVE TO SITECONFIG
	private static $default_where_statement_description = ""; //MOVE TO SITECONFIG
	private static $no_status_at_all = false; //MOVE TO SITECONFIG
	private static $add_kml_link = false; //MOVE TO SITECONFIG
	private static $hidden_layers_removed_from_list = false;
	private static $change_page_title = false; //MOVE TO SITECONFIG
	private static $number_of_items_before_showing_list = 1; //MOVE TO SITECONFIG

	private static $title_div_id = "";
		public function getTitleDivID() {return Config::inst()->get("GoogleMap", "title_div_id");}

	private static $side_bar_div_id = "";
		public function getSideBarDivId() {return Config::inst()->get("GoogleMap", "side_bar_div_id");}

	private static $drop_down_div_id	="";
		public function getDropDownDivId() {return Config::inst()->get("GoogleMap", "drop_down_div_id");}

	private static $layer_list_div_id = "";
		public function getLayerListDivId() {return Config::inst()->get("GoogleMap", "layer_list_div_id");}

	private static $directions_div_id = "";
		public function getDirectionsDivId() {return Config::inst()->get("GoogleMap", "directions_div_id");}

	private static $status_div_id = "";
		public function getStatusDivId() {return Config::inst()->get("GoogleMap", "status_div_id");}


	/* INFOWINDOW*/
	private static $info_window_options = "{maxWidth:280, zoomLevel:17, mapTypeId: google.maps.MapTypeId.HYBRID}";
	private static $add_antipodean = false; //MOVE TO SITECONFIG
	private static $add_directions = false; //MOVE TO SITECONFIG
	private static $add_current_address_finder = false; //MOVE TO SITECONFIG
	private static $add_zoom_in_button = true; //MOVE TO SITECONFIG
	private static $add_close_up_button = false; //MOVE TO SITECONFIG
	private static $add_close_window_button = false; //MOVE TO SITECONFIG
	private static $ajax_info_window_text = "View Details"; //MOVE TO SITECONFIG

	/* MARKERS */
	private static $add_points_to_map = false;
		public function allowAddPointsToMap() {$this->Config()->update("add_points_to_map", true);}
	private static $add_delete_marker_button = "delete this point";
	private static $marker_options = "{bouncy:true,title: \"click me\"}";
	private static $preload_images = false;

	/* ICONS */
	private static $default_icon_url = "";
	private static $icon_folder = "/googlemap/images/icons/";
	private static $icon_width = 20;
	private static $icon_height = 34;
	private static $icon_image_map = "[]";
	private static $icon_extension = "png";
	private static $icon_max_count = 12;

	/* POLYS */
	private static $line_colour = "#000";
	private static $line_width = 12;
	private static $line_opacity = 0.5;
	private static $fill_colour = "#ffccff";
	private static $fill_opacity = 0.5;
	private static $poly_icon = "";

	/* STATIC MAP */
	private static $show_static_map_first = false; //MOVE TO SITECONFIG
		public function getShowStaticMapFirst() {return (Config::inst()->get("GoogleMap", "show_static_map_first") && !Session::get("StaticMapsOff")) ? true : false;}
	private static $static_map_settings = "maptype=roadmap";
	private static $static_icon = "";
	private static $save_static_map_locally = false;

/* ADDRESS FINDER */
	private static $add_address_finder = true; //MOVE TO SITECONFIG
		public function getAddAddressFinder() {return Config::inst()->get("GoogleMap", "add_address_finder");}
	private static $default_country_code = "NZ";
	private static $default_address_text = " New Zealand"; //MOVE TO SITECONFIG
	private static $number_shown_in_around_me = 7; //MOVE TO SITECONFIG

/* DIRECTIONS SETTINGS */
	private static $style_sheet_url = true;
	private static $locale_for_results = "en_NZ";

/* SERVER SETTINGS */
	private static $lat_form_field_id = "";
	private static $lng_form_field_id = "";

/* JS SETTINGS */

	/*********************
	 *  data objects
	 ********************/

	/**
	 * @var String
	 */
	protected $dataPointsXML;
		function setDataPointsXML($s){$this->dataPointsXML = $s;}
		function getDataPointsXML(){return $this->dataPointsXML;}

	/**
	 * @var ArrayList
	 */
	protected $dataPointsObjectSet;
		public function setDataPointsObjectSet($s){$this->dataPointsObjectSet = $s;}
		public function getDataPointsObjectSet(){return $this->dataPointsObjectSet;}

	/**
	 * @var String (HTML)
	 */
	protected $dataPointsStaticMapHTML;
		public function setDataPointsStaticMapHTML($s){$this->dataPointsObjectSet = $s;}
		public function getDataPointsStaticMapHTML(){return $this->dataPointsObjectSet;}

	/*********************
	 *  map data
	 ********************/

	/**
	 * @var DataList
	 */
	protected $googlePointsDataObject = null;
		public function setGooglePointsDataObject($s){$this->googlePointsDataObject = $s;}
		public function getGooglePointsDataObject(){return $this->googlePointsDataObject;}

		/**
		 * @param DataList $pageDataList
		 */
		public function setPageDataObjectSet($pageDataList) {
			if($pageDataList->count()) {
				$this->googlePointsDataObject = GoogleMapLocationsObject::get()->filter(array("ParentID" => $pageDataList->map("ID", "ID")->toArray));
				$pageDataList = null;
			}
		}

	/**
	 * @var String
	 */
	protected $whereStatementDescription = "";
		public function setWhereStatementDescription($s){$this->whereStatementDescription = $s;}
		public function getWhereStatementDescription(){return $this->whereStatementDescription;}

	/**
	 * Method on an object that returns the datapoints
	 * @var String
	 */
	protected $fieldNameForGoogleDataObjectWithPages = "GoogleDataPoints";
		public function setFieldNameForGoogleDataObjectWithPages($s){$this->fieldNameForGoogleDataObjectWithPages = $s;}
		public function getFieldNameForGoogleDataObjectWithPages(){return $this->fieldNameForGoogleDataObjectWithPages;}

	/**
	 * address being searched for
	 * @var String
	 */
	protected $address = "";
		public function setAddress($v) {$this->address = Convert::raw2js($v);}
		public function getAddress($v) {return $this->address;}

	/**
	 * filter for class names
	 * @var Array
	 */
	protected $filteredClassNameArray = Array();
		public function setFilteredClassNameArray($a){$this->filteredClassNameArray = $s;}
		public function getFilteredClassNameArray(){return $this->filteredClassNameArray;}

	/**
	 * title of map
	 * @var String
	 */
	protected $dataObjectTitle = "";
		public function setDataObjectTitle($a){$this->dataObjectTitle = $s;}
		public function getDataObjectTitle(){return $this->dataObjectTitle;}


	/**
	 * @var ArrayList
	 */
	protected $extraLayersAsLinks = null;
		public function setExtraLayersAsLinks($v) {user_error("use GoogleMap::addExtraLayersAsLinks");}
		public function getExtraLayersAsLinks() {return $this->extraLayersAsLinks;}

		/**
		 * @param String $title
		 * @param String $link
		 */
		public function addExtraLayersAsLinks($title, $link) {
			if($this->getExtraLayersAsLinks() === null) {
				$this->extraLayersAsLinks = new ArrayList();
			}
			$this->extraLayersAsLinks->push(new ArrayData(array("Title" => $title, "Link" => $link)));
		}

		/**
		 *
		 * @return ArrayList
		 */
		public function AllExtraLayersAsLinks() {
			if(!$this->getShowStaticMapFirst()) {
				return $this->getExtraLayersAsLinks();
			}
		}

	/**
	 * @var Array
	 */
	protected $linksForData = Array();
		public function setLinksForData($a) {$this->linksForData = $a;}
		public function getLinksForData() {return $this->linksForData;}

		/**
		 * @param ArrayData $linkForData (Title, Link)
		 */
		function addLayer($linkForData) {
			$this->linksForData[] = $linkForData;
		}

	/*********************
	 *  server interaction
	 ********************/

	/**
	 * @var String
	 */
	protected $updateServerUrlAddressSearchPoint = "";
		public function setUpdateServerUrlAddressSearchPoint($v) {$this->updateServerUrlAddressSearchPoint = Director::absoluteBaseURL().$v;}
		public function getUpdateServerURLAddressSearchPoint() {return $this->updateServerUrlAddressSearchPoint;}

	/**
	 * @var String
	 */
	protected $updateServerUrlDragend = "";
		public function setUpdateServerUrlDragend($v) {$this->updateServerUrlDragend = Director::absoluteBaseURL().$v;}
		public function getUpdateServerUrlDragend() {return $this->updateServerUrlDragend;}
		/**
		 * @return Boolean
		 */
		public function canEdit($member = null) {if($this->getUpdateServerUrlDragend()) {return true;} }


	public function loadGoogleMap() {
		$js = '';
		$this->loadDefaults();
		if(!self::$_includes_are_done) {
			Requirements::themedCSS("googleMap");
			Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
			Requirements::javascript("googlemap/javascript/googleMapStatic.js");
			Requirements::javascript("googlemap/javascript/loadAjaxInfoWindow.js");
			Requirements::insertHeadTags('<style type="text/css">v\:* {behavior:url(#default#VML);}</style>', "GoogleMapCustomHeadTag");
			if(!$this->getShowStaticMapFirst()) {
				Requirements::javascript("http://maps.googleapis.com/maps/api/js?v=3.16&sensor=".$this->showFalseOrTrue(self::$uses_sensor));
				Requirements::javascript("googlemap/javascript/googleMaps.js");
				$js .= 'var scriptsLoaded = true; jQuery(document).ready( function() { initiateGoogleMap();} );';
			}
			else {
				$js .= 'var scriptsLoaded = false;';
				Requirements::javascript('http://www.google.com/jsapi?key='.Config::inst()->get("GoogleMap", "google_map_api_key"));//
			}
			$js .= 'var absoluteBaseURL = "'. Director::absoluteBaseURL() .'";';
			$js .= $this->createJavascript();
			Requirements::customScript($js, "GoogleMapCustomScript");
			self::$_includes_are_done = true;
		}
	}

	/**
	 * sorts points by Latitude
	 * @param ArrayList
	 *
	 * @return ArrayList
	 */
	public function orderItemsByLatitude($unsortedSet = null, $reverse = false) {
		if(!$unsortedSet) {
			$unsortedSet = $this->dataPointsObjectSet;
		}
		$sortedSet = new ArrayList();
		if($unsortedSet->count()) {
			foreach($unsortedSet as $item) {
				$tempArray[$item->Latitude] = $item;
			}
		}
		ksort($tempArray);
		//not sure why this is a bit counter intuitive.
		if(!$reverse) {
			$tempArray = array_reverse($tempArray);
		}
		foreach($tempArray as $item) {
			$sortedSet->push($item);
		}
		return $sortedSet;
	}

	/**
	 *
	 * @return Int
	 */
	public function getDataPointCount() {
		if($this->dataPointsObjectSet) {
			return $this->dataPointsObjectSet->count();
		}
		elseif($this->googlePointsDataObject->count()){
			return $this->googlePointsDataObject->count();
		}
		elseif(isset($_SESSION["addCustomGoogleMap"])) {
			return count($_SESSION["addCustomGoogleMap"]);
		}
		elseif($a = Session::get("addCustomGoogleMap")) {
			return count($a);
		}
		return 0;
	}

	/**
	 * @return Boolean
	 */
	public function EnoughPointsForAList() {
		//we were using the line below, but that did not seem to work
		//return $this->getDataPointCount() >= $this->Config()->get("number_of_items_before_showing_list") ? true : false;
		return true;
	}

	/**
	 * @return String
	 */
	public function Link() {
		$page = Controller::curr();
		if($page) {
			return $page->Link();
		}
	}

	/**
	 * must be public
	 * does not return the datapoints XML
	 * but loads it into variables...
	 * @return Boolean
	 */
	public function createDataPoints() {
		$this->dataPointsStaticMapHTML = '';
		$this->dataPointsXML = '';
		$this->dataPointsObjectSet = new ArrayList();
		$this->loadDefaults();
		$idArray = array();
		//width
		$staticMapWidth = $this->Config()->get("google_map_width");
		if($staticMapWidth > 512) { $staticMapWidth = 512;}
		//height
		$staticMapHeight = $this->Config()->get("google_map_height");
		if($staticMapHeight > 512) { $staticMapHeight = 512;}
		$this->dataPointsStaticMapHTML = "size=".$staticMapWidth."x".$staticMapHeight;
		$totalCount = $this->googlePointsDataObject->count();
		if($totalCount > 0  && $totalCount < 500) {
			$count = 0;
			$pointsXml = '';
			$this->dataPointsStaticMapHTML .= '&amp;markers=';
			if($iconURLForStatic = $this->Config()->get("default_icon_url")) {
				$this->dataPointsStaticMapHTML .= 'icon:'.urlencode($iconURLForStatic).'|';
			}
			//the sort works, but Google Map does not seem to care...
			//$this->googlePointsDataObject = $this->orderItemsByLatitude($this->GooglePointsDataObject);
			foreach($this->googlePointsDataObject as $dataPoint) {
				$dataPoint->addParentData();
				if(!count($this->filteredClassNameArray) || in_array($dataPoint->ClassName, $this->filteredClassNameArray)) {
					if(!in_array($dataPoint->ID, $idArray)) {
						if($dataPoint->PointType == "polygon") {
							$dataLine = '<Polygon><outerBoundaryIs><LinearRing><coordinates>'.$dataPoint->PointString.'</coordinates></LinearRing></outerBoundaryIs></Polygon>';
						}
						elseif($dataPoint->PointType == "polyline") {
							$dataLine = '<LineString><coordinates>'.$dataPoint->PointString.'</coordinates></LineString>';
						}
						else {
							$dataLine = '<Point><coordinates>'.$dataPoint->Longitude.','.$dataPoint->Latitude.'</coordinates></Point>';
						}
						$link = '';
						if($dataPoint->Link) {
							$link = $dataPoint->AjaxInfoWindowLink;
						}
						$staticIcon = '';
						if($dataPoint->staticIcon) {
							$staticIcon = $dataPoint->staticIcon;
						}
						else {
							$staticIcon = $this->Config()->get("static_icon");
						}
						if($count) {
						 $this->dataPointsStaticMapHTML .= '|';
						}
						$center = round($dataPoint->Latitude, 6).",".round($dataPoint->Longitude, 6);
						if(!$count) {
							$defaultCenter = $center;
						}
						$this->dataPointsStaticMapHTML .= $center;
						if($staticIcon) {
							$this->dataPointsStaticMapHTML .= ",".$staticIcon;
						}
						$pointsXml .=
									'<Placemark>'.
									'<id>'.$dataPoint->ID.'</id>'.
									'<name>'.Convert::raw2xml($dataPoint->Name).'</name>'.
									$dataLine.
									'<description><![CDATA[ '.$dataPoint->AjaxInfoWindowLink.']]></description>'.
									'</Placemark>';
						$this->dataPointsObjectSet->push($dataPoint);
						$count++;
					}
				}
				$idArray[$dataPoint->ID] = $dataPoint->ID;
			}
			if($count == 1) {
				$this->dataPointsStaticMapHTML .= '&amp;center='.$defaultCenter.'&amp;zoom='.$this->Config()->get("default_zoom");
			}
			$this->dataPointsXML =
						'<mapinfo>'.'<title>'.$this->dataObjectTitle.'</title>'
						.'<latitude>'.$this->Config()->get("default_latitude").'</latitude>'
						.'<longitude>'.$this->Config()->get("default_longitude").'</longitude>'
						.'<zoom>'.$this->Config()->get("default_zoom").'</zoom>'
						.'<pointcount>'.$count.'</pointcount>'
						.'<info>'.$this->getWhereStatementDescription().'</info>'
						.'</mapinfo>'
						.$pointsXml;
		}
		else {
			$this->dataPointsStaticMapHTML .=
				"&amp;center=".$this->Config()->get("default_latitude").",".$this->Config()->get("default_longitude").
				"&amp;zoom=".$this->Config()->get("default_zoom");
		}
		$this->dataPointsStaticMapHTML = self::make_static_map_url_into_image($this->dataPointsStaticMapHTML, $this->dataObjectTitle);
		return true;
	}

	/**
	 * @return String (Javascript)
	 */
	protected function createJavascript() {
		$js = '
		function loadSunnySideUpMap() {
			GMO = new GMC(
				"map",
				null,
				 {
					/* HELPDIVS */
					sideBarId:"'.$this->config()->get("side_bar_div_id").'",
					dropBoxId:"'.$this->config()->get("drop_down_div_id").'",
					titleId:"'.$this->config()->get("title_div_id").'",
					layerListId:"'.$this->config()->get("layer_list_div_id").'",
					directionsDivId:"'.$this->config()->get("directions_div_id").'",
					statusDivId:"'.$this->config()->get("status_div_id").'",
					noStatusAtAll:'.$this->showFalseOrTrue($this->config()->get("no_status_at_all")).',
					addKmlLink:'.$this->showFalseOrTrue($this->config()->get("add_kml_link")).',
					hiddenLayersRemovedFromList:'.$this->showFalseOrTrue($this->config()->get("hidden_layers_removed_from_list")).',

					/* PAGE*/
					changePageTitle:'.$this->showFalseOrTrue($this->config()->get("change_page_title")).',
					defaultTitle:"'.$this->config()->get("default_title").'",

					/* INFOWINDOW*/
					infoWindowOptions:'.$this->config()->get("info_window_options").',
					addAntipodean:'.$this->showFalseOrTrue($this->config()->get("add_antipodean")).',
					addDirections:'.$this->showFalseOrTrue($this->config()->get("add_directions")).',
					addCurrentAddressFinder:'.$this->showFalseOrTrue($this->config()->get("add_current_address_finder")).',
					addZoomInButton:"'.$this->config()->get("add_zoom_in_button").'",
					addCloseUpButton:"'.$this->config()->get("add_close_up_button").'",
					addCloseWindowButton:"'.$this->config()->get("add_close_window_button").'",

					/* MARKER */
					addPointsToMap:'.$this->showFalseOrTrue($this->config()->get("add_points_to_map")).',
					addDeleteMarkerButton:"'.$this->config()->get("add_delete_marker_button").'",
					markerOptions: '.$this->config()->get("marker_options").',
					preloadImages:'.$this->showFalseOrTrue($this->config()->get("preload_images")).',

					/* ICONS */
					defaultIconUrl: "'.$this->config()->get("default_icon_url").'",
					iconFolder: "'.$this->config()->get("icon_folder").'",
					iconWidth:'.$this->config()->get("icon_width").',
					iconHeight:'.$this->config()->get("icon_height").',
					iconImageMap:'.$this->config()->get("icon_image_map").',
					iconExtension:"'.$this->config()->get("icon_extension").'",
					iconMaxCount:'.$this->config()->get("icon_max_count").',

					/* POLYS */
					lineColour: "'.$this->config()->get("line_colour").'",
					lineWidth: "'.$this->config()->get("line_width").'",
					lineOpacity: "'.$this->config()->get("line_opacity").'",
					fillColour: "'.$this->config()->get("fill_colour").'",
					fillOpacity: "'.$this->config()->get("fill_opacity").'",
					polyIcon: "'.$this->config()->get("poly_icon").'",

					/* MAP*/
					mapTypeDefaultZeroToTwo: '.intval($this->config()->get("map_type_default_zero_to_two")-0).',
					viewFinderSize:'.intval($this->config()->get("view_finder_size") - 0).',
					mapAddTypeControl:'.$this->showFalseOrTrue($this->config()->get("map_add_type_control")).',
					mapControlSizeOneToThree:'.$this->config()->get("map_control_size_one_to_three").',
					mapScaleInfoSizeInPixels:'.intval($this->config()->get("map_scale_info_size_in_pixels") - 0).',

					/* START POSITION */
					defaultLatitude:'.floatval($this->config()->get("default_latitude") - 0 ).',
					defaultLongitude:'.floatval($this->config()->get("default_longitude") - 0).',
					defaultZoom:'.intval($this->config()->get("default_zoom")  - 0).',

					/* SERVER INTERACTION */
					updateServerUrlAddressSearchPoint: "'.$this->getUpdateServerUrlAddressSearchPoint(). '",
					updateServerUrlDragend: "'.$this->getUpdateServerUrlDragend().'",
					latFormFieldId:"'.$this->config()->get("lat_form_field_id").'",
					lngFormFieldId:"'.$this->config()->get("lng_form_field_id").'",

					/* ADDRESS FORM */
					addAddressFinder:'.$this->showFalseOrTrue($this->config()->get("add_address_finder")).',
					defaultCountryCode:"'.$this->config()->get("default_country_code").'",
					defaultAddressText:"'.$this->config()->get("DefaultAddressText").'",

					/* DIRECTIONS */
					styleSheetUrl: "'.$this->config()->get("style_sheet_url").'",
					localeForResults: "'.$this->config()->get("locale_for_results").'"
				 }
			);
		}
		function initiateGoogleMap() {
			if(!scriptsLoaded) {
				alert("load interactive map by clicking on it");
			}
			else {
				loadSunnySideUpMap();';
		if($this->linksForData && count($this->linksForData)) {
			foreach($this->linksForData as $link) {
				$js .= '
				'.$this->getMyMapFunctionName().'.addLayer("'.Director::absoluteBaseURL().$link.'");';
			}
		}
		elseif($this->address) {
			$js .= '
				'.$this->getMyMapFunctionName().'.findAddress(\''.$this->address.'\')';
		}
		$js .= '
			}
		}';
		return $js;
	}

	/**
	 * turns 0 into false and 1 into true
	 * @param Mixed
	 * @return String (true|false)
	 */
	protected function showFalseOrTrue($v) {
		if($v === true || 1 == $v) {
			return "true";
		}
		else{
			return "false";
		}
	}

	/**
	 * load some defaults
	 */
	protected function loadDefaults() {
		if(!isset($this->whereStatementDescription)) {
			$this->whereStatementDescription = $this->Config()->get("default_where_statement_description");
		}
		if(!isset($this->dataObjectTitle)) {
			$this->dataObjectTitle = $this->Config()->get("default_title");
		}
	}

}


