<?php
/**

**/
class GoogleMap extends ViewableData {

	private static $includes_are_done = false;// this is a hack to avoid having multiple includes

	private static $GoogleMapAPIKey = "";

	/* SUNDRY */
	private static $uses_sensor = true;

	/* INFORMATION AROUND THE MAP */
	private static $DefaultTitle = ""; //MOVE TO SITECONFIG
	private static $NoStatusAtAll = false; //MOVE TO SITECONFIG
	private static $AddKmlLink = false; //MOVE TO SITECONFIG
	private static $HiddenLayersRemovedFromList = false;
	private static $ChangePageTitle = false; //MOVE TO SITECONFIG
	private static $number_of_items_before_showing_list = 1; //MOVE TO SITECONFIG

	/* DIVS */
	private static $TitleDivId = "";
		public function getTitleDivID() {return Config::inst()->get("GoogleMap", "TitleDivId");}

	private static $SideBarDivId = "";
		public function getSideBarDivId() {return Config::inst()->get("GoogleMap", "SideBarDivId");}

	private static $DropDownDivId	="";
		public function getDropDownDivId() {return Config::inst()->get("GoogleMap", "DropDownDivId");}

	private static $LayerListDivId = "";
		public function getLayerListDivId() {return Config::inst()->get("GoogleMap", "LayerListDivId");}

	private static $DirectionsDivId = "";
		public function getDirectionsDivId() {return Config::inst()->get("GoogleMap", "DirectionsDivId");}

	private static $StatusDivId = "";
		public function getStatusDivId() {return Config::inst()->get("GoogleMap", "StatusDivId");}


	/* INFOWINDOW*/
	private static $InfoWindowOptions = "{maxWidth:280, zoomLevel:17, mapType:G_HYBRID_MAP}";
	private static $AddAntipodean = false; //MOVE TO SITECONFIG
	private static $AddDirections = false; //MOVE TO SITECONFIG
	private static $AddCurrentAddressFinder = false; //MOVE TO SITECONFIG
	private static $AddZoomInButton = false; //MOVE TO SITECONFIG
	private static $AddCloseUpButton = false; //MOVE TO SITECONFIG
	private static $AddCloseWindowButton = false; //MOVE TO SITECONFIG
	private static $ajax_info_window_text = "View Details"; //MOVE TO SITECONFIG


	/* MARKERS */
	private static $AddPointsToMap = false;
	private static $AddDeleteMarkerButton = "delete this point";
	private static $AllowMarkerDragAndDrop = false;
	private static $MarkerOptions = "{bouncy:true,title: \"click me\"}";
	private static $PreloadImages = false;

	/* ICONS */
	private static $DefaultIconUrl = "";
	private static $IconFolder = "/googlemap/images/icons/";
	private static $IconWidth = 20;
	private static $IconHeight = 34;
	private static $IconImageMap = "[]";
	private static $IconExtension = "png";
	private static $IconMaxCount = 12;

	/* POLYS */
	private static $LineColour = "#000";
	private static $LineWidth = 12;
	private static $LineOpacity = 0.5;
	private static $FillColour = "#ffccff";
	private static $FillOpacity = 0.5;
	private static $PolyIcon = "";

	/* MAP*/
	private static $GoogleMapWidth = 500;
		public function getGoogleMapWidth() {return Config::inst()->get("GoogleMap", "GoogleMapWidth");}
	private static $GoogleMapHeight = 500;
		public function getGoogleMapHeight() {return Config::inst()->get("GoogleMap", "GoogleMapHeight");}
	private static $MapTypeDefaultZeroToTwo = 0; //MOVE TO SITECONFIG
	private static $ViewFinderSize = 100; //MOVE TO SITECONFIG
	private static $MapAddTypeControl = false; //MOVE TO SITECONFIG
	private static $MapControlSizeOneToThree = 3; //MOVE TO SITECONFIG
	private static $MapScaleInfoSizeInPixels = 100; //MOVE TO SITECONFIG
	private static $DefaultLatitude = 0.000000001; //MOVE TO SITECONFIG
	private static $DefaultLongitude = 0.0000000001; //MOVE TO SITECONFIG
	private static $DefaultZoom = 0; //MOVE TO SITECONFIG
	private static $ShowStaticMapFirst = 0; //MOVE TO SITECONFIG
		public function getShowStaticMapFirst() {
			return (
				!Config::inst()->get("GoogleMap", "ShowStaticMapFirst") ||
				Session::get("StaticMapsOff")
			) ? false : true;
		}
	private static $number_shown_in_around_me = 7; //MOVE TO SITECONFIG

	/* STATIC MAP */
	private static $StaticMapSettings = "maptype=roadmap";
	private static $StaticIcon = "";
	private static $LatFormFieldId = "";
	private static $LngFormFieldId = "";
	private static $save_static_map_locally = false;

/* ADDRESS FINDER */
	private static $AddAddressFinder = true; //MOVE TO SITECONFIG
		public function getAddAddressFinder() {return Config::inst()->get("GoogleMap", "AddAddressFinder");}
	private static $DefaultCountryCode = "NZ";
	private static $DefaultAddressText = " New Zealand"; //MOVE TO SITECONFIG

/* DIRECTIONS SETTINGS */
	private static $StyleSheetUrl = true;
	private static $LocaleForResults = "en_NZ";

/* JS SETTINGS */

/* DATA OBJECT */
	public $dataPointsXML;
	public $dataPointsObjectSet;
	public $dataPointsStaticMapHTML;

/* map data */
	protected $GooglePointsDataObject = null;
	protected $whereStatementDescription = "";
	protected $fieldNameForGoogleDataObjectWithPages = "GoogleDataPoints";
	protected $Address = "";
	protected $filteredClassNameArray = Array();

/* map titles and headings */
	protected $dataObjectTitle = "";

	/* SERVER INTERACTION */
	protected $UpdateServerUrlAddressSearchPoint = "";
	protected $UpdateServerUrlDragend = "";
	protected $ExtraLayersAsLinks = null;
	protected $linksForData = Array();

	/* Option 1 / 3 Set Address and update functions for Map */
	public function setAddress($v) {$this->Address = Convert::raw2js($v);}
	public function setUpdateServerUrlAddressSearchPoint($v) {$this->UpdateServerUrlAddressSearchPoint = Director::absoluteBaseURL().$v;}
	public function getUpdateServerURLAddressSearchPoint() {return $this->UpdateServerUrlAddressSearchPoint;}
	public function setUpdateServerUrlDragend($v) {$this->UpdateServerUrlDragend = Director::absoluteBaseURL().$v;}
	public function getUpdateServerUrlDragend() {return $this->UpdateServerUrlDragend;}
	public function allowAddPointsToMap() {self::$AddPointsToMap = true;}

	public function addExtraLayersAsLinks($Title, $Link) {
		if(!$this->ExtraLayersAsLinks) {
			$this->ExtraLayersAsLinks = new ArrayList();
		}
		$this->ExtraLayersAsLinks->push(new ArrayData(array("Title" => $Title, "Link" => $Link)));
	}

	public function AllExtraLayersAsLinks() {
		if(!$this->getShowStaticMapFirst()) {
			return $this->ExtraLayersAsLinks;
		}
	}

	public function canEdit($member = null) {
		if($this->UpdateServerUrlDragend) {
			return true;
		}
	}

	public function orderItemsByLatitude($unsortedSet = null) {
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
		foreach(array_reverse($tempArray) as $item) {
			$sortedSet->push($item);
		}
		return $sortedSet;
	}
	/* Option 2 / 3 Set Page DataObject for Map */
	public function setPageDataObjectSet($PageDataObjectSet) {
		if(count($PageDataObjectSet)) {
			$where = "ParentID IN (-1 ";
			foreach($PageDataObjectSet as $page) {
				if($page->HasGeoInfo) {
					$where .= ", ".$page->ID;
				}
			}
			$where .= ') ';
			$this->GooglePointsDataObject = GoogleMapLocationsObject::get()->where($where);
			$PageDataObjectSet = null;
		}
	}

	/* Option 3 / 3 Set Points Directly Class = GoogleMapLocationsObject */
	public function setGooglePointsDataObject($GooglePointsDataObject) {
		$this->GooglePointsDataObject = $GooglePointsDataObject;
	}

	/* extra 1: set title */
	public function setDataObjectTitle($dataObjectTitle) {
		$this->dataObjectTitle = $dataObjectTitle;
	}
	/* extra 1: set where statement description */
	public function setWhereStatementDescription($whereStatementDescription) {
		$this->whereStatementDescription = $whereStatementDescription;
	}
	/* FILTER DATA e.g. Page, BusinessPage */
	public function setFilteredClassNameArray($array) {
		$this->filteredClassNameArray= $array;
	}

	function addLayer($linkForData) {
		echo $linkForData;
		$this->linksForData[] = $linkForData;
	}

	/* Load Google Map into page  */
	public function loadGoogleMap() {
		$js = '';
		$this->loadDefaults();
		if(!self::$includes_are_done) {
			Requirements::themedCSS("googleMap");
			Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
			Requirements::javascript("googlemap/javascript/googleMapStatic.js");
			Requirements::javascript("googlemap/javascript/loadAjaxInfoWindow.js");
			Requirements::insertHeadTags('<style type="text/css">v\:* {behavior:url(#default#VML);}</style>', "GoogleMapCustomHeadTag");
			if(!$this->getShowStaticMapFirst()) {
				Requirements::javascript("http://maps.google.com/maps?file=api&v=2.x&sensor".$this->showFalseOrTrue(self::$uses_sensor)."&key=". Config::inst()->get("GoogleMap", "GoogleMapAPIKey"));
				Requirements::javascript("googlemap/javascript/googleMaps.js");
				$js .= 'var scriptsLoaded = true; jQuery(document).ready( function() { initiateGoogleMap();} );';
			}
			else {
				$js .= 'var scriptsLoaded = false;';
				Requirements::javascript('http://www.google.com/jsapi?key='.Config::inst()->get("GoogleMap", "GoogleMapAPIKey"));//
			}
			$js .= 'var absoluteBaseURL = "'. Director::absoluteBaseURL() .'";';
			$js .= $this->createJavascript();
			Requirements::customScript($js, "GoogleMapCustomScript");
			self::$includes_are_done = true;
		}
	}


	public function getDataPointCount() {
		if($this->dataPointsObjectSet) {
			return $this->dataPointsObjectSet->count();
		}
		elseif($this->GooglePointsDataObject->count()){
			return $this->GooglePointsDataObject->count();
		}
		elseif(isset($_SESSION["addCustomGoogleMap"])) {
			return count($_SESSION["addCustomGoogleMap"]);
		}
		elseif($a = Session::get("addCustomGoogleMap")) {
			return count($a);
		}
		return 0;
	}

	public function EnoughPointsForAList() {
		//we were using the line below, but that did not seem to work
		//return $this->getDataPointCount() >= self::$number_of_items_before_showing_list ? true : false;
		return true;
	}

	public function Link() {
		$page = Controller::curr();
		if($page) {
			return $page->Link();
		}
	}

	/* turns 0 into false and 1 into true */
	private function showFalseOrTrue($v) {
		if($v === true || 1 == $v) {
			return "true";
		}
		else{
			return "false";
		}
	}

 /* OUTPUT DATA */
	public function createDataPoints() {
		$this->dataPointsStaticMapHTML = '';
		$this->dataPointsXML = '';
		$this->dataPointsObjectSet = new ArrayList();
		$this->loadDefaults();
		$idArray = array();
		if(self::$GoogleMapWidth > 512) { $staticMapWidth = 512;	}	else { $staticMapWidth = self::$GoogleMapWidth;	}
		if(self::$GoogleMapHeight > 512) { $staticMapHeight = 512;	}	else { $staticMapHeight = self::$GoogleMapHeight;	}
		$this->dataPointsStaticMapHTML = "size=".$staticMapWidth."x".$staticMapHeight;
		$totalCount = count($this->GooglePointsDataObject);
		if($totalCount > 0  && $totalCount < 500) {
			$count = 0;
			$pointsXml = '';
			$this->dataPointsStaticMapHTML .= '&amp;markers=';
			if(self::$DefaultIconUrl) {
				$this->dataPointsStaticMapHTML .= 'icon:'.urlencode(self::$DefaultIconUrl).'|';
			}
			//the sort works, but Google Map does not seem to care...
			//$this->GooglePointsDataObject = $this->orderItemsByLatitude($this->GooglePointsDataObject);
			foreach($this->GooglePointsDataObject as $dataPoint) {
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
							$staticIcon = self::$StaticIcon;
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
				$this->dataPointsStaticMapHTML .= '&amp;center='.$defaultCenter.'&amp;zoom='.self::$DefaultZoom;
			}
			$this->dataPointsXML =
						'<mapinfo>'.'<title>'.$this->dataObjectTitle.'</title>'
						.'<longitude>'.self::$DefaultLatitude.'</longitude>'
						.'<latitude>'.self::$DefaultLongitude.'</latitude>'
						.'<zoom>'.self::$DefaultZoom.'</zoom>'
						.'<pointcount>'.$count.'</pointcount>'
						.'<info>'.$this->whereStatementDescription.'</info>'
						.'</mapinfo>'
						.$pointsXml;
		}
		else {
			$this->dataPointsStaticMapHTML .=
				"&amp;center=".self::$DefaultLatitude.",".self::$DefaultLongitude.
				"&amp;zoom=".self::$DefaultZoom;
		}
		$this->dataPointsStaticMapHTML = self::make_static_map_url_into_image($this->dataPointsStaticMapHTML, $this->dataObjectTitle);
		return true;
	}

	/*
	* var ArrayOfLatitudeAndLongitude Array (Latitude" => 123, "Longitude" => 123, "Marker" => "red1");
	* Marker is optional
	*/

	public static function quick_static_map($ArrayOfLatitudeAndLongitude, $title) {
		$staticMapURL = '';
		$count = 0;
		if(self::$GoogleMapWidth > 512) { $staticMapWidth = 512;	}	else { $staticMapWidth = self::$GoogleMapWidth;	}
		if(self::$GoogleMapHeight > 512) { $staticMapHeight = 512;	}	else { $staticMapHeight = self::$GoogleMapHeight;	}
		$staticMapURL = "size=".$staticMapWidth."x".$staticMapHeight;
		if(count($ArrayOfLatitudeAndLongitude)) {
			$staticMapURL .= '&amp;markers=';
			if(self::$DefaultIconUrl) {
				$staticMapURL .= "icon".urlencode(self::$DefaultIconUrl)."|";
			}
			foreach($ArrayOfLatitudeAndLongitude as $row) {
				if($count) {
				 $staticMapURL .= '|';
				}
				$center = round($row["Latitude"], 6).",".round($row["Longitude"], 6);
				if(!$count) {
					$defaultCenter = $center;
				}
				$staticMapURL .= $center.",";
				if(isset($row["Marker"])) {
					$staticMapURL .= ",".$row["Marker"];
				}
				elseif(self::$StaticIcon) {
					$staticMapURL .= ",".self::$StaticIcon;
				}
				$count++;
			}
			if($count == 1) {
				$staticMapURL .= '&amp;center='.$defaultCenter.'&amp;zoom='.self::$DefaultZoom;
			}
		}
		return self::make_static_map_url_into_image($staticMapURL, $title);
	}

	private static function make_static_map_url_into_image($staticMapURL, $title) {
		$fullStaticMapURL  = '';
		$uses_sensor = "false";
		if(self::$uses_sensor) {
			$uses_sensor = "true";
		}
		$fullStaticMapURL = 'http://maps.google.com/maps/api/staticmap?sensor='.$uses_sensor.'&amp;'.self::$StaticMapSettings.'&amp;'.$staticMapURL.'&amp;key='.Config::inst()->get("GoogleMap", "GoogleMapAPIKey"); //key goes here...GoogleMapAPIKey
		if(self::$save_static_map_locally) {
			$fileName = str_replace(array('&', '|', ',', '=', ';'), array('', '', '', '', ''), $staticMapURL);
			$length = strlen($fileName);
			$fileName = "_sGMap".substr(hash("md5", $fileName), 0, 35)."_".$length.".gif";
			$fullStaticMapURL = StaticMapSaverForHTTPS::convert_to_local_file(str_replace('&amp;', '&', $fullStaticMapURL), $fileName);
		}
		return '<img class="staticGoogleMap" src="'.$fullStaticMapURL.'" alt="map: '.$title.'" />';
	}

	/* OUTPUT JAVASCRIPT */
	private function createJavascript() {
		$js = '
		function loadSunnySideUpMap() {
		 if (GBrowserIsCompatible()) {
			GMO = new GMC("map", null,
			 {
		/* HELPDIVS */
				sideBarId:"'.self::$SideBarDivId.'",
				dropBoxId:"'.self::$DropDownDivId.'",
				titleId:"'.self::$TitleDivId.'",
				layerListId:"'.self::$LayerListDivId.'",
				directionsDivId:"'.self::$DirectionsDivId.'",
				statusDivId:"'.self::$StatusDivId.'",
				noStatusAtAll:'.$this->showFalseOrTrue(self::$NoStatusAtAll).',
				addKmlLink:'.$this->showFalseOrTrue(self::$AddKmlLink).',
				hiddenLayersRemovedFromList:'.$this->showFalseOrTrue(self::$HiddenLayersRemovedFromList).',
		/* PAGE*/
				changePageTitle:'.$this->showFalseOrTrue(self::$ChangePageTitle).',
				defaultTitle:"'.self::$DefaultTitle.'",
		/* INFOWINDOW*/
				infoWindowOptions:'.self::$InfoWindowOptions.',
				addAntipodean:'.$this->showFalseOrTrue(self::$AddAntipodean).',
				addDirections:'.$this->showFalseOrTrue(self::$AddDirections).',
				addCurrentAddressFinder:'.$this->showFalseOrTrue(self::$AddCurrentAddressFinder).',
				addZoomInButton:"'.self::$AddZoomInButton.'",
				addCloseUpButton:"'.self::$AddCloseUpButton.'",
				addCloseWindowButton:"'.self::$AddCloseWindowButton.'",
		/* MARKER */
				addPointsToMap:'.$this->showFalseOrTrue(self::$AddPointsToMap).',
				addDeleteMarkerButton:"'.self::$AddDeleteMarkerButton.'",
				allowMarkerDragAndDrop:"'.$this->showFalseOrTrue(self::$AllowMarkerDragAndDrop).'",
				markerOptions: '.self::$MarkerOptions.',
				preloadImages:'.$this->showFalseOrTrue(self::$PreloadImages).',
		/* ICONS */
				defaultIconUrl: "'.self::$DefaultIconUrl.'",
				iconFolder: "'.self::$IconFolder.'",
				iconWidth:'.self::$IconWidth.',
				iconHeight:'.self::$IconHeight.',
				iconImageMap:'.self::$IconImageMap.',
				iconExtension:"'.self::$IconExtension.'",
				iconMaxCount:'.self::$IconMaxCount.',
		/* POLYS */
				lineColour: "'.self::$LineColour.'",
				lineWidth: "'.self::$LineWidth.'",
				lineOpacity: "'.self::$LineOpacity.'",
				fillColour: "'.self::$FillColour.'",
				fillOpacity: "'.self::$FillOpacity.'",
				polyIcon: "'.self::$PolyIcon.'",
		/* MAP*/
				mapTypeDefaultZeroToTwo: '.intval(self::$MapTypeDefaultZeroToTwo+0).',
				viewFinderSize:'.intval(self::$ViewFinderSize + 0).',
				mapAddTypeControl:'.$this->showFalseOrTrue(self::$MapAddTypeControl).',
				mapControlSizeOneToThree:'.self::$MapControlSizeOneToThree.',
				mapScaleInfoSizeInPixels:'.intval(self::$MapScaleInfoSizeInPixels + 0).',
		/* START POSITION*/
				defaultLatitude:'.floatval(self::$DefaultLatitude+0).',
				defaultLongitude:'.floatval(self::$DefaultLongitude+0).',
				defaultZoom:'.intval(self::$DefaultZoom+0).',
		/* SERVER INTERACTION */
				updateServerUrlAddressSearchPoint: "'.$this->getUpdateServerUrlAddressSearchPoint().'",
				updateServerUrlDragend: "'.$this->getupdateServerUrlDragend().'",
				latFormFieldId:"'.self::$LatFormFieldId.'",
				lngFormFieldId:"'.self::$LngFormFieldId.'",
		/* ADDRESS FORM */
				addAddressFinder:'.$this->showFalseOrTrue(self::$AddAddressFinder).',
				defaultCountryCode:"'.self::$DefaultCountryCode.'",
				defaultAddressText:"'.self::$DefaultAddressText.'",
		/* DIRECTIONS */
				styleSheetUrl: "'.self::$StyleSheetUrl.'",
				localeForResults: "'.self::$LocaleForResults.'"
			 }
			);
		 }
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
				addLayer("'.Director::absoluteBaseURL().$link.'");';
			}
		}
		elseif($this->Address) {
			$js .= '
				findAddress(\''.$this->Address.'\')';
		}
		$js .= '
			}
		}';
		return $js;
	}

	private function loadDefaults() {
		if(!isset($this->whereStatementDescription)) {
			$this->whereStatementDescription = self::$WhereStatementDescription;
		}
		if(!isset($this->dataObjectTitle)) {
			$this->dataObjectTitle = self::$dataObjectTitle;
		}
	}

}


