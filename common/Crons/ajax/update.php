<?

//check if SMARTCLASS defined
if (!defined("SMARTCLASS")) {
    header("Location: index.php");
    die("SmartClass Undefined!");
}

use GuzzleHttp\Client;

echo PHP_EOL . "Update Update Run time: " . date("Y-m-d H:i:s") . PHP_EOL;

$client = new Client([
    'base_uri' => $schoostApiUrl,
    'headers' => [
        'Authorization' => 'Bearer ' . SMARTCLASS_MOBILE_API_TOKEN,
        'Accept' => 'application/json'
    ],
    'allow_redirects' => false
]);

//get locales
$response = $client->get('/updates?isActive=true');

// if ($response->isError()) {
//     throw new \Exception($response->getBody(true));
// }

$updates = json_decode($response->getBody(), true);

$queryData = array();

foreach ($updates as $update) {
    $queryData[] = array(
        'id' => $update["id"],
        'title' => $update["title"],
        'details' => $update["details"],
        'update_date' => $update["updateDate"],
        'version' => $update["version"],
        'update_type' => $update["updateType"],
        'update_icon' => $update["updateIcon"],
        'is_active' => $update["isActive"]
    );
}

//get all instances in the server
$instances = $dbi->get("smartclass_common.instances");
$instances[] = array("id" => "base", "title" => "Base", "prefix" => "base"); //add base
foreach ($instances as $instance) {
    $insTitle = $instance["title"];
    $insPrefix = $instance["prefix"];
    $mainDB = $insPrefix . "_main";

    echo "Working on the instance: " . $insTitle . PHP_EOL;

    //truncate the table first
    $dbi->rawQuery("TRUNCATE TABLE " . $mainDB . ".update");

    //insert new date
    $kaydet = $dbi->insertMulti($mainDB . ".update", $queryData);

    if ($kaydet) echo "\t" . $insTitle . " has been updated for updates." . PHP_EOL;
    else echo "\t" . $insTitle . " has not been updated." . PHP_EOL;
}

echo "Done." . PHP_EOL;
