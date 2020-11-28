<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

echo PHP_EOL . "Exams Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

//get all instances in the server
$instances = $dbi->get("smartclass_common.instances");
foreach($instances as $instance)
{
	$i = 0;
	$insTitle = $instance["title"];
	$insPrefix = $instance["prefix"];
	$mainDB = $insPrefix . "_main";

	//include language files
    include __DIR__ . "/../../../settings/cli_languages.php";
	
	echo "Working on the instance: " . $insTitle . PHP_EOL;

	//get default season db
	$dbi->where("ontanimli", "on");
	$dbi->where("aktif", "on");
	$seasonDB = $dbi->getValue($mainDB . ".seasons", "veritabani");
	
	echo "Checking the season: " . $seasonDB . PHP_EOL;
	
	//include tables' file
    include __DIR__ . "/../../../settings/tables_cli.php";
	
	//get exams which have not been published yet
	$dbi->join($examsTable . " e", "e.s_id=p.sinavKodu", "LEFT");
	$dbi->where("p.aktif", "0");
	$dbi->where("p.yayinTarihi", $simsDate);
	$dbi->where("p.yayinSaati", $simsTimeWOs, "<=");
	$examPublishes = $dbi->get($examsPublishTable . " p", null, "p.yID, p.notify, p.sinavKodu, p.subeKodu, e.sinav_adi, e.tarih, e.ekleyen");
	
	foreach($examPublishes as $examPublish)
	{
		$feedback = array();
		
		//set exam as published
		$publish = $dbi->where("yID", $examPublish["yID"])->update($examsPublishTable, array("aktif" => "1"));
		
		if($publish)
		{
			if($examPublish["notify"] == "on")
			{
				//get school general settings
				//$genelAyarlar = $dbi->where("subeKodu", $examPublish["subeKodu"])->where("setting", array("email_student_exam_results"), "IN")->map("setting")->get($settingsTable);
	
				if($examPublish["subeKodu"] == 0) $countryCode = $dbi->getValue($configTable, "countryCode");
				else $countryCode = $dbi->where("subeID", $examPublish["subeKodu"])->getValue($schoolsTable, "countryCode");
				
				//locale code
				$localeCode = empty($countryCode) ? "tr" : strtolower($countryCode);
				
				//include localization file
				include_once "localization/" . $localeCode . ".php";
		 
				//exam results db
				$examsDB = $seasonDB . "_exams";
		 		$examDataBaseTableWDB = $examsDB. ".exam_results_". $examPublish["sinavKodu"];
		
				//$dbi->where("subeKodu", $examPublish["subeKodu"]);
		        $studentsInfo = $dbi->get($examDataBaseTableWDB, null, "ogrID, AdiSoyadi, TCKimlikNo, SubeKodu");
		
				foreach($studentsInfo as $studentInfo)
				{
		            //student aid
		            $stdName = $studentInfo["AdiSoyadi"];
		            $stdEmail = UserEMail($studentInfo["TCKimlikNo"]);
		
		            if($stdEmail || $studentInfo["TCKimlikNo"])
		            {
					    $messageStd = _NEW_EXAM_RESULTS_PUBLISHED . "<br><br>";
		                $messageStd .= "<b>". _EXAM . "</b> : " . $examPublish["sinav_adi"] . "<br>";
		                $messageStd .= "<b>". _DATE . "</b> : " . FormatDate2Local($examPublish["tarih"]) . "<br>";
		
		                //send notification to student
		                if($studentInfo["TCKimlikNo"]) sendInternalMessage(_NEW_EXAM_RESULTS, $messageStd, $studentInfo["SubeKodu"], "system", $studentInfo["TCKimlikNo"]);
		
		                //send email to student
		                /*
		                if($stdEmail && $genelAyarlar["email_student_exam_results"] == "on")
		                {
		                    $messageStdTemplated = externalMessageTemplate($studentInfo["TCKimlikNo"], $messageStd);
		                    saveEmail2Send($umail, $uname, $stdEmail, _NEW_EXAM_RESULTS, $messageStdTemplated);
		                }
		                */
		            }
		
		            //parent aid
		            $prtId = fnOgrID2ParentID($studentInfo["ogrID"]);
		            $prtAid = fnParentId2ParentInfo($prtId);
		            $prtEmail = UserEMail($prtAid);
		
		            if($prtEmail || $prtAid)
		            {
					    $messagePrt = _NEW_EXAM_RESULTS_PUBLISHED_FOR_STD . "<br><br>";
		                $messagePrt .= "<b>". _STUDENT . "</b> : " . $studentInfo["AdiSoyadi"] . "<br>";
		                $messagePrt .= "<b>". _EXAM . "</b> : " . $examPublish["sinav_adi"] . "<br>";
		                $messagePrt .= "<b>". _DATE . "</b> : " . FormatDate2Local($examPublish["tarih"]) . "<br>";
		
		                //send notification to parent
		                if($prtAid) sendInternalMessage(_NEW_EXAM_RESULTS, $messagePrt, $studentInfo["SubeKodu"], "system", $prtAid);
		
		                //send email to parent
		                /*
		                if($prtEmail && $genelAyarlar["email_student_exam_results"] == "on")
		                {
		                    $messagePrtTemplated = externalMessageTemplate($prtAid, $messagePrt);
		                    saveEmail2Send($umail, $uname, $prtEmail, _NEW_EXAM_RESULTS, $messagePrtTemplated);
		                }
		                */
		            }
				}
			}
			
			//feedback message
			$message = "The exam '" . $examPublish["sinav_adi"] . "' has been published." . PHP_EOL;
			
			//send to cli
			echo $message;
	
			//add to feedback
			$feedback[] = $message;
		}
		
		//send email to the teacher
		if(!empty($feedback))
		{
			//teacher info
			$examAdderUserInfo = fnUserId2UserInfo($examPublish["ekleyen"], "name, lastName, email", true);
			$examAdderName = $examAdderUserInfo["name"]. " ". $examAdderUserInfo["lastName"];
			
			$subject = "[SmartClass] Exam Results Published";
		
			$message = PHP_EOL . "<br>";
			$message .= PHP_EOL . "<br>";
			$message .= implode(PHP_EOL . "<br>", $feedback);
			$message .= PHP_EOL . "<br>";
			$message .= PHP_EOL . "<br>";
			$message .= "-SmartClass Team";
			
			sendEmailAsSystem($examAdderUserInfo["email"], $subject, $message);
		}
	}
}

echo "Done." . PHP_EOL;
