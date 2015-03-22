<?php

class SearchByAddressForm extends Form {


	/**
	 *
	 * @var String
	 */
	protected $defaultAddress = "";

	/**
	 *
	 * @param String
	 */
	public function setDefaultAddress($s) {$this->defaultAddress = $s;}

	/**
	 *
	 * @var Array
	 */
	protected $classNamesSearchedFor = array();

	/**
	 *
	 * @param Array
	 */
	public function setClassNamesSearchedFor($a) {$this->classNamesSearchedFor = $a;}



	/**
	 *
	 * @param Controller $controller
	 * @param String $name
	 * @param String $defaultAddress
	 * @param Array $classNamesSearchedFor
	 *
	 * @return Form
	 */
	function __construct($controller, $name, $defaultAddress = "", $classNamesSearchedFor = array("SiteTree")) {
		$this->defaultAddress = $defaultAddress;
		if(!$this->defaultAddress) {
			$this->defaultAddress = isset($_GET["FindNearAddress"]) ? $_GET["FindNearAddress"] : "";
		}
		$this->classNamesSearchedFor = $classNamesSearchedFor;
		$classNamesAsString = implode(",", $this->classNamesSearchedFor);
		parent::__construct(
			$controller,
			"SearchByAddressForm",
			new FieldList(
				$addressField = new TextField("FindNearAddress", _t("GoogleMapLocationsDOD.ENTERLOCATION", "Enter your location"),$this->defaultAddress),
				new HiddenField("ClassNamesSearchedFor", "ClassName", $classNamesAsString)
			),
			new FieldList(new FormAction("findnearaddress", _t("GoogleMapLocationsDOD.SEARCH", "Search"))),
			new RequiredFields("FindNearAddress")
		);
		$addressField->setAttribute('placeholder', _t('GoogleMapLocationsDOD.YOUR_ADDRESS', "Enter your address or zip code here.")) ;
		return $this;
	}

	function findnearaddress($data, $form) {
		$address = Convert::raw2sql($data["FindNearAddress"]);
		$classNames = Convert::raw2sql($data["ClassNamesSearchedFor"]);
		$pointArray = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($address);
		if(!$pointArray || !isset($pointArray["Longitude"]) || !isset($pointArray["Latitude"])) {
			GoogleMapSearchRecord::create_new($address, $this->getController()->dataRecord->ID, false);
			$this->addErrorMessage('FindNearAddress', _t("GoogleMapLocationsDOD.ADDRESSNOTFOUND", "Sorry, address could not be found..."), 'warning');
			return;
		}
		else {
			GoogleMapSearchRecord::create_new(Convert::raw2sql($address), $this->getController()->dataRecord->ID);
		}
		$this->address = $pointArray["FullAddress"];
		$lng = $pointArray["Longitude"];
		$lat = $pointArray["Latitude"];
		//$form->Fields()->fieldByName("Address")->setValue($pointArray["address"]); //does not work ....
		//$this->owner->addMap($action = "showsearchpoint", "Your search", $lng, $lat);
		$action = "showaroundmexml";
		$title = _t("GoogleMap.CLOSEST_TO_YOUR_SEARCH", "Closest to")." ".$this->address;
		$this->getController()->addMap($action, $title, $lng, $lat, $classNames);
		return array();
	}

}
