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

use Schoost\Schools;

class Levels {
    
    /* function */
	function getLevels()
	{
        global $dbi, $ySubeKodu;

	}

    /* function */
	function getSchoolLevels()
	{
        global $dbi, $ySubeKodu;
        
        //get school info
        $s = new Schools();
        $s->setSchoolId($ySubeKodu);
        $sInfo = $s->getSchoolInfo();
        
        $slevels = explode(",", $sInfo["classLevels"]);
        
		//show class levels
		$dbi->where("seviyeID", $slevels, "IN");
		$dbi->where("aktif", "1");
		$dbi->orderBy("siralama", "asc");
        $schoolLevels = $dbi->get(_SINIF_SEVIYELERI_, null, array("seviyeID", "seviye"));
        
        return $schoolLevels;
	}

}
