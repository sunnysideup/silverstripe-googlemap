<?php
/**
 * add to sitetree
 *
 *
 *
 *
 */

class GoogleMapLocationsDOD extends SiteTreeExtension {

	private static $db = array("HasGeoInfo" => "Boolean");

	private static $has_many = array("GeoPoints" => "GoogleMapLocationsObject");

	/**
	 * list of pages types without a map
	 * @var Array
	 */
	private static $page_classes_without_map = array();

	/**
	 * list of pages types with a map
	 * @var Array
	 */
	private static $page_classes_with_map = array();

	/**
	 * @param FieldList
	 */
	function updateCMSFields(FieldList $fields) {
		if($this->classHasGoogleMap()) {
			$fields->addFieldToTab("Root", new Tab("Map"));
			$fields->addFieldToTab("Root.Map", $hasGeoInfoBox = new CheckboxField("HasGeoInfo", _t("GoogleMapLocationsDOD.HAS_ADDRESSES", "Has Address(es)?")));
			if(!$this->owner->HasGeoInfo) {
				$hasGeoInfoBox->setDescription(_t("GoogleMap.HasGeoInfoSTART", "tick and save this page to start data-entry ..."));
			}
			if($this->owner->HasGeoInfo) {
				$dataObject = new GoogleMapLocationsObject();
				$source = $this->owner->GeoPoints();
				$GeoPointsField = new GridField(
					"GeoPoints",
					"Locations",
					$source,
					GridFieldConfig_RelationEditor::create()
				);
				$fields->addFieldToTab("Root.Map", $GeoPointsField);
			}
		}
		return $fields;
	}

	/**
	 * returns the HTML for a info window on a map for this page.
	 * @return String (HTML)
	 */
	public function AjaxInfoWindowLink() {
		if($this->owner->hasMethod("CustomAjaxInfoWindow") || 1 == 1) {
			return $this->owner->CustomAjaxInfoWindow();
		}
		if($this->owner->hasMethod("ajaxinfowindowreturn")) {
			return '<div class="viewMoreInformationHolder"><a href="'.$this->owner->Link().'" onclick="return !loadAjaxInfoWindow(this,\''.$this->owner->Link().'ajaxinfowindowreturn/\');">'. Config::inst()->get("GoogleMap", "ajax_info_window_text") .'</a><div class="loadAjaxInfoWindowSpan"></div></div>';
		}
	}

	/**
	 * Page Type has a Google Map
	 * @return Boolean
	 */
	public function ClassHasGoogleMap() {
		//assumptions:
		//1. in general YES
		//2. if list of WITH is shown then it must be in that
		//3. otherwise check if it is specifically excluded (WITHOUT)
		$result = true;
		$inc =  Config::inst()->get("GoogleMapLocationsDOD", "page_classes_with_map");
		$exc =  Config::inst()->get("GoogleMapLocationsDOD", "page_classes_without_map");
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
	 *
	 * @return ArrayList of items if Class $classType
	 */
	function getChildrenOfType($parentPage, $classType = null) {
		$children = $parentPage->AllChildren();
		if (!isset($childrenOfType)) {
			$childrenOfType = new ArrayList();
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
		return ($childrenOfType) ? $childrenOfType : new ArrayList();

	}

}


