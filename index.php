<?php

require_once('dbsetup.php'); //defines and opens $dbconn
require_once('functions.php');

// default is 4 days ago, if $_GET['d'] is not
// set, or is not a date.
if (
  !@isset($_GET['d']) ||
  (($reqdate = strtotime($_GET['d'])) === false)
  ) {
  $reqdate = strtotime("-4 days");
}
// make sure $reqdate is at midnight
$reqdate = strtotime(date("M d, Y", $reqdate));
$prevdate = $reqdate-60*60*24;
$nextdate = $reqdate+60*60*24;

// earliest date for which data exists
$earliest_date = strtotime("February 21, 2006");

// Get incident lists
$incidents = incidents_by_date($reqdate, $nextdate-1);
$categories = incident_category_counts($reqdate, $nextdate-1);
$hours = incident_time_counts($reqdate, $nextdate-1);
$count = count($incidents);

include('common_head.php');

?>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?= get_gmaps_api_key() ?>"
      type="text/javascript"></script>
    <script type="text/javascript" src="henrico.js"></script>
    <script type="text/javascript">
    //<![CDATA[

var map;
var bounds;
var coords;
var icon;

function load() {
  pngfix(document.getElementById('logo'));

  // Icon setup
  icon = new GIcon();
  icon.image = "http://labs.google.com/ridefinder/images/mm_20_red.png";
  icon.iconSize = new GSize(12, 20);
  icon.iconAnchor = new GPoint(6, 20);
  icon.infoWindowAnchor = new GPoint(5, 1);

  // IE VML Setup
  var isIE = navigator.appVersion.match(/MSIE (\d\.\d)/);
  if ((isIE) && isIE[1] >= 6) {
	document.namespaces.add("v", "urn:schemas-microsoft-com:vml");
	document.createStyleSheet().addRule('v\\:*', "behavior: url(#default#VML);");
  }

  // Map setup
  map = new GMap2(document.getElementById("map"));
  map.addControl(new GLargeMapControl());
  map.addControl(new GMapTypeControl());
  map.addControl(new GOverviewMapControl());
  map.setCenter(new GLatLng(clat + 0.05, clon), 10); // leave a little room up north for info windows
  bounds = new GLatLngBounds(new GLatLng(slat, wlon), new GLatLng(nlat, elon));
  showHenricoBorder();
  addLocations();
  

  // Dynamic coordinates display
  coords = document.getElementById('coords');
  GEvent.addListener(map, 'mousemove', displayCoords);
  GEvent.addListener(map, 'mouseout', function() { coords.innerHTML = "&nbsp;"; });
}

function addLocations() {
<?php
// generate locations for the map display
foreach($incidents as $inc) {
  if ($inc['latitude']==0) continue;
  printf(
    "  addMarker( '%s', %f, %f );\n",
    info_html( $inc['location_text'], $inc['description'], $inc['begin_time'] ),
    $inc['latitude'],
    $inc['longitude']
  );
}
?>
}
    //]]>
    </script>
    <link rel="start" href="<?= date("Y/n/j/", $earliest_date) ?>"/>
    <link rel="prev" href="<?= date("Y/n/j/", $prevdate) ?>"/>
    <link rel="next" href="<?= date("Y/n/j/", $nextdate) ?>"/>
  </head>
  <body onload="load()" onunload="GUnload()">

<?php include('common_menu.php'); ?>
  <div id="title">
  <h2>Incidents by day<br/>Showing <?= $count ?> incidents on <?= date("F j, Y", $reqdate) ?></h2>
  </div>

  <div id="info">
    <h2>Introducing HenricoCrime.org</h2>
    <p>HenricoCrime.org tracks police activity
    in Henrico County, Virginia.  Using the Henrico Police Department's
    <a href="http://www.co.henrico.va.us/police/policesearch.htm">publicly available data</a>, we locate each incident and display the 
    information on a map.</p>
    <p><strong>New:</strong> Police incidents are now available as an 
    <a href="georss/" rel="alternate" type="application/rss+xml"><img src="http://www.feedburner.com/fb/images/pub/feed-icon16x16.png" alt="RSS icon" style="vertical-align:middle;border:0"/></a>
    <a href="georss/" rel="alternate" type="application/rss+xml" style="color: #961">RSS feed</a>,
    so you can get daily updates right in your feed reader. The incident location is 
    in there as well, which means you can even <a href="http://maps.google.com/maps?q=http://henricocrime.org/georss/">add them to a Google Map</a> if you like.</p>
    
    <p><strong>Even newer:</strong> HenricoCrime.org is now hosted by Dreamhost instead of
    on my home server. That's just so much better.</p>

    <p style="color: #600;"><strong>The disclaimer:</strong> I can't guarantee the accuracy 
    or completeness of any of this information; in fact, I can guarantee
    that some of it is inaccurate and incomplete. Please keep that in mind.</p>
    <p>New information will be fetched on a daily basis, but it seems like
    it takes about four days for most of the incidents to appear (which is why
    you'll see the incidents from four days ago when you first come to the site).</p>
    <p>You can see details on particular events by clicking each marker on the
    map.  You can also move around and zoom in and out on the map to find 
    a particular area of the county.</p>
    <p>Please note that the markers do not indicate the exact location of
    the incident.  The police do not publicize exact addresses, only 
    approximate locations.</p>
    <p>This website is a free service, and is not associated with the 
    Henrico Police Department. It is inspired by 
    <a href="http://richmondcrime.org">RichmondCrime.org</a>, where you 
    can see crime information for the city of Richmond. Please read the
    <a href="faq/">FAQ</a> for more information.  Thanks for visiting!</p>

    <h3>Statistics for <?= date("F j, Y", $reqdate) ?>:</h3>
    <h4>Incidents by category:</h4>
    <table class="stat cat">
    <?php 
    $c = 'light';
    foreach($categories as $cat) { 
      $c = ($c == 'dark') ? 'light' : 'dark';
	?>
      <tr class="<?= $c ?>">
        <td><?= $cat['description'] ?></td>
        <td><?= $cat['count'] ?></td>
      </tr>
    <?php } ?>
    </table>

    <h4>Incidents by time:</h4>
    <table class="stat time">
    <?php 
    $c = 'light';
    $unitsize = 10;
    for($i = 0; $i <= 23; $i++) { 
      $c = ($c == 'dark') ? 'light' : 'dark';
 	  $timedesc = date('g a',mktime($i)) . ' - ' . date('g a',mktime($i+1));  
	  $count = 0;

      foreach($hours as $hour) {
        if ($hour['hour'] == $i) { 
          $count = $hour['count'];
          break;
        }
      }	?>
      <tr class="<?= $c ?>">
        <td><?= $timedesc ?></td>
        <td><?= $count ?></td>
        <td><div class="timebar" style="width: <?= $unitsize * $count ?>px">&nbsp;</div></td>
      </tr>
    <?php } ?>
    </table>
  </div>

  <div id="container">
    <div class="nav">
    <? if ($prevdate > $earliest_date) { ?>
      <a class="prev" href="<?= date("Y/n/j/", $prevdate) ?>">&laquo; <?= date("F j, Y", $prevdate) ?></a>
    <? } ?>
    <? if ($nextdate <= time()) { ?>
      <a class="next" href="<?= date("Y/n/j/", $nextdate) ?>"><?= date("F j, Y", $nextdate) ?> &raquo;</a>
    <? } ?>
      <div class="spacer"></div>
    </div>
    <div id="map"></div>
    <div id="yattr"><a href="http://developer.yahoo.net/about/">Web Services by Yahoo!</a></div>
    <div id="coords">&nbsp;</div>

<?php if ($count > 0) { ?>
  <h3 style="clear: both;">Incident list <span class="caption">(addresses in red do not appear on the map.)</span></h3>

  <table id="incidents">
  <?php foreach ($incidents as $inc) {
    $class = ($inc['latitude']!=0) ? "inc" : "inc miss";
  ?>
    <tr class="<?= $class ?>">
      <td>
      <h3><?= htmlspecialchars(ucwords(strtolower($inc['location_text']))) ?></h3>
      <p><?= $inc['description'] ?></p>
      <p><?= formatted_time( strtotime($inc['begin_time'])) ?></p>
      </td>
    </tr>
  <?php } ?>
  </table>
<?php } ?>

  </div>

  </body>
</html>
