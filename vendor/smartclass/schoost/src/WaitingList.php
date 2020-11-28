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

class WaitingList {
    
    private $waitingList = array();
    private $nofList = 0;

	function __construct()
	{
		global $dbi, $ySubeKodu;
		
		//set nof intervies
		$dbi->where("SubeKodu", $ySubeKodu);
		$dbi->where("KayitliMi", "3");
		$this->nofList = $dbi->getValue(_OGRENCILER_, "count(ogrID)");
	}
	
    /* function */
	function getNofList()
	{
		return $this->nofList;
	}

}
?>