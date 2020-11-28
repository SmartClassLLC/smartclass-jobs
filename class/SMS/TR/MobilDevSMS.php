<?php

class sendSMS {
    
    private $username = "";
    private $password = "";
    private $BayiKodu = "";
    private $requestedpass;
    private $originator = "";
    private $action = "0";
    private $gsmno;
    private $msgtext;
    private $sendMulti = false;
    //private $apiUrl = "http://gateway.mobilus.net/com.mobilus";
    private $apiUrl = "https://secure.mobilus.net/com.mobilus";

	//set user account info
	function __construct()
	{
    	global $dbi, $ySubeKodu;
        
		$smsConfig = $dbi->where("subeKodu", array("0", "$ySubeKodu"), "IN")->orderBy("subeKodu", "desc")->getOne(_SMS_CONFIG_);
	
	    $this->username = $smsConfig["api_user"];
	    $this->password = $smsConfig["api_password"];
	    $this->bayikodu = $smsConfig["api_code"];
	    $this->originator = ($this->originator != "") ? $this->originator : $smsConfig["api_originator"];
	}
	
	/* user information control */
	function UserControl()
	{
		$strXML = "<MainReportRoot><UserName>". $this->username. "</UserName><PassWord>". $this->password. "</PassWord><Action>4</Action></MainReportRoot>";
		
		$result = $this->postViaCurl($this->apiUrl, $strXML);
		
		$arrDonus = explode(Chr(10), $result);
		$strDonus = implode("<br>", $arrDonus);
		
		echo $strDonus;
	}

	/* check message by message id from the provider */
	function CheckMessageById($msgId)
	{
		global $dbi;
		
		$strXML = "<MainReportRoot><UserName>". $this->username. "</UserName><PassWord>". $this->password. "</PassWord><Action>3</Action><MsgID>". $msgId. "</MsgID></MainReportRoot>";

		$result = $this->postViaCurl($this->apiUrl, $strXML);
		
		//return format "TimerID<32>GSM Numarası<32>Durum<10>"
		//Durum: 1 -> waiting, 2 -> sent, 3 -> not sent
		
		$arrResult = explode(Chr(32), $result);
		
		$outcome = substr($arrResult[2], 0, 1);
		
		//outcomes
		if($outcome == "1") $outcomeText = _SMS_WAITING;
		else if($outcome == "2") $outcomeText = _SENT_SMS;
		else if($outcome == "3") $outcomeText = _UNSENT_SMS;
		
		//update db
		if(in_array($outcome, array("1", "2", "3"))) $dbi->where("message_id", $msgId)->update(_SMS_LOGS_, array("kontrolDurumId" => $outcome, "kontrolDurum" => $outcomeText));
		
		//return text
		return $outcomeText;
	}

	function setGsmNo($gsmno)
	{
		/* mobildev accepts almost all formats */
		/* <Numbers>05321234567</Numbers> */
		/* <Numbers>5321234567</Numbers> */
		/* <Numbers>905321234567</Numbers> */
		
		$this->gsmno = substr($gsmno, -11);
	}

	function setOriginator($originator)
	{
		$this->originator = $originator;
	}
	
	function setMessageText($text) {
		$text = str_replace ( array ("&#304;", "\u0130", "\xDD", "İ" ), "I", $text );
		$text = str_replace ( array ("&#305;", "\u0131", "\xFD", "ı" ), "i", $text );
		$text = str_replace ( array ("&#286;", "\u011e", "\xD0", "Ğ" ), "G", $text );
		$text = str_replace ( array ("&#287;", "\u011f", "\xF0", "ğ" ), "g", $text );
		$text = str_replace ( array ("&Uuml;", "\u00dc", "\xDC", "U" ), "U", $text );
		$text = str_replace ( array ("&uuml;", "\u00fc", "\xFC", "ü" ), "u", $text );
		$text = str_replace ( array ("&#350;", "\u015e", "\xDE", "Ş" ), "S", $text );
		$text = str_replace ( array ("&#351;", "\u015f", "\xFE", "ş" ), "s", $text );
		$text = str_replace ( array ("&Ouml;", "\u00d6", "\xD6", "Ö" ), "O", $text );
		$text = str_replace ( array ("&ouml;", "\u00f6", "\xF6", "ö" ), "o", $text );
		$text = str_replace ( array ("&Ccedil;", "\u00c7", "\xC7", "Ç" ), "C", $text );
		$text = str_replace ( array ("&ccedil;", "\u00e7", "\xE7", "ç" ), "c", $text );
		$this->msgtext = trim($text);
	}
	
	function send()
    {
    	//set action to 40 if the number of characters is greater than 160 else to 0
        $this->action = (mb_strlen($this->msgtext) > 160) ? "40" : "0";

		$xml = "<MainmsgBody><UserName>". $this->username. "</UserName><PassWord>". $this->password. "</PassWord><Mesgbody>" . $this->msgtext . "</Mesgbody><Numbers>" . $this->gsmno . "</Numbers><Action>" . $this->action . "</Action><Originator>" . $this->originator . "</Originator><SDate></SDate></MainmsgBody>";
		
		$result = $this->postViaCurl($this->apiUrl, $xml);
		
		$msg ['01'] = "Kullanıcı Adı ve/veya Şifre Hatalı";
		$msg ['02'] = "Kredisi yeterli değil";
		$msg ['03'] = "Tanımsız Action parametresi";
		$msg ['04'] = "Gelen XML yok";
		$msg ['05'] = "XML düğümü eksik ya da hatalı";
		$msg ['06'] = "Tanımsız Orijinatör bilgisi";
		$msg ['07'] = "Mesaj kodu (ID) yok";
		$msg ['08'] = "Verilen tarihler arasında SMS gönderimi yok";
		$msg ['09'] = "Tarih alanları boş - hatalı";
		$msg ['10'] = "SMS gönderilemedi";
		$msg ['11'] = "Tanımlanamayan hata";
		$msg ['12'] = "Admin yetkisiyle ulaşılabilecek bir alana Admin yetkisi olmayan biri ulaşmaya çalıştı.";
		$msg ['13'] = "Rapor istenen kullanıcı yok";

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
		
		$result = curl_exec ( $curl );
		curl_close ( $curl );
		return $result;
	}
}
?>
