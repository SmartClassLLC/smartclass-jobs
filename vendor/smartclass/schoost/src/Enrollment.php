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

class Enrollment {
    
    protected $stdId = 0;
    private $enrollmentSteps = array();

	/* function */
	function setStudentId($Id)
	{
		//set season Id
		$this->stdId = $Id;
	}

	/* function */
	function getEnrollmentSteps()
	{
		global $dbi, $ySubeKodu;
		
		//if there is no record then create it
		$dbi->where("subeKodu", $ySubeKodu);
		$this->enrollmentSteps = $dbi->getOne(_ENROLLMENT_STEPS_SETTINGS_);
		
		if(empty($enrollmentSteps))
		{
			$dbi->insert(_ENROLLMENT_STEPS_SETTINGS_, array("subeKodu" => $ySubeKodu));
		
			//get steps
			$dbi->where("subeKodu", $ySubeKodu);
			$this->enrollmentSteps = $dbi->getOne(_ENROLLMENT_STEPS_SETTINGS_);
		}
		
		return $this->enrollmentSteps;
	}

	/* function */
	function setTuitionFee()
	{
		global $dbi, $ySubeKodu, $previousSeason, $previousSeasonExists;
		
		//if there is a old student fee and the student is transferred from the previos season then get old student fee else get regular fee
		//$ucret_bilgileri = $db->sql_fetchrow($db->sql_query("SELECT s.ogrID, s.eskiOgrenci, f.egitimUcreti, f.eoEgitimUcreti FROM "._OGRENCILER_." s LEFT JOIN "._OGRENCI_UCRETLERI_." f ON s.KursTuruKodu=f.kursID WHERE s.ogrID='".$ogrID."'"));
		$dbi->join(_OGRENCI_UCRETLERI_. " f", "f.kursID=s.KursTuruKodu", "LEFT");
		$dbi->where("s.ogrID", $this->stdId);
		$feeInfo = $dbi->getOne(_OGRENCILER_. " s", "s.ogrID, s.eskiOgrenci, f.egitimUcreti, f.eoEgitimUcreti, f.eoEgitimZammi");
		
		//tuition
		if($feeInfo["eskiOgrenci"] == "0")
		{
			$kayitliEgitimUcreti = $feeInfo["egitimUcreti"];
		}
		else
		{
			if($feeInfo["eoEgitimZammi"] != "0.00" && $previousSeasonExists)
			{
				//student tcno
				$stdUniqueId = fnStdId2StdInfo($this->stdId, "TCKimlikNo");
				
				//get last year tuition fee
				$dbi->join($previousSeason["veritabani"].".yapilan_ucretler f", "f.ogrNo=s.ogrID", "INNER");
				$dbi->where("f.ucretAdi", "_EGITIM_UCRETI");
				$dbi->where("s.TCKimlikNo", $stdUniqueId);
				$lastYearFee = $dbi->getValue($previousSeason["veritabani"].".ogrenciler s", "f.ucretMiktari");

				if(empty($lastYearFee))
				{
					if($feeInfo["eoEgitimUcreti"] != "0.00") $kayitliEgitimUcreti = $feeInfo["eoEgitimUcreti"];
					else $kayitliEgitimUcreti = $feeInfo["egitimUcreti"];
				}
				else
				{
					//get last year tuition fee
					$dbi->join($previousSeason["veritabani"].".yapilan_indirimler f", "f.ogrNo=s.ogrID", "INNER");
					$dbi->where("f.indirimTuru", "egitim");
					$dbi->where("s.TCKimlikNo", $stdUniqueId);
					$lastYearFeeDiscounts = $dbi->getValue($previousSeason["veritabani"].".ogrenciler s", "SUM(f.indirimMiktari)");

					$kayitliEgitimUcreti = ($lastYearFee - $lastYearFeeDiscounts) * (1 + $feeInfo["eoEgitimZammi"]);
				}
			}
			else if($feeInfo["eoEgitimUcreti"] != "0.00")
			{
				$kayitliEgitimUcreti = $feeInfo["eoEgitimUcreti"];
			}
			else
			{
				$kayitliEgitimUcreti = $feeInfo["egitimUcreti"];
			}
		}
		
		if($kayitliEgitimUcreti != "0.00")
		{
			//check applied tuition	
			$dbi->where("ogrNo", $this->stdId);
			$dbi->where("ucretAdi", "_EGITIM_UCRETI");
			$appliedFee = $dbi->getOne(_YAPILAN_UCRETLER_, "ucretMiktari");
			
			if(empty($appliedFee))
			{
				//insert fee
				$queryData = array(
					"ogrNo"			=> $this->stdId,
					"ucretAdi"		=> "_EGITIM_UCRETI",
					"ucretMiktari"	=> $kayitliEgitimUcreti,
					"subeKodu"		=> $ySubeKodu
				);
				
				$dbi->insert(_YAPILAN_UCRETLER_, $queryData);
			}
			else if(intval($appliedFee["ucretMiktari"]) == 0)
			{
				//update as it is saved as 0
				$dbi->where("ogrNo", $this->stdId);
				$dbi->where("ucretAdi", "_EGITIM_UCRETI");
				$dbi->update(_YAPILAN_UCRETLER_, array("ucretMiktari" => $kayitliEgitimUcreti));
			}
		}
		else
		{
			
			$stdSubs = $dbi->where("stdId", $this->stdId)->getValue(_STUDENT_SUBSCRIPTIONS_, "subscriptionId", null);
			
			if(empty($stdSubs))
			{
				//student course id
				$stdCourseId = fnStdId2StdInfo($this->stdId, "KursTuruKodu");

	            //subscriptions
				$dbi->where("courseId", $stdCourseId);
				$dbi->orderBy("title", "asc");
				$subscriptions = $dbi->get(_COURSE_SUBSCRIPTIONS_, null, "Id, title, totalFee");
				
				foreach($subscriptions as $subscription)
				{
					//insert fee
					$queryData = array(
						"ogrNo"			=> $this->stdId,
						"ucretAdi"		=> $subscription["title"],
						"ucretMiktari"	=> $subscription["totalFee"],
						"subeKodu"		=> $ySubeKodu
					);
					
					$dbi->insert(_YAPILAN_UCRETLER_, $queryData);
				}
			}
		}
	}

	/* function */
	function getStudentFees()
	{
		global $dbi;

		$dbi->join(_OGRENCI_UCRETLERI_. " f", "f.kursID=s.KursTuruKodu", "LEFT");
		$dbi->where("s.ogrID", $this->stdId);
		$fees = $dbi->getOne(_OGRENCILER_. " s", "s.KursTuruKodu, f.kursAdi, f.dergiUcreti, f.kahvaltiUcreti, f.yayinUcreti, f.egitimUcreti, f.eoEgitimUcreti, f.eoEgitimZammi, f.yemekUcreti, f.kirtasiyeUcreti, f.kiyafetUcreti, f.destekUcreti");
		
		//subscriptions
		$dbi->join(_COURSE_SUBSCRIPTIONS_. " s", "s.Id=ss.subscriptionId", "LEFT");
		$dbi->where("ss.stdId", $this->stdId);
		$subs = $dbi->get(_STUDENT_SUBSCRIPTIONS_. " ss", null, "ss.subscriptionId, s.courseId, s.title, s.totalFee");
			
		return array("fees" => $fees, "subscriptions" => $subs);
	}

	/* function */
	function getRecentEnrollments($limit = 10)
	{
		global $dbi, $ySubeKodu, $aid, $simsDateTime;
		
		$dbi->where("opener", $aid);
		$dbi->where("schoolId", $ySubeKodu);
		$dbi->orderBy("openDateTime", "DESC");
		$recentStudents = $dbi->get(_RECENT_OPENED_STUDENTS_);
		
		$displayedStds = array();
		$nofStd = 0;
		foreach($recentStudents as $k => $recentStudent)
		{
			//if the same std then continue
			if(in_array($recentStudent["stdId"], $displayedStds)) continue;

			//add some extra data
			$recentStudents[$k]["batchTitle"] = SinifAdi($recentStudent["stdBatchId"]);
			$recentStudents[$k]["dateDifference"] = DateDifferenceWStrings($simsDateTime, $recentStudent["openDateTime"]);
			
			//set temp std to the current std
			$displayedStds[] = $recentStudent["stdId"];
			$nofStd++;

			if($nofStd == $limit) break;
		}

		return $recentStudents;
	}
}
?>
