<?php

/**
 * form that can be used to search by address
 *
 *
 *
 *
 */


class SearchByAddressForm extends Form
{
    private static $type_of_result = '';

    /**
     *
     * @var String
     */
    protected $defaultAddress = "";

    /**
     *
     * @param String
     */
    public function setDefaultAddress($s)
    {
        $this->defaultAddress = $s;
    }

    /**
     *
     * @var Array
     */
    protected $classNamesSearchedFor = array();

    /**
     *
     * @param Array
     */
    public function setClassNamesSearchedFor($a)
    {
        $this->classNamesSearchedFor = $a;
    }

    /**
     *
     * @var Boolean
     */
    protected $useAutocomplete = true;

    /**
     *
     * @param Boolean
     */
    public function setUseAutocomplete($a)
    {
        $this->useAutocomplete = $b;
    }



    /**
     *
     * @param Controller $controller
     * @param String $name
     * @param String $defaultAddress
     * @param Array $classNamesSearchedFor
     *
     * @return Form
     */
    public function __construct($controller, $name, $defaultAddress = "", $classNamesSearchedFor = array("SiteTree"))
    {
        $this->defaultAddress = $defaultAddress;
        if (!$this->defaultAddress) {
            $this->defaultAddress = isset($_GET["FindNearAddress"]) ? $_GET["FindNearAddress"] : "";
        }
        $classNamesAsString = '';
        if (is_array($classNamesSearchedFor)) {
            $this->classNamesSearchedFor = $classNamesSearchedFor;
            $classNamesAsString = implode(",", $this->classNamesSearchedFor);
        }
        parent::__construct(
            $controller,
            "SearchByAddressForm",
            new FieldList(
                $addressField = new TextField(
                    "FindNearAddress",
                    _t("GoogleMapLocationsDOD.ENTERLOCATION", "Enter your location"),
                    $this->defaultAddress
                ),
                new HiddenField("ClassNamesSearchedFor", "ClassName", $classNamesAsString)
            ),
            new FieldList(new FormAction("findnearaddress", _t("GoogleMapLocationsDOD.SEARCH", "Search"))),
            new RequiredFields("FindNearAddress")
        );
        $addressField->setAttribute('placeholder', _t('GoogleMapLocationsDOD.YOUR_ADDRESS', "Enter your exact address or zip code here.")) ;
        if ($this->useAutocomplete) {
            Requirements::javascript(
                "//maps.googleapis.com/maps/api/js"
                ."?v=".Config::inst()->get("GoogleMap", "api_version")
                ."&libraries=places"
                ."&key=".Config::inst()->get('GoogleMap', 'google_map_api_key')
            );
            $typeOfResult = $this->Config()->get('type_of_result');
            $setTypeLine = '';
            if ($typeOfResult) {
                $setTypeLine = 'autocomplete.setTypes([\''.$typeOfResult.'\']);';
            }
            Requirements::customScript(
                '
                function init_search_by_address_form() {
                    var input = document.getElementById("'.$this->getName()."_".$this->getName().'_FindNearAddress");
                    var options = {
                        \'location_type\' : \'ROOFTOP\'
                    };
                    var autocomplete = new google.maps.places.Autocomplete(input, options);
                    '.$setTypeLine.'
                }
                google.maps.event.addDomListener(window, "load", init_search_by_address_form);
                ',
                "SearchByAddressFormInit"
            );
        }
        $this->disableSecurityToken();
        return $this;
    }

    public function findnearaddress($data, $form)
    {
        $address = Convert::raw2sql($data["FindNearAddress"]);
        $classNames = Convert::raw2sql($data["ClassNamesSearchedFor"]);
        $pointArray = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($address);

        if (!$pointArray || !isset($pointArray["Longitude"]) || !isset($pointArray["Latitude"])) {
            GoogleMapSearchRecord::create_new(
                Convert::raw2sql($address),
                $this->getController()->dataRecord->ID,
                false
            );
            $this->addErrorMessage(
                'FindNearAddress',
                _t("GoogleMapLocationsDOD.ADDRESSNOTFOUND", "Sorry, address could not be found..."),
                'warning'
            );
            return array();
        } else {
            GoogleMapSearchRecord::create_new(
                Convert::raw2sql($address),
                $this->getController()->dataRecord->ID,
                false
            );
        }
        $this->address = $pointArray["FullAddress"];
        $lng = $pointArray["Longitude"];
        $lat = $pointArray["Latitude"];
        //$form->Fields()->fieldByName("Address")->setValue($pointArray["address"]); //does not work ....
        //$this->owner->addMap($action = "showsearchpoint", "Your search", $lng, $lat);
        $action = "showaroundmexml";
        $title = _t("GoogleMap.CLOSEST_TO_YOUR_SEARCH", "Closest to ").$this->address;
        if (Director::is_ajax()) {
            $this->getController()->response->setBody(json_encode(array(
                'Action' =>  $action,
                'Title' => urlencode($title),
                'ParentID' => $this->getController()->ID,
                'Lng'=> $lng,
                'Lat'=> $lat,
                'ClassNames'=> $classNames
            )));

            $this->getController()->response->addHeader("Content-type", "application/json");

            return $this->getController()->response;
        }
        $this->getController()->addMap($action, $title, $lng, $lat, $classNames);

        return [];
    }

    /**
     * turns 0 into false and 1 into true
     * @param Mixed
     * @return String (true|false)
     */
    protected function showFalseOrTrue($v)
    {
        if ($v === true || 1 == $v) {
            return "true";
        } else {
            return "false";
        }
    }
}
