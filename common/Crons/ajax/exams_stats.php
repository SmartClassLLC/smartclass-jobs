<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

echo PHP_EOL . "Exams Statistics Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

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

	//get exams to be handled
	$dbi->where("done", "off");
	$exams = $dbi->get($examsCronsTable, null, "DISTINCT examID, schoolID, hqExam");

	foreach($exams as $exam)
	{
		$examID = $exam["examID"];
		$schoolID = $exam["schoolID"];
		$hqExam = $exam["hqExam"] == "on" ? true : false;
		$feedback = array();

		echo "Handling examID: " . $examID . " and schoolID: " . $schoolID . PHP_EOL;
		
		//calculate course averages in terms of net scores
		$dbi->where("examID", $examID);
		$dbi->orderBy("dOrder", "ASC");
		$examSubjects = $dbi->get($examsSubjectsTable, null, "id, dersKodu");
		
		$numberOfSubjects = empty($examSubjects) ? 0 : sizeof($examSubjects);
		
		foreach ($examSubjects as $examSubject)
		{
			echo "\tHandling subjectID: " . $examSubject["dersKodu"] . PHP_EOL;

			/******************************/
			/*** batch subject averages ***/
			/******************************/
	    	//delete current ones if exist
			$dbi->where("sinavKodu", $examID);
			$dbi->where("dersKodu", $examSubject["dersKodu"]);
			$dbi->delete($examsBatchAveragesTable);

			//calculate new ones
			$dbi->where("sinavKodu", $examID);
			$dbi->where("dersKodu", $examSubject["dersKodu"]);
			$dbi->where("sinifKodu", "0", "!=");
			$dbi->groupBy("sinifKodu");
			$batchAverages = $dbi->get($examsResultsTable, null, "sinifKodu, AVG(netSayisi) AS sinif_ortalamasi, subeKodu");
	
			if(!empty($batchAverages))
			{
				$multiQueryData = array();
			    foreach ($batchAverages as $batchAverage)
			    {
					//save averages
					$multiQueryData[] = array(
						"sinifOrtalamasi"	=> $batchAverage["sinif_ortalamasi"],
						"dersKodu"			=> $examSubject["dersKodu"],
						"sinifKodu"			=> $batchAverage["sinifKodu"],
						"sinavKodu"			=> $examID,
						"subeKodu"			=> $batchAverage["subeKodu"]
					);
				}
	
				$dbi->insertMulti($examsBatchAveragesTable, $multiQueryData);
	
				echo "\t\tBatch averages have been calculated for the subject of " . $examSubject["dersKodu"] . " in the exam of " . $examID . "." . PHP_EOL;
			}

			/******************************/
			/*** school subject average ***/
			/******************************/
			//delete current ones if exist
			$dbi->where("sinavKodu", $examID);
			$dbi->where("dersKodu", $examSubject["dersKodu"]);
			$dbi->delete($examsSchoolAveragesTable);
			
			//calcualte new ones
			$dbi->where("sinavKodu", $examID);
			$dbi->where("dersKodu", $examSubject["dersKodu"]);
			$dbi->groupBy("subeKodu");
			$subjectSchoolAverages = $dbi->get($examsResultsTable, null, "subeKodu, AVG(netSayisi) AS okul_ortalamasi");
	
			if(!empty($subjectSchoolAverages))
			{
				$multiQueryData = array();
			    foreach ($subjectSchoolAverages as $subjectSchoolAverage)
			    {
					//save subject average for the school
					$multiQueryData[] = array(
						"subeOrtalamasi"	=> $subjectSchoolAverage["okul_ortalamasi"],
						"dersKodu"			=> $examSubject["dersKodu"],
						"sinavKodu"			=> $examID,
						"subeKodu"			=> $subjectSchoolAverage["subeKodu"]
					);
			    }
				
				$dbi->insertMulti($examsSchoolAveragesTable, $multiQueryData);
			
				echo "\t\tSchool averages have been calculated for the subject of " . $examSubject["dersKodu"] . " in the exam of " . $examID . "." . PHP_EOL;
			}
			
			/***********************/
			/*** general average ***/
			/***********************/
			//delete current one if exists
			$dbi->where("sinavKodu", $examID);
			$dbi->where("dersKodu", $examSubject["dersKodu"]);
			$dbi->delete($examsGenAveragesTable);
	
			$dbi->where("sinavKodu", $examID);
			$dbi->where("dersKodu", $examSubject["dersKodu"]);
			$subjectGeneralAverage = $dbi->getValue($examsResultsTable, "AVG(netSayisi)");
	
			//save subject average for all
			$queryData = array(
				"genelOrtalama"	=> $subjectGeneralAverage,
				"dersKodu"		=> $examSubject["dersKodu"],
				"sinavKodu"		=> $examID
			);
			
			$dbi->insert($examsGenAveragesTable, $queryData);

			echo "\t\tGeneral average " . $subjectGeneralAverage . " have been calculated for the subject of " . $examSubject["dersKodu"] . " in the exam of " . $examID . "." . PHP_EOL;
			
			/**********************************************************/
			/*** student sorting in the batch based on exam results ***/
			/**********************************************************/
			$dbi->where("sinavKodu", $examID);
			$dbi->where("sinifKodu", "0", "!=");
			$dbi->where("sinifKodu", NULL, "IS NOT");
			$studentExamResults = $dbi->get($examsResultsTable, null, "DISTINCT sinifKodu");
		
			foreach ($studentExamResults as $studentExamResult)
			{
				//batch rank
				$batchRank = 0;
		
				$dbi->where("sinavKodu", $examID);
				$dbi->where("sinifKodu", $studentExamResult["sinifKodu"]);
				$dbi->where("dersKodu", $examSubject["dersKodu"]);
				$dbi->orderBy("netSayisi", "DESC");
				$results = $dbi->get($examsResultsTable, null, "id");
		
				foreach ($results as $result)
				{
					$batchRank++;
		
					//save the rank of the student in the batch
					$dbi->where("id", $result["id"]);
					$dbi->update($examsResultsTable, array("sinifSirasi" => $batchRank));
				}
			}
		
			echo "\t\tStudents ranks on batches have been calculated for the subject of " . $examSubject["dersKodu"] . " in the exam of " . $examID . "." . PHP_EOL;
			
			/***********************************************************/
			/*** student sorting in the school based on exam results ***/
			/***********************************************************/
			$dbi->where("sinavKodu", $examID);
			$studentExamResults = $dbi->get($examsResultsTable, null, "DISTINCT subeKodu");

			foreach ($studentExamResults as $studentExamResult)
			{
				$schoolRank = 0;
			
				$dbi->where("subeKodu", $studentExamResult["subeKodu"]);
				$dbi->where("sinavKodu", $examID);
				$dbi->where("dersKodu", $examSubject["dersKodu"]);
				$dbi->orderBy("netSayisi", "DESC");
				$results = $dbi->get($examsResultsTable, null, "id");
			
				foreach ($results as $result)
				{
					$schoolRank++;
			
					//save the rank of the student in the school
					$dbi->where("id", $result["id"]);
					$dbi->update($examsResultsTable, array("okulSirasi" => $schoolRank));
				}
			
			}
			
			echo "\t\tStudents ranks on schools have been calculated for the subject of " . $examSubject["dersKodu"] . " in the exam of " . $examID . "." . PHP_EOL;
			
			/********************************************************/
			/*** student sorting in general based on exam results ***/
			/********************************************************/
			$generalRank = 0;
	
			$dbi->where("sinavKodu", $examID);
			$dbi->where("dersKodu", $examSubject["dersKodu"]);
			$dbi->orderBy("netSayisi", "DESC");
			$results = $dbi->get($examsResultsTable, null, "id, ogrID");
	
			foreach ($results as $result)
			{
				$generalRank++;
	
				//save the rank of the student in general
				$dbi->where("id", $result["id"]);
				$dbi->update($examsResultsTable, array("genelSira" => $generalRank));
	
				//save item data for item analysis
				$dbi->where("ogrID", $result["ogrID"]);
				$dbi->where("dersKodu", $examSubject["dersKodu"]);
				$dbi->where("sinavKodu", $examID);
				$dbi->update($examsItemAnalysisTable, array("genelSira" => $generalRank));
			}
			
			echo "\t\tStudents overall ranks have been calculated for the subject of " . $examSubject["dersKodu"] . " in the exam of " . $examID . "." . PHP_EOL;
		}
		
		//set as calculated on db
		$dbi->where("examID", $examID);
		$dbi->where("schoolID", $schoolID);
		$dbi->update($examsCronsTable, array("done" => "on", "completeDate" => $simsDateTime));
		
		echo "Handling completed." . PHP_EOL . PHP_EOL;
	}
}

echo "Done." . PHP_EOL;
