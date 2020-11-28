<?php

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

//check if the file is tried to be accessed directly
if(stristr($_SERVER['SCRIPT_NAME'], "tables_common.php")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

$SmartClassCommonDB = "smartclass_common";

/* function */
//common database for all cloud
define("_GLOBAL_CONFIG_", $SmartClassCommonDB.".global_config");
define("_CUSTOMER_SCHOOLS_", $SmartClassCommonDB.".customers");
define("_BANNED_IP_", $SmartClassCommonDB.".banned_ip"); //not used yet
define("_HATA_BILDIRIMI_", $SmartClassCommonDB.".bugs");
define("_COUNTRIES_", $SmartClassCommonDB.".countries");
define("_ILLER_", $SmartClassCommonDB.".states");
define("_ILCELER_", $SmartClassCommonDB.".cities");
define("_DERS_TURU_", $SmartClassCommonDB.".ders_turu");
define("_FORUMS_", $SmartClassCommonDB.".forums"); //not used yet
define("_ICONS_", $SmartClassCommonDB.".icons");
define("_MESLEKLER_", $SmartClassCommonDB.".meslekler");
define("_ACTION_LINKS_", $SmartClassCommonDB.".op_links");
define("_SETTINGS_LIST_", $SmartClassCommonDB.".settings_list");
define("_TICKETS_", $SmartClassCommonDB.".tickets");
define("_TICKETS_CATEGORIES_", $SmartClassCommonDB.".tickets_categories");
define("_TICKETS_MESSAGES_", $SmartClassCommonDB.".tickets_messages");
define("_TICKETS_MESSAGES_READ_", $SmartClassCommonDB.".tickets_messages_read");
define("_GRADING_TYPES_", $SmartClassCommonDB.".grading_types");
define("_STUDENT_REPORT_WIDGETS_", $SmartClassCommonDB.".student_report_widgets");
define("_MAINTENANCE_", $SmartClassCommonDB.".maintenance");
define("_DEVELOPERS_MANUAL_", $SmartClassCommonDB.".developers_manual");
define("_USER_HELP_", $SmartClassCommonDB.".user_help");
define("_GOOGLE_CREDENTIALS_", $SmartClassCommonDB.".google_credentials");
define("_WIDGET_FILES_", $SmartClassCommonDB.".widget_files");
define("_WORKFLOW_DEFINITIONS_", $SmartClassCommonDB.".workflows");
define("_SURVEY_QUESTION_TYPES_", $SmartClassCommonDB.".survey_question_types");
define("_HELPDESK_ISSUE_TYPES_", $SmartClassCommonDB.".helpdesk_issue_types");
define("_PARENT_INTERVIEW_TYPES_", $SmartClassCommonDB.".parent_interview_types");
//define("_HEALTH_BLOOD_GROUP_", $SmartClassCommonDB.".health_blood_group"); //kullanmadigimiz icin comment ettim. ayrica blood diye ayri bir tablo var. onu kullanabiliriz.
define("_BLOOM_TAXONOMY_", $SmartClassCommonDB.".bloom_taxonomy");
define("_COMMON_FORMS_", $SmartClassCommonDB.".forms");
define("_RELIGIONS_", $SmartClassCommonDB.".religions");
define("_BLOOD_GROUPS_", $SmartClassCommonDB.".blood_groups");
define("_CURRENCIES_", $SmartClassCommonDB.".currencies");
define("_GLOBAL_INTEGRATIONS_", $SmartClassCommonDB.".integrations");
define("_GLOBAL_INTEGRATION_PARAMETERS_", $SmartClassCommonDB.".integration_parameters");
define("_SUCCESS_GRADE_TYPES_", $SmartClassCommonDB.".success_grade_types");
define("_OZ_UVEY_DURUMU_", $SmartClassCommonDB.".oz_uvey_durumu");
define("_ACCOUNTING_ACCOUNT_TYPES_", $SmartClassCommonDB.".accounting_account_types");
define("_ACCOUNTING_ACCOUNTS_", $SmartClassCommonDB.".accounting_accounts");
define("_GAMES_", $SmartClassCommonDB.".games");
?>