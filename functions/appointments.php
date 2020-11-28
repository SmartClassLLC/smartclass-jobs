<?php

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

//check if the file is tried to be accessed directly
if(stristr($_SERVER['SCRIPT_NAME'], "appointments.php")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

function TeacherClassForStudent($teacherCode, $ogrID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `ders_brans_code` FROM "._CLASSES_." WHERE `ogretmen_code`='".$teacherCode."' AND `sinif_code` IN (SELECT `SinifKodu` FROM "._OGRENCILER_." WHERE `ogrID` IN (".$ogrID.")) ORDER BY `ders_brans_code` ASC LIMIT 1"));
    return DersBransAdi($row["ders_brans_code"]);
}

function GrupOgrenciSinifAdlari($ogrID)
{
	global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT GROUP_CONCAT(`SinifKodu` SEPARATOR ',') AS classIDs FROM "._OGRENCILER_." WHERE `ogrID` IN (".$ogrID.")"));
    return SinifAdlari($row["classIDs"]);
}

function Haftalik_Etut_Sayisi($ogrID, $hafta)
{
    global $db;
    $etut_sayisi = $db->sql_numrows($db->sql_query("SELECT `pr_id` FROM "._SCHEDULE_." WHERE FIND_IN_SET(".$ogrID.",`ogrenci_code`) > 0 AND `hafta`='".$hafta."'"));
    return $etut_sayisi;
}

function Brans_Etut_Sayisi($ogrID, $bransKodu, $hafta)
{
    global $db;
    $num_pr = $db->sql_numrows($db->sql_query("SELECT `pr_id` FROM "._SCHEDULE_." WHERE FIND_IN_SET(".$ogrID.",`ogrenci_code`) > 0 AND `ders_brans_code`='".$bransKodu."' AND `hafta`='".$hafta."'"));
    return $num_pr;
}

function OgrenciEtutListesi($ogrenciler, $dersTuru)
{
    $gonderilecek = "";
	$ogrenci_dizi = explode(",", $ogrenciler);
	for($t=0; $t<sizeof($ogrenci_dizi); $t++)
	{
		$gonderilecek .= "<button type='button' data-toggle='tooltip' data-placement='top' title='".fnStudentId2StudentName($ogrenci_dizi[$t])." [".SinifAdi(fnOgrenciID2SinifID($ogrenci_dizi[$t]))."]' class='btn btn-xs' style='background-color:".ScheduleLabelTypeBgColor($dersTuru)."; color:white'>".OgrenciNo($ogrenci_dizi[$t])."</button> "; 
	}
    return $gonderilecek;
}


/*
function Brans_Max_Etut_Sayisi($brans_code)
{
    global $db, $dbname2, $prefix2, $ySubeKodu;
    $brans_ayari = $db->sql_fetchrow($db->sql_query("SELECT maxEtutSayisi FROM ".$dbname2.".".$prefix2."_etut_brans_ayarlari WHERE bransKodu='$brans_code' AND subeKodu='$ySubeKodu'"));
    return $brans_ayari["maxEtutSayisi"];
}

function Yapilmamis_Etut_Sayisi($ogrenci_code)
{
    global $db, $dbname2, $prefix2;
    $num_pr = $db->sql_numrows($db->sql_query("SELECT pr_id FROM $dbname2." .$prefix2 ."_ders_programi WHERE ogrenci_code='".$ogrenci_code."' AND yapildi_mi='0'"));
    return $num_pr;
}

function Yapilmamis_Brans_Etut_Sayisi($ogrenci_code, $brans_code)
{
    global $db, $dbname2, $prefix2;
    $num_pr = $db->sql_numrows($db->sql_query("SELECT pr_id FROM $dbname2." .$prefix2 ."_ders_programi WHERE ogrenci_code='".$ogrenci_code."' AND ders_brans_code='".$brans_code."' AND yapildi_mi='0'"));
    return $num_pr;
}

function Yapilmamis_Son_Etut_Tarihi($ogrenci_code)
{
    global $db, $dbname2, $prefix2;
    $etutler = $db->sql_fetchrow($db->sql_query("SELECT tarih FROM ".$dbname2.".".$prefix2."_ders_programi WHERE ogrenci_code='".$ogrenci_code."' AND yapildi_mi='0' ORDER BY tarih DESC LIMIT 1"));
    return $etutler["tarih"];
}

function Etut_Yasak_Bitis_Tarihi($ogrenci_code)
{
    global $db, $dbname2, $prefix2;
    $yasakli_listesi = $db->sql_fetchrow($db->sql_query("SELECT bitis_tarihi FROM ".$dbname2.".".$prefix2."_etut_yasakli WHERE ogrenci_code='".$ogrenci_code."' AND affedildi='0' AND cezasini_cekti='0' ORDER BY y_id DESC LIMIT 1"));
    return $yasakli_listesi["bitis_tarihi"];
}

function Etut_Yasak_Kontrol($ogrenci_code)
{
    global $db, $dbname2, $prefix2;
	$bugun = date("Y-m-d");
	
    $yasakli_listesi = $db->sql_query("SELECT y_id, bitis_tarihi FROM ".$dbname2.".".$prefix2."_etut_yasakli WHERE ogrenci_code='".$ogrenci_code."' AND affedildi='0' AND cezasini_cekti='0'");
	if($db->sql_numrows($yasakli_listesi) > 0)
	{
		$yasakli_ogrenci = $db->sql_fetchrow($yasakli_listesi);
		if($yasakli_ogrenci["bitis_tarihi"] < $bugun) //cezasi dolmus. etut verelim.
		{
			$db->sql_query("UPDATE ".$dbname2.".".$prefix2."_etut_yasakli SET cezasini_cekti='1' WHERE y_id='".$yasakli_ogrenci["y_id"]."'");
			return 1;
		}
		else
		{
			return 0;
		}
	}
	else
	{
		return 1;
	}
}

function Etut_Yasaklama_Durumu()
{
    global $username, $db;

    //deðiþkenleri alalým.
    $row_degiskenler = $db->sql_fetchrow($db->sql_query("SELECT * FROM dershane_etut_degiskenler"));
    $yasaklanma_siniri = intval($row_degiskenler[yasaklanma_siniri]);
    $yasaklanma_suresi = intval($row_degiskenler[yasaklanma_suresi]);

    $q_yasakli = $db->sql_query("SELECT y_id, bitis_tarihi FROM dershane_etut_yasakli WHERE ogrenci_code='$username'");
    $row_yasakli = $db->sql_fetchrow($q_yasakli);
    $num_yasakli = $db->sql_numrows($q_yasakli);
    if($num_yasakli > 0){
        $y_id = $row_yasakli[y_id];
        $bitis_tarihi = $row_yasakli[bitis_tarihi];
        $bugun = date("Y-m-d");     //bugünün tarihi

        if($bugun < $bitis_tarihi){
            //parçalayalým.
            $bitis_tarihi = explode("-", $bitis_tarihi);
            $bitis_tarihi_yil = $bitis_tarihi[0];
            $bitis_tarihi_ay = GetMonthName($bitis_tarihi[1]);
            $bitis_tarihi_gun = $bitis_tarihi[2];

            echo "<hr><b>Mesajýnýz var:</b> ";
            echo "Daha önce ($yasaklanma_siniri) kez etüt randevusuna gelmediðiniz için $bitis_tarihi_gun $bitis_tarihi_ay $bitis_tarihi_yil tarihine kadar bireysel etüt alamazsýnýz.";
        }else{
            //bitiþ tarihi geçmiþse yasaklý bölümden öðrenciyi silelim.
            $sil = $db->sql_query("DELETE FROM dershane_etut_yasakli WHERE y_id='$y_id'");
        }
    }else{
        $num_pr = $db->sql_numrows($db->sql_query("SELECT pr_id FROM dershane_ders_programi WHERE ogrenci_code='$username' AND yapildi_mi='0'"));

        for($i=1; $i<$yasaklanma_siniri; $i++){
            if($num_pr == $i){
                echo "<hr><b>Mesajýnýz var:</b> ";
                echo "Daha önce ($num_pr) kez etüt randevusuna gelmemiþsiniz. (" .intval($yasaklanma_siniri - $num_pr);
                echo ") kez daha etüt randevusuna gelmemeniz durumunda ($yasaklanma_suresi) gün süre ile etüt alamazsýnýz.";
            }
        }
        if ($num_pr == $yasaklanma_siniri){
            //yasaklanma sýnýrýna ulaþan öðrenciyi yasaklayalým.
            //son yapýlmayan etüt tarihini bulalým.
            $row_son_yapilmayan_etut = $db->sql_fetchrow($db->sql_query("SELECT tarih FROM dershane_ders_programi WHERE ogrenci_code='$username' AND yapildi_mi='0' ORDER BY tarih DESC LIMIT 0, 1"));
            $son_yapilmayan_etut_tarihi = $row_son_yapilmayan_etut[tarih];

            //parçalayalým.
            $son_yapilmayan_etut_tarihi = explode("-", $son_yapilmayan_etut_tarihi);
            $son_yapilmayan_etut_tarihi_yil = $son_yapilmayan_etut_tarihi[0];
            $son_yapilmayan_etut_tarihi_ay = $son_yapilmayan_etut_tarihi[1];
            $son_yapilmayan_etut_tarihi_gun = $son_yapilmayan_etut_tarihi[2];

            //yasaklanma süresini ekleyelim ve yasak bitiþ tarihini bulalim
            $bitis_tarihi  = date("Y-m-d", mktime(0, 0, 0, $son_yapilmayan_etut_tarihi_ay, $son_yapilmayan_etut_tarihi_gun + $yasaklanma_suresi, $son_yapilmayan_etut_tarihi_yil));
            //öðrenciye mesaj yazarken tarih formatýný deðiþtirelim.
            $bitis_tarihi_mesaj = date("d-m-Y", mktime(0, 0, 0, $son_yapilmayan_etut_tarihi_ay, $son_yapilmayan_etut_tarihi_gun + $yasaklanma_suresi, $son_yapilmayan_etut_tarihi_yil));

            //yasaklayalim.
            $yasakli_kayit = $db->sql_query("INSERT INTO dershane_etut_yasakli VALUES (NULL, '$username', '$bitis_tarihi')");

            echo "<hr><b>Mesajiniz var:</b> ";
            echo "Daha önce ($yasaklanma_siniri) kez etüt randevusuna gelmediðiniz için ($yasaklanma_suresi) gün süre ile etüt almanýz engellenmiþtir.<br>";
            echo "$bitis_tarihi_mesaj tarihinden itibaren tekrar bireysel etüt alabilirsiniz.";

            //yapýlmayan etütleri de yapildi_mi=2 yapalým ki tekrar onlarý yapýlmamýþ saymayalým.
            $yapildi_yap = $db->sql_query("UPDATE dershane_ders_programi SET yapildi_mi='2' WHERE ogrenci_code='$username' AND yapildi_mi='0'");
        }
    }
}
*/

?>
