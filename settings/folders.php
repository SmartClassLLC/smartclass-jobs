<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

//cms folder
$cmsFolder = "/school";

//files folder definition for further usage
$filesFolder = "files";

//attachments folder definition for further usage
$attachmentsFolder = $filesFolder. "/attachments/";
$attachmentsSubFolder = "attachments/";

//set logo files folder
$logoFileFolder = ($ySubeKodu == "0") ? $filesFolder. "/logo/" : $filesFolder. "/". $ySubeKodu."/logo/";
$logoFileSubFolder = ($ySubeKodu == "0") ? "logo/" : $ySubeKodu. "/logo/";

//set exam files folder for further usage
$examFilesFolder = $ySubeKodu. "/". $seasonYear. "/examfiles/";
$examFilesSubFolder = $seasonYear. "/examfiles/";

//set invoices folder
$invoicesFileFolder = ($ySubeKodu == 0) ? $filesFolder. "/invoices/" : $filesFolder. "/". $ySubeKodu. "/invoices/";
$invoicesFileSubFolder = ($ySubeKodu == 0) ? "invoices/" : $ySubeKodu. "/invoices/";

//set student photos folder
$studentFilesFolder = $filesFolder. "/". $ySubeKodu. "/". $seasonYear. "/students/";
$studentFilesSubFolder = $ySubeKodu. "/". $seasonYear. "/students/";

//set user files folder
$userFilesFolder = $filesFolder. "/userfiles/". $aid. "/";
$userFilesSubFolder = "userfiles/". $aid. "/";

//set student reports folder
$studentReportsFolder = "sReports/";
$examStudentReportFile = $studentReportsFolder."studentReport.php";

?>