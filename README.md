###############################################
Google Map
Pre 0.1 proof of concept
###############################################


___________________________________
STEP-BY-STEP (by alex.guez [at] gmail dot com)
-----------------------------------

1.	Get a Google Maps API Key : http://code.google.com/apis/maps/signup.html

2.	Put the googlemap folder into the base installation folder (attention : the folder name is case sensitive)

3.	Add 2 lines in the mysite/_config.php :

	a.	DataObject::add_extension('SiteTree', 'GoogleMapLocationsDOD');

	b.	define("GoogleMapAPIKey", "abc"); ///here the abc stands for the key you obtained at step 1.

	c.	Copy all the commented code from googlemap/_config.php to mysite/_config.php and set values according to your needs

4.	Run a dev/build

5.	The page type (lets call it “TestPage”)for which you want to add a location must contain in the controller class:

	a.	static $allowed_actions = array('showPagePointsMapXML');

	b.	public function init() {
					parent::init();
					if($this->HasGeoInfo) { //only add when there are points added
						$this->addMap("showPagePointsMapXML");
					}
				}

6.	Run a dev/build

7.	Create a new page in the TestPage type

8.	Go tho the menu “Map”, add a full address, save&publish

9.	Add in mysite/code/Page.php in the init function the code (for SS 2.3.1, as prototype is loaded by default, we need to load jquery):

10.	Find your template file and add the following: <% include GoogleMap %>

11. to customise information in pop-ups, add the following function to your pages with map: CustomAjaxInfoWindow


___________________________________
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
