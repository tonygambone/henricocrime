#!/usr/bin/env php
<?php

require_once("HenricoCrime.php");

$date = $argv[1];

$h=new HenricoCrime($date);
echo $h->get_incidents()." incidents found.\n";

$h->save_incidents();
?>
