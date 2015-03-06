<?php
/**
 * Enter an address and check that it is correct ...
 * @package forms
 * @subpackage fields-formattedinput
 */
class AddressFinderField extends TextField {

	private static $_address_array = null;

	/**
	 * returns false if the address can not be found and TRUE
	 * if the address can be found...
	 * @return False | Array
	 */
	function getAddressArray() {
		if(!isset(self::$_address_array) && $this->value) {
			self::$_address_array = GetLatLngFromGoogleUsingAddress::get_placemark_as_array($this->value);
		}
		if(isset(self::$_address_array["Longitude"]) && isset(self::$_address_array["Latitude"])) {
			if(floatval(self::$_address_array["Longitude"]) && floatval(self::$_address_array["Latitude"])) {
				return self::$_address_array;
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
