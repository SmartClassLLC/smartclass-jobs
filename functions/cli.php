<?php

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

/* function */
function RandomizedFileTitle($fileName)
{
	$t = explode("_", $fileName);
	return str_replace($t[0] . "_" . $t[1] . "_", "", $fileName);
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
/*
 * @mail_to can be array or single string
 * @mail_cc can be array or single string
 * @mail_bcc should be string
 * @mail_parameters should be array
 * 
 */
function sendEmail($mail_to, $subject, $message, $mail_cc = "", $mail_bcc = "", $mail_attachment = "", $log = true, $mail_from = "", $mail_from_name = "", $senderId = "", $schoolId = "0")
{
	global $dbi, $timeZone, $smtpSettingsTable, $emailLogsTable, $simsDateTime;

	//sleep for 0.1 sec for escaping to be assumed as spam
	usleep(100000);

	//get smtp info	
	$smtpRow = $dbi->orderBy("ID", "ASC")->getOne($smtpSettingsTable);
	
	$smtpHost = $smtpRow["smtp_host"];
	$smtpUsername = $smtpRow["smtp_username"];
	$smtpPassword = $smtpRow["smtp_password"];
	$smtpEmail = $smtpRow["smtp_email"];
	$smtpPort = $smtpRow["smtp_port"];
	
	date_default_timezone_set($timeZone);
	
	//add PHPMailer class auto loader
	include_once __DIR__ . "/../class/PHPMailer/PHPMailerAutoload.php";

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
	if(empty($mail_from_name)) $mail->setFrom($mail_from);
	else $mail->setFrom($mail_from, $mail_from_name);
	
	//set mail to addresses
	if(is_array($mail_to))
	{
		foreach ($mail_to as $key => $value) {
			$mail->addAddress($value);
		}
		
		$mail_to_str = implode(",", $mail_to);
	}
	else
	{
		$mail->addAddress($mail_to);
		$mail_to_str = $mail_to;
	}
	
	//set mail reply-to address
	//$mail->addReplyTo('no-reply@smartclass.biz');
	
	//set cc address
	if(is_array($mail_cc))
	{
		foreach ($mail_cc as $key => $value) {
            if (!empty($value)) {
                $mail->addCC($value);
            }
		}
		
		$mail_cc_str = implode(",", $mail_cc);
	}
	else if($mail_cc != '')
	{
		$mail->addCC($mail_cc);
		$mail_cc_str = $mail_cc;
	}
	else
	{
		$mail_cc_str = "";	
	}
	
	//set bcc address
	if($mail_bcc != '') $mail->addBCC($mail_bcc);
	
	//add smartclass emailing group to bcc
	//$mail->addBCC("smartclass-emailing@googlegroups.com");
	
	//set attachments
	if(is_array($mail_attachment))
	{
		foreach ($mail_attachment as $key => $attch)
		{
			$attchname = FilePath2FileName($attch);
			$attchname = RandomizedFileTitle($attchname);
			
			//$mail->addAttachment($attch, $name);
			$mail->addStringAttachment(file_get_contents($attch), $attchname);
		}
		
		$mail_attachment_str = serialize($mail_attachment);
	}
	else
	{
		$mail_attachment_str = "";
	}
	
	
	//Set email format to HTML
	$mail->isHTML(true);

	//set mail subject body and altbody
	$mail->Subject = $subject;
	$mail->Body    = $message;
	//$mail->AltBody = $message;

	//variables for log
	$emailSender = $senderId == "" ? "SmartClass" : $senderId;
	
	//query data for logs
	$queryData = array(
		"emailFrom"			=> $mail_from,
		"emailTo"			=> $mail_to_str,
		"emailCC"			=> $mail_cc_str,
		"emailSubject"		=> $subject,
		"emailContent"		=> $message,
		"emailAttachment"	=> $mail_attachment_str,
		"sentDateTime"		=> $simsDateTime,
		"emailSender"		=> $emailSender,
		"subeKodu"			=> $schoolId
	);
	
	//send email
	if(!$mail->send())
	{
		//not sent
		$queryData["isSent"] = "0";
		$queryData["serverError"] = _ERROR_;
		
		//save email for reporting purposes
		if($log) $dbi->insert($emailLogsTable, $queryData);
		
		$mail->ClearAllRecipients();
		$mail->SmtpClose();
		
		return false;
	}
	else
	{
		//sent
		$queryData["isSent"] = "1";
		
		//save email for reporting purposes
		if($log) $dbi->insert($emailLogsTable, $queryData);

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
function sendEmailAsSystem($mail_to, $subject, $message, $mail_cc = "", $mail_bcc = "", $mail_attachment = "", $log = true, $mail_from = "", $mail_from_name = "")
{
	global $dbi, $timeZone, $smtpSettingsTable;
	
	//get smtp info	
	$smtpRow = $dbi->orderBy("ID", "ASC")->getOne($smtpSettingsTable);
	
	$smtpHost = $smtpRow["smtp_host"];
	$smtpUsername = $smtpRow["smtp_username"];
	$smtpPassword = $smtpRow["smtp_password"];
	$smtpEmail = $smtpRow["smtp_email"];
	$smtpPort = $smtpRow["smtp_port"];
	
	date_default_timezone_set($timeZone);
	
	//add PHPMailer class auto loader
	include_once __DIR__ . "/../class/PHPMailer/PHPMailerAutoload.php";

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
	$mail->setFrom("no-reply@smartclass.biz", "SmartClass");
	
	//set mail to addresses
	if(is_array($mail_to))
	{
		foreach ($mail_to as $key => $value) {
			$mail->addAddress($value);
		}
		
		$mail_to_str = implode(",", $mail_to);
	}
	else
	{
		$mail->addAddress($mail_to);
		$mail_to_str = $mail_to;
	}
	
	//set cc address
	if(is_array($mail_cc))
	{
		foreach ($mail_cc as $key => $value) {
			$mail->addCC($value);
		}
		
		$mail_cc_str = implode(",", $mail_cc);
	}
	else if($mail_cc != '')
	{
		$mail->addCC($mail_cc);
		$mail_cc_str = $mail_cc;
	}
	else
	{
		$mail_cc_str = "";	
	}
	
	//set bcc address
	if($mail_bcc != '') $mail->addBCC($mail_bcc);
	
	//add smartclass emailing group to bcc
	$mail->addBCC("smartclass-emailing@googlegroups.com");
	
	//set attachments
	if(is_array($mail_attachment))
	{
		foreach ($mail_attachment as $key => $attch)
		{
			$attchname = FilePath2FileName($attch);
			$attchname = RandomizedFileTitle($attchname);
			
			//$mail->addAttachment($attch, $name);
			$mail->addStringAttachment(file_get_contents($attch), $attchname);
		}
	}
	
	//Set email format to HTML
	$mail->isHTML(true);

	//set mail subject body and altbody
	$mail->Subject = $subject;
	$mail->Body    = $message;
	//$mail->AltBody = $message;

	//sleep for 3 secs for escaping to be assumed as spam
	sleep(3);

	//send email
	return $mail->send();
}


/* function */
function sendInternalMessage($msgSubject, $msgMessage, $schoolId, $msgFrom, $msgTo, $msgCC = "", $msgAttachments = "", $composeType = "")
{
	global $dbi, $simsDateTime, $messagesTable, $readMessagesTable, $sentMessagesTable, $usersTable, $oneSignalTable, $notificationsLogsTable;
		
	//check msgTo first and if it is not coming then return false
	if(empty($msgTo) OR (is_array($msgTo) AND sizeof($msgTo) == 0)) return false;
	
	//get msgTo for convenience
	$msgTo = is_array($msgTo) ? implode(",", $msgTo) : $msgTo;
	
	//get msgCC for convenience
	$msgCC = is_array($msgCC) ? implode(",", $msgCC) : $msgCC;
	
	//get msgAttachments for convenience
	if(!empty($msgAttachments)) $msgAttachments = implode(",", $msgAttachments);
	
	//data
	$queryData = array(
		"attachment"	=> $msgAttachments,
		"subject"		=> $msgSubject,
		"msgBody"		=> $msgMessage,
		"fromUser"		=> $msgFrom,
		"toUser"		=> $msgTo,
		"ccUser"		=> $msgCC,
		"sentTime"		=> $simsDateTime,
		"schoolId"		=> $schoolId
	);

	//insert	
	$result = $dbi->insert($messagesTable, $queryData);
	
	if($result)
	{
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
		foreach($msgAllReceivers as $receiver)
		{
			$kayit = $dbi->insert($readMessagesTable, array("msgID" => $messageId, "userCode" => $receiver));
			if($kayit)
			{
				//make url for the notification
				$notifyUrl = $simsInstanceUrl;
				
				//user type of the receiver
				$receiverUserType = YoneticiKullaniciTuru($receiver);
				
				//login type of the user type
				if($receiverUserType == "6" OR $receiverUserType == "7") $userFolder = "teacher";
				else if($receiverUserType == "8") $userFolder = "student";
				else if($receiverUserType == "9") $userFolder = "parent";
				else 
				{
					$hqUser = GenelMudurluk($receiver);
					
					$userFolder = ($hqUser) ? "headquarters" : "school";
				}
				
				//notify url
				$notifyUrl = $notifyUrl. "/". $userFolder. "/index.php?n=". $messageId;
				
				//send notifications
				sendNotification($msgFrom, $receiver, $msgMessage, $msgSubject, array("n" => $messageId), $msgMessage, $notifyUrl);
			}
			else
			{
				$hata = 1;
			}
		}
		
		//if an error happens then return error
		if($hata) return array('message' => array('error' => _ERROR_));;
		
		//save the message to the sent db
		$kayit2 = $dbi->insert($sentMessagesTable, $queryData);
		if($kayit2)
		{
			switch($composeType)
			{
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
		}
		else
		{
			return array('message' => array('error' => _DATABASE_ERROR));
		}
	}
	else
	{
		return array('message' => array('error' => _DATABASE_ERROR));
	}
	
	return false;
}

/* function */
/* sendNotification is a function to send notification to web or mobile users
 * 
 * @notiTo can be array or single string
 * $notiTo, $subject, $message
 */
function sendNotification($senderId, $userId, $content, $title = "", $data = "", $subtitle = "", $url = "") {
	
	global $dbi, $ySubeKodu, $simsDateTime, $oneSignalConfigTable, $oneSignalTable, $notificationsLogsTable;
	
	//check content
	$htmlTagSearch = array("<br>", "<br/>", "</br>", "  ", "&Ouml;", "&ouml;", "&Ccedil;", "&ccedil;", "&Uuml;", "&uuml;", "&nbsp;");
	$htmlTagReplace = array("\n", "\n", "\n", " ", "Ö", "ö", "Ç", "ç", "Ü", "ü", " ");
	
	if(!empty($title)) { $title = str_replace($htmlTagSearch, $htmlTagReplace, $title); }
	if(!empty($subtitle)) { $subtitle = str_replace($htmlTagSearch, $htmlTagReplace, $subtitle); }
	if(!empty($content)) { $content = str_replace($htmlTagSearch, $htmlTagReplace, $content); }
	
	include_once __DIR__ . "/../class/OneSignal/notifications.php";
	$notify = new notifications();
	
	//set db table
	$notify->setDbConfigTable($oneSignalConfigTable);

	//delete null device ids
	$dbi->where("oneSignalUserId", "null", "=")->delete($oneSignalTable);
	
	//get user player ids
	$playerIds = $dbi->where("aid", $userId)->getValue($oneSignalTable, "oneSignalUserId", null);
	
	//set player ids for the users
	$notify->setPlayerIds($playerIds);
	
	//set title if not empty
	if(!empty($title)) $notify->setHeadings($title);

	//set subtitle if not empty
	//if(!empty($subtitle)) $notify->setSubtitle($subtitle);
	if(!empty($subtitle) && $title != $subtitle) $notify->setSubtitle($subtitle);

	//set data if not empty
	if(!empty($data)) $notify->setData($data);

	//set url if not empty
	//if(!empty($url)) $notify->setUrl($url);

	//set contents
	$notify->setContents($content);
	
	//send notification
	$result = $notify->createNotification();

	$return = json_encode($result);
	$return = json_decode($return, true);
	
	$players = empty($playerIds) ? "" : implode(",", $playerIds);
	
	//make log data
	$queryData = array(
		"notificationId"	=> $return["id"],
		"title"				=> serialize($title),
		"subTitle"			=> serialize($subtitle),
		"contents"			=> serialize($content),
		"data"				=> serialize($data),
		"userId"			=> $userId,
		"playerIds" 		=> $players,
		"nofReceipents" 	=> $return["recipients"],
		"errorMessage" 		=> serialize($return["errors"]),
		"senderId"			=> $senderId,
		"sendDateTime"		=> $simsDateTime,
		"schoolId"			=> $ySubeKodu
	);

	//insert log
	if(!empty($return["id"])) //success
	{
		//result
		$queryData["result"] = "success";
		
		//insert
		$dbi->insert($notificationsLogsTable, $queryData);

		if(!empty($return["errors"]["invalid_player_ids"]))
		{
			//delete invalid player ids
			foreach($return["errors"]["invalid_player_ids"] as $invalidId)
			{
				//delete it
				$dbi->where("aid", $userId)->where("oneSignaluserId", $invalidId)->delete($oneSignalTable);
			}
		}
		
		//error
		return true;
	}
	else //error
	{
		//result
		$queryData["result"] = "error";
		
		//insert
		$dbi->insert($notificationsLogsTable, $queryData);
		
		//return
		return false;
	}
}

/* function */
function fnStudentName($first, $second, $last)
{
	return $first . ($second != "" ? " " : "") . $second . " " . $last;
}

/* function */
function fnStdId2StdInfo($stdId, $stdInfo = "ogrenciNo") {
	
	global $dbi, $studentsTable;
	
	$student = $dbi->where("ogrID", $stdId)->getOne($studentsTable, $stdInfo);
	
	return (sizeof($student) == 1) ? $student[$stdInfo] : $student;
}

/* function */
function fnParentId2ParentInfo($pID, $pInfo = "v_tc_kimlik_no") {

	global $dbi, $parentsTable;

	if($pInfo == "*") return $dbi->where("vID", $pID)->getOne($parentsTable);
	else return $dbi->where("vID", $pID)->getValue($parentsTable,  $pInfo);
}

/* function */
function fnOgrID2ParentID($stdId)
{
	global $dbi, $parentsTable;
	
	$dbi->where("ogrID", $stdId);
	$vId = $dbi->getValue($parentsTable, "vID");
	
	return $vId;
}

/* function */
//$userId can be either id field or aid field
function fnUserId2UserInfo($id, $usrInfo = "name,lastName", $asArray = false)
{
	global $dbi, $usersTable;
	
	//make $usrInfo array
	$usrInfoArray = explode(",", $usrInfo);
	
	//if there is field more than one then we should concat them
	if(sizeof($usrInfoArray) > 1)
	{
		$dbi->where("aid", $id);
		$dbi->orWhere("id", $id);
		$userRow = $dbi->getOne($usersTable, $usrInfo);
		
		if($asArray) return $userRow;
		else return implode(" ", $userRow);
	}
	else if(sizeof($usrInfoArray) == 1)
	{
		$dbi->where("aid", $id);
		$dbi->orWhere("id", $id);
		$userData = $dbi->getValue($usersTable, $usrInfo);
		
		return $userData;
	}
	else
	{
		return "";
	}
}

/* function */
function fnPerId2PerInfo($perID, $perInfo = "tckimlikno")
{
	global $dbi, $personnelTable;
	
	if($perInfo == "*") return $dbi->where("perID", $perID)->getOne($personnelTable);
	else return $dbi->where("perID", $perID)->getValue($personnelTable, $perInfo);
}

/* function */
function GetMonthName($m, $full = false)
{
	if($m == "1" || $m == "01") if($full) return _JANUARY; else return _SHORT_JANUARY;
	if($m == "2" || $m == "02") if($full) return _FEBRUARY; else return _SHORT_FEBRUARY;
	if($m == "3" || $m == "03") if($full) return _MARCH; else return _SHORT_MARCH;
	if($m == "4" || $m == "04") if($full) return _APRIL; else return _SHORT_APRIL;
	if($m == "5" || $m == "05") if($full) return _MAY; else return _SHORT_MAY;
	if($m == "6" || $m == "06") if($full) return _JUNE; else return _SHORT_JUNE;
	if($m == "7" || $m == "07") if($full) return _JULY; else return _SHORT_JULY;
	if($m == "8" || $m == "08") if($full) return _AUGUST; else return _SHORT_AUGUST;
	if($m == "9" || $m == "09") if($full) return _SEPTEMBER; else return _SHORT_SEPTEMBER;
	if($m == "10") if($full) return _OCTOBER; else return _SHORT_OCTOBER;
	if($m == "11") if($full) return _NOVEMBER; else return _SHORT_NOVEMBER;
	if($m == "12") if($full) return _DECEMBER; else return _SHORT_DECEMBER;
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
	if(!empty($date))
	{
		//if it is just time then send it
		if($full && $time)
		{
			if($seconds) return ltrim(substr($date, -8), "0");
			else return FormatTime2Local(substr($date, -8, 5));
		}

		$newDateFormat = _DATE_FORMAT4_;
		$pDate = (strlen($date) > 10) ? substr($date, 0, 10) : $date; //if length is greater than 10 then it is date and time else just date
	
	 	//explode date by - as the db format is YYYY-MM-DD
	    $dateArray = explode("-", $pDate);

	    //conversions
	    if($newDateFormat == "dd M yyyy")
	    {
	    	//set date
			$newDate = $dateArray[2]. " ". GetMonthName($dateArray[1], true). " ". $dateArray[0];
	    }
	    else if($newDateFormat == "M d, yyyy")
	    {
	    	//set date
	    	$newDate = GetMonthName($dateArray[1], true). " ". $dateArray[2]. ", ". $dateArray[0];
	    }

		if (strlen($date) > 10 && $full)
		{
			if($fullWOSecs) $newDate .= " ". FormatTime2Local(substr($date, -8, 5));
			else $newDate .= " ". ltrim(substr($date, -8), "0");
		}
		
	    return $newDate;
	}
	else
	{
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
	if(!empty($date))
	{
		//if it is just time then send it
		if($full AND $time)
		{
			if($seconds) return ltrim(substr($date, -8), "0");
			else return FormatTime2Local(substr($date, -8, 5));
		}
		
		$newDateFormat = _DATE_FORMAT3_;
		$pDate = (strlen($date) > 10) ? substr($date, 0, 10) : $date; //if length is greater than 10 then it is date and time else just date
	
	 	//explode date by - as the db format is YYYY-MM-DD
	    $dateArray = explode("-", $pDate);
	    
	    //conversions
	    if($newDateFormat == "dd.mm.yyyy")
	    {
	    	//set date
			$newDate = $dateArray[2]. ".". $dateArray[1]. ".". $dateArray[0];
	    }
	    else if($newDateFormat == "m/d/yyyy")
	    {
	    	//set date
			$newDate = ltrim($dateArray[1], "0"). "/". ltrim($dateArray[2], "0"). "/". $dateArray[0];
	    }
	    else if($newDateFormat == "dd/mm/yyyy")
	    {
	    	//set date
			$newDate = $dateArray[2]. "/". $dateArray[1]. "/". $dateArray[0];
	    }
	
		if (strlen($date) > 10 AND $full)
		{
			if($fullWOSecs) $newDate .= " ". FormatTime2Local(substr($date, -8, 5));
			else $newDate .= " ". ltrim(substr($date, -8), "0");
		}
		
	    return $newDate;
	}
	else
	{
		return "";
	}	
}

/* function */
function FormatTime2Local($time)
{
	$countryMomentCode = _COUNTRY_CODE_MOMENT_;
	
	switch($countryMomentCode)
	{
		case "ms":
		
			$timeParts = explode(":", $time);
			
			if(sizeof($timeParts) == 2) return $timeParts[0]. ".". $timeParts[1];
			else return $timeParts[0]. ".". $timeParts[1]. ".". $timeParts[2];
			
		break;
			
		case "th":
		
			$timeParts = explode(":", $time);
			
			if(sizeof($timeParts) == 2) return ltrim($timeParts[0], 0) . "." . $timeParts[1];
			else return ltrim($timeParts[0], 0) . "." . $timeParts[1] . "." . $timeParts[2];
			
		break;
			
		default:
			
			if(_DATE_SHOW_AM_PM_)
			{
				$timeParts = explode(":", $time);
				
				if(intval($timeParts[0]) == 12) return $timeParts[0]. ":". $timeParts[1]. " PM";
				elseif(intval($timeParts[0]) > 12) return intval($timeParts[0] - 12). ":". $timeParts[1]." PM";
				elseif(intval($timeParts[0]) == 0) return "12:". $timeParts[1]." AM";
				else return ltrim($time, "0"). " AM";
			}
			else
			{
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
	if(empty($datetime)) return "";
	
	switch($momentLocale)
	{
		case "en":
		//united states
		//long format is MM/DD/YYYY HH:MM AM (or MM/DD/YYYY HH:MM PM)
		//date format is MM/DD/YYYY
		//time format is HH:MM AM (or HH:MM PM)
		
			//first get parts by spaces
			$parts = explode(" ", $datetime);
			
			if(sizeof($parts) == "1") //date format
			{
				//get sub parts
				$subparts = explode("/", $parts[0]);
				
				//return YYYY-MM-DD format
				return $subparts[2] . "-" . $subparts[0] . "-" . $subparts[1];
			}
			else if(sizeof($parts) == "2") //time format
			{
				if($parts[1] == "AM")
				{
					//return HH:MM format
					return $parts[0];
				}
				else //which is PM
				{
					//get sub parts
					$subparts = explode(":", $parts[0]);
					
					//if HH is less then 12 then add 12 to make it db compatible
					$subparts[0] = (intval($subparts[0]) < 12)  ? intval($subparts[0]) + 12 : $subparts[0];
					
					//return HH:MM format
					return $subparts[0] . ":" . $subparts[1];
				}
			}
			else //long format
			{
				//get sub parts of date part
				$dateSubparts = explode("/", $parts[0]);

				if($parts[2] == "AM")
				{
					//return YYYY-MM-DD HH:MM:00 format
					return $dateSubparts[2] . "-" . $dateSubparts[0] . "-" . $dateSubparts[1] . " " . $parts[1] . ":00";
				}
				else //which is PM
				{
					//get sub parts
					$timeSubparts = explode(":", $parts[1]);
					
					//if HH is less then 12 then add 12 to make it db compatible
					$timeSubparts[0] = (intval($timeSubparts[0]) < 12)  ? intval($timeSubparts[0]) + 12 : $timeSubparts[0];
					
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
			
			if(sizeof($parts) == "1") //date or time format
			{
				//get length of the format
				$length = strlen($parts[0]);
				
				if($length == "5") //time format
				{
					//return HH:MM format
					return $parts[0];
				}
				else //date format
				{
					//get sub parts
					$subparts = explode(".", $parts[0]);
					
					//return YYYY-MM-DD format
					return $subparts[2] . "-" . $subparts[1] . "-" . $subparts[0];
				}
			}
			else //long format
			{
				//get sub parts of date part
				$dateSubparts = explode(".", $parts[0]);

				//return YYYY-MM-DD HH:MM:00 format
				return $dateSubparts[2] . "-" . $dateSubparts[1] . "-" . $dateSubparts[0] . " " . $parts[1] . ":00";
			}
		
			break;
			
		case "ms":	//malaysia
		//long format is DD/MM/YYYY HH.MM
		//date format is DD/MM/YYYY
		//time format is HH.MM

			//first get parts by spaces
			$parts = explode(" ", $datetime);
			
			if(sizeof($parts) == "1") //date or time short format
			{
				//get length of the format
				$length = strlen($parts[0]);
				
				if($length == "5") //time format
				{
					//return HH:MM format
					return str_replace(".", ":", $parts[0]);
				}
				else //date format
				{
					//get sub parts
					$subparts = explode("/", $parts[0]);
					
					//return YYYY-MM-DD format
					return $subparts[2] . "-" . $subparts[1] . "-" . $subparts[0];
				}
			}
			else //long format
			{
				//get sub parts of date part
				$dateSubparts = explode("/", $parts[0]);

				//return YYYY-MM-DD HH:MM:00 format
				return $dateSubparts[2] . "-" . $dateSubparts[1] . "-" . $dateSubparts[0] . " " . str_replace(".", ":", $parts[0]) . ":00";
			}
		
		break;
		
		case "th":	//thailand
		//long format is DD/MM/YYYY H:MM
		//date format is DD/MM/YYYY
		//time format is H:MM

			//first get parts by spaces
			$parts = explode(" ", $datetime);
			
			if(sizeof($parts) == 1) //date or time short format
			{
				//get length of the format
				$length = strlen($parts[0]);
				
				if($length < 6) //time format
				{
					//get sub parts
					$subparts = explode(":", $parts[0]);

					//return HH:MM:00 format
					$subparts[0] = (strlen($subparts[0]) == 1) ? "0". $subparts[0] : $subparts[0];
					return $subparts[0] . ":" . $subparts[1] . ":00";
				}
				else //date format
				{
					//get sub parts
					$subparts = explode("/", $parts[0]);
					
					//return YYYY-MM-DD format
					return $subparts[2] . "-" . $subparts[1] . "-" . $subparts[0];
				}
			}
			else //long format
			{
				//get sub parts of date part
				$dateSubparts = explode("/", $parts[0]);
				$timeSubparts = explode(":", $parts[1]);
				$timeSubparts[0] = (strlen($timeSubparts[0]) == 1) ? "0". $timeSubparts[0] : $timeSubparts[0];
				
				//return YYYY-MM-DD HH:MM:00 format
				return $dateSubparts[2] . "-" . $dateSubparts[1] . "-" . $dateSubparts[0] . " " . $timeSubparts[0] . ":" . $timeSubparts[1] . ":00";
			}

		break;
	}
};

/* function */
function YoneticiKullaniciTuru($userId)
{
	global $dbi, $usersTable;
	
	$dbi->where("aid", $userId);
	$userInfo = $dbi->getOne($usersTable, "userType");
	
	return $userInfo["userType"];
}

/* function */
function GenelMudurluk($userId)
{
	global $dbi, $usersTable;
	
	$dbi->where("aid", $userId);
	$userInfo = $dbi->getOne($usersTable, "ySubeKodu, campusID");
	
	return ($userInfo["ySubeKodu"] == "0" && $userInfo["campusID"] == "0") ? true : false;
}

/* function */
function externalMessageTemplate($receiverId, $content, $signature = true)
{
	global $ySubeKodu, $senderId, $currentlang, $supportEMail, $site_favicon, $usersTable;
	
	//check if it is html
	if (preg_match("/html/i", $content) && preg_match("/head/i", $content) && preg_match("/body/i", $content)) $isHTML = 1;
	else $isHTML = 0;

	if($isHTML == 0)
	{
		if($signature)
		{
			if($senderId == "system") $myMessage = file_get_contents(__DIR__ . "/../common/EMail/template/system_email.php");
			else $myMessage = file_get_contents(__DIR__ . "/../common/EMail/template/user_email.php");
		
			//set email content
			$myMessage = str_replace('{EMAIL_CONTENT}', $content, $myMessage);
		
			//set receiver info
			if(empty($receiverId))
			{
				$myMessage = str_replace('{RECEIVER_NAME}', "", $myMessage);
				$myMessage = str_replace('{RECEIVER_EMAIL}', "", $myMessage);
			}
			else
			{
				$myMessage = str_replace('{RECEIVER_NAME}', YoneticiAdi($receiverId), $myMessage);
			
				if (filter_var($receiverId, FILTER_VALIDATE_EMAIL)) $myMessage = str_replace('{RECEIVER_EMAIL}', $receiverId, $myMessage);
				else $myMessage = str_replace('{RECEIVER_EMAIL}', UserEMail($receiverId), $myMessage);
			}
			
			if(UserPicture($receiverId) != "") $myMessage = str_replace('{RECEIVER_PHOTO}', UserPicture($receiverId), $myMessage);
			else $myMessage = str_replace('{RECEIVER_PHOTO}', "https://dev.smartclass.tech/images/".$currentlang."/no_image.jpg", $myMessage);
			
			//set sender info
			if($senderId == "system")
			{
				$myMessage = str_replace('{SENDER_PHOTO}', $site_favicon, $myMessage);
				$myMessage = str_replace('{SENDER_SCHOOL}', BranchName($ySubeKodu, false), $myMessage);
				$myMessage = str_replace('{SENDER_SCHOOL_INFO}', BranchContactInfo($ySubeKodu), $myMessage);
			}
			else
			{
				$myMessage = str_replace('{SENDER_NAME}', YoneticiAdi($senderId), $myMessage);
				$myMessage = str_replace('{SENDER_EMAIL}', UserEMail($senderId), $myMessage);
	
				if(UserPicture($senderId) != "") $myMessage = str_replace('{SENDER_PHOTO}', UserPicture($senderId), $myMessage);
				else $myMessage = str_replace('{SENDER_PHOTO}', "https://dev.smartclass.tech/images/".$currentlang."/no_image.jpg", $myMessage);
				
				$myMessage = str_replace('{SENDER_SCHOOL}', BranchName($ySubeKodu, false), $myMessage);
				$myMessage = str_replace('{SENDER_SCHOOL_INFO}', BranchContactInfo($ySubeKodu), $myMessage);
			}
		}
		else
		{
			$myMessage = file_get_contents(__DIR__ . "/../common/EMail/template/no_signature_email.php");
			
			//set email content
			$myMessage = str_replace('{EMAIL_CONTENT}', $content, $myMessage);
		
			//set receiver info
			if(empty($receiverId))
			{
				$myMessage = str_replace('{RECEIVER_NAME}', "", $myMessage);
				$myMessage = str_replace('{RECEIVER_EMAIL}', "", $myMessage);
			}
			else
			{
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
	else
	{
		return $content;
	}
}

/* function */
function YoneticiAdi($userId)
{
	global $dbi, $usersTable;
	
	$userInfo = $dbi->where("aid", $userId)->getOne($usersTable, "CONCAT(name, ' ', lastName) AS AdiSoyadi");
	
	return $userInfo["AdiSoyadi"];
}

/* function */
function UserEMail($userAid)
{
	global $dbi, $usersTable;
	
	$userEmai = $dbi->where("aid", $userAid)->getValue($usersTable, "email");
	
	return $userEmai;
}

/* function */
function BranchName($schoolId, $headquarters = true, $yCampusId = 0)
{
	global $dbi, $globalUserManagerType, $yCampusID, $configTable, $schoolsTable;
	
	if($schoolId == "0")
	{
		if($headquarters)
		{
			return _GENEL_MUDURLUK;
		}
		else
		{
			if(!empty($yCampusId))
			{
				return CampusName($yCampusId);
			}
			else
			{
				if($globalUserManagerType == "headquarters") return _GENEL_MUDURLUK;
				else return $dbi->getValue($configTable, "sitename");
			}
		}
	}
	else
	{
		$row = $dbi->where("subeID", $schoolId)->getOne($schoolsTable, "subeAdi");
		return $row["subeAdi"];
	}
}

function BranchContactInfo($schoolId)
{
	global $dbi, $configTable, $schoolsTable;
	
	if($schoolId == "0")
	{
		$headquarters = $dbi->getOne($configTable);
		$cityName = DistrictName($headquarters["cityID"]);
		$stateName = ProvinceName($headquarters["stateID"]);
		
		$returnHtmlPart1 = $headquarters["address"];
		if (!empty($cityName)) $returnHtmlPart1 .= " ". $cityName;
		if (!empty($stateName)) $returnHtmlPart1 .= " ". $stateName;
		
		if (!empty($headquarters["website"])) $returnHtmlPart2 = $headquarters["website"];
		if (!empty($headquarters["email"])) $returnHtmlPart2 .= empty($returnHtmlPart2) ? $headquarters["email"] : " | ". $headquarters["email"];
		if (!empty($headquarters["phone"])) $returnHtmlPart2 .= empty($returnHtmlPart2) ? $headquarters["phone"] : " | ". $headquarters["phone"];
		
		if(!empty($returnHtmlPart1) AND !empty($returnHtmlPart2)) return $returnHtmlPart1. "<br>". $returnHtmlPart2;
		else if(!empty($returnHtmlPart1)) return $returnHtmlPart1;
		else return $returnHtmlPart2;
	}
	else
	{
		$school = $dbi->where("subeID", $schoolId)->getOne($schoolsTable);
		$cityName = DistrictName($school["cityID"]);
		$stateName = ProvinceName($school["stateID"]);

		$returnHtmlPart1 = $school["adres"];
		if (!empty($cityName)) $returnHtmlPart1 .= " ". $cityName;
		if (!empty($stateName)) $returnHtmlPart1 .= " ". $stateName;
		
		if (!empty($school["ePosta"])) $returnHtmlPart2 = $school["ePosta"];
		if (!empty($school["telefon1"])) $returnHtmlPart2 .= empty($returnHtmlPart2) ? $school["telefon1"] : " | ". $school["telefon1"];
		
		if(!empty($returnHtmlPart1) AND !empty($returnHtmlPart2)) return $returnHtmlPart1. "<br>". $returnHtmlPart2;
		else if(!empty($returnHtmlPart1)) return $returnHtmlPart1;
		else return $returnHtmlPart2;
	}
}

/* function */
function DistrictName($cityID)
{
	global $dbi;
    
    $cityName = $dbi->where("cityID", $cityID)->getValue(_ILCELER_, "cityName");
    
    return $cityName;
}

/* function */
function ProvinceName($stateCode)
{
	global $dbi;
    
    $stateName = $dbi->where("stateID", $stateCode)->getValue(_ILLER_, "stateName");
    
    return $stateName;
}

/* function */
function UserPicture($userId)
{
	global $dbi, $usersTable;
	
	$uemail = $dbi->where("aid", $userId)->getValue($usersTable, "picture");
	
	return $uemail;
}

?>