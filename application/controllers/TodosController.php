<?php

class TodosController extends Pms_Controller_Action
{
	public function completedAction(){
		if(!empty($_REQUEST['todo_id']) && is_numeric($_REQUEST['todo_id'])) {
			$id = $_REQUEST['todo_id'];
			$logininfo= new Zend_Session_Namespace('Login_Info');

			$todo = new ToDos();
			$todo_details = $todo->getTodoById($id);

			if($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA' || $logininfo->userid == $todo_details[0]['user_id'] ||  ($logininfo->groupid == $todo_details[0]['group_id'] && $todo_details[0]['group_id'] > 0)) {
				$todo->completeTodo($id);
			}
		}
		exit;
	}
	public function uncompletedAction(){
		if(!empty($_REQUEST['todo_id']) && is_numeric($_REQUEST['todo_id'])) {
			$id = $_REQUEST['todo_id'];
			$logininfo= new Zend_Session_Namespace('Login_Info');

			$todo = new ToDos();
			$todo_details = $todo->getTodoById($id);

			if($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA' || $logininfo->userid == $todo_details[0]['user_id'] ||  ($logininfo->groupid == $todo_details[0]['group_id'] && $todo_details[0]['group_id'] > 0)) {
				$todo->uncompleteTodo($id);
			}
		}
		exit;
	}

	public function printtodosoldAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->_helper->layout->setLayout('layout_totalblank');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$groupid = $logininfo->groupid;
		$user_type = $logininfo->usertype;

		$this->view->userid = $userid;
		$this->view->groupid = $groupid;
		$this->view->user_type = $user_type;
		
		//	TODO-1298
		$notifications = new Notifications();
		$user_notification_settings = $notifications->get_notification_settings($userid);
		
		$modules = new Modules();
		$ModulePrivileges = $modules->get_client_modules($clientid);
		$grouped_dashbord = "0";
		if($user_notification_settings[$userid]['dashboard_grouped'] == '1' && $ModulePrivileges['156'] == "1" )
		{
			$grouped_dashbord = '1';
		}
		$this->view->grouped_dashbord = $grouped_dashbord;
		
		
		
		$dashboard_labels = new DashboardLabels();
		$action_last_label = $dashboard_labels->getActionsLastLabel();
		//todo stuff
		$user = new User();
		$client_users = $user->getUserByClientid($clientid, 0, true);

		$users2groups[] = '9999999999999';
		foreach ($client_users as $k_user => $v_user)
		{
			$todo_users[$v_user['id']] = $v_user;
			$users2groups[$v_user['id']] = $v_user['groupid'];
			$groups2users[$v_user['groupid']][] = $v_user['id'];
		}

		$current_user_group_asignees[] = '999999999';
		$current_user_group_asignees[] = '9999999';
		$current_user_group_asignees = $groups2users[$groupid];
		$this->view->group2users = $groups2users;
		if ($_REQUEST['dbgz'])
		{
			print_r($groupid);
			print_r($groups2users);
		}

		//get client coord groups
		$usergroup = new Usergroup();
		$MasterGroups = array("6"); // Koordinator
		$coord_groups[] = '999999999';
		$usersgroups = $usergroup->getUserGroups($MasterGroups);
		if (count($usersgroups) > 0)
		{
			foreach ($usersgroups as $group)
			{
				$coord_groups[] = $group['id'];
			}
		}
		
		
		$all_client_patients_q = Doctrine_Query::create()
		->select('pm.ipid,ep.epid')
		->from('PatientMaster pm')
		->where('pm.isdelete = 0')
		->leftJoin('pm.EpidIpidMapping ep')
		->andWhere('ep.clientid=' . $clientid)
		->andWhere('ep.ipid=pm.ipid');
		$all_clipids = $all_client_patients_q->fetchArray();
			
		$all_client_ipids_arr[] = "9999999999999999999999999999";
		foreach($all_clipids as $clipi)
		{
		    $all_client_ipids_arr[] = $clipi['ipid'];
		}
		
		//todos
		$todo = Doctrine_Query::create()
		->select("*")
		->from('ToDos')
		->where('client_id="' . $clientid . '"')
		->andWhere('isdelete="0"')
		->andWhere('iscompleted="0"')
		->andWhereIn('ipid',$all_client_ipids_arr)
		->orderBy('create_date DESC');
		if ($user_type != 'SA')
		{
			if (!in_array($groupid, $coord_groups))
			{
				$todo->andWhere('triggered_by !="system"');
			}

			if ($groupid > 0)
			{
				$sql_group = ' OR group_id = "' . $groupid . '"';
			}
			$todo->andWhere('user_id IN(' . implode(', ', $current_user_group_asignees) . ') ' . $sql_group . '');
		}
		if ($_REQUEST['dbgz'] == '1')
		{
			print_r($todo->getSqlQuery());
		}
		$todo_array = $todo->fetchArray();

		if ($_REQUEST['dbgz'] == '1')
		{
			print_r($todo_array);
			exit;
		}
		$todo_ipids[] = '99999999999';
		if (count($todo_array) > 0)
		{
			//first todo foreach to gather all ipids to avoid 10 second loading as in old todo!
			foreach ($todo_array as $k_todo_d => $v_todo_d)
			{
				$todo_ipids[] = $v_todo_d['ipid'];
			}

			if ($_REQUEST['dbgz'])
			{
				print_r($users2groups);
			}

			//second todo foreach to append data to master_data
			$tabname = 'todo';

			foreach ($todo_array as $k_todo => $v_todo)
			{
				if (!in_array($v_todo['id'], $excluded_events[$tabname]))
				{
					if ($v_todo['user_id'] > '0')
					{
						$user_details = $todo_users[$v_todo['user_id']]['last_name'] . ', ' . $todo_users[$v_todo['user_id']]['first_name'];
					}
					else
					{
						$user_details = '';
					}

					$todo_ipids[] = $v_todo['ipid'];
					if ($v_todo['triggered_by'] != 'system_medipumps')
					{
						if(($v_todo['group_id'] == $groupid && $v_todo['group_id'] != '0') || $v_todo['user_id'] == $userid)
						{
							$due_date = date('Y-m-d', strtotime($v_todo['until_date']));
							$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
							$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_todo['ipid'];
							$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_todo['id'];
							$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_todo['todo'];
							$master_data[strtotime($due_date)][$key_start]['user_id'] = $v_todo['user_id'];
							$master_data[strtotime($due_date)][$key_start]['group_id'] = $v_todo['group_id'];
							$master_data[strtotime($due_date)][$key_start]['todo_user'] = $user_details;
							$master_data[strtotime($due_date)][$key_start]['triggered_by'] = $v_todo['triggered_by'];
							$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
							$key_start++;
						}
					}
					else if ($v_todo['group_id'] == $groupid || $user_type == 'SA') //show system_medipumps only to $v_todo['group_id'] (koord)
					{
						$due_date = date('Y-m-d', strtotime($v_todo['until_date']));
						$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
						$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_todo['ipid'];
						$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_todo['id'];
						$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_todo['todo'];
						$master_data[strtotime($due_date)][$key_start]['user_id'] = $v_todo['user_id'];
						$master_data[strtotime($due_date)][$key_start]['group_id'] = $v_todo['group_id'];
						$master_data[strtotime($due_date)][$key_start]['todo_user'] = $user_details;
						$master_data[strtotime($due_date)][$key_start]['triggered_by'] = $v_todo['triggered_by'];
						$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
						$key_start++;
					}
				}
			}
		}

		$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
		$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
		$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
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
			$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
			$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
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

		$patients = Doctrine_Query::create()
		->select($sql)
		->from('PatientMaster p')
		->whereIn("p.ipid", $todo_ipids)
		->leftJoin("p.EpidIpidMapping e")
		->andWhere('e.clientid = ' . $clientid);
		$patients_res = $patients->fetchArray();
		foreach ($patients_res as $k_pat_todo => $v_pat_todo)
		{
			$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']] = $v_pat_todo;
			$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']]['epid'] = strtoupper($v_pat_todo['EpidIpidMapping']['epid']);
			$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']]['enc_id'] = Pms_Uuid::encrypt($v_pat_todo['id']);
		}
		
		//LIMIT & SORT MASTER DATA
		$user_c = new User();
		$user_c_details = $user_c->getUserDetails($userid);
		$user_dash_limit = $user_c_details[0]['dashboard_limit'];
		$user_dash_limit = 0; // no limit it is needed for print.
		
		
		
		//TODO-1298
		if($grouped_dashbord == '1')
		{
		
			foreach($master_data as $key_time => $time_dval){
				foreach($time_dval as $key => $dval){
					$dash_grouped[$dval['ipid']]['by_tabname'][$dval['tabname']][] = $dval;
					$dash_grouped[$dval['ipid']]['kids'][] = $dval;
					$exp_ipids[] = $dval['ipid'];
				}
			}
			foreach($exp_ipids as $k=>$ipid){
				usort($dash_grouped[$ipid]['kids'], array(new Pms_Sorter('due_date'), "_date_compare"));
			}
			
			if ($_REQUEST['sort_order'] == 'asc')
			{
				$dash_grouped = array_reverse($dash_grouped);
			}
			
			$incr2pat = 1;
			foreach($dash_grouped as $ipid=>$val_arr){
					
				foreach($val_arr['kids'] as $kk=>$kv){
			
					if($user_dash_limit != '0')
					{
			
						if($incr2pat<= $user_dash_limit) {
							if($kk == 0 ){
								$dash_arr[$ipid] = $kv;
							}
							$dash_arr[$ipid]['child_rows'][] = $kv;
						}
					}
					else
					{
							
						if($kk == 0 ){
							$dash_arr[$ipid] = $kv;
						}
						$dash_arr[$ipid]['child_rows'][] = $kv;
					}
				}
				$incr2pat++;
			}
			$master_data_final = $dash_arr;
			
		}
		else
		{
	
				if ($user_dash_limit != '0')
				{
					$incr = 1;
					foreach ($master_data as $k_tabname => $v_events)
					{
						foreach ($v_events as $k_event => $v_event)
						{
		
							if ($incr <= $user_dash_limit)
							{
								$master_data_final[$k_tabname][$k_event] = $v_event;
							}
							$incr++;
						}
					}
				}
				else
				{
					$master_data_final = $master_data;
				}
		
				if ($_REQUEST['sort_order'] == 'desc')
				{
					krsort($master_data_final);
				}
				else
				{
					ksort($master_data_final);
				}
		}

		
// 		die_ancuta($master_data_final); 
		
		$this->view->todo_patients = $todo_patients;
		$this->view->dasboard_events = $master_data_final;
		$this->view->action_label = $action_last_label;
	}
	/**
	 * ISPC-2160 : 02.03.2018	
	 */
	public function managementAction(){
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
		
			$hidemagic = Zend_Registry::get('hidemagic');
			
			$userid = $logininfo->userid;
			$groupid = $logininfo->groupid;
			$user_type = $logininfo->usertype;

			//populate the datatables
			if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
		
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				
				$p_users = new PatientUsers();
				$user_patients = $p_users->getUserPatients($userid); //get user's patients by permission
				
				
				$approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
				$modules = new Modules();
				
				if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
				{
					$this->view->acknowledge_func = "1";
					if(in_array($userid,$approval_users)){
						$this->view->approval_rights = "1";
					} else{
						$this->view->approval_rights = "0";
					}
						
				}
				else
				{
					$this->view->acknowledge_func = "0";
				}
				

				//ISPC-2174 : option to exclude todos for discharged or dead patients (31.05.2018 @ancuta) 
				$exclude_discharged = 0;
				if($_REQUEST['exclude_discharged'] == "1"){
				    $exclude_discharged = "1";
				} 
				$exclude_dead = 0;
				if($_REQUEST['exclude_dead'] == "1"){
				    $exclude_dead = "1";
				} 
				//---
				
				
				
				
				
				
				if(strlen($_REQUEST['tab'])){
					$tab = $_REQUEST['tab'];
				} else{
					$tab = "user_undone";
				}
				
				if(!$_REQUEST['length']){
					$_REQUEST['length'] = "50";
				}
				$limit = (int)$_REQUEST['length'];
				$offset = (int)$_REQUEST['start'];
				$search_value = $_REQUEST['search']['value'];
		
				$columns_array = array(
						"0" => "",
						"1" => "",
						"2" => "todo",
						"3" => "user_id",
						"4" => "group_id",
						"5" => "until_date",
						"6" => "complete_date",
						"7" => "complete_user",
						"8" => "complete_comment"
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
				
				if(strlen($columns_array[$order_column]) > 0 ){
					$order_by_str ='CONVERT(CONVERT('.addslashes(htmlspecialchars($columns_array[$order_column])).' USING BINARY) USING utf8) '.$order_dir;
				} else {
					$order_by_str ='CONVERT(CONVERT(todo USING BINARY) USING utf8) ASC';
				}
				
				// USER  AND GROUPS DETAILS
				$user_c = new User();
				$user_c_details = $user_c->getUserDetails($userid);
				
				// CLIENT USERS
				$client_users = $user_c->getUserByClientid($clientid, 0, true);
				foreach($client_users as $k_c_usr => $v_c_usr)
				{
					$client_users_arr[$v_c_usr['id']] = $v_c_usr;
					$all_users_ids[] = $v_c_usr['id'];
				}
				
				$user_nice_name_array = array();
				if( ! empty($all_users_ids)){
					$user_nice_name_array = $user_c->getUsersNiceName($all_users_ids,$clientid);
				}
				
				
				
				$users2groups = array();
				$groups2users = array();
				foreach($client_users as $k_user => $v_user)
				{
					$todo_users[$v_user['id']] = $v_user;
					$client_users[$v_user['id']] = $v_user;
					$users2groups[$v_user['id']] = $v_user['groupid'];
					$groups2users[$v_user['groupid']][] = $v_user['id'];
				}
				$current_user_group_asignees = $groups2users[$groupid];
				

				//get client coord groups
				$usergroup = new Usergroup();
				$MasterGroups = array("6"); // Koordinator
				$usersgroups = $usergroup->getUserGroups($MasterGroups);
				
				// CLIENT GROUPS DETAILS 
				$client_usersgroups = $usergroup->getClientGroups($clientid);
				if(!empty($client_usersgroups)){
					foreach($client_usersgroups as $k=>$grdata){
						$client_group_details[$grdata['id']] = $grdata; 
					}
				}				
				if(count($usersgroups) > 0)
				{
					foreach($usersgroups as $group)
					{
						$coord_groups[] = $group['id'];
					}
				}
				
				//TODO-3797 Lore 01.02.2021
				// get all pseudogroups details
				$psdgrp = new UserPseudoGroup();
				$client_pseudogroups = $psdgrp->get_pseudogroups_for_todo($clientid);
				$selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();
				//.
				

				//ISPC-2174 : option to exclude todos for discharged or dead patients (31.05.2018 @ancuta)
				// get dead patients and remove them from ipids array
				$dead_patients = array();
				if($exclude_dead == "1"){
				    $PatientDischarge_obj = new PatientDischarge();
				    $patient_death_dates = $PatientDischarge_obj->getPatientsDeathDate($clientid,array());
				
				    if(!empty($patient_death_dates)){
				        $dead_patients = array_keys($patient_death_dates);
				    }
				}
				//--
				
				
				// SEARCh IN PATIENTS
				$search_ipids_arr = array();
				if(!empty($search_value)){
					$search_patients = PatientMaster::search_patients($search_value, array(
							'clientid' => $clientid,
							'isstandby' => 'all',
							'isdischarged' => 'all',
							'isstandbydelete' => 'all',
					    
					));
					if(!empty($search_patients)){
						$search_ipids_arr = array_column(array_column($search_patients, 'PatientMaster'), 'ipid');
					} 
				}

				
				$salt = Zend_Registry::get('salt');
				$sql = "e.ipid,e.epid,p.ipid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "CONVERT(AES_DECRYPT(first_name,'{$salt}') using latin1) as firstname,";
				$sql .= "CONVERT(AES_DECRYPT(last_name,'{$salt}') using latin1)  as lastname";
				
				// if super admin check if patient is visible or not
				if($logininfo->usertype == 'SA')
				{
					$sql = "e.ipid,e.epid,p.ipid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'{$salt}') using latin1),'" . $hidemagic . "') as firstname, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'{$salt}') using latin1),'" . $hidemagic . "') as lastname";
				}
				
				$all_client_patients_q = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->where('p.isdelete = 0')
				->leftJoin('p.EpidIpidMapping e')
				->andWhere('e.clientid = ?',$clientid) // change to user patients
				->andWhere('e.ipid = p.ipid')
				->andWhere('p.ipid IN (' . $user_patients['patients_str'] . ')');
				if(!empty($search_ipids_arr)){
					$all_client_patients_q->andWhereIn('p.ipid',$search_ipids_arr);
				}
				
				
				//ISPC-2174 : option to exclude todos for discharged or dead patients (31.05.2018 @ancuta) 				
				if($exclude_discharged == "1"){
				    $all_client_patients_q->andWhere('p.isdischarged = 0');
				}
				
				// Dead option
				if($exclude_dead == "1" && ! empty($dead_patients)){
					$all_client_patients_q->andWhereNotIn('p.ipid',$dead_patients);
				}
				//--
				
				
				$all_clipids = $all_client_patients_q->fetchArray();
				 
				if(empty($all_clipids)){
					$this->returnDatatablesEmptyAndExit();
				}
				
				$client_patient_users = array();
				foreach($all_clipids as $k=>$pdata)
				{
					$client_patient_users[] = $pdata['ipid'];
					
					$todo_patients[$pdata['EpidIpidMapping']['ipid']] = $pdata;
					$todo_patients[$pdata['EpidIpidMapping']['ipid']]['epid'] = strtoupper($pdata['EpidIpidMapping']['epid']);
					$todo_patients[$pdata['EpidIpidMapping']['ipid']]['enc_id'] = Pms_Uuid::encrypt($pdata['id']);
				}
                $client_patient_users[] = 'XXXXXXXXX'; //fake ipid for voluntary workers, these todo entries are not associated with patients
				
				
				// Get ONLY allowed todos - For a corect count of todo's listed 
				if($tab == "user_undone"){
    				$all_todos = Doctrine_Query::create()
    				->select("id,triggered_by,group_id,user_id,iscompleted")
    				->from('ToDos')
    				->where('client_id=?',$clientid);
    				$all_todos->andWhere('isdelete="0"');
    				$all_todos->andWhereIn('ipid',$client_patient_users); // SHOWS ONLY PATIENTS THAT ARE VISIBLE TO USER!
    				if($tab =="user_undone" || $tab =="client_undone" )
    				{
    				    $all_todos->andWhere('iscompleted="0"');
    				} else { // IF tab =completed
    				    $all_todos->andWhere('iscompleted = "1"');
    				}
    				$all_todos_array = $all_todos->fetchArray();
    
    				
    				$allowed_todos = array();
    				foreach($all_todos_array as $k=>$todo_val){
    				    
    			        if($todo_val['triggered_by'] != 'system_medipumps' && (($todo_val['group_id'] == $groupid && $todo_val['group_id'] != '0') || $todo_val['user_id'] == $userid)  )
    			        {
    			            $allowed_todos[] = $todo_val['id'];
    			        }
    			        else if($todo_val['group_id'] == $groupid || $user_type == 'SA') //show system_medipumps only to $todo_val['group_id'] (koord)
    			        {
    			            $allowed_todos[] = $todo_val['id'];
    			        }
    				}
    				
    				if( empty ( $allowed_todos ) ){
    				    
    				    
    				    $resulted_data = array_values(array());
    				    $response['draw'] = 0; //? get the sent draw from data table
    				    $response['recordsTotal'] = 0;
    				    $response['recordsFiltered'] = 0; // ??
    				    $response['data'] = $resulted_data;
    				    $response['selected_tab'] = $tab;
    				    	
    				    $this->_helper->json->sendJson($response);
    				    
    				}
				}
				
				// Get todos 
				$todo_q = Doctrine_Query::create()
				->select("count(*)")
				->from('ToDos')
				->where('client_id=?',$clientid);
				$todo_q->andWhere('isdelete="0"');
				$todo_q->andWhereIn('ipid',$client_patient_users); // SHOWS ONLY PATIENTS THAT ARE VISIBLE TO USER!
				if($tab == "user_undone"){ 
				    $todo_q->andWhereIn('id',$allowed_todos);
				}  
				if($tab =="user_undone" || $tab =="client_undone" )
				{
					$todo_q->andWhere('iscompleted="0"');
				} else { // IF tab =completed
					$todo_q->andWhere('iscompleted = "1"');
				}
				//$todo_q->orderBy('create_date DESC');
				
				if($tab == "user_undone")
				{
					if($user_type != 'SA')
					{
						if(!empty($coord_groups) && !in_array($groupid, $coord_groups))
						{
							$todo_q->andWhere('triggered_by !="system"');
						}
					
						if($groupid > 0)
						{
							$sql_group = ' OR group_id = "' . $groupid . '"';
						}
						$todo_q->andWhere('user_id IN(' . implode(', ', $current_user_group_asignees) . ') ' . $sql_group . '');
					}
				}  
				
				if (!empty($search_value) && empty($search_ipids_arr)) // if patients found then show todo's of patients
				{
					$regexp = trim($search_value);
					Pms_CommonData::value_patternation($regexp, false, false, true);
					$todo_q->andwhere("todo REGEXP ?",$regexp);
				}
				$todos_array = $todo_q->fetchOne();
				$full_count  = $todos_array['count']; 
				
				if($full_count == 0){
					$this->returnDatatablesEmptyAndExit();
				}

				
				// ########################################
				// #####  Query for details ###############				
				$todo_q->select('*');
				$todo_q->orderBy($order_by_str);
				if($limit != "-1"){
					$todo_q->limit($limit);
				}
				$todo_q->offset($offset);
				$todos_array = $todo_q->fetchArray();

				$print_receipt_ids = array();
				$fax_receipt_ids = array();
				foreach($todos_array as $k_todo_d => $v_todo_d)
				{
					if($v_todo_d['triggered_by'] == "newreceipt_1")
					{
						$print_receipt_ids[] = $v_todo_d['record_id'];
					}
					else if($v_todo_d['triggered_by'] == "newreceipt_2")
					{
						$fax_receipt_ids[] = $v_todo_d['record_id'];
					}
				}
				$receipt_creators_print = Receipts::get_multiple_receipt_print_assign_creators($print_receipt_ids, $clientid);
				$receipt_creators_fax = Receipts::get_multiple_receipt_fax_assign_creators($fax_receipt_ids, $clientid);
 
				
				
				// CREATE FINAL ARRAY
				$tabname = 'todo';
				$triggered_by_arr = array();
// 				$allowed_todos = array();
                //ISPC-2908,Elena,21.05.2021
                //VoluntaryWorkers TODOs need to be parsed
                $aVoluntary = [];
                foreach($todos_array as $k_todo => $v_todo){
                    if($v_todo['triggered_by'] == 'VoluntaryWorkers'){
                        $text_json = json_decode(trim($v_todo['todo_text'], true));
                        if(!empty($text_json) && !empty($text_json['vw_id']) && !in_array($text_json['vw_id'], $aVoluntary)){
                            $aVoluntary[] = $text_json['vw_id'];
                        }
                    }
				
                }
                $vworkers_array = Voluntaryworkers::getClientsVoluntaryworkers(null, $aVoluntary);
                $aVoluntaryOrdered = [];
                foreach($vworkers_array as $vw){
                    $aVoluntaryOrdered[$vw['id']] = $vw;
                }
//print_r($todos_array);

				foreach($todos_array as $k_todo => $v_todo)
				{
				    
				    
				    //creator_details
					if($v_todo['record_id'] != '0')
					{
						if(($v_todo['triggered_by'] == "newreceipt_1" && !empty($receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']])))
						{
							$creator_details[$v_todo['id']] = $user_nice_name_array[$receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']]]['nice_name'];
						}
						else if(($v_todo['triggered_by'] == "newreceipt_2"  && !empty($receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']])))
						{
							$creator_details[$v_todo['id']] =  $user_nice_name_array[$receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']]]['nice_name'];
						}
						else
						{
							$creator_details[$v_todo['id']] = '';
						}
					}
					
					
					//user_details
					if($v_todo['user_id'] > '0')
					{
						$user_details = $user_nice_name_array[$v_todo['user_id']]['nice_name'];
					}
					else
					{
						$user_details = '';
					}
					
					// allowed todos
					/* if($tab == "user_undone"){
						
						if($v_todo['triggered_by'] != 'system_medipumps' && (($v_todo['group_id'] == $groupid && $v_todo['group_id'] != '0') || $v_todo['user_id'] == $userid)  )
						{
							$allowed_todos[] = $v_todo['id'];
						}
						else if($v_todo['group_id'] == $groupid || $user_type == 'SA') //show system_medipumps only to $v_todo['group_id'] (koord)
						{
							$allowed_todos[] = $v_todo['id'];
						}
						
					} else {
						$allowed_todos[] = $v_todo['id'];
					} */
					
					if($v_todo['triggered_by'] != 'system_medipumps')
					{
						if(($v_todo['group_id'] == $groupid && $v_todo['group_id'] != '0') || $v_todo['user_id'] == $userid)
						{
							$triggered_by_arr[$v_todo['id']] = explode("-",$v_todo['triggered_by']);
							$due_date = date('Y-m-d', strtotime($v_todo['until_date']));
			
							if($triggered_by_arr[$v_todo['id']][0] == "medacknowledge")
							{
								$master_data[$v_todo['id']]['triggered_by_info'] = 'medacknowledge';
								$master_data[$v_todo['id']]['medical_change'] = '1';
								$master_data[$v_todo['id']]['hide_checkbox'] = '1';
								if(strlen($triggered_by_arr[$v_todo['id']][1]) > 0){
									$master_data[$v_todo['id']]['drugplan_id'] = $triggered_by_arr[$v_todo['id']][1];
								}
								$master_data[$v_todo['id']]['cocktail_id'] = '0';
							}
			
							elseif($triggered_by_arr[$v_todo['id']][0] == "pumpmedacknowledge")
							{
								$master_data[$v_todo['id']]['triggered_by_info'] = 'pumpmedacknowledge';
								$master_data[$v_todo['id']]['medical_change'] = '1';
								$master_data[$v_todo['id']]['hide_checkbox'] = '1';
								$master_data[$v_todo['id']]['drugplan_id'] = '0';
								if(strlen($triggered_by_arr[$v_todo['id']][1]) > 0){
									$master_data[$v_todo['id']]['cocktail_id'] = $triggered_by_arr[$v_todo['id']][1];
								}
							}
							else
							{
								$master_data[$v_todo['id']]['triggered_by_info'] = '0';
								$master_data[$v_todo['id']]['medical_change'] = '0';
								$master_data[$v_todo['id']]['hide_checkbox'] = '0';
								$master_data[$v_todo['id']]['drugplan_id'] = '0';
								$master_data[$v_todo['id']]['cocktail_id'] = '0';
							}
			
							$master_data[$v_todo['id']]['alt_id'] = $v_todo['record_id'];
							$master_data[$v_todo['id']]['tabname'] = $tabname;
							$master_data[$v_todo['id']]['ipid'] = $v_todo['ipid'];
							$master_data[$v_todo['id']]['event_id'] = $v_todo['id'];
							$master_data[$v_todo['id']]['event_title'] = $v_todo['todo'];
							$master_data[$v_todo['id']]['user_id'] = $v_todo['user_id'];
							$master_data[$v_todo['id']]['group_id'] = $v_todo['group_id'];
							$master_data[$v_todo['id']]['todo_user'] = $user_details;
							$master_data[$v_todo['id']]['receipt_creator_user'] = $creator_details[$v_todo['id']];
							$master_data[$v_todo['id']]['triggered_by'] = $v_todo['triggered_by'];
							$master_data[$v_todo['id']]['due_date'] = date('d.m.Y', strtotime($due_date));
						}
						
							
					}
					else if($v_todo['group_id'] == $groupid || $user_type == 'SA') //show system_medipumps only to $v_todo['group_id'] (koord)
					{
						$due_date = date('Y-m-d', strtotime($v_todo['until_date']));
			
						$triggered_by_arr[$v_todo['id']] = explode("-",$v_todo['triggered_by']);
			
						if($triggered_by_arr[$v_todo['id']][0] == "medacknowledge")
						{
							$master_data[$v_todo['id']]['triggered_by_info'] = 'medacknowledge';
							$master_data[$v_todo['id']]['hide_checkbox'] = '1';
							$master_data[$v_todo['id']]['medical_change'] = '1';
							$master_data[$v_todo['id']]['drugplan_id'] = '0';
							if(strlen($triggered_by_arr[$v_todo['id']][1]) > 0){
								$master_data[$v_todo['id']]['drugplan_id'] = $triggered_by_arr[$v_todo['id']][1];
							}
						}
						elseif($triggered_by_arr[$v_todo['id']][0] == "pumpmedacknowledge")
						{
							$master_data[$v_todo['id']]['triggered_by_info'] = 'pumpmedacknowledge';
							$master_data[$v_todo['id']]['hide_checkbox'] = '1';
							$master_data[$v_todo['id']]['medical_change'] = '1';
							$master_data[$v_todo['id']]['drugplan_id'] = '0';
							if(strlen($triggered_by_arr[$v_todo['id']][1]) > 0){
								$master_data[$v_todo['id']]['cocktail_id'] = $triggered_by_arr[$v_todo['id']][1];
							}
						}
						else
						{
							$master_data[$v_todo['id']]['triggered_by_info'] = '0';
							$master_data[$v_todo['id']]['hide_checkbox'] = '0';
							$master_data[$v_todo['id']]['medical_change'] = '0';
							$master_data[$v_todo['id']]['drugplan_id'] = '0';
							$master_data[$v_todo['id']]['cocktail_id'] = '0';
						}
			
						$master_data[$v_todo['id']]['alt_id'] = $v_todo['record_id'];
						$master_data[$v_todo['id']]['tabname'] = $tabname;
						$master_data[$v_todo['id']]['ipid'] = $v_todo['ipid'];
						$master_data[$v_todo['id']]['event_id'] = $v_todo['id'];
						$master_data[$v_todo['id']]['event_title'] = $v_todo['todo'];
						$master_data[$v_todo['id']]['user_id'] = $v_todo['user_id'];
						$master_data[$v_todo['id']]['group_id'] = $v_todo['group_id'];
						$master_data[$v_todo['id']]['todo_user'] = $user_details;
						$master_data[$v_todo['id']]['triggered_by'] = $v_todo['triggered_by'];
						$master_data[$v_todo['id']]['due_date'] = date('d.m.Y', strtotime($due_date));
					}
					

					// final output
// 					if(in_array($v_todo['id'],$allowed_todos))
// 					{
						$master_data[$v_todo['id']]['patient_data'] = $todo_patients[$v_todo['ipid']]['epid']. ' - '.'<a style="float: none !important;" href="'.APP_BASE.'patientcourse/patientcourse?id='.$todo_patients[$v_todo['ipid']]['enc_id'].'">'.$todo_patients[$v_todo['ipid']]['lastname'].', '.$todo_patients[$v_todo['ipid']]['firstname'].'</a>';
						$master_data[$v_todo['id']]['todo_text'] = $v_todo['todo'];

						if(($v_todo['triggered_by'] == "newreceipt_1" || $v_todo['triggered_by'] == 'newreceipt_2') && strlen(trim(rtrim($creator_details[$v_todo['id']]))) > '0')
						{
							$master_data[$v_todo['id']]['todo_text'] .= '<br style="clear:both;"/><i>die '.$creator_details[$v_todo['id']].'</i>';
						}
                        //ISPC-2908,Elena,21.05.2021
                        //VoluntaryWorkers TODOs need to be extra formatted
						if($v_todo['triggered_by'] == 'VoluntaryWorkers'){
                            $master_data[$v_todo['id']]['patient_data'] = '';
                            $todos_data_array = json_decode(trim($v_todo['todo']), true);
                            $todo_text = '<h4>Ehrenamtliche:</h4>';
                            $todo_text .= '<a href="' . APP_BASE . '/voluntaryworkers/editvoluntaryworkerdet?id='. $todos_data_array['vw_id'] . '">' . $aVoluntaryOrdered[$todos_data_array['vw_id']]['first_name'] . ' ' . $aVoluntaryOrdered[$todos_data_array['vw_id']]['last_name'] . '</a><br>';
                            $todo_text .= $todos_data_array['todo_text'];
                            $master_data[$v_todo['id']]['todo_text'] = $todo_text;

						
                        }
						
						$master_data[$v_todo['id']]['action'] = '';
						if($master_data[$v_todo['id']]['hide_checkbox'] != '1')
						{
							if($user_type == 'SA' || $user_type == 'CA' || $userid == $v_todo['user_id'] || $groupid == $v_todo['group_id'] || in_array($userid, $groups2users[ $v_todo['group_id']]))
							{
							    if($tab == "completed"){
    								$master_data[$v_todo['id']]['action'] .= '<input type="checkbox" name="select_done" value="1" class="undone_event" id="undone_event_'.$k_todo.'" rel="'.$k_todo.'" />';
							        
							    } else{
    								$master_data[$v_todo['id']]['action'] .= '<input type="checkbox" name="select_done" value="1" class="done_event" id="done_event_'.$k_todo.'" rel="'.$k_todo.'" />';
							    }
							}
						}
						else
						{
							$master_data[$v_todo['id']]['action'] .= '';
						}
						
						
						$master_data[$v_todo['id']]['action'] .= ' 
															 <input type="hidden" id="event_done_'.$k_todo.'" value="'.$v_todo['id'].'" style="width:2px!important" />
															 <input type="hidden" id="tabname_'.$k_todo.'" value="todo" style="width:2px!important"/>
															 <input type="hidden" id="done_date_'.$k_todo.'" value="'.date('d.m.Y', strtotime($v_todo['until_date'])).'"  style="width:2px!important"/>
															 <input type="hidden" id="completecomment_'.$k_todo.'" name ="completecomment_'.$k_todo.'" value="" />';
						$master_data[$v_todo['id']]['action'] .= '<div class="loading_div" id="loading_div_'.$k_todo.'" style="display: none;">'.$this->view->translate('loadingpleasewait').'</div>';
						
// 						if($tab == "completed"){
// 							$master_data[$v_todo['id']]['action']  = "";
// 						}
						
						$master_data[$v_todo['id']]['due_date'] = date('d.m.Y', strtotime($v_todo['until_date']));
						
						if(strlen($master_data[$v_todo['id']]['triggered_by']) != 0 && $master_data[$v_todo['id']]['triggered_by_info'] =="medacknowledge" && $master_data[$v_todo['id']]['tabname'] == 'todo')
							{
								if($master_data[$v_todo['id']]['medical_change'] == '1' && $this->view->approval_rights =="1")
									{
										$master_data[$v_todo['id']]['todo_text'] .= '<br style="clear:both;"/><button id="med_approv_'.$v_todo['id'].'" class="med_approve_rights approvem" data-row_id="'.$v_todo['id'].'" data-todoid="'.$master_data[$v_todo['id']]['event_id'].'" data-action="approve" data-patid = "'.$todo_patients[$master_data[$v_todo['id']]['ipid']]['enc_id']
										.'" data-recordid="'.$master_data[$v_todo['id']]['drugplan_id'].'" data-alt_id="'.$master_data[$v_todo['id']]['alt_id'].'">'.$this->view->translate("Approve").'</button><button id="med_decl_'.$v_todo['id'].'" class="med_approve_rights denym"   data-row_id="'.$v_todo['id']
										.'"	data-todoid="'.$master_data[$v_todo['id']]['event_id'].'" data-action="decline"  data-patid = "'.$todo_patients[$master_data[$v_todo['id']]['ipid']]['enc_id']
										.'" data-recordid="'.$master_data[$v_todo['id']]['drugplan_id'].'" data-alt_id="'.$master_data[$v_todo['id']]['alt_id'].'">'.$this->view->translate("Decline").'</button>';
									}
								}
								if(strlen($master_data[$v_todo['id']]['triggered_by']) != 0 && $master_data[$v_todo['id']]['triggered_by_info'] =="pumpmedacknowledge" && $master_data[$v_todo['id']]['tabname'] == 'todo')
									{
										if($master_data[$v_todo['id']]['medical_change'] == '1' && $this->view->approval_rights =="1")
											{
												$master_data[$v_todo['id']]['todo_text'] .='<br style="clear:both;"/><button id="pump_approv_'.$v_todo['id'].'" class="pump_med_approve_rights approvem" data-row_id="'.$v_todo['id'].'" data-todoid="'.$master_data[$v_todo['id']]['event_id']
												.'" data-action="approve" data-patid = "'.$todo_patients[$master_data[$v_todo['id']]['ipid']]['enc_id'].'" data-recordid="'.$master_data[$v_todo['id']]['cocktail_id'].'" data-alt_id="'.$master_data[$v_todo['id']]['alt_id']
												.'">'.$this->view->translate("Approve").'</button><button id="pump_decl_'.$v_todo['id'].'" class="pump_med_approve_rights denym"   data-row_id="'.$v_todo['id'].'"   data-todoid="'.$master_data[$v_todo['id']]['event_id']
												.'" data-action="decline"  data-patid = "'.$todo_patients[$master_data[$v_todo['id']]['ipid']]['enc_id'].'" data-recordid="'.$master_data[$v_todo['id']]['cocktail_id'].'" data-alt_id="'.$master_data[$v_todo['id']]['alt_id'].'">'
										.$this->view->translate("Decline").'</button>';
							}
						}
						
						if($tab == "completed"){
							
							$master_data[$v_todo['id']]['complete_date'] = date("d.m.Y",strtotime($v_todo['complete_date']));
							$master_data[$v_todo['id']]['complete_user'] = $user_nice_name_array[$v_todo['complete_user']]['nice_name'];
							if($v_todo['complete_comment'] != "")
							{
							    //TODO-2641 Ancuta 12.11.2019
								//$master_data[$v_todo['id']]['complete_comment'] = $v_todo['complete_comment'];
								$master_data[$v_todo['id']]['complete_comment'] = htmlentities($v_todo['complete_comment']);
								//-- 
							}
							else 
							{
								$master_data[$v_todo['id']]['complete_comment'] = "-";
							}
						} else{
							$master_data[$v_todo['id']]['complete_date'] = "-";
							$master_data[$v_todo['id']]['complete_user'] = "-";
							$master_data[$v_todo['id']]['complete_comment'] = "-";
						}
						$master_data[$v_todo['id']]['create_user'] =  $user_nice_name_array[$v_todo['create_user']]['nice_name'];
						
										
						$aditional_info_users = array();
						$aditional_info_groups = array();
							
						$ad_info = array();
						
						if($v_todo['user_id']!=0 || $v_todo['group_id'] != 0 ){
						
							$aditional_info_users[$v_todo['id']] = array();
							$aditional_info_groups[$v_todo['id']] = array();
						
							if($v_todo['user_id'] != 0  && strlen($user_nice_name_array[$v_todo['user_id']]['nice_name'])){
								$aditional_info_users[$v_todo['id']][] = $user_nice_name_array[$v_todo['user_id']]['nice_name'];
							}
							if($v_todo['group_id'] != 0 && strlen($client_group_details[$v_todo['group_id']]['groupname']) ){
								$aditional_info_groups[$v_todo['id']][] = $client_group_details[$v_todo['group_id']]['groupname'];
							}
							
							
							if(strlen($v_todo['additional_info'])  > 0 ){
							    $ad_info = explode(";",$v_todo['additional_info']);
							    
							    foreach($ad_info as $info){
							        //TODO-3797 Lore 01.02.2021
							        if( strpos($info, $selectbox_separator_string["pseudogroup"], 0) === 0 ) {
							            $psdgrp_id = substr($info,12);
							            if(!in_array($client_pseudogroups[$psdgrp_id]['servicesname'],$aditional_info_groups[$v_todo['id']])){
							                $aditional_info_groups[$v_todo['id']][] = $client_pseudogroups[$psdgrp_id]['servicesname'];
							            }
							        }
							    }
							}
							
						} else{
							
							if(strlen($v_todo['additional_info'])  > 0 ){
								$ad_info = explode(";",$v_todo['additional_info']);
								foreach($ad_info as $info){
									if(substr($info,0,1) == 'g')
									{
										if(strlen($client_group_details[substr($info,1)]['groupname'])){
											$aditional_info_groups[$v_todo['id']][] = $client_group_details[substr($info,1)]['groupname'];
										}
									}
									elseif(substr($info,0,1) == 'u')
									{
										if(strlen($user_nice_name_array[substr($info,1)]['nice_name'])){
											$aditional_info_users[$v_todo['id']][] = $user_nice_name_array[substr($info,1)]['nice_name'];
										}
									} else {
										$aditional_info_groups[$v_todo['id']][] = "alle";
									}
									
									//TODO-3797 Lore 01.02.2021
									if( strpos($info, $selectbox_separator_string["pseudogroup"], 0) === 0 ) {
									    $psdgrp_id = substr($info,12);
									    if(!in_array($client_pseudogroups[$psdgrp_id]['servicesname'],$aditional_info_groups[$v_todo['id']])){
									        $aditional_info_groups[$v_todo['id']][] = $client_pseudogroups[$psdgrp_id]['servicesname'];
									    }
									}
								}
							} 
						}
						
						$master_data[$v_todo['id']]['assigneduser'] =  implode(";<br/>",$aditional_info_users[$v_todo['id']]);
						$master_data[$v_todo['id']]['assignedgroup'] =  implode(";<br/>",$aditional_info_groups[$v_todo['id']]);
						$master_data[$v_todo['id']]['tab'] =  $tab;
// 					}
				}
				
				$resulted_data = array_values($master_data);
				$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
				$response['recordsTotal'] = $full_count;
				$response['recordsFiltered'] = $full_count; // ??
				$response['data'] = $resulted_data;
				$response['selected_tab'] = $tab;
				
				
			
				$this->_helper->json->sendJson($response);
			}
		
		}
	
	
	
		public function todosactionsAction ()
		{
			set_time_limit(0);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->layout->setLayout('layout_ajax');
				
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
				
			$labels_form = new Application_Form_DashboardActions();
			$todos = new ToDos();
			$dashboard_events = new DashboardEvents();
			$extra = array();
			$data['client'] = $clientid;
			$data['user'] = $userid;
			$data['event'] = $_REQUEST['eventid'];
			$data['tabname'] = $_REQUEST['tabname'];
			$data['source'] = 'u'; //aded by user interactions
			$data['done_date'] = $_REQUEST['donedate']; //aded by user interactions
				
			if($_REQUEST['tabname'] == "anlage" || $_REQUEST['tabname'] == "anlage4awl"){
		
				$data['triggered_by'] = "forced_system";
				$extra  = explode("_",$_REQUEST['extra']);
				$decid = Pms_Uuid::decrypt(end($extra));
				$extra[5] = $decid; // replace encrypted id  with normal id
				$ipid = Pms_CommonData::getIpid($decid);
				$data['ipid'] = $ipid;
				$data['extra'] = implode('_',$extra);
		
			} else {
		
				$data['extra'] = $_REQUEST['extra'];
			}
			$data['due_date'] = date("Y-m-d 00:00:00",strtotime($_REQUEST['donedate']));
				
			$dashboard_labels = new DashboardLabels();
			$action_last_label = $dashboard_labels->getActionsLastLabel();
			$action_last_label['custom_doctor_event_team'] = $action_last_label['custom_team_event'];
				
			$labels_f['0'] = $this->view->translate('select');
			foreach($action_last_label as $k_act_label => $v_act_label)
			{
				if($k_act_label != "custom_doctor_event_team")
				{
					$labels_f[$k_act_label] = $v_act_label['name'];
				}
			}
				
			if($_REQUEST['label_filter'] && $_REQUEST['label_filter'] != '0' && $_REQUEST['label_filter'] != 'undefined')//0=all
			{
				$this->view->label_filter_selected = $_REQUEST['label_filter'];
			}
				
			$this->view->sort_order_selected = $_REQUEST['sort_order'];
				
			$sort_arr = array('asc' => $this->view->translate('asc_sort'), 'desc' => $this->view->translate('desc_sort'));
			$this->view->date_sort = $this->view->formSelect("date_sort", $_REQUEST['sort_order'], '', $sort_arr);
			$this->view->label_filter = $this->view->formSelect("label_filter", $_REQUEST['label_filter'], '', $labels_f);
				
			if($_REQUEST['mode'] == 'undone')
			{
				if($_REQUEST['tabname'] == 'todo' || $_REQUEST['tabname'] == 'old_todo')
				{
					//changed to delete a comment in verlauf entry
					//$save_todo = $todos->uncompleteTodo($_REQUEST['eventid']);
					$save_todo = $todos->uncompleteTodonew($_REQUEST['eventid']);
				}
				elseif($_REQUEST['tabname'] == 'custom_doctor_event_team')
				{
					$save_todo = $dashboard_events->uncomplete_dashboard_event($_REQUEST['eventid']);
				}
				elseif($_REQUEST['tabname'] == 'sgbxi' || $_REQUEST['tabname'] == 'old_sgbxi')
				{
					$save_todo = $dashboard_events->uncomplete_dashboard_event($_REQUEST['eventid']);
				}
					
				$done_entry_id = $_REQUEST['eventid'];
				$labels_event_form = $labels_form->delete_done_entry($done_entry_id);
				echo '1';
				exit;
			}
			elseif($_REQUEST['mode'] == 'done')
			{
				if($_REQUEST['tabname'] == 'todo' || $_REQUEST['tabname'] == 'old_todo')
				{
					//$save_todo = $todos->completeTodo($_REQUEST['eventid']);
					//changed to write a comment in verlauf entry
					$save_todo = $todos->completeTodonew($_REQUEST['eventid'], $_REQUEST['event_comment']);
				}
				elseif($_REQUEST['tabname'] == 'sgbxi' || $_REQUEST['tabname'] == 'old_sgbxi')
				{
					$save_sgbxi = $dashboard_events->complete_dashboard_event($_REQUEST['eventid']);
				}
				elseif($_REQUEST['tabname'] == 'anlage' || $_REQUEST['tabname'] == 'anlage4awl')
				{
					$save_anlage = $dashboard_events->create_dashboard_event($data,true);
				}
					
				$labels_event_form = $labels_form->add_done_entry($data);
				echo '1';
				exit;
			}
		}
		
		
	public function printtodosAction ()
	{
	    setlocale(LC_ALL, 'de_DE.UTF-8');
	    $logininfo = new Zend_Session_Namespace('Login_Info');
		$this->_helper->layout->setLayout('layout_totalblank');
	    $hidemagic = Zend_Registry::get('hidemagic');
	    
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    $this->view->userid = $userid;
	    
	    $groupid = $logininfo->groupid;
	    $user_type = $logininfo->usertype;
	    $done_events = new DashboardActionsDone();
	    $labels_form = new Application_Form_DashboardActions();
	    $todos = new ToDos();
	    $dashboard_events = new DashboardEvents();
	    $wlprevileges = new Modules();
	    $pm = new PatientMaster();
	    	
	    $user_c = new User();
	    $user_c_details = $user_c->getUserDetails($userid);
	    $client_users = $user_c->getUserByClientid($clientid, 0, true);
	    foreach($client_users as $k_c_usr => $v_c_usr)
	    {
	        $client_users_arr[$v_c_usr['id']] = $v_c_usr;
	    }
	    //todo stuff
	    $dashboard_labels = new DashboardLabels();
	    $action_last_label = $dashboard_labels->getActionsLastLabel();
	    $user = new User();
	    $client_users = $user->getUserByClientid($clientid, 0, true);
	    
	    $users2groups[] = '9999999999999';
	    foreach ($client_users as $k_user => $v_user)
	    {
	        $todo_users[$v_user['id']] = $v_user;
	        $users2groups[$v_user['id']] = $v_user['groupid'];
	        $groups2users[$v_user['groupid']][] = $v_user['id'];
	    }
	    
	    $current_user_group_asignees[] = '999999999';
	    $current_user_group_asignees[] = '9999999';
	    $current_user_group_asignees = $groups2users[$groupid];
	    $this->view->group2users = $groups2users;
	    
	    
	    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
	    $modules = new Modules();
	    
	    if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
	    {
	        $this->view->acknowledge_func = "1";
	        if(in_array($userid,$approval_users)){
	            $this->view->approval_rights = "1";
	        } else{
	            $this->view->approval_rights = "0";
	        }
	        	
	    }
	    else
	    {
	        $this->view->acknowledge_func = "0";
	    }
	    	
	    
	    
	    
	    
	    
	    
	    
	    
	    //todos
	    $all_client_patients_q = Doctrine_Query::create()
	    ->select('pm.ipid,ep.epid')
	    ->from('PatientMaster pm')
	    ->where('pm.isdelete = 0')
	    ->leftJoin('pm.EpidIpidMapping ep')
	    ->andWhere('ep.clientid=' . $logininfo->clientid)
	    ->andWhere('ep.ipid=pm.ipid');
	    $all_clipids = $all_client_patients_q->fetchArray();
	    
	    $all_client_ipids_arr[] = "9999999999999999999999999999";
	    foreach($all_clipids as $clipi)
	    {
	        $all_client_ipids_arr[] = $clipi['ipid'];
	    }
	    
	    $todo = Doctrine_Query::create()
	    ->select("*")
	    ->from('ToDos')
	    ->where('client_id="' . $clientid . '"')
	    ->andWhere('isdelete="0"')
	    ->andWhereIn('ipid',$all_client_ipids_arr)
	    ->andWhere('iscompleted="0"')
	    ->orderBy('create_date DESC');
	    if($user_type != 'SA')
	    {
	        if(!in_array($groupid, $coord_groups))
	        {
	            $todo->andWhere('triggered_by !="system"');
	        }
	    
	        if($groupid > 0)
	        {
	            $sql_group = ' OR group_id = "' . $groupid . '"';
	        }
	        $todo->andWhere('user_id IN(' . implode(', ', $current_user_group_asignees) . ') ' . $sql_group . '');
	    }
	    
	    $todo_array = $todo->fetchArray();
	    
	    $todo_ipids[] = '99999999999';
	    $receipt_ids[] = '99999999999999';
	    if(count($todo_array) > 0)
	    {
	        //first todo foreach to gather all ipids to avoid 10 second loading as in old todo!
	        //here ... catch all receipts ids too .. in this way we have the receipt creator //only for "triggered_by = newreceipt_1 and newreceipt_2"
	        $triggered_by_arr = "";
	        foreach($todo_array as $k_todo_d => $v_todo_d)
	        {
	            $todo_ipids[] = $v_todo_d['ipid'];
	    
	            if($v_todo_d['triggered_by'] == "newreceipt_1")
	            {
	            				$print_receipt_ids[] = $v_todo_d['record_id'];
	            }
	            else if($v_todo_d['triggered_by'] == "newreceipt_2")
	            {
	            				$fax_receipt_ids[] = $v_todo_d['record_id'];
	            }
	    
	    
	        }
	    
	        //query to get all receipts involved
	        //				$receipts_creators = Receipts::get_multiple_receipts_creators($receipt_ids, $clientid);
	        $receipt_creators_print = Receipts::get_multiple_receipt_print_assign_creators($print_receipt_ids, $clientid);
	        $receipt_creators_fax = Receipts::get_multiple_receipt_fax_assign_creators($fax_receipt_ids, $clientid);
	    
	        //				print_r($receipt_creators_print);
	        //				print_r($receipt_creators_fax);
	        //				exit;
	        //second todo foreach to append data to master_data
	        $tabname = 'todo';
	        $triggered_by_arr = array();
	        $triggered_by_arr[0] = "";
	        $triggered_by_arr[1] = "";
	    
	    
	        foreach($todo_array as $k_todo => $v_todo)
	        {
	            if(!in_array($v_todo['id'], $excluded_events[$tabname]))
	            {
    				if($v_todo['record_id'] != '0')
    				{
    				    if(($v_todo['triggered_by'] == "newreceipt_1" && !empty($receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']])))
    				    {
    				        $creator_details = $todo_users[$receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']]]['user_title'] . ' ' . $todo_users[$receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']]]['last_name'] . ', ' . $todo_users[$receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']]]['first_name'];
    				    }
    				    else if(($v_todo['triggered_by'] == "newreceipt_2"  && !empty($receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']])))
    				    {
    				        $creator_details = $todo_users[$receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']]]['user_title'] . ' ' . $todo_users[$receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']]]['last_name'] . ', ' . $todo_users[$receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']]]['first_name'];
    				    }
    				    else
    				    {
    				        $creator_details = '';
    				    }
    				}

    				if($v_todo['user_id'] > '0')
    				{
    				    $user_details = $todo_users[$v_todo['user_id']]['user_title'] . ' ' . $todo_users[$v_todo['user_id']]['last_name'] . ', ' . $todo_users[$v_todo['user_id']]['first_name'];
    				}
    				else
    				{
    				    $user_details = '';
    				}
    				$todo_ipids[] = $v_todo['ipid'];

    				if($v_todo['triggered_by'] != 'system_medipumps')
    				{
    				    if(($v_todo['group_id'] == $groupid && $v_todo['group_id'] != '0') || $v_todo['user_id'] == $userid)
    				    {
    				        $triggered_by_arr[$key_start] = explode("-",$v_todo['triggered_by']);
    				        $due_date = date('Y-m-d', strtotime($v_todo['until_date']));




    				        if($triggered_by_arr[$key_start][0] == "medacknowledge")
    				        {
    				            $master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = 'medacknowledge';
    				            $master_data[strtotime($due_date)][$key_start]['medical_change'] = '1';
    				            $master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '1';
    				            if(strlen($triggered_by_arr[$key_start][1]) > 0){
    				                $master_data[strtotime($due_date)][$key_start]['drugplan_id'] = $triggered_by_arr[$key_start][1];
    				            }
    				            $master_data[strtotime($due_date)][$key_start]['cocktail_id'] = '0';
    				        }

    				        elseif($triggered_by_arr[$key_start][0] == "pumpmedacknowledge")
    				        {
    				            $master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = 'pumpmedacknowledge';
    				            $master_data[strtotime($due_date)][$key_start]['medical_change'] = '1';
    				            $master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '1';
    				            $master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
    				            if(strlen($triggered_by_arr[$key_start][1]) > 0){
    				                $master_data[strtotime($due_date)][$key_start]['cocktail_id'] = $triggered_by_arr[$key_start][1];
    				            }
    				        }
    				        else
    				        {
    				            $master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = '0';
    				            $master_data[strtotime($due_date)][$key_start]['medical_change'] = '0';
    				            $master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '0';
    				            $master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
    				            $master_data[strtotime($due_date)][$key_start]['cocktail_id'] = '0';
    				        }

    				        $master_data[strtotime($due_date)][$key_start]['alt_id'] = $v_todo['record_id'];
    				        $master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
    				        $master_data[strtotime($due_date)][$key_start]['ipid'] = $v_todo['ipid'];
    				        $master_data[strtotime($due_date)][$key_start]['event_id'] = $v_todo['id'];
    				        $master_data[strtotime($due_date)][$key_start]['event_title'] = $v_todo['todo'];
    				        $master_data[strtotime($due_date)][$key_start]['user_id'] = $v_todo['user_id'];
    				        $master_data[strtotime($due_date)][$key_start]['group_id'] = $v_todo['group_id'];
    				        $master_data[strtotime($due_date)][$key_start]['todo_user'] = $user_details;
    				        $master_data[strtotime($due_date)][$key_start]['receipt_creator_user'] = $creator_details;
    				        $master_data[strtotime($due_date)][$key_start]['triggered_by'] = $v_todo['triggered_by'];
    				        $master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
    				        $key_start++;
    				    }
    				}
    				else if($v_todo['group_id'] == $groupid || $user_type == 'SA') //show system_medipumps only to $v_todo['group_id'] (koord)
    				{
    				    $due_date = date('Y-m-d', strtotime($v_todo['until_date']));

    				    $triggered_by_arr[$key_start] = explode("-",$v_todo['triggered_by']);

    				    if($triggered_by_arr[$key_start][0] == "medacknowledge")
    				    {
    				        $master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = 'medacknowledge';
    				        $master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '1';
    				        $master_data[strtotime($due_date)][$key_start]['medical_change'] = '1';
    				        $master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
    				        if(strlen($triggered_by_arr[$key_start][1]) > 0){
    				            $master_data[strtotime($due_date)][$key_start]['drugplan_id'] = $triggered_by_arr[$key_start][1];
    				        }
    				    }
    				    elseif($triggered_by_arr[$key_start][0] == "pumpmedacknowledge")
    				    {
    				        $master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = 'pumpmedacknowledge';
    				        $master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '1';
    				        $master_data[strtotime($due_date)][$key_start]['medical_change'] = '1';
    				        $master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
    				        if(strlen($triggered_by_arr[$key_start][1]) > 0){
    				            $master_data[strtotime($due_date)][$key_start]['cocktail_id'] = $triggered_by_arr[$key_start][1];
    				        }
    				    }
    				    else
    				    {
    				        $master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = '0';
    				        $master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '0';
    				        $master_data[strtotime($due_date)][$key_start]['medical_change'] = '0';
    				        $master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
    				        $master_data[strtotime($due_date)][$key_start]['cocktail_id'] = '0';
    				    }

    				    $master_data[strtotime($due_date)][$key_start]['alt_id'] = $v_todo['record_id'];
    				    $master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
    				    $master_data[strtotime($due_date)][$key_start]['ipid'] = $v_todo['ipid'];
    				    $master_data[strtotime($due_date)][$key_start]['event_id'] = $v_todo['id'];
    				    $master_data[strtotime($due_date)][$key_start]['event_title'] = $v_todo['todo'];
    				    $master_data[strtotime($due_date)][$key_start]['user_id'] = $v_todo['user_id'];
    				    $master_data[strtotime($due_date)][$key_start]['group_id'] = $v_todo['group_id'];
    				    $master_data[strtotime($due_date)][$key_start]['todo_user'] = $user_details;
    				    $master_data[strtotime($due_date)][$key_start]['triggered_by'] = $v_todo['triggered_by'];
    				    $master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
    				    $key_start++;
    				}
	            }
	        }
	    }
	    
	    $sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
	    $sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
	    $sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
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
	    if($logininfo->usertype == 'SA')
	    {
	        $sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
	        $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
	        $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
	        $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
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
	    
	    $patients = Doctrine_Query::create()
	    ->select($sql)
	    ->from('PatientMaster p')
	    ->whereIn("p.ipid", $todo_ipids)
	    ->leftJoin("p.EpidIpidMapping e")
	    ->andWhere('e.clientid = ' . $clientid);
	    $patients_res = $patients->fetchArray();
	    foreach($patients_res as $k_pat_todo => $v_pat_todo)
	    {
	        $todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']] = $v_pat_todo;
	        $todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']]['epid'] = strtoupper($v_pat_todo['EpidIpidMapping']['epid']);
	        $todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']]['enc_id'] = Pms_Uuid::encrypt($v_pat_todo['id']);
	    }
	    
	    
	    //TODO END
	    //	TODO-1298
	    $notifications = new Notifications();
	    $user_notification_settings = $notifications->get_notification_settings($userid);
	    
	    $grouped_dashbord = "0";
	    $ModulePrivileges = $modules->get_client_modules($clientid);
	    if($user_notification_settings[$userid]['dashboard_grouped'] == '1' && $ModulePrivileges['156'] == "1" )
	    {
	        $grouped_dashbord = '1';
	    }
	    $this->view->grouped_dashbord = $grouped_dashbord;
	    	
	    //LIMIT & SORT MASTER DATA
	    $user_dash_limit = $user_c_details[0]['dashboard_limit'];
	    
	    

	    if($_REQUEST['sort_order'] == 'desc')
	    {
	        krsort($master_data);
	    }
	    else
	    {
	        ksort($master_data);
	    }
	    
	    	
	    if($grouped_dashbord == "0"){
	    
	        if($user_dash_limit != '0')
	        {
                // no limit is needed for print
	            /* $incr = 1;
	            foreach($master_data as $k_tabname => $v_events)
	            {
	                foreach($v_events as $k_event => $v_event)
	                {
	    
	                    if($incr <= $user_dash_limit)
	                    {
	                        $master_data_final[$k_tabname][$k_event] = $v_event;
	                    }
	                    $incr++;
	                }
	            } */
	            $master_data_final = $master_data;
	        }
	        else
	        {
	            $master_data_final = $master_data;
	        }
	    } else {
	        $master_data_final = $master_data;
	    }
	    	
	    $dashdata = array();
	    $keydashdata = 1;
	    	
	    // get all ipids
	    $all_dash_ipids = array();
	    foreach($master_data_final as $day_event=> $events)
	    {
	        foreach($events as $k_event => $v_event)
	        {
	            if(isset($v_event['ipid']) && !empty($v_event['ipid'])){
	                if(!in_array($v_event['ipid'],$all_dash_ipids)){
	                    $all_dash_ipids[]	 = $v_event['ipid'];
	                }
	            }
	        }
	    }
	    	
	    $ipid2enc_id = array();
	    if(!empty($all_dash_ipids)){
	        $idiipid_sql = Doctrine_Query::create()
	        ->select('id,ipid')
	        ->from('PatientMaster')
	        ->whereIn("ipid",$all_dash_ipids);
	        $idiipid_array = $idiipid_sql->fetchArray();
	    
	    
	        foreach($idiipid_array as $k => $ipval){
	            $ipid2enc_id[$ipval['ipid']] = Pms_Uuid::encrypt($ipval['id']);
	        }
	    }
	    	
	
	    foreach($master_data_final as $day_event=> $events)
	    {
	        foreach($events as $k_event => $v_event)
	        {
	            $dashdata[$keydashdata]['nr'] = $keydashdata;
	            $dashdata[$keydashdata]['label'] =  $action_last_label[$v_event['tabname']]['name'];
	            $dashdata[$keydashdata]['color'] =  $action_last_label[$v_event['tabname']]['color'];
	            $dashdata[$keydashdata]['font_color'] =  $action_last_label[$v_event['tabname']]['font_color'];
	            	
	            if($v_event['triggered_by'] == 'system_medipumps' && $v_event['tab_name'] == 'todo')
	            {
	                $dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.'<a style="float: none !important;" href="'.APP_BASE.'patientcourse/patientcourse?id='.$todo_patients[$v_event['ipid']]['enc_id'].'">'.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'].'</a> - '. $v_event['event_title'];
	                $dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'];
	            }
	            elseif(strlen($v_event['triggered_by']) == 0 && $v_event['tabname'] == 'todo')
	            {
	                $dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.'<a style="float: none !important;" href="'.APP_BASE.'patientcourse/patientcourse?id='.$todo_patients[$v_event['ipid']]['enc_id'].'">'.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'].'</a> - '. $v_event['event_title'];
	                $dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'];
	            }
	            elseif(strlen($v_event['triggered_by']) == 0  && $v_event['triggered_by_info'] =="medacknowledge" && $v_event['tabname'] == 'todo')
	            {
	                $dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$v_event['event_title'];
	                $dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$v_event['event_title'];
	            }
	            elseif(strlen($v_event['triggered_by']) == 0  && ($v_event['tabname'] == 'team_events' || $v_event['tabname'] == 'custom_doctor_event' ||  $v_event['tabname'] == 'custom_team_event'))
	            {
	                $dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$v_event['event_type'].$v_event['event_title'];
	                $dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$v_event['event_type'].$v_event['event_title'];
	            }
	            elseif($v_event['triggered_by'] == 'teammeeting_completed' && $v_event['tabname'] == 'todo')
	            {
	                $dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.'<a style="float: none !important;" href="'.APP_BASE.'patientcourse/patientcourse?id='.$todo_patients[$v_event['ipid']]['enc_id'].'">'.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'].'</a> <br />'. $v_event['event_title'];
	                $dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'];
	            }
	            else
	            {
	                $dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$v_event['event_title'];
	    
	                if($v_event['tabname'] == "todo" ){
	    
	                    if($grouped_dashbord == '0')
	                    {
	                        $dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.'<a style="float: none !important;" href="'.APP_BASE.'patientcourse/patientcourse?id='.$todo_patients[$v_event['ipid']]['enc_id'].'">'.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'].'</a> - '. $v_event['event_title'];
	                    }
	                    $dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'];
	                }
	                elseif($v_event['tabname'] == "sgbxi" )
	                {
	                    $dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$sgbxi_events_patients[$v_event['ipid']]['epid']. ' - '.$sgbxi_events_patients[$v_event['ipid']]['lastname'].', '.$sgbxi_events_patients[$v_event['ipid']]['firstname'];
	                }
	                elseif(in_array($v_event['tabname'],array("anlage4awl","anlage","patient_birthday")) )
	                {
	                    $dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$v_event['event_title_short'];
	                }
	                else{
	                    $dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$v_event['event_title'];
	                }
	    
	            }
	    
	            if(strlen($v_event['todo_user'])>0 && trim($v_event['todo_user']) != ',')
	            {
	                $dashdata[$keydashdata]['column_title'] .= '<br /><div id="event_details"> <i>Wer '.$v_event['todo_user'].'</i></div>';
	            }
	            elseif(strlen($v_event['event_patient'])>0)
	            {
	                $dashdata[$keydashdata]['column_title'] .= '<div id="event_details"> <i>Patient: '.strtoupper($v_event['event_patient']).'</i></div>';
	                $dashdata[$keydashdata]['short_column_title'] .= '<i>Patient: '.strtoupper($v_event['event_patient']).'</i>';
	            }
	            elseif($this->userid != $v_event['todo_user']  && ($v_event['tabname'] == 'team_events' || $v_event['tabname'] == 'custom_doctor_event' ||  $v_event['tabname'] == 'custom_team_event'))
	            {
	                //gol
	            }
	    
	            if(($v_event['triggered_by'] == "newreceipt_1" || $v_event['triggered_by'] == 'newreceipt_2') && strlen(trim(rtrim($v_event['receipt_creator_user']))) > '0')
	            {
	                // 						$dashdata[$keydashdata]['column_title'] .= '<br style="clear:both;"/><div class="width:100%;"><i>'.$this->translate('who_todo_receipt').' '.$v_event['receipt_creator_user'].'</i></div>';
	                $dashdata[$keydashdata]['column_title'] .= '<br style="clear:both;"/><div id="event_details"> <div class="width:100%;"><i>die '.$v_event['receipt_creator_user'].'</i></div></div>';
	                $dashdata[$keydashdata]['short_column_title'] .= '';
	            }
	            if($v_event['tabname'] == "anlage" || $v_event['tabname'] == "anlage4awl")
	            {
	                $dashdata[$keydashdata]['extra'] .= $v_event['extra'];
	            }
	            	
	            $dashdata[$keydashdata]['due_date'] = $v_event['due_date'];
	            $dashdata[$keydashdata]['user_id'] = $v_event['user_id'];
	            $dashdata[$keydashdata]['group_id'] = $v_event['group_id'];
	            $dashdata[$keydashdata]['hide_checkbox'] = $v_event['hide_checkbox'];
	            $dashdata[$keydashdata]['tabname'] = $v_event['tabname'];
	            $dashdata[$keydashdata]['event_id'] = $v_event['event_id'];
	            $dashdata[$keydashdata]['triggered_by'] = $v_event['triggered_by'];
	            $dashdata[$keydashdata]['triggered_by_info'] = $v_event['triggered_by_info'];
	            $dashdata[$keydashdata]['medical_change'] = $v_event['medical_change'];
	            $dashdata[$keydashdata]['ipid'] = $v_event['ipid'];
	            $dashdata[$keydashdata]['drugplan_id'] = $v_event['drugplan_id'];
	            $dashdata[$keydashdata]['alt_id'] = $v_event['alt_id'];
	            $dashdata[$keydashdata]['cocktail_id'] = $v_event['cocktail_id'];
	            $keydashdata++;
	        }
	    }

	    
	    

	    if($_REQUEST['dbgz'] == '1')
	    {
	        print_r(count($dashdata));
	        print_r($dashdata);
	        exit;
	    }
	     
	    
	    
	    if($grouped_dashbord == '1')
	    {
	        foreach($dashdata as $key => $dval){
	            $dash_grouped[$dval['ipid']]['by_tabname'][$dval['tabname']][] = $dval;
	            $dash_grouped[$dval['ipid']]['kids'][] = $dval;
	            $exp_ipids[] = $dval['ipid'];
	        }
	        foreach($exp_ipids as $k=>$ipid){
	            usort($dash_grouped[$ipid]['kids'], array(new Pms_Sorter('due_date'), "_date_compare"));
	        }
	        	
	    
	        $incr2pat = 1;
	        foreach($dash_grouped as $ipid=>$val_arr){
	            	
	            foreach($val_arr['kids'] as $kk=>$kv){
	    
	                /* if($user_dash_limit != '0')
	                {
	    
	                    if($incr2pat<= $user_dash_limit) {
	                        if($kk == 0 ){
	                            $dash_arr[$ipid] = $kv;
	                        }
	    
	                        $dash_arr[$ipid]['child_rows'][] = $kv;
	                        $dash_arr[$ipid]['by_tabname'][$kv['tabname']][] = $kv;
	                    }
	                }
	                else
	                { */
	                    	
	                    if($kk == 0 ){
	                        $dash_arr[$ipid] = $kv;
	                    }
	                    	
	                    $dash_arr[$ipid]['child_rows'][] = $kv;
	                    $dash_arr[$ipid]['by_tabname'][$kv['tabname']][] = $kv;
	                /* } */
	            }
	            $incr2pat++;
	        }
	        	
	        	
	        $full_count = count($dash_arr);
	    
	        if($limit != "" && $offset != "")
	        {
	            $dashdatalimit = array_slice($dash_arr, $offset, $limit, true);
	            $dashdatalimit = Pms_CommonData::array_stripslashes($dashdatalimit);
	        }
	        else
	        {
	            $dashdatalimit = $dash_arr;
	        }
	    
	    
	        $row_id = 0;
	        $link = "";
	        $resulted_data = array();
	    
	        foreach($dashdatalimit as $report_id =>$mdata)
	        {
	            $resulted_data[$row_id]['nr'] = $mdata['nr'];
	            if(isset($mdata['ipid']) && !empty($mdata['ipid'])){
	                $resulted_data[$row_id]['column_title'] = sprintf('<a href="'.APP_BASE.'patientcourse/patientcourse?id=%s">%s</a>',$ipid2enc_id[$mdata['ipid']],$mdata['short_column_title']);
	            } else{
	                $resulted_data[$row_id]['column_title'] = $mdata['short_column_title'];
	            }
	            	
	            $child_row_id = 0;
	            foreach($mdata['child_rows'] as $ch_id =>$child_data)
	            {
                    $link = '%s ';
                    $resulted_data[$row_id]['child_rows'][$child_row_id]['nr'] = sprintf($link,$child_data['nr']);
                    $resulted_data[$row_id]['child_rows'][$child_row_id]['action'] = '<div id="preview-label" class="dashboard_label" style="float: left;background:'.$child_data['color'].'"><span style="width: 20px; float:left;">';
                    if($child_data['hide_checkbox'] != '1')
                    {
                        if($child_data['tabname'] == 'todo')
                        {
                            if($user_type == 'SA' || $user_type == 'CA' || $userid == $child_data['user_id'] || $groupid == $child_data['group_id'] || in_array($userid, $groups2users[$groupid]))
                            {
                                $resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= '</span>';
                            }
                        }
                        elseif($child_data['tabname'] == 'medications')
                        {
                            $resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= ' </span>';
                        }
                        else
                        {
                            $resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= '</span>';
                        }
                        	
                        if($child_data['tabname'] == 'anlage' || $child_data['tabname'] == 'anlage4awl' )
                    	{
                            $resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= '</span>';
                        }
                        	
                    }
                    else
                    {
                        $resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= '</span>';
                    }
                    
                    $resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= '<span id="preview-font" style="color:'.$child_data['font_color'].'">'.$child_data['label'].'</span></div>';
                    $resulted_data[$row_id]['child_rows'][$child_row_id]['column_title'] = $child_data['column_title'];
                    $resulted_data[$row_id]['child_rows'][$child_row_id]['tabname'] = $child_data['tabname'];
                    if(strlen($child_data['triggered_by']) != 0 && $child_data['triggered_by_info'] =="medacknowledge" && $child_data['tabname'] == 'todo')
                    {
                        if($child_data['medical_change'] == '1' && $this->view->approval_rights =="1")
                        {
                            $resulted_data[$row_id]['child_rows'][$child_row_id]['column_title'] .= '  </div>';
                    }
                    }
                    if(strlen($child_data['triggered_by']) != 0 && $child_data['triggered_by_info'] =="pumpmedacknowledge" && $child_data['tabname'] == 'todo')
                    {
                        if($child_data['medical_change'] == '1' && $this->view->approval_rights =="1")
                        {
                            $resulted_data[$row_id]['child_rows'][$child_row_id]['column_title'] .=' </div>';
                        }
                    }
                    $resulted_data[$row_id]['child_rows'][$child_row_id]['due_date'] = '<div class="done_container">'.$child_data['due_date'].'</div>';
                    
                    $child_row_id++;
                }
	                	
                $row_id++;
            }
	    
        } else {
	    
            $full_count = count($dashdata);
            if($limit != "" && $offset != "")
            {
                $dashdatalimit = array_slice($dashdata, $offset, $limit, true);
                $dashdatalimit = Pms_CommonData::array_stripslashes($dashdatalimit);
            }
            else
            {
                $dashdatalimit = $dashdata;
            }
	    
	            $dashdatalimit = $dashdata;
	    
	            $row_id = 0;
	            $link = "";
	            $resulted_data = array();
				foreach($dashdatalimit as $report_id =>$mdata)
				{
					$link = '%s ';
                    $resulted_data[$row_id]['nr'] = sprintf($link,$mdata['nr']);
                    $resulted_data[$row_id]['tabname'] = $mdata['tabname'];

                    $resulted_data[$row_id]['action']  = '<div id="preview-label" class="dashboard_label" style="float: left;width:80px;background:'.$mdata['color'].'">';
                    $resulted_data[$row_id]['action'] .= '<span style="width: 20px; float:left;"></span>';
					$resulted_data[$row_id]['action'] .= '<span id="preview-font" style="color:'.$mdata['font_color'].'">'.$mdata['label'].'</span>';
					$resulted_data[$row_id]['action'] .= '</div>';
					
                    $resulted_data[$row_id]['column_title'] = $mdata['column_title'];
                    
                    if(strlen($mdata['triggered_by']) != 0 && $mdata['triggered_by_info'] =="medacknowledge" && $mdata['tabname'] == 'todo')
					{
						if($mdata['medical_change'] == '1' && $this->view->approval_rights =="1")
						{
							$resulted_data[$row_id]['column_title'] .= '</div>';
                        }
					}
					if(strlen($mdata['triggered_by']) != 0 && $mdata['triggered_by_info'] =="pumpmedacknowledge" && $mdata['tabname'] == 'todo')
                    {
                        if($mdata['medical_change'] == '1' && $this->view->approval_rights =="1")
                        {
							$resulted_data[$row_id]['column_title'] .='</div>';
                        }
                    }
														
                    $resulted_data[$row_id]['due_date'] = '<div class="done_container">'.$mdata['due_date'].'</div>';
	    
                    $row_id++;
                }
            }	    
// 	    dd($resulted_data);
	    
	    
	    $this->view->dasboard_events = $resulted_data;
	    $this->view->action_label = $action_last_label;
	    
	    
	    
	}

	
	/**
	 * @author Ancuta 29.11.2019
	 * 
	 */
	public function clientspecificAction(){
	    
	    
	    $previleges = new Modules();
	    $module207_allow_todos= $previleges->checkModulePrivileges("207", $this->logininfo->clientid);
	    if(!$module207_allow_todos)
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	    
	    $blockname = 'block_todos';
	    $blockname_val = array();
	    $todo_users = $this->get_nice_name_multiselect();
	    $blockname_val['todo_users'] = $todo_users;
	    $triggered_for_values = array('both', 'active', 'standby'); //TODO-3332 Carmen 17.08.2020

	    // get saved data
	    $all_client_todos = array();
	    $all_client_todos= Doctrine_Query::create()
	    ->select('*')
	    ->from('ClientTodos')
	    ->where('isdelete = 0')
	    ->andWhere('clientid= ?', $this->logininfo->clientid)
	    ->fetchArray();
	    
	    $blockname_val['client_todos_values'] = $all_client_todos;
	    //TODO-3332 Carmen 17.08.2020
	    foreach($blockname_val['client_todos_values'] as $kr => &$vr)
	    {
	    	foreach($vr as $kc => &$vc)
	    	{
	    		if($kc == 'triggered_for')
	    		{
	    			$vc = array_search($vc, $triggered_for_values);
	    		}
	    	}
	    }
	    //--
	    $this->view->{$blockname} = $blockname_val;
	    
		//TODO-3332 Carmen 17.08.2020
		$this->view->triggered_for =  array(
				'0' => $this->translator->translate(ClientTodos::LANGUAGE_ARRAY)['[both]'],
				"1" => $this->translator->translate(ClientTodos::LANGUAGE_ARRAY)['[active]'],
				"2" => $this->translator->translate(ClientTodos::LANGUAGE_ARRAY)['[standby]']
		);
		//--
	   
	    if($this->getRequest()->isPost())
	    {
	        $post = $_POST;
	        //First clear old data
	        $qdel = Doctrine_Query::create()
	        ->delete("ClientTodos")
	        ->where('clientid= ?',$this->logininfo->clientid);
	        $qdelexec = $qdel->execute();
	        
	        
	        // insert new data
	        $insert_data  = array();
	        if(!empty($post['todos'])){
	           foreach($post['todos'] as $todo_row=>$todo_data){
	               if( !empty($todo_data['text']) ) {
    	               $insert_data[] = array(
    	                  'clientid'=> $this->logininfo->clientid,
    	                  'todo'=> $todo_data['text'],
    	                  'todo_recipients'=> $todo_data['user'],
    	                  'triggered_by'=> 'patient_admission',
    	               	  'triggered_for'=> $triggered_for_values[$todo_data['triggered_for']], //TODO-3332 Carmen 17.08.2020
    	               ) ;
	               }
	           }
	        }

	        //insert all
	        $collection = new Doctrine_Collection('ClientTodos');
	        $collection->fromArray($insert_data);
	        $collection->save();
	        
	        
	        $this->_redirect(APP_BASE . "todos/clientspecific");
	        exit;
	        
	    }
	    
	}
	
    /**
     *  ISPC-2491 29.11.2019
     * @return boolean|multitype:NULL multitype:string  multitype:Ambigous <string, NULL, string, Ambigous <string, Zend_View_Helper_Translate>>  multitype:Ambigous <>
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
	        	
	        $userarray = array_filter($userarray,function($row){
	            return $row['isactive'] == 0 && $row['isdelete'] == 0;
	        });
	    } else {
	        $users = new User();
	        $userarray = $users->getUserByClientid($this->logininfo->clientid);
	    }
	
	    	
	    User::beautifyName($userarray);
	    	
	    $userarraytodo = array();
	    foreach ($userarray as $user)
	    {
	        $userarraytodo[$selectbox_separator_string['user'] . $user['id']] = $user['nice_name'];
	    }
	
	    asort($userarraytodo);
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
	                $pseudogrouparraytodo[$selectbox_separator_string['pseudogroup'] . $row['id']] = $row['servicesname'];
	        }
	        	
	        $todousersarr[$this->view->translate('liste_user_pseudo_group')] = $pseudogrouparraytodo;
	    }
	    $todousersarr[$this->view->translate('users')] = $userarraytodo;
	    return $todousersarr;
	}
	
	
}

?>