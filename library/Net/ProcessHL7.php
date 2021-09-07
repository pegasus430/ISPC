<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */ 
class Net_ProcessHL7{

    public $message;
    public $msgType;

    public $parsed_diagnosis; //Diagnosis
    public $parsed_insurance; //insurance
    public $parsed_patient;
    public $parsed_contactperson;
    public $parsed_location;
    public $parsed_familydoctor;

    public $ipid;
    public $clientid;
    public $userid;
    public $testdata;


    /*
     * this selects the processor that is configured for this client
     * this class is the default
     */
    static function select_processor($conf){
        $clientid=$conf['clientid'];

        $classname=Client::getClientconfig($clientid, 'hl7_processor_class');
        if(!$classname){
            $classname="Net_ProcessHL7";
        }

        $my_processor=new $classname($conf);
        return $my_processor;
    }


    /*
     * process message with configured processor
     */
    static function process_message($msg_as_text, $conf){
        $my_processor=Net_ProcessHL7::select_processor($conf);
        $my_processor->processHL7Msg($msg_as_text);
    }


    /*
     * send message with configured processor
     */
    static function send_message($post, $conf){
        $my_processor=Net_ProcessHL7::select_processor($conf);
        $return=$my_processor->sendHL7Msg($post);
        return ($return);
    }


    /*
     * start/stop server with configured processor
     */
    static function manage_server($action,$conf){
        $my_processor=Net_ProcessHL7::select_processor($conf);
        return $my_processor->manageServer($action,$conf);
    }

    public function __construct($conf) {
        $this->config = $conf;
        $this->clientid 	= $conf['clientid'];
        $this->userid 		= $conf['userid'];
        if($conf['testdata']) {
            $this->testdata = true;
        }
        $this->ipid=null;
    }


    public function manageServer($action, $conf){
        if($action=="start" || $action=="stop"){
            $out=$this->stopServer($conf);
        }
        if($action=="start"){
            $out=$this->startServer($conf);
        }
        if($action=="monitor"){
            $out=$this->monitorServer($conf);
        }
        return $out;
    }

    public function startServer($conf){
        if(isset($this->config['serverpath_sh'])) {
            $path_parts = pathinfo($this->config['serverpath_sh']);
            $cmd = "(bash " . $this->config['serverpath_sh'] . " > " . $path_parts['dirname'] . "/server.log) &";
            $a = exec($cmd);
            $out="OK";
        }else{
            $out="No server configured!";
        }
        return $out;
    }

    public function stopServer($conf){
        if(isset($this->config['serverpath_sh'])) {
            $path_parts = pathinfo($this->config['serverpath_sh']);
            $a = exec("ps axf | grep \"" . $path_parts['basename'] . "\" | grep -v grep | awk '{print \"kill -9 \" $1}' | sh");

            $path_parts = pathinfo($this->config['serverpath_php']);
            $a = exec("ps axf | grep \"" . $path_parts['basename'] . "\" | grep -v grep | awk '{print \"kill -9 \" $1}' | sh");
        }
    }

    public function monitorServer($conf){
        $listprocs=array($this->config['serverpath_sh'], $this->config['serverpath_php']);
        $procs_list=array();

        foreach($listprocs as $proc){
            $path_parts = pathinfo($proc);
            $a = exec('ps axf | grep "'.$path_parts['basename'].'" | grep -v grep', $out);
            foreach ($out as $p){
                $procs_list[]=$p;
            }
        }

        return $procs_list;
    }

    public function sendHL7Msg($post){
        $this->ipid=$post['ipid'];
        $type=$post['type'];
        if(strlen($post['case'])<1){
            return ("Kein Fall ausgewählt");
        }
        $pcs=new PatientCaseStatus();
        $pcs=$pcs->get_patient_status($post['case']);
        if(!$pcs){
            return ("Kein gültiger Fall ausgewählt");
        }
        $casenumber=$pcs['case_number'];
        if(strlen($casenumber)<1){
            return ("Kein Fall ausgewählt");
        }
        $msg = new Net_HL7_Message();
        $msh = new Net_HL7_Segments_MSH();
        $evn = new Net_HL7_Segment("EVN");
        $pid = $this->createPIDfromIpid($this->ipid);
        $pv1 = new Net_HL7_Segment("PV1");

        $msg->addSegment($msh);
        $msg->addSegment($evn);
        $msg->addSegment($pid);
        $msg->addSegment($pv1);

        $pid->setField(18, $casenumber);
        $pv1->setField(19, $casenumber);


        if($type=="doctransferhl7") {

            if($post['fileid']<0){
                return('Keine gültige Datei: Datei ID fehlt?');
            }
            $register=Hl7DocSend::register_file($this->ipid, $this->clientid, $post['fileid']);
            $fileinfo=Hl7DocSend::get_files_for_hl7transmit($this->ipid, $this->clientid);
            $fileinfo=$fileinfo[$post['fileid']];
            $filepath=Hl7DocSend::get_filepath($this->ipid, $post['fileid']);
            $file_extension=strtolower($fileinfo['file_type']);
            $file_title=$fileinfo['title'];
            $mime=Pms_CommonData::extensionToMime($file_extension);
            if(!$mime){
                $mime="application/pdf";
            }

            $msgtype="T02";
            $timestamp=$fileinfo['create_date'];
            $uniqDocId=$fileinfo['id'];
            $uniqLocalFileName=$fileinfo['id'] . "." . strtolower($fileinfo['file_type']);
            $docDescr="";
            $msh->setField(9, "MDM^" . $msgtype);
            $evn->setField(1, $msgtype);
            $txa = new Net_HL7_Segment("TXA");
            $obx = new Net_HL7_Segment("OBX");
            $msg->addSegment($txa);
            $msg->addSegment($obx);
            $txa->setField(2, 'ACC');
            $txa->setField(3, 'PDF');
            $txa->setField(4, $timestamp);
            $txa->setField(6, $timestamp);

            $txa->setField(12, $uniqDocId);
            $txa->setField(13, $uniqDocId);
            $txa->setfield(16, $uniqLocalFileName);
            $txa->setfield(17, 'LA');
            $txa->setfield(18, "U");

            $base64cont = $this->getFileAsBase64($filepath);
            $obx->setField(2,"ED");
            $obx->setField(3, array("42","Typ-42-Document"));
            $obx->setField(4,"1");
            $obx->setField(5, array("",$mime,"","Base64",$base64cont));
            $obx->setField(11,"F");

            $server_conf=Client::getClientconfig($this->clientid, 'hl7_mdm_server');
            $host=$server_conf['mdm_host'];
            $port=$server_conf['mdm_port'];

            $return=$this->do_send_message($msg, $host, $port, false);

            if($return=="OK"){
                $register->mark_sent();
            }

            return $return;
        }
    }


 /**
 * Sends HL7-Message through Socket-Connection to remote system configured
 */
    public function do_send_message($msg, $host, $port, $convert)
    {
        $msg_as_string=$msg->toString(1);

        $send_real=false;//dbg-switch
        if(APPLICATION_ENV != 'staging' && APPLICATION_ENV != 'development'){
            $send_real=true;
        }

        if($send_real) {
            $conn = new Net_HL7_Connection($host, $port);

            if (!$conn) {
                $this->log("Unable to connect to " . $host . ":" . $port, 2);
                return "FAIL, host:" . $host . ", port: " . $port;
            }

            $resp = $conn->Send($msg, $convert);
            $conn->close();
        }else{
            $resp="xxACK";
        }
        $this->log( "Sent \n" . $msg_as_string, 0);
        $returnvalue="FAIL";

        if (isset($resp) && strpos ( $resp , 'ACK' )>0){
            $this->log( "Received answer \n" . $resp, 0);
            $returnvalue="OK";
        } else {
            $this->log( "No ACK received \n" ,2);
            if(isset($resp)) $this->log( $resp ,2);
            $returnvalue="No ACK received";
        }
        return  $returnvalue;
    }


 /*
 * Constucts PID-Segment from given $ipid-No.
 */
    public function createPIDfromIpid($ipid){

        $pmaster = Doctrine_Query::create()
            ->select('*')
            ->from('PatientMaster')
            ->where('ipid = "'.$ipid.'"')
            ->limit(1);

        $pmaster_array = $pmaster->fetchArray();

        $epid = Pms_CommonData::getEpidcharsandNum($ipid);
        $epid=$epid['num'];

        $sex="U";
        switch (Pms_CommonData::aesDecrypt($pmaster_array[0]['sex']))
        {
            case "1":
                $sex="M";
                break;
            case "2":
                $sex="F";
                break;
        }

        $pidsegment = new Net_HL7_Segments_PID();
        $pidsegment->setPatientId($epid);
        $pidsegment->setPatientName(Pms_CommonData::aesDecrypt($pmaster_array[0]['last_name']), Pms_CommonData::aesDecrypt($pmaster_array[0]['first_name']));
        $pidsegment->setDateOfBirth(str_replace('-','',$pmaster_array[0]['birthd']));
        $pidsegment->setSex($sex);

        $mdm_msg = new Net_HL7_Message();
        $mdm_msg ->addSegment($pidsegment);

        return $pidsegment;
    }

    public function processHL7Msg($msg_as_text){

        $this->parse_message($msg_as_text);

        //Skip messages for events older than 1 day
        $this->filter_by_msgdate_vs_zbedate(60*60*24);

        if(!$this->msgType=="ERROR") {
            $this->getIpidFromMessage();
        }

        try{
            //Depending on MsgType select further proceed
            switch($this->msgType){
                case "ERROR":
                case "ACK":
                    break;

                //changeEPID
                case "ADT^A40":
                    break;

                //Aufnahmedaten stornieren (ADT^A11)
                //Verlegung stornieren (ADT^A12)
                case "ADT^A11":
                case "ADT^A12":
                    break;

                //Entlassung (ADT^A03)
                case "ADT^A03":
                    //ignore message - discharge in ispc only
                    break;

                //Entlassung stornieren (??)
                case "ADT^A13":
                    break;

                case "ORM^O01":
                    break;

                // Admission Patient anlegen (ADT^A01)
                // Ambulate Aufnahme(ADT^A04)
                // Fallartwechsel (ADT^A06 / ADT^A07)
                // Verlegung aus anderer Abteilung (ADT^A02)
                case "ADT^A04":
                case "ADT^A02":
                case "ADT^A06":
                case "ADT^A07":
                case "ADT^A01":
                case "ADT^A01^ADT_A01":
                    if (!isset($this->ipid)) {
                        $this->createPatient();
                    } elseif (isset($this->ipid)) {
                        $this->reactivate_patient();
                        $this->updatePatient();
                    }
                    $this->setClinicCase();
                    $this->setPatientLocation();
                    $this->addPatientDiagnosis();
                    $this->setFamilydoctor(false);
                    $this->setPatientInsurance(false);
                    $this->setNextOfKin();
                    break;

                // Change Patient-Data (ADT^A08)
                case "ADT^A08":
                    if (!isset($ipid) && $this->zbe_update) {
                        $this->log("IGNORED MESSAGE: Update of nonexistent patient.", 2);
                    } elseif (isset($ipid) && $this->zbe_update) {
                        //$this->updatePatient();
                        $this->addPatientDiagnosis();
                        $this->setFamilydoctor(false);
                        $this->setPatientInsurance(false);
                        $this->setNextOfKin();
                    }
                    break;

                default:
                    $this->log("Unknown MessageType:" . $this->msgType, 2);
            }

        } catch (Exception $e){
            $this->log("ERROR:" . $e->getMessage(),2);
        }
    }

    public function filter_by_msgdate_vs_zbedate($seconds){
        $zbe=$this->message->getSegmentsByName("ZBE");
        if (sizeof ($zbe)>0) {
            $msgDate = $this->message->getSegmentFieldAsString(0, 7);

            $zbe=$zbe[0];
            $zdate=$zbe->getField(2);

            if ($zdate && strlen($zdate)==14){
                if(abs(strtotime($msgDate) - strtotime($zdate)) > $seconds){
                    $this->log('Message seems to be outdated. Message not processed.',2);
                    $this->msgType = "ERROR";
                }
            }
        }
    }

    public function parse_message($msg_as_text){
        try {
            $this->message = new Net_HL7_Message(trim($msg_as_text));
            $this->log("Received:\n".$this->message->toString(1),1);
            $this->msgType = $this->message->getSegmentFieldAsString(0, 9); //Example: "ADT^A08"

            $this->zbe_update==false;
            $zbe = $this->message->getSegmentsByName("ZBE");
            if (sizeof ($zbe)>0) {
                $zbe = $zbe[0];
                $zbe=$zbe->getField(4);
                if($zbe==="UPDATE"){
                    $this->zbe_update=true;
                }
            }

            $this->parsePatient();
            $this->parseFamilydoctor();
            $this->parseNextOfKin();
            $this->parsePatientDiagnosis();
            $this->parsePatientInsurance();
            $this->parsePatientLocation();

        } catch (Exception $e) {
            $this->log($e->getMessage (),2);
            $this->msgType = "ERROR";
        }
    }

    public function getIpidFromMessage(){
        $pat = $this->parsed_patient;
        if ( !is_array($pat) || !strlen($pat['epid_no']) ) {
            throw new Exception("no Patient EPID");
        }
        $client = Doctrine::getTable('Client')->findOneBy('id',$this->clientid);
        $clientarray = $client->toArray();
        $epid_chars = $clientarray['epid_chars'];
        $epid = $epid_chars . $pat['epid_no'];
        $this->ipid = Pms_CommonData::getIpidFromEpid($epid);
    }

    public function reactivate_patient(){
        //reactivate deleted patients
        $patient = Doctrine::getTable('PatientMaster')->findOneBy('ipid',$this->ipid);
        if($patient->isdelete==1){
            $patient->isdelete=0;
            $patient->isstandby=0;
            $patient->save();
        }
        if($patient->isstandbydelete==1){
            $patient->isdelete=0;
            $patient->isstandbydelete=0;
            $patient->save();
        }
    }


    public function parsePatient(){
        $pid = $this->message->getSegmentsByName("PID");
        if (sizeof ($pid)>0) {
            $pid=$pid[0];
        }else {
            throw new Exception("PID-Segement not found");
        }

        $pat=array();

        $pat['epid_no']         = $pid->getPatientID()->id;

        $namefield = $pid->getPatientName();
        $pat['last_name']       = $namefield->lastname;
        $pat['first_name']      = $namefield->firstname;

        $bd_field = $pid->getDateOfBirth();
        if (strlen($bd_field)==8) {
            $pat['birthd'] = substr($bd_field,0,4) . "-" . substr($bd_field,4,2) . "-" . substr($bd_field,6,2);
        } else {
            throw new Exception("Birthday-Field can not get parsed.");
        }

        $addressfield = $pid->getAddress();
        $pat['street1'] 	= 	$addressfield->street;
        $pat['zip'] 		= 	$addressfield->zip;
        $pat['city'] 	    = 	$addressfield->city;

        $phonenumber=$pid->getPhoneNumber();
        $phonenumber=str_replace(';',',',$phonenumber);
        $numbers=explode(',',$phonenumber);
        if (count($numbers)>0){
            if(strlen($numbers[0])){
                $pat['phone'] 	= 	$numbers[0];
            }
        }
        if (count($numbers)>1){
            if(strlen($numbers[1])){
                $pat['mobile'] 	= 	$numbers[1];
            }
        }

        switch(strtolower ($pid->getSex()))
        {
            case 'm' : $sex = 1; break;
            case 'f' : $sex = 2; break;
            default : $sex = 0;
        }
        $pat['sex'] = $sex;

        $this->parsed_patient = $pat;

    }

    public function createPatient(){
        $pat=$this->parsed_patient;

        $ipid =Pms_Uuid::GenerateIpid();
        $this->ipid=$ipid;

        $cust = new PatientMaster();
        $cust->isadminvisible   = 1;
        $cust->ipid             = $this->ipid;
        $cust->last_name        = Pms_CommonData::aesEncrypt($pat['last_name']);
        $cust->first_name       = Pms_CommonData::aesEncrypt($pat['first_name']);
        $cust->birthd           = $pat['birthd'];
        $cust->sex              = Pms_CommonData::aesEncrypt($pat['sex']);
        $cust->street1 	        = Pms_CommonData::aesEncrypt($pat['street']);
        $cust->zip 		        = Pms_CommonData::aesEncrypt($pat['zip']);
        $cust->city 	        = Pms_CommonData::aesEncrypt($pat['city']);
        if(isset($pat['phone'])){
            $cust->phone=Pms_CommonData::aesEncrypt($pat['phone']);
        }
        if(isset($pat['mobile'])){
            $cust->mobile=Pms_CommonData::aesEncrypt($pat['mobile']);
        }

        $adm_date               = date("Y-m-d H:i:s",time());
        $cust->admission_date   = $adm_date;
        $cust->recording_date   = $adm_date;
        $cust->save();

        $client = Doctrine::getTable('Client')->findOneBy('id',$this->clientid);
        $clientarray = $client->toArray();
        $epid_chars = $clientarray['epid_chars'];
        $epid = $epid_chars . $pat['epid_no'];
        $res = new EpidIpidMapping();
        $res->clientid          = $this->clientid;
        $res->ipid              = $this->ipid;
        $res->epid              = $epid;
        $res->epid_chars        = $epid_chars;
        $res->epid_num          = $pat['epid_no'];
        $res->visible_since     = substr(date("Y-m-d H:i:s",time()), 0, 10);
        $res->discharge_since   = "0000-00-00";
        $res->save();
    }


    public function setClinicCase(){
        $pv1 = $this->message->getSegmentsByName("PV1");
        if (sizeof ($pv1)>0) {
            $pv1=$pv1[0];
        }else {
            throw new Exception("PV1-Segement not found");
        }


        $case_number = $pv1->getVisitNumber()->id;
        $case_type="";

        $location_field = $this->parsed_location;
        $org1=$location_field['org1'];
        $org2=$location_field['org2'];

        if ($org1 == "PAGKO") {$case_type="konsil";}

        if ($org1 == "PAGL23"){
            $case_type =  "station";
        }
        if ($org1 == "PAGL22" || $org1 == "PAGA"){
            $case_type =  "ambulant";
        }
        if ($org1 == "PAGSAPV" || $org2 == "PAGSAPV"  ){
            $case_type =  "sapv";
        }

        if($case_type!=="") {

            $patient = Doctrine::getTable('PatientMaster')->findOneBy('ipid',$this->ipid);
            $patient->isdischarged = 0;
            $patient->save();

            $pcs=new PatientCaseStatus();
            $caseinfo=array(
                'case_number'=>$case_number,
                'case_type'=>$case_type,
                'admdate'=>date('d.m.Y'),
                'admtime'=>date('H:i')
            );
            $pcs->update_patient_status($this->ipid, $this->clientid, 0, $caseinfo);

            $title="Patient wurde zum " . date("d.m.Y H:i", time()) . " aktiviert";
            $this->addToPatientCourse($this->ipid, "K", $title);
        }
    }


    public function parsePatientDiagnosis(){
        $dg1s = $this->message->getSegmentsByName("DG1");
        $diags=array();
        foreach ($dg1s as $dg1) {
            $diags[]=array(
                'code'=>$dg1->getDiagnosisCode()->code,
                'text'=>$dg1->getDiagnosisCode()->text
            );
        }
        $this->parsed_diagnosis=$diags;
    }
    /*
     * Adds diagnosis from message if this diag is not already present
     */
    public function addPatientDiagnosis(){
        if(!is_array($this->parsed_diagnosis)){
            return;
        }

        foreach ($this->parsed_diagnosis as $dg1) {

            $diagtextfind = Doctrine_Query::create()
                ->select('id')
                ->from('DiagnosisText')
                ->where(	"icd_primary = ?", $dg1['code'])
                ->andwhere(	"free_name = ?", $dg1['text'])
                ->andwhere( "clientid= ?", $this->clientid );
            $diagtextid = $diagtextfind->fetchArray();
            if(sizeof($diagtextid)>0) {
                $diagtextid=$diagtextid[0]['id'];
            } else {
                //Create Diagnosis-Code
                $dgtext = new DiagnosisText();
                $dgtext->clientid = $this->clientid;
                $dgtext->icd_primary = $dg1['code'];
                $dgtext->free_name = $dg1['text'];
                $dgtext->free_desc = "";
                $dgtext->save();
                $diagtextid = $dgtext->id;
            }

            //Getting Code for 'HD'-Diagnosistype
            $dg = new DiagnosisType();
            $diagtype = $dg->getDiagnosisTypes($this->clientid,"'HD'");
            $diagtype = $diagtype[0]['id'];


            $patdiagfind = Doctrine_Query::create()
                ->select('id')
                ->from('PatientDiagnosis')
                ->where(	"tabname = ?",Pms_CommonData::aesEncrypt("diagnosis_freetext"))
                ->andwhere(	"ipid = ?",	$this->ipid)
                ->andwhere(	"diagnosis_id = ?", $diagtextid)
                ->andwhere(	"diagnosis_type_id = ?"	, $diagtype);
            $patdiagid = $patdiagfind->fetchArray();

            if (sizeof($patdiagid)==0)
            {
                $pdiag = new PatientDiagnosis();
                $pdiag->ipid = $this->ipid;
                $pdiag->tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");
                $pdiag->diagnosis_type_id = $diagtype;
                $pdiag->diagnosis_id = $diagtextid;
                $pdiag->icd_id = $dg1['code'];
                $pdiag->save();
            }
        }
    }

    public function parsePatientLocation(){
        $pv1 = $this->message->getSegmentsByName("PV1");
        if (sizeof ($pv1)>0) {
            $pv1=$pv1[0];
        }else {
            throw new Exception("PV1-Segement not found");
        }

        $loc=array();
        $loc['ambu']    = $pv1->getPatientClass();

        $location_field = $pv1->getAssignedPatientLocation();

        $loc['org1']        = $location_field->org1;
        $loc['org2']        = $location_field->org2;
        $loc['bed']         = $location_field->bed;
        $loc['room']        = $location_field->room;
        $loc['mapped']      = $this->mapstations($location_field->org1);

        $loc['type']        = "1"; //1=Hospital, 5=Home
        $loc['street']      = "";
        $loc['city']        = "";
        $loc['zip']         = "";
        $loc['phone']       = "";

        if($loc['org1'] == "PAGL23"){
            $locname = str_replace("PAGL23", "GL23", $loc['bed']);
            $locname = $loc['org1'] . str_replace("GL23", " Zimmer ", $locname);
        }
        elseif ($loc['ambu']=="O" && $loc['mapped']!="NOT"){
            $locname = $loc['mapped'];
            if($loc['room'] && strlen($loc['room'])>3){
                $room=substr($loc['room'],-3,3);
                $locname = $locname . " Zimmer " . $room;
            }
        }
        else {
            $locname = $loc['mapped'];
            if($loc['room'] && strlen($loc['room'])>3){
                $room=substr($loc['room'],-3,3);
                $locname = $locname . " Zimmer " . $room;
            }
        }

        if(strlen($loc['org1'])) {
            $loc['pretty']=$locname;
            $this->parsed_location = $loc;
        }
    }

    public function setPatientLocation(){
        $ploc=$this->parsed_location;
        if(!is_array($ploc)){
            return;
        }

        $adm_date       = date("Y-m-d H:i:s",time());

        if (strlen($ploc['pretty'])){
            $locationFind = Doctrine_Query::create()
                ->select('id')
                ->from('Locations')
                ->where("location = ?",Pms_CommonData::aesEncrypt($ploc['pretty']))
                ->andWhere("street = ?", $ploc['street'])
                ->andWhere("phone1 = ?", $ploc['phone'])
                ->andWhere("client_id = ?", $this->clientid)
                ->andWhere("isdelete='0'");
            $locId = $locationFind->fetchArray();


            if (sizeof($locId)==0){
                $location = new Locations();
                $location->client_id        = $this->clientid;
                $location->location         = Pms_CommonData::aesEncrypt($ploc['pretty']);
                $location->location_type    = $ploc['type'];
                $location->street           = $ploc['street'];
                $location->zip              = $ploc['zip'];
                $location->city             = $ploc['city'];
                $location->phone1           = $ploc['phone'];
                $location->phone2           = "";
                $location->fax              = "";
                $location->comment          = "";
                $location->save();
                $locId = $location->id;
            } else {
                $locId = $locId[0]['id'];
            }

            $old_location = Doctrine::getTable('PatientLocation')
                ->findOneByIpidAndValidTill($this->ipid,"0000-00-00");

            if ($old_location && $old_location->location_id!=$locId){
                $old_location->valid_till = $adm_date;
                $old_location->save();
            }

            if(!$old_location || $old_location->location_id!=$locId){
                $loc = new PatientLocation();
                $loc->clientid = $this->clientid;
                $loc->ipid =$this->ipid;
                $loc->location_id = $locId;
                $loc->reason = "";
                $loc->reason_txt ="";
                $loc->hospdoc ="";
                $loc->transport ="";
                $loc->valid_from = $adm_date;
                $loc->valid_till = "0000-00-00";
                $loc->admission_comments = "";
                $loc->save();
            }
        }
    }

    public function mapstations($statname){
        $stations=array(
            'ANGA'=>'GA'        ,
            'ANGH2'=>'H2'       ,
            'ANGI3'=>'I3'       ,
            'ANISMAI'=>'SFR'    ,
            'CHGG2'=>'G2'       ,
            'CHGG5'=>'G5'       ,
            'CHGG6'=>'G6'       ,
            'CHGG7'=>'G7'       ,
            'CHGH21'=>'H21'     ,
            'CHGH22C'=>'H22C'   ,
            'CHGH5A'=>'H5A'     ,
            'CHGH5B'=>'H5B'     ,
            'CHGH6'=>'H6'       ,
            'CHGH7'=>'H7'       ,
            'CHGPA'=>'PA'       ,
            'CHGPB'=>'PB'       ,
            'CHGPR'=>'PR'       ,
            'CHIBG'=>'BG'       ,
            'CHIFW'=>'FW'       ,
            'CHIGF'=>'GF'       ,
            'CHINOT'=>'NOT'     ,
            'CHIPLAS'=>'PLAS'   ,
            'CHIPR'=>'PR'       ,
            'CHIS1'=>'S1'       ,
            'CHIS2'=>'S2'       ,
            'CHIS3'=>'S3'       ,
            'CHIS4'=>'S4'       ,
            'CHIS5'=>'S5'       ,
            'CHISHOCK'=>'SHOCK' ,
            'CHITN'=>'TN'       ,
            'CHITP'=>'TP'       ,
            'CHIUN'=>'UN'       ,
            'CHIUN10'=>'UN10'   ,
            'CHIVIS'=>'VIS'     ,
            'CHIVIS10'=>'VIS10' ,
            'DEIONKO'=>'ONKO'   ,
            'DEIVENEN'=>'CHWUN' ,
            'FRGBRCA'=>'BRCA'   ,
            'FRGBRUST'=>'GBRU'  ,
            'FRGEKRIN'=>'EKRIN' ,
            'FRGENTB'=>'ENTB'   ,
            'FRGH10'=>'H10'     ,
            'FRGI21B'=>'I21B'   ,
            'FRGI4'=>'I4'       ,
            'FRGI5'=>'I5'       ,
            'FRGI5B'=>'I5B'     ,
            'FRGP'=>'P'         ,
            'FRIBRUST'=>'IBRU'  ,
            'FRINOT'=>'NOT'     ,
            'FRION1'=>'ON1'     ,
            'HCGG3'=>'G3'       ,
            'HCGG3I'=>'G3I'     ,
            'HCGH3A'=>'H3A'     ,
            'HCGHS'=>'HS'       ,
            'HCGI21A'=>'I21A'   ,
            'HCGLTX'=>'HCLTX'   ,
            'HCGP'=>'P'         ,
            'HCGTRA'=>'TRA'     ,
            'HNGI22B'=>'I22B'   ,
            'HNGI6'=>'I6'       ,
            'HNGI7'=>'I7'       ,
            'HNGI8'=>'I8'       ,
            'HNGP'=>'P'         ,
            'HNGPA'=>'PA'       ,
            'IBGG0'=>'G0'       ,
            'IBGHAP'=>'HAP'     ,
            'IBGHASIN'=>'HASIN' ,
            'IBGHASMI'=>'HASMI' ,
            'IBGNOT'=>'NOT'     ,
            'IBGTRA'=>'TRA'     ,
            'IBGTRAHE'=>'TRAHE' ,
            'MIGF0A'=>'F0A'     ,
            'MIGF10A'=>'F10A'   ,
            'MIGF10B'=>'F10B'   ,
            'MIGF11'=>'F11'     ,
            'MIGF21'=>'F21'     ,
            'MIGF22'=>'F22'     ,
            'MIGF2A'=>'F2A'     ,
            'MIGF2B'=>'F2B'     ,
            'MIGF2C'=>'F2C'     ,
            'MIGF3'=>'F3'       ,
            'MIGF4'=>'F4'       ,
            'MIGF5A'=>'F5A'     ,
            'MIGF5B'=>'F5B'     ,
            'MIGF5PTC'=>'F5PTC' ,
            'MIGF6A'=>'F6A'     ,
            'MIGF6B'=>'F6B'     ,
            'MIGF7'=>'F7'       ,
            'MIGF8'=>'F8'       ,
            'MIGF9'=>'F9'       ,
            'MIGG10A'=>'G10A'   ,
            'MIGG10B'=>'G10B'   ,
            'MIGG22'=>'G22'     ,
            'MIGKMT'=>'KMT'     ,
            'MIGL21'=>'L21'     ,
            'MIGLDL'=>'LDL'     ,
            'MIGLFD'=>'GLFD'    ,
            'MIGM21'=>'M21'     ,
            'MIGMUK'=>'GMUK'    ,
            'MIGNEPH'=>'NEPH'   ,
            'MIGOST'=>'OST'     ,
            'MIGPHOTO'=>'PHOTO' ,
            'MIGPM1'=>'PM1'     ,
            'MIGPM2'=>'PM2'     ,
            'MIGPM3'=>'PM3'     ,
            'NCGG21B'=>'G21B'   ,
            'NCGH3B'=>'H3B'     ,
            'NCGH9'=>'H9'       ,
            'NCGI9A'=>'I9A'     ,
            'NCGI9B'=>'I9B'     ,
            'NCGKO'=>'KO'       ,
            'NCGNP'=>'NP'       ,
            'NCGP'=>'NCHIR-A'   ,
            'NRGAIV'=>'AIV'     ,
            'NRGALS'=>'ALS'     ,
            'NRGEEG'=>'EEG'     ,
            'NRGEMG'=>'EMG'     ,
            'NRGENG'=>'ENG'     ,
            'NRGEP'=>'EP'       ,
            'NRGG21A'=>'G21A'   ,
            'NRGG8'=>'G8'       ,
            'NRGG8STR'=>'G8STR' ,
            'NRGH8'=>'H8'       ,
            'NRGH8EPI'=>'STH8'  ,
            'NRGI2'=>'I2'       ,
            'NRGI8'=>'I8'       ,
            'PAGL23'=>'L23'     ,
            'PAGA'=>'L22'       ,
            'STGK21'=>'K21'     ,
            'STGK22A'=>'K22A'   ,
            'STGK22B'=>'K22B'   ,
            'URGG4'=>'G4'       ,
            'URGH22A'=>'H22A'   ,
            'URGH4'=>'H4'       ,
            'URGI22A'=>'I22A');
        $a=$stations[$statname];
        if (!$a){
            $a=$statname;
        }
        return $a;
    }


    public function parsePatientInsurance(){
        $in1a=$this->message->getSegmentsByName('IN1');
        $pv1=$this->message->getSegmentsByName('PV1');

        $insurance=array();
        if (sizeof ($in1a)>0 && sizeof($pv1)>0) {
            $pv1 = $pv1[0];
            foreach ($in1a as $in1) {

                $company_id = $in1->getCompanyId();
                if (is_array($company_id)) {
                    $company_id = end($company_id);
                }
                if(!strlen($company_id)){
                    continue;
                }

                $insurance['company_id'] = $company_id;
                $insurance['insurance_no'] = $in1->getPolicyNumber();
                $insurance['companyname'] = $in1->getCompanyName();

                $ins_address_field = $in1->getCompanyAddress();
                $insurance['ins_country'] = $ins_address_field->country;
                $insurance['ins_street'] = $ins_address_field->street;
                $insurance['ins_zip'] = $ins_address_field->ins_zip;
                $insurance['ins_city'] = $ins_address_field->ins_city;

                $isname_field = $in1->getNameOfInsured();
                $insurance['ins_first_name'] = $isname_field->firstname;
                $insurance['ins_last_name'] = $isname_field->lastname;

                $bd_field = $in1->getInsuredsDateOfBirth();
                if (strlen($bd_field) == 8) {
                    $bd_date = substr($bd_field, 0, 4) . "-" . substr($bd_field, 4, 2) . "-" . substr($bd_field, 6, 2);
                    $insurance['date_of_birth'] = $bd_date;
                }

                $insurance['privatepatient'] = $pv1->getChargePriceIndicator() == "P" ? 1 : 0;
                if ($pv1->getField(28) == "X") {
                    $insurance['privatepatient'] = 1;
                }

                if (is_array($insurance['companyname'])) {
                    $insurance['companyname'] = implode(" & ", $insurance['companyname']);
                }

                $status_no = $in1->getField(15);
                if (strlen($status_no) > 1) {
                    $status_no = $status_no[0];
                }
                $statusmap = array(
                    '' => '',
                    '3' => 'F',
                    '5' => 'R',
                    '1' => 'M'
                );
                $insurance['insurance_no'] = $statusmap[$status_no];
            }
        }

        if(strlen($insurance['companyname'])){
            $this->parsed_insurance=$insurance;
        }
    }

    public function setPatientInsurance($update_on_existing_entries){

        if (count($this->parsed_insurance)) {
            $in=$this->parsed_insurance;

            $cust = Doctrine::getTable('PatientHealthInsurance')->findOneByIpid($this->ipid);

            if (!$cust){
                $cust = new PatientHealthInsurance();
            }else{
                if(!$update_on_existing_entries){
                    return;
                }
            }
            $cust->ipid = $this->ipid;

            $cust->kvk_no = $in['company_id'];
            $cust->insurance_no = $in['insurance_no'];

            $cust->company_name = Pms_CommonData::aesEncrypt($in['companyname']);

            $cust->ins_first_name = Pms_CommonData::aesEncrypt($in['ins_first_name']);
            $cust->ins_last_name  = Pms_CommonData::aesEncrypt($in['ins_last_name']);
            if(isset($in['date_of_birth'])) {
                $cust->date_of_birth = $in['date_of_birth'];
            }

            $cust->ins_country  = $in['ins_country'];
            $cust->ins_street   = Pms_CommonData::aesEncrypt($in['ins_street']);
            $cust->ins_zip      = Pms_CommonData::aesEncrypt($in['ins_zip']);
            $cust->ins_city     = Pms_CommonData::aesEncrypt($in['ins_city']);

            if ($in['privatpatient']){$cust->privatepatient=1;}

            $cust->insurance_status=Pms_CommonData::aesEncrypt($in['unsurance_status']);


            $cust->save();
        }
    }

    public function parseNextOfKin(){
        $nk=$this->message->getSegmentsByName('NK1');

        if (sizeof ($nk)<=0) {
            return;
        }
        $persons=array();
        foreach ($nk as $nk1){
            $person=array();
            $nk1_namefiled = $nk1->getNKName();
            $person['first_name'] = $nk1_namefiled->firstname;
            $person['last_name']  = $nk1_namefiled->lastname;

            $nk1_addressfield   = $nk1->getAddress();
            $person['street']   = $nk1_addressfield->street;
            $person['zip']      = $nk1_addressfield->zip;
            $person['city']     = $nk1_addressfield->city;

            //Handling Phone-Number
            $phonenumber=$nk1->getPhoneNumber()->number;
            $phonenumber=str_replace(';',',',$phonenumber);
            $numbers=explode(',',$phonenumber);
            if (count($numbers)>0){
                if($numbers[0]){
                    $person['phone'] 	= 	$numbers[0];
                }
            }
            if (count($numbers)>1){
                if($numbers[1]){
                    $person['mobile'] 	= 	$numbers[1];
                }
            }
            $persons[]=$person;
        }
        if(count($persons)){
            $this->parsed_contactperson=$persons;
        }
    }

    /*
     * Only adds contact persons if no contact present already
     */
    public function setNextOfKin(){
        $nks=$this->parsed_contactperson;
        if (sizeof ($nks)<=0) {
            return;
        }
        $old_nk = Doctrine::getTable('ContactPersonMaster')->findByIpidAndIsdelete($this->ipid, "0");
        if(count($old_nk)){
            return;
        }
        foreach($nks as $nk) {
            $cpm = new ContactPersonMaster();
            $cpm->ipid = $this->ipid;
            $cpm->cnt_first_name = Pms_CommonData::aesEncrypt($nk['first_name']);
            $cpm->cnt_last_name = Pms_CommonData::aesEncrypt($nk['last_name']);
            $cpm->cnt_street1 = Pms_CommonData::aesEncrypt($nk['street']);
            $cpm->cnt_zip = Pms_CommonData::aesEncrypt($nk['zip']);
            $cpm->cnt_city = Pms_CommonData::aesEncrypt($nk['city']);
            $cpm->cnt_phone = Pms_CommonData::aesEncrypt($nk['phone']);
            $cpm->cnt_mobile = Pms_CommonData::aesEncrypt($nk['mobile']);
            $cpm->save();
        }
    }


    public function parseFamilydoctor(){
        $pv1=$this->message->getSegmentsByName("PV1");
        if(!count($pv1)){
            return;
        }

        $pv1=$pv1[0];
        $fdocfield = $pv1->getConsultingDoctor();

        $doc=array();
        $doc['first_name']      = $fdocfield->firstname;
        $doc['last_name']       = $fdocfield->lastname;
        $doc['street']          = $fdocfield->street;
        $doc['title']           = $fdocfield->degree;
        $doc['zip']             = $fdocfield->zipcity[0];
        $doc['city']            = $fdocfield->zipcity[1];
        $doc['phone_practice']  = $fdocfield->phone;
        $doc['fax']             = $fdocfield->fax;
        $doc['doctornumber']    = $fdocfield->id;

        if(strlen($doc['last_name'])){
            $this->parsed_familydoctor=$doc;
        }


    }

    public function setFamilydoctor($update_on_existing_entries){
        $cust = Doctrine::getTable('PatientMaster')->findOneBy('ipid', $this->ipid);
        if($cust->familydoc_id > 0){
            if(!$update_on_existing_entries) {
                return;
            }
        }

        $doc=$this->parsed_familydoctor;
        if(!count($doc)){
            return;
        }

        $fdocfind = Doctrine_Query::create()
            ->select('id')
            ->from('FamilyDoctor')
            ->where(	"first_name = ?",   $doc['first_name'])
            ->andwhere(	"last_name = ?",    $doc['last_name'])
            ->andwhere(	"zip = ?",          $doc['zip'])
            ->andWhere(	"clientid=?",       $this->clientid)
            ->andWhere ("indrop=0")
            ->andWhere(	"isdelete='0'");

        $fdocId = $fdocfind->fetchArray();

        if (sizeof($fdocId)==1) {
            $familydoc_id = $fdocId[0]['id'];
        } else {
            $fdoc = new FamilyDoctor();
            $fdoc->clientid         = $this->clientid;
            $fdoc->first_name       = $doc['first_name'];
            $fdoc->last_name        = $doc['last_name'];
            $fdoc->street1          = $doc['street'];
            $fdoc->title            = $doc['title'];
            $fdoc->zip              = $doc['zip'];
            $fdoc->city             = $doc['city'];
            $fdoc->phone_practice   = $doc['phone_practice'];
            $fdoc->fax              = $doc['fax'];
            $fdoc->doctornumber     = $doc['doctornumber'];
            $fdoc->indrop           = 0;
            $fdoc->save();

            $familydoc_id = $fdoc->id;
        }

        if($familydoc_id>0) {
            $cust->familydoc_id = $familydoc_id;
            $cust->save();
        }
    }


    public function addToPatientCourse($ipid, $type, $title, $date="", $done_date="", $tabname="", $recordid=""){
        if($date==""){
            $date=date("Y-m-d H:i:s", time());
        }
        if ($done_date==""){
            $done_date = $date;
        }
        $cust = new PatientCourse();
        $cust->ipid = $ipid;
        $cust->course_date = $date;
        $cust->course_type = Pms_CommonData::aesEncrypt($type);
        $cust->course_title = Pms_CommonData::aesEncrypt(addslashes($title));
        $cust->user_id = $this->userid;
        $cust->done_date = $done_date;
        $cust->done_name = "";
        $cust->tabname = Pms_CommonData::aesEncrypt($tabname);
        $cust->recordid = $recordid;
        $cust->save();
    }

    //log to db, classify errors and infos, log EVERYTHING
    // level: 0 just log
    // level: 1 debug
    // level: 2 error
    public function log($msg, $level=0) {
        //log everything to db
        $logmsg = ($this->config['encryptlog']==true) ? Pms_CommonData::aesEncrypt($msg) : $msg;
        try{
            Hl7Log::log_hl7($logmsg,$level);
        } catch (Exception $e) {
            echo "Logging not possible! ";
        }

        //print to console depending on loglevel
        if ( $level >= $this->config['verbosity'] ) {
            echo "[".date('Y-m-d H:i:s')."] " . $msg . "\r\n";
        }
    }


    /*
 * Reads file and returns it as base64-encoded string
 * @param path to input file
 */
    public static  function getFileAsBase64($file){
        if (file_exists($file)==false) return false;
        $data = file_get_contents($file);
        $base64 = base64_encode($data);
        return($base64);
    }
}