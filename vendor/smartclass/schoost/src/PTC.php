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

class PTC {
    
    private $settings = array();
    
    function __construct()
    {
    }
    
	/* function */
	function getPTCs()
	{
		global $dbi, $ySubeKodu;

	}

	/* function */
	function getPTCInfo()
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
	function getPersonnel()
	{
		global $dbi, $ySubeKodu;
		
		$dbi->where("aktif", "0", "!=");
		$dbi->where("(yonetici=? OR rehber=? OR ogretmen=?)", array("1", "1", "1"));
		$dbi->where("(SubeKodu=? OR perID IN (SELECT perId FROM " . _PERSONEL_TRANSFER_ . " WHERE transferSchoolId=? AND active=?))", array("$ySubeKodu", "$ySubeKodu", "on"));
		$dbi->orderBy("yonetici", "desc")->orderBy("rehber", "desc")->orderBy("adi_soyadi", "asc");
		$personnel = $dbi->get(_PERSONEL_, null, array("perID", "adi_soyadi", "foto", "cat_code", "translator"));
		
		return $personnel;
	}
	
	/* function */
	function getTranslators()
	{
		global $dbi, $ySubeKodu;

		//get languages
		$l = new Languages();
		$ptcLanguages = $l->getLanguages();
		
		//get personnel that are assigned as translator	
		$dbi->where("aktif", "0", "!=");
		$dbi->where("translator", "1");
		$dbi->where("(SubeKodu=? OR perID IN (SELECT perId FROM ". _PERSONEL_TRANSFER_. " WHERE transferSchoolId=? AND active=?))", array("$ySubeKodu", "$ySubeKodu", "on"));
		$dbi->orderBy("adi_soyadi", "asc");
		$translators = $dbi->get(_PERSONEL_, null, "perID, adi_soyadi, cinsiyeti, ceptel, eposta");
	
		foreach($translators as $k => $translator)
		{
			//get translator's languages
			$dbi->where("subeKodu", $ySubeKodu);
			$dbi->where("translatorID", $translator["perID"]);
			$dbi->orderBy("languageName", "asc");
			$langs = $dbi->getValue(_PTM_TRANSLATORS_LANGS_, "languageID", null);
			
			$langStrings = array();
			if(!empty($langs)) foreach($langs as $lng) $langStrings[] = $ptcLanguages[$lng];
			
			$translators[$k]["langs"] = $langStrings;
		}
		
		return $translators;
	}
	
}
