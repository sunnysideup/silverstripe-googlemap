<?php
class Page extends SiteTree {

	function CustomAjaxInfoWindow(){
		return "my custom message";
	}

}
class Page_Controller extends ContentController {

	public function init() {
		parent::init();
		//$this->addMap("showemptymap");
		$this->addMap("showPagePointsMapXML");
		//$this->addExtraLayer(...);
		//loadmap - test as /home/loadmap/....
		//$this->addExtraLayersUsingRawLink(...);
		//$this->addAddress("128 evans bay parade, wellington, new zealand");
		//$this->addAllowAddingAndDeletingPoints(...);
		//$this->addUpdateServerUrlAddressSearchPoint(...);
		//$this->addUpdateServerUrlDragend(...);
		//$this->clearCustomMaps(...);
		//$this->addCustomMap(...);
	}

}
