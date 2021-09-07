<?php

// 	class TeamController extends Zend_Controller_Action {
class TeamController extends Pms_Controller_Action 
{
// Maria:: Migration ISPC to CISPC 08.08.2020
// 		private $logininfo = null;
		
		public function init()
		{
			/* Initialize action controller here */
// 			$this->logininfo = new Zend_Session_Namespace('Login_Info');
			
			array_push($this->actions_with_js_file, "teammeeting");
			array_push($this->actions_with_js_file, "teamevent");
		}

		public function teamlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('team', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$user_groups = Doctrine_Query::create()
			->select('*')
			->from('Usergroup')
			->where('clientid = ?', $logininfo->clientid)
			->andWhere('isdelete = ?',0);
			$user_groups_array= $user_groups->fetchArray();
				
			$group_array[0] = "All";
			foreach($user_groups_array as $k=>$gr_data){
				$group_array[$gr_data['id']] = $gr_data['groupname'];
			}
			$this->view->group=$group_array;
			//print_r($this->view->group); exit;
		}

		public function fetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$usertype = $logininfo->usertype;
			$this->view->usertype = $usertype;
			$this->view->userid = $logininfo->userid;
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('team', $logininfo->userid, 'canview');
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			
			$showinfo = new Modules();
			$emergency_duty_module = $showinfo->checkModulePrivileges("96", $logininfo->clientid);
			
			if($emergency_duty_module && ($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA')){
				$this->view->emergency_duty_module = "1";
			} else{
				$this->view->emergency_duty_module = "0";
			}
			
			if($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA'){
				$this->view->write_comment = "1";
			} else{
				$this->view->write_comment = "0";
			}
 
			$columnarray = array("pk" => "id", "ln" => "last_name");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$user = Doctrine_Query::create()
				->select('count(*)')
				->from('User')
				->where('isdelete =  0 and isactive=0')
				->andWhere('clientid = ?', $logininfo->clientid)
				->andWhere("usertype != 'SA'") ;
				//->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$userexec = $user->execute();
			$userarray = $userexec->toArray();

			$limit = 500;
			$user->select('*');			
			$user->limit($limit);
			$user->offset($_GET['pgno'] * $limit);
			if (isset($columnarray[strtolower($_GET['clm'])]) && isset($orderarray[strtoupper($_GET['ord'])])) {
				$user->orderBy($columnarray[strtolower($_GET['clm'])] . " " . $orderarray[strtoupper($_GET['ord'])]);
			}
			
			$userlimitexec = $user->execute();
			$usserlimit = $userlimitexec->toArray();
 
			//rint_r($usserlimit); exit;
			
			$user_groups = Doctrine_Query::create()
			->select('*')
			->from('Usergroup')
			->where('clientid=' . $logininfo->clientid)
			->andWhere('isdelete=0');
			$user_groups_array= $user_groups->fetchArray();
			
			$group_array[0] = "";
			foreach($user_groups_array as $k=>$gr_data){
				$group_array[$gr_data['id']] = $gr_data['groupname'];
			}
			//print_r($group_array); exit;
			
			$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "listteam.html");
			$grid->group_array = $group_array;
			
			$grid->emergency_duty_module = $this->view->emergency_duty_module;
			$this->view->teamgrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("teamnavigation.html", 5, $_GET['pgno'], $limit);
			
			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['teamlist'] = $this->view->render('team/fetchlist.html');

			echo json_encode($response);
			exit;
		}

		public function teammeetingAction()
		{
			set_time_limit(0);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$usertype = $logininfo->usertype;
			$userid = $logininfo->userid;
			$hidemagic = Zend_Registry::get('hidemagic');
			$case = strtolower($_REQUEST['case']);//Maria:: Migration CISPC to ISPC 22.07.2020

			$months_details[0]['start'] = date('Y-m-d');
			
			if(!$logininfo->clientid)
			{
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}

			/*$modules = new Modules();
			if($modules->checkModulePrivileges("92", $logininfo->clientid))
			{
				$this->view->add_extra_patients = "1";
			}
			else
			{
				$this->view->add_extra_patients = "0";
			}
			
			if($modules->checkModulePrivileges("93", $logininfo->clientid))
			{
				$location_option = "1";
			}
			else
			{
				$location_option = "0";
			}
				$this->view->location_option = $location_option ;
			
			if($modules->checkModulePrivileges("110", $logininfo->clientid))
			{
				$statust = "1";
			}
			else
			{
				$statust = "0";
			}
			$this->view->status_t = $statust ;*/
			
			
			//ISPC-2138 make the XT coloumn client specific
			/*ISPC - 2161 $showXTcolumn  = $modules->checkModulePrivileges("160", $logininfo->clientid);
			$this->view->showXTcolumn = $showXTcolumn ;*/
			
			//ISPC-2161 p.1 - if activated display a LINE with "TODO: and list all TODO comma seprated (TODO; WHO)
			//$show_TODO_row  = $modules->checkModulePrivileges("161", $logininfo->clientid);
			//$this->view->show_TODO_row  = $show_TODO_row ;
			
			//ISPC-2161 get the client details , we will need ['teammeeting_settings']; in pdf
			$Client_entity =  new Client();
			$ClientDetails = $Client_entity->findOneById();
			$this->view->client_details = $ClientDetails;
			$this->view->client_teammeeting_settings = $ClientDetails['teammeeting_settings'];
			//ISPC - 2161
			if($ClientDetails['teammeeting_settings']['addextrapat'] == 'yes')
			{
				$this->view->add_extra_patients = "1";
			}
			else
			{
				$this->view->add_extra_patients = "0";
			}
			
			if($ClientDetails['teammeeting_settings']['onlyactivepat'] == 'yes')
			{
				$location_option = "1";
			}
			else
			{
				$location_option = "0";
			}

			// IM-141 if we need station or consil patients
			if($case === 'station' || $case === 'consil'){
			    $location_option = "0";
            }
			$this->view->location_option = $location_option ;
			$this->view->case = $case;
			if($ClientDetails['teammeeting_settings']['statusdrop'] == 'yes')
			{
				$statust = "1";
			}
			else
			{
				$statust = "0";
			}
			$this->view->status_t = $statust ;
			if($ClientDetails['teammeeting_settings']['xt'] == 'yes')
			{
				$this->view->showXTcolumn = $showXTcolumn = true;
			}
			else
			{
				$this->view->showXTcolumn = $showXTcolumn = false;
			}
			
			
			
			if($ClientDetails['teammeeting_settings']['showtodos'] != 'no')
			{
				$this->view->show_TODO_row = $show_TODO_row = true;
			}
			else
			{
				$this->view->show_TODO_row = $show_TODO_row = false;
			}
			
			//ISPC - 2161
			//ISPC-2681 Lore 13.10.2020
			if($ClientDetails['teammeeting_settings']['targets'] != 'no'){ // STUPID - I KNOW
			    $this->view->showTargetsColumn = $showTargetsColumn = true;
			}else {
			    $this->view->showTargetsColumn = $showTargetsColumn = false;
			}
			if($ClientDetails['teammeeting_settings']['todo'] != 'no'){
			    $this->view->showTodoColumn = $showTodoColumn = true;
			}else {
			    $this->view->showTodoColumn = $showTodoColumn = false;
			}
			if($ClientDetails['teammeeting_settings']['users'] != 'no'){
			    $this->view->showUsersColumn = $showUsersColumn = true;
			}else {
			    $this->view->showUsersColumn = $showUsersColumn = false;
			}
			if($ClientDetails['teammeeting_settings']['events'] == 'yes'){
			    $this->view->showEventsColumn = $showEventsColumn = true;
			}else {
			    $this->view->showEventsColumn = $showEventsColumn = false;
			}
			if($ClientDetails['teammeeting_settings']['contact'] == 'yes'){
			    $this->view->showContactColumn = $showContactColumn = true;
			}else {
			    $this->view->showContactColumn = $showContactColumn = false;
			}
			
			//Teambesprechung Behandelt von 
			if($ClientDetails['teammeeting_settings']['tbyusers'] == 'yes') {
			    $this->view->show_assigned_doctors = $show_assigned_doctors = true;
			} else {
			    $this->view->show_assigned_doctors = $show_assigned_doctors = false;
			}
			//Teambesprechung Pat.Nr
			
			if($ClientDetails['teammeeting_settings']['epid'] != 'no') {
			    $this->view->show_epid = $show_epid = true;
			} else {
			    $this->view->show_epid = $show_epid = false;
			}
			
			//Teambesprechung Maßnahme
			if($ClientDetails['teammeeting_settings']['action'] != 'no') {
			    $this->view->show_action = $show_action = true;
			} else {
			    $this->view->show_action = $show_action = false;
			}
			//.
			
			//ISPC-2896 Lore 19.04.2021
			if($ClientDetails['teammeeting_settings']['treatment_process'] != 'no'){
			    $this->view->show_treatment_process = $show_treatment_process = true;
			} else {
			    $this->view->show_treatment_process = $show_treatment_process = false;
			}
			
			//ISPC-2896 Lore 23.04.2021
			if($ClientDetails['teammeeting_settings']['show_problems'] == 'yes'){
			    $this->view->show_problems = $show_problems = true;
			} else {
			    $this->view->show_problems = $show_problems = false;
			}
			
			$statusarray = array(
					"0" => "",
					"1" => "rehabilitativ",
					"2" =>  "pre-final",
					"3" =>  "final",
					"4" =>  "terminal",
					
			);
			
			//print_r($this->view->status_t)
			
			
			// MODULE for location
			
			// get locations 
			$client_locations= Locations :: getLocations($clientid);	
			
			//ISPC-1814 - WL Vollversorgung
			if ($location_option){			
				$Vollversorgung = array("id"=>"WL_Vollversorgung",
						"location"=>"WL Vollversorgung"
				);
				$client_locations[] = $Vollversorgung;
			}
			
			$this->view->client_locations = $client_locations;
			
			foreach($client_locations as $k=>$vl){
				$locations_ids[] = $vl['id'];
				$location_details[$vl['id']] = $vl;
			}
			$this->view->location_details = $location_details;
			if(empty($locations_ids)){
				$locations_ids[] = "XXXXXX";
			}
			
			$sql = "p.ipid,e.ipid,e.epid,convert(AES_DECRYPT(p.kontactnumber,'" . Zend_Registry::get('salt') . "') using latin1) as p.kontactnumber";
			//ISPC-1814
			if ($location_option){
				$sql .= " , p.vollversorgung";
				$WL_Vollversorgung = 0;
			}
			
			$active_ipids = array();
			
			$active_ipids_details = Pms_CommonData::patients_active($sql, $clientid, $months_details, false, 'e.epid_num', 'ASC');
			$active_epids = array();         //ISPC-2681 Lore 14.10.2020
			foreach($active_ipids_details as $k_active_patient => $v_active_patient)
			{
				$active_ipids[] = $v_active_patient['ipid'];
				$active_epids[] = $v_active_patient['epid'];                 //ISPC-2681 Lore 14.10.2020
			}
			
			if(empty($active_ipids)){
				$active_ipids[] = "XXXXXX";
			}


			//ISPC-1814
			//get history so we can also get/count ended today ones
			if ($location_option){
				$vvhistory = new VollversorgungHistory();
				$historyvv = $vvhistory->getVollversorgungHistoryAll($active_ipids);
				// ! if you change getVollversorgungHistoryAll order by id ASC, this will NOT work
				foreach($historyvv as $v){
					$historyvv[ $v['ipid'] ] = $v;
				}
				
				foreach($active_ipids_details as $k_active_patient => $v_active_patient)
				{
					if( $v_active_patient['PatientMaster']['vollversorgung']){
						$WL_Vollversorgung++;
					}elseif(isset($historyvv[ $v_active_patient['ipid'] ])){
						if ($historyvv[ $v_active_patient['ipid'] ]['date_type'] == "1" || strtotime($historyvv[ $v_active_patient['ipid'] ]['date']) >=strtotime(date("Y-m-d")) ){
							//count this also
							$WL_Vollversorgung++;
						}
					}
				}
			}
			
			// get patients in active locations 
			$ql = Doctrine_Query::create()
			->select('count(*) as patients2location,location_id')
			->from('PatientLocation')
			->where('clientid = ?', $logininfo->clientid)
			->andWhere('isdelete = 0')
			->andWhereIn('location_id',$locations_ids)
			->andWhere('valid_till="0000-00-00 00:00:00"')
			->andWhereIn('ipid',$active_ipids)
			->orderBy('ipid ASC')
			->groupBy('location_id');
			$patientsl = $ql->fetchArray();
			
			foreach($patientsl as $k=>$vpl){
				$patients2location[$vpl['location_id']] = $vpl['patients2location'];
			}
			
			foreach($client_locations as $kl=>$vl){
				if($patients2location[$vl['id']] > 0 ){
					$location_patients_count[$vl['id']] =$patients2location[$vl['id']]; 
				} else{
					$location_patients_count[$vl['id']] = 0; 
				}
			}
			//ISPC-1814
			if ($location_option){
				$location_patients_count['WL_Vollversorgung'] = $WL_Vollversorgung;
			}
			$this->view->location_patients_count = $location_patients_count;

			
			

			//attending users and groups
			$ug = new Usergroup();
			$grouparr = $ug->getClientGroups($clientid);
			foreach($grouparr as $k_group => $v_group)
			{
				$client_groups[$v_group['id']] = $v_group;
			}

			$attendee_users = User::get_meeting_attendee_users($clientid);
			$attendee_users = $this->array_sort($attendee_users, 'last_name', SORT_ASC);
			
			$this->view->attendee_users = $attendee_users;

			foreach($attendee_users as $k_user => $v_user)
			{
				if($v_user['groupid'] != '0')
				{
					$available_user_groups[$v_user['groupid']] = $client_groups[$v_user['groupid']]['groupname'];
				}
			}
			$this->view->available_groups = $available_user_groups;

			//patient history users
			$all_users = Pms_CommonData::get_client_users($clientid, true);

			$all_users = $this->array_sort($all_users, 'last_name', SORT_ASC);

			foreach($all_users as $keyu => $user)
			{
				//all users is used in history!
				$all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];

				if($user['usertype'] != 'SA')
				{
					$usr['id'] = $user['id'];
					$usr['value'] = $user['user_title'] . " ". $user['last_name'] . ", " . $user['first_name'];

					$js_users_array[] = $usr;

					//used in pdf
					$cli_usr_names['user_title'] = $user['user_title'];
					$cli_usr_names['last_name'] = $user['last_name'];
					$cli_usr_names['first_name'] = $user['first_name'];
					$client_users[$user['id']] = $cli_usr_names;
				}
			}

			$this->view->allusers = $all_users_array;
			$this->view->js_all_users = $js_users_array;
			$this->view->client_users = $client_users;

			
			//overwrite $js_users_array, $this->view->js_all_users that is used on todo select
			$todo_selectbox_users = $this->get_nice_name_multiselect();
			unset($todo_selectbox_users[0]);
			$flat_todo_users_selectbox = array();
			foreach($todo_selectbox_users as $k=>$row_user) {
				if ( ! is_array( $row_user)) { $row_user = array($k => $row_user); }
				$flat_todo_users_selectbox = array_merge($flat_todo_users_selectbox, $row_user );
			}
			$js_users_array =  array();
			foreach($flat_todo_users_selectbox as $k=>$v) {
				$js_users_array[] = array("id" => $k , "value" => $v);
			}
			$this->view->js_all_users = $js_users_array;
			$this->view->client_users_flat = $flat_todo_users_selectbox;
		
			//get all team meetings of client and create select
			$all_meetings = TeamMeeting::get_team_meetings_for_case($clientid, $case, 'from_time',"DESC");//Maria:: Migration CISPC to ISPC 22.07.2020
			$last_meeting_id = "0";

// 			print_r($all_meetings); exit;
			
			foreach($all_meetings as $k_meeting => $v_meeting)
			{
				if($_REQUEST['new_meeting'])
				{
					$meeting_selector[0] = '---';
				}
				$meeting_selector[$v_meeting['id']] = date('d.m.Y', strtotime($v_meeting['from_time'])) . ' ' . date('H:i', strtotime($v_meeting['from_time'])) . ' - ' . date('H:i', strtotime($v_meeting['till_time']));

				if($_REQUEST['meetingid'] == $v_meeting['id'] && !empty($_REQUEST['meetingid']))
				{
					$last_meeting_id = $v_meeting['id'];
				}
				else if(empty($_REQUEST['meetingid']))
				{
// 					$last_meeting_id = $v_meeting['id'];
					$last_meeting_id = $all_meetings[0]['id']; // last meeting -  Now being the first in the array-  is orderd desc
				}
			}

			if(count($all_meetings) == '0' && !$this->getRequest()->isPost() && $_REQUEST['new_meeting'] != '1')
			{
				if($case === 'station' || $case === 'konsil'){
                    $this->redirect(APP_BASE . 'team/teammeeting?case='. $case . '&new_meeting=1');
                }else{
                    $this->redirect(APP_BASE . 'team/teammeeting?new_meeting=1');
                }

				exit;
			}

			if($_POST['new_meeting'])
			{
				$last_meeting_id = '0';
			}

			if(empty($meeting_selector))
			{
				$meeting_selector[0] = $this->view->translate('no_team_meetings_saved_select');
			}

			if($_REQUEST['meetingid'] > '0')
			{
				$this->view->meetingid = $_REQUEST['meetingid'];
				$meeting_id = $_REQUEST['meetingid'];
			}
			else
			{
				$this->view->meetingid = $last_meeting_id;
				$meeting_id = $last_meeting_id;
			}

			$this->view->meeting_selector = $meeting_selector;

			
			if($last_meeting_id != '0')
			{
				$meeting_data = TeamMeeting::get_meeting_details($last_meeting_id);
				
				$location_id = "0";
					
				if($_REQUEST['location_id'] > '0')
				{
					$location_id = $_REQUEST['location_id'];
				}
				else
				{
					$location_id = $meeting_data['patients_location'];
				}
				
			}
			
			
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$meeting_form = new Application_Form_TeamMeeting();
				$post = $_POST;
				$post['clientid'] = $clientid;
				
				if(strlen($post['meeting_name'])>0){
					$post['meeting_name'] = $post['meeting_name'];
				} else{
					$post['meeting_name'] = "Teambesprechung";
				}
				
				$post['status_team'] = $statust;
				$post['status_name'] = $statusarray;
				
				$team_patients['extra'] = array();
				if($meeting_id > 0 && !empty($_REQUEST['meetingid'])){
					$team_patients_array = TeamMeetingPatients::get_team_meeting_patients($meeting_id,$clientid);
				}						
				

				if(!empty($team_patients_array)){
						foreach($team_patients_array as $k=>$pat_data){
							$existing_team_patients[] = $pat_data['patient'];
							if($pat_data['extra'] == "1"){
								$team_patients['extra'][] = $pat_data['patient'];
							} else {
								$team_patients['active'][] = $pat_data['patient'];
							}
						}
 
						$sql_ep = "p.*,e.*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
						$sql_ep .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
						$sql_ep .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
						$sql_ep .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
						$sql_ep .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
						$sql_ep .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
						$sql_ep .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
						$sql_ep .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
						$sql_ep .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
						$sql_ep .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
						$sql_ep .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
						$sql_ep .="AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') as kontactnumber,";
						//if isstandby it must be shown as Anfrange in LS even if is also isdischarged
						$sql_ep .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
						$sql_ep .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
							
						// if super admin check if patient is visible or not
						if($logininfo->usertype == 'SA')
						{
							$sql_ep = "p.*,e.*,";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
							$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as kontactnumber, ";
							$sql_ep .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
						}
				
						$q = Doctrine_Query::create()
						->select($sql_ep)
						->from('EpidIpidMapping e')
						->leftJoin('e.PatientMaster p')
						->leftJoin('e.PatientActive a')
						->where("e.clientid = ?", $logininfo->clientid )
						->andWhereIn("p.ipid",$existing_team_patients)
						->andWhere('p.isdelete = 0');
						$active_ipids_details = $q->fetchArray();
				} 
				else 
				{
					//construct active patients from posted data(maintaining sort order)
					$sql = "a.*,e.*,p.*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
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
					$sql .= "CONVERT(AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') using latin1)  as kontactnumber,";
					$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";
	
					// if super admin check if patient is visible or not
					if($logininfo->usertype == 'SA')
					{
						$sql = "a.*,e.*,p.*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as kontactnumber, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex ";
					}
					$active_ipids_details = Pms_CommonData::patients_active($sql, $clientid, $months_details, false, 'e.epid_num', 'ASC');

					
					// insert locations data
					foreach($active_ipids_details as $k_active_patient => $v_active_patient)
					{
						$post_active_ipids[] = $v_active_patient['ipid'];
						$current_active[]= $v_active_patient['ipid'];
					}
					$location_id = $_POST['patients_location'];
 
					if($location_id > 0){
						 
						$locations_ids = array($location_id);
					
						// get patients in active locations
						$ql = Doctrine_Query::create()
						->select('ipid,location_id')
						->from('PatientLocation')
						->where('clientid = ?', $logininfo->clientid)
						->andWhere('isdelete = 0')
						->andWhereIn('location_id',$locations_ids)
						->andWhere('valid_till="0000-00-00 00:00:00"')
						->andWhereIn('ipid',$post_active_ipids)
						->orderBy('ipid ASC');
						$patientsl = $ql->fetchArray();
					
						foreach($patientsl as $k=>$vpl){
							$location_patients_array[$vpl['ipid']] =$active_patients[$vpl['ipid']];
							$patients2locations[] = $vpl['ipid'];
						}
						$team_patients['active']  = $patients2locations;
					} else {
						$team_patients['active'] = $current_active;
					}
				}
				
				foreach($active_ipids_details as $k_active_patient => $v_active_patient)
				{
					//if(is_array($v_active_patient['PatientActive']) && !empty($v_active_patient['PatientActive'])) {
						$active_pats[$v_active_patient['ipid']] = $v_active_patient;

						$last_period[$v_active_patient['ipid']] = end($v_active_patient['PatientActive']);

						$active_pats[$v_active_patient['ipid']]['admission_date'] = date('d.m.Y', strtotime($last_period[$v_active_patient['ipid']]['start']));

						if($last_period[$v_active_patient['ipid']]['end'] != "0000-00-00")
						{
							$active_pats[$v_active_patient['ipid']]['discharge_date'] = date('d.m.Y', strtotime($last_period[$v_active_patient['ipid']]['end']));
						}
						else
						{
							$active_pats[$v_active_patient['ipid']]['discharge_date'] = "-";
						}
						$active_pats[$v_active_patient['ipid']]['id'] = $v_active_patient['PatientMaster']['id'];
					//}					
				}

				foreach($post['patient'] as $k_pat => $v_pat)
				{
					$active_patients[$v_pat] = $active_pats[$v_pat];
				}

				

				//TODO-1384
				$ipidsactive = array_keys($active_patients);
				if( ! empty($ipidsactive)){
						
					$patientMasterData = Doctrine_Query::create()
					->select("ipid")
					->from('PatientMaster p')
					->whereIn('ipid',$ipidsactive);
					//ISPC-2045 - get kontact-phone
					$patientMasterData->leftJoin("p.PatientContactphone pcp");
					$patientMasterData->addSelect("pcp.phone, pcp.mobile");
					$patient_pc_arr = $patientMasterData->fetchArray();
						
					foreach($patient_pc_arr as $key=>$data){
				
						if (  ! empty($data['PatientContactphone'])) {
							$active_patients[$data['ipid']]['kontactnumber'] = "";
							$active_patients[$data['ipid']]['kontactnumber'] = implode("; ", array_column($data['PatientContactphone'], 'phone_number'));
						} 
					}
				}
				
				
				
				$post['active_patients'] = $active_patients;
				
				if($meeting_id > 0 && !empty($_REQUEST['meetingid'])){
					foreach($team_patients as $tp=>$tp_ipids){
						foreach ($tp_ipids as $p=>$v_ipid){
							if(!in_array($v_ipid,$post['patient'])){
								unset($team_patients[$tp][$p]);
							}
						}
					}
				}

				
				if(strlen($post['extra_patients'])>0 ||!empty($post['extra_patients']) ){

					$epids = explode(",",$post['extra_patients']);
					if(empty($epids)) {
						$epids[] ="999999";
					}
				
					$patient = Doctrine_Query::create()
					->select('p.ipid,e.epid')
					->from('PatientMaster p')
					->where("p.isdelete = 0");
					$patient->leftJoin("p.EpidIpidMapping e");
					$patient->andWhereIn("e.epid", $epids);
					$patient->andwhere("e.clientid = " . $logininfo->clientid );
					$extra_patients_details = $patient->fetchArray();
						
					$extra_patient_ipids = array();
					$extra_ipid2epid = array();
					foreach($extra_patients_details as $k=>$extra_ipid){
                        $extra_patient_ipids[] = $extra_ipid['ipid'];
                        $extra_ipid2epid[$extra_ipid['ipid']] = $extra_ipid['EpidIpidMapping']['epid']; 
					    array_push($team_patients['extra'], $extra_ipid['ipid']);
					}
					
					// TODO-1921 -
					if ($ClientDetails['teammeeting_settings']['iconprefill'] == 'yes' && ! empty($extra_patient_ipids)) {
					    	
					    $icon_order = array('sapv_appl', 'measure', 'current_situation');
					    	
					    $pcpr = new PatientCurrentProblems();
					    $pat_problems_data = $pcpr->get_patients_problems($extra_patient_ipids, null, true);
					    	
					    foreach($pat_problems_data as $kpat=>$vpat)
					    {
					        foreach($icon_order as $kpicon=>$vpicon)
					        {
					            if(!array_key_exists($vpicon, $vpat))
					            {
					                $pat_problems_data[$kpat][$vpicon] = "";
					            }
					        }
					    }
					    	
					    $pmemo = new PatientMemo();
					    $pat_memo_data = $pmemo->get_multiple_patient_memo($extra_patient_ipids);
					    	
					    $problem = array();
					   
					    foreach($pat_problems_data as $pipid=>$vprob)
					    {
					        $problem[$pipid] = '';
					        $vprob = array_merge(array_flip($icon_order), $vprob);
					
					        foreach($vprob as $kicon=>$vicon)
					        {
					            if($vicon != '')
					            {
					                $problem[$pipid] .= $this->translator->translate($kicon) . "\n";
					                $problem[$pipid] .= str_replace("<br />", "",$vicon);
					            }
					            if(strlen($problem[$pipid]) > 0 ){
					                $problem[$pipid] .=  "\n\n";
					            }
					        }
					
					        if($pat_memo_data[$pipid] != "")
					        {
					            $problem[$pipid] .= $this->translator->translate('memo') . "\n";
					            $problem[$pipid] .= str_replace("<br />", "", $pat_memo_data[$pipid]);
					        }
					    }
					    
					    
					    foreach($extra_ipid2epid as $ipid=>$epid)
					    {
					        $post['meeting']['problem'][$epid][1] = $problem[$ipid];
					        $post['meeting']['patient'][] = $epid;
					    }
					}
					
				}

				//ISPC-2681 Lore 14.10.2020
				if ($showEventsColumn && !empty($post['patient'])) {
				    $customEvents = Doctrine_Query::create()->select("*")
				    ->from('DoctorCustomEvents')
				    ->where("clientid = ?", $clientid)
				    ->andWhereIn("ipid", $post['patient'])
				    //->andWhere('DATE(startDate) >= CURRENT_DATE()')
				    ->fetchArray();
				    
				    $DoctorCustomEvents_vals = array();
				    $customEvents_arr = array();
				    foreach ($customEvents as $event) {
				        $customEvents_arr[$event['ipid']][] = $event['eventTitle'];
				    }

				    foreach($active_patients as $ipid=>$active_data) {
				        $DoctorCustomEvents_vals[$active_data['epid']]['1'] =  implode("; ", array_map('trim', $customEvents_arr[$active_data['ipid']]));
				    }
				}
				
				if ($show_assigned_doctors) {
				    $passdoc = new PatientQpaMapping();
				    $patient_assigned_doctors = $passdoc->get_patient_assigned_doctors($active_epids, $clientid, "names");
				    
				    $assigned_doctors_vals = array();
				    foreach($active_patients as $ipid=>$active_data)
				    {
				        $assigned_doctors_vals[$active_data['epid']]['1'] =  implode("; ", array_map('trim', $patient_assigned_doctors[$active_data['epid']]));
				    }
				}
				
				if ($showContactColumn) {
				    $last_contactform = ContactForms::get_last_contactform($active_ipids);
				    
				    $last_contact_arr =  array(); // this is appended to the view
				    $contact_form_types = array();
				    $users_ids_arr =  array();
				    
				    $last_XT = PatientCourse::get_last_XT($active_ipids);
				    
				    if ( ! empty ($last_XT)) {
				        $users_ids_arr =  array_merge(array_column($last_XT, 'create_user') , array_column($last_XT, 'change_user'));
				    }
				    if ( ! empty ($last_contactform)) {
				        $form_types = new FormTypes();
				        $contact_form_types = $form_types->get_form_types($clientid);
				        
				        $users_ids_arr =  array_merge($users_ids_arr, array_column($last_contactform, 'create_user') , array_column($last_contactform, 'change_user'));
				    }
				    if ( ! empty($users_ids_arr)) {
				        $users_ids_arr = array_unique($users_ids_arr);
				        $users_arr =  User::getUsersNiceName($users_ids_arr);
				    }
				    
				    foreach ($last_XT as $XT) {
				        $action_user = ! empty($XT['change_user']) ? $XT['change_user'] : $XT['create_user'];
				        $action_user = $XT['create_user'];
				        $last_contact_arr[$XT['ipid']][] = array(
				            'action_user' => $action_user,
				            'action_date' => strtotime($XT['done_date']),
				            'action_type' => 'Telefonat'
				        );
				    }
				    
				    foreach ($last_contactform as $contactform) {
				        $action_user = ! empty($contactform['change_user']) ? $contactform['change_user'] : $contactform['create_user'];
				        $action_user = $contactform['create_user'];
				        $last_contact_arr[$contactform['ipid']][] = array(
				            'action_user' => $action_user,
				            'action_date' => strtotime($contactform['start_date']),
				            'action_type' => $contact_form_types[$contactform['form_type']]['name']
				        );
				    }
				    
				    $active_patients_last_contact = array();
				    
				    foreach($last_contact_arr as $kipid=>$vipid) {
				        if(count($vipid) > 1) {
				            array_multisort(array_column($vipid, 'action_date'), SORT_ASC, $vipid);
				        }
				        $comma = "";
				        foreach($vipid as $kr=>$vr) {
				            $last_contact = $vr['action_type'] . " " . date('d.m.Y H:i', $vr['action_date']) . ", " . $users_arr[$vr['action_user']]['nice_initials'];
				            $active_patients_last_contact[$kipid] .= $comma . trim($last_contact);
				            $comma = "\n";
				        }
				    }
				    
				    foreach($active_patients as $ipid=>$active_data)
				    {
				        $last_contact_vals[$active_data['epid']]['1'] =  $active_patients_last_contact[$active_data['ipid']];
				    }
				}
				
				/* 			if($ClientDetails['teammeeting_settings']['lastfcomment'] == 'yes' ) {
				 $show_contact_comment = true;
				 } else {
				 $show_contact_comment = false;
				 }
				 $this->view->show_contact_comment = $show_contact_comment;
				 
				 if ($show_contact_comment) {
				 foreach ($last_contactform as $contactform) {
				 $active_patients[$contactform['ipid']]['last_comment'] = $contactform['comment'];
				 }
				 } */
				//.
				
				
				$post['team_patients'] = $team_patients;
				
				//construct array meeting_details structure from post
				$ipids_arr = Pms_CommonData::get_ipids_from_epids($post['meeting']['patient'], true);

				$meeting_details['client'] = $clientid;
				$meeting_details['meeting_name'] = $post['meeting_name'];
				$meeting_details['date'] = date('Y-m-d H:i:s', strtotime($post['date']));
				$meeting_details['from_time'] = date('Y-m-d', strtotime($post['date'])) . ' ' . $post['from_time'] . ':00';
				$meeting_details['till_time'] = date('Y-m-d', strtotime($post['date'])) . ' ' . $post['till_time'] . ':00';

				
				foreach($post['meeting']['patient'] as $k_pat => $v_patient)
				{
					foreach($post['meeting']['problem'][$v_patient] as $k_row => $v_prb)
					{
						if(strlen($v_prb) > '0' || strlen($post['meeting']['todo'][$v_patient][$k_row]) > '0' || count($post['meeting']['assigned_users'][$v_patient][$k_problem_row]) > 0)
						{
							$details['patient'] = $ipids_arr[$v_patient];
							$details['row'] = $k_row;
							$details['problem'] = $v_prb;
							$details['todo'] = $post['meeting']['todo'][$v_patient][$k_row];
							$details['status'] = $post['meeting']['status'][$v_patient][$k_row];
							$details['send_todo'] = (int) $post['meeting']['send_todo'][$v_patient][$k_row];
							$details['verlauf'] = (int) $post['meeting']['verlauf'][$v_patient][$k_row];
							
							$meeting_details['details'][$ipids_arr[$v_patient]][] = $details;
						}
					}
					
					//ISPC-2681 Lore 13.10.2020
					foreach($post['meeting']['targets'][$v_patient] as $k_targ => $v_targ) {
					    $meeting_details['details'][$ipids_arr[$v_patient]][$k_targ-1]['targets'] =  $v_targ;
					    //need exist $post['meeting']['problem'][$v_patient]
					    if(!isset($post['meeting']['problem'][$v_patient])){
					        $post['meeting']['problem'][$v_patient][$k_targ] = '';
					        $meeting_details['details'][$ipids_arr[$v_patient]][$k_targ]['problem'] =  '';
					    }
					}
					
					//ISPC-2896 Lore 19.04.2021
					foreach($post['meeting']['current_situation'][$v_patient] as $k_targ => $v_targ) {
					    $meeting_details['details'][$ipids_arr[$v_patient]][$k_targ-1]['current_situation'] =  $v_targ;
					    //need exist $post['meeting']['problem'][$v_patient]
					    if(!isset($post['meeting']['problem'][$v_patient][$k_targ])){
					        $post['meeting']['problem'][$v_patient][$k_targ] = '';
					        $meeting_details['details'][$ipids_arr[$v_patient]][$k_targ]['problem'] =  '';
					    }
					}
					foreach($post['meeting']['hypothesis_problem'][$v_patient] as $k_targ => $v_targ) {
					    $meeting_details['details'][$ipids_arr[$v_patient]][$k_targ-1]['hypothesis_problem'] =  $v_targ;
					    //need exist $post['meeting']['problem'][$v_patient]
					    if(!isset($post['meeting']['problem'][$v_patient][$k_targ])){
					        $post['meeting']['problem'][$v_patient][$k_targ] = '';
					        $meeting_details['details'][$ipids_arr[$v_patient]][$k_targ]['problem'] =  '';
					    }
					}
					foreach($post['meeting']['measures_problem'][$v_patient] as $k_targ => $v_targ) {
					    $meeting_details['details'][$ipids_arr[$v_patient]][$k_targ-1]['measures_problem'] =  $v_targ;
					    //need exist $post['meeting']['problem'][$v_patient]
					    if(!isset($post['meeting']['problem'][$v_patient][$k_targ])){
					        $post['meeting']['problem'][$v_patient][$k_targ] = '';
					        $meeting_details['details'][$ipids_arr[$v_patient]][$k_targ]['problem'] =  '';
					    }
					}
					//.

				}
				
				foreach($assigned_doctors_vals as $key=>$vals){
				    $meeting_details['details'][$ipids_arr[$key]][0]['assigned_doctors'] = implode(";", array_map('trim', $vals));      //ISPC-2681 Lore 14.10.2020
				}
				$post['meeting']['assigned_doctors'] = $assigned_doctors_vals;
				
				foreach($DoctorCustomEvents_vals as $ke_dry=>$vals_dr){
				   // $meeting_details['details'][$ipids_arr[$ke_dry]][0]['events'] = implode(";", array_map('trim', $vals_dr));      //ISPC-2681 Lore 14.10.2020
				}
				//$post['meeting']['events'] = $DoctorCustomEvents_vals;
				
				foreach($last_contact_vals as $ke_lc=>$vals_lc){
				    $meeting_details['details'][$ipids_arr[$ke_lc]][0]['last_contact'] = implode(";", array_map('trim', $vals_lc));      //ISPC-2681 Lore 14.10.2020
				}
				$post['meeting']['last_contact'] = $last_contact_vals;
				
				
				foreach($post['meeting']['assigned_users'] as $k_patient => $v_patient_rows_data)
				{
					foreach($v_patient_rows_data as $k_row => $v_usr)
					{
						$meeting_details['assigned_users'][$ipids_arr[$k_patient]][$k_row] = $v_usr;
					}
				}
				$meeting_details['attending_users'] = $post['attending_users'];

				$post['meeting_details'] = $meeting_details;
				$post['client_users'] = $client_users;
				$post['client_users_flat'] = $flat_todo_users_selectbox;
				$post['attendee_users'] = $attendee_users;
				$post['meeting_date_pdf'] = date('d.m.Y', strtotime($meeting_details['date'])) . ' ' . date('H:i', strtotime($meeting_details['from_time'])) . ' - ' . date('H:i', strtotime($meeting_details['till_time']));
				
				if($meeting_details)
				{
					$meeting_period['start'] = $meeting_details['date'];
					$meeting_period['end'] = $meeting_details['date'];

					$conditions['ipids'] = $_POST['patient'];;
					$conditions['period'] = $meeting_period;

					$patients_locations = TeamMeeting::get_patients_locations($conditions);

					if($patients_locations)
					{
						$post['patients_locations'] = $patients_locations;
					}
				}
// 				$_POST['pdf'] = 1
// 				print_r($_POST);
// 				exit;
// 				die("dddddd");
				if($_POST['save_form'] == "1" || $_POST['save_by_modal'] == "1")
				{
					//validate.. but dont cancel the insert method.. just correct wrong data
					$post = $meeting_form->_validate($post);

					$post['showXTcolumn'] = $showXTcolumn; //ISPC-2138
					$post['show_TODO_row'] = $show_TODO_row; //ISPC-2161
					$post['ClientDetails'] = $ClientDetails; //ISPC-2161
                    $post['case'] = $_REQUEST['case'];//Maria:: Migration CISPC to ISPC 22.07.2020
					
					//simple insert ... date and time validation is now handled by js
					$meeting_form->allusers = $all_users_array;
					$saved_meeting_id = $meeting_form->insert_meeting_data($post);

					//inserted or updated meeting id
					$post['meetingid'] = $saved_meeting_id;
					$post['location_details'] = $location_details;
					$post['location_option'] = $this->view->location_option;

					//ISPC-2681 Lore 13.10.2020
					$post['showTargetsColumn'] = $showTargetsColumn;     
					$post['showTodoColumn']    = $showTodoColumn;           
					$post['showUsersColumn']   = $showUsersColumn;         
					$post['showEventsColumn']  = $showEventsColumn;     
					$post['showContactColumn'] = $showContactColumn;           
					$post['show_assigned_doctors'] = $show_assigned_doctors;
					
					$post['show_epid'] = $show_epid;         
					$post['show_action'] = $show_action;         
					//.
					
					//ISPC-2896 Lore 19.04.2021
					$post['show_treatment_process'] = $show_treatment_process;
					$post['show_problems'] = $show_problems;
					
					
					//generate pdf without download and with internal history
					//LE: changed to generate pdf only on "completed" save
					if($post['completed'] == '1')
					{
						$template_files = array('teammeeting_pdf.html');
						$orientation = array('L');
						$background_pages = false; //array('0') is first page;
						$mode = "F";
						$this->generate_pdf($post, "teammeeting", $template_files, $orientation, $background_pages, $mode);
					}
					//then redirect page
					    
					if($saved_meeting_id)
					{
					    if($post['ajax_save'] == '1')
					    {
					       echo json_encode(array(success => true,'meeting_id'=>$saved_meeting_id));
					       exit;
					    } else {
					            
	       					$this->redirect(APP_BASE . 'team/teammeeting?meetingid=' . $saved_meeting_id);
    						exit;
					    }
					}
				}
				else if( !empty($_POST['pdf']) || $this->getRequest()->getPost('pdf_option') == "pdf"   )
				{	
					$template_files = array('teammeeting_pdf.html');
					$orientation = array('L');
					$background_pages = false; //array('0') is first page;
					$mode = "D";
					
					$post['showXTcolumn'] = $showXTcolumn; //ISPC-2138
					$post['show_TODO_row'] = $show_TODO_row; //ISPC-2161
					$post['ClientDetails'] = $ClientDetails; //ISPC-2161
					$post['patients_location'] = $location_id;
					$post['location_details'] = $location_details;
					$post['location_option'] = $this->view->location_option;
					
					//ISPC-2681 Lore 13.10.2020
					$post['showTargetsColumn'] = $showTargetsColumn;
					$post['showTodoColumn']    = $showTodoColumn;
					$post['showUsersColumn']   = $showUsersColumn;
					$post['showEventsColumn']  = $showEventsColumn;
					$post['showContactColumn'] = $showContactColumn;
					$post['show_assigned_doctors'] = $show_assigned_doctors;
					
					$post['show_epid'] = $show_epid;
					$post['show_action'] = $show_action;
					//.
					
					//ISPC-2896 Lore 19.04.2021
					$post['show_treatment_process'] = $show_treatment_process;
					$post['show_problems'] = $show_problems;
					
					$this->generate_pdf($post, "teammeeting" , $template_files, $orientation, $background_pages, $mode);
					exit;
				}
				else if( !empty($_POST['pdfview']) || $this->getRequest()->getPost('pdf_option') == "pdfview"   )
				{
					
					//Get diagnosis type -- only for project ISPC-1207
					$dg = new DiagnosisType();
					$abbr = "'HD'";
					$abbr_arr = $dg->getDiagnosisTypes($clientid, $abbr);

					$comma = ",";
					$typeid = "'0'";
					$typeids[] = '999999999999';
					foreach($abbr_arr as $key => $valdia)
					{
						$typeids = $valdia['id'];
						$typeid .=$comma . "'" . $valdia['id'] . "'";
						$comma = ", ";
					}
					$post['showXTcolumn'] = $showXTcolumn; //ISPC-2138
					$post['show_TODO_row'] = $show_TODO_row; //ISPC-2161
					
					//ISPC-2681 Lore 13.10.2020
					$post['showTargetsColumn'] = $showTargetsColumn;
					$post['showTodoColumn']    = $showTodoColumn;
					$post['showUsersColumn']   = $showUsersColumn;
					$post['showEventsColumn']  = $showEventsColumn;
					$post['showContactColumn'] = $showContactColumn;
					$post['show_assigned_doctors'] = $show_assigned_doctors;
					
					$post['show_epid'] = $show_epid;
					$post['show_action'] = $show_action;
					//.
					
					//ISPC-2896 Lore 19.04.2021
					$post['show_treatment_process'] = $show_treatment_process;
					$post['show_problems'] = $show_problems;
					
					$post['ClientDetails'] = $ClientDetails; //ISPC-2161
					$post['patients_location'] = $location_id;
					$post['location_details'] = $location_details;
					$posted_ipids = $_POST['patient'];
					$patdia = new PatientDiagnosis();
					$patients_diagnosis_arr = $patdia->get_multiple_patients_diagnosis($posted_ipids, $typeids);

					foreach($patients_diagnosis_arr as $k_diag => $v_diag)
					{
						$temp_diag_parts = array();
						$temp_diag = '';
						if(strlen(trim(rtrim($v_diag['icdnumber']))) > '0')
						{
							$temp_diag_parts[] = trim(rtrim($v_diag['icdnumber']));
						}

						if(strlen(trim(rtrim($v_diag['diagnosis']))) > '0')
						{
							$temp_diag_parts[] = trim(rtrim($v_diag['diagnosis']));
						}

						if(!empty($temp_diag_parts))
						{
							$temp_diag = implode(' - ', $temp_diag_parts);

							$patients_diagnosis[$v_diag['ipid']][] = $temp_diag;
						}
					}

					$post['patients_diagnosis'] = $patients_diagnosis;
					$post['location_option'] = $this->view->location_option;
					
					$template_files = array('teammeeting_pdf_view.html');
					$orientation = array('L');
					$background_pages = false; //array('0') is first page;
					$mode = "D";

					$this->generate_pdf($post, "teammeetingpdfview", $template_files, $orientation, $background_pages, $mode);
					exit;
				}
				else if( !empty($_POST['pdfviewusers']) || $this->getRequest()->getPost('pdf_option') == "pdfviewusers"   )
				{
					//ISPC-1283
					//Get diagnosis type
					$dg = new DiagnosisType();
					$abbr = "'HD'";
					$abbr_arr = $dg->getDiagnosisTypes($clientid, $abbr);

					$comma = ",";
					$typeid = "'0'";
					$typeids[] = '999999999999';
					foreach($abbr_arr as $key => $valdia)
					{
						$typeids = $valdia['id'];
						$typeid .=$comma . "'" . $valdia['id'] . "'";
						$comma = ", ";
					}
					$post['showXTcolumn'] = $showXTcolumn; //ISPC-2138
					$post['show_TODO_row'] = $show_TODO_row; //ISPC-2161
					
					//ISPC-2681 Lore 13.10.2020
					$post['showTargetsColumn'] = $showTargetsColumn;
					$post['showTodoColumn']    = $showTodoColumn;
					$post['showUsersColumn']   = $showUsersColumn;
					$post['showEventsColumn']  = $showEventsColumn;
					$post['showContactColumn'] = $showContactColumn;
					$post['show_assigned_doctors'] = $show_assigned_doctors;
					//.
					
					//ISPC-2896 Lore 19.04.2021
					$post['show_treatment_process'] = $show_treatment_process;
					$post['show_problems'] = $show_problems;
					
					$post['ClientDetails'] = $ClientDetails; //ISPC-2161
					$post['patients_location'] = $location_id;
					$post['location_details'] = $location_details;
					$posted_ipids = $_POST['patient'];
					$patdia = new PatientDiagnosis();
					$patients_diagnosis_arr = $patdia->get_multiple_patients_diagnosis($posted_ipids, $typeids);

					foreach($patients_diagnosis_arr as $k_diag => $v_diag)
					{
						$temp_diag_parts = array();
						$temp_diag = '';
						if(strlen(trim(rtrim($v_diag['icdnumber']))) > '0')
						{
							$temp_diag_parts[] = trim(rtrim($v_diag['icdnumber']));
						}

						if(strlen(trim(rtrim($v_diag['diagnosis']))) > '0')
						{
							$temp_diag_parts[] = trim(rtrim($v_diag['diagnosis']));
						}

						if(!empty($temp_diag_parts))
						{
							$temp_diag = implode(' - ', $temp_diag_parts);

							$patients_diagnosis[$v_diag['ipid']][] = $temp_diag;
						}
					}

					$post['patients_diagnosis'] = $patients_diagnosis;
					

					// asigned users
					foreach($all_users as $k_qpa => $v_qpa)
					{
						$users_details[$v_qpa['id']]['name'] = $v_qpa['user_title'] . " " . trim($v_qpa['last_name']) . ', ' . trim($v_qpa['first_name']);
						$users_details[$v_qpa['id']]['phone'] = $v_qpa['phone'];
						$users_details[$v_qpa['id']]['mobile'] = $v_qpa['mobile'];
					}
					
					$patientarray = array();
					if (!empty($posted_ipids) && is_array($posted_ipids)) {
    					$patient = Doctrine_Query::create()
    						->select('e.ipid,e.epid,q.*')
    						->from('EpidIpidMapping e')
    						->leftJoin("e.PatientQpaMapping q")
    						->andWhereIn("e.ipid", $posted_ipids);
    					$patient->andWhere('q.clientid = e.clientid and q.clientid = ' . $logininfo->clientid);
    					$patientarray = $patient->fetchArray();
					}


					foreach($patientarray as $kp => $pat_data)
					{
						foreach($pat_data['PatientQpaMapping'] as $k => $udata)
						{
							$assigned[$pat_data['ipid']][$udata['userid']]['name'] = trim($users_details[$udata['userid']]['name']);
							$assigned[$pat_data['ipid']][$udata['userid']]['phone'] = $users_details[$udata['userid']]['phone'];
							$assigned[$pat_data['ipid']][$udata['userid']]['mobile'] = $users_details[$udata['userid']]['mobile'];
						}
					}
					$post['assigend_data'] = $assigned;
					$post['location_option'] = $this->view->location_option;
					$template_files = array('teammeeting_pdf_users_view.html');
					$orientation = array('L');
					$background_pages = false; //array('0') is first page;
					$mode = "D";

					$this->generate_pdf($post, "teammeetingpdfuserview", $template_files, $orientation, $background_pages, $mode);
					exit;
				}
			}

			//get meeting details if we have an existing meeting
			if($last_meeting_id != '0')
			{
				$meeting_details = TeamMeeting::get_meeting_details($last_meeting_id);

				$this->view->meeting_details = $meeting_details;
				if(strlen($meeting_details['meeting_name'])>0){
					$this->view->meeting_name = $meeting_details['meeting_name'];
				} else{
					$this->view->meeting_name = "Teambesprechung";
				}
				$this->view->date = date('d.m.Y', strtotime($meeting_details['date']));
				$this->view->from_time = date('H:i', strtotime($meeting_details['from_time']));
				$this->view->till_time = date('H:i', strtotime($meeting_details['till_time']));
				$this->view->completed = $meeting_details['completed'];
				$this->view->organizational = $meeting_details['organizational'];
				$this->view->attending_users = $meeting_details['attending_users'];
				$this->view->patients_location = $meeting_details['patients_location'];
			}
			else
			{
				$this->view->date = date('d.m.Y', time());
				$this->view->from_time = date('H:i', time());
				$this->view->till_time = date('H:i', strtotime('+1 hour', time()));
				
				$this->view->meeting_name = "Teambesprechung";
				
			}

			$history = ClientFileUpload::get_latest_on_top_client_files($clientid, array('teammeeting'));
			$this->view->files_history = $history;
			
			
		}

		public function fetchteampatientsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$user_type = $logininfo->usertype;
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->_helper->layout->setLayout('layout_ajax');

			$users_arr= array();//holds the users for last_XT and last_contactform
			
			if(!$logininfo->clientid)
			{
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}
            /*$moduless = new Modules();
			if($moduless->checkModulePrivileges("110", $logininfo->clientid))
			{
				$statust = "1";
			}
			else
			{
				$statust = "0";
			}
			$this->view->status_t = $statust ;*/
            $templates_permitted = false;//IM-162, elena, 07.12.2020
           $modules = new Modules();
			if($modules->checkModulePrivileges("1014", $logininfo->clientid))
			{
                $templates_permitted = true;
                //IM-162,elena, 10.12.2020
                $standardtextblocks = ClientConfig::getConfig($clientid, 'meetingtextblocks');
                $this->view->standardtextblocks = json_decode($standardtextblocks);

            }
			$this->view->templates_permitted = $templates_permitted;
			
			$this->view->team_weak_2 = $team_weak2;
			
			
			
			/*if($moduless->checkModulePrivileges("110", $logininfo->clientid))
			{
				$statust = "1";
			}
			else
			{
				$statust = "0";
			}
			$this->view->status_t = $statust ;*/
			
			//ISPC-2138 make the XT coloumn client specific
			//ISPC - 2161 $showXTcolumn  = $moduless->checkModulePrivileges("160", $logininfo->clientid);
			
			
			//ISPC-2161 p.1 - if activated display a LINE with "TODO: and list all TODO comma seprated (TODO; WHO)
			//$show_TODO_row  = $moduless->checkModulePrivileges("161", $logininfo->clientid);
			
			$all_meetings = TeamMeeting::get_team_meetings($clientid, 'from_time');
			$last_meeting_id = "0";
			
			foreach($all_meetings as $k_meeting => $v_meeting)
			{
				$meeting_selector[$v_meeting['id']] = date('d.m.Y', strtotime($v_meeting['from_time'])) . ' ' . date('H:i', strtotime($v_meeting['from_time'])) . ' - ' . date('H:i', strtotime($v_meeting['till_time']));
			
				if($_REQUEST['meetingid'] == $v_meeting['id'] && !empty($_REQUEST['meetingid']))
				{
					$last_meeting_id = $v_meeting['id'];
				}
				else if(empty($_REQUEST['meetingid']))
				{
					$last_meeting_id = $v_meeting['id'];
				}
			}
			
			if($_POST['new_meeting'] || $_REQUEST['new_meeting'])
			{
				$last_meeting_id = '0';
			}
			
			if(empty($meeting_selector))
			{
				$meeting_selector[0] = $this->view->translate('no_team_meetings_saved_select');
			}
			
			if($_REQUEST['meetingid'] > '0')
			{
				$this->view->meetingid = $_REQUEST['meetingid'];
				$meeting_id = $_REQUEST['meetingid'];
			}
			else
			{
				$this->view->meetingid = $last_meeting_id;
				$meeting_id = $last_meeting_id;
			}
			
			$meeting_details = TeamMeeting::get_meeting_details($last_meeting_id);
			$this->view->meeting_details = $meeting_details;
			
			$this->view->{"style" . $_GET['pgno']} = "active";

			//Maria:: Migration CISPC to ISPC 22.07.2020
            //IM-141, Menue items "Teambesprechung Station" and "Teambesprechung Konsil", for new teammeeting, show all patients with case status (station or konsil)
			$array_ipids_status = [];
			if($meeting_id == 0 && isset($_REQUEST['case']) && (strtolower($_REQUEST['case']) === 'station' || strtolower($_REQUEST['case']) === 'konsil')){
                $dropCaseStatus = Doctrine_Query::create()
                    ->select('ipid')
                    ->from('PatientCaseStatus')
                    ->where("clientid = '" . $clientid . "'")
                    ->andWhere('isdelete=0')
                    ->andWhere('disdate="0000-00-00 00:00:00"')
                    ->andWhere("case_type='" . $_REQUEST['case'] . "'");
                //print_r($dropCaseStatus->buildSqlQuery());
                $dropcasestatusdata = $dropCaseStatus->fetchArray();
                foreach($dropcasestatusdata as $dropcase){
                    $array_ipids_status[] = $dropcase['ipid'];
                }

                //print_r($array_ipids_status);
            }


            $location_id = "0";
		
			if(isset($_REQUEST['location_id']) && $_REQUEST['location_id'] >= '0')
			{
				$location_id = $_REQUEST['location_id'];
			}
			elseif($meeting_details['patients_location'] > '0')
			{
				$location_id = $meeting_details['patients_location'];
			} else{
				$location_id = "0";
			}
 
			$columnarray = array(
				"epid" => "e.epid_num",
				"ln" => 'TRIM(CONVERT(CONVERT(AES_DECRYPT(p.last_name, "' . Zend_Registry::get('salt') . '") using utf8) using latin1)) COLLATE latin1_german2_ci',
				"fn" => 'TRIM(CONVERT(CONVERT(AES_DECRYPT(p.first_name, "' . Zend_Registry::get('salt') . '") using utf8) using latin1)) COLLATE latin1_german2_ci'
			);

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
			$months_details[0]['start'] = date('Y-m-d');

			$sort = $columnarray[$_REQUEST['clm']];

			
			
			if($meeting_id > 0 && !empty($_REQUEST['meetingid'])){
				$team_patients_array = TeamMeetingPatients::get_team_meeting_patients($meeting_id,$clientid);
			}

			
			if((!empty($team_patients_array) || !empty($array_ipids_status)  ) && !isset($_REQUEST['location_id']) ){
				
				foreach($team_patients_array as $k=>$pat_data){
					$team_patients[] = $pat_data['patient'];
					$patient_extra_info[$pat_data['patient']] = $pat_data['extra'];
				}
					
					
				$sql_ep = "p.*,e.*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
				$sql_ep .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
				$sql_ep .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
				$sql_ep .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
				$sql_ep .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
				$sql_ep .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
				$sql_ep .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
				$sql_ep .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
				$sql_ep .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
				$sql_ep .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
				$sql_ep .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
				//if isstandby it must be shown as Anfrange in LS even if is also isdischarged
				$sql_ep .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
				$sql_ep .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
		
				// if super admin check if patient is visible or not
				if($logininfo->usertype == 'SA')
				{
					$sql_ep = "p.*,e.*,";
					$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
					$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
					$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
					$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
					$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
					$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
					$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
					$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
					$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
					$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
					$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
					$sql_ep .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
					$sql_ep .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
				}
		
				$order_by = $sort;
				$sort = $_REQUEST['ord'];
					
				$q = Doctrine_Query::create()
				->select($sql_ep)
				->from('EpidIpidMapping e')
				->leftJoin('e.PatientMaster p')
				->leftJoin('e.PatientActive a')
				->where("e.clientid = " . $logininfo->clientid )
				->andWhereIn("p.ipid",(!empty($team_patients) ? $team_patients : $array_ipids_status))
				->andWhere('p.isdelete = 0')
				->orderBy($order_by . ' ' . $sort);
				$active_ipids_details = $q->fetchArray();
				
				
				
						
			} else {
				$sql = "a.*,e.*,p.*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
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
				if($logininfo->usertype == 'SA')
				{
					$sql = "a.*,e.*,p.*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex ";
				}
	
				$active_ipids_details = Pms_CommonData::patients_active($sql, $clientid, $months_details, false, $sort, $_REQUEST['ord']);
				//ISPC-1814 - WL_Vollversorgung
				if ($location_id == "WL_Vollversorgung"){
					
					$p_active_ipids = array_column( $active_ipids_details , 'ipid');
				
					$vvhistory = new VollversorgungHistory();
					$historyvv = $vvhistory->getVollversorgungHistoryAll($p_active_ipids);
					// ! if you change getVollversorgungHistoryAll order by id ASC, this will NOT work
					foreach($historyvv as $v){
						$historyvv[ $v['ipid'] ] = $v;
					}
				
					foreach($active_ipids_details as $key_patient => $patient_value)
					{
						if( $patient_value['PatientMaster']['vollversorgung']){
							$WL_Vollversorgung [] = $patient_value['ipid'];
						}elseif(isset($historyvv[ $patient_value['ipid'] ])){
							if ($historyvv[ $patient_value['ipid'] ]['date_type'] == "1" || strtotime($historyvv[ $patient_value['ipid'] ]['date']) >=strtotime(date("Y-m-d")) ){
								$WL_Vollversorgung [] = $patient_value['ipid'];	
							}
						}
					}
					
					foreach($active_ipids_details as $key_patient => $patient_value){
						if(!in_array($patient_value['ipid'], $WL_Vollversorgung)){
							unset($active_ipids_details[$key_patient ]);
						}
					}
				}
				elseif($location_id > 0){
					
					foreach($active_ipids_details as $k_active_patient => $v_active_patient)
					{
						$p_active_ipids[] = $v_active_patient['ipid'];
					}
					
					$locations_ids = array($location_id);
	 
					// get patients in active locations
					$ql = Doctrine_Query::create()
					->select('ipid,location_id')
					->from('PatientLocation')
					->where('clientid = "' . $logininfo->clientid. '"')
					->andWhere('isdelete = 0')
					->andWhereIn('location_id',$locations_ids)
					->andWhere('valid_till="0000-00-00 00:00:00"')
					->andWhereIn('ipid',$p_active_ipids)
					->orderBy('ipid ASC');
					$patientsl = $ql->fetchArray();
					
					foreach($patientsl as $k=>$vpl){
						$patient_location[$vpl['ipid']] = $vpl['location_id'];
						$location_patients[] = $vpl['ipid'];
						$location_patients_array[$vpl['ipid']] =$active_patients[$vpl['ipid']];
					}
				
					foreach($active_ipids_details as $key_patient => $patient_value){
						if(!in_array($patient_value['ipid'],$location_patients)){
							unset($active_ipids_details[$key_patient ]);
						}
					}
				}
			}
			
			$active_ipids[] = '9999999999999';
			foreach($active_ipids_details as $k_active_patient => $v_active_patient)
			{
				$active_patients[$v_active_patient['ipid']] = $v_active_patient;

				$active_ipids[] = $v_active_patient['ipid'];
				$active_ipids_str .= '"'.$v_active_patient['ipid'].'", ';

				$last_period[$v_active_patient['ipid']] = end($v_active_patient['PatientActive']);

				$active_patients[$v_active_patient['ipid']]['admission_date'] = date('d.m.Y', strtotime($last_period[$v_active_patient['ipid']]['start']));

				if($last_period[$v_active_patient['ipid']]['end'] != "0000-00-00")
				{
					$active_patients[$v_active_patient['ipid']]['discharge_date'] = date('d.m.Y', strtotime($last_period[$v_active_patient['ipid']]['end']));
				}
				else
				{
					$active_patients[$v_active_patient['ipid']]['discharge_date'] = "-";
				}
				$active_patients[$v_active_patient['ipid']]['id'] = $v_active_patient['PatientMaster']['id'];	
				$active_patients[$v_active_patient['ipid']]['enc_id'] = Pms_Uuid::encrypt($v_active_patient['PatientMaster']['id']); ;
				$active_patients[$v_active_patient['ipid']]['pepid'] = $v_active_patient['epid'];	
				$active_patients[$v_active_patient['ipid']]['extra'] = $patient_extra_info[$v_active_patient['PatientMaster']['ipid']];	
				 
			}
			//var_dump($active_patients); exit;
			//start get icons status and last location for all $active_patients
			$pat_icons = new IconsPatient();
			$status_data = $pat_icons->get_patients_status($active_ipids, $filter = false, $filter_value = false, $details_included = false);
			
			$lc = new Locations();
			$locationsdata = $lc->getLocations($clientid, 0);
			$locType = $lc->checkLocationsClientByType($clientid, 6);
			
			if($locType)
			{
				$pc = new ContactPersonMaster();
				$cpactive_patients = $pc->getContactPersonsByIpids($active_ipids);
				foreach($cpactive_patients as $cpactive_patient)
				{
					$cpactive_patients_ids[$cpactive_patient['ipid']][] = $cpactive_patient;
				}
			}
					
			foreach($locationsdata as $locationdata) 
			{
				$locdata[$locationdata['id']] = $locationdata;
			}
			
			//ISPC - 2310 - team meeting - change status directly in team meeting
			foreach($status_data['ipids'] as $active_ipid) {
				$active_patients[$active_ipid]['icon_data']['status_icon'] = $status_data[$active_ipid]['show']['image'];
				$active_patients[$active_ipid]['icon_data']['traffic_status'] = $status_data[$active_ipid]['traffic_status'];
				$active_patients[$active_ipid]['icon_data']['condition'] = $status_data[$active_ipid]['condition'];
				
				
				//$active_patients[$active_ipid]['status_icon'] = $status_data[$active_ipid]['show']['image'];
				
				if($status_data[$active_ipid]['last_location']['location_id'] != '')
				{
					if(substr($status_data[$active_ipid]['last_location']['location_id'], 0, 4) == '8888' && strlen($status_data[$active_ipid]['last_location']['location_id'] > 4))
					{
						$active_patients[$active_ipid]['location'] = 'bei Kontaktperson '.substr($status_data[$active_ipid]['last_location']['location_id'], -1).' ('
																						 .$cpactive_patients_ids[$active_ipid][substr($status_data[$active_ipid]['last_location']['location_id'], -1)-1]['cnt_first_name'].','
																						 .$cpactive_patients_ids[$active_ipid][substr($status_data[$active_ipid]['last_location']['location_id'], -1)-1]['cnt_last_name'].')<br />'
																						 .$cpactive_patients_ids[$active_ipid][substr($status_data[$active_ipid]['last_location']['location_id'], -1)-1]['cnt_street1']
																						 .' '.$cpactive_patients_ids[$active_ipid][substr($status_data[$active_ipid]['last_location']['location_id'], -1)-1]['cnt_zip']
																						 .'<br />'.$cpactive_patients_ids[$active_ipid][substr($status_data[$active_ipid]['last_location']['location_id'], -1)-1]['cnt_city'];
					}
					elseif ($locdata[$status_data[$active_ipid]['last_location']['location_id']]['location_type'] == '5')
					{
						$active_patients[$active_ipid]['location'] = $locdata[$status_data[$active_ipid]['last_location']['location_id']]['location'].'<br />'
																	.$active_patients[$active_ipid]['street1'].' '.$active_patients[$active_ipid]['zip'].'<br />'.$active_patients[$active_ipid]['city'];
					}
					else 
					{
						$active_patients[$active_ipid]['location'] = $locdata[$status_data[$active_ipid]['last_location']['location_id']]['location'].'<br />'
																.$locdata[$status_data[$active_ipid]['last_location']['location_id']]['street'].' '
																.$locdata[$status_data[$active_ipid]['last_location']['location_id']]['zip'].'<br />'
																.$locdata[$status_data[$active_ipid]['last_location']['location_id']]['city'];
					}
				}
				else 
				{
					$active_patients[$active_ipid]['location'] = '';
				}
			}			
			//end get icons and last location
			
			
			$Client_entity =  new Client();
			$Client_arr = $Client_entity->findOneById();
			$this->view->client_teammeeting_settings = $Client_arr['teammeeting_settings'];
			//ISPC - 2161
			if($Client_arr['teammeeting_settings']['xt'] == 'yes')
			{
				$showXTcolumn = true;
			}
			else
			{
				$showXTcolumn = false;
			}
				
			if($Client_arr['teammeeting_settings']['statusdrop'] == 'yes')
			{
				$statust = "1";
			}
			else
			{
				$statust = "0";
			}
			$this->view->status_t = $statust ;
			
			if($Client_arr['teammeeting_settings']['showtodos'] == 'yes')
			{
				$show_TODO_row = true;
			}
			else
			{
				$show_TODO_row = false;
			}
			
			//ISPC - 2161
			
			$active_patients_ipids = array_keys($active_patients);
			
			//TODO this fn MUST be changed.... awaiting response what to do is user is inakive or deleted
			//TODO: this is NOT good, the IconsPatient::get_patient_todos is flawed
			//ISPC-2161 p.1
            if($templates_permitted){ //IM-162,elena,03.12.2020
                $a_last_entries = TeamMeetingDetails::get_patient_last_entries($active_patients_ipids);
                //print_r($a_last_entries);
                $a_entries = [];
                foreach($a_last_entries as $ipid => $aVals){
                    foreach($aVals as $aVal){
                        $a_entries[$ipid]['targets'][] = $aVal[2];
                        $a_entries[$ipid]['problems'][] = $aVal[0];
                        $a_entries[$ipid]['todo'][] = $aVal[1];
                    }

                }

                //print_r($a_entries);
                $this->view->a_last_entries = $a_entries;
            }

			if ($show_TODO_row && ! empty($active_patients_ipids)) {
			    
			    $current_meeting_date = date("Y-m-d");
			    if(!empty($meeting_details['date'])){
			        $current_meeting_date =date("Y-m-d",strtotime($meeting_details['date']));
			    } 
			    $patient_todos= array();
			    //$patient_todos = IconsPatient::get_patient_todos($active_patients_ipids);
			    $pattodos = new IconsPatient();
			    $patient_todos = $pattodos->get_patient_todos($active_patients_ipids);
			   
			    foreach ($patient_todos['todo_data'] as $ipid => $todos) {
			        $active_patients[$ipid]['todo_data'] =  array();
			        foreach ($todos as $todo) {

			            if(!empty($meeting_details['date'])){
    			            if (!empty($todo['todo'])
    			                && Pms_CommonData::isintersected(
    			                    strtotime($current_meeting_date), 
    			                    strtotime($current_meeting_date), 
    			                    strtotime(date("Y-m-d",strtotime($todo['create_date']))), 
    			                    strtotime(date("Y-m-d",strtotime($todo['until_date'])))
    			                    ) 
				
    			                
    			                ) {
    			                    $Completed_text = "";
    			                    if($todo['iscompleted'] =="1"){
    			                        $Completed_text = "<br/>".$this->translate("[Completed todo in team meeting]");
    			                    }
    			                $active_patients[$ipid]['todo_data'][] = array(
    			                	'todo_date' => $todo['until_date'].$Completed_text, //ISPC-2161 p.8
    			                    'todo' => $todo['todo'],
    			                    'todoers' => ($todo['user_name'] != '-' ? $todo['user_name'] : '') . ($todo['group_name'] != '-' ? $todo['group_name'] : ''),
    			                    'todo_completed' => $todo['iscompleted'],
    		                    );    
    			            }
    			            
			            } else {
    			            if ($todo['iscompleted'] == 0 && ! empty($todo['todo'])) {
    			                $active_patients[$ipid]['todo_data'][] = array(
    			                	'todo_date' => $todo['until_date'], //ISPC-2161 p.8
    			                    'todo' => $todo['todo'],
    			                    'todoers' => ($todo['user_name'] != '-' ? $todo['user_name'] : '') . ($todo['group_name'] != '-' ? $todo['group_name'] : ''),
    			                    'todo_completed' => $todo['iscompleted'],
    		                    );    
    			            }
			                
			            }
			        } 
			    }
			    
			}
			
		    //ISPC-2161 p.2
			if ($Client_arr['teammeeting_settings']['events'] && ! empty($active_patients_ipids)) {
			    $customEvents = Doctrine_Query::create()->select("*")
			    ->from('DoctorCustomEvents')
			    ->where("clientid = ?", $logininfo->clientid)
			    ->andWhereIn("ipid", $active_patients_ipids)
			    ->andWhere('DATE(startDate) >= CURRENT_DATE()')
			    ->fetchArray()
			    ;
			    
			    foreach ($customEvents as $event) {
			        $active_patients[$event['ipid']]['DoctorCustomEvents'][] = $event;
			    }
			}
			//ISPC - 2326 - start - plz change that coloumn Letzter Kontakt to display the last phone call AND the last visit.
			if (($Client_arr['teammeeting_settings']['contact'] || $Client_arr['teammeeting_settings']['lastfcomment'] == 'yes') && ! empty($active_patients_ipids)) {
				$last_contactform = ContactForms::get_last_contactform($active_patients_ipids);
				
			    //ISPC-2161 p.5 last_contact <= contact
				if ($Client_arr['teammeeting_settings']['contact'] && ! empty($active_patients_ipids)) {
				    
				    $last_contact_arr =  array(); // this is appended to the view
				    $contact_form_types = array();
				    $users_ids_arr =  array();
				    
				    $last_XT = PatientCourse::get_last_XT($active_patients_ipids);
				    
				    //$last_contactform = ContactForms::get_last_contactform($active_patients_ipids); // l-am scos in afara if-ului ca sa-l pot folosi si pentru punctul 17
				    
				    if ( ! empty ($last_XT)) {
				        $users_ids_arr =  array_merge(array_column($last_XT, 'create_user') , array_column($last_XT, 'change_user'));
				    }
				    if ( ! empty ($last_contactform)) {
				        $form_types = new FormTypes();
				        $contact_form_types = $form_types->get_form_types($logininfo->clientid);
				        
				        $users_ids_arr =  array_merge($users_ids_arr, array_column($last_contactform, 'create_user') , array_column($last_contactform, 'change_user'));
				    }
				    if ( ! empty($users_ids_arr)) {
				       $users_ids_arr = array_unique($users_ids_arr);
				       $users_arr =  User::getUsersNiceName($users_ids_arr);
				    }
				    
				    //compare  xt done_date <> contactform start_date
				    /*foreach ($last_XT as $XT) {
				        if (isset($last_contactform[$XT['ipid']]) && strtotime($last_contactform[$XT['ipid']]['start_date']) > strtotime($XT['done_date'])) {
				            //compare  xt done_date <> contactform start_date
	// 			            $action_user = ! empty($last_contactform[$XT['ipid']]['change_user']) ? $last_contactform[$XT['ipid']]['change_user'] : $last_contactform[$XT['ipid']]['create_user'];
				            $action_user = $last_contactform[$XT['ipid']]['create_user'];
				            $last_contact_arr[$XT['ipid']] =  $contact_form_types[$last_contactform[$XT['ipid']]['form_type']]['name'] . ', '. date('d.m.Y H:i', strtotime($last_contactform[$XT['ipid']]['start_date'])) . ', ' . $users_arr[$action_user]['nice_initials'];
				        } else {
	// 			            $action_user = ! empty($XT['change_user']) ? $XT['change_user'] : $XT['create_user'];
				            $action_user = $XT['create_user'];
				            $last_contact_arr[$XT['ipid']] =  'Telefonat, ' . date('d.m.Y H:i', strtotime($XT['done_date'])) . ', ' .  $users_arr[$action_user]['nice_initials'];
				        }
				    }
				    foreach ($last_contactform as $contactform) {
				        if (isset($last_XT[$contactform['ipid']]) && strtotime($last_XT[$contactform['ipid']]['done_date']) > strtotime($contactform['start_date'])) {
				           
	// 			            $action_user = ! empty($last_XT[$contactform['ipid']]['change_user']) ? $last_XT[$contactform['ipid']]['change_user'] : $last_XT[$contactform['ipid']]['create_user'];
				            $action_user = $last_XT[$contactform['ipid']]['create_user'];
				            $last_contact_arr[$contactform['ipid']] =  'Telefonat, ' . date('d.m.Y H:i', strtotime($last_XT[$contactform['ipid']]['done_date'])) . ', ' . $users_arr[$action_user]['nice_initials'];
				        } else {
	// 			            $action_user = ! empty($contactform['change_user']) ? $contactform['change_user'] : $contactform['create_user'];			             
				            $action_user = $contactform['create_user'];			             
				            $last_contact_arr[$contactform['ipid']] =  $contact_form_types[$contactform['form_type']]['name']  .", ". date('d.m.Y H:i', strtotime($contactform['start_date'])) . ', ' . $users_arr[$action_user]['nice_initials'];
				        }			        
				    }*/
				    foreach ($last_XT as $XT) {
				    	$action_user = ! empty($XT['change_user']) ? $XT['change_user'] : $XT['create_user'];
				    	$action_user = $XT['create_user'];
				    	$last_contact_arr[$XT['ipid']][] = array(
				    					'action_user' => $action_user,
				    					'action_date' => strtotime($XT['done_date']),
				    					'action_type' => 'Telefonat'
				    	);
				    }
				    
				    foreach ($last_contactform as $contactform) {
				    	$action_user = ! empty($contactform['change_user']) ? $contactform['change_user'] : $contactform['create_user'];
				    	$action_user = $contactform['create_user'];
				    	$last_contact_arr[$contactform['ipid']][] = array(
				    			'action_user' => $action_user,
				    			'action_date' => strtotime($contactform['start_date']),
				    			'action_type' => $contact_form_types[$contactform['form_type']]['name']
				    	);
				    }
				    foreach($last_contact_arr as $kipid=>$vipid)
				    {
				    	if(count($vipid) > 1)
				    	{
				    		array_multisort(array_column($vipid, 'action_date'), SORT_ASC, $vipid);
				    	}
				    	$active_patients[$kipid]['last_contact'] = "";
				    	$comma = "";
				    	foreach($vipid as $kr=>$vr)
				    	{
				    		$last_contact = $vr['action_type'] . " " . date('d.m.Y H:i', $vr['action_date']) . ", " . $users_arr[$vr['action_user']]['nice_initials'];			    		
				    		$active_patients[$kipid]['last_contact'] .= $comma . trim($last_contact);
				    		$comma = "\n";
				    	}
				    }
				    //assign for view
				    /*foreach ($last_contact_arr as $ipid=>$last_contact) {
				        $active_patients[$ipid]['last_contact'] = $last_contact;
				         
				    }*/
				}
				//print_r($last_contactform); exit;
				//ISPC-2161 p.17 - show comment from last contact form if the coresponding module is activated
				//$show_contact_comment  = $moduless->checkModulePrivileges("172", $logininfo->clientid);
				if($Client_arr['teammeeting_settings']['lastfcomment'] == 'yes'  && ! empty($active_patients_ipids))
				{
					$show_contact_comment = true;
				}
				else
				{
					$show_contact_comment = false;
				}
				$this->view->show_contact_comment = $show_contact_comment;
				
				if ($show_contact_comment) {
					foreach ($last_contactform as $contactform) {
						$active_patients[$contactform['ipid']]['last_comment'] = $contactform['comment'];
					}
				}
			}
			//ISPC - 2326 - end - plz change that coloumn Letzter Kontakt to display the last phone call AND the last visit.
			//ISPC-2161 p.16 - show assigned doctors if the coresponding module is activated
			//$show_assigned_doctors  = $moduless->checkModulePrivileges("171", $logininfo->clientid);
			if($Client_arr['teammeeting_settings']['tbyusers'] == 'yes')
			{
				$show_assigned_doctors = true;
			}
			else
			{
				$show_assigned_doctors = false;
			}
			$this->view->show_assigned_doctors = $show_assigned_doctors;
			if ($show_assigned_doctors && ! empty($active_patients_ipids)) {
				$active_patients_epids = array();
				foreach($active_patients as $ipid=>$active_data)
				{
					$active_patients_epids[] = $active_data['pepid']; 
				}
				
				$passdoc = new PatientQpaMapping();
				$patient_assigned_doctors = $passdoc->get_patient_assigned_doctors($active_patients_epids, $clientid, "names");
				
				foreach($active_patients as $ipid=>$active_data)
				{
					$active_patients[$ipid]['assigned_doctors'] =  implode("; ", array_map('trim', $patient_assigned_doctors[$active_data['epid']]));
					
				}
			}
			
			//ISPC-2896 Lore 23.04.2021          show aktuelle problematik column
			if(isset($Client_arr['teammeeting_settings']['treatment_process'])){
			    if($Client_arr['teammeeting_settings']['treatment_process'] == 'yes'){
			        $this->view->show_treatment_process = $show_treatment_process = true;
			    } else {
			        $this->view->show_treatment_process = $show_treatment_process = false;
			    }
			} else{
			    $this->view->show_treatment_process = $show_treatment_process = true;
			}
			//--

			//ISPC - 2261 - prefill the aktuelle problematik field with the content of 3 patient icon and memo field			
			if ($Client_arr['teammeeting_settings']['iconprefill'] == 'yes' && ! empty($active_patients_ipids) && $last_meeting_id == '0') {
				
				//$icon_order = array('sapv_appl', 'measure', 'current_situation');
			    $icon_order = array('sapv_appl', 'measure', 'current_situation', 'ventilation');     //TODO-3707 Lore 06.01.2021
			    
				$pcpr = new PatientCurrentProblems();
				$pat_problems_data = $pcpr->get_patients_problems($active_patients_ipids, null, true);
				
				foreach($pat_problems_data as $kpat=>$vpat)
				{
					foreach($icon_order as $kpicon=>$vpicon)
					{
						if(!array_key_exists($vpicon, $vpat))
						{
							$pat_problems_data[$kpat][$vpicon] = "";
						}
					}
				}
				
				$pmemo = new PatientMemo();
				$pat_memo_data = $pmemo->get_multiple_patient_memo($active_patients_ipids);
				
				$problem = array();
				// TODO-1921 - chenge the line spacing: 08.11.2018
				foreach($pat_problems_data as $pipid=>$vprob)
				{
					$problem[$pipid] = '';
					$vprob = array_merge(array_flip($icon_order), $vprob);
					
					foreach($vprob as $kicon=>$vicon)
					{
						if($vicon != '')
						{
							$problem[$pipid] .= $this->translator->translate($kicon) . "\n";
							$problem[$pipid] .= str_replace("<br />", "",$vicon);
						}
						if(strlen($problem[$pipid]) > 0 ){
    						$problem[$pipid] .=  "\n\n";
						}
					}
					
					if($pat_memo_data[$pipid] != "")
					{
						$problem[$pipid] .= $this->translator->translate('memo') . "\n";
						$problem[$pipid] .= str_replace("<br />", "", $pat_memo_data[$pipid]);
					}  
					
				}				
				
				foreach($active_patients as $ipid=>$active_data)
				{
					$active_patients[$ipid]['problem'] = $problem[$ipid];
				}
			}
			//ISPC-2896 Lore 19.04.2021
			if($Client_arr['teammeeting_settings']['show_problems'] == 'yes'){
			    $this->view->show_problems = $show_problems = true;
			} else {
			    $this->view->show_problems = $show_problems = false;
			}
			
			if($show_problems){

		        $tmd_last_entries = TeamMeetingDetails::get_ipid_details($active_patients_ipids, $last_meeting_id, $clientid);
		        
		        //get client problems
		        $client_problems_array = Doctrine_Query::create()
		        ->select('*')
		        ->from('ClientProblemsList IndexBy id')
		        ->where("clientid = ?", $clientid)
		        ->fetchArray();
		        
		        // get problems per patient
		        $patients_problems = FormBlockProblemsTable::find_patient_problems($active_patients_ipids);
		        
		        if($last_meeting_id[0] != '0'){
/* 		            foreach($tmd_last_entries as $key=> $vals){
		                $active_patients[$vals['patient']]['current_situation']  = $vals['current_situation'];
		                $active_patients[$vals['patient']]['hypothesis_problem'] = $vals['hypothesis_problem'];
		                $active_patients[$vals['patient']]['measures_problem']   = $vals['measures_problem'];
		            } */
		        } else {
		            
		            foreach($patients_problems as $p_problem_id=>$pinf){
		                if(!empty($pinf['FormBlockProblemsSituations'])){

		                    //Ancuta 26.04.2021 - change the latest is taken - according to ISPC-2864
		                    foreach($pinf['FormBlockProblemsSituations'] as $kd=>$sit){
		                        $all_problems_situations[$p_problem_id][$sit['situation_type']][] = $sit;
		                        if(!empty($sit['situation_description'])){
		                            $all_filled_problems_situations[$p_problem_id][$sit['situation_type']][] = $sit;
		                        }
		                    }
		                    //--
		                    
		                    foreach($pinf['FormBlockProblemsSituations'] as $k=>$pfssd){
		                        if( $pfssd['latest_version'] == '1'){
		                            
		                            $meeting_details['details'][$pinf['ipid']][0]['patient']  = $pinf['ipid'];
		                            $meeting_details['details'][$pinf['ipid']][0]['row']  = '1';
		                            
		                            //Ancuta 26.04.2021 - change the latest is taken - according to ISPC-2864
		                            if(empty($pfssd['situation_description'])){
		                                usort($all_filled_problems_situations[$p_problem_id][$pfssd['situation_type']], array(new Pms_Sorter('situation_date'), "_date_compare"));
		                                $latest= end( $all_filled_problems_situations[$p_problem_id][$pfssd['situation_type']] );
		                                $pfssd['situation_description'] = $latest['situation_description'];
		                            }
		                            // -- 
		                            
		                            if($pfssd['situation_type'] == 'current_situation') {
		                                $meeting_details['details'][$pinf['ipid']][0]['current_situation']  .= $client_problems_array[$pinf['problem_id']]['problem_name'].": \n".$pfssd['situation_description']."\n";
		                            }
		                            if($pfssd['situation_type'] == 'hypothesis') {
		                                $meeting_details['details'][$pinf['ipid']][0]['hypothesis_problem'] .= $client_problems_array[$pinf['problem_id']]['problem_name'].": \n".$pfssd['situation_description']."\n";
		                            }
		                            if($pfssd['situation_type'] == 'measures') {
		                                $meeting_details['details'][$pinf['ipid']][0]['measures_problem']   .= $client_problems_array[$pinf['problem_id']]['problem_name'].": \n".$pfssd['situation_description']."\n";
		                            }
		                        }
		                    }
		                }
		            }
		            $this->view->meeting_details = $meeting_details;
		            
		        }
			}
			//.
			
			//print_r($active_patients); exit;
			$this->view->active_patients = $active_patients;
			
			
			//get all patients diagnosis ISPC-1169
			//Get diagnosis type
			$dg = new DiagnosisType();
			$abbr = "'HD'";
			$abbr_arr = $dg->getDiagnosisTypes($clientid, $abbr);

			$comma = ",";
			$typeid = "'0'";
			$typeids[] = '999999999999';
			foreach($abbr_arr as $key => $valdia)
			{
				$typeids = $valdia['id'];
				$typeid .=$comma . "'" . $valdia['id'] . "'";
				$comma = ", ";
			}

			$patdia = new PatientDiagnosis();
			$patients_diagnosis_arr = $patdia->get_multiple_patients_diagnosis($active_ipids, $typeids);

			foreach($patients_diagnosis_arr as $k_diag => $v_diag)
			{
				$temp_diag_parts = array();
				$temp_diag = '';
				if(strlen(trim(rtrim($v_diag['icdnumber']))) > '0')
				{
					$temp_diag_parts[] = trim(rtrim($v_diag['icdnumber']));
				}

				if(strlen(trim(rtrim($v_diag['diagnosis']))) > '0')
				{
					$temp_diag_parts[] = trim(rtrim($v_diag['diagnosis']));
				}

				if(!empty($temp_diag_parts))
				{
					$temp_diag = implode(' - ', $temp_diag_parts);

					$patients_diagnosis[$v_diag['ipid']][] = $temp_diag;
				}
			}

			$this->view->patient_diagnosis = $patients_diagnosis;
			$this->view->showXTcolumn  = $showXTcolumn ;
			$this->view->show_TODO_row  = $show_TODO_row ;

			
			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['teampatients'] = $this->view->render('team/fetchteampatients.html');

			echo json_encode($response);
			exit;
		}

		private function generate_pdf($post_data, $pdfname, $filename, $orientation = false, $background_pages = false, $mode = 'D')
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if(!empty($post_data['date']))
			{
				$meeting_date = date('d.m.Y', strtotime($post_data['date']));
			}

			if($pdfname == 'teamevent' ){
				if(!empty($post_data['date']))
				{
					$event_date = date('d.m.Y', strtotime($post_data['date']));
				}
				if(!empty($post_data['event_name']))
				{
					$event_name = $post_data['event_name'];
				}
			}
			if($pdfname == 'teammeeting' || $pdfname == 'teammeetingpdfview' || $pdfname == 'teammeetingpdfuserview')
			{
				if(!empty($post_data['meeting_name']))
				{
					$meeting_name = $post_data['meeting_name'];
				}
				if(!empty($post_data['meeting_date']))
				{
					$meeting_date = date('d.m.Y', strtotime($post_data['date']));
				}
				
			}
			
			$pdf_names = array(
				'teammeeting' => $meeting_name .' vom '. $meeting_date,
				'teammeetingpdfview' => $meeting_name . ' Druck Ansicht',
				'teammeetingpdfuserview' => $meeting_name .' Druck Ansicht mit Telefonnnummern',
				'teamevent' => $event_name .' vom ' . $event_date,
			);
			
			$files_names = array(
					'teammeeting' => $meeting_name ,
					'teammeetingpdfview' => $meeting_name . ' pdfview',
					'teammeetingpdfuserview' => $meeting_name .' pdfuserview',
			);
			if(is_array($filename))
			{
				foreach($filename as $k_file => $v_file)
				{
					$htmlform[$k_file] = Pms_Template::createTemplate($post_data, 'templates/' . $v_file);
					$html[$k_file] = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform[$k_file]);
				}
			}
			else
			{
				$htmlform = Pms_Template::createTemplate($post_data, 'templates/' . $filename);
				$html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
			}

			$pdf = new Pms_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
			$pdf->setDefaults(true); //defaults with header
			$pdf->setImageScale(1.6);
			$pdf->SetMargins(6, 5, 10); //reset margins
			$pdf->setPrintFooter(false); // remove black line at bottom
			$pdf->SetAutoPageBreak(TRUE, 10);

			//set page background for a defined page key in $background_pages array
			$bg_image = Pms_CommonData::getPdfBackground($post_data['clientid'], $pdf_type);
			if($bg_image !== false)
			{
				$bg_image_path = PDFBG_PATH . '/' . $post_data['clientid'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];
				if(is_file($bg_image_path))
				{
					$pdf->setBackgroundImage($bg_image_path);
				}
			}

			if($_REQUEST['dbgpdf'])
			{
				print_r($html[0]);
				exit;
			}

			if(is_array($html))
			{
				foreach($html as $k_html => $v_html)
				{
					if(is_array($orientation))
					{
						if(is_array($background_pages))
						{
							if(!in_array($k_html, $background_pages))
							{
								//unset page background for a nondefined page key in $background_pages array
								$pdf->setBackgroundImage();
							}
						}
						//each page has it`s own orientation
						$pdf->setHTML($v_html, $orientation[$k_html]);
					}
					else
					{
						//all pages one custom orientation
						$pdf->setHTML($v_html, $orientation);
					}
				}
			}
			else
			{
				if(empty($background_pages) && is_file($bg_image_path))
				{
					$pdf->setBackgroundImage($bg_image_path);
				}
				$pdf->setHTML($html, $orientation);
			}

// 			$tmpstmp = substr(md5(time() . rand(0, 999)), 0, 12);
// 			mkdir('clientuploads/' . $tmpstmp);
			$tmpstmp = Pms_CommonData::uniqfolder(CLIENTUPLOADS_PATH);
			
			if($pdfname == 'teammeeting' || $pdfname == 'teammeetingpdfview' || $pdfname == 'teammeetingpdfuserview')
			{
				
// 				$filepdf = str_replace(" ","_", $files_names[$pdfname]);
				$fileName = Pms_CommonData::normalizeString($files_names[$pdfname]);
				$filepdf = str_replace(" ","_", $fileName);
				
// 				str_replace("uploads", "clientuploads", PDF_PATH);
// 				$pdf->toFile(str_replace("uploads", "clientuploads", PDF_PATH) . '/' . $tmpstmp . '/' . $filepdf . '.pdf');
				$pdf->toFile(CLIENTUPLOADS_PATH . '/' . $tmpstmp . '/' . $filepdf . '.pdf');
				
				$_SESSION['filename'] = $tmpstmp . '/' . $filepdf . '.pdf';
				
			}
			else
			{
// 				str_replace("uploads", "clientuploads", PDF_PATH);
// 				$pdf->toFile(str_replace("uploads", "clientuploads", PDF_PATH) . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
				$pdf->toFile(CLIENTUPLOADS_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
				$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
			}
			


			
			//$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
// 			$cmd = "zip -9 -r -P " . $logininfo->filepass . " clientuploads/" . $tmpstmp . ".zip clientuploads/" . $tmpstmp . ";";
// 			exec($cmd);
			$zipname = $tmpstmp . ".zip";
			$filename = "clientuploads/" . $tmpstmp . ".zip";
// 			$con_id = Pms_FtpFileupload::ftpconnect();
// 			if($con_id)
// 			{
// 				$upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
// 				Pms_FtpFileupload::ftpconclose($con_id);
// 			}

			$ftp_put_queue = Pms_CommonData :: ftp_put_queue(CLIENTUPLOADS_PATH . '/'. $_SESSION['filename']  , 'clientuploads');
			
			if($pdfname == 'teammeeting' && $mode == "F")
			{
				$tabname = 'teammeeting';
				$cust = new ClientFileUpload();
				$cust->title = Pms_CommonData::aesEncrypt(addslashes($pdf_names[$pdfname]));
				$cust->clientid = $post_data['clientid'];
				$cust->file_name = Pms_CommonData::aesEncrypt($_SESSION ['filename']);
				$cust->file_type = Pms_CommonData::aesEncrypt('PDF');
				$cust->tabname = $tabname;
				$cust->recordid = $post_data['meetingid'];
				$cust->save();
			}
			if($pdfname == 'teamevent' && $mode == "F")
			{
				$tabname = 'teamevent';

				$cust = new ClientFileUpload();
				$cust->title = Pms_CommonData::aesEncrypt(addslashes($pdf_names[$pdfname]));
				$cust->clientid = $post_data['clientid'];
				$cust->file_name = Pms_CommonData::aesEncrypt($_SESSION ['filename']);
				$cust->file_type = Pms_CommonData::aesEncrypt('PDF');
				$cust->tabname = $tabname;
				$cust->recordid = $post_data['eventid'];
				$cust->save();
			}


			ob_end_clean();
			ob_start();

			if($mode == "D")
			{
				//download file
				if($pdfname == 'teammeeting' || $pdfname == 'teammeetingpdfview' || $pdfname == 'teammeetingpdfuserview')
				{
					$filepdf = str_replace(" ","_", $files_names[$pdfname]);
					$pdf->toBrowser($filepdf . '.pdf', "D");
					exit;
				}
				else
				{
					//download file
					$pdf->toBrowser($pdfname . '.pdf', "D");
					exit;
				}
			
			}
			else if($mode == "F")
			{
				//to file without bugging the user to download the file
//				$pdf->toFTP($pdfname . 'pdf');
			}
			else
			{
				//default (send to browser)
				$pdf->toBrowser($pdfname . '.pdf', "D");
				exit;
			}
		}

		public function meetingfileAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$this->_helper->layout->setLayout('layout');
			$this->_helper->viewRenderer->setNoRender();

			if($_GET['delid'] > 0)
			{
				$upload_form = new Application_Form_ClientFileUpload();
				$upload_form->deleteFile($_GET['delid'], 'teammeeting');

				if($_REQUEST['meetingid'])
				{
					$this->redirect(APP_BASE . 'team/teammeeting?meetingid=' . $_REQUEST['meetingid']);
				}
				else
				{
					$this->redirect(APP_BASE . 'team/teammeeting');
				}
				exit;
			}

			if($_REQUEST['doc_id'] > 0)
			{
				$patient = Doctrine_Query::create()
					->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
					->from('ClientFileUpload')
					->where('id= ?', $_REQUEST['doc_id'])
					->andWhere('clientid= ?', $clientid)
					->andWhere('tabname = ?', $_REQUEST['tab']);
				$flarr = $patient->fetchArray();

				if($flarr)
				{
					$explo = explode("/", $flarr[0]['file_name']);

					$fdname = $explo[0];
					$flname = utf8_decode($explo[1]);
				}

				if(!empty($flarr))
				{
					// if no doc_id - in db
// 					$con_id = Pms_FtpFileupload::ftpconnect();

// 					if($con_id)
// 					{
// 						$upload = Pms_FtpFileupload::filedownload($con_id, 'clientuploads/' . $fdname . '.zip', 'clientuploads/' . $fdname . '.zip');
// 						Pms_FtpFileupload::ftpconclose($con_id);
// 					}

					$file_password = $logininfo->filepass;

// 					$cmd = "unzip -P " . $file_password . " clientuploads/" . $fdname . ".zip;";
// 					exec($cmd);

					$old = $_REQUEST['old'] ? true : false;
					if (($path = Pms_CommonData::ftp_download('clientuploads/' . $fdname . '.zip' ,  $file_password , $old , $flarr[0]['clientid'] , $flarr[0]['file_name'])) === false){
						//failed to download file
					}
					
					$fullPath = $path ."/".$flname;
					
					$pre = '';
					//$path = $_SERVER['DOCUMENT_ROOT'] . $pre . "/clientuploads/" . $fdname . "/"; // change the path to fit your websites document structure
// 					$path = PUBLIC_PATH . $pre . "/clientuploads/" . $fdname . "/"; // change the path to fit your websites document structure
// 					$fullPath = $path . $flname;

					if($fd = fopen($fullPath, "r"))
					{
						$fsize = filesize($fullPath);
						$path_parts = pathinfo($fullPath);

						$ext = strtolower($path_parts["extension"]);
						
						ob_end_clean();
						ob_start();
						
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
							default;
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
						@readfile($fullPath);
						fclose($fd);
						unlink($fullPath);
					}
					
					exit;
				}
			}
		}

		//new sort method (sorts umlauts using multibyte string)
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
								if($on == 'stratDate')
								{
									$sortable_array[$k] = strtotime($v2);
								}
								elseif($on == 'epid')
								{
									$sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
								}
								else
								{
//									$sortable_array[$k] = ucfirst($v2);
									$sortable_array[$k] = trim(Pms_CommonData::mb_ucfirst($v2));
								}
							}
						}
					}
					else
					{
						if($on == 'stratDate')
						{
							$sortable_array[$k] = strtotime($v);
						}
						elseif($on == 'epid')
						{
							$sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
						}
						else
						{
							$sortable_array[$k] = trim(Pms_CommonData::mb_ucfirst($v));
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

		
		public function teameventAction()
		{
			set_time_limit(0);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$usertype = $logininfo->usertype;
			$userid = $logininfo->userid;
			$hidemagic = Zend_Registry::get('hidemagic');
		
			if(!$logininfo->clientid)
			{
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}
		
			$months_details[0]['start'] = date('Y-m-d');
			
			//attending voluntary workers
			$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
			if($connected_client){
			    $vw_clientid = $connected_client;
			} else{
			    $vw_clientid = $logininfo->clientid;
			}
			
			
			//get all team meetings of client and create select
			$all_events = TeamEvent::get_team_events($clientid, 'from_time');
			$last_event_id = "0";
			
			foreach($all_events as $k_event => $v_event)
			{
				if($_REQUEST['new_event'])
				{
					$event_selector[0] = '---';
				}
				$event_selector[$v_event['id']] = date('d.m.Y', strtotime($v_event['from_time'])) . ' ' . date('H:i', strtotime($v_event['from_time'])) . ' - ' . date('H:i', strtotime($v_event['till_time']));
			
				if($_REQUEST['eventid'] == $v_event['id'] && !empty($_REQUEST['eventid']))
				{
					$last_event_id = $v_event['id'];
				}
				else if(empty($_REQUEST['eventid']))
				{
					$last_event_id = $v_event['id'];
				}
			}
			
			if(count($all_events) == '0' && !$this->getRequest()->isPost() && $_REQUEST['new_event'] != '1')
			{
				$this->redirect(APP_BASE . 'team/teamevent?new_event=1');
				exit;
			}
			
			if($_POST['new_event'])
			{
				$last_event_id = '0';
			}
			
			if(empty($event_selector))
			{
				$event_selector[0] = $this->view->translate('no_team_events_saved_select');
			}
			
			if($_REQUEST['eventid'] > '0')
			{
				$this->view->eventid = $_REQUEST['eventid'];
				$event_id = $_REQUEST['eventid'];
			}
			else
			{
				$this->view->eventid = $last_event_id;
				$event_id = $last_event_id;
			}
			
			$this->view->event_selector = $event_selector;
			
			if($event_id != 0){
				ini_set("upload_max_filesize", "10M");
				/*			 * ********Deletefile*********** */
				if($_GET['delid'] > 0)
				{
					$has_edit_permissions = Links::checkLinkActionsPermission();
					if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
					{
						$this->_redirect(APP_BASE . "error/previlege");
						exit;
					}
					
					$upload_form = new Application_Form_ClientFileUpload();
					$upload_form->deleteFile($_GET['delid']);
				}
				/*			 * ************************************ */
				if($this->getRequest()->isPost())
				{
					
					$has_edit_permissions = Links::checkLinkActionsPermission();
					if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
					{
						$this->_redirect(APP_BASE . "error/previlege");
						exit;
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
						$a_post['tabname'] = 'teamevent_custom';
						$a_post['recordid'] = $event_id;
					
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
				}
			}
			
			
			
			//attending users and groups
			$ug = new Usergroup();
			$grouparr = $ug->getClientGroups($clientid);
			foreach($grouparr as $k_group => $v_group)
			{
				$client_groups[$v_group['id']] = $v_group;
			}
		
			$attendee_users = User::get_meeting_attendee_users($clientid);
			$attendee_users = $this->array_sort($attendee_users, 'last_name', SORT_ASC);
		
			
			$this->view->attendee_users = $attendee_users;
		
			foreach($attendee_users as $k_user => $v_user)
			{
				if($v_user['groupid'] != '0')
				{
					$available_user_groups[$v_user['groupid']] = $client_groups[$v_user['groupid']]['groupname'];
				}
			}
			$this->view->available_groups = $available_user_groups;

			
			//attending voluntary workers
			$vw_q = Doctrine_Query::create()
			->select('*')
			->from('Voluntaryworkers')
			->where('clientid = "' . $vw_clientid . '"')
			->andWhere("indrop = 0")
			->andWhere("isdelete = 0")
			->andWhere("inactive = 0")
			->andWhere("isarchived = 0")   /*ISPC-2401 p.13*/
			->orderBy('last_name ASC');
			$vw_array = $vw_q->fetchArray();
			
			foreach($vw_array as $kw => $vvw){
			    $attendee_vw[$vvw['id']] = $vvw;
			}
			$this->view->attendee_vw = $attendee_vw;
			
			// event types
			$event_types = TeamEventTypes::get_team_event_types ( $vw_clientid );
			
			foreach($event_types as $ket =>$vet){
				$event_types_select[$vet['id']] = $vet; 
			}
			$this->view->event_types_select = $event_types_select;

			
			
			//patient history users
			$all_users = Pms_CommonData::get_client_users($clientid, true);
		
			$all_users = $this->array_sort($all_users, 'last_name', SORT_ASC);
		
			foreach($all_users as $keyu => $user)
			{
				//all users is used in history!
				$all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
		
				if($user['usertype'] != 'SA')
				{
					$usr['id'] = $user['id'];
					$usr['value'] = $user['last_name'] . ", " . $user['first_name'];
		
					$js_users_array[] = $usr;
		
					//used in pdf
					$cli_usr_names['user_title'] = $user['user_title'];
					$cli_usr_names['last_name'] = $user['last_name'];
					$cli_usr_names['first_name'] = $user['first_name'];
					$client_users[$user['id']] = $cli_usr_names;
				}
			}
		
			$this->view->allusers = $all_users_array;
			$this->view->js_all_users = $js_users_array;
			$this->view->client_users = $client_users;
		
		
		
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$event_form = new Application_Form_TeamEvent();
				$post = $_POST;
		
				$post['clientid'] = $clientid;
		 
				//construct array event_details structure from post
				$event_details['client'] = $clientid;
				$event_details['event_name'] = $post['event_name'];
				$event_details['event_type'] = $post['event_type'];
				$event_details['date'] = date('Y-m-d H:i:s', strtotime($post['date']));
				$event_details['from_time'] = date('Y-m-d', strtotime($post['date'])) . ' ' . $post['from_time'] . ':00';
				$event_details['till_time'] = date('Y-m-d', strtotime($post['date'])) . ' ' . $post['till_time'] . ':00';
	 
				$event_details['attending_users'] = $post['attending_users'];
				$event_details['attending_vw'] = $post['attending_vw'];
		
				$post['event_details'] = $event_details;
				$post['client_users'] = $client_users;
				$post['attendee_users'] = $attendee_users;
				$post['attendee_vw'] = $attendee_vw;
				$post['event_types'] = $event_types_select;
				$post['event_date_pdf'] = date('d.m.Y', strtotime($event_details['date'])) . ' ' . date('H:i', strtotime($event_details['from_time'])) . ' - ' . date('H:i', strtotime($event_details['till_time']));

				
				
				if(!empty($_POST['save'])){
					
					if($event_form->validate($post)){
						$event_form->allusers = $all_users_array;
						$saved_event_id = $event_form->insert_event_data($post);
	
						//inserted or updated event id
						$post['eventid'] = $saved_event_id;
			
						//generate pdf without download and with internal history
						//LE: changed to generate pdf only on "completed" save
						if($post['completed'] == '1'){
							$template_files = array('teamevent_pdf.html');
							$orientation = array('P');
							$background_pages = false; //array('0') is first page;
							$mode = "F";
							
							$this->generate_pdf($post, "teamevent", $template_files, $orientation, $background_pages, $mode);
						}
						//then redirect page
						$this->redirect(APP_BASE . 'team/teamevent?eventid=' . $saved_event_id);
						exit;
					
					} else {
						
						$event_form->assignErrorMessages();
						$this->retainValues($post);
					}
					
				}
				else if(!empty($_POST['pdf']))
				{
					$template_files = array('teamevent_pdf.html');
					$orientation = array('P');
					$background_pages = false; //array('0') is first page;
					$mode = "D";
					
					$this->generate_pdf($post, "teamevent", $template_files, $orientation, $background_pages, $mode);
					exit;
				}  
 			}

 			
            //get event details if we have an existing event
    		if ($last_event_id != '0') {
    			$event_details = TeamEvent::get_event_details ( $last_event_id );
    		
    			$this->view->event_details = $event_details;
    			$this->view->event_name = $event_details ['event_name'];
    			$this->view->event_type = $event_details ['event_type'];
    			$this->view->date = date ( 'd.m.Y', strtotime ( $event_details ['date'] ) );
    			$this->view->from_time = date ( 'H:i', strtotime ( $event_details ['from_time'] ) );
    			$this->view->till_time = date ( 'H:i', strtotime ( $event_details ['till_time'] ) );
    			$this->view->completed = $event_details ['completed'];
    			$this->view->organizational = $event_details ['organizational'];
    			$this->view->attending_users = $event_details ['attending_users'];
    			$this->view->attending_vw = $event_details ['attending_vw'];
    			$this->view->voluntary_event = $event_details ['voluntary_event'];
    		} 
    		else
    		{
    			
    			$this->view->date = date ( 'd.m.Y', time () );
    			$this->view->from_time = date ( 'H:i', time () );
    			$this->view->till_time = date ( 'H:i', strtotime ( '+1 hour', time () ) );
    		}
		
            $history = ClientFileUpload::get_client_files_sorted( $clientid, array ('teamevent','teamevent_custom') );
            
            foreach($history as $ke => $ve){
            	$files[$ve['recordid']] []= $ve; 
            }
            $this->view->event_files = $files;
            $this->view->files_history = $history;
		}
		
		
		
		public function teameventtypesAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('team', $logininfo->userid, 'canview');
		
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
		}
		
	
		public function fetchteameventtypesAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('team', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$columnarray = array("na" => "name");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			
			// get associated clients of current clientid START
			$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
			if($connected_client){
			    $clientid = $connected_client;
			} else{
			    $clientid = $logininfo->clientid;
			}
			
			
			if($clientid > 0)
			{
				$where = ' and client=' . $clientid;
			}
			else
			{
				$where = ' and client=0';
			}

			$limit = 50;
			$fdoc = Doctrine_Query::create()
				->select('count(*)')
				->from('TeamEventTypes')
				->where("isdelete = 0 " . $where)
				->andWhere('name != ""')
				->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
				$fdoc->andWhere("`name` like ?","%" . trim($_REQUEST['val']) . "%");
			}
			$fdoc->offset($_REQUEST['pgno'] * $limit);
			$fdocarray = $fdoc->fetchArray();

			$fdoc->select('*');
			$fdoc->where("`isdelete` ='0' ".$where."");
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
				$fdoc->andWhere("`name` like ?","%" . trim($_REQUEST['val']) . "%");
			}
			$fdoc->limit($limit);
			$fdoc->offset($_REQUEST['pgno'] * $limit);

			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
			$team_event_type[] = '9999999999999';
			foreach($fdoclimit as $k_limit => $v_limit)
			{
				$team_event_type[] = $v_limit['id'];
			}

			$this->view->{"style" . $_GET['pgno']} = "active";
			$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "teameventtypes.html");

			if($fdoclimit)
			{
				$this->view->typesgrid = $grid->renderGrid();
			}
			else
			{
				$this->view->typesgrid = '<tr><td colspan="4"><center>' . $this->view->translate('no_events_types') . '</center></td></tr>';
			}

			$this->view->navigation = $grid->dotnavigation("teameventtypesnavigation.html", 5, $_REQUEST['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['teameventtypes'] = $this->view->render('team/fetchteameventtypes.html');

			echo json_encode($response);
			exit;
		}
		

		public function addteameventtypeAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
		
			
			// get associated clients of current clientid START
			$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
			if($connected_client){
			    $clientid = $connected_client;
			} else{
			    $clientid = $logininfo->clientid;
			}
			
			
			if($this->getRequest()->isPost())
			{
				$team_event_type_form = new Application_Form_TeamEventTypes();
		
				$post = $_POST;
				$post['client'] = $clientid;
		
				if($team_event_type_form->validate($post))
				{
					$team_event_type_form->insert($post);
		
					$this->_redirect(APP_BASE . 'team/teameventtypes?flg=suc');
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				}
				else
				{
					$team_event_type_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}
		
		
		public function editteameventtypeAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$this->view->act = "team/addteameventtype?id=" . $_REQUEST['id'];
		
			$this->_helper->viewRenderer('addteameventtype');
		
			// get associated clients of current clientid START
			$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
			if($connected_client){
			    $clientid = $connected_client;
			} else{
			    $clientid = $logininfo->clientid;
			}
			
			
			if($this->getRequest()->isPost())
			{
				$team_event_type_form = new Application_Form_TeamEventTypes();
		
				if($team_event_type_form->validate($_POST))
				{
					$a_post = $_POST;
					$tid = $_REQUEST['tid'];
					$a_post['tid'] = $tid;
					$a_post['clientid'] = $clientid;
		
					$team_event_type_form->update($a_post);
					$this->_redirect(APP_BASE . 'team/teameventtypes?flg=suc');
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				}
				else
				{
					$fdoctor_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		
			if($_REQUEST['tid'] > 0)
			{
				$fdoc = Doctrine::getTable('TeamEventTypes')->find($_REQUEST['tid']);
		
				if($fdoc)
				{
					$fdocarray = $fdoc->toArray();
					$this->retainValues($fdocarray);
				}
				$clientid = $fdocarray['clientid'];
			}
		}
		

		public function deleteteameventtypeAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
				
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			if($_REQUEST['tid'] > '0')
			{
				$fdoc = Doctrine::getTable('TeamEventTypes')->findOneByIdAndClient($_REQUEST['tid'], $logininfo->clientid);
		
				if($fdoc)
				{
					$fdoc->isdelete = 1;
					$fdoc->save();
				}
			}
			$this->redirect(APP_BASE.'team/teameventtypes');
			exit;
		}
		
		
		private function retainValues($values)
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}

		

		public function uploadfilesAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
		
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
								->where('id= ?', $_GET['doc_id']);
				$fl = $patient->execute();
		
				if($fl)
				{
					$flarr = $fl->toArray();
		
					$explo = explode("/", $flarr[0]['file_name']);
		
					$fdname = $explo[0];
					$flname = utf8_decode($explo[1]);
				}
		
// 				$con_id = Pms_FtpFileupload::ftpconnect();
// 				if($con_id)
// 				{
// 					$upload = Pms_FtpFileupload::filedownload($con_id, 'clientuploads/' . $fdname . '.zip', 'clientuploads/' . $fdname . '.zip');
// 					Pms_FtpFileupload::ftpconclose($con_id);
// 				}
		
				$old = $_REQUEST['old'] ? true : false;
				if (($path = Pms_CommonData::ftp_download('clientuploads/' . $fdname . '.zip' ,  $logininfo->filepass , $old , $flarr[0]['clientid'] , $flarr[0]['file_name'])) === false){
					//failed to download file
				}
				
				$full_path = $path ."/".$flname;
				
// 				$cmd = "unzip -P " . $logininfo->filepass . " clientuploads/" . $fdname . ".zip;";
// 				exec($cmd);
		
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
			$filesData = $files->getClientFiles($logininfo->clientid);
		
			$this->view->filesData = $filesData;
			$this->view->showInfo = $logininfo->showinfo;
		
			$allUsers = Pms_CommonData::getClientUsers($logininfo->clientid);
			foreach($allUsers as $keyu => $user)
			{
				$allUsersArray[$user['id']] = $user['last_name'] . ", " . $user['first_name'];
			}
			$this->view->allusers = $allUsersArray;
		}
		
		
		public function clientuploadifyAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$extension = explode(".", $_FILES['qqfile']['name']);
		
			$_SESSION['filetype'] = $extension[count($extension) - 1];
			$_SESSION['filetitle'] = $extension[0];
			$timestamp_filename = time() . "_file." . $extension[count($extension) - 1];
		
			
			
			
// 			$path = 'clientuploads';
// 			$dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
// 			while(!is_dir($path . '/' . $dir))
// 			{
// 				$dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
// 				mkdir($path . '/' . $dir);
// 				if($i >= 50)
// 				{
// 					exit; //failsafe
// 				}
// 				$i++;
// 			}
			
			$dir = Pms_CommonData::uniqfolder(CLIENTUPLOADS_PATH);
			$folderpath = $dir;
			
		
// 			$filename = "clientuploads/" . $folderpath . "/" . trim($timestamp_filename);
			$filename = CLIENTUPLOADS_PATH . "/" . $folderpath . "/" . trim($timestamp_filename);
			$_SESSION['filename'] = $folderpath . "/" . trim($timestamp_filename);
			move_uploaded_file($_FILES['qqfile']['tmp_name'], $filename);

			$_SESSION['zipname'] = CLIENTUPLOADS_PATH . "/" . $folderpath . ".zip";
			
			$cmd = "sh -c \"cd '".CLIENTUPLOADS_PATH."/../'  && zip -9 -r -P " . $logininfo->filepass ." " . $_SESSION['zipname']. " clientuploads/{$folderpath}" . '/* && rm -r clientuploads/'.$folderpath.'"';
// 			echo $cmd;
			
// 			$cmd = "zip -9 -r -P " . $logininfo->filepass . " clientuploads/" . $folderpath . ".zip  clientuploads/" . $folderpath . "; rm -r clientuploads/" . $folderpath . ";";
			exec($cmd);
// 			$_SESSION['zipname'] = $folderpath . ".zip";
			$zipname = $folderpath . ".zip";
// 			$con_id = Pms_FtpFileupload::ftpconnect();
// 			if($con_id)
// 			{
// 				$upload = Pms_FtpFileupload::fileupload($con_id, 'clientuploads/' . $zipname, 'clientuploads/' . $zipname);
// 				Pms_FtpFileupload::ftpconclose($con_id);
// 			}
			echo json_encode(array(success => true));
			exit;
		}
		
		

		public function eventfileAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
		
			$this->_helper->layout->setLayout('layout');
			$this->_helper->viewRenderer->setNoRender();
		
			if($_GET['delid'] > 0)
			{
				$upload_form = new Application_Form_ClientFileUpload();
// 				$upload_form->deleteFile($_GET['delid'], 'teamevent');
				$upload_form->deleteFile($_GET['delid']);
		
				if($_REQUEST['eventid'])
				{
					$this->redirect(APP_BASE . 'team/teamevent?eventid=' . $_REQUEST['eventid']);
				}
				else
				{
					$this->redirect(APP_BASE . 'team/teamevent');
				}
				exit;
			}
		
			if($_REQUEST['doc_id'] > 0)
			{
				$patient = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
								->from('ClientFileUpload')
								->where('id= ?', $_REQUEST['doc_id'])
								->andWhere('clientid= ?', $clientid)
								->andWhere('tabname = ?', $_REQUEST['tab']);
				$flarr = $patient->fetchArray();
		
				if($flarr)
				{
					$explo = explode("/", $flarr[0]['file_name']);
		
					$fdname = $explo[0];
					$flname = utf8_decode($explo[1]);
				}
		
				if(!empty($flarr))
				{
// 					print_r($flarr);
// 					die();
					// if no doc_id - in db
// 					$con_id = Pms_FtpFileupload::ftpconnect();
		
// 					if($con_id)
// 					{
// 						$upload = Pms_FtpFileupload::filedownload($con_id, 'clientuploads/' . $fdname . '.zip', 'clientuploads/' . $fdname . '.zip');
// 						Pms_FtpFileupload::ftpconclose($con_id);
// 					}
		
					$file_password = $logininfo->filepass;
		
					$old = $_REQUEST['old'] ? true : false;
					if (($path = Pms_CommonData::ftp_download('clientuploads/' . $fdname . '.zip' , $file_password , $old , $flarr[0]['clientid'] , $flarr[0]['file_name'])) === false){
						//failed to download file
					}
					
// 					$cmd = "unzip -P " . $file_password . " clientuploads/" . $fdname . ".zip;";
// 					exec($cmd);
		
					$pre = '';
					//$path = $_SERVER['DOCUMENT_ROOT'] . $pre . "/clientuploads/" . $fdname . "/"; // change the path to fit your websites document structure
// 					$path = PUBLIC_PATH . $pre . "/clientuploads/" . $fdname . "/"; // change the path to fit your websites document structure
					
// 					$fullPath = $path . $flname;
					$fullPath = $path . "/" . $flname;

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
							default;
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
		
		//ispc 1817
		public function addusercommentAction(){
			 
			if (!$this->getRequest()->isXmlHttpRequest()) {
				die('!isXmlHttpRequest');
			}
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			//verify if this user is allowed to edit 
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('user', $logininfo->userid, 'canedit');
			
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			
			$user_form = new Application_Form_User();
			$user = $user_form->add_user_comment((int)$_POST['userid'] , $logininfo->clientid, htmlentities($_POST['usercomment']));
			
			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "addusercommentcallback";
			$response['callBackParameters'] = array();
			$response['callBackParameters'] = array("id"=>(int)$_POST['userid']);
			
			echo json_encode($response);
			
			exit;
			
		}
		
		
		/**
		 *
		 * Jul 14, 2017 @claudiu
		 *
		 * @return multitype:
		 */
		private function get_nice_name_multiselect ()
		{
				
			$selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();
				
			$todousersarr = array(
					"0" => $this->view->translate('select'),
					//$selectbox_separator_string['all'] => $this->view->translate('all')
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
					if ( ! empty($users_in_pseudogroups[$row['id']])){
						
// 					$pseudogrouparraytodo[$selectbox_separator_string['pseudogroup'] . $row['id']] = $row['servicesname'];
					$pseudogrouparraytodo[$selectbox_separator_string['pseudogroup'] . $row['id']] = str_replace('"', ' ', str_replace("'", " ", $row['servicesname'])); // Hack for JS (TODO - 1145)
					}
				}
					
				$todousersarr[$this->view->translate('liste_user_pseudo_group')] = $pseudogrouparraytodo;
			}
			$todousersarr[$this->view->translate('users')] = $userarraytodo;
			return $todousersarr;
		}
	
    /**
     * this fn should have the same permisions as fetchteampatientsAction
     * fetchteampatientsAction has no restrictions... so same here
     * 
     * @throws Exception
     */
    public function prefillteamproblemAction() 
    {
        
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        
        $resultArray = array();
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        
        $ipid = $this->getRequest()->getParam('ipid');
        $case =  $this->getRequest()->getParam('patient_case');//Maria:: Migration CISPC to ISPC 22.07.2020
        if (empty($ipid)) {
            $this->_helper->getHelper('json')->sendJson($resultArray);
            exit; //for readability
        }
        
        $actual_id = $this->getRequest()->getParam('actual_id');
        if (empty($actual_id)) {
        	$actual_id = null;
        }
        
        $meeting_date = $this->getRequest()->getParam('meeting_start_date');
        if (empty($meeting_date)) {
			$meeting_date = date('d.m.Y', time());
        }
        
        $meeting_start_time = $this->getRequest()->getParam('meeting_start_time');
        if (empty($meeting_start_time)) {
			$meeting_start_time =  date('H:i', time());
        }
        
        
        $start_date_time = date("Y-m-d H:i:s",strtotime(date('Y-m-d', strtotime($meeting_date)) . ' ' . $meeting_start_time . ':00'));
        
        $last_meeting_details = TeamMeeting::get_last_team_meetings_for_case($this->logininfo ->clientid, $case, $actual_id, $start_date_time);//Maria:: Migration CISPC to ISPC 22.07.2020

        //get meeting details
        if ( ! empty($last_meeting_details['id'])) {
            
            $meeting_details = TeamMeetingDetails::get_ipid_details($ipid, $last_meeting_details['id'], $this->logininfo ->clientid);
            
            foreach ($meeting_details as $row) {
                $resultArray[] = array(
                     'send_todo' => $row['send_todo'],
                     'verlauf' => $row['verlauf'],
                     'problem' => $row['problem'],
                     'targets' => $row['targets'], //ISPC-2556 Andrei 27.05.2020
                     'current_situation'  => $row['current_situation'],     //ISPC-2896 Lore 19.04.2021
                     'hypothesis_problem' => $row['hypothesis_problem'],    //ISPC-2896 Lore 19.04.2021
                     'measures_problem'   => $row['measures_problem'],      //ISPC-2896 Lore 19.04.2021
                     'todo' => $row['todo'],
                     'status' => $row['status'],
                );
                
            }
        }
        
        
        $this->_helper->getHelper('json')->sendJson($resultArray);
        exit; //for readability
        
    }
    
    public function teammeetinghistorylistAction () {
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$clientid = $logininfo->clientid;
    	//patient history users
    	$all_users = Pms_CommonData::get_client_users($clientid, true);
    	
    	$all_users = $this->array_sort($all_users, 'last_name', SORT_ASC);
    	
    	foreach($all_users as $keyu => $user)
    	{
    		//all users is used in history!
    		$all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
    	}
    	
    	$meeting = '';
    	if($_REQUEST['meetingid'])
    	{
    		$meeting = "meetingid=".$_REQUEST['meetingid'].'&';
    	}
    
    	//populate the datatables
    	if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
    		$this->_helper->layout()->disableLayout();
    		$this->_helper->viewRenderer->setNoRender(true);
    
    		if(!$_REQUEST['length'])
    		{
    			$_REQUEST['length'] = "10";
    		}
    			
    		$limit = $_REQUEST['length'];
    		$offset = $_REQUEST['start'];
    		$search_value = $_REQUEST['search']['value'];
    			
    		$columns_array = array(
    				"0" => "create_date",
    				"1" => "title",
    				"2" => "file_type",
    				"3" => "create_user_name"
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
    		
    		//get teameeting history
    		$history = ClientFileUpload::get_latest_on_top_client_files($clientid, array('teammeeting'));
    		
    		foreach($history as $fkey=>$fval)
    		{
    			$fval['create_user_name'] = $all_users_array[$fval['create_user']];
    			if($fval['isdeleted'] == 1)
    			{
    				$fval['title'] = $fval['title'] . ' - '. $this->translator->translate('deleted');
    			}
    			
    			$fdoclimit[$fkey] = $fval;
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
    			$link = '%s ';
    			
    			$resulted_data[$row_id]['create_date'] = sprintf($link, date('d.m.Y', strtotime($mdata['create_date'])));
    			if($mdata['isdeleted'] == 1)
    			{
    				$title_arr = explode(' - ', $mdata['title']);
    				$resulted_data[$row_id]['title'] = sprintf($link, "<strike>" . $title_arr[0] . "</strike>" . " - " . $title_arr[1]);
    			}
    			else
    			{
    				$resulted_data[$row_id]['title'] = '<a href="'.APP_BASE.'team/meetingfile?doc_id=' . $mdata['id']. '&tab='.$mdata['tabname'].'">' . $mdata['title'] . '</a>';
    			}
    			$resulted_data[$row_id]['filetype'] = sprintf($link, strtoupper($mdata['file_type']));
    			$resulted_data[$row_id]['create_user_name'] = sprintf($link, $mdata['create_user_name']);
    			if($mdata['isdeleted'] != 1)
    			{
    				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE.'team/meetingfile?'.$meeting.'delid=' . $mdata['id'] . '"><img src="' . RES_FILE_PATH . '/images/action_delete.png" border="0" /></a>';
    			}
    			else 
    			{
    				$resulted_data[$row_id]['actions'] = '';
    			}
    			$row_id++;
    		}
    			
    		$response['draw'] = (int)$_REQUEST['draw']; //? get the sent draw from data table
    		$response['recordsTotal'] = $full_count;
    		$response['recordsFiltered'] = $filter_count; // ??
    		$response['data'] = $resulted_data;
    			
    		$this->_helper->json->sendJson($response);
    	}
    }
    
    public function teammeetingpatientsearchlistAction () {
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$clientid = $logininfo->clientid;
    	
    	$field_value = $_REQUEST['field_value'];
		$search_string = addslashes(urldecode(trim($_REQUEST['field_value'])));
		$patient_status = $_REQUEST['status'];
		
    	//populate the datatables
    	if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
    		$this->_helper->layout()->disableLayout();
    		$this->_helper->viewRenderer->setNoRender(true);
    
    		if(!$_REQUEST['length'])
    		{
    			$_REQUEST['length'] = "10";
    		}
    		
    		$limit = $_REQUEST['length'];
    		$offset = $_REQUEST['start'];
    		$search_value = $_REQUEST['search']['value'];
    		 
    		$columns_array = array(
    				"1" => "epid",
    				"2" => "first_name",
    				"3" => "last_name"
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
    
    		//get teameeting patients
    		if(strlen($_REQUEST['meetingid']) > 0 && !empty($_REQUEST['meetingid']))
			{
				$meeting_id = $_REQUEST['meetingid'];
				$team_patients_array = TeamMeetingPatients::get_team_meeting_patients($meeting_id, $clientid);
				if(!empty($team_patients_array))
				{
					foreach($team_patients_array as $k => $pat_data)
					{
						$existing_team_patients[] = $pat_data['patient'];
					}
				}
			}

			$standby_sql = "";
			$discharge_sql = "";
			if(!empty($patient_status))
			{
				if($patient_status == "standby")
				{
					$standby_sql = " AND isstandby = 1 ";
				}
				else if($patient_status == "discharged_alive" || $patient_status == "discharged_dead")
				{
					$discharge_sql = " AND isdischarged = 1 AND isstandby = 0  AND isarchived = 0 ";
				}
			}




			if(!empty($_REQUEST['status']) && ($_REQUEST['status'] == "discharged_alive" || $_REQUEST['status'] == "discharged_dead"))
			{

				$discharge_method = new DischargeMethod();
				$discharge_methods = $discharge_method->getDischargeMethod($clientid, 0);
				foreach($discharge_methods as $k_dis_method => $v_dis_method)
				{
					if($v_dis_method['abbr'] == "TOD" || $v_dis_method['abbr'] == "TODNA" || $v_dis_method['abbr'] == "verstorben")
					{
						$death_methods[] = $v_dis_method['id'];
					}
				}
				$death_methods = array_values(array_unique($death_methods));
				if(empty($death_methods))
				{
					$death_methods[] = "999999";
				}
			}


			if(strlen($_REQUEST['field_value']) > 2 || !empty($_REQUEST['status']))
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('EpidIpidMapping')
					->where("clientid = '" . $clientid . "'")
					->orderBy('epid asc');
				$droparray = $drop->fetchArray();

				if($droparray)
				{
					foreach($droparray as $key => $val)
					{
						$ipidval .= $comma . "'" . $val['ipid'] . "'";
						$comma = ",";
						$client_ipids[] = $val['ipid'];
					}
				}

				if(empty($client_ipids))
				{
					$client_ipids[] = "999999";
				}

				if(count($droparray) > 0)
				{
					$sql = "*,e.epid,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
					$sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
					$sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
					$sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
					$sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
					$sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
					$sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
					$sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
					$sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
					$sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
					$sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
					//if isstandby it must be shown as Anfrange in LS even if is also isdischarged
					$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
					$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

					// if super admin check if patient is visible or not
					if($logininfo->usertype == 'SA')
					{
						$sql = "*, e.epid, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
						$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
					}

					$patient = Doctrine_Query::create()
						->select($sql)
						->from('PatientMaster p')
						->whereIn("p.ipid", $client_ipids)
						->andWhere("p.isdelete = 0")
						->andWhere('isstandbydelete="0" ' . $standby_sql . $discharge_sql . ' ');
					if(!empty($existing_team_patients))
					{
						$patient->whereNotIn("p.ipid", $existing_team_patients);
					}
					$patient->leftJoin("p.EpidIpidMapping e");
					$patient->andwhere("e.clientid = " . $logininfo->clientid);
					$patient->andwhere("trim(lower(e.epid)) like ? or 
						(trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ? or 
						trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?  or 
						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?)",
							array(
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
							);
					if($logininfo->hospiz == 1)
					{
						$patient->andwhere('ishospiz = 1');
					}
					$patient->orderby('status,ipid');
					$droparray1 = $patient->fetchArray();

					if($_REQUEST['status'] == "discharged_alive" || $_REQUEST['status'] == "discharged_dead")
					{

						if(!empty($droparray1))
						{

							foreach($droparray1 as $k => $pdata)
							{
								$discharged_patients[] = $pdata['ipid'];
								$discharged_patients_str .= '"' . $pdata['ipid'] . '", ';
							}

							if(!empty($discharged_patients))
							{
								$discharged_patients[] = "999999";
							}

							$discharged_patients_array = array();

							$patient_discharge = Doctrine_Query::create();
							$patient_discharge->from('PatientDischarge d');
							$patient_discharge->whereIn("d.ipid", $discharged_patients);
							$patient_discharge->andWhere("d.isdelete = 0");
							if($_REQUEST['status'] == "discharged_alive")
							{
								$patient_discharge->andWhereNotIn('d.discharge_method', $death_methods);
							}
							else
							{
								$patient_discharge->andWhereIn('d.discharge_method', $death_methods);
								$patient_discharge->andWhere("date(d.discharge_date) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 4 WEEK) AND CURRENT_DATE() ");
							}
							$discharged_patients_q_array = $patient_discharge->fetchArray();


							if(!empty($discharged_patients_q_array))
							{
								foreach($discharged_patients_q_array as $d_key => $d_patient)
								{
									$discharged_patients_array[] = $d_patient['ipid'];
								}
							}

							foreach($droparray1 as $patient_k => $patient_data)
							{
								if(!in_array($patient_data['ipid'], $discharged_patients_array))
								{
									$unseted[] = $droparray1[$patient_k];
									unset($droparray1[$patient_k]);
								}
							}
						}
					}
				}
				elseif($logininfo->showinfo == 'show')
				{
					$fndrop = Doctrine_Query::create()
						->select('*')
						->from('EpidIpidMapping')
						->where("clientid = '" . $clientid . "'");
					$fndroparray = $fndrop->fetchArray();
					if($fndroparray)
					{
						$comma = ",";
						$fnipidval = "'0'";
						foreach($fndroparray as $key => $val)
						{
							$fnipidval .= $comma . "'" . $val['ipid'] . "'";
							$comma = ",";
							$client_ipids[] = $val['ipid'];
						}
					}

					if(empty($client_ipids))
					{
						$client_ipids[] = "999999";
					}
					$patient1 = Doctrine_Query::create()
						->select("*, e.epid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
						AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,
						AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,
						AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as oll,
						AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,
						AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
						AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
						AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip
						,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city
						,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone
						,AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,
						IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,
						,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
						->from('PatientMaster p')
						->leftJoin("p.EpidIpidMapping e")
						->whereIn("p.ipid", $client_ipids)
						->andWhere("p.isdelete = 0");

					if(!empty($existing_team_patients))
					{
						$patient1->whereNotIn("p.ipid", $existing_team_patients);
					}

					$patient1->andWhere("(trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ? or 
							trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?  or 
							concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
								concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?)",
							array(
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
							)
						->andwhere("e.clientid = " . $logininfo->clientid)
						->andWhere('isstandbydelete="0"  ' . $standby_sql . $discharge_sql . '  ')
						->orderby('status');

					$droparray2 = $patient1->fetchArray();

					if($_REQUEST['status'] == "discharged_alive" || $_REQUEST['status'] == "discharged_dead")
					{
						if(!empty($droparray2))
						{
							foreach($droparray2 as $k => $pdata)
							{
								$discharged_patients2[] = $pdata['ipid'];
							}

							if(!empty($discharged_patients2))
							{
								$discharged_patients2[] = "999999";
							}

							$discharged_patients_array_2 = array();

							$patient_discharge = Doctrine_Query::create();
							$patient_discharge->from('PatientDischarge d');
							$patient_discharge->whereIn("d.ipid", $discharged_patients2);
							$patient_discharge->andWhere("d.isdelete = 0");
							if($_REQUEST['status'] == "discharged_alive")
							{
								$patient_discharge->andWhereNotIn('d.discharge_method', $death_methods);
							}
							else
							{
								$patient_discharge->andWhereIn('d.discharge_method', $death_methods);
								$patient_discharge->andWhere("date(d.discharge_date) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 4 WEEK) AND CURRENT_DATE() ");
							}
							$discharged_patients_array_2 = $patient_discharge->fetchArray();

							if(!empty($discharged_patients_array_2))
							{
								foreach($discharged_patients_array_2 as $d_key => $d_patient)
								{
									$discharged_patients_array2[] = $d_patient['ipid'];
								}
							}
							foreach($droparray2 as $patient_k => $patient_data)
							{
								if(!in_array($patient_data['ipid'], $discharged_patients_array2))
								{
									$unseted2[] = $droparray2[$patient_k];
									unset($droparray2[$patient_k]);
								}
							}
						}
					}
				}
			}

			$res_data = array();


			if(is_array($droparray2) || is_array($droparray1))
			{
				$results = array_merge((array) $droparray2, (array) $droparray1);

				foreach($results as $i => $res)
				{
					$res_data[$i]['status'] = $res['status'];
					$res_data[$i]['epid'] = $res['EpidIpidMapping']['epid'];
					$res_data[$i]['first_name'] = $res['first_name'];
					$res_data[$i]['last_name'] = $res['last_name'];

					if(strlen($res['middle_name']) > 0)
					{
						$res_data[$i]['middle_name'] = $res['middle_name'];
					}
					else
					{
						$res_data[$i]['middle_name'] = " ";
					}

					if($res['admission_date'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['admission_date'] = date('d.m.Y', strtotime($res['admission_date']));
					}
					else
					{
						$res_data[$i]['recording_date'] = "-";
					}

					if($res['recording_date'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['recording_date'] = date('d.m.Y', strtotime($res['recording_date']));
					}
					else
					{
						$res_data[$i]['recording_date'] = "-";
					}

					if($res['birthd'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['birthd'] = date('d.m.Y', strtotime($res['birthd']));
					}
					else
					{
						$res_data[$i]['birthd'] = "-";
					}

					$res_data[$i]['birthd'] = Pms_CommonData::hideInfo($res['birthd'], $res['isadminvisible']);

					$res_data[$i]['id'] = Pms_Uuid::encrypt($res['id']);
				}
				$patient_data = $res_data;
			}
			else
			{
				$patient_data = array();
			}
			
    		$fdoclimit = $patient_data;    
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
    		 
    		if($limit > 0)
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
    		 
    		foreach($fdoclimit_arr as $report_id =>$mdata)
    		{
    			$link = '%s ';
    			 
    			$resulted_data[$row_id]['checkloc'] = '<input class="select_patients" name="patients[]" id=pat_"'.$mdata['id'].'" type="checkbox" del="1" rel="'.$mdata['id'].'" value="' . $mdata['epid'] .'" />';
    			$resulted_data[$row_id]['epid'] = sprintf($link, $mdata['epid']);
    			$resulted_data[$row_id]['first_name'] = sprintf($link, $mdata['first_name']);
    			$resulted_data[$row_id]['last_name'] = sprintf($link, $mdata['last_name']);
    			$row_id++;
    		}
    		 
    		$response['draw'] = (int)$_REQUEST['draw']; //? get the sent draw from data table
    		$response['recordsTotal'] = $full_count;
    		$response['recordsFiltered'] = $filter_count; // ??
    		$response['data'] = $resulted_data;
    		 
    		$this->_helper->json->sendJson($response);
    	}
    }
    
    public function teameventhistorylistAction () {
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$clientid = $logininfo->clientid;
    	//patient history users
    	$all_users = Pms_CommonData::get_client_users($clientid, true);
    	 
    	$all_users = $this->array_sort($all_users, 'last_name', SORT_ASC);
    	 
    	foreach($all_users as $keyu => $user)
    	{
    		//all users is used in history!
    		$all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
    	}
    	 
    	$eventid = '';
    	if($_REQUEST['eventid'])
    	{
    		$eventid = "eventid=".$_REQUEST['eventid'].'&';
    	}
    
    	//populate the datatables
    	if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
    		$this->_helper->layout()->disableLayout();
    		$this->_helper->viewRenderer->setNoRender(true);
    
    		if(!$_REQUEST['length'])
    		{
    			$_REQUEST['length'] = "10";
    		}
    		 
    		$limit = $_REQUEST['length'];
    		$offset = $_REQUEST['start'];
    		$search_value = $_REQUEST['search']['value'];
    		 
    		$columns_array = array(
    				"1" => "create_date",
    				"2" => "title",
    				"3" => "file_type",
    				"4" => "create_user_name"
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
    
    		//get teaevent history
    		$history = ClientFileUpload::get_client_files_sorted( $clientid, array ('teamevent','teamevent_custom') );
    		
    		foreach($history as $fkey=>$fval)
    		{
    			$fval['create_user_name'] = $all_users_array[$fval['create_user']];
    			if($fval['isdeleted'] == 1)
    			{
    				$fval['title'] = $fval['title'] . ' - '. $this->translator->translate('deleted');
    			}
    			 
    			$fdoclimit[$fkey] = $fval;
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
    				if($order_column == '1')
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
    			$link = '%s ';
    			
    			if($mdata['tabname'] == 'teamevent_custom')
    			{
    				$resulted_data[$row_id]['tabname'] = 'teamevent_custom';
    				$resulted_data[$row_id]['create_date'] = sprintf($link, date('d.m.Y', strtotime($mdata['create_date'])));
    			}
    			else
    			{
    				$resulted_data[$row_id]['tabname'] = '';
    				$resulted_data[$row_id]['create_date'] = sprintf($link, date('d.m.Y', strtotime($mdata['create_date'])));
    			}
    			
    			if($mdata['isdeleted'] == 1)
    			{
    				$title_arr = explode(' - ', $mdata['title']);
    				$resulted_data[$row_id]['title'] = sprintf($link, "<strike>" . $title_arr[0] . "</strike>" . " - " . $title_arr[1]);
    			}
    			else
    			{
    				$resulted_data[$row_id]['title'] = '<a href="'.APP_BASE.'team/eventfile?doc_id=' . $mdata['id']. '&tab='.$mdata['tabname'].'">' . $mdata['title'] . '</a>';
    			}
    			$resulted_data[$row_id]['filetype'] = sprintf($link, strtoupper($mdata['file_type']));
    			$resulted_data[$row_id]['create_user_name'] = sprintf($link, $mdata['create_user_name']);
    			if($mdata['isdeleted'] != 1)
    		    {
    				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE.'team/eventfile?'.$eventid.'delid=' . $mdata['id'] . '"><img src="' . RES_FILE_PATH . '/images/action_delete.png" border="0" /></a>';
    			}
    			else
    			{
    				$resulted_data[$row_id]['actions'] = '';
    			}
    			$row_id++;
    		}
    		 
    		$response['draw'] = (int)$_REQUEST['draw']; //? get the sent draw from data table
    		$response['recordsTotal'] = $full_count;
    		$response['recordsFiltered'] = $filter_count; // ??
    		$response['data'] = $resulted_data;
    		 
    		$this->_helper->json->sendJson($response);
    	}
    }
}

?>