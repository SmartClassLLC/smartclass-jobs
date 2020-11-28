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

use Schoost\Batches;

class Classes {
    
    private $classId = 0;
    protected $seasonId = 0;
	
	/* function */
	function setSeasonId($Id)
	{
		//set season Id
		$this->seasonId = $Id;
	}
	
	/* function */    
	function setClassId($Id)
	{
		$this->classId = $Id;
	}

	/* function */
	function classTitle()
	{
        global $dbi;
        
		$info = $dbi->where("dag_id", $this->classId)->getOne(_CLASSES_, "ders_baslik");
        
		return $info["ders_baslik"];
	}

	/* function */
	function classRoom()
	{
        global $dbi;
        
        $dbi->join(_DERSLIKLER_. " d", "d.dID=c.derslik_code", "LEFT");
        $dbi->join(_ROOMS_. " r", "r.Id=c.room_id", "LEFT");
        $dbi->where("c.dag_id", $this->classId);
		$info = $dbi->getOne(_CLASSES_. " c", "d.derslik, r.roomName");
        
		return empty($info["roomName"]) ? $info["derslik"] : $info["roomName"];
	}

	/* function */
	function classSeasonId()
	{
        global $dbi;

		$classInfo = $dbi->where("dag_id", $this->classId)->getOne(_CLASSES_, "donemKodu");
		
		return $classInfo["donemKodu"];
	}

	/* function */
	/*
	$term parameter can be set to 'all' to get all classes regardless of terms
	*/
	function getAllClasses($term = "", $branches = array() )
	{
        global $dbi, $ySubeKodu;
		
		if(empty($term) && empty($this->seasonId))
		{
			$queryTermId = $this->getCurrentSeasonId();
			$dbi->where("FIND_IN_SET(?, c.donemKodu)", array("$queryTermId"));
		}
		else if(!empty($this->seasonId))
		{
			$queryTermId = $this->seasonId;
			$dbi->where("FIND_IN_SET(?, c.donemKodu)", array("$queryTermId"));
		}
		else if(!empty($term) && $term != "all")
		{
			$queryTermId = $term;
			$dbi->where("FIND_IN_SET(?, c.donemKodu)", array("$queryTermId"));
		}

		$classBatches = $dbi->subQuery("cbb");
		$classBatches->join(_BATCHES_. " b", "b.sinifID=cb.batchId", "LEFT");
		if(!empty($queryTermId)) $classBatches->where("cb.seasonId", $queryTermId);
		$classBatches->groupBy("cb.classId");
		$classBatches->get(_CLASS_BATCHES_. " cb", null, "cb.classId, GROUP_CONCAT(DISTINCT b.sinifAdi) AS batchTitles");

		$dbi->join($classBatches, "cbb.classId=c.dag_id", "LEFT");
		if (!empty($branches)) $dbi->where("c.ders_brans_code", $branches, "IN");
		$dbi->where("c.subeKodu", $ySubeKodu);
		$dbi->orderBy("c.ders_baslik", "asc");
		$allClasses = $dbi->get(_CLASSES_. " c", null, "cbb.batchTitles, c.*");

		return $allClasses;
	}
	
	/* function */
	/*
	$term parameter can be set to 'all' to get all classes regardless of terms
	*/
	function searchClasses($filters = array())
	{
        global $dbi, $ySubeKodu;
        
        $batchId = $filters["batchId"];
        $teacherId = $filters["teacherId"];
        $subjectIds = $filters["subjectIds"];
        $termId = empty($this->seasonId) ? $filters["termId"] : $this->seasonId;
        
		//if there is no criteria then get all classes
        if(empty($batchId))
        {
            if(empty($teacherId))
            {
                if (empty($subjectIds)) $classes = $this->getAllClasses();
                else $classes = $this->getAllClasses("", $subjectIds);

            }
            else
            {
                if (empty($subjectIds)) $classes = $this->getTeacherClasses($teacherId);
                else $classes = $this->getTeacherClasses($teacherId, $subjectIds);

            }
        }
        //if there is batchId then get batch classes
        else
        {
        	$b = new Batches();
        	$b->setSeasonId($termId);
            $b->setBatchId($batchId);

            if (empty($subjectIds)) $classes = $b->batchClasses("all", $teacherId, false);
            else $classes = $b->batchClasses("all", $teacherId, false, false, $subjectIds);
        }
        
        $sumOfClasses = 0;
        $sumOfCredits = 0;
        
        //handle classes for extra values
        foreach($classes as $k => $class)
        {
        	if (!empty($class["ust_ders_kodu"]))
        	{
        		unset($classes[$k]);
        		continue;
        	}
        	
            //set class Id
            $this->setClassId($class["dag_id"]);

            //subject info
            $classes[$k]["subjectInfo"] = $this->classSubjectInfo();

            //# of students
            $classes[$k]["nofStudents"] = $this->numberOfEnrolledStudents();

			//get classroom title
            if($class["room_id"] != 0) $classes[$k]["roomTitle"] = fnRoomId2RoomTitle($class["room_id"]);
            else if($class["derslik_code"] != "0") $classes[$k]["roomTitle"] = DerslikAdi($class["derslik_code"]);
            
			//get class batches
			$classBatches = $this->classBatchesInfo();
			foreach ($classBatches as $b1 => $classBatch)
			{
				$electiveBatchId = $classBatch["batchId"];
				$tipText = "";
				
				//get # of students in the class from the batch
				$nofStudentsFromBatch = $this->numberOfEnrolledStudentsFromBatch($electiveBatchId);
				
				if($class["secmeli_ders"] == "1")
				{
					if($class["ogrenci_atama_yontemi"] == "manual")
					{
						$tipText = str_replace("{#}", $nofStudentsFromBatch, _THERE_ARE_X_STUDENTS_INTHE_CLASS_FROM_THE_BATCH);
						$tipText .= "<br>" ._CLICK_TO_ASSIGN_STUDENTS_TO_ELECTIVE_CLASS;
					}
					else if($class["ogrenci_atama_yontemi"] == "automatic")
					{
						$tipText = str_replace("{#}", $nofStudentsFromBatch, _THERE_ARE_X_STUDENTS_INTHE_CLASS_FROM_THE_BATCH);
						$tipText .= "<br>" ._CLICK_TO_SET_AUTOMATIC_ASSIGNMENT_RULES;
					}
					else if($class["ogrenci_atama_yontemi"] == "bookbuilding")
					{
						$tipText = str_replace("{#}", $nofStudentsFromBatch, _THERE_ARE_X_STUDENTS_INTHE_CLASS_FROM_THE_BATCH);
						$tipText .= "<br>" ._CLICK_TO_SET_BOOKBUILDING_ASSIGNMENT_DETAILS;
					}
				}
				
				$classBatches[$b1]["tooltipText"] = $tipText;
			}
			
			$classes[$k]["classBatches"] = $classBatches;
			
			//get class teacher
			$classes[$k]["classTeachers"] = $this->classTeachersInfo();
			
            //$sumOfClasses += $class["haftalik_ders_saati"];
            //$sumOfCredits += $class["kredi_sayisi"];

			//get sub classes
            $dbi->where("ust_ders_kodu", $class["dag_id"]);
            $dbi->where("FIND_IN_SET(?, donemKodu)", array("$termId"));
            $dbi->where("subeKodu", $ySubeKodu);
            $subClasses = $dbi->get(_CLASSES_);
            
            foreach($subClasses as $s => $subClass)
            {
				//set class Id
				$this->setClassId($subClass["dag_id"]);
	
				//subject info
				$subClasses[$s]["subjectInfo"] = $this->classSubjectInfo();
	
				//# of students
				$subClasses[$s]["nofStudents"] = $this->numberOfEnrolledStudents();

				//get classroom title
				if($subClass["room_id"] != 0) $subClasses[$s]["roomTitle"] = fnRoomId2RoomTitle($subClass["room_id"]);
				else if($subClass["derslik_code"] != "0") $subClasses[$s]["roomTitle"] = DerslikAdi($subClass["derslik_code"]);
	
				//get class batches
				$classBatches = $this->classBatchesInfo();
				foreach ($classBatches as $b2 => $classBatch)
				{
					$electiveBatchId = $classBatch["batchId"];
					$tipText = "";
					
					//get # of students in the class from the batch
					$nofStudentsFromBatch = $this->numberOfEnrolledStudentsFromBatch($electiveBatchId);
					
					if($subClass["secmeli_ders"] == "1")
					{
						if($subClass["ogrenci_atama_yontemi"] == "manual")
						{
							$tipText = str_replace("{#}", $nofStudentsFromBatch, _THERE_ARE_X_STUDENTS_INTHE_CLASS_FROM_THE_BATCH);
							$tipText .= "<br>" ._CLICK_TO_ASSIGN_STUDENTS_TO_ELECTIVE_CLASS;
						}
						else if($subClass["ogrenci_atama_yontemi"] == "automatic")
						{
							$tipText = str_replace("{#}", $nofStudentsFromBatch, _THERE_ARE_X_STUDENTS_INTHE_CLASS_FROM_THE_BATCH);
							$tipText .= "<br>" ._CLICK_TO_SET_AUTOMATIC_ASSIGNMENT_RULES;
						}
						else if($subClass["ogrenci_atama_yontemi"] == "bookbuilding")
						{
							$tipText = str_replace("{#}", $nofStudentsFromBatch, _THERE_ARE_X_STUDENTS_INTHE_CLASS_FROM_THE_BATCH);
							$tipText .= "<br>" ._CLICK_TO_SET_BOOKBUILDING_ASSIGNMENT_DETAILS;
						}
					}
					
					$classBatches[$b2]["tooltipText"] = $tipText;
				}
				
				$subClasses[$s]["classBatches"] = $classBatches;

				//get teachers
				$subClasses[$s]["classTeachers"] = $this->classTeachersInfo();
            }

			$classes[$k]["subClasses"] = $subClasses;
			
			$dbi->where("branchID", $ySubeKodu);
	        $moodle = $dbi->getOne(_MOODLE_CONFIG_);
	        $lms = sizeof($moodle) > 0 ? 1 : 0;
	        
	        $classes[$k]["lms"] = $lms;
        }
        
        return array_values($classes); //reindex
	}	

	/* function */
	function getTeacherClasses($teacherId, $branches = "")
	{
        global $dbi, $ySubeKodu;
		
		if(empty($this->seasonId)) $this->seasonId = $this->getCurrentSeasonId();

		$classBatches = $dbi->subQuery("cbb");
		$classBatches->join(_BATCHES_. " b", "b.sinifID=cb.batchId", "LEFT");
		$classBatches->where("cb.seasonId", $this->seasonId);
		$classBatches->get(_CLASS_BATCHES_. " cb", null, "cb.classId, b.sinifAdi");
		
		$dbi->join(_CLASSES_." c", "c.dag_id=t.classId", "INNER");
		$dbi->joinWhere(_CLASSES_." c", "FIND_IN_SET(?, c.donemKodu)", array("$this->seasonId"));
		$dbi->join($classBatches, "cbb.classId=c.dag_id", "LEFT");
		$dbi->where("t.teacherId", $teacherId);
		if (!empty($branches)) $dbi->where("c.ders_brans_code", $branches, "IN");
		$dbi->where("t.seasonId", $this->seasonId);
		$dbi->where("t.schoolId", $ySubeKodu);
		$dbi->groupBy("t.classId");
		$allTeacherClasses = $dbi->get(_CLASS_TEACHERS_." t", null, "t.teacherType, t.teachertitle, GROUP_CONCAT(cbb.sinifAdi) AS batchTitles, c.*");
		
		return $allTeacherClasses;
	}

	/* function */
	function getStudentClasses($studentId, $onlyIds = false)
	{
        global $dbi;

		if(empty($this->seasonId)) $this->seasonId = $this->getCurrentSeasonId();
		
		$dbi->join(_CLASSES_." c", "c.dag_id=t.classId", "INNER");
		$dbi->joinWhere(_CLASSES_." c", "FIND_IN_SET(?, c.donemKodu)", array("$this->seasonId"));
		$dbi->where("t.studentId", $studentId);
		$dbi->where("t.seasonId", $this->seasonId);
		$dbi->orderBy("c.kredi_sayisi", "desc");
		$dbi->orderBy("c.ders_baslik", "asc");

		if($onlyIds) $allStudentClasses = $dbi->getValue(_CLASS_STUDENTS_." t", "t.classId", null);
		else $allStudentClasses = $dbi->get(_CLASS_STUDENTS_." t", null, "t.studentId, t.classId, c.*");
		
		return $allStudentClasses;
	}

	/* function */
	function classInfo()
	{
        global $dbi;

		$classInfo = $dbi->where("dag_id", $this->classId)->getOne(_CLASSES_);
		$classInfo["classRoom"] = $this->classRoom();
		
		return $classInfo;
	}

	/* function */
	function classInfo4Attendance()
	{
        global $dbi;

		$dbi->where("dag_id", $this->classId);
		$classInfo = $dbi->getOne(_CLASSES_, "dag_id, ders_baslik, secmeli_ders");
		$classInfo["classRoom"] = $this->classRoom();
		$classInfo["students"] = $this->enrolledStudentIds(true);
		
		return $classInfo;
	}

	/* function */
	function classSubjectInfo()
	{
        global $dbi;

		$cInfo = $this->classInfo();
		$classSubjectInfo = $dbi->where("bID", $cInfo["ders_brans_code"])->get(_DERS_BRANSLARI_);
		
		return $classSubjectInfo[0];
	}

	/* function */
	function classBatchesInfo($field = array())
	{
		global $dbi;
		
		$dbi->join(_BATCHES_." b", "b.sinifID=cb.batchId", "LEFT");
		if(!empty($this->seasonId)) $dbi->where("cb.seasonId", $this->seasonId);
		$dbi->where("cb.classId", $this->classId);
		$classBatches = $dbi->get(_CLASS_BATCHES_." cb", null, "cb.Id, cb.classId, cb.batchId, b.sinifAdi");
	    
		if(empty($field))
		{
			return $classBatches;
		}
		else
		{
			$returnArray = array();
			foreach($classBatches as $value)
			{
				$returnArray[] = $value[$field];
			}
			
			return $returnArray;
		}
	}

	/* function */
	function classBatchesIds($commaSeparated = false)
	{
		global $dbi;

		if(!empty($this->seasonId)) $dbi->where("seasonId", $this->seasonId);
		$classBatches = $dbi->where("classId", $this->classId)->getValue(_CLASS_BATCHES_, "batchId", null);
		
		if(empty($classBatches)) return '';
		else return $commaSeparated ? implode(",", $classBatches) : $classBatches;
	}

	/* function */	
	function classTeachersInfo()
	{
		global $dbi;
		
		$dbi->join(_PERSONEL_." p", "t.teacherId=p.perID", "LEFT");
		$dbi->where("t.classId", $this->classId);
		if(!empty($this->seasonId)) $dbi->where("t.seasonId", $this->seasonId);
		$dbi->orderBy("FIELD(t.teacherType, 'primary', 'secondary', 'assistant')", "asc");
		$classTeachers = $dbi->get(_CLASS_TEACHERS_." t", null, "t.teacherId, t.teacherType, t.teacherTitle, p.*");
		
		return $classTeachers;
	}

	/* function */	
	function classTeacherIds($commaSeparated = false)
	{
		global $dbi;
		
	    $dbi->join(_PERSONEL_." p", "t.teacherId=p.perID", "LEFT");
		$dbi->where("t.classId", $this->classId);
		if(!empty($this->seasonId)) $dbi->where("t.seasonId", $this->seasonId);
		$dbi->orderBy("FIELD(t.teacherType, 'primary', 'secondary', 'assistant')", "asc");
		$classTeachers = $dbi->getValue(_CLASS_TEACHERS_." t", "t.teacherId", null);
		
		if(empty($classTeachers)) return '';
		else return $commaSeparated ? implode(",", $classTeachers) : $classTeachers;
	}

	/* function */	
	function numberOfEnrolledStudents()
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_." s", "c.studentId=s.ogrID", "INNER");
		$dbi->where("c.classId", $this->classId);
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		if(!empty($this->seasonId)) $dbi->where("c.seasonId", $this->seasonId);
		$nof = $dbi->getValue(_CLASS_STUDENTS_." c", "COUNT(c.Id)");

		return $nof;
	}

	/* function */
	function numberOfEnrolledMaleStudents()
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_." s", "c.studentId=s.ogrID", "INNER");
		$dbi->where("c.classId", $this->classId);
		$dbi->where("s.Cinsiyeti", "E");
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		$nof = $dbi->getValue(_CLASS_STUDENTS_." c", "COUNT(c.Id)");
        
		return $nof;
	}

	/* function */
	function numberOfEnrolledFemaleStudents()
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_." s", "c.studentId=s.ogrID", "INNER");
		$dbi->where("c.classId", $this->classId);
		$dbi->where("s.Cinsiyeti", "K");
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		$nof = $dbi->getValue(_CLASS_STUDENTS_." c", "COUNT(c.Id)");
        
		return $nof;
	}

	/* function */
	function numberOfEnrolledStudentsFromBatch($batchId)
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_BATCH_STUDENTS_." b", "c.studentId=b.studentId", "INNER");
		$dbi->where("c.classId", $this->classId);
		$dbi->where("b.batchId", $batchId);
		if(!empty($this->seasonId)) $dbi->where("c.seasonId", $this->seasonId);
		$nof = $dbi->getValue(_CLASS_STUDENTS_." c", "COUNT(c.Id)");

		return $nof;
	}

	/* function */
	function enrolledStudentIds($justValues = true)
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_." s", "c.studentId=s.ogrID", "INNER");
		$dbi->where("c.classId", $this->classId);
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		
		if(!empty($this->seasonId)) $dbi->where("c.seasonId", $this->seasonId);
		
		$dbi->orderBy("s.Adi", "asc");
		$dbi->orderBy("s.IkinciAdi", "asc");
		$dbi->orderBy("s.Soyadi", "asc");
		//$students = $dbi->get(_CLASS_STUDENTS_." c", null, "c.studentId");

		if($justValues) $students = $dbi->getValue(_CLASS_STUDENTS_. " c", "c.studentId", null);
		else $students = $dbi->get(_CLASS_STUDENTS_. " c", null, array("c.studentId"));
		
		return $students;
	}

	/* function */
	function enrolledStudentInfo($classIds = "", $batchId = "", $fields = "s.*")
	{
        global $dbi, $ySubeKodu;

		$dbi->join(_OGRENCILER_." s", "c.studentId=s.ogrID", "INNER");
		$dbi->join(_BATCHES_." b", "b.sinifID=s.SinifKodu", "LEFT");
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);
		
		if(!empty($batchId)) $dbi->where("s.SinifKodu", $batchId);
		
		if(empty($classIds))
		{
			$dbi->where("c.classId", $this->classId);
		}
		else
		{
			if(!is_array($classIds)) $classIds = explode(",", $classIds);
			$dbi->where("c.classId", $classIds, "IN");
		}
		
		if(!empty($this->seasonId)) $dbi->where("c.seasonId", $this->seasonId);
		else $dbi->where("c.seasonId", NULL, "IS NOT");
		
		$dbi->orderBy("s.Adi", "asc");
		$dbi->orderBy("s.IkinciAdi", "asc");
		$dbi->orderBy("s.Soyadi", "asc");
		
		$students = $dbi->map("studentId")->ArrayBuilder()->get(_CLASS_STUDENTS_." c", null, "c.studentId, b.sinifAdi, " . $fields);

		foreach ($students as $k => $student)
		{
			$students[$k]["AdiSoyadi"] = fnStudentName($student["Adi"], $student["IkinciAdi"], $student["Soyadi"]);
			$students[$k]["Fotograf"] = showPhoto($student["Foto"], $student["Cinsiyeti"]);
		}
		
		return $students;
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

		$seasonId = $dbi->where("startDate", $simsDate, "<=")->where("endDate", $simsDate, ">=")->getValue(_GRADING_TERMS_, "Id");

		return $seasonId;
	}
}