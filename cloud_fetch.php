<?php
// get a few more locations to add to the cloud.
// generates an XML file for the GXml object
header('Content-type: text/xml');

require_once('dbsetup.php');
require_once('functions.php');

$count = 500;
$offset = intval($_GET['offset']);
$incidents = incidents_for_cloud($count, $offset);

echo "<ms>";
foreach($incidents as $inc) {
  printf(
    "<m lat=\"%f\" lng=\"%f\"/>",
    $inc['latitude'],
    $inc['longitude']
  );
}
echo "</ms>";

?>
