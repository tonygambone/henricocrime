HenricoCrime
============

This is a site I developed as a personal project back in 2007. Similar to sites for Chicago and Richmond, 
it displayed crime incident info on a map. I got the data by scraping the HCPD's public information search form,
and running all of the addresses through various geocoding services looking for a good match.

It also had a nifty "heatmap" view that would simply display a few hundred translucent red dots on the map, so
overlapping ones would appear scarier.

At some point it stopped working (as scrapers often do) and as I had moved out of the county and into Richmond City, 
I took down the site.  Henrico County now provides [official web services](http://randolph.co.henrico.va.us/public-data-access/webservices/default.aspx), so hopefully the scraper will
no longer be necessary.

So this is provided mainly as a historical curiosity :)

Running
-------

This was last updated in 2009, so YMM definitely V.  I'm sure the Google Maps API has changed, and probably
also various PHP stuff (this ran under PHP 4.4.7).  The geocoding services may also have changed or gone for-pay.
The Henrico Police website has no doubt completely changed.

Still with me? Okay then.

Some things you'll need to do:

* Set up a MySQL database (schema TBD)
* Fill in DB credentials in dbsetup.php and tools/HenricoCrime.php
* Add a Google Maps API key to get_gmap_api_key in functions.php
* Change the email info in send_message in functions.php
* Change the log file path in tools/fetch_daily.php

Looking over this now, the SQL stuff is injection-tastic, so don't use any of that unless you parameterize
your queries.

Set up a daily cron job to run tools/fetch_daily.php (on the command line, not an HTTP request). This does
the scraping, geocoding, and database inserting.
