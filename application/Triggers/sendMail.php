<?
// require_once 'Pms/Triggers.php';
class application_Triggers_sendMail extends Pms_Triggers
{

	public function createFormPatientAdd ()
	{
		$clt = Doctrine::getTable('Client')->findAll();
		$cltarray = $clt->toArray();
		$client_array = array("" => "Select Client");

		foreach ($cltarray as $keyarray => $clientval)
		{

			$client_array[$clientval['id']] = $clientval['client_name'];
		}

		$this->view->client_array = $client_array;



		if (strlen($this->view->{$this->view->inputstr}['clientid']))
		{

			$clt = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('clientid=' . $this->view->{$this->view->inputstr}['clientid'] . ' and isactive=0 and isdelete=0');
			$usrexec = $clt->execute();
			$cltarray = $usrexec->toArray();

			foreach ($cltarray as $keyarray => $clientval)
			{

				$user_array[$clientval['id']] = $clientval['username'];
			}

			$this->view->user_array = $user_array;
		}

		return $this->view->render("trigger/formtriggerinputs/patientAdd/sendEmail.html");
	}

	public function createFormPatient ()
	{
		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendEmail.html");
	}

	public function createFormWriteMessage ()
	{
		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendEmail.html");
	}

	public function createFormCourseDocumentation ()
	{
		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendEmail.html");
	}

	public function createFormPatientLocation ()
	{
		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendEmail.html");
	}

	public function createFormPatientHealthInsurance ()
	{
		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendEmail.html");
	}

	public function createFormPatientDiagnosis ()
	{
		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendEmail.html");
	}

	public function createFormPatientDischarge ()
	{
		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendEmail.html");
	}

	public function createFormPatientMedication ()
	{
		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendEmail.html");
	}

	public function createFormAssignusertoPatient ()
	{
		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendEmail.html");
	}

	public function createFormPatientCase ()
	{
		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendEmail.html");
	}

	/**
	 * 
	 * Jul 10, 2017 @claudiu 
	 * 
	 * @return string
	 */
	public function createFormRezeptAnforderung ()
	{
		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendEmail.html");
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function getUsers ()
	{

		if ($_GET['cid'] > 0)
		{

			$usr = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('clientid=' . $_GET['cid'] . ' and isactive=0 and isdelete=0');
			$usrexec = $usr->execute();
			$userarray = $usrexec->toArray();
			/* $usr = Doctrine::getTable('User')->findBy('clientid',$_GET['cid']);
			 $usrarray = $usr->toArray(); */
			$userarray = array("" => "Select User");
			foreach ($usrarray as $key => $val)
			{
				$userarray[$val['id']] = $val['username'];
			}
		}

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();

		$response['callBackParameters']['userlist'] = $this->view->formSelect('event_' . $_GET['eventid'] . '_' . $_GET['trrid'] . '[userid][]', $_POST['userid'], array("multiple" => "multiple"), $userarray);
		$response['callBackParameters']['eventid'] = $_GET['eventid'];
		$response['callBackParameters']['trrid'] = $_GET['trrid'];

		echo json_encode($response);
		exit;
	}

	public function triggerWriteMessage ( $event, $inputs, $fieldname, $fieldid, $eventid )
	{

		if ($eventid == 2)
		{
			if ($fieldid == 0)
			{
				$message = $input['message'];
				$from = $input['fromaddress'];
				$subject = $input['subject'];
				$invoker = $event->getInvoker();

				$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));

				$mail = new Zend_Mail('UTF-8');
				$receiver_id = $invoker->recipient;
				$usr = Doctrine::getTable('User')->find($receiver_id);
				$usrarray = $usr->toArray();
				$useremail_id = $usrarray['emailid'];
				$firstname = $usrarray['first_name'] . " " . $usrarray['last_name'];

				$mail->setBodyText($message)
				->setFrom($from, ISPC_SENDERNAME)
				->addTo($useremail_id, $firstname)
				->setSubject($subject . '- System Notification, ' . date('d.m.Y H:i'))
				->send($mail_transport);
			}

			if ($fieldid > 0)
			{

			}
		}
	}

	public function triggersendMail ( $event, $inputs, $fieldname, $fieldid, $eventid, $gpost )
	{
		$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		
		if($controller != 'cron') {
			$logininfo = new Zend_Session_Namespace('Login_Info');
	
			if ($fieldname != "first_name")
			{
				$epid = isset($event->getInvoker()->epid) ? $event->getInvoker()->epid : "" ;
			}
			if ($fieldname == "first_name")
			{
				$decid = $event->getInvoker()->id;
				$patientmaster = new PatientMaster();
				$patarr = $patientmaster->getMasterData($decid, 0);
			}
	
			$formname = $event->getInvoker()->triggerformname;
	
			switch ($formname) {
				
				case "frmasignusertopatient":{
					$receiver_id = $event->getInvoker()->userid;
					
					$ipid = Pms_CommonData::getIpidFromEpid($epid);
					
					$pm = new PatientMaster();
					$patarr = $pm->getMasterData(0, 0, 0, $ipid);
				}
				break;
				
				case "frmwritemessages":{
					$receiver_id = $event->getInvoker()->recipient;
				}
				break;
					
				case "frmpatientcourse":{
					$receiver_id = $event->getInvoker()->user_id;
				}
				break;
				
				case "frmTeamMeeting":
				case "frmDoctorRecipeRequest":{
					//this must contain $gpost[all_users_2_send'] so we have the ids
					return $this->_send_multiple_users( $event, $inputs, $fieldname, $fieldid, $eventid, $gpost);
				}
				break;

				default:{
					$receiver_id = $event->getInvoker()->create_user;
				}
					
			}
		
	
			$message = $inputs['message'];
			$from = $inputs['fromaddress'];
			$subject = $inputs['subject'];
	
	
			$send = 1;
			$bielefeldid = 38;
	
			$user_notification_settings = Notifications::get_notification_settings($receiver_id);
	
			if($user_notification_settings[$receiver_id]['admission'] != 'none')
			{
				$allow_notification = true;
			}
			else
			{
				$allow_notification = false;
			}
	
			if ($logininfo->clientid == $bielefeldid && $receiver_id == $logininfo->userid)
			{
				$send = 0;
			}
	
			if ($send == 1 && $allow_notification)
			{
				$usr = Doctrine::getTable('User')->find($receiver_id);
	
				if ($usr)
				{
					$usrarray = $usr->toArray();
	
					$useremail_id = $usrarray['emailid'];
	
					$firstname = str_replace(".", "\.", $usrarray['first_name']);
					$firstname = str_replace("'", "\'", $firstname);
					$firstname = str_replace(",", "", $firstname);
	
					$lastname = str_replace(".", "\.", $usrarray['last_name']);
					$lastname = str_replace(",", "", $lastname);
					$lastname = str_replace("'", "\'", $lastname);
	
	
					$firstname = $firstname . " " . $lastname;
	
					$patarr['userfirstname'] = $usrarray['first_name'];
					$patarr['userlastname'] = $usrarray['last_name'];
					$patarr['epid'] = $epid;
					$message = $this->setTriggerPlaceHolders($patarr, $inputs['message']);
				}
	
				if (strlen($useremail_id) > 0)
				{
					$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
					$mail = new Zend_Mail('UTF-8');
					$mail->setBodyText($message)
					->setFrom($from, ISPC_SENDERNAME)
					->addTo($useremail_id, $firstname)
					->setSubject($subject . '- System Notification, ' . date('d.m.Y H:i'))
					->send($mail_transport);
				}
			}
	
			if ($logininfo->clientid == $bielefeldid && $fieldname == "first_name" && $allow_notification)
			{
	
	
				$rs = new Roster();
				$rsarr = $rs->getCurrentQPA($bielefeldid);
	
				$rsmail = $rsarr[0]['User']['emailid'];
	
				$usr = Doctrine::getTable('User')->find($receiver_id);
				$usrarray = $usr->toArray();
				$firstname = $usrarray['first_name'] . " " . $usrarray['last_name'];
	
				$patarr['userfirstname'] = $usrarray['first_name'];
				$patarr['userlastname'] = $usrarray['last_name'];
				$patarr['epid'] = $epid;
	
				$newmessage = $this->setTriggerPlaceHolders($patarr, $inputs['message']);
	
				if (strlen($rsmail) > 0)
				{
					$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
					$mail = new Zend_Mail('UTF-8');
					$mail->setBodyText($newmessage)
					->setFrom($from, ISPC_SENDERNAME)
					->addTo($rsmail, $firstname)
					->setSubject($subject . '- System Notification, ' . date('d.m.Y H:i'))
					->send($mail_transport);
				}
			}
		}
	}

	private function setTriggerPlaceHolders ( $patarr, $message )
	{

		$placeholder['patientfirstname'] = $patarr['first_name'];
		$placeholder['patientlastname'] = $patarr['last_name'];
		$placeholder['patientaddress'] = $patarr['street1'];
		$placeholder['patientzip'] = $patarr['zip'];
		$placeholder['patientcity'] = $patarr['city'];
		$placeholder['patientphone'] = $patarr['phone'];
		$placeholder['patientmobile'] = $patarr['mobile'];
		$placeholder['patientbirthdate'] = $patarr['birthd'];
		$placeholder['patientgender'] = $patarr['sex'];
		$placeholder['patientadmissiondate'] = $patarr['admission_date'];
		$placeholder['userfirstname'] = $patarr['userfirstname'];
		$placeholder['userlastname'] = $patarr['userlastname'];
		$placeholder['epid'] = $patarr['epid'];


		foreach ($placeholder as $key => $val)
		{
			$message = str_replace("#" . $key . "", $val, $message);
		}
		return $message;
	}

		
	/**
	 * 
	 * Jul 10, 2017 @claudiu 
	 * triggersendMail() will redirect here if you piggyback
	 * fn is for single ipid, multiple users
	 * 
	 * @param unknown $event
	 * @param unknown $inputs
	 * @param unknown $fieldname
	 * @param unknown $fieldid
	 * @param unknown $eventid
	 * @param unknown $gpost
	 * $gpost['all_users_2_send'] = userid array(1,2,3) - this has priority
	 * or
	 * $gpost['users_and_groups_2_send'] = all array(u11, g22, pseudogroup_33)
	 */
	private function _send_multiple_users($event, $inputs, $fieldname, $fieldid, $eventid, $gpost)
	{
	
		if (empty($gpost['all_users_2_send']) && empty($gpost['users_and_groups_2_send'])) {
			return;
		}
		
		if ( ! isset($event->getInvoker()->ipid)) {
			return;
		}
		
		if (empty($gpost['all_users_2_send'])) {
			$gpost['all_users_2_send'] = $this->_extract_user_from_groups($gpost['users_and_groups_2_send']);
		}
		
		$patarr = array();
		$patientqpa = array();
		
		$Tr = new Zend_View_Helper_Translate();
		
		$triggerformname = $event->getInvoker()->triggerformname;
		
		$message = $gpost['verlauf_entry'] . "\n\n" . $inputs['message'];
		$from = $inputs['fromaddress'];
		$subject = $inputs['subject'];
		
		//user notification checking
		$user_notification_settings = Notifications::get_notification_settings($gpost['all_users_2_send']);
	
		if ($triggerformname == "frmDoctorRecipeRequest") {
			$notification_settings_field = "medication_doctor_receipt";
		}
		elseif ($triggerformname == "frmTeamMeeting") {
			$notification_settings_field = "todo";
		}
		
		$ipid = '';
		if (isset($event->getInvoker()->ipid)) {
			$ipid = $event->getInvoker()->ipid;
		}
		$patarr = array();
		if ($ipid != '') {
			$pm = new PatientMaster();
			$patarr = $pm->getMasterData(0, 0, 0, $ipid);
			
			$patarr['userfirstname'] = $patarr['first_name'];
			$patarr['userlastname'] = $patarr['last_name'];

			$qpas = new PatientQpaMapping();
			$patientqpa = $qpas->getAssignedUsers(array($ipid));
				
		}
		
		$users_array = User::getUsersNiceName($gpost['all_users_2_send'], $this->logininfo->clientid);
		
		$k_cnt = 0;
		$messages_array = array();
		$recipients_array =  array();
		foreach($users_array as $user)
		{
			
			if( empty($user['emailid']) 
					|| $user_notification_settings[$user['id']][$notification_settings_field] == 'none'
					|| ($user_notification_settings[$user['id']][$notification_settings_field] == 'assigned' && ! in_array($user['id'], $patientqpa['assignments'][$ipid]))
			) {
				continue; // this user settings ignores our messages
			}
			
	
			$messages_array[$k_cnt]['subject'] = $subject;
			$messages_array[$k_cnt]['body'] = $this->setTriggerPlaceHolders($patarr, $message);
			$messages_array[$k_cnt]['email'] = trim($user['emailid']);
			$messages_array[$k_cnt]['nice_name'] = $user['nice_name'];
			
			$recipients_array[$k_cnt] = $user['nice_name'];

			$k_cnt++;
			
		}
				
		if (! empty($messages_array)) {
		
			$mail_transport		= new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
			$mail_FromEmail		= empty($from) ? ISPC_SENDER : $from;
			$mail_FromName		= ISPC_SENDERNAME;
			
			$extra_body_text = "";
			
			if (count($recipients_array) > 1) {
// 				$extra_body_text .= "\n\n".$Tr->translate("other_receipients")."\n".implode(", ",$recipients_array);
			}
			
		
			
						
			foreach ($messages_array as $message) {
			    //TODO-3164 Ancuta 08.09.2020
			    $email_data = array();
			    $email_data['additional_text'] = nl2br($message['body'] . $extra_body_text);
			    $email_html = "";//overwrite
			    $email_html = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
			    //--
				$mail = new Zend_Mail('UTF-8');
				$mail->setBodyText($message['body'] . $extra_body_text)
				->setBodyHtml($email_html)//TODO-3164 Ancuta 08.09.2020
				->setFrom($mail_FromEmail, $mail_FromName)
				->addTo($message['email'], $message['nice_name'])
				->setSubject($message['subject'] . '- System Notification, ' . date('d.m.Y H:i'));
				$mail->send($mail_transport);
			}
		}
		
		
		return;

	
	}


	/**
	 * 
	 * Jul 19, 2017 @claudiu
	 * example 
	 * $users => Array
                (
                    [0] => g350
                    [1] => u130
                    [2] => u3055
                    [3] => g144
                    [4] => pseudogroup_7
                )

        $result => Array
                (
                    [0] => 130
                    [1] => 3055
                    [2] => 3086
                    [3] => 267
                    [4] => 1754
                    [5] => 1567
                    [6] => 660
                    [8] => 295
                )
	 * 
	 * @param array $users
	 * @return multitype:
	 */
	private function _extract_user_from_groups( $users =  array() )
	{
		$result = array();
		$clientid = $this->logininfo->clientid;

		$selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();

	
		$users_array = array();
		$group_array = array();
		$pseudogroup_array = array();
			
		foreach($users as $user) {
			
			if(strpos($user, $selectbox_separator_string["group"], 0) === 0) {
				$group_array[] = substr($user, strlen($selectbox_separator_string["group"]));
			}
			elseif(strpos($user, $selectbox_separator_string["user"], 0) === 0) {
				$users_array[] = substr($user, strlen($selectbox_separator_string["user"]));
			}
			elseif(strpos($user, $selectbox_separator_string["pseudogroup"], 0) === 0) {
				$pseudogroup_array[] = substr($user, strlen($selectbox_separator_string["pseudogroup"]));
			}
		}
		
		if(in_array($selectbox_separator_string["all"], $users)){ // send to all
			//get all the groups			
			$users_array = array();
			$group_array = array();
			$pseudogroup_array = array();
			
			$usergroup_obj = new Usergroup();
			$groupsdata = $usergroup_obj->getClientGroups($clientid );
			if (is_array($groupsdata)) {
				$group_array = array_column($groupsdata, 'id');
			}		
			
		}	
	
	
		if( ! empty($users_array)) {
			
			$result = array_merge($result, $users_array);
		}
	
		
		if( ! empty($group_array)) {
			//get all users from groups
			$usergroup_obj = new Usergroup();
			$users_in_groups = $usergroup_obj->get_groups_users($group_array, $clientid , true);
			
			if (is_array($users_in_groups)) {
				$result =  array_merge($result, $users_in_groups);
			}
		}
	
	
		if ( ! empty($pseudogroup_array)) {
			//get all users from pseudo-groups
			$pgu_obj = new PseudoGroupUsers();
			$users_in_pseudogroups = $pgu_obj->get_users_by_groups($pseudogroup_array);	
			if (is_array($users_in_pseudogroups['all_user_id'])) {
 				$result =  array_merge($result, $users_in_pseudogroups['all_user_id']);
			}
	
		}
				
		return array_unique($result);

	}

}
?>
