<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

use GuzzleHttp\Client;

echo PHP_EOL . "Locale Update Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

$client = new Client([
	'base_uri' => $schoostApiUrl,
	'headers' => [
		'Authorization' => 'Bearer ' . SMARTCLASS_MOBILE_API_TOKEN,
		'Accept' => 'application/json'
	],
	'allow_redirects' => false
]);

//get locales
$response = $client->get('/locales');

// if ($response->isError()) {
//     throw new \Exception($response->getBody(true));
// }

$locales = json_decode($response->getBody(), true);

$queryData = array();

foreach($locales as $locale) {
	$queryData[] = array(
		'langID'		=> $locale["id"],
		'language'		=> strtolower($locale["title"]),
		'langName'		=> $locale["localTitle"],
		'langTitle'		=> $locale["localTitle"],
		'code'			=> $locale["shortCode"],
		'locale'		=> $locale["longCode"],
		'flag'			=> $locale["icon"]
	);
}

//get all instances in the server
$instances = $dbi->get("smartclass_common.instances");
$instances[] = array("id" => "base", "title" => "Base", "prefix" => "base"); //add base
foreach($instances as $instance)
{
	$insTitle = $instance["title"];
	$insPrefix = $instance["prefix"];
	$mainDB = $insPrefix . "_main";
	
	echo "Working on the instance: " . $insTitle . PHP_EOL;

	//truncate the table first
	$dbi->rawQuery("TRUNCATE TABLE " . $mainDB . ".common_locale");
	
	//insert new date
	$kaydet = $dbi->insertMulti($mainDB . ".common_locale", $queryData);
	
	if($kaydet) echo "\t" . $insTitle . " has been updated for locales." . PHP_EOL;
	else echo "\t" . $insTitle . " has not been updated." . PHP_EOL;
}

echo "Done." . PHP_EOL;
