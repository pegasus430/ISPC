<?php

	class TestController extends Zend_Controller_Action {

		public function init()
		{
			/* Initialize action controller here */

			//Check patient permissions on controller and action
//			$patient_privileges = PatientPermissions::checkPermissionOnRun();
//
//			if(!$patient_privileges)
//			{
//				$this->_redirect(APP_BASE . 'error/previlege');
//			}
		}

		public function testuserAction()
		{
		    $cookie_user = " claudiaboden"; 
		    $user = Doctrine_Query::create()
		    ->select('*')
		    ->from('User')
		    ->where("username='".$cookie_user."' and isdelete=0 and isactive=0");
		    $userexec = $user->execute();
		    echo "<br/>---------------<br/>";
		    echo "<pre>";
		    var_dump($userexec);
		    echo "</pre>";
		    $userarray = $userexec->toArray();
		    
		    echo "<br/>---------------<br/>";
		    echo "<br/>---------------<br/>";
		    echo "<br/>---------------<br/>";
		    echo "<pre>";
		    var_dump($userarray);
		    echo "</pre>";
		    exit;
		    
		}

		public function testpageAction()
		{
//			$logininfo = new Zend_Session_Namespace('Login_Info');
//			$userid = $logininfo->userid;
//			$clientid = $logininfo->clientid;
//			
			//pms
//			$ipid = '7bae24950ca7c0feab5c6af2728f6c59132dfa44';
			//tubinger TP10449
//			$ipid = 'bb6cf09d08fbc2b99e66ccc202cf0ad151e5c05e';
//			
//			$x = PatientMaster::get_patient_details($ipid);
//			$x = PatientMaster::get_patient_familydoctor($ipid);
//			$x = PatientMaster::get_patient_sapv_details($ipid);
//			$x = PatientMaster::get_patient_hi_details($ipid);
//			$x = PatientMaster::get_client_details($clientid);
//			$x = PatientMaster::get_patient_diagnosis($ipid);
//			$x = PatientMaster::get_user_details($userid);
//			$x = PatientMaster::get_patient_symptomatology($ipid);
			
		    //$mepatient = new Pms_mePatient();
		    
		    //$payload = file_get_contents('/home/htdocs/ispc2017/_tests//mePatient_requests_received-payload22.bin');
		    
		    
		    //$regid = 'ISPC160823fzAlqat90GQ:APA91bGeUhTLmLc-bIVniylZcqWHRxMmoPy-rGzAVqZfdHVjcpaVfPGHPKISeSu5up4fTVutUvzXHKnruLO04tMklhx5OUm0dFyTxyRQNy5D-xOrKw_92pc8NntotsiRNtiul2gxnAPq';
		    
		    //$osmHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Openstreetmap');
		    //$resultArray = $osmHelper->routeLength('str. Dacia, nr. 14, et. 2, Râmnicu Vâlcea 240538, Vâlcea, Romania', 'str. Nicolae Balcescu, nr. 173 F, Pitesti, Arges, Romania');
		    
		    
		    
		    
		    
		    
		    
		    /*
		     * 
		     * mePatient export PDF
		     * 
		     */
		    
		    
		    
		    
		    define (ABSPATH, APPLICATION_PATH.'/../library/Survey/chain2pdf');
		    
		    
		    $patient_details = array(
		        'first_name' => 'First name test',
		        'last_name' => 'last name test',
		        'survey_name' => 'Mep TEST',
		        'dob' => '11.11.2011',
		    );
		    
		    $chain_id = 3;
		    
		    //if outputfolder is set PDF is written there, otherwise it's offered to download
		    
		    //$outputfolder = null;
		    $outputfolder = ABSPATH.'/_tmp';
		    
		    require_once 'Survey/chain2pdf/chain2html.php';
		    
		    var_dump($pdf_disk_path);
		    
		    
		    
		    /*
		     *
		     * mePatient export PDF
		     *
		     */
		    
		    
		    //$data = $mepatient->push_notification($regid, 'foobar');
		    //var_dump($mepatient->process_payload(1, $payload));
		    
		   echo '1';
			exit;
		}
		
		public function testcurlAction() {
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    print_r($logininfo);
		    if ( ! ($HL7_cfg = $this->getInvokeArg('bootstrap')->getOption('HL7_send'))
		        || ! isset($HL7_cfg[$logininfo->clientid])
		        || ! ($serverHL7_addr = $HL7_cfg[$logininfo->clientid]['ft1']['host'])
		        || ! ($serverHL7_port = $HL7_cfg[$logininfo->clientid]['ft1']['port'])
		        )
		    {
		        print_r($HL7_cfg[1]);
		        die ('error 1');
		    } else {
		        
		       
		        $hl7_proxy_sender_url = $HL7_cfg[$logininfo->clientid]['ft1']['proxy_sender_url'];
		        
		        for($i=1;$i<4;$i++) {
		            $messageRESPONSE = $this->__invoicesnew_hl7_activation_transmit_CURL($hl7_proxy_sender_url, $serverHL7_addr, $serverHL7_port, $i);
		            echo "\n\n\n\n".'<br /><br />****************START RESPONSE****************************';
		            print_r($messageRESPONSE);
		            echo '<br /><br />****************END RESPONSE****************************'."\n\n\n\n";
		            //unset($this->_httpService);
		            //$this->_httpService->close();
		        }
		    }
		    exit;
		}
		
		private function __invoicesnew_hl7_activation_transmit_CURL( $hl7_proxy_sender_url = '' , $host = '', $port = '', $message = '')
		{
		    
		    if ( ! isset($this->_httpService) || ! $this->_httpService instanceof Zend_Http_Client) {
		        
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
		            'useragent'    => 'Zend_Http_Client-ISPC-HL7-CURLXXX',// Default = Zend_Http_Client
		            'keepalive'    => false,
		        );
		        $this->_httpService =  new Zend_Http_Client(null, $httpConfig);
		        $this->_httpService->setAdapter($adapter);
		        $this->_httpService->setCookieJar(false);
		        
		        $this->_httpService->setUri(Zend_Uri_Http::fromString($hl7_proxy_sender_url));
		        $this->_httpService->setMethod('POST');
		    }
		    
		    
		    
		    $this->_httpService->setParameterPost([
		        'port'        => $port,
		        'host'        => $host,
		        'message'     => base64_encode($message),
		        '_hash'       => hash("crc32b", $message . $host . $port),
		    ]);
		    
		    echo "\n\n\n\n".'<br /><br />****************PRESTART TRY '.$message.'****************************';
		    print_r($this->_httpService);
		    echo "\n\n\n\n".'<br /><br />****************START BEOFRE TRY '.$message.'****************************';
		    
		    
		    
		    try {
		        $lastReq = $this->_httpService->request();
		        
		        //$this->_httpService->resetParameters(true);
		        
		        if ( ! $lastReq->isError()) {
		            
		            $this->_httpService->resetParameters(true);
		            
		            
		            echo '<br /><br />****************END TRY 1 SUCCESS '.$message.'****************************'."\n\n\n\n";
		            
		            return $lastReq->getBody();
		            
		        } else {
		            
		            $log_text = "__invoicesnew_hl7_activation_transmit_CURL: we had errors:" . PHP_EOL ;
		            $log_text .= "request:" . $this->_httpService->getLastRequest() .PHP_EOL;
		            if($this->_httpService->getLastResponse()){
		                $log_text .= 'response:' . $this->_httpService->getLastResponse()->asString();
		            } else{
		                $log_text .= 'NO response Y';
		            }
		            
		            //$this->getHelper('Log')->error ( $log_text );
		            
		            echo '<br /><br />****************END TRY 1 FAIL '.$message.'****************************'."\n\n\n\n";
		            
		            die ($log_text);
		            
		            
		        }
		        
		        // Ancuta 12.05.2020
		        unset($this->_httpService);
		        // --
		        
		        echo '<br /><br />****************END TRY 1 '.$message.'****************************'."\n\n\n\n";
		        
		    } catch (Zend_Http_Client_Exception $e) {
		        
		        try {
		            
		            // NEW
		            $log_text = "__invoicesnew_hl7_activation_transmit_CURL: Zend_Http_Client_Exception:" . $e->getMessage() . PHP_EOL;
		            $log_text .= "request:" . $this->_httpService->getLastRequest() .PHP_EOL;
		            if($this->_httpService->getLastResponse()){
		                $log_text .= 'response:' . $this->_httpService->getLastResponse()->asString();
		            } else{
		                $log_text .= 'NO response X';
		            }
		            
		            echo '<br /><br />****************END TRY 2 '.$message.'****************************'."\n\n\n\n";
		            //$this->_log_info($log_text);
		            //$this->getHelper('Log')->error ($log_text);
		            die ($log_text);
		            //  ---
		            
		        } catch (Exception $ee) {
		            
		            //$this->getHelper('Log')->error ("__invoicesnew_hl7_ft1_transmit_CURL: Exception:" . $ee->getMessage() . PHP_EOL . $message);
		            
		            //$this->getHelper('Log')->error ($ee->getMessage() . "\n" . $message);
		            
		            die ($ee->getMessage() . "\n" . $message);
		            
		            echo '<br /><br />****************END CATCH 2 '.$message.'****************************'."\n\n\n\n";
		        }
		        
		        echo '<br /><br />****************AFTER CATCH 2 '.$message.'****************************'."\n\n\n\n";
		    }
		    
		}
		/**
		 * ISPC-2675, elena, 23.10.2020
		 * @throws Doctrine_Query_Exception
		 */
		public function clearleadingAction(){
		    
		    $fdoc = Doctrine_Query::create()
		    ->select("*")
		    ->from('PatientQpaLeading')
		    ->where("isdelete = 0")
		    ->andWhere('end_date="0000-00-00 00:00:00"');
		    $aStars = $fdoc->fetchArray();
		    $aStarsExtended = [];
		    $aEpids = [];
		    $leadingIdsToRemove = [];
		    
		    foreach($aStars as $star){
		        if(strlen($star['ipid']) > 0){
		            $star['epid'] = Pms_CommonData::getEpid($star['ipid']);
		            $aStarsExtended[] = $star;
		            $aEpids[] = $star['epid'];
		        }
		        
		    }
		    print_R(count($aStarsExtended)); exit;
		    
		    //print_r($aStarsExtended);
		    
		    
		    $mdoc = Doctrine_Query::create()
		    ->select('*')
		    ->from('PatientQpaMapping')
		    ->whereIn('epid', $aEpids)
		    ;
		    $aMappings = $mdoc->fetchArray();
		    //print_r($aMappings);
		    foreach($aStarsExtended as $star){
		        $starToExclude = true;
		        foreach($aMappings as $mapping){
		            if(($star['epid'] == $mapping['epid']) && ($star['userid'] == $mapping['userid'])){
		                $starToExclude = false;
		                break;
		            }
		        }
		        if($starToExclude){
		            $leadingIdsToRemove[] = $star['id'];
		        }
		    }
		    
		    //print_r($leadingIdsToRemove);
		    
		    Doctrine_Query::create()->delete('*')
		    ->from('PatientQpaLeading')
		    ->whereIn('id',$leadingIdsToRemove)
		    ->execute();
		    
		    echo 'executed';
		    
		    
		    exit();
		    
		}
		
		
	}

?>