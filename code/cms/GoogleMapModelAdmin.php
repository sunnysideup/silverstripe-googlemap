<?php

/**
 *@author nicolaas[at]sunnysideup.co.nz
 *@description model admin for template overview
 **/

class GoogleMapModelAdmin extends ModelAdmin
{
    public $showImportForm = true;

    private static $managed_models = array('GoogleMapLocationsObject', 'GoogleMapSearchRecord');

    private static $url_segment = 'google-maps';

    private static $menu_title = 'Google Maps';

    private static $menu_icon = 'googlemap/images/icons/GoogleMapModelAdmin.png';    
}
