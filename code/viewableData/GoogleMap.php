<?php
/**
 * This is the basic engine for the Google Map
 *
 * There are three options
 * 1. setAddress: set address
 * 2. setPageDataObjectSet: set Page Data List (who have geo points as children)
 * 3. setPoints: set Points Directly Class = GoogleMapLocationsObject
 *
 * Extras include:
 * 1. setTitleOfMap: set title
 * 2. setWhereStatementDescription: set where statement
 * 3. setFilteredClassNameArray: set an array of classnames
 *
 * The static configs are explained in googlemap/_config/googlemap.yml.example
 *
 *
 **/
class GoogleMap extends ViewableData {



	################################
	# SETTINGS
	################################


	/* BASIC MAP SETTINGS */
	private static $api_version = "3.20";
	private static $google_map_api_key = "";
	private static $default_latitude = 0.000000001; //MOVE TO SITECONFIG
	private static $default_longitude = 0.0000000001; //MOVE TO SITECONFIG
	private static $default_zoom = 2; //MOVE TO SITECONFIG
	private static $google_map_width = 500;
	private static $google_map_height = 500;

	/* MAP CONTROLS*/
	private static $map_type_default = 0; //MOVE TO SITECONFIG
	private static $view_finder_size = 100; //MOVE TO SITECONFIG
	private static $map_add_type_control = false; //MOVE TO SITECONFIG
	private static $map_control_size_one_to_three = 3; //MOVE TO SITECONFIG
	private static $map_scale_info_size_in_pixels = 100; //MOVE TO SITECONFIG

	/* INFORMATION AROUND THE MAP */
	private static $default_title = ""; //MOVE TO SITECONFIG
	private static $default_where_statement_description = ""; //MOVE TO SITECONFIG
	private static $no_status_at_all = true; //MOVE TO SITECONFIG
	private static $add_kml_link = false; //MOVE TO SITECONFIG
	private static $hidden_layers_removed_from_list = false;
	private static $change_page_title = false; //MOVE TO SITECONFIG
	private static $number_of_items_before_showing_list = 1; //MOVE TO SITECONFIG
	private static $title_div_id = "";
	private static $side_bar_div_id = "";
	private static $drop_down_div_id	="";
	private static $layer_list_div_id = "";
	private static $directions_div_id = "";
	private static $status_div_id = "";

	/* INFOWINDOW*/
	private static $info_window_options = "{maxWidth:280, zoomLevel:17, mapTypeId: google.maps.MapTypeId.HYBRID}";
	private static $add_antipodean = false; //MOVE TO SITECONFIG
	private static $add_directions = false; //MOVE TO SITECONFIG
	private static $add_current_address_finder = false; //MOVE TO SITECONFIG
	private static $add_zoom_in_button = true; //MOVE TO SITECONFIG
	private static $ajax_info_window_text = "View Details"; //MOVE TO SITECONFIG

	/* MARKERS */
	private static $add_points_to_map = false;
	private static $add_delete_marker_button = "";
	private static $marker_options = "{bouncy:true,title: \"click me\"}";
	private static $preload_images = false;

	/* ICONS */
	private static $default_icon_url = "";
	private static $icon_folder = "/googlemap/images/icons/";
	private static $icon_width = 20;
	private static $icon_height = 34;
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
	private static $static_map_settings = "maptype=roadmap";
	private static $static_icon = "";
	private static $save_static_map_locally = false;

	/* ADDRESS FINDER */
	private static $add_address_finder = false; //MOVE TO SITECONFIG
	private static $default_country_code = "nz"; // see https://developers.google.com/maps/documentation/geocoding/#RegionCodes
	private static $number_shown_in_around_me = 7; //MOVE TO SITECONFIG

	/* DIRECTIONS SETTINGS */
	private static $style_sheet_url = "";
	private static $locale_for_results = "en_NZ";

	/* SERVER SETTINGS */
	private static $lng_form_field_id = "";
	private static $lat_form_field_id = "";







	################################
	# TEMPATE METHODS
	################################

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
	 * function name for map...
	 * @var String
	 */
	protected $myMapFunctionName = "GMO";
		public function setMyMapFunctionName($a){$this->myMapFunctionName = $s;}
		public function MyMapFunctionName(){return $this->getMyMapFunctionName(false);}
		public function MyInstanceName(){return $this->getMyMapFunctionName(true);}
		public function getMyMapFunctionName($instanceName = false){
			if($instanceName) {
				$var = 'load_my_map_'.$this->myMapFunctionName;
			}
			else {
				$var = $this->myMapFunctionName;
			}
			return $var;
		}

	/**
	 * title of map
	 * @var String
	 */
	protected $titleOfMap = "";
		public function setTitleOfMap($s){$this->titleOfMap = $s;}
		public function TitleOfMap(){return $this->getTitleOfMap();}
		public function getTitleOfMap(){return $this->titleOfMap;}


	/**
	 * used for static and non-static maps, hence defined only once.
	 * @return Int
	 */
	public function GoogleMapWidth() {return Config::inst()->get("GoogleMap", "google_map_width");}

	/**
	 * used for static and non-static maps, hence defined only once.
	 * @return Int
	 */
	public function GoogleMapHeight() {return Config::inst()->get("GoogleMap", "google_map_height");}

	/**
	 * @return Boolean
	 */
	public function AddAddressFinder() {return Config::inst()->get("GoogleMap", "add_address_finder");}

	/**
	 * @return Boolean
	 */
	public function CanEdit($member = null) {
		if($this->getUpdateServerUrlDragend()) {
			if(Injector::inst()->get("GoogleMapLocationsObject")->canEdit($member)) {
				return true;
			}
		}
	}

	/**
	 * @return String
	 */
	public function TitleDivID() {return Config::inst()->get("GoogleMap", "title_div_id");}

	/**
	 * @return String
	 */
	public function SideBarDivId() {return Config::inst()->get("GoogleMap", "side_bar_div_id");}

	/**
	 * @return String
	 */
	public function DropDownDivId() {return Config::inst()->get("GoogleMap", "drop_down_div_id");}

	/**
	 * @return String
	 */
	public function LayerListDivId() {return Config::inst()->get("GoogleMap", "layer_list_div_id");}

	/**
	 * @return String
	 */
	public function DirectionsDivId() {return Config::inst()->get("GoogleMap", "directions_div_id");}

	/**
	 * @return String
	 */
	public function StatusDivId() {return Config::inst()->get("GoogleMap", "status_div_id");}

	/**
	 *
	 * @var Boolean
	 */
	public function AllowAddPointsToMap() {$this->Config()->update("add_points_to_map", true);}

	/**
	 * @var ArrayList
	 */
	protected $processedDataPointsForTemplate;
		public function setProcessedDataPointsForTemplate($s){$this->processedDataPointsForTemplate = $s;}
		public function ProcessedDataPointsForTemplate(){return $this->getProcessedDataPointsForTemplate();}
		public function getProcessedDataPointsForTemplate(){return $this->processedDataPointsForTemplate;}

	/**
	 * @var String
	 */
	protected $dataPointsXML;
		protected function DataPointsXML(){return $this->getDataPointsXML();}
		protected function getDataPointsXML(){return $this->dataPointsXML;}











	################################
	# SETUP: LAYER MANAGEMENT
	################################




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
			$this->extraLayersAsLinks->push(
				new ArrayData(
					array(
						"Title" => $title,
						"Link" => $link,
						"MyInstanceName" => $this->MyInstanceName()
					)
				)
			);
		}

		/**
		 *
		 * @return ArrayList
		 */
		public function AllExtraLayersAsLinks() {
			return $this->getExtraLayersAsLinks();
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
			if(!in_array($linkForData, $this->linksForData)) {
				$this->linksForData[] = $linkForData;
			}
		}







	################################
	# SETUP: FILTERS AND POINTS PROVIDERS
	################################

	/**
	 * address being searched for
	 * @var String
	 */
	protected $address = "";
		public function setAddress($v) {$this->address = Convert::raw2js($v);}
		public function getAddress($v) {return $this->address;}

		/**
		 * sets the list of points through a list of parent pages
		 * affected variable is: points
		 * @param DataList | ArrayList $pageDataList
		 */
		public function setPageDataObjectSet($pageDataList) {
			if($pageDataList->count()) {
				if($pageDataList instanceof ArrayList) {
					$array = $pageDataList->map("ID", "ID");
				}
				elseif($pageDataList instanceof DataList) {
					$array = $pageDataList->map("ID", "ID")->toArray();
				}
				else {
					user_error("Wrong format for pageDataList")
				}
				$this->points = GoogleMapLocationsObject::get()->filter(array("ParentID" => $array));
				$pageDataList = null;
			}
		}


	/**
	 * @var DataList
	 */
	protected $points = null;
		public function setPoints($s) {$this->points = $s;}
		public function getPoints() {
			if(!$this->points) {
				$this->points = get_custom_google_map_session_data($this->sessionId, $this->sessionAction);
			}
			return $this->points;
		}
				


	/**
	 * a description of how the points were selected ...
	 * @var String
	 */
	protected $whereStatementDescription = "";
		public function setWhereStatementDescription($s) {$this->whereStatementDescription = $s;}
		public function getWhereStatementDescription() {return $this->whereStatementDescription;}

	/**
	 * filter for class names
	 * @var Array
	 */
	protected $filteredClassNameArray = Array();
		public function setFilteredClassNameArray($a) {$this->filteredClassNameArray = $s;}
		public function getFilteredClassNameArray() {return $this->filteredClassNameArray;}







	################################
	# MAP CHANGES
	################################

	/**
	 * @var String
	 */
	protected $updateServerUrlAddressSearchPoint = "/googlemap/showaroundmexml/";
		public function setUpdateServerUrlAddressSearchPoint($v) {$this->updateServerUrlAddressSearchPoint = Director::absoluteBaseURL().$v;}
		public function getUpdateServerURLAddressSearchPoint() {return $this->updateServerUrlAddressSearchPoint;}

	/**
	 * @var String
	 */
	protected $updateServerUrlDragend = "";
		public function setUpdateServerUrlDragend($v) {$this->updateServerUrlDragend = Director::absoluteBaseURL().$v;}
		public function getUpdateServerUrlDragend() {return $this->updateServerUrlDragend;}










	################################
	# LOADING
	################################
	/**
	 * this is a hack to avoid having multiple includes
	 * @var Boolean
	 */
	private static $_includes_are_done = false;

	public function loadGoogleMap() {
		$js = '';
		$this->loadDefaults();
		if(!self::$_includes_are_done) {
			Requirements::themedCSS("googleMap");
			Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
			$variableName = $this->getMyMapFunctionName(false);
			$instanceName = $this->getMyMapFunctionName(true);
			Requirements::javascript("googlemap/javascript/loadAjaxInfoWindow.js");
			Requirements::insertHeadTags('<style type="text/css">v\:* {behavior:url(#default#VML);}</style>', "GoogleMapCustomHeadTag");
			Requirements::javascript(
				"//maps.googleapis.com/maps/api/js?"
				."v=".Config::inst()->get("GoogleMap", "api_version")
				."&libraries=places"
			);
			Requirements::javascript("googlemap/javascript/googleMaps.js");
			$js .= "\r\n\t\t\tjQuery(document).ready( function() { initiateGoogleMap();} );\r\n";
			$js .= $this->createJavascript();
			Requirements::customScript($js, "GoogleMapCustomScript");
			self::$_includes_are_done = true;
		}
	}







	################################
	# DATA POINTS MASSAGE
	################################

	/**
	 * sorts points by Latitude
	 * @param boolean $reverse
	 *
	 * @return ArrayList
	 */
	protected function orderItemsByLatitude($reverse = false) {
		if(!$unsortedSet) {
			$unsortedSet = $this->getProcessedDataPointsForTemplate();
		}
		$sortedSet = new ArrayList();
		if($unsortedSet->count()) {
			foreach($unsortedSet as $item) {
				$tempArray[$item->Latitude] = $item;
			}
		}
		ksort($tempArray);
		//not sure why this is a bit counter intuitive.
		//north to south is from high to low ... 
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
	public function getPointCount() {
		if($this->getProcessedDataPointsForTemplate()) {
			return $this->processedDataPointsForTemplate->count();
		}
		elseif($points = $this->getPoints()){
			return $points->count();
		}
		return 0;
	}

	/**
	 * @return Boolean
	 */
	public function EnoughPointsForAList() {
		return $this->getPointCount() >= $this->Config()->get("number_of_items_before_showing_list") ? true : false;
	}


	/**
	 * must be public
	 * does not return the datapoints XML
	 * but loads it into variables...
	 * @return Boolean
	 */
	public function createDataPoints() {
		$this->dataPointsXML = '';
		$this->processedDataPointsForTemplate = new ArrayList();
		$this->loadDefaults();
		$idArray = array();
		$bestZoom = $this->Config()->get("default_zoom");
		$averageLatitude = 0;
		$averageLongitude = 0;
		//width
		$totalCount = $this->getPointCount();
		$filterFree = count($this->filteredClassNameArray) ? false : true;
		$averageLongitudeArray = array();
		$averageLatitudeArray = array();
		if($totalCount > 0  && $totalCount < 10000) {
			$count = 0;
			$pointsXml = '';
			foreach($this->getPoints() as $dataPoint) {
				$dataPoint->addParentData();
				if($filterFree || in_array($dataPoint->ClassName, $this->filteredClassNameArray)) {
					if(!in_array($dataPoint->ID, $idArray)) {
						if($dataPoint->Longitude && $dataPoint->Latitude) {
							$dataLine = '<Point><coordinates>'.$dataPoint->Longitude.','.$dataPoint->Latitude.'</coordinates></Point>';
							$link = '';
							if($dataPoint->Link) {
								$link = $dataPoint->getAjaxInfoWindowLink();
							}
							$staticIcon = '';
							if($dataPoint->staticIcon) {
								$staticIcon = $dataPoint->staticIcon;
							}
							else {
								$staticIcon = $this->Config()->get("static_icon");
							}
							$center = round($dataPoint->Latitude, 6).",".round($dataPoint->Longitude, 6);
							//get the center IF there is only one point...
							if(!$count) {
								$defaultCenter = $center;
							}
							$pointsXml .=
										'<Placemark>'.
										'<id>'.$dataPoint->ID.'</id>'.
										'<name>'.Convert::raw2xml($dataPoint->Name).'</name>'.
										$dataLine.
										'<description><![CDATA[ '.$dataPoint->getAjaxInfoWindowLink().']]></description>'.
										'</Placemark>';
							$this->processedDataPointsForTemplate->push($dataPoint);
							$averageLatitudeArray[] = $dataPoint->Longitude;
							$averageLongitudeArray[] = $dataPoint->Latitude;
							$count++;
						}
					}
				}
				$idArray[$dataPoint->ID] = $dataPoint->ID;
			}
			$averageLongitude = array_sum($averageLongitudeArray) / count($averageLongitudeArray);
			$averageLatitude = array_sum($averageLatitudeArray) / count($averageLatitudeArray);
			if(!$averageLongitude) {
				$averageLongitude = $this->config()->get("default_longitude");
			}
			if(!$averageLatitude) {
				$averageLatitude = $this->config()->get("default_latitude");
			}
			$this->dataPointsXML =
						'<mapinfo>'.'<title>'.$this->getTitleOfMap().'</title>'
						.'<longitude>'.number_format($averageLongitude - 0 , 12, ".", "").'</longitude>'
						.'<latitude>'.number_format($averageLatitude - 0 , 9, ".", "").'</latitude>'
						.'<zoom>'.$bestZoom.'</zoom>'
						.'<pointcount>'.$count.'</pointcount>'
						.'<info>'.$this->getWhereStatementDescription().'</info>'
						.'</mapinfo>'
						.$pointsXml;
			$this->processedDataPointsForTemplate = $this->orderItemsByLatitude($this->processedDataPointsForTemplate);
		}
		return true;
	}

	/**
	 * @param String staticVariablename
	 * @return String (Javascript)
	 */
	protected function createJavascript() {
			$variableName = $this->getMyMapFunctionName(false);
			$instanceName = $this->getMyMapFunctionName(true);
		$js = '
			var '.$instanceName.' = new GoogleMapConstructor(
				"GoogleMapDiv",
				null,
				"'.$variableName.'",
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
					mapTypeDefault: '.intval($this->config()->get("map_type_default")-0).',
					viewFinderSize:'.intval($this->config()->get("view_finder_size") - 0).',
					mapAddTypeControl:'.$this->showFalseOrTrue($this->config()->get("map_add_type_control")).',
					mapControlSizeOneToThree:'.$this->config()->get("map_control_size_one_to_three").',
					mapScaleInfoSizeInPixels:'.intval($this->config()->get("map_scale_info_size_in_pixels") - 0).',

					/* START POSITION */
					defaultLatitude:'.number_format($this->config()->get("default_latitude") - 0 , 9, ".", "").',
					defaultLongitude:'.number_format($this->config()->get("default_longitude") - 0 , 12, ".", "").',
					defaultZoom:'.intval($this->config()->get("default_zoom")  - 0).',

					/* SERVER INTERACTION */
					updateServerUrlAddressSearchPoint: "'.$this->getUpdateServerUrlAddressSearchPoint(). '",
					updateServerUrlDragend: "'.$this->getUpdateServerUrlDragend().'",
					latFormFieldId:"'.$this->config()->get("lat_form_field_id").'",
					lngFormFieldId:"'.$this->config()->get("lng_form_field_id").'",

					/* ADDRESS FORM */
					addAddressFinder:'.$this->showFalseOrTrue($this->config()->get("add_address_finder")).',
					defaultCountryCode:"'.$this->config()->get("default_country_code").'",

					/* DIRECTIONS */
					styleSheetUrl: "'.$this->config()->get("style_sheet_url").'",
					localeForResults: "'.$this->config()->get("locale_for_results").'"
				}
			);
			function initiateGoogleMap() {
				'.$instanceName.'.init();';
		if($this->linksForData && count($this->linksForData)) {
			foreach($this->linksForData as $link) {
				$js .= '
				'.$instanceName.'.addLayer("'.Director::absoluteBaseURL().$link.'");';
			}
		}
		elseif($this->address) {
			$js .= '
				'.$instanceName.'.findAddress(\''.$this->address.'\')';
		}
		$js .= '
			}';
		return $js;
	}

	/**
	 * turns 0 into false and 1 into true
	 * @param Mixed
	 * @return string (true|false)
	 */
	protected function showFalseOrTrue($v) {
		if($v && $v !== "false" && $v !== "0" && $v !== 0) {
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
		if(!isset($this->titleOfMap) || !$this->titleOfMap) {
			$this->titleOfMap = $this->Config()->get("default_title");
		}
	}

}


