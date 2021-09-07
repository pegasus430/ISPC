<?php
/*
 * @cla on 09.10.2018
 * ISPC-2254
 * this file was replaced with the new version received from Nico
 * re-added init() If module
 * re-added fn casenumberAction()
 * 
 */

class SystemssyncController extends Zend_Controller_Action
{
	public function init()
	{
        $logininfo= new Zend_Session_Namespace('Login_Info');

        $this->clientid=$logininfo->clientid;
        $this->usertype=$logininfo->usertype;

        //Sync module
        if(!Modules::checkModulePrivileges("115", $logininfo->clientid)) {
            echo 'No Sync module';
            exit;
        }
	}


    public function ipidAction(){
        $patid_from=$_GET['id'];

        $pid_from=Pms_Uuid::decrypt($patid_from) ;
        $ipid_from=Pms_CommonData::getIpid($pid_from);

        $epid_from=Pms_CommonData::getEpid($ipid_from);

        echo "<pre>";

        echo "ipid:\t" . $ipid_from ."\n";
        echo "epid:\t" . $epid_from ."\n";

        echo "</pre>";
        exit();


    }

    public function testversorgersyncAction(){
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        $v=new Versorger();
        $v->update_patient_from_exportpackage('005eb53d982e9feef5b4b0cb2cbddba8c0f30e18');
    }

    public function drop22Action(){
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        if($_POST['payload']){
            $data=urldecode($_POST['payload']);
            $data=unserialize($data);
            $ssync=new SystemsSync($this->clientid);
            $ret=$ssync->receivePatient($data);
            echo "\n".$ret;
            exit();
        }else{
            //Comment out and paste datastring to data for debug
            //$data='';
            //$data=urldecode($data);
            //$data=unserialize($data);
            //$ssync=new SystemsSync(81);
            //$ret=$ssync->receivePatient($data);
            exit();
        }
    }


    public function dropAction(){
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        if($_POST['payload']){
            $data=urldecode($_POST['payload']);
            $data=unserialize($data);

            $ipid_there=$data['_meta']['ipid_here'];
            $ipid_here=$data['_meta']['ipid_there'];

            if($ipid_here){
                $pat = Doctrine::getTable('SystemsSyncPatients')->findOneByIpid_hereAndConnectionAndClientid($ipid_here,$data['_meta']['connection'],$this->clientid);
            }
            if(!$pat && $ipid_there){
                $pat = Doctrine::getTable('SystemsSyncPatients')->findOneByIpid_thereAndConnectionAndClientid($ipid_there,$data['_meta']['connection'],$this->clientid);
            }


            if(!$pat){
                //put patient to staging-list

                $filepath="sync_staging_" . date('ymdhis') . rand(10000,99999);
                $filepath=PUBLIC_PATH . "/uploadfile/" . $filepath;

                $staging=new SystemsSyncStaging();
                $staging->connection    = $data['_meta']['connection'];
                $staging->ipid_there    = $ipid_there;
                $staging->clientid      = $this->clientid;
                $staging->date_received = date('Y-m-d H:i:s');
                $staging->first_name    = $data['PatientMaster'][0]['first_name'];
                $staging->last_name     = $data['PatientMaster'][0]['last_name'];
                $staging->birthd        = $data['PatientMaster'][0]['birthd'];
                $staging->filepath      = $filepath;
                $staging->save();

                $gz = gzopen($filepath,'w1');
                $data=serialize($data);
                gzwrite($gz, $data, strlen($data));
                gzclose($gz);

                $ret="OK";
                echo "\n".$ret;
                exit();
            }else{
                //sync-target is known: do sync
                $ssync=new SystemsSync($this->clientid);
                $ret=$ssync->receivePatient($data);
                echo "\n".$ret;
                exit();
            }


        }else{
            //Comment out and paste datastring to data for debug
            //$data='';
            //$data=urldecode($data);
            //$data=unserialize($data);
            //$ssync=new SystemsSync(81);
            //$ret=$ssync->receivePatient($data);
            exit();
        }
    }

    public function droptestAction(){
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        if($_POST['payload']){
            $data=urldecode($_POST['payload']);
            $data=unserialize($data);
            if($data['test']=="Test") {
                echo "\nOK";
                exit();
            }else{
                echo "\nFAIL";
                exit();
            }
        }else{
            //Comment out and paste datastring to data for debug
            //$data='';
            //$data=unserialize($data);
            //$ssync=new SystemsSync(81);
            //$ret=$ssync->receivePatient($data);

            echo "FAIL";
            exit();
        }
    }

    public function mergepatientsAction(){

        if(!$_POST){
            return;
        }



        $patid_from=$_POST['patid_from'];
        $patid_to=$_POST['patid_to'];

        $pid_from=Pms_Uuid::decrypt($patid_from) ;
        $ipid_from=Pms_CommonData::getIpid($pid_from);
        $pid_to=Pms_Uuid::decrypt($patid_to) ;
        $ipid_to=Pms_CommonData::getIpid($pid_to);

        $epid_from=Pms_CommonData::getEpid($ipid_from);
        $epid_to=Pms_CommonData::getEpid($ipid_to);

        $patnames=PatientMaster::getPatientNames(array($ipid_from,$ipid_to));

        $patients=array();
        $patients['from']=array('ipid'=>$ipid_from,'encid'=>$patid_from,'name'=>$patnames[$ipid_from],'epid'=>$epid_from);
        $patients['to']=array('ipid'=>$ipid_to,'encid'=>$patid_to,'name'=>$patnames[$ipid_to],'epid'=>$epid_to);

        if("1"===$_POST['step']){
            $course_entries_from = Doctrine::getTable('PatientCourse')->findByIpid($ipid_from);
            $course_entries_to = Doctrine::getTable('PatientCourse')->findByIpid($ipid_to);

            $patients['from']['course']=count($course_entries_from);
            $patients['to']['course']=count($course_entries_to);

            $this->view->patients=$patients;
        }

        if("2"===$_POST['step']){

            if("1"===$_POST['add_course_data']){

                $tables=array(  'PatientCourse',
                                'PatientLocation',
                                'ContactForms',
                                'PatientFileUpload',
                                'FormBlockIpos',
                                'FormBlockKeyValue',
                                'FormBlockAdditionalUsers',
                                'FormBlockLmuDocTalkcontents',
                                'FormBlockLmuVisit',
                                'FormBlockProcedures',
                                'FormBlockSingleValue',
                                'FormBlockSoap',
                                'FormBlockSoapPflege',
                                'FormBlockSoapSoza',
                                'FormBlockLmuEmpfehlung',
                                'FormBlockLmuNursingTalkcontents',
                                'FormBlockTalkback',
                                'FormBlockTalkwith',
                                'FormBlockTimedocumentation',
                                'FormBlockTodos',
                                'FormBlockVitalzeichen',
                                'FormBlockZugaenge',
                                'PatientReadmission',
                                'PatientUsers'

                );

                foreach ($tables as $tabname){
                    $entries=Doctrine::getTable($tabname)->findByIpid($ipid_from);
                    if(count($entries) >0){
                        foreach ($entries as $entry){
                            $entry->ipid=$ipid_to;
                            $entry->save();
                        }
                    }
                }


                $entry=Doctrine::getTable('PatientMemo')->findOneByIpid($ipid_from);
                if($entry){
                    $memo=$entry->memo;
                    $entry_to=Doctrine::getTable('PatientMemo')->findOneByIpid($ipid_to);
                    if($entry_to) {
                        $entry_to->memo = $entry_to->memo . "\n" . $memo;
                        $entry_to->save();
                    }else{
                        $entry->ipid=$ipid_to;
                        $entry->save();
                    }
                }


            }
            $from_epid=Doctrine::getTable('EpidIpidMapping')->findOneByIpid($ipid_from);
            $to_epid=Doctrine::getTable('EpidIpidMapping')->findOneByIpid($ipid_to);

            if("1"===$_POST['change_epid']){

                $save_epid      =$from_epid->epid;
                $save_chars     =$from_epid->epid_chars;
                $save_num       =$from_epid->epid_num;

                $from_epid->epid        =$to_epid->epid;
                $from_epid->epid_chars  =$to_epid->epid_chars;
                $from_epid->epid_num    =$to_epid->epid_num;

                $to_epid->epid          =$save_epid;
                $to_epid->epid_chars    =$save_chars;
                $to_epid->epid_num      =$save_num;

                $from_epid->save();
                $to_epid->save();

            } else{
                $qpas=Doctrine::getTable('PatientQpaMapping')->findByEpid($from_epid->epid);
                if(count($qpas)>0){
                    foreach ($qpas as $qpa){
                        $qpa->epid=$to_epid->epid;
                        $qpa->save();
                    }
                }
            }

            $this->view->success=1;
            $this->view->patients2=$patients;
        }


    }

    public function patientinfoAction(){
        $this->_helper->layout->setLayout('layout_ajax');

        $sysync = new SystemsSync($this->clientid);
        $this->view->connection_names=$sysync->get_connection_names();
        $patid = $_REQUEST['patid'];
        $this->view->pid=$patid;
        $pid=Pms_Uuid::decrypt($patid) ;
        $ipid=Pms_CommonData::getIpid($pid);

        $this->view->patconnections = Doctrine::getTable('SystemsSyncPatients')->findByIpid_here($ipid);
    }



    public function indexAction(){

        $this->view->patients=array();
        $this->view->usertype=$this->usertype;
        $this->view->show_connectionselection=false;

        if(($_POST['change_connection_setup'] || $_POST['change_connection_setup_new']) && $this->usertype==="SA"){
            $confarr=array(
                'url'=>$_POST['url'],
                'user'=>$_POST['user'],
                'pass'=>$_POST['pass'],
                'local'=>$this->clientid,
                'id'=>$_POST['id'],
                'name'=>$_POST['name'],
                'localuserid'=>$_POST['localuserid']);

            if(isset($_POST['conn_id']) && !isset($_POST['change_connection_setup_new'])){
                $connection=$_POST['conn_id'];
            }else{
                if($_POST['id'] && strlen($_POST['id'])>2){
                    $connection=$_POST['id'];
                }
            }
            if(isset($connection)){
                $this->view->show_connectionselection=true;
                SystemsSyncConnections::setConnectionConfig($connection, $this->clientid, $confarr);
            }
        }

        $sysync = new SystemsSync($this->clientid);
        $connection_names=$sysync->get_connection_names();
        $this->view->connection_names=$connection_names;


        if((!$_REQUEST['conn_id']&& count($connection_names)>1) || count($connection_names)==0||$this->view->show_connectionselection) {
            $this->view->show_connectionselection=true;
        }else {
            if(count($connection_names)==1){
                $this->view->conn_id=$connection_names[0]->id;
                $this->view->conn_name=$connection_names[0]->name;
            }else {
                $this->view->conn_id = $_REQUEST['conn_id'];
            }
            foreach ($connection_names as $conn){
                if($conn->id==$_REQUEST['conn_id']){
                    $this->view->conn_id=$conn->id;
                    $this->view->conn_name=$conn->name;
                }
            }

            $this->view->connectionconfig=SystemsSyncConnections::getConnectionConfig( $this->view->conn_id,$this->clientid);


            if ($_POST['create_link']) {
                $patid = $_REQUEST['patid'];
                $conn_id=$_REQUEST['conn_id'];
                $pid=Pms_Uuid::decrypt($patid) ;
                $ipid=Pms_CommonData::getIpid($pid);
                SystemsSyncPatients::addConnection($conn_id, $ipid, $this->clientid);
            }

            $transmit=false;

            //this is the request from patient_stammdaten_box
            if ($_POST['transmit_patient']) {
                $pid=Pms_Uuid::decrypt($_REQUEST['pid']) ;
                $ipid=Pms_CommonData::getIpid($pid);

                $conn_id=$_REQUEST['conn_id'];

                if($ipid){
                    $patconnection = Doctrine::getTable('SystemsSyncPatients')->findOneByIpid_hereAndConnection($ipid,$conn_id);

                    if(!$patconnection) {
                        SystemsSyncPatients::addConnection($conn_id, $ipid, $this->clientid);
                    }

                    $transmit=true;
                }
            }

            //this is the request from this page
            if ($_POST['transmit']) {
                $patid = $_REQUEST['patid'];
                $patid = intval($patid);
                if ($patid) {
                    $patconnection = Doctrine::getTable('SystemsSyncPatients')->findOneById($patid);
                    $ipid=$patconnection->ipid_here;
                    $transmit = true;
                }
            }
            if($transmit){
                $conn_id=$_REQUEST['conn_id'];
                $sync=new SystemsSync($this->clientid);
                $sync->set_connection_name($conn_id, 'by_id');
                $return=$sync->sendPatient($ipid);
                if(trim($return)=="OK"){
                    $return="Die Übertragung war erfolgreich.";
                }else{
                    $return="Das hat nicht funktioniert: ".$return;
                }
                die($return);
            }

            //this is for testing the connection
            if ($_POST['droptest']) {
                $conn_id=$_REQUEST['conn_id'];
                $sync=new SystemsSync($this->clientid);
                $sync->set_connection_name($conn_id, 'by_id');
                $data = array('test' => 'Test');
                $urlpath = "/Systemssync/droptest";
                $return=$sync->sendData($data, $urlpath);
                if(trim($return)=="OK"){
                    $return="Der Test war erfolgreich.";
                }else{
                    $return="Das hat nicht funktioniert: ".$return;
                }
                die($return);
            }

            if ($_POST['remove']) {
                $patid = $_REQUEST['patid'];
                $patid=intval($patid);
                if($patid){
                    $pat=Doctrine::getTable('SystemsSyncPatients')->findOneById($patid);
                    if($pat){
                        $ipid_here=$pat->ipid_here;

                        if(strlen($ipid_here)>0) {
                            $patstabs = Doctrine::getTable('SystemsSyncTables')->findByIpid_here($ipid_here);
                            foreach ($patstabs as $pattab){
                                $pattab->delete();
                            }
                        }

                        $pat->delete();
                    }
                }
            }
            if ($_POST['config_sync_shortcuts']) {
                $cs_arr=array('send'=>$_POST['cs_send'],'receive'=>$_POST['cs_receive'], 'rename'=>$_POST['cs_rename']);
                SystemsSyncConnections::setConnectionShortcuts($this->view->conn_id,$this->clientid, $cs_arr);
            }
            $allowed_shortcuts=SystemsSyncConnections::getConnectionShortcuts($this->view->conn_id,$this->clientid);
            if($allowed_shortcuts) {
                $this->view->allowed_cs_receive = $allowed_shortcuts->receive;
                $this->view->allowed_cs_send = $allowed_shortcuts->send;
                $this->view->cs_rename = $allowed_shortcuts->rename;
            }

            $patsync = SystemsSyncPatients::getPatients($this->clientid,$this->view->conn_id, 1,1);
            $this->view->patients = $patsync['patients'];


            $sql = Doctrine_Query::create()
                ->select('c.course_fullname, c.shortcut')
                ->from('Courseshortcuts c')
                ->where('c.clientid=?',$this->clientid)
                ->andWhere("c.isdelete=0")
                ->orderBy('c.shortcut ASC');
            $this->view->course_shortcuts = $sql->fetchArray();



        }

    }

    public function stagingpatientsAction(){
        $this->_helper->layout->setLayout('layout_ajax');

        $sss=new SystemsSyncStaging();
        $this->view->pats=$sss->getPatients($this->clientid);

    }

    public function stagingpatientinfoAction(){
        $this->_helper->layout->setLayout('layout_ajax');

        if(isset($_GET['id'])) {
            $ipid_there = $_GET['id'];

            $sss=new SystemsSyncStaging();
            $original_data=$sss->getPatients($this->clientid, $ipid_there);



            if (!$original_data) {
                exit('No Entry found.');
            }

            $original_data = $original_data[$ipid_there];

            $first_name=$original_data['first_name'];
            $last_name=$original_data['last_name'];
            $birthd=$original_data['birthd'];

            if(isset($_GET['epid'])){
                $e_ipid=Pms_CommonData::get_ipid_from_epid($_GET['epid'], $this->clientid);
                $pm = new PatientMaster();
                $found_data = $pm->get_Masterdata_quick($e_ipid);
                if($found_data){
                    $first_name=$found_data['first_name'];
                    $last_name=$found_data['last_name'];
                    $birthd=$found_data['dob'];
                }
            }


            $css = new CoreSystemsSync();
            $found = $css->findPatientOnLocalside($first_name, $last_name, $birthd);

            $match_ipid=null;
            if ($found[0]) {
                $match_ipid = $found[1];
            }

            $found_data=array();
            if($match_ipid!==null) {
                $pm = new PatientMaster();
                $found_data = $pm->get_Masterdata_quick($match_ipid);
                $eim=new EpidIpidMapping();
                $epid=$eim->getIpidsEpids(array($match_ipid));
                $found_data['epid']=$epid[$match_ipid];
                $found_data['ipid']=$match_ipid;
                $found_data['encid']=Pms_Uuid::encrypt(Pms_CommonData::getIdfromIpid($match_ipid));
            }

            $this->view->found_data=$found_data;
            $this->view->original_data=$original_data;
        }


    }

    public function stagingpatienttakeoverAction(){
        $ipid_there=$_POST['ipidthere'];
        $encid=$_POST['encid'];
        if($encid==="0"){
            $ipid = Pms_Uuid::GenerateIpid();
        }else {
            $pid = Pms_Uuid::decrypt($encid);
            $ipid = Pms_CommonData::getIpid($pid);
        }

        if(strlen($ipid_there)<1){
            die('ERROR:invalid ID');
        }

        $sql = Doctrine_Query::create()
            ->select('*')
            ->from('SystemsSyncStaging')
            ->Where('ipid_there=?', $ipid_there)
            ->andWhere('isdelete=0')
            ->orderBy('id ASC');
        $original_data = $sql->fetchArray();

        foreach ($original_data as $od){
            $filepath=$od['filepath'];

            $gz = gzopen($filepath,'r');
            $tables_data = gzfile($filepath);
            gzclose($gz);
            $tables_data = implode($tables_data);
            $tables_data = unserialize($tables_data);

            if(strlen($ipid)>1) {
                //force mapping to selected patient
                $tables_data['_meta']['ipid_there'] = $ipid;
                $pat=null;
                $pat = Doctrine::getTable('SystemsSyncPatients')->findOneByIpid_hereAndConnectionAndClientid($ipid,$tables_data['_meta']['connection'],$this->clientid);
                if(!$pat) {
                    $pat = SystemsSyncPatients::addConnection($tables_data['_meta']['connection'], $ipid, $this->clientid);
                    $pat->ipid_there=$ipid_there;
                    $pat->save();
                }
            }

            $ssync=new SystemsSync($this->clientid);
            $ssync->receivePatient($tables_data);

            if(file_exists($filepath)){
                unlink($filepath);
            }

        }

        SystemsSyncStaging::mark_done($ipid_there);

        if($pat){
            $ipid=$pat->ipid_here;
            $pid=Pms_CommonData::getIdfromIpid($ipid);
            $encid = Pms_Uuid::encrypt($pid);
            $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $encid);
        }


        echo"ERROR:Unknown target. No redirect possible.";
        exit();




    }
    

    public function casenumberAction(){
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/sync.log');
        $log = new Zend_Log($writer);
        $log->info(serialize($_REQUEST));
        //var_dump('fff'); exit;
        // 	$payload = array('epid' =>"Goe160204336", 'caseno'=>"98888889888", 'date'=>"20170217094416");
    
        if($_POST['payload']){
            $data=urldecode($_POST['payload']);
            $data=unserialize($data);
        }
    
        //         $this->getHelper('Log')->log(__METHOD__ . PHP_EOL . print_r($data, true) . PHP_EOL);
    
    
        if(is_array($data) && !empty($data)){
    
            $st = new SystemsSyncHospital();
            $st->clientid = $this->clientid;
            $st->epid = $data['epid'];
            $st->case_number = $data['caseno'];
            $st->sapv_start_date = date("Y-m-d H:i:s",strtotime($data['date']));
            $st->server_details = serialize($_SERVER);
            $st->request_details= serialize($_REQUEST);
            $st->save();
             
            // check patient and sapv
            if(!empty($data['epid'])){
    
                $patient_ipid = EpidIpidMapping::get_ipids_of_epids(array($data['epid']));
                if(is_array($patient_ipid)){
                    $ipid_here = $patient_ipid[$data['epid']];
                } else{
                    $ipid_here = $patient_ipid;
                }
    
                if(!empty($ipid_here)) {
                     
                    // get latest data from SystemsSyncHospital
                    $sql = Doctrine_Query::create ()
                    ->select('case_number,sapv_start_date')
                    ->from ('SystemsSyncHospital')
                    ->where ('epid =?', $data['epid'])
                    ->andWhere("clientid =?", $this->clientid)
                    ->orderBy('create_date DESC')
                    ->limit(1);
                    $hospital_data = $sql->fetchOne(array(),Doctrine_Core::HYDRATE_ARRAY);
                    /*
                     * original version
                     if(!empty($hospital_data)){
    
                     // update SAPV with latest
                     $sql_sapv = Doctrine_Query::create ()
                     ->select('id')
                     ->from ('SapvVerordnung')
                     ->where ('ipid = ?', $ipid_here)
                     ->andWhere("verordnungam = ?", $hospital_data['sapv_start_date'])
                     ->andWhere("isdelete = ?", '0')
                     ->limit(1);
                     $sapv_array = $sql_sapv->fetchOne(array(),Doctrine_Core::HYDRATE_ARRAY);
    
    
                     if(!empty($sapv_array)){
                     $sapv = Doctrine::getTable('SapvVerordnung')->findOneById($sapv_array['id']);
                     if($sapv){
                     $sapv->case_number = $hospital_data['case_number'];
                     $sapv->save ();
                     }
                     }
                     }
                    */
                    /*
                     * @cla version
                    */
                    if(!empty($hospital_data)){
                         
                        // update SAPV with latest
                        $sql_sapv = Doctrine_Query::create()
                        ->update('SapvVerordnung')
                        ->set ('case_number' , '?', $hospital_data['case_number'])
                        ->where ('ipid = ?', $ipid_here)
                        ->andWhere("isdelete = ?", '0')
                        ->andWhere("DATE(verordnungam) = ?", date("Y-m-d", strtotime($hospital_data['sapv_start_date'])))
                        ->limit(1)
                        ->execute();
    
                    }
                }
            }
             
            die("OK");
            //exit();
        }else{
            //Comment out and paste datastring to data for debug
            //$data='';
            //$data=urldecode($data);
            //$data=unserialize($data);
            //$ssync=new SystemsSync(81);
            //$ret=$ssync->receivePatient($data);
            die("NoData");
            //exit();
        }
    }

}
