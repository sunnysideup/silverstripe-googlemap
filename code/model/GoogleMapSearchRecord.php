<?php
/**
 * saves all places searched for on the site.
 *
 *
 */

class GoogleMapSearchRecord extends DataObject
{
    private static $db = array(
        "IPAddres" => "Varchar(32)",
        "SearchedFor" => "Text"
    );

    private static $searcheabl_fields = array(
        "SearchedFor" => "PartialMatchFilter"
    );
    private static $summary_fields = array(
        "SearchedFor" => "Searched for ..."
    );

    private static $has_one = array(
        "Member" => "Member",
        "Parent" => "SiteTree",
        "GoogleMapLocationsObject" => "GoogleMapLocationsObject"
    );

    public static function create_new($searchedFor, $parentID = 0, $addGoogleMapLocationsObjectOrItsID = false)
    {
        $obj = new GoogleMapSearchRecord();
        $obj->SearchedFor = $searchedFor;
        $obj->ParentID = $parentID;
        if ($addGoogleMapLocationsObjectOrItsID) {
            if ($addGoogleMapLocationsObjectOrItsID === true || $addGoogleMapLocationsObjectOrItsID === 1) {
                //create object
                $location = new GoogleMapLocationsObject();
                $location->Address = $searchedFor;
                $location->Manual = false;
                $location->write();
                $obj->GoogleMapLocationsObjectID = $location->ID;
            } else {
                $obj->GoogleMapLocationsObjectID = intval($addGoogleMapLocationsObjectOrItsID);
            }
        }
        $obj->write();
        return $obj;
    }


    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $m = Member::currentUser();
        if ($m) {
            $this->MemberID = $m->ID;
        }
        $this->IPAddres = Controller::curr()->getRequest()->getIP();
    }

    /**
     * @return bool
     */
    public function canDelete($member = null)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canEdit($member = null)
    {
        return false;
    }
}
