<?php
error_reporting(E_ALL);

$options = [
    'host'      => $_REQUEST['host'] ?? null,
    'port'      => $_REQUEST['port'] ?? null,
    'message'   => isset($_REQUEST['message']) ? base64_decode($_REQUEST['message']) : null,
    '_hash'     => $_REQUEST['_hash'] ?? null,
];

$options = array_filter($options);


if ($_SERVER['HTTP_USER_AGENT'] != 'Zend_Http_Client-ISPC-HL7-CURL' 
    || sizeof($options) !== 4 
    || $options['_hash'] != hash("crc32b", $options['message'] . $options['host'] . $options['port'])) 
{    
    die('horror, something is wrong with this IF, dev take a look');
}


function __autoload($class_name)
{
    $class_name = str_replace("_", "/", $class_name);
    $path = $class_name . '.php';
    include $path;
}

set_include_path(dirname(__FILE__) ."/../../library");



$hl7_client = new Net_HL7_Connection($options['host'], $options['port']);   
$out = $hl7_client->send($options['message']);
$hl7_client->close();

echo $out;
exit;    
