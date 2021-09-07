<?php
class VoluntaryworkersController extends Pms_Controller_Action
{
	public $act;
	
// 	protected $logininfo = null;

	public function init ()
	{
// 		/* Initialize action controller here */

		// Maria:: Migration ISPC to CISPC 08.08.2020
// 		array_push($this->actions_with_js_file, "sendemail2vwshistory");
		
	    
	    
	    // ISPC-2609 Ancuta 12.09.2020
	    $this->user_print_jobs = 1;
	    //
	    
		
		$this
		->setActionsWithJsFile([
		    /*
		     * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
		*/
		    'sendemail2vwshistory',
		    'voluntaryworkerscoloraliases',
		]);
		
		
// 	    setlocale(LC_ALL, 'de_DE.utf-8');
	    
// 	    $logininfo = new Zend_Session_Namespace('Login_Info');
// 	    $this->logininfo = $logininfo;
// 	    $this->clientid = $logininfo->clientid;
// 	    $this->userid = $logininfo->userid;
// 	    $this->usertype = $logininfo->usertype;
// 	    $this->filepass = $logininfo->filepass;
// 	    if(!$logininfo->clientid)
// 	    {
// 	        //redir to select client error
// 	        $this->_redirect(APP_BASE . "error/noclient");
// 	        exit;
// 	    }
	    
	}

	public function addvoluntaryworkerAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');

		$this->view->activities_key = 1;

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		if ($this->getRequest()->isPost())
		{
			$fdoctor_form = new Application_Form_Voluntaryworkers();

			if ($fdoctor_form->validate($_POST))
			{
				if(!empty($_POST['working_week_days']) ){
					$_POST['working_week_days'] = implode(',',$_POST['working_week_days']);
				} else{
					$_POST['working_week_days'] = "";
				}

				if(!empty($_POST['working_hours']) ){
					$_POST['working_hours'] = implode(',',$_POST['working_hours']);
				} else{
					$_POST['working_hours'] = "";
				}

				$fdoctor_form->InsertDataClient($_POST);
				$this->clear_image_details();
				$this->_redirect(APP_BASE . 'voluntaryworkers/voluntaryworkerslist?flg=suc');
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}
			else
			{
				$fdoctor_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if ($this->clientid > 0)
		{
			$where = ' and clientid=' . $this->clientid;
		}
		else
		{
			$where = ' and clientid=0';
		}

		$fdoc1 = Doctrine_Query::create()
		->select('*')
		->from('Hospiceassociation')
		->where("indrop= 0 and isdelete = 0 " . $where);
		$fdocarray = $fdoc1->fetchArray();

		if (count($fdocarray) > 0)
		{
			foreach ($fdocarray as $key => $hassoc)
			{
				$h_associations[$key]['id'] = $hassoc['id'];
				$h_associations[$key]['name'] = $hassoc['hospice_association'];
			}
		}
		$this->view->h_associations = $h_associations;		
		$pri_status_array = Pms_CommonData::get_primary_voluntary_statuses();
		$this->view->pri_status_array = $pri_status_array;
			
		//ISPC-2054(voluntaryworkers statuses updated by clients)
		//$status_array = Pms_CommonData::getVoluntaryWorkersStatuses();
		$status_array = VoluntaryWorkersSecondaryStatuses::get_secondarystatuses($this->clientid);
		
		$this->view->status_array = $status_array;
			
		//var_dump($status_array); exit;
	}
	
	public function addvoluntaryworkerdetAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');

		$this->view->activities_key = 1;

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		//ISPC-2884,Elena,14.04.2021
		$this->view->genders = Pms_CommonData::getGenderMember();
		
	    // get associated clients of current clientid START
	    $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
	    if($connected_client){
	        $clientid = $connected_client;
	    } else{
	        $clientid = $this->clientid;
	    }
	    // shortcuts
	    
	    $shortcuts = Pms_CommonData::get_voluntary_shortcuts();
	    $this->view->all_available_shortcuts = $shortcuts;
	    
	    
	    foreach ($shortcuts as $key => $value)
	    {
	        $lettersforjs[] = $value['shortcut'];
	    }
	    $this->view->shorcuts_js = json_encode($lettersforjs);
	    
	    // event types
	    $event_types = TeamEventTypes::get_team_event_types ($clientid,true );
	    	
	    foreach($event_types as $ket =>$vet){
	        $event_types_select[$vet['id']] = $vet;
	    }
	    
	    foreach($event_types_select as $type_id=>$type_data) {
	    	$sel_str .= '<option value="'.$type_id.'">'.$type_data['name'].'</option>';
	    }
	    
	    $this->view->event_types_select = $event_types_select;
	    $this->view->sel_str = $sel_str;
		
		// Hospiz v -  visits from patients (tab hospiz-v)
		$hospizv = new PatientHospizvizits();
		$this->view->grundarray = $hospizv->gethospizvreason();
		$this->view->s_hospiz_vizits_nr = 1000;
		$this->view->b_hospiz_vizits_nr = 5000;

		$this->view->s_work_nr = 9000;
		$this->view->b_work_nr = 11000;
		
		//ISPC-2401 pct6,7
		$color_statuses = array();
		$all_colors = VoluntaryworkersColorAliasesTable::get_coloralias_array($clientid);
		$modules = new Modules();
	    $saved_colors = VoluntaryworkersColorAliasesTable::findAllVoluntaryworkerscoloraliases($clientid);
	
	    if(!empty($saved_colors)){
	        foreach($saved_colors as $csk=>$csvalue){
	            $all_colors[$csvalue['color']] = $csvalue['colorname'];
	        }
	    }
		
		
		foreach ($all_colors as $status_id => $col_status_name){
		    $color_statuses[$status_id] = 'ready_'.$status_id;
		}
		$this->view->color_statuses = $color_statuses;
		$this->view->all_colors = $all_colors;
		
		// patient details
		$ipidq = Doctrine_Query::create()
		->select("epid,ipid")
		->from('EpidIpidMapping')
		->where('clientid = "' . $this->clientid . '"');
		$ipids_array = $ipidq->fetchArray();
		
		foreach($ipids_array as $pe_val){
		    $ipid2epid[$pe_val['epid']] = $pe_val['ipid'];
		}
 
		
		if ($this->getRequest()->isPost())
		{
			$fdoctor_form = new Application_Form_Voluntaryworkers();

			if ($fdoctor_form->validate($_POST))
			{
			    $a_post = $_POST;
			    $a_post['clientid'] = $clientid;
 
				$new_vw_id = $fdoctor_form->InsertData($a_post);
				
				
				$course_form = new Application_Form_VoluntaryworkersCourse();
				
				if(!empty($a_post['vw_course'])){
				    $allow_course_insert = '0';
				
				    foreach($a_post['vw_course'] as $course_details=>$carray){
				        foreach($carray as $k=>$value){
				            if(strlen($value) > 0){
				                $allow_course_insert += '1';
				            }
				        }
				    }
				
				    if($allow_course_insert > 0 )
				    {
				        $course_form->InsertData($a_post['vw_course'],$new_vw_id);
				    }
				}
				
				
				
				if($_POST['fileuploads'] > 0 && $new_vw_id){
				     
				    $ftype = $_SESSION['filetype'];
				    if($ftype)
				    {
				        $filetypearr = explode("/", $ftype);
				        if($filetypearr[1] == "vnd.openxmlformats-officedocument.spreadsheetml.sheet")
				        {
				            $filetype = "XLSX";
				        }
				        elseif($filetypearr[1] == "vnd.openxmlformats-officedocument.wordprocessingml.document")
				        {
				            $filetype = "docx";
				        }
				        elseif($filetypearr[1] == "X-OCTET-STREAM")
				        {
				            $filetype = "PDF";
				        }
				        else
				        {
				            $filetype = $filetypearr[1];
				        }
				    }
				     
				    $upload_form = new Application_Form_ClientFileUpload();
				     
				    $a_post = $_POST;
				    $a_post['clientid'] = $clientid;
				    $a_post['filetype'] = $_SESSION['filetype'];
				    $a_post['tabname'] = 'voluntary_worker';
				    $a_post['recordid'] = $new_vw_id;
				     
				    if($upload_form->validate($a_post))
				    {
				        $upload_form->insertData($a_post);
				    }
				    else
				    {
				        $upload_form->assignErrorMessages();
				        $this->retainValues($_POST);
				    }
				     
				    //remove session stuff
				    $_SESSION['filename'] = '';
				    $_SESSION['filetype'] = '';
				    $_SESSION['filetitle'] = '';
				    unset($_SESSION['filename']);
				    unset($_SESSION['filetype']);
				    unset($_SESSION['filetitle']);
				}
				
				
				$this->clear_image_details();
				
				if ($_POST['action_redirect'] == "btnsubmit_and_close") {  
				    //ISPC-2560 Lore 02.03.2020
				    $refer_tab = 0;
				    if($_POST['referal_tab'] == 'ineducation' || $_POST['referal_tab'] == 'isarchived'){
				        if($_POST['referal_tab'] == 'ineducation'){
				            $refer_tab = 2;
				        }
				        if($_POST['referal_tab'] == 'isarchived'){
				            $refer_tab = 1;
				        }
				    }
				    $this->_redirect(APP_BASE . 'voluntaryworkers/workersdetailslist?referal_tab='.$refer_tab, array("exit" => true));
				    
					//$this->_redirect(APP_BASE . 'voluntaryworkers/workersdetailslist?flg=suc');
				} else {
					$this->redirect(APP_BASE . 'voluntaryworkers/editvoluntaryworkerdet?flg=suc&id='.$new_vw_id, array("exit" => true));
				}
				
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}
			else
			{
				$fdoctor_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
		

		if ($clientid > 0)
		{
			$where = ' and clientid=' . $clientid;
		}
		else
		{
			$where = ' and clientid=0';
		}

		$fdoc1 = Doctrine_Query::create()
		->select('*')
		->from('Hospiceassociation')
		->where("indrop= 0 and isdelete = 0 " . $where);
		$fdocarray = $fdoc1->fetchArray();

		if (count($fdocarray) > 0)
		{
			foreach ($fdocarray as $key => $hassoc)
			{
				$h_associations[$key]['id'] = $hassoc['id'];
				$h_associations[$key]['name'] = $hassoc['hospice_association'];
			}
		}
		$this->view->h_associations = $h_associations;
		
		$pri_status_array = Pms_CommonData::get_primary_voluntary_statuses();
		$this->view->pri_status_array = $pri_status_array;
			
		//ISPC-2054(voluntaryworkers statuses updated by clients)
		//$status_array = Pms_CommonData::getVoluntaryWorkersStatuses();
		$status_array = VoluntaryWorkersSecondaryStatuses::get_secondarystatuses($clientid, true);
		
		$this->view->status_array = $status_array;
			
		//var_dump($status_array); exit;		
		
        $arr_status_color[0]['start_date'] = date('d.m.Y');
	    $arr_status_color[0]['end_date'] = "";
	    $arr_status_color[0]['status'] =  "g";
	    
		$this->view->status_color_array = $arr_status_color;
		
		//ISPC-2618 Carmen 31.07.2020
		//ISPC-1977 p5 get Koordinators
		$get_koordinators = Voluntaryworkers::get_koordinators ($clientid);
		$koordinators_array =  array("0"=>"");
		if (! empty($get_koordinators)) {
			foreach ($get_koordinators as $row) {
				$koordinators_array[ $row['id'] ] = $row['last_name'] .", " .$row['first_name'];
			}
		}
		$this->view->koordinators_array = $koordinators_array;

		//ISPC-1977 p5 get Koordinator voluntaryworkers
		/* $get_koordinators = Voluntaryworkers::get_koordinators ($clientid);
		$koordinators_array =  array();
		if (! empty($get_koordinators)) {
			foreach ($get_koordinators as $row) {
				 $koordinators_array[ $row['id'] ] = $row['last_name'] .", " .$row['first_name'];
			}
		}
		$this->view->koordinators_array = $koordinators_array; */
		//--
	}

	public function editvoluntaryworkerAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		
		$this->view->act = "voluntaryworkers/editvoluntaryworker?id=" . $_GET['id'];

		$this->_helper->viewRenderer('addvoluntaryworker');
		if ($this->getRequest()->isPost())
		{
			$fdoctor_form = new Application_Form_Voluntaryworkers();
				

			if ($fdoctor_form->validate($_POST))
			{
				$a_post = $_POST;
				$did = $_GET['id'];
				$a_post['did'] = $did;
				$a_post['clientid'] = $this->clientid;


				if(!empty($_POST['working_week_days']) ){
					$a_post['working_week_days'] = implode(',',$_POST['working_week_days']);
				} else{
					$a_post['working_week_days'] = "";
				}

				if(!empty($_POST['working_hours']) ){
					$a_post['working_hours'] = implode(',',$_POST['working_hours']);
				} else{
					$a_post['working_hours'] = "";
				}

				$fdoctor_form->UpdateDataClient($a_post);
				$this->clear_image_details();
				$this->_redirect(APP_BASE . 'voluntaryworkers/voluntaryworkerslist?flg=suc');
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}
			else
			{
				$fdoctor_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if ($_GET['id'] > 0)
		{
			$fdoc = Doctrine::getTable('Voluntaryworkers')->find($_GET['id']);

			if ($fdoc)
			{
				$fdocarray = $fdoc->toArray();
				$this->retainValues($fdocarray);

				if(!empty($fdocarray['working_hours'])){
					$this->view->working_hours = explode(',',$fdocarray['working_hours']);
				}

				if(!empty($fdocarray['working_week_days'])){
					$this->view->working_week_days = explode(',',$fdocarray['working_week_days']);
				}

				//get existing activities

				$vw_activities = new VoluntaryworkersActivities();
				$vw_activities_details = $vw_activities->get_vw_activities($_GET['id']) ;

				if(!empty($vw_activities_details)){
					foreach($vw_activities_details as $ko => $a_va){
						$activities[$a_va['id']] = $a_va;
					}
				}
				$last_key =  end(array_keys($activities));
				
				$activities_key = $last_key + 10;
				$this->view->activities = $activities;
				$this->view->activities_key = $activities_key;
			}

			$clientid = $fdocarray['clientid'];
			if ($clientid > 0 || $this->clientid > 0)
			{
				if ($clientid > 0)
				{
					$client = $clientid;
				}
				else if ($this->clientid > 0)
				{
					$client = $this->clientid;
				}

				$client = Doctrine_Query::create()
				->select("*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
						AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
						AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
						AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
						AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
						AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
						AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
						AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
						AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
						->from('Client')
						->where('id =' . $client);
				$clientarray = $client->fetchArray();

				$this->view->client_name = $clientarray[0]['client_name'];
				$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly"><input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />';


				$fdoc1 = Doctrine_Query::create()
				->select('*')
				->from('Hospiceassociation')
				->where('indrop= 0 and isdelete = 0 and clientid = ' . $this->clientid . ' ');
				$fdocarray = $fdoc1->fetchArray();

				if (count($fdocarray) > 0)
				{
					foreach ($fdocarray as $key => $hassoc)
					{
						$h_associations[$key]['id'] = $hassoc['id'];
						$h_associations[$key]['name'] = $hassoc['hospice_association'];
					}
				}
				$this->view->h_associations = $h_associations;				
				
				$pri_status_array = Pms_CommonData::get_primary_voluntary_statuses();
				$this->view->pri_status_array = $pri_status_array;
					
				//ISPC-2054(voluntaryworkers statuses updated by clients)
				//$status_array = Pms_CommonData::getVoluntaryWorkersStatuses();
				$status_array = VoluntaryWorkersSecondaryStatuses::get_secondarystatuses($this->clientid);
				
				$this->view->status_array = $status_array;
					
				//var_dump($status_array); exit;
				$voluntary_workers_statuses = new VoluntaryworkersStatuses();
				$worker_statuses = $voluntary_workers_statuses->get_voluntaryworker_statuses($_REQUEST['id'], $this->clientid);
				
				$this->view->selected_statuses = $worker_statuses[$_REQUEST['id']];
				//var_dump($worker_statuses); exit;

			}
		}
	}

    /**
     * ISPC-2834,Elena,24.03.2021
     */
	public function edithvisitdataAction(){
        set_time_limit(0);
        $this->_helper->layout->setLayout('layout_ajax');
        $this->view->error_hospiz = '';
        $clientid = $this->clientid;
        if($_REQUEST['vid'] && strlen($_REQUEST['vid']) > 0 ) {
            $this->view->vid0 = $_REQUEST['vid'];
            $sav = new PatientHospizvizits();
            $savarr = $sav->getPatienthospizvizitsById($_REQUEST['vid']);
            $this->view->grundarray = $sav->gethospizvreason();

            //get active patients
            $patietns_act = Doctrine_Query::create()
                ->select('p.ipid, e.epid')
                ->from('EpidIpidMapping e')
                ->leftJoin('e.PatientMaster p')
                ->where('e.ipid = p.ipid')
                ->andWhere('e.clientid = "' . $clientid . '"')
                ->andWhere('p.isstandby = "0"')
                ->andWhere('p.isdischarged = "0"')
                ->andWhere('p.isdelete = "0"')
                ->andWhere('p.isstandbydelete = "0"');
            $active_patients = $patietns_act->fetchArray();
            //print_r($active_patients);

            if (!empty($active_patients))
            {
                $patients_epids_selector[] = $this->view->translate('select_patient');
                foreach ($active_patients as $k_patient => $v_patient)
                {
                    $patients_epids_selector[$v_patient['PatientMaster']['id']] = strtoupper($v_patient['epid']); //used in patients dropdown
                    $patients_ipids_selector[$v_patient['PatientMaster']['id']] = $v_patient['PatientMaster']['ipid']; //used to match patient_master id with ipid
                }
            }

            $this->view->patients_epids_selector = $patients_epids_selector;
            //print_r($patients_epids_selector);
            //echo '<hr>';
            $ipid = '';


            if ($savarr[0]['id'])
            {

                $patient_id = array_search($savarr[0]['ipid'], $patients_ipids_selector);
                $ipid = $savarr[0]['ipid'];
                $patient_epid = $patients_epids_selector[ $patient_id ];
                $this->view->patient_epid = $patient_epid;
                $aNames  = PatientMaster::getPatientsNiceName([$savarr[0]['ipid']]);
                $this->view->patient_name = ($aNames[0]['nice_name']);

                $this->view->vw_id = $savarr[0]['vw_id'];
                $this->view->type = $savarr[0]['type'];
                $this->view->besuchsdauer = $savarr[0]['besuchsdauer'];
                $this->view->fahrtkilometer = $savarr[0]['fahrtkilometer'];
                $this->view->hospizvizit_date = date('d.m.Y', strtotime($savarr[0]['hospizvizit_date']));
                $this->view->fahrtzeit = $savarr[0]['fahrtzeit'];
                $this->view->grund = $savarr[0]['grund'];
                $this->view->amount = $savarr[0]['amount'];
                $this->view->nightshift = $savarr[0]['nightshift'];
                $this->view->pat_id = $patient_id;
                $this->view->pat_ipid = $savarr[0]['ipid'];
                //$this->view->pat_epid = EpidIpidMapping::g $savarr[0]['ipid'];
            }

        }
        if ($this->getRequest()->isPost() ) {
             $voluntary_form = new Application_Form_VoluntaryWorkers();
             //$post_data = json_encode()

            if ($voluntary_form->validate($_POST))
            {
                $_POST['vizitid'] = $_REQUEST['vid'];
                $_POST['ipid'] = EpidIpidMapping::getIpidFromEpidAndClientid($_POST['patient_epid'], $this->clientid);
                //$_POST['hospizvizit_date'] = $_POST[]

                $voluntary_form->UpdateData($_POST);

                //$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
                //$this->_redirect(APP_BASE . 'voluntaryworkers/editworkerdetails?flg=suc&id=' . $_REQUEST['id'] . 'show=patient');
                $res = new stdClass();
                $res->success = true;
                $res->action = "update";

                echo json_encode($res);
                exit;
            }
            else
            {

                $errors = $voluntary_form->getErrorMessages();
                $this->retainValues($_POST);
                $res = new stdClass();
                $res->success = false;
                $res->action = "update";
                $res->errors = $errors;

                echo json_encode($res);
                exit;
            }
        }
    }




	public function editworkbulkAction()
	{
		set_time_limit(0);
		$this->_helper->layout->setLayout('layout_ajax');
		$clientid = $this->clientid;
	
// 		if(empty($_REQUEST)){
// 			$this->_redirect(APP_BASE . "reportscustom/list");
// 			exit;
// 		}
	
		/* ################ GET SAVED  DATA ################################# */
		if($_REQUEST['vid'] && strlen($_REQUEST['vid']) > 0 ){
			$work_entry_id = $_REQUEST['vid'];
	
			// Hospiz v -  visits from patients (tab hospiz-v)
			$hospizv = new PatientHospizvizits();
			$grundarray = $hospizv->gethospizvreason();

			$this->view->grundarray = $grundarray;
			
			// Work data -
			$vww_model = new VwWorkdata();
			$b_work = $vww_model->get_work_data($work_entry_id, "b", false);
			
			foreach($b_work as $sk => $bw){
				$bulk_work = $bw;
				if($bw['work_date'] != "0000-00-00 00:00:00"){
					$bulk_work['work_date'] = date("d.m.Y",strtotime($bw['work_date']));
				}
			}
			$this->view->b_work = $bulk_work;
		}
		$response['error'] = array();
	
		 
		/* ########################## SAVE ################################# */
		if ($this->getRequest()->isPost() ) {
			$v_work = new Application_Form_Voluntaryworkers();
			$vol_work = $v_work->UpdateVworkdata($_POST);
			
			echo json_encode('1');
			exit;
		}
	}
	
	
	
	public function editvoluntaryworkerdetAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
        //ISPC-2884,Elena,14.04.2021
        $this->view->genders = Pms_CommonData::getGenderMember();
	

		$this->view->act = "voluntaryworkers/editvoluntaryworkerdet?id=" . $_GET['id'];

		$this->_helper->viewRenderer('addvoluntaryworkerdet');
		

		$all_users = Pms_CommonData::get_client_users($this->clientid, true);
		$all_users = $this->array_sort($all_users, 'last_name', SORT_ASC);
		
		foreach($all_users as $keyu => $user)
		{
		    $all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
		}
		$this->view->allusers = $all_users_array;
        //ISPC-2908,Elena,21.05.2021
        $this->view->usersnewtodos =  $this->get_nice_name_multiselect();

        $flat_todo_users_selectbox = array();
        foreach($this->view->usersnewtodos as $k=>$row_user) {
            if ( ! is_array( $row_user)) { $row_user = array($k => $row_user); }
            $flat_todo_users_selectbox = array_merge($flat_todo_users_selectbox, $row_user );
        }
        $this->view->usersnewtodos_flat = $flat_todo_users_selectbox;
		

		// get associated clients of current clientid START 
		$connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
		if($connected_client){
		    $clientid = $connected_client;
		} else{
		    $clientid = $this->clientid;
		}
		
		//ISPC-2401 pct6.7
		$color_statuses = array();
		$all_colors = VoluntaryworkersColorAliasesTable::get_coloralias_array($clientid);
		
		$modules = new Modules();
		$saved_colors = VoluntaryworkersColorAliasesTable::findAllVoluntaryworkerscoloraliases($clientid);
		
		if(!empty($saved_colors)){
    		foreach($saved_colors as $csk=>$csvalue){
    		    $all_colors[$csvalue['color']] = $csvalue['colorname'];
    		}
		}
		
		foreach ($all_colors as $status_id => $col_status_name){
		    $color_statuses[$status_id] = 'ready_'.$status_id;
		}
		$this->view->color_statuses = $color_statuses;
		$this->view->all_colors = $all_colors;
		
		
/* 		if ($_GET['id'] > 0)
		{
		    $fdoc = Doctrine::getTable('Voluntaryworkers')->find($_GET['id']);
		    if ($fdoc)
		    {
		        $vw_data = $fdoc->toArray();
		        
		        $associated_clients_parent = VwGroupAssociatedClients::associated_parent_client($this->clientid);
		        
		        if($associated_clients_parent){
		            if($vw_data['clientid'] != $associated_clients_parent ){
		                $this->_redirect(APP_BASE . "error/previlege");
		                exit;
		            }
		        } 
		        else
		        {
		            if($vw_data['clientid'] != $this->clientid ){
		              $this->_redirect(APP_BASE . "error/previlege");
		                exit;
		            }
		        }
		    }
		} */
		
		// ICONS 
		//get client icons 
		$vw_icons = new VoluntaryworkersIcons();
		$icons_client = new IconsClient();
		
		
		//assigned patient icons
		$vw_id = $_GET['id'];
		$vw_icons_array = $vw_icons->get_icons($vw_id);
		
		foreach($vw_icons_array as $v_icon)
		{
		    $vw_icons_ids[] = $v_icon['icon_id'];
		}
		
		
		$client_icons = $icons_client->get_client_icons($clientid, $allowed_icons['custom'],'icons_vw');

		//make hidden available icons_ids array
		foreach($client_icons as $k_client_icon => $v_client_icon)
		{
		    $client_icons_list[$k_client_icon] = $v_client_icon;
		    if(in_array($v_client_icon['id'], $vw_icons_ids))
		    {
		        $client_icons_list[$v_client_icon['id']]['visible'] = '0';
		    }
		    else
		    {
		        $client_icons_list[$v_client_icon['id']]['visible'] = '1';
		    }
		}
		$this->view->custom_icon_details = $client_icons_list;
		
		foreach($client_icons as $k_icon => $v_icon)
		{
		    if(in_array($v_icon['id'], $vw_icons_ids))
		    {
		        $vw_icons_list[] = $v_icon;
		    }
		}
		$this->view->vw_icons_details = $vw_icons_list;
		
		
		// event types
		$event_types = TeamEventTypes::get_team_event_types ($clientid,true );
			
		foreach($event_types as $ket =>$vet){
		    $event_types_select[$vet['id']] = $vet;
		}
		
		foreach($event_types_select as $type_id=>$type_data) {
			$sel_str .= '<option value="'.$type_id.'">'.$type_data['name'].'</option>';
		}
		
		$this->view->event_types_select = $event_types_select;
		$this->view->sel_str = $sel_str;
		
		//get active patients
		$patietns_act = Doctrine_Query::create()
		->select("p.ipid,p.isdischarged,p.isstandby,p.isstandbydelete,
				e.epid,
				AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name,
				AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name
				")
						->from('EpidIpidMapping e')
						->leftJoin('e.PatientMaster p')
						->where('e.ipid = p.ipid')
						->andWhere('e.clientid = "' . $this->clientid . '"')
						->andWhere('p.isdelete = "0"');
		$active_patients = $patietns_act->fetchArray();

		
		if (!empty($active_patients))
		{
		    
		    foreach ($active_patients as $k_patient => $v_patient)
		    {
		        if($v_patient['PatientMaster']['isstandby'] == "0" && $v_patient['PatientMaster']['isstandbydelete'] == "0"){
    		        $patients_epids_selector[$v_patient['PatientMaster']['id']] = strtoupper($v_patient['epid']); //used in patients dropdown
    		        $patients_ipids_selector[$v_patient['PatientMaster']['id']] = $v_patient['PatientMaster']['ipid']; //used to match patient_master id with ipid
    		        $active_ipids[] =  $v_patient['PatientMaster']['ipid'];
		        }
	            $client_ipids[]  = $v_patient['PatientMaster']['ipid'];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['name'] = $v_patient['PatientMaster']['last_name'].', '.$v_patient['PatientMaster']['first_name'];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['epid'] = $v_patient['epid'];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['epid'] = $v_patient['epid'];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['isdischarged'] = $v_patient['PatientMaster']['isdischarged'];
		        
		    }
		}
 
		if(empty($active_ipids)){
		    $active_ipids[] = "XXXXXX";
		}
		if(empty($client_ipids)){
		    $client_ipids[] = "XXXXXX";
		}

		// Hospiz v -  visits from patients (tab hospiz-v)
		$hospizv = new PatientHospizvizits();
		$this->view->grundarray = $hospizv->gethospizvreason();
		
		$s_hospiz_vizits = $hospizv->getworkervisits($_GET['id'], "n", false);
		
		foreach($s_hospiz_vizits as $sk => $sv){
		    if(in_array($sv['ipid'],$client_ipids)){
		        $single_visits[$sv['id']] = $sv;
		        $single_visits[$sv['id']]['patient'] = $patient_details[$sv['ipid']]['name'];
		        if($sv['hospizvizit_date'] != "0000-00-00 00:00:00"){
		            $single_visits[$sv['id']]['hospizvizit_date'] = date("d.m.Y",strtotime($sv['hospizvizit_date']));
		        }
		    }
		}
		$this->view->s_hospiz_vizits = $single_visits;
		$this->view->s_hospiz_vizits_nr = 1000 + count($s_hospiz_vizits);
		
		 
		 
		$b_hospiz_vizits = $hospizv->getworkervisits($_GET['id'], "b", false);
		foreach($b_hospiz_vizits as $bk => $bv){
		    if(in_array($bv['ipid'],$client_ipids)){
		        $bulk_visits[$bv['id']] = $bv;
		        $bulk_visits[$bv['id']]['patient'] = $patient_details[$bv['ipid']]['name'];
		        if($bv['hospizvizit_date'] != "0000-00-00 00:00:00"){
		            $bulk_visits[$bv['id']]['hospizvizit_date'] = date("Y",strtotime($bv['hospizvizit_date']));
		        }
		    }
		}
		 
		$this->view->b_hospiz_vizits = $bulk_visits;
		$this->view->b_hospiz_vizits_nr = 5000 + count($b_hospiz_vizits);
		

		
		
		// Work data -  
		$vww_model = new VwWorkdata();
		$s_work = $vww_model->get_vw_work_data($_GET['id'], "n", false);

		foreach($s_work as $sk => $sw){
		        $single_work[$sw['id']] = $sw;
		        if($sw['work_date'] != "0000-00-00 00:00:00"){
		            $single_work[$sw['id']]['work_date'] = date("d.m.Y",strtotime($sw['work_date']));
		        }
		}

		$this->view->s_work = $single_work;
		$this->view->s_work_nr = 9000 + count($s_work);
		
		$b_work = $vww_model->get_vw_work_data($_GET['id'], "b", false);

		foreach($b_work as $sk => $bw){
		        $bulk_work[$bw['id']] = $bw;
		        if($bw['work_date'] != "0000-00-00 00:00:00"){
		            $bulk_work[$bw['id']]['work_date'] = date("d.m.Y",strtotime($bw['work_date']));
		        }
		}
		$this->view->b_work = $bulk_work;
		//print_r($this->view->b_work); exit;
		$this->view->b_work_nr = 11000 + count($b_work);
		
		 
		 
		$b_hospiz_vizits = $hospizv->getworkervisits($_GET['id'], "b", false);
		foreach($b_hospiz_vizits as $bk => $bv){
		    if(in_array($bv['ipid'],$client_ipids)){
		        $bulk_visits[$bv['id']] = $bv;
		        $bulk_visits[$bv['id']]['patient'] = $patient_details[$bv['ipid']]['name'];
		        if($bv['hospizvizit_date'] != "0000-00-00 00:00:00"){
		            $bulk_visits[$bv['id']]['hospizvizit_date'] = date("Y",strtotime($bv['hospizvizit_date']));
		        }
		    }
		}
		 
		$this->view->b_hospiz_vizits = $bulk_visits;
		$this->view->b_hospiz_vizits_nr = 5000 + count($b_hospiz_vizits);
		
		
		$voluntary_workers_ids = array($_GET['id']);
		$voluntary_workers = new Voluntaryworkers();
		$parent2child = $voluntary_workers->parent2child_workers ($clientid,$voluntary_workers_ids);
		
		$patient_voluntary_workers = new PatientVoluntaryworkers();
		$worker2activepatients = $patient_voluntary_workers->get_workers2patients($parent2child['vw_ids'], $active_ipids,true);
		
		$kr = 0;
		foreach($worker2activepatients as $vwid=>$patipid){
		    $patient_ipids[] = $patipid['ipid'];
		}
		if(empty($patient_ipids)){
		    $patient_ipids[] = "XXXXXX";
		}
		
		
	
		
		//get  patients
		$patietns_dis = Doctrine_Query::create()
		->select("*")
		->from('PatientDischarge')
		->where('isdelete = "0"')
		->andWhereIn('ipid',$patient_ipids);
		$discharge_patients = $patietns_dis->fetchArray();
		
		foreach($discharge_patients as $k=>$pdis){
		    $discgharge_date[$pdis['ipid']] = date('d.m.Y',strtotime($pdis['discharge_date']));
		}
		
		$kr= 0;
		foreach($worker2activepatients as $vwid=>$patipid){
	        $pat2master[ $parent2child['vwid2parent'][$vwid] ][ $kr ]['entry_id'] = $patipid['id'];
	        $pat2master[ $parent2child['vwid2parent'][$vwid] ][ $kr ]['vwid'] = $patipid['vwid'];
	        $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['epid'] = $patient_details[$patipid['ipid']]['epid'];
	        $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['patient_name'] = $patient_details[$patipid['ipid']]['name'];
	        if($patipid['start_date'] != "0000-00-00 00:00:00"){
    	        $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['start'] = date('d.m.Y',strtotime($patipid['start_date']));
	        } else{
    	        $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['start'] = date('d.m.Y',strtotime($patipid['create_date']));
	        }

	        
	        if($patipid['end_date'] != "0000-00-00 00:00:00"){
	            $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['end'] = date('d.m.Y',strtotime($patipid['end_date']));
	        } else{
	            
	            if($patient_details[$patipid['ipid']]['isdischarged'] == "1"){
    	            $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['end'] = date('d.m.Y',strtotime($discgharge_date[$patipid['ipid']]));
	            } else{
    	            $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['end'] = "";
	            }
	        }
	        
            $kr++;
		}

		
		$side_sortarr = 'start';
		$pat2master = $this->array_sort($pat2master,$side_sortarr ,SORT_DESC);

		//print_r($pat2master);exit();
		$this->view->voluntary_worker_history = $pat2master;
		
		
		// patient details
		$ipidq = Doctrine_Query::create()
		->select("epid,ipid")
		->from('EpidIpidMapping')
		->where('clientid = "' . $this->clientid . '"');
		$ipids_array = $ipidq->fetchArray();
		
		foreach($ipids_array as $pe_val){
		    $ipid2epid[$pe_val['epid']] = $pe_val['ipid'];
		}
		
		if ($this->getRequest()->isPost())
		{
		    //dd($_POST);
		    //ISPC-2401 Lore 21.05.2020
		    if(!empty($_POST['patient_vw'])){     // Patient zuordnen
		        foreach($_POST['patient_vw'] as $keipvw=>$valpvw){
		            //TODO-3671 Ancuta 11.12.2020
		            if($valpvw['start'] =='' && $valpvw['patient_epid'] =='' && $valpvw['patient'] ==''){
		            //if($valpvw['start'] =='' || $valpvw['patient_epid'] =='' || $valpvw['patient'] ==''){
		                unset($_POST['patient_vw'][$keipvw]);
		            }
		        }
		    }
		    
		    if(!empty($_POST['simple'])){         // Ehrenamtlichen / Koordinator Besuch einzeln eintragen 
		        foreach($_POST['simple'] as $keis=>$vals){
		            if($vals['hospizvizit_date'] =='' || $vals['patient_epid'] =='' || $vals['patient'] ==''){
		                unset($_POST['simple'][$keis]);
		            }
		        }
		    }
		    if(!empty($_POST['bulk'])){         // Ehrenamtlichen / Koordinator Besuch kumuliert eintragen
		        foreach($_POST['bulk'] as $keib=>$valb){
		            if($valb['hospizvizit_date'] =='' || $valb['patient_epid'] =='' || $valb['patient'] ==''){
		                unset($_POST['bulk'][$keib]);
		            }
		        }
		    }
		    if(!empty($_POST['work_bulk'])){         //  allg. Arbeiten
		        foreach($_POST['work_bulk'] as $keiwb=>$valwb){
		            if($valwb['work_date'] =='' || $valwb['grund'] =='' ){
		                unset($_POST['work_bulk'][$keiwb]);
		            }
		        }
		    }
		    //.
		    
			$fdoctor_form = new Application_Form_Voluntaryworkers();
			
			$course_form = new Application_Form_VoluntaryworkersCourse();
			
			if ($fdoctor_form->validate($_POST)  )
			{
				$a_post = $_POST;
				$did = $_GET['id'];
				$a_post['did'] = $did;
				$a_post['clientid'] = $clientid;
				$a_post['vw_id'] = $_GET['id'];
				$post_vw_id = $_GET['id'];

				//save profile image
				//@TODO loading/saving profile image with other files from files-tab does not work at the same time
				if ( $_POST['profile_image_add'] == '1' ){
					$fdoctor_form->move_uploaded_icon((int)$did);
					
					//remove session stuff
					$_SESSION['filename'] = '';
					$_SESSION['filetype'] = '';
					$_SESSION['filetitle'] = '';
					unset($_SESSION['filename']);
					unset($_SESSION['filetype']);
					unset($_SESSION['filetitle']);
				}
				
				$fdoctor_form->update_from_details($a_post);
                if(!empty($a_post['vw_course'])){
                    $allow_course_insert = '0';
                    
                    foreach($a_post['vw_course'] as $course_details=>$carray){
                        foreach($carray as $k=>$value){
                            if(strlen($value) > 0){
                                $allow_course_insert += '1';
                            }
                        }
                    }
                    
                    if($allow_course_insert > 0 )
                    {
    				    $course_form->InsertData($a_post['vw_course'],$post_vw_id);
                    }
                }
				
		        if($_POST['fileuploads'] > 0){
		             
		            $ftype = $_SESSION['filetype'];
		            if($ftype)
		            {
		                $filetypearr = explode("/", $ftype);
		                if($filetypearr[1] == "vnd.openxmlformats-officedocument.spreadsheetml.sheet")
		                {
		                    $filetype = "XLSX";
		                }
		                elseif($filetypearr[1] == "vnd.openxmlformats-officedocument.wordprocessingml.document")
		                {
		                    $filetype = "docx";
		                }
		                elseif($filetypearr[1] == "X-OCTET-STREAM")
		                {
		                    $filetype = "PDF";
		                }
		                else
		                {
		                    $filetype = $filetypearr[1];
		                }
		            }
		             
		            $upload_form = new Application_Form_ClientFileUpload();
		             
		            $a_post = $_POST;
		            $a_post['clientid'] = $clientid;
		            $a_post['filetype'] = $_SESSION['filetype'];
		            $a_post['tabname'] = 'voluntary_worker';
		            $a_post['recordid'] = $did;
		             
		            if($upload_form->validate($a_post))
		            {
		                $upload_form->InsertData($a_post);
		            }
		            else
		            {
		                $upload_form->assignErrorMessages();
		                $this->retainValues($_POST);
		            }
		             
		            //remove session stuff
		            $_SESSION['filename'] = '';
		            $_SESSION['filetype'] = '';
		            $_SESSION['filetitle'] = '';
		            unset($_SESSION['filename']);
		            unset($_SESSION['filetype']);
		            unset($_SESSION['filetitle']);
		        }
		        
		        
		        $this->clear_image_details();
		        
				if ($_POST['action_redirect'] == "btnsubmit_and_close") {
				    
				    //ISPC-2560 Lore 02.03.2020
				    $refer_tab = 0;
				    
				    if($_REQUEST['ref_tab'] == 'ineducation' || $_REQUEST['ref_tab'] == 'isarchived'){
				        if($_REQUEST['ref_tab'] == 'ineducation'){
				            $refer_tab = 2;
				        }
				        if($_REQUEST['ref_tab'] == 'isarchived'){
				            $refer_tab = 1;
				        }
				    }
				    
				    $this->_redirect(APP_BASE . 'voluntaryworkers/workersdetailslist?referal_tab='.$refer_tab, array("exit" => true));
				    
					//$this->_redirect(APP_BASE . 'voluntaryworkers/workersdetailslist?flg=suc');
				} else {
					$this->redirect(APP_BASE . 'voluntaryworkers/editvoluntaryworkerdet?flg=suc&id='.$did, array("exit" => true));
				}
				
				
				
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}
			else
			{
				$fdoctor_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if ($_GET['id'] > 0)
		{
			if( $_GET['flg'] == 'suc' ){
				$this->view->error_message = $this->view->translate('recordinsertsucessfully');
			}
		    $this->clear_image_details();
			$fdoc = Doctrine::getTable('Voluntaryworkers')->find($_GET['id']);

			if ($fdoc)
			{
				$fdocarray = $fdoc->toArray();
				$vw_details = $fdocarray;
				$this->retainValues($fdocarray);

				if($fdocarray['birthdate'] != "0000-00-00"){
				    $this->view->birthdate = date('d.m.Y',strtotime($fdocarray['birthdate']));
				} else{
				    $this->view->birthdate = "";
				}
                //ISPC-2884,Elena,14.04.2021
                $this->view->gender = $fdocarray['gender'];

				
				if($fdocarray['gc_certificate_date'] != "0000-00-00 00:00:00"){
				    $this->view->gc_certificate_date = date('d.m.Y',strtotime($fdocarray['gc_certificate_date']));
				} else{
				    $this->view->gc_certificate_date = "";
				}
				
				//ISPC-2618 Carmen 30.07.2020
				if($fdocarray['received_certificate_date'] != "0000-00-00 00:00:00"){
					$this->view->received_certificate_date = date('d.m.Y',strtotime($fdocarray['received_certificate_date']));
				} else{
					$this->view->received_certificate_date = "";
				}
				//--
				
				if($fdocarray['engagement_date'] != "0000-00-00 00:00:00"){
				    $this->view->engagement_date = date('d.m.Y',strtotime($fdocarray['engagement_date']));
				} else{
				    $this->view->engagement_date = "";
				}
				
				if(!empty($fdocarray['working_hours'])){
					$this->view->working_hours = explode(',',$fdocarray['working_hours']);
				}

				if(!empty($fdocarray['working_week_days'])){
					$this->view->working_week_days = explode(',',$fdocarray['working_week_days']);
				}

				//get existing working hours
				$vw_availability = new VwAvailability();
				$vw_availability_details = $vw_availability->get_vw_availability($_GET['id']) ;
				foreach($vw_availability_details as $k=>$availability_data){
				    $availability[$availability_data['week_day']][] = $availability_data;
				}
				$this->view->availability = $availability;
				
 
				//get existing working hours
				$vw_availability_details_s = VoluntaryworkersAvailabilityScheduleTable::findvwactivityschedule($_GET['id'],$clientid);
				$availability_scheduled = array();
				foreach($vw_availability_details_s as $k=>$availabilitys_data){
				    $availability_scheduled[$availabilitys_data['week_day']] = $availabilitys_data;
				}
				$this->view->availability_scheduled = $availability_scheduled;
				
				//get existing activities

				$vw_activities = new VoluntaryworkersActivities();
				$vw_activities_details = $vw_activities->get_vw_activities($_GET['id']) ;
				$last_key =  end(array_keys($activities));
				
				if(!empty($vw_activities_details)){
					foreach($vw_activities_details as $ko => $a_va){
						$activities[$a_va['id']] = $a_va;
					}
				}
				$last_key =  reset($activities)['id'];
				//var_dump($last_key);exit;
				$activities_key = $last_key + 10;
				$this->view->activities = $activities;
				$this->view->activities_key = $activities_key;
				
				
			}
 
			$fdoc1 = Doctrine_Query::create()
			->select('*')
			->from('Hospiceassociation')
			->where('indrop= 0 and isdelete = 0 and clientid = ' . $clientid . ' ');
			$fdocarray = $fdoc1->fetchArray();

			if (count($fdocarray) > 0)
			{
				foreach ($fdocarray as $key => $hassoc)
				{
					$h_associations[$key]['id'] = $hassoc['id'];
					$h_associations[$key]['name'] = $hassoc['hospice_association'];
				}
			}
			$this->view->h_associations = $h_associations;

			$pri_status_array = Pms_CommonData::get_primary_voluntary_statuses();
			$this->view->pri_status_array = $pri_status_array;
			
			//ISPC-2054(voluntaryworkers statuses updated by clients)
			//$status_array = Pms_CommonData::getVoluntaryWorkersStatuses();
			$status_array = VoluntaryWorkersSecondaryStatuses::get_secondarystatuses($clientid, true);

			$this->view->status_array = $status_array;
			
			$voluntary_workers_statuses = new VoluntaryworkersStatuses();
			$worker_statuses = $voluntary_workers_statuses->get_voluntaryworker_statuses($_REQUEST['id'], $clientid);

			$this->view->selected_statuses = $worker_statuses[$_REQUEST['id']];
			//var_dump($this->view->selected_statuses); exit;
			$history = ClientFileUpload::get_client_files_recordid( $clientid, array ('voluntary_worker'), $_GET['id'] );
			
			foreach($history as $ke => $ve)
			{	
				if ($ve['parent_id'] == $ve['id'] || $ve['parent_id'] == 0 ){
			    	//revision 1
					
					//$files[$ve['recordid']] [$ve['id']]= $ve;
					$revisions = array();
			    	foreach($history as $k => $v){
			    		if ($v['parent_id'] == $ve['id'] && $v['isdeleted']==0)
			    		{
			    			
			    			$revisions [$v['revision']] = $v;
			    		}
			    	}
			    	krsort($revisions);
			    	$revisions = array_values($revisions);
			    	//print_r($revisions);
			    	
			    	if (isset($revisions[0])){
			    		$files[$ve['recordid']] [$ve['id']]= $revisions[0];
			    		$files[$ve['recordid']] [$ve['id']]["doc_id"] = $ve['id'];
			    		
			    		unset($revisions[0]);
			    		$revisions [] = $ve;
			    		$files[ $ve['recordid'] ] [ $ve['id'] ] ["rev"] = $revisions;
			  	
			    	}
			    	else{
			    		$files[$ve['recordid']] [$ve['id']]= $ve;
			    		$files[$ve['recordid']] [$ve['id']]["doc_id"] = $ve['id'];
			    	}
			    	
				}
			}
			$this->view->event_files = $files;
			
			
			// get statuses
			
			$color_settings = VwColorStatuses::get_color_statuses($_GET['id']);
// 			$saved_colors = VoluntaryworkersColorAliasesTable::findAllVoluntaryworkerscoloraliases($this->logininfo->clientid);
			
			
			if(empty($color_settings[$_GET['id']])){
			   
			    if($vw_details['create_date'] != "0000-00-00 00:00:00"){
                    $arr_status_color[0]['start_date'] = date("d.m.Y",strtotime($vw_details['create_date']));
			    } else  if($vw_details['change_date'] != "0000-00-00 00:00:00"){
                    $arr_status_color[0]['start_date'] = date("d.m.Y",strtotime($vw_details['change_date']));
			    } else{
                    $arr_status_color[0]['start_date'] = "";
			    }
                
			    $arr_status_color[0]['end_date'] = "";
			    $arr_status_color[0]['status'] =  $vw_details['status_color'];
			} else{
			    $arr_status_color = $color_settings[$_GET['id']];
			}
			
// dd($arr_status_color);
			$this->view->status_color_array = $arr_status_color;
					
			// get shortcuts 
			$vw_course = VoluntaryworkersCourse::getCourseData($_GET['id'], false, $chvals = 0, $start = false, $end = false, $sort_direction = 'DESC', $sort_field = 'course_date', $offset = '0', $limit = false, $page = false, $only_count = false, $first_limit = false);
			$this->view->vw_course = $vw_course;
			
			$used_sh_in_vw=array();
			foreach($vw_course as $c=>$vwc){
			    foreach ($vwc['summary'] as $s_key=>$vw_value ){
			        $used_sh_in_vw[] = $vw_value['course_type'];
			    }
			}
    		$used_sh_in_vw= array_unique($used_sh_in_vw);
			$this->view->used_sh_in_vw = $used_sh_in_vw;
		}
// 		dd($used_sh_in_vw,$vw_course);
		// shortcuts 
		
		$shortcuts = Pms_CommonData::get_voluntary_shortcuts();
		$this->view->all_available_shortcuts = $shortcuts;
		
		
		foreach ($shortcuts as $key => $value)
		{
		    $lettersforjs[] = $value['shortcut'];
		}
		$this->view->shorcuts_js = json_encode($lettersforjs);
		
		
		//1501 colors
		//ISPC-1977 p5 get Koordinators
		$get_koordinators = Voluntaryworkers::get_koordinators ($clientid);
		$koordinators_array =  array("0"=>"");
		if (! empty($get_koordinators)) {
			foreach ($get_koordinators as $row) {
				 $koordinators_array[ $row['id'] ] = $row['last_name'] .", " .$row['first_name'];
			}
		}
		$this->view->koordinators_array = $koordinators_array;
		//ISPC-1977 p5.1 get co-Koordinator for this voluntaryworker
		$vw_cok_obj = new VoluntaryworkersCoKoordinator();
		$co_koordinator = $vw_cok_obj->get_co_koordinator_by_vwid( $_GET['id'], $clientid);
		$this->view->co_koordinator = $co_koordinator;
		
	}
	


	public function savewrongcdAction()
	{
	    $this->_helper->viewRenderer->setNoRender();
	
	
	    $pc = new Application_Form_VoluntaryworkersCourse();
	    $ids = $_REQUEST['ids'];
	    $comment = $_REQUEST['comment'];
	    $val = $_REQUEST['val'];
	
	    $a_post['ids'] = $ids;
	    $a_post['comment'] = $comment;
	    $a_post['val'] = $val;

	   if($ids){
    	    $tc = $pc->UpdateWrongEntry($a_post);
	   }
	
	    $response['msg'] = "Success";
	    $response['error'] = "";
	    $response['callBack'] = "callBackWrong";
	    $response['callBackParameters'] = array();
	    $response['callBackParameters']['id'] = $_REQUEST['blockcnt'];
	    $response['callBackParameters']['val'] = $val;
	    $response['callBackParameters']['comment'] = $comment;
	
	    echo json_encode($response);
	    exit;
	}
	
	
	public function workdatalistAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->_helper->layout->setLayout('layout_ajax');
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		// Hospiz v -  visits from patients (tab hospiz-v)
		$hospizv = new PatientHospizvizits();
		$this->view->grundarray = $hospizv->gethospizvreason();
		

		// Work data -
		$vww_model = new VwWorkdata();
		$s_work = $vww_model->get_vw_work_data($_GET['id'], "n", false);
		
		foreach($s_work as $sk => $sw){
			$single_work[$sw['id']] = $sw;
			if($sw['work_date'] != "0000-00-00 00:00:00"){
				$single_work[$sw['id']]['work_date'] = date("d.m.Y",strtotime($sw['work_date']));
			}
		}
		
		$this->view->s_work = $single_work;
		$this->view->s_work_nr = 9000 + count($s_work);
		
		$b_work = $vww_model->get_vw_work_data($_GET['id'], "b", false);
		
		foreach($b_work as $sk => $bw){
			$bulk_work[$bw['id']] = $bw;
			if($bw['work_date'] != "0000-00-00 00:00:00"){
				$bulk_work[$bw['id']]['work_date'] = date("d.m.Y",strtotime($bw['work_date']));
			}
		}
		$this->view->b_work = $bulk_work;
		//print_r($this->view->b_work); exit;
		$this->view->b_work_nr = 11000 + count($b_work);
	 
	}

	public function voluntaryworkerdetailsAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('voluntaryworkers', $this->userid, 'canedit');

		// get client_patients
		$client_patients_q = Doctrine_Query::create()
            ->select("p.ipid, e.epid,	p.admission_date,p.birthd,p.isdischarged,AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name,AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name")
            ->from('EpidIpidMapping e')
			->leftJoin('e.PatientMaster p')
			->where('e.ipid = p.ipid')
			->andWhere('e.clientid = "' . $this->clientid . '"')
			->andWhere('p.isdelete = "0"');
		$client_patients_array = $client_patients_q->fetchArray();

		if (!empty($client_patients_array))
		{
		    foreach ($client_patients_array as $k_patient => $v_patient)
		    {
		        
		        $client_ipids[]  = $v_patient['PatientMaster']['ipid'];
		        if( $v_patient['PatientMaster']['isdischarged'] == 1){
		            $discharge_patients[] = $v_patient['PatientMaster']['ipid'];
		        }
		        $patient_details[$v_patient['PatientMaster']['ipid']]['epid'] = $v_patient['epid'];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['last_name'] = $v_patient['PatientMaster']['last_name'];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['first_name'] =$v_patient['PatientMaster']['first_name'];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['patient_name'] =$v_patient['PatientMaster']['last_name'].', '.$v_patient['PatientMaster']['first_name'];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['birthd'] = date('d.m.Y',strtotime($v_patient['PatientMaster']['birthd']));
		        $patient_details[$v_patient['PatientMaster']['ipid']]['admission_date'] = date('d.m.Y',strtotime($v_patient['PatientMaster']['admission_date']));
		        $patient_details[$v_patient['PatientMaster']['ipid']]['assign_date'] = $patient_assigne_date[$v_patient['PatientMaster']['ipid']];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['isdischarged'] = $v_patient['PatientMaster']['isdischarged'];
		    }
		}
		
		if(empty($client_ipids)){
		    $client_ipids[] = "999999999";
		}

		
		
		if ($_GET['id'] > 0)
		{
		    
			$fdoc = Doctrine::getTable('Voluntaryworkers')->find($_GET['id']);

			if ($fdoc)
			{

			    
			    $fdocarray = $fdoc->toArray();
				$this->retainValues($fdocarray);

				if(!empty($fdocarray['working_hours'])){
					$this->view->working_hours = explode(',',$fdocarray['working_hours']);
				}

				if(!empty($fdocarray['working_week_days'])){
					$this->view->working_week_days = explode(',',$fdocarray['working_week_days']);
				}

				//get existing activities
				$vw_activities = new VoluntaryworkersActivities();
				$vw_activities_details = $vw_activities->get_vw_activities($_GET['id']) ;

				if(!empty($vw_activities_details)){
					foreach($vw_activities_details as $ko => $a_va){
						$activities[$a_va['id']] = $a_va;
					}
				}
				$last_key =  end(array_keys($activities));
				$activities_key = $last_key + 10;
				$this->view->activities = $activities;
				$this->view->activities_key = $activities_key;
				
				
				
				
				// Hospiz v -  visits from patients (tab hospiz-v)
				$hospizv = new PatientHospizvizits();
				$this->view->grundarray = $hospizv->gethospizvreason();
				
			    $s_hospiz_vizits = $hospizv->getworkervisits($_GET['id'], "n", true);
			    foreach($s_hospiz_vizits as $sk => $sv){
			         if(in_array($sv['ipid'],$client_ipids)){
			             $single_visits[$sv['id']] = $sv;
                         $single_visits[$sv['id']]['patient'] = $patient_details[$sv['ipid']]['patient_name'];
	       		          if($sv['hospizvizit_date'] != "0000-00-00 00:00:00"){
	       		              $single_visits[$sv['id']]['hospizvizit_date'] = date("d.m.Y",strtotime($sv['hospizvizit_date']));
	       		          }
			        }
			    }
			    $this->view->s_hospiz_vizits = $single_visits;
			    $this->view->s_hospiz_vizits_nr = 1000 + count($s_hospiz_vizits);

			    
			    
			    $b_hospiz_vizits = $hospizv->getworkervisits($_GET['id'], "b", true);
			    foreach($b_hospiz_vizits as $bk => $bv){
			        if(in_array($bv['ipid'],$client_ipids)){
			            $bulk_visits[$bv['id']] = $bv;
			            $bulk_visits[$bv['id']]['patient'] = $patient_details[$bv['ipid']]['patient_name'];
			            if($bv['hospizvizit_date'] != "0000-00-00 00:00:00"){
			                $bulk_visits[$bv['id']]['hospizvizit_date'] = date("Y",strtotime($bv['hospizvizit_date']));
			            }
			        }
			    }
			    
			    $this->view->b_hospiz_vizits = $bulk_visits;
			    $this->view->b_hospiz_vizits_nr = 5000 + count($b_hospiz_vizits);
				
				
			}

			$clientid = $fdocarray['clientid'];
			
			// get associated clients of current clientid START
			$connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
			if($connected_client){
			    $clientid = $connected_client;
			} else{
			    $clientid = $this->clientid;
			}
			
			
			if ($clientid > 0 )
			{
				if ($clientid > 0)
				{
					$client = $clientid;
				}
				else if ($this->clientid > 0)
				{
					$client = $this->clientid;
				}

				// event types
				$event_types = TeamEventTypes::get_team_event_types ($clientid,true );
					
				foreach($event_types as $ket =>$vet){
				    $event_types_select[$vet['id']] = $vet;
				}
				$this->view->event_types_select = $event_types_select;
				
				
				
				// hospice association
				$fdoc1 = Doctrine_Query::create()
				->select('*')
				->from('Hospiceassociation')
				->where('indrop= 0 and isdelete = 0 and clientid = ' . $clientid. ' ');
				$hfdocarray = $fdoc1->fetchArray();

				
				if (count($hfdocarray) > 0)
				{
					foreach ($hfdocarray as $key => $hassoc)
					{
						$h_associations[$hassoc['id']] = $hassoc;
						$h_associations[$hassoc['id']]['name'] = $hassoc['hospice_association'];
					}
				}
				
				$this->view->h_associations = $h_associations;
				
				//ISPC-2401 pct6.7
				$color_statuses = array();
				$all_colors = VoluntaryworkersColorAliasesTable::get_coloralias_array($clientid);
				foreach ($all_colors as $status_id => $col_status_name){
				    $color_statuses[$status_id] = 'ready_'.$status_id;
				}
				$this->view->color_statuses = $color_statuses;
				$this->view->all_colors = $all_colors;
				
				// Statuses
				$pri_status_array = Pms_CommonData::get_primary_voluntary_statuses();
				$this->view->pri_status_array = $pri_status_array;
					
				//ISPC-2054(voluntaryworkers statuses updated by clients)
				//$status_array = Pms_CommonData::getVoluntaryWorkersStatuses();
				$status_array = VoluntaryWorkersSecondaryStatuses::get_secondarystatuses($clientid);
				
				$this->view->status_array = $status_array;
					
				//var_dump($pri_status_array); exit;
				$voluntary_workers_statuses = new VoluntaryworkersStatuses();
				$worker_statuses = $voluntary_workers_statuses->get_voluntaryworker_statuses($_REQUEST['id'], $clientid);
				$this->view->selected_statuses = $worker_statuses[$_REQUEST['id']];
				//var_dump($worker_statuses); exit;
				
				// Voluntary worker history
				$voluntary_workers_ids[] = $_REQUEST['id'];
				$voluntary_workers = new Voluntaryworkers();
				$parent2child = $voluntary_workers->parent2child_workers ($clientid,$voluntary_workers_ids);

				$patient_voluntary_workers = new PatientVoluntaryworkers();
				$worker2activepatients = $patient_voluntary_workers->get_workers2patients($parent2child['vw_ids'],false,true);

				foreach($worker2activepatients as $vwid=>$patipid){
				    if(in_array($patipid['ipid'],$client_ipids)){
					   $patient_ipids[] = $patipid['ipid'];
				    }
				}
				
 
				if(empty($patient_ipids)){
					$patient_ipids[] = "XXXXXX";
				}


				//get  patients
				$patietns_dis = Doctrine_Query::create()
				->select("*")
				->from('PatientDischarge')
				->where('isdelete = "0"')
				->andWhereIn('ipid',$patient_ipids);
				$discharge_patients = $patietns_dis->fetchArray();
				
				foreach($discharge_patients as $k=>$pdis){
				    $discgharge_date[$pdis['ipid']] = date('d.m.Y',strtotime($pdis['discharge_date']));
				}
				
				$kr= 0;
				foreach($worker2activepatients as $vwid=>$patipid){
				    if(in_array($patipid['ipid'],$client_ipids)){
    				    $pat2master[ $parent2child['vwid2parent'][$vwid] ][ $kr ]['entry_id'] = $patipid['id'];
    				    $pat2master[ $parent2child['vwid2parent'][$vwid] ][ $kr ]['vwid'] = $patipid['vwid'];
    				    $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['epid'] = $patient_details[$patipid['ipid']]['epid'];
    				    $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['patient_name'] = $patient_details[$patipid['ipid']]['patient_name'];
    
    				    if($patipid['start_date'] != "0000-00-00 00:00:00"){
    				        $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['start'] = date('d.m.Y',strtotime($patipid['start_date']));
    				    } else{
    				        $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['start'] = date('d.m.Y',strtotime($patipid['create_date']));
    				    }
    				
    				     
    				    if($patipid['end_date'] != "0000-00-00 00:00:00"){
    				        $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['end'] = date('d.m.Y',strtotime($patipid['end_date']));
    				    } else{
    				         
    				        if($patient_details[$patipid['ipid']]['isdischarged'] == "1"){
    				            $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['end'] = date('d.m.Y',strtotime($discgharge_date[$patipid['ipid']]));
    				        } else{
    				            $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['end'] = "";
    				        }
    				    }
    				     
    				    $kr++;
				    }
				}
				
				$side_sortarr = 'start';
				$pat2master = $this->array_sort($pat2master,$side_sortarr ,SORT_DESC);
				
				$this->view->voluntary_worker_history = $pat2master;
				
			}
						
		/*	$color_statuses = array(
					'g'=>'ready_green',
					'y'=>'ready_yellow',
					'r'=>'ready_red',
					'b'=>'ready_black'
			);*/
			$color_statuses = array();
			$all_colors = VoluntaryworkersColorAliasesTable::get_coloralias_array($clientid);
			foreach ($all_colors as $status_id => $col_status_name){
			    $color_statuses[$status_id] = 'ready_'.$status_id;
			}
			$this->view->color_statuses = $color_statuses;
			$this->view->all_colors = $all_colors;
		}
	}

	private function retainValues ( $values, $prefix  = '')
	{

		foreach ($values as $key => $val)
		{
			if (!is_array($val))
			{
				$this->view->$key = $val;
			}
			else
			{//retain 1 level array used in multiple hospizvbulk form
				foreach ($val as $k_val => $v_val)
				{
					if (!is_array($v_val))
					{
						$this->view->{$prefix . $key . $k_val} = $v_val;
					}
				}
			}
		}
	}

	public function voluntaryworkerslistAction ()
	{
		if ($_GET['flg'] == 'suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
		}
	}

	public function workersdetailslistAction()
	{
	    
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $userid = $this->userid;

	    // ################################################
	    // get associated clients of current clientid START
	    // ###############################################
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
	    if($connected_client){
	        $clientid = $connected_client;
	    } else{
	        $clientid = $this->clientid;
	    }
	    // ################################################
	    // get associated clients of current clientid END
	    // ###############################################
	    
	    //ISPC-2609 Ancuta 28.08.2020 + Changes on  07.09.2020
	    //get printjobs - active or completed - for client, user and invoice type
	    $allowed_invoice_name =  $client_allowed_invoice[0];
	    $this->view->allowed_invoice = $allowed_invoice_name;
	    $invoice_user_printjobs = PrintJobsBulkTable::_find_user_print_jobs($clientid,$this->userid,$this->getRequest()->getControllerName());
	    
	    $print_html = '<div class="print_jobs_div">';
	    $print_html .= "<h3> ".$this->translate('print_job_table_headline')."</h3>";
	    $print_html .= '<span id="clear_user_jobs" class="clear_user_jobs" data-user="'.$this->userid.'"  data-invoice_type="'.$allowed_invoice_name .'" data-client="'.$clientid.'"> '.$this->translate('Clear_all_prints')."</span>";
	    $table_html = $this->view->tabulate($invoice_user_printjobs,array("class"=>"datatable",'id'=>'print_jobs_table','escaped'=>false));
	    $print_html .= $table_html;
	    $print_html .= '</div>';
	    if(count($invoice_user_printjobs) > 1 ){
	        echo $print_html;
	    }
	    
	    $this->view->show_print_jobs = $this->user_print_jobs;
	    
	    //---
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
// 	    $userid = $this->userid;
	    
	    $templates_data = VwLetterTemplates::get_all_letter_templates($this->clientid );
	    if($templates_data){
	        foreach($templates_data as $k => $tpl){
	            $templates[$tpl['id']] = $tpl;
	        }
	    }
	    $this->view->letter_templates = $templates;
	    
		if ($_GET['flg'] == 'suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
		}

		$this->clear_image_details();
		
		$user_filters = UserVwFilters::get_user_filter();
		if($user_filters){
		    $ufilter = $user_filters[0];
		    $this->view->ufilter = $ufilter;
		}
//  		dd($ufilter);
		$color_statuses = array();
		$all_colors = VoluntaryworkersColorAliasesTable::get_coloralias_array($clientid);
		foreach ($all_colors as $status_id => $col_status_name){
		    $color_statuses[$status_id] = 'ready_'.$status_id;
		}
		
		$saved_colors = VoluntaryworkersColorAliasesTable::findAllVoluntaryworkerscoloraliases($clientid);
		
		
		
		
		
		
		$modules = new Modules();
		if ($modules->checkModulePrivileges("190", $clientid)){
    		if(!empty($saved_colors)){
    		    foreach($saved_colors as $csk=>$csvalue){
    		        $all_colors[$csvalue['color']] = $csvalue['colorname'];
    		    }
    		}
		}
		$this->view->color_statuses = $color_statuses;
		$this->view->all_colors = $all_colors;
		
		//get client icons
		$icons_client = new IconsClient();
		$client_icons = $icons_client->get_client_icons($clientid, false,'icons_vw');
		$this->view->client_icons =$client_icons;
	
		
        $data['columns'][] = array('visible' => true);// 1
        $data['columns'][] = array('visible' => true);// 2
        $data['columns'][] = array('visible' => true);// 3
        $data['columns'][] = array('visible' => true);// 4
        $data['columns'][] = array('visible' => false);// 5
        $data['columns'][] = array('visible' => true);// 6
        $data['columns'][] = array('visible' => true);// 7
        $data['columns'][] = array('visible' => true);// 8
        $data['columns'][] = array('visible' => true);// 9
        $data['columns'][] = array('visible' => true);// 10
        $data['columns'][] = array('visible' => true);// 11
        $data['columns'][] = array('visible' => true);// 12
        $data['columns'][] = array('visible' => true);// 13
        $data['columns'][] = array('visible' => true);// 14
        $data['columns'][] = array('visible' => true);// 15
        $data['columns'][] = array('visible' => true);// 16
        $data['columns'][] = array('visible' => true);// 17
        $data['columns'][] = array('visible' => true);// 18
        $data['columns'][] = array('visible' => true);// 19
        $data['columns'][] = array('visible' => true);// 20
        $data['columns'][] = array('visible' => true);// 21
        $data['columns'][] = array('visible' => true);// 22
        
        $data['columns'][] = array('visible' => true);// 23
        $data['columns'][] = array('visible' => true);// 24
        $data['columns'][] = array('visible' => true);// 25
        $data['columns'][] = array('visible' => true);// 26
        $data['columns'][] = array('visible' => true);// 27 ////ISPC-2616 Carmen 03.08.2020
        $data['columns'][] = array('visible' => true);// 28 //ISPC-2884,Elena,14.04.2021
		
		//find previous state
		//$prev_info = UserTableSettings::user_saved_settings($userid,"voluntaryworkers"); //ISPC-2616 Carmen 03.08.2020
		
		$columns_array = array(
		    "1" => "status_color",
		    "2" => "hospice_association",
		    "3" => "status",
		    "4" => "salutation",
		    "5" => "last_name",
		    "6" => "first_name",
		    "7" => "birthdate",
		    "8" => "street",
		    "9" => "zip",
		    "10" => "city",
		    "11" => "phone",
		    "12" => "mobile",
		    "13" => "email",
		    "14" => "comments",
			//TODO-3414 Carmen 09.09.2020
		    /* "15" => "children",
		    "16" => "profession",
		    "17" => "appellation",
		    "18" => "edication_hobbies",
		    "19" => "special_skils",
		    "20" => "gc_certificate_date",
		    "21" => "has_car",
		    "22" => "patients",
			"23" => "cokoordinator",
		    "24" => "icons",			
			"25" => "image", //ISPC - 2231 -p.1
			"26" => "change_date", //ISPC - 2231 -p.2
		    "27" => "comments_availability",          //ISPC-2617 Lore 22.07.2020 */
			"15" => "comments_availability",          //ISPC-2617 Lore 22.07.2020 */
			"16" => "children",
			"17" => "profession",
			"18" => "appellation",
			"19" => "edication_hobbies",
			"20" => "special_skils",
			"21" => "gc_certificate_date",
			"22" => "has_car",
			"23" => "patients",
			"24" => "cokoordinator",
			"25" => "icons",
			"26" => "image", //ISPC - 2231 -p.1
			"27" => "change_date", //ISPC - 2231 -p.2
            "28" => "gender",//ISPC-2884,Elena,14.04.2021
			//--
		);
		
		if ($_REQUEST['generate'] == 'export_xlsx'){
			$columns_array = array_merge (array("0" => "#") , $columns_array); 
		}
		
		$columns['all'] = $columns_array;
		////ISPC-2616 Carmen 03.08.2020
		/* if( !empty($prev_info) )
		{
		    foreach( $prev_info as $k => $row )
		    {
		        $data['columns'][ $row['column_id'] ] = array('visible' => $row['visible'] == 'yes' ? true : false);
		    }
		}
		
		foreach($data['columns'] as $cid => $visible){
		    if($visible['visible']){
		        $visible_columns [] = $cid;
		        if($columns_array[$cid]){
    		        $columns['viewable'][$cid] = $columns_array[$cid];
		        }
		    }
		} */
		//--
		//ISPC 2116 - add columns for street, zip, city(8, 9 si 10)
		$columns['upcomming']['5'] = $columns_array['5'];
		$columns['upcomming']['6'] = $columns_array['6'];
		$columns['upcomming']['7'] = $columns_array['7'];
		$columns['upcomming']['8'] = $columns_array['8'];
		$columns['upcomming']['9'] = $columns_array['9'];
		$columns['upcomming']['10'] = $columns_array['10'];
		
		// column order
		$ordered_columns = array();
		//$colum_order_array = UserTableSettings::user_saved_settings($this->userid,"voluntaryworkers",false,false,true);
		
		//ISPC-2468  Lore 15.11.2019
		$tab = !empty($_REQUEST['tab']) ? (int)$_REQUEST['tab'] : 0;
		$colum_order_array = UserTableSettings::user_saved_settings($this->userid,"voluntaryworkers", false, $tab, true);
		
		////ISPC-2616 Carmen 03.08.2020
		if( !empty($colum_order_array) )
		{
			foreach( $colum_order_array as $k => $row )
			{
				$data['columns'][ $row['column_id'] ] = array('visible' => $row['visible'] == 'yes' ? true : false);
			}
		}
		
		foreach($data['columns'] as $cid => $visible){
			if($visible['visible']){
				$visible_columns [] = $cid;
				if($columns_array[$cid]){
					$columns['viewable'][$cid] = $columns_array[$cid];
				}
			}
		}
		//--
		
		$ordered = 0;
		$order_settings_str ="";
		
		foreach($colum_order_array as $k=>$cor){
		    
	        $order_settings[] = $cor['column_id'];
	        $order_settings_strrr .= $cor['column_id'].',';

		    if($cor['column_order'] != '0'){
		        $ordered +='1';
		    } else{
		        $ordered +='0';
		    }
		}
		
		//$order_settings_str = implode(',',$order_settings); //ISPC-2616 Carmen 04.08.2020
		
		if($ordered != '0' ){
			//ISPC-2616 Carmen 04.08.2020
			if(!empty($colum_order_array) && (count($columns['all'])+2) > count($colum_order_array))
			{
				$all_columns = $columns['all'];
				$all_columns['0'] = 'select_vw';
				$all_columns[key(array_slice($columns['all'], -1, 1, true))+1] = 'actions';
					
				foreach($all_columns as $kc => $vc)
				{
					if(!in_array($kc, $order_settings))
					{
						array_push($order_settings, $kc);
					}
				}
			}
			$order_settings_str = implode(',',$order_settings); //ISPC-2616 Carmen 04.08.2020
			//--
			//var_dump($order_settings_str);exit;
		    $this->view->custom_order = "1";
		    $this->view->columns_order = $order_settings_str;
		    
		    foreach($order_settings as $corder=>$cid){
		        
		        if($columns['all'][$cid]){
    		        $ordered_columns['all'][] = $columns['all'][$cid];  
		        }
		        
		        if($columns['all'][$cid] && in_array($cid,$visible_columns)){
    		        $ordered_columns['viewable'][] = $columns['all'][$cid];  
		        }
		    }
		} else {
		
		    $this->view->custom_order = "0";
		}
		
		
		if($this->getRequest()->isPost())
		{
		    $a_post = $_POST;
		    
		    // generate only if vws are selected
		    if((!empty($a_post['vws']) && count($a_post['vws']) > 0) || $a_post['generate'] == "upcomming_birthdays" || $a_post['generate'] == "allyear_birthdays"){

		        if(strlen($a_post['sortby'])){
		            $sortby = $columns_array[$a_post['sortby']];
		        } else{
		            $sortby = "last_name";
		        }
		        
		        if(strlen($a_post['sortdir'])){
		            $sortdir = $a_post['sortdir'];
		        } else{
		            $sortdir = "ASC";
		        }

		        $a_post['sortby'] = $sortby;
		        $a_post['sortdir'] = $sortdir;
		        
		        // get selected vws details
		        $Voluntaryworkers_model  = new Voluntaryworkers();
		        if($a_post['generate'] == "upcomming_birthdays"){
    		        $vws_array = $Voluntaryworkers_model->get_vws_multiple_details(false,$sortby,$sortdir,true);
		        } elseif($a_post['generate'] == "allyear_birthdays"){
		        // ISPC-2401 1) Lore
		            $vws_array = $Voluntaryworkers_model->get_vws_multiple_details_allyear(false,$sortby,$sortdir,true);
		        } else{
    		        $vws_array = $Voluntaryworkers_model->get_vws_multiple_details($a_post['vws'],$sortby,$sortdir);
		        }

		        
		        
		        $vw_icons = new VoluntaryworkersIcons();
		        $worker_icons_arr  = $vw_icons->get_icons($a_post['vws']);
		        // client_ic
		        $icons_client = new IconsClient();
		        $client_icons = $icons_client->get_client_icons($clientid, false ,'icons_vw');
		        
		        foreach ($worker_icons_arr as $k_vw_id => $v_vw_icons)
		        {
		        	if($client_icons[$v_vw_icons['icon_id']]['image']){
		        			//TODO-3414 Carmen 09.09.2020
		        			$vws_array[$v_vw_icons['vw_id']]['icons'] .= '<span class="vw_list_icon" style="background:#'.$client_icons[$v_vw_icons['icon_id']]['color'].'"><img src="'.APP_BASE.'icons_system/'.$client_icons[$v_vw_icons['icon_id']]['image'].'"   title="'.$client_icons[$v_vw_icons['icon_id']]['name'].'" width="34" height="34" /></span> ';
		        			//--
		        	} else{
		        			$vws_array[$v_vw_icons['vw_id']]['icons'] .= '<span class="vw_list_icon"  style="background:#'.$client_icons[$v_vw_icons['icon_id']]['color'].'"><p></p></span> ';
		        	}
		        }
		        
		        if(!empty($ordered_columns['all'])){
		            $columns['all'] = $ordered_columns['all'];
		        }
		        
		        if(!empty($ordered_columns['viewable'])){
		            $columns['viewable'] = $ordered_columns['viewable'];
		        }

		        
		        if($a_post['generate'] != "0" ){
		            switch($a_post['generate'])
		            {
		                // export
		                case 'export_xlsx':
// 		                    $this->export_xlsx($columns['all'],$vws_array);
		                    $this->export_php_excel($columns['all'],$vws_array);
		                    break;
		                     
		                case 'export_csv':
		                    $this->export_csv($columns['all'],$vws_array);
		                    break;
		                     
		                    // print
		                case 'print_list_all_columns':
		                    $this->export_html($columns['all'],$vws_array,false);
		                    break;
		
		                case 'print_list_viewable_columns':
		                    $this->export_html($columns['viewable'],$vws_array,false);
		                    break;
		                     
		                case 'print_letters':
		                    //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
		                    if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
		                        
    		                    $this->export_letters($a_post);
		                        
		                    } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
		                        $a_post['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
		                        $a_post['vws'] = array_unique($a_post['vws']);
		                        $print_job_data = array();
		                        $print_job_data['clientid'] = $this->clientid;
		                        $print_job_data['user'] = $this->userid;
		                        $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
		                        $print_job_data['output_type'] = 'pdf';
		                        $print_job_data['status'] = 'active';
		                        $print_job_data['template_id'] = $a_post['template_id'];
		                        $print_job_data['invoice_type'] = null;
		                        $print_job_data['print_params'] = serialize($a_post);
		                        $print_job_data['print_function'] = 'export_letters';
		                        $print_job_data['print_controller'] = $this->getRequest()->getControllerName();
		                        
		                        foreach($a_post['vws'] as $k=>$inv_id){
		                            $print_job_data['PrintJobsItems'][] = array(
		                                'clientid'=>$print_job_data['clientid'],
		                                'user'=>$print_job_data['user'],
		                                'item_id'=>$inv_id,
		                                'item_type'=>"voluntaryWorker",
		                                'invoice_type'=>null,
		                                'status'=>"new"
		                            );
		                        }
		                        
		                        $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
		                        $print_id = $PrintJobsBulk_obj->id;
		                        
		                        if($print_id){
		                            $this->__StartPrintJobs();
		                        }
		                    }
		                    
		                    
		                    break;
		                     
		                case 'print_labels_3424':
		                    $this->export_pdf($vws_array, 'Avery105x48', "vw_stickers105x48.html");
		                    break;
		                     
		                case 'print_labels_3422':
		                    $this->export_pdf($vws_array, 'Avery70x35', "vw_stickers70x35.html");
		                    break;
		                    
		                case 'upcomming_birthdays':
		                     $this->export_html($columns['upcomming'],$vws_array,true);
		                     break;

		                case 'allyear_birthdays':
		                   $this->export_html($columns['upcomming'],$vws_array,true);
		                    break;
		                    
		                default:
		                     
		                    break;
		            }
		            $this->_redirect(APP_BASE . "voluntaryworkers/workersdetailslist");
		        } else{
		        	//ISPC - 2114 - archive function for vw
		        	if($a_post['transfer2archive'] == '1')
		        	{		        		 
		        		$vwsform = new Application_Form_Voluntaryworkers();
		        		$vwsform->archive_unarchive_vws($a_post['vws'], $clientid, true);
		        		$this->_redirect(APP_BASE . "voluntaryworkers/workersdetailslist?flg=archived_successful");
		        	}
		        	elseif($a_post['transferfromarchive'] == '1')
		        	{
		        		$vwsform = new Application_Form_Voluntaryworkers();
		        		$vwsform->archive_unarchive_vws($a_post['vws'], $clientid, false);
		        		$this->_redirect(APP_BASE . "voluntaryworkers/workersdetailslist?flg=archived_successful&isarchived=1");
		        	}
		        	/*else 
		        	{		             
		            	$this->_redirect(APP_BASE . "voluntaryworkers/workersdetailslist?flg=no_export_method");
		        	}*/
		        	//ISPC - 2114 - archive function for vw
		        	
		        	//ISPC-2401 -10) ineducation function for vw
		        	if($a_post['transfer2education'] == '1')
		        	{
		        	    $vwsform = new Application_Form_Voluntaryworkers();
		        	    $vwsform->education_uneducation_vws($a_post['vws'], $clientid, true);
		        	    $this->_redirect(APP_BASE . "voluntaryworkers/workersdetailslist?flg=education_successful");
		        	}
		        	elseif($a_post['transferfromeducation'] == '1')
		        	{
		        	    $vwsform = new Application_Form_Voluntaryworkers();
		        	    $vwsform->education_uneducation_vws($a_post['vws'], $clientid, false);
		        	    $this->_redirect(APP_BASE . "voluntaryworkers/workersdetailslist?flg=education_successful&ineducation=2");
		        	}
		        	else
		        	{
		        	    $this->_redirect(APP_BASE . "voluntaryworkers/workersdetailslist?flg=no_export_method");
		        	}
		        	//ISPC-2401 - education function for vw
		        }
		    } else {
		        $this->_redirect(APP_BASE . "voluntaryworkers/workersdetailslist?flg=no_vws_error");
		    }
		     
		} else {
		    //not a post
		    $primary_voluntary_statuses = Pms_CommonData::get_primary_voluntary_statuses();
		    //ISPC - 2231 - p.7 - add for search the secondary statuses too
		    $secondary_status_arr = VoluntaryWorkersSecondaryStatuses::get_secondarystatuses($clientid);
		  
		    /*$primary_voluntary_statuses_select = array();
		    foreach ($primary_voluntary_statuses  as $row ) {
		        if ($row['id'] == 'n') {
		            $row['id'] = "^[n]*$";
		        } else {
		            $row['id'] = "^{$row['id']}$";
		        }
		        $primary_voluntary_statuses_select[$row['id']] = $row['status'];
		        
		    }*/
		    
		    $all_voluntary_statuses_select = array();
		    foreach ($primary_voluntary_statuses  as $row ) {
		    	if ($row['id'] == 'n') {
		    		$row['id'] = "^[n]*$";
		    	} else {
		    		$row['id'] = "^{$row['id']}$";
		    	}
		    	$all_voluntary_statuses_select[$row['id']] = $row['status'];
		    
		    }
		    
		    foreach ($secondary_status_arr  as $row ) {
		    	if ($row['status'] != 'keine Angabe') {	
		    		$searchtext = mb_strtolower(mb_substr($row['status'], 0, 3));
// 		    		$row['id'] = "{$searchtext}";
		    		$row['id'] = $row['id'];
		    		$all_voluntary_statuses_select[$row['id']] = $row['status'];
		    	}		    
		    }
		    //var_dump($all_voluntary_statuses_select); exit;
		    $this->view->primary_voluntary_statuses = $primary_voluntary_statuses;
		    //$this->view->primary_voluntary_statuses_select = $primary_voluntary_statuses_select;
		    $this->view->all_voluntary_statuses_select = $all_voluntary_statuses_select;
		}
		
		
		
	}

	public function getjsondataAction ()
	{
		$fdoc = Doctrine_Query::create()
		->select('*')
		->from('VoluntaryWorkers')
		->where('isdelete = ?', 0);
		$track = $fdoc->execute();

		echo json_encode($track->toArray());
		exit;
	}

	public function fetchlistAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');

		$columnarray = array("pk" => "id", "ha" => "hospice_association", "st" => "status", "fn" => "first_name", "ln" => "last_name", "zp" => "zip", "ct" => "city", "ph" => "phone", 'sc' => 'status_color', 'pm'=>'mobile' , 'em'=>'email' );

		$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		$this->view->order = $orderarray[$_REQUEST['ord']];
		$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
		$this->view->{"style".$_GET['pgno']} = "active";
		if ($this->clientid > 0)
		{
			$where = ' and clientid=' . $this->clientid;
		}
		else
		{
			$where = ' and clientid=0';
		}

		$h_association = Doctrine_Query::create()
		->select('*')
		->from('Hospiceassociation')
		->where('indrop= 0 and isdelete = 0 and clientid=' . $this->clientid);
		$h_association_array = $h_association->fetchArray();

		foreach ($h_association_array as $khas => $h_assoc_item)
		{
			$h_assoc_data[$h_assoc_item['id']] = $h_assoc_item['hospice_association'];
		}

		$fdoc1 = Doctrine_Query::create()
		->select('count(*)')
		->from('Voluntaryworkers')
		->where("isdelete = 0  " . $where)
		->andWhere('indrop=0');
		if (isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
		{
			$fdoc1->andWhere("(first_name like ?  or last_name like ? or  zip like ? or  city like ? or  phone like ?)",array("%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%"));
		}		
		$fdoc1->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		$fdocexec = $fdoc1->execute();
		$fdocarray = $fdocexec->toArray();

		$limit = 50;
		$fdoc1->select('*');
		$fdoc1->where("isdelete = 0");
		$fdoc1->andWhere("indrop=0 " . $where . "");
		if (isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
		{
			$fdoc1->andWhere("(first_name like ?  or last_name like ? or  zip like ? or  city like ? or  phone like ?)",array("%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%"));
		}
		$fdoc1->limit($limit);
		$fdoc1->offset($_REQUEST['pgno'] * $limit);

		$fdoclimitexec = $fdoc1->execute();
		$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());

		$voluntary_workers_ids[] = '99999999999999';

		foreach ($fdoclimit as $key => $voluntary_worker_item)
		{
			$fdoclimit_arr[$voluntary_worker_item['id']] = $voluntary_worker_item;

			if ($voluntary_worker_item['hospice_association'] > 0)
			{
				$fdoclimit_arr[$voluntary_worker_item['id']]['hospice_association'] = $h_assoc_data[$voluntary_worker_item['hospice_association']];
			}
			else
			{
				$fdoclimit_arr[$voluntary_worker_item['id']]['hospice_association'] = '-';
			}

			$voluntary_workers_ids[] = $voluntary_worker_item['id'];
		}
		$primary_status_arr = Pms_CommonData::get_primary_voluntary_statuses();
		//ISPC-2054(voluntaryworkers statuses updated by clients)
		//$status_arr = Pms_CommonData::getVoluntaryWorkersStatuses();
		$status_arr = VoluntaryWorkersSecondaryStatuses::get_secondarystatuses($this->clientid);
		
		foreach ($status_arr as $k_status => $v_status)
		{
			$statuses[$v_status['id']] = $v_status['status'];
		}
		$this->view->status_array = $statuses;

		//var_dump($clientid); exit;
		foreach($primary_status_arr as $k_pri_status => $v_pri_status)
		{
			$statuses[$v_pri_status['id']] = $v_pri_status['status'];
		}


		$voluntary_workers_statuses = new VoluntaryworkersStatuses();
		$worker_statuses = $voluntary_workers_statuses->get_voluntaryworker_statuses($voluntary_workers_ids, $this->clientid);

		foreach ($worker_statuses as $k_data => $v_data)
		{
			foreach ($v_data as $k_vdata => $v_vdata)
			{
				if($statuses[$v_vdata])
				{
					$worker_statuses_arr[$k_data][] = $statuses[$v_vdata];
				}
			}
		}
		//var_dump($worker_statuses_arr); exit;
		foreach ($worker_statuses_arr as $k_vw_id => $v_vw_statuses)
		{
			$fdoclimit_arr[$k_vw_id]['statuses'] = $v_vw_statuses;
		}
		$grid = new Pms_Grid($fdoclimit_arr, 1, $fdocarray[0]['count'], "listvoluntaryworkers.html");
		$this->view->voluntaryworkersgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("voluntarynavigation.html", 5, $_REQUEST['pgno'], $limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['voluntaryworkerslist'] = $this->view->render('voluntaryworkers/fetchlist.html');

		echo json_encode($response);
		exit;
	}

	public function fetchdetailslistAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		
		$voluntary_workers = new Voluntaryworkers();
		$patient_voluntary_workers = new PatientVoluntaryworkers();

		// ################################################
		// get associated clients of current clientid START 
		// ###############################################
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
		if($connected_client){
		    $clientid = $connected_client;
		} else{
		    $clientid = $this->clientid;
		}
        // ################################################
        // get associated clients of current clientid END
        // ###############################################		
		
		
		
		$columnarray = array(
		    "pk" => "id",
		    "ha" => "hospice_association",
		    "st" => "status",
		    "fn" => "first_name",
		    "ln" => "last_name",
		    "zp" => "zip",
		    "ct" => "city",
		    "ph" => "phone",
		    "sc" => "status_color",
		    "pm"=>"mobile",
		    "em"=>"email"
		);
		
		$orderarray = array(
		    "ASC" => "DESC",
		    "DESC" => "ASC"
		);
		
		$this->view->order = $orderarray[$_REQUEST['ord']];
		$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
		$this->view->{"style".$_GET['pgno']} = "active";
		
		if ($clientid > 0)
		{
		    $where = ' and clientid=' . $clientid;
		}
		else
		{
		    $where = ' and clientid=0';
		}
		
		// Hospice association details
		$h_association = Doctrine_Query::create()
		->select('*')
		->from('Hospiceassociation')
		->where('indrop= 0 and isdelete = 0 and clientid=' . $clientid);
		$h_association_array = $h_association->fetchArray();
		
		foreach ($h_association_array as $khas => $h_assoc_item)
		{
		    $h_assoc_data[$h_assoc_item['id']] = $h_assoc_item['hospice_association'];
		}
		
		
		
		// ###################################
		// Filter options  START
		// ###################################
		// save filter
		if($this->getRequest()->isPost())
		{
		    $data = json_decode($_POST['details']);
		
		    if(!empty($data))
		    {
		        $user_filter = new Application_Form_UserVwFilters ();
		        $result = $user_filter->set_filter($this->userid, $this->clientid, $data);
		    }
		}

		
		// get saved filters
		$user_filters = UserVwFilters::get_user_filter();
		
		
		$filter_color_sql = "";
		$filter_sql = "";
		if($user_filters){
		    $ufilter = $user_filters[0];
	        $this->view->ufilter = $ufilter;
	        if($ufilter['status_color_g'] == "1" ){
	            if(strlen($filter_color_sql) == 0 ){
    		        $filter_color_sql .= '  status_color = "g" ';
	            }
	        }
	        
	        if($ufilter['status_color_y'] == "1" ){
	            if(strlen($filter_color_sql) > 0 ){
		          $filter_color_sql .= ' OR status_color = "y" ';
	            } else{
		          $filter_color_sql .= ' status_color = "y" ';
	            }
	        }
	        
	        if($ufilter['status_color_r'] == "1" ){
	            if(strlen($filter_color_sql) > 0 ){
		          $filter_color_sql .= ' OR status_color = "r" ';
	            } else{
		          $filter_color_sql .= ' status_color = "r"  ';
	            }
	        }
	        
	        if($ufilter['status_color_b'] == "1" ){
	            if(strlen($filter_color_sql) > 0 ){
		          $filter_color_sql .= ' OR status_color = "b" ';
	            } else{
		          $filter_color_sql .= ' status_color = "b"  ';
	            }
	        }
	        	        
	        if($ufilter['status_inactive'] == "1" ){
	          $filter_inactive_sql = ' inactive = "1" ';
	        }
	        
	        if($ufilter['status_color_blue'] == "1" ){
	            if(strlen($filter_color_sql) > 0 ){
	                $filter_color_sql .= ' OR status_color = "blue" ';
	            } else{
	                $filter_color_sql .= ' status_color = "blue" ';
	            }
	        }
	        
	        if($ufilter['status_color_purple'] == "1" ){
	            if(strlen($filter_color_sql) > 0 ){
	                $filter_color_sql .= ' OR status_color = "purple" ';
	            } else{
	                $filter_color_sql .= ' status_color = "purple" ';
	            }
	        }
	        
	        if($ufilter['status_color_grey'] == "1" ){
	            if(strlen($filter_color_sql) > 0 ){
	                $filter_color_sql .= ' OR status_color = "grey" ';
	            } else{
	                $filter_color_sql .= ' status_color = "grey" ';
	            }
	        }
		}

		if(strlen($filter_color_sql) > 0){
		    $filter_color_sql_final = $filter_color_sql;
		}
		// ###################################
		// Filter options  END
		// ###################################

		
		
		
		
		if (isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
		{
		// ###################################
		// search by Hospice association START
		// ###################################
		    //search hospice associations
		    $hospice_association_q = Doctrine_Query::create()
		    ->select("*")
		    ->from('Hospiceassociation')
		    ->where('clientid = "' . $clientid . '"')
		    ->andWhere("isdelete=0 and  hospice_association like '%" . trim($_REQUEST['val']) . "%'");
		    $hospice_association_array = $hospice_association_q->fetchArray();
		
		    
		    $hospice_association_ids = array();
		    if($hospice_association_array){

		        foreach($hospice_association_array as $k=>$haso){
		            $hospice_association_ids_str .= '"'.$haso['id'].'",';
		            $hospice_association_ids[] = $haso['id'];
		        }
		    }
		    
		    $hospice_association_sql="";
		    if(!empty($hospice_association_ids)){
		        $hospice_association_sql = " OR hospice_association in (".substr($hospice_association_ids_str,0,-1).") ";
		    }
		    
		// ###################################
		// search by Hospice association END
		// ###################################
		
		
		// ###################################
		// search by primary status START
		// ###################################
		    $statusesd_prima_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('VoluntaryworkersPrimaryStatuses')
		    ->where("description like ?","%" . trim($_REQUEST['val']) . "%");
		    $primary_statuses_details_array = $statusesd_prima_q ->fetchArray();

		    $primary_statuses_ids_str = "";
		    if(!empty($primary_statuses_details_array)){
		        foreach($primary_statuses_details_array as $k=>$pst_data) {
    		        $primary_statuses_ids[] = $pst_data['status_id'];
    		        $primary_statuses_ids_str .= '"'.$pst_data['status_id'].'",';
		        }

		        
		        $primary_statuses_sql="";
		        if(!empty($primary_statuses_ids)){
		            $primary_statuses_sql = " OR status in (".substr($primary_statuses_ids_str,0,-1).") ";
		        }
		    }
		// ###################################
		// search by primary status END
		// ###################################
		
		
		
		// ###################################
		// search by secondary status START
		// ###################################
		    $statusesd_q = Doctrine_Query::create()
		    ->select('*')
		    //ISPC-2054(voluntaryworkers statuses updated by client)
		    //->from('VoluntaryworkersStatusesDetails')
		    ->from('VoluntaryWorkersSecondaryStatuses')
		    //ISPC 1739
		    //->where("description REGEXP '".$regexp ."'");
		    ->where("LOWER(description) like ?", "%" . addslashes(trim($search_value)) . "%")
		    ->andWhere("clientid =? and isdelete =?", array($clientid, '0'));
		    
		    $statuses_details_array = $statusesd_q ->fetchArray();
		     
		    if(!empty($statuses_details_array)){
		    	foreach($statuses_details_array as $k=>$st_data) {
		    		//ISPC-2054(voluntaryworkers statuses updated by client)
		    		//$statuses_ids[] = $st_data['status_id'];
		    		$statuses_ids[] = $st_data['id'];
		    	}
		    
		    	if(!empty($statuses_ids)){
		    		$statuses2vw_Q = Doctrine_Query::create()
		    		->select('*')
		    		->from('VoluntaryworkersStatuses')
		    		->where('clientid = ?', $clientid)
		    		->andWhereIn('status', $statuses_ids);
		    		$statuses2vw_res = $statuses2vw_Q->fetchArray();
		    
		    
		    		if(!empty($statuses2vw_res)){
		    			foreach($statuses2vw_res as $k=>$vw2st){
		    				$secondary_statuses_ids_str .= '"'.$vw2st['vw_id'].'",';
		    				$secondary_statuses_ids[] = $vw2st['vw_id'];
		    			}
		    		}
		    	}
	
		        $secondary_statuses_sql="";
		        if(!empty($secondary_statuses_ids)){
		            $secondary_statuses_sql = " OR id in (".substr($secondary_statuses_ids_str,0,-1).") ";
		        }
		    }
		// ###################################
		// search by secondary status END
		// ###################################
		
		
		// ##################################
		// search by patient START
		// ##################################
		    $search_string = addslashes(urldecode(trim($_REQUEST['val'])));
		    //search  active patients
		    $patietns_act_search_q = Doctrine_Query::create()
		    ->select("p.ipid,
    				e.epid,
    				AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name,
    				AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name
    				")
		    				->from('EpidIpidMapping e')
		    				->leftJoin('e.PatientMaster p')
		    				->where('e.ipid = p.ipid')
		    				->andWhere('e.clientid = ?', $this->clientid)
		    				->andWhere('p.isstandby = "0"')
		    				->andWhere('p.isdischarged = "0"')
		    				->andWhere('p.isdelete = "0"')
		    				->andWhere('p.isstandbydelete = "0"');
		    
		    
		    /*$patietns_act_search_q->andwhere("e.clientid = " . $this->clientid . " and trim(lower(e.epid)) like trim(lower('%" . $search_string . "%')) or (trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%')) or trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%'))  or concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
    						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
    						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
    						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')))");

		    */
		    
		    $patietns_act_search_q->andwhere("e.clientid = " . $this->clientid . " 
		    				and trim(lower(e.epid)) like ? or (trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ? or trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?  or concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
    						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
    						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
    						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?)",
		    		array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
		    				"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
		    				"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
		    				"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
		    				"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
		    				"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
		    				"%".trim(mb_strtolower($search_string, 'UTF-8'))."%"));
		    
		    
		    
		    $search_active_patients = $patietns_act_search_q->fetchArray();
		
    		if (!empty($search_active_patients))
    		{
    		    foreach ($search_active_patients as $k_patient => $v_patient)
    		    {
    		        $search_active_ipids[] =  $v_patient['PatientMaster']['ipid'];
    		    }
    		
    		    $all_parent2child = $voluntary_workers->get_all_parent2child_workers($clientid);
    		
    		    $used_vwids_str = '"XXXXXXXXX",';
    		    $used_vwid = array();
    		    $worker2shactivepatients = $patient_voluntary_workers->get_workers2patients($all_parent2child['vw_ids'], $search_active_ipids);
    		    foreach($worker2shactivepatients as $vwid=>$patipid){
    		
    		        $used_vwid[] = $all_parent2child['vwid2parent'][$vwid];
    		        $used_vwids_str .= '"'.$all_parent2child['vwid2parent'][$vwid].'",';
    		    }
    		    if (!empty($used_vwid)){
    		        $user_sql .= " OR id in (".substr($used_vwids_str,0,-1).") ";
    		    }
    		    
    		}
		// ###############################
		// search by patient END
		// #############################
		}
		

		// ########################################		
		// #####  Query for count ###############
		$fdoc1 = Doctrine_Query::create();
		$fdoc1->select('count(*)');
		$fdoc1->from('Voluntaryworkers');
		$fdoc1->where("isdelete = 0  " . $where);
		$fdoc1->andWhere("indrop = 0  ");
		/* ------------- Search options ------------------------- */
		if (isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
		{
		    $fdoc1->andWhere("first_name like ?  or last_name like ? or zip like ? or city like ? or phone like ? or mobile like ? or email like ? ".$user_sql." ".$hospice_association_sql."  ".$secondary_statuses_sql."  ".$primary_statuses_sql."  ",
		    		array("%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%"));
		}
		
		if ($user_filters && strlen($filter_color_sql_final) > 0)
		{
		    $fdoc1->andWhere($filter_color_sql_final);
		}
		
		if($ufilter['status_inactive'] == "1" ){
		    $fdoc1->andWhere('inactive = "1"');
		}

		$fdoc1->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		$fdocexec = $fdoc1->execute();
		$fdocarray = $fdocexec->toArray();

		
		
		// ########################################		
		// #####  Query for details ###############		
		$limit = 50;
		$fdoc1->select('*');
		$fdoc1->where("isdelete = 0  " . $where);
		$fdoc1->andWhere("indrop = 0  ");
		/* ------------- Search options ------------------------- */
		if (isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
		{
		    $fdoc1->andWhere("first_name like ?  or last_name like ? or zip like ? or city like ? or phone like ? or mobile like ? or email like ? ".$user_sql." ".$hospice_association_sql."  ".$secondary_statuses_sql."  ".$primary_statuses_sql."  ",
		    		array("%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%"));
		}
		
		if ($user_filters && strlen($filter_color_sql_final) > 0)
		{
		    $fdoc1->andWhere($filter_color_sql_final);
		}
		
		if($ufilter['status_inactive'] == "1" ){
		    $fdoc1->andWhere('inactive = "1"');
		}

		$fdoc1->limit($limit);
		$fdoc1->offset($_REQUEST['pgno'] * $limit);
		$fdoclimitexec = $fdoc1->execute();
		$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());

		
		
		
        $voluntary_workers_ids[] = '99999999999999';
		foreach ($fdoclimit as $key => $voluntary_worker_item)
		{
			$fdoclimit_arr[$voluntary_worker_item['id']] = $voluntary_worker_item;

			if ($voluntary_worker_item['hospice_association'] > 0)
			{
				$fdoclimit_arr[$voluntary_worker_item['id']]['hospice_association'] = $h_assoc_data[$voluntary_worker_item['hospice_association']];
			}
			else
			{
				$fdoclimit_arr[$voluntary_worker_item['id']]['hospice_association'] = '-';
			}

			$voluntary_workers_ids[] = $voluntary_worker_item['id'];
		}
		$primary_status_arr = Pms_CommonData::get_primary_voluntary_statuses();
		//ISPC-2054(voluntaryworkers statuses updated by clients)
		//$status_arr = Pms_CommonData::getVoluntaryWorkersStatuses();
		$status_arr = VoluntaryWorkersSecondaryStatuses::get_secondarystatuses($clientid);
		
		foreach ($status_arr as $k_status => $v_status)
		{
			$statuses[$v_status['id']] = $v_status['status'];
		}
		$this->view->status_array = $statuses;


		foreach($primary_status_arr as $k_pri_status => $v_pri_status)
		{
			$statuses[$v_pri_status['id']] = $v_pri_status['status'];
		}
		
		//get active patients
		$patietns_act = Doctrine_Query::create()
		->select("p.ipid,
				e.epid,
				AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name,
				AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name
				")
				->from('EpidIpidMapping e')
				->leftJoin('e.PatientMaster p')
				->where('e.ipid = p.ipid')
				->andWhere('e.clientid = "' . $this->clientid . '"')
				->andWhere('p.isstandby = "0"')
				->andWhere('p.isdischarged = "0"')
				->andWhere('p.isdelete = "0"')
				->andWhere('p.isstandbydelete = "0"');
		$active_patients = $patietns_act->fetchArray();

		if (!empty($active_patients))
		{
		    foreach ($active_patients as $k_patient => $v_patient)
		    {
		        $patients_epids_selector[$v_patient['PatientMaster']['id']] = strtoupper($v_patient['epid']); //used in patients dropdown
		        $patients_ipids_selector[$v_patient['PatientMaster']['id']] = $v_patient['PatientMaster']['ipid']; //used to match patient_master id with ipid
		        $active_ipids[] =  $v_patient['PatientMaster']['ipid'];
		        $patient_details[$v_patient['PatientMaster']['ipid']] = $v_patient['PatientMaster']['last_name'].' '.$v_patient['PatientMaster']['first_name'];
		    }
		}
		
		if(empty($active_ipids)){
		    $active_ipids[] = "XXXXXX";
		}
		

		$parent2child = $voluntary_workers->parent2child_workers ($clientid,$voluntary_workers_ids);
		$worker2activepatients = $patient_voluntary_workers->get_workers2patients($parent2child['vw_ids'], $active_ipids,false, $currently_connected = true);
		
		foreach($worker2activepatients as $vwid=>$patipid){
		    if(!in_array($patient_details[$patipid],$pat2master[$parent2child['vwid2parent'][$vwid]])){
		        $pat2master[$parent2child['vwid2parent'][$vwid]][] = $patient_details[$patipid];
		    }
		}
		
		$voluntary_workers_statuses = new VoluntaryworkersStatuses();
		$worker_statuses = $voluntary_workers_statuses->get_voluntaryworker_statuses($voluntary_workers_ids, $clientid);

		foreach ($worker_statuses as $k_data => $v_data)
		{
			foreach ($v_data as $k_vdata => $v_vdata)
			{
				$worker_statuses_arr[$k_data][] = $statuses[$v_vdata];
			}
		}

		foreach ($worker_statuses_arr as $k_vw_id => $v_vw_statuses)
		{
			$fdoclimit_arr[$k_vw_id]['statuses'] = $v_vw_statuses;
		}

		foreach ($fdoclimit as $key => $voluntary_worker_item)
		{
			if (!empty($pat2master[$voluntary_worker_item['id']]))
			{
				$fdoclimit_arr[$voluntary_worker_item['id']]['patients'] =  implode(',<br/>',$pat2master[$voluntary_worker_item['id']]);
			}
			else
			{
				$fdoclimit_arr[$voluntary_worker_item['id']]['patients'] =    '-';
			}
		}
		
		$grid = new Pms_Grid($fdoclimit_arr, 1, $fdocarray[0]['count'], "voluntaryworkersdetailslist.html");
		$this->view->voluntaryworkersgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("vwdetailsnavigation.html", 5, $_REQUEST['pgno'], $limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['voluntaryworkerslist'] = $this->view->render('voluntaryworkers/fetchdetailslist.html');

		echo json_encode($response);
		exit;
	}
	
	public function vwlistAction()
	{
	    
	    $this->_helper->viewRenderer->setNoRender();
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		
		$voluntary_workers = new Voluntaryworkers();
		$patient_voluntary_workers = new PatientVoluntaryworkers();

		// ################################################
		// get associated clients of current clientid START 
		// ###############################################
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
		if($connected_client){
		    $clientid = $connected_client;
		} else{
		    $clientid = $this->clientid;
		}
        // ################################################
        // get associated clients of current clientid END
        // ###############################################		

		$limit = $_REQUEST['length'];
		$offset = $_REQUEST['start'];
		$search_value = $_REQUEST['search']['value'];
		if(!empty($_REQUEST['order'][0]['column'])){
    		$order_column = $_REQUEST['order'][0]['column'];
		} else{
    		$order_column = "5"; // last_name
		}
		$order_dir = strtoupper($_REQUEST['order'][0]['dir']);
		$order_dir = $order_dir == 'ASC' ? $order_dir : 'DESC';
		
		// get columns from db
		$columns_array = array(
		    "1" => "status_color",
		    "2" => "hospice_association",
		    "3" => "status",
		    "4" => "salutation",
		    "5" => "last_name",
		    "6" => "first_name",
		    "7" => "birthdate",
		    "8" => "street",
		    "9" => "zip",
		    "10" => "city",
		    "11" => "phone",
		    "12" => "mobile",
		    "13" => "email",
		    "14" => "comments",
		    "15" => "children",
		    "16" => "profession",
		    "17" => "appellation",
		    "18" => "edication_hobbies",
		    "19" => "special_skils",
		    "20" => "gc_certificate_date",
		    "21" => "has_car",
		    "22" => "patients",
		    "23" => "cokoordinator",
		    "24" => "icons",   //ISPC - 2231 p.1
			"26" => "change_date", //ISPC - 2231 p.2
		    "27" => "comments_availability",          //ISPC-2617 Lore 22.07.2020
            "28" => "gender",//ISPC-2884,Elena,14.04.2021
		);
		
		if($_REQUEST['columns'][$order_column]['data'] != "patients")
		{
			if(!empty($_REQUEST['columns'][$order_column]['data']) && $_REQUEST['columns'][$order_column]['data'] != "patients" ){
			    //TODO-1261
				$order_by_str ='CONVERT(CONVERT('.addslashes(htmlspecialchars($_REQUEST['columns'][$order_column]['data'])).' USING BINARY) USING utf8) '.$order_dir;
// 				$order_by_str = addslashes(htmlspecialchars($_REQUEST['columns'][$order_column]['data']).' '.$order_dir);
			}else{
			    //TODO-1261
			    $order_by_str ='CONVERT(CONVERT('.$columns_array[$order_column].' USING BINARY) USING utf8) '.$order_dir;
// 				$order_by_str = $columns_array[$order_column].' '.$order_dir;
			}
		} else{
		    //TODO-1261
    		$order_by_str ='CONVERT(CONVERT(last_name USING BINARY) USING utf8) '.$order_dir;
// 				$order_by_str = 'last_name '.$order_dir.' COLLATE utf8_german_ci';
		}
		
		if ($clientid > 0)
		{
		    $where = ' and clientid=' . $clientid;
		}
		else
		{
		    $where = ' and clientid=0';
		}
		
		// Hospice association details
		$h_association = Doctrine_Query::create()
		->select('*')
		->from('Hospiceassociation')
		->where('indrop= 0 and isdelete = 0 and clientid=' . $clientid);
		$h_association_array = $h_association->fetchArray();
		
		foreach ($h_association_array as $khas => $h_assoc_item)
		{
		    $h_assoc_data[$h_assoc_item['id']] = $h_assoc_item['hospice_association'];
		}
		
		
		
		// ###################################
		// Filter options  START
		// ###################################
		// get saved filters
		$user_filters = UserVwFilters::get_user_filter();
		
		
		$filter_color_sql = "";
		$filter_sql = "";
		$st_str = '"0",';
		$filter_color_status_array = array();
		if($user_filters){
		    $ufilter = $user_filters[0];
	        $this->view->ufilter = $ufilter;
	        
	        if($ufilter['status_color_g'] == "1" ){
	            if(strlen($filter_color_sql) == 0 ){
    		        $filter_color_sql .= '  status_color = "g" ';
    		        
	            }
  		        $st_str .= '"g",';
  		        $filter_color_status_array[] = "g";
	        }
	        
	        
	        if($ufilter['status_color_y'] == "1" ){
	            if(strlen($filter_color_sql) > 0 ){
		          $filter_color_sql .= ' OR status_color = "y" ';
	            } else{
		          $filter_color_sql .= ' status_color = "y" ';
	            }
	          $st_str .= '"y",';
	          $filter_color_status_array[] = "y";
	        }
	        
	        
	        if($ufilter['status_color_r'] == "1" ){
	            if(strlen($filter_color_sql) > 0 ){
		          $filter_color_sql .= ' OR status_color = "r" ';
	            } else{
		          $filter_color_sql .= ' status_color = "r"  ';
	            }
                $st_str .= '"r",';
                $filter_color_status_array[] = "r";
	        }
	        
	        
	        if($ufilter['status_color_b'] == "1" ){
	            if(strlen($filter_color_sql) > 0 ){
		          $filter_color_sql .= ' OR status_color = "b" ';
	            } else{
		          $filter_color_sql .= ' status_color = "b"  ';
	            }
	            
	            $st_str .= '"b",';
	            $filter_color_status_array[] = "b";
	        }
	           
	        if($ufilter['status_inactive'] == "1" ){
	          $filter_inactive_sql = ' inactive = "1" ';
	        }
	        
	        if($ufilter['status_color_blue'] == "1" ){
	            if(strlen($filter_color_sql) > 0 ){
	                $filter_color_sql .= ' OR status_color = "blue" ';
	            } else{
	                $filter_color_sql .= ' status_color = "blue" ';
	            }
	            $st_str .= '"blue",';
	            $filter_color_status_array[] = "blue";
	        }
	        
	        if($ufilter['status_color_purple'] == "1" ){
	            if(strlen($filter_color_sql) > 0 ){
	                $filter_color_sql .= ' OR status_color = "purple" ';
	            } else{
	                $filter_color_sql .= ' status_color = "purple" ';
	            }
	            $st_str .= '"purple",';
	            $filter_color_status_array[] = "purple";
	        }
	        
	        if($ufilter['status_color_grey'] == "1" ){
	            if(strlen($filter_color_sql) > 0 ){
	                $filter_color_sql .= ' OR status_color = "grey" ';
	            } else{
	                $filter_color_sql .= ' status_color = "grey" ';
	            }
	            $st_str .= '"grey",';
	            $filter_color_status_array[] = "grey";
	        }
		}
		if(empty($filter_color_status_array)){
		    $filter_color_status_array = false;
		}
		
       $color_firtered_details = VwColorStatuses::get_vw_ids_color_statuses_filter(false,$clientid,true,$filter_color_status_array);

       $color_filterd_ids = $color_firtered_details['filter_ids'];
       
       if(empty($color_filterd_ids)){
           $color_filterd_ids[] = "999999999";
       }
       
       
		if(strlen($filter_color_sql) > 0){
		    $filter_color_sql_final = $filter_color_sql;
		}
		
		// ###################################
		// Filter options  END
		// ###################################
				
		// ###################################
		// Filter icons  START
		// ###################################
		//get client icons
		$vw_icons = new VoluntaryworkersIcons();
		if(strlen($_REQUEST['icons_filter']) > 0 && $_REQUEST['icons_filter'] != "0"){ 
		    $filter_icons = explode(',',$_REQUEST['icons_filter']);
            $filter_by_icons = $vw_icons->filter_icons(false,$filter_icons );
            $filter_by_icons = array_unique($filter_by_icons);
            
		} else {
		    $filter_by_icons = array();
		}
		// ###################################
		// Filter icons  END
		// ###################################
				
		// ###################################
		// Filter work  START
		// ###################################
		$filter_by_work_schedule = array();
 
		if(strlen($_REQUEST['work_day_filter']) > 0  || strlen($_REQUEST['work_period_filter']) > 0 ){ 
		    
		    $work_day = !empty($_REQUEST['work_day_filter']) ? $_REQUEST['work_day_filter'] : false;
		    $work_period_fiter = !empty($_REQUEST['work_period_filter']) ? $_REQUEST['work_period_filter'] : false;
            $filter_by_work_schedule = VoluntaryworkersAvailabilityScheduleTable::find_vw_activity_schedule_by_work($work_day,$work_period_fiter,$clientid );
            $filter_by_work_schedule = array_unique($filter_by_work_schedule);
            
		} else {
		    $filter_by_work_schedule = array();
		}
		// ###################################
		// Filter work  END
		// ###################################

		//filter by columns
		$allowed_columns_4regex = $columns_array;
		
		$column_search_by = array();
		if ($search_value == "") {
			foreach ($_POST['columns'] as $column) {
				if ($column['searchable'] == "true" 
						&& in_array($column['data'], $allowed_columns_4regex)
						&& $column['search']['value'] != "") 
				{

					$column_search_by[ $column['data'] ] = $column['search']['value'];

				}

			}
		}
		
		
		
		
		
		if (isset($search_value) && strlen($search_value) > 0)
		{
			//ISPC 1739
// 			$regexp = mb_strtolower(preg_quote(trim($search_value)), 'UTF-8') ;		
			//@claudiu 12.2017, changed Pms_CommonData::value_patternation	
			$regexp = trim($search_value);		
			Pms_CommonData::value_patternation($regexp);
			
		// ###################################
		// search by Hospice association START
		// ###################################
		    //search hospice associations
		    $hospice_association_q = Doctrine_Query::create()
		    ->select("*")
		    ->from('Hospiceassociation')
		    ->where('clientid = "' . $clientid . '"')
		    //ISPC 1739
		    //->andWhere("isdelete=0 and  hospice_association REGEXP '".$regexp ."'");
		    ->andWhere("isdelete= ? and  hospice_association like ?" ,array("0","%" . addslashes(trim($search_value)) . "%"));
		    $hospice_association_array = $hospice_association_q->fetchArray();
		
		    
		    $hospice_association_ids = array();
		    if($hospice_association_array){

		        foreach($hospice_association_array as $k=>$haso){
		            $hospice_association_ids_str .= '"'.$haso['id'].'",';
		            $hospice_association_ids[] = $haso['id'];
		        }
		    }
		    
		    $hospice_association_sql="";
		    if(!empty($hospice_association_ids)){
		        $hospice_association_sql = " OR hospice_association in (".substr($hospice_association_ids_str,0,-1).") ";
		    }
		    
		// ###################################
		// search by Hospice association END
		// ###################################
		
		
		// ###################################
		// search by primary status START
		// ###################################
		    $statusesd_prima_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('VoluntaryworkersPrimaryStatuses')
		    //ISPC 1739
		    //->where("description REGEXP '".$regexp ."'");
		    ->where("description like ?","%" . addslashes(trim($search_value)) . "%");
		    $primary_statuses_details_array = $statusesd_prima_q ->fetchArray();
		    
		    $primary_statuses_ids_str = "";
		    if(!empty($primary_statuses_details_array)){
		        foreach($primary_statuses_details_array as $k=>$pst_data) {
    		        $primary_statuses_ids[] = $pst_data['status_id'];
    		        $primary_statuses_ids_str .= '"'.$pst_data['status_id'].'",';
		        }

		        $primary_statuses_sql="";
		        if(!empty($primary_statuses_ids)){
		            $primary_statuses_sql = " OR status in (".substr($primary_statuses_ids_str,0,-1).") ";
		        }
		    }
		// ###################################
		// search by primary status END
		// ###################################
		
		
		
		// ###################################
		// search by secondary status START
		// ###################################
		    $statusesd_q = Doctrine_Query::create()
		    ->select('*')
		    //ISPC-2054(voluntaryworkers statuses updated by client)
		    //->from('VoluntaryworkersStatusesDetails')
		    ->from('VoluntaryWorkersSecondaryStatuses')
		    //ISPC 1739
		    //->where("description REGEXP '".$regexp ."'");
		    ->where("LOWER(description) like ?", "%" . addslashes(trim($search_value)) . "%")
		    ->andWhere("clientid =? and isdelete =?", array($clientid, '0'));
	   
		    $statuses_details_array = $statusesd_q ->fetchArray();
		   
		    if(!empty($statuses_details_array)){
		        foreach($statuses_details_array as $k=>$st_data) {
		        	//ISPC-2054(voluntaryworkers statuses updated by client)
    		        //$statuses_ids[] = $st_data['status_id'];
		        	$statuses_ids[] = $st_data['id'];
		        }
		        
		        if(!empty($statuses_ids)){
		            $statuses2vw_Q = Doctrine_Query::create()
		            ->select('*')
		            ->from('VoluntaryworkersStatuses')
		            ->where('clientid = ?', $clientid)
		            //ISPC-2054(voluntaryworkers statuses updated by client)
		            ->andWhereIn('status', $statuses_ids);
		           //->andWhereIn('status_id', $statuses_ids);
		            $statuses2vw_res = $statuses2vw_Q->fetchArray();
		            
		            
		            if(!empty($statuses2vw_res)){
		                foreach($statuses2vw_res as $k=>$vw2st){
		                    $secondary_statuses_ids_str .= '"'.$vw2st['vw_id'].'",';
		                    $secondary_statuses_ids[] = $vw2st['vw_id'];
		                }
		            }
		        }
		        $secondary_statuses_sql="";
		        if(!empty($secondary_statuses_ids)){
		            $secondary_statuses_sql = " OR id in (".substr($secondary_statuses_ids_str,0,-1).") ";
		        }
		    }
		// ###################################
		// search by secondary status END
		// ###################################
		
		
		// ##################################
		// search by patient START
		// ##################################
		    $search_string = addslashes(urldecode(trim($search_value)));
		    //search  active patients
		    $patietns_act_search_q = Doctrine_Query::create()
		    ->select("p.ipid,
    				e.epid,
    				AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name,
    				AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name
    				")
		    				->from('EpidIpidMapping e')
		    				->leftJoin('e.PatientMaster p')
		    				->where('e.ipid = p.ipid')
		    				->andWhere('e.clientid = ? ' , $this->clientid )
		    				->andWhere('p.isstandby = "0"')
		    				->andWhere('p.isdischarged = "0"')
		    				->andWhere('p.isdelete = "0"')
		    				->andWhere('p.isstandbydelete = "0"');
		    				//ISPC 1739 REGEXP '".$regexp ."'"
		    				$patietns_act_search_q->andwhere("( e.clientid = ?	AND lower(e.epid) like (?) 
		    						OR (CONVERT(lower(CONCAT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "'), ' ',
		    												AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "'), ' ' ))  USING utf8) 
		    							REGEXP ? )
		    						
		    						OR (CONVERT(lower(CONCAT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "'), ' ',
		    												AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "'), ' ' )) USING utf8) 
		    						REGEXP ? ))"
		    						, array(
		    							$this->clientid, //0
		    							"%" . trim(strtolower($search_string)) . "%", //1
		    							$regexp ,//2
		    							$regexp, //3
		    								
		    						)
		    						);
		    				/*
		    				 * original query before ispc-1739
		    $patietns_act_search_q->andwhere("e.clientid = " . $this->clientid . " and trim(lower(e.epid)) like trim(lower('%" . $search_string . "%')) or (trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%')) or trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%'))  or concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
    						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
    						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
    						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')))");
    						*/
		    $search_active_patients = $patietns_act_search_q->fetchArray();
		    
		    
    		if (!empty($search_active_patients))
    		{
    		    foreach ($search_active_patients as $k_patient => $v_patient)
    		    {
    		        $search_active_ipids[] =  $v_patient['PatientMaster']['ipid'];
    		    }
    		
    		    $all_parent2child = $voluntary_workers->get_all_parent2child_workers($clientid);
    		
    		    $used_vwids_str = '"XXXXXXXXX",';
    		    $used_vwid = array();
    		    $worker2shactivepatients = $patient_voluntary_workers->get_workers2patients($all_parent2child['vw_ids'], $search_active_ipids);
    		    foreach($worker2shactivepatients as $vwid=>$patipid){
    		
    		        $used_vwid[] = $all_parent2child['vwid2parent'][$vwid];
    		        $used_vwids_str .= '"'.$all_parent2child['vwid2parent'][$vwid].'",';
    		    }
    		    if (!empty($used_vwid)){
    		        $user_sql .= " OR id in (".substr($used_vwids_str,0,-1).") ";
    		    }
    		    
    		}
		// ###############################
		// search by patient END
		// #############################
		} elseif ( isset($column_search_by['patients']) ) {
				
			// ###############################
			// search by patient column
			// #############################
			$used_vwid = array();
			
			$all_parent2child = $voluntary_workers->get_all_parent2child_workers_ids($clientid);
			$worker2shactivepatients = $patient_voluntary_workers->get_workers2patients($all_parent2child['vw_ids']);

			if ("^$" == $column_search_by['patients']) {
				//vw with no patiets
				foreach($worker2shactivepatients as $vwid=>$patipid) {
					$used_vwid[] = $all_parent2child['vwid2parent'][$vwid];
				}
				
			} elseif ( ! empty($worker2shactivepatients)) {
			
				$search_active_patients = PatientMaster::search_patients($column_search_by['patients'], array(
				    'clientid' => $clientid,
				    array('whereIn' => 'ipid', 'params' => $worker2shactivepatients)
				));
				if ( ! empty($search_active_patients) ) {
						
				    $filtered_ipids = array_column(array_column($search_active_patients, 'PatientMaster'), 'ipid');
				    
				    $filteredWorker2shactivepatients = array_filter($worker2shactivepatients, function($value) use ($filtered_ipids) {
				        return in_array($value, $filtered_ipids);
				    });
				    
				    
// 					$search_active_ipids = array();
					foreach ($search_active_patients as $patient) {
						$search_active_ipids[] =  $v_patient['PatientMaster']['ipid'];
					}
					
// 					$worker2shactivepatients = $patient_voluntary_workers->get_workers2patients($all_parent2child['vw_ids'], $search_active_ipids);
					foreach($filteredWorker2shactivepatients as $vwid=>$patipid) {	
						$used_vwid[] = $all_parent2child['vwid2parent'][$vwid];
					}
				}		
			}
			
		}
		
		$templates_data = VwLetterTemplates::get_all_letter_templates($this->clientid );
		if($templates_data){
		    foreach($templates_data as $k => $tpl){
		        $templates[$tpl['id']] = $tpl;
		    }
		}
		$this->view->letter_templates = $templates;
		
		// ########################################		
		// #####  Query for count ###############
		$fdoc1 = Doctrine_Query::create();
		$fdoc1->select('id, count(*) as count');
		$fdoc1->from('Voluntaryworkers as v');
		$fdoc1->where("isdelete = 0  " . $where);
		$fdoc1->andWhere("indrop = 0  "); 
		
		/* ------------- Search options ------------------------- */
		if (isset($search_value) && strlen($search_value) > 0)
		{
		    //$fdoc1->andWhere("first_name like '%" . trim($search_value) . "%'  or last_name like '%" . trim($search_value) . "%' or  zip like '%" . trim($search_value) . "%' or  city like '%" . trim($search_value) . "%' or  phone like '%" . trim($search_value) . "%' or  mobile like '%" . trim($search_value) . "%' or  email like '%" . trim($search_value) . "%'   ".$user_sql." ".$hospice_association_sql."  ".$secondary_statuses_sql."  ".$primary_statuses_sql."  ");
		    //ISPC 1739
		    $fdoc1->andWhere("CONVERT( lower(CONCAT( first_name, ' ',last_name,' ',' ',zip,' ',city,' ',phone,' ', mobile,' ',email )) USING utf8 ) REGEXP '".$regexp ."'  ".$user_sql." ".$hospice_association_sql."  ".$secondary_statuses_sql."  ".$primary_statuses_sql."  ");
		}
		//ISPC-1977 p4 //filter by column searches
		elseif( ! empty($column_search_by)) {

			foreach($column_search_by as $column_name => $column_regex) 
			{
				
				if (substr($column_regex, 0, 3) == "!=^") {
					//not	
					if ('patients' == $column_name) {
						if( ! empty($used_vwid)) $fdoc1->andWhereIn("id" , $used_vwid);
					} else {	
						$column_regex = substr($column_regex, 2, strlen($column_regex));
						Pms_CommonData::value_patternation($column_regex, false, false, false);					
						$fdoc1->andWhere("{$column_name} NOT REGEXP ? " , $column_regex);
					}
					
				} else {

					if ('patients' == $column_name ) {
						if ($column_regex == "^$" && ! empty($used_vwid)){
							$fdoc1->andWhereNotIn("id" , $used_vwid);
						} elseif( ! empty($used_vwid)) {
							$fdoc1->andWhereIn("id" , $used_vwid);
						} else {
							$fdoc1->andWhere("id IS NULL");
						}
					} 
                    else if ('status' == $column_name && substr($column_regex, -1) != "$" ) {
    					    $fdoc1->leftJoin("v.VoluntaryworkersStatuses vs");
    					    $fdoc1->andWhereIn("vs.status",array($column_regex));
                    }					
					else 
					{
						Pms_CommonData::value_patternation($column_regex, false, false, false);
						$fdoc1->andWhere(" {$column_name}  REGEXP ?" , $column_regex);
					}
				}
			}
		}
		
		
		
		if ($user_filters && count($color_filterd_ids) > 0)
		{
		    $fdoc1->andWhereIn('id', $color_filterd_ids );
		}
		
		if(is_array($filter_by_icons) &&  !empty($filter_by_icons))
		{ 
		    $fdoc1->andWhereIn('id',$filter_by_icons );
		}
		
		
		if(is_array($filter_by_work_schedule) &&  !empty($filter_by_work_schedule))
		{ 
		    $fdoc1->andWhereIn('id',$filter_by_work_schedule );
		}
		
		if($ufilter['status_inactive'] == "1" ){
		    $fdoc1->andWhere('inactive = "1"');
		}		

		// Filter by status ISPC - 2114 - archive function for vw
		if(strlen($_REQUEST['isarchived']) > 0){
			$filter_isarchived = $_REQUEST['isarchived'];			
			$fdoc1->andWhere('isarchived = ?', $filter_isarchived);
		}
		
		// Filter by status ISPC-2401 - education function for vw
		if(strlen($_REQUEST['ineducation']) > 0){
		    $filter_ineducation = $_REQUEST['ineducation'];
		    $fdoc1->andWhere('ineducation = ?', $filter_ineducation);
		}
// 		$fdoc1->orderBy($order_by_str);


		$fdocexec = $fdoc1->execute();
		$fdocarray = $fdocexec->toArray();
		
		$full_count  = $fdocarray[0]['count'];
		
		// ########################################		
		// #####  Query for details ###############		

		$vw_sql = '*,';
		$vw_sql .= 'IF(has_car = "1","Ja","Nein") as has_car,';
		$vw_sql .= "IF(birthdate != '0000-00-00',DATE_FORMAT(birthdate,'%d\.%m\.%Y'),'') as birthdate,";
		$vw_sql .= "IF((gc_certificate_date != '0000-00-00 00:00:00' && gc_certificate = 2) ,Concat('Ja',' (',DATE_FORMAT(gc_certificate_date,'%d\.%m\.%Y'),')'  ),'Nein') as gc_certificate_date,";
		$fdoc1->select($vw_sql);
		$fdoc1->where("isdelete = 0  " . $where);
		$fdoc1->andWhere("indrop = 0  ");
		/* ------------- Search options ------------------------- */
		if (isset($search_value) && strlen($search_value) > 0)
		{
			//ISPC 1739
			$fdoc1->andWhere("first_name REGEXP '".$regexp."' or last_name REGEXP '".$regexp."' or  zip like '%" . trim($search_value) . "%' or  city REGEXP '".$regexp ."' or  phone like '%" . trim($search_value) . "%' or  mobile like '%" . trim($search_value) . "%' or  email REGEXP '".$regexp ."'   ".$user_sql." ".$hospice_association_sql."  ".$secondary_statuses_sql."  ".$primary_statuses_sql."  ");
			//$fdoc1->andWhere("first_name like '%" . trim($search_value) . "%'  or last_name like '%" . trim($search_value) . "%' or  zip like '%" . trim($search_value) . "%' or  city like '%" . trim($search_value) . "%' or  phone like '%" . trim($search_value) . "%' or  mobile like '%" . trim($search_value) . "%' or  email like '%" . trim($search_value) . "%'   ".$user_sql." ".$hospice_association_sql."  ".$secondary_statuses_sql."  ".$primary_statuses_sql."  ");
		}
		//ISPC-1977 p4 //filter by column searches
		elseif( !empty($column_search_by)) {
				
			foreach($column_search_by as $column_name => $column_regex) 
			{
			
				if (substr($column_regex,0,3) == "!=^") {
					//not	
					if ('patients' == $column_name) {
						if( ! empty($used_vwid)) $fdoc1->andWhereIn("id" , $used_vwid);
					} else {	
						$column_regex = substr($column_regex,2,strlen($column_regex));
						Pms_CommonData::value_patternation($column_regex, false, false, false);					
						$fdoc1->andWhere("{$column_name} NOT REGEXP ? " , $column_regex);
					}
					
				} else {

					if ('patients' == $column_name ) {
						if ($column_regex == "^$" && ! empty($used_vwid)){
							$fdoc1->andWhereNotIn("id" , $used_vwid);
						} elseif( ! empty($used_vwid)) {
							$fdoc1->andWhereIn("id" , $used_vwid);
						} else {
							$fdoc1->andWhere("id IS NULL");
						}
					} 
				    else if ('status' == $column_name && substr($column_regex, -1) != "$") {
    					    $fdoc1->andWhereIn("vs.status",array($column_regex));
                    }	
					else {
						Pms_CommonData::value_patternation($column_regex, false, false, false);					
						$fdoc1->andWhere(" {$column_name}  REGEXP ?" , $column_regex);
					}
				}
			}
		}
		
        if ($user_filters && count($color_filterd_ids ) > 0)
        {
            $fdoc1->andWhereIn('id',$color_filterd_ids );
        }
        
        if(is_array($filter_by_icons) &&  !empty($filter_by_icons))
        {
            $fdoc1->andWhereIn('id',$filter_by_icons );
        }
        
        
        if(is_array($filter_by_work_schedule) &&  !empty($filter_by_work_schedule))
        {
            $fdoc1->andWhereIn('id',$filter_by_work_schedule );
        }
        
		if($ufilter['status_inactive'] == "1" ){
		    $fdoc1->andWhere('inactive = "1"');
		}
		
		// Filter by status ISPC - 2114 - archive function for vw
		if(strlen($_REQUEST['isarchived']) > 0){
			$filter_isarchived = $_REQUEST['isarchived'];
			$fdoc1->andWhere('isarchived = ?', $filter_isarchived);
		}
		
		// Filter by status ISPC-2401 - education function for vw
		if(strlen($_REQUEST['ineducation']) > 0){
		    $filter_ineducation = $_REQUEST['ineducation'];
		    $fdoc1->andWhere('ineducation = ?', $filter_ineducation);
		}
		
		if($limit != "-1"){ // -1 = list all
	       	$fdoc1->limit($limit);
    		$fdoc1->offset($offset);
		}
		
		$fdoc1->orderBy($order_by_str);

		$fdoclimitexec = $fdoc1->execute();
		$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());

		
		
		if ( ! $fdoclimitexec->count()) {
		    /*
		     * if your $fdoclimitexec is empty.. 
		     * then you have NO results... 
		     * STOP using the server's resources and time ...
		     * do NOT search for the vw with the id 99999999999999, this vw may not belong to this clientid
		     */
		    //TODO: so jump out, 
		    
		    $this->returnDatatablesEmptyAndExit();
		}
		
//         $voluntary_workers_ids[] = '99999999999999';
        $voluntary_workers_ids = array();
		foreach ($fdoclimit as $key => $voluntary_worker_item)
		{
			$fdoclimit_arr[$voluntary_worker_item['id']] = $voluntary_worker_item;

			if ($voluntary_worker_item['hospice_association'] > 0)
			{
				$fdoclimit_arr[$voluntary_worker_item['id']]['hospice_association'] = $h_assoc_data[$voluntary_worker_item['hospice_association']];
			}
			else
			{
				$fdoclimit_arr[$voluntary_worker_item['id']]['hospice_association'] = '-';
			}

			$voluntary_workers_ids[] = $voluntary_worker_item['id'];
		}
		$primary_status_arr = Pms_CommonData::get_primary_voluntary_statuses();
		//ISPC-2054(voluntaryworkers statuses updated by clients)
		//$status_arr = Pms_CommonData::getVoluntaryWorkersStatuses();
		$status_arr = VoluntaryWorkersSecondaryStatuses::get_secondarystatuses($clientid);
		
		foreach ($status_arr as $k_status => $v_status)
		{
			$statuses[$v_status['id']] = $v_status['status'];
		}
		$this->view->status_array = $statuses;

		foreach($primary_status_arr as $k_pri_status => $v_pri_status)
		{
			$statuses[$v_pri_status['id']] = $v_pri_status['status'];
		}		
		
		foreach ($fdoclimit as $keysst => $voluntary_worker_item_cst)
		{
		    if(!empty($color_firtered_details['statuses'][$voluntary_worker_item_cst['id']])){
    		    $fdoclimit_arr[$voluntary_worker_item_cst['id']]['status_color'] = $color_firtered_details['statuses'][$voluntary_worker_item_cst['id']][0]['status'];
		    } else{
    		    $fdoclimit_arr[$voluntary_worker_item_cst['id']]['status_color'] = $voluntary_worker_item_cst['status_color'];
		    }
		}
		
		//TODO: change this next fn to take into account $voluntary_workers_ids ... now you fetch ALL client's, despite you have a paginate(limit) and a patients with vws condition
		//get active patients
		$patietns_act = Doctrine_Query::create()
		->select("p.ipid,
				e.epid,
				AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name,
				AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name
				")
				->from('EpidIpidMapping e')
				->leftJoin('e.PatientMaster p')
				->where('e.ipid = p.ipid')
				->andWhere('e.clientid = "' . $this->clientid . '"')
				->andWhere('p.isstandby = "0"')
				->andWhere('p.isdischarged = "0"')
				->andWhere('p.isdelete = "0"')
				->andWhere('p.isstandbydelete = "0"');
		$active_patients = $patietns_act->fetchArray();

		if (!empty($active_patients))
		{
		    foreach ($active_patients as $k_patient => $v_patient)
		    {
		        $patients_epids_selector[$v_patient['PatientMaster']['id']] = strtoupper($v_patient['epid']); //used in patients dropdown
		        $patients_ipids_selector[$v_patient['PatientMaster']['id']] = $v_patient['PatientMaster']['ipid']; //used to match patient_master id with ipid
		        $active_ipids[] =  $v_patient['PatientMaster']['ipid'];
		        $patient_details[$v_patient['PatientMaster']['ipid']] = $v_patient['PatientMaster']['last_name'].' '.$v_patient['PatientMaster']['first_name'];
		    }
		}
		
		if(empty($active_ipids)){
		    $active_ipids[] = "XXXXXX";
		}
		

		$parent2child = $voluntary_workers->parent2child_workers ($clientid,$voluntary_workers_ids);
		$worker2activepatients = $patient_voluntary_workers->get_workers2patients($parent2child['vw_ids'], $active_ipids,false, $currently_connected = true);
		
		foreach($worker2activepatients as $vwid=>$patipid){
		    if(!in_array($patient_details[$patipid],$pat2master[$parent2child['vwid2parent'][$vwid]])){
		        $pat2master[$parent2child['vwid2parent'][$vwid]][] = $patient_details[$patipid];
		    }
		}
		
		$voluntary_workers_statuses = new VoluntaryworkersStatuses();
		$worker_statuses = $voluntary_workers_statuses->get_voluntaryworker_statuses($voluntary_workers_ids, $clientid);

		foreach ($worker_statuses as $k_data => $v_data)
		{
			foreach ($v_data as $k_vdata => $v_vdata)
			{
				if($statuses[$v_vdata])
				{
					$worker_statuses_arr[$k_data][] = $statuses[$v_vdata];
				}
			}
		}
//var_dump($worker_statuses_arr); exit;
		foreach ($worker_statuses_arr as $k_vw_id => $v_vw_statuses)
		{
			$fdoclimit_arr[$k_vw_id]['statuses'] = $v_vw_statuses;
		}

		foreach ($fdoclimit as $key => $voluntary_worker_item)
		{
			if (!empty($pat2master[$voluntary_worker_item['id']]))
			{
				$fdoclimit_arr[$voluntary_worker_item['id']]['patients'] =  implode(',<br/>',$pat2master[$voluntary_worker_item['id']]);
			}
			else
			{
				$fdoclimit_arr[$voluntary_worker_item['id']]['patients'] =    '-';
			}
		}
		
		$worker_icons_arr  = $vw_icons->get_icons($voluntary_workers_ids);
		// client_ic
		$icons_client = new IconsClient();
		$client_icons = $icons_client->get_client_icons($clientid, false ,'icons_vw');
		$thsi->view->client_icons = $client_icons;
		
		foreach ($worker_icons_arr as $k_vw_id => $v_vw_icons)
		{
		    if($client_icons[$v_vw_icons['icon_id']]['image']){
    		    $fdoclimit_arr[$v_vw_icons['vw_id']]['icons'] .= '<span class="vw_list_icon" style="background:#'.$client_icons[$v_vw_icons['icon_id']]['color'].'"><img src="'.APP_BASE.'icons_system/'.$client_icons[$v_vw_icons['icon_id']]['image'].'"   title="'.$client_icons[$v_vw_icons['icon_id']]['name'].'"    /></span> '; 
		    } else{
    		    $fdoclimit_arr[$v_vw_icons['vw_id']]['icons'] .= '<span class="vw_list_icon"  style="background:#'.$client_icons[$v_vw_icons['icon_id']]['color'].'"><p></p></span> '; 
		    }
		}		
		
		/*$color_statuses = array(
		    'g'=>'ready_green',
		    'y'=>'ready_yellow',
		    'r'=>'ready_red',
		    'b'=>'ready_black'
		);*/
		
		$all_colors = VoluntaryworkersColorAliasesTable::get_coloralias_array($clientid);
		foreach ($all_colors as $status_id => $col_status_name){
		    $color_statuses[$status_id] = 'ready_'.$status_id;
		}
		
		$row_id = 0;
		$link = "";
		$resulted_data = array();
		
		//ISPC-1977 p5
		$cokoordinators_arr = array(); //this will hold the co-koordinators association with a nicename
		$vw_cok_obj = new VoluntaryworkersCoKoordinator();
		$cokoordinators = $vw_cok_obj->get_multiple_cokoordinators_by_vwid($voluntary_workers_ids);
		if (is_array($cokoordinators) && count($cokoordinators)>0) {
			$vw_id_koordinator_arr = array_unique(array_column($cokoordinators, 'vw_id_koordinator'));
			
			$vw_id_koordinator_arr = Voluntaryworkers::getVoluntaryworkersNiceName($vw_id_koordinator_arr , $clientid);
			
			foreach ($cokoordinators as $row) {
				$cokoordinators_arr[$row['vw_id']] = $vw_id_koordinator_arr[$row['vw_id_koordinator']] ['nice_name'];
			}
		}
		
		//========= sorting columns===============
		$unsort = $fdoclimit_arr;
		if($_REQUEST['columns'][$order_column]['data'] == "patients" ){
			$fdoclimit_arr = $this->array_sort($unsort, 'patients', SORT_ASC);
				
		}
		
		foreach($fdoclimit_arr as $v_id =>$vw_data){
		    //ISPC-2560 Lore 06.03.2020
		    $refer_tab = '';

		    if($vw_data['ineducation'] == 1){
	            $refer_tab = 'ineducation';
	        }
	        if($vw_data['isarchived'] == 1){
	            $refer_tab = 'isarchived';
	        }
            $link = '<a href="'.APP_BASE .'voluntaryworkers/editvoluntaryworkerdet?id='.$vw_data['id'].'&ref_tab='.$refer_tab.'"> %s </a>';
	        //.
		    //$link = '<a href="'.APP_BASE .'voluntaryworkers/editvoluntaryworkerdet?id='.$vw_data['id'].'"> %s </a>';
		    $resulted_data[$row_id]['select_vw'] = '<input type="checkbox" name="vws[]" class="vws" value="'.$vw_data['id'].'"/>';
		    $resulted_data[$row_id]['status_color'] = '<div class="ready_box_voluntary '.$color_statuses[$vw_data['status_color']].'"></div>';
		    $resulted_data[$row_id]['hospice_association'] = sprintf($link,$vw_data['hospice_association']);
		    $resulted_data[$row_id]['status'] = sprintf($link,implode(', ',$vw_data['statuses']));
		    $resulted_data[$row_id]['salutation'] = sprintf($link,$vw_data['salutation']);
		    $resulted_data[$row_id]['first_name'] = sprintf($link,$vw_data['first_name']);
		    $resulted_data[$row_id]['last_name'] = sprintf($link,$vw_data['last_name']);
		    $resulted_data[$row_id]['birthdate'] = sprintf($link,$vw_data['birthdate']);
            //ISPC-2884,Elena,14.04.2021
		    $gender_key = $vw_data['gender'];
            if($gender_key == 0){
                $resulted_data[$row_id]['gender'] = sprintf($link,' ');;
            }else{
                $resulted_data[$row_id]['gender'] = sprintf($link,Pms_CommonData::getGenderMember()[$gender_key]);
            }

		    $resulted_data[$row_id]['street'] = sprintf($link,$vw_data['street']);
		    $resulted_data[$row_id]['zip'] = sprintf($link,$vw_data['zip']);
		    $resulted_data[$row_id]['city'] = sprintf($link,$vw_data['city']);
		    $resulted_data[$row_id]['phone'] = sprintf($link,$vw_data['phone']);
		    $resulted_data[$row_id]['mobile'] = sprintf($link,$vw_data['mobile']);
		    $resulted_data[$row_id]['email'] = sprintf($link,$vw_data['email']);
		    
		    $resulted_data[$row_id]['comments'] = sprintf($link,$vw_data['comments']);
		    
		    $resulted_data[$row_id]['comments_availability'] = sprintf($link,$vw_data['comments_availability']);      //ISPC-2617 Lore 22.07.2020
		    
		    $resulted_data[$row_id]['children'] = sprintf($link,$vw_data['children']);
		    $resulted_data[$row_id]['profession'] = sprintf($link,$vw_data['profession']);
		    $resulted_data[$row_id]['appellation'] = sprintf($link,$vw_data['appellation']);
		    $resulted_data[$row_id]['edication_hobbies'] = sprintf($link,$vw_data['edication_hobbies']);
		    $resulted_data[$row_id]['special_skils'] = sprintf($link,$vw_data['special_skils']);
		    
		    $resulted_data[$row_id]['gc_certificate_date'] = sprintf($link,$vw_data['gc_certificate_date']);
		    
		    $resulted_data[$row_id]['has_car'] = sprintf($link,$vw_data['has_car']);
		    $resulted_data[$row_id]['patients'] = sprintf($link,$vw_data['patients']);
		    
		    $resulted_data[$row_id]['cokoordinator'] = isset($cokoordinators_arr[$vw_data['id']]) ? $cokoordinators_arr[$vw_data['id']] : "";
		    
		    
		    $resulted_data[$row_id]['icons'] = sprintf($link,$vw_data['icons']);
		    
		    //ISPC - 2231 p.1+2
		    if($vw_data['img_deleted'] != 1 && $vw_data['img_path'] != "")
		   	{
		    	$resulted_data[$row_id]['image'] = '<a href="'.APP_BASE .'voluntaryworkers/editvoluntaryworkerdet?id='.$vw_data['id'].'"><img width="30" height="30" src="'.APP_BASE.'/icons_system/'.$vw_data['img_path'].'?='. time().'" /></a>';
		  	}
		  	else
		  	{
		 		$resulted_data[$row_id]['image'] = '';
		 	}
		 	
		 	if($vw_data['change_date'] != '0000-00-00 00:00:00')
		 	{
		 		$resulted_data[$row_id]['change_date'] = sprintf($link, date('d.m.Y', strtotime($vw_data['change_date'])));
		 	}
		 	else
		 	{
		 		$resulted_data[$row_id]['change_date'] = '';
		 	}
		 	//ISPC - 2231 p.1+2
		 	
		 	//ISPC-2560 Lore 02.03.2020		 	
		 	if($_REQUEST['ineducation']){
    		    $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'voluntaryworkers/editvoluntaryworkerdet?ref_tab=ineducation&id='.$vw_data['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="confirm_button" rel="'.$vw_data['id'].'" id="delete_'.$vw_data['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
		 	} elseif($_REQUEST['isarchived']){
		 	    $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'voluntaryworkers/editvoluntaryworkerdet?ref_tab=isarchived&id='.$vw_data['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="confirm_button" rel="'.$vw_data['id'].'" id="delete_'.$vw_data['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
		 	} else
		 	{
	       	    $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'voluntaryworkers/editvoluntaryworkerdet?id='.$vw_data['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="confirm_button" rel="'.$vw_data['id'].'" id="delete_'.$vw_data['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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

	public function deletevoluntaryworkerAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->_helper->viewRenderer('voluntaryworkerslist');

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('voluntaryworker', $this->userid, 'candelete');

		if ($return)
		{
			$ids = split(",", $_GET['id']);

			for ($i = 0; $i < sizeof($ids); $i++)
			{
				$thrash = Doctrine::getTable('Voluntaryworkers')->find($_GET['id']);
				$thrash->isdelete = 1;
				$thrash->save();
				$this->view->delete_message = "Record deleted sucessfully";
			}
		}
		else
		{

			$this->_redirect(APP_BASE . "error/previlege");
		}
	}

	public function deletevoluntaryworkerdetAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->_helper->viewRenderer('workersdetailslist');
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('voluntaryworker', $this->userid, 'candelete');

		if ($return)
		{
			$ids = split(",", $_GET['id']);

			for ($i = 0; $i < sizeof($ids); $i++)
			{
				$thrash = Doctrine::getTable('Voluntaryworkers')->find($_GET['id']);
				$thrash->isdelete = 1;
				$thrash->save();
				$this->view->delete_message = "Record deleted sucessfully";
			}
		}
		else
		{

			$this->_redirect(APP_BASE . "error/previlege");
		}
	}

 
	public function fetchdropdownAction ()
	{
		$this->_helper->viewRenderer('patientmasteradd');
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $this->clientid;

		if (strlen($_REQUEST['ltr']) > 0)
		{

			$drop = Doctrine_Query::create()
			->select('*')
			->from('Voluntaryworkers')
			->where("clientid='" . $clientid . "' ")
			->andWhere("(trim(lower(last_name)) like ? ) or (trim(lower(first_name)) like ? )",array(trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%"))
			->andWhere("indrop = 0")
			->andWhere("isdelete = 0")
			->orderBy('last_name ASC');

			$dropexec = $drop->execute();


			$droparray = $dropexec->toArray();
			$drop_array = $droparray;
			foreach ($dropexec->toArray() as $key => $val)
			{
				$drop_array[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
				$drop_array[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
				$drop_array[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
			}
			$droparray = $drop_array;
		}
		else
		{
			$droparray = array();
		}

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "docdropdiv";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['doctors'] = $droparray;

		echo json_encode($response);
		exit;
	}

	public function workersmanagementAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');

		if ($_GET['flg'] == 'suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
		}
	}

	public function fetchliststatusAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');

		$columnarray = array("pk" => "id", "fn" => "first_name", "ln" => "last_name", "zp" => "zip", "ct" => "city", "ph" => "phone");

		$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		$this->view->order = $orderarray[$_REQUEST['ord']];
		$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

		if ($this->clientid > 0)
		{
			$where = ' and clientid=' . $this->clientid;
		}
		else
		{
			$where = ' and clientid=0';
		}

		$fdoc1 = Doctrine_Query::create()
		->select('count(*)')
		->from('Voluntaryworkers')
		->where('isdelete="0" ' . $where)
		->andWhere('indrop="0"')
		->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);

		$fdocarray = $fdoc1->fetchArray();

		$limit = 50;
		$fdoc1->select('*');
		$fdoc1->where("isdelete='0'");
		$fdoc1->andWhere("indrop='0' " . $where . " ");
		if (isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
		{
			$fdoc1->andWhere("(first_name like ?  or last_name like ? or  zip like ? or  city like ? or  phone like ?)"
					,array("%".trim($_REQUEST['val'])."%",
							"%".trim($_REQUEST['val'])."%",
							"%".trim($_REQUEST['val'])."%",
							"%".trim($_REQUEST['val'])."%",
							"%".trim($_REQUEST['val'])."%")
			);
			
		}
		$fdoc1->limit($limit);
		$fdoc1->offset($_REQUEST['pgno'] * $limit);

		$fdoclimit_array = $fdoc1->fetchArray();
		$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit_array);


		$worker_ids[] = '9999999999';
		foreach ($fdoclimit as $k_worker => $v_worker)
		{
			$worker_ids[] = $v_worker['id'];
		}

		$worker_pats = new PatientVoluntaryworkers();
		$worker_patients = $worker_pats->getVoluntaryworkersPatients($worker_ids);

		foreach ($worker_patients as $k_worker => $v_worker)
		{
			foreach ($v_worker as $k_patient => $ipid)
			{
				$worker_pats_ipids[] = $ipid;
			}
		}

		$epids = new EpidIpidMapping();
		$epids_arr = $epids->getIpidsEpids($worker_pats_ipids);

		$this->view->worker_patients = $worker_patients;
		$this->view->epids_pats = $epids_arr;

		$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "listvoluntaryworkersstatus.html");
		$this->view->voluntaryworkersgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("voluntarynavigationstatus.html", 5, $_REQUEST['pgno'], $limit);



		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['voluntaryworkerslist'] = $this->view->render('voluntaryworkers/fetchliststatus.html');

		echo json_encode($response);
		exit;
	}

	public function fetchhospizvoluntaryAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');

		$columnarray = array("pk" => "id", "fn" => "first_name", "ln" => "last_name", "st" => "street", "zp" => "zip", "ct" => "city", "ph" => "phone");

		$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		$this->view->order = $orderarray[$_REQUEST['ord']];
		$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

		if ($this->clientid > 0)
		{
			$where = ' and clientid=' . $this->clientid;
		}
		else
		{
			$where = ' and clientid=0';
		}

		$fdoc1 = Doctrine_Query::create()
		->select('count(*)')
		->from('Voluntaryworkers')
		->where('isdelete="0" ' . $where)
		->andWhere('indrop="0"')
		->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);

		$fdocarray = $fdoc1->fetchArray();

		$limit = 50;
		$fdoc1->select('*');
		$fdoc1->where("isdelete='0'");
		$fdoc1->andWhere("indrop='0' " . $where . " ");
		if (isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
		{
			$fdoc1->andWhere("(first_name like ?  or last_name like ? or  zip like ? or  city like ? or  phone like ?)"
					,array("%".trim($_REQUEST['val'])."%",
							"%".trim($_REQUEST['val'])."%",
							"%".trim($_REQUEST['val'])."%",
							"%".trim($_REQUEST['val'])."%",
							"%".trim($_REQUEST['val'])."%")
					);
		}
		$fdoc1->limit($limit);
		$fdoc1->offset($_REQUEST['pgno'] * $limit);

		$fdoclimit_array = $fdoc1->fetchArray();
		$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit_array);


		$worker_ids[] = '9999999999';
		foreach ($fdoclimit as $k_worker => $v_worker)
		{
			$worker_ids[] = $v_worker['id'];
		}

		$worker_pats = new PatientVoluntaryworkers();
		$worker_patients = $worker_pats->getVoluntaryworkersPatients($worker_ids);

		foreach ($worker_patients as $k_worker => $v_worker)
		{
			foreach ($v_worker as $k_patient => $ipid)
			{
				$worker_pats_ipids[] = $ipid;
			}
		}

		$epids = new EpidIpidMapping();
		$epids_arr = $epids->getIpidsEpids($worker_pats_ipids);

		$this->view->worker_patients = $worker_patients;
		$this->view->epids_pats = $epids_arr;

		$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "hospizvoluntaryworkers.html");
		$this->view->voluntaryworkersgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("hospizvoluntaryworkersnav.html", 5, $_REQUEST['pgno'], $limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['voluntaryworkerslist'] = $this->view->render('voluntaryworkers/fetchlisthospiz.html');

		echo json_encode($response);
		exit;
	}

	public function deleteworkervizitAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientdetails', $this->userid, 'candelete');

		if (!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$this->_helper->viewRenderer('workerdetails');

		$fdoc = Doctrine::getTable('PatientHospizvizits')->find($_GET['vizitid']);
		$fdoc->isdelete = 1;
		$fdoc->save();
		$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
		$this->_redirect(APP_BASE . 'voluntaryworkers/workerdetails?flg=suc&worker=' . $_REQUEST['worker']);
	}

	function workerdetailsAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $this->clientid;

		$worker = $_REQUEST['worker'];

		//get active patients
		$patietns_act = Doctrine_Query::create()
		->select('p.ipid, e.epid')
		->from('EpidIpidMapping e')
		->leftJoin('e.PatientMaster p')
		->where('e.ipid = p.ipid')
		->andWhere('e.clientid = "' . $clientid . '"')
		->andWhere('p.isstandby = "0"')
		->andWhere('p.isdischarged = "0"')
		->andWhere('p.isdelete = "0"')
		->andWhere('p.isstandbydelete = "0"');
		$active_patients = $patietns_act->fetchArray();

		if (!empty($active_patients))
		{
			$patients_epids_selector[] = $this->view->translate('select_patient');
			foreach ($active_patients as $k_patient => $v_patient)
			{
				$patients_epids_selector[$v_patient['PatientMaster']['id']] = strtoupper($v_patient['epid']); //used in patients dropdown
				$patients_ipids_selector[$v_patient['PatientMaster']['id']] = $v_patient['PatientMaster']['ipid']; //used to match patient_master id with ipid
			}
		}
		$this->view->patients_epids_selector = $patients_epids_selector;

		//get reasons
		$hospizv = new PatientHospizvizits();
		$grundarray = $hospizv->gethospizvreason();
		$this->view->grundarray = $grundarray;


		if ($this->getRequest()->isPost())
		{
			$post = $_POST['simple'];
			if (!empty($post))
			{
				$docform = new Application_Form_VoluntaryWorkers();
				if ($docform->validate_multiple_simple($post))
				{
					$docinfo = $docform->InsertDataMultiple($post, $patients_ipids_selector);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'voluntaryworkers/workerdetails?flg=suc&worker=' . $_REQUEST['worker']);
				}
				else
				{
					$docform->assignErrorMessages();
					$this->retainValues($_POST['simple'], 's_');
				}
			}
		}


		if (!empty($_REQUEST['worker']))
		{

			$worker_q = Doctrine::getTable('Voluntaryworkers')->find($worker);

			if ($worker_q)
			{
				$worker_details = $worker_q->toArray();

				$this->retainValues($worker_details);

				//get hospiz vizits
				$hospiz_vizits = $hospizv->getworkervisits($worker, "n", true);

				//construct vizits ipids array
				$worker_pats_ipids[] = '99999999999';

				foreach ($hospiz_vizits as $vizits)
				{
					$worker_pats_ipids[] = $vizits['ipid'];
				}

				//get visited patients epids
				$epids = new EpidIpidMapping();
				$epids_arr = $epids->getIpidsEpids($worker_pats_ipids);

				//generate vizits grid

				foreach ($hospiz_vizits as $vizit)
				{
					$worker_pats_ipids[] = $vizit['ipid'];

					$patienthospizv[$vizit['id']]['wid'] = $_REQUEST['worker'];
					$patienthospizv[$vizit['id']]['vizitid'] = $vizit['id'];
					$patienthospizv[$vizit['id']]['date'] = date('d.m.Y', strtotime($vizit['hospizvizit_date']));
					$patienthospizv[$vizit['id']]['duration'] = $vizit['besuchsdauer'];
					$patienthospizv[$vizit['id']]['km'] = $vizit['fahrtkilometer'];
					$patienthospizv[$vizit['id']]['timetravel'] = $vizit['fahrtzeit'];
					$patienthospizv[$vizit['id']]['grund'] = $grundarray[$vizit['grund']];
					$patienthospizv[$vizit['id']]['epid'] = $epids_arr[$vizit['ipid']];
					$patienthospizv[$vizit['id']]['isdelete'] = $vizit['isdelete'];

					if ($vizit['nightshift'] == 1)
					{
						$patienthospizv[$vizit['id']]['nightshift'] = 'ja';
					}
					else
					{
						$patienthospizv[$vizit['id']]['nightshift'] = 'nein';
					}
				}

				if (count($patienthospizv) > 0)
				{
					$grid = new Pms_Grid($patienthospizv, 1, count($patienthospizv), "patienthospizvisits.html");
					$this->view->hospizvgrid = $grid->renderGrid();
				}
				else
				{
					$this->view->hospizvgrid = false;
				}
			}
			else
			{
				$this->_redirect(APP_BASE . "voluntaryworkers/workersmanagement?flg=notfound");
			}
		}
		else
		{
			$this->_redirect(APP_BASE . "voluntaryworkers/workersmanagement");
		}
	}

	public function editworkervizitAction ()
	{

		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $this->clientid;

		/* ######################################################### */
		//get active patients
		$patietns_act = Doctrine_Query::create()
		->select('p.ipid, e.epid')
		->from('EpidIpidMapping e')
		->leftJoin('e.PatientMaster p')
		->where('e.ipid = p.ipid')
		->andWhere('e.clientid = "' . $clientid . '"')
		->andWhere('p.isstandby = "0"')
		->andWhere('p.isdischarged = "0"')
		->andWhere('p.isdelete = "0"')
		->andWhere('p.isstandbydelete = "0"');
		$active_patients = $patietns_act->fetchArray();

		if (!empty($active_patients))
		{
			$patients_epids_selector[] = $this->view->translate('select_patient');
			foreach ($active_patients as $k_patient => $v_patient)
			{
				$patients_epids_selector[$v_patient['PatientMaster']['id']] = strtoupper($v_patient['epid']); //used in patients dropdown
				$patients_ipids_selector[$v_patient['PatientMaster']['id']] = $v_patient['PatientMaster']['ipid']; //used to match patient_master id with ipid
			}
		}

		$this->view->patients_epids_selector = $patients_epids_selector;

		if ($this->getRequest()->isPost())
		{
			$voluntary_form = new Application_Form_VoluntaryWorkers();

			if ($voluntary_form->validate($_POST))
			{
				$_POST['vizitid'] = $_REQUEST['vizitid'];
				$_POST['ipid'] = $patients_ipids_selector[$_POST['pat_id']];

				$voluntary_form->UpdateData($_POST);

				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				$this->_redirect(APP_BASE . 'voluntaryworkers/workerdetails?flg=suc&worker=' . $_REQUEST['worker']);
			}
			else
			{
				$voluntary_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		$sav = new PatientHospizvizits();
		$savarr = $sav->getPatienthospizvizitsById($_REQUEST['vizitid']);
		$this->view->grundarray = $sav->gethospizvreason();

		if ($savarr[0]['id'])
		{

			$patient_id = array_search($savarr[0]['ipid'], $patients_ipids_selector);

			$this->view->vw_id = $savarr[0]['vw_id'];
			$this->view->type = $savarr[0]['type'];
			$this->view->besuchsdauer = $savarr[0]['besuchsdauer'];
			$this->view->fahrtkilometer = $savarr[0]['fahrtkilometer'];
			$this->view->hospizvizit_date = date('d.m.Y', strtotime($savarr[0]['hospizvizit_date']));
			$this->view->fahrtzeit = $savarr[0]['fahrtzeit'];
			$this->view->grund = $savarr[0]['grund'];
			$this->view->amount = $savarr[0]['amount'];
			$this->view->nightshift = $savarr[0]['nightshift'];
			$this->view->pat_id = $patient_id;
		}
	}

	public function hospicevolunteersAction ()
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $this->clientid;


		if ($this->getRequest()->isPost())
		{
			if (!empty($_REQUEST['vid']))
			{
				$worker_id = $_REQUEST['vid'];
				$post = array();
				$post['workerid'] = $worker_id;


				foreach ($_POST as $k_post => $v_post)
				{
					$post[$worker_id][$k_post] = $v_post[$worker_id];
				}

				$voluntary_form = new Application_Form_Voluntaryworkers();
				$update_voluntaryworker = $voluntary_form->UpdateAjaxData($post);

				if ($update_voluntaryworker)
				{
					echo json_encode(array('formid' => $worker_id)); //return worker id
				}
				else
				{
					echo json_encode(array('formid' => 'FAIL')); //return worker id
				}
				exit;
			}
		}
	}

	private function array_sort($array, $on=NULL, $order=SORT_ASC)
	{
		$new_array = array();
		$sortable_array = array();


		if (count($array) > 0)
		{
			foreach ($array as $k => $v)
			{
				if (is_array($v))
				{
					foreach ($v as $k2 => $v2)
					{
						if ($k2 == $on)
						{
							if($on == 'birthd' || $on == 'admissiondate' || $on == 'admission_date' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate'  || $on == 'beginvisit'  || $on == 'endvisit'  || $on =='dateofbirth' || $on == 'date' || $on == 'day'  || $on == 'assessment_completed_date'  || $on == 'visit_date' || $on == 'contact_form_date' || $on == 'first_sapv_active_day' || $on == 'patient_discharge_date' || $on == 'death_date' || $on == 'start' ) {

								if($on == 'birthdyears'){
									$v2 = substr($v2,0,10);
								}
								$sortable_array[$k] = strtotime($v2);

							} elseif($on == 'epid') {
								$sortable_array[$k] = preg_replace ('/[^\d\s]/', '', $v2) ;
							} elseif($on == 'percentage') {
								$sortable_array[$k] = preg_replace ('/[^\d\.]/', '', $v2) ;
							} else {
								$sortable_array[$k] = ucfirst($v2);
							}
						}
					}
				} else
				{
					if($on == 'birthd' || $on == 'admission_date' || $on == 'admissiondate' || $on == 'discharge_date'  || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate'   || $on == 'beginvisit'  || $on == 'endvisit' || $on =='dateofbirth'  || $on == 'date' || $on == 'day' || $on == 'assessment_completed_date'   || $on == 'visit_date'  || $on == 'contact_form_date'  || $on == 'first_sapv_active_day' || $on == 'patient_discharge_date' || $on == 'death_date') {
						if($on == 'birthdyears'){
							$v = substr($v,0,10);
						}
						$sortable_array[$k] = strtotime($v);
					} elseif($on == 'epid' || $on == 'percentage') {
						$sortable_array[$k] = preg_replace ('/[^\d\s]/', '', $v) ;
					} elseif($on == 'percentage') {
						$sortable_array[$k] = preg_replace ('/[^\d\.]/', '', $v2) ;
					} else {
						$sortable_array[$k] = ucfirst($v);
					}
				}
			}

			switch ($order)
			{
				case SORT_ASC:
//					asort($sortable_array);
					$sortable_array = Pms_CommonData::a_sort($sortable_array);
					break;
				case SORT_DESC:
//					arsort($sortable_array);
					$sortable_array = Pms_CommonData::ar_sort($sortable_array);
					break;
			}

			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}

		return $new_array;
	}
	
	

	private function clear_image_details()
	{
	    $_SESSION['file'] = '';
	    $_SESSION['filetype'] = '';
	    $_SESSION['filetitle'] = '';
	    $_SESSION['filename'] = '';
	
	    unset($_SESSION['file']);
	    unset($_SESSION['filetype']);
	    unset($_SESSION['filetitle']);
	    unset($_SESSION['filename']);
	}


	
	
	public function uploadfilesAction()
	{
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $this->clientid;
	
	    ini_set("upload_max_filesize", "10M");
	
	    if($clientid == '0')
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	    }
	    /*			 * ********Deletefile*********** */
	    if($_GET['delid'] > 0)
	    {
	        $upload_form = new Application_Form_ClientFileUpload();
	        $upload_form->deleteFile($_GET['delid']);
	    }
	
	    if($_GET['doc_id'] > 0)
	    {
	        $patient = Doctrine_Query::create()
	        ->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
							->from('CLientFileUpload')
							->where('id= ? ', $_GET['doc_id']);
	        $fl = $patient->execute();
	
	        if($fl)
	        {
	            $flarr = $fl->toArray();
	
	            $explo = explode("/", $flarr[0]['file_name']);
	
	            $fdname = $explo[0];
	            $flname = utf8_decode($explo[1]);
	        }
	
	        $con_id = Pms_FtpFileupload::ftpconnect();
	        if($con_id)
	        {
	            $upload = Pms_FtpFileupload::filedownload($con_id, 'clientuploads/' . $fdname . '.zip', 'clientuploads/' . $fdname . '.zip');
	            Pms_FtpFileupload::ftpconclose($con_id);
	        }
	
	        $cmd = "unzip -P " . $this->filepass . " clientuploads/" . $fdname . ".zip;";
	        exec($cmd);
	
	        $file = file_get_contents("clientuploads/" . $fdname . "/" . $flname);
	        ob_end_clean();
	        ob_start();
	        $expl = explode(".", $flname);
	
	
	        if($expl[count($expl) - 1] == 'doc' || $expl[count($expl) - 1] == 'docx' || $expl[count($expl) - 1] == 'xls' || $expl[count($expl) - 1] == 'xlsx')
	        {
	            header("location: " . APP_BASE . "clientuploads/" . $fdname . "/" . $flname);
	        }
	        else
	        {
	            header('Content-Description: File Transfer');
	            header('Content-Type: application/octet-stream');
	            header('Content-Disposition: attachment; filename="' . $flname . '"');
	            header('Content-Transfer-Encoding: binary');
	            header('Expires: 0');
	            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	            header('Pragma: public');
	            header('Content-Length: ' . filesize("clientuploads/" . $fdname . "/" . $flname));
	            ob_clean();
	            flush();
	
	            echo readfile("clientuploads/" . $fdname . "/" . $flname);
	        }
	        exit;
	    }
	
	    /*			 * ************************************ */
	    if($this->getRequest()->isPost())
	    {
	        $ftype = $_SESSION['filetype'];
	        if($ftype)
	        {
	            $filetypearr = explode("/", $ftype);
	            if($filetypearr[1] == "vnd.openxmlformats-officedocument.spreadsheetml.sheet")
	            {
	                $filetype = "XLSX";
	            }
	            elseif($filetypearr[1] == "vnd.openxmlformats-officedocument.wordprocessingml.document")
	            {
	                $filetype = "docx";
	            }
	            elseif($filetypearr[1] == "X-OCTET-STREAM")
	            {
	                $filetype = "PDF";
	            }
	            else
	            {
	                $filetype = $filetypearr[1];
	            }
	        }
	
	        $upload_form = new Application_Form_ClientFileUpload();
	
	        $a_post = $_POST;
	        $a_post['clientid'] = $clientid;
	        $a_post['filetype'] = $_SESSION['filetype'];
	
	        if($upload_form->validate($a_post))
	        {
	            $upload_form->insertData($a_post);
	        }
	        else
	        {
	            $upload_form->assignErrorMessages();
	            $this->retainValues($_POST);
	        }
	
	        //remove session stuff
	        $_SESSION['filename'] = '';
	        $_SESSION['filetype'] = '';
	        $_SESSION['filetitle'] = '';
	        unset($_SESSION['filename']);
	        unset($_SESSION['filetype']);
	        unset($_SESSION['filetitle']);
	    }
	
	    $files = new ClientFileUpload();
	    $filesData = $files->getClientFiles($this->clientid);
	
	    $this->view->filesData = $filesData;
	    $this->view->showInfo = $this->showinfo;
	
	    $allUsers = Pms_CommonData::getClientUsers($this->clientid);
	    foreach($allUsers as $keyu => $user)
	    {
	        $allUsersArray[$user['id']] = $user['last_name'] . ", " . $user['first_name'];
	    }
	    $this->view->allusers = $allUsersArray;
	}
	
	public function clientuploadifyAction()
	{
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	
	    $extension = explode(".", $_FILES['qqfile']['name']);
	
	    $_SESSION['filetype'] = $extension[count($extension) - 1];
	    $_SESSION['filetitle'] = $extension[0];
	    $timestamp_filename = time() . "_file." . $extension[count($extension) - 1];
	
// 	    $path = 'clientuploads';
	    $path = CLIENTUPLOADS_PATH;
// 	    $dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
// 	    while(!is_dir($path . '/' . $dir))
// 	    {
// 	        $dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
// 	        mkdir($path . '/' . $dir);
// 	        if($i >= 50)
// 	        {
// 	            exit; //failsafe
// 	        }
// 	        $i++;
// 	    }
	    $dir = Pms_CommonData::uniqfolder(CLIENTUPLOADS_PATH);
	    
	    $folderpath = $path . '/' . $dir;
	
	    $filename = $folderpath . "/" . trim($timestamp_filename);
	    
	    $_SESSION['filename'] = $dir . "/" . $timestamp_filename;
	    
	    $_SESSION['zipname'] = $folderpath . ".zip";
	    
	    $_SESSION['getcwd'] = getcwd();
	    
	    $_SESSION['full_upload_path'] = $filename; 
	    
	    @move_uploaded_file($_FILES['qqfile']['tmp_name'], $filename);
	    
	    
	    $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
	    if($connected_client){
	    	$clientid = $connected_client;
	    	$client_data = Pms_CommonData::getClientDataFp($clientid);
	    	$file_password = $client_data[0]['fileupoadpass'];
	    	
	    } else{
	    	$clientid = $this->clientid;
	    	$file_password = $this->filepass;
	    }
	    
	    $cmd = "zip -9 -j -P " . $file_password . " " . $_SESSION['zipname']. " " . $filename . "; rm -f " . $filename;
	    @exec($cmd);
	    //echo $cmd . "<br>";
	    
	    if ( $_REQUEST['is_revision'] == "1" && isset( $_REQUEST['member_id'] , $_REQUEST['parent_id'] ) ){
	    	//save to ftp as revision
	    	
	    	$post = array(
	    		//'title' => 
	    		'clientid' => $clientid,
	    		//'file_name' => 
	    		'filetype' => $_SESSION['filetype'],
	    		'parent_id' => (int)$_REQUEST['parent_id'],
	    		'tabname'=> 'voluntary_worker',
	    		'recordid' => (int)$_REQUEST['member_id']
	    	);
	    	$upload_form = new Application_Form_ClientFileUpload();
	    	$upload_form->InsertData($post);
	
	    	
	    }
	    echo json_encode(array(success => true));
	    exit;
	}
	
	

	public function vwfileAction()
	{
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $this->clientid;
	
	    $this->_helper->layout->setLayout('layout');
	    $this->_helper->viewRenderer->setNoRender();
	
// 	    if($_GET['delid'] > 0)
// 	    {
// 	        $upload_form = new Application_Form_ClientFileUpload();
// 	        $upload_form->deleteFile($_GET['delid']);
	
// 	        if($_REQUEST['id'])
// 	        {
// 	            $this->redirect(APP_BASE . 'voluntaryworkers/editvoluntaryworkerdet?id=' . $_REQUEST['id']);
// 	        }
// 	    }
	
	    if($_REQUEST['doc_id'] == 0 && $_REQUEST['is_image'] == 1){
	        
	        $fdoc = Doctrine::getTable('Voluntaryworkers')->find($_REQUEST['id']);
            $fdocarray = $fdoc->toArray();
            
            if(!empty($fdocarray)){
                $img_db_path =$fdocarray['img_path'];
 
                // $img_pre = '/ispc2015/public';
                $img_pre = '';
                //$img_path = $_SERVER['DOCUMENT_ROOT'] . $img_pre . "/icons_system/"; // change the path to fit your websites document structure
                $img_path = PUBLIC_PATH . $img_pre . "/icons_system/"; // change the path to fit your websites document structure
                $fullPath = $img_path . $img_db_path;
                
                if($fd = fopen($fullPath, "r"))
                {
                    $fsize = filesize($fullPath);
                    $path_parts = pathinfo($fullPath);
                    $ext = strtolower($path_parts["extension"]);
                    switch($ext)
                    {
                        case "pdf":
                            header('Content-Description: File Transfer');
                            header("Content-type: application/pdf"); // add here more headers for diff. extensions
                            header('Content-Transfer-Encoding: binary');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');
                            if($_COOKIE['mobile_ver'] != 'yes')
                            {
                                //if on mobile version don't send content-disposition to play nice with iPad
                                header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
                            }
                            break;
                        default:
                        header('Content-Description: File Transfer');
                        header("Content-type: application/octet-stream");
                        header('Content-Transfer-Encoding: binary');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Pragma: public');
                        if($_COOKIE['mobile_ver'] != 'yes')
                        {
                            //if on mobile version don't send content-disposition to play nice with iPad
                            header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
                        }
                    }
                    header("Content-length: $fsize");
                    header("Cache-control: private"); //use this to open files directly
                    readfile($fullPath);
                }
                fclose($fd);
                exit;
            }
	    }
	    else if($_REQUEST['doc_id'] > 0 && $_REQUEST['is_image'] == 0)
	    {
	        $patient = Doctrine_Query::create()
	        ->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
							->from('ClientFileUpload')
							->where('id= ?', $_REQUEST['doc_id'])
							->andWhere('tabname = ?', $_REQUEST['tab']);
	        $flarr = $patient->fetchArray();

	        if($flarr)
	        {
	            $explo = explode("/", $flarr[0]['file_name']);
	
	            $fdname = $explo[0];
	            $flname = utf8_decode($explo[1]);
	        }
// 	        echo $fdname;
// 	        echo $flname;
	        
// 	        print_r($flarr);
	        
	       
	
	        if(!empty($flarr))
	        {
	            // get associated clients of current clientid START
// 	            $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);

				// if no doc_id - in db
// 	            $con_id = Pms_FtpFileupload::ftpconnect();
	
// 	            if($con_id)
// 	            {
// 	                $upload = Pms_FtpFileupload::filedownload($con_id, 'clientuploads/' . $fdname . '.zip', 'clientuploads/' . $fdname . '.zip',false, $flarr[0]['clientid']);
// 	                Pms_FtpFileupload::ftpconclose($con_id);
// 	            }
	            
// 	            die('clientuploads/' . $fdname . '.zip');
	            
	            // get associated clients of current clientid 
	            $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
	             
	            if($connected_client && $flarr[0]['clientid'] != $this->clientid ){
	                
	                $client_data = Pms_CommonData::getClientDataFp($flarr[0]['clientid']);
	                $file_password = $client_data[0]['fileupoadpass'];
	                
	            } else{
    	            $file_password = $this->filepass;
	            }

// 	            $cmd = "unzip -P " . $file_password ." ". trim("clientuploads/" . $fdname.".zip") . " -d ".PUBLIC_PATH."/clientuploads;\n";
// 	            exec($cmd);
	            
	            $old = $_REQUEST['old'] ? true : false;
	            if (($path = Pms_CommonData::ftp_download('clientuploads/' . $fdname . '.zip' , $file_password , $old , $flarr[0]['clientid'] , $flarr[0]['file_name'], "ClientFileUpload", $flarr[0]['id'])) === false){
	            	//failed to download file
	            }
	            
	            $pre = '';
// 	            $pre = '/ispc2015/public';
	            //$path = $_SERVER['DOCUMENT_ROOT'] . $pre . "/clientuploads/" . $fdname . "/"; // change the path to fit your websites document structure
	            
// 	            $path = PUBLIC_PATH . $pre . "/clientuploads/"; // change the path to fit your websites document structure
// 	            $fullPath = $path . $flname;
	            $fullPath = $path . "/". $flname;
	            
	            if (!file_exists($fullPath)){
	            	$path = FTP_DOWNLOAD_PATH . "/" . $fdname . "/"; // change the path to fit your websites document structure
	            	$fullPath = $path . $flname;
	            }
	            if (!file_exists($fullPath)){
	            	$path = FTP_DOWNLOAD_PATH . "/" ; // change the path to fit your websites document structure
	            	$fullPath = $path . $flname;
	            }
	            
	
	            if($fd = fopen($fullPath, "r"))
	            {
	                $fsize = filesize($fullPath);
	                $path_parts = pathinfo($fullPath);
	
	                $ext = strtolower($path_parts["extension"]);
	                switch($ext)
	                {
	                    case "pdf":
	                        header('Content-Description: File Transfer');
	                        header("Content-type: application/pdf"); // add here more headers for diff. extensions
	                        header('Content-Transfer-Encoding: binary');
	                        header('Expires: 0');
	                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	                        header('Pragma: public');
	                        if($_COOKIE['mobile_ver'] != 'yes')
	                        {
	                            //if on mobile version don't send content-disposition to play nice with iPad
	                            header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
	                        }
	                        break;
	                    default:
	                    header('Content-Description: File Transfer');
	                    header("Content-type: application/octet-stream");
	                    header('Content-Transfer-Encoding: binary');
	                    header('Expires: 0');
	                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	                    header('Pragma: public');
	                    if($_COOKIE['mobile_ver'] != 'yes')
	                    {
	                        //if on mobile version don't send content-disposition to play nice with iPad
	                        header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
	                    }
	                }
	                header("Content-length: $fsize");
	                header("Cache-control: private"); //use this to open files directly
	                readfile($fullPath);
	                fclose($fd);
	                unlink($fullPath);
	            }
	            
	            exit;
	        }
	    }
	}
	
	public function savefilterAction(){
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    
	    $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->setLayout('layout_ajax');
	    if($this->getRequest()->isPost())
	    {
	        $data = json_decode($_POST['details']);
	    
	        if(!empty($data))
	        {
	            $user_filter = new Application_Form_UserVwFilters ();
	            $result = $user_filter->set_filter($this->userid, $this->clientid, $data);
	        }
	    }
	}
	
	
	
	

	private function export_xlsx($columns, $data)
	{
	    $this->xlsBOF();
	
	    $c = 1;
	    $this->xlsWriteLabel($line, 0, $this->view->translate('no'));
 
	    foreach($columns as $column_id =>$column_name)
	    {
            $this->xlsWriteLabel($line, $c, utf8_decode($this->view->translate($column_name)));
            $c++;
            $column_names[] = $column_name;
	    }
	    
	
	    $line++;
	
	    $xlsRow = $line;
	    foreach($data as $vw_id => $row)
	    {
	        $i++;
	        $this->xlsWriteNumber($xlsRow, 0, "$i");
	        $t = 1;
	        foreach($row as $field => $value)
	        {
	
// 	            if(in_array($field,$column_names)){

	            foreach($columns as $col_id =>$column_name)
	            {
	                $html.= '<td valign="top">' . $row[$column_name] . '</td>';
	                $value = $row[$column_name];
	                
	                $value = str_replace("<br />", "\n", $value);
	                $value = str_replace("<hr/>", "\n", $value);
	                
	                if(is_numeric($value))
	                { //if numeric format as number
	                    if($column_name == "first_name" || $column_name == "last_name")
	                    {
	                        //weird stuff going if first name/last name or memo is numeric = true(ISPC-1243)
	                        $this->xlsWriteLabel($xlsRow, $t, utf8_decode($value));
	                    }
	                    else
	                    {
	                        $this->xlsWriteNumber($xlsRow, $t, $value);
	                    }
	                }
	                else
	                {
	                    $this->xlsWriteLabel($xlsRow, $t, utf8_decode($value));
	                }
	                $t++;
	            }
// 	            }
	        }
	        $xlsRow++;
	    }
	
	    $this->xlsEOF();
	
	    $file = str_replace(" ", "_", $this->view->translate('Voluntaryworkers'));
	    $fileName = $file . ".xls";
	
	    header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Content-Type: application/force-download");
	    header("Content-Type: application/octet-stream");
	    header("Content-type: application/vnd.ms-excel; charset=utf-8");
	    header("Content-Disposition: attachment; filename=" . $fileName);
	    exit;
	}
	
	private function export_php_excel($columns, $data)
	{
	    $Tr = new Zend_View_Helper_Translate();
	
	    // Create new PHPExcel object
	    $excel = new PHPExcel();
	    	
	    $excel->getDefaultStyle()->getFont()
	    ->setSize(10);
	    /* ->setName('Verdana') */
	    /* ->setBold(true) */
	    	
	    $xls = $excel->getActiveSheet();
	    
	    $columns_nr = count($columns);
	    
	    foreach($columns as $cl_nr => $cl_name ){
            $phpexcel_columns[ PHPExcel_Cell::stringFromColumnIndex($cl_nr) ] = $cl_name;
	    }
	    $phpexcel_columns_flip = array_flip($phpexcel_columns); 

	    $colors_translation = array(
		    'Grün' => "008000",
			'Gelb' => "FFFF00",
			'Rot' => "ff0000",
			'Inaktiv' => "ffffff",
	    );
	             
        $line= 0;
        $line++;
        $xls->setCellValue("A" . $line, $this->view->translate('no'));

        foreach($phpexcel_columns as $k=>$column)
        {
            $xls->setCellValue($k. $line, $this->view->translate($column));
        }
        $line++;

        $row_nr = "1";
        	
        foreach($data as $key_date => $row)
        {
            $char_it = 65;
            	
            $xls->setCellValue(chr($char_it).$line, $row_nr);
            foreach($row as $col=>$value){
                 
                if( $col=="description" && (is_array($value) && sizeof($value) > 0))
                {
                    $value = implode(", ",$value);
                }
                 
                $value = str_replace("<br />", "\n", $value);
                //$xls->setCellValue(chr($char_it+1). $line,$value);
               /*
                if( $col=="status_color") {
                	$xls->getStyle($phpexcel_columns_flip[$col] . $line)->applyFromArray(
                		array(
                				'fill' => array(
                						'type' => PHPExcel_Style_Fill::FILL_SOLID,
                						'color' => array('rgb' => $colors_translation[$value])
                				)
                		)
                		);
                }
                */
                $xls->setCellValue( $phpexcel_columns_flip[$col] . $line, $value);
                
                $char_it++;
            }
            $line++;
            $row_nr++;
        }

        $file = str_replace(" ", "_", $this->view->translate("Ehrenamtliche"));
        $fileName = $file . ".xls";
        	
        	
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$fileName);
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save('php://output');
        exit;
        
        }
	
	
	
	public function export_csv($columns, $data)
	{
		$tab = chr(9); // \t
		$space = chr(32); // space
		$comma = chr(44); // ,
		$semicolon = chr(59); // ;
		$delimiter = $comma;
		
	    $file = fopen('php://output', 'w');
	    $filename = 'Ehrenamtliche.csv';
 
	    foreach ($columns as $column_id => $column_name) {
            $csv_header_data[] = $row[$k] = iconv("UTF-8", "Windows-1252", $this->view->translate($column_name)); ;
           
            foreach ($data as $vw => $row) {
            	$data_formated [$vw] [$column_name]  = $row[$column_name];
            }
	    }
	
	    fputcsv($file, $csv_header_data, $delimiter, '"');
	
	    foreach ($data_formated as $vw => $row) {
	        //$row = str_replace("<br />", "\n", $row);
	        
	        foreach($row as $k => $v){
	        	$v = str_replace($delimiter, $space, $v);
	        	$v = str_replace("<br />", "\n", $v);
	        	$row[$k] = iconv("UTF-8", "Windows-1252", $v);
	        }
	      
	        fputcsv($file, $row, $delimiter, '"');
	       	//fputcsv($file, $row);
	    }
	
	    header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Content-Type: application/force-download");
	    header("Content-dType: application/octet-stream");
	    header("Content-type: application/vnd.ms-excel; charset=utf-8");
	    header("Content-Disposition: attachment; filename=" . $filename);
	    exit();
	}
	
	private function export_html($columns, $data, $upcoming_birthdays = false)
	{
	    $html = "";
	    $html .='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr>';
	    $html .= '<th width="1%">' . $this->view->translate('no') . '</th>';
	    
	    foreach($columns as $column_id =>$column_name)
	    {
	            if($upcoming_birthdays && $column_name == "birthdate"){
                    $html .= '<th width="10%">' . $this->view->translate('birthdyears') . '</th>';
	            } else{
                    $html .= '<th width="10%">' . $this->view->translate($column_name) . '</th>';
	            }
            $columns_ids[] = $column_name;
	    }
	    
	    $html .= '</tr>';
	
	    $rowcount = 1;
	    foreach($data as $vw_id => $row)
	    {
	        $html .='<tr class="row"><td valign="top">' . $rowcount . '</td>';
	        
	        foreach($columns as $col_id =>$column_name)
	        {
	            $html.= '<td valign="top">' . $row[$column_name] . '</td>';
	         }
	        
	        /* foreach($row as $field => $value)
	        {
	            if(in_array($field,$columns_ids)){
	                $html.= '<td valign="top">' . $value . '</td>';
	            }
	        } 
	        */
	        $html .='</tr>';
	        $rowcount++;
	    }
	    $html.="</table>";
	
	    $output = "printing";
	
	    if($output == "screen")
	    {
	        $html = '<link href="' . APP_BASE . 'css/voluntaryworkers_export.css?'.date('Ymd', time()).'" rel="stylesheet" type="text/css" />' . $html;
	        echo $html;
	        exit;
	    }
	    elseif($output == "printing")
	    {
	        $html = '<link href="' . APP_BASE . 'css/voluntaryworkers_export.css?'.date('Ymd', time()).'" rel="stylesheet" type="text/css" />' . $html;
	
	        echo $html;
	        echo "<SCRIPT type='text/javascript'>";
	        echo "window.print();";
	        echo "</SCRIPT>";
	        exit;
	    }
	
	}
	
	private function xlsBOF()
	{
	    echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
	    return;
	}
	
	private function xlsEOF()
	{
	    echo pack("ss", 0x0A, 0x00);
	    return;
	}
	
	private function xlsWriteNumber($Row, $Col, $Value)
	{
	    echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
	    echo pack("d", $Value);
	    return;
	}
	
	private function xlsWriteLabel($Row, $Col, $Value)
	{
	    $L = strlen($Value);
	    echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
	    echo $Value;
	    return;
	}
	

	private function export_pdf($post, $pdfname, $filename)
	{
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $this->clientid;
	    $clientinfo = Pms_CommonData::getClientData($clientid);
	
	    $post['vws'] = $post;
	
	    $htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);
	    $pdf = new Pms_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
	
	
	    //defaults with header
	    $pdf->setDefaults(true);
	    $pdf->setImageScale(1.6);
	    //reset margins
	    $pdf->SetMargins(10, 5, 10);
	    $pdf->setPrintFooter(false);
	
	
	    if($pdfname == 'Avery105x48')
	    {
	        $bottom_margin = '3';
	        $pdf->setDefaults(true, 'P', $bottom_margin);
	        $pdf->SetMargins(0, 4.5, 0);
	        $pdf->SetFont('', '', 9);
	    }
	
	    if($pdfname == 'Avery70x35')
	    {
	        $bottom_margin = '5';
	        $pdf->setDefaults(true, 'P', $bottom_margin);
	        $pdf->SetMargins(0, 8.5, 0);
	        $pdf->SetFont('', '', 9);
	    }
	
	    switch($pdfname)
	    {
	
	        case 'Avery70x35':
	            $background_type = false;
	            break;
	        case 'Avery105x48':
	            $background_type = false;
	            break;
	        default:
	            $background_type = false;
	            break;
	    }
	
	    $pdf->HeaderText = false;
	
	    if($background_type != false)
	    {
	        $bg_image = Pms_CommonData::getPdfBackground($clientinfo[0]['id'], $background_type);
	        if($bg_image !== false)
	        {
	            $bg_image_path = PDFBG_PATH . '/' . $clientinfo[0]['id'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];
	            if(is_file($bg_image_path))
	            {
	                $pdf->setBackgroundImage($bg_image_path);
	            }
	        }
	    }
	
	    $html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
	    if($pdfname != 'anlage5' || $pdfname != 'Palliativ-Notfallbogen' || $pdfname != 'Stammblatt4' || $pdfname != 'therapyplan')
	    {
	        $html = preg_replace('/style=\"(.*)\"/i', '', $html);
	    }
	
	    $pdf->setHTML($html);
	
	
	    ob_end_clean();
	    ob_start();
	    $pdf->toBrowser($pdfname . '.pdf', "d");
	    exit;
	}
	

	public function sendemail2vwsAction()
	{
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	     
	    $this->view->email_tabs = $_POST['tabs']; //ISPC - 2114 - archive function for vw
	    
	    //upload_file_attachment from ajax and exit
	    if ($this->getRequest()->isXmlHttpRequest() && $_POST['action'] == "upload_file_attachment")
	    {
	    	 
	    	$fileupload_result = $this->upload_qq_file( array(
	    			"allowed_file_extensions" => array('pdf'),
	    			"max-filesize" => 5 * 1000 * 1024,
	    			"action" => "sendemail2vws",
	    	));
	    	 
	    	$this->_helper->json->sendJson($fileupload_result);
	    	exit;// sendJson exits by default; it has extra param $suppressExit if you just want to echo 
	    }
	    
	    
	    // ################################################
	    // get associated clients of current clientid START
	    // ###############################################
	    $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
	    if($connected_client){
	        $clientid = $connected_client;
	    } else{
	        $clientid = $this->clientid;
	    }
	    $userid = $this->userid;
	    
	    $usr = new User();
	    $usrdata = $usr->getUserDetails($userid);
	   
	    // ################################################
	    // get associated clients of current clientid END
	    // ###############################################
	    	  
	    
	    // get client vws data
	    $client_vw_array = Voluntaryworkers::getClientsVoluntaryworkers($clientid);
	    Voluntaryworkers::beautifyName($client_vw_array);
	    
	    $vworkers_array = array();
	    $no_email_vws_array = array();
	    
	    $emailValidator = new Zend_Validate_EmailAddress();
	    
	    foreach($client_vw_array as $cm => $mv){
	        $vworkers_array[$mv['id']] = $mv;
	        if(empty($mv['email'])  || strlen($mv['email']) == 0 || ! $emailValidator->isValid($mv['email']) ){
	            $no_email_vws_array[] = $mv['id'];
	        }
	    }
	    // get curent user details
// 	    $user_data_array = Pms_CommonData::getUserData($userid);
// 	    $user_data = $user_data_array[0];
	
// 	    if(strlen($user_data['emailid']) == 0 || empty($user_data['emailid'])){
// // 	        $this->_redirect(APP_BASE . "voluntaryworkers/workersdetailslist?flg=no_email");
// // 	        exit;
// 	    }
// 	    $email['sender'] = $user_data;		
	    if($this->getRequest()->isPost())
	    {
	        if($_POST['transfer2sendemail'] == "1" ){
	            if($_POST['vws']){
	                $vw_ids = $_POST['vws'];
	            }
	        } else {
	            $email_form = new Application_Form_VwEmailsLog();
	            $post = $_POST;
	
	            $vw_ids = explode(',',$post['email']['initial_vws']);
	           
	            $validation = $email_form->validate($post['email']);
	           
	            if($validation) {
	            	
	            	$attachments = $this->get_last_uploaded_file("sendemail2vws", $post['email']['attachment']);
	            	$post['email']['attachment'] = $attachments[$post['email']['attachment']];
	            	
	            	$post['email']['client_vw_array'] = $vworkers_array;
	           	
	                $email_form->save2email_log($post['email']);
	
	                //invalidate all attachments
	                $this->set_last_uploaded_file('sendemail2vws');
	                //ISPC - 2231 -p.6
	                $vw_course_type = 'K';
	                $tab_name = 'Kommentar';
	                $vw_course_title = $this->translate('vw - Email sent');
	                $done_date = date("Y-m-d H:i:s", time());
	                
	                foreach($vw_ids as $kvw=>$vvw)
	                {	                	
	                	$cust = new VoluntaryworkersCourse();
	                	$cust->vw_id = $vvw;
	                	$cust->course_date = date("Y-m-d H:i:s", time());
	                	$cust->course_type = $vw_course_type;
	                	$cust->course_title = $vw_course_title;
	                	// 				$cust->course_type = Pms_CommonData::aesEncrypt($post['vw_course_type'][$i]);
	                	// 				$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($post['vw_course_title'][$i]));
	                	$cust->user_id = $userid;
	                	$cust->done_date = $done_date;
	                	$cust->done_name = $tab_name;
	                	$cust->done_id = $userid;
	                	$cust->save();
	                	
	                	$insid = $cust->id;
	                }
	                
	                $this->_redirect(APP_BASE . "voluntaryworkers/workersdetailslist?flg=email_sent&isarchived=".$post['tabs']);
	                exit;
	
	            } else{
	            	//invalidate all attachments
	            	$this->set_last_uploaded_file('sendemail2vws');
	            	
	                $email_form->assignErrorMessages();
	                $this->retainValues($_POST);
	            }
	        }
	    }
	    
	    Voluntaryworkers::beautifyName($vworkers_array);
	    
	    foreach($vw_ids as $k => $vw_id){
	        if(!in_array($vw_id, $no_email_vws_array)){
	            $vw_data[$vw_id] = $vworkers_array[$vw_id];
	            $email['recipients']['data'][$vw_id] = $vworkers_array[$vw_id];
	            $email['recipients']['ids'][] = $vw_id;
	        } else {
	    		$email['no_email_vw_array'][$vw_id] = $vworkers_array[$vw_id];
	    	}
	    }
	    $email['client_vworkers'] = $vworkers_array;


	    $this->view->email_data = $email;

	    
	    $tokens_obj = new Pms_Tokens('VoluntaryworkersEmail');
	   	$email_tokens = $tokens_obj->getTokens4Viewer();
	   	
	   	$this->view->email_tokens = $email_tokens['prefixed_array_viewer'];
	   	$this->view->email_tabs = $_POST['tabs'];
	   
	}
	
	/* ######################################################################*/
	/* ######################################################################*/
	/* ########################   LETTER TEMPLATES  #########################*/
	/* ######################################################################*/
	/* ######################################################################*/
	public function listtemplatesAction()
	{
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $this->clientid;
// 	    $userid = $this->userid;
// 	    $clientid = $this->clientid;
	
	    if($_REQUEST['flg'])
	    {
	        if($_REQUEST['flg'] == 'err')
	        {
	            $this->view->error_mesage = $this->view->translate('error');
	        }
	        else if($_REQUEST['flg'] == 'inv')
	        {
	            $this->view->error_mesage = $this->view->translate('invalid_template');
	        }
	        else if($_REQUEST['flg'] == 'suc')
	        {
	            $this->view->success_message = $this->view->translate('success');
	        }
	        else if($_REQUEST['flg'] == 'del_suc')
	        {
	            $this->view->delete_message = $this->view->translate('deletedsuccessfully');
	        }
	    }
	}
	
	public function fetchtemplatelistAction()
	{
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $this->clientid;
// 	    $userid = $this->userid;
// 	    $clientid = $this->clientid;
	    $user_type = $this->usertype;
	
	    $this->view->default_recipient_values = Pms_CommonData::template_default_recipients();
	
	    $columnarray = array(
	        "crd" => "create_date",
	        "id" => "id",
	        "ti" => "title",
	        "ft" => "file_type"
	    );
	
	    $orderarray = array("ASC" => "DESC", "DESC" => "ASC");
	    $this->view->order = $orderarray[$_REQUEST['ord']];
	    $this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
	
	    $client_users_res = User::getUserByClientid($clientid, 0, true);
	
	    foreach($client_users_res as $k_user => $v_user)
	    {
	        $client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
	    }
	
	    $this->view->client_users = $client_users;
	
	    if($clientid > 0)
	    {
	        $where = ' and clientid=' . $clientid;
	    }
	    else
	    {
	        $where = ' and clientid=0';
	    }
	
	    if($user_type == "CA" || $user_type == "SA")
	    {
	        $this->view->reveal_actions_col = '1';
	    }
	    else
	    {
	        $this->view->reveal_actions_col = '0';
	    }
	
	    $fdoc = Doctrine_Query::create()
	    ->select('count(*)')
	    ->from('VwLetterTemplates')
	    ->where("isdeleted = 0 " . $where)
	    ->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
	
	    //used in pagination of search results
	    if(!empty($_REQUEST['val']))
	    {
	        $fdoc->andWhere("(title != '' OR file_type != '')");
	        $fdoc->andWhere("(title like ? OR file_type like ?)", array("%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%"));
	    }
	    $fdocarray = $fdoc->fetchArray();
	
	    $limit = 50;
	    $fdoc->select('*');
        $fdoc->where("isdeleted = 0 " . $where . "");
	    if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
	    {
	        $fdoc->andWhere("(title != '' or file_type != '')");
	        $fdoc->andWhere("(title like ? OR file_type like ?)", array("%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%"));
	    }
	    $fdoc->limit($limit);
	    $fdoc->offset($_REQUEST['pgno'] * $limit);
	
	    $fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
	
	
	    $this->view->{"style" . $_GET['pgno']} = "active";
	    if(count($fdoclimit) > '0')
	    {
	        $grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "vw_letter_templateslist.html");
	        $this->view->templates_grid = $grid->renderGrid();
	        $this->view->navigation = $grid->dotnavigation("vw_letter_templatesnavigation.html", 5, $_REQUEST['pgno'], $limit);
	    }
	    else
	    {
	        //no items found
	        $this->view->templates_grid = '<tr><td colspan="5" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
	        $this->view->navigation = '';
	    }
	
	    $response['msg'] = "Success";
	    $response['error'] = "";
	    $response['callBack'] = "callBack";
	    $response['callBackParameters'] = array();
	    $response['callBackParameters']['templateslist'] = $this->view->render('voluntaryworkers/fetchtemplatelist.html');
	
	    echo json_encode($response);
	    exit;
	}
	
	public function gettemplateAction()
	{
	    $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->setLayout('layout_ajax');
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $this->clientid;
	
// 	    $clientid = $this->clientid;
	
	    if($_REQUEST['tid'])
	    {
	        $template_id = trim(rtrim($_REQUEST['tid']));
	        $template_data = VwLetterTemplates::get_template($clientid, $template_id, '1');
            $file_check_path = 'vw_letter_templates/' . $template_data['0']['file_path'];
	
	        if($template_data && is_file($file_check_path))
	        {
	            $this->_redirect(APP_BASE . 'vw_letter_templates/' . $template_data['0']['file_path']);
	            exit;
	        }
	        else
	        {
	            $this->_redirect(APP_BASE . "error/nofile");
	        }
	    }
	}
	
	public function templateuploadAction()
	{
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $this->clientid;
// 	    $clientid = $this->clientid;

	    
	    $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->setLayout('layout_ajax');
	
	
	    if($_REQUEST['op'] == 'vwlettertemplate')
	    {
	        $this->resetuploadvars();
	    }
	
	    $extension = explode(".", $_FILES['qqfile']['name']);
	
	    if($_REQUEST['op'] == 'vwlettertemplate')
	    {
	        $timestamp_filename = time() . "_file";
	        $path = VW_LETTER_TEMPLATE_PATH;
	        $dir = $clientid;
	
	        //create first directory in /public
	        while(!is_dir($path))
	        {
	            mkdir($path);
	            chmod($path, "0755");
	            if($i >= 50)
	            {
	                exit; //failsafe
	            }
	            $i++;
	        }
	
	        //create second client directory in first dir /public/first_dir/clientid
	        while(!is_dir($path . '/' . $dir))
	        {
	            mkdir($path . '/' . $dir);
	            chmod($path, "0755");
	            if($i >= 50)
	            {
	                exit; //failsafe
	            }
	            $i++;
	        }
	    }
	
	
	    $folderpath = $dir;
	
	    $filename = $path . "/" . $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
	
	    //file name
	    $_SESSION['template_filename'] = trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
	
	    //file path
	    $_SESSION['template_filepath'] = $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
	
	    //file extension
	    $_SESSION['template_filetype'] = $extension[count($extension) - 1];
	
	    if(move_uploaded_file($_FILES['qqfile']['tmp_name'], $filename))
	    {
	        echo json_encode(array('success' => true));
	        exit;
	    }
	}
	
	public function addtemplateAction()
	{
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $this->clientid;
// 	    $userid = $this->userid;
// 	    $clientid = $this->clientid;
	
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	     
	     
	    $this->view->default_recipient_values = Pms_CommonData::template_default_recipients();
	
	    if($this->getRequest()->isPost())
	    {
	        $upload_form = new Application_Form_VwLetterTemplate();
	
	        $post = $_POST;
	        $post['template_filename'] = $_SESSION['template_filename'];
	        $post['template_filetype'] = $_SESSION['template_filetype'];
	        $post['template_filepath'] = $_SESSION['template_filepath'];
	
	        if($upload_form->validate($post))
	        {
	            $upload_form->insert_template_data($post);
	            $this->_redirect(APP_BASE . 'voluntaryworkers/listtemplates?flg=suc_add');
	        }
	        else
	        {
	            $upload_form->assignErrorMessages();
	            $this->retainValues($_POST);
	        }
	
	        $this->resetuploadvars();
	    }
	}
	
	public function edittemplateAction()
	{
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $this->clientid;
// 	    $userid = $this->userid;
// 	    $clientid = $this->clientid;
	    $upload_form = new Application_Form_VwLetterTemplate();
	     
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	     
	    $this->view->default_recipient_values = Pms_CommonData::template_default_recipients();
	
	    if($_REQUEST['tid'] > '0')
	    {
	        $template_id = trim(rtrim($_REQUEST['tid']));
	
	        if($this->getRequest()->isPost())
	        {
	            $post = $_POST;
	            $post['template_id'] = $template_id;
	
	            //used to cleanup in edit mode(file uploaded but check was deselected)
	            $post['template_filepath'] = $_SESSION['template_filepath'];
	            $post['template_filetype'] = $_SESSION['template_filetype'];
	
	            //reset upload vars(if any) if change template is not checked
	            if($post['change_file'] != '1')
	            {
	                $this->resetuploadvars();
	            }
	
	            if($upload_form->validate($post))
	            {
	                $upload_form->update_template_data($clientid, $post);
	                $this->_redirect(APP_BASE . 'voluntaryworkers/listtemplates?flg=suc_edt');
	                exit;
	            }
	            else
	            {
	                $this->retainValues($post);
	            }
	            $this->resetuploadvars();
	        }
	
	
	        //load data
	        $template_data = VwLetterTemplates::get_template($clientid, $template_id);
	        if($template_data)
	        {
	            $this->retainValues($template_data[0]);
	        }
	        else
	        {
	            $this->redirect(APP_BASE . 'voluntaryworkers/listtemplates?flg=inv');
	            exit;
	        }
	    }
	    else
	    {
	        $this->redirect(APP_BASE . 'voluntaryworkers/listtemplates?flg=inv');
	        exit;
	    }
	}
	
	public function deletetemplateAction()
	{
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $this->clientid;
	    
// 	    $userid = $this->userid;
// 	    $clientid = $this->clientid;
	
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	     
	    $this->_helper->viewRenderer->setNoRender();
	
	    $fdoc = Doctrine::getTable('VwLetterTemplates')->findOneByIdAndClientid($_REQUEST['tid'], $clientid);
	    if($fdoc)
	    {
	        $fdoc->isdeleted = 1;
	        $fdoc->save();
	
	        $this->redirect(APP_BASE . 'voluntaryworkers/listtemplates?flg=del_suc');
	        exit;
	    }
	    else
	    {
	        $this->redirect(APP_BASE . 'voluntaryworkers/listtemplates?flg=del_err');
	        exit;
	    }
	}
	
	
	
	
	// actual function which is generating blank letter
	// changed from private to Public ISPC-2609 Ancuta 15.09.2020
	public function export_letters($export_data)
	{
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = isset($export_data['clientid']) && !empty($export_data['clientid']) ? $export_data['clientid'] :$this->clientid;
	    $userid = isset($export_data['userid']) && !empty($export_data['userid']) ? $export_data['userid'] :$this->userid;
	    
	    //spl_autoload_register(array('AutoLoader', 'autoloadPdf')); // Alex+Ancuta- commented on 18.11.2019- New phpdocx 9.5 added
// 	    $clientid = $this->clientid;
	
	
	    if(strlen($export_data['sortby'])){
	        $sortby = $export_data['sortby'];
	    } else{
	        $sortby = "first_name";
	    }
	    if(strlen($export_data['sortdir'])){
	        $sortdir = $export_data['sortdir'];
	    } else{
	        $sortdir = "ASC";
	    }
// 	    $vws_data = Member::get_all_members_details($export_data['members_ids'],$sortby,$sortdir);
	    // get selected vws details
	    $Voluntaryworkers_model =  new Voluntaryworkers();
	    $vws_data = $Voluntaryworkers_model->get_vws_multiple_details($export_data['vws'],$sortby,$sortdir,false,$clientid);
	 
	     
	    if (count($vws_data) > '0') {
	        // batch temp folder
	        if(!is_dir(PDFDOCX_PATH))
	        {
	            while(!is_dir(PDFDOCX_PATH))
	            {
	                mkdir(PDFDOCX_PATH);
	                if($i >= 50)
	                {
	                    exit; //failsafe
	                }
	                $i++;
	            }
	        }
	
	        if(!is_dir(PDFDOCX_PATH . '/' . $clientid))
	        {
	            while(!is_dir(PDFDOCX_PATH . '/' . $clientid))
	            {
	                mkdir(PDFDOCX_PATH . '/' . $clientid);
	                if($i >= 50)
	                {
	                    exit; //failsafe
	                }
	                $i++;
	            }
	        }
	
	        $path = PDFDOCX_PATH . '/' . $clientid;
	        $i = 0;
	        $dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
	        while(!is_dir($path . '/' . $dir))
	        {
	            $dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
	            mkdir($path . '/' . $dir);
	            if($i >= 50)
	            {
	                exit; //failsafe
	            }
	            $i++;
	        }
	        $batch_temp_folder = $dir;
	
	        // load template data
	        if($export_data['template_id'] != "0"){
	            $template_data = VwLetterTemplates::get_template($clientid,$export_data['template_id']);
	        } else{
	            $template_data = VwLetterTemplates::get_letter_template($clientid);
	        }
	   
	        foreach ($vws_data as $vw_id => $vw_data) {
	            // setup tokens
	            // user token
	            $tokens_multi[$vw_id]['voluntaryworker_firstname'] = $vw_data['first_name'];
	            $tokens_multi[$vw_id]['voluntaryworker_surname'] = $vw_data['last_name'];
	            //TODO-3771 Ancuta 20.01.2021
	            //$tokens_multi[$vw_id]['voluntaryworker_geb_datum'] = $vw_data['birthd'];
	            $tokens_multi[$vw_id]['voluntaryworker_geb_datum'] = $vw_data['birthdate'];
	            // --
	            $tokens_multi[$vw_id]['voluntaryworker_street'] = trim(rtrim($vw_data['street']));
	            $tokens_multi[$vw_id]['voluntaryworker_zip']= trim(rtrim($vw_data['zip']));
	            $tokens_multi[$vw_id]['voluntaryworker_city'] = trim(rtrim($vw_data['city']));
	            $tokens_multi[$vw_id]['voluntaryworker_id'] = $vw_id;
	
	            $recipient = array();
	            $recipient_name = trim(rtrim($vw_data['first_name'])) . ' ' . trim(rtrim($vw_data['last_name']));
	            $recipient_street = trim(rtrim($vw_data['street']));
	            $recipient_zip = trim(rtrim($vw_data['zip']));
	            $recipient_city = trim(rtrim($vw_data['city']));
	
	            if ($recipient_name) {
	                $recipient[] = $recipient_name;
	            }
	
	            if ($recipient_street) {
	                $recipient[] = $recipient_street;
	            }
	
	            if ($recipient_zip || $recipient_city) {
	                $recipient_blocks = array();
	
	                if ($recipient_zip) {
	                    $recipient_blocks[] = $recipient_zip;
	                }
	
	                if ($recipient_city) {
	                    $recipient_blocks[] = $recipient_city;
	                }
	
	                $recipient[] = implode(" ", $recipient_blocks);
	            }
	
	            // benutzer_adresse - never changes
	            $tokens_multi[$vw_id]['voluntaryworker_recipient_block'] = implode("<br />", $recipient);
	
	            $tokens_multi[$vw_id]['address'] = "";
	
	
	            if ($template_data) {
	                $temp_files[$vw_id] = $this->generate_file($clientid,$userid,$template_data[0], $tokens_multi[$vw_id], 'docx', $batch_temp_folder, 'generate');
	            }
	        }   
	
	        if(count($temp_files) > '0')
	        {
	            //final cleanup (check if files are on disk)
	            foreach($temp_files as $k_temp => $v_file)
	            {
	                if(!is_file($v_file))
	                {
	                    //remove unexisting files
	                    //							$unsetted_files[] = $v_file; //for debugs
	                    unset($temp_files[$k_temp]);
	                }
	                else{
	                	//ispc 1739 p.16
	                	//save individual file
	                	$file_showname = $template_data[0]['title'] ;
	                	$this->save_member_file($clientid,$v_file, $k_temp, $file_showname, $template_data[0]['id'] ,0 , 'docx');	
	                }
	                
	            }
	
	            $remaining_temp_files = array_values(array_unique($temp_files));
	
	            if(count($remaining_temp_files) > '0')
	            {
	            	//ISPC - 2231 -p.6
	            	$vw_course_type = 'K';
	            	$tab_name = 'Kommentar';
	            	$vw_course_title = $this->translate('vw- Letter created');
	            	$done_date = date('Y-m-d H:i:s', time());
	            	 
	            	$usr = new User();
	            	$usrdata = $usr->getUserDetails($userid);
	            	
	            	foreach ($vws_data as $vw_id => $vw_data)
	            	{
	            		$cust = new VoluntaryworkersCourse();
	            		$cust->vw_id = $vw_id;
	            		$cust->course_date = date("Y-m-d H:i:s", time());
	            		$cust->course_type = $vw_course_type;
	            		$cust->course_title = $vw_course_title;
	            		// 				$cust->course_type = Pms_CommonData::aesEncrypt($post['vw_course_type'][$i]);
	            		// 				$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($post['vw_course_title'][$i]));
	            		$cust->user_id = $userid;
	            		$cust->done_date = $done_date;
	            		$cust->done_name = $tab_name;
	            		$cust->done_id = $userid;
	            		$cust->save();
	            	
	            		$insid = $cust->id;
	            	}
	            	
	            	
	            	if($this->user_print_jobs && $export_data['print_job'] == 1){
	            	    
	            	    $final_file_name = "";
	            	    $final_file_name = $this->generate_file($clientid,$userid,$template_data[0], null, 'pdf', $export_data['batch_temp_folder'], 'merge', $remaining_temp_files,$export_data['vw_id']);
	            	    
	            	    return $final_file_name;
	            	    
	            	    
	            	} else{
    	            	$final_file = $this->generate_file($clientid,$userid,$template_data[0], false, 'pdf', $batch_temp_folder, 'merge', $remaining_temp_files);
	            	}
	            }
	        }
	       
	        //ISPC - 2231 -p.6
	      	 /*if($final_file) 
	       	 {
		       $vw_course_type = 'K';
		       $tab_name = 'Kommentar';
		       $vw_course_title = 'Letter sent';
		       $done_date = date('Y-m-d H:i:s', time());
		       
		       $usr = new User();
		       $usrdata = $usr->getUserDetails($userid);
		      
		       foreach ($vws_data as $vw_id => $vw_data)
		       {	
	        		$cust = new VoluntaryworkersCourse();
	        		$cust->vw_id = $vw_id;
	        		$cust->course_date = date("Y-m-d H:i:s", time());
	        		$cust->course_type = $vw_course_type;
	        		$cust->course_title = $vw_course_title;
	        		// 				$cust->course_type = Pms_CommonData::aesEncrypt($post['vw_course_type'][$i]);
	        		// 				$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($post['vw_course_title'][$i]));
	        		$cust->user_id = $userid;
	        		$cust->done_date = $done_date;
	        		$cust->done_name = $tab_name;
	        		$cust->done_id = $userid;
	        		$cust->save();
	        	
	        		$insid = $cust->id;
		        }
	        }*/
	        if($this->user_print_jobs && $export_data['print_job_id'] == 1){
	            
	        } else{
    	        exit();
	        }
	    }
	}
	
	private function save_member_file($clientid = 0 ,$file , $member_id,  $file_showname , $template_id = 0, $parent_id=0 , $file_type = 0){
		 
	    $clientid =isset($clientid) && !empty($clientid) ?  $clientid : $this->clientid;
		$file_realname =  pathinfo($file);
	
		/*
		 * //this path is used by ftp_connect
		 * production MUST be this
		 * $path = 'clientuploads';
		 */	     
		$path = 'clientuploads';
		
		$ftp_path = $this->system_file_upload($clientid, $file , false,  $path);
		
		$folder = pathinfo($ftp_path);

		$cust = new ClientFileUpload();
		$cust->title = Pms_CommonData::aesEncrypt($file_showname);
		$cust->clientid = $clientid;
// 		$cust->file_name = Pms_CommonData::aesEncrypt($folder['filename'] . "/" . $file_realname['basename']); //$post['fileinfo']['filename']['name'];
		$cust->file_name = Pms_CommonData::aesEncrypt($ftp_path); //$post['fileinfo']['filename']['name'];
		$cust->file_type = Pms_CommonData::aesEncrypt($file_type);
		$cust->parent_id = $parent_id;
		$cust->tabname= 'voluntary_worker';
		$cust->recordid = $member_id;
		$cust->save();
		return $cust;
		
		
		
		//nnnnnnnnnnn
		 //print_r($ftp_path);
		 //print_r($file_realname);die('sss');
		/*
		$query = new MemberFiles();
		$query->clientid = $clientid;
		$query->member_id = (int)$member_id;
		$query->file_showname = $file_showname;//Pms_CommonData::normalizeString($file_showname);
		$query->file_realname = $file_realname['basename'];
		$query->file_type = $file_type;
		$query->ftp_path = $ftp_path;
		$query->template_id = (int)$template_id;
		$query->isdeleted = "0";
		$query->parent_id = (int)$parent_id;
		 
		$query->save();
		if ($parent_id==0){
			//this is the original file
			$query->parent_id = $query->id;
			$query->revision = "1";
			$query->save();
		}
		//$query->getLast();
		
		 */
	}
	
	
	/* PHPDOCX WORD AND PDF START */
	//		$batch_printing_mode (false, generate, merge)
	//		false (no batch)
	//		generate (generates only temp docx file)
	//		merge (does a merge of all files in a directory)
	
	private function generate_file($clientid = 0,$userid = 0, $template_data = false, $vars = false, $export_file_type = 'docx', $batch_temp_folder = false, $batch_printing_mode = false, $batch_temp_files = false,$export_vw_id = false)
	{
	    //$logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = isset($clientid) && !empty($clientid) ? $clientid : $this->clientid;
	    $userid = isset($userid) && !empty($userid) ? $userid : $this->userid;
// 	    $clientid = $this->clientid;
// 	    $userid = $this->userid;
	
	    ob_end_clean();
	
	    if($template_data && file_exists(VW_LETTER_TEMPLATE_PATH . '/' . $template_data['file_path']))
	    {
	        $template_path = VW_LETTER_TEMPLATE_PATH . '/' . $template_data['file_path'];
	        $docx = new CreateDocxFromTemplate($template_path);
	
	        if($vars)
	        {
	
	            $client_details_vars = BriefTemplates::get_client_details($clientid);
	            $client_details_vars = (!empty($client_details_vars) ? $client_details_vars : array());
	
	            $user_details_vars = BriefTemplates::get_user_details($userid);
	            $user_details_vars = (!empty($user_details_vars) ? $user_details_vars : array());
	
	            $vars = array_merge($vars, $client_details_vars, $user_details_vars);
	
	            //CUSTOM VARS
	            $vars['aktuelles_datum'] = date('d.m.Y', time());
	
	            $html_tokens = array('voluntaryworker_recipient_block');
	
	            foreach($html_tokens as $k_html => $token_html)
	            {

	                //unset the html variable from tokens $vars to avoid errors
	                if(strlen(trim(rtrim($vars[$token_html]))) > '0')
	                {
	                    //set html options
	                    $html_options = array('isFile' => false, 'parseDivs' => false, 'downloadImages' => false, "strictWordStyles" => false);
	
	                    //cleanup token html entities
	                    $html = html_entity_decode($vars[$token_html], ENT_COMPAT, 'UTF-8');
	
	
	                    if($token_html == 'address' || $token_html == 'footer' || $token_html == "recipient" || $token_html == "voluntaryworker_recipient_block" || $token_html == "comment")
	                    {
// 	                        $type = "inline";
	                        $type = "block";
	
// 	                        //get token fonts only for inline tokens
// 	                        $docx_tmp = new CreateDocxFromTemplate($template_path);
// 	                        $docx_tmp->replaceVariableByHTML($token_html, $type, $html, $html_options);
// 	                        //$token_fonts = $docx_tmp->getTokenFont();
// 	                        $token_fonts = array();
	
// 	                        //convert inline_html_tokens to string_tokens
// 	                        $new_tokens[] = $token_html;
	
	                        $vars[$token_html . '_text'] = strip_tags(html_entity_decode($vars[$token_html], ENT_COMPAT, 'UTF-8'), "<br>");
	                        $vars[$token_html . '_text'] = str_replace(array('<br/>', '<br />', '<br>'), '\n\r', $vars[$token_html . '_text']);
	                        
	                        if ($res = Pms_DocUtil::process_html_token($docx, $token_html, $html)) {
	                        	$html = $res; 
	                        }
	                        
	                    }
	                    else
	                    {
	                        $type = "block";
	                    }
	
	                    //set each token font
// 	                    if($type == "inline" && count($token_fonts[$token_html]) > '0')
// 	                    {
// 	                        $css_style = array();
// 	                        if(strlen($token_fonts[$token_html]['font']['name']) > '0')
// 	                        {
// 	                            $css_style[] = 'font-family:' . $token_fonts[$token_html]['font']['name'];
// 	                        }
	
// 	                        if(strlen($token_fonts[$token_html]['font']['size']) > '0')
// 	                        {
// 	                            $css_style[] = 'font-size:' . $token_fonts[$token_html]['font']['size'] . 'pt';
// 	                        }
	
// 	                        if(strlen($token_fonts[$token_html]['font']['color']) > '0')
// 	                        {
// 	                            $css_style[] = 'color:#' . $token_fonts[$token_html]['font']['color'];
// 	                        }
	
// 	                        if($token_fonts[$token_html]['font']['isbold'] == '1')
// 	                        {
// 	                            $css_style[] = 'font-weight:bold';
// 	                        }
	
// 	                        if($token_fonts[$token_html]['font']['isitalic'] == '1')
// 	                        {
// 	                            $css_style[] = 'font-style:italic';
// 	                        }
	
// 	                        if($token_fonts[$token_html]['font']['isunderline'] == "1")
// 	                        {
// 	                            $css_style[] = 'text-decoration:underline';
// 	                        }
	
// 	                        //dummy css control
// 	                        if(!empty($css_style))
// 	                        {
// 	                            $css_style[] = '';
// 	                        }
	
// 	                        $html = html_entity_decode('<p style="' . implode(';', $css_style) . '">' . strip_tags($vars[$token_html], '<br>') . '</p>', ENT_COMPAT, 'UTF-8');
// 	                    }
	

						//force change utf-8 in html entities, because on one server it did not return corectly utf-8
						// TODO-1455(22.03.2018)
						$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
							


	                    $docx->replaceVariableByHTML($token_html, $type, $html, $html_options);
	                    unset($vars[$token_html]);
	                }
	                else
	                {
	                    $vars[$token_html] = '';
	                    $vars[$token_html . '_text'] = '';
	                }
	            }
	
	            //parse header
	            $docx->replaceVariableByText($vars, array('parseLineBreaks' => true, 'target' => 'header'));
	
	            //parse body
	            $options = array('parseLineBreaks' => true);
	            $docx->replaceVariableByText($vars, $options);
	
	            //parse footer
	            $docx->replaceVariableByText($vars, array('parseLineBreaks' => true, 'target' => 'footer'));
	        }
	
	        if(!is_dir(PDFDOCX_PATH))
	        {
	            while(!is_dir(PDFDOCX_PATH))
	            {
	                mkdir(PDFDOCX_PATH);
	                if($i >= 50)
	                {
	                    exit; //failsafe
	                }
	                $i++;
	            }
	        }
	
	        if(!is_dir(PDFDOCX_PATH . '/' . $clientid))
	        {
	            while(!is_dir(PDFDOCX_PATH . '/' . $clientid))
	            {
	                mkdir(PDFDOCX_PATH . '/' . $clientid);
	                if($i >= 50)
	                {
	                    exit; //failsafe
	                }
	                $i++;
	            }
	        }
	
	        if(isset($export_vw_id) && !empty($export_vw_id )){
	            
	            $suffix = $export_vw_id ;
	        } else {
    	        if($vars['voluntaryworker_id']){
    	            $suffix = $vars['voluntaryworker_id'];
    	        } else{
    	            $suffix = "";
    	        }
	        }
	
	        $filename = PDFDOCX_PATH . '/' . $clientid . '/voluntaryworker_letter' . $suffix;
	 
	        //rewrite $filename on batch job in another location
	        //check and create temp folder used in batch
	        if($batch_printing_mode && ($batch_printing_mode == 'merge' || $batch_printing_mode == 'merge_pdfs'))
	        {
	            if(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
	            {
	                while(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
	                {
	                    mkdir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder);
	                    if($i >= 50)
	                    {
	                        exit; //failsafe
	                    }
	                    $i++;
	                }
	            }
	
	            $filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/voluntaryworker_letter' . $suffix;
	            $merged_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/merged_voluntaryworker_letters' . $suffix . '.docx';
	            $merged_other_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/merged_voluntaryworker_letters_' . $suffix . '.' . $export_file_type;
	        }
	        else if($batch_printing_mode && $batch_printing_mode == 'merge_pdfs_multiple')
	        {
	            if(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
	            {
	                while(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
	                {
	                    mkdir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder);
	                    if($i >= 50)
	                    {
	                        exit; //failsafe
	                    }
	                    $i++;
	                }
	            }
	
	            $filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/voluntaryworker_letter' . $suffix;
	            $merged_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/merged_voluntaryworker_letters' . $suffix . '.docx';
	            $merged_other_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/final_merged_voluntaryworker_letters_' . $suffix . '.' . $export_file_type;
	        }
	        else if($batch_printing_mode == 'generate_pdf')
	        {
	            if(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
	            {
	                while(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
	                {
	                    mkdir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder);
	                    if($i >= 50)
	                    {
	                        exit; //failsafe
	                    }
	                    $i++;
	                }
	            }
	            $filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/voluntaryworker_letter' . $suffix;
	            $other_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/voluntaryworker_lettero_' . $suffix . '.' . $export_file_type;
	        }
	        else if($batch_printing_mode && $batch_printing_mode == 'print_job_merge_pdfs_multiple')
	        {
	            
	            if(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
	            {
	                while(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
	                {
	                    mkdir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder);
	                    if($i >= 50)
	                    {
	                        exit; //failsafe
	                    }
	                    $i++;
	                }
	            }
	            
	            $filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/voluntaryworker_letter' . $suffix;
	            $merged_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/merged_voluntaryworker_letters' . $suffix . '.docx';
	            $merged_other_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/final_merged_voluntaryworker_letters_' . $suffix . '.' . $export_file_type;
	        }
 
	        
	        //		rewrite file extension
	        if($_REQUEST['type'] == "pdf")
	        {
	            $export_file_type = $_REQUEST['type'];
	        }
	
	        //batch printing methods
	        //batch printing only docx(in a temp file) and then merge all in one file docx and then pdf
	        if($batch_printing_mode && $batch_printing_mode == 'generate')
	        {
	            
	            
	            
	            
	            //make sure export file type is set to docx
	            if($export_file_type == 'docx')
	            {
	                $docx->createDocx($filename);
	                return $filename . '.' . $export_file_type;
	            }
	        }
	        else if($batch_printing_mode && $batch_printing_mode == 'generate_pdf')
	        {
	            //create pdf but dont download it
	            $docx->createDocx($filename);
	            //$docx->enableCompatibilityMode();
	            $docx->transformDocument($filename . '.docx', $other_filename);
	            
	            return $other_filename;
	        }
	        else if($batch_printing_mode && $batch_printing_mode == 'merge')
	        {
	            //merge all files existing in $batch_temp_files!
	            $merge = new MultiMerge();
	            $merge_options = array(
	                'mergeType' => '0',
	                'numbering' => 'continue',
	                'enforceSectionPageBreak' => true
	            );
	
	            $first_shit = $batch_temp_files[0];
	            unset($batch_temp_files[0]);
	            $merge_process = $merge->mergeDocx($first_shit, $batch_temp_files, $merged_filename, $merge_options);
	
	            //unlink all files
	            array_map('unlink', $batch_temp_files);
	            @unlink($first_shit);

	            if(file_exists($merged_filename))
	            {

	            	$docx = new CreateDocxFromTemplate($merged_filename);
	                //$docx->enableCompatibilityMode();
	                $docx->transformDocument($merged_filename, $merged_other_filename);
	                	
	                $this->system_file_upload($clientid, $merged_other_filename , false , false , $foster_file = true);
	                	                if(isset($export_vw_id) && !empty($export_vw_id )){
	                    
	                    //unlink($merged_filename);
	                    //unlink($batch_temp_files);
	                    
	                    return $merged_other_filename;
	                } 
	                else
	                {
    	                //stop unlinking files
    	                //						unlink($merged_filename);
    	                ob_end_clean();
    	                header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    	                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    	                header("Cache-Control: no-store, no-cache, must-revalidate");
    	                header("Cache-Control: post-check=0, pre-check=0", false);
    	                header("Pragma: no-cache");
    	
    	                switch($export_file_type)
    	                {
    	                    case 'pdf':
    	                        header('Content-type: application/pdf');
    	                        break;
    	                    case 'doc':
    	                        header('Content-type: application/vnd.ms-word');
    	                        break;
    	                    case 'rtf':
    	                        header("Content-type: application/rtf");
    	                        break;
    	                    case 'odt':
    	                        header('Content-type: application/vnd.oasis.opendocument.text');
    	                        break;
    	                    default:
    	                        exit;
    	                        break;
    	                }
    	                header('Content-Disposition: attachment; Filename="merged_voluntaryworker_letters' . $suffix . '.' . $export_file_type . '"');
    	    
    	                readfile($merged_other_filename);
    	                @unlink($merged_other_filename);
    	                @unlink($merged_filename);
    	                exit;
    	               }
	            }
	        }
	        else if($batch_printing_mode && $batch_printing_mode == 'merge_pdfs_multiple')
	        {
	            $merge = new MultiMerge();
	            $merge_process = $merge->mergePdf($batch_temp_files, $merged_other_filename);
	
	            return $merged_other_filename;
	        }
	        else if($batch_printing_mode && $batch_printing_mode == 'print_job_merge_pdfs_multiple')
	        {
	            $merge = new MultiMerge();
	            $merge_process = $merge->mergePdf($batch_temp_files, $merged_other_filename);
	
	            return $merged_other_filename;
	        }
	        else if($batch_printing_mode && $batch_printing_mode == 'merge_pdfs')
	        {
	            //merge all files existing in $batch_temp_files!
	            $merge = new MultiMerge();
	            $merge_process = $merge->mergePdf($batch_temp_files, $merged_other_filename);
	
	            //unlink all files
	            foreach ($batch_temp_files as $k=>$v) {
	            	@unlink($v);
	            }
	            
	            if(file_exists($merged_other_filename))
	            {
	                $this->system_file_upload($clientid, $merged_other_filename, false , false , $foster_file = true);
	                //stop unlinking files
	                //						unlink($merged_filename);
	                ob_end_clean();
	                header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	                header("Cache-Control: no-store, no-cache, must-revalidate");
	                header("Cache-Control: post-check=0, pre-check=0", false);
	                header("Pragma: no-cache");
	
	                switch($export_file_type)
	                {
	                    case 'pdf':
	                        header('Content-type: application/pdf');
	                        break;
	                    case 'doc':
	                        header('Content-type: application/vnd.ms-word');
	                        break;
	                    case 'rtf':
	                        header("Content-type: application/rtf");
	                        break;
	                    case 'odt':
	                        header('Content-type: application/vnd.oasis.opendocument.text');
	                        break;
	                    default:
	                        exit;
	                        break;
	                }
	                header('Content-Disposition: attachment; Filename="merged_voluntaryworker_letters' . $suffix . '.' . $export_file_type . '"');
	                readfile($merged_other_filename);
	                @unlink($merged_other_filename);
	                exit;
	            }
	            exit;
	        }
	        else if($export_file_type == 'docx')
	        {
	            $docx->createDocxAndDownload($filename);
	            //					unlink($filename . '.docx');
	            exit;
	        }
	        else
	        {
	            $docx->createDocx($filename);
	            $other_filename = PDFDOCX_PATH . '/' . $clientid . '/voluntaryworker_letters_final_' . $suffix . '.' . $export_file_type;
	
	            //$docx->enableCompatibilityMode();
	            $docx->transformDocument($filename . '.docx', $other_filename);

	            $this->system_file_upload($clientid, $other_filename, $template_data['title'], false ,  $foster_file = true);
	             
	            //					unlink($filename . '.docx');
	
	            header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	            header("Cache-Control: no-store, no-cache, must-revalidate");
	            header("Cache-Control: post-check=0, pre-check=0", false);
	            header("Pragma: no-cache");
	
	            switch($export_file_type)
	            {
	                case 'pdf':
	                    header('Content-type: application/pdf');
	                    break;
	                case 'doc':
	                    header('Content-type: application/vnd.ms-word');
	                    break;
	                case 'rtf':
	                    header("Content-type: application/rtf");
	                    break;
	                case 'odt':
	                    header('Content-type: application/vnd.oasis.opendocument.text');
	                    break;
	                default:
	                    exit;
	                    break;
	            }
	            header('Content-Disposition: attachment; Filename="voluntaryworker_letters_final_' . $suffix . '.' . $export_file_type . '"');
	            readfile($other_filename);
	            @unlink($other_filename);
	            @unlink($filename . '.docx');
	            exit;
	        }
	    }
	    else
	    {
	        return false;
	    }
	}
	
	
	private function system_file_upload($clientid, $source_path = false, $file_title = false , $test_path = false , $foster_file = false )
	{
	    if($source_path)
	    {
	    	
	    	if ($foster_file == true) {
	    		$legacy_path = strtolower(__CLASS__);
	    		$tmpstmp = Pms_CommonData::uniqfolder(PDF_PATH);
	    		$destination_path = PDF_PATH;
	    		
	    	} else {
	    		
	    		if($test_path == 'clientuploads'){
	    			$legacy_path = "clientuploads";
	    			$tmpstmp = Pms_CommonData::uniqfolder(CLIENTUPLOADS_PATH);
	    			$destination_path = CLIENTUPLOADS_PATH;
	    		}else{
	    			$legacy_path = "uploads";
	    			$tmpstmp = Pms_CommonData::uniqfolder(PDF_PATH);
	    			$destination_path = PDF_PATH;
	    		}
	    		
	    		
	    	}
	    	
	        //prepare unique upload folder
	        //				$tmpstmp = $this->uniqfolder(PDF_PATH);
	
	        //get upload folder name
	        $tmpstmp_filename = basename($tmpstmp);
	
	        //get original file name
	        $file_name_real = basename($source_path);
	        $source_path_info = pathinfo($source_path);
	
	
	        //construct upload folder, file destination
	        $destination_path .=  "/" . $tmpstmp . '/' . $source_path_info['filename'] . '.' . $source_path_info['extension'];
	        $db_filename_destination = $tmpstmp . '/' . $source_path_info['filename'] . '.' . $source_path_info['extension'];
	
	        
	        //do a copy (from place where the pdf is generated to upload folder
	        copy($source_path, $destination_path);
// 	        if($_REQUEST['zzz'])
// 	        {
// 	            print_r("Copied from:");
// 	            print_r($source_path);
// 	            print_r("\n\n");
	
// 	            print_r("Copied to:");
// 	            print_r($destination_path);
// 	            print_r("\n\n");
	
// 	            print_r("Copied");
// 	            var_dump(copy($source_path, $destination_path));
// 	            print_r("\n\n");
// 	        }
	
	        //prepare cmd for folder zip
// 	        $cmd = "zip -9 -r -P " . $this->filepass . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;
	
// 	        if($_REQUEST['zzz'])
// 	        {
// 	            print_r("Executed cmd:");
// 	            var_dump($cmd);
// 	            print_r("\n\n");
	
// 	            print_r("Executed?:");
// 	            var_dump(exec($cmd));
// 	            print_r("\n\n");
// 	        }
	        //execute - zip the folder
// 	        exec($cmd);

	        $zipname = $tmpstmp . ".zip";
	        /*
	        $filename = "uploads/" . $tmpstmp . ".zip";
	        */
// 	        if($test_path == false){
// 	        	$filename = "uploads/" . $tmpstmp . ".zip";
// 	        }else{
// 	        	$filename = $test_path . "/" . $tmpstmp . ".zip";	 
// 	        }
	        
	        //connect
// 	        $con_id = Pms_FtpFileupload::ftpconnect();
// 	        if($_REQUEST['zzz'])
// 	        {
// 	            print_r("Connection ID:");
// 	            var_dump($con_id);
// 	            print_r("\n\n");
// 	            exit;
// 	        }
// 	        if($con_id)
// 	        {
// 	            //do upload
// 	            $upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
// 	            //close connection
// 	            Pms_FtpFileupload::ftpconclose($con_id);
// 	        }


            $client_data = Pms_CommonData::getClientDataFp($clientid);
            $file_password = $client_data[0]['fileupoadpass'];
            
            $filename = Pms_CommonData :: ftp_put_queue($destination_path ,  $legacy_path, $is_zipped = NULL, $foster_file,$clientid,$file_password );
            
	        return $db_filename_destination;
	    }
	}
	/* PHPDOCX WORD AND PDF END */
	
	
	
	private function resetuploadvars()
	{
	    //clear failed/other upload session vars
	    $_SESSION['template_filename'] = '';
	    unset($_SESSION['template_filename']);
	
	    $_SESSION['template_filepath'] = '';
	    unset($_SESSION['template_filepath']);
	
	    $_SESSION['template_filetype'] = '';
	    unset($_SESSION['template_filetype']);
	}
	
	
	public function hvtypeslistAction(){
	    // get associated clients of current clientid START
	    $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
	    if($connected_client){
	        $clientid = $connected_client;
	    } else{
	        $clientid = $this->clientid;
	    }
	     
	    $htypes = HospizVisitsTypes::get_client_hospiz_visits_types($clientid);
	    $this->view->htypes= $htypes;
	    
	}

	public function addhvtypeAction(){

	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	    
	        
	    // get associated clients of current clientid START
	    $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
	    if($connected_client){
	        $clientid = $connected_client;
	    } else{
	        $clientid = $this->clientid;
	    }
	    
	    if ($this->getRequest()->isPost())
	    {
	        $form = new Application_Form_HospizVisitsTypes();
	    
	        $_POST['clientid'] = $clientid ;
	        if ($form->validate($_POST))
	        {
	            $form->insert_data($_POST);
	            
	            $this->_redirect(APP_BASE . 'voluntaryworkers/hvtypeslist?flg=suc');
	            $this->view->error_message = $this->view->translate("recordupdatedsucessfully");
	        }
	        else
	        {
	            $form->assignErrorMessages();
	            $this->retainValues($_POST);
	        }
	    }
	    
	}
	
	public function edithvtypeAction(){

	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	    
	        
	    $this->view->act = "voluntaryworkers/edithvtype?id=" . $_GET['id'];
	    
	    $this->_helper->viewRenderer('addhvtype');
	    
	    
	    // get associated clients of current clientid START
	    $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
	    if($connected_client){
	        $clientid = $connected_client;
	    } else{
	        $clientid = $this->clientid;
	    }
	    
	    if ($this->getRequest()->isPost())
	    {
	        $form = new Application_Form_HospizVisitsTypes();
	    
	        $_POST['clientid'] = $clientid ;
	        $_POST['id'] = $_GET['id'];
	        if ($form->validate($_POST))
	        {
	            $form->update_data($_POST);
	            
	            $this->_redirect(APP_BASE . 'voluntaryworkers/hvtypeslist?flg=suc');
	            $this->view->error_message = $this->view->translate("recordupdatedsucessfully");
	        }
	        else
	        {
	            $form->assignErrorMessages();
	            $this->retainValues($_POST);
	        }
	    }
	    
	    if($_GET['id']){
	        
    	    $htype = HospizVisitsTypes::get_client_hospiz_visits_types($clientid,$_GET['id']);
    	    if($htype){
    	        $this->view->grund = $htype[$_GET['id']]['grund'];
    	        $this->view->billable = $htype[$_GET['id']]['billable'];
    	    }
	    }
	    
	    
	}
	

	public function deletehvtypeAction ()
	{

		// get associated clients of current clientid START
	    $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
	    if($connected_client){
	        $clientid = $connected_client;
	    } else{
	        $clientid = $this->clientid;
	    }
	    
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	    
	    $this->_helper->viewRenderer->setNoRender();
	    
	    $fdoc = Doctrine::getTable('HospizVisitsTypes')->findOneByIdAndClientid($_REQUEST['id'], $clientid);
	    if($fdoc)
	    {
	        $fdoc->isdelete = 1;
	        $fdoc->save();
	    
	        $this->redirect(APP_BASE . 'voluntaryworkers/hvtypeslist?flg=del_suc');
	        exit;
	    }
	    else
	    {
	        $this->redirect(APP_BASE . 'voluntaryworkers/hvtypeslist?flg=del_err');
	        exit;
	    }
	    
	}
	
	
	/**
	 * 
	 * Jul 24, 2017 @claudiu 
	 *
	 */
	public function sendemail2vwshistoryAction()
	{
		 
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->redirect(APP_BASE . "error/previlege" , array("exit" => true));
		}
		$this->view->email_tabs = $_REQUEST['email_tabs']; //ISPC - 2114 - archive function for vw
	
		//this IF = the dataTables ajax request
		if ($this->getRequest()->isXmlHttpRequest() && $_POST['action'] == "fetch_emails_list")
		{
	
			
			// get associated clients of current clientid START
			$connected_client = VwGroupAssociatedClients::connected_parent($this->logininfo->clientid);
			if($connected_client){
				$clientid = $connected_client;
			} else{
				$clientid = $this->logininfo->clientid;
			}
			

			//datatables settings
			$offset = (!empty($_REQUEST['start']))  ? (int)$_REQUEST['start']  : 0 ;
			$length = (!empty($_REQUEST['length'])) ? (int)$_REQUEST['length'] : 50 ;

			$filter_by_vw = false;
			$filtered_ids = array();
	
			if (trim($_REQUEST['sSearch']) != ''){
				$filtered_ids = Voluntaryworkers::search_vwids($_REQUEST['sSearch'] , $clientid);
				if ( empty($filtered_ids) ) {
	
					$this->returnDatatablesEmptyAndExit(); // return NO RESULT 4 dataTables
				}
				$filter_by_vw = true;
			}
	
			$filters = array();
			$filters['limit'] = $length;
			$filters['offset'] = $offset;
	
	
			//count emails
			$email_log = new VwEmailsLog();
			
			$emails_count_filteres =
			$emails_count = $email_log->get_grouped_log_count( $this->logininfo->clientid );
	
			if ( $filter_by_vw ) {
					
				$filters['recipient'] =  $filtered_ids;
				$emails_count_filteres = $email_log->get_grouped_log_filtered_count( $this->logininfo->clientid , $filters);
			}
	
			//get emails groupped as single rows
			$emails = $email_log->get_grouped_log( $this->logininfo->clientid , $filters);
	

			$results = array();
	
			if (! empty($emails)) {
				 
				$users = array_column($emails, 'sender');
				$user_id_arr = array_unique($users);
				$user_names_array = User::getUsersNiceName($user_id_arr, $this->logininfo->clientid);
	
				 
				$recipients = array_column($emails, 'recipients');
				$recipients_id_arr = array_unique(explode(',', implode(',', $recipients)));			
				$recipients_name_array = Voluntaryworkers::getVoluntaryworkersNiceName($recipients_id_arr, $clientid);
				
				 
				$attachment_id_arr = array_unique(array_column($emails, 'attachment_id'));
				$attachment_id_arr = array_filter( $attachment_id_arr, 'strlen' );
				$attachment_name_array = ClientFileUpload::get_files_by_id($attachment_id_arr);
					
				 
				$histoy_lang = $this->translate('sendemail2vwshistory_lang');
				$no_of_recipients_text = $histoy_lang['no_of_recipients_text'];
				 
				 
				foreach($emails  as $row) {
	
					$recipients_id_arr = explode(',', $row['recipients']);
					$email_recipients = array();
					foreach ($recipients_id_arr as $recipient_id) {
						$email_recipients[] = $recipients_name_array[$recipient_id]['nice_name'];
					}
	
	
					$data =  array(
							"debugcolumn" 				=> null,
	
							"entrydate" 				=> date("d.m.Y", strtotime($row['date'])),
							"email_sent_by" 			=> $user_names_array[ $row['sender'] ] ['nice_name'],
	
							"email_subject" 			=> $row['title_plain'],
							"email_content" 			=> $row['content_plain'],
	
							"email_attachment_id" 		=> $row['attachment_id'],
							"email_attachment_filename" => $attachment_name_array[$row['attachment_id']]["title_decrypted"],
	
							"email_recipients"		 	=> $email_recipients,
							"no_of_recipients"	=> sprintf($no_of_recipients_text , count($email_recipients)),
	
					);
					
					if( defined("APPLICATION_ENV") && APPLICATION_ENV == 'development') {
						$data['debugcolumn'] = $row['id'];
						
					}
	
					$results[] = $data;
				}
				 
				 
			}
	
			 
	
			$response = array();
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $emails_count;//count($results);//$full_count;
	
			// 	        	$response['iTotalRecords'] = 0;
			// 	        	$response['iTotalDisplayRecords'] = 0;
			// 	        	$response['aaData'] = $results;
	
			$response['recordsFiltered'] = $emails_count_filteres;//count($results);
			$response['data'] = $results; //empty($results) ? array() : $results;
			 
	
			ob_end_clean();	ob_start();
			$this->_helper->json->sendJson($response);
	
		} 
			 
	}
	
	//get view list secondary statuses
	public function secondarystatuseslistAction(){
	 // get associated clients of current clientid START
	    $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
	    if($connected_client){
	        $clientid = $connected_client;
	    } else{
	        $clientid = $this->clientid;
	    }
	
		//populate the datatables
		if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
			$ast = new VoluntaryworkersStatuses();
			$attst = $ast->get_voluntaryworkers_attached_statuses($clientid);
			$attached_statuses = array();
			
			foreach($attst as $kst=>$vst)
			{
				$attached_statuses[] = $vst['status'];
			}
		
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			if(!$_REQUEST['length']){
				$_REQUEST['length'] = "25";
			}
			$limit = (int)$_REQUEST['length'];
			$offset = (int)$_REQUEST['start'];
			$search_value = addslashes($_REQUEST['search']['value']);
				
			$columns_array = array(
					"0" => "description"
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
			$chars[ 'Ä' ] = 'Ae';
    		$chars[ 'ä' ] = 'ae';
    		$chars[ 'Ö' ] = 'Oe';
    		$chars[ 'ö' ] = 'oe';
    		$chars[ 'Ü' ] = 'Ue';
    		$chars[ 'ü' ] = 'ue';
    		$chars[ 'ß' ] = 'ss';
			
			$colch =addslashes(htmlspecialchars($columns_array[$order_column]));
				
			foreach($chars as $kch=>$vch)
			{
				$colch = 'REPLACE('.$colch.', "'.$kch.'", "'.$vch.'")';
			}
			
			$order_by_str ='LOWER('.$colch.') '.$order_dir;
//			$order_by_str = 'LOWER(REPLACE(REPLACE(REPLACE('.addslashes(htmlspecialchars($columns_array[$order_column])). ', "ö", "o"), "ü", "u"), "Ü", "U")) '.$order_dir;
			//var_dump($order_by_str);
			// ########################################
			// #####  Query for count ###############
			$fdoc1 = Doctrine_Query::create();
			$fdoc1->select('count(*)');
			$fdoc1->from('VoluntaryWorkersSecondaryStatuses');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0  ");
			
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
				//$fdoc1->andWhere("(lower(description) like ?)", array("%" . trim(strtolower($search_value)) . "%"));
			}
			
			$fdocarray = $fdoc1->fetchArray();
			$filter_count  = $fdocarray[0]['count'];
			
			// ########################################
			// #####  Query for details ###############
			$fdoc1->select('*');
			
			$fdoc1->orderBy($order_by_str);
			//echo $fdoc1->getSQLQuery();
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
				
				if(in_array($mdata['id'], $attached_statuses))
				{
					$resulted_data[$row_id]['description'] = sprintf($link,'<span>!</span>'.$mdata['description']);
					$ask_on_del = '1';
				}
				else
				{
					$resulted_data[$row_id]['description'] = sprintf($link,$mdata['description']);
					$ask_on_del = '0';
				}
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'voluntaryworkers/secondarystatusesedit?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" del="'.$ask_on_del.'" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
				$row_id++;
			}
				
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
	
			$this->_helper->json->sendJson($response);
		}
			
	}
	//set add to list voluntaryworkerssecondarystatuses
	public function secondarystatusesaddAction(){	
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
	
		// get associated clients of current clientid START
		$connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
		if($connected_client){
			$clientid = $connected_client;
		} else{
			$clientid = $this->clientid;
		}
		
		$this->_helper->viewRenderer('secondarystatusesedit');
		if($this->getRequest()->isPost())
		{
			$form = new Application_Form_VoluntaryWorkersSecondaryStatuses();
				
			if($form->validate($_POST))
			{
				$form->insert($_POST, $clientid);
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}
			else
			{
				$form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
	}
	
	//set edit list voluntaryworkerssecondarystatuses
	public function secondarystatuseseditAction(){		
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
			
		$this->_helper->viewRenderer('secondarystatusesedit');
	
		if($this->getRequest()->isPost())
		{
			$form = new Application_Form_VoluntaryWorkersSecondaryStatuses();
				
			if($form->validate($_POST))
			{
				$_POST['id'] = (int)$_GET['id'];
				//$form->update($_POST, $clientid);
				$form->update($_POST);
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE . $this->getRequest()->getControllerName() . "/secondarystatuseslist?flg=succ&mes=".urlencode($this->view->error_message));
				exit;
			}
			else
			{
				$form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
			
		if ((int)$_GET['id'] > 0)
		{
			//$fdoc = Doctrine::getTable('VoluntaryWorkersSecondaryStatuses')->findbyIdAndClientid((int)$_GET['id'], $clientid );
			$fdoc = Doctrine::getTable('VoluntaryWorkersSecondaryStatuses')->findbyId((int)$_GET['id']);
			if ($fdoc)
			{
				$fdocarray = $fdoc->toArray();
				$fdocarray = array_values($fdocarray);
				$fdocarray = $fdocarray[0];
				$this->retainValues($fdocarray);
			}
		}
			
	}
	//set delete from list voluntaryworkerssecondarystatuses
	public function secondarystatusesdeleteAction()
	{		
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
	
		if((int)$_GET['id'] > 0)
		{
			$thrash = Doctrine_Query::create()
			->update("VoluntaryWorkersSecondaryStatuses")
			->set('isdelete', 1)
			->where('id = ?' ,(int)$_GET['id'] );
			//->andWhere('clientid = ? ' , $clientid);
			$thrash->execute();
		}
		$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
		$this->_redirect(APP_BASE . $this->getRequest()->getControllerName() . "/secondarystatuseslist?flg=succ&mes=".urlencode($this->view->error_message));
		exit;
	}
	
	//get hospiz worker visits list
	public function gethospizworkervisitsAction(){
		//populate the datatables
		$logininfo = new Zend_Session_Namespace('Login_Info');
		// get associated clients of current clientid START 
		$connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
		if($connected_client){
		    $clientid = $connected_client;
		} else{
		    $clientid = $this->clientid;
		}
		
		$hospizv = new PatientHospizvizits();
		$reasonsarray = $hospizv->gethospizvreason();
		
		//get active patients
		$patietns_act = Doctrine_Query::create()
		->select("p.ipid,p.isdischarged,p.isstandby,p.isstandbydelete,
				e.epid,
				AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name,
				AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name
				")
						->from('EpidIpidMapping e')
						->leftJoin('e.PatientMaster p')
						->where('e.ipid = p.ipid')
						->andWhere('e.clientid = ?', $this->clientid)
						->andWhere('p.isdelete = "0"');
						$active_patients = $patietns_act->fetchArray();
		
		
		if (!empty($active_patients))
		{

			foreach ($active_patients as $k_patient => $v_patient)
			{
				if($v_patient['PatientMaster']['isstandby'] == "0" && $v_patient['PatientMaster']['isstandbydelete'] == "0"){
					$patients_epids_selector[$v_patient['PatientMaster']['id']] = strtoupper($v_patient['epid']); //used in patients dropdown
					$patients_ipids_selector[$v_patient['PatientMaster']['id']] = $v_patient['PatientMaster']['ipid']; //used to match patient_master id with ipid
					$active_ipids[] =  $v_patient['PatientMaster']['ipid'];
				}
				$client_ipids[]  = $v_patient['PatientMaster']['ipid'];
				$patient_details[$v_patient['PatientMaster']['ipid']]['name'] = $v_patient['PatientMaster']['last_name'].', '.$v_patient['PatientMaster']['first_name'];
				$patient_details[$v_patient['PatientMaster']['ipid']]['epid'] = $v_patient['epid'];
				$patient_details[$v_patient['PatientMaster']['ipid']]['epid'] = $v_patient['epid'];
				$patient_details[$v_patient['PatientMaster']['ipid']]['isdischarged'] = $v_patient['PatientMaster']['isdischarged'];

			}
		}
		//print_r($patient_details);
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		if(!$_REQUEST['length']){
			$_REQUEST['length'] = "25";
		}
		$limit = (int)$_REQUEST['length'];
		$offset = (int)$_REQUEST['start'];
		$search_value = addslashes($_REQUEST['search']['value']);
		$vtype = $_REQUEST['vtype'];
		$workerid = $_REQUEST['workerid'];
		if($_REQUEST['from_simple'])
		{
			$from_simple = date('Y-m-d', strtotime($_REQUEST['from_simple']));
		}
		else 
		{
			$from_simple = null;
		}
		if($_REQUEST['to_simple'])
		{
			$to_simple = date('Y-m-d', strtotime($_REQUEST['to_simple']));
		}
		else
		{
			$to_simple = null;
		}
		
		if($_REQUEST['from_bulk'])
		{
			$from_bulk = date('Y-m-d', strtotime($_REQUEST['from_bulk']));
		}
		else
		{
			$from_bulk = null;
		}
		if($_REQUEST['to_bulk'])
		{
			$to_bulk = date('Y-m-d', strtotime($_REQUEST['to_bulk']));
		}
		else
		{
			$to_bulk = null;
		}
		
		$columns_array = array(
				"1" => "patient_name",
				"2" => "reason_name",
				"3" => "hospizvizit_date",
				"4" => "amount",
				"5" => "besuchsdauer",
				"6" => "fahrtkilometer",
				"7" => "fahrtzeit",
				"8" => "nightshift_name"
		);
		$columns_search_array = $columns_array;
			
		if(isset($_REQUEST['order'][0]['column']))
		{
			$order_column = $_REQUEST['order'][0]['column'];
			$order_dir = $_REQUEST['order'][0]['dir'];
		}
		else
		{
			$order_column = '';
		}
		
		$hospiz_visits = $hospizv->getworkervisits($workerid, $vtype);
		foreach($hospiz_visits as $sk => $sv){
			if(in_array($sv['ipid'],$client_ipids)){
				$vwv_data[$sv['id']] = $sv;
			}
		}
		if ($vtype == 'n' && ($from_simple !== null || $to_simple != null))
		{			
			$vwv_data = array_filter($vwv_data, function($var) use($from_simple, $to_simple) {
				
				if($from_simple !== null && $to_simple === null)
				{
					return date('Y-m-d', strtotime($var['hospizvizit_date'])) >= $from_simple;
				}	
				elseif($from_simple === null && $to_simple !== null)
				{
					return date('Y-m-d', strtotime($var['hospizvizit_date'])) <= $to_simple;
				}
				elseif($from_simple !== null && $to_simple !== null)
				{
					return date('Y-m-d', strtotime($var['hospizvizit_date'])) >= $from_simple && date('Y-m-d', strtotime($var['hospizvizit_date'])) <= $to_simple;
				}
			});
		}
		
		if ($vtype == 'b' && ($from_bulk !== null || $to_bulk != null))
		{
			$vwv_data = array_filter($vwv_data, function($var) use($from_bulk, $to_bulk) {
		
				if($from_bulk !== null && $to_bulk === null)
				{
					return date('Y-m-d', strtotime($var['hospizvizit_date'])) >= $from_bulk;
				}
				elseif($from_bulk === null && $to_bulk !== null)
				{
					return date('Y-m-d', strtotime($var['hospizvizit_date'])) <= $to_bulk;
				}
				elseif($from_bulk !== null && $to_bulk !== null)
				{
					return date('Y-m-d', strtotime($var['hospizvizit_date'])) >= $from_bulk && date('Y-m-d', strtotime($var['hospizvizit_date'])) <= $to_bulk;
				}
			});
		}
	//var_dump($vwv_data); exit;	
		$fdoclimit = array();
		foreach($vwv_data as $kvdata=>$vwdata)
		{
			$vwdata['patient_name'] = $patient_details[$vwdata['ipid']]['name'];
			$vwdata['reason_name'] = $reasonsarray[$vwdata['grund']];
			if($vwdata['nightshift'] == 0)
			{
				$vwdata['nightshift_name'] = $this->translator->translate('option_no');
			}
			else
			{
				$vwdata['nightshift_name'] = $this->translator->translate('option_yes');
			}
			$fdoclimit[$kvdata] = $vwdata;
		}
		
		$full_count  = count($fdoclimit);
	
		if(trim($search_value) != "")
		{
			$regexp = trim($search_value);
			Pms_CommonData::value_patternation($regexp);
	
			foreach($columns_search_array as $ks=>$vs)
			{
				$pairs[$vs] = trim(str_replace('\\', '',$regexp));
			}
			//var_dump($pairs);
			$fdocsearch = array();
			foreach ($fdoclimit as $skey => $sval) {
				foreach ($pairs as $pkey => $pval) {
					if($pkey == 'hospizvizit_date')
					{
						$sval[$pkey] = date('d.m.Y H:i', strtotime($sval[$pkey]));
					}
						
					$pval_arr = explode('|', $pval);
						
					foreach($pval_arr as $kpval=>$vpval)
					{
						if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) {
							$fdocsearch[$skey] = $sval;
							break;
						}
					}
						
				}
			}
				
	
			$fdoclimit = $fdocsearch;
		}
		$filter_count  = count($fdoclimit);
			
		if($order_column != "")
		{
			$sort_col = array();
			foreach ($fdoclimit as $key=> $row)
			{
				if($order_column == '3')
				{
					$sort_col[$key] = strtotime($row[$columns_array[$order_column]]);
				}
				else
				{
					$row[$columns_array[$order_column].'_tr'] = mb_strtolower($row[$columns_array[$order_column]], 'UTF-8');
					$fdoclimit[$key] = $row;
					$sort_col[$key] = $row[$columns_array[$order_column].'_tr'];
				}
			}
			if($order_dir == 'desc')
			{
				$dir = SORT_DESC;
			}
			else
			{
				$dir = SORT_ASC;
			}
			array_multisort($sort_col, $dir, $fdoclimit);
				
			$keyw = $columns_array[$order_column].'_tr';
			array_walk($fdoclimit, function (&$v) use ($keyw) {
				unset($v[$keyw]);
			});
		}
	
		if($limit != "")
		{
			$fdoclimit = array_slice($fdoclimit, $offset, $limit, true);
		}
		$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit);
	
		$report_ids = array();
		$fdoclimit_arr = array();
		$nr = 1;
		foreach ($fdoclimit as $key => $report)
		{
			$fdoclimit_arr[$report['id']] = $report;
			$fdoclimit_arr[$report['id']]['no'] = $nr;
			$report_ids[] = $report['id'];
			$nr++;
		}
	
		$row_id = 0;
		$link = "";
	
		$resulted_data = array();
		foreach($fdoclimit_arr as $report_id =>$mdata)
		{
			$link = '%s';
			$resulted_data[$row_id]['no'] = sprintf($link,$mdata['no']);
			$resulted_data[$row_id]['patient_name'] = sprintf($link,$mdata['patient_name']);
			$resulted_data[$row_id]['reason_name'] = sprintf($link,$mdata['reason_name']);
			if($vtype == "n")
			{
				$resulted_data[$row_id]['hospizvizit_date'] = date('d.m.Y', strtotime($mdata['hospizvizit_date']));
			}
			else
			{
				$resulted_data[$row_id]['hospizvizit_date'] = date('Y', strtotime($mdata['hospizvizit_date']));
			}
			$resulted_data[$row_id]['amount'] = sprintf($link,$mdata['amount']);
			$resulted_data[$row_id]['besuchsdauer'] = sprintf($link,$mdata['besuchsdauer']);
			$resulted_data[$row_id]['fahrtkilometer'] = sprintf($link,$mdata['fahrtkilometer']);
			$resulted_data[$row_id]['fahrtzeit'] = sprintf($link,$mdata['fahrtzeit']);
			$resulted_data[$row_id]['nightshift_name'] = sprintf($link,$mdata['nightshift_name']);
			//TODO-3796 Lore 16.02.2021
            $resulted_data[$row_id]['actions'] = '<a data-id="'.$mdata['id'].'"  data-vtype="'.$vtype.'"  class="edit_hvisit_data" href="javascript:void(0);"><img src="'.RES_FILE_PATH.'/images/edit.png"></a>';
            //ISPC-2834,Elena,24.03.2021
            $resulted_data[$row_id]['actions'] .= ' <a data-id="'.$mdata['id'].'"  data-vtype="'.$vtype.'"  class="delete_hvisit_data" href="javascript:void(0);"><img src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
			
			$row_id++;
		}
	
		$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		$response['recordsTotal'] = $full_count;
		$response['recordsFiltered'] = $filter_count; // ??
		$response['data'] = $resulted_data;
	
		$this->_helper->json->sendJson($response);
	}
	
	public function getworkerworkAction(){
		//populate the datatables
		$logininfo = new Zend_Session_Namespace('Login_Info');
		// get associated clients of current clientid START
		$connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
		if($connected_client){
			$clientid = $connected_client;
		} else{
			$clientid = $this->clientid;
		}
	
		$hospizv = new PatientHospizvizits();
		$reasonsarray = $hospizv->gethospizvreason();
	
		
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		if(!$_REQUEST['length']){
			$_REQUEST['length'] = "25";
		}
		$limit = (int)$_REQUEST['length'];
		$offset = (int)$_REQUEST['start'];
		$search_value = addslashes($_REQUEST['search']['value']);
		$vtype = $_REQUEST['vtype'];
		$workerid = $_REQUEST['workerid'];
		if($_REQUEST['from_simple'])
		{
			$from_simple = date('Y-m-d', strtotime($_REQUEST['from_simple']));
		}
		else
		{
			$from_simple = null;
		}
		if($_REQUEST['to_simple'])
		{
			$to_simple = date('Y-m-d', strtotime($_REQUEST['to_simple']));
		}
		else
		{
			$to_simple = null;
		}

		if($_REQUEST['from_bulk'])
		{
			$from_bulk = date('Y-m-d', strtotime($_REQUEST['from_bulk']));
		}
		else
		{
			$from_bulk = null;
		}
		if($_REQUEST['to_bulk'])
		{
			$to_bulk = date('Y-m-d', strtotime($_REQUEST['to_bulk']));
		}
		else
		{
			$to_bulk = null;
		}

		$columns_array = array(
				"1" => "reason_name",
				"2" => "work_date",
				"3" => "besuchsdauer",
				"4" => "fahrtkilometer",
				"5" => "fahrtzeit",
				"6" => "nightshift_name"
		);
		$columns_search_array = $columns_array;
			
		if(isset($_REQUEST['order'][0]['column']))
		{
			$order_column = $_REQUEST['order'][0]['column'];
			$order_dir = $_REQUEST['order'][0]['dir'];
		}
		else
		{
			$order_column = '';
		}

		// Work data -  
		$vww_model = new VwWorkdata();
		$vwv_data = $vww_model->get_vw_work_data($workerid, $vtype);
		if ($vtype == 'n' && ($from_simple !== null || $to_simple != null))
		{
			$vwv_data = array_filter($vwv_data, function($var) use($from_simple, $to_simple) {

				if($from_simple !== null && $to_simple === null)
				{
					return date('Y-m-d', strtotime($var['work_date'])) >= $from_simple;
				}
				elseif($from_simple === null && $to_simple !== null)
				{
					return date('Y-m-d', strtotime($var['work_date'])) <= $to_simple;
				}
				elseif($from_simple !== null && $to_simple !== null)
				{
					return date('Y-m-d', strtotime($var['work_date'])) >= $from_simple && date('Y-m-d', strtotime($var['work_date'])) <= $to_simple;
				}
			});
		}

		if ($vtype == 'b' && ($from_bulk !== null || $to_bulk != null))
		{
			$vwv_data = array_filter($vwv_data, function($var) use($from_bulk, $to_bulk) {

				if($from_bulk !== null && $to_bulk === null)
				{
					return date('Y-m-d', strtotime($var['work_date'])) >= $from_bulk;
				}
				elseif($from_bulk === null && $to_bulk !== null)
				{
					return date('Y-m-d', strtotime($var['work_date'])) <= $to_bulk;
				}
				elseif($from_bulk !== null && $to_bulk !== null)
				{
					return date('Y-m-d', strtotime($var['work_date'])) >= $from_bulk && date('Y-m-d', strtotime($var['work_date'])) <= $to_bulk;
				}
			});
		}
		//var_dump($vwv_data); exit;
		$fdoclimit = array();
		foreach($vwv_data as $kvdata=>$vwdata)
		{
			$vwdata['reason_name'] = $reasonsarray[$vwdata['grund']];
			if($vwdata['nightshift'] == 0)
			{
				$vwdata['nightshift_name'] = $this->translator->translate('option_no');
			}
			else
			{
				$vwdata['nightshift_name'] = $this->translator->translate('option_yes');
			}
			$fdoclimit[$kvdata] = $vwdata;
		}

		$full_count  = count($fdoclimit);

		if(trim($search_value) != "")
		{
			$regexp = trim($search_value);
			Pms_CommonData::value_patternation($regexp);

			foreach($columns_search_array as $ks=>$vs)
			{
				$pairs[$vs] = trim(str_replace('\\', '',$regexp));
			}
			//var_dump($pairs);
			$fdocsearch = array();
			foreach ($fdoclimit as $skey => $sval) {
				foreach ($pairs as $pkey => $pval) {
					if($pkey == 'work_date')
					{
						$sval[$pkey] = date('d.m.Y H:i', strtotime($sval[$pkey]));
					}

					$pval_arr = explode('|', $pval);

					foreach($pval_arr as $kpval=>$vpval)
					{
						if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) {
							$fdocsearch[$skey] = $sval;
							break;
						}
					}

				}
			}


			$fdoclimit = $fdocsearch;
		}
		$filter_count  = count($fdoclimit);
			
		if($order_column != "")
		{
			$sort_col = array();
			foreach ($fdoclimit as $key=> $row)
			{
				if($order_column == '2')
				{
					$sort_col[$key] = strtotime($row[$columns_array[$order_column]]);
				}
				else
				{
					$row[$columns_array[$order_column].'_tr'] = mb_strtolower($row[$columns_array[$order_column]], 'UTF-8');
					$fdoclimit[$key] = $row;
					$sort_col[$key] = $row[$columns_array[$order_column].'_tr'];
				}
			}
			if($order_dir == 'desc')
			{
				$dir = SORT_DESC;
			}
			else
			{
				$dir = SORT_ASC;
			}
			array_multisort($sort_col, $dir, $fdoclimit);

			$keyw = $columns_array[$order_column].'_tr';
			array_walk($fdoclimit, function (&$v) use ($keyw) {
				unset($v[$keyw]);
			});
		}

		if($limit != "")
		{
			$fdoclimit = array_slice($fdoclimit, $offset, $limit, true);
		}
		$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit);

		$report_ids = array();
		$fdoclimit_arr = array();
		$nr = 1;
		foreach ($fdoclimit as $key => $report)
		{
			$fdoclimit_arr[$report['id']] = $report;
			$fdoclimit_arr[$report['id']]['no'] = $nr;
			$report_ids[] = $report['id'];
			$nr++;
		}

		$row_id = 0;
		$link = "";

		$resulted_data = array();
		foreach($fdoclimit_arr as $report_id =>$mdata)
		{
			$link = '%s';
			$resulted_data[$row_id]['no'] = sprintf($link,$mdata['no']);
			$resulted_data[$row_id]['reason_name'] = sprintf($link,$mdata['reason_name']);
			$resulted_data[$row_id]['work_date'] = date('d.m.Y', strtotime($mdata['work_date']));
			$resulted_data[$row_id]['besuchsdauer'] = sprintf($link,$mdata['besuchsdauer']);
			$resulted_data[$row_id]['fahrtkilometer'] = sprintf($link,$mdata['fahrtkilometer']);
			$resulted_data[$row_id]['fahrtzeit'] = sprintf($link,$mdata['fahrtzeit']);
			$resulted_data[$row_id]['nightshift_name'] = sprintf($link,$mdata['nightshift_name']);
			$resulted_data[$row_id]['actions'] = '<a data-id="'.$mdata['id'].'" class="edit_wb" href="javascript:void(0);"><img src="'.RES_FILE_PATH.'/images/edit.png"></a><a data-id="'.$mdata['id'].'" class="delete_work_data" href="javascript:void(0);"><img src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
			$row_id++;
		}

		$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		$response['recordsTotal'] = $full_count;
		$response['recordsFiltered'] = $filter_count; // ??
		$response['data'] = $resulted_data;

		$this->_helper->json->sendJson($response);
	}

	public function getworkeractivityAction(){
		//populate the datatables
		$logininfo = new Zend_Session_Namespace('Login_Info');
		// get associated clients of current clientid START
		$connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
		if($connected_client){
			$clientid = $connected_client;
		} else{
			$clientid = $this->clientid;
		}
		
		$limit = (int)$_REQUEST['length'];
		$offset = (int)$_REQUEST['start'];
		$search_value = addslashes($_REQUEST['search']['value']);
		$workerid = $_REQUEST['workerid'];
		if($_REQUEST['from_activity'])
		{
			$from_activity = date('Y-m-d', strtotime($_REQUEST['from_activity']));
		}
		else
		{
			$from_activity = null;
		}
		if($_REQUEST['to_activity'])
		{
			$to_activity = date('Y-m-d', strtotime($_REQUEST['to_activity']));
		}
		else
		{
			$to_activity = null;
		}
		
		//save changes before redrawing datatables
		
		if(!empty($_REQUEST['changedrows']))
		{
			$post['changedrows'] = $_REQUEST['changedrows'];
			$post['did'] = $workerid;
			$post['clientid'] = $clientid;
			$updvw = new Application_Form_Voluntaryworkers();
			$updvw->update_from_details($post, true);
		}
		
		// event types
		$event_types = TeamEventTypes::get_team_event_types ($this->clientid,true );
			
		foreach($event_types as $ket =>$vet){
			$event_types_select[$vet['id']] = $vet;
		}
		//print_r($event_types_select); exit;
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		if(!$_REQUEST['length']){
			$_REQUEST['length'] = "25";
		}
	
		$columns_array = array(
				"0" => "date",
				"1" => "activity",
				"2" => "comment",
				"3" => "duration",
				"4" => "driving_time",
				"5" => "team_event_type_name"
		);
		$columns_search_array = $columns_array;
			
		if(isset($_REQUEST['order'][0]['column']))
		{
			$order_column = $_REQUEST['order'][0]['column'];
			$order_dir = $_REQUEST['order'][0]['dir'];
		}
		else
		{
			$order_column = '';
		}
	
		-
		//get existing activities
		$vw_activities = new VoluntaryworkersActivities();
		$vwv_data = $vw_activities->get_vw_activities($workerid) ;
		if ($from_activity !== null || $to_activity != null)
		{
			$vwv_data = array_filter($vwv_data, function($var) use($from_activity, $to_activity) {
	
				if($from_activity !== null && $to_activity === null)
				{
					return date('Y-m-d', strtotime($var['date'])) >= $from_activity;
				}
				elseif($from_activity === null && $to_activity !== null)
				{
					return date('Y-m-d', strtotime($var['date'])) <= $to_activity;
				}
				elseif($from_activity !== null && $to_activity !== null)
				{
					return date('Y-m-d', strtotime($var['date'])) >= $from_activity && date('Y-m-d', strtotime($var['date'])) <= $to_activity;
				}
			});
		}
		
		//print_r($vwv_data); exit;
		$fdoclimit = array();
		foreach($vwv_data as $kvdata=>$vwdata)
		{
			$vwdata['team_event_type_name'] = $event_types_select[$vwdata['team_event_type']]['name'];
			$fdoclimit[$kvdata] = $vwdata;
		}
		
		$full_count  = count($fdoclimit);
	
		if(trim($search_value) != "")
		{
			$regexp = trim($search_value);
			Pms_CommonData::value_patternation($regexp);
	
			foreach($columns_search_array as $ks=>$vs)
			{
				$pairs[$vs] = trim(str_replace('\\', '',$regexp));
			}
			//var_dump($pairs);
			$fdocsearch = array();
			foreach ($fdoclimit as $skey => $sval) {
				foreach ($pairs as $pkey => $pval) {
					if($pkey == 'date')
					{
						$sval[$pkey] = date('d.m.Y H:i', strtotime($sval[$pkey]));
					}
	
					$pval_arr = explode('|', $pval);
	
					foreach($pval_arr as $kpval=>$vpval)
					{
						if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) {
							$fdocsearch[$skey] = $sval;
							break;
						}
					}
	
				}
			}
	
	
			$fdoclimit = $fdocsearch;
		}
		$filter_count  = count($fdoclimit);
			
		if($order_column != "")
		{
			$sort_col = array();
			foreach ($fdoclimit as $key=> $row)
			{
				if($order_column == '0')
				{
					$sort_col[$key] = strtotime($row[$columns_array[$order_column]]);
				}
				else
				{
					$row[$columns_array[$order_column].'_tr'] = mb_strtolower($row[$columns_array[$order_column]], 'UTF-8');
					$fdoclimit[$key] = $row;
					$sort_col[$key] = $row[$columns_array[$order_column].'_tr'];
				}
			}
			if($order_dir == 'desc')
			{
				$dir = SORT_DESC;
			}
			else
			{
				$dir = SORT_ASC;
			}
			array_multisort($sort_col, $dir, $fdoclimit);
	
			$keyw = $columns_array[$order_column].'_tr';
			array_walk($fdoclimit, function (&$v) use ($keyw) {
				unset($v[$keyw]);
			});
		}
	
		if($limit != "")
		{
			$fdoclimit = array_slice($fdoclimit, $offset, $limit, true);
		}
		$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit);
	
		$report_ids = array();
		$fdoclimit_arr = array();
		$nr = 1;
		foreach ($fdoclimit as $key => $report)
		{
			$fdoclimit_arr[$report['id']] = $report;
			$report_ids[] = $report['id'];
			$nr++;
		}
	
		$row_id = 0;
		$link = "";
	
		$resulted_data = array();
		foreach($fdoclimit_arr as $report_id =>$mdata)
		{
			$link = '%s';
			$resulted_data[$row_id]['date'] = '<input type="text" name="activity['. $mdata['id'] . '][date]" value="'. date('d.m.Y',strtotime($mdata['date'])) .'"  class="a_date vw_date" />';
			$resulted_data[$row_id]['date'] .= '<input type="hidden" name="activity['. $mdata['id'] .'][id]" value="' . $mdata['id'] . '" />';
			$resulted_data[$row_id]['activity'] = '<input type="text" name="activity['. $mdata['id'] .'][activity]" value="' . $mdata['activity'] .'"  class="vw_activity"/>';
			$resulted_data[$row_id]['comment'] = '<input type="text" name="activity['. $mdata['id'] .'][comment]" value="' . $mdata['comment'] . '" class="vw_comment"/>';
			$resulted_data[$row_id]['comment'] .= '<input type="hidden" name="activity['. $mdata['id'] .'][team_event]" value="' . $mdata['team_event'] .'" />';
			$resulted_data[$row_id]['comment'] .= '<input type="hidden" name="activity['. $mdata['id'] .'][team_event_id]" value="'. $mdata['team_event_id'] . '" />';
			$resulted_data[$row_id]['duration'] = '<input type="text" name="activity['. $mdata['id'] .'][duration]" value="' . $mdata['duration'] . '" class="vw_duration"/>';
			$resulted_data[$row_id]['driving_time'] = '<input type="text" name="activity['. $mdata['id'] .'][driving_time]" value="' . $mdata['driving_time'] . '" class="vw_driving_time"/>';
			$resulted_data[$row_id]['team_event_type_name'] = '<select name="activity['. $mdata['id'] .'][team_event_type]"  class="event_select" id="event_type">
							<option value="">' . $this->translator->translate("select") . '</option>';
							
			foreach($event_types_select as $type_id => $type_data)
			{
			$resulted_data[$row_id]['team_event_type_name'] .= '<option value="' . $type_id .'"' . ($mdata['team_event_type'] == $type_id ? 'selected="selected"' :  "" ) . ' >'. $type_data['name'] .'</option>';
			}
			$resulted_data[$row_id]['team_event_type_name'] .= 	'</select>';
			$resulted_data[$row_id]['actions'] = '<a href="javascript:void(0)" class="delete_row" id="delete_'. $mdata['id'] . '"><img src="' .RES_FILE_PATH . '/images/action_delete.png" /></a>';
			$row_id++;
		}
	
		$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		$response['recordsTotal'] = $full_count;
		$response['recordsFiltered'] = $filter_count; // ??
		$response['data'] = $resulted_data;
	
		$this->_helper->json->sendJson($response);
	}
	
	public function getworkerassignsAction(){
	    //populate the datatables
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    // get associated clients of current clientid START
	    $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
	    if($connected_client){
	        $clientid = $connected_client;
	    } else{
	        $clientid = $this->clientid;
	    }
// 	    if($this->userid == "338"){
// 	    //ERROR REPORTING
// 	    ini_set('display_errors', 1);
// 	    ini_set('display_startup_errors', 1);
// 	    error_reporting(E_ALL);
// 	    }
	    
	    $limit = (int)$_REQUEST['length'];
	    $offset = (int)$_REQUEST['start'];
	    $search_value = addslashes($_REQUEST['search']['value']);
	    $workerid = $_REQUEST['workerid'];
	    
	    //save changes before redrawing datatables
	    
	    if(!empty($_REQUEST['changedrows']))
	    {
	        $post['changedrows'] = $_REQUEST['changedrows'];
	        $post['did'] = $workerid;
	        $post['clientid'] = $clientid;
	        $updvw = new Application_Form_Voluntaryworkers();
	        $updvw->update_from_details($post, true);
	    }
	    
	    // event types
	    $event_types = TeamEventTypes::get_team_event_types ($this->clientid,true );
	    
	    foreach($event_types as $ket =>$vet){
	        $event_types_select[$vet['id']] = $vet;
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    if(!$_REQUEST['length']){
	        $_REQUEST['length'] = "25";
	    }
	    
	    $columns_array = array(
	        "0" => "start_association_date",
	        "1" => "end_association_date",
	        "2" => "epid",
	        "3" => "patientname"
	    );
	    $columns_search_array = $columns_array;
	    
	    if(isset($_REQUEST['order'][0]['column']))
	    {
	        $order_column = $_REQUEST['order'][0]['column'];
	        $order_dir = $_REQUEST['order'][0]['dir'];
	    }
	    else
	    {
	        $order_column = '';
	    }
	    
	   
	    if($_REQUEST['yearassg'])
	    {
	        $yearassg = $_REQUEST['yearassg'];
	    }
	    else
	    {
	        $yearassg = null;
	    }
	    
	    //get active patients
	    $patietns_act = Doctrine_Query::create()
	    ->select("p.ipid,p.isdischarged,p.isstandby,p.isstandbydelete,
        		e.epid,
        		AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name,
        		AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name
		          ")
		->from('EpidIpidMapping e')
		->leftJoin('e.PatientMaster p')
		->where('e.ipid = p.ipid')
		->andWhere('e.clientid = "' . $this->clientid . '"')
		->andWhere('p.isdelete = "0"');
		$active_patients = $patietns_act->fetchArray();
		
		$active_ipids = array();
		$client_ipids = array();
		if (!empty($active_patients))
		{
		    
		    foreach ($active_patients as $k_patient => $v_patient)
		    {
		        if($v_patient['PatientMaster']['isstandby'] == "0" && $v_patient['PatientMaster']['isstandbydelete'] == "0"){
		            $patients_epids_selector[$v_patient['PatientMaster']['id']] = strtoupper($v_patient['epid']); //used in patients dropdown
		            $patients_ipids_selector[$v_patient['PatientMaster']['id']] = $v_patient['PatientMaster']['ipid']; //used to match patient_master id with ipid
		            $active_ipids[] =  $v_patient['PatientMaster']['ipid'];
		        }
		        $client_ipids[]  = $v_patient['PatientMaster']['ipid'];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['name'] = $v_patient['PatientMaster']['last_name'].', '.$v_patient['PatientMaster']['first_name'];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['epid'] = $v_patient['epid'];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['epid'] = $v_patient['epid'];
		        $patient_details[$v_patient['PatientMaster']['ipid']]['isdischarged'] = $v_patient['PatientMaster']['isdischarged'];
		        
		    }
		}
		

		
		if(empty($active_ipids)){
		    $active_ipids[] = "XXXXXX";
		}
		if(empty($client_ipids)){
		    $client_ipids[] = "XXXXXX";
		}
				
	    //get existing patients
		$voluntary_workers_ids = array();
		if($_REQUEST['workerid']){
    	    $voluntary_workers_ids = array($_REQUEST['workerid']);
		}
	    $voluntary_workers = new Voluntaryworkers();
	    $patient_voluntary_workers = new PatientVoluntaryworkers();
	    
	    $parent2child = array();
	    $worker2activepatients = array();
	    if(!empty($voluntary_workers_ids)){
    	    
    	    
    	    $parent2child = $voluntary_workers->parent2child_workers ($clientid,$voluntary_workers_ids);
    	    //var_dump($voluntary_workers_ids);exit();
    	    if(!empty($parent2child)){
        	    $worker2activepatients = $patient_voluntary_workers->get_workers2patients($parent2child['vw_ids'], $active_ipids,true);
    	    }
	    }

	    if($this->userid == "338"){
// 	        print_R($_REQUEST['workerid']);
// 	        print_R($voluntary_workers_ids);
// 	        exit;
	    }
	    
	    $kr = 0;
	    foreach($worker2activepatients as $vwid=>$patipid){
	        $patient_ipids[] = $patipid['ipid'];
	    }
	    if(empty($patient_ipids)){
	        $patient_ipids[] = "XXXXXX";
	    }
	    
	      
	    //get  patients
	    $patietns_dis = Doctrine_Query::create()
	    ->select("*")
	    ->from('PatientDischarge')
	    ->where('isdelete = "0"')
	    ->andWhereIn('ipid',$patient_ipids);
	    $discharge_patients = $patietns_dis->fetchArray();
	    
	    foreach($discharge_patients as $k=>$pdis){
	        $discgharge_date[$pdis['ipid']] = date('d.m.Y',strtotime($pdis['discharge_date']));
	    }
	    
	    $kr= 0;
	    foreach($worker2activepatients as $vwid=>$patipid){
	        $pat2master[ $parent2child['vwid2parent'][$vwid] ][ $kr ]['entry_id'] = $patipid['id'];
	        $pat2master[ $parent2child['vwid2parent'][$vwid] ][ $kr ]['vwid'] = $patipid['vwid'];
	        $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['epid'] = $patient_details[$patipid['ipid']]['epid'];
	        $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['patient_name'] = $patient_details[$patipid['ipid']]['name'];
	        if($patipid['start_date'] != "0000-00-00 00:00:00"){
	            $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['start'] = date('d.m.Y',strtotime($patipid['start_date']));
	        } else{
	            $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['start'] = date('d.m.Y',strtotime($patipid['create_date']));
	        }
	        
	        
	        if($patipid['end_date'] != "0000-00-00 00:00:00"){
	            $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['end'] = date('d.m.Y',strtotime($patipid['end_date']));
	        } else{
	            
	            if($patient_details[$patipid['ipid']]['isdischarged'] == "1"){
	                $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['end'] = date('d.m.Y',strtotime($discgharge_date[$patipid['ipid']]));
	            } else{
	                $pat2master[$parent2child['vwid2parent'][$vwid]][$kr]['end'] = "";
	            }
	        }
	        
	        $kr++;
	    }
	        
	    
	    if ($yearassg !== null)
	    {
	        $pat2master[$workerid] = array_filter($pat2master[$workerid], function($var) use($yearassg) {
	        //    return ((date('Y', strtotime($var['start'])) == $yearassg) || (date('Y', strtotime($var['end'])) == $yearassg ) || (($yearassg>=date('Y', strtotime($var['start'])) && $yearassg<=date('Y', strtotime($var['end']))))); 
	            // TODO-2780 ISPC: Assigned volunteer patients by year - Lore 06.01.2020
	            return ((date('Y', strtotime($var['start'])) == $yearassg) || (date('Y', strtotime($var['end'])) == $yearassg ) || ( ($yearassg>= date('Y', strtotime($var['start'])) && (!empty($var['end']) ? $yearassg<= date('Y', strtotime($var['end'])) : "1" )) ) );
	        });
	    }
	    //var_dump($pat2master);exit();
	    

	    $fdoclimit = array();
	    foreach($pat2master[$workerid] as $kvdata=>$vwdata)
	    {
	        $fdoclimit[$kvdata] = $vwdata;
	    }
	    
	    $full_count  = count($fdoclimit);
	    
	    if(trim($search_value) != "")
	    {
	        $regexp = trim($search_value);
	        Pms_CommonData::value_patternation($regexp);
	        
	        foreach($columns_search_array as $ks=>$vs)
	        {
	            $pairs[$vs] = trim(str_replace('\\', '',$regexp));
	        }
	        //var_dump($pairs);
	        $fdocsearch = array();
	        foreach ($fdoclimit as $skey => $sval) {
	            foreach ($pairs as $pkey => $pval) {
	                if($pkey == 'date')
	                {
	                    $sval[$pkey] = date('d.m.Y H:i', strtotime($sval[$pkey]));
	                }
	                
	                $pval_arr = explode('|', $pval);
	                
	                foreach($pval_arr as $kpval=>$vpval)
	                {
	                    if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) {
	                        $fdocsearch[$skey] = $sval;
	                        break;
	                    }
	                }
	                
	            }
	        }
	        
	        
	        	        
	        $fdoclimit = $fdocsearch;
	    }
	    $filter_count  = count($fdoclimit);
	    
	    if($order_column != "")
	    {
	        $sort_col = array();
	        foreach ($fdoclimit as $key=> $row)
	        {
	            if($order_column == '0')
	            {
	                $sort_col[$key] = strtotime($row[$columns_array[$order_column]]);
	            }
	            else
	            {
	                $row[$columns_array[$order_column].'_tr'] = mb_strtolower($row[$columns_array[$order_column]], 'UTF-8');
	                $fdoclimit[$key] = $row;
	                $sort_col[$key] = $row[$columns_array[$order_column].'_tr'];
	            }
	        }
	        if($order_dir == 'desc')
	        {
	            $dir = SORT_DESC;
	        }
	        else
	        {
	            $dir = SORT_ASC;
	        }
	        array_multisort($sort_col, $dir, $fdoclimit);
	        
	        $keyw = $columns_array[$order_column].'_tr';
	        array_walk($fdoclimit, function (&$v) use ($keyw) {
	            unset($v[$keyw]);
	        });
	    }
	    
	    if($limit != "")
	    {
	        $fdoclimit = array_slice($fdoclimit, $offset, $limit, true);
	    }
	    $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit);
	    
	    $report_ids = array();
	    $fdoclimit_arr = array();
	    $nr = 1;
	    foreach ($fdoclimit as $key => $report)
	    {
	        $fdoclimit_arr[$report['id']] = $report;
	        $report_ids[] = $report['id'];
	        $nr++;
	    }
	    
	    $row_id = 0;
	    $link = "";
	    
	    $resulted_data = array();
	    //foreach($fdoclimit_arr as $report_id =>$vwpatient)     
	    foreach($pat2master[$workerid] as $report_id =>$vwpatient)
	    {
	        $link = '%s';
	        
	        $resulted_data[$row_id]['start_association_date'] = '<input type="text" name="patient_vw['. $vwpatient['entry_id'] . '][start]"  id="patient_assign_start_['. $vwpatient['entry_id'] . ']" data-rowid="'.$vwpatient['entry_id'].'" value="'. $vwpatient['start'] .'"  class="assign_patient_date" />';     
	        $resulted_data[$row_id]['end_association_date']   = '<input type="text" name="patient_vw['. $vwpatient['entry_id'] . '][end]"   id="patient_assign_end_['. $vwpatient['entry_id'] . ']" data-rowid="'.$vwpatient['entry_id'].'"  value="'. $vwpatient['end'].'"  class="assign_patient_date" /> ';
	        $resulted_data[$row_id]['end_association_date']  .= '<input type="hidden" name="patient_vw['.$vwpatient['entry_id']. '][vwid]"  value="'.$vwpatient['vwid'].'"> ';
	        $resulted_data[$row_id]['end_association_date']  .= '<input type="hidden" name="patient_vw['.$vwpatient['entry_id']. '][custom]" value="0" >';
	        $resulted_data[$row_id]['epid'] = $vwpatient['epid'];
            $resulted_data[$row_id]['epid'] .= '<input type="hidden" name="patient_vw['.$vwpatient['entry_id'].'][patient_epid]"  value="'.$vwpatient['epid'].'">';
	        $resulted_data[$row_id]['patientname'] = $vwpatient['patient_name'];
	        
	        $resulted_data[$row_id]['actions'] = '<a href="javascript:void(0)" class="delete_existing_row" rel="'. $vwpatient['entry_id'] . '" id="delete_'. $vwpatient['entry_id'] . '"><img src="' .RES_FILE_PATH . '/images/action_delete.png" /></a>';
            $row_id++;
	    }
	    
	    $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
	    $response['recordsTotal'] = $full_count;
	    $response['recordsFiltered'] = $filter_count; // ??
	    $response['data'] = $resulted_data;
	    
	    
	    $this->_helper->json->sendJson($response);
	}
	
	public function vwactivityAction(){
	    

	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if ($logininfo->usertype != 'SA') {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);

	    $activityarr = Doctrine_Query::create()
	    ->select('*, concat(vw_id,"-",clientid,"-",activity) as indent' )
	    ->from('VoluntaryworkersActivities')
	    ->where('clientid= ?', $this->clientid)
	    ->fetchArray();
	    
	     $activities = array();
	     foreach($activityarr as $k=>$val){
	         if($val["isdelete"] == 0 ){
	             $activities[$val['vw_id']]['active'][] = $val['indent']; 
	         }
	     }
	     
	     $ids4reaftivation = array();
	     foreach($activityarr as $k=>$val){
	         if(
	             !in_array($val['indent'], $activities[$val['vw_id']]['active'])
	             && !in_array($val['indent'], $activities[$val['vw_id']]['inactive'])
	            
	             ){
	             $activities[$val['vw_id']]['inactive'][] = $val['indent']; 
	             $ids4reaftivation[] = $val['id'];
	         }
	     }
	     $str = implode(', ',$ids4reaftivation);

	     echo "<pre>";
	     print_r($activities);
	     echo "<br/>";
	     print_r(count($ids4reaftivation));
	     echo "<br/>";
	     print_r($str );
	     exit;
	    
	}
	
	

	//ISPC-2401 pct6.7
	private function _voluntaryworkerscoloraliases_GatherDetails( $id = null, $blockname)
	{
	    $table = VoluntaryworkersColorAliasesTable;
	
	    if($id)
	    {
	        $saved_formular = $table::getInstance()->findOneBy('id', $id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD);
	    }
	    if(!$saved_formular)
	    {
	        $saved_formular = $table::getInstance()->getFieldNames();
	         
	        foreach($saved_formular as $kcol=>$vcol)
	        {
	            $saved_formular_final[$vcol]['colprop'] = $table::getInstance()->getDefinitionOf($vcol);
	            $saved_formular_final[$kcol]['value'] = null;
	        }
	    }
	    else
	    {
	        foreach($saved_formular as $kcol=>$vcol)
	        {
	            $saved_formular_final[$kcol]['colprop'] = $table::getInstance()->getDefinitionOf($kcol);
	            $saved_formular_final[$kcol]['value'] = $vcol;
	        }
	    }
	    return $saved_formular_final;
	}
	
	//ISPC-2401 pct6.7
	public function voluntaryworkerscoloraliasesAction(){
	     
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    //$clientid = $logininfo->clientid;
	    
	    // get associated clients of current clientid START
	    $connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
	    if($connected_client){
	        $clientid = $connected_client;
	    } else{
	        $clientid = $logininfo->clientid;
	    }
	    
	    $saved_colors = VoluntaryworkersColorAliasesTable::findAllVoluntaryworkerscoloraliases($clientid);
	    $saved_colors_ids = array_map(function($plan) {
	        return $plan['color'];
	    }, $saved_colors);
	         
	        // if has modules 190 has can use/rename 7 colors... if not only 4
	        $all_colors = VoluntaryworkersColorAliasesTable::get_coloralias_array($clientid);
	
	        $unset_colors = array();
	         
	        foreach($all_colors as $key=>$val)
	        {
	            if(!in_array($key, $saved_colors_ids))
	            {
	                $unset_colors[$key] = $val;
	            }
	        }
	        if(empty($unset_colors))
	        {
	            $this->view->dontShow = true;
	        }
	         
	        if($_REQUEST['action'] == 'delete')
	        {
	            $mod = Doctrine::getTable('VoluntaryworkersColorAliases')->find($_REQUEST['id']);
	            $mod->delete();
	        }
	         
	        //populate the datatables
	        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
	             
	            if($_REQUEST['settingstable'] == 'voluntaryworkers_color_aliases')
	            {
	                 
	                $this->getvoluntaryworkerscoloraliases();
	            }
	        }
	         
	}
	//ISPC-2401
	private function getvoluntaryworkerscoloraliases()
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
// 	    $clientid = $logininfo->clientid;
	     
	    $connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
	    if($connected_client){
	        $clientid = $connected_client;
	    } else{
	        $clientid = $logininfo->clientid;
	    }
	    
	    
	    
	    if(!$_REQUEST['length'])
	    {
	        $_REQUEST['length'] = "10";
	    }
	     
	    $limit = $_REQUEST['length'];
	    $offset = $_REQUEST['start'];
	    $search_value = $_REQUEST['search']['value'];
	     
	    $columns_array = array(
	        "0" => "color",
	        "1" => "colorname"
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
	
	    $fdoclimit = VoluntaryworkersColorAliasesTable::findAllVoluntaryworkerscoloraliases($clientid);
	     
	    $full_count = count($fdoclimit);
	     
	    if(trim($search_value) != "")
	    {
	        $regexp = trim($search_value);
	        Pms_CommonData::value_patternation($regexp);
	         
	        foreach($columns_search_array as $ks=>$vs)
	        {
	            $pairs[$vs] = trim(str_replace('\\', '',$regexp));
	             
	        }
	        // var_dump($pairs);
	        $fdocsearch = array();
	        foreach ($fdoclimit as $skey => $sval) {
	            foreach ($pairs as $pkey => $pval) {
	                if($pkey == 'create_date')
	                {
	                    $sval[$pkey] = date('d.m.Y H:i', strtotime($sval[$pkey]));
	                }
	                 
	                $pval_arr = explode('|', $pval);
	                 
	                foreach($pval_arr as $kpval=>$vpval)
	                {
	                    if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) {
	                        $fdocsearch[$skey] = $sval;
	                        break;
	                    }
	                }
	                 
	            }
	        }
	         
	         
	        $fdoclimit = $fdocsearch;
	    }
	     
	
	    $filter_count  = count($fdoclimit);
	     
	    if($order_column != '')
	    {
	        $sort_col = array();
	        foreach ($fdoclimit as $key=> $row)
	        {
	             
	            $row[$columns_array[$order_column].'_tr'] = mb_strtolower($row[$columns_array[$order_column]], 'UTF-8');
	            $fdoclimit[$key] = $row;
	            $sort_col[$key] = $row[$columns_array[$order_column].'_tr'];
	             
	        }
	        if($order_dir == 'desc')
	        {
	            $dir = SORT_DESC;
	        }
	        else
	        {
	            $dir = SORT_ASC;
	        }
	        array_multisort($sort_col, $dir, $fdoclimit);
	         
	        $keyw = $columns_array[$order_column].'_tr';
	        array_walk($fdoclimit, function (&$v) use ($keyw) {
	            unset($v[$keyw]);
	        });
	    }
	     
	    if($limit != "")
	    {
	        $fdoclimit = array_slice($fdoclimit, $offset, $limit, true);
	    }
	    $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit);
	
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
	     
	    $all_colors = VoluntaryworkersColorAliasesTable::get_coloralias_array($clientid);
	     
	    foreach($fdoclimit_arr as $report_id =>$mdata)
	    {
	        $link = '%s ';
	         
	        $resulted_data[$row_id]['color'] = sprintf($link, $all_colors[$mdata['color']]);
	        $resulted_data[$row_id]['colorname'] = sprintf($link, $mdata['colorname']);
	        $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'voluntaryworkers/addvoluntaryworkerscoloraliases?id='.$mdata['id'].'&profile=coloralias"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" profile="coloralias" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
	         
	        $row_id++;
	    }
	    //print_R($fdoclimit_arr);
	    $response['draw'] = (int)$_REQUEST['draw']; //? get the sent draw from data table
	    $response['recordsTotal'] = $full_count;
	    $response['recordsFiltered'] = $filter_count; // ??
	    $response['data'] = $resulted_data;
	     
	    $this->_helper->json->sendJson($response);
	}
	
	

	public function addvoluntaryworkerscoloraliasesAction()
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
	     
	    if($_REQUEST['profile'])
	    {
	        $blockname = 'VOLUNTARYWORKERSCOLORALIASES';
	         
	        $saved_values = $this->_voluntaryworkerscoloraliases_GatherDetails($id, $blockname);
	    }
	     
	    $form = new Application_Form_VoluntaryworkersColorAliases(array(
	        '_block_name'           => $blockname
	    ));
	     
	    $form->create_form_addvolunatryworkerscoloraliases($saved_values);
	     
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
	        if ($form->isValid($this->getRequest()->getPost())) {
	            $savedprintsettings  = $form->save_form_VoluntaryworkersColorAliases($_POST);
	        }
	         
	        if($_POST['id'])
	        {
	            if ($form->isValid($this->getRequest()->getPost())) {
	                $this->_redirect(APP_BASE . "voluntaryworkers/voluntaryworkerscoloraliases");
	            }
	            $this->_helper->flashMessenger->addMessage( $this->translate('only number are accepted'),  'ErrorMessages');
	
	            if (!$form->isValid($this->getRequest()->getPost()))
	            {
	                $this->_redirect(APP_BASE . "voluntaryworkers/addvoluntaryworkerscoloraliases?id=".$_POST['id']."&profile=coloralias");
	            }
	        }
	        else
	        {
	            switch ($_POST['block_name'])
	            {
	                case 'VOLUNTARYWORKERSCOLORALIASES':
	                    if($_POST['unset_color_nr'] == '1')
	                    {
	                        if ($form->isValid($this->getRequest()->getPost())) {
	                            $this->_redirect(APP_BASE . "voluntaryworkers/voluntaryworkerscoloraliases");
	                        }
	                        else
	                        {
	                            $this->_helper->flashMessenger->addMessage( $this->translate('only number are accepted'),  'ErrorMessages');
	                            $this->_redirect(APP_BASE . "voluntaryworkers/addvoluntaryworkerscoloraliases?profile=coloralias");
	                        }
	                    }
	                    else
	                    {
	                        if (!$form->isValid($this->getRequest()->getPost())) {
	                            $this->_helper->flashMessenger->addMessage( $this->translate('only number are accepted'),  'ErrorMessages');
	                        }
	                        $this->_redirect(APP_BASE . "voluntaryworkers/addvoluntaryworkerscoloraliases?profile=coloralias");
	                    }
	                    break;
	            }
	        }
	         
	    }
	}

	/*
	 * ISPC-2401 pct.5 24.09.2019
	 * @auth Ancuta & Lore
	 * edited on 25.11.2019
	 */
	public function vwcourseAction()
	{
	    $this->_helper->layout->setLayout('layout_ajax');
	    $clientid = $this->clientid;
	    
	    
	    $all_users = Pms_CommonData::get_client_users($clientid, true);
	    $all_users = $this->array_sort($all_users, 'last_name', SORT_ASC);
        //ISPC-2908,Elena,21.05.2021
	    
        $this->view->usersnewtodos = $this->get_nice_name_multiselect();
        $flat_todo_users_selectbox = array();
        foreach($this->view->usersnewtodos as $k=>$row_user) {
            if ( ! is_array( $row_user)) { $row_user = array($k => $row_user); }
            $flat_todo_users_selectbox = array_merge($flat_todo_users_selectbox, $row_user );
        }
        $this->view->usersnewtodos_flat = $flat_todo_users_selectbox;
        //print_r($this->view->usersnewtodos_flat);

	    $all_users_array = array();
	    foreach($all_users as $keyu => $user)
	    {
	        $all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
	    }
	    $this->view->allusers = $all_users_array;
	    
	    
      
        $vw_id = $_REQUEST['id'];
	    $sh_array = array();

	    if(!empty($_REQUEST['filter'])){
	       $sh_array = explode(',',$_REQUEST['filter']);
	    } 
	    
	    if ($_REQUEST['id'] > 0){
	        //($_GET['id'], false, $chvals = 0, $start = false, $end = false, $sort_direction = 'DESC', $sort_field = 'course_date', $offset = '0', $limit = false, $page = false, $only_count = false, $first_limit = false);
	        $vw_course_full = VoluntaryworkersCourse::getCourseData($vw_id,$sh_array , $chvals = 0, $start = false, $end = false, $sort_direction = 'DESC'); //ISPC-2401 pct 4. Lore
	        
	        $this->view->vw_course = $vw_course_full;
	        
	    }
	    
	    $this->view->act = "voluntaryworkers/vwcourse?id=" . $_REQUEST['id'];
	    	    
	        
	}
	
	//ISPC-2618 Carmen 30.07.2020
	public function validatebankaccountAction()
	{
	$this->_helper->viewRenderer->setNoRender();
	$this->_helper->layout->setLayout('layout_ajax');	
	
	$post = $_REQUEST;
	$error = 0;
	if((strlen($post['bank_name']) > 0 ||  strlen($post['iban']) > 0 ||  strlen($post['bic']) > 0  ||  strlen($post['account_holder']) > 0 )){
		
		//iban bic validate
		if(strlen(trim($post['iban'])) > 0)
		{
			$iban_Validator = new Pms_SepaIbanValidator(array(
					'allow_non_sepa'=>false ,
					'iban'=>$post['iban']));
				
			$iban_is_valid = false;
			if ( !$iban_is_valid =  $iban_Validator->isValid() ){
				//$this->error_message['iban'] = $this->view->translate('IBAN validation failed');
				$error = 13;
			}
				
			if ( $iban_is_valid && strlen(trim($post['bic'])) > 0 && ! $iban_Validator->bic_isValid($post['bic']) ){
				//$this->error_message['bic'] = $this->view->translate('BIC validation failed');
				$error = 13;
			}
		}			
			
	}
	
	echo $error;
	exit;
	}
	//--
	
	
	

	
	
	
	
	/**
	 * ISPC-2609 Ancuta 07.09.2020
	 */
	public function printjobdeleteAction(){
	    
	    $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->setLayout('layout_ajax');
	    
	    
	    if ( !empty($_REQUEST['delete']) && !empty($_REQUEST['id']) && $_REQUEST['delete'] == "1" )
	    {
	        $pjb_obj = Doctrine::getTable('PrintJobsBulk')->find($_REQUEST['id']);
	        if($pjb_obj){
	            $pjb_obj->delete();
	        }
	        
	    }
	    
	}
	
	
	/**
	 * ISPC-2609 Ancuta 07.09.2020
	 */
	public function printjobclearAction(){
	    
	    $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->setLayout('layout_ajax');
	    
	    
	    if ( !empty($_REQUEST['user']) && !empty($_REQUEST['client']) && !empty($_REQUEST['invoice_type']) )
	    {
	        //find all - and delete all
	        
	        
	        $fdoc1 = Doctrine_Query::create();
	        $fdoc1->select('*');
	        $fdoc1->from('PrintJobsBulk');
	        $fdoc1->where("clientid = ?", $_REQUEST['client']);
	        $fdoc1->andWhere("user = ?", $_REQUEST['user']);
	        $fdoc1->andWhere("print_controller = ?", $_REQUEST['print_controller']);
	        $fdocarray = $fdoc1->fetchArray();
	        
	        if(!empty($fdocarray)){
	            foreach($fdocarray as $job_k=>$job_data){
	                
	                $pjb_obj = Doctrine::getTable('PrintJobsBulk')->find($job_data['id']);
	                if($pjb_obj){
	                    $pjb_obj->delete();
	                }
	            }
	        }
	        
	    }
	    
	}
	
	
	/**
	 * ISPC-2609 Ancuta 07.09.2020
	 */
	public function printjobinfoAction(){
	    $clientid = $this->clientid;
	    $userid = $this->userid;
	    
	    $user = new User();
	    $user_details = array();
	    $user_details = $user->get_client_users($clientid,1,true);
	    
	    //populate the datatables
	    if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost() && !empty($_REQUEST['print_controller'])) {
	        $this->_helper->layout()->disableLayout();
	        $this->_helper->viewRenderer->setNoRender(true);
	        if(!$_REQUEST['length']){
	            $_REQUEST['length'] = "25";
	        }
	        $limit = (int)$_REQUEST['length'];
	        $offset = (int)$_REQUEST['start'];
	        $search_value = addslashes($_REQUEST['search']['value']);
	        
	        $columns_array = array(
	            "0" => "user"
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
	        $fdoc1->from('PrintJobsBulk');
	        $fdoc1->where("clientid = ?", $clientid);
	        $fdoc1->andWhere("user = ?", $userid);
	        $fdoc1->andWhere("print_controller = ?", $_REQUEST['print_controller']);
	        //$fdoc1->andWhere('DATE(create_date) = ? ', date('Y-m-d'));
	        $fdocarray = $fdoc1->fetchArray();
	        $full_count  = $fdocarray[0]['count'];
	        
	        // ########################################
	        // #####  Query for details ###############
	        $fdoc1->select('*');
	        $fdoc1->orderBy('create_date DESC');
	        $fdoc1->limit($limit);
	        $fdoc1->offset($offset);
	        
	        $fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
	        
	        
	        
	        
	        $qs = Doctrine_Query::create();
	        $qs->select('*');
	        $qs->from('PrintJobsBulk');
	        $qs->where('status ="active" ');
	        $qs->orderBy('create_date ASC');
	        $act_result = $qs->fetchArray();
	        
	        $pnr = 0;
	        foreach($act_result as $pk=>$pactive){
	            $pnr++;
	            $qnr[$pactive['id']] = $pnr;
	        }
	        
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
	        
	        $row_id = 0 ;
	        foreach($fdoclimit_arr as $k=>$data){
	            if($data['status'] == 'active'  ) {
	                $resulted_data[$row_id]['queue_nr'] = $qnr[$data['id']];
	            } else{
	                $resulted_data[$row_id]['queue_nr'] =  '--';
	            }
	            
	            $resulted_data[$row_id]['print_user'] = $user_details[$data['user']];
	            $resulted_data[$row_id]['print_status'] = self::translate('ps_'.$data['status']);
	            $data['clientid_enc']= Pms_Uuid::encrypt($data['clientid']);
	            
	            if($data['status'] == 'completed' && $data['client_file_id'] != 0){
	                
	                $resulted_data[$row_id]['print_link'] = '<a href="'.APP_BASE.'misc/clientfile?doc_id='.$data['client_file_id'].'&cid='.$data['clientid_enc'].'">  <img border="0" src="'.RES_FILE_PATH.'/images/file_download.png" />  </a>';
	                
	            } else{
	                $resulted_data[$row_id]['print_link'] = '--';
	            }
	            $resulted_data[$row_id]['print_date'] = date('d.m.Y H:i',strtotime($data['create_date']));
	            
	            
	            $resulted_data[$row_id]['actions'] = '<a href="javascript:void(0);"  class="job_delete" rel="'.$data['id'].'" id="job_delete_'.$data['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
	            $row_id++;
	        }
	        
	        $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
	        $response['recordsTotal'] = $full_count;
	        $response['recordsFiltered'] = $filter_count; // ??
	        $response['data'] = $resulted_data;
	        
	        $this->_helper->json->sendJson($response);
	    }
	    
	}
	
	
	
	/**
	 * ISPC-2609
	 */
	public function __StartPrintJobs(){
	    $appInfo = Zend_Registry::get('appInfo');
	    $app_path  = 	isset($appInfo['appCronPath']) && !empty($appInfo['appCronPath']) ? $appInfo['appCronPath'] : false;
	    
	    $function_path = $app_path.'/cron/processprintjobs';
	    popen('curl -s '.$function_path.' &', 'r');
	}
	
    /**
     * ISPC-2908,Elena,21.05.2021
     * taken from PatientcourseController
     * it is different from the method in Pms_CommonData
     * @todo there are too much methods with this name in project, maybe we have to think about one method for all cases - Elena, 21.05.2021
     * from
     * this fn is used for display and for saved data...
     * if pseudogroup has now keine benutzer assidned to it, it will not be displayed in the selectbox
     * BUTTT it will also make a blank_space in the allready saved W
     * @todo fix this
     * Jul 17, 2017 @claudiu
     *
     * @return multitype:NULL multitype:string  multitype:Ambigous <string, NULL, string, Ambigous <string, Zend_View_Helper_Translate>>  multitype:Ambigous <>
     */
    private function get_nice_name_multiselect ()
    {

        $selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();

        $todousersarr = array(
            "0" => $this->view->translate('select'),
            $selectbox_separator_string['all'] => $this->view->translate('all')
        );

        $usergroup = new Usergroup();
        $todogroups = $usergroup->getClientGroups($this->logininfo->clientid);
        $grouparraytodo = array();
        foreach ($todogroups as $group)
        {
            $grouparraytodo[$selectbox_separator_string['group'] .  $group['id']] = trim($group['groupname']);
        }

        if (isset( $this->{'_patientMasterData'}['User'])){
            $userarray = $this->{'_patientMasterData'}['User'];
        } else {
            $users = new User();
            $userarray = $users->getUserByClientid($this->logininfo->clientid);
        }


        User::beautifyName($userarray);

        $userarraytodo = array();
        foreach ($userarray as $user)
        {
            if($user['isactive'] == "0"){
//                 $userarraytodo[$selectbox_separator_string['user'] . $user['id']] = $user['nice_name'];
                $userarraytodo[$selectbox_separator_string['user'] . $user['id']] = str_replace(array("'",'"'),array(" "," "),$user['nice_name']);
            }
        }

        //asort($userarraytodo);//ISPC-2878,Elena,12.04.2021 //sorting by last name AND NOT by nice name with title wanted - elena
        asort($grouparraytodo);

        $todousersarr[$this->view->translate('group_name')] = $grouparraytodo;


        $user_pseudo =  new UserPseudoGroup();
        $user_ps =  $user_pseudo->get_pseudogroups_for_todo($this->logininfo->clientid);
        $pseudogrouparraytodo = array();
        if ( ! empty ($user_ps)) {

            //pseudogroup must have users in order to display
            $user_ps_ids =  array_column($user_ps, 'id');
            $user_pseudo_users = new PseudoGroupUsers();
            $users_in_pseudogroups = $user_pseudo_users->get_users_by_groups($user_ps_ids);

            foreach($user_ps as $row) {
                if ( ! empty($users_in_pseudogroups[$row['id']]))
//                     $pseudogrouparraytodo[$selectbox_separator_string['pseudogroup'] . $row['id']] = $row['servicesname'];
                    $pseudogrouparraytodo[$selectbox_separator_string['pseudogroup'] . $row['id']] = str_replace(array("'",'"'),array(" "," "),$row['servicesname']);
            }

            $todousersarr[$this->view->translate('liste_user_pseudo_group')] = $pseudogrouparraytodo;
        }
        $todousersarr[$this->view->translate('users')] = $userarraytodo;
        return $todousersarr;
    }
	
	
	
	
}

?>