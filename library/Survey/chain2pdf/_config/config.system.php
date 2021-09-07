<?php
//stripped down painpool PDF generating for ISPC

//debug emails
//$GLOBALS['DEBUG_EMAILS'] = array('andrei@adresa.email', 'alexievici@gmail.com');

if(!defined('ABSPATH')) {
    define('ABSPATH',dirname(dirname(__FILE__)));
}


//DB data

define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','test');
define('DB_NAME','ispc_idat');

define('TABLE_PREFIX','survey');
define('SURVEY_TABLE_PREFIX','survey_'); //needed for survey system
define('AUDIT_TABLE_PREFIX','audit');
define('LIB_PATH', ABSPATH.'/_libraries');
define('LOGFILE_MESSAGES', ABSPATH.'/_logs/messages.log');
define('LOGFILE_EMAILS', ABSPATH.'/_logs/emails.log');

//define('DOMAIN', 'http://www.orwdev.com/chain2pdf'); // NO TRAILING SLASH FOR PATHS/URLS CONSTANTS
//define('DOMAIN_S', 'http://www.orwdev.com/chain2pdf');

define('JS_LIB_PATH', DOMAIN.'/_js/_libraries'); // NO TRAILING SLASH FOR PATHS/URLS CONSTANTS
//define('USER_PASSWORD_HASH', '^D%RJI(&*G(&YG');
define('NO_FILLED_DATA', '999');
define('NO_SHOWED_DATA', '777');
//error_reporting(0);
//error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE);





//barmer is used for both frontend and backend

define('BARMER_ID', 9);
define('QUTENZA_ID', 8);
define('MR_ID', 10);


//barmer blocks

$planner_blocks = array(
						'1' => array('name' => 'Physiotherapie', 'amount' => '720', 'class' => 'phsy', 'bgclass' => 'bg-phsy'),
						'2' => array('name' => 'Work-Hardening', 'amount' => '720', 'class' => 'work', 'bgclass' => 'bg-work'),
						'3' => array('name' => 'Ärztliche Untersuchung', 'amount' => '60', 'class' => 'arzt', 'bgclass' => 'bg-arzt'),
						'4' => array('name' => 'Psycho- / Gesprächs- / Verhaltens- Therapie', 'amount' => '240', 'class' => 'pgv', 'bgclass' => 'bg-pgv'),
						'5' => array('name' => 'Entspannungs- training', 'amount' => '240', 'class' => 'ents', 'bgclass' => 'bg-ents'),
						'6' => array('name' => 'Informations- vermittlung', 'amount' => '120', 'class' => 'info', 'bgclass' => 'bg-info'),
						'7' => array('name' => 'Laser-Behandlung', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'8' => array('name' => 'Akupunktur', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'9' => array('name' => 'Biofeedback', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'10' => array('name' => 'Infiltrations- behandlung', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'11' => array('name' => 'Elektrotherapie', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'12' => array('name' => 'Tens- Behandlung', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'13' => array('name' => 'Infusionstherapie', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'14' => array('name' => 'Sonstige', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						);


//tages blocks

$planner_blocks_mr = array(
						'1' => array('name' => 'Physiotherapie', 'amount' => '720', 'class' => 'phsy', 'bgclass' => 'bg-phsy'),
						'2' => array('name' => 'Work-Hardening', 'amount' => '720', 'class' => 'work', 'bgclass' => 'bg-work'),
						'3' => array('name' => 'Ärztliche Untersuchung', 'amount' => '60', 'class' => 'arzt', 'bgclass' => 'bg-arzt'),
						'4' => array('name' => 'Psycho- / Gesprächs- / Verhaltens- Therapie', 'amount' => '240', 'class' => 'pgv', 'bgclass' => 'bg-pgv'),
						'5' => array('name' => 'Entspannungs- training', 'amount' => '240', 'class' => 'ents', 'bgclass' => 'bg-ents'),
						'6' => array('name' => 'Informations- vermittlung', 'amount' => '120', 'class' => 'info', 'bgclass' => 'bg-info'),
						'7' => array('name' => 'Laser-Behandlung', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'8' => array('name' => 'Akupunktur', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'9' => array('name' => 'Biofeedback', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'10' => array('name' => 'Infiltrations- behandlung', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'11' => array('name' => 'Elektrotherapie', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'12' => array('name' => 'Tens- Behandlung', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'13' => array('name' => 'Infusionstherapie', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'14' => array('name' => 'Sonstige', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
						'15' => array('name' => 'Teamkonferenz', 'amount' => '0', 'class' => 'noreq', 'bgclass' => 'bg-noreq'),
);

$daynames_short = array(0 => 'So', 1 => 'Mo', 2 => 'Di', 3 => 'Mi', 4 => 'Do', 5 => 'Fr', 6 => 'Sa');

$GLOBALS['vr_weights'] = array(

	//Module L - 2015 MCS/PCS calculation
	2354 => array (1 => array('mcs' => '-0.00091593', 'pcs' => '0.07825238')), // L1
	2355 => array (1 => array('mcs' => '-0.03549865', 'pcs' => '0.06506401'), 2 => array('mcs' => '-0.03157714', 'pcs' => '0.07483613')), // L2
	2356 => array (1 => array('mcs' => '-0.0251735', 'pcs' => '0.07169783'), 2 => array('mcs' => '-0.02465223', 'pcs' => '0.07415414')), // L4
	2357 => array (1 => array('mcs' => '0.1266861', 'pcs' => '-0.05759826'), 2 => array('mcs' => '0.08087236', 'pcs' => '-0.03226894')), // L7
	2358 => array (1 => array('mcs' => '-0.02437137', 'pcs' => '0.13397491')), // L8
	2536 => array (1 => array('mcs' => '0.1094085', 'pcs' => '-0.04241186')), // L10-copied (L9)
	2361 => array (1 => array('mcs' => '0.06942713', 'pcs' => '0.02996896')), // L10
	2362 => array (1 => array('mcs' => '0.1493789', 'pcs' => '-0.0533624')), // L11
	2363 => array (1 => array('mcs' => '0.10857344', 'pcs' => '0.00460968')), // L12

	//Module L - 2015_copied MCS/PCS calculation
	4201 => array (1 => array('mcs' => '-0.00091593', 'pcs' => '0.07825238')), // L1
	4202 => array (1 => array('mcs' => '-0.03549865', 'pcs' => '0.06506401'), 2 => array('mcs' => '-0.03157714', 'pcs' => '0.07483613')), // L2
	4203 => array (1 => array('mcs' => '-0.0251735', 'pcs' => '0.07169783'), 2 => array('mcs' => '-0.02465223', 'pcs' => '0.07415414')), // L4
	4204 => array (1 => array('mcs' => '0.1266861', 'pcs' => '-0.05759826'), 2 => array('mcs' => '0.08087236', 'pcs' => '-0.03226894')), // L7
	4205 => array (1 => array('mcs' => '-0.02437137', 'pcs' => '0.13397491')), // L8
	4207 => array (1 => array('mcs' => '0.1094085', 'pcs' => '-0.04241186')), // L10-copied (L9)
	4208 => array (1 => array('mcs' => '0.06942713', 'pcs' => '0.02996896')), // L10
	4209 => array (1 => array('mcs' => '0.1493789', 'pcs' => '-0.0533624')), // L11
	4210 => array (1 => array('mcs' => '0.10857344', 'pcs' => '0.00460968')), // L12

	//Modul L-2015_STV
	6412 => array (1 => array('mcs' => '-0.00091593', 'pcs' => '0.07825238')), // L1
	6413 => array (1 => array('mcs' => '-0.03549865', 'pcs' => '0.06506401'), 2 => array('mcs' => '-0.03157714', 'pcs' => '0.07483613')), // L2
	6414 => array (1 => array('mcs' => '-0.0251735', 'pcs' => '0.07169783'), 2 => array('mcs' => '-0.02465223', 'pcs' => '0.07415414')), // L4
	6415 => array (1 => array('mcs' => '0.1266861', 'pcs' => '-0.05759826'), 2 => array('mcs' => '0.08087236', 'pcs' => '-0.03226894')), // L7
	6416 => array (1 => array('mcs' => '-0.02437137', 'pcs' => '0.13397491')), // L8
	6418 => array (1 => array('mcs' => '0.1094085', 'pcs' => '-0.04241186')), // L10-copied (L9)
	6419 => array (1 => array('mcs' => '0.06942713', 'pcs' => '0.02996896')), // L10
	6420 => array (1 => array('mcs' => '0.1493789', 'pcs' => '-0.0533624')), // L11
	6421 => array (1 => array('mcs' => '0.10857344', 'pcs' => '0.00460968')), // L12

	//Modul L-2015-EB MCS/PCS calculation
	9056 => array (1 => array('mcs' => '-0.00091593', 'pcs' => '0.07825238')), // L1
	9057 => array (1 => array('mcs' => '-0.03549865', 'pcs' => '0.06506401'), 2 => array('mcs' => '-0.03157714', 'pcs' => '0.07483613')), // L2
	9058 => array (1 => array('mcs' => '-0.0251735', 'pcs' => '0.07169783'), 2 => array('mcs' => '-0.02465223', 'pcs' => '0.07415414')), // L4
	9059 => array (1 => array('mcs' => '0.1266861', 'pcs' => '-0.05759826'), 2 => array('mcs' => '0.08087236', 'pcs' => '-0.03226894')), // L7
	9060 => array (1 => array('mcs' => '-0.02437137', 'pcs' => '0.13397491')), // L8
	9062 => array (1 => array('mcs' => '0.1094085', 'pcs' => '-0.04241186')), // L10-copied (L9)
	9063 => array (1 => array('mcs' => '0.06942713', 'pcs' => '0.02996896')), // L10
	9064 => array (1 => array('mcs' => '0.1493789', 'pcs' => '-0.0533624')), // L11
	9065 => array (1 => array('mcs' => '0.10857344', 'pcs' => '0.00460968')), // L12
);

define('PCS_additive',21.0468597);
define('MCS_additive',12.6620483);

define('PATIENT_REMOTE_TOKENS', true);

$GLOBALS['complex_types'] = array(
	'freetext' => 'Free text',
	'html' => 'HTML',
	'select' => 'Drop-down',
	'checkbox' => 'Checkbox',
	'checkboxtext' => 'Checkbox text',
	'radio' => 'Radio',
	'radiotext' => 'Radio text'
);

$GLOBALS['prefill_answers'] = [
	'1178' => [ //PSFS_T1_Neu gets results from PSFS_T0_Neu
		'1172' => [
			//destination question => source question
			'9091' => '9067', //Aktivitäten
			'9093' => '9073', //Aktivitäten_1
			'9095' => '9069', //Aktivität_weitere_1
		]
	],
]

?>
