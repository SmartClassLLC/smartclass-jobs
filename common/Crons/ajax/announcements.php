<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

echo PHP_EOL . "Announcements Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

//get all instances in the server
$instances = $dbi->get("smartclass_common.instances");
foreach($instances as $instance)
{
	$insTitle = $instance["title"];
	$insPrefix = $instance["prefix"];
	$mainDB = $insPrefix . "_main";
	
	echo "Working on the instance: " . $insTitle . PHP_EOL;
	
	//get default season db
	$dbi->where("ontanimli", "on");
	$dbi->where("aktif", "on");
	$seasonDB = $dbi->getValue($mainDB . ".seasons", "veritabani");
	
	echo "Checking the season: " . $seasonDB . PHP_EOL;

	$feedback = array();
	
	//include tables' file
    include __DIR__ . "/../../../settings/tables_cli.php";

	//get announcements those are not set for continuous publish
	$dbi->join($schoolsTable. " s", "s.subeID=a.SubeKodu", "LEFT");
	$dbi->where("a.YayinSureli", "1");
	$announcements = $dbi->get($annsTable. " a", null, "a.*, s.timeZone");
	foreach($announcements as $announcement)
	{
		if(!empty($announcement["timeZone"]))
		{
			//set timezone
			date_default_timezone_set($timeZone);
			
			//general date variables
			$simsDate = date("Y-m-d");
			$simsTime = date("H:i:s");
			$simsTimeWOs = date("H:i");
			$simsDateTime = date("Y-m-d H:i:s");
		}
		
		//if publish date is over due and still not active then activate it
		if($announcement["Aktif"] == "0" && $announcement["YayinTarihi"] < $simsDateTime && $announcement["YayinBitisTarihi"] > $simsDateTime)
		{
			//get school name
			$schoolName = $dbi->where("subeID", $announcement["SubeKodu"])->getValue($schoolsTable, "subeAdi");

			//update
			$update = $dbi->where("Id", $announcement["Id"])->update($annsTable, array("Aktif" => "1"));
			
			//feedback message
			$message = "The announcement '" . $announcement["Baslik"] . "' for " . $schoolName . " has been published." . PHP_EOL;
			
			//send to cli
			echo $message;
			
			//add to feedback
			if($update) $feedback[] = $message;
		}

		//if unpublish date is over due and still active then deactivate it
		if($announcement["Aktif"] == "1" && $announcement["YayinBitisTarihi"] < $simsDateTime)
		{
			//get school name
			$schoolName = $dbi->where("subeID", $announcement["SubeKodu"])->getValue($schoolsTable, "subeAdi");

			//update
			$update = $dbi->where("Id", $announcement["Id"])->update($annsTable, array("Aktif" => "0"));
			
			//feedback message
			$message = "The announcement '" . $announcement["Baslik"] . "' for " . $schoolName . " has been unpublished." . PHP_EOL;
			
			//send to cli
			echo $message;
			
			//add to feedback
			if($update) $feedback[] = $message;
		}
	}

	if(!empty($feedback))
	{
		$subject = "[SmartClass] Announcement Update";
	
		if(sizeof($feedback) == 1) $message = "The announcement below has been updated";
		else $message = "The announcements below have been updated";
		
		$message .= PHP_EOL . "<br>";
		$message .= PHP_EOL . "<br>";
		$message .= implode(PHP_EOL . "<br>", $feedback);
		$message .= PHP_EOL . "<br>";
		$message .= PHP_EOL . "<br>";
		$message .= "-SmartClass Team";
		
		//let admin to know about the updates
		$adminEmail = $dbi->getValue($configTable, "adminmail");
		
		//get school IT managers email
		$dbi->where("active", "1");
		$dbi->where("userType", "18");
		$managerEmails = $dbi->getValue($usersTable, "GROUP_CONCAT(DISTINCT aid SEPARATOR ',')");
	
		sendEmailAsSystem($managerEmails, $subject, $message, $adminEmail);
	}
}

echo "Done." . PHP_EOL;
