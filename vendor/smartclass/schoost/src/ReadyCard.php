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

//@TODO change this to namespace
include "fpdf/".$fpdfVersion."/cellfit.php";

class ReadyCard extends FPDF_CellFit
{
    protected $stdId = 0;
    protected $reportId = 0;
    protected $termId = 0;

	/* function */
	function setReportId($Id)
	{
		global $dbi;
		
		//set season Id
		$this->reportId = $Id;
		
		//set term id
		$this->termId = $dbi->where("Id", $this->reportId)->getValue(_AC_REPORTS_, "seasonID");
	}

	/* function */
	function setStudentId($Id)
	{
		//set season Id
		$this->stdId = $Id;
	}

	/* function */
	function reportTermId()
	{
		global $dbi;
		
		if(empty($this->termId)) $this->termId = $dbi->where("Id", $this->reportId)->getValue(_AC_REPORTS_, "seasonID");
		
		return $this->termId;
	}

	/* function */
	function getStudentClasses()
	{
		global $dbi, $ySubeKodu;
		
		//get classes
        $dbi->join(_CLASSES_. " c", "c.dag_id=cs.classid", "INNER");
		$dbi->where("c.ust_ders_kodu", "0");
		$dbi->where("c.kredi_sayisi", "0", ">");
        $dbi->where("cs.seasonId", $this->termId);
        $dbi->where("cs.schoolid", $ySubeKodu);
        $dbi->where("cs.studentid", $this->stdId);
        $dbi->where("cs.seasonid", NULL, "IS NOT");
        $dbi->orderBy("c.kredi_sayisi", "desc");
        $dbi->orderBy("c.haftalik_ders_saati", "desc");
        $dbi->orderBy("c.ders_baslik", "asc");
        $classes = $dbi->get(_CLASS_STUDENTS_. " cs", null, "DISTINCT cs.classid, c.ders_baslik, c.ders_brans_code, c.haftalik_ders_saati, c.kredi_sayisi");
        
        return $classes;
	}
	
	/* function */
	function getStudentClassGrades($classId)
	{
		global $dbi, $ySubeKodu;
		
		//get classes
        $dbi->where("schoolID", $ySubeKodu);
        $dbi->where("classID", $classId);
        $dbi->where("stdID", $this->stdId);
        $dbi->where("seasonID", NULL, "IS NOT");
        $classGrades = $dbi->map("seasonID")->get(_GRADING_FINALS_, null, "seasonID, isExempt, finalScoreNumber, finalScoreLetter, finalScoreGPA");
        
        return $classGrades;
	}
	
	/* function */
	function getStudentSpaGpa()
	{
		global $dbi, $ySubeKodu;
		
		//get classes
        $dbi->where("schoolId", $ySubeKodu);
        $dbi->where("studentId", $this->stdId);
        $dbi->where("termId", NULL, "IS NOT");
        $spagpa = $dbi->map("termId")->get(_GRADING_SPA_GPA_, null, "termId, average");
        
        return $spagpa;
	}

	/* function */
	function getStudentAttendance($termId = 0)
	{
		$studentAttendance = totalNumberOfAbsence($termId, $this->stdId);
		
		return $studentAttendance;
	}
	
	/* function */
	function getStudentDisciplineFines()
	{
		global $dbi, $ySubeKodu;

		$point = 100;
		$fineCats = array("6", "7", "8", "9");
		$fines = array("6" => 10, "7" => 20, "8" => 80, "9" => 40);
		
	    $dbi->where("fineCategoryId", $fineCats, "IN");
	    $dbi->where("ogrId", $this->stdId);
	    $dbi->where("schoolId", $ySubeKodu);
	    $dbFineIds = $dbi->getValue(_DISCIPLINE_FINES_, "fineCategoryId", null);
		
		foreach($dbFineIds as $dbFineId)
		{
			$point -= $fines[$dbFineId];
		}
		
		return $point;
	}
	
	/* function */
	function getStudentHonorCert()
	{
		global $dbi, $ySubeKodu;

	    $dbi->join(_STUDENT_HONOR_CERTIFICATE_SETTINGS_." d", "d.id=c.certId", "LEFT");
	    $dbi->where("c.stdId", $this->stdId);
	    $dbi->where("c.seasonId", $this->termId);
	    $dbi->where("c.schoolId", $ySubeKodu);
	    $honorCert = $dbi->getOne(_STUDENT_HONOR_CERTIFICATES_. " c", "c.certId, d.title");
		
		return $honorCert;
	}
	
	/* function */
	function getStudentClubs()
	{
		global $dbi, $ySubeKodu;

	    $dbi->join(_CLUBS_." c", "c.Id=cs.kulupKodu", "INNER");
	    $dbi->where("c.schoolId", $ySubeKodu);
	    $dbi->where("cs.ogrenciKodu", $this->stdId);
	    $dbi->where("cs.onaylandiMi", "1");
	    $clubs = $dbi->get(_CLUB_STUDENTS_. " cs", null, "cs.kulupKodu, c.kulup");
		
		return $clubs;
	}
	
	/* function */
	function getConsultantComment()
	{
		global $dbi, $ySubeKodu;

		$dbi->where("reportID", $this->reportId);
		$dbi->where("stdID", $this->stdId);
		$comment = $dbi->getValue(_AC_REPORTS_STUDENTS_, "consultantOpinion");

		return $comment;
	}
	
	/* function */
	function getResponsibleClasses()
	{
		global $dbi, $ySubeKodu, $dbname2;

		//get tc no
		$stdUniqueId = fnStdId2StdInfo($this->stdId, "TCKimlikNo");
		
		//set responsibles
		$responsibles = array();
		
		//get seasons
		$dbi->where("aktif", "on");
		$dbi->orderBy("veritabani", "desc");
		$seasons = $dbi->get(_DONEMLER_, 5);
		
		foreach ($seasons as $season)
		{
		    $studentsTable = str_replace($dbname2, $season["veritabani"], _OGRENCILER_);
		    $gradingFinalsTable = str_replace($dbname2, $season["veritabani"], _GRADING_FINALS_);
		    $classesTable = str_replace($dbname2, $season["veritabani"], _CLASSES_);
		    $gradingTermsTable = str_replace($dbname2, $season["veritabani"], _GRADING_TERMS_);
		
		    //get students
		    $dbi->join($classesTable . " c", "c.dag_id = gf.classID", "LEFT");
		    $dbi->join($studentsTable . " o", "o.ogrID = gf.stdID", "INNER");
		    $dbi->where("o.TCKimlikNo", $stdUniqueId);
		    $dbi->where("o.SubeKodu", $ySubeKodu);
		    $dbi->where("gf.completed", "r");
		    $dbi->where("gf.passed", "0");
		    //$dbi->where("gf.stdID", $this->stdId);
		    $dbi->where("gf.seasonID", 0);
		    $dbi->where("gf.schoolID", $ySubeKodu);
		    $classes = $dbi->getValue($gradingFinalsTable . " gf", "c.ders_baslik", null);
		    //$students = $dbi->get($gradingFinalsTable . " gf", null, "gf.Id as gfId, gf.passed as passed, CONCAT(o.Adi,' ', o.IkinciAdi, ' ', o.Soyadi) as nameSurname, gf.stdID as studentId, gf.seasonID, o.ogrenciNo as studentNumber, gf.classID as classId, o.TCKimlikNo as TCKimlikNo, o.KursTuruKodu");
		    
		    if(!empty($classes))
		    {
		    	foreach($classes as $class)
		    	{
		    		$responsibles[] = $class . " (" . $season["donem"] . ")";
		    	}
		    }
		}
		
		return $responsibles;
	}
}