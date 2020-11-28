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

class Invoices {
    
    private $studentId = "0";
    private $stdSchoolId = "0";
    private $generalSettings = array();
    private $language = "";
    private $invoiceData = array();
    private $includeTotal = true;
    private $includeEmpty = false;
    
    /* function */
	function setStudentId($stdId)
	{
	    global $dbi, $globalZone;
	    
		//set student Id
		$this->studentId = $stdId;
		
		//get student school information for headquarters
		if($globalZone == "headquarters")
		{
		    $this->stdSchoolId = fnStdId2StdInfo($stdId, "SubeKodu");
		    
        	//general preferences
        	$this->generalSettings = $dbi->where("subeKodu", $this->stdSchoolId)->map("setting")->get(_SETTINGS_, null, "`setting`, CASE `value` WHEN 'off' THEN 0 ELSE `value` END AS `settingValue`");
		}
	}

    /* function */
	function setLanguage($lang)
	{
		//set language of the invoice
		$this->language = $lang;
	}

    /* function */
	function setTotal($include)
	{
		//set total fee whether to include or not
		$this->includeTotal = $include;
	}

    /* function */
	function setEmpty($include)
	{
		//set empty fees whether to include or not
		$this->includeEmpty = $include;
	}

    /* function */
	function studentInvoiceData($feesTable = "", $busFeesTable = "", $discountsTable = "")
	{
        global $dbi, $ySubeKodu, $currentlang, $genelAyarlar, $globalUserFolder;
        
        $feesTable = empty($feesTable) ? _YAPILAN_UCRETLER_ : $feesTable;
        $busFeesTable = empty($busFeesTable) ? _YAPILAN_SERVIS_UCRETLERI_ : $busFeesTable;
        $discountsTable = empty($discountsTable) ? _YAPILAN_INDIRIMLER_ : $discountsTable;

        //set data to empty array
        $this->invoiceData = array();
        
        //set school id
        if(empty($this->stdSchoolId)) $this->stdSchoolId = $ySubeKodu;
        
        //set general settings
        if(empty($this->generalSettings)) $this->generalSettings = $genelAyarlar;
        
        //set the language
        if(empty($this->language)) $this->language = $currentlang;
        
        //get fee types
		$dbi->where("active", "on");
		$dbi->orderBy("feeOrder", "ASC");
		$feeTypes = $dbi->get(_FEE_TYPES_);

		if($globalUserFolder == "school")
		{
			foreach ($feeTypes as $k => $feeType)
			{
				if(!empty($feeType["showParameter"]) && !$this->generalSettings[$feeType["showParameter"]]) unset($feeTypes[$k]);
			}
		}

		//get student fees and bus fee
		//$studentFees = $dbi->where("subeKodu", $this->stdSchoolId)->where("ogrNo", $this->studentId)->map("ucretAdi")->get($feesTable, null,  "ucretAdi, ucretMiktari");
		//$studentBusFee = $dbi->where("subeKodu", $this->stdSchoolId)->where("ogrNo", $this->studentId)->getValue($busFeesTable, "ucretMiktari");
		$studentFees = $dbi->where("ogrNo", $this->studentId)->map("ucretAdi")->get($feesTable, null,  "ucretAdi, ucretMiktari");
		$studentBusFee = $dbi->where("ogrNo", $this->studentId)->getValue($busFeesTable, "ucretMiktari");

		//insert bus fee to the fees
		if(!empty($studentBusFee)) $studentFees["_SERVIS_UCRETI"] = $studentBusFee;

		$studentDiscounts = $dbi->where("ogrNo", $this->studentId)->groupBy("indirimTuru")->map("indirimTuru")->get($discountsTable, null,  "indirimTuru, SUM(indirimMiktari) AS toplamIndirim");

		if(empty($studentDiscounts))
		{
			foreach ($feeTypes as $k => $feeType)
			{
			    //if the fee type is not included then pass it
			    if(!$this->includeEmpty && empty($studentFees[$feeType["feeTitle"]])) continue;
	
	            //add fee title and amount to the invoice info			
				$this->invoiceData[] = array(
				    "feeTitle"  => translateWord($feeType["feeTitle"], $this->language),
				    "feeAmount" => $studentFees[$feeType["feeTitle"]]
	            );
			}
		}
		else
		{
			//add the discount that was made from overall price to the tuition discount
			//this is tricky in order to get rid of balance differences
			if(!empty($studentDiscounts["toplam"])) $studentDiscounts["egitim"] = $studentDiscounts["egitim"] + $studentDiscounts["toplam"];
	
			foreach ($feeTypes as $k => $feeType)
			{
				/*
				fee_types tablosunda feeId kolonuna bilgilerin sonuna 'Ucreti' gelecek sekilde kaydedilmis
				ama yapilan_indirimler tablosuna bu sekilde kaydedilmemis bundan dolayi Ucreti kismini kaldirmamiz gerekiyor
				
				if($feeType["feeId"] == "yemekUcreti") $feeType["feeId"] = str_replace("Ucreti", "", $feeType["feeId"]);
				if($feeType["feeId"] == "egitimUcreti") $feeType["feeId"] = str_replace("Ucreti", "", $feeType["feeId"]);
				if($feeType["feeId"] == "kahvaltiUcreti") $feeType["feeId"] = str_replace("Ucreti", "", $feeType["feeId"]);
				if($feeType["feeId"] == "kirtasiyeUcreti") $feeType["feeId"] = str_replace("Ucreti", "", $feeType["feeId"]);
				if($feeType["feeId"] == "dergiUcreti") $feeType["feeId"] = str_replace("Ucreti", "", $feeType["feeId"]);
				if($feeType["feeId"] == "yayinUcreti") $feeType["feeId"] = str_replace("Ucreti", "", $feeType["feeId"]);
				if($feeType["feeId"] == "servisUcreti") $feeType["feeId"] = str_replace("Ucreti", "", $feeType["feeId"]);
				if($feeType["feeId"] == "kiyafetUcreti") $feeType["feeId"] = str_replace("Ucreti", "", $feeType["feeId"]);
				if($feeType["feeId"] == "destekUcreti") $feeType["feeId"] = str_replace("Ucreti", "", $feeType["feeId"]);
				*/

			    //if the fee type is not included then pass it
			    if(!$this->includeEmpty && empty($studentFees[$feeType["feeTitle"]])) continue;
	
	            //calcualte the fee //discountSymbol adinda bit kolon bulunmuyor!!
				$fee = $studentFees[$feeType["feeTitle"]] - $studentDiscounts[$feeType["discountSymbol"]];
				
				//$fee = $studentFees[$feeType["feeTitle"]] - $studentDiscounts[$feeType["feeId"]];
	
	            //add fee title and amount to the invoice info			
				$this->invoiceData[] = array(
				    "feeTitle"  => translateWord($feeType["feeTitle"], $this->language),
				    "feeAmount" => $fee
	            );
			}
		}

        if($this->includeTotal)
        {
            //get total amount of the fee
            $totalFee = $dbi->where("ogrNo", $this->studentId)->where("ucretAdi", "_INDIRIMLI_EGITIM_UCRETI")->getOne($feesTable,  "ucretAdi, ucretMiktari");
    
            //add total fee title and amount to the invoice info			
    		$this->invoiceData[] = array(
    		    "feeTitle"  => translateWord($totalFee["ucretAdi"], $this->language),
    		    "feeAmount" => $totalFee["ucretMiktari"]
            );
        }
        
        return $this->invoiceData;
	}
}