<?php

require_once('HenricoGeocoder.php');

class GlenAllenGeocoder extends HenricoGeocoder {
  var $url = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=%s&street=%s&city=Glen+Allen&state=VA';
}

class SandstonGeocoder extends HenricoGeocoder {
  var $url = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=%s&street=%s&city=Sandston&state=VA';
}

// Varina - no results
// Lakeside - no results
// Tuckahoe - no results

class TuckahoeGeocoder extends HenricoGeocoder {
  var $url = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=%s&street=%s&city=Tuckahoe&state=VA';
}

?>
