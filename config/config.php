<?php

if (preg_match("/config.php/i", $_SERVER['PHP_SELF']))
{
   header ("Location: index.php");
   die();
}

//Set this to 0 to remove the errors display, set to 1 to see all errors
ini_set('display_errors', '0');
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$dbhost = "localhost";
$dbuname = "smartclass";
$dbpass = "!.*Smart-Class*.!";

//main database
$localization = "tr";
$dbnamePrefix = "dev";
$dbname = $dbnamePrefix."_main";

$smartclassUrl = "https://dev.smartclass.tech";

?>
