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

class PerformanceSurveys {
    
    /* function */
	function getSurveys()
	{
        global $dbi, $ySubeKodu, $aid, $currentlang;
        
		$dbi->where("schoolId", array("0", $ySubeKodu), "IN");
		//$dbi->orderBy("name", "asc");
		$dbi->orderBy("addDate", "DESC");
		$surveys = $dbi->get(_PERFORMANCE_SURVEYS_);

		foreach ($surveys as $k => $survey)
		{
			//add icon
			$surveys[$k]["icon"] = (empty($survey["icon"])) ? "file" : $survey["icon"];
			
			//surveyType
			$surveys[$k]["surveyType"] = (empty($survey["surveyType"])) ? "personnel" : $survey["surveyType"];
			
			$dbi->where("surveyId", $survey["Id"]);
			$nofAnsweredStudent = $dbi->getValue(_PERFORMANCE_SURVEYS_ASSIGNMENT_QUESTIONS_STUDENTS_, "COUNT(*)");
			$surveys[$k]["nofAnsweredStudent"] = empty($nofAnsweredStudent) ? 0 : sizeof($nofAnsweredStudent);
			
			//add adder string
			if(empty($survey["adder"])) $surveys[$k]["adderString"] = "";
			else $surveys[$k]["adderString"] = str_replace(array("{#}", "{%}"), 
					array(
						FormatDateNumeric2Local($survey["addDate"]), 
						YoneticiAdi($survey["adder"])), 
					_HAS_BEEN_ADDED_ON_X_BY_Y);
			
			//last update string
			if(empty($survey["lastUpdater"])) $surveys[$k]["lastUpdaterString"] = "";
			else $surveys[$k]["lastUpdaterString"] = str_replace(array("{#}", "{%}"), 
				array(
					FormatDateNumeric2Local($survey["lastUpdateDate"], 1, true), 
					YoneticiAdi($survey["lastUpdater"])), 
				_HAS_BEEN_LAST_UPDATED_ON_X_BY_Y);
			
			//get questions of the form
			$dbi->where("surveyId", $survey["Id"]);
			$testQuestions = $dbi->get(_PERFORMANCE_SURVEYS_QUESTIONS_);
			$nofQuestions = empty($testQuestions) ? 0 : sizeof($testQuestions);
			
			$surveys[$k]["countQuestions"] = empty($testQuestions) ? 0 : sizeof($testQuestions);
			$surveys[$k]["nofQuestions"] = str_replace(array("{#}"), 
				array($nofQuestions), _THERE_ARE_X_QUESTIONS_IN_THE_FORM);

			//get nof answered questions
			if($ySubeKodu > 0) $dbi->where("schoolId", $ySubeKodu);
			$dbi->where("surveyId", $survey["Id"]);
			$dbi->where("answerId", "0", "!=");
			$nofAnswered = $dbi->getOne(_PERFORMANCE_SURVEYS_ASSIGNED_QUESTIONS_);

			//$sizeOfAnswered = (empty($nofAnswered)) ? 0 : sizeof($nofAnswered);
			$surveys[$k]["nofAnswered"] = empty($nofAnswered) ? 0 : sizeof($nofAnswered);
			
			if($ySubeKodu > 0 && $survey["schoolId"] == "0")
			{
				$surveys[$k]["surveysHq"] = 0;
			}
			else $surveys[$k]["surveysHq"] = 1;
			
			$dbi->where("surveyId", $survey["Id"]);
			$opticDataTemplates = $dbi->get(_PERFORMANCE_SURVEYS_OPTIC_DATA_TEMPLATE_);
			$surveys[$k]["opticDataTemplate"] = empty($opticDataTemplates) ? 0 : sizeof($opticDataTemplates);
			
			/**
			 * check assignments for students
			 **/
			//assigned student's batches
			if($ySubeKodu > 0) $dbi->where("schoolId", $ySubeKodu);
			$dbi->where("studentId", "0", "!=");
			$dbi->where("studentUserId", "", "!=");
			$dbi->where("parentId", "0");
			$dbi->where("surveyId", $survey["Id"]);
			$stdIds = $dbi->getValue(_PERFORMANCE_SURVEYS_ASSIGNMENT_, "DISTINCT(studentId)", null);
			//nof assigned students
			$nofAssignedStds = empty($stdIds) ? 0 : sizeof($stdIds);

			//answered students
			if(!empty($stdIds))
			{
				//@TODO
				/**
				 * check this code. i made some changes but need to be tested
				 * for now just comment it out
				**/
				/*
				if($ySubeKodu > 0) $dbi->where("SubeKodu", $ySubeKodu);
				$dbi->where("ogrID", $stdIds, "IN");
				$batchIds = $dbi->getValue(_OGRENCILER_, null, 
					"GROUP_CONCAT(DISTINCT SinifKodu SEPARATOR ',')");
				$surveys[$k]["batchIds"] = $batchIds;
				*/
				//end of TODO

				//nof answered students
				if($survey["surveyType"] == "student")
				{
					$i = 0;
					$dbi->where("ogrID", $stdIds, "IN");
					if($ySubeKodu > 0) $dbi->where("SubeKodu", $ySubeKodu);
					$studentInfos = $dbi->get(_OGRENCILER_, null, "TCKimlikNo");
					
					foreach($studentInfos as $studentInfo)
					{
						if($ySubeKodu > 0) $dbi->where("schoolId", $ySubeKodu);
						$dbi->where("voter", $studentInfo["TCKimlikNo"]);
						$dbi->where("surveyId", $survey["Id"]);
						$dbi->where("answerId", "0", "!=");
						$dbi->where("answerPoint", "0", "!=");
						$answeredStds = $dbi->getOne(_PERFORMANCE_SURVEYS_ASSIGNMENT_QUESTIONS_STUDENTS_, "voter");
						
						if(!empty($answeredStds)) $i++;
					}
					
					$nofAnsweredStds = $i;
				}
				else 
				{
					if($ySubeKodu > 0) $dbi->where("schoolId", $ySubeKodu);
					$dbi->where("studentId", $stdIds, "IN");
					$dbi->where("surveyId", $survey["Id"]);
					$dbi->where("parentId", "0");
					$dbi->where("answerId", "0", "!=");
					$dbi->where("answerPoint", "0", "!=");
					$answeredStds = $dbi->get(_PERFORMANCE_SURVEYS_ASSIGNED_QUESTIONS_, null, "DISTINCT studentId");
					
					$nofAnsweredStds = empty($answeredStds) ? 0 : sizeof($answeredStds);
				}

				//nof assigned students
				$surveys[$k]["nofAssignedStds"] = str_replace("{?}", 
					$nofAssignedStds, _FORM_WAS_ASSIGNED_TO_X_STUDENTS);
					
				//nof answered students
				$surveys[$k]["nofAnsweredStds"] = str_replace("{?}", 
					$nofAnsweredStds, _X_STUDENTS_ANSWERED);

				//nof unanswered students
				if($nofAssignedStds > $nofAnsweredStds) {
					$surveys[$k]["nofUnAnsweredStds"] = str_replace("{?}", 
						$nofAssignedStds - $nofAnsweredStds, _X_STUDENTS_DID_NOT_ANSWER);
				}
				else {
					$surveys[$k]["nofUnAnsweredStds"] = 0;
				}
			}

			/**
			 * check assignments for parents
			 **/
			//assigned parent's batches
			if($ySubeKodu > 0) $dbi->where("schoolId", $ySubeKodu);
			$dbi->where("parentId", "0", "!=");
			$dbi->where("parentUserId", "", "!=");
			$dbi->where("surveyId", $survey["Id"]);
			$parentIds = $dbi->getValue(_PERFORMANCE_SURVEYS_ASSIGNMENT_, "DISTINCT(parentId)", null);

			//nof assigned parents
			$nofAssignedParents = empty($parentIds) ? 0 : sizeof($parentIds);

			//answered parents
			if(!empty($parentIds))
			{
				//nof answered parents
				if($survey["surveyType"] == "student")
				{
					$j = 0;
					$dbi->where("vID", $parentIds, "IN");
					if($ySubeKodu > 0) $dbi->where("subeKodu", $ySubeKodu);
					$parentInfos = $dbi->get(_VELILER_, null, "v_tc_kimlik_no");
					
					foreach($parentInfos as $parentInfo)
					{
						if($ySubeKodu > 0) $dbi->where("schoolId", $ySubeKodu);
						$dbi->where("voter", $parentInfo["v_tc_kimlik_no"]);
						$dbi->where("surveyId", $survey["Id"]);
						$dbi->where("answerId", "0", "!=");
						$dbi->where("answerPoint", "0", "!=");
						$answeredParentsIds = $dbi->getOne(_PERFORMANCE_SURVEYS_ASSIGNMENT_QUESTIONS_STUDENTS_, "voter");
						
						if(!empty($answeredParentsIds)) $j++;
					}
					
					$nofAnsweredParents = $j;
				}
				else 
				{
					if($ySubeKodu > 0) $dbi->where("schoolId", $ySubeKodu);
					//$dbi->where("studentId", $parentIds, "IN");
					$dbi->where("parentId", $parentIds, "IN");
					$dbi->where("surveyId", $survey["Id"]);
					$dbi->where("answerId", "0", "!=");
					$dbi->where("answerPoint", "0", "!=");
					$answeredParents = $dbi->get(_PERFORMANCE_SURVEYS_ASSIGNED_QUESTIONS_, null, "DISTINCT(parentId)");
					
					$nofAnsweredParents = empty($answeredParents) ? 0 : sizeof($answeredParents);	
				}
				
				//nof assigned parents
				$surveys[$k]["nofAssignedParents"] = str_replace("{?}", 
					$nofAssignedParents, _FORM_WAS_ASSIGNED_TO_X_PARENTS);
					
				//nof answered parents
				$surveys[$k]["nofAnsweredParents"] = str_replace("{?}", 
					$nofAnsweredParents, _X_PARENTS_ANSWERED);

				//nof unanswered parents
				if($nofAssignedParents > $nofAnsweredParents) {
					$surveys[$k]["nofUnAnsweredParents"] = str_replace("{?}", 
						$nofAssignedParents - $nofAnsweredParents, _X_PARENTS_DID_NOT_ANSWER);
				}
				else {
					$surveys[$k]["nofUnAnsweredParents"] = 0;
				}
			}

			/**
			 * check assignments for personnel
			 **/
			//assigned personnel
			if($ySubeKodu > 0) $dbi->where("schoolId", $ySubeKodu);
			$dbi->where("perId", "0", "!=");
			$dbi->where("surveyId", $survey["Id"]);
			$perIds = $dbi->getValue(_PERFORMANCE_SURVEYS_ASSIGNMENT_, "DISTINCT(perId)", null);

			//nof assigned personnel
			$nofAssignedPersonnel = empty($perIds) ? 0 : sizeof($perIds);

			//answered personnel
			if(!empty($perIds))
			{
				if($ySubeKodu > 0) $dbi->where("schoolId", $ySubeKodu);
				$dbi->where("perId", $perIds, "IN");
				$dbi->where("surveyId", $survey["Id"]);
				$dbi->where("answerId", "0", "!=");
				$dbi->where("answerPoint", "0", "!=");
				$answeredPersonnel = $dbi->get(_PERFORMANCE_SURVEYS_ASSIGNED_QUESTIONS_, null, "DISTINCT perId");

				//nof answered personnel
				$nofAnsweredPersonnel = empty($answeredPersonnel) ? 0 : sizeof($answeredPersonnel);

				//nof assigned personnel
				$surveys[$k]["nofAssignedPersonnel"] = str_replace("{?}", 
					$nofAssignedPersonnel, _FORM_WAS_ASSIGNED_TO_X_PERSONNEL);
					
				//nof answered personnel
				$surveys[$k]["nofAnsweredPersonnel"] = str_replace("{?}", 
					$nofAnsweredPersonnel, _X_PERSONNEL_ANSWERED);

				//nof unanswered personnel
				if($nofAssignedPersonnel > $nofAnsweredPersonnel) {
					$surveys[$k]["nofUnAnsweredPersonnel"] = str_replace("{?}", 
						$nofAssignedPersonnel - $nofAnsweredPersonnel, _X_PERSONNEL_DID_NOT_ANSWER);
				}
				else {
					$surveys[$k]["nofUnAnsweredPersonnel"] = 0;
				}
			}
		}
		
		return $surveys;
	}
}
