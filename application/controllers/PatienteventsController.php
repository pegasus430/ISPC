<?php
/**
 * 
 * @author  Apr 15, 2020  Ancuta
 * ISPC-2517 
 * #ISPC-2512PatientCharts // Maria:: Migration ISPC to CISPC 08.08.2020
 *
 */
class PatienteventsController extends Pms_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->setActionsWithJsFile([
            "modal", // ISPC-2517
            "management" // ISPC-2517
        ]);

        // phtml is the default for zf1 ... but on bootstrap you manualy set html :(
        $this->getHelper('viewRenderer')->setViewSuffix('phtml');
    }

    public function clientmanagementAction()
    {
        if ($this->getRequest()->isPost()) {
            // update all, set as delete
            $update = Doctrine_Query::create()->update("ClientEvents")
                ->set('isdelete', '?', 1)
                ->where('clientid = ?', $this->logininfo->clientid)
                ->execute();

            foreach ($_POST['canaccess'] as $event_name => $value) {
                if ($value == '1') {
                    $res[] = array(
                        'event_name' => $event_name,
                        'clientid' => $this->logininfo->clientid,
                        'canaccess' => '1',
                        'show_in_chart' => isset($_POST['show_in_chart'][$event_name]) ? '1' : '0',         //ISPC-2841 Lore 22.03.2021
                    );
                }
            }

            //ISPC-2841 Lore 22.03.2021
            foreach ($_POST['show_in_chart'] as $event_name => $value) {
                if ($value == '1') {
                    if(!isset($_POST['canaccess'][$event_name])){
                        $res[] = array(
                            'event_name' => $event_name,
                            'clientid' => $this->logininfo->clientid,
                            'canaccess' => '0',
                            'show_in_chart' => '1'
                        );
                    }
                }
            }
            //.
            
            // insert the shortcut for all the clients
            if (! empty($res)) {
                $collection = new Doctrine_Collection('ClientEvents');
                $collection->fromArray($res);
                $collection->save();
            }

            $this->_redirect(APP_BASE . 'patientevents/clientmanagement?flg=suc');
        }

        $permissions = array();
        // get all default events
        $default_events = Pms_CommonData::available_events();
        $permissions['events'] = $default_events;

        // get saved client events
        $client_events = Doctrine_Query::create()->select("*")
            ->from("ClientEvents")
            ->where("clientid = ?", intval($this->logininfo->clientid))
            //->andWhere('canaccess = 1')
            ->andWhere('isdelete = 0')          //ISPC-2841 Lore 22.03.2021
            ->fetchArray();
           
        //ISPC-2841 Lore 22.03.2021
        foreach($client_events as $key=> $vals){
            if($vals['canaccess'] == 1){
                $permissions['canaccess'][] = $vals['event_name'] ;
            }
            if($vals['show_in_chart'] == 1){
                $permissions['show_in_chart'][] = $vals['event_name'] ;
            }
        }
        //.

        $allowed_events = array();
        $allowed_events = array_column($client_events, 'event_name');
        $permissions['allowed_events'] = $allowed_events;

        $this->view->management = $permissions;
    }

    public function managementAction()
    {
        if ($this->getRequest()->isPost()) {

            // delete all of client
            // update all, set as delete
            $update = Doctrine_Query::create()->update("ClientEvents2groups")
                ->set('isdelete', '?', 1)
                ->where('clientid = ?', $this->logininfo->clientid)
                ->execute();

            // insert new
            $new_gr_ev = array();
            foreach ($_POST['events']['has_access'] as $event_name => $access2gr) {
                if ($event_name == 'contact_form_items') {
                    foreach ($access2gr as $ftype => $accessft2gr) {
                        {
                            foreach ($accessft2gr as $group_id => $gr_acess) {
                                $new_gr_ev[] = array(
                                    'clientid' => $this->logininfo->clientid,
                                    'event_name' => $event_name,
                                    'form_type' => $ftype,
                                    'master_group_id' => $group_id,
                                    'sort_order' => $_POST['events']['order'][$event_name][$ftype]
                                );
                            }
                        }
                    }
                } else {

                    foreach ($access2gr as $group => $access) {
                        $new_gr_ev[] = array(
                            'clientid' => $this->logininfo->clientid,
                            'event_name' => $event_name,
                            'form_type' => 0,
                            'master_group_id' => $group,
                            'sort_order' => $_POST['events']['order'][$event_name]
                        );
                    }
                }
            }
            
            if(!empty($new_gr_ev)){
                $collection = new Doctrine_Collection('ClientEvents2groups');
                $collection->fromArray($new_gr_ev);
                $collection->save();
            }
        }

        // get all contact forms of client
        $form_types = new FormTypes();
        $form_type_array = $form_types->get_form_types($this->logininfo->clientid);
        $types = array();
        foreach ($form_type_array as $k_type => $v_type) {
            $types[$v_type['id']] = $v_type;
        }
        $permissions['client_contact_forms_items'] = $types;
        $this->view->client_contact_forms_items = $types;
        
        
        
        
        $permissions = array();

        // get all master groups
        $GroupMaster = new GroupMaster();
        $master_groups = $GroupMaster->getGroupMaster();
        $permissions['master_groups'] = $master_groups;
        $this->view->master_groups = $master_groups;
        // get client groups
        
        
        
        // get get permissions of contactforms - for group master
        $types_perms = new FormTypePermissions();
        $groups2types_permissions = $types_perms->get_client_permissions($this->logininfo->clientid);
        // $group_perms[$v_perm['groupid']][$v_perm['type']] = $v_perm['value'];
        $this->view->groups2types_permissions = $groups2types_permissions;
        $permissions['groups2types_permissions'] = $groups2types_permissions;
        
        
        // get all default events
        $default_events = Pms_CommonData::available_events();
        
        // get saved client events
        $client_events = Doctrine_Query::create()->select("*")
            ->from("ClientEvents")
            ->where("clientid = ?", intval($this->logininfo->clientid))
            ->andWhere('canaccess = 1')
            ->fetchArray();

        $available_events = array();
        $allowed_events = array();
        $allowed_events = array_column($client_events, 'event_name');
        foreach ($default_events as $event_name) {
            if (in_array($event_name, $allowed_events)) {
                $available_events[] = $event_name;
            }
        }

        $permissions['events'] = $available_events;
        
        $x=1;
        $display_events = array();
        foreach($available_events as $k=>$event){
            if($event != 'contact_form_items'){
                    $display_events[$x]['event_name'] = $event;
                    $display_events[$x]['form_type'] = 0;
                    $display_events[$x]['form_type_name'] = '';
                    $display_events[$x]['sort_order'] = $x;
                    $x++;
            } 
            else
            {
                foreach($types as $type_id=>$type_name){
                    $display_events[$x]['event_name'] = $event;
                    $display_events[$x]['form_type'] = $type_id;
                    $display_events[$x]['form_type_name'] = $type_name['name'];
                    $display_events[$x]['sort_order'] = $x;
                    $x++;
                }
                    
                
            }
        }
        

        // get saved values
        $saved_events  = array();
        $saved_events = Doctrine_Query::create()
        ->select('*')
        ->from('ClientEvents2groups')
        ->where('clientid= ?', $this->logininfo->clientid)
        ->andWhere('isdelete =?','0')
        ->orderBy('sort_order ASC')
        ->fetchArray();
        
        $events_permissions = array();
        if(!empty($saved_events)){
            foreach($saved_events as $kd=>$sdata){
                if($sdata['form_type']!=0){
                    $events_permissions[$sdata['event_name']][$sdata['form_type']][]  = $sdata;
                } else{
                    $events_permissions[$sdata['event_name']][]  = $sdata;
                }
            }
        }
        $display_events_final = array();
        foreach($display_events as $k=> $event_data){
            if($event_data['form_type'] != 0){
                $display_events_final[$k] = $event_data;
                $display_events_final[$k]['permissions2groups'] = array_unique(array_column($events_permissions[$event_data['event_name']][$event_data['form_type']],'master_group_id'));
                $display_events_final[$k]['order'] = !empty($events_permissions[$event_data['event_name']][$event_data['form_type']][0]['sort_order']) ? $events_permissions[$event_data['event_name']][$event_data['form_type']][0]['sort_order'] :  "1000" ;
            } else{
                $display_events_final[$k] = $event_data;
                $display_events_final[$k]['permissions2groups'] = array_unique(array_column($events_permissions[$event_data['event_name']],'master_group_id'));
                $display_events_final[$k]['order'] = !empty($events_permissions[$event_data['event_name']][0]['sort_order']) ? $events_permissions[$event_data['event_name']][0]['sort_order'] :  "1000" ;
                
            }
        }
        usort($display_events_final, array(new Pms_Sorter('order'), "_number_asc"));
        $permissions['final_events'] = $display_events_final;
        $this->view->management = $permissions;
        
    }

    public function modalAction()
    {
        $this->_helper->layout->setLayout('layout_ajax');
        // get all default events
        
        // get all contact forms of client
        $form_types = new FormTypes();
        $form_type_array = $form_types->get_form_types($this->logininfo->clientid);
        $types = array();
        foreach ($form_type_array as $k_type => $v_type) {
            $types[$v_type['id']] = $v_type;
        }
        $permissions['client_contact_forms_items'] = $types;
        $this->view->client_contact_forms_items = $types;

        
        $client_events = Doctrine_Query::create()->select("*")
        ->from("ClientEvents")
        ->where("clientid = ?", intval($this->logininfo->clientid))
        ->andWhere('canaccess = 1')
        ->fetchArray();
        
  
        // get all default events
        $default_events = Pms_CommonData::available_events();
        
        // get saved client events
        $client_events = Doctrine_Query::create()->select("*")
        ->from("ClientEvents")
        ->where("clientid = ?", intval($this->logininfo->clientid))
        ->andWhere('canaccess = 1')
        ->fetchArray();
        
        $available_events = array();
        $allowed_events = array();
        $allowed_events = array_column($client_events, 'event_name');
        foreach ($default_events as $event_name) {
            if (in_array($event_name, $allowed_events)) {
                $available_events[] = $event_name;
            }
        }
        
        $permissions['events'] = $available_events;
        
        $x=1;
        $display_events = array();
        foreach($available_events as $k=>$event){
            if($event != 'contact_form_items'){
                $display_events[$x]['event_name'] = $event;
                $display_events[$x]['form_type'] = 0;
                $display_events[$x]['form_type_name'] = '';
                $display_events[$x]['sort_order'] = $x;
                $x++;
            }
            else
            {
                foreach($types as $type_id=>$type_name){
                    $display_events[$x]['event_name'] = $event;
                    $display_events[$x]['form_type'] = $type_id;
                    $display_events[$x]['form_type_name'] = $type_name['name'];
                    $display_events[$x]['sort_order'] = $x;
                    $x++;
                }
                
                
            }
        }
        //get  master group of user
        $groupid =  $this->logininfo->groupid;
        $master_group = Usergroup::getMasterGroup($groupid);
        
        if($this->logininfo->usertype == "SA"){
            $master_group = 4;// hardcode SA to Arzt group
        }
        if(empty($master_group)){
            return; 
        }
        // get saved values
        $saved_events  = array();
        $saved_events = Doctrine_Query::create()
        ->select('*')
        ->from('ClientEvents2groups')
        ->where('clientid= ?', $this->logininfo->clientid)
        ->andWhere('master_group_id= ?', $master_group)
        ->andWhere('isdelete =?','0')
        ->orderBy('sort_order ASC')
        ->fetchArray();
        
        $events_permissions = array();
        if(!empty($saved_events)){
            foreach($saved_events as $kd=>$sdata){
                if($sdata['form_type']!=0){
                    $events_permissions[$sdata['event_name']][$sdata['form_type']][]  = $sdata;
                } else{
                    $events_permissions[$sdata['event_name']][]  = $sdata;
                }
            }
        }
        //         dd($events_permissions);
        $display_events_final = array();
        foreach($display_events as $k=> $event_data){
            if($event_data['form_type'] != 0){
                $display_events_final[$k] = $event_data;
                $display_events_final[$k]['permissions2groups'] = array_unique(array_column($events_permissions[$event_data['event_name']][$event_data['form_type']],'master_group_id'));
                $display_events_final[$k]['order'] = !empty($events_permissions[$event_data['event_name']][$event_data['form_type']][0]['sort_order']) ? $events_permissions[$event_data['event_name']][$event_data['form_type']][0]['sort_order'] :  "1000" ;
            } else{
                $display_events_final[$k] = $event_data;
                $display_events_final[$k]['permissions2groups'] = array_unique(array_column($events_permissions[$event_data['event_name']],'master_group_id'));
                $display_events_final[$k]['order'] = !empty($events_permissions[$event_data['event_name']][0]['sort_order']) ? $events_permissions[$event_data['event_name']][0]['sort_order'] :  "1000" ;
            }
        }
        
        $display_events_final_allowed = array_filter($display_events_final, function($val) 
            {
            return !empty($val['permissions2groups']);
            }
        );
        usort($display_events_final_allowed, array(new Pms_Sorter('order'), "_number_asc"));
        $permissions['final_events'] = $display_events_final_allowed;
//         dd($display_events_final_allowed);
        
        $this->view->management = $permissions;
    }
    
    
    
    public function medicationiconAction()
    {
//         $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        
        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $ipid = Pms_CommonData::getIpId($decid);
        $clientid = $this->logininfo->clientid;
        
        $color_map = array(
            'given' => 'green',
            'not_given' => 'red',
            'given_different_dosage' => 'blue',
            'not_taken_by_patient' => 'yellow',
        );
        $this->view->color_map  = $color_map;
        $this->view->color_map_js = json_encode($color_map);
        
        $patient_icons = new IconsPatient();
        $medication_icons = $patient_icons->get_patient_medication(array($ipid));
        $this->view->medication_data = $medication_icons['medication_data'][$ipid];
        
    }
    public function eventsAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        $ipid = $this->ipid;
        
        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $decid = Pms_Uuid::decrypt($_REQUEST['patid']);
            $ipid = Pms_CommonData::getIpId($decid);
            $clientid = $this->logininfo->clientid;
            
            switch($_REQUEST['action'])
            {
                case 'validate_form':
                    switch($_REQUEST['form'])
                    {
                        case 'customeventsave':
             
                        $events = $_REQUEST['custom_events'];
                        $Tr = new Zend_View_Helper_Translate();
                        
                        $total_err = 0 ;
                        $res = array();
                        $result = array();
                        foreach($events as $ev_line=>$data){
                            $err = 0;
                            // first validate start date 
                            if(empty($data['form_start_date']) || empty($data['form_start_time'])){
                                $res[$ev_line]['error']['form_start_date'] = 'Start date and time must be filled';
                                $result['error'] = $Tr->translate('Start date and time must be filled');
                                $err = 1;
                            } else {
                                $full_start_date = $data['form_start_date'].' '.$data['form_start_time'].":00";
                            
                                if(strtotime($full_start_date) > strtotime(date("d.m.Y H:i:s", time())))
                                {
                                    $res[$ev_line]['error']['form_start_date'] = $Tr->translate('err_datefuture');
                                    $result['error'] = $Tr->translate('err_datefuture');
                                    $err = 2;
                                }
                            }
                            
                            //If start date is ok, validate end date
                            if($err == 0
                                && $data['onetimeevent'] != "1"
                                && $data['isenduncertain'] != "1" ){
                            
                                if(empty($data['form_end_date']) || empty($data['form_end_time'])){
                                    $res[$ev_line]['error']['form_end_date'] = 'END date and time must be filled';
                                    $result['error'] = $Tr->translate('end date and time must be filled');
                                    $err = 3;
                                }else{
                                    
                                    $full_end_date = $data['form_end_date'].' '.$data['form_end_time'].":00";
                                    if(strtotime($full_end_date) > strtotime(date("d.m.Y H:i:s", time())))
                                    {
                                        $res[$ev_line]['error']['form_end_date'] = $Tr->translate('err_datefuture');
                                        $result['error'] = $Tr->translate('err_datefuture');
                                        $err = 4;
                                    }
                                    
                                    if(strtotime($full_start_date) > strtotime($full_end_date)){
                                        $res[$ev_line]['error']['form_end_date'] = $Tr->translate('End time must be bigger than start date');
                                        $result['error'] = $Tr->translate('End time must be bigger than start date');
                                        $err = 5;
                                    }
                                }
                            
                            }
                        
                            $total_err = $total_err+$err;
                        }
                        
                        if($total_err == 0 ){
                            $result['success'] = '1';
                            echo json_encode($result);
                            
                            exit;
                        } else {
                            $result['success'] = false;
                            echo json_encode($result);
                            exit;
                        }
                        
                        break;
                        
                        case 'positioningsave':
                        	 
                        	$events = $_REQUEST['positioning_events'];
                        	
                        	$Tr = new Zend_View_Helper_Translate();
                        
                        	$total_err = 0 ;
                        	$res = array();
                        	$result = array();
                        	foreach($events as $ev_line=>$data){
                        		$err = 0;
                        		// first validate start date
                        		if(empty($data['form_start_date']) || empty($data['form_start_time'])){
                        			$res[$ev_line]['error']['form_start_date'] = 'Start date and time must be filled';
                        			$result['error'] = $Tr->translate('Start date and time must be filled');
                        			$err = 1;
                        		} else {
                        			$full_start_date = $data['form_start_date'].' '.$data['form_start_time'].":00";
                        
                        			if(strtotime($full_start_date) > strtotime(date("d.m.Y H:i:s", time())))
                        			{
                        				$res[$ev_line]['error']['form_start_date'] = $Tr->translate('err_datefuture');
                        				$result['error'] = $Tr->translate('err_datefuture');
                        				$err = 2;
                        			}
                        		}
                        
                        		//If start date is ok, validate end date
                        		if($err == 0
                        				&& $data['isenduncertain'] != "1" ){
                        
                        					if(empty($data['form_end_date']) || empty($data['form_end_time'])){
                        						$res[$ev_line]['error']['form_end_date'] = 'END date and time must be filled';
                        						$result['error'] = $Tr->translate('End date and time must be filled');
                        						$err = 3;
                        					}else{
                       
                        						$full_end_date = $data['form_end_date'].' '.$data['form_end_time'].":00";
                        						if(strtotime($full_end_date) > strtotime(date("d.m.Y H:i:s", time())))
                        						{
                        							$res[$ev_line]['error']['form_end_date'] = $Tr->translate('err_datefuture');
                        							$result['error'] = $Tr->translate('err_datefuture');
                        							$err = 4;
                        						}
                        
                        						if(strtotime($full_start_date) > strtotime($full_end_date)){
                        							$res[$ev_line]['error']['form_end_date'] = $Tr->translate('End time must be bigger than start date');
                        							$result['error'] = $Tr->translate('End time must be bigger than start date');
                        							$err = 5;
                        						}
                        					}
                        		}
                        
                        		$total_err = $total_err+$err;
                        	}
                        
                        	if($total_err == 0 ){
                        		$result['success'] = '1';
                        		echo json_encode($result);
                        
                        		exit;
                        	} else {
                        		$result['success'] = false;
                        		echo json_encode($result);
                        		exit;
                        	}
                        
                        	break;
                        	
                        	case 'awakesleepingsave':
                        	
                        		$events = $_REQUEST['awakesleeping_events'];
                        		 
                        		$Tr = new Zend_View_Helper_Translate();
                        	
                        		$total_err = 0 ;
                        		$res = array();
                        		$result = array();
                        		foreach($events as $ev_line=>$data){
                        			$err = 0;
                        			// first validate start date
                        			if(empty($data['form_start_date']) || empty($data['form_start_time'])){
                        				$res[$ev_line]['error']['form_start_date'] = 'Start date and time must be filled';
                        				$result['error'] = $Tr->translate('Start date and time must be filled');
                        				$err = 1;
                        			} else {
                        				$full_start_date = $data['form_start_date'].' '.$data['form_start_time'].":00";
                        	
                        				if(strtotime($full_start_date) > strtotime(date("d.m.Y H:i:s", time())))
                        				{
                        					$res[$ev_line]['error']['form_start_date'] = $Tr->translate('err_datefuture');
                        					$result['error'] = $Tr->translate('err_datefuture');
                        					$err = 2;
                        				}
                        			}
                        	
                        			//If start date is ok, validate end date
                        			if($err == 0
                        					&& $data['isenduncertain'] != "1" ){
                        	
                        						if(empty($data['form_end_date']) || empty($data['form_end_time'])){
                        							$res[$ev_line]['error']['form_end_date'] = 'END date and time must be filled';
                        							$result['error'] = $Tr->translate('End date and time must be filled');
                        							$err = 3;
                        						}else{
                        							 
                        							$full_end_date = $data['form_end_date'].' '.$data['form_end_time'].":00";
                        							if(strtotime($full_end_date) > strtotime(date("d.m.Y H:i:s", time())))
                        							{
                        								$res[$ev_line]['error']['form_end_date'] = $Tr->translate('err_datefuture');
                        								$result['error'] = $Tr->translate('err_datefuture');
                        								$err = 4;
                        							}
                        	
                        							if(strtotime($full_start_date) > strtotime($full_end_date)){
                        								$res[$ev_line]['error']['form_end_date'] = $Tr->translate('End time must be bigger than start date');
                        								$result['error'] = $Tr->translate('End time must be bigger than start date');
                        								$err = 5;
                        							}
                        						}
                        			}
                        	
                        			$total_err = $total_err+$err;
                        		}
                        	
                        		if($total_err == 0 ){
                        			$result['success'] = '1';
                        			echo json_encode($result);
                        	
                        			exit;
                        		} else {
                        			$result['success'] = false;
                        			echo json_encode($result);
                        			exit;
                        		}
                        	
                        		break;
                        	
                    }
                    break;
                case 'show_form':
                    switch($_REQUEST['form'])
                    {
                        case 'dosage_interaction':
                            $form = new Application_Form_PatientDrugPlan();
                            
                            $values = $_REQUEST;
                            
                            $dosage_interaction_form = $form->create_dosage_interaction($values);
                            $this->getResponse()->setBody($dosage_interaction_form)->sendResponse();
                            
                            exit;
                            break;
                            
                        case 'dosage_interaction_bulk':
                            $form = new Application_Form_PatientDrugPlan();
                            
                            $values = $_REQUEST;
                            // get all medications - related to type
                            switch($values['medication_type']){
                                case 'M':
                                    $type = 'actual';
                                    $dosage_time = $values['dosage_time_interval'];
                                    break;
                                case 'I':
                                    $type = 'isivmed';
                                    $dosage_time = $values['dosage_time_interval'];
                                    break;
                                case 'ER':
                                    $type = 'isnutrition';
                                    $dosage_time = $values['dosage_time_interval'];
                                    break;
                                case 'N':
                                    $type = 'isbedarfs';
                                    $dosage_time = $values['dosage_time_interval'];
                                    break;
                                case 'KM':
                                    $type = 'iscrisis';
                                    $dosage_time = $values['dosage_time_interval'];
                                    break;
                                    
                                   default:
                                       
                                    break;
                            }
                            
                            if(empty($type)){
                                echo "No bulk interaction for  this type";
                                return;
                            }
                            //get time scchedule options
                            $client_med_options = MedicationOptions::client_saved_medication_options($clientid);
                            
                            
                            $timed_scheduled_medications= array();
                            $NOT_timed_scheduled_medications = array();
                            foreach($client_med_options as $mtype=>$mtime_opt){
                                if($mtime_opt['time_schedule'] == "1"){
                                    $time_blocks[]  = $mtype;
                                    $timed_scheduled_medications[]  = $mtype;
                                } else {
                                    $NOT_timed_scheduled_medications[]  = $mtype;
                                }
                            }
                            
                            if($individual_medication_time == "0"){
                                $timed_scheduled_medications = array("actual","isivmed"); // default
                                $time_blocks  = array("actual","isivmed"); // default
                            }
                            
                            foreach($timed_scheduled_medications  as $tk=>$tmed){
                                if(in_array($tmed,$NOT_timed_scheduled_medications)){
                                    unset($timed_scheduled_medications[$tk]);
                                }
                            }
                            
                            
                            
                            
                            
                            $drugplan = new PatientDrugPlan();
                            $all_data = $drugplan->get_dosage_interaction_medication($ipid,$this->logininfo->clientid, false, $type);
                            
                            $today = date('Y-m-d');
                            $today_time = date('Y-m-d H:i:s');
                            $interaction =  array();
                            if(!empty($all_data[$ipid])){
                                foreach($all_data[$ipid] as $drugplan_id=>$data){
                                    if(!in_array($data['category'],$timed_scheduled_medications))
                                    {
                                        $interaction[$drugplan_id]['drugplan_id'] = $drugplan_id;
                                        $interaction[$drugplan_id]['medication_name'] = $data['medication_name'];
                                        
                                        $interaction[$drugplan_id]['time_schedule'] = '1'; // 1 if has time interval 0 if not
                                        
                                        $interaction[$drugplan_id]['dosage_unit'] = $data['unit_name'];
                                        
                                        $given_info = array();
                                        if($data['givenInfo_time'][$today][$dosage_time.':00'])
                                        {
                                            $given_info = $data['givenInfo_time'][$today][$dosage_time.':00'];
                                            $interaction[$drugplan_id]['dosage'] = $given_info['dosage'];
                                            $interaction[$drugplan_id]['dosage_status'] = $given_info['dosage_status'];
                                            $interaction[$drugplan_id]['dosage_time_interval'] = $given_info['dosage_time_interval'];
                                            $interaction[$drugplan_id]['documented_info'] = $given_info['documented_info'];
                                            $interaction[$drugplan_id]['not_given_reason'] = $given_info['not_given_reason'];
                                            $interaction[$drugplan_id]['dosage_date'] = $given_info['dosage_date'];
                                        }
                                        else
                                        {
                                            $interaction[$drugplan_id]['dosage'] = $data['dosage'];
                                            $interaction[$drugplan_id]['dosage_time_interval'] = '' ;
                                            //                                         $interaction[$drugplan_id]['dosage_date'] = $today_time;
                                        }
                                            
                                    } else{

                                        
                                        if(!empty($data['drugplan_dosage'][$dosage_time.':00']['dosage'])){
                                            
                                            $interaction[$drugplan_id]['drugplan_id'] = $drugplan_id;
                                            $interaction[$drugplan_id]['medication_name'] = $data['medication_name'];
                                            
                                            $interaction[$drugplan_id]['time_schedule'] = '1'; // 1 if has time interval 0 if not
                                            
                                            $interaction[$drugplan_id]['dosage_unit'] = $data['unit_name'];
                                            
                                            $given_info = array();
                                            if($data['givenInfo_time'][$today][$dosage_time.':00'])
                                            {
                                                $given_info = $data['givenInfo_time'][$today][$dosage_time.':00'];
                                                $interaction[$drugplan_id]['dosage'] = $given_info['dosage'];
                                                $interaction[$drugplan_id]['dosage_status'] = $given_info['dosage_status'];
                                                $interaction[$drugplan_id]['dosage_time_interval'] = $given_info['dosage_time_interval'];
                                                $interaction[$drugplan_id]['documented_info'] = $given_info['documented_info'];
                                                $interaction[$drugplan_id]['not_given_reason'] = $given_info['not_given_reason'];
                                                $interaction[$drugplan_id]['dosage_date'] = $given_info['dosage_date'];
                                            } 
                                            else
                                            {
                                                $interaction[$drugplan_id]['dosage'] = $data['drugplan_dosage'][$dosage_time.':00']['dosage'];
                                                $interaction[$drugplan_id]['dosage_time_interval'] = $dosage_time.':00' ;
        //                                         $interaction[$drugplan_id]['dosage_date'] = $today_time;
                                            }
                                        } elseif( !empty($data['dosage'])  && !is_array($data['dosage'])){
                                            $interaction[$drugplan_id]['drugplan_id'] = $drugplan_id;
                                            $interaction[$drugplan_id]['medication_name'] = $data['medication_name'];
                                            
                                            $interaction[$drugplan_id]['time_schedule'] = '1'; // 1 if has time interval 0 if not
                                            
                                            $interaction[$drugplan_id]['dosage_unit'] = $data['unit_name'];
                                            
                                            $given_info = array();
                                            if($data['givenInfo_time'][$today][$dosage_time.':00'])
                                            {
                                                $given_info = $data['givenInfo_time'][$today][$dosage_time.':00'];
                                                $interaction[$drugplan_id]['dosage'] = $given_info['dosage'];
                                                $interaction[$drugplan_id]['dosage_status'] = $given_info['dosage_status'];
                                                $interaction[$drugplan_id]['dosage_time_interval'] = $given_info['dosage_time_interval'];
                                                $interaction[$drugplan_id]['documented_info'] = $given_info['documented_info'];
                                                $interaction[$drugplan_id]['not_given_reason'] = $given_info['not_given_reason'];
                                                $interaction[$drugplan_id]['dosage_date'] = $given_info['dosage_date'];
                                            } 
                                            else
                                            {
                                                $interaction[$drugplan_id]['dosage'] = $data['dosage'];
                                                $interaction[$drugplan_id]['dosage_time_interval'] = $dosage_time.':00' ;
        //                                         $interaction[$drugplan_id]['dosage_date'] = $today_time;
                                            }
                                            
                                            
                                        }
                                    }
                                    
                                    
                                }
                                
                            }
  
                            $dosage_interaction_form ='<form id="bulk_interaction" method="post">';
                            $dosage_interaction_form .='<span style="display: block;float: right;clear: both;width: 100%;text-align: right"><b>Datum:</b>'.date('d.m.Y').'</span>';
                            
                            foreach( $interaction as $frg=>$values){
                                $dosage_interaction_form .= $form->create_dosage_interaction_bulk($values);
                            }
                            $dosage_interaction_form .='</form>';
                            $this->getResponse()->setBody($dosage_interaction_form)->sendResponse();
                            
                            exit;
                            break;
                            
                            //ISPC-2516 Carmen 09.04.2020
                        case 'awakesleepingadd':
                            $afb = new Application_Form_FormBlockAwakeSleepingStatus();
                            
                            if($_REQUEST['recid'])
                            {
                                $values = FormBlockAwakeSleepingStatusTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
                            }
                            
                            $awake_sleeping_form = $afb->create_form_block_awake_sleeping_status($values);
                            $this->getResponse()->setBody($awake_sleeping_form)->sendResponse();
                            
                            exit;
                            break;
                            //--
                        
                        //ISPC-2522 Carmen 10.04.2020
                        case 'positioningadd':
                            $afb = new Application_Form_FormBlockPositioning();
                            
                            if($_REQUEST['recid'])
                            {
                                $values = FormBlockPositioningTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
                            }
                            
                            //ISPC-2662 Carmen 31.08.2020
                            $old_storage = array(
                            		'' => $this->view->translate('select'),
                            		'1' => '0',
                            		'2' => '30',
                            		'3' => '90',
                            		'4' => '180'
                            );
                            if($values['positioning_additional_info_old'] != '')
                            {
                            	$positionind_storage_index = array_search($values['positioning_additional_info_old'], $old_storage);
                            	$values['positioning_additional_info']['storage'] = $positionind_storage_index;
                            }
                            //--
                            $positioning_form = $afb->create_form_block_positioning($values);
                            $this->getResponse()->setBody($positioning_form)->sendResponse();
                            
                            exit;
                            break;
                        //--
                        
                        //ISPC-2523 Carmen 13.04.2020
                        case 'suckoffadd':
                            $afb = new Application_Form_FormBlockSuckoff();
                            
                            if($_REQUEST['recid'])
                            {
                                $values = FormBlockSuckoffTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
                            }
                            
                            $suckoff_form = $afb->create_form_block_suckoff($values);
                            $this->getResponse()->setBody($suckoff_form)->sendResponse();
                            
                            exit;
                            break;
                        //--
                        
                        //ISPC-2518+ISPC-2520 Carmen 14.04.2020
                        case 'organicentriesexitsadd':
                            //get the options box from the client list
                            $client_options = OrganicEntriesExitsListsTable::getInstance()->findAllOptions($clientid);
                            //ISPC-2661 pct.14 Carmen 16.09.2020
                            /* $afb = new Application_Form_FormBlockOrganicEntriesExits(array(
                                "_client_options"		=> $client_options,
                            )); */
                            $allsets = array();
                            //--
                            //ISPC-2661 pct.14 Carmen 16.09.2020
                            //$opensets = OrganicEntriesExitsSetsTable::getInstance()->findByEndsetAndIpid(0,$ipid, Doctrine_Core::HYDRATE_ARRAY);
                            $allsets = OrganicEntriesExitsSetsTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
                            
                            if($_REQUEST['recid'])
                            {
                                $values = FormBlockOrganicEntriesExitsTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
                            }
                            /* else
                            {
                                $opensets = OrganicEntriesExitsSetsTable::getInstance()->findByEndsetAndIpid(0,$ipid, Doctrine_Core::HYDRATE_ARRAY);
                            	
                            } */
                            $afb = new Application_Form_FormBlockOrganicEntriesExits(array(
                            		"_client_options"		=> $client_options,
                            		//"_opensets" => $opensets,
                            		"_allsets" => $allsets,
                            ));
                            //--
                            if(empty($values)){
                                $values['ipid']= $ipid;
                            }
                            $oee_form = $afb->create_form_block_organic_entries_exits($values);
                            $this->getResponse()->setBody($oee_form)->sendResponse();
                            
                            exit;
                            break;
                        //--
                        
                        //ISPC-2519 Carmen 15.04.2020
                        case 'customeventadd':
                            $afb = new Application_Form_FormBlockCustomEvent();
                            
                            if($_REQUEST['recid'])
                            {
                                $values = FormBlockCustomEventTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
                            }
                            
                            $custev_form = $afb->create_form_block_custom_event($values);
                            $this->getResponse()->setBody($custev_form)->sendResponse();
                            
                            exit;
                            break;
                        //--
                        
                        //ISPC-2508 Carmen 21.04.2020
                        case 'artificialentriesexitsadd':
                            //get the options box from the client list
                            $client_options = ArtificialEntriesExitsListTable::getInstance()->findByClientid($this->logininfo->clientid, Doctrine_Core::HYDRATE_ARRAY);
                            
                            $afb = new Application_Form_Stammdatenerweitert(array(
                                "_client_options"		=> $client_options,
                            ));
                            
                            if($_REQUEST['recid'])
                            {
                                $values = PatientArtificialEntriesExitsTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
                            }
                            
                            if($_REQUEST['artaction'] && $_REQUEST['artaction'] == 'remove')
                            {
                                $values['action'] = 'remove';
                                $values['subaction'] = 'remove';
                            }
                            elseif($_REQUEST['artaction'] && $_REQUEST['artaction'] == 'refresh')
                            {
                            	$values['action'] = 'remove';
                            	$values['subaction'] = 'refresh';
                            }                            
                            
                            $artee_form = $afb->create_form_artificial_entries_exits($values);
                            $this->getResponse()->setBody($artee_form)->sendResponse();
                            
                            exit;
                            break;
                            case 'artificialentriesexitsactions':
                            	
                    			$createBox_formFn = 'create_form_artificial_entries_exits';
    
						    	//get the options box from the client list
						    	$client_options = ArtificialEntriesExitsListTable::getInstance()->findByClientid($this->logininfo->clientid, Doctrine_Core::HYDRATE_ARRAY);
						    	
						    	$patientdata = new PatientMaster();
						    	$patientModelData = $patientdata->get_patientMasterData($patientdata->getMasterData_extradata($ipid, 'PatientArtificialEntriesExits'));	
						    	
						    	if ( ! empty($patientModelData)) {
						    
						    		$entries = array();
						    		$exits = array();
						    		
						    		foreach($patientModelData['PatientArtificialEntriesExits'] as $kd => $vd)
						    		{
						    			
						    			if($vd['option_type'] == 'entry')
						    			{
						    				$entries[] = $vd;
						    			}
						    			else
						    			{
						    				$exits[] = $vd;
						    			}
    			 
    								}
 
    	
  			$data = array();
    		if($entries)
    		{
    			$this->__box_Patient_Artificial_Entries_Exits($entries, $client_options);
    			$this->view->openfrom = 'modal';
    			$data['entries'] = $this->view->render("patientnew/patientdetails_box_artificial_entries.phtml");
    
    		}
    		 
    		if($exits)
    		{
    			$this->__box_Patient_Artificial_Entries_Exits($exits, $client_options);
    			$this->view->openfrom = 'modal';
    			$data['exits'] = $this->view->render("patientnew/patientdetails_box_artificial_exits.phtml");
    			
    		}
    		 
    	}
    	
                        $this->_helper->json->sendJson($data);
                            	exit;
                            	break;
                        //--
                        
                        //ISPC-2523 Carmen 13.04.2020
                        case 'symptomatologyadd':
                           $afb = new Application_Form_PatientSymptomatology(array(
                            '_block_name'           => 'Charts',
                            '_setid'				=> '1',
                            ));
                            
                            $sympt_form = $afb->create_form_symptomatology($values);
                            $this->getResponse()->setBody($sympt_form)->sendResponse();
                            
                            exit;
                            break;
                        
                        //ISPC-2516 Carmen 09.07.2020
                        case 'symptomatologyIIadd':
                        	$afb = new Application_Form_FormBlockClientSymptoms(array(
                        			'_block_name'           => 'Charts',
                        	));
                        	
                        	$sympt_form = $afb->create_form_symptomatology($values);
                        	$this->getResponse()->setBody($sympt_form)->sendResponse();
                        	
                            exit;
                            break;
                        //--
                        
                        //ISPC-2512 Ancuta
                        case 'contact_form':
                            $contact_forms = new ContactForms();
                            if($_REQUEST['recid'])
                            {
                                $contact_form_id  = $_REQUEST['recid'];
                                $contact_form_details = $contact_forms->get_contact_form($contact_form_id);
                                
                                $user =  new User();
                                $users_details = array();
                                $users_details= $user->getUserByClientid($this->logininfo->clientid,1,true);
                                
                                $form_types = new FormTypes();
                                $contact_form_types = $form_types->get_form_types($this->logininfo->clientid);
                                $contact_form_types_final = array();
                                foreach($contact_form_types as $k_form_type => $v_form_type)
                                {
                                    $contact_form_types_final[$v_form_type['id']] = $v_form_type['name'];
                                }
                                
                                $contact_form_details['form_type_name'] = $contact_form_types_final[$contact_form_details['form_type']];
                                $contact_form_details['start_date_time'] = date('H:i',strtotime($contact_form_details['start_date'])).' - '.date('H:i',strtotime($contact_form_details['end_date'])).' '.date('d.m.Y',strtotime($contact_form_details['start_date'])) ;
                                $contact_form_details['create_user_name'] = $users_details[$contact_form_details['create_user']];
                                
                            }
                           $cf_form = '';
                           $cf_form .= '<table class="cfc_info">';
                           $cf_form .= '<tr>';
                           $cf_form .= '<td class="cfc_label">'.$this->view->translate('contact_form_type').'</td>';
                           $cf_form .= '<td>'.$contact_form_details['form_type_name'].'</td>';
                           $cf_form .= '</tr>';
                           $cf_form .= '<tr>';
                           $cf_form .= '<td class="cfc_label">'.$this->view->translate('visit_date').'</td>';
                           $cf_form .= '<td>'.$contact_form_details['start_date_time'].'</td>';
                           $cf_form .= '</tr>';
                           $cf_form .= '<tr>';
                           $cf_form .= '<td class="cfc_label">'.$this->view->translate('contact_form_creator').'</td>';
                           $cf_form .= '<td>'.$contact_form_details['create_user_name'].'</td>';
                           $cf_form .= '</tr>';
                           $cf_form .= '</table>';
                            
                           $this->getResponse()->setBody($cf_form)->sendResponse();
                            
                            exit;
                        break;
                        //--
                        
                        //ISPC-2517 Ancuta 23.05.2020
                        case 'medication_view':
                            
                            $patient_icons = new IconsPatient();
                            $medication_icons = $patient_icons->get_patient_medication(array($ipid));
                            dd($ipid,$medication_icons);
                            $afb = new Application_Form_PatientSymptomatology(array(
                            '_block_name'           => 'Charts',
                            ));
                            
                            $sympt_form = $afb->create_form_symptomatology($values);
                            $this->getResponse()->setBody($sympt_form)->sendResponse();
                            
                            exit;
                            break;
                            //--
						//ISPC-2697, elena, 11.11.2020
                        case 'beatmung' :
                            $afkv = new Application_Form_FormBlockKeyValue();
                            //ISPC-2904,Elena,30.04.2021
                            $oldValues = [];

                            if($_REQUEST['recid'])
                            {
                               $oldValues =  Doctrine::getTable('FormBlockKeyValue')->find(intval($_REQUEST['recid']), Doctrine_Core::HYDRATE_ARRAY);
                               $ipid = $oldValues['ipid'];
                                $aOldData = json_decode($oldValues['returnvalue'], true);
                            if($aOldData == null){
                                    $aOldData = json_decode($oldValues['returnvalue'][0], true);
                            }
                                $oldValues['id'] = intval($_REQUEST['recid']);

                            }else{
                                $oldValues = FormBlockKeyValue::getLastBlockValues( $ipid, 'FormBlockBeatmung' , Doctrine_Core::HYDRATE_ARRAY);
                                //print_r($oldValues_otherform);
                                $aOldData = json_decode($oldValues['returnvalue'], true);
                                if($aOldData == null){
                                    $aOldData = json_decode($oldValues['returnvalue'][0], true);
                                }
                            $oldValues['used_machine'] = $aOldData['beatmung']['machine_opt'];

                            }
                            //ISPC-2904,Elena,30.04.2021 end



                            $oldValues['with_datetime'] = true;
                            $beatmung_form = $afkv->create_form_ventilation($oldValues, $ipid);
                            $this->getResponse()->setBody($beatmung_form)->sendResponse();

                            exit;
                            break;
                        


                            
	                      //ISPC-2523 Carmen 13.04.2020
	                      case 'vigilanceawarenessadd':
	                          	$afb = new Application_Form_FormBlockLmuVisit();
	                           
	                           	if($_REQUEST['recid'])
	                           	{
	                           		$values = FormBlockLmuVisitTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
	                           	}
	                            $values['orientation'] = array();
	                           	if($values['ort'] == 1)
	                           	{
	                           		$values['orientation'][] = 'ort';
	                           	}
	                           	if($values['person'] == 1)
	                           	{
	                           		$values['orientation'][] = 'person';
	                           	}
	                           	if($values['situation'] == 1)
	                           	{
	                           		$values['orientation'][] = 'situation';
	                           	}
	                           	if($values['zeit'] == 1)
	                           	{
	                           		$values['orientation'][] = 'zeit';
	                           	}
	                           	if($values['keineorient'] == 1)
	                           	{
	                           		$values['orientation'][] = 'keineorient';
	                           	}
	                           	
	                           	$vigilanceawareness_form = $afb->create_form_block_vigilance_awareness($values);
	                           	$this->getResponse()->setBody($vigilanceawareness_form)->sendResponse();
	                            
	                           	exit;
	                           	break;
	                      //--
                            
                            
	                      //ISPC-2864 Ancuta 14.04.2021
	                      case 'patientproblemsadd':
	                          	$afb = new Application_Form_FormBlockProblems();
	                           
	                           	if($_REQUEST['recid'])
	                           	{
	                           	    $values = FormBlockProblemsTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
	                           	}
	               
	                           	$values['ipid'] = $ipid;
	                           	$values['clientid'] = $clientid;
	                           	$values['enc_id'] = $_REQUEST['patid'];
	                           	$patient_problems_form = $afb->create_form_block_patient_problems($values);
	                           	$this->getResponse()->setBody($patient_problems_form)->sendResponse();
	                            
	                           	exit;
	                           	break;
	                      //--
                            
                            
                        default:
                            exit;
                            break;
                    }
                    break;
                    
                case 'save_form':
                    switch($_REQUEST['form'])
                    {
                        
                        case 'dosage_interaction_bulk_save':
                            
                            $form = new Application_Form_PatientDrugPlan();
                            $result = array();
                            if(!empty($_POST['data'])){
                                foreach($_POST['data'] as $drugplan_id => $post_data){
                                   $result[]= $form->save_dosage_interaction($ipid, $post_data);
                                }
                            }
                            if($result){
                                echo '1';
                                exit;
                            } else{
                                echo 'error';
                                exit;
                                
                            }
                            break;
                        case 'dosage_interaction_save':
                            
                            $form = new Application_Form_PatientDrugPlan();
                            $form->save_dosage_interaction($ipid, $_POST, $_REQUEST['subaction']);
                            
                            exit;
                            break;
                            
                            //ISPC-2516 Carmen 09.04.2020
                        case 'awakesleepingsave':
                            
                            switch($_REQUEST['subaction'])
                            {
                                case 'delete':
                                    $entity = FormBlockAwakeSleepingStatusTable::getInstance()->find($_POST['id']);
                                    
                                    if($entity)
                                    {
                                        $entity->delete();
                                    }
                                    break;
                                    
                                default:
                                    $fas = new Application_Form_FormBlockAwakeSleepingStatus();
                                    $form = $fas->save_form_block_awake_sleeping_status($ipid, $_POST);
                            }
                            
                            exit;
                            break;
                            //--
                            
                            
                            //ISPC-2522 Carmen 10.04.2020
                        case 'positioningsave':
                            
                            switch($_REQUEST['subaction'])
                            {
                                case 'delete':
                                    $entity = FormBlockPositioningTable::getInstance()->find($_POST['id']);
                                    
                                    if($entity)
                                    {
                                        $entity->delete();
                                    }
                                    break;
                                    
                                default:
                                    $fas = new Application_Form_FormBlockPositioning();
                                    $form = $fas->save_form_block_positioning($ipid, $_POST);
                                    break;
                            }
                            
                            exit;
                            break;
                            //--
                            
                            //ISPC-2523 Carmen 13.04.2020
                        case 'suckoffsave':
                            
                            switch($_REQUEST['subaction'])
                            {
                                case 'delete':
                                    $entity = FormBlockSuckoffTable::getInstance()->find($_POST['id']);
                                    
                                    if($entity)
                                    {
                                        $entity->delete();
                                    }
                                    break;
                                    
                                default:
                                    $fas = new Application_Form_FormBlockSuckoff();
                                    $form = $fas->save_form_block_suckoff($ipid, $_POST);
                                    break;
                            }
                            
                            exit;
                            break;
                            //--
                            
                            //ISPC-2518+ISPC-2520 Carmen 14.04.2020
                        case 'organicentriesexitssave':
                            
                            switch($_REQUEST['subaction'])
                            {
                                case 'delete':
                                    $entity = FormBlockOrganicEntriesExitsTable::getInstance()->find($_POST['id']);
                                    
                                    if($entity)
                                    {
                                        $entity->delete();
                                        $setdata = FormBlockOrganicEntriesExitsTable::getInstance()->findByIpidAndSetid($ipid, $entity->setid, Doctrine_Core::HYDRATE_ARRAY);
                                        if(count($setdata) == '1')
                                        {
                                        	$setentity = OrganicEntriesExitsSetsTable::getInstance()->findset($entity->setid);
                                        	
                                        	$setentity->endset = 0;
                                        	$setentity->save();
                                        }
                                    }
                                    break;
                                    
                                default:
                                    $fas = new Application_Form_FormBlockOrganicEntriesExits();
                                    $form = $fas->save_form_block_organic_entries_exits($ipid, $_POST);
                                    break;
                            }
                            
                            exit;
                            break;
                            //--
                            
                            //ISPC-2519 Carmen 15.04.2020
                        case 'customeventsave':
                            
                            switch($_REQUEST['subaction'])
                            {
                                case 'delete':
                                    $entity = FormBlockCustomEventTable::getInstance()->find($_POST['id']);
                                    
                                    if($entity)
                                    {
                                        $entity->delete();
                                    }
                                    break;
                                    
                                default:
                                    $fas = new Application_Form_FormBlockCustomEvent();
                                    $form = $fas->save_form_block_custom_event($ipid, $_POST);
                                    break;
                            }
                            
                            exit;
                            break;
                            //--
                            
                            //ISPC-2508 Carmen 21.04.2020
                        case 'artificialentriesexitssave':
                            
                            /* switch($_REQUEST['subaction'])
                             {
                             case 'delete':
                             $entity = PatientArtificialEntriesExitsTable::getInstance()->find($_POST['id']);
                             
                             if($entity)
                             {
                             $entity->delete();
                             }
                             break;
                             
                             case 'remove':
                             $entity = PatientArtificialEntriesExitsTable::getInstance()->find($_POST['id']);
                             
                             if($entity)
                             {
                             $entity->isremove = 1;
                             $entity->remove_date = date('Y-m-d H:i:s', time());
                             $entity->save();
                             }
                             break;
                             
                             case 'refresh':
                             $entity = PatientArtificialEntriesExitsTable::getInstance()->find($_POST['id']);
                             
                             if($entity)
                             {
                             //remove the entity and create a new one starting now
                             $entity->isremove = 1;
                             $entity->remove_date = date('Y-m-d H:i:s', time());
                             $entity->save();
                             
                             $data['id'] = null;
                             $data['ipid'] = $ipid;
                             $data['option_id'] = $entity->option_id;
                             $data['option_date'] = date('Y-m-d H:i:s', time());
                             $data['option_localization'] = $entity->option_localization;
                             
                             $newentity = PatientArtificialEntriesExitsTable::getInstance()->createIfNotExistsOneBy(array('id', 'ipid'), array($data['id'], $ipid), $data);
                             
                             }
                             break;
                             
                             default:
                             
                             $fas = new Application_Form_Stammdatenerweitert();
                             $form = $fas->save_form_artificial_entries_exits($ipid, $_POST);
                             
                             break;
                             } */
                            //ISPC-2508 Carmen 21.05.2020 new design
                            if($_REQUEST['artaction'] == 'delete')
                            {
                            	$entity = PatientArtificialEntriesExitsTable::getInstance()->find($_POST['id']);
                            	
                            	if($entity)
                            	{
                            		$entity->delete();
	                            	if($_REQUEST['openfrom'] == 'icon')
	                            	{
	                            		
	                            		$data = IconsPatient::get_patient_artificial_entries_exits_expired(array($ipid));
	                            		if(count($data['patient_artificial_entries_exits_expired'][$ipid]) > 0 ){
	                            			$return ['remove_icon'] = "0";
	                            		} else{
	                            			$return ['remove_icon'] = "1";
	                            		}
	                            	
	                            		echo json_encode($return);
	                            	}                            	
                            	}
                            	
                                /* $entity = PatientArtificialEntriesExitsTable::getInstance()->find($_POST['id']);
                                
                                if($entity)
                                {
                                    $entity->delete();
                                } */
                            }
                            else
                            {
                                $_POST['action'] = $_REQUEST['artaction'];
                                //get the options box from the client list
                                $client_options = ArtificialEntriesExitsListTable::getInstance()->findByClientid($this->logininfo->clientid, Doctrine_Core::HYDRATE_ARRAY);
                                $fas = new Application_Form_Stammdatenerweitert(array(
                                    "_client_options"		=> $client_options,
                                ));
                                
                                if($_POST['action'] != 'refresh' && $_POST['action'] != 'remove')
                                {
	                                $fas->mapValidateFunction( 'create_form_artificial_entries_exits', 'create_form_isValid');
	                                
	                                $validated = $fas->triggerValidateFunction('create_form_artificial_entries_exits', array($ipid, $_POST));
	                                
	                                if(!is_string($validated))
	                                {
	                                    $form = $fas->save_form_artificial_entries_exits($ipid, $_POST);
	                                }
	                                else
	                                {
	                                    echo $validated;
	                                }
                                }
                                else
                                { 
                                	$form = $fas->save_form_artificial_entries_exits($ipid, $_POST);
                                	
                                	if($_REQUEST['openfrom'] == 'icon')
                                	{
                                		$form = $fas->save_form_artificial_entries_exits($ipid, $_POST);
                                
                                		$data = IconsPatient::get_patient_artificial_entries_exits_expired(array($ipid));
                                		if(count($data['patient_artificial_entries_exits_expired'][$ipid]) > 0 ){
                                			$return ['remove_icon'] = "0";
                                		} else{
                                			$return ['remove_icon'] = "1";
                                		}
                                
                                		echo json_encode($return);
                                	}                               		
                                }
                                
                            }
                            //--
                            exit;
                            break;
                            //--
                        case 'symptomatologysave':
                        	
                            $post = array();
                            
                            foreach($_POST as $kr => $vr)
                            {
                                if(!is_array($vr))
                                {
                                    $post[$kr] = $vr;
                                }
                                else
                                {
                                    foreach($vr as $keyr => $valr)
                                    {
                                        $post[$keyr] = $valr;
                                    }
                                }
                            }
                            
                            $fas = new Application_Form_PatientSymptomatology();
                            $form = $fas->save_form_symptomatology($ipid, $post,true);
                        	
                            exit;
                            break;
						//ISPC-2516 Carmen 09.07.2020
                       	case 'symptomatologyIIsave':
                            	 
                            $fas = new Application_Form_FormBlockClientSymptoms();
                            $form = $fas->save_form_symptomatology($ipid, $_POST,true);
                            	 
                            exit;
                            break;
						//--
                        //ISPC-2697, elena, 12.11.2020
                        case 'beatmung_save':
                            $afkv = new Application_Form_FormBlockKeyValue();
                            $data_post = [
                                'contact_form_id' => 0
                                ];
                            $data_block = $this->getRequest()->getPost('FormBlockBeatmung', []);
                            $form = $afkv->save_form_ventilation($ipid,   $data_post, $data_block);

							exit;
                            break;
                        //--
                       //ISPC-2523 Carmen 13.04.2020
                       case 'vigilanceawarenesssave':
                            
                       switch($_REQUEST['subaction'])
                       {
                       		case 'delete':
                       			$entity = FormBlockLmuVisitTable::getInstance()->find($_POST['id']);
                           
                       			if($entity)
                       			{
                       				$entity->delete();
                       			}
                       			break;
                            
                          	default:
                           		$fas = new Application_Form_FormBlockLmuVisit();
                           		$form = $fas->save_form_block_vigilance_awareness($ipid, $_POST);
                           		break;
                       }
                            
                       exit;
                       break;
                       //--
                       
                       
                       case 'patientproblemssave':

                       switch($_REQUEST['subaction'])
                       {
                       		case 'delete':
                       		    $entity = FormBlockProblemsTable::getInstance()->find($_POST['id']);
                           
                       			if($entity)
                       			{
                       				$entity->delete();
                       			}
                       			break;
                            
                          	default:
                          	    $fas = new Application_Form_FormBlockProblems();
                          	    $form = $fas->save_form_block_patient_problems($ipid, $_POST);
                           		break;
                       }
                            
                       exit;
                       break;
                       //--
                            
                        default:
                            exit;
                            break;
                    }
                    break;
                    
                case 'load-chart':
                    switch($_REQUEST['chart_name'])
                    {
                        case 'vital-_signs':
                            
                            
                            exit;
                            break;
                            //--
                    }
                default:
                    exit;
                    break;
                    
            }
        }
    }
    
  
    /**
     * ISPC-2518+ISPC-2520 add extrafields to organic block
     * @carmen 14.04.2020
     */
    
    public function createformblockorganicextrafieldsAction()
    {
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        $parent_form = $this->getRequest()->getParam('parent_form');
        
        $_block_name = $this->getRequest()->getParam('_block_name', null);
        
        $orgid = $_REQUEST['orgid'];
        $recid = $_REQUEST['recid'];
        
        $extrafields = OrganicEntriesExitsExtrafieldsTable::getInstance()->findBy('organic_id', $orgid, Doctrine_Core::HYDRATE_ARRAY);
        
        if($_REQUEST['recid'])
        {
            $values = FormBlockOrganicEntriesExitsTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
        }
        
        $af = new Application_Form_FormBlockOrganicEntriesExits();
        $extraf = $af->create_form_block_organic_extrafields([
            '_extrafields' => $extrafields,
            'values' => $values,
        ], $parent_form);
        
        $this->getResponse()->setBody($extraf)->sendResponse();
        
        exit;
    }
    
    /**
     * ISPC 2508 Carmen 27.05.2020
     * patientdetails_box_artificial_entries_exits.phtml
     */
    private function __box_Patient_Artificial_Entries_Exits($data = [], $client_options)
    {
    	$current_date = date('Y-m-d H:i:s');
    
    	if ( ! empty($data))
    	{
    		foreach($data as $kr => $vr)
    		{
    			//var_dump(Pms_CommonData::get_days_number_between($current_date, $vr['option_date'])); exit;
    			$patient_artificial_entries_exits[$kr]['option_name'] = $vr['option_name'];
    			//$patient_artificial_entries_exits[$kr]['option_type'] = $vr['option_type'];
    			$patient_artificial_entries_exits[$kr]['option_date'] = date('d.m.Y', strtotime($vr['option_date']));
    			$patient_artificial_entries_exits[$kr]['option_localization'] = $vr['option_localization'];
    
    			$optkey = array_search($vr['option_id'], array_column($client_options, 'id'));
    			if($optkey !== false)
    			{
    				$option_valability = $client_options[$optkey]['days_availability'];
    			}
    			 
    			$option_age =  Pms_CommonData::get_days_number_between($current_date, $vr['option_date']);
    			 
    			if($option_age < 0)
    			{
    				$option_age = 0;
    			}
    
    			if($option_age > 0)
    			{
    				if($option_valability > 0 && $option_age > $option_valability)
    				{
    					$patient_artificial_entries_exits[$kr]['option_age'] = '<span><font style="color: red;">!</font>'.sprintf('%3s', $option_age).' '. $this->translate('days') . '</span>';
    				}
    				else
    				{
    					$patient_artificial_entries_exits[$kr]['option_age'] = '<span>'.sprintf('%3s', $option_age).' '. $this->translate('days') . '</span>';
    				}
    			}
    			else
    			{
    				$patient_artificial_entries_exits[$kr]['option_age'] = '<span>' . $this->translate('today new') . '</span>';
    			}
    			//ISPC-2508 Carmen 18.05.2020 new design
    			/* $patient_artificial_entries_exits[$kr]['actions'] = '<span class="info-button"><img title="'.$this->translate("edit").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/edit.png" /><input type="hidden" class="hidden-json-data" value="'.$vr['id'].'" > </span>';
    			$patient_artificial_entries_exits[$kr]['actions'] .= '<span class="info-button"><img title="'.$this->translate("notneeded").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_remove.png" /><input type="hidden" class="hidden-json-data" value="'.$vr['id'].'" ><input type="hidden" name="action" value="remove" /> </span>';
    			$patient_artificial_entries_exits[$kr]['actions'] .= '<span class="info-button"><img title="'.$this->translate("refresh").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_renew.png" /><input type="hidden" class="hidden-json-data" value="'.$vr['id'].'" ><input type="hidden" name="action" value="refresh" /> </span>';
    			$patient_artificial_entries_exits[$kr]['actions'] .= '<span class="info-button"><img title="'.$this->translate("delete").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_delete.png" /><input type="hidden" class="hidden-json-data" value="'.$vr['id'].'" ><input type="hidden" name="action" value="delete" /></span>'; */
    			 
    			$patient_artificial_entries_exits[$kr]['actions'] = '<span class="set_patient_artificial_setting" data-recid="'.$vr['id'].'" data-openfrom="addmodal"><img title="'.$this->translate("actions").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/edit.png" /><input type="hidden" class="hidden-json-data" value="'.$vr['id'].'" > </span>';
    			//--
    		}
    
    		if($data[0]['option_type'] == 'entry')
    		{
    			$this->view->patient_artificial_entries = $patient_artificial_entries_exits;
    		}
    		else
    		{
    			$this->view->patient_artificial_exits = $patient_artificial_entries_exits;
    		}
    		//var_dump($this->getView()->patient_artificial_entries); exit;
    	}
    }
    
    /**
     * ISPC-2516 simptome II like modal
     * @carmen 09.07.2020
     */
    
    public function createformblocksymptomatologyrowAction()
    {
    	if ( ! $this->getRequest()->isXmlHttpRequest()) {
    		throw new Exception('!isXmlHttpRequest', 0);
    	}
    	$this->_helper->layout->setLayout('layout_ajax');
    	$this->_helper->viewRenderer->setNoRender();
    
    	//$parent_form = $this->getRequest()->getParam('parent_form');    
    	//$_block_name = $this->getRequest()->getParam('_block_name', null);
    	
    	
    	$af = new Application_Form_FormBlockClientSymptoms();
    	
    	$parent_form = 'clientsymptoms';
    	$newrow = "[new_". uniqid(). "]";
    	$extraff = $af->create_form_block_clientsymtoms_firstrow(null, $parent_form. $newrow);
    	$extrafs = $af->create_form_block_clientsymtoms_secondrow(null, $parent_form. $newrow);
    
    	$this->getResponse()->setBody($extraff.$extrafs)->sendResponse();
    
    	exit;
    }
    
    
    
}
?>