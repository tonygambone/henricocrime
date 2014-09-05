<?php
header('Content-type: text/plain');

require_once('../dbsetup.php'); // defines and opens $dbconn

$query = "SELECT DISTINCT location_text, begin_time FROM incident WHERE latitude = 0 AND offense_code < 2699";
$res = mysql_query($query);

while ($row = mysql_fetch_assoc($res)) {
  $date = date("n/j/Y", strtotime($row['begin_time']));
  printf("%s: %s\n", $date, $row['location_text']);
}
?>
