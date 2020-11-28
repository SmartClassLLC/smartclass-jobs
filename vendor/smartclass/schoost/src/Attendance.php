<?php

/*
 * This file is part of Schoost.
 *
 * (c) SmartClass, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Schoost;

class Attendance
{
    protected $termId = 0;
    protected $absenceSettings = array();

	function __construct()
	{
		global $dbi, $ySubeKodu;
		
		//school attendance settings
		$dbi->where("schoolId", $ySubeKodu);
		$this->absenceSettings = $dbi->map("hourlyTypeId")->get(_DEVAMSIZLIK_AYARLARI_);
	}
	
	/* function */
	function convertHourly2Daily()
	{
		global $dbi, $ySubeKodu, $simsDate, $simsDateTime;
		
		$absSettings = $this->absenceSettings;
		
		//delete previously converted ones first
		$dbi->where("schoolId", $ySubeKodu);
		$dbi->where("adder", "converter");
		$dbi->delete(_GUNLUK_DEVAMSIZLIK_);
		
		//get all students
		$dbi->where("KayitliMi", "1");
		$dbi->where("SubeKodu", $ySubeKodu);
		$students = $dbi->get(_OGRENCILER_, null, "ogrID");
		
		foreach($students as $student)
		{
			//query data for the student
			$queryData = array();
		
			//set total array for type ids
			$totalSum4TypeIds = array();
		
			//get student attendance
			$dbi->where("ogrKodu", $student["ogrID"]);
			$dbi->where("ders_turu_code", "1"); //only classes
			$dbi->where("subeKodu", $ySubeKodu);
			$dbi->groupBy("tarih");
			$absences = $dbi->get(_DEVAMSIZLIK_, null, "tarih, GROUP_CONCAT(saat) AS absHours, GROUP_CONCAT(devamsizlikKodu) AS absTypes");
		
			if(empty($absences)) continue;
		
			foreach($absences as $absence)
			{
				$absDate = $absence["tarih"];
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
							"attDate"		=> $absDate,
							"catCode"		=> $setting["dailyTypeCode"],
							"catId"			=> $setting["dailyTypeId"],
							"stdId"			=> $student["ogrID"],
							"nofDays"		=> $setting["nofDays"],
							"adder"			=> "converter",
							"addDateTime"	=> $simsDateTime,
							"schoolId"		=> $ySubeKodu
						);
					}
					else if($setting["sameDay"] == "sameday" && $setting["hourType"] == "exact" && $typeDailySum == $setting["nofHours"])
					{
						$queryData[] = array(
							"attDate"		=> $absDate,
							"catCode"		=> $setting["dailyTypeCode"],
							"catId"			=> $setting["dailyTypeId"],
							"stdId"			=> $student["ogrID"],
							"nofDays"		=> $setting["nofDays"],
							"adder"			=> "converter",
							"addDateTime"	=> $simsDateTime,
							"schoolId"		=> $ySubeKodu
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
									"attDate"		=> $absDate,
									"catCode"		=> $setting["dailyTypeCode"],
									"catId"			=> $setting["dailyTypeId"],
									"stdId"			=> $student["ogrID"],
									"nofDays"		=> $setting["nofDays"],
									"adder"			=> "converter",
									"addDateTime"	=> $simsDateTime,
									"schoolId"		=> $ySubeKodu
								);
		
								$totalSum4TypeIds[$typeId] = 0;
							}
							else if($setting["hourType"] == "exact" && $totalSum4TypeIds[$typeId] == $setting["nofHours"])
							{
								$queryData[] = array(
									"attDate"		=> $absDate,
									"catCode"		=> $setting["dailyTypeCode"],
									"catId"			=> $setting["dailyTypeId"],
									"stdId"			=> $student["ogrID"],
									"nofDays"		=> $setting["nofDays"],
									"adder"			=> "converter",
									"addDateTime"	=> $simsDateTime,
									"schoolId"		=> $ySubeKodu
								);
		
								$totalSum4TypeIds[$typeId] = 0;
							}
						}
					}
				}
			}
		
			if(!empty($queryData))
			{
				$dbi->insertMulti(_GUNLUK_DEVAMSIZLIK_, $queryData);
			}
		}
		
		return true;
	}

}