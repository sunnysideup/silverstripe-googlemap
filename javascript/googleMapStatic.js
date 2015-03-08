

var googleMapStatic = function(){

	var staticMapObject = {
		/**
		 * have the scripts for the interactive map been loaded?
		 * @var Boolean
		 */
		scriptsLoaded: false,

		/**
		 * variable name for map
		 * @var String
		 */
		variableName: "GMO",

		/**
		 * selector for place where people enter address search
		 * @var String
		 */
		addressInputFieldSelector: '#MapAddress2',

		/**
		 * selector for place where click to search for address
		 * @var String
		 */
		addressSubmitFieldSelector: "#InfoTabSubmitAddress2",

		/**
		 * selector where map is being loaded
		 * @var String
		 */
		mapSelector: "#Map",

		/**
		 * selector for place where click to search for address
		 * @var String
		 */
		variableName: "",

		/**
		 * loading timer
		 * @var object
		 */
		timer: null,

		/**
		 * loading message
		 * @var String
		 */
		loadingGoogleMapText: "Loading Google Application....",

		/**
		 * show only for full map...
		 * @var String
		 */
		extraDivsToShowForRealMapSelectors: ".findAddressHtml, #MapControlsOutsideMap, #GoogleMapExtraLayersAsList",

		/**
		 * show only for static map...
		 * @var String
		 */
		extraDivsToHideForRealMapSelectors: ".loadInteractiveMap, .hideMe",


		/**
		 * startup ...
		 */
		init: function () {
			staticMapObject.swapGoogleMapButtons();
			jQuery(staticMapObject.addressSubmitFieldSelector).on(
				"click",
				function() {
					if(staticMapObject.scriptsLoaded) {
						staticMapObject.variableName.findAddress(jQuery(staticMapObject.addressInputFieldSelector).val());
						return false;
					}
					else {
						staticMapObject.loadMapScripts();
						staticMapObject.loadAddressWhenReady();
					}
				}
			);
		},

		/**
		 * load the google map script ...
		 */
		loadMapScripts: function() {
			jQuery(staticMapObject.mapSelector).text(staticMapObject.loadingGoogelMapText);
			google.load("maps", "3.x", {callback: delayedMapStartup });
		},

		/**
		 * callback function after the google map scripts from Google have been loaded...
		 */
		delayedMapStartup: function() {
			jQuery(staticMapObject.mapSelector).text(staticMapObject.loadingGoogleMapText);
			jQuery.ajax({
				type: "GET",
				url: jQuery("base").attr("href") + "googlemap/javascript/googleMaps.js",
				success:
					function() {
						window.setTimeout(
							function() {
								staticMapObject.scriptsLoaded = true;
								staticMapObject.initiateGoogleMap();
								staticMapObject.swapGoogleMapButtons();
							}
							, 100
						);
					},
					dataType: "script",
					cache: true
			});
		},

		/**
		 * load address if pre-entered...
		 */
		loadAddressWhenReady: function() {
			if(staticMapObject.scriptsLoaded) {
				staticMapObject.timer = null;
				staticMapObject.variableName.findAddress(jQuery(staticMapObject.addressInputFieldSelector).val());
			}
			else {
				staticMapObject.timer = window.setTimeout(
					function() {
						staticMapObject.loadAddressWhenReady();
					},
					500
				);
			}
		},

		/**
		 * swap divs
		 */
		swapGoogleMapButtons: function() {
			if(staticMapObject.scriptsLoaded) {
				jQuery(staticMapObject.extraDivsToShowForRealMapSelectors).fadeIn();
				jQuery(staticMapObject.extraDivsToHideForRealMapSelectors).fadeOut();
			}
			else {
				jQuery(staticMapObject.extraDivsToHideForRealMapSelectors).fadeIn();
				jQuery(staticMapObject.extraDivsToShowForRealMapSelectors).fadeOut();
			}
		}
	}

	/**
	 * publicly exposed methods:
	 * @var Object - main Object that holds all the private functions
	 */
	return {

		/**
		 * get any variable
		 * @param Mixed
		 * @return Mixed
		 */
		getVar: function( variableName ) {
			if ( staticMapObject.hasOwnProperty( variableName ) ) {
				return staticMapObject[ variableName ];
			}
		},

		/**
		 * set any variable
		 * @param String
		 * @param Mixed
		 * @return Mixed
		 */
		setVar: function(variableName, value) {
			staticMapObject[variableName] = value;
			return this;
		},

		init: function() {
			jQuery(document).ready(
				function() {
					staticMapObject.init();
				}
			);
			return this;
		}
	}
}
