<?php

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

//$singleDatabases = ["smartclass_common", "smartclass_common_ch", "smartclass_common_cl", "smartclass_common_my", "smartclass_common_th", "smartclass_common_tr", "smartclass_common_us"];
$singleDatabases = ["smartclass_common"];
foreach($singleDatabases as $singleDatabase) {
	$message = array();
	$messageText = "";

	//scan season folder
	$dir = __DIR__ . '/../../../dbupdates/' . $singleDatabase;
	$files = scandir($dir);
	natsort($files);

	//get db records
	$recentupdates = $dbi->where("type", $singleDatabase)->where("rerun", "0")->getValue("smartclass_common.dbupdates", "filename", null);

	foreach($files as $file) {
		if($file == "." || $file == "..") continue;
		
		if(!empty($recentupdates) && in_array($file, $recentupdates)) continue;
		
		//get file
		$flines = file($dir . "/" . $file);
		if(empty($flines)) continue;

		$message = array();
		$messageText = "";
		
		if($dbi->selectdb($singleDatabase)) {
			echo $singleDatabase . " ...";
			
			foreach ($flines as $fline) {
				if(empty($fline)) continue;
				
				$run = $dbi->rawQuery($fline);
				
				if($run) $message[] = "[" . $singleDatabase . "] Success";
				else $message[] = "[" . $singleDatabase . "] " . $dbi->getLastError();
			}
			
			if(!empty($message)) $messageText = implode("\n", $message);
	
			//query data
			$queryData = array(
				"filename"	=> $file,
				"type"		=> $singleDatabase,
				"query"		=> addslashes(implode("", $flines)),
				"run_date"	=> $simsDateTime,
				"message"	=> $messageText
			);
			
			$dbi->insert("smartclass_common.dbupdates", $queryData);
		
			echo "done\n";
		}
	}
}

//get all instances in the server
$instances = $dbi->getValue("smartclass_common.instances", "prefix", null);
$instances[] = "base";

$commonDatabases = ["main", "seasons"];
foreach($commonDatabases as $commonDatabase) {
	//scan season folder
	$dir = __DIR__ . '/../../../dbupdates/' . $commonDatabase;
	$files = scandir($dir);
	natsort($files);

	//get db records
	$recentupdates = $dbi->where("type", $commonDatabase)->where("rerun", "0")->getValue("smartclass_common.dbupdates", "filename", null);

	foreach($files as $file) {
		if($file == "." || $file == "..") continue;
		
		if(!empty($recentupdates) && in_array($file, $recentupdates)) continue;
		
		//get file
		$flines = file($dir . "/" . $file);
		if(empty($flines)) continue;

		$message = array();
		$messageText = "";

		foreach($instances as $instance) {
			$mainDB = $instance . "_main";
			
			if($commonDatabase == "main") {
				if($dbi->selectdb($mainDB)) {
					echo $mainDB . " ...";
					
					foreach ($flines as $fline) {
						if(empty($fline)) continue;
						
						$run = $dbi->rawQuery($fline);
						
						if($run) $message[] = "[" . $mainDB . "] Success";
						else $message[] = "[" . $mainDB . "] " . $dbi->getLastError();
					}
					
					echo "done\n";
				}
			} else {
				if($instance == "base") {
					if($dbi->selectdb("base_seasons")) {
						echo "base_seasons...";
						
						foreach ($flines as $fline) {
							if(empty($fline)) continue;
							
							$run = $dbi->rawQuery($fline);
							
							if($run) $message[] = "[base_seasons] Success";
							else $message[] = "[base_seasons] " . $dbi->getLastError();
						}
						
						echo "done\n";
					}
				} else {
					//get season dbs
					$seasonDBs = $dbi->getValue($mainDB . ".seasons", "veritabani", null);

					foreach($seasonDBs as $seasonDB) {
						if($dbi->selectdb($seasonDB)) {
							echo $seasonDB . " ...";
							
							foreach ($flines as $fline) {
								if(empty($fline)) continue;
								
								$run = $dbi->rawQuery($fline);
								
								if($run) $message[] = "[" . $seasonDB . "] Success";
								else $message[] = "[" . $seasonDB . "] " . $dbi->getLastError();
							}
							
							echo "done\n";
						}
					}
				}
			}
		}
		
		if(!empty($message)) $messageText = implode("\n", $message);

		//query data
		$queryData = array(
			"filename"	=> $file,
			"type"		=> $commonDatabase,
			"query"		=> addslashes(implode("", $flines)),
			"run_date"	=> $simsDateTime,
			"message"	=> $messageText
		);
		
		$dbi->insert("smartclass_common.dbupdates", $queryData);
	}
}

//get smartclass_common content updates
//if(!in_array("dev", $instances)) {
//	echo "Updating smartclass_common.\n";
//
//	//include backup file from cdn
//	$backup_file = "https://cdn.smartclass.tech/dbupdates/content/sims_last_common.sql";
//
//	$lines = file($backup_file) or die('Could not read the file!');
//
//	$templine = "";
//
//	if(!empty($lines)) {
//		if($dbi->selectdb("smartclass_common")) {
//			$r = 0;
//			foreach($lines as $line) {
//				// Skip it if it's a comment
//				if (substr($line, 0, 2) == '--' || substr($line, 0, 2) == '/*' || substr($line, 0, 4) == 'LOCK' || substr($line, 0, 6) == 'UNLOCK' || $line == '') continue;
//
//				// Add this line to the current segment
//				$line = str_replace("\n", " ", $line);
//				$templine .= $line;
//
//				// If it has a semicolon at the end, it's the end of the query
//				if (substr(trim($line), -1, 1) == ';') {
//				    // Perform the query
//				    $dbi->rawQueryValue($templine);
//
//				    // Reset temp variable to empty
//				    $templine = "";
//
//					$r++;
//				}
//			}
//
//			echo $r . " queries have been successfully processed.\n";
//		}
//	} else {
//		echo "Backup file is empty!";
//	}
//}

die("Update completed!");
