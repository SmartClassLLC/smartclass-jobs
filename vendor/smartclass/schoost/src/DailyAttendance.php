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

class DailyAttendance {
    
	/* function */
	function getDailyAttendance($filters = array())
	{
        global $dbi, $ySubeKodu, $simsDate;

		
        //check current season
        
        if(!empty($filters["term"])) $dbi->where("Id", $filters["term"], "IN");
        else
        {
        	$dbi->where("startDate", $simsDate, "<=");
        	$dbi->where("endDate", $simsDate, ">=");
        }
        
        $seasonInfo = $dbi->getOne(_GRADING_TERMS_, "Id, title, startDate, endDate");
        
        
        if(!empty($filters["batch"]))
        {
            $dbi->where("SinifKodu", $filters["batch"], "IN");
            $dbi->where("SubeKodu", $ySubeKodu);
            $dbi->where("KayitliMi", "1");
            $ogrenciler = $dbi->getValue(_OGRENCILER_, "ogrID", null);
            
            $dbi->where("stdId", $ogrenciler, "IN");
        }
        
        if(!empty($filters["student"])) $dbi->where("stdId", $filters["student"], "IN");
        
        if(!empty($filters["dates"]))
        {
            $firstDate = $filters["dates"][0] . " 00.00.00";
            $secondDate = $filters["dates"][1] . " 23.59.59";
            
        	$dbi->where("attDate", $firstDate, ">=");
        	$dbi->where("attDate", $secondDate, "<=");
        }
        
        $dbi->where("attDate", $seasonInfo["startDate"], ">=");
        $dbi->where("attDate", $seasonInfo["endDate"], "<=");
        $dbi->where("schoolId", $ySubeKodu);
        $dailyAbsences = $dbi->get(_GUNLUK_DEVAMSIZLIK_, null, "id, stdId, attDate, catCode, catId, nofDays");
        
        foreach($dailyAbsences as $dailyAbsence)
		{
		    $stdInfo[] = fnStdId2StdInfo($dailyAbsence["stdId"], "ogrID, ogrenciNo, Adi, IkinciAdi, Soyadi, SinifKodu");
            
            //term attendance
            //$termAttendance = totalNumberOfAbsence($seasonInfo["Id"], $dailyAbsence["stdId"]);

            //total attendance
            //$totalAttendance = totalNumberOfAbsence(0, $dailyAbsence["stdId"]);

			//get setting info
			$stype = substr($dailyAbsence["catCode"], 0, 1);

			if($stype == "t")
			{
				$dbi->where("id", $dailyAbsence["catId"]);
				$dbi->orderBy("id", "asc");
				$settingInfo = $dbi->getOne(_DEVAMSIZLIK_KATEGORILERI_, "category as title");
			}
			else if($stype == "r")
			{
				$dbi->where("sebebID", $dailyAbsence["catId"]);
				$dbi->orderBy("sebebID", "asc");
				$settingInfo = $dbi->getOne(_DEVAMSIZLIK_TURLERI_, "CONCAT(sebebAdi, ' [', sebebSymbol, ']') as title");
			}
			
			$stdBatch[] = array('stdId' => $dailyAbsence["stdId"], 'sinifKodu'  => SinifAdi(fnStdId2StdInfo($dailyAbsence["stdId"], "SinifKodu")));
			$totalAttendance[] = array('stdId' => $dailyAbsence["stdId"], 'totals' => totalNumberOfAbsence(0, $dailyAbsence["stdId"]));
			$termAttendance[] = array('stdId' => $dailyAbsence["stdId"], 'totals' => totalNumberOfAbsence($seasonInfo["Id"], $dailyAbsence["stdId"]));
			$settings[] = array('stdId' => $dailyAbsence["stdId"], 'title' => $settingInfo["title"]);
		}
        
        $respData = array(
            'dailyAbsences' => $dailyAbsences,
            'seasonInfo'    => $seasonInfo,
            'studentInfo'   => $stdInfo,
            'stdBatch'      => $stdBatch,
            'totalAttendance' => $totalAttendance,
            'termTotals'    => $termAttendance,
            'settingInfo'   => $settings
        );
        
		return $respData;
	}

}
