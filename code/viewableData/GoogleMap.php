<?php
/**

**/
class GoogleMap extends ViewableData {

	private static $includes_are_done = false;// this is a hack to avoid having multiple includes

	/* SUNDRY */
	protected static $uses_sensor = true;
		static function set_uses_sensor($v) {self::$uses_sensor = $v;}

	/* INFORMATION AROUND THE MAP */
	protected static $DefaultTitle = ""; //MOVE TO SITECONFIG
		static function setDefaultTitle($v){self::$DefaultTitle = $v;}
	protected static $NoStatusAtAll = false; //MOVE TO SITECONFIG
		static function setNoStatusAtAll($v) {self::$NoStatusAtAll = $v;}
	protected static $AddKmlLink = false; //MOVE TO SITECONFIG
		static function setAddKmlLink($v){self::$AddKmlLink = $v;}
	protected static $HiddenLayersRemovedFromList = false;
		static function setHiddenLayersRemovedFromList($v){self::$HiddenLayersRemovedFromList = $v;}
	protected static $ChangePageTitle = false; //MOVE TO SITECONFIG
		static function setChangePageTitle($v){self::$ChangePageTitle = $v;}
	protected static $number_of_items_before_showing_list = 1; //MOVE TO SITECONFIG
		static function set_number_of_items_before_showing_list($v){self::$number_of_items_before_showing_list = $v;}
		static function get_number_of_items_before_showing_list($v){return self::$number_of_items_before_showing_list;}

	/* DIVS */
	protected static $TitleDivId = "";
		static function setTitleDivId($v) {self::$TitleDivId = $v;}
		public function getTitleDivID() {return self::$TitleDivId;}
	protected static $SideBarDivId = "";
		static function setSideBarDivId($v){self::$SideBarDivId = $v; }
		public function getSideBarDivId() {return self::$SideBarDivId;}
	protected static $DropDownDivId	="";
		static function setDropDownDivId($v) {self::$DropDownDivId = $v; }
		public function getDropDownDivId() {return self::$DropDownDivId;}
	protected static $LayerListDivId = "";
		static function setLayerListDivId($v) {self::$LayerListDivId = $v; }
		public function getLayerListDivId() {return self::$LayerListDivId;}
	protected static $DirectionsDivId = "";
		static function setDirectionsDivId($v) {self::$DirectionsDivId = $v; }
		public function getDirectionsDivId() {return self::$DirectionsDivId;}
	protected static $StatusDivId = "";
		static function setStatusDivId($v) {self::$StatusDivId = $v; }
		public function getStatusDivId() {return self::$StatusDivId;}


	/* INFOWINDOW*/
	protected static $InfoWindowOptions = "{maxWidth:280, zoomLevel:17, mapType:G_HYBRID_MAP}";
		static function setInfoWindowOptions($v) {self::$InfoWindowOptions = $v;}
	protected static $AddAntipodean = false; //MOVE TO SITECONFIG
		static function setAddAntipodean($v) {self::$AddAntipodean = $v;}
	protected static $AddDirections = false; //MOVE TO SITECONFIG
		static function setAddDirections($v) {self::$AddDirections = $v;}
	protected static $AddCurrentAddressFinder = false; //MOVE TO SITECONFIG
		static function setAddCurrentAddressFinder($v) {self::$AddCurrentAddressFinder = $v;}
	protected static $AddZoomInButton = false; //MOVE TO SITECONFIG
		static function setAddZoomInButton($v) {self::$AddZoomInButton = $v;}
	protected static $AddCloseUpButton = false; //MOVE TO SITECONFIG
		static function setAddCloseUpButton($v) {self::$AddCloseUpButton = $v;}
	protected static $AddCloseWindowButton = false; //MOVE TO SITECONFIG
		static function setAddCloseWindowButton($v) {self::$AddCloseWindowButton = $v;}
	static $ajax_info_window_text = "View Details"; //MOVE TO SITECONFIG
		static function get_ajax_info_window_text() {return self::$ajax_info_window_text;}
		static function set_ajax_info_window_text($v) {self::$ajax_info_window_text = $v;}


	/* MARKERS */
	protected static $AddPointsToMap = false;
		static function setAddPointsToMap($v) {self::$AddPointsToMap = $v;}
	protected static $AddDeleteMarkerButton = "delete this point";
		static function setAddDeleteMarkerButton($v) {self::$AddDeleteMarkerButton = $v;}
	protected static $AllowMarkerDragAndDrop = false;
		static function setAllowMarkerDragAndDrop($v) {self::$AllowMarkerDragAndDrop = $v;}
	protected static $MarkerOptions = "{bouncy:true,title: \"click me\"}";
		static function setMarkerOptions($v) {self::$MarkerOptions = $v;}
	protected static $PreloadImages = false;
		static function setPreloadImages($v) {self::$PreloadImages = $v;}
		
	/* ICONS */
	protected static $DefaultIconUrl = "";
		static function setDefaultIconUrl($v) {self::$DefaultIconUrl = $v;}
	protected static $IconFolder = "/googlemap/images/icons/";
		static function setIconFolder($v) {self::$IconFolder = $v;}
	protected static $IconWidth = 20;
		static function setIconWidth($v) {self::$IconWidth = $v;}
	protected static $IconHeight = 34;
		static function setIconHeight($v) {self::$IconHeight = $v;}
	protected static $IconImageMap = "[]";
		static function setIconImageMap($v) {self::$IconImageMap = $v;}
	protected static $IconExtension = "png";
		static function setIconExtension($v) {self::$IconExtension = $v;}
	protected static $IconMaxCount = 12;
		static function setIconMaxCount($v) {self::$IconMaxCount = $v;}

	/* POLYS */
	protected static $LineColour = "#000";
		static function setLineColour($v) {self::$LineColour = $v;}
	protected static $LineWidth = 12;
		static function setLineWidth($v) {self::$LineWidth = $v;}
	protected static $LineOpacity = 0.5;
		static function setLineOpacity($v) {self::$LineOpacity = $v;}
	protected static $FillColour = "#ffccff";
		static function setFillColour($v) {self::$FillColour = $v;}
	protected static $FillOpacity = 0.5;
		static function setFillOpacity($v) {self::$FillOpacity = $v;}
	protected static $PolyIcon = "";
		static function setPolyIcon($v) {self::$PolyIcon = $v;}

	/* MAP*/
	protected static $GoogleMapWidth = 500;
		static function setGoogleMapWidth($v) {self::$GoogleMapWidth = $v; }
		public function getGoogleMapWidth() {return self::$GoogleMapWidth;}
	protected static $GoogleMapHeight = 500;
		static function setGoogleMapHeight($v) {self::$GoogleMapHeight = $v; }
		public function getGoogleMapHeight() {return self::$GoogleMapHeight;}
	protected static $MapTypeDefaultZeroToTwo = 0; //MOVE TO SITECONFIG
		static function setMapTypeDefaultZeroToTwo($v) {self::$MapTypeDefaultZeroToTwo = $v;}
	protected static $ViewFinderSize = 100; //MOVE TO SITECONFIG
		static function setviewFinderSize($v) {self::$ViewFinderSize = $v;}
	protected static $MapAddTypeControl = false; //MOVE TO SITECONFIG
		static function setMapAddTypeControl($v) {self::$MapAddTypeControl = $v;}
	protected static $MapControlSizeOneToThree = 3; //MOVE TO SITECONFIG
		static function setMapControlSizeOneToThree($v) {self::$MapControlSizeOneToThree = $v;}
	protected static $MapScaleInfoSizeInPixels = 100; //MOVE TO SITECONFIG
		static function setMapScaleInfoSizeInPixels($v) {self::$MapScaleInfoSizeInPixels = $v;}
	protected static $DefaultLatitude = 0.000000001; //MOVE TO SITECONFIG
		static function setDefaultLatitude($v) {self::$DefaultLatitude = $v;}
	protected static $DefaultLongitude = 0.0000000001; //MOVE TO SITECONFIG
		static function setDefaultLongitude($v) {self::$DefaultLongitude = $v;}
	protected static $DefaultZoom = 0; //MOVE TO SITECONFIG
		static function setDefaultZoom($v) {self::$DefaultZoom = $v;}
	protected static $ShowStaticMapFirst = 0; //MOVE TO SITECONFIG
		static function setShowStaticMapFirst($v) {self::$ShowStaticMapFirst = $v; }
		public function getShowStaticMapFirst() {(!self::$ShowStaticMapFirst || Session::get("StaticMapsOff"))? false : true;}
	protected static $number_shown_in_around_me = 7; //MOVE TO SITECONFIG
		static function get_number_shown_in_around_me() {return self::$number_shown_in_around_me;}
		static function set_number_shown_in_around_me($v) {self::$number_shown_in_around_me = $v;}

	/* STATIC MAP */
	protected static $StaticMapSettings = "maptype=roadmap";
		static function setStaticMapSettings($v) {self::$StaticMapSettings = $v;}
	protected static $StaticIcon = "";
		static function setStaticIcon($v) {self::$StaticIcon = $v;}
	protected static $LatFormFieldId = "";
		static function setLatFormFieldId($v) {self::$LatFormFieldId = $v;}
	protected static $LngFormFieldId = "";
		static function setLngFormFieldId($v) {self::$LngFormFieldId = $v;}
	protected static $save_static_map_locally = false;
		static function set_save_static_map_locally($v) {self::$save_static_map_locally = $v;}

/* ADDRESS FINDER */
	protected static $AddAddressFinder = true; //MOVE TO SITECONFIG
		static function setAddAddressFinder($v) {self::$AddAddressFinder = $v;}
		public function getAddAddressFinder() {return self::$AddAddressFinder;}
	protected static $DefaultCountryCode = "NZ";
		static function setDefaultCountryCode($v) {self::$DefaultCountryCode = $v;}
	protected static $DefaultAddressText = " New Zealand"; //MOVE TO SITECONFIG
		static function setDefaultAddressText($v) {self::$DefaultAddressText = $v;}

/* DIRECTIONS SETTINGS */
	protected static $StyleSheetUrl = true;
		static function setStyleSheetUrl($v) {self::$StyleSheetUrl = $v;}
	protected static $LocaleForResults = "en_NZ";
		static function setLocaleForResults($v) {self::$LocaleForResults = $v;}

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
			$this->ExtraLayersAsLinks = new DataObjectSet();
		}
		$this->ExtraLayersAsLinks->push(new ArrayData(array("Title" => $Title, "Link" => $Link)));
	}

	public function AllExtraLayersAsLinks() {
		if(!$this->getShowStaticMapFirst()) {
			return $this->ExtraLayersAsLinks;
		}
	}

	public function canEdit() {
		if($this->UpdateServerUrlDragend) {
			return true;
		}
	}

	public function orderItemsByLatitude($unsortedSet = null) {
		if(!$unsortedSet) {
			$unsortedSet = $this->dataPointsObjectSet;
		}
		$sortedSet = new DataObjectSet();
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
			$this->GooglePointsDataObject = DataObject::get("GoogleMapLocationsObject", $where);
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
		$this->linksForData[] = $linkForData;
	}

	/* Load Google Map into page  */
	public function loadGoogleMap() {
		$js = '';
		$this->loadDefaults();
		if(!self::$includes_are_done) {
			Requirements::themedCSS("googleMap");
			Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
			Requirements::javascript('googlemap/javascript/googleMapStatic.js');
			Requirements::javascript("googlemap/javascript/loadAjaxInfoWindow.js");
			Requirements::insertHeadTags('<style type="text/css">v\:* {behavior:url(#default#VML);}</style>', "GoogleMapCustomHeadTag");
			if(!$this->getShowStaticMapFirst()) {
				Requirements::javascript("http://maps.google.com/maps?file=api&amp;v=2.x&amp;&amp;sensor".$this->showFalseOrTrue(self::$uses_sensor)."&amp;key=".GoogleMapAPIKey);
				Requirements::javascript("googlemap/javascript/googleMaps.js");
				$js .= 'var scriptsLoaded = true; jQuery(document).ready( function() { initiateGoogleMap();} );';
			}
			else {
				$js .= 'var scriptsLoaded = false;';
				Requirements::javascript('http://www.google.com/jsapi?key='.GoogleMapAPIKey);
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
		elseif($this->GooglePointsDataObject){
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
		$page = Director::currentPage();
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
		$this->dataPointsObjectSet = New DataObjectSet();
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

	static function quick_static_map($ArrayOfLatitudeAndLongitude, $title) {
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
		$fullStaticMapURL = 'http://maps.google.com/maps/api/staticmap?sensor='.$uses_sensor.'&amp;'.self::$StaticMapSettings.'&amp;'.$staticMapURL.'&amp;key='.GoogleMapAPIKey;
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


