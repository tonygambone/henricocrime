<?php
/**
 * HenricoCrime.php
 * 
 * Provides a means of fetching a list of police incidents from
 * Henrico County's website.
 * 
 * Incidents are sorted by time, although some have only date information
 * and thus may appear out of sequence.
 *
 * Currently Henrico does not allow ranges larger than one month. Certain
 * incidents are excluded, including some sex offenses, offenses involving 
 * children, suicides, etc. The full list of excluded types is at
 * http://randolph.co.henrico.va.us/publicdb/exclusions.asp.
 *
 * Henrico does not allow the use of this data for commerical purposes.
 *
 * And finally, this could break at any time if their system is changed.
 * 
 * @author Tony Gambone <tonygambone@gmail.com>
 * @version 1.1
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License
 * 
 */
require_once('HenricoGeocoder.php');
require_once('AlternateGeocoders.php');

class HenricoCrime {
  
  # Start and end dates
  var $sdate;
  var $edate;
  
  # Server locations
  var $host = 'randolph.co.henrico.va.us';
  var $path = '/publicdb/displayoffense.asp';
  
  # Query data to send
  var $vars = array( 
    'sOrderBy'   => ' Order By begin_datetime asc, icr_report_number, 
                      offense_sequence asc',
    'sSortField' => 'begin_datetime',
    'sSortDir'   => 'asc',
    'source'     => 'searchoffense.asp'
  );

  # Database stuff
  var $db_host     = '_ADDME_';
  var $db_username = '_ADDME_';
  var $db_password = '_ADDME_';
  var $db_name     = '_ADDME_';
  var $dbconn;     // Connection resource

  # Regular expression to extract fields from HTML
  var $incident_regex; 

  # Array to hold incidents once they are fetched
  var $incidents = array();

  # Array of district ids and names 
  var $dists = array(
      0 => 'N/A',
      1 => 'BROOKLAND',
      2 => 'FAIRFIELD',
      3 => 'THREE-CHOPT',
      4 => 'TUCKAHOE',
      5 => 'VARINA',
      6 => 'UNKNOWN' );

  # Array of disposition codes and descriptions
  var $disps = array(
      0 => 'N/A',
      1 => 'Cleared by arrest',
      2 => 'Cleared by exception',
      3 => 'not cleared' );

  # Array of incident status codes and descriptions
  var $statuses = array(
      0 => 'N/A',
      1 => 'Unfounded',
      2 => 'Attempted',
      3 => 'Completed' );

  # Array of instances of geocoders
  var $geo;

  # Number of times to retry a geocode request before exiting
  var $geo_retries = 5;

  /**
   * Constructor.  $start_date is required.  
   * If $end_date is not given, all incidents on
   * $start_date will be fetched.
   *
   * The dates can be timestamps or string expressions,
   * as recognized by strtotime().
   *
   * @param mixed $start_date
   * @param mixed $end_date
   * @return void
   */
  function HenricoCrime($start_date, $end_date = null) {
    $this->set_dates($start_date, $end_date);
	$this->initialize_regex();
    $this->g = array(
      new HenricoGeocoder(null, true),
      new GlenAllenGeocoder(null, true),
      new SandstonGeocoder(null, true) );
  }
 
  /**
   * Fetch a list of police incidents in Henrico using the current
   * values of $sdate and $edate as a range.  The result will
   * be stored in the $incidents array.
   *
   * Return value is the number of incidents found if successful, false if 
   * unsuccessful (i.e. the page could not be loaded).
   * 
   * @return integer|false
   */  
  function get_incidents() {
    # Build the query string
    $pairs = array();
    foreach ($this->vars as $name => $val) {
      $pairs[] = $name . "=" . rawurlencode($val);
    }
    $qstring = implode("&",$pairs);

    # Make the request and save the response
    $ch = @curl_init("http://$this->host$this->path?$qstring");
    if ($ch) {
      curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
      curl_setopt($ch, CURLOPT_HEADER,0);
      $response = curl_exec($ch);
      curl_close($ch);
    } else {
      return false;
    }
    
    /*
    $response = "";	   
    $f = fopen("http://$this->host$this->path?$qstring",'r');
    if ($f) {
      while (!feof($f)) {
        $response .= fgets($f,128);
      }
      fclose($f);
    } else {
      return false;
    }
    */

    # Find matches
    preg_match_all(
      $this->incident_regex,$response,$this->incidents,PREG_SET_ORDER);

    # Discard value [0] (the full matched text) for each match
    array_walk($this->incidents,array(&$this,'shift_each'));

    # Use friendly key names in the array 
    array_walk($this->incidents,array(&$this,'format_incident'));

    # Geocode the addresses
    array_walk($this->incidents,array(&$this,'geocode_incident'));

    echo count($this->incidents) . " incidents fetched.";
    return count($this->incidents);
  }
  
  /**
   * Sets date range for the query.  End date is 
   * optional and defaults to the same date as the start date.
   * 
   * @param mixed $start_date
   * @param mixed $end_date
   * @return void
   */
  function set_dates($start_date, $end_date = null) {
    $this->sdate = $this->format_date($start_date);
    $this->edate = ($end_date === null) ? 
    $this->sdate : $this->format_date($end_date);
	
    if (strtotime($this->edate) < strtotime($this->sdate)) {
      # swap values
      list($this->edate,$this->sdate) = array($this->sdate,$this->edate);
    }
	
    # add dates to the list of query string variables
    $this->vars['DtBegin'] = $this->sdate;
    $this->vars['DtEnd']   = $this->edate;
  }

  /**
   * Assemble the regular expression used to parse the incident list.
   * Called by the constructor; there'd be no need to call it again
   * unless one of the parts ($rx_*) were modifed.
   *
   * @return void
   */
  function initialize_regex() {
   $this->incident_regex = '@' .
    '<tr bgcolor="#F.{5}">\r\n' .                          // Row begin
    '<td nowrap align="left">(\d+\*?)</a>[^>]*</td>\r\n' . // Sequence ID
    '<td nowrap align="left">(\d+)</a>[^>]*</td>\r\n' .    // ICR#
    '<td nowrap align="left">(\d+) - ([^<]*)\s*&nbsp;&nbsp;</td>\r\n' . // Offense code and text
    '<td nowrap align="left">([^<]*)&nbsp;&nbsp;</td>\r\n' . // Start date/time
    '<td nowrap align="left">([^<]*)&nbsp;&nbsp;</td>\r\n' . // End date/time
    '<td nowrap align="left">([^<]*)&nbsp;&nbsp;</td>\r\n' . // Status
    '<td nowrap align="left">([^<]*)&nbsp;&nbsp;</td>\r\n' . // Disposition
    '</tr>\r\n' . // Row end
    '<tr bgcolor="#F.{5}">\r\n' .                          // Row begin
    '<td nowrap align="left">&nbsp;&nbsp;</td>\r\n' . // Empty
    '<td nowrap align="left">([^<]*)&nbsp;&nbsp;</td>\r\n' . // Magisterial district
    '<td nowrap align="left">([^<]*)&nbsp;&nbsp;</td>\r\n' . // Block or intersection description
    '<td nowrap align="left">([^<]*)&nbsp;&nbsp;</td>\r\n' . // Location type
    '<td nowrap align="left">([^<]*)&nbsp;&nbsp;</td>\r\n' . // Location detail
    '<td nowrap align="left">(\d+)&nbsp;&nbsp;</td>\r\n' . // Service area
    '<td nowrap align="left">(\d+)&nbsp;&nbsp;</td>\r\n' . // Service response area
    '</tr>\r\n' . // Row end
    '<tr bgcolor="#F.{5}">\r\n' .                          // Row begin
    '<td nowrap align="left">&nbsp;&nbsp;</td>\r\n' . // Empty
    '<td nowrap align="left" colspan="3">([^<]*)&nbsp;&nbsp;</td>\r\n' . // Comments
    '<td nowrap align="left" colspan="3">([^<&]*)&nbsp;&nbsp;</td>\r\n' . // Officer name and ID
    '@'
   ;                                                        
  }

  /**
   * Geocodes an incident and adds 'latitude',
   * 'longitude', and 'found_address' keys to its array. Those
   * elements will be empty if no match was found.
   *
   * @return boolean
   */
  function geocode_incident(&$inc) {
    $current_retries = 0;

    // loop through each geocoder
    for ($i=0; $i < count($this->g); ) {
      $coder = &$this->g[$i];
      $coder->address = $inc['location_text'];
      $result = $coder->geocode();
      
      // check for invalid response
      if ($result === false) {
        if ($current_retries >= $this->geo_retries) {
          echo "Maximum number of retries reached.\n";
          exit(1);
        }
        $current_retries++;
        // try again (no automatic increment)
        echo "Trying geocoder $i again.\n";
        continue; 
      } 
      // check for not found
      elseif ($result == array( null, null, null )) {
        $i++; // next geocoder
        continue;
      }      
      // handle success
      else {
        echo "Found with geocoder $i.\n";
        list( 
          $inc['latitude'], 
          $inc['longitude'],
          $inc['found_address'] ) = $result;
        return true;
      }
    }
    // here if all geocoders are unsuccessful
    echo "Address not found.\n";
    list(
          $inc['latitude'],
          $inc['longitude'],
          $inc['found_address'] ) = array( null, null, null );
    return false;
  }

  /**
   * array_shift wrapper for array_walk().
   * Removes the first element of an array.
   * This is a class function.
   *
   * @return void
   */
  function shift_each(&$array) {
    array_shift($array);
  }

  /**
   * Processes the incidents array into a consistent format for 
   * better data access.
   *
   * @return void
   */
  function format_incident(&$a) {
    $b = array();
    $b['sequence_no']     = intval($a[0]);
    $b['is_primary_icr']  = (strpos($a[0],'*')!==false) ? true : false; 
    $b['icr_number']      = trim($a[1]);
    $b['offense_code']    = intval($a[2]);
    $b['offense_text']    = trim($a[3]);
    $b['begin_time']      = $this->mysqltime($a[4]);
    $b['end_time']        = $this->mysqltime($a[5]);
    $b['status_id']       = $this->status_id($a[6]);
    $b['disposition_id']  = $this->disposition_id($a[7]);
    $b['mag_district_id'] = $this->mag_district_id($a[8]);
    $b['location_text']   = trim(str_replace('&amp;','&',$a[9]));
    $b['location_type']   = trim($a[10]);
    $b['location_detail'] = trim($a[11]);
    $b['service_area']    = intval($a[12]);
    $b['small_response_area'] = intval($a[13]);
    list(
      $b['is_victim_injured'],
      $b['is_prop_vandalized'],
      $b['is_prop_stolen'],
      $b['is_prop_found'],
      $b['is_prop_lost']
    ) = $this->parse_comments($a[14]);
    list ( 
      $b['officer_name'], 
      $b['officer_id'] 
    ) = $this->parse_officer($a[15]);

    $a = $b;
  }

  /**
   * Converts a status description into an integer
   * status ID, and vice versa. Returns false on failure.
   *
   * @param mixed $text_or_id
   * @return integer|string|false
   */
  function status_id($text_or_id) {
    return $this->two_way_array_search($text_or_id,$this->statuses);
  }

  /**
   * Converts a disposition description into an integer
   * status ID, and vice versa. Returns false on failure.
   *
   * @param mixed $text_or_id
   * @return integer|string|false
   */
  function disposition_id($text_or_id) {
    return $this->two_way_array_search($text_or_id,$this->disps);
  }
 
  /**
   * Converts a magisterial district name into an integer
   * status ID, and vice versa. Returns false on failure.
   *
   * @param mixed $text_or_id
   * @return integer|string|false
   */
  function mag_district_id($text_or_id) {
    return $this->two_way_array_search($text_or_id,$this->dists);
  }

  /**
   * Parse the comments field for information about the
   * incident. Returns an array of booleans:
   *  0 => victim injured
   *  1 => property vandalized
   *  2 => property stolen
   *  3 => property found or recovered
   *  4 => property lost
   *
   * @param string $str Comment field
   * @return array
   */
  function parse_comments($str) { 
    $out = array();
    $out[0] = (strpos($str,'Victim Injured') !== false) ? 1 : 0;
    $out[1] = (strpos($str,'Property Vandalized') !== false) ? 1 : 0;
    $out[2] = (strpos($str,'Property Stolen') !== false) ? 1 : 0;
    $out[3] = (strpos($str,'Property Found/Recovered') !== false) ? 1 : 0;
    $out[4] = (strpos($str,'Property Lost') !== false) ? 1 : 0;
    return $out;
  }

  /**
   * Parse the officer name and ID field
   * and return an array of the name and ID.
   * Field is in the form "J. Q. OFFICER (ID#123)".
   * Returns false on failure.
   *
   * @param string $str Officer field text
   * @return array|false array($name,$id)
   */
  function parse_officer($str) {
    if (preg_match('/([^\(]*) \(ID#(\d+)\)/',$str,$matches) !== 0) {
      return array($matches[1],$matches[2]);
    } else {
      return false;
    } 
  }

  /**
   * Format a date as M/D/YYYY so it can be submitted to the remote server.
   * Accepts a timestamp, or a string value if it is recognized by strtotime().
   * Returns a formatted date if successful, false if unsuccessful.
   * This is a class function.
   *
   * @param mixed $date
   * @return string|false
   */
  function format_date($date) {
    if (is_integer($date) && $date >= 0) return date('n/j/Y', $date);
    if (($ts = strtotime($date)) !== false) return date('n/j/Y', $ts);
    return false;
  }
 
  /** 
   * Format a date string from the website for use in MySQL.
   * Returns false if no date was found.
   * 
   * @param string $str Date field from website
   * @return string|false
   */
  function mysqltime($str) {
    if ($str=='') return false;
    return date('Y-m-d H:i:s',strtotime($str));
  }
  /**
   * If the given $key_or_value is in the $array, either as a key or a value, 
   * then its counterpart is returned (i.e. its value if it is a key, or its
   * key if it is a value).  If it is both a key and a value, then 
   * $array[$key_or_value] will be returned, since keys are checked first.
   * If it is a value for more than one key, the first occurrence will
   * be returned.  Returns false if it is not found.
   *
   * @param mixed $key_or_value needle
   * @param array $array haystack
   * @return mixed|false
   */
  function two_way_array_search($key_or_value,$array) {
    if (array_key_exists($key_or_value,$array) ) {
      return $array[$key_or_value];
    } else {
      $key = array_search($key_or_value,$array);
      if ($key !== false) return $key;
      return false;
    }
  }

  /**
   * Save all the incidents into the database table.  Incidents
   * that already exist will be updated; new incidents will be
   * added.
   *
   * @return void
   */
  function save_incidents() {
    if (count($this->incidents) == 0) {
      echo "No incidents to save.\n";
      return;
    }
    if (!$this->dbconn) {
      $this->dbconn = mysql_pconnect(
        $this->db_host,
	$this->db_username,
	$this->db_password
      );
      if (!$this->dbconn) die('Error connecting to the database.');
    }
    
    mysql_select_db($this->db_name, $this->dbconn) or
      die('Could not select the incident database.');

    foreach ($this->incidents as $inc) {
      # SQL conditions to check whether the incident already exists
      $exists_condition = "icr_number = '".
        mysql_real_escape_string($inc['icr_number'], $this->dbconn)."'
        AND sequence_no = ".mysql_real_escape_string($inc['sequence_no'], 
	$this->dbconn);
      
      # see if we need to insert or update
      $res = mysql_query("SELECT incident_id FROM incident 
                   WHERE $exists_condition",$this->dbconn);
      
      if ( mysql_num_rows($res) == 0) {
        $sql = "INSERT INTO incident (" . $this->db_column_string($inc) . 
               ") VALUES (" . $this->db_value_string($inc) . ")";
      } else {
        $sql = "UPDATE incident SET ".$this->db_update_string($inc)." 
	        WHERE $exists_condition LIMIT 1";
      }
      mysql_query($sql,$this->dbconn);
    }
  }

  /**
   * Generate a SQL string representing the columns
   * to be inserted, based on the keys of the incident
   * array.
   * 
   * @param array $a Single incident array
   * @return string
   */
  function db_column_string($a) {
    $columns = array();
    foreach (array_keys($a) as $c) { 
      $columns[] = '`' .
        mysql_real_escape_string($c,$this->dbconn)
	. '`';
    }
    return implode(', ',$columns);
  }

  /**
   * Generate a SQL string representing the values 
   * to be inserted, based on the values of the incident
   * array.
   * 
   * @param array $a Single incident array
   * @return string
   */
  function db_value_string($a) {
    $values = array();
    foreach ($a as $v) {
      $values[] = "'" . 
        mysql_real_escape_string($v,$this->dbconn)
	. "'";
    }
    return implode(', ',$values);
  }

  /**
   * Generate a SQL string representing the 
   * column/value pairs for an update statement, 
   * based on the keys of the incident array.
   * 
   * @param array $a Single incident array
   * @return string
   */
  function db_update_string($a) {
    $pairs = array();
    foreach ($a as $c => $v) {
      $pairs[] = '`' . mysql_real_escape_string($c,$this->dbconn) .
              "`='" . mysql_real_escape_string($v, $this->dbconn) .
	      "'";
    }
    return implode(', ',$pairs);
  }
}

/**
 **  END OF CLASS DEFINITION
 **/

##
## Some usage examples.  This will run if the script is called directly.
##
if ( false && basename(__FILE__) == basename($_SERVER['SCRIPT_NAME']) ) {
  header('Content-type: text/plain');
  
  // basic usage for a single day
  echo "Looking up incidents from two days ago.\n";
  $start_time = "-2 days";
  $h = new HenricoCrime($start_time);  
  if ($h->get_incidents() !== false) {
    echo count($h->incidents) . " incidents listed for " . $h->sdate . ".\n";
  } else {
    echo "An error occurred.\n";
  }
  
  // lookup by date range
  echo "\nLooking up incidents within the last week.\n";
  $start_time = "-6 days";
  $end_time   = "now";
  
  // reuse the same object, just alter the dates
  $h->set_dates($start_time, $end_time);
  
  if ($h->get_incidents() !== false) {
    echo count($h->incidents) . " incidents listed from " . 
      $h->sdate . " to " . $h->edate . ".\n";
  } else {
    echo "An error occurred.\n";
  }
  
  // work with the results
  if (count($h->incidents) > 0) {
    echo "\nHere's an example of an incident:\n";
    print_r($h->incidents[0]);
  }
}

?>
