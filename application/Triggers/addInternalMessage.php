<?
// require_once 'Pms/Triggers.php';
class application_Triggers_addInternalMessage extends Pms_Triggers
{

	private static $receiver_id = null; // id from User
	
	public function createFormAssignusertoPatient ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$user = Doctrine_Query::create()
		->select("*")
		->from('User')
		->where('clientid = ?', $logininfo->clientid)
		->andWhere('isdelete = 0')
		->orderBy('last_name ASC');

		$userexec = $user->execute();
		$userarray = $userexec->toArray();

		if (count($userarray) > 0)
		{
			$grid = new Pms_Grid($userarray, 4, count($userarray), "listclientuser.html");
			$this->view->usergrid = $grid->renderGrid();
		}
		else
		{
			$this->view->usergrid = "<div class='err'>" . $this->view->translate('nousers') . "</div>";
		}

		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendInternalMessage.html");
	}

	/**
	 * 
	 * Jul 10, 2017 @claudiu 
	 * 
	 * @return string
	 */
	public function createFormRezeptAnforderung ()
	{
		return $this->view->render("trigger/formtriggerinputs/WriteMessage/sendInternalMessage.html");
	}

	

	public function triggeraddInternalMessage ( $event, $inputs, $fieldname, $fieldid, $eventid, $gpost )
	{

		//piggyback 
		if ("frmDoctorRecipeRequest" == $fieldname 
				|| "frmTeamMeeting" == $fieldname
				|| "frmStudypool" == $fieldname
		) {
					
			//$gpost['all_users_2_send'] array that contain the users... 
			//in the future save the users we send this message so we can use invoker->aditional_info
			return $this->_send_multiple_users($event, $inputs, $fieldname, $fieldid, $eventid, $gpost );
		} 
		
		if (isset($event->getInvoker()->userid)) {
			self::$receiver_id = $event->getInvoker()->userid;
		}
		elseif (isset($event->getInvoker()->user_id)) {
			self::$receiver_id = $event->getInvoker()->user_id;
		}
		
		$receiver_id = self::$receiver_id;

		if (is_null($receiver_id)) {
			return; // nothing to do
		}
		
		$epid = "";
		if (isset($event->getInvoker()->epid)) {
			$epid = $event->getInvoker()->epid;
		}

		
		$ipid = "";
		if($epid != "") {
			$ipid = Pms_CommonData::getIpidFromEpid($epid);
		}
		elseif (isset($event->getInvoker()->ipid)) {
			$ipid = $event->getInvoker()->ipid;
		} 
		
		
		//user notification checking
		$user_notification_settings = Notifications::get_notification_settings($receiver_id);

		if($user_notification_settings[$receiver_id]['admission'] != 'none')
		{
			$allow_notification = true;
		}
		else
		{
			$allow_notification = false;
		}

		if($allow_notification)
		{
			$patarr = array();
			if ($ipid != "") {
				$pm = new PatientMaster();
				$patarr = $pm->getMasterData(0, 0, 0, $ipid);
			}
			
			$userdata = Pms_CommonData::getUserDataById($receiver_id);
			$patarr['userfirstname'] = $userdata[0]['first_name'];
			$patarr['userlastname'] = $userdata[0]['last_name'];
			$patarr['epid'] = $epid;

			$message = $this->setTriggerPlaceHolders($patarr, $inputs['content']);

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$Tr = new Zend_View_Helper_Translate();

			$mail = new Messages();
			$mail->sender = $logininfo->userid;
			$mail->clientid = $logininfo->clientid;
			$mail->recipient = $receiver_id;
			$mail->msg_date = date("Y-m-d H:i:s", time());
			$mail->title = Pms_CommonData::aesEncrypt($inputs['title']);
			$mail->content = Pms_CommonData::aesEncrypt($message);
			$mail->create_date = date("Y-m-d", time());
			$mail->create_user = $logininfo->userid;
			$mail->save();
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
	 * triggeraddInternalMessage() will redirect here if you came from Application_Form_DoctorRecipeRequest
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
	 * 
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
		
		//user notification checking
		$user_notification_settings = Notifications::get_notification_settings($gpost['all_users_2_send']);	
		
		if ($triggerformname == "frmDoctorRecipeRequest") {
			$notification_settings_field = "medication_doctor_receipt";
		}
		elseif ($triggerformname == "frmTeamMeeting") {
			$notification_settings_field = "todo";
		}
		else {
			//always notify, bypass user settings and send him message
			$notification_settings_field = true;
		}
		
		
		$ipid = '';
		if (isset($event->getInvoker()->ipid)) {
			$ipid = $event->getInvoker()->ipid;
		}
		$patarr = array();
		$pat_encoded_id = 0;
		if ($ipid != '') {
			$pm = new PatientMaster();
// 			$patarr = $pm->getMasterData(0, 0, 0, $ipid);
			$patarr = $pm->get_multiple_patients_details( array($ipid));
			PatientMaster::beautifyName($patarr);
			$patarr = $patarr[$ipid];
			
			$pat_encoded_id = Pms_Uuid::encrypt($patarr['id']);
			
			$qpas = new PatientQpaMapping();
			$patientqpa = $qpas->getAssignedUsers(array($ipid));
		}
				
		$Invoker_id = isset($event->getInvoker()->id) ? $event->getInvoker()->id : "";	
			
		$records_array = array();
		$messages_array = array();
		$recipients_array = array();
		$msg_date = date("Y-m-d H:i:s", time());
		$create_date = date("Y-m-d", time());
		
		$k_cnt = 0;
		foreach($gpost['all_users_2_send'] as $receiver_id) 
		{
			
			if($notification_settings_field !== true &&
					( $user_notification_settings[$receiver_id][$notification_settings_field] == 'none' 
						|| ($user_notification_settings[$receiver_id][$notification_settings_field] == 'assigned' && ! in_array($receiver_id, $patientqpa['assignments'][$ipid]))
					)
			) {
				continue; // this user settings ignores our messages
			}
				
			$records_array[$k_cnt] = array(
					"sender" => $this->logininfo->userid,
					"clientid" => $this->logininfo->clientid,
					"recipient" => $receiver_id,
					"msg_date" => $msg_date,
					"title" => null,
					"content" => null,
					"create_date" => $create_date,
					"create_user" => $this->logininfo->userid,
			);
				
			$messages_array[$k_cnt] = $gpost['verlauf_entry'];
			
			//$messages_array[$k_cnt] .= "\n<a href='patientcourse/patientcourse?id=". $pat_encoded_id ."#wrc_" . $Invoker_id . "'>".$patarr['nice_name_epid']."</a>";
			//Lore 16.11.2020
			$messages_array[$k_cnt] .= "\n<a href=".APP_BASE."patientcourse/patientcourse?id=". $pat_encoded_id ."#wrc_" . $Invoker_id . ">".$patarr['nice_name_epid']."</a>";
			$messages_array[$k_cnt] .= "\n".$patarr['nice_address'];
			
			$messages_array[$k_cnt] .= (! empty($inputs['content'])) ? "\n\n" . $this->setTriggerPlaceHolders($patarr, $inputs['content']) : "\n";
			
			$recipients_array[] = $receiver_id; 
			$k_cnt++;
			
		}
		
		$messages_array['message_title'] = ! empty($inputs['title']) ? $inputs['title'] : ( ! empty($gpost['title']) ? $gpost['title'] : "");
		
		$messages_array_enc = Pms_CommonData::aesEncryptMultiple($messages_array);
		foreach ($records_array as $k=>&$row) {
			
			$row['title'] = $messages_array_enc['message_title'];
			$row['content'] = $messages_array_enc[$k];
			
			if (count($recipients_array) >1) {
				$row['recipients'] = implode(",",$recipients_array);
			}
			
		}

		if (! empty($records_array)) {
			$collection = new Doctrine_Collection('Messages');
			$collection->fromArray($records_array);
			$collection->save();
		}
		
	}
	
	
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