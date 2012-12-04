jQuery(document).ready(
	function () {
		swapGoogleMapButtons();
		jQuery("#infoTabSubmitAddress2").click(
			function() {
				if(scriptsLoaded) {
					findAddress(jQuery('#mapAddress2').val());
					return false;
				}
				else {
					loadMapScripts();
					loadAddressWhenReady();
				}
			}
		);
	}
);


function loadMapScripts() {
	jQuery("#map").text("Loading Google Application....");
	google.load("maps", "2.x", {callback: delayedMapStartup });
}
function delayedMapStartup() {
	jQuery("#map").text("Starting map....");
	jQuery.ajax({
		type: "GET",
		url: absoluteBaseURL + "googlemap/javascript/googleMaps.js",
		success:
			function() {
				window.setTimeout(
					function() {
						scriptsLoaded = true;
						initiateGoogleMap();
						jQuery("#MapControlsOutsideMap, #GoogleMapExtraLayersAsList").fadeIn();
						swapGoogleMapButtons();
					}
					, 100
				);
			},
			dataType: "script",
			cache: true
	});
}

var t = '';

function loadAddressWhenReady() {
	if(scriptsLoaded) {
		t = '';
		findAddress(jQuery('#mapAddress2').val());
	}
	else {
		t = window.setTimeout(
			function() {
				loadAddressWhenReady();
			},
			500
		);
	}
}

function swapGoogleMapButtons() {
	if(scriptsLoaded) {
		jQuery(".findAddressHtml").show();
		jQuery(".loadInteractiveMap").hide();
		jQuery("#MapControlsOutsideMap, #GoogleMapExtraLayersAsList").show();
		jQuery(".hideMe").hide();
	}
	else {
		jQuery(".loadInteractiveMap").show();
		jQuery(".findAddressHtml").hide();
		jQuery("#MapControlsOutsideMap, #GoogleMapExtraLayersAsList").hide();
		jQuery(".hideMe").hide();
	}
}