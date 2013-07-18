<?php
/**
 * GoogleMapLocationsObject.php: Sub-class of DataObject
 * DataObject holding all GeoPoints
 * @created 14/10/2008
 */

class GoogleMapLocationsObject extends DataObject {

	private static $parent_point_counts = array();

	private static $db = array (
		'PointType' =>'Enum("none, point, polyline, polygon", "point")',
		'Accuracy' => 'Varchar(100)',
		'Latitude' => 'Double(12,7)',
		'Longitude' => 'Double(12,7)',
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
	static function radiusDefinition($lon, $lat) {
		return "(6378.137 * ACOS( ( SIN( PI( ) * ".$lat." /180 ) * SIN( PI( ) * \"GoogleMapLocationsObject\".\"Latitude\" /180 ) ) + ( COS( PI( ) * ".$lat." /180 ) * cos( PI( ) * \"GoogleMapLocationsObject\".\"Latitude\" /180 ) * COS( (PI( ) * \"GoogleMapLocationsObject\".\"Longitude\" /180 ) - ( PI( ) * $lon / 180 ) ) ) ) )";
	}

	static function radiusDefinitionOtherTable($lon, $lat, $table, $latitudeField, $longitudeField) {
		return "(6378.137 * ACOS( ( SIN( PI( ) * ".$lat." /180 ) * SIN( PI( ) * \"".$table."\".\"".$latitudeField."\" /180 ) ) + ( COS( PI( ) * ".$lat." /180 ) * cos( PI( ) * \"".$table."\".\"".$latitudeField."\" /180 ) * COS( (PI( ) * \"".$table."\".\"".$longitudeField."\" / 180 ) - ( PI( ) * $lon / 180 ) ) ) ) ) ";
	}

	static function pointExists($longitude, $latitude) {
		return GoogleMapLocationsObject::get()->filter(array(
			"Longitude" => floatval($longitude),
			"Latitude" => floatval($latitude)
		))->First();
	}

	function  getCMSFields() {
		$fields = parent::getCMSFields();
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
		$fields->addFieldToTab("Root.Main",
			new TextField('Address', 'Enter Full Address (e.g. 123 Main Street, Newtown, Wellington, New Zealand ) - all other fields will be auto-completed (looked up at Google Maps)')
			//new HiddenField('ParentID', 'ParentID', $parentPageID)
		);

		if($this->Manual) {
			$fields->addFieldToTab("Root.Details", new TextField('Latitude', 'Latitude'));
			$fields->addFieldToTab("Root.Details", new TextField('Longitude', 'Longitude'));
		}
		else {
			$fields->addFieldToTab("Root.Details", new ReadonlyField('Latitude', 'Latitude'));
			$fields->addFieldToTab("Root.Details", new ReadonlyField('Longitude', 'Longitude'));
		}
		$fields->addFieldToTab("Root.Main", new CheckboxField('Manual', 'Edit address manually (e.g. enter Longitude and Latitude - check box, save and reload to edit...)'));
		$fields->addFieldToTab("Root.Main", new ReadonlyField('FullAddress', 'Found Address'));
		$fields->addFieldToTab("Root.Details", new HeaderField('Auto-completed (not required)', 2));

		$fields->addFieldToTab("Root.Details", new ReadonlyField('CountryNameCode', 'Country Name Code'));
		$fields->addFieldToTab("Root.Details", new ReadonlyField('AdministrativeAreaName', 'Administrative Area Name'));
		$fields->addFieldToTab("Root.Details", new ReadonlyField('SubAdministrativeAreaName', 'SubAdministrative Area Name'));
		$fields->addFieldToTab("Root.Details", new ReadonlyField('LocalityName', 'Locality Name'));
		$fields->addFieldToTab("Root.Details", new ReadonlyField('PostalCodeNumber', 'Postal Code Number'));
		$fields->addFieldToTab("Root.Details", new ReadonlyField('Accuracy'));
		$fields->addFieldToTab("Root.Type", $fields->dataFieldByName("PointType"));
		if($this->PointType != "point" && $this->PointType != "none") {
			$fields->addFieldToTab("Root.Type", new TextField('PointString', 'PointString'));
		}
		else {
			$fields->removeByName("PointString");
		}
		if($addTitleAndContent) {
			$fields->addFieldToTab("Root.Popup", new TextField('CustomPopUpWindowTitle', 'Custom Title for Info Pop-Up Window, leave Blank to auto-complete the pop-up information on the map'));
			$fields->addFieldToTab("Root.Popup", new TextareaField('CustomPopUpWindowInfo', 'Custom Description for Info Pop-Up Window, leave Blank to auto-complete the pop-up information on the map'));
		}
		else {
			$fields->removeByName("CustomPopUpWindowTitle");
			$fields->removeByName("CustomPopUpWindowInfo");
		}
		return $fields;
	}

	function getParentData() {
		return $this->Parent();
	}

	function getAjaxInfoWindowLink() {
		if(strlen($this->CustomPopUpWindowInfo) > 3) {
			return '<p>'.$this->CustomPopUpWindowInfo.'</p>';
		}
		elseif($parent = $this->getParentData()) {
			return $parent->AjaxInfoWindowLink();
		}
	}

	function getParentClassName() {
		if($parent = $this->getParentData()) {
			return $parent->ClassName;
		}
	}
	function getLink() {
		if($parent = $this->getParentData()) {
			return $parent->Link();
		}
	}

	function addParentData() {
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

	function complexTableFields() {
		$fields = array(
			'FullAddress' => 'FullAddress',
			'Longitude' => 'Longitude',
			'Latitude' => 'Latitude',
		);
		return $fields;
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

	function completePoints() {
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

	function findGooglePointsAndWriteIfFound() {
		$this->findGooglePoints(true);
		if($this->FullAddress && $this->Longitude && $this->Latitude) {
			$this->write();
			return $this;
		}
		return false;
	}

	function findGooglePoints($doNotWrite) {
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
