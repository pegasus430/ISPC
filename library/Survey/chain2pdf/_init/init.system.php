<?php
$start = microtime(true);
//error_reporting(E_ALL &  ~E_STRICT &  ~E_NOTICE);
//error_reporting(0); //production

if(defined('ABSPATH')) {
    require_once(ABSPATH. '/_config/config.system.php');
} else {
    require_once('_config/config.system.php');
}

date_default_timezone_set('Europe/Berlin');
setlocale(LC_ALL, 'de_DE.utf8', 'de_DE.UTF-8', 'de_DE', 'de_DE@euro', 'deu_deu'); 

//require_once('init.error_logger.php');



/**********************************************************************
*  ezSQL initialisation for mySQL
*/

// Include ezSQL core
require_once (LIB_PATH.'/_system/ezSQL/shared/ez_sql_core.php');

// Include ezSQL database specific component
require_once (LIB_PATH.'/_system/ezSQL/mysqli/ez_sql_mysqli.php');

require_once (LIB_PATH.'/_system/ezSQL/custom/ez_sql_survey.php');

// Initialise database object and establish a connection
// at the same time - db_user / db_password / db_name / db_host
$GLOBALS['db'] = new ezSQL_survey(DB_USER,DB_PASS,DB_NAME,DB_HOST);

// Include generic functions needed by all files
require_once (LIB_PATH.'/_system/functions.php');


// Include language file
//require_once (ABSPATH.'/_languages/default.php');

?>
