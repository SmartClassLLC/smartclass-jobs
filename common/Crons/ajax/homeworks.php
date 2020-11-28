<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

echo PHP_EOL . "Homeworks Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

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
	
	//get homeworks which have not been published yet
	$dbi->where("published", "off");
	$dbi->where("publishDate", $simsDateTime, "<=");
	$homeworks = $dbi->get($homeworksTable);
	foreach($homeworks as $homework)
	{
		$feedback = array();
		
		//set homework as published
		$dbi->where("Id", $homework["Id"])->update($homeworksTable, array("published" => "on"));
	
		//get school general settings
		$genelAyarlar = $dbi->where("subeKodu", $homework["subeKodu"])->where("setting", array("email_student_after_homework", "email_parent_after_homework"), "IN")->map("setting")->get($settingsTable);
 
		//teacher info
		$teacherUserInfo = fnUserId2UserInfo($homework["creator"], "name, lastName, email", true);
		$teacherName = $teacherUserInfo["name"]. " ". $teacherUserInfo["lastName"];
	
		//get students from homework
		$studentIds = explode(",", $homework["ogrIDs"]);
		
		//send email and notification
		if(!empty($studentIds) && !empty($homework["ogrIDs"]))
		{
			foreach($studentIds as $studentId)
			{
                //student aid
                $stdInfo = fnStdId2StdInfo($studentId, "Adi, IkinciAdi, Soyadi, TCKimlikNo");
                $stdName = fnStudentName($stdInfo["Adi"], $stdInfo["IkinciAdi"], $stdInfo["Soyadi"]);
                $stdEmail = fnUserId2UserInfo($stdInfo["TCKimlikNo"], "email");
                
                if(!empty($stdEmail) || !empty($stdInfo["TCKimlikNo"]))
                {
				    $messageStd = _NEW_HOMEWORK_ASSIGNED_YOU . "<br><br>";
	                $messageStd .= "<b>". _TEACHER . "</b> : " . $teacherName . "<br>";
	                $messageStd .= "<b>". _TITLE . "</b> : " . $homework["title"] . "<br>";
	                $messageStd .= "<b>". _DUE_DATE . "</b> : " . FormatDateNumeric2Local($homework["dueDate"], true, true) . "<br>";
	            
	            	if(!empty($stdInfo["TCKimlikNo"]))
	            	{
	                	//send notification to student
	                	sendInternalMessage(_NEW_HOMEWORK_ASSIGNED_YOU, $messageStd, $homework["subeKodu"], $homework["creator"], $stdInfo["TCKimlikNo"]);
	            	}
	                
	                if(!empty($stdEmail)  && $genelAyarlar["email_student_after_homework"])
	                {
	                	//send email to student
	                	$messageStdTemplated = externalMessageTemplate($stdInfo["TCKimlikNo"], $messageStd);
	                	sendEmail($stdEmail, _NEW_HOMEWORK_ASSIGNED_YOU, $messageStdTemplated, "", "", "", true, $teacherUserInfo["email"], $teacherName, $homework["creator"], $homework["subeKodu"]);
	        	
	        			echo "An email has been sent to the student (".$stdEmail.")" . PHP_EOL;
	                }
                }
                
                //parent aid
                $prtId = fnOgrID2ParentID($studentId);
                $prtAid = fnParentId2ParentInfo($prtId);
                $prtEmail = fnUserId2UserInfo($prtAid, "email");

				if(!empty($prtEmail) || !empty($prtAid))
				{
				    $messagePrt = _NEW_HOMEWORK_ASSIGNED_YOUR_STD . "<br><br>";
	                $messagePrt .= "<b>". _TEACHER . "</b> : " . $teacherName . "<br>";
	                $messagePrt .= "<b>". _STUDENT . "</b> : " . $stdName . "<br>";
	                $messagePrt .= "<b>". _TITLE . "</b> : " . $homework["title"] . "<br>";
	                $messagePrt .= "<b>". _DUE_DATE . "</b> : " . FormatDateNumeric2Local($homework["dueDate"], true, true) . "<br>";
	            
	            	if(!empty($prtAid))
	            	{
	                	//send notification to parent
	                	sendInternalMessage(_NEW_HOMEWORK_ASSIGNED_YOUR_STD, $messagePrt, $homework["subeKodu"], $homework["creator"], $prtAid);
	            	}
	            
	            	if(!empty($prtEmail) && $genelAyarlar["email_parent_after_homework"])
	            	{
	                	//send email to parent
	                	$messagePrtTemplated = externalMessageTemplate($prtAid, $messagePrt);
	                	sendEmail($prtEmail, _NEW_HOMEWORK_ASSIGNED_YOUR_STD, $messagePrtTemplated, "", "", "", true, $teacherUserInfo["email"], $teacherName, $homework["creator"], $homework["subeKodu"]);
	                
	                	echo "An email has been sent to the parent (".$prtEmail.")" . PHP_EOL;
	            	}
				}
			}
			
			//feedback message
			$message = "The homework '" . $homework["title"] . "' has been published." . PHP_EOL;
			
			//send to cli
			echo $message;
			
			//add to feedback
			$feedback[] = $message;
			
		}

		//send email to the teacher
		if(!empty($feedback))
		{
			$subject = "[SmartClass] Homework Publish";
		
			$message = PHP_EOL . "<br>";
			$message .= PHP_EOL . "<br>";
			$message .= implode(PHP_EOL . "<br>", $feedback);
			$message .= PHP_EOL . "<br>";
			$message .= PHP_EOL . "<br>";
			$message .= "-SmartClass Team";
			
			sendEmailAsSystem($teacherUserInfo["email"], $subject, $message);
		}
	}

	//get homeworks of which results have not been published yet
	$dbi->where("published", "on");
	$dbi->where("resultsPublished", "off");
	$dbi->where("resultsPublishDate", "0000-00-00 00:00:00", "!=");
	$dbi->where("resultsPublishDate", $simsDateTime, "<=");
	$homeworks = $dbi->get($homeworksTable);
	foreach($homeworks as $homework)
	{
		//set homework as published
		$dbi->where("Id", $homework["Id"])->update($homeworksTable, array("resultsPublished" => "on", "resultsPublishDate" => $simsDateTime));

		//teacher info
		$teacherUserInfo = fnUserId2UserInfo($homework["creator"], "name, lastName, email", true);

		//send to cli
		echo "The homework evaluation results '" . $homework["title"] . "' have been published." . PHP_EOL;
		
		$subject = "[SmartClass] Homework Results Publish";
	
		$message .= PHP_EOL . "<br>";
		$message .= PHP_EOL . "<br>";
		$message .= PHP_EOL . "The homework evaluation results '" . $homework["title"] . "' have been published." . PHP_EOL;
		$message .= PHP_EOL . "<br>";
		$message .= PHP_EOL . "<br>";
		$message .= "-SmartClass Team";
		
		sendEmailAsSystem($teacherUserInfo["email"], $subject, $message);
	}
	
}

echo "Done." . PHP_EOL;
