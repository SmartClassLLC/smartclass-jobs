<?php

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

//if the user is in one of the areas of teacher, parent and student then send ok
if($globalUserType == "teacher" OR $globalUserType == "parent" OR $globalUserType == "student")
{
	$authorized = 1;
}
else if (SuperAdmin($aid) OR Admin($aid) OR SchoolTechAdmin($aid)) //if super admin or admin or school it admin return true
{
	$authorized = 1;
}
else //if not admin check the permissions
{
	$permission = ($customOp == "") ? $op : $customOp;
	$userTypeID = YoneticiKullaniciTuru($aid);
	
	//check user type permissions
	$q = $db->sql_query("SELECT `id` FROM "._USER_TYPE_PERMISSIONS_." WHERE `actionCode`='".$permission."' AND `userTypeCode`='".$userTypeID."'");
    if($db->sql_numrows($q) > 0)
    {
    	$authorized = 1;
	}
    else //check user permissions
	{
		$authorized = 0;
		/*
		$q = $db->sql_query("SELECT `id` FROM "._USER_PERMISSIONS_." WHERE `actionCode`='".$permission."' AND `userCode`='".$aid."'");
		
		if($db->sql_numrows($q) > 0) $authorized = 1;
		else $authorized = 0;
		*/
	}
}

//check permissions
if(!$authorized)
{
	if (stristr($_SERVER['HTTP_ACCEPT'], "application/json")) die(json_encode(array('message' => array('error' => _NO_ADMIN_AUTHORITY))));
	else die("<div class='alert alert-danger'><i class='fa fa-ban'></i> "._NO_ADMIN_AUTHORITY."</div>");
}				

?>