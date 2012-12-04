<?php
/**
 * GoogleMapLocationsObject.php: Sub-class of DataObject
 * DataObject holding all GeoPoints
 * @created 14/10/2008
 */

class GoogleMapLocationsObject extends DataObject {

	protected static $parent_point_counts = array();

	static $db = array (
		'PointType' =>'Enum("none, point, polyline, polygon", "point")',
		'Accuracy' => 'Int',
		'Latitude' => 'Double(12,7)',
		'Longitude' => 'Double(12,7)',
		'PointString' => 'Text',
		'Address' => 'Text',
		'FullAddress' => 'Text',
		'CountryNameCode' => 'Varchar(3)',
		'AdministrativeAreaName' => 'Varchar(255)',
		'SubAdministrativeAreaName' => 'Varchar(255)',
		'LocalityName' => 'Varchar(255)',
		'ThoroughfareName' => 'Varchar(255)',
		'PostalCodeNumber' => 'Varchar(30)',
		'Manual' => 'Boolean',
		'CustomPopUpWindowTitle' => "Varchar(50)",
		'CustomPopUpWindowInfo' => "Varchar(255)",
		//'GeoPointField' => 'GeoPoint',
		//'GeoPolygonField' => 'GeoPolygon',
		//'GeoLineString' => 'GeoLineString'
	);

	static $summary_fields = array (
		'PointString',
		'Address',
		'FullAddress',
		'CountryNameCode',
		'AdministrativeAreaName',
		'SubAdministrativeAreaName',
		'LocalityName',
		'ThoroughfareName',
		'PostalCodeNumber'
	);

	static $has_one = array (
		'Parent' => 'SiteTree'
	);

	static $indexes = array(
		"Latitude" => true,
		"Longitude" => true
	);

	static $has_many = array (
		//'' => ''
	);

	/*static $many_many = array (

	);

	static $belongs_many_many = array (

	);*/

	static $defaults = array (
		// '' => ''
	);

	static $casting = array(
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

	static function pointExists($addressArray) {
		return DataObject::get_one("GoogleMapLocationsObject", "Longitude = '".$addressArray[0]."' AND Latitude = '".$addressArray[1]."'");
	}

	function  getCMSFields_forPopup() {
		$addTitleAndContent = true;
		$parentPageID = $this->ParentID;
		if($parentPageID) {
			$parent = DataObject::get_by_id("SiteTree", $parentPageID);
			if($parent) {
				if($parent->hasMethod("CustomAjaxInfoWindow")) {
					$addTitleAndContent = false;
				}
			}
		}
		$fieldset = new FieldSet(
			new TextField('Address', 'Enter Full Address (e.g. 123 Main Street, Newtown, Wellington, New Zealand ) - all other fields will be auto-completed (looked up at Google Maps)'),
			new HiddenField('ParentID', 'ParentID', $parentPageID)
		);
		if($addTitleAndContent) {
			$fieldset->push(new TextField('CustomPopUpWindowTitle', 'Custom Title for Info Pop-Up Window, leave Blank to auto-complete the pop-up information on the map'));
			$fieldset->push(new TextField('CustomPopUpWindowInfo', 'Custom Description for Info Pop-Up Window, leave Blank to auto-complete the pop-up information on the map'));
		}
		$fieldset->push(new CheckboxField('Manual', 'Edit address manually (e.g. enter Longitude and Latitude - check box, save and reload to edit...)'));
		if($this->Manual) {
			$fieldset->push(new HeaderField('Auto-completed (not required)', 2));
			$fieldset->push(new TextField('Latitude', 'Latitude'));
			$fieldset->push(new TextField('Longitude', 'Longitude'));
			$fieldset->push(new TextField('PointString', 'PointString'));
			$fieldset->push(new TextField('FullAddress', 'Found Address'));
			$fieldset->push(new NumericField('Accuracy', 'Accuracy'));
			$fieldset->push(new TextField('CountryNameCode', 'Country Name Code'));
			$fieldset->push(new TextField('AdministrativeAreaName', 'Administrative Area Name'));
			$fieldset->push(new TextField('SubAdministrativeAreaName', 'SubAdministrative Area Name'));
			$fieldset->push(new TextField('LocalityName', 'Locality Name'));
			$fieldset->push(new TextField('ThoroughfareName', 'Thoroughfare Name'));
			$fieldset->push(new TextField('PostalCodeNumber', 'Postal Code Number'));
		}
				//'GeoPointField' => 'GeoPoint',
					//'GeoPolygonField' => 'GeoPolygon',
					//'GeoLineString' => 'GeoLineString'
		return $fieldset;
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
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$parentData = $this->getParentData();
		if(!isset(self::$parent_point_counts[$this->ParentID + 0]) && $this->getParentData()) {
			$result = DB::query("Select Count(*) from {$bt}GoogleMapLocationsObject{$bt} where ParentID = ".$this->ParentID);
			$count = $result->value();
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
		$this->findGooglePoints($doNotWrite = true);
	}

	function completePoints() {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$uncompletedPoints = DataObject::get("GoogleMapLocationsObject", "
			(
				({$bt}GoogleMapLocationsObject{$bt}.{$bt}Address{$bt} <> {$bt}GoogleMapLocationsObject{$bt}.{$bt}FullAddress{$bt})
				OR (
					{$bt}GoogleMapLocationsObject{$bt}.{$bt}Address{$bt} = IsNull
					OR {$bt}GoogleMapLocationsObject{$bt}.{$bt}Address{$bt} = ''
				)
			)
			AND
				{$bt}GoogleMapLocationsObject{$bt}.{$bt}Manual{$bt} <> 1
				AND {$bt}GoogleMapLocationsObject{$bt}.{$bt}Address{$bt} <> IsNull
				AND (({$bt}GoogleMapLocationsObject{$bt}.{$bt}Address{$bt}) <> '' OR ({$bt}GoogleMapLocationsObject{$bt}.{$bt}Longitude{$bt}<> 0
				AND {$bt}GoogleMapLocationsObject{$bt}.{$bt}Latitude{$bt} <> 0
				AND (
					{$bt}GoogleMapLocationsObject{$bt}.{$bt}Address{$bt} = ''
					OR {$bt}GoogleMapLocationsObject{$bt}.{$bt}Address{$bt} = IsNull
				)
			)"
		);
		if($uncompletedPoints) {
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
			if(!$this->Manual && ( (!$this->Latitude || !$this->Longitude) || ($this->Latitude && $this->Longitude && !$this->Address) ) ) {
				if($this->Address) {
					$newData = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($this->Address);
				}
				else {
					$newData = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($this->Latitude.",".$this->Longitude);
				}
				$this->addDataFromArray($newData, $doNotWrite);
			}
		}
	}

	function addDataFromArray($newData, $doNotWrite = false) {
		if(isset($newData["address"])) {$this->FullAddress = $newData["address"]; }
		$option = "";
		foreach($newData as $key => $value) {
			if("0" == $key && floatval($value)) {
				$option .= "A";
				$this->Longitude = $value;
			}
			elseif("1" == $key &&  floatval($value) && "A" == $option) {
				$option .= "B";
				$this->Latitude = $value;
			}
			elseif("1" == $key && floatval($value) && "A" != $option) {
				$option .= "C";
				$this->Longitude = $value;
			}
			elseif("2" == $key && floatval($value) && "C" == $option) {
				$option .= "D";
				$this->Latitude = $value;
			}
		}
		if(isset($newData["CountryNameCode"])) {$this->CountryNameCode = $newData["CountryNameCode"];}
		if(isset($newData["AdministrativeAreaName"])) {$this->AdministrativeAreaName = $newData["AdministrativeAreaName"];}
		if(isset($newData["SubAdministrativeAreaName"])) {$this->SubAdministrativeAreaName = $newData["SubAdministrativeAreaName"];}
		if(isset($newData["LocalityName"])) {$this->LocalityName = $newData["LocalityName"];}
		if(isset($newData["ThoroughfareName"])) {$this->ThoroughfareName = $newData["ThoroughfareName"];}
		if(isset($newData["PostalCodeNumber"])) {$this->PostalCodeNumber = $newData["PostalCodeNumber"];}
		if(isset($newData["Accuracy"])) {
			$this->Accuracy = $newData["Accuracy"];
			if($newData["Accuracy"]> 2) {
				$this->Address = $newData["address"];
			}
		}
		if(!$doNotWrite) {
		/* AS THIS IS A onBeforeWrite there is NO POINT in writing!!!!! */
			$this->write();
		}
	}
}
