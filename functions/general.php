<?php

//check if SMARTCLASS defined
if (!defined("SMARTCLASS")) {
    header("Location: index.php");
    die("SmartClass Undefined!");
}

//check if the file is tried to be accessed directly
if (stristr($_SERVER['SCRIPT_NAME'], "general.php")) {
    header("Location: index.php");
    die("SmartClass Undefined!");
}

function shutDownFunction()
{
    global $dbi, $supportEMail, $timeZone, $adminmail, $sitename, $simsInstanceUrlWithRequestUrl;

    $error = error_get_last();

    //$error = array("file", "line", "message", "type");
    if ($error["type"] == 1) //fatal error
    {
        //send an error log
        error_log($error["message"], 1, "smartclass.sims@gmail.com");

        ob_start();
        //phpconsole::error($error["message"]);
        ob_end_flush();
    } else if ($error["type"] == 2) //warning error
    {
        ob_start();
        //phpconsole::warn($error["message"]);
        ob_end_flush();
    }
}

/* function */
function fnCampusSchools($campusID, $asArray = false, $addHQ = false)
{
    global $dbi;

    if (empty($campusID)) return false;

    $dbi->where("parent_id", $campusID);
    $campusSchools = $dbi->getValue(_SUBELER_, "subeID", null);

    if ($addHQ) $campusSchools[] = 0;

    if ($asArray) return $campusSchools;
    else return implode(",", $campusSchools);
}

/* function */
function fnSchoolCampusId($schoolId)
{
    global $dbi, $ySubeKodu;

    if (empty($schoolId)) $schoolId = $ySubeKodu;

    $dbi->where("subeID", $schoolId);
    $campusId = $dbi->getValue(_SUBELER_, "parent_id");

    return $campusId;
}

/* function */
function YoneticiID($userId)
{
    global $dbi;

    return $dbi->where("aid", $userId)->getValue(_USERS_, "id");
}

/* function */
function YoneticiID2SubeID($userId)
{
    global $dbi;

    return $dbi->where("aid", $userId)->getValue(_USERS_, "ySubeKodu");
}

/* function */
function PersonelID($userId)
{
    global $dbi;

    $perId = $dbi->where("tckimlikno", $userId)->getValue(_PERSONEL_, "perID");

    return $perId;
}

/* function */
/* we should check this function and deprecate it */
function fnUserId2PersonelCepTel($id)
{
    global $dbi;

    $dbi->where("tckimlikno", $id);
    $ceptel = $dbi->getValue(_PERSONEL_, "ceptel");

    return $ceptel;
}

/* function */
function PreAddStatus($k)
{
    if ($k == "_PREADD_POSITIVE") return "<button class='btn btn-success'><i class='fa fa-thumbs-up'></i> " . _PREADD_POSITIVE . "</button>";
    elseif ($k == "_PREADD_NEGATIVE") return "<button class='btn btn-danger'><i class='fa fa-thumbs-down'></i> " . _PREADD_NEGATIVE . "</button>";
    elseif ($k == "_PREADD_RECALL") return "<button class='btn btn-primary'><i class='fa fa-phone'></i> " . _PREADD_RECALL . "</button>";
    elseif ($k == "_PREADD_NOCALL") return "<button class='btn btn-warning'><i class='fa fa-times'></i> " . _PREADD_NOCALL . "</button>";
}

/* function */
function FormatText2Language($k)
{
    return iconv("utf-8", "iso-8859-9//TRANSLIT", $k);
}

/* function */
function FormatTextBack2Language($k)
{
    $search = array('ç', 'Ç', 'ğ', 'Ğ', 'ı', 'İ', 'ö', 'Ö', 'ş', 'Ş', 'ü', 'Ü');
    $replace = array('c', 'C', 'g', 'G', 'i', 'I', 'o', 'O', 's', 'S', 'u', 'U');
    return str_replace($search, $replace, $k);
}

/* function */
function convertUrl2Regular($k)
{
    $search = array('%C3%A7', '%C3%87', '%C4%9F', '%C4%9E', '%C4%B1', '%C4%B0', '%C3%B6', 'o%CC%88', '%C3%96', '%C5%9F', '%C5%9E', '%C3%BC', '%C3%9C', '%20');
    $replace = array('ç', 'Ç', 'ğ', 'Ğ', 'ı', 'İ', 'ö', 'ö', 'Ö', 'ş', 'Ş', 'ü', 'Ü', ' ');
    return str_replace($search, $replace, $k);
}

/* function */
function myAllUpper($str)
{
    global $localization;

    switch ($localization) {
        case "tr":
            $lowerCaseTr = array('ç', 'ğ', 'ı', 'i', 'ü', 'ö', 'ş');
            $upperCaseTr = array('Ç', 'Ğ', 'I', 'İ', 'Ü', 'Ö', 'Ş');
            $str = str_replace($lowerCaseTr, $upperCaseTr, $str);
            $str = mb_convert_case($str, MB_CASE_UPPER, "UTF-8");
            break;

        case "en":
        case "my":
        case "th":
            $str = mb_convert_case($str, MB_CASE_UPPER, "UTF-8");
            break;
    }

    return $str;
}

/* function */
function myAllLower($str)
{
    global $localization;

    switch ($localization) {
        case "tr":
            $lowerCaseTr = array('ç', 'ğ', 'ı', 'i', 'ü', 'ö', 'ş');
            $upperCaseTr = array('Ç', 'Ğ', 'I', 'İ', 'Ü', 'Ö', 'Ş');
            $str = str_replace($upperCaseTr, $lowerCaseTr, $str);
            $str = mb_convert_case($str, MB_CASE_LOWER, "UTF-8");
            break;

        case "en":
        case "my":
        case "th":
            $str = mb_convert_case($str, MB_CASE_LOWER, "UTF-8");
            break;
    }

    return $str;
}

/* function */
function myFirstUpper($str)
{
    global $localization;

    switch ($localization) {
        case "tr":
            $strLength = strlen($str);
            $firstChar = mb_substr($str, 0, 1, "UTF-8");
            $strRest = mb_substr($str, 1, $strLength - 1, "UTF-8");

            $lowerCaseTR = array('ç', 'ğ', 'ı', 'i', 'ü', 'ö', 'ş');
            $upperCaseTr = array('Ç', 'Ğ', 'I', 'İ', 'Ü', 'Ö', 'Ş');
            $firstChar = str_replace($lowerCaseTR, $upperCaseTr, $firstChar);
            $strRest = str_replace($upperCaseTr, $lowerCaseTR, $strRest);

            $str = $firstChar . mb_convert_case($strRest, MB_CASE_LOWER, "UTF-8");
            break;

        case "en":
        case "my":
        case "th":
            $str = mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
            break;
    }

    return $str;
}

/* function */
function TCKimlik2ogrID($tckimlikno)
{
    global $dbi;

    return $dbi->where("TCKimlikNo", $tckimlikno)->getValue(_OGRENCILER_, "ogrID");
}

/* function */
function fnStudentUserId2StudentClassId($userId)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `SinifKodu` FROM " . _OGRENCILER_ . " WHERE `TCKimlikNo`='" . $userId . "'"));
    return $row["SinifKodu"];
}

/* function */
function fnStudentId2StudentName($ogrID)
{
    global $dbi;

    $row = $dbi->where("ogrID", $ogrID)->getOne(_OGRENCILER_, "Adi, IkinciAdi, Soyadi");

    return fnStudentName($row["Adi"], $row["IkinciAdi"], $row["Soyadi"]);
}

/* function */
function fnStudentName($first, $second, $last)
{
    return $first . ($second != "" ? " " : "") . $second . " " . $last;
}

/* function */
/* we can deprecate this function */
/* use this one insted fnStdId2StdInfo($ogrID, "TCKimlikNo") */
function fnOgrID2ogrTCNo($ogrID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `TCKimlikNo` FROM " . _OGRENCILER_ . " WHERE `ogrID`='" . $ogrID . "'"));
    return $row["TCKimlikNo"];
}

/* function */
function OgrenciNo($ogrID)
{
    global $dbi;
    $row = $dbi->where("ogrID", $ogrID)->getOne(_OGRENCILER_, "ogrenciNo");
    return $row["ogrenciNo"];
}

/* function */
function CountryName($countryID)
{
    global $dbi;

    $dbi->where("isoCode", $countryID);
    $row = $dbi->getOne(_COUNTRIES_, "country");

    return $row["country"];
}

/* function */
function NumberOfAbsence($stdId)
{
    global $db, $ySubeKodu;

    $totalNumberOfAbs = totalNumberOfAbsence(0, $stdId);

    return $totalNumberOfAbs;
}

/* function */
function totalNumberOfAbsence($termId = 0, $stdId)
{
    global $dbi, $ySubeKodu;

    $absences = array();

    if ($termId > 0) {
        //get term info
        $dbi->where("Id", $termId);
        $termInfo = $dbi->getOne(_GRADING_TERMS_, "startDate, endDate");

        //check current season
        $dbi->where("attDate", array($termInfo["startDate"], $termInfo["endDate"]), "BETWEEN");
    }

    $dbi->where("stdId", $stdId);
    $dbi->where("schoolId", $ySubeKodu);
    $dbi->groupBy("catCode");
    $dailyAbsences = $dbi->get(_GUNLUK_DEVAMSIZLIK_, null, "catCode, catId, SUM(nofDays) AS total");

    $total = 0;
    foreach ($dailyAbsences as $dailyAbsence) {
        //get setting info
        $stype = substr($dailyAbsence["catCode"], 0, 1);

        if ($stype == "t") {
            $dbi->where("id", $dailyAbsence["catId"]);
            $dbi->orderBy("id", "asc");
            $settingInfo = $dbi->getOne(_DEVAMSIZLIK_KATEGORILERI_, "category as title");
        } else if ($stype == "r") {
            $dbi->where("sebebID", $dailyAbsence["catId"]);
            $dbi->orderBy("sebebID", "asc");
            $settingInfo = $dbi->getOne(_DEVAMSIZLIK_TURLERI_, "CONCAT(sebebAdi, ' [', sebebSymbol, ']') as title");
        }

        if (!empty($settingInfo["title"])) {
            $absences[$settingInfo["title"]] = $dailyAbsence["total"];
            $total += $dailyAbsence["total"];
        }
    }

    $absences["total"] = $total;

    return $absences;
}

/* function */
function showPhoto($photo, $parameter = "", $mywidth = "45px", $myclass = "img-circle", $asFile = false, $checkUrl = true)
{
    global $siteurl;

    //temp fix if old system has been used
    $photo = str_replace("..", $siteurl, $photo);

    //check if the file exists
    if ($checkUrl) $fileExist = checkURL($photo);
    else $fileExist = true;

    if ($fileExist) $photo2Send = $photo;
    else if ($parameter == "E") $photo2Send = 'img/male.png';
    else if ($parameter == "K") $photo2Send = 'img/female.png';
    else $photo2Send = 'img/nopicture.png';

    if ($asFile) return $photo2Send;
    else return "<img src='" . $photo2Send . "' style='width:" . $mywidth . "; height:" . $mywidth . "' class='" . $myclass . "'>";
}

/* function */
//fix picture location as absolute
//switch ../files/* to DOCUMENT_ROOT/files
function absoluteFileLocation($filename)
{
    //realpath(dirname($file)
    return str_replace("..", $_SERVER["DOCUMENT_ROOT"], $filename);
}

/* function */
function RandomizedFileTitle($fileName)
{
    $t = explode("_", $fileName);
    return str_replace($t[0] . "_" . $t[1] . "_", "", $fileName);
}

function myFileManagerLink($link)
{
    $accessKey = md5("SmartClassFileManager");
    $myLink = $link . "&akey=" . $accessKey . "&lang=" . _LANG_SHORT_;
    $myLink = "dialog.php?sc=" . base64_encode($myLink);

    return $myLink;
}

/* function */
function fnPersonnelID2UserID($perID)
{
    global $dbi;

    $dbi->where("perID", $perID);
    $row = $dbi->getOne(_PERSONEL_, "tckimlikno");

    return $row["tckimlikno"];
}

/* function */
//fix this according to the user info
function showUserPhoto($user, $class = "img-circle", $width = "45px")
{
    global $dbi;

    $photo = $dbi->where("aid", $user)->getValue(_USERS_, "picture");

    if (!empty($photo)) {
        return "<img src='" . $photo . "' style='width:" . $width . "; height:" . $width . "' class='" . $class . "'>";
//    } elseif ($parameter == "E") {
//        return "<img src='img/male.png' style='width:" . $width . "; height:" . $width . "' class='" . $class . "'>";
//    } elseif ($parameter == "K") {
//        return "<img src='img/female.png' style='width:" . $width . "; height:" . $width . "' class='" . $class . "'>";
    } else {
        return "<img src='img/nopicture.png' style='width:" . $width . "; height:" . $width . "' class='" . $class . "'>";
    }
}

/* function */
function fnWfType2WfTitle($wfType, $title = true, $icon = false)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `wfIcon`, `wfTitle` FROM " . _WORKFLOW_DEFINITIONS_ . " WHERE `wfID`='" . $wfType . "'"));
    $wfTitle = ($icon) ? "<i class='fa fa-" . $row["wfIcon"] . "'></i> " : "";
    $wfTitle .= ($title) ? translateWord($row["wfTitle"]) : "";
    return $wfTitle;
}

/* function */
function createWorkflow($wfType, $paramId, $messageParameters = array(), $managerId = "", $personnelId = "", $parentId = "", $studentId = "", $schoolId = "")
{
    global $db, $ySubeKodu, $aid;

    if ($schoolId == "") $schoolId = $ySubeKodu;

    $wfTime = date("Y-m-d H:i:s");

    //message parameters
    $messageParameters = serialize($messageParameters);

    /* create approval and end type workflow */

    //get processes
    $qWorkflowApprovals = $db->sql_query("SELECT * FROM " . _WORKFLOWS_ . " WHERE `wfType`='" . $wfType . "' AND `schoolId`='" . $schoolId . "' AND `processType` IN ('approval', 'end') ORDER BY FIELD(`processType`, 'start', 'approval', 'end') ASC, `approveOrder` ASC");
    while ($rWorkflowApprovals = $db->sql_fetchrow($qWorkflowApprovals)) {
        //approvers as array
        $approvers = array();

        //managers
        $managers = $managerId != "" ? array($managerId) : array();

        //get user approver if set
        if ($rWorkflowApprovals["approveUser"] != "") {
            //add to approvers
            $approvers[] = $rWorkflowApprovals["approveUser"];

            //add to managers as well
            $managers[] = $rWorkflowApprovals["approveUser"];
        }

        //get user type approver(s) if set
        $qUserTypeApprovers = $db->sql_query("SELECT `aid` FROM " . _USERS_ . " WHERE `userType`='" . $rWorkflowApprovals["approveUserType"] . "' AND `radminsuper`='0' AND `active`='1' AND `ySubeKodu`='" . $schoolId . "'");
        while ($rUserTypeApprovers = $db->sql_fetchrow($qUserTypeApprovers)) {
            //add to approvers
            $approvers[] = $rUserTypeApprovers["aid"];

            //add to managers as well
            $managers[] = $rUserTypeApprovers["aid"];
        }

        //approver to save
        $approver = implode(",", $approvers);

        //managerId to save
        $manager = implode(",", $managers);

        //insert approve procedure
        $add = $db->sql_query("INSERT INTO " . _WORKFLOWS_APPROVALS_ . " (`wfType`, `approver`, `approveCreatedBy`, `approveCreatedOn`, `approveOrder`, `processType`, `messageParameters`, `workflowId`, `parameterId`, `managerId`, `personnelId`, `parentId`, `studentId`, `schoolId`) VALUES ('" . $wfType . "', '" . $approver . "', '" . $aid . "', '" . $wfTime . "', '" . $rWorkflowApprovals["approveOrder"] . "', '" . $rWorkflowApprovals["processType"] . "', '" . $messageParameters . "', '" . $rWorkflowApprovals["Id"] . "', '" . $paramId . "', '" . $manager . "', '" . $personnelId . "', '" . $parentId . "', '" . $studentId . "', '" . $schoolId . "')");
        if (!$add) return _WORKFLOW_COULD_NOT_BE_SET;
    }

    return true;
}


/* function */
/*
 * check if the workflow process request confirm
*/
function workflowNeedConfirm($wfType, $processType = "", $schoolId = "")
{
    global $db, $ySubeKodu;

    if ($processType == "") $processType = "approval";
    if ($schoolId == "") $schoolId = $ySubeKodu;

    //check approval process
    $qApprovalProcessDefinition = $db->sql_query("SELECT `approveUser`, `approveUserType` FROM " . _WORKFLOWS_ . " WHERE `wfType`='" . $wfType . "' AND `schoolId`='" . $schoolId . "' AND `processType`='" . $processType . "' ORDER BY FIELD(`processType`, 'start', 'approval', 'end') ASC, `approveOrder` ASC");
    while ($rApprovalProcessDefinition = $db->sql_fetchrow($qApprovalProcessDefinition)) {
        if ($rApprovalProcessDefinition["approveUser"] != "") return true;
        else if ($rApprovalProcessDefinition["approveUser"] != "0") return true;
    }

    return false;
}

/* function */
/*
 * add actions for workflows so it can be ran automatically
*/
function addWorkflowAction($wfType, $parameterId, $actionQuery, $actionParameters = array(), $actionType = "approved", $processType = "approval")
{
    global $db;

    $actionParameters = serialize($actionParameters);

    $actionQuery = myfilter($actionQuery, "", "1");

    //add action
    $addAction = $db->sql_query("INSERT INTO " . _WORKFLOWS_ACTIONS_ . " (`action`, `actionParameters`, `actionType`, `processType`, `parameterId`, `wfType`) VALUES ('" . $actionQuery . "', '" . $actionParameters . "', '" . $actionType . "', '" . $processType . "', '" . $parameterId . "', '" . $wfType . "')");
    if ($addAction) return true;
    else return false;
}

/* function */
/*
 * return 1: process is over or there is no process
 * return 2: approval process is running
 * return 3: end process is running
*/
function runWorkflow($wfType, $paramId, $workflowAction = "", $schoolId = "")
{
    global $db, $ySubeKodu, $aid, $simsDateTime;

    if ($schoolId == "") $schoolId = $ySubeKodu;

    if ($workflowAction == "") $workflowAction = "approved";

    $wfTime = $simsDateTime;

    //check if approval process is running
    $qApprovalProcess = $db->sql_query("SELECT a.`Id`, a.`processType`, a.`messageParameters`, a.`managerId`, a.`personnelId`, a.`parentId`, a.`studentId`, w.`emailManager`, w.`smsManager`, w.`emailTeacher`, w.`smsTeacher`, w.`emailParent`, w.`smsParent`, w.`emailStudent`, w.`smsStudent` FROM " . _WORKFLOWS_APPROVALS_ . " a LEFT JOIN " . _WORKFLOWS_ . " w ON a.`workflowId`=w.`Id` WHERE a.`wfType`='" . $wfType . "' AND a.`processType` IN ('approval', 'end') AND a.`status`='none' AND a.`parameterId`='" . $paramId . "' AND a.`schoolId`='" . $schoolId . "' ORDER BY FIELD(a.`processType`, 'start', 'approval', 'end') ASC, a.`approveOrder` ASC");
    while ($rApprovalProcess = $db->sql_fetchrow($qApprovalProcess)) {
        if ($workflowAction == "approved") {
            //set workflows created date and created by values
            $db->sql_query("UPDATE " . _WORKFLOWS_APPROVALS_ . " SET `approveCreatedBy`='" . $aid . "', `approveCreatedOn`='" . $wfTime . "' WHERE `Id`='" . $rApprovalProcess["Id"] . "'");

            //make messageParameters array
            $messageParameters = unserialize($rApprovalProcess["messageParameters"]);
            $messageParameters["{TEACHER_FIRST_LASTNAME}"] = YoneticiAdi($rApprovalProcess["personnelId"]);
            $messageParameters["{PARENT_FIRST_LASTNAME}"] = YoneticiAdi($rApprovalProcess["parentId"]);
            $messageParameters["{STUDENT_NAME_LASTNAME}"] = YoneticiAdi($rApprovalProcess["studentId"]);
            $messageParameters["{STUDENT_CLASS}"] = sinifAdi(fnStudentUserId2StudentClassId($rApprovalProcess["studentId"]));

            //notification for managers if there is a managerId
            if ($rApprovalProcess["managerId"] != "") {
                //make managerIds array
                $managersArray = explode(",", $rApprovalProcess["managerId"]);

                foreach ($managersArray as $key => $value) {
                    $myMessageParameters = $messageParameters;
                    $myMessageParameters["{FIRST_LASTNAME}"] = YoneticiAdi($value);

                    //send email to manager
                    if (intval($rApprovalProcess["emailManager"]) > 0) {
                        //fix subject and content
                        $emailSubject = AdoptEMailSubject2Template($rApprovalProcess["emailManager"], $myMessageParameters);
                        $emailContent = AdoptEMailContent2Template($rApprovalProcess["emailManager"], $myMessageParameters);
                        $emailContent = externalMessageTemplate($value, $emailContent);

                        //send email
                        sendEmail(UserEMail($value), $emailSubject, $emailContent);

                        //send internal message
                        sendInternalMessage($wfTime, $emailSubject, $emailContent, $aid, $value);
                    }

                    //send sms to manager
                    if (intval($rApprovalProcess["smsManager"]) > 0) {
                        //fix subject and content
                        $smsContent = AdoptMessageToTemplate($rApprovalProcess["smsManager"], $myMessageParameters);

                        //send sms
                        sendSMS(fnUserId2PersonelCepTel($value), YoneticiAdi($value), $smsContent);
                    }
                }
            }

            //notification for teacher if there is a personnelId
            if ($rApprovalProcess["personnelId"] != "") {
                $myMessageParameters = $messageParameters;
                $myMessageParameters["{FIRST_LASTNAME}"] = YoneticiAdi($rApprovalProcess["personnelId"]);

                //send email to the teacher
                if (intval($rApprovalProcess["emailTeacher"]) > 0) {
                    //fix subject and content
                    $emailSubject = AdoptEMailSubject2Template($rApprovalProcess["emailTeacher"], $myMessageParameters);
                    $emailContent = AdoptEMailContent2Template($rApprovalProcess["emailTeacher"], $myMessageParameters);
                    $emailContent = externalMessageTemplate($rApprovalProcess["personnelId"], $emailContent);

                    //send email
                    sendEmail(UserEMail($rApprovalProcess["personnelId"]), $emailSubject, $emailContent);

                    //send internal message
                    sendInternalMessage($wfTime, $emailSubject, $emailContent, $aid, $rApprovalProcess["personnelId"]);
                }

                //send sms to manager
                if (intval($rApprovalProcess["smsTeacher"]) > 0) {
                    //fix content
                    $smsContent = AdoptMessageToTemplate($rApprovalProcess["smsTeacher"], $myMessageParameters);

                    //send sms
                    sendSMS(fnPerId2PerInfo(fnUserId2OtherId($rApprovalProcess["personnelId"]), "ceptel"), YoneticiAdi($rApprovalProcess["personnelId"]), $smsContent);
                }
            }

            //notification for parent if there is a parentId
            if ($rApprovalProcess["parentId"] != "") {
                $myMessageParameters = $messageParameters;
                $myMessageParameters["{FIRST_LASTNAME}"] = YoneticiAdi($rApprovalProcess["parentId"]);

                //send email to the teacher
                if (intval($rApprovalProcess["emailParent"]) > 0) {
                    //fix subject and content
                    $emailSubject = AdoptEMailSubject2Template($rApprovalProcess["emailParent"], $myMessageParameters);
                    $emailContent = AdoptEMailContent2Template($rApprovalProcess["emailParent"], $myMessageParameters);
                    $emailContent = externalMessageTemplate($rApprovalProcess["parentId"], $emailContent);

                    //send email
                    sendEmail(UserEMail($rApprovalProcess["parentId"]), $emailSubject, $emailContent);

                    //send internal message
                    sendInternalMessage($wfTime, $emailSubject, $emailContent, $aid, $rApprovalProcess["parentId"]);
                }

                //send sms to manager
                if (intval($rApprovalProcess["smsParent"]) > 0) {
                    //fix content
                    $smsContent = AdoptMessageToTemplate($rApprovalProcess["smsParent"], $myMessageParameters);

                    //send sms
                    sendSMS(fnParentId2ParentInfo(fnUserId2OtherId($rApprovalProcess["parentId"], "parent"), "v_ceptel"), YoneticiAdi($rApprovalProcess["parentId"]), $smsContent);
                }
            }

            //notification for student if there is a studentId
            if ($rApprovalProcess["studentId"] != "") {
                $myMessageParameters = $messageParameters;
                $myMessageParameters["{FIRST_LASTNAME}"] = YoneticiAdi($rApprovalProcess["studentId"]);

                //send email to the teacher
                if (intval($rApprovalProcess["emailStudent"]) > 0) {
                    //fix subject and content
                    $emailSubject = AdoptEMailSubject2Template($rApprovalProcess["emailStudent"], $myMessageParameters);
                    $emailContent = AdoptEMailContent2Template($rApprovalProcess["emailStudent"], $myMessageParameters);
                    $emailContent = externalMessageTemplate($rApprovalProcess["studentId"], $emailContent);

                    //send email
                    sendEmail(UserEMail($rApprovalProcess["studentId"]), $emailSubject, $emailContent);

                    //send internal message
                    sendInternalMessage($wfTime, $emailSubject, $emailContent, $aid, $rApprovalProcess["studentId"]);
                }

                //send sms to manager
                if (intval($rApprovalProcess["smsStudent"]) > 0) {
                    //fix content
                    $smsContent = AdoptMessageToTemplate($rApprovalProcess["smsStudent"], $myMessageParameters);

                    //send sms
                    sendSMS(fnStdId2StdInfo(fnUserId2OtherId($rApprovalProcess["studentId"], "student"), "OgrenciCepTel"), YoneticiAdi($rApprovalProcess["studentId"]), $smsContent);
                }
            }

            //if there is no confirm for the process keep going
            //otherwise break the loop
            if (workflowNeedConfirm($wfType, $rApprovalProcess["processType"], $schoolId)) {
                return true;
                break;
            }
        } else if ($workflowAction == "declined") {
            //set workflows created date and created by values
            $db->sql_query("UPDATE " . _WORKFLOWS_APPROVALS_ . " SET `status`='declined' WHERE `Id`='" . $rApprovalProcess["Id"] . "'");
        }
    }

    return true;
}

/* function */
/*
* runWorkflowActions
* check if any action has been defined along the process
* if any action defined run the action
*/
function runWorkflowActions($wfType, $paramId, $workflowAction = "", $processType = "")
{
    global $db, $ySubeKodu, $aid, $simsDateTime;

    if ($workflowAction == "") $workflowAction = "approved";

    if ($processType == "") $processType = "approval";

    //check process actions
    $qWorkflowActions = $db->sql_query("SELECT `Id`, `action`, `actionParameters` FROM " . _WORKFLOWS_ACTIONS_ . " WHERE `wfType`='" . $wfType . "' AND `parameterId`='" . $paramId . "' AND `actionType`='" . $workflowAction . "' AND `processType`='" . $processType . "' ");
    while ($rWorkflowActions = $db->sql_fetchrow($qWorkflowActions)) {
        $actionQuery = myfilter($rWorkflowActions["action"]);
        $actionParameters = unserialize($rWorkflowActions["actionParameters"]);

        //fix the query in order to get the real query
        foreach ($actionParameters as $key => $value) {
            $realValue = $$value;
            $actionQuery = str_replace("{" . $value . "}", $realValue, $actionQuery);
        }

        //run the query
        $runActionQuery = $db->sql_query($actionQuery);

        if ($runActionQuery) {
            //save action log
            $runLog = $db->sql_query("INSERT INTO " . _WORKFLOWS_ACTION_RUNS_ . " (`runBy`, `runOn`, `actionId`) VALUES ('" . $aid . "', '" . $simsDateTime . "', '" . $rWorkflowActions["Id"] . "')");

            if ($runLog) return true;
            else return false;
        } else {
            return false;
        }
    }
}

/* function */
function sendInternalMessage($sentTime, $msgSubject, $msgMessage, $msgFrom, $msgTo, $msgCC = "", $msgAttachments = "", $composeType = "")
{
    global $dbi, $ySubeKodu, $simsInstanceUrl;

    //fix school id for apis
    if (is_null($ySubeKodu)) $ySubeKodu = "0";

    //check msgTo first and if it is not coming then return false
    if (empty($msgTo)) return false;

    //get msgTo for convenience
    $msgTo = is_array($msgTo) ? implode(",", $msgTo) : $msgTo;

    //get msgCC for convenience
    $msgCC = is_array($msgCC) ? implode(",", $msgCC) : $msgCC;

    //get msgAttachments for convenience
    if (!empty($msgAttachments)) $msgAttachments = implode(",", $msgAttachments);

    //data
    $queryData = array(
        "attachment" => $msgAttachments,
        "subject" => $msgSubject,
        "msgBody" => $msgMessage,
        "fromUser" => $msgFrom,
        "toUser" => $msgTo,
        "ccUser" => $msgCC,
        "sentTime" => $sentTime,
        "schoolId" => $ySubeKodu
    );

    //insert
    $result = $dbi->insert(_MESSAGES_, $queryData);

    if ($result) {
        //get message id just added
        $messageId = $result;

        //save the message to unread and unstarred messages for all users in `to` or `cc` field
        $msgToDizi = explode(",", $msgTo);
        $msgCCDizi = explode(",", $msgCC);

        //merge to and cc receivers
        $msgAllReceivers = empty($msgCC) ? $msgToDizi : array_merge($msgToDizi, $msgCCDizi);

        //unique receivers
        $msgAllReceivers = array_unique($msgAllReceivers);

        //error
        $hata = 0;

        //save for unread messages
        foreach ($msgAllReceivers as $receiver) {
            $kayit = $dbi->insert(_READ_MESSAGES_, array("msgID" => $messageId, "userCode" => $receiver));
            if ($kayit) {
                //make url for the notification
                $notifyUrl = $simsInstanceUrl;

                //user type of the receiver
                $receiverUserType = YoneticiKullaniciTuru($receiver);

                //login type of the user type
                if ($receiverUserType == "6" or $receiverUserType == "7") $userFolder = "teacher";
                else if ($receiverUserType == "8") $userFolder = "student";
                else if ($receiverUserType == "9") $userFolder = "parent";
                else {
                    $hqUser = GenelMudurluk($receiver);

                    $userFolder = ($hqUser) ? "headquarters" : "school";
                }

                //notify url
                $notifyUrl = $notifyUrl . "/" . $userFolder . "/index.php?n=" . $messageId;

                //send notifications
                sendNotification($receiver, $msgMessage, $msgSubject, array("n" => $messageId), $msgMessage, $notifyUrl);
            } else {
                $hata = 1;
            }
        }

        //if an error happens then return error
        if ($hata) return array('message' => array('error' => _ERROR_));

        $queryData["emailID"] = $messageId;

        //save the message to the sent db
        $kayit2 = $dbi->insert(_SENT_MESSAGES_, $queryData);
        if ($kayit2) {
            switch ($composeType) {
                case "reply":
                case "replyAll":
                case "forward":
                    return array('message' => array('success' => _MESSAGE_SENT), "hide" => "#mySubModal");
                    break;

                case "ticket-message":
                    return array('message' => array('success' => _MESSAGE_SENT));
                    break;

                default:
                    return array('message' => array('success' => _MESSAGE_SENT), "hide" => "#myModal");
                    break;
            }
        } else {
            return array('message' => array('error' => _DATABASE_ERROR));
        }
    } else {
        return array('message' => array('error' => _DATABASE_ERROR));
    }

    return false;
}

/* function */
function GCType($type)
{
    if ($type == "class") return _CLASS_TYPE_REGULAR_CLASS;
    else if ($type == "breakfast") return _BREAKFAST;
    else if ($type == "lunch") return _LUNCH;
    else if ($type == "afternoon") return _AFTERNOON_BREAKFAST;
    else if ($type == "afterschool") return _AFTER_SCHOOL;
    else if ($type == "book") return _BOOK_READING_HOUR;
    else return _CLASS_TYPE_REGULAR_CLASS;
}

/* function */
function fnEmailTemplate4SetPassword($greeting = "", $setLink = "")
{
    global $siteurl, $sitename, $currentlang, $supportEMail;

    $myMessage = file_get_contents(__DIR__ . "/../common/EMail/template/set_password.php");

    //set site url
    $myMessage = str_replace('{SITEURL}', $siteurl, $myMessage);

    //site school logo
    $myMessage = str_replace('{SCHOOL_LOGO}', scSchoolLogo("2"), $myMessage);

    //add greeting
    $myMessage = str_replace('{DEAR_USER}', $greeting, $myMessage);

    //add title
    $myMessage = str_replace('{YOUR_USER_HAS_BEEN_CREATED}', YOUR_USER_HAS_BEEN_CREATED, $myMessage);

    //add content
    $myMessage = str_replace('{SET_PASSWORD_FOR_YOUR_USER_USING_THE_LINK_BELOW}', SET_PASSWORD_FOR_YOUR_USER_USING_THE_LINK_BELOW, $myMessage);
    $myMessage = str_replace('{_LINK_IS_VALID_FOR_30_MINS}', _LINK_IS_VALID_FOR_30_MINS, $myMessage);

    //add reset link
    $myMessage = str_replace('{SET_LINK}', $setLink, $myMessage);

    //add button language
    $myMessage = str_replace('{BUTTON_LANGUAGE}', $currentlang, $myMessage);

    //set school name
    $myMessage = str_replace('{SCHOOL_NAME}', $sitename, $myMessage);

    //set support link
    $myMessage = str_replace('{PROBLEMS_OR_QUESTIONS}', _EMAIL_CONTENT_PROBLEMS_OR_QUESTIONS, $myMessage);

    //set support email
    $myMessage = str_replace('{SUPPORT_EMAIL}', $supportEMail, $myMessage);

    //add smartclass title
    $myMessage = str_replace('{SMARTCLASS_TITLE}', _SMARTCLASS_SCHOOL_MANAGEMENT_TITLE_, $myMessage);

    return $myMessage;
}

/* function */
function fnEmailTemplate4ResetPassword($greeting = "", $resetLink = "")
{
    global $siteurl, $sitename, $currentlang, $supportEMail;

    $myMessage = file_get_contents(__DIR__ . "/../common/EMail/template/reset_password.php");

    //set site url
    $myMessage = str_replace('{SITEURL}', $siteurl, $myMessage);

    //site school logo
    $myMessage = str_replace('{SCHOOL_LOGO}', scSchoolLogo("2"), $myMessage);

    //add title
    $myMessage = str_replace('{FORGOT_YOUR_PASSWORD}', _DID_YOU_FORGET_PASSWORD, $myMessage);

    //add greeting
    $myMessage = str_replace('{DEAR_USER}', $greeting, $myMessage);

    //add content
    $myMessage = str_replace('{FORGOT_YOUR_PASSWORD_EXPLANATION}', _USE_LINK_TO_RESET_PASSWORD, $myMessage);

    //add reset link
    $myMessage = str_replace('{RESET_LINK}', $resetLink, $myMessage);

    //add button language
    $myMessage = str_replace('{BUTTON_LANGUAGE}', $currentlang, $myMessage);

    //set school name
    $myMessage = str_replace('{SCHOOL_NAME}', $sitename, $myMessage);

    //set support link
    $myMessage = str_replace('{PROBLEMS_OR_QUESTIONS}', _EMAIL_CONTENT_PROBLEMS_OR_QUESTIONS, $myMessage);

    //set support email
    $myMessage = str_replace('{SUPPORT_EMAIL}', $supportEMail, $myMessage);

    //add smartclass title
    $myMessage = str_replace('{SMARTCLASS_TITLE}', _SMARTCLASS_SCHOOL_MANAGEMENT_TITLE_, $myMessage);

    return $myMessage;
}

/* function */
function fnEmailTemplate4PasswordChanged($greeting = "")
{
    global $siteurl, $sitename, $currentlang, $supportEMail;

    $myMessage = file_get_contents(__DIR__ . "/../common/EMail/template/password_changed.php");

    //set site url
    $myMessage = str_replace('{SITEURL}', $siteurl, $myMessage);

    //site school logo
    $myMessage = str_replace('{SCHOOL_LOGO}', scSchoolLogo("2"), $myMessage);

    //add greeting
    $myMessage = str_replace('{DEAR_USER}', $greeting, $myMessage);

    //add content
    $myMessage = str_replace('{_KULLANICI_SIFRENIZ_DEGISTIRILMISTIR}', _KULLANICI_SIFRENIZ_DEGISTIRILMISTIR, $myMessage);
    $myMessage = str_replace('{_BILGINIZ_DISINDA_ISE_HABER_VERINIZ}', _BILGINIZ_DISINDA_ISE_HABER_VERINIZ, $myMessage);

    //set school name
    $myMessage = str_replace('{SCHOOL_NAME}', $sitename, $myMessage);

    //set support link
    $myMessage = str_replace('{PROBLEMS_OR_QUESTIONS}', _EMAIL_CONTENT_PROBLEMS_OR_QUESTIONS, $myMessage);

    //set support email
    $myMessage = str_replace('{SUPPORT_EMAIL}', $supportEMail, $myMessage);

    //add smartclass title
    $myMessage = str_replace('{SMARTCLASS_TITLE}', _SMARTCLASS_SCHOOL_MANAGEMENT_TITLE_, $myMessage);

    return $myMessage;
}

/* function */
function externalMessageTemplate($receiverId, $content, $senderId = "system", $signature = true, $useHTML = false)
{
    global $ySubeKodu, $aid, $currentlang, $supportEMail, $site_favicon;

    if ($useHTML) {
        $extContent = '<head>
		    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		    <!--[if !mso]><!-->
		    <meta http-equiv="X-UA-Compatible" content="IE=edge">
		    <!--<![endif]-->
		    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		</head>
		<body style="margin:0; padding:0;">';
        $extContent .= $content;
        $extContent .= '</body>';

        return $extContent;
    } else {
        if ($signature) {
            if ($senderId == "system") $myMessage = file_get_contents(__DIR__ . "/../common/EMail/template/system_email.php");
            else $myMessage = file_get_contents(__DIR__ . "/../common/pages/EMail/template/user_email.php");

            //set email content
            $myMessage = str_replace('{EMAIL_CONTENT}', $content, $myMessage);

            //set receiver info
            if (empty($receiverId)) {
                $myMessage = str_replace('{RECEIVER_NAME}', "", $myMessage);
                $myMessage = str_replace('{RECEIVER_EMAIL}', "", $myMessage);
            } else {
                $myMessage = str_replace('{RECEIVER_NAME}', YoneticiAdi($receiverId), $myMessage);

                if (filter_var($receiverId, FILTER_VALIDATE_EMAIL)) $myMessage = str_replace('{RECEIVER_EMAIL}', $receiverId, $myMessage);
                else $myMessage = str_replace('{RECEIVER_EMAIL}', UserEMail($receiverId), $myMessage);
            }

            if (UserPicture($receiverId) != "") $myMessage = str_replace('{RECEIVER_PHOTO}', UserPicture($receiverId), $myMessage);
            else $myMessage = str_replace('{RECEIVER_PHOTO}', "https://cdn.smartclass.tech/images/" . $currentlang . "/no_image.jpg", $myMessage);

            //set sender info
            if ($senderId == "system") {
                $myMessage = str_replace('{SENDER_PHOTO}', $site_favicon, $myMessage);
                $myMessage = str_replace('{SENDER_SCHOOL}', BranchName($ySubeKodu, false), $myMessage);
                $myMessage = str_replace('{SENDER_SCHOOL_INFO}', BranchContactInfo($ySubeKodu), $myMessage);
            } else {
                $myMessage = str_replace('{SENDER_NAME}', YoneticiAdi($senderId), $myMessage);
                $myMessage = str_replace('{SENDER_EMAIL}', UserEMail($senderId), $myMessage);

                if (UserPicture($senderId) != "") $myMessage = str_replace('{SENDER_PHOTO}', UserPicture($senderId), $myMessage);
                else $myMessage = str_replace('{SENDER_PHOTO}', "https://cdn.smartclass.tech/images/" . $currentlang . "/no_image.jpg", $myMessage);

                $myMessage = str_replace('{SENDER_SCHOOL}', BranchName($ySubeKodu, false), $myMessage);
                $myMessage = str_replace('{SENDER_SCHOOL_INFO}', BranchContactInfo($ySubeKodu), $myMessage);
            }
        } else {
            $myMessage = file_get_contents(__DIR__ . "/../common/EMail/template/no_signature_email.php");

            //set email content
            $myMessage = str_replace('{EMAIL_CONTENT}', $content, $myMessage);

            //set receiver info
            if (empty($receiverId)) {
                $myMessage = str_replace('{RECEIVER_NAME}', "", $myMessage);
                $myMessage = str_replace('{RECEIVER_EMAIL}', "", $myMessage);
            } else {
                $myMessage = str_replace('{RECEIVER_NAME}', YoneticiAdi($receiverId), $myMessage);

                if (filter_var($receiverId, FILTER_VALIDATE_EMAIL)) $myMessage = str_replace('{RECEIVER_EMAIL}', $receiverId, $myMessage);
                else $myMessage = str_replace('{RECEIVER_EMAIL}', UserEMail($receiverId), $myMessage);
            }
        }

        //set support link
        $myMessage = str_replace('{PROBLEMS_OR_QUESTIONS}', _EMAIL_CONTENT_PROBLEMS_OR_QUESTIONS, $myMessage);

        //set support email
        $myMessage = str_replace('{SUPPORT_EMAIL}', $supportEMail, $myMessage);

        //add smartclass title
        $myMessage = str_replace('{SMARTCLASS_TITLE}', _SMARTCLASS_SCHOOL_MANAGEMENT_TITLE_, $myMessage);

        return $myMessage;
    }
}

/* function */
/*
* _INTERNAL_MESSAGE_HEADER has 3 parameters
* {SENDERNAME} is string
* {SENDEREMAIL} is string
* {DATETIME} is datetime
*/
function internalMessageReplyHeader($senderId, $sentTime)
{
    //get header string defined by language files
    $headerString = _INTERNAL_MESSAGE_HEADER . "<br>";

    //get sender name and lastname
    $senderName = fnUserId2UserInfo($senderId, "name,lastName");

    //get sender email
    $senderEmail = fnUserId2UserInfo($senderId, "email");

    //get local senttime
    $sentTime = FormatDateNumeric2Local($sentTime, 1, true);

    //set replacement arrays
    $replacementSearch = array("{SENDERNAME}", "{SENDEREMAIL}", "{DATETIME}");
    $replacementReplace = array($senderName, $senderEmail, $sentTime);

    //make replacements
    $returnString = str_replace($replacementSearch, $replacementReplace, $headerString);

    //return it
    return $returnString;
}

/* function */
/*
* _INTERNAL_MESSAGE_HEADER has 3 parameters
* {SENDERNAME} is string
* {SENDEREMAIL} is string
* {DATETIME} is datetime
*/
function externalMessageReplyHeader($senderId, $sentTime)
{
    //get header string defined by language files
    $headerString = _EXTERNAL_MESSAGE_HEADER . "<br>";

    //set replacement arrays
    $replacementSearch = array("{SENDERINFO}", "{DATETIME}");
    $replacementReplace = array($senderId, $sentTime);

    //make replacements
    $returnString = str_replace($replacementSearch, $replacementReplace, $headerString);

    //return it
    return $returnString;
}

/* function */
/*
* _INTERNAL_MESSAGE_HEADER has 3 parameters
* {SENDERNAME} is string
* {SENDEREMAIL} is string
* {DATETIME} is datetime
*/
function internalMessageForwardHeader($senderId, $sentTime)
{
    //get header string defined by language files
    $headerString = _INTERNAL_MESSAGE_HEADER . "<br>";

    //get sender name and lastname
    $senderName = fnUserId2UserInfo($senderId, "name,lastName");

    //get sender email
    $senderEmail = fnUserId2UserInfo($senderId, "email");

    //get local senttime
    $sentTime = FormatDateNumeric2Local($sentTime, true, true);

    //set replacement arrays
    $replacementSearch = array("{SENDERNAME}", "{SENDEREMAIL}", "{DATETIME}");
    $replacementReplace = array($senderName, $senderEmail, $sentTime);

    //start return string
    $returnString = "----------" . _FORWARDED_MESSAGE . "----------<br>";

    //make replacements
    $returnString .= str_replace($replacementSearch, $replacementReplace, $headerString);

    //return it
    return $returnString;
}

/* function */
/*
* _INTERNAL_MESSAGE_HEADER has 3 parameters
* {SENDERNAME} is string
* {SENDEREMAIL} is string
* {DATETIME} is datetime
*/
function externalMessageForwardHeader($senderId, $sentTime)
{
    //get header string defined by language files
    $headerString = _EXTERNAL_MESSAGE_HEADER . "<br>";

    //set replacement arrays
    $replacementSearch = array("{SENDERINFO}", "{DATETIME}");
    $replacementReplace = array($senderId, $sentTime);

    //start return string
    $returnString = _FORWARDED_MESSAGE . "----------<br>";

    //make replacements
    $returnString .= str_replace($replacementSearch, $replacementReplace, $headerString);

    //return it
    return $returnString;
}

/* function */
function fnFileType($filename)
{

    $filename = explode(".", $filename);
    switch ($filename[sizeof($filename) - 1]) {
        case 'png':
        case 'jpg':
        case 'jpeg':
        case 'gif':
            return "image";
            break;

        case 'doc':
        case 'docx':
            return "word";
            break;

        case 'xls':
        case 'xlsx':
            return "excel";
            break;

        case 'ppt':
        case 'pptx':
        case 'pps':
            return "powerpoint";
            break;

        case 'mpg':
        case 'mpeg':
        case 'mp4':
        case 'ogg':
            return "video";
            break;

        case 'mp3':
        case 'wav':
            return "audio";
            break;

        case 'pdf':
            return "pdf";
            break;

        case 'zip':
        case 'rar':
            return "zip";
            break;

        case 'txt':
            return "text";
            break;

        default:
            return "file";
            break;

    }

    return "";
}

/* function */
function reverse_strrchr($haystack, $needle)
{
    $pos = strrpos($haystack, $needle);
    if ($pos === false) {
        return $haystack;
    }
    return substr($haystack, 0, $pos + 1);
}

/* function */
function TinyThumbNailImage($foto)
{
    $FotoDizi = explode("/", $foto);
    return reverse_strrchr($foto, '/') . "mcith/mcith_" . $FotoDizi[sizeof($FotoDizi) - 1];
}

/* function */
function TinyFixFileName($filename)
{
    return str_replace("../../", "../", $filename);
}

/* function */
function YoneticiKullaniciTuru($userId)
{
    global $dbi;

    $dbi->where("aid", $userId);
    $userInfo = $dbi->getOne(_USERS_, "userType");

    return $userInfo["userType"];
}

/* function */
function TeacherDashboardSchedule($dersTuruKodu, $sinifKodu = "", $dersBransKodu = "", $ogrenciKodu = "")
{
    $ders = "";

    switch ($dersTuruKodu) {
        case 1:  //ders
            $ders = SinifAdi($sinifKodu);
            break;

        case 2: //ek ders
            $ders = ScheduleLabelType(2);
            $ders .= ($sinifKodu != "") ? " [" . SinifAdi($sinifKodu) . "]" : "";
            $ders .= ($dersBransKodu == "") ? "" : " (" . DersBransKisaAdi($dersBransKodu) . ")";
            break;

        case 3: //sinif etutu
            $ders = ScheduleLabelType(3);
            $ders .= ($sinifKodu != "") ? " [" . SinifAdi($sinifKodu) . "]" : "";
            break;

        case 4: //bireysel etut
            $ders = ScheduleLabelType(4);
            break;

        case 5: //grup etutu
            $ders = ScheduleLabelType(5);
            break;

        case 6:    //soru cozumu
            $ders = ScheduleLabelType(6);
            break;

        case 7:    //sinav
            $ders = ScheduleLabelType(7);
            break;

        case 8:    //izin
            $ders = ScheduleLabelType(8);
            break;

        case 9:    //veli gorusme
            $ders = ScheduleLabelType(9);
            break;

        case 10: //nöbet
            $ders = ScheduleLabelType(10);
            break;
    }

    return $ders;
}

/* function */
function StudentDashboardSchedule($dersTuruKodu, $sinifKodu, $dersBransKodu = "", $ogrenciKodu = "")
{
    $ders = "";

    switch ($dersTuruKodu) {
        case 1:  //ders
            $ders = SinifAdi($sinifKodu);
            break;

        case 2: //ek ders
            $ders = ScheduleLabelType(2);
            $ders .= ($sinifKodu != "") ? SinifAdi($sinifKodu) : "";
            $ders .= ($dersBransKodu == "") ? "" : " (" . DersBransKisaAdi($dersBransKodu) . ")";
            break;

        case 3: //sinif etutu
            $ders = ScheduleLabelType(3);
            $ders .= ($sinifKodu != "") ? SinifAdi($sinifKodu) : "";
            break;

        case 4: //bireysel etut
            $ders = ScheduleLabelType(4);
            break;

        case 5: //grup etutu
            $ders = ScheduleLabelType(5);
            break;

        case 6:    //soru cozumu
            $ders = ScheduleLabelType(6);
            break;

        case 7:    //sinav
            $ders = ScheduleLabelType(7);
            break;

        case 8:    //izin
            $ders = ScheduleLabelType(8);
            break;

        case 9:    //veli gorusme
            $ders = ScheduleLabelType(9);
            break;

        case 10: //nöbet
            $ders = ScheduleLabelType(10);
            break;
    }

    return $ders;
}

/* function */
function KullaniciTuru($typeId)
{
    global $dbi;

    $userType = $dbi->where("typeID", $typeId)->getValue(_USER_TYPES_, "userType");

    return translateWord($userType);
}

/* function */
function SchoolCompany($schoolId)
{
    global $dbi;

    $company = $dbi->where("subeID", $schoolId)->getValue(_SUBELER_, "kurucu");
    return $company;
}

/* function */
function BranchName($schoolId, $headquarters = false, $yCampusId = 0)
{
    global $dbi, $configuration, $globalZone, $ySubeKodu, $yCampusID;

    if (is_null($schoolId) || $schoolId == "0") {
        if ($headquarters) {
            return _GENEL_MUDURLUK;
        } else {
            if ($globalZone == "headquarters") {
                return _GENEL_MUDURLUK;
            } else if ($globalZone == "campus") {
                return CampusName($schoolId);
            } else {
                return $configuration["sitename"];
            }
        }
    } else {
        return $dbi->where("subeID", $schoolId)->getValue(_SUBELER_, "subeAdi");
    }
}

/* function */
function CampusName($campusId = "")
{
    global $dbi, $ySubeKodu;

    $campusTitle = $dbi->where("subeID", $campusId)->getValue(_SUBELER_, "subeAdi");
    //if campusId is empty then take ySubeKodu as campusId
    // $campusId = empty($campusId) ? $ySubeKodu : $campusId;

    //get campusId integer value as it comes with a prefix c
    // $cid = isCampus($campusId);

    //then get campus title from either cid or campusId which are the same at the end
    // if ($cid) $campusTitle = $dbi->where("Id", $cid)->getValue(_CAMPUSES_, "campusTitle");
    // else $campusTitle = $dbi->where("Id", $campusId)->getValue(_CAMPUSES_, "campusTitle");

    return $campusTitle;
}

function BranchMenuName($schoolId, $headquarters = true)
{
    global $dbi, $configuration;
    if (is_null($schoolId) || $schoolId == "0") {
        if ($headquarters) {
            return _GENEL_MUDURLUK;
        } else {
            return $configuration["sitename"];
        }
    } else {
        $row = $dbi->where("subeID", $schoolId)->getOne(_SUBELER_, "menuSubeAdi");
        return $row["menuSubeAdi"];
    }
}

/* function */
function BranchContactInfo($schoolId)
{
    global $dbi, $configuration;

    if (is_null($schoolId) || $schoolId == "0") {
        $cityName = DistrictName($configuration["cityId"]);
        $stateName = ProvinceName($configuration["stateId"]);

        $returnHtmlPart1 = $configuration["address"];
        if (!empty($cityName)) $returnHtmlPart1 .= " " . $cityName;
        if (!empty($stateName)) $returnHtmlPart1 .= " " . $stateName;

        if (!empty($configuration["website"])) $returnHtmlPart2 = $configuration["website"];
        if (!empty($configuration["email"])) $returnHtmlPart2 .= empty($returnHtmlPart2) ? $configuration["email"] : " | " . $configuration["email"];
        if (!empty($configuration["phone"])) $returnHtmlPart2 .= empty($returnHtmlPart2) ? $configuration["phone"] : " | " . $configuration["phone"];

        if (!empty($returnHtmlPart1) and !empty($returnHtmlPart2)) return $returnHtmlPart1 . "<br>" . $returnHtmlPart2;
        else if (!empty($returnHtmlPart1)) return $returnHtmlPart1;
        else return $returnHtmlPart2;
    } else {
        $school = $dbi->where("subeID", $schoolId)->getOne(_SUBELER_);
        $cityName = DistrictName($school["cityID"]);
        $stateName = ProvinceName($school["stateID"]);

        $returnHtmlPart1 = $school["adres"];
        if (!empty($cityName)) $returnHtmlPart1 .= " " . $cityName;
        if (!empty($stateName)) $returnHtmlPart1 .= " " . $stateName;

        if (!empty($school["ePosta"])) $returnHtmlPart2 = $school["ePosta"];
        if (!empty($school["telefon1"])) $returnHtmlPart2 .= empty($returnHtmlPart2) ? $school["telefon1"] : " | " . $school["telefon1"];

        if (!empty($returnHtmlPart1) and !empty($returnHtmlPart2)) return $returnHtmlPart1 . "<br>" . $returnHtmlPart2;
        else if (!empty($returnHtmlPart1)) return $returnHtmlPart1;
        else return $returnHtmlPart2;
    }
}

/* function */
function BranchIDofStudent($stdID)
{
    global $dbi;

    $studentInfo = $dbi->where("ogrID", $stdID)->getOne(_OGRENCILER_, "SubeKodu");

    return $studentInfo["SubeKodu"];
}

/* function */
function isOgretmen($yonID)
{
    global $db, $prefix;
    $row = $db->sql_fetchrow($db->sql_query("SELECT userType FROM " . _USERS_ . " WHERE aid='" . $yonID . "'"));
    if ($row["userType"] == 6 or $row["userType"] == 7) return 1;
    else return 0;
}

/* function */
function isCampus($schoolId)
{
    global $dbi;

    $stype = $dbi->where("subeID", $schoolId)->getValue(_SUBELER_, "stype");
    if ($stype == "campus") return true;
    else return false;
}

/* function */
function isCampusUser($userId)
{
    global $dbi;

    $dbi->join(_SUBELER_ . " s", "s.subeID=u.ySubeKodu", "LEFT");
    $dbi->where("(u.id=? OR u.aid=?)", array($userId, $userId));
    $stype = $dbi->getValue(_USERS_ . " u", "s.stype");

    if ($stype == "campus") return true;
    else return false;
}

/* function */
function OgretmenSiniflari($userId, $usePerID = false, $currentSchoolOnly = false, $sendArray = false)
{
    global $dbi, $ySubeKodu;

    $batches = array();

    //get teacher id
    $teacherId = $usePerID ? $userId : PersonelID($userId);

    //get batches for which the teacher has classes by distinct
    $dbi->join(_CLASS_BATCHES_ . " b", "b.classId=t.classId", "INNER");

    //join batches for sorting
    $dbi->join(_BATCHES_ . " bt", "bt.sinifID=b.batchId", "LEFT");

    //add teacher condition
    $dbi->where("t.teacherId", $teacherId);

    $dbi->where("t.classId", NULL, "IS NOT");
    $dbi->where("t.classId", "", "!=");

    //add current school condition if it is sent
    if ($currentSchoolOnly) $dbi->where("t.schoolId", $ySubeKodu);

    //order batches
    $dbi->orderBy("bt.sinifAdi", "ASC");

    //get batches
    $batches = $dbi->getValue(_CLASS_TEACHERS_ . " t", "DISTINCT(b.batchId)", null);

    if ($sendArray) return $batches;    //if it is asked to be sent as array then send it as an array
    else return implode(",", $batches);    //else send it as comma seperated
}

function SuperAdmin($userId = "")
{
    global $dbi, $aid;

    $userId = empty($userId) ? $aid : $userId;

    return $dbi->where("aid", $userId)->getValue(_USERS_, "radminsuper");
}

/* function */
function Admin($userId = "")
{
    global $dbi, $aid;

    $userId = empty($userId) ? $aid : $userId;

    $userType = $dbi->where("aid", $userId)->getValue(_USERS_, "userType");

    //if system admin then return true
    return ($userType == '1') ? true : false;
}

/* function */
function SchoolTechAdmin($userId = "")
{
    global $dbi, $aid;

    $userId = empty($userId) ? $aid : $userId;

    $userType = $dbi->where("aid", $userId)->getValue(_USERS_, "userType");

    //if system admin or high level manager or technical manager return true
    return (in_array($userType, array('1', '2', '3', '18')) || SuperAdmin($userId)) ? true : false;
}

/* function */
function SchoolFinanceAdmin($userId = "")
{
    global $dbi, $aid;

    $userId = empty($userId) ? $aid : $userId;

    $userType = $dbi->where("aid", $userId)->getValue(_USERS_, "userType");

    //if system admin or financial manager return true
    return ($userType == '1' or $userType == '10') ? true : false;
}

/* function */
function SchoolPrincipal($schoolId = "", $attr = "*", $checkSchoolInfo = false)
{
    global $dbi, $ySubeKodu;

    $schoolId = empty($schoolId) ? $ySubeKodu : $schoolId;

    if ($checkSchoolInfo) {
        //get from school info
        $principal = $dbi->where("subeID", $schoolId)->getValue(_SUBELER_, "mudur");

        if (!empty($principal)) return $principal;
    }

    //get from personnel if school info empty
    $row = $dbi->where("SubeKodu", $schoolId)->where("cat_code", "7")->where("aktif", "1")->getOne(_PERSONEL_);

    //return as an array
    if ($attr == "*") return $row;
    else return $row[$attr];
}

/* function */
function SchoolVicePrincipals($schoolId = "")
{
    global $dbi, $ySubeKodu;

    $schoolId = empty($schoolId) ? $ySubeKodu : $schoolId;

    //get principal
    $row = $dbi->where("SubeKodu", $schoolId)->where("cat_code", "8")->where("aktif", "1")->get(_PERSONEL_);

    //return as an array
    return $row;
}

/* function */
function SchoolAdmissioners($schoolId = "", $attr = "*")
{
    global $dbi, $ySubeKodu;

    $schoolId = empty($schoolId) ? $ySubeKodu : $schoolId;

    //get principal
    $row = $dbi->where("ySubeKodu", $schoolId)->where("userType", array("3, 4, 12"), "IN")->where("active", "1")->get(_USERS_, null, $attr);

    //return
    return $row;
}

/* function */
function AdminPermissions($customOp = "")
{
    global $db, $aid, $op, $globalUserType;

    //if the user is in one of the areas of teacher, parent and student then send ok
    if ($globalUserType == "teacher" or $globalUserType == "parent" or $globalUserType == "student") {
        return true;
    } else if (SuperAdmin($aid) or Admin($aid) or SchoolTechAdmin($aid)) //if super admin or admin or school it admin return true
    {
        return true;
    } else //if not admin check the permissions
    {
        $permission = ($customOp == "") ? $op : $customOp;
        $userTypeID = YoneticiKullaniciTuru($aid);

        //check user type permissions
        $q = $db->sql_query("SELECT `id` FROM " . _USER_TYPE_PERMISSIONS_ . " WHERE `actionCode`='" . $permission . "' AND `userTypeCode`='" . $userTypeID . "'");
        if ($db->sql_numrows($q) > 0) {
            return true;
        } else //check user permissions
        {
            $q = $db->sql_query("SELECT `id` FROM " . _USER_PERMISSIONS_ . " WHERE `actionCode`='" . $permission . "' AND `userCode`='" . $aid . "'");
            if ($db->sql_numrows($q) > 0) return true;
            else return false;
        }
        return false;
    }
}

/* function */
function SchoolTechAdminEMails($isArray = true, $userCodes = false)
{
    global $db, $ySubeKodu, $adminmail;

    if ($ySubeKodu == "0") {
        if ($isArray) return array($adminmail);
        else return $adminmail;
    } else {
        //get tech admins
        //18 is IT manager user type
        if ($userCodes) $schoolTechs = $db->sql_fetchrow($db->sql_query("SELECT GROUP_CONCAT(DISTINCT `aid` SEPARATOR ',') AS `schoolTechEmails` FROM " . _USERS_ . " WHERE `ySubeKodu`='" . $ySubeKodu . "' AND `userType`='18' AND `active`='1'"));
        else $schoolTechs = $db->sql_fetchrow($db->sql_query("SELECT GROUP_CONCAT(DISTINCT `email` SEPARATOR ',') AS `schoolTechEmails` FROM " . _USERS_ . " WHERE `ySubeKodu`='" . $ySubeKodu . "' AND `userType`='18' AND `active`='1'"));


        //school tech admins as array
        if (is_null($schoolTechs["schoolTechEmails"])) $techAdmins = array();
        else $techAdmins = explode(",", $schoolTechs["schoolTechEmails"]);

        //add site admin mail
        if ($adminmail != "") $techAdmins[] = $adminmail;

        //if asked to be not array then send as comma seperated string
        //else return tech admins as array
        if (!$isArray) {
            $techAdminsStr = implode(",", $techAdmins);
            return $techAdminsStr;
        } else {
            return $techAdmins;
        }
    }
}

/* function */
function CustomerSchoolTitle($shortTitle)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `schoolTitle` FROM " . _CUSTOMER_SCHOOLS_ . " WHERE `schoolShortTitle`='" . $shortTitle . "'"));
    return $row["schoolTitle"];
}

/* function */
function YoneticiAdi($userId)
{
    global $dbi;

    $userInfo = $dbi->where("aid", $userId)->getOne(_USERS_, "CONCAT(COALESCE(name, ''), ' ', COALESCE(lastName, '')) AS AdiSoyadi");

    return $userInfo["AdiSoyadi"];
}

/* function */
function YoneticiAdiWDB($yonID, $yonDB)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT u.`name`, t.`userType` FROM " . $yonDB . "_main.school_yoneticiler u LEFT JOIN " . _USER_TYPES_ . " t ON u.`userType`=t.`typeID` WHERE `aid`='" . $yonID . "'"));
    return $row["name"] . " [" . translateWord($row["userType"]) . "]";
}

/* function */
function SchoolBusID2Name($id)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `servisAdi` FROM " . _SERVISLER_ . " WHERE `Id`='" . $id . "'"));
    return $row["servisAdi"];
}

/* function */
function fnSchoolBusStaffId2Info($Id)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `foto`, `adiSoyadi`, `ceptel` FROM " . _SCHOOL_BUS_STAFF_ . " WHERE `Id`='" . $Id . "'"));
    return "<div class='thumbnail' style='margin-bottom: 0'><img src='" . $row["foto"] . "' style='max-width: 120px; max-height: 120px'><div class='caption text-center' style='padding:0'><h5>" . $row["adiSoyadi"] . "</h5><p style='margin: 0'>" . $row["ceptel"] . "</p></div></div>";
}


/* function */
function UserEMail($userAid)
{
    global $dbi;

    $dbi->where("aid", $userAid);
    $userInfo = $dbi->getOne(_USERS_, "email");

    return $userInfo["email"];
}

/* function */
function UserPicture($userId)
{
    global $dbi;

    $uemail = $dbi->where("aid", $userId)->getValue(_USERS_, "picture");

    return $uemail;
}

/* function */
function fnUserEMail2UserID($email)
{
    global $dbi;

    $dbi->where("email", $email);
    $userId = $dbi->getValue(_USERS_, "aid");

    return $userId;
}

/* function */
function KullaniciAdi($id)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT CONCAT(`name`, ' ', `lastName`) AS username FROM " . _USERS_ . " WHERE `id`='" . $id . "'"));
    return $row["username"];
}

/* function */
function GenelMudurluk($userId)
{
    global $dbi;

    $dbi->join(_SUBELER_ . " s", "s.subeID=u.ySubeKodu", "LEFT");
    $dbi->where("u.aid", $userId);
    $schoolType = $dbi->getValue(_USERS_ . " u", "s.stype");

    return $schoolType === 'headquarters';
}

/* function */
function SubeAdi($subeKodu, $format = 0)
{
    global $dbi;

    $dbi->where("subeID", $subeKodu);
    return $dbi->getValue(_SUBELER_, "subeAdi");
}

/* function */
function headquartersId()
{
    global $dbi;

    $dbi->where("stype", "headquarters");
    return $dbi->getValue(_SUBELER_, "subeID");
}

/* function */
function headquartersInfo()
{
    global $dbi;

    $dbi->where("stype", "headquarters");
    return $dbi->getOne(_SUBELER_);
}

/* function */
function userSchoolId($aid)
{
    global $dbi;

    $dbi->where("aid", $aid);
    return $dbi->getValue(_USERS_, "ySubeKodu");
}

function userZone($userType, $schoolId, $schoolType = "school")
{
    if ($userType == "6" || $userType == "7") {
        return "teacher";
    } else if ($userType == "9") {
        return "parent";
    } else if ($userType == "8" || $userType == "14") {
        return "student";
    } else {
        return $schoolType;
    }
}

/* function */
//function to show school favicon
function scSchoolFavicon()
{
    global $dbi, $SmartClassFavicon, $site_favicon, $ySubeKodu;

    if (empty($ySubeKodu)) $ySubeKodu = headquartersId();

    // Get favicon from school settings
    $dbi->where("subeID", $ySubeKodu);
    $favicon = $dbi->getValue(_SUBELER_, "favicon");

    // If empty then return SmartClass favicon
    if (empty($favicon)) return $SmartClassFavicon;

    // Check if favicon exists
    $fileHeaders = @get_headers($favicon);

    //if exists then send school favicon file
    //if not send smartclass default favicon
    if ($fileHeaders[0] != 'HTTP/1.1 404 Not Found') {
        return $favicon;
    } else {
        return $SmartClassFavicon;
    }
}

/* function */
function scSchoolLogo($logoSize = "1", $schoolId = "0")
{
    global $dbi, $SmartClassLogo, $site_logo, $ySubeKodu;

    if (empty($schoolId)) $schoolId = $ySubeKodu;
    if (empty($ySubeKodu)) $ySubeKodu = headquartersId();

    $dbi->where("subeID", $schoolId);
    $logos = $dbi->getOne(_SUBELER_, "kucukLogo, normalLogo, buyukLogo");

    //if we have the logo requested, return it
    if ($logoSize == "1") $logoFile = $logos["kucukLogo"];
    else if ($logoSize == "2") $logoFile = $logos["normalLogo"];
    else if ($logoSize == "3") $logoFile = $logos["buyukLogo"];
    else $logoFile = $logos["normalLogo"];

    $exist = checkURL($logoFile);

    // If exists then send school logo file
    // If not send smartclass default logo
    if ($exist) {
        return $logoFile;
    } else {
        return $SmartClassLogo;
    }
}

/* function */
function ProvinceName($stateCode)
{
    global $dbi;

    return $dbi->where("stateID", $stateCode)->getValue(_ILLER_, "stateName");
}

/* function */
function ProvinceID2Name($stateCode)
{
    global $dbi;

    return $dbi->where("stateID", $stateCode)->getValue(_ILLER_, "stateName");
}

/* function */
function DistrictName($cityID)
{
    global $dbi;

    return $dbi->where("cityID", $cityID)->getValue(_ILCELER_, "cityName");
}

/* function */
function fnDiscountId2DiscountTitle($id)
{
    global $db;
    $discount = $db->sql_fetchrow($db->sql_query("SELECT `indTuruAdi` FROM " . _INDIRIM_TURLERI_ . " WHERE `indTuruID`='" . $id . "'"));

    return $discount["indTuruAdi"];
}

/* function */
function DiscountTitle($type)
{
    switch ($type) {
        case 'egitim':
            return _EGITIM_UCRETI;
            break;

        case 'yemek':
            return _YEMEK_UCRETI;
            break;

        case 'kahvalti':
            return _KAHVALTI_UCRETI;
            break;

        case 'kirtasiye':
            return _KIRTASIYE_UCRETI;
            break;

        case 'dergi':
            return _DERGI_UCRETI;
            break;

        case 'yayin':
            return _YAYIN_UCRETI;
            break;

        case 'kiyafet':
            return _KIYAFET_UCRETI;
            break;

        case 'destek':
            return _DESTEK_HIZMETLERI_UCRETI;
            break;

        case 'servis':
            return _BUS_FEE;
            break;

        case 'toplam':
            return _TOPLAM_UCRET;
            break;
    }
}

/* function */
function ReverseDiscountTitle($type)
{
    switch ($type) {
        case '_EGITIM_UCRETI':
            return 'egitim';
            break;

        case '_YEMEK_UCRETI':
            return 'yemek';
            break;

        case '_KAHVALTI_UCRETI':
            return 'kahvalti';
            break;

        case '_KIRTASIYE_UCRETI':
            return 'kirtasiye';
            break;

        case '_DERGI_UCRETI':
            return 'dergi';
            break;

        case '_YAYIN_UCRETI':
            return 'yayin';
            break;

        case '_KIYAFET_UCRETI':
            return 'kiyafet';
            break;

        case '_DESTEK_HIZMETLERI_UCRETI':
            return 'destek';
            break;

        case '_BUS_FEE':
            return 'servis';
            break;

        case '_TOPLAM_UCRET':
            return 'toplam';
            break;
    }
}

/* function */
function KullaniciGorebilirMi($gorebilenler, $yonUserType)
{
    if ($yonUserType == 1) return 1;
    $gorebilenler = explode(",", $gorebilenler);
    for ($i = 0; $i < sizeof($gorebilenler); $i++) {
        if ($gorebilenler[$i] == $yonUserType) return 1;
    }
    return 0;
}

/* function */
function KimlerGorebilir($kim)
{
    global $db, $prefix;
    $kimler = "";
    $types = $db->sql_query("SELECT userType FROM " . $prefix . "_user_types WHERE typeID IN (" . $kim . ")");
    while ($row = $db->sql_fetchrow($types)) {
        $kimler .= ($kimler == "") ? $row["userType"] : ", " . $row["userType"];
    }
    return $kimler;
}

/* function */
function localCountry($local)
{
    global $db;
    $country = $db->sql_fetchrow($db->sql_query("SELECT `country` FROM " . _COUNTRIES_ . " WHERE `isoCode`='" . strtoupper($local) . "'"));
    return $country["country"];
}

/* function */
function GetMonthName($m, $full = false)
{
    if ($m == "1" || $m == "01") if ($full) return _JANUARY; else return _SHORT_JANUARY;
    if ($m == "2" || $m == "02") if ($full) return _FEBRUARY; else return _SHORT_FEBRUARY;
    if ($m == "3" || $m == "03") if ($full) return _MARCH; else return _SHORT_MARCH;
    if ($m == "4" || $m == "04") if ($full) return _APRIL; else return _SHORT_APRIL;
    if ($m == "5" || $m == "05") if ($full) return _MAY; else return _SHORT_MAY;
    if ($m == "6" || $m == "06") if ($full) return _JUNE; else return _SHORT_JUNE;
    if ($m == "7" || $m == "07") if ($full) return _JULY; else return _SHORT_JULY;
    if ($m == "8" || $m == "08") if ($full) return _AUGUST; else return _SHORT_AUGUST;
    if ($m == "9" || $m == "09") if ($full) return _SEPTEMBER; else return _SHORT_SEPTEMBER;
    if ($m == "10") if ($full) return _OCTOBER; else return _SHORT_OCTOBER;
    if ($m == "11") if ($full) return _NOVEMBER; else return _SHORT_NOVEMBER;
    if ($m == "12") if ($full) return _DECEMBER; else return _SHORT_DECEMBER;
}

/* function */
/*
 * tr locale: define("_DATE_FORMAT4_", "dd M yyyy");
 * us locale: define("_DATE_FORMAT4_", "M d, yyyy");
 * my locale: define("_DATE_FORMAT4_", "dd M yyyy");
 * th locale: define("_DATE_FORMAT4_", "dd M yyyy");
 *
 * samples: if the locale is us
 *		FormatDate2Local("2018-09-26") = September 26, 2018
 *		FormatDate2Local("2018-09-26 15:38:45") = September 26, 2018
 *		FormatDate2Local("2018-09-26 15:38:45", $full = true) = September 26, 2018 15:38:45
 *		FormatDate2Local("2018-09-26 15:38:45", $full = true, $fullWOSecs = true) = September 26, 2018 3:38 PM
 *		FormatDate2Local("2018-09-26 15:38:45", $full = true, $fullWOSecs = true, $time = true) = 3:38 PM
 *		FormatDate2Local("2018-09-26 15:38:45", $full = true, $fullWOSecs = true, $time = true, $seconds = true) = 15:38:45
 */

function FormatDate2Local($date, $full = 0, $fullWOSecs = false, $time = false, $seconds = false)
{
    if (!empty($date)) {
        //if it is just time then send it
        if ($full and $time) {
            if ($seconds) return ltrim(substr($date, -8), "0");
            else return FormatTime2Local(substr($date, -8, 5));
        }

        $newDateFormat = _DATE_FORMAT4_;
        $pDate = (strlen($date) > 10) ? substr($date, 0, 10) : $date; //if length is greater than 10 then it is date and time else just date

        //explode date by - as the db format is YYYY-MM-DD
        $dateArray = explode("-", $pDate);

        //conversions
        if ($newDateFormat == "dd M yyyy") {
            //set date
            $newDate = $dateArray[2] . " " . GetMonthName($dateArray[1], true) . " " . $dateArray[0];
        } else if ($newDateFormat == "M d, yyyy") {
            //set date
            $newDate = GetMonthName($dateArray[1], true) . " " . $dateArray[2] . ", " . $dateArray[0];
        } else {
            //set date
            $newDate = $dateArray[2] . " " . GetMonthName($dateArray[1], true) . " " . $dateArray[0];
        }

        if (strlen($date) > 10 and $full) {
            if ($fullWOSecs) $newDate .= " " . FormatTime2Local(substr($date, -8, 5));
            else $newDate .= " " . ltrim(substr($date, -8), "0");
        }

        return $newDate;
    } else {
        return "";
    }
}

/* function */
/*
 * tr locale: define("_DATE_FORMAT3_", "dd.mm.yyyy");
 * us locale: define("_DATE_FORMAT3_", "m/d/yyyy");
 * my locale: define("_DATE_FORMAT3_", "d/m/yyyy");
 * th locale: define("_DATE_FORMAT3_", "d/m/yyyy");
 *
 * samples: if the locale is us
 *		FormatDateNumeric2Local("2018-09-26") = 9/26/2018
 *		FormatDateNumeric2Local("2018-09-26 15:38:45") = 9/26/2018
 *		FormatDateNumeric2Local("2018-09-26 15:38:45", $full = true) = 9/26/2018 15:38:45
 *		FormatDateNumeric2Local("2018-09-26 15:38:45", $full = true, $fullWOSecs = true) = 9/26/2018 3:38 PM
 *		FormatDateNumeric2Local("2018-09-26 15:38:45", $full = true, $fullWOSecs = true, $time = true) = 3:38 PM
 *		FormatDateNumeric2Local("2018-09-26 15:38:45", $full = true, $fullWOSecs = true, $time = true, $seconds = true) = 15:38:45
 */

function FormatDateNumeric2Local($date, $full = false, $fullWOSecs = false, $time = false, $seconds = false)
{
    if (!empty($date)) {
        //if it is just time then send it
        if ($full and $time) {
            if ($seconds) return ltrim(substr($date, -8), "0");
            else return FormatTime2Local(substr($date, -8, 5));
        }

        $newDateFormat = _DATE_FORMAT3_;
        $pDate = (strlen($date) > 10) ? substr($date, 0, 10) : $date; //if length is greater than 10 then it is date and time else just date

        //explode date by - as the db format is YYYY-MM-DD
        $dateArray = explode("-", $pDate);

        //conversions
        if ($newDateFormat == "dd.mm.yyyy") {
            //set date
            $newDate = $dateArray[2] . "." . $dateArray[1] . "." . $dateArray[0];
        } else if ($newDateFormat == "m/d/yyyy") {
            //set date
            $newDate = ltrim($dateArray[1], "0") . "/" . ltrim($dateArray[2], "0") . "/" . $dateArray[0];
        } else if ($newDateFormat == "dd/mm/yyyy") {
            //set date
            $newDate = $dateArray[2] . "/" . $dateArray[1] . "/" . $dateArray[0];
        } else if ($newDateFormat == "yyyy-mm-dd") {
            //set date
            $newDate = $dateArray[0] . "-" . $dateArray[1] . "-" . $dateArray[2];
        } else if ($newDateFormat == "dd-mm-yyyy") {
            //set date
            $newDate = $dateArray[2] . "-" . $dateArray[1] . "-" . $dateArray[0];
        } else {
            //set date
            $newDate = $dateArray[2] . "." . $dateArray[1] . "." . $dateArray[0];
        }

        if (strlen($date) > 10 and $full) {
            if ($fullWOSecs) $newDate .= " " . FormatTime2Local(substr($date, -8, 5));
            else $newDate .= " " . ltrim(substr($date, -8), "0");
        }

        return $newDate;
    } else {
        return "";
    }
}

/* function */
function FormatTime2Local($time)
{
    $countryMomentCode = _COUNTRY_CODE_MOMENT_;

    switch ($countryMomentCode) {
        case "ms":

            $timeParts = explode(":", $time);

            if (sizeof($timeParts) == 2) return $timeParts[0] . "." . $timeParts[1];
            else return $timeParts[0] . "." . $timeParts[1] . "." . $timeParts[2];

            break;
            
        case "en-in":

            $timeParts = explode(":", $time);

            if (sizeof($timeParts) == 2) return $timeParts[0] . ":" . $timeParts[1];
            else return $timeParts[0] . ":" . $timeParts[1] . ":" . $timeParts[2];

            break;

        case "th":

            $timeParts = explode(":", $time);

            if (sizeof($timeParts) == 2) return ltrim($timeParts[0], 0) . "." . $timeParts[1];
            else return ltrim($timeParts[0], 0) . "." . $timeParts[1] . "." . $timeParts[2];

            break;

        default:

            if (_DATE_SHOW_AM_PM_) {
                $timeParts = explode(":", $time);

                if (intval($timeParts[0]) == 12) return $timeParts[0] . ":" . $timeParts[1] . " PM";
                elseif (intval($timeParts[0]) > 12) return intval($timeParts[0] - 12) . ":" . $timeParts[1] . " PM";
                elseif (intval($timeParts[0]) == 0) return "12:" . $timeParts[1] . " AM";
                else return ltrim($time, "0") . " AM";
            } else {
                return $time;
            }

            break;
    }
}

/* function */
/*
 * new version of previous datetime and time conversion functions
 * database format is YYYY-MM-DD HH:MM:SS
 */
function FormatDateTime2DbStandard($datetime, $momentLocale = _COUNTRY_CODE_MOMENT_)
{
    if (empty($datetime)) return "";

    switch ($momentLocale) {
        case "en":
        case "en-in":
            //united states || india
            //long format is MM/DD/YYYY HH:MM AM (or MM/DD/YYYY HH:MM PM)
            //date format is MM/DD/YYYY
            //time format is HH:MM AM (or HH:MM PM)

            //first get parts by spaces
            $parts = explode(" ", $datetime);

            if (sizeof($parts) == "1") //date format
            {
                //get sub parts
                $subparts = explode("/", $parts[0]);

                //return YYYY-MM-DD format
                return $subparts[2] . "-" . $subparts[0] . "-" . $subparts[1];
            } else if (sizeof($parts) == "2") //time format
            {
                if ($parts[1] == "AM") {
                    //return HH:MM format
                    return strlen($parts[0]) == 5 ? $parts[0] : "0" . $parts[0];
                } else //which is PM
                {
                    //get sub parts
                    $subparts = explode(":", $parts[0]);

                    //if HH is less then 12 then add 12 to make it db compatible
                    $subparts[0] = (intval($subparts[0]) < 12) ? intval($subparts[0]) + 12 : $subparts[0];

                    //return HH:MM format
                    return $subparts[0] . ":" . $subparts[1];
                }
            } else //long format
            {
                //get sub parts of date part
                $dateSubparts = explode("/", $parts[0]);

                if ($parts[2] == "AM") {
                    //return YYYY-MM-DD HH:MM:00 format
                    return $dateSubparts[2] . "-" . $dateSubparts[0] . "-" . $dateSubparts[1] . " " . $parts[1] . ":00";
                } else //which is PM
                {
                    //get sub parts
                    $timeSubparts = explode(":", $parts[1]);

                    //if HH is less then 12 then add 12 to make it db compatible
                    $timeSubparts[0] = (intval($timeSubparts[0]) < 12) ? intval($timeSubparts[0]) + 12 : $timeSubparts[0];

                    //return HH:MM format
                    return $dateSubparts[2] . "-" . $dateSubparts[0] . "-" . $dateSubparts[1] . " " . $timeSubparts[0] . ":" . $timeSubparts[1] . ":00";
                }
            }
            break;

        case "tr":
            //turkey
            //long format is DD.MM.YYYY HH:MM
            //date format is DD.MM.YYYY
            //time format is HH:MM

            //first get parts by spaces
            $parts = explode(" ", $datetime);

            if (sizeof($parts) == "1") //date or time format
            {
                //get length of the format
                $length = strlen($parts[0]);

                if ($length == "5") //time format
                {
                    //return HH:MM format
                    return $parts[0];
                } else //date format
                {
                    //get sub parts
                    $subparts = explode(".", $parts[0]);

                    //return YYYY-MM-DD format
                    return $subparts[2] . "-" . $subparts[1] . "-" . $subparts[0];
                }
            } else //long format
            {
                //get sub parts of date part
                $dateSubparts = explode(".", $parts[0]);

                //return YYYY-MM-DD HH:MM:00 format
                return $dateSubparts[2] . "-" . $dateSubparts[1] . "-" . $dateSubparts[0] . " " . $parts[1] . ":00";
            }

            break;

        case "ms":    //malaysia
            //long format is DD/MM/YYYY HH.MM
            //date format is DD/MM/YYYY
            //time format is HH.MM

            //first get parts by spaces
            $parts = explode(" ", $datetime);

            if (sizeof($parts) == "1") //date or time short format
            {
                //get length of the format
                $length = strlen($parts[0]);

                if ($length == "5") //time format
                {
                    //return HH:MM format
                    return str_replace(".", ":", $parts[0]);
                } else //date format
                {
                    //get sub parts
                    $subparts = explode("/", $parts[0]);

                    //return YYYY-MM-DD format
                    return $subparts[2] . "-" . $subparts[1] . "-" . $subparts[0];
                }
            } else //long format
            {
                //get sub parts of date part
                $dateSubparts = explode("/", $parts[0]);

                //return YYYY-MM-DD HH:MM:00 format
                return $dateSubparts[2] . "-" . $dateSubparts[1] . "-" . $dateSubparts[0] . " " . str_replace(".", ":", $parts[0]) . ":00";
            }

            break;

        case "th":    //thailand
            //long format is DD/MM/YYYY H:MM
            //date format is DD/MM/YYYY
            //time format is H:MM

            //first get parts by spaces
            $parts = explode(" ", $datetime);

            if (sizeof($parts) == 1) //date or time short format
            {
                //get length of the format
                $length = strlen($parts[0]);

                if ($length < 6) //time format
                {
                    //get sub parts
                    $subparts = explode(":", $parts[0]);

                    //return HH:MM:00 format
                    $subparts[0] = (strlen($subparts[0]) == 1) ? "0" . $subparts[0] : $subparts[0];
                    return $subparts[0] . ":" . $subparts[1] . ":00";
                } else //date format
                {
                    //get sub parts
                    $subparts = explode("/", $parts[0]);

                    //return YYYY-MM-DD format
                    return $subparts[2] . "-" . $subparts[1] . "-" . $subparts[0];
                }
            } else //long format
            {
                //get sub parts of date part
                $dateSubparts = explode("/", $parts[0]);
                $timeSubparts = explode(":", $parts[1]);
                $timeSubparts[0] = (strlen($timeSubparts[0]) == 1) ? "0" . $timeSubparts[0] : $timeSubparts[0];

                //return YYYY-MM-DD HH:MM:00 format
                return $dateSubparts[2] . "-" . $dateSubparts[1] . "-" . $dateSubparts[0] . " " . $timeSubparts[0] . ":" . $timeSubparts[1] . ":00";
            }
            break;

        case "en-ca": //canada
            //long format is YYYY-MM-DD HH:MM
            //date format is YYYY-MM-DD
            //time format is HH:MM

            //first get parts by spaces
            $parts = explode(" ", $datetime);

            if (sizeof($parts) == "1") //date or time format
            {
                return $parts[0];
            } else //long format
            {
                //return YYYY-MM-DD HH:MM:00 format
                return $parts[0] . " " . $parts[1] . ":00";
            }
            break;

        case "en-ie": //ireland
            //long format is DD-MM-YYYY HH:MM
            //date format is DD-MM-YYYY
            //time format is HH:MM

            //first get parts by spaces
            $parts = explode(" ", $datetime);

            if (sizeof($parts) == "1") //date or time format
            {
                //get length of the format
                $length = strlen($parts[0]);

                if ($length == "5") //time format
                {
                    //return HH:MM format
                    return $parts[0];
                } else //date format
                {
                    //get sub parts
                    $subparts = explode("-", $parts[0]);

                    //return YYYY-MM-DD format
                    return $subparts[2] . "-" . $subparts[1] . "-" . $subparts[0];
                }
            } else //long format
            {
                //get sub parts of date part
                $dateSubparts = explode("-", $parts[0]);

                //return YYYY-MM-DD HH:MM:00 format
                return $dateSubparts[2] . "-" . $dateSubparts[1] . "-" . $dateSubparts[0] . " " . $parts[1] . ":00";
            }

            break;

        default:
            //long format is DD.MM.YYYY HH:MM
            //date format is DD.MM.YYYY
            //time format is HH:MM

            //first get parts by spaces
            $parts = explode(" ", $datetime);

            if (sizeof($parts) == "1") //date or time format
            {
                //get length of the format
                $length = strlen($parts[0]);

                if ($length == "5") //time format
                {
                    //return HH:MM format
                    return $parts[0];
                } else //date format
                {
                    //get sub parts
                    $subparts = explode(".", $parts[0]);

                    //return YYYY-MM-DD format
                    return $subparts[2] . "-" . $subparts[1] . "-" . $subparts[0];
                }
            } else //long format
            {
                //get sub parts of date part
                $dateSubparts = explode(".", $parts[0]);

                //return YYYY-MM-DD HH:MM:00 format
                return $dateSubparts[2] . "-" . $dateSubparts[1] . "-" . $dateSubparts[0] . " " . $parts[1] . ":00";
            }
            break;

    }
}

/* function */
/*
 * converts localestring to php standard yyyy-mm-dd
 * @localeString -> _DATE_FORMAT3_
 */

/* this function is deprecated, use FormatDateTime2DbStandard instead */
function FormatDate2DbStandard($date, $localeString = _DATE_FORMAT3_)
{
    if (empty($date)) return "";

    if (strlen($date) > 10) //long format
    {
        $dateParts = explode(" ", $date);
        $dataX = $dateParts[0];

        if (strlen($dateParts[1]) == 5) $dataY = " " . FormatTime2DbStandard($dateParts[1], 1);
        else $dataY = " " . FormatTime2DbStandard($dateParts[1]);
    } else //short format
    {
        $dataX = $date;
        $dataY = "";
    }

    if ($localeString == "dd.mm.yyyy") {
        $n = explode(".", $dataX);
        return $n[2] . "-" . $n[1] . "-" . $n[0] . $dataY;
    } else if ($localeString == "m/d/yyyy") {
        $n = explode("/", $dataX);
        $n[0] = (strlen($n[0]) == 1) ? "0" . $n[0] : $n[0];
        $n[1] = (strlen($n[1]) == 1) ? "0" . $n[1] : $n[1];
        return $n[2] . "-" . $n[0] . "-" . $n[1] . $dataY;
    } else if ($localeString == "d/m/yyyy") {
        $n = explode("/", $dataX);
        $n[0] = (strlen($n[0]) == 1) ? "0" . $n[0] : $n[0];
        $n[1] = (strlen($n[1]) == 1) ? "0" . $n[1] : $n[1];
        return $n[2] . "-" . $n[1] . "-" . $n[0] . $dataY;
    }
}

/* function */

/* this function is deprecated, use FormatDateTime2DbStandard instead */
function FormatTime2DbStandard($time, $full = false)
{
    if (empty($time)) return "";

    $countryMomentCode = _COUNTRY_CODE_MOMENT_;

    switch ($countryMomentCode) {
        case "ms":

            $timeParts = explode(".", $time);

            if (sizeof($timeParts) == 2) return $timeParts[0] . ":" . $timeParts[1];
            else return $timeParts[0] . ":" . $timeParts[1] . ":" . $timeParts[2];

            break;

        default:

            //fix according to whether it is shown as pm/am or not
            if (_DATE_SHOW_AM_PM_) {
                $timeParts = explode(" ", $time);
                if ($timeParts[1] == "PM") {
                    $timeParts2 = explode(":", $timeParts[0]);
                    $timeParts2[0] = (intval($timeParts2[0]) < 12) ? intval($timeParts2[0]) + 12 : $timeParts2[0];

                    if ($full) return $timeParts2[0] . ":" . $timeParts2[1] . ":00";
                    else return $timeParts2[0] . ":" . $timeParts2[1];
                } else {
                    $timeParts2 = explode(":", $timeParts[0]);
                    $timeParts2[0] = (intval($timeParts2[0]) == 12) ? "00" : $timeParts2[0];

                    if ($full) return $timeParts2[0] . ":" . $timeParts2[1] . ":00";
                    else return $timeParts2[0] . ":" . $timeParts2[1];
                }
            } else {
                if ($full) return $time . ":00";
                else return $time;
            }

            break;
    }
}

/* function */
function GetDayNameFromDate($date)
{
    $day = strftime('%w', strtotime($date));
    return GetDayName($day);
}

/* function */
function langTranslate()
{
    global $dbi, $currentlang, $pageFolder, $personalIdentity;

    //define db translations
    $translations = $dbi->get(_LANGUAGES_, null, array("alan", "english", $currentlang . " as translation"));
    foreach ($translations as $translation) {
        define($translation["alan"], empty($translation["translation"]) ? $translation["english"] : $translation["translation"]);
    }
}

/* function */
function translateWord($word, $lang = "")
{
    global $dbi, $currentlang;

    $lang = empty($lang) ? $currentlang : $lang;

    $translation = $dbi->where("alan", $word)->getOne(_LANGUAGES_, array("alan", "english", $lang . " as translation"));

    return empty($translation["translation"]) ? $translation["english"] : $translation["translation"];
}

/* function */
function transcriptionTurkish2Ottoman($text)
{
    //get rid of html tags first
    $htmlTags = array("<br>", "</br>");
    $text = str_replace($htmlTags, "", $text);

    $text = str_replace("H", "ح", $text);
    $text = str_replace("Z", "ظ", $text);
    $text = str_replace("T", "ط", $text);
    $text = str_replace("İ", "ى", $text);
    $text = str_replace("a  ", "ه ", $text);
    $text = str_replace("i ", "ى ", $text);
    $text = str_replace("ı ", "ى ", $text);
    $text = str_replace(" i", " اي", $text);
    $text = str_replace("e ", "ه ", $text);
    $text = str_replace("S", "ص", $text);
    $text = str_replace("A", "آ", $text);
    $text = str_replace("a", "ا", $text);
    $text = str_replace("w", "ع", $text);
    $text = str_replace(" o", " او", $text);
    $text = str_replace(" ö", " او", $text);
    $text = str_replace(" u", " او", $text);
    $text = str_replace(" ü", " او", $text);
    //$text = str_replace("<", "ه ", $text);

    $text = str_replace("b", "ب", $text);
    $text = str_replace("B", "ب", $text);
    $text = str_replace("p", "پ", $text);
    $text = str_replace("P", "پ", $text);
    $text = str_replace("t", "ت", $text);
    $text = str_replace("T", "ت", $text);
    $text = str_replace("c", "ج", $text);
    $text = str_replace("C", "ج", $text);
    $text = str_replace("ç", "چ", $text);
    $text = str_replace("Ç", "چ", $text);
    $text = str_replace("x", "خ", $text);
    $text = str_replace("X", "خ", $text);
    $text = str_replace("d", "د", $text);
    $text = str_replace("D", "ض", $text);
    $text = str_replace("r", "ر", $text);
    $text = str_replace("R", "ر", $text);
    $text = str_replace("z", "ز", $text);
    $text = str_replace("Z", "ز", $text);
    $text = str_replace("j", "ژ", $text);
    $text = str_replace("J", "ژ", $text);
    $text = str_replace("s", "س", $text);
    $text = str_replace("S", "س", $text);
    $text = str_replace("ş", "ش", $text);
    $text = str_replace("Ş", "ش", $text);
    $text = str_replace("f", "ف", $text);
    $text = str_replace("F", "ف", $text);
    $text = str_replace("q", "ق", $text);
    $text = str_replace("Q", "ق", $text);
    $text = str_replace("k", "ك", $text);
    $text = str_replace("K", "ك", $text);
    $text = str_replace("Ğ", "ك", $text);
    $text = str_replace("g", "ك", $text);
    $text = str_replace("G", "ك", $text);
    $text = str_replace("l", "ل", $text);
    $text = str_replace("L", "ل", $text);
    $text = str_replace("m", "م", $text);
    $text = str_replace("M", "م", $text);
    $text = str_replace("n", "ن", $text);
    $text = str_replace("N", "ن", $text);
    $text = str_replace("v", "و", $text);
    $text = str_replace("V", "و", $text);
    $text = str_replace("w", "و", $text);
    $text = str_replace("W", "و", $text);
    $text = str_replace("o", "و", $text);
    $text = str_replace("u", "و", $text);
    $text = str_replace("U", "و", $text);
    $text = str_replace("ü", "و", $text);
    $text = str_replace("ö", "و", $text);
    $text = str_replace("O", "و", $text);
    $text = str_replace("Ö", "و", $text);
    $text = str_replace("Ü", "و", $text);
    $text = str_replace("h", "ه", $text);
    $text = str_replace("H", "ه", $text);
    $text = str_replace("e", "", $text);
    $text = str_replace("E", "ا", $text);
    $text = str_replace(",", "،", $text);
    $text = str_replace(";", "؛", $text);
    $text = str_replace("ğ", "غ", $text);

    // Trankskripsiyon
    $text = str_replace("â", "آ", $text);
    $text = str_replace("æ", "ث", $text);
    $text = str_replace("Â", "آ", $text);
    $text = str_replace("å", "ث", $text);
    $text = str_replace("Ó", "ح", $text);
    $text = str_replace("ó", "ح", $text);
    $text = str_replace("Ô", "ظ", $text);
    $text = str_replace("ô", "ظ", $text);
    $text = str_replace("Õ", "ذ", $text);
    $text = str_replace("õ", "ذ", $text);
    $text = str_replace("Ù", "ط", $text);
    $text = str_replace("ù", "ط", $text);
    $text = str_replace("Ú", "ق", $text);
    $text = str_replace("ú", "ق", $text);
    $text = str_replace("Û", "و", $text);
    $text = str_replace("û", "و", $text);
    $text = str_replace("Ò", "خ", $text);
    $text = str_replace("ò", "خ", $text);
    $text = str_replace("á", "غ", $text);
    $text = str_replace("à", "غ", $text);
    $text = str_replace("ä", "ص", $text);
    $text = str_replace("ã", "ص", $text);
    $text = str_replace("ë", "ض", $text);
    $text = str_replace("ê", "ض", $text);
    $text = str_replace("è", "ع", $text);
    $text = str_replace("é", "غ", $text);
    $text = str_replace("ñ", "ك", $text);
    $text = str_replace("'", "", $text);
    $text = str_replace("I", "ى", $text);
    $text = str_replace("À", "ا", $text);
    $text = str_replace("Á", "ا", $text);
    $text = str_replace("Ì", "ي", $text);
    $text = str_replace("Í", "ي", $text);
    $text = str_replace("Ñ", "ك", $text);
    $text = str_replace("ß", "و", $text);

    // Y harfi
    $text = str_replace("ı", "ي", $text);
    $text = str_replace("i", "ي", $text);
    $text = str_replace("y", "ي", $text);
    $text = str_replace("Y", "ي", $text);
    $text = str_replace("î", "ي", $text);

    // Digerleri
    $text = str_replace("ت'", "ث", $text);
    $text = str_replace("وء", "ؤ", $text);
    $text = str_replace("يء", "ئ", $text);
    $text = str_replace("ءا", "أ", $text);
    $text = str_replace("\"", "ع", $text);
    $text = str_replace("س'", "ش", $text);
    $text = str_replace("غ'", "غ", $text);
    $text = str_replace("ص'", "ض", $text);
    $text = str_replace("ط'", "ظ", $text);
    $text = str_replace("د'", "ذ", $text);
    $text = str_replace("ح'", "خ", $text);
    $text = str_replace("ر'", "ز", $text);
    $text = str_replace("ه'", "ة", $text);
    $text = str_replace("وو", "و", $text);

    $text = str_replace("0", "۰", $text);
    $text = str_replace("1", "۱", $text);
    $text = str_replace("2", "۲", $text);
    $text = str_replace("3", "۳", $text);
    $text = str_replace("4", "۴", $text);
    $text = str_replace("5", "۵", $text);
    $text = str_replace("6", "۶", $text);
    $text = str_replace("7", "۷", $text);
    $text = str_replace("8", "۸", $text);
    $text = str_replace("9", "۹", $text);

    return $text;
}

/* function */
function BugTicketPriorityClass($pr)
{
    switch ($pr) {
        case "1":
            return "danger";
            break;
        case "2":
            return "warning";
            break;
        case "3":
            return "info";
            break;
        default:
            return "default";
            break;
    }
}

/* function */
function BugTicketPriorityTitle($pr)
{
    switch ($pr) {
        case "1":
            return _BUG_PRIORITY_1;
            break;
        case "2":
            return _BUG_PRIORITY_2;
            break;
        case "3":
            return _BUG_PRIORITY_3;
            break;
        default:
            return _BUG_PRIORITY_4;
            break;
    }
}

/* function */
function TicketStatusTitle($pr)
{
    switch ($pr) {
        case "onhold":
            return _TICKET_STATUS_ONHOLD;
            break;
        case "custreplied":
            return _TICKET_STATUS_CUSTOMER_REPLIED;
            break;
        case "awaitingcustomer":
            return _TICKET_STATUS_AWAITING_CUSTOMER;
            break;
        case "reopened":
            return _TICKET_STATUS_REOPENED;
            break;
        case "closed":
            return _TICKET_STATUS_CLOSED;
            break;
        case "resolved":
            return _TICKET_STATUS_RESOLVED;
            break;
        case "new":
        default:
            return _TICKET_STATUS_NEW;
            break;
    }
}

/* function */
function TicketCategoryTitle($catID)
{
    global $db, $currentlang;
    $cat = $db->sql_fetchrow($db->sql_query("SELECT `name` FROM " . _TICKETS_CATEGORIES_ . " WHERE `ID`='" . $catID . "'"));
    return translateWord($cat["name"], $currentlang);
}

/* function */
function BankName($bankaID)
{
    global $db;
    $banka = $db->sql_fetchrow($db->sql_query("SELECT `bankaAdi` FROM " . _BANKALAR_ . " WHERE `bankaID`='" . $bankaID . "'"));
    return $banka["bankaAdi"];
}

/* function */
function KayitDurumu($kod)
{
    global $db, $ogrID;
    $ogr = $db->sql_fetchrow($db->sql_query("SELECT KayitSilmeTalebiTarihi FROM " . _OGRENCILER_ . " WHERE ogrID='" . $ogrID . "'"));
    switch ($kod) {
        case "0":
            return "<div class='callout callout-warning'><h4>" . _KAYIT_DURUMU . "</h4><p>" . _PASSIVE . "</p></div>";
            break;

        case "1":
            $deactivatonText = "";
            if ($ogr["KayitSilmeTalebiTarihi"] != "0000-00-00 00:00:00") {
                $deactivatonText = "<p class='text-black'>" . _KAYIT_SILME_ICIN_ONAY_BEKLENIYOR . "</p>";
            }
            return "<div class='callout callout-info'><h4>" . _KAYIT_DURUMU . "</h4><p>" . _ACTIVE . "</p>" . $deactivatonText . "</div>";
            break;

        case "2":
            return "<div class='callout callout-danger'><h4>" . _KAYIT_DURUMU . "</h4><p>" . _MATT . "</p></div>";
            break;

        case "3":
            return "<div class='callout callout-warning'><h4>" . _KAYIT_DURUMU . "</h4><p>" . _PREADMISSION . "</p></div>";
            break;

        case "4":
            return "<div class='callout callout-default'><h4>" . _KAYIT_DURUMU . "</h4><p>" . _GRADUATED . "</p></div>";
            break;
    }
}

/* function */
function KayitDurumuID2Status($kod)
{
    switch ($kod) {
        case "0":
            return _PASSIVE;
            break;

        case "1":
            return _ACTIVE;
            break;

        case "2":
            return _MATT;
            break;

        case "3":
            return _PREADMISSION;
            break;

        case "4":
            return _GRADUATED;
            break;
    }
}

/* function */
function myInfoBox($title, $explanation, $type = "info", $dismissable = true, $withModal = false)
{
    global $globalUserTypeClass, $globalUserTypeBgClass;

    $dismissableTypeIcon = ($type == "danger") ? "warning" : ($type == "info" ? "info-circle" : $type);
    $dismissableDivId = $dismissable ? "myDivDismissableAlert" : "myDivNonDismissableAlert";
    $dismissableCloseButton = $dismissable ? "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>" : "";
    $dismissableClass = $dismissable ? "alert-dismissable" : "";

    if ($withModal) {
        ?>
        <div class="box box-<?= $globalUserTypeClass ?>">
            <div class="box-header with-border">
                <h3 class="box-title text-<?= $globalUserTypeBgClass ?>"><i class="fa fa-warning"></i> <?= _WARNING ?>
                </h3>
                <div class="pull-right box-tools">
                    <button class="btn btn-<?= $globalUserTypeClass ?> btn-xs" type="button" data-dismiss="modal"><i
                                class="fa fa-times"></i></button>
                </div>
            </div>
            <div class="box-body">
                <div id="<?= $dismissableDivId ?>" class="alert alert-<?= $type ?> <?= $dismissableClass ?>"
                     style="margin-bottom: 3px">
                    <?= $dismissableCloseButton ?>
                    <h4 class="margin-bottom-0"><i class="fa fa-<?= $dismissableTypeIcon ?>"></i> <?= $title ?> <small
                                class="text-<?= $type ?>"><?= $explanation ?></small></h4>
                </div>
            </div>
        </div>
        <?
    } else {
        ?>
        <div id="<?= $dismissableDivId ?>" class="alert alert-<?= $type ?> <?= $dismissableClass ?>"
             style="margin-bottom: 3px">
            <?= $dismissableCloseButton ?>
            <h4 class="margin-bottom-0"><i class="fa fa-<?= $dismissableTypeIcon ?>"></i> <?= $title ?>
                <small><?= $explanation ?></small></h4>
        </div>
        <?
    }
}

/* function */
function getFirstDayOfWeekFromDate($date = null, $dbformat = false)
{
    if ($date instanceof DateTime) {
        $date = clone $date;
    } else if (!$date) {
        $date = new DateTime();
    } else {
        //if it is not database format then convert it
        if (!$dbformat) $date = FormatDateTime2DbStandard($date);
        $date = new DateTime($date);
    }

    // If the date is already a Monday, return it as-is
    if ($date->format('N') == 1) return $date->format("Y-m-d");

    // Otherwise, return the date of the nearest Monday in the past
    // This includes Sunday in the previous week instead of it being the start of a new week
    else return $date->modify('last Monday')->format("Y-m-d");
}

/* function */
function makePass()
{
    $cons = "bcdfghjklmnpqrstvwxyz";
    $vocs = "aeiou";
    for ($x = 0; $x < 6; $x++) {
        mt_srand((double)microtime() * 1000000);
        $con[$x] = substr($cons, mt_rand(0, strlen($cons) - 1), 1);
        $voc[$x] = substr($vocs, mt_rand(0, strlen($vocs) - 1), 1);
    }
    mt_srand((double)microtime() * 1000000);
    $num1 = mt_rand(0, 9);
    $num2 = mt_rand(0, 9);
    $makepass = $con[0] . $voc[0] . $con[2] . $num1 . $num2 . $con[3] . $voc[3] . $con[4];
    return ($makepass);
}

/* function */
/* most probably not using anymore */
function is_admin($admin)
{
    global $dbi;
    if (!is_array($admin)) {
        $admin = base64_decode($admin);
        $admin = addslashes($admin);
        $admin = explode(":", $admin);
        $aid = addslashes($admin[1]);
        $pwd = $admin[2];
    } else {
        $aid = addslashes($admin[1]);
        $pwd = $admin[2];
    }

    if ($aid != "" and $pwd != "") {
        //$aid = substr($aid, 0, 25);
        $pass = $dbi->where("aid", $aid)->getValue(_USERS_, "pwd");
        if (($pass == $pwd) && $pass != "") {
            return 1;
        }
    }

    return 0;
}

/* function */
function setOnline()
{
    global $dbi, $ySubeKodu, $aid, $userType, $simsDateTime;

    $ip = $_SERVER["REMOTE_ADDR"];

    $ctime = time();
    $past = $ctime - 60;

    //delete sessions older than a minute
    $dbi->where("startTime", $past, "<")->delete(_SESSION_);

    $dbi->where("userId", $aid);
    $dbi->where("hostAddress", $ip);
    $sessionId = $dbi->getValue(_SESSION_, "Id");

    if (empty($sessionId)) {
        $queryData = array(
            "userId" => $aid,
            "startTime" => $ctime,
            "startDateTime" => $simsDateTime,
            "hostAddress" => $ip,
            "userType" => $userType,
            "schoolId" => $ySubeKodu,
        );

        //add a new session
        $dbi->insert(_SESSION_, $queryData);
    }
}

/* function */
function isOnline($userId)
{
    global $dbi;

    $sessionId = $dbi->where("userId", $userId)->getValue(_SESSION_, "Id");

    if (empty($sessionId)) return "offline";
    else return "online";
}

/* function */
function updateTypeTitle($type)
{
    if ($type == "newModule") return _UPDATE_NEW_MODULE;
    elseif ($type == "newProperty") return _UPDATE_NEW_PROPERTY;
    elseif ($type == "regularUpdate") return _UPDATE_REGULAR_UPDATE;
    elseif ($type == "securityFix") return _UPDATE_SECURITY_FIX;
    elseif ($type == "bugFix") return _UPDATE_BUG_FIX;
    else return "";
}

/* function */
function incrementVersion($version)
{
    $parts = explode(".", $version);
    $vMajor = intval($parts[0]);
    $vMinor = intval($parts[1]);
    $vLatest = intval($parts[2]);

    $vLatest++;

    if ($vLatest == 100) {
        $vMinor++;
        $vLatest = "00";

        if ($vMinor == 100) {
            $vMajor++;
            $vMinor = "00";
        }
    }

    $vMinor = (strlen($vMinor) == 1) ? "0" . $vMinor : $vMinor;
    $vLatest = (strlen($vLatest) == 1) ? "0" . $vLatest : $vLatest;

    return $vMajor . "." . $vMinor . "." . $vLatest;
}

/* function */
function formatBytes($bytes, $precision = 2)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/* function */
function BolumKisaAdi($bolumKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `bolumKisaAdi` FROM " . _KURS_BOLUMLERI_ . " WHERE `bolumID`='" . $bolumKodu . "'"));
    return $row["bolumKisaAdi"];
}

/* function */
function DersBransAdi($bransKodu)
{
    global $dbi;

    $subject = $dbi->where("bID", $bransKodu)->getValue(_DERS_BRANSLARI_, "bransAdi");

    return $subject;
}

/* function */
function DersBransRengi($bransKodu)
{
    global $dbi;

    $dbi->where("bID", $bransKodu);
    $subjectInfo = $dbi->getOne(_DERS_BRANSLARI_, "bransRenk");

    return $subjectInfo["bransRenk"];
}

/* function */
function ProgressBarColor($rate, $reverse = true)
{
    if ($reverse) {
        if ($rate > 80) return "danger";
        elseif ($rate > 60) return "warning";
        elseif ($rate > 40) return "default";
        elseif ($rate > 20) return "info";
        else return "success";
    } else {
        if ($rate < 20) return "danger";
        elseif ($rate < 40) return "warning";
        elseif ($rate < 60) return "default";
        elseif ($rate < 80) return "info";
        else return "success";
    }
}

/* function */
function QuestionDifficultyLevelBackground($df)
{
    if ($df == 100) return "#d9534f";
    else if ($df >= 80) return "#f0ad4e";
    else if ($df >= 60) return "#337ab7";
    else if ($df >= 40) return "#5bc0de";
    else if ($df >= 20) return "#5cb85c";
    else return "#fff";
}

/* function */
function ScheduleLabelType($type)
{
    global $db, $currentlang;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `ders_turu_name` FROM " . _DERS_TURU_ . " WHERE `ders_turu_id`='" . $type . "'"));
    return translateWord($row["ders_turu_name"], $currentlang);
}

/* function */
function ScheduleLabelTypeBgColor($type)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `bgcolor` FROM " . _DERS_TURU_ . " WHERE `ders_turu_id`='" . $type . "'"));
    return $row["bgcolor"];
}

/* function */
function ScheduleLabel($type)
{
    if ($type == 1) return "label-blue";        //ders
    if ($type == 2) return "label-yellow";    //sinif etutu
    if ($type == 3) return "label-navy";        //grup etut
    if ($type == 4) return "label-teal";        //bireysel etut
    if ($type == 5) return "label-fuchsia";    //soru cozum etutu
    if ($type == 6) return "label-aqua";        //ek ders
    if ($type == 7) return "label-red";        //sinav
    if ($type == 8) return "label-gray";        //izin
    if ($type == 9) return "label-green";    //veli gorusme
    if ($type == 10) return "label-purple";    //nobet
}

/* function */
function ScheduleLabelTextColor($type)
{
    if ($type == 1) return "text-blue";            //ders
    elseif ($type == 2) return "text-yellow";    //sinif etutu
    elseif ($type == 3) return "text-navy";        //grup etut
    elseif ($type == 4) return "text-teal";        //bireysel etut
    elseif ($type == 5) return "text-fuchsia";    //soru cozum etutu
    elseif ($type == 6) return "text-aqua";        //ek ders
    elseif ($type == 7) return "text-red";        //sinav
    elseif ($type == 8) return "text-gray";        //izin
    elseif ($type == 9) return "text-green";        //veli gorusme
    elseif ($type == 10) return "text-purple";    //nobet
    else return "text-black";
}

/* function */
function DersBransKisaAdi($bransKodu)
{
    global $dbi;

    $subject = $dbi->where("bID", $bransKodu)->getValue(_DERS_BRANSLARI_, "bransKisaAdi");

    return $subject;
}

/* function */
/*deprecated*/
function fnOgrenciID2SinifID($studentId)
{
    return fnStudentId2BatchId($studentId);
}

/* function */
function fnStudentId2BatchId($studentId)
{
    global $dbi;

    //check student info
    $batchId = fnStdId2StdInfo($studentId, "SinifKodu");

    if (empty($batchId)) $batchId = $dbi->where("studentId", $studentId)->getValue(_BATCH_STUDENTS_, "batchId");

    return $batchId;
}

/* function */
function fnOgrenciID2OgrenciFoto($ogrID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `Foto` FROM " . _OGRENCILER_ . " WHERE `ogrID`='" . $ogrID . "'"));
    return $row["Foto"];
}

/* function */
function fnclassID2GroupID($batchId)
{
    global $dbi;

    return $dbi->where("sinifID", $batchId)->getValue(_BATCHES_, "kursKodu");
}

/* function */
function fnSeasonName($dbName = "")
{
    global $dbi, $dbname2;

    $dbName = empty($dbName) ? $dbname2 : $dbName;

    $seasonName = $dbi->where("veritabani", $dbName)->getValue(_DONEMLER_, "donem");

    return empty($seasonName) ? "-" : $seasonName;
}

/* function */
function fnUserType2LoginType($userType)
{
    global $dbi;

    $loginType = $dbi->where("typeID", $userType)->getValue(_USER_TYPES_, "loginType");

    return $loginType;
}

/* function */
function SinifAdi($sinifKodu)
{
    global $dbi;

    return $dbi->where("sinifID", $sinifKodu)->getValue(_BATCHES_, "sinifAdi");
}

/* function */
function ClassTitle($classId)
{
    global $dbi;

    return $dbi->where("dag_id", $classId)->getValue(_CLASSES_, "ders_baslik");
}

/* function */
function fnClassId2SchoolId($classId)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `subeKodu` FROM " . _BATCHES_ . " WHERE `sinifID`='" . $classId . "'"));
    return $row["subeKodu"];
}

/* function */
function SinifDerslik($sinifKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT d.`derslik` FROM " . _DERSLIKLER_ . " d, " . _BATCHES_ . " c WHERE d.`dID`=c.`derslikKodu` AND c.`sinifID`='" . $sinifKodu . "'"));
    return $row["derslik"];
}

/* function */
function DerslikAdi($derslikID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `derslik` FROM " . _DERSLIKLER_ . " WHERE `dID`='" . $derslikID . "'"));
    return $row["derslik"];
}

/* function */
function fnRoomId2RoomTitle($Id)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `roomName` FROM " . _ROOMS_ . " WHERE `Id`='" . $Id . "'"));
    return $row["roomName"];
}

/* function */
function fnTCKimlikNo2PersonelAdiSoyadi($citizenId)
{
    global $dbi;

    $personnelName = $dbi->where("tckimlikno", $citizenId)->getValue(_PERSONEL_, "adi_soyadi");

    return $personnelName;
}

/* function */
function OgretmenAdiSoyadi($perId)
{
    global $dbi;

    $personnelName = $dbi->where("perID", $perId)->getValue(_PERSONEL_, "adi_soyadi");

    return $personnelName;
}

/* function */
function OgretmenASoyadi($ogretmen_code)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `adi_soyadi` FROM " . _PERSONEL_ . " WHERE `perID`='" . $ogretmen_code . "'"));
    $adi_soyadi = $row["adi_soyadi"];
    $isim = explode(" ", $adi_soyadi);
    if (sizeof($isim) == 2) {
        $gonder = substr($isim[0], 0, 1) . ". $isim[1]";
    } else if (sizeof($isim) == 3) {
        $gonder = substr($isim[0], 0, 1) . ". " . substr($isim[1], 0, 1) . ". $isim[2]";
    }
    return $gonder;
}

/* function */
function OccupationID2Name($id)
{
    global $db, $currentlang;
    $row = $db->sql_fetchrow($db->sql_query("SELECT o.`meslekID`, l.`" . $currentlang . "` AS `translatedOcc` FROM " . _MESLEKLER_ . " o LEFT JOIN " . _LANGUAGES_ . " l ON o.`meslekAdi`=l.`alan` WHERE o.`meslekID`='" . $id . "'"));
    return $row["translatedOcc"];
}

/* function */
function NobetAlaniAdi($alanKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `yerAdi` FROM " . _OGRETMEN_NOBET_YERLERI_ . " WHERE `yerID`='" . $alanKodu . "'"));
    return $row["yerAdi"];
}

/* function */
function NobetAlaniKisaAdi($alanKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `yerKisaAdi` FROM " . _OGRETMEN_NOBET_YERLERI_ . " WHERE `yerID`='" . $alanKodu . "'"));
    return $row["yerKisaAdi"];
}

/* function */
function OgretimProgrami($progKodu, $showLevels = false)
{
    global $dbi;

    if ($showLevels) {
        return $dbi->join(_SINIF_SEVIYELERI_ . " l", "l.seviyeID=p.seviyeKodu", "LEFT")->where("p.pID", $progKodu)->getValue(_EGITIM_PROGRAMLARI_ . " p", "CONCAT('[', l.seviye, '] ', p.pAdi)");
    } else {
        return $dbi->where("pID", $progKodu)->getValue(_EGITIM_PROGRAMLARI_, "pAdi");
    }
}

/* function */
function DanismanOgretmen($sinifKodu)
{
    global $db;
    $danismanlar = array();
    $query = $db->sql_query("SELECT DISTINCT(DanismanKodu) FROM " . _OGRENCILER_ . " WHERE `SinifKodu`='" . $sinifKodu . "'");
    while ($row = $db->sql_fetchrow($query)) {
        $danismanlar[] = OgretmenAdiSoyadi($row["DanismanKodu"]);
    }
    return implode(", ", $danismanlar);
}

/* function */
function VeliAdi($ogrID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `adi_soyadi` FROM " . _VELILER_ . " WHERE `ogrID`='" . $ogrID . "'"));
    return $row["adi_soyadi"];
}

/* function */
function SeviyeAdi($levelId)
{
    global $dbi;

    $dbi->where("seviyeID", $levelId);
    $gradeLevelInfo = $dbi->getOne(_SINIF_SEVIYELERI_, "seviye");

    return $gradeLevelInfo["seviye"];
}

/* function */
function KursAdi($kursKodu)
{
    global $dbi;

    $dbi->where("kursID", $kursKodu);
    $kursAdi = $dbi->getValue(_OGRENCI_UCRETLERI_, "kursAdi");

    return $kursAdi;
}

/* function */
function BolumAdi($bolumKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `bolumAdi` FROM " . _KURS_BOLUMLERI_ . " WHERE `bolumID`='" . $bolumKodu . "'"));
    return $row["bolumAdi"];
}

/* function */
function ClassroomTitle($derslikKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `derslik` FROM " . _DERSLIKLER_ . " WHERE `dID`='" . $derslikKodu . "'"));
    return $row["derslik"];
}

/* function */
function DerslikKontenjani($derslikKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `kontenjan` FROM " . _DERSLIKLER_ . " WHERE `dID`='" . $derslikKodu . "'"));
    return intval($row["kontenjan"]);
}

/* function */
function SinifKontenjani($sinifKodu)
{
    global $dbi;

    $sinifInfo = $dbi->where("sinifID", $sinifKodu)->getOne(_BATCHES_, array("derslikKodu", "kontenjan"));

    //if quota is not emtpy then send it
    if (!empty($sinifInfo["kontenjan"])) {
        return $sinifInfo["kontenjan"];
    } //if quota is empty then check classroom quota
    else {
        $dbi->join(_DERSLIKLER_ . " d", "c.derslikKodu=d.dID", "LEFT");
        $dbi->where("c.sinifID", $sinifKodu);
        $sinifInfo = $dbi->getOne(_BATCHES_ . " c", "d.kontenjan");

        return intval($sinifInfo["kontenjan"]);
    }
}

/* function */
function SinifMevcudu($sinifKodu, $noColor = false, $includePre = true)
{
    global $dbi;

    $kayitKesin = $dbi->where("SinifKodu", $sinifKodu)->where("KayitliMi", "1")->getValue(_OGRENCILER_, "COUNT(ogrID)");
    $kayitOnKayit = $dbi->where("SinifKodu", $sinifKodu)->where("KayitliMi", "3")->getValue(_OGRENCILER_, "COUNT(ogrID)");

    if ($includePre) $ogrenciSayisi = intval($kayitKesin + $kayitOnKayit);
    else $ogrenciSayisi = intval($kayitKesin);

    if ($noColor) {
        return $ogrenciSayisi;
    } else {
        $derslik = $dbi->where("sinifID", $sinifKodu)->getValue(_BATCHES_, "derslikKodu");

        if ($ogrenciSayisi < DerslikKontenjani($derslik)) return "<span style='color:green'>" . $ogrenciSayisi . "</span>";
        else return "<span style='color:red'>" . $ogrenciSayisi . "</span>";
    }
}

/* function */
function SinifKizMevcudu($sinifKodu)
{
    global $db;
    $ogrenciler = $db->sql_fetchrow($db->sql_query("SELECT COUNT(ogrID) AS kizSayisi FROM " . _OGRENCILER_ . " WHERE `SinifKodu`='" . $sinifKodu . "' AND `Cinsiyeti`='K' AND `KayitliMi` IN ('1', '3')"));
    return $ogrenciler["kizSayisi"];
}

/* function */
function SinifErkekMevcudu($sinifKodu)
{
    global $db;
    $ogrenciler = $db->sql_fetchrow($db->sql_query("SELECT COUNT(ogrID) AS erkekSayisi FROM " . _OGRENCILER_ . " WHERE `SinifKodu`='" . $sinifKodu . "' AND `Cinsiyeti`='E' AND `KayitliMi` IN ('1', '3')"));
    return $ogrenciler["erkekSayisi"];
}

/* function */
function PersonnelID2CategoryName($id)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `cat_name` FROM " . _PERSONEL_ . " p, " . _PERSONNEL_CATEGORIES_ . " c WHERE c.`cat_id`=p.`cat_code` AND p.`perID`='" . $id . "'"));
    return translateWord($row["cat_name"]);
}

/* function */
function PersonnelCategoryID2CategoryName($id)
{

    global $dbi;

    $catname = $dbi->where("cat_id", $id)->getValue(_PERSONNEL_CATEGORIES_, "cat_name");

    return translateWord($catname);
}

/* function */
function PersonnelDepID2DepName($id)
{

    global $dbi;

    $depname = $dbi->where("Id", $id)->getValue(_PERSONEL_DEPARTMENTS_, "depTitle");

    return $depname;
}

/* function */
function FilePath2FileName($filepath)
{
    $filepath = str_replace(array("http://", "https://"), array("", ""), $filepath);
    //$dizi = explode("/", $filepath);
    //return $dizi[sizeof($dizi)-1];
    return basename($filepath);
}

/* function */
function SinifinDersi($sinifID, $tarih, $saat)
{
    global $db, $ySubeKodu;
    $dersrow = $db->sql_fetchrow($db->sql_query("SELECT `ders_turu_code`, `ders_brans_code`, `ogretmen_code`, `sinav_code` FROM " . _SCHEDULE_ . " WHERE `sinif_code`='" . $sinifID . "' AND `tarih`='" . $tarih . "' AND `saat`='" . $saat . "' AND `subeKodu`='" . $ySubeKodu . "'"));
    $ders = "";
    $ders_brans_code = $dersrow["ders_brans_code"];

    //ders ise
    if ($dersrow["ders_turu_code"] == 1 or $dersrow["ders_turu_code"] == 2 or $dersrow["ders_turu_code"] == 5 or $dersrow["ders_turu_code"] == 6) {
        $ders .= DersBransAdi($dersrow["ders_brans_code"]);
    }

    //sinav
    if ($dersrow[ders_turu_code] == 7) $ders .= ($dersrow['sinav_code'] != 0) ? SinavKisaAdi($dersrow['sinav_code']) : DersBransAdi($dersrow["ders_brans_code"]) . " (" . _SINAV . ")";

    if ($ders == "") return " ";
    else return $ders;
}

/* function */
function SinifAdlari($sinifKodlari)
{
    global $db;

    $siniflar = array();
    $kodlar = explode(",", $sinifKodlari);

    foreach ($kodlar as $key => $value) {
        $siniflar[] = "<button class='btn btn-default'>" . SinifAdi($value) . "</button>";
    }

    return implode(" ", $siniflar);
}

/* function */
//calculates the difference between two dates and returns in the format parameter
function dateDifference($date1, $date2, $differenceFormat = '%a')
{
    $date1 = new DateTime($date1);
    $date2 = new DateTime($date2);

    $interval = date_diff($date1, $date2);

    return $interval->format($differenceFormat);
}

/* function */
//calculates the difference between two dates and returns in string
function DateDifferenceWStrings($date1, $date2, $pull = "right")
{
    $date1 = new DateTime($date1);
    $date2 = new DateTime($date2);

    $interval = date_diff($date1, $date2);

    if ($interval->format("%y") > 0) {
        $labelClass = "label-danger";
        $delay = ($interval->format("%y") == 1) ? $interval->format("%y") . " " . _LC_YEAR : $interval->format("%y") . " " . _LC_YEARS;
    } else if ($interval->format("%m") > 0) {
        $labelClass = "label-warning";
        $delay = ($interval->format("%m") == 1) ? $interval->format("%m") . " " . _LC_MONTH : $interval->format("%m") . " " . _LC_MONTHS;
    } else if ($interval->format("%d") > 0) {
        $labelClass = "label-info";
        $delay = ($interval->format("%d") == 1) ? $interval->format("%d") . " " . _LC_DAY : $interval->format("%d") . " " . _LC_DAYS;
    } else if ($interval->format("%h") > 0) {
        $labelClass = "label-success";
        $delay = ($interval->format("%h") == 1) ? $interval->format("%h") . " " . _LC_HOUR : $interval->format("%h") . " " . _LC_HOURS;
    } else if ($interval->format("%i") > 0) {
        $labelClass = "label-primary";
        $delay = ($interval->format("%i") == 1) ? $interval->format("%i") . " " . _LC_MINUTE : $interval->format("%i") . " " . _LC_MINUTES;
    } else {
        $labelClass = "label-default";
        $delay = $interval->format("%s") . " " . _LC_SECONDS;
    }

    $pull = ($pull == "right") ? "pull-right" : ($pull == "left" ? "pull-left" : "");

    return '<span class="label ' . $pull . ' ' . $labelClass . '"><i class="fa fa-clock-o"></i> ' . $delay . '</span>';
}

/* function */
function SinifinOgretmeni($sinifID, $tarih, $saat)
{
    global $db, $ySubeKodu;
    $dersrow = $db->sql_fetchrow($db->sql_query("SELECT `ders_turu_code`, `ogretmen_code` FROM " . _SCHEDULE_ . " WHERE `sinif_code`='" . $sinifID . "' AND `tarih`='" . $tarih . "' AND `saat`='" . $saat . "' AND `subeKodu`='" . $ySubeKodu . "'"));
    $ders = "";
    $ders_brans_code = $dersrow[ders_brans_code];

    //ders ise
    if ($dersrow["ders_turu_code"] == 1 or $dersrow["ders_turu_code"] == 2 or $dersrow["ders_turu_code"] == 5 or $dersrow["ders_turu_code"] == 6 or $dersrow["ders_turu_code"] == 7)
        $ders .= "(" . OgretmenAdiSoyadi($dersrow["ogretmen_code"]) . ")";

    if ($ders == "") return " ";
    else return $ders;
}

/* function */
function OgretmeninDersi($perID, $tarih, $saat)
{
    global $db, $ySubeKodu;

    $ders = "";
    $ders_kayitlari = $db->sql_query("SELECT `ders_turu_code`, `ders_brans_code`, `sinif_code`, `ogrenci_code`, `sinav_code` FROM " . _SCHEDULE_ . " WHERE `ogretmen_code`='" . $perID . "' AND `tarih`='" . $tarih . "' AND `saat`='" . $saat . "' AND `subeKodu`='" . $ySubeKodu . "' ORDER BY `sinif_code` ASC");
    while ($dersrow = $db->sql_fetchrow($ders_kayitlari)) {
        $ders .= ($ders != "") ? " / " : "";
        $ders_brans_code = $dersrow["ders_brans_code"];

        //ders ise
        if ($dersrow["ders_turu_code"] == 1 or $dersrow["ders_turu_code"] == 2) $ders .= SinifAdi($dersrow["sinif_code"]) . " (" . DersBransKisaAdi($dersrow["ders_brans_code"]) . ")";

        //etut
        if ($dersrow["ders_turu_code"] == 3 or $dersrow["ders_turu_code"] == 4) $ders .= ($dersrow["ogrenci_code"] == 0) ? _ETUT : OgrenciNo($dersrow["ogrenci_code"]);

        //soru cozumu
        if ($dersrow["ders_turu_code"] == 5) $ders .= _SCS;

        //ek ders
        if ($dersrow["ders_turu_code"] == 6) $ders .= SinifAdi($dersrow["sinif_code"]) . " (" . DersBransKisaAdi($dersrow["ders_brans_code"]) . ")";

        //sýnav
        if ($dersrow[ders_turu_code] == 7) $ders .= ($dersrow['sinav_code'] != 0) ? SinavKisaAdi($dersrow['sinav_code']) : SinifAdi($dersrow["sinif_code"]) . " (" . DersBransKisaAdi($dersrow["ders_brans_code"]) . ")" . " (" . _SINAV . ")";

        //izin
        if ($dersrow[ders_turu_code] == 8) $ders .= _IZIN;

        //veli gorusme
        if ($dersrow[ders_turu_code] == 9) $ders .= _VGS;

        //nöbet
        if ($dersrow[ders_turu_code] == 10) $ders .= "N:" . NobetAlaniAdi($ders_brans_code);
    }

    if ($ders == "") return " ";
    else return $ders;
}

/* function */
function OgretmeninVGS($perID, $tarih, $saat)
{
    global $db, $ySubeKodu;
    $dersrow = $db->sql_fetchrow($db->sql_query("SELECT `ders_turu_code`, `ders_brans_code`, `sinif_code` FROM " . _SCHEDULE_ . " WHERE `ogretmen_code`='" . $perID . "' AND `tarih`='" . $tarih . "' AND `saat`='" . $saat . "' AND `subeKodu`='" . $ySubeKodu . "' AND `ders_turu_code`='9'"));
    $ders = "";
    $ders_brans_code = $dersrow[ders_brans_code];

    //izin
    if ($dersrow[ders_turu_code] == 9) $ders .= _VGS;

    if ($ders == "") return " ";
    else return $ders;
}

/* function */
function NobetListesi($tarih, $saat)
{
    global $db, $ySubeKodu;
    $ders = "";
    $nobetler = $db->sql_query("SELECT ogretmen_code, ders_brans_code FROM " . _SCHEDULE_ . " WHERE tarih='" . $tarih . "' AND saat='" . $saat . "' AND subeKodu='" . $ySubeKodu . "' AND ders_turu_code='10'");
    while ($dersrow = $db->sql_fetchrow($nobetler)) {
        $ders .= OgretmenAdiSoyadi($dersrow["ogretmen_code"]) . " (" . NobetAlaniAdi($dersrow["ders_brans_code"]) . ")";
    }
    if ($ders == "") return " ";
    else return $ders;
}

/* function */
function KulupAdi($Id)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT kulup FROM " . _CLUBS_ . " WHERE Id='" . $Id . "'"));
    return $row["kulup"];
}

/* function */
function KulupZamanDilimi($zamanKodu)
{
    global $db;
    $zaman = $db->sql_fetchrow($db->sql_query("SELECT `zaman` FROM " . _CLUB_TIMES_ . " WHERE `Id`='" . $zamanKodu . "'"));
    return $zaman["zaman"];
}

/* function */
function GetDayName($gun)
{
    if ($gun == 1) return _MONDAY;
    if ($gun == 2) return _TUESDAY;
    if ($gun == 3) return _WEDNESDAY;
    if ($gun == 4) return _THURSDAY;
    if ($gun == 5) return _FRIDAY;
    if ($gun == 6) return _SATURDAY;
    if ($gun == 7 or $gun == 0) return _SUNDAY;
}

/* function */
function GunKisaAdi($gun)
{
    if ($gun == 1) return _SHORT_MONDAY;
    if ($gun == 2) return _SHORT_TUESDAY;
    if ($gun == 3) return _SHORT_WEDNESDAY;
    if ($gun == 4) return _SHORT_THURSDAY;
    if ($gun == 5) return _SHORT_FRIDAY;
    if ($gun == 6) return _SHORT_SATURDAY;
    if ($gun == 7 or $gun == 0) return _SHORT_SUNDAY;
}

/* function */
function ServisAdi($servidID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `servisNo`, `servisAdi` FROM " . _SERVISLER_ . " WHERE `Id`='" . $servidID . "'"));
    return "[" . $row["servisNo"] . "] " . $row["servisAdi"];
}

/* function */
function SinavKisaAdi($sinavKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `sinav_k_adi` FROM " . _EXAMS_ . " WHERE `s_id`='" . $sinavKodu . "'"));
    return $row["sinav_k_adi"];
}

/* function */
function GozetmenSinavSalonu($ogretmenKodu, $sinavKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `derslik` FROM " . _DERSLIKLER_ . " d, " . _EXAM_EXAMINERS_ . " g, " . _EXAM_SESSIONS_ . " s WHERE g.`gozetmenKodu`='" . $ogretmenKodu . "' AND g.`seansKodu`=s.`seansID` AND s.`sinavKodu`='" . $sinavKodu . "' AND g.`derslikKodu`=d.`dID`"));
    return $row["derslik"];
}

/* function */
function OgrenciListesi($ogrenciler)
{
    $gonderilecek = "";
    $ogrenci_dizi = explode(",", $ogrenciler);
    for ($t = 0; $t < sizeof($ogrenci_dizi); $t++) {
        $gonderilecek .= ($gonderilecek == "") ? "<i data-toggle='tooltip' data-placement='top' title='" . fnStudentId2StudentName($ogrenci_dizi[$t]) . "'>" . OgrenciNo($ogrenci_dizi[$t]) . "</i>" : ", <i data-toggle='tooltip' data-placement='top' title='" . fnStudentId2StudentName($ogrenci_dizi[$t]) . "'>" . OgrenciNo($ogrenci_dizi[$t]) . "</i>";
    }
    return $gonderilecek;
}

/* function */
function SinavAdiDegistir($sinavKodu)
{
    switch ($sinavKodu) {
        case "sinav1":
            return "1. " . _YAZILI_SINAV;
            break;
        case "sinav2":
            return "2. " . _YAZILI_SINAV;
            break;
        case "sinav3":
            return "3. " . _YAZILI_SINAV;
            break;
        case "sinav4":
            return "4. " . _YAZILI_SINAV;
            break;
    }
}

/* function */
function SinavAdi($sinavKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `sinav_adi` FROM " . _EXAMS_ . " WHERE `s_id`='" . $sinavKodu . "'"));
    return $row["sinav_adi"];
}

/* function */
function SinavTuruKodu($sinavKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `sinav_turu` FROM " . _EXAMS_ . " WHERE `s_id`='" . $sinavKodu . "'"));
    return $row["sinav_turu"];
}

/* function */
function ExamType($Id)
{
    if ($Id == "1") return _MULTIPLE_CHOICE;
    else if ($Id == "2") return _OPEN_END;
    else return _TANIMLANMAMIS;
}

/* function */
function UcretTuruAdi($ucretKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `kursAdi` FROM " . _OGRENCI_UCRETLERI_ . " WHERE `kursID`='" . $ucretKodu . "'"));
    return $row["kursAdi"];
}

/* function */
function UcretKodu2SeviyeAdi($ucretKodu)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT l.`seviye` FROM " . _SINIF_SEVIYELERI_ . " l, " . _OGRENCI_UCRETLERI_ . " c WHERE c.`seviyeKodu`=l.`seviyeID` AND c.`kursID`='" . $ucretKodu . "'"));
    return $row["seviye"];
}

/* function */
function BankaSubeAdi($schoolId)
{
    global $db;
    $banka = $db->sql_fetchrow($db->sql_query("SELECT `subeAdi` FROM " . _BANKA_SUBELERI_ . ".banka_subeleri WHERE `subeID`='" . $schoolId . "'"));
    return $banka["subeAdi"];
}

/* function */
function BankaAdi($bankaID)
{
    global $db;
    $banka = $db->sql_fetchrow($db->sql_query("SELECT `bankaAdi` FROM " . _BANKALAR_ . " WHERE `bankaID`='" . $bankaID . "'"));
    return $banka["bankaAdi"];
}

/* function */
function OkulAdi($Id)
{
    global $dbi;

    $dbi->where("okul_id", $Id);
    return $dbi->getValue(_OKULLAR_, "okul_adi");
}

/* function */
function HesapBilgileri($accountId)
{
    global $dbi;

    $dbi->where("hesapID", $accountId);
    $accountInfo = $dbi->getOne(_BANKA_HESAPLARI_, "hesapNo, bankaKodu, bankaSubesiKodu");

    return BankaAdi($accountInfo["bankaKodu"]) . " - " . BankaSubeAdi($accountInfo["bankaSubesiKodu"]) . "<br>" . $accountInfo["hesapNo"];
}

/* function */
function BankAccountTitle($accountId)
{
    global $dbi;

    $dbi->where("hesapID", $accountId);
    $accountTitle = $dbi->getValue(_BANKA_HESAPLARI_, "hesapAdi");

    return $accountTitle;
}

/* function */
function TahsilatTuru($typeId)
{
    global $dbi;

    $dbi->where("turID", $typeId);
    return $dbi->getValue(_TAHSILAT_TURLERI_, "tahsilatTuru");
}

/* function */
function TahsilatID2ReceiptNumber($tahsilatID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `makbuzNo` FROM " . _MAKBUZLAR_ . " WHERE `tahsilatKodu`='" . $tahsilatID . "'"));
    return $row["makbuzNo"];
}

/* function */
function SenetNumarasi($senetID)
{
    global $db;
    $senetler = $db->sql_fetchrow($db->sql_query("SELECT senetNumarasi FROM " . _SENETLER_ . " WHERE `senetID`='" . $senetID . "'"));
    return $senetler["senetNumarasi"];
}

/* function */
function AvukatAdi($avID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT avukatAdi FROM " . _AVUKATLAR_ . " WHERE avID='" . $avID . "'"));
    return $row["avukatAdi"];
}

/* function */
function MakbuzNoOlustur($ogrID)
{
    global $db;
    $makbuzlar = $db->sql_fetchrow($db->sql_query("SELECT MAX(makbuzNo) AS MaximumMakbuzNo FROM " . _MAKBUZLAR_ . " WHERE `subeKodu`='" . BranchIDofStudent($ogrID) . "'"));
    $yeniMakbuzNo = (intval($makbuzlar["MaximumMakbuzNo"]) == 0) ? 100000 * BranchIDofStudent($ogrID) + 1 : intval($makbuzlar["MaximumMakbuzNo"]) + 1;
    return $yeniMakbuzNo;
}

/* function */
function ParcaliTahsilEdilmemisTaksit($ogrID)
{
    global $dbi;

    $dbi->where("ogrID", $ogrID);
    $dbi->where("tahsilatYapildiMi", "2");
    $partiallyPaidInstallment = $dbi->getOne(_TAKSITLER_, "taksitID");

    if (sizeof($partiallyPaidInstallment) > 0) {
        $sumOfPayments = $dbi->where("ogrID", $ogrID)->getValue(_TAHSILATLAR_, "SUM(tahsilatMiktari)");
        $sumOfInstallments = $dbi->where("ogrID", $ogrID)->where("tahsilatYapildiMi", array("1", "2"), "IN")->getValue(_TAKSITLER_, "SUM(taksitMiktari)");

        $difference = floatval($sumOfInstallments - $sumOfPayments);

        return ($difference > 0) ? $difference : 0;
    } else {
        return 0;
    }
}

/* function */
function SMSTelefonu($AdiSoyadi, $TCKimlikNo, $sinavKodu)
{
    global $db;
    if ($TCKimlikNo != "0") $row = $db->sql_fetchrow($db->sql_query("SELECT `telefon` FROM " . _EXAM_FILE_INFO_ . " WHERE `tc_kimlik_no`='" . $TCKimlikNo . "' AND `sinavKodu`='" . $sinavKodu . "'"));
    else $row = $db->sql_fetchrow($db->sql_query("SELECT `telefon` FROM " . _EXAM_FILE_INFO_ . " WHERE (`soyadi_adi`='" . $AdiSoyadi . "' OR `adi_soyadi`='" . $AdiSoyadi . "') AND `sinavKodu`='" . $sinavKodu . "'"));
    return $row["telefon"];
}

/* function */
function CepTelefonu($ogrenciNo)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `OgrenciCepTel` FROM " . _OGRENCILER_ . " WHERE `ogrenciNo`='" . $ogrenciNo . "'"));
    return $row["OgrenciCepTel"];
}

/* function */
function YaziIleUcret($ucret)
{
    define(_CURRENCY_ZERO, "");

    $ucret = preg_replace('/[^0-9]/', '', $ucret);
    //return $ucret;
    $yazi_ile = "";

    $ucret1 = substr($ucret, 0, -2);
    $ucret2 = substr($ucret, -2);

    $birler_basamagi_yazi = array('', _CURRENCY_ONE, _CURRENCY_TWO, _CURRENCY_THREE, _CURRENCY_FOUR, _CURRENCY_FIVE, _CURRENCY_SIX, _CURRENCY_SEVEN, _CURRENCY_EIGHT, _CURRENCY_NINE);
    $onlar_basamagi_yazi = array('', _CURRENCY_TEN, _CURRENCY_TWENTY, _CURRENCY_THIRTY, _CURRENCY_FORTY, _CURRENCY_FIFTY, _CURRENCY_SIXTY, _CURRENCY_SEVENTY, _CURRENCY_EIGHTY, _CURRENCY_NINETY);
    $yuz = _CURRENCY_HUNDRED;
    $bin = _CURRENCY_THOUSAND;
    $ana_para_birimi = _ANA_PARA_BIRIMI_;
    $ikinci_para_birimi = _IKINCI_PARA_BIRIMI_;

    //decimal part
    $kurus_birler_basamagi = $birler_basamagi_yazi[substr($ucret2, -1)];
    $kurus_onlar_basamagi = $onlar_basamagi_yazi[substr($ucret2, 0, -1)];
    $kurus_yazi_ile = (!empty($kurus_onlar_basamagi)) ? trim($kurus_onlar_basamagi) : "";
    $kurus_yazi_ile .= (!empty($kurus_birler_basamagi)) ? " " . trim($kurus_birler_basamagi) : "";
    $kurus_yazi_ile .= (!empty($kurus_yazi_ile)) ? " " . $ikinci_para_birimi : "";

    if (strlen($ucret1) > 3) {
        $binler_boluk = substr($ucret1, 0, -3);
        switch (strlen($binler_boluk)) {
            case 3:
                if (substr($binler_boluk, 0, 1) == 1) $yuzbinler_yazi = $yuz;
                else {
                    $yuzbinler = $birler_basamagi_yazi[substr($binler_boluk, 0, 1)];
                    $yuzbinler_yazi = (!empty($yuzbinler)) ? $yuzbinler . " " . $yuz : "";
                }
                $onbinler = $onlar_basamagi_yazi[substr($binler_boluk, 1, 1)];
                $binler = $birler_basamagi_yazi[substr($binler_boluk, 2, 1)];

                $yazi_ile .= (!empty($yuzbinler_yazi)) ? trim($yuzbinler_yazi) : "";
                $yazi_ile .= (!empty($onbinler)) ? " " . trim($onbinler) : "";
                $yazi_ile .= (!empty($binler)) ? " " . trim($binler) : "";
                $yazi_ile .= (!empty($yazi_ile)) ? " " . $bin : "";
                break;

            case 2:
                $onbinler = $onlar_basamagi_yazi[substr($binler_boluk, 0, 1)];
                $binler = $birler_basamagi_yazi[substr($binler_boluk, 1, 1)];

                $yazi_ile .= (!empty($onbinler)) ? trim($onbinler) : "";
                $yazi_ile .= (!empty($binler)) ? " " . trim($binler) : "";
                $yazi_ile .= (!empty($yazi_ile)) ? " " . $bin : "";

                break;

            case 1:
                if ($binler_boluk == 1) {
                    $yazi_ile .= $bin;
                } else {
                    $binler = $birler_basamagi_yazi[$binler_boluk];

                    $yazi_ile .= (!empty($binler)) ? trim($binler) : "";
                    $yazi_ile .= (!empty($yazi_ile)) ? " " . $bin : "";
                }
                break;
        }

        //birler bolugu
        $birler_basamagi = $birler_basamagi_yazi[substr($ucret1, -1)];
        $onlar_basamagi = $onlar_basamagi_yazi[substr($ucret1, -2, 1)];

        if (substr($ucret1, -3, 1) == 0) $yuzler_basamagi = "";
        else if (substr($ucret1, -3, 1) == 1) $yuzler_basamagi = $yuz;
        else $yuzler_basamagi = $birler_basamagi_yazi[substr($ucret1, -3, 1)] . " " . $yuz;

        $yazi_ile .= (!empty($yuzler_basamagi)) ? " " . $yuzler_basamagi : "";
        $yazi_ile .= (!empty($onlar_basamagi)) ? " " . $onlar_basamagi : "";
        $yazi_ile .= (!empty($birler_basamagi)) ? " " . $birler_basamagi : "";
        $yazi_ile .= (!empty($yazi_ile)) ? " " . $ana_para_birimi : "";

        $yazi_ile .= (!empty($kurus_yazi_ile)) ? " " . $kurus_yazi_ile : "";

        return trim($yazi_ile);
    } else if (strlen($ucret1) == 3) {
        $birler_basamagi = $birler_basamagi_yazi[substr($ucret1, -1)];
        $onlar_basamagi = $onlar_basamagi_yazi[substr($ucret1, -2, 1)];

        if (substr($ucret1, -3, 1) == 0) $yuzler_basamagi = "";
        else if (substr($ucret1, -3, 1) == 1) $yuzler_basamagi = $yuz;
        else $yuzler_basamagi = $birler_basamagi_yazi[substr($ucret1, -3, 1)] . " " . $yuz;

        $yazi_ile .= (!empty($yuzler_basamagi)) ? trim($yuzler_basamagi) : "";
        $yazi_ile .= (!empty($onlar_basamagi)) ? " " . $onlar_basamagi : "";
        $yazi_ile .= (!empty($birler_basamagi)) ? " " . $birler_basamagi : "";
        $yazi_ile .= (!empty($yazi_ile)) ? " " . $ana_para_birimi : "";

        $yazi_ile .= (!empty($yazi_ile) && !empty($kurus_yazi_ile)) ? " " . $kurus_yazi_ile : "";

        return trim($yazi_ile);
    } else if (strlen($ucret1) == 2) {
        $birler_basamagi = $birler_basamagi_yazi[substr($ucret1, -1)];
        $onlar_basamagi = $onlar_basamagi_yazi[substr($ucret1, -2, 1)];

        $yazi_ile .= (!empty($onlar_basamagi)) ? trim($onlar_basamagi) : "";
        $yazi_ile .= (!empty($birler_basamagi)) ? " " . $birler_basamagi : "";
        $yazi_ile .= (!empty($yazi_ile)) ? " " . $ana_para_birimi : "";

        $yazi_ile .= (!empty($yazi_ile) && !empty($kurus_yazi_ile)) ? " " . $kurus_yazi_ile : "";

        return trim($yazi_ile);
    } else if (strlen($ucret1) == 1) {
        if ($ucret1 == 0) return trim($kurus_yazi_ile);
        else {
            $birler_basamagi = $birler_basamagi_yazi[$ucret1];

            $yazi_ile .= (!empty($birler_basamagi)) ? trim($birler_basamagi) : "";
            $yazi_ile .= (!empty($yazi_ile)) ? " " . $ana_para_birimi : "";

            $yazi_ile .= (!empty($yazi_ile) && !empty($kurus_yazi_ile)) ? " " . $kurus_yazi_ile : "";

            return trim($yazi_ile);
        }
    } else {
        return trim($kurus_yazi_ile);
    }
}

/* function */
function SenetNoOlustur($ogrID)
{
    global $dbi, $ySubeKodu;

    $maxSenetNo = $dbi->where("subeKodu", $ySubeKodu)->getValue(_SENETLER_, "MAX(senetNumarasi)");

    $yeniSenetNo = (empty($maxSenetNo)) ? (100000 * $ySubeKodu) + 1 : intval($maxSenetNo) + 1;

    return $yeniSenetNo;
}

/* function */
function fnExamTemplateID2ExamTemplateTitle($tID)
{
    global $db;
    if ($tID == "-1") {
        return _STS;
    } else {
        $row = $db->sql_fetchrow($db->sql_query("SELECT `tTitle` FROM " . _EXAM_TYPES_ . " WHERE `tID`='" . $tID . "'"));
        return $row["tTitle"];
    }
}

/* function */
function OptikAdi($optikKodu)
{
    global $dbi;

    return $dbi->where("oID", $optikKodu)->getValue(_OPTIC_FORMS_, "formAdi");
}


/* function */
function fnOpticFieldID2OpticFieldTitle($fieldID)
{
    global $dbi;

    if (is_numeric($fieldID)) {
        return DersBransAdi($fieldID);
    } else {
        $dbi->where("alan_tanimi_id", $fieldID);
        $fieldTitle = $dbi->getValue(_OPTIC_FORM_FIELD_NAMES_, "alan_tanimi_title");

        return $fieldTitle;
    }
}

/* function */
function puanHesaplamaFormulu($pointID, $basePoint, $delete = false)
{
    global $dbi;

    //get base point
    $hesaplamaFormulu = ($basePoint > 0) ? array("<button class='btn btn-flat btn-info btn-xs'>" . $basePoint . "</button>") : array();

    //get coefficients
    $dbi->join(_DERS_BRANSLARI_ . " s", "s.bID=k.dersKodu", "LEFT");
    $dbi->join(_EXAM_TYPE_SCORE_TYPES_ . " p", "p.pID=k.puanKodu", "LEFT");
    $dbi->where("k.puanTuruKodu", $pointID);
    $dbi->orderBy("k.puanKodu", "desc");
    $dbi->orderBy("k.kID", "asc");
    $katsayilar = $dbi->get(_EXAM_TYPE_SCORE_TYPE_COEFFICIENTS_ . " k", null, "k.kID, k.puanKodu, k.puanPercentage, k.dersKodu, k.katSayi, p.puanTuruAdi, s.bransKisaAdi");

    foreach ($katsayilar as $katsayi) {
        if (!empty($katsayi["dersKodu"])) {
            //print formula
            if ($delete) $hesaplamaFormulu[] = " <button class='btn btn-flat btn-warning btn-xs btn-sims-delete-coefficient' href='index.php?op=iudExamPointTemplate&action=deleteCoefficient&kID=" . $katsayi["kID"] . "' data-toggle='tooltip' data-placement='top' title='" . _CLICK_TO_DELETE_COEFFICIENT . "'>" . $katsayi["katSayi"] . " &times; " . $katsayi["bransKisaAdi"] . "</button>";
            else $hesaplamaFormulu[] = " <button class='btn btn-flat btn-warning btn-xs'>" . $katsayi["katSayi"] . " &times; " . $katsayi["bransKisaAdi"] . "</button>";
        } else if (!empty($katsayi["puanKodu"])) {
            //print formula
            if ($delete) $hesaplamaFormulu[] = " <button class='btn btn-flat btn-danger btn-xs btn-sims-delete-coefficient' href='index.php?op=iudExamPointTemplate&action=deleteCoefficient&kID=" . $katsayi["kID"] . "' data-toggle='tooltip' data-placement='top' title='" . _CLICK_TO_DELETE_COEFFICIENT . "'><span class='sims-percentage'>" . $katsayi["puanPercentage"] . "</span> &times; " . $katsayi["puanTuruAdi"] . "</button>";
            else $hesaplamaFormulu[] = " <button class='btn btn-flat btn-danger btn-xs'><span class='sims-percentage'>" . $katsayi["puanPercentage"] . "</span> &times; " . $katsayi["puanTuruAdi"] . "</button>";
        }
    }

    //return by imploding
    return implode(" <button class='btn btn-flat btn-primary btn-xs text-bold'>+</button> ", $hesaplamaFormulu);
}

/* function */
function fnClassID2CurriculumProgramID($classID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `progKodu` FROM " . _BATCHES_ . " WHERE `sinifID`='" . $classID . "'"));
    return $row["progKodu"];
}

/* function */
function CurriculumUnitTitle($unitID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `unite` FROM " . _EGITIM_UNITELER_ . " WHERE `uniteID`='" . $unitID . "'"));
    return $row["unite"];
}

/* function */
function CurriculumUnitLongTitle($unitID, $subUnit = "")
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `unite`, `ustUniteKodu` FROM " . _EGITIM_UNITELER_ . " WHERE `uniteID`='" . $unitID . "'"));
    if ($row["ustUniteKodu"] == "0") return $subUnit == "" ? $row["unite"] : $row["unite"] . " -> " . $subUnit;
    else return CurriculumUnitLongTitle($row["ustUniteKodu"], $row["unite"]);
}

/* function */
function CurriculumObjectiveTitle($kID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `kazanim` FROM " . _EGITIM_UNITE_KAZANIMLAR_ . " WHERE `kID`='" . $kID . "'"));
    return $row["kazanim"];
}

/* function */
function CurriculumObjectiveLongTitle($kID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `kazanim`, `uniteKodu` FROM " . _EGITIM_UNITE_KAZANIMLAR_ . " WHERE `kID`='" . $kID . "'"));
    return CurriculumUnitLongTitle($row["uniteKodu"]) . " &#8594; " . $row["kazanim"];
}

/* function */
function FixPhoneNumberForSMS($gsmno, $phoneCode)
{
    //clean non numerics
    $gsmno = preg_replace("/[^0-9\.]/i", "", $gsmno);

    return $phoneCode . substr($gsmno, -10);
}

/* function */
function fnSMSTemplateTitle($tID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `sablon_adi` FROM " . _SMS_TEMPLATES_ . " WHERE `sablon_id`='" . $tID . "'"));
    return $row["sablon_adi"];
}

/* function */
function SMSLog($sent, $ogrenciNo, $veliNo, $personelNo, $adiSoyadi, $gsmno, $message, $messageID = "0", $messageOriginator = "", $sdatetime = "")
{
    global $dbi, $aid, $ySubeKodu, $simsDateTime;

    $sdatetime = empty($sdatetime) ? $simsDateTime : $sdatetime;

    $queryData = array(
        "ogrenciNo" => $ogrenciNo,
        "veliNo" => $veliNo,
        "personelNo" => $personelNo,
        "adiSoyadi" => $adiSoyadi,
        "telefonNo" => $gsmno,
        "mesajBaslik" => $messageOriginator,
        "mesaj" => $message,
        "durum" => $sent,
        "message_id" => $messageID,
        "zaman" => $sdatetime,
        "gonderen" => $aid,
        "gonderimTarihi" => $simsDateTime,
        "subeKodu" => $ySubeKodu
    );

    $kayit = $dbi->insert(_SMS_LOGS_, $queryData);

    if ($kayit) return true;
    else return false;
}

/* function */
function SMSStatus($code, $html = true)
{
    if ($code == "0") {
        if ($html) return "<span class='text-red'>" . _UNSENT_SMS . "</span>";
        else return _UNSENT_SMS;
    } elseif ($code == "1") {
        if ($html) return "<span class='text-green'>" . _SENT_SMS . "</span>";
        else return _SENT_SMS;
    } else {
        if ($html) return "<span class='text-orange'>" . _WAITING_APPROVAL_SMS . "</span>";
        else return _WAITING_APPROVAL_SMS;
    }
}

/* function */
function AdoptMessageToTemplate($templateId = 0, $parameters = "", $message = "")
{
    global $dbi, $ySubeKodu, $simsDate;

    $parametersKeys = array_keys($parameters);

    //add school name and date to the parameters
    if (!in_array("{SCHOOL}", $parametersKeys)) $parameters["{SCHOOL}"] = BranchName($ySubeKodu);
    if (!in_array("{DATE}", $parametersKeys)) $parameters["{DATE}"] = FormatDateNumeric2Local($simsDate);

    if ($templateId > 0 && empty($message)) {
        //get template
        $dbi->where("sablon_id", $templateId);
        $message2Send = $dbi->getValue(_SMS_TEMPLATES_, "sablon_mesaj");
    } else {
        $message2Send = $message;
    }

    //get parameters
    $dbi->orderBy("ID", "ASC");
    $textParameters = $dbi->get(_SMS_PARAMETERS_, null, "degisken");

    foreach ($textParameters as $textParameter) {
        $message2Send = str_replace($textParameter["degisken"], $parameters[$textParameter["degisken"]], $message2Send);
    }

    return $message2Send;
}

/* function */
function AdoptEMailSubject2Template($templateId = 0, $parameters = "", $subject = "")
{
    global $dbi, $ySubeKodu, $simsDate;

    //add school name and date to the parameters
    $parameters["{SCHOOL}"] = BranchName($ySubeKodu);
    $parameters["{DATE}"] = FormatDateNumeric2Local($simsDate);

    if ($templateId > 0 && empty($subject)) {
        //get template
        $dbi->where("Id", $templateId);
        $messageSubject = $dbi->getValue(_EMAIL_TEMPLATES_, "subject");
    } else {
        $messageSubject = $subject;
    }

    //get parameters
    $dbi->orderBy("ID", "ASC");
    $emailParameters = $dbi->get(_SMS_PARAMETERS_, null, "degisken");

    foreach ($emailParameters as $emailParameter) {
        $messageSubject = str_replace($emailParameter["degisken"], $parameters[$emailParameter["degisken"]], $messageSubject);
    }

    return $messageSubject;
}

/* function */
function AdoptEMailContent2Template($templateId = 0, $parameters = "", $content = "")
{
    global $dbi, $ySubeKodu, $simsDate;

    //add school name and date to the parameters
    $parameters["{SCHOOL}"] = BranchName($ySubeKodu);
    $parameters["{DATE}"] = FormatDateNumeric2Local($simsDate);

    if ($templateId > 0 && empty($content)) {
        //get template
        $dbi->where("Id", $templateId);
        $messageContent = $dbi->getValue(_EMAIL_TEMPLATES_, "content");
    } else {
        $messageContent = $content;
    }

    //get parameters
    $dbi->orderBy("ID", "ASC");
    $emailParameters = $dbi->get(_SMS_PARAMETERS_, null, "degisken");

    foreach ($emailParameters as $emailParameter) {
        $messageContent = str_replace($emailParameter["degisken"], $parameters[$emailParameter["degisken"]], $messageContent);
    }

    return $messageContent;
}

/* function */
function fnOgrID2ParentID($stdId)
{
    global $dbi;

    $dbi->where("ogrID", $stdId);
    $vId = $dbi->getValue(_VELILER_, "vID");

    //$row = $db->sql_fetchrow($db->sql_query("SELECT `vID` FROM "._VELILER_." WHERE `ogrID`='".$ogrID."'"));
    return $vId;
}

/* function */
function fnParentID2ParentName($vID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `adi_soyadi` FROM " . _VELILER_ . " WHERE `vID`='" . $vID . "'"));
    return $row["adi_soyadi"];
}

/* function */
function fnUnitID2UnitTitle($Id)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `unite` FROM " . _EGITIM_UNITELER_ . " WHERE `uniteID`='" . $Id . "'"));
    return $row["unite"];
}

/* function */
function fnUnitID2AllUpperUnits($Id)
{
    global $db;

    $pUpperUnits = $Id;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `ustUniteKodu` FROM " . _EGITIM_UNITELER_ . " WHERE `uniteID`='" . $Id . "'"));
    if ($row["ustUniteKodu"] == 0) return $pUpperUnits;
    else {
        $pUpperUnits .= "," . $row["ustUniteKodu"];
        $row = $db->sql_fetchrow($db->sql_query("SELECT `ustUniteKodu` FROM " . _EGITIM_UNITELER_ . " WHERE `uniteID`='" . $row["ustUniteKodu"] . "'"));
        if ($row["ustUniteKodu"] == 0) return $pUpperUnits;
        else {
            $pUpperUnits .= "," . $row["ustUniteKodu"];
            $row = $db->sql_fetchrow($db->sql_query("SELECT `ustUniteKodu` FROM " . _EGITIM_UNITELER_ . " WHERE `uniteID`='" . $row["ustUniteKodu"] . "'"));
            if ($row["ustUniteKodu"] == 0) return $pUpperUnits;
            else return $pUpperUnits . "," . $row["ustUniteKodu"];
        }
    }
}

/* function */
function IhtiyacTuru($Id)
{
    global $db;
    $ihtiyac_turu = $db->sql_fetchrow($db->sql_query("SELECT `ihtiyacTuru` FROM " . _REQUESTS_TYPES_ . " WHERE `Id`='" . $Id . "'"));
    return $ihtiyac_turu["ihtiyacTuru"];
}

/* function */
function fnEMailTemplateTitle($tID)
{
    global $db;
    $row = $db->sql_fetchrow($db->sql_query("SELECT `title` FROM " . _EMAIL_TEMPLATES_ . " WHERE `Id`='" . $tID . "'"));
    return $row["title"];
}

/* function */
/*
 * @mail_to can be array or single string
 * @mail_cc can be array or single string
 * @mail_bcc should be string
 * @mail_parameters should be array
 *
 */
function sendEmail($mail_to, $subject, $message, $mail_cc = "", $mail_bcc = "", $mail_attachment = "", $log = true, $mail_from = "", $mail_from_name = "")
{
    global $dbi, $timeZone, $adminmail, $headquartersmail, $sitename, $aid, $ySubeKodu, $simsDateTime, $dbname2;

    //sleep for 0.1 sec for escaping to be assumed as spam
    usleep(100000);

    //get smtp info
    $smtpRow = $dbi->where("schoolId", ['0', $ySubeKodu], "IN")->orderBy("schoolId", "DESC")->getOne(_SMTP_SETTINGS_);

    $smtpHost = $smtpRow["smtp_host"];
    $smtpUsername = $smtpRow["smtp_username"];
    $smtpPassword = $smtpRow["smtp_password"];
    $smtpEmail = $smtpRow["smtp_email"];
    $smtpPort = $smtpRow["smtp_port"];

    date_default_timezone_set($timeZone);

    //add PHPMailer class auto loader
    require_once __DIR__ . "/../class/PHPMailer/PHPMailerAutoload.php";

    $mail = new PHPMailer;

    // Set mailer to use SMTP
    $mail->isSMTP();
    //$mail->Mailer = "smtp";

    //send single emails
    //$mail->SingleTo = true;

    // Specify main and backup SMTP servers
    $mail->Host = $smtpHost;

    // SMTP username
    $mail->Username = $smtpUsername;

    // SMTP password
    $mail->Password = $smtpPassword;

    // Enable SMTP authentication
    $mail->SMTPAuth = true;

    // Enable TLS encryption, `ssl` also accepted
    $mail->SMTPSecure = 'tls';

    // TCP port to connect to
    $mail->Port = $smtpPort;

    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;

    //Ask for HTML-friendly debug output
    //$mail->Debugoutput = 'html';

    //set character set
    $mail->CharSet = 'UTF-8';

    //set mail from
    if (empty($mail_from)) $mail->setFrom($adminmail, $sitename);
    else if (empty($mail_from_name)) $mail->setFrom($mail_from);
    else $mail->setFrom($mail_from, $mail_from_name);

    //set mail to addresses
    if (is_array($mail_to)) {
        foreach ($mail_to as $key => $value) {
            $mail->addAddress($value);
        }

        $mail_to_str = implode(",", $mail_to);
    } else {
        $mail->addAddress($mail_to);
        $mail_to_str = $mail_to;
    }

    //set mail reply-to address
    //$mail->addReplyTo('no-reply@smartclass.biz');

    //set cc address
    if (is_array($mail_cc)) {
        foreach ($mail_cc as $key => $value) {
            if (!empty($value)) {
                $mail->addCC($value);
            }
        }

        $mail_cc_str = implode(",", $mail_cc);
    } else if (!empty($value)) {
        $mail->addCC($mail_cc);
        $mail_cc_str = $mail_cc;
    } else {
        $mail_cc_str = "";
    }

    //set bcc address
    if (!empty($mail_bcc)) {
        $mail->addBCC($mail_bcc);
    }

    //set smartclass emailing group for bcc
    //$mail->addBCC("smartclass-emailing@googlegroups.com");

    //set attachments
    if (is_array($mail_attachment)) {
        foreach ($mail_attachment as $key => $attch) {
            $attchname = FilePath2FileName($attch);
            $attchname = RandomizedFileTitle($attchname);

            //$mail->addAttachment($attch, $name);
            $mail->addStringAttachment(file_get_contents($attch), $attchname);
        }

        $mail_attachment_str = serialize($mail_attachment);
    }

    //Set email format to HTML
    $mail->isHTML(true);

    //set mail subject body and altbody
    $mail->Subject = $subject;
    $mail->Body = $message;
    //$mail->AltBody = $message;

    //variables for log
    $emailSender = $aid == "" ? "SmartClass" : $aid;

    //query data for logs
    $queryData = array(
        "emailFrom" => $adminmail,
        "emailTo" => $mail_to_str,
        "emailCC" => $mail_cc_str,
        "emailSubject" => $subject,
        "emailContent" => $message,
        "emailAttachment" => $mail_attachment_str,
        "sentDateTime" => $simsDateTime,
        "emailSender" => $emailSender,
        "subeKodu" => $ySubeKodu
    );

    //send email
    if (!$mail->send()) {
        //not sent
        $queryData["isSent"] = "0";
        //$queryData["serverError"] = $mail->getError();
        $queryData["serverError"] = _ERROR_;

        //save email for reporting purposes
        if ($log) $dbi->insert(_EMAIL_LOGS_, $queryData);

        $mail->ClearAllRecipients();
        $mail->SmtpClose();

        return false;
    } else {
        //sent
        $queryData["isSent"] = "1";

        //save email for reporting purposes
        if ($log) $dbi->insert(_EMAIL_LOGS_, $queryData);

        $mail->ClearAllRecipients();
        $mail->SmtpClose();

        //send to emailing group
        //$headers = "From: SmartClass Admin <admin@smartclass.biz>\r\n";
        //$headers .= "MIME-Version: 1.0\r\n";
        //$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        //mail(EMAILING_GROUP, $subject, $message, $headers);

        return true;
    }
}

/* function */
/*
 * @mail_to can be array or single string
 * @mail_cc can be array or single string
 * @mail_bcc should be string
 * @mail_parameters should be array
 *
 */
function saveEmail2Send($mailFrom, $mailFromName, $mailTo, $subject, $message, $mailCC = "", $mailBCC = "", $mailAttachment = "")
{
    global $dbi, $timeZone, $adminmail, $headquartersmail, $sitename, $aid, $ySubeKodu, $simsDateTime, $dbname2;

    date_default_timezone_set($timeZone);

    $queryData = array(
        "emailFrom" => $mailFrom,
        "emailFromName" => $mailFromName,
        "emailTo" => $mailTo,
        "emailSubject" => html_entity_decode($subject, ENT_QUOTES),
        "emailContent" => html_entity_decode($message, ENT_QUOTES),
        "emailCC" => $mailCC,
        "emailBCC" => $mailBCC,
        "emailSender" => $aid,
        "sentDateTime" => $simsDateTime,
        "subeKodu" => $ySubeKodu
    );

    if (!empty($mailAttachment)) $queryData["emailAttachment"] = serialize($mailAttachment);

    //save emails to send on background
    $dbi->insert(_EMAILS_2SEND_, $queryData);
}

/* function */
/*
 * @mail_to can be array or single string
 * @mail_cc can be array or single string
 * @mail_bcc should be string
 * @mail_parameters should be array
 *
 */
function sendMeetingRequest($from_email, $from_title, $to_email, $subject, $message, $start, $end, $url = '', $uid = '', $unformattedMessage = '', $log = true)
{

    global $dbi, $timeZone, $adminmail, $sitename, $aid, $ySubeKodu, $simsDateTime;

    $smtpRow = $dbi->orderBy("id", "asc")->getOne(_SMTP_SETTINGS_);

    $smtpHost = $smtpRow["smtp_host"];
    $smtpUsername = $smtpRow["smtp_username"];
    $smtpPassword = $smtpRow["smtp_password"];
    $smtpEmail = $smtpRow["smtp_email"];
    $smtpPort = $smtpRow["smtp_port"];

    date_default_timezone_set($timeZone);

    //add PHPMailer class auto loader
    require_once "class/PHPMailer/PHPMailerAutoload.php";
    require_once "class/PHPMailer/extras/EasyPeasyICS.php";

    $mail = new PHPMailer;
    $ics = new EasyPeasyICS("SmartClass");

    // Set mailer to use SMTP
    $mail->isSMTP();

    // Specify main and backup SMTP servers
    $mail->Host = $smtpHost;

    // SMTP username
    $mail->Username = $smtpUsername;

    // SMTP password
    $mail->Password = $smtpPassword;

    // Enable SMTP authentication
    $mail->SMTPAuth = true;

    // Enable TLS encryption, `ssl` also accepted
    $mail->SMTPSecure = 'tls';

    // TCP port to connect to
    $mail->Port = $smtpPort;

    //Enable SMTP debugging
    $mail->SMTPDebug = 0;

    //set character set
    $mail->CharSet = 'UTF-8';

    //set mail from
    $mail->setFrom($from_email, $from_title);

    //set mail to addresses
    if (is_array($to_email)) {
        foreach ($to_email as $key => $value) {
            $mail->addAddress($value);
        }

        $mail_to_str = implode(",", $to_email);
    } else {
        $mail->addAddress($to_email);
        $mail_to_str = $to_email;
    }

    //Set email format to HTML
    $mail->isHTML(true);

    //set alternative exists
    //$mail->alternativeExists();

    //set mail subject body and altbody
    //$mail->Subject = _MEETING_REQUEST . " [" . $subject . "]";
    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->AltBody = $message;

    //make dates unix timestamp
    $start = strtotime($start);
    $end = strtotime($end);

    //set ical if a meeting request
    $ics->addEvent($start, $end, $subject, $unformattedMessage, $url, $uid);
    $iCal = $ics->render(false);

    //set ics to Ical parameter
    $mail->Ical = $iCal;

    sleep(1);

    //variables for log
    $emailSender = empty($aid) ? "SmartClass" : $aid;

    //query data for logs
    $queryData = array(
        "emailFrom" => $adminmail,
        "emailTo" => $mail_to_str,
        "emailSubject" => $subject,
        "emailContent" => $message,
        "sentDateTime" => $simsDateTime,
        "emailSender" => $emailSender,
        "subeKodu" => $ySubeKodu
    );

    //send email
    if (!$mail->send()) {
        //not sent
        $queryData["isSent"] = "0";
        //$queryData["serverError"] = $mail->getError();
        $queryData["serverError"] = _ERROR_;

        //save email for reporting purposes
        if ($log) $dbi->insert(_EMAIL_LOGS_, $queryData);

        $mail->ClearAllRecipients();
        $mail->SmtpClose();

        return false;
    } else {
        //sent
        $queryData["isSent"] = "1";

        //save email for reporting purposes
        if ($log) $dbi->insert(_EMAIL_LOGS_, $queryData);

        $mail->ClearAllRecipients();
        $mail->SmtpClose();

        //send to emailing group
        //$headers = "From: SmartClass Admin <admin@smartclass.biz>\r\n";
        //$headers .= "MIME-Version: 1.0\r\n";
        //$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        //mail(EMAILING_GROUP, $subject, $message, $headers);

        return true;
    }

    return true;
}

/* function */
/* sendNotification is a function to send notification to web or mobile users
 *
 * @notiTo can be array or single string
 * $notiTo, $subject, $message
 */
function sendNotification($userId, $content, $title = "", $data = "", $subtitle = "", $url = "")
{

    global $dbi, $aid, $ySubeKodu, $simsDateTime;

    //fix school id for apis
    if (is_null($ySubeKodu)) $ySubeKodu = "0";

    //check content
    $htmlTagSearch = array("  ", "&Ouml;", "&ouml;", "&Ccedil;", "&ccedil;", "&Uuml;", "&uuml;", "&nbsp;");
    $htmlTagReplace = array(" ", "Ö", "ö", "Ç", "ç", "Ü", "ü", " ");

    //if(!empty($title)) { $title = str_replace($htmlTagSearch, $htmlTagReplace, $title); }
    //if(!empty($subtitle)) { $subtitle = str_replace($htmlTagSearch, $htmlTagReplace, $subtitle); }
    //if(!empty($content)) { $content = str_replace($htmlTagSearch, $htmlTagReplace, $content); }

    if (!empty($title)) {
        $title = strip_tags($title);
        $title = str_replace($htmlTagSearch, $htmlTagReplace, $title);
    }
    if (!empty($subtitle)) {
        $subtitle = strip_tags($subtitle);
        $subtitle = str_replace($htmlTagSearch, $htmlTagReplace, $subtitle);
    }
    if (!empty($content)) {
        $content = strip_tags($content);
        $content = str_replace($htmlTagSearch, $htmlTagReplace, $content);
    }

    include_once "class/OneSignal/notifications.php";
    $notify = new notifications();

    //delete null device ids
    //this is commented. move it somewhere else later.
    //$dbi->where("oneSignalUserId", "null", "=")->delete(_USER_ONESIGNAL_IDS_);

    //get user player ids
    $playerIds = $dbi->where("aid", $userId)->getValue(_USER_ONESIGNAL_IDS_, "oneSignalUserId", null);

    //set player ids for the users
    $notify->setPlayerIds($playerIds);

    //set title if not empty
    if (!empty($title)) $notify->setHeadings($title);

    //set subtitle if not empty
    //if(!empty($subtitle)) $notify->setSubtitle($subtitle);
    //if(!empty($subtitle) && $title != $subtitle) $notify->setSubtitle($subtitle);

    //set data if not empty
    if (!empty($data)) $notify->setData($data);

    //set url if not empty
    //if(!empty($url)) $notify->setUrl($url);

    //add button
    //$notify->addButton(array("id" => "id1", "text" => "Open", "icon" => "ic_menu_share"));

    //set contents
    $notify->setContents($content);

    //send notification
    $result = $notify->createNotification();

    $return = json_encode($result);
    $return = json_decode($return, true);

    $players = empty($playerIds) ? "" : implode(",", $playerIds);

    //make log data
    $queryData = array(
        "notificationId" => $return["id"],
        "title" => serialize($title),
        "subTitle" => serialize($subtitle),
        "contents" => serialize($content),
        "data" => serialize($data),
        "userId" => $userId,
        "playerIds" => $players,
        "nofReceipents" => $return["recipients"],
        "errorMessage" => serialize($return["errors"]),
        "senderId" => $aid,
        "sendDateTime" => $simsDateTime,
        "schoolId" => $ySubeKodu
    );

    //insert log
    if (!empty($return["id"])) //success
    {
        //result
        $queryData["result"] = "success";

        //insert
        $dbi->insert(_NOTIFICATION_LOGS_, $queryData);

        return $dbi->getLastError();

        if (!empty($return["errors"]["invalid_player_ids"])) {
            //delete invalid player ids
            foreach ($return["errors"]["invalid_player_ids"] as $invalidId) {
                //delete it
                $dbi->where("aid", $userId)->where("oneSignaluserId", $invalidId)->delete(_USER_ONESIGNAL_IDS_);
            }
        }

        //error
        return true;
    } else //error
    {
        //result
        $queryData["result"] = "error";

        //insert
        $dbi->insert(_NOTIFICATION_LOGS_, $queryData);

        //return
        return false;
    }
}

/* function */
function msgSMSMessage($message)
{
    $smsMessage = $message;
    $smsMessage .= " SmartClass";

    return $smsMessage;
}

/* function */
/*
 * @$phoneNumber should be single string
 *
 */
function sendSMS($phoneNumber, $name = '', $message = '', $ogrenciNo = '', $veliNo = '', $personelNo = '', $adiSoyadi = '', $apiOriginator = '', $sdatetime = '')
{
    global $dbi, $countryCode, $ySubeKodu, $simsDateTime;

    //get country phone code
    $dbi->where("isoCode", $countryCode);
    $phoneCode = $dbi->getValue(_COUNTRIES_, "phone");

    //get sms class
    $dbi->join(_SMS_APIS_ . " a", "a.Id=c.api_class_id", "LEFT");
    $dbi->where("c.subeKodu", array(0, $ySubeKodu), "IN");
    $dbi->orderBy("c.subeKodu", "DESC");
    $smsConfig = $dbi->getOne(_SMS_CONFIG_ . " c", "c.api_class_id, a.api_file");

    if (empty($smsConfig["api_file"]) || !file_exists(absoluteFileLocation($smsConfig["api_file"]))) return _SMS_API_FILE_DOES_NOT_EXIST;

    include_once $smsConfig["api_file"];
    $sms = new sendSMS();

    //set phone number
    $gsmno = FixPhoneNumberForSMS($phoneNumber, $phoneCode);
    $sms->setGsmNo($gsmno);

    //set message
    $sms->setMessageText($message);

    //set originator if it is not empty
    if (!empty($apiOriginator)) $sms->setOriginator($apiOriginator);

    //sleep for 0.2 sec for escaping to be assumed as spam
    usleep(200000);

    //send sms
    $result = $sms->send();

    // sms sunucusundan cevap alalim.
    $isSent = $result["basari"] ? "1" : "0";

    //save to logs
    $t = SMSLog($isSent, $ogrenciNo, $veliNo, $personelNo, $adiSoyadi, $gsmno, $message, $result["mesaj"], $apiOriginator, $sdatetime);

    //return
    return $result["basari"] ? "sent" : $result["mesaj"];
}


/* function */
function FixQuotes($what = "")
{
    /*
    while (preg_match("\\\\'", $what)) {
        $what = ereg_replace("\\\\'","'",$what);
    }
	    */
    return $what;
}

/* function */
function check_words($Message)
{
    global $CensorMode, $CensorReplace, $EditedMessage;
    //include("config.php");
    $EditedMessage = $Message;
    if ($CensorMode != 0) {
        if (is_array($CensorList)) {
            $Replace = $CensorReplace;
            if ($CensorMode == 1) {
                for ($i = 0; $i < count($CensorList); $i++) {
                    $EditedMessage = preg_replace("$CensorList[$i]([^a-zA-Z0-9])", "$Replace\\1", $EditedMessage);
                }
            } elseif ($CensorMode == 2) {
                for ($i = 0; $i < count($CensorList); $i++) {
                    $EditedMessage = preg_replace("(^|[^[:alnum:]])$CensorList[$i]", "\\1$Replace", $EditedMessage);
                }
            } elseif ($CensorMode == 3) {
                for ($i = 0; $i < count($CensorList); $i++) {
                    $EditedMessage = preg_replace("$CensorList[$i]", "$Replace", $EditedMessage);
                }
            }
        }
    }
    return ($EditedMessage);
}

/* function */
function delQuotes($string)
{
    /* no recursive function to add quote to an HTML tag if needed */
    /* and delete duplicate spaces between attribs. */
    $tmp = "";    # string buffer
    $result = ""; # result string
    $i = 0;
    $attrib = -1; # Are us in an HTML attrib ?   -1: no attrib   0: name of the attrib   1: value of the atrib
    $quote = 0;   # Is a string quote delimited opened ? 0=no, 1=yes
    $len = strlen($string);
    while ($i < $len) {
        switch ($string[$i]) { # What car is it in the buffer ?
            case "\"": #"       # a quote.
                if ($quote == 0) {
                    $quote = 1;
                } else {
                    $quote = 0;
                    if (($attrib > 0) && ($tmp != "")) {
                        $result .= "=\"$tmp\"";
                    }
                    $tmp = "";
                    $attrib = -1;
                }
                break;
            case "=":           # an equal - attrib delimiter
                if ($quote == 0) {  # Is it found in a string ?
                    $attrib = 1;
                    if ($tmp != "") $result .= " $tmp";
                    $tmp = "";
                } else $tmp .= '=';
                break;
            case " ":           # a blank ?
                if ($attrib > 0) {  # add it to the string, if one opened.
                    $tmp .= $string[$i];
                }
                break;
            default:            # Other
                if ($attrib < 0)    # If we weren't in an attrib, set attrib to 0
                    $attrib = 0;
                $tmp .= $string[$i];
                break;
        }
        $i++;
    }
    if (($quote != 0) && ($tmp != "")) {
        if ($attrib == 1) $result .= "=";
        /* If it is the value of an atrib, add the '=' */
        $result .= "\"$tmp\"";  /* Add quote if needed (the reason of the function ;-) */
    }
    return $result;
}

/* function */
function filter_text($Message, $strip = "")
{
    global $EditedMessage;
    check_words($Message);
    //$EditedMessage=check_html($EditedMessage, $strip);
    return ($EditedMessage);
}

/* function */
function myfilter($what, $strip = "", $save = "", $type = "")
{
    if ($strip == "nohtml") {
        $what = strip_tags($what);
    }

    if ($save == 1) {
        $what = check_words($what);
        //$what = check_html($what, $strip);
        $what = stripslashes($what);
        //$what = addslashes($what);
        $what = str_replace("'", "''", $what);
    } else {
        $what = stripslashes(FixQuotes($what));
        $what = check_words($what);
        //$what = check_html($what, $strip);
    }
    return ($what);
}

/* function */
function formatTimestamp($time)
{
    global $datetime, $locale;
    setlocale(LC_TIME, $locale);
    preg_match("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $datetime);
    $datetime = strftime("" . _DATESTRING . "", mktime($datetime[4], $datetime[5], $datetime[6], $datetime[2], $datetime[3], $datetime[1]));
    $datetime = ucfirst($datetime);
    return ($datetime);
}

/* function */
function removecrlf($str)
{
    return strtr($str, "\015\012", ' ');
}

/* function */
function hex2rgb($hex)
{
    $hex = str_replace("#", "", $hex);

    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }

    return $r . "," . $g . "," . $b;

    //$rgb = array($r, $g, $b);
    //return implode(",", $rgb); // returns the rgb values separated by commas
    //return $rgb; // returns an array with the rgb values
}

/* function */
function myUndoId($myPhrase)
{
    global $db;
    $undoId = md5(date("YmdHis"));
    $phrase = myfilter($myPhrase, "", "1");
    $kayit = $db->sql_query("INSERT INTO " . _UNDOS_ . " (`undoId`, `phrase`) VALUES ('" . $undoId . "', '" . $phrase . "')");
    if ($kayit) return $undoId;
    else return 0;
}

/* function */
function HomeWorkDueDate($date, $format = "")
{
    $todayDate = date("Y-m-d");

    $datetime = new DateTime('tomorrow');
    $tomorrowDate = $datetime->format("Y-m-d");

    if ($date == $todayDate) return "<span class='text-red'>" . _BUGUN . "</span>";
    elseif ($date == $tomorrowDate) return "<span class='text-red'>" . _YARIN . "</span>";
    else return ($format == "") ? FormatDate2Local($date) : FormatDateNumeric2Local($date);
}

/* function */
function HomeworkStatusClass($status = "done")
{
    if ($status == "done") return "btn-success";
    else if ($status == "partly_done") return "btn-primary";
    else if ($status == "undone") return "btn-danger";
    else if ($status == "overdue") return "btn-default";
    else return "btn-warning";
}

/* function */
function HomeworkStatusTitle($status = "done", $default = _HOMEWORK_STATUS)
{
    if ($default == _HOMEWORK_STATUS) {
        if ($status == "done") return _HOMEWORK_STATUS_DONE;
        else if ($status == "partly_done") return _HOMEWORK_STATUS_PARTLY_DONE;
        else if ($status == "undone") return _HOMEWORK_STATUS_UNDONE;
        else if ($status == "overdue") return _HOMEWORK_STATUS_OVERDUE;
        else return $default;
    } else if ($default == _READING_STATUS) {
        if ($status == "done") return _READING_STATUS_DONE;
        else if ($status == "partly_done") return _READING_STATUS_PARTLY_DONE;
        else if ($status == "undone") return _READING_STATUS_UNDONE;
        else if ($status == "overdue") return _READING_STATUS_OVERDUE;
        else return $default;
    }
}

/* function */
function makeFSConfigFile($file)
{
    global $db, $dbname2, $ySubeKodu, $seasonYear, $aid;

    //get file name
    $file = str_replace(array("../", $seasonYear, $ySubeKodu), array("", "{seasonYear}", "{schoolID}"), $file);

    //config variables
    $configVariables = array("delete_files", "create_folders", "delete_folders", "upload_files", "rename_files", "rename_folders", "duplicate_files", "copy_cut_files", "copy_cut_dirs", "chmod_files", "chmod_dirs", "preview_text_files", "edit_text_files", "create_text_files");

    //get config
    $getConfig = $db->sql_fetchrow($db->sql_query("SELECT * FROM " . _FILE_SYSTEM_ . " WHERE `folder`='" . $file . "'"));
    if ($getConfig["configFile"] == "on") {
        //start config gile
        $config_file = htmlentities("<?\n\n");

        //add folder message
        $folderMessage = $getConfig["folder_message"];
        if ($folderMessage != "") {
            $folderMessage = str_replace(array("{SchoolName}", "{SeasonYear}"), array(BranchName($ySubeKodu), fnSeasonName($dbname2)), $folderMessage);
            $config_file .= htmlentities("$") . "folder_message = \"" . htmlentities($folderMessage) . htmlentities("\";\n\n");
        }

        //add permission for variables
        foreach ($configVariables as $key => $value) {
            $config_file .= htmlentities("$") . $value . " = ";
            $config_file .= ($getConfig[$value] == "on") ? "true" : "false";
            $config_file .= ";" . htmlentities("\n");
        }

        //finish config file
        $config_file .= htmlentities("\n?>");

        return $config_file;
    } else {
        return "";
    }
}

/* function */
function fnUserId2OtherId($id, $otherId = "personnel")
{
    global $db;

    if ($otherId == "personnel") {
        $row = $db->sql_fetchrow($db->sql_query("SELECT `perID` AS `otherId` FROM " . _PERSONEL_ . " WHERE `tckimlikno`='" . $id . "'"));
    } else if ($otherId == "student") {
        $row = $db->sql_fetchrow($db->sql_query("SELECT `ogrID` AS `otherId` FROM " . _OGRENCILER_ . " WHERE `TCKimlikNo`='" . $id . "'"));
    } else if ($otherId == "parent") {
        $row = $db->sql_fetchrow($db->sql_query("SELECT `vID` AS `otherId` FROM " . _VELILER_ . " WHERE `v_tc_kimlik_no`='" . $id . "'"));
    }

    return $row["otherId"];
}

/* function */
//$userId can be either id field or aid field
function fnUserId2UserInfo($id, $usrInfo = "name,lastName", $asArray = false)
{
    global $dbi;

    $user = $dbi->where("aid", $id)->orWhere("id", $id)->getOne(_USERS_, $usrInfo);

    return (sizeof($user) == 1) ? $user[$usrInfo] : ($asArray ? $user : implode(" ", $user));
}

/* function */
function fnStdId2StdInfo($stdId, $stdInfo = "ogrenciNo")
{

    global $dbi;

    $student = $dbi->where("ogrID", $stdId)->getOne(_OGRENCILER_, $stdInfo);

    return (!empty($student) && sizeof($student) == 1) ? $student[$stdInfo] : $student;
}

/* function */
function fnPerId2PerInfo($perID, $perInfo = "tckimlikno")
{

    global $dbi;

    $personnel = $dbi->where("perID", $perID)->getOne(_PERSONEL_, $perInfo);

    return (!empty($personnel) && sizeof($personnel) == 1) ? $personnel[$perInfo] : $personnel;
}

/* function */
function fnParentId2ParentInfo($pID, $pInfo = "v_tc_kimlik_no")
{

    global $dbi;

    //$parent = $dbi->where("vID", $pID)->getValue(_VELILER_,  $pInfo);
    $parent = $dbi->where("vID", $pID)->getOne(_VELILER_, $pInfo);

    return (!empty($parent) && sizeof($parent) == 1) ? $parent[$pInfo] : $parent;
}

/* function */
function fnParentSSN2ParentInfo($pSSN, $pInfo = "adi_soyadi")
{
    global $dbi;

    return $dbi->where("v_tc_kimlik_no", $pSSN)->getValue(_VELILER_, $pInfo);
}

function mySQLQueryData($theValue, $theType = "yazi", $theDefinedValue = "", $theNotDefinedValue = "")
{
    if (PHP_VERSION < 6) {
        $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
    }

    $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

    switch ($theType) {
        case "yazi":
            $theValue = ($theValue != "") ? "'" . myfilter($theValue, "", "1") . "'" : "NULL";
            break;
        case "tirnaksiz" :
            $theValue = ($theValue != "") ? $theValue : "NULL";
            break;
        case "hok":
            $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
            $theValue = htmlspecialchars($theValue);
            break;
        case "uzun":
        case "sayi":
            $theValue = ($theValue != "") ? intval($theValue) : "NULL";
            break;
        case "ondalik":
            $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
            break;
        case "tarih":
            $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
            break;
        case "tanimsiz":
            $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
            break;
    }
    return $theValue;
}

function mySQLQueryCreate($tablo, $bilgiler)
{
    global $html_istisna;
    $toplamAlan = "";
    $toplamVeri = "";
    foreach ($bilgiler as $anahtar => $deger) { //anahtar değerleri alalım
        $toplamAlan .= "`" . $anahtar . "`" . ",";
    }


    foreach ($bilgiler as $anahtar => $deger) { // her anahtara ait değerleri alalım
        if (is_string($deger)) {

            $deger = mySQLQueryData($deger);

        }
        $toplamVeri .= $deger . ",";
    }
    $toplamAlan = substr($toplamAlan, 0, strlen($toplamAlan) - 1);
    $toplamVeri = substr($toplamVeri, 0, strlen($toplamVeri) - 1);

    $ekleSQL = sprintf("INSERT INTO %s (%s) VALUES (%s)", $tablo, $toplamAlan, $toplamVeri);
    return $ekleSQL;

}

function mySQLQueryUpdate($tablo, $set, $where)
{
    global $html_istisna;
    $toplamSet = "";
    $toplamWhere = "";
    $deger = "";
    $k = 0;
    foreach ($set as $anahtar => $deger) {
        if (is_string($deger)) {
            $deger = mySQLQueryData($deger);
        }
        $toplamSet .= $anahtar . "=" . $deger . ",";
    }
    if (is_array($where)) {
        foreach ($where as $anahtar => $deger) {
            if (is_string($deger)) {
                if (!array_key_exists($anahtar, $html_istisna)) {
                    $deger = strip_tags($deger);
                }
                $deger = mySQLQueryData($deger);
            }
            $toplamWhere .= $anahtar . "=" . $deger . " AND ";
        }
        $toplamWhere = substr($toplamWhere, 0, strlen($toplamWhere) - 5);
    } else {
        $toplamWhere = $where;
    }
    $toplamSet = substr($toplamSet, 0, strlen($toplamSet) - 1);
    $guncelleSQL = sprintf("UPDATE %s SET %s WHERE %s",
        $tablo,
        $toplamSet,
        $toplamWhere);
    return $guncelleSQL;
}

function mySQLQueryDelete($tablo, $kriter)
{
    $silSQL = sprintf("DELETE FROM %s WHERE %s",
        $tablo,
        $kriter);
    return $silSQL;

}

/* function */
function setSMSText($text)
{
    $text = str_replace(array("&#304;", "\u0130", "\xDD", "İ"), "I", $text);
    $text = str_replace(array("&#305;", "\u0131", "\xFD", "ı"), "i", $text);
    $text = str_replace(array("&#286;", "\u011e", "\xD0", "Ğ"), "G", $text);
    $text = str_replace(array("&#287;", "\u011f", "\xF0", "ğ"), "g", $text);
    $text = str_replace(array("&Uuml;", "\u00dc", "\xDC", "U"), "U", $text);
    $text = str_replace(array("&uuml;", "\u00fc", "\xFC", "ü"), "u", $text);
    $text = str_replace(array("&#350;", "\u015e", "\xDE", "Ş"), "S", $text);
    $text = str_replace(array("&#351;", "\u015f", "\xFE", "ş"), "s", $text);
    $text = str_replace(array("&Ouml;", "\u00d6", "\xD6", "Ö"), "O", $text);
    $text = str_replace(array("&ouml;", "\u00f6", "\xF6", "ö"), "o", $text);
    $text = str_replace(array("&Ccedil;", "\u00c7", "\xC7", "Ç"), "C", $text);
    $text = str_replace(array("&ccedil;", "\u00e7", "\xE7", "ç"), "c", $text);

    return $text;
}

/* function */
/**
 * Returns an authorized server-to-server connection
 */
function getGoogleServer2Server()
{
    global $aid;

    $setCredential = putenv('GOOGLE_APPLICATION_CREDENTIALS=settings/keys/google/SmartClass-a397fa3b3ec3.json');

    if (!$setCredential) return false;

    $client = new Google_Client();
    //$client->addScope("https://www.googleapis.com/auth/books");
    $client->addScope("https://www.googleapis.com/auth/urlshortener");
    //$client->addScope("https://www.googleapis.com/auth/calendar");
    //$client->addScope("https://www.googleapis.com/auth/youtube");

    $client->useApplicationDefaultCredentials();

    return $client;
}

/* function */
/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getGoogleClient()
{
    global $aid, $simsInstanceUrl;

    //set redirect uri
    $redirect_uri = $simsInstanceUrl . "/public/v1/googleauthorize";

    //google credentials file
    $credentialsPath = $_SERVER["DOCUMENT_ROOT"] . "/credentials/" . $aid . "/google.json";

    // oauth2 credentials
    $oauth_credentials = 'settings/keys/google/oauth-credentials.json';

    $client = new Google_Client();
    $client->setApplicationName("SmartClass");
    $client->setAuthConfig($oauth_credentials);
    $client->setRedirectUri($redirect_uri);
    $client->setAccessType("offline");
    $client->setApprovalPrompt('force');
    $client->addScope("https://www.googleapis.com/auth/userinfo.email");
    $client->addScope("https://www.googleapis.com/auth/userinfo.profile");
    $client->addScope("https://mail.google.com");
    $client->addScope("https://www.googleapis.com/auth/calendar");
    $client->addScope("https://www.googleapis.com/auth/drive");

    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);

        $client->setAccessToken($accessToken);

        return $client;
    } else {
        if (isset($_GET['code'])) {
            $authCode = $_GET['code'];

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            // Store the credentials to disk.
            if (!file_exists(dirname($credentialsPath))) {
                mkdir(dirname($credentialsPath), 0755, true);
            }

            $putcred = file_put_contents($credentialsPath, json_encode($accessToken));

            if (!$putcred) return false;
            else {
                $client->setAccessToken($accessToken);

                return $client;
            }
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();

            header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));

            return false;
        }
    }

    /*
	// Refresh the token if it's expired.
	if ($client->isAccessTokenExpired())
	{
    	$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    	$putcred = file_put_contents($credentialsPath, json_encode($client->getAccessToken()));

    	if(!$putcred) return false;
	}
	else
	{
		$client->setAccessToken($accessToken);
	}
	*/

}

/* function */
function getIMAPAccount()
{
    global $db, $aid, $attachments_folder;

    require_once "class/EMail/imap.php";

    $userInfo = $db->sql_fetchrow($db->sql_query("SELECT * FROM " . _ACCOUNTS_ . " WHERE `accountType`='Custom' AND `userAid`='" . $aid . "'"));
    $userEmail = $userInfo["emailAddress"];
    $userPassword = $userInfo["emailPassword"];
    $incomingServerUrl = $userInfo["incomingServerUrl"];
    $incomingConnType = $userInfo["incomingConnType"];
    $incomingConnPort = $userInfo["incomingConnPort"];
    $incomingConnSecure = $userInfo["incomingConnSecure"];

    if ($incomingConnType == "IMAP") {
        $connectionString = ($incomingConnSecure == "SSL") ? '{' . $incomingServerUrl . ':' . $incomingConnPort . '/imap/ssl}INBOX' : '{' . $incomingServerUrl . ':' . $incomingConnPort . '}INBOX';

        $mailbox = new ImapMailbox($connectionString, $userEmail, $userPassword, $attachments_folder, 'utf-8');

        if (!$mailbox) return false;
    } else if ($incomingConnType == "POP3") {
        $connectionString = ($incomingConnSecure == "SSL") ? '{' . $incomingServerUrl . ':' . $incomingConnPort . '/pop3/ssl/novalidate-cert}INBOX' : '{' . $incomingServerUrl . ':' . $incomingConnPort . '/pop3}INBOX';

        $mailbox = new ImapMailbox($connectionString, $userEmail, $userPassword, $attachments_folder, 'utf-8');

        if (!$mailbox) return false;
    }

    return $mailbox;
}

/* web servis key should be included in $data as wskey => SMARTCLASS_SECRET_PHRASE */
function makeWebServicesKey($data = array())
{
    //sort data
    array_keys(sort($data));

    //make string from data array and SmartClass secret phrase joining by ::
    $dataString = implode("::", $data) . "::" . $op . "::" . SMARTCLASS_SECRET_PHRASE;

    //make sha of the string
    $key = sha1($dataString);

    //return
    return $key;
}

function checkWebServicesKey($data = array())
{
    //check if wskey exists
    if (array_key_exists("wskey", $data)) {
        $wskey = $data["wskey"];

        //remove wskey
        unset($data["wskey"]);

        //sort data
        array_keys(sort($data));

        //make string from data array and SmartClass secret phrase joining by ::
        $dataString = implode("::", $data) . "::" . $op . "::" . SMARTCLASS_SECRET_PHRASE;

        //make sha of the string
        $key = sha1($dataString);

        //return
        return ($wskey == $key) ? true : false;
    } else {
        return false;
    }
}

/* function */
function timeAgoCalculate($timestamp)
{
    $timeAgo = strtotime($timestamp);
    $currentTime = time();
    $timeDifference = $currentTime - $timeAgo;

    $seconds = $timeDifference;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);

    if ($seconds <= 60) {
        return _JUST_NOW;
    } else if ($minutes <= 60) {

        if ($minutes == 1) return $minutes . " " . _MINUTE_AGO;
        else return $minutes . " " . _MINUTES_AGO;

    } else if ($hours <= 24) {

        if ($hours == 1) return $hours . " " . _HOUR_AGO;
        else return $hours . " " . _HOURS_AGO;

    } else if ($days <= 7) {

        if ($days == 1) return _YESTERDAY;
        else return $days . " " . _DAYS_AGO;

    } else if ($weeks <= 4.3) {

        if ($weeks == 1) return _WEEK_AGO;
        else return $weeks . " " . _WEEKS_AGO;

    } else if ($months <= 12) {

        if ($months == 1) return _MONTH_AGO;
        else return $months . " " . _MONTHS_AGO;

    } else {

        if ($years == 1) return _YEAR_AGO;
        else return $years . " " . _YEARS_AGO;
    }
}

/* function */
/* this function gets calendar type title by getting calendar Id
   example: fnCalendarType("googlecalendar") -> _GOOGLE_CALENDAR
*/
function fnCalendarType($calendarId)
{

    switch ($calendarId) {
        case "webcalendar":
            return _WEB_CALENDAR;
            break;

        case "googlecalendar":
            return _GOOGLE_CALENDAR;
            break;

        case "holidayscalendar":
            return _HOLIDAYS_CALENDAR;
            break;

        case "customcalendar":
        default:
            return _CUSTOM_CALENDAR;
            break;
    }
}

/* function */
/* this function gets post events and show them in a designed pattern */
function getPosts($postsLists)
{

    global $dbi, $aid, $fileFolder, $currentlang;

    foreach ($postsLists as $postList) {
        $classes = array();
        $classes = explode(",", $postList["classes"]);

        //get like count of post

        $likes = $dbi->where("postId", $postList["Id"])->get(_SOCIAL_LIKES_);
        $likeCount = $dbi->count;

        $nameSurnames = array();

        foreach ($likes as $like) $nameSurnames[] = YoneticiAdi($like["userId"]);

        $users = implode(',', $nameSurnames);

        // get comments in social_comments table with postId condition
        $comments = $dbi->where("postId", $postList["Id"])->get(_SOCIAL_COMMENTS_, null, array("userId", "createdAt", "comment", "Id"));
        $commentCount = $dbi->count;

        //get photos of post
        $photos = $dbi->where("postId", $postList["Id"])->get(_SOCIAL_PHOTOS_); //simdilik tek resim olduğu için get one kullandım.

        //get documents of post
        $documents = $dbi->where("postId", $postList["Id"])->get(_SOCIAL_DOCUMENTS_);

        // get my like counts
        $myLikeCount = $dbi->where("postId", $postList["Id"])->where("userId", $aid)->getOne(_SOCIAL_LIKES_, "count(*) as cnt");

        // if I liked the post, this condition works
        if ($myLikeCount["cnt"] > 0) $likeClass = "btn-success";
        else $likeClass = "btn-default";

        $fileURL = $fileFolder . "/images/" . $currentlang . "/";

        $html = '<div class="panel-white post">

                    <div class="post-heading">

                        <div class="pull-left image">
                            <img src="' . UserPicture($postList["userId"]) . '" class="avatar" />
                        </div>
                        <div class="pull-left meta">
                            <div class="title">
                               <a href="javascript:void(0)"><b>' . YoneticiAdi($postList["userId"]) . '</b></a>
                            </div>
                            <h6 class="text-muted time">' . timeAgoCalculate($postList["createdAt"]) . '</h6>
                        </div>';

        if ($postList["userId"] == $aid) {
            $html .= '
                            <div class="pull-right meta">
                                <button href="index.php?op=posts&action=editPost&nohelp=1&Id=' . $postList["Id"] . '" class="btn btn-xs btn-warning" style="margin-right:5px" data-toggle="modal" data-target="#myModal"><i class="fa fa-pencil"></i></button>
                                <button type="button" class="btn btn-xs btn-danger simsdel" href="index.php?op=iudPost&action=deletePost&nohelp=1&Id=' . $postList["Id"] . '"><i class="fa fa-trash"></i></a></button>
                            </div>';
        }

        $html .= '</div>';

        if ($photos) {
            $html .= '<div class="photos">';

            foreach ($photos as $photo) {
                $html .= '<a data-toggle="modal" data-target="#mySubModal" href="index.php?op=showPicture&picture=' . $photo["path"] . '"><img src="' . $photo["path"] . '" class="img-rounded"></img></a>';
            }

            $html .= '</div>';
        }

        if ($documents) {

            $html .= '<div class="photos" style="width:100%;">';

            foreach ($documents as $document) {

                $pathArray = explode("/", $document["path"]);

                $documentTitle = $pathArray[count($pathArray) - 1];

                $html .= '<div class="pad" style="height: 70px;width:80px;float:left" data-toggle="tooltip" data-html="true" data-placement="top" data-container="body" title="' . $documentTitle . '">
										  			<a href="' . $document["path"] . '"  target="_blank"><i class="fa fa-3x fa-file-text-o"></i></a>
												</div>';
            }
            $html .= '<div style="clear:both"></div></div>';

        }

        $html .= '<div class="post-description">
                          <p>' . $postList["message"] . '</p>';

        if ($postList["url"] != "") {

            $html .= '<div class="url">
                                 <div class="pull-left image">
                                   <img src="' . $fileURL . $postList["urlThumbnail"] . '" class="avatar" />
                                 </div>
                                 <div class="pull-left meta">
                                    <div class="title">
                                        <a href="' . $postList["url"] . '" target="_blank"><b>' . $postList["urlTitle"] . '</b></a>
                                    </div>
                                    <h6 class="text-muted time">' . $postList["urlDescription"] . '</h6>
                                 </div>
                                 <div class="clear"></div>
                              </div>';
        }

        $html .= '<div class="actions">
                              <a href="javascript:void(0)" data-toggle="tooltip" data-html="true" data-placement="top" title="' . $users . '" class="btn btn-default like ' . $likeClass . '" id="like' . $postList["Id"] . '" data-post-id="' . $postList["Id"] . '">
                                  <i class="fa fa-thumbs-o-up icon"></i>' . $likeCount . ' ' . _LIKE . '
                              </a>
                              <a href="javascript:void(0)" class="showComments btn btn-default comment" id="comment' . $postList["Id"] . '" data-id="' . $postList["Id"] . '">
                                  <i class="fa fa-commenting-o icon"></i> ' . $commentCount . ' ' . _COMMENT . '
                              </a>';

        if ($postList["students"] == "") {
            foreach ($classes as $class) {

                $dbi->where("sinifId", $class);
                $classDetails = $dbi->getOne(_BATCHES_);

                $html .= '<a href="javascript:void(0)" class="btn btn-default comment">
									<i class="fa fa-bullhorn"></i> ' . $classDetails["sinifAdi"] . ' </a>';
            }

        }

        $html .= '</div>
                    </div>
                    <div class="post-footer" id="comments' . $postList["Id"] . '" style="display:none">
                         <div class="input-group">
                              <input class="form-control commentInput comment' . $postList["Id"] . '" data-post-id="' . $postList["Id"] . '" placeholder="' . _ADD_COMMENT . '" type="text">
                              <span class="input-group-addon">
                                  <a href="javascript:void(0)"><i class="fa fa-edit fa-edit-comment" data-post-id="' . $postList["Id"] . '"></i></a>
                              </span>
                          </div>
                          <ul class="comments-list">';

        foreach ($comments as $comment) {
            $html .= '<li class="comment">
                                  <a class="pull-left" href="#">
                                      <img class="avatar" src="' . UserPicture($comment["userId"]) . '" alt="avatar">
                                  </a>';

            if ($comment["userId"] == $aid) {
                $html .= '
                                        <div class="pull-right meta">
                                            <div class="btn-group">
                                               <button type="button" class="btn btn-xs btn-danger sims-delete-comment" href="index.php?op=iudPost&action=deleteComment&nohelp=1&Id=' . $comment["Id"] . '"><i class="fa fa-trash"></i></a></button>
                                            </div>
                                        </div>';
            }
            $html .= '<div class="comment-body">
                                      <div class="comment-heading">
                                          <h4 class="user">' . YoneticiAdi($comment["userId"]) . '</h4>
                                          <h5 class="time">' . timeAgoCalculate($comment["createdAt"]) . '</h5>
                                      </div>
                                      <p>' . $comment["comment"] . '</p>
                                  </div>
                              </li>';
        }

        $html .= '</ul>
                    </div>
            </div>';
        echo $html;
    }
}

/* function */
function fnGuidanceSubject($sId)
{
    global $dbi;
    $row = $dbi->where("id", $sId)->getOne(_GUIDANCE_DEFINITIONS_);
    return $row["type"];
}

/* function */
function InterviewType($Id, $format = "text")
{
    global $dbi;
    $row = $dbi->where("Id", $Id)->getOne(_PARENT_INTERVIEW_TYPES_);

    if ($format == "text") return translateWord($row["type"]);
    else if ($format == "icon-text") return "<i class='" . $row["icon"] . "'></i> " . translateWord($row["type"]);
    else if ($format == "icon") return "<i class='" . $row["icon"] . "' data-toggle='tooltip' data-html='true' data-placement='top' title='" . translateWord($row["type"]) . "'></i>";
}

/* function */
function currentSeasonInfo()
{
    global $dbi, $simsDate;

    //get current term info
    $termInfo = $dbi->where("startDate", $simsDate, "<=")->where("endDate", $simsDate, ">=")->getOne(_GRADING_TERMS_);

    return $termInfo;
}


/* function */
function reservationTitle($prId)
{
    global $dbi;

    //$dbi->join(classes);
    $scheduleInfo = $dbi->where("pr_id", $prId)->getOne(_SCHEDULE_);

    return ScheduleLabelType($scheduleInfo["ders_turu_code"]);
}

/* function */
function tempStudentNumber4Exam($schoolId = "", $dbName = "")
{
    global $dbi, $ySubeKodu, $dbname2;

    if (empty($schoolId)) $schoolId = $ySubeKodu;

    if (empty($dbName)) $examTempStudentNumberTable = $dbname2 . ".sinav_gecici_ogrenci_no";
    else $examTempStudentNumberTable = $dbName . ".sinav_gecici_ogrenci_no";

    $tempNo = $dbi->getValue($examTempStudentNumberTable, "MAX(ogrID)");

    if ($tempNo < 100000) $tempNo = 100000; //temp should be greater than 100.000
    if ($tempNo < 999999) $tempNo++; //temp should be less than 999.999
    if ($tempNo == 999999) $tempNo = 100000; //temp should not be greater than 999.999

    $insert = $dbi->insert($examTempStudentNumberTable, array("ogrID" => $tempNo, "subeKodu" => $schoolId));

    if ($insert) return $tempNo;
    else return false;
}

/* function */
/* table parameter should be with database name */
function fnDatabaseColumnExists($table, $column)
{
    global $dbi;

    $exists = false;
    //$columns = @mysql_query("SHOW COLUMNS FROM ". $table);
    $columns = $dbi->rawQuery("SHOW COLUMNS FROM " . $table);
    foreach ($columns as $column) {
        if ($column["Field"] == $column) {
            $exists = true;
            break;
        }
    }

    return $exists;
}

/* function */
/* function to check if a remote url exists or not */
function simsIsUrlExists($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($code == 200) $status = true;
    else $status = false;

    curl_close($ch);

    return $status;
}

function fileUploadViaCurl($uploadDir, $uploadFile, $randomize = false, $createFolder = true)
{
    global $localeCode, $configuration, $currentlang, $ySubeKodu, $encryptedPhrase;

    $phpver = phpversion();

    if (version_compare(PHP_VERSION, '7.0.0', '<')) {
        //upload parameters
        $curlParams = array(
            "id" => "file",
            "file" => "@" . $uploadFile,
            "uploadDir" => $uploadDir,
            "randomize" => $randomize,
            "locale" => $localeCode,
            "simsUploadPhrase" => $encryptedPhrase,
            "lang" => $currentlang,
            "createFolder" => $createFolder
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $configuration["fileServerUrl"] . "/index.php");
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlParams);

        curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);

        $result = curl_exec($curl);
        curl_close($curl);
    } else {
        $simsFile = curl_file_create($uploadFile);

        //upload parameters
        $curlParams = array(
            "id" => "file",
            "file" => $simsFile,
            "uploadDir" => $uploadDir,
            "randomize" => $randomize,
            "locale" => $localeCode,
            "simsUploadPhrase" => $encryptedPhrase,
            "lang" => $currentlang,
            "createFolder" => $createFolder
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $configuration["fileServerUrl"] . "/index.php");
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlParams);

        $result = curl_exec($curl);
        curl_close($curl);
    }

    return $result;
}

/* function */
function array_in_select($array = array(), $select_key, $select_value, $operator = '==')
{

    return array_filter($array, function ($e) use ($select_key, $select_value, $operator) {

        switch ($operator) {
            case '=='   :
                return $e[$select_key] == $select_value;
                break;
            case '==='  :
                return $e[$select_key] === $select_value;
                break;
            case '!='   :
                return $e[$select_key] != $select_value;
                break;
            case '!=='  :
                return $e[$select_key] !== $select_value;
                break;
            case '<>'   :
                return $e[$select_key] <> $select_value;
                break;
            case '>'    :
                return $e[$select_key] > $select_value;
                break;
            case '<'    :
                return $e[$select_key] < $select_value;
                break;
            case '<='   :
                return $e[$select_key] <= $select_value;
                break;
            case '>='   :
                return $e[$select_key] >= $select_value;
                break;
        }
    });
}

/* function */
function array_in_find($array = array(), $search_text)
{

    return array_filter($array, function ($var) use ($search_text) {
        return preg_match("/\b$search_text\b/i", $var);
    });
}

/* function */
/* this function is written to replace array_column fn as array_column is only supported for php>=5.5 */
function mbp_array_column(array $input, $column_key, $index_key = null)
{
    $result = array();
    foreach ($input as $k => $v)
        $result[$index_key ? $v[$index_key] : $k] = $v[$column_key];
    return $result;
}

/* function */
function fnLanguageName($lang, $icon = false, $iconOnly = false)
{
    global $dbi;

    $dbi->where("language", $lang);
    $language = $dbi->getOne(_AVAILABLE_LANGUAGES_);

    $languageName = translateWord($language["langName"]);

    //send only icon
    if ($iconOnly) return '<img src="' . $language["flag"] . '" style="width:16px; height:16px;" data-toggle="tooltip" data-html="true" data-placement="top" title="' . $languageName . '">';

    //send icon and text
    else if ($icon) return '<img src="' . $language["flag"] . '" style="width:16px; height:16px;">	<span>' . $languageName . '</span>';

    //send just text
    else return '<span>' . $languageName . '</span>';
}

/* function */
function makeNewObjectiveCode($subjectId, $objectiveId)
{
    global $dbi;

    if (empty($subjectId) || empty($objectiveId)) return "0";

    $length = strlen($subjectId) + strlen($objectiveId);

    if ($length >= 8) $objectiveCode = $subjectId . $objectiveId;
    else if ($length = 7) $objectiveCode = $subjectId . "0" . $objectiveId;
    else if ($length = 6) $objectiveCode = $subjectId . "00" . $objectiveId;
    else if ($length = 5) $objectiveCode = $subjectId . "000" . $objectiveId;
    else if ($length = 4) $objectiveCode = $subjectId . "0000" . $objectiveId;
    else if ($length = 3) $objectiveCode = $subjectId . "00000" . $objectiveId;
    else if ($length = 2) $objectiveCode = $subjectId . "000000" . $objectiveId;

    return $objectiveCode;
}

/* function */
function simsDashboardBox($boxId, $title, $titleIcon = "", $urlTitle = "", $urlIcon = "", $url = "", $chartType = "", $disableCloseButton = false, $initialContent = "")
{
    global $globalUserTypeClass, $globalUserTypeBgClass;

    $content = '<div id="' . $boxId . '" class="box box-solid box-default box-small sims-dashboard-box">';
    $content .= '<div class="box-header with-border sims-widget-header">';
    $content .= '<h3 class="box-title sims-widget-title handle"><i class="fa ' . $titleIcon . '"></i> ' . $title . '</h3>';
    $content .= '<div class="pull-right box-tools">';
    if (!$disableCloseButton) $content .= '<button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>';
    $content .= '</div>';
    $content .= '</div>';

    $content .= '<div class="box-body" style="overflow: hidden; min-height: 64px">';
    if (empty($initialContent)) $content .= '<span class="h3 sims-box-content"></span>';
    else $content .= '<span class="h3 sims-box-content">' . $initialContent . '</span>';
    $content .= '<span class="sims-box-chart pull-right pad-top-5"></span>';
    $content .= '</div>';

    if (!empty($url)) {
        $content .= '<div class="box-footer text-right">';
        $content .= '<a href="#" data-link-type="sims-main-tab" data-menu-image="' . $urlIcon . '" data-menu-title="' . $urlTitle . '" data-menu-content="' . $url . '"><i class="fa fa-external-link"></i> ' . $urlTitle . '</a>';
        $content .= '</div>';
    }

    $content .= '</div>';

    return $content;
}

/* function */
function simsRandomColorPart()
{
    return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
}

/* function */
function simsRandomColor()
{
    return "#" . simsRandomColorPart() . simsRandomColorPart() . simsRandomColorPart();
}

/* function */
/*example for ajax usage
	$.ajax({
		beforeSend: function(){
			simsDivDashboardWidgets.find(".box-body").html('<?=simsLoading()?>');
		},
	});
*/

function simsLoading()
{
    global $dbi;

    //get random quotes
    $randomQuote = $dbi->orderBy("RAND()")->getValue(_RANDOM_QUOTES_, "quote");

    //loading
    $loading = '<p class="text-center text-bold margin" style="font-size: 20px;"><i class="fa fa-spin fa-spinner"></i> ' . _LOADING . '</p>';
    if (!empty($randomQuote)) $loading .= '<p class="text-center">' . _DID_YOU_KNOW_THAT . "<br><i>" . $randomQuote . '</i></p><br>';

    return $loading;
}

/* function */
function GradingPassRules($rule, $color = '', $textColor = '', $icon = '')
{
    $borderClass = empty($color) ? "box-success" : "";
    $borderStyle = !empty($color) ? "border:1px solid " . $color : '';
    $boxHeaderClass = empty($color) ? "box-header" : "";
    $boxHeaderBackgroundStyle = !empty($color) ? "background:" . $color . " !important;background-color:" . $color . " !important;" : "";
    $boxHeaderFontStyle = !empty($textColor) ? "color:" . $textColor : " !important;";
    ?>
    <div class="col-md-4">

        <div class="box <?= $borderClass ?> box-solid" style="<?= $borderStyle ?>">

            <div class="box-header with-border" style="<?= $boxHeaderBackgroundStyle . $boxHeaderFontStyle ?>">
                <h3 class="box-title"><i
                            class="fa <?= empty($icon) ? "fa-trophy" : "fa-" . $icon ?>"></i> <?= GradingPassStatus($rule["passStatus"]) ?>
                </h3>
            </div>

            <div class="box-body text-center">
                <p><?= $rule["ruleExpression"] ?></p>
            </div>

            <div class="box-footer text-center">
                <button class="btn btn-xs btn-warning" data-toggle="modal"
                        href="index.php?op=iudPassRule&action=editRule&Id=<?= $rule["Id"] ?>" data-target="#myModal"><i
                            class="fa fa-edit"></i> <?= _EDIT ?></button>
                <button class="btn btn-xs btn-danger btn-sims-delete"
                        data-url="index.php?op=iudPassRule&action=deleteRule&Id=<?= $rule["Id"] ?>"><i
                            class="fa fa-trash"></i> <?= _DELETE ?></button>
            </div>

        </div>

    </div>
    <?
}

/* function */
function GradingPassStatus($status)
{
    if ($status == "pass") return _PASS;
    else if ($status == "responsible") return _TRANSCRIPT_RESPONSIBLE;
    else if ($status == "repeat") return _TRANSCRIPT_REPEAT;
    else if ($status == "makeUpExam") return _MAKE_UP_EXAM;
    else if ($status == "fail") return _BASARISIZ;
}

/* function */
function mathAverage()
{

    $args = func_get_args();
    $nofArgs = func_num_args();

    $total = 0;
    foreach ($args as $arg) {
        $total += floatval($arg);
    }

    return ($nofArgs > 0) ? $total / $nofArgs : 0;
}

/* function */
function mathSum()
{

    $args = func_get_args();

    $total = 0;
    foreach ($args as $arg) {
        $total += floatval($arg);
    }

    return $total;
}

/* function */
/*
$tgx : grade of the term which has id x
$ag : annual grade
$bg : behavior grade
$rg : grade of responsible class exam
$nofar : number of annual responsible classes (for the current term)
$nofr : number of all responsible classes (including previous terms)
*/
function applyPassFailRules($termId, $tg1 = "", $tg2 = "", $tg3 = "", $tg4 = "", $ag = "", $bg = "", $rg = "", $nofar = "", $nofr = "", $courseId = "", $ruleId = "")
{
    global $dbi, $ySubeKodu;

    if (!empty($ruleId)) {
        $dbi->where("Id", $ruleId);
        $dbi->where("schoolId", $ySubeKodu);
        $ruleDetails = $dbi->getOne(_GRADING_PASSING_RULES_);

        if (sizeof($ruleDetails) == 0) return "";

        $result = "";

        //get rule expression
        $passRule = $ruleDetails["ruleExpression"];

        //check expression and grades
        if (strpos($passRule, "TG-1") !== false && $tg1 == "") return "";
        else if (strpos($passRule, "TG-2") !== false && $tg2 == "") return "";
        else if (strpos($passRule, "TG-3") !== false && $tg3 == "") return "";
        else if (strpos($passRule, "TG-4") !== false && $tg4 == "") return "";
        else if (strpos($passRule, "AG") !== false && $ag == "") return "";
        else if (strpos($passRule, "BG") !== false && $bg == "") return "";

        //replace grades
        $passRule = str_replace(array("TG-1", "TG-2", "TG-3", "TG-4", "AG", "BG"), array($tg1, $tg2, $tg3, $tg4, $ag, $bg), $passRule);
        $passRule = str_replace(array("SUM", "AVG"), array("mathSum", "mathAverage"), $passRule);

        //echo $passRule;
        $passRule = "if(" . $passRule . ") { \$result = '" . $ruleDetails["passStatus"] . "'; }";

        //run the code
        eval($passRule);

        if (!empty($result)) return $result;
    }

}

/* function */
/**
 * @param $url
 * @param array $options
 * @return string
 * @throws Exception
 */
function checkURL($url, array $options = array())
{

    if (empty($url)) return false;

    // list of HTTP status codes
    $httpStatusCodes = array(
        '100' => 'Continue',
        '101' => 'Switching Protocols',
        '102' => 'Processing',
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',
        '207' => 'Multi-Status',
        '208' => 'Already Reported',
        '226' => 'IM Used',
        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '306' => 'Switch Proxy',
        '307' => 'Temporary Redirect',
        '308' => 'Permanent Redirect',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Payload Too Large',
        '414' => 'Request-URI Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',
        '418' => 'I\'m a teapot',
        '422' => 'Unprocessable Entity',
        '423' => 'Locked',
        '424' => 'Failed Dependency',
        '425' => 'Unordered Collection',
        '426' => 'Upgrade Required',
        '428' => 'Precondition Required',
        '429' => 'Too Many Requests',
        '431' => 'Request Header Fields Too Large',
        '449' => 'Retry With',
        '450' => 'Blocked by Windows Parental Controls',
        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported',
        '506' => 'Variant Also Negotiates',
        '507' => 'Insufficient Storage',
        '508' => 'Loop Detected',
        '509' => 'Bandwidth Limit Exceeded',
        '510' => 'Not Extended',
        '511' => 'Network Authentication Required',
        '599' => 'Network Connect Timeout Error'
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    if (isset($options['timeout'])) {
        $timeout = (int)$options['timeout'];
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    }

    curl_exec($ch);
    $returnedStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (array_key_exists($returnedStatusCode, $httpStatusCodes)) return true;
    else return false;
}

/* function */
function infoUseBrowser($text)
{
    global $globalUserTypeClass;

    $template = '<div class="box box-' . $globalUserTypeClass . '">';
    $template .= '<div class="box-header with-border">';
    $template .= '<div class="box-body">';
    $template .= '<h4>' . $text . '</h4>';
    $template .= '</div>';
    $template .= '</div>';
    $template .= '</div>';

    return $template;
}

/* function */
function getBrowserInfo()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version = "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'Linux';
    } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'MacOS';
    } elseif (preg_match('/cros/i', $u_agent)) {
        $platform = 'ChromeOS';
    } elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'Windows';
    }

    // Next get the name of the useragent yes seperately and for good reason
    if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    } else if (preg_match('/Firefox/i', $u_agent)) {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    } else if (preg_match('/OPR/i', $u_agent)) {
        $bname = 'Opera';
        $ub = "Opera";
    } else if (preg_match('/Chrome/i', $u_agent)) {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    } else if (preg_match('/Safari/i', $u_agent)) {
        $bname = 'Apple Safari';
        $ub = "Safari";
    } else if (preg_match('/Netscape/i', $u_agent)) {
        $bname = 'Netscape';
        $ub = "Netscape";
    } else if (preg_match('/Mobile/i', $u_agent) && preg_match('/CriOS/i', $u_agent)) {
        $bname = 'Google Chrome';
        $ub = "Google Chrome";
    }

    if ($bname == 'Unknown' && $platform == 'MacOS') {
        $bname = 'SmartClass iOS App';
    } else if ($bname == 'Unknown' && $platform == 'Linux') {
        $bname = 'SmartClass Android App';
    }

    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }

    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
            $version = $matches['version'][0];
        } else {
            $version = $matches['version'][1];
        }
    } else {
        $version = $matches['version'][0];
    }

    // check if we have a number
    if ($version == null || $version == "") {
        $version = "?";
    }

    return array(
        'userAgent' => $u_agent,
        'browser' => $bname,
        'version' => $version,
        'platform' => $platform,
    );
}

/* function */
/**
 * Checks if a given IP address matches the specified CIDR subnet/s
 *
 * @param string $ip The IP address to check
 * @param mixed $cidrs The IP subnet (string) or subnets (array) in CIDR notation
 * @param string $match optional If provided, will contain the first matched IP subnet
 * @return boolean TRUE if the IP matches a given subnet or FALSE if it does not
 */
function ipMatch($ip, $cidrs, &$match = null)
{
    foreach ((array)$cidrs as $cidr) {
        list($subnet, $mask) = explode('/', $cidr);
        if (((ip2long($ip) & ($mask = ~((1 << (32 - $mask)) - 1))) == (ip2long($subnet) & $mask))) {
            $match = $cidr;
            return true;
        }
    }
    return false;
}

/* function */
function homeworkEvalType($eId)
{
    if ($eId == "status") return '<i class="fa fa-fw fa-check-square"></i><i class="fa fa-fw fa-check-square-o"></i><i class="fa fa-fw fa-square-o"></i><i class="fa fa-fw fa-clock-o"></i>';
    else if ($eId == "scale") return '5, 4, 3, 2, 1';
    else if ($eId == "symbol3") return '<i class="fa fa-fw fa-smile-o"></i><i class="fa fa-fw fa-meh-o"></i><i class="fa fa-fw fa-frown-o"></i>';
    else if ($eId == "symbol2") return '<i class="fa fa-fw fa-thumbs-up"></i><i class="fa fa-fw fa-thumbs-down"></i>';
    else if ($eId == "grade") return '[0-100]';
    else return '';
}

/* function */
function fnStudentGender($Id)
{
    if ($Id == "E") return _ERKEK;
    else if ($Id == "K") return _KIZ;
    else return _OTHER;
}

/* function */
function fnPersonnelGender($Id)
{
    if ($Id == "E") return _MALE;
    else if ($Id == "K") return _FEMALE;
    else if ($Id == "NULL") return _OTHER;
    else return "";
}

/* function */
function fnBloodType($Id)
{
    global $dbi;

    $bloodType = $dbi->where("Id", $Id)->getValue(_BLOOD_GROUPS_, "name");

    return $bloodType;
}

/* function */
function isIntegrationActive($Id)
{
    global $dbi, $ySubeKodu;

    if (empty($ySubeKodu)) $dbi->where("schoolId", "0");
    else $dbi->where("schoolId", array("0", "$ySubeKodu"), "IN");

    $active = $dbi->where("skey", $Id)->getValue(_INTEGRATIONS_, "active");

    return empty($active) ? false : true;
}

/* function */
function fnSchoolIdFromPartner($partnerId, $pSchoolId)
{
    global $dbi, $ySubeKodu;

    if ($partnerId == "vcloud") $scSchoolId = $dbi->where("vCloudCode", $pSchoolId)->getValue(_VCLOUD_SCHOOL_CODES_, "scSchoolId");

    else $scSchoolId = 0;

    return $scSchoolId;
}

/* function */
function fnLanguageLocale($language, $short = false)
{
    if ($language == "turkish") return $short ? "tr" : "tr_TR";
    else if ($language == "ottoman") return $short ? "tr" : "tr_TR";
    else if ($language == "english") return $short ? "en" : "en_EN";
    else if ($language == "spanish") return $short ? "es" : "es_ES";
    else if ($language == "german") return $short ? "de" : "de_DE";
    else if ($language == "arabic") return $short ? "ar" : "ar_AR";
    else if ($language == "french") return $short ? "fr" : "fr_FR";
    else if ($language == "russian") return $short ? "ru" : "ru_RU";
    else if ($language == "chinese") return $short ? "zh" : "zh_ZH";
    else return "en_EN";
}

/* function */
function EvalToolTitleAcronym($string)
{
    $output = "";
    $parts = explode(' ', $string);
    $partsLen = sizeof($parts);
    $last = $parts[$partsLen - 1];

    foreach ($parts as $k => $part) {
        if ($k == $partsLen - 1) continue;
        $output .= $part[0];
    }

    return $output . " " . $last;
}

/* function */
/**
 * Every time you call session_start(), PHP adds another
 * identical session cookie to the response header. Do this
 * enough times, and your response header becomes big enough
 * to choke the web server.
 *
 * This method clears out the duplicate session cookies. You can
 * call it after each time you've called session_start(), or call it
 * just before you send your headers.
 */
function clear_duplicate_cookies()
{
    // If headers have already been sent, there's nothing we can do
    if (headers_sent()) {
        return;
    }

    $cookies = array();
    foreach (headers_list() as $header) {
        // Identify cookie headers
        if (strpos($header, 'Set-Cookie:') === 0) {
            $cookies[] = $header;
        }
    }
    // Removes all cookie headers, including duplicates
    header_remove('Set-Cookie');

    // Restore one copy of each cookie
    foreach (array_unique($cookies) as $cookie) {
        header($cookie, false);
    }
}
