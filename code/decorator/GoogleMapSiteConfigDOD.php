<?php
/*
 *
 */


class GoogleMapSiteConfigDOD extends DataExtension {

	private static $db = array("GoogleMapDefaultTitle" => "Varchar(150)");

	private static $default = array();


	function updateCMSFields(FieldList $fields) {
		return $fields;
 }



}



