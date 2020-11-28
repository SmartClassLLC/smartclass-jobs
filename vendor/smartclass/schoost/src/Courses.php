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
use Schoost\Students;

class Courses {
    
    protected $courses = array();
    protected $courseId = 0;
    protected $courseInfo = array();
    protected $seasonId = 0;

	/* function */
	function setSeasonId($Id)
	{
		//set season Id
		$this->seasonId = $Id;
	}

	/* function */
	function setCourseId($Id)
	{
		global $dbi;
		
		//set course Id
		$this->courseId = $Id;
		
		//set course info
		$dbi->where("kursID", $this->courseId);
		$this->courseInfo = $dbi->getOne(_OGRENCI_UCRETLERI_);
	}

	/* function */
	function courseInfo()
	{
        return $this->courseInfo;
	}

	/* function */
	function courseTitle()
	{
        global $dbi;
        
		$courseInfo = $this->courseInfo;
        
		return $courseInfo["kursAdi"];
	}

	/* function */
	function getCourses($fields = "*")
	{
        global $dbi, $ySubeKodu;
        
		$dbi->where("subeKodu", $ySubeKodu);
		$dbi->orderBy("kursAdi", "ASC");
		$courses = $dbi->get(_OGRENCI_UCRETLERI_, null, $fields);
		
		return $courses;
	}

	/* function */
	function searchCourses($filters = array(), $fields = "*")
	{
        global $dbi, $ySubeKodu;
        
        //get tags first
        $tagCourses = $this->searchTags($filters["tags"]);
        
		//get courses
		$dbi->join(_SINIF_SEVIYELERI_. " s", "s.seviyeID=g.seviyeKodu", "LEFT");
		
		//apply filters
		if(!empty($filters))
		{
			if(!empty($filters["daterange"])) {
				if(!empty($filters["daterange"]["startDate"])) $dbi->where("g.kursBaslangic", $filters["daterange"]["startDate"], ">=");
				if(!empty($filters["daterange"]["endDate"])) $dbi->where("g.kursBaslangic", $filters["daterange"]["endDate"], "<=");
			}
			if(!empty($filters["gradeLevels"])) $dbi->where("g.seviyeKodu", $filters["gradeLevels"], "IN");
			if(!empty($filters["tagCourses"])) $dbi->where("g.kursID", $filters["tagCourses"], "IN");
		}

		//other filters		
		$dbi->where("g.subeKodu", $ySubeKodu);
		$dbi->orderBy("s.seviyeID", "ASC");
		$dbi->orderBy("g.kursAdi", "ASC");
		$courses = $dbi->get(_OGRENCI_UCRETLERI_. " g", null, "g.`kursID`, g.`kursAdi`, g.`kursTanim`, g.`haftalikDersSaati`, g.`haftalikKulupSaati`, g.`kursBaslangic`, g.`kursBitis`, g.`seviyeKodu`, s.`seviye`");
		
		foreach ($courses as $k => $course)
		{
			$courses[$k]["kursBaslangicFormatted"] = FormatDateNumeric2Local($course["kursBaslangic"]);
			$courses[$k]["kursBitisFormatted"] = FormatDateNumeric2Local($course["kursBitis"]);
		
		    //nof batches
		    $dbi->where("kursKodu", $course["kursID"]);
		    $nofBatches = $dbi->getValue(_BATCHES_, "COUNT(sinifID)");
			$nofBatchesText = str_replace("{#}", $nofBatches, _THERE_ARE_X_BATCHES);
		
			$courses[$k]["nofBatches"] = $nofBatches;
			$courses[$k]["nofBatchesText"] = $nofBatchesText;
		}
		
		return $courses;
	}
	
	/* function */
	function searchTags($tags = array())
	{
        global $dbi, $ySubeKodu;
        
	    $dbi->where("okulKodu", $ySubeKodu);
	    if(!empty($tags)) $dbi->where("bolumKisaAdi", $tags, "IN");
	    $tags = $dbi->getValue(_KURS_BOLUMLERI_, "DISTINCT kursKodu", null);
		
		return $tags;
	}
	
	/* function */
	function getCoursesWithBatches($fields = "*")
	{
        global $dbi, $ySubeKodu;
        
        //get an instance of Batches
        $b = new Batches();
        
        //get courses first
        $courses = $this->getCourses($fields);
        
        foreach ($courses as $k => $course)
        {
        	//get batches for the course
        	$batches = $b->getAllBatches($course["kursID"], array("sinifID, sinifAdi"));

            foreach ($batches as $m => $batch)
            {
                //get the number of enrolled students of the batch
                $b->setBatchId($batch["sinifID"]);
                $nofStudents = $b->numberOfEnrolledStudents();
                
                //add to the batch
                $batches[$m]["nofStudents"] = $nofStudents;
            }
            
            //add batches to the course
            $courses[$k]["batches"] = $batches;
        }
		
		return $courses;
	}
	
	function getOldCourses()
	{
		global $dbi, $ySubeKodu, $simsDate;
		
		$dbi->where("subeKodu", $ySubeKodu);
		$dbi->where("kursBitis", $simsDate, "<");
		$dbi->where("kursBitis", "0000-00-00", "!=");
		$oldCourses = $dbi->get(_OGRENCI_UCRETLERI_);
		
		foreach($oldCourses as $k => $oldCourse)
		{
			$oldCourses[$k]["kursBaslangicFormatted"] = FormatDateNumeric2Local($oldCourse["kursBaslangic"]);
			$oldCourses[$k]["kursBitisFormatted"] = FormatDateNumeric2Local($oldCourse["kursBitis"]);
		}
					
		return $oldCourses;
	}
}