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

class Terms {

    private $terms = array();
    
    /* function */
	function getTerms()
	{
        global $dbi;
            
        $this->terms = $dbi->get(_GRADING_TERMS_);
		
		return $this->terms;
	}

}
?>
