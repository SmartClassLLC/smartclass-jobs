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
		
		//$xml = "<SingleTextSMS><UserName>" . $this->username . "</UserName><PassWord>" . $this->password . "</PassWord><Action>1</Action><Mesgbody>" . $this->msgtext . "</Mesgbody><Numbers>" . $this->gsmno . "</Numbers><Originator>" . $this->originator . "</Originator><SDate></SDate></SingleTextSMS>";
        
		$xml = <<<EOH
<?xml version="1.0" encoding="utf-8" ?>
<SingleTextSMS>
<UserName>{$this->username}</UserName>
<PassWord>{$this->password}</PassWord>
<Action>1</Action>
<Mesgbody>{$this->msgtext}</Mesgbody>
<Numbers>{$this->gsmno}</Numbers>
<Originator>{$this->originator}</Originator>
<SDate></SDate>
</SingleTextSMS>
EOH;
		
		$result = $this->postViaCurl("http://www.smspaketim.com/api/mesaj_gonder", $xml);
		$msg ['01'] = "Hatalı Kullanıcı Adı yada Şifre";
		$msg ['02'] = "Numara tanımlanmamış";
		$msg ['03'] = "Tanımsız Action Parametresi";
		$msg ['04'] = "Yetersiz Kredi";
		$msg ['05'] = "Xml Düğümü Eksik yada Hatalı";
		$msg ['06'] = "Tanımsız Orginator";
		$msg ['07'] = "Mesaj Kodu (ID) yok";
		$msg ['09'] = "Tarih alanları hatalı";
		$msg ['10'] = "Sms Gönderilemedi";

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
		curl_setopt($curl, CURLOPT_POSTFIELDS, "data=".$data);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_URL, $url);
		
		//curl_setopt($curl, CURLOPT_MUTE, 1);
		//curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		//curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		//curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);
		//curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);

		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}
}
?>
