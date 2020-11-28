<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

//get default language
$currentlang = $dbi->where("configKey", "language")->getValue($mainDB.".configuration", "configValue");

//include main language file
include __DIR__ . "/../language/".$currentlang."/statics.php";
include __DIR__ . "/../language/".$currentlang."/variables.php";

//define db translations	
$translations = $dbi->get("smartclass_common.languages", null, array("alan", "english", $currentlang ." as translation"));
foreach($translations as $translation)
{
	define($translation["alan"], empty($translation["translation"]) ? $translation["english"] : $translation["translation"]);
}
