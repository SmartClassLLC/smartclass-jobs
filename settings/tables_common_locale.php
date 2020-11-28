<?php

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

//check if the file is tried to be accessed directly
if(stristr($_SERVER['SCRIPT_NAME'], "tables_common_locale.php")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

$SmartClassCommonLocalDB = "smartclass_common_" . $localeCode;

/* function */
//common local database (ie. tr)
define("_OKUL_SEVIYELERI_", $SmartClassCommonLocalDB.".010_okul_seviyesi");
define("_SINIF_SEVIYELERI_", $SmartClassCommonLocalDB.".020_sinif_seviyeleri");
define("_EGITIM_PROGRAMLARI_", $SmartClassCommonLocalDB.".030_programlar");
define("_EGITIM_DOKUMANLAR_", $SmartClassCommonLocalDB.".04-_dokumanlar");
define("_EGITIM_DERSLER_", $SmartClassCommonLocalDB.".050_dersler");
define("_EGITIM_UNITELER_", $SmartClassCommonLocalDB.".060_uniteler");
define("_EGITIM_UNITE_KAZANIMLAR_", $SmartClassCommonLocalDB.".061_unite_kazanimlar");
define("_EGITIM_UNITE_ETKINLIKLER_", $SmartClassCommonLocalDB.".062_unite_kazanim_etkinlikler");
define("_EGITIM_UNITE_ACIKLAMALAR_", $SmartClassCommonLocalDB.".063_unite_kazanim_aciklamalar");
define("_EGITIM_UNITE_OLCME_DEGERLENDIRME_", $SmartClassCommonLocalDB.".064_unite_kazanim_olcme_degerlendirme");
define("_EGITIM_UNITE_DISIPLINLER_", $SmartClassCommonLocalDB.".065_unite_kazanim_disiplinler");

define("_COURSES_", $SmartClassCommonLocalDB.".courses");
define("_CONTENT_", $SmartClassCommonLocalDB.".content");
define("_QUESTIONS_", $SmartClassCommonLocalDB.".questions");
define("_QUESTIONS_OPTIONS_", $SmartClassCommonLocalDB.".questions_options");

define("_OKULLAR_", $SmartClassCommonLocalDB.".okullar");
define("_OKUL_GRUPLARI_", $SmartClassCommonLocalDB.".okul_gruplari");
define("_LOCAL_MAINTENANCE_", $SmartClassCommonLocalDB.".maintenance");
define("_FILE_SYSTEM_", $SmartClassCommonLocalDB.".filesystem");
define("_GLOBAL_ANNOUNCEMENTS_", $SmartClassCommonLocalDB.".announcements");
define("_PERSONNEL_CATEGORIES_", $SmartClassCommonLocalDB.".personel_categories");
define("_OPTIC_FORM_FIELD_NAMES_", $SmartClassCommonLocalDB.".optik_form_alan_adlari");
define("_FEE_TYPES_", $SmartClassCommonLocalDB.".fee_types");
define("_TAHSILAT_TURLERI_", $SmartClassCommonLocalDB.".tahsilat_turu");
define("_SAVED_STUDENT_REPORTS_", $SmartClassCommonLocalDB.".saved_student_reports");
define("_SMS_APIS_", $SmartClassCommonLocalDB.".sms_integrated_apis");
define("_ACADEMIC_STUDENT_REPORTS_", $SmartClassCommonLocalDB.".academic_student_reports");
define("_STUDENT_REPORTS_", $SmartClassCommonLocalDB.".student_reports");
define("_SMS_PARAMETERS_", $SmartClassCommonLocalDB.".sms_degiskenler");
define("_UNIVERSITELER_", $SmartClassCommonLocalDB.".universiteler");
define("_UNIVERSITELER_BOLUMLER_", $SmartClassCommonLocalDB.".universiteler_bolumler");
define("_UNIVERSITELER_FAKULTELER_", $SmartClassCommonLocalDB.".universiteler_fakulteler");
define("_UNIVERSITELER_PROGRAMLAR_", $SmartClassCommonLocalDB.".universiteler_programlar");
define("_DEVAMSIZLIK_KATEGORILERI_", $SmartClassCommonLocalDB.".yoklama_kategorileri");
define("_READONLY_ENROLLMENT_CONTRACTS_", $SmartClassCommonLocalDB.".kayit_sozlesmeleri");
define("_SINAV_SONUC_LISTELERI_", $SmartClassCommonLocalDB.".sinav_sonuc_listeleri");
define("_SINAV_RAPORLARI_", $SmartClassCommonLocalDB.".sinav_raporlari");   /* deprecated on 3/5/2018 */
define("_GRADING_TOOLS_", $SmartClassCommonLocalDB.".grading_tools");
define("_GRADING_STATIC_LETTERS_", $SmartClassCommonLocalDB.".grading_static_letters");
define("_DISCIPLINE_FINE_CATEGORIES_", $SmartClassCommonLocalDB.".discipline_fine_categories");
define("_PUNISHMENT_TYPES_", $SmartClassCommonLocalDB.".punishment_types");
define("_GUIDANCE_DEFINITIONS_", $SmartClassCommonLocalDB.".guidance_definitions");
define("_SCHOOL_BUS_TOURS_", $SmartClassCommonLocalDB.".school_bus_tours");
define("_BANKALAR_", $SmartClassCommonLocalDB.".bankalar");
define("_BANKA_SUBELERI_", $SmartClassCommonLocalDB.".banka_subeleri");
/*healthcare*/
define("_HC_HEALTH_COMPLAINTS_", $SmartClassCommonLocalDB.".health_complaints");
define("_HC_HEALTH_SYMPTOMS_", $SmartClassCommonLocalDB.".health_symptoms");
define("_HC_HEALTH_TREATMENTS_", $SmartClassCommonLocalDB.".health_treatments");
define("_HC_MEDICATIONS_", $SmartClassCommonLocalDB.".health_medications");
define("_HC_DISEASES_", $SmartClassCommonLocalDB.".health_diseases");
define("_HC_ALLERGIES_", $SmartClassCommonLocalDB.".health_allergies");
define("_HC_VACCINES_", $SmartClassCommonLocalDB.".health_vaccines");
define("_HC_DIAGNOSIS_TYPES_", $SmartClassCommonLocalDB.".health_diagnosis_types");
define("_HC_EXAMINATION_TYPES_", $SmartClassCommonLocalDB.".health_examination_types");
define("_HC_MEDICAL_SCANNING_TYPE_", $SmartClassCommonLocalDB.".health_medical_scanning_type");
define("_HC_MEDICAL_SCANNING_PARAMETER_TYPE_", $SmartClassCommonLocalDB.".health_medical_scanning_parameter_type");
define("_HC_MEDICAL_SCANNING_PARAMETER_INFO_", $SmartClassCommonLocalDB.".health_medical_scanning_parameter_info");
define("_HC_MEDICAL_CASES_CLOSING_TYPE_", $SmartClassCommonLocalDB.".health_medical_cases_closing_type");
/* Enrollment Interview Steps*/
define("_ENROLLMENT_INTERVIEW_STEPS_", $SmartClassCommonLocalDB.".enrollment_interview_steps");
/* Contract */
define("_CONTRACT_SETTINGS_", $SmartClassCommonLocalDB.".contract_settings");

define("_ACTIVITY_TYPES_", $SmartClassCommonLocalDB.".activity_types");
define("_PERSONEL_DEPARTMENTS_", $SmartClassCommonLocalDB.".personel_departments");
define("_GUIDANCE_SURVEY_CATEGORIES_", $SmartClassCommonLocalDB.".guidance_survey_categories");
define("_REQUESTS_TYPES_", $SmartClassCommonLocalDB.".ihtiyac_turleri");
define("_WORK_ORDER_TYPES_", $SmartClassCommonLocalDB.".work_order_types");
define("_LOCAL_CALENDARS_", $SmartClassCommonLocalDB.".calendars");

/* parent forms */
define("_PARENT_FORMS_", $SmartClassCommonLocalDB.".parent_forms");
/* parent forms end */

define("_RANDOM_QUOTES_", $SmartClassCommonLocalDB.".random_quotes");
define("_GRADUATION_SITUATIONS_", $SmartClassCommonLocalDB.".graduation_situations");
define("_MULTIPLE_INTELLIGENCE_ZONES_", $SmartClassCommonLocalDB.".multiple_intelligence_zones");
define("_LOCAL_INTEGRATIONS_", $SmartClassCommonLocalDB.".integrations");
define("_LOCAL_INTEGRATION_PARAMETERS_", $SmartClassCommonLocalDB.".integration_parameters");

define("_ADULT_GENDERS_", $SmartClassCommonLocalDB.".adult_genders");
define("_SUCCESS_CERTIFICATES_", $SmartClassCommonLocalDB.".success_certificates");
define("_EOKUL_SINIF_ISIMLERI_", $SmartClassCommonLocalDB.".eokul_sinif_isimleri");
define("_EOKUL_DERS_ISIMLERI_", $SmartClassCommonLocalDB.".eokul_ders_isimleri");

/* Transcript */
define("_LOCAL_TRANSCRIPT_SETTINGS_", $SmartClassCommonLocalDB.".local_transcript_settings");

/* subjects */
if($dbi->tableExists($dbname, "ders_branslari")) {
    define("_DERS_BRANSLARI_", $dbname.".ders_branslari");
} else {
    define("_DERS_BRANSLARI_", $SmartClassCommonLocalDB.".ders_branslari");
}

/* absence types */
if($dbi->tableExists($dbname, "yoklama_sebebi")) {
    define("_DEVAMSIZLIK_TURLERI_", $dbname.".yoklama_sebebi");
} else {
    define("_DEVAMSIZLIK_TURLERI_", $SmartClassCommonLocalDB.".yoklama_sebebi");
}
