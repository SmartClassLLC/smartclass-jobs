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

use Schoost\Levels;

class Curriculum {
    
    protected $programId = 0;

	/* function */
	function setProgramId($Id)
	{
		//set program Id
		$this->programId = $Id;
		
		return $this;
	}

	/* function */
	function getProgramId()
	{
		return $this->programId;
	}

	/* function */
	function programInfo()
	{
        return $this->batchInfo;
	}

	/* function */
	function getSchoolPrograms()
	{
		global $dbi, $ySubeKodu, $dbnamePrefix;
		
		$l = new Levels();
		$schoolLevels = $l->getSchoolLevels();
		
		foreach($schoolLevels as $k => $l)
		{
			$dbi->where("seviyeKodu", $l["seviyeID"]);
			$dbi->where("aktif", "1");
			$dbi->where("ownerSchool", array("0", "$dbnamePrefix"), "IN");
			$dbi->orderBy("progSirasi", "asc");
			$programs = $dbi->get(_EGITIM_PROGRAMLARI_, null, array("pID", "pAdi", "ownerSchool"));
			
			foreach($programs as $p => $program)
			{
				//get subjects with title
				$dbi->join(_DERS_BRANSLARI_. " d", "d.bID=s.bransKodu", "LEFT");
				$dbi->where("s.programKodu", $program["pID"]);
				$dbi->where("s.aktif", "1");
				$dbi->orderBy("s.dersSirasi", "asc");
				$subjects = $dbi->get(_EGITIM_DERSLER_." s", null, array("s.dID", "s.bransKodu", "d.bransAdi"));

				$programs[$p]["subjects"] = $subjects;
			}
			
			$schoolLevels[$k]["programs"] = $programs;
		}
		
		return $schoolLevels;
	}

}
