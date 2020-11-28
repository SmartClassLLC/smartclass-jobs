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

class Main {

	public $config = array();
	public $schoolConfig = array();
	public $currentlang = 'english';
	public $htmlDirection = 'ltr';
	public $isSchoolUrl = false;
	public $requestParams = array();
	
    function __construct()
    {
        global $dbi, $ySubeKodu;

		// Get configuration
		$dbi->where("schoolId", NULL, "IS");
		$configuration = $dbi->map("configKey")->get(_CONFIGURATION_, null, "configKey, configValue");
		$this->config = $configuration;
		
		//get school config
		if(!empty($ySubeKodu))
		{
			$dbi->where("subeID", $ySubeKodu);
			$this->schoolConfig = $dbi->getOne(_SUBELER_);
		}

		//set html direction
		if($this->currentlang == "arabic" OR $this->currentlang == "ottoman")
		{
			$this->htmlDirection = "rtl";
		}
		else
		{
			$this->htmlDirection = "ltr";
		}
		
		//set request parameters
		if(isset($_GET["newlang"]) && !empty($_GET["newlang"]))
		{
			$this->requestParams[] = "newlang=" . $_GET["newlang"];
		}
		
		if(isset($_GET["n"]) && !empty($_GET["n"]))
		{
			$this->requestParams[] = "n=". $_GET["n"];
		}
    }

	/* function */
	function setCurrentLang($lng)
	{
        $this->currentlang = $lng;
        
        return $this;
	}
    
	/* function */
	function getCurrentLang()
	{
        return $this->currentlang;
	}
	
	/* function */
	function setHTMLDirection($dr)
	{
        $this->htmlDirection = $dr;
        
        return $this;
	}
    
	/* function */
	function getHTMLDirection()
	{
        return $this->htmlDirection;
	}
	
	/* function */
	function setTitle($title)
	{
        $this->title = $title;
        
        return $this;
	}
    
	/* function */
	function getTitle()
	{
        return $this->title;
	}
    
	/* function */
	function setRequestParameters($params)
	{
		$this->requestParams = $params;
		
		return $this;
	}
	
	/* function */
	function getRequestParameters()
	{
		return implode("&", $this->requestParams);
	}
	
	/* function */
	function isSchoolUrl()
	{
		global $dbi, $ySubeKodu;
		
		//check school url from the domain
		if($config["checkServerName"] == "on")
		{
			//school url
			$schoolDomain = $this->urlHttpType . "://" . $_SERVER["SERVER_NAME"];
			
			//check if defined on db
			if(!empty($ySubeKodu)) $dbi->where("subeID", $ySubeKodu);
			$dbi->where("scUrl", $schoolDomain);
			$schoolIdFromUrl = $dbi->getValue(_SUBELER_, "subeID");
			
			if(!empty($schoolIdFromUrl))
			{
				return true;
				$ySubeKodu = $schoolIdFromUrl;
			}
		}
		
		return false;
	}
	
	/* function */
	function getConfig($setting = "")
	{
		if(empty($setting)) return $this->config;
		else
		{
			$settings = $this->config;
			return $settings[$setting];
		}
	}
	
	/* function */
	function getSchoolConfig($setting = "")
	{
		if(empty($setting)) return $this->schoolConfig;
		else
		{
			$settings = $this->schoolConfig;
			return $settings[$setting];
		}
	}
	
	/* function */
	function urlHttpType()
	{
		return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && !in_array(strtolower($_SERVER['HTTPS']), array('off', 'no'))) ? 'https' : 'http';
	}
	
}
