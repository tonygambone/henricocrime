<?php
$db_host     = '_ADDME_';
$db_username = '_ADDME_';
$db_password = '_ADDME_';
$db_name     = '_ADDME_';
$db_table    = 'incident';

$dbconn = mysql_pconnect($db_host,$db_username,$db_password);
mysql_select_db($db_name,$dbconn);
?>
