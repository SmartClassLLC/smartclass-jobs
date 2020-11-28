<?php

class sendSMS {
    
    private $username = "";
    private $password = "";
    private $BayiKodu = "";
    private $requestedpass;
    private $originator = "";
    private $gsmno;
    private $msgtext;
    private $sendMulti = false;

	function setGsmNo($gsmno)
	{
		//this reguirement comes from postaGuvercini document. they only accept numbers starting with service provider code like 532xxxxxxx
		$this->gsmno = substr($gsmno, -10);
	}

	function setOriginator($originator)
	{
		$this->originator = $originator;
	}
	
	function setMessageText($text) {
		$text = str_replace(array ("&#304;", "\u0130", "\xDD", "İ" ), "I", $text);
		$text = str_replace(array ("&#305;", "\u0131", "\xFD", "ı" ), "i", $text);
		$text = str_replace(array ("&#286;", "\u011e", "\xD0", "Ğ" ), "G", $text);
		$text = str_replace(array ("&#287;", "\u011f", "\xF0", "ğ" ), "g", $text);
		$text = str_replace(array ("&Uuml;", "\u00dc", "\xDC", "U" ), "U", $text);
		$text = str_replace(array ("&uuml;", "\u00fc", "\xFC", "ü" ), "u", $text);
		$text = str_replace(array ("&#350;", "\u015e", "\xDE", "Ş" ), "S", $text);
		$text = str_replace(array ("&#351;", "\u015f", "\xFE", "ş" ), "s", $text);
		$text = str_replace(array ("&Ouml;", "\u00d6", "\xD6", "Ö" ), "O", $text);
		$text = str_replace(array ("&ouml;", "\u00f6", "\xF6", "ö" ), "o", $text);
		$text = str_replace(array ("&Ccedil;", "\u00c7", "\xC7", "Ç" ), "C", $text);
		$text = str_replace(array ("&ccedil;", "\u00e7", "\xE7", "ç" ), "c", $text);
		$this->msgtext = $text;
	}
	
	function send()
    {
        global $dbi, $ySubeKodu;
           
        $dbi->where("subeKodu", array("0", "$ySubeKodu"), "IN");
        $dbi->orderBy("subeKodu", "desc");
		$smsConfig = $dbi->getOne(_SMS_CONFIG_, "api_user, api_password, api_originator, api_code");
        
        $this->username = $smsConfig["api_user"];
        $this->password = $smsConfig["api_password"];
        $this->BayiKodu = $smsConfig["api_code"];
        $this->originator = ($this->originator != "") ? $this->originator : $smsConfig["api_originator"];
        
        $data = array(
        	"username"	=> $this->username,
        	"password"	=> $this->password,
        	"vperiod"	=> "48",
			"message"	=> array(
							"sender"	=> $this->originator,
							"text"		=> $this->msgtext,
							"utf8"		=> "0",
							"gsm"		=> array($this->gsmno)
						)
        );
		
		$jsonData = json_encode($data);
		
		$jsonResult = $this->postViaCurl("http://www.goldmesaj.com.tr/api/v1/sendsms", $jsonData);
		
		$result = json_decode($jsonResult, true);
		
		if ($result["status"] == "error")
		{
			$dataToSend = array ("basari" => "0", "mesaj" => $result["error"]);
		}
		else
		{
			$dataToSend = array ("basari" => "1", "mesaj" => $result["result"]["messageid"]);
		}
		
		return $dataToSend;
	}
	
	private function postViaCurl($url, $data) {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 300);
		curl_setopt($curl, CURLE_OPERATION_TIMEOUTED, 300);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_URL, $url);
		
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}
}
?>
