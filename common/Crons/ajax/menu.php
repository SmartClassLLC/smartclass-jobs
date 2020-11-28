<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

use GuzzleHttp\Client;

echo PHP_EOL . "Menu Update Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

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
	//get locales
	$response = $client->get('/legacy_menus?page=' . $page);
	
	$menus = json_decode($response->getBody(), true);

	if(empty($menus)) {
		$page = 0;
	} else {
		foreach($menus as $k => $menu) {
			
			$isNew = new DateTime($menu["isNew"]);
			$queryData[] = array(
				'id'			=> $menu["id"],
				'menu'			=> $menu["title"],
				'resim'			=> $menu["icon"],
				'url'			=> $menu["url"],
				'appUrl'		=> $menu["appUrl"],
				'menuSirasi'	=> $menu["position"],
				'aktif'			=> $menu["isPublished"] ? "1" : "0",
				'parent_id'		=> $menu["parent"]["id"] ?: NULL,
				'adminMenu'		=> $menu["isAdmin"] ? "on" : "off",
				'headQuarterMenu'=> $menu["isHeadquarters"] ? "on" : "off",
				'campusMenu'	=> $menu["isCampus"] ? "on" : "off",
				'branchMenu'	=> $menu["isSchool"] ? "on" : "off",
				'teacherMenu'	=> $menu["isTeacher"] ? "on" : "off",
				'parentMenu'	=> $menu["isParent"] ? "on" : "off",
				'studentMenu'	=> $menu["isStudent"] ? "on" : "off",
				'newBadge'		=> $isNew->format("Y-m-d H:i:s")
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
	$dbi->rawQuery("TRUNCATE TABLE " . $mainDB . ".common_menu");

	//insert new date
	$kaydet = $dbi->insertMulti($mainDB . ".common_menu", $queryData);

	if($kaydet) echo " ->" . $insTitle . " has been updated for menus." . PHP_EOL;
	else echo " ->" . $insTitle . " has not been updated for menus. Error: " . $dbi->getLastError() . $menu["is_new"] . PHP_EOL;
}

//set foreign key check to zero
$dbi->rawQuery("SET foreign_key_checks = 1");

echo "Done." . PHP_EOL;
