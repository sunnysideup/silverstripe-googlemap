<?php
	/**
	 * Geocode an address-string to a set of coordinates using Google's free
	 * geocoding services.
	 *
	 * see: http://code.google.com/apis/maps/documentation/geocoding/index.html
	 *
	 * CHECKS IF CURL / file_get_contents is available
	 * Requirements: allow_url_fopen = on
	 *
	 * @author Ingo Schomme and Nicolaas Francken
	 * @todo Implement CURL with fopen fallback
	 * @todo Implement client-side selection when multiple results are found (through validation-errors and javasript)
	 * @see http://code.google.com/apis/maps/documentation/services.html#Geocoding_Direct
	 *
	 * you can use
	 * GetLatLngFromGoogleUsingAddress::get_placemark_as_array()
	 */
class GetLatLngFromGoogleUsingAddress extends Object {

	/**
	 * For debugging.
	 * needs to be static because we use static methods
	 * in this class
	 * @var Boolean for debugging
	 */
	private static $debug = false;

	/**
	 * location for API
	 *
	 * @var String
	 */
	private static $geocode_url = "http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false";

	 /**
		* default user to first result that is returned.
		*
		* @var boolean
		*/
	private static $default_to_first_result = true;

	/**
	 *
	 * tells you if CURL / file_get_contents is available
	 * set to true , unless it is not sure if CURL is available
	 *
	 * @var boolean
	 */
	private static $server_side_available = true;

	/**
	 * alternative to api key
	 * @var string
	 */
	private static $google_client_id = "";

	/**
	 * Get first placemark as flat array
	 *
	 * @param string $q
	 * @param Boolean $tryAnyway
	 * @return Array
	 */
	public static function get_placemark_as_array($q, $tryAnyway = 0) {
		$debug = Config::inst()->get("GetLatLngFromGoogleUsingAddress","debug");
		$q = trim($q);
		if($q) {
			$result = null;
			$searchRecord = GetLatLngFromGoogleUsingAddressSearchRecord::get()
				->filter(array("SearchPhrase" => Convert::raw2sql($q)))
				->First();
			if($searchRecord && $searchRecord->ResultArray) {
				if(Config::inst()->get("GetLatLngFromGoogleUsingAddress","debug")) {
					debug::show("Results from GetLatLngFromGoogleUsingAddressSearchRecord");
				}
				//@ is important here!
				$result = @unserialize($searchRecord->ResultArray);
				if($result === null) {
					$searchRecord->ResultArray = "";
					$searchRecord->write();
				}
				if(isset($result["FullAddress"]) && isset($result["Longitude"]) && isset($result["Latitude"])) {
					return $result;
				}
				$result = null;
			}
			if(!$result) {
				$result = self::get_placemark($q, $tryAnyway);
				if($debug) {debug::show(print_r($result, 1));}
				if(is_object($result)) {
					$resultArray = self::google_2_ss($result);
					if($debug) {debug::show(print_r($resultArray, 1));}
					if(!isset($searchRecord) || !$searchRecord) {
						$searchRecord = GetLatLngFromGoogleUsingAddressSearchRecord::create();
						$searchRecord->SearchPhrase = Convert::raw2sql($q);
					}
					$searchRecord->ResultArray = serialize($resultArray);
					$searchRecord->write();
					return $resultArray;
				}
				else {
					return Array("FullAddress"=> "Could not find address");
				}
			}
		}
		else {
			return Array("FullAddress"=> "No search term provided");
		}
	}


	/**
	* Get first placemark from google, or return false.
	*
	* @param string $q
	* @param Boolean $tryAnyway
	*
	* @return Object Single placemark | false
	*/
	protected static function get_placemark($q, $tryAnyway = false) {
		if(Config::inst()->get("GetLatLngFromGoogleUsingAddress","server_side_available") || $tryAnyway) {
			$responseObj = self::get_geocode_obj($q);
			if(Config::inst()->get("GetLatLngFromGoogleUsingAddress","debug")) {
				debug::show(print_r($responseObj, 1));
			}
			if($responseObj && $responseObj->status == 'OK' && isset($responseObj->results[0])) {
				//we just take the first address!
				if(Config::inst()->get("GetLatLngFromGoogleUsingAddress","default_to_first_result") || count($responseObj->results) ==1) {
					$result = $responseObj->results[0];
					return $result;
				}
			}
		}
	}


 /**
	 * Get geocode from google.
	 *
	 * @see http://code.google.com/apis/maps/documentation/services.html#Geocoding_Direct
	 * @param string $q Place name (e.g. 'Portland' or '30th Avenue, New York")
	 * @return Object Multiple Placemarks and status code
	 */
	protected static function get_geocode_obj($q) {
		$debug = Config::inst()->get("GetLatLngFromGoogleUsingAddress","debug");
		$q = trim($q);
		if($debug) {
			var_dump($q);
		}
		if(empty($q)) return false;
		$url = sprintf(Config::inst()->get("GetLatLngFromGoogleUsingAddress","geocode_url"), urlencode($q));
		if($clientID = Config::inst()->get("GetLatLngFromGoogleUsingAddress","google_client_id")) {
			$url .= "&client=".$clientID;
		}
		elseif($api = Config::inst()->get("GoogleMap", "google_map_api_key")) {
			$url .= "&key=".$api;
		}
		if(Config::inst()->get("GetLatLngFromGoogleUsingAddress","debug")) {
			debug::show(print_r($url, 1));
		}
		$curl = curl_init($url);
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $curl, CURLOPT_VERBOSE, true );
		$responseString = curl_exec( $curl );
		if(!$responseString) {
			$responseString = file_get_contents($url);
			if(!$responseString) {
				return false;
			}
		}
		if($debug) {debug::show(print_r($responseString, 1));}
		return self::json_decoder($responseString);
	}

	/**
	 *
	 * @param String (JSON)
	 * @param Boolean $assoc
	 *
	 * @return Array
	 */
	private static function json_decoder($content, $assoc = false) {
		return json_decode($content);
	}

	/**
	 *
	 * @param Array
	 *
	 * @return Array
	 */
	private static function json_encoder($content) {
		return json_encode($content);
	}

	/**
	 *
	 *
GOOGLE:
	street_address indicates a precise street address.
	route indicates a named route (such as "US 101").
	intersection indicates a major intersection, usually of two major roads.
	political indicates a political entity. Usually, this type indicates a polygon of some civil administration.
	country indicates the national political entity, and is typically the highest order type returned by the Geocoder.
	administrative_area_level_1 indicates a first-order civil entity below the country level. Within the United States, these administrative levels are states. Not all nations exhibit these administrative levels.
	administrative_area_level_2 indicates a second-order civil entity below the country level. Within the United States, these administrative levels are counties. Not all nations exhibit these administrative levels.
	administrative_area_level_3 indicates a third-order civil entity below the country level. This type indicates a minor civil division. Not all nations exhibit these administrative levels.
	colloquial_area indicates a commonly-used alternative name for the entity.
	locality indicates an incorporated city or town political entity.
	sublocality indicates an first-order civil entity below a locality
	neighborhood indicates a named neighborhood
	premise indicates a named location, usually a building or collection of buildings with a common name
	subpremise indicates a first-order entity below a named location, usually a singular building within a collection of buildings with a common name
	postal_code indicates a postal code as used to address postal mail within the country.
	natural_feature indicates a prominent natural feature.
	airport indicates an airport.
	park indicates a named park.
	point_of_interest indicates a named point of interest. Typically, these "POI"s are prominent local entities that don't easily fit in another category such as "Empire State Building" or "Statue of Liberty."

	post_box indicates a specific postal box.
	street_number indicates the precise street number.
	floor indicates the floor of a building address.
	room indicates the room of a building address.

SS:
	'Latitude' => 'Double(12,7)',
	'Longitude' => 'Double(12,7)',
	'PointString' => 'Text',
	'Address' => 'Text',
	'FullAddress' => 'Text',
	'CountryNameCode' => 'Varchar(3)',
	'AdministrativeAreaName' => 'Varchar(255)',
	'SubAdministrativeAreaName' => 'Varchar(255)',
	'LocalityName' => 'Varchar(255)',
	'PostalCodeNumber' => 'Varchar(30)',
	*/

	protected static $google_2_ss_translation_array = array(
		"administrative_area_level_1" => "AdministrativeAreaName",
		//two into one
		"locality" => "SubAdministrativeAreaName",
		"administrative_area_level_2" => "SubAdministrativeAreaName",
		//two into one!
		"sublocality" => "LocalityName",
		"locality" => "LocalityName",
		//two into one!
		"street_address" => "FullAddress",
		"formatted_address" => "FullAddress",
		//key ones
		"lng" => "Longitude",
		"lat" => "Latitude",
		"country" => "CountryNameCode",
		"postal_code" => "PostalCodeNumber"
	);

	/**
	 * Converts Google Response INTO Silverstripe Google Map Array
	 * that can be saved into a GoogleMapLocationsObject
	 * @param GoogleResponseObject (JSON)
	 * @return Array
	 */
	protected static function google_2_ss($responseObj) {
		//get address parts
		$outputArray = array(
			"Original"=> $responseObj,
			"FullAddress"=> "Could not find address"
		);
		$translationArray = Config::inst()->get("GetLatLngFromGoogleUsingAddress","google_2_ss_translation_array");
		if(isset($responseObj->address_components) && is_array($responseObj->address_components)) {
			foreach($responseObj->address_components as $addressItem) {
				if(
					is_object($addressItem)
					&& isset($addressItem->types)
					&& is_array($addressItem->types)
					&& count($addressItem->types)
					&& isset($addressItem->short_name)
				) {
					if(isset($translationArray[$addressItem->types[0]])) {
						$outputArray[$translationArray[$addressItem->types[0]]] = $addressItem->short_name;
					}
					else {
						$outputArray[$addressItem->types[0]] = $addressItem->short_name;
					}
				}
			}
		}
		if(!empty($responseObj->geometry) && !empty($responseObj->geometry->location)) {
			$outputArray["Longitude"] = $responseObj->geometry->location->lng;
			$outputArray["Latitude"] = $responseObj->geometry->location->lat;
			$outputArray["Accuracy"] = $responseObj->geometry->location_type;
		}
		//get other data
		if(!empty($responseObj->formatted_address)) {
			$outputArray["FullAddress"] = $responseObj->formatted_address;
		}
		return $outputArray;
	}
}



