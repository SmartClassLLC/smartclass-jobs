<?php

define("SMARTCLASS", true);
define("SMARTCLASS_SECRET_PHRASE", "93S*02m!43a@68r=!7t#27C-23l?00a5&&s(+)s.*");
define("SMARTCLASS_MOBILE_API_TOKEN", "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NDQ2ODg2ODg2fQ.UEw0PxG4bl2BOV3FOkBRe6EoLb5aX6DLNOYDuubfS1M");
//emailing group
define("EMAILING_GROUP", "smartclass-emailing@googlegroups.com");
//set schoost api url
$schoostApiUrl = "https://api.schoost.com";

//autoload
require_once "vendor/autoload.php";

error_reporting(1);
ini_set('display_errors', '1');

$dbhost = getenv('DB_HOST');
$dbport = getenv('DB_PORT');
$dbuname = getenv('DB_UNAME');
$dbpass = getenv('DB_PASS');
$dbnamePrefix = getenv('DBNAME_PREFIX');
$dbname = $dbnamePrefix."_main";
$localization = getenv('LOCALIZATION');
$smartclassUrl = getenv('SMARTCLASS_URL');

//MySQL database connection classes
require_once "class/MySQL/MysqliDb.php";

//add database connection
$dbi = new MysqliDb($dbhost, $dbuname, $dbpass, $dbname, $dbport);

//include common tables
require_once "settings/tables_common.php";

//set php timezone
$timeZone = empty($timeZone) ? "Europe/Istanbul" : $timeZone;
date_default_timezone_set($timeZone);

//general date variables
$simsDate = date("Y-m-d");
$simsTime = date("H:i:s");
$simsTimeWOs = date("H:i");
$simsDateTime = date("Y-m-d H:i:s");

// Include functions file
include "functions/cli.php";

// Include cron file
include "common/Crons/crons.php";
