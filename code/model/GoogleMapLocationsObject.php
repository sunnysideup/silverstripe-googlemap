<?php
/**
 *
 * Map Location Object
 * onBeforeWrite, it automagically adds all the details.
 *
 */

class GoogleMapLocationsObject extends DataObject {

	private static $parent_point_counts = array();

	private static $db = array (
		'PointType' =>'Enum("none, point, polyline, polygon", "point")',
		'Accuracy' => 'Varchar(100)',
		'Longitude' => 'Double(12,7)',
		'Latitude' => 'Double(12,7)',
		'PointString' => 'Text',
		'Address' => 'Text',
		'FullAddress' => 'Text',
		'CountryNameCode' => 'Varchar(3)',
		'AdministrativeAreaName' => 'Varchar(255)',
		'SubAdministrativeAreaName' => 'Varchar(255)',
		'LocalityName' => 'Varchar(255)',
		'PostalCodeNumber' => 'Varchar(30)',
		'Manual' => 'Boolean',
		'CustomPopUpWindowTitle' => "Varchar(50)",
		'CustomPopUpWindowInfo' => "Varchar(255)",
		//'GeoPointField' => 'GeoPoint',
		//'GeoPolygonField' => 'GeoPolygon',
		//'GeoLineString' => 'GeoLineString'
	);

	private static $summary_fields = array (
		'FullAddress' => "FullAddress",
	);

	private static $has_one = array (
		'Parent' => 'SiteTree'
	);

	private static $indexes = array(
		"Latitude" => true,
		"Longitude" => true
	);

	private static $field_labels = array(
		'PointType' =>'Marker Type',
		'Accuracy' => 'Accuracy',
		'Longitude' => 'Longitude',
		'Latitude' => 'Latitude',
		'PointString' => 'PointString',
		'Address' => 'Searched For Address',
		'FullAddress' => 'Found Address',
		'CountryNameCode' => 'Country Code',
		'AdministrativeAreaName' => 'Main Area',
		'SubAdministrativeAreaName' => 'Sub Area',
		'LocalityName' => 'Locality',
		'PostalCodeNumber' => 'Postal Code',
		'Manual' => 'Set Details Manually',
		'CustomPopUpWindowTitle' => "Marker Title",
		'CustomPopUpWindowInfo' => "Marker Description"
	);

	private static $casting = array(
		"ParentData" => "SiteTree",
		"AjaxInfoWindowLink" => "HTMLText",
		"ParentClassName" => "Varchar",
		"Link" => "Varchar"
	);

	/**
	 * Provides MySQL snippet to work out distance between GoogleMapLocationsObject and location
	 * The method returns a string for use in queries
	 * The query snippet returns the distance between the GoogleMapLocationsObject and the latitude and longitude provided
	 * NOTE: 6378.137 is the radius of the earth in kilometers
	 * @param Double $lon - longitude of location
	 * @param Double $lat - latitude of location
	 * @return String
	 */
	public static function radiusDefinition($lon, $lat) {
		return "(6378.137 * ACOS( ( SIN( PI( ) * ".$lat." /180 ) * SIN( PI( ) * \"GoogleMapLocationsObject\".\"Latitude\" /180 ) ) + ( COS( PI( ) * ".$lat." /180 ) * cos( PI( ) * \"GoogleMapLocationsObject\".\"Latitude\" /180 ) * COS( (PI( ) * \"GoogleMapLocationsObject\".\"Longitude\" /180 ) - ( PI( ) * $lon / 180 ) ) ) ) )";
	}

	public static function radiusDefinitionOtherTable($lon, $lat, $table, $latitudeField, $longitudeField) {
		return "(6378.137 * ACOS( ( SIN( PI( ) * ".$lat." /180 ) * SIN( PI( ) * \"".$table."\".\"".$latitudeField."\" /180 ) ) + ( COS( PI( ) * ".$lat." /180 ) * cos( PI( ) * \"".$table."\".\"".$latitudeField."\" /180 ) * COS( (PI( ) * \"".$table."\".\"".$longitudeField."\" / 180 ) - ( PI( ) * $lon / 180 ) ) ) ) ) ";
	}

	/**
	 * @param Int $longitude
	 * @param Int $latitude
	 *
	 * return GoogleMapLocationsObject | Null
	 */
	public static function pointExists($longitude, $latitude) {
		return GoogleMapLocationsObject::get()->filter(array(
			"Longitude" => floatval($longitude),
			"Latitude" => floatval($latitude)
		))->First();
	}

	function  getCMSFields() {
		$fields = parent::getCMSFields();
		$labels = $this->FieldLabels();
		$addTitleAndContent = true;
		$parentPageID = $this->ParentID;
		if($parentPageID) {
			$parent = SiteTree::get()->byID($parentPageID);
			if($parent) {
				if($parent->hasMethod("CustomAjaxInfoWindow")) {
					$addTitleAndContent = false;
				}
			}
		}
		$fields->addFieldToTab("Root.Main", $addressField = new TextField('Address', $labels["Address"]));
		$addressField->setRightTitle(
			_t("GoogleMap.CMS_ADDRESS_EXPLANATION",
			"(e.g. 123 Main Street, 90210, Newtown, Wellington, New Zealand ) - all other fields will be auto-completed")
		);
		if($this->Manual) {
			$fields->addFieldToTab("Root.Details", new TextField('Latitude', $labels["Latitude"]));
			$fields->addFieldToTab("Root.Details", new TextField('Longitude', $labels["Longitude"]));
		}
		else {
			$fields->addFieldToTab("Root.Details", new ReadonlyField('Latitude', $labels["Latitude"]));
			$fields->addFieldToTab("Root.Details", new ReadonlyField('Longitude', $labels["Longitude"]));
		}
		$fields->addFieldToTab("Root.Main", $manualField = new CheckboxField('Manual', $labels["Manual"]));
		$manualField->setDescription(
			_t("GoogleMap.MANUAL_DESCRIPTION", 'Edit address manually (e.g. enter Longitude and Latitude - check box, save and reload to edit...)')
		);
		$fields->addFieldToTab("Root.Main", new ReadonlyField('FullAddress', $labels["FullAddress"]));
		$fields->addFieldToTab("Root.Details", new ReadonlyField('CountryNameCode', $labels["CountryNameCode"]));
		$fields->addFieldToTab("Root.Details", new ReadonlyField('AdministrativeAreaName', $labels["AdministrativeAreaName"]));
		$fields->addFieldToTab("Root.Details", new ReadonlyField('SubAdministrativeAreaName', $labels["SubAdministrativeAreaName"]));
		$fields->addFieldToTab("Root.Details", new ReadonlyField('LocalityName', $labels["LocalityName"]));
		$fields->addFieldToTab("Root.Details", new ReadonlyField('PostalCodeNumber', $labels["PostalCodeNumber"]));
		$fields->addFieldToTab("Root.Details", new ReadonlyField('Accuracy', $labels["Accuracy"]));
		$fields->addFieldToTab("Root.Type", $fields->dataFieldByName("PointType"));
		if($this->PointType != "point" && $this->PointType != "none") {
			$fields->addFieldToTab("Root.Type", new TextField('PointString', $labels["PointString"]));
		}
		else {
			$fields->removeByName("PointString");
		}
		if($addTitleAndContent) {
			$fields->addFieldToTab("Root.Popup", $customPopUpWindowTitleField = new TextField('CustomPopUpWindowTitle', $labels["CustomPopUpWindowTitle"]));
			$customPopUpWindowTitleField->setRightTitle(
				_t("GoogleMap.CUSTOM_POP_UP_WINDOW_TITLE", 'Leave Blank to auto-complete the pop-up information on the map.')
			);
			$fields->addFieldToTab("Root.Popup", $customPopUpWindowInfoField = new TextareaField('CustomPopUpWindowInfo', $labels["CustomPopUpWindowInfo"] ));
			$customPopUpWindowInfoField->setRightTitle(
				_t("GoogleMap.CUSTOM_POP_UP_WINDOW_INFO", 'Leave Blank to auto-complete the pop-up information on the map.')
			);

		}
		else {
			$fields->removeByName("CustomPopUpWindowTitle");
			$fields->removeByName("CustomPopUpWindowInfo");
		}
		return $fields;
	}

	/**
	 * @casted variable
	 * @return SiteTree
	 */
	function getParentData() {
		return $this->Parent();
	}

	/**
	 * @casted variable
	 * @return String (HTML)
	 */
	function getAjaxInfoWindowLink() {
		if(strlen($this->CustomPopUpWindowInfo) > 3) {
			return '<p>'.$this->CustomPopUpWindowInfo.'</p>';
		}
		elseif($parent = $this->getParentData()) {
			return $parent->AjaxInfoWindowLink();
		}
	}

	/**
	 * @casted variable
	 * @return String | Null
	 */
	function getParentClassName() {
		if($parent = $this->getParentData()) {
			return $parent->ClassName;
		}
	}

	/**
	 * @casted variable
	 * @return String | Null
	 */
	function getLink() {
		if($parent = $this->getParentData()) {
			return $parent->Link();
		}
	}

	/**
	 * add data from Parent to the object
	 */
	public function addParentData() {
		$parentData = $this->getParentData();
		if(!isset(self::$parent_point_counts[$this->ParentID + 0]) && $this->getParentData()) {
			$count = GoogleMapLocationsObject::get()->filter(array("ParentID" => $this->ParentID))->count();
			self::$parent_point_counts[$this->ParentID] = $count;
		}
		if(isset(self::$parent_point_counts[$this->ParentID + 0]) && self::$parent_point_counts[$this->ParentID + 0] == 1 && $this->getParentData()) {
			$this->Title = $this->getParentData()->Title;
			$this->Name = $this->getParentData()->Title;
		}
		else {
			$this->Title = $this->Address;
			$this->Name = $this->Address;
		}
		if($this->CustomPopUpWindowTitle) {
			$this->Title = $this->CustomPopUpWindowTitle;
			$this->Name = $this->CustomPopUpWindowTitle;
		}
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		/*
		$this->GeoPointField->setX($this->Latitude);
		$this->GeoPointField->setX($this->Longitude);
		parent::onBeforeWrite();
		*/
		if($this->PointType == "none"){
			$this->PointType = "point";
		}
		$this->findGooglePoints($doNotWrite = true);
	}

	/**
	 * complete points data
	 *
	 */
	protected function completePoints() {
		$uncompletedPoints = GoogleMapLocationsObject::get()->where("
			(
				(\"GoogleMapLocationsObject\".\"Address\" <> \"GoogleMapLocationsObject\".\"FullAddress\")
				OR (
					\"GoogleMapLocationsObject\".\"Address\" = IsNull
					OR \"GoogleMapLocationsObject\".\"Address\" = ''
				)
			)
			AND
				\"GoogleMapLocationsObject\".\"Manual\" <> 1
				AND \"GoogleMapLocationsObject\".\"Address\" <> IsNull
				AND ((\"GoogleMapLocationsObject\".\"Address\") <> '' OR (\"GoogleMapLocationsObject\".\"Longitude\"<> 0
				AND \"GoogleMapLocationsObject\".\"Latitude\" <> 0
				AND (
					\"GoogleMapLocationsObject\".\"Address\" = ''
					OR \"GoogleMapLocationsObject\".\"Address\" = IsNull
				)
			)");
		if($uncompletedPoints->count()) {
			foreach($uncompletedPoints as $point) {
				$point->findGooglePoints(false);
			}
		}
	}

	/**
	 *
	 * @return this
	 */
	public function findGooglePointsAndWriteIfFound() {
		$this->findGooglePoints(true);
		if($this->FullAddress && $this->Longitude && $this->Latitude) {
			$this->write();
			return $this;
		}
		return false;
	}

	/**
	 *
	 * @param Boolean $doNotWrite - do not write to Database
	 */
	protected function findGooglePoints($doNotWrite) {
		if($this) {
			if($this->Address && !$this->Manual) {
				$newData = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($this->Address);
			}
			elseif($this->Latitude && $this->Longitude && $this->Manual) {
				$newData = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($this->Latitude.",".$this->Longitude);
			}
			if(isset($newData) && is_array($newData)) {
				$this->addDataFromArray($newData, $doNotWrite);
			}
		}
	}

	/**
	 *
	 * @param Array $newData
	 * @param Boolean $doNotWrite - do not write object to database
	 */
	protected function addDataFromArray($newData, $doNotWrite = false) {
		foreach($newData as $field => $value) {
			$this->$field = $value;
		}
		if(!$doNotWrite) {
		/* AS THIS IS A onBeforeWrite there is NO POINT in writing!!!!! */
			$this->write();
		}
	}
}
