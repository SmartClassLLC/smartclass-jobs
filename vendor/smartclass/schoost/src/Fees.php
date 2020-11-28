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

class Fees {
    
    private $studentId = "0";
    private $stdSchoolId = "0";
    private $generalSettings = array();
    private $language = "";
    private $includeTotal = true;
    private $includeEmpty = false;
    private $feeData = false;
    
    /* function */
	function setSchoolId($schoolId)
	{
		//set school of the student
		$this->stdSchoolId = $schoolId;
	}

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
		//set language
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
	function studentFeeData()
	{
        global $dbi, $ySubeKodu, $currentlang, $genelAyarlar;
        
        //set data to empty array
        $this->feeData = array();
        
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

		foreach ($feeTypes as $k => $feeType)
		{
			if(!empty($feeType["showParameter"]) && $this->generalSettings[$feeType["showParameter"]] != "on") unset($feeTypes[$k]);
		}

		//get student fees and bus fee
		$studentFees = $dbi->where("subeKodu", $this->stdSchoolId)->where("ogrNo", $this->studentId)->map("ucretAdi")->get(_YAPILAN_UCRETLER_, null,  "ucretAdi, ucretMiktari");
		$studentBusFee = $dbi->where("subeKodu", $this->stdSchoolId)->where("ogrNo", $this->studentId)->getValue(_YAPILAN_SERVIS_UCRETLERI_, "ucretMiktari");

		//insert bus fee to the fees
		if(!empty($studentBusFee)) $studentFees["_SERVIS_UCRETI"] = $studentBusFee;

		//$studentDiscounts = $dbi->where("ogrNo", $this->studentId)->groupBy("indirimTuru")->map("indirimTuru")->get(_YAPILAN_INDIRIMLER_, null,  "indirimTuru, SUM(indirimMiktari) AS toplamIndirim");

		//add the discount that was made from overall price to the tuition discount
		//this is tricky in order to get rid of balance differences
		//if(!empty($studentDiscounts["toplam"])) $studentDiscounts["egitim"] = $studentDiscounts["egitim"] + $studentDiscounts["toplam"];

		foreach ($feeTypes as $k => $feeType)
		{
		    //if the fee type is not included then pass it
		    if(!$this->includeEmpty && empty($studentFees[$feeType["feeTitle"]])) continue;

            //calcualte the fee
			$fee = $studentFees[$feeType["feeTitle"]] - $studentDiscounts[$feeType["discountSymbol"]];

            //add fee title and amount to the invoice info			
			$this->invoiceData[] = array(
			    "feeTitle"  => translateWord($feeType["feeTitle"], $this->language),
			    "feeAmount" => $fee
            );
		}

        if($this->includeTotal)
        {
            //get total amount of the fee
            $totalFee = $dbi->where("ogrNo", $this->studentId)->where("ucretAdi", "_INDIRIMLI_EGITIM_UCRETI")->getOne(_YAPILAN_UCRETLER_,  "ucretAdi, ucretMiktari");
    
            //add total fee title and amount to the invoice info			
    		$this->feeData[] = array(
    		    "feeTitle"  => translateWord($totalFee["ucretAdi"], $this->language),
    		    "feeAmount" => $totalFee["ucretMiktari"]
            );
        }
        
        return $this->feeData;
	}

    /* function */
	function finalFee()
	{
        global $dbi;
        
        //get final amount of the fee
        $finalFee = $dbi->where("ogrNo", $this->studentId)->where("ucretAdi", "_INDIRIMLI_EGITIM_UCRETI")->getValue(_YAPILAN_UCRETLER_,  "ucretMiktari");
    
    	if(empty($finalFee)) $finalFee = $dbi->where("ogrNo", $this->studentId)->getValue(_YAPILAN_UCRETLER_,  "ucretMiktari");	
    	
    	return $finalFee;
	}

    /* function */
	function totalPayment()
	{
        global $dbi, $ySubeKodu;

        //set school id
        if(empty($this->stdSchoolId)) $this->stdSchoolId = $ySubeKodu;
        
        //get final amount of the fee
        $totalPayment = $dbi->where("ogrID", $this->studentId)->where("subeKodu", $this->stdSchoolId)->getValue(_TAHSILATLAR_,  "SUM(tahsilatMiktari)");
    
        return $totalPayment;
	}

    /* function */
	function remainderFee()
	{
        global $dbi;
        
        //get remainder
        $remainderFee = floatval($this->finalFee() - $this->totalPayment());
    
        return $remainderFee;
	}

	function fixInstallments()
	{
		global $dbi, $ySubeKodu;

		//do not now
		if(1) return true;
		
        //set school id
        if(empty($this->stdSchoolId)) $this->stdSchoolId = $ySubeKodu;
		
		//get payments
		$paymentInstallmentIds = array();
		$dbi->where("ogrID", $this->studentId);
		$dbi->where("subeKodu", $this->stdSchoolId);
		$payments = $dbi->getValue(_TAHSILATLAR_, "taksitID", null);
		
		//get installmentIds from payments
		foreach($payments as $payment)
		{
			if(!empty($payment))
			{
				$paymentArr = explode(",", $payment);
				foreach($paymentArr as $tid) $paymentInstallmentIds[] = $tid;
			}
		}
		
		$paymentInstallmentIds = array_unique($paymentInstallmentIds);

		//get installments
		$dbi->where("ogrID", $this->studentId);
		$dbi->where("subeKodu", $this->stdSchoolId);
		$installments = $dbi->get(_TAKSITLER_);

		foreach($installments as $installment)
		{
			//cross check for payment
			//update as unpaid as installment id is not in payment inst ids
			if(!in_array($installment["taksitID"], $paymentInstallmentIds)) $dbi->where("taksitID", $installment["taksitID"])->update(_TAKSITLER_, array("tahsilatYapildiMi" => 0));
		}
		
		return true;
	}
	
	function partialUnpaidInstallmentAmount()
	{
		global $dbi, $ySubeKodu;

		$dbi->where("ogrID", $this->studentId);
		$dbi->where("tahsilatYapildiMi", "2");
		$partiallyPaidInstallment = $dbi->getOne(_TAKSITLER_, "taksitID");
		
		if(!empty($partiallyPaidInstallment))
		{
			$sumOfPayments = $dbi->where("ogrID", $this->studentId)->getValue(_TAHSILATLAR_, "SUM(tahsilatMiktari)");
			$sumOfInstallments = $dbi->where("ogrID", $this->studentId)->where("tahsilatYapildiMi", array("1", "2"), "IN")->getValue(_TAKSITLER_, "SUM(taksitMiktari)");
			
			$difference = floatval($sumOfInstallments - $sumOfPayments);
			
			return ($difference > 0) ? $difference : 0;
		}
		else
		{
			return 0;
		}
	}
}