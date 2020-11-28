<?php

class Yourls {

	//api url    
    private $apiUrl = "https://schst.in/yourls-api.php";
    private $secretToken = "0b084003ef";
    private $responseFormat = "json";
    private $timestamp = "";
    private $signature = "";
    private $url = ""; //url to send to api which could be long or short

	//set user account info
	function __construct()
	{
    	//create passwordless signature
    	$this->timestamp = time();
    	$this->signature = md5($this->timestamp . $this->secretToken);
	}
	
	function setUrl($url)
	{
		$this->url = $url;
	}

	function setResponseFormat($format)
	{
		$this->responseFormat = $format;
	}

	function shortUrl($returnAllData = false)
    {
    	$data = array(
    		"url"			=> $this->url,
    		"timestamp"		=> $this->timestamp,
    		"signature"		=> $this->signature,
    		"action"		=> "shorturl",
    		"format"		=> $this->responseFormat
    	);
		
		$result = $this->postViaCurl($this->apiUrl, $data);
		
		if ($result)
		{
			if($returnAllData) return json_decode(array("result" => "success", "data" => $result));
			else 
			{
				$decodedResult = json_decode($result);
				return $decodedResult->shorturl;
			}
		}
		else 
		{
			return json_decode(array("result" => "fail", "data" => $result));
		}
	}
	
	function longUrl($returnAllData = false)
    {
    	$data = array(
    		"url"			=> $this->url,
    		"timestamp"		=> $this->timestamp,
    		"signature"		=> $this->signature,
    		"action"		=> "expand",
    		"format"		=> $this->responseFormat
    	);
		
		$result = $this->postViaCurl($this->apiUrl, $data);
		
		if ($result)
		{
			if($returnAllData) return json_decode(array("result" => "success", "data" => $result));
			else 
			{
				$decodedResult = json_decode($result);
				return $decodedResult->longurl;
			}
		}
		else 
		{
			return json_decode(array("result" => "fail", "data" => $result));
		}
	}
	
	function urlStats()
    {
    	$data = array(
    		"url"			=> $this->url,
    		"timestamp"		=> $this->timestamp,
    		"signature"		=> $this->signature,
    		"action"		=> "url-stats",
    		"format"		=> $this->responseFormat
    	);
		
		$result = $this->postViaCurl($this->apiUrl, $data);
		
		if ($result)
		{
			return json_decode(array("result" => "success", "data" => $result));
		}
		else 
		{
			return json_decode(array("result" => "fail", "data" => $result));
		}
	}
	
	function stats()
    {
    	$data = array(
    		"url"			=> $this->url,
    		"timestamp"		=> $this->timestamp,
    		"signature"		=> $this->signature,
    		"action"		=> "stats",
    		"format"		=> $this->responseFormat
    	);
		
		$result = $this->postViaCurl($this->apiUrl, $data);
		
		if ($result)
		{
			return json_decode(array("result" => "success", "data" => $result));
		}
		else 
		{
			return json_decode(array("result" => "fail", "data" => $result));
		}
	}
	
	function dbStats()
    {
    	$data = array(
    		"url"			=> $this->url,
    		"timestamp"		=> $this->timestamp,
    		"signature"		=> $this->signature,
    		"action"		=> "db-stats",
    		"format"		=> $this->responseFormat
    	);
		
		$result = $this->postViaCurl($this->apiUrl, $data);
		
		if ($result)
		{
			return json_decode(array("result" => "success", "data" => $result));
		}
		else 
		{
			return json_decode(array("result" => "fail", "data" => $result));
		}
	}
	
	private function postViaCurl($url, $data) {
		
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 300);
		curl_setopt($curl, CURLE_OPERATION_TIMEOUTED, 300);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		
		$result = curl_exec ($curl);
		curl_close ($curl);
		
		return $result;
	}
}
?>
