<?php

if (stristr($_SERVER['PHP_SELF'], "db.php")) { header ("Location: index.php"); die(); }

$db = new sql_db($dbhost, $dbuname, $dbpass, $dbname, $dbport);

if(!$db->db_connect_id) {
    echo "Error33: Could not connect to the database!";
}
/*
else
{
	if($lang == "chinese") mysql_query("SET COLLATION_CONNECTION = 'utf8mb4_chinese_ci'");
	else if($lang == "spanish") mysql_query("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'"); 
	else if($lang == "russian") mysql_query("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");  
	else if($lang == "german") mysql_query("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");
	else if($lang == "french") mysql_query("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");
	else if($lang == "arabic") mysql_query("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");
	else if($lang == "turkish") mysql_query("SET COLLATION_CONNECTION = 'utf8mb4_turkish_ci'");
	else if($lang == "ottoman") mysql_query("SET COLLATION_CONNECTION = 'utf8mb4_turkish_ci'");
	else mysql_query("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");

	mysql_query("SET NAMES 'utf8'");
	mysql_query("SET CHARACTER SET 'utf8'");

	if($lang == "chinese") mysql_query("SET COLLATION_CONNECTION = 'utf8_chinese_ci'");  
	else if($lang == "spanish") mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'"); 
	else if($lang == "russian") mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");  
	else if($lang == "german") mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
	else if($lang == "french") mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
	else if($lang == "arabic") mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
	else if($lang == "turkish") mysql_query("SET COLLATION_CONNECTION = 'utf8_turkish_ci'");
	else if($lang == "ottoman") mysql_query("SET COLLATION_CONNECTION = 'utf8_turkish_ci'");
	else mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
}
*/

$dbi = new MysqliDb($dbhost, $dbuname, $dbpass, $dbname, $dbport);

$db->sql_query("SET NAMES 'utf8mb4'");
$db->sql_query("SET CHARACTER SET 'utf8mb4'");
$db->sql_query("SET COLLATION_CONNECTION = 'utf8mb4_general_ci'");
