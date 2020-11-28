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

class Games {
    
	/* function */
	function getGames($filters = array())
	{
        global $dbi, $ySubeKodu, $countryCode;

		if(!empty($filters["subject"])) $dbi->where("subjectId", $filters["subject"], "IN");
		
		if(!empty($filters["gradeLevels"])) $dbi->where("FIND_IN_SET(?, gradeLevelIds)", array($filters["gradeLevels"]));
		
		if(!empty($filters["search"])) $dbi->where("(title LIKE '%" . $filters["search"] . "%')");
		
		$dbi->where("countryId", $countryCode);
		$dbi->where("active", "1");
		$games = $dbi->get(_GAMES_);
		
		return $games;
	}

}
