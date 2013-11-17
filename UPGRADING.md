In v3, we use geocoder.geocode() instead of GClientGeocoder.getLocation().

GClientGeocoder(); -> google.maps.Geocoder
GDirections(); -> google.maps.DirectionsService & google.maps.DirectionsRenderer

http://gabrielduque.wordpress.com/2011/01/13/upgrading-from-google-maps-api-v2-to-v3/


GLatLngBounds() --> google.maps.LatLngBounds()
GlatLng --> google.maps.LatLng
GPoint --> google.maps.Point
Event.addListener --> google.maps.event.addListener
map.getInfoWindow().getPoint --> google.maps.getPosition()
markers[i].getPoint() --> markers[i].getPosition()
closeInfoWindow() --> map.InforWindow.Close();
map.getBoundsZoomLevel(bounds) --> map.fitBounds(bounds)
markers[i].setImage --> .setIcon
map.InfoWindow.close() --> create a function to close
find in maps for objects --> $('#id')[0] or $('#id').get(0) or document.getElementbyId

//TO DO MIGRATION TO V3: http://markus.tao.at/geo/google-maps-api-v3-is-in-town/

As you've noted GDownloadUrl() no longer exists in GMap V3. I'd recommend jQuery.get(url)

http://stackoverflow.com/questions/9838498/how-to-parse-xml-file-for-marker-locations-and-plot-on-map/9843692#9843692

http://www.sitefinity.com/developer-network/forums/developing-with-sitefinity/update-google-map-v2-to-v3

https://developers.google.com/maps/documentation/javascript/examples/

http://stackoverflow.com/questions/16218277/google-maps-api-convert-from-v2-to-v3

BEST: http://gabrielduque.wordpress.com/2011/01/13/upgrading-from-google-maps-api-v2-to-v3/

OFFICIAL: https://developers.google.com/maps/articles/v2tov3

http://markus.tao.at/google-maps-api-v3-is-in-town/

https://developers.google.com/maps/documentation/javascript/tutorial#api_key
