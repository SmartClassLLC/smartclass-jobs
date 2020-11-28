<?php

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

//check if the file is tried to be accessed directly
if(stristr($_SERVER['SCRIPT_NAME'], "exams.php")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

function MaddeGuclukIndeksi($soruNo, $dersKodu, $sinavKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `".$dersOptikTanimi."` FROM "._EXAM_FILE_INFO_." WHERE `ogrenci_no`='".$ogrenciNo."' AND `sinavKodu`='".$sinavKodu."'"));
    return $row[$dersOptikTanimi];
}

function stsOgrenciDersCevapAnahtari($ogrenciNo, $sinavKodu, $dersOptikTanimi)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `".$dersOptikTanimi."` FROM "._EXAM_FILE_INFO_." WHERE `ogrenci_no`='".$ogrenciNo."' AND `sinavKodu`='".$sinavKodu."'"));
    return $row[$dersOptikTanimi];
}

function sinavDersCevapAnahtari($sinavKodu, $dersKodu, $kitapcikTuru)
{
    global $dbi, $activeSeasonDb;

	if(empty($kitapcikTuru)) return false;
	
	//key for the booklet A
	$dbi->where("dersKodu", $dersKodu);
	$dbi->where("kitapcikTuru", "A");
	$dbi->where("sinavKodu", $sinavKodu);
	$key4A = $dbi->getValue($activeSeasonDb.".sinavlar_cevap_anahtari", "cevapAnahtari");
    
    if($kitapcikTuru == "A")
    {
    	return $key4A;
    }
    else
    {
		//key for the booklet
		$dbi->where("dersKodu", $dersKodu);
		$dbi->where("kitapcikTuru", $kitapcikTuru);
		$dbi->where("sinavKodu", $sinavKodu);
		$key4Booklet = $dbi->getValue($activeSeasonDb.".sinavlar_cevap_anahtari", "cevapAnahtari");
    	
    	if(empty($key4Booklet))
    	{
    		$key = array();
    		
			//check if a mapping exists
			$dbi->where("sinavKodu", $sinavKodu);
			$dbi->where("dersKodu", $dersKodu);
			$dbi->orderBy("questionNoX", "ASC");
			$mappings = $dbi->get($activeSeasonDb.".sinav_kitapcik_soru_eslesmeleri", null, "soruNoA AS questionNoA, soruNo". $kitapcikTuru . " AS questionNoX");
			
			foreach ($mappings as $k => $mapping)
			{
				$key[] = substr($key4A, $mapping["questionNoA"] - 1, 1);
			}
    		$key4Booklet = implode("", $key);
    	}
    	
    	return $key4Booklet;
    }
}

function sinavDersKitapcikTuru($ogrID, $sinavKodu, $dersKodu)
{
    global $dbi, $ySubeKodu, $activeSeasonDb;
    
    //$dbi->where("subeKodu", $ySubeKodu);
    $dbi->where("sinavKodu", $sinavKodu);
    $dbi->where("dersKodu", $dersKodu);
    $dbi->where("ogrID", $ogrID);
    $kitapcikTuru = $dbi->getValue($activeSeasonDb.".sinav_sonuclari_netler", "kitapcikTuru");

    return $kitapcikTuru;
}

function sinavOgrenciDersCevapAnahtari($ogrID, $dersKodu, $sinavKodu, $kitapcikTuru)
{
	global $dbi, $activeSeasonDb;

    $dbi->where("sinavKodu", $sinavKodu);
    $dbi->where("dersKodu", $dersKodu);
    $dbi->where("kitapcikTuru", $kitapcikTuru);
    $dbi->where("ogrID", $ogrID);
    $mcAnswers = $dbi->getValue($activeSeasonDb.".sinav_sonuclari_netler", "mcAnswers");
	
	return $mcAnswers;
}

function dersSoruSayisi($sinavTuruKodu, $dersKodu)
{
	global $dbi;
	
    $dbi->where("dersBransKodu", $dersKodu);
    $dbi->where("sinavTuruKodu", $sinavTuruKodu);
    $soruSayisi = $dbi->getValue(_EXAM_TYPE_COURSES_, "soruSayisi");
	
	return $soruSayisi;
}

function doluCevapSayisi($sonuc)
{
	$uzunluk = strlen($sonuc);
	$dolu_sayisi = 0;
	for($i=0; $i < $uzunluk; $i++)
	{
		if($sonuc[$i] != ' ') $dolu_sayisi++;
	}
	return $dolu_sayisi;
}

function examMappedKey($anahtarA, $kitapcikTuru, $dersKodu, $sinavKodu)
{
	global $dbi;
	
	if(empty($anahtarA)) return '0';
	if($kitapcikTuru == "A" || empty(trim($kitapcikTuru))) return '0';
	if(empty($dersKodu)) return '0';
	if(empty($sinavKodu)) return '0';

	//check if a mapping exists
	$dbi->where("sinavKodu", $sinavKodu);
	$dbi->where("dersKodu", $dersKodu);
	$dbi->orderBy("soruNoA", "ASC");
	$mappings = $dbi->get(_EXAM_BOOKLET_QUESTION_MATCHES_, null, "soruNoA AS questionNoA, soruNo". $kitapcikTuru . " AS questionNoX");
	
	foreach ($mappings as $k => $mapping)
	{
		$mappings[$k]["key"] = substr($anahtarA, $k, 1);
	}
	
	//return mappings
	return $mappings;
}

function dogruCevapSayisi($anahtar, $cevap, $subjectKeyA = "", $studentID = "", $sinifKodu = "", $subjectID = "", $examID = "")
{
	global $dbi, $ySubeKodu;
	
	//set the number of correct answers
	$dogru_sayisi = 0;	

	//delete previous item data if exists
	$dbi->where("ogrID", $studentID);
	$dbi->where("dersKodu", $subjectID);
	$dbi->where("sinavKodu", $examID);
	$dbi->delete(_EXAM_RESULTS_QUESTION_ANALYSIS_);
	
	if(is_array($anahtar))
	{
		//set item data
		$queryData = array();
		
		//start checking answers
		foreach($anahtar as $i => $key)
		{
			$studentAnswer = $cevap[$key["questionNoX"] - 1];
			
			if($key["key"] == " " || $key["key"] == "X" || $key["key"] == "x") $dogruYanlis = NULL; //empty key or cancelled question
			else if ($studentAnswer == " ") $dogruYanlis = NULL; //empty answer
			else if ($key["key"] == "H") $dogruYanlis = "T"; //all answers are correct
			else if ($key["key"] == "K" && ($studentAnswer == "A" || $studentAnswer == "B")) $dogruYanlis = "T"; //A || B is correct
			else if ($key["key"] == "L" && ($studentAnswer == "A" || $studentAnswer == "C")) $dogruYanlis = "T"; //A || C is correct
			else if ($key["key"] == "M" && ($studentAnswer == "A" || $studentAnswer == "D")) $dogruYanlis = "T"; //A || D is correct
			else if ($key["key"] == "N" && ($studentAnswer == "A" || $studentAnswer == "E")) $dogruYanlis = "T"; //A || E is correct
			else if ($key["key"] == "P" && ($studentAnswer == "B" || $studentAnswer == "C")) $dogruYanlis = "T"; //B || C is correct
			else if ($key["key"] == "Q" && ($studentAnswer == "B" || $studentAnswer == "D")) $dogruYanlis = "T"; //B || D is correct
			else if ($key["key"] == "R" && ($studentAnswer == "B" || $studentAnswer == "E")) $dogruYanlis = "T"; //B || E is correct
			else if ($key["key"] == "S" && ($studentAnswer == "C" || $studentAnswer == "D")) $dogruYanlis = "T"; //C || D is correct
			else if ($key["key"] == "T" && ($studentAnswer == "C" || $studentAnswer == "E")) $dogruYanlis = "T"; //C || E is correct
			else if ($key["key"] == "V" && ($studentAnswer == "D" || $studentAnswer == "E")) $dogruYanlis = "T"; //D || E is correct
			else if ($key["key"] == $studentAnswer) $dogruYanlis = "T"; //correct answer
			else $dogruYanlis = "F";
			
			//if it is true then increment nof trues
			if($dogruYanlis == "T") $dogru_sayisi++;
		
			//get objective id
			$dbi->where("dersKodu", $subjectID);
			$dbi->where("sinavKodu", $examID);
			$dbi->where("soruNo", $key["questionNoA"]);
			$objectiveIds = $dbi->getValue(_EXAM_OBJECTIVES_, "GROUP_CONCAT(DISTINCT `kazanimID` SEPARATOR ',')");

			//make queries
			$queryData[] = array(
				"ogrID"			=> $studentID,
				"sinifKodu"		=> $sinifKodu,
				"dersKodu"		=> $subjectID,
				"soruNo"		=> $key["questionNoA"],
				"objectiveIds"	=> $objectiveIds,
				"dogruSecenek"	=> $key["key"],
				"ogrenciSecenek"=> $studentAnswer,
				"ogrenciCevabi"	=> $dogruYanlis,
				"sinavKodu"		=> $examID,
				"subeKodu"		=> $ySubeKodu
			);
		}
		
		//save item values
		$dbi->insertMulti(_EXAM_RESULTS_QUESTION_ANALYSIS_, $queryData);
	}
	else
	{
		//set item data
		$queryData = array();

		//get the length of the key
		$uzunluk = strlen($anahtar);
		
		//start checking answers
		for($i=0; $i < $uzunluk; $i++)
		{
			$soruNo = intval($i + 1);
			
			if($anahtar[$i] == " " || $anahtar[$i] == "X" || $anahtar[$i] == "x") $dogruYanlis = NULL; //empty key or cancelled question
			else if ($cevap[$i] == " ") $dogruYanlis = NULL; //empty answer
			else if ($anahtar[$i] == "H") $dogruYanlis = "T"; //all answers are correct
			else if ($anahtar[$i] == "K" && ($cevap[$i] == "A" || $cevap[$i] == "B")) $dogruYanlis = "T"; //A or B is correct
			else if ($anahtar[$i] == "L" && ($cevap[$i] == "A" || $cevap[$i] == "C")) $dogruYanlis = "T"; //A or C is correct
			else if ($anahtar[$i] == "M" && ($cevap[$i] == "A" || $cevap[$i] == "D")) $dogruYanlis = "T"; //A or D is correct
			else if ($anahtar[$i] == "N" && ($cevap[$i] == "A" || $cevap[$i] == "E")) $dogruYanlis = "T"; //A or E is correct
			else if ($anahtar[$i] == "P" && ($cevap[$i] == "B" || $cevap[$i] == "C")) $dogruYanlis = "T"; //B or C is correct
			else if ($anahtar[$i] == "Q" && ($cevap[$i] == "B" || $cevap[$i] == "D")) $dogruYanlis = "T"; //B or D is correct
			else if ($anahtar[$i] == "R" && ($cevap[$i] == "B" || $cevap[$i] == "E")) $dogruYanlis = "T"; //B or E is correct
			else if ($anahtar[$i] == "S" && ($cevap[$i] == "C" || $cevap[$i] == "D")) $dogruYanlis = "T"; //C or D is correct
			else if ($anahtar[$i] == "T" && ($cevap[$i] == "C" || $cevap[$i] == "E")) $dogruYanlis = "T"; //C or E is correct
			else if ($anahtar[$i] == "V" && ($cevap[$i] == "D" || $cevap[$i] == "E")) $dogruYanlis = "T"; //d or E is correct
			else if ($anahtar[$i] == $cevap[$i]) $dogruYanlis = "T"; //correct answer
			else $dogruYanlis = "F";
			
			//if it is true then increment nof trues
			if($dogruYanlis == "T") $dogru_sayisi++;

			//get objective id
			$dbi->where("dersKodu", $subjectID);
			$dbi->where("sinavKodu", $examID);
			$dbi->where("soruNo", $soruNo);
			$objectiveIds = $dbi->getValue(_EXAM_OBJECTIVES_, "GROUP_CONCAT(DISTINCT `kazanimID` SEPARATOR ',')");
			
			//make queries
			$queryData[] = array(
				"ogrID"			=> $studentID,
				"sinifKodu"		=> $sinifKodu,
				"dersKodu"		=> $subjectID,
				"soruNo"		=> $soruNo,
				"objectiveIds"	=> $objectiveIds,
				"dogruSecenek"	=> $anahtar[$i],
				"ogrenciSecenek"=> $cevap[$i],
				"ogrenciCevabi"	=> $dogruYanlis,
				"sinavKodu"		=> $examID,
				"subeKodu"		=> $ySubeKodu
			);
		}

		//if the booklet is A then insert it into the db else do not
		if($anahtar == $subjectKeyA) $dbi->insertMulti(_EXAM_RESULTS_QUESTION_ANALYSIS_, $queryData);
	}
		
	//return # of trues
	return $dogru_sayisi;
}

function bosCevapSayisi($anahtar, $answers)
{
	if(is_array($anahtar)) $examKey = implode("", array_column($anahtar, "key"));
	else $examKey = $anahtar;

	$uzunluk = strlen($examKey);
	$bos_sayisi = 0;
	
	for($i=0; $i < $uzunluk; $i++)
	{
		if($examKey[$i] != " " && $examKey[$i] != "X" && $examKey[$i] != "x" && $examKey[$i] != "H" && $examKey[$i] != "h" && $answers[$i] == " ") $bos_sayisi++;
	}
	
	return $bos_sayisi;
}

function iptalEdilenSoruSayisi($anahtar)
{
	if(is_array($anahtar)) $examKey = implode("", array_column($anahtar, "key"));
	else $examKey = $anahtar;

	$uzunluk = strlen($examKey);

	$iptal_sayisi = 0;
	for($i=0; $i < $uzunluk; $i++)
	{
		if($examKey[$i] == "X" OR $examKey[$i] == "x") $iptal_sayisi++;
	}
	return($iptal_sayisi);
}

function sinifDersOrtalamasi($sinifKodu, $dersKodu, $sinavKodu)
{
	global $dbi, $activeSeasonDb;
	
    $dbi->where("dersKodu", $dersKodu);
    $dbi->where("sinavKodu", $sinavKodu);
    $dbi->where("sinifKodu", $sinifKodu);
    $sinifOrtalamasi = $dbi->getValue($activeSeasonDb. ".sinav_net_ortalama_sinif", "sinifOrtalamasi");

    return number_format($sinifOrtalamasi, 1);
}

function subeDersOrtalamasi($dersKodu, $sinavKodu, $subeKodu)
{
	global $dbi, $activeSeasonDb;
	
    $dbi->where("dersKodu", $dersKodu);
    $dbi->where("sinavKodu", $sinavKodu);
    $dbi->where("subeKodu", $subeKodu);
    $subeOrtalamasi = $dbi->getValue($activeSeasonDb. ".sinav_net_ortalama_okul", "subeOrtalamasi");

    return number_format($subeOrtalamasi, 1);
}

function genelDersOrtalamasi($dersKodu, $sinavKodu)
{
	global $dbi, $activeSeasonDb;
	
    $dbi->where("dersKodu", $dersKodu);
    $dbi->where("sinavKodu", $sinavKodu);
    $genelOrtalama = $dbi->getValue($activeSeasonDb. ".sinav_net_ortalama_genel", "genelOrtalama");

    return number_format($genelOrtalama, 1);
}

function sinifPuanOrtalamasi($puanTuruKodu, $sinavKodu, $sinifKodu)
{
	global $dbi;
	
    $dbi->where("puanTuruKodu", $puanTuruKodu);
    $dbi->where("sinavKodu", $sinavKodu);
    $dbi->where("sinifKodu", $sinifKodu);
    $sinifOrtalamasi = $dbi->getValue(_EXAM_CLASS_SCORE_AVERAGES_, "sinifOrtalamasi");

    return number_format($sinifOrtalamasi, 1);
}

function subePuanOrtalamasi($puanTuruKodu, $sinavKodu, $subeKodu)
{
	global $dbi;
	
    $dbi->where("puanTuruKodu", $puanTuruKodu);
    $dbi->where("sinavKodu", $sinavKodu);
    $dbi->where("subeKodu", $subeKodu);
    $subeOrtalamasi = $dbi->getValue(_EXAM_BRANCH_SCORE_AVERAGES_, "subeOrtalamasi");

    return number_format($subeOrtalamasi, 1);
}

function genelPuanOrtalamasi($puanTuruKodu, $sinavKodu)
{
	global $dbi;

    $dbi->where("puanTuruKodu", $puanTuruKodu);
    $dbi->where("sinavKodu", $sinavKodu);
    $genelOrtalama = $dbi->getValue(_EXAM_GENERAL_SCORE_AVERAGES_, "genelOrtalama");

    return number_format($genelOrtalama, 1);
}

function sinifKatilim($sinavKodu, $sinifKodu)
{
	global $dbi;

	$dbi->where("sinifKodu", $sinifKodu);
	$dbi->where("sinavKodu", $sinavKodu);
	$katilim = $dbi->getValue(_EXAM_CLASS_STUDENTS_, "sinifKatilim");
	
	return $katilim;
}

function subeKatilim($sinavKodu, $subeKodu)
{
	global $dbi;

	$dbi->where("subeKodu", $subeKodu);
	$dbi->where("sinavKodu", $sinavKodu);
	$katilim = $dbi->getValue(_EXAM_BRANCH_STUDENTS_, "subeKatilim");
	
	return $katilim;
}

function genelKatilim($sinavKodu)
{
	global $dbi;
	
	$dbi->where("sinavKodu", $sinavKodu);
	$katilim = $dbi->getValue(_EXAM_ALL_STUDENTS_, "genelKatilim");
	
	return $katilim;
}

function ItemAnalysisObjID2UnitTitle($items)
{
	global $db;
	$returnArray = array();
	$itemsArray = explode(",", $items);
	foreach ($itemsArray as $key => $value) {
		$row = $db->sql_fetchrow($db->sql_query("SELECT `uniteKodu` FROM "._EGITIM_UNITE_KAZANIMLAR_." WHERE `kID`='".$value."'"));		
		$returnArray[] = CurriculumUnitTitle($row["uniteKodu"]);
	}
	array_unique($returnArray);
	return implode(" ", $returnArray);
}

function pointMaximumScore($pID, $examID)
{
	global $dbi, $examsDB;
	
	//exam database table for results
	$examDataBaseTableWDB = $examsDB. ".exam_results_". $examID;

	//get max point
	$maxPoint = $dbi->getValue($examDataBaseTableWDB, "MAX(P_" . $pID . ")");
	
	//return
	return $maxPoint;
}

function karakterleriTrYap($yazi)
{
	//$yazi = str_replace("˜", "Ý", $yazi);
	//$yazi = str_replace("¦", "Ð", $yazi);
	//$yazi = str_replace("™", "Ö", $yazi);
	//$yazi = str_replace("š", "Ü", $yazi);
	//$yazi = str_replace("€", "Ç", $yazi);
	//$yazi = str_replace("ž", "Þ", $yazi);
	return $yazi;
}

function karakterleriUTF8Yap($yazi)
{
	$yazi = str_replace("İ", "I", $yazi);
	$yazi = str_replace("Ğ", "G", $yazi);
	$yazi = str_replace("Ö", "O", $yazi);
	$yazi = str_replace("Ü", "U", $yazi);
	$yazi = str_replace("Ç", "C", $yazi);
	$yazi = str_replace("Ş", "S", $yazi);
	return $yazi;
}

?>
