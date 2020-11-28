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

class Personnel {
    
    private $campusId = 0;
    private $schoolId = 0;
    private $positionId = 0;
    private $positions = array();
    private $personnel = array();
    private $personnelCategoryTitle = array();
    private $positionInfo = array();
    private $perId = 0;
    private $userInfo = false;

	/* function */
	function setPersonnelId($perId)
	{
		$this->perId = $perId;
	}

	/* function */
	function addUserInfo()
	{
		$this->userInfo = true;
	}

	/* function */
	function getAllPersonnel()
	{
        global $dbi, $ySubeKodu;
		
		$dbi->join(_PERSONNEL_CATEGORIES_. " c", "a.`cat_code`=c.`cat_id`", "LEFT")
			->join(_PERSONEL_DEPARTMENTS_. " d", "a.`dep_code`=d.`Id`", "LEFT")
			->orderBy("a.adi_soyadi", "asc");
		
		if($ySubeKodu != "0") $dbi->where("a.SubeKodu", $ySubeKodu);
		
		$allPersonnel = $dbi->get(_PERSONEL_." a", null, "a.*, c.`cat_name`, d.`depTitle`, d.`depType`");

		return $allPersonnel;
	}

	/* function */
	function getPersonnel($manager = false, $guide = false, $teacher = false, $club = false, $intern = false)
	{
		global $dbi, $ySubeKodu;

		$whereQuery = array(); $whereValue = array();
		
		if($manager){ $whereQuery[] = "p.yonetici=?"; $whereValue[] = "1"; }
		if($teacher){ $whereQuery[] = "p.ogretmen=?";  $whereValue[] = "1"; }
		if($guide){ $whereQuery[] = "p.rehber=?";  $whereValue[] = "1"; }
		if($club){ $whereQuery[] = "p.kulup=?";  $whereValue[] = "1"; }
		if($intern){ $whereQuery[] = "p.etut_verilebilir=?";  $whereValue[] = "1"; }
		
		$whereQueryText = implode(" OR ", $whereQuery);
		
		if($this->userInfo) $dbi->join(_USERS_. " u", "u.aid=p.tckimlikno", "LEFT");
		$dbi->where("(". $whereQueryText. ")", $whereValue);
		$dbi->where("p.aktif", "0", "!=");
		
		if($ySubeKodu != "0") $dbi->where("(p.SubeKodu=? OR p.perID IN (SELECT perId FROM " . _PERSONEL_TRANSFER_ . " WHERE transferSchoolId=? AND active=?))", array("$ySubeKodu", "$ySubeKodu", "on"));
		
		if(empty($ySubeKodu)) $dbi->orderBy("p.SubeKodu", "asc");
		
		$dbi->orderBy("p.yonetici", "desc");
		$dbi->orderBy("p.rehber", "desc");
		$dbi->orderBy("p.ogretmen", "desc");
		$dbi->orderBy("p.adi_soyadi", "asc");
		if($this->userInfo) $personnel = $dbi->get(_PERSONEL_. " p", null, "p.*, u.id, u.aid, u.userType");
		else $personnel = $dbi->get(_PERSONEL_. " p", null, "p.*");
		
		return $personnel;
	}

	/* function */
	function getNonAcademicPersonnel()
	{
		global $dbi, $ySubeKodu;
		
		if($ySubeKodu != "0") $dbi->where("(SubeKodu=? OR perID IN (SELECT perId FROM " . _PERSONEL_TRANSFER_ . " WHERE transferSchoolId=? AND active=?))", array("$ySubeKodu", "$ySubeKodu", "on"));
		
		$dbi->where("(yonetici=? AND rehber=? AND ogretmen=? AND kulup=?)", array("0", "0", "0", "0"));
		$dbi->where("aktif", "0", "!=");
		//$dbi->where("SubeKodu", $ySubeKodu);
		$dbi->orderBy("adi_soyadi", "asc");
		$personnel = $dbi->get(_PERSONEL_);
		
		return $personnel;
	}

	/* function */
    function getPersonnelInfo()
    {
    	global $dbi, $dbname2;

    	if (!$dbname2) return [];

    	//get personnel info
		$personnnelInfo = $dbi	->join(_PERSONNEL_CATEGORIES_. " c", "a.`cat_code`=c.`cat_id`", "LEFT")
								->join(_PERSONEL_DEPARTMENTS_. " d", "a.`dep_code`=d.`Id`", "LEFT")
								->where("a.`perID`", $this->perId)
								->getOne(_PERSONEL_." a", "a.*, c.`cat_name`, d.`depTitle`, d.`depType`");

		//get category name translated
		$personnnelInfo["categoryName"] = translateWord($personnnelInfo["cat_name"]);

		//add photo file
		$personnnelInfo["photoFile"] = showPhoto($personnnelInfo["foto"], $personnnelInfo["cinsiyeti"], "", "", true);
		
		//add rounded photo
		$personnnelInfo["roundedPhoto"] = showPhoto($personnnelInfo["foto"], $personnnelInfo["cinsiyeti"], "45px", "img-rounded", false);

		//add circle photo
		$personnnelInfo["circlePhoto"] = showPhoto($personnnelInfo["foto"], $personnnelInfo["cinsiyeti"], "45px", "img-circle", false);
		
		//return as array
		return $personnnelInfo;
    }
    
	/* function */
    function getPersonnelName($perId)
    {
    	global $dbi;
    	
    	//get personnel info
		$personnnelInfo = $dbi->where("perID", $perId)->getOne(_PERSONEL_, "adi_soyadi");

		//return name
		return $personnnelInfo["adi_soyadi"];
    }
    
	/* function */
	function getPositions($campusId = "0", $schoolId = "0")
	{
        global $db;
		
        $this->campusId = $campusId;
        $this->schoolId = $schoolId;

		if($this->campusId == "0" && $this->schoolId == "0") $this->positions = $db->sql_fetchrowset($db->sql_query("SELECT p.`Id`, p.`permanentPosition` AS `positionId`, c.`cat_name` AS `positionTitle` FROM "._NORM_KADRO_." p LEFT JOIN "._PERSONNEL_CATEGORIES_." c ON p.`permanentPosition`=c.`cat_id` WHERE p.`campusId`='0' ORDER BY c.`view_order` ASC"), MYSQL_ASSOC);		
		else if($this->campusId != "0" && $this->schoolId == "0") $this->positions = $db->sql_fetchrowset($db->sql_query("SELECT p.`Id`, p.`permanentPosition` AS `positionId`, c.`cat_name` AS `positionTitle` FROM "._NORM_KADRO_." p LEFT JOIN "._PERSONNEL_CATEGORIES_." c ON p.`permanentPosition`=c.`cat_id` WHERE p.`campusId`='".$this->campusId."' ORDER BY c.`view_order` ASC"), MYSQL_ASSOC);
		else $this->positions = $db->sql_fetchrowset($db->sql_query("SELECT p.`Id`, p.`permanentPosition` AS `positionId`, c.`cat_name` AS `positionTitle` FROM "._NORM_KADRO_." p LEFT JOIN "._PERSONNEL_CATEGORIES_." c ON p.`permanentPosition`=c.`cat_id` WHERE p.`campusId`='0' AND p.`schoolId`='".$this->schoolId."' ORDER BY c.`view_order` ASC"), MYSQL_ASSOC);
		
		return $this->positions;
	}

	/* function */
	function getPositionInfo($positionId)
	{
        global $db;
		
        $this->positionId = $positionId;
		
		$this->positionInfo = $db->sql_fetchrowset($db->sql_query("SELECT * FROM "._NORM_KADRO_." WHERE `Id`='".$this->positionId."'"), MYSQL_ASSOC);
		
		return $this->positionInfo[0];
	}

	/* function */
	function getPersonnelCategoryTitle($categoryId, $original = false)
	{
        global $db;
		
		$this->personnelCategoryTitle = $db->sql_fetchrowset($db->sql_query("SELECT `cat_name` FROM "._PERSONNEL_CATEGORIES_." WHERE `cat_id`='".$categoryId."'"), MYSQL_ASSOC);
		
		if($original) return $this->personnelCategoryTitle[0]["cat_name"];
		else return translateWord($this->personnelCategoryTitle[0]["cat_name"]);
	}

	/* function */
	function getPersonnelCategories()
	{
        global $dbi;
		
		$categories = $dbi->orderBy("view_order", "asc")->get(_PERSONNEL_CATEGORIES_);

		return $categories;
	}

	/* function */
	function getPersonnelDepartments($depType = "")
	{
        global $dbi;
		
		$dbi->orderBy("depType", "asc")->orderBy("depTitle", "asc");
		
		if($depType) $dbi->where("depType", $depType);
		
		$departments = $dbi->get(_PERSONEL_DEPARTMENTS_);

		return $departments;
	}
}