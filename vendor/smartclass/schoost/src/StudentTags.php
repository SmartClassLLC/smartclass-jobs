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

class StudentTags {

    function __construct()
    {
        global $dbi, $ySubeKodu, $globalZone;

		//get hits
		if($globalZone == "school") $dbi->where("SubeKodu", $ySubeKodu);
		$dbi->where("tags", NULL, "IS NOT");
		$dbi->where("KayitliMi", "1");
		$studentTags = $dbi->getValue(_OGRENCILER_, "tags", null);
		
		$tags = array();
		foreach($studentTags as $studentTag)
		{
		    if(!empty($studentTag)) $stdtags = explode(",", $studentTag);
		    
		    foreach($stdtags as $stdtag) $tags[] = $stdtag;
		}
		
		$counts = array_count_values($tags);
		
		//update hits just for headquarters
		if($globalZone == "headquarters" && !empty($tags))
		{
		    $dbtags = $dbi->get(_STUDENT_TAGS_);
		    foreach($dbtags as $dbtag)
		    {
		        $dbi->where("Id", $dbtag["Id"])->update(_STUDENT_TAGS_, array("hit" => $counts[$dbtag["Id"]]));
		    }
		}
    }
    
	/* function */
	function getTags()
	{
        global $dbi, $ySubeKodu, $globalZone;

	    if($globalZone == "school") $dbi->where("schoolId", array("0", "$ySubeKodu"), "IN");
	    $tags = $dbi->get(_STUDENT_TAGS_);

		return $tags;
	}
}