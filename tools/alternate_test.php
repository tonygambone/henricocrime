<?php

require_once('AlternateGeocoders.php');
require_once('../dbsetup.php');

$g = new TuckahoeGeocoder(null, true);

$res = mysql_query("SELECT location_text FROM incident WHERE latitude = 0");

print mysql_num_rows($res) . " rows have no location data.\n";

$count = 0;
$fixed = 0;
while ($row = mysql_fetch_assoc($res)) {
  $count++;
  $addr = $row['location_text'];
  $g->address = $addr;
  list( $lat, $lon, $faddr ) = $g->geocode();
  if ( $lat != 0 ) {
    echo "Address found.\n";
    $query = "UPDATE incident SET
      latitude = $lat,
      longitude = $lon,
      found_address = '$faddr'
      WHERE location_text = '$addr'";
    mysql_query($query);
    echo mysql_affected_rows() . " rows updated.\n";
    $fixed++;
  } else {
    echo "... No address found.\n";
  }
}
echo "$fixed of $count fixed.\n";

$res = mysql_query("SELECT location_text FROM incident WHERE latitude = 0");
print mysql_num_rows($res) . " rows currently have no location data.\n";

?>
