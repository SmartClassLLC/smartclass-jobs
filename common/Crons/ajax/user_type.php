    <?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

use GuzzleHttp\Client;

echo PHP_EOL . "User Type Update Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

$client = new Client([
	'base_uri' => $schoostApiUrl,
	'headers' => [
		'Authorization' => 'Bearer ' . SMARTCLASS_MOBILE_API_TOKEN,
		'Accept' => 'application/json'
	],
	'allow_redirects' => false
]);

//set the first page
$page = 1;

$queryData = array();

do {
    //get locales
    $response = $client->get('/user_types?page=' . $page);

    $locales = json_decode($response->getBody(), true);

    if (empty($locales)) {
        $page = 0;
    } else {
        foreach ($locales as $locale) {
            $queryData[] = array(
                'typeID' => $locale["id"],
                'userType' => $locale["title"],
                'loginType' => $locale["loginType"],
                'typeOrder' => $locale["position"],
                'active' => $locale["isActive"],
            );
        }

        $page++;
    }

} while ($page > 0);

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
	$dbi->rawQuery("TRUNCATE TABLE " . $mainDB . ".common_user_type");
	
	//insert new date
	$kaydet = $dbi->insertMulti($mainDB . ".common_user_type", $queryData);
	
	if($kaydet) echo "\t" . $insTitle . " has been updated for user types." . PHP_EOL;
	else echo "\t" . $insTitle . " has not been updated." . PHP_EOL;
}

echo "Done." . PHP_EOL;
