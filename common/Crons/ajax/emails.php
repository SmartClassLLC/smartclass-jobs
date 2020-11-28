<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

echo PHP_EOL . "Sending Email Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

//get all instances in the server
$instances = $dbi->get("smartclass_common.instances");
foreach($instances as $instance)
{
	$i = 0;
	$insTitle = $instance["title"];
	$insPrefix = $instance["prefix"];
	$mainDB = $insPrefix . "_main";
	
	echo "Working on the instance: " . $insTitle . PHP_EOL;
	
	/*
	//get default season db
	$dbi->where("ontanimli", "on"); 
	$dbi->where("aktif", "on");
	$seasonDB = $dbi->getValue($mainDB . ".seasons", "veritabani");
	*/
	
	//get default season db
	$dbi->where("aktif", "on");
	$seasonDBs = $dbi->get($mainDB . ".seasons", null, "veritabani");
	
	foreach($seasonDBs as $row)
	{
		//echo $dbi->lastq();
		
		$seasonDB = $row["veritabani"];
		
		echo "Checking the season: " . $seasonDB . PHP_EOL;
		
		//include tables' file
		include __DIR__ . "/../../../settings/tables_cli.php";
		
		//$emailsTable = $seasonDB.".emails_tosend";
		
		//delete emails that have more than 15 tries
		$dbi->where("isSent", "0");
		$dbi->where("nofFails", "15", ">=");
		$dbi->delete($emailsTable);
		
		//get emails which have not been sent yet
		$dbi->where("isSent", "0");
		$emails = $dbi->get($emailsTable, 500);
		foreach($emails as $email)
		{
			$emailAttachments = unserialize($email["emailAttachment"]);
			
			$sendEmail = sendEmail($email["emailTo"], $email["emailSubject"], $email["emailContent"], $email["emailCC"], $email["emailBCC"], $emailAttachments, true, $email["emailFrom"], $email["emailFromName"], $email["emailSender"], $email["subeKodu"]);
			
			if($sendEmail)
			{
				$i++;
				
				//update the email row
				$dbi->where("id", $email["id"]);
				$dbi->delete($emailsTable);
			}
			else
			{
				//update the email row
				$dbi->where("id", $email["id"]);
				$dbi->update($emailsTable, array("nofFails" => $dbi->inc(1)));
			}
		}
	}
	//echo $i ." emails have been sent." . PHP_EOL . PHP_EOL;
	echo $i ." emails have been sent. [" . date("Y-m-d H:i:s") . "]" . PHP_EOL;
}

echo "Done." . PHP_EOL;
