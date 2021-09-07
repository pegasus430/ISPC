<?php

	require_once("Pms/Form.php");

	class Application_Form_TeamMeeting extends Pms_Form {

		public $allusers;
		
		private $triggerformid = 0; //use 0 if you want not to trigger
		
		private $triggerformname = "frmTeamMeeting";  //define the name if you want to piggyback some triggers
		
		public function validate($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$Tr = new Zend_View_Helper_Translate();
			$error = '0';
			$val = new Pms_Validation();

// 			if(!$val->isstring($post['meeting_name']))
// 			{
// 				$this->error_message['meeting_name'] = $Tr->translate('meeting_fill_name');
// 				$error = '1';
// 			}
			if(!$val->isstring($post['from_time']))
			{
				$this->error_message['time'] = $Tr->translate('meeting_fill_start_time');
				$error = '2';
			}

			if(!$val->isstring($post['till_time']))
			{
				$this->error_message['time'] = $Tr->translate('meeting_fill_end_time');
				$error = '3';
			}

			$start_date_time = strtotime(date('Y-m-d', time()) . ' ' . $post['from_time'] . ':00');
			$end_date_time = strtotime(date('Y-m-d', time()) . ' ' . $post['till_time'] . ':00');

			if($start_date_time > $end_date_time)
			{
				$this->error_message['time'] = $Tr->translate('meeting_start_higher_than_end');
				$error = '4';
			}

			if(!$val->isstring($post['date']))
			{
				$this->error_message['date'] = $Tr->translate('meeting_fill_date');
				$error = '5';
			}

			if($error == 0)
			{
				return true;
			}
			else
			{

				return false;
			}
		}
		
		//validate and correct submitted data
		public function _validate($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$Tr = new Zend_View_Helper_Translate();
			$error = '0';
			$val = new Pms_Validation();
			$now = time();
			$both = false;
			
			if(empty($post['date']))
			{
				$post['date'] = date('d.m.Y', $now);
			}
			
			if(!empty($post['from_time']))
			{
				//setup timestamp
				$post['from_time_ts'] = strtotime(date('Y-m-d', $now) . " " . $post['from_time'].":00");
			}
			
			if(!empty($post['till_time']))
			{
				//setup timestamp
				$post['till_time_ts'] = strtotime(date('Y-m-d', $now) . " " . $post['till_time'].":00");
			}
			
			
			if(!$val->isstring($post['from_time']) && !$val->isstring($post['till_time']))
			{
				$post['from_time'] = date('H:i', $now);
				$post['till_time'] = date('H:i', strtotime('+1 hour', $now));
				
				$post['meeting_details']['from_time'] = date('Y-m-d H:i:s', $now);
				$post['meeting_details']['till_time'] = date('Y-m-d H:i:s', strtotime('+1 hour', $now));
				$both = true;
			}
			
			if(!$val->isstring($post['from_time']) && !$both)
			{
				$post['from_time'] = date('H:i', $now);
				$post['meeting_details']['from_time'] = date('Y-m-d H:i:s', $now);
			}

			if(!$val->isstring($post['till_time']) && !$both)
			{
				$post['till_time'] = date('H:i', strtotime('+1 hour', $post['from_time_ts']));
				$post['meeting_details']['till_time'] = date('Y-m-d H:i:s', strtotime('+1 hour', $post['from_time_ts']));
				
				$post['till_time_ts'] = strtotime($post['till_time']);
			}

			$start_date_time = strtotime(date('Y-m-d', $now) . ' ' . $post['from_time'] . ':00');
			$end_date_time = strtotime(date('Y-m-d', $now) . ' ' . $post['till_time'] . ':00');

			if($start_date_time > $end_date_time)
			{
				$post['from_time'] = date('H:i', $end_date_time);
				$post['till_time'] = date('H:i', $start_date_time);
				
				$post['meeting_details']['from_time'] = date('Y-m-d H:i:s', $end_date_time);
				$post['meeting_details']['till_time'] = date('Y-m-d H:i:s', $start_date_time);
			}

			if(!$val->isstring($post['date']))
			{
				$post['date'] = date('d.m.Y', $now);
			}
	
			return $post;
		}

		public function insert_meeting_data($post)
		{
// 			print_r($post); exit;
			//99a61ca48e72795c0f3e21bfcd424f56d2bf729a
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$first_time_completed = false;
			//meeting date formated db style Y-m-d H:i:s
			$meeting_date_fdb = date('Y-m-d H:i:s', strtotime($post['date']));

			//meeting date formated Y-m-d
			$meeting_date_f = date('Y-m-d', strtotime($post['date']));

			$start_time = $meeting_date_f . ' ' . $post['from_time'] . ':00';
			$end_time = $meeting_date_f . ' ' . $post['till_time'] . ':00';

			$selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();
			
			if(empty($post['mid']) || $post['mid'] == '0')
			{
				//save basic meeting data
				$ins = new TeamMeeting();
				$ins->client = $clientid;
				$ins->meeting_name = $post['meeting_name'];
				$ins->casestatus = isset($post['case']) ? $post['case'] : '';//Maria:: Migration CISPC to ISPC 22.07.2020
				$ins->date = $meeting_date_fdb;
				$ins->from_time = date('Y-m-d H:i:s', strtotime($start_time));
				$ins->till_time = date('Y-m-d H:i:s', strtotime($end_time));
				$ins->organizational = trim(rtrim($post['organizational']));
				$ins->patients_location = $post['patients_location'];
				if($post['completed'])
				{
					$ins->completed = "1";
					$ins->first_time = "1";
					$first_time_completed = true;
				}
				else
				{
					$ins->completed = "0";
					$first_time_completed = false;
				}
				$ins->save();

				$meeting_id = $ins->id;
			}
			elseif($post['mid'] > '0')
			{
				//update meeting master data on edit
				$update = Doctrine::getTable('TeamMeeting')->findOneByIdAndClient($post['mid'], $clientid);
				if($update)
				{
					$found_meeting = $update->toArray();

					if($found_meeting['completed'] == "0" && $found_meeting['first_time'] == "0" && $post['completed'] == '1')
					{
						$first_time_completed = true;
					}
					else
					{
						$first_time_completed = false;
					}

					$update->date = $meeting_date_fdb;
					$update->meeting_name = $post['meeting_name'];
					$update->from_time = date('Y-m-d H:i:s', strtotime($start_time));
					$update->till_time = date('Y-m-d H:i:s', strtotime($end_time));
					$update->organizational = trim(rtrim($post['organizational']));
					$update->patients_location = $post['patients_location'];
					if($post['completed'])
					{
						$update->completed = "1";
						$update->first_time = "1";
					}
					else
					{
						$update->completed = "0";
					}
					$update->save();
				}

				$meeting_id = $post['mid'];
			}



			//get patients ipids
// 			$ipids = Pms_CommonData::get_ipids($post['meeting']['patient'], true);
			$ipids = Pms_CommonData::get_ipids_from_epids($post['meeting']['patient'], true);
			$pipids = array_values($ipids);

			//get patients details
			$patientmaster = new PatientMaster();
			$allpatientsinfo = $patientmaster->get_multiple_patients_details_dta($pipids);

			//clear meeting details and assigned users on edit
			if($post['mid'] > '0')
			{
				$ipids_arr = array_values(array_unique($ipids));

				$clear_patient_details = $this->clear_meeting_details($clientid, $meeting_id, $ipids_arr);
			}

			
			
			//clear meeting patients and assigned users on edit
			if($post['mid'] > '0')
			{
				$clear_meeting_patients = $this->clear_meeting_patients($clientid, $meeting_id);
			}
	
			if(!empty($post['team_patients'])){

				foreach($post['team_patients'] as $p_type => $pat_ipids){
					
					$extra = "0";
					foreach( $pat_ipids as $kp => $v_ipid){
						if($p_type == "extra"){
							$extra = "1";
						} else{
							$extra = "0";
						}
					
						$meeting_patients_data[]= array(
								'client' => $clientid,
								'meeting' => $meeting_id,
								'patient' => $v_ipid,
								'extra' => $extra,
								'isdelete' => "0"
						);
					}
				}
				
				
				if($meeting_patients_data)
				{
					$collection_u = new Doctrine_Collection('TeamMeetingPatients');
					$collection_u->fromArray($meeting_patients_data);
					$collection_u->save();
				}
				
			}
			
			

			//ISPC-1228 TODO AND XT entries for patients with data START
			if($post['completed'] == '1')
			{
				$meeting_details['date'] = $meeting_date_f; //Y-m-d format
				$meeting_details['start_time'] = $post['from_time']; //H:i format
				$meeting_details['end_time'] = $post['till_time']; //H:i format
				$meeting_details['patients_no'] = count($post['meeting']['patient']);
				$xt_duration = Pms_CommonData::calculate_meeting_xt_duration($meeting_details);
				
				//construct verlauf entries array for all patients, xt entry is added with loggedin user
				$row_problem = array();
				foreach($post['meeting']['verlauf'] as $k_patient_rv => $v_patient_rv)
				{
					$patient_problem_values = array();
					foreach($v_patient_rv as $k_pat_row => $v_pat_value)
					{
						if($v_pat_value == "1")
						{
						    if(strlen($post['meeting']['problem'][$k_patient_rv][$k_pat_row]) > 0 )
						    {
    							$patient_problem_values[$k_patient_rv][] = $post['meeting']['problem'][$k_patient_rv][$k_pat_row];
						    }
						    
						    if(strlen($post['meeting']['todo'][$k_patient_rv][$k_pat_row]) > 0 )
						    {
    	   						$patient_todo_values[$k_patient_rv][] = $post['meeting']['todo'][$k_patient_rv][$k_pat_row];
						    }
						    //ISPC-2556 Lore 26.06.2020
						    if(strlen($post['meeting']['targets'][$k_patient_rv][$k_pat_row]) > 0 )
						    {
						        $patient_targets_values[$k_patient_rv][] = $post['meeting']['targets'][$k_patient_rv][$k_pat_row];
						    }
						}
					}

					
					$xt_data[$k_patient_rv] = "Teambesprechung: ";
					$xt_data[$k_patient_rv] .= implode('; ', $patient_problem_values[$k_patient_rv]);

					if(!empty($patient_problem_values[$k_patient_rv]))
					{
    					$xt_data[$k_patient_rv] .= "; ";
					}
					
					if(!empty($patient_todo_values[$k_patient_rv])){
    					$xt_data[$k_patient_rv] .= implode('; ', $patient_todo_values[$k_patient_rv]);
					}
					//ISPC-2556 Lore 26.06.2020
					if(!empty($patient_targets_values[$k_patient_rv])){
					    $xt_data[$k_patient_rv] .= implode('; ', $patient_targets_values[$k_patient_rv]);
					}
					
					$row_problem['ipid'] = $ipids[$k_patient_rv];
					$row_problem['user_id'] = $userid;
					$row_problem['course_date'] = date('Y-m-d H:i:s', time());
// 					$row_problem['done_date'] = date('Y-m-d H:i:s', time());
					$row_problem['done_date'] = date('Y-m-d H:i:s', strtotime($post['meeting_details']['from_time']));
					$row_problem['course_type'] = "XT";
					$row_problem['date'] = $post['meeting_details']['from_time'];
					//course_title should be in following format "23 | problem bla bla | date"
					//todo add xt duration time based on meeting duration / number of patients (if low than 10 => default 10?)
// 					$row_problem['course_title'] = $xt_duration . ' | ' . implode(';', $patient_problem_values[$k_patient_rv]) . ' | ' . date('d.m.Y H:i', strtotime($post['meeting_details']['from_time']));
					$row_problem['course_title'] = $xt_duration . ' | ' . $xt_data[$k_patient_rv] . ' | ' . date('d.m.Y H:i', strtotime($post['meeting_details']['from_time']));
					$row_problem['tabname'] = "teammeeting_completed";
					$row_problem['recordid'] = $meeting_id;

					$problem_data[$ipids[$k_patient_rv]] = $row_problem;
				}

				//construct todos array for all assigned users for all patients which have todo field completed
				$todo_date = date('Y-m-d', strtotime('+1 day', time())) . ' 23:59:59';
				foreach($post['meeting']['send_todo'] as $k_patient_r => $v_patient_r)
				{
					foreach($v_patient_r as $k_pat_r => $v_pat_r)
					{
						if($v_pat_r == '1')
						{
// 						    $users_td[$k_patient_r][$k_pat_r] = implode(",",$post['meeting']['assigned_users'][$k_patient_r][$k_pat_r]);
						    
						    foreach($post['meeting']['assigned_users'][$k_patient_r][$k_pat_r] as $k_usr => $v_usr)
						    {
// 						        $users_td[$k_patient_r][$k_pat_r][] = 'u'.$v_usr; //ISPC-1912 removed the u. and is added in html
						        $users_td[$k_patient_r][$k_pat_r][] = $v_usr;
						        $users_td_ids[$k_patient_r][$k_pat_r][] = $v_usr;
						    }
						    //ISPC-1912 removed the foreach
// 							foreach($post['meeting']['assigned_users'][$k_patient_r][$k_pat_r] as $k_usr => $v_usr)
							{
								//add todo only if the field is not empty
								if(strlen(trim(rtrim($post['meeting']['todo'][$k_patient_r][$k_pat_r]))) > '0' && !empty($post['meeting']['todo'][$k_patient_r][$k_pat_r]))
								{
									$todo_content = '<b> Thema:</b> ' . $post['meeting']['problem'][$k_patient_r][$k_pat_r] . '<br /> <b> Maßnahme: </b>' . $post['meeting']['todo'][$k_patient_r][$k_pat_r] . '';

									$row_todo_data['client_id'] = $clientid;
									$row_todo_data['user_id'] = $v_usr;
									$row_todo_data['group_id'] = "0";
									$row_todo_data['ipid'] = $ipids[$k_patient_r];
									$row_todo_data['todo'] = $todo_content;
									$row_todo_data['triggered_by'] = "teammeeting_completed";
									$row_todo_data['isdelete'] = "0";
									$row_todo_data['iscompleted'] = "0";
									$row_todo_data['record_id'] = $meeting_id;
									$row_todo_data['until_date'] = $todo_date;

								 
									// data for patient course
								    $row_todo_data['content'] = '';
								    $row_todo_data['content'] .= 'Teambesprechung: ';
								    if(strlen($post['meeting']['problem'][$k_patient_r][$k_pat_r]) > '0')
								    {
								        $row_todo_data['content'] .= '<b> Thema:</b> ' . $post['meeting']['problem'][$k_patient_r][$k_pat_r];
								    }
								    $row_todo_data['content'] .= '<br /> <b> Maßnahme: </b>' .  $post['meeting']['todo'][$k_patient_r][$k_pat_r];
								    
								    $row_todo_data['content'] .= '<br /> <b> Ziele: </b>' .  $post['meeting']['targets'][$k_patient_r][$k_pat_r];  //ISPC-2556 Lore 26.06.2020
								    
								    $row_todo_data['course_title'] = $row_todo_data['content'].' |---------| '.implode(',',$users_td[$k_patient_r][$k_pat_r]).' |---------| ';
								    $row_todo_data['course_title'] .= date('d.m.Y', strtotime('+1 day', time())).' |---------| '.'0';
								    $row_todo_data['additional_info'] = implode(';',$users_td[$k_patient_r][$k_pat_r]);
								    $row_todo_data['assignedusrids'] = $users_td_ids[$k_patient_r][$k_pat_r];

								    $row_todo_data['ident'] = $k_patient_r.$k_pat_r.implode(';',$users_td[$k_patient_r][$k_pat_r]);
								    $row_todo_data['messages_ident'] = $meeting_id.'_'.$k_patient_r.'_'.$k_pat_r.'_'.implode('',$users_td[$k_patient_r][$k_pat_r]);
									
								    
									$todo_data[] = $row_todo_data;
								}
							}
						}
					}
				}
			}
			//ISPC-1228 TODO AND XT entries for patients with data END
			

			//save advanced meeting data
			foreach($post['meeting']['patient'] as $k_patient => $v_patient)
			{
				//insert XT in verlauf if is first time completed
				if($first_time_completed === true)
				{
				    if(!empty($problem_data[$ipids[$v_patient]]))
				    {
    					$v_row_data = $problem_data[$ipids[$v_patient]];
    
    					$insert_new_course = new PatientCourse();
    					$insert_new_course->ipid = $v_row_data['ipid'];
    					$insert_new_course->user_id = $v_row_data['user_id'];
    					$insert_new_course->course_date = $v_row_data['course_date'];
    					$insert_new_course->done_date = $v_row_data['done_date'];
    					$insert_new_course->course_type = Pms_CommonData::aesEncrypt($v_row_data['course_type']);
    					$insert_new_course->course_title = Pms_CommonData::aesEncrypt($v_row_data['course_title']);
    					$insert_new_course->tabname = Pms_CommonData::aesEncrypt($v_row_data['tabname']);
    					$insert_new_course->recordid = $v_row_data['recordid'];
    					$insert_new_course->save();
				    }
				}

				//adding problems to verlauf
				foreach($post['meeting']['problem'][$v_patient] as $k_problem_row => $v_problem)
				{
					if(strlen($v_problem) > '0' || strlen($post['meeting']['todo'][$v_patient][$k_problem_row]) || count($post['meeting']['assigned_users'][$v_patient][$k_problem_row]) > 0)
					{
						$insert_new_det_row = new TeamMeetingDetails();
						$insert_new_det_row->client = $clientid;
						$insert_new_det_row->meeting = $meeting_id;
						$insert_new_det_row->patient = $ipids[$v_patient];
						$insert_new_det_row->row = $k_problem_row;
						$insert_new_det_row->send_todo = $post['meeting']['send_todo'][$v_patient][$k_problem_row];
						$insert_new_det_row->verlauf = $post['meeting']['verlauf'][$v_patient][$k_problem_row];
						$insert_new_det_row->problem = $v_problem;
						$insert_new_det_row->targets = $post['meeting']['targets'][$v_patient][$k_problem_row]; //ISPC-2556 Andrei 27.05.2020
						$insert_new_det_row->todo = $post['meeting']['todo'][$v_patient][$k_problem_row];
						$insert_new_det_row->status = $post['meeting']['status'][$v_patient][$k_problem_row];
						//ISPC-2896 Lore 20.04.2021
						$insert_new_det_row->current_situation  = $post['meeting']['current_situation'][$v_patient][$k_problem_row]; 
						$insert_new_det_row->hypothesis_problem = $post['meeting']['hypothesis_problem'][$v_patient][$k_problem_row];
						$insert_new_det_row->measures_problem   = $post['meeting']['measures_problem'][$v_patient][$k_problem_row];
						//.
						
						$insert_new_det_row->isdelete = "0";
						$insert_new_det_row->save();
						$inserted_row_id = $insert_new_det_row->id;

						$patient_verlauf[$ipids[$v_patient]]['problem'][$k_problem_row] = $v_problem;
						$patient_verlauf[$ipids[$v_patient]]['todo'][$k_problem_row] = $post['meeting']['todo'][$v_patient][$k_problem_row];
						//ISPC-2556 Lore 26.06.2020
						$patient_verlauf[$ipids[$v_patient]]['targets'][$k_problem_row] = $post['meeting']['targets'][$v_patient][$k_problem_row];
						
					}
					//ISPC-2896 Lore 23.04.2021
					//need insert empty problem if other problems are !empty
					if( (empty($v_problem) || strlen($v_problem) == '0' ) && 
					    strlen($post['meeting']['todo'][$v_patient][$k_problem_row]) == '0' &&
					    ( strlen($post['meeting']['current_situation'][$v_patient][$k_problem_row]) >'0' ||
					      strlen($post['meeting']['hypothesis_problem'][$v_patient][$k_problem_row]) >'0' ||
					      strlen($post['meeting']['measures_problem'][$v_patient][$k_problem_row]) >'0' ||
					      strlen($post['meeting']['targets'][$v_patient][$k_problem_row]) >'0' ) )
                     {
					    
					    $insert_new_det_row = new TeamMeetingDetails();
					    $insert_new_det_row->client = $clientid;
					    $insert_new_det_row->meeting = $meeting_id;
					    $insert_new_det_row->patient = $ipids[$v_patient];
					    $insert_new_det_row->row = $k_problem_row;
					    $insert_new_det_row->send_todo = $post['meeting']['send_todo'][$v_patient][$k_problem_row];
					    $insert_new_det_row->verlauf = $post['meeting']['verlauf'][$v_patient][$k_problem_row];
					    $insert_new_det_row->problem = $v_problem;
					    $insert_new_det_row->targets = $post['meeting']['targets'][$v_patient][$k_problem_row]; //ISPC-2556 Andrei 27.05.2020
					    $insert_new_det_row->todo = $post['meeting']['todo'][$v_patient][$k_problem_row];
					    $insert_new_det_row->status = $post['meeting']['status'][$v_patient][$k_problem_row];
					    $insert_new_det_row->current_situation  = $post['meeting']['current_situation'][$v_patient][$k_problem_row];
					    $insert_new_det_row->hypothesis_problem = $post['meeting']['hypothesis_problem'][$v_patient][$k_problem_row];
					    $insert_new_det_row->measures_problem   = $post['meeting']['measures_problem'][$v_patient][$k_problem_row];					    
					    $insert_new_det_row->isdelete = "0";
					    $insert_new_det_row->save();
					    $inserted_row_id = $insert_new_det_row->id;
					    
					}
					//insert each row assigned users

					$curent_row_users = array_unique(array_values($post['meeting']['assigned_users'][$v_patient][$k_problem_row]));
//					foreach($post['meeting']['assigned_users'][$v_patient][$k_problem_row] as $k_usr => $v_usr_id)
					foreach($curent_row_users as $k_usr => $v_usr_id)
					{
						$user_type = "user";
						if(strpos($v_usr_id, $selectbox_separator_string["group"], 0) === 0)
						{
							$v_usr_id = substr($v_usr_id, strlen($selectbox_separator_string["group"]));
							$user_type = "group";
						}
						elseif(strpos($v_usr_id, $selectbox_separator_string["user"], 0) === 0)
						{
							$v_usr_id = substr($v_usr_id, strlen($selectbox_separator_string["user"]));
							$user_type = "user";
						}
						elseif(strpos($v_usr_id, $selectbox_separator_string["pseudogroup"], 0) === 0)
						{
							$v_usr_id = substr($v_usr_id, strlen($selectbox_separator_string["pseudogroup"]));
							$user_type = "pseudogroup";
						}
						
						$mutiple_assigned_users_data[] = array(
							'client' => $clientid,
							'patient' => $ipids[$v_patient],
							'meeting' => $meeting_id,
						    '`row`' => $k_problem_row,//MySQL 8 Ancuta 16.06.2021
							'user' => $v_usr_id,
							'user_type' => $user_type,	
							'isdelete' => '0'
						);

						//goes to  patient verlauf
						$patient_verlaufusr[$ipids[$v_patient]][$k_problem_row][] = $this->allusers[$v_usr_id];
					}

					$patient_verlauf[$ipids[$v_patient]]['users'][$k_problem_row] = implode("; ", $patient_verlaufusr[$ipids[$v_patient]][$k_problem_row]);
				}
			}

//			print_r("todo_data\n");
//			print_r($todo_data);
//			print_r("problem_data\n");
//			print_r($problem_data);
			//insert patient verlauf entries
			

			$client_details_array = Client::getClientDataByid($logininfo->clientid);
			$Tr = new Zend_View_Helper_Translate();
				
			//get client users
			$usrs = new User();
			$postids = array();
			$post_users = $usrs->get_client_users($clientid);
			foreach($post_users as $userdata) {
			    $usersdata[$userdata['id']] = $userdata;
			    $postids[] = $userdata['id'];
			}
				
			//notification settings for client users
			$notification = new Notifications();
			$notif_settings_users = $notification->get_notification_settings($postids);
				

			
			
			// get assigned users
// 			$qpas = new PatientQpaMapping(); //ISPC-1912 moved this
// 			$patientqpa = $qpas->getAssignedUsers($pipids);//ISPC-1912 moved this
			
			
			
			// get todo_messages
			
			if(!empty($postids)){
    			$mess_q = Doctrine_Query::create()
    			->select("*")
    			->from("Messages")
    			->whereIn("recipient", $postids)
    			->andWhere("clientid = '" . $clientid . "'")
    			->andWhere("source LIKE  'teammeeting%' ");
    			$messarray = $mess_q->fetchArray();
    			
    			foreach($messarray as $k=>$messages) {
    			    if(!empty($messages['source'])){
//         			    $messages_array[$messages['recipient']][] = str_replace("teammeeting_","",$messages['source']); 
        			    $messages_array[$messages['recipient']][] = $messages['source']; 
    			    }
    			}
			}
			
			if($post['completed'] == '1')
			{
				if(!empty($todo_data))
				{
					if($meeting_id)
					{
						$clear_todos = self::clear_todos($clientid, $meeting_id);
						$clear_verlauf_todos = self::clear_verlauf_todos($meeting_id);
						
					}

					//$collection_u = new Doctrine_Collection('ToDos');
					//$collection_u->fromArray($todo_data);
					//$collection_u->save();
				
					$postes_w = array();
					$sent_messages = array();
					
					foreach($todo_data as $kt=>$v_row_data){
					
					    if(!in_array($v_row_data['ident'],$postes_w)){
					         
					        $postes_w[] = $v_row_data['ident'];
					        	
					        $insert_new_todo_course = new PatientCourse();
					        
					        $insert_new_todo_course->triggerformid = $this->triggerformid; //bypass the PatientCourse trigger
					        $insert_new_todo_course->triggerformname = $this->triggerformname; //bypass the PatientCourse trigger
					        
					        $insert_new_todo_course->ipid = $v_row_data['ipid'];
					        $insert_new_todo_course->user_id = $userid;
					        $insert_new_todo_course->course_date = date('Y-m-d H:i:s', time());;
					        $insert_new_todo_course->done_date = date('Y-m-d H:i:s', strtotime($post['meeting_details']['from_time']));
					        $insert_new_todo_course->course_type = Pms_CommonData::aesEncrypt("W");
					        $insert_new_todo_course->course_title = Pms_CommonData::aesEncrypt($v_row_data['course_title']);
					        $insert_new_todo_course->tabname = Pms_CommonData::aesEncrypt("teammeeting");
					        $insert_new_todo_course->recordid = $meeting_id;
					        $insert_new_todo_course->save();
					        $course_id = $insert_new_todo_course->id;
					        
					        $event = new Doctrine_Event($insert_new_todo_course, Doctrine_Event::RECORD_SAVE);
					        
					     	//piggyback	ToDos
							$gpost = array(
									"todo_text"		=> $v_row_data['todo'],
									"todo_date"		=> $v_row_data['until_date'],
									"todo_users"	=> $v_row_data['assignedusrids'],
							);
							$inputs = array();
					        $trigger_ToDos_obj = new application_Triggers_addValuetoToDos();
					        $trigger_ToDos_obj->triggerAddValuetoTodos($event, $inputs, $this->triggerformname, $this->triggerformid, 2, $gpost);
					        $todos_ids = $trigger_ToDos_obj->get_last_insert_ids();
					        
					        
					        //piggyback	addInternalMessage
					        $gpost = array(
					        		"title" => "Ein neues TODO",
					        		"verlauf_entry"		=> "Ein neues TODO" ."\n\n". $v_row_data['todo'],
					        		"users_and_groups_2_send"	=> $v_row_data['assignedusrids'],
					        );
					        $inputs = array();
					        $trigger_addInternalMessage_obj = new application_Triggers_addInternalMessage();
					        $trigger_addInternalMessage_obj->triggeraddInternalMessage($event, $inputs, $this->triggerformname, $this->triggerformid, 2, $gpost);
					        			        
					        
					        //piggyback	sendMail
					        $email_text = $Tr->translate('youhavenewmailinyourispcinbox');
					        //$email_text .= "\n\n Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>";			        	
					        // ISPC-2475 @Lore 31.10.2019
					        $email_text .= $Tr->translate('system_wide_email_text_login');
					        
					        if( ! empty($client_details_array[0]))
					        {
					        	$client_details = $client_details_array[0];
					        	$email_text .= "\n";
					        	$email_text .= "\n ".$client_details['team_name'];
					        	$email_text .= "\n ".$client_details['street1'];
					        	$email_text .= "\n ".$client_details['postcode']." ".$client_details['city'];
					        	$email_text .= "\n ".$client_details['emailid'];
					        	$client_details_string = "\n";
					        	$client_details_string .= "\n ".$client_details['team_name'];
					        	$client_details_string .= "\n ".$client_details['street1'];
					        	$client_details_string .= "\n ".$client_details['postcode']." ".$client_details['city'];
					        	$client_details_string .= "\n ".$client_details['emailid'];
					        }
					        
					        //TODO-3164 Ancuta 08.09.2020
					        $email_data = array();
					        $email_data['client_info'] = $client_details_string;
					        $email_text = "";//overwrite
					        $email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
					        //-- 
					        
					        $gpost = array(
					        		"title" => "Ein neues TODO",
					        		"verlauf_entry"	=> "", // this can also be used as message, to be added on top
					        		"users_and_groups_2_send"	=> $v_row_data['assignedusrids'],
					        );
					        $inputs = array(
					        		"fromaddress" => "",
					        		"subject" => $Tr->translate('youhavenewmailinispc').' - '.date('d.m.Y H:i'),
					        		"message" => $email_text,
					        );
					        $trigger_sendMail_obj = new application_Triggers_sendMail();
					        $trigger_sendMail_obj->triggersendMail($event, $inputs, $this->triggerformname, $this->triggerformid, 2, $gpost);
					        
					    }
					     
					    continue;
					   
					    /*
					    $ins_todo = new ToDos();
					    $ins_todo->client_id = $clientid;
					    $ins_todo->user_id = $v_row_data['user_id'];
					    $ins_todo->group_id = 0;
					    $ins_todo->ipid =  $v_row_data['ipid'];;
					    $ins_todo->todo = $v_row_data['todo'];
					    $ins_todo->create_date = date("Y-m-d H:i:s");
					    $ins_todo->until_date = date("Y-m-d H:i:s", strtotime($v_row_data['until_date']));
					    $ins_todo->course_id = $course_id;
				        $ins_todo->record_id = $v_row_data['record_id'];
				        $ins_todo->triggered_by = $v_row_data['triggered_by'];
					    $ins_todo->additional_info = $v_row_data['additional_info'];
					    $ins_todo->save();
					
					    $assignedusrids = $v_row_data['assignedusrids'];
					    
					  
					    
					    foreach($assignedusrids as $k=>$assuserid)
					    {
					        $mess = false;
					        if($notif_settings_users[$assuserid]['todo'] != 'none')
					        {
					            if($notif_settings_users[$assuserid]['todo'] == 'assigned')
					            {
					                if(in_array($assuserid, $patientqpa['assignments'][$keyipid]))
					                {
					                    $mess = true;
					                }
					            }
					            if($notif_settings_users[$assuserid]['todo'] == 'all')
					            {
					                $mess = true;
					            }
					        }
					         
					        $message_identification = 'teammeeting_'.$v_row_data['messages_ident'];

					        
					        if($mess && !in_array($message_identification,$messages_array[$assuserid]) && !in_array($message_identification,$sent_messages))
					        {
					                
					            $sent_messages[] = $message_identification;
					            
					            $patname = $allpatientsinfo[$v_row_data['ipid']]['first_name'].', '.$allpatientsinfo[$v_row_data['ipid']]['last_name'];
					            $pataddress = $allpatientsinfo[$v_row_data['ipid']]['street1'].', '.$allpatientsinfo[$v_row_data['ipid']]['zip'].' '.$allpatientsinfo[$v_row_data['ipid']]['city'];
					            $patid = Pms_Uuid::encrypt($allpatientsinfo[$v_row_data['ipid']]['id']);
					            
					            
					
					            $message_entry  ="";
					            $message_entry .= " Ein neues TODO\n";
					            $message_entry .= "\n" ;
					            $message_entry .= '<a href="'.APP_BASE.'patientcourse/patientcourse?id='.$patid.'">' . $patname.'</a>' ;
					            $message_entry .= "\n" . $pataddress;
					             
					
					            $mail = new Messages();
					            $mail->sender = $logininfo->userid;
					            $mail->clientid = $logininfo->clientid;
					            $mail->recipient = $assuserid;
					            
					            $mail->msg_date = date("Y-m-d H:i:s", time());
					            $mail->title = Pms_CommonData::aesEncrypt('Ein neues TODO');
					            $mail->content = Pms_CommonData::aesEncrypt($message_entry);
					            $mail->create_date = date("Y-m-d", time());
					            $mail->create_user = $logininfo->userid;
					            $mail->source = $message_identification;
					            $mail->read_msg = '0';
					            $mail->save();
					
					            // ###################################
					            // ISPC-1600
					            // ###################################
					            $email_subject = $Tr->translate('youhavenewmailinispc').' - '.$the_sender.', '.date('d.m.Y H:i');
					            $email_text = "";
					            $email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
					            // link to ISPC
					            $email_text .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>";
					            // client details
					
					            if(!empty($client_details_array))
					            {
					                $client_details = $client_details_array[0];
					            }
					            $client_details_string = "<br/>";
					            $client_details_string  .= "<br/> ".$client_details['team_name'];
					            $client_details_string  .= "<br/> ".$client_details['street1'];
					            $client_details_string  .= "<br/> ".$client_details['postcode']." ".$client_details['city'];
					            $client_details_string  .= "<br/> ".$client_details['emailid'];
					            $email_text .= $client_details_string;
					
					            if ($mail->id > 0)
					            {
					                if (!empty($this->allusers[$assuserid]['emailid']))
					                {
					                    $mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
					                    $mail = new Zend_Mail('UTF-8');
					                    $mail->setBodyHtml($email_text)
					                    ->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
					                    ->addTo($this->allusers[$assuserid]['emailid'], $this->allusers[$assuserid]['last_name'] . ' ' . $this->allusers[$assuserid]['first_name'])
					                    ->setSubject($email_subject)
					                    ->setIpids($keyipid)
					                    ->send($mail_transport);
					                }
					            }
					        }
					    }
					    
					    */
					}
				}
					
				foreach($ipids as $k_patient => $v_ipid)
				{
					foreach($patient_verlauf[$v_ipid]['problem'] as $k_row => $v_course_data)
					{
					    if(!empty($v_course_data)){
					        
    						$course_data = '';
    						$course_data .= 'Teambesprechung: ';
    						if(strlen($v_course_data) > '0')
    						{
    							$course_data .= '  Thema: ' . $v_course_data;
    						}
    
    						if(strlen($patient_verlauf[$v_ipid]['todo'][$k_row]) > '0')
    						{
    							$course_data .= '  Maßnahme: ' . $patient_verlauf[$v_ipid]['todo'][$k_row];
    						}
    						//ISPC-2556 Lore 26.06.2020
    						if(strlen($patient_verlauf[$v_ipid]['targets'][$k_row]) > '0')
    						{
    						    $course_data .= '  Ziele: ' . $patient_verlauf[$v_ipid]['targets'][$k_row];
    						}
    						
    						if(strlen($patient_verlauf[$v_ipid]['users'][$k_row]) > '0')
    						{
    							$course_data .= '  Wer: ' . $patient_verlauf[$v_ipid]['users'][$k_row];
    						}
    						
    						
    						if($first_time_completed === true)
    						{
    							$nshortcut = "TD"; // ISPC-2382 09.05.2019 
    							
    							$cust = new PatientCourse();
    							$cust->ipid = $v_ipid;
    							$cust->course_date = date("Y-m-d H:i:s", time());
    							$cust->course_type = Pms_CommonData::aesEncrypt($nshortcut);
    							$cust->course_title = Pms_CommonData::aesEncrypt($course_data);
    							$cust->done_date = date("Y-m-d H:i:s", strtotime($start_time));
    							$cust->tabname = Pms_CommonData::aesEncrypt('teammeeting');
    							$cust->recordid = $meeting_id;
    							$cust->user_id = $userid;
    							$cust->save();
    							$course_data = '';
    						}
					    }
					}
				}
			}

			if($mutiple_assigned_users_data)
			{
				$collection_u = new Doctrine_Collection('TeamMeetingAssignedUsers');
				$collection_u->fromArray($mutiple_assigned_users_data);
				$collection_u->save();
			}

			//attending users
			foreach($post['attending_users'] as $k_usr => $v_usr)
			{
				$mutiple_attending_users_data[] = array(
					'client' => $clientid,
					'meeting' => $meeting_id,
					'user' => $v_usr
				);
			}

			if($mutiple_attending_users_data)
			{
				$collection_u = new Doctrine_Collection('TeamMeetingAttendingUsers');
				$collection_u->fromArray($mutiple_attending_users_data);
				$collection_u->save();
			}
//			exit;
			return $meeting_id;
		}

		public function clear_meeting_details($client, $meeting, $patients)
		{
			if($client && $meeting && $patients)
			{
				$patients_arr = array_values($patients);

				$q = Doctrine_Query::create()
					->update('TeamMeetingDetails')
					->set('isdelete', "1")
					->where('client = "' . $client . '"')
					->andWhere('meeting = "' . $meeting . '"')
					->andWhereIn("patient", $patients_arr)
					->andWhere('isdelete = "0"');
				$q->execute();

				$clear_assigned_users = $this->clear_assigned_users($client, $meeting);
				$clear_attending_users = $this->clear_attending_users($client, $meeting);
			}
		}

		public function clear_meeting_patients($client, $meeting)
		{
			if($client && $meeting)
			{
				$q = Doctrine_Query::create()
					->update('TeamMeetingPatients')
					->set('isdelete', "1")
					->where('client = "' . $client . '"')
					->andWhere('meeting = "' . $meeting . '"')
					->andWhere('isdelete = "0"');
				$q->execute();
			}
		}

		public function clear_assigned_users($client, $meeting)
		{
			$q = Doctrine_Query::create()
				->update('TeamMeetingAssignedUsers')
				->set('isdelete', "1")
				->where('client = "' . $client . '"')
				->andWhere('meeting = "' . $meeting . '"')
				->andWhere('isdelete = "0"');
			$q->execute();
		}

		public function clear_attending_users($client, $meeting)
		{
			if($client && $meeting)
			{
				$q = Doctrine_Query::create()
					->update('TeamMeetingAttendingUsers')
					->set('isdelete', "1")
					->where('client = "' . $client . '"')
					->andWhere('meeting = "' . $meeting . '"')
					->andWhere('isdelete = "0"');
				$q->execute();

				return true;
			}
			else
			{
				return false;
			}
		}

		public function clear_todos($client, $meeting = false)
		{
			if($client && $meeting)
			{
				$q = Doctrine_Query::create()
					->update('ToDos')
					->set('isdelete', "1")
					->set('change_date', "NOW()")
					->set('change_user', "?", $this->logininfo->userid)
					->where('client_id = ?', $client)
					->andWhere('record_id = ?', $meeting)
					->andWhere('isdelete = "0"')
					->execute();

				return true;
			}
			else
			{
				return false;
			}
		}

		

		public function clear_verlauf_todos($meeting = false)
		{
		    if($meeting)
		    {
		
		        $q = Doctrine_Query::create()
		        ->update('PatientCourse')
		        ->set('wrong', "1")
		        ->set('ishidden', "1")
		        ->set('change_date', "NOW()")
				->set('change_user', "?", $this->logininfo->userid)
		        ->where('recordid = ?', $meeting )
		        ->andWhere('tabname= ? ', addslashes(Pms_CommonData::aesEncrypt("teammeeting")))
		        ->andWhere('course_type= ?', addslashes(Pms_CommonData::aesEncrypt("W")))
		        ->execute();
		
		        return true;
		    }
		    else
		    {
		        return false;
		    }
		}
		

	}

?>