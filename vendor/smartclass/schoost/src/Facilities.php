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

class Facilities {
    
    private $facilityId = 0;
    private $settings = array();
    
    function __construct()
    {
    }
    
	/* function */
	function setFacilityId($Id)
	{
		$this->facilityId = $Id;
		
		return $this;
	}

	/* function */
	function getFacilityId()
	{
		return $this->facilityId;
	}

	/* function */
	function getFacilities()
	{
		global $dbi, $ySubeKodu, $yCampusID;

		if(empty($yCampusID)) $yCampusID = fnSchoolCampusId($ySubeKodu);

		$dbi->where("(schoolId=? OR campusId=?)", array("$ySubeKodu", "$yCampusID"));
		$facilities = $dbi->get(_ROOMS_);

		return $facilities;
	}

	/* function */
	function getFacilityInfo()
	{
		global $dbi, $ySubeKodu;
		
		//get calendar info
		// $dbi->where("id", $this->calendarId);
		// $calendarInfo = $dbi->getOne(_CALENDARS_);

		// return $calendarInfo;
	}
	
	/* function */
	function getSettings()
	{
		global $dbi, $ySubeKodu;
		
		$dbi->where("branchID", $ySubeKodu);
		$this->settings = $dbi->getOne(_APPOINTMENT_SETTINGS_);
		
		return $this->settings;
	}
	
	/* function */
	function getReservations()
	{
		global $dbi, $ySubeKodu;

	}
	
}
