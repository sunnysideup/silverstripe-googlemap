<?php


class GoogleMapQuickStaticAccess extends Object {




	################################
	# STATIC QUICK ACCESS
	################################

	/**
	 * var arrayOfLatitudeAndLongitude: Array (Latitude" => 123, "Longitude" => 123, "Marker" => "red1");
	 * Marker is optional
	 * @param Array arrayOfLatitudeAndLongitude
	 * @param String title
	 *
	 * @return String (HTML - img tag)
	 */

	public static function quick_static_map($arrayOfLatitudeAndLongitude, $title) {
		$staticMapURL = '';
		$count = 0;
		//width
		$staticMapWidth = Config::inst()->get("GoogleMap", "google_map_width");
		if($staticMapWidth > 512) { $staticMapWidth = 512;}
		//height
		$staticMapHeight = Config::inst()->get("GoogleMap", "google_map_height");
		if($staticMapHeight > 512) { $staticMapHeight = 512;}
		$staticMapURL = "size=".$staticMapWidth."x".$staticMapHeight;
		if(count($arrayOfLatitudeAndLongitude)) {
			//http://maps.google.com/maps/api/staticmap?sensor=true&maptype=map&size=209x310&
			//markers=color:green%7Clabel:A%7C-45.0302,168.663
			//&markers=color:red%7Clabel:Q%7C-36.8667,174.767
			foreach($arrayOfLatitudeAndLongitude as $row) {
				$staticMapURL .= '&amp;markers=color:'.$row["Colour"].'%7Clabel:'.$row["Label"].'%7C';
				$staticMapURL .= round($row["Latitude"], 6).",".round($row["Longitude"], 6);
				$count++;
			}
			if($count == 1) {
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
	protected static function make_static_map_url_into_image($staticMapURL, $title) {
		$fullStaticMapURL =
			'http://maps.google.com/maps/api/staticmap?'
				.Config::inst()->get("GoogleMap", "static_map_settings").'&amp;'
				.$staticMapURL.'&amp;'
				.'key='.Config::inst()->get("GoogleMap", "google_map_api_key");
		if(Config::inst()->get("GoogleMap", "save_static_map_locally")) {
			$fileName = str_replace(array('&', '|', ',', '=', ';'), array('', '', '', '', ''), $staticMapURL);
			$length = strlen($fileName);
			$fileName = "_sGMap".substr(hash("md5", $fileName), 0, 35)."_".$length.".gif";
			$fullStaticMapURL = StaticMapSaverForHTTPS::convert_to_local_file(str_replace('&amp;', '&', $fullStaticMapURL), $fileName);
		}
		return '<img class="staticGoogleMap" src="'.$fullStaticMapURL.'" alt="map: '.Convert::raw2att($title).'" />';
	}

}
