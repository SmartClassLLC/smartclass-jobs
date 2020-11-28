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

class Batches {
    
    protected $batchId = 0;
    protected $batchInfo = array();
    protected $seasonId = 0;

	/* function */
	function setSeasonId($Id)
	{
		//set season Id
		$this->seasonId = $Id;
	}

	/* function */
	function setBatchId($Id)
	{
		global $dbi;
		
		//set batch Id
		$this->batchId = $Id;
		
		//set batch info
		$this->batchInfo = $dbi->where("sinifID", $this->batchId)->getOne(_BATCHES_);
	}

	/* function */
	function batchInfo()
	{
        return $this->batchInfo;
	}

	/* function */
	function batchTitle()
	{
        global $dbi;
        
		$batchInfo = $this->batchInfo;
        
		return $batchInfo["sinifAdi"];
	}

	/* function */
	function batchCourseTitle()
	{
        global $dbi;
        
        $batchInfo = $this->batchInfo;
        
		$courseInfo = $dbi->where("kursID", $batchInfo["kursKodu"])->getOne(_OGRENCI_UCRETLERI_, "kursAdi");
        
		return $courseInfo["kursAdi"];
		
	}

	/* function */
	function batchProgramTitle()
	{
        global $dbi;
        
        $batchInfo = $this->batchInfo;
        
		$programInfo = $dbi->where("pID", $batchInfo["progKodu"])->getOne(_EGITIM_PROGRAMLARI_, "pAdi");
        
		return $programInfo["pAdi"];
	}

	/* function */
	function getAllBatches($courseIds = "", $fields = "*")
	{
        global $dbi, $ySubeKodu;

		$dbi->where("subeKodu", $ySubeKodu);
		if(!empty($courseIds)) {
			if(!is_array($courseIds)) $courseIds = explode(",", $courseIds);
			$dbi->where("kursKodu", $courseIds, "IN");
		}
		$dbi->orderBy("sinifAdi", "asc");
		$allBatches = $dbi->get(_BATCHES_, null, $fields);
		
		return $allBatches;
	}

	/* function */
	function numberOfEnrolledStudents()
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_. " s", "s.ogrID=b.studentId", "INNER");
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		$dbi->where("b.batchId", $this->batchId);
		$nof = $dbi->getValue(_BATCH_STUDENTS_. " b", "COUNT(b.studentId)");

		return $nof;
	}

	/* function */
	function numberOfEnrolledMaleStudents()
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_. " s", "s.ogrID=b.studentId", "INNER");
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		$dbi->where("s.Cinsiyeti", "E");
		$dbi->where("b.batchId", $this->batchId);
		$dbi->get(_BATCH_STUDENTS_. " b", null, array("b.studentId"));

		return $dbi->count;
	}

	/* function */
	function numberOfEnrolledFemaleStudents()
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_. " s", "s.ogrID=b.studentId", "INNER");
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		$dbi->where("s.Cinsiyeti", "K");
		$dbi->where("b.batchId", $this->batchId);
		$dbi->get(_BATCH_STUDENTS_. " b", null, array("b.studentId"));
		
		return $dbi->count;
	}

	/* function */
	function enrolledStudentIds($justValues = true, $batches = array())
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_. " s", "s.ogrID=b.studentId", "INNER");
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		
		if(empty($batches)) $dbi->where("b.batchId", $this->batchId);
		else $dbi->where("b.batchId", $batches, "IN");
		
		$dbi->orderBy("s.Adi", "asc");
		$dbi->orderBy("s.IkinciAdi", "asc");
		$dbi->orderBy("s.Soyadi", "asc");

		if($justValues) $students = $dbi->getValue(_BATCH_STUDENTS_. " b", "b.studentId", null);
		else $students = $dbi->get(_BATCH_STUDENTS_. " b", null, array("b.studentId"));

		return $students;
	}

	/* function */
	function enrolledMaleStudentIds($justValues = true)
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_. " s", "s.ogrID=b.studentId", "INNER");
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		$dbi->where("s.Cinsiyeti", "E");
		$dbi->where("b.batchId", $this->batchId);
		$dbi->orderBy("s.Adi", "asc");
		$dbi->orderBy("s.IkinciAdi", "asc");
		$dbi->orderBy("s.Soyadi", "asc");

		if($justValues) $students = $dbi->getValue(_BATCH_STUDENTS_. " b", "b.studentId", null);
		else $students = $dbi->get(_BATCH_STUDENTS_. " b", null, array("b.studentId"));

		return $students;
	}

	/* function */
	function enrolledFemaleStudentIds($justValues = true)
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_. " s", "s.ogrID=b.studentId", "INNER");
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		$dbi->where("s.Cinsiyeti", "K");
		$dbi->where("b.batchId", $this->batchId);
		$dbi->orderBy("s.Adi", "asc");
		$dbi->orderBy("s.IkinciAdi", "asc");
		$dbi->orderBy("s.Soyadi", "asc");

		if($justValues) $students = $dbi->getValue(_BATCH_STUDENTS_. " b", "b.studentId", null);
		else $students = $dbi->get(_BATCH_STUDENTS_. " b", null, array("b.studentId"));

		return $students;
	}
	
	/* function */
	function enrolledStudentInfo($batchIds = "",$orderBy = "")
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_. " s", "s.ogrID=b.studentId", "INNER");
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		
		if(empty($batchIds))
		{
			$dbi->where("b.batchId", $this->batchId);
		}
		else
		{
			if(!is_array($batchIds)) $batchIds = explode(",", $batchIds);
			$dbi->where("b.batchId", $batchIds, "IN");
		}
		
		if ($orderBy == "orderStudentNumber")
		{
			$dbi->orderBy("s.ogrenciNo","ASC");
		}
		else
		{
			$dbi->orderBy("s.Adi", "asc");
			$dbi->orderBy("s.IkinciAdi", "asc");
			$dbi->orderBy("s.Soyadi", "asc");	
		}
		
		$students = $dbi->get(_BATCH_STUDENTS_. " b", null, array("b.studentId", "s.*"));
		
		foreach ($students as $k => $student)
		{
			$students[$k]["AdiSoyadi"] = fnStudentName($student["Adi"], $student["IkinciAdi"], $student["Soyadi"]);
			$students[$k]["Fotograf"] = showPhoto($student["Foto"], $student["Cinsiyeti"]);
		}
		
		return $students;
	}

	/* function */
	function enrolledStudents4Attendance()
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_. " s", "s.ogrID=b.studentId", "INNER");
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		$dbi->where("b.batchId", $this->batchId);
		$dbi->orderBy("s.Adi", "asc");
		$dbi->orderBy("s.IkinciAdi", "asc");
		$dbi->orderBy("s.Soyadi", "asc");
		$students = $dbi->get(_BATCH_STUDENTS_. " b", null, "b.studentId, s.ogrID, s.Adi, s.IkinciAdi, s.Soyadi, CONCAT(s.Adi, ' ', s.Soyadi) AS AdiSoyadi, s.ogrenciNo, s.Cinsiyeti, s.Foto");
		
		return $students;
	}

	/* function */
	//filter options
	//all		: gets all classes
	//mandatory	: gets mandatory classes
	//elective	: gets elective classes
	function batchClasses($filter = "all", $teacherId = 0, $forceCurrentTerm = true, $showSubClasses = false, $branches = "")
	{
        global $dbi;

		if(empty($this->seasonId) && $forceCurrentTerm) $this->seasonId = $this->getCurrentSeasonId();

		//set columns
		$cols = array("c.dag_id", "c.ust_ders_kodu", "c.ders_baslik_ingilizce", "c.ders_baslik", "c.ders_brans_code", "c.derslik_code", "c.room_id", "c.kontenjan", "c.haftalik_ders_saati", "c.kredi_sayisi", "c.ders_notuna_etkisi", "c.ogrenci_atama_yontemi", "c.secmeli_ders", "c.donemKodu", "s.bransAdi", "s.bransRenk");
		
		if(empty($this->seasonId))
		{
			$classBatches = $dbi->subQuery();
			$classBatches->where("batchId", $this->batchId);
			$classBatches->get(_CLASS_BATCHES_, null, "classId");
			
			if(!empty($teacherId)) $dbi->joinWhere(_CLASSES_. " c", "c.dag_id IN (SELECT classId FROM ". _CLASS_TEACHERS_ ." WHERE teacherId=?)", array($teacherId));
			
			$dbi->join(_DERS_BRANSLARI_ . " s", "c.ders_brans_code=s.bID", "LEFT");
			
			$dbi->where("c.dag_id", $classBatches, "IN");
			
			if (!$showSubClasses) $dbi->where("ust_ders_kodu", "0");
			
			//set filters	
			if($filter == "elective") $dbi->where("c.secmeli_ders", "1");
			else if($filter == "mandatory") $dbi->where("c.secmeli_ders", "0");
			
			if (!empty($branches)) $dbi->where("c.ders_brans_code",$branches,"IN");
			
			$dbi->orderBy("c.haftalik_ders_saati", "desc");
			$dbi->orderBy("c.ders_baslik", "asc");
			
			//get classes
			$classes = $dbi->get(_CLASSES_ . " c", null, $cols);
		}
		else
		{
			$classBatches = $dbi->subQuery();
			$classBatches->where("batchId", $this->batchId);
			$classBatches->get(_CLASS_BATCHES_, null, "classId");
			
			//set where and join tables
			if(!empty($teacherId)) $dbi->joinWhere(_CLASSES_. " c", "c.dag_id IN (SELECT classId FROM ". _CLASS_TEACHERS_ ." WHERE teacherId=? AND seasonId=?)", array($teacherId, $this->seasonId));
			
			$dbi->join(_DERS_BRANSLARI_ . " s", "c.ders_brans_code=s.bID", "LEFT");
			//$dbi->where("b.batchId", $this->batchId);
			//$dbi->where("b.seasonId", $this->seasonId);
			$dbi->where("c.dag_id", $classBatches, "IN");
			
			if (!$showSubClasses) $dbi->where("ust_ders_kodu", "0");
			
			$dbi->where("FIND_IN_SET(?, c.donemKodu)", array("$this->seasonId"));
			
			//set filters	
			if($filter == "elective") $dbi->where("c.secmeli_ders", "1");
			else if($filter == "mandatory") $dbi->where("c.secmeli_ders", "0");
			
			if (!empty($branches)) $dbi->where("c.ders_brans_code",$branches,"IN");
			
			$dbi->orderBy("c.haftalik_ders_saati", "desc");
			$dbi->orderBy("c.ders_baslik", "asc");
			
			//get classes
			$classes = $dbi->get(_CLASSES_ . " c", null, $cols);
		}
		
		return $classes;
	}

	/* function */
	//parameters
	//sdate		: date of the schedule
	function batchScheduleClasses($sdate)
	{
        global $dbi;

		//get classes
		$dbi->where("FIND_IN_SET(?, CAST(`sinif_code` AS CHAR)) > 0", array($this->batchId));
		$dbi->where("tarih", $sdate);
		$dbi->orderBy("giris_saati", "asc");
		$classes = $dbi->get(_SCHEDULE_, null, "pr_id, atama_code, ogretmen_code, giris_saati, cikis_saati");
		
		return $classes;
	}

	/* function */
	function getSeasonId($date)
	{
		global $dbi, $simsDate;

		if(empty($date)) $date = $simsDate;
		
		$rermId = $dbi->where("startDate", $date, "<=")->where("endDate", $date, ">=")->getValue(_GRADING_TERMS_, "Id");

		return $rermId;
	}

	/* function */
	function getCurrentSeasonId()
	{
		global $dbi, $simsDate;

		$currentTermId = $dbi->where("startDate", $simsDate, "<=")->where("endDate", $simsDate, ">=")->getValue(_GRADING_TERMS_, "Id");

		return $currentTermId;
	}
}