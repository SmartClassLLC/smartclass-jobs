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

class Parents {

    private $total = 0;
    private $studentId = 0;
    private $parentId = 0;
    
    function __construct()
    {
        global $dbi, $ySubeKodu;

		//get total records
		$dbi->join(_OGRENCILER_ . " o", "o.ogrID = v.ogrID", "INNER");
		$dbi->where("o.KayitliMi", "1");
		$dbi->where("o.SubeKodu", $ySubeKodu);
		$this->total = $dbi->getValue(_VELILER_. " v", "COUNT(v.vID)");
    }
    
    /* function */
	function getTotal()
	{
		return $this->total;
	}

	/* function */
	function getParents($page = 0, $limit = 8, $filters = array())
	{
        global $dbi, $ySubeKodu;

		//join tables
		$dbi->join(_OGRENCILER_ . " o", "o.ogrID = v.ogrID", "INNER");

		//apply filters
		if(!empty($filters))
		{
			//if(!empty($filters["courses"])) $dbi->where("s.KursTuruKodu", $filters["courses"], "IN");
			//if(!empty($filters["batches"])) $dbi->where("s.SinifKodu", $filters["batches"], "IN");
		}
		
		//other filters
		$dbi->where("o.KayitliMi", "1");
		$dbi->where("o.SubeKodu", $ySubeKodu);

		if(empty($page))
		{
			$prts = $dbi->get(_VELILER_. " v", null, "DISTINCT v.v_tc_kimlik_no, v.adi_soyadi");
		}
		else
		{
			$dbi->pageLimit = $limit;
			$prts = $dbi->paginate(_VELILER_. " v", $page, "DISTINCT v.v_tc_kimlik_no, v.adi_soyadi");
		}
		
		foreach($prt as $k => $prts)
		{
			//student full name
			//$stds[$k]["stdFullName"] = fnStudentName($std["Adi"], $std["IkinciAdi"], $std["Soyadi"]);
			
			//student photo
			//$stds[$k]["stdFullPhoto"] = showPhoto($std["Foto"], $std["Cinsiyeti"], "60px", "img-rounded");
		}
		
		return $prts;
	}
}