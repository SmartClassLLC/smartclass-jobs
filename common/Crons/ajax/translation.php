<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

use GuzzleHttp\Client;

echo PHP_EOL . "Translation Update Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

$client = new Client([
	'base_uri' => $schoostApiUrl,
	'headers' => [
		'Authorization' => 'Bearer ' . SMARTCLASS_MOBILE_API_TOKEN,
		'Accept' => 'application/json'
	],
	'allow_redirects' => false
]);

//set error to false
$hata = false;

//set the first page
$page = 1;

//query data
$queryData = array();

do {
	//get translations
	$response = $client->get('/translations?page=' . $page);
	
	$menus = json_decode($response->getBody(), true);
	
	if(empty($menus)) {
		$page = 0;
	}
	else {
		foreach($menus as $k => $menu) {
			
			$isNew = new DateTime($menu["isNew"]);
			$queryData[] = array(
				'id'		=> $menu["id"],
				'alan'		=> $menu["name"],
				'turkish'	=> $menu["tr"],
				'english'	=> $menu["en"],
				'chinese'	=> $menu["zh"],
				'arabic'	=> $menu["ar"],
				'spanish'	=> $menu["es"],
				'russian'	=> $menu["ru"],
				'french'	=> $menu["fr"],
				'german'	=> $menu["de"],
				'hebrew'	=> $menu["he"]
			);
		}
		
		$page++;
	}
	
} while ($page > 0);

//set foreign key check to zero
$dbi->rawQuery("SET foreign_key_checks = 0");

//get all instances in the server
$instances = $dbi->get("smartclass_common.instances");
$instances[] = array("id" => "base", "title" => "Base", "prefix" => "base"); //add base
foreach($instances as $instance)
{
	$insTitle = $instance["title"];
	$insPrefix = $instance["prefix"];
	$mainDB = $insPrefix . "_main";
	
	echo "Working on the instance: " . $insTitle . PHP_EOL;

	//clean the table
	$dbi->rawQuery("TRUNCATE TABLE " . $mainDB . ".common_translation");

	//insert new date
	$kaydet = $dbi->insertMulti($mainDB . ".common_translation", $queryData);

	if($kaydet) echo "-> " . $insTitle . " has been updated for translations." . PHP_EOL;
	else echo "-> " . $insTitle . " has not been updated for translations. Error: " . $dbi->getLastError() . $menu["is_new"] . PHP_EOL;
}
		
//set foreign key check to zero
$dbi->rawQuery("SET foreign_key_checks = 1");

//create language files
echo "Handling language files." . PHP_EOL;

//get locales first
$dbi->where("active", "1");
$locales = $dbi->get("base_main.common_locale", null, "language, code");

foreach($locales as $locale) {

	// get the translation file
	$handle = fopen(__DIR__ . '/../../../language/' . $locale['language'] . '/' . $locale['language'] . '.php', 'w');
	$filedata = '<?php' . PHP_EOL . PHP_EOL;
	
	// get translations
	if($locale['code'] == 'en') {
	
		$translations = $dbi->get('base_main.common_translation', null, array('alan', $locale['language'] . ' as translation'));
	
		foreach($translations as $translation) {
			$filedata .= 'define("' . $translation["alan"] . '", "' . $translation["translation"] . '");' . PHP_EOL;
		}
	}
	else {
		$translations = $dbi->get('base_main.common_translation', null, array('alan', 'english', $locale['language'] . ' as translation'));
		
		foreach($translations as $translation) {
			$trans = $translation['translation'] ?: $translation["english"];
			$filedata .= 'define("' . $translation["alan"] . '", "' . $trans . '");' . PHP_EOL;
		}
	}
	
	//write the file
	fwrite($handle, $filedata);

    // get translations for React UI
    $handle = fopen(__DIR__ . '/../../../language/' . $locale['language'] . '/' . $locale['code'] . '.json', 'w');

    // get translations
    if($locale['code'] == 'en' || $locale['code'] == 'tr') {
        $translations = $dbi->orderBy('langVar', 'ASC')->map('langVar')->get('base_main.common_translation', null, 'SUBSTR(alan, 2, LENGTH(alan)-1) AS langVar, ' . $locale['language'] . ' as translation');
    }
    else {
        $translations = [];
        $translationsArray = $dbi->orderBy('langVar', 'ASC')->get('base_main.common_translation', null, 'SUBSTR(alan, 2, LENGTH(alan)-1) AS langVar, english, ' . $locale['language'] . ' as translation');

        foreach($translationsArray as $translation) {
            $trans = $translation['translation'] ?: $translation["english"];
            $translations[$translation['langVar']] = $trans;
        }
    }

    $filedata = json_encode($translations, JSON_PRETTY_PRINT);

    //write the file
    fwrite($handle, $filedata);

	//message
	echo '-> ' . $locale['language'] . " file has been written." . PHP_EOL;
}

echo "Done!" . PHP_EOL;
