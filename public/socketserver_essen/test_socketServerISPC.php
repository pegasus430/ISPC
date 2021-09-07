#!/usr/bin/env php
<?php
error_reporting(E_ALL);
/**
 * HL7 test client
 * hardcoded port 1000
 */



/*
 * get the port as param
 */
$shortopts  = "";
$longopts  = array("port:", "host:");
$options = getopt($shortopts, $longopts);

if (empty($options['port']) || empty($options['host'])) {
    echo ( PHP_EOL . "Usage: " . __FILE__ . " --host 'host_address or ip' --port 'socket_port'" . PHP_EOL);
    die();
}

$service_port = $options['port'];

$address = $options['host'];
$address = gethostbyname($address);

echo ( PHP_EOL . "USING : host {$address} port {$service_port}" . PHP_EOL);


// $service_port = 12888;

/* Get the port for the WWW service. */
//$service_port = getservbyname('www', 'tcp');


/* Get the IP address for the target host. */
// $address = gethostbyname('localhost');
// $address = gethostbyname("10.0.0.21");
// $address = gethostbyname("dev.smart-q.de");


function __autoload($class_name)
{
    $class_name = str_replace("_", "/", $class_name);
    $path = $class_name . '.php';
    include $path;
}

set_include_path(dirname(__FILE__) ."/../../library");


echo "<h2>TCP/IP Connection</h2>" . PHP_EOL;



/* Create a TCP/IP socket. */
//$hl7_client = new Net_HL7_Connection($address, $service_port);

$out = '';

define('HL7_MESSAGE_PREFIX' ,  chr(11) ); //"\013";
define('HL7_MESSAGE_SUFFIX' ,  chr(28) . chr(13)); //"\034\015";


$messages = [];

for ($messagecoubtet =1; $messagecoubtet<4; $messagecoubtet++) {
    
    $tstmsg_ID =  rand(1,5);
    $tmsg = file_get_contents(dirname(__FILE__) . "/testmessages/tmsg_{$tstmsg_ID}.txt");
    
    $message = new Net_HL7_Message($tmsg);
    
    echo "send tmsg_ {$tstmsg_ID} request..." . PHP_EOL;
    echo $message->toString(1);
    
//     $messages[] = $message->toString(1);
    $hl7_client = new Net_HL7_Connection($address, $service_port);
    $out = $hl7_client->send($message);
    echo "response:\n\n";
    echo $out;
    
    echo PHP_EOL;
    echo PHP_EOL;
    

}


// $out = $hl7_client->justSend(  implode( HL7_MESSAGE_SUFFIX . HL7_MESSAGE_PREFIX, $messages));
// echo "response:\n\n";
// echo $out;



echo PHP_EOL;
echo PHP_EOL;

echo "Closing client socket..." . PHP_EOL;
$hl7_client->close();


echo "OK.\n\n";
?>