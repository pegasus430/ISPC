<?php
// phpinfo();
// die();
// set_time_limit(0);

if (isset($_REQUEST['test'])) error_reporting(E_ALL);
defined('DEBUGMODE') || define('DEBUGMODE', true);



$appStartTime = microtime(true);
if(isset($_REQUEST['test']) && $_REQUEST['test'] == 1)
{
    //error_reporting(E_ALL);
}
else
{
    //error_reporting(E_ERROR);
    //error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);
    //5.4.0         E_STRICT became part of E_ALL.
}
error_reporting(E_ALL);
//      Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
//      defined('APPLICATION_PATH') || define('APPLICATION_PATH', '/var/www/ispc/application');

/**
 * it should allways end in folder uploads ..., this "uploads" string is hardcoded in phps
 * this should not be change to not brake the app,
 * this constant is alo in pdf-privatrezept.php ... remember to change there also
 */
defined('PDF_PATH') || define('PDF_PATH', dirname(APPLICATION_PATH) . '/public/uploads');


defined('PUBLIC_PATH') || define('PUBLIC_PATH', dirname(APPLICATION_PATH) . '/public');
defined('PDFBG_PATH') || define('PDFBG_PATH', dirname(APPLICATION_PATH) . '/public/pdfbackgrounds');
defined('BRIEF_TEMPLATE_PATH') || define('BRIEF_TEMPLATE_PATH', dirname(APPLICATION_PATH) . '/public/brief_templates');
defined('MEMBER_LETTER_TEMPLATE_PATH') || define('MEMBER_LETTER_TEMPLATE_PATH', dirname(APPLICATION_PATH) . '/public/member_letter_templates');
defined('VW_LETTER_TEMPLATE_PATH') || define('VW_LETTER_TEMPLATE_PATH', dirname(APPLICATION_PATH) . '/public/vw_letter_templates');
defined('INVOICE_TEMPLATE_PATH') || define('INVOICE_TEMPLATE_PATH', dirname(APPLICATION_PATH) . '/public/invoice_templates');
defined('PDFDOCX_PATH') || define('PDFDOCX_PATH', dirname(APPLICATION_PATH) . '/public/pdfdocx_files');
defined('PDFJOIN_PATH') || define('PDFJOIN_PATH', dirname(APPLICATION_PATH) . '/public/joined_files');
defined('DOCX_TEMPLATE_PATH') || define('DOCX_TEMPLATE_PATH', dirname(APPLICATION_PATH) . '/public/docx_templates');
defined('REMINDERINVOICE_TEMPLATE_PATH') || define('REMINDERINVOICE_TEMPLATE_PATH', dirname(APPLICATION_PATH) . '/public/reminder_invoice_templates');
//      echo APPLICATION_PATH;exit;
defined('PUBLIC_PATH') || define('PUBLIC_PATH', dirname(APPLICATION_PATH) . '/public');

/**
 * FTP_QUEUE_PATH
 *
 * temporary path used to hold the ziped files, before put to ftp by a cronjob
 */
defined('FTP_QUEUE_PATH') || define('FTP_QUEUE_PATH', APPLICATION_PATH."/../ftp_put_cron");

/**
 * CLIENTUPLOADS_PATH
 *
 * on ftp is hardcoded to: clientuploads
 *
 * used in MiscController, MedicationController
 */
defined('CLIENTUPLOADS_PATH') || define('CLIENTUPLOADS_PATH', dirname(APPLICATION_PATH) . '/public/clientuploads');

/**
 * FTP_DOWNLOAD_PATH
 *
 * temporary path used to hold the ziped and unzipped files, from the download requests
 */
defined('FTP_DOWNLOAD_PATH') || define('FTP_DOWNLOAD_PATH', APPLICATION_PATH."/../ftp_download");



/**
 * MEPATIENT_TEMP_IMAGES
 *
 * temporary path for images grabbed from mepatient
 */
defined('MEPATIENT_TEMP_IMAGES') || define('MEPATIENT_TEMP_IMAGES', APPLICATION_PATH."/../mepatient_images");



//Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../library'),
    realpath(dirname(__FILE__) . '/../library/PHPExcel'),
    realpath(dirname(__FILE__) . '/../library/Doctrine'),
    realpath(dirname(__FILE__) . '/../library/Pms'),
    realpath(dirname(__FILE__) . '/../library/Zend'),
    realpath(APPLICATION_PATH . '/Triggers'),
    //              realpath(APPLICATION_PATH . '/models/doctrine/models'),
//              realpath(APPLICATION_PATH . '/models/doctrine/generated'),
//              realpath(dirname(__FILE__).'/models'),
//              realpath(dirname(__FILE__).'/models/generated'),
    get_include_path()
)));
require_once 'Phpdocx/classes/CreateDocx.inc';
require_once 'dompdf/autoload.inc.php';

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();


//      Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'staging'));

if(isset($_COOKIE['IGUser']))
{
    define('APP_BASE', 'http://ispc.sqdev.de/public/');
}
else
{ //xss
    define('APP_BASE', 'http://ispc.sqdev.de/public/');
}

if (! function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if ( ! isset($value[$columnKey])) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            }
            else {
                if ( ! isset($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if ( ! is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}


if (! function_exists('split')) {
    function split(string $pattern , string $string,  int $limit = PHP_INT_MAX) {
        $array = explode ( $pattern , $string ,$limit );
        return $array;
    }
}
//      echo get_include_path();exit;
//      echo realpath(dirname(__FILE__) . '/../library');
//      exit;
//      echo realpath(dirname(__FILE__).'../../application/models/doctrine/models/');

/** Zend_Application */
//      require_once 'Doctrine/Doctrine.php';
//      require_once 'Pms/Pms.php';
//      require_once 'Zend/Application.php';


//      Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap()->run();
//      global $appStartTime;
//      $generateTime = (microtime(true) - $appStartTime);
//
//      echo $generateTime;
//      if($generateTime > 3)
    //      {
    //              $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/times.log');
    //              $log = new Zend_Log($writer);
    //              if($log)
        //              {
        //                      $log->warn('Long page load time of ' . $generateTime . ' on ' . Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        //              }
        //      }
        $appTotalTime =   microtime(true) - $appStartTime;
        echo $appTotalTime;
        