<?php

/**
 * Adds functions to a Page_Controller
 * to action a map.
 *
 *
 */

class GoogleMapLocationsDOD_Controller extends Extension
{





    #####################
    # INITS
    #####################

    /**
     * @inherited
     */
    private static $allowed_actions = array(
        "AddressFinderForm",
        "doAddressFinderForm",
        "SearchByAddressForm",
        "loadmap"
    );

    /**
     * @var GoogleMap
     */
    protected $googleMap = null;

    /**
     * @var String
     */
    protected $googleMapAddress = "";

    /**
     * look for address
     *
     */
    public function onAfterInit()
    {
        if (!$this->googleMapAddress && isset($_REQUEST["address"])) {
            $this->googleMapAddress = urldecode($_REQUEST["address"]);
        }
        if ($this->googleMapAddress) {
            $this->MyGoogleMap()->setAddress($this->googleMapAddress);
        }
    }


    /**
     * initialise GoogleMap
     * @return GoogleMap
     */
    public function MyGoogleMap()
    {
        if (!$this->googleMap) {
            $this->googleMap = Injector::inst()->get("GoogleMap");
        }
        return $this->googleMap;
    }







    #####################
    # ACTIONS
    #####################


    /**
     * provides a link to any map you like.
     * e.g. mysite.com/mypage/mysub-page/loadmap/optionsHereURLEncoded/
     * optionsHereURLEncoded are basically the link to the map.
     * you can use this to link through to a page and provide a specific map
     *
     * @param HTTPRequest
     */
    public function loadmap($request)
    {
        $link = urldecode($request->param("ID"));
        $options = explode("/", $link);
        $title = $options[3];
        $this->owner->addMapUsingRawLink($title, $link);
        return array();
    }

    /**
     * returns encoded link for the loadmap function
     *
     * @param SiteTree $page
     * @param String $action
     * @param String $title
     * @param Int $lng
     * @param Int $lat
     * @param String $filterCode
     *
     * @return String
     */
    public function LoadmapLink($page = null, $action = "", $title = "", $lng = 0, $lat = 0, $filterCode = "")
    {
        if (!$page) {
            $page = $this->owner->dataRecord;
        }

        //todo: why not Convert::raw2js
        return urlencode($this->getLinkForData($page->ID, $action, $title, $lng, $lat, $filterCode));
    }







    #####################
    # TEMPLATE METHODS
    #####################


    /**
     * @return GoogleMap
     */
    public function GoogleMapController()
    {
        $obj =  $this->MyGoogleMap()->loadGoogleMap();
        return $obj;
    }

    /**
     * @return Boolean
     */
    public function HasGoogleMap()
    {
        if ($this->MyGoogleMap() && $this->owner->classHasGoogleMap()) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param array (optional)  $classNamesSearchedFor e.g. StockistPage
     *
     * @return Form
     */
    public function AddressFinderForm($classNamesSearchedFor = array())
    {
        return $this->SearchByAddressForm($classNamesSearchedFor);
    }

    public function doAddressFinderForm($data, $form)
    {
        return $this->redirect($this->owner->link('addresssearch').'?searchterm='.$data['searchterm']);
    }


    /**
     * @param array (optional)  $classNamesSearchedFor e.g. StockistPage
     *
     * @return Form
     */
    public function SearchByAddressForm($classNamesSearchedFor = array())
    {
        return SearchByAddressForm::create(
            $this->owner,
            "SearchByAddressForm",
            $this->googleMapAddress,
            $classNamesSearchedFor
        );
    }

    /**
     * @param array (optional)  $classNamesSearchedFor e.g. StockistPage
     *
     * @return Form
     */
    public function AddAddressFinder()
    {
        return $this->MyGoogleMap()->AddAddressFinder();
    }




    #####################
    # CREATE MAPS
    #####################


    /**
     * add a layer to a Google Map
     *
     * @param String $action - see GoogleMapDataResponse::allowed_actions to get a list of actions
     * @param String $title
     * @param float $lng - default LATITUDE
     * @param float $lat - default LONGITUDE
     * @param String $filterCode - can be a SiteTree class name, e.g. "ProductPage"
     *                             filter depends on the type of action
     *
     */
    public function addMap($action = "", $title = "", $lng = 0, $lat = 0, $filterCode = "")
    {
        if (!$title) {
            $title = $this->owner->Title;
        }
        $allowedActions = Config::inst()->get("GoogleMapDataResponse", "allowed_actions");
        if (isset($allowedActions[$action]) || in_array($action, $allowedActions)) {
            $title = str_replace('/', '', $title);
            $linkForData = $this->getLinkForData($this->owner->ID, $action, $title, $lng, $lat, $filterCode);
            //where the magic happens...
            $this->MyGoogleMap()->addLayer($linkForData, $title);
        } else {
            user_error("Could not find $action action in GoogleMapDataResponse", E_USER_NOTICE);
        }
    }

    /**
     * add a layer to a Google Map
     *
     * @param String $action - see GoogleMapDataResponse::allowed_actions to get a list of actions
     * @param String $title
     * @param String $filterCode - can be a SiteTree class name, e.g. "ProductPage"
     *                         filter depends on the type of action
     *
     */
    public function addMapUsingRawLink($link = "", $title = "", $filterCode = "")
    {
        if (!$title) {
            $title = $this->owner->Title;
        }
        $this->MyGoogleMap()->addLayer($link, $title);
    }

    /**
     * add an additional layer to an existing map
     *
     * @param String $action
     * @param String $title
     * @param Int $lng
     * @param Int $lat
     * @param String $filter
     */
    public function addExtraLayer($action = "", $title = "", $lng = 0, $lat = 0, $filter = "")
    {
        $linkForData = $this->getLinkForData($this->owner->ID, $action, $title, $lng, $lat, $filter);
        $this->owner->addExtraLayersUsingRawLink($title, $linkForData);
    }

    /**
     * Make up your own link and add this as a layer
     *
     * @param String $title
     * @param String $link
     */
    public function addExtraLayerUsingRawLink($title, $link)
    {
        $this->MyGoogleMap()->addExtraLayer($title, $link);
    }


    /**
     * add an address to the map
     *
     * @param String $address
     * @param Boolean $addShowAroundAdress
     * @param String $filter - usually a SiteTree ClassName (e.g. ProductPage)
     * @param array $params - params for the Google Server
     */
    public function addAddress($address, $addShowAroundAdress = false, $filter = "", $params = [])
    {
        if ($addShowAroundAdress) {
            $pointArray = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($address, false, $params);
            if ($pointArray) {
                $title = $pointArray["FullAddress"];
                $lng = $pointArray["Longitude"];
                $lat = $pointArray["Latitude"];
                $this->owner->addMap("showaroundmexml", $title, $lng, $lat, $filter);
            }
        } else {
            $this->owner->MyGoogleMap();
            $this->googleMapAddress = $address;
        }
    }



    /**
     * @param DataList $pagesOrGoogleMapLocationsObjects
     * @param Boolean $retainOldSessionData
     * @param string $title
     * @param string $filterCode
     *
     * @param String $title
     */
    public function addCustomMap($pagesOrGoogleMapLocationsObjects, $retainOldSessionData = false, $title = '', $filterCode = "")
    {
        $isGoogleMapLocationsObject = $pagesOrGoogleMapLocationsObjects->DataClass() == "GoogleMapLocationsObject" ? true : false;
        if (!$filterCode) {
            $filterCode = ""
                .$this->owner->ID."_"
                .($this->owner->request->param("Action") ? $this->owner->request->param("Action") : "index")
                .($this->owner->request->param("ID") ? $this->owner->request->param("ID") : 0);
        }
        if ($pagesOrGoogleMapLocationsObjects) {
            if (!$retainOldSessionData) {
                $addCustomGoogleMapArray = array();
                $this->owner->clearCustomMaps($filterCode);
            } else {
                $addCustomGoogleMapArray = GoogleMapDataResponse::get_custom_google_map_session_data($filterCode);
            }
            foreach ($pagesOrGoogleMapLocationsObjects as $obj) {
                if (!$obj->ID) {
                    user_error("Page provided to addCustomMap that does not have an ID", E_USER_ERROR);
                }
                $addCustomGoogleMapArray[$obj->ID] = $obj->ID;
            }
        }
        GoogleMapDataResponse::set_custom_google_map_session_data($addCustomGoogleMapArray, $filterCode);
        Session::save();
        if ($isGoogleMapLocationsObject) {
            $fn = "showcustomdosmapxml";
        } else {
            $fn = "showcustompagesmapxml";
        }
        $this->owner->addMap($fn, $title, $lng = 0, $lat = 0, $filterCode);
    }









    #####################
    # MAP SETTINGS
    #####################


    /**
     * @param String $updateServerUrlAddPoint
     */
    public function addUpdateServerUrlAddressSearchPoint($updateServerUrlAddPoint = "/googlemap/showaroundmexml/")
    {
        $link = Controller::join_links($updateServerUrlAddPoint, $this->owner->ID);
        $this->MyGoogleMap()->setUpdateServerUrlAddressSearchPoint($link);
    }

    /**
     * @param String $updateServerUrlDragend
     */
    public function addUpdateServerUrlDragend($updateServerUrlDragend = "googlemap/updatemexml/")
    {
        $link = Controller::join_links($UpdateServerUrlDragend, $this->owner->ID);
        $this->MyGoogleMap()->setUpdateServerUrlDragend($link);
    }

    /**
     * make the map editable
     */
    public function addAllowAddingAndDeletingPoints()
    {
        $this->MyGoogleMap()->AllowAddPointsToMap();
    }

    /**
     * removes user settings for map
     * a custom map is a bunch of points that are customised via a session
     *
     * @param string $filterCode
     */
    public function clearCustomMaps($filterCode = "")
    {
        GoogleMapDataResponse::clear_custom_google_map_session_data($filterCode);
    }


    /**
     * @param String $action
     * @param String $title
     * @param Int $lng
     * @param Int $lat
     * @param String $filter
     *
     * @return String
     */
    protected function getLinkForData($pageID = 0, $action = "", $title = "", $lng = 0, $lat = 0, $filterCode = "")
    {
        if (!$pageID) {
            $pageID = $this->owner->ID;
        }
        $linkForData = "googlemap/".$action."/".$pageID."/".urlencode($title)."/";
        if (($lng && $lat) || $filterCode) {
            $linkForData .= $lng."/".$lat."/";
        }
        if ($filterCode) {
            $linkForData .= '?filtercode='.$filterCode;
        }

        return $linkForData;
    }
}
