<?php
class HenricoGeocoder {

  var $address;
  
  var $appid = 'HenricoCrime';
  var $url = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=%s&street=%s&city=Richmond&state=VA';

  var $output = false;
  var $cache = array();

  function HenricoGeocoder($address = null, $output = false) {
    $this->address = $address;
    $this->output = $output;
    $this->nice_address();
  }

  /**
   * Performs the geocode. Returns:
   * array ( $lat, $lon, $found_address ) on 
   * success, and an array ( null, null, null ) on failure.
   * $found_address is a string indicating the address found 
   * by Yahoo.
   *
   * @return array
   */
  function geocode() {
    if ($this->address) {
      $this->nice_address();
      
      // check cache
      if (@isset($this->cache[$this->address])) {
        return $this->cache[$this->address];
      } else {

      $this->msg(sprintf("Geocoding %s.", $this->address));
    
      $response = $this->query($this->address);
      $dom = domxml_open_mem($response);
      if (!$dom) {
        $this->msg("Invalid response from geocoder.");
        return false;
      }
      $domroot = $dom->document_element();
      $results = $domroot->get_elements_by_tagname("Result");
 
      foreach ($results as $r) {
        switch ($r->get_attribute("precision")) {
          case "address":
            // exact match. return good result.
            $lats = $r->get_elements_by_tagname("Latitude");
            $lons = $r->get_elements_by_tagname("Longitude");
            $adrs = $r->get_elements_by_tagname("Address");
            $result = array ( 
              $lats[0]->get_content(), 
              $lons[0]->get_content(),
              $adrs[0]->get_content() );
            $this->add_to_cache($this->address,$result);
            return $result;
            break;
          case "street":
            // inexact match.  look for a good street name,
            // but only if the street number is zero (otherwise,
            // the exact address may get picked up by a different geocoder).

			// get the street name (doesn't work for intersections)
            $matches = array();
            $ct = preg_match('/^(\d+)\s+(.*)$/',$this->address,$matches);
            if ($ct == 0) { // not a numeric street address
              continue;
            } else {
              $num = $matches[1];
              $street = $matches[2];
            }
 
            if ($num != 0) continue;

            $adrs = $r->get_elements_by_tagname("Address");
            $adr = $adrs[0]->get_content();

            if (strpos($adr,$street) !== false) {
              // good enough.
              $lats = $r->get_elements_by_tagname("Latitude");
              $lons = $r->get_elements_by_tagname("Longitude");
              $result = array (
                $lats[0]->get_content(),
                $lons[0]->get_content(),
                $adr);
              $this->add_to_cache($this->address, $result);
              $this->msg("... Found good street match.");
              return $result;
            }            
            break;
          default:
            // not good enough :(
        }
      }
      $result = array( null, null, null );
      $this->msg("... Not found.");
      $this->add_to_cache($this->address,$result);
      return $result;
      }
    } else {
      $this->msg("Set address first.");
      return array( null, null, null );
    }
  }

  function query($qstring) {
    $url = sprintf($this->url,
      $this->appid, 
      urlencode($qstring));
    $ch = @curl_init($url);
    if ($ch) {
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      $response = curl_exec($ch);
      curl_close($ch);
      return $response;
    } else {
      return "";
    }
  }

  function nice_address() { 
    $new_addr = str_replace('BLOCK','',$this->address); 
    $new_addr = str_replace(' E ',' East ',$new_addr);
    $new_addr = str_replace(' W ',' West ',$new_addr);
    $new_addr = str_replace(' N ',' North ',$new_addr);
    $new_addr = str_replace(' S ',' South ',$new_addr);

    $this->address = $new_addr;
  }

  function add_to_cache($key,$result) {
    $this->cache[$key] = $result;
  }

  function msg($str) { if ($this->output) echo $str . "\n"; }
}

?>
