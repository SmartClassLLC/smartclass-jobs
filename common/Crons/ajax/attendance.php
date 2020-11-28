<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

echo PHP_EOL . "Attendance Conversion Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

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

	//get schools
	$dbi->where("aktif", "1");
	$schoolIds = $dbi->getValue($mainDB . ".schools", "subeID", null);
	foreach($schoolIds as $schoolId)
	{
		//school attendance settings
		$dbi->where("schoolId", $schoolId);
		$absSettings = $dbi->map("hourlyTypeId")->get($absenceSettingsTable);
		
		if(empty($absSettings)) continue;
		
		//delete previously converted ones first
		$dbi->where("schoolId", $schoolId);
		$dbi->where("attDate", $simsDate);
		$dbi->where("adder", "converter");
		$dbi->delete($dailyAttendanceTable);
		
		//get all students
		$dbi->where("KayitliMi", "1");
		$dbi->where("SubeKodu", $schoolId);
		$students = $dbi->get($studentsTable, null, "ogrID");
		
		foreach($students as $student)
		{
			//check if there is any record for the same day
			$dbi->where("stdId", $student["ogrID"]);
			$dbi->where("attDate", $simsDate);
			$dbi->where("schoolId", $schoolId);
			$varmi = $dbi->getOne($dailyAttendanceTable, "id");
			
			//if there is any record for the day then continue
			if(!empty($varmi)) continue;
			
			//query data for the student
			$queryData = array();
		
			//set total array for type ids
			$totalSum4TypeIds = array();
		
			//get student attendance
			$dbi->where("ogrKodu", $student["ogrID"]);
			$dbi->where("ders_turu_code", "1"); //only classes
			$dbi->where("tarih", $simsDate);
			$dbi->where("subeKodu", $schoolId);
			$absence = $dbi->getOne($hourlyAttendanceTable, "GROUP_CONCAT(saat) AS absHours, GROUP_CONCAT(devamsizlikKodu) AS absTypes");
		
			if(empty($absence)) continue;
		
			$absHours = explode(",", $absence["absHours"]);
			$absTypes = explode(",", $absence["absTypes"]);
			$absTypeDailySums = array_count_values($absTypes);
	
			foreach($absTypeDailySums as $typeId => $typeDailySum)
			{
				$setting = $absSettings[$typeId];
	
				if(empty($setting)) continue;
	
				//check if the same day
				if($setting["sameDay"] == "sameday" && $setting["hourType"] == "atleast" && $typeDailySum >= $setting["nofHours"])
				{
					$queryData[] = array(
						"attDate"		=> $simsDate,
						"catCode"		=> $setting["dailyTypeCode"],
						"catId"			=> $setting["dailyTypeId"],
						"stdId"			=> $student["ogrID"],
						"nofDays"		=> $setting["nofDays"],
						"adder"			=> "converter",
						"addDateTime"	=> $simsDateTime,
						"schoolId"		=> $schoolId
					);
				}
				else if($setting["sameDay"] == "sameday" && $setting["hourType"] == "exact" && $typeDailySum == $setting["nofHours"])
				{
					$queryData[] = array(
						"attDate"		=> $simsDate,
						"catCode"		=> $setting["dailyTypeCode"],
						"catId"			=> $setting["dailyTypeId"],
						"stdId"			=> $student["ogrID"],
						"nofDays"		=> $setting["nofDays"],
						"adder"			=> "converter",
						"addDateTime"	=> $simsDateTime,
						"schoolId"		=> $schoolId
					);
				}
				else if($setting["sameDay"] == "total")
				{
					for($k = 1; $k <= $typeDailySum; $k++)
					{
						$totalSum4TypeIds[$typeId] = empty($totalSum4TypeIds[$typeId]) ? 1 : $totalSum4TypeIds[$typeId] + 1;
	
						if($setting["hourType"] == "atleast" && $totalSum4TypeIds[$typeId] >= $setting["nofHours"])
						{
							$queryData[] = array(
								"attDate"		=> $simsDate,
								"catCode"		=> $setting["dailyTypeCode"],
								"catId"			=> $setting["dailyTypeId"],
								"stdId"			=> $student["ogrID"],
								"nofDays"		=> $setting["nofDays"],
								"adder"			=> "converter",
								"addDateTime"	=> $simsDateTime,
								"schoolId"		=> $schoolId
							);
	
							$totalSum4TypeIds[$typeId] = 0;
						}
						else if($setting["hourType"] == "exact" && $totalSum4TypeIds[$typeId] == $setting["nofHours"])
						{
							$queryData[] = array(
								"attDate"		=> $simsDate,
								"catCode"		=> $setting["dailyTypeCode"],
								"catId"			=> $setting["dailyTypeId"],
								"stdId"			=> $student["ogrID"],
								"nofDays"		=> $setting["nofDays"],
								"adder"			=> "converter",
								"addDateTime"	=> $simsDateTime,
								"schoolId"		=> $schoolId
							);
	
							$totalSum4TypeIds[$typeId] = 0;
						}
					}
				}
			}
		
			if(!empty($queryData)) $dbi->insertMulti($dailyAttendanceTable, $queryData);
		}
	}
	
	if($convert) echo "Attendance conversion has been done." . PHP_EOL;
}

echo "Done." . PHP_EOL;
