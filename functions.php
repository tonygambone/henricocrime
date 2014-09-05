<?php

function formatted_time( $timestamp ) {
  if (date("g:i a", $timestamp) == "12:00 am") {
    $format = "n/j/Y";
  } else {
    $format = "n/j/Y - g:i a";
  }
  return date( $format, $timestamp );
}


function info_html( $location, $offense, $time ) {
  return sprintf("<h3>%s</h3><p>%s</p><p>%s</p>",
    ucwords( strtolower($location) ),
    $offense,
    formatted_time( strtotime($time) )
  );
}

function incidents_by_date($startdate,$enddate) {
  $query = "SELECT 
    incident_id, icr_number, location_text, begin_time, incident.offense_text,
    latitude, longitude, offenses.description
    FROM incident
    LEFT JOIN offenses ON
    offenses.offense_code = incident.offense_code
    WHERE 
    begin_time BETWEEN FROM_UNIXTIME($startdate)
    AND FROM_UNIXTIME($enddate)
    AND incident.offense_code < 2699
    ORDER BY begin_time
    ";
  return incidents_by_query($query);
}

function incidents_for_cloud($count, $offset) {
  $query = "SELECT latitude, longitude FROM incident
    WHERE latitude != 0 AND longitude != 0 AND
    begin_time BETWEEN '2006-02-21' AND NOW()
    AND incident.offense_code < 2699
    ORDER BY begin_time DESC
    LIMIT $offset, $count
    ";
  return incidents_by_query($query);
}

function incidents_by_query($query) {
  $res = mysql_query($query);
  $incidents = array();
  if (!$res) return array();
  while ($row = mysql_fetch_assoc($res)) {
    $incidents[] = $row;
  }
  return $incidents;
}

function incident_category_counts($startdate,$enddate) {
  $query = "SELECT COUNT(*) AS count, FLOOR(offense_code/100) AS cat, 
    categories.description
    FROM incident
    LEFT JOIN categories
    ON category = FLOOR(offense_code/100)
    WHERE begin_time BETWEEN FROM_UNIXTIME($startdate)
    AND FROM_UNIXTIME($enddate)
    AND offense_code <= 2699
    GROUP BY FLOOR(offense_code/100)";
  return incidents_by_query($query);
}

function incident_time_counts($startdate,$enddate) {
  $query = "SELECT HOUR(begin_time) AS hour, COUNT(*) AS count
    FROM incident
    WHERE begin_time BETWEEN FROM_UNIXTIME($startdate)
    AND FROM_UNIXTIME($enddate)
    GROUP BY HOUR(begin_time)";
  return incidents_by_query($query);
}

function send_message($message, $from_email = null) {
  return mail(
    'tonygambone+henrico@gmail.com',
    'HenricoCrime.org form submission',
    (($from_email) ? "From: $from_email\r\n" : "") . 
      "IP Address: " . $_SERVER['REMOTE_ADDR'] . 
      " (" . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ")\r\n" .
      $message,
    "From: HenricoCrime.org <tonygambone@gmail.com>\r\n"
  );
}

function get_gmaps_api_key() {
  $henrico_mogrify_org_key = '_GMAPS_API_KEY1_';
  $henricocrime_org_key    = '_GMAPS_API_KEY2_';

  if ($_SERVER['SERVER_NAME'] == 'henrico.mogrify.org') {
    return $henrico_mogrify_org_key;
  } else {
    return $henricocrime_org_key;
  }
}

?>
