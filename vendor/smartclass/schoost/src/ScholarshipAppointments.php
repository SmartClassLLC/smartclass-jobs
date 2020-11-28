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

use Schoost\Languages;

class ScholarshipAppointments {
    
    private $settings = array();
    
    function __construct()
    {
    }
    
	/* function */
	function getAppointments()
	{
		global $dbi, $ySubeKodu;

	}

	/* function */
	function getAppointmentInfo()
	{
		global $dbi, $ySubeKodu;
		
		//get calendar info
		//$dbi->where("id", $this->calendarId);
		//$calendarInfo = $dbi->getOne(_CALENDARS_);

		// return $calendarInfo;
	}
	
	/* function */
	function getSettings()
	{
		global $dbi, $ySubeKodu;
		
		$dbi->where("branchID", $ySubeKodu);
		$this->settings = $dbi->getOne(_SCHOLARSHIP_INTERVIEWS_SETTINGS_);
		
		return $this->settings;
	}
	
	/* function */
	function getPersonnel()
	{
		global $dbi, $ySubeKodu;
		
		//get settings
		$this->getSettings();
		$settings = $this->settings;

		//get personnel cats related to the scholarship appointments from settings
		$perCats = explode(",", $settings["personelCategoryId"]);
		
		//get personnel
		$dbi->where("aktif", "0", "!=");
		$dbi->where("cat_code", $perCats, "IN");
		$dbi->where("(SubeKodu=? OR perID IN (SELECT perId FROM " . _PERSONEL_TRANSFER_ . " WHERE transferSchoolId=? AND active=?))", array("$ySubeKodu", "$ySubeKodu", "on"));
		$dbi->orderBy("adi_soyadi", "asc");
		$dbi->orderBy("cat_code", "asc");
		$personnel = $dbi->get(_PERSONEL_, null, array("perID", "adi_soyadi", "foto", "cat_code"));
		
		return $personnel;
	}
	
}
