<?php
/**
 * 
 * @author  Dec 17, 2020  ancuta
 * 
 *
 */
class Hl7Controller extends Pms_Controller_Action
{
    private $start_timer = 0;
    
    public function init()
    {
        /* Initialize action controller here */
        $this->setActionsWithJsFile([
            
            
        ]);

        // phtml is the default for zf1 ... but on bootstrap you manualy set html :(
        $this->getHelper('viewRenderer')->setViewSuffix('phtml');
        
        
        $this->start_timer = microtime(true);
        
    }
 
    public function sentproofAction(){
        
        $falls = array(
            "4673797" => "2020-11-03",
            "4673794" => "2020-11-03",
            "4673743" => "2020-11-03",
            "4673769" => "2020-11-03",
            "4673782" => "2020-11-03",
            "4673783" => "2020-11-03",
            "4673776" => "2020-11-03",
            "4673746" => "2020-11-03",
            "4673732" => "2020-11-03",
            "4673701" => "2020-11-03",
            "4673704" => "2020-11-03",
            "4673758" => "2020-11-03",
            "4673767" => "2020-11-03",
            "4673716" => "2020-11-03",
            "4673705" => "2020-11-03",
            "4673694" => "2020-11-03",
            "4673779" => "2020-11-03",
            "4673727" => "2020-11-03",
            "4673802" => "2020-11-03",
            "4673768" => "2020-11-03",
            "4673772" => "2020-11-03",
            "4673750" => "2020-11-03",
            "4673753" => "2020-11-03",
            "4673770" => "2020-11-03",
            "4673777" => "2020-11-03",
            "4673805" => "2020-11-03",
            "4673806" => "2020-11-03",
            "4673803" => "2020-11-03",
            "4673790" => "2020-11-03",
            "4673799" => "2020-11-03",
            "4673765" => "2020-11-03",
            "4673789" => "2020-11-03",
            "4673807" => "2020-11-03",
            "4673751" => "2020-11-03",
            "4673740" => "2020-11-03",
            "4673734" => "2020-11-03",
            "4673710" => "2020-11-03",
            "4673788" => "2020-11-03",
            "4673798" => "2020-11-03",
            "4673735" => "2020-11-03",
            "4673400" => "2020-11-03",
            "4673899" => "2020-11-03",
            "4673787" => "2020-11-03",
            "4673723" => "2020-11-03",
            "4673780" => "2020-11-03",
            "4673748" => "2020-11-03",
            "4673706" => "2020-11-03",
            "4674378" => "2020-11-03",
            "4674237" => "2020-11-03",
            "4673742" => "2020-11-03",
            "4683311" => "2020-12-01",
            "4683168" => "2020-12-01",
            "4682393" => "2020-12-01",
            "4682402" => "2020-12-01",
            "4682409" => "2020-12-01",
            "4683169" => "2020-12-01",
            "4683165" => "2020-12-01",
            "4682767" => "2020-12-01",
            "4682436" => "2020-12-01",
            "4682440" => "2020-12-01",
            "4682699" => "2020-12-01",
            "4682705" => "2020-12-01",
            "4682733" => "2020-12-01",
            "4682740" => "2020-12-01",
            "4682744" => "2020-12-01",
            "4682748" => "2020-12-01",
            "4682757" => "2020-12-01",
            "4682772" => "2020-12-01",
            "4682815" => "2020-12-01",
            "4682822" => "2020-12-01",
            "4682828" => "2020-12-01",
            "4682844" => "2020-12-01",
            "4682856" => "2020-12-01",
            "4682859" => "2020-12-01",
            "4682868" => "2020-12-01",
            "4682872" => "2020-12-01",
            "4682879" => "2020-12-01",
            "4682886" => "2020-12-01",
            "4682887" => "2020-12-01",
            "4682889" => "2020-12-01",
            "4682899" => "2020-12-01",
            "4682901" => "2020-12-01",
            "4683255" => "2020-12-01",
            "4683278" => "2020-12-01",
            "4683289" => "2020-12-01",
            "4682882" => "2020-12-01",
            "4682881" => "2020-12-01",
            "4683743" => "2020-12-02",
            "4686668" => "2020-12-11"
        );
        
        foreach ($falls as $fall_number => $item_day) {
            
            // get received and processed messages for current day
            $sent_messages[$fall_number] = Doctrine_Query::create()->select('messages_sent_id,item_day,ipid,AES_DECRYPT(message,"' . Zend_Registry::get('salt') . '") as message_dec,AES_DECRYPT(message_ack,"' . Zend_Registry::get('salt') . '") as message_ack')
            ->from('Hl7MessagesSent')
            ->where("  AES_DECRYPT(message,'" . Zend_Registry::get('salt') . "')  like '%" . $fall_number . "%'")
            ->andWhere("item_day =?", $item_day)
            ->fetchArray();
            
        }
        
        foreach($sent_messages as $fall_nr => $lkd){
            foreach($lkd as $l=>$gfd){
                $sent_messages[$fall_nr ][$l]['epid'] = Pms_CommonData::getEpid($gfd['ipid']);
            }
        }
//         echo "<pre>";
//         print_R($sent_messages); exit;
        
        $export_data[] = array(
            0 => "epid",
            1 => "Date",
            2 => "Message_Sent",
            3 => "Message_received",
        );
        
        foreach($sent_messages as $ks=> $rows)
        {
            foreach($rows as $row){
                if(!empty($row)){
                    $export_data[] = array(
                        0 => $row['epid'],
                        1 => date('d.m.Y', strtotime($row['item_day']) ),
                        2 => $row['message_dec'],
                        3 => $row['message_ack'],
                    );
                }
            }
            
        }
        
        $this->generatePHPExcel('Proof201217', $export_data);
        exit;        
    }
 
    public function sentproofoldAction(){
        
        $falls = array(
            "4673797" => "2020-11-03",
            "4673794" => "2020-11-03",
            "4673743" => "2020-11-03",
            "4673769" => "2020-11-03",
            "4673782" => "2020-11-03",
            "4673783" => "2020-11-03",
            "4673776" => "2020-11-03",
            "4673746" => "2020-11-03",
            "4673732" => "2020-11-03",
            "4673701" => "2020-11-03",
            "4673704" => "2020-11-03",
            "4673758" => "2020-11-03",
            "4673767" => "2020-11-03",
            "4673716" => "2020-11-03",
            "4673705" => "2020-11-03",
            "4673694" => "2020-11-03",
            "4673779" => "2020-11-03",
            "4673727" => "2020-11-03",
            "4673802" => "2020-11-03",
            "4673768" => "2020-11-03",
            "4673772" => "2020-11-03",
            "4673750" => "2020-11-03",
            "4673753" => "2020-11-03",
            "4673770" => "2020-11-03",
            "4673777" => "2020-11-03",
            "4673805" => "2020-11-03",
            "4673806" => "2020-11-03",
            "4673803" => "2020-11-03",
            "4673790" => "2020-11-03",
            "4673799" => "2020-11-03",
            "4673765" => "2020-11-03",
            "4673789" => "2020-11-03",
            "4673807" => "2020-11-03",
            "4673751" => "2020-11-03",
            "4673740" => "2020-11-03",
            "4673734" => "2020-11-03",
            "4673710" => "2020-11-03",
            "4673788" => "2020-11-03",
            "4673798" => "2020-11-03",
            "4673735" => "2020-11-03",
            "4673400" => "2020-11-03",
            "4673899" => "2020-11-03",
            "4673787" => "2020-11-03",
            "4673723" => "2020-11-03",
            "4673780" => "2020-11-03",
            "4673748" => "2020-11-03",
            "4673706" => "2020-11-03",
            "4674378" => "2020-11-03",
            "4674237" => "2020-11-03",
            "4673742" => "2020-11-03",
            "4683311" => "2020-12-01",
            "4683168" => "2020-12-01",
            "4682393" => "2020-12-01",
            "4682402" => "2020-12-01",
            "4682409" => "2020-12-01",
            "4683169" => "2020-12-01",
            "4683165" => "2020-12-01",
            "4682767" => "2020-12-01",
            "4682436" => "2020-12-01",
            "4682440" => "2020-12-01",
            "4682699" => "2020-12-01",
            "4682705" => "2020-12-01",
            "4682733" => "2020-12-01",
            "4682740" => "2020-12-01",
            "4682744" => "2020-12-01",
            "4682748" => "2020-12-01",
            "4682757" => "2020-12-01",
            "4682772" => "2020-12-01",
            "4682815" => "2020-12-01",
            "4682822" => "2020-12-01",
            "4682828" => "2020-12-01",
            "4682844" => "2020-12-01",
            "4682856" => "2020-12-01",
            "4682859" => "2020-12-01",
            "4682868" => "2020-12-01",
            "4682872" => "2020-12-01",
            "4682879" => "2020-12-01",
            "4682886" => "2020-12-01",
            "4682887" => "2020-12-01",
            "4682889" => "2020-12-01",
            "4682899" => "2020-12-01",
            "4682901" => "2020-12-01",
            "4683255" => "2020-12-01",
            "4683278" => "2020-12-01",
            "4683289" => "2020-12-01",
            "4682882" => "2020-12-01",
            "4682881" => "2020-12-01",
            "4683743" => "2020-12-02",
            "4686668" => "2020-12-11"
        );
//         dd($falls);
        $manager = Doctrine_Manager::getInstance();
        $manager->setCurrentConnection('SYSDAT');
        $conn = $manager->getCurrentConnection();
        foreach ($falls as $fall_number => $item_day) {
            
            $querystr = "
			select  messages_sent_id,item_day,ipid,aes_decrypt(message,'encrypt') as  message_dec,aes_decrypt(message_ack,'encrypt') as  message_ack
			from hl7_messages_sent_201217
			where 
            item_day = '" . $item_day . "'
			and  aes_decrypt(message,'encrypt') LIKE '%".$fall_number."%' ";
            
            $query = $conn->prepare($querystr);
            $dropexec = $query->execute();
            $sent_messages[$fall_number] = $query->fetchAll();
        }
       
        
        foreach($sent_messages as $fall_nr => $lkd){
            foreach($lkd as $l=>$gfd){
                $sent_messages[$fall_nr ][$l]['epid'] = Pms_CommonData::getEpid($gfd['ipid']);
            }
        }
//         echo "<pre>";
//         print_R($sent_messages); exit;
        
        $export_data[] = array(
            0 => "epid",
            1 => "Date",
            2 => "Message_Sent",
            3 => "Message_received",
        );
        
        foreach($sent_messages as $ks=> $rows)
        {
            foreach($rows as $row){
                if(!empty($row)){
                    $export_data[] = array(
                        0 => $row['epid'],
                        1 => date('d.m.Y', strtotime($row['item_day']) ),
                        2 => $row['message_dec'],
                        3 => $row['message_ack'],
                    );
                }
            }
            
        }
        
        $this->generatePHPExcel('InitialProof201217', $export_data);
        exit;        
    }

    
    private function generatePHPExcel($report_name,$data)
    {
        $Tr = new Zend_View_Helper_Translate();
        
        // Create new PHPExcel object
        $excel = new PHPExcel();
        
        $excel->getDefaultStyle()->getFont()
        ->setSize(10);
        
        $xls = $excel->getActiveSheet();
        
        $line= 1;
        
        foreach($data as $key => $key_date)
        {
            $char_it = 65;
            foreach($key_date as $valcell)
            {
                $valcell = str_replace("<br />", "\n", $valcell);
                $xls->setCellValue(chr($char_it).$line, $valcell);
                $char_it++;
            }
            $line++;
        }
        
        $file = str_replace(" ", "_", $report_name);
        $fileName = $file . ".xls";
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    
    public function resendmessagesAction(){
    exit;
        
        $falls = array();
        
//         $falls['252'] = array(
// //             "4673797" => "2020-11-03"
// //             "4673794" => "2020-11-03",
//         );
        
        $falls['252'] = array(
            "4673797" => "2020-11-03",
            "4673794" => "2020-11-03",
            "4673743" => "2020-11-03",
            "4673769" => "2020-11-03",
            "4673782" => "2020-11-03",
            "4673783" => "2020-11-03",
            "4673776" => "2020-11-03",
            "4673746" => "2020-11-03",
            "4673732" => "2020-11-03",
            "4673701" => "2020-11-03",
            "4673704" => "2020-11-03",
            "4673758" => "2020-11-03",
            "4673767" => "2020-11-03",
            "4673716" => "2020-11-03",
            "4673705" => "2020-11-03",
            "4673694" => "2020-11-03",
            "4673779" => "2020-11-03",
            "4673727" => "2020-11-03",
            "4673802" => "2020-11-03",
            "4673768" => "2020-11-03",
            "4673772" => "2020-11-03",
            "4673750" => "2020-11-03",
            "4673753" => "2020-11-03",
            "4673770" => "2020-11-03",
            "4673777" => "2020-11-03",
            "4673805" => "2020-11-03",
            "4673806" => "2020-11-03",
            "4673803" => "2020-11-03",
            "4673790" => "2020-11-03",
            "4673799" => "2020-11-03",
            "4673765" => "2020-11-03",
            "4673789" => "2020-11-03",
            "4673807" => "2020-11-03",
            "4673751" => "2020-11-03",
            "4673740" => "2020-11-03",
            "4673734" => "2020-11-03",
            "4673710" => "2020-11-03",
            "4673788" => "2020-11-03",
            "4673798" => "2020-11-03",
            "4673735" => "2020-11-03",
            "4673400" => "2020-11-03",
            "4673899" => "2020-11-03",
            "4673787" => "2020-11-03",
            "4673723" => "2020-11-03",
            "4673780" => "2020-11-03",
            "4673748" => "2020-11-03",
            "4673706" => "2020-11-03",
            "4674378" => "2020-11-03",
            "4674237" => "2020-11-03",
            "4673742" => "2020-11-03",
            "4683311" => "2020-12-01",
            "4683168" => "2020-12-01",
            "4682393" => "2020-12-01",
            "4682402" => "2020-12-01",
            "4682409" => "2020-12-01",
            "4683169" => "2020-12-01",
            "4683165" => "2020-12-01",
            "4682767" => "2020-12-01",
            "4682436" => "2020-12-01",
            "4682440" => "2020-12-01",
            "4682699" => "2020-12-01",
            "4682705" => "2020-12-01",
            "4682733" => "2020-12-01",
            "4682740" => "2020-12-01",
            "4682744" => "2020-12-01",
            "4682748" => "2020-12-01",
            "4682757" => "2020-12-01",
            "4682772" => "2020-12-01",
            "4682815" => "2020-12-01",
            "4682822" => "2020-12-01",
            "4682828" => "2020-12-01",
            "4682844" => "2020-12-01",
            "4682856" => "2020-12-01",
            "4682859" => "2020-12-01",
            "4682868" => "2020-12-01",
            "4682872" => "2020-12-01",
            "4682879" => "2020-12-01",
            "4682886" => "2020-12-01",
            "4682887" => "2020-12-01",
            "4682889" => "2020-12-01",
            "4682899" => "2020-12-01",
            "4682901" => "2020-12-01",
            "4683255" => "2020-12-01",
            "4683278" => "2020-12-01",
            "4683289" => "2020-12-01",
            "4682882" => "2020-12-01",
            "4682881" => "2020-12-01",
            "4683743" => "2020-12-02",
            "4686668" => "2020-12-11"
        );

        
        if (! ($hl7_send_cfg = $this->getInvokeArg('bootstrap')->getOption('HL7_send'))) {
            return;
        }

        $hl7_clients = array();
        foreach ($hl7_send_cfg as $client_id => $hl7s) {
            if ($hl7s['ft1']['host'] && $hl7s['ft1']['port']) {
                $hl7_clients[] = $client_id;
            }
        }

        $sent_messages = array();
        $serverHL7_addr = "";
        $serverHL7_port = "";
        $hl7_proxy_sender_url = "";
        $resultsACK = array();

        foreach ($hl7_clients as $k => $client_id) {

            $serverHL7_addr = $hl7_send_cfg[$client_id]['ft1']['host'];
            $serverHL7_port = $hl7_send_cfg[$client_id]['ft1']['port'];
            $hl7_proxy_sender_url = $hl7_send_cfg[$client_id]['ft1']['proxy_sender_url'];

            try {
                
                foreach ($falls[$client_id] as $fall_number => $item_day) {

                    // get received and processed messages for current day
                    $sent_messages = Doctrine_Query::create()->select('messages_sent_id,item_day,ipid,AES_DECRYPT(message,"' . Zend_Registry::get('salt') . '") as message_dec')
                        ->from('Hl7MessagesSent')
                        ->where("  AES_DECRYPT(message,'" . Zend_Registry::get('salt') . "')  like '%" . $fall_number . "%'")
                        ->andWhere("item_day =?", $item_day)
                        ->fetchArray();

//                     echo "<pre/>";
//                     print_R($sent_messages);exit;
                    
//                     exit;
                    
                    if (! empty($sent_messages) && count($sent_messages) == 1) {
                        $msg_info = $sent_messages[0];

                        if(empty($msg_info)){
                            continue;
                        }
                        
                        $message_string = $msg_info['message_dec'];
                        $item_day = $msg_info['item_day'];
                        $ipid = $msg_info['ipid'];

                        $messageRESPONSE = '';
                        $send_ok = null; // if MSA-1 == AA => 'yes', elseif other code => 'no', else => 'null'

                        try {
                            
                            if (! empty($hl7_proxy_sender_url)) {
                                $messageRESPONSE = $this->__invoicesnew_hl7_activation_transmit_CURL($hl7_proxy_sender_url, $serverHL7_addr, $serverHL7_port, $message_string);
                            } else {
                                $hl7_connection = new Net_HL7_Connection($serverHL7_addr, $serverHL7_port);
                                $messageRESPONSE = $hl7_connection->send($message_string);
                            }

                            if (! empty($messageRESPONSE)) {
                                $messageMSA = new Net_HL7_Message($messageRESPONSE);
                                $MSA = $messageMSA->getSegmentsByName('MSA')[0];
                                if ($MSA instanceof Net_HL7_Segments_MSA && $MSA->getAcknowledgementCode() == "AA") {
                                    // all was ok?
                                    $send_ok = 'yes';
                                } else {
                                    $send_ok = 'no'; // you have as AE or AC .. get messages for error it they send any
                                }
                            }
                        } catch (Exception $e) {}

                        $resultsACK[$ipid][$item_day] = Hl7MessagesSentTable::getInstance()->findOrCreateOneBy(
                            ['parent_table',  'ipid',    'item_day',     'message_type'],
                            ['CronActivation', $ipid, $item_day, 'ADT^A08'],
                            [
                                'client_id'       => $client_id,
                                'port'            => $port,
                                'message'         => $message_string,
                                'message_ack'     => $messageRESPONSE,
                                'send_trys'       => new Doctrine_Expression('send_trys + 1'),
                                'send_ok'         => $send_ok,
                            ]
                            );

                        $sent_days2ipid[$ipid][$item_day]['msg'] = $message_string;
                        $sent_days2ipid[$ipid][$item_day]['resp'] = $messageRESPONSE;
                    }
                }

                // $time_elapsed = microtime(true) - $this->start_timer;
                // $message = "[ " . __FUNCTION__ . " ] - took (" . round($time_elapsed, 2) . " Seconds )";
                // $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            } catch (Exception $e) {

                // $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        }
        
        echo "<pre/>";
        echo "finish";
        print_R($sent_days2ipid); 
         
        exit;
        
        
    }
    
  
    /**
     * ISPC-2459
     * COPY of __invoicesnew_hl7_activation_transmit_CURL from InvoicenewController.php
     * @param string $hl7_proxy_sender_url
     * @param string $host
     * @param string $port
     * @param string $message
     * @return string
     */
    private function __invoicesnew_hl7_activation_transmit_CURL( $hl7_proxy_sender_url = '' , $host = '', $port = '', $message = '')
    {
        
        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig(array(
            'curloptions' => array(
                CURLOPT_FOLLOWLOCATION  => false,
                CURLOPT_MAXREDIRS      => 0,
                CURLOPT_RETURNTRANSFER  => true,
                
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                
                CURLOPT_TIMEOUT => 15,
                CURLINFO_CONNECT_TIME => 16,
                CURLOPT_CONNECTTIMEOUT => 17,
                // 	            CURLOPT_COOKIE => $_req_cookie,
            )
        ));
        
        $httpConfig = array(
            'timeout'      => 10,// Default = 10
            'useragent'    => 'Zend_Http_Client-ISPC-HL7-CURL',// Default = Zend_Http_Client
            'keepalive'    => true,
        );
        $httpService =  new Zend_Http_Client(null, $httpConfig);
        $httpService->setAdapter($adapter);
        $httpService->setCookieJar(false);
        
        $httpService->setUri(Zend_Uri_Http::fromString($hl7_proxy_sender_url));
        $httpService->setMethod('POST');
        
        $httpService->setParameterPost([
            'port'        => $port,
            'host'        => $host,
            'message'     => base64_encode($message),
            '_hash'       => hash("crc32b", $message . $host . $port),
        ]);
        
        
        try{
            $lastReq = $httpService->request();
            
            sleep(1); //wait for the previous request to be completed
            // 		        $httpService->resetParameters(true);
            
            if ( ! $lastReq->isError()) {
                
                $httpService->resetParameters(true);
                
                return $lastReq->getBody();
                
            } else {
                
                $log_text = "__invoicesnew_hl7_activation_transmit_CURL: we had errors:" . PHP_EOL ;
                $log_text .= "request:" . $httpService->getLastRequest() .PHP_EOL;
                if($httpService->getLastResponse()){
                    $log_text .= 'response:' . $httpService->getLastResponse()->asString();
                } else{
                    $log_text .= 'NO response Y';
                }
                
                $this->getHelper('Log')->error ( $log_text );
                
                
            }
            
            // Ancuta 12.05.2020
            unset($httpService);
            // --
            
        } catch (Zend_Http_Client_Exception $e) {
            
            try {
                
                // NEW
                $log_text = "__invoicesnew_hl7_activation_transmit_CURL: Zend_Http_Client_Exception:" . $e->getMessage() . PHP_EOL;
                $log_text .= "request:" . $httpService->getLastRequest() .PHP_EOL;
                if($httpService->getLastResponse()){
                    $log_text .= 'response:' . $httpService->getLastResponse()->asString();
                } else{
                    $log_text .= 'NO response X';
                }
                
                //$this->_log_info($log_text);
                $this->getHelper('Log')->error ($log_text);
                //  ---
                
            } catch (Exception $ee) {
                
                $this->getHelper('Log')->error ("__invoicesnew_hl7_ft1_transmit_CURL: Exception:" . $ee->getMessage() . PHP_EOL . $message);
                
                $this->getHelper('Log')->error ($ee->getMessage() . "\n" . $message);
            }
            
            
        }
        
    }
    
    
    
    
    
    
    
}
?>