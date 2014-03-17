<?php

$dbhost = '';
$username = '';
$serverpassword = '';
$dbname = '';

$connect = mysql_connect($dbhost, $username, $serverpassword, $dbname) or die("Couldn't connect!");;
mysql_select_db($dbname) or die("Couldn't find db");

$db_con = new PDO("mysql:host=$dbhost;dbname=$dbname", $username, $serverpassword);

?>
