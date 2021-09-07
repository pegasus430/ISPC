<?php
/**
 * 
 * @author claudiu 
 * Jun 11, 2018
 * 
 * 
 * ISPC-2198 - @cla deprecated all the other/older functions in this class, and writen new ones 
 * 
 * TODO : completed/submited logic is NOT per formular... 
 * this must be changed if you need to list a patient in multiple tabs, with the exact fall he has not completed or submited to server
 *
 */
class DgpController extends Pms_Controller_Action
{

	public $act; // What is the purpose of this ??

	public function init()
	{
		/* Initialize action controller here */
	    array_push($this->actions_with_js_file, "dgpfullpatientlist");
	}
	
	/**
	 * @cla on 10.06.2018 : this replaces dgpfullpatientlistAction_OLD2018
	 * ISPC-2198 
	 */
	public function dgpfullpatientlistAction()
	{
	     
	    $clientid = $this->clientid;
	    $userid = $this->userid;
	    //set_time_limit(0); //why?
	
	    $this->_populateCurrentMessages(); // this will not show the success message caue i used ajax on success
	    
	    if ( $this->getRequest()->isPost() ) {
	         
	        switch ($step = $this->getRequest()->getPost('step')) {
	
	            case "fetch_patients_list":
	                $result = $this->_fetch_patients_list();
	                //this result is ech
	                break;
	                 
	            case "dgp_send" :
	                 
	                $enc_ids = $this->getRequest()->getPost('idpd');
	                 
	                $ids = array();
	                
	                foreach ($enc_ids as $enc_id) {
	                    $ids[] = Pms_Uuid::decrypt($enc_id);
	                }
	                
	                $result = $this->_send_dgpKern_to_server($ids);
	                 
	                break;
	        }
	         
	        $this->_helper->json($result);
	
	        exit; //for readability
	    }
	}
	
	
	/**
	 * ISPC-2198
	 * 
	 * @param multitype:numeric $patient_ids
	 * @return multitype:boolean string |Ambigous <multitype:boolean string , multitype:boolean NULL >
	 */
	private function _send_dgpKern_to_server($patient_ids = array())
	{
	    $result = array(
	        'success' => false,
	        'message' => $this->translate("[Failed to export, please contact admin]") . " (error : 1)"
	    );
	     
	    if (empty($patient_ids) || ! is_array($patient_ids)) {
	        
// 	        $this->_helper->flashMessenger->addMessage( $result['message'],  'ErrorMessages');
	        
	        return $result;//fail-safe
	    }	    
	    
	    $patients = Doctrine_Query::create()
	    ->select("p.id, p.ipid")
	    ->from('PatientMaster p')
	    ->leftJoin("p.EpidIpidMapping e")
	    ->whereIn('p.id', $patient_ids)
	    ->andWhere("e.clientid = ?" , $this->clientid) //enforced
	    ->fetchArray()
	    ;
	     
	    if (empty($patients)) {
	        return $result;//fail-safe
	    }
	     
	    $ipids = array_column($patients, 'ipid');
	     
	    $dph_obj = new DgpPatientsHistory();
	     
	    $code = $dph_obj->dgp_auto_export_v3($this->clientid, $ipids);
	     
	    if (empty($code)) {
	         
	        //something went wrong ... before post to server.. please check the dgp-log to see errors
	        $result = array(
	            'success' => false,
	            'message' => $this->translate("[Failed to export, please contact admin]") . " (error : 2)"
	        );
	        
// 	        $this->_helper->flashMessenger->addMessage( $result['message'],  'ErrorMessages');
	         
	    } else {
	         
	        switch ($code) {
	             
	            case "1000":
	                 
	                $result = array(
    	                'success' => true,
    	                'message' => $this->translate("[Export to www.hospiz-palliativ-register.de was successful]")
	                );
	                 
	                $this->_helper->flashMessenger->addMessage( $result['message'],  'SuccessMessages');
	                
	                $this->_log_info("RUN DGP:: - MANUAL export function  was executed for client {$this->clientid} for this ipids : " . print_r($ipids, true));
	                 
	                break;
	                 
	            default:
	                
	                if (isset($dph_obj->NatHospiz_response_text[$code])) {
	                    $message = "xml_upload_status_{$code}";//$dph_obj->NatHospiz_response_text[$code];
	                    
	                    $message_tr = $this->translate($message);
	                    
	                    $message = $message != $message_tr ? $message_tr : $this->translate($dph_obj->NatHospiz_response_text[$code]);
	                    
	                } else {
	                    $message = $this->translate("[Export to www.hospiz-palliativ-register.de Failed with unknown error]");
	                }
	                 
	                $result = array(
	                    'success' => false,
	                    'message' => $message
	                );
	                
// 	                $this->_helper->flashMessenger->addMessage( $result['message'],  'ErrorMessages');
	                 
	                $this->_log_error("RUN DGP:: - auto export FAILED for client {$clientid} ({$end_dgp} seconds ) , code {$code} = " . $dph_obj->NatHospiz_response_text[$code]);
	                 
	                break;
	                 
	        }
	         
	    }
	     
	     
	    return $result;
	     
	}
	
	
	/**
	 * !! this has multiple exit points fo fail-safe
	 * ISPC-2198
	 *
	 * result is format Datatables
	 *
	 * @return void|boolean|multitype:number multitype:
	 */
	private function _fetch_patients_list()
	{
	    $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
	    $viewRenderer->setNoRender(true); // disable view rendering
	     
	     
	    $dgp_status = $this->getRequest()->getPost('patient_dgp_status', 'submited'); // defaults to submited
	
	    $wanted_ipids = array();
	     
	    $patients_ipids = DgpKern::findIpidsOfClient($this->clientid);
	     
	    if (empty($patients_ipids)) {
	        $this->returnDatatablesEmptyAndExit();
	        return; // this has no patients for dgp
	    }
	     
	    $limit = intval($this->getRequest()->getPost('iDisplayLength', 10));
	    $offset = intval($this->getRequest()->getPost('iDisplayStart', 0));
	     
	    $orderColId = intval($this->getRequest()->getPost('iSortCol_0', 0));
	    $orderCol = $this->getRequest()->getPost("mDataProp_{$orderColId}", "last_name");
	    $orderDir = strtoupper($this->getRequest()->getPost("sSortDir_0", "ASC"));
	
	    $stringSearch = trim($this->getRequest()->getPost("sSearch"));
	     
	     
	     
	    if ( ! in_array($orderCol, array('epid', 'last_name', 'first_name'))) {
	        $orderCol = "last_name"; //fail-safe
	    }
	     
	    if ( ! in_array($orderDir, array('ASC', 'DESC'))) {
	        $orderDir = "ASC"; //fail-safe
	    }
	     
	    $sorting = array(
	        "limit" => $limit,
	        "offset" => $offset,
	        "orderCol" => $orderCol,
	        "orderDir" => $orderDir,
	        "stringSearch" => $stringSearch,
	    );
	     
	     
	    $client_details = Client::getClientDataByid($this->clientid);
	    $client_details = $client_details[0];
	    $clientid = $this->clientid; //TODO-3337
	    if ($client_details['dgp_transfer_date'] != "0000-00-00 00:00:00"
	        && $client_details['dgp_transfer_date'] != "1970-01-01 01:00:00")
	    {
	        //this is a client settings
	        //we must filter only the patients active in this period
	         
	        $sqls = 'e.epid, p.ipid, e.ipid,';
	         
	        $conditions = array(
	            'client' => $clientid,
	            'ipids' => $patients_ipids,
	            'periods' => array(
	                array(
	                    'start' => date('Y-m-d', strtotime($client_details['dgp_transfer_date'])),
	                    'end' => date('Y-m-d')
	                )
	            )
	        );
	         
	        //TODO replace this Pms_CommonData::patients_days with a straight one
	        $patient_days = Pms_CommonData::patients_days($conditions, $sqls);
	         
	        if ( ! empty($patient_days)) {
	            $patients_ipids = array_keys($patient_days); // new filtered ipids
	        } else {
	            $patients_ipids =  null;
	        }
	    }
	     
	    if (empty($patients_ipids)) {
	        // this has no patients for dgp
	        $this->returnDatatablesEmptyAndExit();
	         
	        exit; //for readability
	    }
	     
	     
	
	    //$patients_ipids - this are all that have a started form
	     
	    $completed_forms = DgpKern::findFormsCompletedOfIpids($patients_ipids);
	     
	    $ipids_completed = array();
	    if ( ! empty($completed_forms)) {
	        $ipids_completed = array_unique(array_column($completed_forms, 'ipid'));
	    }
	    $patients_submited_status =  DgpPatientsHistory::findSubmitedStatusOfIpids($ipids_completed, array($this->clientid));
	
	    //submited
	    $ipids_submited = array_filter ( $patients_submited_status, function($val) {return $val == 'submited' ;});
	    $ipids_submited = array_keys($ipids_submited);
	     
	    //ready_to_send
	    $ipids_notsubmited = array_filter ( $patients_submited_status, function($val) {return $val == 'not_submited' ;});
	    $ipids_notsubmited = array_keys($ipids_notsubmited);
	     
	    //no-completed
	    $ipids_notcompleted = array_diff($patients_ipids, $ipids_notsubmited, $ipids_submited);
	     
	     
	    $wanted_ipids =  null;
	    $status = '';
	     
	    switch ($dgp_status) {
	
	        case "submited" :
	            $status = 'filledsubmited';
	            $wanted_ipids = $ipids_submited;
	            break;
	
	        case "not-completed" :
	            $status = 'not_fillednot_submited';
	            $wanted_ipids = $ipids_notcompleted;
	            break;
	
	        case "ready_to_send" :
	            $status = 'fillednot_submited';
	            $wanted_ipids = $ipids_notsubmited;
	            break;
	    }
	     
	     
	    if (empty($wanted_ipids)) {
	         
	        $this->returnDatatablesEmptyAndExit();//fail-safe
	
	        exit; //for readability
	    }
	     
	     
	     
	    //apply the limit and order by
	    $patients_details = $this->_getPatientsDetails($wanted_ipids, $sorting);
	     
	    if (empty($patients_details)) {
	        $this->returnDatatablesEmptyAndExit();//fail-safe
	
	        exit; //for readability
	    }
	     
	    //TODO-3337 Ancuta 18-19.08.2020:: Check treatment days of patient
	    $patientmaster_obj = new PatientMaster();
	    
	    $sqls_ov = 'e.epid, p.ipid, e.ipid,';
	    
	    $conditions_ov = array(
	        'client' => $clientid,
	        'ipids' => $patients_ipids,
	        'periods' => array(
	            array(
	                'start' => date('2008-01-01'),
	                'end' => date('Y-m-d')
	            )
	        )
	    );
	    $patient_days_ov = Pms_CommonData::patients_days($conditions_ov, $sqls_ov);
	    //--
	    
	    
	    
	    $data = array();
	    foreach ($patients_details as $patient) {
	         
	        //here i used the falls from PatientActive.. that in theory should be the same as from PatientReadmission
	        //i did not used PatientReadmission because it would be more work :) ..
	        //the foreach from PatientReadmission::findFallsOfIpid can be done also in _getPatientsDetails, if you really want consistency
	         
	         
	        $admission_discharge = array();
	        array_walk($patient['PatientActive'], function($val) use (&$admission_discharge) {
	            $start = date("d.m.Y", strtotime($val['start']));
	            $end = ! empty($val['end']) && $val['end']!= '0000-00-00' ? date("d.m.Y", strtotime($val['end'])) : '';
	            array_push($admission_discharge,  $start . " - " . $end);
	        });
	
	        
	        
	        //TODO-3337 Ancuta 18-19..08.2020:: Check treatment days of patient
	        $admission_discharge_dates = array();
            foreach($patient['PatientActive'] as $k=>$val_date){
                $start_date = date("d.m.Y", strtotime($val_date['start']));
                $end_date_calc = ! empty($val_date['end']) && $val_date['end']!= '0000-00-00' ? date("d.m.Y", strtotime($val_date['end'])) : date('d.m.Y');
                $end_date = ! empty($val_date['end']) && $val_date['end']!= '0000-00-00' ? date("d.m.Y", strtotime($val_date['end'])) : '';
                $current_period_days = $patientmaster_obj->getDaysInBetween($start_date,  $end_date_calc , false, 'd.m.Y');
                $current_period_treatment_days = array_intersect($patient_days_ov[$val_date['ipid']]['treatment_days'], $current_period_days);

                $alarm = "";
                if(count($current_period_treatment_days) < 1 && !empty($end_date)){
                    $alarm = ' <span>!</span>';
                }
                $admission_discharge_dates[] = $start_date . " - " . $end_date.$alarm;
            }
            //-- 
             
	            $pat = array(
	                'idpd' => Pms_Uuid::encrypt($patient['id']),
	                'debug' => '',
	                'epid' => $patient['epid'],
	                'last_name' => $patient['last_name'],
	                'first_name' => $patient['first_name'],
	                 
	                'admission_date' => $patient['admission_date'], //this is the current admission date
	                'discharge_date' => isset($patient['PatientDischarge'][0]) ? $patient['PatientDischarge'][0]['discharge_date'] : "",//you have this only if it currenty is discharged
	                 
	                //TODO-3337 Ancuta 18-19..08.2020:: Check treatment days of patient:: use new array
	                //'admission_discharge' => implode("<br/>", $admission_discharge),
	                'admission_discharge' => implode("<br/>", $admission_discharge_dates),
	                //--  
	                
	                'register_status' => $status,
	            );
	             
	            array_push($data, $pat);
	    }
	     
	     
	    $response = array(
	        'draw' => (int)$this->getRequest()->getParam('draw'),
	        'recordsTotal' => count($wanted_ipids),
	        'recordsFiltered' => count($wanted_ipids),
	        'data' => $data,
	    );
	     
	    return $response;
	}
	
	
	/**
	 * ! fn works only for $this->clientid
	 * ISPC-2198
	 *
	 * @param unknown $ipids
	 * @param unknown $sorting
	 * @return void|Ambigous <multitype:, Doctrine_Collection>
	 */
	private function _getPatientsDetails($ipids = array(), $sorting = array())
	{
	
	    if (empty($ipids) || ! is_array($ipids)) {
	        return;
	    }
	     
	    $ipids = array_values($ipids);
	     
	    $qr = Doctrine_Query::create()
	    ->select("
	        p.ipid,
            e.epid as epid,
            p.birthd,
            p.admission_date,
            p.change_date,
            p.last_update,
            CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,
            CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,
            CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,
            CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,
            CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,
            CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,
            CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,
            CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,
            CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,
            CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,
            CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,
            CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex
	        ")
	
        ->from('PatientMaster p indexBy p.ipid')
        ->leftJoin("p.EpidIpidMapping e")

        ->addSelect("pd.discharge_method, pd.discharge_date, pd.isdelete")
        ->leftJoin("p.PatientDischarge pd ON (p.ipid=pd.ipid AND pd.isdelete = 0)")

        ->addSelect("pr.id, pr.date, pr.date_type")
        ->leftJoin("p.PatientReadmission pr ON (p.ipid=pr.ipid )")

        ->addSelect("pa.*")
        ->leftJoin("p.PatientActive pa ON (p.ipid=pa.ipid )")


        ->whereIn('p.ipid', $ipids)
        ->andWhere("p.isdelete = 0")
        ->andWhere("p.isstandbydelete=0")

        ->andWhere("e.clientid = ?" , $this->clientid) //enforced
        ;
	     
	    if ( ! empty($sorting['stringSearch'])) {
	         
	        Pms_CommonData::value_patternation($sorting['stringSearch']);
	         
	        $qr->andWhere( "CONVERT( CONCAT_WS(' ', AES_DECRYPT(p.first_name, '" . Zend_Registry::get('salt') . "'), AES_DECRYPT(p.last_name, '" . Zend_Registry::get('salt') . "'), e.epid  ) USING utf8 ) REGEXP ?" , $sorting['stringSearch']);
	         
	    }
	     
	     
	     
	    if ( ! empty($sorting['orderCol'])) {
	
	        if (in_array($sorting['orderCol'] , array('first_name', 'last_name'))) {
	            $sorting['orderCol'] = "AES_DECRYPT(p.{$sorting['orderCol']}, '" . Zend_Registry::get('salt') . "')";
	        } elseif ($sorting['orderCol'] == 'epid') {
	            $sorting['orderCol'] = "e.epid" ;
	        } else {
	            //fail-safe here.. what column did you sent ? is this a new one?
	            $sorting['orderCol'] = "e.epid" ;
	        }
	           
	        //re-sanitize $sorting['orderDir']
	        $sorting['orderDir'] =  strtoupper($sorting['orderDir']) == 'ASC' ? 'ASC' : 'DESC';
	
	        $qr->orderBy("{$sorting['orderCol']} {$sorting['orderDir']}");
	    }
	
	    if ($sorting['limit'] > 0) {
	
	        $qr->limit($sorting['limit']);
	
	        if ($sorting['offset'] > 0) {
	            $qr->offset($sorting['offset']);
	        }
	    }
	
	    return $qr ->fetchArray();
	}
	
	
	
	

	private function _populateCurrentMessages()
	{
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	    );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	    );
	
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	}
	
	
	
	
	
	/**
	 * @cla on 10.06.2018 : do not use
	 * @deprecated
	 */
	private function array_sort($array, $on = NULL, $order = SORT_ASC)
	{
	    $new_array = array();
	    $sortable_array = array();
	    if(count($array) > 0)
	    {
	        foreach($array as $k => $v)
	        {
	            if(is_array($v))
	            {
	                foreach($v as $k2 => $v2)
	                {
	                    if($k2 == $on)
	                    {
	                        if($on == 'birthd' || $on == 'admissiondate' || $on == 'admission_date'  || $on == 'discharge_date')
	                        {
	
	                            if($on == 'birthdyears')
	                            {
	                                $v2 = substr($v2, 0, 10);
	                            }
	                            $sortable_array[$k] = strtotime($v2);
	                        }
	                        elseif($on == 'epid')
	                        {
	                            $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
	                        }
	                        elseif($on == 'percentage')
	                        {
	                            $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
	                        }
	                        else
	                        {
	                            $sortable_array[$k] = ucfirst($v2);
	                        }
	                    }
	                }
	            }
	            else
	            {
	                if($on == 'birthd' || $on == 'admissiondate'   || $on == 'admission_date'  || $on == 'discharge_date'  )
	                {
	                    if($on == 'birthdyears')
	                    {
	                        $v = substr($v, 0, 10);
	                    }
	                    $sortable_array[$k] = strtotime($v);
	                }
	                elseif($on == 'epid' || $on == 'percentage')
	                {
	                    $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
	                }
	                elseif($on == 'percentage')
	                {
	                    $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
	                }
	                else
	                {
	                    $sortable_array[$k] = ucfirst($v);
	                }
	            }
	        }
	        switch($order)
	        {
	            case SORT_ASC:
	                $sortable_array = Pms_CommonData::a_sort($sortable_array);
	                break;
	
	            case SORT_DESC:
	                $sortable_array = Pms_CommonData::ar_sort($sortable_array);
	
	                break;
	        }
	
	        foreach($sortable_array as $k => $v)
	        {
	            $new_array[$k] = $array[$k];
	        }
	    }
	
	    return $new_array;
	}
	
	/**
	 * @cla on 10.06.2018 : do not use
	 * @deprecated
	 */
	public function dgppatientlistAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('dgplist', $logininfo->userid, 'canview');

		if (!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$client_data = new Client();
		$client_details = $client_data->getClientDataByid($logininfo->clientid);
		
		$this->view->dgp_user = $client_details[0]['dgp_user'];
		$this->view->dgp_pass = $client_details[0]['dgp_pass'];

		if((!empty($client_details[0]['dgp_user']) && !empty($client_details[0]['dgp_pass'])) || $_REQUEST['dbg'] == "1")
		{
			$this->view->xml_gen_disabled = false;
		}
		else
		{
			$this->view->xml_gen_disabled = true;
		}
		
		
		
		/* ############################################################################################### */
		/* ############################### ONLY COMPLETED DATA -  START ################################## */
		/* ############################################################################################### */
		
		$modules = new Modules();
		$display_only_completed = $modules->checkModulePrivileges("98", $logininfo->clientid);
		
		if($display_only_completed){
			$only_completed = "1"; 
		} else{
			$only_completed = "0"; 
		}
		$this->view->display_only_completed = $only_completed ;
		
	}

	/**
	 * @cla on 10.06.2018 : do not use
	 * @deprecated
	 */
	public function dgplistAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$hidemagic = Zend_Registry::get('hidemagic');
		$this->view->hidemagic = $hidemagic;
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('dgplist', $logininfo->userid, 'canview');

		if (!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if ($logininfo->usertype != 'SA')
		{
			$where = 'clientid=' . $logininfo->clientid;
		}
		else
		{
			$where = 'clientid=' . $logininfo->clientid;
		}
		
		
		$columnarray = array ("pk" => "id", "fn" => "p__0", "ln" => "p__3", "rd" => "p__admission_date", "ledt" => "p__change_date", "bd" => "p__birthd", 'ed' => 'epid_num');
		$orderarray = array ("ASC" => "DESC", "DESC" => "ASC");

		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];

		//		1.get client current discharge methods(all)
		$discharge_methods = Doctrine_Query::create()
		->select("*")
		->from('DischargeMethod')
		->where("isdelete = 0  and clientid=" . $logininfo->clientid . "");

		$c_discharged_methods = $discharge_methods->fetchArray();

		$client_discharge_methods[] = '999999999';
		foreach ($c_discharged_methods as $k_method => $v_method)
		{
			$client_discharge_methods[] = $v_method['id'];
		}

		//		2.use discharge methods to get last 40 discharged patient of current client

		$last_discharges = Doctrine_Query::create()
		->select('*')
		->from('PatientDischarge')
		->where('isdelete = 0')
		->andWhereIn('discharge_method', $client_discharge_methods)
		->orderBy('discharge_date DESC');

		$last_discharged_patients = $last_discharges->fetchArray();

		$discharged_ipids[] = '999999999';
		foreach ($last_discharged_patients as $k_dis_patient => $v_dis_patient)
		{
			$discharged_ipids[] = $v_dis_patient['ipid'];
		}


		$patient = Doctrine_Query::create()
		->select('p.ipid, p.admission_date')
		->from('PatientMaster p')
		->leftJoin("p.EpidIpidMapping e")
		->where("p.isdischarged = 1 and p.isarchived=0 and p.isdelete = 0 and p.isstandbydelete=0")
		->andwhereIn('p.ipid', $discharged_ipids);
		$patient->andWhere('e.ipid = p.ipid');
		$patient->andWhere('e.clientid = ' . $logininfo->clientid);
		$patienidtarray = $patient->fetchArray();
		if (count($patienidtarray) == 0)
		{
			$patienidtarray[0] = "1";
		}
		$patientarray[0]['count'] = sizeof($patienidtarray);

		$disdata = new PatientDischarge();
		$dischargedata = $disdata->getPatientsDischargeDetails($patienidtarray, $_GET['clm'], $_GET['ord']);


		foreach ($dischargedata as $discharge_key => $discharge_item)
		{
			$orderbydischarge_str .= '"' . $discharge_key . '",';
		}

		foreach ($patienidtarray as $ipid)
		{
			$ipidz_simple[] = $ipid['ipid'];
			$ipid_str .= '"' . $ipid['ipid'] . '",';
		}

		$ipid_str = substr($ipid_str, 0, -1);

		$patientKvnofirst = Doctrine_Query::create()
		->select('*')
		->from('DgpKern ka')
		->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid and  kb.form_type = "adm"  and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` ASC	LIMIT 1 )')
		->groupBy('ipid')
		->orderby('id asc');
		$patientKvnoarrayfirst = $patientKvnofirst->fetchArray();

		// verify if first form is completed
		foreach ($patientKvnoarrayfirst as $key => $value)
		{
			$itemfirst[$value['ipid']]['ipid'] = $value['ipid'];
			$itemfirst[$value['ipid']]['kvno_id'] = $value['id'];
			if (empty($value['wohnsituations'])
					|| $value['ecog'] == '0'
					|| $value['datum_der_erfassung1'] == "0000-00-00 00:00:00"
					|| $value['schmerzen'] == '0'
					|| $value['ubelkeit'] == '0'
					|| $value['erbrechen'] == '0'
					|| $value['luftnot'] == '0'
					|| $value['verstopfung'] == '0'
					|| $value['swache'] == '0'
					|| $value['appetitmangel'] == '0'
					|| $value['mudigkeit'] == '0'
					|| $value['dekubitus'] == '0'
					|| $value['hilfebedarf'] == '0'
					|| $value['depresiv'] == '0'
					|| $value['angst'] == '0'
					|| $value['anspannung'] == '0'
					|| $value['desorientier'] == '0'
					|| $value['versorgung'] == '0'
					|| $value['umfelds'] == '0'
					|| $value['sonstige_probleme'] == '0'
					|| empty($value['kontaktes'])
					|| empty($value['who'])
					|| $value['steroide'] == '0'
					|| $value['chemotherapie'] == '0'
					|| $value['strahlentherapie'] == '0'
					|| empty($value['entlasung_date'])
					|| $value['therapieende'] == '0'
					|| empty($value['sterbeort_dgp'])
					|| $value['zufriedenheit_mit'] == '0'
			)
			{
				$itemfirst[$value['ipid']]['status'] = 'n'; // not completed
			}
			else
			{
				$itemfirst[$value['ipid']]['status'] = 'c'; // completed
			}
		}

		$patientKvnolast = Doctrine_Query::create()
		->select('*')
		->from('DgpKern ka')
		->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid  and  kb.form_type = "dis" and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` DESC	LIMIT 1 )')
		->groupBy('ipid')
		->orderby('id asc');
		$patientKvnoarraylast = $patientKvnolast->fetchArray();

		foreach ($patientKvnoarraylast as $key_last => $value_last)
		{
			$itemlast[$value_last['ipid']]['ipid'] = $value_last['ipid'];
			$itemlast[$value_last['ipid']]['kvno_id'] = $value_last['id'];
			if ($itemlast[$value_last['ipid']]['kvno_id'] != $itemfirst[$value_last['ipid']]['kvno_id'])
			{
				if (
						empty($value_last['wohnsituations'])
						|| $value_last['ecog'] == '0'
						|| $value_last['datum_der_erfassung1'] == "0000-00-00 00:00:00"
						|| $value_last['schmerzen'] == '0'
						|| $value_last['ubelkeit'] == '0'
						|| $value_last['erbrechen'] == '0'
						|| $value_last['luftnot'] == '0'
						|| $value_last['verstopfung'] == '0'
						|| $value_last['swache'] == '0'
						|| $value_last['appetitmangel'] == '0'
						|| $value_last['mudigkeit'] == '0'
						|| $value_last['dekubitus'] == '0'
						|| $value_last['hilfebedarf'] == '0'
						|| $value_last['depresiv'] == '0'
						|| $value_last['angst'] == '0'
						|| $value_last['anspannung'] == '0'
						|| $value_last['desorientier'] == '0'
						|| $value_last['versorgung'] == '0'
						|| $value_last['umfelds'] == '0'
						|| $value_last['sonstige_probleme'] == '0'
						|| empty($value_last['kontaktes'])
						|| empty($value_last['who'])
						|| $value_last['steroide'] == '0'
						|| $value_last['chemotherapie'] == '0'
						|| $value_last['strahlentherapie'] == '0'
						|| empty($value_last['entlasung_date'])
						|| $value_last['therapieende'] == '0'
						|| empty($value_last['sterbeort_dgp'])
						|| $value_last['zufriedenheit_mit'] == '0'
				)
				{
					$itemlast[$value_last['ipid']]['status'] = 'n'; //not completed
				}
				else
				{
					$itemlast[$value_last['ipid']]['status'] = 'c'; // completed
				}
			}
			else
			{
				$itemlast[$value_last['ipid']]['status'] = 'n'; // not completed
			}
		}


		//		1. preluare sapvs pacient din stamdattem
		$sapv= Doctrine_Query::create ()
		->select("*")
		->from('SapvVerordnung')
		->whereIn('ipid', $ipidz_simple)
		->andwhere('isdelete = 0')
		->orderBy('verordnungam ASC');
		$patient_sapvsarray = $sapv->fetchArray();

		$patient_sapvs= array();

		foreach($patient_sapvsarray as  $value ) {
			$patient_sapvs[$value['ipid']][] =  $value['id'];
		}

		//		2. preluare filled sapvs
		$dgpsapv= Doctrine_Query::create()
		->select("*")
		->from('DgpSapv')
		->whereIn('ipid', $ipidz_simple);
		$patient_dgpsapvsarray = $dgpsapv->fetchArray();

		//		3. check if filled sapv is completed or not
		foreach($patient_dgpsapvsarray as $k_dgpsapv=>$v_dgpsapv)
		{
			if(
					empty($v_dgpsapv['identifiknr'])
					|| empty($v_dgpsapv['sapv'])
					|| empty($v_dgpsapv['verordnung_datum'])
					|| empty($v_dgpsapv['art_der_erordnung'])
					|| $v_dgpsapv['verordnung_durch'] == '0'
					|| $v_dgpsapv['ubernahme_aus'] == '0'
					|| empty($v_dgpsapv['regel_km'])
					|| empty($v_dgpsapv['end_date_sapv'])
					|| empty($v_dgpsapv['grund_einweisung'])
			)
			{
				//filled but not completed
				$filled_sapvs[$v_dgpsapv['ipid']]['not_completed'][$v_dgpsapv['sapv']] = $v_dgpsapv['sapv']; //not completed
			}
			else
			{
				//filled but completed
				$filled_sapvs[$v_dgpsapv['ipid']]['completed'][$v_dgpsapv['sapv']] = $v_dgpsapv['sapv']; // completed
			}

			$sapv2dgp[$v_dgpsapv['sapv']] = $v_dgpsapv['id'];

		}
		//		4. use this array in grid
		$this->view->filled_sapvs = $filled_sapvs;
		$this->view->sapv2dgp = $sapv2dgp;

		$limit = 40;
		$sql = "ipid,e.epid as epid,birthd,admission_date,change_date,last_update,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
		$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
		$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
		$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
		$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
		$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
		$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
		$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
		$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
		$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
		$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
		$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";

		// if super admin check if patient is visible or not
		if ($logininfo->usertype == 'SA')
		{
			$sql = "*,ipid, e.epid as epid,";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
		}

		$patient->select($sql);
		$patient->limit($limit);
		if ($dischargedata)
		{
			if ($_GET['clm'] == 'dd')
			{// discharge date
				$patient->orderBy('FIELD(p__ipid, ' . substr($orderbydischarge_str, 0, -1) . '), p__ipid');
			}
		}
		$patientlimit = $patient->fetchArray();

		
	 
		/* ############################################################################################### */
		/* ############################### ONLY COMPLETED DATA -  START ################################## */
		/* ############################################################################################### */
		
		$modules = new Modules();
		$display_only_completed = $modules->checkModulePrivileges("98", $logininfo->clientid);
		
		if($display_only_completed){
		
			foreach ($patientlimit as $key => $patient_item)
			{
				if (empty($itemfirst[$patient_item['ipid']]['status']))
				{ // check if any form was filed
					$patient_data[$patient_item['ipid']]['kvno_first'] = "0"; // non extistent
					$patient_data[$patient_item['ipid']]['kvno_last'] = "0"; //non extistent
				}
				elseif (!empty($itemfirst[$patient_item['ipid']]['status']))
				{
					if ($itemfirst[$patient_item['ipid']]['status'] == 'c')
					{
						$patient_data[$patient_item['ipid']]['kvno_first'] = "c"; // completed
					}
					else
					{
						$patient_data[$patient_item['ipid']]['kvno_first'] = "n"; //not completed
					}
					if ($itemlast[$patient_item['ipid']]['kvno_id'] != $itemfirst[$patient_item['ipid']]['kvno_id'])
					{
						if ($itemlast[$patient_item['ipid']]['status'] == 'c')
						{
							$patient_data[$patient_item['ipid']]['kvno_last'] = "c"; // completed
						}
						else
						{
							$patient_data[$patient_item['ipid']]['kvno_last'] = "n"; //not completed
						}
					}
					else
					{
						$patient_data[$patient_item['ipid']]['kvno_last'] = "0"; //non existent
					}
				}
					
				if(!empty($patient_sapvs[$patient_item['ipid']])) {
					foreach ($patient_sapvs[$patient_item['ipid']] as $value)
					{
						if(array_key_exists($value, $filled_sapvs[$patient_item['ipid']]['completed']))
						{
							//completed link
							$patient_data[$patient_item['ipid']]['sapv_form']['filled_completed'][] = $value;
						}
						else if(array_key_exists($value, $filled_sapvs[$patient_item['ipid']]['not_completed']))
						{
							//not completed link
							$patient_data[$patient_item['ipid']]['sapv_form']['filled_not_completed'][]= $value ;
						}
						else
						{
							//not filled link
							$patient_data[$patient_item['ipid']]['sapv_form']['not_filled'][] = $value;
						}
					}
				
					if(!empty($patient_data[$patient_item['ipid']]['sapv_form']['not_filled'])){
						$patient_data[$patient_item['ipid']]['sapv_form_data'] = "not_done";
					}elseif(!empty($patient_data[$patient_item['ipid']]['sapv_form']['filled_not_completed'])){
						$patient_data[$patient_item['ipid']]['sapv_form_data'] = "not_done";
					} else{
						$patient_data[$patient_item['ipid']]['sapv_form_data'] = "done";
					}
				} else{
					$patient_data[$patient_item['ipid']]['sapv_form_data'] = "done";
				}
	
				// return only patients with completed data
				if( $patient_data[$patient_item['ipid']]['kvno_last'] == "c" && $patient_data[$patient_item['ipid']]['kvno_first'] == "c" && $patient_data[$patient_item['ipid']]['sapv_form_data'] == "done") {
				 	$completed_patient_data[] = $patient_item['ipid'];
				 }
			}
	
			// if not completed unset the master array
			if(!empty($completed_patient_data)){
				foreach($patientlimit  as $pkey => $pdata){
					if(!in_array($pdata['ipid'],$completed_patient_data)){
						unset($patientlimit[$pkey]);
					}
				}
			}
		}
  
		/* ###############################################################################################  */
		/* ############################### ONLY COMPLETED DATA -  END ####################################  */
		/* ###############################################################################################  */
		
		
		foreach ($patientlimit as $key => $patient_item)
		{

			if ($dischargedata[$patient_item['ipid']]['discharge_date'] != '00.00.0000')
			{
				$patientlimit[$key]['discharge_date'] = $dischargedata[$patient_item['ipid']]['discharge_date'];
			}
			else
			{
				$patientlimit[$key]['discharge_date'] = '-';
			}

			$patientlimit[$key]['kvno_first_id'] = $itemfirst[$patient_item['ipid']]['kvno_id'];
			$patientlimit[$key]['kvno_last_id'] = $itemlast[$patient_item['ipid']]['kvno_id'];

			if (empty($itemfirst[$patient_item['ipid']]['status']))
			{ // check if any form was filed
				$patientlimit[$key]['kvno_first'] = "0"; // non extistent
				$patientlimit[$key]['kvno_last'] = "0"; //non extistent
			}
			elseif (!empty($itemfirst[$patient_item['ipid']]['status']))
			{
				if ($itemfirst[$patient_item['ipid']]['status'] == 'c')
				{
					$patientlimit[$key]['kvno_first'] = "c"; // completed
				}
				else
				{
					$patientlimit[$key]['kvno_first'] = "n"; //not completed
				}
				if ($itemlast[$patient_item['ipid']]['kvno_id'] != $itemfirst[$patient_item['ipid']]['kvno_id'])
				{
					if ($itemlast[$patient_item['ipid']]['status'] == 'c')
					{
						$patientlimit[$key]['kvno_last'] = "c"; // completed
					}
					else
					{
						$patientlimit[$key]['kvno_last'] = "n"; //not completed
					}
				}
				else
				{
					$patientlimit[$key]['kvno_last'] = "0"; //non existent
				}
			}
			$patientlimit[$key]['sapv'] = $patient_sapvs[$patient_item['ipid']]; //all patients sapv

		}

		$grid = new Pms_Grid($patientlimit, 1, $patientarray[0]['count'], "dgppatientlist.html");
		$this->view->patientgrid = $grid->renderGrid();

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array ();
		$response['callBackParameters']['dgppatientlist'] = $this->view->render('dgp/dgplist.html');

		echo json_encode($response);
		exit;
	}

	/**
	 * @cla on 10.06.2018 : do not use
	 * @deprecated
	 * use $this->dgpfullpatientlistAction
	 */
	function dgpfullpatientlistAction_OLD2018(){
	    /* ISPC-1775,ISPC-1678 */
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		set_time_limit(0);
	}
 
	/**
	 * @cla on 10.06.2018 : do not use
	 * @deprecated
	 */
	function dgpexportAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('dgplist', $userid, 'canview');
		
		
		//save DGP credentials
		
		if(!empty($_POST['dgp_user']) && !empty($_POST['dgp_pass'])) {
			$cust = Doctrine::getTable('Client')->find($clientid);
			$cust->dgp_user = Pms_CommonData::aesEncrypt($_POST['dgp_user']);
			$cust->dgp_pass = Pms_CommonData::aesEncrypt($_POST['dgp_pass']);
			$cust->save();
		}
		
		
		//process submited patients 
		$ipids_arr[] = '999999';
		foreach($_POST['dgp_patients'] as $k_pat => $v_pat)
		{
			$ipids_arr[] = Pms_CommonData::getIpid(Pms_Uuid::decrypt($v_pat));
		}

		//		1.get client current discharge methods(all)
		$discharge_methods = Doctrine_Query::create()
		->select("*")
		->from('DischargeMethod')
		->where("isdelete = 0  and clientid=" . $clientid . "");

		$c_discharged_methods = $discharge_methods->fetchArray();

		$client_discharge_methods[] = '999999999';
		foreach ($c_discharged_methods as $k_method => $v_method)
		{
			$client_discharge_methods[] = $v_method['id'];
		}

		//		2.use discharge methods to get last 40 discharged patient of current client
		$last_discharges = Doctrine_Query::create()
		->select('*')
		->from('PatientDischarge')
		->where('isdelete = 0')
		->andWhereIn('discharge_method', $client_discharge_methods)
		->orderBy('discharge_date DESC');

		$last_discharged_patients = $last_discharges->fetchArray();

		$discharged_ipids[] = '999999999';
		foreach ($last_discharged_patients as $k_dis_patient => $v_dis_patient)
		{
			$discharged_ipids[] = $v_dis_patient['ipid'];
		}

		$patient = Doctrine_Query::create()
		->select('*, p.ipid, p.admission_date, p.birthd, CONVERT(AES_DECRYPT(sex,"' . Zend_Registry::get('salt') . '") using latin1)  as gensex, e.epid, p.living_will as living_will')
		->from('PatientMaster p')
		->leftJoin("p.EpidIpidMapping e")
		->where("p.isdischarged = 1 and p.isarchived=0 and p.isdelete = 0 and p.isstandbydelete=0")
		->andwhereIn('p.ipid', $discharged_ipids)
		->andwhereIn('p.ipid', $ipids_arr);
		$patient->andWhere('e.ipid = p.ipid');
		$patient->andWhere('e.clientid = ' . $clientid);
		$patienidtarray = $patient->fetchArray();


		if (count($patienidtarray) == 0)
		{
			$patienidtarray[0] = "1";
		}

		$patientarray[0]['count'] = sizeof($patienidtarray);

		$ipid_arr[] = '999999999';
		$ipidz_simple[] = '99999999999';

		foreach ($patienidtarray as $ipid)
		{
			$ipid_arr[] = $ipid['ipid'];

			$ipidz_simple[] = $ipid['ipid'];
		}

		$disdata = new PatientDischarge();
		$dischargedata = $disdata->getPatientsDischargeDetails($patienidtarray, 'discharge_date', 'DESC');

		foreach ($dischargedata as $discharge_key => $discharge_item)
		{
			$orderbydischarge_str .= '"' . $discharge_key . '",';
		}

		$limit = 30;
		$sql = "ipid,e.epid as epid,birthd,admission_date,change_date,last_update,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
		$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
		$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
		$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
		$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
		$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
		$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
		$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
		$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
		$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
		$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
		$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";

		$patient->select($sql);
		$patient->whereIn('ipid', $ipids_arr);
		$patient->limit($limit);
		if ($dischargedata)
		{

			$patient->orderBy('FIELD(p__ipid, ' . substr($orderbydischarge_str, 0, -1) . '), p__ipid');
		}

		$patients_ids_arr = $patient->fetchArray();

		$ipid_str = '"99999999",';
		foreach ($patients_ids_arr as $ipid)
		{
			$ipid_str .= '"' . $ipid['ipid'] . '",';
		}
		$ipid_str = substr($ipid_str, 0, -1);

		//get patientlives data(wohnsituation)
		$pl = new PatientLives();
		$pat_lives = $pl->getpatientLivesData($ipid_arr);

		$this->view->alone = $pat_lives[0]['alone'];
		$this->view->house_of_relatives = $pat_lives[0]['house_of_relatives'];
		$this->view->home = $pat_lives[0]['home'];

		//get first and last kvno data
		$patient_k_first = Doctrine_Query::create()
		->select('*')
		->from('DgpKern ka')
		->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid and  kb.form_type = "adm"  and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` ASC	LIMIT 1 )')
		->groupBy('ka.ipid')
		->orderby('id asc');

		$p_kvno_first = $patient_k_first->fetchArray();

		$patient_k_last = Doctrine_Query::create()
		->select('*')
		->from('DgpKern ka')
		->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid  and  kb.form_type = "dis" AND  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` DESC	LIMIT 1 )')
		->groupBy('ka.ipid')
		->orderby('id asc');

		$p_kvno_last = $patient_k_last->fetchArray();

		foreach ($p_kvno_first as $k_first => $v_first)
		{
			$patient_kvno_first[$v_first['ipid']] = $v_first;
		}

		foreach ($p_kvno_last as $k_last => $v_last)
		{
			if ($patient_kvno_first[$v_last['ipid']]['id'] != $v_last['id'])
			{
				$patient_kvno_last[$v_last['ipid']] = $v_last;
			}
		}

		//get contactperson_master data
		$contact_pm = Doctrine_Query::create()
		->select('cpm.ipid, SUM(cpm.cnt_hatversorgungsvollmacht) as versorgung_max')
		->from('ContactPersonMaster cpm')
		->andwhere('cpm.ipid IN (' . $ipid_str . ')')
		->groupBy('cpm.ipid')
		->orderBy('cnt_hatversorgungsvollmacht ASC');

		$contact_data = $contact_pm->fetchArray();
		foreach ($contact_data as $k_contact => $v_contact)
		{
			$contact_patient_data[$v_contact['ipid']] = $v_contact;
		}

		//get all patients sapvs
		$sapv = new SapvVerordnung();
		$sapvarray = $sapv->getPatientsSapvVerordnungDetails($ipid_arr, true);

		$patient_sapv_forms = Doctrine_Query::create()
		->select('*')
		->from('DgpSapv ds')
		->where('ds.ipid in (' . $ipid_str . ')')
		->orderby('ds.id asc');

		$patient_sapv_forms_filled = $patient_sapv_forms ->fetchArray();

		foreach($patient_sapv_forms_filled as $k_sapvf=>$v_sapvf)
		{
			$filled_sapvs[$v_sapvf['sapv']] = $v_sapvf;
		}


		//get discharge locations
		$dl = new DischargeLocation();
		$discharge_locations = $dl->getDischargeLocation($clientid, 0);
		foreach ($discharge_locations as $k_disloc => $v_disloc)
		{
			$discharge_loc[$v_disloc['id']] = $v_disloc;
		}

		//process patient data
		foreach ($patients_ids_arr as $k_pat => $v_pat)
		{
// 			$export_data[$v_pat['ipid']]['B_Programm'] = 'PROGRAM';
			$export_data[$v_pat['ipid']]['B_Programm'] = 'ISPC';
			$export_data[$v_pat['ipid']]['B_Pat_ID'] = strtoupper($v_pat['epid']);
			$export_data[$v_pat['ipid']]['B_Dat_ID'] = strtoupper($v_pat['epid']);
			$export_data[$v_pat['ipid']]['B_geb_datum'] = date('Y-m', strtotime($v_pat['birthd']));
			$export_data[$v_pat['ipid']]['B_auf_datum'] = date('Y-m-d', strtotime($v_pat['admission_date']));
			
			
			// 4. Geschlecht
			// B_geschlecht
			// -1=Keine Angabe;
			// 1=1 - weiblich;
			// 2=2 - männlich
			if (!empty($v_pat['sex']))
			{
				$sex_map = array ('1' => '2', '2' => '1');
				$gender = $sex_map[$v_pat['sex']];
			}
			else
			{
				$gender = '-1';
			}

			$export_data[$v_pat['ipid']]['B_geschlecht'] = $gender;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			
			// BA_Patientenverfuegung
			// 1 = ausgewählt 
			// -1 = nicht ausgewählt
			if (!empty($v_pat['living_will']))
			{
				if ($v_pat['living_will'] == '0')
				{
					$living_will = "-1";
				}
				else if ($v_pat['living_will'] == '1')
				{
					$living_will = $v_pat['living_will'];
				}

				if(!empty($patient_kvno_first[$v_pat['ipid']]))
				{
					$export_data[$v_pat['ipid']]['BA_Patientenverfuegung'] = $living_will;
				}

				if(!empty($patient_kvno_last[$v_pat['ipid']]))
				{
					$export_data[$v_pat['ipid']]['BL_Patientenverfuegung'] = $living_will;
				}
			}
			else
			{
				if(!empty($patient_kvno_first[$v_pat['ipid']]))
				{
					$export_data[$v_pat['ipid']]['BA_Patientenverfuegung'] = '-1';
				}

				if(!empty($patient_kvno_last[$v_pat['ipid']]))
				{
					$export_data[$v_pat['ipid']]['BL_Patientenverfuegung'] = '-1';
				}
			}
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/			
			
			
			// versorgung from contact data master
			// BA_Vorsorgevollmacht
			// 1 = ausgewählt 
			// -1 = nicht ausgewählt 
			if ($contact_patient_data[$v_pat['ipid']]['versorgung_max'] > 0 && !empty($contact_patient_data[$v_pat['ipid']]))
			{
				$versorgung = "1";
			}
			else
			{
				$versorgung = "-1";
			}

			if(!empty($patient_kvno_first[$v_pat['ipid']]))
			{
				$export_data[$v_pat['ipid']]['BA_Vorsorgevollmacht'] = $versorgung;
			}

			if(!empty($patient_kvno_last[$v_pat['ipid']]))
			{
				$export_data[$v_pat['ipid']]['BL_Vorsorgevollmacht'] = $versorgung;
			}
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			
			//SAPV XML
			if ($sapvarray[$v_pat['ipid']])
			{
				foreach ($sapvarray[$v_pat['ipid']] as $k_sapv => $sapv_data)
				{

					//if is filled
					if (!empty($filled_sapvs[$sapv_data['id']]) && array_key_exists($sapv_data['id'], $filled_sapvs))
					{
						//@to do: here find a way to use keine
						//
						//minimal data set required to pass validation
// 						$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Programm'] = 'PROGRAM';
						$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Programm'] = 'ISPC';
						$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Pat_ID'] = strtoupper($v_pat['epid']);
						$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Dat_ID'] = $sapv_data['id'];
						$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_vo_datum'] = date('Y-m-d', strtotime($sapv_data['verordnungam']));
						$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_sapvdatum_beginn'] = date('Y-m-d', strtotime($sapv_data['verordnungam']));
						$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Keine'] = '-1';
						//get what kind of vv we have

						$sapv_vv = explode(',', $sapv_data['verordnet']);
						if (in_array('1', $sapv_vv))
						{
							$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Beratung'] = '1';
						}
						else
						{
							$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Beratung'] = '-1';
						}

						if (in_array('3', $sapv_vv))
						{
							$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_additiv'] = '1';
						}
						else
						{
							$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_additiv'] = '-1';
						}

						if (in_array('4', $sapv_vv))
						{
							$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_voll'] = '1';
						}
						else
						{
							$sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_voll'] = '-1';
						}


						//sapv xml for each saved sapv form
						$verordnung_map = array ('1' => '1', '2' => '2', '3' => '4', '4' => '-1');
						if (empty($filled_sapvs[$sapv_data['id']]['verordnung_durch']) || $filled_sapvs[$sapv_data['id']]['verordnung_durch'] == '0')
						{
							$vv_durch = "-1";
						}
						else
						{
							$vv_durch = $verordnung_map[$filled_sapvs[$sapv_data['id']]['verordnung_durch']];
						}
						$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_verordnung_durch'] = $vv_durch;



						if (!empty($filled_sapvs[$sapv_data['id']]['art_der_erordnung']))
						{
							if ($filled_sapvs[$sapv_data['id']]['art_der_erordnung'] == 'Erstverordnung')
							{
								$art_erordung = '1';
							}
							elseif ($filled_sapvs[$sapv_data['id']]['art_der_erordnung'] == 'Folgeverordnung')
							{
								$art_erordung = '2';
							}
						}
						else
						{
							$art_erordung = '-1';
						}

						$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_verordnung_art'] = $art_erordung;


						$ubernahme_aus_mapping = array ('0' => '-1', '1' => '1', '2'=>'-1', '3' => '2', '4' => '3', '5' => '4', '6' => '5');

						if (!empty($filled_sapvs[$sapv_data['id']]['ubernahme_aus']) && $filled_sapvs[$sapv_data['id']]['ubernahme_aus'] != '0')
						{
							$ubernahme_aus = $ubernahme_aus_mapping[$filled_sapvs[$sapv_data['id']]['ubernahme_aus']];
						}
						else
						{
							$ubernahme_aus = '-1';
						}

						$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_uebernahme_aus'] = $ubernahme_aus;

						if (!empty($filled_sapvs[$sapv_data['id']]['pcteam']) && $filled_sapvs[$sapv_data['id']]['pcteam'] != '0')
						{
							$pcteam = $filled_sapvs[$sapv_data['id']]['pcteam'];
						}
						else
						{
							$pcteam = '-1';
						}

						$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_PC_Team'] = $pcteam;

						//Ärztlich:
						$arztlich = explode(',', $filled_sapvs[$sapv_data['id']]['arztlich']);

						if (count($arztlich) > 0 && !empty($arztlich))
						{
							$arztlich_array = array ('1' => 'SAPV_Hausarzt', '2' => 'SAPV_andere_aerztlich', '3' => 'SAPV_Krankenhausarzt', '5' => 'SAPV_amb_Facharzt');

							foreach ($arztlich_array as $k_arztlich => $v_arztlich)
							{
								if (in_array($k_arztlich, $arztlich))
								{
									//set 1 if is in array from db
									$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_arztlich] = '1';
								}
								else
								{

									//set -1
									$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_arztlich] = '-1';
								}
							}
						}

						//						Pflegerisch
						$pflegerisch = explode(',', $filled_sapvs[$sapv_data['id']]['pflegerisch']);

						if (count($pflegerisch) > 0 && !empty($pflegerisch))
						{
							$pflegerisch_array = array ('1' => 'SAPV_amb_Pflege', '2' => 'SAPV_amb_Palliativpflege', '3' => 'SAPV_Pflegeheim', '4' => 'SAPV_stat_Hospiz');

							foreach ($pflegerisch_array as $k_pflegerisch => $v_pflegerisch)
							{
								if (in_array($k_pflegerisch, $pflegerisch))
								{
									//set 1 if is in array from db
									$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_pflegerisch] = '1';
								}
								else
								{

									//set -1
									$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_pflegerisch] = '-1';
								}
							}
						}

						//Ambulanter hospizdienst
						$ambulanter = explode(',', $filled_sapvs[$sapv_data['id']]['ambulanter_hospizdienst']);

						if (count($ambulanter) > 0 && !empty($ambulanter))
						{
							$ambulanter_array = array ('1' => 'SAPV_Palliativberatung', '2' => 'SAPV_ehrenamtl_Begleitung');

							foreach ($ambulanter_array as $k_ambulanter => $v_ambulanter)
							{
								if (in_array($k_ambulanter, $ambulanter))
								{
									//set 1 if is in array from db
									$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_ambulanter] = '1';
								}
								else
								{

									//set -1
									$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_ambulanter] = '-1';
								}
							}
						}

						$weitere_pro = explode(',', $filled_sapvs[$sapv_data['id']]['weitere_professionen']);

						if (count($weitere_pro) > 0 && !empty($weitere_pro))
						{
							$weitere_array = array ('1' => 'SAPV_Case_Management', '2' => 'SAPV_Ernaehrungsberatung', '3' => 'SAPV_Physiotherapie', '4' => 'SAPV_Psychotherapie',
									'5' => 'SAPV_Seelsorge', '6' => 'SAPV_Sozialarbeit', '7' => 'SAPV_andere_professionen');

							foreach ($weitere_array as $k_weitere => $v_weitere)
							{
								if (in_array($k_weitere, $weitere_pro))
								{
									//set 1 if is in array from db
									$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_weitere] = '1';
								}
								else
								{

									//set -1
									$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_weitere] = '-1';
								}
							}
						}

						//weitere Professionen
						$professions = explode(',', $filled_sapvs[$sapv_data['id']]['weitere_professionen']);

						if (count($professions) > 0 && !empty($professions))
						{
							$professions_array = array ('1' => 'SAPV_Case_Management', '2' => 'SAPV_Ernaehrungsberatung', '3' => 'SAPV_Physiotherapie', '4' => 'SAPV_Psychotherapie',
									'5' => 'SAPV_Seelsorge', '6' => 'SAPV_Sozialarbeit', '7' => 'SAPV_andere_professionen');

							foreach ($professions_array as $k_professions => $v_professions)
							{
								if (in_array($k_professions, $professions))
								{
									//set 1 if is in array from db
									$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_professions] = '1';
								}
								else
								{
									//set -1
									$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_professions] = '-1';
								}
							}
						}

						if (!empty($filled_sapvs[$sapv_data['id']]['grund_einweisung']))
						{
							$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_einw_grund'] = $filled_sapvs[$sapv_data['id']]['grund_einweisung'];
						}

						if (!empty($filled_sapvs[$sapv_data['id']]['end_date_sapv']))
						{
							$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_sapvdatum_ende'] = date('Y-m-d', strtotime($filled_sapvs[$sapv_data['id']]['end_date_sapv']));
						} else{
							$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_sapvdatum_ende'] = date('Y-m-d', strtotime($sapv_data['verordnungbis']));
						}


						if (empty($filled_sapvs[$sapv_data['id']]['therapieende']) || $filled_sapvs[$sapv_data['id']]['therapieende'] == '0')
						{
							$thera_ende = "-1";
						}
						else
						{
							$thera_ende_mapping = array('0'=>'-1', '1'=>'3', '2'=>'2', '3'=>'1','4'=>'4'); //thera ende has no "3" value in xsd...weird
							$thera_ende = $thera_ende_mapping[$filled_sapvs[$sapv_data['id']]['therapieende']];
						}

						$sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_ende_spez_amb_behandlung'] = $thera_ende;

					} //end if filled sapvs not empty
					else
					{
						//minimal data set required to pass validation
						$sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_Programm'] = 'PROGRAM';
						$sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_Pat_ID'] = strtoupper($v_pat['epid']);
						$sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_Dat_ID'] = $sapv_data['id'];
						$sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_vo_datum'] = date('Y-m-d', strtotime($sapv_data['verordnungam']));
						$sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_sapvdatum_beginn'] = date('Y-m-d', strtotime($sapv_data['verordnungam']));
						$sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_sapvdatum_ende'] = date('Y-m-d', strtotime($sapv_data['verordnungbis']));
						$sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_Keine'] = '1';
					}
				}
			}
		}

		//		get various nonkvno saved data
		//		get Haupt Diagnosis diagnosis
		$dg = new DiagnosisType();
		$abb2 = "'HD'";
		$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid, $abb2);
		$comma = ",";
		$typeid = "'0'";
		foreach ($ddarr2 as $key => $valdia)
		{
			$typeid .=$comma . "'" . $valdia['id'] . "'";
			$comma = ",";
		}

		$patdia = new PatientDiagnosis();
		$dianoarray = $patdia->getFinalData($ipid_str, $typeid, true); //set last param true to accept a list of ipids

		if (count($dianoarray) > 0)
		{
			foreach ($dianoarray as $key => $valdia)
			{
				if (!empty($valdia['diagnosis']) && !empty($valdia['icdnumber']))
				{
					$export_data[$valdia['ipid']]['BA_ICD_haupt'] = $valdia['icdnumber'];
					$export_data[$valdia['ipid']]['BL_ICD_haupt'] = $valdia['icdnumber'];
				}
			}
		}


		$wohn_mapping = array ('0' => '-1', '1' => '1', '2' => '3', '4' => '2', '6' => '4');

		//prepare export first kvno data
		foreach ($patient_kvno_first as $k_pkvno => $v_pkvno)
		{

		    //BA_wohnsituation
		    //-1=Keine Angabe; 
		    // 1=1 - allein; 
		    // 2=2 - Heim; 
		    // 3=3 - mit Angehörigen; 
		    // 4=4 - Sonstige
			if (!empty($v_pkvno['wohnsituations']))
			{
				$wohn = $wohn_mapping[$v_pkvno['wohnsituations']];
			}
			else
			{
				$wohn = '-1';
			}

			$export_data[$v_pkvno['ipid']]['BA_wohnsituation'] = $wohn;
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			//BA_ecog
            // -1 = Keine Angabe;
            // 0 = 0  - Normale Aktivitat;
            // 1 = 1 - Gehfähig, leichte Aktivitat moglich;
            // 2 = 2 - Nicht arbeitsfahig, kann > 50% der Wachzeit aufstehen;
            // 3 = 3 - Begrenzte Selbstversorgung, >50% Wachzeit bettlagerig;
            // 4 = 4 - Pflegebedurftig, permanent bettlagerig
			if (!empty($v_pkvno['ecog']))
			{
				$ecog_value = ($v_pkvno['ecog'] - 1);
			}
			else
			{
				$ecog_value = '-1';
			}

			$export_data[$v_pkvno['ipid']]['BA_ecog'] = $ecog_value;
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			
			
			
			// BA_datum
			// jjjj-mm-tt
			if (!empty($v_pkvno['datum_der_erfassung1']) && $v_pkvno['datum_der_erfassung1'] != '1970-01-01 00:00:00' && $v_pkvno['datum_der_erfassung1'] != '0000-00-00 00:00:00')
			{
				$export_data[$v_pkvno['ipid']]['BA_datum'] = date('Y-m-d', strtotime($v_pkvno['datum_der_erfassung1']));
			}
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			

			/* ================================================*/ 
			// this part is repeating with BA and BL prefix   //
			/* ================================================*/ 


			// BA_Hausarzt... all from group
			// 1 = ausgewählt 
			// -1 = nicht ausgewählt
			if (!empty($v_pkvno['begleitung']))
			{
				$begleitung = explode(',', $v_pkvno['begleitung']);
			}
			$export_data[$v_pkvno['ipid']]['BA_Hausarzt'] = (in_array('2', $begleitung)) ? '1' : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Ambulante_Pflege'] = (in_array('3', $begleitung)) ? '1' : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Palliativarzt'] = (in_array('4', $begleitung)) ? '1' : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Palliativpflege'] = (in_array('5', $begleitung)) ? '1' : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Palliativberatung'] = (in_array('6', $begleitung)) ? '1' : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Ehrenamtlicher_Dienst'] = (in_array('7', $begleitung)) ? '1' : '-1';
			$export_data[$v_pkvno['ipid']]['BA_KH_Palliativstation'] = (in_array('8', $begleitung)) ? '1' : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Hospiz_stationaer'] = (in_array('9', $begleitung)) ? '1' : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Krankenhaus_Andere_Station'] = (in_array('10', $begleitung)) ? '1' : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Heim'] = (in_array('11', $begleitung)) ? '1' : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Palliative_Care_Team'] = (in_array('12', $begleitung)) ? '1' : '-1';
			$export_data[$v_pkvno['ipid']]['BA_sonstige_behandlung'] = (in_array('13', $begleitung)) ? '1' : '-1';
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			
            // BA_Schmerzen .. all from group
            // -1=Keine Angabe;
            // 0 = 0 - kein;
            // 1 = 1 - leicht;
            // 2 = 2 - mittel;
            // 3 = 3 - stark
			$export_data[$v_pkvno['ipid']]['BA_Schmerzen'] = (!empty($v_pkvno['schmerzen'])) ? ($v_pkvno['schmerzen'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Ubelkeit'] = (!empty($v_pkvno['ubelkeit'])) ? ($v_pkvno['ubelkeit'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Erbrechen'] = (!empty($v_pkvno['erbrechen'])) ? ($v_pkvno['erbrechen'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Luftnot'] = (!empty($v_pkvno['luftnot'])) ? ($v_pkvno['luftnot'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Verstopfung'] = (!empty($v_pkvno['verstopfung'])) ? ($v_pkvno['verstopfung'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Schwache'] = (!empty($v_pkvno['swache'])) ? ($v_pkvno['swache'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Appetitmangel'] = (!empty($v_pkvno['appetitmangel'])) ? ($v_pkvno['appetitmangel'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Mudigkeit'] = (!empty($v_pkvno['mudigkeit'])) ? ($v_pkvno['mudigkeit'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Wunden'] = (!empty($v_pkvno['dekubitus'])) ? ($v_pkvno['dekubitus'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Hilfe_ATL'] = (!empty($v_pkvno['hilfebedarf'])) ? ($v_pkvno['hilfebedarf'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Depressivitat'] = (!empty($v_pkvno['depresiv'])) ? ($v_pkvno['depresiv'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Angst'] = (!empty($v_pkvno['angst'])) ? ($v_pkvno['angst'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Anspannung'] = (!empty($v_pkvno['anspannung'])) ? ($v_pkvno['anspannung'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Verwirrtheit'] = (!empty($v_pkvno['desorientier'])) ? ($v_pkvno['desorientier'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Versorgungsorg'] = (!empty($v_pkvno['versorgung'])) ? ($v_pkvno['versorgung'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_Uberforderung'] = (!empty($v_pkvno['umfelds'])) ? ($v_pkvno['umfelds'] - 1) : '-1';
			$export_data[$v_pkvno['ipid']]['BA_sonstige_probleme'] = (!empty($v_pkvno['sonstige_probleme'])) ? ($v_pkvno['sonstige_probleme'] - 1) : '-1';
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
		}

		
		/* LAST FILLED KERN FORM */
		
		foreach ($patient_kvno_last as $k_pkvno_last => $v_pkvno_last)
		{
		    // BL_wohnsituation 
		    // -1 = Keine Angabe;
		    // 1 = 1 - allein;
		    // 2 = 2 - Heim;
		    // 3 = 3 - mit Angehörigen;
		    // 4 = 4 - Sonstig
			if (!empty($v_pkvno_last['wohnsituations']))
			{
				$wohn = $wohn_mapping[$v_pkvno_last['wohnsituations']];
			}
			else
			{
				$wohn = '-1';
			}

			$export_data[$v_pkvno_last['ipid']]['BL_wohnsituation'] = $wohn;
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			//BL_datum 
			// jjjj-mm-tt
			if (!empty($v_pkvno_last['datum_der_erfassung1']) && $v_pkvno_last['datum_der_erfassung1'] != '1970-01-01 00:00:00' && $v_pkvno_last['datum_der_erfassung1'] != '0000-00-00 00:00:00')
			{
				$export_data[$v_pkvno_last['ipid']]['BL_datum'] = date('Y-m-d', strtotime($v_pkvno_last['datum_der_erfassung1']));
			}
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			//BL_ecog
			// -1 = Keine Angabe;
			// 0 = 0  - Normale Aktivitat;
			// 1 = 1 - Gehfähig, leichte Aktivitat moglich;
			// 2 = 2 - Nicht arbeitsfahig, kann > 50% der Wachzeit aufstehen;
			// 3 = 3 - Begrenzte Selbstversorgung, >50% Wachzeit bettlagerig;
			// 4 = 4 - Pflegebedurftig, permanent bettlagerig
			
			if (!empty($v_pkvno_last['ecog']))
			{
				$ecog_value = ($v_pkvno_last['ecog'] - 1);
			}
			else
			{
				$ecog_value = '-1';
			}
			$export_data[$v_pkvno_last['ipid']]['BL_ecog'] = $ecog_value;
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			
			// BL_Hausarzt .. all from group
			// 1 = ausgewählt 
			// -1 = nicht ausgewählt
			
			if (!empty($v_pkvno_last['begleitung']))
			{
				$begleitung_last = explode(',', $v_pkvno_last['begleitung']);
			}
			$export_data[$v_pkvno_last['ipid']]['BL_Hausarzt'] = (in_array('2', $begleitung_last)) ? '1' : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Ambulante_Pflege'] = (in_array('3', $begleitung_last)) ? '1' : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Palliativarzt'] = (in_array('4', $begleitung_last)) ? '1' : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Palliativpflege'] = (in_array('5', $begleitung_last)) ? '1' : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Palliativberatung'] = (in_array('6', $begleitung_last)) ? '1' : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Ehrenamtlicher_Dienst'] = (in_array('7', $begleitung_last)) ? '1' : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_KH_Palliativstation'] = (in_array('8', $begleitung_last)) ? '1' : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Hospiz_stationaer'] = (in_array('9', $begleitung_last)) ? '1' : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Krankenhaus_Andere_Station'] = (in_array('10', $begleitung_last)) ? '1' : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Heim'] = (in_array('11', $begleitung_last)) ? '1' : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Palliative_Care_Team'] = (in_array('12', $begleitung_last)) ? '1' : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_sonstige_behandlung'] = (in_array('13', $begleitung_last)) ? '1' : '-1';
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			//BL_Schmerzen  all from this group 
			// -1 =Keine Angabe;
			// 0 = 0 - kein;
			// 1 = 1 - leicht;
			// 2 = 2 - mittel;
			// 3 = 3 - stark
			$export_data[$v_pkvno_last['ipid']]['BL_Schmerzen'] = (!empty($v_pkvno_last['schmerzen'])) ? ($v_pkvno_last['schmerzen'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Ubelkeit'] = (!empty($v_pkvno_last['ubelkeit'])) ? ($v_pkvno_last['ubelkeit'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Erbrechen'] = (!empty($v_pkvno_last['erbrechen'])) ? ($v_pkvno_last['erbrechen'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Luftnot'] = (!empty($v_pkvno_last['luftnot'])) ? ($v_pkvno_last['luftnot'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Verstopfung'] = (!empty($v_pkvno_last['verstopfung'])) ? ($v_pkvno_last['verstopfung'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Schwache'] = (!empty($v_pkvno_last['swache'])) ? ($v_pkvno_last['swache'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Appetitmangel'] = (!empty($v_pkvno_last['appetitmangel'])) ? ($v_pkvno_last['appetitmangel'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Mudigkeit'] = (!empty($v_pkvno_last['mudigkeit'])) ? ($v_pkvno_last['mudigkeit'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Wunden'] = (!empty($v_pkvno_last['dekubitus'])) ? ($v_pkvno_last['dekubitus'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Hilfe_ATL'] = (!empty($v_pkvno_last['hilfebedarf'])) ? ($v_pkvno_last['hilfebedarf'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Depressivitat'] = (!empty($v_pkvno_last['depresiv'])) ? ($v_pkvno_last['depresiv'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Angst'] = (!empty($v_pkvno_last['angst'])) ? ($v_pkvno_last['angst'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Anspannung'] = (!empty($v_pkvno_last['anspannung'])) ? ($v_pkvno_last['anspannung'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Verwirrtheit'] = (!empty($v_pkvno_last['desorientier'])) ? ($v_pkvno_last['desorientier'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Versorgungsorg'] = (!empty($v_pkvno_last['versorgung'])) ? ($v_pkvno_last['versorgung'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_Uberforderung'] = (!empty($v_pkvno_last['umfelds'])) ? ($v_pkvno_last['umfelds'] - 1) : '-1';
			$export_data[$v_pkvno_last['ipid']]['BL_sonstige_probleme'] = (!empty($v_pkvno_last['sonstige_probleme'])) ? ($v_pkvno_last['sonstige_probleme'] - 1) : '-1';
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			//BL_Opioide_WHO_Stufe_3
			// 1 = ausgewählt 
			// -1 = nicht ausgewählt
			if ($v_pkvno_last['who'] == '1')
			{
				$opioids = '1';
			}
			else
			{
				$opioids = '-1';
			}
			$export_data[$v_pkvno_last['ipid']]['BL_Opioide_WHO_Stufe_3'] = $opioids;
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			// BL_Kortikosteroide
			// 1 = ausgewählt 
			// -1 = nicht ausgewählt
			if ($v_pkvno_last['steroide'] == '1')
			{
				$steroide = '1';
			}
			else
			{
				$steroide = '-1';
			}
			$export_data[$v_pkvno_last['ipid']]['BL_Kortikosteroide'] = $steroide;
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			//BL_Chemotherapie
			// -1 =Keine Angabe;
			// 0=0 = nein;
			// 1=1 = fortgesetzt;
			// 2=2 = initiiert
			$export_data[$v_pkvno_last['ipid']]['BL_Chemotherapie'] = ($v_pkvno_last['chemotherapie'] - 1);
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			
			//BL_Strahlentherapie
			// -1=Keine Angabe;
			// 0 =0 = nein;
			// 1 =1 = fortgesetzt;
			// 2 =2 = initiiert
			$export_data[$v_pkvno_last['ipid']]['BL_Strahlentherapie'] = ($v_pkvno_last['strahlentherapie'] - 1);
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
            // BL_Aufwand
            // text 
			if (!empty($v_pkvno_last['aufwand_mit']))
			{
				$export_data[$v_pkvno_last['ipid']]['BL_Aufwand'] = $v_pkvno_last['aufwand_mit'];
			}
			
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			//BL_problem_1
			// text
			if(!empty($v_pkvno_last['problem_besonders']))
			{
				$export_data[$v_pkvno_last['ipid']]['BL_problem_1'] = $v_pkvno_last['problem_besonders'];
			}
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			//BL_problem_2
			// text
			if(!empty($v_pkvno_last['problem_ausreichend']))
			{
				$export_data[$v_pkvno_last['ipid']]['BL_problem_2'] = $v_pkvno_last['problem_ausreichend'];
			}
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
            // B_datum_ende
            // 20. Datum (Entlassung / Anderung der Betreuung / Tod)|Datumsfeld (jjjj-mm-tt)
            if(strlen($v_pkvno_last['entlasung_date'])){
    			$export_data[$v_pkvno_last['ipid']]['B_datum_ende'] = date('Y-m-d', strtotime($v_pkvno_last['entlasung_date']));
            }
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			//B_thera_ende
			// -1= Keine Angabe;
			// 1 = 1 - Verstorben;
			// 2 = 2 - Verlegung, Entlassung;
			// 4 = 4 - Sonstiges
			if ($v_pkvno_last['therapieende'] == '0' || empty($v_pkvno_last['therapieende']))
			{
				$thera_ende = "-1";
			}
			else
			{
				$thera_ende_mapping = array('0'=>'-1', '1'=>'1', '2'=>'2', '3'=>'2','4'=>'4'); //thera ende has no "3" value in xsd...weird
				$thera_ende = $thera_ende_mapping[$v_pkvno_last['therapieende']];
			}
			$export_data[$v_pkvno_last['ipid']]['B_thera_ende'] = $thera_ende;
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			//B_sterbeort_0
			// -1=Keine Angabe;
			// 1=1 - zuhause;
			// 2=2 - Heim;
			// 3=3 - Hospiz;
			// 4=4 - Palliativstation;
			// 5=5 - Krankenhaus;
			// 6=6 - nicht bekannt
			if (empty($v_pkvno_last['sterbeort_dgp']) || $v_pkvno_last['sterbeort_dgp'] == '0')
			{
				$sterbeort = '-1';
			}
			else
			{
				//system location type id=>xml loc type id
				//SYS
				//1 = zu Hause
				//2 = KH
				//3 = Hospiz
				//4 = Altenheim, Pflegeheim
				//5 = Palliativ
				//XML:
				//-1=Keine Angabe;
				//1 zuhause;
				//2 - Heim; (<-- Altenheim / Pflegeheim)
				//3 - Hospiz;
				//4 - Palliativstation;
				//5 - Krankenhaus
				//6 - nicht bekannt
				$location_type_map = array ('1' => '1', '2' => '5', '3' => '3', '4' => '2', '5' => '4');

				if ($discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type'] != 0)
				{
					$sterbeort = $location_type_map[$discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type']];
				}
				else
				{
					//no location type or other(sonstiges...)
					$sterbeort = '6';
				}
			}

			$export_data[$v_pkvno_last['ipid']]['B_sterbeort_0'] = $sterbeort;
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
			
			
            //   B_bewertung
            //  -1=Keine Angabe;
            //   1=1 - sehr gut;
            //   2=2 - gut;
            //   3=3 - mittel;
            //   4=4 - schlecht;
            //   5=5 - sehr schlecht
			$satisfaction_map = array ('0'=>'-1','1' => '5', '2' => '4', '3' => '3', '4' => '2', '5' => '1');
			
			if (empty($v_pkvno_last['zufriedenheit_mit']))
			{
				$satisfaction = '-1';
			}
			else
			{
				$satisfaction = $satisfaction_map[$v_pkvno_last['zufriedenheit_mit']];
			}
			$export_data[$v_pkvno_last['ipid']]['B_bewertung'] = $satisfaction;
			/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
		} //end last kvno foreach

		
		
		
		

		if (!empty($_POST['kern']))
		{
			//get KERNE xml
			$kern_schema = '<alle xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="KERN.xsd"></alle>';
			$xml = $this->toXml($export_data, $kern_schema, null, 'KERN');

			//echo $xml;
			if (class_exists('DOMDocument'))
			{
				$doc = new DOMDocument();
				$dom_xml = $doc->loadXML($xml);
				$source_path = APP_BASE . 'xsd/KERN.xsd';
				$validation = $doc->schemaValidate($source_path);
			}
			else
			{
				$validation = true; //sskip validation if DOMDocument is not installed
			}


			$validation = true;
			if ($validation)
			{
				$xml_type = 'KERN';
				$xml_string = $this->xmlpp($xml, false);
				//$xml_string = $xml;

				if ($_REQUEST['dbg'])
				{

					//download xml
					echo $xml_string;

					header("Pragma: public");
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Content-Type: application/force-download");
					header("Content-Type: application/octet-stream");
					header("Content-type: text/xml; charset=utf-8");
					header("Content-Disposition: attachment; filename=KERN.xml");
				}
				else
				{
					//send xml via post
					$response = $this->data_send($clientid, $xml_string, $xml_type);

					$history_id = $this->history_save($userid, $clientid, $xml_string, $response);

					if (substr($response, 0, 4) == '1000') //1000 is ok RC
					{
					    // save patients - for this upload
					    if($history_id ){
					        foreach($export_data as $pipid => $ex_data){
					            $exported_ipids[]= $pipid ;
					        }
					        $this->history_patients_save($history_id,$exported_ipids, $userid, $clientid);
					        
					    }
						//redir success
						$this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=suc&c=".substr($response, 0, 4));
					}
					else if(substr($response, 0, 4) == '2000')
					{
						//redir error auth error
						$this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
					}
					else if(substr($response, 0, 4) == '0000')
					{
						//curl error
						$this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
					}
					else if(substr($response, 0, 4) == '3100')
					{
						//XML error
						$this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
					}
					else
					{
						//redir generic error
						$this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err");
					}
				}
				exit;
			}
			else
			{
				//validation error
				$this->history_save($userid, $clientid, $xml, 'NULL');
				$this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=9999");
			}
		}
		else if (!empty($_POST['sapv']))
		{
			$sapv_schema = '<alle xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="SAPV.xsd"></alle>';
			$xml_sapv = $this->toXml($sapv_export_data, $sapv_schema, null, 'SAPV');

			//			echo $xml_sapv;
			if (class_exists('DOMDocument'))
			{
				$doc = new DOMDocument();
				$dom_xml = $doc->loadXML($xml_sapv);
				$source_path = APP_BASE . 'xsd/SAPV.xsd';
				$validation = $doc->schemaValidate($source_path);
			}
			else
			{
				$validation = true;
			}

			$validation = true;
			if ($validation)
			{
				$xml_type = 'SAPV';
				$xml_string = $this->xmlpp($xml_sapv, false);

				if ($_REQUEST['dbg'])
				{

					//download xml
					echo $xml_string;

					header("Pragma: public");
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Content-Type: application/force-download");
					header("Content-Type: application/octet-stream");
					header("Content-type: text/xml; charset=utf-8");
					header("Content-Disposition: attachment; filename=SAPV.xml");
				}
				else
				{
					//send xml via post
					$response = $this->data_send($clientid, $xml_string, $xml_type);
					$this->history_save($userid, $clientid, $xml_string, $response);

					if (substr($response, 0, 4) == '1000') //1000 is ok RC
					{
						//redir success
						$this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=suc&c=".substr($response, 0, 4));
					}
					else if(substr($response, 0, 4) == '2000')
					{
						//redir error auth error
						$this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
					}
					else if(substr($response, 0, 4) == '0000')
					{
						//curl error
						$this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
					}
					else
					{
						//redir generic error
						$this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err");
					}
				}
				exit;
			}
			else
			{
				//validation error
				$this->history_save($userid, $clientid, $xml, 'NULL');
				$this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=9999");
			}
		}
	}
	
	/**
	 * @cla on 10.06.2018 : do not use
	 * @deprecated
	 */
	private function toXml($data, $rootNodeName = 'data', $xml = null, $elem_root = 'ELEMENT', $xsd_file = false)
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1)
		{
			ini_set('zend.ze1_compatibility_mode', 0);
		}

		if ($xml == null)
		{
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?>$rootNodeName");
		}

		// loop through the data passed in.
		foreach ($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				$key = "unknownNode" . (string)$key;
			}

			// replace anything not alpha numeric
			//find out if key is ipid
			$ipid_key = explode('_', $key);
			if (count($ipid_key) == '1')
			{
				$key = $elem_root;
			}

			// if there is another array found recrusively call this function
			if (is_array($value))
			{
				$node = $xml->addChild($key);
				// recrusive call.
				DgpController::toXml($value, $rootNodeName, $node);
			}
			else
			{
				// add single node.
				$value = htmlspecialchars($value);
				$xml->addChild($key, $value);
			}
		}
		// pass back as string. or simple xml object if you want!
		return $xml->asXML();
	}

	
	/** 
	 * @cla on 10.06.2018 : do not use
	 * @deprecated
	 * 
	 * Prettifies an XML string into a human-readable and indented work of art
	 *  @param string $xml The XML as a string
	 *  @param boolean $html_output True if the output should be escaped (for use in HTML)
	 */
	function xmlpp($xml, $html_output = false)
	{
		$xml_obj = new SimpleXMLElement($xml);
		$level = 4;
		$indent = 0; // current indentation level
		$pretty = array ();

		// get an array containing each XML element
		$xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));

		// shift off opening XML tag if present
		if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0]))
		{
			$pretty[] = array_shift($xml);
		}

		foreach ($xml as $el)
		{
			if (preg_match('/^<([\w])+[^>\/]*>$/U', $el))
			{
				// opening tag, increase indent
				$pretty[] = str_repeat(' ', $indent) . $el;
				$indent += $level;
			}
			else
			{
				if (preg_match('/^<\/.+>$/', $el))
				{
					$indent -= $level;  // closing tag, decrease indent
				}
				if ($indent < 0)
				{
					$indent += $level;
				}
				$pretty[] = str_repeat(' ', $indent) . $el;
			}
		}
		$xml = implode("\n", $pretty);
		return ($html_output) ? htmlspecialchars($xml) : $xml;
	}

	/**
	 * @cla on 10.06.2018 : do not use
	 * @deprecated
	 */
	function data_send($clientid, $xml_string = false, $xml_type = false)
	{

		$client_data = new Client();
		$client_details = $client_data->getClientDataByid($clientid);

		$params['BNAME'] = $client_details[0]['dgp_user'];
		$params['KWORT'] = $client_details[0]['dgp_pass'];
		$params['VERSION'] = '3.1';

		if ($xml_string && $xml_type)
		{
			$params['DATEN'] = $xml_string;

			if ($xml_type == 'SAPV')
			{
				$url = 'https://daten.hospiz-palliativ-register.de/upload/sapv.php';
			}
			else if ($xml_type == 'KERN')
			{
				$url = 'https://daten.hospiz-palliativ-register.de/upload/kern.php';
			}

			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_VERBOSE, false);


			$result = curl_exec($ch);
			$err = curl_error($ch);

			if (!$result)
			{
				return '0000'.$err;
			}
			else
			{
				return htmlspecialchars($result);
			}
			curl_close($ch);

		}
	}

	/**
	 * @cla on 10.06.2018 : do not use
	 * @deprecated
	 */
	function history_save($user, $client, $xml, $response)
	{
		$history = new DgpHistory();
		$history->user = $user;
		$history->client = $client;
		$history->upload_date = date('Y-m-d H:i:s');
		$history->xml_string = $xml;
		$history->response_code = substr($response, 0, 4);
		$history->response_text = $response;
		$history->save();
		
		return $history->id;
	}

	/**
	 * @cla on 10.06.2018 : do not use
	 * @deprecated
	 */
	function history_patients_save($history_id, $ipids,$user, $client)
	{
	    /* ISPC-1775,ISPC-1678 */
	    if(!empty($ipids)){
	        foreach($ipids as  $ipid){
                $records_array[] = array(
                    'history_id' => $history_id,
                    'ipid' => $ipid,
                    'upload_date' => date('Y-m-d H:i:s'),
                    'user' => $user, 
                    'client' => $client 
                );	            
	        }
	    }

	    if(count($records_array) > 0)
	    {
	        //insert many records with one query!!
	        $collection = new Doctrine_Collection('DgpPatientsHistory');
	        $collection->fromArray($records_array);
	        $collection->save();
	    }
	    
	}

	/**
	 * @cla on 10.06.2018 : do not use
	 * @deprecated
	 */
    public function listpatientsAction()
    {/* 
    	wl_bochum
    	t1 34s submited
    	t2 1.9m
    	t3 1.42
    	
    	wl_dortmund
    	t1 41s
    	t2	1.34
    	t3 2.15*/
    	
    	
        /* ISPC-1775,ISPC-1678 */
        set_time_limit(0);
        $this->_helper->viewRenderer->setNoRender();
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $hidemagic = Zend_Registry::get('hidemagic');
        $this->view->hidemagic = $hidemagic;
        
        if(strlen($_REQUEST['length']) > 0 ){
            $limit = (int)$_REQUEST['length'];
        } else{
            $limit = 10;
        }
        if(strlen($_REQUEST['start']) > 0 ){
            $offset = (int)$_REQUEST['start'];
        } else{
            $offset = 0;
        }
        $search_value = $_REQUEST['search']['value'];
        
        if (strlen($_REQUEST['order'][0]['column']) > 0) {
            $order_column = $_REQUEST['order'][0]['column'];
        } else {
            $order_column = "1"; // last_name
        }
        
        $order_dir = $_REQUEST['order'][0]['dir'];
        
        // get columns from db
        if ($_REQUEST['status'] == "notsubmited" || $_REQUEST['status'] == "ready_to_send") {
            $columns_array = array(
                "1" => "e.epid_num",
                "2" => "p.last_name",
                "3" => "p.first_name",
                "4" => "p.admission_date",
                "5" => "d.discharge_date"
            );
        } else {
            
            $columns_array = array(
                "0" => "e.epid_num",
                "1" => "p.last_name",
                "2" => "p.first_name",
                "3" => "p.admission_date",
                "4" => "d.discharge_date"
            );
        }
        
        if ($columns_array[$order_column]) {
            $column_sort = $columns_array[$order_column];
            $order_by_str = $columns_array[$order_column] . ' ' . $order_dir . ' ';
        }
        
        if ($clientid > 0) {
            $where = ' and clientid=' . $clientid;
        } else {
            $where = ' and clientid=0';
        }
        
        $sql = "e.epid as epid,e.ipid,e.clientid,e.epid_num as epid_num,";
        $sql .= "p.ipid,p.admission_date,p.isdischarged,p.last_update,p.last_update_user,";
        $sql .= "d.ipid,d.discharge_date,d.discharge_method,";
        $sql .= "CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)  as first_name,";
        $sql .= "CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name";

        // if super admin check if patient is visible or not
        if ($logininfo->usertype == 'SA') {
            $sql = "e.epid as epid,e.ipid,e.clientid,e.epid_num as epid_num,";
            $sql .= "p.ipid,p.first_name,p.last_name,p.admission_date,p.isdischarged,p.last_update,p.last_update_user,";
            $sql .= "d.ipid,d.discharge_date,d.discharge_method,";
            
            $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
            $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name";
        }
        
        $first_sql_select = "e.ipid";
        
        $sql_discharge = "";
        $q = Doctrine_Query::create()
            ->select($first_sql_select)
            ->from('EpidIpidMapping e ')
            ->leftJoin('e.PatientMaster p')
            ->leftJoin('e.PatientDischarge d ON d.ipid = e.ipid AND d.isdelete = "0"')
            ->where('e.clientid = "' . $clientid . '"')
            ->andWhere('p.isdelete = 0')
            ->andWhere('p.isstandby = 0')
            ->andWhere('p.isstandbydelete = 0');
        
        if (isset($search_value) && strlen($search_value) > 0) {
            $q->andWhere('(CONCAT(AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '"), " ",AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '"), " ",e.epid) LIKE "%' . addslashes(trim($search_value)) . '%")');
        }
        
//         echo $q->getSqlQuery(); exit;
        $patientlimit_all = $q->fetchArray();
        if (count($patientlimit_all) == 0 ){
        	$patientlimit_all = array("99999999");
        }
        
        $all_patients =  array_column($patientlimit_all, 'ipid');
        if (count($all_patients) == 0 ){
        	$all_patients = array("99999999");
        }
        
        $client_data = new Client();
        $client_details_array = $client_data->getClientDataByid($clientid);
        $client_details = $client_details_array[0];

        if($client_details['dgp_transfer_date'] != "0000-00-00 00:00:00" && $client_details['dgp_transfer_date'] != "1970-01-01 01:01:00")
        {
        	$sqls = 'e.epid, p.ipid, e.ipid,';
        	$conditions['periods'][0]['start'] =   date('Y-m-d',strtotime($client_details['dgp_transfer_date']));
        	$conditions['periods'][0]['end'] = date('Y-m-d');
        	$conditions['client'] = $clientid;
        	$conditions['ipids'] = $all_patients;
        	 
        	$patient_days = Pms_CommonData::patients_days($conditions,$sqls);
        	$active_patients = array_keys($patient_days);
        
  			foreach ($all_patients as $k=> $ipid_val){
				if(!in_array($ipid_val,$active_patients)){
        			unset($all_patients[$k]);
        		}
        	}
        
        	foreach ($patientlimit_all as $ks=> $ipid_vals){
        		if(!in_array($ipid_vals['ipid'],$active_patients)){
        			unset($patientlimit_all[$ks]);
        		}
        	}
        } else{
        	$active_patients = $all_patients;
        }
        
//         echo "<pre>";
//        print_r(count($all_patients));
//        print_r($all_patients);
//         die();
        
        $completed_patients = DgpKern::patients_filled_status($all_patients);
       
        if( ! $completed_patients || $completed_patients === null ){
	        $completed_patients = array();
        }
//         var_dump($completed_patients);
//         die();
        // get all patients
       $all_submited_patients =  DgpPatientsHistory::submited_patients($patientlimit_all);

//                print_r($all_submited_patients);
//                die();
               
        if ($_REQUEST['status'] == "ready_to_send" && count($completed_patients) > 0) {
        	
        	$all_submited_patients['ready_to_send'] = array_diff($completed_patients , $all_submited_patients['submited']);
        
        	$q->andWhereIn('p.ipid', $all_submited_patients['ready_to_send']);
        	
        } elseif($_REQUEST['status'] == "notsubmited") {
        	
        	$all_submited_patients['not_submited'] = array_diff($all_submited_patients['not_submited'], $completed_patients);
        	
        	if (count($all_submited_patients['not_submited']) > 0) {
        		$q->andWhereIn('p.ipid',$all_submited_patients['not_submited']);
        	}
        	
        } else if (count($all_submited_patients['submited']) > 0) {
        	
           	$q->andWhereIn('p.ipid',$all_submited_patients['submited']);
           	
        }
        
        
        if($client_details['dgp_transfer_date'] != "0000-00-00 00:00:00" && $client_details['dgp_transfer_date'] != "1970-01-01 01:01:00" && !empty($active_patients))
        {
           	$q->andWhereIn('p.ipid',$active_patients);
        }        	
        
        if ($_REQUEST['status'] == "ready_to_send" ){
        	
        	$full_count = count($all_submited_patients['ready_to_send']);
        	
        } elseif($_REQUEST['status'] == "notsubmited") {
        	
        	$full_count = count($all_submited_patients['not_submited']);
        	
        } elseif($_REQUEST['status'] == "submited") {
        	
        	$full_count = count($all_submited_patients['submited']);
        }
       
        // get tab patients   
        
//         $patientlimit_subm = $q->select("count(*) as cnt" )->fetchOne();
//         $full_count = count($patientlimit_subm);
//         $full_count = $patientlimit_subm['cnt'];
        
        if ($limit != "-1") { // -1 = list all
            $q->limit($limit);
            $q->offset($offset);
        }
        // get tab patients on page
        $q->select($sql);
        $q->orderBy($order_by_str);
        
        if($_REQUEST['dbg']=="zet"){
        	print_r("\n completed_patients \n");
        	print_r($completed_patients);
        	print_r("\n ready_to_send \n");
        	print_r($all_submited_patients['ready_to_send']);
        	print_r("\n active_patients \n");
        	print_r($active_patients);
        	print_r("\n patientlimit_all \n");
        	print_r($patientlimit_all);

        	print_r("\n all_submited_patients");
        	print_r("\n");
        	print_r($all_submited_patients);
        	print_r("\n");
        	echo $q->getSqlQuery(); exit;
        }
        
        $patientlimit = $q->fetchArray();
        

        
        foreach ($patientlimit as $key => $patient_data) {
            if(!empty($patient_data['PatientDischarge'])){
                foreach($patient_data['PatientDischarge'] as $k=>$pd_Data){
                    $discharge_detils[$patient_data['ipid']][] = $pd_Data;
                }
            }
        }

        $patients_ipids[] = '99999999999999';
        foreach ($patientlimit as $key => $patient_data) {
//         	if(in_array($patient_data['ipid'],$active_patients)){
        		
	            $patient_details_arr[$patient_data['ipid']]['encrypt_id'] = Pms_Uuid::encrypt($patient_data['PatientMaster']['id']);
	            $patient_details_arr[$patient_data['ipid']]['ipid'] = $patient_data['ipid'];
	            $patient_details_arr[$patient_data['ipid']]['epid'] = $patient_data['epid'];
	            $patient_details_arr[$patient_data['ipid']]['epid_num'] = $patient_data['epid_num'];
	            $patient_details_arr[$patient_data['ipid']]['first_name'] = $patient_data['first_name'];
	            $patient_details_arr[$patient_data['ipid']]['last_name'] = $patient_data['last_name'];
	            $patient_details_arr[$patient_data['ipid']]['admission_date'] = date("d.m.Y", strtotime($patient_data['PatientMaster']['admission_date']));
	            
	            if ($patient_data['PatientMaster']['isdischarged'] == '1') {
	                //$patient_details_arr[$patient_data['ipid']]['discharge_date'] = date("d.m.Y", strtotime($patient_data['PatientDischarge'][$patient_data['ipid']]['discharge_date']));
	                $last_discharge = end( $discharge_detils[$patient_data['ipid']]);
	                $patient_details_arr[$patient_data['ipid']]['discharge_date'] = date("d.m.Y", strtotime($last_discharge['discharge_date']));
	            } else {
	                $patient_details_arr[$patient_data['ipid']]['discharge_date'] = "-";
	            }
	            
	            $patients_ipids[] = $patient_data['ipid'];
//         	}
        }
        
//         if($_REQUEST['dbg']=="11"){
// //            	print_R($patients_ipids); 
// //            	var_dump($completed_patients); 
//             print_R($completed_patients); 
//             exit;
//         }
//         $submited_patients =  DgpPatientsHistory::patients_submited_status($patients_ipids);

        $row_id = 0;
        $resulted_data = array();
        $status = "";
        $submited_count = 0;
        $not_submited_count = 0;
        

        
        foreach ($patient_details_arr as $v_id => $vw_data) {

            if(in_array($vw_data['ipid'],$completed_patients)){
                $status = "filled";
            } else {
                $status = "not_filled";
            }
            
            if(in_array($vw_data['ipid'],$all_submited_patients['submited'])){
//             if($submited_patients[$vw_data['ipid']] == "submited"){
                $status .= "submited";
                $pat_status[$vw_data['ipid']] = "submited";
            } 
            elseif (in_array($vw_data['ipid'], $completed_patients)){
            	$status .= "not_submited";
            	$pat_status[$vw_data['ipid']] = "ready_to_send";
            }
            else {
                $status .= "not_submited";
                $pat_status[$vw_data['ipid']] = "not_submited";
            }
            
            
            if ($_REQUEST['status'] == "ready_to_send" && $pat_status[$vw_data['ipid']] == "ready_to_send") {
            
            	if($userid == "338")
            	{
            		if(in_array($vw_data['ipid'],$completed_patients)){
            			$resulted_data[$row_id]['select_patient'] = '<input type="checkbox" name="dgp_patients[]" class="vws_ready_to_send" value="' . $vw_data['encrypt_id'] . '"/>';
            		} else{
            			$resulted_data[$row_id]['select_patient'] = '';
            		}
            	}
            	else
            	{
            		$resulted_data[$row_id]['select_patient'] = '<input type="checkbox" name="dgp_patients[]" class="vws_ready_to_send" value="' . $vw_data['encrypt_id'] . '"/>';
            	}
            
            	$resulted_data[$row_id]['discharge_date'] = "";
            	$resulted_data[$row_id]['epid'] = $vw_data['epid'];
            	$resulted_data[$row_id]['first_name'] = $vw_data['first_name'];
            	$resulted_data[$row_id]['last_name'] = $vw_data['last_name'];
            	$resulted_data[$row_id]['admission_date'] = $vw_data['admission_date'];
            	$resulted_data[$row_id]['discharge_date'] = $vw_data['discharge_date'];
            	$resulted_data[$row_id]['register_status'] = '<a href="' . APP_BASE . 'patientnew/hospizregisterv3?id=' . $vw_data['encrypt_id'] . '" class="register_icon '.$status.' " title="'.$this->view->translate('register status '.$status).'" ></a>';
            
            	$row_id ++;
            	$not_submited_count++;
            }
            elseif ($_REQUEST['status'] == "notsubmited" && $pat_status[$vw_data['ipid']] == "not_submited") {
            
                if($userid == "338")
                {
                    if(in_array($vw_data['ipid'],$completed_patients)){
                        $resulted_data[$row_id]['select_patient'] = '<input type="checkbox" name="dgp_patients[]" class="vws" value="' . $vw_data['encrypt_id'] . '"/>';
                    } else{
                        $resulted_data[$row_id]['select_patient'] = '';
                    }
                } 
                else
                {
                    $resulted_data[$row_id]['select_patient'] = '<input type="checkbox" name="dgp_patients[]" class="vws" value="' . $vw_data['encrypt_id'] . '"/>';
                }
                
                $resulted_data[$row_id]['discharge_date'] = "";
                $resulted_data[$row_id]['epid'] = $vw_data['epid'];
                $resulted_data[$row_id]['first_name'] = $vw_data['first_name'];
                $resulted_data[$row_id]['last_name'] = $vw_data['last_name'];
                $resulted_data[$row_id]['admission_date'] = $vw_data['admission_date'];
                $resulted_data[$row_id]['discharge_date'] = $vw_data['discharge_date'];
                $resulted_data[$row_id]['register_status'] = '<a href="' . APP_BASE . 'patientnew/hospizregisterv3?id=' . $vw_data['encrypt_id'] . '" class="register_icon '.$status.' " title="'.$this->view->translate('register status '.$status).'" ></a>';
    
                $row_id ++;
                $not_submited_count++;
            } 
            else if ($_REQUEST['status'] == "submited" && $pat_status[$vw_data['ipid']] == "submited") 
            {
                $resulted_data[$row_id]['discharge_date'] = "";
                $resulted_data[$row_id]['epid'] = $vw_data['epid'];
                $resulted_data[$row_id]['first_name'] = $vw_data['first_name'];
                $resulted_data[$row_id]['last_name'] = $vw_data['last_name'];
                $resulted_data[$row_id]['admission_date'] = $vw_data['admission_date'];
                $resulted_data[$row_id]['discharge_date'] = $vw_data['discharge_date'];
                
                $resulted_data[$row_id]['select_patient'] = '';
                
                $resulted_data[$row_id]['register_status'] = '<a href="' . APP_BASE . 'patientnew/hospizregisterv3?id=' . $vw_data['encrypt_id'] . '" class="register_icon '.$status.' " title="'.$this->view->translate('register status '.$status).'" ></a>';
    
                $row_id ++;
                $submited_count ++; 
            }
        }
        
        $response['draw'] = $_REQUEST['draw']; // ? get the sent draw from data table
        $response['recordsTotal'] = $full_count;
        $response['recordsFiltered'] = $full_count; // ??
        $response['data'] = $resulted_data;
        
        header("Content-type: application/json; charset=UTF-8");
        
        echo json_encode($response);
//         if (extension_loaded('xhprof')) {
//         	$profiler_namespace = 'AllInOne';  // namespace for your application
//         	$xhprof_data = xhprof_disable();
//         	$xhprof_runs = new XHProfRuns_Default();
//         	$run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);
        
//         	// url to the XHProf UI libraries (change the host name and path)
//         	$profiler_url = sprintf('http://localhost/xhprof/xhprof_html/index.php?run=%s&source=%s', $run_id, $profiler_namespace);
// //         	echo '<br style="clear: both"/><div style="block;background: #c00"><a href="'. $profiler_url .'" target="_blank">Profiler output</a></div>';
//         } else {
//         	echo '<br><br>MOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO<br><br>';
//         }
        
        exit();
    }

    

    /**
     * @cla on 10.06.2018 : do not use
     * @deprecated
     */
    function dgpnewexportAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $previleges = new Pms_Acl_Assertion();
        $return = $previleges->checkPrevilege('dgplist', $userid, 'canview');
        $patientmaster = new PatientMaster();
        //save DGP credentials
    
      /*   if(!empty($_POST['dgp_user']) && !empty($_POST['dgp_pass'])) {
            $cust = Doctrine::getTable('Client')->find($clientid);
            $cust->dgp_user = Pms_CommonData::aesEncrypt($_POST['dgp_user']);
            $cust->dgp_pass = Pms_CommonData::aesEncrypt($_POST['dgp_pass']);
            $cust->save();
        }
     */
        
        /* ISPC-1775,ISPC-1678, ISPC-1994 */
        
        if(empty($_POST['dgp_patients'])){
        	$this->_redirect(APP_BASE . "dgp/dgpfullpatientlist?res=No_patients");
        }
        //process submited patients
        foreach($_POST['dgp_patients'] as $k_pat => $v_pat)
        {
            $ipids_arr[] = Pms_CommonData::getIpid(Pms_Uuid::decrypt($v_pat));
        }


        $dgp_kern_model = new DgpKern();
        $partners_array = $dgp_kern_model->get_form_texts();
        
        $dgp['texts'] = $partners_array;
        
        
        //		1.get client current discharge methods(all)
        $discharge_methods = Doctrine_Query::create()
        ->select("*")
        ->from('DischargeMethod')
        ->where("isdelete = 0  and clientid=" . $clientid . "");
    
        $c_discharged_methods = $discharge_methods->fetchArray();
    
        $client_discharge_methods[] = '999999999';
        $dm_deadfinal = array();
        
        foreach ($c_discharged_methods as $k_method => $v_method)
        {
            $client_discharge_methods[] = $v_method['id'];
            if(in_array(strtolower($v_method['abbr']),array("tod","verstorben")))
            {
            	$dm_deadfinal[] = $val['id'];
            }
        }
    
        //		2.use discharge methods to get last 40 discharged patient of current client
        $last_discharges = Doctrine_Query::create()
        ->select('*')
        ->from('PatientDischarge')
        ->where('isdelete = 0')
        ->andWhereIn('discharge_method', $client_discharge_methods)
        ->orderBy('discharge_date DESC');
    
        $last_discharged_patients = $last_discharges->fetchArray();
    
        $discharged_ipids[] = '999999999';
        foreach ($last_discharged_patients as $k_dis_patient => $v_dis_patient)
        {
            $discharged_ipids[] = $v_dis_patient['ipid'];
            if(in_array($v_dis_patient['discharge_method'],$dm_deadfinal)){
            	$patients_tod_date[$v_dis_patient['ipid']] = date("Y-m-d", strtotime($v_dis_patient['discharge_date']));
            }
        }
    
        $patient = Doctrine_Query::create()
        ->select('*, p.ipid, p.admission_date, p.birthd, CONVERT(AES_DECRYPT(sex,"' . Zend_Registry::get('salt') . '") using latin1)  as gensex, e.epid, p.living_will as living_will')
        ->from('PatientMaster p')
        ->leftJoin("p.EpidIpidMapping e")
        ->where("p.isdelete = 0 and p.isstandbydelete=0")
        ->andwhereIn('p.ipid', $ipids_arr);
        $patient->andWhere('e.ipid = p.ipid');
        $patient->andWhere('e.clientid = ' . $clientid);
        $patienidtarray = $patient->fetchArray();
    
    
        if (count($patienidtarray) == 0)
        {
            $patienidtarray[0] = "1";
        }
    
        $patientarray[0]['count'] = sizeof($patienidtarray);
    
        $ipid_arr[] = '999999999';
        $ipidz_simple[] = '99999999999';
    
        foreach ($patienidtarray as $ipid)
        {
            $ipid_arr[] = $ipid['ipid'];
    
            $ipidz_simple[] = $ipid['ipid'];
        }
    
        $disdata = new PatientDischarge();
        $dischargedata = $disdata->getPatientsDischargeDetails($patienidtarray, 'discharge_date', 'DESC');
    
        foreach ($dischargedata as $discharge_key => $discharge_item)
        {
            $orderbydischarge_str .= '"' . $discharge_key . '",';
        }
    
        $limit = 30;
        $sql = "ipid,e.epid as epid,birthd,admission_date,change_date,last_update,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
        $sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
        $sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
        $sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
        $sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
        $sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
        $sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
        $sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
        $sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
        $sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
        $sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
        $sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";
    
        $patient->select($sql);
        $patient->whereIn('ipid', $ipids_arr);
        $patient->limit($limit);
        if ($dischargedata)
        {
    
            $patient->orderBy('FIELD(p__ipid, ' . substr($orderbydischarge_str, 0, -1) . '), p__ipid');
        }
    
        $patients_ids_arr = $patient->fetchArray();
    
        $ipid_str = '"99999999",';
        foreach ($patients_ids_arr as $ipid)
        {
            $ipid_str .= '"' . $ipid['ipid'] . '",';
        }
        $ipid_str = substr($ipid_str, 0, -1);
    
        //get patientlives data(wohnsituation)
        $pl = new PatientLives();
        $pat_lives = $pl->getpatientLivesData($ipid_arr);
    
        $this->view->alone = $pat_lives[0]['alone'];
        $this->view->house_of_relatives = $pat_lives[0]['house_of_relatives'];
        $this->view->home = $pat_lives[0]['home'];
    
        //get first and last kvno data
        $patient_k_first = Doctrine_Query::create()
        ->select('*')
        ->from('DgpKern ka')
        ->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid and  kb.form_type = "adm" and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` ASC	LIMIT 1 )')
        ->groupBy('ka.ipid')
        ->orderby('id asc');
    
        $p_kvno_first = $patient_k_first->fetchArray();
    
        $patient_k_last = Doctrine_Query::create()
        ->select('*')
        ->from('DgpKern ka')
        ->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid and  kb.form_type = "dis"  and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` DESC	LIMIT 1 )')
        ->groupBy('ka.ipid')
        ->orderby('id asc');
    
        $p_kvno_last = $patient_k_last->fetchArray();
    
        foreach ($p_kvno_first as $k_first => $v_first)
        {
            $patient_kvno_first[$v_first['ipid']] = $v_first;
        }
    
        foreach ($p_kvno_last as $k_last => $v_last)
        {
            if ($patient_kvno_first[$v_last['ipid']]['id'] != $v_last['id'])
            {
                $patient_kvno_last[$v_last['ipid']] = $v_last;
            }
        }
    
        //get contactperson_master data
        $contact_pm = Doctrine_Query::create()
        ->select('cpm.ipid, SUM(cpm.cnt_hatversorgungsvollmacht) as versorgung_max')
        ->from('ContactPersonMaster cpm')
        ->andwhere('cpm.ipid IN (' . $ipid_str . ')')
        ->groupBy('cpm.ipid')
        ->orderBy('cnt_hatversorgungsvollmacht ASC');
    
        $contact_data = $contact_pm->fetchArray();
        foreach ($contact_data as $k_contact => $v_contact)
        {
            $contact_patient_data[$v_contact['ipid']] = $v_contact;
        }
 
    
        //get discharge locations
        $dl = new DischargeLocation();
        $discharge_locations = $dl->getDischargeLocation($clientid, 0);
        foreach ($discharge_locations as $k_disloc => $v_disloc)
        {
            $discharge_loc[$v_disloc['id']] = $v_disloc;
        }
        
        // ACP
        
        $acp = new PatientAcp();
        $acp_data_patients = $acp->getByIpid($ipids_arr);
        
        if(!empty($acp_data_patients))
        {
        	foreach($acp_data_patients as $ipid=>$acp_data)
        	{
        		foreach($acp_data as $k=>$block)
        		{
        			if($block['division_tab'] == "living_will"){
        
        				if($block['active'] == "yes"){
        					$export_data[$ipid]['BL_Patientenverfuegung'] = '1';
        				} else{
        					$export_data[$ipid]['BL_Patientenverfuegung'] = '-1';
        				}
        
        			}
        			elseif($block['division_tab'] == "healthcare_proxy")
        			{
        				if($block['active'] == "yes"){
        					$export_data[$ipid]['BL_Vorsorgevollmacht'] = '1';
        				} else{
        					$export_data[$ipid]['BL_Vorsorgevollmacht'] = '-1';
        				}
        
        			}
        			elseif($block['division_tab'] == "care_orders")
        			{
        				if($block['active'] == "yes"){
        					$export_data[$ipid]['BL_Betreuungsurkunde'] = '1';
        				} else{
        					$export_data[$ipid]['BL_Betreuungsurkunde'] = '-1';
        				}
        
        			}
        		}
        	}
        }
        
        //process patient data
        foreach ($patients_ids_arr as $k_pat => $v_pat)
        {
        	$tod_date_patient = '';
        	if(array_key_exists($v_pat['ipid'], $patients_tod_date))
        	{
        		$tod_date_patient = $patients_tod_date[$v_pat['ipid']];
        	}
        	else
        	{
        		$tod_date_patient = date("Y-m-d", time());
        	}
        	$final_patient_data[$v_pat['ipid']]['age'] = $patientmaster->GetAge($v_pat['birthd'], $tod_date_patient, true);
        }
        foreach ($patients_ids_arr as $k_pat => $v_pat)
        {
            $export_data[$v_pat['ipid']]['B_Programm'] = 'ISPC';
            $export_data[$v_pat['ipid']]['B_Pat_ID'] = strtoupper($v_pat['epid']);
            $export_data[$v_pat['ipid']]['B_Dat_ID'] = strtoupper($v_pat['epid']);
//             $export_data[$v_pat['ipid']]['B_geb_datum'] = date('Y-m', strtotime($v_pat['birthd']));
            $export_data[$v_pat['ipid']]['B_XML_date'] = date('Y-m-d', time());
            $export_data[$v_pat['ipid']]['B_Alter'] = $final_patient_data[$v_pat['ipid']]['age'];
            $export_data[$v_pat['ipid']]['B_auf_datum'] = date('Y-m-d', strtotime($v_pat['admission_date']));
            
            //B_geschlecht
            // -1 = Keine Angabe;
            // 1 = 1 - weiblich;
            // 2 = 2 - männlich
            if (!empty($v_pat['sex']))
            {
                $sex_map = array ('1' => '2', '2' => '1');
                $gender = $sex_map[$v_pat['sex']];
            }
            else
            {
                $gender = '-1';
            }
    
            $export_data[$v_pat['ipid']]['B_geschlecht'] = $gender;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
        }
    
        //		get various nonkvno saved data
        //		get Haupt Diagnosis diagnosis
        $dg = new DiagnosisType();
        $abb2 = "'HD'";
        $ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid, $abb2);
        $comma = ",";
        $typeid = "'0'";
        foreach ($ddarr2 as $key => $valdia)
        {
            $typeid .=$comma . "'" . $valdia['id'] . "'";
            $comma = ",";
        }
    
        $patdia = new PatientDiagnosis();
        $dianoarray = $patdia->getFinalData($ipid_str, $typeid, true); //set last param true to accept a list of ipids
    
        if (count($dianoarray) > 0)
        {
            foreach ($dianoarray as $key => $valdia)
            {
                if (!empty($valdia['diagnosis']) && !empty($valdia['icdnumber']))
                {
                    $export_data[$valdia['ipid']]['BA_ICD_haupt'] = $valdia['icdnumber'];
                    $export_data[$valdia['ipid']]['BL_ICD_haupt'] = $valdia['icdnumber'];
                }
            }
        }
    
    
        $wohn_mapping = array ('0' => '-1', '1' => '1', '2' => '3', '4' => '2', '6' => '4','5'=>'5');
    
        
        //prepare export first kvno data
        foreach ($patient_kvno_first as $k_pkvno => $v_pkvno)
        {
    
            //BA_wohnsituation 
            // -1=Keine Angabe; 
            // 1 =1 - allein;
            // 2 =2 - Heim; 
            // 3 =3 - mit Angehörigen;
            // 4 =4 - Sonstige
            
        	//BA_wohnsituation
        	//1	 Allein zu Hause lebend
        	//2	 Im (Pflege-/Alten-)Heim
        	//3	 mit Angehörigen oder anderen privaten Bezugspersonen im Haushalt
        	//4	 Sonstige
        	//5	 stationäres Hospiz
        	//-1 not applicable
        	
        	
            if (!empty($v_pkvno['wohnsituations']))
            {
                $wohn = $wohn_mapping[$v_pkvno['wohnsituations']];
            }
            else
            {
                $wohn = '-1';
            }
    
            $export_data[$v_pkvno['ipid']]['BA_wohnsituation'] = $wohn;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

            // BA_ecog
            // -1=Keine Angabe;
            // 0=0 - Normale Aktivitat;
            // 1=1 - Gehfahig, leichte Aktivitat möglich;
            // 2=2 - Nicht arbeitsfahig, kann > 50% der Wachzeit aufstehen;
            // 3=3 - Begrenzte Selbstversorgung, >50% Wachzeit bettlagerig;
            // 4=4 - Pflegebedurftig, permanent bettlagerig
            if (!empty($v_pkvno['ecog']))
            {
                $ecog_value = ($v_pkvno['ecog'] - 1);
            }
            else
            {
                $ecog_value = '-1';
            }
    
            $export_data[$v_pkvno['ipid']]['BA_ecog'] = $ecog_value;
            
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            //BA_Pverfuegung
            if ($v_pkvno['pverfuegung'] == "1")
            {
            	$export_data[$v_pkvno['ipid']]['BA_Patientenverfuegung'] = '1';
            }
            else
            {
            	$export_data[$v_pkvno['ipid']]['BA_Patientenverfuegung'] = '-1';
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            	
            //BA_vollmacht
            if ($v_pkvno['vollmacht'] == "1")
            {
            	$export_data[$v_pkvno['ipid']]['BA_Vorsorgevollmacht'] = '1';
            }
            else
            {
            	$export_data[$v_pkvno['ipid']]['BA_Vorsorgevollmacht'] = '-1';
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            //BA_Betreuungsurkunde
            if ($v_pkvno['betreuungsurkunde'] == "1")
            {
            	$export_data[$v_pkvno['ipid']]['BA_Betreuungsurkunde'] = '1';
            }
            else
            {
            	$export_data[$v_pkvno['ipid']]['BA_Betreuungsurkunde'] = '-1';
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            //BA_ACP
            if ($v_pkvno['acp'] == "1")
            {
            	$export_data[$v_pkvno['ipid']]['BA_ACP'] = '1';
            }
            else
            {
            	$export_data[$v_pkvno['ipid']]['BA_ACP'] = '-1';
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            		
            		
            
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            // BA_datum
            // Datum der Erfassung und Dokumentation|Datumsfeld (jjjj-mm-tt)
            if (!empty($v_pkvno['datum_der_erfassung1']) && $v_pkvno['datum_der_erfassung1'] != '1970-01-01 00:00:00' && $v_pkvno['datum_der_erfassung1'] != '0000-00-00 00:00:00')
            {
//                 $export_data[$v_pkvno['ipid']]['BA_datum'] = date('Y-m-d', strtotime($v_pkvno['datum_der_erfassung1']));
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

            // =============================================== //
            //this part is repeating with BA and BL prefix
            // =============================================== //
    
            // BA_Hausarzt
            // 1 = ausgewählt 
            // -1 = nicht ausgewählt
            if (!empty($v_pkvno['begleitung']))
            {
                $begleitung = explode(',', $v_pkvno['begleitung']);
            }
            
            $export_data[$v_pkvno['ipid']]['BA_KH_Palliativstation'] = (in_array('8', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Hospiz_stationaer'] = (in_array('9', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Krankenhaus_Andere_Station'] = (in_array('10', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Heim'] = (in_array('11', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Hausarzt'] = (in_array('2', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Ambulante_Pflege'] = (in_array('3', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Palliativarzt'] = (in_array('4', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Palliativpflege'] = (in_array('5', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Palliativberatung'] = (in_array('14', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Ehrenamtlicher_Dienst'] = (in_array('7', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Palliative_Care_Team'] = (in_array('12', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_MVZ'] = (in_array('15', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_KH_Palliativdienst'] = (in_array('16', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_sonstige_behandlung'] = (in_array('17', $begleitung)) ? '1' : '-1';
            
            	
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            //BA_Schmerzen
            // -1=Keine Angabe;
            // 0=0 - kein;
            // 1=1 - leicht;
            // 2=2 - mittel;
            // 3=3 - stark
            $export_data[$v_pkvno['ipid']]['BA_Schmerzen'] = (!empty($v_pkvno['schmerzen'])) ? ($v_pkvno['schmerzen'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Ubelkeit'] = (!empty($v_pkvno['ubelkeit'])) ? ($v_pkvno['ubelkeit'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Erbrechen'] = (!empty($v_pkvno['erbrechen'])) ? ($v_pkvno['erbrechen'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Luftnot'] = (!empty($v_pkvno['luftnot'])) ? ($v_pkvno['luftnot'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Verstopfung'] = (!empty($v_pkvno['verstopfung'])) ? ($v_pkvno['verstopfung'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Schwache'] = (!empty($v_pkvno['swache'])) ? ($v_pkvno['swache'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Appetitmangel'] = (!empty($v_pkvno['appetitmangel'])) ? ($v_pkvno['appetitmangel'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Mudigkeit'] = (!empty($v_pkvno['mudigkeit'])) ? ($v_pkvno['mudigkeit'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Wunden'] = (!empty($v_pkvno['dekubitus'])) ? ($v_pkvno['dekubitus'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Hilfe_ATL'] = (!empty($v_pkvno['hilfebedarf'])) ? ($v_pkvno['hilfebedarf'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Depressivitat'] = (!empty($v_pkvno['depresiv'])) ? ($v_pkvno['depresiv'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Angst'] = (!empty($v_pkvno['angst'])) ? ($v_pkvno['angst'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Anspannung'] = (!empty($v_pkvno['anspannung'])) ? ($v_pkvno['anspannung'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Verwirrtheit'] = (!empty($v_pkvno['desorientier'])) ? ($v_pkvno['desorientier'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Versorgungsorg'] = (!empty($v_pkvno['versorgung'])) ? ($v_pkvno['versorgung'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Uberforderung'] = (!empty($v_pkvno['umfelds'])) ? ($v_pkvno['umfelds'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_sonstige_probleme'] = (!empty($v_pkvno['sonstige_probleme'])) ? ($v_pkvno['sonstige_probleme'] - 1) : '-1';
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
        }
    
        foreach ($patient_kvno_last as $k_pkvno_last => $v_pkvno_last)
        {
    
            //BL_wohnsituation
            // -1=Keine Angabe;
            // 1=1 - allein;
            // 2=2 - Heim;
            // 3=3 - mit Angehörigen;
            // 4=4 - Sonstige
            
        	//-1 = Keine Angabe; 
        	//1 = Allein zu Hause lebend ;
        	//2 = Im (Pflege-/Senioren-)Heim; 
        	//3 = mit Angehörigen oder anderen privaten Bezugspersonen im Haushalt; 
        	//4 = Sonstige; 
        	//5 = stationäres Hospiz
            if (!empty($v_pkvno_last['wohnsituations']))
            {
                $wohn = $wohn_mapping[$v_pkvno_last['wohnsituations']];
            }
            else
            {
                $wohn = '-1';
            }
            $export_data[$v_pkvno_last['ipid']]['BL_wohnsituation'] = $wohn;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            
            // BL_datum
            // 13. Datum der Erfassung und Dokumentation|Datumsfeld (jjjj-mm-tt)
            if (!empty($v_pkvno_last['datum_der_erfassung1']) && $v_pkvno_last['datum_der_erfassung1'] != '1970-01-01 00:00:00' && $v_pkvno_last['datum_der_erfassung1'] != '0000-00-00 00:00:00')
            {
//                 $export_data[$v_pkvno_last['ipid']]['BL_datum'] = date('Y-m-d', strtotime($v_pkvno_last['datum_der_erfassung1']));
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            
            //BL_ecog
            // -1=Keine Angabe;
            // 0=0 - Normale Aktivitat;
            // 1=1 - Gehfahig, leichte Aktivitat möglich;
            // 2=2 - Nicht arbeitsfahig, kann > 50% der Wachzeit aufstehen;
            // 3=3 - Begrenzte Selbstversorgung, >50% Wachzeit bettlagerig;
            // 4=4 - Pflegebedurftig, permanent bettlagerig
            if (!empty($v_pkvno_last['ecog']))
            {
                $ecog_value = ($v_pkvno_last['ecog'] - 1);
            }
            else
            {
                $ecog_value = '-1';
            }
    
            $export_data[$v_pkvno_last['ipid']]['BL_ecog'] = $ecog_value;
            

            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            //BL_Pverfuegung
            if ($v_pkvno_last['pverfuegung'] == "1")
            {
            	$export_data[$v_pkvno_last['ipid']]['BL_Patientenverfuegung'] = '1';
            }
            else
            {
            	$export_data[$v_pkvno_last['ipid']]['BL_Patientenverfuegung'] = '-1';
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            //BL_vollmacht
            if ($v_pkvno_last['vollmacht'] == "1")
            {
            	$export_data[$v_pkvno_last['ipid']]['BL_Vorsorgevollmacht'] = '1';
            }
            else
            {
            	$export_data[$v_pkvno_last['ipid']]['BL_Vorsorgevollmacht'] = '-1';
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            //BL_Betreuungsurkunde
            if ($v_pkvno_last['betreuungsurkunde'] == "1")
            {
            	$export_data[$v_pkvno_last['ipid']]['BL_Betreuungsurkunde'] = '1';
            }
            else
            {
            	$export_data[$v_pkvno_last['ipid']]['BL_Betreuungsurkunde'] = '-1';
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            //BL_ACP
            if ($v_pkvno_last['acp'] == "1")
            {
            	$export_data[$v_pkvno_last['ipid']]['BL_ACP'] = '1';
            }
            else
            {
            	$export_data[$v_pkvno_last['ipid']]['BL_ACP'] = '-1';
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            			
            			
            
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            //BL_Hausarzt
            // 1 = ausgewählt 
            // -1 = nicht ausgewählt
            if (!empty($v_pkvno_last['begleitung']))
            {
                $begleitung_last = explode(',', $v_pkvno_last['begleitung']);
            }
            $export_data[$v_pkvno_last['ipid']]['BA_KH_Palliativstation'] = (in_array('8', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_Hospiz_stationaer'] = (in_array('9', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_Krankenhaus_Andere_Station'] = (in_array('10', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_Heim'] = (in_array('11', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_Hausarzt'] = (in_array('2', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_Ambulante_Pflege'] = (in_array('3', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_Palliativarzt'] = (in_array('4', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_Palliativpflege'] = (in_array('5', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_Palliativberatung'] = (in_array('14', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_Ehrenamtlicher_Dienst'] = (in_array('7', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_Palliative_Care_Team'] = (in_array('12', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_MVZ'] = (in_array('15', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_KH_Palliativdienst'] = (in_array('16', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BA_sonstige_behandlung'] = (in_array('17', $begleitung_last)) ? '1' : '-1';
            
            
            
            
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            // BL_Schmerzen
            // -1=Keine Angabe;
            // 0=0 - kein;
            // 1=1 - leicht;
            // 2=2 - mittel;
            // 3=3 - stark
            $export_data[$v_pkvno_last['ipid']]['BL_Schmerzen'] = (!empty($v_pkvno_last['schmerzen'])) ? ($v_pkvno_last['schmerzen'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Ubelkeit'] = (!empty($v_pkvno_last['ubelkeit'])) ? ($v_pkvno_last['ubelkeit'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Erbrechen'] = (!empty($v_pkvno_last['erbrechen'])) ? ($v_pkvno_last['erbrechen'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Luftnot'] = (!empty($v_pkvno_last['luftnot'])) ? ($v_pkvno_last['luftnot'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Verstopfung'] = (!empty($v_pkvno_last['verstopfung'])) ? ($v_pkvno_last['verstopfung'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Schwache'] = (!empty($v_pkvno_last['swache'])) ? ($v_pkvno_last['swache'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Appetitmangel'] = (!empty($v_pkvno_last['appetitmangel'])) ? ($v_pkvno_last['appetitmangel'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Mudigkeit'] = (!empty($v_pkvno_last['mudigkeit'])) ? ($v_pkvno_last['mudigkeit'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Wunden'] = (!empty($v_pkvno_last['dekubitus'])) ? ($v_pkvno_last['dekubitus'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Hilfe_ATL'] = (!empty($v_pkvno_last['hilfebedarf'])) ? ($v_pkvno_last['hilfebedarf'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Depressivitat'] = (!empty($v_pkvno_last['depresiv'])) ? ($v_pkvno_last['depresiv'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Angst'] = (!empty($v_pkvno_last['angst'])) ? ($v_pkvno_last['angst'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Anspannung'] = (!empty($v_pkvno_last['anspannung'])) ? ($v_pkvno_last['anspannung'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Verwirrtheit'] = (!empty($v_pkvno_last['desorientier'])) ? ($v_pkvno_last['desorientier'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Versorgungsorg'] = (!empty($v_pkvno_last['versorgung'])) ? ($v_pkvno_last['versorgung'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Uberforderung'] = (!empty($v_pkvno_last['umfelds'])) ? ($v_pkvno_last['umfelds'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_sonstige_probleme'] = (!empty($v_pkvno_last['sonstige_probleme'])) ? ($v_pkvno_last['sonstige_probleme'] - 1) : '-1';
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            // BL_Opioide_WHO_Stufe_3
            // 1 = ausgewählt 
            // -1 = nicht ausgewählt
            if ($v_pkvno_last['who'] == '1')
            {
                $opioids = '1';
            }
            else
            {
                $opioids = '-1';
            }
    
//             $export_data[$v_pkvno_last['ipid']]['BL_Opioide_WHO_Stufe_3'] = $opioids;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            // BL_Kortikosteroide
            // 1 = ausgewählt
            // -1 = nicht ausgewählt
            if ($v_pkvno_last['steroide'] == '1')
            {
                $steroide = '1';
            }
            else
            {
                $steroide = '-1';
            }
    
//             $export_data[$v_pkvno_last['ipid']]['BL_Kortikosteroide'] = $steroide;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            //BL_Chemotherapie
            // -1=Keine Angabe;
            // 0=0 = nein;
            // 1=1 = fortgesetzt;
            // 2=2 = initiiert
            $export_data[$v_pkvno_last['ipid']]['BL_Chemotherapie'] = ($v_pkvno_last['chemotherapie'] - 1);
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            // BL_Strahlentherapie
            // -1=Keine Angabe; 
            // 0=0 = nein;
            // 1=1 = fortgesetzt;
            // 2=2 = initiiert
            $export_data[$v_pkvno_last['ipid']]['BL_Strahlentherapie'] = ($v_pkvno_last['strahlentherapie'] - 1);
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            // BL_Aufwand
            // text
            if (!empty($v_pkvno_last['aufwand_mit']))
            {
                $export_data[$v_pkvno_last['ipid']]['BL_Aufwand'] = $v_pkvno_last['aufwand_mit'];
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            // BL_problem_1 
            // text
            if(!empty($v_pkvno_last['problem_besonders']))
            {
                $export_data[$v_pkvno_last['ipid']]['BL_problem_1'] = $v_pkvno_last['problem_besonders'];
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            
            //BL_problem_2
            // text
            if(!empty($v_pkvno_last['problem_ausreichend']))
            {
                $export_data[$v_pkvno_last['ipid']]['BL_problem_2'] = $v_pkvno_last['problem_ausreichend'];
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            //BL_bedarf
            // text
            if(!empty($v_pkvno_last['bedarf']))
            {
            	$export_data[$v_pkvno_last['ipid']]['BL_Bedarf'] = $v_pkvno_last['bedarf'];
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            	
            	
            //BL_massnahmen
            // text
            if(!empty($v_pkvno_last['massnahmen']))
            {
            	$export_data[$v_pkvno_last['ipid']]['BL_Massnahmen'] = $v_pkvno_last['massnahmen'];
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            $med_referance_value = array();
            foreach( $dgp['texts']['medication_references_all'] as $k=>$mr_data){

            	if ($v_pkvno_last[$mr_data['code']] == '1')
            	{
            		$med_referance_value[$mr_data['code']]  = '1';
            	}
            	else
            	{
            		$med_referance_value[$mr_data['code']]= '-1';
            	}
            	$export_data[$v_pkvno_last['ipid']][$mr_data['dgp_export']] == $med_referance_value[$mr_data['code']];
            }
            
            
//             $export_data[$v_pkvno_last['ipid']]['BL_Analgetika'] = (in_array('1', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Opioide_WHO_Stufe_2'] = (in_array('2', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Opioide_WHO_Stufe_3'] = (in_array('3', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Nicht_Opioide'] = (in_array('4', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Co_Analgetika'] = (in_array('5', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Steroide'] = (in_array('6', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Anxiolytika'] = (in_array('7', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Laxantien'] = (in_array('8', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Sedativa'] = (in_array('9', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Neuroleptika'] = (in_array('10', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Anti_Epileptika'] = (in_array('11', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Anti_Emetika'] = (in_array('12', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Anti_Biotika'] = (in_array('13', $medication_references)) ? '1' : '-1';
//             $export_data[$v_pkvno_last['ipid']]['BL_Magenschutz'] = (in_array('14', $medication_references)) ? '1' : '-1';
            
            
            
            //B_datum_ende
            // jjjj-mm-tt
            // Entlassung / Anderung der Betreuung / Tod
            if(strlen($v_pkvno_last['entlasung_date'])){
                $export_data[$v_pkvno_last['ipid']]['B_datum_ende'] = date('Y-m-d', strtotime($v_pkvno_last['entlasung_date']));
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            // B_thera_ende
            // -1=Keine Angabe;
            // 1=1 - Verstorben;
            // 2=2 - Verlegung, Entlassung;
            // 4=4 - Sonstiges
            if ($v_pkvno_last['therapieende'] == '0' || empty($v_pkvno_last['therapieende']))
            {
                $thera_ende = "-1";
            }
            else
            {
                $thera_ende_mapping = array('0'=>'-1', '1'=>'1', '2'=>'2', '3'=>'2','4'=>'4'); //thera ende has no "3" value in xsd...weird
                $thera_ende = $thera_ende_mapping[$v_pkvno_last['therapieende']];
            }
    
            $export_data[$v_pkvno_last['ipid']]['B_thera_ende'] = $thera_ende;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            //B_sterbeort_0
            //XSD V 3.0
            //23. Sterbeort | -1=Keine Angabe; 1=zuhause; 2=Heim; 3=Hospiz; 4=Palliativstation; 5=Krankenhaus; 6=unbekannt; 7=Sonstiges
            
            $sterbeort = '-1';//Keine Angabe
            if (empty($v_pkvno_last['sterbeort_dgp']) || $v_pkvno_last['sterbeort_dgp'] == '0')
            {
            	$sterbeort = '-1';//Keine Angabe
            }
            else
            {
            	//SYSTEM Loc type ids       -> XSD values
            	//1 = zu Hause              -> XSD : 1
            	//2 = KH                    -> XSD : 5
            	//3 = Hospiz                -> XSD : 3
            	//4 = Altenheim, Pflegeheim -> XSD : 2
            	//5 = Palliativ             -> XSD : 4
            	//6 = bei Kontaktperson     -> XSD : 6 // unknown in register
            	//7 = Kurzzeitpflege        -> XSD : 6 // unknown in register
            	//8 = betreutes Wohnen      -> XSD : 6 // unknown in register
            	//other				        -> XSD : 7 // sonstiges
            	 
            	$location_type_map = array (
            	'1' => '1', //zu Hause
            	'2' => '5', //Krankenhaus
            	'3' => '3', //Hospiz
            	'4' => '2', //Heim
            	'5' => '4', //Palliativstation
            	'6' => '6', //bei Kontaktperson
            	'7' => '6', //Kurzzeitpflege
            	'8' => '6'  //betreutes Wohnen
            	);
            		
            	if ($discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type'] != 0 && strlen($location_type_map[$discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type']]) > 0 )
            	{
            		$sterbeort = $location_type_map[$discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type']];
            	}
            	else
            	{
            		//no location type or other(sonstiges...)
            		$sterbeort = '7';
            	}
            }
            	
            $export_data[$v_pkvno_last['ipid']]['B_sterbeort_0'] = $sterbeort;
            
            
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            
            //   B_bewertung
            //  -1=Keine Angabe;
            //   1=1 - sehr gut;
            //   2=2 - gut;
            //   3=3 - mittel;
            //   4=4 - schlecht;
            //   5=5 - sehr schlecht
            $satisfaction_map = array ('0'=>'-1','1' => '5', '2' => '4', '3' => '3', '4' => '2', '5' => '1');
            
            if (empty($v_pkvno_last['zufriedenheit_mit']))
            {
                $satisfaction = '-1';
            }
            else
            {
                $satisfaction = $satisfaction_map [$v_pkvno_last['zufriedenheit_mit']];
            }
            $export_data[$v_pkvno_last['ipid']]['B_bewertung'] = $satisfaction;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
        } //end last kvno foreach
        
        
        if (!empty($_POST)){
        	
            // #############
            // SEND KERNE PART
            // #############
            
            //get KERNE xml
            $kern_schema = '<alle xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="KERN_3.1.xsd"></alle>';
            $xml = $this->toXml($export_data, $kern_schema, null, 'KERN');

            //echo $xml;
            if (class_exists('DOMDocument'))
            {
                $doc = new DOMDocument();
                $dom_xml = $doc->loadXML($xml);
                $source_path = APP_BASE . 'xsd/KERN_3.1.xsd';
                $validation = $doc->schemaValidate($source_path);
            }
            else
            {
                $validation = true; //sskip validation if DOMDocument is not installed
            }

            $validation = true;
            if ($validation)
            {
                $xml_type = 'KERN';
                $xml_string = $this->xmlpp($xml, false);
                //$xml_string = $xml;

                //send xml via post
                $response = $this->data_send($clientid, $xml_string, $xml_type);
        
                $history_id = $this->history_save($userid, $clientid, $xml_string, $response);
        
                if (substr($response, 0, 4) == '1000') //1000 is ok RC
                {
                    // save patients - for this upload
                    if($history_id ){
                        foreach($export_data as $pipid => $ex_data){
                            $exported_ipids[]= $pipid ;
                        }
                        $this->history_patients_save($history_id,$exported_ipids, $userid, $clientid);
                         
                    }
                    // redir success
                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=suc&c=".substr($response, 0, 4));
                    $kern_send_status = "suc"; 
                    $kern_msg = substr($response, 0, 4);
                }
                else if(substr($response, 0, 4) == '2000')
                {
                    // redir error auth error
                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                    $kern_send_status = "err";
                    $kern_msg = substr($response, 0, 4);
                }
                else if(substr($response, 0, 4) == '0000')
                {
                    // curl error
                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                    $kern_send_status = "err";
                    $kern_msg = substr($response, 0, 4);
                }
                else if(substr($response, 0, 4) == '3100')
                {
                    // XML error
                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                    $kern_send_status = "err";
                    $kern_msg = substr($response, 0, 4);
                }
                else
                {
                    // redir generic error
                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err");
                    $kern_send_status = "err";
                    $kern_msg = "generic error";
                }
            }
            else
            {
                // validation error
                // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=9999");
                $this->history_save($userid, $clientid, $xml, 'NULL');
                $kern_send_status = "error";
                $kern_msg = "9999";
            }
            
//             $this->_redirect(APP_BASE . "dgp/dgpfullpatientlist?ks=".$kern_send_status."&km=".$kern_msg."&ss=".$sapv_send_status."&sm=".$sapv_msg);
            $this->_redirect(APP_BASE . "dgp/dgpfullpatientlist?ks=".$kern_send_status."&km=".$kern_msg);
        }
 
    }

    
    /**
     * @cla on 10.06.2018 : do not use
     * @deprecated
     */
    function dgpnewexportv2Action()//NOT USED
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $previleges = new Pms_Acl_Assertion();
        $return = $previleges->checkPrevilege('dgplist', $userid, 'canview');
    
        //save DGP credentials
    
      /*   if(!empty($_POST['dgp_user']) && !empty($_POST['dgp_pass'])) {
            $cust = Doctrine::getTable('Client')->find($clientid);
            $cust->dgp_user = Pms_CommonData::aesEncrypt($_POST['dgp_user']);
            $cust->dgp_pass = Pms_CommonData::aesEncrypt($_POST['dgp_pass']);
            $cust->save();
        }
     */
        
        /* ISPC-1775,ISPC-1678 */
        
        //process submited patients
        $ipids_arr[] = '999999';
        foreach($_POST['dgp_patients'] as $k_pat => $v_pat)
        {
            $ipids_arr[] = Pms_CommonData::getIpid(Pms_Uuid::decrypt($v_pat));
        }
        
        //		1.get client current discharge methods(all)
        $discharge_methods = Doctrine_Query::create()
        ->select("*")
        ->from('DischargeMethod')
        ->where("isdelete = 0  and clientid=" . $clientid . "");
    
        $c_discharged_methods = $discharge_methods->fetchArray();
    
        $client_discharge_methods[] = '999999999';
        foreach ($c_discharged_methods as $k_method => $v_method)
        {
            $client_discharge_methods[] = $v_method['id'];
        }
    
        //		2.use discharge methods to get last 40 discharged patient of current client
        $last_discharges = Doctrine_Query::create()
        ->select('*')
        ->from('PatientDischarge')
        ->where('isdelete = 0')
        ->andWhereIn('discharge_method', $client_discharge_methods)
        ->orderBy('discharge_date DESC');
    
        $last_discharged_patients = $last_discharges->fetchArray();
    
        $discharged_ipids[] = '999999999';
        foreach ($last_discharged_patients as $k_dis_patient => $v_dis_patient)
        {
            $discharged_ipids[] = $v_dis_patient['ipid'];
        }
    
        $patient = Doctrine_Query::create()
        ->select('*, p.ipid, p.admission_date, p.birthd, CONVERT(AES_DECRYPT(sex,"' . Zend_Registry::get('salt') . '") using latin1)  as gensex, e.epid, p.living_will as living_will')
        ->from('PatientMaster p')
        ->leftJoin("p.EpidIpidMapping e")
//         ->where("p.isdischarged = 1 and p.isarchived=0 and p.isdelete = 0 and p.isstandbydelete=0")
        ->where("p.isdelete = 0 and p.isstandbydelete=0")
//         ->andwhereIn('p.ipid', $discharged_ipids)
        ->andwhereIn('p.ipid', $ipids_arr);
        $patient->andWhere('e.ipid = p.ipid');
        $patient->andWhere('e.clientid = ' . $clientid);
        $patienidtarray = $patient->fetchArray();
    
    
        if (count($patienidtarray) == 0)
        {
            $patienidtarray[0] = "1";
        }
    
        $patientarray[0]['count'] = sizeof($patienidtarray);
    
        $ipid_arr[] = '999999999';
        $ipidz_simple[] = '99999999999';
    
        foreach ($patienidtarray as $ipid)
        {
            $ipid_arr[] = $ipid['ipid'];
    
            $ipidz_simple[] = $ipid['ipid'];
        }
    
        $disdata = new PatientDischarge();
        $dischargedata = $disdata->getPatientsDischargeDetails($patienidtarray, 'discharge_date', 'DESC');
    
        foreach ($dischargedata as $discharge_key => $discharge_item)
        {
            $orderbydischarge_str .= '"' . $discharge_key . '",';
        }
    
        $limit = 30;
        $sql = "ipid,e.epid as epid,birthd,admission_date,change_date,last_update,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
        $sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
        $sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
        $sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
        $sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
        $sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
        $sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
        $sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
        $sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
        $sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
        $sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
        $sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";
    
        $patient->select($sql);
        $patient->whereIn('ipid', $ipids_arr);
        $patient->limit($limit);
        if ($dischargedata)
        {
    
            $patient->orderBy('FIELD(p__ipid, ' . substr($orderbydischarge_str, 0, -1) . '), p__ipid');
        }
    
        $patients_ids_arr = $patient->fetchArray();
    
        $ipid_str = '"99999999",';
        foreach ($patients_ids_arr as $ipid)
        {
            $ipid_str .= '"' . $ipid['ipid'] . '",';
        }
        $ipid_str = substr($ipid_str, 0, -1);
    
        //get patientlives data(wohnsituation)
        $pl = new PatientLives();
        $pat_lives = $pl->getpatientLivesData($ipid_arr);
    
        $this->view->alone = $pat_lives[0]['alone'];
        $this->view->house_of_relatives = $pat_lives[0]['house_of_relatives'];
        $this->view->home = $pat_lives[0]['home'];
    
        //get first and last kvno data
        $patient_k_first = Doctrine_Query::create()
        ->select('*')
        ->from('DgpKern ka')
        ->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid and  kb.form_type = "adm" and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` ASC	LIMIT 1 )')
        ->groupBy('ka.ipid')
        ->orderby('id asc');
    
        $p_kvno_first = $patient_k_first->fetchArray();
    
        $patient_k_last = Doctrine_Query::create()
        ->select('*')
        ->from('DgpKern ka')
        ->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid and  kb.form_type = "dis"  and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` DESC	LIMIT 1 )')
        ->groupBy('ka.ipid')
        ->orderby('id asc');
    
        $p_kvno_last = $patient_k_last->fetchArray();
    
        foreach ($p_kvno_first as $k_first => $v_first)
        {
            $patient_kvno_first[$v_first['ipid']] = $v_first;
        }
    
        foreach ($p_kvno_last as $k_last => $v_last)
        {
            if ($patient_kvno_first[$v_last['ipid']]['id'] != $v_last['id'])
            {
                $patient_kvno_last[$v_last['ipid']] = $v_last;
            }
        }
    
        //get contactperson_master data
        $contact_pm = Doctrine_Query::create()
        ->select('cpm.ipid, SUM(cpm.cnt_hatversorgungsvollmacht) as versorgung_max')
        ->from('ContactPersonMaster cpm')
        ->andwhere('cpm.ipid IN (' . $ipid_str . ')')
        ->groupBy('cpm.ipid')
        ->orderBy('cnt_hatversorgungsvollmacht ASC');
    
        $contact_data = $contact_pm->fetchArray();
        foreach ($contact_data as $k_contact => $v_contact)
        {
            $contact_patient_data[$v_contact['ipid']] = $v_contact;
        }
    
        //get all patients sapvs
        $sapv = new SapvVerordnung();
        $sapvarray = $sapv->getPatientsSapvVerordnungDetails($ipid_arr, true);
    
        $patient_sapv_forms = Doctrine_Query::create()
        ->select('*')
        ->from('DgpSapv ds')
        ->where('ds.ipid in (' . $ipid_str . ')')
        ->orderby('ds.id asc');
    
        $patient_sapv_forms_filled = $patient_sapv_forms ->fetchArray();
    
        foreach($patient_sapv_forms_filled as $k_sapvf=>$v_sapvf)
        {
            $filled_sapvs[$v_sapvf['sapv']] = $v_sapvf;
        }
    
    
        //get discharge locations
        $dl = new DischargeLocation();
        $discharge_locations = $dl->getDischargeLocation($clientid, 0);
        foreach ($discharge_locations as $k_disloc => $v_disloc)
        {
            $discharge_loc[$v_disloc['id']] = $v_disloc;
        }
    
        //process patient data
        foreach ($patients_ids_arr as $k_pat => $v_pat)
        {
            $export_data[$v_pat['ipid']]['B_Programm'] = 'ISPC';
            $export_data[$v_pat['ipid']]['B_Pat_ID'] = strtoupper($v_pat['epid']);
            $export_data[$v_pat['ipid']]['B_Dat_ID'] = strtoupper($v_pat['epid']);
            $export_data[$v_pat['ipid']]['B_geb_datum'] = date('Y-m', strtotime($v_pat['birthd']));
            $export_data[$v_pat['ipid']]['B_auf_datum'] = date('Y-m-d', strtotime($v_pat['admission_date']));
            
            //B_geschlecht
            // -1 = Keine Angabe;
            // 1 = 1 - weiblich;
            // 2 = 2 - männlich
            if (!empty($v_pat['sex']))
            {
                $sex_map = array ('1' => '2', '2' => '1');
                $gender = $sex_map[$v_pat['sex']];
            }
            else
            {
                $gender = '-1';
            }
    
            $export_data[$v_pat['ipid']]['B_geschlecht'] = $gender;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

            // BA_Patientenverfuegung
            // 1 = ausgewählt 
            // -1 = nicht ausgewählt
            if (!empty($v_pat['living_will']))
            {
                if ($v_pat['living_will'] == '0')
                {
                    $living_will = "-1";
                }
                else if ($v_pat['living_will'] == '1')
                {
                    $living_will = $v_pat['living_will'];
                }
    
                if(!empty($patient_kvno_first[$v_pat['ipid']]))
                {
                    $export_data[$v_pat['ipid']]['BA_Patientenverfuegung'] = $living_will;
                }
    
                if(!empty($patient_kvno_last[$v_pat['ipid']]))
                {
                    $export_data[$v_pat['ipid']]['BL_Patientenverfuegung'] = $living_will;
                }
            }
            else
            {
                if(!empty($patient_kvno_first[$v_pat['ipid']]))
                {
                    $export_data[$v_pat['ipid']]['BA_Patientenverfuegung'] = '-1';
                }
    
                if(!empty($patient_kvno_last[$v_pat['ipid']]))
                {
                    $export_data[$v_pat['ipid']]['BL_Patientenverfuegung'] = '-1';
                }
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/            
            
            
            
            // versorgung from contact data master
            // BL_Vorsorgevollmacht
            // 1 = ausgewählt 
            // -1 = nicht ausgewählt
            
            if ($contact_patient_data[$v_pat['ipid']]['versorgung_max'] > 0 && !empty($contact_patient_data[$v_pat['ipid']]))
            {
                $versorgung = "1";
            }
            else
            {
                $versorgung = "-1";
            }
    
            if(!empty($patient_kvno_first[$v_pat['ipid']]))
            {
                $export_data[$v_pat['ipid']]['BA_Vorsorgevollmacht'] = $versorgung;
            }
    
            if(!empty($patient_kvno_last[$v_pat['ipid']]))
            {
                $export_data[$v_pat['ipid']]['BL_Vorsorgevollmacht'] = $versorgung;
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            
            //SAPV XML
            if ($sapvarray[$v_pat['ipid']])
            {
                foreach ($sapvarray[$v_pat['ipid']] as $k_sapv => $sapv_data)
                {
    
                    //if is filled
                    if (!empty($filled_sapvs[$sapv_data['id']]) && array_key_exists($sapv_data['id'], $filled_sapvs))
                    {
                        //@to do: here find a way to use keine
                        //
                        //minimal data set required to pass validation
                        $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Programm'] = 'ISPC';
                        $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Pat_ID'] = strtoupper($v_pat['epid']);
                        $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Dat_ID'] = $sapv_data['id'];
                        $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_vo_datum'] = date('Y-m-d', strtotime($sapv_data['verordnungam']));
                        $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_sapvdatum_beginn'] = date('Y-m-d', strtotime($sapv_data['verordnungam']));
                        $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Keine'] = '-1';
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

                        //get what kind of vv we have
                        $sapv_vv = explode(',', $sapv_data['verordnet']);
                        
                        //SAPV_Beratung
                        // 1 = ausgewählt 
                        // -1 = nicht ausgewählt
                        if (in_array('1', $sapv_vv))
                        {
                            $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Beratung'] = '1';
                        }
                        else
                        {
                            $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Beratung'] = '-1';
                        }
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
                        
                        // SAPV_additiv
                        // 1 = ausgewählt 
                        // -1 = nicht ausgewähl
                        if (in_array('3', $sapv_vv))
                        {
                            $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_additiv'] = '1';
                        }
                        else
                        {
                            $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_additiv'] = '-1';
                        }
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
                        
                        //SAPV_voll
                        // 1 = ausgewählt 
                        // -1 = nicht ausgewählt
                        if (in_array('4', $sapv_vv))
                        {
                            $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_voll'] = '1';
                        }
                        else
                        {
                            $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_voll'] = '-1';
                        }
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
    
                        //sapv xml for each saved sapv form
                        //SAPV_verordnung_durch
                        // - 1=Keine Angabe;
                        // 1 = Hausarzt;
                        // 2 = amb. Facharzt;
                        // 3 = Krankenhausarzt;
                        // 4 = andere
                        $verordnung_map = array ('1' => '1', '2' => '2', '3' => '4', '4' => '-1');
                        if (empty($filled_sapvs[$sapv_data['id']]['verordnung_durch']) || $filled_sapvs[$sapv_data['id']]['verordnung_durch'] == '0')
                        {
                            $vv_durch = "-1";
                        }
                        else
                        {
                            $vv_durch = $verordnung_map[$filled_sapvs[$sapv_data['id']]['verordnung_durch']];
                        }
                        $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_verordnung_durch'] = $vv_durch;
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
    
                        // SAPV_verordnung_art
                        // -1=Keine Angabe;
                        // 1 = Erst-VO;
                        // 2 = Folge-VO
                        if (!empty($filled_sapvs[$sapv_data['id']]['art_der_erordnung']))
                        {
                            if ($filled_sapvs[$sapv_data['id']]['art_der_erordnung'] == 'Erstverordnung')
                            {
                                $art_erordung = '1';
                            }
                            elseif ($filled_sapvs[$sapv_data['id']]['art_der_erordnung'] == 'Folgeverordnung')
                            {
                                $art_erordung = '2';
                            }
                        }
                        else
                        {
                            $art_erordung = '-1';
                        }
    
                        $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_verordnung_art'] = $art_erordung;
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
                        // SAPV_uebernahme_aus
                        //-1 = Keine Angabe;
                        // 1 = eigene Haueslichkeit;
                        // 2 = Pflegeheim;
                        // 3 = stationaeres Hospiz;
                        // 4 = Krankenhausstation;
                        // 5 = Palliativstation  
                        $ubernahme_aus_mapping = array ('0' => '-1', '1' => '1', '2'=>'-1', '3' => '2', '4' => '3', '5' => '4', '6' => '5');
    
                        if (!empty($filled_sapvs[$sapv_data['id']]['ubernahme_aus']) && $filled_sapvs[$sapv_data['id']]['ubernahme_aus'] != '0')
                        {
                            $ubernahme_aus = $ubernahme_aus_mapping[$filled_sapvs[$sapv_data['id']]['ubernahme_aus']];
                        }
                        else
                        {
                            $ubernahme_aus = '-1';
                        }
    
                        $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_uebernahme_aus'] = $ubernahme_aus;
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
                        //SAPV_PC_Team
                        // 1 = ausgewählt
                        // -1 = nicht ausgewählt
                        if (!empty($filled_sapvs[$sapv_data['id']]['pcteam']) && $filled_sapvs[$sapv_data['id']]['pcteam'] != '0')
                        {
                            $pcteam = $filled_sapvs[$sapv_data['id']]['pcteam'];
                        }
                        else
                        {
                            $pcteam = '-1';
                        }
    
                        $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_PC_Team'] = $pcteam;
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
                        
                        //Ärztlich:
                        // SAPV_Hausarzt
                        // 1 = ausgewählt 
                        // -1 = nicht ausgewählt
                        $arztlich = explode(',', $filled_sapvs[$sapv_data['id']]['arztlich']);
    
                        if (count($arztlich) > 0 && !empty($arztlich))
                        {
                            $arztlich_array = array ('1' => 'SAPV_Hausarzt', '2' => 'SAPV_andere_aerztlich', '3' => 'SAPV_Krankenhausarzt', '5' => 'SAPV_amb_Facharzt');
    
                            foreach ($arztlich_array as $k_arztlich => $v_arztlich)
                            {
                                if (in_array($k_arztlich, $arztlich))
                                {
                                    //set 1 if is in array from db
                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_arztlich] = '1';
                                }
                                else
                                {
    
                                    //set -1
                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_arztlich] = '-1';
                                }
                            }
                        }
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
                        
                        
                        //Pflegerisch
                        // SAPV_amb_Pflege
                        //  1 = ausgewählt 
                        // -1 = nicht ausgewählt
                        $pflegerisch = explode(',', $filled_sapvs[$sapv_data['id']]['pflegerisch']);
    
                        if (count($pflegerisch) > 0 && !empty($pflegerisch))
                        {
                            $pflegerisch_array = array ('1' => 'SAPV_amb_Pflege', '2' => 'SAPV_amb_Palliativpflege', '3' => 'SAPV_Pflegeheim', '4' => 'SAPV_stat_Hospiz');
    
                            foreach ($pflegerisch_array as $k_pflegerisch => $v_pflegerisch)
                            {
                                if (in_array($k_pflegerisch, $pflegerisch))
                                {
                                    //set 1 if is in array from db
                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_pflegerisch] = '1';
                                }
                                else
                                {
    
                                    //set -1
                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_pflegerisch] = '-1';
                                }
                            }
                        }
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
                        //Ambulanter hospizdienst
                        //SAPV_Palliativberatung
                        // 1 = ausgewählt 
                        // -1 = nicht ausgewählt
                        $ambulanter = explode(',', $filled_sapvs[$sapv_data['id']]['ambulanter_hospizdienst']);
    
                        if (count($ambulanter) > 0 && !empty($ambulanter))
                        {
                            $ambulanter_array = array ('1' => 'SAPV_Palliativberatung', '2' => 'SAPV_ehrenamtl_Begleitung');
    
                            foreach ($ambulanter_array as $k_ambulanter => $v_ambulanter)
                            {
                                if (in_array($k_ambulanter, $ambulanter))
                                {
                                    //set 1 if is in array from db
                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_ambulanter] = '1';
                                }
                                else
                                {
    
                                    //set -1
                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_ambulanter] = '-1';
                                }
                            }
                        }
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
                        //SAPV_Case_Management
                        // 1 = ausgewählt 
                        // -1 = nicht ausgewählt
                        $weitere_pro = explode(',', $filled_sapvs[$sapv_data['id']]['weitere_professionen']);
    
                        if (count($weitere_pro) > 0 && !empty($weitere_pro))
                        {
                            $weitere_array = array ('1' => 'SAPV_Case_Management', '2' => 'SAPV_Ernaehrungsberatung', '3' => 'SAPV_Physiotherapie', '4' => 'SAPV_Psychotherapie',
                                '5' => 'SAPV_Seelsorge', '6' => 'SAPV_Sozialarbeit', '7' => 'SAPV_andere_professionen');
    
                            foreach ($weitere_array as $k_weitere => $v_weitere)
                            {
                                if (in_array($k_weitere, $weitere_pro))
                                {
                                    //set 1 if is in array from db
                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_weitere] = '1';
                                }
                                else
                                {
    
                                    //set -1
                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_weitere] = '-1';
                                }
                            }
                        }
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
                        //weitere Professionen
                        //SAPV_Case_Management
                        // 1 = ausgewählt
                        // -1 = nicht ausgewählt
                        $professions = explode(',', $filled_sapvs[$sapv_data['id']]['weitere_professionen']);
    
                        if (count($professions) > 0 && !empty($professions))
                        {
                            $professions_array = array ('1' => 'SAPV_Case_Management', '2' => 'SAPV_Ernaehrungsberatung', '3' => 'SAPV_Physiotherapie', '4' => 'SAPV_Psychotherapie',
                                '5' => 'SAPV_Seelsorge', '6' => 'SAPV_Sozialarbeit', '7' => 'SAPV_andere_professionen');
    
                            foreach ($professions_array as $k_professions => $v_professions)
                            {
                                if (in_array($k_professions, $professions))
                                {
                                    //set 1 if is in array from db
                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_professions] = '1';
                                }
                                else
                                {
                                    //set -1
                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_professions] = '-1';
                                }
                            }
                        }
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
                        //SAPV_einw_grund
                        // text
                        if (!empty($filled_sapvs[$sapv_data['id']]['grund_einweisung']))
                        {
                            $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_einw_grund'] = $filled_sapvs[$sapv_data['id']]['grund_einweisung'];
                        }
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
                        //SAPV_sapvdatum_ende
                        // (jjjj-mm-tt)
                        if (!empty($filled_sapvs[$sapv_data['id']]['end_date_sapv']))
                        {
                            $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_sapvdatum_ende'] = date('Y-m-d', strtotime($filled_sapvs[$sapv_data['id']]['end_date_sapv']));
                        } else {
                            $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_sapvdatum_ende'] = date('Y-m-d', strtotime($sapv_data['verordnungbis']));
                        }
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
                        //SAPV_ende_spez_amb_behandlung
                        //-1 =Keine Angabe;
                        // 1 = SAPV nicht erforderlich;
                        // 2 = Einweisung in KH;
                        // 3 = verstorben;
                        // 4 = anderes
                        if (empty($filled_sapvs[$sapv_data['id']]['therapieende']) || $filled_sapvs[$sapv_data['id']]['therapieende'] == '0')
                        {
                            $thera_ende = "-1";
                        }
                        else
                        {
                            $thera_ende_mapping = array('0'=>'-1', '1'=>'3', '2'=>'2', '3'=>'1','4'=>'4'); //thera ende has no "3" value in xsd...weird
                            $thera_ende = $thera_ende_mapping[$filled_sapvs[$sapv_data['id']]['therapieende']];
                        }
    
                        $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_ende_spez_amb_behandlung'] = $thera_ende;
                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
                        
                    } //end if filled sapvs not empty
                    else
                    {
                        //minimal data set required to pass validation
                        $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_Programm'] = 'PROGRAM';
                        $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_Pat_ID'] = strtoupper($v_pat['epid']);
                        $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_Dat_ID'] = $sapv_data['id'];
                        $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_vo_datum'] = date('Y-m-d', strtotime($sapv_data['verordnungam']));
                        $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_sapvdatum_beginn'] = date('Y-m-d', strtotime($sapv_data['verordnungam']));
                        $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_sapvdatum_ende'] = date('Y-m-d', strtotime($sapv_data['verordnungbis']));
                        $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_Keine'] = '1';
                    }
                }
            }
        }
    
        //		get various nonkvno saved data
        //		get Haupt Diagnosis diagnosis
        $dg = new DiagnosisType();
        $abb2 = "'HD'";
        $ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid, $abb2);
        $comma = ",";
        $typeid = "'0'";
        foreach ($ddarr2 as $key => $valdia)
        {
            $typeid .=$comma . "'" . $valdia['id'] . "'";
            $comma = ",";
        }
    
        $patdia = new PatientDiagnosis();
        $dianoarray = $patdia->getFinalData($ipid_str, $typeid, true); //set last param true to accept a list of ipids
    
        if (count($dianoarray) > 0)
        {
            foreach ($dianoarray as $key => $valdia)
            {
                if (!empty($valdia['diagnosis']) && !empty($valdia['icdnumber']))
                {
                    $export_data[$valdia['ipid']]['BA_ICD_haupt'] = $valdia['icdnumber'];
                    $export_data[$valdia['ipid']]['BL_ICD_haupt'] = $valdia['icdnumber'];
                }
            }
        }
    
    
        $wohn_mapping = array ('0' => '-1', '1' => '1', '2' => '3', '4' => '2', '6' => '4');
    
        
        //prepare export first kvno data
        foreach ($patient_kvno_first as $k_pkvno => $v_pkvno)
        {
    
            //BA_wohnsituation 
            // -1=Keine Angabe; 
            // 1 =1 - allein;
            // 2 =2 - Heim; 
            // 3 =3 - mit Angehörigen;
            // 4 =4 - Sonstige
            if (!empty($v_pkvno['wohnsituations']))
            {
                $wohn = $wohn_mapping[$v_pkvno['wohnsituations']];
            }
            else
            {
                $wohn = '-1';
            }
    
            $export_data[$v_pkvno['ipid']]['BA_wohnsituation'] = $wohn;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

            // BA_ecog
            // -1=Keine Angabe;
            // 0=0 - Normale Aktivitat;
            // 1=1 - Gehfahig, leichte Aktivitat möglich;
            // 2=2 - Nicht arbeitsfahig, kann > 50% der Wachzeit aufstehen;
            // 3=3 - Begrenzte Selbstversorgung, >50% Wachzeit bettlagerig;
            // 4=4 - Pflegebedurftig, permanent bettlagerig
            if (!empty($v_pkvno['ecog']))
            {
                $ecog_value = ($v_pkvno['ecog'] - 1);
            }
            else
            {
                $ecog_value = '-1';
            }
    
            $export_data[$v_pkvno['ipid']]['BA_ecog'] = $ecog_value;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            // BA_datum
            // Datum der Erfassung und Dokumentation|Datumsfeld (jjjj-mm-tt)
            if (!empty($v_pkvno['datum_der_erfassung1']) && $v_pkvno['datum_der_erfassung1'] != '1970-01-01 00:00:00' && $v_pkvno['datum_der_erfassung1'] != '0000-00-00 00:00:00')
            {
                $export_data[$v_pkvno['ipid']]['BA_datum'] = date('Y-m-d', strtotime($v_pkvno['datum_der_erfassung1']));
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

            // =============================================== //
            //this part is repeating with BA and BL prefix
            // =============================================== //
    
            // BA_Hausarzt
            // 1 = ausgewählt 
            // -1 = nicht ausgewählt
            if (!empty($v_pkvno['begleitung']))
            {
                $begleitung = explode(',', $v_pkvno['begleitung']);
            }
            $export_data[$v_pkvno['ipid']]['BA_Hausarzt'] = (in_array('2', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Ambulante_Pflege'] = (in_array('3', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Palliativarzt'] = (in_array('4', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Palliativpflege'] = (in_array('5', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Palliativberatung'] = (in_array('6', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Ehrenamtlicher_Dienst'] = (in_array('7', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_KH_Palliativstation'] = (in_array('8', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Hospiz_stationaer'] = (in_array('9', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Krankenhaus_Andere_Station'] = (in_array('10', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Heim'] = (in_array('11', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Palliative_Care_Team'] = (in_array('12', $begleitung)) ? '1' : '-1';
            $export_data[$v_pkvno['ipid']]['BA_sonstige_behandlung'] = (in_array('13', $begleitung)) ? '1' : '-1';
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            //BA_Schmerzen
            // -1=Keine Angabe;
            // 0=0 - kein;
            // 1=1 - leicht;
            // 2=2 - mittel;
            // 3=3 - stark
            $export_data[$v_pkvno['ipid']]['BA_Schmerzen'] = (!empty($v_pkvno['schmerzen'])) ? ($v_pkvno['schmerzen'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Ubelkeit'] = (!empty($v_pkvno['ubelkeit'])) ? ($v_pkvno['ubelkeit'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Erbrechen'] = (!empty($v_pkvno['erbrechen'])) ? ($v_pkvno['erbrechen'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Luftnot'] = (!empty($v_pkvno['luftnot'])) ? ($v_pkvno['luftnot'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Verstopfung'] = (!empty($v_pkvno['verstopfung'])) ? ($v_pkvno['verstopfung'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Schwache'] = (!empty($v_pkvno['swache'])) ? ($v_pkvno['swache'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Appetitmangel'] = (!empty($v_pkvno['appetitmangel'])) ? ($v_pkvno['appetitmangel'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Mudigkeit'] = (!empty($v_pkvno['mudigkeit'])) ? ($v_pkvno['mudigkeit'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Wunden'] = (!empty($v_pkvno['dekubitus'])) ? ($v_pkvno['dekubitus'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Hilfe_ATL'] = (!empty($v_pkvno['hilfebedarf'])) ? ($v_pkvno['hilfebedarf'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Depressivitat'] = (!empty($v_pkvno['depresiv'])) ? ($v_pkvno['depresiv'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Angst'] = (!empty($v_pkvno['angst'])) ? ($v_pkvno['angst'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Anspannung'] = (!empty($v_pkvno['anspannung'])) ? ($v_pkvno['anspannung'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Verwirrtheit'] = (!empty($v_pkvno['desorientier'])) ? ($v_pkvno['desorientier'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Versorgungsorg'] = (!empty($v_pkvno['versorgung'])) ? ($v_pkvno['versorgung'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_Uberforderung'] = (!empty($v_pkvno['umfelds'])) ? ($v_pkvno['umfelds'] - 1) : '-1';
            $export_data[$v_pkvno['ipid']]['BA_sonstige_probleme'] = (!empty($v_pkvno['sonstige_probleme'])) ? ($v_pkvno['sonstige_probleme'] - 1) : '-1';
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
        }
    
        foreach ($patient_kvno_last as $k_pkvno_last => $v_pkvno_last)
        {
    
            //BL_wohnsituation
            // -1=Keine Angabe;
            // 1=1 - allein;
            // 2=2 - Heim;
            // 3=3 - mit Angehörigen;
            // 4=4 - Sonstige
            if (!empty($v_pkvno_last['wohnsituations']))
            {
                $wohn = $wohn_mapping[$v_pkvno_last['wohnsituations']];
            }
            else
            {
                $wohn = '-1';
            }
            $export_data[$v_pkvno_last['ipid']]['BL_wohnsituation'] = $wohn;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            
            // BL_datum
            // 13. Datum der Erfassung und Dokumentation|Datumsfeld (jjjj-mm-tt)
            if (!empty($v_pkvno_last['datum_der_erfassung1']) && $v_pkvno_last['datum_der_erfassung1'] != '1970-01-01 00:00:00' && $v_pkvno_last['datum_der_erfassung1'] != '0000-00-00 00:00:00')
            {
                $export_data[$v_pkvno_last['ipid']]['BL_datum'] = date('Y-m-d', strtotime($v_pkvno_last['datum_der_erfassung1']));
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            
            //BL_ecog
            // -1=Keine Angabe;
            // 0=0 - Normale Aktivitat;
            // 1=1 - Gehfahig, leichte Aktivitat möglich;
            // 2=2 - Nicht arbeitsfahig, kann > 50% der Wachzeit aufstehen;
            // 3=3 - Begrenzte Selbstversorgung, >50% Wachzeit bettlagerig;
            // 4=4 - Pflegebedurftig, permanent bettlagerig
            if (!empty($v_pkvno_last['ecog']))
            {
                $ecog_value = ($v_pkvno_last['ecog'] - 1);
            }
            else
            {
                $ecog_value = '-1';
            }
    
            $export_data[$v_pkvno_last['ipid']]['BL_ecog'] = $ecog_value;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            //BL_Hausarzt
            // 1 = ausgewählt 
            // -1 = nicht ausgewählt
            if (!empty($v_pkvno_last['begleitung']))
            {
                $begleitung_last = explode(',', $v_pkvno_last['begleitung']);
            }
            $export_data[$v_pkvno_last['ipid']]['BL_Hausarzt'] = (in_array('2', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Ambulante_Pflege'] = (in_array('3', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Palliativarzt'] = (in_array('4', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Palliativpflege'] = (in_array('5', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Palliativberatung'] = (in_array('6', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Ehrenamtlicher_Dienst'] = (in_array('7', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_KH_Palliativstation'] = (in_array('8', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Hospiz_stationaer'] = (in_array('9', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Krankenhaus_Andere_Station'] = (in_array('10', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Heim'] = (in_array('11', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Palliative_Care_Team'] = (in_array('12', $begleitung_last)) ? '1' : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_sonstige_behandlung'] = (in_array('13', $begleitung_last)) ? '1' : '-1';
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            // BL_Schmerzen
            // -1=Keine Angabe;
            // 0=0 - kein;
            // 1=1 - leicht;
            // 2=2 - mittel;
            // 3=3 - stark
            $export_data[$v_pkvno_last['ipid']]['BL_Schmerzen'] = (!empty($v_pkvno_last['schmerzen'])) ? ($v_pkvno_last['schmerzen'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Ubelkeit'] = (!empty($v_pkvno_last['ubelkeit'])) ? ($v_pkvno_last['ubelkeit'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Erbrechen'] = (!empty($v_pkvno_last['erbrechen'])) ? ($v_pkvno_last['erbrechen'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Luftnot'] = (!empty($v_pkvno_last['luftnot'])) ? ($v_pkvno_last['luftnot'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Verstopfung'] = (!empty($v_pkvno_last['verstopfung'])) ? ($v_pkvno_last['verstopfung'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Schwache'] = (!empty($v_pkvno_last['swache'])) ? ($v_pkvno_last['swache'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Appetitmangel'] = (!empty($v_pkvno_last['appetitmangel'])) ? ($v_pkvno_last['appetitmangel'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Mudigkeit'] = (!empty($v_pkvno_last['mudigkeit'])) ? ($v_pkvno_last['mudigkeit'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Wunden'] = (!empty($v_pkvno_last['dekubitus'])) ? ($v_pkvno_last['dekubitus'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Hilfe_ATL'] = (!empty($v_pkvno_last['hilfebedarf'])) ? ($v_pkvno_last['hilfebedarf'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Depressivitat'] = (!empty($v_pkvno_last['depresiv'])) ? ($v_pkvno_last['depresiv'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Angst'] = (!empty($v_pkvno_last['angst'])) ? ($v_pkvno_last['angst'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Anspannung'] = (!empty($v_pkvno_last['anspannung'])) ? ($v_pkvno_last['anspannung'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Verwirrtheit'] = (!empty($v_pkvno_last['desorientier'])) ? ($v_pkvno_last['desorientier'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Versorgungsorg'] = (!empty($v_pkvno_last['versorgung'])) ? ($v_pkvno_last['versorgung'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_Uberforderung'] = (!empty($v_pkvno_last['umfelds'])) ? ($v_pkvno_last['umfelds'] - 1) : '-1';
            $export_data[$v_pkvno_last['ipid']]['BL_sonstige_probleme'] = (!empty($v_pkvno_last['sonstige_probleme'])) ? ($v_pkvno_last['sonstige_probleme'] - 1) : '-1';
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            // BL_Opioide_WHO_Stufe_3
            // 1 = ausgewählt 
            // -1 = nicht ausgewählt
            if ($v_pkvno_last['who'] == '1')
            {
                $opioids = '1';
            }
            else
            {
                $opioids = '-1';
            }
    
            $export_data[$v_pkvno_last['ipid']]['BL_Opioide_WHO_Stufe_3'] = $opioids;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            // BL_Kortikosteroide
            // 1 = ausgewählt
            // -1 = nicht ausgewählt
            if ($v_pkvno_last['steroide'] == '1')
            {
                $steroide = '1';
            }
            else
            {
                $steroide = '-1';
            }
    
            $export_data[$v_pkvno_last['ipid']]['BL_Kortikosteroide'] = $steroide;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            //BL_Chemotherapie
            // -1=Keine Angabe;
            // 0=0 = nein;
            // 1=1 = fortgesetzt;
            // 2=2 = initiiert
            $export_data[$v_pkvno_last['ipid']]['BL_Chemotherapie'] = ($v_pkvno_last['chemotherapie'] - 1);
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            // BL_Strahlentherapie
            // -1=Keine Angabe; 
            // 0=0 = nein;
            // 1=1 = fortgesetzt;
            // 2=2 = initiiert
            $export_data[$v_pkvno_last['ipid']]['BL_Strahlentherapie'] = ($v_pkvno_last['strahlentherapie'] - 1);
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            // BL_Aufwand
            // text
            if (!empty($v_pkvno_last['aufwand_mit']))
            {
                $export_data[$v_pkvno_last['ipid']]['BL_Aufwand'] = $v_pkvno_last['aufwand_mit'];
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            // BL_problem_1 
            // text
            if(!empty($v_pkvno_last['problem_besonders']))
            {
                $export_data[$v_pkvno_last['ipid']]['BL_problem_1'] = $v_pkvno_last['problem_besonders'];
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            
            //BL_problem_2
            // text
            if(!empty($v_pkvno_last['problem_ausreichend']))
            {
                $export_data[$v_pkvno_last['ipid']]['BL_problem_2'] = $v_pkvno_last['problem_ausreichend'];
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            
            //B_datum_ende
            // jjjj-mm-tt
            // Entlassung / Anderung der Betreuung / Tod
            if(strlen($v_pkvno_last['entlasung_date'])){
                $export_data[$v_pkvno_last['ipid']]['B_datum_ende'] = date('Y-m-d', strtotime($v_pkvno_last['entlasung_date']));
            }
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
            
            // B_thera_ende
            // -1=Keine Angabe;
            // 1=1 - Verstorben;
            // 2=2 - Verlegung, Entlassung;
            // 4=4 - Sonstiges
            if ($v_pkvno_last['therapieende'] == '0' || empty($v_pkvno_last['therapieende']))
            {
                $thera_ende = "-1";
            }
            else
            {
                $thera_ende_mapping = array('0'=>'-1', '1'=>'1', '2'=>'2', '3'=>'2','4'=>'4'); //thera ende has no "3" value in xsd...weird
                $thera_ende = $thera_ende_mapping[$v_pkvno_last['therapieende']];
            }
    
            $export_data[$v_pkvno_last['ipid']]['B_thera_ende'] = $thera_ende;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

            //B_sterbeort_0
            // -1=Keine Angabe;
            // 1=1 - zuhause;
            // 2=2 - Heim;
            // 3=3 - Hospiz;
            // 4=4 - Palliativstation;
            // 5=5 - Krankenhaus;
            // 6=6 - nicht bekannt
            if (empty($v_pkvno_last['sterbeort_dgp']) || $v_pkvno_last['sterbeort_dgp'] == '0')
            {
                $sterbeort = '-1';
            }
            else
            {
                //system location type id=>xml loc type id
                //SYS
                //1 = zu Hause
                //2 = KH
                //3 = Hospiz
                //4 = Altenheim, Pflegeheim
                //5 = Palliativ
                //XML:
                //-1=Keine Angabe;
                //1 zuhause;
                //2 - Heim; (<-- Altenheim / Pflegeheim)
                //3 - Hospiz;
                //4 - Palliativstation;
                //5 - Krankenhaus
                //6 - nicht bekannt
                $location_type_map = array ('1' => '1', '2' => '5', '3' => '3', '4' => '2', '5' => '4');
    
                if ($discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type'] != 0)
                {
                    $sterbeort = $location_type_map[$discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type']];
                }
                else
                {
                    //no location type or other(sonstiges...)
                    $sterbeort = '6';
                }
            }
    
            $export_data[$v_pkvno_last['ipid']]['B_sterbeort_0'] = $sterbeort;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
            
            //   B_bewertung
            //  -1=Keine Angabe;
            //   1=1 - sehr gut;
            //   2=2 - gut;
            //   3=3 - mittel;
            //   4=4 - schlecht;
            //   5=5 - sehr schlecht
            $satisfaction_map = array ('0'=>'-1','1' => '5', '2' => '4', '3' => '3', '4' => '2', '5' => '1');
            
            if (empty($v_pkvno_last['zufriedenheit_mit']))
            {
                $satisfaction = '-1';
            }
            else
            {
                $satisfaction = $satisfaction_map [$v_pkvno_last['zufriedenheit_mit']];
            }
            $export_data[$v_pkvno_last['ipid']]['B_bewertung'] = $satisfaction;
            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    
        } //end last kvno foreach
        
        
        
        if (!empty($_POST)){
            // #############
            // SEND KERNE PART
            // #############
            
            //get KERNE xml
            $kern_schema = '<alle xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="KERN.xsd"></alle>';
            $xml = $this->toXml($export_data, $kern_schema, null, 'KERN');
            
            //echo $xml;
            if (class_exists('DOMDocument'))
            {
                $doc = new DOMDocument();
                $dom_xml = $doc->loadXML($xml);
                $source_path = APP_BASE . 'xsd/KERN.xsd';
                $validation = $doc->schemaValidate($source_path);
            }
            else
            {
                $validation = true; //sskip validation if DOMDocument is not installed
            }
            
            
            $validation = true;
            if ($validation)
            {
                $xml_type = 'KERN';
                $xml_string = $this->xmlpp($xml, false);
                //$xml_string = $xml;
 
                //send xml via post
                $response = $this->data_send($clientid, $xml_string, $xml_type);
        
                $history_id = $this->history_save($userid, $clientid, $xml_string, $response);
        
                if (substr($response, 0, 4) == '1000') //1000 is ok RC
                {
                    // save patients - for this upload
                    if($history_id ){
                        foreach($export_data as $pipid => $ex_data){
                            $exported_ipids[]= $pipid ;
                        }
                        $this->history_patients_save($history_id,$exported_ipids, $userid, $clientid);
                         
                    }
                    // redir success
                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=suc&c=".substr($response, 0, 4));
                    $kern_send_status = "suc"; 
                    $kern_msg = substr($response, 0, 4);
                }
                else if(substr($response, 0, 4) == '2000')
                {
                    // redir error auth error
                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                    $kern_send_status = "err";
                    $kern_msg = substr($response, 0, 4);
                }
                else if(substr($response, 0, 4) == '0000')
                {
                    // curl error
                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                    $kern_send_status = "err";
                    $kern_msg = substr($response, 0, 4);
                }
                else if(substr($response, 0, 4) == '3100')
                {
                    // XML error
                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                    $kern_send_status = "err";
                    $kern_msg = substr($response, 0, 4);
                }
                else
                {
                    // redir generic error
                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err");
                    $kern_send_status = "err";
                    $kern_msg = "generic error";
                }
            }
            else
            {
                // validation error
                // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=9999");
                $this->history_save($userid, $clientid, $xml, 'NULL');
                $kern_send_status = "error";
                $kern_msg = "9999";
            }

            
            // #############
            // SEND KERNE PART
            // #############
            $sapv_schema = '<alle xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="SAPV.xsd"></alle>';
            $xml_sapv = $this->toXml($sapv_export_data, $sapv_schema, null, 'SAPV');
            
            //			echo $xml_sapv;
            if (class_exists('DOMDocument'))
            {
                $doc_sapv = new DOMDocument();
                $dom_xml = $doc_sapv->loadXML($xml_sapv);
                $source_path = APP_BASE . 'xsd/SAPV.xsd';
                $validation_sapv = $doc_sapv->schemaValidate($source_path);
            }
            else
            {
                $validation_sapv = true;
            }
            
            $validation_sapv = true;
            if ($validation_sapv)
            {
                $xml_type = 'SAPV';
                $xml_string = $this->xmlpp($xml_sapv, false);
            
                if ($_REQUEST['dbg'])
                {
            
                    //download xml
                    echo $xml_string;
            
                    header("Pragma: public");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Content-Type: application/force-download");
                    header("Content-Type: application/octet-stream");
                    header("Content-type: text/xml; charset=utf-8");
                    header("Content-Disposition: attachment; filename=SAPV.xml");
                }
                else
                {
                    //send xml via post
                    $response = $this->data_send($clientid, $xml_string, $xml_type);
                    $sapv_history_id = $this->history_save($userid, $clientid, $xml_string, $response);
            
                    if (substr($response, 0, 4) == '1000') //1000 is ok RC
                    {
                        if($sapv_history_id){
                            foreach($export_data as $pipid => $ex_data){
                                $exported_ipids[]= $pipid ;
                            }
                            $this->history_patients_save($history_id,$exported_ipids, $userid, $clientid);
                        }
                        
                            
                        // redir success
                        // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=suc&c=".substr($response, 0, 4));
                        $sapv_send_status = "suc";
                        $sapv_msg = substr($response, 0, 4);
                    }
                    else if(substr($response, 0, 4) == '2000')
                    {
                        // redir error auth error
                        // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                        $sapv_send_status = "err";
                        $sapv_msg = substr($response, 0, 4);
                    }
                    else if(substr($response, 0, 4) == '0000')
                    {
                        // curl error
                        // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                        $sapv_send_status = "err";
                        $sapv_msg = substr($response, 0, 4);
                    }
                    else
                    {
                        //redir generic error
                        // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err");
                        $sapv_send_status = "err";                                                                        
                        $sapv_msg = "generic error";
                    }
                }
            }
            else
            {
                //validation error
                $this->history_save($userid, $clientid, $xml, 'NULL');
                // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=9999");
                $sapv_send_status = "error";                                                                        
                $sapv_msg = "9999";
            }
            
            
            $this->_redirect(APP_BASE . "dgp/dgpfullpatientlist?ks=".$kern_send_status."&km=".$kern_msg."&ss=".$sapv_send_status."&sm=".$sapv_msg);
        }
 
    }
    
    
}
?>