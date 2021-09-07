<?php
/*
 * class was inherited from ISPC-2213
 * @author Nico Kamper
 * 
 * touched by @cla on 18.06.2018
 * touched by @cla on 13.02.2019
 */
class Net_SocketServerEssen
{

    /**
     * ISPC ipid
     * @var string
     */
    private $__ipid = null;
    
    /**
     * 
     * @var Net_HL7_Message
     */
    private $__hl7Message = null;
    
    
    /**
     * Hl7MessagesReceived pk
     * @var integer
     */
    private $__messages_received_ID = null; 
    
    
        
    private $config = null;
    private $clientid = null;
    private $userid = null;
    private $testdatamodus = 0;
    private $conn_id = 'essen';
    private $deny_messages_older_than = null;
    
    private $zbe = null; //http://wiki.hl7.de/index.php?title=Segment_ZBE
    private $zdate = null;
    
    public function __construct($conf = array(), $test = 0)
    {
        if (empty($conf) 
            || empty($conf['clientid']) 
            || ! is_numeric($conf['clientid'])
            || ! is_numeric($conf['userid'])
            ) 
        {
            throw new Zend_Exception(__METHOD__ . " config failed ", 0);
        }
        
        $this->config = $conf;
        
        $this->clientid = (int)$conf['clientid']; // cast to int
        
        $this->userid = (int)$conf['userid']; // cast to int
        
        $this->testdatamodus = $test;
        
        $this->conn_id = 'essen';
        
        $this->deny_messages_older_than = 60 * 60 * 24 * 80; // 80days
        
        $this->__messages_received_ID = $this->config['messages_received_ID'];
        
    }
    
    public function __destruct()
    {
        Zend_Session::namespaceUnset('Login_Info');
    }
    
    // process the incoming message
    public function processHL7Msg($req)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $logininfo->userid = $this->userid;
        $logininfo->clientid = $this->clientid;
        
        try {
            
            $message = new Net_HL7_Message(trim($req));
            
            $this->__hl7Message = $message;
            
            $this->log("Received:\n" . $message->toString(1), 1);
            
            $msgType = $message->getSegmentFieldAsString(0, 9); // Example: "ADT^A08"
            
            $msgDate = $message->getSegmentFieldAsString(0, 7);
            
            $zbe = $message->getSegmentsByName("ZBE");
            
            if (sizeof($zbe) > 0) {
                
                $zbe = $zbe[0];
                $zdate = $zbe->getField(2);
                $zbe = $zbe->getField(4);
                
                if ($zdate && strlen($zdate) == 14) {
                    if (abs(strtotime($msgDate) - strtotime($zdate)) > $this->deny_messages_older_than) {
                        $this->log('Message seems to be outdated. Message not processed.', 2);
                        $msgType = "ERROR";
                    }
                }
            }
            $this->zbe = $zbe;
            $this->zdate = $zdate;
            
        } catch (Exception $e) {
            $this->log($e->getMessage(), 2);
            $msgType = "ERROR";
        }
        
        try {
            // Depending on MsgType select further proceed
            switch ($msgType) {
                case "ERROR":
                case "ACK":
                    break;
                
                // changeEPID
                case "ADT^A40":
                    try {
                        $this->changeEpid($message);
                    } catch (Exception $e) {
                        $this->log("ERROR:" . $e->getMessage(), 2);
                    }
                    break;
                
                // Aufnahmedaten stornieren (ADT^A11)
                // Verlegung stornieren (ADT^A12)
                case "ADT^A11":
                    
                    $segZBE = $this->__hl7Message->getSegmentsByName("ZBE")[0];
                    
                    if ( $segZBE instanceof Net_HL7_Segments_ZBE) {
                        switch($segZBE->getEventType())
                        {
                            case "CANCEL":
                            case "DELETE":
                                
                                $this->__messageDeleteMovementID($segZBE->getMovementId());
                                
                                break;
                        }
                    }
                break;
                    
                case "ADT^A12":
                    // ignore message
                    break;
                
                // Entlassung (ADT^A03)
                case "ADT^A03":
                    // ignore message - discharge in ispc only
                    break;
                
                // Entlassung stornieren
                case "ADT^A13":
                    // ignore message
                    break;
                
                case "ORM^O01":
                    // ignore message
                    break;
                
                // Verlegung aus anderer Abteilung (ADT^A02)
                case "ADT^A02":
                // Admission Patient anlegen (ADT^A01)
                // Ambulate Aufnahme(ADT^A04)
                // Fallartwechsel (ADT^A06 / ADT^A07)
                // Verlegung aus anderer Abteilung (ADT^A02)
                case "ADT^A04":
                case "ADT^A02":
                case "ADT^A06":
                case "ADT^A07":
                case "ADT^A01":
                    $ipid = $this->getIpidFromMessage($message);
                    
                    if (! isset($ipid)) {
                        $this->createPatient($message);
                        $this->updatePatDiagnosis($message);
                    } elseif (isset($ipid)) {
                        $this->updatePatient($message, $ipid);
                    }
                    
                    $this->__setPatientVisitnumber();
                
                    break;
                
                // Change Patient-Data (ADT^A08)
                case "ADT^A08":
                    $ipid = $this->getIpidFromMessage($message);
                    
                    if (! isset($ipid) && $zbe == "UPDATE") {
                        $this->log("IGNORED MESSAGE: Update of nonexistant patient.", 2);
                    } elseif (isset($ipid)) {
                        $this->updatePatient($message, $ipid);
                    } else {
                        if ($this->clientid == 81) {
                            $this->createPatient($message, $ipid);
                        }
                    }
                    
                    break;
                
                default:
                    $this->log("Unknown MessageType:" . $msgType, 2);
            }
        
            return true;
        
        } catch (Exception $e) {
            $this->log("ERROR:" . $e->getMessage(), 2);
        }
        
        return false;
        
    }

    private function log($msg, $level = 0)
    {
        // log everything to db
        $logmsg = ($this->config['encryptlog'] == true) ? Pms_CommonData::aesEncrypt($msg) : $msg;
        try {
            Hl7Log::log_hl7($logmsg, $level);
        } catch (Exception $e) {
            echo "Logging nicht möglich! ";
        }
        
        // print to console depending on loglevel
        if ($level >= $this->config['verbosity']) {
            echo "[" . date('Y-m-d H:i:s') . "] " . $msg . "\r\n";
        }
    }

    private function changeEpid($msg)
    {
        throw new Zend_Exception("changeEpid was not implemented, admin was informed.", 0);
        
        return;
        
        $pid = $msg->getSegmentsByName("PID");
        if (sizeof($pid) > 0) {
            $pid = $pid[0];
        } else {
            throw new Exception("PID-Segement not found");
        }
        
        $mrg = $msg->getSegmentsByName("MRG");
        if (sizeof($mrg) > 0) {
            $mrg = $mrg[0];
        } else {
            throw new Exception("MRG-Segement not found");
        }
        
        $client = Doctrine::getTable('Client')->findOneBy('id', $this->clientid);
        
        if ($client) {
            $clientarray = $client->toArray();
            $epid_chars = $clientarray['epid_chars'];
        }
        
        $old_epid = $mrg->getField(1);
        $new_epid = $pid->getField(3);
        $new_epid = $new_epid[0];
        
        $epidipid = Doctrine::getTable('EpidIpidMapping')->findOneBy("epid", $epid_chars . $old_epid);
        if ($epidipid) {
            $epidipid->clientid = $this->clientid;
            $epidipid->epid = $epid_chars . $new_epid;
            $epidipid->epid_chars = $epid_chars;
            $epidipid->epid_num = $new_epid;
            $epidipid->save();
        } else {
            throw new Exception("Patient not found");
        }
        $this->log("epid changed. OldEPID:" . $old_epid . ", new EPID:" . $new_epid, 1);
    }

    private function getIpidFromMessage($msg)
    {
        $pid = $msg->getSegmentsByName("PID");
        if (sizeof($pid) > 0) {
            $pid = $pid[0];
        } else {
            throw new Exception("PID-Segement not found");
        }
        $pv1 = $msg->getSegmentsByName("PV1");
        if (sizeof($pv1) > 0) {
            $pv1 = $pv1[0];
        } else {
            $pv1 = null;
        }
        
        if ($pv1) {
            $loc = $pv1->getAssignedPatientLocation();
            $pv1_location1 = $loc->org1;
            $pv1_location2 = $loc->org2;
        }
        $client = Doctrine::getTable('Client')->findOneBy('id', $this->clientid);
        
        if ($client) {
            $clientarray = $client->toArray();
            $epid_chars = $clientarray['epid_chars'];
        }
        $epid_no = $pid->getField(3);
        $epid_no = $epid_no[0];
        $epid_no = $epid_no;
        $epid = $epid_chars . $epid_no;
        
        $ipid = EpidIpidMapping::getIpidFromEpidAndClientid($epid, $this->clientid);
        
        $this->setIpid($ipid);
        
        if ($ipid && $this->zbe != "UPDATE") {
            // reactivate deleted patients
            $patient = Doctrine::getTable('PatientMaster')->findOneBy('ipid', $ipid);
            if ($patient->isdelete == 1) {
                $patient->isdelete = 0;
                $patient->isstandby = 0;
                $patient->save();
            }
            if ($patient->isstandbydelete == 1) {
                $patient->isdelete = 0;
                $patient->isstandbydelete = 0;
                $patient->save();
            }
        }
        return $ipid;
    }
    
    
    private function _generateNewIpid ()
    {
        $ipid = Pms_Uuid::GenerateIpid();
        
        $trysNewIpid = 0;
        
        while ($trysNewIpid < 10 && EpidIpidMapping::assertIpidExists($ipid)) {
        
            $trysNewIpid ++;
        
            $ipid = Pms_Uuid::GenerateIpid();
        }
        
        if ($trysNewIpid >= 10) {
            throw new Zend_Exception("Cannot generate new ipid, {$trysNewIpid} tryies failed.. exiting", 0);
        }
        
        return $ipid;
    }
    
    
    // Changing some Patient-Data that may have been entered wrong before
    private function createPatient($msg)
    {
        $pid = $msg->getSegmentsByName("PID");
        if (sizeof($pid) > 0) {
            $pid = $pid[0];
        } else {
            throw new Exception("PID-Segement not found");
        }
        
        $pv1 = $msg->getSegmentsByName("PV1");
        if (sizeof($pv1) > 0) {
            $pv1 = $pv1[0];
        } else {
            throw new Exception("PV1-Segement not found");
        }
        
        $ipid = $this->_generateNewIpid();
        
        $this->setIpid($ipid);
        
        $cust = new PatientMaster();
        $cust->isadminvisible = 1;
        $cust->ipid = $ipid;
        
        $bd_field = $pid->getDateOfBirth();
        if (strlen($bd_field) == 8) {
            $bd_date = substr($bd_field, 0, 4) . "-" . substr($bd_field, 4, 2) . "-" . substr($bd_field, 6, 2);
        } else {
            throw new Exception("Birthday-Field can not get parsed.");
        }
        
        $cust->birthd = $bd_date;
        
        $namefield = $pid->getPatientName();
        
        $patient_lastname = $namefield->lastname;
        if (is_array($namefield->lastname)) {
            $patient_lastname = $namefield->lastname[0];
        }
        
        $cust->last_name = Pms_CommonData::aesEncrypt($patient_lastname);
        $cust->first_name = Pms_CommonData::aesEncrypt($namefield->firstname);
        
        $addressfield = $pid->getAddress();
        $cust->street1 = Pms_CommonData::aesEncrypt($addressfield->street);
        $cust->zip = Pms_CommonData::aesEncrypt($addressfield->zip);
        $cust->city = Pms_CommonData::aesEncrypt($addressfield->city);
        
        $phonenumber = $pid->getPhoneNumber();
        $phonenumber = $phonenumber[0];
        $phonenumber = str_replace(';', ',', $phonenumber);
        $numbers = explode(',', $phonenumber);
        if (count($numbers) > 0) {
            if ($numbers[0]) {
                $cust->phone = Pms_CommonData::aesEncrypt($numbers[0]);
            }
        }
        if (count($numbers) > 1) {
            if ($numbers[1]) {
                $cust->mobile = Pms_CommonData::aesEncrypt($numbers[1]);
            }
        }
        
        switch (strtolower($pid->getSex())) {
            case "m":
                $sex = 1;
                break;
            case "w":
                $sex = 2;
                break;
            case "f":
                $sex = 2;
                break;
            default:
                $sex = 0;
        }
        $cust->sex = Pms_CommonData::aesEncrypt($sex);
        
        $adm_date = date("Y-m-d H:i:s", time());
        $cust->admission_date = $adm_date;
        
        $cust->recording_date = date("Y-m-d H:i:s", time());
        
        $cust->save();
        
        $client = Doctrine::getTable('Client')->findOneBy('id', $this->clientid);
        
        if ($client) {
            $clientarray = $client->toArray();
            $epid_chars = $clientarray['epid_chars'];
        }
        $epid_no = $pid->getField(3);
        $epid_no = $epid_no[0];
        $epid = $epid_chars . $epid_no;
        
        $res = new EpidIpidMapping();
        
        $res->clientid = $this->clientid;
        $res->ipid = $ipid;
        $res->epid = $epid;
        $res->epid_chars = $epid_chars;
        $res->epid_num = $epid_no;
        $res->visible_since = substr(date("Y-m-d H:i:s", time()), 0, 10);
        $res->discharge_since = "0000-00-00";
        $res->save();
        
        $visitnumber = $pv1->getVisitNumber()->id;
        // @todo
        
        // familydoctor
        $familydoc_id = $this->setFamilydoctor($pv1);
        if ($familydoc_id)
            $cust->familydoc_id = $familydoc_id;
            
            // location
        $this->updatePatientLocation($pv1, $ipid);
        $location_field = $pv1->getAssignedPatientLocation();
        $org1 = $location_field->org1;
        
        // Insurance
        $in1 = $msg->getSegmentsByName("IN1");
        $this->setPatientInsurance($in1, $ipid, $pv1);
        
        // next of kin
        $nk1 = $msg->getSegmentsByName("NK1");
        $this->setNextOfKin($nk1, $ipid);
        
        $cust = new PatientCourse();
        $cust->ipid = $ipid;
        $cust->course_date = date("Y-m-d H:i:s", time());
        $cust->course_type = Pms_CommonData::aesEncrypt("K");
        $cust->course_title = Pms_CommonData::aesEncrypt(addslashes("Patient wurde zum " . date("d.m.Y H:i", time()) . " aktiviert"));
        $cust->user_id = $this->userid;
        $cust->save();
    }


    /*
     * Nico's original fn
     */
    /*
	private function setFamilydoctor($pv1){

		//BEGIN Family Doctor
		$fdocfield = $pv1->getField(9);

		if ($fdocfield[1]){
            $lastname=$fdocfield[1];
            if(is_array($lastname)){
                $lastname=$lastname[0];
            }
            $firstname=$fdocfield[2];
            $street=$fdocfield[9];
            $zip=$fdocfield[7];
            $city=$fdocfield[8];
            $phone=$fdocfield[20];
            $fax=$fdocfield[21];

			$fdocfind = Doctrine_Query::create()
				->select('id')
				->from('FamilyDoctor')
				->where(	"first_name = ?", $firstname)
				->andwhere(	"last_name = ?", $lastname)
				->andwhere(	"zip = ?", $zip)
				->andWhere(	"clientid=?", $this->clientid)
				->andWhere(	"isdelete=0");

			$fdocId = $fdocfind->fetchArray();

			if (sizeof($fdocId)==1) {
				$fdocId = $fdocId[0]['id'];
			} else {
				$fdoc = new FamilyDoctor();
				$fdoc->clientid = $this->clientid;
				$fdoc->first_name = $firstname;
				$fdoc->last_name = $lastname;
				$fdoc->street1 = $street;
                $fdoc->title = $fdocfield[6];
				$fdoc->zip = $zip;
				$fdoc->city = $city;
                $fdoc->indrop = 1;
				$fdoc->phone_practice =$phone[0];
				$fdoc->fax =$fax[0];
				$fdoc->doctornumber =$fdocfield[0];
				$fdoc->save();
				$fdocId = $fdoc->id;
			}
		}
		return $fdocId;
		}
    */
    
    
    /**
     * @author @cla
     * @since 13.02.2019
     * 
     * @param Net_HL7_Segments_PV1 $pv1
     * @param string $old_familydoc_id
     * @return integer|NULL
     */
    private function setFamilydoctor($pv1, $old_familydoc_id = null)
    {
         
        $fdocId = null; //return
         
        $allreadySaved = false;
         
        // BEGIN Family Doctor
        $fdocfield = $pv1->getField(9);
         
        if ($fdocfield[1]) {
            $lastname = $fdocfield[1];
            if (is_array($lastname)) {
                $lastname = $lastname[0];
            }
            $firstname = (string)$fdocfield[2];
            $street = (string)$fdocfield[9];
            $zip = (string)$fdocfield[7];
            $city = (string)$fdocfield[8];
            $phone = (string)$fdocfield[20];
            $fax = (string)$fdocfield[21];
             
            $fdocfind = Doctrine_Query::create()
            ->select('*')
            ->from('FamilyDoctor indexBy id')
            ->where("first_name = ?", $firstname)
            ->andwhere("last_name = ?", $lastname)
            ->andwhere("zip = ?", $zip)
            ->andWhere("clientid = ?", $this->clientid)
            ->andWhere("isdelete = 0")
            ->fetchArray()
            ;
             
             
            if ( ! empty($fdocfind)) {
                 
                if ( ! empty($old_familydoc_id) && isset($fdocfind[$old_familydoc_id])) {
                     
                    //this is allready set as patient;s doctor, nothing more to do, just bypass newdoctor
                    $allreadySaved =  true;
                     
                } else {
                     
                    //check if any of this doctrors is from indrop=0... we don't add doctors from other patients
                     
                    $fdocfind = array_filter($fdocfind, function($i) {return $i['indrop'] == 0;});
                    $fdocfind = reset($fdocfind);
                     
                    if ( ! empty($fdocfind) && $fdocfind['id'])
                    {
                        //duplicate this doctor
                        unset($fdocfind['id'], $fdocfind['create_date'], $fdocfind['change_date'], $fdocfind['create_user'], $fdocfind['change_user']);
                        $fdocfind['indrop'] = 1;
                         
                        $newFdoc = Doctrine_Core::getTable('FamilyDoctor')->create();
                        $newFdoc->fromArray($fdocfind);
                        $newFdoc->save();
                         
                        if ($newFdoc && $newFdoc->id) {
                            $fdocId = $newFdoc->id;
                        }
                    }
                }
                 
            }
             
            if (empty($fdocId) && ! $allreadySaved) {
                 
                //create as new doctor
                $fdoc = new FamilyDoctor();
                $fdoc->clientid = $this->clientid;
                $fdoc->first_name = $firstname;
                $fdoc->last_name = $lastname;
                $fdoc->street1 = $street;
                $fdoc->title = $fdocfield[6];
                $fdoc->zip = $zip;
                $fdoc->city = $city;
                $fdoc->indrop = 1;
                $fdoc->phone_practice = $phone[0];
                $fdoc->fax = $fax[0];
                $fdoc->doctornumber = $fdocfield[0];
                $fdoc->save();
                 
                $fdocId = $fdoc->id;
                 
                //send message/email that a new familydoc was added ?
            }
        }
         
        return $fdocId;
    }
    
    
    
    
    
    
    
    

    private function updatePatientLocation($pv1, $ipid)
    {
        return;//TODO-2049- Ancuta BY Claudiu 16.01.2019
        
        $adm_date = date("Y-m-d H:i:s", time());
        
        $location_field = $pv1->getAssignedPatientLocation();
        
        $org1 = $location_field->org1;
        
        if ($org1) {
            // $caseinfo=$this->getCaseInfoFromOrg1($org1);
            
            $locname = $org1;
            $loctype = "1"; // 1=Hospital, 5=Home
            $locstreet = "";
            $loccity = "";
            $loczip = "";
            $locphone = "";
            
            if ($locname) {
                $locationFind = Doctrine_Query::create()->select('id')
                    ->from('Locations')
                    ->where("location = ?", Pms_CommonData::aesEncrypt($locname))
                    ->andWhere("street = ?", $locstreet)
                    ->andWhere("phone1 = ?", $locphone)
                    ->andWhere("client_id = ?", $this->clientid)
                    ->andWhere("isdelete='0'");
                $locId = $locationFind->fetchArray();
                
                if (sizeof($locId) == 0) {
                    $location = new Locations();
                    $location->client_id = $this->clientid;
                    $location->location = Pms_CommonData::aesEncrypt($locname);
                    $location->location_type = $loctype;
                    $location->street = $locstreet;
                    $location->zip = $loczip;
                    $location->city = $loccity;
                    $location->phone1 = $locphone;
                    $location->phone2 = "";
                    $location->fax = "";
                    $location->comment = "";
                    $location->save();
                    $locId = $location->id;
                } else {
                    $locId = $locId[0]['id'];
                }
                
                $old_location = Doctrine::getTable('PatientLocation')->findOneByIpidAndValidTill($ipid, "0000-00-00");
                
                if ($old_location && $old_location->location_id != $locId) {
                    $old_location->valid_till = $adm_date;
                    $old_location->save();
                }
                
                if (! $old_location || $old_location->location_id != $locId) {
                    $loc = new PatientLocation();
                    $loc->clientid = $this->clientid;
                    $loc->ipid = $ipid;
                    $loc->location_id = $locId;
                    $loc->reason = "";
                    $loc->reason_txt = "";
                    $loc->hospdoc = "";
                    $loc->transport = "";
                    $loc->valid_from = $adm_date;
                    $loc->valid_till = "0000-00-00";
                    $loc->admission_comments = "";
                    $loc->save();
                }
            }
        }
    }

    private function setPatientInsurance($in1, $ipid, $pv1)
    {
        if (sizeof($in1) > 0) {
            $in1 = $in1[0];
            
            $cust = Doctrine::getTable('PatientHealthInsurance')->findOneByIpid($ipid);
            
            if (! $cust) {
                $cust = new PatientHealthInsurance();
            }
            $cust->ipid = $ipid;
            $cust->kvk_no = $in1->getCompanyId();
            $cust->kvk_no = $cust->kvk_no[0];
            $cust->insurance_no = $in1->getPolicyNumber();
            
            if (! (isset($cust->insurance_no)) || strlen($cust->insurance_no) < 1) {
                // $cust->insurance_no=$in1->getField(409);
            }
            
            $companyname = $in1->getCompanyName();
            if (is_array($companyname)) {
                $companyname = implode(" & ", $companyname);
            }
            $cust->company_name = Pms_CommonData::aesEncrypt($companyname);
            $isname_field = $in1->getNameOfInsured();
            $cust->ins_first_name = Pms_CommonData::aesEncrypt($isname_field->firstname);
            $cust->ins_last_name = Pms_CommonData::aesEncrypt($isname_field->lastname);
            $bd_field = $in1->getInsuredsDateOfBirth();
            if (strlen($bd_field) == 8) {
                $bd_date = substr($bd_field, 0, 4) . "-" . substr($bd_field, 4, 2) . "-" . substr($bd_field, 6, 2);
                $cust->date_of_birth = $bd_date;
            }
            $ins_address_field = $in1->getField(5);
            $cust->ins_country = $ins_address_field->country;
            $cust->ins_street = Pms_CommonData::aesEncrypt($ins_address_field[0]);
            $cust->ins_zip = Pms_CommonData::aesEncrypt($ins_address_field[4]);
            $cust->ins_city = Pms_CommonData::aesEncrypt($ins_address_field[2]);
            $ins_phone_field = $in1->getField(7);
            $ins_phone = $ins_phone_field[0];
            $cust->ins_phone = Pms_CommonData::aesEncrypt($ins_phone);
            
            $cust->privatepatient = $pv1->getChargePriceIndicator() == "P" ? 1 : 0;
            
            $cust->save();
        }
    }

    private function setNextOfKin($nk, $ipid)
    {
        if (sizeof($nk) <= 0) {
            return;
        }
        
        $old_nk = Doctrine::getTable('ContactPersonMaster')->findByIpidAndIsdelete($ipid, "0");
        
        foreach ($old_nk as $nkold) {
            $nkold->isdelete = "1";
            $nkold->save();
        }
        
        foreach ($nk as $nk1) {
            
            $nk1_namefiled = $nk1->getNKName();
            $firstname = $nk1_namefiled->firstname;
            $lastname = $nk1_namefiled->lastname;
            
            if (is_array($lastname)) {
                $lastname = $lastname[0];
            }
            $cnt_first_name = Pms_CommonData::aesEncrypt($firstname);
            $cnt_last_name = Pms_CommonData::aesEncrypt($lastname);
            
            $nk1_addressfield = $nk1->getAddress();
            $cnt_street1 = Pms_CommonData::aesEncrypt($nk1_addressfield->street);
            $cnt_zip = Pms_CommonData::aesEncrypt($nk1_addressfield->zip);
            $cnt_city = Pms_CommonData::aesEncrypt($nk1_addressfield->city);
            
            // Handling Phone-Number
            $cnt_phone = null;
            $cnt_phone = null;
            $phonenumber = $nk1->getField(5);
            $phonenumber = $phonenumber[0];
            $phonenumber = str_replace(';', ',', $phonenumber);
            $numbers = explode(',', $phonenumber);
            if (count($numbers) > 0) {
                if ($numbers[0]) {
                    $cnt_phone = Pms_CommonData::aesEncrypt($numbers[0]);
                }
            }
            if (count($numbers) > 1) {
                if ($numbers[1]) {
                    $cnt_mobile = Pms_CommonData::aesEncrypt($numbers[1]);
                }
            }
            
            $cpm = new ContactPersonMaster();
            $cpm->ipid = $ipid;
            $cpm->cnt_first_name = $cnt_first_name;
            $cpm->cnt_last_name = $cnt_last_name;
            $cpm->cnt_street1 = $cnt_street1;
            $cpm->cnt_zip = $cnt_zip;
            $cpm->cnt_city = $cnt_city;
            $cpm->cnt_phone = $cnt_phone;
            $cpm->cnt_mobile = $cnt_mobile;
            $cpm->save();
        }
    }

    private function updatePatDiagnosis($msg)
    {
        // do not update diags from klau
        $pv1 = $msg->getSegmentsByName("PV1");
        if (sizeof($pv1) > 0) {
            $pv1 = $pv1[0];
        } else {
            throw new Exception("PV1-Segement not found");
        }
        
        if ($pv1->getDischargeDate() != "0000-00-00 00:00") {
            // no updates to discharged patientes
            // return;
        }
        
        $ipid = $this->getIpidFromMessage($msg);
        // DG1
        $dg1s = $msg->getSegmentsByName("DG1");
        foreach ($dg1s as $dg1) {
            
            $diagtextfind = Doctrine_Query::create()->select('id')
                ->from('DiagnosisText')
                ->where("icd_primary = ?", $dg1->getDiagnosisCode()->code)
                ->andwhere("free_name = ?", $dg1->getDiagnosisCode()->text)
                ->andwhere("clientid= ?", $this->clientid);
            $diagtextid = $diagtextfind->fetchArray();
            if (sizeof($diagtextid) > 0) {
                $diagtextid = $diagtextid[0]['id'];
            } else {
                // Create Diagnosis-Code
                $dgtext = new DiagnosisText();
                $dgtext->clientid = $this->clientid;
                $dgtext->icd_primary = $dg1->getDiagnosisCode()->code;
                $dgtext->free_name = $dg1->getDiagnosisCode()->text;
                $dgtext->free_desc = "";
                $dgtext->save();
                $diagtextid = $dgtext->id;
            }
            
            // Getting Code for 'HD'-Diagnosistype
            $dg = new DiagnosisType();
            $diagtype = $dg->getDiagnosisTypes($this->clientid, "'HD'");
            $diagtype = $diagtype[0]['id'];
            
            $patdiagfind = Doctrine_Query::create()->select('id')
                ->from('PatientDiagnosis')
                ->where("tabname = ?", Pms_CommonData::aesEncrypt("diagnosis_freetext"))
                ->andwhere("ipid = ?", $ipid)
                ->andwhere("diagnosis_id = ?", $diagtextid)
                ->andwhere("diagnosis_type_id = ?", $diagtype);
            $patdiagid = $patdiagfind->fetchArray();
            
            if (sizeof($patdiagid) == 0) {
                $pdiag = new PatientDiagnosis();
                $pdiag->ipid = $ipid;
                $pdiag->tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");
                $pdiag->diagnosis_type_id = $diagtype;
                $pdiag->diagnosis_id = $diagtextid;
                $pdiag->icd_id = $dg1->getDiagnosisCode()->code;
                $pdiag->save();
            }
        }
    }
    
    // log to db, classify errors and infos, log EVERYTHING
    // level: 0 just log
    // level: 1 debug
    // level: 2 error
    private function updatePatient($msg, $ipid)
    {
        $pid = $msg->getSegmentsByName("PID");
        if (sizeof($pid) > 0) {
            $pid = $pid[0];
        } else {
            throw new Exception("PID-Segement not found");
        }
        
        $pv1 = $msg->getSegmentsByName("PV1");
        if (sizeof($pv1) > 0) {
            $pv1 = $pv1[0];
        } else {
            throw new Exception("PV1-Segement not found");
        }
        
        if ($pv1->getDischargeDate() != "0000-00-00 00:00") {
            // no updates to discharged patientes
            return;
        }
        
        $cust = Doctrine::getTable('PatientMaster')->findOneBy("ipid", $ipid);
        
        // familydoctor
        $familydoc_id = $this->setFamilydoctor($pv1, $cust->familydoc_id);
        if ($familydoc_id)
            $cust->familydoc_id = $familydoc_id;
        
        $bd_field = $pid->getDateOfBirth();
        if (strlen($bd_field) == 8) {
            $bd_date = substr($bd_field, 0, 4) . "-" . substr($bd_field, 4, 2) . "-" . substr($bd_field, 6, 2);
        } else {
            throw new Exception("Birthday-Field can not get parsed.");
        }
        
        $cust->birthd = $bd_date;
        
        $namefield = $pid->getPatientName();
        $patient_lastname = $namefield->lastname;
        if (is_array($namefield->lastname)) {
            $patient_lastname = $namefield->lastname[0];
        }
        $cust->last_name = Pms_CommonData::aesEncrypt($patient_lastname);
        $cust->first_name = Pms_CommonData::aesEncrypt($namefield->firstname);
        
        $addressfield = $pid->getAddress();
        $cust->street1 = Pms_CommonData::aesEncrypt($addressfield->street);
        $cust->zip = Pms_CommonData::aesEncrypt($addressfield->zip);
        $cust->city = Pms_CommonData::aesEncrypt($addressfield->city);
        
        // Handling Phone-Number
        $phonenumber = $pid->getPhoneNumber();
        $phonenumber = $phonenumber[0];
        $phonenumber = str_replace(';', ',', $phonenumber);
        $numbers = explode(',', $phonenumber);
        
        if (count($numbers) > 0) {
            if ($numbers[0]) {
                $cust->phone = Pms_CommonData::aesEncrypt($numbers[0]);
            }
        }
        if (count($numbers) > 1) {
            if ($numbers[1]) {
                $cust->mobile = Pms_CommonData::aesEncrypt($numbers[1]);
            }
        }
        
        switch (strtolower($pid->getSex())) {
            case "m":
                $sex = 1;
                break;
            case "w":
                $sex = 2;
                break;
            case "f":
                $sex = 2;
                break;
            default:
                $sex = 0;
        }
        $cust->sex = Pms_CommonData::aesEncrypt($sex);
        
        $cust->save();
        
        // location
        $this->updatePatientLocation($pv1, $ipid);
        
        // Insurance
        $in1 = $msg->getSegmentsByName("IN1");
        $this->setPatientInsurance($in1, $ipid, $pv1);
        
        // next of kin
        $nk1 = $msg->getSegmentsByName("NK1");
        $this->setNextOfKin($nk1, $ipid);
        
        // visitnumber
        $visitnumber = $pv1->getVisitNumber()->id;
        // @todo
    }
    
    
    /**
     * 16.7.83.10 MSH-9 Message Type (MSG) 00009 (2.14.9.9)
     * @return void|NULL
     */
    public function getMessageType() 
    {
        $messageType = null;
        
        if ($this->__hl7Message instanceof Net_HL7_Message) {
            $messageType = $this->__hl7Message->getSegmentFieldAsString(0, 9); // Example: "ADT^A08"    
        }
        return $messageType;
        
    }
    
    
    
    /**
     * !! multiple exit points
     * 
     * @return void|Ambigous <NULL, void, string>
     */
    public function getIpid()
    {
        $ipid = null;
        
        if ( ! empty($this->__ipid)) {
        
            $ipid = $this->__ipid;
            
        } elseif ($this->__hl7Message instanceof Net_HL7_Message) {
            
            $pid = $this->__hl7Message->getSegmentsByName("PID");
            
            if (sizeof($pid) != 1) {
                return; 
                //throw new Exception("PID-Segement not found");
            }
            
            $pid = $pid[0];
            
            $clientarray = Doctrine::getTable('Client')->findOneBy('id', $this->clientid, Doctrine_Core::HYDRATE_ARRAY);        
            if ($clientarray) {
                $epid_chars = $clientarray['epid_chars'];
            }
            
            $epid_no = $pid->getField(3);
            $epid_no = $epid_no[0];
            $epid_no = $epid_no; // ??
            $epid = $epid_chars . $epid_no;
        
            $ipid = EpidIpidMapping::getIpidFromEpidAndClientid($epid, $this->clientid);
            
            $this->setIpid($ipid);
                    
        }
        
        return $ipid;
    }
    
    public function setIpid($ipid = null)
    {
        if (empty($this->__ipid) && ! empty($ipid)) {
            
            $this->__ipid = $ipid;
        }
        
        return $this;
    }
    
    
    /**
     * @author @cla
     * @since 14.02.2018
     * update/insert into PatientVisitnumber PV1-19, PV1-44, ipid
     * 
     * @return boolean
     */
    private function __setPatientVisitnumber()
    {
        
        if ( ! $this->__hl7Message instanceof Net_HL7_Message) {
            return false; //fail-safe
        }
                    
        $PV1s = $this->__hl7Message->getSegmentsByName('PV1');
        
        $segPV1 = null;

        if (sizeof($PV1s) > 0) {
            $segPV1 = $PV1s[0];
        } else {
            return false;
        }
        
        if ( ! $segPV1 instanceof Net_HL7_Segments_PV1) {
            return false;//fail-safe
        }
        
        
        
        PatientVisitnumberTable::getInstance()->findOrCreateOneBy(
            
            //search fields
            ['ipid', 'visit_number', 'admit_date'],
             
            //search values
            [$this->__ipid, $segPV1->getVisitNumber()->id[0], $segPV1->getAdmitDate()],
        
            //data
            [
                "ipid"                 => $this->__ipid,
                "messages_received_id" => $this->__messages_received_ID,
                "visit_number"         => $segPV1->getVisitNumber()->id[0],
                "admit_date"           => $segPV1->getAdmitDate(),
            ]
        );
         
        return true;
        
        
    }
    
    
    private function __messageDeleteMovementID($movementID = '')
    {
        if (empty($movementID)) {
            return; //fail-safe
        }
        
        $ipid = $this->getIpid();
        
        if (empty($ipid)) {
            return; //fail-safe
        }
        
        
        $messages_received_IDs = [];//this are the messages we have to delete, they all have the same $movementID
        
        $messages = Hl7MessagesReceivedTable::findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
         
        foreach ($messages as $row) {
            
            $hl7Message = new Net_HL7_Message(trim($row['message']));
             
            $ZBEs = $hl7Message->getSegmentsByName('ZBE');
            
            if (count($ZBEs) == 1 && ($segZBE = $ZBEs[0]) && $segZBE instanceof Net_HL7_Segments_ZBE) {
                 
                if ($segZBE->getMovementId() == $movementID ) {
                                         
                    $messages_received_IDs[] = $row['messages_received_ID'];
                }
            }
        }
        
        /*
         * now that we have the messages_received_IDs, proceed to delete what was inserted from those messages
         */
        
        /*
         * delete visit number
         */
        $this->__deletePatientVisitnumber($messages_received_IDs);
        
        //delete disgnosis.. delete/reset whatever..
        
        return true;
    }
    
    

    private function __deletePatientVisitnumber($messages_received_IDs = [])
    {
    
        if (empty($messages_received_IDs) || ! is_array($messages_received_IDs)) {
            return; //fail-safe
        }
    
        $ipid = $this->getIpid();
    
        if (empty($ipid)) {
            return; //fail-safe
        }
    
        PatientVisitnumberTable::getInstance()->createQuery()
        ->delete()
        ->where("ipid = ?", $ipid)
        ->andWhereIn('messages_received_id', $messages_received_IDs)
        ->execute();

        return true;
    }
    
    
}

?>