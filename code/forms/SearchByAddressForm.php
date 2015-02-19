<?php

class SearchByAddressForm extends Form {


	/**
	 *
	 * @param NULL | String | array $className (OPTIONAL)
	 * @param NULL | SiteTree $displayPage (OPTIONAL)
	 *
	 * @return Form
	 */
	function SearchByAddressForm($className = '', $displayPage = null) {
		if(is_array($className)) {
			$className = implode(",", $className);
		}
		if($displayPage) {
			$displayPageID = $displayPage->ID;
		}
		else {
			$displayPageID = 0;
		}
		$form = new Form(
			$this->owner,
			"SearchByAddressForm",
			new FieldList(
				$addressField = new TextField("FindNearAddress", _t("GoogleMapLocationsDOD.ENTERLOCATION", "Enter your location"),$this->address),
				new HiddenField("FindNearClassName", "ClassName", $className),
				new HiddenField("DisplayPageID", "DisplayPageID", $displayPageID)
			),
			new FieldList(new FormAction("findnearaddress", _t("GoogleMapLocationsDOD.SEARCH", "Search"))),
			new RequiredFields("FindNearAddress")
		);
		$addressField->setAttribute('placeholder', _t('GoogleMapLocationsDOD.YOUR_ADDRESS', "Enter your address or zip code here.")) ;
		return $form;
	}

	function findnearaddress($data, $form) {
		$address = Convert::raw2sql($data["FindNearAddress"]);
		$className = Convert::raw2sql($data["FindNearClassName"]);
		$pointArray = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($address);
		$this->address = $pointArray["FullAddress"];
		if(!isset($pointArray["Longitude"]) || !isset($pointArray["Latitude"])) {
			GoogleMapSearchRecord::create_new($address, $this->owner->ID, false);
			$form->addErrorMessage('FindNearAddress', _t("GoogleMapLocationsDOD.ADDRESSNOTFOUND", "Sorry, address could not be found..."), 'warning');
			$this->redirectBack();
			return;
		}
		else {
			GoogleMapSearchRecord::create_new(Convert::raw2sql($address), $this->owner->ID, true);
		}
		$lng = $pointArray["Longitude"];
		$lat = $pointArray["Latitude"];
		//$form->Fields()->fieldByName("Address")->setValue($pointArray["address"]); //does not work ....
		//$this->owner->addMap($action = "showsearchpoint", "Your search",$lng, $lat);
		$this->owner->addMap($action = "showaroundmexml","Closests to your search", $lng, $lat, $className);
		return array();
	}

}
