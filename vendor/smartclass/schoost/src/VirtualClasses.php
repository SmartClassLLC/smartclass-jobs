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

class VirtualClasses {
    
    private $total = 0;
    
    function __construct()
    {
        global $dbi, $ySubeKodu;

		//get total records
		$dbi->where("schoolId", $ySubeKodu);
		$this->total = $dbi->getValue(_VIRTUAL_CLASSES_, "COUNT(Id)");

    }
    
    /* function */
	function getTotal()
	{
		return $this->total;
	}
    
	/* function */
	function getVirtualClasses()
	{
        global $dbi, $ySubeKodu, $simsDateTime;
        
        //make all meetings passive if they are already expired
        $dbi->where("dersBitis", $simsDateTime, "<")->update(_VIRTUAL_CLASSES_, array("active" => "0"));

        //get meetings
        $virtualClasses = $dbi->where("schoolId", $ySubeKodu)->orderBy("active", "desc")->orderBy("dersBaslangic", "desc")->get(_VIRTUAL_CLASSES_);

		return $virtualClasses;
	}

}
