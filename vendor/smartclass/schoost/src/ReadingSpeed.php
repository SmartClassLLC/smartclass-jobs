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

class ReadingSpeed {
    
	/* function */
	function getReadingSpeed($filters = array())
	{
        global $dbi, $ySubeKodu, $countryCode;
        
        $dbi->join(_BATCHES_ . " b", "b.sinifID=rs.classId", "LEFT");
		$dbi->join(_OGRENCILER_ . " o","o.ogrID=rs.studentId", "LEFT");

        if(!empty($filters["dates"]))
        {
            $firstDate = $filters["dates"][0] . " 00.00.00";
            $secondDate = $filters["dates"][1] . " 23.59.59";
            
            $dbi->where("rs.date", $firstDate, ">=");
            $dbi->where("rs.date", $secondDate, "<=");
        }
        
        if(sizeof($filters["students"]))
        {
            $dbi->where("rs.studentId", $filters["students"], "IN");
        }
        
        $dbi->where("rs.schoolId", $ySubeKodu);
        $dbi->orderBy("date", "DESC");
        $getReading = $dbi->get(_READING_SPEED_. " rs",null,"rs.studentId, rs.performance, rs.metric, rs.date, rs.classId, b.sinifAdi, o.ogrenciNo, o.Adi, o.IkinciAdi, o.Soyadi");

		return $getReading;
	}

}
