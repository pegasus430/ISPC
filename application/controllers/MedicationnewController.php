<?php
	class MedicationnewController extends Pms_Controller_Action {

		public function init()
		{
			/* Initialize action controller here */
			array_push($this->actions_with_js_file, "listmedicationfrequency");
			setlocale(LC_ALL, 'de_DE.utf-8');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->clientid = $logininfo->clientid;
			$this->userid = $logininfo->userid;
			$this->usertype = $logininfo->usertype;
			$this->filepass = $logininfo->filepass;
			if(!$logininfo->clientid)
			{
				//redir to select client error
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}
		}

		private function retainValues($values, $prefix = '')
		{
		    foreach($values as $key => $val)
		    {
		        if(!is_array($val))
		        {
		            $this->view->$key = $val;
		        }
		        else
		        {
		            //retain 1 level array used in multiple hospizvbulk form
		            foreach($val as $k_val => $v_val)
		            {
		                if(!is_array($v_val))
		                {
		                    $this->view->{$prefix . $key . $k_val} = $v_val;
		                }
		            }
		        }
		    }
		}
		
		public function timeschemeAction()
		{
		    $clientid = $this->clientid;
		    
		    //get all client modules - to see what medications  teh client can see
		    $modules = new Modules();
		    $individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
		    if($individual_medication_time_m){
		        $individual_medication_time = 1;
		    }else {
		        $individual_medication_time = 0;
		    }
		    $this->view->individual_medication_time = $individual_medication_time;
		    
		    
		    if($this->getRequest()->isPost())
		    {
		        if($individual_medication_time == 0){
		            
    		        if(!empty($_POST['client_interval']) && $_POST['time_changed'] == "1")
    		        {
    		            
                        $this->clear_client_intervals($clientid,"all"); // clear  medication_type "all"
    		            
    		            foreach($_POST['client_interval'] as $int_nr =>$int_time_value)
    		            {
        		            $interval_times_arr[] = array(
        		                'time_interval' => $int_time_value,
        		                'clientid' => $clientid,
        		            );
    		            }
    		            
    		            if(count($interval_times_arr) > 0)
    		            {
    		                //insert many records with one query!!
    		                $collection = new Doctrine_Collection('MedicationIntervals');
    		                $collection->fromArray($interval_times_arr);
    		                $collection->save();
    		            }
    		        }
                } 
                else
                {
                    if(!empty($_POST['medications_intervals']))
                    {
                        $this->clear_client_intervals($clientid,false,"all"); // clear except medication_type "all" 
                        
                    
                        foreach($_POST['medications_intervals'] as $med_type => $imints)
                        {
                            if($_POST['individual_time'][$med_type]['time_schedule'] == "1")
                            {
                                foreach($imints as $k => $int_time_value){
                                    $interval_times_arr[] = array(
                                        'clientid' => $clientid,
                                        'time_interval' => $int_time_value,
                                        'medication_type' => $med_type
                                    );
                                }
                            }
                        }
                        
                        if(count($interval_times_arr) > 0)
                        {
                            //insert many records with one query!!
                            $collection = new Doctrine_Collection('MedicationIntervals');
                            $collection->fromArray($interval_times_arr);
                            $collection->save();
                        }
                    }
                }
                
                if(!empty($_POST['individual_time'])){
                    
                    $this->clear_client_options($clientid);
                    
                    foreach($_POST['individual_time'] as $med_type=>$values){
                        $options_arr[] = array(
                            'clientid' => $clientid,
                            'medication_type' => $med_type,
                            'time_schedule' => $values['time_schedule']
                        );
                    }
                    
                    if(count($options_arr) > 0)
                    {
                        //insert many records with one query!!
                        $collection = new Doctrine_Collection('MedicationOptions');
                        $collection->fromArray($options_arr);
                        $collection->save();
                    }
                }
                
		    }
		    
		    /* ================ MEDICATION BLOCK VISIBILITY AND OPTIONS ======================= */
		    
		    //$medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition");
		    $medication_blocks = array("actual","isbedarfs","isivmed","isnutrition"); // "iscrisis" - this is not allowed to have time interval ISPC-2247 02.11.2018 
		    $medication_blocks[] = "isintubated"; // ISPC-2176 16.04.2018 @Ancuta
		    
		    
		    /* IV BLOCK -  i.v. / s.c. */
		    $iv_medication_block = $modules->checkModulePrivileges("53", $clientid);
		    if(!$iv_medication_block){
		        $medication_blocks = array_diff($medication_blocks,array("isivmed"));
		    }
		    
		    /* TREATMENT CARE BLOCK -  Behandlungspflege*/
		    $treatmen_care_block = $modules->checkModulePrivileges("85", $clientid);
		    if(!$treatmen_care_block){
		        $medication_blocks = array_diff($medication_blocks,array("treatment_care"));
		    }
		    
		    /* NUTRITION  BLOCK - Ernahrung */
		    $nutrition_block = $modules->checkModulePrivileges("103", $clientid);
		    if(!$nutrition_block){
		        $medication_blocks = array_diff($medication_blocks,array("isnutrition"));
		    }
		    /* SCHMERZPUMPE  BLOCK - Schmerzpumpe */
		    $schmerzepumpe_block = $modules->checkModulePrivileges("54", $clientid);
		    if(!$schmerzepumpe_block){
		        $medication_blocks = array_diff($medication_blocks,array("isschmerzpumpe"));
		    }
		    
		    /* CRISIS BLOCK */
		    $crisis_block = $modules->checkModulePrivileges("144", $clientid);
		    if(!$crisis_block){
		        $medication_blocks = array_diff($medication_blocks,array("iscrisis"));
		    }
		    
		    /* INTUBETED/INFUSION MEDICATION  BLOCK */
		    $intubated_block = $modules->checkModulePrivileges("167", $clientid);
		    if(!$intubated_block){
		        $medication_blocks = array_diff($medication_blocks,array("isintubated"));
		    }
		    
		    
		    $this->view->medication_blocks = $medication_blocks;
		    

		    
		    //get get saved data
		    if($individual_medication_time == "0"){
		        $client_time_scheme = MedicationIntervals::client_saved_medication_intervals($clientid,array("all"));
		    } else {
		        $client_time_scheme = MedicationIntervals::client_saved_medication_intervals($clientid,$medication_blocks);
		    
		    }
            $this->view->intervals = $client_time_scheme;
		    
		    //get time scchedule options
		    $client_med_options = MedicationOptions::client_saved_medication_options($clientid);
		    $this->view->client_medication_options = $client_med_options; 
		    
		}

		
		public function clear_client_intervals($clientid,$type = false)
		{
		    $saved_time_scheme = MedicationIntervals::client_medication_intervals($clientid, $type);

		    if($saved_time_scheme)
		    {
		        $int = 1;
		        foreach($saved_time_scheme as $k=>$ts)
		        {
		            $med = Doctrine::getTable('MedicationIntervals')->find($ts['id']);
		            $med->isdelete = 1;
        		    $med->save();
		        }
		    }
		}
		
		public function clear_client_options($clientid)
		{
		    $saved_options = MedicationOptions::client_medication_options($clientid);

		    if($saved_options)
		    {
		        $int = 1;
		        foreach($saved_options as $k=>$ts)
		        {
		            $med = Doctrine::getTable('MedicationOptions')->find($ts['id']);
		            $med->isdelete = 1;
        		    $med->save();
		        }
		    }
		}
		
		/* ##################################################################### */
		/* ############ medication unit list :: Einheit  ####################### */
		/* ##################################################################### */
		
		public function unitlistAction()
		{
		    set_time_limit(0);
		    $clientid = $this->clientid;
		}
		
		
		public function getunitlistAction()
		{
		    $clientid = $this->clientid;
		    
		    $this->_helper->viewRenderer->setNoRender();
		     
		    if(!$_REQUEST['length'])
		    {
		        $_REQUEST['length'] = "100";
		    }
		
		    $limit = $_REQUEST['length'];
		    $offset = $_REQUEST['start'];
		    $search_value = $_REQUEST['search']['value'];

		    
		    
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
		        "1" => "unit"
		    );
		     
		    $order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
		     
		    // ########################################
		    // #####  Query for count ###############
		    $fdoc1 = Doctrine_Query::create();
		    $fdoc1->select('count(*)');
		    $fdoc1->from('MedicationUnit');
		    $fdoc1->where("isdelete = 0  ");
		    $fdoc1->andWhere("clientid = ".$clientid);
		    $fdoc1->andWhere("extra = 0");
		    /* ------------- Search options ------------------------- */
		    if (isset($search_value) && strlen($search_value) > 0)
		    {
		        $fdoc1->andWhere("unit like '%" . trim($search_value) . "%'  ");
		    }
		    $fdoc1->orderBy($order_by_str);
		    $fdocexec = $fdoc1->execute();
		    $fdocarray = $fdocexec->toArray();
		     
		    $full_count  = $fdocarray[0]['count'];
		     
		    // ########################################
		    // #####  Query for details ###############
		    $sql = '*,';
		    $fdoc1->select($sql);
		    $fdoc1->where("isdelete = 0  ");
		    $fdoc1->andWhere("clientid = ".$clientid);
		    $fdoc1->andWhere("extra = 0");
		    /* ------------- Search options ------------------------- */
		    if (isset($search_value) && strlen($search_value) > 0)
		    {
		        $fdoc1->andWhere("unit like '%" . trim($search_value) . "%'     ");
		    }
		    $fdoc1->limit($limit);
		    $fdoc1->offset($offset);
		    $fdoclimitexec = $fdoc1->execute();
		    $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
		     
		    $report_ids[] = '99999999999999';
		    foreach ($fdoclimit as $key => $report)
		    {
		        $fdoclimit_arr[$report['id']] = $report;
		        $report_ids[] = $report['id'];
		    }
		
		    $all_users = Pms_CommonData::get_client_users($clientid, true);
		
		    foreach($all_users as $keyu => $user)
		    {
		        $all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
		    }
		
		
		    $row_id = 0;
		    $link = "";
		    $resulted_data = array();
		    foreach($fdoclimit_arr as $report_id =>$mdata)
		    {
		        $link = '%s ';
		         
		        $resulted_data[$row_id]['unit'] = sprintf($link,$mdata['unit']);
// 		        $resulted_data[$row_id]['create_date'] = sprintf($link,date('d.m.Y H:i',strtotime($mdata['create_date'])));
// 		        $resulted_data[$row_id]['created_by'] = sprintf($link,$all_users_array[$mdata['create_user']]);
// 		        $resulted_data[$row_id]['created_by'] = sprintf($link,$all_users_array[$mdata['create_user']]);
		
		        $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medicationnew/editunit?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
		        $row_id++;
		    }
		     
		    $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		    $response['recordsTotal'] = $full_count;
		    $response['recordsFiltered'] = $full_count; // ??
		    $response['data'] = $resulted_data;
		     
		    header("Content-type: application/json; charset=UTF-8");
		    
		    echo json_encode($response);
		    exit;
		}
		
		public function addunitAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    
		    if($this->getRequest()->isPost())
		    {
		        $med_form = new Application_Form_MedicationUnit();
		
		        if($med_form->validate($_POST))
		        {
		            $med_form->insert($_POST);
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $med_form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }
		}
		
		public function editunitAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    
		    $this->_helper->viewRenderer('addunit');
		
		    if($this->getRequest()->isPost())
		    {
		        if ($_GET['id'] > 0)
		        {
		            $unit_id= $_GET['id'];
		        }
		
		        $med_form = new Application_Form_MedicationUnit();
		
		        if($med_form->validate($_POST))
		        {
		            $_POST['id'] = $unit_id;
		            $med_form->update($_POST);
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $med_form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }

		    if ($_GET['id'] > 0)
		    {
		        $u_id = $_GET['id'];
		        $fdoc = Doctrine::getTable('MedicationUnit')->find($u_id );
		
		        if ($fdoc)
		        {
		            $fdocarray = $fdoc->toArray();
		            $this->retainValues($fdocarray);
		        }
		    }
		}
		
		public function deleteunitAction ()
		{
		    $this->_helper->viewRenderer('list');
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		
		    if($_GET['id'])
		    {

		        $thrash = Doctrine::getTable('MedicationUnit')->find($_GET['id']);
		        $thrash->isdelete = 1;
		        $thrash->save();
		
		        $this->_redirect(APP_BASE . "medicationnew/unitlist");
		        exit;
		    }
		}
		
		
		
		
		/* ##################################################################### */
		/* ############ medication "kind" list :: Applikationsweg  ############# */
		/* ##################################################################### */

		public function typelistAction()
		{
		    set_time_limit(0);
		    $clientid = $this->clientid;
		}
		
		
		public function gettypelistAction()
		{
		    $clientid = $this->clientid;
		    
		    $this->_helper->viewRenderer->setNoRender();
		    
		    $medsets = new MedicationsSetsItems();
		    $medsetsitems = $medsets->get_all_sets_medications($clientid);
		     
		    $total_typ_attached = array();
		    foreach($medsetsitems as $kr=>$vr)
		    {
		    	if($vr['type'])
		    	{
		    		foreach($vr['type'] as $kf=>$vf)
		    		{
		    			$total_typ_attached[] = $vf;
		    		}
		    	}
		    }
		    $total_typ_attached = array_unique($total_typ_attached);
		    //print_r($total_typ_attached); exit;
		     
		    if(!$_REQUEST['length']){
		        $_REQUEST['length'] = "100";
		    }
		
		    $limit = $_REQUEST['length'];
		    $offset = $_REQUEST['start'];
		    $search_value = $_REQUEST['search']['value'];
		     
		    if(!empty($_REQUEST['order'][0]['column'])){
		        $order_column = $_REQUEST['order'][0]['column'];
		    } else{
		        $order_column = "1";
		    }
		    $order_dir = $_REQUEST['order'][0]['dir'];
		     
		    $columns_array = array(
		        "1" => "type"
		    );
		     
		    $order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
		     
		    // ########################################
		    // #####  Query for count ###############
		    $fdoc1 = Doctrine_Query::create();
		    $fdoc1->select('count(*)');
		    $fdoc1->from('MedicationType');
		    $fdoc1->where("isdelete = 0  ");
		    $fdoc1->andWhere("clientid = ".$clientid);
		    $fdoc1->andWhere("extra = 0");
		    /* ------------- Search options ------------------------- */
		    if (isset($search_value) && strlen($search_value) > 0)
		    {
		        $fdoc1->andWhere("type like '%" . trim($search_value) . "%'  ");
		    }
		    $fdoc1->orderBy($order_by_str);
		    $fdocexec = $fdoc1->execute();
		    $fdocarray = $fdocexec->toArray();
		     
		    $full_count  = $fdocarray[0]['count'];
		     
		    // ########################################
		    // #####  Query for details ###############
		    $sql = '*,';
		    $fdoc1->select($sql);
		    $fdoc1->where("isdelete = 0  ");
		    $fdoc1->andWhere("clientid = ".$clientid);
		    $fdoc1->andWhere("extra = 0");
		    /* ------------- Search options ------------------------- */
		    if (isset($search_value) && strlen($search_value) > 0)
		    {
		        $fdoc1->andWhere("type like '%" . trim($search_value) . "%'     ");
		    }
		    $fdoc1->limit($limit);
		    $fdoc1->offset($offset);
		    $fdoclimitexec = $fdoc1->execute();
		    $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
		     
		    $report_ids[] = '99999999999999';
		    foreach ($fdoclimit as $key => $report)
		    {
		        $fdoclimit_arr[$report['id']] = $report;
		        $report_ids[] = $report['id'];
		    }
		
		    $all_users = Pms_CommonData::get_client_users($clientid, true);
		
		    foreach($all_users as $keyu => $user)
		    {
		        $all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
		    }
		
		
		    $row_id = 0;
		    $link = "";
		    $resulted_data = array();
		    foreach($fdoclimit_arr as $report_id =>$mdata){
		        $link = '%s ';
		        
		        if(in_array($mdata['id'], $total_typ_attached))
		        {
		        	$resulted_data[$row_id]['type'] = sprintf($link,'<span>!</span>'.$mdata['type']);
		        	$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medicationnew/edittype?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a>';
		        }
		        else
		        {
		        	$resulted_data[$row_id]['type'] = sprintf($link,$mdata['type']);
		        	$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medicationnew/edittype?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
		        }
		        
		        $row_id++;
		    }
		     
		    $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		    $response['recordsTotal'] = $full_count;
		    $response['recordsFiltered'] = $full_count; // ??
		    $response['data'] = $resulted_data;
		    
		    header("Content-type: application/json; charset=UTF-8");
		    
		    
		    echo json_encode($response);
		    exit;
		}
		
		
		public function addtypeAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    
		    if($this->getRequest()->isPost())
		    {
		        $med_form = new Application_Form_MedicationType();
		
		        if($med_form->validate($_POST))
		        {
		            $med_form->insert($_POST);
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $med_form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }
		}
		
		public function edittypeAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    
		    $this->_helper->viewRenderer('addtype');
		
		    if($this->getRequest()->isPost())
		    {
		        if ($_GET['id'] > 0)
		        {
		            $type_id= $_GET['id'];
		        }
		
		        $med_form = new Application_Form_MedicationType();
		
		        if($med_form->validate($_POST))
		        {
		            $_POST['id'] = $type_id;
		            $med_form->update($_POST);
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $med_form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }

		    if ($_GET['id'] > 0)
		    {
		        $u_id = $_GET['id'];
		        $fdoc = Doctrine::getTable('MedicationType')->find($u_id );
		
		        if ($fdoc)
		        {
		            $fdocarray = $fdoc->toArray();
		            $this->retainValues($fdocarray);
		        }
		    }
		}
		
		public function deletetypeAction ()
		{
		    $this->_helper->viewRenderer('list');
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		
		    if($_GET['id'])
		    {

		        $thrash = Doctrine::getTable('MedicationType')->find($_GET['id']);
		        $thrash->isdelete = 1;
		        $thrash->save();
		
		        $this->_redirect(APP_BASE . "medicationnew/typelist");
		        exit;
		    }
		}
		
		
		
		
		/* ##################################################################### */
		/* ############ medication indications list :: Indikation  ############# */
		/* ##################################################################### */

		public function indicationlistAction()
		{
		    set_time_limit(0);
		    $clientid = $this->clientid;
		}
		
		
		public function getindicationlistAction()
		{
		    $clientid = $this->clientid;
		    
		    $this->_helper->viewRenderer->setNoRender();
		     
		    if(!$_REQUEST['length']){
		        $_REQUEST['length'] = "100";
		    }
		
		    $limit = $_REQUEST['length'];
		    $offset = $_REQUEST['start'];
		    $search_value = $_REQUEST['search']['value'];
		     
		    if(!empty($_REQUEST['order'][0]['column'])){
		        $order_column = $_REQUEST['order'][0]['column'];
		    } else{
		        $order_column = "1";
		    }
		    $order_dir = $_REQUEST['order'][0]['dir'];
		     
		    $columns_array = array(
		        "1" => "indication"
		    );
		     
		    $order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
		     
		    // ########################################
		    // #####  Query for count ###############
		    $fdoc1 = Doctrine_Query::create();
		    $fdoc1->select('count(*)');
		    $fdoc1->from('MedicationIndications');
		    $fdoc1->where("isdelete = 0  ");
		    $fdoc1->andWhere("clientid = ".$clientid);
		    $fdoc1->andWhere("extra = 0");
		    /* ------------- Search options ------------------------- */
		    if (isset($search_value) && strlen($search_value) > 0)
		    {
		        $fdoc1->andWhere("indication like '%" . trim($search_value) . "%'  ");
		    }
		    $fdoc1->orderBy($order_by_str);
		    $fdocexec = $fdoc1->execute();
		    $fdocarray = $fdocexec->toArray();
		     
		    $full_count  = $fdocarray[0]['count'];
		     
		    // ########################################
		    // #####  Query for details ###############
		    $sql = '*,';
		    $fdoc1->select($sql);
		    $fdoc1->where("isdelete = 0  ");
		    $fdoc1->andWhere("clientid = ".$clientid);
		    $fdoc1->andWhere("extra = 0");
		    /* ------------- Search options ------------------------- */
		    if (isset($search_value) && strlen($search_value) > 0)
		    {
		        $fdoc1->andWhere("indication like '%" . trim($search_value) . "%'     ");
		    }
		    $fdoc1->limit($limit);
		    $fdoc1->offset($offset);
		    $fdoclimitexec = $fdoc1->execute();
		    $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
		     
		    $report_ids[] = '99999999999999';
		    foreach ($fdoclimit as $key => $report)
		    {
		        $fdoclimit_arr[$report['id']] = $report;
		        $report_ids[] = $report['id'];
		    }
		
		    $all_users = Pms_CommonData::get_client_users($clientid, true);
		
		    foreach($all_users as $keyu => $user)
		    {
		        $all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
		    }
		
		
		    $row_id = 0;
		    $link = "";
		    $resulted_data = array();
		    foreach($fdoclimit_arr as $report_id =>$mdata){
		        $link = '%s ';
		         
		        $resulted_data[$row_id]['indication'] = sprintf($link,$mdata['indication']);
// 		        $resulted_data[$row_id]['create_date'] = sprintf($link,date('d.m.Y H:i',strtotime($mdata['create_date'])));
// 		        $resulted_data[$row_id]['created_by'] = sprintf($link,$all_users_array[$mdata['create_user']]);
// 		        $resulted_data[$row_id]['created_by'] = sprintf($link,$all_users_array[$mdata['create_user']]);
		
		        $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medicationnew/editindication?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
		        $row_id++;
		    }
		     
		    $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		    $response['recordsTotal'] = $full_count;
		    $response['recordsFiltered'] = $full_count; // ??
		    $response['data'] = $resulted_data;
		    
		    header("Content-indication: application/json; charset=UTF-8");
		    
		    
		    echo json_encode($response);
		    exit;
		}
		
		
		public function addindicationAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    
		    if($this->getRequest()->isPost())
		    {
		        $med_form = new Application_Form_MedicationIndications();
		
		        if($med_form->validate($_POST))
		        {
		            $med_form->insert($_POST);
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $med_form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }
		}
		
		public function editindicationAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    
		    $this->_helper->viewRenderer('addindication');
		
		    if($this->getRequest()->isPost())
		    {
		        if ($_GET['id'] > 0)
		        {
		            $indication_id= $_GET['id'];
		        }
		
		        $med_form = new Application_Form_MedicationIndications();
		
		        if($med_form->validate($_POST))
		        {
		            $_POST['id'] = $indication_id;
		            $med_form->update($_POST);
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $med_form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }

		    if ($_GET['id'] > 0)
		    {
		        $u_id = $_GET['id'];
		        $fdoc = Doctrine::getTable('MedicationIndications')->find($u_id );
		
		        if ($fdoc)
		        {
		            $fdocarray = $fdoc->toArray();
		            $this->retainValues($fdocarray);
		        }
		    }
		}
		
		public function deleteindicationAction ()
		{
		    $this->_helper->viewRenderer('list');
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		
		    if($_GET['id'])
		    {

		        $thrash = Doctrine::getTable('MedicationIndications')->find($_GET['id']);
		        $thrash->isdelete = 1;
		        $thrash->save();
		
		        $this->_redirect(APP_BASE . "medicationnew/indicationlist");
		        exit;
		    }
		}
	
		

		/* ##################################################################### */
		/* ############ medication dosage form list :: Darreichungsform  ####################### */
		/* ##################################################################### */
		
		public function dosageformlistAction()
		{
		    set_time_limit(0);
		    $clientid = $this->clientid;
		}
		
		
		public function getdosageformlistAction()
		{
		    $clientid = $this->clientid;
		
		    $this->_helper->viewRenderer->setNoRender();
		    
		    $medsets = new MedicationsSetsItems();
		    $medsetsitems = $medsets->get_all_sets_medications($clientid);
		   
		    $total_dos_attached = array();
		    foreach($medsetsitems as $kr=>$vr)
		    {
		    	if($vr['med_dosage_form'])
		    	{
		    		foreach($vr['med_dosage_form'] as $kf=>$vf)
		    		{
		    			$total_dos_attached[] = $vf;
		    		}
		    	}
		    }
		    $total_dos_attached = array_unique($total_dos_attached);
		    //print_r($total_dos_attached); exit;
		     
		    if(!$_REQUEST['length'])
		    {
		        $_REQUEST['length'] = "100";
		    }
		
		    $limit = $_REQUEST['length'];
		    $offset = $_REQUEST['start'];
		    $search_value = $_REQUEST['search']['value'];
		
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
		        "1" => "dosage_form"
		    );
		     
		    $order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
		     
		    // ########################################
		    // #####  Query for count ###############
		    $fdoc1 = Doctrine_Query::create();
		    $fdoc1->select('count(*)');
		    $fdoc1->from('MedicationDosageform');
		    $fdoc1->where("isdelete = 0  ");
		    $fdoc1->andWhere("clientid = ".$clientid);
		    $fdoc1->andWhere("extra = 0");
		    /* ------------- Search options ------------------------- */
		    if (isset($search_value) && strlen($search_value) > 0)
		    {
		        $fdoc1->andWhere("dosage_form like '%" . trim($search_value) . "%'  ");
		    }
		    $fdoc1->orderBy($order_by_str);
		    $fdocexec = $fdoc1->execute();
		    $fdocarray = $fdocexec->toArray();
		     
		    $full_count  = $fdocarray[0]['count'];
		     
		    // ########################################
		    // #####  Query for details ###############
		    $sql = '*,';
		    $fdoc1->select($sql);
		    $fdoc1->where("isdelete = 0  ");
		    $fdoc1->andWhere("clientid = ".$clientid);
		    $fdoc1->andWhere("extra = 0");
		    /* ------------- Search options ------------------------- */
		    if (isset($search_value) && strlen($search_value) > 0)
		    {
		        $fdoc1->andWhere("dosage_form like '%" . trim($search_value) . "%'     ");
		    }
		    $fdoc1->limit($limit);
		    $fdoc1->offset($offset);
		    $fdoclimitexec = $fdoc1->execute();
		    $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
		     
		    $report_ids[] = '99999999999999';
		    foreach ($fdoclimit as $key => $report)
		    {
		        $fdoclimit_arr[$report['id']] = $report;
		        $report_ids[] = $report['id'];
		    }

		    $all_users = Pms_CommonData::get_client_users($clientid, true);

		    foreach($all_users as $keyu => $user)
		    {
		        $all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
		    }

		    $row_id = 0;
		    $link = "";
		    $resulted_data = array();
		    foreach($fdoclimit_arr as $report_id =>$mdata)
		    {
		        $link = '%s ';
		        
		        if(in_array($mdata['id'], $total_dos_attached))
		        {
		        	$resulted_data[$row_id]['dosage_form'] = sprintf($link,'<span>!</span>'.$mdata['dosage_form']);
		        	$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medicationnew/editdosageform?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a>';
		        }
		        else
		        {
		        	$resulted_data[$row_id]['dosage_form'] = sprintf($link,$mdata['dosage_form']);
		        	$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medicationnew/editdosageform?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
		        }
		        
		        $row_id++;
		    }
		     
		    $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		    $response['recordsTotal'] = $full_count;
		    $response['recordsFiltered'] = $full_count; // ??
		    $response['data'] = $resulted_data;
		     
		    header("Content-type: application/json; charset=UTF-8");
		
		    echo json_encode($response);
		    exit;
		}
		
		public function adddosageformAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		
		    if($this->getRequest()->isPost())
		    {
		        $med_form = new Application_Form_MedicationDosageform();
		
		        if($med_form->validate($_POST))
		        {
		            $med_form->insert($_POST);
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $med_form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }
		}
		
		public function editdosageformAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		
		    $this->_helper->viewRenderer('adddosageform');
		
		    if($this->getRequest()->isPost())
		    {
		        if ($_GET['id'] > 0)
		        {
		            $dosageform_id= $_GET['id'];
		        }
		
		        $med_form = new Application_Form_MedicationDosageform();
		
		        if($med_form->validate($_POST))
		        {
		            $_POST['id'] = $dosageform_id;
		            $med_form->update($_POST);
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $med_form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }
		
		    if ($_GET['id'] > 0)
		    {
		        $u_id = $_GET['id'];
		        $fdoc = Doctrine::getTable('MedicationDosageform')->find($u_id );
		
		        if ($fdoc)
		        {
		            $fdocarray = $fdoc->toArray();
		            $this->retainValues($fdocarray);
		        }
		    }
		}
		
		public function deletedosageformAction ()
		{
		    $this->_helper->viewRenderer('list');
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		
		    if($_GET['id'])
		    {
		
		        $thrash = Doctrine::getTable('MedicationDosageform')->find($_GET['id']);
		        $thrash->isdelete = 1;
		        $thrash->save();
		
		        $this->_redirect(APP_BASE . "medicationnew/dosageformlist");
		        exit;
		    }
		}
		
		//get view list medication frequency - ISPC-2247
		public function listmedicationfrequencyAction(){
			//$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $this->clientid;
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($_GET['action']=='delete')
			{
				$entity = Doctrine::getTable('MedicationFrequency')->find($_GET['id']);
				$entity->delete();
				$this->_redirect(APP_BASE . "medicationnew/listmedicationfrequency");
			}
		
			//populate the datatables
			if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				
				$medsets = new MedicationsSetsItems();
				$medsetsitems = $medsets->get_all_sets_medications($clientid);
				
				$total_freq_attached = array();
				foreach($medsetsitems as $kr=>$vr)
				{
					if($vr['frequency'])
					{
						foreach($vr['frequency'] as $kf=>$vf)
						{
							$total_freq_attached[] = $vf;
						}
					}
				}
				$total_freq_attached = array_unique($total_freq_attached);
				//print_r($total_freq_attached); exit;
				
				if(!$_REQUEST['length']){
					$_REQUEST['length'] = "25";
				}
				$limit = (int)$_REQUEST['length'];
				$offset = (int)$_REQUEST['start'];
				$search_value = addslashes($_REQUEST['search']['value']);
		
				$columns_array = array(
						"0" => "frequency"
				);
				$columns_search_array = $columns_array;
				
				if(isset($_REQUEST['order'][0]['column']))
				{
					$order_column = $_REQUEST['order'][0]['column'];
					$order_dir = $_REQUEST['order'][0]['dir'];
				}
				else
				{
					array_push($columns_array, "id");
					$nrcol = array_search ('id', $columns_array);
					$order_column = $nrcol;
					$order_dir = "ASC";
				}
				
				$order_by_str ='CONVERT(CONVERT('.addslashes(htmlspecialchars($columns_array[$order_column])).' USING BINARY) USING utf8) '.$order_dir;
				// ########################################
				// #####  Query for count ###############
				$fdoc1 = Doctrine_Query::create();
				$fdoc1->select('count(*)');
				$fdoc1->from('MedicationFrequency');
				$fdoc1->where("clientid = ?", $clientid);
				$fdoc1->andWhere("extra = 0");
				//$fdoc1->andWhere("isdelete = 0 ");
		
				$fdocarray = $fdoc1->fetchArray();
				$full_count  = $fdocarray[0]['count'];
		
				/* ------------- Search options ------------------------- */
				if (isset($search_value) && strlen(trim($search_value)) > 0)
				{
					$comma = '';
					$filter_string_all = '';
					
					foreach($columns_search_array as $ks=>$vs)
					{
						$filter_string_all .= $comma.$vs;
						$comma = ',';
					}			
				
					$regexp = trim($search_value);
					Pms_CommonData::value_patternation($regexp);
					
					$searchstring = mb_strtolower(trim($search_value), 'UTF-8');
					$searchstring_input = trim($search_value);
					if(strpos($searchstring, 'ae') !== false || strpos($searchstring, 'oe') !== false || strpos($searchstring, 'ue') !== false)
					{
						if(strpos($searchstring, 'ss') !== false)
						{
							$ss_flag = 1;
						}
						else
						{
							$ss_flag = 0;
						}
						$regexp = Pms_CommonData::complete_patternation($searchstring_input, $regexp, $ss_flag);
					}
					
					$filter_search_value_arr[] = 'CONVERT( CONCAT_WS(\' \','.$filter_string_all.' ) USING utf8 ) REGEXP ?';
					$regexp_arr[] = $regexp;
					
					//var_dump($regexp_arr);
					$fdoc1->andWhere($filter_search_value_arr[0] , $regexp_arr);
					//$search_value = strtolower($search_value);
					//$fdoc1->andWhere("(lower(name) like ? or lower(manufacturer) like ?)", array("%" . trim($search_value) . "%", "%" . trim($search_value) . "%"));
				}
		
				$fdocarray = $fdoc1->fetchArray();
				$filter_count  = $fdocarray[0]['count'];
		
				// ########################################
				// #####  Query for details ###############
				$fdoc1->select('*');
				
				$fdoc1->orderBy($order_by_str);
				$fdoc1->limit($limit);
				$fdoc1->offset($offset);
		
				$fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
		
				$report_ids = array();
				$fdoclimit_arr = array();
				foreach ($fdoclimit as $key => $report)
				{
					$fdoclimit_arr[$report['id']] = $report;
					$report_ids[] = $report['id'];
				}
		
				$row_id = 0;
				$link = "";
		
				$resulted_data = array();
				foreach($fdoclimit_arr as $report_id =>$mdata)
				{
					$link = '%s';
					
					if(in_array($mdata['id'], $total_freq_attached))
					{
						$resulted_data[$row_id]['frequency'] = sprintf($link,'<span>!</span>'.$mdata['frequency']);
						$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medicationnew/addmedicationfrequency?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a>';
					}
					else
					{
						$resulted_data[$row_id]['frequency'] = sprintf($link,$mdata['frequency']);
						$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medicationnew/addmedicationfrequency?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
					}
					
					$row_id++;
				}
		
				$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
				$response['recordsTotal'] = $full_count;
				$response['recordsFiltered'] = $filter_count; // ??
				$response['data'] = $resulted_data;
		
				$this->_helper->json->sendJson($response);
			}
		
		}
		
		public function addmedicationfrequencyAction()
		{		
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($_REQUEST['id'])
			{
				$id = $_REQUEST['id'];
			}
			
			$saved_values = $this->_medicationfrequency_GatherDetails($id);
		
			$form = new Application_Form_MedicationFrequency(array(
					'_block_name'           => 'MEDICATIONFREQUENCY'
			));
			
			//print_r($saved_values); exit;
			$form->create_form_medicationfrequency($saved_values);			
			
			
			//@todo : move messages in layout
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
			
			$this->view->form = $form;
		
			if($this->getRequest()->isPost())
			{
				$post = $_POST['freq_table']['frequency'];
				$post['clientid'] = $_POST['clientid'];
				$post['id'] = $_POST['id'];
				$medfreq  = $form->save_form_medicationfrequency($post);
				
				if($_POST['id'])
				{
					$this->_redirect(APP_BASE . "medicationnew/listmedicationfrequency");
				}
				
			}
		}
		
		private function _medicationfrequency_GatherDetails( $id = null)
		{
			$entity  = new MedicationFrequency();
			$saved_formular_final = array();
			$saved_formular= $entity->findOrCreateOneById($id);
			//print_r($saved_formular);exit;
			if(!$saved_formular)
			{
				$saved_formular= $entity->getTable()->getFieldNames();
					
				foreach($saved_formular as $kcol=>$vcol)
				{
					$saved_formular_final[$vcol]['colprop'] = $entity->getTable()->getDefinitionOf($vcol);
					$saved_formular_final[$kcol]['value'] = null;
				}
			}
			else
			{
				foreach($saved_formular as $kcol=>$vcol)
				{
					$saved_formular_final[$kcol]['colprop'] = $entity->getTable()->getDefinitionOf($kcol);
					$saved_formular_final[$kcol]['value'] = $vcol;
				}
			}
			//print_r($saved_formular_final); exit;
			return $saved_formular_final;
		}
		
		
	}

?>