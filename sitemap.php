<?php header('Content-type: text/xml'); 
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">
  <url>
    <loc>http://henricocrime.org/</loc>
    <lastmod><?= date('Y-m-d', @filemtime('index.php')) ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>  
  <url>
    <loc>http://henricocrime.org/cloud/</loc>
    <lastmod><?= date('Y-m-d', @filemtime('cloud.php')) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>  
  <url>
    <loc>http://henricocrime.org/faq/</loc>
    <lastmod><?= date('Y-m-d', @filemtime('faq.php')) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.5</priority>
  </url>  
  <url>
    <loc>http://henricocrime.org/contact/</loc>
    <lastmod><?= date('Y-m-d', @filemtime('contact.php')) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.3</priority>
  </url>  
  <url>
    <loc>http://henricocrime.org/thanks/</loc>
    <lastmod><?= date('Y-m-d', @filemtime('thanks.php')) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.3</priority>
  </url>  
</urlset>
