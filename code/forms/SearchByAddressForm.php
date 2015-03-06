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
	 * @var SiteTree
	 */
	protected $displayPage = null;


	/**
	 *
	 * @param SiteTree
	 */
	public function setDisplayPage($s) {$this->displayPage = $s;}


	/**
	 *
	 * @param Controller $controller
	 * @param String $name
	 * @param Array $classNamesSearchedFor
	 * @param String $defaultAddress
	 * @param SiteTree $displayPage
	 *
	 * @return Form
	 */
	function __construct($controller, $name, $defaultAddress = "", $classNamesSearchedFor = array("SiteTree"),  $displayPage = null) {
		$this->defaultAddress = $defaultAddress;
		$this->classNamesSearchedFor = $classNamesSearchedFor;
		$this->displayPage = $displayPage;
		$classNamesAsString = implode(",", $this->classNamesSearchedFor);
		if($this->displayPage) {
			$this->displayPageID = $displayPage->ID;
		}
		else {
			$this->displayPageID = 0;
		}
		parent::__construct(
			$controller,
			"SearchByAddressForm",
			new FieldList(
				$addressField = new AddressFinderField("FindNearAddress", _t("GoogleMapLocationsDOD.ENTERLOCATION", "Enter your location"),$this->defaultAddress),
				new HiddenField("ClassNamesSearchedFor", "ClassName", $classNamesAsString),
				new HiddenField("DisplayPageID", "DisplayPageID", $this->displayPageID)
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
		$displayPageID = intval($data["DisplayPageID"]);
		if($displayPageID) {
			if($displayPage = SiteTree::get()->byID($displayPageID)) {
				$this->getController()->redirect($displayPage->Link()."?address=".urlencode($address));
			}
		}
		$pointArray = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($address);
		$this->address = $pointArray["FullAddress"];
		if(!isset($pointArray["Longitude"]) || !isset($pointArray["Latitude"])) {
			GoogleMapSearchRecord::create_new($address, $this->owner->ID, false);
			$this->addErrorMessage('FindNearAddress', _t("GoogleMapLocationsDOD.ADDRESSNOTFOUND", "Sorry, address could not be found..."), 'warning');
			$this->redirectBack();
			return;
		}
		else {
			GoogleMapSearchRecord::create_new(Convert::raw2sql($address), $this->getController()->dataRecord->ID);
		}
		$lng = $pointArray["Longitude"];
		$lat = $pointArray["Latitude"];
		//$form->Fields()->fieldByName("Address")->setValue($pointArray["address"]); //does not work ....
		//$this->owner->addMap($action = "showsearchpoint", "Your search",$lng, $lat);
		$action = "showaroundmexml";
		$title = _t("GoogleMap.CLOSESTS_TO_YOUR_SEARCH", "Closests to your search");
		$link = "";
		if($displayPageID) {
			if($displayPage = SiteTree::get()->byID($displayPageID)) {
				$loadMapLink = $this->getController()->LoadmapLink($displayPageID, $action, $title, $lng, $lat, $classNames);
				$link = $displayPage->Link();
			}
		}
		if(!$link) {
			$link = $this->getController()->Link();
			$loadMapLink = $this->getController()->LoadmapLink($this->getController()->ID, $action, $title, $lng, $lat, $classNames);

		}
		$this->getController()->addMap($action, $title, $lng, $lat, $classNames);
		return array();
	}

}
