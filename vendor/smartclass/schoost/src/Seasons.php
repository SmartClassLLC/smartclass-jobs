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

class Seasons {

    private $seasons = array();
    private $previousSeasons = array();
    
    /* function */
	function getSeasons()
	{
        global $dbi;
            
		$dbi->where("aktif", "on");
		$dbi->orderBy("donem", "DESC");
		$this->seasons = $dbi->get(_DONEMLER_, null, "donem, veritabani");
		return $this->seasons;
		
	}

    /* function */
	function getPreviousSeasons()
	{
        global $dbi, $dbname2;
            
		$dbi->where("veritabani", $dbname2, "<");
		$dbi->where("aktif", "on");
		$dbi->orderBy("donem", "DESC");
		$this->previousSeasons = $dbi->get(_DONEMLER_, null, "donem, veritabani");
		
		return $this->previousSeasons;
	}

}
?>
