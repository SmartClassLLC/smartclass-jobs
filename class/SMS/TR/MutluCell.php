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
        global $db, $ySubeKodu;
            
		$smsConfig = $db->sql_fetchrow($db->sql_query("SELECT `api_user`, `api_password`, `api_originator`, `api_code` FROM "._SMS_CONFIG_." WHERE `subeKodu`='".$ySubeKodu."' OR `subeKodu`='0' ORDER BY `subeKodu` DESC LIMIT 1"));
        
        $this->username = $smsConfig["api_user"];
        $this->password = $smsConfig["api_password"];
        $this->BayiKodu = $smsConfig["api_code"];
        $this->originator = ($this->originator != "") ? $this->originator : $smsConfig["api_originator"];
        
		$xml = <<<EOH
<?xml version="1.0" encoding="utf-8" ?>
<smspack ka="{$this->username}" pwd="{$this->password}" org="{$this->originator}" charset="unicode">
<mesaj>
	<metin>{$this->msgtext}</metin>
	<nums>{$this->gsmno}</nums>
</mesaj>
</smspack>
EOH;
		$result = $this->postViaCurl("https://smsgw.mutlucell.com/smsgw-ws/sndblkex", $xml);
		$msg ['20'] = "Post edilen xml eksik veya hatalı";
		$msg ['21'] = "Kullanılan originatöre sahip değilsiniz";
		$msg ['22'] = "Kontörünüz yetersiz";
		$msg ['23'] = "Kullanıcı adı ya da parolanız hatalı";
		$msg ['24'] = "Şu anda size ait başka bir işlem aktif";
		$msg ['25'] = "SMSC Stopped (Bu hatayı alırsanız, işlemi 1-2 dk sonra tekrar deneyin)";
		$msg ['30'] = "Hesap Aktivasyonu sağlanmamış";

		if (is_numeric($result) && isset($msg[$result]))
		{
			$dataToSend = array ("basari" => "0", "mesaj" => $msg[$result]);
		}
		else
		{
			$resultArray = explode(":", $result);
			$dataToSend = array ("basari" => "1", "mesaj" => $resultArray[1]);
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
