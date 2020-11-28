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

class Calendars {
    
    private $calendarId = 0;

	/* function */
	function setCalendarId($calendarId)
	{
		$this->calendarId = $calendarId;
	}

	/* function */
	function getCalendars()
	{
		global $dbi, $ySubeKodu;

		//get calendars
		$dbi->where("schoolId", $ySubeKodu);
		$dbi->where("active", "on");
		$dbi->orderBy("calendarOrder", "asc");
		$calendars = $dbi->get(_CALENDARS_);

		return $calendars;
	}

	/* function */
	function getCalendarInfo()
	{
		global $dbi, $ySubeKodu;
		
		//get calendar info
		$dbi->where("id", $this->calendarId);
		$calendarInfo = $dbi->getOne(_CALENDARS_);

		return $calendarInfo;
	}
	
}
