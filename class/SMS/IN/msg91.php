<?php


class sendSMS {
    
    private $authKey = ""; // authkey => Login authentication key (this key is unique for every user) 
    private $route = ""; // route => If your operator supports multiple routes then give one route name. Eg: route=1 for promotional, route=4 for transactional SMS.
    private $originator = ""; // sender Id => Receiver will see this as sender's ID.
    private $gsmno;
    private $msgtext; // Message content to send
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
	
	function setMessageText($text) 
	{
		$this->msgtext = $text;
	}
    
    function send()
    {
        global $dbi, $ySubeKodu;
           
        $dbi->where("subeKodu", array("0", "$ySubeKodu"), "IN");
        $dbi->orderBy("subeKodu", "desc");
		$smsConfig = $dbi->getOne(_SMS_CONFIG_, "api_user, api_password, api_originator, api_code");
		
		$this->authKey = $smsConfig["api_user"];
		$this->route = $smsConfig["api_code"];
		
		//senderID
        $this->originator = $smsConfig["api_originator"];
        
        $sms = array(
            'message' => $this->msgtext,
            'to' => array($this->gsmno)
        );
        
        //Prepare you post parameters
        $postData = array(
            'sender'    => $this->originator,
            'route'     => $this->route,
            'sms'       => array($sms)
        );
        
        $postDataJson = json_encode($postData);
        
        $jsonResult = $this->postViaCurl("http://api.msg91.com/api/v2/sendsms", $postDataJson, $smsConfig["api_user"]);
        
        $result = json_decode($jsonResult, true);
		
		if ($result["type"] == "success")
		{
		    $dataToSend = array ("basari" => "1", "mesaj" => $result["message"]);
		}
		else
		{
			$dataToSend = array ("basari" => "0", "mesaj" => $result["message"]);
		}
		
		return $dataToSend;
		
    }
    
    private function postViaCurl($url, $data, $key) {
		
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$url",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "authkey:".$key,
                "content-type: application/json"
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
	}
    
}


?>