/**
 * many thanks to : http://econym.googlepages.com/index.htm
 *
 * @todo:
 * - make GMO into generic object that can have any name! (e.g. replace GMO with "this")
 * - replace variables below with encapsulated values
 * - http://net.tutsplus.com/tutorials/javascript-ajax/the-basics-of-object-oriented-javascript/
 *
 * Structure:
 * van MyMap = new GoogleMapConstructor("mapDivName", "URLForPoints", "MyMap", options);
 *
 * @see:
 *  - https://developers.google.com/maps/documentation/javascript/reference
 *
 *
 */

jQuery(document).ready(
    function(){
        if(typeof GoogleMapConstructors !== "undefined") {
            for(var i = 0; i < GoogleMapConstructors.length; i++) {
                var obj = GoogleMapConstructors[i];
                GoogleMapConstructors[i].googleMap = new GoogleMapConstructor(
                    obj.divID,                            //div ID
                    null,                                 //url for map... added via layers
                    GoogleMapConstructors[i].googleMap,   // object that holds constructor
                    obj.options                           //map options ...
                );
                GoogleMapConstructors[i].googleMap.init();
                if(typeof obj.layers !== "undefined") {
                    var layers = obj.layers;
                    if(layers.length > 0) {
                        for(var j = 0; j < layers.length; j++) {
                            console.debug(layers[j].link);
                            console.debug(layers[j].title);
                            GoogleMapConstructors[i].googleMap.addLayer(layers[j].link, layers[j].title);
                        }
                    }
                }
                if(typeof obj.address !== "undefined") {
                    if(obj.address.length > 0) {
                        GoogleMapConstructors[i].googleMap.findAddress(obj.address);
                    }
                }


            }
        }
    }
);

var GoogleMapTranslatableUserMessages = {
    position_saved: "position saved.",
    return_to_position: "returned to saved position.",
    map_ready: "map ready.",
    currently_set_to: "currently set to: ",
    loading_map: "loading . . .",
    manually_added_point: "manually added point.",
    points: "points",
    updating_database: "updating database",
    drill_a_hole: "drill a hole",
    found_opposite: "Exact opposite point on earth - goodluck finding your way back...",
    antipodean_of: "Antipodean of",
    drag_instructions: "marker can be dragged to new location once this info window has been closed.",
    partial_obscuring: "this marker (partially) obscures the following point(s):",
    and_with_spaces: " and ",
    error_in_zoom: "Error in MaxZoomService",
    select_this_point: "select this point",
    clear_last_route: "clear Last Route",
    do_next: "do next:",
    calculate_route: "calculate route",
    alernatively: "Alternatively:",
    create_routes_using_current_points: "create route using current points.",
    this_point: "This Point",
    find_address: "find address",
    address_not_found: "address not found.",
    address_not_retrieved: "address could not be retrieved from server.",
    address_not_accurate: "NB: Address may not be accurate",
    close: "close",
    remove: "remove",
    added_to_db: "added point to database",
    added_to_db_error: "could NOT add point to database",
    loaded: "loaded",
    error_in_loading: "sorry - could not load data",
    processing: "processing",
    of: "of",
    locations_added: "locations added",
    one_location_loaded: "one location loaded.",
    large_number_of_items: "location are being loaded. It may take a while for all the locations to show on the map, please be patient.",
    no_location_could_be_found: "No locations could be found.",
    updating_lists: "Updating lists . . .",
    you_added: "You added",
    select_a_location: " - Highlight a Location on the Map - ",
    kml: "kml",
    hide: "hide [x]",
    show_help: "show help",
    can_not_hide_bar_in_full_screen_mode: "Can not hide this bar in full screen mode",
    searching_for_address: "searching for address . . .",
    added_position: "added position",
    address_finder_not_loaded: "ERROR: address finder not loaded.",
    address_not_found: "Sorry, we were unable to find that address.",
    address_search: "address search",
    address_found: "address found",
    no_valid_start: "No valid start location has been selected.",
    no_valid_end: "No valid end location has been selected.",
    searching_for_route: "Searching for Route . . .",
    no_route_could_be_found: "No Route could be Found . . .",
    from: "from",
    to: "to",
    do_next: "do next"
};

/**
 * main METHOD to encapsulate a map
 * You use this as follows:
 * var GMO = new GoogleMapConstructor()
 * @param String mapDivName
 * @param String url - url that provides points as XML
 * @param String name for the variable name being assigned to GMC
 *    e.g. var XXX = new GoogleMapConstructor(....); then the variable name is XXX
 * @param Object opts - list of options
 *    these are all the PHP options provided for the map...
 */
function GoogleMapConstructor(mapDivName, url, variableName, opts) {

    var GMO = {

        /**
         * key variable translator that allows
         * deep into the code references to talk to "this"
         * @var Object
         */
        variableName: variableName,

        /**
         * map holder
         * @var Object
         */
        mapObject: null,

        /**
         *
         * @var Object
         */
        geocoder: null,

        /**
         *
         * @var Object
         */
        directions: null,

        /**
         *
         * @var Object
         */
        directionsDisplay: null,

        /**
         * default longitude
         * @var Float
         */
        NZLongitude: '0.0001',//173.2210

        /**
         * default latitude
         * @var Float
         */
        NZLatitude: '0.0001',//-41.2943

        /**
         * default zoom
         * @var Float
         */
        NZZoom: 2,

        /**
         * array key of added point
         * NOT USED!!!!
         * @var Int
         */
        addedPoint: 0,

        /**
         * array of markers
         * @var Array
         */
        markersArray: [],

        /**
         * array of markers
         * @var Array
         */
        _t: GoogleMapTranslatableUserMessages,



        /**
         * adds layer to map with with points
         * @param URL url
         * @param string url
         * @todo: encapsulate
         */
        addLayer: function(url, title) {
            GMO.downloadXml(url, title);
            return true;
        },

        /**
         * add a point to the map
         * @todo: change processXML to something else...
         * @todo: move xmlParse to createPointXml method
         * @param LatLng latLng
         * @param String nameString - name for marker
         * @param String description - description for marker
         */
        addPoint: function(latLng, nameString, description) {
            var xmlSheet = GMO.createPointXml(nameString, latLng, description);
            GMO.processXml(xmlSheet);
        },

        /**
         * finds an address on the map, similar to the opening page of maps.google.com
         * @param String address
         */
        findAddress: function(address) {
            GMO.showAddress(address);
            return true;
        },

        /**
         * shows a route for pre-selected locations
         */
        findRoute: function() {
            GMO.showRoute();
            return true;
        },

        /**
         * saves your current location on the map
         */
        savePosition: function() {
            GMO.savePositionNow(GMO.mapObject);
            GMO.updateStatus(GMO._t.position_saved);
            return true;
        },

        /**
         * resets the map to last saved position
         */
        goToSavedPosition: function() {
            //GMO.mapObject.returnToSavedPosition();
            GMO.returnToSavedPosition(GMO.mapObjec);
            GMO.updateStatus(GMO._t.return_to_position);
            return true;
        },




        /**
         * provides a map type
         * @param Int i = type of map
         * @return Google Map Type
         */
        mapTypesArray: function(i){
            var a = new Array();
            a[1] = google.maps.MapTypeId.ROADMAP;
            a[2] = google.maps.MapTypeId.SATELLITE;
            a[3] = google.maps.MapTypeId.HYBRID;
            a[4] = google.maps.MapTypeId.TERRAIN;
            return a[i];
        },

        /**
         * provides a map type
         * @param Int i = type of map
         * @return Google Map Type
         * @todo Private
         */
        zoomControlStyleArray: function(i){
            var a = new Array();
            a[1] = google.maps.ZoomControlStyle.SMALL;
            a[2] = google.maps.ZoomControlStyle.SMALL;
            a[3] = google.maps.ZoomControlStyle.LARGE;
            return a[i];
        },

        /**
         * basic setup of map
         * @param String mapDivName
         */
        setupMap: function(mapDivName) {
            this.opts.mapControlSizeOneToThree = this.zoomControlStyleArray(this.opts.mapControlSizeOneToThree-0);
            var mapOptions = {
                center: new google.maps.LatLng(this.opts.defaultLatitude, this.opts.defaultLongitude),
                zoom: this.opts.defaultZoom,

                // Add map type control
                mapTypeControl: this.opts.mapAddTypeControl,
                mapTypeControlOptions: {
                    style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                    position: google.maps.ControlPosition.TOP_LEFT
                },
                //turn off annoying map zoom with scrollwheel
                scrollwheel: false,
                // Add pan
                panControl: true,
                // Add zoom
                zoomControl: true,
                zoomControlOptions:{
                    style: this.opts.mapControlSizeOneToThree
                },
                // Add scale
                scaleControl: true,
                scaleControlOptions: {
                        position: google.maps.ControlPosition.BOTTOM_RIGHT
                }
            };
            GMO.mapObject = new google.maps.Map(document.getElementById(mapDivName), mapOptions);
            //standard
            //new GKeyboardHandler(GMO.mapObject);
            //GMO.mapObject.enableContinuousZoom();
            //GMO.mapObject.enableDoubleClickZoom();
            //optional
            //GMO.mapObject.setMapTypeId();
            //if(this.opts.mapAddTypeControl) { GMO.mapObject.addControl(new GMapTypeControl()); }
            //if(this.opts.mapScaleInfoSizeInPixels > 0) {GMO.mapObject.addControl(new GScaleControl(this.opts.mapScaleInfoSizeInPixels)); }]
            //if(this.opts.mapControlSizeOneToThree == 3) {
                //GMO.mapObject.addControl(new GLargeMapControl());
            //}
            //else if(this.opts.mapControlSizeOneToThree == 2) {
                //GMO.mapObject.addControl(new GSmallMapControl());
            //}
            //else {
                //GMO.mapObject.addControl(new GSmallZoomControl());
            //}
            //add statusDiv
            if(this.opts.statusDivId == "statusDivOnMap" && !this.opts.noStatusAtAll) {
                //GMO.mapObject.addControl(new this.statusDivControl);
            }
            if(this.opts.addDirections) {
                GMO.directions = new google.maps.DirectionsService();
                GMO.directionsDisplay = new google.maps.DirectionsRenderer();
                GMO.directionsDisplay.setMap(GMO.mapObject);
                google.maps.event.addListener(GMO.directions, "load",  function() {
                        GMO.directionsOnLoad();
                });
                google.maps.event.addListener(GMO.directions, "error", this.directionsHandleErrors);
                //start icon
                G_START_ICON = "";
                G_START_ICON.iconSize = new google.maps.Size(0, 0);
                G_START_ICON.image="";
                G_START_ICON.shadow = "";
                //end icon
                G_END_ICON = "";
                G_END_ICON.iconSize = new google.maps.Size(0, 0);
                G_END_ICON.image="";
                G_END_ICON.shadow = "";
            }
            google.maps.event.addListener(
                GMO.mapObject,
                "click",
                function(event) {
                    var marker = null;
                    if(marker) {
                        //update marker details
                        alert(marker);
                        alert("marker");
                    }
                    else {
                        var latLng = new google.maps.LatLng(event.latLng.lat(), event.latLng.lng(), false);
                        GMO.zoomTo(latLng, GMO.mapObject.getZoom() +1);
                    }
                }
            );
            google.maps.event.addListener(
                GMO.mapObject,
                "rightclick",
                function(point) {
                    var latLng = new google.maps.LatLng(point.latLng.lat(), point.latLng.lng(), false);
                    point = new google.maps.Point(point.x, point.y);
                    if(GMO.opts.addPointsToMap) {
                        var nameString = "Longitude (" + Math.round(point.x*10000)/10000 + ") and Latitude (" + Math.round(point.y*10000)/10000 + ")";
                        var pointLngLat = new google.maps.LatLng(point.x, point.y);
                        var description = GMO._t.manually_added_point;
                        xmlSheet = GMO.createPointXml(nameString,pointLngLat,description);
                        GMO.processXml(xmlSheet);
                    }
                    else {
                        GMO.zoomTo(latLng, GMO.mapObject.getZoom()-1);
                    }
                }
            );
            if(this.opts.viewFinderSize > 0) {

                window.setTimeout(
                    function() {
                        //GMO.viewFinderSize = GM0.opts.viewFinderSize;
                        //Call commented out for now, readd later
                        //GMO.addViewFinder(GMO.opts.viewFinderSize, GMO.opts.viewFinderSize);
                    }, 2000
                );
            }
        },

        /**
         * resets map
         */
        basicResetMap: function() {
            //GMO.mapObject.checkResize();
            GMO.mapObject.setCenter(this.opts.defaultLatitude, this.opts.defaultLongitude);
            GMO.mapObject.setZoom(this.opts.defaultZoom);
        },

        /**
         * add view fiender
         * @param Int width
         * @param Int height
         * @todo //to be completed later, call has been commented out for now, see line 223
         * @todo make private?
         */
        addViewFinder: function(width, height) {
            var ovSize = new google.maps.Size(width, height);
            var ovMap = new google.maps.OverviewMapControlOptions(ovSize);
            GMO.mapObject.addControl(ovMap);
            window.setTimeout(
                function() {
                    var mini = ovMap.getOverviewMap();
                    ovMap.hide(true);
                }, 1000
            );
        },

        /**
         * zoom into a particular point
         * @param latitudeAndlongitude
         * @param Int zoom
         * @todo make public
         * @todo turn latitudeAndlongitude into two variables!
         */
        zoomTo: function(latitudeAndlongitude, zoom) {
            if(latitudeAndlongitude && zoom) {
                GMO.mapObject.setZoom(zoom);
                GMO.mapObject.panTo(latitudeAndlongitude);
            }
            else{
                GMO.mapObject.setCenter(new google.maps.LatLng(latitudeAndlongitude, false));
            }
        },

        /**
         * saves map position
         * @todo make public
         */
        savePositionNow: function() {
            GMO.previousPosition = GMO.mapObject.getCenter();
        },

        /**
         * returns to last saved map position
         * @todo make public
         */
        returnToSavedPosition: function() {
            if (GMO.previousPosition) {
                GMO.mapObject.panTo(GMO.previousPosition); // or setCenter
            }
        },

        /**
         * adds a status and map control DIV
         * @todo make private
         * @todo //to be completed, not currently called
         */
        statusDivControl: function() {
            var el = document.createElement("div");
            var statusControl = new StatusControl(statusControlDiv, map);
            el.setAttribute('id',"statusDivOnMap");
            el.style.backgroundColor = "white";
            el.style.font = "small Arial";
            el.style.border = "1px solid black";
            el.style.padding = "2px";
            el.style.zIndex = "99999";
            el.style.marginRight = "7px";
            google.maps.event.addDomListener(el, "click", function() {
                el.style.display = "none";
            });
            GMO.mapObject.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(statusControlDiv);
            GMO.mapObject.getContainer().appendChild(el);

            return el;
        },

        /**
         * change layer visibility
         * @param String ID of layer.
         * @todo make public
         * @todo add and delete layers
         */

        changeLayerVisibility: function(selectedLayerId){//remove layer
            var newStatus = 1;
            var newStatusName;
            var count = 0;
            if(this.layerInfo[selectedLayerId].show) {
                newStatus = 0;
                GMO.mapObject.closeInfoWindow();
            }
            for (var i = 0; i < this.gmarkers.length; i++) {
                if (this.gmarkers[i].layerId == selectedLayerId) {
                    count++;
                    if(newStatus) {
                        this.gmarkers[i].show();
                        newStatusName = "shown";
                    }
                    else {
                        this.gmarkers[i].hide();
                        newStatusName = "hidden";
                    }
                }
            }
            this.layerInfo[selectedLayerId].show = newStatus;
            this.updateLists();
            this.updateStatus(GMO._t.points+" (" + count + ") " + newStatusName + ".");
        },

        /**
         * adds marker to map
         * @param LatLng point
         * @param String name
         * @param String desc
         * @param String serverID
         * @param URL iconUrl
         * @todo make private
         */
        createMarker: function(point, name, desc, serverId, iconUrl) {// Create Marker
            var currentLayerId = this.layerInfo.length - 1;
            //marker options
            var markerOpts = this.opts.markerOptions || {};
            if(!iconUrl) {
                iconUrl = "http://maps.google.com/mapfiles/marker_yellow"+name[0].toUpperCase()+".png";
            }
            var icon = this.createStandardIcon(iconUrl);
            markerOpts.icon = icon;
            if(markerOpts.title) {
                markerOpts.title = name;
            }
            markerOpts.position = point;
            markerOpts.map = GMO.mapObject;
            // create the marker
            var m = new google.maps.Marker(markerOpts);
            //TO DO: fix following line
            //GMO.updateAddressFormFields(m.getPosition());
            //set other marker variables
            m.layerId = currentLayerId;
            m.markerName = name;
            m.markerDesc = desc.nodeValue;
            m.type = 'marker';
            m.serverId = serverId;
            if(this.opts.updateServerUrlDragend) {
                markerOpts.draggable = true;
                m.draggable = true;
                google.maps.event.addListener(m, "dragend", function() {
                    GMO.updateStatus('<p>'+GMO._t.updating_database+'</p>');
                    var lng = m.getPoint().lng();
                    var lat = m.getPoint().lat();
                    jQuery.get(
                        GMO.opts.updateServerUrlDragend,
                        { x: lng, y: lat, i: m.serverId, a: "move" },
                        function(response){
                            GMO.updateStatus('<p>' + response + '</p>');
                        }
                    );
                });
            }
            else {
                markerOpts.draggable = false;
                m.draggable = false;
            }
            var contentString = GMO.retrieveInfoWindowContent(m);
            var infoWindowSettings = this.opts.infoWindowOptions || {};
            infoWindowSettings.content = contentString;
            var infowindow = new google.maps.InfoWindow(infoWindowSettings);
            //GMO.spiderCode.addListener(
            google.maps.event.addListener(
                m,
                "click",
                function() {
                    infowindow.open(GMO.mapObject, m);
                    GMO.lastMarker = m;
                    GMO.lastInfoWindow = infowindow;
                }
            );
            google.maps.event.addListener(
                m,
                "rightclick",
                function() {
                        m.setMap(null);
                        //infowindow.close();
                        GMO.updateLists();
                        if(GMO.opts.updateServerUrlDragend) {
                            //delete marker from database....
                            var lng = m.getPoint().lng();
                            var lat = m.getPoint().lat();
                            var id = m.serverId;
                            jQuery.get(
                                GMO.opts.updateServerUrlDragend,
                                { x: lng, y: lat, i: id, a: "remove" },
                                function(response){
                                    GMO.updateStatus('<p>' + response + '</p>');
                                }
                            );
                        }

                }
            );
            m.setMap(GMO.mapObject);
            this.gmarkers.push(m);
            this.gmarkers.push(m);
            //GMO.spiderCode.addMarker(m);
            return m;
        },

        /**
         * adds polyline to map
         * @param LatLng point
         * @param String color
         * @param Int width
         * @param Int opacity
         * @param String name
         * @param String desc
        createPolyline: function(points,color,width,opacity,name,desc) {
            var currentLayerId = this.layerInfo.length - 1;
            //marker options
            var markerOpts = this.opts.markerOptions || {};
            //something about the style of the line here
            if(markerOpts.title) {
                markerOpts.title = name;
            }
            // create the marker
            var p = new GPolyline(points,color,width,opacity);
            //set other marker variables
            p.layerId = currentLayerId;
            p.markerName = name;
            p.markerDesc = desc;
            p.type = 'polyline';
            google.maps.event.addListener(p, "click", function() {
                GMO.lastMarker = p;
                GMO.openPolyInfoTabs(p, false);
            });
            GMO.mapObject.addOverlay(p);
            this.gmarkers.push(p);
            return p;
        },

        /**
         * adds polyl to map
         * @param Array points
         * @param String color
         * @param Int width
         * @param Int opacity
         * @param String fillcolor
         * @param Int fillopacity
         * @param google.maps.LatLngBounds pbounds
         * @param String name
         * @param String desc
         * @todo delete?
        createPolygon: function(points,color,width,opacity,fillcolor,fillopacity,pbounds, name, desc) {
            var currentLayerId = this.layerInfo.length - 1;
            //marker options
            var markerOpts = this.opts.markerOptions || {};
            //something about the style of the line here
            if(markerOpts.title) {
                markerOpts.title = name;
            }
            // create the marker
            var p = new GPolygon(points,color,width,opacity,fillcolor,fillopacity)
            //set other marker variables
            p.layerId = currentLayerId;
            p.markerName = name;
            p.markerDesc = desc;
            p.type = 'polygon';
            google.maps.event.addListener(p, "click", function() {
                    GMO.lastMarker = p;
                    GMO.openPolyInfoTabs(p, pbounds);
            });
            GMO.mapObject.addOverlay(p);
            this.gmarkers.push(p);
            return p;
        },
        */

        /**
         * retrieves html content to display above marker inside and infowindow
         * @param google.maps.Map Map
         * @todo ???
         */
        retrieveInfoWindowContent: function(m) {
            var hiddenMarkerArray = GMO.checkForHiddenMarkers(m);
            var name = m.markerName;
            var desc = m.markerDesc;
            var point = m.getPosition();
            var options = this.opts.infoWindowOptions || {};
            //add delete category
            var infoTabExtraLinksArray = new Array();
            var obscuringLinks = '';
            var pointCount = this.layerInfo[m.layerId].pointCount;
            if(pointCount > 1) {
                //infoTabExtraLinksArray.push(', <a href="javascript:void(0)" onclick="google.maps.event.trigger(GMO.lastMarker,\'hideGroup\');">Hide Group ('+ pointCount +' points)</a>');
                google.maps.event.addListener(m, "hideGroup", function() {
                        GMO.changeLayerVisibility(m.layerId);
                        infowindow.close();
                });
            }
            if(this.opts.addAntipodean) {
                infoTabExtraLinksArray.push(
                    '<a href="javascript:void(0)" onclick="google.maps.event.trigger(GMO.lastMarker,\'clickAntipodean\');">'+GMO._t.drill_a_hole+'</a>'
                );
                google.maps.event.addListener(m, "clickAntipodean", function() {
                    GMO.mapObject.setZoom(6);
                    var position = m.getPosition();
                    var pointLngLat = GMO.antipodeanPointer(position.lng(), position.lat());
                    var longitude = GMO.checkLongitude(point.lng());
                    var latitude = GMO.checkLatitude(point.lat());
                    var nameString = GMO._t.antipodean_of + " " + m.markerName;
                    var description = GMO._t.found_opposite;
                    var zoom = 3;
                    var xmlSheet = GMO.createPointXml(nameString, pointLngLat, description, longitude, latitude, zoom)
                    GMO.processXml(xmlSheet);
                });
            }
            if(m.draggable) {
                infoTabExtraLinksArray.push('<b>'+GMO._t.drag_instructions+'</b>');
            }
            if(hiddenMarkerArray.length) {
                obscuringLinks += ' <p><span class="partialObscuring">'+GMO._t.partial_obscuring;
                for(var i = 0; i < hiddenMarkerArray.length; i++) {
                    var markerId = hiddenMarkerArray[i];
                    obscuringLinks += ' <a href="javascript:void(0)" onclick="'+this.variableName+'.showMarkerFromList('+markerId+');">' + this.gmarkers[markerId].markerName + '</a>';
                    if(i < (hiddenMarkerArray.length - 2)) {
                        obscuringLinks += ", ";
                    }
                    else if(i == (hiddenMarkerArray.length - 2 )){
                        obscuringLinks += GMO._t.and_with_spaces;
                    }
                }
                obscuringLinks += "</span></p>";
            }
            //basic html
            var html = '<div id="infoWindowTab1" class="infoWindowTab"><h1>'+name+'</h1>' + obscuringLinks + '<div>'+desc+'</div>';
            if(this.opts.addZoomInButton) {
                infoTabExtraLinksArray.push(
                    '<a href="javascript:void(0)" onclick="google.maps.event.trigger(GMO.lastMarker,\'clickZoomIn\')">zoom in</a>'
                );
            }
            if(this.opts.addDeleteMarkerButton) {
                infoTabExtraLinksArray.push(
                    '<a href="javascript:void(0)" onclick="google.maps.event.trigger(GMO.lastMarker,\'clickRemoveMe\')">'+this.opts.addDeleteMarkerButton+'</a>'
                );
            }
            if(infoTabExtraLinksArray.length) {
                html += '<p class="infoTabExtraLinks">'+infoTabExtraLinksArray.join(", ")+'.</p>';
            }
            google.maps.event.addListener(m, "clickZoomIn", function() {
                var maxZoomService = new google.maps.MaxZoomService();
                maxZoomService.getMaxZoomAtLatLng(m.position, function(response) {
                    if (response.status != google.maps.MaxZoomStatus.OK) {
                        alert(GMO._t.error_in_zoom);
                        return;
                    }
                    else {
                        GMO.mapObject.setZoom(response.zoom);
                        GMO.mapObject.setCenter(m.position);
                    }
                });
            });
            google.maps.event.addListener(m, "clickRemoveMe", function() {
                m.setMap(null);
                //google.maps.event.addlistener(marker, "rightclick");
            });
            //var tabsHtml = [new GInfoWindowTab("info", html)];
            //directions and address finder
            if(this.opts.addDirections) {
                //var lonLatString = point.toUrlValue();
                var findDirections = '';
                if(this.opts.addDirections) {
                    var currentFrom = this.currentFromLocation();
                    findDirections = ''
                        + '<p class="infoTabFromOption">'
                        + ' <b>' + GMO._t.from + ':</b>'
                        + '</p>'
                        + '<p id="fromHereLink">'
                        + ' <a href="javascript:void(0)" onclick="google.maps.event.trigger(GMO.lastMarker,\'clickFromHere\')">'+ GMO._t.select_this_point + '</a>: '
                        + ' ' + this.currentFromLocation()
                        + '</p>'
                        + '<p class="infoTabToOption">'
                        + ' <b>' + GMO._t.to + ':</b>'
                        + '</p>'
                        + '<p id="toHereLink">'
                        + ' <a href="javascript:void(0)" onclick="google.maps.event.trigger(GMO.lastMarker,\'clickToHere\')">' + GMO._t.select_this_point + '</a>:'
                        + ' ' + this.currentToLocation()
                        + '</p>'
                        + '<p class="infoTabAlternative">';
                    if(this.routeShown) {
                        findDirections += ''
                            + ' <b>' + GMO._t.do_next + ':</b>'
                            + '</p>'
                            + '<p>'
                            + ' <a href="javascript:google.maps.event.trigger(GMO.lastMarker,\'clickClearRoute\')" id="clearRouteLink">'+GMO._t.clear_last_route+'</a> ';
                    }
                    else if((this.floatFrom || this.floatTo) || (this.to || this.from)) {
                        findDirections += ''
                            + '<b>'+GMO._t.do_next+':</b></p>'
                            + '<p><a href="javascript:void(0)" class="submitButton" onclick="google.maps.event.trigger(GMO.lastMarker,\'clickFindRoute\')" id="calculateLinkRoute">'+GMO._t.calculate_route+'</a>';
                    }
                    else if(this.layerInfo[m.layerId].pointCount > 1 && this.layerInfo[m.layerId].pointCount < 20) {
                        findDirections += ''
                            + '<span class="alternatively">'+GMO._t.alernatively+'</p>'
                            + '<p><a href="javascript:void(0)" class="submitButton" onclick="google.maps.event.trigger(GMO.lastMarker,\'joinTheDots\')" id="calculateLinkRoute">'+GMO._t.create_routes_using_current_points+'</a>';
                    }
                    findDirections += ''
                        + '</p>'
                    //join the dots
                    google.maps.event.addListener(m, "joinTheDots", function() {
                        GMO.createRouteFromLayer(m.layerId);
                        //infowindow.close();
                    });
                    //current start point
                    google.maps.event.addListener(m, "clickFromHere", function() {
                        GMO.from = name;
                        GMO.floatFrom = new google.maps.LatLng(point.ob, point.pb);
                        document.getElementById("fromHereLink").innerHTML = GMO._t.this_point;
                    });
                    //current end point
                    google.maps.event.addListener(m, "clickToHere", function() {
                        document.getElementById("toHereLink").innerHTML = GMO._t.this_point;
                        GMO.to = name;
                        GMO.floatTo = new google.maps.LatLng(point.ob, point.pb);
                    });
                    //add route
                    google.maps.event.addListener(m, "clickFindRoute", function() {
                        GMO.showRoute();
                        GMO.mapObject.closeInfoWindow();
                    });
                    //clearRoute
                    google.maps.event.addListener(m, "clickClearRoute", function() {
                        GMO.clearRouteAll();
                    });
                }
                html = html + '<div id="infoWindowTab2" class="infoWindowTab">' + findDirections + '</div>';
                //tabsHtml.push(new GInfoWindowTab("directions", '<div id="infoWindowTab2" class="infoWindowTab">' + findDirections + '</div>' ));
            }

            if(this.opts.addCurrentAddressFinder) {
                html = html + '<div id="infoWindowTab3" class="infoWindowTab">'
                    + '<a href="javascript:void(0)" onclick="google.maps.event.trigger(GMO.lastMarker,\'findAddressFromLngLat\')">'+GMO._t.find_address+'</a>'
                    + '</div>';
                //tabsHtml.push(new GInfoWindowTab("address", '<div id="infoWindowTab3" class="infoWindowTab"><a href="javascript:void(0)" onclick="google.maps.event.trigger(GMO.lastMarker,\'findAddressFromLngLat\')">find address</a></div>'));
                google.maps.event.addListener(m, "findAddressFromLngLat",
                    function() {
                        GMO.geocoder = new google.maps.Geocoder();
                        return;
                        GMO.geocoder.getLocations(
                            m.position,
                            function(response) {
                                var html = '<p>'+GMO._t.address_not_found+'</p>';
                                if (!response || response.Status.code != 200) {
                                    var html = '<p>'+GMO._t.address_not_retrieved+'</p>';
                                }
                                else {
                                    place = response.Placemark[0];
                                    if(place) {
                                        var html = place.address;
                                    }
                                }
                                html += '<hr /><h2>'+GMO._t.address_not_accurate+'.</h2>';
                                jQuery("#infoWindowTab3").html(html);
                            }
                        );
                    }
                );
            }
            return html;
        },

        /**
         * opens an info window for a a polyline
         * @param google.maps.Map Map
         * @param google.maps.LatLngBounds pbounds
         * @todo delete along with ployline function???
         */
        openPolyInfoTabs: function( m, pbounds) {
            //if pbounds then it must be a polygon rather than a polyline
            var name = m.markerName;
            var desc = m.markerDesc;
            var options = this.opts.infoWindowOptions || {};
            //add delete category
            var hideGroupLink = '';
            var pointCount = this.layerInfo[m.layerId].pointCount;
            if(pointCount > 1) {
                //hideGroupLink += ', <a href="javascript:void(0)" onclick="google.maps.event.trigger(GMO.lastMarker,\'hideGroup\');">Hide Group ('+ pointCount +' points)</a>';
                google.maps.event.addListener(
                    m,
                    "hideGroup",
                    function() {
                        GMO.changeLayerVisibility(m.layerId);
                        GMO.mapObject.closeInfoWindow();
                    }
                );
            }
            //basic html
            var html = '<div id="infoWindowTab1" class="infoWindowTab">'
                + '<div>'+desc+'</div>'
                + '<p class="infoTabBasicLinks">'
                + '<a href="javascript:void(0)" onclick="'+this.variableName+'.mapObject.closeInfoWindow();">'+GMO._t.close+'</a>'
                + ', <a href="javascript:void(0)" onclick="google.maps.event.trigger(GMO.lastMarker,\'clickHideMe\')">'+GMO._t.remove+'</a>'
                + hideGroupLink
                + '.</p>'
            google.maps.event.addListener(m, "clickHideMe", function() {
                GMO.mapObject.closeInfoWindow();
                m.hide();
                var currentLayerId = m.layerId
                GMO.updateLists();
            });
            //var tabsHtml = [new google.maps.InfoWindowTab("info", html)];//[new GInfoWindowTab("info", html)];
            //directions and address finder
            if(pbounds) {
                GMO.mapObject.openInfoWindowHtml(pbounds.getCenter(),html,options);
            }
            else {
                GMO.mapObject.openInfoWindowHtml(m.getVertex(Math.floor(m.getVertexCount()/2)),html,options);
            }
        },

        /**
         * creates a marker icon given a url to the image/icon
         * @param URL iconUrl
         * @todo fix this.updateStatus(iconUrl),
         */
        createStandardIcon: function(iconUrl) {
            // create icon
            var icon = {
                //this.updateStatus(iconUrl),
                url : iconUrl,
                size: new google.maps.Size(this.opts.iconWidth,this.opts.iconHeight),
                anchor: new google.maps.Point(Math.round(this.opts.iconWidth/2),this.opts.iconHeight)
            };
            return icon;
        },




        /* process XML sheets */
        /**
         * creates a XML point and attempts to add it to the database
         * @param String name
         * @param google.maps.LatLng pointLngLat
         *@param String description
         * @param Int latitude
         * @param Int longitude
         * @param Int zoom
         * @param String info
         * @return XML
         * @todo test if(GMO.opts.updateServerUrlDragend),
         */
        createPointXml: function(name, pointLngLat, description, longitude, latitude, zoom, info) {//creates XML for one point only

            if(!longitude) {longitude = pointLngLat.x;}
            if(!latitude) {latitude = pointLngLat.y;}
            if(!zoom) {zoom = 5;}

            var serverId = "Marker_manuallyAdded" + GMO.layerInfo.length;
            var string = '<?xml version="1.0" encoding="UTF-8"?>'
            + '<kml xmlns="http://earth.google.com/kml/2.1"><Document><mapinfo>'
            + '<title>' + name + '</title>'
            + '<longitude>' + longitude + '</longitude>'
            + '<latitude>' + latitude + '</latitude>'
            + '<zoom>' + zoom + '</zoom>'
            + '<pointcount>1</pointcount>'
            + '<info>' + info + '</info>'
            + '</mapinfo><Placemark>'
            + ' <id>' + serverId + '</id>'
            + ' <name>' + name + '</name>'
            + ' <Point><coordinates>' + pointLngLat.lng() + "," + pointLngLat.lat() + '</coordinates></Point>'
            + ' <description><![CDATA[ <p>' + description +'</p>]]></description>'
            + '</Placemark>'
            + '</Document>'
            +'</kml>';
            if(GMO.opts.updateServerUrlDragend) {
                var pointLngLatArray = pointLngLat.split(',');
                var lng = parseFloat(pointLngLatArray[0]);
                var lat = parseFloat(pointLngLatArray[1]);
                jQuery.get(
                    GMO.opts.updateServerUrlDragend,
                    { x: lng, y: lat, i: 0, a: "add" },
                    function(data){
                        if(parseInt(data) > 0) {
                            GMO.updateStatus('<p>'+GMO._t.added_to_db+'</p>');
                            GMO.lastMarker.serverId = data;
                        }
                        else {
                            GMO.updateStatus('<p>'+GMO._t.added_to_db_error+'</p>');
                        }
                    }
                );
            }
            var xmlSheet = xmlParse(string);
            return xmlSheet;
        },

        /**
         * downloads the XML file containing map coordinates and then processes
         * @param URL url
         * @todo ???
         */
        downloadXml: function(url) {
            var previouslyLoaded;
            console.debug(this);
            if(previouslyLoaded = this.layerInfo.inSubArray("url", url)) {
                this.updateStatus(GMO._t.loaded);
                var realLayerId = previouslyLoaded - 1;
                if(!this.layerInfo[realLayerId].show) {
                    this.changeLayerVisibility(previouslyLoaded-1);
                }
            }
            else {
                this.updateStatus(GMO._t.loading_map);
                this.latestUrl = url;
                downloadUrl(
                    url,
                    function(doc) {
                        GMO.processXml(doc);
                    }
                );

            }
        },

        /**
         * processes the downloaded XML file into the points required to create a marker for the location
         * @param DOM doc
         * @todo ???
         */
        processXml: function(doc) {
            this.bounds = new google.maps.LatLngBounds();
            if(doc.getElementsByTagName("pointcount").length > 0) {
                var pointCount = parseInt(doc.getElementsByTagName("pointcount")[0].childNodes[0].nodeValue);
                this.tooManyPointsWarning(pointCount + 1);
                if(pointCount > 0) {

                    var currentLayerId = this.layerInfo.length;
                    var groupInfo = {};
                    var iconUrlCollection = [];
                    groupInfo.show = 1;
                    groupInfo.url = this.latestUrl;
                    this.latestUrl = '';
                    groupInfo.pointCount = pointCount;
                    //parse basics:
                    var mapInfo = doc.getElementsByTagName("mapinfo")[0];
                    groupInfo.title =  mapInfo.getElementsByTagName("title")[0].firstChild.nodeValue;
                    if(mapInfo.getElementsByTagName("info")[0].firstChild !== null) {
                        groupInfo.info =  mapInfo.getElementsByTagName("info")[0].firstChild.nodeValue;
                    }
                    groupInfo.a = mapInfo.getElementsByTagName("latitude")[0].firstChild.nodeValue;
                    groupInfo.o = mapInfo.getElementsByTagName("longitude")[0].firstChild.nodeValue;
                    groupInfo.z = mapInfo.getElementsByTagName("zoom")[0].firstChild.nodeValue;
                    //add icon here? createStandardIcon
                    var currentIconId = currentLayerId + 1;
                    var layerIconCount = 0;
                    if(currentIconId > this.opts.iconMaxCount) {
                        currentIconId = 1;
                    }
                    if(currentIconId && (this.opts.iconFolder || this.opts.defaultIconUrl)) {
                        var iconUrl = this.opts.defaultIconUrl || this.opts.iconFolder + "i" + currentIconId + "." + this.opts.iconExtension;
                    }
                    //add layer information ... IMPORTANT MUST BE BEFORE MARKER LOOP
                    //groupInfo.iconUrl = iconUrlCollection;
                    this.layerInfo.push (groupInfo);
                    // Read through the Placemarks
                    var placemarks = doc.documentElement.getElementsByTagName("Placemark");
                    for (var i = 0; i < placemarks.length; i++) {
                        var serverId = placemarks[i].getElementsByTagName("id")[0].childNodes[0];
                        var name = placemarks[i].getElementsByTagName("name")[0].childNodes[0].nodeValue;
                        var desc = placemarks[i].getElementsByTagName("description")[0].childNodes[0];
                        var styleLocationId = null
                        if (placemarks[i].getElementsByTagName("styleUrl").length > 0) {
                            styleLocationId = placemarks[i].getElementsByTagName("styleUrl")[0];
                            styleLocationId = styleLocationId.substring(1, styleLocationId.length);
                        }
                        var newIconURL = ""; //use standard one => iconUrl;
                        if(styleLocationId) {
                            //<Style id="randomColorIcon"><IconStyle><Icon>URL here
                            var IconStyleDoc = xmlDoc.getElementsByTagName("Style");
                            for(var j=0;j<IconStyleDoc.length;j++){
                                if(IconStyleDoc[j].getAttribute("id")) {
                                    if(IconStyleDoc[j].getAttribute("id") == styleLocationId){
                                        layerIconCount++;
                                        newIconURL = IconStyleDoc[j].getElementsByTagName("Icon")[0].childNodes[0];
                                    }
                                }
                            }
                        }
                        // Attempt to preload images
                        var coords = placemarks[i].getElementsByTagName("coordinates")[0].childNodes[0].nodeValue;
                        coords = coords.replace(/\s+/g," "); // tidy the whitespace
                        coords = coords.replace(/^ /,"");    // remove possible leading whitespace
                        coords = coords.replace(/, /,",");   // tidy the commas
                        var path = coords.split(" ");
                        // Is this a polyline/polygon?
                        if (path.length > 1) {
                            // Build the list of points
                            var points = [];
                            var pbounds = new google.maps.LatLngBounds();
                            for (var p=0; p<path.length-1; p++) {
                                var bits = path[p].split(",");
                                var point = new google.maps.LatLng(parseFloat(bits[1]),parseFloat(bits[0]));
                                points.push(point);
                                this.bounds.extend(point);
                                pbounds.extend(point);
                            }
                            if(this.opts.polyIcon) {
                                newIconURL = this.opts.polyIcon;
                            }
                            var width = this.opts.lineWidth;
                            var color = this.opts.lineColour;
                            var opacity = this.opts.lineOpacity;
                            var linestring = placemarks[i].getElementsByTagName("LineString");
                            var polygons = placemarks[i].getElementsByTagName("Polygon");
                            if (linestring.length) {
                                var p = this.createPolyline(points,color,width,opacity,name,desc);
                            }
                            else if (polygons.length) {
                                var fillopacity = this.opts.fillOpacity;
                                var fillcolor = this.opts.fillColour;
                                var p = this.createPolygon(points,color,width,opacity,fillcolor,fillopacity,pbounds, name, desc);
                            }
                            else {
                                alert(GMO._t.error_in_loading);
                            }
                        }
                        else {
                        //it must be a marker
                            var bits = path[0].split(",");
                            var point = new google.maps.LatLng(parseFloat(bits[1]),parseFloat(bits[0]));
                            this.bounds.extend(point);
                        // create marker
                            this.updateStatus(GMO._t.processing + " " + i + " " + GMO._t.of + " " + pointCount + ".");
                            var m = this.createMarker(point, name, desc, serverId, newIconURL);
                        }
                        if(!iconUrlCollection.inArray(newIconURL)) {
                            iconUrlCollection.push(newIconURL);
                        }
                    }
                    //add icons
                    var currentLayerId = this.layerInfo.length - 1;
                    this.layerInfo[currentLayerId].iconUrl = iconUrlCollection;
                    // Shall we zoom to the bounds?
                    if (pointCount > 1) {
                        GMO.mapObject.fitBounds(this.bounds);
                    }
                    else {
                        GMO.mapObject.panTo(point);
                    }
                    this.updateLists();
                    //this.zoomTo(groupInfo.a, groupInfo.o, groupInfo.z);
                    //if(!GMO.mapObject.getInfoWindow().getVisible()) {
                    if(typeof m !== "undefined" &&  ! m.getVisible) {
                        //GMO.mapObject.closeInfoWindow();
                        m.infowindow.close();
                    }
                    if(pointCount > 1) {
                        this.updateStatus(pointCount + " " + GMO._t.locations_added + ".");
                    }
                    else {
                        window.setTimeout(
                            function () {
                                google.maps.event.trigger(GMO.gmarkers[GMO.gmarkers.length -1], "click");
                                //GMO.mapObject.panDirection(0, 1);
                                GMO.mapObject.panBy(0, 1);
                            }
                            , 300
                        );
                        this.updateStatus(GMO._t.one_location_loaded);
                    }
                }
                else {
                    var title =  doc.getElementsByTagName("title")[0];
                    if(this.opts.titleId) {
                        if(el = document.getElementById(this.opts.titleId)) {
                            el.innerHTML = title;
                        }
                    }
                    if(this.opts.changePageTitle && document.title) {
                        document.title = title;
                    }
                }
            }
        },

        /**
         * if the are more than points to add to the map user receives a warning it could take a while
         * @param Int pointCount
         * @todo ???
         */
        tooManyPointsWarning: function(pointCount) {
            if(pointCount > 100) {
                this.updateStatus(pointCount + " " + GMO._t.large_number_of_items);
            }
            else if(pointCount == 0) {
                this.updateStatus(GMO._t.no_location_could_be_found);
            }
        },




        /* update lists */

        /**
         * updates lists
         * @todo test???
         */
        updateLists: function() {
            this.updateStatus(GMO._t.updating_lists);
            if(this.opts.sideBarId || this.opts.dropBoxId || this.opts.layerListId) {
                var a = [];
                var sideBarArray = [];
                for(var i = 0; i < this.layerInfo.length; i++) {
                    this.layerInfo[i].show = 0;
                }
                for (var i = 0; i < this.gmarkers.length; i++) {
                    if (this.gmarkers[i].getVisible()) {
                        this.layerInfo[this.gmarkers[i].layerId].show = 1;
                        if(!a.inArray(this.gmarkers[i].markerName)) {
                            sideBarArray.push(this.gmarkers[i].markerName + "$$$" + i);
                        }
                        a.push(this.gmarkers[i].markerName);
                    }
                }
                sideBarArray.sort();
            }
            if(this.gmarkers.length > 1) {
                var displayItemList="block";
            }
            else {
                var displayItemList="none";
            }
            if(this.layerInfo.length > 1) {
                var displayLayerList="block";
            }
            else {
                var displayLayerList="none";
            }
            if(this.opts.sideBarId) {
                jQuery("#"+this.opts.sideBarId).css("display", displayItemList);
                this.createSideBar(sideBarArray);
            }
            if(this.opts.dropBoxId) {
                jQuery("#"+this.opts.dropBoxId).css("display", displayItemList);
                this.createDropDown(sideBarArray);
            }
            if(this.opts.titleId) {
                this.updateTitles();
            }
            if(this.opts.layerListId) {
                jQuery("#"+this.opts.layerListId).css("display", displayLayerList);
                this.updateLayerList();
            }
            this.updateStatus(GMO._t.map_ready);
        },

        /**
         * creates and displays a sidebar
         * @param Array sideBarArray
         * @todo ???
         */
        createSideBar: function(sideBarArray) {
            if(this.opts.sideBarId) {
                var el;
                var layerName = '';
                var sideBarElements = '';
                if(el = document.getElementById(this.opts.sideBarId)) {
                    if(sideBarArray.length > 1) {
                        var html = '<ul id="' + this.opts.sideBarId + 'list">';
                        for (var j = 0; j < sideBarArray.length; j++) {
                            sideBarElements = sideBarArray[j].split("$$$", 2);
                            var i = sideBarElements[1];
                            layerName = this.gmarkers[i].layerId;
                            var serverID = String(this.gmarkers[i].serverId);
                            var isManuallyAdded = serverID.indexOf( "manuallyAdded", 0 ); // returns -1
                            if(isManuallyAdded == -1) {
                                html += ''
                                    + '<li class="forLayer'+layerName+' icon'+i+'">'
                                    +' <a href="'+ this.currentPageURL + '#GoogleMapDiv" onclick="GMO.showMarkerFromList(' + i + '); return false;">' + this.gmarkers[i].markerName + '</a>'
                                    +' <div class="infowindowDetails">'  + this.gmarkers[i].markerDesc + '</div>'
                                    +'</li>';
                            }
                            else {
                                html += ''
                                    +'<li class="forLayer'+layerName+'">' + GMO._t.you_added + ':'
                                    +' <a href="'+ this.currentPageURL + '#GoogleMapDiv" onclick="GMO.showMarkerFromList(' + i + '); return false;">' + this.gmarkers[i].markerName + '</a>'
                                    +'</li>';
                            }
                        }
                        html += '</ul>';
                        el.innerHTML = html;
                        if(typeof AdjustHeightsForGoogleMap !== "undefined") {
                            AdjustHeightsForGoogleMap.boxesSelector = '#' + this.opts.sideBarId + 'list > li';
                            jQuery(window).resize(
                                function() {
                                    AdjustHeightsForGoogleMap.adjustBoxes()();
                                }
                            );
                            AdjustHeightsForGoogleMap.adjustBoxes()();
                        }
                    }
                }
                else {
                    console.debug("you defined the dropbox like this " + this.opts.sideBarId + ", but it does not exist");
                }
            }
        },

        /**
         * creates and displays a drop down list of selectable locations
         * @param Array sideBarArray
         * @todo ???
         */
        createDropDown: function(sideBarArray) {
            if(this.opts.dropBoxId && sideBarArray.length > 1) {
                var el;
                if(el = document.getElementById(this.opts.dropBoxId)) {
                    var html = '<select onchange="GMO.showMarkerFromList(this.value);">'
                        + '<option selected="selected">' + GMO._t.select_a_location + ' </option>';
                    for (var j = 0; j < sideBarArray.length; j++) {
                        var sideBarElements = sideBarArray[j].split("$$$", 2);
                        i = sideBarElements[1];
                        html += '<option value="' + i + '">' + this.gmarkers[i].markerName +'</option>';
                    }
                    html + "</select>";
                    el.innerHTML = html;
                }
                else {
                    console.debug("you defined the dropbox like this "+this.opts.dropBoxId+", but it does not exist");
                }
            }
        },

        /**
         * updates layer list
         * @todo ???
         */
        updateLayerList: function() {
            if(this.opts.layerListId) {
                var el = null;
                if(el = document.getElementById(this.opts.layerListId)) {
                    var html = '<ul>';
                    var className = '';
                    var style = '';
                    var linkText = '';
                    for (var i = 0; i < this.layerInfo.length; i++) {
                        if (this.layerInfo[i].show == 1) {
                            linkText = "hide";
                            className = "layerListShown";
                            style = '';
                        }
                        else {
                            linkText = "show";
                            className = "layerListHidden";
                            if(this.opts.hiddenLayersRemovedFromList) {
                                style = ' style="display: none;"';
                            }
                            else {
                                style = ' style="opacity:.30; filter: alpha(opacity=30); -moz-opacity: 0.30;"';
                            }
                        }
                        html += ''
                            + '<li class="'+className+'"'+style+'>';
                        for(var j=0; j < this.layerInfo[i].iconUrl.length; j++) {
                            html += ''
                                + '<img src="'+ this.layerInfo[i].iconUrl[j] + '" alt="icon ' + j + ' for '+ this.layerInfo[i].title + '" class="iconBulletPoint" width="' + this.opts.iconWidth + '" height="' + this.opts.iconHeight + '" /> ';
                        }
                        html += ''
                            + this.layerInfo[i].title
                            + ' <a href="javascript:void(0)" onclick="GMO.changeLayerVisibility('+i+')">' + linkText + '</a>';
                            if(!this.opts.hiddenLayersRemovedFromList) {
                                + ' - <a href="javascript:void(0)" onclick="GMO.deleteLayer('+i+')">' + GMO._t.remove + '</a>';
                            }
                        if(this.opts.addKmlLink && this.layerInfo[i].url) {
                            html += ''
                                + ' (<a href="' + this.layerInfo[i].url + '&kml=1">' + GMO._t.kml + '</a>)</li>';
                        }
                    }
                    html += "<ul>";
                    el.innerHTML = html;
                }
                else {
                    console.debug("you defined the layerlist like this "+this.opts.layerListId+", but it does not exist");
                }
            }
        },

        /**
         * updates titles
         * @todo ???
         */
        updateTitles: function() {
            var title = this.opts.defaultTitle;
            for (var i = (this.layerInfo.length - 1); i > -1; i--) {
                if(this.layerInfo[i].show) {
                    title = this.layerInfo[i].title;
                    i = -99;
                }
            }
            var el = null;
            if(this.opts.titleId) {
                if(el = document.getElementById(this.opts.titleId)) {
                    el.innerHTML = title;
                }
            }
            if(this.opts.changePageTitle && document.title) {
                document.title = title;
            }
        },

        /**
         * updates status
         * @todo test???
         */
        updateStatus: function(html, add) {
            var el = null;
            var hideAction = "";
            //depreciated...
            if(this.opts.addAddressFinder || add == "find") {
                if(html) {
                    html += "<hr />";
                }
                html += this.findAddressForm() + "";
            }
            if(add == "help") {
                if(html) {
                    html += "<hr />";
                }
                html += "<hr />" + this.helpHtml() + "";
            }
            else {
                //do nothing
            }
            if(document.getElementById(this.mapDivName)) {
                if(this.mapIsZoomed) {
                    var zoomLinkLabel = 'reduce map size';
                    var hideAction = ""
                }
                else {
                    var zoomLinkLabel = 'full-screen';
                    var hideAction = '<span>|</span> <a href="javascript:void(0)" onclick="GMO.hideStatus();">' + GMO._t.hide + '</a> ';
                }
            }
            var fullHtml = '' + '<p class="helpLink" style="text-align: right; font-size: 10px; width: auto; float: right;">';
            // depreciated
            if(this.opts.addAddressFinder) {
                fullHtml += ' <a href="javascript:void(0)" onclick="GMO.updateStatus(\'\', \'find\');">' + GMO._t.find_address + '</a> <span>|</span>'
            }
            fullHtml += ' <a href="javascript:void(0)" onclick="GMO.updateStatus(\'\', \'help\');"> ' + GMO._t.show_help + ' </a> <span>|</span>'
            + ' <a href="javascript:void(0)" onclick="GMO.enlargeMap();" id="mapZoomLinkLabel">' + zoomLinkLabel + '</a> '
            + hideAction
            + '</p>'+ html;
            if(this.opts.statusDivId) {
                if(el = document.getElementById(this.opts.statusDivId)) {
                    el.innerHTML = fullHtml;
                    el.style.display = "block";
                }
            }
        },

        /**
         * hides status
         * @todo test???
         */
        hideStatus: function() {
            if(this.opts.statusDivId) {
                if(el = document.getElementById(this.opts.statusDivId)  && !this.mapIsZoomed) {
                    el = document.getElementById(this.opts.statusDivId);
                    el.style.display = "none";
                }
                else if(this.mapIsZoomed) {
                    alert(GMO._t.can_not_hide_bar_in_full_screen_mode);
                }
            }
        },



        /* special searches: find address */
        /**
         * searchs for address and displays on screen or a reason why it was not successful
         * @param String address
         */
        showAddress: function(address) {
            var countryCode = "";
            if(this.opts.defaultCountryCode) {
                countryCode = this.opts.defaultCountryCode;
            }
            GMO.geocoder = new google.maps.Geocoder();
            if (GMO.geocoder) {
                this.updateStatus(GMO._t.search_for_address);
                this.mapAddress = address;
                GMO.geocoder.geocode( { 'address': address, 'region': countryCode}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        console.debug(results);
                        var result = results[0];
                        GMO.addAddressToMap(result);
                        GMO.mapObject.setCenter(result.geometry.location);
                        /*
                        var marker = GMO.addPoint(
                            result.geometry.location, //latLng
                            result.formatted_address, //address string
                            GMO._t.added_position //description
                        );
                        */
                    }
                    else {
                        alert("Geocode was not successful for the following reason: " + status);
                    }
                });
                return false;
            }
            else {
                this.updateStatus(GMO._t.address_finder_not_loaded);
            }
        },

        /**
         * if address can be found create a point and add a layer to the map
         * @param HTTP Response response
         * @todo test???
         */
        addAddressToMap: function(firstResponseObject) {
            place = firstResponseObject.geometry.location;
            var pointLngLat = new google.maps.LatLng(place.lat(),  place.lng());
            var nameString = GMO._t.address_search + ": " + GMO.mapAddress;
            var description = place.formatted_address;
            var xmlSheet = GMO.createPointXml(nameString, pointLngLat, description);
            GMO.processXml(xmlSheet);
            GMO.updateStatus(GMO._t.address_found + ": " + description);
            var serverURL = GMO.opts.updateServerUrlAddressSearchPoint + encodeURIComponent(description) + "/" + place.lng() + "/" + place.lat() + "/";
            GMO.addLayer(serverURL);
            return true;
        },

        /**
         * update address form fields
         * @param google.maps.LatLng latLng
         * @todo test???
         */
        updateAddressFormFields: function(latLng) {
            if(GMO.opts.latFormFieldId) {
                if(el = document.getElementById(GMO.opts.latFormFieldId)) {
                    document.getElementById(GMO.opts.latFormFieldId).value = latLng.lat();
                    document.getElementById(GMO.opts.lngFormFieldId).value = latLng.lng();
                }
            }
        },





        /* special searches: find route */
        /**
         * finds route between a start and end location
         * @todo not currently working, fix!!
         */
        showRoute: function() {
            if(!this.floatFrom) {
                this.updateStatus(GMO._t.no_valid_start);
            }
            else if(!this.floatTo) {
                this.updateStatus(GMO._t.no_valid_end);
            }
            else {
                GMO.routeShown = true;
                this.updateStatus(GMO._t.searching_for_route);
                var fromTo = this.floatFrom + " to " + this.floatTo;
                var directionOptions = { locale: this.opts.localeForResults, getSteps:true}
                GMO.directions.load(fromTo, directionOptions);
            }
        },

        /**
         * finds route between all points displayed in a layer
         * @param Int layerId
         * @todo not currently working, fix!!
         */
        createRouteFromLayer: function(layerId) {
            var wayPointArray = Array();
            var k = 0;
            var doAllMarkers = false;
            var pointCount = this.layerInfo[layerId].pointCount;
            if(pointCount < 2) {
                var doAllMarkers = true;
            }
            for (var i = 0; i < this.gmarkers.length; i++) {
                if(this.gmarkers[i].layerId == layerId || doAllMarkers) {
                    wayPointArray[k] = (k+1) + ": " + this.gmarkers[i].markerName + "@" + this.gmarkers[i].getPosition().lat() + "," + this.gmarkers[i].getPosition().lng();
                    k++;
                }
            }
            if(wayPointArray.length) {
                GMO.routeShown = true;
                this.updateStatus(GMO._t.searching_for_route);
                var request = {
                    origin:GMO.floatFrom,
                    destination:GMO.floatTo,
                    travelMode: google.maps.TravelMode.TRANSIT
                    };
                GMO.directions.route(request, function(result, status) {
                    if (status == google.maps.DirectionsStatus.OK) {
                        GMO.directionsDisplay.setDirections(result);
                    }
                });
            }
            else {
                this.updateStatus(GMO._t.no_route_could_be_found);
            }
        },

        /**
         * returns a string of from/start location
         * @todo test
         */
        currentFromLocation: function() {
            var string = '';
            if(this.from ) {
                string =  '<span id="currentlySetToFrom">' + this.currentlySetToString + this.from + '</span>';
            }
            else if(this.floatFrom) {
                string = '<span id="currentlySetToTo">' + this.currentlySetToString + this.floatFrom + '</span>';
            }
            return string;
        },

        /**
         * returns a string of to/end location
         * @todo test
         */
        currentToLocation: function() {
            var string = '';
            if(this.to ) {
                string =  this.currentlySetToString + this.to;
            }
            else if(this.floatTo) {
                string = this.currentlySetToString + this.floatTo;
            }
            return string;
        },

        /**
         * clears elements containing "from" and "to" location/s
         * @todo test
         */
        clearRouteVariables: function() {
            this.to = '';
            this.floatTo = 0;
            this.from = '';
            this.floatFrom = 0;
            var el = null;
            if(el = document.getElementById("currentlySetToFrom")) {el.innerHTML = '';}
            if(el = document.getElementById("currentlySetToTo")) {el.innerHTML = '';}
        },

        /**
         * clears elements containing "directions"
         * @todo test and fix
         */
        clearRouteAll: function() {
            var el = null;
            if(el = document.getElementById(GMO.opts.directionsDivId)) {el.innerHTML = '';}
            this.clearRouteVariables();
            this.routeShown = false;
            GMO.directions.clear();
            GMO.updateStatus("Route Cleared");
        },

        /**
         * handles errors relating to the displaying of directions
         * @todo test
         */
        directionsHandleErrors: function(){
            if (GMO.directions.getStatus().code == G_GEO_UNKNOWN_ADDRESS) {GMO.updateStatus("No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.\nError code: " + GMO.directions.getStatus().code);}
            else if (GMO.directions.getStatus().code == G_GEO_SERVER_ERROR) GMO.updateStatus("A geocoding or directions request could not be successfully processed, yet the exact reason for the failure is not known.\n Error code: " + GMO.directions.getStatus().code);
            else if (GMO.directions.getStatus().code == G_GEO_MISSING_QUERY) GMO.updateStatus("The HTTP q parameter was either missing or had no value. For geocoder requests, this means that an empty address was specified as input. For directions requests, this means that no query was specified in the input.\n Error code: " + GMO.directions.getStatus().code);
            //else if (GMO.directions.getStatus().code == G_UNAVAILABLE_ADDRESS)  GMO.updateStatus("The geocode for the given address or the route for the given directions query cannot be returned due to legal or contractual reasons.\n Error code: " + GMO.directions.getStatus().code);
            else if (GMO.directions.getStatus().code == G_GEO_BAD_KEY) GMO.updateStatus("The given key is either invalid or does not match the domain for which it was given. \n Error code: " + GMO.directions.getStatus().code);
            else if (GMO.directions.getStatus().code == G_GEO_BAD_REQUEST) GMO.updateStatus("A directions request could not be successfully parsed.\n Error code: " + GMO.directions.getStatus().code);
            else GMO.updateStatus("An unknown error occurred - Maybe the start or end address could not be found?");
        },

        /**
         * displays directions upon loading
         * @todo test
         */
        directionsOnLoad: function(){
            GMO.updateStatus("Route Found - Details Loading . . .");
            var html = '';
            function waypoint(point, type, address, pointName) {
                html += ''
                    + '<tr>'
                    +' <th colspan="2">'
                    +'  <a href="javascript:void(0);" onclick="'+this.variableName+'.mapObject.showMapBlowup(new google.maps.LatLng('+point.toUrlValue(6)+')); return false;">'
                    +'   <img src="http://www.google.com/intl/en_ALL/mapfiles/icon-dd-' +type+ '-trans.png" alt="marker">'
                    +'  </a>'
                    +   pointName + " (" + address + ")"
                    +' </th>'
                    +'</tr>';
            }
            function routeDistance(dist) {
                html +=  ''
                    + '<tr><th colspan="2" class="routeDistance">' + dist + '</th></tr>';
            }
            function copyright(text) {
                html +=  ''
                    + '<tr><th colspan="2" class="copyright">' + text + '</th></tr>';
            }
            function detail(point, num, description, dist) {
                html += ''
                    + '<tr>'
                    +' <td>'
                    +'  <a href="javascript:void(0);" onclick="'+this.variableName+'.mapObject.showMapBlowup(new google.maps.LatLng('+point.toUrlValue(6)+'));">'+num+'.</a> '
                    +   description
                    +' </td>'
                    +' <td class="dist">'
                    +   dist
                    +' </td>'
                    +'</tr>';
            }
            function formatDistance(meters) {
                var distanceString = '';
                if (meters > 99) {
                    distanceString += ( (meters/1000).toFixed(1) +"&nbsp;km." );
                }
                else {
                    distanceString += ( meters +"&nbsp;me." );
                }
                miles = meters * 0.000621371192
                distanceString += " / " + ((miles).toFixed(1) +" mi.");
                return distanceString;
            }
                // === read through the GRoutes and GSteps ===
            for (var i=0; i < GMO.directions.getNumRoutes(); i++) {
                if (i==0) {
                    var type="play";
                    var pointName = GMO.from;
                }
                else {
                    var type="pause";
                    var pointName = GMO.via;
                }
                var route = GMO.directions.getRoute(i);
                var geocode = route.getStartGeocode();
                var point = route.getStep(0).getLatLng();
                // create Html
                waypoint(point, type, geocode.address, pointName);
                routeDistance(formatDistance(route.getDistance().meters)+" - about "+route.getDuration().html);
                for (var j=0; j<route.getNumSteps(); j++) {
                    var step = route.getStep(j);
                    // === detail lines for each step ===
                    detail(step.getLatLng(), j+1, step.getDescriptionHtml(), formatDistance(step.getDistance().meters));
                }
            }
            // === the final destination waypoint ===
            var geocode = route.getEndGeocode();
            var point = route.getEndLatLng();
            var pointName = GMO.to;
            waypoint(point, "stop", geocode.address, pointName);
            copyright(GMO.directions.getCopyrightsHtml());
            //open window
            var html =  ''
            + '<div id="directionsInnerDiv">'
            + ' <table class="directionsClassTable"><col class="col1" /><col class="col2"/>' + html + '</table>'
            + '</div>';
            var directionsPanel = document.getElementById(GMO.opts.directionsDivId);
            if(directionsPanel) {
                html += ''
                + '<p id="directionsPrintOptions"><a href="javascript:void(0)" onclick="GMO.printDirections()">Print Directions</a>, <a href="javascript:void(0)" onclick="GMO.clearRouteAll();">Clear Route</a></p>'
                directionsPanel.innerHTML = html;
                GMO.updateStatus('Directions Loaded - <a href="javascript:void(0)" onclick="GMO.printDirections()">Print Directions</a>.');
            }
            else {
                GMO.openDirectionsPopup(html);
            }
            GMO.updateStatus("Route Loaded");
            //clear variables
            GMO.clearRouteVariables();
            GMO.attachStyleDirections(document);
            //setTimeout(GMO.printDirections, 1000);
        },

        /**
         * enlarges the map
         * @todo test
         */
        enlargeMap: function() {
            var el = document.getElementById(this.mapDivName);
            var elLabel = document.getElementById("mapZoomLinkLabel");
            if(el && elLabel) {
                if(this.mapIsZoomed == true) {
                    el.style.width = this.mapOriginalSize.width + "px";
                    el.style.height = this.mapOriginalSize.height + "px";
                    el.style.position = "relative";
                    el.style.top = "";
                    el.style.left = "";
                    el.style.zIndex = "";
                    document.body.style.overflow = 'auto';
                    this.mapIsZoomed = false;
                    elLabel.innerHTML = "full-screen";
                }
                else {
                    el.style.width = this.viewPortWidth()+"px";
                    el.style.height = this.viewPortHeight()+"px";
                    el.style.position = "fixed";
                    el.style.top = 0;
                    el.style.left = 0;
                    el.style.zIndex = "9";
                    document.body.style.overflow = 'hidden';
                    this.mapIsZoomed = true;
                    elLabel.innerHTML = "original size";
                }
                GMO.mapObject.checkResize();
                window.scrollTo(0, 0);
            }
        },

        /**
         * opens a pop up box to print directions
         * @todo test
         */
        printDirections: function() {
            var el = null;
            if(el = document.getElementById("directionsInnerDiv")) {
                var html = el.innerHTML
                GMO.openDirectionsPopup(html);
            }
            else {
                GMO.updateStatus("Could not find directions.");
            }
        },

        /**
         * attacheds a style sheet, is this for the printing of directions?
         * @todo test
         */
        attachStyleDirections: function(selectedDocument) {
            var styleSheetUrl = GMO.opts.styleSheetUrl;
            if(selectedDocument.createStyleSheet) {
                selectedDocument.createStyleSheet(styleSheetUrl);
            }
            else {
                var styles = "@import url(" + styleSheetUrl + ");"; // NOT USED AT PRESENT
                var newSS = document.createElement('link');
                newSS.rel = 'stylesheet';
                newSS.href = styleSheetUrl;
                selectedDocument.getElementsByTagName("head")[0].appendChild(newSS);
            }
        },

        /**
         * opens a pop up box containg html to allow user to print directions
         * @todo test
         */
        openDirectionsPopup: function(innerHTML) {
            if(innerHTML) {
                var html = ''
                    + '<html><head><title>Directions</title>'
                    + '<style type="text/css">'
                    + '</style>'
                    + '<body onLoad="self.focus()">'
                    + '<p><a href="javascript:void(0)" onclick="self.print()">Print This page</a>, <a href="javascript:void(0)" onclick="self.close()">Close Window</a></p>'
                    + innerHTML
                    + '</body></html>';
            }
            else {
                GMO.updateStatus("Could not find directions.");
            }
            var url = "";
            var win = null;
            var features = 'width=500,height=500,menubar=1,resizable=1,scrollbars=1';
            var name = "directions";
            win = window.open(url, name, features);
            var newdocument = null;
            if(newdocument = win.document) {
                newdocument.open();
                newdocument.write(html);
                GMO.attachStyleDirections(newdocument);
                newdocument.close();
            }
            else {
                alert("Could not open directions in new window, please try again.");
            }
        },




        /* marker interaction and computations */

        /**
         * returns the LtnLng of the last marker
         * @todo fix, object has no method getPoint())
         */
        getlastMarkerLonLat: function() {
            return this.lastMarker.getPoint();
        },

        /**
         * shows marker from list, when marker is clicked on?
         * @todo test
         */
        showMarkerFromList: function(selectedId) {
            GMO.closeLastInfoWindow();
            if(selectedId > -1){
                google.maps.event.trigger(this.gmarkers[selectedId],'click');
                //scroll to map ...
                var target = jQuery("#"+GMO.mapDivName);
                if(target.length > 0) {
                    jQuery('html,body').animate(
                        {scrollTop: target.offset().top},
                        1000
                    );
                }
            }
        },

        closeLastInfoWindow: function(){
            if(GMO.lastInfoWindow) {
                GMO.lastInfoWindow.close();
            }
        },

        /**
         * drills hole to the other side of the world from marker clicked on
         * @param float lng
         * @param float lat
         *
         * @return google.maps.LatLng
         */
        antipodeanPointer: function(lng, lat) {
            var point = {}
            if(lng > 0) {
                lng = -180 + lng;
            }
            else {
                lng = 180 + lng;
            }
            lat = lat * -1;
            this.updateStatus("Hole Drilled!");
            point = new google.maps.LatLng(lat, lng, false)
            return point;
        },

        /**
         * check that latitude is a valid latitude
         * @param Int latitude
         */
        checkLatitude: function(latitude) {
            if(latitude && latitude != 0 && latitude >= -90 && latitude <= 90){
                return latitude;
            }
            else {
                return false;
            }
        },

        /**
         * check that longitude is a valid longitude
         * @param Int longitude
         */
        checkLongitude: function(longitude) {
            if(longitude && longitude != 0 && longitude >= -180 && longitude <= 180) {
                return longitude;
            }
            else {
                return false;
            }
        },

        /**
         * check that zoom is a valid zoom (greater than 1, less than 50)
         * @param Int zoom
         */
        checkZoom: function(zoom) {
            if(zoom > 0 && zoom < 50) {
                return zoom;
            }
            else {
                return 0;
            }
        },

        /* marker distances */
        /**
         * return distance in pixels from new point to current point
         * @param Int latLngPoint
         * @todo update to V3 functionality
         */
        distancePerPixel: function(latLngPoint) {
            var pixelCoordinates = GMO.mapObject.fromLatLngToDivPixel(latLngPoint);
            var newPoint = new google.maps.Point(pixelCoordinates.x + 10, pixelCoordinates.y);
            var newlatLngPoint = GMO.mapObject.fromDivPixelToLatLng(newPoint);
            return distance = latLngPoint.distanceFrom(newlatLngPoint)/10;
        },


        /**
         * checks fro hidden markers
         * @param google.maps.Marker marker
         * @todo test
         */
        checkForHiddenMarkers: function(marker) {
            var a = [];
            if(this.gmarkers.length > 1) {
                var currentMarkerBounds = this.obscuringPixelDiv(marker);
                var otherMarkerBounds;
                for (var i = 0; i < this.gmarkers.length; i++) {
                    if (this.gmarkers[i].markerName != marker.markerName) {
                        if (!this.gmarkers[i].view) {
                            if(this.gmarkers[i].type == "marker") {
                                if (this.gmarkers[i].getPosition().lat() >= marker.getPosition().lat()) {
                                    otherMarkerBounds = this.obscuringPixelDiv(this.gmarkers[i]);
                                    if(this.GBoundIntersection(currentMarkerBounds, otherMarkerBounds)) {
                                        a.push (i);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            //BROKEN!!!
            return [];
        },

        /**
         * returns true if bounds intersect
         * @param Array GA
         * @param Array GB
         * @return boolean
         * @todo test
         */
        GBoundIntersection: function(GA, GB) {
            if((GA[0] >= GB[0] && GA[0] <= GB[2]) || (GA[2] >= GB[0] && GA[2] <= GB[2]) || (GA[0] <= GB[0] && GA[2] >= GB[2])){
                if((GA[1] >= GB[1] && GA[1] <= GB[3]) || (GA[3] >= GB[1] && GA[3] <= GB[3]) || (GA[1] <= GB[1] && GA[3] >= GB[3])){
                    return true;
                }
            }
            return false;
        },

        /**
         * returns array of points/markers obscured by infowindow div
         * @param google.maps.Marker marker
         * @return Array
         * @todo test
         */
        obscuringPixelDiv: function(marker) {
            //get pixel point from LatLng
            var icon = marker.getIcon();
            //var PointpixelCoordinates = GMO.mapObject.fromLatLngToDivPixel(marker.getPoint());
            var PointpixelCoordinates = marker.position;
            //get NW offset in pixels from LatLng Px-Ax
            var NWx = PointpixelCoordinates.lat() - icon.anchor.x;
            var NWy = PointpixelCoordinates.lng() - icon.anchor.y;
            //get SE offset in pixels from LatLng
            var SEx = PointpixelCoordinates.lat() + (icon.size.width - icon.anchor.x);
            var SEy = PointpixelCoordinates.lng();
            return new Array(NWx, NWy, SEx, SEy);
        },





        /* update server */
        /**
         * updates status of server
         * @param String v
         * @todo test
         */
        updateServerDone: function(v) {
            GMO.updateStatus(v);
            GMO.updateStatus("done");
        },

        /**
         * returns array of points/markers obscured by infowindow div
         * @param google.maps.Marker marker
         * @return Array
         * @todo test
         */
        /* address finder form */
        /**
         * returns a search form for user to do an addresss search
         * @return String
         */
        findAddressForm: function() {
                var findAddressHtml = ''
                    findAddressHtml = ''
                    //+ '<form action="#">'
                    + ' <p class="findAddressHtml">'
                    + '  <input type="text" size="60" id="mapAddress" class="infoTabInputAddress" />' + GMO.opts.defaultAddressText
                    + '  <input type="button" value="find address" id="infoTabSubmitAddress" class="submitButton" onclick="' + GMO.variableName + '.findAddress(document.getElementById(\'mapAddress\').value); return false" />'
                    + ' </p>'
                    //+ '</form>';
            return findAddressHtml
        },




        /* help */
        /**
         * displays help information for user
         * @return String
         * @todo test
         */
        helpHtml: function() {
            var string = ''
                + '<ul>'
                + '<li><b>keyboard tricks:</b><ul>'
                + '<li>keys <i>up</i>, <i>down</i>, <i>left</i>, <i>right</i>: moves the map continuously while the key is pressed. Two keys can be pressed simultaneously in order to move diagonally.</li>'
                + '<li>keys <i>+</i>, <i>-</i>: zoom in and out respectively</li>'
                + '</ul></li>'
                + '<li><b>mouse tricks:</b><ul>'
                + '<li><i>click on a marker</i> to find out more about the location'
                + '<li><i>click</i> anywhere else on the map to zoom-in on that particular point</li>'
                + '<li><i>click-and-hold-down</i> anywhere on the map to drag in any direction</li>';
            if(this.opts.addPointsToMap) {
                string += ''
                + '<li><i>right-mouse-click</i> on the map to add a marker (for example for creating a route)</li>'
                + '<li><i>right-mouse-click</i> on a marker to delete it</li>'
            }
            else {
                string += ''
                + '<li><i>right-mouse-click</i> to zoom out</li>'
            }
            string += ''
                + '</ul></li>';
            if(this.opts.addKmlLink) {
                string += ''
                + '<li><b>KML files</b> are for use in <a href="http://earth.google.com">Google Earth</a>.';
                + 'Download KML files, save them to the desktop and open them.  From there, the should automatically start Google Earth.</li>';
            }
            if(this.opts.addDirections) {
                string += ''
                + '<li><b>Finding directions</b> can be done by clicking on a first marker and choosing <i>from here</i> under the <i>directions</i> tab, then doing the same for another marker choosing the <i>to here</i> link. Finally, click on the <i>calculate route</i> link at the bottom of the <i>directions</i> tab and the map will calculate your route.</li>';
            }
            string += ''
                + '<li>many thanks to: http://econym.googlepages.com</li>'
                + '<li>module developed by www.sunnysideup.co.nz</li>'
                + '<li>To <b>hide</b> help from the map, just click on the (x).</li>'
                + '</ul>'
            return string;
        },

        /**
         * calculates the width of the viewport
         * @return Int
         * @todo test
         */
        viewPortWidth: function() {
            var viewportwidth;
            // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
            if (typeof window.innerWidth != 'undefined') {
                viewportwidth = window.innerWidth;
            }
            // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
            else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
                viewportwidth = document.documentElement.clientWidth;
            }
            else {
                    viewportwidth = document.getElementsByTagName('body')[0].clientHeight;
            }
            return viewportwidth;
        },

        /**
         * calculates the height of the viewport
         * @return Int
         * @todo test
         */
        viewPortHeight: function() {
            var viewportheight;
            // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerHeight and window.innerHeight
            if (typeof window.innerHeight != 'undefined') {
                viewportheight = window.innerHeight;
            }
            // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
            else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientHeight != 'undefined' && document.documentElement.clientHeight != 0) {
                viewportheight = document.documentElement.clientHeight;
            }
            else {
                    viewportheight = document.getElementsByTagName('body')[0].clientHeight;
            }
            return viewportheight;
        }
    }







    /******************************
     * PUBLIC API
     *
     ******************************/

    /**
     * publicly exposed methods:
     * @var Object - main Object that holds all the private functions
     */
    return {

        /**
         * get any variable
         * @param Mixed
         * @return Mixed
         */
        getVar: function( name ) {
            if ( GMO.hasOwnProperty( name ) ) {
                return GMO[ name ];
            }
        },

        /**
         * set any variable
         * @param String
         * @param Mixed
         * @return Mixed
         */
        setVar: function(name, value) {
            GMO[name] = value;
            return this;
        },

        /**
         * adds layer to map with with points
         * @param URL url
         * @param string title
         *
         * @todo: encapsulate
         */
        addLayer: function(url, title) {
            return GMO.addLayer(url, title);
        },

        /**
         * add a point to the map
         * @todo: change processXML to something else...
         * @todo: move xmlParse to createPointXml method
         * @param LatLng latLng
         * @param String nameString - name for marker
         * @param String description - description for marker
         */
        addPoint: function(latLng, nameString, description) {
            GMO.addPoint(latLng, nameString, description);
        },

        /**
         * finds an address on the map, similar to the opening page of maps.google.com
         * @param String address
         */
        findAddress: function(address) {
            return GMO.findAddress(address);
        },

        /**
         * shows a route for pre-selected locations
         */
        findRoute: function() {
            return GMO.findRoute();
        },

        /**
         * saves your current location on the map
         */
        savePosition: function() {
            return GMO.savePosition();
        },

        /**
         * resets the map to last saved position
         */
        goToSavedPosition: function() {
            //GMO.mapObject.returnToSavedPosition();
            return GMO.goToSavedPosition();
        },

        /**
         * startup map
         *
         */
        init: function(){
            // MAIN

            // store the parameters
            GMO.opts = opts || {};
            GMO.mapDivName = mapDivName;
            GMO.mapIsZoomed = false;
            GMO.mapOriginalSize = {};
            GMO.defaultUrl = url || "";
            GMO.latestUrl = url || "";
            GMO.currentPageURL = location.href.replace(/#/g,""); //replaces all #

            // other useful "global" stuff
            GMO.gmarkers = [];
            GMO.layerInfo = [];
            GMO.lastMarker = {};
            GMO.markerImageCollection = [];
            GMO.imageNum = 0;
            GMO.previousPosition = null;
            if(!GMO.opts.defaultLatitude) {  GMO.opts.defaultLatitude = GMO.NZLatitude; }
            if(!GMO.opts.defaultLongitude) {  GMO.opts.defaultLongitude = GMO.NZLongitude; }
            if(!GMO.opts.defaultZoom) {  GMO.opts.defaultZoom = GMO.NZZoom; }
            if(!GMO.opts.defaultTitle) {GMO.opts.defaultTitle = GMO._t.map_ready; }
            var el = null;

            //clear html areas
            if(GMO.opts.changePageTitle) {document.title = GMO.opts.defaultTitle;}
            if(GMO.opts.titleId) { if(el = document.getElementById(GMO.opts.titleId)) {el.innerHTML = GMO.opts.defaultTitle;}}
            if(GMO.opts.layerListId) { if(el = document.getElementById(GMO.opts.layerListId)) {el.innerHTML = "";} else {GMO.opts.layerListId = "";}}
            if(GMO.opts.sideBarId) { if(el = document.getElementById(GMO.opts.sideBarId)) {el.innerHTML = "";} else {GMO.opts.sideBarId = "";}}
            if(GMO.opts.dropBoxId) { if(el = document.getElementById(GMO.opts.dropBoxId)) {el.innerHTML = "";} else {GMO.opts.dropBoxId = "";}}
            if(GMO.opts.statusDivId) { if( el = document.getElementById(GMO.opts.statusDivId)) {el.innerHTML = GMO._t.loading_map;} else {GMO.opts.statusDivId = "statusDivOnMap";}} else {GMO.opts.statusDivId = "statusDivOnMap";}
            if(GMO.opts.directionsDivId) {
                if(el = document.getElementById(GMO.opts.directionsDivId)) {
                    el.innerHTML = "";
                }
                else {
                    GMO.opts.directionsDivId = "";
                }
            }
            GMO.clearRouteVariables();
            GMO.mapAddress = "";
            GMO.currentlySetToString = "<br />"+ GMO._t.currently_set_to;

            //setup map
            if(!GMO.map) {
                GMO.setupMap(mapDivName);
            }
            var latLng = new google.maps.LatLng(GMO.opts.defaultLatitude, GMO.opts.defaultLongitude, false);
            GMO.zoomTo(latLng, GMO.opts.defaultZoom);
            GMO.mapObject.clearOverlays(GMO.markersArray);
            if(GMO.opts.mapTypeDefault) {
                var mapType = GMO.mapTypesArray(GMO.opts.mapTypeDefault-0);
                GMO.mapObject.setMapTypeId(mapType);
            }
            if(GMO.defaultUrl) {
                GMO.downloadXml(GMO.defaultUrl);
            }
            //GMO.mapOriginalSize = GMO.mapObject.getSize();
            GMO.mapOriginalSize = {width: GMO.mapObject.getDiv().offsetWidth, height: GMO.mapObject.getDiv().offsetHeight};
            GMO.updateStatus(GMO._t.map_ready);
            //GMO.spiderCode = new OverlappingMarkerSpiderfier(GMO.map);
            //GMO.spiderCode.addListener('spiderfy',
            //	function(markers) {
            //		iw.close();
            //	}
            //);
        }
    }



}


/* additional functions */
/**
 * searchs for object in array
 * @return Int
 * @todo test
 */
Array.prototype.inArray = function (v) {
    for (var i=0; i < this.length; i++) {
        if (this[i] === v) {
            return i+1;
        }
    }
    return false;
}
/**
 * searchs for object in sub array
 * @return Int
 * @todo test
 */
Array.prototype.inSubArray = function(variableName, v) {
    var a = []
    for (var i=0; i < this.length; i++) {
        var x = this[i]
        a.push (x[variableName]);
    }
    return a.inArray(v);
}
/**
 * tests to see if map function is defined
 * @return boolen
 * @todo test
 */
function mapFunctionIsDefined(variable) {
    return (typeof(window[variable]) == "undefined")?  false: true;
}



/**
 * clear overlays function to replace deprecated function from google maps api v2
 */
google.maps.Map.prototype.clearOverlays = function(markersArray) {
    for (var i = 0; i < markersArray.length; i++ ) {
        markersArray[i].setMap(null);
    }
    markersArray = [];
}




/**
* Returns an XMLHttp instance to use for asynchronous
* downloading. This method will never throw an exception, but will
* return NULL if the browser does not support XmlHttp for any reason.
* @return {XMLHttpRequest|Null}
*/
function createXmlHttpRequest() {
 try {
   if (typeof ActiveXObject != 'undefined') {
     return new ActiveXObject('Microsoft.XMLHTTP');
   } else if (window["XMLHttpRequest"]) {
     return new XMLHttpRequest();
   }
 } catch (e) {
   changeStatus(e);
 }
 return null;
};

/**
* This functions wraps XMLHttpRequest open/send function.
* It lets you specify a URL and will call the callback if
* it gets a status code of 200.
* @param {String} url The URL to retrieve
* @param {Function} callback The function to call once retrieved.
*/
function downloadUrl(url, callback) {
    var status = -1;
    var request = createXmlHttpRequest();
    if (!request) {
        return false;
    }
    request.onreadystatechange = function() {
    if (request.readyState == 4) {
        try {
            status = request.status;
            }
            catch (e) {
                // Usually indicates request timed out in FF.
            }
            if (status == 200) {
                callback(xmlParse(request.response), request.status);
                request.onreadystatechange = function() {};
            }
        }
    }
    request.open('GET', url, true);
    try {
        request.send(null);
    }
    catch (e) {
        changeStatus(e);
    }
};

/**
 * Parses the given XML string and returns the parsed document in a
 * DOM data structure. This function will return an empty DOM node if
 * XML parsing is not supported in this browser.
 * @param {string} str XML string.
 * @return {Element|Document} DOM.
 */
function xmlParse(str) {
    if (typeof ActiveXObject != 'undefined' && typeof GetObject != 'undefined') {
        var doc = new ActiveXObject('Microsoft.XMLDOM');
        doc.loadXML(str);
        return doc;
    }
    if (typeof DOMParser != 'undefined') {
        return (new DOMParser()).parseFromString(str, 'text/xml');
    }
    return createElement('div', null);
}



//overlapping spider to be added
