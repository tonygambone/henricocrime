#!/usr/local/bin/php
<?php

require_once("HenricoCrime.php");

$logfile = '/home/mogrify/sites/henricocrime/henrico.log';

$end = strtotime(date("n/j/Y",time())) - 1; // 1 sec before 12:00 am on the current day
$start = $end - 7*24*60*60 + 1; // 7 day span
$cur = $start;

ob_start();

while ($cur <= $end) {
  $h=new HenricoCrime($cur);
  echo "### " . date("n/j/Y",$cur) . " ###\n";
  $count = $h->get_incidents();
  echo $count." incidents found.\n";
  $h->save_incidents();
  $cur = $cur+60*60*24;
}

$output = ob_get_contents();
ob_end_clean();

// write log
$f = fopen($logfile, 'a');
fwrite($f, "==================================\n" . date('r') . "\n---------------------------------\n");
fwrite($f, $output);
fclose($f);
?>
