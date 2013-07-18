<?php
/**
 * Text field with Email Validation.
 * @package forms
 * @subpackage fields-formattedinput
 */
class AddressFinderField extends TextField {

	private static $addressArray = null;

	function getAddressArray() {
		if(!isset(self::$addressArray) && $this->value) {
			self::$addressArray = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($this->value);
		}
		if(isset(self::$addressArray["Longitude"]) && isset(self::$addressArray["Latitude"])) {
			if(floatval(self::$addressArray["Longitude"]) && floatval(self::$addressArray["Latitude"])) {
				return self::$addressArray;
			}
		}
		return false;
	}

	function validate($validator){
		$this->value = trim($this->value);

		if(!$this->getAddressArray()){
 			$validator->validationError(
 				$this->name,
				_t('AddressFinderField.VALIDATION', "Please enter a valid address."),
				"validation"
			);
			return false;
		}
		else{
			return true;
		}
	}
}
