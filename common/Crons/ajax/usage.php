<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

echo PHP_EOL . "Usage Statistics Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

//get all instances in the server
$instances = $dbi->get("smartclass_common.instances");
foreach($instances as $instance)
{
	$i = 0;
	$insTitle = $instance["title"];
	$insPrefix = $instance["prefix"];
	$mainDB = $insPrefix . "_main";
	
	echo "Working on the instance: " . $insTitle . PHP_EOL;
	
	//get default season db
	$dbi->where("ontanimli", "on");
	$dbi->where("aktif", "on");
	$seasonDB = $dbi->getValue($mainDB . ".seasons", "veritabani");

	echo "Checking the season: " . $seasonDB . PHP_EOL;
	
	//include tables' file
    include __DIR__ . "/../../../settings/tables_cli.php";

	if(empty($startDate)) $startDate = date("Y-m-d H:i:s", strtotime("-1 month"));
	if(empty($endDate)) $endDate = $simsDateTime;
	
	//get usage data
	$dbi->join($schoolsTable. " s", "s.subeId=l.subeKodu", "LEFT");
	$dbi->where("l.islemTarihi", array($startDate, $endDate), "BETWEEN");
	$dbi->groupBy("l.subeKodu");
	$usage = $dbi->get($logsTable. " l", null, "(CASE WHEN (l.subeKodu = '0') THEN '" . _GENEL_MUDURLUK . "' ELSE s.subeAdi END) AS x, COUNT(l.id) AS y");

	$schooltitles = serialize(array_column($usage, "x"));
	$schoolusages = serialize(array_column($usage, "y"));
	
	$dbi->insert($schoolUsageStatsTable, array("yValues" => $schoolusages, "xValues" => $schooltitles, "statDate" => $simsDate));

	echo "Usage Statistics have been calculated. [" . date("Y-m-d H:i:s") . "]" . PHP_EOL . PHP_EOL;
}

echo "Done." . PHP_EOL;
