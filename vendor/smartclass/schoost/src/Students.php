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

class Students {

    private $total = 0;
    private $studentId = 0;
    
    function __construct()
    {
        global $dbi, $ySubeKodu;

		//get total records
		$dbi->where("KayitliMi", "1");
		$dbi->where("SubeKodu", $ySubeKodu);
		$this->total = $dbi->getValue(_OGRENCILER_, "COUNT(ogrID)");
    }
    
    /* function */
	function setStdId($stdId)
	{
        $this->studentId = $stdId;
	}

    /* function */
	function getTotal()
	{
		return $this->total;
	}

	/* function */
	function getStudents($page = 0, $limit = 8, $filters = array())
	{
        global $dbi, $ySubeKodu;

		//join tables
		$dbi->join(_BATCHES_. " c", "s.SinifKodu=c.sinifID", "LEFT");
		$dbi->join(_OGRENCI_UCRETLERI_. " f", "s.KursTuruKodu=f.kursID", "LEFT");

		//apply filters
		if(!empty($filters))
		{
			if(!empty($filters["courses"])) $dbi->where("s.KursTuruKodu", $filters["courses"], "IN");
			if(!empty($filters["batches"])) $dbi->where("s.SinifKodu", $filters["batches"], "IN");
		}
		
		//get total records
		$dbi->where("s.KayitliMi", "1");
		$dbi->where("s.SubeKodu", $ySubeKodu);

		if(empty($page))
		{
			$stds = $dbi->get(_OGRENCILER_. " s", null, "s.ogrID, s.Foto, s.ogrenciNo, s.Adi, s.IkinciAdi, s.Soyadi, s.TCKimlikNo, s.Cinsiyeti, s.DogumTarihi, s.DogumYeri, s.SinifKodu, s.SubeKodu, c.sinifAdi as stdBatchTitle, s.KursTuruKodu, f.kursAdi as stdCourseTitle, s.KayitTarihi");
		}
		else
		{
			$dbi->pageLimit = $limit;
			$stds = $dbi->paginate(_OGRENCILER_. " s", $page, "s.ogrID, s.Foto, s.ogrenciNo, s.Adi, s.IkinciAdi, s.Soyadi, s.TCKimlikNo, s.Cinsiyeti, s.DogumTarihi, s.DogumYeri, s.SinifKodu, s.SubeKodu, c.sinifAdi as stdBatchTitle, s.KursTuruKodu, f.kursAdi as stdCourseTitle, s.KayitTarihi");
		}
		
		foreach($stds as $k => $std)
		{
			//student full name
			$stds[$k]["stdFullName"] = fnStudentName($std["Adi"], $std["IkinciAdi"], $std["Soyadi"]);
			
			//student photo
			$stds[$k]["stdFullPhoto"] = showPhoto($std["Foto"], $std["Cinsiyeti"], "60px", "img-rounded");
		}
		
		return $stds;
	}
}