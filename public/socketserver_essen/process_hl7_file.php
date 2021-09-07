<?php
/*
 * for the original file please 
 * @see serve.php
 * @author Nico  
 * 
 * 
 */

/**
 * 
 * @author claudiu 
 * Jun 20, 2018
 * 
 */
if ('cli' != php_sapi_name()) {
    die();
}

$appStartTime = microtime(true);

$shortopts  = "f:p:";
$longopts  = array(
    "hl7_file:",    // Required value
    "port:",        // Required value
);
$options = getopt($shortopts, $longopts);

if (empty($options['hl7_file']) || empty($options['port'])) {
    echo ( PHP_EOL . "Usage: " . __FILE__ . " --hl7_file '/filepath' --port 'socket_port'" . PHP_EOL);
    die();
}

$socket_port = ! empty($options['port']) ? $options['port'] : $options['p'] ;
$hl7_file = ! empty($options['hl7_file']) ? $options['hl7_file'] : $options['f'] ;


set_time_limit(360); //??? why do you need this?

$currentdir = str_replace('public/socketserver_essen', 'application', dirname(__FILE__));

defined('APPLICATION_PATH') || define('APPLICATION_PATH', $currentdir);

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

if (APPLICATION_ENV == 'development') {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}


set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../../library'),
    realpath(dirname(__FILE__) . '/../../library/Doctrine'),
    realpath(dirname(__FILE__) . '/../../library/Pms'),
    realpath(dirname(__FILE__) . '/../../library/Zend'),
    realpath(APPLICATION_PATH . '/Triggers'),
    get_include_path()
)));

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap();

echo "hl7_file : {$hl7_file}" . PHP_EOL;
echo "socket_port : {$socket_port}" . PHP_EOL;

if (Zend_Registry::isRegistered('HL7') && ($hl7_cfg = Zend_Registry::get("HL7")) ) {
    
    
    if (is_numeric($socket_port) && isset($hl7_cfg[$socket_port])) {
        
        $conf = $hl7_cfg[$socket_port];
        
        $serv = new $conf['class']($conf);
        
        if (is_file($hl7_file)) {
            
            $req = "";
            
            if ($handle = fopen($hl7_file, "r")) {
                
                while ( ! feof($handle)) {
                    
                    $buffer = fgets($handle);
                    
                    $req .= $buffer . "\r";  // !!!!
                }
                
                fclose($handle);
            }
        
            if ( ! empty($req)) {
                $serv->processHL7Msg($req);
            }
        
            $generateTime = (microtime(true) - $appStartTime);
            
            echo "Runtime: {$generateTime}s" . PHP_EOL;
            
        } else {
            
            die('missing hl7 file' . PHP_EOL);
            
        }
        
    } else {
        //something wrong with call or cfg
        die('missing from config.ini' . PHP_EOL);
    }
    
    
}

?>