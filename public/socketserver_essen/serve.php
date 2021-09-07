<?php

if ('cli' != php_sapi_name()) {
    die();
}

error_reporting(E_ERROR);

$appStartTime = microtime(true);
set_time_limit(0);

$currentdir = str_replace('public/socketserver_essen', 'application', dirname(__FILE__));

defined('APPLICATION_PATH') || define('APPLICATION_PATH', $currentdir);
defined('PDF_PATH') || define('PDF_PATH', dirname(APPLICATION_PATH) . '/../public/uploads');
defined('PDFBG_PATH') || define('PDFBG_PATH', dirname(APPLICATION_PATH) . '/../public/pdfbackgrounds');
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../../library'),
    realpath(dirname(__FILE__) . '/../../library/Doctrine'),
    realpath(dirname(__FILE__) . '/../../library/Pms'),
    realpath(dirname(__FILE__) . '/../../library/Zend'),
    realpath(APPLICATION_PATH . '/Triggers'),
    get_include_path()
)));

require_once 'Phpdocx/classes/CreateDocx.inc';
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

define('APP_BASE', '');

$config = new Zend_Config(require APPLICATION_PATH . '/configs/config.php');

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    $config
);
$application->bootstrap();
error_reporting(E_ERROR);

//the socket config
$conf = require('config.php');

// starting the server
$serv = new Net_SocketServerEssen($conf);

//if argument given, read file and make testrun without real server but 
//use contents of file as message
if (isset($argv[1]) && !is_numeric($argv[1])) {
    $req = "";
    $handle = fopen($argv[1], "r");
    while (!feof($handle)) {
        $buffer = fgets($handle);
        $req .= $buffer . "\r";
    }
    fclose($handle);

    $serv->processHL7Msg($req);

    global $appStartTime;
    $generateTime = (microtime(true) - $appStartTime);
    //echo "Runtime: " . $generateTime ."s\n";
} else {
    //real server
    $serv->startServer($argv[1]);
}
?>
