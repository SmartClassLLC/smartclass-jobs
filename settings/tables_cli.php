<?php

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

//check if the file is tried to be accessed directly
if(stristr($_SERVER['SCRIPT_NAME'], "tables_cli.php")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

$configTable = $mainDB.".config";
$schoolsTable = $mainDB.".schools";
$settingsTable = $mainDB.".settings";
$smtpSettingsTable = $mainDB.".smtp_settings";
$usersTable = $mainDB.".users";
$moodleConfigTable = $mainDB.".moodle_config";
$logsTable = $mainDB.".logs";
$messagesTable = $mainDB.".emails";
$readMessagesTable = $mainDB.".emails_read";
$sentMessagesTable = $mainDB.".emails_sent";
$oneSignalConfigTable = $mainDB.".onesignal_config";
$oneSignalTable = $mainDB.".user_onesignal_ids";
$meetingsTable = $mainDB.".meetings";
$schoolUsageStatsTable = $mainDB.".school_usage_stats";
$schoolStatsTable = $mainDB.".school_stats";
$absenceSettingsTable = $mainDB.".devamsizlik_ayarlari";

$emailsTable = $seasonDB.".emails_tosend";
$emailLogsTable = $seasonDB.".email_logs";
$notificationsLogsTable = $seasonDB.".notification_logs";
$homeworksTable = $seasonDB.".homeworks";
$batchesTable = $seasonDB.".batches";
$studentsTable = $seasonDB.".ogrenciler";
$parentsTable = $seasonDB.".veliler";
$personnelTable = $seasonDB.".personel";
$annsTable = $seasonDB.".announcements";
$homeworksTable = $seasonDB.".homeworks";
$classBatchesTable = $seasonDB.".class_batches";
$classTeachersTable = $seasonDB.".class_teachers";
$socialPostsTable = $seasonDB.".social_posts";
$lmsCourseIdsTable = $seasonDB.".lms_course_ids";
$examsTable = $seasonDB.".sinavlar";
$examsCronsTable = $seasonDB.".sinav_rapor_cronlar";
$examsSubjectsTable = $seasonDB.".sinav_dersleri";
$examsResultsTable = $seasonDB.".sinav_sonuclari_netler";
$examsBatchAveragesTable = $seasonDB.".sinav_net_ortalama_sinif";
$examsSchoolAveragesTable = $seasonDB.".sinav_net_ortalama_okul";
$examsGenAveragesTable = $seasonDB.".sinav_net_ortalama_genel";
$examsItemAnalysisTable = $seasonDB.".sinav_sonuclari_soru_analizi";
$examsPublishTable = $seasonDB.".sinavlar_yayin_aktivasyon";
$hourlyAttendanceTable = $seasonDB.".yoklama";
$dailyAttendanceTable = $seasonDB.".gunluk_yoklama";

?>