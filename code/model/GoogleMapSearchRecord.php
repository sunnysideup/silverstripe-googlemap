<?php


class GoogleMapSearchRecord extends DataObject {

	private static $db = array(
		"IPAddres" => "Varchar(32)",
		"SearchedFor" => "Text"
	);

	private static $has_one = array(
		"Member" => "Member",
		"Parent" => "SiteTee",
		"GoogleMapLocationsObject" => "GoogleMapLocationsObject"
	);

	public static function create_new($searchedFor, $parentID = 0, $addGoogleMapLocationsObjectOrItsID = false){
		$obj = new GoogleMapSearchRecord();
		$obj->SearchedFor = $searchedFor;
		$obj->ParentID = $parentID;
		if(!$addGoogleMapLocationsObjectOrItsID ) {
			//do nothing
		}
		elseif($addGoogleMapLocationsObjectOrItsID === true || $addGoogleMapLocationsObjectOrItsID === 1) {
			//create object
			$location = new GoogleMapLocationsObject();
			$location->Address = $searchedFor;
			$location->Manual = false;
			$location->write();
			$obj->GoogleMapLocationsObjectID = $location->ID;
		}
		else {
			$obj->GoogleMapLocationsObjectID = $addGoogleMapLocationsObjectOrItsID;
		}
		$obj->write();
		return $obj;
	}


	function onBeforeWrite() {
		parent::onBeforeWrite();
		$m = Member::currentUser();
		if($m) {
			$this->MemberID = $m->ID;
		}
		$this->IPAddres = Controller::curr()->getRequest()->getIP();
	}

}
