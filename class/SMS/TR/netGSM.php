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

	function setGsmNo($gsmno) {
		if (is_array ( $gsmno )) {
			$nos = "";
			foreach ( $gsmno as $no ) {
				if (preg_match_all ( '/(?:[+]|[0]{1,2}){0,1}(?:[\s]{0,})(?P<icode>90|9[\s]0){0,1}(?:[\s]{0,})(?P<t1>5[0-9]{2})(?:[\s]{0,})(?P<t2>[0-9]{3})(?:[\s]{0,})(?P<t3>[0-9]{2})(?:[\s]{0,})(?P<t4>[0-9]{2})(?:[\s]{0,})/im', $no, $result, PREG_PATTERN_ORDER )) {
					$no = $result ['t1'] [0] . $result ['t2'] [0] . $result ['t3'] [0] . $result ['t4'] [0];
					$nos .= $no . ",";
				}
			}
			$this->gsmno = substr ( $nos, 0, - 1 );
		} else {
			if (preg_match_all ( '/(?:[+]|[0]{1,2}){0,1}(?:[\s]{0,})(?P<icode>90|9[\s]0){0,1}(?:[\s]{0,})(?P<t1>5[0-9]{2})(?:[\s]{0,})(?P<t2>[0-9]{3})(?:[\s]{0,})(?P<t3>[0-9]{2})(?:[\s]{0,})(?P<t4>[0-9]{2})(?:[\s]{0,})/im', $gsmno, $result, PREG_PATTERN_ORDER )) {
				$this->gsmno = $result ['t1'] [0] . $result ['t2'] [0] . $result ['t3'] [0] . $result ['t4'] [0];
			}
		}
	
	}
	
	function setOriginator($originator)
	{
		$this->originator = $originator;
	}
	
	function setMessageText($text) {
		/*
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
		*/
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
        
		$xml = <<<EOH
<?xml version="1.0" encoding="UTF-8"?>
<mainbody>
	<header>
		<company dil='TR'>{$this->BayiKodu}</company>
		<usercode>{$this->username}</usercode>
		<password>{$this->password}</password>
		<startdate></startdate>
		<stopdate></stopdate>
		<type>1:n</type>
		<msgheader>{$this->originator}</msgheader>
	</header>
	<body>
		<msg><![CDATA[{$this->msgtext}]]></msg>
		<no>{$this->gsmno}</no>
	</body>
</mainbody>	
EOH;
		$result = $this->postViaCurl ( "http://api.netgsm.com.tr/xmlbulkhttppost.asp", $xml );
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
		if (is_numeric ( $result ) && isset ( $msg [$result] )) {
			$result = array ("basari" => false, "mesaj" => $msg [$result] );
		} else {
			$result = array ("basari" => true, "mesaj" => $result );
		}
		return $result;
	}
	
	private function postViaCurl($url, $data) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		curl_setopt ($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt ($curl, CURLOPT_URL, $url);
		$result = curl_exec ($curl);
		curl_close ($curl);
		return $result;
	}
}
?>
