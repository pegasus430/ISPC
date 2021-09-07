<?php
/**
 * 
 * @author  Feb 8, 2020  ancuta
 * ISPC-2507
 * // Maria:: Migration ISPC to CISPC 08.08.2020
 */
class PharmarequestsController extends Pms_Controller_Action {
    public function init()
    {
        
        /* Initialize action controller here */
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $this->clientid = $logininfo->clientid;
        $this->userid = $logininfo->userid;
        $this->usertype = $logininfo->usertype;
        $this->logininfo = $logininfo;
        $this->groupid = $logininfo->groupid;  
        

        $this->setActionsWithJsFile([
            "overview", 
        ]);
        
        //phtml is the default for zf1 ... but on bootstrap you manualy set html :(
        $this->getHelper('viewRenderer')->setViewSuffix('phtml');
    }
    
    
    public function listAction()
    {
        set_time_limit(0);
        $clientid = $this->clientid;
        
        // PAGE IS NO LONGER NEEDED
        if($this->usertype != 'SA')
        {
            $this->_redirect(APP_BASE . 'error/previlege');
            exit;
        }
        // ---- 
    }
    
    
    public function getlistAction()
    {
        $clientid = $this->clientid;
        $userid = $this->userid;
        
        $this->_helper->viewRenderer->setNoRender();
        
        if(!$_REQUEST['length'])
        {
            $_REQUEST['length'] = "100";
        }
        
        $limit = $_REQUEST['length'];
        $offset = $_REQUEST['start'];
        $search_value = $_REQUEST['search']['value'];
        
        $usr = new User();
        $all_users = $usr->getUserByClientid($clientid, '1', true);
        
        if(!empty($_REQUEST['order'][0]['column']))
        {
            $order_column = $_REQUEST['order'][0]['column'];
        }
        else
        {
            $order_column = "1";
        }
        
        $order_dir = $_REQUEST['order'][0]['dir'];
        
        $columns_array = array(
            "4" => "create_date"
        );
        
        // get assigned patients of current client
        
        $order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
        
        // ########################################
        // #####  Query for count ###############
        $fdoc1 = Doctrine_Query::create();
        $fdoc1->select('count(*)');
        $fdoc1->from('PharmaRequestsReceived');
        $fdoc1->where("isdelete = 0  ");
        $fdoc1->andWhere("clientid = ?", $clientid);
        $fdoc1->andWhere("doctor_id = ?", $userid);
        $fdoc1->orderBy($order_by_str);
//         $fdoc1->orderBy('request_id');
        $fdocexec = $fdoc1->execute();
        $fdocarray = $fdocexec->toArray();
        
        $full_count  = $fdocarray[0]['count'];
        
        // ########################################
        // #####  Query for details ###############
        $sql = '*,';
        $fdoc1->select($sql);
        $fdoc1->where("isdelete = 0  ");
        $fdoc1->andWhere("clientid = ?", $clientid);
        $fdoc1->andWhere("doctor_id = ?", $userid);
        $fdoc1->limit($limit);
        $fdoc1->offset($offset);
        $fdoclimitexec = $fdoc1->execute();
        $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
        
        $request_ipids  = array();
        foreach($fdoclimit as $fk=>$fdata){
            $request_ipids[] = $fdata['ipid'];
        }
        
        /* ================ PATIENT DETAILS ======================= */
        $patients_details = array();
        if(!empty($request_ipids)){
            $patients_details = PatientMaster::get_multiple_patients_details($request_ipids);
        }
        
        $request_entries = array();
        foreach($fdoclimit as $k=>$req_data){
            $request_entries[$req_data['request_id']]['request_id'] = $req_data['request_id'];
            $request_entries[$req_data['request_id']]['request_user_id'] = $req_data['request_user'];
            $request_entries[$req_data['request_id']]['request_user_name'] = $all_users[$req_data['request_user']];
            $request_entries[$req_data['request_id']]['patient'] = $patients_details[$req_data['ipid']]['first_name'].' '.$patients_details[$req_data['ipid']]['last_name'];
            $request_entries[$req_data['request_id']]['doctor_ids'][] = $req_data['doctor_id'];
            $request_entries[$req_data['request_id']]['doctor_names'][] = $all_users[$req_data['doctor_id']];
            $request_entries[$req_data['request_id']]['status'] = ($req_data['processed'] == 'yes')? 'Ja':'Nein';
            $request_entries[$req_data['request_id']]['create_date'] = date('d.m.Y H:i',strtotime($req_data['create_date']));
        }
       
        
        $row_id = 0;
        $link = "";
        $resulted_data = array();
        foreach($request_entries as $request_id => $request_data)
        {
            $link = '%s ';
            
            $resulted_data[$row_id]['patient'] = $request_data['patient'];
            $resulted_data[$row_id]['user'] = $request_data['request_user_name'];
            $resulted_data[$row_id]['status'] = $request_data['status'];
            $resulted_data[$row_id]['doctors'] = $request_data['doctor_names'];
            $resulted_data[$row_id]['create_date'] = $request_data['create_date'];
            $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'pharmarequests/requestpage?request_id='.$request_data['request_id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /></a> ';
            $row_id++;
        }
        
//         dd($resulted_data,$request_entries,$fdoclimit);
        
        
        $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
        $response['recordsTotal'] = $full_count;
        $response['recordsFiltered'] = $full_count; // ??
        $response['data'] = $resulted_data;
        
        header("Content-type: application/json; charset=UTF-8");
        
        echo json_encode($response);
        exit;
    }
    
    
    public function requestpageAction(){
        $clientid = $this->clientid;
        $userid = $this->userid;
        
        // PAGE IS NO LONGER NEEDED 
        if($this->usertype != 'SA')
        {
            $this->_redirect(APP_BASE . 'error/previlege');
            exit;
        }
        // ---- 
        
        if(empty($_REQUEST['request_id'])){
            $this->_redirect(APP_BASE . 'pharmarequest/list?flg=err');
            exit;
        }
        // get request details
        $request_details = PharmaPatientDrugplanRequestsTable::find_request_by_ids(array($_REQUEST['request_id']),'sent');
        $request_data = $request_details[0];
        if(empty($request_data['ipid'])){
            return;
        }
        $ipid = $request_data['ipid'];
        $this->view->request_data = $request_data;
        
        // Check if we have data in proccesed for  current request
//         PharmaRequestsProcessed
        
        $request_processed_array = Doctrine_Query::create()
        ->select('*')
        ->from('PharmaRequestsProcessed')
        ->where(" ipid = ? ", $request_data['ipid']  )
        ->andWhere("request_id = ? ", $request_data['id'] )
        ->andWhere("request_user = ? ", $request_data['user'] )
        ->fetcharray();
        $processed_data = array();
        if(!empty($request_processed_array)){
            foreach($request_processed_array as $k=>$pr){
                $processed_data[$pr['request_id']][$pr['drugplan_id']]['status'] = $pr['status'];
            }
        }
        $this->view->processed_data = $processed_data[$request_data['id'] ];
        
        
        if($this->getRequest()->isPost())
        {
            $proccessd_Data = array();
            if(!empty($_REQUEST['request_id'])){
                foreach($_POST['request_data'] as $drugplan_id => $details){
                    if($details['request_id'] == $_REQUEST['request_id'] ){
                        
                        $proccessd_Data[] = array(
                            'clientid' => $clientid,
                            'doctor_id' => $userid,// current user that  accepts / denies the changes 
                            'request_id' => $details['request_id'], 
                            'request_user' => $request_data['user'],//user that created the request
                            'ipid' => $ipid, // pateint for wich the request was created
                            'drugplan_id' => $drugplan_id, // medication line for wich the request was created 
                            'original_line_id' => $details['original_line_id'], // id of original line of drugplan from PharmaPatientDrugplan
                            'requested_line_id' => $details['requested_line_id'],// id of requested line of drugplan from PharmaPatientDrugplan
                            'status' => (!empty($details['status'])) ? $details['status'] : null // status of acceptance per drugplan 
                        );
                    }
                }
            }
            
            if(!empty($proccessd_Data)){
                // if existing data in proccesc - marck as deleted  and re-add all - this is for log purposes - to kno
                // ???? remove all ??? 
                
                // save  proccesed data
                $collection = new Doctrine_Collection('PharmaRequestsProcessed');
                $collection->fromArray($proccessd_Data);
                $collection->save();
                
                
                // mark request as procesed
                // find all - so all doctors can see as proccessd 
                $prr = Doctrine_Query::create()
                ->update('PharmaRequestsReceived')
                ->set('processed','?', 'yes')
                ->where("ipid=?", $ipid)
                ->andWhere("request_id = ?", $_REQUEST['request_id'])
                ->andWhere("request_user = ?", $request_data['user']);
                
                $prr->execute();
            }
            
            $this->_redirect(APP_BASE . "pharmarequests/list");
            exit;
        }
        
        /* ================ PATIENT DETAILS ======================= */
        $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
        
        if(!Pms_CommonData::getPatientClient($patient_details[$ipid]['id'], $clientid))
        {
            //deny acces to this patient as is does not belong to this client
            $this->_redirect(APP_BASE . "overview/overview");
            exit;
        }
        
        $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
        $patient_details[$ipid]['patient_name'] = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
        
        $this->view->patient_details = $patient_details[$ipid];
        
        /* ================ CLIENT USER DETAILS ======================= */
        $usr = new User();
        $all_users = $usr->getUserByClientid($clientid, '1', true);
        
        $this->view->users_details = $all_users;
        
        /* ================ MEDICATION :: CLIENT EXTRA ======================= */
        $client_medication_extra  = array();
        //UNIT
        $medication_unit = MedicationUnit::client_medication_unit($clientid);
        
        foreach($medication_unit as $k=>$unit){
            $client_medication_extra['unit'][$unit['id']] = $unit['unit'];
        }
        
        //TYPE
        $medication_types = MedicationType::client_medication_types($clientid,true);
        foreach($medication_types as $k=>$type){
            if($type['extra'] == 0 ){
                $client_medication_extra['type'][$type['id']] = $type['type'];
            }
            $client_medication_extra['type_custom'][$type['id']] = $type['type'];
            
        }
        
        //DOSAGE FORM
        $medication_dosage_forms = MedicationDosageform::client_medication_dosage_form($clientid,true); // retrive all- incliding extra
        foreach($medication_dosage_forms as $k=>$df){
            if($df['extra'] == 0 ){
                $client_medication_extra['dosage_form'][$df['id']] = $df['dosage_form'];
            }
            $client_medication_extra['dosage_form_custom'][$df['id']] = $df['dosage_form'];
            
        }
        
        //INDICATIONS
        $medication_indications = MedicationIndications::client_medication_indications($clientid);
        
        foreach($medication_indications as $k=>$indication){
            $client_medication_extra['indication'][$indication['id']]['name'] = $indication['indication'];
            $client_medication_extra['indication'][$indication['id']]['color'] = $indication['indication_color'];
        }
        
        //ESKALATION  ( ISPC-2247)
        $medication_escalation = PatientDrugPlanExtra::getMedicationEscalation();
        foreach($medication_escalation as $esc_id=>$escalation_label){
            $client_medication_extra['escalation'][$esc_id] = $escalation_label;
        }

        
        $js_med_indication = array_combine(
            array_map(function($key){ return ' '.$key; }, array_keys($client_medication_extra['indication'])),
            $client_medication_extra['indication']
            );
        
        //ISPC-2176 p6
        $packaging_array = PatientDrugPlanExtra::intubated_packaging();
        
        $client_medication_extra['packaging']= $packaging_array;
        
        
        $this->view->client_medication_extra = $client_medication_extra;
        
        
        
        
        
        
        
        
        $medication_block = array();
        foreach($request_details as $request_data){
            if(!empty($request_data['PharmaPatientDrugplan'])){
                foreach($request_data['PharmaPatientDrugplan'] as $med_k  => $medication_data){
                    if($medication_data['isbedarfs'] == "1")
                    {
                        $med_type = 'isbedarfs';
                    }
                    elseif($medication_data['isivmed'] == "1")
                    {
                        $med_type = 'isivmed';
                    }
                    elseif($medication_data['isschmerzpumpe'] == "1")
                    {
                        $med_type = 'isschmerzpumpe';
                    }
                    elseif($medication_data['treatment_care'] == "1")
                    {
                        $med_type = 'treatment_care';
                    }
                    elseif($medication_data['isnutrition'] == "1")
                    {
                        $med_type = 'isnutrition';
                    }
                    elseif($medication_data['scheduled'] == "1")
                    {
                        $med_type = 'scheduled';
                    }
                    elseif($medication_data['iscrisis'] == "1")
                    {
                        $med_type = 'iscrisis';
                    }
                    elseif($medication_data['isintubated'] == "1") // ISPC-2176 16.04.2018 @Ancuta
                    {
                        $med_type = 'isintubated';
                    }
                    else
                    {
                        $med_type = 'actual';
                    }
                    
                    $medication_block[$med_type][$medication_data['drugplan_id']][$medication_data['pharma_med_type']] = $medication_data;
                    
                }
            }
        }
//         dd($medication_block);
//         dd('echo',$medication_block);

        $this->view->request_details  = $request_details;
        $this->view->medication_blocks = $medication_block;
        // get patient data
        
        // get medication details
        
        
        
        
    }
    
}
	