<?php

	Doctrine_Manager::getInstance()->bindComponent('ToDos', 'MDAT');

	class ToDos extends BaseToDos {

		public function getTodoById($id)
		{
			$todo = Doctrine_Query::create()
				->select("*")
				->from('ToDos')
				->where('id="' . $id . '"');
			$todoarray = $todo->fetchArray();
			if(!empty($todoarray))
			{
				return $todoarray;
			}
		}
		
		public function completeTodo($id)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$todo = Doctrine::getTable('ToDos')->find($id);
			if($todo)
			{
				$todo->iscompleted = '1';
				$todo->complete_user = $logininfo->userid;
				$todo->complete_date = date("Y-m-d H:i:s", time());
				$todo->save();
			}
		}
		
		// completeTodo and update verlauf entry
		/**
		 * 
		 * @param unknown $id
		 * @param unknown $event_comment
		 * New feature: TODO-3105 ANcuta - added option to send message and email to the creator user -  if module 228 activated 
		 */
		public function completeTodonew($id, $event_comment)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			$Tr = new Zend_View_Helper_Translate();
			$usr = new User();
			$previleges = new Modules();

		    //TODO-3105 Ancuta 28.04.2020
			$send_email2creator = $previleges->checkModulePrivileges("228", $logininfo->clientid);
			//

			
			
			$todo = Doctrine::getTable('ToDos')->find($id);
			if($todo)
			{
			    
			    //$usr = new User(); // moved at the top
			    $userdata = $usr->getUserDetails($logininfo->userid);
			    $username = $userdata[0]['first_name'].' '.$userdata[0]['last_name'];
			    
			    
			    //TODO-3105 Ancuta 28.04.2020
				if($send_email2creator){
				    
				    $se_todo_creator = $todo['create_user'];
				    $se_todo_ipid = $todo['ipid'];
				    $se_todo_client = $todo['client_id'];
				    
				    $todo_creator_details = array();
				    if(!empty($todo['create_user']) && $se_todo_creator != 0 ){
				        $todo_creator_details_array = $usr->getUserDetails($se_todo_creator);
				        if(!empty($todo_creator_details_array)){
				            $todo_creator_details = $todo_creator_details_array[0];
				        }
			    	}
			    	// get patient info 
			    	$se_patient_epid = Pms_CommonData::getEpid($se_todo_ipid);
			    	
			    	//TODO-3105 Ancuta 17.12.2020
			    	// get patient details
			    	$patient_details = PatientMaster::get_multiple_patients_details(array($se_todo_ipid));
			    	$patient_fl_name = $patient_details[$se_todo_ipid]['first_name'] . ' ' . $patient_details[$se_todo_ipid]['last_name'];
			    	// --
			    	
			    	$se_current_user = $username;
			    	
				}
			    //-- 
	
				//$previleges = new Modules();
				$modulepriv_todo = $previleges->checkModulePrivileges("142", $logininfo->clientid);
				
				if($modulepriv_todo)
				{					
					
					$todo_p_course_id = $todo['course_id'];
					$todoipid = $todo['ipid'];
					$todocrdate = $todo['create_date'];
					$todotext = $todo['todo'];
					$todouserid = $todo['user_id'];
					$todogroupid = $todo['group_id'];
					$todotill = date('d.m.Y', strtotime($todo['until_date']));
					$todo_tanbame = $todo['triggered_by'];
					
				
					$patcourse = new PatientCourse();
					$currper = array();
					$currper['start'] = $todocrdate;
					$currper['end'] = $todocrdate;
					$patshotw = $patcourse->get_patient_shortcuts_course($todoipid, array('W','Q'), $currper);	
						
					if(!empty($patshotw))
					{
						$shid = "";
						$outfor = 0;
						foreach ($patshotw as $shkey=>$shval)
						{
							if($shval['course_type'] == "W"){
								
								$sh_title = explode('|---------|', $shval['course_title']);
							
								if(substr($sh_title[0], 0, 9) == 'Eventuell')
								{
								continue;
								}
								else
								{
									$sh_title_test = preg_replace('/Teambesprechung: /', '', $sh_title[0]);
											
									if( (strlen($todo_p_course_id) >0 &&  $todo_p_course_id == $shval['id']) || trim($sh_title_test) == trim($todotext) && trim($sh_title[2]) == trim($todotill) && $todo_p_course_id == 0 )
									{
										$shid = $shval['id'];
										$outfor = 1;
										break;
									}
								}
								if($outfor == 1) {
									break;
								}
							} 					
						}
						
						if ($shid != "" and count($sh_title) == 4)
						{
							if(strlen($todo_p_course_id) >0 && $todo_p_course_id != "0")
							{
								$sh_title[] = $logininfo->userid;
							}
							else 
							{
								$sh_title[] = $logininfo->userid.", Erledigt von ".$username." am ".date('d.m.Y') . "\n" . $event_comment;
							}
							
							$shtitle = implode('|---------|', $sh_title);
							$upd = Doctrine::getTable('PatientCourse')->find($shid);						
							$upd->course_title = Pms_CommonData::aesEncrypt(addslashes($shtitle));
							$upd->save();
						}					
					}
					
					if( ! empty($todo_p_course_id) ){
						$patshotq = $patcourse->get_patient_shortcuts_course($todoipid, array('Q'),false,$todo_p_course_id);
						if( ! empty($patshotq)){
							foreach($patshotq as $q_key => $q_val){
								if($q_val['id'] == $todo_p_course_id && $q_val['tabname'] =="reciperequest"){

									$qtitle =  $q_val['course_title']."\n \n Wurde erledigt von  ".$username." am ".date('d.m.Y');
									$upd = Doctrine::getTable('PatientCourse')->find($q_val['id']);
									$upd->course_title = Pms_CommonData::aesEncrypt(addslashes($qtitle));
									$upd->save();
								}
							}
							
						}
					}
					
					
				}
				
				// get course_id and additional info
				$todo_course_id = $todo['course_id'];
				$todo_ipid = $todo['ipid'];
				$todo_additional_info = $todo['additional_info'];
				
				
				if(strlen($todo_course_id) >0 && $todo_course_id != "0")
				{
					$todo = Doctrine_Query::create()
					->select("id")
					->from('ToDos')
					->where('client_id= ?', $logininfo->clientid)
					->andWhere('ipid= ?', $todo_ipid)
					->andWhere('iscompleted = 0')
					->andWhere('isdelete = 0')
					->andWhere('course_id = ?', $todo_course_id)
					->andWhere('additional_info= ?', $todo['additional_info']);
					$todoarray = $todo->fetchArray();
					
					foreach($todoarray as $k=>$tid){
						$todo_individual = Doctrine::getTable('ToDos')->find($tid);
						if($todo_individual)
						{
							$todo_individual->iscompleted = '1';
							$todo_individual->complete_user = $logininfo->userid;
							$todo_individual->complete_date = date("Y-m-d H:i:s", time());
							$todo_individual->complete_comment = $event_comment;
							$todo_individual->save();
						}
					}
					
					
// 					if(!empty($todo_additional_info) && $todo_additional_info == "a")
// 					{
// 						// marck all todos with course id and additional info a
// 						// for a is added one todo per client group
// 						$todo = Doctrine_Query::create()
// 						->select("id")
// 						->from('ToDos')
// 						->where('client_id= ?', $logininfo->clientid)
// 						->andWhere('ipid= ?', $todo_ipid)
// 						->andWhere('isdelete = 0')
// 						->andWhere('course_id = ?', $todo_course_id)
// 						->andWhere('additional_info= ?', "a");
// 						$todoarray = $todo->fetchArray();
// 						print_R($todoarray);
// 						foreach($todoarray as $k=>$tid){
// 							$todo_ids[] = $tid;
							
// 						}
// 					}
// 					elseif(!empty($todo_additional_info) && $todo_additional_info != "a")
// 					{
// 						$additional_info_array = explode(";",$todo_additional_info);
// 						if(count($additional_info_array) > 1)
// 						{
// 							foreach($additional_info_array as $k=>$recipient)
// 							{
// 								$todos_types[substr($recipient, 0, 1)][] = substr($recipient, 1);
// 							}
							
// 							if(!empty($todos_types['g']))
// 							{
// 								// get all todos 
// 							}
							
							
// 						}
// 					}
						
				}
				else
				{
					// special casses
					// receipt print and receipt fax
					if(($todo['triggered_by'] == "newreceipt_1" || $todo['triggered_by'] == "newreceipt_2") && $todo->record_id != "0")
					{
						$todo_receipt_q = Doctrine_Query::create()
						->select("id")
						->from('ToDos')
						->where('client_id= ?', $logininfo->clientid)
						->andWhere('ipid= ?', $todo_ipid)
						->andWhere('isdelete = 0')
						->andWhere('iscompleted = 0')
						->andWhere('triggered_by = ?',$todo['triggered_by'])
						->andWhere('record_id = ?', $todo->record_id)
						->andWhere('additional_info= ?', $todo_additional_info);
						$todoarray_receipt = $todo_receipt_q->fetchArray();
						
						foreach($todoarray_receipt as $k=>$tid_r){
							$todo_receipt_individual = Doctrine::getTable('ToDos')->find($tid_r);
							if($todo_receipt_individual)
							{
								$todo_receipt_individual->iscompleted = '1';
								$todo_receipt_individual->complete_user = $logininfo->userid;
								$todo_receipt_individual->complete_date = date("Y-m-d H:i:s", time());
								$todo_receipt_individual->save();
							}
						}
						
					}
					elseif($todo['triggered_by'] == "sh_folgeko")
					{
						$todo_sh_q = Doctrine_Query::create()
						->select("id")
						->from('ToDos')
						->where('client_id= ?', $logininfo->clientid)
						->andWhere('ipid= ?', $todo_ipid)
						->andWhere('isdelete = 0')
						->andWhere('iscompleted = 0')
						->andWhere('triggered_by = ?',$todo['triggered_by'])
						->andWhere('DATE(create_date) = ?', date('Y-m-d',strtotime($todo->create_date)))
						->andWhere('additional_info= ?', $todo_additional_info);
						$todo_sh_arr = $todo_sh_q->fetchArray();
						
						foreach($todo_sh_arr as $k=>$tid_sh){
							$todo_sh_individual = Doctrine::getTable('ToDos')->find($tid_sh);
							if($todo_sh_individual)
							{
								$todo_sh_individual->iscompleted = '1';
								$todo_sh_individual->complete_user = $logininfo->userid;
								$todo_sh_individual->complete_date = date("Y-m-d H:i:s", time());
								$todo_sh_individual->save();
							}
						}
					}
					else
					{
						
						// it is done even the entry in patientcourse is not found
						$todo->iscompleted = '1';
						$todo->complete_user = $logininfo->userid;
						$todo->complete_date = date("Y-m-d H:i:s", time());
						$todo->complete_comment = $event_comment;
						$todo->save();
					}
				}

				
				//TODO-3105 Ancuta 28.05.2020
				if($send_email2creator && !empty($todo_creator_details)){
				    // get client details
				    $client = new Client();
				    $client_data = array();
				    $client_data = $client->findOneById($logininfo->clientid);

				    
				    if(!empty($client_data) && !empty($todo_creator_details['emailid'])){
    				
				        $email_subject = $Tr->translate('youhavenewmailinispc'). ' ' . date('d.m.Y H:i');
    				    $email_text = "";
    				    $email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
    				    $email_text .= $Tr->translate('system_wide_email_text_login');
    				        
    				    // client details
    				    $client_details_string = "<br/>";
    				    $client_details_string  .= "<br/> ".$client_data[$todo_creator_details['clientid']]['team_name'];
    				    $client_details_string  .= "<br/> ".$client_data[$todo_creator_details['clientid']]['street1'];
    				    $client_details_string  .= "<br/> ".$client_data[$todo_creator_details['clientid']]['postcode']." ".$client_data[$todo_creator_details['clientid']]['city'];
    				    $client_details_string  .= "<br/> ".$client_data[$todo_creator_details['clientid']]['emailid'];
    				    $email_text .= $client_details_string;
     
    
    				    //TODO-3164 Ancuta 08.09.2020
    				    $email_data = array();
    				    $email_data['client_info'] = $client_details_string;
    				    $email_text = "";//overwrite
    				    $email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
    				    //-- 
    				    
    				    //TODO-3105 Ancuta 17.12.2020
    				    //$title_str ="Ein TODO für Patient %epid von Ihnen wurde als erledigt markiert";
    				    //$title = str_replace('%epid', $se_patient_epid, $title_str );
    				    
    				    //$message_str ="Ein Todo von Ihnen für Patient %epid wurde vom Benutzer*in %username als erledigt markiert.";
    				    //$message = str_replace(array('%epid','%username'),array($se_patient_epid,$se_current_user), $message_str );

    				    $title_str ="Ein TODO für Patient %epid %patient_first_last_name von Ihnen wurde als erledigt markiert";
    				    $title = str_replace(array('%epid','%patient_first_last_name'),array($se_patient_epid,$patient_fl_name), $title_str );
    				    $message_str ="Ein Todo von Ihnen für Patient %epid %patient_first_last_name wurde vom Benutzer*in %username als erledigt markiert.";
    				    $message = str_replace(array('%epid','%patient_first_last_name','%username'),array($se_patient_epid,$patient_fl_name,$se_current_user), $message_str );
    				    // --
    				    
    				    //SEND MESSAGE
    				    $msg = new Messages();
    				    $msg->sender = $logininfo->userid;
    				    $msg->clientid = $se_todo_client;
    				    $msg->recipient = $se_todo_creator;
    				    $msg->ipid = $se_todo_ipid;
    				    $msg->msg_date = date("Y-m-d H:i:s", time());
    				    $msg->title = Pms_CommonData::aesEncrypt($title);
    				    $msg->content = Pms_CommonData::aesEncrypt($message);
    				    $msg->source = 'todo_completed_TODO-3105';
    				    $msg->create_date = date("Y-m-d", time());
    				    $msg->read_msg = '0';
    				    $msg->save();
    				    
    				    if($msg->id > 0)
    				    {
        			        $mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
        			        $mail = new Zend_Mail('UTF-8');
        			        $mail->setBodyHtml($email_text)
        			        ->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
        			        ->addTo($todo_creator_details['emailid'], $todo_creator_details['last_name'] . ' ' . $todo_creator_details['first_name'])
        			        ->setSubject($email_subject)
        			        ->send($mail_transport);
    				    }
				    }
				}
				
			}
		}
		
		
		// completeTodo and update verlauf entry
		public function completeTodonew_170405($id)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			$todo = Doctrine::getTable('ToDos')->find($id);
			if($todo)
			{
				$previleges = new Modules();
				$modulepriv_todo = $previleges->checkModulePrivileges("142", $logininfo->clientid);
				
				if($modulepriv_todo)
				{					
					$usr = new User();
					$userdata = $usr->getUserDetails($logininfo->userid);
						
					$username = $userdata[0]['first_name'].' '.$userdata[0]['last_name'];
					
					$todoipid = $todo['ipid'];
					$todocrdate = $todo['create_date'];
					$todotext = $todo['todo'];
					$todouserid = $todo['user_id'];
					$todogroupid = $todo['group_id'];
					$todotill = date('d.m.Y', strtotime($todo['until_date']));
					
				
					$patcourse = new PatientCourse();
					$currper = array();
					$currper['start'] = $todocrdate;
					$currper['end'] = $todocrdate;
					$patshotw = $patcourse->get_patient_shortcuts_course($todoipid, 'W', $currper);	
					
					if(!empty($patshotw))
					{
						$shid = "";
						$outfor = 0;
						foreach ($patshotw as $shkey=>$shval)
						{						
							$sh_title = explode('|---------|', $shval['course_title']);
						
							if(substr($sh_title[0], 0, 9) == 'Eventuell')
							{
							continue;
							}
							else
							{
								$sh_title_test = preg_replace('/Teambesprechung: /', '', $sh_title[0]);
										
								if(trim($sh_title_test) == trim($todotext) && trim($sh_title[2]) == trim($todotill))
								{
									$shid = $shval['id'];
									$outfor = 1;
									break;
								}
							}
							if($outfor == 1) {
								break;
							}
						}
						
						if ($shid != "" and count($sh_title) == 4)
						{
							$sh_title[] = $logininfo->userid.", Erledigt von ".$username." am ".date('d.m.Y');
							$shtitle = implode('|---------|', $sh_title);
							$upd = Doctrine::getTable('PatientCourse')->find($shid);						
							$upd->course_title = Pms_CommonData::aesEncrypt(addslashes($shtitle));
							$upd->save();
						}					
					}
				}
				// it is done even the entry in patientcourse is not found
				$todo->iscompleted = '1';
				$todo->complete_user = $logininfo->userid;
				$todo->complete_date = date("Y-m-d H:i:s", time());
				$todo->save();
			}
		}

		public function uncompleteTodo($id)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$todo = Doctrine::getTable('ToDos')->find($id);
			if($todo)
			{
				$todo->iscompleted = '0';
				$todo->complete_user = $logininfo->userid;
				$todo->complete_date = date("Y-m-d H:i:s", time());
				$todo->save();
			}
		}
		
		public function uncompleteTodonew($id)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$todo = Doctrine::getTable('ToDos')->find($id);
			if($todo)
			{					
				$usr = new User();
				$userdata = $usr->getUserDetails($logininfo->userid);
				
				$usrgrid = $userdata[0]['groupid'];
						
				$todoipid = $todo['ipid'];
				$todocrdate = $todo['create_date'];
				$todotext = $todo['todo'];
				$todouserid = $todo['user_id'];
				$todogroupid = $todo['group_id'];
				$todotill = date('d.m.Y', strtotime($todo['until_date']));
				
				$patcourse = new PatientCourse();
				$currper = array();
				$currper['start'] = $todocrdate;
				$currper['end'] = $todocrdate;
				$patshotw = $patcourse->get_patient_shortcuts_course($todoipid, 'W', $currper);
				
				if(!empty($patshotw))
				{
					$shid = "";
					$outfor = 0;
					foreach ($patshotw as $shkey=>$shval)
					{						
						$sh_title = explode('|---------|', $shval['course_title']);
						
						if(substr($sh_title[0], 0, 9) == 'Eventuell')
						{
							continue;
						}
						else
						{
							$sh_title_test = preg_replace('/Teambesprechung: /', '', $sh_title[0]);
							
							if (trim($sh_title_test) == trim($todotext) && trim($sh_title[2]) == trim($todotill))
							{
								$shid = $shval['id'];
								$outfor =1;
								break;
							}						
						}
					if($outfor == 1)
					{
						break;
					}
				
				}
				
				if ($shid != "")
				{							
						
						unset($sh_title[4]);
						$shtitle = implode('|---------|', $sh_title);
								
														
						$upd = Doctrine::getTable('PatientCourse')->find($shid);
						$upd->course_title = Pms_CommonData::aesEncrypt(addslashes($shtitle));
						$upd->save();
						
				}
			}

				// it is done even the entry in patientcourse is not found				
				$todo->iscompleted = '0';
				$todo->complete_user = $logininfo->userid;
				$todo->complete_date = date("Y-m-d H:i:s", time());
				$todo->save();
		}
	}
		

		public function getTodosByClientIdAndIpid($clientid, $ipid)
		{
			$todo = Doctrine_Query::create()
				->select("*")
				->from('ToDos')
				->where('client_id="' . $clientid . '"')
				->andWhere('ipid="' . $ipid . '"')
				->andWhere('isdelete=0');
			$todoarray = $todo->fetchArray();
			
			if(!empty($todoarray))
			{
				return $todoarray;
			}
		}

		public function getTodosByClientId($clientid)
		{
			$todo = Doctrine_Query::create()
				->select("*")
				->from('ToDos')
				->where('client_id="' . $clientid . '"')
				->andWhere('isdelete=0')
				->andWhere('iscompleted=0');
			$todoarray = $todo->fetchArray();
			
			if(!empty($todoarray))
			{
				return $todoarray;
			}
		}

		public function getCompletedTodosByClientId($clientid, $skip_ids = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$todo = Doctrine_Query::create()
				->select("*")
				->from('ToDos')
				->where('client_id="' . $clientid . '"')
				->andWhere('isdelete=0')
				->andWhere('iscompleted=1');
			if($skip_ids)
			{
				$todo->andWhereNotIn('id', $skip_ids);
			}
			$todo->andWhere('(user_id = "' . $logininfo->userid . '" OR group_id = "' . $logininfo->groupid . '")');
			$todoarray = $todo->fetchArray();
			if(count($todoarray) > 0)
			{
				return $todoarray;
			}
		}

		public function deleteTodoById($id)
		{
			$todo = Doctrine::getTable('ToDos')->find($id);
			$todo->isdelete = '1';
			$todo->save();
		}

		/**
		 * be aware, the fn name may be misleading - this is how Doctrine works!
		 * this fn will insert new if there is no db-record object in our class...
		 * if you called second time, or you fetchOne, it will update!
		 * fn was intended for single record, not collection
		 * @param array $params
		 * @return boolean|number
		 * return $this->id | false if you don't have the mandatory_columns in the params
		 */
		public function set_new_record($params = array())
		{
		
			if (empty($params) || !is_array($params)) {
				return false;// something went wrong
			}
			if (empty($params['ipid']) || empty($params['user_id']) ||  empty($params['client_id']) ) {
				return false;
			}
		
			foreach ($params as $k => $v)
				if (isset($this->{$k})) {
		
					//next columns should be encrypted
					switch ($k) {
						case "column_name_example1":
						case "column_name_example2":
							$v = Pms_CommonData::aesEncrypt($v);
							break;
					}
					$this->{$k} = $v;
		
				}
			
// 			if (empty($params['course_date'])){
// 				$this->course_date = date("Y-m-d H:i");
// 			}
			
			$this->save();
			return $this->id;
		
		}
	}

?>