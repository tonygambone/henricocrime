<?php

require_once('dbsetup.php'); //defines and opens $dbconn
require_once('functions.php');

// Get incident lists (include 4 days by default)
$incidents = array_reverse(incidents_by_date(time() - 60*60*24*4, time()));

// time of last data fetch (crontab runs at 1:00 AM daily)
$lastfetch = strtotime("1:00am");

header('Content-type: application/rss+xml');
echo '<'.'?'.'xml version="1.0"'.'?'.'>' . "\n"; ?>
<rss version="2.0"
  xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#">
  <channel>
    <title>Henrico County police incidents</title>
    <link>http://henricocrime.org/</link>
    <description>Police incidents in Henrico County, provided by HenricoCrime.org</description>
    <language>en-us</language>
    <pubDate><?= date('r', $lastfetch) ?></pubDate>
    <lastBuildDate><?= date('r', $lastfetch) ?></lastBuildDate>
    <docs>http://blogs.law.harvard.edu/tech/rss</docs>
    <generator>PHP</generator>
    <managingEditor>tonygambone@gmail.com</managingEditor>
    <webMaster>tonygambone@gmail.com</webMaster>
    <ttl>1440</ttl><!-- one day -->
    <?php foreach ($incidents as $inc) { ?>
    <item>
      <title><?= htmlspecialchars($inc['description'] . ' - ' . ucwords(strtolower($inc['location_text']))) ?></title>
      <link>http://henricocrime.org</link>
      <description><![CDATA[<?= info_html( $inc['location_text'], $inc['description'], $inc['begin_time'] ) ?>]]></description>
      <pubDate><?= date('r', strtotime($inc['begin_time'])) ?></pubDate>
      <guid isPermaLink="false"><?= $inc['incident_id'].'-'.$inc['icr_number'] ?></guid>
      <?php if ($inc['latitude'] != 0 && $inc['longitude'] != 0) { ?>
      <geo:lat><?= $inc['latitude'] ?></geo:lat>
      <geo:long><?= $inc['longitude'] ?></geo:long>
      <?php } ?>
    </item>
    <?php } ?>
  </channel>
</rss>
