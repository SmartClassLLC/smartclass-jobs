<?php

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

use Schoost\LMS\LMS;

echo PHP_EOL . "LMS Update Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

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

	$lms = new LMS();
	$lms->setConfigTable($moodleConfigTable);
	$lms->setUsersTable($usersTable);
	$lms->setStudentsTable($studentsTable);
	$lms->setPersonnelTable($personnelTable);
	$lms->setBatchesTable($batchesTable);
	$lms->setClassTeachersTable($classTeachersTable);

	/**
	 * Create User In LMS
	 */
	//GM kullanicilari icin tum subelerin insKeyleri gerekli
	
	echo "Starting to create users." . PHP_EOL;
	
	$insKeys = array();
	$moodleInsKey = $dbi->get($moodleConfigTable, null, "moodleInsKey");
	
	foreach($moodleInsKey as $moodleKey)
	{
		$insKeys[] = array('institutionkey' => $moodleKey["moodleInsKey"]);
	}

	$dbi->where("lmsUserId is NULL");
	$users = $dbi->get($usersTable);
	foreach($users as $user)
	{
		if(empty($user["ySubeKodu"]))
		{
			//GM kullanicilari icin create islemi
			$createLmsUserData = array(
				'username'      => strtolower($user["aid"]),
				'password'      => $user["pwdPlain"],
				'firstname'     => $user["name"],
				'lastname'      => $user["lastName"],
				'email'         => $user["email"],
				'auth'			=> 'lti'
			);
	
			$createLmsUser = $lms->lmsCreateManagerUsers($insKeys, $createLmsUserData);
		}
		else
		{
			//Okulda bulunan kullanicilar icin create islemi
			$lms->lmsSetSchoolInfo($user["ySubeKodu"]);
	
		    $createLmsUserData = array(
			    'username'      => strtolower($user["aid"]),
				'password'      => $user["pwdPlain"],
				'firstname'     => $user["name"],
				'lastname'      => $user["lastName"],
				'email'         => $user["email"],
				'auth'			=> 'lti'
			);
			
			//if user type is student
			if($user["userType"] == "8") $createLmsUserData["studentno"] = $lms->createStudentNo($user["id"]);
			
			$createLmsUser = $lms->lmsCreateUsers($createLmsUserData);
		}
	
		if(!empty($createLmsUser))
		{
		    $queryData = array('lmsUserId' => $createLmsUser[0]["id"]);
	
			$dbi->where("id", $user["id"]);
			$update = $dbi->update($usersTable, $queryData);
		}
	}
	echo "Finished creating users." . PHP_EOL;
	 
	/**
	 * Create Batches In LMS
	 */
	
	echo "Starting to create batches." . PHP_EOL;
	
	$dbi->join($schoolsTable. " s", "s.subeID=b.subeKodu", "LEFT");
	$dbi->where("b.lmsBatchId", "0");
	$batches = $dbi->get($batchesTable . " b", null, "b.sinifID, b.sinifAdi, b.subeKodu, s.subeAdi");
	
	foreach($batches as $batch)
	{
		//set school id
		$s = $lms->lmsSetSchoolInfo($batch["subeKodu"]);
		
		if($s == "noconfig") {
			echo $batch["subeAdi"]. ": No config for the school!" . PHP_EOL;
			continue;
		}
		
	    //moodle da sınıf isimleri her institution için unique olmasi gerekiyor bu şekilde bir çözüm buldum
	    $lmsBatches = array('name' => $batch["subeAdi"]." ".$batch["sinifAdi"]);
	
	    $createLmsBatch = $lms->lmsCreateBatches($lmsBatches);

		//update batch
		if($createLmsBatch)
		{
        	$queryData = array('lmsBatchId' => $createLmsBatch[0]["id"]);
        	$dbi->where("sinifID", $batch["sinifID"]);
        	$update = $dbi->update($batchesTable, $queryData);
        
        	if($update) echo $batch["subeAdi"]." ".$batch["sinifAdi"]. " has been updated!" . PHP_EOL;
        	else echo $batch["subeAdi"]." ".$batch["sinifAdi"]. " has not been updated!" . PHP_EOL;
		}
		else {
			echo $batch["subeAdi"]." ".$batch["sinifAdi"]. ": An unknown error occurred!" . PHP_EOL;
		}
	}
	echo "Finished creating batches." . PHP_EOL;
	
	
	/**
	 * Assign teachers as teacher user in LMS
	 * 
	 * Ogretmenlerin moodledaki kurslara ogretmen olarak atanmasi icin:
	 * 1. Kullanicilarinin moodle da olusmus olmasi gerekli
	 * 2. Atandiklari ders icin Kurs secim isleminin yapilmis olmasi gerekli
	 * 3. Atandiklari dersin bulundugu sinifin moodle da olmasi gerekli
	 */
	
	echo "Starting to assign teachers." . PHP_EOL;
	
	$dbi->join($personnelTable." p", "p.perID=ct.teacherId", "LEFT");
	$classTeachers = $dbi->get($classTeachersTable." ct", null, "p.tckimlikno, p.SubeKodu, p.adi_soyadi, ct.*");
	
	foreach($classTeachers as $teacher)
	{
	    $dbi->where("ySubeKodu", $teacher["SubeKodu"]);
	    $dbi->where("lmsUserId", "", "!=");
	    $dbi->where("aid", $teacher["tckimlikno"]);
	    $lmsUserId = $dbi->getValue($usersTable, "lmsUserId");
	
	    $dbi->join($batchesTable." b", "b.sinifID=cb.batchId", "LEFT");
	    $dbi->where("cb.schoolId", $teacher["SubeKodu"]);
	    $dbi->where("cb.classId", $teacher["classId"]);
	    $getBatches = $dbi->getOne($classBatchesTable." cb", "b.lmsBatchId");
	
	    $dbi->where("schoolId", $teacher["SubeKodu"]);
	    $dbi->where("classId", $teacher["classId"]);
	    $getLmsCourseId = $dbi->getOne($lmsCourseIdsTable);
	
		/* kullanicin moodle da olmasi, ilgili sinifin moodle da olmasi ve ilgili ders icin kurs secim
		islemlerinden birtanesi bile yapilmamis ise ogretmen kursa atanamaz */
	    if(empty($getLmsCourseId) || empty($lmsUserId) || $getBatches["lmsBatchId"] == "0") continue;
	
	    $lms->lmsSetSchoolInfo($teacher["SubeKodu"]);
	
	    $manuelEnrolData = array(
	        'userid'        => $lmsUserId,
	        'courseid'      => $getLmsCourseId["lmsCourseId"],
	        'batchid'       => $getBatches["lmsBatchId"],
	        'roleshortname' => "teacher"
	    );
	
	    $enrol = $lms->lmsManuelEnrol($manuelEnrolData);
	}
	
	echo "Finished assigning teachers." . PHP_EOL;
	
	echo "Starting to assign students." . PHP_EOL;
	
	$getStudents = $dbi->get($studentsTable, null, "TCKimlikNo, SubeKodu, SinifKodu");

	foreach($getStudents as $student)
	{
	    $dbi->where("subeKodu", $student["SubeKodu"]);
	    $dbi->where("sinifID", $student["SinifKodu"]);
	    $getLmsBatchId = $dbi->getValue($batchesTable, "lmsBatchId");

	    $dbi->where("aid", $student["TCKimlikNo"]);
	    $dbi->where("ySubeKodu", $student["SubeKodu"]);
	    $getLmsUserId = $dbi->getValue($usersTable, "lmsUserId");

	    if(empty($getLmsUserId) || $getLmsBatchId == "0") continue;

		$lms->lmsSetSchoolInfo($student["SubeKodu"]);

	    $user = array('userid' => $getLmsUserId);

	    $assignBatch = $lms->lmsAssignBatchmembers($user, $getLmsBatchId);

	}
	
	echo "Finished assigning students." . PHP_EOL;
}

echo "Done." . PHP_EOL;
