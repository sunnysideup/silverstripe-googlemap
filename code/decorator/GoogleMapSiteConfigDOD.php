<?php
/*
 *
 */


class GoogleMapSiteConfigDOD extends DataObjectDecorator {

	function extraStatics(){
		return array(
			'db' => array(
				"GoogleMapDefaultTitle" => "Varchar(150)"
			),
			'default' => array()
		);
	}

	function updateCMSFields(FieldSet &$fields) {
		return $fields;
 }



}



