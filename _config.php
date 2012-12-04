<?php


/**
 * developed by www.sunnysideup.co.nz
 * author: Nicolaas - modules [at] sunnysideup.co.nz
**/

//TO DO MIGRATION TO V3: http://markus.tao.at/geo/google-maps-api-v3-is-in-town/



Director::addRules(40, array(
	'googlemap/$Action/$OwnerID/$Title/$Longitude/$Latitude/$Filter' => 'GoogleMapDataResponse'
));

//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
//===================---------------- START googlemap MODULE ----------------===================
/*
define("GoogleMapAPIKey", "abc");

//add maps to sitetree
//Object::add_extension('SiteTree', 'GoogleMapLocationsDOD');
//Object::add_extension('ContentController', 'GoogleMapLocationsDOD_Controller');
//GoogleMapLocationsDOD::set_page_classes_with_map(array("HomePage"));
//GoogleMapLocationsDOD::set_page_classes_without_map(array("UserDefinedForms"));

//SENSOR - SOME CRAZY STUFF
GoogleMap::set_uses_sensor(true); // should be set to false!

// MAP
GoogleMap::setDefaultLatitude(12.0001);
GoogleMap::setDefaultLongitude(133.2210);
GoogleMap::setDefaultZoom(2);
GoogleMap::setGoogleMapWidth(473); //map width in pixels (ranges from around 100 to 900)
GoogleMap::setGoogleMapHeight(525); //map height in pixels (ranges from around 100 to 600)
GoogleMap::setMapTypeDefaultZeroToTwo(3); //"0" => "normal", "1" => "satellite", "2" => "satellite with markings", "3" => "natural"
GoogleMap::setViewFinderSize(200); //size of the view finder in pixels (e.g. 250)
GoogleMap::setMapAddTypeControl(true);//Allow the visitor to change the map type (e.g. from a satellite to a normal map)
GoogleMap::setMapControlSizeOneToThree(3);//map controller size (allows user to zoom and pan)", array("1" => "small", "2" => "medium", "3" => "large"), $value = "3"));
GoogleMap::setMapScaleInfoSizeInPixels(150); //size of the map scale in pixels (default is 100)
GoogleMap::setShowStaticMapFirst(0); //if set to 1, the map will load as a picture rather than an interactive map, with the opportunity to start an interactive map - you need to set this to 1 (true)
GoogleMap::set_number_shown_in_around_me(7); //if set to 1, the map will load as a picture rather than an interactive map, with the opportunity to start an interactive map - you need to set this to 1 (true)

/* STATIC MAP SETTINGS
//center=-41.2943,173.2210&amp;zoom=5&amp;size=512x512&amp;maptype=roadmap - ONLY MAPTYPE IS REQUIRED
//max size is 512pixels
GoogleMap::setStaticMapSettings("maptype=terrain"); //
GoogleMap::setStaticIcon("red1"); - -e.g. bluea - alternatively you can use the setDefaultIcon to any absolute URL
GoogleMap::set_save_static_map_locally(false);
	//# {color} (optional) specifies a color from the set {black, brown, green, purple, yellow, blue, gray, orange, red, white}.
	//# {alphanumeric-character} (optional) specifies a single lowercase alphanumeric character from the set {a-z, 0-9}. Note that default and mid sized

// INCLUSIONS
GoogleMap::setNoStatusAtAll(false); //hide map status (which shows information like ... loading new points now ...)
GoogleMap::setHiddenLayersRemovedFromList(false); //remove points hidden by visitors to your map
GoogleMap::setAddAntipodean(false); //add antipodean option (allowing visitors to find the exact opposite point on earth)
GoogleMap::setChangePageTitle(false); //adjust the page title when you change the map
GoogleMap::setAddDirections(false); //add directions finder to map pop-up windows
GoogleMap::setAddAddressFinder(false); //provide an address finder helping visitors to enter an address and search for it on the map

// POLYS
GoogleMap::setLineColour("#dcb916"); //colour for additional lines (e.g. routes) on map (use web colour codes)
GoogleMap::setLineWidth(5); //width of the line in pixels
GoogleMap::setLineOpacity(0.5); //opacity for the line (default is 0.5 - should range from transparent: 0 to opaque: 1
GoogleMap::setFillColour("#dcb916");//colour for polygons (e.g. regions) on map
GoogleMap::setFillOpacity(0.3);//opacity for polygons (default is 0.3 - should range from transparent: 0 to opaque: 1)
GoogleMap::setPolyIcon(""); //location for icon used for polygon and polyline (e.g. http://www.mysite.com/icon.png)

// HELPDIVS
GoogleMap::setSideBarDivId(""); //ID for DIV that shows additional information about map leave blank to remove)"
GoogleMap::setDropDownDivId("GoogleMapDropDownList"); //ID for DIV of dropdown box with points in map (leave blank to remove)
GoogleMap::setTitleDivId("GmapTitleID"); //ID for DIV of map title (leave blank to remove)
GoogleMap::setLayerListDivId(""); //ID for DIV that shows list of map layers (leave blank to remove)
GoogleMap::setDirectionsDivId(""); //ID for DIV that shows directions from map (leave blank to remove)
GoogleMap::setStatusDivId(""); //ID for DIV that shows status of map

// INFOWINDOW
GoogleMap::setInfoWindowOptions("{maxWidth:280, zoomLevel:17, mapType:G_HYBRID_MAP}"); //info window options (see http://code.google.com/apis/maps/documentation/reference.html for details)
GoogleMap::setAddCurrentAddressFinder(false); //add a tab with the address finder
GoogleMap::setAddZoomInButton(""); //add a "zoom in" link on info window, should be emptry string or text for button.
GoogleMap::setAddCloseUpButton(""); //add a "close-up" link on info window, shouldbe emptry string or text for button.
GoogleMap::setAddCloseWindowButton(""); //add a "close window" link on info window, shouldbe emptry string or text for button.
GoogleMap::set_ajax_info_window_text("view details");

// MARKER
GoogleMap::setMarkerOptions("{draggable:false,bouncy:true,title: \"click me\"}"); //marker options (see http://code.google.com/apis/maps/documentation/reference.html for details)
GoogleMap::AddPointsToMap(true);
GoogleMap::AddDeleteMarkerButton("remove this location");
GoogleMap::AllowMarkerDragAndDrop(true);,
GoogleMap::MarkerOptions(true);
GoogleMap::PreloadImages(true);

//ICONS
GoogleMap::setPreloadImages(true); //pre-load marker images
GoogleMap::setDefaultIconUrl(""); //default Icon Url
GoogleMap::setIconFolder("googlemap/images/icons/"); //default Icon Folder - icons need to be name: i1, i2, i3, i4, etc...
GoogleMap::setIconWidth(20); //default icon width in pixels (e.g. 20)
GoogleMap::setIconHeight(34); //default icon height in pixels (e.g. 34)
GoogleMap::setIconImageMap("[0,0 , 3,2 , 7,7]"); // can leave blank by setting it to []
GoogleMap::setIconExtension("png"); //default icon extension (e.g. png, gif or jpg)
GoogleMap::setIconMaxCount(12); //maximum number of layers, before reverting back to icon number one (e.g. 12)

// SERVER INTERACTION
GoogleMap::setLatFormFieldId(""); //latitude form field to be updated on new marker creation or marker movement
GoogleMap::setLngFormFieldId(""); //longitude form field to be updated on new marker creation or marker movement

// ADDRESS
GoogleMap::setDefaultCountryCode(""); //default country code for address searches (to narrow searches to one country) - examples include US or NZ
GoogleMap::setDefaultAddressText(""); //extra phrase added to the end of an address (e.g. New Zealand or United Kingdom)
GoogleMap::setStyleSheetUrl("googlemap/css/googleMapDirections.css"); //style sheet to be used for formatting directions (e.g. googlemap/css/mapDirections.css)
GoogleMap::setLocaleForResults("en_NZ"); //language to be used for directions (e.g. en_US, fr, fr_CA, en_NZ, etc...
*/


//SEARCH
//GoogleMapLocationsDOD_Controller::set_class_name_only("MyPage");
//===================---------------- END googlemap MODULE ----------------===================
