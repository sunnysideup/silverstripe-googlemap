<?php

/**
 * The way the map works is that you open a page, which loads the initial page
 * with the map points being loaded as a separate XML doc.
 *
 * This controller returns the Google Map data XML sheet
 * You can show one point by adding ?i=123
 * Where 123 is the ID of a GoogleMapLocationsObject
 *
 * Here are other return options for the Map .... *
 *
 * 'index' / 'showemptymap' => map without anything on it, fallback
 *
 * 'showpagepointsmapxml' => show points from the current page
 *
 * 'showchildpointsmapxml' => show points from the child pages (all child pages)
 *
 * 'showdirectchildren' => show points from the child pages (direct ones only)
 *
 * 'showsearchpoint' =>
 *
 * 'showcustompagesmapxml' => these are sitetree elements loaded by session
 *
 * 'showcustomdosmapxml' =>
 *
 * 'showdataobjects' =>
 *
 * 'updatemexml' =>
 *
 * 'showaroundmexml' =>
 *
 * 'showpointbyid' => can also be more than one ID
 *
 */

class GoogleMapDataResponse extends Controller
{
    private static $session_var_prefix = "addCustomGoogleMap";

    /**
     * Default URL handlers - (Action)/(ID)/(OtherID)
     */
    private static $url_handlers = array(
        '/$Action//$OwnerID/$Title/$Longitude/$Latitude/$FilterCode/$SecondFilterCode' => 'handleAction',
    );





    #################
    # SESSION MANAGEMENT
    #################

    protected static function session_var_name($filterCode = "")
    {
        return Config::inst()->get("GoogleMapDataResponse", "session_var_prefix")."_".$filterCode;
    }

    /**
     * @param Array $addCustomGoogleMapArrayNEW
     * @param string $filterCode
     *
     */
    public static function add_custom_google_map_session_data($addCustomGoogleMapArrayNEW, $filterCode = "")
    {
        $addCustomGoogleMapArrayOLD = Session::get(self::session_var_name($filterCode));
        $addCustomGoogleMapArrayNEW = array_merge($addCustomGoogleMapArrayOLD, $addCustomGoogleMapArrayNEW);
        Session::set(Session::get(self::session_var_name($filterCode), serialize($addCustomGoogleMapArrayNEW)));
    }

    /**
     *
     *
     * @param Array $addCustomGoogleMapArray
     * @param string $filterCode
     *
     */
    public static function set_custom_google_map_session_data($addCustomGoogleMapArray, $filterCode = "")
    {
        if (!is_array($addCustomGoogleMapArray)) {
            user_error("addCustomGoogleMapArray should be an array!");
        }
        Session::set(self::session_var_name($filterCode), serialize($addCustomGoogleMapArray));
    }

    /**
     * @param string $filterCode
     *
     * @return Array
     */
    public static function get_custom_google_map_session_data($filterCode = "")
    {
        $data = Session::get(self::session_var_name($filterCode));
        if (is_array($data)) {
            $addCustomGoogleMapArray = $data;
        } else {
            try {
                $addCustomGoogleMapArray = unserialize($data);
            } catch (Exception $e) {
                $addCustomGoogleMapArray = array();
            }
        }
        return $addCustomGoogleMapArray;
    }

    /**
     *
     * @param string $filterCode
     */
    public static function clear_custom_google_map_session_data($filterCode = "")
    {
        Session::clear(self::session_var_name($filterCode));
    }







    #################
    # BASICS
    #################
    /**
     * @inherited
     */
    private static $allowed_actions = array(
        'showemptymap' => true,
        'showpagepointsmapxml' => true,
        'showchildpointsmapxml' => true,
        'showdirectchildren' => true,
        'showsearchpoint' => true,
        'showcustompagesmapxml' => true,
        'showcustomdosmapxml' => true,
        'showdataobjects' => true,
        'updatemexml' => 'ADMIN',
        'showaroundmexml' => true,
        'showpointbyid' => true
    );

    /**
     * @var Array
     */
    private static $actions_without_owner = array(
        'showemptymap'
    );

    /**
     * The Page that is displaying the
     * @var SiteTree
     */
    protected $owner = null;

    /**
     * @var Float
     */
    protected $lng = 0;

    /**
     * @var Float
     */
    protected $lat = 0;

    /**
     * @var String
     */
    protected $title = "";

    /**
     * @var String
     */
    protected $filterCode = "";

    /**
     * @var String
     */
    protected $secondFilterCode = "";

    /**
     * @var GoogleMap
     */
    protected $map = null;











    #################
    # SET AND GET VARIABLES
    #################

    public function init()
    {
        parent::init();
        $id = 0;
        if ($this->request->param("OwnerID")) {
            $id = intval($this->request->param("OwnerID"));
        } elseif (isset($_GET["i"])) {
            $i = intval($_GET["i"]);
            $point = GoogleMapLocationsObject::get()->byID($i);
            if (!$point) {
                //New POINT
            } else {
                $id = $point->ParentID;
            }
        }
        if ($id) {
            $this->owner = SiteTree::get()->byID($id);
        }
        //HACK
        elseif (!$this->owner) {
            $this->owner = DataObject::get_one(
                'SiteTree',
                array("Title" => Convert::raw2sql($this->request->param("Title")))
            );
        }
        if ($this->owner || in_array($this->request->param("Action"), self::$actions_without_owner)) {
            //all ok
        } elseif (in_array($this->request->param("Action"), self::$actions_without_owner)) {
            //ok too
            $this->owner = DataObject::get_one('SiteTree');
        } else {
            user_error("no owner has been identified for GoogleMapDataResponse", E_USER_NOTICE);
        }
        //END HACK
        $this->title = urldecode($this->request->param("Title"));
        $this->lng = floatval($this->request->param("Longitude"));
        $this->lat = floatval($this->request->param("Latitude"));
        $this->filterCode = urldecode($this->request->param("FilterCode"));
        $this->secondFilterCode = urldecode($this->request->param("SecondFilterCode"));
        if (!$this->title && $this->owner) {
            $this->title = $this->owner->Title;
        }
    }

    /**
     * @param object $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @param String $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param float $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * @param Float $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * @param string $filterCode
     */
    public function setFilterCode($filterCode)
    {
        $this->filterCode = $filterCode;
    }











    #################
    # ACTIONS
    #################

    /**
     * @param SS_HTTPRequest
     *
     * @return String (XML)
     */
    public function index($request)
    {
        return $this->showemptymap($request);
    }

    /**
     * @param SS_HTTPRequest
     *
     * @return String (XML)
     */
    public function showemptymap($request)
    {
        return $this->makeXMLData(null, null, $this->title, $this->title." "._t("GoogleMap.MAP", "map"));
    }

    /**
     * @param SS_HTTPRequest
     *
     * @return String (XML)
     */
    public function showpagepointsmapxml($request)
    {
        $data = GoogleMapLocationsObject::get()->filter(array("ParentID" => $this->owner->ID));
        if ($data->count()) {
            return $this->makeXMLData(null, $data, $this->title, $this->title." "._t("GoogleMap.MAP", "map"));
        }
        return $this->showemptymap($request);
    }

    /**
     * @param SS_HTTPRequest
     *
     * @return String (XML)
     */
    public function showchildpointsmapxml($request)
    {
        if ($children = $this->owner->getChildrenOfType($this->owner, null)) {
            return $this->makeXMLData($children, null, $this->title, $this->title." "._t("GoogleMap.MAP", "map"));
        }
        return $this->showemptymap($request);
    }

    /**
     * @param SS_HTTPRequest
     *
     * @return String (XML)
     */
    public function showdirectchildren($request)
    {
        if ($children = Provider::get()) {
            return $this->makeXMLData($children, null, $this->title, $this->title." "._t("GoogleMap.MAP", "map"));
        }
        return $this->showemptymap($request);
    }

    /**
     * @param SS_HTTPRequest
     *
     * @return String (XML)
     */
    public function showsearchpoint($request)
    {
        if ($this->lat && $this->lng) {
            $point = GoogleMapLocationsObject::create();
            $point->ParentID = $this->owner->ID;
            $point->Latitude = $this->lat;
            $point->Longitude = $this->lng;
            $point->CustomPopUpWindowTitle = $this->title;
            if ($this->address) {
                die("get address to do");
                $point->CustomPopUpWindowInfo = $this->address;
            }
            if ($point) {
                $data = new ArrayList();
                $data->push($point);
                return $this->makeXMLData(null, $data, $this->title, $this->title);
            }
        }
        return $this->showemptymap($request);
    }

    /**
     * @param SS_HTTPRequest
     *
     * @return String (XML)
     */
    public function showpointbyid($request)
    {
        $id = $request->param("FilterCode");
        $ids = explode(',', $id);
        foreach ($ids as $key => $id) {
            $ids[$key] = intval($id);
        }
        $className = Convert::raw2sql($request->param("SecondFilterCode"));
        $direct = false;
        if (! $className) {
            $direct = true;
        } elseif (! class_exists($className)) {
            $direct = true;
        }
        if ($direct) {
            $className = "GoogleMapLocationsObject";
        }
        $objects = $className::get()->filter(array("ID" => $ids));
        if ($direct) {
            return $this->makeXMLData(null, $objects, $this->title, $this->title);
        } else {
            return $this->makeXMLData($objects, null, $this->title, $this->title);
        }
    }


    /**
     * load data from session
     *
     * @param SS_HTTPRequest
     *
     * @return String (XML)
     */
    public function showcustompagesmapxml($request)
    {
        $addCustomGoogleMapArray = GoogleMapDataResponse::get_custom_google_map_session_data($this->filterCode);
        $pages = SiteTree::get()->filter(array("ID" => $addCustomGoogleMapArray));
        return $this->makeXMLData($pages, null, $this->title, $this->title);
    }

    /**
     * load a custom set of GoogleMapLocationsObjects
     *
     * @param SS_HTTPRequest
     *
     * @return String (XML)
     */
    public function showcustomdosmapxml($request)
    {
        $array = GoogleMapDataResponse::get_custom_google_map_session_data($this->filterCode);
        $googleMapLocationsObjects = GoogleMapLocationsObject::get()->filter(array("ID" => $array));
        return $this->makeXMLData(null, $googleMapLocationsObjects, $this->title, $this->title);
    }

    /**
     * Show what is around my points
     *
     * @param SS_HTTPRequest
     *
     * @return String (XML)
     */
    public function showaroundmexml($request)
    {
        $lng = 0;
        $lat = 0;
        $excludeIDList = array();
        $stage = '';
        if (Versioned::current_stage() == "Live") {
            $stage = "_Live";
        }
        if ($this->lng && $this->lat) {
            $lng = $this->lng;
            $lat = $this->lat;
        } elseif ($this->owner->ID) {
            //find the average!
            $objects = GoogleMapLocationsObject::get()->filter(array("ParentID" => $this->owner->ID));
            if ($count = $objects->count()) {
                foreach ($objects as $point) {
                    $lng += $point->Longitude;
                    $lat += $point->Latitude;
                }
                $lng = $lng / $count;
                $lat = $lat / $count;
            }
        }
        $classNameForParent = '';
        if ($otherClass = $this->filterCode) {
            $classNameForParent = $otherClass;
        }
        if ($this->title) {
            $title = $this->title;
        } else {
            $title = _t("GoogleMap.CLOSES_TO_ME", "Closest to me");
        }
        if ($lng && $lat) {
            $orderByRadius = GoogleMapLocationsObject::radius_definition($lng, $lat);
            $where = "(".$orderByRadius.") > 0 AND \"GoogleMapLocationsObject\".\"Latitude\" <> 0 AND \"GoogleMapLocationsObject\".\"Longitude\" <> 0";
            if ($classNameForParent && !is_object($classNameForParent)) {
                $where .= " AND \"SiteTree".$stage."\".\"ClassName\" = '".$classNameForParent."'";
            }
            if (count($excludeIDList)) {
                $where .= " AND \"GoogleMapLocationsObject\".\"ID\" NOT IN (".implode(",", $excludeIDList).") ";
            }
            $objects = GoogleMapLocationsObject::get()
                ->where($where)
                ->sort($orderByRadius)
                ->leftJoin("SiteTree".$stage."", "SiteTree".$stage.".ID = GoogleMapLocationsObject.ParentID")
                ->limit(Config::inst()->get("GoogleMap", "number_shown_in_around_me"));
            if ($objects->count()) {
                return $this->makeXMLData(
                    null,
                    $objects,
                    $title,
                    Config::inst()->get("GoogleMap", "number_shown_in_around_me") . " "._t("GoogleMap.CLOSEST_POINTS", "closest points")
                );
            }
        }
        return $this->showemptymap($request);
    }

    /**
     * URL must contain for GET variables
     * i - ID of owner
     * a - action
     * x - lng
     * y - lat
     *
     * actions are:
     *   - add
     *   - move
     *   - remove
     *
     * @param SS_HTTPRequest
     *
     * @return String (message)
     */
    public function updatemexml($request)
    {
        //we use request here, because the data comes from javascript!
        if ($this->owner->canEdit()) {
            if (isset($_REQUEST["x"]) && isset($_REQUEST["y"]) && isset($_REQUEST["i"]) && isset($_REQUEST["a"])) {
                $lng = floatval($_REQUEST["x"]);
                $lat = floatval($_REQUEST["y"]);
                $id = intval($_REQUEST["i"]);
                $action = $_REQUEST["a"];
                if ($lng && $lat) {
                    if (0 == $id && "add" == $action) {
                        $point = new GoogleMapLocationsObject;
                        $point->ParentID = $this->owner->ID;
                        $point->Longitude = $lng;
                        $point->Latitude = $lat;
                        $point->write();
                        return $point->ID;
                    } elseif ($id > 0 && "move" == $action) {
                        $point = GoogleMapLocationsObject::get()->byID($id);
                        if ($point) {
                            if ($point->ParentID == $this->owner->ID) {
                                $point->Longitude = $lng;
                                $point->Latitude = $lat;
                                $point->Address = "";
                                $point->FullAddress = "";
                                $point->write();
                                return  _t("GoogleMap.LOCATION_UPDATED", "location updated");
                            } else {
                                return _t("GoogleMap.NO_PERMISSION_TO_UPDATE", "you dont have permission to update that location");
                            }
                        } else {
                            return _t("GoogleMap.COULD_NOT_FIND_LOCATION", "could not find location");
                        }
                    } elseif ($id && "remove" == $action) {
                        $point = GoogleMapLocationsObject::get()->byID($id);
                        if ($point) {
                            if ($point->ParentID == $this->owner->ID) {
                                $point->delete();
                                $point = null;
                                return _t("GoogleMap.LOCATION_DELETED", "location deleted");
                            } else {
                                return _t("GoogleMap.NO_DELETE_PERMISSION", "you dont have permission to delete that location");
                            }
                        } else {
                            return _t("GoogleMap.COULD_NOT_FIND_LOCATION", "could not find location.");
                        }
                    }
                } else {
                    return _t("GoogleMap.LOCATION_NOT_DEFINED", "point not defined.");
                }
            } else {
                return _t("GoogleMap.MISSING_VARIABLES", "not enough information was provided.");
            }
        }
        return  _t("GoogleMap.POINT_NOT_UPDATED", "You do not have permission to change the map.");
    }







    #################
    # TEMPLATE METHODS
    #################
    /**
     *
     * @return GoogleMap
     */
    public function GoogleMapController()
    {
        if (!$this->map) {
            user_error("No map has been created");
        }
        return $this->map;
    }








    #################
    # PRIVATE PARTY
    #################

    /**
     * @param DataList $pages
     * @param DataList $dataPoints
     * @param string $title
     * @param string $selectionStatement
     *
     * @return String (XML)
     */
    protected function makeXMLData(
        $pages = null,
        $dataPoints = null,
        $title = '',
        $selectionStatement = ''
    ) {
        $this->response->addHeader("Content-Type", "text/xml; charset=\"utf-8\"");
        return self::xml_sheet(
            $pages,
            $dataPoints,
            $title,
            $selectionStatement = ''
        );
    }



    ################################
    # STATIC METHODS
    ################################

    public static function xml_sheet(
        $pages = null,
        $dataPoints = null,
        $title = '',
        $selectionStatement = ''
    ) {
        $map = Injector::inst()->get("GoogleMap");
        $map->setTitleOfMap($title);
        $map->setWhereStatementDescription($selectionStatement ? $selectionStatement : $title);
        if ($pages) {
            $map->setPageDataObjectSet($pages);
        } elseif ($dataPoints) {
            $map->setPoints($dataPoints);
        }
        $map->createDataPoints();
        return $map->renderWith("GoogleMapXml");
    }



    /**
     * var arrayOfLatitudeAndLongitude: Array (Latitude" => 123, "Longitude" => 123, "Marker" => "red1");
     * Marker is optional
     * @param Array arrayOfLatitudeAndLongitude
     * @param String title
     *
     * @return String (HTML - img tag)
     */

    public static function quick_static_map($arrayOfLatitudeAndLongitude, $title)
    {
        $staticMapURL = '';
        $count = 0;
        //width
        $staticMapWidth = Config::inst()->get("GoogleMap", "google_map_width");
        if ($staticMapWidth > 512) {
            $staticMapWidth = 512;
        }
        //height
        $staticMapHeight = Config::inst()->get("GoogleMap", "google_map_height");
        if ($staticMapHeight > 512) {
            $staticMapHeight = 512;
        }
        $staticMapURL = "size=".$staticMapWidth."x".$staticMapHeight;
        if (count($arrayOfLatitudeAndLongitude)) {
            //http://maps.google.com/maps/api/staticmap?sensor=true&maptype=map&size=209x310&
            //markers=color:green%7Clabel:A%7C-45.0302,168.663
            //&markers=color:red%7Clabel:Q%7C-36.8667,174.767
            foreach ($arrayOfLatitudeAndLongitude as $row) {
                $staticMapURL .= '&amp;markers=color:'.$row["Colour"].'%7Clabel:'.$row["Label"].'%7C';
                $staticMapURL .= round($row["Latitude"], 6).",".round($row["Longitude"], 6);
                $count++;
            }
            if ($count == 1) {
                $staticMapURL .= '&amp;center='.$defaultCenter.'&amp;zoom='. Config::inst()->get("GoogleMap", "default_zoom");
            }
        }
        return self::make_static_map_url_into_image($staticMapURL, $title);
    }

    /**
     * @param String $staticMapURL
     * @param String $title
     *
     * @return String (HTML - img tag)
     */
    protected static function make_static_map_url_into_image($staticMapURL, $title)
    {
        $fullStaticMapURL =
            'http://maps.google.com/maps/api/staticmap?'
                .Config::inst()->get("GoogleMap", "static_map_settings").'&amp;'
                .$staticMapURL.'&amp;'
                .'key='.Config::inst()->get("GoogleMap", "google_map_api_key");
        if (Config::inst()->get("GoogleMap", "save_static_map_locally")) {
            $fileName = str_replace(array('&', '|', ',', '=', ';'), array('', '', '', '', ''), $staticMapURL);
            $length = strlen($fileName);
            $fileName = "_sGMap".substr(hash("md5", $fileName), 0, 35)."_".$length.".gif";
            $fullStaticMapURL = StaticMapSaverForHTTPS::convert_to_local_file(str_replace('&amp;', '&', $fullStaticMapURL), $fileName);
        }
        return '<img class="staticGoogleMap" src="'.$fullStaticMapURL.'" alt="map: '.Convert::raw2att($title).'" />';
    }
}
