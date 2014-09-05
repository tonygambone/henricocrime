<?php

require_once('dbsetup.php'); //defines and opens $dbconn
require_once('functions.php');

// Get incident locations
$count = 100;
$offset = 0;

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
  icon.image = "images/vapor.png";
  icon.iconSize = new GSize(4, 4);
  icon.iconAnchor = new GPoint(2, 2);

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
 

  // Dynamic coordinates display
  coords = document.getElementById('coords');
  GEvent.addListener(map, 'mousemove', displayCoords);
  GEvent.addListener(map, 'mouseout', function() { coords.innerHTML = "&nbsp;"; });
}

var offset = 0;
var count;

function fetch() {
  document.getElementById('fetching').style.display = 'inline';
  GDownloadUrl("cloud_fetch/"+offset+"/", function(data, responseCode) {
    var xml = GXml.parse(data);
    var ms = xml.documentElement.getElementsByTagName("m");
    count = ms.length;
    
    for (var i=0; i < ms.length; i++) {
      m = ms[i];
      addInertMarker(
        parseFloat(m.getAttribute("lat")),
        parseFloat(m.getAttribute("lng"))
      );
      offset++;
      document.getElementById('cloudCount').innerHTML = offset;
    }
    document.getElementById('fetching').style.display = 'none';
    });
}

    //]]>
    </script>
  </head>
  <body onload="load()" onunload="GUnload()">

<?php include('common_menu.php'); ?>
  <div id="title">
  <h2>The Cloud</h2>
  </div>

  <div id="info">
    <p>The Cloud shows the distribution of police incidents across the county.  Similar to a
    weather radar map, the areas of highest intensity are those with the most activity.  If enough
    incidents are shown on the map, you start to get a pretty good idea of where the "hot spots"
    are.</p>
    <p><strong>Here's how it works.</strong> Each time you click the button below, you add 500 points 
    to your cloud. Each point is a police incident of some kind.  The more points you add, the better
    your cloud looks. <strong>However, the Cloud is likely to make your computer work very hard.</strong>
    It will take a few seconds to load each set of points.  During this time, your browser may become
    unresponsive. This will intensify with each set of points, and <strong>your browser, or
    even your computer, may eventually 
    stop functioning entirely</strong>. I generally stop at about 4000 points, but this will vary
    depending on your computer's RAM and CPU.</p>
    <p>I promise that it's entirely worth it.  But you ought to finish anything important you happen
    to be doing before you try this. To sum up:</p>
    <p><strong style="color: #600;">By clicking the button below, you may cause your browser to
    crash.  Clicking the button indicates that you have read the above.</strong></p>
    <p><input type="button" value="Load some more points!" onclick="fetch()"/> <span id="fetching" style="font-style: italic; display: none;">(Fetching...)</span></p>
    <p>Your cloud has <span id="cloudCount">0</span> points.</p>
  </div>

  <div id="container">
    <div id="map"></div>
    <div id="yattr"><a href="http://developer.yahoo.net/about/">Web Services by Yahoo!</a></div>
    <div id="coords">&nbsp;</div>
  </div>

  </body>
</html>
