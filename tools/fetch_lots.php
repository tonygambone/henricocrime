#!/usr/bin/env php
<?php

require_once("HenricoCrime.php");

$start = strtotime("10/1/2007");
$end = strtotime("10/20/2007");
$cur = $start;


while ($cur <= $end) {
  $h=new HenricoCrime($cur);
  echo "### " . date("n/j/Y",$cur) . " ###\n";
  echo $h->get_incidents()." incidents found.\n";
  $h->save_incidents();
  $cur = $cur+60*60*24;
}
?>
