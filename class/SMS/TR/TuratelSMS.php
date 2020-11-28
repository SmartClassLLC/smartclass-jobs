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
<MainmsgBody>
	<Command>0</Command>
	<PlatformID>1</PlatformID>
	<ChannelCode>{$this->BayiKodu}</ChannelCode>
	<UserName>{$this->username}</UserName>
	<PassWord>{$this->password}</PassWord>
	<Mesgbody>{$this->msgtext}</Mesgbody>
	<Numbers>{$this->gsmno}</Numbers>
	<Type>1</Type>
	<Originator>{$this->originator}</Originator>
</MainmsgBody>	
EOH;
		$result = $this->postViaCurl ("http://processor.smsorigin.com/xml/process.aspx", $xml);
		$msg ['00'] = "Sistem Hatası";
		$msg ['01'] = "Kullanıcı Adı ve/veya Şifre Hatalı";
		$msg ['02'] = "Kredisi yeterli değil";
		$msg ['03'] = "Geçersiz içerik";
		$msg ['04'] = "Bilinmeyen SMS tipi";
		$msg ['05'] = "Hatalı gönderen ismi";
		$msg ['06'] = "Mesaj metni ya da Alıcı bilgisi girilmemiş";
		$msg ['07'] = "İçerik uzun fakat Concat özelliği ayarlanmadığından mesaj birleştirilemiyor";
		$msg ['08'] = "Kullanıcının mesaj göndereceği gateway tanımlı değil ya da şu anda çalışmıyor";
		$msg ['09'] = "Yanlış tarih formatı.Tarih ddMMyyyyhhmm formatında olmalıdır";
		$msg ['20'] = "Tanımsız Hata (XML formatını kontrol ediniz veya TURATEL’den destek alınız)";
		$msg ['21'] = "Hatalı XML Formatı (\n - carriage return – newline vb içeriyor olabilir)";
		$msg ['22'] = "Kullanıcı Aktif Değil";
		$msg ['23'] = "Kullanıcı Zaman Aşımında";

		if (is_numeric ($result) && isset($msg[$result]))
		{
			$dataToSend = array ("basari" => "0", "mesaj" => $msg[$result]);
		} else {
			$resultArray = explode(":", $result);
			$dataToSend = array ("basari" => "1", "mesaj" => $resultArray[1]);
		}
		return $dataToSend;
	}
	
	private function postViaCurl($url, $data) {
		//$user_agent = 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9) Gecko/2008061015 Firefox/3.0';
		//$httpheader = array ("Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Accept-Language: tr,en-us;q=0.7,en;q=0.3", "Accept-Encoding: gzip,deflate", "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7", "Keep-Alive: 300" );
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
