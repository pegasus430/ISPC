<?php
function __autoload($class_name)
{
    $class_name = str_replace("_", "/", $class_name);
    $path = $class_name . '.php';
    include $path;
}


/*
 * Starts Socket-server
 */
function startServer($port, $ipaddress)
{
    global $msgsock;

    if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
        printlog("socket_create() failed: " . socket_strerror(socket_last_error()) . "\n", 2);
        exit();
    }

    if (socket_bind($sock, $ipaddress, $port) === false) {
        printlog("socket_bind() failed: " . socket_strerror(socket_last_error($sock)) . "\n", 2);
        exit();
    }

    if (socket_listen($sock, 5) === false) {
        printlog("socket_listen() failed: " . socket_strerror(socket_last_error($sock)) . "\n", 2);
        exit();
    }

    //do{
    printlog("waiting for connection...", 1);
    //Timeout after 600 secs = 10 Minutes.
    switch (@socket_select($r = array($sock), $w = array($sock), $e = array($sock), 600)) {
        case 2:
            printlog("Connection refused", 2);
            break;
        case 1:
            printlog("Connection accepted", 1);
            $msgsock = @socket_accept($sock);
            break;
        case 0:
            printlog("Connection timed out", 1);
            break;
    }
    $loops = 0;
    $maxloops = 1;
    do {
        $loops++;
        if ($msgsock !== false) {

            printlog('loop: ' . $loops . '/' . $maxloops, 1);
            $maxtime = time() + 60 * 5;//5min
            $req = "";
            do {
                $buf = "";
                //if (false === ($buf = socket_read ($msgsock, 30, PHP_BINARY_READ))) {
                if (false === ($state = socket_recv($msgsock, $buf, 30, MSG_DONTWAIT))) {
                    if (time() > $maxtime) {
                        break;
                    };
                    if ('Resource temporarily unavailable' != socket_strerror(socket_last_error($msgsock))) {
                        printlog(socket_strerror(socket_last_error($msgsock)) . "\n", 2);
                        socket_close($sock);
                        exit();
                    } else {
                        sleep(5);
                    }
                }
                if (!empty($buf)) {
                    $req .= $buf;
                    if (strlen($req) > 50000) {
                        $req = "";
                        printlog("Message received was too long.", 2);
                        exit();
                    }
                    if (strpos($buf, chr(28)) !== false) {
                        $i = strpos($req, chr(28));
                        $req = substr($req, 0, $i);
                        break;
                    }
                }

            } while (true);

            if ($port == 12099) {
                //socket plaintext-test
                echo $req;
            } else {
                //convert to utf8
                $req = utf8_encode($req);
                printlog("Start request prcess", 1);
                //here we process the request
                if (strlen(trim($req)) > 1) {
                    processHL7Msg($req);
                }
            }
        }
    } while ($loops < $maxloops);
    printlog("Reestablishing connection in few minutes.", 1);

    socket_close($sock);
    exit();

}


/*
 * parse message to create ack-message, save message to file
 */
function processHL7Msg($req)
{
    global $port;
    try {
        if (strlen(trim($req)) < 9 || substr(trim($req), 0, 3) != "MSH") throw new Exception("Message to short or not starting with MSH");
        $message = new Net_HL7_Message(trim($req));
        printlog("Received:\n" . $message->toString(1), 1);
    } catch (Exception $e) {
        printlog($e->getMessage(), 2);
        $msgType = "ERROR";
        $fname = dirname(__FILE__) . "/messages/err_" . $port . "_" . time() . ".txt";

        $handle = fopen($fname, "w");
        fwrite($handle, $req);
        fclose($handle);
        return;
    }


    $msh = $message->getSegmentsByName('MSH');
    $id = $msh[0]->getField(7);
    $fname = dirname(__FILE__) . "/messages/" . $port . "_" . $id . "_" . time() . ".txt";

    $handle = fopen($fname, "w");
    fwrite($handle, $message->toString(1));
    fclose($handle);

    $cmd = "php " . dirname(__FILE__) . "/serve.php " . $fname;
    if ($port == 12004) $cmd = "php serve_test.php " . $fname;

    $ackMsg = new Net_HL7_Messages_ACK($message);
    sendACK($ackMsg);

    $out = shell_exec($cmd);

    printlog($out, 1);


}


function printlog($msg, $level)
{
    global $port;
    if ($level > -1)
        echo "[" . date('Y-m-d H:i:s') . " # Port:" . $port . "]>" . $msg . "\r\n";
}

/**
 * Sends ACK for ACK-Message
 * No matter if error or not, error-code is always "AA"
 * because LMU can't handle other events than AA
 * if simulate is not false, no message is sent for real - just simulate
 */
function sendACK($ackMsg, $simulate = false)
{
    global $msgsock;
    $level = 2;
    //if ($ackMsg->getErrorMessage()) $level=2;
    printlog("Sending ACK:\n" . $ackMsg->toString(1), $level);
    $ackMsg->setErrorMessage('');
    if ($simulate == false) {
        socket_write($msgsock, "\013" . $ackMsg->toString() . "\034\015");
    }
}


// Set time limit to indefinite execution
if ('cli' != php_sapi_name()) {
    die();
}
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(E_ERROR);
set_time_limit(0);
set_include_path("../../library");

$ipaddress = "0.0.0.0";
$port = intval($argv[1]);
$msgsock = false;

$req = "";

startServer($port, $ipaddress);


echo("\n  --exit--  \n");

?>
