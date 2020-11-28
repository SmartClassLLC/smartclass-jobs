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

class Schools {
    
    private $campusId = 0;
    private $schoolId = 0;
    private $campuses = array();
    private $campusInfo = array();
    private $schools = array();
    private $schoolInfo = array();
    
    /* function */
	function setCampusId($Id)
	{
		$this->campusId = $Id;
        
		return $this;
	}

    /* function */
	function getCampusId()
	{
		return $this->campusId;
	}
    
    /* function */
	function setSchoolId($Id)
	{
		$this->schoolId = $Id;
        
		return $this;
	}

    /* function */
	function getSchoolId()
	{
		return $this->schoolId;
	}

    /* function */
	function getCampuses($n = "all")
	{
        global $dbi;
            
        $dbi->where("parent_id", NULL, "IS");
        $dbi->where("stype", "campus");
        $dbi->orderBy("subeAdi", "asc");
		
		$this->campuses = $dbi->get(_SUBELER_);
        
		return $this->campuses;
	}

    /* function */
	function getCampusInfo($Id)
	{
        global $dbi;
        
        if(empty($this->campusId)) $this->campusId = $Id;
        
        $dbi->where("Id", $this->campusId);
		$this->campusInfo = $dbi->getOne(_SUBELER_);
        
		return $this->campusInfo;
	}

    /* function */
    function getCampusTitle($Id)
    {
        if(empty($this->campusId)) $this->campusId = $Id;
        
        if($this->campusId == "0")
        {
            return _GENEL_MUDURLUK;
        }
        else
        {
            $myCampusInfo = $this->getCampusInfo($this->campusId);
            return $myCampusInfo["subeAdi"];
        }
    }

    /* function */
	function getSchoolIds($campusId = "0", $noneCampus = false)
	{
        global $dbi, $seasonSchools;
        
        $this->campusId = $campusId;

        $dbi->where("subeID", $seasonSchools, "IN");        
        $dbi->where("parent_id", $this->campusId);
    	$dbi->orderBy("subeAdi", "ASC");
    	$schoolIds = $dbi->getValue(_SUBELER_, "subeID", null);
        
		return $schoolIds;
	}

    /* function */
	function getSchools($campusId = 0, $noneCampus = false)
	{
        global $dbi, $seasonSchools;
        
        $this->campusId = $campusId;
        
		$dbi->where("subeID", $seasonSchools, "IN");
		$dbi->where("parent_id", $this->campusId);
		$dbi->orderBy("subeAdi", "ASC");
		$this->schools = $dbi->get(_SUBELER_);
        
		return $this->schools;
	}

    /* function */
	function getSchoolInfo($Id = 0)
	{
        global $dbi;
        
        if(empty($this->schoolId)) $this->schoolId = $Id;
        
        $dbi->where("subeID", $this->schoolId);
		$this->schoolInfo = $dbi->getOne(_SUBELER_);
        
		return $this->schoolInfo;
	}

    /* function */
	function getSchoolCampusId($Id)
	{
        global $dbi;
        
        if(empty($this->schoolId)) $this->schoolId = $Id;
        
        $schoolInfo = $this->getSchoolInfo($this->schoolId);
        
		return $schoolInfo["parent_id"];
	}

    /* function */
    function getSchoolTitle($schoolId)
    {
        $this->schoolId = $schoolId;
        
        $mySchoolInfo = $this->getSchoolInfo($this->schoolId);
        
        return $mySchoolInfo["subeAdi"];
    }

    /* function */
	function getCampusesSchools($hq = true, $noneCampus = true, $htmlTag = "")
	{
        global $db;
        
        $returnData = "";
        
        //add hq if it set
        if($hq) $returnData .= "<".$htmlTag."><a class='sims-campuses-schools sims-hq' href='#' data-id='0' data-title='"._GENEL_MUDURLUK."'><i class='fa fa-fw fa-institution'></i> "._GENEL_MUDURLUK."</a></".$htmlTag.">";

        //add campuses
        $dbi->where("parent_id", NULL, "IS");
        $dbi->where("stype", "campus");
        $dbi->orderBy("subeID");
        $campuses = $dbi->get(_SUBELER_);

        foreach($campuses as $campus)
        {
            //echo campus
            $returnData .= "<".$htmlTag."><a class='sims-campuses-schools sims-campuses' href='#' data-id='".$campus["subeID"]."' data-title='".$campus["subeAdi"]."'><i class='fa fa-fw fa-map'></i> ".$campus["subeAdi"]."</a></".$htmlTag.">";

            //get schools for the campus
            $dbi->where("parent_id", $campus["subeID"]);
            $dbi->where("stype", "school");
            $dbi->orderBy("subeAdi");
            $campusSchools = $dbi->get(_SUBELER_);
            
    	    foreach($campusSchools as $campusSchool)
    	    {
    	        $returnData .= "<".$htmlTag." style='padding-left: 7px'><a class='sims-campuses-schools sims-schools' href='#' data-id='".$campusSchool["subeID"]."' data-title='".$campusSchool["subeAdi"]."'><i class='fa fa-fw fa-building-o'></i> ".$campusSchool["subeAdi"]."</a></".$htmlTag.">";
    	    }            
        }
        
		return $returnData;
	}    
}