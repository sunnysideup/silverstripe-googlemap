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
	 * @author Ingo Schommer, Silverstripe Ltd. (<firstname>@silverstripe.com), Nicolaas Francken
	 * @version 0.1
	 * @todo Implement CURL with fopen fallback
	 * @todo Implement client-side selection when multiple results are found (through validation-errors and javasript)
	 * @see http://code.google.com/apis/maps/documentation/services.html#Geocoding_Direct
	 */
class GetLatLngFromGoogleUsingAddress extends Object {


	public $status;

	public $Value;

	public static $geocode_url = "http://maps.google.com/maps/geo?sensor=false&oe=utf8&q=%s&output=json&key=%s";

	 /**
		* Instead of asking for a selection on multiple matches,
		* default user to first result thats returned.
		*
		* @var boolean
		*/
	public $defaultToFirstResult = false;


	 /**
		* Storing the first result from validate()
		* for later usage in saveInto().
		*
		* @var object
		*/
	protected $_cachePlacemark;

	 /**
		* Store the coordinates on those pair of
		* fields on the currently used object
		* in the form (only works if the field-saving
		* is triggered by $myForm->saveInto($myObject)).
		*
		* Unset this array to disable auto-saving to these fields.
		*
		* Alternatively, you can use {@link getLat()}
		* and {@link getLng()}.
		*
		* @var array
		*/
	protected $dataFields = array('Lat','Lng');
	 /**
		*
		* tells you if CURL / file_get_contents is available
		* set to true , unless it is not sure if CURL is available
		*
		* @var boolean
		*/
	protected static $server_side_available = true;
	 /**
		* Get geocode from google.
		*
		* @see http://code.google.com/apis/maps/documentation/services.html#Geocoding_Direct
		* @param string $q Place name (e.g. 'Portland' or '30th Avenue, New York")
		* @return Object Multiple Placemarks and status code
		*/

	public static function get_geocode_obj($q) {

		if(!defined("GoogleMapAPIKey")) {
			user_error('Please define a valid Google Maps API Key: GoogleMapAPIKey', E_USER_ERROR);
		}

		if(empty($q)) return false;

		$url = sprintf(self::$geocode_url, urlencode($q), GoogleMapAPIKey);
		$curl = curl_init($url);
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $curl, CURLOPT_VERBOSE, true );
		$response = curl_exec( $curl );
		if(!$response) {
			$response = file_get_contents($url);
			if(!$response) {
				return false;
			}
		}
		return self::json_decoder($response);
	}

	/**
	* Get first placemark from google, or return false.
	*
	* @param string $q
	* @return Object Single placemark
	*/
	public static function get_placemark($q, $tryAnyway = 0) {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$q = trim($q);
		$result = DataObject::get_one("GetLatLngFromGoogleUsingAddressSearchRecord", "{$bt}SearchPhrase{$bt} = '".($q)."'");
		if($result) {
			return unserialize($result->ResultArray);
		}
		elseif(self::$server_side_available || $tryAnyway) {
			$responseObj = self::get_geocode_obj($q);
			if(!$responseObj || $responseObj->Status->code != '200') {
				return "Could not find address";
			}
			else {
				$object = new GetLatLngFromGoogleUsingAddressSearchRecord();
				$object->SearchPhrase = $q;
				$object->ResultArray = serialize($responseObj->Placemark[0]);
				$object->write();
				return $responseObj->Placemark[0];
			}
		}
	}

	/**
	* Get first placemark as flat array
	*
	* @param string $q
	* @return Object Single placemark
	*/
	public static function get_placemark_as_array($q, $tryAnyway = 0) {
		$placemark = self::get_placemark($q, $tryAnyway);
		if(is_object($placemark)) {
			return self::array_flatten($placemark);
		}
		else {
			return Array("Address"=> "Could not find address");
		}
	}
	/**
	* Get first placemark from google, or return false.
	*
	* @param string $q
	* @return Object Single placemark
	*/
	 public function validate() {
		if(empty($this->value)) return false;
		// cache
		//if($this->_cachePlacemark) return $this->_cachePlacemark;

		// get geocode from google
		$responseObj = self::get_geocode_obj($this->value());

		$validator = $this->form->getValidator();

		// TODO Better evaluation of status codes
		if(!$responseObj || $responseObj->status->code != '200') {
			$validator->validationError(
				$this->Name(),
					_t('GetLatLngFromGoogleUsingAddress.LOCATIONNOTFOUND',"Location can't be found - please try again."),
					"validation",
				false
			);
			return false;
		}

		$isUnique = (count($responseObj->Placemark) == 1);
		if(!$isUnique && !$this->defaultToFirstResult) {
		 $validator->validationError(

			$this->Name(),
			_t('GeocoderField.LOCATIONNOTUNIQUE',"Location is not unique, please be more specific"),
			"validation",
			false
		 );
		 return false;
		}
		$placemark =  $responseObj->Placemark[0];
		return ($placemark);
	 }

	 /**
		* Sets query-string as normal value,
		* but also queries the google geocoder
		* to get the first placemark and caches it.
		*
		* @param unknown_type $value
		*/

	 public function setValue($Value) {
		$this->value = $Value;
		if($this->value) {
		 $placemark = self::get_placemark($this->value);
		 if($placemark) {
			$this->_cachePlacemark = $placemark;
			$this->value = $this->_cachePlacemark->address;
		 }
		}
	 }


	 /**
		* Get address of first result.
		*
		* @return string
		*/
	 public function getAddress() {
		if(!isset($this->_cachePlacemark)) $this->_cachePlacemark = self::get_placemark($this->value());
		return $this->_cachePlacemark->address;
	 }

	 /**
		* Get latitude of first result.
		*
		* @return float
		*/
	 public function getLat() {
		if(!isset($this->_cachePlacemark)) $this->_cachePlacemark = self::get_placemark($this->value());

		return (float)$this->_cachePlacemark->Point->coordinates[0];
	 }

	 /**
		* Get longitude of first result.
		*
		* @return float
		*/
	 public function getLng() {
		if(!isset($this->_cachePlacemark)) $this->_cachePlacemark = self::get_placemark($this->value());

		return (float)$this->_cachePlacemark->Point->coordinates[1];
	 }

	 /**
		* Set coordinate storage fields.
		*
		* @param array $arr
		*/
	 public function setDataFields($arr) {
		$this->dataFields = $arr;
	 }

	 /**
		* Get coordinate storage fields.
		*
		* @return array
		*/
	 public function getDataFields() {
		return $this->dataFields;
	 }

	 /**
		* Clear the Placemark (first result)
		* that is cached when {@link validate()} or
		* {@link setValue()} are called.
		*/
	 public function clearCache() {
		unset($this->_cachePlacemark);
	 }

	 private function checkIfServerSideIsAvailable() {
		if(self::$server_side_available) {
		 return true;
		}
		elseif(self::get_placemark("New Zealand", true)) {
		 return self::$server_side_available = true;
		}
	 }


	private function json_decoder($content, $assoc = false) {
		if ( !function_exists('json_decode')){
			include_once(Director::baseFolder().".googlemap/code/converters/thirdparty/Services_JSON.php");
			if ( $assoc ){
				$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
			}
			else {
				$json = new Services_JSON;
			}
			return $json->decode($content);
		}
		else {
			return json_decode($content);
		}
	}

	private function json_encoder($content) {
		if ( !function_exists('json_encode') ){
			include_once(Director::baseFolder().".googlemap/code/converters/thirdparty/Services_JSON.php");
			$json = new Services_JSON;
			return $json->encode($content);
		}
		else {
			return json_encode($content);
		}
	}
	static function array_flatten($array, $preserve_keys = 1, &$newArray = Array()) {
		foreach ($array as $key => $child) {
			if (is_array($child) || is_object($child)) {
				$newArray =& self::array_flatten($child, $preserve_keys, $newArray);
			} elseif ($preserve_keys + is_string($key) > 1) {
				$newArray[$key] = $child;
			} else {
				$newArray[] = $child;
			}
		}
		return $newArray;
	}
}



