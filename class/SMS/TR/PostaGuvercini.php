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
	private $sendDate = "";

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
		/*
		$text = str_replace ( array("&#304;", "\u0130", "\xDD", "İ"), "I", $text );
		$text = str_replace ( array("&#305;", "\u0131", "\xFD", "ı"), "i", $text );
		$text = str_replace ( array("&#286;", "\u011e", "\xD0", "Ğ"), "G", $text );
		$text = str_replace ( array("&#287;", "\u011f", "\xF0", "ğ"), "g", $text );
		$text = str_replace ( array("&Uuml;", "\u00dc", "\xDC", "U"), "U", $text );
		$text = str_replace ( array("&uuml;", "\u00fc", "\xFC", "ü"), "u", $text );
		$text = str_replace ( array("&#350;", "\u015e", "\xDE", "Ş"), "S", $text );
		$text = str_replace ( array("&#351;", "\u015f", "\xFE", "ş"), "s", $text );
		$text = str_replace ( array("&Ouml;", "\u00d6", "\xD6", "Ö"), "O", $text );
		$text = str_replace ( array("&ouml;", "\u00f6", "\xF6", "ö"), "o", $text );
		$text = str_replace ( array("&Ccedil;", "\u00c7", "\xC7", "Ç"), "C", $text );
		$text = str_replace ( array("&ccedil;", "\u00e7", "\xE7", "ç"), "c", $text );
		*/
		
		$text = str_replace("İ", "I", $text);
		$text = str_replace( "ı", "i", $text);
		$text = str_replace( "Ğ", "G", $text);
		$text = str_replace( "ğ", "g", $text);
		$text = str_replace( "Ü", "U", $text);
		$text = str_replace( "ü", "u", $text);
		$text = str_replace( "Ş", "S", $text);
		$text = str_replace( "ş", "s", $text);
		$text = str_replace( "Ö", "O", $text);
		$text = str_replace( "ö", "o", $text);
		$text = str_replace( "Ç", "C", $text);
		$text = str_replace( "ç", "c", $text);

		$this->msgtext = $text;
	}
	
	function send()
    {
        global $db, $ySubeKodu;
            
		$smsConfig = $db->sql_fetchrow($db->sql_query("SELECT `api_user`, `api_password`, `api_originator`, `api_code` FROM "._SMS_CONFIG_." WHERE `subeKodu`='".$ySubeKodu."' OR `subeKodu`='0' ORDER BY `subeKodu` DESC LIMIT 1"));
        
        $this->username = $smsConfig["api_user"];
        $this->password = $smsConfig["api_password"];
        $this->BayiKodu = $smsConfig["api_code"];
        $this->originator = ($this->originator != "") ? $this->originator : $smsConfig["api_originator"];
        $this->sendDate = date("Y/m/d H:i");
        
		$postData = "user=".$this->username."&password=".$this->password."&gsm=".$this->gsmno."&text=".urlencode($this->msgtext);
		$response = $this->postViaCurl("http://www.postaguvercini.com/api_http/sendsms.asp", $postData); 

		$responseArray = explode("&", $response);
		
		$returnArray = array();
		for ($i=0; $i < count($responseArray); $i++) { 
			$r = explode("=", $responseArray[$i]);
			$returnArray[$r[0]] = $r[1];
		}
		if($returnArray["errno"] == "0") $dataToSend = array ("basari" => "1", "mesaj" => $returnArray["message_id"]);
		else $dataToSend = array ("basari" => "0", "mesaj" => $returnArray["errtext"] );

		return $dataToSend;
	}

	function sendMultiPhoneMultiText()
    {
        global $db, $ySubeKodu;
            
		$smsConfig = $db->sql_fetchrow($db->sql_query("SELECT `api_user`, `api_password`, `api_originator`, `api_code` FROM "._SMS_CONFIG_." WHERE `subeKodu`='".$ySubeKodu."' OR `subeKodu`='0' ORDER BY `subeKodu` DESC LIMIT 1"));
        
        $this->username = $smsConfig["api_user"];
        $this->password = $smsConfig["api_password"];
        $this->BayiKodu = $smsConfig["api_code"];
        //$this->originator = $smsConfig["api_originator"];
        $this->sendDate = date("Y/m/d H:i");
        
		$xml = <<<EOH
<?xml version="1.0" encoding="iso-8859-9"?>
<SMS-InsRequest>
	<CLIENT user="{$this->username}" pwd="{$this->password}" />
	<INSERT to="{$this->gsmno}" text="{$this->msgtext}" dt="{$this->sendDate}" />
</SMS-InsRequest>
EOH;
		$result = $this->postViaCurl ( "http://www.postaguvercini.com/api_xml/Sms_insreq.asp", $xml );
		$msg ['00'] = "Kullanıcı Bilgileri Boş";
		$msg ['01'] = "Kullanıcı Bilgileri Hatalı";
		$msg ['02'] = "Hesap Kapalı";
		$msg ['03'] = "Kontör Hatası";
		$msg ['04'] = "Bayi Kodunuz Hatalı";
		$msg ['05'] = "Originator Bilginiz Hatalı";
		$msg ['06'] = "Yapılan İşlem İçin Yetkiniz Yok";
		$msg ['10'] = "Geçersiz IP Adresi";
		$msg ['14'] = "Mesaj Metni Girilmemiş";
		$msg ['15'] = "GSM Numarası Girilmemiş";
		$msg ['20'] = "Rapor Hazır Değil";
		$msg ['27'] = "Aylık Atım Limitiniz Yetersiz";
		$msg ['100'] = "XML Hatası";
		if (is_numeric($result) && isset($msg[$result]))
		{
			$result = array ("basari" => false, "mesaj" => $msg [$result] );
		} else {
			$result = array ("basari" => true, "mesaj" => $result );
		}
		return $result;
	}
	
	private function postViaCurl($url, $data) {
		$curl = curl_init();
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl, CURLOPT_VERBOSE, true );
		//curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, true );
		//curl_setopt ( $curl, CURLOPT_HTTPHEADER, $httpheader );
		//curl_setopt ( $curl, CURLOPT_USERAGENT, $user_agent );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 300 );
		curl_setopt ( $curl, CURLE_OPERATION_TIMEOUTED, 300 );
		curl_setopt ( $curl, CURLOPT_HEADER, false );
		curl_setopt ( $curl, CURLOPT_POST, true );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data );
		curl_setopt ( $curl, CURLOPT_URL, $url );
		$result = curl_exec ( $curl );
		curl_close ( $curl );
		return $result;
	}
}
?>
