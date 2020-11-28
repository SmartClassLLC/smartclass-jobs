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

use \Twig\Loader;

class Announcements {
    
    private $announcements = array();
    private $announcementId = 0;
    private $nofUnread = 0;

    /* function */
	function setAnnouncementId($Id)
	{
		$this->announcementId = $Id;
	}

    /* function */
	function getAnnouncements($page = 0, $limit = 8)
	{
        global $dbi, $ySubeKodu, $aid, $userType, $currentlang, $dashboard, $userStudentInfo, $simsDateTime, $globalUserManagerType;
        
		//get announcements
		if($globalUserManagerType == "parent" || $globalUserManagerType == "student")
		{
			$batchId = $userStudentInfo["SinifKodu"];
			
			//if batchid is empty then return empty announcements
			if(empty($batchId)) $dbi->where("Id", "0"); //just show nothing
		
			//otherwise get announcements
			else $dbi->where("IF(HangiSiniflarGorebilir IS NULL OR HangiSiniflarGorebilir = '', FIND_IN_SET(". $userType. ", CAST(`KimGorebilir` AS CHAR)) > 0, FIND_IN_SET(". $userType. ", CAST(`KimGorebilir` AS CHAR)) > 0 AND FIND_IN_SET(". $batchId. ", CAST(`HangiSiniflarGorebilir` AS CHAR)) > 0)");
			
		}
		else
		{
			$dbi->where("FIND_IN_SET(". $userType. ", CAST(`KimGorebilir` AS CHAR))", "0", ">");
		}
		
		$dbi->where("SubeKodu", $ySubeKodu);
		$dbi->where("Aktif", "1");
		$dbi->orderBy("OnayTarihi", "DESC");
		
		if(empty($page))
		{
			$announcements = $dbi->get(_ANNOUNCEMENTS_);
		}
		else
		{
			$dbi->pageLimit = $limit;
			$announcements = $dbi->paginate(_ANNOUNCEMENTS_, $page);
		}

		if(!empty($announcements))
		{
			//handle posts for template
			foreach($announcements as $k => $p) {

				//check if read or not				
				$dbi->where("aidKodu", $aid);
				$dbi->where("mKodu", $p["Id"]);
				$readDate = $dbi->getValue(_ANNOUNCEMENTS_READ_, "rDate");
				
				if($dashboard && !empty($readDate)) continue;

				//add other parameters
				$announcements[$k]["KisaDetay"] = substr($p["Detay"], 0, 120);
				$announcements[$k]["OnayTarihi"] = FormatDateNumeric2Local($p["OnayTarihi"]);
				$announcements[$k]["OkunmaTarihi"] = FormatDateNumeric2Local($readDate, true, true);
				$announcements[$k]["sentTime"] = DateDifferenceWStrings($simsDateTime, $p["OnayTarihi"], "none");
				$announcements[$k]["Read"] = empty($readDate) ? "0" : "1";
				
				//get total number of announcement messages
				$dbi->where("subject", $p["Baslik"]);
				$dbi->where("sentTime", $p["OnayTarihi"]);
				$announcements[$k]["nofAnnouncements"] = $dbi->getValue(_MESSAGES_, "COUNT(id)");

				//get total number of read announcement
				$dbi->where("mKodu", $p["Id"]);
				$announcements[$k]["nofReadAnnouncements"] = $dbi->getValue(_ANNOUNCEMENTS_READ_, "COUNT(Id)");

				//get stats
				$announcements[$k]["readPercentage"] = $announcements[$k]["nofAnnouncements"] == 0 ? 0 : ($announcements[$k]["nofReadAnnouncements"]/$announcements[$k]["nofAnnouncements"]) * 100;
				$announcements[$k]["unreadPercentage"] = 100 - $announcements[$k]["readPercentage"];
				
				$announcements[$k]["readPercentageFormatted"] = number_format($announcements[$k]["readPercentage"], 2) . "%";
				$announcements[$k]["unreadPercentageFormatted"] = number_format($announcements[$k]["unreadPercentage"], 2) . "%";
				
				//set nof unread messages for the user
				$this->nofUnread++;
			}
		
			$this->announcements = $announcements;
		}
		
		return $this->announcements;
	}

    /* function */
	function nofUnread()
	{
        return $this->nofUnread;
	}
	
    /* function */
	function deleteAnnouncement()
	{
        global $dbi;
        
		// delete comment with the id
		//$dbi->where("Id", $this->postId);
		//$result = $dbi->delete(_SOCIAL_POSTS_);
        
		return $result;
	}
	
}