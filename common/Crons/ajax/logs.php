<?

//check if SMARTCLASS defined
if (!defined("SMARTCLASS")) {
    header("Location: index.php");
    die("SmartClass Undefined!");
}

echo PHP_EOL . "Cleaning General Logs Run time: " . date("Y-m-d H:i:s") . PHP_EOL;

$today = new DateTime();
$todayString = $today->format('Y-m-d H:i:s');
$olderThanMonth = new DateTime($todayString . ' 1 month ago');
$olderThanMonthString = $olderThanMonth->format('Y-m-d H:i:s');

//get all instances in the server
$instances = $dbi->get("smartclass_common.instances");
foreach ($instances as $instance) {
    $insTitle = $instance["title"];
    $insPrefix = $instance["prefix"];
    $mainDB = $insPrefix . "_main";

    echo "Working on the instance: " . $insTitle . PHP_EOL;

    // Remove logs that are older than a month
    $dbi->where("islemTarihi<?", [$olderThanMonthString]);
    $update = $dbi->delete($mainDB . ".logs");

    if ($update) {
        echo "Logs have been removed. [" . date("Y-m-d H:i:s") . "]" . PHP_EOL;
    } else {
        echo "Logs could not be removed. [" . date("Y-m-d H:i:s") . "]" . PHP_EOL;
    }
}

echo "Done." . PHP_EOL;
