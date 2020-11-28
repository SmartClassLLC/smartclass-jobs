<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

/*info
user $globalUserFolder to get userzone
*/

if ($userFolder == 'teacher') {
	$globalUserType = "teacher";
	$globalUserFolder = "teacher";
	$globalUserManagerType = "teacher";
	$globalUserManagerMenu = "teacherMenu";
	$globalUserTypeTitle = _TEACHER;
	$globalUserTypeClass = "success";
	$globalUserTypeBgClass = "green";
	$globalZone = "teacher";
} else if($userFolder == 'parent') {
	$globalUserType = "parent";
	$globalUserFolder = "parent";
	$globalUserManagerType = "parent";
	$globalUserManagerMenu = "parentMenu";
	$globalUserTypeTitle = _PARENT;
	$globalUserTypeClass = "danger";
	$globalUserTypeBgClass = "red";
	$globalZone = "parent";
} else if($userFolder == 'student') {
	$globalUserType = "student";
	$globalUserFolder = "student";
	$globalUserManagerType = "student";
	$globalUserManagerMenu = "studentMenu";
	$globalUserTypeTitle = _STUDENT;
	$globalUserTypeClass = "warning";
	$globalUserTypeBgClass = "yellow";
	$globalZone = "student";
} else if($userFolder == 'headquarters') {
	$globalUserType = "manager";
	$globalUserFolder = "headquarters";
	$globalUserManagerType = "headquarters";
	$globalUserManagerMenu = "headQuarterMenu";
	$globalUserTypeTitle = _MANAGER;
	$globalUserTypeClass = "primary";
	$globalUserTypeBgClass = "blue";
	$globalZone = "headquarters";
} else if($userFolder == 'campus') {
	$globalUserType = "manager";
	$globalUserFolder = "campus";
	$globalUserManagerType = "campus";
	$globalUserManagerMenu = "campusMenu";
	$globalUserTypeTitle = _MANAGER;
	$globalUserTypeClass = "primary";
	$globalUserTypeBgClass = "blue";
	$globalZone = "campus";
	
	$campusID = $_COOKIE["campusID"];
} else {
	$globalUserType = "manager";
	$globalUserFolder = "school";
	$globalUserManagerType = "school";
	$globalUserManagerMenu = "branchMenu";
	$globalUserTypeTitle = _MANAGER;
	$globalUserTypeClass = "primary";
	$globalUserTypeBgClass = "blue";
	$globalZone = "school";
}
