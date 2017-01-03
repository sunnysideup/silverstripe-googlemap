Google Map
====================

Adds an in-depth application of the google map API to your Silverstripe application


Also See
-----------------------------------

 - embedded maps: https://developers.google.com/maps/documentation/embed/guide
 -

STEP-BY-STEP
-----------------------------------

1.	install googlemap as per usual (composer: sunnysideup/googlemap)

2.	Modify settings as you wish in googlemap.yml.example
		Copy the parts from googlemap.yml.example that you want to edit
		into your mysite config.yml

3.	Run a dev/build

5.	The page type (lets call it TestPage), for which you want to add one more more locations, 
	add the following to the controller class:

	```php
		public function init() {
			parent::init();
			$zoom = $this->DefaultZoom ? $this->DefaultZoom : 15;
			Config::inst()->update("GoogleMap", "default_zoom", $zoom);
			if($this->HasGeoInfo) {
				$this->addMap("showpagepointsmapxml");
			}
		}

	```
	on the parent page controller of the TestPage you can add something like - to show all the points of all child pages ...:
	```php
		public function init() {
			parent::init();
			$this->HasGeoInfo = true;
			$this->addMap("showchildpointsmapxml");
		}	
	```

6.	Find your TestPage Layout template file and add the following: `<% include GoogleMap %>`

7.	load page ?flush=all to see the map




CUSTOMISING POP-UP INFORMATION
-----------------------------------
1. enter CustomPopUpWindowTitle and CustomPopUpWindowInfo in CMS (overrules everything)

2. add the method: CustomAjaxInfoWindow to the PARENT of the point.



___________________________________
KNOWN BUGS AND ISSUES
-----------------------------------
this module does NOT work if the html of your page starts with :
<?xml version="1.0" encoding="UTF-8"?>
http://code.google.com/p/gmaps-api-issues/issues/detail?id=530


___________________________________
Features (some still to do)
-----------------------------------
* add one or more locations per page
* convert addresses to LAT LNG
* convert LAT LNG to addresses
* map with the following side features
 - warns when points are overlapping
 - lists of points and layers with ability to hide
 - move markers
 - add markers
 - delete markers
 - find antipodeans
 - find around me (points closest to point)
 - add polygons, lines and points
 - add directions
 - customise the...
   - icons
	 - map size
	 - viewfinder
	 - zoom
	 - map type
	 - map control type
	 - starting lat, lng, zoom
	 - infowindow
	 - country
	 - locale
* static maps and interactive maps
* use data directly in GoogleEarth Application
* update database using map itself (e.g. drag marker to update location)

___________________________________
Concept
-----------------------------------

Data:
The idea of this module is that you attach zero or more (has_many) datapoints to any page.

Presentation:
you can select any number of pages and pass them to the DataObjectDecorator as a DataObjectSet.
From there, the module works out what points it should use.

Interaction:
If the user can edit the current page, then it can also edit the locations (points) associated with current page.
Add/Edit/Delete can be done on the map itself.

How it works:
Basically,  you add a bunch of pages to the viewer, using the decorator.
The viewer then creates a XML dataset, and JS files.
The JS scripts are loaded inline with html and load the map
The JS scripts also use ajax to retrieve data for map (as xml).
The XML file can be used as a KML file for Google Earth as well.

___________________________________
How to install and use this module
-----------------------------------

1. review _config.php settings
2. add extension: DataObject::add_extension('SiteTree', 'GoogleMapLocationsDOD'));
3. run db/build/?flush=1
you can add maps as follows by adding the following to your init() function in a page controller:
$this->addMap("showPagePointsMapXML"); //SEE GoogleMapLocationsDOD for the type of maps you can add

for each page type you must / can:
- static $defaults = array ("HasGeoInfo" => 1); - allow points to be added in the CMS
- add a map and static map icon (to be implemented)
 *

you can filter the map data for particular pagetypes
 * see GoogleMapDecorator for more information

___________________________________
To Do PHP
-----------------------------------

- lines
- polygons
- integrate with GIS model
- automatically add map IF it has been added to the CMS
- NO STATIC option
- NO dynamic option
- static maps not working
- layer management
- implement:


class GoogleMaps extends RequestHandlingData {
	function __construct($parent, $name) {
		$this->parent = $parent;
		$this->name = $name;
	}

	function Link() {
		return Controller::join_links($this->parent->Link(), $this->name);
	}

	function aroundMe($request) {
		$x = $request->getVar('x');
		$y = $request->getVar('y');

		return json_encode(....);
	}

}


class Page_Controller ... {
	function GoogleMaps() {
		return new GoogleMaps($this, 'GoogleMaps');
	}
}


___________________________________
To Do JS
-----------------------------------
many thanks to : http://econym.googlepages.com/index.htm
created by www.sunnysideup.co.nz
to do:
	-. map.setZoom(marker.accuracy*2 + 3);
	-. replace add marker title from longitue, lattitude to address
	-. replace javascript(void) with a warning message that the command did not work.
	-. allow an icon to be anchored anywhere (right now it is at the center bottom)
	-. zoom to in xml does not work....
	-. make all human added markers dragable
	-. implement this:
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.2">
<Document>
			<Style id="randomColorIcon">
						<IconStyle>
									<Icon>
												<href>http://maps.google.com/mapfiles/kml/pal3/icon21.png</href>
									</Icon>
						</IconStyle>
			</Style>
			<Placemark>
						<name>IconStyle.kml</name>
						<styleUrl>#randomColorIcon</styleUrl>
						<Point>
									<coordinates>-122.36868,37.831145,0</coordinates>
						</Point>
			</Placemark>
</Document>
</kml>
1. if points has styleUrl
	2. find id for styleUrl
		3. get location from styleUrl
			4. replace icon URL with styleUrl
