<?php

date_default_timezone_set('Europe/Berlin'); 

define('TEMPLATE',  'melon1');

define('TMP_ABSPATH', ABSPATH.'/_tmp');
define('TEMPLATE_ABSPATH', ABSPATH . '/_templates/_backend/' . TEMPLATE);


/*
define('TEMPLATE', isset($_SESSION['template'])?$_SESSION['template']:'melon1');

define('TEMPLATE_ABSPATH', ABSPATH . '/_templates/_backend/' . TEMPLATE);
define('TEMPLATE_WWWPATH', DOMAIN . '/_templates/_backend/' . TEMPLATE);

define('JS_LIB_ABSPATH', ABSPATH . '/_js/_libraries');

define('JS_PATH', DOMAIN . '/_js/_backend');
define('JS_ABSPATH', ABSPATH . '/_js/_backend');

define('TMP_ABSPATH', ABSPATH.'/_tmp');
define('SURVEYTAKE_DOMAIN', DOMAIN.'/survey'); //path to survey fill URL, defined for device redirection

define('COOKIE_HRS', '8');
define('DEVICE_NO_AUTH', false);

define('FILES_PATH', ABSPATH . '/_files');
define('PDFBG_PATH', FILES_PATH . '/_pdfbg');
define('LETTER_TEMPLATES_PATH', FILES_PATH . '/_templates');
define('LIBREOFFFICE_PATH', '/usr/bin/libreoffice');
define('DOCX_TEMPLATES_PATH', ABSPATH . '/_templates/_docx');
define('ICONS_PATH', FILES_PATH . '/_icons');
define('IMPORT_PATH', FILES_PATH . '/_import');
define('PDFBG_WWWPATH', DOMAIN . '/_files/_pdfbg');
define('LETTER_TEMPLATES_WWWPATH', DOMAIN . '/_files/_templates');
define('ICONS_WWWPATH', DOMAIN . '/_files/_icons');
define('SURVEY_RESULTS_EXPORT_PATH', FILES_PATH . '/_survey_pdf_exports');
define('TEAM_MEETINGS_EXPORTS_PATH', FILES_PATH . '/_team_meetings');

define('SURVEY_DOWNLOADS_TEMPLATES_PATH', FILES_PATH . '/_survey_downloads');
define('SURVEY_DOWNLOADS_WWWPATH', DOMAIN . '/_files/_survey_downloads');

define('LETTERS_PATH', FILES_PATH . '/_letters');
define('LETTERS_WWWPATH', DOMAIN . '/_files/_letters');

define('LETTERS_LOCAL_PATH', ABSPATH.'/_tmp');
define('LETTERS_LOCAL_FILENAME', 'F#%fall_number% D#painPool T#%filename%');
define('LETTERS_LOCAL_PDF', true);

define('GDPR_TEMPLATES_PATH', ABSPATH . '/_templates/_gdpr_templates');

define('TREATMENTS_PATH', FILES_PATH . '/_treatments');
define('TREATMENTS_WWWPATH', DOMAIN . '/_files/_treatments');

define('AVATAR_PATH', FILES_PATH . '/_avatar');
define('AVATAR_WWWPATH', DOMAIN . '/_files/_avatar');

define('PATIENT_AVATAR_PATH', FILES_PATH . '/_patient_avatar');
define('PATIENT_AVATAR_WWWPATH', DOMAIN . '/_files/_patient_avatar');

define('SURVEYTAKE_TEMPLATE_PATH', ABSPATH . '/_templates/_survey');
define('SURVEYTAKE_TEMPLATE_WWWPATH', DOMAIN . '/_templates/_survey');
define('SURVEYTAKE_JS_PATH', DOMAIN . '/_js/_survey');
define('SURVEY_SYSTEM_PATH', ABSPATH . '/_survey-admin');
define('DEVICE_URL', DOMAIN . '/admin/mobile.php');
define('EXPORT_INGORE_DATA_USAGE', true);

define('CRON_HASH', 'yCtrXchbnpNygvtfcyg');

define('HTTP_PROXY', null);

define('YUBIKEY_ACTIVE', false);

define('YUBICO_CLIENT_ID', '7595');
define('YUBICO_CLIENT_KEY', 'FxlQEhHQ8cPRGjbDnyv4u/6u9Uo=');
define('YUBICO_URLS', 'api.yubico.com/wsapi/2.0/verify,api2.yubico.com/wsapi/2.0/verify,api3.yubico.com/wsapi/2.0/verify,api4.yubico.com/wsapi/2.0/verify,api5.yubico.com/wsapi/2.0/verify');

define('NO_FILLED_DATA','-');

define('SESSION_TIME', 1800);

define('CHARTEXPORT_PATH',  ABSPATH . '/_chart_export');
define('CHARTEXPORT_WWW_PATH',  DOMAIN . '/_chart_export');

define('MAX_ATTACHMENTS', 5);
define('MAX_ATTACHMENT_SIZE_MB', 5);
define('MAX_EMAIL_RETRY', 5);
define('MAX_CHART_LABEL_COUNT', 100);

define('MAX_SURVEY_FILL_ACTION_AGE', 30);

$GLOBALS['ALLOWED_MESSAGE_ATTACHMENT_EXTENSIONS'] = array("doc","docx", "xls", "xlsx", "pdf", "jpg", "png", "gif", "bmp");

/* Practice message type 

$practice_message_types = array('survey_email','welcome_email','daily_survey_email','underage_lg','underage_child','underage_parent_welcome','custom');

//practice features
//$GLOBALS['practice_rights'] = array(2,3,4,5,6,26,43,11,10,34,99);
//$GLOBALS['practice_softlogin'] = array(2);

//Barmer treatment days
define('BARMER_DAYS', 20);

//Barmer target minutes
define('BARMER_MINUTES', 3840);


//Projects for patients lists
$project_quetenza = "qutenza";
$project_barmer = "barmer";



/*define('SCHMERZ_ID', 93);
define('VERLAUF_ID', 80);
define('TAGES_ID', 85);*/

define('SCHMERZ_ID', 113);
define('VERLAUF_ID', 114);
define('TAGES_ID', 115);

$GLOBALS['quickstart'] = array(
					SCHMERZ_ID => 'Schmerzfragebogen V3.1',
					VERLAUF_ID => 'Verlaufsbogen V3',
					TAGES_ID => 'Tagesprotokoll V3'
);

$GLOBALS['new_scores_map'] = array (
							'69' => array('39','41','47'), //NRS
							'82' => array('39','41','47'), //NRS
							'63' => array('46','13'), //Korff
							'81' => array('46','13'), //Korff Tagesprotokoll
							'85' => array('29'), //QLIP
							'50' => array('29'), //QLIP
							'75' => array('25'), //SBLS
							'76' => array('26'), //SBLA
							'53' => array('21','16'), //Depression
							'54' => array('22','17'), //Angst
							'55' => array('23','18'), //Stress
							'56' => array('11','24') //FW7
								);

//score IDs needed everywhere

$GLOBALS['new_scores_ids'] = array(
							'nrs' => array(69,82),
							'korff' => array(63,81),
							'qlip' => array(50,85),
							'slbs' => array(75),
							'slba' => array(76),
							'depression' => array(53),
							'angst' => array(54),
							'stress' => array(55),
							'fw7' => array(56)
							
							);

//survey IDs needed everywhere

$GLOBALS['new_surveys_ids'] = array(
							'nrs' => array(138,141),
							'korff' => array(137,141),
							'qlip' => array(142,117),
							'slbs' => array(140),
							'slba' => array(140),
							'depression' => array(123),
							'angst' => array(123),
							'stress' => array(123),
							'fw7' => array(124)
								
					);


//survey IDs needed everywhere

$GLOBALS['all_scores_map'] = array(
							'nrs' => array(69,82,39,41,47, 159, 172, 182, 280, 276, 418, 429, 403, 663),
							'korff' => array(63,81,46,13, 171, 269, 417, 434, 402),
							'qlip' => array(50,85,29, 422),
							'slbs' => array(75,25,166, 177, 393, 423, 665),
							'slba' => array(76,26,167, 178, 394, 424, 666),
							'depression' => array(53,21,16,174,184,201, 396, 437),
							'angst' => array(54,22,17,175,185, 202, 397, 438),
							'stress' => array(55,23,18,176,186, 203, 398, 439),
							'fw7' => array(56,11,24, 173, 395, 436),
                            'nrsm' => array(192,193, 258, 183, 271, 430, 404, 664),
							'mpss' => array(12,34),
							'mcs' => array(196, 200, 442,676),
							'pcs' => array(195, 199, 441,675),
							'mpss1' => array(7,30),
							'mpss3' => array(9,32),
							'mpss4' => array(10,33),
							'rm' => array(205),
							'fess' => array(260),
							'ffbhr' => array(207, 408, 451),
							'cpaqd' => array(208),
							'odi' => array(209),
							'pdi' => array(6),
							'tsk' => array(627),
							'psfs' => array(691)

);


//health insurance statuses

$GLOBALS['hi_statuses'] = array(
		'1' => 'health insurance member',
		'3' => 'health insurance family member',
		'5' => 'health insurance retired'
		
);

