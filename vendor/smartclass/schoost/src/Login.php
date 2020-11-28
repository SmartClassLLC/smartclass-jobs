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

class Login {

	public $backgroundImage = '';
	public $backgroundColor = 'bg-black';
	
    function __construct()
    {
        global $dbi, $ySubeKodu;
    }

	/* function */
	function setBackgroundImage($img)
	{
        $this->backgroundImage = $img;
        
        return $this;
	}
    
	/* function */
	function getBackgroundColor()
	{
        return $this->backgroundColor;
	}
    
	/* function */
	function setBackgroundColor($img)
	{
        $this->backgroundColor = $img;
        
        return $this;
	}
    
	/* function */
	function getBackgroundImage()
	{
        return $this->backgroundImage;
	}
    
    /* function */
	function setCookies()
	{
		//check if it is SmartClass app or not
		$simsApp = (isset($_COOKIE["simsApp"]) && $_COOKIE["simsApp"] == "1") ? "1" : ((isset($_GET["simsapp"]) && $_GET["simsapp"] == "1") ? "1" : "0");
		
		//if app then put a cookie for web usage
		if($simsApp == "1" && empty($_COOKIE["simsApp"]))
		{
			setcookie("simsApp", "1", time() + (24 * 60 * 60), "/", $_SERVER["SERVER_NAME"], true, false);
		}
		
		//remove current cookies
		unset($_COOKIE['admin']);
		setcookie("user", false, time() - (3000 * 24 * 60 * 60), "/", $_SERVER["SERVER_NAME"], true, false);
		
		unset($_COOKIE['userFolder']);
		setcookie("userFolder", false, time() - (3000 * 24 * 60 * 60), "/", $_SERVER["SERVER_NAME"], true, false);
		
		unset($_COOKIE['ogrID']);
		setcookie("ogrID", false, time() - (3000 * 24 * 60 * 60), "/", $_SERVER["SERVER_NAME"], true, false);
		
		//unset($_COOKIE['seasonID']); setcookie("seasonID", false, time() - (3000 * 24 * 60 * 60), $globalUserFolder, $_SERVER["SERVER_NAME"], true, false);
		//unset($_COOKIE['branchID']); setcookie("branchID", false, time() - (3000 * 24 * 60 * 60), $globalUserFolder, $_SERVER["SERVER_NAME"], true, false);
		
		//@todo delete following lines later
		unset($_COOKIE['dbname2']);
		setcookie("dbname2", false, time() - (3000 * 24 * 60 * 60), "/", $_SERVER["SERVER_NAME"], true, false);
		
	}

}
