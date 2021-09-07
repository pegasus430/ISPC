#!/usr/bin/env php
<?php
/**
 * @author claudiu 
 * Jun 20, 2018
 *
 * this is a re-write from @author Nico
 * @see socketserve.php for the original
 *
 * this file requires process_hl7_file.php
 * 
 * @update 02.07.2018
 * - removed instant-process the message, file process_hl7_file.php no longer mandatory
 * + just saving messagess to a db , from where will be fetched by a master-server
 * 
 * 
 * 
 * read this if you need infos on HL7 structure: 
 * https://corepointhealth.com/resource-center/hl7-resources/
 * https://corepointhealth.com/resource-center/hl7-resources/hl7-msh-message-header/
 * https://corepointhealth.com/resource-center/hl7-resources/hl7-batch-file-protocol/
 * ...
 * *Note: For the complete HL7 Standard, please go to the HL7 organization website. https://www.hl7.org/
 */

if ('cli' != php_sapi_name()) {
    die();
}

/*
 * match this to HL7server.ini of the master
 * XXXX.doctrine.dsn XXXX.encrypt_key
 * if this is from maser ISPC app server, and no extra db is needed, set fetched_by_master => 'YES'
 */
$mysql_cfg = array(
    'host' => 'localhost',
    'db' => 'ispc_testDB',
    'user' => 'root',
    'pass' => 'test',
    'charset' => 'utf8',
     
    'encrypt_key' => "KEy_wordpress",

    'fetched_by_master' => 'NO' //optional, set this to YES if you use the script on ISPC main server (you have no extra socket-server, no extra db)
);
/*
 CREATE TABLE hl7_messages_received (
 messages_received_id INT AUTO_INCREMENT,
 client_id INT,
 port INT,
 message LONGBLOB,
 fetched_by_master ENUM('NO', 'YES') DEFAULT 'NO' NOT NULL,
 external_messages_received_id INT,
 create_date DATETIME,
 change_date DATETIME,
 create_user INT,
 change_user INT,
 INDEX client_id_idx (client_id),
 INDEX fetched_by_master_idx (fetched_by_master),
 INDEX port_idx (port),
 PRIMARY KEY(messages_received_id)
 ) ENGINE = INNODB;
 */



/*
 * default is 0 or 0.0.0.0 for localhost
 */
$ipaddress = "0.0.0.0";

/*
 * use true for debug, false for production
 * errors are logged regardless
 */
$printLog = true;


/*
 * HL7 defaults
 * _MESSAGE_PREFIX = "\013";
 *  \013 chr(11) VT	Vertical Tab
 *
 * _MESSAGE_SUFFIX = "\034\015";
 *  \034 chr(28) FS	File Separator
 *  \015 chr(13) CR	Carriage Return
 *
 *  https://www.joecolantonio.com/2011/07/26/qtp-ascii-chr-code-chart/
 *
 *  perl client discussion and ex => https://www.perlmonks.org/?node_id=535845
 */
define('HL7_MESSAGE_PREFIX' ,  chr(11) ); //"\013";
define('HL7_MESSAGE_SUFFIX' ,  chr(28) . chr(13)); //"\034\015";

set_include_path(dirname(__FILE__) . "/../../library");

function __autoload($class_name)
{
    $class_name = str_replace("_", "/", $class_name);
    $path = $class_name . '.php';
    include $path;
}

/*
 * get the port as param
 */
$shortopts  = "";
$longopts  = array("port:");
$options = getopt($shortopts, $longopts);

if (empty($options['port'])) {
    echo ( PHP_EOL . "Usage: " . __FILE__ . " --port 'socket_port'" . PHP_EOL);
    die();
}

$port = $options['port'];









/*
 * ISPCSocketServer is a copy of MySocketServer from http://php.net/manual/ro/sockets.examples.php
 * 
 * it was then modified to for HL7 process
 * 
 * TODO : catch interrupt system call
 * if lib pcntl then use pcntl_signal(SIGINT, 'signalHandler');
 */
class ISPCSocketServer
{
    protected $socket;
    protected $clients = [];
    protected $changed;
    protected $host;
    protected $port;
    protected $showlog = true;
    
    private $_mysql_cfg = array();
    
    
   
    function __construct($host = '0', $port = 0, $mysql_cfg = array(), $showlog = true)
    {
        set_time_limit(0);
        
        if (($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
            exit;
        }
        
        //socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        //bind socket to specified host
        if (socket_bind($socket, $host, $port) === false) {
            echo "socket_bind() failed for {$host}:{$port} reason: " . socket_strerror(socket_last_error($socket)) . "\n";
            exit;
        }

        //listen to port
        //sudo sysctl -a | grep somaxconn  => default 128
        if (socket_listen($socket, 5) === false) { 
            echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($socket)) . "\n";
            exit;
        }
        
        $this->socket = $socket;
        
        $this->host = $host;
        $this->port = $port;
        $this->showlog = $showlog;
        
        $this->_mysql_cfg = $mysql_cfg;
        
        $this->printlog('socket OK, waithing for clients on port '. $this->port);
    }

    function __destruct()
    {
        foreach($this->clients as $client) {
            socket_close($client);
        }
        @socket_close($this->socket);
    }
   
    
    function run()
    {
        while(true) {
            $this->waitForChange();
            $this->checkNewClients();
            $this->checkMessageRecieved();
            $this->checkDisconnect();
        }
    }
   
    function checkDisconnect()
    {
        foreach ($this->changed as $changed_socket) {
            $buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
            if ($buf !== false) { // check disconnected client
                continue;
            }
            // remove client for $clients array
            $found_socket = array_search($changed_socket, $this->clients);
            //@socket_getpeername($changed_socket, $ip);
            unset($this->clients[$found_socket]);
            
            $response = "[client #{$found_socket} has disconnected]";
            $this->printlog($response);
            $this->printlog("==========\n");
            //$this->sendMessage($response);
        }
    }
   
    function checkMessageRecieved()
    {
        foreach ($this->changed as $key => $socket) {
            
            $buffer = null;
            
            $this->printlog(__FUNCTION__, 7);
            
            $this->readHL7Message($socket);
            
        }
    }
   
    function waitForChange()
    {   
        //reset changed
        $this->changed = array_merge([$this->socket], $this->clients);
        //variable call time pass by reference req of socket_select
        $null = null;
        //this next part is blocking so that we dont run away with cpu
        socket_select($this->changed, $null, $null, null);
    }
   
    function checkNewClients()
    {
        if ( ! in_array($this->socket, $this->changed)) {
            return; //no new clients
        }
        
        //accept new socket
        if (($socket_new = socket_accept($this->socket)) !== false) {
                
            $this->clients[] = $socket_new;
            
            unset($this->changed[0]);
            
            @socket_getpeername ( $socket_new , $ip );
            
            $keys = array_keys($this->clients);
            $cid = end($keys);
            
            $this->printlog("\n==========");
            $this->printlog("[A new client #{$cid} has connected from {$ip}]");
            
        } else {
            
            $this->printlog("ERROR socket_accept(): " . socket_strerror(socket_last_error($this->socket)), 0);
        }
    }
   
    
    /**
     * 
     * @param socket resource $client
     */
    function readHL7Message($client) 
    {
        $this->printlog(__FUNCTION__, 7);
        
        $maxtime = time() + 60 * 1;//1min
        
        $data = "";
        
        do {
            
            $buf = "";
            
            if (false === ($state = socket_recv($client, $buf, 30, MSG_DONTWAIT))) {
                
                if (time() > $maxtime) {
                    
                    $found_socket = array_search($client, $this->clients);
                    
                    $this->printlog( "hl7 ERROR: #{$found_socket} maxtime exceeded, still receiving" . "\n", 0);
                    
                    break;
                };
        
                if ('Resource temporarily unavailable' != socket_strerror(socket_last_error($client))) {
        
                    $this->printlog(socket_strerror(socket_last_error($client)) . "\n", 0);
                    
                    socket_close($client);

                    return; //error for readHL7Message - socket problem
        
                } else {
        
                    sleep(1);
                }

                
            } elseif (time() > $maxtime) {
                
                $found_socket = array_search($client, $this->clients);
                
                $this->printlog( "hl7 BIG ERROR: #{$found_socket} maxtime exceeded on else state = {$state}" . "\n", 0);

                socket_close($client);
            
                return; //error for readHL7Message - socket timeout
            }
            

        
            if ( ! empty($buf)) {
        
                $data .= $buf;
        
                if (strlen($data) > 50000) {
                    
                    $data = "";
                    
                    $this->printlog("hl7 ERROR: Message received was too long." , 0);
                    
                    socket_close($client);
                    
                    return; //error for readHL7Message - message has not suffix.. or very long
                    
                } elseif(preg_match("/" . HL7_MESSAGE_SUFFIX . "$/", $data)) {
                   
                    //$HL7string = substr($data, 0, strpos($data, HL7_MESSAGE_SUFFIX));
                    
                    $HL7string = $data;
                    
                    $HL7string = preg_replace("/^" . HL7_MESSAGE_PREFIX . "/", "", $HL7string);
                    $HL7string = preg_replace("/" . HL7_MESSAGE_SUFFIX . "$/", "", $HL7string);
                    
                    $resultMsg = $this->processHL7Message($HL7string);
                    
                    if ( ! empty($resultMsg['errorMsg'])) {
                        $this->printlog("sending this error ACK Response: " . $resultMsg['ackMsg'], 0);
                    }
                    
                    @socket_write($client, $resultMsg['ackMsg'] , strlen($resultMsg['ackMsg']));
                    
                    break;
                }
            }
        
        } while (true);
        
        
        //fail-safe;
        socket_close($client);
        return;
    }

    
       
    
    
    
 
    public function processHL7Message($req = '')
    {      
        $this->printlog(__FUNCTION__, 7);
        
        $result = [
            'ackMsg' => null,
            'errorMsg' => null,
        ];
        
        /**
         * Net_HL7_Messages_ACK
         */
        $ackMsg =  null;
        
        
        //Net_HL7_Messages can throw errors .. so we catch them here
        try{
            //convert to utf8
            $req = utf8_encode($req);
            
            $message = new Net_HL7_Message(trim($req));
            
            if ($this->_saveMessage2DB($message->toString(1)) !== true) {
            
                $result['errorMsg'] = "ISPC database is down, please try resubmiting later" ;
                
                $this->printlog($result['errorMsg'], 0);
                
                $ackMsg = new Net_HL7_Messages_ACK($message);//Construct an ACK based on the request
                $ackMsg->setAckCode("R", $result['errorMsg']);
                
            
            } else {
                //all OK
                $ackMsg = new Net_HL7_Messages_ACK($message);//Construct an ACK based on the request
                $result['errorMsg'] = $ackMsg->getErrorMessage();
                
                $ackMsg->setErrorMessage(''); //this should be NOT needed... cause there is on _construct $msa->setField(1, "AA");  
            }
            
            
        } catch (Exception $e) {
            
            ///Net_HL7_Messages has thrown an error .. so we respond with a generic message
            $ackMsg = new Net_HL7_Messages_ACK();            
            $ackMsg->setAckCode("R", $e->getMessage());
            
            $result['errorMsg'] .= $e->getMessage();
            
            $this->printlog("try-catch: " . $result['errorMsg'], 0);
        }
        
        $result['ackMsg'] =  HL7_MESSAGE_PREFIX . $ackMsg->toString() . HL7_MESSAGE_SUFFIX;
         
        return $result;
    
    }
    
    public function processHL7Message_ORIGINAL($req = '')
    {   
        $this->printlog(__FUNCTION__);
        
        $port = $this->port;
        
        //convert to utf8
        $req = utf8_encode($req);
        
        if (strlen(trim($req)) < 1) {
            return;
        }
        
        try {
        
            if (strlen(trim($req)) < 9 || substr(trim($req), 0, 3) != "MSH") {
                
                $this->printlog("hl7 ERROR: Message to short or not starting with MSH: {$req}" , 0);
                
                return "Message to short or not starting with MSH";
                //throw new Exception("Message to short or not starting with MSH");
            }
        
            $message = new Net_HL7_Message(trim($req));
        
            //$this->printlog("Received: " . strlen($message->toString()) . " chars");
        
        } catch (Exception $e) {
        
            $this->printlog($e->getMessage(), 0);
        
            $msgType = "ERROR";
        
            $fname = dirname(__FILE__) . "/messages/err_" . $port . "_" . time() . ".txt";
        
            if ($handle = fopen($fname, "w")) {
                fwrite($handle, $req);
                fclose($handle);
            }
            
            $this->printlog("hl7 ERROR: saved in " . $fname, 0);
            
            return;
        }
        
        
        /*
         * save to file if you want to direct-process from here
         */
        /*
        $msh = $message->getSegmentsByName('MSH');
        
        $id = $msh[0]->getField(7);
        
        $fname = dirname(__FILE__) . "/messages/" . $port . "_" . $id . "_" . time() . ".txt";
        
        if ($handle = fopen($fname, "w")) {
            fwrite($handle, $message->toString(1));
            fclose($handle);
            
            $this->printlog("hl7 message saved in " . $fname);
        }
        */
        
        /*
         * !!!!!! $message->toString(pretty = 1) adds a \n .. if you have a bug in processings, check this first !
         */
        $this->_saveMessage2DB($message->toString(1));
        
        
        $ackMsg = new Net_HL7_Messages_ACK($message);
        
        $ackMsg->setErrorMessage(''); //this is the TODO
        
        $result = array(
            'ackMsg' =>  HL7_MESSAGE_PREFIX . $ackMsg->toString() . HL7_MESSAGE_SUFFIX,
//             'savedHL7File' => $fname,
        );
        
        
        return $result;
                    
        
    }
    
    /**
     * !! NOT USED as of 03.07.2018 ... removed the call from readHL7Message
     * 
     * !! if you run this with root, and you exec() ...
     * also a error because of Zend_Log_Writer_Stream cannot access log file created as root
     * because you created the log files as root, and apache2 is www-data 
     * 
     * !! ... an attempt to run as www-data is here if you run this script with root, give it a try
     * 
     * p.s. 
     * if Nico is using the files he sent and opens the socket as root, 
     * I think the server can be captured 
     * $filepath is a string created from the text in the message you received ...
     * and he shell_exec($filepath) ...
     * the simple is_file($filepath) can prevent that
     * 
     * 
     * 
     * @param string $filepath
     */
    function processHL7savedFile ($filepath = null)
    {
        $this->printlog(__FUNCTION__ . "({$filepath}) ", 7);
        
        $port = $this->port;
    
        if ( ! is_file($filepath)) {
            return; //fail-safe
        }
        
        $php_cmd = "php " . dirname(__FILE__) . "/process_hl7_file.php --hl7_file '{$filepath}'  --port {$port}";
        
        $this->printlog( $php_cmd );
        
        //this is if you run with current user and want to see the result
        /*
        $exec_cmd = $php_cmd;
        $out = shell_exec($exec_cmd);
        $this->printlog($out);
        */
        
        //try to run as www-data  + setsid + discard output
        $exec_cmd = 'sudo -u www-data bash -c "exec nohup setsid '. $php_cmd .' > /dev/null 2>&1 &"';
//         $cmd_www_data = 'sudo -u www-data bash -c "exec nohup setsid ' . $php_cmd . ' > /tmp/processHL7savedFile.out"';
        exec($exec_cmd);
    
        $this->printlog( "EXEC() :\n " . $exec_cmd . "\n" );
    
    }
    
    private function _saveMessage2DB( $message = '') 
    {
        $this->printlog(__FUNCTION__, 7);
        
        try {
            
            $dsn = "mysql:host={$this->_mysql_cfg['host']};dbname={$this->_mysql_cfg['db']};charset={$this->_mysql_cfg['charset']}";
            $opt = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $pdo = new PDO($dsn, $this->_mysql_cfg['user'], $this->_mysql_cfg['pass'], $opt);
        
        
            try {
                
                $pdo->prepare("INSERT INTO hl7_messages_received (
                    messages_received_ID,
                    client_id, 
                    port, 
                    message, 
                    fetched_by_master,
                    create_date)
                    VALUES (NULL, :client_id, :port, AES_ENCRYPT(:message, :encrypt_key), :fetched_by_master, NOW())")
                ->execute(array(
                    "client_id"         => isset($this->_mysql_cfg['client_id']) ? $this->_mysql_cfg['client_id'] : null, //this is optional, leave null
                    "port"              => $this->port,
                    "message"           => $message,
                    "encrypt_key"       => $this->_mysql_cfg['encrypt_key'],
                    "fetched_by_master" => isset($this->_mysql_cfg['fetched_by_master']) ? $this->_mysql_cfg['fetched_by_master'] : "NO"
                ));
                
                return true; // only this one and only now can result be true
                
            } catch (PDOException $e) {
                
                $this->printlog("PDOException: " . $e->getMessage());
            }
            
            $pdo = null;
            
        } catch (PDOException $e) {
            
            $this->printlog("PDOException: " . $e->getMessage());
        }
        
    }
    
   
    /**
     * 
     * @param unknown $msg
     * @return boolean
     * 
     * @deprecated
     */
    function sendMessage($msg)
    {
        foreach($this->clients as $client)
        {
            @socket_write($client,$msg,strlen($msg));
        }
        return true;
    }
    
    
    public function printlog($msg , $errorLevel = null) 
    {
        
        if (is_null($errorLevel) 
            || $errorLevel == 0 
            || $this->showlog) 
        {
            echo "[" . date("Y-m-d H:i:s") . " # Port:". $this->port . "]>". $msg . PHP_EOL;
        } 
    }
    
}



(new ISPCSocketServer($ipaddress, $port , $mysql_cfg, $printLog))->run();
?>