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

class Admissions {
    
    private $admissions = array();
    private $interviews = array();
    private $admissionId = 0;
    private $interviewtId = 0;
    private $nofInterviews = 0;
    private $settings = array();

	function __construct()
	{
		global $dbi, $ySubeKodu;
		
		//set nof intervies
		$dbi->where("subeKodu", $ySubeKodu);
		$this->nofInterviews = $dbi->getValue(_ADMISSION_INTERVIEWS_, "count(id)");
		
		//set admission interview settings
		$dbi->where("subeKodu", $ySubeKodu);
		$this->settings = $dbi->getOne(_ADMISSION_INTERVIEW_FORM_SETTINGS_);
	}
	
    /* function */
	function setAdmissionId($Id)
	{
		$this->admissionId = $Id;
	}

    /* function */
	function setInterviewId($Id)
	{
		$this->interviewId = $Id;
	}

    /* function */
	function getNofInterviews()
	{
		return $this->nofInterviews;
	}

    /* function */
	function getSettings()
	{
		return $this->settings;
	}

    /* function */
	function getInterviews()
	{
        global $dbi, $ySubeKodu, $aid, $currentlang;

		//get interviews
		$dbi->where("subeKodu", $ySubeKodu);
		$dbi->orderBy("frmKayitTarihi", "DESC");
		$rows = $dbi->get(_ADMISSION_INTERVIEWS_);
		
		if(!empty($rows))
		{
			//handle admissions
			foreach($rows as $k => $v) {
				
				//order number
				$rows[$k]["n"] = $k + 1;
				
				//date
				$rows[$k]["frmKayitTarihiFormatted"] = FormatDateNumeric2Local($v["frmKayitTarihi"], 1);
				
				//source
				$rows[$k]["frmKaynakFormatted"] = $v["frmKaynak"] == "tablet" ? _TABLET_FORM : YoneticiAdi($v["frmKaynak"]);
				
				//get students for the interview
				$dbi->where("interviewID", $v['id']);
				$students = $dbi->get(_ADMISSION_INTERVIEWS_STUDENTS_);

				foreach($students as $s => $student)
            	{
					if (!empty($student["ogrSinifGrubu"]))
					{
						$dbi->where("subeKodu", $ySubeKodu);
						$dbi->where("kursID", $student["ogrSinifGrubu"]);
						$courseTitle = $dbi->getValue(_OGRENCI_UCRETLERI_, "kursAdi");
			
						//set student course title
						$students[$s]["courseTitle"] = $courseTitle;
						$students[$s]["courseCount"] = "1";
					}
					else
					{
						$students[$s]["courseTitle"] = _NOT_SELECTED;
						$students[$s]["courseCount"] = "0";
					}
            		
            		//get last note for the student related to the interview
            		$dbi->join(_ADMISSION_INTERVIEW_NEGATIVE_REASONS_. " r", "r.Id = n.negativeReasonId", "LEFT");
            		$dbi->where("n.interviewID", $v['id']);
            		$dbi->where("n.studentId", $student["id"]);
					$dbi->where("n.subeKodu", $ySubeKodu);
					$dbi->where("n.status", NULL, "IS NOT");
					$dbi->orderBy("n.id", "DESC");
					$noteDetails = $dbi->getOne(_ADMISSION_INTERVIEWS_NOTES_. " n", "n.status, n.recallDate, r.neden");

					//recall date
					$students[$s]["recallDate"] = ($noteDetails["status"] == "_PREADD_RECALL" && $noteDetails["recallDate"] != "0000-00-00") ? FormatDateNumeric2Local($noteDetails["recallDate"]) : "-";
					
					//recognition work
					$students[$s]["recognitionWork"] = ($noteDetails["status"] == "_RECOGNITION_WORK" && $noteDetails["recallDate"] != "0000-00-00") ? FormatDateNumeric2Local($noteDetails["recallDate"]) : "-";

					//get reason title
			        $students[$s]["negativeTooltipText"] = $noteDetails["neden"];

					//status formatted
					if($noteDetails["status"] == "_PREADD_POSITIVE") $students[$s]["statusFormatted"] = "<button class='btn btn-success' style='width:75px; margin-bottom:5px'><i class='fa fa-thumbs-up'></i> "._PREADD_POSITIVE."</button>";
					else if($noteDetails["status"] == "_PREADD_NEGATIVE") $students[$s]["statusFormatted"] = "<button class='btn btn-danger' style='width:75px; margin-bottom:5px' data-toggle='tooltip' data-placement='right' title='".$negativeTooltipText."'><i class='fa fa-thumbs-down'></i> "._PREADD_NEGATIVE."</button>";
					else if($noteDetails["status"] == "_PREADD_RECALL") $students[$s]["statusFormatted"] = "<button class='btn btn-warning' style='margin-bottom:5px'><i class='fa fa-phone'></i> "._PREADD_RECALL." [".$recallDate."]</button>";
					else if($noteDetails["status"] == "_WILL_BE_FOLLOWED") $students[$s]["statusFormatted"] = "<button class='btn btn-primary' style='margin-bottom:5px'><i class='fa fa-clock-o'></i> "._WILL_BE_FOLLOWED."</button>";
					else if($noteDetails["status"] == "_RECOGNITION_WORK") $students[$s]["statusFormatted"] = "<button class='btn btn-default' style='margin-bottom:5px'><i class='fa fa-address-card-o'></i> "._RECOGNITION_WORK."</button>";
					
					//add status to the student
					$students[$s]["status"] = $noteDetails["status"];
            	}

				//add students to the interview	
				$rows[$k]["students"] = $students;
				
				//add settings
				$rows[$k]["settings"] = $this->settings;
			}
		
			$this->interviews = $rows;
		}
		
		return $this->interviews;
	}

}
?>