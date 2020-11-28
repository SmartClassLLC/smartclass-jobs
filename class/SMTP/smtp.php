<?php

function smtpmail($mail_to, $subject, $message, $headers = '', $mail_cc = '', $mail_bcc = '')
{
	global $db, $timeZone, $adminmail, $sitename;
	
	$smtpRow = $db->sql_fetchrow($db->sql_query("SELECT * FROM "._SMTP_SETTINGS_));
	
	$smtpHost = $smtpRow["smtp_host"];
	$smtpUsername = $smtpRow["smtp_username"];
	$smtpPassword = $smtpRow["smtp_password"];
	$smtpEmail = $smtpRow["smtp_email"];
	$smtpPort = intval($smtpRow["smtp_port"]);
	
	date_default_timezone_set($timeZone);
	
	//add PHPMailer class auto loader
	require 'class/PHPMailer/PHPMailerAutoload.php';

	$mail = new PHPMailer;
	
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
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	$mail->SMTPDebug = 0;
	
	//Ask for HTML-friendly debug output
	//$mail->Debugoutput = 'html';

	//set character set
	$mail->CharSet = 'UTF-8';

	//set mail from							
	$mail->setFrom($adminmail, $sitename);
	
	//set mail to addresses
	//$mail->addAddress($adminmail, $sitename);
	$mail->addAddress($mail_to);
	
	//set mail reply-to address
	$mail->addReplyTo('no-reply@smartclass.biz');
	
	//set cc address
	$mail->addCC($mail_cc);
	
	//set bcc address
	$mail->addBCC($mail_bcc);
	
	//set attachments
	//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
	//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
	
	//Set email format to HTML
	$mail->isHTML(true);

	$mail->Subject = $subject;
	$mail->Body    = $message;
	$mail->AltBody = $message;
	
	if(!$mail->send())
	{
		return $mail->ErrorInfo;
	}
	else
	{
		return true;
	}
}

?>