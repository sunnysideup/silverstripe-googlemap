<?php
/*
 *
 */


class GoogleMapSiteConfigDOD extends DataExtension {

	protected static $db = array("GoogleMapDefaultTitle" => "Varchar(150)");

	protected static $default = array();


	function updateCMSFields(FieldList $fields) {
		return $fields;
 }



}



