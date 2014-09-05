// Map bounds
var nlat = 37.71;
var slat = 37.33;
var wlon = -77.66;
var elon = -77.18;
var clat = slat + (nlat-slat)/2;
var clon = wlon + (elon-wlon)/2;

function addMarker( description, lat, long ) {
  var latlng = new GLatLng( lat, long );
  var marker = new GMarker( latlng, icon );
  GEvent.addListener(marker, 'click',
    function() { marker.openInfoWindowHtml('<div class="info">'+description+'</div>'); }
  );
  map.addOverlay(marker);
}

function addInertMarker( lat, long ) {
  var latlng = new GLatLng( lat, long );
  var marker = new GMarker( latlng, icon, {clickable: false} );
  map.addOverlay(marker);
}

function displayCoords( latlng ) {
  var lat = latlng.lat().toString();
  lat = lat.substring(0, 6);
  var lng = latlng.lng().toString();
  lng = lng.substring(0, 7);
  coords.innerHTML = "(" + lat + ", " + lng + ")";
}

function showHenricoBorder() {
  var bpts = new Array(
    new GLatLng( 37.71, -77.62 ), // NW corner
    new GLatLng( 37.68, -77.56 ),
    new GLatLng( 37.70, -77.50 ),
    new GLatLng( 37.68, -77.44 ),
    new GLatLng( 37.60, -77.40 ),
    new GLatLng( 37.54, -77.23 ),
    new GLatLng( 37.49, -77.18 ), // NE corner
    new GLatLng( 37.40, -77.23 ),
    new GLatLng( 37.39, -77.21 ),
    new GLatLng( 37.38, -77.22 ),
    new GLatLng( 37.40, -77.25 ),
    new GLatLng( 37.38, -77.25 ), // SE corner
    new GLatLng( 37.35, -77.28 ),
    new GLatLng( 37.37, -77.31 ),
    new GLatLng( 37.40, -77.30 ),
    new GLatLng( 37.38, -77.33 ),
    new GLatLng( 37.43, -77.43 ),
    new GLatLng( 37.45, -77.42 ), // Ric/Ches/Hen corner SE
    new GLatLng( 37.51, -77.41 ),
    new GLatLng( 37.50, -77.39 ),
    new GLatLng( 37.55, -77.39 ),
    new GLatLng( 37.60, -77.45 ),
    new GLatLng( 37.58, -77.50 ),
    new GLatLng( 37.59, -77.53 ),
    new GLatLng( 37.56, -77.53 ),
    new GLatLng( 37.56, -77.60 ), // Ric/Ches/Hen corner NW
    new GLatLng( 37.56, -77.65 ), // SW corner
    new GLatLng( 37.58, -77.62 ),
    new GLatLng( 37.63, -77.65 ),
    new GLatLng( 37.71, -77.62 ) // NW corner
  );

  map.addOverlay( new GPolyline( bpts, '#006600', 5, 0.4 ));
}
