<?php include('common_head.php'); ?>
  <script type="text/javascript">
    function load() { pngfix(document.getElementById('logo')); }
  </script>
  </head>
  <body onload="load()">

<?php include('common_menu.php'); ?>

  <div id="title">
  <h2>(Anticipated) frequently asked questions</h2>
  </div>

  <div id="info" class="full faq">
    <p>Nobody has really asked any questions about HenricoCrime.org yet. These
    are some of the questions that I think people would ask, if they asked. I'll even throw
    in the answers for free.</p>

    <h4>What is HenricoCrime.org?</h4>
    <p>The main reason for HenricoCrime.org's existence is to show incidents
    involving the Henrico County police, in any given day, on a map. The information
    is publicly available from the police department, but it's easier to understand
    what's going on around you if you can visualize it.  In my humble opinion, that is.</p>
    <p>The secondary purpose of this website is to provide some analysis of the 
    data. Long-term trends and categorization of the incidents are some of the things I
    have in mind.  This is still in its early stages, so we'll see what happens.</p>
    <p>The tertiary purpose is just that I'm a geek, and this is fun for me.</p>

    <h4>Who operates HenricoCrime.org?</h4>
    <p>Hi, I'm Tony Gambone.  I live in Henrico County and I work for the Virginia DEQ
    as a web developer and webmaster.  This is entirely a personal project of mine, though.</p>

    <h4>Is this the Henrico County Police Department?</h4>
    <p>Not even the tiniest little bit.  I've never even spoken to those fine folks (except when I 
    let my car tax sticker expire, and when I was driving around with headphones - not my 
    finest hour).  But I appreciate what they do, and I hope this website will
    help contribute to the public's understanding of the job they do every day.  Their website
    is <a href="http://www.co.henrico.va.us/police/" title="Henrico County Police Department">here</a>.</p>

    <h4>Where does the data come from?</h4>
    <p>It comes from the police department's <a href="http://www.co.henrico.va.us/police/policesearch.htm">public 
    ICR search form</a>. You can go there and look up months' worth of police incident information; 
    it's a great service, and it makes this website possible.</p>

    <h4>Is there any information that you're not showing here?</h4>
    <p>Yep. There are certain incidents you'll never see here, because the police department
    doesn't publish them.  Judging from the <a href="http://randolph.co.henrico.va.us/publicdb/exclusions.asp">list of exclusions</a>,
    this is mostly to protect minors or to respect privacy in some situations.</p>
    <p>You can also get more information from the <a href="http://www.co.henrico.va.us/police/policesearch.htm">search form</a>
    than I've chosen to display here.</p>

    <h4>How do you get the locations onto the map?</h4>
    <p>Based on the address of the incident (which is typically not exact, but given by block
    number), I get a latitude and longitude by using <a href="http://developer.yahoo.com/maps/rest/V1/geocode.html">Yahoo's geocoding API</a>.
    Once I have that, it's easy to place those locations on a map.</p>

    <h4>Some of the points on the map are way off. What's the deal?</h4>
    <p>Yeah, I know.  The geocoding (converting an address 
    to latitude/longitude) is great, but it
    isn't perfect.  The same goes for the police data. Sometimes a similar street name or a
    typo will produce a false location.  Because the process is entirely automated, the occasional
    mistake will slip through.  I wish I had time to verify every address whilst sipping on a 
    pi&ntilde;a colada with my toes buried in fine Caribbean beach sand, but I don't.</p>

    <h4>Why don't some of the incidents show up on the map?</h4>
    <p>Sometimes (for the reasons listed in the previous question), an address can't be found.  Occasional
    misses are also part of the geocoding process.</p>

    <h4>Why aren't there any incidents today, or in the last few days?</h4>
    <p>They're not in Henrico's public database yet.  It seems like most of them are in there
    within four days or so, so check back then.</p>

    <h4>Why just Henrico County?</h4>
    <p>Well, because I live there, and not anywhere else. Seemed like a good place to start, anyway.</p>

    <h4>Didn't I see something like this for Richmond?</h4>
    <p>Yep.  <a href="http://richmondcrime.org/">RichmondCrime.org</a> gave me the idea for this site,
    and they gave me some technical assistance and advice.</p>

    <h4>So this is free, right?  What's the catch?</h4>
    <p>No catch.  In fact, I'm not allowed use the data for commercial purposes, so you'll never
    pay for anything here.  You'll never see a single ad on this site, either.</p>

    <h4>But I want to give you my money!</h4>
    <p>That's great, but no thanks.  Give it to charity instead.</p>

    <h4>But...</h4>
    <p>No.</p>
    
  </div>

  </body>
</html>
