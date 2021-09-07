<?

class Application_Form_DoctorRecipeRequest extends Pms_Form {


	private $triggerformid = 18;
	private $triggerformname = "frmDoctorRecipeRequest";  //piggyback the triggers from PatientCourse
		

	public function validate() 
	{
		//we should validate that the users we are sending, belong to this clientid	
	}
	
	public function InsertData($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		$selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();
		
		$user_separator_string = $selectbox_separator_string['user'];
		$group_separator_string = $selectbox_separator_string['group'];
		$pseudogroup_separator_string = $selectbox_separator_string['pseudogroup'];
		
		$Tr = new Zend_View_Helper_Translate();
		
		$post_data =  $post['data'];
		
		$send_to_all_users = false;
		$verlauf_users =  array();
		
		$working_to_send_array =  array( 
				$user_separator_string =>  array(),
				$group_separator_string =>  array(),
				$pseudogroup_separator_string  =>  array(),
		);
		
		$final_to_send_array = array();
		
		foreach ($post_data['phuser'] as $opt) 
		{
			if ($opt == $selectbox_separator_string["all"]) {
				
				$send_to_all_users = true;
				
				$working_to_send_array =  array();
				
				break;
			}
			
			if(strpos($opt, $user_separator_string, 0) === 0)
			{
				$pseudoid = substr($opt, strlen($user_separator_string));
				$working_to_send_array[$user_separator_string][] = $pseudoid;			
			}
			elseif(strpos($opt, $group_separator_string, 0) === 0)
			{
				$pseudoid = substr($opt, strlen($group_separator_string));
				$working_to_send_array[$group_separator_string][] = $pseudoid;			
			}
			elseif(strpos($opt, $pseudogroup_separator_string, 0) === 0)
			{
				$pseudoid = substr($opt, strlen($pseudogroup_separator_string));
				$working_to_send_array[$pseudogroup_separator_string][] = $pseudoid;			
			}
		}
		
		if ( ! empty($working_to_send_array[$group_separator_string])) {
			
			$usergroup = new Usergroup();
			$todogroups = $usergroup->getClientGroups($post['clientid']);
			
			foreach ($todogroups as $group) {
				if (in_array($group['id'], $working_to_send_array[$group_separator_string])) {
					
					$verlauf_users[] = $group['groupname'];
					
					$final_to_send_array[] =  array(
							'user_id' =>  0,
							'group_id' =>  $group['id']							
					);
				}
			}
			
			$user_obj = new User();
			$users_in_groups = $user_obj->getuserbyGroupId($working_to_send_array[$group_separator_string] , $post['clientid'], true );
			foreach ($users_in_groups as $user) {
				$all_users_sent[] =  $user['id'];
			}
			
			
		}
		
		
		
		if ( ! empty($working_to_send_array[$user_separator_string])
				|| $send_to_all_users
		) {	
			
			$user = new User();
			
			if ($send_to_all_users) {
				
				$UsersDetails = $user->getClientsUsers($post['clientid'] );
				$working_to_send_array[$user_separator_string] = $UsersDetails['ids'];
				
			} else {
				$UsersDetails = $user->getMultipleUserDetails( $working_to_send_array[$user_separator_string] );
			}
			User::beautifyName($UsersDetails);

						
			foreach ($working_to_send_array[$user_separator_string] as $user) {
				if ( ! in_array($user, $all_users_sent)) {
					
					$final_to_send_array[] =  array(
							'user_id' =>  $user,
							'group_id' =>  0
					);
					
					$verlauf_users[] = $UsersDetails[$user]['nice_name'];
					
					$all_users_sent[] = $user;
				}
			}
		}
		
		if ( ! empty($working_to_send_array[$pseudogroup_separator_string])) {
			
			$user_pseudo =  new UserPseudoGroup();
			$user_ps =  $user_pseudo->get_pseudogroups_for_todo($post['clientid']);
			foreach ($user_ps as $group) {
				if (in_array($group['id'], $working_to_send_array[$pseudogroup_separator_string])) {
						
					$verlauf_users[] = $group['servicesname'];
				}
			}
			
			$pgu_obj = new PseudoGroupUsers();
			$users_in_pseudogroups = $pgu_obj->get_users_by_groups($working_to_send_array[$pseudogroup_separator_string]);

			foreach ($users_in_pseudogroups['all_user_id'] as $pseudo_user) {
				if ( ! in_array($pseudo_user, $all_users_sent)) {
						
					$final_to_send_array[] =  array(
							'user_id' =>  $pseudo_user,
							'group_id' =>  0
					);
					
					$all_users_sent[] = $pseudo_user;
				}
			}
		}

		//assign doctors to patient	
		if($post['data']['assign_user'] == '1'
				&& ! empty($all_users_sent)
				&& ! empty($post['patientMasterData'])
		) {
			$allready_assigned_qpa = array();
			if ( ! empty($post['patientMasterData']['PatientQpaMapping'])) {
				$allready_assigned_qpa = array_column($post['patientMasterData']['PatientQpaMapping'], "userid");
			}
			
			$new_assigned_qpa = array_diff($all_users_sent , $allready_assigned_qpa);
						
			foreach($new_assigned_qpa as $userid) {
				
				PatientQpaMapping::assignUserPatient($userid, $post['epid'], 0);
				
				PatientUsers::assignUserPatient($userid, $ipid, 0);
			}
		}		

		$meds = "";
		if(!empty($post_data['medication']))
		{
			$med = new Medication();
			$master_medarr = $med->master_medications_get($post_data['medication']);
			if(!empty($master_medarr))
			{
				$meds = "\n". implode("\n",$master_medarr);
			}
		}
		
		$patname = $post['patientMasterData']['nice_name'];
		$pataddress = $post['patientMasterData']['nice_address'];
		
		$verlauf_entry = $Tr->translate("Rezept-Anforderung") . ":" . $meds  ;
		if (trim($post_data['komment']) != "") {
			$verlauf_entry .=  "\nKommentar: " . $post_data['komment'] ;
		}
		$verlauf_entry .= "\nBenutzer: " .  implode($selectbox_separator_string['glue_on_view'], $verlauf_users);
		
		
		$cust = array();
		$cust['ipid'] = $post['ipid'];
		$cust['course_date'] = date("Y-m-d H:i");
		$cust['course_type'] = "Q";
		$cust['course_title'] = $verlauf_entry;
		$cust['user_id'] = $logininfo->userid;
		$cust['tabname'] = "reciperequest";
		
		//add course_title  as _POST so we haveit in the trigger
		// course_date will be used also
		$_POST['verlauf_entry'] = $verlauf_entry;
		$_POST['course_title'] = $Tr->translate("mail_subject_action_recipe_request");
		if (trim($post_data['komment']) != "") {
			$_POST['course_title'] .=  "\n (" . $post_data['komment'].")" ;
		}
		$_POST['all_users_2_send'] = array_unique($all_users_sent);
		
		$pc = new PatientCourse();
		
		//piggyback the triggers from PatientCourse
		$pc->triggerformid = $this->triggerformid;
		$pc->triggerformname = $this->triggerformname;
		
		$pc_id = $pc->set_new_record($cust);
			

		return; //moved all into the triggers
		
		
		
		
		//save to local message table
		$message_entry = $Tr->translate("Rezept-Anforderung") . ": \n " . $meds ;
		
		if (trim($post_data['komment']) != "") {
			$message_entry .=  "\n " . $post_data['komment'] ;
		}
		
		$message_entry .= "\n" . $patname . "\n" . $pataddress;
		
		//SEND MESSAGE
		$mail = new Messages();
		$mail->sender = $post->userid;
		$mail->clientid = $post->clientid;
		$mail->recipient = $usertosend;
		$mail->msg_date = date("Y-m-d H:i:s", time());
		$mail->title = Pms_CommonData::aesEncrypt($Tr->translate('Medication Order'));
		$mail->content = Pms_CommonData::aesEncrypt($message_entry);
		$mail->source = "medication_order";
		$mail->create_date = date("Y-m-d", time());
		$mail->create_user = $post->userid;
		$mail->read_msg = '0';		
		$mail->save();
		$mail_id = $mail->id;

		//send email
		$the_sender = '';
		if( ! empty($UserDetails[$post['userid']]))
		{
			$the_sender = $UserDetails[$post['userid']]['first_name'] . ' ' . $UserDetails[$post['userid']]['last_name'];
		}
		

		
		
		
		
		$email_subject = $Tr->translate('mail_subject_action_recipe_request') . ' - ' . $the_sender . ', ' . date('d.m.Y H:i');
		$email_text = "";
		$email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
		// link to ISPC
		// $email_text .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>";
		// ISPC-2475 @Lore 31.10.2019 // Maria:: Migration ISPC to CISPC 08.08.2020	
		$email_text .= $Tr->translate('system_wide_email_text_login');
		// client details
		$client_details_array = Client::getClientDataByid($post['clientid']);
		if(!empty($client_details_array)){
			$client_details = $client_details_array[0];
		}
		$client_details_string = "<br/>";
		$client_details_string  .= "<br/> ".$client_details['team_name'];
		$client_details_string  .= "<br/> ".$client_details['street1'];
		$client_details_string  .= "<br/> ".$client_details['postcode']." ".$client_details['city'];
		$client_details_string  .= "<br/> ".$client_details['emailid'];
		$email_text .= $client_details_string;
	
		//TODO-3164 Ancuta 08.09.2020
		$email_data = array();
		$email_data['client_info'] = $client_details_string;
		$email_text = "";//overwrite
		$email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
		//--
		if($mail_id > 0  && !empty($UserDetails[$usertosend]['emailid']))
		{

			$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
			$mail = new Zend_Mail('UTF-8');
			$mail->setBodyHtml($email_text)
			->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
			->addTo($UserDetails[$usertosend]['emailid'], $UserDetails[$usertosend]['last_name'] . ' ' . $UserDetails[$usertosend]['first_name'])
			->setSubject($email_subject)
			->setIpids($ipid)
			->send($mail_transport);

		}	

		
		//ISPC-1884 - this entire function should have been a trigger: addInternalMessage , addValuetoToDos , sendMail
		$user_notification_settings = Notifications::get_notification_settings($usertosend);
		if (isset($user_notification_settings[$usertosend]) && $user_notification_settings[$usertosend]['medication_doctor_receipt'] != 'none') {
				
			$add_todo = true;
				
			if ($user_notification_settings[$usertosend]['medication_doctor_receipt'] == 'assigned'
					&& $post_data['assign_user'] != "1")
			{
				//find if this patient is assigned to user
				$add_todo = PatientQpaMapping::assert_epid2userid($post['epid'], $usertosend);
		
			}
				
			if ($add_todo) {
		
				$todo_text = $Tr->translate('mail_subject_action_recipe_request');
				if (trim($post_data['komment']) != "") {
					$todo_text .=  "<br>" . $post_data['komment'] ;
				}
				
				$todo_arr =  array(
						"id"			=> null,
						"client_id"		=> $post['clientid'],
						"user_id"		=> $usertosend,
						"group_id"		=> "0",
						"ipid"			=> $post['ipid'],
						"todo"			=> $todo_text,
						"triggered_by"	=> "frmDoctorRecipeRequest",
						"isdelete"		=> "0",
						"iscompleted"	=> "0",
						"course_id"		=> $pc_id,
						"create_date"	=> date("Y-m-d H:i:s", time()),
						"until_date"	=> date("Y-m-d H:i:s", time()),
						"additional_info" => "",
				);
		
				$ToDos_obj = new ToDos();
				$ToDos_id = $ToDos_obj->set_new_record($todo_arr);
			}
				
				
		}
		
		return $mail_id;
		
	}
	



}

?>