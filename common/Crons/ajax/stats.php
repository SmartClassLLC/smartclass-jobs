<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

echo PHP_EOL . "School Stats Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

$twoWeeksAgo = date("Y-m-d 00:00:00", strtotime("-13 days"));
$weekAgo = date("Y-m-d 00:00:00", strtotime("-7 days"));
$yesterday = date("Y-m-d", strtotime("-1 day"));
$yesterdayWTime = $yesterday . " 23:59:59";

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
	
	//echo $dbi->lastq();
	
	echo "Checking the season: " . $seasonDB . PHP_EOL;
	
	//include tables' file
    include __DIR__ . "/../../../settings/tables_cli.php";

	$statItems = array();
	$statItems[] = array("title" => "meeting", "barColor" => "coral", "chartjsBorderColor" => "#34bfa3", "chartjsBgColor" => "#dcf4f0", "table" => $meetingsTable, "schoolColumn" => "schoolId", "dateColumn" => "meetingStart");
	$statItems[] = array("title" => "homework", "barColor" => "crimson", "chartjsBorderColor" => "#f8b822", "chartjsBgColor" => "#fdf2d8", "table" => $homeworksTable, "schoolColumn" => "subeKodu", "dateColumn" => "createDate");
	$statItems[] = array("title" => "post", "barColor" => "deepskyblue", "chartjsBorderColor" => "#7169ca", "chartjsBgColor" => "#e3e1f4", "table" => $socialPostsTable, "schoolColumn" => "schoolId", "dateColumn" => "createdAt");
	$statItems[] = array("title" => "email", "barColor" => "olivedrab", "chartjsBorderColor" => "#3ec8de", "chartjsBgColor" => "#eefbfd", "table" => $emailLogsTable, "schoolColumn" => "subeKodu", "dateColumn" => "sentDateTime");
	$statItems[] = array("title" => "notification", "barColor" => "orchid", "chartjsBorderColor" => "#f8397a", "chartjsBgColor" => "#fde0ec", "table" => $notificationsLogsTable, "schoolColumn" => "schoolId", "dateColumn" => "sendDateTime");
	$statItems[] = array("title" => "announcement", "barColor" => "darkmagenta", "chartjsBorderColor" => "#34bfa3", "chartjsBgColor" => "#dcf4f0", "table" => $annsTable, "schoolColumn" => "subeKodu", "dateColumn" => "OnayTarihi");
	
	//get schools
	$dbi->where("aktif", "1");
	$dbi->orderBy("subeid", "asc");
	$schools = $dbi->get($schoolsTable, null, "subeid, countryCode");
	
	foreach($schools as $school)
	{
		$schoolid = $school["subeid"];
		$schoolLocale = strtolower($school["countryCode"]);
		
		//set locale code
		$localeCode = empty($schoolLocale) ? "tr" : $schoolLocale;
		
		//include localization file
		include_once "localization/" . $localeCode . ".php";
		
		$queryData = array();
		
		foreach($statItems as $statItem)
		{
			//two weeks ago
			$dbi->where($statItem["schoolColumn"], $schoolid);
			$dbi->where($statItem["dateColumn"], array($twoWeeksAgo, $weekAgo), "BETWEEN");
			$nofLast2Weeks = $dbi->getValue($statItem["table"], "COUNT(id)");

			//last week				
			$dbi->where($statItem["schoolColumn"], $schoolid);
			$dbi->where($statItem["dateColumn"], array($weekAgo, $yesterdayWTime), "BETWEEN");
			$dbi->groupBy("dDate");
			$rowsLastWeek = $dbi->map("dDate")->get($statItem["table"], null, "DATE(" . $statItem["dateColumn"] . ") AS dDate, COUNT(id) AS nof");

			$d = substr($weekAgo, 0, 10);
			while(strtotime($d) < strtotime($simsDate))
			{
				if(empty($rowsLastWeek) || !array_key_exists($d, $rowsLastWeek)) $rowsLastWeek[$d] = 0;
				$d = date("Y-m-d", strtotime("+1 day", strtotime($d)));
			}
		
			ksort($rowsLastWeek);
			$nofLastWeek = array_sum($rowsLastWeek);
			$fark = $nofLastWeek - $nofLast2Weeks;
			$rateColor = ($fark < 0) ? "red" : "green";
			$rateArrow = ($fark < 0) ? "down" : "up";
			$chartyValues = implode(",", $rowsLastWeek);
			$chartxValues = implode(",", array_map("FormatDateNumeric2Local", array_keys($rowsLastWeek)));

			if($nofLast2Weeks == 0 && $nofLastWeek > 0) $nofLast2Weeks = 1;
			$rate = $fark/$nofLast2Weeks;
		
			$queryData[] = array(
				"stat"			=> $statItem["title"],
				"nofLastWeek"	=> $nofLastWeek,
				"yValues"		=> $chartyValues,
				"xValues"		=> $chartxValues,
				"barColor"		=> $statItem["barColor"],
				"rate"			=> $rate,
				"rateColor"		=> $rateColor,
				"rateArrow"		=> $rateArrow,
				"statDate"		=> $simsDate,
				"schoolId"		=> $schoolid,
				"chartjsBorderColor" => $statItem["chartjsBorderColor"],
				"chartjsBgColor"=> $statItem["chartjsBgColor"]
			);
		}
		
		//save for the school
		$dbi->insertMulti($schoolStatsTable, $queryData);
	}
	
	echo "Stats have been calculated. [" . date("Y-m-d H:i:s") . "]" . PHP_EOL . PHP_EOL;
}

echo "Done." . PHP_EOL;
